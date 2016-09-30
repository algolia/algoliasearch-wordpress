<?php

class Algolia_Logger
{
	const LEVEL_INFO = 'info';
	const LEVEL_OPERATION = 'operation';
	const LEVEL_ERROR = 'error';

	const DEFAULT_MAX_LOG_ENTRIES = 50;

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
		// Only log if logging is enabled or if we are dealing with an error.
		if ( false === $this->logging_enabled && self::LEVEL_ERROR !== $level ) {
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

		$this->clear_old_logs();
	}

	/**
	 * Erases all log files.
	 */
	public function clear_logs() {
		$query = new WP_Query( array(
			'post_type'      => self::$post_type,
			'post_status'    => 'private',
			'nopaging'		 => true,
			'posts_per_page' => -1,
			'fields'         => 'ids',
		) );

		foreach ( $query->posts as $log_id ) {
			wp_delete_post( (int) $log_id, true );
		}
	}

	/**
	 * Deletes oldest log entries until we have less than the ALGOLIA_MAX_LOG_ENTRIES.
	 * ALGOLIA_MAX_LOG_ENTRIES defaults to 50.
	 *
	 * Deletes the 5 oldest log entries which allows big existing log lists to catch-up slowly.
	 */
	public function clear_old_logs() {
		$max_entries = defined( 'ALGOLIA_MAX_LOG_ENTRIES' ) ? (int) ALGOLIA_MAX_LOG_ENTRIES : self::DEFAULT_MAX_LOG_ENTRIES;

		if ( $this->get_log_entries_count() <= $max_entries ) {
			return;
		}

		$query = new WP_Query( array(
			'post_type'      => self::$post_type,
			'post_status'    => 'private',
			'orderby'		 => 'ID',
			'order'			 => 'ASC',
			'posts_per_page' => 5, // We delete the 5 oldest log entries.
			'fields'         => 'ids',
		) );

		foreach ( $query->posts as $log_id ) {
			wp_delete_post( (int) $log_id, true );
		}
	}

	/**
	 * @return int
	 */
	public function get_log_entries_count() {
		$logs_count = wp_count_posts( self::$post_type );

		return (int) $logs_count->private;
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
			'orderby'		 => 'ID',
			'paged'			 => absint( $paged ),
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
