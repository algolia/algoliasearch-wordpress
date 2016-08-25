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
		add_filter( 'template_include', array( $this, 'template_loader' ) );
		$this->plugin = $plugin;
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
	 * @return string
	 */
	public function template_loader( $template ) {
		$file = '';

		/*if ( is_embed() ) {
			return $template;
		}*/

		$settings = $this->plugin->get_settings();

		if ( is_search() && $settings->should_override_search_with_instantsearch() ) {
			$file = 'instantsearch.php';
			$find[] = $file;
			$find[] = $this->plugin->get_templates_path() . $file;

			add_action( 'wp_enqueue_scripts', function () {
				wp_enqueue_script( 'algolia-instantsearch' );
				wp_enqueue_style( 'algolia-instantsearch' );
			} );
		}

		// Fallback to templates shipped with the plugin.
		if ( $file ) {
			$template = locate_template( array_unique( $find ) );
			if ( ! $template ) {
				$template = $this->plugin->get_path() . '/templates/' . $file;
			}
		}

		return $template;
	}
}
