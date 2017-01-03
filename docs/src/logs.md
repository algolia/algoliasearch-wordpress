---
title: Logs
description: Track what is going on with the built in logs.
layout: page.html
---
## Introduction

There is a lot going on behind the scenes of the Algolia Search plugin for WordPress which is precisely why we provide you with some logs.

<div class="alert alert-warning">If the plugin does not work for you and you have no errors logged on the Logs page of this plugin, please be sure to check for [PHP logs](#php-logs).</div>

## Log Levels

Each log entry is attributed a `Log Level` to ease the audit log reading.

Available log levels:
- **info:** Useful information on something that happened.
- **error:** Something is probably wrong somewhere and needs to be fixed.
- **operation:** Algolia API client call that has lead to an operation being counted. [Read more about operations](https://www.algolia.com/doc/faq/basics/what-is-an-operation).

## Filter Logs Display

On the `Logs` admin page, you can filter the log entries by log level. This is very convenient if you want to isolate errors, or only track down operations.

To do so, simply click on the log level you want to filter by.

![Filter Logs Display](img/logs/filter-logs-display-1.png)

## Communicating Logs

When you are asking for support on [Stack Overflow](http://stackoverflow.com/questions/tagged/algolia+wordpress) for example, we recommend you join some logs if your issue is related to indexing problems, or if your question is quota related.

This way, people will get a better sense of what is happening in your WordPress website.

## PHP logs - With an extension

If you are not technical or find it easier to use a plugin to access the PHP error logs, you can use: https://wordpress.org/plugins/error-log-monitor/

Once installed, you will be able to see your PHP error logs in a widget on the WordPress dashboard screen.

## PHP logs - Manual

Most of the job done by this plugin is done asynchronously which makes debugging a bit harder.
For that reason we introduced our custom logs like explained in the previous sections of this page.

When you are unable to debug your issues with the provided logs, it probably means you have PHP exceptions that are being raised behind the scenes.

**To access your PHP logs you should:**

1. Turn WordPress debug mode on:

	Simply add the following lines to your `wp-config.php`:

	```
	<?php
	// [...]
	define( 'WP_DEBUG', true );
	define( 'WP_DEBUG_LOG', true );
	// [...]
	```

	<div class="alert alert-warning">If these lines are already there, be sure they are defined to `true`.</div>

2. Re-try the operations you expect to go wrong, like hitting the <span class="wp-btn">Process queue</span> button on the Indexing page of the plugin.
3. Check what has been logged in the `wp-content/debug.log` file.

<div class="alert alert-info">If none of that helped, please head to the [frequently asked questions](frequently-asked-questions.html) to get help.</div>
