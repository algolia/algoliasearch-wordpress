/**
 * Common variables and function for autocomplete and instant search
 */
var algolia_client = algoliasearch(algoliaSettings.app_id, algoliaSettings.search_key);
var custom_facets_types = algoliaSettings.theme.facet_types;

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
 * Autocomplete functions
 */

if (algoliaSettings.type_of_search.indexOf("autocomplete") !== -1)
{
    window.getBrandingHits = function () {
        return function findMatches(q, cb) {
            return cb(["algolia-branding"]);
        }
    };
}

/**
 * Instant Search
 */

if (algoliaSettings.type_of_search.indexOf("instant") !== -1)
{
    var engine;
    var history_timeout;

    jQuery(document).ready(function ($) {

        if ($(algoliaSettings.instant_jquery_selector).length !== 1)
            throw '[Algolia] Invalid instant-search selector: ' + algoliaSettings.instant_jquery_selector;

        if ($(algoliaSettings.instant_jquery_selector).find(algoliaSettings.search_input_selector).length > 0)
            throw '[Algolia] You can\'t have a search input matching "' + algoliaSettings.search_input_selector +
            '" inside you instant selector "' + algoliaSettings.instant_jquery_selector + '"';

        engine = new function () {

            this.helper = undefined;

            this.setHelper = function (helper) {
              this.helper = helper;
              this.helper.setQuery('');
            };

            this.updateUrl = function (push_state)
            {
                var refinements = [];

                /** Get refinements for conjunctive facets **/
                for (var refine in this.helper.state.facetsRefinements)
                {
                    if (this.helper.state.facetsRefinements[refine])
                    {
                        var r = {};

                        r[refine] = this.helper.state.facetsRefinements[refine];

                        refinements.push(r);
                    }
                }

                /** Get refinements for disjunctive facets **/
                for (var refine in this.helper.state.disjunctiveFacetsRefinements)
                {
                    for (var i = 0; i < this.helper.state.disjunctiveFacetsRefinements[refine].length; i++)
                    {
                        var r = {};

                        r[refine] = this.helper.state.disjunctiveFacetsRefinements[refine][i];

                        refinements.push(r);
                    }
                }

                var url = '#q=' + encodeURIComponent(this.helper.state.query) + '&page=' + this.helper.getCurrentPage() + '&refinements=' + encodeURIComponent(JSON.stringify(refinements)) + '&numerics_refinements=' + encodeURIComponent(JSON.stringify(this.helper.state.numericRefinements)) + '&index_name=' + encodeURIComponent(JSON.stringify(this.helper.getIndex()));

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
            };

            this.getRefinementsFromUrl = function()
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

                    this.helper.setQuery(q);

                    this.helper.clearRefinements();

                    /** Set refinements from url data **/
                    for (var i = 0; i < refinements.length; ++i) {
                        for (var refine in refinements[i]) {
                            this.helper.toggleRefine(refine, refinements[i][refine]);
                        }
                    }

                    for (var key in numericsRefinements)
                        for (var operator in numericsRefinements[key])
                            this.helper.addNumericRefinement(key, operator, numericsRefinements[key][operator]);

                    this.helper.setCurrentPage(page);
                    this.helper.setIndex(indexName);

                    $(algoliaSettings.search_input_selector).val(this.helper.state.query);

                    this.helper.search();

                }
            };

            this.getFacets = function (content) {

                var facets = [];

                for (var i = 0; i < algoliaSettings.facets.length; i++)
                {
                    var sub_facets = [];

                    if (custom_facets_types[algoliaSettings.facets[i].type] != undefined)
                    {
                        try
                        {
                            var params = custom_facets_types[algoliaSettings.facets[i].type](this, content, algoliaSettings.facets[i]);

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
                            var checked = this.helper.isRefined(algoliaSettings.facets[i].tax, key);

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
            };

            this.getPages = function (content) {
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
            };


            /**
             * Rendering Html Function
             */
            this.getHtmlForPagination = function (paginationTemplate, content, pages, facets) {
                var pagination_html = paginationTemplate.render({
                    pages: pages,
                    facets_count: facets.length,
                    prev_page: (content.page > 0 ? content.page : false),
                    next_page: (content.page + 1 < content.nbPages ? content.page + 2 : false)
                });

                return pagination_html;
            };

            this.getHtmlForResults = function (resultsTemplate, content, facets) {

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
                    getDate: this.getDate,
                    relevance_index_name: algoliaSettings.index_name + 'all',
                    sorting_indices: algoliaSettings.sorting_indices,
                    sortSelected: this.sortSelected,
                    hits: content.hits,
                    nbHits: content.nbHits,
                    nbHits_zero: (content.nbHits === 0),
                    nbHits_one: (content.nbHits === 1),
                    nbHits_many: (content.nbHits > 1),
                    query: this.helper.state.query,
                    processingTimeMS: content.processingTimeMS
                });

                return results_html;
            };

            this.getHtmlForFacets = function (facetsTemplate, facets) {

                var facets_html = facetsTemplate.render({
                    facets: facets,
                    count: facets.length,
                    getDate: this.getDate,
                    relevance_index_name: algoliaSettings.index_name + 'all',
                    sorting_indices: algoliaSettings.sorting_indices,
                    sortSelected: this.sortSelected
                });

                return facets_html;
            };

            /**
             * Helper methods
             */
            this.sortSelected = function () {
                return function (val) {
                    var template = Hogan.compile(val);

                    var renderer = function(context) {
                        return function(text) {
                            return template.c.compile(text, template.options).render(context);
                        };
                    };

                    var render = renderer(this);

                    var index_name = render(val);

                    if (index_name == engine.helper.getIndex())
                        return "selected";
                    return "";
                }
            };

            this.gotoPage = function(page) {
                this.helper.setCurrentPage(+page - 1);
            };

            this.getDate = function () {
                return function (val) {
                    var template = Hogan.compile(val);

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
            };
        };
    });
}