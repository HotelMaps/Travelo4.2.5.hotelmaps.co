<?php
/**
 *	Icon Box Shortcode
 */
if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly.
}

// [trav_modern_icon_box]
function trav_shortcode_icon_box( $atts, $content = null ) {
	extract( shortcode_atts( array(
		'icon_library'			=>	'fontawesome',
		'fontawesome_icon'		=>	'',
		'openiconic_icon'		=>	'',
		'typicons_icon'			=>	'',
		'entypo_icon'			=>	'',
		'linecons_icon'			=>	'',
		'monosocial_icon'		=>	'',
		'traveloicons_icon'		=>	'',
		'icon_size'				=>	'30',
		'icon_bg_color'			=>	'#222',
		'icon_color'			=>	'#fff',
		'css'					=>	'',
		'box_align'				=>	'center',
		'content_align'			=>	'left',
		'extra_class'			=>	'',
		'animation'				=>	'',
		'animation_delay'		=>	''
	), $atts ) );

	$rand_id = rand( 100, 9999 );
	$shortcode_icon_box_id = uniqid( 'trav-icon-box-' . $rand_id );

	$icon_box_classes = $content_class = $html = $styles = '';

	if ( ! empty( $icon_library ) ) {
		$icon_box_classes .= $icon_library . '-icon-style';
	}

	if ( ! empty( $extra_class ) ) {
		$icon_box_classes .= ' ' . $extra_class;
	}

	if ( ! empty( $css ) && function_exists( 'vc_shortcode_custom_css_class' ) ) {
		$content_class .= vc_shortcode_custom_css_class( $css );
	}

	if ( ! empty( $content_align ) ) {
		$content_class .= ' icon-box-content-' . $content_align;
	}

	if ( ! empty( $box_align ) ) {
		$content_class .= ' icon-box-' . $box_align;
	}

	vc_icon_element_fonts_enqueue( $icon_library );

	$font_icon_class = isset( ${ $icon_library . '_icon' } ) ? esc_attr( ${ $icon_library . '_icon' } ) : 'fa fa-smile-o';
	$icon = '<i class="' . $font_icon_class . '" style="color: ' . $icon_color . '; font-size: ' . $icon_size . 'px; width: ' . $icon_size . 'px; height: ' . $icon_size . 'px;"></i>';

	$html .= '<div id="' . esc_attr( $shortcode_icon_box_id ) . '" class="shortcode-icon-box ' . esc_attr( $icon_box_classes ) . '" style="animation-delay: ' . $animation_delay . 's;">';
		$html .= '<div class="shortcode-icon-box-wrapper ' . esc_attr( $content_class ) . '">';
			$html .= '<div class="icon-wrap" style="background-color: ' . $icon_bg_color . '; margin-' . $content_align . ': 20px;">';
				$html .= $icon;
			$html .= '</div>';
			$html .= '<div class="content-inner">' . do_shortcode( $content ) . '</div>';
		$html .= '</div>';
	$html .= '</div>';

	$margin_align = $content_align;

	return $html;
}

add_shortcode( 'trav_modern_icon_box', 'trav_shortcode_icon_box' );

/**
 * WPBakery
 */
