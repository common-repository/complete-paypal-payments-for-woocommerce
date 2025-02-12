<?php

/**
 * @since      1.0.0
 * @package    Complete_PayPal_Payments_WooCommerce_Request
 * @subpackage Complete_PayPal_Payments_WooCommerce_Request/includes
 * @author     Eric Babin <completepaypalpayments@gmail.com>
 */
class Complete_PayPal_Payments_WooCommerce_Request extends WC_Payment_Gateway {

    /**
     * @since    1.0.0
     */
    public $log_enabled = false;
    public static $log = false;
    public $request;
    public $id;
    public $decimals;

    public function __construct() {
        try {
            $this->id = 'complete_paypal_payments';
            $this->is_sandbox = 'yes' === $this->get_option('testmode', 'no');
            $this->debug = 'yes' === $this->get_option('debug', 'no');
            $this->cpp_currency = array('AUD', 'BRL', 'CAD', 'CZK', 'DKK', 'EUR', 'HKD', 'INR', 'ILS', 'JPY', 'MYR', 'MXN', 'TWD', 'NZD', 'NOK', 'PHP', 'PLN', 'GBP', 'RUB', 'SGD', 'SEK', 'CHF', 'THB', 'USD');
            $this->log_enabled = $this->debug;
            if ($this->is_sandbox) {
                $this->client_id = $this->get_option('sandbox_client_id');
                $this->secret = $this->get_option('sandbox_api_secret');
                $this->token_url = 'https://api.sandbox.paypal.com/v1/oauth2/token';
                $this->order_url = 'https://api.sandbox.paypal.com/v2/checkout/orders/';
                $this->paypal_oauth_api = 'https://api.sandbox.paypal.com/v1/oauth2/token/';
                $this->paypal_order_api = 'https://api.sandbox.paypal.com/v2/checkout/orders/';
                $this->paypal_refund_api = 'https://api.sandbox.paypal.com/v2/payments/captures/';
                $this->auth = 'https://api.sandbox.paypal.com/v2/payments/authorizations/';
                $this->webhook = 'https://api.sandbox.paypal.com/v1/notifications/webhooks';
                $this->basicAuth = base64_encode($this->client_id . ":" . $this->secret);
                $this->webhook_id = 'cpp_sandbox_webhook_id';
                $this->webhook_url = 'https://api.sandbox.paypal.com/v1/notifications/verify-webhook-signature';
                $this->generate_token_url = 'https://api.sandbox.paypal.com/v1/identity/generate-token';
            } else {
                $this->client_id = $this->get_option('api_client_id');
                $this->secret = $this->get_option('api_secret');
                $this->token_url = 'https://api.paypal.com/v1/oauth2/token';
                $this->order_url = 'https://api.paypal.com/v2/checkout/orders/';
                $this->paypal_oauth_api = 'https://api.paypal.com/v1/oauth2/token/';
                $this->paypal_order_api = 'https://api.paypal.com/v2/checkout/orders/';
                $this->paypal_refund_api = 'https://api.paypal.com/v2/payments/captures/';
                $this->auth = 'https://api.paypal.com/v2/payments/authorizations/';
                $this->webhook = 'https://api.paypal.com/v1/notifications/webhooks';
                $this->basicAuth = base64_encode($this->client_id . ":" . $this->secret);
                $this->webhook_id = 'cpp_live_webhook_id';
                $this->webhook_url = 'https://api.paypal.com/v1/notifications/verify-webhook-signature';
                $this->generate_token_url = 'https://api.paypal.com/v1/identity/generate-token';
            }
            $this->paymentaction = $this->get_option('paymentaction', 'capture');
            $this->payee_preferred = 'yes' === $this->get_option('payee_preferred', 'no');
            $this->invoice_prefix = $this->get_option('invoice_prefix', 'WC-PSB');
            $this->soft_descriptor = $this->get_option('soft_descriptor', '');
            $this->brand_name = $this->get_option('brand_name', get_bloginfo('name'));
            $this->landing_page = $this->get_option('landing_page', 'NO_PREFERENCE');
            $this->advanced_card_payments = 'yes' === $this->get_option('enable_advanced_card_payments', 'no');
            $this->decimals = $this->cpp_get_number_of_decimal_digits();
            if (cpp_is_advanced_cards_available() === false) {
                $this->advanced_card_payments = false;
            }
            if (false === get_transient('cpp_is_webhook_process_started')) {
                $webhook_id = get_option($this->webhook_id, '');
                if (empty($webhook_id)) {
                    $this->cpp_create_webhooks_request();
                }
            }
            $this->send_items = 'yes' === $this->get_option('send_items', 'yes');
            $this->AVSCodes = array("A" => "Address Matches Only (No ZIP)",
                "B" => "Address Matches Only (No ZIP)",
                "C" => "This tranaction was declined.",
                "D" => "Address and Postal Code Match",
                "E" => "This transaction was declined.",
                "F" => "Address and Postal Code Match",
                "G" => "Global Unavailable - N/A",
                "I" => "International Unavailable - N/A",
                "N" => "None - Transaction was declined.",
                "P" => "Postal Code Match Only (No Address)",
                "R" => "Retry - N/A",
                "S" => "Service not supported - N/A",
                "U" => "Unavailable - N/A",
                "W" => "Nine-Digit ZIP Code Match (No Address)",
                "X" => "Exact Match - Address and Nine-Digit ZIP",
                "Y" => "Address and five-digit Zip match",
                "Z" => "Five-Digit ZIP Matches (No Address)");

            $this->CVV2Codes = array(
                "E" => "N/A",
                "M" => "Match",
                "N" => "No Match",
                "P" => "Not Processed - N/A",
                "S" => "Service Not Supported - N/A",
                "U" => "Service Unavailable - N/A",
                "X" => "No Response - N/A"
            );
            if (isset($_GET['from']) && 'cart' === $_GET['from']) {
                $this->order_button_text = __('Continue to payment', 'complete-paypal-payments-for-woocommerce');
            }
        } catch (Exception $ex) {
            
        }
    }

    public function cpp_application_context() {
        return array(
            'brand_name' => $this->brand_name,
            'locale' => ppc_get_paypal_api_locale_code(),
            'landing_page' => $this->landing_page,
            'shipping_preference' => $this->cpp_shipping_preference(),
            'user_action' => is_checkout() ? 'PAY_NOW' : 'CONTINUE',
            'return_url' => 'https://www.google.com',
            'cancel_url' => 'https://www.google.com'
        );
    }

    public function cpp_shipping_preference() {
        $shipping_preference = 'GET_FROM_FILE';
        $is_cart = is_cart() && !WC()->cart->is_empty();
        $is_checkout = is_checkout();
        $page = $is_cart ? 'cart' : ( $is_checkout ? 'checkout' : null );
        switch ($page) {
            case 'cart':
                $shipping_preference = WC()->cart->needs_shipping_address() ? 'GET_FROM_FILE' : 'NO_SHIPPING';
                break;
            case 'checkout':
                $shipping_preference = WC()->cart->needs_shipping_address() ? 'SET_PROVIDED_ADDRESS' : 'NO_SHIPPING';
                break;
        }
        return $shipping_preference;
    }

    public function is_valid_for_use() {
        try {
            if (empty($this->client_id) || empty($this->secret)) {
                return false;
            }

            return true;
        } catch (Exception $ex) {
            
        }
    }

    public function cpp_log($message, $level = 'info') {
        if ($this->log_enabled) {
            if (empty($this->logger)) {
                $this->logger = wc_get_logger();
            }
            $this->logger->log($level, $message, array('source' => 'complete_paypal_payments'));
        }
    }

