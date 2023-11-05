<?php
/**
 * Plugin Name: Word Search & Replace
 * Description: Add admin page in dashboard to search word in title, content, meta-title, meta-description
 * Version: 1.0
 * Author: pettrushkov
 * Author URI: https://denys.pp.ua/
 * License: GPLv2 or later
 * Text Domain: replace
 */

// require Replace class.
require 'class-replace.php';

// Run plugin on hook
add_action( 'plugins_loaded', 'run_replace_plugin' );
function run_replace_plugin() {
	$replace = new Replace();
	$replace->init();
}

/**
 * Is Yoast SEO plugin installed and activated
 * @return bool
 */
function is_yoast_activated() {
	return defined( 'WPSEO_VERSION' );
}