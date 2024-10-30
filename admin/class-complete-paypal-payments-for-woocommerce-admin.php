<?php

/**
 * @package    Complete_PayPal_Payments_WooCommerce
 * @subpackage Complete_PayPal_Payments_WooCommerce/admin
 * @author     Eric Babin <completepaypalpayments@gmail.com>
 */
class Complete_PayPal_Payments_WooCommerce_Admin {

    private $plugin_name;
    private $version;

    public function __construct($plugin_name, $version) {

        $this->plugin_name = $plugin_name;
        $this->version = $version;
    }

    public function enqueue_scripts() {
        if (isset($_GET['section']) && 'complete_paypal_payments' === $_GET['section']) {
            wp_enqueue_script($this->plugin_name, plugin_dir_url(__FILE__) . 'js/complete-paypal-payments-for-woocommerce-admin.js', array('jquery'), time(), false);
            wp_localize_script($this->plugin_name, 'complete_paypal_payments', array(
                'woocommerce_currency' => get_woocommerce_currency(),
                'is_advanced_cards_available' => cpp_is_advanced_cards_available() ? 'yes' : 'no'
            )); 
        }
    }

}
