<?php
/*
 * Accommodation Booking Form
 */
if ( ! defined( 'ABSPATH' ) ) {
	exit; // exit if accessed directly
}

global $trav_options, $def_currency;
global $trav_booking_page_data, $is_payment_enabled, $language_count;

do_action( 'trav_acc_booking_before' ); // init $trav_booking_page_data
if ( empty( $trav_booking_page_data ) ) {
	return;
}
$booking_data = $trav_booking_page_data['booking_data'];
$transaction_id = $trav_booking_page_data['transaction_id'];
$review = $trav_booking_page_data['review'];
$acc_url = $trav_booking_page_data['acc_url'];
$edit_url = $trav_booking_page_data['edit_url'];
$room_price_data = $trav_booking_page_data['room_price_data'];
$is_payment_enabled = $trav_booking_page_data['is_payment_enabled'];
$tax_rate = $trav_booking_page_data['tax_rate'];
$tax = $trav_booking_page_data['tax'];
$discount_rate = $booking_data['discount_rate'];

$action_url = $trav_booking_page_data['acc_book_conf_url'];
$post_id = $booking_data['room_type_id'];
$action = 'acc_submit_booking';
?>
<div class="checkout-page-inner">
	<div class="container">
		<div class="row">

			<div class="head-part style3">
				<h1 class="main-title"><?php echo esc_html__( 'Secure Booking - Only Takes 5 Minutes!', 'trav' ); ?></h1>
				<p class="description"><?php echo esc_html__( 'Act fast! Pricing and availability may change.', 'trav' ); ?></p>
			</div>

			<div class="col-lg-8 main-content-area">
				
				<?php do_action( 'trav_acc_booking_form_before', $booking_data ); ?>

				<form action="<?php echo esc_url( $action_url ); ?>" class="checkout-form-wrapper booking-form">

					<input type="hidden" name="action" value="<?php echo esc_attr( $action ); ?>">
					<input type="hidden" name="transaction_id" value='<?php echo esc_attr( $transaction_id ) ?>'>
					<?php wp_nonce_field( 'post-' . $post_id, '_wpnonce', false ); ?>

					<div class="personal-info-part content-section">
						<div class="section-head-part">
							<div class="part-icon"><i class="travelo-adults"></i></div>
							<div class="part-txt">
								<h2 class="section-title"><?php echo esc_html__( 'Your Personal Information', 'trav' ); ?></h2>
								<p class="description"><span class="bold"><?php echo esc_html__( 'Room', 'trav' ); ?>:</span> <?php echo esc_html( $booking_data['rooms'] . ' ' . get_the_title( $booking_data['room_type_id'] ) ); ?> <?php echo esc_html( $booking_data['adults'] . ' ' . _n( 'Adult', 'Adults', $booking_data['adults'], 'trav' ) ); if ( ! empty( $booking_data['kids'] ) ) echo esc_html( $booking_data['kids'] . ' ' . _n( 'Child', 'Children', $booking_data['kids'], 'trav' ) );?></p>
							</div>
						</div>

						<?php trav_get_template( 'booking-form.php', '/templates/modern/booking/' ); ?>

					</div>
				</form>

				<?php do_action( 'trav_acc_booking_form_after', $booking_data ); ?>
			</div>

			<aside class="col-lg-4 sidebar-content-area sidebar">
				<div class="checkout-sidebar-part single-sidebar-part">
					<?php echo get_the_post_thumbnail( $booking_data['accommodation_id'], 'thumbnail', array( 'class'=>'checkout-featured-img' ) ); ?>

					<h2 class="detail-title"><?php echo get_the_title( $booking_data['accommodation_id'] ); ?></h2>
					<p class="detail-location">
						<i class="fas fa-map-marker-alt"></i>
						<?php 
						$acc_location = get_post_meta( $booking_data['accommodation_id'], 'trav_accommodation_address', true );
						if ( ! empty( $acc_location ) ) {
							echo esc_html( $acc_location );
						}
						?>
					</p>

					<a href="<?php echo esc_url( $edit_url );?>" class="edit-booking-detail border-btn-primary"><?php echo esc_html__( 'Edit Booking Details','trav' ); ?></a>

					<div class="sidebar-section booking-detail-part">
						<div class="widget-head">
							<span class="head-txt"><?php echo esc_html__( 'Booking Details', 'trav' ); ?></span>
							<i class="fas fa-chevron-down"></i>
						</div>

						<div class="widget-content">
							<ul class="booking-detail-list">
								<li class="single-list">
									<i class="fas fa-bed"></i>
									<span class="bold"><?php echo esc_html__( 'Room', 'trav' ); ?>:</span> <?php echo esc_html( $booking_data['rooms'] . ' ' . get_the_title( $booking_data['room_type_id'] ) ); ?>
								</li>
								<li class="single-list">
									<i class="far fa-calendar-alt"></i>
									<span class="bold"><?php echo esc_html__( 'Check In', 'trav' ); ?>:</span> <?php echo date( "D, M j", trav_strtotime( $booking_data['date_from'] ) );?>
								</li>
								<li class="single-list">
									<i class="far fa-calendar-alt"></i>
									<span class="bold"><?php echo esc_html__( 'Check Out', 'trav' ); ?>:</span> <?php echo date( "D, M j", trav_strtotime( $booking_data['date_to'] ) );?>
								</li>
								<li class="single-list">
									<i class="far fa-user"></i>
									<span class="bold"><?php echo esc_html__( 'Guests', 'trav' ); ?>:</span> <?php echo esc_html( $booking_data['adults'] . ' ' . _n( 'Adult', 'Adults', $booking_data['adults'], 'trav' ) ); if ( ! empty( $booking_data['kids'] ) ) echo ',' . esc_html( $booking_data['kids'] . ' ' . _n( 'Child', 'Children', $booking_data['kids'], 'trav' ) );?>
								</li>
								<li class="single-list">
									<i class="far fa-clock"></i>
									<span class="bold"><?php echo esc_html__( 'Duration', 'trav' ); ?>:</span> <?php echo esc_html( trav_get_day_interval( $booking_data['date_from'], $booking_data['date_to'] ) . ' ' . _n( 'Night Stay', 'Nights Stay', trav_get_day_interval( $booking_data['date_from'], $booking_data['date_to'] ), 'trav' ) ); ?>
								</li>
							</ul>
						</div>
					</div>

					<div class="price-summary-part">
						<h3 class="title"><?php echo esc_html__( 'Price Summary', 'trav' ); ?></h3>

						<div class="price-summary-table">
							<div class="detail-summary">
								<div class="single-summary">
									<span class="label"><?php echo esc_html( trav_get_day_interval( $booking_data['date_from'], $booking_data['date_to'] ) . ' ' . _n( 'Night Stay', 'Nights Stay', trav_get_day_interval( $booking_data['date_from'], $booking_data['date_to'] ), 'trav' ) ); ?></span>
									<span class="price"><?php echo esc_html( trav_get_price_field( $room_price_data['total_price'] ) ); ?></span>
								</div>

								<?php if ( is_array( $room_price_data['check_dates'] ) ) : ?>
									<div class="detail-inner-summary">
										<?php
										foreach ( $room_price_data['check_dates'] as $check_date ) {
											?>
											<div class="single-summary">
												<span class="label"><?php echo esc_html( date( "D, M j", trav_strtotime( $check_date ) ) ); ?></span>
												<span class="price"><?php echo esc_html( trav_get_price_field( $room_price_data['prices'][ $check_date ]['total'] ) ); ?></span>
											</div>
											<?php
										}
										?>
									</div>
								<?php endif; ?>

								<?php if ( ! empty( $tax_rate ) ) : ?>
									<div class="single-summary tax-info">
										<span class="label"><?php echo esc_html__( 'Taxes and Fee', 'trav' ); ?></span>
										<span class="price"><?php echo esc_html( trav_get_price_field( $tax ) ); ?></span>
									</div>
								<?php endif; ?>

								<?php if ( ! empty( $discount_rate ) ) : ?>
									<div class="single-summary discount-info">
										<span class="label"><?php echo esc_html__( 'Discount', 'trav' ); ?></span>
										<span class="price"><?php echo esc_html( trav_get_price_field( $booking_data['deposit_price'], $booking_data['currency_code'], 0 ) ); ?></span>
									</div>
								<?php endif; ?>
							</div>
							
							<div class="total-price">
								<span class="label"><?php echo esc_html__( 'Grand Total', 'trav' ); ?></span>
								<span class="price"><?php echo esc_html( trav_get_price_field( $booking_data['total_price'] ) ); ?></span>
							</div>
						</div>
					</div>
				</div>

				<?php generated_dynamic_sidebar(); ?>

			</aside>
		</div>
	</div>
