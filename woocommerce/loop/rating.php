<?php
/**
 * Loop Rating
 *
 * @see 	    https://docs.woocommerce.com/document/template-structure/
 * @author 		WooThemes
 * @package 	WooCommerce/Templates
 * @version 3.6.0
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly
}

global $product, $trav_options;

if ( ( ! wc_review_ratings_enabled() ) || empty( $trav_options['shop_ratings_archive_page'] ) )
	return;
?>

<?php if ( $rating_html = wc_get_rating_html( $product->get_average_rating() ) ) :
	$review_count = $product->get_review_count(); ?>

	<div class="product-review"> 
		
		<?php echo $rating_html; ?>
		
		<span class="review-count"><?php echo $review_count . __( ' Reviews', 'trav' ) ?></span>
	
	</div>

	<hr />
<?php endif; ?>