<?php

class Algolia_Compatibility {

	public function __construct() {
		add_action( 'algolia_before_handle_task', array( $this, 'register_vc_shortcodes' ) );
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
