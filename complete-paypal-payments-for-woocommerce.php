<?php

/**
 * @wordpress-plugin
 * Plugin Name:       Complete PayPal Payments For WooCommerce
 * Plugin URI:        https://github.com/completepaypalpayments/complete-paypal-payments-for-woocommerce
 * Description:       PayPal Checkout with Smart Payment Buttons gives your buyers a simplified and secure checkout experience.
 * Version:           1.0.7
 * Author:            Eric Babin, completepaypalpayments
 * Author URI:        https://profiles.wordpress.org/completepaypalpayments/
 * License:           GPLv3
 * License URI:       http://www.gnu.org/licenses/gpl-3.0.html
 * Text Domain:       complete-paypal-payments-for-woocommerce
 * Domain Path:       /languages
 * WC tested up to: 7.7.0
 * WC requires at least: 3.0.0
 */
// If this file is called directly, abort.
if (!defined('WPINC')) {
    die;
}

define('COMPLETE_PAYPAL_PAYMENTS_FOR_WOOCOMMERCE_VERSION', '1.0.7');
if (!defined('COMPLETE_PAYPAL_PAYMENTS_FOR_WOOCOMMERCE_PATH')) {
    define('COMPLETE_PAYPAL_PAYMENTS_FOR_WOOCOMMERCE_PATH', untrailingslashit(plugin_dir_path(__FILE__)));
}
if (!defined('COMPLETE_PAYPAL_PAYMENTS_FOR_WOOCOMMERCE_URL')) {
    define('COMPLETE_PAYPAL_PAYMENTS_FOR_WOOCOMMERCE_URL', plugin_dir_url(__FILE__));
}
if (!defined('COMPLETE_PAYPAL_PAYMENTS_FOR_WOOCOMMERCE_DIR')) {
    define('COMPLETE_PAYPAL_PAYMENTS_FOR_WOOCOMMERCE_DIR', dirname(__FILE__));
}
if (!defined('COMPLETE_PAYPAL_PAYMENTS_FOR_WOOCOMMERCE_BASENAME')) {
    define('COMPLETE_PAYPAL_PAYMENTS_FOR_WOOCOMMERCE_BASENAME', plugin_basename(__FILE__));
}
if (!defined('COMPLETE_PAYPAL_PAYMENTS_FOR_WOOCOMMERCE_ASSET_URL')) {
    define('COMPLETE_PAYPAL_PAYMENTS_FOR_WOOCOMMERCE_ASSET_URL', plugin_dir_url(__FILE__));
}

function activate_complete_paypal_payments_woocommerce() {
    require_once plugin_dir_path(__FILE__) . 'includes/class-complete-paypal-payments-for-woocommerce-activator.php';
    Complete_PayPal_Payments_WooCommerce_Activator::activate();
}

function deactivate_complete_paypal_payments_woocommerce() {
    require_once plugin_dir_path(__FILE__) . 'includes/class-complete-paypal-payments-for-woocommerce-deactivator.php';
    Complete_PayPal_Payments_WooCommerce_Deactivator::deactivate();
}

register_activation_hook(__FILE__, 'activate_complete_paypal_payments_woocommerce');
register_deactivation_hook(__FILE__, 'deactivate_complete_paypal_payments_woocommerce');
require plugin_dir_path(__FILE__) . 'includes/class-complete-paypal-payments-for-woocommerce.php';

function run_complete_paypal_payments_woocommerce() {
    $plugin = new Complete_PayPal_Payments_WooCommerce();
    $plugin->run();
}

add_action('plugins_loaded', 'load_complete_paypal_payments_woocommerce', 11);

function load_complete_paypal_payments_woocommerce() {
    if(function_exists('WC')) {
        run_complete_paypal_payments_woocommerce();
    }
}
