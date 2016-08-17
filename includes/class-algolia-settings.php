<?php


class Algolia_Settings
{
	/**
	 * Algolia_Settings constructor.
	 */
	public function __construct()
	{
		add_option( 'algolia_application_id', '' );
		add_option( 'algolia_search_api_key', '' );
		add_option( 'algolia_api_key', '' );
		add_option( 'algolia_synced_indices_ids', array() );
		add_option( 'algolia_autocomplete_enabled', 'no' );
		add_option( 'algolia_autocomplete_config', array() );
		add_option( 'algolia_override_native_search', 'no' );
		add_option( 'algolia_native_search_index_id', 'post' );
		add_option( 'algolia_index_name_prefix', 'wp_' );
		add_option( 'algolia_logging_enabled', 'no' );
	}

	/**
	 * @return string
	 */
	public function get_application_id() {
		return get_option( 'algolia_application_id', '' );
	}

	/**
	 * @return string
	 */
	public function get_search_api_key() {
		return get_option( 'algolia_search_api_key', '' );
	}

	/**
	 * @return string
	 */
	public function get_api_key() {
		return get_option( 'algolia_api_key', '' );
	}

	/**
	 * @return array
	 */
	public function get_post_types_blacklist() {
		$blacklist = (array) apply_filters( 'algolia_post_types_blacklist', array( 'nav_menu_item' ) );

		// Native WordPress.
		$blacklist[] = 'revision';

		// Native to Algolia Search plugin.
		$blacklist[] = 'algolia_task';
		$blacklist[] = 'algolia_log';

		// Native to WordPress VIP platform.
		$blacklist[] = 'kr_request_token';
		$blacklist[] = 'kr_access_token';
		$blacklist[] = 'deprecated_log';
		$blacklist[] = 'async-scan-result';
		$blacklist[] = 'scanresult';
		
		return array_unique( $blacklist );
	}

	/**
	 * @return array
	 */
	public function get_synced_indices_ids() {
		return (array) get_option( 'algolia_synced_indices_ids', array() );
	}

	/**
	 * @return array
	 */
	public function get_taxonomies_blacklist() {
		return (array) apply_filters( 'algolia_taxonomies_blacklist', array( 'nav_menu', 'link_category', 'post_format' ) );
	}

	/**
	 * @return string Can be 'yes' or 'no'.
	 */
	public function get_autocomplete_enabled() {
		return get_option( 'algolia_autocomplete_enabled', 'no' );
	}

	/**
	 * @return array
	 */
	public function get_autocomplete_config() {
		return (array) get_option( 'algolia_autocomplete_config', array() );
	}

	/**
	 * @return string Can be 'yes' or 'no'.
	 */
	public function get_override_native_search() {
		return get_option( 'algolia_override_native_search', 'no' );
	}

	/**
	 * @return bool
	 */
	public function get_native_search_index_id() {
		return (string) get_option( 'algolia_native_search_index_id', 'post' );
	}

	/**
	 * @return string
	 */
	public function get_index_name_prefix() {
		return (string) get_option( 'algolia_index_name_prefix', 'wp_' );
	}

	/**
	 * @return bool
	 */
	public function get_logging_enabled() {
		$enabled = get_option( 'algolia_logging_enabled', 'no' );
		
		return $enabled === 'yes';
	}

	/**
	 * @param bool $flag
	 */
	public function set_logging_enabled($flag) {
		$enabled = (bool) $flag === true ? 'yes' : 'no';
		
		update_option( 'algolia_logging_enabled', $enabled );
	}
}
