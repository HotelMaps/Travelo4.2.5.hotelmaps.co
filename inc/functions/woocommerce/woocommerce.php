<?php
/*
 * Functions for WooCommerce Integration
 */

if ( ! function_exists( 'trav_woo_init' ) ) {
    function trav_woo_init() {
        $trav_options = get_option('travelo');
        if ( ! empty( $trav_options['acc_pay_woocommerce'] ) ) {
            // create necessary product category terms
            $product_cats = array(
                'accommodation' => __('Accommodations', 'trav'),
                'tour' => __('Tours', 'trav'),
                'car' => __('Cars', 'trav'),
                'cruise' => __('Cruises', 'trav'),
                'flight' => __('Flights', 'trav'),
            );

            foreach ( $product_cats as $slug => $name ) {
                if ( ! term_exists( $slug , 'product_cat' ) ) {
                    trav_woo_create_product_category( $slug, $name );
                }
            }

            add_action( 'trav_woo_add_acc_booking', 'trav_woo_add_acc_booking' );
            add_action( 'trav_woo_add_tour_booking', 'trav_woo_add_tour_booking' );
            add_action( 'trav_woo_add_car_booking', 'trav_woo_add_car_booking' );
            add_action( 'trav_woo_add_cruise_booking', 'trav_woo_add_cruise_booking' );
            add_action( 'trav_woo_add_flight_booking', 'trav_woo_add_flight_booking' );

            // Add reservetions to Booking System after payment has been completed.
            add_action( 'woocommerce_order_status_changed', 'trav_woo_process_payment', 50, 4 ); 
            add_action( 'woocommerce_thankyou', 'trav_woo_process_payment_on_thankyou', 50 ); 

            add_action( 'woocommerce_before_cart', 'trav_woo_before_cart' );
            add_action( 'woocommerce_after_cart', 'trav_woo_after_cart' );

            add_filter( 'trav_def_currency', 'trav_woo_get_def_currency' );
            add_filter( 'woocommerce_checkout_get_value', 'trav_woo_checkout_get_def_value', 20, 2 );
            add_filter( 'post_type_link', 'trav_woo_update_product_link', 10, 4  );
            // add_filter( 'template_include', 'trav_woo_disable_template_access' );
            // add_filter( 'woocommerce_return_to_shop_redirect', 'trav_woo_return_to_shop_redirect', 10, 4  );
        }
    }
}

/*
 * create woocommerce product category terms
 */
if ( ! function_exists( 'trav_woo_create_product_category' ) ) {
    function trav_woo_create_product_category( $term_slug, $term_name ) {
        wp_insert_term(
            $term_name,
            'product_cat', // the taxonomy
            array(
                // 'description'=> $term_description,
                'slug' => $term_slug,
            )
        );
    }
}

if ( ! function_exists( 'trav_woo_create_product' ) ) {
    function trav_woo_create_product( $product_data ) {

        $booking_product = array(
            'post_title' => $product_data['name'],
            'post_content' => $product_data['content'],
            'post_status' => 'publish',
            'post_type' => 'product',
            'comment_status' => 'closed'
        );
        $product_id = wp_insert_post($booking_product);

        $default_attributes = array();
        update_post_meta( $product_id, '_sku', $product_data['sku'] );
        update_post_meta( $product_id, '_stock_status', 'instock' );
        update_post_meta( $product_id, '_visibility', 'visible' );
        update_post_meta( $product_id, '_downloadable', 'no' );
        update_post_meta( $product_id, '_virtual', 'no' );
        update_post_meta( $product_id, '_featured', 'no' );
        update_post_meta( $product_id, '_sold_individually', 'yes' );
        update_post_meta( $product_id, '_default_attributes', $default_attributes );
        update_post_meta( $product_id, '_manage_stock', 'no' );
        update_post_meta( $product_id, '_backorders', 'no' );
        update_post_meta( $product_id, '_regular_price', $product_data['booking_price'] );
        update_post_meta( $product_id, '_price', $product_data['booking_price'] );
        update_post_meta( $product_id, '_trav_post_id', $product_data['post_id'] );

        wp_set_object_terms ($product_id, 'simple', 'product_type' );
        wp_set_object_terms ($product_id, $product_data['category_slug'], 'product_cat' );

        $product_attributes = array(
            'trav-booking-no'=> array(
                'name' => 'Travelo Booking Id',
                'value' => $product_data['booking_no'],
                'position' => '0',
                'is_visible' => '1',
                'is_variation' => '0',
                'is_taxonomy' => '0'
            ),
            'trav-pin-code'=> array(
                'name' => 'Travelo Pin Code',
                'value' => $product_data['pin_code'],
                'position' => '0',
                'is_visible' => '1',
                'is_variation' => '0',
                'is_taxonomy' => '0'
            )
        );

        update_post_meta( $product_id, '_product_attributes', $product_attributes);
        
        return $product_id;
    }
}

