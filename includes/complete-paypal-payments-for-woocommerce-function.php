<?php

if (!function_exists('cpp_remove_empty_key')) {

    function cpp_remove_empty_key($data) {
        $original = $data;
        $data = array_filter($data);
        $data = array_map(function ($e) {
            return is_array($e) ? cpp_remove_empty_key($e) : $e;
        }, $data);
        return $original === $data ? $data : cpp_remove_empty_key($data);
    }

}

if (!function_exists('cpp_set_session')) {

    function cpp_set_session($key, $value) {
        if (!class_exists('WooCommerce') || WC()->session == null) {
            return false;
        }
        $cpp_session = WC()->session->get('cpp_session');
        if (!is_array($cpp_session)) {
            $cpp_session = array();
        }
        $cpp_session[$key] = $value;
        WC()->session->set('cpp_session', $cpp_session);
    }

}

if (!function_exists('cpp_get_session')) {

    function cpp_get_session($key) {
        if (!class_exists('WooCommerce') || WC()->session == null) {
            return false;
        }

        $cpp_session = WC()->session->get('cpp_session');
        if (!empty($cpp_session[$key])) {
            return $cpp_session[$key];
        }
        return false;
    }

}
if (!function_exists('cpp_unset_session')) {

    function cpp_unset_session($key) {
        if (!class_exists('WooCommerce') || WC()->session == null) {
            return false;
        }
        $cpp_session = WC()->session->get('cpp_session');
        if (!empty($cpp_session[$key])) {
            unset($cpp_session[$key]);
            WC()->session->set('cpp_session', $cpp_session);
        }
    }

}
if (!function_exists('has_active_session')) {

    function has_active_session() {
        $checkout_details = cpp_get_session('cpp_paypal_transaction_details');
        $cpp_paypal_order_id = cpp_get_session('cpp_paypal_order_id');
        if (!empty($checkout_details) && !empty($cpp_paypal_order_id) && isset($_GET['paypal_order_id'])) {
            return true;
        }
        return false;
    }

}
if (!function_exists('cpp_update_post_meta')) {

    function cpp_update_post_meta($order, $key, $value) {
        if (!is_object($order)) {
            $order = wc_get_order($order);
        }
        $old_wc = version_compare(WC_VERSION, '3.0', '<');
        if ($old_wc) {
            update_post_meta($order->id, $key, $value);
        } else {
            $order->update_meta_data($key, $value);
        }
        if (!$old_wc) {
            $order->save_meta_data();
        }
    }

}
if (!function_exists('cpp_get_post_meta')) {

    function cpp_get_post_meta($order, $key, $bool = true) {
        $order_meta_value = false;
        if (!is_object($order)) {
            $order = wc_get_order($order);
        }
        $old_wc = version_compare(WC_VERSION, '3.0', '<');
        if ($old_wc) {
            $order_meta_value = get_post_meta($order->id, $key, $bool);
        } else {
            $order_meta_value = $order->get_meta($key, $bool);
        }
        return $order_meta_value;
    }

}
if (!function_exists('get_button_locale_code')) {

    function get_button_locale_code() {
        $_supportedLocale = array(
            'en_US', 'fr_XC', 'es_XC', 'zh_XC', 'en_AU', 'de_DE', 'nl_NL',
            'fr_FR', 'pt_BR', 'fr_CA', 'zh_CN', 'ru_RU', 'en_GB', 'zh_HK',
            'he_IL', 'it_IT', 'ja_JP', 'pl_PL', 'pt_PT', 'es_ES', 'sv_SE', 'zh_TW', 'tr_TR'
        );
        $wpml_locale = cpp_get_wpml_locale();
        if ($wpml_locale) {
            if (in_array($wpml_locale, $_supportedLocale)) {
                return $wpml_locale;
            }
        }
        $locale = get_locale();
        if (get_locale() != '') {
            $locale = substr(get_locale(), 0, 5);
        }
        if (!in_array($locale, $_supportedLocale)) {
            $locale = 'en_US';
        }
        return $locale;
    }

}

if (!function_exists('ppc_get_paypal_api_locale_code')) {

    function ppc_get_paypal_api_locale_code() {
        $locale = str_replace('_', '-', get_button_locale_code());

        if (preg_match('/^[a-z]{2}(?:-[A-Z][a-z]{3})?(?:-(?:[A-Z]{2}))?$/', $locale)) {
            return $locale;
        }

        $parts = explode('-', $locale);
        if (count($parts) === 3) {
            $ret = substr($locale, 0, strrpos($locale, '-'));
            if (false !== $ret) {
                return $ret;
            }
        }

        return 'en';
    }

}

