<?php

$facets = $this->buildSettings();
$facets = $facets['facets'];

?>

<!--
//================================
//
// Multi-category Autocomplete
//
//================================
-->

<script type="text/template" id="autocomplete-template">
    <div class="result">
        <div class="title">
            {{#featureImage}}
            <div class="thumb">
                <img style="width: 30px" src="{{{ featureImage.sizes.thumbnail.file }}}" />
            </div>
            {{/featureImage}}
            <div class="info{{^featureImage}}-without-thumb{{/featureImage}}">
                {{{ _highlightResult.post_title.value }}}
            </div>
            <div style="clear: both;"></div>
        </div>
    </div>
</script>


<!--
//================================
//
// Instant search results page
//
//================================
-->

<!-- Wrapping template -->
<?php $s = isset($_GET['s']) ? $_GET['s'] : "" ?>
<script type="text/template" id="instant_wrapper_template">

    <div id="algolia_instant_selector"<?php echo count($facets) > 0 ? ' class="with-facets"' : '' ?>>

        <div class="row">
            <div class="col-md-offset-3 col-md-9">
                <div>
                    {{#second_bar}}
                    <div id="instant-search-bar-container">
                        <div id="instant-search-box">
                            <label for="instant-search-bar">
                                Search
                            </label>
                            <input value="<?php echo $s; ?>" placeholder="Search for products" id="instant-search-bar" type="text" autocomplete="off" spellcheck="false" autocorrect="off" autocapitalize="off" />
                            <svg xmlns="http://www.w3.org/2000/svg" class="magnifying-glass" width="24" height="24" viewBox="0 0 128 128">
                                <g transform="scale(4)">
                                    <path stroke-width="3" d="M19.5 19.582l9.438 9.438"></path>
                                    <circle stroke-width="3" cx="12" cy="12" r="10.5" fill="none"></circle>
                                    <path d="M23.646 20.354l-3.293 3.293c-.195.195-.195.512 0 .707l7.293 7.293c.195.195.512.195.707 0l3.293-3.293c.195-.195.195-.512 0-.707l-7.293-7.293c-.195-.195-.512-.195-.707 0z"></path>
                                </g>
                            </svg>
                        </div>
                    </div>
                    {{/second_bar}}
                </div>
            </div>
        </div>

        <div class="row">
            <div class="col-md-3" id="algolia-left-container">
                <div id="refine-toggle" class="visible-xs visible-sm">Refine</div>
                <div class="hidden-xs hidden-sm" id="instant-search-facets-container"></div>
            </div>

            <div class="col-md-9" id="algolia-right-container">
                <div class="row">
                    <div>
                        <div class="hits">
                            <div class="infos">
                                <div class="pull-left" id="algolia-stats"></div>
                                <div class="pull-right" id="algolia-sorts"></div>
                                <div class="clearfix"></div>
                            </div>
                            <div id="instant-search-results-container"
                        </div>
                    </div>
                    <div class="clearfix"></div>
                </div>

                <div class="text-center">
                    <div id="instant-search-pagination-container"></div>
                </div>
            </div>
        </div>

    </div>
</script>

<script type="text/template" id="instant-hit-template">
    <div class="result">
        <div class="result-content">
            <div class="result-thumbnail">
                {{#featureImage}}
                    <img src="{{{ featureImage.file }}}" />
                {{/featureImage}}
            </div>
            <div class="result-sub-content">
                <div class="result-wrapper">
                    <h1 class="result-title">
                        <a href="{{permalink}}">
                            {{{ _highlightResult.post_title.value }}}
                        </a>
                    </h1>

                    <div class="result-excerpt">
                        <div class="content">{{{ _highlightResult.post_content.value }}}</div>
                        <div>
                            <a href="{{permalink}}" class="more-link">Continue reading…</a>
                        </div>
                    </div>
                </div>
            </div>
            <div class="clearfix"></div>
        </div>
    </div>
</script>


<script type="text/template" id="instant-stats-template">
    {{#hasNoResults}}No results'){{/hasNoResults}}
    {{#hasOneResult}}1 result{{/hasOneResult}}
    {{#hasManyResults}}{{#helpers.formatNumber}}{{nbHits}}{{/helpers.formatNumber}} results{{/hasManyResults}}
    {{#query}}matching "<strong>{{query}}</strong>" {{/query}}
    in {{processingTimeMS}}ms ?>
</script>

<script type="text/template" id="facet-template">
    <div class="sub_facet {{operator}} {{#isRefined}}checked{{/isRefined}}">
        <input class="facet_value" {{#isRefined}}checked{{/isRefined}} type="checkbox">
        {{name}}
        <span class="count">{{count}}</span>
    </div>
</script>