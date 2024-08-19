<?php
/**
 * Accommodation Shortcode
 */
if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly
}

// [trav_modern_accommodations]
function trav_shortcode_accommodations( $atts, $content = null ) {
	
	$variables = array(
						'style'			=> 'style1', //style1, style2
						'title'			=> '',
						'subtitle'		=> '',
						'description'	=> '',
						'button_title'	=> '',
						'button_link'	=> '',
						'bg_img_id'		=> '',
						'type'			=> 'latest',
						'city'			=> '',
						'country'		=> '',
						'acc_type'		=> '',
						'post_ids'		=> '',
						'count'			=> 10,
						'extra_class'	=> '',
						'css'			=> '',
					);
	extract( shortcode_atts( $variables, $atts ) );

	$link = vc_build_link($button_link);
	if ( !empty($link['url'])) {
		$button_link = $link['url'];
	}

	$types = array( 'latest', 'featured', 'popular', 'hot', 'selected' );
	if ( ! in_array( $type, $types ) ) {
		$type = 'latest';
	}
	$post_ids = explode( ',', $post_ids );
	$acc_type = ( ! empty( $acc_type ) ) ? explode( ',', $acc_type ) : array();
	$count = is_numeric( $count )? $count : 10;

	$id = rand( 100, 9999 );
	$shortcode_id = uniqid( 'trav-accommodation-' . $id );

	$accs = array();
	if ( $type == 'selected' ) {
		$accs = trav_acc_get_accs_from_id( $post_ids );
	} elseif ( $type == 'hot' ) {
		$accs = trav_acc_get_hot_accs( $count, $country, $city, $acc_type );
	} else {
		$accs = trav_acc_get_special_accs( $type, $count, array(), $country, $city, $acc_type );
	}

	$content_class = '';
	if ( ! empty( $extra_class ) ) {
		$content_class .= ' ' . $extra_class;
	}

	if ( ! empty( $css ) && function_exists( 'vc_shortcode_custom_css_class' ) ) {
		$content_class .= ' ' . vc_shortcode_custom_css_class( $css );
	}

	ob_start();	
	if ( $style == 'style1' ) {
	?>
		<div id="<?php echo esc_attr( $shortcode_id ); ?>" class="style1 accommodation-shortcode-wapper featured-packages-section hotel-package-section <?php echo esc_attr( $content_class ); ?>" style="background-image: url('<?php echo wp_get_attachment_image_url( $bg_img_id, 'full' ); ?>');">
			<div class="packages-section-inner container">
				<div class="section-description">
					<span class="subtitle"><?php echo esc_html__( $subtitle ); ?></span>
					<h2 class="title"><?php echo esc_html__( $title ); ?></h2>
					<p class="desc"><?php echo esc_html__( $description ); ?></p>
					<a href="<?php echo esc_url( $button_link ); ?>" class="hotels-link"><?php echo esc_html__( $button_title ); ?> <i class="fas fa-angle-double-right"></i></a>
				</div>

				<div class="travel-package-carousel-wrap">
					<div class="package-carousel-inner">
						<div class="main-carousel-stage owl-carousel">
							<?php
							foreach ( $accs as $acc ) {
								echo trav_modern_get_acc_grid_single( $acc->ID );
							}							
							?>
						</div>
					</div>
				</div>
			</div>
		</div>
	<?php
	} else {
	?>
		<div id="<?php echo esc_attr( $shortcode_id ); ?>" class="style2 accommodation-shortcode-wapper similar-detail-slide <?php echo esc_attr( $content_class ); ?>">
			<div class="available-travel-package-wrap owl-carousel">
				<?php
				foreach ( $accs as $acc ) {
					echo trav_modern_get_acc_grid_single( $acc->ID );
				}							
				?>
			</div>
		</div>
	<?php
	}
	
	$output = ob_get_contents();
	ob_end_clean();
	
	return $output;
}

add_shortcode( 'trav_modern_accommodations', 'trav_shortcode_accommodations' );

/**
 * WPBakery
 */
