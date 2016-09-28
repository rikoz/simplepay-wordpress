<?php

/**
 * Give Wordpress Integration class
 */

// Exit if accessed directly.
if (!defined('ABSPATH')) {
    exit;
}

/*
 * Adds Costa Rican Colon currency to your Give settings
 * 
 * @access      public
 * @since       1.5
 */
function give_simplepay_add_nigerian_currency($currencies)
{
    $currencies['NGN'] = __('Nigerian Naira (&#8358;)', 'give');
    return $currencies;
}

add_filter('give_currencies', 'give_simplepay_add_nigerian_currency');

/*
 * Converts the currency code to the correct HTML character symbol
 * for the form output
 * 
 * @access      public
 * @since       1.5
 */
function give_simplepay_add_naira_symbol($symbol, $currency)
{
    switch ($currency) :
        case "NGN" :
            $symbol = '&#8358;';
            break;
    endswitch;
    return $symbol;
}

add_filter('give_currency_symbol', 'give_simplepay_add_naira_symbol', 10, 2);

/**
 * No Decimals
 *
 * @access      public
 * @since       1.5
 * @return int
 */
function give_simplepay_remove_decimals()
{
    return 0;
}

add_filter('give_format_amount_decimals', 'give_simplepay_remove_decimals');


/**
 * Internationalization
 *
 * @access      public
 * @since       1.5
 * @return      void
 */
function give_simplepay_textdomain()
{
    load_plugin_textdomain('give-simplepay', false, dirname(plugin_basename(__FILE__)) . '/languages/');
}

add_action('init', 'give_simplepay_textdomain');

/**
 * Register our payment gateway
 *
 * @access      public
 * @since       1.5
 * @return      array
 */

function give_simplepay_register_gateway($gateways)
{
    $gateways['simplepay'] = array(
        'admin_label' => 'SimplePay',
        'checkout_label' => __('Master Card, Visa and Verve (Processed by SimplePay)', 'give-simplepay'),
        'supports' => array(
            'buy_now'
        )
    );

    return $gateways;
}

add_filter('give_payment_gateways', 'give_simplepay_register_gateway');


/**
 * Add an errors div
 *
 * @access      public
 * @since       1.5
 * @return      void
 */

function give_simplepay_add_simplepay_errors()
{
    echo '<div id="give-simplepay-payment-errors"></div>';
}

add_action('give_after_cc_fields', 'give_simplepay_add_simplepay_errors', 999);

/**
 * simplepay uses it's own credit card form because the card details are tokenized.
 *
 * We don't want the name attributes to be present on the fields in order to prevent them from getting posted to the server
 *
 * @access      public
 * @since       1.5
 * @return      void
 */
function give_simplepay_credit_card_form()
{


}

add_action('give_simplepay_cc_form', 'give_simplepay_credit_card_form');

/**
 * Process simplepay checkout submission
 *
 * @access      public
 * @since       1.5
 * @return      void
 */
function give_simplepay_process_simplepay_payment($purchase_data)
{

    $settingsDB = SimplePay_DB::get_instance()->load_admin_data();
    $admin_settings = $settingsDB[0];

    if (give_is_test_mode()) {
        $private_key = $admin_settings->simplepay_test_private_api_key;
    } else {
        $private_key = $admin_settings->simplepay_live_private_api_key;
    }

    $purchase_summary = give_get_purchase_summary($purchase_data, false);

    // make sure we don't have any left over errors present
    give_clear_errors();

    if (!isset($_POST['give_simplepay_token'])) {
        // no simplepay token
        give_set_error('no_token', __('Missing simplepay token. Please contact support.', 'give-simplepay'));
        give_record_gateway_error(__('Missing simplepay Token', 'give-simplepay'), __('A simplepay token failed to be generated. Please check simplepay logs for more information', 'give-simplepay'));
    } else {
        $card_data = $_POST['give_simplepay_token'];
    }

    $errors = give_get_errors();

    if (!$errors) {

        try {

            // setup the payment details
            $payment_data = array(
                'price' => $purchase_data['price'],
                'give_form_title' => $purchase_data['post_data']['give-form-title'],
                'give_form_id' => intval($purchase_data['post_data']['give-form-id']),
                'date' => $purchase_data['date'],
                'user_email' => $purchase_data['user_email'],
                'purchase_key' => $purchase_data['purchase_key'],
                'currency' => give_get_currency(),
                'user_info' => $purchase_data['user_info'],
                'status' => 'pending',
                'gateway' => 'simplepay'
            );

            // record the pending payment
            $payment = give_insert_payment($payment_data);

            // verify transaction
            $verified_transaction = verify_transaction(
                $_POST['give_simplepay_token'],
                $_POST['give_simplepay_amount'],
                $_POST['give_simplepay_currency'],
                $private_key);

            if ($verified_transaction['verified'] || $_POST['give_simplepay_status'] === 'true') {
                give_update_payment_status($payment, 'publish');

                give_set_payment_transaction_id($payment, $verified_transaction['response']['id']);
                give_insert_payment_note($payment, 'Reference ID: ' . $verified_transaction['response']['payment_reference']);

                give_send_to_success_page();
            } else {
                give_set_error('payment_not_recorded', __('Your payment could not be recorded, please contact the site administrator.', 'give-simplepay'));
                // if errors are present, send the user back to the purchase page so they can be corrected
                give_send_back_to_checkout('?payment-mode=simplepay');
            }
        } catch (Exception $e) {
            give_set_error('api_error', __('Something went wrong.', 'give-simplepay'));
            give_send_back_to_checkout('?payment-mode=simplepay');
        }
    } else {
        give_send_back_to_checkout('?payment-mode=simplepay');
    }
}

