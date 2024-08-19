<?php
if ( ! defined( 'ABSPATH' ) ) exit; // Exit if accessed directly

/*
 * get current site style
 */
if ( ! function_exists( 'trav_get_current_style' ) ) {
	function trav_get_current_style() {
		global $trav_options;

		if ( empty( $trav_options ) ) {
			$trav_options = get_option( 'travelo' );
		}

		$website_style = 'default';
		if ( ! empty( $trav_options['website_style'] ) ) {
			$website_style = $trav_options['website_style'];        
		}
		
		return $website_style;
	}
}

if ( ! function_exists( 'trav_get_opt' ) ) {
	function trav_get_opt( $slug, $default = false ) {
		global $trav_options;

		$trav_opt_val = isset( $trav_options[$slug] ) ? $trav_options[$slug] : '';

		if ( empty( $trav_opt_val ) && ! empty( $default ) ) {
			$trav_opt_val = $default;
		}

		return $trav_opt_val;
	}
}

if ( ! function_exists( 'trav_modern_enqueue_scripts' ) ) {
	function trav_modern_enqueue_scripts() {

		$suffix = WP_DEBUG ? '' : '.min';

		if ( is_multisite() ) {
			$blog_id = get_current_blog_id();
			$file_name = 'theme' . $blog_id . '.css';
		} else {
			$file_name = 'theme.css';
		}
		
		wp_enqueue_style( 'trav-google-fonts', trav_google_fonts(), array(), '', 'all' );
		wp_enqueue_style( 'bootstrap', TRAV_TEMPLATE_DIRECTORY_URI . '/css/modern/bootstrap.min.css', NULL, '4.1.3', 'all' );
		wp_enqueue_style( 'font-awesome-5', TRAV_TEMPLATE_DIRECTORY_URI . '/css/modern/font-awesome/css/all.min.css', NULL, '5.9.0', 'all' );
		wp_enqueue_style( 'font-travelo', TRAV_TEMPLATE_DIRECTORY_URI . '/css/modern/font-travelo/css/font-travelo.css', NULL, '1.0', 'all' );
		wp_enqueue_style( 'jquery-ui', TRAV_TEMPLATE_DIRECTORY_URI . '/css/modern/jquery-ui.min.css', NULL, '1.21.1', 'all' );
		wp_enqueue_style( 'trav-main-style', TRAV_TEMPLATE_DIRECTORY_URI . '/css/modern/' . $file_name );
		wp_enqueue_style( 'trav_child_theme_css', get_stylesheet_directory_uri() . '/style.css' ); //register default style.css file. only include in childthemes. has no purpose in main theme

		$custom_css = trav_get_custom_css();
		
		// custom css
		wp_add_inline_style( 'trav_style_custom', $custom_css );

		wp_enqueue_script( 'jquery' );
		wp_enqueue_script( 'jquery-ui-core', false, array('jquery') );
		wp_enqueue_script( 'jquery-ui-slider', false, array('jquery'));
		wp_enqueue_script( 'bootstrap', TRAV_TEMPLATE_DIRECTORY_URI . '/js/modern/bootstrap.bundle.min.js', array( 'jquery' ), '4.1.3', true );
		wp_enqueue_script( 'jquery-moment', TRAV_TEMPLATE_DIRECTORY_URI . '/js/modern/moment.min.js', array( 'jquery' ), '1.0', true );
		wp_enqueue_script( 'jquery-validate', TRAV_TEMPLATE_DIRECTORY_URI . '/js/jquery.validate.min.js', array( 'jquery' ), '', true );
		wp_enqueue_script( 'date-range-picker', TRAV_TEMPLATE_DIRECTORY_URI . '/js/modern/daterangepicker' . $suffix . '.js', array( 'jquery' ), '3.0.5', true );
		wp_enqueue_script( 'owl-carousel', TRAV_TEMPLATE_DIRECTORY_URI . '/js/modern/owl.carousel' . $suffix . '.js', array( 'jquery' ), '2.3.4', true );
		wp_enqueue_script( 'slick', TRAV_TEMPLATE_DIRECTORY_URI . '/js/modern/slick' . $suffix . '.js', array( 'jquery' ), '2.3.4', true );
		wp_enqueue_script( 'trav_script_main_script', TRAV_TEMPLATE_DIRECTORY_URI . '/js/modern/scripts' . $suffix . '.js', array( 'jquery' ), '', true );

		if ( ! empty( $trav_options['map_api_key'] ) ) { 
            wp_enqueue_script( 'google_map', '//maps.googleapis.com/maps/api/js?key=' . $trav_options['map_api_key'], array(), '3.0', true );
        } else { 
            wp_enqueue_script( 'google_map', '//maps.googleapis.com/maps/api/js?v=3.exp&sensor=false', array(), '3.0', true );
        }

		wp_enqueue_script( 'google-map-marker-clusterer', TRAV_TEMPLATE_DIRECTORY_URI . '/js/modern/markerclusterer' . $suffix . '.js', array( 'jquery' ), '', true );
		wp_enqueue_script( 'trav_script_map', TRAV_TEMPLATE_DIRECTORY_URI . '/js/modern/map_listing.js', array( 'jquery' ), '', true );
        wp_enqueue_script( 'google-map-infobox', TRAV_TEMPLATE_DIRECTORY_URI . '/js/modern/infobox' . $suffix . '.js', array( 'jquery' ), '', true );
        

		
        wp_localize_script( 'trav_script_main_script', 'themeurl', TRAV_TEMPLATE_DIRECTORY_URI );
        wp_localize_script( 'trav_script_main_script', 'date_format', trav_get_date_format('js') );
        wp_localize_script( 'trav_script_main_script', 'ajaxurl', admin_url( 'admin-ajax.php' ) );

		if ( is_singular( 'accommodation' ) ) {
            $acc_data = trav_get_acc_js_data();
            wp_enqueue_script( 'trav_script_accommodation', TRAV_TEMPLATE_DIRECTORY_URI . '/js/modern/accommodation.js', array( 'jquery' ), '', true );
            wp_localize_script( 'trav_script_accommodation', 'acc_data', $acc_data );
            //wp_localize_script( 'trav_script_accommodation', 'ajaxurl', admin_url( 'admin-ajax.php' ) );
            
        }
	}
}

