<?php

final class Algolia_Terms_Index extends Algolia_Index
{
	/**
	 * @var string
	 */
	protected $contains_only = 'terms';

	/**
	 * @var string
	 */
	private $taxonomy;

	/**
	 * Algolia_Terms_Index constructor.
	 *
	 * @param string $taxonomy
	 */
	public function __construct( $taxonomy )
	{
		$this->taxonomy = (string) $taxonomy;
	}

	/**
	 * @return string The name displayed in the admin UI.
	 */
	public function get_admin_name()
	{
		$taxonomy = get_taxonomy( $this->taxonomy );
		
		return $taxonomy->labels->name;
	}

	/**
	 * @param $item
	 *
	 * @return bool
	 */
	protected function should_index( $item )
	{
		// For now we index the term if it is in use somewhere.
		$should_index = $item->count > 0;

		return (bool) apply_filters( 'algolia_should_index_term', $should_index, $item );
	}

	/**
	 * @param $item
	 *
	 * @return array
	 */
	protected function get_records( $item )
	{
		$record = array();
		$record['objectID'] = $item->term_id;
		$record['term_id'] = $item->term_id;
		$record['taxonomy'] = $item->taxonomy;
		$record['name'] = $item->name;
		$record['description'] = $item->description;
		$record['slug'] = $item->slug;
		$record['posts_count'] = (int) $item->count;
		if( function_exists( 'wpcom_vip_get_term_link' ) ) {
			$record['permalink'] = wpcom_vip_get_term_link( $item );
		} else {
			$record['permalink'] = get_term_link( $item );
		}

		$record = (array) apply_filters( 'algolia_term_record', $record, $item );
		$record = (array) apply_filters( 'algolia_term_' . $item->taxonomy . '_record', $record, $item );
		
		return array( $record );
	}

	/**
	 * @return int
	 */
	protected function get_re_index_items_count()
	{
		return (int) wp_count_terms( $this->taxonomy );
	}

	/**
	 * @return array
	 */
	protected function get_settings()
	{
		$settings = array(
			'attributesToIndex' => array(
				'unordered(name)',
				'unordered(description)',
			),
			'customRanking' => array(
				'desc(posts_count)',
			),
		);

		$settings = (array) apply_filters( 'algolia_terms_index_settings', $settings, $this->taxonomy );
		$settings = (array) apply_filters( 'algolia_terms_' . $this->taxonomy . '_index_settings', $settings );

		return $settings;
	}

	/**
	 * @return array
	 */
	protected function get_synonyms()
	{
		return (array) apply_filters( 'algolia_terms_index_synonyms', array() );
	}

	/**
	 * @return string
	 */
	public function get_id()
	{
		return 'terms_' . $this->taxonomy;
	}

	
	/**
	 * @param int $page
	 * @param int $batch_size
	 *
	 * @return array
	 */
	protected function get_items( $page, $batch_size )
	{
		$offset = $batch_size * ( $page - 1 );

		$args = array(
			'order'        => 'ASC',
			'orderby'      => 'id',
			'offset'       => $offset,
			'number'	      => $batch_size,
			'hide_empty'   => false, // Let users choose what to index.
		);

		// We use prior to 4.5 syntax for BC purposes.
		return get_terms( $this->taxonomy, $args );
	}

	/**
	 * A performing function that return true if the item can potentially
	 * be subject for indexation or not. This will be used to determine if a task can be queued
	 * for this index. As this function will be called synchronously during other operations,
	 * it has to be as lightweight as possible. No db calls or huge loops.
	 *
	 * @param mixed $task_data
	 *
	 * @return bool
	 */
	public function supports( $task_data )
	{
		return isset( $task_data['taxonomy'] ) && $task_data['taxonomy'] === $this->taxonomy;
	}
	
	/**
	 * @param Algolia_Task $task
	 *
	 * @return mixed
	 */
	protected function extract_item( Algolia_Task $task )
	{
		$data = $task->get_data();
		if ( ! isset( $data['term_id'] ) ) {
			return;
		}
		
		$term = get_term( (int) $data['term_id'] );
		
		return  ! $term ? null : $term ;
	}

	public function get_default_autocomplete_config() {
		$config = array(
			'position'        => 20,
			'max_suggestions' => 3,
			'tmpl_suggestion' => 'autocomplete-term-suggestion',
		);

		return array_merge( parent::get_default_autocomplete_config(), $config );
	}

	/**
	 * @param Algolia_Task $task
	 */
	public function delete_item( Algolia_Task $task ) {
		$data = $task->get_data();
		if ( ! isset( $data['term_id'] ) || ! is_int( $data['term_id'] ) ) {
			return;
		}

		$index = $this->get_index();
		$index->deleteObject( $data['term_id'] );
		$this->get_logger()->log_operation( sprintf( '[1] Deleted 1 record from index %s', $index->indexName ) );
	}
}
