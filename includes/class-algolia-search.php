<?php

use AlgoliaSearch\AlgoliaException;

class Algolia_Search
{
	/**
	 * @var int
	 */
	private $nb_hits;

	/**
	 * @var array
	 */
	private $ranked_post_ids;

	/**
	 * @var Algolia_Logger
	 */
	private $logger;

	/**
	 * @var Algolia_Index
	 */
	private $index;

	/**
	 * @param Algolia_Index  $index
	 * @param Algolia_Logger $logger
	 */
	public function __construct( Algolia_Index $index, Algolia_Logger $logger ) {
		$this->index = $index;
		$this->logger = $logger;

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

		// This can fail if the index is still queued.
		try {
			$params = apply_filters( 'algolia_search_params', array(
				'attributesToRetrieve' => 'post_id',
				'hitsPerPage'          => $posts_per_page,
				'page'                 => $current_page - 1, // Algolia pages are zero indexed.
			) );
			
			$results = $this->index->search( $query->query['s'], $params );
		} catch ( AlgoliaException $exception ) {
			$this->logger->log( 'An error occurred while performing a search.', $exception, Algolia_Logger::LEVEL_ERROR );

			return;
		}

		add_filter( 'the_posts', array( $this, 'the_posts' ), 10, 2 );
		add_filter( 'found_posts', array( $this, 'found_posts' ), 10, 2 );
		add_filter( 'posts_search', array( $this, 'posts_search' ), 10, 2 );
		add_filter( 'posts_clauses', array( $this, 'posts_clauses' ), 10, 2 );

		// Store the total number of its, so that we can hook into the `found_posts`.
		// This is useful for pagination.
		$this->nb_hits = $results['nbHits'];

		$post_ids = array();
		foreach ( $results['hits'] as $result ) {
			$post_ids[] = $result['post_id'];
		}

		$this->ranked_post_ids = $post_ids;

		$query->set( 'posts_per_page', $posts_per_page );
		$query->set( 'offset', 0 );
		$query->set( 'post_type', 'any' );
		$query->set( 'post__in', $post_ids );

		// Todo: this actually still excludes trash and auto-drafts.
		$query->set( 'post_status', 'any' );
	}

	/**
	 * This hook orders the posts according to Algolia's ranking instead
	 * of the MySQL default ranking.
	 *
	 * @param array    $posts
	 * @param WP_Query $query
	 *
	 * @return array
	 */
	public function the_posts( array $posts, WP_Query $query ) {
		if ( ! $this->should_filter_query( $query ) ) {
			return $posts;
		}

		$ranked_posts = array();

		$ranked_post_ids = array_reverse( $this->ranked_post_ids );
		do {
			$ranked_post_id = array_pop( $ranked_post_ids );
			foreach ( $posts as $post ) {
				// Todo: This could be optimized.
				if ( $ranked_post_id === $post->ID ) {
					$ranked_posts[] = $post;
					break;
				}
			}
		} while ( ! empty( $ranked_post_ids ) );

		return $ranked_posts;
	}

	/**
	 * This hook returns the actual real number of results available in Algolia.
	 *
	 * @param int $found_posts
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
	 * @param string $search
	 * @param WP_Query $query
	 *
	 * @return string
	 */
	public function posts_search( $search, WP_Query $query ) {
		return $this->should_filter_query( $query ) ? '' : $search;
	}

	/**
	 * Removes remaining unused SQL pieces.
	 *
	 * @param array $pieces
	 * @param WP_Query $query
	 *
	 * @return mixed
	 */
	public function posts_clauses( $pieces, WP_Query $query ) {
		if ( ! $this->should_filter_query( $query ) ) {
			return $pieces;
		}

		// We don't care about MySQL ordering. We will need to re-order with Algolia's ranking anyway.
		$pieces['orderby'] = '';

		return $pieces;
	}
}
