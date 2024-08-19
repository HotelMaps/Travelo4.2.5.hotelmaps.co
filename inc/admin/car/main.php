<?php
if ( ! defined( 'ABSPATH' ) ) exit; // Exit if accessed directly

require_once( TRAV_INC_DIR . '/admin/car/bookings-admin-panel.php' );

/*
 * remove or add columns on admin/Car list
 */
if ( ! function_exists('trav_car_set_columns') ) {
	function trav_car_set_columns( $columns ) {
		$author = $columns['author'];
		$date = $columns['date'];
		unset($columns['taxonomy-preference']);
		unset($columns['comments']);
		unset($columns['author']);
		unset($columns['date']);

		$columns['author'] = $author;
		$columns['date'] = $date;
		return $columns;
	}
}

/*
 * remove pending booking if payment is not finished in 30 mins
 */
if ( ! function_exists( 'trav_car_remove_pending_booking' ) ) {
	function trav_car_remove_pending_booking( ) {
		global $wpdb;
		// set to cancelled if someone did not finish booking in 30 mins
		$check_time = date('Y-m-d H:i:s', strtotime('-30 minutes'));
		$wpdb->query( "UPDATE " . TRAV_CAR_BOOKINGS_TABLE . " SET status = 0 WHERE status = 1 AND deposit_paid = 0 AND deposit_price > 0 AND created < '" . $check_time . "'" );
	}
}

/*
 * change to completed if start date is passed
 */
if ( ! function_exists( 'trav_car_change_booking_status' ) ) {
	function trav_car_change_booking_status( ) {
		global $wpdb;
		$where = ' WHERE 1=1';
		$where .= ' AND status=1 AND date_from < "' . date('Y-m-d') . '"';
		$wpdb->query( "UPDATE " . TRAV_CAR_BOOKINGS_TABLE . " SET status = 2" . $where );
	}
}

/*
 * update meta value when car save
 */
if ( ! function_exists( 'trav_init_car_meta' ) ) {
	function trav_init_car_meta( $post_id ) {
		if ( 'car' == get_post_type( $post_id ) ) {
			$tax = get_post_meta( $post_id, 'trav_car_tax', true );
			if ( '' == $tax ) {
				delete_post_meta( $post_id, 'trav_car_tax' );
				add_post_meta( $post_id, 'trav_car_tax', 0 );
			}

			$price = get_post_meta( $post_id, 'trav_car_price', true );
			if ( '' == $price ) {
				delete_post_meta( $post_id, 'trav_car_price' );
				add_post_meta( $post_id, 'trav_car_price', 0 );
			}

			$max_cars = get_post_meta( $post_id, 'trav_car_max_cars', true );
			if ( '' == $max_cars ) {
				delete_post_meta( $post_id, 'trav_car_max_cars' );
				add_post_meta( $post_id, 'trav_car_max_cars', 0 );
			}
		}
	}
}


add_filter( 'manage_car_posts_columns', 'trav_car_set_columns' );

add_action( 'trav_hourly_cron', 'trav_car_remove_pending_booking' );
add_action( 'trav_twicedaily_cron', 'trav_car_change_booking_status' );
add_action( 'save_post', 'trav_init_car_meta', 15 );
