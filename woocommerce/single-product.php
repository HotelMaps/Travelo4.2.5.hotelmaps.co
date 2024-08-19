<?php
/**
 * The Template for displaying all single products
 *
 * @see         https://docs.woocommerce.com/document/template-structure/
 * @author      WooThemes
 * @package     WooCommerce/Templates
 * @version     1.6.4
 */

if ( ! defined( 'ABSPATH' ) ) {
    exit; // Exit if accessed directly
}

global $trav_options;

if ( 'no_sidebar' == $trav_options['product_page_layout'] ) { 
    $content_class = 'col-md-12';
    $sidebar_class = 'col-md-12';
} else if ( 'left_sidebar' == $trav_options['product_page_layout'] ) { 
    $content_class = 'col-md-9 pull-right';
    $sidebar_class = 'col-md-3';
} else { 
    $content_class = 'col-md-9';
    $sidebar_class = 'col-md-3';
}

get_header( 'shop' ); ?>

<section id="content">
    <div class="container">
        <div class="row">

        <?php
            /**
             * woocommerce_before_main_content hook.
             *
             * @hooked woocommerce_output_content_wrapper - 10 (outputs opening divs for the content) : removed
             * @hooked woocommerce_breadcrumb - 20 : removed
             */
            do_action( 'woocommerce_before_main_content' );
        ?>

        <div id="main" class="entry-content <?php echo $content_class ?>"> 
            <?php while ( have_posts() ) : the_post(); ?>

                <?php wc_get_template_part( 'content', 'single-product' ); ?>

            <?php endwhile; // end of the loop. ?>
        </div>

        <?php
            /**
             * woocommerce_after_main_content hook.
             *
             * @hooked woocommerce_output_content_wrapper_end - 10 (outputs closing divs for the content) : removed
             */
            do_action( 'woocommerce_after_main_content' );
        ?>

        <div class="sidebar <?php echo $sidebar_class ?>">
            <?php
                /**
                 * woocommerce_sidebar hook.
                 *
                 * @hooked woocommerce_get_sidebar - 10
                 */
                do_action( 'woocommerce_sidebar' );
            ?>
        </div>

        </div>
    </div>
</section>

<?php get_footer( 'shop' ); ?>
