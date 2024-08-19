<?php
if ( ! defined( 'ABSPATH' ) ) exit; // Exit if accessed directly

if ( ! class_exists( 'WP_List_Table' ) ) {
	require_once( ABSPATH . 'wp-admin/includes/class-wp-list-table.php' );
}

/**
 * functions to manage schedules
 */
if ( ! class_exists( 'Trav_Tour_Schedule_List_Table') ) :
class Trav_Tour_Schedule_List_Table extends WP_List_Table {

	function __construct() {
		global $status, $page;
		parent::__construct( array(
			'singular'  => 'schedule',     //singular name of the listed records
			'plural'    => 'schedules',    //plural name of the listed records
			'ajax'      => false        //does this table support ajax?
		) );
	}

	function column_default( $item, $column_name ) {
		$link_pattern = '<a href="edit.php?post_type=%1s&page=%2$s&action=%3$s&schedule_id=%4$s">%5$s</a>';

		switch( $column_name ) {
			case 'id':
			case 'max_people':
			case 'price':
			case 'child_price':
			case 'duration':
				return $item[ $column_name ];
			case 'tour_date':
				$actions = array(
					'edit'		=> sprintf( $link_pattern, sanitize_text_field( $_REQUEST['post_type'] ), 'schedules', 'edit', $item['id'], 'Edit' ),
					'delete'	=> sprintf( $link_pattern, sanitize_text_field( $_REQUEST['post_type'] ), 'schedules', 'delete', $item['id'] . '&_wpnonce=' . wp_create_nonce( 'schedule_delete' ), 'Delete' )
				);
				$content = sprintf( $link_pattern, sanitize_text_field( $_REQUEST['post_type'] ), 'schedules', 'edit', $item['id'], $item[$column_name] );
				//Return the title contents
				return sprintf( '%1$s %2$s', $content, $this->row_actions( $actions ) );
			case 'other':
				if ( empty( $item['is_daily'] ) ) return '';
				if ( empty( $item['date_to'] ) || ( $item['date_to'] == '9999-12-31' ) ) return 'daily';
				return 'daily until ' . $item['date_to'];
			case 'st_id':
				return trav_tour_get_schedule_type_title( $item['tour_id'], $item['st_id'] );
			default:
				return print_r( $item, true ); //Show the whole array for troubleshooting purposes
		}
	}

	function column_cb( $item ) {
		return sprintf( '<input type="checkbox" name="%1$s[]" value="%2$s" />', $this->_args['singular'], $item['id'] );
	}

	function column_tour_name( $item ) {
		return '<a href="' . get_edit_post_link( $item['tour_id'] ) . '">' . $item['tour_name'] . '</a>';
	}

	function get_columns() {
		$columns = array(
			'cb'        => '<input type="checkbox" />', //Render a checkbox instead of text
			'id'        => __( 'ID', 'trav' ),
			'tour_date' => __( 'Tour Date', 'trav' ),
			'tour_name' => __( 'Tour Name', 'trav' ),
			'max_people'=> __( 'Max People', 'trav' ),
			'price'     => __( 'Price (adult)', 'trav' ),
			'child_price'=> __( 'Price (child)', 'trav' ),
			'duration'  => __( 'Duration', 'trav' ),
			'st_id'  => __( 'Schedule Type', 'trav' ),
			'other'  => __( 'Other', 'trav' ),
		);
		return $columns;
	}

	function get_sortable_columns() {
		$sortable_columns = array(
			'id'			=> array( 'id', false ),
			'tour_date'		=> array( 'tour_date', false ),
			'duration'		=> array( 'duration', false ),
			'tour_name'		=> array( 'tour_name', false ),
			'max_people'	=> array( 'max_people', false ),
			'price'			=> array( 'price', false ),
			'child_price'	=> array( 'child_price', false )
		);
		return $sortable_columns;
	}

	function get_bulk_actions() {
		$actions = array(
			'bulk_delete'    => 'Delete'
		);
		return $actions;
	}

