<?php

use AlgoliaSearch\Client;
use AlgoliaSearch\Index;

abstract class Algolia_Index {

	/**
	 * @var Client
	 */
	private $client;

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
	 * be subject for indexation or not. This will be used to determine if an item is part of the index
	 * As this function will be called synchronously during other operations,
	 * it has to be as lightweight as possible. No db calls or huge loops.
	 *
	 * @param mixed $item
	 *
	 * @return bool
	 */
	abstract public function supports( $item );

	public function assert_is_supported( $item ) {
		if ( ! $this->supports( $item ) ) {
			throw new RuntimeException( 'Item is no supported on this index.' );
		}
	}

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
	 * @param string     $query
	 * @param array|null $args
	 *
	 * @return array
	 */
	final public function search( $query, $args = null, $order_by = null, $order = 'desc' ) {

		if ( null !== $order_by ) {
			return $this->search_in_replica( $query, $args, $order_by, $order );
		}

		return $this->get_index()->search( $query, $args );
	}

	/**
	 * @param string $query
	 * @param array  $args
	 * @param string $order_by
	 * @param string $order
	 *
	 * @return array
	 */
	private function search_in_replica( $query, $args, $order_by, $order = 'desc' ) {
		$replica      = $this->get_replica( $order_by, $order );
		$replica_name = $replica->get_replica_index_name( $this );

		$index = $this->client->initIndex( $replica_name );

		return $index->search( $query, $args );
	}

	/**
	 * @param $attribute_name
	 * @param $order
	 *
	 * @return Algolia_Index_Replica
	 */
	private function get_replica( $attribute_name, $order ) {
		$replicas = $this->get_replicas();
		foreach ( $replicas as $replica ) {
			/** @var Algolia_Index_Replica $replica */
			if ( $replica->get_attribute_name() === $attribute_name && $replica->get_order() === $order ) {
				return $replica;
			}
		}

		throw new RuntimeException( sprintf( 'Unable to find replica for attribute "%s" with order "%s".', $attribute_name, $order ) );
	}

	/**
	 * @param bool $flag
	 */
	final public function set_enabled( $flag ) {
		$this->enabled = (bool) $flag;
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
		$this->assert_is_supported( $item );
		if ( $this->should_index( $item ) ) {
			do_action( 'algolia_before_get_records', $item );
			$records = $this->get_records( $item );
			do_action( 'algolia_after_get_records', $item );

			$this->update_records( $item, $records );
			return;
		}

		$this->delete_item( $item );
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
			$this->delete_item( $item );
			return;
		}

