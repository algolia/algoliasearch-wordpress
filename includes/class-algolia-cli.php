<?php

/**
 * Process Algolia Task Queue.
 */
class Algolia_CLI extends WP_CLI_Command {

	/**
	 * @var Algolia_Plugin
	 */
	private $plugin;

	public function __construct() {
		$this->plugin = Algolia_Plugin::get_instance();
	}

	/**
	 * Process Algolia queue.
	 * 
	 * ## EXAMPLES
	 *
	 *     wp algolia process_queue
	 *
	 * @alias process-queue
	 */
	public function process_queue() {
		$queue = $this->plugin->get_task_queue();
		$dispatcher = $this->plugin->get_task_dispatcher();

		$count = $queue->get_queued_tasks_count();

		if ( 0 === $count ) {
			WP_CLI::success( 'No tasks to process.');
			return;
		}

		// Make sure we do not trigger http loopback.
		remove_all_filters( 'algolia_process_queue' );

		\WP_CLI::debug( "About to process a total of $count task(s)." );

		$notify = \WP_CLI\Utils\make_progress_bar( "Processing $count task(s)", $count );
		$queue->run( $dispatcher );
		do {
			$queue->run( $dispatcher );

			$newCount = $queue->get_queued_tasks_count();

			// One task can have many sub-tasks. Here we only need to tick on main tasks.
			if ( $newCount < $count ) {
				$notify->tick();
			}
			
			$count = $newCount;
		} while ( $count > 0 );

		$notify->finish();

		WP_CLI::success( "Done.");
	}
}
