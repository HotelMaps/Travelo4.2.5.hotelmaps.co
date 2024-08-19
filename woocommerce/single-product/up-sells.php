<?php
/**
 * Single Product Up-Sells
 *
 * @see 	    https://docs.woocommerce.com/document/template-structure/
 * @author 		WooThemes
 * @package 	WooCommerce/Templates
 * @version     3.0.0
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

global $trav_options, $woocommerce_loop;

if ( $upsells && $trav_options['product_up_sell'] ) : ?>
	
	<?php
	
	$woocommerce_loop['columns'] = $trav_options['up_sell_product_columns'];
	$attr = ' data-columns=' . $woocommerce_loop['columns'];
	
	?>
	<section class="up-sells upsells products flexslider" <?php echo $attr ?>>

		<h2><?php esc_html_e( 'You may also like&hellip;', 'woocommerce' ) ?></h2>

		<?php woocommerce_product_loop_start(); ?>

			<?php foreach ( $upsells as $upsell ) : ?>

				<?php
				 	$post_object = get_post( $upsell->get_id() );

					setup_postdata( $GLOBALS['post'] =& $post_object );

					wc_get_template_part( 'content', 'product' ); ?>

			<?php endforeach; ?>

		<?php woocommerce_product_loop_end(); ?>

	</section>

<?php endif;

wp_reset_postdata();
