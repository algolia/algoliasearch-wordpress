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

require_once(plugin_dir_path(__FILE__).'/lib/algolia/algoliasearch.php');

require_once(plugin_dir_path(__FILE__).'/core/Indexer.php');
require_once(plugin_dir_path(__FILE__).'/core/AlgoliaHelper.php');
require_once(plugin_dir_path(__FILE__).'/core/Registry.php');
require_once(plugin_dir_path(__FILE__).'/core/WordpressFetcher.php');
require_once(plugin_dir_path(__FILE__).'/core/ThemeHelper.php');

require_once(plugin_dir_path(__FILE__).'/AlgoliaPlugin.php');
require_once(plugin_dir_path(__FILE__).'/AlgoliaPluginAuto.php');


new AlgoliaPlugin();
new AlgoliaPluginAuto();

/**
 * Variables Definition
 */

$batch_count = 50;

/**
 * Attribute to Index For Autocomplete
 */
$attributesToIndex      = array("title", "unordered(content)", "author");

/**
 * Attribute to Instant search
 */
$attributesToIndex2     = array("title", "unordered(content)", "author", "type");
$attributesToHighlight  = array("title", "content", "author", "type");
$attributesToSnippet    = array("content:100");


/**
 * Functions definitions
 */

function get_meta_key_list($type)
{
    global $wpdb;
    $query = "
        SELECT DISTINCT($wpdb->postmeta.meta_key)
        FROM $wpdb->posts
        LEFT JOIN $wpdb->postmeta
        ON $wpdb->posts.ID = $wpdb->postmeta.post_id
        WHERE $wpdb->posts.post_type = '%s'
        AND $wpdb->postmeta.meta_key != ''
        ORDER BY $wpdb->postmeta.meta_key
    ";
    $meta_keys = $wpdb->get_col($wpdb->prepare($query, $type));

    return $meta_keys;
}

function my_excerpt($text, $excerpt)
{
    if ($excerpt) return $excerpt;

    $text = strip_shortcodes( $text );

    $text = apply_filters('the_content', $text);
    $text = str_replace(']]>', ']]&gt;', $text);
    $text = strip_tags($text);
    $excerpt_length = apply_filters('excerpt_length', 55);
    $excerpt_more = apply_filters('excerpt_more', ' ' . '[...]');
    $words = preg_split("/[\n\r\t ]+/", $text, $excerpt_length + 1, PREG_SPLIT_NO_EMPTY);
    if ( count($words) > $excerpt_length ) {
        array_pop($words);
        $text = implode(' ', $words);
        $text = $text . $excerpt_more;
    } else {
        $text = implode(' ', $words);
    }

    return apply_filters('wp_trim_excerpt', $text);
}