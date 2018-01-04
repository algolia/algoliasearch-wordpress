(function($) {

	$(
		function() {
			var $buttons = $( '.algolia-push-settings-button' );
			$buttons.on( 'click', handleButtonClick );
		}
	);

	function handleButtonClick(e) {
		$clickedButton = $( e.currentTarget );
		var index      = $clickedButton.data( 'index' );
		if ( ! index) {
			throw new Error( 'Clicked button has no "data-index" set.' );
		}

		disableButton( $clickedButton );

		pushSettings( $clickedButton, index );
	}

	function disableButton($button) {
		$button.prop( 'disabled', true );
	}

	function enableButton($button) {
		$button.prop( 'disabled', false );
	}

	function pushSettings($clickedButton, index) {

		var data = {
			'action': 'algolia_push_settings',
			'index_id': index
		};

		$.post(
			ajaxurl, data, function(response) {
				if (typeof response.success === 'undefined') {
					alert( 'An error occurred' );
					enableButton( $clickedButton );
					return;
				}

				alert( 'Settings correctly pushed for index: ' + index );
				enableButton( $clickedButton );
			}
		).fail(
			function(response) {
				alert( 'An error occurred: ' + response.responseText );
				enableButton( $clickedButton );
			}
		);
	}

})( jQuery );
