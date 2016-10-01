<div class="wrap">
	<h1>
		<?php echo esc_html( get_admin_page_title() ); ?>
		<?php if ( ! empty( $enabled_indices ) ): ?>
			<a href="<?php echo admin_url( 'admin-post.php?action=algolia_re_index_all' ); ?>" class="page-title-action"><?php _e( 'Re-index everything', 'algolia' ); ?></a>
		<?php endif; ?>
	</h1>

	<div class="queue-status">
		<h2><?php _e( 'Queue Monitoring', 'algolia' ); ?></h2>
		<table class="widefat">
			<thead>
				<tr>
					<td>#</td>
					<th>Status (refreshed every 3s.)</th>
					<th>Actions</th>
				</tr>
			</thead>
			<tbody>
				<tr>
					<td><?php _e( 'Queue status', 'algolia' ); ?></td>
					<td>
							<span class="status-running">
								<strong><?php _e( 'Running', 'algolia' ); ?></strong>
							</span>
							<span class="status-idle">
								<strong><?php _e( 'Idle', 'algolia' ); ?></strong>
							</span>
					</td>
					<td>
							<span class="run-queue-link">
								<a href="#" class="page-title-action algolia-run-queue"><?php echo __( 'Process queue', 'algolia' ); ?></a>
							</span>
							<span class="stop-queue-link">
								<a href="#" class="page-title-action algolia-stop-queue"><?php echo __( 'Stop queue processing', 'algolia' ); ?></a>
							</span>
					</td>
				</tr>
				<tr>
					<td><?php _e( 'Pending tasks', 'algolia' ); ?></td>
					<td>
						<strong class="pending-tasks"><?php echo Algolia_Task::get_queued_tasks_count(); ?></strong>
					</td>
					<td>
						<a href="<?php echo admin_url( 'admin-post.php?action=algolia_delete_pending_tasks' ); ?>" id="delete-pending-tasks" class="page-title-action"><?php _e( 'Delete all pending tasks', 'algolia' ); ?></a>
					</td>
				</tr>
				<tr class="current-task">
					<td>
						<?php _e( 'Current task', 'algolia' ); ?>
					</td>
					<td class="current-task-name"></td>
					<td></td>
				</tr>
			</tbody>
		</table>

		<p class="failed-task error-message">
			<?php _e( 'One of your recent tasks failed. Please check the logs for more information.', 'algolia' ); ?>
		</p>
	</div>
	
	
	<form method="post" action="options.php">
		<?php
		settings_fields( $this->option_group );
		do_settings_sections( $this->slug );
		submit_button();
		?>
	</form>
</div>
