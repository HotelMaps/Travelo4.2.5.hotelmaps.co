<?php
/**
 * The Template for displaying product archives, including the main shop page which is a post type archive
 *
 * @see         https://docs.woocommerce.com/document/template-structure/
 * @author      WooThemes
 * @package     WooCommerce/Templates
 * @version 3.4.0
 */

if ( ! defined( 'ABSPATH' ) ) {
    exit; // Exit if accessed directly
}

global $trav_options;

if ( 'no_sidebar' == $trav_options['shop_page_layout'] ) { 
    $content_class = 'col-md-12';
    $sidebar_class = '';
} else if ( 'left_sidebar' == $trav_options['shop_page_layout'] ) { 
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
                 * @hooked woocommerce_output_content_wrapper - 10 : removed
                 * @hooked woocommerce_breadcrumb - 20 : removed
                 */
                do_action( 'woocommerce_before_main_content' );
            ?>

            <?php if ( apply_filters( 'woocommerce_show_page_title', false ) ) : ?>

                <h1 class="page-title"><?php woocommerce_page_title(); ?></h1>

            <?php endif; ?>

            <div id="main" class="entry-content <?php echo $content_class ?>"> 
                <?php
                    /**
                     * woocommerce_archive_description hook.
                     *
                     * @hooked woocommerce_taxonomy_archive_description - 10
                     * @hooked woocommerce_product_archive_description - 10
                     */
                    do_action( 'woocommerce_archive_description' );
                ?>

                <?php if ( have_posts() ) : ?>

                    <?php
                        /**
                         * woocommerce_before_shop_loop hook.
                         *
                         * @hooked woocommerce_result_count - 20 : removed
                         * @hooked woocommerce_catalog_ordering - 30
                         */
                        do_action( 'woocommerce_before_shop_loop' );
                        woocommerce_product_loop_start();
                    ?>

                    <?php
                    if ( wc_get_loop_prop( 'total' ) ) {
                        while ( have_posts() ) {
                            the_post();

                            /**
                             * Hook: woocommerce_shop_loop.
                             *
                             * @hooked WC_Structured_Data::generate_product_data() - 10
                             */
                            do_action( 'woocommerce_shop_loop' );

                            wc_get_template_part( 'content', 'product' );
                        }
                    }

                    woocommerce_product_loop_end();
                    ?>

                    <?php
                        /**
                         * woocommerce_after_shop_loop hook.
                         *
                         * @hooked woocommerce_pagination - 10
                         */
                        do_action( 'woocommerce_after_shop_loop' );
                    ?>

                <?php else :

                    /**
                     * Hook: woocommerce_no_products_found.
                     *
                     * @hooked wc_no_products_found - 10
                     */
                    do_action( 'woocommerce_no_products_found' );

                endif; ?>
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
                     * @hooked woocommerce_result_count - 5 : added
                     * @hooked woocommerce_get_sidebar - 10
                     */
                    do_action( 'woocommerce_sidebar' );
                ?>
            </div>

            </div>
        </div>
    </section>

<?php get_footer( 'shop' ); ?>
