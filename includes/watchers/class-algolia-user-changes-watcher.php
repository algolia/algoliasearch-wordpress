<?php

use AlgoliaSearch\AlgoliaException;

class Algolia_User_Changes_Watcher implements Algolia_Changes_Watcher {

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
		// Fires immediately after an existing user is updated.
		add_action( 'profile_update', array( $this, 'sync_item' ) );

		// Fires immediately after a new user is registered.
		add_action( 'user_register', array( $this, 'sync_item' ) );

		// Fires immediately before a user is deleted.
		add_action( 'delete_user', array( $this, 'delete_item' ) );

		// Fires once a post has been saved.
		add_action( 'save_post', array( $this, 'on_save_post' ), 10, 2 );

		// Fires before a post is deleted, at the start of wp_delete_post().
		// At this stage the post metas are still available, and we need them.
		add_action( 'before_delete_post', array( $this, 'on_delete_post' ) );
	}

	/**
	 * @param $user_id
	 */
	public function sync_item( $user_id ) {
		if ( defined( 'DOING_AUTOSAVE' ) && DOING_AUTOSAVE ) {
			return;
		}

		$user = get_user_by( 'id', $user_id );

		if ( ! $user || ! $this->index->supports( $user ) ) {
			return;
		}

		try {
			$this->index->sync( $user );
		} catch ( AlgoliaException $exception ) {
			error_log( $exception->getMessage() );
		}
	}

	/**
	 * @param int $user_id
	 */
	public function delete_item( $user_id ) {
		$user = get_user_by( 'id', $user_id );

		if ( ! $user || ! $this->index->supports( $user ) ) {
			return;
		}

		try {
			$this->index->delete_item( $user );
		} catch ( AlgoliaException $exception ) {
			error_log( $exception->getMessage() );
		}
	}

	/**
	 * Ensures that the user post count gets updated.
	 *
	 * @param int     $post_id
	 * @param WP_Post $post
	 */
	public function on_save_post( $post_id, WP_Post $post ) {
		$this->sync_item( (int) $post->post_author );
	}

	/**
	 * Ensures that the user post count gets updated.
	 *
	 * @param int $post_id
	 */
	public function on_delete_post( $post_id ) {
		$post = get_post( (int) $post_id );

		if ( ! $post ) {
			return;
		}

		$watcher   = $this;
		$author_id = $post->post_author;

		// We delay the sync until after the post was deleted to propagate the change
		// posts count change for the author.
		// Todo: this is not optimal given it would be triggered for every future triggered hook.
		// Todo: needs to be changed.
		add_action(
			'after_delete_post', function() use ( $watcher, $author_id ) {
				$watcher->sync_item( $author_id );
			}
		);
	}
}