    public function cpp_create_order_request($woo_order_id = null) {
        try {
            if ($woo_order_id == null) {
                $cart = $this->cpp_get_details_from_cart();
            } else {
                $cart = $this->cpp_get_details_from_order($woo_order_id);
            }
            $reference_id = wc_generate_order_key();
            cpp_set_session('cpp_reference_id', $reference_id);
            $intent = ($this->paymentaction === 'capture') ? 'CAPTURE' : 'AUTHORIZE';
            $body_request = array(
                'intent' => $intent,
                'application_context' => $this->cpp_application_context(),
                'payment_method' => array('payee_preferred' => ($this->payee_preferred) ? 'IMMEDIATE_PAYMENT_REQUIRED' : 'UNRESTRICTED'),
                'purchase_units' =>
                array(
                    0 =>
                    array(
                        'reference_id' => $reference_id,
                        'amount' =>
                        array(
                            'currency_code' => get_woocommerce_currency(),
                            'value' => cpp_ppcp_round($cart['order_total'], $this->decimals),
                            'breakdown' => array()
                        )
                    ),
                ),
            );
            if ($woo_order_id != null) {
                $order = wc_get_order($woo_order_id);
                $body_request['purchase_units'][0]['soft_descriptor'] = $this->soft_descriptor;
                $body_request['purchase_units'][0]['invoice_id'] = $this->invoice_prefix . str_replace("#", "", $order->get_order_number());
                $body_request['purchase_units'][0]['custom_id'] = wp_json_encode(
                        array(
                            'order_id' => $order->get_id(),
                            'order_key' => $order->get_order_key(),
                        )
                );
            } else {
                $body_request['purchase_units'][0]['invoice_id'] = $reference_id;
                $body_request['purchase_units'][0]['custom_id'] = wp_json_encode(
                        array(
                            'order_id' => $reference_id,
                            'order_key' => $reference_id,
                        )
                );
            }
            if ($this->send_items === true) {
                if (isset($cart['total_item_amount']) && $cart['total_item_amount'] > 0) {
                    $body_request['purchase_units'][0]['amount']['breakdown']['item_total'] = array(
                        'currency_code' => get_woocommerce_currency(),
                        'value' => cpp_ppcp_round($cart['total_item_amount'], $this->decimals)
                    );
                }
                if (isset($cart['shipping']) && $cart['shipping'] > 0) {
                    $body_request['purchase_units'][0]['amount']['breakdown']['shipping'] = array(
                        'currency_code' => get_woocommerce_currency(),
                        'value' => cpp_ppcp_round($cart['shipping'], $this->decimals)
                    );
                }
                if (isset($cart['ship_discount_amount']) && $cart['ship_discount_amount'] > 0) {
                    $body_request['purchase_units'][0]['amount']['breakdown']['shipping_discount'] = array(
                        'currency_code' => get_woocommerce_currency(),
                        'value' => cpp_ppcp_round($cart['ship_discount_amount'], $this->decimals),
                    );
                }
                if (isset($cart['order_tax']) && $cart['order_tax'] > 0) {
                    $body_request['purchase_units'][0]['amount']['breakdown']['tax_total'] = array(
                        'currency_code' => get_woocommerce_currency(),
                        'value' => cpp_ppcp_round($cart['order_tax'], $this->decimals)
                    );
                }
                if (isset($cart['discount']) && $cart['discount'] > 0) {
                    $body_request['purchase_units'][0]['amount']['breakdown']['discount'] = array(
                        'currency_code' => get_woocommerce_currency(),
                        'value' => cpp_ppcp_round($cart['discount'], $this->decimals)
                    );
                }
                if (isset($cart['items']) && !empty($cart['items'])) {
                    foreach ($cart['items'] as $key => $order_items) {
                        $description = !empty($order_items['description']) ? $order_items['description'] : '';
                        if (strlen($description) > 127) {
                            $description = substr($description, 0, 124) . '...';
                        }
                        $body_request['purchase_units'][0]['items'][$key] = array(
                            'name' => $order_items['name'],
                            'description' => html_entity_decode($description, ENT_NOQUOTES, 'UTF-8'),
                            'sku' => $order_items['sku'],
                            'category' => $order_items['category'],
                            'quantity' => $order_items['quantity'],
                            'unit_amount' =>
                            array(
                                'currency_code' => get_woocommerce_currency(),
                                'value' => cpp_ppcp_round($order_items['amount'], $this->decimals)
                            ),
                        );
                    }
                }
            }
            if ($woo_order_id != null) {
                $order = wc_get_order($woo_order_id);
                $old_wc = version_compare(WC_VERSION, '3.0', '<');
                if ($order->needs_shipping_address() || WC()->cart->needs_shipping_address()) {
                    if (( $old_wc && ( $order->shipping_address_1 || $order->shipping_address_2 ) ) || (!$old_wc && $order->has_shipping_address() )) {
                        $shipping_first_name = $old_wc ? $order->shipping_first_name : $order->get_shipping_first_name();
                        $shipping_last_name = $old_wc ? $order->shipping_last_name : $order->get_shipping_last_name();
                        $shipping_address_1 = $old_wc ? $order->shipping_address_1 : $order->get_shipping_address_1();
                        $shipping_address_2 = $old_wc ? $order->shipping_address_2 : $order->get_shipping_address_2();
                        $shipping_city = $old_wc ? $order->shipping_city : $order->get_shipping_city();
                        $shipping_state = $old_wc ? $order->shipping_state : $order->get_shipping_state();
                        $shipping_postcode = $old_wc ? $order->shipping_postcode : $order->get_shipping_postcode();
                        $shipping_country = $old_wc ? $order->shipping_country : $order->get_shipping_country();
                    } else {
                        $shipping_first_name = $old_wc ? $order->billing_first_name : $order->get_billing_first_name();
                        $shipping_last_name = $old_wc ? $order->billing_last_name : $order->get_billing_last_name();
                        $shipping_address_1 = $old_wc ? $order->billing_address_1 : $order->get_billing_address_1();
                        $shipping_address_2 = $old_wc ? $order->billing_address_2 : $order->get_billing_address_2();
                        $shipping_city = $old_wc ? $order->billing_city : $order->get_billing_city();
                        $shipping_state = $old_wc ? $order->billing_state : $order->get_billing_state();
                        $shipping_postcode = $old_wc ? $order->billing_postcode : $order->get_billing_postcode();
                        $shipping_country = $old_wc ? $order->billing_country : $order->get_billing_country();
                    }
                    if (!empty($shipping_first_name) && !empty($shipping_last_name)) {
                        $body_request['purchase_units'][0]['shipping']['name']['full_name'] = $shipping_first_name . ' ' . $shipping_last_name;
                    }
                    $body_request['purchase_units'][0]['shipping']['address'] = array(
                        'address_line_1' => $shipping_address_1,
                        'address_line_2' => $shipping_address_2,
                        'admin_area_2' => $shipping_city,
                        'admin_area_1' => $shipping_state,
                        'postal_code' => $shipping_postcode,
                        'country_code' => $shipping_country,
                    );
                }
            } else {
                if (true === WC()->cart->needs_shipping_address()) {
                    if (is_user_logged_in()) {
                        if (!empty($cart['shipping_address']['first_name']) && !empty($cart['shipping_address']['last_name'])) {
                            $body_request['purchase_units'][0]['shipping']['name']['full_name'] = $cart['shipping_address']['first_name'] . ' ' . $cart['shipping_address']['last_name'];
                        }
                        if (!empty($cart['shipping_address']['address_1']) && !empty($cart['shipping_address']['city']) && !empty($cart['shipping_address']['state']) && !empty($cart['shipping_address']['postcode']) && !empty($cart['shipping_address']['country'])) {
                            $body_request['purchase_units'][0]['shipping']['address'] = array(
                                'address_line_1' => $cart['shipping_address']['address_1'],
                                'address_line_2' => $cart['shipping_address']['address_2'],
                                'admin_area_2' => $cart['shipping_address']['city'],
                                'admin_area_1' => $cart['shipping_address']['state'],
                                'postal_code' => $cart['shipping_address']['postcode'],
                                'country_code' => $cart['shipping_address']['country'],
                            );
                        }
                    }
                }
            }
            $body_request = $this->cpp_set_payer_details($woo_order_id, $body_request);
            $body_request = cpp_remove_empty_key($body_request);
            $this->cpp_add_log_details('Create order');
            $this->cpp_log('Order Request : ' . wc_print_r($body_request, true));
            $body_request = json_encode($body_request);
            $response = wp_remote_post($this->paypal_order_api, array(
                'method' => 'POST',
                'timeout' => 60,
                'redirection' => 5,
                'httpversion' => '1.1',
                'blocking' => true,
                'headers' => array('Content-Type' => 'application/json', 'Authorization' => "Basic " . $this->basicAuth, "prefer" => "return=representation", 'PayPal-Partner-Attribution-Id' => 'MBJTechnolabs_SI_SPB', 'PayPal-Request-Id' => $this->generate_request_id()),
                'body' => $body_request,
                'cookies' => array()
                    )
            );
            if (is_wp_error($response)) {
                $error_message = $response->get_error_message();
                $this->cpp_log('Error Message : ' . wc_print_r($error_message, true));
            } else {
                if (ob_get_length())
                    ob_end_clean();
                $return_response = array();
                $api_response = json_decode(wp_remote_retrieve_body($response), true);
                if (!empty($api_response['status'])) {
                    $this->cpp_log('Response Code: ' . wp_remote_retrieve_response_code($response));
                    $this->cpp_log('Response Message: ' . wp_remote_retrieve_response_message($response));
                    $this->cpp_log('Response Body: ' . wc_print_r($api_response, true));
                    $return_response['orderID'] = $api_response['id'];
                    if (!empty(isset($woo_order_id) && !empty($woo_order_id))) {
                        cpp_update_post_meta($order, '_paypal_order_id', $api_response['id']);
                        cpp_set_session('cpp_paypal_transaction_details', $api_response);
                    }
                    wp_send_json($return_response, 200);
                    exit();
                } else {
                    $error_message = $this->cpp_get_readable_message($api_response);
                    $this->cpp_log('Error Message : ' . wc_print_r($api_response, true));
                    wp_send_json_error($error_message);
                }
            }
        } catch (Exception $ex) {
            
        }
    }

    public function cpp_get_extra_offset_line_item($amount) {
        try {
            return array(
                'name' => 'Line Item Amount Offset',
                'description' => 'Adjust cart calculation discrepancy',
                'quantity' => 1,
                'amount' => cpp_ppcp_round($amount, $this->decimals),
            );
        } catch (Exception $ex) {
            
        }
    }

    public function cpp_get_number_of_decimal_digits() {
        try {
            return $this->cpp_is_currency_supports_zero_decimal() ? 0 : 2;
        } catch (Exception $ex) {
            
        }
    }

