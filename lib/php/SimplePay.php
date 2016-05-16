<?php
    
    // Main Module
    // Library sub-modules

    include_once 'lib/Transaction.php';
    include_once 'lib/Verify.php';
    include_once 'exceptions/Exceptions.php';
    include_once 'lib/Assets.php';


    class SimplePayPaymentsLibrary{

        // Instance Variables.

        private static $global;

        private $_sandboxMode = false;

        private $_publicTestKey = null;
        private $_privateTestKey = null;

        private $_publicLiveKey = null;
        private $_privateLiveKey = null;


        // Platform information for developers statistics

        private $_platform = null;

        // Optional Parameters of the lib.

        private $_customCheckoutDescription = null;
        private $_customCheckoutImg = null;

        // Variable that contains the URL to complete the verification (call to the completeTransaction).

        private $_completeTransactionURL = null;

        // Default Construction with optional arguments.

        public function __construct($prefs = []){

            // Dependency verification.

            if(!is_callable('curl_init') ) {
                throw new SimplePayExceptionNotFound('Curl isn\'t installed in your system!');
            }

            // If arguments update the instance variables.

            foreach ($prefs as $pref => $value){

                if($pref == 'publicTestApi'){
                    $this->_publicTestKey = $pref;
                }
                else if($pref == 'privateTestApi'){
                    $this->_privateTestKey= $pref;
                }
                else if($pref == 'publicLiveApi'){
                    $this->_publicLiveKey = $pref;
                }
                else if($pref == 'privateLiveApi'){
                    $this->_privateLiveKey = $pref;
                }

            }

        }

        // Default method of initialization for the library.

        public static function initialization(){

            // Allowing the library to be available through all the php.

            self::$global = new SimplePayPaymentsLibrary();

        }

        // This method sets or gets the API working mode, (Test or Live).

        // All the transactions made in test mode AREN'T REAL TRANSACTIONS, please use the respective TEST CARDS!
        // All the transactions made in live mode ARE REAL TRANSACTIONS, these transactions WILL CHARGE MONEY!

        // This function has optional arguments.
        // It works like a get without arguments and like a set when including them.

        public static function sandboxMode($value = null){

            if(empty($value)){
                return self::$global->_sandboxMode;
            }

            self::$global->_sandboxMode = $value;
            return null;

        }

        // This method sets or gets the platform information. This declaration is optional.
        // This function has optional arguments.
        // It works like a get without arguments and like a set when including them.

        public static function platform($value = null){

            if(empty($value)){
                return self::$global->_platform;
            }

            self::$global->_platform = $value;
            return null;

        }

        // This method sets or gets the URL the will call the verify module, ( Hidden Form URL for submitting ).
        // This function has optional arguments.
        // It works like a get without arguments and like a set when including them.

        // This method returns a exception, getting a value that isn't set.

        public static function completeTransactionURL($value = null){

            if(empty($value) and empty(self::$global->_completeTransactionURL)){

                throw new SimplePayInvalidCompleteTransactionURL('Please configure the complete transaction URL, if this
                                                                  isn\'t properly configured, it won\'t be possible to
                                                                  verify the transaction!');

            }
            
            else if(empty($value)){
                return self::$global->_completeTransactionURL;
            }

            self::$global->_completeTransactionURL = $value;
            return null;

        }

        // This method sets or gets the test keys (Public and Private). The Private Key SHOULDN'T be used, or displayed
        // outside the server environment

        // This function has optional arguments.
        // It works like a get without arguments and like a set when including them.

        public static function testKeys($values = null){

            if(empty($values)){
                return Array(
                    'public' => self::$global->_publicTestKey,
                    'private' => self::$global->_privateTestKey
                );
            }

            foreach ($values as $key => $item ){
                if($key == 'public'){
                    self::$global->_publicTestKey = $item;
                }
                else if($key == 'private'){
                    self::$global->_privateTestKey = $item;
                }
            }

            return null;

        }

        // This method sets or gets the live keys (Public and Private). The Private Key SHOULDN'T be used, or displayed
        // outside the server environment

        // This function has optional arguments.
        // It works like a get without arguments and like a set when including them.

        public static function liveKeys($values = null){

            if(empty($values)){
                return Array(
                    'public' => self::$global->_publicLiveKey,
                    'private' => self::$global->_privateLiveKey
                );
            }

            foreach ($values as $key => $item ){
                if($key == 'public'){
                    self::$global->_publicLiveKey = $item;
                }
                else if($key == 'private'){
                    self::$global->_privateLiveKey = $item;
                }
            }

            return null;

        }

        // This method sets or gets the Custom Checkout Description. This declaration is optional
        // This function has optional arguments.
        // It works like a get without arguments and like a set when including them.

        public static function customCheckOutDescription($value = null){

            if(empty($value)){
                return self::$global->_customCheckoutDescription;
            }

            self::$global->_customCheckoutDescription = $value;
            return null;

        }

        // This method sets or gets the Custom Checkout Image. This declaration is optional
        // This function has optional arguments.
        // It works like a get without arguments and like a set when including them.

        public static function customCheckOutImg($value = null){

            if(empty($value)){
                return self::$global->_customCheckoutImg;
            }

            self::$global->_customCheckoutImg = $value;
            return null;

        }



        public static function paymentForms($form=true){

            $inputsStrings = '<input type="hidden" id="simplepay_amount" name="simplepay_amount" value=""/>';
            $inputsStrings .= '<input type="hidden" id="simplepay_currency" name="simplepay_currency" value=""/>';
            $inputsStrings .= '<input type="hidden" id="simplepay_order_id" name="simplepay_order_id" value=""/>';
            $inputsStrings .= '<input type="hidden" id="simplepay_token" name="simplepay_token" value=""/>';

            if($form){
                return '
                    <form method="post" action="'.self::completeTransactionURL().'" id="simplepay_checkout" >
                       '.$inputsStrings.'
                    </form>';
            }
            else {
                return $inputsStrings;
            }
        }


        // This method returns the necessary code (HTML and Javascript) to implement the gateway payment button
        // All the arguments of this function are mandatory.
        // The Method should only be called by an SimplePayTransaction class.

        public static function paymentButton($transaction,$clientInformation){

            // Inclusion of the gateway token in to the form, and gateway configuration according with the supplied
            // arguments.

            // Returning the code that will open the checkout Gateway according with the supplied arguments.

            return '
            
                    function openCheckOut(){
                        
                        // Including the transaction information.
                        
                        '.$clientInformation.'.amount = SimplePay.amountToLower(\''.$transaction->_amount.'\');
                        '.$clientInformation.'.currency = \''.$transaction->_currency.'\'
                        
                        document.getElementById("simplepay_amount").value = SimplePay.amountToLower(\''.$transaction->_amount.'\')
                        document.getElementById("simplepay_currency").value = \''.$transaction->_currency.'\'
                        
                        // Opening the payments gateway, in order to proceed the payment.
                        
                        simplepay_checkout_handler.open(SimplePay.CHECKOUT,'.$clientInformation.');
                        
                    }
                    
                    // Run the gateway configuration and open the checkout gateway.
                    
                    openCheckOut();
                                
                  ';

        }

        // This method completes the transaction for a specific payment.
        // All the arguments of this function are mandatory.
        // The Method should only be called by an SimplePayTransaction class.

        // This method returns a exception, getting a private key that isn't set.

        public static function completeTransaction($paymentId,$token,$amount,$currency){

            // Inclusion of the Verify library.

            //include_once 'lib/Verify.php';

            // Get the private key according with the API mode, return exception if private key isn't
            // declared in that mode.

            $privateKey = self::sandboxMode() ? self::$global->_privateTestKey : self::$global->_privateLiveKey;
            
            if(empty($privateKey)){
                throw new SimplePayInvalidPrivateKey('Please make sure you have inserted the respective private key');
            }

            // Return the library result and call the function passed in the arguments.

            $apiResponse = verifyTransaction($token,$amount,$currency,$privateKey);
            $apiResponse['purchaseId'] = $paymentId;

            return $apiResponse;

        }

        public static function initializeGateway($formName = 'simplepay_checkout',$preventFunction=''){

            // Get the public key according with the API mode, return exception if public key isn't
            // declared in that mode.

            $publicKey = self::sandboxMode() ? self::$global->_publicTestKey : self::$global->_publicLiveKey;

            if(empty($publicKey)){
                throw new SimplepayInvalidPublicKey();
            }

            // Gateway Source code script

            echo '<script src="https://checkout.simplepay.ng/simplepay.js"></script>';

            // Exporting to HTML the necessary code to import the Simplepay Payments Gateway
            // and complete the transaction.

            echo '
                    
                <script>
                
                    function simplepayProcessPayment(token) {
                        
                        // Create the token form element and appending it.
                        var simplepay_form = document.'.$formName.';
                        
                        var simplepay_amount = document.getElementById("simplepay_amount");
                        simplepay_amount.value = SimplePay.amountToLower(simplepay_amount.value);
                        
                        var simplepay_token = document.getElementById("simplepay_token");
                        simplepay_token.value = token;
                        
                        // Submit the payment, in order to complete the transaction. 
                        
                        '.$preventFunction.'
                        
                        
                    }
                     
                    // Initial SimplePay Gateway configuration.
                    
                    var gatewayInit = function () {

                        simplepay_checkout_handler = SimplePay.configure({
                        
                            token: simplepayProcessPayment,
                            key: \'' . $publicKey . '\',
                            image: \'' . self::$global->_customCheckoutImg . '\',
                            platform: \'' . self::$global->_platform . '\',
                            
                        });    
                    };
                    
                    document.addEventListener(\'DOMContentLoaded\', gatewayInit, false);
                    
                
                </script>
                
                 ';
        }

    }

// Initialization of the SimplePay Configuration at inclusion.

SimplePayPaymentsLibrary::initialization();