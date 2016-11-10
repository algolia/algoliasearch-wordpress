---
title: Actions Reference
description: The available actions and how to use them.
layout: page.html
---

## Introduction

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
|algolia_autocomplete_scripts|*none*
|algolia_instantsearch_scripts|*none*
|algolia_before_handle_task|Algolia_Task $task
|algolia_task_handled|Algolia_Task $task

