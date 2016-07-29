<script type="text/html" id="tmpl-autocomplete-post-suggestion">
	<a class="suggestion-link" href="{{ data.permalink }}">
		<# if ( data.thumbnail_url ) { #>
			<img class="suggestion-post-thumbnail" src="{{ data.thumbnail_url }}" width="32" height="32" alt="{{ data.post_title }}">
		<# } #>
		<span class="suggestion-post-title">{{{ data._highlightResult.post_title.value }}}</span>
	</a>
</script>
