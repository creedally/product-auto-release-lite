let product_const = 'product_var';
jQuery( document ).ready(
	function($) {
		$( document ).on(
			'click',
			'.voting-option-wrapper .notify-product-button' ,
			function() {
				let productId           = $( this ).attr( 'data-product-id' );
				let dataAction          = $( this ).attr( 'data-action' );
				let nonce               = $( 'input[name="_nonce"]' ).val();
				let data = {
					'action': 'wpar_notify_request',
					'product_id': productId,
					'data_action': dataAction,
					'_nonce': nonce,
				};
				jQuery.post(
					product_auto_release_object.ajax_url,
					data,
					function (response) {
						if (response.status) {
							$( '.woocommerce-notices-wrapper' ).html( '' ).html( get_message_html( response.message, response.type ) );
							$( 'html, body' ).animate(
								{
									scrollTop: $( "div.woocommerce-notices-wrapper" ).offset().top - 30
								},
								1000
							)
							if (response.reload) {
								setTimeout(
									function () {
										location.reload();
									},
									1000
								);
							}
						} else {
							if (response.reload) {
								location.reload();
							}
						}
					}
				);
			}
		);
		const counter_interval = setInterval( wp_auto_release_timer, 1000 );
	}
);

function get_message_html( message = '', type= "success" ){
	let structure = '';
	if ( "success" === type ) {
		structure = '<div class="woocommerce-message" role="alert">' + message + '</div>';
	} else if ( "error" === type ) {
		structure = '<ul class="woocommerce-error" role="alert"> <li>' + message + '</li></ul>';
	}
	return structure;
}

function wp_auto_release_timer() {

	auto_release_date = jQuery( '.wpar-timer' ).attr( 'data-available-time' );
	auto_release_date = Date.parse( auto_release_date );

	if ( undefined !== auto_release_date && NaN !== auto_release_date && '' !== auto_release_date ) {
		let now  = new Date();
		let diff = auto_release_date - now;

		let days  = Math.floor( diff / (1000 * 60 * 60 * 24) );
		let hours = Math.floor( diff / (1000 * 60 * 60) );
		let mins  = Math.floor( diff / (1000 * 60) );
		let secs  = Math.floor( diff / 1000 );

		let d = days;
		let h = hours - days * 24;
		let m = mins - hours * 60;
		let s = secs - mins * 60;


		if ( null !== d && undefined !== d && 0 > d  ) {

			try {
				if ( counter_interval !== undefined ) {
					clearInterval( counter_interval );
				}
			} catch (err) {

				try {
					if ( product_const !== undefined && product_const !== '' ) {
						product_available();
						product_const = '';
					}
				} catch (err) {
				}
			}
		} else {
			jQuery( '.wpar-timer' ).html( '' ).html( '<div>' + d + '<span>' + product_auto_release_object.days + '</span></div>' + '<div>' + h + '<span>' + product_auto_release_object.hours + '</span></div>' + '<div>' + m + '<span>' + product_auto_release_object.minutes + '</span></div>' + '<div>' + s + '<span>' + product_auto_release_object.seconds + '</span></div>' );
		}
	}
}

function product_available(){
	let productId   = jQuery( '.wc-auto-release-wrap' ).attr( 'data-product-id' );
	let nonce 		= jQuery( 'input[name="_nonce"]' ).val();

	let data = {
		'action': 'notify_product_release',
		'product_id': productId,
		'_nonce': nonce,
	};
	jQuery.post(
		product_auto_release_object.ajax_url,
		data,
		function(response) {
			if (response.status) {
				jQuery( '.notify-email-field' ).val( '' );
				jQuery( '.woocommerce-notices-wrapper' ).html( '' ).html( get_message_html( response.message, response.type ) );
				product_const = '';
				jQuery( 'html, body' ).animate(
					{
						scrollTop: jQuery( "div.woocommerce-notices-wrapper" ).offset().top - 30
					},
					1000
				)
				if (response.reload) {
					setTimeout(
						function() {
							location.reload();
						},
						1000
					);
				}
			} else {
				if (response.reload) {
					location.reload();
				}
			}
		}
	);
}

function wp_auto_release_validate_email(mail) {

	if ( mail.match( /^\w+([\.-]?\w+)*@\w+([\.-]?\w+)*(\.\w{2,3})+$/ ) ) {
		return true;
	} else {
		return false;
	}
}
