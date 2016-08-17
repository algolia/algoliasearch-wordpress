<div class="wrap">
	<h1>
		<?php echo esc_html( get_admin_page_title() ); ?>
		<a href="<?php echo esc_url( admin_url( 'admin-post.php?action=algolia_clear_logs' ) ); ?>" class="page-title-action"><?php esc_html_e( 'Clear logs', 'algolia' ); ?></a>

		<?php if ( $this->logger->is_logging_enabled() ): ?>
			<a href="<?php echo esc_url( admin_url( 'admin-post.php?action=algolia_disable_logging' ) ); ?>" class="page-title-action"><?php esc_html_e( 'Disable logging', 'algolia' ); ?></a>
		<?php else: ?>
			<a href="<?php echo esc_url( admin_url( 'admin-post.php?action=algolia_enable_logging' ) ); ?>" class="page-title-action"><?php esc_html_e( 'Enable logging', 'algolia' ); ?></a>
		<?php endif; ?>
	</h1>

	<?php  global $wp_query; ?>
	<?php  $wp_query = $logs_query; ?>


	<div class="tablenav top">
		<div class="alignleft actions bulkactions">
			<ul class="subsubsub">
				<li class="level-all">
					<?php $current_class = 'class="current"'; ?>
					<?php $class = ! isset( $_GET['log_level'] ) ? $current_class : ''; ?>
					<a href="<?php echo esc_url( admin_url( 'admin.php?page=algolia-logs' ) ); ?>" <?php echo $class; ?>><?php esc_html_e( 'All', 'algolia' ); ?> </a>
					|
				</li>
				<li class="level-info">
					<?php $class = isset( $_GET['log_level'] ) && $_GET['log_level'] === 'info' ? $current_class : ''; ?>
					<a href="<?php echo esc_url( admin_url( 'admin.php?page=algolia-logs&log_level=info' ) ); ?>" <?php echo $class; ?>><?php esc_html_e( 'Info', 'algolia' ); ?> </a>
					|
				</li>
				<li class="level-operation">
					<?php $class = isset( $_GET['log_level'] ) && $_GET['log_level'] === 'operation' ? $current_class : ''; ?>
					<a href="<?php echo esc_url( admin_url( 'admin.php?page=algolia-logs&log_level=operation' ) ); ?>" <?php echo $class; ?>><?php esc_html_e( 'Operations', 'algolia' ); ?> </a>
					|
				</li>
				<li class="level-error">
					<?php $class = isset( $_GET['log_level'] ) && $_GET['log_level'] === 'error' ? $current_class : ''; ?>
					<a href="<?php echo esc_url( admin_url( 'admin.php?page=algolia-logs&log_level=error' ) ); ?>" <?php echo $class; ?>><?php esc_html_e( 'Errors', 'algolia' ); ?> </a>
				</li>
			</ul>
		</div>

		<div class="tablenav-pages">
			<span class="displaying-num"><?php esc_html_e( $wp_query->found_posts ); ?> <?php esc_html_e( 'items' ); ?> | </span>
			<span class="pagination-links">
				<?php echo str_replace( '/?page', '?page', paginate_links( array(
					'format' => '?paged=%#%',
				) ) ) ; ?>
			</span>
		</div>
		<br class="clear">
	</div>


	<table class="widefat">
		<thead>
			<tr>
				<th>#</th>
				<th><?php esc_html_e( 'Level', 'algolia' ); ?></th>
				<th><?php esc_html_e( 'Time', 'algolia' ); ?></th>
				<th><?php esc_html_e( 'Message', 'algolia' ); ?></th>
				<th><?php esc_html_e( 'Data', 'algolia' ); ?></th>
			</tr>
		</thead>
		<tbody>

		<?php foreach ( $logs_query->posts as $log ) : ?>
			<?php /** @var WP_Post $log */ ?>
			<tr>
				<td><?php echo esc_html( $log->ID ); ?></td>
				<td><?php echo esc_html( get_post_meta( $log->ID, 'algolia_log_level', true ) ); ?></td>
				<td><?php echo esc_html( get_the_date( 'Y/m/d \a\t h:i:s', $log ) ); ?></td>
				<td><?php echo esc_html( $log->post_title ); ?></td>
				<td>
					<?php if ( ! empty( $log->post_content ) ) : ?>
						<a href="#" class="display-logs"><?php esc_html_e( 'Show log details', 'algolia' ); ?></a>
						<pre class="log-details"><?php echo esc_html( $log->post_content ); ?></pre>
					<?php endif; ?>
				</td>
			</tr>
		<?php endforeach; ?>
		</tbody>
	</table>

</div>