if ( ! function_exists( 'trav_woo_product_add_to_cart' ) ) {
    function trav_woo_product_add_to_cart( $product_id ) {
        global $woocommerce;

        $cart = $woocommerce->cart->get_cart();
        $in_cart = false;

        // check if product already in cart
        if ( count( $cart ) > 0 ) {
            foreach ( $cart as $cart_item_key => $values ) {
                $_product = $values['data'];

                if ( $_product->get_id() == $product_id ){
                    $in_cart = true;
                    break;
                }
            }

            if ( ! $in_cart ) {
                $woocommerce->cart->add_to_cart( $product_id );
            }
        } else {
            $woocommerce->cart->add_to_cart( $product_id );
        }
    }
}

/*
 * Handle accommodation woocommerce booking payment
 */
if ( ! function_exists( 'trav_woo_add_acc_booking' ) ) {
    function trav_woo_add_acc_booking( $booking_data ) {
        $sku = 'acc' . $booking_data['booking_id'];
        $date_from = trav_tophptime( $booking_data['date_from'] );
        $date_to = trav_tophptime( $booking_data['date_to'] );
        $product_name = __( 'Deposit for ', 'trav' );
        $product_name .= get_the_title( $booking_data['accommodation_id'] ) . ' ' . get_the_title( $booking_data['room_type_id'] ) . ' ' . $booking_data['rooms'] . __( 'rooms', 'trav' ) . ' ' . $date_from . ' - ' . $date_to;
        $product_content = __( 'From', 'trav' ) . ' ' . $date_from . ' ' . __( 'To', 'trav' ) . ' ' . $date_to . ' ' . get_the_title( $booking_data['accommodation_id'] ) . ' ' . get_the_title( $booking_data['room_type_id'] ) . ' ' . $booking_data['rooms'] . __( 'rooms', 'trav' );
        $booking_no = $booking_data['booking_no'];
        $pin_code = $booking_data['pin_code'];
        $booking_price = $booking_data['deposit_price'];
        $product_category_slug = 'accommodation';

        $product_data = array(
            'sku' => $sku,
            'name' => $product_name,
            'content' => $product_content,
            'booking_no' => $booking_no,
            'pin_code' => $pin_code,
            'booking_price' => $booking_price,
            'category_slug' => $product_category_slug,
            'post_id' => $booking_data['accommodation_id'],
        );
        $product_id = trav_woo_create_product( $product_data );
        
        trav_woo_product_add_to_cart( $product_id );
    }
}

/*
 * Handle tour woocommerce booking payment
 */
if ( ! function_exists( 'trav_woo_add_tour_booking' ) ) {
    function trav_woo_add_tour_booking( $booking_data ) {
        $sku = 'tour' . $booking_data['booking_id'];
        $tour_date = trav_tophptime( $booking_data['tour_date'] );
        $product_name = __( 'Deposit for ', 'trav' );
        $product_name .= get_the_title( $booking_data['tour_id'] ) . ' ' . trav_tour_get_schedule_type_title( $booking_data['tour_id'], $booking_data['st_id'] ) . ' ' . $tour_date;
        $product_content = __( 'Tour Date', 'trav' ) . ' ' . $tour_date . ' ' . get_the_title( $booking_data['tour_id'] ) . ' ' . trav_tour_get_schedule_type_title( $booking_data['tour_id'], $booking_data['st_id'] );
        $booking_no = $booking_data['booking_no'];
        $pin_code = $booking_data['pin_code'];
        $booking_price = $booking_data['deposit_price'];
        $product_category_slug = 'tour';

        $product_data = array(
            'sku' => $sku,
            'name' => $product_name,
            'content' => $product_content,
            'booking_no' => $booking_no,
            'pin_code' => $pin_code,
            'booking_price' => $booking_price,
            'category_slug' => $product_category_slug,
            'post_id' => $booking_data['tour_id'],
        );
        $product_id = trav_woo_create_product( $product_data );

        trav_woo_product_add_to_cart( $product_id );
    }
}

