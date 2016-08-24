<?php

/**
 * Class Algolia_Public.
 */
class Algolia_Public {
	
	/**
	 * Stores a references to the plugin.
	 *
	 * @var Algolia_Plugin
	 */
	private $plugin;

	/**
	 *
	 * @param Algolia_Plugin $plugin A reference to the plugin.
	 */
	public function __construct( Algolia_Plugin $plugin ) {
		$this->plugin = $plugin;

		add_action( 'wp_footer', array( $this, 'inject_algolia_config' ) );
		if ( $this->should_activate_autocomplete() ) {
			$this->activate_autocomplete();
		}
	}

	/**
	 * Determines if the autocomplete should be rendered or not.
	 * 
	 * @return bool
	 */
	public function should_activate_autocomplete() {
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
	 * Registers hooks required for autocomplete rendering.
	 */
	public function activate_autocomplete() {
		add_action( 'wp_footer', array( $this, 'inject_autocomplete_templates' ) );
		add_action( 'wp_enqueue_scripts', array( $this, 'enqueue_autocomplete_assets' ) );
	}

	/**
	 * Enqueue autocomplete CSS and JS files.
	 */
	public function enqueue_autocomplete_assets() {
		// CSS.
		wp_enqueue_style( 'algolia-autocomplete' );

		// Javascript.
		wp_enqueue_script( 'algolia-autocomplete' );
		wp_enqueue_script( 'algolia-search' );
		wp_enqueue_script( 'algolia-frontend-autocomplete' );
		wp_enqueue_script( 'tether' );
		
		do_action( 'algolia_autocomplete_assets' );
	}

	/**
	 * Pass autocomplete config data to the view.
	 */
	public function inject_algolia_config() {
		$settings = $this->plugin->get_settings();
		$autocomplete_config = $this->plugin->get_autocomplete_config();

		if ( defined( 'WP_DEBUG' ) && WP_DEBUG ) {
			$debug = true;
		} else {
			$debug = false;
		}

		$config = array(
			'debug'                => $debug,
			'application_id'       => $settings->get_application_id(),
			'search_api_key'       => $settings->get_search_api_key(),
			'autocomplete'         => array(
				'tmpl_footer' => 'autocomplete-footer',
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

	/**
	 * Inject default underscore templates for autocomplete rendering.
	 */
	public function inject_autocomplete_templates() {

		$templates = array(
			'header'          => plugin_dir_path( __FILE__ ) . '../templates/autocomplete-header.php',
			'post-suggestion' => plugin_dir_path( __FILE__ ) . '../templates/autocomplete-post-suggestion.php',
			'term-suggestion' => plugin_dir_path( __FILE__ ) . '../templates/autocomplete-term-suggestion.php',
			'user-suggestion' => plugin_dir_path( __FILE__ ) . '../templates/autocomplete-user-suggestion.php',
			'footer'          => plugin_dir_path( __FILE__ ) . '../templates/autocomplete-footer.php',
			'empty'           => plugin_dir_path( __FILE__ ) . '../templates/autocomplete-empty.php',
		);

		$templates = (array) apply_filters( 'algolia_autocomplete_templates', $templates );

		foreach ( $templates as $name => $file ) {
			require_once $file;
		}
	}
}
