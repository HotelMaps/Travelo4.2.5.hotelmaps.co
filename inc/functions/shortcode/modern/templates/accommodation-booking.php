<?php

/**
 * Accommodation Booking Shortcode
 */
if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly
}

// [accommodation_booking]
function trav_shortcode_accommodation_booking( $atts, $content = null ) {
	ob_start();
	trav_get_template( 'accommodation-booking.php', '/templates/modern/accommodation' );
	$output = ob_get_contents();
	ob_end_clean();
	return $output;
}

add_shortcode( 'accommodation_booking', 'trav_shortcode_accommodation_booking' );