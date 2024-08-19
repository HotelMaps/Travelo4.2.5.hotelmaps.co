<?php
/**
 *	Shortcode Init
 */
if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly.
}

/* Remove p and br tags before and after shortcodes in content */
function trav_clean_shortcodes( $content ) {
	$array = array (
	  '<p>['	=> '[',
	  ']</p>'   => ']',
	  ']<br />' => ']',
	);

	$content = strtr( $content, $array );
	$content = preg_replace( "/<br \/>.\[/s", "[", $content );

	return $content;
}

add_filter( 'the_content', 'trav_clean_shortcodes' );

/* Return default Orderby values */
function trav_default_orderby_values() { 
	return array( 
		'',
		esc_html__( 'Date', 'trav' )		=> 'date',
		esc_html__( 'ID', 'trav' )			=> 'ID',
		esc_html__( 'Slug', 'trav' )		=> 'slug',
		esc_html__( 'Author', 'trav' )		=> 'author',
		esc_html__( 'Title', 'trav' )		=> 'title',
		esc_html__( 'Modified', 'trav' )	=> 'modified',
		esc_html__( 'Random', 'trav' )		=> 'rand',
		esc_html__( 'Menu Order', 'trav' )	=> 'menu_order'
	);
}

/* Function Add */
function trav_getCSSAnimation( $css_animation ) {
	$output = '';
	
	if ( '' !== $css_animation && 'none' !== $css_animation ) {
		wp_enqueue_script( 'waypoints' );
		wp_enqueue_style( 'animate-css' );
		$output = ' wpb_animate_when_almost_visible wpb_' . $css_animation . ' ' . $css_animation;
	}

	return $output;
}

/* Return extra class field */
function trav_extra_class_field() {
	return array(
		'type'			=>	'textfield',
		'heading'		=>	esc_html__( 'Extra Class Name', 'trav' ),
		'param_name'	=>	'extra_class',
		'description'	=>	esc_html__( 'If you wish to style particular content element differently, then use this field to add a class name and then refer to it in your css file.', 'trav' )
	);
}

/* Return animation style field */
function trav_animation_style_field() {
	return array(
		'type'			=>	'animation_style',
		'heading'		=>	__( 'Animation Style', 'trav' ),
		'param_name'	=>	'animation',
		'description'	=>	__( 'Choose your animation style', 'trav' ),
		'admin_label'	=>	false,
		'weight'		=>	0
	);
}

/* Return animation delay field */
function trav_animation_delay_field() {
	return array(
		'type'			=>	'textfield',
		'heading'		=>	__( 'Animation Delay', 'trav' ),
		'param_name'	=>	'animation_delay',
		'description'	=>	__( 'Enter the delay second number', 'trav' ),
		'admin_label'	=>	false,
		'weight'		=>	0,
		'std'			=>	1
	);
}

/* Include Shortcode template files */
function trav_shortcode_init() {
	// Add Shortcode Functions
	include_once( 'modern/functions.php' );
	
	if ( defined( 'WPB_VC_VERSION' ) ) {

		// Enable VC on Post Types
		$list = array( 'post', 'page', 'accommodation', 'tour', 'html_block' );
		vc_set_default_editor_post_types( $list );

		include_once( 'modern/templates/search-group.php' );
		include_once( 'modern/templates/search-form.php' );
		include_once( 'modern/templates/location-slider.php' );
		include_once( 'modern/templates/location-grid.php' );
		include_once( 'modern/templates/title.php' );
		include_once( 'modern/templates/accommodations.php' );
		include_once( 'modern/templates/accommodation-booking.php' );
		include_once( 'modern/templates/accommodation-booking-confirmation.php' );
		include_once( 'modern/templates/dashboard.php' );
		include_once( 'modern/templates/html-block.php' );
		include_once( 'modern/templates/testimonial.php' );
		include_once( 'modern/templates/icon-box.php' );
		include_once( 'modern/templates/team-member.php' );
	}
}
add_action( 'init', 'trav_shortcode_init', 30 );