---
title: Automated Synchronization
description: Understand the indexing flow and the generated operations.
layout: page.html
---

## Introduction

By default, this plugin will ensure your WordPress content flagged for indexing stays in sync with Algolia.

<div class="alert alert-info">This behaviour does not rely on CRON, but uses a custom private post_type `algolia_task`. </div>

We support the automatic synchronization of 3 types of content:
- Post Types,
- Taxonomies,
- Users.

Each content type has its own indexing flow.

## Triggers

We try as much as possible to automate the indexing for you. That means that we listen for events like 'post was saved' to push the new data to Algolia.

**We synchronize posts when:**
- A post was created or updated,
- A post was deleted,
- The featured image was changed.

**We synchronize taxonomy terms when:**
- A term was created or updated,
- A term was deleted.

**We synchronize users when:**
- A user was created or updated,
- A term was deleted,
- A post was created or updated,
- A post was deleted.

<div class="alert alert-warning">We synchronize without trying to detect if the updates has lead to real changes. This ensures consistency and easier extensibility.</div>

## Indexing Decision

The existence of an indexing task, does not necessarily mean that the data will be pushed to Algolia.

Indeed, before each post, term and user indexing, we decide if the item should be indexed or not. If it should, then we re-push the data for the item otherwise we remove it from the index.

**Default decisions:**

| Content Type    | Rule                                                                                                         |
|-----------------|--------------------------------------------------------------------------------------------------------------|
| Searchable Post | We only index a post when it is in the `published` state and if its post type is not `excluded_from_search`. |
| Post            | We only index a post when it is in the `published` state if it has no password.                              |
| Term            | We only index a term if it has been assigned to at least 1 post (count > 0).                                 |
| User            | We index a user if he has authored at least 1 post.                                                          |

You can hook into the indexing decision making for searchable posts, posts, terms and users by using respectively the `algolia_should_index_searchable_post`, `algolia_should_index_post`, `algolia_should_index_term` and `algolia_should_index_user` filters.

**Here is an example for filtering user indexing:**

```php
<?php
/**
 * @param bool $should_index
 * @param WP_User $user
 *
 * @return bool
 */
function filter_user( $should_index, $user ) {
	if ( false === $should_index ) {
		return false;
	}

	return $user->ID !== 1;
}

add_filter( 'algolia_should_index_user', 'filter_user', 10, 2 );
```

In the above example, User with ID 1 would never get indexed. This example is trivial, but at least gives you a quick overview on how to filter your items to index.
Also note how we return `false` early on if the decision has already been taken to not index the content.

**Another example to exclude posts from the searchable_posts index where the noindex option from the Yoast SEO plugin is set to "noindex"**

<div class="alert alert-warning">This example assumes you are using the Yoast SEO plugin for WordPress</div>

```php
<?php
/**
 * Don't index pages where the robot index option
 * in the Yoast SEO plugin is set to noindex.
 *
 * @param bool    $should_index
 * @param WP_Post $post
 *
 * @return bool
 */
function filter_post( $should_index, WP_Post $post )
{
    if ( false === $should_index ) {
        return false;
    }

    return get_post_meta($post->ID, '_yoast_wpseo_meta-robots-noindex', true) == 1 ? false : true;
}

// Hook into Algolia to manipulate the post that should be indexed.
add_filter( 'algolia_should_index_searchable_post', 'filter_post', 10, 2 );
```

**Exclude post types from `searchable_posts_index`**

In this example we exclude the `page` post type from the `searchable_posts` index.

```php
<?php
/**
 * @param bool    $should_index
 * @param WP_Post $post
 *
 * @return bool
 */
function exclude_post_types( $should_index, WP_Post $post )
{
    // Add all post types you don't want to make searchable.
    $excluded_post_types = array( 'page' );
    if ( false === $should_index ) {
        return false;
    }

    return ! in_array( $post->post_type, $excluded_post_types, true );
}

// Hook into Algolia to manipulate the post that should be indexed.
add_filter( 'algolia_should_index_searchable_post', 'exclude_post_types', 10, 2 );
```

**Add post type to `searchable_posts_index`**

By default, the plugin will only index post types that are not flagged as excluded from search.

If you want to manually determine the post types you want to index, you can use the `algolia_searchable_post_types` filter:

```php
add_filter( 'algolia_searchable_post_types', function( $post_types ) {
    $post_types[] = 'custom_post_type';

    return $post_types;
} );
```

**Exclude a post by it's ID**

The following snippets skips the post with ID `18517`.

```php
<?php

function filter_post( $should_index, WP_Post $post )
{
    if ( 18517 === $post->ID ) {
        return false;
    }

    return $should_index;
}

// Hook into Algolia to manipulate the post that should be indexed.
add_filter( 'algolia_should_index_searchable_post', 'filter_post', 10, 2 );
add_filter( 'algolia_should_index_post', 'filter_post', 10, 2 );
```


## Queue processing

Every time a change of your content is detected, see [previous section](#triggers), we synchronize the item.

You can disable automatic sychronization by removing all the watchers using the `algolia_changes_watchers` filter:

```php
add_filter( 'algolia_changes_watchers', function() {
    return array();
} );
```
