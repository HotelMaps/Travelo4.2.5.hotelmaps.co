<?php

/**
 * Theme Option Value Compile
 */
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

$body_font = trav_get_opt( 'body_font_family' );

if ( ! empty( $body_font['font-size'] ) ) {
	$body_font_size = $body_font['font-size'];
} else {
	$body_font_size = '16px';
}

if ( ! empty( $body_font['line-height'] ) ) {
	$body_line_height = intval( $body_font['line-height'] ) / intval($body_font_size );
} else {
	$body_line_height = 20 / intval($body_font_size );
}

?>

// RTL Setting
<?php if ( is_rtl() ) : ?>
$left: right;
$right: left;
<?php else : ?>
$left: left;
$right: right;
<?php endif; ?>

$site_primary_color: <?php echo trav_get_opt( 'site_primary_color', '#01b7f2' ); ?>;
$site_secondary_color: <?php echo trav_get_opt( 'site_secondary_color', '#fdb714' ); ?>;
$site_third_color: <?php echo trav_get_opt( 'site_third_color', '#72be2e' ); ?>;
$site_fourth_color: <?php echo trav_get_opt( 'site_fourth_color', '#f94068' ); ?>;

$body_font_family: '<?php echo ( ! empty( $body_font['font-family'] ) ) ? $body_font['font-family'] : 'Barlow'; ?>', sans-serif;
$body_font_size: <?php echo $body_font_size; ?>;
$body_font_weight: <?php echo ( ! empty( $body_font['font-weight'] ) ) ? ( $body_font['font-weight'] ) : '400'; ?>;
$body_line_height: <?php echo $body_line_height; ?>;
$body_font_color: <?php echo ( ! empty( $body_font['color'] ) ) ? $body_font['color'] : '#6c7578'; ?>;

$nav_menu_color: <?php echo trav_get_opt( 'nav_menu_color', '#6c7578' ); ?>;
$nav_menu_hover_color: <?php echo trav_get_opt( 'nav_menu_hover_color', '#1e2325' ); ?>;

$header_bg_color: <?php echo trav_get_opt( 'header_bg_clr', '#fff' ); ?>;
$sticky_header_bg_color: <?php echo trav_get_opt( 'sticky_header_bg_color', '#fff' ); ?>;

$website_margin: <?php echo trav_get_opt( 'website_margin', '50' ); ?>px;

$shadow_color: rgba(253, 183, 20, .5);
<?php
$logo_height_header = trav_get_opt( 'logo_height_header', false );
$sticky_logo_height = trav_get_opt( 'logo_height_chaser', false );
?>
$site_logo_height: <?php echo $logo_height_header ? intval( $logo_height_header['height'] ) : '39'; ?>px;
$sticky_logo_height: <?php echo $sticky_logo_height ? intval( $sticky_logo_height['height'] ) : '39'; ?>px;
