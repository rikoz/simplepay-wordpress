

//TODO:
// add description

var give_global_vars;
jQuery( document ).ready( function ( $ ) {

	jQuery( 'body' ).on( 'submit', '.give-form', function ( event ) {
		
		var handler = SimplePay.configure({
			token: give_simplepay_response_handler, 
			key: give_simplepay_vars.public_key,
			platform: 'wordpress-givewp-' + give_simplepay_vars.simplepay_plugin_version,
			image: give_simplepay_vars.custom_image
		});

		var $form = jQuery( this );

		if ( $form.find( 'input.give-gateway:checked' ).val() == 'simplepay' ) {
			
			event.preventDefault();
			$form.addClass( 'simplepay-checkout' );

			// disable the submit button to prevent repeated clicks
			//$form.find( '#give-purchase-button' ).attr( 'disabled', 'disabled' );

			// call SimplePay
			handler.open(SimplePay.CHECKOUT,
			{
				email: $form.find( '#give-email' ).val(),
				amount: SimplePay.amount_to_lower(jQuery('.give-final-total-amount').data('total')),
				currency: 'NGN', 
				description: 'Donation'
		   });

		}

	} );

} );

function give_simplepay_response_handler( token ) {
	var $form = jQuery( '.give-form.simplepay-checkout' );

	// insert the token into the form so it gets submitted to the server
	$form.append( "<input type='hidden' name='give_simplepay_token' value='" + token + "' />" );
	$form.append( "<input type='hidden' name='give_simplepay_amount' value='" + SimplePay.amount_to_lower(jQuery('.give-final-total-amount').data('total')) + "' />" );
	$form.append( "<input type='hidden' name='give_simplepay_currency' value='NGN' />" );

	// and submit
	$form.get( 0 ).submit();
}

