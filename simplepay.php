<?php
/*
Plugin Name: SimplePay
Plugin URI: http://www.simplepay.ng
Description: Online and Mobile Payment. Secure. Simple.
Version: 1.8.0
Author: SimplePay (support@simplepay.ng)
Author URI: http://www.simplepay.ng
*
* Copyright 2016 SimplePay. All rights reserved.
*/

// Exit if accessed directly.
if (!defined('ABSPATH')) {
    exit;
}

// Includes
include_once(ABSPATH . 'wp-admin/includes/plugin.php');
include_once('simplepay-requirements.php');

// Plugin requirements.
$simplepay_requires = array(
    'wp' => '4.3',
    'php' => '5.2.0',
    'curl' => '7.30.0'
);

// Define constants.
$simplepay_constants = array(
    'SP_REQUIRES' => serialize($simplepay_requires),
    'SP_MAIN_FILE' => __FILE__,
    'SP_DIR_PATH' => plugin_dir_path(__FILE__),
    'SP_DIR_URL' => plugin_dir_url(__FILE__),
    'SP_PLUGIN_VERSION' => '1.8.0',
    'SP_PAYMENT_SCRIPT_VERSION' => '1.0.1'
);
foreach ($simplepay_constants as $constant => $value) {
    if (!defined($constant)) {
        define($constant, $value);
    }
}

// Check plugin requirements.
$simplepay_requirements = new SimplePay_Requirements($simplepay_requires);

if ($simplepay_requirements->pass() === false) {
    $simplepay_fails = $simplepay_requirements->failures();

    if (isset($simplepay_fails['wp']) || isset($simplepay_fails['php']) || isset($simplepay_fails['curl'])) {
        // Display an admin notice if running old WordPress or PHP
        function simplepay_plugin_requirements()
        {
            $required = unserialize(SP_REQUIRES);
            global $wp_version;
            echo '<div class="error">' .
                '<p>' .
                sprintf(
                    __('SimplePay requires PHP %1$s and WordPress %2$s to function properly.
						     	PHP version found: %3$s. WordPress installed version: %4$s. 
						     	Please upgrade to meet the minimum requirements. <a href="http://www.wpupdatephp.com/update/" target=_blank">
						     	Read more on why it is important to stay updated.</a>', 'sp'),
                    $required['php'],
                    $required['wp'],
                    PHP_VERSION,
                    $wp_version
                ) .
                '</p>' .
                '</div>';
        }

        add_action('admin_notices', 'simplepay_plugin_requirements');
    }

    // Halt the rest of the plugin execution if PHP check fails
    if (isset($simplepay_fails['php'])) {
        return;
    }
}

// Load the plugin.
require_once SP_DIR_PATH . 'classes/class-simplepay.php';
// Load the short code class
require_once SP_DIR_PATH . 'classes/class-simplepay-shortcode.php';
// Load the widget class
require_once SP_DIR_PATH . 'classes/class-simplepay-widget.php';
// Load verify transaction lib.
require_once SP_DIR_PATH . 'lib/php/verify.php';


// Register hooks that are fired when the plugin is activated, deactivated, and uninstalled, respectively.
register_activation_hook(SP_MAIN_FILE, array('SimplePay', 'activate'));


// Set up global holding the base class instance so we can easily use it throughout
global $base_simplepay_class;

$base_simplepay_class = SimplePay::get_instance();

// Hook: Submit admin form (need to be added here, not inside of any class)
add_action('admin_post_simplepay_update', array('SimplePay_DB', 'update_admin_data'));

// Hook: Load shortcode configuration
add_action('plugins_loaded', array('SimplePay_PaymentsShortcode', 'get_instance'));


/***
 * Integrations
 **/
// WooCommerce integration
if (is_plugin_active('woocommerce/woocommerce.php')) {
    require_once(SP_DIR_PATH . 'integrations/woocommerce/woocommerce.php');
}

// Give Wordpress integration
if (is_plugin_active('give/give.php')) {
    require_once(SP_DIR_PATH . 'integrations/givewp/givewp.php');
}

?>
