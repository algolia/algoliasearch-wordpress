<?php

class Algolia_Deactivator {
	
	public static function deactivate() {
		// Remove the scheduled queue run hook.
		wp_clear_scheduled_hook( 'algolia_run_queue' );
		wp_clear_scheduled_hook( 'algolia_process_queue' );

		$query = new WP_Query( array(
			'post_type'      => 'algolia_task',
			'post_status'    => 'any',
			'nopaging'       => true,
			'posts_per_page' => -1,
			'fields'	        => 'ids',
		) );

		foreach ( $query->posts as $id ) {
			wp_delete_post( $id, true );
		}

		$query = new WP_Query( array(
			'post_type'      => 'algolia_log',
			'post_status'    => 'any',
			'nopaging'       => true,
			'posts_per_page' => -1,
			'fields'	        => 'ids',
		) );

		foreach ( $query->posts as $id ) {
			wp_delete_post( $id, true );
		}
	}
}