</div>

<script>
    jQuery(document).ready( function($) {
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

        if ( $('input[name="security_code"]').length ) {
            validation_rules['security_code'] = { required: true };
        }

        if ( $('input[name="cc_type"]').length ) {
            validation_rules['cc_type'] = { required: true };
            validation_rules['cc_holder_name'] = { required: true };
            validation_rules['cc_number'] = { required: true };
        }

        //validation form
        $('.booking-form').validate({
            rules: validation_rules,
            submitHandler: function (form) {
                if ( $('input[name="agree"]').length ) {
                    if ( $('input[name="agree"]:checked').length == 0 ) {
                        alert("<?php echo esc_js( __( 'Agree to terms&conditions is required' ,'trav' ) ); ?>");
                        return false;
                    }
                }

                var booking_data = $('.booking-form').serialize();

                $.ajax({
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
                                    $('.confirm-booking-btn').before('<div class="alert alert-success"><?php echo esc_js( __( 'You will be redirected to paypal.', 'trav' ) ) ?><span class="close"></span></div>');
                                }

                                var confirm_url = $('.booking-form').attr('action');

                                if ( confirm_url.indexOf('?') > -1 ) {
                                    confirm_url = confirm_url + '&';
                                } else {
                                    confirm_url = confirm_url + '?';
                                }

                                confirm_url = confirm_url + 'booking_no=' + response.result.booking_no + '&pin_code=' + response.result.pin_code + '&transaction_id=' + response.result.transaction_id + '&message=1';
                                <?php if ( defined('ICL_LANGUAGE_CODE') && ( $language_count > 1 ) && ( trav_get_default_language() != ICL_LANGUAGE_CODE ) ) { ?>
                                    confirm_url = confirm_url + '&lang=<?php echo esc_attr( ICL_LANGUAGE_CODE ) ?>';
                                <?php } ?>

                                $('.confirm-booking-btn').hide();

                                setTimeout( function(){ 
                                    $('.opacity-ajax-overlay').show(); 
                                }, 500 );

                                window.location.href = confirm_url;
                            }
                        } else if ( response.success == -1 ) {
                            alert( response.result );

                            setTimeout( function(){ $('.opacity-ajax-overlay').show(); }, 500 );
                            window.location.href = '<?php echo esc_js( $edit_url ); ?>';
                        } else {
                            // console.log( response );
                            trav_show_modal( 0, response.result, '' );
                        }
                    }
                });

                return false;
            }
        });

        $('.show-price-detail').click( function(e){
            e.preventDefault();

            $('.price-details').toggle();
            if ($('.price-details').is(':visible')) {
                $(this).html( $(this).data('hide-desc') );
            } else {
                $(this).html( $(this).data('show-desc') );
            }

            return false;
        });
    });
</script>