/*
 * Handle car woocommerce booking payment
 */
if ( ! function_exists( 'trav_woo_add_car_booking' ) ) {
    function trav_woo_add_car_booking( $booking_data ) {
        $sku = 'acc' . $booking_data['booking_id'];
        $date_from = trav_tophptime( $booking_data['date_from'] );
        $date_to = trav_tophptime( $booking_data['date_to'] );
        $product_name = __( 'Deposit for ', 'trav' );
        $product_name .= get_the_title( $booking_data['car_id'] ) . $date_from . ' - ' . $date_to;
        $product_content = __( 'From', 'trav' ) . ' ' . $date_from . ' ' . __( 'To', 'trav' ) . ' ' . $date_to . ' ' . get_the_title( $booking_data['car_id'] );
        $booking_no = $booking_data['booking_no'];
        $pin_code = $booking_data['pin_code'];
        $booking_price = $booking_data['deposit_price'];
        $product_category_slug = 'car';

        $product_data = array(
            'sku' => $sku,
            'name' => $product_name,
            'content' => $product_content,
            'booking_no' => $booking_no,
            'pin_code' => $pin_code,
            'booking_price' => $booking_price,
            'category_slug' => $product_category_slug,
            'post_id' => $booking_data['car_id'],
        );
        $product_id = trav_woo_create_product( $product_data );
        
        trav_woo_product_add_to_cart( $product_id );
    }
}

/*
 * Handle cruise woocommerce booking payment
 */
if ( ! function_exists( 'trav_woo_add_cruise_booking' ) ) {
    function trav_woo_add_cruise_booking( $booking_data ) {
        $sku = 'acc' . $booking_data['booking_id'];
        $date_from = trav_tophptime( $booking_data['date_from'] );
        $date_to = trav_tophptime( $booking_data['date_to'] );
        $product_name = __( 'Deposit for ', 'trav' );
        $product_name .= get_the_title( $booking_data['cruise_id'] ) . ' ' . get_the_title( $booking_data['cabin_type_id'] ) . ' ' . $booking_data['cabins'] . __( 'cabins', 'trav' ) . ' ' . $date_from . ' - ' . $date_to;
        $product_content = __( 'From', 'trav' ) . ' ' . $date_from . ' ' . __( 'To', 'trav' ) . ' ' . $date_to . ' ' . get_the_title( $booking_data['cruise_id'] ) . ' ' . get_the_title( $booking_data['cabin_type_id'] ) . ' ' . $booking_data['cabins'] . __( 'cabins', 'trav' );
        $booking_no = $booking_data['booking_no'];
        $pin_code = $booking_data['pin_code'];
        $booking_price = $booking_data['deposit_price'];
        $product_category_slug = 'cruise';

        $product_data = array(
            'sku' => $sku,
            'name' => $product_name,
            'content' => $product_content,
            'booking_no' => $booking_no,
            'pin_code' => $pin_code,
            'booking_price' => $booking_price,
            'category_slug' => $product_category_slug,
            'post_id' => $booking_data['cruise_id'],
        );
        $product_id = trav_woo_create_product( $product_data );
        
        trav_woo_product_add_to_cart( $product_id );
    }
}

/*
 * Handle flight woocommerce booking payment
 */
if ( ! function_exists( 'trav_woo_add_flight_booking' ) ) {
    function trav_woo_add_flight_booking( $booking_data ) {
        $sku = 'flight' . $booking_data['booking_id'];
        $flight_date = trav_tophptime( $booking_data['flight_date'] );
        $product_name = __( 'Deposit for ', 'trav' );
        $product_name .= get_the_title( $booking_data['flight_id'] ) . ' ' . $flight_date;
        $product_content = __( 'Date', 'trav' ) . ' ' . $flight_date . ' ' . get_the_title( $booking_data['flight_id'] );
        $booking_no = $booking_data['booking_no'];
        $pin_code = $booking_data['pin_code'];
        $booking_price = $booking_data['deposit_price'];
        $product_category_slug = 'flight';

        $product_data = array(
            'sku' => $sku,
            'name' => $product_name,
            'content' => $product_content,
            'booking_no' => $booking_no,
            'pin_code' => $pin_code,
            'booking_price' => $booking_price,
            'category_slug' => $product_category_slug,
            'post_id' => $booking_data['flight_id'],
        );
        $product_id = trav_woo_create_product( $product_data );
        
        trav_woo_product_add_to_cart( $product_id );
    }
}

