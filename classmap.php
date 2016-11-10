<?php

if ( ! defined( 'ALGOLIA_PATH' ) ) {
	exit();
}

// The Algolia Search PHP API Client.
require_once ALGOLIA_PATH . 'includes/libraries/algoliasearch-client-php/algoliasearch.php';

// The PHP DOM parser that splits posts into multiple Algolia records.
require_once ALGOLIA_PATH . 'includes/libraries/php-dom-parser/lib/simple_html_dom.php';
require_once ALGOLIA_PATH . 'includes/libraries/php-dom-parser/src/DOMParser.php';

// The TechCrunch async task handling.
require_once ALGOLIA_PATH . 'includes/libraries/wp-async-task/wp-async-task.php';

require_once ALGOLIA_PATH . 'includes/class-algolia-activator.php';
require_once ALGOLIA_PATH . 'includes/class-algolia-deactivator.php';
require_once ALGOLIA_PATH . 'includes/class-algolia-api.php';
require_once ALGOLIA_PATH . 'includes/class-algolia-autocomplete-config.php';
require_once ALGOLIA_PATH . 'includes/class-algolia-compatibility.php';
require_once ALGOLIA_PATH . 'includes/class-algolia-logger.php';
require_once ALGOLIA_PATH . 'includes/class-algolia-plugin.php';
require_once ALGOLIA_PATH . 'includes/class-algolia-search.php';
require_once ALGOLIA_PATH . 'includes/class-algolia-settings.php';
require_once ALGOLIA_PATH . 'includes/class-algolia-task.php';
require_once ALGOLIA_PATH . 'includes/class-algolia-task-dispatcher.php';
require_once ALGOLIA_PATH . 'includes/class-algolia-task-queue.php';
require_once ALGOLIA_PATH . 'includes/class-algolia-task-queue-loopback-async.php';
require_once ALGOLIA_PATH . 'includes/class-algolia-template-loader.php';
require_once ALGOLIA_PATH . 'includes/class-algolia-utils.php';

require_once ALGOLIA_PATH . 'includes/indices/class-algolia-index.php';
require_once ALGOLIA_PATH . 'includes/indices/class-algolia-index-replica.php';
require_once ALGOLIA_PATH . 'includes/indices/class-algolia-searchable-posts-index.php';
require_once ALGOLIA_PATH . 'includes/indices/class-algolia-posts-index.php';
require_once ALGOLIA_PATH . 'includes/indices/class-algolia-terms-index.php';
require_once ALGOLIA_PATH . 'includes/indices/class-algolia-users-index.php';

require_once ALGOLIA_PATH . 'includes/watchers/class-algolia-changes-watcher.php';
require_once ALGOLIA_PATH . 'includes/watchers/class-algolia-post-changes-watcher.php';
require_once ALGOLIA_PATH . 'includes/watchers/class-algolia-term-changes-watcher.php';
require_once ALGOLIA_PATH . 'includes/watchers/class-algolia-user-changes-watcher.php';

if ( is_admin() ) {
	require_once ALGOLIA_PATH . 'includes/admin/class-algolia-admin.php';
	require_once ALGOLIA_PATH . 'includes/admin/class-algolia-cache-helper.php';
	require_once ALGOLIA_PATH . 'includes/admin/class-algolia-admin-page-autocomplete.php';
	require_once ALGOLIA_PATH . 'includes/admin/class-algolia-admin-page-indexing.php';
	require_once ALGOLIA_PATH . 'includes/admin/class-algolia-admin-page-logs.php';
	require_once ALGOLIA_PATH . 'includes/admin/class-algolia-admin-page-native-search.php';
	require_once ALGOLIA_PATH . 'includes/admin/class-algolia-admin-page-settings.php';
}