function trav_vc_shortcode_icon_box() {

	$extra_class = trav_extra_class_field();

	vc_map( array(
		'name'			=>	esc_html__( 'Icon Box', 'trav' ),
		'base'			=>	'trav_modern_icon_box',
		'icon'			=>	'trav-js-composer',
		'category'		=>	esc_html__( 'by C-Themes', 'trav' ),
		'description'	=>	esc_html__( 'Place Icon Box.', 'trav' ),
		'params'		=>	array(
			array(
				'type'			=>	'dropdown',
				'heading'		=>	esc_html__( 'Icon Library', 'trav' ),
				'param_name'	=>	'icon_library',
				'admin_label'	=>	true,
				'value'			=>	array(
					esc_html__( 'Font Awesome', 'trav' )	=>	'fontawesome',
					esc_html__( 'Open Iconic', 'trav' )		=>	'openiconic',
					esc_html__( 'Typicons', 'trav' )		=>	'typicons',
					esc_html__( 'Entypo', 'trav' )			=>	'entypo',
					esc_html__( 'Linecons', 'trav' )		=>	'linecons',
					esc_html__( 'Mono Social', 'trav' )		=>	'monosocial',
					esc_html__( 'Travelo Icon', 'trav' )	=>	'traveloicons',
				)
			),
			array(
				'type'			=>	'iconpicker',
				'heading'		=>	esc_html__( 'FontAwesome Icon', 'trav' ),
				'param_name'	=>	'fontawesome_icon',
				'dependency'	=>	array(
					'element'	=>	'icon_library',
					'value'		=>	'fontawesome'
				),
				'settings'		=>	array(
					'emptyIcon'		=>	true,
					'iconsPerPage'	=>	500,
				),
				'description'	=>	esc_html__( 'Select Icon Library', 'trav' )
			),
			array(
				'type'			=>	'iconpicker',
				'heading'		=>	esc_html__( 'Openiconic', 'trav' ),
				'param_name'	=>	'openiconic_icon',
				'dependency'	=>	array(
					'element'	=>	'icon_library',
					'value'		=>	'openiconic'
				),
				'settings'		=>	array(
					'emptyIcon'		=>	true,
					'type'			=>	'openiconic',
					'iconsPerPage'	=>	500,
				),
				'description'	=>	esc_html__( 'Select Icon Library', 'trav' )
			),
			array(
				'type'			=>	'iconpicker',
				'heading'		=>	esc_html__( 'Typicons', 'trav' ),
				'param_name'	=>	'typicons_icon',
				'dependency'	=>	array(
					'element'	=>	'icon_library',
					'value'		=>	'typicons'
				),
				'settings'		=>	array(
					'emptyIcon'		=>	true,
					'type'			=>	'typicons',
					'iconsPerPage'	=>	500,
				),
				'description'	=>	esc_html__( 'Select Icon Library', 'trav' )	
			),
			array(
				'type'			=>	'iconpicker',
				'heading'		=>	esc_html__( 'Entypo', 'trav' ),
				'param_name'	=>	'entypo_icon',
				'dependency'	=>	array(
					'element'	=>	'icon_library',
					'value'		=>	'entypo'
				),
				'settings'		=>	array(
					'emptyIcon'		=>	true,
					'type'			=>	'entypo',
					'iconsPerPage'	=>	500,
				),
				'description'	=>	esc_html__( 'Select Icon Library', 'trav' )
			),
			array(
				'type'			=>	'iconpicker',
				'heading'		=>	esc_html__( 'Linecons', 'trav' ),
				'param_name'	=>	'linecons_icon',
				'dependency'	=>	array(
					'element'	=>	'icon_library',
					'value'		=>	'linecons'
				),
				'settings'		=>	array(
					'emptyIcon'		=>	true,
					'type'			=>	'linecons',
					'iconsPerPage'	=>	500,
				),
				'description'	=>	esc_html__( 'Select Icon Library', 'trav' )
			),
			array(
				'type'			=>	'iconpicker',
				'heading'		=>	esc_html__( 'Mono Social Icon', 'trav' ),
				'param_name'	=>	'monosocial_icon',
				'dependency'	=>	array(
					'element'	=>	'icon_library',
					'value'		=>	'monosocial'
				),
				'settings'		=>	array(
					'emptyIcon'		=>	false,
					'type'			=>	'monosocial',
					'iconsPerPage'	=>	500,
				),
				'description'	=>	esc_html__( 'Select Icon Library', 'trav' )
			),
			array(
				'type'			=>	'iconpicker',
				'heading'		=>	esc_html__( 'Travelo Icon', 'trav' ),
				'param_name'	=>	'traveloicons_icon',
				'dependency'	=>	array(
					'element'	=>	'icon_library',
					'value'		=>	'traveloicons'
				),
				'settings'		=>	array(
					'emptyIcon'		=>	false,
					'type'			=>	'traveloicons',
					'iconsPerPage'	=>	500,
				),
				'description'	=>	esc_html__( 'Select Icon Library', 'trav' )
			),
			array(
				'type'			=>	'textfield',
				'heading'		=>	esc_html__( 'Icon Size', 'trav' ),
				'param_name'	=>	'icon_size',
				'description'	=>	esc_html__( 'Add icon size instead of pixel unit.', 'trav' ),
				'dependency'	=>	array(
					'element'	=>	'icon_library',
					'value'		=>	array( 'fontawesome', 'openiconic', 'typicons', 'entypo', 'linecons', 'monosocial', 'traveloicons' )
				),
				'std'			=>	14
			),
			array(
				'type'			=>	'colorpicker',
				'heading'		=>	esc_html__( 'Icon Background Color', 'trav' ),
				'param_name'	=>	'icon_bg_color',
				'std'			=>	'#222'
			),
			array(
				'type'			=>	'colorpicker',
				'heading'		=>	esc_html__( 'Icon Color', 'trav' ),
				'param_name'	=>	'icon_color',
				'std'			=>	'#fff'
			),
			array(
				'type'			=>	'textarea_html',
				'heading'		=>	esc_html__( 'Icon Box Content', 'trav' ),
				'param_name'	=>	'content',
			),
			array(
				'type'			=>	'css_editor',
				'heading'		=>	esc_html__( 'Icon Box Custom CSS', 'trav' ),
				'param_name'	=>	'css',
				'group'			=>	esc_html__( 'Design For Content', 'trav' )
			),
			array(
				'type'			=>	'dropdown',
				'heading'		=>	esc_html__( 'Alignment', 'trav' ),
				'param_name'	=>	'box_align',
				'value'			=>	array(
					esc_html__( 'Left', 'trav' )		=>	'left',
					esc_html__( 'Center', 'trav' )		=>	'center',
					esc_html__( 'Right', 'trav' )		=>	'right',
				),
				'std'			=>	'center'
			),
			array(
				'type'			=>	'dropdown',
				'heading'		=>	esc_html__( 'Content Alignment', 'trav' ),
				'param_name'	=>	'content_align',
				'value'			=>	array(
					esc_html__( 'Left', 'trav' )		=>	'left',
					esc_html__( 'Right', 'trav' )		=>	'right',
					esc_html__( 'Top', 'trav' )			=>	'top',
					esc_html__( 'Bottom', 'trav' )		=>	'bottom'
				),
				'std'			=>	'left'
			),
			$extra_class,
		)
	) );
}

trav_vc_shortcode_icon_box();