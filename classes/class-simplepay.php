<?php

/**
 * Main class
 */

// Exit if accessed directly.
if (!defined( 'ABSPATH' ) ) {
	exit;
}

if (!class_exists('SimplePay') ) {
	class SimplePay {

		/**
		 * Plugin version, used for cache-busting of style and script file references.
		 *
		 * @since   1.0.0
		 *
		 * @var     string
		 */
		public $version = '1.0.0';

		/**
		 * Unique identifier for your plugin.
		 *
		 * Use this value (not the variable name) as the text domain when internationalizing strings of text. It should
		 * match the Text Domain file header in the main plugin file.
		 *
		 * @since    1.0.0
		 *
		 * @var      string
		 */
		public $plugin_slug = 'simplepay-plugin';

		/**
		 * Database name.
		 *
		 * @since    1.0.0
		 *
		 * @var      string
		 */
		public $plugin_db = 'simplepay';

		/**
		 * Instance of this class.
		 *
		 * @since    1.0.0
		 *
		 * @var      object
		 */
		protected static $instance = null;

		/**
		 * Initialize the plugin
		 *
		 * @since     1.0.0
		 */
		private function __construct() {

			// Include all necessary files
			$this->includes();

			// Load all instances
			add_action('init', array($this, 'init'), 1);
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

		/**
		 * Fired when the plugin is activated.
		 *
		 * @since    1.0.0
		 */
		public static function activate() {
			// Add value to indicate that we should show admin install notice.
			// TODO:
			update_option('sp_show_admin_install_notice', 1);

			// Create checkout page  for Payment Buttons
			$args = array(
				'post_type' => 'page'
			);
			$pages = get_pages($args);
			$checkout_page_id = '';
			$checkout_page_url = '';
			foreach ($pages as $page) {
				if(strpos($page->post_content,'accept_simplepay_button_payment_checkout') !== false){
					$checkout_page_id = $page->ID;
				}
			}

			if ($checkout_page_id == '') {
				$checkout_page_id = SimplePay::create_post('page', 'SimplePay Button Payment Checkout', 'AcceptSimplePayButtonPayments-checkout', '[accept_simplepay_button_payment_checkout]');
				$checkout_page = get_post($checkout_page_id);
				$checkout_page_url = $checkout_page->guid;
			} else {
				$checkout_page = get_post($checkout_page_id);
				$checkout_page_url = $checkout_page->guid;
			}

			// store the checkout page url configuration
			SimplePay_DB::get_instance()->update_checkout_url($checkout_page_url);

			// generate a secret key to protect the redirect and download urls
			SimplePay_DB::get_instance()->generate_button_url_encrypt_key();
		}

		public static function create_post($postType, $title, $name, $content, $parentId = NULL){
			$post = array(
				'post_title' => $title,
				'post_name' => $name,
				'comment_status' => 'closed',
				'ping_status' => 'closed',
				'post_content' => $content,
				'post_status' => 'publish',
				'post_type' => $postType
			);

			if ($parentId !== NULL){
					$post['post_parent'] = $parentId;
			}
			$postId = wp_insert_post($post);
			return $postId;
		}

		/**
		 * Include required files (admin and frontend).
		 *
		 * @since     1.0.0
		 */
		public function includes() {
			include_once(SP_DIR_PATH . 'classes/class-simplepay-db.php');
			include_once(SP_DIR_PATH . 'classes/class-simplepay-admin.php');
			include_once(SP_DIR_PATH . 'classes/class-simplepay-button-order.php');
		}

		/**
		 * Get the instance for all the included classes
		 */
		public function init() {
			SimplePay_DB::get_instance();
			SimplePay_Admin::get_instance();

			// Register custom post type to store button orders
			$SimplePay_ButtonOrder = SimplePay_ButtonOrder::get_instance();
			add_action('init', array($SimplePay_ButtonOrder,'register_post_type') );
		}

		/**
		 * Return localized base plugin title.
		 *
		 * @since     1.0.0
		 *
		 * @return    string
		 */
		public static function get_plugin_title() {
			return __('SimplePay', 'sp');
		}

		public static function get_plugin_menu_title() {
			return __('SimplePay', 'sp');
		}
	}
}
