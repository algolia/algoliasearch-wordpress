jQuery(document).ready(function ($) {

    if (algoliaSettings.type_of_search == "autocomplete")
    {
        jQuery(document).ready(function ($)
        {
            var $autocompleteTemplate = Hogan.compile($('#autocomplete-template').text());

            var hogan_objs = [];

            algoliaSettings.indices.sort(indicesCompare);

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

            $(algoliaSettings.search_input_selector).each(function (i) {
                $(this).typeahead({hint: false}, hogan_objs);

                $(this).on('typeahead:selected', function (e, item) {
                    window.location.href = item.permalink;
                });
            });
        });
    }

    if (algoliaSettings.type_of_search == "instant")
    {
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
        }

        algoliaSettings.facets = algoliaSettings.facets.sort(facetsCompare);

        engine.setHelper(new AlgoliaSearchHelper(algolia_client, algoliaSettings.index_name + 'all', {
            facets: conjunctive_facets,
            disjunctiveFacets: disjunctive_facets,
            hitsPerPage: algoliaSettings.number_by_page
        }));

        /**
         * Functions
         */

        function performQueries(push_state)
        {
            engine.helper.search(engine.query, searchCallback);

            engine.updateUrl(push_state);
        }

        function searchCallback(success, content)
        {
            if (success)
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
        }

        /**
         * Bindings
         */
        $("body").on("click", ".sub_facet", function () {
            $(this).find("input[type='checkbox']").each(function (i) {
                $(this).prop("checked", !$(this).prop("checked"));
                engine.helper.toggleRefine($(this).attr("data-tax"), $(this).attr("data-name"));
            });

            engine.helper.setPage(0);

            performQueries(true);
        });


        $("body").on("slide", "", function (event, ui) {
            updateSlideInfos(ui);
        });

        $("body").on("change", "#index_to_use", function () {
            engine.helper.setIndex($(this).val());

            engine.helper.setPage(0);

            performQueries(true);
        });

        $("body").on("slidechange", ".algolia-slider-true", function (event, ui) {

            var slide_dom = $(ui.handle).closest(".algolia-slider");
            var min = slide_dom.slider("values")[0];
            var max = slide_dom.slider("values")[1];

            if (parseInt(slide_dom.slider("values")[0]) >= parseInt(slide_dom.attr("data-min")))
                engine.helper.addNumericsRefine(slide_dom.attr("data-tax"), ">=", min);
            if (parseInt(slide_dom.slider("values")[1]) <= parseInt(slide_dom.attr("data-max")))
                engine.helper.addNumericsRefine(slide_dom.attr("data-tax"), "<=", max);

            if (parseInt(min) == parseInt(slide_dom.attr("data-min")))
                engine.helper.removeNumericRefine(slide_dom.attr("data-tax"), ">=");

            if (parseInt(max) == parseInt(slide_dom.attr("data-max")))
                engine.helper.removeNumericRefine(slide_dom.attr("data-tax"), "<=");

            engine.helper.setPage(0);

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

            var $this = $(this);

            engine.query = $(this).val();

            $(algoliaSettings.search_input_selector).each(function (i) {
                if ($(this)[0] != $this[0])
                    $(this).val(engine.query);
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

                var new_min = engine.helper.getNumericsRefine($(this).attr("data-tax"), ">=");
                var new_max = engine.helper.getNumericsRefine($(this).attr("data-tax"), "<=");

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

        $(algoliaSettings.search_input_selector).attr('autocomplete', 'off');

        engine.getRefinementsFromUrl(searchCallback);

        window.addEventListener("popstate", function(e) {
            engine.getRefinementsFromUrl(searchCallback);
        });
    }
});