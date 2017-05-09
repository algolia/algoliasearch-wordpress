---
title: Migration Best Practices
description: How to promote the plugin from dev to staging and from staging to production.
layout: page.html
---
## Introduction

It is a common practice to run your website changes on a development or staging environment before pushing them to production.

The Algolia plugin for WordPress requires you to take some additional steps in order to work properly once promoted.

First we have to understand some of the challenges.

## Full URLS are indexed

When indexing content, we always index full URLS to allow every plugin to be able to intervene in the generation process.
We can not simply remove the domain name of URLS, because one website might use a CDN strategy for example.

<div class="alert alert-warning">You will always need to re-index all your content from your production environment to update all URLS, otherwise the results will point to your staging environment.</div>

## Use index prefixes to allow multiple websites on a same Algolia account

Each index stored in Algolia has a prefix. You should always use a prefix that contains a reference to your current environment's purpose.

For example, use the following index prefixes to make things obvious:

* `mywebsite_prod_` => for production
* `mywebsite_staging_` => for staging
* `mywebsite_dev_` => for development

As you might notice, we always also include a unique way to identify the website, here `mywebsite_`.
By doing so, you ensure that you will easily identify indices even if you have multiple website bound to a single Algolia account.

<div class="alert alert-info">As rule of thumb, I'd recommend you always use the following pattern for your index prefixes: `{websitename}_{environment}_`.</div>
<div class="alert alert-warning">Don't forget the trailing underscore; `_`; if you want your index names to stay readable in the Algolia dashboard.</div>

## Avoid breaking frontend search

There are several situations where your frontend implementations might break if you don't take some precautions.

Here are some situations where the frontend might break:

* If your templates rely on newly indexed fields that have not been pushed yet
* If you upgrade the plugin and there is a mention of a breaking change on the plugin
* If you customize the data pushed to Algolia by the means of filter hooks
* If you install a plugin that uses filter hooks to customize the data pushed to Algolia

**To avoid search downtime, we recommend you always make sure your website works fine when disabling Algolia.**

You should be able to disable the `autocomplete dropdown` and the `instant search results page` experiences without breaking your website.

This is the default behaviour if you use the template overriding strategy defined here:

* [Override autocomplete template](customize-autocomplete.html#customization)
* [Override search page template](customize-search-page.html#customization)

<div class="alert alert-info">As a best practice, before pushing your implementation to production, we recommend you turn off `autocomplete` and `instant search page`. You will be able to re-enable them from the production environment once you made sure your indices are up-to-date with the templates.</div>

## From staging to production steps

To conclude this section, here are the steps to bring the Search by Algolia plugin to production from a staging environment.

1. **Make sure you are using a prefix like `mywebsite_staging_`**, and if not, change it on the Indexing page of the plugin,
2. **Disable the autocomplete and instantsearch experiences** to make sure your search still works without Algolia,
3. **Promote your code and your plugin configuration** to production
4. **Update the index name prefix** to `mywebsite_production_`,
5. **Hit the <span class="wp-btn">Re-index everything</span> button** on the Indexing page of the plugin,
6. **Wait for the indexing to finish** by watching the queue emptying itself (you don't need to stay on the page though, the queue processing will continue even if you leave the page).
7. **Re-enable to autocomplete and the search page features** of the plugin on the dedicated pages of those features.

🎉You should now have Algolia up and running smoothly in your production environment 🎉 !


<div class="alert alert-info">If you experience any problem applying this guide, please reach out to support+wordpress@algolia.com so we can improve it!</div>

## Ease environment switching with constants

Starting from version 1.6.0, you can add the following lines to your `wp-config.php` file:

```
define( 'ALGOLIA_APPLICATION_ID', '<your_application_id>' );
define( 'ALGOLIA_SEARCH_API_KEY', '<your_search_api_key>' );
define( 'ALGOLIA_API_KEY', '<your_api_key>' );
define( 'ALGOLIA_INDEX_NAME_PREFIX', '<your_index_name_prefix>' );
```

<div class="alert alert-info">Adding the constants to your `wp-config.php` will disable the editing of those fields via the admin panel.</div>
<div class="alert alert-warning">When you are using the constant for setting the indices name prefix we have no way to detect the change so you should re-index your data form the admin panel.</div>
