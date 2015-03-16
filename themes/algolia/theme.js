jQuery(document).ready(function ($) {
    /**
     * Bindings
     */

    $("body").on("click", ".sub_facet", function () {
        $(this).find("input[type='checkbox']").each(function (i) {
            $(this).prop("checked", !$(this).prop("checked"));
            engine.helper.toggleRefine($(this).attr("data-tax"), $(this).attr("data-name"));
        });

        engine.performQueries();
    });


    $("body").on("slide", "", function (event, ui) {
        engine.updateSlideInfos(ui);
    });

    $("body").on("slidechange", "", function (event, ui) {

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

        engine.updateSlideInfos(ui);
        engine.performQueries();
    });

    $(algoliaSettings.search_input_selector).keyup(function (e) {
        var $this = $(this);

        $(algoliaSettings.search_input_selector).each(function (i) {
            if ($(this)[0] != $this[0])
                $(this).val(engine.query);
        });

        if ($(this).val().length == 0) {
            location.replace('#');

            $(algoliaSettings.instant_jquery_selector).html(engine.old_content);

            return;
        }

        engine.helper.clearRefinements();
        engine.helper.clearNumericRefinements();

        engine.query = $(this).val();

        engine.performQueries();
    });

    window.finishRenderingResults = function()
    {
        $(".algolia-slider").each(function (i) {
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
});