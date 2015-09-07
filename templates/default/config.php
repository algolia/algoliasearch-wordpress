<?php

return array(
    'name'                      => 'Default',
    'screenshot'                => 'screenshot.png',
    'facet_types'               => array('slider' => 'Slider', 'menu' => 'Menu'),
    'attributes'                => array(
        'autocompleteTypes'             => [
            ["name" => "page", "count" => 1, "nb_results_by_section" => 3, "label" => ""],
            ["name" => "post", "count" => 1, "nb_results_by_section" => 3, "label" => ""]
        ],
        'additionalAttributes'          => [["name" => "category", "group" => "Taxonomy", "nb_results_by_section" => 3, "label" => ""]],
        'instantTypes'                  => [["name" => "post", "count" => 1, "label" => ""], ["name" => "page", "count" => 1, "label" => ""]],
        'attributesToIndex'             => [
            ["name" => "post_title", "group" => "Record attribute", "ordered" => "ordered", "searchable" => true],
            ["name" => "post_content", "group" => "Record attribute", "ordered" => "unordered", "searchable" => true],
            ["name" => "category", "group" => "Taxonomy", "ordered" => "ordered", "searchable" => true],
            ["name" => "display_name", "group" => "Record attribute", "ordered" => "ordered", "searchable" => true],
            ["name" => "post_type", "group" => "Record attribute", "ordered" => "ordered", "searchable" => true],
            ["name" => "post_date", "group" => "Record attribute", "ordered" => "ordered", "searchable" => true],
            ["name" => "permalink", "group" => "Record attribute", "ordered" => "ordered", "searchable" => true],
            ["name" => "featureImage", "group" => "Record attribute", "ordered" => "ordered", "searchable" => true],
        ],
        'customRankings'                => [],
        'facets'                        => [
            ["name" => "display_name", "group" => "Record attribute", "type" => "conjunctive", "label" => "Author"],
            ["name" => "post_type", "group" => "Record attribute", "type" => "menu", "label" => "Type"],
            ["name" => "category", "group" => "Taxonomy", "type" => "disjunctive", "label" => ""]
        ],
        'sorts'                         => [
            ["name" => "post_date", "group" => "Record attribute", "sort" => "asc", "label" => "Date asc"],
            ["name" => "post_date", "group" => "Record attribute", "sort" => "desc", "label" => "Date desc"]
        ]
    )
);