<?php

return array(
    'name'                      => 'Default',
    'screenshot'                => 'screenshot.png',
    'attributes'                => array(
        'autocompleteTypes'             => [
            ["name" => "page", "count" => 1, "nb_results_by_section" => 3, "label" => "Pages"],
            ["name" => "post", "count" => 1, "nb_results_by_section" => 3, "label" => "Posts"]
        ],
        'additionalAttributes'          => [["name" => "category", "group" => "Taxonomy", "nb_results_by_section" => 3, "label" => "Categories"]],
        'instantTypes'                  => [["name" => "post", "count" => 1], ["name" => "page", "count" => 1]],
        'attributesToIndex'             => [
            ["name" => "post_title", "group" => "Record attribute", "ordered" => "ordered", "searchable" => true, "retrievable" => true],
            ["name" => "post_content", "group" => "Record attribute", "ordered" => "unordered", "searchable" => true, "retrievable" => true],
            ["name" => "category", "group" => "Taxonomy", "ordered" => "ordered", "searchable" => true, "retrievable" => true],
            ["name" => "display_name", "group" => "Record attribute", "ordered" => "ordered", "searchable" => true, "retrievable" => true],
            ["name" => "post_type", "group" => "Record attribute", "ordered" => "ordered", "searchable" => true, "retrievable" => true],
            ["name" => "post_date", "group" => "Record attribute", "ordered" => "ordered", "searchable" => true, "retrievable" => true],
            ["name" => "permalink", "group" => "Record attribute", "ordered" => "ordered", "searchable" => true, "retrievable" => true],
            ["name" => "featureImage", "group" => "Record attribute", "ordered" => "ordered", "searchable" => true, "retrievable" => true],
        ],
        'customRankings'                => [],
        'facets'                        => [
            ["name" => "display_name", "group" => "Record attribute", "type" => "conjunctive", "label" => "Authors"],
            ["name" => "post_type", "group" => "Record attribute", "type" => "menu", "label" => "Types"],
            ["name" => "category", "group" => "Taxonomy", "type" => "disjunctive", "label" => "Categories"]
        ],
        'sorts'                         => [
            ["name" => "post_date", "group" => "Record attribute", "sort" => "asc", "label" => "Date asc"],
            ["name" => "post_date", "group" => "Record attribute", "sort" => "desc", "label" => "Date desc"]
        ]
    )
);