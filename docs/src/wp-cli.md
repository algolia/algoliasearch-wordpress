---
title: WP-CLI
description: WP-CLI Algolia commands.
layout: page.html
---
## Introduction

WP-CLI is an extensible third party [Command Line tool for WordPress](http://wp-cli.org/).

This plugin comes shipped with commands that can be triggered via the tool.

## Installation

To setup WP-CLI please checkout its [official documentation](http://wp-cli.org/#installing).

## Command: algolia process-queue

The following command will process all the pending tasks of the queue.

By triggering the processing this way, you won't trigger any http remote call to loop over the task, making it a good alternative to the default indexing logic.

<div class="alert alert-warning">Please note that your queue must not be already running. You can go stop it from the Indexing page of the plugin if needed.</div>

```bash
wp algolia process-queue
# or
wp algolia process_queue
```

## Command: algolia re-index-all

The following command will queue all indices for re-indexing.

<div class="alert alert-warning">This does not trigger the processing of the queue. Please see the `process-queue` command to process all generated tasks.</div>

```bash
wp algolia re-index-all
# or
wp algolia re_index_all
```