if (!function_exists('cpp_get_wpml_locale')) {

    function cpp_get_wpml_locale() {
        $locale = false;
        if (defined('ICL_LANGUAGE_CODE') && function_exists('icl_object_id')) {
            global $sitepress;
            if (isset($sitepress)) {
                $locale = $sitepress->get_current_language();
            } else if (function_exists('pll_current_language')) {
                $locale = pll_current_language('locale');
            } else if (function_exists('pll_default_language')) {
                $locale = pll_default_language('locale');
            }
        }
        return $locale;
    }

}
if (!function_exists('cpp_is_local_server')) {

    function cpp_is_local_server() {
        if (!isset($_SERVER['HTTP_HOST'])) {
            return;
        }
        if ($_SERVER['HTTP_HOST'] === 'localhost' || substr($_SERVER['REMOTE_ADDR'], 0, 3) === '10.' || substr($_SERVER['REMOTE_ADDR'], 0, 7) === '192.168') {
            return true;
        }
        $live_sites = [
            'HTTP_CLIENT_IP',
            'HTTP_X_REAL_IP',
            'HTTP_X_FORWARDED_FOR',
            'HTTP_X_FORWARDED',
            'HTTP_X_CLUSTER_CLIENT_IP',
        ];
        foreach ($live_sites as $ip) {
            if (!empty($_SERVER[$ip])) {
                return false;
            }
        }
        if (in_array($_SERVER['REMOTE_ADDR'], array('127.0.0.1', '::1'))) {
            return true;
        }
        $fragments = explode('.', site_url());
        if (in_array(end($fragments), array('dev', 'local', 'localhost', 'test'))) {
            return true;
        }
        return false;
    }

}
if (!function_exists('cpp_readable')) {

    function cpp_readable($tex) {
        $tex = ucwords(strtolower(str_replace('_', ' ', $tex)));
        return $tex;
    }

}
if (!function_exists('cpp_is_advanced_cards_available')) {

    function cpp_is_advanced_cards_available() {
        try {
            $currency = get_woocommerce_currency();
            $country_state = wc_get_base_location();
            $available = array(
                'US' => array('AUD', 'CAD', 'EUR', 'GBP', 'JPY', 'USD'),
                'AU' => array('AUD', 'CAD', 'CHF', 'CZK', 'DKK', 'EUR', 'GBP', 'HKD', 'HUF', 'JPY', 'NOK', 'NZD', 'PLN', 'SEK', 'SGD', 'USD'),
                'GB' => array('AUD', 'CAD', 'CHF', 'CZK', 'DKK', 'EUR', 'GBP', 'HKD', 'HUF', 'JPY', 'NOK', 'NZD', 'PLN', 'SEK', 'SGD', 'USD'),
                'FR' => array('AUD', 'CAD', 'CHF', 'CZK', 'DKK', 'EUR', 'GBP', 'HKD', 'HUF', 'JPY', 'NOK', 'NZD', 'PLN', 'SEK', 'SGD', 'USD'),
                'IT' => array('AUD', 'CAD', 'CHF', 'CZK', 'DKK', 'EUR', 'GBP', 'HKD', 'HUF', 'JPY', 'NOK', 'NZD', 'PLN', 'SEK', 'SGD', 'USD'),
                'ES' => array('AUD', 'CAD', 'CHF', 'CZK', 'DKK', 'EUR', 'GBP', 'HKD', 'HUF', 'JPY', 'NOK', 'NZD', 'PLN', 'SEK', 'SGD', 'USD'),
                'DE' => array('AUD', 'CAD', 'CHF', 'CZK', 'DKK', 'EUR', 'GBP', 'HKD', 'HUF', 'JPY', 'NOK', 'NZD', 'PLN', 'SEK', 'SGD', 'USD',
                ),
            );
            if (isset($available[$country_state['country']]) && in_array($currency, $available[$country_state['country']])) {
                return true;
            }
            return false;
        } catch (Exception $ex) {
            return false;
        }
    }

}

