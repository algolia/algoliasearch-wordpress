---
title: The basics for extending Algolia Search for WordPress
description: Discover the best way to extend the plugin to customize the behaviour.
layout: page.html
---
## How To Extend

The plugin has been built with extensibility in mind. We have tried to wisely provide `filters` and `actions` but also `constants`.

<div class="alert alert-warning">You should never modify files inside the `wp-content/plugins` directory. If you find yourself in a situation where you require to hack the plugin, let us know so that we can improve the plugin.</div>

## Create your Custom Plugin

Your first step is to create your own plugin. You can find [detailed explanation on how to create a WordPress plugin here](https://developer.wordpress.org/plugins/the-basics/).

You can also simply create a file `my-blog.php` like this:

```php
<?php
/*
Plugin Name: My Blog
*/
```

Then you should be able to enable that plugin from your WordPress admin panel.

You can now hook into the Algolia Search for WordPress plugin!

## Extend from a theme

Every theme contains a `functions.php` file where you can also extend the Algolia Search plugin.

If you choose to use your theme files to extend the plugin, please consider creating a child theme so that you can update your theme
without losing your changes.

You can read more about [child themes in the official documentation](https://codex.wordpress.org/Child_Themes).
