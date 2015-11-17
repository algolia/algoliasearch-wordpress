
var selectTab;

algoliaBundle.$(document).ready(function($) {
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
        var url = algoliaAdminSettings.site_url + "/wp-admin/admin-post.php";
        var value = $(this).attr('data-value');

        $(this).parent().append('<iframe id="createdIframe"></iframe>');

        $(this).parent().find('#createdIframe').hide();

        $(this).parent().find('#createdIframe').contents().find('html').html('<form id="to-submit" action="' + url + '" method="post" style="display:none;">' +
                '<input type="text" name="action" value="' + value+ '" />' +
            '</form>'
        );

        $(this).parent().find('#createdIframe').contents().find('#to-submit').submit();
    });

    /**
     * Handle Indexing Count
     */


    /*function countNumberOfRecords()
    {
        var autocomplete_count  = 0;
        var instant_count       = 0;

        $('#_indexable-types tr:not(:first)').each(function () {
            var tds = $(this).find('td');
            var type = $(tds[0]).find('input[type=checkbox]').attr('value');

            if ($(tds[0]).find('input[type=checkbox]').prop('checked'))
                autocomplete_count += parseInt(algoliaAdminSettings.types[type].count);

            if ($(tds[1]).find('input[type=checkbox]').prop('checked'))
                instant_count += parseInt(algoliaAdminSettings.types[type].count);
        });

        var count = autocomplete_count + instant_count;

        $('#_sortable_attributes tr:not(:first)').each(function () {
            if ($(this).find("td:first input[type=checkbox]").prop('checked')) {
                count += instant_count;
            }
        });

        $('#extra-meta-and-taxonomies tr:not(:first)').each(function () {
            var tds = $(this).find('td');

            var tax = $(tds[0]).find('input[type=checkbox]').attr('value');

            if (tax == undefined)
                return;

            if ($(tds[3]).find("input[type=checkbox]").prop('checked')) {
                count += parseInt(algoliaAdminSettings.taxonomies[tax].count);
            }
        });

        $('#algolia_reindex .record-count').html("(" + count + " records)");
    }

    countNumberOfRecords();

    $('#algolia-settings input').change(function () {
        countNumberOfRecords();
    });*/

    /**
     * Handle Async Indexation
     */



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
        $("#algolia_reindex").removeClass('button-primary').addClass('button-secondary').html('<i class="dashicons dashicons-upload"></i> Reindex data <span class="record-count"></span>').show();
        $('#algolia_reindex_hint').hide();
        countNumberOfRecords();
    });

    $("#algolia_reindex").click(function (e) {
        var base_url    = algoliaAdminSettings.site_url + '/wp-admin/admin-post.php';
        var actions     = [];
        var batch_count = algoliaAdminSettings.batch_count;

        $("#results-wrapper").show();
        $("#reindex-log").html("");

        $(this).hide();

        actions.push({ subaction: "saveSettings", name: "Save settings", sup: "" })

        actions.push({ subaction: "handle_index_creation", name: "Setup indices", sup: "" });

        for (value in algoliaAdminSettings.types)
        {
            var number = Math.ceil(algoliaAdminSettings.types[value].count / batch_count);
            for (var i = 0; i < number; i++)
            {
                actions.push({
                    name: "Upload " + algoliaAdminSettings.types[value].type,
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


Array.prototype.unique = function(){
    var u = {}, a = [];
    for(var i = 0, l = this.length; i < l; ++i){
        if(u.hasOwnProperty(this[i])) {
            continue;
        }
        a.push(this[i]);
        u[this[i]] = 1;
    }
    return a;
};

algoliaBundle.$(document).ready(function ($) {
    var iframe = $('#instant_zone_grabber');

    $('#instant_jquery_selector_helper').click(function () {
        $('#instant_jquery_selector_helper_wrapper').show();
        $('#algolia-settings').hide();
    });



    iframe.on('load', function () {
        $('#possible_divs').html('');// reset possible input on link click
        var selector = $('#instant_zone_grabber').contents();
        selector.find('body').append('<style>.algolia-potential-div{border: solid 4px red !important;}</style>');

        var selectors = [];

        var loadChilds = function (div) {
            var childs = div.children();

            var myChilds = [];

            for (var i = 0; i < childs.length; i++)
            {
                var child_selector = $(childs[i]).attr('id');

                if (child_selector !== undefined) {
                    child_selector = "#" + child_selector;
                }
                else if (child_selector === undefined) {
                    child_selector = $(childs[i]).attr('class');

                    if (child_selector !== undefined)
                        child_selector = '.' + child_selector.split(' ').join('.');
                }

                var child = {};

                child.selector = child_selector;
                child.children = loadChilds($(childs[i]));

                if (child.selector == undefined && child.children.length === 0)
                    continue;

                if (child.selector !== undefined)
                    selectors.push(child.selector);

                child.print = function () {
                    var c = '';
                    for (var i = 0; i < this.children.length; i++)
                        c += this.children[i].print();

                    var s = '<div>' +
                                (this.selector ? '<div class="possible-div">' + this.selector + '</div>' : '') +
                                '<div style="margin-left: 4px;">' + c + '</div>' +
                            '</div>';

                    return s;
                };


                myChilds.push(child);
            }

            return myChilds;
        };

        var divs = loadChilds($(selector.find('body')));

        for (var i = 0; i < divs.length; i++)
            $('#possible_divs').append(divs[i].print());

        $(document).on('mouseenter', '.possible-div', function () {
            var potential_divs = selector.find($(this).html());

            console.log('enter ' + $(this).html(), potential_divs);
            potential_divs.addClass('algolia-potential-div');
        });

        $(document).on('mouseleave', '.possible-div', function () {
            var potential_divs = selector.find($(this).html());

            console.log('leave ' + $(this).html(), potential_divs);
            potential_divs.removeClass('algolia-potential-div');
        });

        $(document).on('click', '.possible-div', function () {
            var appElement = document.querySelector('[ng-app=algoliaSettings]');
            var $scope = angular.element(appElement).scope();

            var selector = $(this).html();

            $scope.$apply(function() {
                $scope.instant_jquery_selector = selector;
                $('#instant_jquery_selector_helper_wrapper').hide();
                $('#algolia-settings').show();
            });
        });
    });

    iframe.attr('src', algoliaAdminSettings.site_url);
});

algoliaBundle.$(document).ready(function ($) {
    var iframe = $('#search_input_grabber');

    $('#search_input_selector_helper').click(function () {
        $('#search_input_selector_helper_wrapper').show();
    });

    iframe.on('load', function () {
        $('#possible_inputs').html('');// reset possible input on link click
        var selector = $('#search_input_grabber').contents();
        selector.find('body').append('<style>.algolia-potential-input{border: solid 4px red !important;}</style>');

        var inputs = selector.find('input');

        var attributes = ['id', 'name', 'class', 'type'];
        var selectors = [];

        inputs.each(function () {

            for (var i = 0; i < attributes.length; i++)
            {
                var selector = '';

                var values = $(this).attr(attributes[i]);

                var type = $(this).attr('type');

                if (type !== undefined && (type === 'hidden' || type === 'submit'))
                    continue;

                if (values === undefined)
                    continue;

                var values = values.split(' ');

                $.each(values, function (index, value) {
                    selector += "[" + attributes[i] + "='" + value + "']"
                });

                selectors.push(selector);
            }
        });

        selectors = selectors.unique();

        for (var i = 0; i < selectors.length; i++)
            $('#possible_inputs').append('<div class="possible-attribute">' + selectors[i] + '</div>');

        var old_content = $('#search_input_grabber').contents().find('body').html();

        $(document).on('mouseenter', '.possible-attribute', function () {
            var potential_inputs = $('#search_input_grabber').contents().find($(this).html());

            potential_inputs.addClass('algolia-potential-input');
        });

        $(document).on('mouseleave', '.possible-attribute', function () {
            var potential_inputs = $('#search_input_grabber').contents().find($(this).html());
            potential_inputs.removeClass('algolia-potential-input');
        });

        $(document).on('click', '.possible-attribute', function () {
            var appElement = document.querySelector('[ng-app=algoliaSettings]');
            var $scope = angular.element(appElement).scope();

            var selector = $(this).html();

            $scope.$apply(function() {
                $scope.search_input_selector = selector;
                $('#search_input_selector_helper_wrapper').hide();
            });
        });
    });

    iframe.attr('src', algoliaAdminSettings.site_url);

});