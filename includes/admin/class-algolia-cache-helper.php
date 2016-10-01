<?php


class Algolia_Cache_Helper
{
	private $was_using_ext_object_cache;
	
	private $transients = array(
		'algolia_running_task',
		'algolia_queue_running',
		'algolia_queue_recently_running',
		'algolia_recently_failed_task',
		'algolia_stop_queue'
	);
	
	public function __construct() {
		foreach ( $this->transients as $transient ) {
			add_filter( 'pre_set_transient_' . $transient, array( $this, 'disable_cache') );
			add_filter( 'pre_transient_' . $transient, array( $this, 'disable_cache') );
			add_action( 'delete_transient_' . $transient, array( $this, 'disable_cache') );

			add_action( 'set_transient_' . $transient, array( $this, 'disable_cache') );
			add_filter( 'transient_' . $transient, array( $this, 'enable_cache') );
			add_action( 'deleted_transient_' . $transient, array( $this, 'disable_cache') );
		}
	}

	public function disable_cache( $value = null ) {
		global $_wp_using_ext_object_cache;
		$this->was_using_ext_object_cache = $_wp_using_ext_object_cache;
		$_wp_using_ext_object_cache = false;

		return $value;
	}

	function enable_cache( $value = null ) {
		global $_wp_using_ext_object_cache;
		$_wp_using_ext_object_cache = $this->was_using_ext_object_cache;

		return $value;
	}
}
