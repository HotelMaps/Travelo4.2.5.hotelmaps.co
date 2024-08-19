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
if ( ! class_exists( 'Trav_Tour_Booking_List_Table') ) :
class Trav_Tour_Booking_List_Table extends WP_List_Table {

    function __construct() {
        parent::__construct( array(
            'singular'  => 'tour_booking',     //singular name of the listed records
            'plural'    => 'tour_bookings',    //plural name of the listed records
            'ajax'      => false        //does this table support ajax?
        ) );
    }

    function column_default( $item, $column_name ) {
        switch( $column_name ) {
            case 'id':
            case 'adults':
            case 'kids':
            case 'created':
            case 'total_price':
            case 'tour_date':
                return $item[ $column_name ];
            default:
                return print_r( $item, true ); //Show the whole array for troubleshooting purposes
        }
    }

    function column_customer_name( $item ) {
        //Build row actions
        $link_pattern = '<a href="edit.php?post_type=%1s&page=%2$s&action=%3$s&booking_id=%4$s">%5$s</a>';
        $actions = array(
            'edit'      => sprintf( $link_pattern, sanitize_text_field( $_REQUEST['post_type'] ), 'tour_bookings', 'edit', $item['id'], 'Edit' ),
            'delete'    => sprintf( $link_pattern, sanitize_text_field( $_REQUEST['post_type'] ), 'tour_bookings', 'delete', $item['id'] . '&_wpnonce=' . wp_create_nonce( 'booking_delete' ) , 'Delete' )
        );
        $content = sprintf( $link_pattern, sanitize_text_field( $_REQUEST['post_type'] ), 'tour_bookings', 'edit', $item['id'], esc_html( $item['first_name'] . ' ' . $item['last_name'] ) );

        //Return the title contents
        return sprintf( '%1$s %2$s', $content , $this->row_actions( $actions ) );
    }

    function column_tour_name( $item ) {
        return '<a href="' . get_edit_post_link( $item['tour_id'] ) . '">' . $item['tour_name'] . '</a>';
    }

    function column_schedule_type( $item ) {
        return trav_tour_get_schedule_type_title( $item['tour_id'], $item['st_id'] );
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
            'tour_name'     => __( 'Tour Name', 'trav' ),
            'schedule_type' => __( 'Schedule Type', 'trav' ),
            'tour_date'     => __( 'Tour Date', 'trav' ),
            'adults'        => __( 'Adults', 'trav' ),
            'kids'          => __( 'Kids', 'trav' ),
            'total_price'   => __( 'Price', 'trav' ),
            'created'       => __( 'Created Date', 'trav' ),
            'status'        => __( 'Status', 'trav' ),
        );

