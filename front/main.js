console.log(algoliaSettings);

/**
 * Common stuff
 */
var algolia_client = new AlgoliaSearch(algoliaSettings.app_id, algoliaSettings.search_key);
var indexes = [];

for (var i = 0; i < algoliaSettings.indexes.length; i++)
    indexes.push(algolia_client.initIndex(algoliaSettings.indexes[i].index_name));

var myCompare = function (a, b) {
    if (a.order1 < b.order1)
        return -1;

    if (a.order1 == b.order1 && a.order2 <= b.order2)
        return -1;

    return 1;
}

/**
 * Autocomplete
 */

if (algoliaSettings.type_of_search == "autocomplete")
{
    jQuery(document).ready(function ($)
    {
        var $autocompleteTemplate = Hogan.compile($('#autocomplete-template').text());

        hogan_objs = [];

        console.log(algoliaSettings);

        algoliaSettings.indexes.sort(myCompare);

        for (var i = 0; i < algoliaSettings.indexes.length; i++)
        {
            hogan_objs.push({
                source: indexes[i].ttAdapter({hitsPerPage: algoliaSettings.number_by_type}),
                displayKey: 'title',
                templates: {
                    header: '<div class="category">' + algoliaSettings.indexes[i].name + '</div>',
                    suggestion: function (hit) {
                        return $autocompleteTemplate.render(hit);
                    }
                }
            });
        }

        $(algoliaSettings.search_input_selector).each(function (i) {
            $(this).typeahead({hint: false}, hogan_objs);

            $(this).on('typeahead:selected', function (e, item) {
                window.location.href = item.permalink;
            });
        });
    });
}

/**
 * Instant Search
 */

