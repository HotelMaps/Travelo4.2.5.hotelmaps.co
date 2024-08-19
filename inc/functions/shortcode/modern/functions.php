<?php
/**
 *	Shortcode Functions
 */
if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly.
}

/* Get image by size */
function trav_get_image( $attach_id, $img_size ) {
	if ( function_exists( 'wpb_getImageBySize' ) ) {
		$image = wpb_getImageBySize( array(
					'attach_id'		=>	$attach_id,
					'thumb_size'	=>	$img_size
				) );
		$image = $image['thumbnail'];
	} else {
		$image = wp_get_attachment_image( $attach_id, $img_size );
	}

	return $image;
}

/* Get accommodation types */
function trav_get_accommodation_types() {
	$acc_type_terms = get_terms( 'accommodation_type', array( 'hide_empty' => false	) );

	$acc_types = array( esc_html__( 'All', 'trav' ) => '' );
	if ( ! is_wp_error( $acc_type_terms ) ) {
		foreach ( $acc_type_terms as $term ) {
			$acc_types[$term->name] = $term->term_id;
		}
	}
	return $acc_types;
}

/* Get country values */
function trav_get_country_values() {
	$location_terms = get_terms( 'location', array( 'parent' => 0, 'hide_empty' => false ) );
	$countries = array( esc_html__( 'All', 'trav' ) => '' );
	if ( ! is_wp_error( $location_terms ) ) {
		foreach ( $location_terms as $term ) {
			$countries[$term->term_id] = $term->name;
		}
	}
	return $countries;
}

/* Get city values */
function trav_get_city_values() {
	$location_terms = get_terms( 'location', array( 'hide_empty' => false ) );
	$location_terms = array_filter( $location_terms, 'trav_check_term_depth_1' );
	$cities = array( esc_html__( 'All', 'trav' ) => '' );
	if ( ! is_wp_error( $location_terms ) ) {
		foreach ( $location_terms as $term ) {
			$cities[$term->term_id] = $term->name;
		}
	}
	return $cities;
}


add_filter( 'vc_autocomplete_trav_modern_accommodations_post_ids_callback', 'trav_accommodations_post_ids_autocomplete_suggestor', 10, 3 );
add_filter( 'vc_autocomplete_trav_modern_accommodations_post_ids_render', 'trav_accommodations_post_ids_autocomplete_render', 10, 3 );
