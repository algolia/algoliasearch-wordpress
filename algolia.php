<?php

/*
  Plugin Name: Algolia Realtime Search
  Plugin URI:
  Description: The Algolia Realtime Search plugin turns the standards WordPress search (including WooCommerce) into a friendly Find-as-you-type autocomplete or an InstantSearch faceted result page. Both allow your users to find what they are looking for with just a few keystrokes, in seconds. Fully customizable via the plugin and the Algolia dashboard.
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

$attributesToSnippet    = array("content", "content_stripped");

$facetsLabels = array(
    'post'      => 'Article',
    'page'      => 'Page'
);

/**
 * Defaults Variables
 */

$attributesToIndex      = array("title", "content", "content_stripped", "author", "type");

/**
 * Handling Extension
 */

$external_attrs = array();

$external_attrs['product_attrs'] = array('virtual', 'sku', 'taxable', 'tax_status', 'tax_class', 'managing_stock', 'stock_quantity', 'in_stock', 'backorders_allowed', 'sold_individually', 'purchaseable', 'visible', 'catalog_visibility', 'on_sale', 'weight', 'length', 'shipping_required', 'shipping_taxable', 'shipping_class', 'shipping_class_id', 'reviews_allowed', 'average_rating', 'rating_count', 'related_ids', 'upsell_ids', 'download_type', 'purchase_note');
$external_attrs['product'] = function ($data) {
        if (class_exists('WC_Product_Factory'))
        {
            $factory = new WC_Product_Factory();
            $product = $factory->get_product($data->ID);



            $product_attrs = array(
                'virtual'            => $product->is_virtual(),
                'sku'                => $product->get_sku(),
                'taxable'            => $product->is_taxable(),
                'tax_status'         => $product->get_tax_status(),
                'tax_class'          => $product->get_tax_class(),
                'managing_stock'     => $product->managing_stock(),
                'stock_quantity'     => (int) $product->get_stock_quantity(),
                'in_stock'           => $product->is_in_stock(),
                'backorders_allowed' => $product->backorders_allowed(),
                'sold_individually'  => $product->is_sold_individually(),
                'purchaseable'       => $product->is_purchasable(),
                'visible'            => $product->is_visible(),
                'catalog_visibility' => $product->visibility,
                'on_sale'            => $product->is_on_sale(),
                'weight'             => $product->get_weight() ? wc_format_decimal( $product->get_weight(), 2 ) : null,
                'length'             => $product->length,
                'shipping_required'  => $product->needs_shipping(),
                'shipping_taxable'   => $product->is_shipping_taxable(),
                'shipping_class'     => $product->get_shipping_class(),
                'shipping_class_id'  => (0 !== $product->get_shipping_class_id()) ? $product->get_shipping_class_id() : null,
                'reviews_allowed'    => ('open' === $product->get_post_data()->comment_status),
                'average_rating'     => wc_format_decimal($product->get_average_rating(), 2),
                'rating_count'       => (int) $product->get_rating_count(),
                'related_ids'        => array_map('absint', array_values($product->get_related())),
                'upsell_ids'         => array_map('absint', $product->get_upsells()),
                'download_type'      => $product->download_type,
                'purchase_note'      => wpautop(do_shortcode(wp_kses_post($product->purchase_note))),
            );

            foreach ($product->get_attributes() as $attribute)
                $product_attrs[$attribute['name']] = $attribute['value'];

            return $product_attrs;
        }

        return array();
    };
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
