<?php
if ( ! defined( 'ABSPATH' ) ) exit; // Exit if accessed directly

/*
 * Get Flight Search Result
 */
if ( ! function_exists( 'trav_flight_get_search_result' ) ) {
	function trav_flight_get_search_result( $location_from = '', $location_to = '', $flight_date = '', $adults = 1, $min_price = 0, $max_price = '', $flight_types = array(), $air_lines = array(), $flight_stops = array() ) {
		// if wrong date return false
		if ( empty( $location_from ) || empty( $location_to ) || empty( $flight_date ) ) {
			return false;
		}

		global $wpdb, $language_count;

		$tbl_posts = esc_sql( $wpdb->posts );
		$tbl_postmeta = esc_sql( $wpdb->postmeta );
		$tbl_terms = esc_sql( $wpdb->prefix . 'terms' );
		$tbl_term_taxonomy = esc_sql( $wpdb->prefix . 'term_taxonomy' );
		$tbl_term_relationships = esc_sql( $wpdb->prefix . 'term_relationships' );
		$tbl_icl_translations = esc_sql( $wpdb->prefix . 'icl_translations' );

		$sql = '';
		$adults = esc_sql( $adults );
		$min_price = esc_sql( $min_price );
		$max_price = esc_sql( $max_price );
		
		if ( empty( $flight_types ) || ! is_array( $flight_types ) ) $flight_types = array();
		if ( empty( $air_lines ) || ! is_array( $air_lines ) ) $air_lines = array();
		if ( empty( $flight_stops ) || ! is_array( $flight_stops ) ) $flight_stops = array();
		foreach ( $flight_types as $key => $value ) {
			if ( ! is_numeric( $value ) ) unset( $flight_types[$key] );
		}
		foreach ( $air_lines as $key => $value ) {
			if ( ! is_numeric( $value ) ) unset( $air_lines[$key] );
		}
		foreach ( $flight_stops as $key => $value ) {
			if ( ! is_numeric( $value ) ) unset( $flight_stops[$key] );
		}

		$flight_date = trav_tosqltime( $flight_date );
		$day_of_week = date( 'w', trav_strtotime( $flight_date ) );
		switch ( $day_of_week ) {
			case '0':
				$meta_day_key = 'trav_flight_sunday_available';
				break;
			case '1':
				$meta_day_key = 'trav_flight_monday_available';
				break;
			case '2':
				$meta_day_key = 'trav_flight_tuesday_available';
				break;
			case '3':
				$meta_day_key = 'trav_flight_wednesday_available';
				break;
			case '4':
				$meta_day_key = 'trav_flight_thursday_available';
				break;
			case '5':
				$meta_day_key = 'trav_flight_friday_available';
				break;
			case '6':
				$meta_day_key = 'trav_flight_saturday_available';
				break;
			default:
				$meta_day_key = 'trav_flight_sunday_available';
				break;
		}

		$s_query = "SELECT DISTINCT post_s1.ID AS flight_id FROM {$tbl_posts} AS post_s1 
						WHERE (post_s1.post_status = 'publish') AND (post_s1.post_type = 'flight')";


		// if wpml is enabled do search by default language post
		if ( defined('ICL_LANGUAGE_CODE') && ( $language_count > 1 ) && ( trav_get_default_language() != ICL_LANGUAGE_CODE ) ) {
			$s_query = "SELECT DISTINCT it2.element_id AS flight_id FROM ({$s_query}) AS t0
						INNER JOIN {$tbl_icl_translations} it1 ON (it1.element_type = 'post_flight') AND it1.element_id = t0.flight_id
						INNER JOIN {$tbl_icl_translations} it2 ON (it2.element_type = 'post_flight') AND it2.language_code='" . trav_get_default_language() . "' AND it2.trid = it1.trid ";
		}

		$sql = "SELECT t1.* FROM ( {$s_query} ) AS t1 ";
		$sql .= " LEFT JOIN {$tbl_postmeta} AS meta_max_adults ON (meta_max_adults.meta_key = 'trav_flight_max_adults') AND (t1.flight_id = meta_max_adults.post_id)";
		$sql .= " LEFT JOIN {$tbl_postmeta} AS meta_price ON (t1.flight_id = meta_price.post_id) AND (meta_price.meta_key = 'trav_flight_price') ";
		$sql .= " LEFT JOIN {$tbl_postmeta} AS meta_available_date_setting ON (t1.flight_id = meta_available_date_setting.post_id) AND (meta_available_date_setting.meta_key = 'trav_flight_available_setting') ";
		$sql .= " LEFT JOIN {$tbl_postmeta} AS meta_week_days ON (t1.flight_id = meta_week_days.post_id) AND (meta_week_days.meta_key = '" . $meta_day_key . "') ";
		$sql .= " LEFT JOIN {$tbl_postmeta} AS meta_special_dates ON (t1.flight_id = meta_special_dates.post_id) AND (meta_special_dates.meta_key = 'trav_flight_available_dates') ";
		$sql .= " LEFT JOIN ( SELECT flight_booking.flight_id, SUM( flight_booking.adults ) as adults FROM " . TRAV_FLIGHT_BOOKINGS_TABLE . " AS flight_booking
						WHERE flight_booking.flight_date = '{$flight_date}' AND flight_booking.status != 'cancelled'
						GROUP BY flight_booking.flight_id ) AS booking_info ON booking_info.flight_id = t1.flight_id";

		$sql .= " WHERE ((meta_max_adults.meta_value IS NULL) OR (meta_max_adults.meta_value='') OR (meta_max_adults.meta_value-IFNULL(booking_info.adults, 0) >= {$adults}) )
						AND (meta_available_date_setting.meta_value = 'daily' OR ( meta_available_date_setting.meta_value = 'weekly' AND meta_week_days.meta_value = '1' ) OR ( meta_available_date_setting.meta_value = 'special_dates' AND meta_special_dates.meta_value like '%" . $flight_date . "%' ) )";
		if ( $min_price != 0 ) {
			$sql .= " AND cast(meta_price.meta_value as unsigned) >= {$min_price}";
		}
		if ( $max_price != 'no_max' ) {
			$sql .= " AND cast(meta_price.meta_value as unsigned) <= {$max_price} ";
		}

		// if wpml is enabled return current language posts
		if ( defined('ICL_LANGUAGE_CODE') && ( $language_count > 1 ) && ( trav_get_default_language() != ICL_LANGUAGE_CODE ) ) {
			$sql = "SELECT it4.element_id AS flight_id FROM ({$sql}) AS t5
					INNER JOIN {$tbl_icl_translations} it3 ON (it3.element_type = 'post_flight') AND it3.element_id = t5.flight_id
					INNER JOIN {$tbl_icl_translations} it4 ON (it4.element_type = 'post_flight') AND it4.language_code='" . ICL_LANGUAGE_CODE . "' AND it4.trid = it3.trid";
		}

		// var_dump($sql);
		$sql = "SELECT  DISTINCT t1.* FROM ( {$sql} ) as t1
				INNER JOIN {$tbl_posts} post_l1 ON (t1.flight_id = post_l1.ID) AND (post_l1.post_status = 'publish') AND (post_l1.post_type = 'flight') ";
				
		$where = ' 1=1';

		if ( ! empty( $flight_types ) ) {
			$sql .= " INNER JOIN {$tbl_term_relationships} AS tr1 ON tr1.object_id = t1.flight_id 
					INNER JOIN {$tbl_term_taxonomy} AS tt1 ON tt1.term_taxonomy_id = tr1.term_taxonomy_id";
			$where .= " AND tt1.taxonomy = 'flight_type' AND tt1.term_id IN (" . esc_sql( implode( ',', $flight_types ) ) . ")";
		}

		if ( ! empty( $air_lines ) ) {
			$sql .= " INNER JOIN {$tbl_term_relationships} AS tr2 ON tr2.object_id = t1.flight_id 
					INNER JOIN {$tbl_term_taxonomy} AS tt2 ON tt2.term_taxonomy_id = tr2.term_taxonomy_id";
			$where .= " AND tt2.taxonomy = 'air_line' AND tt2.term_id IN (" . esc_sql( implode( ',', $air_lines ) ) . ")";
		}

		if ( ! empty( $flight_stops ) ) {
			$sql .= " INNER JOIN {$tbl_term_relationships} AS tr3 ON tr3.object_id = t1.flight_id 
					INNER JOIN {$tbl_term_taxonomy} AS tt3 ON tt3.term_taxonomy_id = tr3.term_taxonomy_id";
			$where .= " AND tt3.taxonomy = 'flight_stop' AND tt3.term_id IN (" . esc_sql( implode( ',', $flight_stops ) ) . ")";
		}

		if ( ! empty( $location_from ) ) {
			$sql .= " INNER JOIN {$tbl_term_relationships} AS tr4 ON tr4.object_id = t1.flight_id 
					INNER JOIN {$tbl_term_taxonomy} AS tt4 ON tt4.term_taxonomy_id = tr4.term_taxonomy_id";
			$where .= " AND tt4.taxonomy = 'flight_location' AND tt4.term_id = '" . esc_sql( $location_from ) . "'";
		}

		if ( ! empty( $location_to ) ) {
			$sql .= " LEFT JOIN {$tbl_postmeta} AS meta_location_to ON (meta_location_to.meta_key = 'trav_flight_location_to') AND (t1.flight_id = meta_location_to.post_id)";
			$where .= " AND meta_location_to.meta_value='" . esc_sql( $location_to ) . "'";
		}

		$sql .= " WHERE {$where};";

		$results = $wpdb->get_results( $sql, ARRAY_A );
		
		return $results;
	}
}

