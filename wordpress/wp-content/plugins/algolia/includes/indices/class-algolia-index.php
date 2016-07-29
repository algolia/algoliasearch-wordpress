<?php

use AlgoliaSearch\Client;
use AlgoliaSearch\Index;

abstract class Algolia_Index
{
	/**
	 * @var Client
	 */
	private $client;

	/**
	 * @var Algolia_Logger
	 */
	private $logger;

	/**
	 * @var bool
	 */
	private $enabled = false;

	/**
	 * @var string
	 */
	private $name_prefix = '';

	/**
	 * @var string|null Should be one of posts, terms or users or left null. 
	 */
	protected $contains_only;

	/**
	 * @return string The name displayed in the admin UI.
	 */
	abstract public function get_admin_name();

	/**
	 * @param $type
	 *
	 * @return bool
	 */
	final public function contains_only( $type ) {
		if ( null === $this->contains_only ) {
			return false;
		}
		
		return $this->contains_only === $type;
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
	abstract public function supports( $task_data );

	/**
	 * @param Client $client
	 */
	final public function set_client( Client $client ) {
		$this->client = $client;
	}

	/**
	 * @return Client
	 */
	final protected function get_client() {
		if ( null === $this->client ) {
			throw new LogicException( 'Client has not been set.' );
		}
		
		return $this->client;
	}

	/**
	 * @param string $query
	 * @param array|null $args
	 *
	 * @return array
	 */
	final public function search( $query, $args = null ) {
		return $this->get_index()->search( $query, $args );
	}

	/**
	 * @param bool $flag
	 */
	final public function set_enabled( $flag ) {
		$this->enabled = (bool) $flag;
	}
	
	/**
	 * @param Algolia_Logger $logger
	 */
	final public function set_logger( Algolia_Logger $logger ) {
		$this->logger = $logger;
	}

	/**
	 * @return Algolia_Logger
	 */
	final protected function get_logger() {
		if ( null === $this->client ) {
			throw new LogicException( 'Logger has not been set.' );
		}

		return $this->logger;
	}
	
	/**
	 * @return bool
	 */
	final public function is_enabled() {
		return $this->enabled;
	}

	/**
	 * @param string $prefix
	 */
	final public function set_name_prefix( $prefix ) {
		$this->name_prefix = (string) $prefix;
	}

	/**
	 * @param mixed $item
	 */
	public function sync( $item ) {
		if ( $this->should_index( $item ) ) {
			$records = $this->get_records( $item );

			return $this->update_records( $item, $records );
		}

		$this->update_records( $item, array() );
	}
	
	/**
	 * @param $item
	 *
	 * @return bool
	 */
	abstract protected function should_index( $item );

	/**
	 * @param $item
	 * 
	 * @return array
	 */
	abstract protected function get_records( $item );

	/**
	 * @param mixed $item
	 * @param array $records
	 */
	protected function update_records( $item, array $records ) {
		if ( empty( $records ) ) {
			return;
		}

		$index = $this->get_index();
		$index->addObjects( $records );

		$records_count = count( $records );
		$this->logger->log_operation( sprintf( '[%d] Added %d records to index %s', $records_count, $records_count, $index->indexName ), $records );
	}
	
	/**
	 * @return Index
	 */
	public function get_index() {
		return $this->client->initIndex( (string) $this->get_name() );
	}

	/**
	 * @return Index
	 */
	protected function get_tmp_index() {
		return $this->client->initIndex( (string) $this->get_tmp_name() );
	}

	/**
	 * @param string $prefix
	 *
	 * @return string
	 */
	public function get_name( $prefix = null ) {
		if ( null === $prefix ) {
			$prefix = $this->name_prefix;
		}

		return $prefix . $this->get_id();
	}

	/**
	 * @return string
	 */
	protected function get_tmp_name() {
		return $this->get_name() . '_tmp';
	}
	
	/**
	 * @param int $page
	 */
	public function re_index( $page ) {
		$page = (int) $page;

		if ( $page < 1 ) {
			throw new InvalidArgumentException( 'Page should be superior to 0.' );
		}
		
		if ( 1 === $page ) {
			$this->create_tmp_index();
		}

		$batch_size = (int) $this->get_re_index_batch_size();
		if ( $batch_size < 1 ) {
			throw new InvalidArgumentException( 'Re-index batch size can not be lower than 1.' );
		}

		$items_count = $this->get_re_index_items_count();

		$max_num_pages = (int) max( ceil( $items_count / $batch_size ), 1 );

		$items = $this->get_items( $page, $batch_size );

		$records = array();
		foreach ( $items as $item ) {
			if ( ! $this->should_index( $item ) ) {
				continue;
			}

			$records = array_merge( $records, $this->get_records( $item ) );
		}

		if ( ! empty( $records ) ) {
			$index = $this->get_tmp_index();
			$index->addObjects( $records );
			$this->logger->log_operation( sprintf( '[%d] Added %d records to index %s', count( $records ), count( $records ), $index->indexName ), $records );
		}

		if ( $page === $max_num_pages ) {
			$this->deploy_tmp_index();
			do_action( 'algolia_re_indexed_items', $this->get_id() );
		}
	}

	/**
	 * @return int
	 */
	abstract protected function get_re_index_items_count();

	/**
	 * @param int $page
	 *
	 * @return bool
	 */
	protected function is_last_page_to_re_index( $page ) {

		return (int) $page >= $this->get_re_index_max_num_pages();
	}

	/**
	 * @return int
	 */
	private function get_re_index_max_num_pages() {
		$items_count = $this->get_re_index_items_count();
		
		return (int) ceil( $items_count / $this->get_re_index_batch_size() );
	}

	public function de_index_items() {
		$index_name = $this->get_name();
		$this->client->deleteIndex( $index_name );
		$this->logger->log_operation( sprintf( "[1] Deleted index '%s'.", $index_name ) );

		$tmp_name = $this->get_tmp_name();
		$this->client->deleteIndex( $tmp_name );
		$this->logger->log_operation( sprintf( "[1] Deleted temporary index '%s'.", $tmp_name ) );

		do_action( 'algolia_de_indexed_items', $this->get_id() );
	}

	/**
	 * @return int
	 */
	protected function get_re_index_batch_size() {
		return 50;
	}

	protected function create_tmp_index() {
		$tmp_index = $this->get_tmp_index();

		// Ensure the tmp index is in a clean state.
		$tmp_index->clearIndex();
		$this->logger->log_operation( sprintf( "[1] Cleared index '%s'.", $tmp_index->indexName ) );

		// Set the temporary index settings.
		$settings = $this->get_settings();
		$tmp_index->setSettings( $settings );
		$this->logger->log_operation( sprintf( "[1] Pushed settings to index '%s'.", $tmp_index->indexName ), $settings );
		
		// Set the synonyms.
		$synonyms = $this->get_synonyms();
		if ( empty( $synonyms ) ) {
			$tmp_index->clearSynonyms();
			$this->logger->log_operation( sprintf( "[1] Cleared synonyms of index '%s'.", $tmp_index->indexName ), $synonyms );
		} else {
			$tmp_index->batchSynonyms( $synonyms, false, true );
			$this->logger->log_operation( sprintf( "[1] Pushed synonyms to index '%s'.", $tmp_index->indexName ), $synonyms );
		}
	}

	/**
	 * @return array
	 */
	abstract protected function get_settings();

	/**
	 * @return array
	 */
	abstract protected function get_synonyms();

	protected function deploy_tmp_index() {
		$index_name = $this->get_name();
		$tmp_name = $this->get_tmp_name();
		$this->client->moveIndex( $tmp_name, $index_name );
		$this->logger->log_operation( sprintf( '[1] Moved index %s to index %s', $tmp_name, $index_name ) );
	}

	/**
	 * @return string
	 */
	abstract public function get_id();
	
	/**
	 * @param int $page
	 * @param int $batch_size
	 *
	 * @return array
	 */
	abstract protected function get_items( $page, $batch_size );

	/**
	 * @param string $from
	 */
	public function change_name_prefix( $from, $to ) {
		$from_name = $this->get_name( $from );
		$to_name = $this->get_name( $to );

		$this->client->moveIndex( $from_name, $to_name );
		$this->logger->log_operation( sprintf( '[1] Moved index from %s to %s.', $from_name, $to_name ) );
	}
	
	public function get_default_autocomplete_config() {
		return array(
			'index_id'        => $this->get_id(),
			'index_name'      => $this->get_name(),
			'label'           => $this->get_admin_name(),
			'position'        => 10,
			'max_suggestions' => 5,
			'tmpl_suggestion' => 'autocomplete-post-suggestion',
			'tmpl_header' 	   => 'autocomplete-header',
		);
	}

	/**
	 * @param Algolia_Task $task
	 *
	 * @return mixed
	 */
	abstract protected function extract_item( Algolia_Task $task );

	/**
	 * @param Algolia_Task $task
	 *
	 * @return bool
	 */
	final public function handle_task( Algolia_Task $task )
	{
		$data = $task->get_data();

		switch ( $task->get_name() ) {
			case 'sync_item':
				$item = $this->extract_item( $task );
				
				if ( null === $item ) {
					throw new RuntimeException( 'Unable to extract item from task.' );
				}
				
				$this->sync( $item );
				break;
			case 're_index_items':
				$page = get_post_meta( $task->get_id(), 'algolia_task_re_index_page', true );
				if ( ! $page ) {
					$page = 1;
				}
				
				$this->re_index( $page );

				if ( ! $this->is_last_page_to_re_index( $page ) ) {
					update_post_meta( $task->get_id(), 'algolia_task_re_index_page', ++$page );
					update_post_meta( $task->get_id(), 'algolia_task_re_index_max_num_pages', $this->get_re_index_max_num_pages() );
					
					return false;
				}
				break;
			case 'de_index_items':
				$this->de_index_items();
				break;
			case 'change_name_prefix':
				$this->change_name_prefix( $data['from'], $data['to'] );
				break;
		}

		return true;
	}

	/**
	 * @return array
	 */
	public function to_array() {
		return array(
			'name' 		  => $this->get_name(),
			'id'   		  => $this->get_id(),
			'enabled' 	=> $this->enabled,
		);
	}
}
