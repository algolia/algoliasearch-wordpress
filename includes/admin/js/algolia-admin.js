(function( $ ) {
	'use strict';

	$(
		function() {

			function updateAutocompletePositions () {
				$( '.table-autocomplete .position-input' ).each(
					function(index, value) {
						$( value ).val( index );
					}
				);
			}
			$( '.table-autocomplete tbody' ).sortable(
				{
					update: function() {
						updateAutocompletePositions();
					}
				}
			);
		}
	);
})( jQuery );
