---
title: Filters Reference
description: The available filters and how to use them to customize the behaviour.
layout: page.html
---

## Introduction

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

| Filter Name                                              | Params                                                                           |
|:---------------------------------------------------------|:---------------------------------------------------------------------------------|
| algolia_autocomplete_config                              | $config                                                                          |
| algolia_post_types_blacklist                             | array $blacklist, defaults to array( 'nav_menu_item', revision' )                |
| algolia_taxonomies_blacklist                             | array $blacklist, defaults to array( 'nav_menu', link_category', 'post_format' ) |
| algolia_should_index_searchable_post                     | bool $should_index, WP_Post $post                                                |
| algolia_searchable_post_parser                           | Algolia\DOMParser $parser                                                        |
| algolia_searchable\_post\_{$post_type}_shared_attributes | array $shared_attributes, WP_Post $post                                          |
| algolia_searchable_posts_index_settings                  | array $settings                                                                  |
| algolia_searchable_post_shared_attributes                | array $shared_attributes, WP_Post $post                                          |
| algolia_searchable_posts_index_synonyms                  | array $synonyms                                                                  |
| algolia_should_index_post                                | bool $should_index, WP_Post $post                                                |
| algolia_post_parser                                      | Algolia\DOMParser $parser                                                        |
| algolia_post_shared_attributes                           | array $shared_attributes, WP_Post $post                                          |
| algolia\_post\_{$post_type}_shared_attributes            | array $shared_attributes, WP_Post $post                                          |
| algolia_posts_index_settings                             | array $settings, string $post_type                                               |
| algolia\_posts\_{$post_type}_index_settings              | array $settings                                                                  |
| algolia_posts_index_synonyms                             | array $synonyms, string $post_type                                               |
| algolia\_posts\_{$post_type}_index_synonyms              | array $synonyms                                                                  |
| algolia_should_index_term                                | bool $should_index, WP_Term/object $term                                         |
| algolia_term_record                                      | array $record, WP_Term/object$term                                               |
| algolia\_term\_{$taxonomy}_record                        | array $record, WP_Term/object $term                                              |
| algolia_terms_index_settings                             | array $settings                                                                  |
| algolia\_terms\_{$taxonomy}_index_settings               | array $settings                                                                  |
| algolia_terms_index_synonyms                             | array $synonyms, string $taxonomy                                                |
| algolia\_terms\_{$taxonomy}_index_synonyms               | array $synonyms                                                                  |
| algolia_should_index_user                                | bool $should_index, WP_User $user                                                |
| algolia_user_record                                      | array $record, WP_User $user                                                     |
| algolia_users_index_settings                             | array $settings                                                                  |
| algolia_users_index_synonyms                             | array $synonyms                                                                  |
| algolia_index_replicas                                   | array $replicas, Algolia_Index $index                                            |
| algolia\_{$index_id}_index_replicas                      | array $replicas, Algolia_Index $index                                            |
| algolia_config                                           | array $config                                                                    |
| algolia_indexing_batch_size                              | int $batch_size (default: 100)                                                    |
| algolia\_{$index_id}_indexing_batch_size                 | int $batch_size                                                                  |
| algolia_templates_path                                   | string $path (default: 'algolia/')                                               |
| algolia_template_locations                               | array $locations                                                                 |
| algolia_default_template                                 | string $template, string $file                                                   |
| algolia_search_params                                    | array $params                                                                    |
| algolia_should_override_search_with_instantsearch        | bool $bool (default: depending on configuration)                                 |
| algolia_post_images_sizes                                | array $sizes (default: only the 'thumbnail' size)                                |
| algolia_get_post_images                                  | array $images (default: only the info about the 'thumbnail' size)                |
| algolia_get_synced_indices_ids                           | array $ids                                                                       |
