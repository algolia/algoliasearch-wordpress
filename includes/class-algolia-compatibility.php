<?php

class Algolia_Compatibility {

	public function __construct() {
		add_action( 'algolia_before_handle_task', array( $this, 'register_vc_shortcodes' ) );
		add_action( 'algolia_before_handle_task', array( $this, 'enable_yoast_frontend' ) );
	}

	/**
	 * @param Algolia_Task $task
	 */
	public function enable_yoast_frontend( Algolia_Task $task ) {
		if ( class_exists( 'WPSEO_Frontend' ) && method_exists( 'WPSEO_Frontend', 'get_instance' ) ) {
			WPSEO_Frontend::get_instance();
		}
	}

	/**
	 * @param Algolia_Task $task
	 */
	public function register_vc_shortcodes( Algolia_Task $task ) {
		if ( class_exists( 'WPBMap' ) && method_exists( 'WPBMap', 'addAllMappedShortcodes' ) ) {
			WPBMap::addAllMappedShortcodes();
		}
	}
}
