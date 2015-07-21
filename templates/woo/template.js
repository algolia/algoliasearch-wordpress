algoliaBundle.$(document).ready(function ($) {

    /*****************
     **
     ** INITIALIZATION
     **
     *****************/

    var algolia_client = algoliaBundle.algoliasearch(algoliaSettings.app_id, algoliaSettings.search_key);
    var custom_facets_types = algoliaSettings.template.facet_types;


    /*****************
     **
     ** HELPERS
     **
     *****************/

    window.indicesCompare = function (a, b) {
        if (a.order1 < b.order1)
            return -1;

        if (a.order1 == b.order1 && a.order2 <= b.order2)
            return -1;

        return 1;
    };

    window.facetsCompare = function (a, b) {
        if (a.order < b.order)
            return -1;

        if (a.order == b.order)
            return -1;

        return 1;
    };

    /**
     * Autocomplete helpers
     */

    function getBrandingHits() {
        return function findMatches(q, cb) {
            return cb(["algolia-branding"]);
        }
    }

    function updateUrl(push_state)
    {
        var refinements = [];

        /** Get refinements for conjunctive facets **/
        for (var refine in helper.state.facetsRefinements)
        {
            if (helper.state.facetsRefinements[refine])
            {
                var r = {};

                r[refine] = helper.state.facetsRefinements[refine];

                refinements.push(r);
            }
        }

        /** Get refinements for disjunctive facets **/
        for (var refine in helper.state.disjunctiveFacetsRefinements)
        {
            for (var i = 0; i < helper.state.disjunctiveFacetsRefinements[refine].length; i++)
            {
                var r = {};

                r[refine] = helper.state.disjunctiveFacetsRefinements[refine][i];

                refinements.push(r);
            }
        }

        var url = '#q=' + encodeURIComponent(helper.state.query) + '&page=' + helper.getCurrentPage() + '&refinements=' + encodeURIComponent(JSON.stringify(refinements)) + '&numerics_refinements=' + encodeURIComponent(JSON.stringify(helper.state.numericRefinements)) + '&index_name=' + encodeURIComponent(JSON.stringify(helper.getIndex()));

        /** If push_state is false wait for one second to push the state in history **/
        if (push_state)
            history.pushState(url, null, url);
        else
        {
            clearTimeout(history_timeout);
            history_timeout = setTimeout(function () {
                history.pushState(url, null, url);
            }, 1000);
        }
    }

    function getRefinementsFromUrl()
    {
        if (location.hash && location.hash.indexOf('#q=') === 0)
        {
            var params                          = location.hash.substring(3);
            var pageParamOffset                 = params.indexOf('&page=');
            var refinementsParamOffset          = params.indexOf('&refinements=');
            var numericsRefinementsParamOffset  = params.indexOf('&numerics_refinements=');
            var indexNameOffset                 = params.indexOf('&index_name=');

            var q                               = decodeURIComponent(params.substring(0, pageParamOffset));
            var page                            = parseInt(params.substring(pageParamOffset + '&page='.length, refinementsParamOffset));
            var refinements                     = JSON.parse(decodeURIComponent(params.substring(refinementsParamOffset + '&refinements='.length, numericsRefinementsParamOffset)));
            var numericsRefinements             = JSON.parse(decodeURIComponent(params.substring(numericsRefinementsParamOffset + '&numerics_refinements='.length, indexNameOffset)));
            var indexName                       = JSON.parse(decodeURIComponent(params.substring(indexNameOffset + '&index_name='.length)));

            helper.setQuery(q);

            helper.clearRefinements();

            /** Set refinements from url data **/
            for (var i = 0; i < refinements.length; ++i) {
                for (var refine in refinements[i]) {
                    helper.toggleRefine(refine, refinements[i][refine]);
                }
            }

            for (var key in numericsRefinements)
                for (var operator in numericsRefinements[key])
                    helper.addNumericRefinement(key, operator, numericsRefinements[key][operator]);

            helper.setIndex(indexName).setCurrentPage(page);

            helper.search();

        }
    }

    function getFacets(content) {

        var facets = [];

        for (var i = 0; i < algoliaSettings.facets.length; i++)
        {
            var sub_facets = [];

            if (custom_facets_types[algoliaSettings.facets[i].type] != undefined)
            {
                try
                {
                    var params = custom_facets_types[algoliaSettings.facets[i].type](helper, content, algoliaSettings.facets[i]);

                    if (params)
                        for (var k = 0; k < params.length; k++)
                            sub_facets.push(params[k]);
                }
                catch(error)
                {
                    console.log(error.message);
                    throw("Bad facet function for '" + algoliaSettings.facets[i].type + "'");
                }
            }
            else
            {
                var content_facet = content.getFacetByName(algoliaSettings.facets[i].tax);

                if (content_facet == undefined)
                    continue;

                for (var key in content_facet.data)
                {
                    var checked = helper.isRefined(algoliaSettings.facets[i].tax, key);

                    var name = window.facetsLabels && window.facetsLabels[key] != undefined ? window.facetsLabels[key] : key;
                    var nameattr = key;

                    var params = {
                        type: {},
                        checked: checked,
                        nameattr: nameattr,
                        name: name,
                        count: content_facet.data[key]
                    };
                    params.type[algoliaSettings.facets[i].type] = true;

                    sub_facets.push(params);
                }
            }
            facets.push({count: sub_facets.length, tax: algoliaSettings.facets[i].tax, facet_categorie_name: algoliaSettings.facets[i].name, sub_facets: sub_facets });
        }

        return facets;
    }

    function getPages(content) {
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

        return pages;
    }


    /*****************
     **
     ** RENDERING HELPERS
     **
     *****************/

    function getHtmlForPagination(paginationTemplate, content, pages, facets) {
        var pagination_html = paginationTemplate.render({
            pages: pages,
            facets_count: facets.length,
            prev_page: (content.page > 0 ? content.page : false),
            next_page: (content.page + 1 < content.nbPages ? content.page + 2 : false)
        });

        return pagination_html;
    }

    function getHtmlForResults(resultsTemplate, content, facets) {

        var fields = ['h1', 'h2', 'h3', 'h4', 'h5', 'h6', 'text'];

        for (var l = 0; l < content.hits.length; l++)
        {
            if (content.hits[l].type != 'page' && content.hits[l].type != 'post')
                continue;

            var content_matches = {};

            var noHighlights = false;

            var highligth_hit = content.hits[l]._highlightResult;

            for (var i = 0; i < fields.length; i++)
            {
                if (highligth_hit[fields[i]] != undefined)
                {
                    for (var j = 0; j < highligth_hit[fields[i]].length; j++)
                    {
                        for (var k = 0; k < highligth_hit[fields[i]][j].value.matchedWords.length; k++)
                        {
                            if (content_matches[highligth_hit[fields[i]][j].value.matchedWords[k]] == undefined)
                            {
                                content_matches[highligth_hit[fields[i]][j].value.matchedWords[k]] = {i: i, type: fields[i], order: highligth_hit[fields[i]][j].order, count : highligth_hit[fields[i]][j].value.matchedWords.length, value: highligth_hit[fields[i]][j].value.value};
                            }
                            else
                            {
                                if (i == content_matches[highligth_hit[fields[i]][j].value.matchedWords[k]].i
                                    && highligth_hit[fields[i]][j].value.matchedWords.length > content_matches[highligth_hit[fields[i]][j].value.matchedWords[k]].count)
                                {
                                    content_matches[highligth_hit[fields[i]][j].value.matchedWords[k]] = {i: i, type: fields[i], order: highligth_hit[fields[i]][j].order, count : highligth_hit[fields[i]][j].value.matchedWords.length, value: highligth_hit[fields[i]][j].value.value};
                                }
                            }
                        }
                    }
                }
            }

            content_matches = $.map(content_matches, function(value, index) {
                return [value];
            });

            if (content_matches.length == 0)
            {
                noHighlights = true;

                for (var i = 0; i < fields.length; i++)
                {
                    if (content.hits[l][fields[i]] != undefined)
                    {
                        for (var j = 0; j < content.hits[l][fields[i]].length; j++)
                        {
                            content.hits[l][fields[i]][j].type = fields[i];
                            content_matches.push(content.hits[l][fields[i]][j]);
                        }
                    }
                }
            }

            content_matches.sort(function (a, b) {
                if (a.order < b.order)
                    return -1;
                return 1;
            });

            if (content.hits[l]._highlightResult == undefined)
                content.hits[l]._highlightResult = {};

            if (content.hits[l]._highlightResult.content == undefined)
                content.hits[l]._highlightResult.content = {};

            content.hits[l]._highlightResult.content.value = "";

            var separator = "<div>[...]</div>";
            var old_order = -1;
            for (i = 0; i < content_matches.length; i++)
            {
                if (old_order != content_matches[i].order)
                {
                    old_order = content_matches[i].order;

                    var balise = content_matches[i].type != "text" ? content_matches[i].type : "div";

                    content.hits[l]._highlightResult.content.value += "<div>";
                    content.hits[l]._highlightResult.content.value += "<" + balise + '>';
                    content.hits[l]._highlightResult.content.value += content_matches[i].value;
                    content.hits[l]._highlightResult.content.value += "</" + balise + '>';
                    content.hits[l]._highlightResult.content.value += "</div>";

                    if (noHighlights === false)
                        content.hits[l]._highlightResult.content.value += separator;
                }
            }

            if (noHighlights == false)
                content.hits[l]._highlightResult.content.value.substring(0, content.hits[l]._highlightResult.content.value.length - separator.length);
        }



        var results_html = resultsTemplate.render({
            facets_count: facets.length,
            getDate: getDate,
            relevance_index_name: algoliaSettings.index_name + 'all',
            sorting_indices: algoliaSettings.sorting_indices,
            sortSelected: sortSelected,
            hits: content.hits,
            nbHits: content.nbHits,
            nbHits_zero: (content.nbHits === 0),
            nbHits_one: (content.nbHits === 1),
            nbHits_many: (content.nbHits > 1),
            query: helper.state.query,
            processingTimeMS: content.processingTimeMS
        });

        return results_html;
    }

    function getHtmlForFacets(facetsTemplate, facets) {

        var facets_html = facetsTemplate.render({
            facets: facets,
            count: facets.length,
            getDate: getDate,
            relevance_index_name: algoliaSettings.index_name + 'all',
            sorting_indices: algoliaSettings.sorting_indices,
            sortSelected: sortSelected
        });

        return facets_html;
    }

    function sortSelected() {
        return function (val) {
            var template = algoliaBundle.Hogan.compile(val);

            var renderer = function(context) {
                return function(text) {
                    return template.c.compile(text, template.options).render(context);
                };
            };

            var render = renderer(this);

            var index_name = render(val);

            if (index_name == helper.getIndex())
                return "selected";
            return "";
        }
    }

    function gotoPage(page) {
        helper.setCurrentPage(+page - 1);
    }

    function getDate() {
        return function (val) {
            var template = algoliaBundle.Hogan.compile(val);

            var renderer = function(context) {
                return function(text) {
                    return template.c.compile(text, template.options).render(context);
                };
            };

            var render = renderer(this);

            var timestamp = render(val);


            var date = new Date(timestamp * 1000);

            var days = ["Sunday", "Monday", "Tuesday", "Wednesday", "Thursday", "Friday", "Saturday"];
            var months = ["January", "February", "March", "April", "May", "June", "July", "August", "September", "October", "November", "December"];

            var day = date.getDate();

            if (day == 1)
                day += "st";
            else if (day == 2)
                day += "nd";
            else if (day == 3)
                day += "rd";
            else
                day += "th";

            return days[date.getDay()] + ", " + months[date.getMonth()] + " " + day + ", " + date.getFullYear();
        }
    }

    /*****************
     **
     ** AUTOCOMPLETE
     **
     *****************/

    if (algoliaSettings.autocomplete)
    {
        var $autocompleteTemplate = algoliaBundle.Hogan.compile($('#autocomplete-template').text());

        var hogan_objs = [];

        algoliaSettings.indices.sort(indicesCompare);

        var indices = [];
        for (var i = 0; i < algoliaSettings.indices.length; i++)
            indices.push(algolia_client.initIndex(algoliaSettings.indices[i].index_name));

        for (var i = 0; i < algoliaSettings.indices.length; i++) {
            hogan_objs.push({
                source: indices[i].ttAdapter({hitsPerPage: algoliaSettings.number_by_type}),
                displayKey: 'title',
                templates: {
                    header: '<div class="category">' + algoliaSettings.indices[i].name + '</div>',
                    suggestion: function (hit) {
                        return $autocompleteTemplate.render(hit);
                    }
                }
            });

        }

        hogan_objs.push({
            source: getBrandingHits(),
            displayKey: 'title',
            templates: {
                suggestion: function (hit) {
                    return '<div class="footer">powered by <img width="45" src="' + algoliaSettings.plugin_url + '/front/algolia-logo.png"></div>';
                }
            }
        });

        $(algoliaSettings.search_input_selector).each(function (i) {
            $(this).typeahead({hint: false}, hogan_objs);

            $(this).on('typeahead:selected', function (e, item) {
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

    if (algoliaSettings.instant && (algoliaSettings.is_search_page === '1' || ! algoliaSettings.autocomplete))
    {
        window.facetsLabels = {
            'post': 'Article',
            'page': 'Page',
            'product': 'Products'
        };

        if ($(algoliaSettings.instant_jquery_selector).length !== 1)
            throw '[Algolia] Invalid instant-search selector: ' + algoliaSettings.instant_jquery_selector;

        if ($(algoliaSettings.instant_jquery_selector).find(algoliaSettings.search_input_selector).length > 0)
            throw '[Algolia] You can\'t have a search input matching "' + algoliaSettings.search_input_selector +
            '" inside you instant selector "' + algoliaSettings.instant_jquery_selector + '"';

        /**
         * Variables Initialization
         */

        var instant_selector = ! algoliaSettings.autocomplete ? algoliaSettings.search_input_selector : "#instant-search-bar";

        var wrapperTemplate     = algoliaBundle.Hogan.compile($('#instant_wrapper_template').html());

        var initialized         = false;

        var resultsTemplate     = algoliaBundle.Hogan.compile($('#instant-content-template').text());
        var facetsTemplate      = algoliaBundle.Hogan.compile($('#instant-facets-template').text());
        var paginationTemplate  = algoliaBundle.Hogan.compile($('#instant-pagination-template').text());

        var conjunctive_facets  = [];
        var disjunctive_facets  = [];

        var history_timeout;

        for (var i = 0; i < algoliaSettings.facets.length; i++)
        {
            if (algoliaSettings.facets[i].type == "conjunctive")
                conjunctive_facets.push(algoliaSettings.facets[i].tax);

            if (algoliaSettings.facets[i].type == "disjunctive")
                disjunctive_facets.push(algoliaSettings.facets[i].tax);

            if (algoliaSettings.facets[i].type == "slider")
                disjunctive_facets.push(algoliaSettings.facets[i].tax);

            if (algoliaSettings.facets[i].type == "menu")
                disjunctive_facets.push(algoliaSettings.facets[i].tax);
        }

        algoliaSettings.facets = algoliaSettings.facets.sort(facetsCompare);

        var helper = algoliaBundle.algoliasearchHelper(algolia_client, algoliaSettings.index_name + 'all', {
            facets: conjunctive_facets,
            disjunctiveFacets: disjunctive_facets,
            hitsPerPage: algoliaSettings.number_by_page
        });

        /**
         * Functions
         */

        function performQueries(push_state)
        {
            helper.search(helper.state.query, searchCallback);

            updateUrl(push_state);
        }

        function searchCallback(content)
        {
            if (initialized === false)
            {
                $(algoliaSettings.instant_jquery_selector).html(wrapperTemplate.render({ second_bar: algoliaSettings.autocomplete })).show();
                initialized = true;
            }

            var instant_search_facets_container = $('#instant-search-facets-container');
            var instant_search_results_container = $('#instant-search-results-container');
            var instant_search_pagination_container = $('#instant-search-pagination-container');

            instant_search_facets_container.html('');
            instant_search_results_container.html('');
            instant_search_pagination_container.html('');

            var facets = [];
            var pages = [];

            if (content.hits.length > 0)
            {
                facets = getFacets(content);
                pages = getPages(content);

                instant_search_facets_container.html(getHtmlForFacets(facetsTemplate, facets));
            }

            instant_search_results_container.html(getHtmlForResults(resultsTemplate, content, facets));

            if (content.hits.length > 0)
                instant_search_pagination_container.html(getHtmlForPagination(paginationTemplate, content, pages, facets));

            updateSliderValues();

            var instant_search_bar = $(instant_selector);

            if (instant_search_bar.is(":focus") === false)
            {
                instant_search_bar.focus().val('');
                instant_search_bar.val(helper.state.query);
            }
        }


        helper.on('result', searchCallback);


        /**
         * Custom Facets Types
         */

        custom_facets_types["slider"] = function (helper, content, facet) {
            if (content.getFacetByName(facet.tax) != undefined)
            {
                var min = content.getFacetByName(facet.tax).stats.min;
                var max = content.getFacetByName(facet.tax).stats.max;

                var current_min = helper.state.getNumericRefinement(facet.tax, ">=");
                var current_max = helper.state.getNumericRefinement(facet.tax, "<=");

                if (current_min == undefined)
                    current_min = min;

                if (current_max == undefined)
                    current_max = max;

                var params = {
                    type: {},
                    current_min: Math.floor(current_min),
                    current_max: Math.ceil(current_max),
                    count: min == max ? 0 : 1,
                    min: Math.floor(min),
                    max: Math.ceil(max)
                };

                params.type[facet.type] = true;

                return [params];
            }

            return [];
        };

        custom_facets_types["menu"] = function (helper, content, facet) {

            var data = [];

            var all_count = 0;
            var all_unchecked = true;

            var content_facet = content.getFacetByName(facet.tax);

            if (content_facet == undefined)
                return data;

            for (var key in content_facet.data)
            {
                var checked = helper.isRefined(facet.tax, key);

                all_unchecked = all_unchecked && !checked;

                var name = window.facetsLabels && window.facetsLabels[key] != undefined ? window.facetsLabels[key] : key;
                var nameattr = key;

                var params = {
                    type: {},
                    checked: checked,
                    nameattr: nameattr,
                    name: name,
                    print_count: true,
                    count: content_facet.data[key]
                };

                all_count += content_facet.data[key];

                params.type[facet.type] = true;

                data.push(params);
            }

            var params = {
                type: {},
                checked: all_unchecked,
                nameattr: 'all',
                name: 'All',
                print_count: false,
                count: all_count
            };

            params.type[facet.type] = true;

            data.unshift(params);

            return data;
        };

        /**
         * Handle click on menu custom facet
         */
        $("body").on("click", ".sub_facet.menu", function (e) {

            e.stopImmediatePropagation();

            if ($(this).attr("data-name") == "all")
                helper.state.clearRefinements($(this).attr("data-tax"));

            $(this).find("input[type='checkbox']").each(function (i) {
                $(this).prop("checked", !$(this).prop("checked"));

                if (false == helper.isRefined($(this).attr("data-tax"), $(this).attr("data-name")))
                    helper.state.clearRefinements($(this).attr("data-tax"));

                if ($(this).attr("data-name") != "all")
                    helper.toggleRefine($(this).attr("data-tax"), $(this).attr("data-name"));
            });

            performQueries(true);
        });

        /**
         * Handle click on conjunctive and disjunctive facet
         */
        $("body").on("click", ".sub_facet", function () {

            $(this).find("input[type='checkbox']").each(function (i) {
                $(this).prop("checked", !$(this).prop("checked"));

                helper.toggleRefine($(this).attr("data-tax"), $(this).attr("data-name"));
            });

            performQueries(true);
        });

        /**
         * Handle jquery-ui slider initialisation
         */
        $("body").on("slide", "", function (event, ui) {
            updateSlideInfos(ui);
        });


        /**
         * Handle sort change
         */
        $("body").on("change", "#index_to_use", function () {
            helper.setIndex($(this).val());

            helper.setCurrentPage(0);

            performQueries(true);
        });

        /**
         * Handle jquery-ui slide event
         */
        $("body").on("slidechange", ".algolia-slider-true", function (event, ui) {

            var slide_dom = $(ui.handle).closest(".algolia-slider");
            var min = slide_dom.slider("values")[0];
            var max = slide_dom.slider("values")[1];

            if (parseInt(slide_dom.slider("values")[0]) >= parseInt(slide_dom.attr("data-min")))
                helper.addNumericRefinement(slide_dom.attr("data-tax"), ">=", min);
            if (parseInt(slide_dom.slider("values")[1]) <= parseInt(slide_dom.attr("data-max")))
                helper.addNumericRefinement(slide_dom.attr("data-tax"), "<=", max);

            if (parseInt(min) == parseInt(slide_dom.attr("data-min")))
                helper.removeNumericRefinement(slide_dom.attr("data-tax"), ">=");

            if (parseInt(max) == parseInt(slide_dom.attr("data-max")))
                helper.removeNumericRefinement(slide_dom.attr("data-tax"), "<=");

            updateSlideInfos(ui);
            performQueries(true);
        });

        /**
         * Handle page change
         */
        $("body").on("click", ".algolia-pagination a", function (e) {
            e.preventDefault();

            gotoPage($(this).attr("data-page"));
            performQueries(true);

            $("body").scrollTop(0);

            return false;
        });

        /**
         * Handle input clearing
         */
        $('body').on('click', '.clear-button', function () {
            $(instant_selector).val('').focus();
            helper.clearRefinements().setQuery('');

            performQueries(true);
        });

        /**
         * Handle search
         */
        $('body').on('keyup', instant_selector, function (e) {
            e.preventDefault();

            helper.setQuery($(this).val());

            /* Uncomment to clear refinements on keyup */

            //helper.clearRefinements();

            performQueries(false);

            return false;
        });

        function updateSliderValues()
        {
            $(".algolia-slider-true").each(function (i) {
                var min = $(this).attr("data-min");
                var max = $(this).attr("data-max");

                var new_min = helper.state.getNumericRefinement($(this).attr("data-tax"), ">=");
                var new_max = helper.state.getNumericRefinement($(this).attr("data-tax"), "<=");

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

        function updateSlideInfos(ui)
        {
            var infos = $(ui.handle).closest(".algolia-slider").nextAll(".algolia-slider-info");

            infos.find(".min").html(ui.values[0]);
            infos.find(".max").html(ui.values[1]);
        }

        /**
         * Initialization
         */

        if (algoliaSettings.is_search_page === '1' || location.hash.length > 1) {
            getRefinementsFromUrl();
        }

        window.addEventListener("popstate", function(e) {
            getRefinementsFromUrl();
        });
    }

    $(algoliaSettings.search_input_selector).attr('autocomplete', 'off').attr('autocorrect', 'off').attr('spellcheck', 'false').attr('autocapitalize', 'off');
});
