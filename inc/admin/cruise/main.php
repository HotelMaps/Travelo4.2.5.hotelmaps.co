<?php
if ( ! defined( 'ABSPATH' ) ) exit; // Exit if accessed directly
require_once( TRAV_INC_DIR . '/admin/cruise/schedules-admin-panel.php' );
require_once( TRAV_INC_DIR . '/admin/cruise/vacancies-admin-panel.php' );
require_once( TRAV_INC_DIR . '/admin/cruise/bookings-admin-panel.php' );

/*
 * get cruise cabin list from cruise id
 */
if ( ! function_exists( 'trav_ajax_cruise_get_cruise_cabin_list' ) ) {
	function trav_ajax_cruise_get_cruise_cabin_list() {

		$args = array(
				'post_type'         => 'cabin_type',
				'posts_per_page'    => -1,
				'orderby'           => 'title',
				'order'             => 'ASC',
		);

		if ( ! empty( $_POST['cruise_id'] ) ) {
			$args['meta_query'] = array(
					array(
						'key'     => 'trav_cabin_cruise',
						'value'   => sanitize_text_field( $_POST['cruise_id'] ),
					),
				);
		}

		echo '<option></option>';
		$cabin_type_query = new WP_Query( $args );
		if ( $cabin_type_query->have_posts() ) {
			while ( $cabin_type_query->have_posts() ) {
				$cabin_type_query->the_post();
				$id = $cabin_type_query->post->ID;
				echo '<option value="' . esc_attr( $id ) .'">' . wp_kses_post( get_the_title( $id ) ) . '</option>';
			}
		}
		wp_reset_postdata();

		exit();
	}
}

/*
 * get cruise id from cabin type id
 */
if ( ! function_exists( 'trav_ajax_cruise_get_cabin_cruise_id' ) ) {
	function trav_ajax_cruise_get_cabin_cruise_id() {
		if ( isset( $_POST['cabin_id'] ) ) {
			$cruise_id = get_post_meta( sanitize_text_field( $_POST['cabin_id'] ), 'trav_cabin_cruise', true );
			echo esc_js( $cruise_id );
		} else {
			//
		}
		exit();
	}
}

/*
 * add cruise filter to admin/cabin_type list
 */
if ( ! function_exists('trav_cruise_table_filtering') ) {
	function trav_cruise_table_filtering() {
		global $wpdb;
		if ( isset( $_GET['post_type'] ) && 'cabin_type' == $_GET['post_type'] ) {
			$cruises = get_posts( array( 'post_type'=>'cruise', 'posts_per_page'=>-1, 'orderby'=>'post_title', 'order'=>'ASC', 'suppress_filters'=>0 ) );
			echo '<select name="cruise_id">';
			echo '<option value="">' . esc_html__( 'All Cruises', 'trav' ) . '</option>';
			foreach( $cruises as $cruise ) {
				$selected = ( ! empty( $_GET['cruise_id'] ) AND $_GET['cruise_id'] == $cruise->ID ) ? 'selected="selected"' : '';
				echo '<option value="' . esc_attr( $cruise->ID ) . '" ' . esc_attr( $selected ) . '>' . esc_html( $cruise->post_title ) . '</option>';
			}
			echo '</select>';
		}
	}
}

/*
 * add cruise filter to admin/cabin_type list
 */
if ( ! function_exists('trav_admin_filter_cabin_type') ) {
	function trav_admin_filter_cabin_type( $query ) {
		global $pagenow;
		$qv = &$query->query_vars;
		if ( $pagenow=='edit.php' && isset($qv['post_type']) && $qv['post_type']=='cabin_type' && !empty($_GET['cruise_id']) && is_numeric($_GET['cruise_id']) ) {
			$qv['meta_key'] = 'trav_cabin_cruise';
			$qv['meta_value'] = $_GET['cruise_id'];
		}
	}
}

/*
 * Modify columns on admin/cruise list
 */
if ( ! function_exists('trav_cruise_custom_columns') ) {
	function trav_cruise_custom_columns( $column, $post_id ) {
		switch ( $column ) {
			case 'ship_name' :
				$name = get_post_meta( $post_id, 'trav_cruise_ship_name', true );
				echo esc_html( $name );
				break;
		}
	}
}

/*
 * remove or add columns on admin/cruise list
 */