if ( ! function_exists( 'trav_google_fonts' ) ) {
	function trav_google_fonts() {
		$fonts_url = '';

		$body_font = trav_get_opt( 'body_font_family' );
		if ( ! empty( $body_font['font-family'] ) ) {
			$font_name = $body_font['font-family'];
		} else {
			$font_name = 'Barlow';
		}
		$default_google_fonts = $font_name . ':100,200,300,400,400i,500,600,700,800,900';
		
		$query_args = array( 
			'family'	=>	$default_google_fonts
		);

		$fonts_url = add_query_arg( $query_args, '//fonts.googleapis.com/css' );

		return esc_url_raw( $fonts_url );
	}
}

if ( ! function_exists( 'trav_modern_get_review_count' ) ) {
	function trav_modern_get_review_count( $post_id ) {
		global $wpdb;

		$sql = "SELECT COUNT(*) FROM " . TRAV_REVIEWS_TABLE . " WHERE post_id='" . esc_sql( $post_id ) . "' AND status='1'";
		$result = $wpdb->get_var( $sql );

		return $result;
	}
}
/*
 * get review text based on review rating
 */
if ( ! function_exists( 'trav_get_review_based_text' ) ) {
	function trav_get_review_based_text( $review ) {
		$review_base_text = array(
							'4.75'	=> esc_html__( 'exceptional', 'trav' ),
							'4.5'	=> esc_html__( 'wonderful', 'trav' ),
							'4'		=> esc_html__( 'very good', 'trav' ),
							'3.5'	=> esc_html__( 'good', 'trav' ),
							'3'		=> esc_html__( 'pleasant', 'trav' ),
							'0'		=> esc_html__( 'disappointed', 'trav' ),
		);

		foreach( $review_base_text  as $key => $text ) {
			if ( $key <= $review ) {
				return $text;
			}
		}

		return '';
	}
}
/*
 * get post gallery function
 */
