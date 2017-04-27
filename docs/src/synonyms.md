---
title: Synonyms
description: How to leverage the Algolia synonyms feature.
layout: page.html
---
## Introduction

The plugin supports the synonym feature of Algolia.

Be sure to understand how synonyms work by reading the [synonyms official documentation](https://www.algolia.com/doc/relevance/synonyms).

## Push Your Own Synonyms

Pushing your own synonyms is fairly simple by using the different `filters` the plugin provides.

| Filter Name                                 | Parameters                         |
|:--------------------------------------------|:-----------------------------------|
| algolia_posts_index_synonyms                | array $synonyms, string $post_type |
| algolia\_posts\_{$post_type}_index_synonyms | array $synonyms                    |
| algolia_searchable_posts_index_synonyms     | array $synonyms                    |
| algolia_terms_index_synonyms                | array $synonyms, string $taxonomy  |
| algolia\_terms\_{$taxonomy}_index_synonyms  | array $synonyms                    |
| algolia_users_index_synonyms                | array $synonyms                    |

Example:

```php
<?php
/*
Plugin Name: Algolia Greeting Synonyms Example
*/

function custom_posts_page_index_synonyms( array $synonyms ) {
	$synonyms[] = array(
		'objectID' => 'greeting',
		'type'     => 'synonym',
		'synonyms' => array( 'hello', 'hi', 'hey' )
	);

	$synonyms[] = array(
		'objectID' => 'tablet',
		'type'     => 'oneWaySynonym',
		'input'    => 'tablet',
		'synonyms' => array( 'ipad', 'galaxy note' )
	);

	$synonyms[] = array(
        'objectID' 		=> 'street',
        'type'     		=> 'placeholder',
        'placeholder'   => '<Street>',
        'replacements' 	=> array( 'street', 'st' )
    );

	return $synonyms;
}

add_filter( 'algolia_posts_page_index_synonyms', 'custom_posts_page_index_synonyms' );
```

To fully understand the synonyms arguments please check out the [official documentation about synonyms](https://www.algolia.com/doc/relevance/synonyms).

<div class="alert alert-warning">As for index settings, synonyms will be reset by the WordPress plugin each time you re-index your content. You should NOT configure your synonyms via the Algolia Dashboard. Instead create a plugin as explained in [the documentation page about extending this plugin](extend-basics.html).</div>
