<?php

/**
 * @wordpress-plugin
 * Plugin Name:       Algolia Search
 * Plugin URI:        https://community.algolia.com/wordpress
 * Description:       Algolia Search plugin for WordPress is a drop in replacement for WordPress search. It also provides an optional "as you type" auto-complete experience.
 * Version:           0.2.6
 * Author:            Algolia
 * Author URI:        https://www.algolia.com/
 * License:           MIT
 * License URI:       https://opensource.org/licenses/MIT
 * Text Domain:       algolia
 * Domain Path:       /languages/
 */

// Nothing to see here if not loaded in WP context.
if ( ! defined( 'WPINC' ) ) {
	die;
}

// Check for required PHP version.
if ( version_compare( PHP_VERSION, '5.3', '<' ) ) {
	exit( sprintf( esc_html__( 'Algolia plugin requires PHP 5.3 or higher. You’re still on %s.', 'algolia' ), esc_html( PHP_VERSION ) ) );
}

// Check for required WordPress version.
global $wp_version;
if ( version_compare( $wp_version, '3.7.14', '<' ) ) {
	exit( sprintf( esc_html__( 'Algolia plugin requires at least WordPress in version 3.7.14., You are on %s', 'algolia' ) , esc_html( $wp_version ) ) );
}

// The Algolia Search plugin version.
define( 'ALGOLIA_VERSION', '0.2.6' );

if ( ! defined( 'ALGOLIA_PATH' ) ) {
	define( 'ALGOLIA_PATH', plugin_dir_path( __FILE__ ) );
}

/**
 * I18n.
 */
function algolia_load_textdomain() {
	load_plugin_textdomain( 'algolia', false, plugin_basename( dirname( __FILE__ ) ) . '/languages/' );
}
add_action( 'plugins_loaded', 'algolia_load_textdomain' );

/**
 * The code that runs during plugin activation.
 * This action is documented in includes/class-algolia-activator.php.
 */
function activate_algolia() {
	require_once ALGOLIA_PATH . 'includes/class-algolia-activator.php';
	Algolia_Activator::activate();
}

/**
 * The code that runs during plugin deactivation.
 * This action is documented in includes/class-algolia-deactivator.php.
 */
function deactivate_algolia() {
	require_once ALGOLIA_PATH . 'includes/class-algolia-deactivator.php';
	Algolia_Deactivator::deactivate();
}

register_activation_hook( __FILE__, 'activate_algolia' );
register_deactivation_hook( __FILE__, 'deactivate_algolia' );

require_once ALGOLIA_PATH . 'classmap.php';

$algolia = new Algolia_Plugin( plugin_dir_path( __FILE__ ), plugin_dir_url( __FILE__ ) );
