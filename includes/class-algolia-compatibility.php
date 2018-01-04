<?php

class Algolia_Compatibility {

	private $current_language;

	public function __construct() {
		add_action( 'algolia_before_get_records', array( $this, 'register_vc_shortcodes' ) );
		add_action( 'algolia_before_get_records', array( $this, 'enable_yoast_frontend' ) );
		add_action( 'algolia_before_get_records', array( $this, 'wpml_switch_language' ) );
		add_action( 'algolia_after_get_records', array( $this, 'wpml_switch_back_language' ) );
	}

	public function enable_yoast_frontend() {
		if ( class_exists( 'WPSEO_Frontend' ) && method_exists( 'WPSEO_Frontend', 'get_instance' ) ) {
			WPSEO_Frontend::get_instance();
		}
	}

	public function register_vc_shortcodes() {
		if ( class_exists( 'WPBMap' ) && method_exists( 'WPBMap', 'addAllMappedShortcodes' ) ) {
			WPBMap::addAllMappedShortcodes();
		}
	}

	public function wpml_switch_language( $post ) {
		if ( ! $post instanceof WP_Post || ! $this->is_wpml_enabled() ) {
			return;
		}

		global $sitepress;
		$lang_info              = wpml_get_language_information( null, $post->ID );
		$this->current_language = $sitepress->get_current_language();
		$sitepress->switch_lang( $lang_info['language_code'] );
	}

	public function wpml_switch_back_language( $post ) {
		if ( ! $post instanceof WP_Post || ! $this->is_wpml_enabled() ) {
			return;
		}

		global $sitepress;

		$sitepress->switch_lang( $this->current_language );
	}

	/**
	 * @return bool
	 */
	private function is_wpml_enabled() {
		// See https://github.com/algolia/algoliasearch-wordpress/issues/567
		return function_exists( 'icl_object_id' ) && ! class_exists( 'Polylang' );
	}
}
