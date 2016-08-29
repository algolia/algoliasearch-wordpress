---
title: Instantsearch
description: Quick tutorial on how to implement instantsearch.js on your WordPress website.
layout: page.html
---

## Introduction

Instantsearch.js is a javascript library that eases the process of building rich search experiences.

Please checkout the [official website of the instantsearch.js website](https://community.algolia.com/instantsearch.js/).

Also take a look at some examples of [what can be achieved with instantsearch.js](https://community.algolia.com/instantsearch.js/examples/).

## Enabling Instantsearch.js

The instantsearch.js experience can be enabled on the `Search page` of the plugin.

The instantsearch feature will do its search on the `Searchable posts index`. If you don't have that index flagged for indexing you will be invited to index it with a notice on every page of the WordPress admin.

## Customizing Implementation

**You should never directly edit files of the plugin, because all changes would be lost at every update of the plugin.**

Instead you should copy paste the `wp-content/plugins/algolia/templates` directory in your own theme, and rename it to `algolia`.

eg: If your theme is named `mytheme`, then the folder should look like: `wp-content/themes/mytheme/algolia`.

You can then tweak tke implementation by changing the `instantsearch.php` file. For simplicity purposes, that file contains both the HTML and the JavaScript required for the instantsearch.js to work.

Feel free to extract the JavaScript to a separate script in your theme.

Whatever changes you make, be sure to understand how the instantsearch.js library works by checking out the [official documentation](https://community.algolia.com/instantsearch.js/documentation/).


## Custom CSS

We provide with some default CSS rules that are locate on the `algolia/assets/css/algolia-instantsearch.css` stylesheet.

You can very easily add your own CSS rules to your theme's stylesheet.

If for any reason you don't want the default stylesheet to be included, you can remove it like this:

```php
<?php


/**
 * Dequeue the Algolia Instantsearch CSS file.
 *
 * Hooked to the wp_print_styles action, with a late priority (100),
 * so that it is after the stylesheet was enqueued.
 */
function my_theme_dequeue_styles() {
   wp_dequeue_style( 'algolia-instantsearch' );
}
add_action( 'wp_print_styles', 'my_theme_dequeue_styles', 100 );
```







