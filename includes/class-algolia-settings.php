<?php

class Algolia_Settings {

	/**
	 * Algolia_Settings constructor.
	 */
	public function __construct() {
		add_option( 'algolia_application_id', '' );
		add_option( 'algolia_search_api_key', '' );
		add_option( 'algolia_api_key', '' );
		add_option( 'algolia_synced_indices_ids', array() );
		add_option( 'algolia_autocomplete_enabled', 'no' );
		add_option( 'algolia_autocomplete_config', array() );
		add_option( 'algolia_override_native_search', 'native' );
		add_option( 'algolia_index_name_prefix', 'wp_' );
		add_option( 'algolia_api_is_reachable', 'no' );
		add_option( 'algolia_powered_by_enabled', 'yes' );
	}

	/**
	 * @return string
	 */
	public function get_application_id() {
		if ( ! $this->is_application_id_in_config() ) {

			return (string) get_option( 'algolia_application_id', '' );
		}

		$this->assert_constant_is_non_empty_string( ALGOLIA_APPLICATION_ID, 'ALGOLIA_APPLICATION_ID' );

		return ALGOLIA_APPLICATION_ID;
	}

	/**
	 * @return string
	 */
	public function get_search_api_key() {
		if ( ! $this->is_search_api_key_in_config() ) {

			return (string) get_option( 'algolia_search_api_key', '' );
		}

		$this->assert_constant_is_non_empty_string( ALGOLIA_SEARCH_API_KEY, 'ALGOLIA_SEARCH_API_KEY' );

		return ALGOLIA_SEARCH_API_KEY;
	}

	/**
	 * @return string
	 */
	public function get_api_key() {
		if ( ! $this->is_api_key_in_config() ) {

			return (string) get_option( 'algolia_api_key', '' );
		}

		$this->assert_constant_is_non_empty_string( ALGOLIA_API_KEY, 'ALGOLIA_API_KEY' );

		return ALGOLIA_API_KEY;
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
		$ids = array();

		// Gather indices used in autocomplete experience.
		$config = $this->get_autocomplete_config();
		foreach ( $config as $index ) {
			if ( isset( $index['index_id'] ) ) {
				$ids[] = $index['index_id'];
			}
		}

		// Push index used in instantsearch experience.
		// Todo: we should allow users to index without using the shipped search UI or backend implementation.
		if ( $this->should_override_search_in_backend() || $this->should_override_search_with_instantsearch() ) {
			$ids[] = $this->get_native_search_index_id();
		}

		return (array) apply_filters( 'algolia_get_synced_indices_ids', $ids );
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
	 * @return string Can be 'native' 'backend' or 'instantsearch'.
	 */
	public function get_override_native_search() {
		$search_type = get_option( 'algolia_override_native_search', 'native' );

		// BC compatibility.
		if ( 'yes' === $search_type ) {
			$search_type = 'backend';
		}

		return $search_type;
	}

	/**
	 * @return bool
	 */
	public function should_override_search_in_backend() {
		return $this->get_override_native_search() === 'backend' || $this->should_override_search_with_instantsearch();
	}

	/**
	 * @return bool
	 */
	public function should_override_search_with_instantsearch() {
		$value = $this->get_override_native_search() === 'instantsearch';

		return (bool) apply_filters( 'algolia_should_override_search_with_instantsearch', $value );
	}

	/**
	 * @return string
	 */
	public function get_native_search_index_id() {
		return (string) apply_filters( 'algolia_native_search_index_id', 'searchable_posts' );
	}

	/**
	 * @return string
	 */
	public function get_index_name_prefix() {
		if ( ! $this->is_index_name_prefix_in_config() ) {

			return (string) get_option( 'algolia_index_name_prefix', 'wp_' );
		}

		$this->assert_constant_is_non_empty_string( ALGOLIA_INDEX_NAME_PREFIX, 'ALGOLIA_INDEX_NAME_PREFIX' );

		return ALGOLIA_INDEX_NAME_PREFIX;
	}

	/**
	 * Makes sure that constants are non empty strings.
	 * This makes sure that we fail early if the environment configuration is wrong.
	 *
	 * @param $value
	 * @param $constant_name
	 */
	protected function assert_constant_is_non_empty_string( $value, $constant_name ) {
		if ( ! is_string( $value ) ) {
			throw new RuntimeException( sprintf( 'Constant %s in wp-config.php should be a string, %s given.', $constant_name, gettype( $value ) ) );
		}

		if ( 0 === mb_strlen( $value ) ) {
			throw new RuntimeException( sprintf( 'Constant %s in wp-config.php cannot be empty.', $constant_name ) );
		}
	}

	/**
	 * @return bool
	 */
	public function is_application_id_in_config() {
		return defined( 'ALGOLIA_APPLICATION_ID' );
	}

	/**
	 * @return bool
	 */
	public function is_search_api_key_in_config() {
		return defined( 'ALGOLIA_SEARCH_API_KEY' );
	}

	/**
	 * @return bool
	 */
	public function is_api_key_in_config() {
		return defined( 'ALGOLIA_API_KEY' );
	}

	/**
	 * @return bool
	 */
	public function is_index_name_prefix_in_config() {
		return defined( 'ALGOLIA_INDEX_NAME_PREFIX' );
	}

	/**
	 * @return bool
	 */
	public function get_api_is_reachable() {
		$enabled = get_option( 'algolia_api_is_reachable', 'no' );

		return 'yes' === $enabled;
	}

	/**
	 * @param bool $flag
	 */
	public function set_api_is_reachable( $flag ) {
		$value = (bool) true === $flag ? 'yes' : 'no';
		update_option( 'algolia_api_is_reachable', $value );
	}

	/**
	 * @return bool
	 */
	public function is_powered_by_enabled() {
		$enabled = get_option( 'algolia_powered_by_enabled', 'yes' );

		return 'yes' === $enabled;
	}

	public function enable_powered_by() {
		update_option( 'algolia_powered_by_enabled', 'yes' );
	}

	public function disable_powered_by() {
		update_option( 'algolia_powered_by_enabled', 'no' );
	}
}
