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
		}

		/**
		 * Include required files (admin and frontend).
		 *
		 * @since     1.0.0
		 */
		public function includes() {
			include_once(SP_DIR_PATH . 'classes/class-simplepay-db.php');
			include_once(SP_DIR_PATH . 'classes/class-simplepay-admin.php');
		}
		
		/**
		 * Get the instance for all the included classes
		 */
		public function init() {
			
			SimplePay_DB::get_instance();
			SimplePay_Admin::get_instance();
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
