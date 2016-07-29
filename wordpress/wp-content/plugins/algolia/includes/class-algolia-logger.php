<?php

class Algolia_Logger
{
	const LEVEL_INFO = 'info';
	const LEVEL_OPERATION = 'operation';
	const LEVEL_ERROR = 'error';

	public static $post_type = 'algolia_log';
	
	private $logging_enabled = false;

	public function __construct( $logging_enabled ) {
		$this->logging_enabled = (bool) $logging_enabled;
	}

	/**
	 * @return bool
	 */
	public function is_logging_enabled()
	{
		return $this->logging_enabled;
	}

	/**
	 * @param string $message
	 * @param mixed  $data
	 * @param string $level
	 */
	public function log( $message, $data = null, $level = self::LEVEL_INFO ) {
		// Do not log if we are not in debug mode.
		if ( false === $this->logging_enabled ) {
			return;
		}

		if ( null === $data ) {
			$data = '';
		} elseif ( is_object( $data ) ) {
			$data = print_r( $data, true );
		} elseif ( is_array( $data ) ) {
			$data = print_r( $data, true );
		} elseif ( is_bool( $data ) ) {
			$data = true === $data ? 'true' : 'false';
		} else {
			$data = (string) $data;
		}

		wp_insert_post( array(
			'post_content' => $data,
			'post_title'   => $message,
			'post_status'  => 'private',
			'post_type'    => self::$post_type,
			'meta_input'   => array( 'algolia_log_level' => $level ),
		) );
	}

	/**
	 * Erases all log files.
	 */
	public function clear_logs() {
		$query = new WP_Query( array(
			'post_type'      => self::$post_type,
			'post_status'    => 'private',
			'nopaging'		     => true,
			'posts_per_page' => -1,
			'fields'         => 'ids',
		) );

		foreach ( $query->posts as $log_id ) {
			wp_delete_post( (int) $log_id, true );
		}
	}

	/**
	 * @param int         $paged
	 * @param string|null $level Filter by the log level.
	 *
	 * @return WP_Query
	 */
	public function get_logs_query( $paged = 0, $level = null ) {
		
		$logs_per_page = (int) apply_filters( 'algolia_logs_per_page', 100 );

		$args = array(
			'post_type'      => self::$post_type,
			'post_status'    => 'private',
			'posts_per_page' => $logs_per_page,
			'orderby'		      => 'ID',
			'paged'			       => absint( $paged ),
		);

		if ( null !== $level && in_array( $level, $this->get_log_levels(), true ) ) {
			$args['meta_key'] = 'algolia_log_level';
			$args['meta_value'] = $level;
		}

		return new WP_Query( $args );
	}

	/**
	 * @param string $message
	 * @param mixed $data
	 */
	public function log_info( $message, $data = null ) {
		$this->log( $message, $data, self::LEVEL_INFO );
	}

	/**
	 * @param string $message
	 * @param mixed $data
	 */
	public function log_error( $message, $data = null ) {
		$this->log( $message, $data, self::LEVEL_ERROR );
	}

	/**
	 * @param string $message
	 * @param mixed $data
	 */
	public function log_operation( $message, $data = null ) {
		$this->log( $message, $data, self::LEVEL_OPERATION );
	}

	/**
	 * Retrieve the available log levels.
	 * 
	 * @return array
	 */
	public function get_log_levels() {
		return array(
			self::LEVEL_INFO,
			self::LEVEL_ERROR,
			self::LEVEL_OPERATION,
		);
	}
}
