<?php
if ( ! defined( 'ABSPATH' ) ) exit; // Exit if accessed directly

if ( ! class_exists( 'WP_List_Table' ) ) {
	require_once( ABSPATH . 'wp-admin/includes/class-wp-list-table.php' );
}

/**
 * functions to manage reviews
 */
if ( ! class_exists( 'Trav_Review_List_Table') ) :
class Trav_Review_List_Table extends WP_List_Table {

	function __construct() {
		global $status, $page;
		parent::__construct( array(
			'singular'  => 'review',     //singular name of the listed records
			'plural'    => 'reviews',    //plural name of the listed records
			'ajax'      => false        //does this table support ajax?
		) );
	}

	function column_default( $item, $column_name ) {
		switch( $column_name ) {
			case 'date':
			//case 'post_name':
				return $item[ $column_name ];
			default:
				return print_r( $item, true ); //Show the whole array for troubleshooting purposes
		}
	}

	function column_author_info( $item ) {
		//Build row actions
		$default = '';
		$photo = trav_get_avatar( array( 'id' => $item['user_id'], 'email' => $item['reviewer_email'], 'size' => 32 ) );
		$str = '';
		$str = $photo;
		$str .= '<span class="author-detail">' . $item['reviewer_name'] . '<br />';
		$str .= '<a href="mailto:' . sanitize_email( $item['reviewer_email'] ) . '">' . $item['reviewer_email'] . '</a><br />';
		$str .= '<a href="admin.php?page=reviews&amp;reviewer_ip=' . esc_url( $item['reviewer_ip'] ) . '">' . $item['reviewer_ip'] . '</a></span>';
		return $str;
	}

	function column_review( $item ) {
		$str = '';
		$str .= '<a href="admin.php?page=reviews&amp;action=edit&amp;review_id=' . $item['id'] .'"><div class="five-stars-container"><span class="five-stars" style="width:' . $item['review_rating']/5*100 . '%" title="' . $item['review_rating'] . '"></span></div></a>';
		$str .= '<div>' . esc_html( $item['review_title'] ) . '</div>';
		$str .= '<div>' . esc_html( substr( stripslashes( $item['review_text'] ), 0, 150 ) ) . '...</div>';

		$actions = array();
		if ( $item['status'] == 0 ) {
			$actions = array(
				'review_approve'  => sprintf( '<a href="admin.php?page=%1$s&action=%2$s&review_id=%3$s">%4$s</a>', 'reviews', 'approve', $item['id'], __( 'Approve', 'trav' ) ),
				'edit'  => sprintf( '<a href="admin.php?page=%1$s&action=%2$s&review_id=%3$s">%4$s</a>', 'reviews', 'edit', $item['id'], __( 'Edit', 'trav' ) ),
				'trash'    => sprintf( '<a href="admin.php?page=%1$s&action=%2$s&review_id=%3$s">%4$s</a>', 'reviews', 'trash', $item['id'], __( 'Trash', 'trav' ) )
			);
		} else if ( $item['status'] == 1 ) {
			$actions = array(
				'review_unapprove'  => sprintf( '<a href="admin.php?page=%1$s&action=%2$s&review_id=%3$s">%4$s</a>', 'reviews', 'unapprove', $item['id'], __( 'Unapprove', 'trav' ) ),
				'edit'  => sprintf( '<a href="admin.php?page=%1$s&action=%2$s&review_id=%3$s">%4$s</a>', 'reviews', 'edit', $item['id'], __( 'Edit', 'trav' ) ),
				'trash'    => sprintf( '<a href="admin.php?page=%1$s&action=%2$s&review_id=%3$s">%4$s</a>', 'reviews', 'trash', $item['id'], __( 'Trash', 'trav' ) )
			);
		} else if ( $item['status'] == 2 ) {
			$actions = array(
				'untrash'  => sprintf( '<a href="admin.php?page=%1$s&action=%2$s&review_id=%3$s">%4$s</a>', 'reviews', 'untrash', $item['id'], __( 'Untrash', 'trav' ) ),
				'delete'  => sprintf( '<a href="admin.php?page=%1$s&action=%2$s&review_id=%3$s">%4$s</a>', 'reviews', 'delete', $item['id'], __( 'Delete Permanently', 'trav' ) )
			);
		}
		$str .= $this->row_actions( $actions );
		return $str;
	}

