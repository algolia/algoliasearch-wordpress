<script type="text/html" id="tmpl-autocomplete-header">
	<div class="autocomplete-header">
		<div class="autocomplete-header-title">{{ data.label }}</div>
		<# if( data.more_link ) { #>
			<div class="autocomplete-header-more">
				<a href="{{ data.more_link }}"><?php esc_html_e( 'More', 'algolia' ); ?></a>
			</div>
		<# } #>
		<div class="clear"></div>
	</div>
</script>