if (!function_exists('cpp_get_raw_data')) {
    if (!function_exists('cpp_get_raw_data')) {

        function cpp_get_raw_data() {
            try {
                if (function_exists('phpversion') && version_compare(phpversion(), '5.6', '>=')) {
                    return file_get_contents('php://input');
                }
                global $HTTP_RAW_POST_DATA;
                if (!isset($HTTP_RAW_POST_DATA)) {
                    $HTTP_RAW_POST_DATA = file_get_contents('php://input');
                }
                return $HTTP_RAW_POST_DATA;
            } catch (Exception $ex) {
                
            }
        }

    }
}
if (!function_exists('cpp_key_generator')) {
    if (!function_exists('cpp_key_generator')) {

        function cpp_key_generator() {
            $key = md5(microtime());
            $new_key = '';
            for ($i = 1; $i <= 19; $i++) {
                $new_key .= $key[$i];
                if ($i % 5 == 0 && $i != 19)
                    $new_key .= '';
            }
            return strtoupper($new_key);
        }

    }
}
if (!function_exists('cpp_update_woo_order_status')) {

    function cpp_update_woo_order_status($orderid, $payment_status, $pending_reason) {
        try {
            if (empty($pending_reason)) {
                $pending_reason = $payment_status;
            }
            $order = wc_get_order($orderid);
            switch (strtoupper($payment_status)) :
                case 'DECLINED' :
                case 'PENDING' :
                    switch (strtoupper($pending_reason)) {
                        case 'BUYER_COMPLAINT':
                            $pending_reason_text = __('BUYER_COMPLAINT: The payer initiated a dispute for this captured payment with PayPal.', 'complete-paypal-payments-for-woocommerce');
                            break;
                        case 'CHARGEBACK':
                            $pending_reason_text = __('CHARGEBACK: The captured funds were reversed in response to the payer disputing this captured payment with the issuer of the financial instrument used to pay for this captured payment.', 'complete-paypal-payments-for-woocommerce');
                            break;
                        case 'ECHECK':
                            $pending_reason_text = __('ECHECK: The payer paid by an eCheck that has not yet cleared.', 'complete-paypal-payments-for-woocommerce');
                            break;
                        case 'INTERNATIONAL_WITHDRAWAL':
                            $pending_reason_text = __('INTERNATIONAL_WITHDRAWAL: Visit your online account. In your **Account Overview**, accept and deny this payment.', 'complete-paypal-payments-for-woocommerce');
                            break;
                        case 'OTHER':
                            $pending_reason_text = __('No additional specific reason can be provided. For more information about this captured payment, visit your account online or contact PayPal.', 'complete-paypal-payments-for-woocommerce');
                            break;
                        case 'PENDING_REVIEW':
                            $pending_reason_text = __('PENDING_REVIEW: The captured payment is pending manual review.', 'complete-paypal-payments-for-woocommerce');
                            break;
                        case 'RECEIVING_PREFERENCE_MANDATES_MANUAL_ACTION':
                            $pending_reason_text = __('RECEIVING_PREFERENCE_MANDATES_MANUAL_ACTION: The payee has not yet set up appropriate receiving preferences for their account. For more information about how to accept or deny this payment, visit your account online. This reason is typically offered in scenarios such as when the currency of the captured payment is different from the primary holding currency of the payee.', 'complete-paypal-payments-for-woocommerce');
                            break;
                        case 'REFUNDED':
                            $pending_reason_text = __('REFUNDED: The captured funds were refunded.', 'complete-paypal-payments-for-woocommerce');
                            break;
                        case 'TRANSACTION_APPROVED_AWAITING_FUNDING':
                            $pending_reason_text = __('TRANSACTION_APPROVED_AWAITING_FUNDING: The payer must send the funds for this captured payment. This code generally appears for manual EFTs.', 'complete-paypal-payments-for-woocommerce');
                            break;
                        case 'UNILATERAL':
                            $pending_reason_text = __('UNILATERAL: The payee does not have a PayPal account.', 'complete-paypal-payments-for-woocommerce');
                            break;
                        case 'VERIFICATION_REQUIRED':
                            $pending_reason_text = __('VERIFICATION_REQUIRED: The payee\'s PayPal account is not verified.', 'complete-paypal-payments-for-woocommerce');
                            break;
                        case 'none':
                        default:
                            $pending_reason_text = __('No pending reason provided.', 'complete-paypal-payments-for-woocommerce');
                            break;
                    }
                    if ($payment_status === 'PENDING') {
                        $order->update_status('on-hold', sprintf(__('Payment via %s Pending. PayPal Pending reason: %s.', 'complete-paypal-payments-for-woocommerce'), $order->get_payment_method_title(), $pending_reason_text));
                    }
                    if ($payment_status === 'DECLINED') {
                        $order->update_status('failed', sprintf(__('Payment via %s declined. PayPal declined reason: %s.', 'complete-paypal-payments-for-woocommerce'), $order->get_payment_method_title(), $pending_reason_text));
                    }
                    break;
                case 'PARTIALLY_REFUNDED' :
                    $order->update_status('on-hold');
                    $order->add_order_note(sprintf(__('Payment via %s partially refunded. PayPal reason: %s.', 'complete-paypal-payments-for-woocommerce'), $order->get_payment_method_title(), $pending_reason));
                case 'REFUNDED' :
                    $order->update_status('refunded');
                    $order->add_order_note(sprintf(__('Payment via %s refunded. PayPal reason: %s.', 'complete-paypal-payments-for-woocommerce'), $order->get_payment_method_title(), $pending_reason));
                case 'FAILED' :
                    $order->update_status('failed', sprintf(__('Payment via %s failed. PayPal reason: %s.', 'complete-paypal-payments-for-woocommerce'), $order->get_payment_method_title(), $pending_reason));
                    break;
                default:
                    break;
            endswitch;
            return;
        } catch (Exception $ex) {
            
        }
    }

}

if (!function_exists('cpp_ppcp_round')) {

    function cpp_ppcp_round($price, $precision) {
        $round_price = round($price, $precision);
        return number_format($round_price, $precision, '.', '');
    }

}