    public function cpp_order_capture_request($woo_order_id, $need_to_update_order = true) {
        try {
            $order = wc_get_order($woo_order_id);
            if ($need_to_update_order) {
                $this->cpp_update_order($order);
            }
            $paypal_order_id = cpp_get_session('cpp_paypal_order_id');
            $this->cpp_add_log_details('Capture payment for order');
            $this->cpp_log('Request : ' . wc_print_r($this->paypal_order_api . $paypal_order_id . '/capture', true));
            $response = wp_remote_post($this->paypal_order_api . $paypal_order_id . '/capture', array(
                'timeout' => 60,
                'redirection' => 5,
                'httpversion' => '1.1',
                'blocking' => true,
                'headers' => array('Content-Type' => 'application/json', 'Authorization' => "Basic " . $this->basicAuth, "prefer" => "return=representation", 'PayPal-Partner-Attribution-Id' => 'MBJTechnolabs_SI_SPB', 'PayPal-Request-Id' => $this->generate_request_id()),
                    )
            );
            if (is_wp_error($response)) {
                $error_message = $response->get_error_message();
                $this->cpp_log('Error Message : ' . wc_print_r($error_message, true));
                wc_add_notice($error_message, 'error');
                return false;
            } else {
                $return_response = array();
                $api_response = json_decode(wp_remote_retrieve_body($response), true);
                $this->cpp_log('Response : ' . wc_print_r($api_response, true));
                if (isset($api_response['id']) && !empty($api_response['id'])) {
                    $return_response['paypal_order_id'] = $api_response['id'];
                    cpp_update_post_meta($order, '_paypal_order_id', $api_response['id']);
                    if ($api_response['status'] == 'COMPLETED') {
                        $payment_source = isset($api_response['payment_source']) ? $api_response['payment_source'] : '';
                        if (!empty($payment_source['card'])) {
                            $card_response_order_note = __('Card Details', 'complete-paypal-payments-for-woocommerce');
                            $card_response_order_note .= "\n";
                            $card_response_order_note .= 'Last digits : ' . $payment_source['card']['last_digits'];
                            $card_response_order_note .= "\n";
                            $card_response_order_note .= 'Brand : ' . cpp_readable($payment_source['card']['brand']);
                            $card_response_order_note .= "\n";
                            $card_response_order_note .= 'Card type : ' . cpp_readable($payment_source['card']['type']);
                            $order->add_order_note($card_response_order_note);
                        }
                        $processor_response = isset($api_response['purchase_units']['0']['payments']['captures']['0']['processor_response']) ? $api_response['purchase_units']['0']['payments']['captures']['0']['processor_response'] : '';
                        if (!empty($processor_response['avs_code'])) {
                            $avs_response_order_note = __('Address Verification Result', 'complete-paypal-payments-for-woocommerce');
                            $avs_response_order_note .= "\n";
                            $avs_response_order_note .= $processor_response['avs_code'];
                            if (isset($this->AVSCodes[$processor_response['avs_code']])) {
                                $avs_response_order_note .= ' : ' . $this->AVSCodes[$processor_response['avs_code']];
                            }
                            $order->add_order_note($avs_response_order_note);
                        }
                        if (!empty($processor_response['cvv_code'])) {
                            $cvv2_response_code = __('Card Security Code Result', 'complete-paypal-payments-for-woocommerce');
                            $cvv2_response_code .= "\n";
                            $cvv2_response_code .= $processor_response['cvv_code'];
                            if (isset($this->CVV2Codes[$processor_response['cvv_code']])) {
                                $cvv2_response_code .= ' : ' . $this->CVV2Codes[$processor_response['cvv_code']];
                            }
                            $order->add_order_note($cvv2_response_code);
                        }
                        $currency_code = isset($api_response['purchase_units'][0]['payments']['captures'][0]['seller_receivable_breakdown']['paypal_fee']['currency_code']) ? $api_response['purchase_units'][0]['payments']['captures'][0]['seller_receivable_breakdown']['paypal_fee']['currency_code'] : '';
                        $value = isset($api_response['purchase_units'][0]['payments']['captures'][0]['seller_receivable_breakdown']['paypal_fee']['value']) ? $api_response['purchase_units'][0]['payments']['captures'][0]['seller_receivable_breakdown']['paypal_fee']['value'] : '';
                        cpp_update_post_meta($order, '_paypal_fee', $value);
                        cpp_update_post_meta($order, '_paypal_fee_currency_code', $currency_code);
                        $transaction_id = isset($api_response['purchase_units']['0']['payments']['captures']['0']['id']) ? $api_response['purchase_units']['0']['payments']['captures']['0']['id'] : '';
                        $seller_protection = isset($api_response['purchase_units']['0']['payments']['captures']['0']['seller_protection']['status']) ? $api_response['purchase_units']['0']['payments']['captures']['0']['seller_protection']['status'] : '';
                        $payment_status = isset($api_response['purchase_units']['0']['payments']['captures']['0']['status']) ? $api_response['purchase_units']['0']['payments']['captures']['0']['status'] : '';
                        if ($payment_status == 'COMPLETED') {
                            $order->payment_complete($transaction_id);
                            $order->add_order_note(sprintf(__('Payment via %s : %s.', 'complete-paypal-payments-for-woocommerce'), $order->get_payment_method_title(), ucfirst(strtolower($payment_status))));
                        } else {
                            $payment_status_reason = isset($api_response['purchase_units']['0']['payments']['captures']['0']['status_details']['reason']) ? $api_response['purchase_units']['0']['payments']['captures']['0']['status_details']['reason'] : '';
                            cpp_update_woo_order_status($woo_order_id, $payment_status, $payment_status_reason);
                        }
                        cpp_update_post_meta($order, '_payment_status', $payment_status);
                        $order->add_order_note(sprintf(__('%s Transaction ID: %s', 'complete-paypal-payments-for-woocommerce'), $order->get_payment_method_title(), $transaction_id));
                        $order->add_order_note('Seller Protection Status: ' . cpp_readable($seller_protection));
                    }
                    return true;
                } else {
                    $error_message = $this->cpp_get_readable_message($api_response);
                    wc_add_notice($error_message, 'error');
                    return false;
                }
            }
        } catch (Exception $ex) {
            
        }
    }

    public function cpp_order_auth_request($woo_order_id) {
        try {
            $order = wc_get_order($woo_order_id);
            $this->cpp_update_order($order);
            $paypal_order_id = cpp_get_session('cpp_paypal_order_id');
            $this->cpp_add_log_details('Authorize payment for order');
            $this->cpp_log('Request : ' . wc_print_r($this->paypal_order_api . $paypal_order_id . '/authorize', true));
            $response = wp_remote_post($this->paypal_order_api . $paypal_order_id . '/authorize', array(
                'timeout' => 60,
                'redirection' => 5,
                'httpversion' => '1.1',
                'blocking' => true,
                'headers' => array('Content-Type' => 'application/json', 'Authorization' => "Basic " . $this->basicAuth, "prefer" => "return=representation", 'PayPal-Partner-Attribution-Id' => 'MBJTechnolabs_SI_SPB', 'PayPal-Request-Id' => $this->generate_request_id()),
                    )
            );
            if (is_wp_error($response)) {
                $error_message = $response->get_error_message();
                $this->cpp_log('Error Message : ' . wc_print_r($error_message, true));
                wc_add_notice($error_message, 'error');
                return false;
            } else {
                $return_response = array();
                $api_response = json_decode(wp_remote_retrieve_body($response), true);
                $this->cpp_log('Response : ' . wc_print_r($api_response, true));
                if (!empty($api_response['id'])) {
                    $return_response['paypal_order_id'] = $api_response['id'];
                    if (isset($woo_order_id) && !empty($woo_order_id)) {
                        cpp_update_post_meta($order, '_paypal_order_id', $api_response['id']);
                    }
                    if ($api_response['status'] == 'COMPLETED') {
                        $transaction_id = isset($api_response['purchase_units']['0']['payments']['authorizations']['0']['id']) ? $api_response['purchase_units']['0']['payments']['authorizations']['0']['id'] : '';
                        $seller_protection = isset($api_response['purchase_units']['0']['payments']['authorizations']['0']['seller_protection']['status']) ? $api_response['purchase_units']['0']['payments']['authorizations']['0']['seller_protection']['status'] : '';
                        $payment_status = isset($api_response['purchase_units']['0']['payments']['authorizations']['0']['status']) ? $api_response['purchase_units']['0']['payments']['authorizations']['0']['status'] : '';
                        cpp_update_post_meta($order, '_transaction_id', $transaction_id);
                        cpp_update_post_meta($order, '_payment_status', $payment_status);
                        cpp_update_post_meta($order, '_auth_transaction_id', $transaction_id);
                        cpp_update_post_meta($order, '_payment_action', $this->paymentaction);
                        $order->add_order_note(sprintf(__('%s Transaction ID: %s', 'complete-paypal-payments-for-woocommerce'), $order->get_payment_method_title(), $transaction_id));
                        $order->add_order_note('Seller Protection Status: ' . cpp_readable($seller_protection));
                        $order->update_status('on-hold');
                        $order->add_order_note(__('Payment authorized. Change payment status to processing or complete to capture funds.', 'complete-paypal-payments-for-woocommerce'));
                    }
                    WC()->cart->empty_cart();
                    return true;
                } else {
                    $error_message = $this->cpp_get_readable_message($api_response);
                    wc_add_notice($error_message, 'error');
                    return false;
                }
            }
        } catch (Exception $ex) {
            
        }
    }

    public function cpp_get_checkout_details($paypal_order_id) {
        try {
            $this->cpp_add_log_details('Get Order Details');

            $this->cpp_log('Endpoint: ' . $this->paypal_order_api . $paypal_order_id);
            $response = wp_remote_get($this->paypal_order_api . $paypal_order_id, array(
                'timeout' => 60,
                'redirection' => 5,
                'httpversion' => '1.1',
                'blocking' => true,
                'headers' => array('Content-Type' => 'application/json', 'Authorization' => "Basic " . $this->basicAuth, "prefer" => "return=representation", 'PayPal-Partner-Attribution-Id' => 'MBJTechnolabs_SI_SPB'),
                'body' => array(),
                'cookies' => array()
                    )
            );
            if (is_wp_error($response)) {
                $error_message = $response->get_error_message();
                $this->cpp_log('Error Message : ' . wc_print_r($error_message, true));
            } else {
                $api_response = json_decode(wp_remote_retrieve_body($response));
                $this->cpp_log('Response Code: ' . wp_remote_retrieve_response_code($response));
                $this->cpp_log('Response Message: ' . wp_remote_retrieve_response_message($response));
                $this->cpp_log('Response Body: ' . wc_print_r($api_response, true));
                cpp_set_session('cpp_paypal_order_id', $paypal_order_id);
                cpp_set_session('cpp_paypal_transaction_details', $api_response);
                return $api_response;
            }
        } catch (Exception $ex) {
            
        }
    }

    public function cpp_get_details_from_cart() {
        try {
            $old_wc = version_compare(WC_VERSION, '3.0', '<');
            $rounded_total = $this->cpp_get_rounded_total_in_cart();
            $discounts = WC()->cart->get_cart_discount_total();
            $details = array(
                'total_item_amount' => cpp_ppcp_round(WC()->cart->cart_contents_total, $this->decimals) + $discounts,
                'order_tax' => cpp_ppcp_round(WC()->cart->tax_total + WC()->cart->shipping_tax_total, $this->decimals),
                'shipping' => cpp_ppcp_round(WC()->cart->shipping_total, $this->decimals),
                'items' => $this->cpp_get_paypal_line_items_from_cart(),
                'shipping_address' => $this->cpp_get_address_from_customer(),
                'email' => $old_wc ? WC()->customer->billing_email : WC()->customer->get_billing_email(),
            );
            return $this->cpp_get_details($details, $discounts, $rounded_total, WC()->cart->total);
        } catch (Exception $ex) {
            
        }
    }

    public function cpp_is_currency_supports_zero_decimal() {
        try {
            return in_array(get_woocommerce_currency(), array('HUF', 'JPY', 'TWD'));
        } catch (Exception $ex) {
            
        }
    }

    public function cpp_get_rounded_total_in_cart() {
        try {
            $rounded_total = 0;
            foreach (WC()->cart->cart_contents as $cart_item_key => $values) {
                $amount = cpp_ppcp_round($values['line_subtotal'] / $values['quantity'], $this->decimals);
                $rounded_total += cpp_ppcp_round($amount * $values['quantity'], $this->decimals);
            }
            return $rounded_total;
        } catch (Exception $ex) {
            
        }
    }

