---
title: Custom Search
description: Discover how to build your custom WordPress search experience with the Algolia plugin.
layout: page.html
---
## Introduction

By default, this plugin is shipped with a way to override the default WordPress search and also provides an as you type experience called "autocomplete".

In your case you may want to provide some very specific search experience depending of the data you have, in the UI you want to build.

Here we are not going to show you the whole process of creating a full featured custom search experience, because it would probably not be useful for your needs.

That said we are going to give you some of the resources end extension points the plugin provides allowing you to build any kind of search experience you'd like!

## Static Assets

This plugin ships with several javascript libraries that can be used for implementing your custom search experience.

|name|description
|-|-
|algolia-search|The official Algolia Javascript API Client: https://www.algolia.com/doc/javascript
|algolia-autocomplete|The official autocomplete.js library: https://github.com/algolia/autocomplete.js
|algolia-instantsearch|The official instantsearch.js library: https://community.algolia.com/instantsearch.js/

If you want to create your custom search experience, you should take care of "enqueuing" scripts.

The best way would be to declare the scripts as required dependencies:

```php
<?php

function register_custom_search_assets() {
	wp_enqueue_script( 'custom-search', plugins_url( 'js/custom-search.js', dirname(__FILE__) ), array( 'algolia-search', 'algolia-autocomplete' ) );
}

add_action( 'wp_enqueue_scripts', 'register_custom_search_assets' );
```
This code will ensure that the 'algolia-search' and 'algolia-autocomplete' scripts are also added to the page.

In this example, you can now use the Algolia JS client and the autocomplete library in your custom search script.
Please take a look at the documentation of the underlying libraries by following the links shared above.

## Work With The Existing Indices

The Algolia Plugin exports a global `$algolia` variable. That variable contains a reference to the `Algolia_Plugin` class.

That class gives you access to all of the features of the plugin.

Here is one way to get the class instance:

```php
function custom_algolia_usage() {
	/** @var Algolia_Plugin $algolia */
	global $algolia;

	// Use the Algolia plugin in your own.
}

add_action( 'plugins_loaded', 'custom_algolia_usage' );
```

**You should always access the variable once the plugins are loaded. Otherwise, depending on the order in which the user enabled the plugins it could fail.**

Now that you have access to the `Algolia_Plugin` instance, you can start using it.

Also be sure to checkout the [custom attributes documentation](custom-attributes.html) which contains a detailed example of a plugin creation.


