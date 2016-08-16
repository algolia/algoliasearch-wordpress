<?php

class Algolia_Activator {
	
	public static function activate() {
		if ( ! wp_next_scheduled( 'algolia_process_queue' ) ) {
			wp_schedule_event( time(), 'hourly', 'algolia_process_queue' );
		}
	}
}
