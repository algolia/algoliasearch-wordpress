<?php

/*
  Plugin Name: Algolia
  Plugin URI:
  Description: replace wordpress search with Algolia Api
  Version: 0.1
  Author: Algolia
  Author URI: http://www.algolia.com
  Copyright: Algolia
 */

defined( 'ABSPATH' ) or die( 'Not Allowed' );

require_once(plugin_dir_path(__FILE__).'/lib/algoliasearch.php');

require_once(plugin_dir_path(__FILE__).'/core/Indexer.php');
require_once(plugin_dir_path(__FILE__).'/core/AlgoliaHelper.php');
require_once(plugin_dir_path(__FILE__).'/core/Registry.php');
require_once(plugin_dir_path(__FILE__).'/core/WordpressFetcher.php');

require_once(plugin_dir_path(__FILE__).'/AlgoliaPlugin.php');
require_once(plugin_dir_path(__FILE__).'/AlgoliaPluginAuto.php');


new AlgoliaPlugin();
new AlgoliaPluginAuto();