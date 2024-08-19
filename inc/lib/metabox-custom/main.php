<?php
/*
 * Meta Box Custom Fields
 */

defined( 'ABSPATH' ) || exit;

if ( ! function_exists( 'trav_register_metabox_custom_fields' ) ) {
	function trav_register_metabox_custom_fields() {
	    require 'fields/rev-slider.php';
	}
}

add_action( 'init', 'trav_register_metabox_custom_fields' );