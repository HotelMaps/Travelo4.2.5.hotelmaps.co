<?php

/**
 * Accommodation Booking Confirmation Shortcode
 */
if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly
}

// [accommodation_booking_confirmation]
function trav_shortcode_accommodation_booking_confirmation( $atts, $content = null ) {
	ob_start();
	trav_get_template( 'accommodation-booking-confirmation.php', '/templates/modern/accommodation' );
	$output = ob_get_contents();
	ob_end_clean();
	return $output;
}

add_shortcode( 'accommodation_booking_confirmation', 'trav_shortcode_accommodation_booking_confirmation' );