/*
 * get woocommerce currency
 */
if ( ! function_exists( 'trav_woo_get_def_currency' ) ) {
    function trav_woo_get_def_currency() {
        return get_woocommerce_currency();
    }
}

/*
 * get woocommerce cart page url
 */
if ( ! function_exists( 'trav_woo_get_cart_page_url' ) ) {
    function trav_woo_get_cart_page_url() {
        $cart_page_url = false;

        if ( function_exists( 'wc_get_page_id' ) ) {
            $cart_page_id = wc_get_page_id( 'cart' );
            $cart_page_id = trav_post_clang_id( $cart_page_id );
            $cart_page_url = get_permalink($cart_page_id);
        }

        return $cart_page_url;
    }
}

/*
 * get checkout prefill value that user added in booking form.
 */
if ( ! function_exists( 'trav_woo_checkout_get_def_value' ) ) {
    function trav_woo_checkout_get_def_value( $value, $input ) {

        global $wpdb, $woocommerce;
        $billing_booking_fields = array( 
            'billing_first_name' => 'first_name',
            'billing_last_name' => 'last_name',
            'billing_address_1' => 'address',
            'billing_city' => 'city',
            'billing_phone' => 'phone',
            'billing_email' => 'email',
            'billing_postcode' => 'zip',
        );

        if ( array_key_exists( $input, $billing_booking_fields ) ) {
            $cart = $woocommerce->cart->get_cart();
            if ( count( $cart ) > 0) {
                $first_product = reset( $cart );
                $first_product_id = $first_product['product_id'];
                $first_product_data = $first_product['data'];
                $booking_data = array();

                $attributes = $first_product_data->get_attributes();
                if ( isset( $attributes['travelo-booking-id'] ) || isset( $attributes['trav-booking-no'] ) ) {
                    if ( isset( $attributes['trav-booking-no'] ) ) { 
                        $booking_no = $attributes['trav-booking-no']['value'];
                        $pin_code = $attributes['trav-pin-code']['value'];
                    } else if ( isset( $attributes['travelo-booking-id'] ) ) { 
                        $booking_no = $attributes['travelo-booking-id']['value'];
                        $pin_code = $attributes['travelo-pin-code']['value'];
                    }

                    $term_tables = array(
                            'accommodation' => TRAV_ACCOMMODATION_BOOKINGS_TABLE,
                            'tour' => TRAV_TOUR_BOOKINGS_TABLE,
                            'car' => TRAV_CAR_BOOKINGS_TABLE,
                            'cruise' => TRAV_CRUISE_BOOKINGS_TABLE,
                            'flight' => TRAV_FLIGHT_BOOKINGS_TABLE,
                        );
                    foreach ( $term_tables as $term_name => $booking_table_name ) {
                        if ( has_term( $term_name, 'product_cat', $first_product_id ) ) {
                            if ( $booking_data = $wpdb->get_row( 'SELECT * FROM ' . $booking_table_name . ' WHERE booking_no="' . esc_sql( $booking_no ) . '" AND pin_code="' . esc_sql( $pin_code ) . '"', ARRAY_A ) ) {
                                break;
                            }
                        }
                    }

                    if ( ! empty( $booking_data ) && ! empty( $booking_data[$billing_booking_fields[$input]] ) ) {
                        $value = $booking_data[$billing_booking_fields[$input]];
                    }

                }
            }
        }

        return $value;
    }
}

/*
 * disable direct access to product page templates.
 */
if ( ! function_exists( 'trav_woo_disable_template_access' ) ) {
    function trav_woo_disable_template_access( $template ) {
        if ( ( is_single() && get_post_type() == 'product' ) // product detail page
                || is_tax( 'product_cat' ) // product category archive page
                || is_tax( 'product_tag' ) // product tag archive page
                || is_post_type_archive( 'product' ) // product post type archive page
                || ( function_exists( 'wc_get_page_id' ) && is_page( wc_get_page_id( 'shop' ) ) ) ) // shop page
        {
            return locate_template( '404.php' );
        }
        return $template;
    }
}

/*
 * after woocommerce payment perform remaining tasks such as sending email & update booking.
 */
