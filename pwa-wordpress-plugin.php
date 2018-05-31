<?php
/**
 * Plugin Name:PWA WordPress Plugin.
 * Description: WordPress Plugin to enable PWA features.
 * Author: rtCamp, Sagar Bhatt
 * Author URI: https://rtcamp.com/
 * Version: 0.1
 * License: GPLv2 or later
 * Text Domain: pwa-wp-plugin
 *
 * @package PWA_WP_Plugin
 */

if ( ! defined( 'ABSPATH' ) ) {
	return;
}

if ( ! defined( 'PWA_WP_PLUGIN_VERSION' ) ) {
	define( 'PWA_WP_PLUGIN_VERSION', '0.1' );
}

if ( ! defined( 'PWA_WP_PLUGIN_DIR_URL' ) ) {
	define( 'PWA_WP_PLUGIN_DIR_URL', plugin_dir_url( __FILE__ ) );
}

if ( ! defined( 'PWA_WP_PLUGIN_DIR' ) ) {
	define( 'PWA_WP_PLUGIN_DIR', __DIR__ );
}

require_once PWA_WP_PLUGIN_DIR . '/class-service-worker.php';