    public function cpp_get_paypal_line_items_from_cart() {
        try {
            $items = array();
            foreach (WC()->cart->cart_contents as $cart_item_key => $values) {
                $desc = '';
                $amount = cpp_ppcp_round($values['line_subtotal'] / $values['quantity'], $this->decimals);
                if (version_compare(WC_VERSION, '3.0', '<')) {
                    $product = $values['data'];
                    $name = $values['data']->post->post_title;
                    $sku = '';
                    $category = '';
                } else {
                    $product = $values['data'];
                    $name = $product->get_name();
                    $sku = $product->get_sku();
                    $category = $product->needs_shipping() ? 'PHYSICAL_GOODS' : 'DIGITAL_GOODS';
                }
                if (is_object($product)) {
                    if ($product->is_type('variation')) {
                        if (!empty($values['variation']) && is_array($values['variation'])) {
                            foreach ($values['variation'] as $key => $value) {
                                $key = str_replace(array('attribute_pa_', 'attribute_', 'Pa_', 'pa_'), '', $key);
                                $desc .= ' ' . ucwords($key) . ': ' . $value;
                            }
                            $desc = trim($desc);
                        }
                    }
                }
                $item = array(
                    'name' => $name,
                    'description' => $desc,
                    'sku' => $sku,
                    'category' => $category,
                    'quantity' => $values['quantity'],
                    'amount' => $amount,
                );
                $items[] = $item;
            }
            return $items;
        } catch (Exception $ex) {
            
        }
    }

    public function cpp_get_address_from_customer() {
        try {
            $customer = WC()->customer;
            $old_wc = version_compare(WC_VERSION, '3.0', '<');
            if ($customer->get_shipping_address() || $customer->get_shipping_address_2()) {
                $shipping_first_name = $old_wc ? $customer->shipping_first_name : $customer->get_shipping_first_name();
                $shipping_last_name = $old_wc ? $customer->shipping_last_name : $customer->get_shipping_last_name();
                $shipping_address_1 = $customer->get_shipping_address();
                $shipping_address_2 = $customer->get_shipping_address_2();
                $shipping_city = $customer->get_shipping_city();
                $shipping_state = $customer->get_shipping_state();
                $shipping_postcode = $customer->get_shipping_postcode();
                $shipping_country = $customer->get_shipping_country();
            } else {
                $shipping_first_name = $old_wc ? $customer->billing_first_name : $customer->get_billing_first_name();
                $shipping_last_name = $old_wc ? $customer->billing_last_name : $customer->get_billing_last_name();
                $shipping_address_1 = $old_wc ? $customer->get_address() : $customer->get_billing_address_1();
                $shipping_address_2 = $old_wc ? $customer->get_address_2() : $customer->get_billing_address_2();
                $shipping_city = $old_wc ? $customer->get_city() : $customer->get_billing_city();
                $shipping_state = $old_wc ? $customer->get_state() : $customer->get_billing_state();
                $shipping_postcode = $old_wc ? $customer->get_postcode() : $customer->get_billing_postcode();
                $shipping_country = $old_wc ? $customer->get_country() : $customer->get_billing_country();
            }
            return array(
                'first_name' => $shipping_first_name,
                'last_name' => $shipping_last_name,
                'company' => '',
                'address_1' => $shipping_address_1,
                'address_2' => $shipping_address_2,
                'city' => $shipping_city,
                'state' => $shipping_state,
                'postcode' => $shipping_postcode,
                'country' => $shipping_country,
                'phone' => $old_wc ? $customer->billing_phone : $customer->get_billing_phone(),
            );
        } catch (Exception $ex) {
            
        }
    }

    public function cpp_get_details($details, $discounts, $rounded_total, $total) {
        try {
            $discounts = cpp_ppcp_round($discounts, $this->decimals);
            $details['order_total'] = cpp_ppcp_round(
                    $details['total_item_amount'] + $details['order_tax'] + $details['shipping'] - $discounts, $this->decimals
            );
            $diff = 0;
            if ($details['total_item_amount'] != $rounded_total) {
                $diff = round($details['total_item_amount'] + $discounts - $rounded_total, $this->decimals);
                if (abs($diff) > 0.000001 && 0.0 !== (float) $diff) {
                    $extra_line_item = $this->cpp_get_extra_offset_line_item($diff);
                    $details['items'][] = $extra_line_item;
                    $details['total_item_amount'] += $extra_line_item['amount'];
                    $details['total_item_amount'] = cpp_ppcp_round($details['total_item_amount'], $this->decimals);
                    $details['order_total'] += $extra_line_item['amount'];
                    $details['order_total'] = cpp_ppcp_round($details['order_total'], $this->decimals);
                }
            }
            if (0 == $details['total_item_amount']) {
                unset($details['items']);
            }
            if ($details['total_item_amount'] != $rounded_total) {
                unset($details['items']);
            }
            if ($details['total_item_amount'] == $discounts) {
                unset($details['items']);
            } else if ($discounts > 0 && $discounts < $details['total_item_amount'] && !empty($details['items'])) {
                $details['discount'] = $discounts;
            }
            $details['discount'] = $discounts;
            $details['ship_discount_amount'] = 0;
            $wc_order_total = cpp_ppcp_round($total, $this->decimals);
            $discounted_total = cpp_ppcp_round($details['order_total'], $this->decimals);
            if ($wc_order_total != $discounted_total) {
                if ($discounted_total < $wc_order_total) {
                    $details['order_tax'] += $wc_order_total - $discounted_total;
                    $details['order_tax'] = cpp_ppcp_round($details['order_tax'], $this->decimals);
                } else {
                    $details['ship_discount_amount'] += $wc_order_total - $discounted_total;
                    $details['ship_discount_amount'] = cpp_ppcp_round($details['ship_discount_amount'], $this->decimals);
                    $details['ship_discount_amount'] = abs($details['ship_discount_amount']);
                }
                $details['order_total'] = $wc_order_total;
            }
            if (!is_numeric($details['shipping'])) {
                $details['shipping'] = 0;
            }
            $lisum = 0;
            if (!empty($details['items'])) {
                foreach ($details['items'] as $li => $values) {
                    $lisum += $values['quantity'] * $values['amount'];
                }
            }
            if (abs($lisum) > 0.000001 && 0.0 !== (float) $diff) {
                $details['items'][] = $this->cpp_get_extra_offset_line_item($details['total_item_amount'] - $lisum);
            }
            return $details;
        } catch (Exception $ex) {
            
        }
    }

    public function cpp_get_details_from_order($order_id) {
        try {
            $order = wc_get_order($order_id);
            $rounded_total = $this->cpp_get_rounded_total_in_order($order);
            $details = array(
                'total_item_amount' => cpp_ppcp_round($order->get_subtotal(), $this->decimals),
                'order_tax' => cpp_ppcp_round($order->get_total_tax(), $this->decimals),
                'shipping' => cpp_ppcp_round(( version_compare(WC_VERSION, '3.0', '<') ? $order->get_total_shipping() : $order->get_shipping_total()), $this->decimals),
                'items' => $this->cpp_get_paypal_line_items_from_order($order),
            );
            $details = $this->cpp_get_details($details, $order->get_total_discount(), $rounded_total, $order->get_total());
            return $details;
        } catch (Exception $ex) {
            
        }
    }

    public function cpp_get_paypal_line_items_from_order($order) {
        try {
            $items = array();
            foreach ($order->get_items() as $cart_item_key => $values) {
                $desc = '';
                $amount = cpp_ppcp_round($values['line_subtotal'] / $values['qty'], $this->decimals);
                $product = version_compare(WC_VERSION, '3.0', '<') ? $order->get_product_from_item($values) : $values->get_product();
                $name = $product->get_name();
                $sku = $product->get_sku();
                $category = $product->needs_shipping() ? 'PHYSICAL_GOODS' : 'DIGITAL_GOODS';
                if (is_object($product)) {
                    if ($product->is_type('variation')) {
                        if (!empty($values['variation']) && is_array($values['variation'])) {
                            foreach ($values['variation'] as $key => $value) {
                                $key = str_replace(array('attribute_pa_', 'attribute_', 'Pa_', 'pa_'), '', $key);
                                $desc .= ' ' . ucwords($key) . ': ' . $value;
                            }
                            $desc = trim($desc);
                        }
                    }
                }
                $item = array(
                    'name' => $name,
                    'description' => $desc,
                    'sku' => $sku,
                    'category' => $category,
                    'quantity' => $values['quantity'],
                    'amount' => $amount,
                );
                $items[] = $item;
            }
            return $items;
        } catch (Exception $ex) {
            
        }
    }

    public function cpp_get_rounded_total_in_order($order) {
        try {

            $order = wc_get_order($order);
            $rounded_total = 0;
            foreach ($order->get_items() as $cart_item_key => $values) {
                $amount = cpp_ppcp_round($values['line_subtotal'] / $values['qty'], $this->decimals);
                $rounded_total += cpp_ppcp_round($amount * $values['qty'], $this->decimals);
            }
            return $rounded_total;
        } catch (Exception $ex) {
            
        }
    }