	function column_post_name( $item ) {
		return '<a href="' . get_edit_post_link( $item['post_id'] ) . '">' . $item['post_name'] . '</a>';
	}

	/*function column_action( $item ) {
		$action = array(
			'edit'      => sprintf( '<a href="admin.php?post_type=%1s&page=%2$s&action=%3$s&review_id=%4$s">Edit</a>', $_REQUEST['post_type'], 'reviews', 'edit', $item['id'] ),
			'delete'    => sprintf( '<a href="admin.php?post_type=%1s&page=%2$s&action=%3$s&review_id=%4$s">Delete</a>', $_REQUEST['post_type'], 'reviews', 'delete', $item['id'] )

		);
	}*/

	function column_cb( $item ) {
		return sprintf( '<input type="checkbox" name="%1$s[]" value="%2$s" />', $this->_args['singular'], $item['id'] );
	}

	function get_columns() {
		$columns = array(
			'cb'        => '<input type="checkbox" />', //Render a checkbox instead of text
			'author_info'     => __( 'Author', 'trav' ),
			'review'=> __( 'Review', 'trav' ),
			'post_name'=> __( 'Post Name', 'trav' ),
			'date'=> __( 'Review Date (UTC)', 'trav' )
		);
		return $columns;
	}

	function get_sortable_columns() {
		$sortable_columns = array(
			'date'            => array( 'date', false ),
			'post_name' => array( 'post_name', false ),
		);
		return $sortable_columns;
	}