add_action('give_gateway_simplepay', 'give_simplepay_process_simplepay_payment');


/**
 * Register payment statuses
 *
 * @since 1.5
 * @return void
 */
function give_simplepay_register_post_statuses()
{
    register_post_status('cancelled', array(
        'label' => _x('Cancelled', 'Cancelled payment', 'give-simplepay'),
        'public' => true,
        'exclude_from_search' => false,
        'show_in_admin_all_list' => true,
        'show_in_admin_status_list' => true,
        'label_count' => _n_noop('Active <span class="count">(%s)</span>', 'Active <span class="count">(%s)</span>', 'give-simplepay')
    ));
}

add_action('init', 'give_simplepay_register_post_statuses', 110);


/**
 * Register our new payment status labels for EDD
 *
 * @since 1.5
 * @return array
 */
function give_simplepay_payment_status_labels($statuses)
{
    $statuses['preapproval'] = __('Preapproved', 'give-simplepay');
    $statuses['cancelled'] = __('Cancelled', 'give-simplepay');

    return $statuses;
}

add_filter('give_payment_statuses', 'give_simplepay_payment_status_labels');


/**
 * Load our javascript
 *
 * @access      public
 * @since       1.5
 * @return      void
 */
function give_simplepay_js($override = false)
{
    $settingsDB = SimplePay_DB::get_instance()->load_admin_data();
    $admin_settings = $settingsDB[0];

    if (give_is_test_mode()) {
        $public_key = $admin_settings->simplepay_test_public_api_key;
    } else {
        $public_key = $admin_settings->simplepay_live_public_api_key;
    }

    if (give_is_gateway_active('simplepay')) {

        wp_enqueue_script('simplepay-js', 'https://checkout.simplepay.ng/v2/simplepay.js', array('jquery'), false, true);
        wp_enqueue_script('give-simplepay-js', SP_DIR_URL . 'lib/js/givewp.js', array(
            'jquery',
            'simplepay-js'));

        $simplepay_vars = array(
            'public_key' => $public_key,
            'custom_image' => $admin_settings->simplepay_custom_image_url,
            'simplepay_plugin_version' => SP_PLUGIN_VERSION
        );

        wp_localize_script('give-simplepay-js', 'give_simplepay_vars', $simplepay_vars);

    }

}

add_action('wp_enqueue_scripts', 'give_simplepay_js', 100);

/**
 * Get the meta key for storing simplepay customer IDs in
 *
 * @access      public
 * @since       1.5
 * @return      void
 */
function give_simplepay_get_customer_key()
{

    $key = '_give_simplepay_customer_id';
    if (give_is_test_mode()) {
        $key .= '_test';
    }

    return $key;
}


/**
 * Given a Payment ID, extract the transaction ID from simplepay
 *
 * @param  string $payment_id Payment ID
 *
 * @return string                   Transaction ID
 */
function give_simplepay_get_payment_transaction_id($payment_id)
{

    $notes = give_get_payment_notes($payment_id);
    $transaction_id = '';

    foreach ($notes as $note) {
        if (preg_match('/^simplepay Charge ID: ([^\s]+)/', $note->comment_content, $match)) {
            $transaction_id = $match[1];
            continue;
        }
    }

    return apply_filters('give_simplepay_set_payment_transaction_id', $transaction_id, $payment_id);
}

add_filter('give_get_payment_transaction_id-simplepay', 'give_simplepay_get_payment_transaction_id', 10, 1);


/**
 * Injects the simplepay token and customer email into the pre-gateway data
 *
 * @since  1.5
 * @return array
 */
function give_simplepay_straight_to_gateway_data($purchase_data)
{

    if (isset($_REQUEST['give_simplepay_token'])) {
        $purchase_data['gateway'] = 'simplepay';
        $_REQUEST['give-gateway'] = 'simplepay';

        if (isset($_REQUEST['give_email'])) {
            $purchase_data['user_info']['email'] = $_REQUEST['give_email'];
            $purchase_data['user_email'] = $_REQUEST['give_email'];
        }

    }

    return $purchase_data;
}

add_filter('give_straight_to_gateway_purchase_data', 'give_simplepay_straight_to_gateway_data');