    public function cpp_refund_order($order_id, $amount, $reason, $transaction_id) {
        try {
            $this->cpp_add_log_details('Refund Request');
            $this->cpp_log('Endpoint: ' . $this->paypal_refund_api . $transaction_id . '/refund');
            $order = wc_get_order($order_id);
            $this->decimals = $this->cpp_get_number_of_decimal_digits();
            $reason = !empty($reason) ? $reason : 'Refund';
            $body_request['note_to_payer'] = $reason;
            if (!empty($amount) && $amount > 0) {
                $body_request = array(
                    'amount' =>
                    array(
                        'value' => cpp_ppcp_round($amount, $this->decimals),
                        'currency_code' => $order->get_currency()
                    )
                );
            }
            $body_request = cpp_remove_empty_key($body_request);
            $body_request = json_encode($body_request);
            $this->cpp_log('Refund request: ' . $body_request);
            $response = wp_remote_post($this->paypal_refund_api . $transaction_id . '/refund', array(
                'timeout' => 60,
                'redirection' => 5,
                'httpversion' => '1.1',
                'blocking' => true,
                'headers' => array('Content-Type' => 'application/json', 'Authorization' => "Basic " . $this->basicAuth, "prefer" => "return=representation", 'PayPal-Partner-Attribution-Id' => 'MBJTechnolabs_SI_SPB', 'PayPal-Request-Id' => $this->generate_request_id()),
                'body' => $body_request,
                'cookies' => array()
                    )
            );
            if (is_wp_error($response)) {
                $api_response = json_decode(wp_remote_retrieve_body($response), true);
                $this->cpp_log('Response Code: ' . wp_remote_retrieve_response_code($response));
                $this->cpp_log('Response Message: ' . wp_remote_retrieve_response_message($response));
                $this->cpp_log('Response Body: ' . wc_print_r($api_response, true));
                $error_message = $response->get_error_message();
                $this->cpp_log('Error Message : ' . wc_print_r($error_message, true));
                $order->add_order_note('Error Failed Message : ' . wc_print_r($error_message, true));
                return new WP_Error('error', $$error_message);
            }
            $api_response = json_decode(wp_remote_retrieve_body($response), true);
            $this->cpp_log('Response Code: ' . wp_remote_retrieve_response_code($response));
            $this->cpp_log('Response Message: ' . wp_remote_retrieve_response_message($response));
            $this->cpp_log('Response Body: ' . wc_print_r($api_response, true));
            if (isset($api_response['status']) && $api_response['status'] == "COMPLETED") {
                $gross_amount = isset($api_response['seller_payable_breakdown']['gross_amount']['value']) ? $api_response['seller_payable_breakdown']['gross_amount']['value'] : '';
                $refund_transaction_id = isset($api_response['id']) ? $api_response['id'] : '';
                $order->add_order_note(
                        sprintf(__('Refunded %1$s - Refund ID: %2$s', 'complete-paypal-payments-for-woocommerce'), $gross_amount, $refund_transaction_id)
                );
            } else if (isset($api_response['status']) && $api_response['status'] == "PENDING") {
                $gross_amount = isset($api_response['seller_payable_breakdown']['gross_amount']['value']) ? $api_response['seller_payable_breakdown']['gross_amount']['value'] : '';
                $refund_transaction_id = isset($api_response['id']) ? $api_response['id'] : '';
                $pending_reason_text = isset($api_response['status_details']['reason']) ? $api_response['status_details']['reason'] : '';
                $order->add_order_note(sprintf(__('Payment via %s Pending. Pending reason: %s.', 'complete-paypal-payments-for-woocommerce'), $order->get_payment_method_title(), $pending_reason_text));
                $order->add_order_note(
                        sprintf(__('Refund Amount %1$s - Refund ID: %2$s', 'complete-paypal-payments-for-woocommerce'), $gross_amount, $refund_transaction_id)
                );
            } else {
                if (!empty($api_response['details'][0]['description'])) {
                    $order->add_order_note('Error Message : ' . wc_print_r($api_response['details'][0]['description'], true));
                    throw new Exception($api_response['details'][0]['description']);
                }
                return false;
            }
            return true;
        } catch (Exception $ex) {
            return new WP_Error('error', $ex->getMessage());
        }
    }

    public function cpp_update_order($order) {
        try {
            $patch_request = array();
            $update_amount_request = array();
            $reference_id = cpp_get_session('cpp_reference_id');
            $order_id = version_compare(WC_VERSION, '3.0', '<') ? $order->id : $order->get_id();
            $cart = $this->cpp_get_details_from_order($order_id);
            $old_wc = version_compare(WC_VERSION, '3.0', '<');
            $purchase_units = array(
                'reference_id' => $reference_id,
                'amount' =>
                array(
                    'currency_code' => get_woocommerce_currency(),
                    'value' => $cart['order_total'],
                    'breakdown' => array()
                )
            );
            $purchase_units['invoice_id'] = $this->invoice_prefix . str_replace("#", "", $order->get_order_number());
            $purchase_units['custom_id'] = wp_json_encode(
                    array(
                        'order_id' => $order->get_id(),
                        'order_key' => $order->get_order_key(),
                    )
            );
            if ($this->send_items === true) {
                if (isset($cart['total_item_amount']) && $cart['total_item_amount'] > 0) {
                    $purchase_units['amount']['breakdown']['item_total'] = array(
                        'currency_code' => get_woocommerce_currency(),
                        'value' => $cart['total_item_amount'],
                    );
                }
                if (isset($cart['shipping']) && $cart['shipping'] > 0) {
                    $purchase_units['amount']['breakdown']['shipping'] = array(
                        'currency_code' => get_woocommerce_currency(),
                        'value' => $cart['shipping'],
                    );
                }
                if (isset($cart['ship_discount_amount']) && $cart['ship_discount_amount'] > 0) {
                    $purchase_units['amount']['breakdown']['shipping_discount'] = array(
                        'currency_code' => get_woocommerce_currency(),
                        'value' => cpp_ppcp_round($cart['ship_discount_amount'], $this->decimals),
                    );
                }
                if (isset($cart['order_tax']) && $cart['order_tax'] > 0) {
                    $purchase_units['amount']['breakdown']['tax_total'] = array(
                        'currency_code' => get_woocommerce_currency(),
                        'value' => $cart['order_tax'],
                    );
                }
                if (isset($cart['discount']) && $cart['discount'] > 0) {
                    $purchase_units['amount']['breakdown']['discount'] = array(
                        'currency_code' => get_woocommerce_currency(),
                        'value' => $cart['discount'],
                    );
                }
                if (isset($cart['items']) && !empty($cart['items'])) {
                    foreach ($cart['items'] as $key => $order_items) {
                        $description = !empty($order_items['description']) ? $order_items['description'] : '';
                        $product_name = !empty($order_items['name']) ? $order_items['name'] : '';
                        $purchase_units['items'][$key] = array(
                            'name' => $product_name,
                            'description' => html_entity_decode($description, ENT_NOQUOTES, 'UTF-8'),
                            'sku' => $order_items['sku'],
                            'category' => $order_items['category'],
                            'quantity' => $order_items['quantity'],
                            'unit_amount' =>
                            array(
                                'currency_code' => get_woocommerce_currency(),
                                'value' => $order_items['amount'],
                            ),
                        );
                    }
                }
            }
            if (( $old_wc && ( $order->shipping_address_1 || $order->shipping_address_2 ) ) || (!$old_wc && $order->has_shipping_address() )) {
                $shipping_first_name = $old_wc ? $order->shipping_first_name : $order->get_shipping_first_name();
                $shipping_last_name = $old_wc ? $order->shipping_last_name : $order->get_shipping_last_name();
                $shipping_address_1 = $old_wc ? $order->shipping_address_1 : $order->get_shipping_address_1();
                $shipping_address_2 = $old_wc ? $order->shipping_address_2 : $order->get_shipping_address_2();
                $shipping_city = $old_wc ? $order->shipping_city : $order->get_shipping_city();
                $shipping_state = $old_wc ? $order->shipping_state : $order->get_shipping_state();
                $shipping_postcode = $old_wc ? $order->shipping_postcode : $order->get_shipping_postcode();
                $shipping_country = $old_wc ? $order->shipping_country : $order->get_shipping_country();
            } else {
                $shipping_first_name = $old_wc ? $order->billing_first_name : $order->get_billing_first_name();
                $shipping_last_name = $old_wc ? $order->billing_last_name : $order->get_billing_last_name();
                $shipping_address_1 = $old_wc ? $order->billing_address_1 : $order->get_billing_address_1();
                $shipping_address_2 = $old_wc ? $order->billing_address_2 : $order->get_billing_address_2();
                $shipping_city = $old_wc ? $order->billing_city : $order->get_billing_city();
                $shipping_state = $old_wc ? $order->billing_state : $order->get_billing_state();
                $shipping_postcode = $old_wc ? $order->billing_postcode : $order->get_billing_postcode();
                $shipping_country = $old_wc ? $order->billing_country : $order->get_billing_country();
            }
            if ($order->needs_shipping_address() || WC()->cart->needs_shipping_address()) {
                if (!empty($shipping_first_name) && !empty($shipping_last_name)) {
                    $purchase_units['shipping']['name']['full_name'] = $shipping_first_name . ' ' . $shipping_last_name;
                }
                $purchase_units['shipping']['address'] = array(
                    'address_line_1' => $shipping_address_1,
                    'address_line_2' => $shipping_address_2,
                    'admin_area_2' => $shipping_city,
                    'admin_area_1' => $shipping_state,
                    'postal_code' => $shipping_postcode,
                    'country_code' => $shipping_country,
                );
            }
            $body_request = cpp_remove_empty_key($purchase_units);
            $patch_request[] = array(
                'op' => 'replace',
                'path' => "/purchase_units/@reference_id=='$reference_id'",
                'value' => $body_request
            );
            $patch_request_json = json_encode($patch_request);
            $paypal_order_id = cpp_get_session('cpp_paypal_order_id');
            $this->cpp_add_log_details('Update order');
            $this->cpp_log('Endpoint: ' . $this->paypal_order_api . $paypal_order_id);
            $this->cpp_log('Request: ' . print_r($patch_request, true));
            $response = wp_remote_request($this->paypal_order_api . $paypal_order_id, array(
                'timeout' => 60,
                'method' => 'PATCH',
                'redirection' => 5,
                'httpversion' => '1.1',
                'blocking' => true,
                'headers' => array('Content-Type' => 'application/json', 'Authorization' => "Basic " . $this->basicAuth, "prefer" => "return=representation", 'PayPal-Partner-Attribution-Id' => 'MBJTechnolabs_SI_SPB', 'PayPal-Request-Id' => $this->generate_request_id()),
                'body' => $patch_request_json,
                'cookies' => array()
                    )
            );
            if (is_wp_error($response)) {
                $error_message = $response->get_error_message();
                $this->cpp_log('Error Message : ' . wc_print_r($response, true));
                wc_add_notice($error_message, 'error');
                return false;
            } else {
                $api_response = json_decode(wp_remote_retrieve_body($response), true);
                $this->cpp_log('Response Code: ' . wp_remote_retrieve_response_code($response));
                $this->cpp_log('Response Message: ' . wp_remote_retrieve_response_message($response));
                $this->cpp_log('Response Body: ' . wc_print_r($api_response, true));
            }
        } catch (Exception $ex) {
            
        }
    }

