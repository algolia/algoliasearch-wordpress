jQuery(function() {
    if(jQuery('#algolia-search-box').length > 0) {
        /* global instantsearch */
        var search = instantsearch({
            appId: algolia.application_id,
            apiKey: algolia.search_api_key,
            indexName: algolia.indices.searchable_posts.name,
            urlSync: {
                mapping: {'q': 's'},
                trackedParameters: ['query']
            }
        });

        search.addWidget(
            instantsearch.widgets.searchBox({
                container: '#algolia-search-box',
                placeholder: 'Search for...',
                wrapInput: false,
                poweredBy: true
            })
        );

        search.addWidget(
            instantsearch.widgets.stats({
                container: '#algolia-stats'
            })
        );

        var hitTemplate = jQuery("#tmpl-hit").html();

        var noResultsTemplate = 'No result was found for "<strong>{{query}}</strong>".';

        search.addWidget(
            instantsearch.widgets.hits({
                container: '#algolia-hits',
                hitsPerPage: 10,
                templates: {
                    empty: noResultsTemplate,
                    item: hitTemplate
                }
            })
        );

        search.addWidget(
            instantsearch.widgets.pagination({
                container: '#algolia-pagination'
            })
        );

        search.addWidget(
            instantsearch.widgets.menu({
                container: '#facet-post-types',
                attributeName: 'post_type_label',
                sortBy: ['isRefined:desc', 'count:desc', 'name:asc'],
                limit: 10,
                templates: {
                    header: '<h3 class="widgettitle">Type</h3>'
                },
            })
        );

        search.addWidget(
            instantsearch.widgets.hierarchicalMenu({
                container: '#facet-categories',
                separator: ' > ',
                sortBy: ['count'],
                attributes: ['category_tree.lvl0', 'category_tree.lvl1', 'category_tree.lvl2'],
                templates: {
                    header: '<h3 class="widgettitle">Categories</h3>'
                }
            })
        );

        search.addWidget(
            instantsearch.widgets.refinementList({
                container: '#facet-tags',
                attributeName: 'taxonomy_post_tag',
                operator: 'and',
                limit: 15,
                sortBy: ['isRefined:desc', 'count:desc', 'name:asc'],
                templates: {
                    header: '<h3 class="widgettitle">Tags</h3>'
                }
            })
        );

        search.addWidget(
            instantsearch.widgets.menu({
                container: '#facet-users',
                attributeName: 'post_author.display_name',
                sortBy: ['isRefined', 'count:desc', 'name:asc'],
                limit: 10,
                templates: {
                    header: '<h3 class="widgettitle">Auteurs</h3>'
                }
            })
        );

        search.templatesConfig.helpers.relevantContent = function() {
            var attributes = ['content', 'title6', 'title5', 'title4', 'title3', 'title2', 'title1'];
            var attribute_name;
            for ( var index in attributes ) {
                attribute_name = attributes[ index ];
                if ( this._highlightResult[ attribute_name ].matchedWords.length > 0 ) {
                    return this._snippetResult[ attribute_name ].value;
                }
            }

            return this._snippetResult[ attributes[ 0 ] ].value;
        };

        search.start();

        jQuery('#algolia-search-box input').attr('type', 'search').select();
    }
});
