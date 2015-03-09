console.log(algoliaSettings);

/**
 * Common stuff
 */
var algolia_client = new AlgoliaSearch(algoliaSettings.app_id, algoliaSettings.search_key);
var indexes = [];

for (var i = 0; i < algoliaSettings.indexes.length; i++)
    indexes.push(algolia_client.initIndex(algoliaSettings.indexes[i].index_name));

/**
 * Autocomplete
 */

if (algoliaSettings.type_of_search == "autocomplete")
{
    jQuery(document).ready(function ($)
    {
        var template = Hogan.compile(
            '<div class="result">' +
            '<div class="title">' +
            '{{#featureImage}}' +
            '<img width="30px" src="{{{ featureImage.sizes.thumbnail.file }}}" />' +
            '{{/featureImage}} ' +
            '{{{ _highlightResult.title.value }}}' +
            '</div>' +
            '</div>'
        );

        hogan_objs = [];

        for (var i = 0; i < algoliaSettings.indexes.length; i++)
        {
            hogan_objs.push({
                source: indexes[i].ttAdapter({hitsPerPage: 5}),
                displayKey: 'title',
                templates: {
                    header: '<div class="category">' + algoliaSettings.indexes[i].name + '</div>',
                    suggestion: function (hit) {
                        console.log(hit);
                        return template.render(hit);
                    }
                }
            });
        }

        $("input[name='s']").each(function (i) {
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

var algolia_div_id = "algolia_instant_selector";

if (algoliaSettings.type_of_search == "instant")
{
    jQuery(document).ready(function ($) {

        if ($(algoliaSettings.instant_jquery_selector).length == 1)
        {
            var template = Hogan.compile(
                '<div class="hits">' +
                '{{#hits}}' +
                    '<div class="result entry-header">' +
                        '<div>' +
                            '{{#featureImage}}' +
                                '<div class="entry-thumbnail">' +
                                    '<img src="{{{ featureImage.file }}}" />' +
                                '</div>' +
                            '{{/featureImage}} ' +
                            '<div>' +
                                '<h1 class="entry-title">' +
                                    '<a href="{{permalink}}">' +
                                        '{{{ _highlightResult.title.value }}}' +
                                    '</a>' +
                                '</h1>' +
                            '</div>' +
                        '</div>' +
                    '</div>' +
                '{{/hits}}' +
                '</div>' +
                '<div style="clear: both;"></div>'
            );

            var facetsTemplate = Hogan.compile(
                '<div class="facets">' +
                    '{{#facets}}' +
                        '{{#count}}' +
                        '<div class="facet">' +
                            '<div class="name">' +
                                '{{ facet_categorie_name }}' +
                            '</div>' +
                            '<div>' +
                                '{{#sub_facets}}' +
                                    '{{#conjunctive}}' +
                                        '{{#checked}}' +
                                        '<div class="checked sub_facet conjunctive">' +
                                        '{{/checked}}' +
                                        '{{^checked}}' +
                                        '<div class="sub_facet conjunctive">' +
                                        '{{/checked}}' +
                                            '{{#checked}}' +
                                                '<input style="display: none;" data-tax="{{tax}}" checked data-name="{{name}}" class="facet_value" type="checkbox" />' +
                                            '{{/checked}}' +
                                            '{{^checked}}' +
                                                '<input style="display: none;" data-tax="{{tax}}" data-name="{{name}}" class="facet_value" type="checkbox" />' +
                                            '{{/checked}}' +
                                            ' {{name}} ({{count}})' +
                                        '</div>' +
                                    '{{/conjunctive}}' +
                                    '{{#disjunctive}}' +
                                        '{{#checked}}' +
                                        '<div class="checked sub_facet disjunctive">' +
                                        '{{/checked}}' +
                                        '{{^checked}}' +
                                        '<div class="sub_facet disjunctive">' +
                                        '{{/checked}}' +
                                            '{{#checked}}' +
                                                '<input data-tax="{{tax}}" checked data-name="{{name}}" class="facet_value" type="checkbox" />' +
                                            '{{/checked}}' +
                                            '{{^checked}}' +
                                                '<input data-tax="{{tax}}" data-name="{{name}}" class="facet_value" type="checkbox" />' +
                                            '{{/checked}}' +
                                            ' {{name}} ({{count}})' +
                                        '</div>' +
                                    '{{/disjunctive}}' +
                                '{{/sub_facets}}' +
                            '</div>' +
                        '</div>' +
                        '{{/count}}' +
                    '{{/facets}}' +
                '</div>'
            );

            var conjunctive_facets = [];
            var disjunctive_facets = [];

            for (var i = 0; i < algoliaSettings.facets.length; i++)
                if (algoliaSettings.facets[i].type == "conjunctive")
                    conjunctive_facets.push(algoliaSettings.facets[i].tax);
                else
                    disjunctive_facets.push(algoliaSettings.facets[i].tax);

            var helper = new AlgoliaSearchHelper(algolia_client, algoliaSettings.index_name, {
                facets: conjunctive_facets,
                disjunctiveFacets: disjunctive_facets,
                hitsPerPage: 3
            });

            function searchCallback(success, content) {
                algolia_div.html("");

                if (success)
                {
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
                                        isjunctive: 0,
                                        checked: checked,
                                        name: key,
                                        count: content.facets[algoliaSettings.facets[i].tax][key]
                                    });
                                }
                            }
                            else
                            {
                                for (var key in content.disjunctiveFacets[algoliaSettings.facets[i].tax])
                                {
                                    var checked = helper.isRefined(algoliaSettings.facets[i].tax, key);
                                    sub_facets.push({ conjunctive: 0, disjunctive: 1, checked: checked, name: key, count: content.disjunctiveFacets[algoliaSettings.facets[i].tax][key] });
                                }
                            }

                            facets.push({count: sub_facets.length, tax: algoliaSettings.facets[i].tax, facet_categorie_name: algoliaSettings.facets[i].name, sub_facets: sub_facets });
                        }

                        algolia_div.append(facetsTemplate.render({ facets: facets }));

                        algolia_div.append(template.render({ hits: content.hits }));
                    }
                }
            }

            $(algoliaSettings.instant_jquery_selector).after("<div style='display: none; min-height: 600px;' id='" + algolia_div_id + "'></div>");

            var algolia_div = $("#" + algolia_div_id);

            function performQueries()
            {
                helper.search(query, searchCallback);

                algolia_div.show();
            }

            var query = "";

            $("input[name='s']").keyup(function (e) {
                var keycode = e.keyCode;

                var valid =
                    (keycode > 47 && keycode < 58) || // number keys
                    keycode == 32 || keycode == 13 || // spacebar & return key(s) (if you want to allow carriage returns)
                    (keycode > 64 && keycode < 91) || // letter keys
                    (keycode > 95 && keycode < 112)  //|| // numpad keys
                //      (keycode > 185 && keycode < 193) || // ;=,-./` (in order)
                //        (keycode > 218 && keycode < 223);

                if ($(this).val().length == 0)
                {
                    $(algoliaSettings.instant_jquery_selector).show();
                    algolia_div.hide();
                    console.log("ok");
                    return;
                }

                $(algoliaSettings.instant_jquery_selector).hide();

                if (valid == false)
                    return;

                query = $(this).val();

                performQueries();
            });

            $("body").on("click", ".sub_facet", function () {
               $(this).find("input[type='checkbox']").each(function (i) {
                   $(this).prop("checked", ! $(this).prop("checked"));
                   helper.toggleRefine($(this).attr("data-tax"), $(this).attr("data-name"));
               });

                performQueries();
            });
        }
        else
        {
            console.log("Bad selector for instant search");
        }
    });
}