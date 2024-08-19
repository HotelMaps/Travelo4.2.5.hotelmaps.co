<?php
/**
 * Cart Page
 *
 * @author      WooThemes
 * @package     WooCommerce/Templates
 * @version     3.8.0
 */

if ( ! defined( 'ABSPATH' ) ) {
    exit; // Exit if accessed directly
}

do_action( 'woocommerce_before_cart' ); ?>

<form class="woocommerce-cart-form" action="<?php echo esc_url( wc_get_cart_url() ); ?>" method="post">

<?php do_action( 'woocommerce_before_cart_table' ); ?>

<table class="shop_table shop_table_responsive cart woocommerce-cart-form__contents" cellspacing="0">
    <thead>
        <tr>
            <th class="product-remove">&nbsp;</th>
            <th class="product-name" colspan="2"><?php esc_html_e( 'Product', 'woocommerce' ); ?></th>
            <th class="product-order"><?php esc_html_e( 'Order Detail', 'woocommerce' ); ?></th>
            <th class="product-price"><?php esc_html_e( 'Price', 'woocommerce' ); ?></th>
            <th class="product-quantity"><?php esc_html_e( 'Quantity', 'woocommerce' ); ?></th>
            <th class="product-subtotal"><?php esc_html_e( 'Total', 'woocommerce' ); ?></th>
        </tr>
    </thead>
    <tbody>
        <?php do_action( 'woocommerce_before_cart_contents' ); ?>

        <?php
        foreach ( WC()->cart->get_cart() as $cart_item_key => $cart_item ) {
            $_product     = apply_filters( 'woocommerce_cart_item_product', $cart_item['data'], $cart_item, $cart_item_key );
            $product_id   = apply_filters( 'woocommerce_cart_item_product_id', $cart_item['product_id'], $cart_item, $cart_item_key );

            if ( $_product && $_product->exists() && $cart_item['quantity'] > 0 && apply_filters( 'woocommerce_cart_item_visible', true, $cart_item, $cart_item_key ) ) {
                $attributes = $_product->get_attributes();
                $booking_data = array();

                if ( isset( $attributes['travelo-booking-id'] ) || isset( $attributes['trav-booking-no'] ) ) {
                    if ( isset( $attributes['trav-booking-no'] ) ) { 
                        $booking_no = $attributes['trav-booking-no']['value'];
                        $pin_code = $attributes['trav-pin-code']['value'];
                    } else if ( isset( $attributes['travelo-booking-id'] ) ) { 
                        $booking_no = $attributes['travelo-booking-id']['value'];
                        $pin_code = $attributes['travelo-pin-code']['value'];
                    }
                    $property_type = '';

                    if ( has_term( 'accommodation', 'product_cat', $product_id ) ) {
                        $property_type = 'accommodation';
                        $booking_data = trav_acc_get_booking_data( $booking_no, $pin_code );
                    } elseif ( has_term( 'tour', 'product_cat', $product_id ) ) {
                        $property_type = 'tour';
                        $booking_data = trav_tour_get_booking_data( $booking_no, $pin_code );
                    }
                }
                if ( ! empty( $booking_data ) ) {
                ?>
                    <tr class="woocommerce-cart-form__cart-item <?php echo esc_attr( apply_filters( 'woocommerce_cart_item_class', 'cart_item', $cart_item, $cart_item_key ) ); ?>">

                        <td class="product-remove">
                            <?php
                                echo apply_filters( 'woocommerce_cart_item_remove_link', sprintf( '<a href="%s" class="remove" title="%s">&times;</a>', esc_url( wc_get_cart_remove_url( $cart_item_key ) ), __( 'Remove this item', 'woocommerce' ) ), $cart_item_key );
                            ?>
                        </td>

                        <td class="product-thumbnail" data-title="<?php esc_html_e( 'Product', 'woocommerce' ); ?>">
                            <?php if ( $property_type == 'accommodation' ) { ?>
                                <a href="<?php echo get_permalink( $booking_data['accommodation_id'] ); ?>">
                                    <?php echo get_the_post_thumbnail( $booking_data['accommodation_id'], 'thumbnail' ); ?>
                                </a>
                            <?php } elseif ( $property_type == 'tour' ) { ?>
                                <a href="<?php echo get_permalink( $booking_data['tour_id'] ); ?>">
                                    <?php echo get_the_post_thumbnail( $booking_data['tour_id'], 'thumbnail' ); ?>
                                </a>
                            <?php } ?>
                        </td>

                        <td class="product-name" data-title="<?php esc_html_e( 'Product', 'woocommerce' ); ?>">
                            <?php if ( $property_type == 'accommodation' ) { ?>
                                <a href="<?php echo get_permalink( $booking_data['accommodation_id'] ); ?>">
                                    <h5 class="product-title">
                                        <?php echo get_the_title( $booking_data['accommodation_id'] ); ?>
                                        <small><?php echo get_the_title( $booking_data['room_type_id'] ); ?></small>
                                        <?php // echo esc_html( trav_get_day_interval( $booking_data['date_from'], $booking_data['date_to'] ) . ' ' . __( 'Nights', 'trav' ) ); ?>
                                    </h5>
                                </a>
                            <?php } elseif ( $property_type == 'tour' ) { ?>
                                <a href="<?php echo get_permalink( $booking_data['tour_id'] ); ?>">
                                    <h5 class="product-title">
                                        <?php echo get_the_title( $booking_data['tour_id'] ); ?>
                                        <small><?php echo esc_html( trav_tour_get_schedule_type_title( $booking_data['tour_id'], $booking_data['st_id'] ) ); ?></small>
                                    </h5>
                                </a>
                            <?php }
                                // Backorder notification
                                if ( $_product->backorders_require_notification() && $_product->is_on_backorder( $cart_item['quantity'] ) )
                                    echo '<p class="backorder_notification">' . __( 'Available on backorder', 'woocommerce' ) . '</p>';
                            ?>
                        </td>

                        <td class="product-order" data-title="<?php esc_html_e( 'Order Detail', 'woocommerce' ); ?>">
                            <dl class="other-details">
                                <?php
                                    if ( $property_type == 'accommodation' ) {
                                        
                                        $fields = array(
                                            'adults' => __( 'adults', 'trav'),
                                            'kids' => __( 'children', 'trav'),
                                            'rooms' => __( 'rooms', 'trav'),
                                            'date_from' => __( 'from', 'trav'),
                                            'date_to' => __( 'to', 'trav'),
                                        );

                                        foreach( $fields as $key => $value ) {
                                            if ( ! empty( $booking_data[ $key ] ) ) {
                                                echo '<dt class="feature">' . $value . ':</dt><dd class="value">' . esc_html( $booking_data[ $key ] ) . '</dd>';
                                            }
                                        }

                                    } elseif ( $property_type == 'tour' ) {

                                        $fields = array(
                                            'adults' => __( 'adults', 'trav'),
                                            'kids' => __( 'children', 'trav'),
                                            'tour_data' => __( 'tour data', 'trav'),
                                        );

                                        foreach( $fields as $key => $value ) {
                                            if ( ! empty( $booking_data[ $key ] ) ) {
                                                echo '<dt class="feature">' . $value . ':</dt><dd class="value">' . esc_html( $booking_data[ $key ] ) . '</dd>';
                                            }
                                        }

                                    }
                                ?>
                            </dl>
                        </td>

                        <td class="product-price" data-title="<?php esc_html_e( 'Price', 'woocommerce' ); ?>">
                            <?php
                                echo apply_filters( 'woocommerce_cart_item_price', WC()->cart->get_product_price( $_product ), $cart_item, $cart_item_key );
                            ?>
                        </td>

                        <td class="product-quantity" data-title="<?php esc_html_e( 'Quantity', 'woocommerce' ); ?>">
                            <?php
                                if ( $_product->is_sold_individually() ) {
                                    $product_quantity = sprintf( '1 <input type="hidden" name="cart[%s][qty]" value="1" />', $cart_item_key );
                                } else {
                                    $product_quantity = woocommerce_quantity_input( array(
                                        'input_name'  => "cart[{$cart_item_key}][qty]",
                                        'input_value' => $cart_item['quantity'],
                                        'max_value'   => $_product->get_max_purchase_quantity(),
                                        'min_value'   => '0'
                                    ), $_product, false );
                                }

                                echo apply_filters( 'woocommerce_cart_item_quantity', $product_quantity, $cart_item_key );
                            ?>
                        </td>

                        <td class="product-subtotal" data-title="<?php esc_html_e( 'Total', 'woocommerce' ); ?>">
                            <?php
                                echo apply_filters( 'woocommerce_cart_item_subtotal', WC()->cart->get_product_subtotal( $_product, $cart_item['quantity'] ), $cart_item, $cart_item_key );
                            ?>
                        </td>
                    </tr>
                <?php
                } else { 
                    $product_permalink = apply_filters( 'woocommerce_cart_item_permalink', $_product->is_visible() ? $_product->get_permalink( $cart_item ) : '', $cart_item, $cart_item_key );
                    ?>
                    <tr class="woocommerce-cart-form__cart-item <?php echo esc_attr( apply_filters( 'woocommerce_cart_item_class', 'cart_item', $cart_item, $cart_item_key ) ); ?>">

                        <td class="product-remove">
                            <?php
                                echo apply_filters( 'woocommerce_cart_item_remove_link', sprintf( 
                                    '<a href="%s" class="remove" aria-label="%s" data-product_id="%s" data-product_sku="%s">&times;</a>', 
                                    esc_url( wc_get_cart_remove_url( $cart_item_key ) ), 
                                    __( 'Remove this item', 'woocommerce' ),
                                    esc_attr( $product_id ),
                                    esc_attr( $_product->get_sku() )
                                ),  $cart_item_key );
                            ?>
                        </td>

                        <td class="product-thumbnail">
                            <?php
                            $thumbnail = apply_filters( 'woocommerce_cart_item_thumbnail', $_product->get_image(), $cart_item, $cart_item_key );

                            if ( ! $product_permalink ) {
                                echo $thumbnail;
                            } else {
                                printf( '<a href="%s">%s</a>', esc_url( $product_permalink ), $thumbnail );
                            }
                            ?>
                        </td>

                        <td class="product-name" data-title="<?php esc_attr_e( 'Product', 'woocommerce' ); ?>">
                            <?php
                                if ( ! $product_permalink ) {
                                    echo apply_filters( 'woocommerce_cart_item_name', $_product->get_name(), $cart_item, $cart_item_key ) . '&nbsp;';
                                } else {
                                    echo apply_filters( 'woocommerce_cart_item_name', sprintf( '<a href="%s">%s</a>', esc_url( $product_permalink ), $_product->get_name() ), $cart_item, $cart_item_key );
                                }

                                // Backorder notification
                                if ( $_product->backorders_require_notification() && $_product->is_on_backorder( $cart_item['quantity'] ) )
                                    echo '<p class="backorder_notification">' . esc_html__( 'Available on backorder', 'woocommerce' ) . '</p>';
                            ?>
                        </td>

                        <td class="product-order">
                            <?php 
                                // Meta data
                                echo wc_get_formatted_cart_item_data( $cart_item );
                            ?>
                        </td>

                        <td class="product-price" data-title="<?php esc_attr_e( 'Price', 'woocommerce' ); ?>">
                            <?php
                                echo apply_filters( 'woocommerce_cart_item_price', WC()->cart->get_product_price( $_product ), $cart_item, $cart_item_key );
                            ?>
                        </td>

                        <td class="product-quantity" data-title="<?php esc_attr_e( 'Quantity', 'woocommerce' ); ?>">
                            <?php
                                if ( $_product->is_sold_individually() ) {
                                    $product_quantity = sprintf( '1 <input type="hidden" name="cart[%s][qty]" value="1" />', $cart_item_key );
                                } else {
                                    $product_quantity = woocommerce_quantity_input( array(
                                        'input_name'    => "cart[{$cart_item_key}][qty]",
                                        'input_value'   => $cart_item['quantity'],
                                        'max_value'     => $_product->get_max_purchase_quantity(),
                                        'min_value'     => '0',
                                        'product_name'  => $_product->get_name(),
                                    ), $_product, false );
                                }

                                echo apply_filters( 'woocommerce_cart_item_quantity', $product_quantity, $cart_item_key, $cart_item );
                            ?>
                        </td>

                        <td class="product-subtotal" data-title="<?php esc_attr_e( 'Total', 'woocommerce' ); ?>">
                            <?php
                                echo apply_filters( 'woocommerce_cart_item_subtotal', WC()->cart->get_product_subtotal( $_product, $cart_item['quantity'] ), $cart_item, $cart_item_key );
                            ?>
                        </td>
                    </tr>
                    <?php
                }
            }
        }

        do_action( 'woocommerce_cart_contents' );
        ?>
        <tr>
            <td colspan="7" class="actions">

                <?php if ( wc_coupons_enabled() ) { ?>
                    <div class="coupon">

                        <label for="coupon_code"><?php _e( 'Coupon:', 'woocommerce' ); ?></label> <input type="text" name="coupon_code" class="input-text" id="coupon_code" value="" placeholder="<?php esc_attr_e( 'Coupon code', 'woocommerce' ); ?>" /> <input type="submit" class="button" name="apply_coupon" value="<?php esc_attr_e( 'Apply coupon', 'woocommerce' ); ?>" />

                        <?php do_action( 'woocommerce_cart_coupon' ); ?>

                    </div>
                <?php } ?>
                <button name="update_cart" class="icon-check" type="submit"><?php esc_attr_e( 'Update cart', 'woocommerce' ); ?></button>

                <?php do_action( 'woocommerce_cart_actions' ); ?>

                <?php wp_nonce_field( 'woocommerce-cart' ); ?>
            </td>
        </tr>

        <?php do_action( 'woocommerce_after_cart_contents' ); ?>
    </tbody>
</table>

<?php do_action( 'woocommerce_after_cart_table' ); ?>

</form>

<div class="cart-collaterals">
    <?php do_action( 'woocommerce_cart_collaterals' ); ?>
</div>

<?php do_action( 'woocommerce_after_cart' ); ?>