	function process_bulk_action() {
		global $wpdb;
		//Detect when a bulk action is being triggered...

		if ( isset( $_POST['_wpnonce'] ) && ! empty( $_POST['_wpnonce'] ) ) {

			$nonce  = filter_input( INPUT_POST, '_wpnonce', FILTER_SANITIZE_STRING );
			$action = 'bulk-' . $this->_args['plural'];

			if ( ! wp_verify_nonce( $nonce, $action ) )
				wp_die( 'Sorry, your nonce did not verify' );

		}
		if ( 'bulk_delete'===$this->current_action() ) {
			$selected_ids = $_GET[ $this->_args['singular'] ];
			$how_many = count($selected_ids);
			$placeholders = array_fill(0, $how_many, '%d');
			$format = implode(', ', $placeholders);
			$current_user_id = get_current_user_id();
			$post_table_name  = esc_sql( $wpdb->prefix . 'posts' );
			$sql = '';

			if ( current_user_can( 'manage_options' ) ) {
				$sql = sprintf('DELETE FROM %1$s WHERE id IN (%2$s)', TRAV_TOUR_SCHEDULES_TABLE, "$format" );
			} else {
				$sql = sprintf('DELETE %1$s FROM %1$s INNER JOIN %2$s as tour ON tour_id=tour.ID WHERE %1$s.id IN (%3$s) AND tour.post_author = %4$d', TRAV_TOUR_SCHEDULES_TABLE, $post_table_name, "$format", $current_user_id );
			}

			//$sql = sprintf( $sql, $selected_ids );
			$sql = $wpdb->prepare( $sql, $selected_ids );
			$wpdb->query( $sql );
			wp_redirect( admin_url( 'edit.php?post_type=tour&page=schedules&bulk_delete=true') );
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
		$post_table_name  = $wpdb->prefix . 'posts';

		$where = "1=1";
		if ( isset( $_REQUEST['tour_id'] ) ) $where .= " AND schedule.tour_id = '" . esc_sql( trav_tour_org_id( $_REQUEST['tour_id'] ) ) . "'";
		if ( isset( $_REQUEST['st_id'] ) ) $where .= " AND schedule.st_id = '" . esc_sql( $_REQUEST['st_id'] ) . "'";
		if ( isset( $_REQUEST['date'] ) ) $where .= " AND (schedule.tour_date = '" . esc_sql( $_REQUEST['date'] ) . "' OR ( schedule.is_daily = 1 AND schedule.tour_date <= '" . esc_sql( $_REQUEST['date'] ) . "' AND schedule.date_to >= '" . esc_sql( $_REQUEST['date'] ) . "' ) )" ;
		if ( ! current_user_can( 'manage_options' ) ) { $where .= " AND tour.post_author = '" . get_current_user_id() . "' "; }

		$sql = sprintf( 'SELECT schedule.* , tour.ID as tour_id, tour.post_title as tour_name FROM %1$s as schedule
			INNER JOIN %2$s as tour ON schedule.tour_id=tour.ID
			WHERE ' . $where . ' ORDER BY %3$s %4$s
			LIMIT %5$s, %6$s' , TRAV_TOUR_SCHEDULES_TABLE, $post_table_name, $orderby, $order, ( $per_page * ( $current_page - 1 ) ), $per_page );

		$data = $wpdb->get_results( $sql, ARRAY_A );

		$sql = sprintf( 'SELECT COUNT(*) FROM %1$s as schedule INNER JOIN %2$s as tour ON schedule.tour_id=tour.ID WHERE ' . $where , TRAV_TOUR_SCHEDULES_TABLE, $post_table_name );
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
 * add schedule list page to menu
 */
if ( ! function_exists( 'trav_tour_schedule_add_menu_items' ) ) {
	function trav_tour_schedule_add_menu_items() {
		$page = add_submenu_page( 'edit.php?post_type=tour', 'Tour Schedules', 'Schedules', 'edit_accommodations', 'schedules', 'trav_tour_schedule_render_pages' );
		add_action( 'admin_print_scripts-' . $page, 'trav_tour_schedule_admin_enqueue_scripts' );
	}
}

/*
 * schedule admin main actions
 */
if ( ! function_exists( 'trav_tour_schedule_render_pages' ) ) {
	function trav_tour_schedule_render_pages() {

		if ( ( ! empty( $_REQUEST['action'] ) ) && ( ( 'add' == $_REQUEST['action'] ) || ( 'edit' == $_REQUEST['action'] ) ) ) {
			trav_tour_schedule_render_manage_page();
		} elseif ( ( ! empty( $_REQUEST['action'] ) ) && ( 'delete' == $_REQUEST['action'] ) ) {
			trav_tour_schedule_delete_action();
		} else {
			trav_tour_schedule_render_list_page();
		}
	}
}

/*
 * render schedule list page
 */
if ( ! function_exists( 'trav_tour_schedule_render_list_page' ) ) {
	function trav_tour_schedule_render_list_page() {
		global $wpdb;
		$travTourScheduleTable = new Trav_Tour_Schedule_List_Table();
		$travTourScheduleTable->prepare_items();
		$tour_id = empty( $_REQUEST['tour_id'] ) ? '' : $_REQUEST['tour_id'];
		$st_id = empty( $_REQUEST['st_id'] ) ? '' : $_REQUEST['st_id'];
		$schedule_types = trav_tour_get_schedule_types( $tour_id );
		?>

		<div class="wrap">
			<h2><?php _e('Tour Schedules ', 'trav') ?><a href="edit.php?post_type=tour&amp;page=schedules&amp;action=add" class="add-new-h2"><?php _e('Add New', 'trav') ?></a></h2>
			<?php if ( isset( $_REQUEST['bulk_delete'] ) ) echo '<div id="message" class="updated below-h2"><p>' . __('Schedules deleted', 'trav') . '</p></div>'?>
			<select id="tour_id">
				<?php echo trav_tour_get_tour_list( $tour_id ) ?>
			</select>
			<span class="schedule_type_wrapper<?php if ( empty( $schedule_types ) ) echo ' hide' ?>">
				<select name="st_id" id="schedule_type">
					<?php echo trav_tour_get_schedule_type_list( $tour_id, $st_id ) ?>
				</select>
			</span>
			<input type="text" id="tour_date" placeholder="<?php _e('Filter by Start Date', 'trav') ?>" value="<?php echo isset($_REQUEST['date']) ? esc_attr( $_REQUEST['date'] ):'' ?>">
			<input type="button" name="schedule_filter" id="schedule-filter" class="button" value="<?php _e('Filter', 'trav') ?>">
			<a href="edit.php?post_type=tour&amp;page=schedules" class="button-secondary"><?php _e('Show All', 'trav') ?></a>
			<form id="tour-schedules-filter" method="get">
				<input type="hidden" name="post_type" value="<?php echo esc_attr( $_REQUEST['post_type'] ) ?>" />
				<input type="hidden" name="page" value="<?php echo esc_attr( $_REQUEST['page'] ) ?>" />
				<?php $travTourScheduleTable->display() ?>
			</form>
			
		</div>
		<?php
	}
}

/*
 * render schedule detail page
 */
if ( ! function_exists( 'trav_tour_schedule_render_manage_page' ) ) {
	function trav_tour_schedule_render_manage_page() {

		global $wpdb;

		if ( ! empty( $_POST['save'] ) ) {
			trav_tour_schedule_save_action();
			return;
		}

		$default_schedule_data = array( 
			'id'			=> '',
			'tour_id'		=> '',
			'st_id'			=> 0,
			'tour_date'		=> date( 'Y-m-d' ),
			'duration'		=> '',
			'max_people'	=> 1,
			'price'			=> '',
			'child_price'	=> '',
			'is_daily'		=> 0,
			'date_to'		=> '',
		);
		$schedule_data = array();

		if ( 'add' == $_REQUEST['action'] ) {
			$page_title = __("Add New Tour Schedule", "trav");
		} elseif ( 'edit' == $_REQUEST['action'] ) {
			$page_title = __('Edit Tour Schedule', 'trav') . '<a href="edit.php?post_type=tour&amp;page=schedules&amp;action=add" class="add-new-h2">' . __('Add New', 'trav') . '</a>';
			
			if ( empty( $_REQUEST['schedule_id'] ) ) {
				echo "<h2>" . __("You attempted to edit an item that doesn't exist. Perhaps it was deleted?", "trav") . "</h2>";
				return;
			}
			$schedule_id = sanitize_text_field( $_REQUEST['schedule_id'] );
			$post_table_name  = $wpdb->prefix . 'posts';

			$where = 'schedule.id = %3$d';
			if ( ! current_user_can( 'manage_options' ) ) { $where .= " AND tour.post_author = '" . get_current_user_id() . "' "; }

			$sql = sprintf( 'SELECT schedule.* , tour.post_title as tour_name FROM %1$s as schedule
				INNER JOIN %2$s as tour ON schedule.tour_id=tour.ID
				WHERE ' . $where , TRAV_TOUR_SCHEDULES_TABLE, $post_table_name, $schedule_id );

			$schedule_data = $wpdb->get_row( $sql, ARRAY_A );
			if ( empty( $schedule_data ) ) {
				echo "<h2>" . __("You attempted to edit an item that doesn't exist. Perhaps it was deleted?", "trav") . "</h2>";
				return;
			}
		}
		$schedule_data = array_replace( $default_schedule_data, $schedule_data );
		$schedule_types = trav_tour_get_schedule_types( $schedule_data['tour_id'] );
		$schedule_data['tour_id'] = trav_get_current_language_post_id($schedule_data['tour_id']);
		?>

		<div class="wrap">
			<h2><?php echo wp_kses_post( $page_title ); ?></h2>
			<?php if ( isset( $_REQUEST['updated'] ) ) echo '<div id="message" class="updated below-h2"><p>Schedule saved</p></div>'?>
			<form method="post" onsubmit="return manage_schedule_validateForm();">
				<input type="hidden" name="id" value="<?php echo esc_attr( $schedule_data['id'] ); ?>">
				<table class="trav_admin_table trav_tour_schedule_manage_table">
					<tr>
						<th><?php _e('Tour', 'trav') ?></th>
						<td>
							<select name="tour_id" id="tour_id">
								<?php echo trav_tour_get_tour_list( $schedule_data['tour_id'] ) ?>
							</select>
						</td>
					</tr>
					<tr class="schedule_type_wrapper<?php if ( empty( $schedule_types ) ) echo ' hide' ?>">
						<th><?php _e('Schedule Type', 'trav') ?></th>
						<td>
							<select name="st_id" id="schedule_type">
								<?php echo trav_tour_get_schedule_type_list( $schedule_data['tour_id'], $schedule_data['st_id'] ) ?>
							</select>
						</td>
					</tr>
					<tr>
						<th><?php _e('Max People', 'trav') ?></th>
						<td><input type="number" name="max_people" min="1" value="<?php echo esc_attr( $schedule_data['max_people'] ); ?>"></td>
					</tr>
					<tr>
						<th><?php _e('Duration', 'trav') ?></th>
						<td><input type="text" name="duration" id="duration" value="<?php echo esc_attr( $schedule_data['duration'] ); ?>"></td>
						<td><?php _e('You can put duration information to this field. For example 2 hours, 1 day etc', 'trav') ?></td>
					</tr>
					<tr>
						<th><?php _e('Is Daily?', 'trav') ?></th>
						<td>
							<label for="is_daily">
								<input type="checkbox" id="is_daily" name="is_daily" value="1" <?php if ( ! empty( $schedule_data['is_daily'] ) ) echo esc_attr( 'checked' ); ?>>
							</label>
						</td>
						<td><?php _e('Check this option if this tour is opened daily based.', 'trav') ?></td>
					</tr>
					<tr class="start_date">
						<th><?php _e('Tour Date', 'trav') ?></th>
						<td><input type="text" name="tour_date" id="tour_date" value="<?php echo esc_attr( $schedule_data['tour_date'] ); ?>"></td>
					</tr>
					<tr class="end_date">
						<th><?php _e('End Date', 'trav') ?></th>
						<td><input type="text" name="date_to" id="date_to" value="<?php if ( ! empty( $schedule_data['date_to'] ) && ( $schedule_data['date_to'] != '9999-12-31' ) ) echo esc_attr( $schedule_data['date_to'] ); ?>"></td>
					</tr>
					<tr>
						<th><?php _e('Charge Per Person?', 'trav') ?></th>
						<td>
							<label for="per_person_yn">
								<input type="checkbox" id="per_person_yn" name="per_person_yn" value="1" <?php if ( ! empty( $schedule_data['per_person_yn'] ) ) echo esc_attr( 'checked' ); ?>>
							</label>
						</td>
						<td><?php _e('Check this option if this tour is charged per person.', 'trav') ?></td>
					</tr>
					<tr class="price">
						<th><?php _e('Price', 'trav') ?></th>
						<td><input type="text" name="price" value="<?php echo esc_attr( $schedule_data['price'] ); ?>"></td>
					</tr>
					<tr class="child_price">
						<th><?php _e('Price Per Child', 'trav') ?></th>
						<td><input type="text" name="child_price" value="<?php echo esc_attr( $schedule_data['child_price'] ); ?>"></td>
					</tr>
				</table>
				<input type="submit" class="button-primary" name="save" value="<?php _e('Save Schedule', 'trav') ?>">
				<a href="edit.php?post_type=tour&amp;page=schedules" class="button-secondary"><?php _e('Cancel', 'trav') ?></a>
				<?php wp_nonce_field('trav_tour_schedule_manage','schedule_save'); ?>
			</form>
		</div>
		<?php
	}
}

/*
 * schedule delete action
 */
if ( ! function_exists( 'trav_tour_schedule_delete_action' ) ) {
	function trav_tour_schedule_delete_action() {

		global $wpdb;
		// data validation
		if ( empty( $_REQUEST['schedule_id'] ) ) {
			print __('Sorry, you tried to remove nothing.', 'trav');
			exit;
		}

		// nonce check
		if ( ! isset( $_GET['_wpnonce'] ) || ! wp_verify_nonce( $_GET['_wpnonce'], 'schedule_delete' ) ) {
			print __('Sorry, your nonce did not verify.', 'trav');
			exit;
		}

		// check ownership if user is not admin
		if ( ! current_user_can( 'manage_options' ) ) {
			$sql = sprintf( 'SELECT schedule.tour_id FROM %1$s as schedule WHERE schedule.id = %2$d' , TRAV_TOUR_SCHEDULES_TABLE, $_REQUEST['schedule_id'] );
			$tour_id = $wpdb->get_var( $sql );
			$post_author_id = get_post_field( 'post_author', $tour_id );
			if ( get_current_user_id() != $post_author_id ) {
				print __('You don\'t have permission to remove other\'s item.', 'trav');
				exit;
			}
		}

		// do action
		$wpdb->delete( TRAV_TOUR_SCHEDULES_TABLE, array( 'id' => $_REQUEST['schedule_id'] ) );
		//}
		wp_redirect( admin_url( 'edit.php?post_type=tour&page=schedules') );
		exit;
	}
}

/*
 * schedule save action
 */
if ( ! function_exists( 'trav_tour_schedule_save_action' ) ) {
	function trav_tour_schedule_save_action() {

		if ( ! isset( $_POST['schedule_save'] ) || ! wp_verify_nonce( $_POST['schedule_save'], 'trav_tour_schedule_manage' ) ) {
			print __('Sorry, your nonce did not verify.', 'trav');
			exit;
		} else {

			global $wpdb;

			$default_schedule_data = array( 
				'tour_id'		=> '',
				'max_people'	=> 0,
				'tour_date'		=> date( 'Y-m-d' ),
				'duration'		=> '',
				'price'			=> 0,
				'child_price'	=>0,
				'is_daily'		=>0,
				'per_person_yn'	=>0,
				'st_id'			=>0,
				'date_to'		=>'9999-12-31',
			);

			$table_fields = array( 'tour_date', 'duration', 'tour_id', 'max_people', 'price', 'child_price', 'is_daily', 'per_person_yn', 'st_id', 'date_to' );
			$data = array();
			foreach ( $table_fields as $table_field ) {
				if ( ! empty( $_POST[ $table_field ] ) ) {
					$data[ $table_field ] = sanitize_text_field( $_POST[ $table_field ] );
				}
			}

			$data = array_replace( $default_schedule_data, $data );
			$data['tour_id'] = trav_tour_org_id( $data['tour_id'] );
			if ( empty( $_POST['id'] ) ) {
				//insert
				$wpdb->insert( TRAV_TOUR_SCHEDULES_TABLE, $data );
				$id = $wpdb->insert_id;
			} else {
				//update
				$wpdb->update( TRAV_TOUR_SCHEDULES_TABLE, $data, array( 'id' => sanitize_text_field( $_POST['id'] ) ) );
				$id = sanitize_text_field( $_POST['id'] );
			}
			wp_redirect( admin_url( 'edit.php?post_type=tour&page=schedules&action=edit&schedule_id=' . $id . '&updated=true') );
			exit;
		}
	}
}

/*
 * schedule admin enqueue script action
 */
if ( ! function_exists( 'trav_tour_schedule_admin_enqueue_scripts' ) ) {
	function trav_tour_schedule_admin_enqueue_scripts() {

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
		wp_enqueue_style( 'trav_admin_tour_style' , TRAV_TEMPLATE_DIRECTORY_URI . '/inc/admin/css/style.css' ); 
		wp_enqueue_script( 'trav_admin_tour_script' , TRAV_TEMPLATE_DIRECTORY_URI . '/inc/admin/tour/js/script.js', array('jquery'), '1.0', true );
	}
}

add_action( 'admin_menu', 'trav_tour_schedule_add_menu_items' );