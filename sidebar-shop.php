<?php 
/* Sidebar for WooCommerce */
global $trav_options;

if ( is_single() ) { 
    if ( 'no_sidebar' != $trav_options['shop_page_layout'] ) { 
        dynamic_sidebar( 'product-sidebar' );
    }
} else { 
    if ( 'no_sidebar' != $trav_options['shop_page_layout'] ) { 
        dynamic_sidebar( 'shop-sidebar' );
    }
}