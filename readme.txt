=== SimplePay Official Wordpress Plugin ===
Contributors: simplepayng
Tags: simplepay, payments, payment gateway, visa, mastercard, verve
Requires at least: 3.6
Tested up to: 4.4.2
Stable tag: 1.2.1
License: MIT

SimplePay is the best Online Payment Gateway for the Nigerian market.


== Description ==

SimplePay offers the fastest and easiest way to send and receive money online. The innovative payment solution enables online businesses to integrate payments into their websites easily. Customize the check-out process the way you want.

Super-fast account activation. Top-notch customer support. Support for all currencies and all major card brands like MasterCard, Visa and Verve.

If you don't have a SimplePay account you can register for one at: https://www.simplepay.ng/registration/

= Features =

* Quick installation and setup.
* Easily take payment for a service from your site via SimplePay.
* Complete support for woocommerce e-commerce plugin 
* Create buy buttons for your products or services on the fly and embed it anywhere on your site using a user-friendly shortcode.
* Accept donation on your WordPress site for a cause.
* View purchase orders form buy butons on your WordPress admin dashboard.
* Allow users to automatically download the digital file after the purchase is complete.

= Buy Button Shortcode Attributes =

In order to create a buy button insert the following shortcode into a post/page.

`[accept_simplepay_button_payment]`

It supports the following attributes in the shortcode -

    name:
    (string) (required) Name of the product
    Possible Values: 'Awesome Script', 'My Ebook', 'Wooden Table' etc.

    price:
    (number) (required) Price of the product or item
    Possible Values: '2000', '3000', etc.

    quantity:
    (number) (optional) Number of products to be charged.
    Possible Values: '1', '5' etc.
    Default: 1
    
    url:
    (URL) (optional) URL of the downloadable file.
    Possible Values: http://example.com/my-downloads/product.zip

    button_text:
    (string) (optional) Label of the payment button
    Possible Values: 'Buy Now', 'Pay Now' etc

    button_style:
    (string) (optional) CSS class to be applyed to button
    Default: 'simplepay-button-style'

`[accept_simplepay_button_payment name="Cool Script" price="50" url="http://example.com/downloads/my-script.zip" button_text="Buy Now"]`

= Missing some feature? =
Please send us an email to support@simplepay.ng

= Contribute =
To contribute to this plugin feel free to fork it on GitHub - https://github.com/simplepayng/simplepay-wordpress

== Installation ==

= Requirements =
* PHP 5.2 or higher
* Wordpress 3.6 or higher
* cURL 7.30.x or higher

= Instalation steps =
1. Upload simplepay directory to the /wp-content/plugins/ directory
2. Activate the plugin through the 'Plugins' menu in WordPress
3. Register a free account on https://www.simplepay.ng
4. Insert Api Keys in plugin settings


== Changelog ==

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

