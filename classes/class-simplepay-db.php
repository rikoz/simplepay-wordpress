<?php

/**
 * Database class
 */

// Exit if accessed directly.
if (!defined('ABSPATH')) {
	exit;
}

if (!class_exists('SimplePay_DB') ) {
	class SimplePay_DB {

		// Class instance variable
		public static $instance = null;

		// Full database name
		private $db = null;

		/**
		 * Class constructor
		 */
		private function __construct() {
			
			global $base_simplepay_class;
			global $wpdb;
			$this->db = $wpdb->prefix . $base_simplepay_class->plugin_db;
			
			if(!$this->db_created()) {
				// Create database tables
				$this->create();

				// Add initial database data - initial configurations
				$this->initial_data();
			}
		}

		/**
		 * Create database tables
		 *
		 * @since     1.0.0
		 */
		public function create() {
			
			$sql = "CREATE TABLE " . $this->db . " (
				id VARCHAR(30) NOT NULL,
				simplepay_live_private_api_key VARCHAR(50),
				simplepay_live_public_api_key VARCHAR(50),
				simplepay_test_private_api_key VARCHAR(50),
				simplepay_test_public_api_key VARCHAR(50),
				simplepay_description VARCHAR(100),
				simplepay_custom_image_url VARCHAR(500),
				simplepay_button_checkout_url VARCHAR(500),
				simplepay_button_default_text VARCHAR(100),
				simplepay_button_encrypt_key VARCHAR(500),
				simplepay_test_mode BOOLEAN NOT NULL DEFAULT TRUE,
				simplepay_payment_type VARCHAR(30),
				UNIQUE KEY  id (id)
				);";
			
			require_once(ABSPATH . 'wp-admin/includes/upgrade.php');
			dbDelta($sql);
		}

		/**
		 * Initial database data
		 *
		 * @since     1.0.0
		 */
		public function initial_data() {
			
			global $base_simplepay_class;
			global $wpdb;
	
			$wpdb->insert( 
				$this->db, 
				array( 
					'id' => $base_simplepay_class->plugin_slug,
					'simplepay_payment_type' => 'checkout'
				) 
			);
		}

		/**
		 * Load admin data
		 *
		 * @since     1.0.0
		 */
		public function load_admin_data() {
			
			global $base_simplepay_class;
			global $wpdb;

			$results = $wpdb->get_results("SELECT *
				FROM $this->db
				WHERE id = '" . $base_simplepay_class->plugin_slug . "'");

			return $results;
		}

		/**
		 * Update admin data
		 *
		 * @since     1.0.0
		 */
		public static function update_admin_data() {
			
			global $base_simplepay_class;
			global $wpdb;

			$cb_test_mode = 0;
			if(isset($_POST['test_mode']) && $_POST['test_mode'] == 1) {
				$cb_test_mode = 1;
			}    
			
			$wpdb->query("UPDATE " . SimplePay_DB::get_instance()->db . "
				SET simplepay_live_private_api_key = '" . $_POST['live_private_api_key'] . "',
				simplepay_live_public_api_key = '" . $_POST['live_public_api_key'] . "',
				simplepay_test_private_api_key = '" . $_POST['test_private_api_key'] . "',
				simplepay_test_public_api_key = '" . $_POST['test_public_api_key'] . "',
				simplepay_description = '" . $_POST['description'] . "',
				simplepay_custom_image_url = '" . $_POST['custom_image_url'] . "',
				simplepay_button_checkout_url = '" . $_POST['button_checkout_url'] . "',
				simplepay_button_default_text = '" . $_POST['button_default_text'] . "',
				simplepay_button_encrypt_key = '" . $_POST['simplepay_button_encrypt_key'] . "',
				simplepay_test_mode = '" . $cb_test_mode . "' 
				WHERE id = '" . $base_simplepay_class->plugin_slug . "'");

			wp_redirect(site_url('wp-admin/?page=simplepay-plugin'));
		}

		/**
		 * Update the checkout url (called from plugin activate)
		 *
		 * @since     1.0.0
		 */
		public static function update_checkout_url($url) {
			global $base_simplepay_class;
			global $wpdb;

			$wpdb->query("UPDATE " . SimplePay_DB::get_instance()->db . "
				SET simplepay_button_checkout_url = '" .$url . "'
				WHERE id = '" . $base_simplepay_class->plugin_slug . "'");
		}

		/**
		 * Create the URL encrypt key
		 *
		 * @since     1.3.0
		 */
		public static function generate_button_url_encrypt_key(){
			global $base_simplepay_class;
			global $wpdb;

			$wpdb->query("UPDATE " . SimplePay_DB::get_instance()->db . "
				SET simplepay_button_encrypt_key = '" . bin2hex(openssl_random_pseudo_bytes(256)) . "'
				WHERE id = '" . $base_simplepay_class->plugin_slug . "'");
		}
		/**
		 * Check if the database exists
		 *
		 * @since     1.0.0
		 */
		public function db_created() {

			global $wpdb;

			return $wpdb->get_var("SHOW TABLES LIKE '$this->db'") == $this->db;
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
	}
}