/*
 * flight booking page before action
 */
if ( ! function_exists( 'trav_flight_booking_before' ) ) {
    function trav_flight_booking_before() {
        global $trav_options, $def_currency;

        // init booking data : array( 'flight_id', 'flight_date', 'adults' );
        // init booking_data fields
        $booking_fields = array( 'flight_id', 'flight_date', 'adults', 'trip_type' );
        $booking_data = array();
        foreach ( $booking_fields as $field ) {
            if ( ! isset( $_REQUEST[ $field ] ) ) {
                return;
                do_action('trav_flight_booking_wrong_data');
                exit;
            } else {
                $booking_data[ $field ] = $_REQUEST[ $field ];
            }
        }

        if ( $booking_data['trip_type'] == 'round_trip' ) {
        	$booking_fields = array( 'return_flight_id', 'return_flight_date' );
			foreach ( $booking_fields as $field ) {
	            if ( ! isset( $_REQUEST[ $field ] ) ) {
	                return;
	                do_action('trav_flight_booking_wrong_data');
	                exit;
	            } else {
	                $booking_data[ $field ] = $_REQUEST[ $field ];
	            }
	        }        	
        }

        $flight_url = get_permalink( $booking_data['flight_id'] );
        $price = get_post_meta( $booking_data['flight_id'], 'trav_flight_price', true );
        $price *=  $booking_data['adults'];

        // calculate tax, discount and total price
        $tax_rate = get_post_meta( $booking_data['flight_id'], 'trav_flight_tax_rate', true );
        $tax = 0;
        if ( ! empty( $tax_rate ) ) {
            $tax = $tax_rate * $price / 100;
        }

        $return_price = 0;
        $return_tax = 0;

        if ( $booking_data['trip_type'] == 'round_trip' ) {
	        $return_flight_url = get_permalink( $booking_data['return_flight_id'] );
	        $return_price = get_post_meta( $booking_data['return_flight_id'], 'trav_flight_price', true );
	        $return_price *=  $booking_data['adults'];

	        // calculate tax, discount and total price
	        $return_tax_rate = get_post_meta( $booking_data['return_flight_id'], 'trav_flight_tax_rate', true );
	        
	        if ( ! empty( $return_tax_rate ) ) {
	            $return_tax = $return_tax_rate * $return_price / 100;
	        }

	        $return_deposit_rate = get_post_meta( $booking_data['return_flight_id'], 'trav_flight_security_deposit', true );
	    }
        
        $booking_data['price'] = $price + $return_price;
        $booking_data['tax'] = $tax + $return_tax;
        $booking_data['total_price'] = $booking_data['price'] + $booking_data['tax'];

        // calculate deposit payment
        $deposit_rate = get_post_meta( $booking_data['flight_id'], 'trav_flight_security_deposit', true );
        

        if ( empty( $deposit_rate ) ) {
        	$deposit_rate = 0;
        }
        if ( empty( $return_deposit_rate ) ) {
        	$return_deposit_rate = 0;
        }

        // if woocommerce-integration enabled, change currency_code and exchange rate as default
        if ( ( ! empty( $deposit_rate ) || ( ! empty( $return_deposit_rate ) ) ) && trav_is_woo_enabled() ) {
            $booking_data['currency_code'] = $def_currency;
            $booking_data['exchange_rate'] = 1;
        } else {
            if ( ! isset( $_SESSION['exchange_rate'] ) ) {
                trav_init_currency();
            }
            $booking_data['currency_code'] = trav_get_user_currency();
            $booking_data['exchange_rate'] = $_SESSION['exchange_rate'];
        }

        // if payment enabled set deposit price field
        $is_payment_enabled = ( ! empty( $deposit_rate ) || ( ! empty( $return_deposit_rate ) ) ) && trav_is_payment_enabled();
        if ( $is_payment_enabled ) {
            $booking_data['deposit_price'] = ( $deposit_rate / 100 * ( $price + $tax ) + $return_deposit_rate / 100 * ( $return_price + $return_tax ) ) * $booking_data['exchange_rate'];
        }

        // initialize session values
        $transaction_id = mt_rand( 100000, 999999 );
        $_SESSION['booking_data'][$transaction_id] = $booking_data; //'flight_id', 'flight_date', 'adults', tax, total_price, currency_code, exchange_rate, deposit_price

        // thank you page url
        $flight_book_conf_url = '';
        if ( ! empty( $trav_options['flight_booking_confirmation_page'] ) ) {
            $flight_book_conf_url = trav_get_permalink_clang( $trav_options['flight_booking_confirmation_page'] );
        } else {
            // thank you page is not set
        }

        global $trav_booking_page_data;
        $trav_booking_page_data['transaction_id'] = $transaction_id;
        $trav_booking_page_data['booking_data'] = $booking_data;
        $trav_booking_page_data['price'] = $price;
        $trav_booking_page_data['is_payment_enabled'] = $is_payment_enabled;
        $trav_booking_page_data['flight_book_conf_url'] = $flight_book_conf_url;
        $trav_booking_page_data['tax'] = $tax;
        $trav_booking_page_data['tax_rate'] = $tax_rate;
    }
}

