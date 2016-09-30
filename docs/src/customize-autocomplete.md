---
title: Customize Autocomplete drop-down
description: Customize your drop-down menu that appears underneath every search bar of your WordPress website.
layout: page.html
---

<div class="alert alert-warning">**Reminder**: You should never directly edit the plugin files because all your changes would be lost at every plugin update.</div>

## Introduction

*The autocomplete experience can be enabled in the **Autocomplete** section of the plugin.*

The autocomplete dropdown menu is powered by **autocomplete.js**, a JavaScript library that eases the process of building rich dropdown menus.

Please checkout the [github repository of the autocomplete.js library](https://github.com/algolia/autocomplete.js/).

<div class="alert alert-info">Whatever changes you make, be sure to understand how the **autocomplete.js** library work by checking out their official documentations.</div>

## Customization

To start customizing your dropdown menu, copy/paste the `wp-content/plugins/algolia/templates` directory in your own theme, and rename it to `algolia`. If your theme is named `mytheme`, then the folder should look like: `wp-content/themes/mytheme/algolia`.

You can then edit the `algolia/autocomplete.php` file to customize the autocomplete dropdown menu (suggestions, header, footer) templates & associated JavaScript code.

<div class="alert alert-info">All autocomplete.js templates are using the underscore templating, learn more about it reading this [complete documentation](http://www.2ality.com/2012/06/underscore-templates.html).</div>

## Edit the suggestion templates

Each autocomplete.js source will display its suggestions with an associated template. To configure them, edit the `algolia/autocomplete.php` file and locate the `tmpl-autocomplete-*-suggestion` templates:

## Post template

This is the template used for all **Post**-based types.

```html
<script type="text/html" id="tmpl-autocomplete-post-suggestion">
	<a class="suggestion-link" href="{{ data.permalink }}" title="{{ data.post_title }}">
		<# if ( data.images.thumbnail ) { #>
		<img class="suggestion-post-thumbnail" src="{{ data.images.thumbnail.url }}" alt="{{ data.post_title }}">
		<# } #>
		<div class="suggestion-post-attributes">
			<span class="suggestion-post-title">{{{ data._highlightResult.post_title.value }}}</span>

			<#
			var attributes = ['content', 'title6', 'title5', 'title4', 'title3', 'title2', 'title1'];
			var attribute_name;
			var relevant_content = '';
			for ( var index in attributes ) {
			attribute_name = attributes[ index ];
			if ( data._highlightResult[ attribute_name ].matchedWords.length > 0 ) {
			relevant_content = data._snippetResult[ attribute_name ].value;
			break;
			} else if( data._snippetResult[ attribute_name ].value !== '' ) {
			relevant_content = data._snippetResult[ attribute_name ].value;
			}
			}
			#>
			<span class="suggestion-post-content">{{{ relevant_content }}}</span>
		</div>
	</a>
</script>
```

## Term template

This is the template used for all **Term**-based types.

```html
<script type="text/html" id="tmpl-autocomplete-term-suggestion">
  <a class="suggestion-link" href="{{ data.permalink }}"  title="{{ data.name }}">
    <svg viewBox="0 0 21 21" width="21" height="21"><svg width="21" height="21" viewBox="0 0 21 21"><path d="M4.662 8.72l-1.23 1.23c-.682.682-.68 1.792.004 2.477l5.135 5.135c.7.693 1.8.688 2.48.005l1.23-1.23 5.35-5.346c.31-.31.54-.92.51-1.36l-.32-4.29c-.09-1.09-1.05-2.06-2.15-2.14l-4.3-.33c-.43-.03-1.05.2-1.36.51l-.79.8-2.27 2.28-2.28 2.27zm9.826-.98c.69 0 1.25-.56 1.25-1.25s-.56-1.25-1.25-1.25-1.25.56-1.25 1.25.56 1.25 1.25 1.25z" fill-rule="evenodd"></path></svg></svg>
    <span class="suggestion-post-title">{{{ data._highlightResult.name.value }}}</span>
  </a>
</script>
```

## User template

This is the template used for **Users**.

```html
<script type="text/html" id="tmpl-autocomplete-user-suggestion">
  <a class="suggestion-link user-suggestion-link" href="{{ data.posts_url }}"  title="{{ data.display_name }}">
    <# if ( data.avatar_url ) { #>
    <img class="suggestion-user-thumbnail" src="{{ data.avatar_url }}" alt="{{ data.display_name }}">
    <# } #>

    <span class="suggestion-post-title">{{{ data._highlightResult.display_name.value }}}</span>
  </a>
</script>
```

## Edit the source headers

Each autocomplete.js source will have an associated header displayed before the suggestions. To configure it, edit the `algolia/autocomplete.php` file and locate the `tmpl-autocomplete-header` template:

```html
<script type="text/html" id="tmpl-autocomplete-header">
  <div class="autocomplete-header">
    <div class="autocomplete-header-title">{{ data.label }}</div>
    <div class="clear"></div>
  </div>
</script>
```

## Edit the dropdown menu footer

The dropdown menu is provided with a default footer. To configure it, edit the `algolia/autocomplete.php` file and locate the `tmpl-autocomplete-footer` template:

```html
<script type="text/html" id="tmpl-autocomplete-footer">
  <div class="autocomplete-footer">
    <div class="autocomplete-footer-branding">
      <?php esc_html_e( 'Powered by', 'algolia' ); ?>
      <a href="#" class="algolia-powered-by-link" title="Algolia">
        <img class="algolia-logo" src="https://www.algolia.com/assets/algolia128x40.png" alt="Algolia" />
      </a>
    </div>
  </div>
</script>
```

## Edit the no results message

When no results are found, autocomplete.js will display a specific template.  To configure it, edit the `algolia/autocomplete.php` file and locate the `tmpl-autocomplete-empty` template:

```html
<script type="text/html" id="tmpl-autocomplete-empty">
  <div class="autocomplete-empty">
    <?php esc_html_e( 'No results matched your query ', 'algolia' ); ?>
    <span class="empty-query">{{ data.query }}"</span>
  </div>
</script>
```

## Adding an extra source

In some cases, you may want to add an extra section to your dropdown menu to search in an external index (of another website you own for instance). In order to achieve that, edit the `algolia/autocomplete.php` file and locate the `autocomplete(...)` call. As you'll see, the 3rd parameter of the function is the array of `sources` you're using.

To add an extra source, just append it to the existing `sources` array, just before the `autocomplete(...)` call:


```js
// new source appended to the `sources` array
sources.push({
  source: /* .... */,
  templates: {
    header: /* .... */,
    suggestion: /* .... */
  }
});

autocomplete(/* .... */);
```

You can read more about multi sources/categories autocomplete.js implementation in [this tutorial](https://www.algolia.com/doc/guides/search/auto-complete#multi-category).

## Look & feel

We provide with some default CSS rules that are located in the `algolia/assets/css/algolia-autocomplete.css`. You can very easily add your own CSS rules to your theme's stylesheet.

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
}
add_action( 'wp_print_styles', 'my_theme_dequeue_styles', 100 );
```

## Disable autocomplete on certain search inputs

By default, if the autocomplete feature is turned on, we will attach an Algolia dropdown to every search input of your website matching the jQuery selector `input[name='s']`.

Note however that we also exclude inputs that have an associated `no-autocomplete` class.

So to easily disable the Algolia autocomplete dropdown for a given search input, simply add the `no-autocomplete` class to it.