if ( ! function_exists('trav_blog_morden_gallery') ) {
    function trav_blog_morden_gallery( $post_id ) {
        $isv_setting = get_post_meta( $post_id, 'trav_post_media_type', true );
        $img_size = 'modern-blog-thumb';

        if ( is_single() ) {
        	$img_size = 'modern-post-thumb';
        }
        
        if ( empty( $isv_setting ) || $isv_setting == 'img' ) {
            if ( '' == get_the_post_thumbnail() ) {
                $isv_setting = '';
                return false;
            } else {
                echo '<figure class="image-container">';
                echo get_the_post_thumbnail( $post_id, $img_size );
                echo '</figure>';
            }
        } elseif ( $isv_setting == 'sld' ) {
            $gallery_imgs = get_post_meta( $post_id, 'trav_gallery_imgs' );
            
            if ( empty( $gallery_imgs ) ) {
                $isv_setting = '';
                return false;
            } else {
                $img_empty = true;

                foreach ( $gallery_imgs as $gallery_img ) {
                    $src = wp_get_attachment_image_src( $gallery_img, $img_size );
                    if ( !empty( $src ) ) {
                        $img_empty = false;
                        break;
                    }
                }

                if ( $img_empty ) {
                    $isv_setting = '';
                    return false;
                }

                $gallery_type = get_post_meta( $post_id, 'trav_post_gallery_type', true );
                ?>
                <div class="photo-gallery-wrapper">
                    <div class="gallery-inner owl-carousel">
                    	<div class="gallery-item"><?php echo get_the_post_thumbnail( $post_id, $img_size ); ?></div>
                        <?php foreach ( $gallery_imgs as $gallery_img ) {
                            echo '<div class="gallery-item">' . wp_get_attachment_image( $gallery_img, $img_size ) . '</div>';
                        } ?>
                    </div>
                </div>
                <?php
            }
        } elseif ( $isv_setting == 'video' ) {
            $video_code = get_post_meta( $post_id, 'trav_post_video', true );
            $video_width = get_post_meta( $post_id, 'trav_post_video_width', true );
            
            if ( empty( $video_code ) ) {
                $isv_setting = '';
            } else { ?>
                <div class="video-container">
                    <div <?php if ( ! empty( $video_width ) ) echo 'class="full-video"' ?>>
                        <?php echo do_shortcode( $video_code ); ?>
                    </div>
                </div>
            <?php }
        }
    }
}

if ( ! function_exists( 'trav_modern_page_heading' ) ) {
	function trav_modern_page_heading() {
		global $post;

		$page_id = trav_modern_get_page_id();

		$banner_type = get_post_meta( $page_id, 'trav_page_banner_type', true );
		$slider_active = get_post_meta( $page_id, 'trav_page_slider', true );
		$slider        = ( $slider_active == '' ) ? 'Deactivated' : $slider_active;
		if ( class_exists( 'RevSlider' ) && $banner_type == 'revslider' && $slider != 'Deactivated' ) {
			echo '<div id="slideshow">';
			putRevSlider( $slider );
			echo '</div>';
		} else if ( $banner_type == 'html_block') {
			$header_html_block = get_post_meta( $page_id, 'trav_page_header_html_block', true );
			
			if ( ! empty( $header_html_block ) ) {
				echo '<div id="header_banner_html_block_wrapper">';
				echo do_shortcode( '[html_block block_id="' . $header_html_block . '"]' );
				echo '</div>';
			}
		}
	}
}

/* Get current page ID */
if ( ! function_exists( 'trav_modern_get_page_id' ) ) {
	function trav_modern_get_page_id() {
		global $post;

		$page_id = 0;
		$page_for_posts = get_option( 'page_for_posts' );
		
		if ( isset( $post->ID ) ) {
			$page_id = $post->ID;
		}

		if ( trav_modern_is_blog_archive() || is_404() ) {
			$page_id = $page_for_posts;
		}

		return $page_id;
	}
}

/* Return bool by checking if current page is blog page */ 
if( ! function_exists( 'trav_modern_is_blog_archive' ) ) {
	function trav_modern_is_blog_archive() {
		return ( is_home() || is_search() || is_tag() || is_category() || is_date() || is_author() );
	}
}