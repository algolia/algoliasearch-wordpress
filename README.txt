=== Search by Algolia – Instant & Relevant results ===
Contributors: algolia, rayrutjes
Tags: Search, Algolia, Autocomplete, instant-search, relevant search, search highlight, faceted search, find-as-you-type search, suggest, search by category, ajax search, better search, custom search
Requires at least: 4.4
Tested up to: 4.7
Stable tag: trunk
License: MIT License, GNU General Public License v2.0

Search by Algolia is the smartest way to improve search on your site. Autocomplete is included, along with full control over look, feel and relevance.

== Description ==

The plugin provides relevant search results in milliseconds, ensuring that your users can find your best posts at the speed of thought. It also comes with native typo-tolerance and is language-agnostic, so that every WordPress user, no matter where they are, can benefit from it.

= About pricing =

This plugin relies on the [Algolia service](https://www.algolia.com/) which requires you to [create an account here](https://www.algolia.com/users/sign_up).
Algolia offers its Search as a Service provider on a incremental payment program, including a free Hacker Plan which includes 10,000 records & 100,000 operations per month. Beyond that, plans start at $49/month.

Note that there isn’t a direct correlation between the number of posts in WordPress and the number of records in Algolia.
Also note that we only offer support to paying plans.
On average, you can expect to have about 10 times more records than you have posts, though this is not a golden rule and you could end up with more records.

= Getting started guide =
Once you have installed the plugin, you can follow the step by step guide provided here: https://community.algolia.com/wordpress/configuration.html

= Relevance =
Algolia enhances your search functionality with a completely customizable search experience which can be seamlessly integrated into your Wordpress theme. It lets you create a find-as-you-type experience or an auto-complete dropdown menu, which provides relevant results from the first keystroke. Our extension also automatically synchronizes data (posts, taxonomies etc.) in real-time, making sure that any updates to your site are available as soon as they are made.

= Speed =
Algolia returns results in under 35ms on an average – irrespective of whether you have 100s or 1000s of posts.
Our state-of-the-art infrastructure and distributed search network ensures that your readers benefit from this, no matter where they are.

= Accessibility =
Search by Algolia gives you the ability to make all forms of data – blog posts, categories, users etc. – searchable from a single search bar. Algolia also gives you the ability to completely customize your search results based on criteria that makes sense for your business, such as popularity, date, relevance etc.

= Built by developers for developers =
Search by Algolia is also completely configurable and fully extensible by means of WordPress filters and hooks, letting you build a custom search experience or theme based on Algolia.

Want to see out how we did it?

Check it out here: [Search by Algolia GitHub Repository](https://github.com/algolia/algoliasearch-wordpress)

Join the Algolia community and meet thousands of search enthusiasts. We’re also always on the lookout for feedback: https://community.algolia.com/wordpress

== Frequently Asked Questions ==

= Where can I find Search by Algolia documentation and user guides? =

- For help setting up and configuring Search by Algolia please refer to our [user guide](https://community.algolia.com/wordpress/installation.html)
- For extending or theming the Autocomplete dropdown, see our [Autocomplete Customization guide](https://community.algolia.com/wordpress/customize-autocomplete.html).
- For extending or theming the Instant Search results page, see our [Search Page Customization guide](https://community.algolia.com/wordpress/customize-search-page.html).

= Will Search by Algolia work with my theme? =

Yes; Search by Algolia will work with any theme, but the Instant Search results page may require some styling to make it match nicely.

= Where can I report bugs or contribute to the project? =

Bugs can be reported either in our support forum or preferably on the [Search by Algolia GitHub repository](https://github.com/algolia/algoliasearch-wordpress).

= My issue is not listed here, what should I do? =

Please check out the [Frequently Asked Questions](https://community.algolia.com/wordpress/frequently-asked-questions.html) on our website which might have more information than this thread.
It will also give you guidance about where to ask support if your question is not covered.


== Installation ==

= Minimum Requirements =

* PHP version 5.3 or greater (PHP 5.6 or greater is recommended)
* MySQL version 5.0 or greater (MySQL 5.6 or greater is recommended)
* Some payment gateways require fsockopen support (for IPN access)
* Requires WordPress 3.7+ (WordPress 4.4+ is recommended because we will drop support below it in upcoming releases)

Visit the [Search by Algolia server requirements documentation](https://community.algolia.com/wordpress/installation.html) for a detailed list of server requirements.

= Automatic installation =

Automatic installation is the easiest option as WordPress handles the file transfers itself and you don’t need to leave your web browser. To do an automatic install of Search by Algolia, log in to your WordPress dashboard, navigate to the Plugins menu and click Add New.

In the search field type “Algolia” and click Search Plugins. Once you’ve found our search plugin you can view details about it such as the point release, rating and description. Most importantly of course, you can install it by simply clicking “Install Now”.

= Manual installation =

The manual installation method involves downloading our search plugin and uploading it to your webserver via your favourite FTP application. The WordPress codex contains [instructions on how to do this here](https://codex.wordpress.org/Managing_Plugins#Manual_Plugin_Installation).

= Updating =

Automatic updates should work like a charm; as always though, ensure you backup your site just in case.

== Screenshots ==

1. Algolia Settings.
2. Indexing configuration.
3. Instant-search results configuration.
4. Autocomplete dropdown configuration.
5. Autocomplete dropdown example.
6. Instant-search results page example.

== Changelog ==

= 1.7.0 =

To upgrade from 1.6.0, follow the [Upgrade instructions](https://community.algolia.com/wordpress/upgrade.html#from-1-6-0-to-1-7-0).

* Fix the condition to remove the powered by
* Use autocomplete.js in noConflict mode
* Also append Cookies to wp_remote_post test calls on indexing screen
* Update WordPress tested up to 4.7
* Ensure wp-util is always loaded before instantsearch.js or autocomplete.js
* Enable Yoast frontend hooks when indexing records
* Check if the API is reachable before executing CLI commands

= 1.6.0 =

* Re-index indices instead of moving them when index name prefix is changed
* Keep the synonyms configured on the Algolia dashboard when we re-index
* Add a command to the WP-CLI integration to re-index a single index
* Allow API Keys and index name prefix to be configured with constants to ease switching between environments

= 1.5.0 =

* Split content attribute over several records if greater than 5000 bytes
* Support changing prefix on indices having replicas
* Add a constant to disable post splitting
* Fix an issue where the index name would rely on a non existing 'label' key of the post type object
* Replace Visual Composer shortcodes in posts

= 1.4.1 =

* Fix the validity check for the Admin API key in the settings tab

= 1.4.0 =

* Introduce the `algolia_loopback_request_args` filter to override loopback args. Allows queue to work with Basic Authentication
* Allow user generated Admin API keys
* Resolve logging performances causing queue to crash before being able to move _tmp indices to their final destination
* Make sure "shortcodes" are parsed inside post excerpts
* Add support for non UTF-8 content
* Display a more explicit error when credential validation fails
* Add a filter `algolia_search_params` to be able to filter backend search parameters

= 1.3.0 =

* Make the plugin play nicely with the W3 Total Cache plugin
* Fix an issue where the Cache Enabler plugin would break the autocomplete when JavaScript was inlined
* Introduced a constant to hide admin help notices
* Add WP CLI process-queue & re-index-all commands
* Allow theme authors to get full control over the Algolia templates location
* Stop the queue instead of trashing failed tasks
* Allow users to delete all pending tasks
* Allow users to stop the queue when it is running

= 1.2.0 =

* Fix broken pagination on instant search page
* Fix conflicts with plugins also using the PHP `simple_html_dom` library
* Limit the maximum number of log entries to 50 by default
* Fix an issue where empty errors would get logged during queue processing
* Introduce class `.no-autocomplete` to disable autocomplete on search inputs
* Add support for a new constant `ALGOLIA_LOOPBACK_HTTP` that allows forcing HTTP in queue loopback

You can access older changes [here](https://github.com/algolia/algoliasearch-wordpress/blob/master/CHANGELOG.md)

== Upgrade Notice ==

= 1.3.0 =

We fixed some issues in the `templates/autocomplete.php` and `templates/instantsearch.php` files.

If you have copied the templates folder in your theme folder, you should make sure you manually patch the following fixes:

- https://github.com/algolia/algoliasearch-wordpress/commit/81ce6975ad1069d8db95cdb92cbd60e139328465

= 1.2.0 =

We fixed some issues in the `templates/autocomplete.php` and `templates/instantsearch.php` files.

If you have copied the templates folder in your theme folder, you should make sure you manually patch the following fixes:

- https://github.com/algolia/algoliasearch-wordpress/pull/305

