<?php

use AlgoliaSearch\AlgoliaException;

class Algolia_Post_Changes_Watcher implements Algolia_Changes_Watcher {

	/**
	 * @var Algolia_Index
	 */
	private $index;

	/**
	 * @param Algolia_Index $index
	 */
	public function __construct( Algolia_Index $index ) {
		$this->index = $index;
	}

	public function watch() {
		// Fires once a post has been saved.
		add_action( 'save_post', array( $this, 'sync_item' ) );

		// Fires before a post is deleted, at the start of wp_delete_post().
		// At this stage the post metas are still available, and we need them.
		add_action( 'before_delete_post', array( $this, 'delete_item' ) );

        // Create global to check if post is being deleted
		add_action( 'before_delete_post', array( $this, 'before_delete' ), 9 );
        add_action( 'after_delete_post', array( $this, 'after_delete' ), 9 );

		// Handle meta changes after the change occurred.
		add_action( 'added_post_meta', array( $this, 'on_meta_change' ), 10, 4 );
		add_action( 'updated_post_meta', array( $this, 'on_meta_change' ), 10, 4 );
		add_action( 'deleted_post_meta', array( $this, 'on_meta_change' ), 10, 4 );

		// Handle attachment changes. These are required because the other post hooks are not triggered.
		add_action( 'add_attachment', array( $this, 'sync_item' ) );
		add_action( 'attachment_updated', array( $this, 'sync_item' ) );
		add_action( 'delete_attachment', array( $this, 'delete_item' ) );
	}

	/**
	 * @param int $post_id
	 */
	public function sync_item( $post_id ) {
		if ( defined( 'DOING_AUTOSAVE' ) && DOING_AUTOSAVE ) {
			return;
		}

		$post = get_post( (int) $post_id );
		if ( ! $post || ! $this->index->supports( $post ) ) {
			return;
		}

		try {
			$this->index->sync( $post );
		} catch ( AlgoliaException $exception ) {
			error_log( $exception->getMessage() );
		}
	}

	/**
	 * @param int $post_id
	 */
	public function delete_item( $post_id ) {
		$post = get_post( (int) $post_id );
		if ( ! $post || ! $this->index->supports( $post ) ) {
			return;
		}

		try {
			$this->index->delete_item( $post );
		} catch ( AlgoliaException $exception ) {
			error_log( $exception->getMessage() );
		}
	}

	/**
	 * @param string|array $meta_id
	 * @param int          $object_id
	 * @param string       $meta_key
	 */
	public function on_meta_change( $meta_id, $object_id, $meta_key ) {
	    global $doing_post_delete;
		if ( '_thumbnail_id' === $meta_key && $doing_post_delete !==  $object_id ) {
		    $this->sync_item( $object_id );
			return;
		}

        return;
	}

    /**
     * @param $post_id
     */
	public function before_delete($post_id)
    {
        $GLOBALS['doing_post_delete'] = $post_id;
    }

    /**
     * @param $post_id
     */
    public function after_delete($post_id)
    {
        $GLOBALS['doing_post_delete'] = null;
    }

}
