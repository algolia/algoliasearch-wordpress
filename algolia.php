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
require_once(plugin_dir_path(__FILE__).'/lib/dom/simple_html_dom.php');

require_once(plugin_dir_path(__FILE__).'/core/Indexer.php');
require_once(plugin_dir_path(__FILE__).'/core/AlgoliaHelper.php');
require_once(plugin_dir_path(__FILE__).'/core/Registry.php');
require_once(plugin_dir_path(__FILE__).'/core/WordpressFetcher.php');
require_once(plugin_dir_path(__FILE__).'/core/TemplateHelper.php');
require_once(plugin_dir_path(__FILE__).'/core/QueryReplacer.php');

require_once(plugin_dir_path(__FILE__).'/AlgoliaPlugin.php');
require_once(plugin_dir_path(__FILE__).'/AlgoliaPluginAuto.php');


\AlgoliaSearch\Version::$custom_value = " Wordpress (0.0.9)";


new AlgoliaPlugin();
new AlgoliaPluginAuto();

/**
 * Variables Definition
 */

$batch_count = 100;

/**
 * Defaults Variables
 */

$attributesToIndex      = array("title", "excerpt", "content", "author", "type");

/** Woo Commerce Handling */
add_filter(/**
 * @param $data
 * @return mixed
 */
    'prepare_algolia_record', function ($data) {

    if (class_exists('WC_Product_Factory') && $data->type == 'product')
    {
        $algolia_registry = \Algolia\Core\Registry::getInstance();

        $factory = new WC_Product_Factory();
        $product = $factory->get_product($data->objectID);

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

        $attributes = $product->get_attributes();

        foreach ($algolia_registry->attributesToIndex as $value)
        {
            if (isset($data->{$value['name']}))
                continue;

            if (isset($product_attrs[$value['name']]))
                $data->{$value['name']} = \Algolia\Core\WordpressFetcher::try_cast($product_attrs[$value['name']]);

            $name = $value['name'];
            if (($pos = strpos($value['name'], 'attribute_')) !== false)
                $name = substr($name, 10);

            if (in_array($name, array_keys($attributes)))
                $data->{$value['name']} = \Algolia\Core\WordpressFetcher::try_cast($attributes[$name]['value']);
            else
            {
                $meta = get_post_meta($data->objectID, $value['name'], true);

                if ($meta !== null)
                    $data->{$value['name']} = \Algolia\Core\WordpressFetcher::try_cast($meta);
            }
        }
    }

    return $data;
}, 0);

/**
 * Functions definitions
 */

function get_meta_key_list_count($type)
{
    global $wpdb;
    $query = "
        SELECT count(*)
        FROM $wpdb->posts
        LEFT JOIN $wpdb->postmeta
        ON $wpdb->posts.ID = $wpdb->postmeta.post_id
        WHERE $wpdb->posts.post_type = '%s'
        AND $wpdb->postmeta.meta_key != ''
    ";

    $count = (int) $wpdb->get_col($wpdb->prepare($query, $type));

    return $count[0];
}

function get_meta_key_list($type, $offset, $batch_count)
{
    global $wpdb;
    $query = "
        SELECT DISTINCT($wpdb->postmeta.meta_key)
        FROM $wpdb->posts
        LEFT JOIN $wpdb->postmeta
        ON $wpdb->posts.ID = $wpdb->postmeta.post_id
        WHERE $wpdb->posts.post_type = '%s'
        AND $wpdb->postmeta.meta_key != ''
        LIMIT $offset, $batch_count
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

function truncate($text, $length)
{
    $text           = strip_shortcodes( $text );
    $text           = apply_filters('the_content', $text);
    $text           = str_replace(']]>', ']]&gt;', $text);
    $excerpt_length = apply_filters('excerpt_length', $length);
    $words          = preg_split("/[\n\r\t ]+/", $text, $excerpt_length + 1, PREG_SPLIT_NO_EMPTY);

    if (count($words) > $excerpt_length)
    {
        array_pop($words);
        $text = implode(' ', $words);
    }
    else
        $text = implode(' ', $words);

    return apply_filters('wp_trim_excerpt', $text);
}
