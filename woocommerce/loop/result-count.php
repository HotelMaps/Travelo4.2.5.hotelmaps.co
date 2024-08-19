<?php
/**
 * Result Count
 *
 * @see         https://docs.woocommerce.com/document/template-structure/
 * @author      WooThemes
 * @package     WooCommerce/Templates
 * @version     3.7.0
 */

if ( ! defined( 'ABSPATH' ) ) {
    exit; // Exit if accessed directly
}
?>

<h4 class="woocommerce-result-count search-results-title">
    <i class="soap-icon-search"></i>
    <?php
    $first    = ( $per_page * $current ) - $per_page + 1;
    $last     = min( $total, $per_page * $current );

    if ( $total <= $per_page || -1 === $per_page ) {
        /* translators: %d: total results */
        printf( _n( 'Showing the single result', 'Showing all <b>%d</b> results', $total, 'trav' ), $total );
    } else {
        /* translators: 1: first result 2: last result 3: total results */
        printf( _nx( 'Showing the single result', '<b>%3$d</b> results found', $total, '%1$d = first, %2$d = last, %3$d = total', 'trav' ), $first, $last, $total );
    }
    ?>
</h4>