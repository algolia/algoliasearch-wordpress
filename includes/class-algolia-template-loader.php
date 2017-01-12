<?php

class Algolia_Template_Loader {

	/**
	 * @var Algolia_Plugin
	 */
	private $plugin;

	/**
	 * Algolia_Template_Loader constructor.
	 *
	 * @param Algolia_Plugin $plugin
	 */
	public function __construct( Algolia_Plugin $plugin ) {
		$this->plugin = $plugin;

		// Inject Algolia configuration in a JavaScript variable.
		add_filter( 'wp_footer', array( $this, 'load_algolia_config') );

		// Listen for native templates to override.
		add_filter( 'template_include', array( $this, 'template_loader' ) );

		// Load autocomplete.js search experience if its enabled.
		if ( $this->should_load_autocomplete() ) {
			add_filter( 'wp_enqueue_scripts', array( $this, 'enqueue_autocomplete_scripts' ) );
			add_filter( 'wp_footer', array( $this, 'load_autocomplete_template' ), PHP_INT_MAX );
		}
	}

	public function load_algolia_config() {
		$settings = $this->plugin->get_settings();
		$autocomplete_config = $this->plugin->get_autocomplete_config();

		$config = array(
			'debug'                => defined( 'WP_DEBUG' ) && WP_DEBUG,
			'application_id'       => $settings->get_application_id(),
			'search_api_key'       => $settings->get_search_api_key(),
			'powered_by_enabled'   => $settings->is_powered_by_enabled(),
			'query' 			   => isset( $_GET['s'] ) ? esc_html( $_GET['s'] ) : '',
			'autocomplete'         => array(
				'sources'     => $autocomplete_config->get_config(),
			),
			'indices' => array(),
		);

		// Inject all the indices into the config to ease instantsearch.js integrations.
		$indices = $this->plugin->get_indices( array( 'enabled' => true ) );
		foreach ( $indices as $index ) {
			$config['indices'][ $index->get_id() ] = $index->to_array();
		}

		// Give developers a last chance to alter the configuration.
		$config = (array) apply_filters( 'algolia_config', $config );

		$json_config = json_encode( $config );

		echo '<script type="text/javascript">var algolia = ' . $json_config . '</script>';
	}

	private function should_load_autocomplete() {
		$settings = $this->plugin->get_settings();
		$autocomplete = $this->plugin->get_autocomplete_config();

		if ( null === $autocomplete ) {
			// The user has not provided his credentials yet.
			return false;
		}

		$config = $autocomplete->get_config();
		if ( 'yes' !== $settings->get_autocomplete_enabled() ) {
			return false;
		}

		return ! empty( $config );
	}

	/**
	 * Enqueue Algolia autocomplete.js scripts.
	 */
	public function enqueue_autocomplete_scripts() {

		// Enqueue the autocomplete.js default styles.
		wp_enqueue_style( 'algolia-autocomplete' );

		// Javascript.
		wp_enqueue_script( 'algolia-search' );

		// Enqueue the autocomplete.js library.
		wp_enqueue_script( 'algolia-autocomplete' );
		wp_enqueue_script( 'algolia-autocomplete-noconflict' );

		// Lib useful for positioning the autocomplete.js dropdown.
		wp_enqueue_script( 'tether' );

		// Allow users to easily enqueue custom styles and scripts.
		do_action( 'algolia_autocomplete_scripts' );
	}

	/**
	 * Load a template.
	 *
	 * Handles template usage so that we can use our own templates instead of the themes.
	 *
	 * Templates are in the 'templates' folder. algolia looks for theme.
	 * overrides in /your-theme/algolia/ by default.
	 *
	 * @param mixed $template
	 *
	 * @return string
	 */
	public function template_loader( $template ) {
		$settings = $this->plugin->get_settings();
		if ( is_search() && $settings->should_override_search_with_instantsearch() ) {

			return $this->load_instantsearch_template();
		}

		return $template;
	}

	/**
	 * @return string
	 */
	public function load_instantsearch_template() {
		add_action( 'wp_enqueue_scripts', function () {
			// Enqueue the instantsearch.js default styles.
			wp_enqueue_style( 'algolia-instantsearch' );

			// Ensure jQuery is loaded.
			wp_enqueue_script( 'jquery' );

			// Enqueue the instantsearch.js library.
			wp_enqueue_script( 'algolia-instantsearch' );

			// Allow users to easily enqueue custom styles and scripts.
			do_action( 'algolia_instantsearch_scripts' );
		} );

		return $this->locate_template( 'instantsearch.php' );
	}

	/**
	 * @return string
	 */
	public function load_autocomplete_template() {
		require $this->locate_template( 'autocomplete.php' );
	}

	/**
	 * @param string $file
	 *
	 * @return string
	 */
	private function locate_template( $file ) {
		$locations[] = $file;

		$templates_path = $this->plugin->get_templates_path();
		if ( 'algolia/' !== $templates_path ) {
			$locations[] = 'algolia/' . $file;
		}

		$locations[] = $templates_path . $file;

		$locations = (array) apply_filters( 'algolia_template_locations', $locations, $file );

		$template = locate_template( array_unique( $locations ) );

		$default_template = (string) apply_filters( 'algolia_default_template', $this->plugin->get_path() . '/templates/' . $file, $file );

		return $template ? $template : $default_template;
	}
}