if ( ! function_exists('trav_cruise_set_columns') ) {
	function trav_cruise_set_columns( $columns ) {
		$author = $columns['author'];
		$date = $columns['date'];
		unset($columns['taxonomy-amenity']);
		unset($columns['taxonomy-location']);
		unset($columns['comments']);
		unset($columns['author']);
		unset($columns['date']);

		$columns['ship_name'] = __( 'Ship Name', 'trav' );
		$columns['author'] = $author;
		$columns['date'] = $date;
		return $columns;
	}
}

/*
 * Modify columns on admin/cabin_type list
 */
if ( ! function_exists('trav_cabin_type_custom_columns') ) {
	function trav_cabin_type_custom_columns( $column, $post_id ) {
		switch ( $column ) {

			case 'cruise' :
				$cruise_id = get_post_meta( $post_id, 'trav_cabin_cruise', true );
				if ( ! empty( $cruise_id ) ) {
					edit_post_link( get_the_title( $cruise_id ), '', '', $cruise_id );
				} else {
					echo esc_html__( 'Not Set', 'trav' );
				}
				break;
			case 'max_adults' :
				$max_adults = get_post_meta( $post_id, 'trav_cabin_max_adults', true );
				echo esc_html( $max_adults );
				break;
			case 'max_kids' :
				$max_adults = get_post_meta( $post_id, 'trav_cabin_max_kids', true );
				echo esc_html( $max_adults );
				break;
		}
	}
}

/*
 * remove or add columns on admin/cabin_type list
 */
if ( ! function_exists('trav_cabin_type_set_columns') ) {
	function trav_cabin_type_set_columns( $columns ) {
		$author = $columns['author'];
		$date = $columns['date'];
		unset($columns['author']);
		unset($columns['date']);
		unset($columns['taxonomy-amenity']);

		$columns['cruise'] = __( 'cruise', 'trav' );
		$columns['max_adults'] = __( 'Max Adults', 'trav' );
		$columns['max_kids'] = __( 'Max Kids', 'trav' );
		$columns['author'] = $author;
		$columns['date'] = $date;
		return $columns;
	}
}

/*
 * declare sortable columns on admin/cabin_type list
 */
if ( ! function_exists('trav_cabin_type_table_sorting') ) {
	function trav_cabin_type_table_sorting( $columns ) {
	  $columns['cruise'] = 'cruise';
	  return $columns;
	}
}

/*
 * make cruise column orderable on admin/cabin_type list
 */
if ( ! function_exists('trav_cabin_type_cruise_column_orderby') ) {
	function trav_cabin_type_cruise_column_orderby( $vars ) {
		if ( isset( $vars['orderby'] ) && 'cabin_type' == $vars['orderby'] && isset( $vars['orderby'] ) && 'cruise' == $vars['orderby'] ) {
			$vars = array_merge( $vars, array(
				'meta_key' => 'trav_cabin_cruise',
				'orderby' => 'meta_value'
			) );
		}

		return $vars;
	}
}

/*
 * admin enqueue script function
 */
if ( ! function_exists( 'trav_cruise_admin_enqueue' ) ) {
	function trav_cruise_admin_enqueue($hook) {
		if ( 'post.php' == $hook || 'post-new.php' == $hook) {
			global $post_type;
			if ( $post_type == 'cruise' ) {
				wp_enqueue_script( 'trav_admin_cruise_admin_js', TRAV_TEMPLATE_DIRECTORY_URI . '/inc/admin/cruise/js/admin.js' );
			}
		}
	}
}

/*
 * remove pending booking if payment is not finished in 30 mins
 */
if ( ! function_exists( 'trav_cruise_remove_pending_booking' ) ) {
	function trav_cruise_remove_pending_booking( ) {
		global $wpdb;
		// set to cancelled if someone did not finish booking in 30 mins
		$check_time = date('Y-m-d H:i:s', strtotime('-30 minutes'));
		$wpdb->query( "UPDATE " . TRAV_CRUISE_BOOKINGS_TABLE . " SET status = 0 WHERE status = 1 AND deposit_paid = 0 AND deposit_price > 0 AND created < '" . $check_time . "'" );
	}
}

/*
 * change to completed if start date is passed
 */
if ( ! function_exists( 'trav_cruise_change_booking_status' ) ) {
	function trav_cruise_change_booking_status( ) {
		global $wpdb;
		$where = ' WHERE 1=1';
		$where .= ' AND status=1 AND date_from < "' . date('Y-m-d') . '"';
		$wpdb->query( "UPDATE " . TRAV_CRUISE_BOOKINGS_TABLE . " SET status = 2" . $where );
	}
}

/*
 * update meta value when cruise save
 */
