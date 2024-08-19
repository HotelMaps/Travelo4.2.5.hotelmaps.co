<?php
/**
 * Product loop sale flash
 *
 * @see         https://docs.woocommerce.com/document/template-structure/
 * @author      WooThemes
 * @package     WooCommerce/Templates
 * @version     1.6.4
 */

if ( ! defined( 'ABSPATH' ) ) {
    exit; // Exit if accessed directly
}

global $post, $product, $trav_options;

?>
<?php if ( $product->is_on_sale() && $trav_options['woo_show_sale_flash'] ) : 

    if ( 'only_text' == $trav_options['woo_sale_flash_type'] ) { 
        $sale_text = ($trav_options['shop_sale_label'])? $trav_options['shop_sale_label'] : __( 'On Sale!', 'trav' );
    } else { 
        $sale_price = $product->get_sale_price();
        $regular_price = $product->get_regular_price();

        $discount = 100 - ( $sale_price / $regular_price ) * 100;
        $sale_text = (int)$discount . __( '% Discount', 'trav' );
    }

    echo apply_filters( 'woocommerce_sale_flash', '<span class="discount"><span class="discount-text">' . $sale_text . '</span></span>', $post, $product ); ?>

<?php endif; ?>
