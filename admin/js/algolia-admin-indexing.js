(function( $ ) {
	'use strict';

	$(function() {
		// Indexing page.
		var queueStatus = {
			tasks: 0,
			running: false,
			current: false
		};

		var $pendingTasks = $(".pending-tasks");
		var $statusRunning = $(".status-running");
		var $statusIdle = $(".status-idle");
		var $runQueueLink = $(".run-queue-link");
		var $currentTask = $(".current-task");
		var $currentTaskName = $(".current-task-name");

		function refreshQueueStatus() {
			$.post("admin-ajax.php", {
				action: "algolia_queue_status"
			}, function(data) {
				queueStatus = data;
				updateDisplay();
			});
		}

		function updateDisplay() {
			// Update the number of pending tasks.
			$pendingTasks.html(queueStatus.tasks);

			var taskName = "";
			if(queueStatus.current) {
				taskName = queueStatus.current.name;

				if(queueStatus.current['page'] !== undefined && queueStatus.current['max_num_pages'] !== undefined) {
					taskName += " ( page " + queueStatus.current['page'] + " of " + queueStatus.current['max_num_pages'] + " )";
				}
			}
			$currentTaskName.html(taskName);

			if(queueStatus.running) {
				$statusRunning.show();
				$statusIdle.hide();
			} else {
				$statusRunning.hide();
				$statusIdle.show();

				if(queueStatus.tasks > 0) {
					$runQueueLink.show();
				} else {
					$runQueueLink.hide();
				}
			}

			if(queueStatus.running && queueStatus.tasks > 0) {
				$currentTask.show();
			} else {
				$currentTask.hide();
			}
		}

		$(".algolia-run-queue").click(function(e) {
			e.preventDefault();
			$.post("admin-ajax.php", {
				action: "algolia_run_queue"
			});
			refreshQueueStatus();
		});

		refreshQueueStatus();

		// Refresh every 3 seconds.
		setInterval(refreshQueueStatus, 3000);
	});
})( jQuery );
