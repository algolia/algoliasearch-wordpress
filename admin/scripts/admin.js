var selectTab;

jQuery(document).ready(function($) {
    /**
     * Handle display/hide of subcontent
     */
    $(".has-extra-content input[type='radio']").each(function () {
            if ($(this).is(':checked'))
                $(this).closest(".has-extra-content").find(".show-hide").show();
    });

    $(".has-extra-content input[type='radio']").change(function (e) {
        $(".has-extra-content input[type='radio']").each(function () {
            if ($(this).is(':checked'))
                $(this).closest(".has-extra-content").find(".show-hide").show();
            else
                $(this).closest(".has-extra-content").find(".show-hide").hide();
        });
    });

    selectTab = function(hash)
    {
        $(".tab-content").hide();
        $(hash).show();
        $(".tabs .title").removeClass("selected");
        $("[data-tab='"+ hash +"']").addClass("selected");

        window.location.hash = hash;

        $(window).scrollTop(0);
    }

    $(".tabs .title").click(function () {
        var hash = $(this).attr("data-tab");

        selectTab(hash);
    });

    var hash = $(".tabs .title.selected").attr("data-tab");

    if (window.location.hash != "")
        hash = window.location.hash;

    selectTab(hash);

    /**
     * Handle Theme chooser
     */

    $('#algolia-settings .theme').click(function () {
        $('#algolia-settings .theme').removeClass('active');
        $(this).addClass('active');
    });

    /**
     * Handle Sorting
     */

    var fixHelper = function(e, ui) {
        ui.children().each(function() {
            $(this).width($(this).width());
        });
        return ui;
    };

    $('#taxonomies tr, #extra-metas tr, #indexable-types tr, #custom-ranking tr').sort(function (a, b) {
        var contentA = parseInt($(a).attr('data-order'));
        var contentB = parseInt($(b).attr('data-order'));

        return (contentA < contentB) ? -1 : (contentA > contentB) ? 1 : 0;
    }).each(function (_, container) {
        $(container).parent().append(container);
    });;

    $("#taxonomies tbody, #extra-metas tbody, #indexable-types tbody, #custom-ranking tbody").sortable({
        containment: "parent",
        helper: fixHelper
    }).disableSelection();


    /**
     * Handle Async Indexation
     */

    $(document).ready(function () {

        function renderPercentage(percent)
        {
            return "<div style='float: left; width: 300px; height: 20px; border: solid 1px #dddddd;'>" +
                "<div style='width: " + percent + "%; height: 20px; background-color: rgba(42, 148, 0, 0.6);'></div>" +
                "</div>" +
                "<div style='float: left; margin-left: 20px'>" + percent + "%</div>"
        }

        function render(actions, i)
        {
            var percentage = Math.ceil(i * 100 / actions.length);
            if (i == -1)
                percentage = 0;

            $("#reindex-percentage").html(renderPercentage(percentage));

            if (i == -1)
                return;

            $("#reindex-log").append(
                "<tr>" +
                "<td>" + actions[i].name + " " + actions[i].sup + "<td>" +
                "<td>[OK]</td>" +
                "</tr>");
        }

        $("body").on("click", ".close-results", function () {
            $("#results-wrapper").hide();
            $(this).hide();
        });

        $("#algolia_reindex").click(function (e) {
            var base_url    = '/wp-admin/admin-post.php';
            var actions     = [];
            var batch_count = algoliaAdminSettings.batch_count;

            $("#results-wrapper").show();
            $("#reindex-log").html("");

            actions.push({ subaction: "handle_index_creation", name: "Handle index creation", sup: "" });

            for (value in algoliaAdminSettings.types)
            {
                var number = Math.ceil(algoliaAdminSettings.types[value].count / batch_count);
                for (var i = 0; i < number; i++)
                {
                    actions.push({
                        name: algoliaAdminSettings.types[value].name,
                        subaction: "type__" + algoliaAdminSettings.types[value].type + "__" + i,
                        sup: (i + 1) + "/" + number
                    });
                }
            }

            actions.push({ subaction: "index_taxonomies", name: "Index taxonomies", sup: "" });

            actions.push({ subaction: "move_indexes", name: "Move all temp indexes", sup: "" });

            var i = 0;
            var call = function () {

                $.ajax({
                    method: "POST",
                    url: base_url,
                    data: { action: "reindex", subaction: actions[i].subaction },
                    success: function (result) {
                        render(actions, i);
                    },
                    async: false
                });

                if (i < actions.length - 1)
                {
                    i = i + 1;
                    setTimeout(call, 1);
                }
                else
                {
                    $("#reindex-percentage").html(renderPercentage(100));
                    $(".close-results").show();
                }
            };

            render(actions, -1);
            setTimeout(call, 1);

            console.log(actions);
            console.log(algoliaAdminSettings);

        });
    });
});