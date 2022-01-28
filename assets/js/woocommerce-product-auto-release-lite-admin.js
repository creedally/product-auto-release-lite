let ready = jQuery( document ).ready(
	function($) {

		var dateToday = new Date();
		$( '.auto-release-date' ).datetimepicker(
			{
				minDate: dateToday,
				format:'Y-m-d H:i',
			}
		);

		const notify_checkbox = function( option ) {

			if ( $( '.' + option ).prop( "checked" ) === true) {
				$( '.wc-notify-general-field' ).show();
			} else {
				$( '.wc-notify-general-field' ).hide();
			}
		}

		const notify_product_lead = function ( option1, option2 ){

			if( $( '.' + option1 ).prop( "checked" ) === true && $( '.' + option2 ).prop( "checked" ) === true ){
				$('#notify_product_lead').attr('min', 1 );
			}else{
				$('#notify_product_lead').attr('min','0');
			}

		}

		const notification_checkbox = function( option ) {

			if ( $( '.' + option ).prop( "checked" ) === true) {
				if ( $( '.wc-notify-checkbox' ).prop( "checked" ) === true ) {
					$( '.wc-notify-fields' ).show();
				} else {
					$( '.wc-notify-fields' ).hide();
				}
			} else {
				$( '.wc-notify-fields' ).hide();
			}
			notify_product_lead('wc-notify-checkbox', 'wc-enable-notification-checkbox');
		}

		const auto_release_checkbox = function( option ) {

			if ( $( '.' + option ).prop( "checked" ) === true) {
				if ( $( '.wc-notify-checkbox' ).prop( "checked" ) === true ) {
					$( '.wc-auto-release-fields' ).show();
				} else {
					$( '.wc-auto-release-fields' ).hide();
				}
			} else {
				$( '.wc-auto-release-fields' ).hide();
			}
		}

		jQuery( '.wc-notify-checkbox' ).change(
			function () {
				notify_checkbox( 'wc-notify-checkbox' );
				notification_checkbox( 'wc-enable-notification-checkbox' );
				notify_product_lead('wc-notify-checkbox', 'wc-enable-notification-checkbox');
				auto_release_checkbox( 'enable-email-auto-release-checkbox' );
			}
		).change();

		jQuery( '.wc-enable-notification-checkbox' ).change(
			function () {
				notification_checkbox( 'wc-enable-notification-checkbox' );
			}
		).change();

		jQuery( '.enable-email-auto-release-checkbox' ).change(
			function () {
				auto_release_checkbox( 'enable-email-auto-release-checkbox' );
			}
		).change();

	}
);


