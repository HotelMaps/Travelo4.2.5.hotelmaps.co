<?php
/**
 * Flight Booking Confirmation Template
 */
if ( ! defined( 'ABSPATH' ) ) {
    exit; // exit if accessed directly
}

global $wpdb;
global $booking_data, $flight_id, $deposit_rate, $flight_date;

if ( ! isset( $_REQUEST['booking_no'] ) || ! isset( $_REQUEST['pin_code'] ) ) {
    return;
    do_action('trav_flight_conf_wrong_data');
    exit;
}

if ( ! $booking_data = trav_flight_get_booking_data( $_REQUEST['booking_no'], $_REQUEST['pin_code'] ) ) {
    return;
    do_action('trav_flight_conf_wrong_data');
    exit;
}

$flight_id = trav_flight_clang_id( $booking_data['flight_id'] );

$flight_date = trav_tophptime( $booking_data['flight_date'] );

// if deposit is required and it is not paid process payment
if ( ! empty( $booking_data['deposit_price'] ) && empty( $booking_data['deposit_paid'] ) ) {
    // init payment variables
    $ItemName = '';
    if ( $deposit_rate < 100 ) {
        $ItemName = sprintf( __( 'Deposit(%d%%) for ', 'trav' ), $deposit_rate );
    } else {
        $ItemName = __( 'Deposit for ', 'trav' );
    }
    $ItemName .= get_the_title( $flight_id );

    $payment_data = array();
    $payment_data['item_name'] = $ItemName;
    $payment_data['item_number'] = $flight_id;
    $payment_data['item_desc'] = __( 'Date', 'trav' ) . ' ' . $flight_date . ' ' . get_the_title( $flight_id );
    $payment_data['item_qty'] = 1;
    $payment_data['item_price'] = $booking_data['deposit_price'];
    $payment_data['item_total_price'] = $payment_data['item_qty'] * $payment_data['item_price'];
    $payment_data['grand_total'] = $payment_data['item_total_price'];
    $payment_data['currency'] = strtoupper( $booking_data['currency_code'] );
    $payment_data['return_url'] = trav_get_current_page_url() . '?booking_no=' . $booking_data['booking_no'] . '&pin_code=' . $booking_data['pin_code'] . '&payment=success';
    $payment_data['cancel_url'] = trav_get_current_page_url() . '?booking_no=' . $booking_data['booking_no'] . '&pin_code=' . $booking_data['pin_code'] . '&payment=failed';
    $payment_data['status'] = '';
    $payment_data['deposit_rate'] = $deposit_rate;

    if ( ! empty( $_REQUEST['transaction_id'] ) && ! empty( $_SESSION['booking_data'][$_REQUEST['transaction_id']] ) ) $payment_data['status'] = 'before';
    $payment_result = trav_process_payment( $payment_data );

    // after payment
    if ( $payment_result ) {
        if ( ! empty( $payment_result['success'] ) && ( $payment_result['method'] == 'paypal' ) ) {
            $other_booking_data = array();
            if ( ! empty( $booking_data['other'] ) ) {
                $other_booking_data = unserialize( $booking_data['other'] );
            }
            $other_booking_data['pp_transaction_id'] = $payment_result['transaction_id'];
            $booking_data['deposit_paid'] = 1;
            $update_status = $wpdb->update( TRAV_FLIGHT_BOOKINGS_TABLE, array( 'deposit_paid' => $booking_data['deposit_paid'], 'status' => 1, 'other' => serialize( $other_booking_data ) ), array( 'booking_no' => $booking_data['booking_no'], 'pin_code' => $booking_data['pin_code'] ) );
            if ( $update_status === false ) {
                do_action( 'trav_payment_update_booking_error' );
            } elseif ( empty( $update_status ) ) {
                do_action( 'trav_payment_update_booking_no_row' );
            } else {
                do_action( 'trav_payment_update_booking_success' );
            }
        }
    }
}

if ( ! empty( $deposit_rate ) && empty( $booking_data['deposit_paid'] ) ) {
    do_action('trav_flight_deposit_payment_not_paid', $booking_data ); // deposit payment not paid
}

if ( empty( $booking_data['mail_sent'] ) && ! empty( $booking_data['status'] ) && ( empty( $deposit_rate ) || ! empty( $booking_data['deposit_paid'] ) ) ) {
    do_action('trav_flight_conf_mail_not_sent', $booking_data); // mail is not sent
}

if ( ! empty( $_REQUEST['transaction_id'] ) && ! empty( $_SESSION['booking_data'] ) ) {
    unset( $_SESSION['booking_data'][$_REQUEST['transaction_id']] ); // unset session data for further action
}

if ( $booking_data['status'] == 1 ) { // if upcoming 
    trav_get_template( 'booking-success.php', '/templates/flight/' ); 
} elseif ( $booking_data['status'] == 0 ) { // if cancelled 
    trav_get_template( 'booking-cancelled.php', '/templates/flight/' ); 
} elseif ( $booking_data['status'] == 2 ) { // if completed 
    trav_get_template( 'booking-completed.php', '/templates/flight/' ); 
}