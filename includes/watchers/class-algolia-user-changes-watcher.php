<?php

class Algolia_User_Changes_Watcher implements Algolia_Changes_Watcher
{
	/**
	 * @var Algolia_Task_Queue
	 */
	private $queue;
	
	/**
	 * @var Algolia_Index
	 */
	private $index;
	
	/**
	 * @var array
	 */
	private $blacklisted_post_types;

	/**
	 * @param Algolia_Task_Queue $queue
	 * @param Algolia_Index      $index
	 * @param array              $blacklisted_post_types
	 */
	public function __construct( Algolia_Task_Queue $queue, Algolia_Index $index, array $blacklisted_post_types ) {
		$this->queue = $queue;
		$this->index = $index;
		$this->blacklisted_post_types = $blacklisted_post_types;
	}

	public function watch() {
		// Fires immediately after an existing user is updated.
		add_action( 'profile_update', array( $this, 'queue_sync_item' ) );

		// Fires immediately after a new user is registered.
		add_action( 'user_register', array( $this, 'queue_sync_item' ) );

		// Fires immediately before a user is deleted.
		add_action( 'delete_user', array( $this, 'queue_sync_item' ) );

		// Fires once a post has been saved.
		add_action( 'save_post', array( $this, 'on_save_post' ), 10, 2 );

		// Fires before a post is deleted, at the start of wp_delete_post().
		// At this stage the post metas are still available, and we need them.
		add_action( 'before_delete_post', array( $this, 'on_delete_post' ) );
	}

	/**
	 * @param $user_id
	 */
	public function queue_sync_item( $user_id ) {
		if ( defined( 'DOING_AUTOSAVE' ) && DOING_AUTOSAVE ) {
			return;
		}

		$task = array(
			'index_id'      => $this->index->get_id(),
			'user_id'       => (int) $user_id,
		);

		if ( ! $this->index->supports( $task ) ) {
			return;
		}

		$this->queue->queue( 'sync_item', $task );
	}

	/**
	 * Ensures that the user post count gets updated.
	 * 
	 * @param int     $post_id
	 * @param WP_Post $post
	 */
	public function on_save_post( $post_id, WP_Post $post ) {
		if ( in_array( $post->post_type, $this->blacklisted_post_types, true ) ) {
			return;
		}
		
		$this->queue_sync_item( (int) $post->post_author );
	}

	/**
	 * Ensures that the user post count gets updated.
	 *
	 * @param int $post_id
	 */
	public function on_delete_post( $post_id ) {
		$post = get_post( (int) $post_id );
		
		if ( ! $post || in_array( $post->post_type, $this->blacklisted_post_types, true ) ) {
			return;
		}

		$this->queue_sync_item( (int) $post->post_author );
	}
}
