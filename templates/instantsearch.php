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

	<script type="text/html" id="tmpl-instantsearch-hit">
		<article itemtype="http://schema.org/Article">
			<# if ( data.images.thumbnail ) { #>
			<div class="ais-hits--thumbnail">
				<a href="{{ data.permalink }}" title="{{ data.post_title }}">
					<img src="{{ data.images.thumbnail.url }}" alt="{{ data.post_title }}" title="{{ data.post_title }}" itemprop="image" />
				</a>
			</div>
			<# } #>

			<div class="ais-hits--content">
				<h2 itemprop="name headline"><a href="{{ data.permalink }}" title="{{ data.post_title }}" itemprop="url">{{{ data._highlightResult.post_title.value }}}</a></h2>
				<div class="excerpt">
					<p>
			<# if ( data._snippetResult['content'] ) { #>
			  <span class="suggestion-post-content">{{{ data._snippetResult['content'].value }}}</span>
			<# } #>
					</p>
				</div>
			</div>
			<div class="ais-clearfix"></div>
		</article>
	</script>


	<script type="text/javascript">
		jQuery(function() {
			if(jQuery('#algolia-search-box').length > 0) {

				if (algolia.indices.searchable_posts === undefined && jQuery('.admin-bar').length > 0) {
					alert('It looks like you haven\'t indexed the searchable posts index. Please head to the Indexing page of the Algolia Search plugin and index it.');
				}

				/* Instantiate instantsearch.js */
				var search = instantsearch({
					appId: algolia.application_id,
					apiKey: algolia.search_api_key,
					indexName: algolia.indices.searchable_posts.name,
					urlSync: {
						mapping: {'q': 's'},
						trackedParameters: ['query']
					},
					searchParameters: {
						facetingAfterDistinct: true,
			highlightPreTag: '__ais-highlight__',
			highlightPostTag: '__/ais-highlight__'
					}
				});

				/* Search box widget */
				search.addWidget(
					instantsearch.widgets.searchBox({
						container: '#algolia-search-box',
						placeholder: 'Search for...',
						wrapInput: false,
						poweredBy: algolia.powered_by_enabled
					})
				);

				/* Stats widget */
				search.addWidget(
					instantsearch.widgets.stats({
						container: '#algolia-stats'
					})
				);

				/* Hits widget */
				search.addWidget(
					instantsearch.widgets.hits({
						container: '#algolia-hits',
						hitsPerPage: 10,
						templates: {
							empty: 'No results were found for "<strong>{{query}}</strong>".',
							item: wp.template('instantsearch-hit')
						},
			transformData: {
						  item: function (hit) {
				for(var key in hit._highlightResult) {
				  // We do not deal with arrays.
				  if(typeof hit._highlightResult[key].value !== 'string') {
					continue;
				  }
				  hit._highlightResult[key].value = _.escape(hit._highlightResult[key].value);
				  hit._highlightResult[key].value = hit._highlightResult[key].value.replace(/__ais-highlight__/g, '<em>').replace(/__\/ais-highlight__/g, '</em>');
				}

				for(var key in hit._snippetResult) {
				  // We do not deal with arrays.
				  if(typeof hit._snippetResult[key].value !== 'string') {
					continue;
				  }

				  hit._snippetResult[key].value = _.escape(hit._snippetResult[key].value);
				  hit._snippetResult[key].value = hit._snippetResult[key].value.replace(/__ais-highlight__/g, '<em>').replace(/__\/ais-highlight__/g, '</em>');
				}

				return hit;
			  }
			}
					})
				);

				/* Pagination widget */
				search.addWidget(
					instantsearch.widgets.pagination({
						container: '#algolia-pagination'
					})
				);

				/* Post types refinement widget */
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

				/* Categories refinement widget */
				search.addWidget(
					instantsearch.widgets.hierarchicalMenu({
						container: '#facet-categories',
						separator: ' > ',
						sortBy: ['count'],
						attributes: ['taxonomies_hierarchical.category.lvl0', 'taxonomies_hierarchical.category.lvl1', 'taxonomies_hierarchical.category.lvl2'],
						templates: {
							header: '<h3 class="widgettitle">Categories</h3>'
						}
					})
				);

				/* Tags refinement widget */
				search.addWidget(
					instantsearch.widgets.refinementList({
						container: '#facet-tags',
						attributeName: 'taxonomies.post_tag',
						operator: 'and',
						limit: 15,
						sortBy: ['isRefined:desc', 'count:desc', 'name:asc'],
						templates: {
							header: '<h3 class="widgettitle">Tags</h3>'
						}
					})
				);

				/* Users refinement widget */
				search.addWidget(
					instantsearch.widgets.menu({
						container: '#facet-users',
						attributeName: 'post_author.display_name',
						sortBy: ['isRefined:desc', 'count:desc', 'name:asc'],
						limit: 10,
						templates: {
							header: '<h3 class="widgettitle">Authors</h3>'
						}
					})
				);

				/* Start */
				search.start();

				jQuery('#algolia-search-box input').attr('type', 'search').select();
			}
		});
	</script>

<?php get_footer(); ?>
