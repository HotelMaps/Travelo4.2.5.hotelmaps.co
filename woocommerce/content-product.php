<?php
/**
 * The template for displaying product content within loops
 *
 * @see     https://docs.woocommerce.com/document/template-structure/
 * @author  WooThemes
 * @package WooCommerce/Templates
 * @version 3.6.0
 */

if ( ! defined( 'ABSPATH' ) ) {
    exit; // Exit if accessed directly
}

global $product, $trav_options;

// Ensure visibility
if ( empty( $product ) || ! $product->is_visible() ) {
    return;
}

?>
<li <?php wc_product_class( '', $product ); ?>>
    <article class="box">
        <?php
        /**
         * woocommerce_before_shop_loop_item hook.
         *
         * @hooked woocommerce_show_product_loop_sale_flash - 5 : added
         * @hooked woocommerce_template_loop_product_link_open - 10 : removed
         * @hooked trav_woo_template_loop_product_link_open - 10 : added
         */
        do_action( 'woocommerce_before_shop_loop_item' );

        /**
         * woocommerce_before_shop_loop_item_title hook.
         *
         * @hooked woocommerce_show_product_loop_sale_flash - 10 : removed
         * @hooked woocommerce_template_loop_product_thumbnail - 10
         * @hooked trav_woo_template_loop_product_link_close - 20 : added
         * @hooked trav_woo_template_loop_product_detail_open - 30 : added
         */
        do_action( 'woocommerce_before_shop_loop_item_title' );

        /**
         * woocommerce_shop_loop_item_title hook.
         *
         * @hooked woocommerce_template_loop_product_title - 10 : removed
         * @hooked trav_woo_template_loop_product_title - 10 : added
         */
        do_action( 'woocommerce_shop_loop_item_title' );

        /**
         * woocommerce_after_shop_loop_item_title hook.
         *
         * @hooked woocommerce_template_loop_price - 10
         * @hooked woocommerce_template_loop_rating - 20
         */
        do_action( 'woocommerce_after_shop_loop_item_title' );

        /**
         * woocommerce_after_shop_loop_item hook.
         *
         * @hooked woocommerce_template_loop_product_link_close - 5 : removed
         * @hooked woocommerce_template_loop_add_to_cart - 10
         * @hooked trav_woo_template_loop_product_detail_close - 30 : added
         */
        do_action( 'woocommerce_after_shop_loop_item' );
        ?>
    </article>
</li>
