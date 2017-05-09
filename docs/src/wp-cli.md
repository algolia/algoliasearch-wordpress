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

## Command: algolia reindex [<indexName>] [--clear] [--all]

The following command will re-index all items belonging to a given index.

If the `--all` parameter is passed, all enabled indices will be re-indexed.

If the `--clear` parameter is passed, all existing records will be cleared prior to pushing new records. Also works with in combination with the `--all` parameter.

```bash
wp algolia reindex posts_post
# or
wp algolia reindex --all
# or
wp algolia reindex --clear --all
```
