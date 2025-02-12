<?php

defined('ABSPATH') || exit;

if (!class_exists('Complete_PayPal_Payments_WooCommerce_Settings')) {

    class Complete_PayPal_Payments_WooCommerce_Settings {

        public $gateway_key;
        public $settings = array();
        protected static $_instance = null;

        public static function instance() {
            if (is_null(self::$_instance)) {
                self::$_instance = new self();
            }
            return self::$_instance;
        }

        public function __construct() {
            $this->gateway_key = 'woocommerce_complete_paypal_payments_settings';
        }

        public function get($id, $default = false) {
            if (!$this->has($id)) {
                return $default;
            }
            return $this->settings[$id];
        }

        public function get_load() {
            return get_option($this->gateway_key, array());
        }

        public function has($id) {
            $this->load();
            return array_key_exists($id, $this->settings);
        }

        public function set($id, $value) {
            $this->load();
            $this->settings[$id] = $value;
        }

        public function persist() {
            update_option($this->gateway_key, $this->settings);
        }

        public function load() {
            if ($this->settings) {
                return false;
            }
            $this->settings = get_option($this->gateway_key, array());

            $defaults = array(
                'title' => __('PayPal', 'complete-paypal-payments-for-woocommerce'),
                'description' => __(
                        'Accept PayPal, PayPal Credit and alternative payment types.', 'complete-paypal-payments-for-woocommerce'
                )
            );
            foreach ($defaults as $key => $value) {
                if (isset($this->settings[$key])) {
                    continue;
                }
                $this->settings[$key] = $value;
            }
            return true;
        }

        public function cpp_setting_fields() {
            $default_settings = array(
                'enabled' => array(
                    'title' => __('Enable/Disable', 'complete-paypal-payments-for-woocommerce'),
                    'type' => 'checkbox',
                    'label' => __('Enable PayPal Checkout', 'complete-paypal-payments-for-woocommerce'),
                    'description' => __('Check this box to enable the payment gateway. Leave unchecked to disable it.', 'complete-paypal-payments-for-woocommerce'),
                    'desc_tip' => true,
                    'default' => 'yes',
                ),
                'title' => array(
                    'title' => __('Title', 'complete-paypal-payments-for-woocommerce'),
                    'type' => 'text',
                    'description' => __('This controls the title which the user sees during checkout.', 'complete-paypal-payments-for-woocommerce'),
                    'default' => __('PayPal Checkout', 'complete-paypal-payments-for-woocommerce'),
                    'desc_tip' => true,
                ),
                'description' => array(
                    'title' => __('Description', 'complete-paypal-payments-for-woocommerce'),
                    'type' => 'text',
                    'description' => __('Payment method description that the customer will see on your checkout.', 'complete-paypal-payments-for-woocommerce'),
                    'default' => __('Accept PayPal, PayPal Credit and alternative payment types.', 'complete-paypal-payments-for-woocommerce'),
                    'desc_tip' => true,
                ),
                'api_details' => array(
                    'title' => __('Account Settings', 'complete-paypal-payments-for-woocommerce'),
                    'type' => 'title',
                    'description' => '',
                    'class' => 'cpp_separator_heading',
                ),
                'testmode' => array(
                    'title' => __('PayPal sandbox', 'complete-paypal-payments-for-woocommerce'),
                    'type' => 'checkbox',
                    'label' => __('Enable PayPal sandbox', 'complete-paypal-payments-for-woocommerce'),
                    'default' => 'no',
                    'description' => __('Check this box to enable test mode so that all transactions will hit PayPal’s sandbox server instead of the live server. This should only be used during development as no real transactions will occur when this is enabled.', 'complete-paypal-payments-for-woocommerce'),
                    'desc_tip' => true
                ),
                'live_api_details' => array(
                    'title' => __('PayPal REST API Details &nbsp;&nbsp;&nbsp;<a target="_blank" href="https://developer.paypal.com/dashboard/applications/live" style="text-decoration: none;">Get a PayPal Live Client ID and Secret</a>', 'complete-paypal-payments-for-woocommerce'),
                    'type' => 'title'
                ),
                'api_client_id' => array(
                    'title' => __('PayPal Client ID', 'complete-paypal-payments-for-woocommerce'),
                    'type' => 'password',
                    'description' => __('Enter your PayPal Client ID.', 'complete-paypal-payments-for-woocommerce'),
                    'default' => '',
                    'desc_tip' => true
                ),
                'api_secret' => array(
                    'title' => __('PayPal Secret', 'complete-paypal-payments-for-woocommerce'),
                    'type' => 'password',
                    'description' => __('Enter your PayPal Secret.', 'complete-paypal-payments-for-woocommerce'),
                    'default' => '',
                    'desc_tip' => true
                ),
                'sandbox_api_details' => array(
                    'title' => __('PayPal REST API Details &nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;<a target="_blank" href="https://developer.paypal.com/dashboard/applications/sandbox" style="text-decoration: none;">Get a PayPal Sandbox Client ID and Secret</a>', 'complete-paypal-payments-for-woocommerce'),
                    'type' => 'title'
                ),
                'sandbox_client_id' => array(
                    'title' => __('Sandbox Client ID', 'complete-paypal-payments-for-woocommerce'),
                    'type' => 'password',
                    'description' => __('Enter your PayPal Sandbox Client ID.', 'complete-paypal-payments-for-woocommerce'),
                    'default' => '',
                    'desc_tip' => true
                ),
                'sandbox_api_secret' => array(
                    'title' => __('Sandbox Secret', 'complete-paypal-payments-for-woocommerce'),
                    'type' => 'password',
                    'description' => __('Enter your PayPal Sandbox Secret.', 'complete-paypal-payments-for-woocommerce'),
                    'default' => '',
                    'desc_tip' => true
                )
            );

            $button_manager_settings = array(
                'product_button_settings' => array(
                    'title' => __('Product Page', 'complete-paypal-payments-for-woocommerce'),
                    'class' => '',
                    'description' => __('Enable the Product specific button settings, and the options set will be applied to the PayPal Smart buttons on your Product pages.', 'complete-paypal-payments-for-woocommerce'),
                    'type' => 'title',
                    'class' => 'cpp_separator_heading',
                ),
                'enable_product_button' => array(
                    'title' => __('Enable/Disable', 'complete-paypal-payments-for-woocommerce'),
                    'class' => '',
                    'type' => 'checkbox',
                    'label' => __('Enable PayPal Smart Button on the Product Pages.', 'complete-paypal-payments-for-woocommerce'),
                    'default' => 'yes',
                    'desc_tip' => true,
                    'description' => __('', 'complete-paypal-payments-for-woocommerce'),
                ),
                'product_disallowed_funding_methods' => array(
                    'title' => __('Hide Funding Method(s)', 'complete-paypal-payments-for-woocommerce'),
                    'type' => 'multiselect',
                    'class' => 'wc-enhanced-select cpp_product_button_settings',
                    'description' => __('Funding methods selected here will be hidden from buyers during checkout.', 'complete-paypal-payments-for-woocommerce'),
                    'default' => '',
                    'desc_tip' => true,
                    'options' => array(
                        'card' => __('Credit or Debit Card', 'complete-paypal-payments-for-woocommerce'),
                        'credit' => __('PayPal Credit', 'complete-paypal-payments-for-woocommerce'),
                        'paylater' => __('Pay Later', 'complete-paypal-payments-for-woocommerce'),
                        'bancontact' => __('Bancontact', 'complete-paypal-payments-for-woocommerce'),
                        'blik' => __('BLIK', 'complete-paypal-payments-for-woocommerce'),
                        'eps' => __('eps', 'complete-paypal-payments-for-woocommerce'),
                        'giropay' => __('giropay', 'complete-paypal-payments-for-woocommerce'),
                        'ideal' => __('iDEAL', 'complete-paypal-payments-for-woocommerce'),
                        'mercadopago' => __('Mercado Pago', 'complete-paypal-payments-for-woocommerce'),
                        'mybank' => __('MyBank', 'complete-paypal-payments-for-woocommerce'),
                        'p24' => __('Przelewy24', 'complete-paypal-payments-for-woocommerce'),
                        'sepa' => __('SEPA-Lastschrift', 'complete-paypal-payments-for-woocommerce'),
                        'sofort' => __('Sofort', 'complete-paypal-payments-for-woocommerce'),
                        'venmo' => __('Venmo', 'complete-paypal-payments-for-woocommerce')
                    ),
                ),
                'product_button_layout' => array(
                    'title' => __('Button Layout', 'complete-paypal-payments-for-woocommerce'),
                    'type' => 'select',
                    'class' => 'wc-enhanced-select cpp_product_button_settings',
                    'description' => __('Select Vertical for stacked buttons, and Horizontal for side-by-side buttons.', 'complete-paypal-payments-for-woocommerce'),
                    'default' => 'horizontal',
                    'desc_tip' => true,
                    'options' => array(
                        'horizontal' => __('Horizontal (Recommended)', 'complete-paypal-payments-for-woocommerce'),
                        'vertical' => __('Vertical', 'complete-paypal-payments-for-woocommerce')
                    ),
                ),
                'product_style_color' => array(
                    'title' => __('Button Color', 'complete-paypal-payments-for-woocommerce'),
                    'type' => 'select',
                    'class' => 'wc-enhanced-select cpp_product_button_settings',
                    'description' => __('Set the color you would like to use for the PayPal button.', 'complete-paypal-payments-for-woocommerce'),
                    'desc_tip' => true,
                    'default' => 'gold',
                    'options' => array(
                        'gold' => __('Gold (Recommended)', 'complete-paypal-payments-for-woocommerce'),
                        'blue' => __('Blue', 'complete-paypal-payments-for-woocommerce'),
                        'silver' => __('Silver', 'complete-paypal-payments-for-woocommerce'),
                        'white' => __('White', 'complete-paypal-payments-for-woocommerce'),
                        'black' => __('Black', 'complete-paypal-payments-for-woocommerce')
                    ),
                ),
                'product_style_shape' => array(
                    'title' => __('Button Shape', 'complete-paypal-payments-for-woocommerce'),
                    'type' => 'select',
                    'class' => 'wc-enhanced-select cpp_product_button_settings',
                    'description' => __('Set the shape you would like to use for the buttons.', 'complete-paypal-payments-for-woocommerce'),
                    'desc_tip' => true,
                    'default' => 'rect',
                    'options' => array(
                        'rect' => __('Rect (Recommended)', 'complete-paypal-payments-for-woocommerce'),
                        'pill' => __('Pill', 'complete-paypal-payments-for-woocommerce')
                    ),
                ),
                'product_button_label' => array(
                    'title' => __('Button Label', 'complete-paypal-payments-for-woocommerce'),
                    'type' => 'select',
                    'class' => 'wc-enhanced-select cpp_product_button_settings',
                    'description' => __('Set the label type you would like to use for the PayPal button.', 'complete-paypal-payments-for-woocommerce'),
                    'default' => 'paypal',
                    'desc_tip' => true,
                    'options' => array(
                        'paypal' => __('PayPal (Recommended)', 'complete-paypal-payments-for-woocommerce'),
                        'checkout' => __('Checkout', 'complete-paypal-payments-for-woocommerce'),
                        'buynow' => __('Buy Now', 'complete-paypal-payments-for-woocommerce'),
                        'pay' => __('Pay', 'complete-paypal-payments-for-woocommerce'),
                    ),
                ),
                'product_button_tagline' => array(
                    'title' => __('Tagline', 'complete-paypal-payments-for-woocommerce'),
                    'type' => 'checkbox',
                    'class' => 'cpp_product_button_settings',
                    'default' => 'yes',
                    'label' => __('Enable tagline', 'complete-paypal-payments-for-woocommerce'),
                    'desc_tip' => true,
                    'description' => __(
                            'Add the tagline. This line will only show up, if you select a horizontal layout.', 'complete-paypal-payments-for-woocommerce'
                    ),
                ),
                'cart_button_settings' => array(
                    'title' => __('Cart Page', 'complete-paypal-payments-for-woocommerce'),
                    'class' => '',
                    'description' => __('Enable the Cart specific button settings, and the options set will be applied to the PayPal buttons on your Cart page.', 'complete-paypal-payments-for-woocommerce'),
                    'type' => 'title',
                    'class' => 'cpp_separator_heading',
                ),
                'enable_cart_button' => array(
                    'title' => __('Enable/Disable', 'complete-paypal-payments-for-woocommerce'),
                    'class' => '',
                    'type' => 'checkbox',
                    'label' => __('Enable PayPal Smart Button on the Cart page.', 'complete-paypal-payments-for-woocommerce'),
                    'default' => 'yes'
                ),
                'cart_button_top_position' => array(
                    'title' => __('Enable/Disable', 'complete-paypal-payments-for-woocommerce'),
                    'class' => 'cpp_cart_button_settings',
                    'type' => 'checkbox',
                    'label' => __('Enable PayPal Smart Button on the Top of the Cart page.', 'complete-paypal-payments-for-woocommerce'),
                    'default' => 'no'
                ),
                'cart_disallowed_funding_methods' => array(
                    'title' => __('Hide Funding Method(s)', 'complete-paypal-payments-for-woocommerce'),
                    'type' => 'multiselect',
                    'class' => 'wc-enhanced-select cpp_cart_button_settings',
                    'description' => __('Funding methods selected here will be hidden from buyers during checkout.', 'complete-paypal-payments-for-woocommerce'),
                    'default' => '',
                    'desc_tip' => true,
                    'options' => array(
                        'card' => __('Credit or Debit Card', 'complete-paypal-payments-for-woocommerce'),
                        'credit' => __('PayPal Credit', 'complete-paypal-payments-for-woocommerce'),
                        'paylater' => __('Pay Later', 'complete-paypal-payments-for-woocommerce'),
                        'bancontact' => __('Bancontact', 'complete-paypal-payments-for-woocommerce'),
                        'blik' => __('BLIK', 'complete-paypal-payments-for-woocommerce'),
                        'eps' => __('eps', 'complete-paypal-payments-for-woocommerce'),
                        'giropay' => __('giropay', 'complete-paypal-payments-for-woocommerce'),
                        'ideal' => __('iDEAL', 'complete-paypal-payments-for-woocommerce'),
                        'mercadopago' => __('Mercado Pago', 'complete-paypal-payments-for-woocommerce'),
                        'mybank' => __('MyBank', 'complete-paypal-payments-for-woocommerce'),
                        'p24' => __('Przelewy24', 'complete-paypal-payments-for-woocommerce'),
                        'sepa' => __('SEPA-Lastschrift', 'complete-paypal-payments-for-woocommerce'),
                        'sofort' => __('Sofort', 'complete-paypal-payments-for-woocommerce'),
                        'venmo' => __('Venmo', 'complete-paypal-payments-for-woocommerce')
                    ),
                ),
                'cart_button_layout' => array(
                    'title' => __('Button Layout', 'complete-paypal-payments-for-woocommerce'),
                    'type' => 'select',
                    'class' => 'wc-enhanced-select cpp_cart_button_settings',
                    'description' => __('Select Vertical for stacked buttons, and Horizontal for side-by-side buttons.', 'complete-paypal-payments-for-woocommerce'),
                    'default' => 'vertical',
                    'desc_tip' => true,
                    'options' => array(
                        'vertical' => __('Vertical (Recommended)', 'complete-paypal-payments-for-woocommerce'),
                        'horizontal' => __('Horizontal', 'complete-paypal-payments-for-woocommerce'),
                    ),
                ),
                'cart_style_color' => array(
                    'title' => __('Button Color', 'complete-paypal-payments-for-woocommerce'),
                    'type' => 'select',
                    'class' => 'wc-enhanced-select cpp_cart_button_settings',
                    'description' => __('Set the color you would like to use for the PayPal button.', 'complete-paypal-payments-for-woocommerce'),
                    'desc_tip' => true,
                    'default' => 'gold',
                    'options' => array(
                        'gold' => __('Gold (Recommended)', 'complete-paypal-payments-for-woocommerce'),
                        'blue' => __('Blue', 'complete-paypal-payments-for-woocommerce'),
                        'silver' => __('Silver', 'complete-paypal-payments-for-woocommerce'),
                        'white' => __('White', 'complete-paypal-payments-for-woocommerce'),
                        'black' => __('Black', 'complete-paypal-payments-for-woocommerce')
                    ),
                ),
                'cart_style_shape' => array(
                    'title' => __('Button Shape', 'complete-paypal-payments-for-woocommerce'),
                    'type' => 'select',
                    'class' => 'wc-enhanced-select cpp_cart_button_settings',
                    'description' => __('Set the shape you would like to use for the buttons.', 'complete-paypal-payments-for-woocommerce'),
                    'desc_tip' => true,
                    'default' => 'rect',
                    'options' => array(
                        'rect' => __('Rect (Recommended)', 'complete-paypal-payments-for-woocommerce'),
                        'pill' => __('Pill', 'complete-paypal-payments-for-woocommerce')
                    ),
                ),
                'cart_button_label' => array(
                    'title' => __('Button Label', 'complete-paypal-payments-for-woocommerce'),
                    'type' => 'select',
                    'class' => 'wc-enhanced-select cpp_cart_button_settings',
                    'description' => __('Set the label type you would like to use for the PayPal button.', 'complete-paypal-payments-for-woocommerce'),
                    'default' => 'paypal',
                    'desc_tip' => true,
                    'options' => array(
                        'paypal' => __('PayPal (Recommended)', 'complete-paypal-payments-for-woocommerce'),
                        'checkout' => __('Checkout', 'complete-paypal-payments-for-woocommerce'),
                        'buynow' => __('Buy Now', 'complete-paypal-payments-for-woocommerce'),
                        'pay' => __('Pay', 'complete-paypal-payments-for-woocommerce'),
                    ),
                ),
                'cart_button_tagline' => array(
                    'title' => __('Tagline', 'complete-paypal-payments-for-woocommerce'),
                    'type' => 'checkbox',
                    'class' => 'cpp_cart_button_settings',
                    'default' => 'yes',
                    'label' => __('Enable tagline', 'complete-paypal-payments-for-woocommerce'),
                    'desc_tip' => true,
                    'description' => __(
                            'Add the tagline. This line will only show up, if you select a horizontal layout.', 'complete-paypal-payments-for-woocommerce'
                    ),
                ),
                'checkout_button_settings' => array(
                    'title' => __('Checkout Page', 'complete-paypal-payments-for-woocommerce'),
                    'class' => '',
                    'description' => __('Enable the checkout specific button settings, and the options set will be applied to the PayPal buttons on your checkout page.', 'complete-paypal-payments-for-woocommerce'),
                    'type' => 'title',
                    'class' => 'cpp_separator_heading',
                ),
                'enable_checkout_button' => array(
                    'title' => __('Enable/Disable', 'complete-paypal-payments-for-woocommerce'),
                    'class' => '',
                    'type' => 'checkbox',
                    'label' => __('Enable PayPal Smart Button on the Checkout page.', 'complete-paypal-payments-for-woocommerce'),
                    'default' => 'yes'
                ),
                'enable_checkout_button_top' => array(
                    'title' => __('Enable/Disable', 'complete-paypal-payments-for-woocommerce'),
                    'class' => 'cpp_checkout_button_settings',
                    'type' => 'checkbox',
                    'label' => __('Enable PayPal Smart Button on the Top of the Checkout page.', 'complete-paypal-payments-for-woocommerce'),
                    'default' => 'no'
                ),
                'checkout_disallowed_funding_methods' => array(
                    'title' => __('Hide Funding Method(s)', 'complete-paypal-payments-for-woocommerce'),
                    'type' => 'multiselect',
                    'class' => 'wc-enhanced-select cpp_checkout_button_settings',
                    'description' => __('Funding methods selected here will be hidden from buyers during checkout.', 'complete-paypal-payments-for-woocommerce'),
                    'default' => '',
                    'desc_tip' => true,
                    'options' => array(
                        'card' => __('Credit or Debit Card', 'complete-paypal-payments-for-woocommerce'),
                        'credit' => __('PayPal Credit', 'complete-paypal-payments-for-woocommerce'),
                        'paylater' => __('Pay Later', 'complete-paypal-payments-for-woocommerce'),
                        'bancontact' => __('Bancontact', 'complete-paypal-payments-for-woocommerce'),
                        'blik' => __('BLIK', 'complete-paypal-payments-for-woocommerce'),
                        'eps' => __('eps', 'complete-paypal-payments-for-woocommerce'),
                        'giropay' => __('giropay', 'complete-paypal-payments-for-woocommerce'),
                        'ideal' => __('iDEAL', 'complete-paypal-payments-for-woocommerce'),
                        'mercadopago' => __('Mercado Pago', 'complete-paypal-payments-for-woocommerce'),
                        'mybank' => __('MyBank', 'complete-paypal-payments-for-woocommerce'),
                        'p24' => __('Przelewy24', 'complete-paypal-payments-for-woocommerce'),
                        'sepa' => __('SEPA-Lastschrift', 'complete-paypal-payments-for-woocommerce'),
                        'sofort' => __('Sofort', 'complete-paypal-payments-for-woocommerce'),
                        'venmo' => __('Venmo', 'complete-paypal-payments-for-woocommerce')
                    ),
                ),
                'checkout_button_layout' => array(
                    'title' => __('Button Layout', 'complete-paypal-payments-for-woocommerce'),
                    'type' => 'select',
                    'class' => 'wc-enhanced-select cpp_checkout_button_settings',
                    'description' => __('Select Vertical for stacked buttons, and Horizontal for side-by-side buttons.', 'complete-paypal-payments-for-woocommerce'),
                    'default' => 'vertical',
                    'desc_tip' => true,
                    'options' => array(
                        'vertical' => __('Vertical (Recommended)', 'complete-paypal-payments-for-woocommerce'),
                        'horizontal' => __('Horizontal', 'complete-paypal-payments-for-woocommerce'),
                    ),
                ),
                'checkout_style_color' => array(
                    'title' => __('Button Color', 'complete-paypal-payments-for-woocommerce'),
                    'type' => 'select',
                    'class' => 'wc-enhanced-select cpp_checkout_button_settings',
                    'description' => __('Set the color you would like to use for the PayPal button.', 'complete-paypal-payments-for-woocommerce'),
                    'desc_tip' => true,
                    'default' => 'gold',
                    'options' => array(
                        'gold' => __('Gold (Recommended)', 'complete-paypal-payments-for-woocommerce'),
                        'blue' => __('Blue', 'complete-paypal-payments-for-woocommerce'),
                        'silver' => __('Silver', 'complete-paypal-payments-for-woocommerce'),
                        'white' => __('White', 'complete-paypal-payments-for-woocommerce'),
                        'black' => __('Black', 'complete-paypal-payments-for-woocommerce')
                    ),
                ),
                'checkout_style_shape' => array(
                    'title' => __('Button Shape', 'complete-paypal-payments-for-woocommerce'),
                    'type' => 'select',
                    'class' => 'wc-enhanced-select cpp_checkout_button_settings',
                    'description' => __('Set the shape you would like to use for the buttons.', 'complete-paypal-payments-for-woocommerce'),
                    'desc_tip' => true,
                    'default' => 'rect',
                    'options' => array(
                        'rect' => __('Rect (Recommended)', 'complete-paypal-payments-for-woocommerce'),
                        'pill' => __('Pill', 'complete-paypal-payments-for-woocommerce')
                    ),
                ),
                'checkout_button_label' => array(
                    'title' => __('Button Label', 'complete-paypal-payments-for-woocommerce'),
                    'type' => 'select',
                    'class' => 'wc-enhanced-select cpp_checkout_button_settings',
                    'description' => __('Set the label type you would like to use for the PayPal button.', 'complete-paypal-payments-for-woocommerce'),
                    'default' => 'paypal',
                    'desc_tip' => true,
                    'options' => array(
                        'paypal' => __('PayPal', 'complete-paypal-payments-for-woocommerce'),
                        'checkout' => __('Checkout', 'complete-paypal-payments-for-woocommerce'),
                        'buynow' => __('Buy Now', 'complete-paypal-payments-for-woocommerce'),
                        'pay' => __('Pay', 'complete-paypal-payments-for-woocommerce'),
                    ),
                ),
                'checkout_button_tagline' => array(
                    'title' => __('Tagline', 'complete-paypal-payments-for-woocommerce'),
                    'type' => 'checkbox',
                    'class' => 'cpp_checkout_button_settings',
                    'default' => 'yes',
                    'label' => __('Enable tagline', 'complete-paypal-payments-for-woocommerce'),
                    'desc_tip' => true,
                    'description' => __(
                            'Add the tagline. This line will only show up, if you select a horizontal layout.', 'complete-paypal-payments-for-woocommerce'
                    ),
                ),
                'mini_cart_button_settings' => array(
                    'title' => __('Mini Cart Page', 'complete-paypal-payments-for-woocommerce'),
                    'class' => '',
                    'description' => __('Enable the Mini Cart specific button settings, and the options set will be applied to the PayPal buttons on your Mini Cart page.', 'complete-paypal-payments-for-woocommerce'),
                    'type' => 'title',
                    'class' => 'cpp_separator_heading',
                ),
                'enable_mini_cart_button' => array(
                    'title' => __('Enable/Disable', 'complete-paypal-payments-for-woocommerce'),
                    'class' => '',
                    'type' => 'checkbox',
                    'label' => __('Enable PayPal Smart Button on the Mini Cart page.', 'complete-paypal-payments-for-woocommerce'),
                    'default' => 'yes'
                ),
                'mini_cart_disallowed_funding_methods' => array(
                    'title' => __('Hide Funding Method(s)', 'complete-paypal-payments-for-woocommerce'),
                    'type' => 'multiselect',
                    'class' => 'wc-enhanced-select cpp_mini_cart_button_settings',
                    'description' => __('Funding methods selected here will be hidden from buyers during checkout.', 'complete-paypal-payments-for-woocommerce'),
                    'default' => '',
                    'desc_tip' => true,
                    'options' => array(
                        'card' => __('Credit or Debit Card', 'complete-paypal-payments-for-woocommerce'),
                        'credit' => __('PayPal Credit', 'complete-paypal-payments-for-woocommerce'),
                        'paylater' => __('Pay Later', 'complete-paypal-payments-for-woocommerce'),
                        'bancontact' => __('Bancontact', 'complete-paypal-payments-for-woocommerce'),
                        'blik' => __('BLIK', 'complete-paypal-payments-for-woocommerce'),
                        'eps' => __('eps', 'complete-paypal-payments-for-woocommerce'),
                        'giropay' => __('giropay', 'complete-paypal-payments-for-woocommerce'),
                        'ideal' => __('iDEAL', 'complete-paypal-payments-for-woocommerce'),
                        'mercadopago' => __('Mercado Pago', 'complete-paypal-payments-for-woocommerce'),
                        'mybank' => __('MyBank', 'complete-paypal-payments-for-woocommerce'),
                        'p24' => __('Przelewy24', 'complete-paypal-payments-for-woocommerce'),
                        'sepa' => __('SEPA-Lastschrift', 'complete-paypal-payments-for-woocommerce'),
                        'sofort' => __('Sofort', 'complete-paypal-payments-for-woocommerce'),
                        'venmo' => __('Venmo', 'complete-paypal-payments-for-woocommerce')
                    ),
                ),
                'mini_cart_button_layout' => array(
                    'title' => __('Button Layout', 'complete-paypal-payments-for-woocommerce'),
                    'type' => 'select',
                    'class' => 'wc-enhanced-select cpp_mini_cart_button_settings',
                    'description' => __('Select Vertical for stacked buttons, and Horizontal for side-by-side buttons.', 'complete-paypal-payments-for-woocommerce'),
                    'default' => 'vertical',
                    'desc_tip' => true,
                    'options' => array(
                        'vertical' => __('Vertical (Recommended)', 'complete-paypal-payments-for-woocommerce'),
                        'horizontal' => __('Horizontal', 'complete-paypal-payments-for-woocommerce'),
                    ),
                ),
                'mini_cart_style_color' => array(
                    'title' => __('Button Color', 'complete-paypal-payments-for-woocommerce'),
                    'type' => 'select',
                    'class' => 'wc-enhanced-select cpp_mini_cart_button_settings',
                    'description' => __('Set the color you would like to use for the PayPal button.', 'complete-paypal-payments-for-woocommerce'),
                    'desc_tip' => true,
                    'default' => 'gold',
                    'options' => array(
                        'gold' => __('Gold (Recommended)', 'complete-paypal-payments-for-woocommerce'),
                        'blue' => __('Blue', 'complete-paypal-payments-for-woocommerce'),
                        'silver' => __('Silver', 'complete-paypal-payments-for-woocommerce'),
                        'white' => __('White', 'complete-paypal-payments-for-woocommerce'),
                        'black' => __('Black', 'complete-paypal-payments-for-woocommerce')
                    ),
                ),
                'mini_cart_style_shape' => array(
                    'title' => __('Button Shape', 'complete-paypal-payments-for-woocommerce'),
                    'type' => 'select',
                    'class' => 'wc-enhanced-select cpp_mini_cart_button_settings',
                    'description' => __('Set the shape you would like to use for the buttons.', 'complete-paypal-payments-for-woocommerce'),
                    'desc_tip' => true,
                    'default' => 'rect',
                    'options' => array(
                        'rect' => __('Rect (Recommended)', 'complete-paypal-payments-for-woocommerce'),
                        'pill' => __('Pill', 'complete-paypal-payments-for-woocommerce')
                    ),
                ),
                'mini_cart_button_label' => array(
                    'title' => __('Button Label', 'complete-paypal-payments-for-woocommerce'),
                    'type' => 'select',
                    'class' => 'wc-enhanced-select cpp_mini_cart_button_settings',
                    'description' => __('Set the label type you would like to use for the PayPal button.', 'complete-paypal-payments-for-woocommerce'),
                    'default' => 'mini_cart',
                    'desc_tip' => true,
                    'options' => array(
                        'paypal' => __('PayPal (Recommended)', 'complete-paypal-payments-for-woocommerce'),
                        'checkout' => __('Checkout', 'complete-paypal-payments-for-woocommerce'),
                        'buynow' => __('Buy Now', 'complete-paypal-payments-for-woocommerce'),
                        'pay' => __('Pay', 'complete-paypal-payments-for-woocommerce'),
                    ),
                ),
                'mini_cart_button_tagline' => array(
                    'title' => __('Tagline', 'complete-paypal-payments-for-woocommerce'),
                    'type' => 'checkbox',
                    'class' => 'cpp_mini_cart_button_settings',
                    'default' => 'yes',
                    'label' => __('Enable tagline', 'complete-paypal-payments-for-woocommerce'),
                    'desc_tip' => true,
                    'description' => __(
                            'Add the tagline. This line will only show up, if you select a horizontal layout.', 'complete-paypal-payments-for-woocommerce'
                    ),
                ),
            );

            $order_review_page_settings = array(
                'order_review_page' => array(
                    'title' => __('Order Review Page options', 'complete-paypal-payments-for-woocommerce'),
                    'type' => 'title',
                    'description' => '',
                    'class' => 'cpp_separator_heading',
                ),
                'order_review_page_title' => array(
                    'title' => __('Page Title', 'complete-paypal-payments-for-woocommerce'),
                    'type' => 'text',
                    'description' => __('Set the Page Title value you would like used on the PayPal Checkout order review page.', 'complete-paypal-payments-for-woocommerce'),
                    'default' => __('Confirm your PayPal order', 'complete-paypal-payments-for-woocommerce'),
                    'desc_tip' => true,
                ),
                'order_review_page_description' => array(
                    'title' => __('Description', 'complete-paypal-payments-for-woocommerce'),
                    'type' => 'text',
                    'desc_tip' => true,
                    'description' => __('Set the Description you would like used on the PayPal Checkout order review page.', 'complete-paypal-payments-for-woocommerce'),
                    'default' => __("<strong>You're almost done!</strong><br>Review your information before you place your order.", 'complete-paypal-payments-for-woocommerce'),
                ),
                'order_review_page_button_text' => array(
                    'title' => __('Button Text', 'complete-paypal-payments-for-woocommerce'),
                    'type' => 'text',
                    'description' => __('Set the Button Text you would like used on the PayPal Checkout order review page.', 'complete-paypal-payments-for-woocommerce'),
                    'default' => __('Confirm your PayPal order', 'complete-paypal-payments-for-woocommerce'),
                    'desc_tip' => true,
                )
            );

            $pay_later_messaging_settings = array(
                'pay_later_messaging_settings' => array(
                    'title' => __('Pay Later Messaging Settings', 'complete-paypal-payments-for-woocommerce'),
                    'class' => '',
                    'description' => '',
                    'type' => 'title',
                    'class' => 'cpp_separator_heading',
                ),
                'enabled_pay_later_messaging' => array(
                    'title' => __('Enable/Disable', 'complete-paypal-payments-for-woocommerce'),
                    'label' => __('Enable Pay Later Messaging', 'complete-paypal-payments-for-woocommerce'),
                    'type' => 'checkbox',
                    'description' => 'PayPal offers short-term, interest-free payments, longer-term, monthly installments, and other special financing options that buyers can use to buy now and pay later, while merchants get paid up-front. Pay Later is included with the standard PayPal Checkout. However, specific Pay Later offers differ by country.',
                    'default' => 'no'
                ),
                'pay_later_messaging_page_type' => array(
                    'title' => __('Page Type', 'complete-paypal-payments-for-woocommerce'),
                    'type' => 'multiselect',
                    'css' => 'width: 100%;',
                    'class' => 'wc-enhanced-select pay_later_messaging_field',
                    'default' => array('home', 'category', 'product', 'cart', 'payment'),
                    'options' => array('home' => __('Home', 'complete-paypal-payments-for-woocommerce'), 'category' => __('Category', 'complete-paypal-payments-for-woocommerce'), 'product' => __('Product', 'complete-paypal-payments-for-woocommerce'), 'cart' => __('Cart', 'complete-paypal-payments-for-woocommerce'), 'payment' => __('Payment', 'complete-paypal-payments-for-woocommerce')),
                    'description' => '<div style="font-size: smaller;">Set the page(s) you want to display messaging on, and then adjust that page\'s display option below.</div>',
                ),
                'pay_later_messaging_home_page_settings' => array(
                    'title' => __('Home Page', 'complete-paypal-payments-for-woocommerce'),
                    'description' => __('', 'complete-paypal-payments-for-woocommerce'),
                    'type' => 'title',
                    'class' => 'pay_later_messaging_field pay_later_messaging_home_field cpp_separator_below_heading',
                ),
                'pay_later_messaging_home_layout_type' => array(
                    'title' => __('Layout Type', 'complete-paypal-payments-for-woocommerce'),
                    'type' => 'select',
                    'class' => 'wc-enhanced-select pay_later_messaging_field pay_later_messaging_home_field',
                    'description' => __('', 'complete-paypal-payments-for-woocommerce'),
                    'default' => 'flex',
                    'desc_tip' => true,
                    'options' => array('text' => __('Text Layout', 'complete-paypal-payments-for-woocommerce'), 'flex' => __('Flex Layout', 'complete-paypal-payments-for-woocommerce'))
                ),
                'pay_later_messaging_home_text_layout_logo_type' => array(
                    'title' => __('Logo Type', 'complete-paypal-payments-for-woocommerce'),
                    'type' => 'select',
                    'class' => 'wc-enhanced-select pay_later_messaging_field pay_later_messaging_home_field pay_later_messaging_home_text_layout_field',
                    'description' => __('', 'complete-paypal-payments-for-woocommerce'),
                    'default' => 'primary',
                    'desc_tip' => true,
                    'options' => array('primary' => __('Primary', 'complete-paypal-payments-for-woocommerce'), 'alternative' => __('Alternative', 'complete-paypal-payments-for-woocommerce'), 'inline' => __('Inline', 'complete-paypal-payments-for-woocommerce'), 'none' => __('None', 'complete-paypal-payments-for-woocommerce'))
                ),
                'pay_later_messaging_home_text_layout_logo_position' => array(
                    'title' => __('Logo Position', 'complete-paypal-payments-for-woocommerce'),
                    'type' => 'select',
                    'class' => 'wc-enhanced-select pay_later_messaging_field pay_later_messaging_home_field pay_later_messaging_home_text_layout_field',
                    'description' => __('', 'complete-paypal-payments-for-woocommerce'),
                    'default' => 'left',
                    'desc_tip' => true,
                    'options' => array('left' => __('Left', 'complete-paypal-payments-for-woocommerce'), 'right' => __('Right', 'complete-paypal-payments-for-woocommerce'), 'top' => __('Top', 'complete-paypal-payments-for-woocommerce'))
                ),
                'pay_later_messaging_home_text_layout_text_size' => array(
                    'title' => __('Text Size', 'complete-paypal-payments-for-woocommerce'),
                    'type' => 'select',
                    'class' => 'wc-enhanced-select pay_later_messaging_field pay_later_messaging_home_field pay_later_messaging_home_text_layout_field',
                    'description' => __('', 'complete-paypal-payments-for-woocommerce'),
                    'default' => '12',
                    'desc_tip' => true,
                    'options' => array('10' => __('10 px', 'complete-paypal-payments-for-woocommerce'), '11' => __('11 px', 'complete-paypal-payments-for-woocommerce'), '12' => __('12 px', 'complete-paypal-payments-for-woocommerce'), '13' => __('13 px', 'complete-paypal-payments-for-woocommerce'), '14' => __('14 px', 'complete-paypal-payments-for-woocommerce'), '15' => __('15 px', 'complete-paypal-payments-for-woocommerce'), '16' => __('16 px', 'complete-paypal-payments-for-woocommerce'))
                ),
                'pay_later_messaging_home_text_layout_text_color' => array(
                    'title' => __('Text Color', 'complete-paypal-payments-for-woocommerce'),
                    'type' => 'select',
                    'class' => 'wc-enhanced-select pay_later_messaging_field pay_later_messaging_home_field pay_later_messaging_home_text_layout_field',
                    'description' => __('', 'complete-paypal-payments-for-woocommerce'),
                    'default' => 'black',
                    'desc_tip' => true,
                    'options' => array('black' => __('Black', 'complete-paypal-payments-for-woocommerce'), 'white' => __('White', 'complete-paypal-payments-for-woocommerce'), 'monochrome' => __('Monochrome', 'complete-paypal-payments-for-woocommerce'), 'grayscale' => __('Grayscale', 'complete-paypal-payments-for-woocommerce'))
                ),
                'pay_later_messaging_home_flex_layout_color' => array(
                    'title' => __('Color', 'complete-paypal-payments-for-woocommerce'),
                    'type' => 'select',
                    'class' => 'wc-enhanced-select pay_later_messaging_field pay_later_messaging_home_field pay_later_messaging_home_flex_layout_field',
                    'description' => __('', 'complete-paypal-payments-for-woocommerce'),
                    'default' => 'blue',
                    'desc_tip' => true,
                    'options' => array('blue' => __('Blue', 'complete-paypal-payments-for-woocommerce'), 'black' => __('Black', 'complete-paypal-payments-for-woocommerce'), 'white' => __('White', 'complete-paypal-payments-for-woocommerce'), 'white-no-border' => __('White (No Border)', 'complete-paypal-payments-for-woocommerce'), 'gray' => __('Gray', 'complete-paypal-payments-for-woocommerce'), 'monochrome' => __('Monochrome', 'complete-paypal-payments-for-woocommerce'), 'grayscale' => __('Grayscale', 'complete-paypal-payments-for-woocommerce'))
                ),
                'pay_later_messaging_home_flex_layout_ratio' => array(
                    'title' => __('Ratio', 'complete-paypal-payments-for-woocommerce'),
                    'type' => 'select',
                    'class' => 'wc-enhanced-select pay_later_messaging_field pay_later_messaging_home_field pay_later_messaging_home_flex_layout_field',
                    'description' => __('', 'complete-paypal-payments-for-woocommerce'),
                    'default' => '8x1',
                    'desc_tip' => true,
                    'options' => array('1x1' => __('Flexes between 120px and 300px wide', 'complete-paypal-payments-for-woocommerce'), '1x4' => __('160px wide', 'complete-paypal-payments-for-woocommerce'), '8x1' => __('Flexes between 250px and 768px wide', 'complete-paypal-payments-for-woocommerce'), '20x1' => __('Flexes between 250px and 1169px wide', 'complete-paypal-payments-for-woocommerce'))
                ),
                'pay_later_messaging_home_shortcode' => array(
                    'title' => __('Enable/Disable', 'complete-paypal-payments-for-woocommerce'),
                    'label' => __('I need a shortcode so that I can place the message in a better spot on Home page.', 'complete-paypal-payments-for-woocommerce'),
                    'type' => 'checkbox',
                    'class' => 'pay_later_messaging_field pay_later_messaging_home_field pay_later_messaging_home_shortcode',
                    'description' => '',
                    'default' => 'no'
                ),
                'pay_later_messaging_home_preview_shortcode' => array(
                    'title' => __('Shortcode', 'complete-paypal-payments-for-woocommerce'),
                    'type' => 'copy_text',
                    'class' => 'pay_later_messaging_field pay_later_messaging_home_field pay_later_messaging_home_preview_shortcode preview_shortcode',
                    'description' => '',
                    'custom_attributes' => array('readonly' => 'readonly'),
                    'button_class' => 'home_copy_text',
                    'default' => '[cpp_bnpl_message placement="home"]'
                ),
                'pay_later_messaging_category_page_settings' => array(
                    'title' => __('Category Page', 'complete-paypal-payments-for-woocommerce'),
                    'class' => '',
                    'description' => __('', 'complete-paypal-payments-for-woocommerce'),
                    'type' => 'title',
                    'class' => 'pay_later_messaging_field pay_later_messaging_category_field cpp_separator_below_heading',
                ),
                'pay_later_messaging_category_layout_type' => array(
                    'title' => __('Layout Type', 'complete-paypal-payments-for-woocommerce'),
                    'type' => 'select',
                    'class' => 'wc-enhanced-select pay_later_messaging_field pay_later_messaging_category_field',
                    'description' => __('', 'complete-paypal-payments-for-woocommerce'),
                    'default' => 'flex',
                    'desc_tip' => true,
                    'options' => array('text' => __('Text Layout', 'complete-paypal-payments-for-woocommerce'), 'flex' => __('Flex Layout', 'complete-paypal-payments-for-woocommerce'))
                ),
                'pay_later_messaging_category_text_layout_logo_type' => array(
                    'title' => __('Logo Type', 'complete-paypal-payments-for-woocommerce'),
                    'type' => 'select',
                    'class' => 'wc-enhanced-select pay_later_messaging_field pay_later_messaging_category_field pay_later_messaging_category_text_layout_field',
                    'description' => __('', 'complete-paypal-payments-for-woocommerce'),
                    'default' => 'primary',
                    'desc_tip' => true,
                    'options' => array('primary' => __('Primary', 'complete-paypal-payments-for-woocommerce'), 'alternative' => __('Alternative', 'complete-paypal-payments-for-woocommerce'), 'inline' => __('Inline', 'complete-paypal-payments-for-woocommerce'), 'none' => __('None', 'complete-paypal-payments-for-woocommerce'))
                ),
                'pay_later_messaging_category_text_layout_logo_position' => array(
                    'title' => __('Logo Position', 'complete-paypal-payments-for-woocommerce'),
                    'type' => 'select',
                    'class' => 'wc-enhanced-select pay_later_messaging_field pay_later_messaging_category_field pay_later_messaging_category_text_layout_field',
                    'description' => __('', 'complete-paypal-payments-for-woocommerce'),
                    'default' => 'left',
                    'desc_tip' => true,
                    'options' => array('left' => __('Left', 'complete-paypal-payments-for-woocommerce'), 'right' => __('Right', 'complete-paypal-payments-for-woocommerce'), 'top' => __('Top', 'complete-paypal-payments-for-woocommerce'))
                ),
                'pay_later_messaging_category_text_layout_text_size' => array(
                    'title' => __('Text Size', 'complete-paypal-payments-for-woocommerce'),
                    'type' => 'select',
                    'class' => 'wc-enhanced-select pay_later_messaging_field pay_later_messaging_category_field pay_later_messaging_category_text_layout_field',
                    'description' => __('', 'complete-paypal-payments-for-woocommerce'),
                    'default' => '12',
                    'desc_tip' => true,
                    'options' => array('10' => __('10 px', 'complete-paypal-payments-for-woocommerce'), '11' => __('11 px', 'complete-paypal-payments-for-woocommerce'), '12' => __('12 px', 'complete-paypal-payments-for-woocommerce'), '13' => __('13 px', 'complete-paypal-payments-for-woocommerce'), '14' => __('14 px', 'complete-paypal-payments-for-woocommerce'), '15' => __('15 px', 'complete-paypal-payments-for-woocommerce'), '16' => __('16 px', 'complete-paypal-payments-for-woocommerce'))
                ),
                'pay_later_messaging_category_text_layout_text_color' => array(
                    'title' => __('Text Color', 'complete-paypal-payments-for-woocommerce'),
                    'type' => 'select',
                    'class' => 'wc-enhanced-select pay_later_messaging_field pay_later_messaging_category_field pay_later_messaging_category_text_layout_field',
                    'description' => __('', 'complete-paypal-payments-for-woocommerce'),
                    'default' => 'black',
                    'desc_tip' => true,
                    'options' => array('black' => __('Black', 'complete-paypal-payments-for-woocommerce'), 'white' => __('White', 'complete-paypal-payments-for-woocommerce'), 'monochrome' => __('Monochrome', 'complete-paypal-payments-for-woocommerce'), 'grayscale' => __('Grayscale', 'complete-paypal-payments-for-woocommerce'))
                ),
                'pay_later_messaging_category_flex_layout_color' => array(
                    'title' => __('Color', 'complete-paypal-payments-for-woocommerce'),
                    'type' => 'select',
                    'class' => 'wc-enhanced-select pay_later_messaging_field pay_later_messaging_category_field pay_later_messaging_category_flex_layout_field',
                    'description' => __('', 'complete-paypal-payments-for-woocommerce'),
                    'default' => 'blue',
                    'desc_tip' => true,
                    'options' => array('blue' => __('Blue', 'complete-paypal-payments-for-woocommerce'), 'black' => __('Black', 'complete-paypal-payments-for-woocommerce'), 'white' => __('White', 'complete-paypal-payments-for-woocommerce'), 'white-no-border' => __('White (No Border)', 'complete-paypal-payments-for-woocommerce'), 'gray' => __('Gray', 'complete-paypal-payments-for-woocommerce'), 'monochrome' => __('Monochrome', 'complete-paypal-payments-for-woocommerce'), 'grayscale' => __('Grayscale', 'complete-paypal-payments-for-woocommerce'))
                ),
                'pay_later_messaging_category_flex_layout_ratio' => array(
                    'title' => __('Ratio', 'complete-paypal-payments-for-woocommerce'),
                    'type' => 'select',
                    'class' => 'wc-enhanced-select pay_later_messaging_field pay_later_messaging_category_field pay_later_messaging_category_flex_layout_field',
                    'description' => __('', 'complete-paypal-payments-for-woocommerce'),
                    'default' => '8x1',
                    'desc_tip' => true,
                    'options' => array('1x1' => __('Flexes between 120px and 300px wide', 'complete-paypal-payments-for-woocommerce'), '1x4' => __('160px wide', 'complete-paypal-payments-for-woocommerce'), '8x1' => __('Flexes between 250px and 768px wide', 'complete-paypal-payments-for-woocommerce'), '20x1' => __('Flexes between 250px and 1169px wide', 'complete-paypal-payments-for-woocommerce'))
                ),
                'pay_later_messaging_category_shortcode' => array(
                    'title' => __('Enable/Disable', 'complete-paypal-payments-for-woocommerce'),
                    'label' => __('I need a shortcode so that I can place the message in a better spot on category page.', 'complete-paypal-payments-for-woocommerce'),
                    'type' => 'checkbox',
                    'class' => 'pay_later_messaging_field pay_later_messaging_category_field pay_later_messaging_category_shortcode',
                    'description' => '',
                    'default' => 'no'
                ),
                'pay_later_messaging_category_preview_shortcode' => array(
                    'title' => __('Shortcode', 'complete-paypal-payments-for-woocommerce'),
                    'type' => 'copy_text',
                    'class' => 'pay_later_messaging_field pay_later_messaging_category_field pay_later_messaging_category_preview_shortcode preview_shortcode',
                    'description' => '',
                    'button_class' => 'category_copy_text',
                    'custom_attributes' => array('readonly' => 'readonly'),
                    'default' => '[cpp_bnpl_message placement="category"]'
                ),
                'pay_later_messaging_product_page_settings' => array(
                    'title' => __('Product Page', 'complete-paypal-payments-for-woocommerce'),
                    'description' => __('', 'complete-paypal-payments-for-woocommerce'),
                    'type' => 'title',
                    'class' => 'pay_later_messaging_field pay_later_messaging_product_field cpp_separator_below_heading',
                ),
                'pay_later_messaging_product_layout_type' => array(
                    'title' => __('Layout Type', 'complete-paypal-payments-for-woocommerce'),
                    'type' => 'select',
                    'class' => 'wc-enhanced-select pay_later_messaging_field pay_later_messaging_product_field',
                    'description' => __('', 'complete-paypal-payments-for-woocommerce'),
                    'default' => 'text',
                    'desc_tip' => true,
                    'options' => array('text' => __('Text Layout', 'complete-paypal-payments-for-woocommerce'), 'flex' => __('Flex Layout', 'complete-paypal-payments-for-woocommerce'))
                ),
                'pay_later_messaging_product_text_layout_logo_type' => array(
                    'title' => __('Logo Type', 'complete-paypal-payments-for-woocommerce'),
                    'type' => 'select',
                    'class' => 'wc-enhanced-select pay_later_messaging_field pay_later_messaging_product_field pay_later_messaging_product_text_layout_field',
                    'description' => __('', 'complete-paypal-payments-for-woocommerce'),
                    'default' => 'primary',
                    'desc_tip' => true,
                    'options' => array('primary' => __('Primary', 'complete-paypal-payments-for-woocommerce'), 'alternative' => __('Alternative', 'complete-paypal-payments-for-woocommerce'), 'inline' => __('Inline', 'complete-paypal-payments-for-woocommerce'), 'none' => __('None', 'complete-paypal-payments-for-woocommerce'))
                ),
                'pay_later_messaging_product_text_layout_logo_position' => array(
                    'title' => __('Logo Position', 'complete-paypal-payments-for-woocommerce'),
                    'type' => 'select',
                    'class' => 'wc-enhanced-select pay_later_messaging_field pay_later_messaging_product_field pay_later_messaging_product_text_layout_field',
                    'description' => __('', 'complete-paypal-payments-for-woocommerce'),
                    'default' => 'left',
                    'desc_tip' => true,
                    'options' => array('left' => __('Left', 'complete-paypal-payments-for-woocommerce'), 'right' => __('Right', 'complete-paypal-payments-for-woocommerce'), 'top' => __('Top', 'complete-paypal-payments-for-woocommerce'))
                ),
                'pay_later_messaging_product_text_layout_text_size' => array(
                    'title' => __('Text Size', 'complete-paypal-payments-for-woocommerce'),
                    'type' => 'select',
                    'class' => 'wc-enhanced-select pay_later_messaging_field pay_later_messaging_product_field pay_later_messaging_product_text_layout_field',
                    'description' => __('', 'complete-paypal-payments-for-woocommerce'),
                    'default' => '12',
                    'desc_tip' => true,
                    'options' => array('10' => __('10 px', 'complete-paypal-payments-for-woocommerce'), '11' => __('11 px', 'complete-paypal-payments-for-woocommerce'), '12' => __('12 px', 'complete-paypal-payments-for-woocommerce'), '13' => __('13 px', 'complete-paypal-payments-for-woocommerce'), '14' => __('14 px', 'complete-paypal-payments-for-woocommerce'), '15' => __('15 px', 'complete-paypal-payments-for-woocommerce'), '16' => __('16 px', 'complete-paypal-payments-for-woocommerce'))
                ),
                'pay_later_messaging_product_text_layout_text_color' => array(
                    'title' => __('Text Color', 'complete-paypal-payments-for-woocommerce'),
                    'type' => 'select',
                    'class' => 'wc-enhanced-select pay_later_messaging_field pay_later_messaging_product_field pay_later_messaging_product_text_layout_field',
                    'description' => __('', 'complete-paypal-payments-for-woocommerce'),
                    'default' => 'black',
                    'desc_tip' => true,
                    'options' => array('black' => __('Black', 'complete-paypal-payments-for-woocommerce'), 'white' => __('White', 'complete-paypal-payments-for-woocommerce'), 'monochrome' => __('Monochrome', 'complete-paypal-payments-for-woocommerce'), 'grayscale' => __('Grayscale', 'complete-paypal-payments-for-woocommerce'))
                ),
                'pay_later_messaging_product_flex_layout_color' => array(
                    'title' => __('Color', 'complete-paypal-payments-for-woocommerce'),
                    'type' => 'select',
                    'class' => 'wc-enhanced-select pay_later_messaging_field pay_later_messaging_product_field pay_later_messaging_product_flex_layout_field',
                    'description' => __('', 'complete-paypal-payments-for-woocommerce'),
                    'default' => 'blue',
                    'desc_tip' => true,
                    'options' => array('blue' => __('Blue', 'complete-paypal-payments-for-woocommerce'), 'black' => __('Black', 'complete-paypal-payments-for-woocommerce'), 'white' => __('White', 'complete-paypal-payments-for-woocommerce'), 'white-no-border' => __('White (No Border)', 'complete-paypal-payments-for-woocommerce'), 'gray' => __('Gray', 'complete-paypal-payments-for-woocommerce'), 'monochrome' => __('Monochrome', 'complete-paypal-payments-for-woocommerce'), 'grayscale' => __('Grayscale', 'complete-paypal-payments-for-woocommerce'))
                ),
                'pay_later_messaging_product_flex_layout_ratio' => array(
                    'title' => __('Ratio', 'complete-paypal-payments-for-woocommerce'),
                    'type' => 'select',
                    'class' => 'wc-enhanced-select pay_later_messaging_field pay_later_messaging_product_field pay_later_messaging_product_flex_layout_field',
                    'description' => __('', 'complete-paypal-payments-for-woocommerce'),
                    'default' => '8x1',
                    'desc_tip' => true,
                    'options' => array('1x1' => __('Flexes between 120px and 300px wide', 'complete-paypal-payments-for-woocommerce'), '1x4' => __('160px wide', 'complete-paypal-payments-for-woocommerce'), '8x1' => __('Flexes between 250px and 768px wide', 'complete-paypal-payments-for-woocommerce'), '20x1' => __('Flexes between 250px and 1169px wide', 'complete-paypal-payments-for-woocommerce'))
                ),
                'pay_later_messaging_product_shortcode' => array(
                    'title' => __('Enable/Disable', 'complete-paypal-payments-for-woocommerce'),
                    'label' => __('I need a shortcode so that I can place the message in a better spot on product page.', 'complete-paypal-payments-for-woocommerce'),
                    'type' => 'checkbox',
                    'class' => 'pay_later_messaging_field pay_later_messaging_product_field pay_later_messaging_product_shortcode',
                    'description' => '',
                    'default' => 'no'
                ),
                'pay_later_messaging_product_preview_shortcode' => array(
                    'title' => __('Shortcode', 'complete-paypal-payments-for-woocommerce'),
                    'type' => 'copy_text',
                    'class' => 'pay_later_messaging_field pay_later_messaging_product_field pay_later_messaging_product_preview_shortcode preview_shortcode',
                    'description' => '',
                    'button_class' => 'product_copy_text',
                    'custom_attributes' => array('readonly' => 'readonly'),
                    'default' => '[cpp_bnpl_message placement="product"]'
                ),
                'pay_later_messaging_cart_page_settings' => array(
                    'title' => __('Cart Page', 'complete-paypal-payments-for-woocommerce'),
                    'description' => __('', 'complete-paypal-payments-for-woocommerce'),
                    'type' => 'title',
                    'class' => 'pay_later_messaging_field pay_later_messaging_cart_field cpp_separator_below_heading',
                ),
                'pay_later_messaging_cart_layout_type' => array(
                    'title' => __('Layout Type', 'complete-paypal-payments-for-woocommerce'),
                    'type' => 'select',
                    'class' => 'wc-enhanced-select pay_later_messaging_field pay_later_messaging_cart_field',
                    'description' => __('', 'complete-paypal-payments-for-woocommerce'),
                    'default' => 'text',
                    'desc_tip' => true,
                    'options' => array('text' => __('Text Layout', 'complete-paypal-payments-for-woocommerce'), 'flex' => __('Flex Layout', 'complete-paypal-payments-for-woocommerce'))
                ),
                'pay_later_messaging_cart_text_layout_logo_type' => array(
                    'title' => __('Logo Type', 'complete-paypal-payments-for-woocommerce'),
                    'type' => 'select',
                    'class' => 'wc-enhanced-select pay_later_messaging_field pay_later_messaging_cart_field pay_later_messaging_cart_text_layout_field',
                    'description' => __('', 'complete-paypal-payments-for-woocommerce'),
                    'default' => 'primary',
                    'desc_tip' => true,
                    'options' => array('primary' => __('Primary', 'complete-paypal-payments-for-woocommerce'), 'alternative' => __('Alternative', 'complete-paypal-payments-for-woocommerce'), 'inline' => __('Inline', 'complete-paypal-payments-for-woocommerce'), 'none' => __('None', 'complete-paypal-payments-for-woocommerce'))
                ),
                'pay_later_messaging_cart_text_layout_logo_position' => array(
                    'title' => __('Logo Position', 'complete-paypal-payments-for-woocommerce'),
                    'type' => 'select',
                    'class' => 'wc-enhanced-select pay_later_messaging_field pay_later_messaging_cart_field pay_later_messaging_cart_text_layout_field',
                    'description' => __('', 'complete-paypal-payments-for-woocommerce'),
                    'default' => 'left',
                    'desc_tip' => true,
                    'options' => array('left' => __('Left', 'complete-paypal-payments-for-woocommerce'), 'right' => __('Right', 'complete-paypal-payments-for-woocommerce'), 'top' => __('Top', 'complete-paypal-payments-for-woocommerce'))
                ),
                'pay_later_messaging_cart_text_layout_text_size' => array(
                    'title' => __('Text Size', 'complete-paypal-payments-for-woocommerce'),
                    'type' => 'select',
                    'class' => 'wc-enhanced-select pay_later_messaging_field pay_later_messaging_cart_field pay_later_messaging_cart_text_layout_field',
                    'description' => __('', 'complete-paypal-payments-for-woocommerce'),
                    'default' => '12',
                    'desc_tip' => true,
                    'options' => array('10' => __('10 px', 'complete-paypal-payments-for-woocommerce'), '11' => __('11 px', 'complete-paypal-payments-for-woocommerce'), '12' => __('12 px', 'complete-paypal-payments-for-woocommerce'), '13' => __('13 px', 'complete-paypal-payments-for-woocommerce'), '14' => __('14 px', 'complete-paypal-payments-for-woocommerce'), '15' => __('15 px', 'complete-paypal-payments-for-woocommerce'), '16' => __('16 px', 'complete-paypal-payments-for-woocommerce'))
                ),
                'pay_later_messaging_cart_text_layout_text_color' => array(
                    'title' => __('Text Color', 'complete-paypal-payments-for-woocommerce'),
                    'type' => 'select',
                    'class' => 'wc-enhanced-select pay_later_messaging_field pay_later_messaging_cart_field pay_later_messaging_cart_text_layout_field',
                    'description' => __('', 'complete-paypal-payments-for-woocommerce'),
                    'default' => 'black',
                    'desc_tip' => true,
                    'options' => array('black' => __('Black', 'complete-paypal-payments-for-woocommerce'), 'white' => __('White', 'complete-paypal-payments-for-woocommerce'), 'monochrome' => __('Monochrome', 'complete-paypal-payments-for-woocommerce'), 'grayscale' => __('Grayscale', 'complete-paypal-payments-for-woocommerce'))
                ),
                'pay_later_messaging_cart_flex_layout_color' => array(
                    'title' => __('Color', 'complete-paypal-payments-for-woocommerce'),
                    'type' => 'select',
                    'class' => 'wc-enhanced-select pay_later_messaging_field pay_later_messaging_cart_field pay_later_messaging_cart_flex_layout_field',
                    'description' => __('', 'complete-paypal-payments-for-woocommerce'),
                    'default' => 'blue',
                    'desc_tip' => true,
                    'options' => array('blue' => __('Blue', 'complete-paypal-payments-for-woocommerce'), 'black' => __('Black', 'complete-paypal-payments-for-woocommerce'), 'white' => __('White', 'complete-paypal-payments-for-woocommerce'), 'white-no-border' => __('White (No Border)', 'complete-paypal-payments-for-woocommerce'), 'gray' => __('Gray', 'complete-paypal-payments-for-woocommerce'), 'monochrome' => __('Monochrome', 'complete-paypal-payments-for-woocommerce'), 'grayscale' => __('Grayscale', 'complete-paypal-payments-for-woocommerce'))
                ),
                'pay_later_messaging_cart_flex_layout_ratio' => array(
                    'title' => __('Ratio', 'complete-paypal-payments-for-woocommerce'),
                    'type' => 'select',
                    'class' => 'wc-enhanced-select pay_later_messaging_field pay_later_messaging_cart_field pay_later_messaging_cart_flex_layout_field',
                    'description' => __('', 'complete-paypal-payments-for-woocommerce'),
                    'default' => '8x1',
                    'desc_tip' => true,
                    'options' => array('1x1' => __('Flexes between 120px and 300px wide', 'complete-paypal-payments-for-woocommerce'), '1x4' => __('160px wide', 'complete-paypal-payments-for-woocommerce'), '8x1' => __('Flexes between 250px and 768px wide', 'complete-paypal-payments-for-woocommerce'), '20x1' => __('Flexes between 250px and 1169px wide', 'complete-paypal-payments-for-woocommerce'))
                ),
                'pay_later_messaging_cart_shortcode' => array(
                    'title' => __('Enable/Disable', 'complete-paypal-payments-for-woocommerce'),
                    'label' => __('I need a shortcode so that I can place the message in a better spot on cart page.', 'complete-paypal-payments-for-woocommerce'),
                    'type' => 'checkbox',
                    'class' => 'pay_later_messaging_field pay_later_messaging_cart_field pay_later_messaging_cart_shortcode',
                    'description' => '',
                    'default' => 'no'
                ),
                'pay_later_messaging_cart_preview_shortcode' => array(
                    'title' => __('Shortcode', 'complete-paypal-payments-for-woocommerce'),
                    'type' => 'copy_text',
                    'class' => 'pay_later_messaging_field pay_later_messaging_cart_field pay_later_messaging_cart_preview_shortcode preview_shortcode',
                    'description' => '',
                    'button_class' => 'cart_copy_text',
                    'custom_attributes' => array('readonly' => 'readonly'),
                    'default' => '[cpp_bnpl_message placement="cart"]'
                ),
                'pay_later_messaging_payment_page_settings' => array(
                    'title' => __('Payment Page', 'complete-paypal-payments-for-woocommerce'),
                    'description' => __('', 'complete-paypal-payments-for-woocommerce'),
                    'type' => 'title',
                    'class' => 'pay_later_messaging_field pay_later_messaging_payment_field cpp_separator_below_heading',
                ),
                'pay_later_messaging_payment_layout_type' => array(
                    'title' => __('Layout Type', 'complete-paypal-payments-for-woocommerce'),
                    'type' => 'select',
                    'class' => 'wc-enhanced-select pay_later_messaging_field pay_later_messaging_payment_field',
                    'description' => __('', 'complete-paypal-payments-for-woocommerce'),
                    'default' => 'text',
                    'desc_tip' => true,
                    'options' => array('text' => __('Text Layout', 'complete-paypal-payments-for-woocommerce'), 'flex' => __('Flex Layout', 'complete-paypal-payments-for-woocommerce'))
                ),
                'pay_later_messaging_payment_text_layout_logo_type' => array(
                    'title' => __('Logo Type', 'complete-paypal-payments-for-woocommerce'),
                    'type' => 'select',
                    'class' => 'wc-enhanced-select pay_later_messaging_field pay_later_messaging_payment_field pay_later_messaging_payment_text_layout_field',
                    'description' => __('', 'complete-paypal-payments-for-woocommerce'),
                    'default' => 'primary',
                    'desc_tip' => true,
                    'options' => array('primary' => __('Primary', 'complete-paypal-payments-for-woocommerce'), 'alternative' => __('Alternative', 'complete-paypal-payments-for-woocommerce'), 'inline' => __('Inline', 'complete-paypal-payments-for-woocommerce'), 'none' => __('None', 'complete-paypal-payments-for-woocommerce'))
                ),
                'pay_later_messaging_payment_text_layout_logo_position' => array(
                    'title' => __('Logo Position', 'complete-paypal-payments-for-woocommerce'),
                    'type' => 'select',
                    'class' => 'wc-enhanced-select pay_later_messaging_field pay_later_messaging_payment_field pay_later_messaging_payment_text_layout_field',
                    'description' => __('', 'complete-paypal-payments-for-woocommerce'),
                    'default' => 'left',
                    'desc_tip' => true,
                    'options' => array('left' => __('Left', 'complete-paypal-payments-for-woocommerce'), 'right' => __('Right', 'complete-paypal-payments-for-woocommerce'), 'top' => __('Top', 'complete-paypal-payments-for-woocommerce'))
                ),
                'pay_later_messaging_payment_text_layout_text_size' => array(
                    'title' => __('Text Size', 'complete-paypal-payments-for-woocommerce'),
                    'type' => 'select',
                    'class' => 'wc-enhanced-select pay_later_messaging_field pay_later_messaging_payment_field pay_later_messaging_payment_text_layout_field',
                    'description' => __('', 'complete-paypal-payments-for-woocommerce'),
                    'default' => '12',
                    'desc_tip' => true,
                    'options' => array('10' => __('10 px', 'complete-paypal-payments-for-woocommerce'), '11' => __('11 px', 'complete-paypal-payments-for-woocommerce'), '12' => __('12 px', 'complete-paypal-payments-for-woocommerce'), '13' => __('13 px', 'complete-paypal-payments-for-woocommerce'), '14' => __('14 px', 'complete-paypal-payments-for-woocommerce'), '15' => __('15 px', 'complete-paypal-payments-for-woocommerce'), '16' => __('16 px', 'complete-paypal-payments-for-woocommerce'))
                ),
                'pay_later_messaging_payment_text_layout_text_color' => array(
                    'title' => __('Text Color', 'complete-paypal-payments-for-woocommerce'),
                    'type' => 'select',
                    'class' => 'wc-enhanced-select pay_later_messaging_field pay_later_messaging_payment_field pay_later_messaging_payment_text_layout_field',
                    'description' => __('', 'complete-paypal-payments-for-woocommerce'),
                    'default' => 'black',
                    'desc_tip' => true,
                    'options' => array('black' => __('Black', 'complete-paypal-payments-for-woocommerce'), 'white' => __('White', 'complete-paypal-payments-for-woocommerce'), 'monochrome' => __('Monochrome', 'complete-paypal-payments-for-woocommerce'), 'grayscale' => __('Grayscale', 'complete-paypal-payments-for-woocommerce'))
                ),
                'pay_later_messaging_payment_flex_layout_color' => array(
                    'title' => __('Color', 'complete-paypal-payments-for-woocommerce'),
                    'type' => 'select',
                    'class' => 'wc-enhanced-select pay_later_messaging_field pay_later_messaging_payment_field pay_later_messaging_payment_flex_layout_field',
                    'description' => __('', 'complete-paypal-payments-for-woocommerce'),
                    'default' => 'blue',
                    'desc_tip' => true,
                    'options' => array('blue' => __('Blue', 'complete-paypal-payments-for-woocommerce'), 'black' => __('Black', 'complete-paypal-payments-for-woocommerce'), 'white' => __('White', 'complete-paypal-payments-for-woocommerce'), 'white-no-border' => __('White (No Border)', 'complete-paypal-payments-for-woocommerce'), 'gray' => __('Gray', 'complete-paypal-payments-for-woocommerce'), 'monochrome' => __('Monochrome', 'complete-paypal-payments-for-woocommerce'), 'grayscale' => __('Grayscale', 'complete-paypal-payments-for-woocommerce'))
                ),
                'pay_later_messaging_payment_flex_layout_ratio' => array(
                    'title' => __('Ratio', 'complete-paypal-payments-for-woocommerce'),
                    'type' => 'select',
                    'class' => 'wc-enhanced-select pay_later_messaging_field pay_later_messaging_payment_field pay_later_messaging_payment_flex_layout_field',
                    'description' => __('', 'complete-paypal-payments-for-woocommerce'),
                    'default' => '8x1',
                    'desc_tip' => true,
                    'options' => array('1x1' => __('Flexes between 120px and 300px wide', 'complete-paypal-payments-for-woocommerce'), '1x4' => __('160px wide', 'complete-paypal-payments-for-woocommerce'), '8x1' => __('Flexes between 250px and 768px wide', 'complete-paypal-payments-for-woocommerce'), '20x1' => __('Flexes between 250px and 1169px wide', 'complete-paypal-payments-for-woocommerce'))
                ),
                'pay_later_messaging_payment_shortcode' => array(
                    'title' => __('Enable/Disable', 'complete-paypal-payments-for-woocommerce'),
                    'label' => __('I need a shortcode so that I can place the message in a better spot on payment page.', 'complete-paypal-payments-for-woocommerce'),
                    'type' => 'checkbox',
                    'class' => 'pay_later_messaging_field pay_later_messaging_payment_field pay_later_messaging_payment_shortcode',
                    'description' => '',
                    'default' => 'no'
                ),
                'pay_later_messaging_payment_preview_shortcode' => array(
                    'title' => __('Shortcode', 'complete-paypal-payments-for-woocommerce'),
                    'type' => 'copy_text',
                    'class' => 'pay_later_messaging_field pay_later_messaging_payment_field pay_later_messaging_payment_preview_shortcode preview_shortcode',
                    'description' => '',
                    'button_class' => 'payment_copy_text',
                    'custom_attributes' => array('readonly' => 'readonly'),
                    'default' => '[cpp_bnpl_message placement="payment"]'
            ));

            $advanced_settings = array(
                'advanced' => array(
                    'title' => __('Advanced Settings', 'complete-paypal-payments-for-woocommerce'),
                    'type' => 'title',
                    'description' => '',
                    'class' => 'cpp_separator_heading',
                ),
                'brand_name' => array(
                    'title' => __('Brand Name', 'complete-paypal-payments-for-woocommerce'),
                    'type' => 'text',
                    'description' => __('This controls what users see as the brand / company name on PayPal review pages.', 'complete-paypal-payments-for-woocommerce'),
                    'default' => __(get_bloginfo('name'), 'complete-paypal-payments-for-woocommerce'),
                    'desc_tip' => true,
                ),
                'landing_page' => array(
                    'title' => __('Landing Page', 'complete-paypal-payments-for-woocommerce'),
                    'type' => 'select',
                    'class' => 'wc-enhanced-select',
                    'description' => __('The type of landing page to show on the PayPal site for customer checkout. PayPal Account Optional must be checked for this option to be used.', 'complete-paypal-payments-for-woocommerce'),
                    'options' => array('LOGIN' => __('Login', 'complete-paypal-payments-for-woocommerce'),
                        'BILLING' => __('Billing', 'complete-paypal-payments-for-woocommerce'),
                        'NO_PREFERENCE' => __('No Preference', 'complete-paypal-payments-for-woocommerce')),
                    'default' => 'NO_PREFERENCE',
                    'desc_tip' => true,
                ),
                'payee_preferred' => array(
                    'title' => __('Instant Payments ', 'complete-paypal-payments-for-woocommerce'),
                    'type' => 'checkbox',
                    'default' => 'no',
                    'desc_tip' => true,
                    'description' => __(
                            'If you enable this setting, PayPal will be instructed not to allow the buyer to use funding sources that take additional time to complete (for example, eChecks). Instead, the buyer will be required to use an instant funding source, such as an instant transfer, a credit/debit card, or PayPal Credit.', 'complete-paypal-payments-for-woocommerce'
                    ),
                    'label' => __('Require Instant Payment', 'complete-paypal-payments-for-woocommerce'),
                ),
                'send_items' => array(
                    'title' => __('Send Item Details', 'complete-paypal-payments-for-woocommerce'),
                    'label' => __('Send line item details to PayPal', 'complete-paypal-payments-for-woocommerce'),
                    'type' => 'checkbox',
                    'description' => __('Include all line item details in the payment request to PayPal so that they can be seen from the PayPal transaction details page.', 'complete-paypal-payments-for-woocommerce'),
                    'default' => 'yes'
                ),
                'enable_advanced_card_payments' => array(
                    'title' => __('Enable/Disable', 'complete-paypal-payments-for-woocommerce'),
                    'type' => 'checkbox',
                    'label' => __('Enable advanced credit and debit card payments', 'complete-paypal-payments-for-woocommerce'),
                    'default' => 'no',
                    'description' => __('Currently PayPal support Unbranded payments in US, AU, UK, FR, IT, CA, DE and ES only. <br> <br>Advanced credit and debit cards requires that your business account be evaluated and approved by PayPal. <br><a target="_blank" href="https://www.sandbox.paypal.com/bizsignup/entry/product/ppcp">Enable for Sandbox Account</a> <span> | </span> <a target="_blank" href="https://www.paypal.com/bizsignup/entry/product/ppcp">Enable for Live Account</a><br>', 'complete-paypal-payments-for-woocommerce'),
                ),
                '3d_secure_contingency' => array(
                    'title' => __('Contingency for 3D Secure', 'complete-paypal-payments-for-woocommerce'),
                    'type' => 'select',
                    'class' => 'wc-enhanced-select',
                    'options' => array(
                        'SCA_WHEN_REQUIRED' => __('3D Secure when required', 'complete-paypal-payments-for-woocommerce'),
                        'SCA_ALWAYS' => __('Always trigger 3D Secure', 'complete-paypal-payments-for-woocommerce'),
                    ),
                    'default' => 'SCA_WHEN_REQUIRED',
                    'desc_tip' => true,
                    'description' => __('3D Secure benefits cardholders and merchants by providing an additional layer of verification using Verified by Visa, MasterCard SecureCode and American Express SafeKey.', 'complete-paypal-payments-for-woocommerce'),
                ),
                'paymentaction' => array(
                    'title' => __('Payment action', 'complete-paypal-payments-for-woocommerce'),
                    'type' => 'select',
                    'class' => 'wc-enhanced-select',
                    'description' => __('Choose whether you wish to capture funds immediately or authorize payment only.', 'complete-paypal-payments-for-woocommerce'),
                    'default' => 'capture',
                    'desc_tip' => true,
                    'options' => array(
                        'capture' => __('Capture', 'complete-paypal-payments-for-woocommerce'),
                        'authorize' => __('Authorize', 'complete-paypal-payments-for-woocommerce'),
                    ),
                ),
                'invoice_prefix' => array(
                    'title' => __('Invoice prefix', 'complete-paypal-payments-for-woocommerce'),
                    'type' => 'text',
                    'description' => __('Please enter a prefix for your invoice numbers. If you use your PayPal account for multiple stores ensure this prefix is unique as PayPal will not allow orders with the same invoice number.', 'complete-paypal-payments-for-woocommerce'),
                    'default' => 'WC-PSB',
                    'desc_tip' => true,
                ),
                'order_review_page_enable_coupons' => array(
                    'title' => __('Enable/Disable coupons', 'complete-paypal-payments-for-woocommerce'),
                    'type' => 'checkbox',
                    'label' => __('Enable the use of coupon codes', 'complete-paypal-payments-for-woocommerce'),
                    'description' => __('Coupons can be applied from the order review.', 'complete-paypal-payments-for-woocommerce'),
                    'default' => 'yes',
                ),
                'soft_descriptor' => array(
                    'title' => __('Credit Card Statement Name', 'complete-paypal-payments-for-woocommerce'),
                    'type' => 'text',
                    'description' => __('The value entered here will be displayed on the buyer\'s credit card statement.', 'complete-paypal-payments-for-woocommerce'),
                    'default' => '',
                    'desc_tip' => true,
                    'custom_attributes' => array('maxlength' => '22'),
                ),
                'debug' => array(
                    'title' => __('Debug log', 'complete-paypal-payments-for-woocommerce'),
                    'type' => 'checkbox',
                    'label' => __('Enable logging', 'complete-paypal-payments-for-woocommerce'),
                    'default' => 'no',
                    'description' => sprintf(__('Log PayPal events, such as Webhook, Payment, Refund inside %s Note: this may log personal information. We recommend using this for debugging purposes only and deleting the logs when finished.', 'complete-paypal-payments-for-woocommerce'), '<code>' . WC_Log_Handler_File::get_log_file_path('complete_paypal_payments') . '</code>'),
                )
            );
            if (function_exists('wc_coupons_enabled')) {
                if (!wc_coupons_enabled()) {
                    unset($advanced_settings['order_review_page_enable_coupons']);
                }
            }
            $settings = apply_filters('cpp_settings', array_merge($default_settings, $button_manager_settings, $pay_later_messaging_settings, $order_review_page_settings, $advanced_settings));
            return $settings;
        }

    }

}