/*
 * get booking data with booking_no and pin_code
 */
if ( ! function_exists( 'trav_flight_get_booking_data' ) ) {
    function trav_flight_get_booking_data( $booking_no, $pin_code ) {
        global $wpdb;
        return $wpdb->get_row( 'SELECT * FROM ' . TRAV_FLIGHT_BOOKINGS_TABLE . ' WHERE booking_no="' . esc_sql( $booking_no ) . '" AND pin_code="' . esc_sql( $pin_code ) . '"', ARRAY_A );
    }
}

/*
 * echo deposit payment not paid notice on confirmation page
 */
if ( ! function_exists( 'trav_flight_deposit_payment_not_paid' ) ) {
	function trav_flight_deposit_payment_not_paid( $booking_data ) {
		echo '<div class="alert alert-notice">' . __( 'Deposit amount is not paid.', 'trav' ) . '<span class="close"></span></div>';
	}
}

/*
 * send confirmation email
 */
if ( ! function_exists( 'trav_flight_conf_send_mail' ) ) {
	function trav_flight_conf_send_mail( $booking_data ) {
		global $wpdb;
		$mail_sent = 0;
		if ( trav_flight_send_confirmation_email( $booking_data['booking_no'], $booking_data['pin_code'], 'new' ) ) {
			$mail_sent = 1;
			$wpdb->update( TRAV_FLIGHT_BOOKINGS_TABLE, array( 'mail_sent' => $mail_sent ), array( 'booking_no' => $booking_data['booking_no'], 'pin_code' => $booking_data['pin_code'] ), array( '%d' ), array( '%d','%d' ) );
		}
	}
}

