<?php 
/*
 * Location page template
 */ 
if ( ! defined( 'ABSPATH' ) ) {
    exit; // Exit if accessed directly
}

get_header(); 

global $trav_options;
?>

<section id="content">
    <div class="container">
        <div class="row">
            <div id="main" class="col-md-9">
                <?php
                //$term = get_query_var( 'term' );
                $term = get_term_by( 'slug', get_query_var( 'term' ), 'location' );
                $args = array(
                    'hide_empty'    => false,
                    'child_of'      => $term->term_id,
                );
                $child_terms = get_terms( 'location', $args );

                if ( empty( $child_terms ) ) { 
                    // show Accommodations
                    $args = array(
                        'post_type'     => 'accommodation',
                        'meta_key'      => 'trav_accommodation_city',
                        'meta_value'    => $term->term_id,
                    );

                    $acc_query = new WP_Query( $args );

                    if ( $acc_query->have_posts() ) { 
                        ?>

                        <h2><?php _e( 'Accommodations', 'trav' ) ?></h2>
                        <div class="image-box style2 activities no-bottom-border blog-infinite"> 
                            <?php 
                            while( $acc_query->have_posts() ) { 
                                $acc_query->the_post();
                                $acc_brief = get_post_meta( $acc_query->post->ID, 'trav_accommodation_brief', true );
                                ?>

                                <article class="box">
                                    <?php if ( '' != get_the_post_thumbnail() ) : ?>
                                        <figure>
                                            <a href="<?php echo get_the_permalink( $acc_query->post->ID ); ?>" class="hover-effect"><?php echo get_the_post_thumbnail( $acc_query->post->ID, 'gallery-thumb' ); ?></a>
                                        </figure>
                                    <?php endif; ?>

                                    <div class="details entry-content">
                                        <div class="details-header">
                                            <h4 class="box-title"><?php echo get_the_title( $acc_query->post->ID ); ?></h4>
                                        </div>

                                        <p><?php echo $acc_brief ?></p>

                                        <a class="button pull-right" title="" href="<?php echo get_the_permalink( $acc_query->post->ID ); ?>"><?php echo __( 'MORE', 'trav' ) ?></a>
                                    </div>
                                </article>

                                <?php 
                            } 
                            ?>
                        </div>
                        <?php
                    }

                    wp_reset_postdata();

                    // show Tours
                    $args = array(
                        'post_type'     => 'tour',
                        'meta_key'      => 'trav_tour_city',
                        'meta_value'    => $term->term_id,
                    );

                    $tour_query = new WP_Query( $args );
                    if ( $tour_query->have_posts() ) { 
                        ?>

                        <h2><?php _e( 'Tours', 'trav' ) ?></h2>
                        <div class="image-box style2 activities no-bottom-border blog-infinite"> 
                            <?php 
                            while( $tour_query->have_posts() ) { 
                                $tour_query->the_post();
                                $tour_brief = get_post_meta( $tour_query->post->ID, 'trav_tour_brief', true );
                                ?>

                                <article class="box">
                                    <?php if ( '' != get_the_post_thumbnail() ) : ?>
                                        <figure>
                                            <a href="<?php echo get_the_permalink( $tour_query->post->ID ); ?>" class="hover-effect"><?php echo get_the_post_thumbnail( $tour_query->post->ID, 'gallery-thumb' ); ?></a>
                                        </figure>
                                    <?php endif; ?>

                                    <div class="details entry-content">
                                        <div class="details-header">
                                            <h4 class="box-title"><?php echo get_the_title( $tour_query->post->ID ); ?></h4>
                                        </div>

                                        <p><?php echo $tour_brief ?></p>

                                        <a class="button pull-right" title="" href="<?php echo get_the_permalink( $tour_query->post->ID ); ?>"><?php echo __( 'MORE', 'trav' ) ?></a>
                                    </div>
                                </article>

                                <?php 
                            } 
                            ?>
                        </div>
                        <?php
                    }

                    wp_reset_postdata();
                    ?>

                    <h2><?php _e( 'Things to Do', 'trav' ) ?></h2>
                    <div class="image-box style2 activities no-bottom-border blog-infinite">
                        <?php while ( have_posts()): the_post(); ?>
                            <article class="box">
                                <?php if ( '' != get_the_post_thumbnail() ) : ?>
                                    <figure>
                                        <a href="<?php the_permalink(); ?>" class="hover-effect"><?php echo get_the_post_thumbnail(); ?></a>
                                    </figure>
                                <?php endif; ?>

                                <div class="details entry-content">
                                    <div class="details-header">
                                        <h4 class="box-title"><?php the_title(); ?></h4>
                                    </div>

                                    <p><?php the_content( '...' ); ?></p>

                                    <a class="button pull-right" title="" href="<?php the_permalink(); ?>"><?php echo __( 'MORE', 'trav' ) ?></a>
                                </div>
                            </article>
                        <?php endwhile; ?>
                    </div>

                    <?php

                    if ( ! empty( $trav_options['ajax_pagination'] ) ) {
                        next_posts_link( __( 'LOAD MORE POSTS', 'trav' ) );
                    } else {
                        echo paginate_links( array( 'type' => 'list' ) );
                    }

                } elseif ( ! empty( $child_terms ) && ! is_wp_error( $child_terms ) ) { 
                    echo do_shortcode( '[locations parent="' . $term->term_id . '" column="5" image_size="thumbnail"]' ); 
                } 
                ?>
            </div>

            <div class="sidebar col-md-3">
                <?php dynamic_sidebar('sidebar-ttd'); ?>
            </div>
        </div>
    </div>
</section>

<?php 
get_footer();