<?php
if ( ! defined( 'ABSPATH' ) ) exit; // Exit if accessed directly
/*
 * Accommodation Booking Completed Form
 */
global $booking_data, $acc_id;
?>

<div class="confirm-page-inner">
    <div class="container">

        <div class="head-part style3">
            <h1 class="main-title"><?php echo esc_html__( 'Your Booking Was Completed.', 'trav' ); ?></h1>
            <p class="description"><a href="<?php echo esc_url( get_permalink( $acc_id ) ); ?>" class="button btn-small green"><?php _e( "BOOK AGAIN", "trav" ); ?></a></p>
        </div>

        
        <div class="row">
            <div class="col-lg-8 main-content-area">
                <div class="booking-confirm-detail-part content-section">

                    <?php do_action( 'trav_acc_conf_completed_form_before', $booking_data ); ?>

                    <div class="section-head-part">
                        <div class="part-icon"><i class="travelo-adults"></i></div>
                        <div class="part-txt">
                            <h2 class="section-title"><?php echo esc_html__( 'Your Personal Information', 'trav' ); ?></h2>
                            <p class="description"><span class="bold"><?php echo esc_html__( 'Room', 'trav' ); ?>:</span> <?php echo esc_html( $booking_data['rooms'] . ' ' . get_the_title( $booking_data['room_type_id'] ) ); ?> <?php echo esc_html( $booking_data['adults'] . ' ' . _n( 'Adult', 'Adults', $booking_data['adults'], 'trav' ) ); if ( ! empty( $booking_data['kids'] ) ) echo esc_html( $booking_data['kids'] . ' ' . _n( 'Child', 'Children', $booking_data['kids'], 'trav' ) );?></p>
                        </div>
                    </div>

                    <div class="confirm-content-part">
                        <div class="single-content-section booking-details-info">
                            <h3 class="subsection-title"><?php echo esc_html__( 'Booking Details', 'trav' ); ?></h3>

                            <div class="detail-part-one">
                                <?php
                                $booking_detail = array(
                                    'booking_no'    => array( 'label' => esc_html__( 'Booking Number', 'trav' ), 'pre' => '', 'sur' => '' ),
                                    'pin_code'      => array( 'label' => esc_html__( 'Pin Code', 'trav' ), 'pre' => '', 'sur' => '' ),
                                    'email'         => array( 'label' => esc_html__( 'E-mail address', 'trav' ), 'pre' => '', 'sur' => '' ),
                                    'date_from'     => array( 'label' => esc_html__( 'Check-In', 'trav' ), 'pre' => '', 'sur' => '' ),
                                    'date_to'       => array( 'label' => esc_html__( 'Check-out', 'trav' ), 'pre' => '', 'sur' => '' ),
                                    'rooms'         => array( 'label' => esc_html__( 'Rooms', 'trav' ), 'pre' => '', 'sur' => '' ),
                                    'adults'        => array( 'label' => esc_html__( 'Adults', 'trav' ), 'pre' => '', 'sur' => '' ),
                                );

                                foreach ( $booking_detail as $field => $value ) {
                                    if ( empty( $$field ) ) {
                                        $$field = empty( $booking_data[ $field ] ) ? '' : $booking_data[ $field ];
                                    }

                                    if ( ! empty( $$field ) ) {
                                        $content = $value['pre'] . $$field . $value['sur'];
                                        ?>
                                        <div class="single-info">
                                            <span class="label"><?php echo esc_html( $value['label'] ); ?>:</span>
                                            <span class="val"><?php echo esc_html( $content ); ?></span>
                                        </div>
                                        <?php
                                    }
                                }
                                ?>
                            </div>

                            <div class="detail-part-two">
                                <div class="single-info">
                                    <span class="label"><?php echo esc_html__( 'Room', 'trav' ); ?>:</span>
                                    <span class="val"><?php echo trav_get_price_field( $booking_data['room_price'] * $booking_data['exchange_rate'], $booking_data['currency_code'], 0 ); ?></span>
                                </div>

                                <?php if ( ! empty( $tax_rate ) ) : ?>
                                <div class="single-info">
                                    <span class="label"><?php printf( esc_html__( 'VAT (%d%%) Included', 'trav' ), $tax_rate ); ?>:</span>
                                    <span class="val"><?php echo trav_get_price_field( $booking_data['tax'] * $booking_data['exchange_rate'], $booking_data['currency_code'], 0 ); ?></span>
                                </div>
                                <?php endif; ?>

                                <?php if ( ! empty( $booking_data['discount_rate'] ) && floatval( $booking_data['discount_rate'] ) != 0 ) : ?>
                                <div class="single-info">
                                    <span class="label"><?php echo esc_html__( 'Discount', 'trav' ); ?>:</span>
                                    <span class="val"><?php echo esc_html( $booking_data['discount_rate'] ) . '%'; ?></span>
                                </div>
                                <?php endif; ?>

                                <?php if ( ! ( $booking_data['deposit_price'] == 0 ) ) : ?>
                                <div class="single-info">
                                    <span class="label"><?php printf( __( 'Security Deposit (%d%%)', 'trav' ), $deposit_rate ); ?>:</span>
                                    <span class="val"><?php echo trav_get_price_field( $booking_data['deposit_price'], $booking_data['currency_code'], 0 ); ?></span>
                                </div>
                                <?php endif; ?>
                            </div>

                            <div class="total-count-part">
                                <span class="label"><?php echo esc_html__( 'Grand Total', 'trav' ); ?>:</span>
                                <span class="val"><?php echo trav_get_price_field( $booking_data['total_price'] * $booking_data['exchange_rate'], $booking_data['currency_code'], 0 ); ?></span>
                            </div>
                        </div>

                        <?php trav_get_template( 'acc-room-detail.php', '/templates/modern/accommodation/' ); ?>
                        <?php do_action( 'trav_acc_conf_form_after', $booking_data ); ?>
                    </div>
                </div>
            </div>

            <aside class="col-lg-4 sidebar-content-area confirm-sidebar-area sidebar">
                <?php do_action( 'trav_acc_conf_completed_sidebar_before', $booking_data ); ?>
                <?php generated_dynamic_sidebar(); ?>
                <?php do_action( 'trav_acc_conf_completed_sidebar_after', $booking_data ); ?>
            </aside>
        </div>
    </div>
</div>