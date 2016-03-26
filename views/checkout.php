<?php
	global $PaymentSuccessfull, $asp_error;
	if($PaymentSuccessfull) {
		if(!empty($content)) {
			echo do_shortcode($content);
		}
		else{ 
			echo __('Thank you for your purchase.');
		}

		if(!empty($download_url)) {
			echo "Please <a href='".$download_url."'>click here</a> to download.";
		}

		if(!empty($redirect_url)) {
			echo "Please <a href='".$redirect_url."'>click here</a> to continue.";
			echo "<script>window.location = '$redirect_url';</script>";
		}
	}
	else{
		echo __("System was not able to complete the payment.\n\n".$asp_error);
	}
?>
