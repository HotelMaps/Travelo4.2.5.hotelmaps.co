<?php  
/**
 *	Add & Custom Js Composer Element
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit();
}

// Extra Param to "Row" Element
vc_add_param( 'vc_row', array(
	'type'			=>	'dropdown',
	'heading'		=>	esc_html__( 'Wrap as a Container', 'trav' ),
	'param_name'	=>	'wrap_container',
	'value'			=>	array(
		__( 'Enable', 'trav' ) => 'true',
		__( 'Disable', 'trav' ) => 'false',
	),
	'std'			=>	'false',
	'group'			=>	esc_html__( 'Container Option', 'trav' ),
	'admin_label'	=>	true,
) );

vc_add_param( 'vc_row_inner', array(
	'type'			=>	'dropdown',
	'heading'		=>	esc_html__( 'Wrap as a Container', 'trav' ),
	'param_name'	=>	'wrap_container',
	'value'			=>	array(
		__( 'Enable', 'trav' ) => 'true',
		__( 'Disable', 'trav' ) => 'false',
	),
	'std'			=>	'false',
	'group'			=>	esc_html__( 'Container Option', 'trav' ),
	'admin_label'	=>	true,
) );

// Add a custom icon set from icomoon to visual composer vc_icon shortcode
function trav_modern_icon_array() {
	return array(
		array( 'travelo-air-conditioning'	=> 'air-conditioning' ),
		array( 'travelo-breakfast'			=> 'breakfast' ),
		array( 'travelo-cars'				=> 'cars' ),
		array( 'travelo-children'			=> 'children' ),
		array( 'travelo-comment'			=> 'comment' ),
		array( 'travelo-days'				=> 'days' ),
		array( 'travelo-doorman'			=> 'doorman' ),
		array( 'travelo-cruises'			=> 'cruises' ),
		array( 'travelo-elevator'			=> 'elevator' ),
		array( 'travelo-entertainment'		=> 'entertainment' ),
		array( 'travelo-fitness-facility'	=> 'fitness-facility' ),
		array( 'travelo-flights'			=> 'flights' ),
		array( 'travelo-flight-search'		=> 'flight' ),
		array( 'travelo-grid'				=> 'grid' ),
		array( 'travelo-honeymoon'			=> 'honeymoon' ),
		array( 'travelo-hotel'				=> 'hotel' ),
		array( 'travelo-hotels'				=> 'hotels' ),
		array( 'travelo-hotel-schedule'		=> 'hotel-schedule' ),
		array( 'travelo-hot-tub'			=> 'hot' ),
		array( 'travelo-infants'			=> 'infants' ),
		array( 'travelo-king-size-bed'		=> 'king-size-bed' ),
		array( 'travelo-kitchen'			=> 'kitchen' ),
		array( 'travelo-late-saving'		=> 'late-saving' ),
		array( 'travelo-list'				=> 'list' ),
		array( 'travelo-payment'			=> 'payment' ),
		array( 'travelo-peoples'			=> 'peoples' ),
		array( 'travelo-protect'			=> 'protect' ),
		array( 'travelo-room'				=> 'room' ),
		array( 'travelo-security'			=> 'security' ),
		array( 'travelo-sun-umbrella'		=> 'sun-umbrella' ),
		array( 'travelo-support'			=> 'support' ),
		array( 'travelo-tours'				=> 'tours' ),
		array( 'travelo-adults'				=> 'adults' ),
	);
}
add_filter( 'vc_iconpicker-type-traveloicons', 'trav_modern_icon_array' );

add_action( 'vc_base_register_front_css', 'trav_modern_vc_iconpicker_base_register_css' );
add_action( 'vc_base_register_admin_css', 'trav_modern_vc_iconpicker_base_register_css' );
function trav_modern_vc_iconpicker_base_register_css(){
    wp_register_style( 'trav-vc-custom-font', TRAV_TEMPLATE_DIRECTORY_URI . '/css/modern/font-travelo/css/font-travelo.css' );
}

add_action( 'vc_backend_editor_enqueue_js_css', 'trav_modern_vc_iconpicker_editor_jscss' );
add_action( 'vc_frontend_editor_enqueue_js_css', 'trav_modern_vc_iconpicker_editor_jscss' );
function trav_modern_vc_iconpicker_editor_jscss(){
    wp_enqueue_style( 'trav-vc-custom-font' );
}

add_action('vc_enqueue_font_icon_element', 'trav_enqueue_font_icomoon');
function trav_enqueue_font_icomoon( $font ){
    switch ( $font ) {
        case 'traveloicons' : wp_enqueue_style( 'trav-vc-custom-font' );
    }
}