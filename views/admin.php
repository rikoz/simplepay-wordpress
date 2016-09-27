<?php

/**
 * View for the admin page
 */

// Exit if accessed directly.
if (!defined('ABSPATH')) {
    exit;
}

$form_data = SimplePay_DB::get_instance()->load_admin_data();
$payment_types = array('ESCROW', 'Checkout');

?>

<div class="admin-page">
    <img class="logo" src="<?php echo plugins_url('assets/img/logos/logo.png', SP_MAIN_FILE); ?>"/>
    <h2>General Plugin Settings</h2>
    <form method="post" action="admin-post.php">
        <input type='hidden' name='action' value='simplepay_update'/>
        <div class="test-mode">
            <?php if ($form_data[0]->simplepay_test_mode == 1) { ?>
                <h4>Test mode &nbsp;<input type="checkbox" name="test_mode" value="1" checked/></h4>
            <?php } else { ?>
                <h4>Test mode &nbsp;<input type="checkbox" name="test_mode" value="1"/></h4>
            <?php } ?>
        </div>
        <div class="api-keys margin-top-md">
            <h4 class="no-margin-bottom">If you still don't have your keys you can get them at: <a
                    href="https://merchants.simplepay.ng/api-keys" target="_blank">https://merchants.simplepay.ng/dashboard/account</a>
            </h4>
            <hr class="margin-top-sm"/>
            <h3>Live API Keys</h3>
            <h4 class="no-margin-bottom">PRIVATE API Key</h4>
            <input type="text" name="live_private_api_key"
                   value="<?php echo $form_data[0]->simplepay_live_private_api_key; ?>"/>

            <h4 class="no-margin-bottom">PUBLIC API Key</h4>
            <input type="text" name="live_public_api_key"
                   value="<?php echo $form_data[0]->simplepay_live_public_api_key; ?>"/>

            <hr class="margin-top-sm"/>

            <h3>Test API Keys</h3>
            <h4 class="no-margin-bottom">PRIVATE API Key</h4>
            <input type="text" name="test_private_api_key"
                   value="<?php echo $form_data[0]->simplepay_test_private_api_key; ?>"/>

            <h4 class="no-margin-bottom">PUBLIC API Key</h4>
            <input type="text" name="test_public_api_key"
                   value="<?php echo $form_data[0]->simplepay_test_public_api_key; ?>"/>

            <hr class="margin-top-sm"/>
            <h3>SimplePay Checkout Dialog</h3>

            <h4>Description</h4>
            <input type="text" name="description" value="<?php echo $form_data[0]->simplepay_description; ?>"/>
            <div style="font-size:11px;">The message that apears on top of the SimplePay payment gateway.</div>

            <h4>Image URL</h4>
            <input type="text" name="custom_image_url"
                   value="<?php echo $form_data[0]->simplepay_custom_image_url; ?>"/>
            <div style="font-size:11px;">The image that is shown in the SimplePay payment gateway.</div>

            <hr class="margin-top-sm"/>
            <h3>SimplePay Payment Buttons</h3>
            <h4>Button Checkout Page URL</h4>
            <input type="text" name="button_checkout_url"
                   value="<?php echo $form_data[0]->simplepay_button_checkout_url; ?>"/>
            <div style="font-size:11px;">This page is automatically created for you when you install the plugin.</div>

            <h4>Button Default Text</h4>
            <input type="text" name="button_default_text"
                   value="<?php echo $form_data[0]->simplepay_button_default_text; ?>"/>

            <h4>Button URL Encrypt Key</h4>
            <input type="text" name="button_encrypt_key"
                   value="<?php echo $form_data[0]->simplepay_button_encrypt_key; ?>"/>
            <div style="font-size:11px;">This key is automatically created for you when you install the plugin.</div>
        </div>
        <div class="margin-top-md">
            <input type="submit" name="submit" value="Save Changes"/>
        </div>
    </form>
</div>
