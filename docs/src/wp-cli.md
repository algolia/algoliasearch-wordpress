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

<div class="alert alert-warning">
If you are using WordPress in a multiwesbite configuration (network enabled), you need to use the `--url` argument to precise on what website you want to execute the commmand.
If you get the following message: `The configuration for this website does not allow to contact the Algolia API.`, it means you are trying to execute a command on a website that
is not configured properly to reach the Algolia API (missing the API keys for example).
</div>

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

## Command: algolia re-index

The following command will queue a single index for re-indexing.

<div class="alert alert-warning">This does not trigger the processing of the queue. Please see the `process-queue` command to process all generated tasks.</div>

```bash
wp algolia re-index <index_id>
# or
wp algolia re_index <index_id>
```
