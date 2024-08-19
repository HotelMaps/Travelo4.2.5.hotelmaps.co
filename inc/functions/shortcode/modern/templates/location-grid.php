<?php
/**
 * Location Grid Shortcode
 */
if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly
}

// [trav_modern_location_grid]
function trav_shortcode_location_grid( $atts, $content = null ) {
	
	$variables = array(
						'button_title'	=> '',
						'button_link'	=> '#',
						'extra_class'	=> '',
						'css'			=> '',
					);
	extract( shortcode_atts( $variables, $atts ) );

	$link = vc_build_link($button_link);
	if ( !empty($link['url'])) {
		$button_link = $link['url'];
	}

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
	?>

	<div id="<?php echo esc_attr( $shortcode_id ); ?>" class="destination-masonry-wrap <?php echo esc_attr( $content_class ); ?>">
		<div class="destinations-masonry-grid">
			<?php echo do_shortcode( $content ); ?>
		</div>

		<?php if ( ! empty( $button_title ) ) { ?>
			<div class="full-destination-list-link">
				<a href="<?php echo esc_url( $button_link ); ?>" class="border-btn-third"><?php echo $button_title; ?> <i class="fas fa-angle-double-right"></i></a>
			</div>
		<?php } ?>
	</div>

	<?php
	$output = ob_get_contents();
	ob_end_clean();
	
	return $output;
}

add_shortcode( 'trav_modern_location_grid', 'trav_shortcode_location_grid' );

// [trav_modern_location_grid_item]
function trav_shortcode_location_grid_item( $atts, $content = null ) {
	
	$variables = array(
						'img_id'			=> '',
						'location_title'	=> '',
						'location_desc'		=> '',
						'location_link'		=> '',
						'extra_class'		=> '',
					);
	extract( shortcode_atts( $variables, $atts ) );

	$id = rand( 100, 9999 );
	$shortcode_id = uniqid( 'trav-location-grid-' . $id );

	$link = vc_build_link($location_link);
	if ( !empty($link['url'])) {
		$location_link = $link['url'];
	}

	$content_class = '';
	if ( ! empty( $extra_class ) ) {
		$content_class .= ' ' . $extra_class;
	}

	if ( ! empty( $css ) && function_exists( 'vc_shortcode_custom_css_class' ) ) {
		$content_class .= ' ' . vc_shortcode_custom_css_class( $css );
	}
	
	ob_start();
	?>

	<div id="<?php echo esc_attr( $shortcode_id ); ?>" class="single-destination <?php echo esc_attr( $content_class ); ?>">
		<div class="destination-wrap">
			<?php
				if ( ! empty( $img_id ) && is_numeric( $img_id ) ) {
				?>
					<span class="destination-bg" style="background-image: url('<?php echo wp_get_attachment_image_url( $img_id, 'slider-gallery' ); ?>');"></span>
				<?php
				}
			?>

			<a href="<?php echo esc_url( $location_link ); ?>" class="destination-link">
				<div class="info-wrap">
					<h3 class="destination-title"><?php echo esc_html( $location_title ); ?></h3>
					<p class="destination-desc"><?php echo esc_html( $location_desc ); ?></p>
				</div>
			</a>
		</div>
	</div>

	<?php
	$output = ob_get_contents();
	ob_end_clean();
	
	return $output;
}

add_shortcode( 'trav_modern_location_grid_item', 'trav_shortcode_location_grid_item' );


/**
 * WPBakery
 */
function trav_vc_shortcode_location_grid() {

	$extra_class = trav_extra_class_field();

	vc_map( array(
		'name'			=>	esc_html__( 'Location Grid', 'trav' ),
		'base'			=>	'trav_modern_location_grid',
		'icon'			=>	'trav-js-composer',
		'category'		=>	esc_html__( 'by C-Themes', 'trav' ),
		'description'	=>	esc_html__( 'Show location grid in your page.', 'trav' ),
		'params'		=>	array(
			array(
				'type'			=>	'textfield',
				'heading'		=>	esc_html__( 'Button Title', 'trav' ),
				'param_name'	=>	'button_title',
				'admin_label'	=>	true,
			),
			array(
				'type'			=>	'vc_link',
				'heading'		=>	esc_html__( 'Button Link', 'trav' ),
				'param_name'	=>	'button_link',
				'description'	=>	esc_html__( 'Enter URL if you want to add a link.', 'trav' ),				
			),
			$extra_class,
			array(
				'type'			=>	'css_editor',
				'heading'		=>	esc_html__( 'Custom CSS', 'trav' ),
				'param_name'	=>	'css',
				'group'			=>	esc_html__( 'Design For Content', 'trav' )
			)
		),
		'as_parent'			=>	array( 'only' => 'trav_modern_location_grid_item' ),
		'js_view'			=>	'VcColumnView',
	) );

	vc_map( array(
		'name'				=>	esc_html__( 'Location', 'trav' ),
		'base'				=>	'trav_modern_location_grid_item',
		'icon'				=>	'trav-js-composer',
		'category'			=>	esc_html__( 'by C-Themes', 'trav' ),
		'as_child'			=>	array( 'only' => 'trav_modern_location_grid' ),
		'params'			=>	array(
			array(
				'type'			=>	'textfield',
				'heading'		=>	esc_html__( 'Location Title', 'trav' ),
				'param_name'	=>	'location_title',
				'admin_label'	=>	true,
			),			
			array(
				'type'			=>	'vc_link',
				'heading'		=>	esc_html__( 'Location Link', 'trav' ),
				'param_name'	=>	'location_link',
				'description'	=>	esc_html__( 'Enter URL if you want to add a link.', 'trav' ),
			),
			array(
				'type'			=>	'attach_image',
				'heading'		=>	esc_html__( 'Location Image', 'trav' ),
				'param_name'	=>	'img_id',
				'class'			=>	'hide_in_vc_editor',
				'admin_label'	=>	false
			),
			array(
				'type'			=>	'textfield',
				'heading'		=>	esc_html__( 'Location Description', 'trav' ),
				'param_name'	=>	'location_desc',
			),
			$extra_class
		)
	) );

	if ( class_exists( 'WPBakeryShortCodesContainer' ) ) {
		class WPBakeryShortCode_Trav_Modern_Location_Grid extends WPBakeryShortCodesContainer {
		}
	}

	if ( class_exists( 'WPBakeryShortCode' ) ) {
		class WPBakeryShortCode_Trav_Modern_Location_Grid_Item extends WPBakeryShortCode {
		}
	}
}

trav_vc_shortcode_location_grid();