if ( ! function_exists( 'trav_woo_process_payment' ) ) {
    function trav_woo_process_payment( $order_id, $prev_status, $current_status, $original_order ) {
        global $wpdb, $woocommerce;

        if ( empty( $original_order ) ) { 
            $order = new WC_Order( $order_id );
        } else { 
            $order = $original_order;
        }

        $items = $order->get_items();
        $items_info = array();
        $term_tables = array(
            'accommodation' => TRAV_ACCOMMODATION_BOOKINGS_TABLE,
            'tour' => TRAV_TOUR_BOOKINGS_TABLE,
            'car' => TRAV_CAR_BOOKINGS_TABLE,
            'cruise' => TRAV_CRUISE_BOOKINGS_TABLE,
            'flight' => TRAV_FLIGHT_BOOKINGS_TABLE,
        );

        foreach ( $items as $item ) {
            $product_name = $item['name'];
            $product_id = $item['product_id'];
            $post_type = '';

            $_pf = new WC_Product_Factory();  
            $_product = $_pf->get_product($product_id);

            $attributes = $_product->get_attributes();
            if ( isset( $attributes['travelo-booking-id'] ) || isset( $attributes['trav-booking-no'] ) ) {
                if ( isset( $attributes['trav-booking-no'] ) ) { 
                    $booking_no = $attributes['trav-booking-no']['value'];
                    $pin_code = $attributes['trav-pin-code']['value'];
                } else if ( isset( $attributes['travelo-booking-id'] ) ) { 
                    $booking_no = $attributes['travelo-booking-id']['value'];
                    $pin_code = $attributes['travelo-pin-code']['value'];
                }

                // check product_category of product ( post_type of property )
                foreach ( $term_tables as $term_name => $booking_table_name ) {
                    if ( has_term( $term_name, 'product_cat', $product_id ) ) {
                        $post_type = $term_name;
                        break;
                    }
                }

                if ( ! empty( $post_type ) ) {
                    $new_data = array(
                        'woo_order_id' => $order_id 
                    );

                    if ( ! empty( $original_order ) ) { 
                        if ( 'completed' == $current_status ) { 
                            $new_data['deposit_paid'] = 1;
                            $new_data['status'] = 2;
                        } else if ( 'processing' == $current_status || 'pending' == $current_status ) { 
                            $new_data['deposit_paid'] = 1;
                            $new_data['status'] = 1;
                        } else if ( 'on-hold' == $current_status || 'failed' == $current_status || 'cancelled' == $current_status ) { 
                            $new_data['deposit_paid'] = 0;
                            $new_data['status'] = 0;
                        }

                        $mail_type = 'update';

                        if ( 'cancelled' == $current_status ) { 
                            $mail_type = 'cancel';
                        }
                    } else { 
                        $payment_method = get_post_meta( $order_id, '_payment_method', true );

                        if ( 'cheque' != $payment_method && 'cod' != $payment_method ) { 
                            $new_data['deposit_paid'] = 1;
                        }
                        $new_data['status'] = 1;

                        $mail_type = 'new';
                    }

                    $update_status = $wpdb->update( $term_tables[ $post_type ], $new_data, array( 'booking_no' => $booking_no, 'pin_code' => $pin_code ) );

                    if ( $update_status === false ) {
                        do_action( 'trav_woo_update_booking_error', $booking_no, $pin_code, $mail_type );
                    } elseif ( empty( $update_status ) ) {
                        do_action( 'trav_woo_update_booking_no_row', $booking_no, $pin_code, $mail_type );
                    } else {
                        //if ( in_array( $current_status, array( 'completed', 'processing', 'pending' ) ) {
                            do_action( 'trav_woo_update_booking_success', $booking_no, $pin_code, $mail_type );
                        //}
                    }

                    $items_info[] = array(
                        'booking_no' => $booking_no,
                        'pin_code' => $pin_code,
                        'post_type' => $post_type,
                    );
                }
            }
        }

        if ( ! empty( $items_info ) ) {
            do_action( 'trav_woo_payment_success', $items_info );
        }
    }
}

/*
 * on thankyou page after process payment
 */
if ( ! function_exists( 'trav_woo_process_payment_on_thankyou' ) ) { 
    function trav_woo_process_payment_on_thankyou( $order_id ) { 
        trav_woo_process_payment( $order_id, null, null, null );
    }
}

/*
 * disable generated product link and set it property link
 */
