---
title: Polylang integration with Algolia Search
description: Integrate Algolia Search for WordPress with Polylang.
layout: page.html
---

## Introduction

[Polylang](https://wordpress.org/plugins/polylang/) helps WordPress users offer different content to their users 
depending on the locale of the displayed page.

In this guide you will learn how to filter the search results based on the current locale.

## Initial Setup

Before being able to filter on the locale, we need to ensure that:
* the locale is added to every post record in Algolia
* the locale attribute is considered as being a facet by adding it to the index settings.


Copy paste the following directly in the `functions.php` file of your active theme.

You can also create a new file like: `wp-content/plugins/mu-plugins/algolia-polylang.php`. 
This will automatically be picked up by WordPress and be run and has the benefit of 
not requiring you to change your theme files.

```php
<?php

// Add the locale of every post to every record of every post type indexed.
function add_locales_to_records( array $attrs, WP_Post $post ) {
    if (function_exists('pll_get_post_language')) {
        $attrs['locale'] = pll_get_post_language( $post->ID, "locale" );
    }
    return $attrs;
}
add_filter( 'algolia_post_shared_attributes', 'add_locales_to_records', 10, 2 );
add_filter( 'algolia_searchable_post_shared_attributes', 'add_locales_to_records', 10, 2 );

// Register the locale attribute as an Algolia facet which will allow us to filter on the current displayed locale.
function add_locale_to_facets( array $settings ) {
    $settings['attributesForFaceting'][] = 'locale';

    return $settings;
}
add_filter( 'algolia_searchable_posts_index_settings', 'add_locale_to_facets' );
add_filter( 'algolia_posts_index_settings', 'add_locale_to_facets' );

// Expose the current locale of the displayed page in JavaScript.
function enqueue_locale() {
    wp_add_inline_script( 'algolia-search', sprintf('var current_locale = "%s";', get_locale()), 'before' );
}
add_action( 'wp_enqueue_scripts', 'enqueue_locale', 99 );

```

**After you implemented the above code, you need to re-index every index you are using from the "Algolia Search -> Autocomplete" page.**

**You also need to hit the "Push Settings" button for every used index.**


## Filter search results page based on current locale

In the previous section you should have put in place all the logic required for you to filter your results 
based on the current locale.

Now what you need to do is tell InstantSearch.js and Autocomplete.js to "facet" on the "locale" attribute of your post records.

To do so you first need to [copy instantsearch.php template file in your own theme](https://community.algolia.com/wordpress/customize-search-page.html#customization) in order to customize it 
without changing the plugin's files.

Once that is done, you need to filter on the `locale` facet.
Add the `filters` line to your instantsearch initialization:

```js
/* Instantiate instantsearch.js */
var search = instantsearch({
	appId: algolia.application_id,
	apiKey: algolia.search_api_key,
	indexName: algolia.indices.searchable_posts.name,
	urlSync: {
		mapping: {'q': 's'},
		trackedParameters: ['query']
	},
	searchParameters: {
		facetingAfterDistinct: true,
    highlightPreTag: '__ais-highlight__',
    highlightPostTag: '__/ais-highlight__',
    filters: 'locale:"' + current_locale + '"', // This is the line we added.
	}
});
```

Now your results on the dedicated search page should be properly filtered.

For the autocomplete dropdown experience, you need to do a similar change.

First [copy the autocomplete.php file to your own theme](https://community.algolia.com/wordpress/customize-autocomplete.html#customization), 
and then do the following change to your file:

```js
source: algoliaAutocomplete.sources.hits(client.initIndex(config['index_name']), {
  hitsPerPage: config['max_suggestions'],
  attributesToSnippet: [
	'content:10'
  ],
  highlightPreTag: '__ais-highlight__',
  highlightPostTag: '__/ais-highlight__',
  filters: 'locale:"' + current_locale + '"', // This is the added line.

}),
```

After this change your autocomplete dropdown results should now also be filtered by the current locale.
