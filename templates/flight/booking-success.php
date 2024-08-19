<?php
/*
 * Accommodation Booking Success Form
 */
if ( ! defined( 'ABSPATH' ) ) {
    exit; // exit if accessed directly
}

global $logo_url, $booking_data, $flight_id, $deposit_rate;

$dt_dd = '<dt>%s:</dt><dd>%s</dd>';
$flight_meta = get_post_meta( $flight_id );
$tax_rate = get_post_meta( $flight_id, 'trav_flight_tax_rate', true );
?>

<div class="row">
    <div class="col-sm-8 col-md-9">
        <div class="booking-information travelo-box">

            <?php do_action( 'trav_flight_conf_form_before', $booking_data ); ?>

            <?php if ( ( isset( $_REQUEST['payment'] ) && ( $_REQUEST['payment'] == 'success' ) ) || ( isset( $_REQUEST['message'] ) && ( $_REQUEST['message'] == 1 ) ) ): ?>
                <h2><?php _e( 'Booking Confirmation', 'trav' ); ?></h2>

                <hr />

                <div class="booking-confirmation clearfix">
                    <i class="soap-icon-recommend icon circle"></i>
                    <div class="message">
                        <h4 class="main-message"><?php _e( 'Thank You. Your Booking Order is Confirmed Now.', 'trav' ); ?></h4>
                        <p><?php _e( 'A confirmation email has been sent to your provided email address.', 'trav' ); ?></p>
                    </div>
                    <!-- <a href="#" class="button btn-small print-button uppercase">print Details</a> -->
                </div>
                <hr />
            <?php endif; ?>

            <h3><?php echo __( 'Check Your Details' , 'trav' ) ?></h3>
            <dl class="term-description">
                <?php
                $booking_detail = array(
                    'booking_no'    => array( 'label' => __('Booking Number', 'trav'), 'pre' => '', 'sur' => '' ),
                    'pin_code'      => array( 'label' => __('Pin Code', 'trav'), 'pre' => '', 'sur' => '' ),
                    'email'         => array( 'label' => __('E-mail address', 'trav'), 'pre' => '', 'sur' => '' ),
                    'flight_date'     => array( 'label' => __('Booking Date', 'trav'), 'pre' => '', 'sur' => '' ),
                    'return_flight_date'     => array( 'label' => __('Return Date', 'trav'), 'pre' => '', 'sur' => '' ),
                    'adults'        => array( 'label' => __('Adults', 'trav'), 'pre' => '', 'sur' => '' ),
                );

                foreach ( $booking_detail as $field => $value ) {
                    if ( empty( $$field ) ) $$field = empty( $booking_data[ $field ] )?'':$booking_data[ $field ];
                    if ( ! empty( $$field ) ) {
                        $content = $value['pre'] . $$field . $value['sur'];
                        echo sprintf( $dt_dd, esc_html( $value['label'] ), esc_html( $content ) );
                    }
                }
                ?>
            </dl>

            <hr />

            <dl class="term-description">

                <?php if ( ! empty( $tax_rate ) ) : ?>
                    <dt><?php printf( __('VAT (%d%%) Included', 'trav' ), $tax_rate ) ?>:</dt><dd><?php echo esc_html( trav_get_price_field( $booking_data['tax'] * $booking_data['exchange_rate'], $booking_data['currency_code'], 0 ) ) ?></dd>
                <?php endif; ?>

                <?php if ( ! ( $booking_data['deposit_price'] == 0 ) ) : ?>
                    <dt><?php _e('Security Deposit ', 'trav' ); ?>:</dt><dd><?php echo esc_html( trav_get_price_field( $booking_data['deposit_price'], $booking_data['currency_code'], 0 ) ) ?></dd>
                <?php endif; ?>
            </dl>
            
            <dl class="term-description" style="font-size: 16px;" >
                <dt style="text-transform: none;"><?php echo __( 'Total Price', 'trav' ) ?></dt><dd><b style="color: #2d3e52;"><?php echo esc_html( trav_get_price_field( $booking_data['total_price'] * $booking_data['exchange_rate'], $booking_data['currency_code'], 0 ) ) ?></b></dd>
            </dl>
            <hr />

            <?php do_action( 'trav_flight_conf_form_after', $booking_data ); ?>
        </div>
    </div>
    <div class="sidebar col-sm-4 col-md-3">
        <?php generated_dynamic_sidebar(); ?>
    </div>
</div>

<style>#ui-datepicker-div {z-index: 10004 !important;} .update-search > div.row > div {margin-bottom: 10px;} .booking-details .other-details dt, .booking-details .other-details dd {padding: 0.2em 0;} .update-search > div.row > div:last-child {margin-bottom:0 !important;} </style>
