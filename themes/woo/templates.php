<script type="text/template" id="autocomplete-template">
    <div class="result">
        <div class="title">
            {{#featureImage}}
            <img style="width: 30px" src="{{{ featureImage.sizes.thumbnail.file }}}" />
            {{/featureImage}}
            {{{ _highlightResult.title.value }}}
        </div>
    </div>
</script>

<script type="text/template" id="instant-content-template">
    <div class="hits{{#facets_count}} with_facets{{/facets_count}}">
        {{#hits.length}}
        <div class="infos">
            <div style="float: left">
                {{nbHits}} result{{^nbHits_one}}s{{/nbHits_one}} found matching "<strong>{{query}}</strong>" in {{processingTimeMS}} ms
            </div>
            <div class="logo" style="float: right;">
                by <img src="<?php echo plugin_dir_url(__FILE__); ?>../../front/algolia-logo.png">
            </div>
            {{#sorting_indexes.length}}
            <div style="float: right; margin-right: 10px;">
                Order by
                <select id="index_to_use">
                    <option {{#sortSelected}}{{relevance_index_name}}{{/sortSelected}} value="{{relevance_index_name}}">relevance</option>
                    {{#sorting_indexes}}
                    <option {{#sortSelected}}{{index_name}}{{/sortSelected}} value="{{index_name}}">{{label}}</option>
                    {{/sorting_indexes}}
                </select>
            </div>
            {{/sorting_indexes.length}}
            <div style="clear: both;"></div>
        </div>
        {{/hits.length}}

        {{#hits}}
        <a href="{{permalink}}">
            <div class="result-wrapper">
                <div class="result">
                    <div class="result-content">
                        <div>
                            <h1 class="result-title">
                                {{{ _highlightResult.title.value }}}
                            </h1>
                        </div>
                        <div class="result-sub-content">
                            <div class="result-thumbnail">
                            {{#featureImage}}
                                <img height="216" src="{{{ featureImage.file }}}" />
                            {{/featureImage}}
                            {{^featureImage}}
                            <div style="height: 216px;"></div>
                            {{/featureImage}}
                            </div>
                            <div class="result-excerpt">
                                <div class="price">Price : {{_price}}€</div>
                                <div class="rating">Rating : {{average_rating}}/5</div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </a>
        {{/hits}}
        {{^hits.length}}
        <div class="infos">
            No results found matching "<strong>{{query}}</strong>".
        </div>
        {{/hits.length}}
    </div>
    <div style="clear: both;"></div>
</script>

<script type="text/template" id="instant-facets-template">
<div class="facets{{#count}} with_facets{{/count}}">
    {{#facets}}
    {{#count}}
    <div class="facet">
        <div class="name">
            {{ facet_categorie_name }}
            </div>
        <div>
            {{#sub_facets}}

                {{#conjunctive}}

                    {{#checked}}
                        <div class="checked sub_facet conjunctive">
                    {{/checked}}

                    {{^checked}}
                        <div class="sub_facet conjunctive">
                    {{/checked}}

                    {{#checked}}
                            <input style="display: none;" data-tax="{{tax}}" checked data-name="{{name}}" class="facet_value" type="checkbox" />
                    {{/checked}}

                    {{^checked}}
                            <input style="display: none;" data-tax="{{tax}}" data-name="{{name}}" class="facet_value" type="checkbox" />
                    {{/checked}}

                            {{name}} ({{count}})
                        </div>
                {{/conjunctive}}

                {{#slider}}
                <div class="algolia-slider algolia-slider-true" data-tax="{{tax}}" data-min="{{min}}" data-max="{{max}}"></div>
                <div class="algolia-slider-info">
                    <div class="min" style="float: left;">{{current_min}}</div>
                    <div class="max" style="float: right;">{{current_max}}</div>
                    <div style="clear: both"></div>
                    </div>
                {{/slider}}

                {{#disjunctive}}

                    {{#checked}}
                        <div class="checked sub_facet disjunctive">
                    {{/checked}}

                    {{^checked}}
                        <div class="sub_facet disjunctive">
                    {{/checked}}

                    {{#checked}}
                            <input data-tax="{{tax}}" checked data-name="{{name}}" class="facet_value" type="checkbox" />
                    {{/checked}}

                    {{^checked}}
                            <input data-tax="{{tax}}" data-name="{{name}}" class="facet_value" type="checkbox" />
                    {{/checked}}

                         {{name}} ({{count}})
                        </div>
                {{/disjunctive}}
            {{/sub_facets}}
        </div>
    </div>
    {{/count}}
    {{/facets}}
</div>
</script>

<script type="text/template" id="instant-pagination-template">
<div class="pagination-wrapper">
    <div class="text-center">
        <ul class="algolia-pagination">
            <li {{^prev_page}}class="disabled"{{/prev_page}}>
                <a href="#" onclick="{{#prev_page}}gotoPage({{ prev_page }});{{/prev_page}} return false;">&laquo;</a>
            </li>

            {{#pages}}
            <li class="{{#current}}active{{/current}}{{#disabled}}disabled{{/disabled}}">
                <a href="#" onclick="{{^disabled}}gotoPage({{ number }});{{/disabled}} return false;">
                    {{ number }}
                </a>
            </li>
            {{/pages}}

            <li {{^next_page}}class="disabled"{{/next_page}}>
                <a href="#" onclick="{{#next_page}}gotoPage({{ next_page }});{{/next_page}} return false;">&raquo;</a>
            </li>
        </ul>
    </div>
</div>
</script>