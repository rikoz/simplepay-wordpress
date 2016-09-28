

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

			var payValue = give_simplepay_convert_value(jQuery('.give-final-total-amount').data('total'));

			// call SimplePay
			handler.open(SimplePay.CHECKOUT,
			{
				email: $form.find( '#give-email' ).val(),
				amount: SimplePay.amount_to_lower(payValue),
				currency: 'NGN', 
				description: 'Donation'
		   });

		}

	} );

} );

function give_simplepay_response_handler( token, paid ) {
	var $form = jQuery( '.give-form.simplepay-checkout' );
	var payValue = give_simplepay_convert_value(jQuery('.give-final-total-amount').data('total'));

	// insert the token into the form so it gets submitted to the server
	$form.append( "<input type='hidden' name='give_simplepay_token' value='" + token + "' />" );
	$form.append( "<input type='hidden' name='give_simplepay_amount' value='" + SimplePay.amount_to_lower(payValue) + "' />" );
	$form.append( "<input type='hidden' name='give_simplepay_currency' value='NGN' />" );
	$form.append( "<input type='hidden' name='give_simplepay_status' value='" + paid + "' />" );

	// and submit
	$form.get( 0 ).submit();
}

function give_simplepay_convert_value(value){
	if (typeof value === 'string'){
		return value.replace(give_global_vars.thousands_separator,'')
	} else if (typeof value === 'number'){
		return value
	}
}

