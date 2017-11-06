<?php

use AlgoliaSearch\AlgoliaException;

class Algolia_Term_Changes_Watcher implements Algolia_Changes_Watcher {

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
		// Fires immediately after the given terms are edited.
		add_action( 'edited_term', array( $this, 'sync_item' ) );

		// Fires after an object's terms have been set.
		add_action( 'set_object_terms', array( $this, 'handle_changes' ), 10, 6 );

		// Fires after a term is deleted from the database and the cache is cleaned.
		add_action( 'delete_term', array( $this, 'on_delete_term' ), 10, 4 );

	}

	/**
	 * @param int $term_id
	 */
	public function sync_item( $term_id ) {
		if ( defined( 'DOING_AUTOSAVE' ) && DOING_AUTOSAVE ) {
			return;
		}

		$term = get_term( (int) $term_id );

		if ( ! $term || ! $this->index->supports( $term ) ) {
			return;
		}

		try {
			$this->index->sync( $term );
		} catch ( AlgoliaException $exception ) {
			error_log( $exception->getMessage() );
		}
	}

	/**
	 * @param $object_id
	 * @param $terms
	 * @param $tt_ids
	 * @param $taxonomy
	 * @param $append
	 * @param $old_tt_ids
	 */
	public function handle_changes( $object_id, $terms, $tt_ids, $taxonomy, $append, $old_tt_ids ) {
		$terms_to_sync = array_unique( array_merge( $terms, $old_tt_ids ) );

		foreach ( $terms_to_sync as $term_id ) {
			$this->sync_item( $term_id );
		}
	}

	/**
	 * @param $term
	 * @param $tt_id
	 * @param $taxonomy
	 * @param $deleted_term
	 */
	public function on_delete_term( $term, $tt_id, $taxonomy, $deleted_term ) {
		if ( ! $this->index->supports( $deleted_term ) ) {
			return;
		}

		try {
			$this->index->delete_item( $deleted_term );
		} catch ( AlgoliaException $exception ) {
			error_log( $exception->getMessage() );
		}
	}
}
