<script type="text/html" id="tmpl-autocomplete-post-suggestion">
	<a class="suggestion-link" href="{{ data.permalink }}">
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
