<?php

final class Algolia_Posts_Index extends Algolia_Index
{
	/**
	 * @var string
	 */
	private $post_type;

	protected $contains_only = 'posts';

	/**
	 * @param string $post_type
	 */
	public function __construct( $post_type )
	{
		$this->post_type = (string) $post_type;
	}

	/**
	 * @param mixed $task_data
	 *
	 * @return bool
	 */
	public function supports( $task_data ) {
		return isset( $task_data['post_type'] ) && $task_data['post_type'] === $this->post_type;
	}

	/**
	 * @return string The name displayed in the admin UI.
	 */
	public function get_admin_name()
	{
		$post_type = get_post_type_object( $this->post_type );
		if( null === $post_type ) {
			throw new RuntimeException( 'Unable to fetch the post type information.' );
		}

		return $post_type->labels->name;
	}

	/**
	 * @param $item
	 *
	 * @return bool
	 */
	protected function should_index( $item )
	{
		return $this->should_index_post( $item );
	}

	/**
	 * @param WP_Post $post
	 *
	 * @return bool
	 */
	private function should_index_post( WP_Post $post ) {
		if ( ! $this->post_type === $post->post_type ) {
			return false;
		}

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
	protected function get_records( $item )
	{
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

		$post_content = apply_filters( 'the_content', $post->post_content );

		$parser = new \Algolia\DOMParser();

		$parser->setExcludeSelectors( array(
			'pre',
		) );
		$parser->setSharedAttributes( $shared_attributes );

		apply_filters( 'algolia_post_parser', $parser );

		$records = $parser->parse( $post_content );

		// Inject the objectID's.
		foreach ( $records as $i => &$record ) {
			$record['objectID'] = $this->get_post_object_id( $post->ID, $i );
		}

		$records = (array) apply_filters( 'algolia_post_records', $records, $post );
		$records = (array) apply_filters( 'algolia_post_' . $post->post_type . '_records', $records, $post );

		return $records;
	}

	/**
	 * @return int
	 */
	protected function get_re_index_batch_size() {
		return (int) apply_filters( 'algolia_posts_indexing_batch_size', 10 );
	}

	/**
	 * @param WP_Post $post
	 *
	 * @return array
	 */
	private function get_post_shared_attributes( WP_Post $post ) {
		$shared_attributes = array();
		$shared_attributes['post_id'] = $post->ID;
		$shared_attributes['post_type'] = $post->post_type;
		
		$post_type = get_post_type_object( $post->post_type );
		if( null === $post_type ) {
			throw new RuntimeException( 'Unable to fetch the post type information.' );
		}
		$shared_attributes['post_type_label'] = $post_type->labels->name;
		$shared_attributes['post_title'] = $post->post_title;
		$shared_attributes['post_excerpt'] = $post->post_excerpt;
		$shared_attributes['post_date'] = get_post_time( 'U', false, $post );
		$shared_attributes['post_date_formatted'] = get_the_date( '', $post );
		$shared_attributes['post_modified'] = get_post_modified_time( 'U', false, $post );
		$shared_attributes['comment_count'] = (int) $post->comment_count;

		$author = get_userdata( $post->post_author );
		if ( $author ) {
			$shared_attributes['post_author'] = array(
				'user_id'       => (int) $post->post_author,
				'display_name'  => $author->display_name,
				'user_url'      => $author->user_url,
				'user_login'    => $author->user_login,
			);
		}

		// We did not use get_the_post_thumbnail_url because not available prior to WP 4.4.
		$thumbnail_url = '';
		$post_thumbnail_id = get_post_thumbnail_id( $post->ID );
		if ( $post_thumbnail_id ) {
			$thumbnail_url = wp_get_attachment_thumb_url( $post_thumbnail_id );
		}

		$shared_attributes['thumbnail_url'] = $thumbnail_url;
		$shared_attributes['permalink'] = get_permalink( $post );
		$shared_attributes['post_mime_type'] = $post->post_mime_type;

		$post_tags = get_the_terms( $post->ID, 'post_tag' );
		$post_tags = is_array( $post_tags ) ? $post_tags : array();
		$shared_attributes['taxonomy_post_tag'] = wp_list_pluck( $post_tags, 'name' );

		$categories = get_the_terms( $post->ID, 'category' );
		$categories = is_array( $categories ) ? $categories : array();
		$shared_attributes['taxonomy_category'] = wp_list_pluck( $categories, 'name' );
		$shared_attributes['category_tree'] = $this->get_category_tree( $categories );

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
	 * Returns an array like:
	 * array(
	 *	'lvl0' => ['Sales', 'Marketing'],
	 *  'lvl1' => ['Sales > Strategies', 'Marketing > Tips & Tricks']
	 * 	...
	 * );
	 *
	 * This is useful when building hierarchical menus.
	 * @see https://community.algolia.com/instantsearch.js/documentation/#hierarchicalmenu
	 *
	 * @param array $categories
	 *
	 * @return array
	 */
	protected function get_category_tree( array $categories ) {
		$termIds = wp_list_pluck( $categories, 'term_id' );

		$parents = [];
		foreach ( $termIds as $termId ) {

			$path = get_category_parents( $termId, false, ' > ' );
			$parents[] = rtrim( $path, ' >' );
		}

		$categories = [];
		foreach ( $parents as $parent ) {
			$levels = explode( ' > ', $parent );

			$previousLvl = '';
			foreach ( $levels as $index => $level ) {
				$categories[ 'lvl' . $index ][] = $previousLvl . $level;
				$previousLvl .= $level . ' > ';

				// Make sure we have not duplicate.
				// The call to `array_values` ensures that we do not end up with an object in JSON.
				$categories[ 'lvl' . $index ] = array_values( array_unique( $categories[ 'lvl' . $index ] ) );
			}
		}

		return $categories;
	}
	
	/**
	 * @return array
	 */
	protected function get_settings() {
		$settings = array(
			'attributesToIndex' => array(
				'unordered(post_title)',
				'unordered(title1)',
				'unordered(title2)',
				'unordered(title3)',
				'unordered(title4)',
				'unordered(title5)',
				'unordered(title6)',
				'unordered(content)',
			),
			'customRanking' => array(
				'desc(is_sticky)',
				'desc(post_date)',
			),
			'attributeForDistinct'  => 'post_id',
			'distinct'              => true,
			'attributesForFaceting' => array(
				'taxonomy_post_tag',
				'taxonomy_category',
				'post_author.display_name',
				'category_tree',
			),
			'attributesToSnippet' => array(
				'post_title:30',
				'title1:30',
				'title2:30',
				'title3:30',
				'title4:30',
				'title5:30',
				'title6:30',
				'content:30',
			),
			'snippetEllipsisText' => '…',
		);

		$settings = (array) apply_filters( 'algolia_posts_index_settings', $settings, $this->post_type );
		$settings = (array) apply_filters( 'algolia_posts_' . $this->post_type . '_index_settings', $settings );

		return $settings;
	}

	/**
	 * @return array
	 */
	protected function get_synonyms()
	{
		$synonyms = (array) apply_filters( 'algolia_posts_index_synonyms', array(), $this->post_type );
		$synonyms = (array) apply_filters( 'algolia_posts_' . $this->post_type . '_index_synonyms', $synonyms );

		return $synonyms;
	}

	/**
	 * @param int $post_id
	 * @param int $current_records_count
	 * @param int $new_records_count
	 */
	private function remove_post_records( $post_id, $current_records_count, $new_records_count = 0 ) {
		// Find out the records that are no longer needed.
		$dirty_object_ids = array();
		for ( $i = $new_records_count; $i < $current_records_count; $i++ ) {
			$dirty_object_ids[] = $this->get_post_object_id( $post_id, $i );
		}

		// Remove the dirty records.
		if ( ! empty( $dirty_object_ids ) ) {
			$index = $this->get_index();
			$index->deleteObjects( $dirty_object_ids );
			$this->get_logger()->log_operation( sprintf( '[%d] Deleted %d records from index %s', count( $dirty_object_ids ), count( $dirty_object_ids ), $index->indexName ), $dirty_object_ids );
		}
	}

	/**
	 * @param int $post_id
	 * @param int $record_index
	 *
	 * @return string
	 */
	private function get_post_object_id( $post_id, $record_index )
	{
		return $post_id . '-' . $record_index;
	}

	/**
	 * @param int $post_id
	 *
	 * @return int
	 */
	private function get_post_records_count( $post_id )
	{
		return (int) get_post_meta( (int) $post_id, 'algolia_' . $this->get_id() . '_records_count', true );
	}

	/**
	 * @param WP_Post $post
	 * @param int $count
	 */
	private function set_post_records_count( WP_Post $post, $count ) {
		update_post_meta( (int) $post->ID, 'algolia_' . $this->get_id() . '_records_count', (int) $count );
	}

	/**
	 * @param mixed $item
	 * @param array $records
	 */
	protected function update_records( $item, array $records )
	{
		$this->update_post_records( $item, $records );
	}

	/**
	 * @param WP_Post $post
	 * @param array   $records
	 */
	private function update_post_records( WP_Post $post, array $records ) {
		$current_records_count = $this->get_post_records_count( $post->ID );
		$new_records_count = count( $records );

		// Remove dirty records.
		$this->remove_post_records( $post->ID, $current_records_count, $new_records_count );

		// Update the other records.
		parent::update_records( $post, $records );

		// Keep track of the new record count for future updates relying on the objectID's naming convention .
		$this->set_post_records_count( $post, $new_records_count );

		do_action( 'algolia_posts_index_post_updated', $post, $records );
		do_action( 'algolia_posts_index_post_' . $post->post_type . '_updated', $post, $records );
	}

	/**
	 * @return string
	 */
	public function get_id()
	{
		return 'posts_' . $this->post_type;
	}

	/**
	 * @return int
	 */
	protected function get_re_index_items_count()
	{
		$query = new WP_Query( array(
			'post_type'   		=> $this->post_type,
			'post_status' 		=> 'any', // Let the `should_index` take care of the filtering.
			'suppress_filters' 	=> true,
		) );

		return (int) $query->found_posts;
	}
	
	/**
	 * @param int $page
	 * @param int $batch_size
	 *
	 * @return array
	 */
	protected function get_items( $page, $batch_size )
	{
		$query = new WP_Query( array(
			'post_type'      	=> $this->post_type,
			'posts_per_page' 	=> $batch_size,
			'post_status'    	=> 'any',
			'order'          	=> 'ASC',
			'orderby'        	=> 'ID',
			'paged'			 	=> $page,
			'suppress_filters' 	=> true
		) );

		return $query->posts;
	}

	public function de_index_items()
	{
		parent::de_index_items();

		// Remove all the records count for the post type in one call.
		delete_post_meta_by_key( 'algolia_' . $this->get_id() . '_records_count' );
	}

	/**
	 * @param Algolia_Task $task
	 *
	 * @return mixed
	 */
	protected function extract_item(Algolia_Task $task)
	{
		$data = $task->get_data();
		if ( ! isset( $data['post_id'] ) ) {
			return;
		}
			
		return get_post( $data['post_id'] );
	}
}
