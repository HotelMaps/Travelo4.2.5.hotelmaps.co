<?php
/**
 * Testimonials Shortcode
 */
if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly
}

// [trav_modern_testimonials]
function trav_shortcode_testimonials( $atts, $content = null ) {
	
	$variables = array(
						//'style'			=> 'style1', //style1, style2
						'title'			=> '',
						'subtitle'		=> '',
						'bg_img_id'		=> '',
						'extra_class'	=> '',
						'css'			=> '',
					);
	extract( shortcode_atts( $variables, $atts ) );

	
	$id = rand( 100, 9999 );
	$shortcode_id = uniqid( 'trav-testimonials-' . $id );

	$content_class = '';
	if ( ! empty( $extra_class ) ) {
		$content_class .= ' ' . $extra_class;
	}

	if ( ! empty( $css ) && function_exists( 'vc_shortcode_custom_css_class' ) ) {
		$content_class .= ' ' . vc_shortcode_custom_css_class( $css );
	}

	ob_start();	
	?>
		<div id="<?php echo esc_attr( $shortcode_id ); ?>" class="style1 testimonials-section section-wrapper <?php echo esc_attr( $content_class ); ?>" style="background-image: url('<?php echo wp_get_attachment_image_url( $bg_img_id, 'full' ); ?>');">
			<div class="testimonials-section-inner container">
				<div class="main-title-wrap">
					<span class="sub-title"><?php echo esc_html__( $subtitle ); ?></span>
					<h2 class="main-title"><?php echo esc_html__( $title ); ?></h2>
				</div>

				<div class="testimonials-carousel">
					<div class="carousel-wrapper owl-carousel" data-col="3">
						<?php echo do_shortcode( $content ); ?>			
					</div>
				</div>
			</div>
		</div>
	<?php
	
	$output = ob_get_contents();
	ob_end_clean();
	
	return $output;
}

add_shortcode( 'trav_modern_testimonials', 'trav_shortcode_testimonials' );

// [trav_modern_testimonial]
function trav_shortcode_testimonial( $atts, $content = null ) {
	extract( shortcode_atts( array(
									'author_name'			=>	'',
									'author_photo'			=>	'',
									'author_job'			=>	'',
									'review_rate'			=>	5,
									'short_description'		=>	'',
									'extra_class'			=>	''
								), $atts ) );

	$review_rate = floatval( $review_rate );
	if ( $review_rate > 5 ) {
		$review_rate = 5;
	}

	ob_start();
	?>
		<div class="single-testimonial">
			<div class="testimonial-inner">
				<div class="rating-section">
					<span class="rating-review"><span class="value"><?php echo number_format( $review_rate, 1 ); ?></span></span>
					<div class="five-stars-container">
						<span class="five-stars" style="width: <?php echo esc_attr( $review_rate * 20 ); ?>%;"></span>
					</div>
					<span class="rating-txt"><?php echo trav_get_review_based_text( $review_rate ); ?></span>
				</div>

				<div class="info-section">
					<p class="testimonial-desc"><?php echo esc_html( $short_description ); ?></p>
					<div class="author-info">
						<?php
						if ( ! empty( $author_photo ) && is_numeric( $author_photo ) ) {
							echo trav_get_image( $author_photo, '50x50' );
						}
						?>

						<span class="author-name-job">
							<span class="name"><?php echo esc_html( $author_name ); ?></span>
							<span class="job"><?php echo esc_html( $author_job ); ?></span>
						</span>
					</div>
				</div>
			</div>
		</div>
	<?php
	$html = ob_get_clean();

	return $html;
}

add_shortcode( 'trav_modern_testimonial', 'trav_shortcode_testimonial' );

/**
 * WPBakery
 */
function trav_vc_shortcode_testimonials() {

	$extra_class = trav_extra_class_field();

	vc_map( array(
		'name'				=>	esc_html__( 'Testimonials', 'trav' ),
		'base'				=>	'trav_modern_testimonials',
		'icon'				=>	'trav-js-composer',
		'category'			=>	esc_html__( 'by C-Themes', 'trav' ),
		'description'		=>	esc_html__( 'Show testimonials in your page.', 'trav' ),
		'as_parent'			=>	array( 'only' => 'trav_modern_testimonial' ),
		'js_view'			=>	'VcColumnView',
		'default_content'	=>	'[trav_modern_testimonial][/trav_modern_testimonial]',
		'params'			=>	array(
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
			array(
				'type'			=>	'attach_image',
				'heading'		=>	esc_html__( 'Background Image', 'trav' ),
				'param_name'	=>	'bg_img_id',
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

	vc_map( array(
		'name'				=>	esc_html__( 'Testimonial', 'trav' ),
		'base'				=>	'trav_modern_testimonial',
		'icon'				=>	'trav-js-composer',
		'category'			=>	esc_html__( 'by C-Themes', 'trav' ),
		'as_child'			=>	array( 'only' => 'trav_modern_testimonials' ),
		'params'			=>	array(
			array(
				'type'			=>	'textfield',
				'heading'		=>	esc_html__( 'Author Name', 'trav' ),
				'param_name'	=>	'author_name',
				'admin_label'	=>	true
			),
			array(
				'type'			=>	'checkbox',
				'param_name'	=>	'enable_author_photo',
				'value'			=>	array(
					esc_html__( 'Enable Author Photo', 'trav' ) => 'yes'
				),
				'std'			=>	'yes',
				'admin_label'	=>	false
			),
			array(
				'type'			=>	'attach_image',
				'heading'		=>	esc_html__( 'Author Photo', 'trav' ),
				'param_name'	=>	'author_photo',
				'class'			=>	'hide_in_vc_editor',
				'dependency'	=>	array(
					'element'	=>	'enable_author_photo',
					'value'		=>	'yes'
				),
				'admin_label'	=>	false
			),
			array(
				'type'			=>	'textfield',
				'heading'		=>	esc_html__( 'Author Job', 'trav' ),
				'param_name'	=>	'author_job',
				'admin_label'	=>	true
			),
			array(
				'type'			=>	'textfield',
				'heading'		=>	esc_html__( 'Review Rate', 'trav' ),
				'descrition'	=>	esc_html__( 'It should be decimal and should maximum is 5.0 such as 4.8, 5.0, etc.', 'trav' ),
				'param_name'	=>	'review_rate',
				'admin_label'	=>	true
			),
			array(
				'type'			=>	'textarea',
				'heading'		=>	esc_html__( 'Description', 'trav' ),
				'param_name'	=>	'short_description',
				'admin_label'	=>	false
			),
			$extra_class
		)
	) );

	if ( class_exists( 'WPBakeryShortCodesContainer' ) ) {
		class WPBakeryShortCode_Trav_Modern_Testimonials extends WPBakeryShortCodesContainer {
		}
	}

	if ( class_exists( 'WPBakeryShortCode' ) ) {
		class WPBakeryShortCode_Trav_Modern_Testimonial extends WPBakeryShortCode {
		}
	}
}

trav_vc_shortcode_testimonials();