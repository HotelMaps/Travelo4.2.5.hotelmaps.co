<?php
/**
 * Html Block Shortcode
 */
if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly
}

// [html_block]
function trav_shortcode_html_block( $atts, $content = null ) {

	extract( shortcode_atts( array(
			'block_id'			=> '',
			'extra_class'		=> '',
			'animation'			=> '',
			'animation_delay'	=> 1
		), $atts ) );
	
	if ( is_numeric( $block_id ) ) {
		$content = get_post_field( 'post_content', $block_id );



		$post_custom_css = get_post_meta( $block_id, '_wpb_post_custom_css', true );
		$shortcode_custom_css = get_post_meta( $block_id, '_wpb_shortcodes_custom_css', true );
		$styles = '';

		if ( ! empty( $post_custom_css ) ) {
			$styles .= $post_custom_css;
		}

		if ( ! empty( $shortcode_custom_css ) ) {
			$styles .= $shortcode_custom_css;
		}

		
		if ( ! empty( $styles ) ) {
			$content .= '<style type="text/css" data-type="vc_shortcodes-custom-css">';
			$content .= $styles;
			$content .= '</style>';
		}
	}

	// Build the animation classes
	$animation_classes = trav_getCSSAnimation( $animation );
	
	$html = '';

	$html .= '<div class="trav-html-block ' . esc_attr( $extra_class ) . ' ' . $animation_classes . '" style="animation-delay: ' . $animation_delay . 's;">';

	if ( ! empty( $content ) ) {
		$html .= '<div class="html_block_content">' . do_shortcode( $content ) . '</div>';
	}

	$html .= '</div>';

	return $html;

}

add_shortcode( 'html_block', 'trav_shortcode_html_block' );

/**
 * WPBakery
 */
function trav_vc_shortcode_html_block() {

	$args = array(
		'post_per_page'	=>	100,
		'post_type'		=>	'html_block'
	);

	$html_block_posts = get_posts( $args );
	$html_block_dropdown = array();
	$html_block_dropdown[ esc_html__( 'Select Block', 'trav' ) ] = 'select_block';
	
	foreach ( $html_block_posts as $post ) {
		$html_block_dropdown[ $post->post_title ] = $post->ID;
	}
	
	$animation_style = trav_animation_style_field();

	$animation_delay = trav_animation_delay_field();

	$extra_class = trav_extra_class_field();

	vc_map( array(
		'name'			=>	esc_html__( 'Html Block', 'trav' ),
		'base'			=>	'html_block',
		'icon'			=>	'trav-js-composer',
		'category'		=>	esc_html__( 'by C-Themes', 'trav' ),
		'description'	=>	esc_html__( 'Add Custom Html Block on your page.', 'trav' ),
		'params'		=>	array(
			array(
				'type' 			=> 'dropdown',
				'holder' 		=> 'div',
				'heading' 		=> esc_html__( 'Select Html Block', 'trav' ),
				'param_name' 	=> 'block_id',
				'admin_label' 	=> true,
				'save_always'	=> true,
				'value'			=> $html_block_dropdown,
			),
			$animation_style,
			$animation_delay,
			$extra_class
		)
	) );

}

trav_vc_shortcode_html_block();