    public function cpp_show_details_authorized_payment($authorization_id) {
        try {
            $this->cpp_add_log_details('Show details for authorized payment');
            $this->cpp_log('Endpoint: ' . $this->auth . $authorization_id);
            $response = wp_remote_get($this->auth . $authorization_id, array(
                'timeout' => 60,
                'redirection' => 5,
                'httpversion' => '1.1',
                'blocking' => true,
                'headers' => array('Content-Type' => 'application/json', 'Authorization' => "Basic " . $this->basicAuth, "prefer" => "return=representation", 'PayPal-Partner-Attribution-Id' => 'MBJTechnolabs_SI_SPB'),
                'body' => array(),
                'cookies' => array()
                    )
            );
            if (is_wp_error($response)) {
                $error_message = $response->get_error_message();
                $this->cpp_log('Error Message : ' . wc_print_r($error_message, true));
            } else {
                $api_response = json_decode(wp_remote_retrieve_body($response));
                $this->cpp_log('Response Code: ' . wp_remote_retrieve_response_code($response));
                $this->cpp_log('Response Message: ' . wp_remote_retrieve_response_message($response));
                $this->cpp_log('Response Body: ' . wc_print_r($api_response, true));
                cpp_set_session('cpp_paypal_transaction_details', $api_response);
                return $api_response;
            }
        } catch (Exception $ex) {
            
        }
    }

    public function cpp_capture_authorized_payment($woo_order_id) {
        try {
            $order = wc_get_order($woo_order_id);
            if ($order === false) {
                return false;
            }
            $capture_arg = array(
                'amount' =>
                array(
                    'value' => cpp_ppcp_round($order->get_total(), $this->decimals),
                    'currency_code' => version_compare(WC_VERSION, '3.0', '<') ? $order->get_order_currency() : $order->get_currency(),
                ),
                'invoice_id' => $this->invoice_prefix . str_replace("#", "", $order->get_order_number()),
                'final_capture' => true,
            );
            $body_request = cpp_remove_empty_key($capture_arg);
            $body_request = json_encode($body_request);
            $authorization_id = cpp_get_post_meta($order, '_auth_transaction_id');
            $this->cpp_add_log_details('Capture authorized payment');
            $this->cpp_log('Request : ' . wc_print_r($this->auth . $authorization_id . '/capture', true));
            $response = wp_remote_post($this->auth . $authorization_id . '/capture', array(
                'timeout' => 60,
                'redirection' => 5,
                'httpversion' => '1.1',
                'blocking' => true,
                'headers' => array('Content-Type' => 'application/json', 'Authorization' => "Basic " . $this->basicAuth, "prefer" => "return=representation", 'PayPal-Partner-Attribution-Id' => 'MBJTechnolabs_SI_SPB', 'PayPal-Request-Id' => $this->generate_request_id()),
                'body' => $body_request,
                'cookies' => array()
                    )
            );
            if (is_wp_error($response)) {
                $error_message = $response->get_error_message();
                $this->cpp_log('Error Message : ' . wc_print_r($error_message, true));
                wc_add_notice($error_message, 'error');
                return false;
            } else {
                $return_response = array();
                $api_response = json_decode(wp_remote_retrieve_body($response), true);
                $this->cpp_log('Response : ' . wc_print_r($api_response, true));
                if (!empty($api_response['id'])) {
                    $return_response['paypal_order_id'] = $api_response['id'];
                    cpp_update_post_meta($order, '_paypal_order_id', $api_response['id']);
                    $payment_source = isset($api_response['payment_source']) ? $api_response['payment_source'] : '';
                    if (!empty($payment_source['card'])) {
                        $card_response_order_note = __('Card Details', 'complete-paypal-payments-for-woocommerce');
                        $card_response_order_note .= "\n";
                        $card_response_order_note .= 'Last digits : ' . $payment_source['card']['last_digits'];
                        $card_response_order_note .= "\n";
                        $card_response_order_note .= 'Brand : ' . $payment_source['card']['brand'];
                        $card_response_order_note .= "\n";
                        $card_response_order_note .= 'Card type : ' . $payment_source['card']['type'];
                        $order->add_order_note($card_response_order_note);
                    }
                    $processor_response = isset($api_response['purchase_units']['0']['payments']['captures']['0']['processor_response']) ? $api_response['purchase_units']['0']['payments']['captures']['0']['processor_response'] : '';
                    if (!empty($processor_response['avs_code'])) {
                        $avs_response_order_note = __('Address Verification Result', 'complete-paypal-payments-for-woocommerce');
                        $avs_response_order_note .= "\n";
                        $avs_response_order_note .= $processor_response['avs_code'];
                        if (isset($this->AVSCodes[$processor_response['avs_code']])) {
                            $avs_response_order_note .= ' : ' . $this->AVSCodes[$processor_response['avs_code']];
                        }
                        $order->add_order_note($avs_response_order_note);
                    }
                    if (!empty($processor_response['cvv_code'])) {
                        $cvv2_response_code = __('Card Security Code Result', 'complete-paypal-payments-for-woocommerce');
                        $cvv2_response_code .= "\n";
                        $cvv2_response_code .= $processor_response['cvv_code'];
                        if (isset($this->CVV2Codes[$processor_response['cvv_code']])) {
                            $cvv2_response_code .= ' : ' . $this->CVV2Codes[$processor_response['cvv_code']];
                        }
                        $order->add_order_note($cvv2_response_code);
                    }
                    $currency_code = isset($api_response['seller_receivable_breakdown']['paypal_fee']['currency_code']) ? $api_response['seller_receivable_breakdown']['paypal_fee']['currency_code'] : '';
                    $value = isset($api_response['seller_receivable_breakdown']['paypal_fee']['value']) ? $api_response['seller_receivable_breakdown']['paypal_fee']['value'] : '';
                    cpp_update_post_meta($order, '_paypal_fee', $value);
                    cpp_update_post_meta($order, '_paypal_fee_currency_code', $currency_code);
                    $transaction_id = isset($api_response['id']) ? $api_response['id'] : '';
                    $seller_protection = isset($api_response['seller_protection']['status']) ? $api_response['seller_protection']['status'] : '';
                    $payment_status = isset($api_response['status']) ? $api_response['status'] : '';
                    cpp_update_post_meta($order, '_paypal_fee', $value);
                    cpp_update_post_meta($order, '_payment_status', $payment_status);
                    $order->add_order_note(sprintf(__('%s Transaction ID: %s', 'complete-paypal-payments-for-woocommerce'), $order->get_payment_method_title(), $transaction_id));
                    $order->add_order_note('Seller Protection Status: ' . cpp_readable($seller_protection));
                    if ($payment_status === 'COMPLETED') {
                        $order->payment_complete($transaction_id);
                        $order->add_order_note(sprintf(__('Payment via %s : %s.', 'complete-paypal-payments-for-woocommerce'), $order->get_payment_method_title(), ucfirst(strtolower($payment_status))));
                    } else {
                        $payment_status_reason = isset($api_response['purchase_units']['0']['payments']['captures']['0']['status_details']['reason']) ? $api_response['purchase_units']['0']['payments']['captures']['0']['status_details']['reason'] : '';
                        cpp_update_woo_order_status($woo_order_id, $payment_status, $payment_status_reason);
                    }
                    update_post_meta($woo_order_id, '_transaction_id', $transaction_id);
                    cpp_update_post_meta($order, '_transaction_id', $transaction_id);
                    return true;
                } else {
                    $error_message = $this->cpp_get_readable_message($api_response);
                    wc_add_notice($error_message, 'error');
                    return false;
                }
            }
        } catch (Exception $ex) {
            
        }
    }

    public function cpp_add_log_details($action_name = null) {
        $this->cpp_log(sprintf(__('Complete PayPal Payments For WooCommerce Version: %s', 'complete-paypal-payments-for-woocommerce'), COMPLETE_PAYPAL_PAYMENTS_FOR_WOOCOMMERCE_VERSION));
        $this->cpp_log(sprintf(__('WooCommerce Version: %s', 'complete-paypal-payments-for-woocommerce'), WC_VERSION));
        $mode = $this->is_sandbox ? 'Yes' : 'No';
        $this->cpp_log("Test Mode: " . $mode);
        $this->cpp_log('Action Name : ' . $action_name);
    }

    public function cpp_get_readable_message($error) {
        $message = '';
        if (isset($error['name'])) {
            switch ($error['name']) {
                case 'VALIDATION_ERROR':
                    foreach ($error['details'] as $e) {
                        $message .= "\t" . $e['field'] . "\n\t" . $e['issue'] . "\n\n";
                    }
                    break;
                case 'INVALID_REQUEST':
                    foreach ($error['details'] as $e) {
                        if (isset($e['field']) && isset($e['description'])) {
                            $message .= "\t" . $e['field'] . "\n\t" . $e['description'] . "\n\n";
                        } elseif (isset($e['issue'])) {
                            $message .= "\t" . $e['issue'] . "n\n";
                        }
                    }
                    break;
                case 'BUSINESS_ERROR':
                    $message .= $error['message'];
                    break;
                case 'UNPROCESSABLE_ENTITY' :
                    foreach ($error['details'] as $e) {
                        $message .= "\t" . $e['issue'] . ": " . $e['description'] . "\n\n";
                    }
                    break;
            }
        }
        if (!empty($message)) {
            
        } else if (!empty($error['message'])) {
            $message = $error['message'];
        } else if (!empty($error['error_description'])) {
            $message = $error['error_description'];
        } else {
            $message = $error;
        }
        return $message;
    }

