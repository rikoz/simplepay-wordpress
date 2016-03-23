<?php

// Exit if accessed directly.
if (!defined('ABSPATH')) {
	exit;
}

if (!class_exists('SimplePay_PaymentsShortcode') ) {        
		
	class SimplePay_PaymentsShortcode {

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

		function __construct() {
			$this->SimplePayAdminSettings = SimplePay_DB::get_instance()->load_admin_data()[0];
			
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

		public function interfer_for_redirect() {
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
		public static function get_instance() {

			// If the single instance hasn't been set, set it now.
			if (null == self::$instance) {
				self::$instance = new self;
			}

			return self::$instance;
		}

		function shortcode_accept_simplepay_button_payment($atts, $content = "") {
			
			// Enqueue the simplepay script
			wp_enqueue_script('simplepay', 'https://checkout.simplepay.ng/simplepay.js', array(), false, false);
			
			extract(shortcode_atts(array(
				'name' => 'Item Name',
				'price' => '0',
				'quantity' => '1',
				'download_url' => '',
				'redirect_url' => '',
				'currency' => 'NGN',
				'button_text' => $this->SimplePayAdminSettings->simplepay_button_default_text,
				'button_style' => 'simplepay-button-style',
				'fee_amount' => '',
				'fee_label' => ''
							), $atts));

			if (!empty($download_url)) {
				$download_url = openssl_encrypt($download_url, 'AES-256-CBC', $this->SimplePayAdminSettings->simplepay_button_encrypt_key, 0, '1234567891234567');
			}
			else
				$download_url = '';

			if (!empty($redirect_url)) {
				$redirect_url = openssl_encrypt($redirect_url, 'AES-256-CBC', $this->SimplePayAdminSettings->simplepay_button_encrypt_key,0, '1234567891234567');
			}
			else
				$redirect_url = '';

			$quantity = strtoupper($quantity);
			if ("$quantity" === "N/A") $quantity = "NA";

				$form_id = 'simplepay_form_' . count(self::$payment_buttons);
				self::$payment_buttons[] = $form_id;
				$paymentAmount = ("$quantity" === "NA" ? $price : ($price * $quantity));
				$priceInCents = $paymentAmount * 100 ;
				
				$feeInCents = '';
				if (!empty($fee_amount)){
					$feeInCents = $fee_amount * 100;
				}

				$description = '';
				
				if ("$quantity"==="NA")
					  $description = "{$name}";
				else
					  $description .= "{$name} - {$quantity} piece".($quantity <> 1 ? "s" : "")." for {$paymentAmount} {$currency}";

				$output = "<form action='" .  $this->SimplePayAdminSettings->simplepay_button_checkout_url . "' METHOD='POST' id='".$form_id."'> ";
				$output .= "<button type='button' id='".$form_id."_button' class='".$button_style."'>{$button_text}</button>";
				$output .= "
						<script>
						jQuery( document ).ready(function(){
							var handler = SimplePay.configure({
							   token: function (token) {
									// put token and transaction ID to be sent forward
									jQuery('#".$form_id."').append(
										jQuery('<input />', { name: 'token', type: 'hidden', value: token })
									);
									jQuery('#".$form_id."').submit();
							   } ,
							   key: '".$this->public_key."',
							   image: '".$this->SimplePayAdminSettings->simplepay_custom_image_url."'        
							});
							jQuery('#".$form_id."_button').on('click', function (e) {
								handler.open(SimplePay.CHECKOUT,
								{
								   description: '{$description}',
								   amount: '{$priceInCents}',
								   currency: 'NGN',
								   fee_amount: '{$feeInCents}',
								   fee_label: '{$fee_label}',
								   address: 'NA',
								   postal_code: '',
								   city: 'NA',
								   country: 'NG'
								});
							});
						});
						</script>
				";

				$output .= "<input type='hidden' value='{$name}' name='item_name' />";
				$output .= "<input type='hidden' value='{$price}' name='item_price' />";
				$output .= "<input type='hidden' value='{$quantity}' name='item_quantity' />";
				$output .= "<input type='hidden' value='{$currency}' name='currency_code' />";
				$output .= "<input type='hidden' value='{$download_url}' name='download_url' />";
				$output .= "<input type='hidden' value='{$redirect_url}' name='redirect_url' />";
				$output .= "</form>";

				return $output;
		}


		public function shortcode_accept_simplepay_button_payment_checkout($atts = array(), $content) {

			if (empty($_POST['token'])) {
				echo ('Invalid Token');
				return;
			}

			$GLOBALS['PaymentSuccessfull'] = false;

			$data = array (
				'token' => $_POST['token']
			);
			$data_string = json_encode($data); 

			ob_start();
				
			$ch = curl_init();
			curl_setopt($ch, CURLOPT_URL, 'https://checkout.simplepay.ng/v1/payments/verify/');
			curl_setopt($ch, CURLOPT_USERPWD, $this->private_key . ':');
			curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
			curl_setopt($ch, CURLOPT_CUSTOMREQUEST, 'POST');
			curl_setopt($ch, CURLOPT_POST, true);
			curl_setopt($ch, CURLOPT_POSTFIELDS, $data_string);
			curl_setopt($ch, CURLOPT_HEADER, true);
			curl_setopt($ch, CURLOPT_HTTPHEADER, array(                                                                          
				'Content-Type: application/json',                                                                                
				'Content-Length: ' . strlen($data_string)                                                                       
			));       
			$curl_response = curl_exec($ch);
			$curl_response = preg_split("/\r\n\r\n/",$curl_response);
			$response_content = $curl_response[1];
			$json_response = json_decode(chop($response_content), TRUE);
			$response_code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
			curl_close($ch);
			
			
			if ($response_code == '200' && $json_response['response_code'] == '20000') {
				$order = SimplePay_ButtonOrder::get_instance();
				$order->insert($_POST, $json_response);

				do_action('SimplePayButtonPayments_payment_completed', $order, $json_response);

				$GLOBALS['PaymentSuccessfull'] = true;
				if (!empty($_POST['download_url'])){
					$download_url = openssl_decrypt($_POST['download_url'], 'AES-256-CBC', $this->SimplePayAdminSettings->simplepay_button_encrypt_key, 0, '1234567891234567');
				} else {
					$download_url = '';
				}

				if (!empty($_POST['redirect_url'])){
					$redirect_url = openssl_decrypt($_POST['redirect_url'], 'AES-256-CBC', $this->SimplePayAdminSettings->simplepay_button_encrypt_key, 0, '1234567891234567');
				} else {
					$redirect_url = '';
				}
			}else{
				$GLOBALS['asp_error'] = 'Error processing payment';
			}

			include dirname(dirname(__FILE__)) . '/views/checkout.php';

			return ob_get_clean();
		}
	}
}
