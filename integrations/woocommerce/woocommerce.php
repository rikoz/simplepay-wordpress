<?php

/**
 * WooCommerce Integration class
 */

// Exit if accessed directly.
if (!defined('ABSPATH')) {
    exit;
}

// SimplePay Payment Class
if (!function_exists('simplepay_woocommerce_errorHandling')) {
    function simplepay_woocommerce_errorHandling($errors)
    {
        if (!defined('SIMPLEPAY_WOO_NOTICES_SENT')) {
            foreach ($errors as $error) {
                wc_add_notice('<div class="simplepay_error">' . $error . '</div>', 'error');
            }
            define('SIMPLEPAY_WOO_NOTICES_SENT', true);
        }
    }
}

function init_simplepay_gateway_class()
{
    global $wpdb;

    if (class_exists('WC_Payment_Gateway')) {

        class WC_Gateway_SimplePay_Gateway extends WC_Payment_Gateway
        {

            /**
             * Constructor
             */
            public function __construct()
            {

                $this->id = 'simplepay';
                $this->icon = plugins_url('', __FILE__) . '/../img/icon.png';
                $this->logo = plugins_url('', __FILE__) . '/../img/logo.png';
                $this->logo_small = plugins_url('', __FILE__) . '/../img/logo.png';
                $this->order_button_text = __('Proceed with SimplePay', 'woocommerce');
                $this->has_fields = true;

                add_action('woocommerce_update_options_payment_gateways_' . $this->id, array($this, 'process_admin_options'));

                // Load the form fieldsx
                $this->init_form_fields();

                // Load the settings
                $this->init_settings();

                // Get setting values
                $this->title = $this->get_option('title');
                $this->description = $this->get_option('description');
                $this->email = $this->get_option('email');

                // Get SimplePay admin settings
                $this->simplepay_admin_settings();

                // Hooks
                add_action('woocommerce_after_checkout_validation', array($this, 'simplepay_transaction_id_field_process'));
                add_action('woocommerce_checkout_update_order_meta', array($this, 'simplepay_transaction_id_field_update_order_meta'));
                add_action('woocommerce_admin_order_data_after_billing_address', array($this, 'simplepay_transaction_id_field_display_admin_order_meta'), 10, 1);
            }

            /**
             * SimplePay API Keys
             */
            protected function simplepay_admin_settings()
            {
                global $woocommerce;
                global $wp_version;

                $settingsDB = SimplePay_DB::get_instance()->load_admin_data();
                $admin_settings = $settingsDB[0];

                SimplePayPaymentsLibrary::sandboxMode($admin_settings->simplepay_test_mode);
                SimplePayPaymentsLibrary::liveKeys(
                    Array(
                        'public' => $admin_settings->simplepay_live_public_api_key,
                        'private' => $admin_settings->simplepay_live_private_api_key
                    )
                );

                SimplePayPaymentsLibrary::testKeys(
                    Array(
                        'public' => $admin_settings->simplepay_test_public_api_key,
                        'private' => $admin_settings->simplepay_test_private_api_key
                    )
                );

                SimplePayPaymentsLibrary::customCheckOutDescription($admin_settings->simplepay_description);
                SimplePayPaymentsLibrary::customCheckOutImg($admin_settings->simplepay_custom_image_url);
                SimplePayPaymentsLibrary::completeTransactionURL($admin_settings->simplepay_button_checkout_url);
                SimplePayPaymentsLibrary::platform('Wordpress ' . $wp_version . ' WooCommerce ' . $woocommerce->version);

            }

            /**
             * Woocommerce gateway icon
             */
            public function get_icon()
            {
                $icon = '';
                if ($this->icon) {
                    $icon = '<img class="simplepay-woocommerce-checkout-logo" src="' . plugins_url('integrations/woocommerce/assets/img/logo-checkout.png', SP_MAIN_FILE) . '" alt="' . esc_attr($this->get_title()) . '" />
				<a href="https://www.simplepay.ng/" class="simplepay-woocommerce-checkout-learn-more" target="_blank">Learn about SimplePay</a>';
                }

                return apply_filters('woocommerce_gateway_icon', $icon, $this->id);
            }

            /**
             * Woocommerce gateway description
             */
            public function get_description()
            {
                $description = $this->description . '<script>var simplepay_cart_total = "' . WC()->cart->total . '";</script>';

                return apply_filters('woocommerce_gateway_description', $description, $this->id);
            }

            /**
             * Initialise Gateway Settings Form Fields
             */
            public function init_form_fields()
            {

                $this->form_fields = array(
                    'enabled' => array(
                        'title' => __('Enable/Disable', 'woocommerce'),
                        'type' => 'checkbox',
                        'label' => __('Enable SimplePay Payment', 'woocommerce'),
                        'default' => 'yes'
                    ),
                    'title' => array(
                        'title' => __('Title', 'woocommerce'),
                        'type' => 'text',
                        'description' => __('This controls the title which the user sees during checkout.', 'woocommerce'),
                        'default' => __('Debit/ Credit Cards', 'woocommerce'),
                        'desc_tip' => true,
                    ),
                    'description' => array(
                        'title' => __('Description', 'woocommerce'),
                        'description' => __('This controls the description which the user sees during checkout.', 'woocommerce'),
                        'type' => 'textarea',
                        'default' => 'Online and Mobile Payment. Secure. Simple.'
                    )
                );
            }


            /**
             * Add simplepay_transaction_id field to checkout form
             *
             * Hidden to control the form status along with the payment popup
             */
            function simplepay_transaction_id_field($checkout)
            {

                woocommerce_form_field('simplepay_transaction_id', array(
                    'type' => 'text',
                    'required' => true
                ), $checkout->get_value('simplepay_transaction_id'));
            }

            /**
             * Process the checkout - error message
             * Error on this field will be hidden - do not validate on the form submission
             */
            function simplepay_transaction_id_field_process()
            {
                return true;
            }


            /**
             * Update the order meta with simplepay_transaction_id field value
             */
            function simplepay_transaction_id_field_update_order_meta($order_id)
            {
                if (!empty($_POST['simplepay_transaction_id'])) {
                    update_post_meta($order_id, 'SimplePay Transaction ID', sanitize_text_field($_POST['simplepay_transaction_id']));
                }
            }

            /**
             * Display simplepay_transaction_id field value on the order edit page
             */
            function simplepay_transaction_id_field_display_admin_order_meta($order)
            {
                echo '<p class="form-field"><strong>' . __('SimplePay Transaction ID') . ':</strong><br/>' . get_post_meta($order->id, 'SimplePay Transaction ID', true) . '</p>';
            }


            function payment_fields()
            {

                echo 'Master Card, Visa and Verve (Processed securely by SimplePay)';

                SimplePayPaymentsLibrary::initializeGateway('checkout', 'preventFunction');
                echo '<script>localStorage.removeItem("simplepay_payed");</script>';
                SimplePayPaymentsLibrary::paymentForms(false);

                $transaction = new SimplePayTransaction(
                    Array(
                        'amount' => WC()->cart->total,
                        'currency' => get_woocommerce_currency(),
                        'transactionId' => ''
                    )
                );

                $order_data = '';
                $cart_contents = WC()->cart->cart_contents;
                $cart_contents_values = array_values($cart_contents);
                if (!empty($cart_contents_values)) {
                    $order_data = $cart_contents_values[0]['data']->id;
                }

                echo '
                
                    <script>
                    
                        document.addEventListener(\'DOMContentLoaded\', function() {
                        
                            
                                              
                            jQuery(\'body\').on(\'click\', \'input[name="woocommerce_checkout_place_order"]\', function (e) {
                              
                                var localVar = localStorage.getItem("simplepay_payed")
                                
                                if(localVar === null ){
                                    e.preventDefault();
                                    checkout_page = jQuery(document.checkout);

                                    jQuery.ajax({
                                        type: \'POST\',
                                        url: "' . get_site_url() . '/index.php?wc-ajax=checkout",
                                        data: checkout_page.serializeArray(),
                                        dataType: \'json\',
                                        success: function (response) {
                                                                                                
                                            if(response.messages == ""){
                                            
                                                localStorage.setItem("simplepay_payed","a");
                                                document.checkout.woocommerce_checkout_place_order.click()
                                                    
                                            }
                                            else{
                                                checkout_page.prepend(response.messages);
                                                jQuery(\'html, body\').animate({
                                                    scrollTop: checkout_page.offset().top - 100
                                                 }, 1000);
                                            }
                                        }
                                
                                    });
                                }
                              
                                
                                if(localVar === "a"){
                                    e.preventDefault();

                                    clientInformation = {
                                       email: jQuery(\'input[name="billing_email"]\').val(),
                                       phone: jQuery(\'input[name="billing_phone"]\').val(),
                                       description: "' . SimplePayPaymentsLibrary::customCheckOutDescription() . '" + "  Order # " + "' . $order_data . '",
                                       address: jQuery(\'input[name="billing_address_1"]\').val() + \' \' + jQuery(\'input[name="billing_address_2"]\').val(),
                                       postal_code: jQuery(\'input[name="billing_postcode"]\').val(),
                                       city: jQuery(\'input[name="billing_city"]\').val(),
                                       country: jQuery(\'#billing_country\').val(),
                                    }
                                    
                                    
                                    preventFunction = function(){
                                        localStorage.setItem("simplepay_payed","b");
                                        document.checkout.woocommerce_checkout_place_order.click();
                                    }
                                    
                                    ' . SimplePayPaymentsLibrary::paymentButton($transaction, 'clientInformation') . '
                                
                                }
                                
                                if(localVar === "b"){
                                    return true;
                                }
                                
                            });
                          
                        }, false);
                 
                    </script>
                ';

            }


            /**
             * Submit payment
             */
            public function process_payment($order_id)
            {

                $verify_result = SimplePayPaymentsLibrary::completeTransaction($order_id, $_POST['simplepay_token'], $_POST['simplepay_amount'], $_POST['simplepay_currency'], 'simplepay_callback');

                if ($verify_result['verified']) {

                    // Update the order meta with the new transaction id
                    update_post_meta($verify_result['purchaseId'], 'SimplePay Transaction ID', $verify_result['response']['id']);
                    $order = wc_get_order($verify_result['purchaseId']);

                    // Complete the payment and reduce stock levels
                    $order->payment_complete();

                    // Remove cart
                    WC()->cart->empty_cart();

                    return array(
                        'result' => 'success',
                        'redirect' => $this->get_return_url($order)
                    );
                }

            }
        }
    }
}

add_action('plugins_loaded', 'init_simplepay_gateway_class');

function add_simplepay_gateway_class($methods)
{
    $methods[] = 'WC_Gateway_SimplePay_Gateway';
    return $methods;
}

add_filter('woocommerce_payment_gateways', 'add_simplepay_gateway_class');
