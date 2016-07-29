<?php
/*
Plugin Name: Visits Matters
*/

function vm_increment_post_counter() {
	if ( ! is_singular() ) {
		return;
	}

	$post = get_post();
	vm_increment_post_visit_count( $post->ID );
}

function vm_get_post_visit_count( $post_id ) {
	return (int) get_post_meta( $post_id, 'vm_visits_counter', true );
}

function vm_increment_post_visit_count( $post_id ) {
	$visits_count = vm_get_post_visit_count( $post_id );
	update_post_meta( $post_id, 'vm_visits_counter', (string) ++$visits_count, (string) --$visits_count);
}

add_filter( 'wp', 'vm_increment_post_counter' );


function vm_post_shared_attributes( array $shared_attributes, WP_Post $post) {
	$shared_attributes['visits_count'] = vm_get_post_visit_count( $post->ID );

	return $shared_attributes;
}
add_filter( 'algolia_post_shared_attributes', 'vm_post_shared_attributes', 10, 2 );


function vm_posts_index_settings( array $settings ) {
	$custom_ranking = $settings['customRanking'];
	array_unshift( $custom_ranking, 'desc(visits_count)' );
	$settings['customRanking'] = $custom_ranking;

	// Protect our sensitive data.
	$protected_attributes = array();
	if ( isset( $settings['unretrievableAttributes'] ) ) {
		// Ensure we merge our values with the existing ones if available.
		$protected_attributes = $settings['unretrievableAttributes'];
	}
	$protected_attributes[] = 'visits_count';
	$settings['unretrievableAttributes'] = $protected_attributes;

	return $settings;
}

add_filter( 'algolia_posts_index_settings', 'vm_posts_index_settings' );


// Queues re-indexation of every post type.
function vm_re_index_posts() {
	/** @var Algolia_Plugin $algolia */
	global $algolia;

	$task_queue = $algolia->get_task_queue();

	$indices = $algolia->get_indices( array( 
		'enabled' => true,
		'contains' => 'posts',
	) );
	foreach ( $indices as $index ) {
		$task_queue->queue( 're_index_items', array( 'index_id' => $index->get_id() ) );
	}
}
// This action is required for wp_schedule_event binding.
add_action( 'vm_re_index_posts', 'vm_re_index_posts' );


// Registers the recurring vm_re_index_posts event.
function wp_register_re_index_posts() {
	if ( ! wp_next_scheduled( 'vm_re_index_posts' ) ) {
		wp_schedule_event( time(), 'daily', 'vm_re_index_posts' );
	}
}

// Only register the event on WordPress init.
add_action( 'init', 'wp_register_re_index_posts' );
