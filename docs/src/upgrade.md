---
title: Upgrade instructions
description: How to upgrade the Algolia plugin for WordPress from one version to another.
layout: page.html
---
## About

This section explains the required changes from one version of a plugin to another.

## From <= v1.7.0 to v2.0.0

**The change**

In v2.0.0 we resolved most of the conflicts with third party plugins. We did so by simplifying the way we index data.

**Required change on your side**

1. If you previously implemented a custom `autocomplete.php` template, you need to update your changes according to the new template shipped with the plugin.
1. If you previously implemented a custom `instantsearch.php` template, you need to update your changes according to the new template shipped with the plugin.
1. You will need to re-index all your indices and also hit the "save" button of the autocomplete & search page in the admin.

## From <= v1.6.0 to v1.7.0

**The change**

In v1.7.0 we made sure the `autocomplete.js` library does not conflict with jQuery UI autocomplete and Google Maps Autocomplete.

To do so we introduced a `noConflict` strategy.

The Algolia autocomplete instance is now accessible under the `algoliaAutocomplete` name.

**Required change on your side**

If you previously implemented a custom `autocomplete.php` template, you need to replace occurrences of `autocomplete` with `algoliaAutocomplete` in your JavaScript implementations.
