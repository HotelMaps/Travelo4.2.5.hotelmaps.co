<?php
if ( ! defined( 'ABSPATH' ) ) exit; // Exit if accessed directly

/**
 * Custom Menu
 */
if ( ! class_exists( 'Trav_Custom_Menu') ) :
class Trav_Custom_Menu {

	function __construct() {
		add_filter( 'wp_setup_nav_menu_item', array( $this, 'trav_add_custom_nav_fields' ) );
		add_action( 'wp_update_nav_menu_item', array( $this, 'trav_update_custom_nav_fields'), 10, 3 );
		add_filter( 'wp_edit_nav_menu_walker', array( $this, 'trav_edit_walker'), 10, 2 );
	}

	//Adding Custom Fields Filter
	function trav_add_custom_nav_fields( $menu_item ) {
		$menu_item->megamenu = get_post_meta( $menu_item->ID, '_menu_item_megamenu', true );
		$menu_item->megamenu_style = get_post_meta( $menu_item->ID, '_menu_item_megamenu_style', true );
		$menu_item->menu_color = get_post_meta( $menu_item->ID, '_menu_item_menu_color', true );
		return $menu_item;
	}

	//Saving Custom Fields
	function trav_update_custom_nav_fields( $menu_id, $menu_item_db_id, $args ) {

		if ( isset( $_REQUEST['menu-item-color'] ) && is_array( $_REQUEST['menu-item-color']) ) {
			$value = isset( $_REQUEST['menu-item-color'][$menu_item_db_id] )?$_REQUEST['menu-item-color'][$menu_item_db_id]:'';
			update_post_meta( $menu_item_db_id, '_menu_item_menu_color', $value );
		}

		$value = '';
		if ( isset( $_REQUEST['menu-item-megamenu'] ) && is_array( $_REQUEST['menu-item-megamenu']) ) {
			$value = isset( $_REQUEST['menu-item-megamenu'][$menu_item_db_id] )?$_REQUEST['menu-item-megamenu'][$menu_item_db_id]:'';
		}
		update_post_meta( $menu_item_db_id, '_menu_item_megamenu', $value );

		if ( isset( $_REQUEST['menu-item-megamenu-style'] ) && is_array( $_REQUEST['menu-item-megamenu-style']) ) {
			$value = isset( $_REQUEST['menu-item-megamenu-style'][$menu_item_db_id] )?$_REQUEST['menu-item-megamenu-style'][$menu_item_db_id]:'';
			update_post_meta( $menu_item_db_id, '_menu_item_megamenu_style', $value );
		}
	}

	//form
	function trav_edit_walker($walker,$menu_id) {
		return 'Trav_Walker_Nav_Menu_Edit';
	}
}
endif;

new Trav_Custom_Menu();

include_once( 'trav_walker_nav_menu_edit.clas.php' );
include_once( 'trav_walker_nav_menu.class.php' );