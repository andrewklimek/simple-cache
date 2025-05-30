<?php
/*
Plugin Name: Mnml Cache
Plugin URI:  https://github.com/andrewklimek/mnml-contact/
Description: 
Author:      Andrew J Klimek
Author URI:  https://andrewklimek.com
License:     GPL2
License URI: https://www.gnu.org/licenses/gpl-2.0.html
Version: 1.0.0
Text Domain: mnml-cache
Domain Path: /languages
*/

defined( 'ABSPATH' ) || exit;

require_once __DIR__ . '/serve.php';
require_once __DIR__ . '/functions.php';
require_once __DIR__ . '/class-mc-notices.php';
require_once __DIR__ . '/class-mc-settings.php';
require_once __DIR__ . '/class-mc-config.php';
require_once __DIR__ . '/cloudflare.php';
MC_Notices::factory();
MC_Settings::factory();
$config = MC_Config::factory()->get();

if ( ! empty( $config['enable_caching'] ) ) {
	require_once __DIR__ . '/class-mc-cache.php';
	MC_Advanced_Cache::factory();
}

/**
 * Add settings link to plugin actions
 *
 * @param  array  $plugin_actions Each action is HTML.
 * @param  string $plugin_file Path to plugin file.
 * @return array
 */
function mc_filter_plugin_action_links( $links, $file ) {

	if ( 'mnml-cache/mnml-cache.php' === $file ) {// && current_user_can( 'manage_options' )// also could avoid hard-coding plugin name: basename( __DIR__ ) .'/'. basename( __FILE__ ) 
		$links = (array) $links;
		$links[] = '<a href="' . admin_url( 'options-general.php?page=mnml-cache' ) . '">Settings</a>';
	}

	return $links;
}
add_filter( 'plugin_action_links', 'mc_filter_plugin_action_links', 10, 2 );

/**
 * Clean up necessary files
 */
function mc_deactivate() {
	require_once __DIR__ . '/class-mc-cache.php';
	mc_cache_flush();
	MC_Advanced_Cache::factory()->clean_up();
	MC_Advanced_Cache::factory()->toggle_caching( false );
	MC_Config::factory()->clean_up();
}
add_action( 'deactivate_' . plugin_basename( __FILE__ ), 'mc_deactivate' );

/**
 * Would prefer to only delete cache when uninstalling
 * But it would really be a good idea to flush cache after a day of being deactivated
 * and I dont think I can because the functions wouldn't be included
 * or... can I include like this in an anonymous function?
 * https://github.com/WordPress/wordpress-develop/blob/0cb8475c0d07d23893b1d73d755eda5f12024585/src/wp-admin/includes/plugin.php#L1252
 *  or even trigger that uninstall function
 */
function mc_uninstall() {
	// MC_Advanced_Cache::factory()->clean_up();
	// MC_Advanced_Cache::factory()->toggle_caching( false );
	MC_Config::factory()->clean_up();
	mc_cache_flush();
}
// register_uninstall_hook( __FILE__, 'mc_uninstall' );

/**
 * Create config file
 */
function mc_activate() {
	MC_Config::factory()->write( array() );
	update_option( 'mnmlcache_notices', [ 'welcome' => 3 ], true );
}
add_action( 'activate_' . plugin_basename( __FILE__ ), 'mc_activate' );
