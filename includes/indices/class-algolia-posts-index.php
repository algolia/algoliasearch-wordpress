<?php

final class Algolia_Posts_Index extends Algolia_Index {

	/**
	 * @var string
	 */
	private $post_type;

	protected $contains_only = 'posts';

	/**
	 * @param string $post_type
	 */
	public function __construct( $post_type ) {
		$this->post_type = (string) $post_type;
	}

	/**
	 * @param mixed $item
	 *
	 * @return bool
	 */
	public function supports( $item ) {
		return $item instanceof WP_Post && $item->post_type === $this->post_type;
	}

	/**
	 * @return string The name displayed in the admin UI.
	 */
	public function get_admin_name() {
		$post_type = get_post_type_object( $this->post_type );

		return null === $post_type ? $this->post_type : $post_type->labels->name;
	}

	/**
	 * @param $item
	 *
	 * @return bool
	 */
	protected function should_index( $item ) {
		return $this->should_index_post( $item );
	}

	/**
	 * @param WP_Post $post
	 *
	 * @return bool
	 */
	private function should_index_post( WP_Post $post ) {
		$post_status = $post->post_status;

		if ( 'inherit' === $post_status ) {
			$parent_post = get_post( $post->post_parent );
			if ( null !== $parent_post ) {
				$post_status = $parent_post->post_status;
			} else {
				$post_status = 'publish';
			}
		}

		$should_index = 'publish' === $post_status && empty( $post->post_password );

		return (bool) apply_filters( 'algolia_should_index_post', $should_index, $post );
	}

	/**
	 * @param $item
	 *
	 * @return array
	 */
	protected function get_records( $item ) {
		return $this->get_post_records( $item );
	}

	/**
	 * Turns a WP_Post in a collection of records to be pushed to Algolia.
	 * Given every single post is splitted into several Algolia records,
	 * we also attribute an objectID that follows a naming convention for
	 * every record.
	 *
	 * @param WP_Post $post
	 *
	 * @return array
	 */
	private function get_post_records( WP_Post $post ) {
		$shared_attributes = $this->get_post_shared_attributes( $post );

		$removed = remove_filter( 'the_content', 'wptexturize', 10 );

		$post_content = apply_filters( 'algolia_post_content', $post->post_content, $post );
		$post_content = apply_filters( 'the_content', $post_content );

		if ( true === $removed ) {
			add_filter( 'the_content', 'wptexturize', 10 );
		}

		$post_content = Algolia_Utils::prepare_content( $post_content );
		$parts        = Algolia_Utils::explode_content( $post_content );

		if ( defined( 'ALGOLIA_SPLIT_POSTS' ) && false === ALGOLIA_SPLIT_POSTS ) {
			$parts = array( array_shift( $parts ) );
		}

		$records = array();
		foreach ( $parts as $i => $part ) {
			$record                 = $shared_attributes;
			$record['objectID']     = $this->get_post_object_id( $post->ID, $i );
			$record['content']      = $part;
			$record['record_index'] = $i;
			$records[]              = $record;
		}

		$records = (array) apply_filters( 'algolia_post_records', $records, $post );
		$records = (array) apply_filters( 'algolia_post_' . $post->post_type . '_records', $records, $post );

		return $records;
	}

	/**
	 * @param WP_Post $post
	 *
	 * @return array
	 */
	private function get_post_shared_attributes( WP_Post $post ) {
		$shared_attributes                        = array();
		$shared_attributes['post_id']             = $post->ID;
		$shared_attributes['post_type']           = $post->post_type;
		$shared_attributes['post_type_label']     = $this->get_admin_name();
		$shared_attributes['post_title']          = $post->post_title;
		$shared_attributes['post_excerpt']        = apply_filters( 'the_excerpt', $post->post_excerpt );
		$shared_attributes['post_date']           = get_post_time( 'U', false, $post );
		$shared_attributes['post_date_formatted'] = get_the_date( '', $post );
		$shared_attributes['post_modified']       = get_post_modified_time( 'U', false, $post );
		$shared_attributes['comment_count']       = (int) $post->comment_count;
		$shared_attributes['menu_order']          = (int) $post->menu_order;

		$author = get_userdata( $post->post_author );
		if ( $author ) {
			$shared_attributes['post_author'] = array(
				'user_id'      => (int) $post->post_author,
				'display_name' => $author->display_name,
				'user_url'     => $author->user_url,
				'user_login'   => $author->user_login,
			);
		}

		$shared_attributes['images'] = Algolia_Utils::get_post_images( $post->ID );

		$shared_attributes['permalink']      = get_permalink( $post );
		$shared_attributes['post_mime_type'] = $post->post_mime_type;

		// Push all taxonomies by default, including custom ones.
		$taxonomy_objects = get_object_taxonomies( $post->post_type, 'objects' );

		$shared_attributes['taxonomies']              = array();
		$shared_attributes['taxonomies_hierarchical'] = array();
		foreach ( $taxonomy_objects as $taxonomy ) {

			$terms = wp_get_object_terms( $post->ID, $taxonomy->name );
			$terms = is_array( $terms ) ? $terms : array();

			if ( $taxonomy->hierarchical ) {
				$hierarchical_taxonomy_values = Algolia_Utils::get_taxonomy_tree( $terms, $taxonomy->name );
				if ( ! empty( $hierarchical_taxonomy_values ) ) {
					$shared_attributes['taxonomies_hierarchical'][ $taxonomy->name ] = $hierarchical_taxonomy_values;
				}
			}

			$taxonomy_values = wp_list_pluck( $terms, 'name' );
			if ( ! empty( $taxonomy_values ) ) {
				$shared_attributes['taxonomies'][ $taxonomy->name ] = $taxonomy_values;
			}
		}

		$shared_attributes['is_sticky'] = is_sticky( $post->ID ) ? 1 : 0;

		if ( 'attachment' === $post->post_type ) {
			$shared_attributes['alt'] = get_post_meta( $post->ID, '_wp_attachment_image_alt', true );

			$metadata = get_post_meta( $post->ID, '_wp_attachment_metadata', true );
			$metadata = (array) apply_filters( 'wp_get_attachment_metadata', $metadata, $post->ID );

			$shared_attributes['metadata'] = $metadata;
		}

		$shared_attributes = (array) apply_filters( 'algolia_post_shared_attributes', $shared_attributes, $post );
		$shared_attributes = (array) apply_filters( 'algolia_post_' . $post->post_type . '_shared_attributes', $shared_attributes, $post );

		return $shared_attributes;
	}

