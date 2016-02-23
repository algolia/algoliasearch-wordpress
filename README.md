# WARNING

This extension is currently in **beta**. You can download it and play with it as much as you want, but we would only recommend its usage to experienced developers

# Algolia Realtime Search Wordpress Plugin

[Algolia Search](http://www.algolia.com) is a hosted full-text, numerical, and faceted search engine capable of delivering realtime results from the first keystroke.

This plugin replaces the default search of Wordpress adding an as-you-type auto-completion menu and an instant search results page. It has been designed to support plugins like WooCommerce in addition to the standard blog system.

![Latest version](https://img.shields.io/badge/latest-0.0.9-green.svg)
![MIT](http://img.shields.io/badge/license-MIT-green.svg?style=flat-square)
![Wordpress 4.2](https://img.shields.io/badge/wordpress-4.2.x-blue.svg)
![Wordpress 4.1](https://img.shields.io/badge/wordpress-4.1.x-blue.svg)
![Wordpress 4.0](https://img.shields.io/badge/wordpress-4.0.x-blue.svg)
![Wordpress 3.9](https://img.shields.io/badge/wordpress-3.9.x-blue.svg)
![Wordpress 3.8](https://img.shields.io/badge/wordpress-3.8.x-blue.svg)
![PHP >= 5.3](https://img.shields.io/badge/php-%3E=5.3-green.svg)

Installation
--------

1. Create an [Algolia account](https://www.algolia.com/users/sign_up)
2. Once logged in, go to the *Credentials* section and get your **Application ID**, **ADMIN API key** and **Search-only API key**
3. Install Algolia Wordpress Plugin from your Wordpress dashboard or unzip the plugin archive in the `wp-content/plugins` directory.
4. Activate the plugin through the *Plugins* menu in Wordpress
5. Go to the **Algolia Search** left menu configuration page and fill your credentials
6. Configure the way you want the plugin to render your search results
7. Click the "Reindex data" button

Features
--------

##### Search bar with auto-complete

This extension adds an auto-completion menu to your search bar displaying product, categories & pages "as-you-type".

##### Instant & Faceted search results page

This extension adds a default implementation of an instant & faceted search results page. Just customize the underlying CSS & JavaScript to suits your wordpress theme.


Configuration
--------

Once the plugin is installed a new tab will appears in Wordpress admin panel

### Credentials & Template

In this section you configure two things:

##### Your Application ID & API keys

You need to configure this section based on your Algolia credentials. You can fetch them from [here](https://www.algolia.com/licensing).

##### Your index names prefix

The plugin will create several indices, all of them will be prefixed by this value:

 * 1 index for the instant search results page
 * 1 index for each section of the auto-completion menu
 * 1 (slave) index for each sort order you enable

##### Template

The plugin supports templates which allows you to customize the way your results are displayed. You can choose one of the 2 sample templates or build your own. The template is **totally independent** from the Wordpress theme. The default templates are handling both Autocompletion **AND** Instant-search results page.

### Attributes & Ranking

##### Attributes To Index

You can configure the Wordpress attributes and taxonomies you want to index by adding them to the list. If you use Wordpress as a blog you shouldn't have to change anything.

For each attribute you enable, you can decide to make it searchable & retrievable.

You can also choose if you want the attribute to be consider ordered or not:

   1. **Ordered**: Matches at the beginning of an attribute **will be** considered more important than matches at the end. This setting is recommended for short attributes like `title`.

   2. **Unordered**: Matches at the beginning of the list **will not be** considered more important than matches at the end. This setting is recommended for long attribute like `content`.


##### Custom Ranking

For each attribute you enable, you can make them part of your custom ranking formula.

**Notes:** Attributes that you enable here <b>NEED to be numerical attributes</b>.

### Auto-complete Menu

##### Search Bar

To be able to trigger the search at each keystroke on your site you need to specify where the plugin is supposed to find the search input. You do that by specifying a jQuery/DOM selector.

In Wordpress's default theme it is: `[name='s']`

##### Sections

Configure here the sections you want to see in the dropdown menu displayed as-you-type bellow the search bar.

### Search Results Page

##### Container

Configure here the container that will be used to inject the search results in your page.

##### Enabled Types

You can choose the Wordpress types you want to search for in the full results page. By default, only `post` and `page` are selected. If one of your extensions (WooCommerce for instance) creates new types you will see them in this list.

##### Faceting

When you make an attribute facetable **AND if you are in Instant Search Results Page mode** you will see a faceting/filtering bloc appears in which you can filter your results easily.

There is two facet types you can choose:

  1. **Conjunctive**

    Your refinements are ANDed. Think about a post having the tags `lifestyle` AND `mood`.

  2. **Disjunctive**

    Your refinements are ORed. Think about a products search with: `4` OR `5` stars.

  3. **Custom: slider, tag clouds, ...**

      A results template can declare new facet types. The "Default" template is adding the "slider" type.

You can add a label to each enabled facet to display the Facet bloc title. You can also order the facet blocs ordering the attributes.

##### Sorting

You can create different sort orders by selecting the attributes or taxonomies you want to sort with.

**Notes:** Adding a new sort order will create a slave index with the exact same settings except the ranking formula will have the use the attribute you enable as the first ranking criterion. You can choose a label which will be displayed on the instant search result page.


### Advanced

To avoid errors with too large record. We cut the content attribute. If you choose to disable it, you could have error while reindexing. To increase the limit you need to contact us.

Indexing
--------

### Initial import

Once you configure your credentials and others settings do not forget to reindex data. You can do that from the ```Algolia Search``` config page on your Wordpress admin

### Indexing flow

Once the initial import done. Every time you add/update some wordpress content. It will be indexed automatically to Algolia.

If you have modified some settings or clear/delete some indices you will need reindex everything. For that you just do like the initial import.

## Customization

#### Template folder architecture

A template is composed by several **MANDATORY** files:

- `config.php`

	It should look like

	```php
	<?php

	return array(
      'name'                      => 'Woo Default',
      'screenshot'                => 'screenshot.png',
      'facet_types'               => array()
	);
	```

	The `name` will be displayed on the UI Page: it's your template's name. And the screenshot is the path screenshot (**relative to your template root directory**).

- `styles.css`

  This file contains every CSS rules used by the template.

- `templates.php`

  This file contains all the HTML (we use Hogan.js) templates you need. The **mandory** content is:

  ```php
  <script type="text/template" id="autocomplete-template">
  /* Your autocomplete Hogan template HERE */
  </script>

  <script type="text/template" id="instant-content-template">
  /* Your instant search results Hogan template HERE */
  </script>

  <script type="text/template" id="instant-facets-template">
  /* Your facets template HERE */
  </script>

  <script type="text/template" id="instant-pagination-template">
    /* Your Pagination template HERE */
  </script>
  ```

- `template.js`

  This file includes all the JavaScript logic.

#### Add settings programatically to an index

You can use the ```prepare_algolia_set_settings``` filter

```php
add_filter('prepare_algolia_set_settings', function ($index_name, $settings)
{
	  if ($index_name == 'SOME_INDEX_NAME')
	  {
	    // Add settings here to the $settings object
	  }
    return $settings;
}, 10, 3);
```

#### Add some attributes to a record

You can use the ```prepare_algolia_record``` filter

```php
add_filter('prepare_algolia_record', function ($data) {
	if ($data->type == 'product')
	{
		// Add any attribute you want here
	}
  return $data;
});
```

## FAQ

#### Are my other plugins compatible?

It depends :) The plugin handles every other plugins that uses the `post` and `meta` tables of Wordpress. If a plugin creates its own tablem the plugin will not be able to see it.

#### How do I handle a non-supported plugin?

You can use the filters to add any attribute you want to the record

#### What happens if I modify a setting from the Algolia dashboard?

The plugin tries to merge the settings that have been modified from the Algolia dashboard and the one generated from your plugin configuration. In every case the following settings will be erased:

1. For the autocompletion-menu
  * `attributesToIndex`
  * `attributesToHighlight`
  * `attributesToSnippet`

2. For the instant search results page
  * `attributesToIndex`
  * `attributesForFaceting`
  * `attributesToSnippet`
  * `customRanking`
  * `slaves`
  * `ranking` (only for the sorting (slaves) indexes)

This works for any other facet but the Type one is the most likely to have non wanted formating.

#### I saved my configuration but I do not see any change on my results?

When you change a setting that impacts the index you may want to use the **Reindex data** button to reflect your changes in your Algolia index.
