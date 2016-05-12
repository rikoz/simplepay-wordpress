<?php

    class SimplePayTransaction {

        // Instance Variables.
        
        public $_amount = null;
        public $_currency = null;
        public $_transactionId = null;

        // Default Construction with optional arguments.
        // This method returns a exception, if the attributes aren't properly set, or completed.

        public function __construct($attributes = []){

            foreach($attributes as $attribute => $value){

                if($attribute == 'amount'){
                    $this->_amount = $value;
                }
                else if($attribute == 'currency'){
                    $this->_currency = $value;
                }
                else if($attribute == 'transactionId'){
                    $this->_transactionId = $value;
                }

            }
            
            if(empty($this->_amount) and empty($this->_currency) and empty($this->_transactionId)){
                throw new SimplePayInvalidTransaction('The transaction fields aren\'t properly filled!');
            }

        }

        // This method creates the payment button for the payment with the information from the object
        // All the arguments of this function are mandatory.
        // You should use this button according with the following example.


        // <html>
        //   <body>
        //      ...
        //
        //      <div class='btn_checkout'></div>
        //
        //      <?php
        //
        //          $transaction =  new SimplePayTransaction(Array(
        //                                                      'amount'=> '123',
        //                                                      'currency' => 'NGN',
        //                                                      'transactionId' => '123'
        //                                                    ));
        //
        //          $buttonCode = $transaction.pay(Array(
        //                                          'email'=> 'customer@store.com',
        //                                          'phone'=> '+23412312312',
        //                                          'description'=> 'My Test Store Checkout 123-456',
        //                                          'address'=> '31 Kade St, Abuja, Nigeria',
        //                                          'postal_code'=> '110001',
        //                                          'city'=> 'Abuja',
        //                                          'country'=> 'NG'
        //                                        ));
        //      ?\>
        //
        //      <script>
        //          $('.btn_checkout').onclick(function(){
        //              <?php echo $buttonCode; ?/>
        //          });
        //      </script>
        //      ...
        //   </body>
        // </html>

        public function pay($clientInformation){
            return SimplePay::paymentButton($this,$clientInformation);
        }

    }
