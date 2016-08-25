
<div class="input-radio">
	<label>
		<input type="radio" value="native" name="algolia_override_native_search" <?php if ( $value === 'native' ): ?>checked<?php endif; ?>>
		<?php esc_html_e( 'Do not use Algolia', 'algolia' ); ?>
	</label>
	<div class="radio-info">
		Do not use Algolia for searching at all.
		This is only a valid option if you wish to search on your content from another website.
	</div>

	<label>
		<input type="radio" value="backend" name="algolia_override_native_search" <?php if ( $value === 'backend' ): ?>checked<?php endif; ?>>
		<?php esc_html_e( 'Use Algolia in the backend', 'algolia' ); ?>
	</label>
	<div class="radio-info">
		With this option WordPress search will be powered by Algolia behind the scenes.
		This will allow your search results to be typo tolerant.
		<b>This option does not support filtering and displaying instant search results but has the advantage to play nicely with any theme.</b>
	</div>

	<label>
		<input type="radio" value="instantsearch" name="algolia_override_native_search" <?php if ( $value === 'instantsearch' ): ?>checked<?php endif; ?>>
		<?php esc_html_e( 'Use Algolia with Instantsearch.js', 'algolia' ); ?>
	</label>
	<div class="radio-info">
		This will replace the search page with an instant search experience powered by Algolia.
		By default you will be able to filter by post type, categories, tags and authors.

		Please note that the plugin is shipped with some sensible default styling rules but it could require some
		CSS adjustments to provide an optimal search experience.
	</div>
</div>
