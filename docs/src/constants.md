---
title: List of available constants
description: Constants that can be added to your wp-config.php
layout: page.html
---
## Introduction

Constants are variables that can be added to your `wp-config.php`.

They are generally used for configuring development or technical features.

To use a constant, simply add a line to your `wp-config.php` file like so:

```
<?php
// [...]
define( 'ALGOLIA_AUTO_PROCESS_QUEUE', false );
/* That's all, stop editing! Happy blogging. */
// [...]
```

<div class="alert alert-warning">Make sure you define your constants before the `That's all, stop editing! Happy blogging.` comment, like showcased just above.</div>

## Constants Reference

Here is the list of all available constants.

| Constant Name | Default Value | Description |
| --- | --- | --- |
| ALGOLIA_AUTO_PROCESS_QUEUE | true | By default, every time a new indexing task is queued, the plugin tries to process them. By passing this constant to false, you can process the queue by pressing the 'process queue' button on the indexing page, or choose to programatically trigger it. |
| ALGOLIA_LOOPBACK_HTTP | false | Allow you to force the queue remote calls to be done over HTTP even if your site is served over HTTPS. This is useful in certain environments where the SSL certificates can not be validated because of old version of the cURL library. |
| ALGOLIA_MAX_LOG_ENTRIES | 50 | Maximum number of log entries before we start discarding old entries. |
| ALGOLIA_POWERED_BY | true | By passing this constant to false, you will remove the Algolia branding from the autocomplete.js and instantsearch.js implementations. Please note however that we ask you to not remove the Algolia logo if you are on a Hacker plan. |
| ALGOLIA_HIDE_HELP_NOTICES | false | By turning this on, you will hide help notices on the autocomplete and instantsearch admin pages. This is sometimes useful if you are using a custom autocomplete or instantsearch implementation.|
| ALGOLIA_SPLIT_POSTS | true | By turning this off, 1 post will equal 1 record but content will be truncated to fit into the Algolia payload max size.|
