<?php
/**
 * Title Shortcode
 */
if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly
}

// [trav_modern_title]
function trav_shortcode_title( $atts, $content = null ) {
	
	$variables = array(
						'style'			=> 'style1', //style1, style2, style3
						'title'			=> '',
						'subtitle'		=> '',
						'extra_class'	=> '',
						'css'			=> '',
					);
	extract( shortcode_atts( $variables, $atts ) );

	$id = rand( 100, 9999 );
	$shortcode_id = uniqid( 'trav-location-grid-' . $id );

	$content_class = '';
	if ( ! empty( $extra_class ) ) {
		$content_class .= ' ' . $extra_class;
	}

	if ( ! empty( $css ) && function_exists( 'vc_shortcode_custom_css_class' ) ) {
		$content_class .= ' ' . vc_shortcode_custom_css_class( $css );
	}
	
	ob_start();

	if ( ! empty( $style ) && $style == 'style2' ) {
	?>
		<div id="<?php echo esc_attr( $shortcode_id ); ?>" class="main-title style2 <?php echo esc_attr( $content_class ); ?>">
			<span class="sub-title"><?php echo esc_html( $subtitle ); ?></span>
			<h1 class="main-title"><?php echo esc_html( $title ); ?></h1>
		</div>
	<?php
	} elseif ( ! empty( $style ) && $style == 'style3' ) {
	?>
		<div id="<?php echo esc_attr( $shortcode_id ); ?>" class="head-part style3 <?php echo esc_attr( $content_class ); ?>">
			<h1 class="main-title"><?php echo esc_html( $title ); ?></h1>
			<p class="description"><?php echo esc_html( $subtitle ); ?></p>
		</div>
	<?php
	} else {
	?>
		<div id="<?php echo esc_attr( $shortcode_id ); ?>" class="main-title-wrap style1 <?php echo esc_attr( $content_class ); ?>">
			<span class="sub-title"><?php echo esc_html( $subtitle ); ?></span>
			<h2 class="main-title"><?php echo esc_html( $title ); ?></h2>
		</div>
	<?php
	}
	$output = ob_get_contents();
	ob_end_clean();
	
	return $output;
}

add_shortcode( 'trav_modern_title', 'trav_shortcode_title' );

/**
 * WPBakery
 */
function trav_vc_shortcode_title() {

	$extra_class = trav_extra_class_field();

	vc_map( array(
		'name'			=>	esc_html__( 'Title', 'trav' ),
		'base'			=>	'trav_modern_title',
		'icon'			=>	'trav-js-composer',
		'category'		=>	esc_html__( 'by C-Themes', 'trav' ),
		'description'	=>	esc_html__( 'Show title in your page.', 'trav' ),
		'params'		=>	array(
			array(
				"type"			=> "dropdown",
				"class"			=> "",
				"heading"		=> esc_html__( "Style", 'trav' ),
				"param_name"	=> "style",
				'value' => array(
					esc_html__( 'Style 1', 'trav' )		=> 'style1',
					esc_html__( 'Style 2', 'trav' )		=> 'style2',
					esc_html__( 'Style 3', 'trav' )		=> 'style3',
				),
				"std"			=> "style1",
			),
			array(
				'type'			=>	'textfield',
				'heading'		=>	esc_html__( 'Title', 'trav' ),
				'param_name'	=>	'title',
				'admin_label'	=>	true,
			),	
			array(
				'type'			=>	'textfield',
				'heading'		=>	esc_html__( 'Subtitle', 'trav' ),
				'param_name'	=>	'subtitle',
			),	
			$extra_class,
			array(
				'type'			=>	'css_editor',
				'heading'		=>	esc_html__( 'Custom CSS', 'trav' ),
				'param_name'	=>	'css',
				'group'			=>	esc_html__( 'Design For Content', 'trav' )
			),
		)
	) );
}

trav_vc_shortcode_title();