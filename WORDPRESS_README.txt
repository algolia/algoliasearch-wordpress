[Tags]
[SHORT DESCRIPTION] Here is a short description of the plugin.  This should be no more than 150 characters.  No markup here.
[LONG DESCRIPTION] markdown description
[SCREENSHOTS] example:
                1. This screen shot description corresponds to screenshot-1.(png|jpg|jpeg|gif). Note that the screenshot is taken from
                the /assets directory or the directory that contains the stable readme.txt (tags or trunk). Screenshots in the /assets
                directory take precedence. For example, `/assets/screenshot-1.png` would win over `/tags/4.3/screenshot-1.png`
                (or jpg, jpeg, gif).
                2. This is the second screen shot

=== Plugin Name ===
Contributors: algolia
Donate link: http://example.com/
Tags: [TAGS]
Requires at least: 3.8.x
Tested up to: 4.2
Stable tag: 1.0
License: MIT
License URI: http://opensource.org/licenses/MIT

[SHORT DESCRIPTION]

== Description ==

[LONG DESCRIPTION]

== Installation ==

1. Upload the plugin to the `/wp-content/plugins/` directory
2. Create an Algolia account with [https://www.algolia.com/users/sign_up](https://www.algolia.com/users/sign_up)
3. Once logged in, go to the "Credential" section and get your Application ID & API keys
4. Install Algolia Wordpress Plugin in your Wordpress dashboard
5. Activate the plugin through the Plugins menu in Wordpress
6. Go to the "Algolia Search" left menu configuration page and fill the your API keys
7. Configure the way Algolia is indexing your posts, products

== Frequently Asked Questions ==

= Are my other plugins compatible? =

It depends :) The plugin handles every other plugins that uses the `post` and `meta` tables of Wordpress. If a plugin creates its own tablem the plugin will not be able to see it.

= How do I handle a non-supported plugin? =

If you want to integrate a plugin that is not handled out of the box, you will need to dive into the plugin source code since there is no way to handle it in a generic way.

= What If I want to add some attributes to snippet? =

In the `algolia.php` file in the root directory of the plugin you can change the following two lines:

```php
$attributesToSnippet    = array("content");
```

For the attributes to snippet you can add a length **EXCEPT for "content" which is set from the params in UI integration**. If you add an attribute it should look like

```php
$attributesToSnippet    = array("description:30");
```

= What happens if I modify a setting from the Algolia dashboard? =

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

== Screenshots ==

[SCREENSHOTS]

== Changelog ==

= 1.0.0 =
* First Release


== Upgrade Notice ==

= 1.0.0 =
* None