<?php
/*
 * Accommodation List
 */
if ( ! defined( 'ABSPATH' ) ) {
    exit; // Exit if accessed directly
}

global $acc_list, $current_view, $login_url;

foreach( $acc_list as $acc_id ) {
    $acc_id = trav_acc_clang_id( $acc_id );
    $avg_price = get_post_meta( $acc_id, 'trav_accommodation_avg_price', true );
    $review = get_post_meta( $acc_id, 'review', true );
    $review = ( ! empty( $review ) ) ? round( $review, 1 ) : 0;
    $brief = get_post_meta( $acc_id, 'trav_accommodation_brief', true );
    if ( empty( $brief ) ) {
        $brief = apply_filters( 'the_content', get_post_field( 'post_content', $acc_id ) );
        $brief = wp_trim_words( $brief, 20, '' );
    }

    $gallery_imgs = get_post_meta( $acc_id, 'trav_gallery_imgs' );
    if ( empty( $gallery_imgs ) ) {
        $gallery_imgs = array();
    }

    $featured_img_id = get_post_thumbnail_id( $acc_id );
    if ( $featured_img_id ) {
        array_unshift( $gallery_imgs, $featured_img_id );
    }

    $city = trav_acc_get_city( $acc_id );
    $country = trav_acc_get_country( $acc_id );
    $review_count = trav_modern_get_review_count( $acc_id );

    if ( $current_view == 'grid' ) {
    ?>

        <div class="single-travel-item-wrap">
            <div class="single-travel-item">
                <?php if ( is_user_logged_in() ) {
                    $user_id = get_current_user_id();
                    $wishlist = get_user_meta( $user_id, 'wishlist', true );
                    if ( empty( $wishlist ) ) $wishlist = array();
                    if ( ! in_array( trav_acc_org_id( $acc_id ), $wishlist) ) { ?>
                        <a class="wishlist-ribbon btn-add-wishlist" data-post-id="<?php echo esc_attr( $acc_id ); ?>" data-label-add="<?php _e( 'add to wishlist', 'trav' ); ?>" data-label-remove="<?php _e( 'remove from wishlist', 'trav' ); ?>"><i class="fas fa-heart"></i></a>
                    <?php } else { ?>
                        <a class="wishlist-ribbon active btn-remove-wishlist" data-post-id="<?php echo esc_attr( $acc_id ); ?>" data-label-add="<?php _e( 'add to wishlist', 'trav' ); ?>" data-label-remove="<?php _e( 'remove from wishlist', 'trav' ); ?>"><i class="fas fa-heart"></i></a>
                    <?php } ?>
                <?php } else { ?>
                    <a href="<?php echo $login_url; ?>" class="wishlist-ribbon btn-add-wishlist"><i class="fas fa-heart"></i></a>
                <?php } ?>


                <div class="featured-imgs">
                    <?php foreach ( $gallery_imgs as $gallery_img ) {
                        echo wp_get_attachment_image( $gallery_img, 'modern-gallery-thumb' );
                    } ?>                    
                </div>

                <div class="package-item-info">
                    <h3 class="package-item-name"><?php echo get_the_title( $acc_id ); ?></h3>
                    <p class="package-item-direction"><?php echo esc_html( $city ); ?>, <?php echo esc_html( $country ); ?></p>
                    <div class="review-rating">
                        <div class="five-stars-container">
                            <span class="five-stars" style="width: <?php echo $review * 20; ?>%;"></span>
                        </div>
                        <span class="review-nums"><?php echo ( ! empty( $review_count ) ) ? number_format( $review_count ) : '0'; ?> <?php echo esc_html__( 'reviews', 'trav' ); ?></span>
                    </div>
                    <p class="description"><?php echo esc_html( $brief ); ?></p>
                    <div class="price-field"><?php echo trav_get_price_field( $avg_price ); ?> <span>/ <?php echo esc_html__( 'Pre Night', 'trav' ); ?></span></div>
                    <a href="<?php echo get_permalink( $acc_id ); ?>" class="view-detail border-btn-third"><?php echo esc_html__( 'See Details', 'trav' ); ?> <i class="fas fa-angle-double-right"></i></a>
                </div>
            </div>
        </div>

    <?php
    } elseif ( $current_view == 'map' ) {
    ?>

        <div class="single-travel-item-wrap">
            <div class="single-travel-item map-view-room">
                <?php if ( is_user_logged_in() ) {
                    $user_id = get_current_user_id();
                    $wishlist = get_user_meta( $user_id, 'wishlist', true );
                    if ( empty( $wishlist ) ) $wishlist = array();
                    if ( ! in_array( trav_acc_org_id( $acc_id ), $wishlist) ) { ?>
                        <a class="wishlist-ribbon btn-add-wishlist" data-post-id="<?php echo esc_attr( $acc_id ); ?>" data-label-add="<?php _e( 'add to wishlist', 'trav' ); ?>" data-label-remove="<?php _e( 'remove from wishlist', 'trav' ); ?>"><i class="fas fa-heart"></i></a>
                    <?php } else { ?>
                        <a class="wishlist-ribbon btn-remove-wishlist" data-post-id="<?php echo esc_attr( $acc_id ); ?>" data-label-add="<?php _e( 'add to wishlist', 'trav' ); ?>" data-label-remove="<?php _e( 'remove from wishlist', 'trav' ); ?>"><i class="fas fa-heart"></i></a>
                    <?php } ?>
                <?php } else { ?>
                    <a href="<?php echo $login_url; ?>" class="wishlist-ribbon btn-add-wishlist"><i class="fas fa-heart"></i></a>
                <?php } ?>

                <div class="featured-imgs">
                    <?php foreach ( $gallery_imgs as $gallery_img ) {
                        echo wp_get_attachment_image( $gallery_img, 'modern-gallery-thumb' );
                    } ?>                    
                </div>

                <div class="package-item-info">
                    <h3 class="package-item-name"><?php echo get_the_title( $acc_id ); ?></h3>
                    <p class="package-item-direction"><?php echo esc_html( $city ); ?>, <?php echo esc_html( $country ); ?></p>
                    <div class="review-rating">
                        <div class="five-stars-container">
                            <span class="five-stars" style="width: <?php echo $review * 20; ?>%;"></span>
                        </div>
                        <span class="review-nums"><?php echo ( ! empty( $review_count ) ) ? number_format( $review_count ) : '0'; ?> <?php echo esc_html__( 'reviews', 'trav' ); ?></span>
                    </div>

                    <div class="bottom-info">
                        <div class="price-field"><?php echo trav_get_price_field( $avg_price ); ?> <span>/ <?php echo esc_html__( 'Pre Night', 'trav' ); ?></span></div>
                        <a href="<?php echo get_permalink( $acc_id ); ?>" class="view-detail border-btn-third"><i class="fas fa-chevron-right"></i></a>
                    </div>
                </div>
            </div>
        </div>

    <?php
    } else {
    ?>
        <div class="single-travel-item-wrap">
            <div class="single-travel-item list-view-room">
                <?php if ( is_user_logged_in() ) {
                    $user_id = get_current_user_id();
                    $wishlist = get_user_meta( $user_id, 'wishlist', true );
                    if ( empty( $wishlist ) ) $wishlist = array();
                    if ( ! in_array( trav_acc_org_id( $acc_id ), $wishlist) ) { ?>
                        <a class="wishlist-ribbon btn-add-wishlist" data-post-id="<?php echo esc_attr( $acc_id ); ?>" data-label-add="<?php _e( 'add to wishlist', 'trav' ); ?>" data-label-remove="<?php _e( 'remove from wishlist', 'trav' ); ?>"><i class="fas fa-heart"></i></a>
                    <?php } else { ?>
                        <a class="wishlist-ribbon active btn-remove-wishlist" data-post-id="<?php echo esc_attr( $acc_id ); ?>" data-label-add="<?php _e( 'add to wishlist', 'trav' ); ?>" data-label-remove="<?php _e( 'remove from wishlist', 'trav' ); ?>"><i class="fas fa-heart"></i></a>
                    <?php } ?>
                <?php } else { ?>
                    <a href="<?php echo $login_url; ?>" class="wishlist-ribbon btn-add-wishlist"><i class="fas fa-heart"></i></a>
                <?php } ?>

                <div class="featured-imgs">
                    <?php foreach ( $gallery_imgs as $gallery_img ) {
                        echo wp_get_attachment_image( $gallery_img, 'modern-gallery-thumb' );
                    } ?>                    
                </div>

                <div class="package-item-info">
                    <div class="info-left-part">
                        <h3 class="package-item-name"><?php echo get_the_title( $acc_id ); ?></h3>
                        <p class="package-item-direction"><?php echo esc_html( $city ); ?>, <?php echo esc_html( $country ); ?></p>
                        <div class="review-rating">
                            <div class="five-stars-container">
                                <span class="five-stars" style="width: <?php echo $review * 20; ?>%;"></span>
                            </div>
                            <span class="review-nums"><?php echo ( ! empty( $review_count ) ) ? number_format( $review_count ) : '0'; ?> <?php echo esc_html__( 'reviews', 'trav' ); ?></span>
                        </div>
                        <p class="description"><?php echo esc_html( $brief ); ?></p>
                    </div>
                    <div class="info-right-part">
                        <div class="price-field"><?php echo trav_get_price_field( $avg_price ); ?> <span>/ <?php echo esc_html__( 'Pre Night', 'trav' ); ?></span></div>
                        <a href="<?php echo get_permalink( $acc_id ); ?>" class="view-detail border-btn-third"><?php echo esc_html__( 'See Details', 'trav' ); ?> <i class="fas fa-angle-double-right"></i></a>
                    </div>
                </div>
            </div>
        </div>
        
    <?php }    

}