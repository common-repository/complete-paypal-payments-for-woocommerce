<?php

/**
 * @since      1.0.0
 * @package    Complete_PayPal_Payments_WooCommerce
 * @subpackage Complete_PayPal_Payments_WooCommerce/includes
 * @author     Eric Babin <completepaypalpayments@gmail.com>
 */
class Complete_PayPal_Payments_WooCommerce_i18n {

    /**
     * @since    1.0.0
     */
    public function load_plugin_textdomain() {

        load_plugin_textdomain(
                'complete-paypal-payments-for-woocommerce', false, dirname(dirname(plugin_basename(__FILE__))) . '/languages/'
        );
    }

}
