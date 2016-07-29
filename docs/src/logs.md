---
title: Logs
description: Track what is going on with the built in logs.
layout: page.html
---
## Introduction

There is a lot going on behind the scenes of the Algolia Search plugin for WordPress which is precisely why we provide you with some logs.

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


