---
title: Frequently Asked Questions
description: Most common issues and how to fix them.
layout: page.html
---
## How it works

This is a living `common questions & answers` section.

Please ask your question on Stack Overflow: http://stackoverflow.com/questions/tagged/algolia+wordpress

If you are a developer, you can also open an issue on github at: https://github.com/algolia/algoliasearch-wordpress. We will regularly add the most frequently asked questions and answers found on Stack Overflow and Github about the Algolia Search plugin for WordPress, and append them to this very page.

If you are desperate and your deadline was yesterday, send us an email at [support+wordpress@algolia.com](mailto:support+wordpress@algolia.com).

**Please give priority to Stack Overflow and Github when asking for support, that way the whole community can benefit from it and you might get a faster answer. If you are having an issue, the chances are high other users might have the same. Let's share!**


## Questions & Answers

### I have more records than I have posts, is that normal?

This is intentional and allows you to fully leverage the Algolia engine.

The plugin takes care of the splitting for you and makes sure that your articles are fully indexed independently from their size.

This means that even if your article is huge and you are searching for a word that is at the very bottom, you will have it as part of the suggested articles.

### Is it possible to disable the post splitting?

Yes, you can turn off the post splitting by defining a constant in your WordPress `wp-config.php` file.

```
define( 'ALGOLIA_SPLIT_POSTS', false );
```

You will need to re-index the indices to see the change.

### Can I customize the Autocomplete dropdown look & feel?

Yes. You can find some detailed explanations on [this page](customize-autocomplete.html).

### I have created a custom API key, but it is not accepted as Search-Only API key in the plugin settings.

Creating a dedicated search API key is indeed a good practice. If you want to be able to use that new generated API key as the Search-Only API key, you have to make sure it only has the `search` ACL privilege.
For security reasons, we reject every filled Search-Only API key that has additional privileges.

Secondly, we also require that the provided search API key has the TTL set to 0 (standing for unlimited validity in time). We do not want your users to get search outage.

### Will this plugin be compatible with my WordPress theme?

Yes. Actually this plugin introduces 2 main user oriented features:
- Native Search: Which overrides the default WordPress search with Algolia,
- Autocomplete: Which provides the user with an 'as you type' instant search experience.

In the first case, this plugin hooks into the WordPress native search feature. As a result, this will not impact your template because we only act on the backend side of things.

In the second case, we simply provide a dropdown menu that will be displayed underneath your search bar as you type. We have provided default scoped CSS rules so that the look and feel stays slick across different themes.

If you have any kind of issue related to default appearance, please send us a link to your website so that we can optimize our stylesheet.

### My data is out of sync with Algolia

Sometimes your WordPress content gets out of sync with Algolia. For example if you are changing your permalink templates, all your URLs will change and Algolia will not know about it.

Content also gets inconsistent when you use filters to change settings or attributes to push to Algolia.

In that case you can simply re-index your content.

### Can I use one Algolia account for multiple WordPress sites?

Definitely! In the same fashion that WordPress lets you prefix all database tables, we allow you to prefix every indices in Algolia.

This can be configured on the `Indexing` section of the plugin.

### Why doesn't my autocomplete dropdown show up?

1. First of all ensure the autocomplete feature is turned on on the Autocomplete page of the admin UI,
1. Make sure you actually selected at least one index to display from that same page, and ensure those indices were created in Algolia,

### The indexing is slow, can I optimize the required time?

Yes, it depends on your servers capacities though and the number of plugins you have enabled.

By default we only index 100 items per indexing process to ensure we don't reach PHP max execution time or memory limits.

Here is a way to increase the number of items indexed on each PHP indexing process:

```
<?php

add_filter( 'algolia_indexing_batch_size', function() {
    return 200;
} );
```

If indexing doesn't work anymore after the change, it probably means you pushed the number to high. Checkout the logs in that case to be sure.

### Can I push my custom user attributes?

Yes, and you can do so by using the `algolia_user_record` filter:

```
<?php

/**
 * @param array   $record
 * @param WP_User $user
 *
 * @return array
 */
function custom_user_record( array $record, WP_User $user ) {
	$record['custom_field'] = get_user_meta( $user->ID, 'custom_field', true );
	/* Add as many as you want. */

	return $record;
}

add_filter( 'algolia_user_record', 'custom_user_record', 10, 2 );
```

### My case is not listed here, what to do?

If your problem is covered here, please submit an issue with the error details here: https://github.com/algolia/algoliasearch-wordpress/issues
