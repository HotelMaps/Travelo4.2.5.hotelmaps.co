<?php
/*
 * flight Booking Form
 */
if ( ! defined( 'ABSPATH' ) ) {
    exit; // exit if accessed directly
}

global $trav_options, $def_currency;
global $trav_booking_page_data, $is_payment_enabled, $language_count;

do_action( 'trav_flight_booking_before' ); // init $trav_booking_page_data
if ( empty( $trav_booking_page_data ) ) {
    return;
}

$booking_data = $trav_booking_page_data['booking_data'];
$flight_date = $booking_data['flight_date'];
$adults = $booking_data['adults'];
$transaction_id = $trav_booking_page_data['transaction_id'];
$is_payment_enabled = $trav_booking_page_data['is_payment_enabled'];
$tax_rate = $trav_booking_page_data['tax_rate'];
$tax = $trav_booking_page_data['tax'];

$action_url = $trav_booking_page_data['flight_book_conf_url'];
$flight_id = $booking_data['flight_id'];
$action = 'flight_submit_booking';

$departure_time = get_post_meta( $flight_id, 'trav_flight_departure_time', true );
$arrival_time = get_post_meta( $flight_id, 'trav_flight_arrival_time', true );
$duration = get_post_meta( $flight_id, 'trav_flight_duration', true );

$air_line = wp_get_post_terms( $flight_id, 'air_line', array( "fields" => "all" ) );
if ( ! empty( $air_line ) ) {
    $air_line = $air_line[0];
    $air_line->img = get_tax_meta( $air_line->term_id, 'air_line_image', true );
}

$flight_stop = wp_get_post_terms( $flight_id, 'flight_stop', array( "fields" => "all" ) );
if ( ! empty( $flight_stop ) ) {
    $flight_stop = $flight_stop[0];
}

$flight_type = wp_get_post_terms( $flight_id, 'flight_type', array( "fields" => "all" ) );
if ( ! empty( $flight_type ) ) {
    $flight_type = $flight_type[0];
}
$location_from = wp_get_post_terms( $flight_id, 'flight_location', array( "fields" => "all" ) );
if ( ! empty( $location_from ) ) {
    $location_from = $location_from[0];
}
$location_to = get_post_meta( $flight_id, 'trav_flight_location_to', true );
if ( ! empty( $location_to ) ) {
    $location_to = get_term( $location_to, 'flight_location' );
}

if ( $booking_data['trip_type'] == 'round_trip' ) {
    $return_flight_date = $booking_data['return_flight_date'];
    $return_flight_id = $booking_data['return_flight_id'];

    $return_departure_time = get_post_meta( $return_flight_id, 'trav_flight_departure_time', true );
    $return_arrival_time = get_post_meta( $return_flight_id, 'trav_flight_arrival_time', true );
    $return_duration = get_post_meta( $return_flight_id, 'trav_flight_duration', true );

    $return_air_line = wp_get_post_terms( $return_flight_id, 'air_line', array( "fields" => "all" ) );
    if ( ! empty( $return_air_line ) ) {
        $return_air_line = $return_air_line[0];
        $return_air_line->img = get_tax_meta( $return_air_line->term_id, 'air_line_image', true );
    }

    $return_flight_stop = wp_get_post_terms( $return_flight_id, 'flight_stop', array( "fields" => "all" ) );
    if ( ! empty( $return_flight_stop ) ) {
        $return_flight_stop = $return_flight_stop[0];
    }

    $return_flight_type = wp_get_post_terms( $return_flight_id, 'flight_type', array( "fields" => "all" ) );
    if ( ! empty( $return_flight_type ) ) {
        $return_flight_type = $return_flight_type[0];
    }
    $return_location_from = wp_get_post_terms( $return_flight_id, 'flight_location', array( "fields" => "all" ) );
    if ( ! empty( $return_location_from ) ) {
        $return_location_from = $return_location_from[0];
    }
    $return_location_to = get_post_meta( $return_flight_id, 'trav_flight_location_to', true );
    if ( ! empty( $return_location_to ) ) {
        $return_location_to = get_term( $return_location_to, 'flight_location' );
    }
}
?>

