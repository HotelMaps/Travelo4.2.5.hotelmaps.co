<?php
if ( ! defined( 'ABSPATH' ) ) exit; // Exit if accessed directly

require_once( TRAV_INC_DIR . '/admin/accommodation/vacancies-admin-panel.php' );
require_once( TRAV_INC_DIR . '/admin/accommodation/bookings-admin-panel.php' );

/*
 * get accommodation room list from accommodation id
 */
if ( ! function_exists( 'trav_ajax_acc_get_acc_room_list' ) ) {
	function trav_ajax_acc_get_acc_room_list() {

		$args = array(
				'post_type'         => 'room_type',
				'posts_per_page'    => -1,
				'orderby'           => 'title',
				'order'             => 'ASC',
		);

		if ( ! empty( $_POST['accommodation_id'] ) ) {
			$accommodation_id = trav_acc_clang_id( $_POST['accommodation_id'] );
			$args['meta_query'] = array(
					array(
						'key'     => 'trav_room_accommodation',
						'value'   => sanitize_text_field( $accommodation_id ),
					),
				);
		}

		echo '<option></option>';
		$room_type_query = new WP_Query( $args );
		if ( $room_type_query->have_posts() ) {
			while ( $room_type_query->have_posts() ) {
				$room_type_query->the_post();
				$id = $room_type_query->post->ID;
				echo '<option value="' . esc_attr( $id ) .'">' . wp_kses_post( get_the_title( $id ) ) . '</option>';
			}
		}
		wp_reset_postdata();

		exit();
	}
}

/*
 * get accommodation id from room type id
 */
if ( ! function_exists( 'trav_ajax_acc_get_room_acc_id' ) ) {
	function trav_ajax_acc_get_room_acc_id() {
		if ( isset( $_POST['room_id'] ) ) {
			$acc_id = get_post_meta( sanitize_text_field( $_POST['room_id'] ), 'trav_room_accommodation', true );
			echo esc_js( $acc_id );
		} else {
			//
		}
		exit();
	}
}

/*
 * add accommodation filter to admin/room_type list
 */
if ( ! function_exists('trav_acc_table_filtering') ) {
	function trav_acc_table_filtering() {
		global $wpdb;
		if ( isset( $_GET['post_type'] ) && 'room_type' == $_GET['post_type'] ) {
			$accs = get_posts( array( 'post_type'=>'accommodation', 'posts_per_page'=>-1, 'orderby'=>'post_title', 'order'=>'ASC', 'suppress_filters'=>0 ) );
			echo '<select name="acc_id">';
			echo '<option value="">' . esc_html__( 'All Accommodations', 'trav' ) . '</option>';
			foreach( $accs as $acc ) {
				$selected = ( ! empty( $_GET['acc_id'] ) AND $_GET['acc_id'] == $acc->ID ) ? 'selected="selected"' : '';
				echo '<option value="' . esc_attr( $acc->ID ) . '" ' . esc_attr( $selected ) . '>' . esc_html( $acc->post_title ) . '</option>';
			}
			echo '</select>';
		}
	}
}

/*
 * add accommodation filter to admin/room_type list
 */
if ( ! function_exists('trav_admin_filter_room_type') ) {
	function trav_admin_filter_room_type( $query ) {
		global $pagenow;
		$qv = &$query->query_vars;
		if ( $pagenow=='edit.php' && isset($qv['post_type']) && $qv['post_type']=='room_type' && !empty($_GET['acc_id']) && is_numeric($_GET['acc_id']) ) {
			$qv['meta_key'] = 'trav_room_accommodation';
			$qv['meta_value'] = $_GET['acc_id'];
		}
	}
}

/*
 * Modify columns on admin/Accommodation list
 */
