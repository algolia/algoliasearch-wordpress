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
    * 1 index for each sort you enable
   
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
Foreach type you can specify a label which we be the name of the section on the autocomplete.

```
Order and label of types will have no impact on Instant Search
```

### Attributes

You can choose extra attribute you want to index. If you use Wordpress as a blog you should not have to change anything. At the contrary when using Wordpress with plugin as WooCommerce this should be very useful as you probaly want to index the price or the total_sales attributes.

When you enable an attribute, the plugin will add this attribute in the records but **it will not be ableble unless you enable it on the Search Configuration tab**.

When you make an attribute facetable **AND your are in Instant Search mode** you will see a facet bloc appears in which you can filter your results easily.

There is three facet type you can choose : 

  1. Conjunctive

	  Your refinements on conjunctive facets are ANDed
     
  2. Disjunctive

	  Your refinements on conjunctive facets are ORed (e.g., hotels with 4 OR 5 stars)
  
  3. Slider

      The slider facet is a Disjunctive facet but will have an different look.
      Be careful when using this type, your attribute **MUST be an numeric attribute**
      
You can had a label to each enabled facet, and it will appear on the Facet bloc title
 
You can also order the facet blocs

```
Note: Your taxonomies facets will always appears before your attributes facets
```

### Taxonomies

You can choose the taxonomie you want to index

In a similar way to attributes you can enable facets 
There is two facet type (conjunctive and disjuntive) you can choose, see [Attributes]() for more info.

### Search Configuration

**For each extra attribute you enabled** you can decide to make it searchable.

If you do you can choose if you want the attribute ordered or unordered

   1. Ordered
      
      Matches in attributes at the beginning of the list **will be** considered more important than matches in attributes further down the list. This setting is recommended for short attribute like title
      
   2. Unordered

   Matches in attributes at the beginning of the list **will not be** considered more important than matches in attributes further down the list. This setting is recommended for long attribute like content 

<pre>
If an attribute does not appears in this tab be sure
that you enabled it first on the <b>Attributes tab</b>
</pre>

### Result Ranking

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
the <b>templates.php</b> file so that you can put you <b>own class name to the templates</b>
</pre>

One thing to know is that there is no link between the Wordpress theme and the plugin theme.

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

  You should do all event handling in this file.
  
  The **mandatory** content **(only if you use the default searchCallback)** is :
  
  ```
  window.finishRenderingResults = function() {
  }
  ```
  
### Customize the main.js file

From this file you can define every event handler you need

#### algoliaSettings variable

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

#### engine variable

You will have access to every function and attribute define in the front/main.js

### Customize the template.php file

Each template will be parsed by Hogan

---

#### autocomplete-template

You have access to every attributes that are in the record

For example to print the highlighted title you can do :

```
{{{ _highlightResult.title.value }}}
```

---

#### instant-content-template

You have access to every attributes that are in the record and the following attriubtes:
 
##### facets_count

  The number of facets
  
##### getDate

  A function that allow you to get a readable date from timestamp you can use like that :

  ```html
  {{#getDate}}{{date}}{{/getDate}}
  ```
  
##### sortSelected
  
  A function that return "selected" if the parameter is equals to the current sort. You can use in that way :
  
  ```html
  <input {{#sortSelected}}name_of_the_index{{/sortSelected}} />
  ```
  
##### relevance_index_name
  
  The name of the default index_name
  
##### sorting_indexes

  An array containing every indexes
  
  ```html
  {{#sorting_indexes.length}}
	  <div>{{index_name}}</div>
  {{/sorting_indexes.length}}
  ```
  
  could will print
  
  ```html
  <div>index_name_1_asc</div>
  <div>index_name_2_desc</div>
  ```

##### hits
  
  The hits attribute present on the algolia response
  
##### nbHits
  
  The number of results matching the query

##### nbHits_zero

  Booleean true if nbHits == 0

##### nbHits_one

  Booleean true if nbHits == 1
  
##### nbHits_many
  
  Booleean true if nbHits > 1
  
##### query

  The current query
  