<div class="row">
    <div class="col-sms-6 col-sm-8 col-md-9">
        <div class="booking-section travelo-box">

            <?php do_action( 'trav_flight_booking_form_before', $booking_data ); ?>
            <form class="booking-form" method="POST" action="<?php echo esc_url( $action_url ); ?>">
                <input type="hidden" name="action" value="<?php echo esc_attr( $action ); ?>">
                <input type="hidden" name="transaction_id" value='<?php echo esc_attr( $transaction_id ) ?>'>
                <?php wp_nonce_field( 'post-' . $flight_id, '_wpnonce', false ); ?>

                <?php trav_get_template( 'booking-form.php', '/templates/booking/' ); ?>
            </form>
            <?php do_action( 'trav_flight_booking_form_after', $booking_data ); ?>

        </div>
    </div>
    <div class="sidebar col-sms-6 col-sm-4 col-md-3">
        <div class="booking-details travelo-box">

            <?php do_action( 'trav_flight_booking_sidebar_before', $booking_data ); ?>

            <h4><?php _e( 'Booking Details', 'trav'); ?></h4>
            <article class="flight-booking-details">
                <figure class="clearfix">
                    <a href="#" class="">
                        <?php if ( ! empty( $air_line->img['src'] ) ) : ?>
                            <img class="" alt="" src="<?php echo $air_line->img['src']; ?>">
                        <?php endif; ?>
                    </a>
                    <div class="travel-title">
                        <h5 class="box-title">
                            <a href="#"><?php echo esc_html( get_the_title( $booking_data['flight_id'] ) );?></a>                            
                        </h5>
                    </div>
                </figure>
                <div class="details">
                    <div class="constant-column-3 timing clearfix">
                        <div class="check-in">
                            <label><?php echo esc_html__( 'Take off', 'trav' ); ?></label>
                            <span><?php echo date( 'M d, Y', trav_strtotime( $flight_date ) ); ?><br /><?php echo date( 'h:i A', trav_strtotime( $departure_time ) ); ?></span>
                        </div>
                        <div class="duration text-center">
                            <i class="soap-icon-clock"></i>
                            <span><?php echo date( 'G', trav_strtotime( $duration ) ) . esc_html__( 'H' ); ?><?php echo ( date( 'i', trav_strtotime( $duration ) ) != '00' ) ? ', ' . date( 'i', trav_strtotime( $duration ) ) . esc_html__( 'M', 'trav' ) : ''; ?></span>
                        </div>
                        <div class="check-out">
                            <label><?php echo esc_html__( 'landing', 'trav' ); ?></label>
                            <span><?php echo date( 'M d, Y', trav_strtotime( $flight_date . ' ' . $departure_time  ) + trav_strtotime( $flight_date . ' ' . $duration ) - trav_strtotime( $flight_date ) ); ?><br /><?php echo date( 'h:i A', trav_strtotime( $arrival_time ) ); ?></span>
                        </div>
                    </div>
                </div>
            </article>

            <?php if ( $booking_data['trip_type'] == 'round_trip' ) : ?>
                <article class="flight-booking-details">
                    <figure class="clearfix">
                        <a href="#" class="">
                            <?php if ( ! empty( $return_air_line->img['src'] ) ) : ?>
                                <img class="" alt="" src="<?php echo $return_air_line->img['src']; ?>">
                            <?php endif; ?>
                        </a>
                        <div class="travel-title">
                            <h5 class="box-title">
                                <a href="#"><?php echo esc_html( get_the_title( $booking_data['return_flight_id'] ) );?></a>                            
                            </h5>
                        </div>
                    </figure>
                    <div class="details">
                        <div class="constant-column-3 timing clearfix">
                            <div class="check-in">
                                <label><?php echo esc_html__( 'Take off', 'trav' ); ?></label>
                                <span><?php echo date( 'M d, Y', trav_strtotime( $return_flight_date ) ); ?><br /><?php echo date( 'h:i A', trav_strtotime( $return_departure_time ) ); ?></span>
                            </div>
                            <div class="duration text-center">
                                <i class="soap-icon-clock"></i>
                                <span><?php echo date( 'G', trav_strtotime( $return_duration ) ) . esc_html__( 'H' ); ?><?php echo ( date( 'i', trav_strtotime( $return_duration ) ) != '00' ) ? ', ' . date( 'i', trav_strtotime( $return_duration ) ) . esc_html__( 'M', 'trav' ) : ''; ?></span>
                            </div>
                            <div class="check-out">
                                <label><?php echo esc_html__( 'landing', 'trav' ); ?></label>
                                <span><?php echo date( 'M d, Y', trav_strtotime( $return_flight_date . ' ' . $return_departure_time  ) + trav_strtotime( $return_flight_date . ' ' . $return_duration ) - trav_strtotime( $return_flight_date ) ); ?><br /><?php echo date( 'h:i A', trav_strtotime( $return_arrival_time ) ); ?></span>
                            </div>
                        </div>
                    </div>
                </article>
            <?php endif; ?>

            <h4><?php _e( 'Other Details', 'trav' ); ?></h4>
            <dl class="other-details">
                <dt class="feature"><?php _e( 'Adults', 'trav' ); ?>:</dt><dd class="value"><?php echo ( ! empty( $adults ) ) ? esc_html( $adults ) : ''; ?></dd>
                <dt class="feature"><?php _e( 'From', 'trav' ); ?>:</dt><dd class="value"><?php echo ( ! empty( $location_from->name ) ) ? esc_html( $location_from->name ) : ''; ?></dd>
                <dt class="feature"><?php _e( 'To', 'trav' ); ?>:</dt><dd class="value"><?php echo ( ! empty( $location_to->name ) ) ? esc_html( $location_to->name ) : ''; ?></dd>
             
                <?php if ( ! empty( $tax ) ) : ?>
                    <dt class="feature"><?php echo __( 'taxes and fees', 'trav') ?>:</dt><dd class="value"><?php echo esc_html( trav_get_price_field( $tax ) ) ?></dd>
                <?php endif; ?>
                <?php if ( $is_payment_enabled ) : ?>
                        <dt class="feature"><?php _e( 'Security Deposit', 'trav' ); ?>:</dt><dd class="value"><?php echo esc_html( trav_get_price_field( $booking_data['deposit_price'], $booking_data['currency_code'], 0 ) ) ?></dd>
                <?php endif; ?>

                <dt class="total-price"><?php _e( 'Total Price', 'trav'); ?></dt><dd class="total-price-value"><?php echo esc_html( trav_get_price_field( $booking_data['total_price'] ) ) ?></dd>
            </dl>

            <?php do_action( 'trav_flight_booking_sidebar_after', $booking_data ); ?>

        </div>

        <?php generated_dynamic_sidebar(); ?>

    </div>
