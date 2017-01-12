<?php

/**
 * Process Algolia Task Queue.
 */
class Algolia_CLI extends \WP_CLI_Command {

	/**
	 * @var Algolia_Plugin
	 */
	private $plugin;

	public function __construct() {
		$this->plugin = \Algolia_Plugin::get_instance();
	}

	/**
	 * Process Algolia queue.
	 * 
	 * ## EXAMPLES
	 *
	 *     wp algolia process-queue
	 *
	 * @alias process-queue
	 */
	public function process_queue() {
		if ( ! $this->plugin->get_api()->is_reachable() ) {
			\WP_CLI::error( 'The configuration for this website does not allow to contact the Algolia API.');

			return;
		}

		$queue = $this->plugin->get_task_queue();
		$dispatcher = $this->plugin->get_task_dispatcher();

		$count = $queue->get_queued_tasks_count();

		if ( 0 === $count ) {
			\WP_CLI::success( 'No tasks to process.');
			
			return;
		}

		if ( $queue->is_running() ) {
			\WP_CLI::error( 'Queue is already running.');

			return;
		}

		// Make sure we do not trigger http loopback.
		remove_all_filters( 'algolia_process_queue' );

		\WP_CLI::success( "About to process a total of $count task(s)." );

		$notify = \WP_CLI\Utils\make_progress_bar( "Processing $count task(s)", $count );
		
		do {
			$success = $queue->run( $dispatcher );

			if ( false === $success ) {
				$notify->finish();
				\WP_CLI::error( "The queue processing was aborted.");

				return;
			}

			$newCount = $queue->get_queued_tasks_count();

			// One task can have many sub-tasks. Here we only need to tick on main tasks.
			if ( $newCount < $count ) {
				$notify->tick();
			}
			
			$count = $newCount;
		} while ( $count > 0 );

		$notify->finish();

		\WP_CLI::success( "All Done.");
	}

	/**
	 * Re-index all indices.
	 *
	 * ## EXAMPLES
	 *
	 *     wp algolia re-index-all
	 *
	 * @alias re-index-all
	 */
	public function re_index_all() {
		if ( ! $this->plugin->get_api()->is_reachable() ) {
			\WP_CLI::error( 'The configuration for this website does not allow to contact the Algolia API.');

			return;
		}

		$ids = $this->plugin->get_settings()->get_synced_indices_ids();
		$queue = $this->plugin->get_task_queue();
		foreach ( $ids as $id ) {
			$queue->queue( 're_index_items', array( 'index_id' => $id ) );
			\WP_CLI::success( "Queued [$id] for indexing.");
		}
	}

	/**
	 * Re-index the index passed as first parameter.
	 *
	 * ## OPTIONS
	 *
	 * <index_id>
	 * : Index ID to re-index.
	 *
	 * ## EXAMPLES
	 *
	 *     wp algolia re-index
	 *
	 * @alias re-index
	 */
	public function re_index( $args ) {
		if ( ! $this->plugin->get_api()->is_reachable() ) {
			\WP_CLI::error( 'The configuration for this website does not allow to contact the Algolia API.');

			return;
		}

		list( $index_id ) = $args;

		$ids = $this->plugin->get_settings()->get_synced_indices_ids();
		if ( ! in_array( $index_id, $ids ) ) {
			return \WP_CLI::error( "Index ID '$index_id' does not exist, thus can not be re-indexed." );
		}

		$queue = $this->plugin->get_task_queue();

		$queue->queue( 're_index_items', array( 'index_id' => $index_id ) );
		\WP_CLI::success( "Queued [$index_id] for indexing.");
	}
}
