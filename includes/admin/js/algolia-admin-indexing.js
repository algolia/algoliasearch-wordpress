(function( $ ) {
	'use strict';

	$(function() {
		// Indexing page.
		var queueStatus = {
			tasks: 0,
			running: false,
			current: false,
			recently_failed: false
		};

		var $pendingTasks = $(".pending-tasks");
		var $statusRunning = $(".status-running");
		var $statusIdle = $(".status-idle");
		var $runQueueLink = $(".run-queue-link");
		var $stopQueueLink = $(".stop-queue-link");
		var $currentTask = $(".current-task");
		var $currentTaskName = $(".current-task-name");
		var $taskFailed = $(".failed-task");
		var $deleteTasksBtn = $('#delete-pending-tasks');

		function refreshQueueStatus() {
			$.post("admin-ajax.php", {
				action: "algolia_queue_status"
			}, function(data) {
				queueStatus = data;
				updateDisplay();
				setTimeout(refreshQueueStatus, 3000);
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

			if(queueStatus.running && queueStatus.tasks > 0) {
				$statusRunning.show();
				$statusIdle.hide();
				$deleteTasksBtn.hide();
				$runQueueLink.hide();
				$stopQueueLink.show();
			} else {
				$statusRunning.hide();
				$statusIdle.show();
				$stopQueueLink.hide();

				if(queueStatus.tasks > 0) {
					$runQueueLink.show();
					$deleteTasksBtn.show();
				} else {
					$runQueueLink.hide();
					$deleteTasksBtn.hide();
				}
			}

			if(queueStatus.running && queueStatus.tasks > 0) {
				$currentTask.show();
			} else {
				$currentTask.hide();
			}

			if(queueStatus.recently_failed) {
				$taskFailed.show();
			} else {
				$taskFailed.hide();
			}

		}

		$runQueueLink.click(function(e) {
			e.preventDefault();
			$.post("admin-ajax.php", {
				action: "algolia_run_queue"
			});
		});

		$stopQueueLink.click(function(e) {
			e.preventDefault();
			$.post("admin-ajax.php", {
				action: "algolia_stop_queue"
			});
		});

		refreshQueueStatus();
	});
})( jQuery );
