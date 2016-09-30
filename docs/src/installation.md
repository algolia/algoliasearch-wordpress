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
- `/wp-admin` part of the website should not be protected behind an .htaccess password as this won't allow the queue to handle tasks
- OpenSSL greater than 1.0.1
- WordPress in version 3.7.x

<div class="alert alert-warning">You will not be able to install the plugin if one of this requirement is not met.</div>

## Plugin Installation

Please follow installation steps here: https://wordpress.org/plugins/search-by-algolia-instant-relevant-results/installation/

## Conflicting plugins

Here is a list of known incompatibilities with other plugins. Be sure you go through them as you are installing the plugin:

**W3 Total Cache:**
- Object caching may cause admin UI to no display indexing status in realtime.
