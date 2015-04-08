# Algolia Wordpress Plugin

## Installation

1. Go to [https://www.algolia.com/users/sign_up](https://www.algolia.com/users/sign_up) and signup
2. After logging into Algolia Dashboard, go to the credential section and get your api keys
3. Install Algolia Wordpress Plugin in your Wordpress dashboard
4. Activate the plugin through the Plugins menu in Wordpress
5. Go to the Algolia Wordpress Plugin configuration page and fill the your api Keys
6. You can now configure every aspect of the search experience. See the [Configuration]() section for more information

## Configuration

### Credentials

In this section you configure two things :

   1. Your api keys

   You need to choose which api keys to use. You can go [https://www.algolia.com/licensing](https://www.algolia.com/licensing)
   
   2. Your indexes prefix

   The plugin will create several indexes :
   
    * 1 index for the instant search
    * 1 index for each section of the autocomplete
    * 1 index (slave) for each sort you enable
   
   You choose to prefix them to be able to find them easily in your dashboard or you can leave the field empty.
   
### UI Integration

#### Search Exprerience Configuration

   1. Search Input used

   To be able to integrate the search experience on you site you need to incate where the plugin is supposed to find the search input. You do that by specifing the JQuery selector.
   
   In the wordpress default theme : [name='s']

   2. Search Experience

   The plugin let you choose between two differents experience, an autocomplete one and an instant search one.
   
     * Autocomplete
      
      This will add an autocompletion menu to your search. In this menu you will have a section for every type you enable like page and post, and one section for every taxonomie you enable like categories or tag
      
      You need to specify the max number of result you want to be display in each section. If you choose 2 you will have for each section the two most revelevant results for your query
      
      ```
      The types section will be shown before the taxonomies one,
      but you can order them separately
      ```
     
     * Instant search

     This will refresh the whole results page as you type. For each query this will display your results of each type you enabled, your facets in you enable any and a pagination bar.
     
     You need to indicate a Jquery dom selector to specify where the plugin is supposed to put your results, when you type a query it will save the current content of this element and restore it if the query is erased
     
     You can also choose the number of result you want by page and the number of word on the content snippet. 

#### Themes

The plugin embedded a theme support, which allow you to choose the way your results are displayed. You can choose one of the 2 samples themes or build your own [Build your theme]().

The theme is **completely independent** from the Wordpress Theme 

Each theme handle both Autocomplete **AND** Instant Search

### Types

You can choose the Wordpress type you want to index, by default post and page. If one of your extension (Woo commerce for example) create new types you will see them in this panel and can also choose to index them as well. You can order them to change the order of the section in the autocomplete menu.


In autocomplete mode you can specify a label foreach type which will be the name
of the section on the autocomplete menu.

### Attributes

You can choose extra attribute and taxonomies you want to index.

If you use Wordpress as a blog you should not have to change anything. At the contrary when using Wordpress with plugin like WooCommerce this should be very useful as you probaly want to index the price or the total_sales attributes.

When you enable an attribute, the plugin will add this attribute in the records but **it will not be searchable unless you enable it on the Search Configuration tab**.

When you make an attribute facetable **AND are in Instant Search mode** you will see a facet bloc appears in which you can filter your results easily.

There is three facet type you can choose : 

  1. Conjunctive

	  Your refinements on conjunctive facets are ANDed
     
  2. Disjunctive

	  Your refinements on conjunctive facets are ORed (e.g., hotels with 4 OR 5 stars)
      
You can had a label to each enabled facet, and it will appear on the Facet bloc title
 
You can also order the facet blocs

```
Note: A theme can add new facet types
```

### Search Configuration

**For each extra attribute you enabled** you can decide to make it searchable.

If you do you can choose if you want the attribute ordered or unordered

   1. Ordered
      
      Matches in attributes at the beginning of the list **will be** considered more important than matches in attributes further down the list. This setting is recommended for short attribute like title
      
   2. Unordered

   Matches in attributes at the beginning of the list **will not be** considered more important than matches in attributes further down the list. This setting is recommended for long attribute like content 

<pre>
If an attribute does not appears in this tab be sure
that you first enabled it on the <b>Attributes tab</b>
</pre>

### Results Ranking

**For each extra attribute you enabled** you can make them part of your custom ranking and you in which order you want to rank them

<pre>
Attributes that you enable <b>NEED to been numerics</b> 
</pre>

### Sorting

**For each extra attribute and taxonomies you enabled** you can select which one you want be able to sort them. This is useful **only for InstantSearch**.

By enabling a sort, this will create a slave index with the exact same settings except the ranking formula will have the use the attribute you enable as the first ranking criterion

You can choose a label which will be displayed on the instant search result page

## Build your theme

You can build a theme so that the search looks exactly like you want.

```
The simpliest is to copy one of the two defaults themes and work from there
```
<pre>
In the majority of the case the only thing you will need to do is modify
the <b>templates.php</b> file so that you can put you <b>own dom elements to the template</b>
</pre>

```
One thing to know is that there is no link
between the Wordpress theme and the plugin theme.
```

### Folder architecture

A theme is composed of several **MANDATORY** files : 

- config.php
	
	It should look like
	
	```php
	<?php

	return array(
      'name'                      => 'Woo Default',
      'screenshot'                => 'screenshot.png',
      'screenshot-autocomplete'   => 'screenshot-autocomplete.png'
      'facet_types'               => array()
	);
	```
	
	The name attribute will be displayed on the UI Page
	And the screenshot and screenshot-autocomplete is the path of your instant search **AND** autocomplete screenshot **relative to your theme root directory**
	
- styles.css
  
  This file will contain every css rule you need
  
- templates.php

  This file will contain all the Hogan templates you need
  
  The **mandory** content is
  
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
- theme.js

  You should do all js logic in this file.
  
  
### Customize the theme.js file

You have access to the following things

#### the "algoliaSettings" variable

This global javascript variable contains every Settings coming from the back end

It is defined on the AlgoliaPlugin.php as follow :

```
$algoliaSettings = array(
            'app_id'                    => $this->algolia_registry->app_id,
            'search_key'                => $this->algolia_registry->search_key,
            'indexes'                   => $indexes,
            'sorting_indexes'           => $sorting_indexes,
            'index_name'                => $this->algolia_registry->index_name,
            'type_of_search'            => $this->algolia_registry->type_of_search,
            'instant_jquery_selector'   => str_replace("\\", "", $this->algolia_registry->instant_jquery_selector),
            'facets'                    => $facets,
            'number_by_type'            => $this->algolia_registry->number_by_type,
            'number_by_page'            => $this->algolia_registry->number_by_page,
            'search_input_selector'     => str_replace("\\", "", $this->algolia_registry->search_input_selector),
            'facetsLabels'              => $facetsLabels,
            "plugin_url"                => plugin_dir_url(__FILE__)
        );
```

#### the "engine" variable (defined in front/main.js)

You will have access to every function and attribute define in the front/main.js.

You use the functions in this variable as a starting point to make your own or you can use it directly.

Those function are the following :

#### setHelper

To use the engine you should set the AlgoliaSearchHelper at the beginning of your code

#### updateUrl

Save the refinements to the url

#### getRefinementsFromUrl

Load the refinements from the url and make a query with those refinements

#### getFacets

Make an object you can use to render your facets template

#### getPages

Make an object you can use to render your page template

#### getHtmlForPagination

Return the rendered hogan template for pagination

#### getHtmlForResults

Return the rendered hogan template for results

#### getHtmlForFacets

Return the rendered hogan template for facets

##### gotoPage

Set the current page when your are in instant search mode

##### getDate

A function that allow you to get a readable date from timestamp you can use like that in your hogan theme :

```html
{{#getDate}}{{date}}{{/getDate}}
```

##### sortSelected

A function that return "selected" if the parameter is equals to the current sort. You can use in that way in your hogan theme :
  
```html
<input {{#sortSelected}}name_of_the_index{{/sortSelected}} />
```
    
## FAQ

### Are my other plugin compatible ?

It depends. The plugin handle every plugin that integrate in the post table and the meta table of wordpress, if a plugin create its own table the plugin will not work but you can always add support for it.

### How do I handle a non supported plugin

If you want to integrate a plugin that is not handle out of the box, you will need dive into the plugin source code as there is no way to handle it in a generic way

### What If I want to add some attribute to snippet

In the algolia.php file in the root directory of the plugin you can change the following two lines

```php
$attributesToSnippet    = array("content", "content_stripped");
```

For the attributes to snippet you can add a length **EXCEPT for "content" and "content_stripped" which is set from the params in UI integration**.

If you add an attribute it should look like

```php
$attributesToSnippet    = array("content", "content_stripped", "description:30");
```

### What happens if I modify a setting from the dashboard ?

The plugin will try to merge the settings that have been modified from the dashboard.

In every case the following settings will be erased: 

1. For the autocomplete
  * attributesToIndex
  * attributesToHighlight
  * attributesToSnippet

2. For the Instant Search
  * attributesToIndex
  * attributesForFaceting
  * attributesToSnippet
  * customRanking
  * slaves
  * ranking (only for the sorting (slaves) indexes)

### I want to change a facet value for example the "Types" facet, How should I do ?

In the algolia.php file in the root directory of the plugin you want to add those attribute to this line

```php
$facetsLabels = array(
    'post'      => 'Article',
    'page'      => 'Page'
);
```

For example if you want to replace "product" by "Products" in facets elements, it will look like this

```php
$facetsLabels = array(
    'post'      => 'Article',
    'page'      => 'Page',
    'product'   => 'Product'
);
```

This works for any other Facets, but the Type one is the most likely to have non wanted formating

### I want to make a custom attribute searchable, How should I do ?

If you do not see an attribute in the Search Configuration tab and **you checked that you attribute is not in the Attributes tab**, you can go to in the algolia.php file in the root directory of the plugin you want to add those attribute to this line.

```php
$attributesToIndex = array("title", "content", "content_stripped", "author", "type");
```

For example if you want to index an attribute called "texture", it will look like this

```php
$attributesToIndex = array("title", "content", "content_stripped", "author", "type", "texture");
```

### I save my configuration but, I do not see any change on my results ?

When you change an setting that impact the index you may want to use the Re-index data, so that you be able to see the changes