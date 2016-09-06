---
title: Customization
description: Customize your drop-down menu and instant search results page.
layout: page.html
---

<div class="alert alert-warning">**Reminder**: You should never directly edit the plugin files because all your changes will be lost at every plugin update.</div>

## Autocomplete

### Introduction

*The autocomplete experience can be enabled in the **Autocomplete** section of the plugin.*

The autocomplete dropdown menu is powered by **autocomplete.js**, a JavaScript library that eases the process of building rich dropdown menus.

Please checkout the [github repository of the autocomplete.js library](https://github.com/algolia/autocomplete.js/).

<div class="alert alert-info">Whatever changes you make, be sure to understand how the **autocomplete.js** library work by checking out their official documentations.</div>

### Customization

To start customizing your dropdown menu, copy/paste the `wp-content/plugins/algolia/templates` directory in your own theme, and rename it to `algolia`. If your theme is named `mytheme`, then the folder should look like: `wp-content/themes/mytheme/algolia`.

You can then edit the `algolia/autocomplete.php` file to customize the autocomplete dropdown menu (suggestions, header, footer) templates & associated JavaScript code.

<div class="alert alert-info">All autocomplete.js templates are using the underscore templating, learn more about it reading this [complete documentation](http://www.2ality.com/2012/06/underscore-templates.html).</div>

#### Edit the suggestion templates

Each autocomplete.js source will display its suggestions with an associated template. To configure them, edit the `algolia/autocomplete.php` file and locate the `tmpl-autocomplete-*-suggestion` templates:

#### Post template

This is the template used for all **Post**-based types.

```html
<script type="text/html" id="tmpl-autocomplete-post-suggestion">
  <a class="suggestion-link" href="{{ data.permalink }}" title="{{ data.post_title }}">
    <# if ( data.thumbnail_url ) { #>
    <img class="suggestion-post-thumbnail" src="{{ data.thumbnail_url }}" alt="{{ data.post_title }}">
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

#### Term template

This is the template used for all **Term**-based types.

```html
<script type="text/html" id="tmpl-autocomplete-term-suggestion">
  <a class="suggestion-link" href="{{ data.permalink }}"  title="{{ data.name }}">
    <svg viewBox="0 0 21 21" width="21" height="21"><svg width="21" height="21" viewBox="0 0 21 21"><path d="M4.662 8.72l-1.23 1.23c-.682.682-.68 1.792.004 2.477l5.135 5.135c.7.693 1.8.688 2.48.005l1.23-1.23 5.35-5.346c.31-.31.54-.92.51-1.36l-.32-4.29c-.09-1.09-1.05-2.06-2.15-2.14l-4.3-.33c-.43-.03-1.05.2-1.36.51l-.79.8-2.27 2.28-2.28 2.27zm9.826-.98c.69 0 1.25-.56 1.25-1.25s-.56-1.25-1.25-1.25-1.25.56-1.25 1.25.56 1.25 1.25 1.25z" fill-rule="evenodd"></path></svg></svg>
    <span class="suggestion-post-title">{{{ data._highlightResult.name.value }}}</span>
  </a>
</script>
```

#### User template

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

#### Edit the source headers

Each autocomplete.js source will have an associated header displayed before the suggestions. To configure it, edit the `algolia/autocomplete.php` file and locate the `tmpl-autocomplete-header` template:

```html
<script type="text/html" id="tmpl-autocomplete-header">
  <div class="autocomplete-header">
    <div class="autocomplete-header-title">{{ data.label }}</div>
    <div class="clear"></div>
  </div>
</script>
```

#### Edit the dropdown menu footer

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

#### Edit the no results message

When no results are found, autocomplete.js will display a specific template.  To configure it, edit the `algolia/autocomplete.php` file and locate the `tmpl-autocomplete-empty` template:

```html
<script type="text/html" id="tmpl-autocomplete-empty">
  <div class="autocomplete-empty">
    <?php esc_html_e( 'No results matched your query ', 'algolia' ); ?>
    <span class="empty-query">{{ data.query }}"</span>
  </div>
</script>
```

#### Adding an extra source

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

#### Look & feel

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


## Search Results Page

### Introduction

*The instant search results experience can be enabled in the **Search page** section of the plugin.*

The instant search results page is powered by **instantsearch.js**, a JavaScript library that eases the process of building rich search experiences.

Please checkout the [official documentation of the instantsearch.js website](https://community.algolia.com/instantsearch.js/). Also take a look at some examples of [what can be achieved with instantsearch.js](https://community.algolia.com/instantsearch.js/examples/).

The instantsearch feature will do its search on the **Searchable posts** index. If you don't have that index flagged for indexing you will be invited to index it with a notice on every page of the WordPress admin.

<div class="alert alert-info">Whatever changes you make, be sure to understand how the **instantsearch.js** library work by checking out their official documentations.</div>

### Customization

To start customizing your search results page, copy/paste the `wp-content/plugins/algolia/templates` directory in your own theme, and rename it to `algolia`. If your theme is named `mytheme`, then the folder should look like: `wp-content/themes/mytheme/algolia`.

You can then edit the `algolia/instantsearch.php` file to customize the instant search results page (search widgets & page layout).

<div class="alert alert-info">All instantsearch.js templates are using the underscore templating, learn more about it reading this [complete documentation](http://www.2ality.com/2012/06/underscore-templates.html).</div>

#### Edit the page layout

The instant search results page is provided with a default layout. You'll need to slightly customize it to fit your theme's look & feel. To configure the layout, edit the `algolia/instantsearch.php` file and locate the `<div id="ais-wrapper">...</div>` code:

```html
<div id="ais-wrapper">
  <main id="ais-main">
    <div id="algolia-search-box">
      <div id="algolia-stats"></div>
      <svg class="search-icon" width="25" height="25" viewBox="0 0 40 40" xmlns="http://www.w3.org/2000/svg"><path d="M24.828 31.657a16.76 16.76 0 0 1-7.992 2.015C7.538 33.672 0 26.134 0 16.836 0 7.538 7.538 0 16.836 0c9.298 0 16.836 7.538 16.836 16.836 0 3.22-.905 6.23-2.475 8.79.288.18.56.395.81.645l5.985 5.986A4.54 4.54 0 0 1 38 38.673a4.535 4.535 0 0 1-6.417-.007l-5.986-5.986a4.545 4.545 0 0 1-.77-1.023zm-7.992-4.046c5.95 0 10.775-4.823 10.775-10.774 0-5.95-4.823-10.775-10.774-10.775-5.95 0-10.775 4.825-10.775 10.776 0 5.95 4.825 10.775 10.776 10.775z" fill-rule="evenodd"></path></svg>
    </div>
    <div id="algolia-hits"></div>
    <div id="algolia-pagination"></div>
  </main>
  <aside id="ais-facets">
    <section class="ais-facets" id="facet-post-types"></section>
    <section class="ais-facets" id="facet-categories"></section>
    <section class="ais-facets" id="facet-tags"></section>
    <section class="ais-facets" id="facet-users"></section>
  </aside>
</div>
```

Edit this HTML layout to change the way the overall page is structured.

#### Edit the hit template

Each matching **Searchable Post** will be rendered with a default hit template. To customize it, edit the `algolia/instantsearch.php` file and locate the `tmpl-hit` template:

```html
<script type="text/html" id="tmpl-instantsearch-hit">
  <article itemtype="http://schema.org/Article">
    <# if ( data.thumbnail_url ) { #>
    <div class="ais-hits--thumbnail">
      <a href="{{ data.permalink }}" title="{{ data.post_title }}">
        <img src="{{ data.thumbnail_url }}" alt="{{ data.post_title }}" title="{{ data.post_title }}" itemprop="image" />
      </a>
    </div>
    <# } #>

    <div class="ais-hits--content">
      <h2 itemprop="name headline"><a href="{{ data.permalink }}" title="{{ data.post_title }}" itemprop="url">{{{ data._highlightResult.post_title.value }}}</a></h2>
      <div class="ais-hits--tags">
        <# for (var index in data.taxonomy_post_tag) { #>
        <span class="ais-hits--tag">{{ data.taxonomy_post_tag[index].value }}</span>
        <# } #>
      </div>
      <div class="excerpt">
        <p>
          <#
          var attributes = ['content', 'title6', 'title5', 'title4', 'title3', 'title2', 'title1'];
          var attribute_name;
          var relevant_content = '';
          for ( var index in attributes ) {
            attribute_name = attributes[ index ];
            if ( data._highlightResult[ attribute_name ].matchedWords.length > 0 ) {
              relevant_content = data._snippetResult[ attribute_name ].value;
            }
          }

          relevant_content = data._snippetResult[ attributes[ 0 ] ].value;
          #>
          {{{ relevant_content }}}
        </p>
      </div>
    </div>
    <div class="ais-clearfix"></div>
  </article>
</script>
```

#### Adding an extra search widget

To add an extra search widget, the best is to follow the [official documentation of instantsearch.js](https://community.algolia.com/instantsearch.js/) and instantiate the widget in the `algolia/instantsearch.php` file, just before the `Search.start()` call.

```js
// new widget
search.addWidget(/* ... */);

search.start();
```


#### Look & feel

We provide with some default CSS rules that are located in the `algolia/assets/css/algolia-instantsearch.css`. You can very easily add your own CSS rules to your theme's stylesheet.

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
  // Remove the algolia-instantsearch.css.
  wp_dequeue_style( 'algolia-instantsearch' );
}
add_action( 'wp_print_styles', 'my_theme_dequeue_styles', 100 );
```
