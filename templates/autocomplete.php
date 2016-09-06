<script type="text/html" id="tmpl-autocomplete-header">
	<div class="autocomplete-header">
		<div class="autocomplete-header-title">{{ data.label }}</div>
		<div class="clear"></div>
	</div>
</script>

<script type="text/html" id="tmpl-autocomplete-post-suggestion">
	<a class="suggestion-link" href="{{ data.permalink }}" title="{{ data.post_title }}">
		<# if ( data.thumbnail_url ) { #>
		<img class="suggestion-post-thumbnail" src="{{ data.thumbnail_url }}" alt="{{ data.post_title }}">
		<# } #>
			
		<div class="suggestion-post-attributes">
			<span class="suggestion-post-title">{{{ data._highlightResult.post_title.value }}}</span>

			<#
			var attributes = ['content', 'title6', 'title5', 'title4', 'title3', 'title2', 'title1'];
			var attribute_name;
			var relevant_content = '';
			for ( var index in attributes ) {
			attribute_name = attributes[ index ];
			if ( data._highlightResult[ attribute_name ].matchedWords.length > 0 ) {
			relevant_content = data._snippetResult[ attribute_name ].value;
			break;
			} else if( data._snippetResult[ attribute_name ].value !== '' ) {
			relevant_content = data._snippetResult[ attribute_name ].value;
			}
			}
			#>
			<span class="suggestion-post-content">{{{ relevant_content }}}</span>
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
		var client = algoliasearch(algolia.application_id, algolia.search_api_key);

		var sources = [];
		for(var index in algolia.autocomplete.sources) {
			var config = algolia.autocomplete.sources[index];
			sources.push({
				source: sourceCallback(client.initIndex(config['index_name']), config['max_suggestions']),
				templates: {
					header: function (label, template) {
						return function() {
							return wp.template(template)({
								label: label
							});
						}
					}(config['label'], config['tmpl_header']),
					suggestion: function(template) {
						return wp.template(template);
					}(config['tmpl_suggestion'])
				}
			});
		}


		function sourceCallback( index, max_suggestions ) {
			return function (q, cb) {
				index.search(
					q,
					{
						hitsPerPage: max_suggestions,
						attributesToSnippet: [
							'content:10',
							'title1:10',
							'title2:10',
							'title3:10',
							'title4:10',
							'title5:10',
							'title6:10'
						]
					},
					function (error, content) {
						if (error) {
							cb([]);
							return;
						}
						cb(content.hits, content);
					});
			}
		}

		// Make this come from config as well.
		var searchInputSelector = "input[name='s']";
		var searchInput = jQuery(searchInputSelector);

		// Leverage the autocomplete power.
		autocomplete(searchInputSelector,
			{
				debug: algolia.debug,
				hint: false,
				openOnFocus: true,
				templates: {
					// empty: wp.template(algolia.autocomplete.tmpl_empty), // Waiting for https://github.com/algolia/autocomplete.js/issues/109
					footer: wp.template(algolia.autocomplete.tmpl_footer)
				}
			},
			sources
		).on('autocomplete:selected', function(e, suggestion, datasetName) {
			// Redirect the user when we detect a suggestion selection.
			window.location.href = suggestion.permalink;
		});


		// This ensures that when the dropdown overflows the window, Thether can reposition it.
		jQuery('body').css('overflow-x', 'hidden');

		searchInput.each(function(index) {
			var $item = jQuery(this);
			var $autocomplete = $item.parent();

			// Remove autocomplete.js default inline input search styles.
			$autocomplete.removeAttr('style');

			var $menu = $autocomplete.find('.aa-dropdown-menu');
			var config = {
				element: $menu,
				target: this,
				attachment: 'top left',
				targetAttachment: 'bottom left',
				constraints: [
					{
						to: 'window',
						attachment: 'none element'
					}
				]
			};

			// This will make sure the dropdown is no longer part of the same container as
			// the search input container.
			// It ensures styles are not overridden and limits theme breaking.
			var tether = new Tether(config);
			tether.on('update', function(item) {
				// todo: fix the inverse of this: https://github.com/HubSpot/tether/issues/182
				if (item.attachment.left == 'right' && item.attachment.top == 'top' && item.targetAttachment.left == 'left' && item.targetAttachment.top == 'bottom') {
					config.attachment = 'top right';
					config.targetAttachment = 'bottom right';

					tether.setOptions(config, false);
				}
			});

			searchInput.on('autocomplete:updated', function() {
				tether.position();
			});

			searchInput.on('autocomplete:opened', function() {
				updateDropdownWidth();
			});


			// Trick to ensure the autocomplete is always above all.
			$menu.css('z-index', '99999');

			var dropdownMinWidth = 280;

			// Makes dropdown match the input size.
			function updateDropdownWidth() {
				var inputWidth = $item.outerWidth();
				if (inputWidth >= dropdownMinWidth) {
					$menu.css('width', $item.outerWidth());
				} else {
					$menu.css('width', dropdownMinWidth);
				}
				tether.position();
			}
			jQuery(window).on('resize', updateDropdownWidth);
		} );

		jQuery(document).on("click", ".algolia-powered-by-link", function(e) {
			e.preventDefault();
			window.location = "https://www.algolia.com/?utm_source=WordPress&utm_medium=extension&utm_content=" + window.location.hostname + "&utm_campaign=poweredby";
		})
	});
</script>