    public function cpp_create_webhooks_request() {
        try {
            set_transient('cpp_is_webhook_process_started', 'done', 3 * HOUR_IN_SECONDS);
            $webhook_request = array();
            $webhook_request['url'] = add_query_arg(array('cpp_action' => 'webhook_handler', 'utm_nooverride' => '1'), WC()->api_request_url('Complete_PayPal_Payments_WooCommerce_Button_Manager'));
            $webhook_request['event_types'][] = array('name' => 'CHECKOUT.ORDER.APPROVED');
            $webhook_request['event_types'][] = array('name' => 'PAYMENT.AUTHORIZATION.CREATED');
            $webhook_request['event_types'][] = array('name' => 'PAYMENT.AUTHORIZATION.VOIDED');
            $webhook_request['event_types'][] = array('name' => 'PAYMENT.CAPTURE.COMPLETED');
            $webhook_request['event_types'][] = array('name' => 'PAYMENT.CAPTURE.DENIED');
            $webhook_request['event_types'][] = array('name' => 'PAYMENT.CAPTURE.PENDING');
            $webhook_request['event_types'][] = array('name' => 'PAYMENT.CAPTURE.REFUNDED');
            $webhook_request = cpp_remove_empty_key($webhook_request);
            $webhook_request = json_encode($webhook_request);
            $response = wp_remote_post($this->webhook, array(
                'method' => 'POST',
                'timeout' => 60,
                'redirection' => 5,
                'httpversion' => '1.1',
                'blocking' => true,
                'headers' => array('Content-Type' => 'application/json', 'Authorization' => "Basic " . $this->basicAuth, "prefer" => "return=representation", 'PayPal-Partner-Attribution-Id' => 'MBJTechnolabs_SI_SPB', 'PayPal-Request-Id' => $this->generate_request_id()),
                'body' => $webhook_request,
                'cookies' => array()
                    )
            );
            if (is_wp_error($response)) {
                delete_transient('cpp_is_webhook_process_started');
                $error_message = $response->get_error_message();
                $this->cpp_log('Error Message : ' . wc_print_r($error_message, true));
            } else {
                ob_start();
                $return_response = array();
                $api_response = json_decode(wp_remote_retrieve_body($response), true);
                $this->cpp_log('function called: cpp_create_webhooks_request');
                if (!empty($api_response['id'])) {
                    $this->cpp_log('Response Code: ' . wp_remote_retrieve_response_code($response));
                    $this->cpp_log('Response Message: ' . wp_remote_retrieve_response_message($response));
                    $this->cpp_log('Response Body: ' . wc_print_r($api_response, true));
                    update_option($this->webhook_id, $api_response['id']);
                } else {
                    $this->cpp_log('Response Body: ' . wc_print_r($api_response, true));
                    $error = $this->cpp_get_readable_message($api_response);
                    $this->cpp_log('Response Message: ' . wc_print_r($error, true));
                    if (isset($api_response['name']) && strpos($api_response['name'], 'WEBHOOK_NUMBER_LIMIT_EXCEEDED') !== false) {
                        $this->cpp_delete_first_webhook();
                        delete_transient('cpp_is_webhook_process_started');
                    } elseif ($api_response['name'] && strpos($api_response['name'], 'WEBHOOK_URL_ALREADY_EXISTS') !== false) {
                        $this->cpp_delete_exiting_webhook();
                        delete_transient('cpp_is_webhook_process_started');
                    } elseif ($api_response['details']['0']['field'] !== 'url') {
                        delete_transient('cpp_is_webhook_process_started');
                    }
                }
            }
        } catch (Exception $ex) {
            
        }
    }

    public function cpp_delete_exiting_webhook() {
        try {
            $response = wp_remote_get($this->webhook, array('headers' => array('Content-Type' => 'application/json', 'Authorization' => "Basic " . $this->basicAuth, "prefer" => "return=representation", 'PayPal-Partner-Attribution-Id' => 'MBJTechnolabs_SI_SPB')));
            $api_response = json_decode(wp_remote_retrieve_body($response), true);
            if (!empty($api_response['webhooks'])) {
                foreach ($api_response['webhooks'] as $key => $webhooks) {
                    if (isset($webhooks['url']) && strpos($webhooks['url'], site_url()) !== false) {
                        $response = wp_remote_request($this->webhook . '/' . $webhooks['id'], array(
                            'timeout' => 60,
                            'method' => 'DELETE',
                            'redirection' => 5,
                            'httpversion' => '1.1',
                            'blocking' => true,
                            'headers' => array('Content-Type' => 'application/json', 'Authorization' => "Basic " . $this->basicAuth, "prefer" => "return=representation", 'PayPal-Partner-Attribution-Id' => 'MBJTechnolabs_SI_SPB', 'PayPal-Request-Id' => $this->generate_request_id()),
                            'cookies' => array()
                                )
                        );
                        $this->cpp_log('Response Code: ' . wp_remote_retrieve_response_code($response));
                        $this->cpp_log('Response Message: ' . wp_remote_retrieve_response_message($response));
                        $this->cpp_log('Response Body: ' . wc_print_r($api_response, true));
                    }
                }
            }
        } catch (Exception $ex) {
            
        }
    }

    public function cpp_delete_first_webhook() {
        try {
            $response = wp_remote_get($this->webhook, array('headers' => array('Content-Type' => 'application/json', 'Authorization' => "Basic " . $this->basicAuth, "prefer" => "return=representation", 'PayPal-Partner-Attribution-Id' => 'MBJTechnolabs_SI_SPB')));
            $api_response = json_decode(wp_remote_retrieve_body($response), true);
            if (!empty($api_response['webhooks'])) {
                foreach ($api_response['webhooks'] as $key => $webhooks) {
                    $response = wp_remote_request($this->webhook . $webhooks['id'], array(
                        'timeout' => 60,
                        'method' => 'DELETE',
                        'redirection' => 5,
                        'httpversion' => '1.1',
                        'blocking' => true,
                        'headers' => array('Content-Type' => 'application/json', 'Authorization' => "Basic " . $this->basicAuth, "prefer" => "return=representation", 'PayPal-Partner-Attribution-Id' => 'MBJTechnolabs_SI_SPB', 'PayPal-Request-Id' => $this->generate_request_id()),
                        'cookies' => array()
                            )
                    );
                    $this->cpp_log('Response Code: ' . wp_remote_retrieve_response_code($response));
                    $this->cpp_log('Response Message: ' . wp_remote_retrieve_response_message($response));
                    $this->cpp_log('Response Body: ' . wc_print_r($api_response, true));
                    return false;
                }
            }
        } catch (Exception $ex) {
            
        }
    }

    public function cpp_handle_webhook_request_handler() {
        try {
            $bool = false;
            if ($this->is_valid_for_use() === true) {
                $posted_raw = cpp_get_raw_data();
                if (empty($posted_raw)) {
                    return false;
                }
                $headers = $this->getallheaders_value();
                $headers = array_change_key_case($headers, CASE_UPPER);
                $posted = json_decode($posted_raw, true);
                $this->cpp_log('Response Body: ' . wc_print_r($posted, true));
                $this->cpp_log('Headers: ' . wc_print_r($headers, true));
                $bool = $this->cpp_validate_webhook_event($headers, $posted);
                if ($bool) {
                    $this->cpp_update_order_status($posted);
                }
            }
        } catch (Exception $ex) {
            
        }
    }

    public function getallheaders_value() {
        try {
            if (!function_exists('getallheaders')) {
                return $this->getallheaders_custome();
            } else {
                return getallheaders();
            }
        } catch (Exception $ex) {
            
        }
    }

    public function getallheaders_custome() {
        try {
            $headers = [];
            foreach ($_SERVER as $name => $value) {
                if (substr($name, 0, 5) == 'HTTP_') {
                    $headers[str_replace(' ', '-', ucwords(strtolower(str_replace('_', ' ', substr($name, 5)))))] = $value;
                }
            }
            return $headers;
        } catch (Exception $ex) {
            
        }
    }

    public function cpp_validate_webhook_event($headers, $body) {
        try {
            $this->cpp_prepare_webhook_validate_request($headers, $body);
            if (!empty($this->request)) {
                $response = wp_remote_post($this->webhook_url, array(
                    'method' => 'POST',
                    'timeout' => 60,
                    'redirection' => 5,
                    'httpversion' => '1.1',
                    'blocking' => true,
                    'headers' => array('Content-Type' => 'application/json', 'Authorization' => "Basic " . $this->basicAuth, "prefer" => "return=representation", 'PayPal-Partner-Attribution-Id' => 'MBJTechnolabs_SI_SPB', 'PayPal-Request-Id' => $this->generate_request_id()),
                    'body' => json_encode($this->request),
                    'cookies' => array()
                        )
                );
            } else {
                return false;
            }
            if (is_wp_error($response)) {
                $error_message = $response->get_error_message();
                $this->cpp_log('Webhook Error Message : ' . wc_print_r($error_message, true));
                return false;
            } else {
                $return_response = array();
                $api_response = json_decode(wp_remote_retrieve_body($response), true);
                $this->cpp_log('Request Body: ' . wc_print_r($this->request, true));
                $this->cpp_log('Response Body: ' . wc_print_r($api_response, true));
                if (!empty($api_response['verification_status']) && 'SUCCESS' === $api_response['verification_status']) {
                    $this->cpp_log('Response Code: ' . wp_remote_retrieve_response_code($response));
                    $this->cpp_log('Response Message: ' . wp_remote_retrieve_response_message($response));
                    return true;
                } else {
                    $this->cpp_log('Response Code: ' . wp_remote_retrieve_response_code($response));
                    $this->cpp_log('Response Message: ' . wp_remote_retrieve_response_message($response));
                    return false;
                }
            }
        } catch (Exception $ex) {
            return false;
        }
    }

    public function cpp_prepare_webhook_validate_request($headers, $body) {
        try {
            $this->request = array();
            $webhook_id = get_option($this->webhook_id, false);
            $this->request['transmission_id'] = $headers['PAYPAL-TRANSMISSION-ID'];
            $this->request['transmission_time'] = $headers['PAYPAL-TRANSMISSION-TIME'];
            $this->request['cert_url'] = $headers['PAYPAL-CERT-URL'];
            $this->request['auth_algo'] = $headers['PAYPAL-AUTH-ALGO'];
            $this->request['transmission_sig'] = $headers['PAYPAL-TRANSMISSION-SIG'];
            $this->request['webhook_id'] = $webhook_id;
            $this->request['webhook_event'] = $body;
        } catch (Exception $ex) {
            
        }
    }

    public function cpp_update_order_status($posted) {
        $order = false;
        if (!empty($posted['resource']['purchase_units'][0]['custom_id'])) {
            $order = $this->cpp_get_paypal_order($posted['resource']['purchase_units'][0]['custom_id']);
        } elseif (!empty($posted['resource']['custom_id'])) {
            $order = $this->cpp_get_paypal_order($posted['resource']['custom_id']);
        }
        if ($order && isset($posted['event_type']) && !empty($posted['event_type'])) {
            $order->add_order_note('Webhooks Update : ' . $posted['summary']);
            if (isset($posted['resource']['status']) && !empty($posted['resource']['status'])) {
                $this->cpp_log('Payment status: ' . $posted['resource']['status']);
            }
            if (isset($posted['resource']['id']) && !empty($posted['resource']['id'])) {
                $this->cpp_log('PayPal Transaction ID: ' . $posted['resource']['id']);
            }
            if (isset($posted['resource']['status']) && isset($posted['resource']['id'])) {
                switch ($posted['event_type']) {
                    case 'PAYMENT.AUTHORIZATION.CREATED' :
                        $this->payment_status_on_hold($order, $posted);
                        break;
                    case 'PAYMENT.AUTHORIZATION.VOIDED' :
                        $this->payment_status_voided($order, $posted);
                        break;
                    case 'PAYMENT.CAPTURE.COMPLETED' :
                        $this->payment_status_completed($order, $posted);
                        break;
                    case 'PAYMENT.CAPTURE.DENIED' :
                        $this->payment_status_denied($order, $posted);
                        break;
                    case 'PAYMENT.CAPTURE.PENDING' :
                        $this->payment_status_on_hold($order, $posted);
                        break;
                    case 'PAYMENT.CAPTURE.REFUNDED' :
                        $this->payment_status_refunded($order, $posted);
                        break;
                }
            }
        }
    }