	function get_bulk_actions() {
		$actions = array();
		$status = isset( $_GET['status'] )?$_GET['status']:0;
		if ( $status == -1 ) {
			$actions = array(
				'bulk_movetrash'    => __( 'Move to Trash', 'trav' )
			);
		} elseif ( $status == 1 ) {
			$actions = array(
				'bulk_unapprove'    => __( 'Unapprove', 'trav' ),
				'bulk_movetrash'    => __( 'Move to Trash', 'trav' )
			);
		} elseif ( $status == 2 ) {
			$actions = array(
				'bulk_untrash'    => __( 'Restore', 'trav' ),
				'bulk_delete'    => __( 'Delete Permanently', 'trav' )
			);
		} else {
			$actions = array(
				'bulk_approve'    => __( 'Approve', 'trav' ),
				'bulk_movetrash'    => __( 'Move to Trash', 'trav' )
			);
		}
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

		$sql = '';
		$status = 0;
		switch( $this->current_action() ) {
				//wp_redirect( admin_url( 'admin.php?page=reviews&bulk_delete=true') );
			case 'bulk_movetrash': //status will be 2
				$status++;
			case 'bulk_approve': //status will be 1
				$status++;
			case 'bulk_unapprove':
			case 'bulk_untrash': //status will be 0
			case 'bulk_delete':
				$selected_ids = $_GET[ $this->_args['singular'] ];
				$how_many = count($selected_ids);
				$placeholders = array_fill(0, $how_many, '%d');
				$format = implode(', ', $placeholders);
				if ( $this->current_action() == "bulk_delete" ) {
					$sql = sprintf( 'DELETE FROM %1$s WHERE id IN (%2$s)', TRAV_REVIEWS_TABLE, "$format" );
				} else {
					$sql = sprintf( 'UPDATE %1$s SET status="%2$s" WHERE id IN (%3$s)', TRAV_REVIEWS_TABLE, esc_sql( $status ), "$format" );
				}
				//$sql = sprintf( $sql, $selected_ids );
				$sql = $wpdb->prepare( $sql, $selected_ids );
				$wpdb->query( $sql );

				/* calculate post rating */
				$sql = sprintf( 'SELECT post_id FROM %1$s WHERE id IN (%2$s)', TRAV_REVIEWS_TABLE, "$format" );
				//$sql = sprintf( $sql, $selected_ids );
				$sql = $wpdb->prepare( $sql, $selected_ids );
				$post_ids = $wpdb->get_col( $sql );

				foreach ( $post_ids as $post_id ) {
					trav_post_review_calculate_rating( $post_id );
				}
				wp_redirect( $_SERVER[HTTP_REFERER] );
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
		if ( ! empty( $_REQUEST['post_id'] ) ) $where .= " AND Trav_Reviews.post_id = '" . esc_sql( trav_post_org_id( $_REQUEST['post_id'] ) ) . "'";
		//if ( ! empty( $_REQUEST['post_type'] ) ) $where .= " AND Trav_Posts.post_type = '" . esc_sql( $_REQUEST['post_type'] ) . "'";
		if ( ! empty( $_REQUEST['reviewer_ip'] ) ) $where .= " AND Trav_Reviews.reviewer_ip = '" . esc_sql( $_REQUEST['reviewer_ip'] ) . "'";
		$status = ( isset( $_REQUEST['status'] ) ) ? esc_sql( $_REQUEST['status'] ) : 0;
		if ( $status != -1 ) $where .= " AND Trav_Reviews.status = '" . esc_sql( $status ) . "'";

		$sql = sprintf( 'SELECT Trav_Reviews.* , Trav_Posts.post_title as post_name FROM %1$s as Trav_Reviews
						INNER JOIN %2$s as Trav_Posts ON Trav_Reviews.post_id=Trav_Posts.ID
						WHERE ' . $where . ' ORDER BY %4$s %5$s
						LIMIT %6$s, %7$s' , TRAV_REVIEWS_TABLE, $post_table_name, '', $orderby, $order, ( $per_page * ( $current_page - 1 ) ), $per_page );

		$data = $wpdb->get_results( $sql, ARRAY_A );

		$sql = sprintf( 'SELECT Trav_Reviews.* , Trav_Posts.post_title as post_name FROM %1$s as Trav_Reviews
						INNER JOIN %2$s as Trav_Posts ON Trav_Reviews.post_id=Trav_Posts.ID
						WHERE ' . $where, TRAV_REVIEWS_TABLE, $post_table_name );

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
 * add review list page to menu
 */
if ( ! function_exists( 'trav_review_add_menu_items' ) ) {
	function trav_review_add_menu_items() {
//		$page = add_submenu_page( 'admin.php?post_type=accommodation', 'Accommodation Reviews', 'Reviews', 'manage_options', 'reviews', 'trav_review_render_pages' );
//		add_action( 'admin_print_scripts-' . $page, 'trav_review_admin_enqueue_scripts' );

		$page = add_menu_page( 'Reviews', 'Reviews', 'manage_options', 'reviews', 'trav_review_render_pages');
		add_action( 'admin_print_scripts-' . $page, 'trav_review_admin_enqueue_scripts' );
	}
}

/*
 * review admin main actions
 */
if ( ! function_exists( 'trav_review_render_pages' ) ) {
	function trav_review_render_pages() {

		$action = isset( $_REQUEST['action'] ) ? sanitize_text_field( $_REQUEST['action'] ) : '';

		if ( ( 'add' == $action ) || ( 'edit' == $action ) ) {
			trav_review_render_manage_page();
		} elseif ( 'delete' == $action ) {
			trav_review_delete_action();
		} elseif ( 'trash' == $action ) {
			trav_review_change_status_action(2);
		} elseif ( 'untrash' == $action ) {
			trav_review_change_status_action(0);
		} elseif ( 'approve' == $action ) {
			trav_review_change_status_action(1);
		} elseif ( 'unapprove' == $action ) {
			trav_review_change_status_action(0);
		} else {
			trav_review_render_list_page();
		}
	}
}

/*
 * render review list page
 */
if ( ! function_exists( 'trav_review_render_list_page' ) ) {
	function trav_review_render_list_page() {

		global $wpdb;
		$travVancancyTable = new Trav_Review_List_Table();
		$travVancancyTable->prepare_items();
		$page_url = 'admin.php?page=reviews';
		?>

		<div class="wrap">
			
			<h2><?php _e( 'Post Reviews', 'trav' )?><a href="<?php echo esc_url( $page_url ); ?>&amp;action=add" class="add-new-h2"><?php _e( 'Add New', 'trav' ); ?></a></h2>
			<?php if ( isset( $_REQUEST['bulk_delete'] ) ) echo '<div id="message" class="updated below-h2"><p>' . __( 'Reviews deleted', 'trav' ) . '</p></div>'; ?>
			<ul class="subsubsub">
				<?php
					$status_filters = array(
										'-1' => __( 'All', 'trav' ),
										'0' => __( 'Pending', 'trav' ),
										'1' => __( 'Approved', 'trav' ),
										'2' => __( 'Trash', 'trav' )
						);
					$status = ( isset( $_REQUEST['status'] ) ) ? sanitize_text_field( $_REQUEST['status'] ) : 0;
					foreach ( $status_filters as $value => $label ) {

						$where = '1=1';
						if ( $value != -1 ) $where .= " AND Trav_Reviews.status = '" . esc_sql( $value ) . "'";
						$sql = sprintf( 'SELECT COUNT(*) FROM %1$s as Trav_Reviews WHERE %2$s', TRAV_REVIEWS_TABLE, $where );
						$count = $wpdb->get_var( $sql );
						$class = '';
						if ( $status == $value ) $class='current';
						echo '<li><a href="' . esc_url( $page_url . '&amp;status=' . $value ) . '" class="' . esc_attr( $class ) . '">' . esc_html( $label ) . '</a><span class="count">(<span class="pending-count">' . esc_html( $count ) . '</span>)</span></a> |</li>';
					}
				?>
			</ul>
			<div style="float:right;">
				<select id="post_filter">
					<option></option>
					<?php
					if ( ! empty( $_REQUEST['post_type'] ) ) {
						$post_type = sanitize_text_field( $_REQUEST['post_type'] );
					} else {
						$post_type = array( 'accommodation', 'cruise' );
					}
					$args = array(
							'post_type'         => $post_type,
							'posts_per_page'    => -1,
							'orderby'           => 'title',
							'order'             => 'ASC',
							'post_status' => 'publish',
					);
					$post_query = new WP_Query( $args );

					if ( $post_query->have_posts() ) {
						while ( $post_query->have_posts() ) {
							$post_query->the_post();
							$selected = '';
							$id = $post_query->post->ID;
							if ( ! empty( $_REQUEST['post_id'] ) && ( $_REQUEST['post_id'] == $id ) ) $selected = ' selected ';
							echo '<option ' . esc_attr( $selected ) . 'value="' . esc_attr( $id ) .'">' . wp_kses_post( get_the_title( $id ) ) . '</option>';
						}
					} else {
						// no posts found
					}
					/* Restore original Post Data */
					wp_reset_postdata();
					?>
				</select>
				<select id="post_type_filter">
					<option></option>
					<option value="accommodation" <?php echo ( ! empty( $_REQUEST['post_type'] ) && ( $_REQUEST['post_type'] == "accommodation" ) )?' selected':''?> ><?php _e( 'Accommodation', 'trav' ); ?></option>
					<option value="cruise" <?php echo ( ! empty( $_REQUEST['post_type'] ) && ( $_REQUEST['post_type'] == "cruise" ) )?' selected':''?>><?php _e( 'Cruise', 'trav' ); ?></option>
				</select>
			</div>
			<form id="post-reviews-filter" method="get">
				<input type="hidden" name="post_type" value="<?php echo (! empty( $_REQUEST['post_type'] ) ) ? esc_attr( $_REQUEST['post_type'] ) : ''; ?>" />
				<input type="hidden" name="page" value="<?php echo esc_attr( $_REQUEST['page'] ) ?>" />
				<?php $travVancancyTable->display() ?>
			</form>
			
		</div>
		<script>
		jQuery(document).ready(function($) {
			$('#post_filter').select2({
				placeholder: "Filter by Post",
				allowClear: true,
				width: "240px"
			});
			$('#post_filter').change(function() {
				var postId = $('#post_filter').val();
				var postType = $('#post_type_filter').val();
				var loc_url = '<?php echo esc_js( $page_url ); ?>';
				var status = '<?php echo esc_js( $status );?>'
				if (postId) loc_url += '&post_type=' + postType + '&post_id=' + postId + '&status=' + status;
				document.location = loc_url;
			});
			$('#post_type_filter').select2({
				placeholder: "Filter by Post Type",
				allowClear: true,
				width: "240px"
			});
			$('#post_type_filter').change(function() {
				var postId = $('#post_filter').val();
				var postType = $('#post_type_filter').val();
				var loc_url = '<?php echo esc_js( $page_url ); ?>';
				var status = '<?php echo esc_js( $status );?>'
				if (postType) loc_url += '&post_type=' + postType + '&post_id=' + postId + '&status=' + status;
				document.location = loc_url;
			});
			$('.row-actions .delete a').click(function(){
				var r = confirm("It will be deleted permanently. Do you want to delete it?");
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
 * render review detail page
 */
if ( ! function_exists( 'trav_review_render_manage_page' ) ) {
	function trav_review_render_manage_page() {

		global $wpdb;

		if ( ! empty( $_POST['save'] ) ) {
			trav_review_save_action();
			return;
		}

		$default_review_data = array(   'post_id'  => '',
										'review_rating' => 0,
										'review_rating_detail' => '',
										'review_title' => '',
										'review_text' => '',
										'reviewer_ip' => '127.0.0.1',
										'reviewer_email' => '',
										'reviewer_name' => '',
										'status'        => 0,
										'date'        => date( 'Y-m-d H:i:s' ),
										'user_id' => ''
									);

		$review_data = array();

		if ( 'add' == $_REQUEST['action'] ) {
			$page_title = __( 'Add New Post Review', 'trav' );
		} elseif ( 'edit' == $_REQUEST['action'] ) {
			$page_title = __( 'Edit Post Review', 'trav' ) . '<a href="admin.php?page=reviews&amp;action=add" class="add-new-h2">' . __( 'Add New', 'trav' ) . '</a>';
			
			if ( empty( $_REQUEST['review_id'] ) ) {
				echo "<h2>" . __( 'You attempted to edit an item that doesn\'t exist. Perhaps it was deleted?', 'trav' ) . "</h2>";
				return;
			}
			$review_id = sanitize_text_field( $_REQUEST['review_id'] );
			$post_table_name  = $wpdb->prefix . 'posts';

			$sql = sprintf( 'SELECT Trav_Reviews.* , Trav_Posts.post_title as post_name FROM %1$s as Trav_Reviews
							INNER JOIN %2$s as Trav_Posts ON Trav_Reviews.post_id=Trav_Posts.ID
							WHERE Trav_Reviews.id = %3$d' , TRAV_REVIEWS_TABLE, $post_table_name, $review_id );

			$review_data = $wpdb->get_row( $sql, ARRAY_A );
			if ( empty( $review_data ) ) {
				echo "<h2>" . __( 'You attempted to edit an item that doesn\'t exist. Perhaps it was deleted?', 'trav' ) . "</h2>";
				return;
			}
		}

		$review_data = array_replace( $default_review_data, $review_data );
		?>

		<div class="wrap">
			<h2><?php echo wp_kses_post( $page_title ); ?></h2>
			<?php if ( isset( $_REQUEST['updated'] ) ) echo '<div id="message" class="updated below-h2"><p>' . __( 'Review saved', 'trav' ) . '</p></div>'; ?>
			<form method="post" onsubmit="return manage_review_validateForm1();">
				<input type="hidden" name="id" value="<?php if ( ! empty( $review_data['id'] ) ) echo esc_attr( $review_data['id'] ); ?>">
				<table class="trav_admin_table trav_review_manage_table one-half">
					<tr>
						<th><h3><?php _e( 'Review Info', 'trav' ) ?></h3></th>
					</tr>
					<tr>
						<th><?php _e( 'Select Post', 'trav' ); ?></th>
						<td>
							<select name="post_id" id="post_id">
								<option></option>
								<?php
									$args = array(
											'post_type'         => array( 'accommodation', 'cruise' ),
											'posts_per_page'    => -1,
											'orderby'           => 'title',
											'order'             => 'ASC',
											'post_status' => 'publish',
									);
									$post_query = new WP_Query( $args );

									if ( $post_query->have_posts() ) {
										while ( $post_query->have_posts() ) {
											$post_query->the_post();
											$selected = '';
											$id = $post_query->post->ID;
											if ( ( ! empty( $review_data['post_id'] ) ) && ( $review_data['post_id'] == $id ) ) $selected = ' selected ';
											echo '<option ' . esc_attr( $selected ) . 'value="' . esc_attr( $id ) .'">' . wp_kses_post( get_the_title( $id ) ) . '</option>';
										}
									}
									wp_reset_postdata();
								?>
							</select>
						</td>
					</tr>
					<tr>
						<th><?php _e( 'Reviewer Name', 'trav' ); ?></th>
						<td><input type="text" name="reviewer_name" value="<?php echo esc_attr( $review_data['reviewer_name'] ); ?>"></td>
					</tr>
					<tr>
						<th><?php _e( 'Reviewer Email', 'trav' ); ?></th>
						<td><input type="text" name="reviewer_email" value="<?php echo esc_attr( $review_data['reviewer_email'] ); ?>"></td>
					</tr>
					<tr>
						<th><?php _e( 'Reviewer IP', 'trav' ); ?></th>
						<td><input type="text" name="reviewer_ip" value="<?php echo esc_attr( $review_data['reviewer_ip'] ); ?>"></td>
					</tr>
					<tr>
						<th><?php _e( 'Title', 'trav' ); ?></th>
						<td><input type="text" name="review_title" value="<?php echo esc_attr( $review_data['review_title'] ); ?>"></td>
					</tr>
					<tr>
						<th><?php _e( 'Content', 'trav' ); ?></th>
						<td><textarea name="review_text"><?php echo esc_textarea( stripslashes( $review_data['review_text'] ) ); ?></textarea></td>
					</tr>
					<tr>
						<th><?php _e( 'Trip Type', 'trav' ); ?></th>
						<td><select name="trip_type">
							<?php
								$trip_types = array(
										'0' => __( 'Business', 'trav' ),
										'1' => __( 'Couples', 'trav' ),
										'2' => __( 'Family', 'trav' ),
										'3' => __( 'Friends', 'trav' ),
										'4' => __( 'Solo', 'trav' )
									);

								foreach ( $trip_types as $val => $label ) {
									$selected = '';
									if ( ( ! empty( $review_data['trip_type'] ) ) && $review_data['trip_type'] == $val ) $selected = 'selected';
									echo '<option value="' . esc_attr( $val ) . '" ' . esc_attr( $selected ) . '>' . esc_html( $label ) . '</option>';
								}
							?>
						</select></td>
					</tr>
					<tr>
						<th><?php _e( 'Review Status', 'trav' ); ?></th>
						<td><select name="status">
							<?php
								$statuses = array(
										'0' => __( 'Pending', 'trav' ),
										'1' => __( 'Approved', 'trav' ),
										'2' => __( 'Trashed', 'trav' ),
									);

								foreach ( $statuses as $val => $label ) {
									$selected = '';
									if ( $review_data['status'] == $val ) $selected = 'selected';
									echo '<option value="' . esc_attr( $val ) . '" ' . esc_attr( $selected ) . '>' . esc_html( $label ) . '</option>';
								}
							?>
						</select></td>
					</tr>
					<tr>
						<th><?php _e( 'Review Date', 'trav' ); ?><br/>(example : 2015-02-20 03:24:16)</th>
						<td><input type="text" name="date" value="<?php echo esc_attr( $review_data['date'] ); ?>"></td>
					</tr>
					<tr>
						<th><?php _e( 'User ID', 'trav' ); ?></th>
						<td><input type="text" name="user_id" value="<?php echo esc_attr( $review_data['user_id'] ); ?>"></td>
					</tr>
				</table>

				<table class="trav_admin_table trav_review_manage_table one-half">
					<tr><th><h3><?php _e( 'Review Rating Details', 'trav' ) ?></h3></th></tr>
					<?php
						if ( get_post_type( $review_data['post_id'] ) == 'accommodation' ) {
							$review_fields = array(
									__( 'Cleanliness', 'trav' ),
									__( 'Comfort', 'trav' ),
									__( 'Location', 'trav' ),
									__( 'Facilities', 'trav' ),
									__( 'Staff', 'trav' ),
									__( 'Value for money', 'trav' ),
								);
						} else {
							$review_fields = array(
									__( 'Ship Quality', 'trav' ),
									__( 'Dining/Food', 'trav' ),
									__( 'Rooms Quality', 'trav' ),
									__( 'Play areas', 'trav' ),
									__( 'Cleanliness', 'trav' ),
									__( 'Fitness facility', 'trav' ),
								);
						}
						$review_rating_detail = unserialize( $review_data['review_rating_detail'] );
						$i = 0;

						foreach ( $review_fields as $review_field ) {
					?>

						<tr>
							<th><?php echo esc_html( $review_field ) ?></th>
							<td><input type="number" name="review_rating_detail[<?php echo esc_attr( $i ) ?>]" min="1" max="5" value="<?php echo esc_attr( isset( $review_rating_detail[$i] ) ? $review_rating_detail[$i] : 5 ) ?>"></td>
						</tr>

					<?php
							$i++;
						}

					?>
				</table>
				<input type="submit" class="button-primary" name="save" value="<?php _e( 'Save Review', 'trav' ); ?>">
				<a href="admin.php?page=reviews" class="button-secondary"><?php _e( 'Cancel', 'trav' ); ?></a>
				<?php wp_nonce_field('trav_review_manage','review_save'); ?>
			</form>
			<script>
				jQuery(document).ready(function($) {
					$('#post_id').select2({
						placeholder: "Select a Post",
						width: "250px"
					});
				});
			</script>
		</div>
		<?php
	}
}

/*
 * review delete action
 */
if ( ! function_exists( 'trav_review_delete_action' ) ) {
	function trav_review_delete_action() {

		global $wpdb;
		/*if ( ! isset( $_GET['review_delete'] ) || ! wp_verify_nonce( $_GET['review_delete'], 'trav_review_manage' ) ) {
			print 'Sorry, your nonce did not verify.';
			exit; 
		} else {*/
		$wpdb->delete( TRAV_REVIEWS_TABLE, array( 'id' => $_REQUEST['review_id'] ) );
		//}
		wp_redirect( admin_url( 'admin.php?page=reviews') );
		exit;
	}
}

/*
 * review change status action
 */
if ( ! function_exists( 'trav_review_change_status_action' ) ) {
	function trav_review_change_status_action( $status = 0 ) {
		global $wpdb;
		$wpdb->update( TRAV_REVIEWS_TABLE, array( 'status' => $status ), array( 'id' => $_REQUEST['review_id'] ) );

		$sql = 'SELECT Trav_Reviews.post_id FROM ' . TRAV_REVIEWS_TABLE . ' AS Trav_Reviews WHERE id="' . esc_sql( $_REQUEST['review_id'] ) . '"';
		$post_id = $wpdb->get_var( $sql );
		trav_post_review_calculate_rating( $post_id );
		//wp_redirect( admin_url( 'admin.php?page=reviews') );
		wp_redirect( $_SERVER[HTTP_REFERER] );
	}
}

/*
 * review calculate post rating action and update it
 */
if ( ! function_exists( 'trav_post_review_calculate_rating' ) ) {
	function trav_post_review_calculate_rating( $post_id ) {
		//recalculate post rating
		global $wpdb;
		$sql = 'SELECT review_rating, review_rating_detail FROM ' . TRAV_REVIEWS_TABLE . ' WHERE status="1" AND post_id="' . esc_sql( $post_id ) . '"';
		$review_datas = $wpdb->get_results( $sql, ARRAY_A );

		$post_rating_detail = array( 0, 0, 0, 0, 0, 0);
		foreach ( $review_datas as $review_data ) {
			$review_rating_detail = unserialize( $review_data['review_rating_detail'] );
			for( $i = 0; $i <= 5; $i++ ) {
				$post_rating_detail[$i] += (float)$review_rating_detail[$i];
			}
		}

		$count_review = count( $review_datas ); 
		$post_rating = 0;
		for( $i = 0; $i <= 5; $i++ ) {
			$post_rating_detail[$i] = round( $post_rating_detail[$i] / $count_review, 1 );
			$post_rating += $post_rating_detail[$i];
		}
		$post_rating /= 6;
		update_post_meta( $post_id, 'review', $post_rating );
		update_post_meta( $post_id, 'review_detail', $post_rating_detail );
	}
}

/*
 * reveiw save action
 */
if ( ! function_exists( 'trav_review_save_action' ) ) {
	function trav_review_save_action() {

		if ( ! isset( $_POST['review_save'] ) || ! wp_verify_nonce( $_POST['review_save'], 'trav_review_manage' ) ) {
			print 'Sorry, your nonce did not verify.';
			exit;
		} else {

			global $wpdb;

			$default_review_data = array(
									'post_id'  => '',
									'review_rating' => 0,
									'review_rating_detail' => '',
									'review_title'  => '',
									'review_text'   => '',
									'reviewer_ip'   => '127.0.0.1',
									'reviewer_email' => '',
									'reviewer_name' => '',
									'trip_type'     => 0,
									'status'        => 0,
									'date'        => date( 'Y-m-d H:i:s' ),
									'user_id' => ''
								);

			$table_fields = array( 'reviewer_name', 'reviewer_email', 'reviewer_ip', 'review_title', 'review_text', 'post_id', 'trip_type', 'status', 'date', 'user_id' );
			//review_rating, review_rating_detail, date
			$data = array();
			foreach ( $table_fields as $table_field ) {
				if ( ! empty( $_POST[ $table_field ] ) ) {
					$data[ $table_field ] = sanitize_text_field( $_POST[ $table_field ] );
				}
			}

			$data['review_rating_detail'] = serialize( $_POST['review_rating_detail'] );
			$data['review_rating'] = round( array_sum( $_POST['review_rating_detail'] ) / count( $_POST['review_rating_detail'] ), 1 );
			$data = array_replace( $default_review_data, $data );

			//$data['post_id'] = trav_post_org_id( $data['post_id'] );
			if ( empty( $_POST['id'] ) ) {
				//insert
				$wpdb->insert( TRAV_REVIEWS_TABLE, $data );
				$id = $wpdb->insert_id;
			} else {
				//update
				$wpdb->update( TRAV_REVIEWS_TABLE, $data, array( 'id' => sanitize_text_field( $_POST['id'] ) ) );
				$id = sanitize_text_field( $_POST['id'] );
			}

			if ( $data['status'] == 1 ) {
				trav_post_review_calculate_rating( $data['post_id'] );
			}
			wp_redirect( admin_url( 'admin.php?page=reviews&action=edit&review_id=' . $id . '&updated=true') );
			exit;
		}
	}
}

/*
 * reveiw admin enqueue script action
 */
if ( ! function_exists( 'trav_review_admin_enqueue_scripts' ) ) {
	function trav_review_admin_enqueue_scripts() {

		// support select2
		wp_enqueue_style( 'rwmb_select2', RWMB_URL . 'css/select2/select2.css', array(), '3.2' );
		wp_enqueue_script( 'rwmb_select2', RWMB_URL . 'js/select2/select2.min.js', array(), '3.2', true );

		// custom style and js
		wp_enqueue_style( 'trav_admin_acc_style' , TRAV_TEMPLATE_DIRECTORY_URI . '/inc/admin/css/style.css' ); 
	}
}

add_action( 'admin_menu', 'trav_review_add_menu_items' );