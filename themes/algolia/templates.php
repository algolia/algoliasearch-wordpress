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
    <div class="hits">
        {{#hits}}
        <div class="post">
            <h1><a href="{{permalink}}">{{{ _highlightResult.title.value }}}</a></h1>

            <div class="media postmetadata">
                <a href="" class="pull-left">
                    <img class="author" src="//d3ibatyzauff7b.cloudfront.net/assets/about-{{author_login}}.jpg">
                </a>
                <div class="media-body text-muted">
                    Posted on
                    {{#getDate}}{{date}}{{/getDate}}<br>
                    Written by <strong>{{{_highlightResult.author.value}}}</strong>
                </div>
            </div>

            <div class="entry">
                <p>{{{ _snippetResult.content.value }}}</p>
                <a href="{{permalink}}" class="more-link">Continue reading…</a>
            </div>
        </div>
        {{/hits}}
        {{^hits.length}}
        <div class="no-result">
            No results
        </div>
        {{/hits.length}}
        </div>
    <div style="clear: both;"></div>
</script>

<script type="text/template" id="instant-facets-template">
<div class="facets">
    {{#facets}}
    {{#count}}
    <div class="facet">
        <div class="name">
            {{ facet_categorie_name }}
            </div>
        <div>
            {{#sub_facets}}

            {{#conjunctive}}
            <div class="{{#checked}}checked {{/checked}}sub_facet conjunctive">
                <input style="display: none;" data-tax="{{tax}}" {{#checked}}checked{{/checked}} data-name="{{nameattr}}" class="facet_value" type="checkbox" />
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
            <div class="{{#checked}}checked {{/checked}}sub_facet disjunctive">
                <input data-tax="{{tax}}" {{#checked}}checked{{/checked}} data-name="{{nameattr}}" class="facet_value" type="checkbox" />
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