if (algoliaSettings.type_of_search == "instant")
{
    jQuery(document).ready(function ($) {

        if ($(algoliaSettings.instant_jquery_selector).length == 1)
        {
            /**
             * Variables Initialization
             */

            var old_content         = $(algoliaSettings.instant_jquery_selector).html();

            var query               = "";

            var template            = Hogan.compile($('#instant-content-template').text());
            var facetsTemplate      = Hogan.compile($('#instant-facets-template').text());
            var paginationTemplate  = Hogan.compile($('#instant-pagination-template').text());

            var conjunctive_facets  = [];
            var disjunctive_facets  = [];
            var slider_facets       = [];

            for (var i = 0; i < algoliaSettings.facets.length; i++)
            {
                if (algoliaSettings.facets[i].type == "conjunctive")
                    conjunctive_facets.push(algoliaSettings.facets[i].tax);

                if (algoliaSettings.facets[i].type == "disjunctive")
                    disjunctive_facets.push(algoliaSettings.facets[i].tax);

                if (algoliaSettings.facets[i].type == "slider")
                {
                    disjunctive_facets.push(algoliaSettings.facets[i].tax);
                    slider_facets.push(algoliaSettings.facets[i].tax);
                }
            }

            algoliaSettings.facets = algoliaSettings.facets.sort(myCompare);

            var helper = new AlgoliaSearchHelper(algolia_client, algoliaSettings.index_name, {
                facets: conjunctive_facets,
                disjunctiveFacets: disjunctive_facets,
                hitsPerPage: algoliaSettings.number_by_page
            })

            /**
             * Functions
             */
            function updateUrl()
            {
                var refinements = [];

                for (var refine in helper.refinements)
                {
                    if (helper.refinements[refine])
                    {
                        var i = refine.indexOf(':');
                        var r = {};

                        r[refine.slice(0, i)] = refine.slice(i + 1);

                        refinements.push(r);
                    }
                }

                for (var refine in helper.disjunctiveRefinements)
                {
                    for (var value in helper.disjunctiveRefinements[refine])
                    {
                        if (helper.disjunctiveRefinements[refine][value])
                        {
                            var r = {};

                            r[refine] = value;

                            refinements.push(r);
                        }
                    }
                }

                location.replace('#q=' + encodeURIComponent(query) + '&page=' + helper.page + '&refinements=' + encodeURIComponent(JSON.stringify(refinements)) + '&numerics_refinements=' + encodeURIComponent(JSON.stringify(helper.numericsRefinements)));
            }

            function getRefinementsFromUrl()
            {
                if (location.hash && location.hash.indexOf('#q=') === 0)
                {
                    var params                          = location.hash.substring(3);
                    var pageParamOffset                 = params.indexOf('&page=');
                    var refinementsParamOffset          = params.indexOf('&refinements=');
                    var numericsRefinementsParamOffset  = params.indexOf('&numerics_refinements=');

                    var q                               = decodeURIComponent(params.substring(0, pageParamOffset));
                    var page                            = parseInt(params.substring(pageParamOffset + 6, refinementsParamOffset));
                    var refinements                     = JSON.parse(decodeURIComponent(params.substring(refinementsParamOffset + 13, numericsRefinementsParamOffset)));
                    var numericsRefinements             = JSON.parse(decodeURIComponent(params.substring(numericsRefinementsParamOffset + 22)));

                    query = q;

                    for (var i = 0; i < refinements.length; ++i) {
                        for (var refine in refinements[i]) {
                            helper.toggleRefine(refine, refinements[i][refine]);
                        }
                    }

                    helper.numericsRefinements = numericsRefinements;

                    helper.setPage(page);

                    $(algoliaSettings.search_input_selector).val(query);

                    performQueries();

                }
            }

            function performQueries()
            {
                helper.search(query, searchCallback);

                updateUrl();
            }

            function updateSlideInfos(ui)
            {
                var infos = $(ui.handle).closest(".algolia-slider").nextAll(".algolia-slider-info");

                infos.find(".min").html(ui.values[0]);
                infos.find(".max").html(ui.values[1]);
            }

            function searchCallback(success, content) {


                if (success)
                {
                    var html_content = "";

                    html_content += "<div id='algolia_instant_selector'>";

                    console.log(content);

                    if (content.hits.length > 0)
                    {
                        var facets = [];

                        for (var i = 0; i < algoliaSettings.facets.length; i++)
                        {
                            var sub_facets = [];

                            if (algoliaSettings.facets[i].type == "conjunctive")
                            {
                                for (var key in content.facets[algoliaSettings.facets[i].tax])
                                {
                                    var checked = helper.isRefined(algoliaSettings.facets[i].tax, key);
                                    sub_facets.push({
                                        conjunctive: 1,
                                        disjunctive: 0,
                                        slider: 0,
                                        checked: checked,
                                        name: key,
                                        count: content.facets[algoliaSettings.facets[i].tax][key]
                                    });
                                }
                            }

                            if (algoliaSettings.facets[i].type == "slider")
                            {
                                if (content.facets_stats[algoliaSettings.facets[i].tax] != undefined)
                                {
                                    var min = content.facets_stats[algoliaSettings.facets[i].tax].min;
                                    var max = content.facets_stats[algoliaSettings.facets[i].tax].max;

                                    var current_min = helper.getNumericsRefine(algoliaSettings.facets[i].tax, ">=");
                                    var current_max = helper.getNumericsRefine(algoliaSettings.facets[i].tax, "<=");

                                    if (current_min == undefined)
                                        current_min = min;

                                    if (current_max == undefined)
                                        current_max = max;

                                    sub_facets.push({ current_min: current_min, current_max: current_max, count: min == max ? 0 : 1,slider: 1, conjunctive: 0, disjunctive: 0, name: key, min: min, max: max });
                                }
                            }

                            if (algoliaSettings.facets[i].type == "disjunctive")
                            {
                                for (var key in content.disjunctiveFacets[algoliaSettings.facets[i].tax])
                                {
                                    var checked = helper.isRefined(algoliaSettings.facets[i].tax, key);
                                    sub_facets.push({ slider: 0, conjunctive: 0, disjunctive: 1, checked: checked, name: key, count: content.disjunctiveFacets[algoliaSettings.facets[i].tax][key] });
                                }
                            }

                            facets.push({count: sub_facets.length, tax: algoliaSettings.facets[i].tax, facet_categorie_name: algoliaSettings.facets[i].name, sub_facets: sub_facets });
                        }

                        var pages = [];
                        if (content.page > 5)
                        {
                            pages.push({ current: false, number: 1 });
                            pages.push({ current: false, number: '...', disabled: true });
                        }

                        for (var p = content.page - 5; p < content.page + 5; ++p)
                        {
                            if (p < 0 || p >= content.nbPages)
                                continue;

                            pages.push({ current: content.page == p, number: (p + 1) });
                        }
                        if (content.page + 5 < content.nbPages)
                        {
                            pages.push({ current: false, number: '...', disabled: true });
                            pages.push({ current: false, number: content.nbPages });
                        }

                        html_content += facetsTemplate.render({ facets: facets });
                    }

                    html_content += template.render({ hits: content.hits, nbHits: content.nbHits, query: query, processingTimeMS: content.processingTimeMS  });

                    if (content.hits.length > 0)
                        html_content += paginationTemplate.render({ pages: pages, prev_page: (content.page > 0 ? content.page : false), next_page: (content.page + 1 < content.nbPages ? content.page + 2 : false) });

                    html_content += "</div>";

                    $(algoliaSettings.instant_jquery_selector).html(html_content);

                    $(".algolia-slider").each(function (i) {
                        var min = $(this).attr("data-min");
                        var max = $(this).attr("data-max");

                        var new_min = helper.getNumericsRefine($(this).attr("data-tax"), ">=");
                        var new_max = helper.getNumericsRefine($(this).attr("data-tax"), "<=");

                        if (new_min != undefined)
                            min = new_min;

                        if (new_max != undefined)
                            max = new_max;

                        $(this).slider({
                            min: parseInt($(this).attr("data-min")),
                            max: parseInt($(this).attr("data-max")),
                            range: true,
                            values: [min, max]
                        });
                    });
                }
            }

            window.gotoPage = function(page) {
                helper.gotoPage(+page - 1);
            };

            /**
             * Initialization
             */

            getRefinementsFromUrl();

            /**
             * Bindings
             */

            $("body").on("click", ".sub_facet", function () {
               $(this).find("input[type='checkbox']").each(function (i) {
                   $(this).prop("checked", ! $(this).prop("checked"));
                   helper.toggleRefine($(this).attr("data-tax"), $(this).attr("data-name"));
               });

                performQueries();
            });



            $("body").on("slide", "", function(event, ui) {
                updateSlideInfos(ui);
            });

            $("body").on("slidechange", "", function(event, ui) {

                var slide_dom = $(ui.handle).closest(".algolia-slider");
                var min = slide_dom.slider("values")[0];
                var max = slide_dom.slider("values")[1];

                if (parseInt(slide_dom.slider("values")[0]) >= parseInt(slide_dom.attr("data-min")))
                    helper.addNumericsRefine(slide_dom.attr("data-tax"), ">=", min);
                if (parseInt(slide_dom.slider("values")[1]) <= parseInt(slide_dom.attr("data-max")))
                    helper.addNumericsRefine(slide_dom.attr("data-tax"), "<=", max);

                if (parseInt(min) == parseInt(slide_dom.attr("data-min")))
                    helper.removeNumericRefine(slide_dom.attr("data-tax"), ">=");

                if (parseInt(max) == parseInt(slide_dom.attr("data-max")))
                    helper.removeNumericRefine(slide_dom.attr("data-tax"), "<=");

                updateSlideInfos(ui);
                performQueries();
            });

            $(algoliaSettings.search_input_selector).keyup(function (e) {
                var $this = $(this);

                $(algoliaSettings.search_input_selector).each(function (i) {
                    if ($(this)[0] != $this[0])
                        $(this).val(query);
                });

                if ($(this).val().length == 0)
                {
                    location.replace('#');

                    $(algoliaSettings.instant_jquery_selector).html(old_content);

                    return;
                }

                helper.clearRefinements();
                helper.clearNumericRefinements();

                query = $(this).val();

                performQueries();
            });
        }
        else
        {
            console.log("Bad selector for instant search");
        }
    });
}