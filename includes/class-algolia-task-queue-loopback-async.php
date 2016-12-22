<?php

class Algolia_Task_Queue_Loopback_Async extends WP_Async_Task
{
	/**
	 * @var string
	 */
	protected $action = 'algolia_process_queue';

	/**
	 * @var int
	 */
	protected $argument_count = 0;

	/**
	 * @var Algolia_Task_Queue The Algolia task queue.
	 */
	private $queue;

	/**
	 * @var Algolia_Task_Dispatcher
	 */
	private $dispatcher;
	/**
	 * @var Algolia_Logger
	 */
	private $logger;

	/**
	 * @var int
	 */
	private $retries = 0;

	/**
	 * @var int
	 */
	private $max_retries = 10;

	/**
	 * @var int
	 */
	private $timeout = 1;

	/**
	 * Algolia_Task_Run_Async constructor.
	 *
	 * @param Algolia_Task_Queue      $queue      The Algolia task queue.
	 * @param Algolia_Task_Dispatcher $dispatcher The Algolia task dispatcher.
	 * @param Algolia_Logger          $logger
	 */
	public function __construct( Algolia_Task_Queue $queue, Algolia_Task_Dispatcher $dispatcher, Algolia_Logger $logger ) {
		$this->queue = $queue;
		$this->dispatcher = $dispatcher;
		$this->logger = $logger;

		// Todo: Collect more data to evaluate if we should base the async calls on streams instead of cURL.
		// add_filter( 'http_api_transports', array( $this, 'custom_transport'), 10, 3 );
		parent::__construct();
	}

	/**
	 * @param $transports
	 * @param $args
	 * @param $url
	 *
	 * @return array
	 */
	public function custom_transport( $transports, $args, $url ) {
		// cURL cannot do real non-blocking calls, and the fractional timeout values are only
		// supported since cURL 7.15.5 but are not supported in WP_Http_Curl. So if one of these
		// conditions are met, we rather use PHP Streams to not delay the execution here (e.g. by cron).
		// @see https://core.trac.wordpress.org/ticket/8923
		// @see https://wordpress.org/support/topic/save-a-full-second-on-cron-execution
		// Todo: Merge the given transports instead of re-writing everything.
		if ( $args['blocking'] == false || ceil( $args['timeout'] ) != $args['timeout'] ) {
			$available_transports = array( 'streams' );
		} else {
			$available_transports = array( 'curl', 'streams' );
		}

		return $available_transports;
	}

	/**
	 * Prepare any data to be passed to the asynchronous postback.
	 *
	 * The array this function receives will be a numerically keyed array from
	 * func_get_args(). It is expected that you will return an associative array
	 * so that the $_POST values used in the asynchronous call will make sense.
	 *
	 * The array you send back may or may not have anything to do with the data
	 * passed into this method. It all depends on the implementation details and
	 * what data is needed in the asynchronous postback.
	 *
	 * Do not set values for 'action' or '_nonce', as those will get overwritten
	 * later in launch().
	 *
	 * @throws Exception If the postback should not occur for any reason.
	 *
	 * @param array $data The raw data received by the launch method.
	 *
	 * @return array The prepared data
	 */
	protected function prepare_data( $data ) {

		return array();
	}

	/**
	 * Run the do_action function for the asynchronous postback.
	 *
	 * This method needs to fetch and sanitize any and all data from the $_POST
	 * superglobal and provide them to the do_action call.
	 *
	 * The action should be constructed as "wp_async_task_$this->action"
	 */
	protected function run_action() {
		$this->queue->run( $this->dispatcher );
	}

	public function launch_on_shutdown() {
		if ( ! empty( $this->_body_data ) ) {
			$url = Algolia_Utils::get_loopback_request_url();
			$request_args = Algolia_Utils::get_loopback_request_args( array(
				'timeout'	=> $this->timeout,
				'body'		=> $this->_body_data,
			) );

			$result = wp_remote_post( $url, $request_args );

			if ( ! $result instanceof WP_Error ) {
				// We only log errors, so we are done here.
				return;
			}

			if ( ! $result instanceof WP_Error ) {
				return $this->logger->log_error( 'An error occurred while trying to remotely trigger the next task execution.', $result );
			}

			$message = $result->get_error_message( 'http_request_failed' );
			if ( is_string( $message ) ) {
				if ( substr( $message, 0, 19 ) === 'Operation timed out' ) {
					// This is not an actual error, because the task processing has been triggered.
					return;
				}

				if ( ( substr( $message, 0, 20 ) === 'Connection timed out' || substr( $message, 0, 19 ) === 'Resolving timed out' ) && $this->retries < $this->max_retries ) {
					// Keep track of the retries attempts.
					$this->retries++;

					// Exponentially increase the timeout.
					$this->timeout = pow( $this->timeout, 2 );

					return $this->launch_on_shutdown();
				}
			}

			$this->logger->log_error( 'An error occurred while trying to remotely trigger the next task execution.', $result );
		}
	}
}
