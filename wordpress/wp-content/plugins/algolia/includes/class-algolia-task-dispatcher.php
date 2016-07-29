<?php

final class Algolia_Task_Dispatcher
{
	/**
	 * @var array
	 */
	private $indices = array();

	/**
	 * @param array $indices
	 */
	public function __construct( array $indices ) {
		foreach ( $indices as $index ) {
			$this->add_index( $index );
		}
	}

	/**
	 * @param Algolia_Index $index
	 */
	private function add_index( Algolia_Index $index ) {
		$index_id = $index->get_id();
		if ( isset( $this->indices[ $index_id ] ) ) {
			throw new InvalidArgumentException( sprintf( 'Index with ID %s has already been added to the dispatcher.', $index_id ) );
		}
		
		$this->indices[ $index->get_id() ] = $index;
	}

	/**
	 * @param Algolia_Task $task
	 * 
	 * @return bool
	 */
	public function dispatch( Algolia_Task $task ) {
		$data = $task->get_data();

		if ( ! isset( $data['index_id'] ) ) {
			throw new RuntimeException( 'No "index_id" attribute found in the task payload.' );
		}
		$index_id = $data['index_id'];		
		
		if ( ! isset( $this->indices[ $index_id ] ) ) {
			// The index no longer exists, consider the task done.
			return true;
		}

		$index = $this->indices[ $index_id ];

		return $index->handle_task( $task );
	}
}
