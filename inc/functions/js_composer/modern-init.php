<?php  
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 *	Initialize Visual Composer
 */

if ( class_exists('Vc_Manager', false) ) {
	// Disable update
	add_action('vc_before_init', 'trav_vcSetAsTheme');

	function trav_vcSetAsTheme() {
		vc_manager()->disableUpdater( true );
		vc_set_as_theme();
	}

	// Modify and remove existing shortcodes from VC
	add_action('vc_before_init', 'trav_load_js_composer');

	function trav_load_js_composer() {
		require_once TRAV_INC_DIR . '/functions/js_composer/modern/functions.php';
		require_once TRAV_INC_DIR . '/functions/js_composer/modern/js_composer.php';
	}

	// VC Templates
	if ( function_exists( 'vc_set_shortcodes_templates_dir' ) ) {
		vc_set_shortcodes_templates_dir( TRAV_INC_DIR . '/functions/js_composer/modern/vc_templates' );
	}
}