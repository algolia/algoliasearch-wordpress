<?php

class Algolia_Plugin {

	const NAME = 'algolia';

	/**
	 * @var Algolia_Plugin
	 */
	static private $instance;

	/**
	 * @var Algolia_API
	 */
	protected $api;

	/**
	 * @var Algolia_Settings
	 */
	private $settings;

	/**
	 * @var Algolia_Autocomplete_Config
	 */
	private $autocomplete_config;

	/**
	 * @var array
	 */
	private $indices;

	/**
	 * @var array
	 */
	private $changes_watchers;

	/**
	 * @var Algolia_Template_Loader
	 */
	private $template_loader;

	/**
	 * @var Algolia_Compatibility
	 */
	private $compatibility;

	/**
	 * @return Algolia_Plugin
	 */
	public static function get_instance() {
		if ( null === self::$instance ) {
			self::$instance = new Algolia_Plugin();
		}

		return self::$instance;
	}

	/**
	 * Loads the plugin.
	 */
	private function __construct() {
		// Register the assets so that they can be used in other plugins outside of the context of the core features.
		add_action( 'wp_enqueue_scripts', array( $this, 'register_assets' ) );
		add_action( 'admin_enqueue_scripts', array( $this, 'register_assets' ) );

		$this->settings = new Algolia_Settings();

		$this->api = new Algolia_API( $this->settings );

		$this->compatibility = new Algolia_Compatibility();

		add_action( 'init', array( $this, 'load' ), 20 );
	}


	public function load() {
		if ( $this->api->is_reachable() ) {
			$this->load_indices();
			$this->override_wordpress_search();
			$this->autocomplete_config = new Algolia_Autocomplete_Config( $this );
			$this->template_loader     = new Algolia_Template_Loader( $this );
		}

		// Load admin or public part of the plugin.
		if ( is_admin() ) {
			new Algolia_Admin( $this );
		}
	}

	/**
	 * The name of the plugin used to uniquely identify it within the context of
	 * WordPress and to define internationalization functionality.
	 *
	 * @since     1.0.0
	 * @return    string    The name of the plugin.
	 */
	public function get_name() {
		return self::NAME;
	}

	/**
	 * Retrieve the version number of the plugin.
	 *
	 * @since     1.0.0
	 * @return    string    The version number of the plugin.
	 */
	public function get_version() {
		return ALGOLIA_VERSION;
	}

	/**
	 * @return Algolia_API
	 */
	public function get_api() {
		return $this->api;
	}

	/**
	 * @return Algolia_Settings
	 */
	public function get_settings() {
		return $this->settings;
	}

	/**
	 * Replaces native WordPress search results by Algolia ranked results.
	 */
	private function override_wordpress_search() {
		// Do not override native search if the feature is not enabled.
		if ( ! $this->settings->should_override_search_in_backend() ) {
			return;
		}

		$index_id = $this->settings->get_native_search_index_id();
		$index    = $this->get_index( $index_id );

		if ( null == $index ) {
			return;
		}

		new Algolia_Search( $index );
	}

	/**
	 * @return Algolia_Autocomplete_Config
	 */
	public function get_autocomplete_config() {
		return $this->autocomplete_config;
	}

	public function register_assets() {
		$suffix = defined( 'SCRIPT_DEBUG' ) && SCRIPT_DEBUG ? '' : '.min';

		// CSS.
		wp_register_style( 'algolia-autocomplete', plugin_dir_url( __FILE__ ) . '../css/algolia-autocomplete.css', array(), ALGOLIA_VERSION, 'screen' );
		wp_register_style( 'algolia-instantsearch', plugin_dir_url( __FILE__ ) . '../css/algolia-instantsearch.css', array(), ALGOLIA_VERSION, 'screen' );

		// JS.
		wp_register_script( 'algolia-search', plugin_dir_url( __FILE__ ) . '../js/algoliasearch/algoliasearch.jquery' . $suffix . '.js', array( 'jquery', 'underscore', 'wp-util' ), ALGOLIA_VERSION );
		wp_register_script( 'algolia-autocomplete', plugin_dir_url( __FILE__ ) . '../js/autocomplete.js/autocomplete' . $suffix . '.js', array( 'jquery', 'underscore', 'wp-util' ), ALGOLIA_VERSION );
		wp_register_script( 'algolia-autocomplete-noconflict', plugin_dir_url( __FILE__ ) . '../js/autocomplete-noconflict.js', array( 'algolia-autocomplete' ), ALGOLIA_VERSION );

		wp_register_script( 'algolia-instantsearch', plugin_dir_url( __FILE__ ) . '../js/instantsearch.js/instantsearch-preact' . $suffix . '.js', array( 'jquery', 'underscore', 'wp-util' ), ALGOLIA_VERSION );
	}

