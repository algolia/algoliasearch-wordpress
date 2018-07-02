<?php

/**
 * @wordpress-plugin
 * Plugin Name:       Search by Algolia – Instant & Relevant results
 * Plugin URI:        https://community.algolia.com/wordpress
 * Description:       Search by Algolia is the smartest way to improve search on your site. Autocomplete is included, along with full control over look, feel and relevance.
 * Version:           2.11.2
 * Author:            Algolia
 * Author URI:        https://www.algolia.com/
 * License:           MIT License, GNU General Public License v2.0
 * Text Domain:       algolia
 * Domain Path:       /languages/
 */

// Nothing to see here if not loaded in WP context.
if ( ! defined( 'WPINC' ) ) {
	die;
}

// Check for required PHP version.
if ( version_compare( PHP_VERSION, '5.3', '<' ) ) {
	/* translators: the placeholder always contains the plugin version. */
	exit( sprintf( esc_html__( 'Algolia plugin requires PHP 5.3 or higher. You’re still on %s.', 'algolia' ), esc_html( PHP_VERSION ) ) );
}

// Check for required WordPress version.
global $wp_version;
if ( version_compare( $wp_version, '3.7.14', '<' ) ) {
	/* translators: the placeholder always contains the plugin version. */
	exit( sprintf( esc_html__( 'Algolia plugin requires at least WordPress in version 3.7.14., You are on %s', 'algolia' ), esc_html( $wp_version ) ) );
}

// The Algolia Search plugin version.
define( 'ALGOLIA_VERSION', '2.11.2' );
define( 'ALGOLIA_PLUGIN_BASENAME', plugin_basename( __FILE__ ) );

if ( ! defined( 'ALGOLIA_PATH' ) ) {
	define( 'ALGOLIA_PATH', plugin_dir_path( __FILE__ ) );
}

/**
 * I18n.
 */
function algolia_load_textdomain() {
	$locale = apply_filters( 'plugin_locale', get_locale(), 'algolia' );

	load_textdomain( 'algolia', WP_LANG_DIR . '/algolia/algolia-' . $locale . '.mo' );
	load_plugin_textdomain( 'algolia', false, plugin_basename( dirname( __FILE__ ) ) . '/languages/' );
}

add_action( 'init', 'algolia_load_textdomain' );

require_once ALGOLIA_PATH . 'classmap.php';

$algolia = Algolia_Plugin::get_instance();

if ( defined( 'WP_CLI' ) && WP_CLI ) {
	include ALGOLIA_PATH . '/includes/class-algolia-cli.php';
	WP_CLI::add_command( 'algolia', new Algolia_CLI() );
}
