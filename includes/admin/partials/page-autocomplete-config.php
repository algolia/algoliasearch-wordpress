<table class="widefat table-autocomplete">
	<thead>
		<tr>
			<th><?php _e( 'Enable', 'algolia' ); ?></th>
			<th><?php _e( 'Index', 'algolia' ); ?></th>
			<th><?php _e( 'Max. Suggestions', 'algolia' ); ?></th>
			<th><?php _e( 'Position', 'algolia' ); ?></th>
		</tr>
	</thead>
	<tbody>
		<?php foreach ( $indices as $index ) : ?>
		<tr>
			<td>
				<input type="checkbox" name="algolia_autocomplete_config[<?php echo esc_attr( $index['index_id'] ); ?>][enabled]" <?php echo $index['enabled'] ? 'checked="checked"' : ''; ?>/>
			</td>
			<td>
				<?php echo esc_html( $index['label'] ); ?>
			</td>
			<td>
				<input type="number" name="algolia_autocomplete_config[<?php echo esc_attr( $index['index_id'] ); ?>][max_suggestions]"  value="<?php echo (int) $index['max_suggestions']; ?>" />
			</td>
			<td>
				<input type="number" name="algolia_autocomplete_config[<?php echo esc_attr( $index['index_id'] ); ?>][position]"  value="<?php echo (int) $index['position']; ?>" />
			</td>
		</tr>
		<?php endforeach; ?>
		<?php if ( empty( $indices ) ) : ?>
			<tr>
				<td colspan="4" class="column-comments">
					<em>You have no indexed content yet.</em>
				</td>
			</tr>
		<?php endif; ?>
	</tbody>
</table>
<p class="description" id="home-description">
	<?php _e( 'Configure here the indices you want to display in the dropdown menu.', 'algolia' ); ?>
	<br />
	<?php _e( 'Use the `Max. Suggestions` column to configure the number of entries that will be displayed by section.', 'algolia' ); ?>
	<br />
	<?php _e( 'Use the `Position` column to reflect the order of the sections in the dropdown menu.', 'algolia' ); ?>
</p>