</div>

<script>
    jQuery(document).ready( function(tjq) {
        var validation_rules = {
                first_name: { required: true },
                last_name: { required: true },
                email: { required: true, email: true },
                email2: { required: true, equalTo: 'input[name="email"]' },
                phone: { required: true },
                address: { required: true },
                city: { required: true },
                zip: { required: true },
            };

        if ( tjq('input[name="security_code"]').length ) {
            validation_rules['security_code'] = { required: true };
        }

        //validation form
        tjq('.booking-form').validate({
            rules: validation_rules,
            submitHandler: function (form) {
                if ( tjq('input[name="agree"]').length ) {
                    if ( tjq('input[name="agree"]:checked').length == 0 ) {
                        alert("<?php echo esc_js( __( 'Agree to terms&conditions is required' ,'trav' ) ); ?>");
                        return false;
                    }
                }

                var booking_data = tjq('.booking-form').serialize();
                tjq.ajax({
                    type: "POST",
                    url: ajaxurl,
                    data: booking_data,
                    success: function ( response ) {
                        if ( response.success == 1 ) {
                            if ( response.result.payment == 'woocommerce' ) {
                                <?php if ( function_exists( 'trav_woo_get_cart_page_url' ) && trav_woo_get_cart_page_url() ) { ?>
                                    window.location.href = '<?php echo esc_js( trav_woo_get_cart_page_url() ); ?>';
                                <?php } else { ?>
                                    trav_show_modal( 0, "<?php echo esc_js( __( 'Please set woocommerce cart page', 'trav' ) ); ?>", '' );
                                <?php } ?>
                            } else {
                                if ( response.result.payment == 'paypal' ) {
                                    tjq('.confirm-booking-btn').before('<div class="alert alert-success"><?php echo esc_js( __( 'You will be redirected to paypal.', 'trav' ) ) ?><span class="close"></span></div>');
                                }

                                var confirm_url = tjq('.booking-form').attr('action');

                                if ( confirm_url.indexOf('?') > -1 ) {
                                    confirm_url = confirm_url + '&';
                                } else {
                                    confirm_url = confirm_url + '?';
                                }

                                confirm_url = confirm_url + 'booking_no=' + response.result.booking_no + '&pin_code=' + response.result.pin_code + '&transaction_id=' + response.result.transaction_id + '&message=1';
                                <?php if ( defined('ICL_LANGUAGE_CODE') && ( $language_count > 1 ) && ( trav_get_default_language() != ICL_LANGUAGE_CODE ) ) { ?>
                                    confirm_url = confirm_url + '&lang=<?php echo esc_attr( ICL_LANGUAGE_CODE ) ?>';
                                <?php } ?>

                                tjq('.confirm-booking-btn').hide();

                                setTimeout( function(){ 
                                    tjq('.opacity-ajax-overlay').show(); 
                                }, 500 );

                                window.location.href = confirm_url;
                            }
                        } else if ( response.success == -1 ) {
                            alert( response.result );
                            setTimeout( function(){ tjq('.opacity-ajax-overlay').show(); }, 500 );
                        } else {
                            // console.log( response );
                            trav_show_modal( 0, response.result, '' );
                        }
                    }
                });

                return false;
            }
        });

        tjq('.show-price-detail').click( function(e){
            e.preventDefault();

            tjq('.price-details').toggle();
            if (tjq('.price-details').is(':visible')) {
                tjq(this).html( tjq(this).data('hide-desc') );
            } else {
                tjq(this).html( tjq(this).data('show-desc') );
            }

            return false;
        });
    });
</script>