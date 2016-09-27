/***
 * Show trusted confirmation for mobile payments
 **/
function showMobilePayment() {
    var mobilePaymentDivOverlay = jQuery('<div>', {
        id: 'spMobilePaymentOverlay'
    })
        .css({
            'width': '100%',
            'height': '100%',
            'position': 'fixed',
            'left': 0,
            'top': 0,
            'zIndex': 99998,
            'overflow': 'hidden',
            'background': 'rgba(0, 0, 0, 0.6)'
        })
        .appendTo('body');

    var mobilePaymentDiv = jQuery('<div>')
        .css({
            'position': 'fixed',
            'left': '2.8%',
            'top': '17%',
            'width': '94%',
            'height': 'auto',
            'padding': '10% 0 12%',
            'border-radius': '5px',
            'background': '#fcfcfc ',
            'overflow': 'hidden',
            'text-align': 'center'
        })
        .appendTo(mobilePaymentDivOverlay);

    jQuery('<img>')
        .css({
            'font-family': "'Open Sans', 'Helvetica Neue', Helvetica, Arial, sans-serif",
            'font-size': '22px',
            'width': '70%',
            'text-align': 'center',
            'color': '#828282'
        })
        .attr('src', jQuery('.simplepay-woocommerce-checkout-logo').attr('src'))
        .appendTo(mobilePaymentDiv);

    jQuery('<button>', {
        id: 'spProceedToMobilePayment'
    })
        .css({
            'width': '80%',
            'border-radius': '5px',
            'border': 'none',
            'margin': '26px 0 0 10%',
            'font-size': '17px',
            'font-weight': 'bold',
            'color': '#f8f8f8',
            'background': '#bc75f3',
            'cursor': 'pointer',
            'outline': 'none',
            'float': 'left'
        })
        .html('Proceed to mobile payment')
        .appendTo(mobilePaymentDiv);

}

jQuery(document).ready(function (jQuery) {
    var checkoutForm = jQuery('form.checkout');
    checkoutForm.append('<input type="text" id="simplepay_transaction_id" name="simplepay_transaction_id" required="true"/>');
    checkoutForm.append('<input type="text" id="simplepay_transaction_status" name="simplepay_transaction_status" required="true"/>');

    jQuery('body').on('click', 'input[name="woocommerce_checkout_place_order"]', function (e) {
        if (jQuery('#payment_method_simplepay').is(':checked')) {
            e.preventDefault();

            jQuery.ajax({
                type: 'POST',
                url: checkout_page.ajax_url,
                data: checkoutForm.serialize(),
                dataType: 'json',
                success: function (response) {
                    if (response.result === 'failure' && !response.messages) {
                        var WCPhone = '';
                        var WCAddress = '';
                        var WCCity = '';
                        var WCCountry  = '';
                        var WCPostCode = '';
                        var WCEmail = '';

                        if (jQuery('input[name="billing_phone"]').val()){
                            WCPhone = jQuery('input[name="billing_phone"]').val();
                        }

                        if (jQuery('input[name="billing_address_1"]').val()){
                            WCAddress = jQuery('input[name="billing_address_1"]').val() + ' ' + jQuery('input[name="billing_address_2"]').val();
                        }

                        if (jQuery('input[name="billing_city"]').val()){
                            WCCity = jQuery('input[name="billing_city"]').val();
                        }

                        if (jQuery('#billing_country').val()){
                            WCCountry = jQuery('#billing_country').val();
                        }

                        if (jQuery('input[name="billing_postcode"]').val()){
                            WCPostCode = jQuery('input[name="billing_postcode"]').val();
                        }

                        if (jQuery('input[name="billing_email"]').val()){
                            WCEmail = jQuery('input[name="billing_email"]').val();
                        }

                        // Payment popup
                        var handler = SimplePay.configure({
                            token: function (token, paid) {
                                jQuery('#simplepay_transaction_id').val(token);
                                jQuery('#simplepay_transaction_status').val(paid);

                                // Proceed with the form submission
                                checkoutForm.submit();
                            },
                            key: checkout_page.public_key,
                            platform: 'wordpress-woocommerce-' + checkout_page.simplepay_plugin_version,
                            image: checkout_page.custom_image
                        });

                        var customDescription = checkout_page.description;
                        if (!customDescription) {
                            customDescription = checkout_page.title;
                        }
                        var paymentData = {
                            email: WCEmail,
                            phone: WCPhone,
                            description: customDescription + ' - Order #' + checkout_page.order,
                            address: WCAddress,
                            postal_code: WCPostCode,
                            city: WCCity,
                            country: WCCountry,
                            amount: SimplePay.amountToLower(simplepay_cart_total),
                            currency: checkout_page.currency
                        };

                        if (window.mobilecheck()) {
                            showMobilePayment();

                            jQuery('body').on('click', '#spProceedToMobilePayment', function () {
                                jQuery('#spMobilePaymentOverlay').remove();
                                handler.open(SimplePay.CHECKOUT, paymentData);
                            });

                        } else {
                            handler.open(SimplePay.CHECKOUT, paymentData);
                        }
                    } else {
                        // Proceed with the form submission - output form validation error
                        checkoutForm.submit();
                    }
                }
            });

        } else {
            // This field is required, need to have a some value to avoid conflicts with other payment gateways
            jQuery('#simplepay_transaction_id').val('#');

            // Proceed with the form submission
            checkoutForm.submit();
        }
    });
});
