<?php

final class Algolia_Task_Queue
{
	/**
	 * @var Algolia_Logger
	 */
	private $logger;

	/**
	 * @var int
	 */
	private $max_task_retries = 3;

	/**
	 * @param Algolia_Logger $logger
	 */
	public function __construct( Algolia_Logger $logger ) {
		$this->logger = $logger;
	}

	/**
	 * @param string $task_name
	 * @param array $data
	 */
	public function queue( $task_name, array $data ) {
		$result = Algolia_Task::queue( $task_name, $data );

		if ( $result instanceof WP_Error ) {
			return $this->logger->log_error( 'Unable to queue task.', $result );
		}

		$this->logger->log( sprintf( 'New task %s was queued.', $task_name ), $data );

		// Let the automatic queue handling be disabled.
		if ( ! defined( 'ALGOLIA_AUTO_PROCESS_QUEUE' ) || ALGOLIA_AUTO_PROCESS_QUEUE === true ) {
			do_action( 'algolia_process_queue' );
		}
	}

	/**
	 * Runs the next task with a FIFO logic.
	 * The queue is stored in a custom post type, and we use a transient
	 * to ensure the tasks are ran only one at a time.
	 *
	 * @param Algolia_Task_Dispatcher $task_dispatcher
	 *
	 * @throws Exception
	 */
	public function run( Algolia_Task_Dispatcher $task_dispatcher ) {
		if ( 0 === Algolia_Task::get_queued_tasks_count() ) {
			// Nothing to process or queue is already running.
			return false;
		}

		if ( $this->is_running() ) {
			$this->logger->log( sprintf( 'Queue is already being handled.' ) );

			return false;
		}
		
		$should_stop = get_transient( 'algolia_stop_queue' );
		if ( $should_stop ) {
			delete_transient( 'algolia_stop_queue' );
			$this->logger->log( 'Queue was manually stopped' );

			return false;
		}

		$task = Algolia_Task::get_first();

		$this->logger->log( 'About to run 1 task.' );

		$start_time = microtime( true );
		try {
			$this->lock_queue( $task );

			$should_delete = $task_dispatcher->dispatch( $task );
			if ( false !== $should_delete ) {
				$task->delete();
			}
			$this->unlock_queue();
			$handling_time = microtime( true ) - $start_time;
			$this->logger->log( sprintf( 'Task %s was handled in %fs.', $task->get_name(), $handling_time ), $task->get_data() );
			
			do_action( 'algolia_task_handled', $task );
		} catch ( Exception $exception ) {
			$this->unlock_queue();

			$retries = get_post_meta( $task->get_id(), 'algolia_task_retries', true );
			if ( ! $retries ) {
				$retries = 0;
			}

			if ( ++$retries <= $this->max_task_retries ) {
				// Just increase the retries.
				update_post_meta( $task->get_id(), 'algolia_task_retries', $retries );
				set_transient( 'algolia_recently_failed_task', true, 60);
				$this->logger->log_error( sprintf( 'An error occurred while handling task %s. It is gonna be retried.', $task->get_name() ), array( 'task' => $task->get_data(), 'exception' => $exception ) );
			} else {
				// Consider the task un-processable, delete it and go on!
				$this->logger->log_error( sprintf( 'Queue processing stopped after trying to handle task %s %d times.', $task->get_name(), $this->max_task_retries ), array( 'task' => $task->get_data(), 'exception' => $exception ) );

				// Let's reset the retry count for next queue processing attempt.
				delete_post_meta( $task->get_id(), 'algolia_task_retries' );

				// We need to return here to stop the queue processing.
				return false;
			}
		}

		if ( Algolia_Task::get_queued_tasks_count() > 0 ) {
			do_action( 'algolia_process_queue' );
		}

		return true;
	}
	
	/**
	 * Locks the queue to avoid it being handled by several processes at the same time.
	 */
	private function lock_queue( Algolia_Task $task ) {
		$lock_ttl = apply_filters( 'algolia_task_queue_lock_ttl', 30 );
		set_transient( 'algolia_queue_running', true , $lock_ttl );
		set_transient( 'algolia_queue_recently_running', true , 10 );
		
		$task_data = array(
			'name' => $task->get_name(),
		);
		
		if ( 're_index_items' === $task_data['name'] ) {
			$page = get_post_meta( $task->get_id(), 'algolia_task_re_index_page', true );
			$max_num_pages = get_post_meta( $task->get_id(), 'algolia_task_re_index_max_num_pages', true );
			$task_data['page'] = $page ? (int) $page : 1 ;
			$task_data['max_num_pages'] = $max_num_pages ? (int) $max_num_pages : 1 ;
		}
		
		set_transient( 'algolia_running_task', $task_data, 10 );
	}

	/**
	 * Releases the queue lock.
	 */
	private function unlock_queue() {
		delete_transient( 'algolia_queue_running' );
	}

	/**
	 * @return bool
	 */
	public function is_running() {
		if ( 0 === Algolia_Task::get_queued_tasks_count() ) {
			// No task can be running if there are no tasks queued.
			return false;
		}

		// We rely on a transient to keep track of a runs.
		return (bool) get_transient( 'algolia_queue_running' );
	}

	/**
	 * Utility method to know if the queue was recently running.
	 * This is useful for the UI, and make things smoother.
	 *
	 * @return bool
	 */
	public function was_recently_running() {

		return (bool) get_transient( 'algolia_queue_recently_running' );
	}

	/**
	 * @return int
	 */
	public function get_queued_tasks_count()
	{
		return Algolia_Task::get_queued_tasks_count();
	}
}