if ( ! function_exists('trav_acc_custom_columns') ) {
	function trav_acc_custom_columns( $column, $post_id ) {
		switch ( $column ) {

			case 'location' :
				$city = get_post_meta( $post_id, 'trav_accommodation_city', true );
				$country = get_post_meta( $post_id, 'trav_accommodation_country', true );
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
 * remove or add columns on admin/Accommodation list
 */
if ( ! function_exists('trav_acc_set_columns') ) {
	function trav_acc_set_columns( $columns ) {
		$author = $columns['author'];
		$date = $columns['date'];
		unset($columns['taxonomy-amenity']);
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
 * Modify columns on admin/room_type list
 */
if ( ! function_exists('trav_room_type_custom_columns') ) {
	function trav_room_type_custom_columns( $column, $post_id ) {
		switch ( $column ) {

			case 'accommodation' :
				$acc_id = get_post_meta( $post_id, 'trav_room_accommodation', true );
				if ( ! empty( $acc_id ) ) {
					edit_post_link( get_the_title( $acc_id ), '', '', $acc_id );
				} else {
					echo esc_html__( 'Not Set', 'trav' );
				}
				break;
			case 'max_adults' :
				$max_adults = get_post_meta( $post_id, 'trav_room_max_adults', true );
				echo esc_html( $max_adults );
				break;
			case 'max_kids' :
				$max_adults = get_post_meta( $post_id, 'trav_room_max_kids', true );
				echo esc_html( $max_adults );
				break;
		}
	}
}

/*
 * remove or add columns on admin/room_type list
 */
if ( ! function_exists('trav_room_type_set_columns') ) {
	function trav_room_type_set_columns( $columns ) {
		$author = $columns['author'];
		$date = $columns['date'];
		unset($columns['author']);
		unset($columns['date']);
		unset($columns['taxonomy-amenity']);

		$columns['accommodation'] = __( 'Accommodation', 'trav' );
		$columns['max_adults'] = __( 'Max Adults', 'trav' );
		$columns['max_kids'] = __( 'Max Kids', 'trav' );
		$columns['author'] = $author;
		$columns['date'] = $date;
		return $columns;
	}
}

/*
 * declare sortable columns on admin/room_type list
 */
if ( ! function_exists('trav_room_type_table_sorting') ) {
	function trav_room_type_table_sorting( $columns ) {
	  $columns['accommodation'] = 'accommodation';
	  return $columns;
	}
}

/*
 * make accommodation column orderable on admin/room_type list
 */
if ( ! function_exists('trav_room_type_acc_column_orderby') ) {
	function trav_room_type_acc_column_orderby( $vars ) {
		if ( isset( $vars['orderby'] ) && 'room_type' == $vars['orderby'] && isset( $vars['orderby'] ) && 'accommodation' == $vars['orderby'] ) {
			$vars = array_merge( $vars, array(
				'meta_key' => 'trav_room_accommodation',
				'orderby' => 'meta_value'
			) );
		}

		return $vars;
	}
}

/*
 * admin enqueue script function
 */
if ( ! function_exists( 'trav_acc_admin_enqueue' ) ) {
	function trav_acc_admin_enqueue($hook) {
		if ( 'post.php' == $hook || 'post-new.php' == $hook) {
			global $post_type;
			if ( $post_type == 'accommodation' ) {
				wp_enqueue_script( 'trav_admin_acc_admin_js', TRAV_TEMPLATE_DIRECTORY_URI . '/inc/admin/accommodation/js/admin.js' );
			}
		}
	}
}

/*
 * remove pending booking if payment is not finished in 30 mins
 */
if ( ! function_exists( 'trav_acc_remove_pending_booking' ) ) {
	function trav_acc_remove_pending_booking( ) {
		global $wpdb;
		// set to cancelled if someone did not finish booking in 30 mins
		$check_time = date('Y-m-d H:i:s', strtotime('-30 minutes'));
		$wpdb->query( "UPDATE " . TRAV_ACCOMMODATION_BOOKINGS_TABLE . " SET status = 0 WHERE status = 1 AND deposit_paid = 0 AND deposit_price > 0 AND created < '" . $check_time . "'" );
	}
}

/*
 * change to completed if start date is passed
 */
if ( ! function_exists( 'trav_acc_change_booking_status' ) ) {
	function trav_acc_change_booking_status( ) {
		global $wpdb;
		$where = ' WHERE 1=1';
		$where .= ' AND status=1 AND date_from < "' . date('Y-m-d') . '"';
		$wpdb->query( "UPDATE " . TRAV_ACCOMMODATION_BOOKINGS_TABLE . " SET status = 2" . $where );
	}
}

/*
 * update meta value when accommodation save
 */
if ( ! function_exists( 'trav_init_acc_meta' ) ) {
	function trav_init_acc_meta( $post_id ) {
		if ( 'accommodation' == get_post_type( $post_id ) ) {
			$avg_price = get_post_meta( $post_id, 'trav_accommodation_avg_price', true );
			if ( '' == $avg_price ) {
				delete_post_meta( $post_id, 'trav_accommodation_avg_price' );
				add_post_meta( $post_id, 'trav_accommodation_avg_price', 0 );
			}
			$review = get_post_meta( $post_id, 'review', true );
			if ( '' == $review ) {
				delete_post_meta( $post_id, 'review' );
				add_post_meta( $post_id, 'review', 0 );
			}
		}
	}
}

add_action( 'manage_accommodation_posts_custom_column' , 'trav_acc_custom_columns', 10, 2 );
add_action( 'manage_room_type_posts_custom_column' , 'trav_room_type_custom_columns', 10, 2 );
add_action( 'save_post', 'trav_init_acc_meta', 15 );
add_filter( 'manage_edit-room_type_sortable_columns', 'trav_room_type_table_sorting' );
add_action( 'admin_enqueue_scripts', 'trav_acc_admin_enqueue' );
add_action( 'trav_hourly_cron', 'trav_acc_remove_pending_booking' );
add_action( 'trav_twicedaily_cron', 'trav_acc_change_booking_status' );
add_action( 'restrict_manage_posts', 'trav_acc_table_filtering' );
add_filter( 'parse_query','trav_admin_filter_room_type' );
add_filter( 'manage_accommodation_posts_columns', 'trav_acc_set_columns' );
add_filter( 'manage_room_type_posts_columns', 'trav_room_type_set_columns' );
add_filter( 'request', 'trav_room_type_acc_column_orderby' );

/* ajax */
add_action( 'wp_ajax_acc_get_acc_room_list', 'trav_ajax_acc_get_acc_room_list' );
add_action( 'wp_ajax_acc_get_room_acc_id', 'trav_ajax_acc_get_room_acc_id' );