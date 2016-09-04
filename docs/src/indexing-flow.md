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

|Content Type|Rule
|-|-
|Searchable Post|We only index a post when it is in the `published` state and if its post type is not `excluded_from_search`.
|Post|We only index a post when it is in the `published` state if it has no password.
|Term|We only index a term if it has been assigned to at least 1 post (count > 0).
|User|We index a user if he has authored at least 1 post.

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



## Queue processing

Every time a change of your content is detected, see [previous section](#triggers), we queue a synchronization task with the minimum required information for the task to be handled.

When that happens, we directly try to trigger the processing of the queue. The queue follows the FIFO logic, and ensures there is only one task handled at the same time. For scalability reasons, each task handling is done in its own PHP process and triggered by an asynchronous http call.

<div class="alert alert-warning">By default WordPress will ensure that your ssl certificate is valid on every remote call. If you have an invalid ssl certificate, the queue processing won't work.</div>

## Queue processing troubleshooting

Queue processing is the most critical part of the plugin. If the queue processing doesn't work, then the whole plugin becomes un-usable.

If you have queue processing problems, please:
- Make sure you take advantage of built in logs and WordPress logs, see the [Debugging](logs.html) section of the docs,
- Check out the [FAQ](frequently-asked-questions.html) for answers,
- If you don't manage to solve the issue by yourself, ask for help, see: [FAQ](frequently-asked-questions.html).
