---
title: Upgrade instructions
description: How to upgrade the Algolia plugin for WordPress from one version to another.
layout: page.html
---
## About

This section explains the required changes from one version of a plugin to another.


## From <= 1.6.0 to 1.7.0

**The change**

In 1.7.0 we made sure the `autocomplete.js` library does not conflict with jQuery UI autocomplete and Google Maps Autocomplete.

To do so we introduced a `noConflict` strategy.

The Algolia autocomplete instance is now accessible under the `algoliaAutocomplete` name.

**Required change on your side**

If you previously implemented a custom `autocomplete.php` template, you need to replace occurrences of `autocomplete` with `algoliaAutocomplete` in your JavaScript implementations.
