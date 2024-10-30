<?php

defined('ABSPATH') || exit;

class Complete_PayPal_Payments_WooCommerce_API_Request {

    public $is_sandbox;
    public $token_url;
    public $paypal_oauth_api;
    public $client_id;
    public $secret_key;
    public $basicAuth;
    public $api_response;
    public $result;
    public $settings;
    public $api_request;
    protected static $_instance = null;
    public $api_log;

    public static function instance() {
        if (is_null(self::$_instance)) {
            self::$_instance = new self();
        }
        return self::$_instance;
    }

    public function __construct() {
        $this->cpp_load_class();
        $this->is_sandbox = 'yes' === $this->settings->get('testmode', 'no');
        $this->paymentaction = $this->settings->get('paymentaction', 'capture');
        if ($this->is_sandbox) {
            $this->token_url = 'https://api-m.sandbox.paypal.com/v1/oauth2/token';
            $this->paypal_oauth_api = 'https://api-m.sandbox.paypal.com/v1/oauth2/token/';
            $this->client_id = $this->settings->get('sandbox_client_id');
            $this->secret_key = $this->settings->get('sandbox_secret_id');
            $this->basicAuth = base64_encode($this->client_id . ":" . $this->secret_key);
        } else {
            $this->token_url = 'https://api-m.paypal.com/v1/oauth2/token';
            $this->paypal_oauth_api = 'https://api-m.paypal.com/v1/oauth2/token/';
            $this->client_id = $this->settings->get('api_client_id');
            $this->secret_key = $this->settings->get('api_secret');
            $this->basicAuth = base64_encode($this->client_id . ":" . $this->secret_key);
        }
    }

    public function request($url, $args, $action_name = 'default') {
        try {
            $this->result = wp_remote_get($url, $args);
            return $this->api_response->parse_response($this->result, $url, $args, $action_name);
        } catch (Exception $ex) {
            $this->api_log->log("The exception was created on line: " . $ex->getLine(), 'error');
            $this->api_log->log($ex->getMessage(), 'error');
        }
    }

    public function cpp_load_class() {
        try {
            if (!class_exists('Complete_PayPal_Payments_WooCommerce_Response')) {
                include_once COMPLETE_PAYPAL_PAYMENTS_FOR_WOOCOMMERCE_DIR . '/includes/class-complete-paypal-payments-for-woocommerce-response.php';
            }
            if (!class_exists('Complete_PayPal_Payments_WooCommerce_Settings')) {
                include_once COMPLETE_PAYPAL_PAYMENTS_FOR_WOOCOMMERCE_DIR . '/includes/class-complete-paypal-payments-for-woocommerce-settings.php';
            }
            if (!class_exists('Complete_PayPal_Payments_WooCommerce_Log')) {
                include_once COMPLETE_PAYPAL_PAYMENTS_FOR_WOOCOMMERCE_DIR . '/includes/class-complete-paypal-payments-for-woocommerce-log.php';
            }
            $this->api_log = Complete_PayPal_Payments_WooCommerce_Log::instance();
            $this->settings = Complete_PayPal_Payments_WooCommerce_Settings::instance();
            $this->api_response = Complete_PayPal_Payments_WooCommerce_Response::instance();
        } catch (Exception $ex) {
            $this->api_log->log("The exception was created on line: " . $ex->getLine(), 'error');
            $this->api_log->log($ex->getMessage(), 'error');
        }
    }

}