if ( ! function_exists( 'trav_woo_update_product_link' ) ) {
    function trav_woo_update_product_link( $post_link, $post ) {
        if ( $post->post_type === 'product' ) {
            $trav_post_id = get_post_meta( $post->ID, '_trav_post_id', true );
            if ( ! empty( $trav_post_id ) ) {
                $post_link = get_permalink( $trav_post_id );
            }
        }
        return $post_link;
    }
}

/*
 * woocommerce template before cart
 */
if ( ! function_exists( 'trav_woo_before_cart' ) ) {
    function trav_woo_before_cart() {
        echo '<div class="cart-wrapper">';
    }
}

/*
 * woocommerce template after cart
 */
if ( ! function_exists( 'trav_woo_after_cart' ) ) {
    function trav_woo_after_cart() {
        echo '</div>';
    }
}

/*
 * redirect to shop
 */
if ( ! function_exists( 'trav_woo_return_to_shop_redirect' ) ) {
    function trav_woo_return_to_shop_redirect() {
        return esc_url( home_url() );
    }
}

/*
 * update currency description in theme options panel
 */
if ( ! function_exists( 'trav_woo_options_def_currency_desc' ) ) {
    function trav_woo_options_def_currency_desc( $desc ) {
        $trav_options = get_option('travelo');
        if ( ! empty( $trav_options['acc_pay_woocommerce'] ) ) {
            $desc = __('You enabled WooCommerce Integration Option, so this field will be ignored. Please set default currency on ', 'trav') . '<a href="' . admin_url( 'admin.php?page=wc-settings' ) . '">' . __('woocommerce settings panel', 'trav') . '</a>';
        }
        return $desc;
    }
}

/*
 * add woocommerce settings panel to theme options
 */
if ( ! function_exists( 'trav_woo_options_payment_addon_settings' ) ) {
    function trav_woo_options_payment_addon_settings( $options ) {
        $options[] = array(
            'title' => __('Enable WooCommerce Payment', 'trav'),
            'subtitle' => __('Enable Payment by Woocommerce plugin in booking.', 'trav'),
            'id' => 'acc_pay_woocommerce',
            'default' => false,
            'type' => 'switch'
        );

        return $options;
    }
}

/*
 * check if woocommerce payment is enabled
 */
if ( ! function_exists( 'trav_woo_is_woo_enabled' ) ) {
    function trav_woo_is_woo_enabled() {
        $trav_options = get_option('travelo');

        if ( ! empty( $trav_options['acc_pay_woocommerce'] ) ) {
            return true;
        } else {
            return false;
        }
    }
}

/*
 * payment enabled status filter
 */
if ( ! function_exists( 'trav_woo_is_payment_enabled' ) ) {
    function trav_woo_is_payment_enabled( $status ) {
        return $status || trav_woo_is_woo_enabled();
    }
}

/* */
if ( ! function_exists( 'trav_woo_register_sidebar' ) ) { 
    function trav_woo_register_sidebar() { 
        $args = array(
            'name'          => __( 'Shop Sidebar', 'trav' ),
            'id'            => 'shop-sidebar',
            'description'   => '',
            'class'         => '',
            'before_widget' => '<div id="%1$s" class="widget travelo-box %2$s">',
            'after_widget'  => '</div>',
            'before_title'  => '<h4 class="widgettitle">',
            'after_title'   => '<span class="toggle-widget"></span></h4>' 
        );
        register_sidebar( $args );

        $args = array(
            'name'          => __( 'Single Product Sidebar', 'trav' ),
            'id'            => 'product-sidebar',
            'description'   => '',
            'class'         => '',
            'before_widget' => '<div id="%1$s" class="widget travelo-box %2$s">',
            'after_widget'  => '</div>',
            'before_title'  => '<h4 class="widgettitle">',
            'after_title'   => '</h4>' 
        );
        register_sidebar( $args );
    }
}

/* Remove 'Accommodation', 'Tour', 'Car', 'Cruise' categories from shop loop */
if ( ! function_exists( 'trav_shop_filter_certain_cat' ) ) { 
    function trav_shop_filter_certain_cat( $query ) { 
        if ( !is_admin() && is_post_type_archive( 'product' ) && $query->is_main_query() ) {
            $query->set('tax_query', array(
                array(
                    'taxonomy'  => 'product_cat',
                    'field'     => 'slug',
                    'terms'     => array( 'accommodation', 'tour', 'car', 'cruise', 'flight' ),
                    'operator'  => 'NOT IN'
                    )
                )
            );   
        }
    }
}

