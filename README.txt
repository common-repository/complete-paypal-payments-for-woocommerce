=== Complete PayPal Payments For WooCommerce ===
Contributors: Eric Babin, completepaypalpayments
Tags: paypal, paypal checkout, paylater, Bancontact, BLIK, eps, giropay, iDEAL, Mercado Pago, Przelewy24, SEPA-Lastschrift, Sofort, Venmo
Requires at least: 5.8
Tested up to: 6.2.2
Requires PHP: 7.2
Stable tag: 1.0.7
License: License: GPLv3
License URI: http://www.gnu.org/licenses/gpl-3.0.html

PayPal Checkout with Smart Payment Buttons gives your buyers a simplified and secure checkout experience.

== Description ==
PayPal Checkout with Smart Payment Buttons gives your buyers a simplified and secure checkout experience. PayPal intelligently presents the most relevant payment types to your shoppers, automatically, making it easier for them to complete their purchase using methods like Pay with Venmo, PayPal Credit, credit card payments, iDEAL, Bancontact, Sofort, and other payment types.

= Introduction =

Easily add PayPal payment options to your WordPress / WooCommerce website.

 * PayPal Checkout.
 * Advanced credit and debit card payments.

List of Methods.

 * PayPal
 * Pay in 4
 * Venmo
 * American Express
 * Bancontact
 * BLIK
 * Discover
 * eps
 * giropay
 * iDEAL
 * MyBank
 * Mastercard
 * Przelewy24
 * Sofort
 * Visa
 
 
= Quality Control =
Payment processing can't go wrong.  It's as simple as that.  Our certified PayPal engineers have developed and thoroughly tested this plugin on the PayPal sandbox (test) servers to ensure your customers don't have problems paying you.  

= Seamless PayPal Integration =
Stop bouncing back and forth between WooCommerce and PayPal to manage and reconcile orders.  We've made sure to include all WooCommerce order data in PayPal transactions so that everything matches in both places.  If you're looking at a PayPal transaction details page it will have all of the same data as a WooCommerce order page, and vice-versa.  

= Get Involved =
Developers can contribute to the source code on the [Complete PayPal Payments For WooCommerce GitHub repository](https://github.com/completepaypalpayments/complete-paypal-payments-for-woocommerce).


== Installation ==
= Automatic installation =

Automatic installation is the easiest option as WordPress handles the file transfers itself and you don't need to leave your web browser. To do an automatic install of Complete PayPal Payments For WooCommerce, log in to your WordPress dashboard, navigate to the Plugins menu and click Add New.

= Manual Installation =

1. Unzip the files and upload the folder into your plugins folder (/wp-content/plugins/) overwriting older versions if they exist
2. Activate the plugin in your WordPress admin area.

== Frequently Asked Questions ==
= How do I create sandbox accounts for testing?=

* Login at http://developer.paypal.com.  
* Click the Applications tab in the top menu.
* Click Sandbox Accounts in the left sidebar menu.
* Click the Create Account button to create a new sandbox account.

= How to get phone numbers for PayPal Checkout orders?=

If you use PayPal Checkout, then you might have noticed that the checkout page does not contain the customer's phone number. If you have a PayPal business account, then you can require customer phone numbers from your PayPal account settings.

PayPal has the ability to provide the customer’s phone number which can be used to autopopulate your checkout page. We recommend you enable this option as it reduces the number of checkout fields your customer has to manually enter. It reduces friction and will improve conversion rates.

1) Login to your Sandbox or Production account.

2) On the Website payment preferences page, scroll down to the Contact telephone number section. If the billing phone number is optional on your checkout page, you can select the On (optional field). If it’s required, select the On (required field)

== Screenshots ==
1. Setting Panel.
2. Product Page
3. Cart Page

== Changelog ==

= 1.0.7 - 05.25.2023 =
* Fix - minor issue.

= 1.0.6 - 24.11.2022 =
* Feature - Adds  Top of the Smart button on checkout and cart page.

= 1.0.4 - 11.04.2022 =
* Fix - Use WP lang for PayPal request.

= 1.0.3 - 10.28.2022 =
* Fix - Resolved issue related to hide/show CC fields on checkout page.

= 1.0.2 - 10.25.2022 =
* Fix - Resolved multiple issue related to gateway setting.

= 1.0.1 - 10.17.2022 =
* Feature - Adds  Enable/Disable Send line item details to PayPal.
* Fix - Resolves billing phone number auto populate on checkout page issue.
* Fix - Hide/Show setting page setting fields.


= 1.0.0 =
 * PayPal Checkout (Smart Payment Buttons)
 * Advanced credit and debit card payments ( with 3D Secure for If you are based in Europe, you are subjected to PSD2. PayPal recommends this option )

== Upgrade Notice ==
After updating, make sure to clear any caching / CDN plugins you may be using.  Also, go into the plugin's gateway settings, review everything, and click Save even if you do not make any changes.