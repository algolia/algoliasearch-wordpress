(function ($, algoliasearch, wp) {
    'use strict';

    $(function () {

        var footerTmpl = wp.template(algolia.autocomplete.tmpl_footer);

        // Implement an empty template once helped obtained here: footerTmpl
        // var emptyTmpl = wp.template('autocomplete-empty');

        var client = algoliasearch(algolia.application_id, algolia.search_api_key);

        var sources = [];
        for(var index in algolia.autocomplete.sources) {
            var config = algolia.autocomplete.sources[index];
            sources.push({
                source: sourceCallback(client.initIndex(config['index_name']), config['max_suggestions']),
                templates: {
                    header: function (label, template) {
                        return function() {
                            return wp.template(template)({
                                label: label
                            });
                        }
                    }(config['label'], config['tmpl_header']),
                    suggestion: function(template) {
                        return wp.template(template);
                    }(config['tmpl_suggestion'])
                }
            });
        }


        function sourceCallback( index, max_suggestions ) {
            return function (q, cb) {
                index.search(
                    q,
                    {
                        hitsPerPage: max_suggestions,
                        attributesToSnippet: [
                            'content:10',
                            'title1:10',
                            'title2:10',
                            'title3:10',
                            'title4:10',
                            'title5:10',
                            'title6:10'
                        ]
                    },
                    function (error, content) {
                        if (error) {
                            cb([]);
                            return;
                        }
                        cb(content.hits, content);
                    });
            }
        }

        // Make this come from config as well.
        var searchInputSelector = "input[name='s']";
        var searchInput = $(searchInputSelector);

        // Leverage the autocomplete power.
        autocomplete(searchInputSelector,
            {
                debug: algolia.debug,
                hint: false,
                openOnFocus: true,
                templates: {
                    footer: footerTmpl
                }
            },
            sources
        ).on('autocomplete:selected', function(e, suggestion, datasetName) {
            // Redirect the user when we detect a suggestion selection.
            window.location.href = suggestion.permalink;
        });


        // This ensures that when the dropdown overflows the window, Thether can reposition it.
        $('body').css('overflow-x', 'hidden');

        searchInput.each(function(index) {
            var $item = $(this);
            var $autocomplete = $item.parent();

            // Remove autocomplete.js default inline input search styles.
            $autocomplete.removeAttr('style');

            var $menu = $autocomplete.find('.aa-dropdown-menu');
            var config = {
                element: $menu,
                target: this,
                attachment: 'top left',
                targetAttachment: 'bottom left',
                constraints: [
                    {
                        to: 'window',
                        attachment: 'none element'
                    }
                ]
            };

            // This will make sure the dropdown is no longer part of the same container as
            // the search input container.
            // It ensures styles are not overridden and limits theme breaking.
            var tether = new Tether(config);
            tether.on('update', function(item) {
                // todo: fix the inverse of this: https://github.com/HubSpot/tether/issues/182
                if (item.attachment.left == 'right' && item.attachment.top == 'top' && item.targetAttachment.left == 'left' && item.targetAttachment.top == 'bottom') {
                        config.attachment = 'top right';
                        config.targetAttachment = 'bottom right';

                        tether.setOptions(config, false);
                }
            });

            searchInput.on('autocomplete:updated', function() {
                tether.position();
            });

            searchInput.on('autocomplete:opened', function() {
                updateDropdownWidth();
            });


            // Trick to ensure the autocomplete is always above all.
            $menu.css('z-index', '99999');

            var dropdownMinWidth = 280;

            // Makes dropdown match the input size.
            function updateDropdownWidth() {
                var inputWidth = $item.outerWidth();
                if (inputWidth >= dropdownMinWidth) {
                    $menu.css('width', $item.outerWidth());
                } else {
                    $menu.css('width', dropdownMinWidth);
                }
                tether.position();
            }
            $(window).on('resize', updateDropdownWidth);
        } );

        $(document).on("click", ".algolia-powered-by-link", function(e) {
            e.preventDefault();
            window.location = "https://www.algolia.com/?utm_source=WordPress&utm_medium=extension&utm_content=" + window.location.hostname + "&utm_campaign=poweredby";
        })
    });

})(jQuery, algoliasearch, wp);
