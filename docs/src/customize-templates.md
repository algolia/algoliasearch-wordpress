---
title: Customize templates from your theme
description: Ship custom templates for Algolia Search with your theme.
layout: page.html
---
## Introduction

We provide users with a default look & feel for the 2 main features of the plugin which are the autocomplete drop-down and the instant search page.

That being said, as a theme author you might want to customize the appearance of the search experiences so that they fit into your theme even more.

To do so, you can ship one of the 2 template files with your theme or both like explained in:

* [The customization of the autocomplete dropdown](customize-autocomplete.html)
* [The customization of the instant search page](customize-search-page.html)

<div class="alert alert-warning">If you are not the author of the theme you are using, we recommend you create a [child theme](https://codex.wordpress.org/Child_Themes) so that you don't loose your changes after updating the theme.</div>

## Default location of template files

By default, the plugin will look for the template files in your theme in different locations by order of priority:

1. In the root folder of your theme, e.g. `wp-content/themes/yourtheme/autocomplete.php`
1. In a folder named `algolia`, e.g. `wp-content/themes/yourtheme/algolia/autocomplete.php`
1. In a custom defined directory like explained in the (Customize template folder name section)[#customize-templates-folder-name]
1. At custom locations like explained in (Customize template names and location)[#customize-template-names-and-location]
1. In the plugin's `templates` folder if none of the preceeding matched.

<div class="alert alert-warning">
The first matching template found will be used.
Even if you defined a custom directory like explained in the next section, and if a template is found in `wp-content/themes/yourtheme/algolia/*`, then that later one would be used.
</div>

## Customize templates folder name

We provide a filter called `algolia_templates_path` which allows you to customize the name of the folder where the plugin will look for templates.

Here is an example that let's you place your template files for customizing the Algolia Search experiences in a folder named `partials/` instead of `algolia/`.

 ```php
 <?php
 // functions.php in your theme's root directory.

 add_filter( 'algolia_templates_path', function() {
 	return 'partials/';
 } );
 ```

 <div class="alert alert-warning">Be sure you don't omit the trailing slash.</div>

 You can now place your algolia template inside of the `partials` folder.

 The plugin will try to load:

 * wp-content/themes/yourtheme/partials/autocomplete.php
 * wp-content/themes/yourtheme/partials/instantsearch.php


 <div class="alert alert-info">If one of the template is not present in the folder, it will fallback to the template shipped with the plugin only for that one, still loading your custom version for the other.</div>

## Customize template names and location

In the previous section you learned how to customize the folder where the plugin will try to load the templates from.

As a theme author, maybe you are respecting some kind of naming convention, and it that regard you would also like to change the template names in addition to their location.

Here again we provide a filter hook called `algolia_template_locations` that allows you to suggest the plugin additional locations to look at when loading templates.

In the next example, we make it so that the templates will be loaded from:

* wp-content/themes/yourtheme/search/algolia-autocomplete.php
* wp-content/themes/yourtheme/search/algolia-instantsearch.php


```php
<?php

/**
 * @param array  $locations
 * @param string $file
 *
 * @return array
 */
function yourtheme_algolia_template_locations( array $locations, $file ) {
	if ( $file === 'autocomplete.php' ) {
		$locations[] = 'search/algolia-autocomplete.php';
	} elseif ( $file === 'instantsearch.php' ) {
		$locations[] = 'search/algolia-instantsearch.php';
	}

	return $locations;
}

// functions.php in your theme's root directory.
add_filter( 'algolia_template_locations', 'yourtheme_algolia_template_locations', 10, 2 );
```

<div class="alert alert-warning">Please note that templates found in the templates directory we customized in the previous section will always take preceedence.</div>
