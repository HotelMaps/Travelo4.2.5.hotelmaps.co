<?php
if ( ! defined( 'ABSPATH' ) ) exit; // Exit if accessed directly

if ( ! class_exists( 'WP_List_Table' ) ) {
	require_once( ABSPATH . 'wp-admin/includes/class-wp-list-table.php' );
}

/**
 * functions to manage currencies
 */
if ( ! class_exists( 'Trav_Currency_List_Table') ) :
class Trav_Currency_List_Table extends WP_List_Table {

	function __construct() {
		global $status, $page;
		parent::__construct( array(
			'singular'  => 'currency',   //singular name of the listed records
			'plural'    => 'currencies',    //plural name of the listed records
			'ajax'    => false        //does this table support ajax?
		) );
	}

	function column_default( $item, $column_name ) {
		switch( $column_name ) {
			case 'id':
			case 'currency_code':
			case 'currency_label':
			case 'currency_symbol':
			case 'exchange_rate':
				return $item[ $column_name ];
			default:
				return print_r( $item, true ); //Show the whole array for troubleshooting purposes
		}
	}

	function column_cb( $item ) {
		return sprintf( '<input type="checkbox" name="%1$s[]" value="%2$s" />', $this->_args['singular'], $item['id'] );
	}

	function column_currency_code( $item ) {
		$actions = array(
			'edit'    => sprintf( '<a href="admin.php?page=%1$s&action=%2$s&currency_id=%3$s">' . __('Edit', 'trav') . '</a>', 'currencies', 'edit', $item['id'] ),
			'delete'    => sprintf( '<a href="admin.php?page=%1$s&action=%2$s&currency_id=%3$s">' . __('Delete', 'trav') . '</a>', 'currencies', 'delete', $item['id'] )
		);
		return sprintf( '%1$s %2$s', $item['currency_code'], $this->row_actions( $actions ) );
	}

	function get_columns() {
		$columns = array(
			'cb'        => '<input type="checkbox" />', //Render a checkbox instead of text
			'id'        => __( 'ID', 'trav' ),
			'currency_code'=> __( 'Currency Code', 'trav' ),
			'currency_label'  => __( 'Currency Label', 'trav' ),
			'currency_symbol'  => __( 'Currency Symbol', 'trav' ),
			//'exchange_rate' => __( 'Exchange Rate', 'trav' )
		);
		return $columns;
	}

