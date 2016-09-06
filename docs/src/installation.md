---
title: Algolia Search plugin for WordPress Installation
description: Algolia Search for WordPress system requirements and plugin installation.
layout: page.html
---
## Server requirements

Algolia Search plugin for WordPress has a few system requirements to be able to work properly.

You will need to make sure your server meets the following requirements:

- PHP >= 5.3
- cURL PHP extension
- mbstring PHP extension

You will also need at least **WordPress in version 3.7.14**.

<div class="alert alert-warning">You will not be able to install the plugin if one of this requirement is not met.</div>

## Plugin Installation

If you are not familiar with the way you install plugins on WordPress, you can read this [excellent blog post](http://www.wpbeginner.com/beginners-guide/step-by-step-guide-to-install-a-wordpress-plugin-for-beginners/).

The official name of the plugin is: **Algolia Search for WordPress**.

## Conflicting plugins

Here is a list of known incompatibilities with other plugins. Be sure you go through them as you are installing the plugin:

**W3 Total Cache:**
- The autocomplete experience will not work if JavaScript is minified with W3 Total Cache
- It looks like the queue processing does not work with object caching turned on. (We have been unable to reproduce this one yet).

**Post Type Switcher**
- This plugin will trigger an infinite loop upon updating/creating/saving a post because it will convert 'algolia_task' post types to the one provided on the post form instead.
