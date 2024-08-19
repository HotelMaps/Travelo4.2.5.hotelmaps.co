<?php
if ( ! defined( 'ABSPATH' ) ) exit; // Exit if accessed directly

require_once( TRAV_INC_DIR . '/admin/flight/bookings-admin-panel.php' );

/*
 * get all flights as option list
 */
if ( ! function_exists( 'trav_flight_get_flight_list' ) ) {
	function trav_flight_get_flight_list( $def_flight_id = '' ) {
		$str = '<option></option>';

		$args = array(
				'post_type'         => 'flight',
				'posts_per_page'    => -1,
				'orderby'           => 'title',
				'order'             => 'ASC'
		);
		if ( ! current_user_can( 'manage_options' ) ) {
			$args['author'] = get_current_user_id();
		}
		$flight_query = new WP_Query( $args );

		if ( $flight_query->have_posts() ) {
			while ( $flight_query->have_posts() ) {
				$flight_query->the_post();
				$selected = '';
				$id = $flight_query->post->ID;
				if ( ( $def_flight_id == $id ) ) $selected = ' selected ';
				$str .= '<option ' . esc_attr( $selected ) . 'value="' . esc_attr( $id ) .'">' . wp_kses_post( get_the_title( $id ) ) . '</option>';
			}
		}
		/* Restore original Post Data */
		wp_reset_postdata();

		return $str;
	}
}

/*
 * get air line list as option list
 */
if ( ! function_exists( 'trav_flight_get_air_line_list' ) ) {
	function trav_flight_get_air_line_list( $def_air_line='' ) {
		$str = '';
		$air_lines = get_terms( 'air_line', array( 'hide_empty' => false ) );
		if ( ! empty( $air_lines ) ){
			$str .= '<option></option>';
			if ( $air_lines ) {
				foreach( $air_lines as $st_id => $air_line ) {
					$selected = '';
					if ( $def_air_line == $air_line->term_id ) $selected = ' selected ';
					$str .= '<option ' . esc_attr( $selected ) . 'value="' . esc_attr( $air_line->term_id ) .'">' . $air_line->name . '</option>';
				}
			}
		}
		return $str;
	}
}

/*
 * get flight location list as option list
 */
if ( ! function_exists( 'trav_flight_get_flight_location_list' ) ) {
	function trav_flight_get_flight_location_list( $def_location = '' ) {
		$str = '';
		$flight_locations = get_terms( 'flight_location', array( 'hide_empty' => false ) );
		if ( ! empty( $flight_locations ) ){
			$str .= '<option></option>';
			if ( $flight_locations ) {
				foreach( $flight_locations as $st_id => $flight_location ) {
					$selected = '';
					if ( $def_location == $flight_location->term_id ) $selected = ' selected ';
					$str .= '<option ' . esc_attr( $selected ) . 'value="' . esc_attr( $flight_location->term_id ) .'">' . $flight_location->name . '</option>';
				}
			}
		}
		return $str;
	}
}