    public function payment_status_completed($order, $posted) {
        if ($order->has_status(wc_get_is_paid_statuses())) {
            $this->cpp_log('Aborting, Order #' . $order->get_id() . ' is already complete.');
            exit;
        }
        $this->save_paypal_meta_data($order, $posted);
        if ('COMPLETED' === $posted['resource']['status']) {
            $this->payment_complete($order);
        } else {
            if ('created' === $posted['resource']['status']) {
                $this->payment_on_hold($order, __('Payment authorized. Change payment status to processing or complete to capture funds.', 'complete-paypal-payments-for-woocommerce'));
            } else {
                if (!empty($posted['pending_reason'])) {
                    $this->payment_on_hold($order, sprintf(__('Payment pending (%s).', 'complete-paypal-payments-for-woocommerce'), $posted['pending_reason']));
                }
            }
        }
    }

    public function payment_complete($order, $txn_id = '', $note = '') {
        if (!$order->has_status(array('processing', 'completed'))) {
            if(!empty($note)) {
                $order->add_order_note($note);
            }
            $order->payment_complete($txn_id);
            WC()->cart->empty_cart();
        }
    }

    public function payment_on_hold($order, $reason = '') {
        if (!$order->has_status(array('processing', 'completed', 'refunded'))) {
            $order->update_status('on-hold', $reason);
        }
    }

    public function payment_status_pending($order, $posted) {
        if (!$order->has_status(array('processing', 'completed', 'refunded'))) {
            $this->payment_status_completed($order, $posted);
        }
    }

    public function payment_status_failed($order) {
        if (!$order->has_status(array('failed'))) {
            $order->update_status('failed');
        }
    }

    public function payment_status_denied($order) {
        $this->payment_status_failed($order);
    }

    public function payment_status_expired($order) {
        $this->payment_status_failed($order);
    }

    public function payment_status_voided($order) {
        $this->payment_status_failed($order);
    }

    public function payment_status_refunded($order) {
        if (!$order->has_status(array('refunded'))) {
            $order->update_status('refunded');
        }
    }

    public function payment_status_on_hold($order) {
        if ($order->has_status(array('pending'))) {
            $order->update_status('on-hold');
        }
    }

    public function save_paypal_meta_data($order, $posted) {
        if (!empty($posted['resource']['id'])) {
            update_post_meta($order->get_id(), '_transaction_id', wc_clean($posted['resource']['id']));
        }
        if (!empty($posted['resource']['status'])) {
            update_post_meta($order->get_id(), '_paypal_status', wc_clean($posted['resource']['status']));
        }
    }

    public function cpp_get_paypal_order($raw_custom) {
        $custom = json_decode($raw_custom);
        if ($custom && is_object($custom)) {
            $order_id = $custom->order_id;
            $order_key = $custom->order_key;
        } else {
            $this->cpp_log('Order ID and key were not found in "custom_id".');
            return false;
        }
        $order = wc_get_order($order_id);
        if (!$order) {
            $order_id = wc_get_order_id_by_order_key($order_key);
            $order = wc_get_order($order_id);
        }
        if (!$order || !hash_equals($order->get_order_key(), $order_key)) {
            $this->cpp_log('Order Keys do not match.');
            return false;
        }
        $this->cpp_log('Order  match : ' . $order_id);

        return $order;
    }

    public function generate_request_id() {
        static $pid = -1;
        static $addr = -1;

        if ($pid == -1) {
            $pid = getmypid();
        }

        if ($addr == -1) {
            if (array_key_exists('SERVER_ADDR', $_SERVER)) {
                $addr = ip2long(wc_clean(wp_unslash($_SERVER['SERVER_ADDR'])));
            } else {
                $addr = php_uname('n');
            }
        }

        return $addr . $pid . wc_clean(wp_unslash($_SERVER['REQUEST_TIME'])) . mt_rand(0, 0xffff);
    }

    public function cpp_set_payer_details($woo_order_id, $body_request) {
        if ($woo_order_id != null) {
            $order = wc_get_order($woo_order_id);
            $old_wc = version_compare(WC_VERSION, '3.0', '<');
            $first_name = $old_wc ? $order->billing_first_name : $order->get_billing_first_name();
            $last_name = $old_wc ? $order->billing_last_name : $order->get_billing_last_name();
            $billing_email = version_compare(WC_VERSION, '3.0', '<') ? $order->billing_email : $order->get_billing_email();
            $billing_phone = version_compare(WC_VERSION, '3.0', '<') ? $order->billing_phone : $order->get_billing_phone();
            if (!empty($billing_email)) {
                $body_request['payer']['email_address'] = $billing_email;
            }
            if (!empty($billing_phone)) {
                $body_request['payer']['phone']['phone_number']['national_number'] = preg_replace('/[^0-9]/', '', $billing_phone);
            }
            if (!empty($first_name)) {
                $body_request['payer']['name']['given_name'] = $first_name;
            }
            if (!empty($last_name)) {
                $body_request['payer']['name']['surname'] = $last_name;
            }
            $address_1 = $old_wc ? $order->billing_address_1 : $order->get_billing_address_1();
            $address_2 = $old_wc ? $order->billing_address_2 : $order->get_billing_address_2();
            $city = $old_wc ? $order->billing_city : $order->get_billing_city();
            $state = $old_wc ? $order->billing_state : $order->get_billing_state();
            $postcode = $old_wc ? $order->billing_postcode : $order->get_billing_postcode();
            $country = $old_wc ? $order->billing_country : $order->get_billing_country();
            if (!empty($address_1) && !empty($city) && !empty($state) && !empty($postcode) && !empty($country)) {
                $body_request['payer']['address'] = array(
                    'address_line_1' => $address_1,
                    'address_line_2' => $address_2,
                    'admin_area_2' => $city,
                    'admin_area_1' => $state,
                    'postal_code' => $postcode,
                    'country_code' => $country,
                );
            }
        } else {
            if (is_user_logged_in()) {
                $customer = WC()->customer;
                $old_wc = version_compare(WC_VERSION, '3.0', '<');
                $first_name = $old_wc ? $customer->billing_first_name : $customer->get_billing_first_name();
                $last_name = $old_wc ? $customer->billing_last_name : $customer->get_billing_last_name();
                $address_1 = $old_wc ? $customer->get_address() : $customer->get_billing_address_1();
                $address_2 = $old_wc ? $customer->get_address_2() : $customer->get_billing_address_2();
                $city = $old_wc ? $customer->get_city() : $customer->get_billing_city();
                $state = $old_wc ? $customer->get_state() : $customer->get_billing_state();
                $postcode = $old_wc ? $customer->get_postcode() : $customer->get_billing_postcode();
                $country = $old_wc ? $customer->get_country() : $customer->get_billing_country();
                $email_address = $old_wc ? WC()->customer->billing_email : WC()->customer->get_billing_email();
                $billing_phone = $old_wc ? $customer->billing_phone : $customer->get_billing_phone();
                if (!empty($first_name)) {
                    $body_request['payer']['name']['given_name'] = $first_name;
                }
                if (!empty($last_name)) {
                    $body_request['payer']['name']['surname'] = $last_name;
                }
                if (!empty($email_address)) {
                    $body_request['payer']['email_address'] = $email_address;
                }
                if (!empty($billing_phone)) {
                    $body_request['payer']['phone']['phone_number']['national_number'] = preg_replace('/[^0-9]/', '', $billing_phone);
                }
                if (!empty($address_1) && !empty($city) && !empty($state) && !empty($postcode) && !empty($country)) {
                    $body_request['payer']['address'] = array(
                        'address_line_1' => $address_1,
                        'address_line_2' => $address_2,
                        'admin_area_2' => $city,
                        'admin_area_1' => $state,
                        'postal_code' => $postcode,
                        'country_code' => $country,
                    );
                }
            }
        }
        return $body_request;
    }

    public function cpp_get_generate_token() {
        try {
            $args = array(
                'method' => 'POST',
                'timeout' => 60,
                'redirection' => 5,
                'httpversion' => '1.1',
                'blocking' => true,
                'headers' => array('Content-Type' => 'application/json', 'Authorization' => "Basic " . $this->basicAuth, 'Accept-Language' => 'en_US'),
                'cookies' => array(),
                'body' => array(),
            );
            /*if ($this->ppc_get_user_id_for_paypal_vault()) {
                $customer_id = $this->ppc_get_user_id_for_paypal_vault();
                $args['body'] = json_encode(array('customer_id' => $customer_id));
            }*/
            $paypal_api_response = wp_remote_get($this->generate_token_url, $args);
            $body = wp_remote_retrieve_body($paypal_api_response);
            $this->cpp_log('Response Body: ' . wc_print_r($body, true));
            $api_response = !empty($body) ? json_decode($body, true) : '';
            if (!empty($api_response['client_token'])) {
                return $api_response['client_token'];
            }
        } catch (Exception $ex) {
            $this->cpp_log("The exception was created on line: " . $ex->getLine(), 'error');
            $this->cpp_log($ex->getMessage(), 'error');
        }
    }

    public function ppc_get_user_id_for_paypal_vault() {
        if (is_user_logged_in()) {
            $current_user = wp_get_current_user();
            if (!get_user_meta($current_user->ID, 'ppc_paypal_customer_id')) {
                $customer_id = preg_replace("/[^A-Za-z0-9_-]/", '', $current_user->user_email . '-' . $current_user->ID);
                update_user_meta($current_user->ID, 'ppc_paypal_customer_id', $customer_id);
                return $customer_id;
            } else {
                return get_user_meta($current_user->ID, 'ppc_paypal_customer_id', true);
            }
        }
        return false;
    }

}
