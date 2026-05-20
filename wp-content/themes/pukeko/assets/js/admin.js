/* global ajaxurl, pukekoNUX */
( function( wp, $ ) {
	'use strict';

	if ( ! wp ) {
		return;
	}

	$( function() {

		// Dismiss notice
		$( document ).on( 'click', '.sf-notice-nux .notice-dismiss', function() {
			$.ajax({
				type:     'POST',
				url:      ajaxurl,
				data:     { nonce: pukekoNUX.nonce, action: 'pukeko_dismiss_notice' },
				dataType: 'json'
			});
		});
	});
})( window.wp, jQuery );


// Add Color Picker with Opacity
jQuery( document ).ready( function( $ ) {
	$( 'input.alpha-color-picker' ).alphaColorPicker();
});
