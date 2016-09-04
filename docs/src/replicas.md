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

|Filter Name|Params
|-|-
|algolia_index_replicas|array $replicas, Algolia_Index $index
|algolia\_{$index_id}_index_replicas|array $replicas, Algolia_Index $index

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

