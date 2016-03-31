<?php

/**
 * Admin class
 */

// Exit if accessed directly.
if (!defined('ABSPATH')) {
	exit;
}

if (!class_exists('SimplePay_Admin') ) {
	class SimplePay_Admin {
		
		// Class instance variable
		public static $instance = null;
		
		/**
		 * Class constructor
		 */
		private function __construct() {
			
			global $base_simplepay_class;

			// Enqueue admin styles
			add_action('admin_enqueue_scripts', array($this, 'enqueue_admin_styles'), 2);
		
			// Add menu item
			add_action('admin_menu', array($this, 'add_plugin_admin_menu'), 3);
		}
		
		/**
		 * Register the administration menu for this plugin into the WordPress Dashboard menu.
		 *
		 * @since    1.0.0
		 */
		public function add_plugin_admin_menu() {
			
			global $base_simplepay_class;

			add_menu_page(
				$base_simplepay_class->get_plugin_title(),
				$base_simplepay_class->get_plugin_menu_title(),
				'manage_options',
				$base_simplepay_class->plugin_slug,
				array($this, 'display_plugin_admin_page'),
				SP_DIR_URL . 'assets/img/icon.png'
			);

			add_submenu_page(
				$base_simplepay_class->plugin_slug,
				'Settings',
				'Settings',
				'manage_options',
				$base_simplepay_class->plugin_slug,
				array($this, 'display_plugin_admin_page')
			);

			add_submenu_page(
				$base_simplepay_class->plugin_slug,
				'Pay Button Orders',
				'Pay Button Orders',
				'manage_options',
				'edit.php?post_type=simplepay_btn_order',
				NULL
			);

		}

		/**
		 * Enqueue admin style sheets
		 *
		 * @since     1.0.0
		 */
		public function enqueue_admin_styles() {

			global $base_simplepay_class;
			
			wp_enqueue_style($base_simplepay_class->plugin_slug .'-admin-styles', SP_DIR_URL . 'assets/css/admin-main.css', false, $base_simplepay_class->version);
		}

		
		/**
		 * Render the settings page for this plugin.
		 *
		 * @since    1.0.0
		 */
		public function display_plugin_admin_page() {
			include_once(SP_DIR_PATH . 'views/admin.php');
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
