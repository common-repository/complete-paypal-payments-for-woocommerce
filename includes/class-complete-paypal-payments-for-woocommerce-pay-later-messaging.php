<?php

defined('ABSPATH') || exit;

class Complete_PayPal_Payments_WooCommerce_Pay_Later {

    public $setting_obj;
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
        $this->cpp_get_properties();
        $this->cpp_pay_later_messaging_properties();
        $this->cpp_add_hooks();
    }

    public function cpp_load_class() {
        try {
            if (!class_exists('Complete_PayPal_Payments_WooCommerce_Settings')) {
                include_once COMPLETE_PAYPAL_PAYMENTS_FOR_WOOCOMMERCE_DIR . '/includes/class-complete-paypal-payments-for-woocommerce-settings.php';
            }
            if (!class_exists('Complete_PayPal_Payments_WooCommerce_Log')) {
                include_once COMPLETE_PAYPAL_PAYMENTS_FOR_WOOCOMMERCE_DIR . '/includes/class-complete-paypal-payments-for-woocommerce-log.php';
            }
            $this->setting_obj = Complete_PayPal_Payments_WooCommerce_Settings::instance();
            $this->settings = $this->setting_obj->get_load();
            $this->api_log = Complete_PayPal_Payments_WooCommerce_Log::instance();
        } catch (Exception $ex) {
            $this->api_log->log("The exception was created on line: " . $ex->getLine(), 'error');
            $this->api_log->log($ex->getMessage(), 'error');
        }
    }

    public function cpp_get_properties() {
        $this->title = $this->setting_obj->get('title', 'PayPal');
        $this->enabled = 'yes' === $this->setting_obj->get('enabled', 'no');
        $this->is_sandbox = 'yes' === $this->setting_obj->get('testmode', 'no');
        if ($this->is_sandbox) {
            $this->client_id = $this->setting_obj->get('sandbox_client_id');
            $this->secret = $this->setting_obj->get('sandbox_api_secret');
        } else {
            $this->client_id = $this->setting_obj->get('api_client_id');
            $this->secret = $this->setting_obj->get('api_secret');
        }
        $this->enabled_pay_later_messaging = 'yes' === $this->setting_obj->get('enabled_pay_later_messaging', 'no');
        $this->pay_later_messaging_page_type = $this->setting_obj->get('pay_later_messaging_page_type', array('home', 'category', 'product', 'cart', 'payment'));
        if (empty($this->pay_later_messaging_page_type)) {
            $this->enabled_pay_later_messaging = false;
        }
    }

    public function cpp_pay_later_messaging_properties() {
        if ($this->enabled_pay_later_messaging) {
            $this->pay_later_messaging_home_shortcode = 'yes' === $this->setting_obj->get('pay_later_messaging_home_shortcode', 'no');
            $this->pay_later_messaging_category_shortcode = 'yes' === $this->setting_obj->get('pay_later_messaging_category_shortcode', 'no');
            $this->pay_later_messaging_product_shortcode = 'yes' === $this->setting_obj->get('pay_later_messaging_product_shortcode', 'no');
            $this->pay_later_messaging_cart_shortcode = 'yes' === $this->setting_obj->get('pay_later_messaging_cart_shortcode', 'no');
            $this->pay_later_messaging_payment_shortcode = 'yes' === $this->setting_obj->get('pay_later_messaging_payment_shortcode', 'no');
        }
    }

    public function cpp_add_hooks() {
        if ($this->enabled_pay_later_messaging && $this->is_valid_for_use()) {
            if ($this->is_paypal_pay_later_messaging_enable_for_page($page = 'home') && $this->pay_later_messaging_home_shortcode === false) {
                add_filter('the_content', array($this, 'cpp_pay_later_messaging_home_page_content'), 10);
                add_action('woocommerce_before_shop_loop', array($this, 'cpp_pay_later_messaging_home_page'), 10);
            }
            if ($this->is_paypal_pay_later_messaging_enable_for_page($page = 'category') && $this->pay_later_messaging_category_shortcode === false) {
                add_action('woocommerce_before_shop_loop', array($this, 'cpp_pay_later_messaging_category_page'), 10);
            }
            if ($this->is_paypal_pay_later_messaging_enable_for_page($page = 'product') && $this->pay_later_messaging_product_shortcode === false) {
                add_action('woocommerce_single_product_summary', array($this, 'cpp_pay_later_messaging_product_page'), 11);
            }
            if ($this->is_paypal_pay_later_messaging_enable_for_page($page = 'cart') && $this->pay_later_messaging_cart_shortcode === false) {
                add_action('woocommerce_before_cart_table', array($this, 'cpp_pay_later_messaging_cart_page'), 9);
                add_action('woocommerce_proceed_to_checkout', array($this, 'cpp_pay_later_messaging_cart_page'), 10);
            }
            if ($this->is_paypal_pay_later_messaging_enable_for_page($page = 'payment') && $this->pay_later_messaging_payment_shortcode === false) {
                add_action('woocommerce_before_checkout_form', array($this, 'cpp_pay_later_messaging_payment_page'), 4);
                add_action('cpp_display_paypal_button_checkout_page', array($this, 'cpp_pay_later_messaging_payment_page'), 9);
            }
            add_shortcode('cpp_bnpl_message', array($this, 'cpp_bnpl_message_shortcode'), 10);
        }
    }

    public function is_valid_for_use() {
        if (!empty($this->client_id) && !empty($this->secret) && $this->enabled) {
            return true;
        }
        return false;
    }

    public function cpp_pay_later_messaging_home_page_content($content) {
        if ((is_home() || is_front_page())) {
            wp_enqueue_script('psb-checkout-js');
            wp_enqueue_script('psb-pay-later-messaging-home', COMPLETE_PAYPAL_PAYMENTS_FOR_WOOCOMMERCE_ASSET_URL . 'public/js/pay-later-messaging/home.js', array('jquery'), COMPLETE_PAYPAL_PAYMENTS_FOR_WOOCOMMERCE_VERSION, true);
            $this->cpp_paypal_pay_later_messaging_js_enqueue($placement = 'home');
            $content = '<div class="cpp_message_home"></div>' . $content;
            return $content;
        }
        return $content;
    }

    public function cpp_pay_later_messaging_home_page() {
        if (is_shop()) {
            wp_enqueue_script('psb-checkout-js');
            wp_enqueue_script('psb-pay-later-messaging-home', COMPLETE_PAYPAL_PAYMENTS_FOR_WOOCOMMERCE_ASSET_URL . 'public/js/pay-later-messaging/home.js', array('jquery'), COMPLETE_PAYPAL_PAYMENTS_FOR_WOOCOMMERCE_VERSION, true);
            $this->cpp_paypal_pay_later_messaging_js_enqueue($placement = 'home');
            echo '<div class="cpp_message_home"></div>';
        }
    }

    public function cpp_pay_later_messaging_category_page() {
        if (is_shop() === false && $this->pay_later_messaging_category_shortcode === false) {
            wp_enqueue_script('psb-checkout-js');
            wp_enqueue_script('psb-pay-later-messaging-category', COMPLETE_PAYPAL_PAYMENTS_FOR_WOOCOMMERCE_ASSET_URL . 'public/js/pay-later-messaging/category.js', array('jquery'), COMPLETE_PAYPAL_PAYMENTS_FOR_WOOCOMMERCE_VERSION, true);
            $this->cpp_paypal_pay_later_messaging_js_enqueue($placement = 'category');
            echo '<div class="cpp_message_category"></div>';
        }
    }

    public function cpp_pay_later_messaging_product_page() {
        wp_enqueue_script('psb-checkout-js');
        wp_enqueue_script('psb-pay-later-messaging-product', COMPLETE_PAYPAL_PAYMENTS_FOR_WOOCOMMERCE_ASSET_URL . 'public/js/pay-later-messaging/product.js', array('jquery'), COMPLETE_PAYPAL_PAYMENTS_FOR_WOOCOMMERCE_VERSION, true);
        $this->cpp_paypal_pay_later_messaging_js_enqueue($placement = 'product');
        echo '<div class="cpp_message_product"></div>';
    }

    public function cpp_pay_later_messaging_cart_page() {
        if (WC()->cart->is_empty()) {
            return false;
        }
        wp_enqueue_script('psb-checkout-js');
        wp_enqueue_script('psb-pay-later-messaging-cart', COMPLETE_PAYPAL_PAYMENTS_FOR_WOOCOMMERCE_ASSET_URL . 'public/js/pay-later-messaging/cart.js', array('jquery'), COMPLETE_PAYPAL_PAYMENTS_FOR_WOOCOMMERCE_VERSION, true);
        if (WC()->cart->needs_payment()) {
            $this->cpp_paypal_pay_later_messaging_js_enqueue($placement = 'cart');
            echo '<div class="cpp_message_cart"></div>';
        }
    }

    public function cpp_pay_later_messaging_payment_page() {
        if (WC()->cart->is_empty()) {
            return false;
        }
        if (has_active_session()) {
            return false;
        }
        wp_enqueue_script('psb-checkout-js');
        wp_enqueue_script('psb-pay-later-messaging-payment', COMPLETE_PAYPAL_PAYMENTS_FOR_WOOCOMMERCE_ASSET_URL . 'public/js/pay-later-messaging/payment.js', array('jquery'), COMPLETE_PAYPAL_PAYMENTS_FOR_WOOCOMMERCE_VERSION, true);
        $this->cpp_paypal_pay_later_messaging_js_enqueue($placement = 'payment');
        echo '<div class="cpp_message_payment"></div>';
    }

    public function is_paypal_pay_later_messaging_enable_for_page($page = '') {
        if (empty($page)) {
            return false;
        }
        if (in_array($page, $this->pay_later_messaging_page_type)) {
            return true;
        }
        return false;
    }

    public function cpp_paypal_pay_later_messaging_js_enqueue($placement = '', $atts = null) {
        if (!empty($placement)) {
            $enqueue_script_param = array();
            $enqueue_script_param['amount'] = $this->cpp_get_order_total();
            switch ($placement) {
                case 'home':
                    $required_keys = array(
                        'pay_later_messaging_home_layout_type' => 'flex',
                        'pay_later_messaging_home_text_layout_logo_type' => 'primary',
                        'pay_later_messaging_home_text_layout_logo_position' => 'left',
                        'pay_later_messaging_home_text_layout_text_size' => '12',
                        'pay_later_messaging_home_text_layout_text_color' => 'black',
                        'pay_later_messaging_home_flex_layout_color' => 'blue',
                        'pay_later_messaging_home_flex_layout_ratio' => '8x1'
                    );
                    foreach ($required_keys as $key => $value) {
                        $enqueue_script_param[$key] = isset($this->settings[$key]) ? $this->settings[$key] : $value;
                    }
                    wp_localize_script('psb-pay-later-messaging-home', 'cpp_pay_later_messaging', $enqueue_script_param);
                    break;
                case 'category':
                    $required_keys = array(
                        'pay_later_messaging_category_layout_type' => 'flex',
                        'pay_later_messaging_category_text_layout_logo_type' => 'primary',
                        'pay_later_messaging_category_text_layout_logo_position' => 'left',
                        'pay_later_messaging_category_text_layout_text_size' => '12',
                        'pay_later_messaging_category_text_layout_text_color' => 'black',
                        'pay_later_messaging_category_flex_layout_color' => 'blue',
                        'pay_later_messaging_category_flex_layout_ratio' => '8x1'
                    );
                    foreach ($required_keys as $key => $value) {
                        $enqueue_script_param[$key] = isset($this->settings[$key]) ? $this->settings[$key] : $value;
                    }
                    wp_localize_script('psb-pay-later-messaging-category', 'cpp_pay_later_messaging', $enqueue_script_param);
                    break;
                case 'product':
                    $required_keys = array(
                        'pay_later_messaging_product_layout_type' => 'text',
                        'pay_later_messaging_product_text_layout_logo_type' => 'primary',
                        'pay_later_messaging_product_text_layout_logo_position' => 'left',
                        'pay_later_messaging_product_text_layout_text_size' => '12',
                        'pay_later_messaging_product_text_layout_text_color' => 'black',
                        'pay_later_messaging_product_flex_layout_color' => 'blue',
                        'pay_later_messaging_product_flex_layout_ratio' => '8x1'
                    );
                    foreach ($required_keys as $key => $value) {
                        $enqueue_script_param[$key] = isset($this->settings[$key]) ? $this->settings[$key] : $value;
                    }
                    wp_localize_script('psb-pay-later-messaging-product', 'cpp_pay_later_messaging', $enqueue_script_param);
                    break;
                case 'cart':
                    $required_keys = array(
                        'pay_later_messaging_cart_layout_type' => 'text',
                        'pay_later_messaging_cart_text_layout_logo_type' => 'primary',
                        'pay_later_messaging_cart_text_layout_logo_position' => 'left',
                        'pay_later_messaging_cart_text_layout_text_size' => '12',
                        'pay_later_messaging_cart_text_layout_text_color' => 'black',
                        'pay_later_messaging_cart_flex_layout_color' => 'blue',
                        'pay_later_messaging_cart_flex_layout_ratio' => '8x1'
                    );
                    foreach ($required_keys as $key => $value) {
                        $enqueue_script_param[$key] = isset($this->settings[$key]) ? $this->settings[$key] : $value;
                    }
                    wp_localize_script('psb-pay-later-messaging-cart', 'cpp_pay_later_messaging', $enqueue_script_param);
                    break;
                case 'payment':
                    $required_keys = array(
                        'pay_later_messaging_payment_layout_type' => 'text',
                        'pay_later_messaging_payment_text_layout_logo_type' => 'primary',
                        'pay_later_messaging_payment_text_layout_logo_position' => 'left',
                        'pay_later_messaging_payment_text_layout_text_size' => '12',
                        'pay_later_messaging_payment_text_layout_text_color' => 'black',
                        'pay_later_messaging_payment_flex_layout_color' => 'blue',
                        'pay_later_messaging_payment_flex_layout_ratio' => '8x1'
                    );
                    foreach ($required_keys as $key => $value) {
                        $enqueue_script_param[$key] = isset($this->settings[$key]) ? $this->settings[$key] : $value;
                    }
                    wp_localize_script('psb-pay-later-messaging-payment', 'cpp_pay_later_messaging', $enqueue_script_param);
                    break;
                case 'shortcode':
                    $atts['amount'] = $enqueue_script_param['amount'];
                    wp_localize_script('psb-pay-later-messaging-shortcode', 'cpp_pay_later_messaging', $atts);
                    break;
                default:
                    break;
            }
        }
    }

    public function cpp_get_default_attribute_pay_later_messaging($placement = '') {
        if (!empty($placement)) {
            $enqueue_script_param = array();
            $enqueue_script_param['amount'] = $this->cpp_get_order_total();
            switch ($placement) {
                case 'home':
                    $required_keys = array(
                        'pay_later_messaging_home_layout_type' => 'flex',
                        'pay_later_messaging_home_text_layout_logo_type' => 'primary',
                        'pay_later_messaging_home_text_layout_logo_position' => 'left',
                        'pay_later_messaging_home_text_layout_text_size' => '12',
                        'pay_later_messaging_home_text_layout_text_color' => 'black',
                        'pay_later_messaging_home_flex_layout_color' => 'blue',
                        'pay_later_messaging_home_flex_layout_ratio' => '8x1'
                    );
                    foreach ($required_keys as $key => $value) {
                        $enqueue_script_param[$key] = isset($this->settings[$key]) ? $this->settings[$key] : $value;
                    }
                    return $enqueue_script_param;
                case 'category':
                    $required_keys = array(
                        'pay_later_messaging_category_layout_type' => 'flex',
                        'pay_later_messaging_category_text_layout_logo_type' => 'primary',
                        'pay_later_messaging_category_text_layout_logo_position' => 'left',
                        'pay_later_messaging_category_text_layout_text_size' => '12',
                        'pay_later_messaging_category_text_layout_text_color' => 'black',
                        'pay_later_messaging_category_flex_layout_color' => 'blue',
                        'pay_later_messaging_category_flex_layout_ratio' => '8x1'
                    );
                    foreach ($required_keys as $key => $value) {
                        $enqueue_script_param[$key] = isset($this->settings[$key]) ? $this->settings[$key] : $value;
                    }
                    return $enqueue_script_param;
                case 'product':
                    $required_keys = array(
                        'pay_later_messaging_product_layout_type' => 'text',
                        'pay_later_messaging_product_text_layout_logo_type' => 'primary',
                        'pay_later_messaging_product_text_layout_logo_position' => 'left',
                        'pay_later_messaging_product_text_layout_text_size' => '12',
                        'pay_later_messaging_product_text_layout_text_color' => 'black',
                        'pay_later_messaging_product_flex_layout_color' => 'blue',
                        'pay_later_messaging_product_flex_layout_ratio' => '8x1'
                    );
                    foreach ($required_keys as $key => $value) {
                        $enqueue_script_param[$key] = isset($this->settings[$key]) ? $this->settings[$key] : $value;
                    }
                    return $enqueue_script_param;
                case 'cart':
                    $required_keys = array(
                        'pay_later_messaging_cart_layout_type' => 'text',
                        'pay_later_messaging_cart_text_layout_logo_type' => 'primary',
                        'pay_later_messaging_cart_text_layout_logo_position' => 'left',
                        'pay_later_messaging_cart_text_layout_text_size' => '12',
                        'pay_later_messaging_cart_text_layout_text_color' => 'black',
                        'pay_later_messaging_cart_flex_layout_color' => 'blue',
                        'pay_later_messaging_cart_flex_layout_ratio' => '8x1'
                    );
                    foreach ($required_keys as $key => $value) {
                        $enqueue_script_param[$key] = isset($this->settings[$key]) ? $this->settings[$key] : $value;
                    }
                    return $enqueue_script_param;
                case 'payment':
                    $required_keys = array(
                        'pay_later_messaging_payment_layout_type' => 'text',
                        'pay_later_messaging_payment_text_layout_logo_type' => 'primary',
                        'pay_later_messaging_payment_text_layout_logo_position' => 'left',
                        'pay_later_messaging_payment_text_layout_text_size' => '12',
                        'pay_later_messaging_payment_text_layout_text_color' => 'black',
                        'pay_later_messaging_payment_flex_layout_color' => 'blue',
                        'pay_later_messaging_payment_flex_layout_ratio' => '8x1'
                    );
                    foreach ($required_keys as $key => $value) {
                        $enqueue_script_param[$key] = isset($this->settings[$key]) ? $this->settings[$key] : $value;
                    }
                    return $enqueue_script_param;
                default:
                    break;
            }
        }
    }

    public function cpp_get_order_total() {
        global $product;
        $total = 0;
        $order_id = absint(get_query_var('order-pay'));
        if (is_product()) {
            $total = ( is_a($product, \WC_Product::class) ) ? wc_get_price_including_tax($product) : 0;
        } elseif (0 < $order_id) {
            $order = wc_get_order($order_id);
            $total = (float) $order->get_total();
        } elseif (isset(WC()->cart) && 0 < WC()->cart->total) {
            $total = (float) WC()->cart->total;
        }
        return $total;
    }

    public function cpp_bnpl_message_shortcode($atts) {
        if (empty($atts['placement'])) {
            return '';
        }
        if (!in_array($atts['placement'], array('home', 'category', 'product', 'cart', 'payment'))) {
            return;
        }
        if ($this->is_paypal_pay_later_messaging_enable_for_page($page = $atts['placement']) === false) {
            return false;
        }
        if ($this->is_paypal_pay_later_messaging_enable_for_shoerpage($page = $atts['placement']) === false) {
            return false;
        }
        $placement = $atts['placement'];
        if (!isset($atts['style'])) {
            $atts['style'] = $this->cpp_pay_later_messaging_get_default_value('style', $placement);
        }
        if ($atts['style'] === 'text') {
            $default_array = array(
                'placement' => 'home',
                'style' => $atts['style'],
                'logotype' => $this->cpp_pay_later_messaging_get_default_value('logotype', $placement),
                'logoposition' => $this->cpp_pay_later_messaging_get_default_value('logoposition', $placement),
                'textsize' => $this->cpp_pay_later_messaging_get_default_value('textsize', $placement),
                'textcolor' => $this->cpp_pay_later_messaging_get_default_value('textcolor', $placement),
            );
        } else {
            $default_array = array(
                'placement' => 'home',
                'style' => $atts['style'],
                'color' => $this->cpp_pay_later_messaging_get_default_value('color', $placement),
                'ratio' => $this->cpp_pay_later_messaging_get_default_value('ratio', $placement)
            );
        }
        $atts = array_merge(
                $default_array, (array) $atts
        );
        wp_enqueue_script('psb-checkout-js');
        wp_enqueue_script('psb-pay-later-messaging-shortcode', COMPLETE_PAYPAL_PAYMENTS_FOR_WOOCOMMERCE_ASSET_URL . 'public/js/pay-later-messaging/shortcode.js', array('jquery'), COMPLETE_PAYPAL_PAYMENTS_FOR_WOOCOMMERCE_VERSION, true);
        $this->cpp_paypal_pay_later_messaging_js_enqueue($placement_default = 'shortcode', $atts);
        return '<div class="cpp_message_shortcode"></div>';
    }

    public function cpp_pay_later_messaging_get_default_value($key, $placement) {
        if (!empty($key) && !empty($placement)) {
            $param = $this->cpp_get_default_attribute_pay_later_messaging($placement);
            $map_keys = array('placement' => '', 'style' => 'pay_later_messaging_default_layout_type', 'logotype' => 'pay_later_messaging_default_text_layout_logo_type', 'logoposition' => 'pay_later_messaging_default_text_layout_logo_position', 'textsize' => 'pay_later_messaging_default_text_layout_text_size', 'textcolor' => 'pay_later_messaging_default_text_layout_text_color', 'color' => 'pay_later_messaging_default_flex_layout_color', 'ratio' => 'pay_later_messaging_default_flex_layout_ratio');
            if (!empty($map_keys[$key])) {
                $default_key = str_replace('default', $placement, $map_keys[$key]);
                if (!empty($param[$default_key])) {
                    return $param[$default_key];
                }
            }
            return '';
        }
    }

    public function is_paypal_pay_later_messaging_enable_for_shoerpage($page = '') {
        switch ($page) {
            case 'home':
                if ($this->pay_later_messaging_home_shortcode) {
                    return true;
                }
                break;
            case 'category':
                if ($this->pay_later_messaging_category_shortcode) {
                    return true;
                }
                break;
            case 'product':
                if ($this->pay_later_messaging_product_shortcode) {
                    return true;
                }
                break;
            case 'cart':
                if ($this->pay_later_messaging_cart_shortcode) {
                    return true;
                }
                break;
            case 'payment':
                if ($this->pay_later_messaging_payment_shortcode) {
                    return true;
                }
                break;
            default:
                break;
        }
        return false;
    }

}