/* Insert the opening anchor tag for products in the loop. */
if ( ! function_exists( 'trav_woo_template_loop_product_link_open' ) ) { 
    function trav_woo_template_loop_product_link_open() {
        echo '<figure><a href="' . get_the_permalink() . '" class="woocommerce-LoopProduct-link hover-effect">';
    }
}

/* Insert the closing anchor tag for products in the loop. */
if ( ! function_exists( 'trav_woo_template_loop_product_link_close' ) ) { 
    function trav_woo_template_loop_product_link_close() {
        echo '</a></figure>';
    }
}

/* Insert the opening div tag for product details in the loop. */
if ( ! function_exists( 'trav_woo_template_loop_product_detail_open' ) ) { 
    function trav_woo_template_loop_product_detail_open() {
        echo '<div class="details">';
    }
}

/* Insert the closing div tag for product details in the loop. */
if ( ! function_exists( 'trav_woo_template_loop_product_detail_close' ) ) { 
    function trav_woo_template_loop_product_detail_close() {
        echo '</div>';
    }
}

/* Product title for product in the loop */
if ( ! function_exists( 'trav_woo_template_loop_product_title' ) ) { 
    function trav_woo_template_loop_product_title() {
        $html = '<h4 class="box-title"><a href="' . get_the_permalink() . '" title="' . get_the_title() . '">' . get_the_title() . '</a></h4>';
        echo $html;
    }
}

/* Show product description in the loop */
if ( ! function_exists( 'trav_woo_template_loop_description' ) ) { 
    function trav_woo_template_loop_description() { 
        global $post;

        if ( ! $post->post_excerpt ) { 
            return;
        }

        $html = '<div class="description">';
        $html .= force_balance_tags( apply_filters( 'woocommerce_short_description', $post->post_excerpt ) );
        $html .= '</div>';

        echo $html;
    }
}

/* Change product counts on Shop page */
if ( ! function_exists( 'trav_loop_shop_per_page' ) ) { 
    function trav_loop_shop_per_page( $cols ) { 
        global $trav_options;

        if ( ! empty( $trav_options['shop_products_per_page'] ) ) { 
            return $trav_options['shop_products_per_page'];
        }

        return $cols;
    }
}

if ( ! function_exists( 'trav_custom_product_categories_widget_args' ) ) { 
    function trav_custom_product_categories_widget_args( $args ) { 
        $acc = get_term_by( 'slug', 'accommodation', 'product_cat' );
        $tour = get_term_by( 'slug', 'tour', 'product_cat' );
        $car = get_term_by( 'slug', 'car', 'product_cat' );
        $cruise = get_term_by( 'slug', 'cruise', 'product_cat' );
        $flight = get_term_by( 'slug', 'flight', 'product_cat' );

        $args['exclude'] = array( $acc->term_id, $tour->term_id, $car->term_id, $cruise->term_id, $flight->term_id );

        return $args;
    }
}

if ( ! function_exists( 'trav_custom_products_widget_query_args' ) ) { 
    function trav_custom_products_widget_query_args( $args ) { 
        if ( isset( $args['tax_query'] ) ) { 
            $args['tax_query'][] = array(
                'taxonomy'  => 'product_cat',
                'field'     => 'slug',
                'terms'     => array( 'accommodation', 'tour', 'car', 'cruise', 'flight' ),
                'operator'  => 'NOT IN'
            );
        } else { 
            $args['tax_query'] = array(
                'taxonomy'  => 'product_cat',
                'field'     => 'slug',
                'terms'     => array( 'accommodation', 'tour', 'car', 'cruise', 'flight' ),
                'operator'  => 'NOT IN'
            );
        }

        return $args;
    }
}

if ( ! function_exists( 'trav_woocommerce_header_add_to_cart_fragment' ) ) { 
    function trav_woocommerce_header_add_to_cart_fragment( $fragments ) { 
        ob_start();
        ?>

        <a href="<?php echo wc_get_cart_url() ?>" class="cart-contents" title="<?php _e('View Cart', 'trav') ?>"> 
            <i class="soap-icon-shopping"></i>
            <div class="item-count"><?php echo WC()->cart->get_cart_contents_count(); ?></div>
        </a>
        
        <?php

        $fragments['a.cart-contents'] = ob_get_clean();

        return $fragments;
    }
}

