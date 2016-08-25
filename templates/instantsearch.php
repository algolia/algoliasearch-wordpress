<?php get_header(); ?>

	<div id="ais-wrapper">
		<main id="ais-main">
			<div id="algolia-search-box">
				<div id="algolia-stats"></div>
				<svg class="search-icon" width="25" height="25" viewBox="0 0 40 40" xmlns="http://www.w3.org/2000/svg"><path d="M24.828 31.657a16.76 16.76 0 0 1-7.992 2.015C7.538 33.672 0 26.134 0 16.836 0 7.538 7.538 0 16.836 0c9.298 0 16.836 7.538 16.836 16.836 0 3.22-.905 6.23-2.475 8.79.288.18.56.395.81.645l5.985 5.986A4.54 4.54 0 0 1 38 38.673a4.535 4.535 0 0 1-6.417-.007l-5.986-5.986a4.545 4.545 0 0 1-.77-1.023zm-7.992-4.046c5.95 0 10.775-4.823 10.775-10.774 0-5.95-4.823-10.775-10.774-10.775-5.95 0-10.775 4.825-10.775 10.776 0 5.95 4.825 10.775 10.776 10.775z" fill-rule="evenodd"></path></svg>
			</div>
			<div id="algolia-hits"></div>
			<div id="algolia-pagination"></div>
		</main>
		<aside id="ais-facets">
			<section class="ais-facets" id="facet-post-types"></section>
			<section class="ais-facets" id="facet-categories"></section>
			<section class="ais-facets" id="facet-tags"></section>
			<section class="ais-facets" id="facet-users"></section>
		</aside>
	</div>

	<script type="text/html" id="tmpl-hit">
		<article itemtype="http://schema.org/Article">
			{{#thumbnail_url}}
			<div class="ais-hits--thumbnail">
				<a href="{{ permalink }}" title="{{ post_title }}">
					<img src="{{ thumbnail_url }}" alt="{{{ post_title }}}" title="{{{ post_title }}}" itemprop="image">
				</a>
			</div>
			{{/thumbnail_url}}

			<div class="ais-hits--content">
				<h2 itemprop="name headline"><a href="{{ permalink }}" title="{{ post_title }}" itemprop="url">{{{ _highlightResult.post_title.value }}}</a></h2>
				<div class="ais-hits--tags">
					{{#taxonomy_post_tag}}
					<span class="ais-hits--tag">{{.}}</span>
					{{/taxonomy_post_tag}}
				</div>
				<div class="excerpt">
					<p>
						{{#helpers.relevantContent}}{{/helpers.relevantContent}}
					</p>
				</div>
			</div>
			<div class="ais-clearfix"></div>
		</article>
	</script>


	<script type="text/javascript">
		jQuery(function() {
			if(jQuery('#algolia-search-box').length > 0) {
				/* global instantsearch */
				var search = instantsearch({
					appId: algolia.application_id,
					apiKey: algolia.search_api_key,
					indexName: algolia.indices.searchable_posts.name,
					urlSync: {
						mapping: {'q': 's'},
						trackedParameters: ['query']
					}
				});

				search.addWidget(
					instantsearch.widgets.searchBox({
						container: '#algolia-search-box',
						placeholder: 'Search for...',
						wrapInput: false,
						poweredBy: true
					})
				);

				search.addWidget(
					instantsearch.widgets.stats({
						container: '#algolia-stats'
					})
				);

				var hitTemplate = jQuery("#tmpl-hit").html();

				var noResultsTemplate = 'No result was found for "<strong>{{query}}</strong>".';

				search.addWidget(
					instantsearch.widgets.hits({
						container: '#algolia-hits',
						hitsPerPage: 10,
						templates: {
							empty: noResultsTemplate,
							item: hitTemplate
						}
					})
				);

				search.addWidget(
					instantsearch.widgets.pagination({
						container: '#algolia-pagination'
					})
				);

				search.addWidget(
					instantsearch.widgets.menu({
						container: '#facet-post-types',
						attributeName: 'post_type_label',
						sortBy: ['isRefined:desc', 'count:desc', 'name:asc'],
						limit: 10,
						templates: {
							header: '<h3 class="widgettitle">Post Type</h3>'
						},
					})
				);

				search.addWidget(
					instantsearch.widgets.hierarchicalMenu({
						container: '#facet-categories',
						separator: ' > ',
						sortBy: ['count'],
						attributes: ['category_tree.lvl0', 'category_tree.lvl1', 'category_tree.lvl2'],
						templates: {
							header: '<h3 class="widgettitle">Categories</h3>'
						}
					})
				);

				search.addWidget(
					instantsearch.widgets.refinementList({
						container: '#facet-tags',
						attributeName: 'taxonomy_post_tag',
						operator: 'and',
						limit: 15,
						sortBy: ['isRefined:desc', 'count:desc', 'name:asc'],
						templates: {
							header: '<h3 class="widgettitle">Tags</h3>'
						}
					})
				);

				search.addWidget(
					instantsearch.widgets.menu({
						container: '#facet-users',
						attributeName: 'post_author.display_name',
						sortBy: ['isRefined', 'count:desc', 'name:asc'],
						limit: 10,
						templates: {
							header: '<h3 class="widgettitle">Auteurs</h3>'
						}
					})
				);

				search.templatesConfig.helpers.relevantContent = function() {
					var attributes = ['content', 'title6', 'title5', 'title4', 'title3', 'title2', 'title1'];
					var attribute_name;
					for ( var index in attributes ) {
						attribute_name = attributes[ index ];
						if ( this._highlightResult[ attribute_name ].matchedWords.length > 0 ) {
							return this._snippetResult[ attribute_name ].value;
						}
					}

					return this._snippetResult[ attributes[ 0 ] ].value;
				};

				search.start();

				jQuery('#algolia-search-box input').attr('type', 'search').select();
			}
		});
	</script>

<?php get_footer(); ?>
