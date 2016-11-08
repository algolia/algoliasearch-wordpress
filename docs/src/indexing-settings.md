---
title: Indices Settings
description: Understand the indices default settings and how to customize the ranking.
layout: page.html
---

## Introduction

Algolia allows you to configure your indices to improve the relevancy of your search results.

Each index comes with its own customizable settings and [a complete list of the settings parameters can be found here](https://www.algolia.com/doc/php#index-settings).

This plugin automatically configures each index with some sensible default settings, that being said, you can of course [customize the settings by using some filters](#custom-index-settings).

<div class="alert alert-warning">Every time an index is re-indexed, the settings will be pushed and override existing ones. This means that you should not edit your settings directly in the Algolia Dashboard. Instead, you should use `filters`  to hook into this plugin and alter the settings.</div>

## Settings Anatomy

Before telling you about the default settings per content type and how to customize them, let's have a quick overview of a few parameters.

**attributesToIndex**:
Refers to the attributes that the search will be based on. By default we will generally list here all the text attributes that the user would like to search in.
Unless we wrap our attribute name in `unordered()`, word match position matters, meaning that if a word is found earlier in the indexed attribute, it will be considered a better match.

**customRanking**
Internally, the Algolia Search engine uses a Tie breaking algorithm, which means that at every step of the search process, it will keep only the most relevant results by applying consequently every available ranking algorithm.
There are a lot of ranking strategies provided out of the box, like `typo`, `geo`, etc. If the search engine has more results to offer than expected, we consider as best practice to provide the engine with some custom metrics to take the best decisions.
In this plugin we provide some sensible defaults so that your search results are as accurate as possible. Feel free to add your custom business metrics.

**attributesForDistinct**
For the `post` content type we actually store multiple records for the same item. Why we do that is explained in [the index schema](index-schema.html).
Even though we have multiple records for a unique `post`, we only want to display one post as part of the result. For this we use the Algolia `attributeForDistinct` & `distinct` feature which is kind of similar to the SQL `DISTINCT` feature.
By using this feature, the search engine will only return the most relevant record for a given query.

**attributesForFaceting**
Algolia allows to prepare your records for faceting. Faceting is the feature allowing you for example to list all posts of a category.
Even though we do not yet provide out of the box usage of faceting in the plugin, we tried to already push some sensible default values for the `attributesForFaceting`.

<div class="alert alert-info">Feel free to dig further into the Algolia Search engine to optimize even more your search relevancy: [Relevancy in Algolia's documentation](https://www.algolia.com/doc#relevance).</div>

## Searchable Posts Index Settings

**Default searchable posts settings:**
```php
<?php
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
		'post_type_label',
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
```

## Posts Index Settings

**Default posts settings:**
```php
<?php
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
```

## Terms Index Settings

**Default terms settings:**
```php
<?php
$settings = array(
	'attributesToIndex' => array(
		'unordered(name)',
		'unordered(description)',
	),
	'customRanking' => array(
		'desc(posts_count)',
	),
);
```

## Users Index Settings

**Default users settings:**
```php
<?php
$settings = array(
	'attributesToIndex' => array(
		'unordered(display_name)',
	),
	'customRanking' => array(
        'desc(posts_count)',
    ),
);
```

## Custom Index Settings

You can customize the settings pushed to Algolia for every content type.
To do so simply use the available filters:

|Filter Name|Params
|-|-
|algolia_posts_index_settings|`$settings`, the posts index settings. `$post_type`, the post type, like "page" or "post".
|algolia\_posts\_{$post_type}_index_settings|`$settings`, the posts index settings.
|algolia_terms_index_settings|`$settings`, the terms index settings. `$taxonomy`, the taxonomy like "post_tag" or "category".
|algolia\_terms\_{$taxonomy}_index_settings|`$settings`, the terms index settings.
|algolia_users_index_settings|`$settings`, the users index settings.


## Custom Index Settings Example

Let's say you have some custom metric named `visits_count` and you would like to add a custom ranking rule on that one:

```php
<?php

function custom_posts_index_settings( array $settings ) {
	$custom_ranking = $settings['customRanking'];
	array_unshift( $custom_ranking, 'desc(visits_count)' );
	$settings['customRanking'] = $custom_ranking;

	return $settings;
}
add_filter( 'algolia_posts_index_settings', 'custom_posts_index_settings' );
```

Every post type having it's own index in Algolia, we can also determine custom settings per post type like:

```php
<?php

// Customize settings for pages only.
function custom_pages_index_settings( array $settings ) {
	/* Your adjustments */

	return $settings;
}
add_filter( 'algolia_posts_page_index_settings', 'custom_pages_index_settings' );

// OR customize for posts only.
function custom_posts_index_settings( array $settings ) {
    /* Your adjustments */

    return $settings;
}
add_filter( 'algolia_posts_post_index_settings', 'custom_posts_index_settings' );

```

## Register custom facet

Let's say you want to register a custom attribute as a facet `number_bedroom` in Algolia:

```php
<?php

function custom_posts_index_settings( array $settings ) {
	$settings['attributesForFaceting'][] = 'number_bedroom';

	return $settings;
}
add_filter( 'algolia_posts_index_settings', 'custom_posts_index_settings' );
```
