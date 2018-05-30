<?php
/**
 * Plugin Name:PWA Ready.
 * Description: Make your theme PWA ready.
 * Author: Sagar Bhatt
 * Author URI: https://github.com/sagarkbhatt/
 * Version: 0.1
 * License: GPLv2+
 * License URI: http://www.gnu.org/licenses/gpl-2.0.txt
 * Text Domain: pwa-ready
 *
 * Copyright (c) 2017 Sagar Bhatt
 *
 * This program is free software; you can redistribute it and/or modify
 * it under the terms of the GNU General Public License, version 2 or, at
 * your discretion, any later version, as published by the Free
 * Software Foundation.
 *
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License
 * along with this program; if not, write to the Free Software
 * Foundation, Inc., 51 Franklin St, Fifth Floor, Boston, MA 02110-1301 USA
 *
 * @package Pwa_Ready
 */

if ( ! defined( 'ABSPATH' ) ) {
	return;
}

if ( ! defined( 'PWA_READY_VERSION' ) ) {
	define( 'PWA_READY_VERSION', '0.1' );
}

if ( ! defined( 'PWA_READY_DIR_URL' ) ) {
	define( 'PWA_READY_DIR_URL', plugin_dir_url( __FILE__ ) );
}

if ( ! defined( 'PWA_READY_DIR' ) ) {
	define( 'PWA_READY_DIR', __DIR__ );
}


require_once PWA_READY_DIR . '/class-service-worker.php';
