<?php

    // SimplePay Assets
    // This class has the objective of creating the SimplePay media elements.

    class SimplePayAssets{

        // This method exports a pay button attached to a specific transaction
        // This function has optional arguments.

        public static function payButton($transaction,$clientInformation,$color = null){

            // Setup the Gateway and get the check out button code

            $buttonCode = $transaction->pay($clientInformation);

            // Append the code to the button

            echo '
                <script>
                
                    function simplepayButtonClick () {
                      '.$buttonCode.'
                    }
                
                </script>';

            // Export the button

            if($color == 'dark'){
                echo '<a href="javascript:simplepayButtonClick()" ><img src="http://assets.simplepay.ng/buttons/pay_medium_dark.png" /></a>';
                return;

            }

            echo '<a href="javascript:simplepayButtonClick()" ><img src="http://assets.simplepay.ng/buttons/pay_medium_light.png" /></a>';

        }

    }
