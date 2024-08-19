<?php
if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly
}

if ( ! class_exists( 'WP_List_Table' ) ) {
	require_once( ABSPATH . 'wp-admin/includes/class-wp-list-table.php' );
}

/**
 * functions to manage booking
 */
if ( ! class_exists( 'Trav_Flight_Booking_List_Table') ) :
class Trav_Flight_Booking_List_Table extends WP_List_Table {

	function __construct() {
		parent::__construct( array(
			'singular'  => 'flight_booking',     //singular name of the listed records
			'plural'    => 'flight_bookings',    //plural name of the listed records
			'ajax'      => false        //does this table support ajax?
		) );
	}

	function column_default( $item, $column_name ) {
		switch( $column_name ) {
			case 'id':
			case 'adults':
			case 'created':
			case 'total_price':
			case 'flight_date':
				return $item[ $column_name ];
			default:
				return print_r( $item, true ); //Show the whole array for troubleshooting purposes
		}
	}

	function column_customer_name( $item ) {
		//Build row actions
		$link_pattern = '<a href="edit.php?post_type=%1s&page=%2$s&action=%3$s&booking_id=%4$s">%5$s</a>';
		$actions = array(
			'edit'      => sprintf( $link_pattern, sanitize_text_field( $_REQUEST['post_type'] ), 'flight_bookings', 'edit', $item['id'], 'Edit' ),
			'delete'    => sprintf( $link_pattern, sanitize_text_field( $_REQUEST['post_type'] ), 'flight_bookings', 'delete', $item['id'] . '&_wpnonce=' . wp_create_nonce( 'booking_delete' ) , 'Delete' )
		);
		$content = sprintf( $link_pattern, sanitize_text_field( $_REQUEST['post_type'] ), 'flight_bookings', 'edit', $item['id'], esc_html( $item['first_name'] . ' ' . $item['last_name'] ) );

		//Return the title contents
		return sprintf( '%1$s %2$s', $content , $this->row_actions( $actions ) );
	}

	function column_flight_name( $item ) {
		return '<a href="' . get_edit_post_link( $item['flight_id'] ) . '">' . $item['flight_name'] . '</a>';
	}

	function column_air_line( $item ) {
		$air_line = get_term( $item['air_line'], 'air_line' );
		return $air_line->name;
	}

	function column_location_from( $item ) {
		$location_from = get_term( $item['location_from'], 'flight_location' );
		return $location_from->name;
	}

	function column_location_to( $item ) {
		$location_to = get_term( $item['location_to'], 'flight_location' );
		return $location_to->name;
	}

	function column_status( $item ) {
		switch( $item['status'] ) {
			/*case -1:
				return __( 'Pending', 'trav' );*/
			case 0:
				return __( 'Cancelled', 'trav' );
			case 1:
				return __( 'Upcoming', 'trav' );
			case 2:
				return __( 'Completed', 'trav' );
		}

		return $item['status'];
	}

	function column_cb( $item ) {
		return sprintf( '<input type="checkbox" name="%1$s[]" value="%2$s" />', $this->_args['singular'], $item['id'] );
	}

	function get_columns() {
		$columns = array(
			'cb'            => '<input type="checkbox" />', //Render a checkbox instead of text
			'id'            => __( 'ID', 'trav' ),
			'customer_name' => __( 'Customer Name', 'trav' ),
			'flight_name'     => __( 'Flight Name', 'trav' ),
			'air_line' => __( 'Air Line', 'trav' ),
			'location_from' => __( 'Departure From', 'trav' ),
			'location_to' => __( 'Arrival At', 'trav' ),
			'flight_date'     => __( 'Flight Date', 'trav' ),
			'adults'        => __( 'Adults', 'trav' ),
			'total_price'   => __( 'Price', 'trav' ),
			'created'       => __( 'Created Date', 'trav' ),
			'status'        => __( 'Status', 'trav' ),
		);

		return $columns;
	}

	function get_sortable_columns() {
		$sortable_columns = array(
			'id'            => array( 'id', false ),
			'flight_date'     => array( 'flight_date', false ),
			'created'       => array( 'created', false ),
			'status'        => array( 'status', false ),
		);

		return $sortable_columns;
	}

	function get_bulk_actions() {
		$actions = array(
			'bulk_delete'    => __( 'Delete', 'trav' ),
		);

		return $actions;
	}

