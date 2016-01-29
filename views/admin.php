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
	<img class="logo" src="<?php echo plugins_url('assets/img/logos/logo.png', SP_MAIN_FILE); ?>" />
	<h2>General Plugin Settings</h2>
	<form method="post" action="admin-post.php">
		<input type='hidden' name='action' value='simplepay_update' />
		<div class="live-mode">
			<h4>Live mode &nbsp;<input type="checkbox" name="live_mode" value="1" <?php echo ($form_data[0]->simplepay_live_mode == 1 ? 'checked' : '');?> /></h4>
		</div>

			<div class="api-keys margin-top-md">
			<h3>Live API Keys</h3>
			<h4 class="no-margin-bottom">PRIVATE API Key</h4>
			<input type="text" name="live_private_api_key" value="<?php echo $form_data[0]->simplepay_live_private_api_key; ?>" />
			
			<h4 class="no-margin-bottom">PUBLIC API Key</h4>
			<input type="text" name="live_public_api_key" value="<?php echo $form_data[0]->simplepay_live_public_api_key; ?>" />

			<hr class="margin-top-sm" />

			<h3>Test API Keys</h3>
			<h4 class="no-margin-bottom">PRIVATE API Key</h4>
			<input type="text" name="test_private_api_key" value="<?php echo $form_data[0]->simplepay_test_private_api_key; ?>" />
			
			<h4 class="no-margin-bottom">PUBLIC API Key</h4>
			<input type="text" name="test_public_api_key" value="<?php echo $form_data[0]->simplepay_test_public_api_key; ?>" />

			<hr class="margin-top-sm" />
			<h3>Custom SimplePay checkout image</h3>
			<h4>Image URL</h4>
			<input type="text" name="custom_image_url" value="<?php echo $form_data[0]->simplepay_custom_image_url; ?>" />

			<!--
			<hr class="margin-top-sm" />
			
			<h3>Payment Type</h3>
			<select name="payment_type">
				<?php // foreach ($payment_types as $type) { ?>
						<option value="<?php // echo strtolower($type); ?>" <?php // echo ($form_data[0]->simplepay_payment_type == strtolower($type) ? 'selected' : '');?>><?php // echo $type; ?></option>
				<?php // } ?>
			</select>
			-->
		</div>
		<div class="margin-top-md">
			<input type="submit" name="submit" value="Save Changes" />
		</div>
	</form>
</div>
