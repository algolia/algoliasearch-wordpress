---
title: Customization
description: Customize your drop-down menu and instant search results page.
layout: page.html
---

## Introduction

### Autocomplete

*The autocomplete experience can be enabled in the **Autocomplete** section of the plugin.*

The autocomplete dropdown menu is powered by **autocomplete.js**, a JavaScript library that eases the process of building rich dropdown menus.

Please checkout the [github repository of the autocomplete.js library](https://github.com/algolia/autocomplete.js/).

### Instant Search Results Page

*The instant search results experience can be enabled in the **Search page** section of the plugin.*

The instant search results page is powered by **instantsearch.js**, a JavaScript library that eases the process of building rich search experiences.

Please checkout the [official documentation of the instantsearch.js website](https://community.algolia.com/instantsearch.js/). Also take a look at some examples of [what can be achieved with instantsearch.js](https://community.algolia.com/instantsearch.js/examples/).

<div class="alert alert-info">The instantsearch feature will do its search on the **Searchable posts** index. If you don't have that index flagged for indexing you will be invited to index it with a notice on every page of the WordPress admin.</div>

## Customizations

<div class="alert alert-warning">You should never directly edit files of the plugin, because all changes would be lost at every update of the plugin.</div>

To start customizing your dropdown menu and instant-search results pages, copy/paste the `wp-content/plugins/algolia/templates` directory in your own theme, and rename it to `algolia`.

**Example**: If your theme is named `mytheme`, then the folder should look like: `wp-content/themes/mytheme/algolia`.

Edit the files in your `algolia` folder to customize the look & feel and the overall search experience of your autocomplete and instant search results page.

 * `autocomplete-empty.php`: The template of the empty dropdown menu (when no results).
 * `autocomplete-footer.php`: The template of the dropdown menu footer.
 * `autocomplete-header.php`: The template of the dropdown menu header.
 * `autocomplete-post-suggestion.php`: The template of the `Post` suggestions.
 * `autocomplete-term-suggestion.php`: The template of the `Term` suggestions.
 * `autocomplete-user-suggestion.php`: The template of the `User` suggestions.


 * `instantsearch.php`: The instantsearch (hits, filters, search bar & pagination) templates & JavaScript code.

<div class="alert alert-info">Whatever changes you make, be sure to understand how the autocomplete.js & instantsearch.js library work by checking out their official documentations.</div>

We provide with some default CSS rules that are locate on the `algolia/assets/css/algolia-autocomplete.css` and `algolia/assets/css/algolia-instantsearch.css` stylesheets. You can very easily add your own CSS rules to your theme's stylesheet.

If for any reason you don't want the default stylesheet to be included, you can remove it like this:

```php
<?php

/**
 * Dequeue default CSS files.
 *
 * Hooked to the wp_print_styles action, with a late priority (100),
 * so that it is after the stylesheets were enqueued.
 */
function my_theme_dequeue_styles() {
	// Remove the algolia-autocomplete.css.
	wp_dequeue_style( 'algolia-autocomplete' );

	// Remove the algolia-instantsearch.css.
	wp_dequeue_style( 'algolia-instantsearch' );
}
add_action( 'wp_print_styles', 'my_theme_dequeue_styles', 100 );
```
