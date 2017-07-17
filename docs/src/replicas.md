---
title: Index Replicas
description: Create indices with the same content but with a different ranking formula.
layout: page.html
---

## Introduction

Sometimes you may want to rank your results based on different criterias.

For example, in a WordPress shop powered by WooCommerce, you might want to list products by displaying cheapest products first.

In Algolia, to achieve different ranking on the same datasets, you can use Index Replicas.

<div class="alert alert-info">Index Replicas are automatically synced with the Master index, and write operations are not deduced from your monthly quota.</div>

## Register custom index replicas

To register custom index replicas, there are 2 filters:

| Filter Name                         | Params                                |
|:------------------------------------|:--------------------------------------|
| algolia_index_replicas              | array $replicas, Algolia_Index $index |
| algolia\_{$index_id}_index_replicas | array $replicas, Algolia_Index $index |

Here is an example of creating an index replica that will order results based on the lowest `price`.

<div class="alert alert-warning">The `price` field is not natively available in WordPress, here we assume it is available.</div>

```
<?php

/**
 * @param array         $replicas
 * @param Algolia_Index $index
 *
 * @return array
 */
function my_products_index_replicas( array $replicas, Algolia_Index $index ) {
	if ( 'posts_product' === $index->get_id() ) {
		$replicas[] = new Algolia_Index_Replica( 'price', 'asc' );
		$replicas[] = new Algolia_Index_Replica( 'price', 'desc' );
	}

	return $replicas;
}
add_filter( 'algolia_index_replicas', 'my_products_index_replicas', 10, 2 );
```

Note that you could also have used the second form of the filter to directly scope the replicas to a given index:

```
<?php

/**
 * @param array $replicas
 *
 * @return array
 */
function my_products_index_replicas( array $replicas) {
	$replicas[] = new Algolia_Index_Replica( 'price', 'asc' );
	$replicas[] = new Algolia_Index_Replica( 'price', 'desc' );

	return $replicas;
}
add_filter( 'algolia_posts_product_index_replicas', 'my_products_index_replicas');
```

## Search on replica when using backend search

When using Algolia in the backend, you can use 2 hooks to customize the replica to search on.

In the example, let's register a custom replica to allow to list searchable posts by date by oldest entry:

```
<?php

/**
 * @param array         $replicas
 * @param Algolia_Index $index
 *
 * @return array
 */
function my_custom_index_replicas( array $replicas, Algolia_Index $index ) {
    if ( 'searchable_posts' === $index->get_id() ) {
        $replicas[] = new Algolia_Index_Replica( 'post_date', 'asc' );
    }

    return $replicas;
}
add_filter( 'algolia_index_replicas', 'my_custom_index_replicas', 10, 2 );

```

After this change in your code, you will need to push the settings of the searchable posts index from the "autocomplete"
page of the plugin.

You should now have a newly created replica index which you can see in the Algolia dashboard.

You now need to use filters to correctly target the new replica:

```
<?php

add_filter( 'algolia_search_order_by', function() {
   return 'post_date';
} );

add_filter( 'algolia_search_order', function() {
    return 'asc';
} );
```

Your search page should now return results from oldest to newest one.

You could also dynamically switch ordering based on a `$_GET` parameter:

```
<?php

add_filter( 'algolia_search_order_by', function( $attribute_name ) {
	if ( isset( $_GET['orderby'] ) && $_GET['orderby'] === 'date-asc' ) {
		return 'post_date';
	}

   return $attribute_name;
} );

add_filter( 'algolia_search_order', function( $order ) {
	if ( isset( $_GET['orderby'] ) && $_GET['orderby'] === 'date-asc' ) {
		return 'asc';
	}

  return $order;
} );
```


