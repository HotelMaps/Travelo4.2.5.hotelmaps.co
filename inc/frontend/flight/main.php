<?php
if ( ! defined( 'ABSPATH' ) ) exit; // Exit if accessed directly

require_once( TRAV_INC_DIR . '/frontend/flight/functions.php');
require_once( TRAV_INC_DIR . '/frontend/flight/templates.php');
require_once( TRAV_INC_DIR . '/frontend/flight/ajax.php');

add_action( 'wp_ajax_flight_submit_booking', 'trav_ajax_flight_submit_booking' );
add_action( 'wp_ajax_nopriv_flight_submit_booking', 'trav_ajax_flight_submit_booking' );

add_action( 'trav_flight_booking_before', 'trav_flight_booking_before' );
add_action( 'trav_flight_deposit_payment_not_paid', 'trav_flight_deposit_payment_not_paid' );
add_action( 'trav_flight_conf_mail_not_sent', 'trav_flight_conf_send_mail' );