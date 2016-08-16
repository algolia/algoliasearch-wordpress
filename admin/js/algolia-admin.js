(function( $ ) {
	'use strict';

	$(function() {

		// Native Search page.
		var $override_native_search = $("input[name='algolia_override_native_search']");
		var $native_search_post_type = $("select[name='algolia_native_search_index_id']").parents('tr');

		function show_native_search_post_type() {
			$native_search_post_type.show();
		}

		function hide_native_search_post_type() {
			$native_search_post_type.hide();
		}

		function refresh_native_search_post_type_display()
		{
			if ($override_native_search.is(':checked') || $override_native_search.is(':disabled')) {
				show_native_search_post_type();
			} else {
				hide_native_search_post_type();
			}
		}
		refresh_native_search_post_type_display();

		$override_native_search.on('change', refresh_native_search_post_type_display);



		// Autocomplete page.
		var $autocomplete_enabled = $("input[name='algolia_autocomplete_enabled']");
		var $autocomplete_settings = $('.table-autocomplete').parents('tr');

		function show_autocomplete_settings() {
			$autocomplete_settings.show();
		}

		function hide_autocomplete_settings() {
			$autocomplete_settings.hide();
		}

		function refresh_autocomplete_display()
		{
			if ($autocomplete_enabled.is(':checked') || $autocomplete_enabled.is(':disabled')) {
				show_autocomplete_settings();
			} else {
				hide_autocomplete_settings();
			}
		}
		refresh_autocomplete_display();

		$autocomplete_enabled.on('change', refresh_autocomplete_display);

		// Logs page.
		$(".display-logs").on("click", function(e) {
			e.preventDefault();
			$(e.currentTarget).parent().find(".log-details").toggle();
		});
	});
})( jQuery );
