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
					woo_product_auto_release_object.ajax_url,
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