<script type="text/html" id="tmpl-autocomplete-user-suggestion">
	<a class="suggestion-link" href="{{ data.posts_url }}">
		<# if ( data.avatar_url ) { #>
			<img class="suggestion-user-thumbnail" src="{{ data.avatar_url }}" width="32" height="32" alt="{{ data.display_name }}">
		<# } #>
		<span class="suggestion-post-title">{{{ data._highlightResult.display_name.value }}}</span>
	</a>
</script>
