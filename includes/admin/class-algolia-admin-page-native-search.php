<?php

class Algolia_Admin_Page_Native_Search
{
	/**
	 * @var string
	 */
	private $slug = 'algolia-search-page';

	/**
	 * @var string
	 */
	private $capability = 'manage_options';

	/**
	 * @var string
	 */
	private $section = 'algolia_section_native_search';

	/**
	 * @var string
	 */
	private $option_group = 'algolia_native_search';

	/**
	 * @var Algolia_Plugin
	 */
	private $plugin;

	/**
	 * @param Algolia_Plugin $plugin
	 */
	public function __construct( Algolia_Plugin $plugin ) {
		$this->plugin = $plugin;

		add_action( 'admin_menu', array( $this, 'add_page' ) );
		add_action( 'admin_init', array( $this, 'add_settings' ) );
		add_action( 'admin_notices', array( $this, 'display_errors' ) );
	}

	public function add_page() {
		add_submenu_page(
			'algolia',
			__( 'Search Page', 'algolia' ),
			__( 'Search Page', 'algolia' ),
			$this->capability,
			$this->slug,
			array( $this, 'display_page' )
		);
	}

	public function add_settings() {
		add_settings_section(
			$this->section,
			null,
			array( $this, 'print_section_settings' ),
			$this->slug
		);

		add_settings_field(
			'algolia_override_native_search',
			__( 'Search results', 'algolia' ),
			array( $this, 'override_native_search_callback' ),
			$this->slug,
			$this->section
		);

		register_setting( $this->option_group, 'algolia_override_native_search', array( $this, 'sanitize_override_native_search' ) );
	}

	public function override_native_search_callback() {
		$value = $this->plugin->get_settings()->get_override_native_search();

		require_once dirname( __FILE__ ) . '/partials/form-override-search-option.php';
	}

	/**
	 * @param $value
	 *
	 * @return array
	 */
	public function sanitize_override_native_search( $value ) {

		if ( 'backend' === $value ) {
			add_settings_error(
				$this->option_group,
				'native_search_enabled',
				__( 'WordPress search is now based on Algolia!', 'algolia' ),
				'updated'
			);
		} elseif ( 'instantsearch' === $value ) {
			add_settings_error(
				$this->option_group,
				'native_search_enabled',
				__( 'WordPress search is now based on Algolia instantsearch.js!', 'algolia' ),
				'updated'
			);
		} else {
			$value = 'native';
			add_settings_error(
				$this->option_group,
				'native_search_disabled',
				__( 'You chose to keep the WordPress native search instead of Algolia. If you are using the autocomplete feature of the plugin we highly recommend you turn Algolia search on instead of the WordPress native search.', 'algolia' ),
				'updated'
			);
		}

		return $value;
	}

	/**
	 * Display the page.
	 */
	public function display_page() {
		require_once dirname( __FILE__ ) . '/partials/form-options.php';
	}

	/**
	 * Display the errors.
	 */
	public function display_errors() {
		settings_errors( $this->option_group );

		if ( defined( 'ALGOLIA_HIDE_HELP_NOTICES' ) && ALGOLIA_HIDE_HELP_NOTICES ) {
			return;
		}

		$settings = $this->plugin->get_settings();

		if ( ! $settings->should_override_search_in_backend() && ! $settings->should_override_search_with_instantsearch() ) {
			return;
		}

		$searchable_posts_index = $this->plugin->get_index( 'searchable_posts' );
		if ( false === $searchable_posts_index->is_enabled() && isset( $_GET['page'] ) && $_GET['page'] === $this->slug ) {
			echo '<div class="error notice">
					  <p>' . sprintf( __( 'Searchable posts index needs to be checked on the <a href="%s">Algolia: Indexing page</a> for the search results to be powered by Algolia.', 'algolia' ), admin_url( 'admin.php?page=algolia-indexing' ) ) . '</p>
				  </div>';
		}
	}

	/**
	 * Prints the section text.
	 */
	public function print_section_settings() {
		echo '<p>' . esc_html__( 'By enabling this plugin to override the native WordPress search, your search results will be powered by Algolia\'s typo-tolerant & relevant search algorithms.', 'algolia' ) . '</p>';

		// todo: replace this with a check on the searchable_posts_index
		$indices = $this->plugin->get_indices( array(
			'enabled'  => true,
			'contains' => 'posts',
		) );

		if ( empty( $indices ) ) {
			echo '<div class="error-message">' .
					__( 'You have no index containing only posts yet. Please index some content on the `Indexing` page.', 'algolia' ) .
					'</div>';
		}
	}
}
