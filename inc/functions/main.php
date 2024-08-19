<?php
if ( ! defined( 'ABSPATH' ) ) exit; // Exit if accessed directly

require_once TRAV_INC_DIR . '/lib/class-tgm-plugin-activation.php';
include_once TRAV_INC_DIR . '/lib/multiple_sidebars.php';
require_once TRAV_INC_DIR . '/lib/payment/main.php';
require_once TRAV_INC_DIR . '/lib/scssphp/scss.inc.php';
require_once TRAV_INC_DIR . '/lib/scssphp/compiler.scss.php';
require_once TRAV_INC_DIR . '/lib/metabox-custom/main.php';

require_once dirname( __FILE__ ) . '/db.php';
require_once dirname( __FILE__ ) . '/functions.php';
require_once dirname( __FILE__ ) . '/modern-functions.php';
require_once dirname( __FILE__ ) . '/user.php';
require_once dirname( __FILE__ ) . '/custom-post-types.php';
require_once dirname( __FILE__ ) . '/currency.php';
require_once dirname( __FILE__ ) . '/wpml.php';
require_once dirname( __FILE__ ) . '/metaboxes.php';
require_once dirname( __FILE__ ) . '/side-bar.php';
require_once dirname( __FILE__ ) . '/widget.php';
require_once dirname( __FILE__ ) . '/custom-menu/custom-menu.php';


if ( trav_get_current_style() == 'modern' ) {
	require_once dirname( __FILE__ ) . '/modern-taxonomy-meta.php';
	require_once dirname( __FILE__ ) . '/shortcode/modern-init.php';
} else {
	require_once dirname( __FILE__ ) . '/taxonomy-meta.php';
	require_once dirname( __FILE__ ) . '/shortcode/init.php';
}

//actions
add_action( 'switch_theme', 'trav_switch_theme' );
add_action( 'after_switch_theme', 'trav_after_switch_theme' );
add_action( 'after_setup_theme', 'trav_after_setup_theme' );
add_action( 'after_setup_theme', 'trav_remove_admin_bar' );
add_action( 'init', 'trav_init' );
add_action( 'wp_enqueue_scripts', 'trav_enqueue_scripts' );
add_action( 'get_header', 'trav_init_currency' );
add_action( 'wp_footer', 'trav_inline_script' );
add_action( 'user_register', 'trav_user_register' );
// add_action( 'wp_logout','trav_logout_page');
add_action( 'login_form_register','trav_register_form');
add_action( 'wp_login_failed', 'trav_login_failed' );
add_action( 'wp_head', 'trav_count_post_views' );
add_action( 'tgmpa_register', 'trav_register_required_plugins' );
add_action( 'comment_form_before_fields', 'trav_comment_form_before_fields' );
add_action( 'comment_form_after_fields', 'trav_comment_form_after_fields' );
add_action( 'draft_to_pending', 'trav_notify_admin_for_pending' );
add_action( 'auto-draft_to_pending', 'trav_notify_admin_for_pending' );
add_action( 'admin_menu', 'trav_remove_redux_menu',12 );

remove_action( 'admin_enqueue_scripts', 'wp_auth_check_load' );

//filters
add_filter( 'wp_title', 'trav_wp_title', 10, 2 );
add_filter( 'template_include', 'trav_template_chooser' );
add_filter( 'posts_request', 'trav_posts_request_filter' );
add_filter( 'authenticate', 'trav_authenticate', 1, 3);
add_filter( 'user_contactmethods', 'trav_modify_contact_methods' );
add_filter( 'next_posts_link_attributes', 'trav_next_posts_link_attributes' );
add_filter( 'pre_get_posts','trav_pre_get_posts' );
add_filter( 'comments_open', 'trav_disable_comments', 10 , 2 );
add_filter( 'comment_form_default_fields', 'trav_comment_form_default_fields', 10 , 2 );
add_filter( 'the_content_more_link', 'trav_modify_read_more_link' );
add_filter( 'pre_get_posts', 'trav_posts_for_current_author' );
add_filter( 'wp_dropdown_users', 'trav_author_override' );
add_filter( 'body_class', 'trav_body_class' );
add_filter( 'rwmb_google_maps_url', 'trav_google_map_url' );
add_filter( 'widget_data_export', 'modify_widget_data_export', 30, 2 );