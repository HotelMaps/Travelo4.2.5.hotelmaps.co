<?php
if ( ! defined( 'ABSPATH' ) ) exit; // Exit if accessed directly

require_once( TRAV_INC_DIR . '/admin/tour/schedule-admin-panel.php' );
require_once( TRAV_INC_DIR . '/admin/tour/bookings-admin-panel.php' );

/*
 * Modify columns on admin/tour list
 */
if ( ! function_exists('trav_tour_custom_columns') ) {
	function trav_tour_custom_columns( $column, $post_id ) {
		switch ( $column ) {

			case 'location' :
				$city = get_post_meta( $post_id, 'trav_tour_city', true );
				$country = get_post_meta( $post_id, 'trav_tour_country', true );
				if ( ! empty( $city ) ) {
					if ( $city_obj = get_term_by( 'id', $city, 'location' ) ) $city = __( $city_obj->name, 'trav');
				}
				if ( ! empty( $country ) ) {
					if ( $country_obj = get_term_by( 'id', $country, 'location' ) ) $country = __( $country_obj->name, 'trav');
				}
				if ( ! empty( $city ) ) echo esc_html( $city ) . ', ';
				echo esc_html( $country );
				break;
		}
	}
}

/*
 * remove or add columns on admin/tour list
 */
if ( ! function_exists('trav_tour_set_columns') ) {
	function trav_tour_set_columns( $columns ) {
		$author = $columns['author'];
		$date = $columns['date'];
		unset($columns['taxonomy-location']);
		unset($columns['comments']);
		unset($columns['author']);
		unset($columns['date']);

		$columns['location'] = __( 'Location', 'trav' );
		$columns['author'] = $author;
		$columns['date'] = $date;
		return $columns;
	}
}

/*
 * admin enqueue script function
 */
if ( ! function_exists( 'trav_tour_admin_enqueue' ) ) {
	function trav_tour_admin_enqueue($hook) {
		if ( 'post.php' == $hook || 'post-new.php' == $hook) {
			global $post_type;
			if ( $post_type == 'tour' ) {
				wp_enqueue_script( 'trav_admin_tour_admin_js', TRAV_TEMPLATE_DIRECTORY_URI . '/inc/admin/tour/js/admin.js' );
			}
		}
	}
}

/*
 * get all tours as option list
 */
if ( ! function_exists( 'trav_tour_get_tour_list' ) ) {
	function trav_tour_get_tour_list( $def_tour_id = '' ) {
		$str = '<option></option>';

		$args = array(
				'post_type'         => 'tour',
				'posts_per_page'    => -1,
				'orderby'           => 'title',
				'order'             => 'ASC'
		);
		if ( ! current_user_can( 'manage_options' ) ) {
			$args['author'] = get_current_user_id();
		}
		$tour_query = new WP_Query( $args );

		if ( $tour_query->have_posts() ) {
			while ( $tour_query->have_posts() ) {
				$tour_query->the_post();
				$selected = '';
				$id = $tour_query->post->ID;
				if ( ( $def_tour_id == $id ) ) $selected = ' selected ';
				$str .= '<option ' . esc_attr( $selected ) . 'value="' . esc_attr( $id ) .'">' . wp_kses_post( get_the_title( $id ) ) . '</option>';
			}
		}
		/* Restore original Post Data */
		wp_reset_postdata();

		return $str;
	}
}

/*
 * get schedule type list as option list
 */
if ( ! function_exists( 'trav_tour_get_schedule_type_list' ) ) {
	function trav_tour_get_schedule_type_list( $tour_id, $def_st='' ) {
		$str = '';
		$schedule_types = trav_tour_get_schedule_types( $tour_id );
		if ( ! empty( $schedule_types ) ){
			$str .= '<option></option>';
			if ( $schedule_types ) {
				foreach( $schedule_types as $st_id => $schedule_type ) {
					$selected = '';
					if ( $def_st == $st_id ) $selected = ' selected ';
					$str .= '<option ' . esc_attr( $selected ) . 'value="' . esc_attr( $st_id ) .'">' . $schedule_type['title'] . '</option>';
				}
			}
		}
		return $str;
	}
}

if ( ! function_exists( 'trav_ajax_tour_get_schedule_type' ) ) {
	function trav_ajax_tour_get_schedule_type() {
		if ( isset( $_POST['tour_id'] ) ) {
			echo trav_tour_get_schedule_type_list( $_POST['tour_id'] );
		}
		exit();
	}
}

/*
 * remove pending booking if payment is not finished in 30 mins
 */
if ( ! function_exists( 'trav_tour_remove_pending_booking' ) ) {
	function trav_tour_remove_pending_booking( ) {
		global $wpdb;
		// set to cancelled if someone did not finish booking in 30 mins
		$check_time = date('Y-m-d H:i:s', strtotime('-30 minutes'));
		$wpdb->query( "UPDATE " . TRAV_TOUR_BOOKINGS_TABLE . " SET status = 0 WHERE status = 1 AND deposit_paid = 0 AND deposit_price > 0 AND created < '" . $check_time . "'" );
	}
}

/*
 * change to completed if start date is passed
 */
if ( ! function_exists( 'trav_tour_change_booking_status' ) ) {
	function trav_tour_change_booking_status( ) {
		global $wpdb;
		$where = ' WHERE 1=1';
		$where .= ' AND status=1 AND tour_date < "' . date('Y-m-d') . '"';
		$wpdb->query( "UPDATE " . TRAV_TOUR_BOOKINGS_TABLE . " SET status = 2" . $where );
	}
}

/*
 * update meta value when tour save
 */
if ( ! function_exists( 'trav_init_tour_meta' ) ) {
	function trav_init_tour_meta( $post_id ) {
		if ( 'tour' == get_post_type( $post_id ) ) {
			$avg_price = get_post_meta( $post_id, 'trav_tour_min_price', true );
			if ( '' == $avg_price ) {
				delete_post_meta( $post_id, 'trav_tour_min_price' );
				add_post_meta( $post_id, 'trav_tour_min_price', 0 );
			}
			/*$review = get_post_meta( $post_id, 'review', true );
			if ( '' == $review ) {
				delete_post_meta( $post_id, 'review' );
				add_post_meta( $post_id, 'review', 0 );
			}*/
		}
	}
}

add_action( 'manage_tour_posts_custom_column' , 'trav_tour_custom_columns', 10, 2 );
add_action( 'save_post', 'trav_init_tour_meta', 15 );
add_action( 'admin_enqueue_scripts', 'trav_tour_admin_enqueue' );
add_filter( 'manage_tour_posts_columns', 'trav_tour_set_columns' );
add_action( 'trav_hourly_cron', 'trav_tour_remove_pending_booking' );
add_action( 'trav_twicedaily_cron', 'trav_tour_change_booking_status' );

/* ajax */
add_action( 'wp_ajax_tour_get_schedule_type', 'trav_ajax_tour_get_schedule_type' );