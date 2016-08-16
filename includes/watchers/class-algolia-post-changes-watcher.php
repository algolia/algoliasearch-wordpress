<?php

class Algolia_Post_Changes_Watcher implements Algolia_Changes_Watcher
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
	 * Algolia_Post_Changes_Watcher constructor.
	 *
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
		// Fires once a post has been saved.
		add_action( 'save_post', array( $this, 'on_save_post' ), 10, 3 );

		// Fires before a post is deleted, at the start of wp_delete_post().
		// At this stage the post metas are still available, and we need them.
		add_action( 'before_delete_post', array( $this, 'queue_sync_item' ) );

		// Handle meta changes after the change occurred.
		add_action( 'added_post_meta', array( $this, 'on_meta_change' ), 10, 4 );
		add_action( 'updated_post_meta', array( $this, 'on_meta_change' ), 10, 4 );
		add_action( 'deleted_post_meta', array( $this, 'on_meta_change' ), 10, 4 );

		// Handle attachment changes. These are required because the other post hooks are not triggered.
		add_action( 'add_attachment', array( $this, 'queue_sync_item' ) );
		add_action( 'attachment_updated', array( $this, 'queue_sync_item' ) );

		// Todo: Implement these when we store the tags and categories as part of Algolia's records.
		// Fires immediately after an object-term relationship is added.
		// do_action( 'added_term_relationship', $object_id, $tt_id );

		//Fires immediately after an object-term relationship is deleted.
		//do_action( 'deleted_term_relationships', $object_id, $tt_ids );
	}

	/**
	 * @param int $post_id
	 * @param string $post_type
	 */
	public function queue_sync_item( $post_id, $post_type = null ) {
		if ( defined( 'DOING_AUTOSAVE' ) && DOING_AUTOSAVE ) {
			return;
		}

		if ( null === $post_type ) {
			$post = get_post( (int) $post_id );
			if ( ! $post ) {
				return;
			}
			$post_type = $post->post_type;
		}
		
		if ( in_array( $post_type, $this->blacklisted_post_types, true ) ) {
			return;
		}

		$task = array(
			'index_id'      => $this->index->get_id(),
			'post_id'       => (int) $post_id,
			'post_type'     => (string) $post_type,
		);

		if ( ! $this->index->supports( $task ) ) {
			return;
		}

		$this->queue->queue( 'sync_item', $task );
	}

	/**
	 * @param int     $post_id
	 * @param WP_Post $post
	 */
	public function on_save_post( $post_id, WP_Post $post ) {
		$this->queue_sync_item( $post->ID, $post->post_type );
	}

	/**
	 * @param string|array $meta_id
	 * @param int $object_id
	 * @param string $meta_key
	 */
	public function on_meta_change( $meta_id, $object_id, $meta_key ) {
		if ( '_thumbnail_id' !== $meta_key ) {
			return;
		}
		$this->queue_sync_item( $object_id );
	}
}
