<?php
/**
 * Product Loop Start
 *
 * @see         https://docs.woocommerce.com/document/template-structure/
 * @author      WooThemes
 * @package     WooCommerce/Templates
 * @version     3.3.0
 */

global $trav_options, $woocommerce_loop;

if ( is_archive() ) { 
    if ( 2 == $trav_options['shop_product_columns'] ) { 
        $class = 'pcols-ls-1 pcols-xs-2 pcols-md-2 pcols-lg-2';
        $woocommerce_loop['columns'] = 2;
    } else if ( 3 == $trav_options['shop_product_columns'] ) { 
        $class = 'pcols-ls-1 pcols-xs-2 pcols-md-3 pcols-lg-3';
        $woocommerce_loop['columns'] = 3;
    } else if ( 4 == $trav_options['shop_product_columns'] ) { 
        $class = 'pcols-ls-1 pcols-xs-2 pcols-md-4 pcols-lg-4';
        $woocommerce_loop['columns'] = 4;
    } else if ( 5 == $trav_options['shop_product_columns'] ) { 
        $class = 'pcols-ls-1 pcols-xs-2 pcols-md-3 pcols-lg-5';
        $woocommerce_loop['columns'] = 5;
    } else if ( 6 == $trav_options['shop_product_columns'] ) { 
        $class = 'pcols-ls-1 pcols-xs-2 pcols-md-3 pcols-lg-6';
        $woocommerce_loop['columns'] = 6;
    }
}

if ( 'up-sells' == $woocommerce_loop['name'] || 'related' == $woocommerce_loop['name'] ) { 
    $class = 'slides';
}

$woocommerce_loop['loop'] = 0;

if ( ! empty( $_GET['view'] ) && $_GET['view'] == 'list' ) { 
    $class .= ' list';
}

?>

<ul class="products image-box <?php echo $class ?>">