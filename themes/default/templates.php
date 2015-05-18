<script type="text/template" id="autocomplete-template">
    <div class="result">
        <div class="title">
            {{#featureImage}}
            <div class="thumb">
                <img style="width: 30px" src="{{{ featureImage.sizes.thumbnail.file }}}" />
            </div>
            {{/featureImage}}
            <div class="info{{^featureImage}}-without-thumb{{/featureImage}}">
                {{{ _highlightResult.title.value }}}
            </div>
            <div style="clear: both;"></div>
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
            <div style="clear: both;"></div>
        </div>
        {{/hits.length}}

        {{#hits}}
        <div class="result">
            <div class="result-content">
                <div>
                    <h1 class="result-title">
                        <a href="{{permalink}}">
                            {{{ _highlightResult.title.value }}}
                        </a>
                    </h1>
                </div>
                <div class="result-sub-content">
                    {{#featureImage}}
                    <div class="result-thumbnail">
                        <img src="{{{ featureImage.file }}}" />
                    </div>
                    {{/featureImage}}
                    <div class="result-excerpt">
                        <div>{{{ _highlightResult.content.value }}}</div>
                        <div>
                            <a href="{{permalink}}" class="more-link">Continue reading…</a>
                        </div>
                    </div>
                </div>
            </div>
        </div>
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

                {{#type.menu}}
                <div class="{{#checked}}checked {{/checked}}sub_facet conjunctive">
                    <input style="display: none;" data-tax="{{tax}}" {{#checked}}checked{{/checked}} data-name="{{nameattr}}" class="facet_value" type="checkbox" />
                    {{name}} ({{count}})
                </div>
                {{/type.menu}}

                {{#type.conjunctive}}
                <div class="{{#checked}}checked {{/checked}}sub_facet conjunctive">
                    <input style="display: none;" data-tax="{{tax}}" {{#checked}}checked{{/checked}} data-name="{{nameattr}}" class="facet_value" type="checkbox" />
                    {{name}} ({{count}})
                </div>
                {{/type.conjunctive}}

                {{#type.slider}}
                <div class="algolia-slider algolia-slider-true" data-tax="{{tax}}" data-min="{{min}}" data-max="{{max}}"></div>
                <div class="algolia-slider-info">
                    <div class="min" style="float: left;">{{current_min}}</div>
                    <div class="max" style="float: right;">{{current_max}}</div>
                    <div style="clear: both"></div>
                </div>
                {{/type.slider}}

                {{#type.disjunctive}}
                <div class="{{#checked}}checked {{/checked}}sub_facet disjunctive">
                    <input data-tax="{{tax}}" {{#checked}}checked{{/checked}} data-name="{{nameattr}}" class="facet_value" type="checkbox" />
                    {{name}} ({{count}})
                </div>
                {{/type.disjunctive}}

            {{/sub_facets}}
        </div>
    </div>
    {{/count}}
    {{/facets}}
</div>
</script>

<script type="text/template" id="instant-pagination-template">
    <div class="pagination-wrapper{{#facets_count}} with_facets{{/facets_count}}">
        <div class="text-center">
            <ul class="algolia-pagination">
                <li {{^prev_page}}class="disabled"{{/prev_page}}>
                <a href="#" data-page="{{prev_page}}">
                    &laquo;
                </a>
                </li>

                {{#pages}}
                <a href="#" data-page="{{number}}" return false;">
                <li class="{{#current}}active{{/current}}{{#disabled}}disabled{{/disabled}}">
                    {{ number }}
                </li>
                </a>
                {{/pages}}

                <li {{^next_page}}class="disabled"{{/next_page}}>
                <a href="#" data-page="{{next_page}}">&raquo;</a>
                </li>
            </ul>
        </div>
    </div>
</script>