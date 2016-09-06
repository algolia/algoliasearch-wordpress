---
title: WPML integration with Algolia Search
description: Integrate Algolia Search for WordPress with WPML.
layout: page.html
---

## Introduction

WPML is a very famous plugin that makes managing multilingual WordPress websites easy.

On this page you we will give some examples on how to push language related data along with your content.

## Push WPML language data to Algolia

In the following example, we add the `wpml` field to the posts.

```php
<?php

/**
 * @param array   $shared_attributes
 * @param WP_Post $post
 *
 * @return array
 */
function my_post_shared_attributes( array $shared_attributes, WP_Post $post ) {
	// Here we make sure we push the post's language data to Algolia.
	$shared_attributes['wpml'] = apply_filters( 'wpml_post_language_details', null,  $post->ID );

	return $shared_attributes;
}

// Push WPML data for both posts and searchable posts indices.
add_filter( 'algolia_post_shared_attributes', 'my_post_shared_attributes', 10, 2 );
add_filter( 'algolia_searchable_post_shared_attributes', 'my_post_shared_attributes', 10, 2 );

```

**Do not forget to hit the 're-index all' button after making such changes to indices configuration.**

## Prepare your indices for filtering based on language code

In the previous section you learned you to [push language data to Algolia](#push-wpml-language-data-to-algolia).

Now what you will probably want at some point is to scope your search on given language codes.

To be able to filter on language codes, we first need to tell Algolia that `wpml.language_code` should be an attribute to consider for faceting.

```php
<?php

/**
 * @param array $settings
 *
 * @return array
 */
function my_posts_index_settings( array $settings ) {
	// We add the language code to the facets to be able to easily filter on it.
	$settings['attributesForFaceting'][] = 'wpml.language_code';

	return $settings;
}

add_filter( 'algolia_posts_index_settings', 'my_posts_index_settings' );
add_filter( 'algolia_searchable_posts_index_settings', 'my_posts_index_settings' );

```

<div class="alert alert-warning">Do not forget to hit the 're-index all' button after making such changes to indices configuration.</div>


