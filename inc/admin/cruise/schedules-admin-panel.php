<?php
if ( ! defined( 'ABSPATH' ) ) exit; // Exit if accessed directly
if ( ! class_exists( 'WP_List_Table' ) ) {
	require_once( ABSPATH . 'wp-admin/includes/class-wp-list-table.php' );
}

/**
 * functions to manage schedules
 */
if ( ! class_exists( 'Trav_Cruise_Schedule_List_Table') ) :
class Trav_Cruise_Schedule_List_Table extends WP_List_Table {

	function __construct() {
		global $status, $page;
		parent::__construct( array(
			'singular'  => 'cruise_schedule',     //singular name of the listed records
			'plural'    => 'cruise_schedules',    //plural name of the listed records
			'ajax'      => false        //does this table support ajax?
		) );
	}

	function column_default( $item, $column_name ) {
		$link_pattern = '<a href="edit.php?post_type=%1s&page=%2$s&action=%3$s&schedule_id=%4$s">%5$s</a>';
		switch( $column_name ) {
			case 'id':
			case 'duration':
			case 'departure':
			case 'arrival':
				return $item[ $column_name ];
			case 'date_from':
			//case 'date_to':
				$actions = array(
					'edit'      => sprintf( $link_pattern, sanitize_text_field( $_REQUEST['post_type'] ), 'cruise_schedules', 'edit', $item['id'], 'Edit' ),
					'delete'    => sprintf( $link_pattern, sanitize_text_field( $_REQUEST['post_type'] ), 'cruise_schedules', 'delete', $item['id'] . '&_wpnonce=' . wp_create_nonce( 'schedule_delete' ), 'Delete' )
				);
				$content = sprintf( $link_pattern, sanitize_text_field( $_REQUEST['post_type'] ), 'cruise_schedules', 'edit', $item['id'], $item[$column_name] );
				//Return the title contents
				return sprintf( '%1$s %2$s', $content, $this->row_actions( $actions ) );
			default:
				return print_r( $item, true ); //Show the whole array for troubleshooting purposes
		}
	}

	function column_cb( $item ) {
		return sprintf( '<input type="checkbox" name="%1$s[]" value="%2$s" />', $this->_args['singular'], $item['id'] );
	}

	function column_cruise_name( $item ) {
		return '<a href="' . get_edit_post_link( $item['cruise_id'] ) . '">' . $item['cruise_name'] . '</a>';
	}


	function get_columns() {
		$columns = array(
			'cb'        	=> '<input type="checkbox" />', //Render a checkbox instead of text
			'id'        	=> __( 'ID', 'trav' ),
			'date_from'		=> __( 'Date From', 'trav' ),
			//'date_to'  => __( 'Date To', 'trav' ),
			'duration'  	=> __( 'Duration', 'trav' ),
			'cruise_name'  	=> __( 'Cruise Name', 'trav' ),
			'departure'  	=> __( 'Departure', 'trav' ),
			'arrival'  	=> __( 'Arrival', 'trav' ),
		);
		return $columns;
	}

