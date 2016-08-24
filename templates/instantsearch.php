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
							header: '<h3 class="widgettitle">Type</h3>'
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

<style>
	#ais-wrapper {
		display: flex;

	}

	#ais-main {
		padding: 1rem;
		width: 100%;
	}

	#ais-facets {
		width: 40%;
		padding: 5%;
		padding: 1rem;
	}

	.ais-facets {
		margin-bottom: 2rem;
	}

	.ais-clearfix {
		clear: both;
	}

	#algolia-search-box {
		position: relative;
		margin-bottom: 2rem;
	}

	#algolia-search-box input {
		border: none;
		border-bottom: 2px solid #21A4D7;
		background: transparent;
		width: 100%;
		line-height: 30px;
		font-size: 22px;
		padding: 10px 0 10px 30px;
		font-weight: 200;
		box-sizing: border-box;
		outline: none;
	}

	#algolia-search-box .search-icon {
		position: absolute;
		left: 0px;
		top: 14px;
		fill: #21A4D7;
	}

	.ais-search-box--powered-by {
		font-size: 14px;
		text-align: right;
		margin-top: 2px;
	}

	.ais-search-box--powered-by-link {
		display: inline-block;
		width: 45px;
		height: 16px;
		text-indent: 101%;
		overflow: hidden;
		white-space: nowrap;
		background-image: url("data:image/png;base64,iVBORw0KGgoAAAANSUhEUgAAAF0AAAAgCAYAAABwzXTcAAAABGdBTUEAALGPC/xhBQAAACBjSFJNAAB6JgAAgIQAAPoAAACA6AAAdTAAAOpgAAA6mAAAF3CculE8AAAACXBIWXMAAA7DAAAOwwHHb6hkAAAAGHRFWHRTb2Z0d2FyZQBwYWludC5uZXQgNC4wLjVlhTJlAAAIJElEQVRoQ+1Za2xURRTugqJVEBAlhICBRFEQeRfodssqiDZaS8vu3dsXVlAbxReJwVfAoqJ/sBqE3S1IgqgBrY9EQ6KJiUAokUfpvQUKogIBlKbyEEUolNL6ndkzw9129+72YaFJv+Rk737nzMyZ756dmXs3oQtd6EJ7oaioqJvX603kr1cl8vPzb+TLzo3MzMx+Xk0r03y+0x5Ne4vpqwoohjeQ4yHYcaYiwcGfVz+ysrIGQfBGsqtWdE37lvLz+nwnmVLIyMjoBd9GxPwL/wKmOw4zCgr6YPBNSGILEviYaVt0dtHxK/DK/BFXq2lad3Z1DJDUqzIBYZrmYldUdLToI4r29HCWmLozUPmEK2AUOgOmRysttRXKTnSPxzMWfD37q0B13DJTUFBwPQatlgKKJJAsu6Oio0VPDlQsTgmajWEWMOaxOyLsRCdQccGez87OHshUxwAJzZbiIYFKkaSmXdJ1fRiHRERHi+4MGk+mBMwXnSVGPj7nQPS3qeLZHRGxRL9ScCAxk8Ur92Rnj5VCItHlHBMRrRDdQRXl8/nG4eaOp5uKz57sC8OkoDEkOWCO5K8CtJRgabnT6TfuS/ZXOKet2duPXVHRDqI7svLz+yPnJCxH07ANuGFDiQ+5WwF0NkWJrOuziEOCm5n7Jy8v7yYRGAHxio4kEyHuK+j3oIyXRr8o2G/wrUXMGIonQbFe18Kq3Ms39By/orw3KnsxKr06fHkxLjkDxubkEuNhMVAE2Ikuni98vsMYtwafQaYVwLvQ9qg1X2mI/xXzyuXQlgGNP+NO/kxLS7tOcOhMda7rz4rACIhH9Ky8vEGY+G4ZZ2ua9hi1gbhvQvBDScu3DUC1j8X1YSV0wDgLsX9m7tJl3lw9onRPDzGoBTFFp1NLyL+WaQUU5GSZG+IuIeYCrhskJ3ivN6o+EYFJDuCOaNBipuXGepI73gMq4k8pluh0E5GsXLoo8U1IMgPLyhDYYExqNL6/Lv1S9FT/7sHOkp0TXCvNYbgBp0hUfB6A2D6rsKn+7YMh9nvOoHkxJL6xLiGhMSzXtoiOfHqDn41ch5MmFC+O1ihEtDnP7c5QHDeJDTSQx8QGTH4E0wLwLWVfo0fXU5kOQyzR0ecL0o/EvoI1O95ZlzcpugAmiKVjKwu+1f2+0Yc9As5VZb3gX4JfQn9XwEyH+HUi1m/kc4hAW0S3A3J9TeaNOWQybQ8aEA0O8IDbmFagM6zsFP5PmA5DTNF5WUH7c7QZMR2GaKK7Ssw0FvyMe2XlIKYVUkrMR4Q/YB6b4t85HKIv5Pj9CY2Xq/3/Ep2qX+aN4prPtD0w2ftlI0z2GaatsJ5qztLPinkFO9Fzc3P7ghfrH/r5nulmiCY6qnhVSEQz4gkKIvvJD2sQS8yqfb3wifWeuN2jOazdRIewibQszszJuYO0yMnJuUXmjbZFHGYPTHAdN7iQOWtWxKMXfPNkx5FujJ3oEHOk9KGfpUw3QzTRsWHuCAloZDFlQaMDN+Ugqrocy8tUJulG/Mg34lGm2iR6YWHhteDnIq8diLmo8gwV0zH5HTGxRcddu1kOhg6PotGCKKbWdVg5N1eIIfpo1VbT3mW6GWxE30cCulbscjOlkLRsb7+UQGUuVOvGlABu0JdC9IChCqS1olNlg9+ocqOY0PG2FrHi1YHi4xJd15+2NorTaLO9h7sQsBOdTieqLX5VTDdD9OXFLCMBm26MdqANV7QpMXWm2iK69VS1AXmm0AmGfOIX4PUmS398omPjFME0oKZtsTPEqDM22qljJcFOdLTtDv4E+2vkM0BT2FR6sRAwaJQyZYuJ2Gyx5NSj2htSPzDpiVGg1aLzfga+mqqeaQX6L0HmjRh70a27Lib5KdNRgZjelsSq3W73NewKEx1xYaITwJVY/IuYDkM00Scv2zGOBETF1+MkM4npqIDga8RNwhMqUwKtFt3n+13wmlbGVBhaJDom9o4MxoQfYtoW6PQLNYDXqx65cX2r4n2+j5hWoN0e/BmOoeUpgDFH0qsFXA+FPQ5/lezDKjoBoq8Ta3TQ/MPl3zWK6XBAOMQtCglu1qcsN8NeScvcIV5d01cadqIjF9o8qd0p+rODaYW4RedBjnBwjbVq7QChPJYBPmda9Ef9sO88fC/NnDnzLnYL4MFqBvk4xt6aiO5ebfSBoLu5gmtxXZzsr0hyBXb1xRFxYHKwwivXfrJkv/EyN1VAn4tk/8hvPebyIK3J5ItR6Qssee1Ageh4drkbn7dT4fC8ZL/RRUeDqZZA2zeIVqAd7eSnud05JKEee3GtnsyEYUlhlwK4MWi3HiZeOVjsF/g+VN+biE6gN4nOYOV3UtiIhvO5028+xU3CgD5vg7B/yzFwXSf3FzvR6Y9s+Lar3GwMbW1Ex7kbHW0iw12bwHRcQPILVVtdn8Y0wYF+52LwChhV+3PMN8N0TARVQu9bJtKLMFAO5HGvSh7VFIpsikaHeNQPGt9A5JMkNG2asP2wJfSuhgMjwpOdPQp5fY0xTiD/vUxL0X8Q88JphWkF8Q5K1+dj7hVoby2Yi+Bq0G4nPkvRdjo36XiI5aaF/zNiUur9DN0Mpu3gmFx8JHH8inKxRLQUcmlpKWhesN4Zc+b0aukcrwSivuynR2lUkHjHjqo53lpBumABKjcRolbBluJ6FpaWKVTNWJ4eQLXQXnD5DwJ852ZdaAsgsvoTwM5wU1Z3hp9spwCqeigELcbS8RPE/QvX9M6iAd/rcH0YtrbJptyFdoYD1dwjPT39hnifD7rQhTiRkPAfxnOcWpCmnRwAAAAASUVORK5CYII=");
		background-repeat: no-repeat;
		background-size: contain;
		vertical-align: middle;
	}
	.ais-stats {
		position: absolute;
		bottom: 0;
		font-size: 14px;
	}

	.ais-hits--item {
		/* hit item */
		margin-bottom: 2rem;
	}


	.ais-hits--item h2 {
		margin-bottom: 0;
	}

	.ais-hits--tags {
		margin-bottom: 1rem;
	}

	.ais-hits--tag {
		background: #F2F2F2;
		padding: 0px 7px;
		border-radius: 2px;
		display: inline-block;
		margin-right: .5rem;
		margin-bottom: .5rem;
		font-size: 14px;
	}


	.ais-hits--item em, .ais-hits--item a em {
		font-style: normal;
		background: #21A4D7;
		color: #FFFFFF;
		padding: 0 1px;
		border-radius: 2px;
	}

	.ais-hits--thumbnail {
		float: left;
		margin-right: 2rem;
	}

	.ais-hits--content {
		overflow: hidden;
	}

	.ais-hits--thumbnail img {
		border-radius: 3px;
	}

	.ais-pagination {
		margin: 0;
	}

	.ais-pagination--item {
		/* pagination item */
		display: inline-block;
		padding: 3px;
	}

	.ais-pagination--item__disabled {
		/* disabled pagination item */
		display: none;
	}

	.ais-pagination--item__active {
		font-weight: bold;
	}

	.ais-refinement-list--count {
		/* item count */
		display: none;
	}

	.ais-menu--item__active {
		/* active list item */
		font-weight: bold;
	}

	.ais-menu--count {
		/* item count */
		display: none;
	}

	.ais-hierarchical-menu--list__lvl1 {
		/* item list level 1 */
		margin-left: 10px;
	}

	.ais-hierarchical-menu--list__lvl2 {
		/* item list level 0 */
		margin-left: 10px;
	}

	.ais-range-slider--target {
		position: relative;
		direction: ltr;
		background: #F3F4F7;
		height: 6px;
		margin-top: 2em;
		margin-bottom: 2em;
	}

	.ais-range-slider--base {
		height: 100%;
		position: relative;
		z-index: 1;
		border-top: 1px solid #DDD;
		border-bottom: 1px solid #DDD;
		border-left: 2px solid #DDD;
		border-right: 2px solid #DDD;
	}

	.ais-range-slider--origin {
		position: absolute;
		right: 0;
		top: 0;
		left: 0;
		bottom: 0;
	}

	.ais-range-slider--connect {
		background: #46AEDA;
	}

	.ais-range-slider--background {
		background: #F3F4F7;
	}

	.ais-range-slider--handle {
		width: 20px;
		height: 20px;
		position: relative;
		z-index: 1;
		background: #FFFFFF;
		border: 1px solid #46AEDA;
		border-radius: 50%;
		cursor: pointer;
	}

	.ais-range-slider--handle-lower {
		left: -10px;
		bottom: 7px;
	}

	.ais-range-slider--handle-upper {
		right: 10px;
		bottom: 7px;
	}

	.ais-range-slider--tooltip {
		position: absolute;
		background: #FFFFFF;
		top: -22px;
		font-size: .8em;
	}

	.ais-range-slider--pips {
		box-sizing: border-box;
		position: absolute;
		height: 3em;
		top: 100%;
		left: 0;
		width: 100%;
	}

	.ais-range-slider--value {
		width: 40px;
		position: absolute;
		text-align: center;
		margin-left: -20px;
		padding-top: 15px;
		font-size: .8em;
	}

	.ais-range-slider--value-sub {
		font-size: .8em;
		padding-top: 15px;
	}

	.ais-range-slider--marker {
		position: absolute;
		background: #DDD;
		margin-left: -1px;
		width: 1px;
		height: 5px;
	}

	.ais-range-slider--marker-sub {
		background: #DDD;
		width: 2px;
		margin-left: -2px;
		height: 13px;
	}

	.ais-range-slider--marker-large {
		background: #DDD;
		width: 2px;
		margin-left: -2px;
		height: 12px;
	}

	.ais-range-slider--marker-large:first-child {
		margin-left: 0;
	}

	.ais-star-rating--item {
		/* list item */
		vertical-align: middle;
	}

	.ais-star-rating--item__active {
		/* active list item */
		font-weight: bold;
	}

	.ais-star-rating--star {
		/* item star */
		display: inline-block;
		width: 1em;
		height: 1em;
	}

	.ais-star-rating--star:before {
		content: '\2605';
		color: #FBAE00;
	}

	.ais-star-rating--star__empty {
		/* empty star */
		display: inline-block;
		width: 1em;
		height: 1em;
	}

	.ais-star-rating--star__empty:before {
		content: '\2606';
		color: #FBAE00;
	}

	.ais-star-rating--link__disabled .ais-star-rating--star:before {
		color: #C9C9C9;
	}

	.ais-star-rating--link__disabled .ais-star-rating--star__empty:before {
		color: #C9C9C9;
	}

	.ais-root__collapsible .ais-header {
		cursor: pointer;
	}

	.ais-root__collapsed .ais-body, .ais-root__collapsed .ais-footer {
		display: none;
	}

	/* Hierarchical Menu: Categories */
	.ais-hierarchical-menu--item__active > div > a {
		font-weight: bold;
	}
	.ais-hierarchical-menu--count {
		display: none;
	}

	/* Responsive */
	@media only screen and (max-width: 1000px) {
		#ais-facets {
			display: none;
		}

		.ais-hits--thumbnail img {
			width: 100% !important;
		}
		.ais-hits--item {
			border-bottom: 1px solid gainsboro;
			padding-bottom: 23px;
		}
	}

	@media only screen and (max-width: 500px) {
		.ais-hits--thumbnail {
			margin-right: 0 !important;
			margin-bottom: 10px;
			float: none !important;
		}
	}

</style>

<?php get_footer(); ?>