	function get_sortable_columns() {
		$sortable_columns = array(
			'id'      => array( 'id', false ),
			'currency_code'    => array( 'currency_code', false ),
			'currency_label'     => array( 'currency_label', false ),
			'currency_symbol'  => array( 'currency_symbol', false ),
			'exchange_rate'       => array( 'exchange_rate', false )
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
				wp_die( __('Sorry, your nonce did not verify', 'trav') );

		}
		if ( 'bulk_delete'===$this->current_action() ) {
			$selected_ids = $_GET[ $this->_args['singular'] ];
			$how_many = count($selected_ids);
			$placeholders = array_fill(0, $how_many, '%d');
			$format = implode(', ', $placeholders);
			$sql = sprintf( 'DELETE FROM %1$s WHERE id IN (%2$s)', TRAV_CURRENCIES_TABLE, "$format" );
			//$sql = sprintf( $sql, $selected_ids );
			$sql = $wpdb->prepare( $sql, $selected_ids );
			$wpdb->query( $sql );
			wp_redirect( admin_url( 'admin.php?page=currencies') );
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
		$order = ( ! empty( $_REQUEST['order'] ) ) ? sanitize_text_field( $_REQUEST['order'] ) : 'desc'; //If no order, default to asc
		$current_page = $this->get_pagenum();
		$post_table_name  = $wpdb->prefix . 'posts';

		$where = "1=1";

		$sql = sprintf( 'SELECT Trav_currencies.* FROM %1$s as Trav_currencies
			WHERE ' . $where . ' ORDER BY %3$s %4$s
			LIMIT %5$s, %6$s' , TRAV_CURRENCIES_TABLE, '', $orderby, $order, $per_page * ( $current_page - 1 ), $per_page );

		$data = $wpdb->get_results( $sql, ARRAY_A );

		$sql = "SELECT COUNT(*) FROM " . TRAV_CURRENCIES_TABLE . " where 1=1 ";
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
 * add currencies list page to menu
 */
if ( ! function_exists( 'trav_currency_admin_add_menu_items' ) ) {
	function trav_currency_admin_add_menu_items() {
		$page = add_menu_page( __('Currencies', 'trav'), __('Currencies', 'trav'), 'manage_options', 'currencies', 'trav_currency_admin_render_pages');
		add_action( 'admin_print_scripts-' . $page, 'trav_currency_admin_enqueue_scripts' );
	}
}

/*
 * currencies admin main actions
 */
if ( ! function_exists( 'trav_currency_admin_render_pages' ) ) {
	function trav_currency_admin_render_pages() {
		if ( ( ! empty( $_REQUEST['action'] ) ) && ( ( 'add' == $_REQUEST['action'] ) || ( 'edit' == $_REQUEST['action'] ) ) ) {
			trav_currency_admin_render_manage_page();
		} elseif ( ( ! empty( $_REQUEST['action'] ) ) && ( 'delete' == $_REQUEST['action'] ) ) {
			trav_currency_admin_delete_action();
		} else {
			trav_currency_admin_render_list_page();
		}
	}
}

/*
 * render currency list page
 */
if ( ! function_exists( 'trav_currency_admin_render_list_page' ) ) {
	function trav_currency_admin_render_list_page() {
		global $wpdb;
		$travVancancyTable = new Trav_currency_List_Table();
		$travVancancyTable->prepare_items();
		?>

		<div class="wrap">
			<h2><?php _e('Accommodation currencies', 'trav') ?> <a href="admin.php?page=currencies&amp;action=add" class="add-new-h2"><?php _e('Add New', 'trav') ?></a></h2>
			<form id="accomo-currencies-filter" method="get">
				<input type="hidden" name="page" value="<?php echo esc_attr( $_REQUEST['page'] ) ?>" />
				<?php $travVancancyTable->display() ?>
			</form>
		</div>
		<script>
		jQuery(document).ready(function($) {
			$('.row-actions .delete a').click(function(){
				var r = confirm("<?php _e('It will be deleted permanetly. Do you want to delete it?', 'trav') ?>");
				if(r == false) {
					return false;
				}
			});
		});
		</script>
		<?php
	}
}

/*
 * render currency detail page
 */
if ( ! function_exists( 'trav_currency_admin_render_manage_page' ) ) {
	function trav_currency_admin_render_manage_page() {
		global $wpdb;
		if ( ! empty( $_POST['save'] ) ) {
			trav_currency_admin_save_action();
			return;
		}

		$currency_data = array();

		if ( 'add' == $_REQUEST['action'] ) {
			$page_title = __("Add New currency", "trav");
		} elseif ( 'edit' == $_REQUEST['action'] ) {
			$page_title = __('Edit currency', 'trav') . '<a href="admin.php?page=currencies&amp;action=add" class="add-new-h2">' . __('Add New', 'trav') . '</a>';
			
			if ( empty( $_REQUEST['currency_id'] ) ) {
				echo "<h2>" . __("You attempted to edit an item that doesn't exist. Perhaps it was deleted?", "trav") . "</h2>";
				return;
			}
			$currency_id = sanitize_text_field( $_REQUEST['currency_id'] );
			$post_table_name  = $wpdb->prefix . 'posts';

			$sql = sprintf( 'SELECT Trav_currencies.* FROM %1$s as Trav_currencies
				WHERE Trav_currencies.id = %2$d' , TRAV_CURRENCIES_TABLE, $currency_id);

			$currency_data = $wpdb->get_row( $sql, ARRAY_A );
			if ( empty( $currency_data ) ) {
				echo "<h2>" . __("You attempted to edit an item that doesn't exist. Perhaps it was deleted?", "trav") . "</h2>";
				return;
			}
		}

		?>

		<div class="wrap">
			<h2><?php echo wp_kses_post( $page_title ); ?></h2>
			<?php if ( isset( $_REQUEST['updated'] ) ) echo '<div id="message" class="updated below-h2"><p>' . __('Currency saved', 'trav') . '</p></div>'?>
			<form method="post" onsubmit="return manage_currency_validateForm1();">
				<input type="hidden" name="id" value="<?php if ( ! empty( $currency_data['id'] ) ) echo esc_attr( $currency_data['id'] ); ?>">
				<table class="trav_admin_table trav_currency_manage_table">
					<tr>
						<th><?php _e('Currency Code', 'trav') ?></th>
						<td><input type="text" name="currency_code"  id="currency_code" value="<?php if ( ! empty( $currency_data['currency_code'] ) ) echo esc_attr( $currency_data['currency_code'] ); ?>"></td>
					</tr>
					<tr>
						<th><?php _e('Currency Label', 'trav') ?></th>
						<td><input type="text" name="currency_label" id="currency_label" value="<?php if ( ! empty( $currency_data['currency_label'] ) ) echo esc_attr( $currency_data['currency_label'] ); ?>"></td>
					</tr>
					<tr>
						<th><?php _e('Currency Symbol', 'trav') ?></th>
						<td><input type="text" name="currency_symbol" id="currency_symbol" value="<?php if ( ! empty( $currency_data['currency_symbol'] ) ) echo esc_attr( $currency_data['currency_symbol'] ); ?>"></td>
					</tr>
					<?php
						global $trav_options;
						if ( isset( $trav_options['fix_ex_rate'] ) && $trav_options['fix_ex_rate'] ) {
					?>
						<tr>
							<th><?php _e('Exchange Rate', 'trav') ?></th>
							<td><input type="text" name="exchange_rate" value="<?php if ( ! empty( $currency_data['exchange_rate'] ) ) echo esc_attr( $currency_data['exchange_rate'] ); ?>"></td>
						</tr>
					<?php
						} else {
					?>
						<input type="hidden" name="exchange_rate" value="<?php if ( ! empty( $currency_data['exchange_rate'] ) ) echo esc_attr( $currency_data['exchange_rate'] ); ?>">
					<?php
						}
					?>
				</table>
				<input type="submit" class="button-primary" name="save" value="<?php _e('Save currency', 'trav') ?>">
				<a href="admin.php?page=currencies" class="button-secondary"><?php _e('Cancel', 'trav') ?></a>
				<?php wp_nonce_field('trav_currency_manage','currency_save'); ?>
			</form>
		</div>
		<script>
			jQuery(document).ready(function($) {

			});
			function manage_currency_validateForm1() {
				$ = jQuery
				if( '' == $('#currency_code').val()){
					alert('<?php _e("Please enter currency code", "trav") ?>');
					return false;
				} else if( '' == $('#currency_label').val()){
					alert('<?php _e("Please enter currency label", "trav") ?>');
					return false;
				} else if( '' == $('#currency_symbol').val()){
					alert('<?php _e("Please enter currency symbol", "trav") ?>');
					return false;
				}
				return true;
			}
		</script>
		<?php
	}
}

/*
 * currency delete action
 */
if ( ! function_exists( 'trav_currency_admin_delete_action' ) ) {
	function trav_currency_admin_delete_action() {
		global $wpdb;
		/*if ( ! isset( $_GET['currency_delete'] ) || ! wp_verify_nonce( $_GET['currency_delete'], 'trav_currency_manage' ) ) {
			print 'Sorry, your nonce did not verify.';
			exit; 
		} else {*/
		$wpdb->delete( TRAV_CURRENCIES_TABLE, array( 'id' => $_REQUEST['currency_id'] ) );
		//}
		wp_redirect( admin_url( 'admin.php?page=currencies') );
		exit;
	}
}

/*
 * currency save action
 */
if ( ! function_exists( 'trav_currency_admin_save_action' ) ) {
	function trav_currency_admin_save_action() {

		if ( ! isset( $_POST['currency_save'] ) || ! wp_verify_nonce( $_POST['currency_save'], 'trav_currency_manage' ) ) {
		   print __('Sorry, your nonce did not verify.', 'trav');
		   exit;
		} else {

			global $wpdb;

			$table_fields = array( 'currency_code', 'currency_label', 'currency_symbol', 'exchange_rate' );
			$data = array();
			foreach ( $table_fields as $table_field ) {
				if ( ! empty( $_POST[ $table_field ] ) ) {
					$data[ $table_field ] = sanitize_text_field( $_POST[ $table_field ] );
				}
			}

			if ( empty( $_POST['id'] ) ) {
				//insert
				$wpdb->insert( TRAV_CURRENCIES_TABLE, $data );
				$id = $wpdb->insert_id;
			} else {
				//update
				$wpdb->update( TRAV_CURRENCIES_TABLE, $data, array( 'id' => sanitize_text_field( $_POST['id'] ) ) );
				$id = sanitize_text_field( $_POST['id'] );
			}
			wp_redirect( admin_url( 'admin.php?page=currencies&action=edit&currency_id=' . $id . '&updated=true') );
			exit;
		}
	}
}

/*
 * currency admin enqueue script action
 */
if ( ! function_exists( 'trav_currency_admin_enqueue_scripts' ) ) {
	function trav_currency_admin_enqueue_scripts() {
		wp_enqueue_style( 'trav_admin' , get_template_directory_uri() . '/inc/admin/css/style.css' ); 
	}
}

add_action( 'admin_menu', 'trav_currency_admin_add_menu_items' );