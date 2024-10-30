<?php

defined('ABSPATH') || exit;

class Complete_PayPal_Payments_WooCommerce_Log {

    public $log_enabled = true;
    public $logger = false;
    protected static $_instance = null;

    public static function instance() {
        if (is_null(self::$_instance)) {
            self::$_instance = new self();
        }
        return self::$_instance;
    }

    public function __construct() {
        $this->cpp_load_class();
        $this->log_enabled = 'yes' === $this->settings->get('debug', 'yes');
    }

    public function log($message, $level = 'info') {
        if ($this->log_enabled) {
            if (empty($this->logger)) {
                $this->logger = wc_get_logger();
            }
            $this->logger->log($level, $message, array('source' => 'complete_paypal_payments'));
        }
    }

    public function webhook_log($message, $level = 'info') {
        if ($this->log_enabled) {
            if (empty($this->logger)) {
                $this->logger = wc_get_logger();
            }
            $this->logger->log($level, $message, array('source' => 'cpp_webhook'));
        }
    }

    public function temp_log($message, $level = 'info') {
        if ($this->log_enabled) {
            if (empty($this->logger)) {
                $this->logger = wc_get_logger();
            }
            $this->logger->log($level, $message, array('source' => 'cpp_temp'));
        }
    }

    public function cpp_load_class() {
        try {
            if (!class_exists('Complete_PayPal_Payments_WooCommerce_Settings')) {
                include_once COMPLETE_PAYPAL_PAYMENTS_FOR_WOOCOMMERCE_DIR . '/includes/class-complete-paypal-payments-for-woocommerce-settings.php';
            }
            $this->settings = Complete_PayPal_Payments_WooCommerce_Settings::instance();
        } catch (Exception $ex) {
            $this->log("The exception was created on line: " . $ex->getLine(), 'error');
            $this->log($ex->getMessage(), 'error');
        }
    }

}