		$index   = $this->get_index();
		$records = $this->sanitize_json_data( $records );
		$index->addObjects( $records );
	}

	/**
	 * @return Index
	 */
	public function get_index() {
		return $this->client->initIndex( (string) $this->get_name() );
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
	 * @param int $page
	 */
	public function re_index( $page ) {
		$page = (int) $page;

		if ( $page < 1 ) {
			throw new InvalidArgumentException( 'Page should be superior to 0.' );
		}

		if ( 1 === $page ) {
			$this->create_index_if_not_existing();
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
				$this->delete_item( $item );
				continue;
			}
			do_action( 'algolia_before_get_records', $item );
			$records = array_merge( $records, $this->get_records( $item ) );
			do_action( 'algolia_after_get_records', $item );
		}

		if ( ! empty( $records ) ) {
			$index = $this->get_index();

			$records = $this->sanitize_json_data( $records );

			$index->addObjects( $records );
		}

		if ( $page === $max_num_pages ) {
			do_action( 'algolia_re_indexed_items', $this->get_id() );
		}
	}

	public function create_index_if_not_existing( $clear_if_existing = true ) {
		$index = $this->get_index();

		try {
			$index->getSettings();
			$index_exists = true;
		} catch ( \AlgoliaSearch\AlgoliaException $exception ) {
			$index_exists = false;
		}

		if ( true === $index_exists ) {

			if ( true === $clear_if_existing ) {
				$index->clearIndex();
			}

			$force_settings_update = (bool) apply_filters( 'algolia_should_force_settings_update', false, $this->get_id() );
			if ( false === $force_settings_update ) {
				// No need to go further in this case.
				// We don't change anything when the index already exists.
				// This means that to override, or go back to default settings you have to
				// Clear the index and re-index again or use the 'algolia_force_settings_update' filter
				// to force a settings update
				return;
			}
		}

		$this->push_settings();
	}

	public function push_settings() {
		$index = $this->get_index();

		// This will create the index if it does not exist.
		$settings = $this->get_settings();
		$index->setSettings( $settings );

		// Push synonyms.
		$synonyms = $this->get_synonyms();
		if ( ! empty( $synonyms ) ) {
			$index->batchSynonyms( $synonyms );
		}

		$this->sync_replicas();
	}

	/**
	 * Sanitize data to allow non UTF-8 content to pass.
	 * Here we use a private function introduced in WP 4.1.
	 *
	 * @param $data
	 *
	 * @return mixed
	 * @throws Exception
	 */
	protected function sanitize_json_data( $data ) {
		if ( function_exists( '_wp_json_sanity_check' ) ) {
			return _wp_json_sanity_check( $data, 512 );
		}

		return $data;
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
	public function get_re_index_max_num_pages() {
		$items_count = $this->get_re_index_items_count();

		return (int) ceil( $items_count / $this->get_re_index_batch_size() );
	}

	public function de_index_items() {
		$index_name = $this->get_name();
		$this->client->deleteIndex( $index_name );

		do_action( 'algolia_de_indexed_items', $this->get_id() );
	}

	/**
	 * @return int
	 */
	protected function get_re_index_batch_size() {
		$batch_size = (int) apply_filters( 'algolia_indexing_batch_size', 100 );
		$batch_size = (int) apply_filters( 'algolia_' . $this->get_id() . '_indexing_batch_size', $batch_size );

		return $batch_size;
	}

	/**
	 * @return array
	 */
	abstract protected function get_settings();

	/**
	 * @return array
	 */
	abstract protected function get_synonyms();

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

	public function get_default_autocomplete_config() {
		return array(
			'index_id'        => $this->get_id(),
			'index_name'      => $this->get_name(),
			'label'           => $this->get_admin_name(),
			'admin_name'      => $this->get_admin_name(),
			'position'        => 10,
			'max_suggestions' => 5,
			'tmpl_suggestion' => 'autocomplete-post-suggestion',
		);
	}

	/**
	 * @return array
	 */
	public function to_array() {
		$replicas = $this->get_replicas();

		$items = array();
		foreach ( $replicas as $replica ) {
			$items[] = array(
				'name' => $replica->get_replica_index_name( $this ),
			);
		}

		return array(
			'name'     => $this->get_name(),
			'id'       => $this->get_id(),
			'enabled'  => $this->enabled,
			'replicas' => $items,
		);
	}

	/**
	 * @return array
	 */
	public function get_replicas() {
		$replicas = (array) apply_filters( 'algolia_index_replicas', array(), $this );
		$replicas = (array) apply_filters( 'algolia_' . $this->get_id() . '_index_replicas', $replicas, $this );

		$filtered = array();
		// Filter out invalid inputs.
		foreach ( $replicas as $replica ) {
			if ( ! $replica instanceof Algolia_Index_Replica ) {
				continue;
			}
			$filtered[] = $replica;
		}

		return $filtered;
	}

	private function sync_replicas() {
		$replicas = $this->get_replicas();
		if ( empty( $replicas ) ) {
			// No need to go further if there are no replicas!
			return;
		}

		$replica_index_names = array();
		foreach ( $replicas as $replica ) {
			/** @var Algolia_Index_Replica $replica */
			$replica_index_names[] = $replica->get_replica_index_name( $this );
		}

		$this->get_index()->setSettings(
			array(
				'replicas' => $replica_index_names,
			), false
		);

		$client = $this->get_client();

		// Ensure we re-push the master index settings each time.
		$settings = $this->get_settings();
		foreach ( $replicas as $replica ) {
			/** @var Algolia_Index_Replica $replica */
			$settings['ranking'] = $replica->get_ranking();
			$replica_index_name  = $replica->get_replica_index_name( $this );
			$index               = $client->initIndex( $replica_index_name );
			$index->setSettings( $settings );
		}
	}

	/**
	 * @param mixed $item
	 */
	abstract public function delete_item( $item );

	/**
	 * Returns true if the index exists in Algolia.
	 * false otherwise.
	 *
	 * @return bool
	 * @throws \AlgoliaSearch\AlgoliaException
	 */
	public function exists() {
		try {
			$this->get_index()->getSettings();
		} catch ( \AlgoliaSearch\AlgoliaException $exception ) {
			if ( $exception->getMessage() === 'Index does not exist' ) {
				return false;
			}

			error_log( $exception->getMessage() );

			return false;
		}

		return true;
	}

	public function clear() {
		$this->get_index()->clearIndex();
	}

}
