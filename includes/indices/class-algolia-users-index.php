<?php

final class Algolia_Users_Index extends Algolia_Index
{
	/**
	 * @var string
	 */
	protected $contains_only = 'users';

	/**
	 * @return string The name displayed in the admin UI.
	 */
	public function get_admin_name()
	{
		return __( 'Users' );
	}

	/**
	 * @param $item
	 *
	 * @return bool
	 */
	protected function should_index( $item )
	{
		if ( function_exists( 'wpcom_vip_count_user_posts' ) ) {
			$should_index = (int) wpcom_vip_count_user_posts( $item->ID ) > 0;
		} else {
			$should_index = (int) count_user_posts( $item->ID ) > 0;
		}

		return (bool) apply_filters( 'algolia_should_index_user', $should_index, $item );
	}

	/**
	 * @param $item
	 *
	 * @return array
	 */
	protected function get_records( $item )
	{
		$record = array();
		$record['objectID'] = $item->ID;
		$record['user_id'] = $item->ID;
		$record['display_name'] = $item->display_name;
		$record['posts_url'] = get_author_posts_url( $item->ID, $item->user_nicename );
		$record['description'] = get_the_author_meta( 'description', $item->ID );

		if ( function_exists( 'wpcom_vip_count_user_posts' ) ) {
			$record['posts_count'] = (int) wpcom_vip_count_user_posts( $item->ID );
		} else {
			$record['posts_count'] = (int) count_user_posts( $item->ID );
		}

		$avatar_size = 32;
		if ( function_exists( 'get_avatar_url' ) ) {
			$record['avatar_url'] = get_avatar_url( $item->ID, array( 'size' => $avatar_size ) );
		} else {
			$email_hash = md5( strtolower( trim( $item->user_email ) ) );
			$record['avatar_url'] = 'https://www.gravatar.com/avatar/' . $email_hash . '?s=' . $avatar_size;
		}

		$record = (array) apply_filters( 'algolia_user_record', $record, $item );
		
		return array( $record );
	}

	/**
	 * @return int
	 */
	protected function get_re_index_items_count()
	{
		$users_count = count_users();
		
		return (int) $users_count['total_users'];
	}

	/**
	 * @return array
	 */
	protected function get_settings()
	{
		$settings = array(
			'attributesToIndex' => array(
				'unordered(display_name)',
			),
			'customRanking' => array(
				'desc(posts_count)',
			),
		);

		return (array) apply_filters( 'algolia_users_index_settings', $settings );
	}

	/**
	 * @return array
	 */
	protected function get_synonyms()
	{
		return (array) apply_filters( 'algolia_users_index_synonyms', array() );
	}

	/**
	 * @return string
	 */
	public function get_id()
	{
		return 'users';
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
			'orderby'      => 'ID',
			'offset'       => $offset,
			'number'	      => $batch_size,
		);

		// We use prior to 4.5 syntax for BC purposes, no `paged` arg.
		return get_users( $args );
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
	public function supports($task_data)
	{
		return true;
	}

	/**
	 * @param Algolia_Task $task
	 *
	 * @return mixed
	 */
	protected function extract_item( Algolia_Task $task )
	{
		$data = $task->get_data();
		if ( ! isset( $data['user_id'] ) ) {
			return;
		}
		
		$user = get_user_by( 'id', $data['user_id'] );
		
		return  ! $user ? null : $user ;
	}

	public function get_default_autocomplete_config() {
		$config = array(
			'position'        => 30,
			'max_suggestions' => 3,
			'tmpl_suggestion' => 'autocomplete-user-suggestion',
		);

		return array_merge( parent::get_default_autocomplete_config(), $config );
	}

	/**
	 * @param Algolia_Task $task
	 */
	public function delete_item( Algolia_Task $task ) {
		$data = $task->get_data();
		if ( ! isset( $data['user_id'] ) || ! is_int( $data['user_id'] ) ) {
			return;
		}

		$index = $this->get_index();
		$index->deleteObject( $data['user_id'] );
		$this->get_logger()->log_operation( sprintf( '[1] Deleted 1 record from index %s', $index->indexName ) );
	}
}