	/**
	 * @return array
	 */
	protected function get_settings() {
		$settings = array(
			'attributesToIndex'     => array(
				'unordered(post_title)',
				'unordered(taxonomies)',
				'unordered(content)',
			),
			'customRanking'         => array(
				'desc(is_sticky)',
				'desc(post_date)',
				'asc(record_index)',
			),
			'attributeForDistinct'  => 'post_id',
			'distinct'              => true,
			'attributesForFaceting' => array(
				'taxonomies',
				'taxonomies_hierarchical',
				'post_author.display_name',
			),
			'attributesToSnippet'   => array(
				'post_title:30',
				'content:30',
			),
			'snippetEllipsisText'   => '…',
		);

		$settings = (array) apply_filters( 'algolia_posts_index_settings', $settings, $this->post_type );
		$settings = (array) apply_filters( 'algolia_posts_' . $this->post_type . '_index_settings', $settings );

		return $settings;
	}

	/**
	 * @return array
	 */
	protected function get_synonyms() {
		$synonyms = (array) apply_filters( 'algolia_posts_index_synonyms', array(), $this->post_type );
		$synonyms = (array) apply_filters( 'algolia_posts_' . $this->post_type . '_index_synonyms', $synonyms );

		return $synonyms;
	}

	/**
	 * @param int $post_id
	 * @param int $record_index
	 *
	 * @return string
	 */
	private function get_post_object_id( $post_id, $record_index ) {
		return $post_id . '-' . $record_index;
	}

	/**
	 * @param mixed $item
	 * @param array $records
	 */
	protected function update_records( $item, array $records ) {
		$this->update_post_records( $item, $records );
	}

	/**
	 * @param WP_Post $post
	 * @param array   $records
	 */
	private function update_post_records( WP_Post $post, array $records ) {
		// If there are no records, parent `update_records` will take care of the deletion.
		// In case of posts, we ALWAYS need to delete existing records.
		if ( ! empty( $records ) ) {
			$this->delete_item( $post );
		}

		parent::update_records( $post, $records );

		// Keep track of the new record count for future updates relying on the objectID's naming convention .
		$new_records_count = count( $records );
		$this->set_post_records_count( $post, $new_records_count );

		do_action( 'algolia_posts_index_post_updated', $post, $records );
		do_action( 'algolia_posts_index_post_' . $post->post_type . '_updated', $post, $records );
	}

	/**
	 * @return string
	 */
	public function get_id() {
		return 'posts_' . $this->post_type;
	}

	/**
	 * @return int
	 */
	protected function get_re_index_items_count() {
		$query = new WP_Query(
			array(
				'post_type'        => $this->post_type,
				'post_status'      => 'any', // Let the `should_index` take care of the filtering.
				'suppress_filters' => true,
			)
		);

		return (int) $query->found_posts;
	}

	/**
	 * @param int $page
	 * @param int $batch_size
	 *
	 * @return array
	 */
	protected function get_items( $page, $batch_size ) {
		$query = new WP_Query(
			array(
				'post_type'        => $this->post_type,
				'posts_per_page'   => $batch_size,
				'post_status'      => 'any',
				'order'            => 'ASC',
				'orderby'          => 'ID',
				'paged'            => $page,
				'suppress_filters' => true,
			)
		);

		return $query->posts;
	}

	/**
	 * @param mixed $item
	 */
	public function delete_item( $item ) {
		$this->assert_is_supported( $item );

		$records_count = $this->get_post_records_count( $item->ID );
		$object_ids    = array();
		for ( $i = 0; $i < $records_count; $i++ ) {
			$object_ids[] = $this->get_post_object_id( $item->ID, $i );
		}

		if ( ! empty( $object_ids ) ) {
			$this->get_index()->deleteObjects( $object_ids );
		}
	}

	/**
	 * @param int $post_id
	 *
	 * @return int
	 */
	private function get_post_records_count( $post_id ) {
		return (int) get_post_meta( (int) $post_id, 'algolia_' . $this->get_id() . '_records_count', true );
	}
	/**
	 * @param WP_Post $post
	 * @param int     $count
	 */
	private function set_post_records_count( WP_Post $post, $count ) {
		update_post_meta( (int) $post->ID, 'algolia_' . $this->get_id() . '_records_count', (int) $count );
	}
}
