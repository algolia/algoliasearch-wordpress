#!/usr/bin/php
<?php
$config = '{
    "last_update": 1439564841,
    "autocompleteTypes": [
        {
            "name": "page",
            "count": 1,
            "nb_results_by_section": 3,
            "label": ""
        },
        {
            "name": "post",
            "count": 1,
            "nb_results_by_section": 3,
            "label": ""
        }
    ],
    "additionalAttributes": [
        {
            "name": "category",
            "group": "Taxonomy",
            "nb_results_by_section": 3,
            "label": ""
        }
    ],
    "instantTypes": [
        {
            "name": "post",
            "count": 1,
            "label": ""
        },
        {
            "name": "page",
            "count": 1,
            "label": ""
        }
    ],
    "attributesToIndex": [
        {
            "name": "post_title",
            "group": "Record attribute",
            "ordered": "ordered",
            "searchable": true,
            "retrievable": true
        },
        {
            "name": "post_content",
            "group": "Record attribute",
            "ordered": "unordered",
            "searchable": true,
            "retrievable": true
        },
        {
            "name": "category",
            "group": "Taxonomy",
            "ordered": "ordered",
            "searchable": true,
            "retrievable": true
        },
        {
            "name": "display_name",
            "group": "Record attribute",
            "ordered": "ordered",
            "searchable": true,
            "retrievable": true
        },
        {
            "name": "post_type",
            "group": "Record attribute",
            "ordered": "ordered",
            "searchable": true,
            "retrievable": true
        },
        {
            "name": "post_date",
            "group": "Record attribute",
            "ordered": "ordered",
            "searchable": true,
            "retrievable": true
        },
        {
            "name": "permalink",
            "group": "Record attribute",
            "ordered": "ordered",
            "searchable": true,
            "retrievable": true
        },
        {
            "name": "featureImage",
            "group": "Record attribute",
            "ordered": "ordered",
            "searchable": true,
            "retrievable": true
        }
    ],
    "customRankings": [],
    "facets": [
        {
            "name": "display_name",
            "group": "Record attribute",
            "type": "conjunctive",
            "label": ""
        },
        {
            "name": "post_type",
            "group": "Record attribute",
            "type": "conjunctive",
            "label": ""
        },
        {
            "name": "category",
            "group": "Taxonomy",
            "type": "disjunctive",
            "label": ""
        }
    ],
    "app_id": "%APP_ID%",
    "search_key": "%SEARCH_KEY%",
    "admin_key": "%ADMIN_KEY%",
    "index_name": "%PREFIX%",
    "enable_truncating": "true",
    "truncate_size": "9000",
    "search_input_selector": "[name=\\"s\\"]",
    "template_dir": "default",
    "number_by_page": "10",
    "instant_jquery_selector": "#content",
    "sorts": [
        {
            "name": "date",
            "group": "Record attribute",
            "sort": "asc",
            "label": "Date asc"
        },
        {
            "name": "date",
            "group": "Record attribute",
            "sort": "desc",
            "label": "Date desc"
        }
    ],
    "validCredential": true,
    "need_to_reindex": true,
    "excluded_types": [
        "revision",
        "nav_menu_item",
        "acf",
        "shop_order",
        "shop_order_refund",
        "shop_coupon",
        "shop_webhook",
        "wooframework"
    ]
}';

$config = str_replace('%APP_ID%', $argv[1], $config);
$config = str_replace('%SEARCH_KEY%', $argv[2], $config);
$config = str_replace('%ADMIN_KEY%', $argv[3], $config);
$config = str_replace('%PREFIX%', $argv[4], $config);

$config = json_decode($config, true);

$config['instant_jquery_selector'] = str_replace('"', '\'', $config['instant_jquery_selector']);
$config['search_input_selector'] = str_replace('"', '\'', $config['search_input_selector']);



echo mysqli_real_escape_string(mysqli_connect("localhost", "root", "P4ssw0rd", "wordpress"), serialize($config));