<?php
if ( ! defined( 'ABSPATH' ) ) exit; // Exit if accessed directly

/*
 * Handle submit booking ajax request
 */
if ( ! function_exists( 'trav_ajax_flight_submit_booking' ) ) {
    function trav_ajax_flight_submit_booking() {
        global $wpdb, $trav_options;
        // validation
        $result_json = array( 'success' => 0, 'result' => '' );

        if ( ! isset( $_POST['transaction_id'] ) || ! isset( $_SESSION['booking_data'][$_POST['transaction_id']] ) ) {
            $result_json['success'] = 0;
            $result_json['result'] = __( 'Sorry, some error occurred on input data validation.', 'trav' );
            wp_send_json( $result_json );
        }

        $raw_booking_data = $_SESSION['booking_data'][$_POST['transaction_id']];
        $booking_fields = array(
                                'flight_id',
                                'adults',
                                'total_price',
                                'tax',
                                'currency_code',
                                'exchange_rate',
                                'deposit_price',
                                'flight_date',
                                'location_from',
                                'location_to',
                                'departure_time',
                                'arrival_time',
                                'air_line',
                                'flight_type',
                                'flight_stop',
                                'trip_type',
                                'return_flight_id',
                                'return_flight_date',
                                'return_location_from',
                                'return_location_to',
                                'return_departure_time',
                                'return_arrival_time',
                                'return_air_line',
                                'return_flight_type',
                                'return_flight_stop',
                                'created',
                                'booking_no',
                                'pin_code',
                                'status'
                            );

        $booking_data = array();
        foreach( $booking_fields as $booking_field ) {
            if ( ! empty( $raw_booking_data[ $booking_field ] ) ) {
                $booking_data[ $booking_field ] = $raw_booking_data[ $booking_field ];
            }
        }
        
        $flight_id = $booking_data['flight_id'];

        $booking_data['departure_time'] = get_post_meta( $flight_id, 'trav_flight_departure_time', true );
		$booking_data['arrival_time'] = get_post_meta( $flight_id, 'trav_flight_arrival_time', true );
		
		$air_line = wp_get_post_terms( $flight_id, 'air_line', array( "fields" => "all" ) );
		if ( ! empty( $air_line ) ) {
		    $booking_data['air_line'] = $air_line[0]->term_id;		    
		}

		$flight_stop = wp_get_post_terms( $flight_id, 'flight_stop', array( "fields" => "all" ) );
		if ( ! empty( $flight_stop ) ) {
		    $booking_data['flight_stop'] = $flight_stop[0]->term_id;
		}

		$flight_type = wp_get_post_terms( $flight_id, 'flight_type', array( "fields" => "all" ) );
		if ( ! empty( $flight_type ) ) {
		    $booking_data['flight_type'] = $flight_type[0]->term_id;
		}
		$location_from = wp_get_post_terms( $flight_id, 'flight_location', array( "fields" => "all" ) );
		if ( ! empty( $location_from ) ) {
		    $booking_data['location_from'] = $location_from[0]->term_id;
		}
		$booking_data['location_to'] = get_post_meta( $flight_id, 'trav_flight_location_to', true );
		$booking_data['flight_date'] = trav_tosqltime( $booking_data['flight_date'] );

        $return_flight_id = $booking_data['return_flight_id'];

        $booking_data['return_departure_time'] = get_post_meta( $return_flight_id, 'trav_flight_departure_time', true );
        $booking_data['return_arrival_time'] = get_post_meta( $return_flight_id, 'trav_flight_arrival_time', true );
        
        $return_air_line = wp_get_post_terms( $return_flight_id, 'air_line', array( "fields" => "all" ) );
        if ( ! empty( $return_air_line ) ) {
            $booking_data['return_air_line'] = $return_air_line[0]->term_id;          
        }

        $return_flight_stop = wp_get_post_terms( $return_flight_id, 'flight_stop', array( "fields" => "all" ) );
        if ( ! empty( $return_flight_stop ) ) {
            $booking_data['return_flight_stop'] = $return_flight_stop[0]->term_id;
        }

        $return_flight_type = wp_get_post_terms( $return_flight_id, 'flight_type', array( "fields" => "all" ) );
        if ( ! empty( $return_flight_type ) ) {
            $booking_data['return_flight_type'] = $return_flight_type[0]->term_id;
        }
        $return_location_from = wp_get_post_terms( $return_flight_id, 'flight_location', array( "fields" => "all" ) );
        if ( ! empty( $return_location_from ) ) {
            $booking_data['return_location_from'] = $return_location_from[0]->term_id;
        }
        $booking_data['return_location_to'] = get_post_meta( $return_flight_id, 'trav_flight_location_to', true );
        $booking_data['return_flight_date'] = trav_tosqltime( $booking_data['return_flight_date'] );

        $is_payment_enabled = trav_is_payment_enabled() && ! empty( $booking_data['deposit_price'] );

        if ( ! isset( $_POST['_wpnonce'] ) || ! wp_verify_nonce( $_POST['_wpnonce'], 'post-' . $booking_data['flight_id'] ) ) {
            $result_json['success'] = 0;
            $result_json['result'] = __( 'Sorry, your nonce did not verify.', 'trav' );
            wp_send_json( $result_json );
        }

        if ( isset( $trav_options['vld_captcha'] ) && ! empty( $trav_options['vld_captcha'] ) ) {
            if ( ! isset( $_POST['security_code'] ) || $_POST['security_code'] != $_SESSION['security_code'] ) {
                $result_json['success'] = 0;
                $result_json['result'] = __( 'Captcha error. Please check your security code again.', 'trav' );
                wp_send_json( $result_json );
            }
        }

        // init variables
        $post_fields = array(
                                'first_name',
                                'last_name',
                                'email',
                                'country_code',
                                'phone',
                                'address',
                                'city',
                                'zip',
                                'country',
                                'special_requirements'
                            );

        $customer_info = array();
        foreach ( $post_fields as $post_field ) {
            if ( ! empty( $_POST[ $post_field ] ) ) {
                $customer_info[ $post_field ] = sanitize_text_field( $_POST[ $post_field ] );
            }
        }

        $data = array_merge( $customer_info, $booking_data );
        if ( is_user_logged_in() ) {
            $data['user_id'] = get_current_user_id();
        }

        $latest_booking_id = $wpdb->get_var( 'SELECT id FROM ' . TRAV_FLIGHT_BOOKINGS_TABLE . ' ORDER BY id DESC LIMIT 1' );
        $booking_no = mt_rand( 1000, 9999 );
        $booking_no .= $latest_booking_id;
        $pin_code = mt_rand( 1000, 9999 );

        if ( ! isset( $_SESSION['exchange_rate'] ) ) trav_init_currency();

        $default_booking_data = array(  
            'first_name'            => '',
            'last_name'             => '',
            'email'                 => '',
            'country_code'          => '',
            'phone'                 => '',
            'address'               => '',
            'city'                  => '',
            'zip'                   => '',
            'country'               => '',
            'special_requirements'  => '',
            'flight_id'      		=> '',
            'flight_date'			=> '',
            'location_from'			=> '',
            'location_to'			=> '',
            'departure_time'		=> '',
            'arrival_time'			=> '',
            'air_line'				=> '',
            'flight_type'			=> '',
            'flight_stop'			=> '',
            'trip_type'             => '',
            'return_flight_id'             => '',
            'return_flight_date'           => '',
            'return_location_from'         => '',
            'return_location_to'           => '',
            'return_departure_time'        => '',
            'return_arrival_time'          => '',
            'return_air_line'              => '',
            'return_flight_type'           => '',
            'return_flight_stop'           => '',
            'adults'                => '',
            'total_price'           => '',
            'tax'                   => '',
            'currency_code'         => 'usd',
            'exchange_rate'         => 1,
            'deposit_price'         => 0,
            'deposit_paid'          => ( $is_payment_enabled ? 0 : 1 ),
            'created'               => date( 'Y-m-d H:i:s' ),
            'booking_no'            => $booking_no,
            'pin_code'              => $pin_code,
            'status'                => 1,
            'discount_rate'         => ''
        );

        $data = array_replace( $default_booking_data, $data );

        do_action( 'trav_flight_add_booking_before', $data );

        // save default language flight and cabin type
        $data['flight_id'] = trav_flight_org_id( $data['flight_id'] );
        $data['return_flight_id'] = trav_flight_org_id( $data['return_flight_id'] );
        
        // add to db
        if ( $wpdb->insert( TRAV_FLIGHT_BOOKINGS_TABLE, $data ) ) {
            $data['booking_id'] = $wpdb->insert_id;
            $_SESSION['booking_data'][$_POST['transaction_id']] = $data;

            $result_json['success'] = 1;
            $result_json['result'] = array();
            $result_json['result']['booking_no'] = $booking_no;
            $result_json['result']['pin_code'] = $pin_code;
            $result_json['result']['transaction_id'] = $_POST['transaction_id'];

            if ( $is_payment_enabled ) {
                if ( trav_is_woo_enabled() ) {
                    // woocommerce
                    do_action( 'trav_woo_add_flight_booking', $data );

                    $result_json['result']['payment'] = 'woocommerce';
                } elseif ( trav_is_paypal_enabled() ) {
                    // paypal direct
                    $result_json['result']['payment'] = 'paypal';
                }
            } else {
                $result_json['result']['payment'] = 'no';
            }

            do_action( 'trav_flight_add_booking_after', $data );
        } else {
            $result_json['success'] = 0;
            $result_json['result'] = __( 'Sorry, An error occurred while add booking.', 'trav' );
        }

        wp_send_json( $result_json );
    }
}