<?php
/*
Plugin Name: Easy Instagram
Plugin URI:
Description: Display one or more Instagram images by user id or tag
Version: 2.0
Author: VeloMedia
Author URI: http://www.velomedia.com
Licence:
*/
if ( ! defined( 'ABSPATH' ) ) exit;

require_once 'include/Instagram-PHP-API/Instagram.php';
require_once 'include/class-easy-instagram.php';
require_once 'include/class-easy-instagram-widget.php';

add_action( 'admin_menu', array( 'Easy_Instagram', 'admin_menu' ) );
add_action( 'init', array( 'Easy_Instagram', 'register_scripts_and_styles' ) );
add_action( 'wp_footer', array( 'Easy_Instagram', 'enqueue_scripts_and_styles' ) );
add_action( 'admin_init', array( 'Easy_Instagram', 'admin_init' ) );

add_action( 'wp_ajax_easy_instagram_content', array( 'Easy_Instagram', 'generate_content_ajax' ) );
add_action( 'wp_ajax_nopriv_easy_instagram_content', array( 'Easy_Instagram', 'generate_content_ajax' ) );

add_action( 'init', array( 'Easy_Instagram', 'init' ) );

add_action( 'widgets_init', create_function( '', 'register_widget( "Easy_Instagram_Widget" );' ) );

register_activation_hook( __FILE__, array( 'Easy_Instagram', 'plugin_activation' ) );
register_deactivation_hook( __FILE__, array( 'Easy_Instagram', 'plugin_deactivation' ) );

add_action( 'easy_instagram_clear_cache_event', array( 'Easy_Instagram', 'clear_cache_event_action' ) );

add_shortcode( 'easy-instagram', array( 'Easy_Instagram', 'shortcode' ) );

//add_filter( 'cron_schedules', array( 'Easy_Instagram', 'debug_cron' ) );

//=============================================================================

define( 'EASY_INSTAGRAM_PLUGIN_PATH', dirname( __FILE__ ) );

load_plugin_textdomain( 'Easy_Instagram', false, dirname( plugin_basename( __FILE__ ) ) . '/languages/' );
