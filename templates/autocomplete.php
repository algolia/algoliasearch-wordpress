<script type="text/html" id="tmpl-autocomplete-header">
	<div class="autocomplete-header">
		<div class="autocomplete-header-title">{{{ data.label }}}</div>
		<div class="clear"></div>
	</div>
</script>

<script type="text/html" id="tmpl-autocomplete-post-suggestion">
	<a class="suggestion-link" href="{{ data.permalink }}" title="{{ data.post_title }}">
		<# if ( data.images.thumbnail ) { #>
		<img class="suggestion-post-thumbnail" src="{{ data.images.thumbnail.url }}" alt="{{ data.post_title }}">
		<# } #>
		<div class="suggestion-post-attributes">
			<span class="suggestion-post-title">{{{ data._highlightResult.post_title.value }}}</span>
      <# if ( data._snippetResult['content'] ) { #>
        <span class="suggestion-post-content">{{{ data._snippetResult['content'].value }}}</span>
			<# } #>
		</div>
	</a>
</script>

<script type="text/html" id="tmpl-autocomplete-term-suggestion">
	<a class="suggestion-link" href="{{ data.permalink }}"  title="{{ data.name }}">
		<svg viewBox="0 0 21 21" width="21" height="21"><svg width="21" height="21" viewBox="0 0 21 21"><path d="M4.662 8.72l-1.23 1.23c-.682.682-.68 1.792.004 2.477l5.135 5.135c.7.693 1.8.688 2.48.005l1.23-1.23 5.35-5.346c.31-.31.54-.92.51-1.36l-.32-4.29c-.09-1.09-1.05-2.06-2.15-2.14l-4.3-.33c-.43-.03-1.05.2-1.36.51l-.79.8-2.27 2.28-2.28 2.27zm9.826-.98c.69 0 1.25-.56 1.25-1.25s-.56-1.25-1.25-1.25-1.25.56-1.25 1.25.56 1.25 1.25 1.25z" fill-rule="evenodd"></path></svg></svg>
		<span class="suggestion-post-title">{{{ data._highlightResult.name.value }}}</span>
	</a>
</script>

<script type="text/html" id="tmpl-autocomplete-user-suggestion">
	<a class="suggestion-link user-suggestion-link" href="{{ data.posts_url }}"  title="{{ data.display_name }}">
		<# if ( data.avatar_url ) { #>
		<img class="suggestion-user-thumbnail" src="{{ data.avatar_url }}" alt="{{ data.display_name }}">
		<# } #>

		<span class="suggestion-post-title">{{{ data._highlightResult.display_name.value }}}</span>
	</a>
</script>

<script type="text/html" id="tmpl-autocomplete-footer">
	<div class="autocomplete-footer">
		<div class="autocomplete-footer-branding">
			<?php esc_html_e( 'Powered by', 'algolia' ); ?>
			<a href="#" class="algolia-powered-by-link" title="Algolia">
				<img class="algolia-logo" src="https://www.algolia.com/assets/algolia128x40.png" alt="Algolia" />
			</a>
		</div>
	</div>
</script>

<script type="text/html" id="tmpl-autocomplete-empty">
	<div class="autocomplete-empty">
		<?php esc_html_e( 'No results matched your query ', 'algolia' ); ?>
		<span class="empty-query">{{ data.query }}"</span>
	</div>
</script>

<script type="text/javascript">
	jQuery(function () {
		/* init Algolia client */
		var client = algoliasearch(algolia.application_id, algolia.search_api_key);

		/* setup default sources */
		var sources = [];
		jQuery.each(algolia.autocomplete.sources, function(i, config) {
			sources.push({
				source: algoliaAutocomplete.sources.hits(client.initIndex(config['index_name']), {
					hitsPerPage: config['max_suggestions'],
					attributesToSnippet: [
						'content:10'
					]
				}),
				templates: {
					header: function() {
						return wp.template('autocomplete-header')({
							label: config['label']
						});
					},
					suggestion: wp.template(config['tmpl_suggestion'])
				}
			});

		});

		/* Setup dropdown menus */
		jQuery("input[name='s']:not('.no-autocomplete')").each(function(i) {
			var $searchInput = jQuery(this);

			var config = {
				debug: algolia.debug,
				hint: false, // Required given we use appendTo feature.
				openOnFocus: true,
				templates: {},
        appendTo: 'body'
			};
			/* Todo: Add empty template when we fixed https://github.com/algolia/autocomplete.js/issues/109 */

			if(algolia.powered_by_enabled) {
				config.templates.footer = wp.template('autocomplete-footer');
			}

			/* Instantiate autocomplete.js */
			algoliaAutocomplete($searchInput[0], config, sources)
			.on('autocomplete:selected', function(e, suggestion, datasetName) {
				/* Redirect the user when we detect a suggestion selection. */
				window.location.href = suggestion.permalink;
			});
		});

		jQuery(document).on("click", ".algolia-powered-by-link", function(e) {
			e.preventDefault();
			window.location = "https://www.algolia.com/?utm_source=WordPress&utm_medium=extension&utm_content=" + window.location.hostname + "&utm_campaign=poweredby";
		});
	});
</script>