if ( class_exists( 'WooCommerce' ) ) {
    remove_action( 'woocommerce_before_main_content', 'woocommerce_output_content_wrapper', 10 );
    remove_action( 'woocommerce_before_main_content', 'woocommerce_breadcrumb', 20 );
    remove_action( 'woocommerce_after_main_content', 'woocommerce_output_content_wrapper_end', 10 );
    remove_action( 'woocommerce_before_shop_loop', 'woocommerce_result_count', 20 );
    remove_action( 'woocommerce_before_shop_loop_item', 'woocommerce_template_loop_product_link_open', 10 );
    remove_action( 'woocommerce_after_shop_loop_item', 'woocommerce_template_loop_product_link_close', 5 );
    remove_action( 'woocommerce_before_shop_loop_item_title', 'woocommerce_show_product_loop_sale_flash', 10 );
    remove_action( 'woocommerce_shop_loop_item_title', 'woocommerce_template_loop_product_title', 10 );
    remove_action( 'woocommerce_after_shop_loop_item_title', 'woocommerce_template_loop_rating', 5 );
    remove_action( 'woocommerce_product_thumbnails', 'woocommerce_show_product_thumbnails', 20 );
    remove_action( 'woocommerce_single_product_summary', 'woocommerce_template_single_price', 10 );
    remove_action( 'woocommerce_single_product_summary', 'woocommerce_template_single_excerpt', 20 );


    // action list
    add_action( 'init', 'trav_woo_init' );
    add_action( 'widgets_init', 'trav_woo_register_sidebar', 20 );
    add_action( 'trav_woo_update_booking_success', 'trav_acc_send_confirmation_email', 10, 3 );
    add_action( 'trav_woo_update_booking_success', 'trav_tour_send_confirmation_email', 10, 3 );
    add_action( 'trav_woo_update_booking_success', 'trav_car_send_confirmation_email', 10, 3 );
    add_action( 'trav_woo_update_booking_success', 'trav_cruise_send_confirmation_email', 10, 3 );
    add_action( 'trav_woo_update_booking_success', 'trav_flight_send_confirmation_email', 10, 3 );
    
    add_action( 'pre_get_posts','trav_shop_filter_certain_cat' );

    add_action( 'woocommerce_sidebar', 'woocommerce_result_count', 5 );
    add_action( 'woocommerce_before_shop_loop_item', 'woocommerce_show_product_loop_sale_flash', 5 );
    add_action( 'woocommerce_before_shop_loop_item', 'trav_woo_template_loop_product_link_open', 10 );
    add_action( 'woocommerce_before_shop_loop_item_title', 'trav_woo_template_loop_product_link_close', 20 );
    add_action( 'woocommerce_before_shop_loop_item_title', 'trav_woo_template_loop_product_detail_open', 30 );
    add_action( 'woocommerce_after_shop_loop_item', 'trav_woo_template_loop_product_detail_close', 30 );
    add_action( 'woocommerce_shop_loop_item_title', 'trav_woo_template_loop_product_title', 10 );
    add_action( 'woocommerce_after_shop_loop_item_title', 'trav_woo_template_loop_description', 5 );
    add_action( 'woocommerce_after_shop_loop_item_title', 'woocommerce_template_loop_rating', 20 );
    add_action( 'woocommerce_single_product_summary', 'woocommerce_template_single_price', 9 );
    add_action( 'woocommerce_after_add_to_cart_quantity', 'woocommerce_template_single_excerpt', 9 );


    // filter list
    add_filter( 'trav_options_payment_addon_settings', 'trav_woo_options_payment_addon_settings' ); // add woo options panel to theme options
    add_filter( 'trav_is_payment_enabled', 'trav_woo_is_payment_enabled', 20 ); // update payment_enabled 
    add_filter( 'trav_options_def_currency_desc', 'trav_woo_options_def_currency_desc' ); // update content in theme options panel
    add_filter( 'loop_shop_per_page', 'trav_loop_shop_per_page', 20 );
    add_filter( 'woocommerce_product_categories_widget_args', 'trav_custom_product_categories_widget_args' );
    add_filter( 'wc_product_dropdown_categories_get_terms_args', 'trav_custom_product_categories_widget_args' );
    add_filter( 'woocommerce_products_widget_query_args', 'trav_custom_products_widget_query_args' );
    add_filter( 'woocommerce_add_to_cart_fragments', 'trav_woocommerce_header_add_to_cart_fragment' );
}