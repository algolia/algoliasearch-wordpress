<div class="wrap">
	<h1>
		<?php echo esc_html( get_admin_page_title() ); ?>
		<a href="<?php echo admin_url( 'admin-post.php?action=algolia_re_index_all' ); ?>" class="page-title-action"><?php _e( 'Re-index everything', 'algolia' ); ?></a>
	</h1>

	<div class="queue-status">
		<p>
			<?php _e( 'Task Queue pending tasks', 'algolia' ); ?> :
			<strong class="pending-tasks"><?php echo Algolia_Task::get_queued_tasks_count(); ?></strong>
		</p>
		<p>
			<?php _e( 'Task Queue status', 'algolia' ); ?>:
			
			<span class="status-running"><strong><?php _e( 'Running', 'algolia' ); ?></strong></span>
			
			<span class="status-idle">
				<strong><?php _e( 'Idle', 'algolia' ); ?>
					<span class="run-queue-link">
						| <a href="#" class="page-title-action algolia-run-queue"><?php echo __( 'Process queue', 'algolia' ); ?></a>
					</span>
				</strong>
			</span>
		</p>
		<p class="current-task">
			<?php _e( 'Current task', 'algolia' ); ?>:
			<span class="current-task-name"></span>
		</p>
	</div>

	<hr>
	
	<form method="post" action="options.php">
		<?php
		settings_fields( $this->option_group );
		do_settings_sections( $this->slug );
		submit_button();
		?>
	</form>
</div>
