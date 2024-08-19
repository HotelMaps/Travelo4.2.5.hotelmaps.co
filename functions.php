<?php
if ( ! session_id() ) {
	session_start();
}

//constants
define( 'TRAV_VERSION', '4.2.4' );
define( 'TRAV_DB_VERSION', '3.2' );
define( 'TRAV_TEMPLATE_DIRECTORY_URI', get_template_directory_uri() );
define( 'TRAV_DIR', get_template_directory() );
define( 'TRAV_INC_DIR', get_template_directory() . '/inc' );
define( 'TRAV_IMAGE_URL', TRAV_TEMPLATE_DIRECTORY_URI . '/images' );
define( 'TRAV_TAX_META_DIR_URL', TRAV_TEMPLATE_DIRECTORY_URI . '/inc/lib/tax-meta-class/' );

global $wpdb;
define( 'TRAV_ACCOMMODATION_VACANCIES_TABLE', $wpdb->prefix . 'trav_accommodation_vacancies' );
define( 'TRAV_ACCOMMODATION_BOOKINGS_TABLE', $wpdb->prefix . 'trav_accommodation_bookings' );
define( 'TRAV_CURRENCIES_TABLE', $wpdb->prefix . 'trav_currencies' );
define( 'TRAV_REVIEWS_TABLE', $wpdb->prefix . 'trav_reviews' );
define( 'TRAV_MODE', 'product' );
define( 'TRAV_TOUR_SCHEDULES_TABLE', $wpdb->prefix . 'trav_tour_schedule' );
define( 'TRAV_TOUR_BOOKINGS_TABLE', $wpdb->prefix . 'trav_tour_bookings' );
define( 'TRAV_CAR_BOOKINGS_TABLE', $wpdb->prefix . 'trav_car_bookings' );
define( 'TRAV_CRUISE_SCHEDULES_TABLE', $wpdb->prefix . 'trav_cruise_schedules' );
define( 'TRAV_CRUISE_BOOKINGS_TABLE', $wpdb->prefix . 'trav_cruise_bookings' );
define( 'TRAV_CRUISE_VACANCIES_TABLE', $wpdb->prefix . 'trav_cruise_vacancies' );
define( 'TRAV_FLIGHT_BOOKINGS_TABLE', $wpdb->prefix . 'trav_flight_bookings' );

define( 'MAX_KID_AGE', 12 );
// define( 'TRAV_MODE', 'dev' );

// require file to woocommerce integration
require_once( TRAV_INC_DIR . '/functions/woocommerce/woocommerce.php' );

// get option
// $trav_options = get_option( 'travelo' );

if ( ! isset( $redux_demo ) ) {
    require_once( dirname( __FILE__ ) . '/inc/lib/redux-framework/config.php' );
}

//require files
require_once( TRAV_INC_DIR . '/functions/main.php' );
if ( trav_get_current_style() == 'modern' ) {
	require_once( TRAV_INC_DIR . '/functions/js_composer/modern-init.php' );
} else {
	require_once( TRAV_INC_DIR . '/functions/js_composer/init.php' );
}

require_once( TRAV_INC_DIR . '/admin/main.php');
require_once( TRAV_INC_DIR . '/frontend/accommodation/main.php');
require_once( TRAV_INC_DIR . '/frontend/tour/main.php');
require_once( TRAV_INC_DIR . '/frontend/car/main.php');
require_once( TRAV_INC_DIR . '/frontend/cruise/main.php');
require_once( TRAV_INC_DIR . '/frontend/flight/main.php');

// Content Width
if (!isset( $content_width )) $content_width = 1000;

// Translation
load_theme_textdomain('trav', get_template_directory() . '/languages');

//theme supports
add_theme_support( 'automatic-feed-links' );
add_theme_support( 'post-thumbnails' );
add_theme_support( 'woocommerce' );
add_image_size( 'list-thumb', 230, 160, true );
add_image_size( 'gallery-thumb', 270, 160, true );
add_image_size( 'biggallery-thumb', 500, 300, true );
add_image_size( 'widget-thumb', 64, 64, true );
add_image_size( 'slider-gallery', 900, 500, true );
add_image_size( 'location-gallery', 285, 370, true );
add_image_size( 'modern-gallery-thumb', 258, 183, true );
add_image_size( 'modern-map-thumb', 200, 200, true );
add_image_size( 'modern-blog-thumb', 800, 430, true );
add_image_size( 'modern-post-thumb', 1800, 790, true );
add_image_size( 'related-post-thumb', 680, 480, true );
add_image_size( 'member-thumb', 600, 600, true );
//add_image_size( 'map-thumb', 280, 140, true );
//add_filter('deprecated_constructor_trigger_error', '__return_false');