function trav_vc_shortcode_accommodations() {

	$extra_class = trav_extra_class_field();

	vc_map( array(
		'name'			=>	esc_html__( 'Accommodations', 'trav' ),
		'base'			=>	'trav_modern_accommodations',
		'icon'			=>	'trav-js-composer',
		'category'		=>	esc_html__( 'by C-Themes', 'trav' ),
		'description'	=>	esc_html__( 'Show accommodations in your page.', 'trav' ),
		'params'		=>	array(
			array(
				'type'			=> 'dropdown',
				'heading'		=> esc_html__( 'Style', 'trav' ),
				'admin_label'	=> true,
				'param_name'	=> 'style',
				'value'			=> array(
					esc_html__( 'Style 1', 'trav' )		=> 'style1',
					esc_html__( 'Style 2', 'trav' )	=> 'style2',
				),
				'std'			=> 'style1',
				'description'	=> ''
			),
			array(
				'type'			=>	'textfield',
				'heading'		=>	esc_html__( 'Title', 'trav' ),
				'param_name'	=>	'title',
				'admin_label'	=>	true,
				'dependency'	=> array(
					'element'		=> 'style',
					'value'			=> array( 'style1' )
				),
			),	
			array(
				'type'			=>	'textfield',
				'heading'		=>	esc_html__( 'Subtitle', 'trav' ),
				'param_name'	=>	'subtitle',
				'dependency'	=> array(
					'element'		=> 'style',
					'value'			=> array( 'style1' )
				),
			),	
			array(
				'type'			=>	'textfield',
				'heading'		=>	esc_html__( 'Description', 'trav' ),
				'param_name'	=>	'description',
				'dependency'	=> array(
					'element'		=> 'style',
					'value'			=> array( 'style1' )
				),
			),	
			array(
				'type'			=>	'textfield',
				'heading'		=>	esc_html__( 'Button Title', 'trav' ),
				'param_name'	=>	'button_title',
				'dependency'	=> array(
					'element'		=> 'style',
					'value'			=> array( 'style1' )
				),
			),
			array(
				'type'			=>	'vc_link',
				'heading'		=>	esc_html__( 'Button Link', 'trav' ),
				'param_name'	=>	'button_link',
				'description'	=>	esc_html__( 'Enter URL if you want to add a link.', 'trav' ),
				'dependency'	=> array(
					'element'		=> 'style',
					'value'			=> array( 'style1' )
				),
			),
			array(
				'type'			=>	'attach_image',
				'heading'		=>	esc_html__( 'Background Image', 'trav' ),
				'param_name'	=>	'bg_img_id',
				'dependency'	=> array(
					'element'		=> 'style',
					'value'			=> array( 'style1' )
				),
			),
			array(
				'type'			=> 'dropdown',
				'heading'		=> esc_html__( 'Type', 'trav' ),
				'admin_label'	=> true,
				'param_name'	=> 'type',
				'value'			=> array(
					esc_html__( 'Latest', 'trav' )		=> 'latest',
					esc_html__( 'Featured', 'trav' )	=> 'featured',
					esc_html__( 'Popular', 'trav' )		=> 'popular',
					esc_html__( 'Hot', 'trav' )			=> 'hot',
					esc_html__( 'Selected', 'trav' )	=> 'selected',
				),
				'std'			=> 'latest',
				'description'	=> ''
			),
			array(
				'type'			=> 'textfield',
				'heading'		=> esc_html__( 'Count of Accommodations', 'trav' ),
				'param_name'	=> 'count',
				'value'			=> '10',
			),
			array(
				'type'			=> 'checkbox',
				'heading'		=> esc_html__( 'Accommodation Type', 'trav' ),
				'param_name'	=> 'acc_type',
				'value'			=> trav_get_accommodation_types(),
				'dependency'	=> array(
					'element'		=> 'type',
					'value'			=> array( 'latest', 'featured', 'popular', 'hot' )
				),
				'std'			=> "",
			),
			array(
				'type'			=> 'dropdown',
				'heading'		=> esc_html__( 'Country', 'trav' ),
				'param_name'	=> 'country',
				'value'			=> trav_get_country_values(),
				'dependency'	=> array(
					'element'		=> 'type',
					'value'			=> array( 'latest', 'featured', 'popular', 'hot' )
				)
			),
			array(
				'type'			=> 'dropdown',
				'heading'		=> esc_html__( 'City', 'trav' ),
				'param_name'	=> 'city',
				'value'			=> trav_get_city_values(),
				'dependency'	=> array(
					'element'		=> 'type',
					'value'			=> array( 'latest', 'featured', 'popular', 'hot' )
				)
			),
			array(
				'type'			=> 'autocomplete',
				'heading'		=> esc_html__( 'Accommodation IDs', 'trav' ),
				'param_name'	=> 'post_ids',
				'settings'		=> array(
					'multiple'		=> true,
					'sortable'		=> true,
				),
				'save_always'	=> true,
				'description' 	=> esc_html__( 'Please select accommodations you want to show.', 'trav' ),
				"dependency"	=> array(
					'element'		=> 'type',
					'value'			=> array( 'selected' )
				)
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

trav_vc_shortcode_accommodations();