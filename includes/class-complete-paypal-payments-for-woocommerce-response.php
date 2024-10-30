<?php

defined('ABSPATH') || exit;

class Complete_PayPal_Payments_WooCommerce_Response {

    public $api_log;
    public $settings;
    protected static $_instance = null;

    public static function instance() {
        if (is_null(self::$_instance)) {
            self::$_instance = new self();
        }
        return self::$_instance;
    }

    public function __construct() {
        $this->cpp_load_class();
        $this->is_sandbox = 'yes' === $this->settings->get('testmode', 'no');
    }

    public function parse_response($paypal_api_response, $url, $request, $action_name) {
        try {
            if (is_wp_error($paypal_api_response)) {
                $response = array(
                    'result' => 'faild',
                    'body' => array('error_message' => $paypal_api_response->get_error_message(), 'error_code' => $paypal_api_response->get_error_code())
                );
            } else {
                $body = wp_remote_retrieve_body($paypal_api_response);
                $response = !empty($body) ? json_decode($body, true) : '';
            }
            do_action('cpp_request_respose_data', $request, $response, $action_name);
            $this->cpp_write_log($url, $request, $paypal_api_response, $action_name);
            return $response;
        } catch (Exception $ex) {
            $this->api_log->log("The exception was created on line: " . $ex->getLine(), 'error');
            $this->api_log->log($ex->getMessage(), 'error');
        }
    }

    public function cpp_write_log($url, $request, $response, $action_name = 'Exception') {
        global $wp_version;
        $environment = ($this->is_sandbox === true) ? 'SANDBOX' : 'LIVE';
        if (strpos($action_name, 'webhook') !== false) {
            $this->api_log->webhook_log('PayPal Environment: ' . $environment);
            $this->api_log->webhook_log('WordPress Version: ' . $wp_version);
            $this->api_log->webhook_log('WooCommerce Version: ' . WC()->version);
            $this->api_log->webhook_log('Complete PayPal Payments For WooCommerce Version Version: ' . COMPLETE_PAYPAL_PAYMENTS_FOR_WOOCOMMERCE_VERSION);
            $this->api_log->webhook_log('Action: ' . $action_name);
            $this->api_log->webhook_log('Request URL: ' . $url);
            $this->api_log->webhook_log('Request: ' . wp_json_encode($request));
            $this->api_log->webhook_log('Response Code: ' . wp_remote_retrieve_response_code($response));
            $this->api_log->webhook_log('Response Message: ' . wp_remote_retrieve_response_message($response));
            $this->api_log->webhook_log('Response Body: ' . wp_json_encode(wp_remote_retrieve_body($response)));
        } else {
            $this->api_log->log('PayPal Environment: ' . $environment);
            $this->api_log->log('WordPress Version: ' . $wp_version);
            $this->api_log->log('WooCommerce Version: ' . WC()->version);
            $this->api_log->log('Complete PayPal Payments For WooCommerce Version: ' . COMPLETE_PAYPAL_PAYMENTS_FOR_WOOCOMMERCE_VERSION);
            $this->api_log->log('Action: ' . $action_name);
            $this->api_log->log('Request URL: ' . $url);
            $this->api_log->log('PayPal Debug ID: ' . wp_remote_retrieve_header($response, 'paypal-debug-id'));
            if (!empty($request['body']) && is_array($request['body'])) {
                $this->api_log->log('Request Body: ' . wp_json_encode($request['body']));
            } elseif (isset($request['body']) && !empty($request['body']) && is_string($request['body'])) {
                $this->api_log->log('Request Body: ' . $request['body']);
            }
            $this->api_log->log('Response Code: ' . wp_remote_retrieve_response_code($response));
            $this->api_log->log('Response Message: ' . wp_remote_retrieve_response_message($response));
            $this->api_log->log('Response Body: ' . wp_remote_retrieve_body($response));
        }
    }

    public function cpp_load_class() {
        try {
            if (!class_exists('Complete_PayPal_Payments_WooCommerce_Log')) {
                include_once COMPLETE_PAYPAL_PAYMENTS_FOR_WOOCOMMERCE_DIR . '/includes/class-complete-paypal-payments-for-woocommerce-log.php';
            }
            if (!class_exists('Complete_PayPal_Payments_WooCommerce_Settings')) {
                include_once COMPLETE_PAYPAL_PAYMENTS_FOR_WOOCOMMERCE_DIR . '/includes/class-complete-paypal-payments-for-woocommerce-settings.php';
            }
            $this->settings = Complete_PayPal_Payments_WooCommerce_Settings::instance();
            $this->api_log = Complete_PayPal_Payments_WooCommerce_Log::instance();
        } catch (Exception $ex) {
            $this->api_log->log("The exception was created on line: " . $ex->getLine(), 'error');
            $this->api_log->log($ex->getMessage(), 'error');
        }
    }

}
