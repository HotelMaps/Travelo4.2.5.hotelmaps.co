<?php
/**
 * Show options for ordering
 *
 * @see         https://docs.woocommerce.com/document/template-structure/
 * @author      WooThemes
 * @package     WooCommerce/Templates
 * @version     3.6.0
 */

if ( ! defined( 'ABSPATH' ) ) {
    exit; // Exit if accessed directly
}

$default_view = ! empty( $_GET['view'] )? $_GET['view'] : 'grid';

?>
<form class="woocommerce-ordering sort-by-section clearfix box" method="get">
    <h4 class="sort-by-title block-sm"><?php _e( 'Sort results by:', 'trav' ); ?></h4>

    <div class="sort-bar clearfix block-sm selector">
        <select name="orderby" class="orderby">
            <?php foreach ( $catalog_orderby_options as $id => $name ) : ?>
                <option value="<?php echo esc_attr( $id ); ?>" <?php selected( $orderby, $id ); ?>><?php echo esc_html( $name ); ?></option>
            <?php endforeach; ?>
        </select>
    </div>

    <ul class="swap-tiles clearfix block-sm">
        <?php
            $views = array( 
                'list' => __( 'List View', 'trav' ),
                'grid' => __( 'Grid View', 'trav' ),
            );
            foreach( $views as $view => $label ) {
                $active = ( $view == $default_view )?' active':'';
                echo '<li class="swap-' . esc_attr( $view . $active ) . '">';
                echo '<a href="' . esc_url( add_query_arg( array( 'view' => $view ) ) ) . '" title="' . esc_attr( $label ) . '" data-view="' . $view . '"><i class="soap-icon-' . esc_attr( $view ) . '"></i></a>';
                echo '</li>';
            }
        ?>
    </ul>
    <?php
        // Keep query string vars intact
        foreach ( $_GET as $key => $val ) {
            if ( 'orderby' === $key || 'submit' === $key ) {
                continue;
            }
            if ( is_array( $val ) ) {
                foreach( $val as $innerVal ) {
                    echo '<input type="hidden" name="' . esc_attr( $key ) . '[]" value="' . esc_attr( $innerVal ) . '" />';
                }
            } else {
                echo '<input type="hidden" name="' . esc_attr( $key ) . '" value="' . esc_attr( $val ) . '" />';
            }
        }
    ?>
</form>
