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
                wp_enqueue_script('simplepay-js', 'https://checkout.simplepay.ng/v2/simplepay.js', array(), false, true);
                add_action('wp_enqueue_scripts', array($this, 'payment_scripts'));

                add_action('woocommerce_after_checkout_validation', array($this, 'simplepay_transaction_id_field_process'));
                add_action('woocommerce_checkout_update_order_meta', array($this, 'simplepay_transaction_id_field_update_order_meta'));
                add_action('woocommerce_admin_order_data_after_billing_address', array($this, 'simplepay_transaction_id_field_display_admin_order_meta'), 10, 1);
            }

            /**
             * SimplePay API Keys
             */
            protected function simplepay_admin_settings()
            {

                $settingsDB = SimplePay_DB::get_instance()->load_admin_data();
                $admin_settings = $settingsDB[0];

                if ($admin_settings->simplepay_test_mode == 1) {
                    $this->public_key = $admin_settings->simplepay_test_public_api_key;
                    $this->private_key = $admin_settings->simplepay_test_private_api_key;

                } else {
                    $this->public_key = $admin_settings->simplepay_live_public_api_key;
                    $this->private_key = $admin_settings->simplepay_live_private_api_key;
                }

                $this->custom_description = $admin_settings->simplepay_description;
                $this->custom_image = $admin_settings->simplepay_custom_image_url;
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
             * Payment scripts
             */
            public function payment_scripts()
            {

                wp_enqueue_script('payment', SP_DIR_URL . 'lib/js/woocommerce.js', array('jquery'), SP_PAYMENT_SCRIPT_VERSION, true);

                wp_enqueue_style('woocommerce_checkout', SP_DIR_URL . 'integrations/woocommerce/assets/css/checkout-page.css');

                $order_data = '';
                $cart_contents = WC()->cart->cart_contents;
                $cart_contents_values = array_values($cart_contents);
                if (!empty($cart_contents_values)) {
                    $order_data = $cart_contents_values[0]['data']->id;
                }

                wp_localize_script('payment', 'checkout_page', array(
                    'ajax_url' => '?wc-ajax=checkout',
                    'currency' => get_woocommerce_currency(),
                    'public_key' => $this->public_key,
                    'order' => $order_data,
                    'title' => get_bloginfo('name'),
                    'description' => $this->custom_description,
                    'custom_image' => $this->custom_image,
                    'simplepay_plugin_version' => SP_PLUGIN_VERSION,
                ));
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

            /**
             * Submit payment
             */
            public function process_payment($order_id)
            {
                if (!empty($_POST['simplepay_transaction_id'])) {
                    $verified_transaction = verify_transaction(
                        $_POST['simplepay_transaction_id'],
                        WC()->cart->total * 100,
                        get_woocommerce_currency(),
                        $this->private_key);

                    if ($verified_transaction['verified'] || $_POST['simplepay_transaction_status'] === 'true') {
                        // Update the order meta with the new transaction id
                        update_post_meta($order_id, 'SimplePay Transaction ID', $verified_transaction['response']['id']);

                        $order = wc_get_order($order_id);

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
}

add_action('plugins_loaded', 'init_simplepay_gateway_class');

function add_simplepay_gateway_class($methods)
{
    $methods[] = 'WC_Gateway_SimplePay_Gateway';
    return $methods;
}

add_filter('woocommerce_payment_gateways', 'add_simplepay_gateway_class');
