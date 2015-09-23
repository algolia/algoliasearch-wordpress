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
<script type="text/template" id="instant_wrapper_template">

    <div id="algolia_instant_selector"<?php echo count($facets) > 0 ? ' class="with-facets"' : '' ?>>

        <div id="algolia-left-container">
            <div id="instant-search-facets-container"></div>
        </div>

        <div id="algolia-right-container">

            {{#second_bar}}
            <div id="instant-search-bar-container">
                <div id="instant-search-box">
                    <label for="instant-search-bar">
                        Search :
                    </label>

                    <?php $s = isset($_GET['s']) ? $_GET['s'] : "" ?>
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

            <div id="instant-search-results-container"></div>
            <div id="instant-search-pagination-container"></div>
        </div>
    </div>
</script>


<script type="text/template" id="instant-content-template">
    <div class="hits">
        {{#hits.length}}
        <div class="infos">
            <div style="float: left">
                {{nbHits}} result{{^nbHits_one}}s{{/nbHits_one}} found {{#query}}matching "<strong>{{query}}</strong>" {{/query}}in {{processingTimeMS}} ms
            </div>
            {{#sorting_indices.length}}
            <div style="float: right; margin-right: 10px;">
                Order by
                <select id="index_to_use">
                    <option {{#sortSelected}}{{relevance_index_name}}{{/sortSelected}} value="{{relevance_index_name}}">relevance</option>
                    {{#sorting_indices}}
                    <option {{#sortSelected}}{{index_name}}{{/sortSelected}} value="{{index_name}}">{{label}}</option>
                    {{/sorting_indices}}
                </select>
            </div>
            {{/sorting_indices.length}}
            <div class="clearfix"></div>
        </div>
        {{/hits.length}}

        {{#hits}}
        <div class="result-wrapper">
            <a href="{{permalink}}" class="result">
                <div class="result-content">
                    <div class="result-thumbnail">
                        {{#featureImage}}
                        <img src="{{{ featureImage.file }}}" />
                        {{/featureImage}}
                        {{^featureImage}}
                        <span class="no-image"></span>
                        {{/featureImage}}
                    </div>
                    <div class="result-sub-content">
                        <h3 class="result-title text-ellipsis">
                            {{{ _highlightResult.post_title.value }}}
                        </h3>
                        <div class="ratings">
                            <div class="rating-box">
                                <div class="rating" style="width:{{average_rating}}%" width="148" height="148"></div>
                            </div>
                        </div>
                        <div class="price">
                            <div class="algoliasearch-autocomplete-price">
                                <div>
                                    {{_price}}€
                                </div>
                            </div>
                        </div>

                        {{#isAddToCartEnabled}}
                        <form action="/checkout/cart/add/product/{{objectID}}" method="post">
                            <input type="hidden" name="form_key" value="" />

                            <input type="hidden" name="qty" value="1">

                            <button type="submit">Add to Cart</button>
                        </form>
                        {{/isAddToCartEnabled}}
                    </div>
                </div>
                <div class="clearfix"></div>
            </a>
        </div>
        {{/hits}}
        {{^hits.length}}
        <div class="infos">
            No results found matching "<strong>{{query}}</strong>".  <span class="button clear-button">Clear query and filters</span>
        </div>
        {{/hits.length}}
        <div style="clear: both;"></div>
    </div>
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

                {{#type.menu}}
                <div class="{{#checked}}checked {{/checked}}sub_facet menu" data-value="{{value}}" data-facet="{{facet}}">
                    <input style="display: none;" data-facet="{{facet}}" {{#checked}}checked{{/checked}} data-value="{{value}}" class="facet_value" type="checkbox" />
                    {{name}} ({{count}})
                </div>
                {{/type.menu}}

                {{#type.conjunctive}}
                <div class="{{#checked}}checked {{/checked}}sub_facet conjunctive">
                    <input style="display: none;" data-facet="{{facet}}" {{#checked}}checked{{/checked}} data-value="{{value}}" class="facet_value" type="checkbox" />
                    {{name}} ({{count}})
                </div>
                {{/type.conjunctive}}

                {{#type.slider}}
                <div class="algolia-slider algolia-slider-true" data-name="{{name}}" data-min="{{min}}" data-max="{{max}}"></div>
                <div class="algolia-slider-info">
                    <div class="min" style="float: left;">{{current_min}}</div>
                    <div class="max" style="float: right;">{{current_max}}</div>
                    <div style="clear: both"></div>
                </div>
                {{/type.slider}}

                {{#type.disjunctive}}
                <div class="{{#checked}}checked {{/checked}}sub_facet disjunctive">
                    <input data-facet="{{facet}}" {{#checked}}checked{{/checked}} data-value="{{value}}" class="facet_value" type="checkbox" />
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
<div class="pagination-wrapper">
    <div class="text-center">
        <ul class="algolia-pagination">
            <a href="#" data-page="{{prev_page}}">
                <li {{^prev_page}}class="disabled"{{/prev_page}}>
                    &laquo;
                </li>
            </a>

            {{#pages}}
            <a href="#" data-page="{{number}}">
                <li class="{{#current}}active{{/current}}{{#disabled}}disabled{{/disabled}}">
                    {{ number }}
                </li>
            </a>
            {{/pages}}

            <a href="#" data-page="{{next_page}}">
                <li {{^next_page}}class="disabled"{{/next_page}}>
                    &raquo;
                </li>
            </a>
        </ul>
    </div>
</div>
</script>