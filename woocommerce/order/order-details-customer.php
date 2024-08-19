<?php
/**
 * Order Customer Details
 *
 * @see 	https://docs.woocommerce.com/document/template-structure/
 * @author  WooThemes
 * @package WooCommerce/Templates
 * @version 3.4.4
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}
?>
<h2><?php _e( 'Customer Details', 'woocommerce' ); ?></h2>

<dl class="term-description">
	<?php 
	if ( $order->get_billing_email() ) :
		echo sprintf('<dt>%s</dt><dd>%s</dd>', __( 'Email:', 'woocommerce' ), esc_html( $order->get_billing_email() ));
	endif;

	if ( $order->get_billing_phone() ) :
		echo sprintf('<dt>%s</dt><dd>%s</dd>', __( 'Phone:', 'woocommerce' ), esc_html( $order->get_billing_phone() ));
	endif; ?>

	<?php do_action( 'woocommerce_order_details_after_customer_details', $order ); ?>

	<dt><strong><?php _e( 'Billing Address', 'woocommerce' ); ?></strong></dt><dd></dd>
	<?php
		if ( $billing_first_name = $order->get_billing_first_name() ) { 
			echo sprintf('<dt>%s:</dt><dd>%s</dd>', __( 'First Name:', 'trav' ), esc_html( $billing_first_name ));
		}
		if ( $billing_last_name = $order->get_billing_last_name() ) { 
			echo sprintf('<dt>%s:</dt><dd>%s</dd>', __( 'Last Name:', 'trav' ), esc_html( $billing_last_name ));
		}

		$billing_address_1 = $order->get_billing_address_1();
		$billing_address_2 = $order->get_billing_address_2();
		if ( $billing_address_1 || $billing_address_2 ) { 
			$label = __( 'Address', 'trav' );
			$value = '';

			if ( $billing_address_1 ) { 
				$value .= esc_html( $billing_address_1 );
			}
			if ( $billing_address_2 ) { 
				$value .= ', ' . esc_html( $billing_address_2 ) ;
			}

			echo sprintf('<dt>%s:</dt><dd>%s</dd>', $label, $value);
		}

		$billing_city = $order->get_billing_city();
		$billing_state = $order->get_billing_state();
		if ( $billing_city || $billing_state ) { 
			$label = __( 'Town / City', 'trav' );
			$value = '';

			if ( $billing_state ) { 
				$value .= esc_html( $billing_state );
			}
			if ( $billing_city ) { 
				$value .= ', ' . esc_html( $billing_city );
			}

			echo sprintf('<dt>%s:</dt><dd>%s</dd>', $label, $value);
		}
		
		if ( $billing_postcode = $order->get_billing_postcode() ) { 
			echo sprintf('<dt>%s:</dt><dd>%s</dd>', __( 'Zip Code', 'trav' ), esc_html( $billing_postcode ));
		}
		if ( $billing_country = $order->get_billing_country() ) { 
			$billing_country = WC()->countries->countries[ $billing_country ];
			echo sprintf('<dt>%s:</dt><dd>%s</dd>', __( 'Country', 'trav' ), esc_html( $billing_country ));
		}
	?>

	<?php if ( ! wc_ship_to_billing_address_only() && $order->needs_shipping_address() ) : ?>

		<dt><strong><?php _e( 'Shipping Address', 'woocommerce' ); ?></strong></dt><dd></dd>
		<?php
			if ( $shipping_first_name = $order->get_shipping_first_name() ) { 
				echo sprintf('<dt>%s:</dt><dd>%s</dd>', __( 'First Name:', 'trav' ), esc_html( $shipping_first_name ));
			}
			if ( $shipping_last_name = $order->get_shipping_last_name() ) { 
				echo sprintf('<dt>%s:</dt><dd>%s</dd>', __( 'Last Name:', 'trav' ), esc_html( $shipping_last_name ));
			}
			if ( $shipping_address_1 = $order->get_shipping_address_1() || $shipping_address_2 = $order->get_shipping_address_2() ) { 
				$label = __( 'Address', 'trav' );
				$value = '';
				if ( $shipping_address_1 ) { 
					$value .= esc_html( $shipping_address_1 );
				}
				if ( $shipping_address_2 ) { 
					$value .= ', ' . esc_html( $shipping_address_2 ) ;
				}
				echo sprintf('<dt>%s:</dt><dd>%s</dd>', $label, $value);
			}
			if ( $shipping_city = $order->get_shipping_city() || $shipping_state = $order->get_shipping_state() ) { 
				$label = __( 'Town / City', 'trav' );
				$value = '';
				if ( $shipping_state ) { 
					$value .= esc_html( $shipping_state );
				}
				if ( $shipping_city ) { 
					$value .= ', ' . esc_html( $shipping_city );
				}
				echo sprintf('<dt>%s:</dt><dd>%s</dd>', $label, $value);
			}
			if ( $shipping_postcode = $order->get_shipping_postcode() ) { 
				echo sprintf('<dt>%s:</dt><dd>%s</dd>', __( 'Zip Code', 'trav' ), esc_html( $shipping_postcode ));
			}
			if ( $shipping_country = $order->get_shipping_country() ) { 
				$shipping_country = WC()->countries->countries[ $shipping_country ];
				echo sprintf('<dt>%s:</dt><dd>%s</dd>', __( 'Country', 'trav' ), esc_html( $shipping_country ));
			}
		?>

	<?php endif; ?>

</dl>