        return $columns;
    }

    function get_sortable_columns() {
        $sortable_columns = array(
            'id'            => array( 'id', false ),
            'tour_date'     => array( 'tour_date', false ),
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

            $how_many = count($selected_ids);
            $placeholders = array_fill(0, $how_many, '%d');
            $format = implode(', ', $placeholders);
            $current_user_id = get_current_user_id();
            $post_table_name  = esc_sql( $wpdb->prefix . 'posts' );
            $sql = '';

            if ( current_user_can( 'manage_options' ) ) {
                $sql = sprintf( 'DELETE FROM %1$s WHERE id IN (%2$s)', TRAV_TOUR_BOOKINGS_TABLE, "$format" );
            } else {
                $sql = sprintf( 'DELETE booking FROM %1$s as booking, %2$s as schedule, %3$s as tour WHERE booking.ts_id=schedule.id AND schedule.tour_id=tour.ID AND booking.id IN (%4$s) AND tour.post_author = %5$d', TRAV_TOUR_BOOKINGS_TABLE, TRAV_TOUR_SCHEDULES_TABLE, $post_table_name, "$format", $current_user_id );
            }

            //$sql = sprintf( $sql, $selected_ids );
            $sql = $wpdb->prepare( $sql, $selected_ids );
            $wpdb->query( $sql );
            wp_redirect( admin_url( 'edit.php?post_type=tour&page=tour_bookings&bulk_delete=true') );
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
        if ( ! empty( $_REQUEST['tour_id'] ) ) $where .= " AND booking.tour_id = " . esc_sql( trav_tour_org_id( $_REQUEST['tour_id'] ) );
        if ( isset( $_REQUEST['st_id'] ) ) $where .= " AND booking.st_id = '" . esc_sql( $_REQUEST['st_id'] ) . "'";
        if ( ! empty( $_REQUEST['tour_date'] ) ) $where .= " AND booking.tour_date = '" . esc_sql( $_REQUEST['tour_date'] ) . "'";
        if ( ! empty( $_REQUEST['booking_no'] ) ) $where .= " AND booking.booking_no = '" . esc_sql( $_REQUEST['booking_no'] ) . "'";
        if ( isset( $_REQUEST['status'] ) ) $where .= " AND booking.status = '" . esc_sql( $_REQUEST['status'] ) . "'";
        if ( ! current_user_can( 'manage_options' ) ) { $where .= " AND tour.post_author = '" . get_current_user_id() . "' "; }

        $sql = sprintf( 'SELECT booking.*, tour.ID as tour_id, tour.post_title as tour_name FROM %1$s as booking
                        INNER JOIN %2$s as tour ON booking.tour_id=tour.ID
                        WHERE ' . $where . ' ORDER BY %3$s %4$s
                        LIMIT %5$s, %6$s' , TRAV_TOUR_BOOKINGS_TABLE, $post_table_name, $orderby, $order, $per_page * ( $current_page - 1 ), $per_page );
        $data = $wpdb->get_results( $sql, ARRAY_A );

        $sql = sprintf( 'SELECT COUNT(*) FROM %1$s as booking INNER JOIN %2$s as tour ON booking.tour_id=tour.ID WHERE ' . $where , TRAV_TOUR_BOOKINGS_TABLE, $post_table_name );
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
if ( ! function_exists( 'trav_tour_booking_add_menu_items' ) ) {
    function trav_tour_booking_add_menu_items() {
        $page = add_submenu_page( 'edit.php?post_type=tour', __('Tour Bookings', 'trav'), __('Bookings', 'trav'), 'edit_accommodations', 'tour_bookings', 'trav_tour_booking_render_pages' );

        add_action( 'admin_print_scripts-' . $page, 'trav_tour_booking_admin_enqueue_scripts' );
    }
}

/*
 * booking admin main actions
 */
if ( ! function_exists( 'trav_tour_booking_render_pages' ) ) {
    function trav_tour_booking_render_pages() {
        if ( ( ! empty( $_REQUEST['action'] ) ) && ( ( 'add' == $_REQUEST['action'] ) || ( 'edit' == $_REQUEST['action'] ) ) ) {
            trav_tour_booking_render_manage_page();
        } elseif ( ( ! empty( $_REQUEST['action'] ) ) && ( 'delete' == $_REQUEST['action'] ) ) {
            trav_tour_booking_delete_action();
        } else {
            trav_tour_booking_render_list_page();
        }
    }
}

/*
 * render booking list page
 */
if ( ! function_exists( 'trav_tour_booking_render_list_page' ) ) {
    function trav_tour_booking_render_list_page() {
        global $wpdb;

        $travBookingTable = new Trav_Tour_Booking_List_Table();
        $travBookingTable->prepare_items();

        $tour_id = empty( $_REQUEST['tour_id'] ) ? '' : $_REQUEST['tour_id'];
        $st_id = empty( $_REQUEST['st_id'] ) ? '' : $_REQUEST['st_id'];
        $schedule_types = trav_tour_get_schedule_types( $tour_id );
        ?>

        <div class="wrap">

            <h2><?php _e('Tour Bookings', 'trav') ?> <a href="edit.php?post_type=tour&amp;page=tour_bookings&amp;action=add" class="add-new-h2"><?php _e('Add New', 'trav') ?></a></h2>

            <?php 
            if ( isset( $_REQUEST['bulk_delete'] ) ) {
                echo '<div id="message" class="updated below-h2"><p>' . __('Bookings deleted', 'trav') . '</p></div>';
            }
            ?>

            <select id="tour_id">
                <?php echo trav_tour_get_tour_list( $tour_id ) ?>
            </select>

            <span class="schedule_type_wrapper<?php if ( empty( $schedule_types ) ) echo ' hide' ?>">
                <select name="st_id" id="schedule_type">
                    <?php echo trav_tour_get_schedule_type_list( $tour_id, $st_id ) ?>
                </select>
            </span>

            <input type="text" id="tour_date" name="tour_date" placeholder="<?php _e('Filter by Start Date', 'trav') ?>" value="<?php if ( ! empty( $_REQUEST['tour_date'] ) ) echo esc_attr( $_REQUEST['tour_date'] ); ?>">
            <input type="text" id="booking_no" name="booking_no" placeholder="<?php _e('Filter by Booking No', 'trav') ?>" value="<?php if ( ! empty( $_REQUEST['booking_no'] ) ) echo esc_attr( $_REQUEST['booking_no'] ); ?>">
            
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

            <a href="edit.php?post_type=tour&amp;page=tour_bookings" class="button-secondary"><?php _e('Show All', 'trav') ?></a>

            <form id="tour-bookings-filter" method="get">
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
if ( ! function_exists( 'trav_tour_booking_render_manage_page' ) ) {
    function trav_tour_booking_render_manage_page() {
        global $wpdb, $trav_options;

        $post_table_name  = $wpdb->prefix . 'posts';

        $site_currency_symbol = trav_get_site_currency_symbol();

        if ( ! empty( $_POST['save'] ) ) {
            trav_tour_booking_save_action();
            return;
        }

        $booking_data = array();

        if ( 'add' == $_REQUEST['action'] ) {
            $page_title = __("Add New Tour booking", "trav");
        } elseif ( 'edit' == $_REQUEST['action'] ) {
            $page_title = __('Edit Tour Booking', 'trav') . '<a href="edit.php?post_type=tour&amp;page=tour_bookings&amp;action=add" class="add-new-h2">' . __('Add New', 'trav') . '</a>';

            if ( empty( $_REQUEST['booking_id'] ) ) {
                echo "<h2>" . __("You attempted to edit an item that doesn't exist. Perhaps it was deleted?", "trav") . "</h2>";
                return;
            }

            $booking_id = $_REQUEST['booking_id'];

            $where = 'booking.id = %3$d';
            if ( ! current_user_can( 'manage_options' ) ) { $where .= " AND tour.post_author = '" . get_current_user_id() . "' "; }

            $sql = sprintf( 'SELECT booking.*, tour.ID as tour_id, tour.post_title as tour_name FROM %1$s as booking
                INNER JOIN %2$s as tour ON booking.tour_id=tour.ID
                WHERE ' . $where , TRAV_TOUR_BOOKINGS_TABLE, $post_table_name, $booking_id );

            $booking_data = $wpdb->get_row( $sql, ARRAY_A );
            if ( empty( $booking_data ) ) {
                echo "<h2>" . __("You attempted to edit an item that doesn't exist. Perhaps it was deleted?", "trav") . "</h2>";
                return;
            }
        }
        $tour_id = empty( $booking_data['tour_id'] ) ? '' : $booking_data['tour_id'];
        $schedule_types = trav_tour_get_schedule_types( $tour_id );
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
                            <th><?php _e('Tour', 'trav') ?></th>
                            <td>
                                <select name="tour_id" id="tour_id">
                                    <?php echo trav_tour_get_tour_list( $booking_data['tour_id'] ) ?>
                                </select>
                            </td>
                        </tr>
                        <tr class="schedule_type_wrapper<?php if ( empty( $schedule_types ) ) echo ' hide' ?>">
                            <th><?php _e('Schedule Type', 'trav') ?></th>
                            <td>
                                <select name="st_id" id="schedule_type">
                                    <?php echo trav_tour_get_schedule_type_list( $booking_data['tour_id'], $booking_data['st_id'] ) ?>
                                </select>
                            </td>
                        </tr>
                        <tr>
                            <th><?php _e('Tour Date', 'trav') ?></th>
                            <td><input type="text" name="tour_date" id="tour_date" value="<?php if ( ! empty( $booking_data['tour_date'] ) ) echo esc_attr( $booking_data['tour_date'] ); ?>"></td>
                        </tr>
                        <tr>
                            <th><?php _e('Adults', 'trav') ?></th>
                            <td><input type="number" name="adults" value="<?php if ( ! empty( $booking_data['adults'] ) ) echo esc_attr( $booking_data['adults'] ) ?>"></td>
                        </tr>
                        <tr>
                            <th><?php _e('Children', 'trav') ?></th>
                            <td><input type="number" name="kids" value="<?php if ( ! empty( $booking_data['kids'] ) ) echo esc_attr( $booking_data['kids'] ) ?>"></td>
                        </tr>
                        <tr>
                            <th><?php _e('Discount', 'trav') ?></th>
                            <td><input type="text" name="discount_rate" value="<?php if ( ! empty( $booking_data['discount_rate'] ) ) echo esc_attr( $booking_data['discount_rate'] ) ?>"> <?php echo '%' ?></td>
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

                <a href="edit.php?post_type=tour&amp;page=tour_bookings" class="button-secondary"><?php _e('Cancel', 'trav') ?></a>

                <?php wp_nonce_field('trav_manage_tour_bookings','booking_save'); ?>
            </form>
        </div>

        <?php if ( ! empty( $trav_options['vld_credit_card'] ) && ! empty( $trav_options['cc_off_charge'] ) && ! empty( $booking_data['other'] ) ) {
            $cc_fields = array( 'cc_type' => __('CREDIT CARD TYPE', 'trav'), 'cc_holder_name' => __('CARD HOLDER NAME', 'trav'), 'cc_number' => __('CARD NUMBER', 'trav'), 'cc_cid' => __('CARD IDENTIFICATION NUMBER', 'trav'), 'cc_exp_year' => __('EXPIRATION YEAR', 'trav'), 'cc_exp_month' => __('EXPIRATION MONTH', 'trav') );
            $cc_infos = unserialize( $booking_data['other'] );

            echo '<style>.cc_table{background:#fff;margin-top:30px;}.cc_table td{padding:10px;}.cc_table,.cc_table tr,.cc_table td{border:1px solid #000; border-collapse: collapse;}</style>';
            echo '<div style="clear:both"></div><h3>' . __('Credit Card Info', 'trav') . '</h3><table class="cc_table"><tbody>';

            foreach ($cc_fields as $key => $label) {
                if ( ! empty( $cc_infos[ $key ] ) ) {
                    echo '<tr><td><label>' . $label . '</label></td><td>' . $cc_infos[ $key ] . '</td></tr>';
                }
            }
            echo '</tbody></table>';
        }
    }
}

/*
 * booking delete action
 */
if ( ! function_exists( 'trav_tour_booking_delete_action' ) ) {
    function trav_tour_booking_delete_action() {
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
            $sql = sprintf( 'SELECT schedule.tour_id FROM %1$s as booking, %2$s as schedule WHERE booking.id = %3$d AND booking.ts_id=schedule.id' , TRAV_TOUR_BOOKINGS_TABLE, TRAV_TOUR_SCHEDULES_TABLE, $_REQUEST['booking_id'] );
            $tour_id = $wpdb->get_var( $sql );

            $post_author_id = get_post_field( 'post_author', $tour_id );

            if ( get_current_user_id() != $post_author_id ) {
                print __('You don\'t have permission to remove other\'s item.', 'trav');
                exit;
            }
        }

        // do action
        $wpdb->delete( TRAV_TOUR_BOOKINGS_TABLE, array( 'id' => $_REQUEST['booking_id'] ) );

        wp_redirect( admin_url( 'edit.php?post_type=tour&page=tour_bookings') );
        exit;
    }
}

/*
 * booking save action
 */
if ( ! function_exists( 'trav_tour_booking_save_action' ) ) {
    function trav_tour_booking_save_action() {
        if ( ! isset( $_POST['booking_save'] ) || ! wp_verify_nonce( $_POST['booking_save'], 'trav_manage_tour_bookings' ) ) {
            print 'Sorry, your nonce did not verify.';
            exit;
        } else {
            global $wpdb;

            $default_booking_data = array(  
                'tour_id'               => '',
                'st_id'                 => '',
                'tour_date'             => '',
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
                'kids'                  => '',
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

            $table_fields = array( 'tour_id', 'st_id', 'tour_date', 'first_name', 'last_name', 'email', 'country_code', 'phone', 'address', 'city', 'zip', 'country', 'special_requirements', 'adults', 'kids', 'discount_rate', 'total_price', 'currency_code', 'exchange_rate', 'deposit_price', 'deposit_paid', 'booking_no', 'pin_code', 'status' );
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
            $data['tour_id'] = trav_tour_org_id( $data['tour_id'] );

            if ( empty( $_POST['id'] ) ) {
                //insert
                $data['created'] = date( 'Y-m-d H:i:s' );
                $wpdb->insert( TRAV_TOUR_BOOKINGS_TABLE, $data );
                $id = $wpdb->insert_id;
            } else {
                //update
                $wpdb->update( TRAV_TOUR_BOOKINGS_TABLE, $data, array( 'id' => sanitize_text_field( $_POST['id'] ) ) );
                $id = sanitize_text_field( $_POST['id'] );
            }
            
            wp_redirect( admin_url( 'edit.php?post_type=tour&page=tour_bookings&action=edit&booking_id=' . $id . '&updated=true') );
            exit;
        }
    }
}

/*
 * booking admin enqueue script action
 */
if ( ! function_exists( 'trav_tour_booking_admin_enqueue_scripts' ) ) {
    function trav_tour_booking_admin_enqueue_scripts() {

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
        wp_enqueue_style( 'trav_admin_tour_stype' , get_template_directory_uri() . '/inc/admin/css/style.css' ); 
        wp_enqueue_script( 'trav_admin_tour_script' , TRAV_TEMPLATE_DIRECTORY_URI . '/inc/admin/tour/js/script.js', array('jquery'), '1.0', true );
    }
}

add_action( 'admin_menu', 'trav_tour_booking_add_menu_items' );