if ( ! function_exists( 'trav_init_cruise_meta' ) ) {
	function trav_init_cruise_meta( $post_id ) {
		if ( 'cruise' == get_post_type( $post_id ) ) {
			$avg_price = get_post_meta( $post_id, 'trav_cruise_avg_price', true );
			if ( '' == $avg_price ) {
				delete_post_meta( $post_id, 'trav_cruise_avg_price' );
				add_post_meta( $post_id, 'trav_cruise_avg_price', 0 );
			}
			$review = get_post_meta( $post_id, 'review', true );
			if ( '' == $review ) {
				delete_post_meta( $post_id, 'review' );
				add_post_meta( $post_id, 'review', 0 );
			}
		}
	}
}

if ( ! function_exists( 'trav_cruise_add_auto_schedules' ) ) {
	function trav_cruise_add_auto_schedules( ) {
		global $wpdb;
		$date = get_option( 'passed_date', '0' );
		$date = ( $date + 1 ) % 7;
		if ( $date == 0 ) {
			$date_from = new DateTime('now');
			$date_from->modify('+7 days');

			$date_from = $date_from->format('Y-m-d');
			$duration = 4;
			$date_to_obj = new DateTime( '@' . trav_strtotime( $date_from .' + ' . $duration . ' days' ) );
        	$date_to = esc_sql( $date_to_obj->format( "Y-m-d" ) );

			$schedule_data = array(
								'date_from' => $date_from,
								'date_to'	=> $date_to,
								'duration'	=> $duration,
								'departure' => 'LONG BEACH, CALIFORNIA',
								'arrival'	=> 'ENSENADA, MEXICO',
								'itinerary' => 'a:4:{i:0;a:4:{i:0;s:1:"1";i:1;s:36:"LONG BEACH (LOS ANGELES), CALIFORNIA";i:2;s:3:"---";i:3;s:8:"5:30 P.M";}i:1;a:4:{i:0;s:1:"2";i:1;s:6:"AT SEA";i:2;s:3:"---";i:3;s:3:"---";}i:2;a:4:{i:0;s:1:"3";i:1;s:27:"CATALINA ISLAND, CALIFORNIA";i:2;s:8:"7:30 A.M";i:3;s:8:"4:30 P.M";}i:3;a:4:{i:0;s:1:"4";i:1;s:16:"ENSENADA, MEXICO";i:2;s:8:"9:00 A.M";i:3;s:8:"8:00 P.M";}}'
								);
			$sql = "SELECT DISTINCT ID as id FROM " . $wpdb->posts . " WHERE (post_status = 'publish') AND (post_type = 'cruise')";
			$results = $wpdb->get_results( $sql, ARRAY_A );
			if ( $results ) {
				foreach ( $results as $result ) {
					$schedule_data['cruise_id'] = $result['id'];
					$wpdb->insert( TRAV_CRUISE_SCHEDULES_TABLE, $schedule_data );
				}
			}
		}
		update_option( 'passed_date', $date );		
	}
}

add_action( 'manage_cruise_posts_custom_column' , 'trav_cruise_custom_columns', 10, 2 );
add_action( 'manage_cabin_type_posts_custom_column' , 'trav_cabin_type_custom_columns', 10, 2 );
add_action( 'save_post', 'trav_init_cruise_meta', 15 );
add_filter( 'manage_edit-cabin_type_sortable_columns', 'trav_cabin_type_table_sorting' );
add_action( 'admin_enqueue_scripts', 'trav_cruise_admin_enqueue' );
add_action( 'trav_hourly_cron', 'trav_cruise_remove_pending_booking' );
add_action( 'trav_twicedaily_cron', 'trav_cruise_change_booking_status' );
//add_action( 'trav_daily_cron', 'trav_cruise_add_auto_schedules' );
add_action( 'restrict_manage_posts', 'trav_cruise_table_filtering' );
add_filter( 'parse_query','trav_admin_filter_cabin_type' );
add_filter( 'manage_cruise_posts_columns', 'trav_cruise_set_columns' );
add_filter( 'manage_cabin_type_posts_columns', 'trav_cabin_type_set_columns' );
add_filter( 'request', 'trav_cabin_type_cruise_column_orderby' );

/* ajax */
add_action( 'wp_ajax_cruise_get_cruise_cabin_list', 'trav_ajax_cruise_get_cruise_cabin_list' );
add_action( 'wp_ajax_cruise_get_cabin_cruise_id', 'trav_ajax_cruise_get_cabin_cruise_id' );