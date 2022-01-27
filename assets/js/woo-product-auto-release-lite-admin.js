let ready = jQuery( document ).ready(
	function($) {
		const notify_checkbox = function( option ) {
			if ( $( '.' + option ).prop( "checked" ) === true) {
				$( '.wc-notify-general-field' ).show();
				$( '#notify_product_lead' ).attr( 'min','1' );
			} else {
				$( '.wc-notify-general-field' ).hide();
				$( '#notify_product_lead' ).attr( 'min','0' );
			}
		}
		jQuery( '.wc-notify-checkbox' ).change(
			function () {
				notify_checkbox( 'wc-notify-checkbox' );
			}
		).change();

	}
);