/*
 * send booking confirmation email function
 */
if ( ! function_exists( 'trav_flight_send_confirmation_email' ) ) {
	function trav_flight_send_confirmation_email( $booking_no, $booking_pincode, $type='new', $subject='', $description='' ) {
		global $wpdb, $logo_url, $trav_options;

		$booking_data = trav_flight_get_booking_data( $booking_no, $booking_pincode );

		if ( ! empty( $booking_data ) ) {
			// server variables
			$admin_email = get_option('admin_email');
			$home_url = esc_url( home_url() );
			$site_name = $_SERVER['SERVER_NAME'];
			$logo_url = esc_url( $logo_url );
			$flight_book_conf_url = trav_flight_get_book_conf_url();
			$booking_data['flight_id'] = trav_flight_clang_id( $booking_data['flight_id'] );

			// flight info
			$flight_name = get_the_title( $booking_data['flight_id'] );
			$flight_phone = get_post_meta( $booking_data['flight_id'], 'trav_flight_phone', true );
			$flight_email = get_post_meta( $booking_data['flight_id'], 'trav_flight_email', true );
			$flight_date = date( 'l, F, j, Y', trav_strtotime($booking_data['flight_date']) );
			$departure_time = $booking_data['departure_time'];
			$arrival_time = $booking_data['arrival_time'];
			$location_from = get_term( $booking_data['location_from'], 'flight_location' );
			if ( ! empty( $location_from ) ) {
				$location_from = $location_from->name;
			}
			$location_to = get_term( $booking_data['location_to'], 'flight_location' );
			if ( ! empty( $location_to ) ) {
				$location_to = $location_to->name;
			}
			$flight_stop = get_term( $booking_data['flight_stop'], 'flight_location' );
			if ( ! empty( $flight_stop ) ) {
				$flight_stop = $flight_stop->name;
			}
			$flight_type = get_term( $booking_data['flight_type'], 'flight_location' );
			if ( ! empty( $flight_type ) ) {
				$flight_type = $flight_type->name;
			}
			$air_line = get_term( $booking_data['air_line'], 'flight_location' );
			if ( ! empty( $air_line ) ) {
				$air_line = $air_line->name;
			}

			$booking_data['return_flight_id'] = trav_flight_clang_id( $booking_data['return_flight_id'] );

			// flight info
			$return_flight_name = get_the_title( $booking_data['return_flight_id'] );
			$return_flight_phone = get_post_meta( $booking_data['return_flight_id'], 'trav_flight_phone', true );
			$return_flight_email = get_post_meta( $booking_data['return_flight_id'], 'trav_flight_email', true );
			$return_flight_date = date( 'l, F, j, Y', trav_strtotime($booking_data['return_flight_date']) );
			$return_departure_time = $booking_data['return_departure_time'];
			$return_arrival_time = $booking_data['return_arrival_time'];
			$return_location_from = get_term( $booking_data['return_location_from'], 'flight_location' );
			if ( ! empty( $return_location_from ) && ! is_wp_error( $return_location_from ) ) {
				$return_location_from = $return_location_from->name;
			} else {
				$return_location_from = '';
			}
			$return_location_to = get_term( $booking_data['return_location_to'], 'flight_location' );
			if ( ! empty( $return_location_to ) && ! is_wp_error( $return_location_to ) ) {
				$return_location_to = $return_location_to->name;
			} else {
				$return_location_to = '';
			}
			$return_flight_stop = get_term( $booking_data['return_flight_stop'], 'flight_location' );
			if ( ! empty( $return_flight_stop ) && ! is_wp_error( $return_flight_stop ) ) {
				$return_flight_stop = $return_flight_stop->name;
			} else {
				$return_flight_stop = '';
			}
			$return_flight_type = get_term( $booking_data['return_flight_type'], 'flight_location' );
			if ( ! empty( $return_flight_type ) && ! is_wp_error( $return_flight_type ) ) {
				$return_flight_type = $return_flight_type->name;
			} else {
				$return_flight_type = '';
			}
			$return_air_line = get_term( $booking_data['return_air_line'], 'flight_location' );
			if ( ! empty( $return_air_line ) && ! is_wp_error( $return_air_line ) ) {
				$return_air_line = $return_air_line->name;
			}else {
				$return_air_line = '';
			}

			$trip_type = $booking_data['trip_type'];
			if ( $trip_type = 'one_way' ) {
				$trip_type = esc_html__( 'ONE-WAY', 'trav' );
			} else {
				$trip_type = esc_html__( 'ROUND-TRIP', 'trav' );
			}
			
			// booking info
			$booking_no = $booking_data['booking_no'];
			$booking_pincode = $booking_data['pin_code'];
			
			$booking_adults = $booking_data['adults'];
			$booking_tax = esc_html( trav_get_price_field( $booking_data['tax'] * $booking_data['exchange_rate'], $booking_data['currency_code'], 0 ) );
			$booking_total_price = esc_html( trav_get_price_field( $booking_data['total_price'] * $booking_data['exchange_rate'], $booking_data['currency_code'], 0 ) );
			$booking_deposit_price = esc_html( $booking_data['deposit_price'] . $booking_data['currency_code'] );
			$booking_deposit_paid = esc_html( empty( $booking_data['deposit_paid'] ) ? 'No' : 'Yes' );

			// customer info
			$customer_first_name = $booking_data['first_name'];
			$customer_last_name = $booking_data['last_name'];
			$customer_email = $booking_data['email'];
			$customer_country_code = $booking_data['country_code'];
			$customer_phone = $booking_data['phone'];
			$customer_address = $booking_data['address'];
			$customer_city = $booking_data['city'];
			$customer_zip = $booking_data['zip'];
			$customer_country = $booking_data['country'];
			$customer_special_requirements = $booking_data['special_requirements'];

			$variables = array( 
				'home_url',
				'site_name',
				'logo_url',
				'booking_no',
				'booking_pincode',
				'trip_type',
				'flight_name',
				'flight_phone',
				'flight_email',
				'flight_date',
				'departure_time',
				'arrival_time',
				'location_from',
				'location_to',
				'flight_stop',
				'flight_type',
				'air_line',
				'return_flight_name',
				'return_flight_phone',
				'return_flight_email',
				'return_flight_date',
				'return_departure_time',
				'return_arrival_time',
				'return_location_from',
				'return_location_to',
				'return_flight_stop',
				'return_flight_type',
				'return_air_line',
				'booking_adults',
				'booking_tax',
				'booking_total_price',
				'booking_deposit_price',
				'booking_deposit_paid',
				'customer_first_name',
				'customer_last_name',
				'customer_email',
				'customer_country_code',
				'customer_phone',
				'customer_address',
				'customer_city',
				'customer_zip',
				'customer_country',
				'customer_special_requirements'
			);

			if ( empty( $subject ) ) {
				if ( $type == 'new' ) {
					$subject = empty( $trav_options['flight_confirm_email_subject'] ) ? 'Booking Confirmation Email Subject' : $trav_options['flight_confirm_email_subject'];
				} elseif ( $type == 'update' ) {
					$subject = empty( $trav_options['flight_update_email_subject'] ) ? 'Booking Updated Email Subject' : $trav_options['flight_update_email_subject'];
				} elseif ( $type == 'cancel' ) {
					$subject = empty( $trav_options['flight_cancel_email_subject'] ) ? 'Booking Canceled Email Subject' : $trav_options['flight_cancel_email_subject'];
				}
			}

			if ( empty( $description ) ) {
				if ( $type == 'new' ) {
					$description = empty( $trav_options['flight_confirm_email_description'] ) ? 'Booking Confirmation Email Description' : $trav_options['flight_confirm_email_description'];
				} elseif ( $type == 'update' ) {
					$description = empty( $trav_options['flight_update_email_description'] ) ? 'Booking Confirmation Email Description' : $trav_options['flight_update_email_description'];
				} elseif ( $type == 'cancel' ) {
					$description = empty( $trav_options['flight_cancel_email_description'] ) ? 'Booking Confirmation Email Description' : $trav_options['flight_cancel_email_description'];
				}
			}

			foreach ( $variables as $variable ) {
				$subject = str_replace( "[" . $variable . "]", $$variable, $subject );
				$description = str_replace( "[" . $variable . "]", $$variable, $description );
			}

			// if ( ! empty( $trav_options['flight_confirm_email_ical'] ) && ( $type == 'new' ) ) {
			//     $mail_sent = trav_send_ical_event( $site_name, $admin_email, $customer_first_name . ' ' . $customer_last_name, $customer_email, $check_in_time, $check_out_time, $subject, $description, $flight_address);
			// } else {
				$mail_sent = trav_send_mail( $site_name, $admin_email, $customer_email, $subject, $description );
			// }

			/* mailing function to business owner */
			$bowner_address = '';
			if ( ! empty( $trav_options['flight_booked_notify_bowner'] ) ) {

				if ( $type == 'new' ) {
					$subject = empty( $trav_options['flight_bowner_email_subject'] ) ? 'You received a booking' : $trav_options['flight_bowner_email_subject'];
					$description = empty( $trav_options['flight_bowner_email_description'] ) ? 'Booking Details' : $trav_options['flight_bowner_email_description'];
				} elseif ( $type == 'update' ) {
					$subject = empty( $trav_options['flight_update_bowner_email_subject'] ) ? 'A booking is updated' : $trav_options['flight_update_bowner_email_subject'];
					$description = empty( $trav_options['flight_update_bowner_email_description'] ) ? 'Booking Details' : $trav_options['flight_update_bowner_email_description'];
				} elseif ( $type == 'cancel' ) {
					$subject = empty( $trav_options['flight_cancel_bowner_email_subject'] ) ? 'A booking is canceled' : $trav_options['flight_cancel_bowner_email_subject'];
					$description = empty( $trav_options['flight_cancel_bowner_email_description'] ) ? 'Booking Details' : $trav_options['flight_cancel_bowner_email_description'];
				}

				foreach ( $variables as $variable ) {
					$subject = str_replace( "[" . $variable . "]", $$variable, $subject );
					$description = str_replace( "[" . $variable . "]", $$variable, $description );
				}

				if ( ! empty( $flight_email ) ) {
					$bowner_address = $flight_email;
				} else {
					$post_author_id = get_post_field( 'post_author', $booking_data['flight_id'] );
					$bowner = get_user_by( 'id', $post_author_id );
					if ( ! empty( $bowner ) ) {
						$bowner_address = $bowner->user_email;
					}
				}

				if ( ! empty( $bowner_address ) ) {
					trav_send_mail( $site_name, $admin_email, $bowner_address, $subject, $description );
				}
			}

			/* mailing function to admin */
			if ( ! empty( $trav_options['flight_booked_notify_admin'] ) ) {
				if ( $bowner_address != $admin_email ) {
					if ( $type == 'new' ) {
						$subject = empty( $trav_options['flight_admin_email_subject'] ) ? 'You received a booking' : $trav_options['flight_admin_email_subject'];
						$description = empty( $trav_options['flight_admin_email_description'] ) ? 'Booking Details' : $trav_options['flight_admin_email_description'];
					} elseif ( $type == 'update' ) {
						$subject = empty( $trav_options['flight_update_admin_email_subject'] ) ? 'A booking is updated' : $trav_options['flight_update_admin_email_subject'];
						$description = empty( $trav_options['flight_update_admin_email_description'] ) ? 'Booking Details' : $trav_options['flight_update_admin_email_description'];
					} elseif ( $type == 'cancel' ) {
						$subject = empty( $trav_options['flight_cancel_admin_email_subject'] ) ? 'A booking is canceled' : $trav_options['flight_cancel_admin_email_subject'];
						$description = empty( $trav_options['flight_cancel_admin_email_description'] ) ? 'Booking Details' : $trav_options['flight_cancel_admin_email_description'];
					}

					foreach ( $variables as $variable ) {
						$subject = str_replace( "[" . $variable . "]", $$variable, $subject );
						$description = str_replace( "[" . $variable . "]", $$variable, $description );
					}

					trav_send_mail( $site_name, $admin_email, $admin_email, $subject, $description );
				}
			}
			return true;
		}
		
		return false;
	}
}

/*
 * get booking confirmation url
 */
if ( ! function_exists( 'trav_flight_get_book_conf_url' ) ) {
	function trav_flight_get_book_conf_url() {
		global $trav_options;
		$flight_book_conf_url = '';
		if ( isset( $trav_options['flight_booking_confirmation_page'] ) && ! empty( $trav_options['flight_booking_confirmation_page'] ) ) {
			$flight_book_conf_url = trav_get_permalink_clang( $trav_options['flight_booking_confirmation_page'] );
		}
		return $flight_book_conf_url;
	}
}