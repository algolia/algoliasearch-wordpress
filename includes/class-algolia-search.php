<?php

class Algolia_Search {

	/**
	 * @var int
	 */
	private $nb_hits;

	/**
	 * @var Algolia_Index
	 */
	private $index;

	/**
	 * @param Algolia_Index $index
	 */
	public function __construct( Algolia_Index $index ) {
		$this->index = $index;

		add_action( 'pre_get_posts', array( $this, 'pre_get_posts' ) );
	}

	/**
	 * Determines if we should filter the query passed as argument.
	 *
	 * @param WP_Query $query
	 *
	 * @return bool
	 */
	private function should_filter_query( WP_Query $query ) {
		return ! $query->is_admin && $query->is_search() && $query->is_main_query();
	}

	/**
	 * We force the WP_Query to only return records according to Algolia's ranking.
	 *
	 * @param WP_Query $query
	 */
	public function pre_get_posts( WP_Query $query ) {
		if ( ! $this->should_filter_query( $query ) ) {
			return;
		}

		$current_page = 1;
		if ( get_query_var( 'paged' ) ) {
			$current_page = get_query_var( 'paged' );
		} elseif ( get_query_var( 'page' ) ) {
			$current_page = get_query_var( 'page' );
		}

		$posts_per_page = (int) get_option( 'posts_per_page' );

		$params = apply_filters(
			'algolia_search_params', array(
				'attributesToRetrieve' => 'post_id',
				'hitsPerPage'          => $posts_per_page,
				'page'                 => $current_page - 1, // Algolia pages are zero indexed.
			)
		);

		$order_by = apply_filters( 'algolia_search_order_by', null );
		$order    = apply_filters( 'algolia_search_order', 'desc' );

		try {
			$results = $this->index->search( $query->query['s'], $params, $order_by, $order );
		} catch ( \AlgoliaSearch\AlgoliaException $exception ) {
			error_log( $exception->getMessage() );

			return;
		}

		add_filter( 'found_posts', array( $this, 'found_posts' ), 10, 2 );
		add_filter( 'posts_search', array( $this, 'posts_search' ), 10, 2 );

		// Store the total number of its, so that we can hook into the `found_posts`.
		// This is useful for pagination.
		$this->nb_hits = $results['nbHits'];

		$post_ids = array();
		foreach ( $results['hits'] as $result ) {
			$post_ids[] = $result['post_id'];
		}

		// Make sure there are not results by tricking WordPress in trying to find
		// a non existing post ID.
		// Otherwise, the query returns all the results.
		if ( empty( $post_ids ) ) {
			$post_ids = array( 0 );
		}

		$query->set( 'posts_per_page', $posts_per_page );
		$query->set( 'offset', 0 );

		$post_types = 'any';
		if ( isset( $_GET['post_type'] ) ) {
			$post_type = get_post_type_object( $_GET['post_type'] );
			if ( null !== $post_type ) {
				$post_types = $post_type->name;
			}
		}

		$query->set( 'post_type', $post_types );
		$query->set( 'post__in', $post_ids );
		$query->set( 'orderby', 'post__in' );

		// Todo: this actually still excludes trash and auto-drafts.
		$query->set( 'post_status', 'any' );
	}

	/**
	 * This hook returns the actual real number of results available in Algolia.
	 *
	 * @param int      $found_posts
	 * @param WP_Query $query
	 *
	 * @return int
	 */
	public function found_posts( $found_posts, WP_Query $query ) {
		return $this->should_filter_query( $query ) ? $this->nb_hits : $found_posts;
	}

	/**
	 * Filter the search SQL that is used in the WHERE clause of WP_Query.
	 * Removes the where Like part of the queries as we consider Algolia as being the source of truth.
	 * We don't want to filter by anything but the actual list of post_ids resulting
	 * from the Algolia search.
	 *
	 * @param string   $search
	 * @param WP_Query $query
	 *
	 * @return string
	 */
	public function posts_search( $search, WP_Query $query ) {
		return $this->should_filter_query( $query ) ? '' : $search;
	}
}