	function get_sortable_columns() {
		$sortable_columns = array(
			'id'            => array( 'id', false ),
			'date_from' => array( 'date_from', false ),
			'cruise_name'    => array( 'cruise_name', false ),
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
				$sql = sprintf( 'DELETE FROM %1$s WHERE id IN (%2$s)', TRAV_CRUISE_SCHEDULES_TABLE, "$format" );
			} else {
				$sql = sprintf( 'DELETE %1$s FROM %1$s INNER JOIN %2$s as cruise ON cruise_id=cruise.ID WHERE %1$s.id IN (%3$s) AND cruise.post_author = %4$d', TRAV_CRUISE_SCHEDULES_TABLE, $post_table_name, "$format", $current_user_id );
			}

			//$sql = sprintf( $sql, $selected_ids );
			$sql = $wpdb->prepare( $sql, $selected_ids );
			$wpdb->query( $sql );
			wp_redirect( admin_url( 'edit.php?post_type=cruise&page=cruise_schedules&bulk_delete=true') );
		}
		
	}

	function prepare_items() {
		global $wpdb;

		$default_lang = trav_get_default_language();
		trav_switch_language( $default_lang );

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
		if ( ! empty( $_REQUEST['cruise_id'] ) ) $where .= " AND Trav_Schedules.cruise_id = '" . esc_sql( trav_cruise_org_id( $_REQUEST['cruise_id'] ) ) . "'";
		if ( ! empty( $_REQUEST['date'] ) ) $where .= " AND Trav_Schedules.date_from <= '" . esc_sql( $_REQUEST['date'] ) . "' and Trav_Schedules.date_to > '" . esc_sql( $_REQUEST['date'] ) . "'" ;
		if ( ! current_user_can( 'manage_options' ) ) { $where .= " AND cruise.post_author = '" . get_current_user_id() . "' "; }

		$sql = sprintf( 'SELECT Trav_Schedules.* , cruise.ID as cruise_id, cruise.post_title as cruise_name FROM %1$s as Trav_Schedules
						INNER JOIN %2$s as cruise ON Trav_Schedules.cruise_id=cruise.ID
						WHERE ' . $where . ' ORDER BY %4$s %5$s
						LIMIT %6$s, %7$s' , TRAV_CRUISE_SCHEDULES_TABLE, $post_table_name, '', $orderby, $order, ( $per_page * ( $current_page - 1 ) ), $per_page );
		$data = $wpdb->get_results( $sql, ARRAY_A );

		$sql = sprintf( 'SELECT COUNT(*) FROM %1$s as Trav_Schedules INNER JOIN %2$s as cruise ON Trav_Schedules.cruise_id=cruise.ID WHERE %3$s' , TRAV_CRUISE_SCHEDULES_TABLE, $post_table_name, $where );
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
if ( ! function_exists( 'trav_cruise_schedule_add_menu_items' ) ) {
	function trav_cruise_schedule_add_menu_items() {
		$page = add_submenu_page( 'edit.php?post_type=cruise', 'Cruise Schedules', 'Schedules', 'edit_accommodations', 'cruise_schedules', 'trav_cruise_schedule_render_pages' );
		add_action( 'admin_print_scripts-' . $page, 'trav_cruise_schedule_admin_enqueue_scripts' );
	}
}

/*
 * schedule admin main actions
 */
if ( ! function_exists( 'trav_cruise_schedule_render_pages' ) ) {
	function trav_cruise_schedule_render_pages() {
		if ( ( ! empty( $_REQUEST['action'] ) ) && ( ( 'add' == $_REQUEST['action'] ) || ( 'edit' == $_REQUEST['action'] ) ) ) {
			trav_cruise_schedule_render_manage_page();
		} elseif ( ( ! empty( $_REQUEST['action'] ) ) && ( 'delete' == $_REQUEST['action'] ) ) {
			trav_cruise_schedule_delete_action();
		} else {
			trav_cruise_schedule_render_list_page();
		}
	}
}

/*
 * render schedule list page
 */
if ( ! function_exists( 'trav_cruise_schedule_render_list_page' ) ) {
	function trav_cruise_schedule_render_list_page() {
		global $wpdb;

		$default_lang = trav_get_default_language();
		trav_switch_language( $default_lang );

		$travScheduleTable = new Trav_Cruise_Schedule_List_Table();
		$travScheduleTable->prepare_items();
		?>

		<div class="wrap">
			<h2><?php _e( 'Cruise Schedules', 'trav' ); ?> <a href="edit.php?post_type=cruise&amp;page=cruise_schedules&amp;action=add" class="add-new-h2"><?php _e( 'Add New', 'trav' ); ?></a></h2>
			<?php if ( isset( $_REQUEST['bulk_delete'] ) ) echo '<div id="message" class="updated below-h2"><p>' . __( 'Schedules deleted', 'trav' ) . '</p></div>'?>
			<select id="cruise_filter">
				<option></option>
				<?php
				$args = array(
						'post_type'         => 'cruise',
						'posts_per_page'    => -1,
						'orderby'           => 'title',
						'order'             => 'ASC'
				);
				/* bussinerss managers can see their own post only */
				if ( ! current_user_can( 'manage_options' ) ) {
					$args['author'] = get_current_user_id();
				}

				$cruise_query = new WP_Query( $args );

				if ( $cruise_query->have_posts() ) {
					while ( $cruise_query->have_posts() ) {
						$cruise_query->the_post();
						$selected = '';
						$id = $cruise_query->post->ID;
						if ( ! empty( $_REQUEST['cruise_id'] ) && ( $_REQUEST['cruise_id'] == $id ) ) $selected = ' selected ';
						echo '<option ' . esc_attr( $selected ) . 'value="' . esc_attr( $id ) .'">' . wp_kses_post( get_the_title( $id ) ) . '</option>';
					}
				} else {
					// no posts found
				}
				/* Restore original Post Data */
				wp_reset_postdata();
				?>
			</select>
			<input type="text" id="date_filter" placeholder="<?php _e( 'Filter by Date', 'trav' ); ?>" value="<?php echo isset($_REQUEST['date']) ? esc_attr( $_REQUEST['date'] ):'' ?>">
			<input type="button" name="schedule_filter" id="schedule-filter" class="button" value="<?php _e( 'Filter', 'trav' ); ?>">
			<a href="edit.php?post_type=cruise&amp;page=cruise_schedules" class="button-secondary"><?php _e( 'Show All', 'trav' ); ?></a>
			<form id="cruise-schedules-filter" method="get">
				<input type="hidden" name="post_type" value="<?php echo esc_attr( $_REQUEST['post_type'] ) ?>" />
				<input type="hidden" name="page" value="<?php echo esc_attr( $_REQUEST['page'] ) ?>" />
				<?php $travScheduleTable->display() ?>
			</form>
			
		</div>
		<?php
	}
}

/*
 * render schedule detail page
 */
if ( ! function_exists( 'trav_cruise_schedule_render_manage_page' ) ) {
	function trav_cruise_schedule_render_manage_page() {
		global $wpdb;

		$default_lang = trav_get_default_language();
		trav_switch_language( $default_lang );

		if ( ! empty( $_POST['save'] ) ) {
			trav_cruise_schedule_save_action();
			return;
		}

		$default_schedule_data = array(  'id'                => '',
										'cruise_id'  => '',
										'date_from'        => date( 'Y-m-d' ),
										'date_to'          => '',
										'duration'	=> 1,
										'departure' => '',
										'arrival' => '',
										'itinerary' => ''
									);
		$schedule_data = array();

		if ( 'add' == $_REQUEST['action'] ) {
			$page_title = __( 'Add New Cruise Schedule', 'trav' );
		} elseif ( 'edit' == $_REQUEST['action'] ) {
			$page_title = __( 'Edit Cruise Schedule', 'trav' ) . '<a href="edit.php?post_type=cruise&amp;page=cruise_schedules&amp;action=add" class="add-new-h2">' . __ ( 'Add New' , 'trav' ) . '</a>';
			
			if ( empty( $_REQUEST['schedule_id'] ) ) {
				echo "<h2>" . __( 'You attempted to edit an item that doesn\'t exist. Perhaps it was deleted?', 'trav' ) . "</h2>";
				return;
			}
			$schedule_id = sanitize_text_field( $_REQUEST['schedule_id'] );
			$post_table_name  = $wpdb->prefix . 'posts';

			$where = 'Trav_Schedules.id = %3$d';
			if ( ! current_user_can( 'manage_options' ) ) { $where .= " AND cruise.post_author = '" . get_current_user_id() . "' "; }

			$sql = sprintf( 'SELECT Trav_Schedules.* , cruise.post_title as cruise_name FROM %1$s as Trav_Schedules
							INNER JOIN %2$s as cruise ON Trav_Schedules.cruise_id=cruise.ID
										WHERE ' . $where , TRAV_CRUISE_SCHEDULES_TABLE, $post_table_name, $schedule_id );

			$schedule_data = $wpdb->get_row( $sql, ARRAY_A );
			if ( empty( $schedule_data ) ) {
				echo "<h2>" . __( 'You attempted to edit an item that doesn\'t exist. Perhaps it was deleted?', 'trav' ) . "</h2>";
				return;
			}
		}

		$schedule_data = array_replace( $default_schedule_data, $schedule_data );
		?>

		<div class="wrap">
			<h2><?php echo wp_kses_post( $page_title ); ?></h2>
			<?php if ( isset( $_REQUEST['updated'] ) ) echo '<div id="message" class="updated below-h2"><p>' . __( 'Schedule saved', 'trav' ) . '</p></div>'; ?>
			<form method="post" onsubmit="return manage_schedule_validateForm();">
				<input type="hidden" name="id" value="<?php if ( ! empty( $schedule_data['id'] ) ) echo esc_attr( $schedule_data['id'] ); ?>">
				<table class="trav_admin_table trav_cruise_schedule_manage_table">
					<tr>
						<th>Cruise</th>
						<td>
							<select name="cruise_id" id="cruise_id">
								<option></option>
								<?php
									$args = array(
											'post_type'         => 'cruise',
											'posts_per_page'    => -1,
											'orderby'           => 'title',
											'order'             => 'ASC'
									);
									/* bussinerss managers can see their own post only */
									if ( ! current_user_can( 'manage_options' ) ) {
										$args['author'] = get_current_user_id();
									}
									$cruise_query = new WP_Query( $args );

									if ( $cruise_query->have_posts() ) {
										while ( $cruise_query->have_posts() ) {
											$cruise_query->the_post();
											$selected = '';
											$id = $cruise_query->post->ID;
											if ( ( ! empty( $schedule_data['cruise_id'] ) ) && ( $schedule_data['cruise_id'] == $id ) ) $selected = ' selected ';
											echo '<option ' . esc_attr( $selected ) . 'value="' . esc_attr( $id ) .'">' . wp_kses_post( get_the_title( $id ) ) . '</option>';
										}
									}
									wp_reset_postdata();
								?>
							</select>
						</td>
					</tr>
					<tr>
						<th><?php _e( 'Date From', 'trav' ); ?></th>
						<td><input type="text" name="date_from" id="date_from" value="<?php if ( ! empty( $schedule_data['date_from'] ) ) echo esc_attr( $schedule_data['date_from'] ); ?>">
						<span><?php _e( 'If you leave this field blank it will be set as current date', 'trav' ); ?></span></td>
					</tr>
					<tr>
						<th><?php _e( 'Duration', 'trav' ); ?></th>
						<td><input type="number" name="duration" min="1" value="<?php if ( ! empty( $schedule_data['duration'] ) ) echo esc_attr( $schedule_data['duration'] ); ?>">
						<span><?php _e( 'If you leave this field blank it will be set as 1 day', 'trav' ); ?></span></td>
					</tr>
					<tr>
						<th><?php _e( 'Departure', 'trav' ); ?></th>
						<td><input type="text" name="departure" min="1" value="<?php if ( ! empty( $schedule_data['departure'] ) ) echo esc_attr( $schedule_data['departure'] ); ?>"></td>
					</tr>
					<tr>
						<th><?php _e( 'Arrival', 'trav' ); ?></th>
						<td><input type="text" name="arrival" min="1" value="<?php if ( ! empty( $schedule_data['arrival'] ) ) echo esc_attr( $schedule_data['arrival'] ); ?>"></td>
					</tr>
					<tr class="itinerary">
						<th><?php _e( 'Cruise Itinerary', 'trav' ); ?></th>
						<td>
							<table>
								<tr>
									<th><?php _e( 'Day', 'trav' ); ?></th>
									<th><?php _e( 'Ports of Call', 'trav' ); ?></th>
									<th><?php _e( 'Arrival', 'trav' ); ?></th>
									<th><?php _e( 'Departure', 'trav' ); ?></th>
								</tr>
								<?php
									$itinerary = array();
									if ( ! empty( $schedule_data['itinerary'] ) ) $itinerary = unserialize( $schedule_data['itinerary'] );
									if ( ! empty( $itinerary ) ) {
										$i = 0;
										foreach ( $itinerary as $itinerary_pair ) {
											if ( is_array( $itinerary_pair ) && ( count( $itinerary_pair ) >=2 ) ) {
												$html = '<tr class="clone-field">';
												$html .= '<td><input type="number" min="1" name="itinerary[' . $i . '][]" value="' . $itinerary_pair[0] . '"></td>';
												$html .= '<td><input type="text" name="itinerary[' . $i . '][]" value="' . $itinerary_pair[1] . '"></td>';
												$html .= '<td><input type="text" name="itinerary[' . $i . '][]" value="' . $itinerary_pair[2] . '"></td>';
												$html .= '<td><input type="text" name="itinerary[' . $i . '][]" value="' . $itinerary_pair[3] . '"><a href="#" class="button remove-clone">-</a></td>';
												$html .= '</tr>';
												echo ( $html );
												$i++;
											}
										}
									} else {
								?>
										<tr class="clone-field">
											<td><input type="number" min="1" name="itinerary[0][]"></td>
											<td><input type="text" name="itinerary[0][]"></td>
											<td><input type="text" name="itinerary[0][]"></td>
											<td><input type="text" name="itinerary[0][]"><a href="#" class="button remove-clone">-</a></td>
										</tr>
								<?php
									}
								?>

							</table>
							<a href="#" class="button-primary add-clone">+</a>
						</td>
					</tr>
				</table>
				<input type="submit" class="button-primary" name="save" value="Save schedule">
				<a href="edit.php?post_type=cruise&amp;page=cruise_schedules" class="button-secondary"><?php _e( 'Cancel', 'trav' ); ?></a>
				<?php wp_nonce_field('trav_cruise_schedule_manage','schedule_save'); ?>
			</form>
		</div>
		<?php
	}
}

/*
 * schedule delete action
 */
if ( ! function_exists( 'trav_cruise_schedule_delete_action' ) ) {
	function trav_cruise_schedule_delete_action() {

		global $wpdb;
		// data validation
		if ( empty( $_REQUEST['schedule_id'] ) ) {
			print __( 'Sorry, you tried to remove nothing.', 'trav' );
			exit;
		}

		// nonce check
		if ( ! isset( $_GET['_wpnonce'] ) || ! wp_verify_nonce( $_GET['_wpnonce'], 'schedule_delete' ) ) {
			print __( 'Sorry, your nonce did not verify.', 'trav' );
			exit;
		}

		// check ownership if user is not admin
		if ( ! current_user_can( 'manage_options' ) ) {
			$sql = sprintf( 'SELECT Trav_Schedules.cruise_id FROM %1$s as Trav_Schedules WHERE Trav_Schedules.id = %2$d' , TRAV_CRUISE_SCHEDULES_TABLE, $_REQUEST['schedule_id'] );
			$cruise_id = $wpdb->get_var( $sql );
			$post_author_id = get_post_field( 'post_author', $cruise_id );
			if ( get_current_user_id() != $post_author_id ) {
				print __( 'You don\'t have permission to remove other\'s item.', 'trav' );
				exit;
			}
		}

		// do action
		$wpdb->delete( TRAV_CRUISE_SCHEDULES_TABLE, array( 'id' => $_REQUEST['schedule_id'] ) );
		//}
		wp_redirect( admin_url( 'edit.php?post_type=cruise&page=cruise_schedules') );
		exit;
	}
}

/*
 * schedule save action
 */
if ( ! function_exists( 'trav_cruise_schedule_save_action' ) ) {
	function trav_cruise_schedule_save_action() {

		if ( ! isset( $_POST['schedule_save'] ) || ! wp_verify_nonce( $_POST['schedule_save'], 'trav_cruise_schedule_manage' ) ) {
		   print __( 'Sorry, your nonce did not verify.', 'trav' );
		   exit;
		} else {

			global $wpdb;

			$default_schedule_data = array( 'cruise_id'  => '',
										'date_from'        => date( 'Y-m-d' ),
										'date_to'          => date( 'Y-m-d' ),
										'duration'	=> 1,
										'departure' =>'',
										'arrival' => '',
										'itinerary' => ''
									);

			$table_fields = array( 'date_from', 'date_to', 'duration', 'cruise_id', 'departure', 'arrival', 'itinerary' );
			$data = array();
			foreach ( $table_fields as $table_field ) {
				if ( ! empty( $_POST[ $table_field ] ) ) {
					$data[ $table_field ] = sanitize_text_field( $_POST[ $table_field ] );
				}
			}
			
			$to_date_obj = new DateTime( '@' . trav_strtotime( $_POST['date_from'] .' + ' . $_POST['duration'] . ' days' ) );
			$data['date_to'] = $to_date_obj->format( "Y-m-d" );

			$data['itinerary'] = '';

			if ( ! empty( $_POST['itinerary'] ) ) {
				$data['itinerary'] = serialize( $_POST['itinerary'] );
			}

			$data = array_replace( $default_schedule_data, $data );
			$data['cruise_id'] = trav_cruise_org_id( $data['cruise_id'] );
			if ( empty( $_POST['id'] ) ) {
				//insert
				$wpdb->insert( TRAV_CRUISE_SCHEDULES_TABLE, $data );
				$id = $wpdb->insert_id;
			} else {
				//update
				$wpdb->update( TRAV_CRUISE_SCHEDULES_TABLE, $data, array( 'id' => sanitize_text_field( $_POST['id'] ) ) );
				$id = sanitize_text_field( $_POST['id'] );
			}
			wp_redirect( admin_url( 'edit.php?post_type=cruise&page=cruise_schedules&action=edit&schedule_id=' . $id . '&updated=true') );
			exit;
		}
	}
}

/*
 * schedule admin enqueue script action
 */
if ( ! function_exists( 'trav_cruise_schedule_admin_enqueue_scripts' ) ) {
	function trav_cruise_schedule_admin_enqueue_scripts() {

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
		wp_enqueue_style( 'trav_admin_cruise_style' , TRAV_TEMPLATE_DIRECTORY_URI . '/inc/admin/css/style.css' ); 
		wp_enqueue_script( 'trav_admin_cruise_script' , TRAV_TEMPLATE_DIRECTORY_URI . '/inc/admin/cruise/js/script.js', array('jquery'), '1.0', true );
	}
}

add_action( 'admin_menu', 'trav_cruise_schedule_add_menu_items' );