	/**
	 * @return array
	 */
	public function load_indices() {
		$synced_indices_ids = $this->settings->get_synced_indices_ids();

		$client            = $this->get_api()->get_client();
		$index_name_prefix = $this->settings->get_index_name_prefix();

		// Add a searchable posts index.
		$searchable_post_types = get_post_types(
			array(
				'exclude_from_search' => false,
			), 'names'
		);
		$searchable_post_types = (array) apply_filters( 'algolia_searchable_post_types', $searchable_post_types );
		$this->indices[]       = new Algolia_Searchable_Posts_Index( $searchable_post_types );

		// Add one posts index per post type.
		$post_types = get_post_types();

		$post_types_blacklist = $this->settings->get_post_types_blacklist();
		foreach ( $post_types as $post_type ) {
			// Skip blacklisted post types.
			if ( in_array( $post_type, $post_types_blacklist, true ) ) {
				continue;
			}

			$this->indices[] = new Algolia_Posts_Index( $post_type );
		}

		// Add one terms index per taxonomy.
		$taxonomies           = get_taxonomies();
		$taxonomies_blacklist = $this->settings->get_taxonomies_blacklist();
		foreach ( $taxonomies as $taxonomy ) {
			// Skip blacklisted post types.
			if ( in_array( $taxonomy, $taxonomies_blacklist, true ) ) {
				continue;
			}

			$this->indices[] = new Algolia_Terms_Index( $taxonomy );
		}

		// Add the users index.
		$this->indices[] = new Algolia_Users_Index();

		// Allow developers to filter the indices.
		$this->indices = (array) apply_filters( 'algolia_indices', $this->indices );

		foreach ( $this->indices as $index ) {
			$index->set_name_prefix( $index_name_prefix );
			$index->set_client( $client );

			if ( in_array( $index->get_id(), $synced_indices_ids ) ) {
				$index->set_enabled( true );

				if ( $index->contains_only( 'posts' ) ) {
					$this->changes_watchers[] = new Algolia_Post_Changes_Watcher( $index );
				} elseif ( $index->contains_only( 'terms' ) ) {
					$this->changes_watchers[] = new Algolia_Term_Changes_Watcher( $index );
				} elseif ( $index->contains_only( 'users' ) ) {
					$this->changes_watchers[] = new Algolia_User_Changes_Watcher( $index );
				}
			}
		}

		$this->changes_watchers = (array) apply_filters( 'algolia_changes_watchers', $this->changes_watchers );

		foreach ( $this->changes_watchers as $watcher ) {
			$watcher->watch();
		}
	}

	/**
	 * @param array $args
	 *
	 * @return array
	 */
	public function get_indices( array $args = array() ) {
		if ( empty( $args ) ) {
			return $this->indices;
		}

		$indices = $this->indices;

		if ( isset( $args['enabled'] ) && true === $args['enabled'] ) {
			$indices = array_filter(
				$indices, function( $index ) {
					return $index->is_enabled();
				}
			);
		}

		if ( isset( $args['contains'] ) ) {
			$contains = (string) $args['contains'];
			$indices  = array_filter(
				$indices, function( $index ) use ( $contains ) {
					return $index->contains_only( $contains );
				}
			);
		}

		return $indices;
	}

	/**
	 * @param string $index_id
	 *
	 * @return Algolia_Index|null
	 */
	public function get_index( $index_id ) {
		foreach ( $this->indices as $index ) {
			if ( $index_id === $index->get_id() ) {
				return $index;
			}
		}

		return null;
	}

	/**
	 * Get the plugin path.
	 *
	 * @return string
	 */
	public function get_path() {
		return untrailingslashit( ALGOLIA_PATH );
	}

	/**
	 * @return string
	 */
	public function get_templates_path() {
		return (string) apply_filters( 'algolia_templates_path', 'algolia/' );
	}

	/**
	 * @return Algolia_Template_Loader
	 */
	public function get_template_loader() {
		return $this->template_loader;
	}
}
