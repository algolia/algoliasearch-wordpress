[TAGS]
[LONG DESCRIPTION] markdown description
[SCREENSHOTS] example:
                1. This screen shot description corresponds to screenshot-1.(png|jpg|jpeg|gif). Note that the screenshot is taken from
                the /assets directory or the directory that contains the stable readme.txt (tags or trunk). Screenshots in the /assets
                directory take precedence. For example, `/assets/screenshot-1.png` would win over `/tags/4.3/screenshot-1.png`
                (or jpg, jpeg, gif).
                2. This is the second screen shot

=== Plugin Name ===
Contributors: Algolia
Donate link: http://example.com/
Tags: [TAGS]
Requires at least: 3.8.x
Tested up to: 4.2
Stable tag: 1.0
License: MIT
License URI: http://opensource.org/licenses/MIT

As-You-Type Auto-Complete and Instant Search Results Page, all Customizable & Open-Source.

== Description ==

## Build unique search experience

With Algolia's as-you-type search readers think less about how to search and more about what they find. Serve relevant content from the first keystroke.


## Features

### Relevance

Increase customer satisfaction by serving relevant results. If a user can't find a product, they won't buy it!

### Speed

Deliver lightning fast results to your customers, anywhere in the world thanks to our distributed search network.


### Typo Tolerance

Don't let spelling mistakes prevent your users from finding the products they are looking for.


### Post Auto-Sync

Benefit from realtime indexing that instantly captures post page additions. No matter server performance.

### Auto-completion menu

Implement a rich Amazon-like auto-completion menu in no time.

### Analytics

From a simple dashboard, get valuable information on how your customers are searching and what they're searching for.

### Distributed Search Network

Algolia's Distributed Search Network brings the instant responsiveness of your search engine to all your users around the world. Thanks to our 12 data centers, your search engine can now be located where your users are, allowing results delivery times under 50ms in the world's top markets.

### And more

- Multilingual store support
- Results computed in less than 10ms
- Smart highlighting
- Work seamlessly on all devices
- Geo-search
- 99.99% SLA
- REST and JSON-based API

## Developer Friendly

### 100% Customizable UI

Make it look like anything you want! Algolia does not restrict you to any user interface implementation.

### Easy Integration

Integrate Algolia Search API on your Wordpress store in minutes.

### Open source

This module is also available on GitHub


## About Algolia

Founded in 2012, Algolia’s aim is to make searching on a website or a mobile app as fast and effective as possible. To this end, Algolia provides developers with an easily-integrated search API offering an intuitive “as-you-type” design that refreshes search results in real time (less than 10ms in average). Ultimately, this enhanced search experience provides an overall boost to user engagement on online and mobile services. With offices in San Francisco and Paris, Algolia currently has over 700 clients in 50 countries. The company’s recent funding round secured over $18M, primarily from growth equity firm Accel Partners. To find out more about Algolia, visit our website: www.algolia.com

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

It depends. The plugin handles every other plugins that uses the `post` and `meta` tables of Wordpress. If a plugin creates its own tablem the plugin will not be able to see it.

= How do I handle a non-supported plugin? =

You can use the filters to add any attribute you want to the record

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