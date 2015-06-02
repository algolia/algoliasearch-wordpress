var selectTab;

jQuery(document).ready(function($) {
    /**
     * Handle display/hide of subcontent
     */
    $(".has-extra-content input[type='checkbox']").each(function () {
            if ($(this).is(':checked'))
                $(this).closest(".has-extra-content").find(".show-hide").show();
    });

    function handleScreenshot()
    {
        if ($('input[name="TYPE_OF_SEARCH"]:checked').val() == 'autocomplete')
        {
            $('.screenshot.autocomplete').show();
            $('.screenshot.instant').hide();
        }
        else
        {
            $('.screenshot.autocomplete').hide();
            $('.screenshot.instant').show();
        }
    }

    handleScreenshot();

    $(".has-extra-content input[type='checkbox']").change(function (e) {
        $(".has-extra-content input[type='checkbox']").each(function () {
            if ($(this).is(':checked'))
                $(this).closest(".has-extra-content").find(".show-hide").show();
            else
                $(this).closest(".has-extra-content").find(".show-hide").hide();
        });

        handleScreenshot();
    });

    if ($("#_custom-ranking tr").length > 1)
        $('#_custom-ranking .warning').hide();
    else
        $('#_custom-ranking .content-item').hide();

    /**
     * Handle Tab
     */
    selectTab = function(hash)
    {
        hash2 = "#_" + hash.substr(1);

        $(".tab-content").hide();
        $(hash2).show();
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
     * Handle sub Tab
     */

    selectSubTab = function(hash)
    {
        $(".sub-tab-content").hide();
        $(hash).show();
        $("#_extra-metas .title").removeClass("selected");
        $("[data-tab='"+ hash +"']").addClass("selected");
    };

    $("#_extra-metas .title").click(function () {
        var hash = $(this).attr("data-tab");

        selectSubTab(hash);
    });

    selectSubTab('#extra-metas-attributes');

    function reorderMetas()
    {
        $('#_extra-metas tr').each(function (i) {
            if ($(this).find('td:first input[type="checkbox"]').prop('checked') || $(this).find('td:first i').length > 0)
            {
                $('#extra-meta-and-taxonomies').append($(this));
            }
        });

        $('#extra-meta-and-taxonomies tr').each(function (i) {
            if ($(this).find('td:first input[type="checkbox"]').prop('checked') == false && $(this).find('td:first i').length <= 0)
            {
                if ($(this).attr('data-type') == 'taxonomy')
                    $('#taxonomies table tr:first').after($(this));
                else
                    $('#extra-metas-attributes table tr:first').after($(this));
            }
        });
    }

    $('#_extra-metas tr td:first-child input').click(function (e) {
        reorderMetas();
    });

    reorderMetas();

    $('#extra-metas-form').submit(function (e) {
        $('#_extra-metas tr').each(function (i) {
           $(this).find('.order').val(i);
        });
    });

    $('#sortable-form').submit(function (e) {
        $('#sortable-form tr').each(function (i) {
            $(this).find('.order').val(i);
        });
    });

    /**
     * Handle disabling
     */

    function disableInput(div)
    {
        $(div + " input, " + div + " select").prop('disabled', false);
        $(div + " tr:not(:first)").each(function (i) {
            var tds = $(this).find("td");

            if ($(tds[0]).find('input[type="checkbox"]').prop('checked') == false)
            {
                $(this).find("td").find("input,select").slice(1).prop('disabled', true);
            }
        });

        if (div == '#_extra-metas')
            disableFacetsInput('#extra-meta-and-taxonomies');
    }

    var disabelable = ['#_indexable-types', '#_extra-metas', '#_indexable-types', '#_searchable_attributes', '#_custom-ranking', '#_sortable_attributes'];

    for (var i = 0; i < disabelable.length; i++)
    {
        (function (i) {
            disableInput(disabelable[i]);

            $(disabelable[i] + " input[type='checkbox']").click(function () {
                disableInput(disabelable[i]);
            });
        })(i);

    }

    function disableFacetsInput(div)
    {
        $(div + " input, " + div + " select").prop('disabled', false);
        $(div + " tr:not(:first)").each(function (i) {
            var tds = $(this).find("td");

            if (($(tds[3]).find('input[type="checkbox"]').prop('checked') == undefined || $(tds[3]).find('input[type="checkbox"]').prop('checked') == false) && $(tds[4]).find('input[type="checkbox"]').prop('checked') == false)
                $(tds[6]).find("input,select").prop('disabled', true);

            if ($(tds[4]).find('input[type="checkbox"]').prop('checked') == false)
                $(tds[5]).find("input,select").prop('disabled', true);
        });
    }

    disableFacetsInput('#extra-meta-and-taxonomies');

    $('#extra-meta-and-taxonomies input[type="checkbox"]').click(function () {
        disableFacetsInput('#extra-meta-and-taxonomies');
    });

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


    $('#_taxonomies tr, #_extra-metas tr, #_indexable-types tr, #_custom-ranking tr, #_searchable_attributes tr, #_sortable_attributes tr').sort(function (a, b) {
        var contentA = parseInt($(a).attr('data-order'));
        var contentB = parseInt($(b).attr('data-order'));

        return (contentA < contentB) ? -1 : (contentA > contentB) ? 1 : 0;
    }).each(function (_, container) {
        $(container).parent().append(container);
    });


    $("#_taxonomies tbody, #_extra-metas tbody, #_indexable-types tbody, #_custom-ranking tbody, #_searchable_attributes tbody, #_sortable_attributes tbody").sortable({
        containment: "parent",
        items: 'tr:not(:first)',
        helper: fixHelper
    });


    /**
     * Handle Reset config to default
     */

    $('#reset-config').click(function (e) {
        var url = $(this).attr('data-form');
        var value = $(this).attr('data-value');

        if (window.confirm('Are you sure ? This will reset your config to default'))
        {
            $.ajax({
                method: "POST",
                url: url,
                data: { action: value },
                success: function () {
                    window.location.reload();
                }
            });
        }
    });

    $('.do-submit').click(function (e) {
        var url = $(this).attr('data-form');
        var value = $(this).attr('data-value');

        $(this).parent().append('<iframe id="createdIframe"></iframe>');

        $(this).parent().find('#createdIframe').hide();

        var iframedoc = $(this).parent().find('#createdIframe').contents().find('html').html('<form id="to-submit" action="' + url + '" method="post" style="display:none;">' +
                '<input type="text" name="action" value="' + value+ '" />' +
            '</form>'
        );

        $(this).parent().find('#createdIframe').contents().find('#to-submit').submit();
    });

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

        function render(action, i, n, result)
        {
            var percentage = Math.ceil(i * 100 / n);
            if (i == -1)
                percentage = 0;

            $("#reindex-percentage").html(renderPercentage(percentage));

            if (i == -1)
                return;

            $("#reindex-log").append(
                "<tr>" +
                "<td>" + action.name + " " + action.sup + "<td>" +
                "<td>[OK]</td>" +
                "<td>" + result + "</td>" +
                "</tr>");
        }

        $("body").on("click", ".close-results", function () {
            $("#results-wrapper").hide();
            $(this).hide();
            $("#algolia_reindex").show();
        });

        $("#algolia_reindex").click(function (e) {
            var base_url    = algoliaAdminSettings.site_url + '/wp-admin/admin-post.php';
            var actions     = [];
            var batch_count = algoliaAdminSettings.batch_count;

            $("#results-wrapper").show();
            $("#reindex-log").html("");

            $(this).hide();

            actions.push({ subaction: "handle_index_creation", name: "Setup indices", sup: "" });

            for (value in algoliaAdminSettings.types)
            {
                var number = Math.ceil(algoliaAdminSettings.types[value].count / batch_count);
                for (var i = 0; i < number; i++)
                {
                    actions.push({
                        name: "Upload " + algoliaAdminSettings.types[value].name,
                        subaction: "type__" + algoliaAdminSettings.types[value].type + "__" + i,
                        sup: (i === number - 1 ? algoliaAdminSettings.types[value].count : (i + 1) * algoliaAdminSettings.batch_count) + "/" + algoliaAdminSettings.types[value].count
                    });
                }
            }

            actions.push({ subaction: "index_taxonomies", name: "Upload taxonomies", sup: "" });

            actions.push({ subaction: "move_indexes", name: "Move indices to production", sup: "" });

            var call = function (i, n) {
                $.ajax({
                    method: "POST",
                    url: base_url,
                    data: { action: "reindex", subaction: actions[0].subaction },
                    success: function (result) {
                        render(actions[0], i + 1, n, result);

                        actions = actions.slice(1);

                        if (actions.length > 0)
                            call(i + 1, n);
                        else
                        {
                            $("#reindex-percentage").html(renderPercentage(100));
                            var date    = new Date();
                            var year    = date.getFullYear();
                            var month   = (date.getMonth() + 1);
                            var day     = date.getDate();
                            var hours   = date.getHours()   < 10 ? '0' + date.getHours()   : date.getHours();
                            var minutes = date.getMinutes() < 10 ? '0' + date.getMinutes() : date.getMinutes();
                            var seconds = date.getSeconds() < 10 ? '0' + date.getSeconds() : date.getSeconds();

                            $("#last-update").html("Last update : " + year + '-' + month + '-' + day + ' ' + hours + ':' + minutes + ':' + seconds);
                            $(".close-results").show();
                        }
                    }
                });
            };

            render(null, -1, actions.length);
            call(0, actions.length);
        });
    });
});