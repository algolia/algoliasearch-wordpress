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
        {{#hits.length}}
        <div class="infos">
            <div style="float: left">
                {{nbHits}} result{{^nbHits_one}}s{{/nbHits_one}} found matching "<strong>{{query}}</strong>" in {{processingTimeMS}} ms
            </div>
            <div style="float: right;">
                powered by <img src="<?php echo plugin_dir_url(__FILE__); ?>../../front/algolia-logo.png">
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
                        {{{ _snippetResult.content.value }}}
                        <a href="{{permalink}}" class="more-link">Continue reading…</a>
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