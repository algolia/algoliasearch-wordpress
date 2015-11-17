algoliaBundle.$(document).ready(function ($) {

    /*****************
     **
     ** INITIALIZATION
     **
     *****************/

    var algolia_client = algoliaBundle.algoliasearch(algoliaConfig.app_id, algoliaConfig.search_key);

    /*****************
     **
     ** HELPERS
     **
     *****************/

    function getBrandingHits() {
        return function findMatches(q, cb) {
            return cb(["algolia-branding"]);
        }
    }

    /*****************
     **
     ** AUTOCOMPLETE
     **
     *****************/

    if (algoliaConfig.autocompleteTypes.length > 0 || algoliaConfig.additionalAttributes.length > 0)
    {
        var $autocompleteTemplate = algoliaBundle.Hogan.compile($('#autocomplete-template').text());

        var hogan_objs = [];

        var sections = ['autocompleteTypes', 'additionalAttributes'];

        for (var j = 0; j < sections.length; j++)
        {
            for (var i = 0; i < algoliaConfig[sections[j]].length; i++)
            {
                var index = algolia_client.initIndex(algoliaConfig.index_prefix + algoliaConfig[sections[j]][i].name);
                var label = algoliaConfig[sections[j]][i].label ? algoliaConfig[sections[j]][i].label : algoliaConfig[sections[j]][i].name;

                hogan_objs.push({
                    source: index.ttAdapter({hitsPerPage: algoliaConfig[sections[j]][i].nb_results_by_section}),
                    displayKey: 'title',
                    templates: {
                        header: '<div class="category">' + label + '</div>',
                        suggestion: function (hit) {
                            return $autocompleteTemplate.render(hit);
                        }
                    }
                });
            }
        }

        hogan_objs.push({
            source: getBrandingHits(),
            displayKey: 'title',
            templates: {
                suggestion: function (hit) {
                    return '<div class="footer">powered by <img width="45" src="' + algoliaConfig.plugin_url + '/front/algolia-logo.png"></div>';
                }
            }
        });

        $(algoliaConfig.search_input_selector).each(function (i) {

            $(this).autocomplete({hint: false}, hogan_objs);

            $(this).on('autocomplete:selected', function (e, item) {
                autocomplete = false;
                instant = false;
                window.location.href = item.permalink;
            });
        });
    }


    /*****************
     **
     ** INSTANT SEARCH
     **
     *****************/

    if (algoliaConfig.instantTypes.length > 0 && (algoliaConfig.is_search_page === '1' || algoliaConfig.autocompleteTypes.length <= 0))
    {
        var instant_selector = !algoliaConfig.autocompleteTypes.length > 0 ? "#search" : "#instant-search-bar";
        var wrapperTemplate = algoliaBundle.Hogan.compile($('#instant_wrapper_template').html());

        $(algoliaConfig.instant_jquery_selector).html(wrapperTemplate.render({ second_bar: (algoliaConfig.autocompleteTypes.length > 0) })).show();

        /** Initialise instant search **/
        var search = algoliaBundle.instantsearch({
            appId: algoliaConfig.app_id,
            apiKey: algoliaConfig.search_key,
            indexName: algoliaConfig.index_prefix + 'all'
        });
        /** Search bar **/
        search.addWidget(
            algoliaBundle.instantsearch.widgets.searchBox({
                container: instant_selector,
                placeholder: 'Search for products'
            })
        );
        /** Stats **/
        var instantStatsTemplate = $('#instant-stats-template').html();
        search.addWidget(
            algoliaBundle.instantsearch.widgets.stats({
                container: '#algolia-stats',
                template: instantStatsTemplate
            })
        );
        /** Sorts **/
        var sorting_indices = algoliaConfig.sorts;
        $.map(sorting_indices, function(index) {
            index.name = algoliaConfig.index_prefix + index.name + '_' + index.sort;
        });
        sorting_indices.unshift({name: algoliaConfig.index_prefix + 'all', label: 'Relevance'});

        search.addWidget(
            algoliaBundle.instantsearch.widgets.indexSelector({
                container: '#algolia-sorts',
                indices: sorting_indices,
                cssClass: 'form-control'
            })
        );
        /** Hits **/
        var instantHitTemplate = $('#instant-hit-template').html();
        search.addWidget(
            algoliaBundle.instantsearch.widgets.hits({
                container: '#instant-search-results-container',
                templates: {
                    empty: '<div class="no-results">No results found matching "<strong>{{query}}</strong>". <span class="button clear-button">Clear query and filters</span> </div>',
                    hit: instantHitTemplate
                },
                transformData: {
                    hit: transformHit.bind(null, $)
                },
                hitsPerPage: algoliaConfig.hitsPerPage
            })
        );

        /** Facets **/
        var facets = algoliaConfig.facets;
        var wrapper = document.getElementById('instant-search-facets-container');
        var facetTemplate = $('#facet-template').html();

        $.each(facets, function (i, facet) {
            facet.template = facetTemplate;
            facet.wrapper = wrapper;
            search.addWidget(getFacetWidget(facet, {
                item: facet.template,
                header: '<div class="facet"><div class="name">' + (facet.label ? facet.label : facet.name) + '</div></div>',
                footer: '</div/></div>'
            }));
        });

        /** Pagination **/
        search.addWidget(
            algoliaBundle.instantsearch.widgets.pagination({
                container: '#instant-search-pagination-container',
                cssClass: 'algolia-pagination',
                showFirstLast: false,
                labels: {
                    prev: '‹', // &lsaquo;
                    next: '›', // &rsaquo;
                    first: '«', // &laquo;
                    last: '»' // &raquo;
                }
            })
        );
        /** Url sync **/
        search.addWidget(
            algoliaBundle.instantsearch.widgets.urlSync({
                useHash: true,
                threshold: 5000,
                trackedParameters: ['query', 'page', 'attribute:*', 'index']
            })
        );
        search.start();
    }

    $(algoliaConfig.search_input_selector).attr('autocomplete', 'off').attr('autocorrect', 'off').attr('spellcheck', 'false').attr('autocapitalize', 'off');
});
