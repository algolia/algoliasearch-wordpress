<?php

class Algolia_Task
{
	public static $post_type = 'algolia_task';
	
	/**
	 * @var WP_Post
	 */
	private $post;

	/**
	 * @var array
	 */
	private $decoded_data;

	/**
	 * @param string $task_name
	 * @param array $data
	 *
	 * @return int|WP_Error
	 */
	public static function queue( $task_name, array $data = array() ) {
		$encoded = json_encode( $data );
		if ( '[]' === $encoded ) {
			$encoded = '{}';
		}

		add_filter( 'wp_insert_attachment_data', array( 'Algolia_Task', 'ensure_task_integrity' ), 100, 2 );
		add_filter( 'wp_insert_post_data',       array( 'Algolia_Task', 'ensure_task_integrity' ), 100, 2 );

		$success = wp_insert_post( array(
			'post_type'    => self::$post_type,
			'post_status'  => 'private',
			'post_title'   => (string) $task_name,
			'post_content' => $encoded,
		), true );

		remove_filter( 'wp_insert_attachment_data', array( 'Algolia_Task', 'ensure_task_integrity' ), 100 );
		remove_filter( 'wp_insert_post_data', array( 'Algolia_Task', 'ensure_task_integrity' ), 100 );

		return $success;
	}

	/**
	 * This ensures tasks always have the correct status and post type.
	 *
	 * @param array $data
	 * @param array $postarr
	 *
	 * @return array
	 */
	public static function ensure_task_integrity( $data = array(), $postarr = array() ) {
		if ( $postarr['post_type'] === self::$post_type ) {
			$data['post_type'] = self::$post_type;
			$data['post_status'] = 'private';
		}

		return $data;
	}

	/**
	 * @return Algolia_Task
	 */
	public static function get_first() {
		$query = new WP_Query( array(
			'post_type'      => self::$post_type,
			'post_status'    => 'private',
			'order'          => 'ASC',
			'orderby'        => 'ID',
			'posts_per_page' => 1,
		) );
		
		return new self( $query->posts[0] );
	}

	/**
	 * @return int
	 */
	public static function get_queued_tasks_count() {
		// Avoid cache because we need the total to always be up to date.
		$cache_key = _count_posts_cache_key( self::$post_type );
		wp_cache_delete( $cache_key, 'counts' );

		$tasks_count = wp_count_posts( self::$post_type );

		return (int) $tasks_count->private;
	}

	/**
	 * @param WP_Post $post
	 */
	public function __construct( WP_Post $post )
	{
		if ( 'algolia_task' !== $post->post_type ) {
			throw new InvalidArgumentException( sprintf( 'Invalid post type, expected "algolia_task", got: "%s"', $post->post_type ) );
		}
		
		$this->post = $post;
	}

	/**
	 * @return int
	 */
	public function get_id() {
		return $this->post->ID;
	}

	/**
	 * @return string
	 */
	public function get_name() {
		return $this->post->post_title;
	}

	/**
	 * @return array
	 */
	public function get_data() {
		if ( null === $this->decoded_data ) {
			$data = json_decode( $this->post->post_content, true );
			if ( null === $data ) {
				throw new RuntimeException( sprintf( 'Unable to decode data for task: %s.', $this->get_name() ) );
			}
			$this->decoded_data = $data;
		}
		
		return $this->decoded_data;
	}

	/**
	 * Deletes a task.
	 */
	public function delete() {
		wp_delete_post( (int) $this->post->ID, true );
	}

	/**
	 * Delete all tasks.
	 */
	public static function delete_all_tasks() {
		$query = new WP_Query( array(
			'post_type'      => self::$post_type,
			'post_status'    => 'private',
			'nopaging'       => true,
			'posts_per_page' => -1,
			'fields'	     => 'ids',
		) );

		foreach ( $query->posts as $id ) {
			wp_delete_post( $id, true );
		}
	}
}
