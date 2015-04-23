jQuery(document).ready(function ($) {

    var autocomplete = true;
    var instant = true;

    if (algoliaSettings.type_of_search.indexOf("autocomplete") !== -1)
    {
        var $autocompleteTemplate = Hogan.compile($('#autocomplete-template').text());

        var hogan_objs = [];

        algoliaSettings.indices.sort(indicesCompare);

        var indices = [];
        for (var i = 0; i < algoliaSettings.indices.length; i++)
            indices.push(algolia_client.initIndex(algoliaSettings.indices[i].index_name));

        for (var i = 0; i < algoliaSettings.indices.length; i++)
        {
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

        function activateAutocomplete()
        {
            $(algoliaSettings.search_input_selector).each(function (i) {

                $(this).typeahead({hint: false}, hogan_objs);

                $(this).on('typeahead:selected', function (e, item) {
                    autocomplete = false;
                    instant = false;
                    window.location.href = item.permalink;
                });
            });

        }

        activateAutocomplete();

        function desactivateAutocomplete()
        {
            $(algoliaSettings.search_input_selector).each(function (i) {
                $(this).typeahead('destroy')
            });
        }
    }

    if (algoliaSettings.type_of_search.indexOf("instant") !== -1)
    {
        window.facetsLabels = {
            'post': 'Article',
            'page': 'Page'
        };

        /**
         * Variables Initialization
         */

        var old_content         = $(algoliaSettings.instant_jquery_selector).html();

        var resultsTemplate     = Hogan.compile($('#instant-content-template').text());
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

            if (algoliaSettings.facets[i].type == "menu")
                conjunctive_facets.push(algoliaSettings.facets[i].tax);
        }

        algoliaSettings.facets = algoliaSettings.facets.sort(facetsCompare);

        var helper = algoliasearchHelper(algolia_client, algoliaSettings.index_name + 'all', {
            facets: conjunctive_facets,
            disjunctiveFacets: disjunctive_facets,
            hitsPerPage: algoliaSettings.number_by_page
        });

        function activateInstant()
        {
            helper.on('result', searchCallback);
        }

        activateInstant();

        function desactivateInstant()
        {
            helper.removeAllListeners();
            location.replace('#');
            $(algoliaSettings.instant_jquery_selector).html(old_content);
        }

        engine.setHelper(helper);

        /**
         * Functions
         */

        function performQueries(push_state)
        {
            engine.helper.search(engine.helper.state.query);

            engine.updateUrl(push_state);
        }

        function searchCallback(content)
        {
            var html_content = "";

            html_content += "<div id='algolia_instant_selector'>";

            var facets = [];
            var pages = [];

            if (content.hits.length > 0)
            {
                facets = engine.getFacets(content);
                pages = engine.getPages(content);

                html_content += engine.getHtmlForFacets(facetsTemplate, facets);
            }

            html_content += engine.getHtmlForResults(resultsTemplate, content, facets);

            if (content.hits.length > 0)
                html_content += engine.getHtmlForPagination(paginationTemplate, content, pages, facets);

            html_content += "</div>";

            $(algoliaSettings.instant_jquery_selector).html(html_content);

            updateSliderValues();
        }

        /**
         * Custom Facets Types
         */

        custom_facets_types["slider"] = function (engine, content, facet) {
            if (content.getFacetByName(facet.tax) != undefined)
            {
                var min = content.getFacetByName(facet.tax).stats.min;
                var max = content.getFacetByName(facet.tax).stats.max;

                var current_min = engine.helper.state.getNumericRefinement(facet.tax, ">=");
                var current_max = engine.helper.state.getNumericRefinement(facet.tax, "<=");

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

        /**
         * Bindings
         */
        $("body").on("click", ".sub_facet", function () {
            $(this).find("input[type='checkbox']").each(function (i) {
                $(this).prop("checked", !$(this).prop("checked"));
                engine.helper.toggleRefine($(this).attr("data-tax"), $(this).attr("data-name"));
            });

            performQueries(true);
        });


        $("body").on("slide", "", function (event, ui) {
            updateSlideInfos(ui);
        });

        $("body").on("change", "#index_to_use", function () {
            engine.helper.setIndex($(this).val());

            performQueries(true);

            engine.helper.setPage(0);
        });

        $("body").on("slidechange", ".algolia-slider-true", function (event, ui) {

            var slide_dom = $(ui.handle).closest(".algolia-slider");
            var min = slide_dom.slider("values")[0];
            var max = slide_dom.slider("values")[1];

            if (parseInt(slide_dom.slider("values")[0]) >= parseInt(slide_dom.attr("data-min")))
                engine.helper.addNumericRefinement(slide_dom.attr("data-tax"), ">=", min);
            if (parseInt(slide_dom.slider("values")[1]) <= parseInt(slide_dom.attr("data-max")))
                engine.helper.addNumericRefinement(slide_dom.attr("data-tax"), "<=", max);

            if (parseInt(min) == parseInt(slide_dom.attr("data-min")))
                engine.helper.removeNumericRefinement(slide_dom.attr("data-tax"), ">=");

            if (parseInt(max) == parseInt(slide_dom.attr("data-max")))
                engine.helper.removeNumericRefinement(slide_dom.attr("data-tax"), "<=");

            updateSlideInfos(ui);
            performQueries(true);
        });

        $("body").on("click", ".algolia-pagination a", function (e) {
            e.preventDefault();

            engine.gotoPage($(this).attr("data-page"));
            performQueries(true);

            $("body").scrollTop(0);

            return false;
        });

        $(algoliaSettings.search_input_selector).keyup(function (e) {
            e.preventDefault();

            if (instant === false)
                return;

            var $this = $(this);

            engine.helper.setQuery($(this).val());

            $(algoliaSettings.search_input_selector).each(function (i) {
                if ($(this)[0] != $this[0])
                    $(this).val(engine.helper.state.query);
            });

            if ($(this).val().length == 0) {

                clearTimeout(history_timeout);

                location.replace('#');

                $(algoliaSettings.instant_jquery_selector).html(old_content);

                return;
            }

            /* Uncomment to clear refinements on keyup */

            //engine.helper.clearRefinements();
            //engine.helper.clearNumericRefinements();


            performQueries(false);

            return false;
        });

        function updateSliderValues()
        {
            $(".algolia-slider-true").each(function (i) {
                var min = $(this).attr("data-min");
                var max = $(this).attr("data-max");

                var new_min = engine.helper.state.getNumericRefinement($(this).attr("data-tax"), ">=");
                var new_max = engine.helper.state.getNumericRefinement($(this).attr("data-tax"), "<=");

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
        };

        function updateSlideInfos(ui)
        {
            var infos = $(ui.handle).closest(".algolia-slider").nextAll(".algolia-slider-info");

            infos.find(".min").html(ui.values[0]);
            infos.find(".max").html(ui.values[1]);
        }

        /**
         * Initialization
         */

        $(algoliaSettings.search_input_selector).attr('autocomplete', 'off').attr('autocorrect', 'off').attr('spellcheck', 'false').attr('autocapitalize', 'off');

        engine.getRefinementsFromUrl();

        window.addEventListener("popstate", function(e) {
            engine.getRefinementsFromUrl();
        });

        if (algoliaSettings.type_of_search.indexOf("autocomplete") !== -1 && algoliaSettings.type_of_search.indexOf("instant") !== -1)
        {
            if (location.hash.length <= 1)
            {
                desactivateInstant();
                instant = false;
            }
            else
            {
                autocomplete = false;
                desactivateAutocomplete();
                $(algoliaSettings.search_input_selector+':first').focus();
            }
        }
    }
});