	function process_bulk_action() {
		global $wpdb;
		//Detect when a bulk action is being triggered...

		if ( isset( $_POST['_wpnonce'] ) && ! empty( $_POST['_wpnonce'] ) ) {

			$nonce  = filter_input( INPUT_POST, '_wpnonce', FILTER_SANITIZE_STRING );
			$action = 'bulk-' . $this->_args['plural'];

			if ( ! wp_verify_nonce( $nonce, $action ) ) {
				wp_die( __('Sorry, your nonce did not verify', 'trav') );
			}
		}
		if ( 'bulk_delete'===$this->current_action() ) {
			$selected_ids = $_GET[ $this->_args['singular'] ];

			$how_many = count( $selected_ids );
			$placeholders = array_fill( 0, $how_many, '%d' );
			$format = implode( ', ', $placeholders );
			$current_user_id = get_current_user_id();
			$post_table_name  = esc_sql( $wpdb->prefix . 'posts' );
			$sql = '';

			if ( current_user_can( 'manage_options' ) ) {
				$sql = sprintf( 'DELETE FROM %1$s WHERE id IN (%2$s)', TRAV_FLIGHT_BOOKINGS_TABLE, "$format" );
			} else {
				$sql = sprintf( 'DELETE booking FROM %1$s as booking, %2$s as flight WHERE booking.flight_id=flight.ID AND booking.id IN (%3$s) AND flight.post_author = %4$d', TRAV_FLIGHT_BOOKINGS_TABLE, $post_table_name, "$format", $current_user_id );
			}

			$sql = $wpdb->prepare( $sql, $selected_ids );
			$wpdb->query( $sql );
			wp_redirect( admin_url( 'edit.php?post_type=flight&page=flight_bookings&bulk_delete=true') );
		}
	}

