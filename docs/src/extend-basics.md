---
title: The basics for extending Algolia Search for WordPress
description: Discover the best way to extend the plugin to customize the behaviour.
layout: page.html
---
## How To Extend

The plugin has been built with extensibility in mind. We have tried to wisely integrate `filters` and `actions`.

<div class="alert alert-warning">You should never modify files inside the `wp-content/plugins` directory. If you find yourself in a situation where you require to hack the plugin, let us know so that we can improve the plugin.</div>

## Create your Custom Plugin

Your first step is to create your own plugin. You can find [detailed explanation on how to create a WordPress plugin here](https://developer.wordpress.org/plugins/the-basics/).

You can also simply create a file `my-blog.php` like this:

```php
<?php
/*
Plugin Name: My Blog
*/
```

Then you should be able to enable that plugin from your WordPress admin panel.

You can now hook into the Algolia Search for WordPress plugin!

## Extend from a theme

Every theme contains a `functions.php` file where you can also extend the Algolia Search plugin.

If you choose to use your theme files to extend the plugin, please consider creating a child theme so that you can update your theme
without loosing your changes.

You can read more about [child themes in the official documentation](https://codex.wordpress.org/Child_Themes).

## Filters

Filters are a way to alter data during the lifecycle of a request. The idea is simple: you listen for a filter to be called by using `add_filter`, you edit your data and return it modified.

## Filter Example

The plugin has a blacklist mechanism to not display certain post types in the admin UI.
By default, we disabled the following post types to appear:
- nav_menu_item
- revision
- algolia_task
- algolia_log
- kr_request_token
- kr_access_token
- deprecated_log
- async-scan-result
- scanresult


Let's say that you have a your plugin introducing some custom content types, but these are meant to be used internally and you don't want to expose them for indexing.

```php
<?php

function mb_blacklist_custom_post_type( array $blacklist ) {
	$blacklist[] = 'custom_post_type';

	return $blacklist;
}

add_filter( 'algolia_post_types_blacklist', 'mb_blacklist_custom_post_type' );
```

Your custom_post_type will no longer appear for indexing in the indexing screen.
This will also disable tasks being generated for that content type.

## Filters Reference

Here is the list of all available Filters.

|Filter Name|Params
|-|-|
|algolia_autocomplete_config|$config
|algolia_post_types_blacklist|array $blacklist, defaults to array( 'nav_menu_item', revision' )
|algolia_taxonomies_blacklist|array $blacklist, defaults to array( 'nav_menu', link_category', 'post_format' )
|algolia_should_index_searchable_post|bool $should_index, WP_Post $post
|algolia_searchable_post_parser|Algolia\DOMParser $parser
|algolia_searchable\_post\_{$post_type}_shared_attributes|array $shared_attributes, WP_Post $post
|algolia_searchable_posts_index_settings|array $settings
|algolia_searchable_post_shared_attributes|array $shared_attributes, WP_Post $post
|algolia_searchable_posts_index_synonyms|array $synonyms
|algolia_should_index_post|bool $should_index, WP_Post $post
|algolia_post_parser|Algolia\DOMParser $parser
|algolia_post_shared_attributes|array $shared_attributes, WP_Post $post
|algolia\_post\_{$post_type}_shared_attributes|array $shared_attributes, WP_Post $post
|algolia_posts_index_settings|array $settings, string $post_type
|algolia\_posts\_{$post_type}_index_settings|array $settings
|algolia_posts_index_synonyms|array $synonyms, string $post_type
|algolia\_posts\_{$post_type}_index_synonyms|array $synonyms
|algolia_should_index_term|bool $should_index, WP_Term/object $term
|algolia_term_record|array $record, WP_Term/object$term
|algolia\_term\_{$taxonomy}_record|array $record, WP_Term/object $term
|algolia_terms_index_settings|array $settings
|algolia\_terms\_{$taxonomy}_index_settings|array $settings
|algolia_terms_index_synonyms|array $synonyms, string $taxonomy
|algolia\_terms\_{$taxonomy}_index_synonyms|array $synonyms
|algolia_should_index_user|bool $should_index, WP_User $user
|algolia_user_record|array $record, WP_User $user
|algolia_users_index_settings|array $settings
|algolia_users_index_synonyms|array $synonyms
|algolia_config|array $config
|algolia_autocomplete_templates|array $templates
|algolia_task_queue_lock_ttl|int $ttl
|algolia_searchable_posts_indexing_batch_size|int $batch_size
|algolia_posts_indexing_batch_size|int $batch_size
|algolia_terms_indexing_batch_size|int $batch_size
|algolia_users_indexing_batch_size|int $batch_size
|algolia_logs_per_page|int $logs_per_page


## Actions

Actions are a way to execute something at a given point in time or after something happened.

## Action Example

Let's say we want to log every post indexing task.

```php
<?php

function mb_log_posts_index_updated( $post ) {
	$log = print_r( $post->to_array(), true );
	file_put_contents( '../mb_logs.txt', $log, FILE_APPEND );
}

add_action( 'algolia_posts_index_post_updated', 'mb_log_post_index_updated' );
```

Note that an action does not need to return anything. If it does, it will be ignored anyway.

In this example, we would simply log an array representation of the post that has been re-indexed.

## Actions Reference

Here is the list of all available Actions.

|Action Name|Params
|-|-|
|algolia_re_indexed_items|string $index_id
|algolia_de_indexed_items|string $index_id
|algolia_autocomplete_assets|*none*
|algolia_task_handled|WP_Post $task

