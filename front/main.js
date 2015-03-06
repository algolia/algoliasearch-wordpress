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
                '{{/hits}}'
            );

            var articleTemplate = Hogan.compile(template);

            function searchMultiCallback(success, content) {
                algolia_div.html("");

                if (success) {
                    console.log(content);

                    algolia_div.append(template.render(content));

                    for (var i = 0; i < content.results.length; i++)
                    {
                        if (content.results[i].hits.length > 0)
                        {
                            algolia_div.append('<div>' + algoliaSettings.indexes[i].name + '</div>')

                            for (var j = 0; j < content.results[i].hits.length; j++)
                            {
                                algolia_div.append(template.render({ hits: content.results[i].hits[j] }));
                            }
                        }
                    }
                }
            }

            $(algoliaSettings.instant_jquery_selector).after("<div style='display: none; min-height: 600px;' id='" + algolia_div_id + "'></div>");

            var algolia_div = $("#" + algolia_div_id);

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

                algolia_client.startQueriesBatch();

                var query = $(this).val();

                for (var i = 0; i < algoliaSettings.indexes.length; i++)
                    algolia_client.addQueryInBatch(algoliaSettings.indexes[i].index_name, query, { facets: '*', hitsPerPage: 3 });

                algolia_client.sendQueriesBatch(searchMultiCallback);

                algolia_div.show();

            });
        }
        else
        {
            console.log("Bad selector for instant search");
        }
    });
}