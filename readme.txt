=== SimplePay Official Wordpress Plugin ===
Contributors: simplepayng
Tags: simplepay, payments, payment gateway, visa, mastercard, verve, givewp, woocommerce, commerce, quick donation, simple donation, wordpress donation, checkout, nigeria
Requires at least: 3.6
Tested up to: 4.6
Stable tag: 1.8.0
License: MIT

SimplePay is the best Online Payment Gateway for the Nigerian market.


== Description ==

SimplePay offers the fastest and easiest way to send and receive money online. The innovative payment solution enables online businesses to integrate payments into their websites easily. Customise the check-out process the way you want.

Super-fast account activation. Top-notch customer support. Support for all currencies and all major card brands like MasterCard, Visa and Verve.

If you don't have a SimplePay account you can register for one at: https://www.simplepay.ng/registration/

= Features =

* Quick installation and setup.
* Easily take payment for a service from your site via SimplePay.
* Support for woocommerce e-commerce plugin (https://www.woothemes.com/woocommerce/)
* Support for woocommerce quick donation (https://wordpress.org/plugins/woocommerce-quick-donation/)
* Support for Give plugin (https://givewp.com/)
* Create buy buttons for your products or services on the fly and embed it anywhere on your site using a user-friendly shortcode.
* Accept donation on your WordPress site for a cause.
* View purchase orders form buy buttons on your WordPress admin dashboard.
* Allow users to automatically download the digital file after the purchase is complete.
* Protect content that is only visible if payment was successful.

= Buy Button Shortcode Attributes =

In order to create a buy button to accept 5000 Naira insert the following shortcode into a post/page.

`[accept_simplepay_button_payment name="My Product" price="5000" button_text="Buy Now"]`

In order to create a buy button to accept 5000 Naira and download a file after payments insert the following shortcode into a post/page.

`[accept_simplepay_button_payment name="My Product" price="5000" button_text="Buy Now" download_url="http://www.example.com/file.zip"]`

In order to create a buy button to accept 5000 Naira and redirect to a hidden site insert the following shortcode into a post/page.

`[accept_simplepay_button_payment name="My Product" price="5000" button_text="Buy Now" redirect_url="http://www.example.com"]`

It supports the following attributes in the shortcode:

    name:
    (string) (required) Name of the product
    Possible Values: 'Awesome Script', 'My Ebook', 'Wooden Table' etc.

    price:
    (number) (required) Price of the product or item
    Possible Values: '2000', '3000', etc.

    quantity:
    (number) (optional) Number of products to be charged.
    Possible Values: 'NA', '1', '5' etc.
    Default: NA
    
    download_url:
    (URL) (optional) URL of the downloadable file.
    Possible Values: http://example.com/my-downloads/product.zip

    redirect_url:
    (URL) (optional) URL of a page to redirect after payment successful.
    Possible Values: http://example.com/my-page

    button_text:
    (string) (optional) Label of the payment button
    Possible Values: 'Buy Now', 'Pay Now' etc

    button_class:
    (string) (optional) CSS class to be applyed to button
    Default: ''

    button_style:
    (string) (optional) CSS style to be applyed to button
    Default: ''

Note that in most cases you should not touch the page "Button Checkout Page URL". This page is created automatically and is used to by
wordpress internal system to finalise and record transactions.

There is however an advanced use where you can edit content of the page referred by "Button Checkout Page URL".
If you want to protect content you can place it inside the accept_simplepay_button_payment_checkout shortcode like:

    [accept_simplepay_button_payment_checkout]
        Please fill the form:

        [contact-form-7 id="113" title="Contact form 1"]
    [/accept_simplepay_button_payment_checkout]

= Missing some feature? =
Please send us an email to support@simplepay.ng

= Contribute =
To contribute to this plugin feel free to fork it on GitHub - https://github.com/simplepayng/simplepay-wordpress

== Installation ==

= Requirements =
* PHP 5.2 or higher
* WordPress 3.6 or higher
* cURL 7.30.x or higher

= Instalation steps =
1. Upload simplepay directory to the /wp-content/plugins/ directory
2. Activate the plugin through the 'Plugins' menu in WordPress
3. Register a free account on https://www.simplepay.ng
4. Insert Api Keys in plugin settings


== Changelog ==

= 1.8.0(September 27, 2016)

- Update to SimplePay gateway version 2

= 1.7.3 (May 5, 2016)

- Fix amount in verify payment validation

= 1.7.2 (April 26, 2016)

- Fix verify payment validation

= 1.7.1 (April 26, 2016)

- Fix verify payment in pay button

= 1.7.0 (April 26, 2016)

- Add retry to verify payment
- Fix SimplePay transaction ID in WooCommerce

= 1.6.9 (April 22, 2016)

- Update widget images

= 1.6.8 (April 19, 2016)

- Corrected value format in GiveWP


= 1.6.7 (April 18, 2016)

- Do not sent to gateway address fields if not available

= 1.6.6 (April 17, 2016)

- Fix WordPress version in requirements

= 1.6.5 (April 15, 2016)

- Update support for WordPress 4.5

= 1.6.4 (April 15, 2016)

- Update php version validation

= 1.6.3 (April 15, 2016)

- Fix cart totals variable

= 1.6.2 (April 14, 2016)

- Fix verify validation

= 1.6.1 (April 14, 2016)

- Fix simplepay_transaction_id field missing from checkout
- Fix verify validation

= 1.6.0 (April 13, 2016)

- Replaced cURL by wordpress internal library
- Updated widget html

= 1.5.7 (April 12, 2016)

- Added footer widget

= 1.5.6 (April 9, 2016)

- Renamed button_style to button_class, added button_class
- Changed default quantity to NA

= 1.5.5 (April 5, 2016)

- Fix order id PHP 5.3

= 1.5.4 (April 5, 2016)

- Fix compatibility with PHP 5.3

= 1.5.3 (April 5, 2016)

- Fix array compatibility with PHP 5.3

= 1.5.2 (April 4, 2016)

- Fix compatibility with PHP 5.3

= 1.5.1 (April 4, 2016)

- Fix compatibility with PHP 5.3

= 1.5.0 (March 31, 2016)

- Add support for GiveWP
- Fix WooCommerce checkout

= 1.4.0 (March 30, 2016)

- Remove simplepay_transaction_id field validation from checkout page
- Add plugin version in gateway

= 1.3.5 (March 28, 2016)

- Fixed cart total amount with multiple shipping methods

= 1.3.4 (March 28, 2016)

- Fixed jQuery on mobile

= 1.3.3 (March 28, 2016) =

- Fixed cart total amount

= 1.3.2 (March 26, 2016) =

- Allow shortcodes inside shortcode_accept_simplepay_button_payment_checkout

= 1.3.1 (March 23, 2016) =

- Added missing support for lower denomination in fees

= 1.3.0 (March 22, 2016) =

- Added encryption key to button URLs
- Added redirect url to button parameters
- Fixed bug when enabling the plugin with an already configured checkout page

= 1.2.1 (February 25, 2016) =

- Added style configuration to button

= 1.2.0 (February 24, 2016) =

- Added payment buttons

= 1.1.6 (February 22, 2016) =

- Updated verify

= 1.1.5 (February 19, 2016) =

- Fix live mode

= 1.1.4 (February 10, 2016) =

- Updated verify payment
- Added custom SimplePay checkout description
- Updated plugin settings page

= 1.1.3 (February 3, 2016) =

- Updated README

= 1.1.2 (February 1, 2016) =

- Checkout image update

= 1.1.1 (January 29, 2016) =

- Fixed cURL verification

= 1.1.0 (January 29, 2016) =

- Added verify payment
- Added cURL to requirements
- Updated README.md
- Updated mobile checkout start dialog

= 1.0.1 (January 27, 2016) =

- Added custom SimplePay checkout image
- Fixed paths
- Hide "simplepay_transaction_id" from WooCommerce error message in checkout page

= 1.0.0 (January 15, 2016) =

- Initial release

