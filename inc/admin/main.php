<?php
if ( ! defined( 'ABSPATH' ) ) exit; // Exit if accessed directly

require_once( TRAV_INC_DIR . '/admin/admin_pages/admin.php' );
require_once( TRAV_INC_DIR . '/admin/currencies-admin-panel.php' );
require_once( TRAV_INC_DIR . '/admin/reviews-admin-panel.php' );
require_once( TRAV_INC_DIR . '/admin/accommodation/main.php' );
require_once( TRAV_INC_DIR . '/admin/tour/main.php' );
require_once( TRAV_INC_DIR . '/admin/car/main.php' );
require_once( TRAV_INC_DIR . '/admin/cruise/main.php' );
require_once( TRAV_INC_DIR . '/admin/flight/main.php' );

/*
 * admin notice hook function
 */
if ( ! function_exists('trav_admin_notice') ) {
	function trav_admin_notice() {
		/*$installed = get_option( 'install_trav_pages' );
		if ( empty( $installed ) && ( empty( $_GET['install_trav_pages'] ) && empty( $_GET['skip_trav_pages'] ) ) ) {
			echo '<div class="updated"><p>' . esc_html__( 'Welcome to Travelo - You\'re almost ready to launch.', 'trav' ) . '</p><p><a class="button-primary" href="' . esc_url( admin_url( 'admin.php?page=theme_options&install_trav_pages=true' ) ) . '">' . esc_html__( 'Install Main Pages', 'trav' ) . '</a> <a href="' . esc_url( admin_url( 'admin.php?page=theme_options&skip_trav_pages=true' ) ) . '" class="skip-setup">' . esc_html__( 'Skip setup', 'trav' ) . '</a></p></div>';
		}
		if ( ! get_option('permalink_structure') ) {
			echo '<div class="updated"><p>' . esc_html__( 'Please change your permalink setting to Post name. We strongly recommended that.', 'trav' ) . '</p><p><a class="button-primary" href="' . esc_url( admin_url( 'options-permalink.php' ) ) . '">' . esc_html__( 'Edit Permalink Settings', 'trav' ) . '</a></p></div>';
		}*/
	}
}


/*
 * get cities list from country id
 */
if ( ! function_exists( 'trav_ajax_get_cities_in_country' ) ) {
	function trav_ajax_get_cities_in_country() {
		echo '<option></option>';
		if ( ! empty( $_POST['country_id'] ) ) {
			$child_terms = get_term_children( sanitize_text_field( $_POST['country_id'] ), 'location' );
			foreach ( $child_terms as $term_id ) {
				$term = get_term( $term_id, 'location' );
				echo '<option value="' . esc_attr( $term->term_id ) .'">' . esc_html( $term->name ) . '</option>';
			}
		} else {
			$terms = get_terms( 'location', array('hide_empty'=>0) );
			$terms = array_filter($terms, 'trav_check_term_depth_1');
			foreach ( $terms as $term ) {
				echo '<option value="' . esc_attr( $term->term_id ) .'">' . esc_attr( $term->name ) . '</option>';
			}
		}
		exit();
	}
}

/*
 * get country id from city id
 */
if ( ! function_exists( 'trav_ajax_get_country_from_city' ) ) {
	function trav_ajax_get_country_from_city() {
		$city = get_term( sanitize_text_field( $_POST['city_id'] ), 'location' );
		if ( ! empty( $city ) ) {
			echo esc_js( $city->parent );
		}
		exit();
	}
}

add_action( 'admin_notices', 'trav_admin_notice' );

add_action( 'wp_ajax_get_cities_in_country', 'trav_ajax_get_cities_in_country' );
add_action( 'wp_ajax_get_country_from_city', 'trav_ajax_get_country_from_city' );