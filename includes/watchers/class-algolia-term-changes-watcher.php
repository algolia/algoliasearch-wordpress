<?php

class Algolia_Term_Changes_Watcher implements Algolia_Changes_Watcher
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
	private $blacklisted_taxonomies;

	/**
	 * @param Algolia_Task_Queue $queue
	 * @param Algolia_Index      $index
	 * @param array              $blacklisted_taxonomies
	 */
	public function __construct( Algolia_Task_Queue $queue, Algolia_Index $index, array $blacklisted_taxonomies ) {
		$this->queue = $queue;
		$this->index = $index;
		$this->blacklisted_taxonomies = $blacklisted_taxonomies;
	}

	public function watch()
	{
		// Fires immediately after the given terms are edited.
		add_action( 'edited_terms', array( $this, 'queue_sync_item' ), 10, 2 );

		// Fires immediately before a term taxonomy ID is deleted.
		add_action( 'delete_term', array( $this, 'on_delete_term' ), 10, 3 );

		// Todo: Maybe add the following edge case:
		// Fires immediately after a term-taxonomy relationship is updated.
		// add_action( 'edited_term_taxonomy', array( $this, 'queue_sync_item' ), 10, 2 );
		// edited_term_taxonomies
		// Hook triggered when the taxonomy is hierarchical, and a new parent is assigned
		// for a bunch of terms at the same time.
		// We will implement this if a use case arises. For now we don't use the parent_ID.
	}

	/**
	 * @param id $term_id
	 * @param object|string $taxonomy
	 */
	public function queue_sync_item( $term_id, $taxonomy ) {
		if ( defined( 'DOING_AUTOSAVE' ) && DOING_AUTOSAVE ) {
			return;
		}

		if ( is_object( $taxonomy ) ) {
			$taxonomy = $taxonomy->name;
		}

		if ( in_array( $taxonomy, $this->blacklisted_taxonomies, true ) ) {
			return;
		}

		$task = array(
			'index_id'      => $this->index->get_id(),
			'term_id'       => (int) $term_id,
			'taxonomy'      => $taxonomy,
		);

		if ( ! $this->index->supports( $task ) ) {
			return;
		}

		$this->queue->queue( 'sync_item', $task );
	}

	/**
	 * @param int    $term_id The term ID.
	 * @param string $tt_id The term taxonomy ID.
	 * @param string $taxonomy The taxonomy.
	 */
	public function on_delete_term( $term_id, $tt_id, $taxonomy ) {
		$this->queue_sync_item( $term_id, $taxonomy );
	}
}