	function prepare_items() {
		global $wpdb;

		$per_page = 10;
		$columns = $this->get_columns();
		$hidden = array();
		$sortable = $this->get_sortable_columns();

		$this->_column_headers = array( $columns, $hidden, $sortable );
		$this->process_bulk_action();

		$orderby = ( ! empty( $_REQUEST['orderby'] ) ) ? sanitize_sql_orderby( $_REQUEST['orderby'] ) : 'id'; //If no sort, default to title
		$order = ( ! empty( $_REQUEST['order'] ) ) ? sanitize_text_field( $_REQUEST['order'] ) : 'desc'; //If no order, default to desc
		$current_page = $this->get_pagenum();
		$post_table_name  = esc_sql( $wpdb->prefix . 'posts' );

		$where = "1=1";
		if ( ! empty( $_REQUEST['flight_id'] ) ) $where .= " AND booking.flight_id = " . esc_sql( trav_flight_org_id( $_REQUEST['flight_id'] ) );
		if ( ! empty( $_REQUEST['flight_date'] ) ) $where .= " AND booking.flight_date = '" . esc_sql( $_REQUEST['flight_date'] ) . "'";
		if ( ! empty( $_REQUEST['air_line'] ) ) $where .= " AND booking.air_line = '" . esc_sql( $_REQUEST['air_line'] ) . "'";
		if ( ! empty( $_REQUEST['location_from'] ) ) $where .= " AND booking.location_from = '" . esc_sql( $_REQUEST['location_from'] ) . "'";
		if ( ! empty( $_REQUEST['location_to'] ) ) $where .= " AND booking.location_to = '" . esc_sql( $_REQUEST['location_to'] ) . "'";
		if ( isset( $_REQUEST['status'] ) ) $where .= " AND booking.status = '" . esc_sql( $_REQUEST['status'] ) . "'";
		if ( ! current_user_can( 'manage_options' ) ) { $where .= " AND flight.post_author = '" . get_current_user_id() . "' "; }

		$sql = sprintf( 'SELECT booking.*, flight.ID as flight_id, flight.post_title as flight_name FROM %1$s as booking
						INNER JOIN %2$s as flight ON booking.flight_id=flight.ID
						WHERE ' . $where . ' ORDER BY %3$s %4$s
						LIMIT %5$s, %6$s' , TRAV_FLIGHT_BOOKINGS_TABLE, $post_table_name, $orderby, $order, $per_page * ( $current_page - 1 ), $per_page );
		$data = $wpdb->get_results( $sql, ARRAY_A );

		$sql = sprintf( 'SELECT COUNT(*) FROM %1$s as booking INNER JOIN %2$s as flight ON booking.flight_id=flight.ID WHERE ' . $where , TRAV_FLIGHT_BOOKINGS_TABLE, $post_table_name );
		$total_items = $wpdb->get_var( $sql );

		$this->items = $data;
		$this->set_pagination_args( array(
			'total_items' => $total_items,                  //WE have to calculate the total number of items
			'per_page'    => $per_page,                     //WE have to determine how many items to show on a page
			'total_pages' => ceil( $total_items/$per_page )   //WE have to calculate the total number of pages
		) );
	}
}
endif;

/*
 * add booking list page to menu
 */
if ( ! function_exists( 'trav_flight_booking_add_menu_items' ) ) {
	function trav_flight_booking_add_menu_items() {
		$page = add_submenu_page( 'edit.php?post_type=flight', __('Flight Bookings', 'trav'), __('Bookings', 'trav'), 'edit_accommodations', 'flight_bookings', 'trav_flight_booking_render_pages' );

		add_action( 'admin_print_scripts-' . $page, 'trav_flight_booking_admin_enqueue_scripts' );
	}
}

/*
 * booking admin main actions
 */
if ( ! function_exists( 'trav_flight_booking_render_pages' ) ) {
	function trav_flight_booking_render_pages() {
		if ( ( ! empty( $_REQUEST['action'] ) ) && ( ( 'add' == $_REQUEST['action'] ) || ( 'edit' == $_REQUEST['action'] ) ) ) {
			trav_flight_booking_render_manage_page();
		} elseif ( ( ! empty( $_REQUEST['action'] ) ) && ( 'delete' == $_REQUEST['action'] ) ) {
			trav_flight_booking_delete_action();
		} else {
			trav_flight_booking_render_list_page();
		}
	}
}

/*
 * render booking list page
 */
if ( ! function_exists( 'trav_flight_booking_render_list_page' ) ) {
	function trav_flight_booking_render_list_page() {
		global $wpdb;

		$travBookingTable = new Trav_Flight_Booking_List_Table();
		$travBookingTable->prepare_items();

		$flight_id = empty( $_REQUEST['flight_id'] ) ? '' : $_REQUEST['flight_id'];
		$air_line = empty( $_REQUEST['air_line'] ) ? '' : $_REQUEST['air_line'];
		$location_from = empty( $_REQUEST['location_from'] ) ? '' : $_REQUEST['location_from'];
		$location_to = empty( $_REQUEST['location_to'] ) ? '' : $_REQUEST['location_to'];
		$flight_date = empty( $_REQUEST['flight_date'] ) ? '' : $_REQUEST['flight_date'];
		$status = empty( $_REQUEST['status'] ) ? '' : $_REQUEST['status'];
		?>

		<div class="wrap">

			<h2><?php _e('Flight Bookings', 'trav') ?> <a href="edit.php?post_type=flight&amp;page=flight_bookings&amp;action=add" class="add-new-h2"><?php _e('Add New', 'trav') ?></a></h2>

			<?php 
			if ( isset( $_REQUEST['bulk_delete'] ) ) {
				echo '<div id="message" class="updated below-h2"><p>' . __('Bookings deleted', 'trav') . '</p></div>';
			}
			?>

			<select id="flight_id">
				<?php echo trav_flight_get_flight_list( $flight_id ) ?>
			</select>

			<span class="air_line_wrapper">
				<select name="air_line" id="air_line">
					<?php echo trav_flight_get_air_line_list( $air_line ) ?>
				</select>
			</span>

			<span class="location_from_wrapper">
				<select name="location_from" id="location_from">
					<?php echo trav_flight_get_flight_location_list( $location_from ) ?>
				</select>
			</span>

			<span class="location_to_wrapper">
				<select name="location_to" id="location_to">
					<?php echo trav_flight_get_flight_location_list( $location_to ) ?>
				</select>
			</span>

			<input type="text" id="flight_date" name="flight_date" placeholder="<?php _e('Filter by Start Date', 'trav') ?>" value="<?php if ( ! empty( $_REQUEST['flight_date'] ) ) echo esc_attr( $_REQUEST['flight_date'] ); ?>">
			
			<select name="status" id="status">
				<option value=""><?php _e('Select a Status', 'trav') ?></option>
				<?php
					$statuses = array( 
						'0' => __('Cancelled', 'trav'), 
						'1' => __('Upcoming', 'trav'), 
						'2' => __('Completed', 'trav'), 
						/*'-1' => 'pending'*/ 
					);
					foreach( $statuses as $key=>$status ) { ?>
						<option value="<?php echo esc_attr( $key ) ?>" <?php selected( $key, isset( $_REQUEST['status'] ) ? esc_attr( $_REQUEST['status'] ) : '' ); ?>><?php echo esc_attr( $status ) ?></option>
				<?php } ?>
			</select>

			<input type="button" name="booking_filter" id="booking-filter" class="button" value="<?php _e('Filter', 'trav') ?>">

			<a href="edit.php?post_type=flight&amp;page=flight_bookings" class="button-secondary"><?php _e('Show All', 'trav') ?></a>

			<form id="flight-bookings-filter" method="get">
				<input type="hidden" name="post_type" value="<?php echo esc_attr( $_REQUEST['post_type'] ) ?>" />
				<input type="hidden" name="page" value="<?php echo esc_attr( $_REQUEST['page'] ) ?>" />

				<?php $travBookingTable->display() ?>
			</form>

		</div>
		<?php
	}
}

/*
 * render booking detail page
 */
if ( ! function_exists( 'trav_flight_booking_render_manage_page' ) ) {
	function trav_flight_booking_render_manage_page() {
		global $wpdb, $trav_options;

		$post_table_name  = $wpdb->prefix . 'posts';

		$site_currency_symbol = trav_get_site_currency_symbol();

		if ( ! empty( $_POST['save'] ) ) {
			trav_flight_booking_save_action();
			return;
		}

		$booking_data = array();

		if ( 'add' == $_REQUEST['action'] ) {
			$page_title = __("Add New Flight booking", "trav");
		} elseif ( 'edit' == $_REQUEST['action'] ) {
			$page_title = __('Edit Flight Booking', 'trav') . '<a href="edit.php?post_type=flight&amp;page=flight_bookings&amp;action=add" class="add-new-h2">' . __('Add New', 'trav') . '</a>';

			if ( empty( $_REQUEST['booking_id'] ) ) {
				echo "<h2>" . __("You attempted to edit an item that doesn't exist. Perhaps it was deleted?", "trav") . "</h2>";
				return;
			}

			$booking_id = $_REQUEST['booking_id'];

			$where = 'booking.id = %3$d';
			if ( ! current_user_can( 'manage_options' ) ) { $where .= " AND flight.post_author = '" . get_current_user_id() . "' "; }

			$sql = sprintf( 'SELECT booking.*, flight.ID as flight_id, flight.post_title as flight_name FROM %1$s as booking
				INNER JOIN %2$s as flight ON booking.flight_id=flight.ID
				WHERE ' . $where , TRAV_FLIGHT_BOOKINGS_TABLE, $post_table_name, $booking_id );

			$booking_data = $wpdb->get_row( $sql, ARRAY_A );
			if ( empty( $booking_data ) ) {
				echo "<h2>" . __("You attempted to edit an item that doesn't exist. Perhaps it was deleted?", "trav") . "</h2>";
				return;
			}
		}
		$trip_type = empty( $booking_data['trip_type'] ) ? 'one_way' : $booking_data['trip_type'];

		$flight_id = empty( $booking_data['flight_id'] ) ? '' : $booking_data['flight_id'];
		$air_line = empty( $booking_data['air_line'] ) ? '' : $booking_data['air_line'];
		$location_from = empty( $booking_data['location_from'] ) ? '' : $booking_data['location_from'];
		$location_to = empty( $booking_data['location_to'] ) ? '' : $booking_data['location_to'];

		$return_flight_id = empty( $booking_data['return_flight_id'] ) ? '' : $booking_data['return_flight_id'];
		$return_air_line = empty( $booking_data['return_air_line'] ) ? '' : $booking_data['return_air_line'];
		$return_location_from = empty( $booking_data['return_location_from'] ) ? '' : $booking_data['return_location_from'];
		$return_location_to = empty( $booking_data['return_location_to'] ) ? '' : $booking_data['return_location_to'];

		?>

		<div class="wrap">
			<h2><?php echo wp_kses_post( $page_title ); ?></h2>

			<?php 
			if ( isset( $_REQUEST['updated'] ) ) {
				echo '<div id="message" class="updated below-h2"><p>' . __('Booking saved', 'trav') . '</p></div>';
			}
			?>

			<form method="post" onsubmit="return manage_booking_validateForm();">
				<input type="hidden" name="id" value="<?php if ( ! empty( $booking_data['id'] ) ) echo esc_attr( $booking_data['id'] ); ?>">

				<div class="one-half">
					<h3><?php _e('Booking Detail', 'trav') ?></h3>
					<table class="trav_admin_table trav_booking_manage_table">
						<tr>
							<th><?php _e('Trip Type', 'trav') ?></th>
							<td>
								<select name="trip_type" id="trip_type">
									<option value="one_way" <?php selected( 'one_way', $trip_type ); ?> ><?php echo esc_html__( 'ONE-WAY', 'trav' ); ?></option>
									<option value="round_trip" <?php selected( 'round_trip', $trip_type ); ?> ><?php echo esc_html__( 'ROUND-TRIP', 'trav' ); ?></option>
								</select>
							</td>
						</tr>
						<tr>
							<th><?php _e('Flight', 'trav') ?></th>
							<td>
								<select name="flight_id" id="flight_id">
									<?php echo trav_flight_get_flight_list( $booking_data['flight_id'] ) ?>
								</select>
							</td>
						</tr>
						<tr class="air_line_wrapper">
							<th><?php _e('Air Line', 'trav') ?></th>
							<td>
								<select name="air_line" id="air_line">
									<?php echo trav_flight_get_air_line_list( $air_line ) ?>
								</select>
							</td>
						</tr>

						<tr class="location_from_wrapper">
							<th><?php _e('Departure From', 'trav') ?></th>
							<td>
								<select name="location_from" id="location_from">
									<?php echo trav_flight_get_flight_location_list( $location_from ) ?>
								</select>
							</td>
						</tr>

						<tr class="location_to_wrapper">
							<th><?php _e('Arrival At', 'trav') ?></th>
							<td>
								<select name="location_to" id="location_to">
									<?php echo trav_flight_get_flight_location_list( $location_to ) ?>
								</select>
							</td>
						</tr>
						<tr>
							<th><?php _e('Flight Date', 'trav') ?></th>
							<td><input type="text" name="flight_date" id="flight_date" value="<?php if ( ! empty( $booking_data['flight_date'] ) ) echo esc_attr( $booking_data['flight_date'] ); ?>"></td>
						</tr>
						<tr>
							<th><?php _e('Departure Time', 'trav') ?></th>
							<td><input type="text" name="departure_time" id="departure_time" value="<?php if ( ! empty( $booking_data['departure_time'] ) ) echo esc_attr( $booking_data['departure_time'] ); ?>"></td>
						</tr>
						<tr>
							<th><?php _e('Arrival Time', 'trav') ?></th>
							<td><input type="text" name="arrival_time" id="arrival_time" value="<?php if ( ! empty( $booking_data['arrival_time'] ) ) echo esc_attr( $booking_data['arrival_time'] ); ?>"></td>
						</tr>

						<tr>
							<th><?php _e('Return Flight', 'trav') ?></th>
							<td>
								<select name="return_flight_id" id="return_flight_id">
									<?php echo trav_flight_get_flight_list( $booking_data['return_flight_id'] ) ?>
								</select>
							</td>
						</tr>
						<tr class="air_line_wrapper">
							<th><?php _e('Return Air Line', 'trav') ?></th>
							<td>
								<select name="return_air_line" id="return_air_line">
									<?php echo trav_flight_get_air_line_list( $return_air_line ) ?>
								</select>
							</td>
						</tr>

						<tr class="location_from_wrapper">
							<th><?php _e('Return Departure From', 'trav') ?></th>
							<td>
								<select name="return_location_from" id="return_location_from">
									<?php echo trav_flight_get_flight_location_list( $return_location_from ) ?>
								</select>
							</td>
						</tr>

						<tr class="location_to_wrapper">
							<th><?php _e('Return Arrival At', 'trav') ?></th>
							<td>
								<select name="return_location_to" id="return_location_to">
									<?php echo trav_flight_get_flight_location_list( $return_location_to ) ?>
								</select>
							</td>
						</tr>
						<tr>
							<th><?php _e('Return Flight Date', 'trav') ?></th>
							<td><input type="text" name="return_flight_date" id="return_flight_date" value="<?php if ( ! empty( $booking_data['return_flight_date'] ) ) echo esc_attr( $booking_data['return_flight_date'] ); ?>"></td>
						</tr>
						<tr>
							<th><?php _e('Return Departure Time', 'trav') ?></th>
							<td><input type="text" name="return_departure_time" id="return_departure_time" value="<?php if ( ! empty( $booking_data['return_departure_time'] ) ) echo esc_attr( $booking_data['return_departure_time'] ); ?>"></td>
						</tr>
						<tr>
							<th><?php _e('Return Arrival Time', 'trav') ?></th>
							<td><input type="text" name="return_arrival_time" id="return_arrival_time" value="<?php if ( ! empty( $booking_data['return_arrival_time'] ) ) echo esc_attr( $booking_data['return_arrival_time'] ); ?>"></td>
						</tr>

						<tr>
							<th><?php _e('Adults', 'trav') ?></th>
							<td><input type="number" name="adults" value="<?php if ( ! empty( $booking_data['adults'] ) ) echo esc_attr( $booking_data['adults'] ) ?>"></td>
						</tr>
						<tr>
							<th><?php _e('Total Price', 'trav') ?></th>
							<td><input type="text" name="total_price" value="<?php if ( ! empty( $booking_data['total_price'] ) ) echo esc_attr( $booking_data['total_price'] ) ?>"> <?php echo esc_html( $site_currency_symbol ) ?></td>
						</tr>
						<?php if ( trav_is_multi_currency() ) { ?>
							<tr>
								<th><?php _e('User Currency', 'trav') ?></th>
								<td>
									<select name="currency_code">
										<?php foreach ( array_filter( $trav_options['site_currencies'] ) as $key => $content) { ?>
											<option value="<?php echo esc_attr( $key ) ?>" <?php if ( ! empty( $booking_data['currency_code'] ) ) selected( $key, $booking_data['currency_code'] ); ?>><?php echo esc_html( $key ) ?></option>
										<?php } ?>
									</select>
								</td>
							</tr>
							<tr>
								<th><?php _e('Exchange Rate', 'trav') ?></th>
								<td><input type="text" name="exchange_rate" value="<?php if ( ! empty( $booking_data['exchange_rate'] ) ) echo esc_attr( $booking_data['exchange_rate'] ) ?>"></td>
							</tr>
							<tr>
								<th><?php _e('Total Price in User Currency', 'trav') ?></th>
								<td><label> <?php if ( ! empty( $booking_data['total_price'] ) && ! empty( $booking_data['exchange_rate'] ) ) echo esc_attr( $booking_data['total_price'] * $booking_data['exchange_rate'] ) . esc_html( trav_get_currency_symbol( $booking_data['currency_code'] ) ) ?></td>
							</tr>
						<?php } ?>
						<?php if ( ! empty( $booking_data['deposit_price'] ) && ! ( $booking_data['deposit_price'] == 0 ) ) { ?>
							<tr>
								<th><?php _e('Deposit Amount', 'trav') ?></th>
								<td><input type="text" name="deposit_price" value="<?php if ( ! empty( $booking_data['deposit_price'] ) ) echo esc_attr( $booking_data['deposit_price'] ) ?>"> <?php echo esc_html( trav_get_currency_symbol( $booking_data['currency_code'] ) ) ?></td>
							</tr>
							<tr>
								<th><?php _e('Deposit Paid', 'trav') ?></th>
								<td>
									<select name="deposit_paid">
										<?php $deposit_paid = array( '1' => 'yes', '0' => 'no' ); ?>
										<?php foreach ( $deposit_paid as $key => $content) { ?>
											<option value="<?php echo esc_attr( $key ) ?>" <?php selected( $key, $booking_data['deposit_paid'] ); ?>><?php echo esc_html( $content ) ?></option>
										<?php } ?>
									</select>
								</td>
							</tr>
							<?php if ( ! empty( $booking_data['deposit_paid'] ) ) {
								$other_data = unserialize( $booking_data['other'] );
								if ( ! empty( $other_data['pp_transaction_id'] ) ) { ?>
								<tr>
									<th><?php _e('Paypal Payment Transaction ID', 'trav') ?></th>
									<td><label><?php echo $other_data['pp_transaction_id'] ?></label></td>
								</tr>
							<?php } } ?>
						<?php } ?>
						<tr>
							<th><?php _e('Status', 'trav') ?></th>
							<td>
								<select name="status">
									<?php 
									$statuses = array( 
										'0' => __('Cancelled', 'trav'), 
										'1' => __('Upcoming', 'trav'), 
										'2' => __('Completed', 'trav'), 
										/*'-1' => 'pending'*/ 
									);
									if ( ! isset( $booking_data['status'] ) ) {
										$booking_data['status'] = 1;
									}
									foreach ( $statuses as $key => $content) { 
										?>
										<option value="<?php echo esc_attr( $key ) ?>" <?php selected( $key, $booking_data['status'] ); ?>><?php echo esc_html( $content ) ?></option>
										<?php 
									} 
									?>
								</select>
							</td>
						</tr>
					</table>
				</div>

				<div class="one-half">
					<h3><?php _e('Customer Infomation', 'trav') ?></h3>
					<table  class="trav_admin_table trav_booking_manage_table">
						<tr>
							<th><?php _e('First Name', 'trav') ?></th>
							<td><input type="text" name="first_name" value="<?php if ( ! empty( $booking_data['first_name'] ) ) echo esc_attr( $booking_data['first_name'] ) ?>"></td>
						</tr>
						<tr>
							<th><?php _e('Last Name', 'trav') ?></th>
							<td><input type="text" name="last_name" value="<?php if ( ! empty( $booking_data['last_name'] ) ) echo esc_attr( $booking_data['last_name'] ) ?>"></td>
						</tr>
						<tr>
							<th><?php _e('Email', 'trav') ?></th>
							<td><input type="email" name="email" value="<?php if ( ! empty( $booking_data['email'] ) ) echo esc_attr( $booking_data['email'] ) ?>"></td>
						</tr>
						<tr>
							<th><?php _e('Country Code', 'trav') ?></th>
							<td><input type="text" name="country_code" value="<?php if ( ! empty( $booking_data['country_code'] ) ) echo esc_attr( $booking_data['country_code'] ) ?>"></td>
						</tr>
						<tr>
							<th><?php _e('Phone', 'trav') ?></th>
							<td><input type="text" name="phone" value="<?php if ( ! empty( $booking_data['phone'] ) ) echo esc_attr( $booking_data['phone'] ) ?>"></td>
						</tr>
						<tr>
							<th><?php _e('Address', 'trav') ?></th>
							<td><input type="text" name="address" value="<?php if ( ! empty( $booking_data['address'] ) ) echo esc_attr( $booking_data['address'] ) ?>"></td>
						</tr>
						<tr>
							<th><?php _e('City', 'trav') ?></th>
							<td><input type="text" name="city" value="<?php if ( ! empty( $booking_data['city'] ) ) echo esc_attr( $booking_data['city'] ) ?>"></td>
						</tr>
						<tr>
							<th><?php _e('Zip', 'trav') ?></th>
							<td><input type="text" name="zip" value="<?php if ( ! empty( $booking_data['zip'] ) ) echo esc_attr( $booking_data['zip'] ) ?>"></td>
						</tr>
						<tr>
							<th><?php _e('Country', 'trav') ?></th>
							<td><input type="text" name="country" value="<?php if ( ! empty( $booking_data['country'] ) ) echo esc_attr( $booking_data['country'] ) ?>"></td>
						</tr>
						<tr>
							<th><?php _e('Special Requirements', 'trav') ?></th>
							<td><textarea name="special_requirements"><?php if ( ! empty( $booking_data['special_requirements'] ) ) echo esc_textarea( stripslashes( $booking_data['special_requirements'] ) ) ?></textarea></td>
						</tr>
						<tr>
							<th><?php _e('Booking No', 'trav') ?></th>
							<td><input type="text" name="booking_no" value="<?php if ( ! empty( $booking_data['booking_no'] ) ) echo esc_attr( $booking_data['booking_no'] ) ?>"></td>
						</tr>
						<tr>
							<th><?php _e('Pin Code', 'trav') ?></th>
							<td><input type="text" name="pin_code" value="<?php if ( ! empty( $booking_data['pin_code'] ) ) echo esc_attr( $booking_data['pin_code'] ) ?>"></td>
						</tr>
					</table>
				</div>

				<input type="submit" class="button-primary button_save_booking" name="save" value="<?php _e('Save booking', 'trav') ?>">

				<a href="edit.php?post_type=flight&amp;page=flight_bookings" class="button-secondary"><?php _e('Cancel', 'trav') ?></a>

				<?php wp_nonce_field('trav_manage_flight_bookings','booking_save'); ?>
			</form>
		</div>

		<?php
	}
}

/*
 * booking delete action
 */
if ( ! function_exists( 'trav_flight_booking_delete_action' ) ) {
	function trav_flight_booking_delete_action() {
		global $wpdb;

		// data validation
		if ( empty( $_REQUEST['booking_id'] ) ) {
			print __('Sorry, you tried to remove nothing.', 'trav');
			exit;
		}

		// nonce check
		if ( ! isset( $_GET['_wpnonce'] ) || ! wp_verify_nonce( $_GET['_wpnonce'], 'booking_delete' ) ) {
			print __('Sorry, your nonce did not verify.', 'trav');
			exit;
		}

		// check ownership if user is not admin
		if ( ! current_user_can( 'manage_options' ) ) {
			$sql = sprintf( 'SELECT flight_id FROM %1$s as booking WHERE booking.id = %2$d' , TRAV_FLIGHT_BOOKINGS_TABLE, $_REQUEST['booking_id'] );
			$flight_id = $wpdb->get_var( $sql );

			$post_author_id = get_post_field( 'post_author', $flight_id );

			if ( get_current_user_id() != $post_author_id ) {
				print __('You don\'t have permission to remove other\'s item.', 'trav');
				exit;
			}
		}

		// do action
		$wpdb->delete( TRAV_FLIGHT_BOOKINGS_TABLE, array( 'id' => $_REQUEST['booking_id'] ) );

		wp_redirect( admin_url( 'edit.php?post_type=flight&page=flight_bookings') );
		exit;
	}
}

/*
 * booking save action
 */
if ( ! function_exists( 'trav_flight_booking_save_action' ) ) {
	function trav_flight_booking_save_action() {
		if ( ! isset( $_POST['booking_save'] ) || ! wp_verify_nonce( $_POST['booking_save'], 'trav_manage_flight_bookings' ) ) {
			print 'Sorry, your nonce did not verify.';
			exit;
		} else {
			global $wpdb;

			$default_booking_data = array(
				'trip_type'				=> '',
				'flight_id'             => '',
				'st_id'                 => '',
				'flight_date'           => '',
				'location_from'			=> '',
				'location_to'			=> '',
				'departure_time'		=> '',
				'arrival_time'			=> '',
				'air_line'				=> '',
				'flight_type'			=> '',

				'return_flight_id'             => '',
				'return_flight_date'           => '',
				'return_location_from'			=> '',
				'return_location_to'			=> '',
				'return_departure_time'		=> '',
				'return_arrival_time'			=> '',
				'return_air_line'				=> '',
				'return_flight_type'			=> '',

				'first_name'            => '',
				'last_name'             => '',
				'email'                 => '',
				'country_code'          => '',
				'phone'                 => '',
				'address'               => '',
				'city'                  => '',
				'zip'                   => '',
				'country'               => '',
				'special_requirements'  => '',
				'adults'                => '',
				'discount_rate'         => '',
				'total_price'           => '',
				'currency_code'         => '',
				'exchange_rate'         => 1,
				'deposit_price'         => 0,
				'deposit_paid'          => 0,
				'booking_no'            => '',
				'pin_code'              => '',
				'status'                => '',
				'updated'               => date( 'Y-m-d H:i:s' ),
			);

			$table_fields = array(
									'trip_type',
									'flight_id',
									'st_id',
									'flight_date',
									'location_from',
									'location_to',
									'departure_time',
									'arrival_time',
									'air_line',
									'flight_type',
									'return_flight_id',
									'return_flight_date',
									'return_location_from',
									'return_location_to',
									'return_departure_time',
									'return_arrival_time',
									'return_air_line',
									'return_flight_type',
									'first_name',
									'last_name',
									'email',
									'country_code',
									'phone',
									'address',
									'city',
									'zip',
									'country',
									'special_requirements',
									'adults',
									'discount_rate',
									'total_price',
									'currency_code',
									'exchange_rate',
									'deposit_price',
									'deposit_paid',
									'booking_no',
									'pin_code',
									'status'
								);
			$data = array();
			foreach ( $table_fields as $table_field ) {
				if ( isset( $_POST[ $table_field ] ) ) {
					$data[ $table_field ] = $_POST[ $table_field ];
					if ( ! is_array( $_POST[ $table_field ] ) ) {
						$data[ $table_field ] = sanitize_text_field( $data[ $table_field ] );
					} else {
						$data[ $table_field ] = serialize( $data[ $table_field ] );
					}
				}
			}

			$data = array_replace( $default_booking_data, $data );
			$data['flight_id'] = trav_flight_org_id( $data['flight_id'] );

			if ( empty( $_POST['id'] ) ) {
				//insert
				$data['created'] = date( 'Y-m-d H:i:s' );
				$wpdb->insert( TRAV_FLIGHT_BOOKINGS_TABLE, $data );
				$id = $wpdb->insert_id;
			} else {
				//update
				$wpdb->update( TRAV_FLIGHT_BOOKINGS_TABLE, $data, array( 'id' => sanitize_text_field( $_POST['id'] ) ) );
				$id = sanitize_text_field( $_POST['id'] );
			}
			
			wp_redirect( admin_url( 'edit.php?post_type=flight&page=flight_bookings&action=edit&booking_id=' . $id . '&updated=true') );
			exit;
		}
	}
}

/*
 * booking admin enqueue script action
 */
if ( ! function_exists( 'trav_flight_booking_admin_enqueue_scripts' ) ) {
	function trav_flight_booking_admin_enqueue_scripts() {

		// support select2
		wp_enqueue_style( 'rwmb_select2', RWMB_URL . 'css/select2/select2.css', array(), '3.2' );
		wp_enqueue_script( 'rwmb_select2', RWMB_URL . 'js/select2/select2.min.js', array(), '3.2', true );

		// datepicker
		$url = RWMB_URL . 'css/jqueryui';
		wp_register_style( 'jquery-ui-core', "{$url}/jquery.ui.core.css", array(), '1.8.17' );
		wp_register_style( 'jquery-ui-theme', "{$url}/jquery.ui.theme.css", array(), '1.8.17' );
		wp_enqueue_style( 'jquery-ui-datepicker', "{$url}/jquery.ui.datepicker.css", array( 'jquery-ui-core', 'jquery-ui-theme' ), '1.8.17' );

		// Load localized scripts
		$locale = str_replace( '_', '-', get_locale() );
		$file_path = 'jqueryui/datepicker-i18n/jquery.ui.datepicker-' . $locale . '.js';
		$deps = array( 'jquery-ui-datepicker' );
		if ( file_exists( RWMB_DIR . 'js/' . $file_path ) )
		{
			wp_register_script( 'jquery-ui-datepicker-i18n', RWMB_URL . 'js/' . $file_path, $deps, '1.8.17', true );
			$deps[] = 'jquery-ui-datepicker-i18n';
		}

		wp_enqueue_script( 'rwmb-date', RWMB_URL . 'js/' . 'date.js', $deps, RWMB_VER, true );
		wp_localize_script( 'rwmb-date', 'RWMB_Datepicker', array( 'lang' => $locale ) );

		// custom style and js
		wp_enqueue_style( 'trav_admin_flight_stype' , get_template_directory_uri() . '/inc/admin/css/style.css' ); 
		wp_enqueue_script( 'trav_admin_flight_script' , TRAV_TEMPLATE_DIRECTORY_URI . '/inc/admin/flight/js/script.js', array('jquery'), '1.0', true );
	}
}

add_action( 'admin_menu', 'trav_flight_booking_add_menu_items' );