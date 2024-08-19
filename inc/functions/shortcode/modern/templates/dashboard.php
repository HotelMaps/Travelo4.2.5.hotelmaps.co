<?php

/**
 * Dashboard Template Shortcode
 */
if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly
}

// [dashboard/]
function trav_shortcode_dashboard( $atts, $content = null ) {
	ob_start();
	trav_get_template( 'main.php', '/templates/user' );
	$output = ob_get_contents();
	ob_end_clean();
	return $output;
}

add_shortcode( 'dashboard', 'trav_shortcode_dashboard' );