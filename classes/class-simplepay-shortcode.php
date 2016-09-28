<?php

// Exit if accessed directly.
if (!defined('ABSPATH')) {
    exit;
}

if (!class_exists('SimplePay_PaymentsShortcode')) {

    class SimplePay_PaymentsShortcode
    {

        var $SimplePayAdminSettings = null;

        /**
         * Instance of this class.
         *
         * @since    1.0.0
         *
         * @var      object
         */
        protected static $instance = null;
        protected static $payment_buttons = array();

        function __construct()
        {
            $settingsDB = SimplePay_DB::get_instance()->load_admin_data();
            $this->SimplePayAdminSettings = $settingsDB[0];

            if ($this->SimplePayAdminSettings->simplepay_test_mode == 1) {
                $this->public_key = $this->SimplePayAdminSettings->simplepay_test_public_api_key;
                $this->private_key = $this->SimplePayAdminSettings->simplepay_test_private_api_key;

            } else {
                $this->public_key = $this->SimplePayAdminSettings->simplepay_live_public_api_key;
                $this->private_key = $this->SimplePayAdminSettings->simplepay_live_private_api_key;
            }

            add_shortcode('accept_simplepay_button_payment', array(&$this, 'shortcode_accept_simplepay_button_payment'));
            add_shortcode('accept_simplepay_button_payment_checkout', array(&$this, 'shortcode_accept_simplepay_button_payment_checkout'));
            if (!is_admin()) {
                add_filter('widget_text', 'do_shortcode');
            }

        }

        public function interfer_for_redirect()
        {
            global $post;
            if (!is_admin()) {
                if (has_shortcode($post->post_content, 'accept_simplepay_button_payment_checkout')) {
                    $this->shortcode_accept_simplepay_button_payment_checkout();
                    exit;
                }
            }
        }

        /**
         * Return an instance of this class.
         *
         * @since     1.0.0
         *
         * @return    object    A single instance of this class.
         */
        public static function get_instance()
        {

            // If the single instance hasn't been set, set it now.
            if (null == self::$instance) {
                self::$instance = new self;
            }

            return self::$instance;
        }

        function shortcode_accept_simplepay_button_payment($atts, $content = "")
        {

            // Enqueue the simplepay script
            wp_enqueue_script('simplepay-js', 'https://checkout.simplepay.ng/v2/simplepay.js', array(), false, false);

            extract(shortcode_atts(array(
                'name' => 'Item Name',
                'price' => '0',
                'quantity' => 'NA',
                'download_url' => '',
                'redirect_url' => '',
                'currency' => 'NGN',
                'button_text' => $this->SimplePayAdminSettings->simplepay_button_default_text,
                'button_class' => '',
                'button_style' => '',
                'fee_amount' => '',
                'fee_label' => ''
            ), $atts));

            if (!empty($download_url)) {
                $download_url = openssl_encrypt($download_url, 'AES-256-CBC', $this->SimplePayAdminSettings->simplepay_button_encrypt_key, 0, '1234567891234567');
            } else
                $download_url = '';

            if (!empty($redirect_url)) {
                $redirect_url = openssl_encrypt($redirect_url, 'AES-256-CBC', $this->SimplePayAdminSettings->simplepay_button_encrypt_key, 0, '1234567891234567');
            } else
                $redirect_url = '';

            $quantity = strtoupper($quantity);
            if ("$quantity" === "N/A") $quantity = "NA";

            $form_id = 'simplepay_form_' . count(self::$payment_buttons);
            self::$payment_buttons[] = $form_id;
            $paymentAmount = ("$quantity" === "NA" ? $price : ($price * $quantity));
            $priceInCents = $paymentAmount * 100;

            $feeInCents = '';
            if (!empty($fee_amount)) {
                $feeInCents = $fee_amount * 100;
            }

            $description = '';

            if ("$quantity" === "NA")
                $description = "{$name}";
            else
                $description .= "{$name} - {$quantity} piece" . ($quantity <> 1 ? "s" : "") . " for {$paymentAmount} {$currency}";

            $output = "<form action='" . $this->SimplePayAdminSettings->simplepay_button_checkout_url . "' METHOD='POST' id='" . $form_id . "'> ";
            $output .= "<button type='button' id='" . $form_id . "_button' class='" . $button_class . "' style='" . $button_style . "'>{$button_text}</button>";
            $output .= "
						<script>
						jQuery( document ).ready(function(){
							var handler = SimplePay.configure({
								token: function (token, paid) {
									// put token and transaction ID to be sent forward
									jQuery('#" . $form_id . "').append(
										jQuery('<input />', { name: 'token', type: 'hidden', value: token })
									);
									jQuery('#" . $form_id . "').append(
										jQuery('<input />', { name: 'simplepay_status', type: 'hidden', value: paid })
									);
									jQuery('#" . $form_id . "').submit();
								} ,
								key: '" . $this->public_key . "',
								platform: 'wordpress-paybutton-" . SP_PLUGIN_VERSION . "',
								image: '" . $this->SimplePayAdminSettings->simplepay_custom_image_url . "'        
							});
							jQuery('#" . $form_id . "_button').on('click', function (e) {
								handler.open(SimplePay.CHECKOUT,
								{
									description: '{$description}',
									amount: '{$priceInCents}',
									currency: 'NGN',
									fee_amount: '{$feeInCents}',
									fee_label: '{$fee_label}'
								});
							});
						});
						</script>
				";

            $output .= "<input type='hidden' value='{$name}' name='item_name' />";
            $output .= "<input type='hidden' value='{$price}' name='item_price' />";
            $output .= "<input type='hidden' value='{$quantity}' name='item_quantity' />";
            $output .= "<input type='hidden' value='{$currency}' name='currency' />";
            $output .= "<input type='hidden' value='{$priceInCents}' name='amount' />";
            $output .= "<input type='hidden' value='{$download_url}' name='download_url' />";
            $output .= "<input type='hidden' value='{$redirect_url}' name='redirect_url' />";
            $output .= "</form>";

            return $output;
        }


        public function shortcode_accept_simplepay_button_payment_checkout($atts = array(), $content)
        {

            if (empty($_POST['token'])) {
                echo('Invalid Token');
                return;
            }

            $GLOBALS['PaymentSuccessfull'] = false;

            ob_start();

            $verified_transaction = verify_transaction(
                $_POST['token'],
                $_POST['amount'],
                $_POST['currency'],
                $this->private_key);

            if ($verified_transaction['verified'] || $_POST['simplepay_status'] === 'true') {
                $order = SimplePay_ButtonOrder::get_instance();
                $order->insert($_POST, $verified_transaction['response']);

                do_action('SimplePayButtonPayments_payment_completed', $order, $verified_transaction['response']);

                $GLOBALS['PaymentSuccessfull'] = true;
                if (!empty($_POST['download_url'])) {
                    $download_url = openssl_decrypt($_POST['download_url'], 'AES-256-CBC', $this->SimplePayAdminSettings->simplepay_button_encrypt_key, 0, '1234567891234567');
                } else {
                    $download_url = '';
                }

                if (!empty($_POST['redirect_url'])) {
                    $redirect_url = openssl_decrypt($_POST['redirect_url'], 'AES-256-CBC', $this->SimplePayAdminSettings->simplepay_button_encrypt_key, 0, '1234567891234567');
                } else {
                    $redirect_url = '';
                }
            } else {
                $GLOBALS['asp_error'] = 'Error processing payment';
            }

            include dirname(dirname(__FILE__)) . '/views/checkout.php';

            return ob_get_clean();
        }
    }
}