##### processingTimeMS

  The algolia processing time

---

#### instant-facets-template

You have access to the following attributes:

##### facets

An array of facets with contain the following attributes :

  - count

    Number of item for the facet
    
  - tax:
    
    The name of the wordpress attribute coresponding to the facet
    
  - facet\_categorie\_name

    Label of the facets
    
  - sub\_facets

    An array of facet item which contain the following attributes

    - conjunctive
  
      Booleean true the facet in a conjunctive one
    
    - disjunctive

      Booleean true the facet in a disjunctive one
    
    - slider

      Booleean true the facet in a slider one
    
    - checked (not present if a slider facet)
  
      Booleean true the facet in a slider one
  
    - name
  
      Label choose on the plugin config
  
    - count

      Number of results under the facet
  
    - current_max (only present on slider facet)

      current max refinement
    
    - current_min (only present on slider facet)

      current min refinement

    - min (only present on slider facet)

      Min value of the slider

    - max (only present on slider facet)

      Min value of the slider
     
##### count

The number of facets

##### sorting_indexes

An array containing every indexes
  
```html
{{#sorting_indexes.length}}
   <div>{{index_name}}</div>
{{/sorting_indexes.length}}
```
  
could will print
  
```html
<div>index_name_1_asc</div>
<div>index_name_2_desc</div>
```

##### getDate

A function that allow you to get a readable date from timestamp you can use like that :

```html
{{#getDate}}{{date}}{{/getDate}}
```

##### sortSelected

A function that return "selected" if the parameter is equals to the current sort. You can use in that way :
  
```html
<input {{#sortSelected}}name_of_the_index{{/sortSelected}} />
```
    
---  
  

#### instant-pagination-template

You have access to the following attributes:

##### pages

  An array containing page object which contains the following attributes :
  
  - current
    
    Boolean true if the page is the current page
    
  - number

    The page number
    
  - disabled
  
    Boolean true if the button need to be disabled
  
  
##### prev_page
	
The previous page number if there is one. Else false

##### next_page

The next page number if there is one. Else false



### Custom searchCallback function

If you want to Customize even more you can replace the searchCallback function by your own, you will be able to custom everything

On your **theme.js** file instead of

```js
engine.performQueries(false);
```

define

```
function yourSearchCallback(success, content)
{
}
```

and then do

```
this.performQueriesWithCustomSearchCallback(false, yourSearchCallback)
```

## FAQ

### How do I handle a non supported plugin

If you want to integrate a plugin that is not handle out of the box, you will need dive into the plugin source code as there is no way to handle it in a generic way

### What If I want to add some attribute to highlight or to snippet

In the algolia.php file in the root directory of the plugin you can change the following two lines

```php
$attributesToHighlight  = array("title", "content", "author", "type");
$attributesToSnippet    = array("content");
```

For the attributes to snippet you can put need to put a length **EXCEPT for content** which is set from the params in UI integration.

If you add an attribute it should look like

```php
$attributesToSnippet    = array("content", "description:30");
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
  * attributesToHighlight
  * attributesToSnippet
  * customRanking
  * slaves
  * ranking (only for the sorting indexes)

### Are my other plugin compatible ?

It depends. The plugin handle every plugin that integrate in the post table and the meta table of wordpress, if a plugin create its own table the plugin will not work but you can always add support for it see [Handle a non supported plugin]()

### I want to change a facet item name for example the "Types" facet, How should I do ?

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

This works for any other Facets, but the Type one is the most likely to have non wanted formats

###I want to make a custom attribute searchable, How should I do ?

If you do not see an attribute in the Search Configuration tab and **you check that you attribute is not in the Attributes tab**, you can go to in the algolia.php file in the root directory of the plugin you want to add those attribute to this line.

```php
$attributesToIndex = array("title", "content", "author", "type");
```

For example if you want to index an attribute called "texture", it will look like this

```php
$attributesToIndex = array("title", "content", "author", "type", "texture");
```

###I save my configuration but, I do not see any change on my results ?

When you change an setting that impact the index you may want to use the Re-index data, so that you be able to see the changes