<?php
if ( ! defined( 'ABSPATH' ) ) exit; // Exit if accessed directly

/**
 * Trav_Walker_Nav_Menu class
 * it is related with wp_nav_menu()
 */
if ( ! class_exists( 'Trav_Walker_Nav_Menu') ) :
class Trav_Walker_Nav_Menu extends Walker_Nav_Menu {

	private $is_megamenu = false;
	function start_lvl( &$output, $depth = 0, $args = array() ) {
		$indent = str_repeat("\t", $depth);
		$class = '';
		if( ( $depth == 0 ) && ( $this->is_megamenu) ) $class = "megamenu";
		$output .= "\n$indent<ul class=\"sub-menu sub-menu-dropdown sub-menu-level-". $depth ." $class\">\n";
	}
	
	function end_lvl( &$output, $depth = 0, $args = array() ) {
		$indent = str_repeat("\t", $depth);
		$output .= "$indent</ul>\n";
	}
	
	function start_el( &$output, $item, $depth = 0, $args = array(), $id = 0 ) {
		$indent = ( $depth ) ? str_repeat( "\t", $depth ) : '';

		$class_names = '';

		$classes = empty( $item->classes ) ? array() : (array) $item->classes;
		$classes[] = 'menu-item-' . $item->ID;
		$classes[] = 'item-level-' . $depth;

		if ( ( $item->menu_item_parent == 0 ) && ( $item->megamenu == "on" ) && in_array( 'menu-item-has-children', $classes ) ) {
			$this->is_megamenu = true;
			$classes[] = 'megamenu-menu';
		}

		if ( ( $item->menu_item_parent == 0 ) && ( $item->menu_color != "" ) ) {
			$classes[] = str_replace( '_', '-', $item->menu_color );
		}

		$class_names = join( ' ', apply_filters( 'nav_menu_css_class', array_filter( $classes ), $item, $args ) );
		$class_names = $class_names ? ' class="' . esc_attr( $class_names ) . '"' : '';

		$id = apply_filters( 'nav_menu_item_id', 'menu-item-'. $item->ID, $item, $args );
		$id = $id ? ' id="' . esc_attr( $id ) . '"' : '';

		$output .= $indent . '<li' . $id . $class_names .'>';

		$atts = array();
		$atts['title']  = ! empty( $item->attr_title ) ? $item->attr_title : '';
		$atts['target'] = ! empty( $item->target )     ? $item->target     : '';
		$atts['rel']    = ! empty( $item->xfn )        ? $item->xfn        : '';
		$atts['href']   = ! empty( $item->url )        ? $item->url        : '';

		$atts = apply_filters( 'nav_menu_link_attributes', $atts, $item, $args );

		$attributes = '';
		foreach ( $atts as $attr => $value ) {
			if ( ! empty( $value ) ) {
				$value = ( 'href' === $attr ) ? esc_url( $value ) : esc_attr( $value );
				$attributes .= ' ' . $attr . '="' . $value . '"';
			}
		}

		$item_output = $args->before;
		$item_output .= '<a'. $attributes .'>';
		/** This filter is documented in wp-includes/post-template.php */
		$item_output .= $args->link_before . apply_filters( 'the_title', $item->title, $item->ID ) . $args->link_after;
		$item_output .= '</a>';
		$item_output .= $args->after;

		if ( ( $depth == 0 ) && ( $this->is_megamenu ) ) {
			$item_output .= '<div class="megamenu-wrapper ' . $item->megamenu_style . ' container">';
		}
		$output .= apply_filters( 'walker_nav_menu_start_el', $item_output, $item, $depth, $args );
	}

	function end_el( &$output, $item, $depth = 0, $args = array() ) {
		if ( ( $depth == 0 ) && ( $this->is_megamenu ) ) {
			$output .= '</div>';
			$this->is_megamenu = false;
		}
		$output .= "</li>\n";
	}
} // Walker_Nav_Menu
endif;