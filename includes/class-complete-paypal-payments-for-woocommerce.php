<?php
defined('ABSPATH') || exit;

/**
 * @since      1.0.0
 * @package    Complete_PayPal_Payments_WooCommerce
 * @subpackage Complete_PayPal_Payments_WooCommerce/includes
 * @author     Eric Babin <completepaypalpayments@gmail.com>
 */
class Complete_PayPal_Payments_WooCommerce {

    protected $loader;
    protected $plugin_name;
    protected $version;
    public $button_manager;

    public function __construct() {
        if (defined('COMPLETE_PAYPAL_PAYMENTS_FOR_WOOCOMMERCE_VERSION')) {
            $this->version = COMPLETE_PAYPAL_PAYMENTS_FOR_WOOCOMMERCE_VERSION;
        } else {
            $this->version = '3.1.0';
        }
        $this->plugin_name = 'complete-paypal-payments-for-woocommerce';
        add_filter('woocommerce_payment_gateways', array($this, 'cpp_woocommerce_payment_gateways'), 99999999);
        add_action('admin_notices', array($this, 'cpp_admin_notice'));
        add_action('wp_ajax_ppc_admin_notice_action', array($this, 'ppc_admin_notice_action'), 10);
        add_action('wp_ajax_ppc_dismiss_notice', array($this, 'ppc_dismiss_notice'), 10);
        $prefix = is_network_admin() ? 'network_admin_' : '';
        add_filter("{$prefix}plugin_action_links_" . COMPLETE_PAYPAL_PAYMENTS_FOR_WOOCOMMERCE_BASENAME, array($this, 'cpp_plugin_action_links'), 10, 1);
        $this->load_dependencies();
        $this->set_locale();
        $this->define_public_hooks();
    }

    private function load_dependencies() {
        require_once plugin_dir_path(dirname(__FILE__)) . 'includes/complete-paypal-payments-for-woocommerce-function.php';
        require_once plugin_dir_path(dirname(__FILE__)) . 'includes/class-complete-paypal-payments-for-woocommerce-loader.php';
        require_once plugin_dir_path(dirname(__FILE__)) . 'includes/class-complete-paypal-payments-for-woocommerce-i18n.php';
        require_once plugin_dir_path(dirname(__FILE__)) . 'includes/complete-paypal-payments-for-woocommerce-function.php';
        require_once plugin_dir_path(dirname(__FILE__)) . 'public/class-complete-paypal-payments-for-woocommerce-button-manager.php';
        require_once plugin_dir_path(dirname(__FILE__)) . 'includes/class-complete-paypal-payments-for-woocommerce-product.php';
        require_once plugin_dir_path(dirname(__FILE__)) . 'includes/class-complete-paypal-payments-for-woocommerce-pay-later-messaging.php';
        $this->loader = new Complete_PayPal_Payments_WooCommerce_Loader();
    }

    private function set_locale() {
        $plugin_i18n = new Complete_PayPal_Payments_WooCommerce_i18n();
        $this->loader->add_action('init', $plugin_i18n, 'load_plugin_textdomain');
    }

    private function define_public_hooks() {
        $this->button_manager = Complete_PayPal_Payments_WooCommerce_Button_Manager::instance();
        Complete_PayPal_Payments_WooCommerce_Pay_Later::instance();
    }

    public function run() {
        $this->loader->run();
    }

    public function get_plugin_name() {
        return $this->plugin_name;
    }

    public function get_loader() {
        return $this->loader;
    }

    public function get_version() {
        return $this->version;
    }

    public function cpp_woocommerce_payment_gateways($methods) {
        include_once plugin_dir_path(dirname(__FILE__)) . 'includes/class-complete-paypal-payments-for-woocommerce-gateway.php';
        $methods[] = 'Complete_PayPal_Payments_WooCommerce_Gateway';
        return $methods;
    }

    public function cpp_plugin_action_links($actions) {
        $custom_actions = array(
            'configure' => sprintf('<a href="%s">%s</a>', admin_url('admin.php?page=wc-settings&tab=checkout&section=complete_paypal_payments'), __('Configure', 'complete-paypal-payments-for-woocommerce')),
            'support' => '<a href="' . esc_url('https://wordpress.org/support/plugin/complete-paypal-payments-for-woocommerce/') . '" aria-label="' . esc_attr__('Support', 'complete-paypal-payments-for-woocommerce') . '">' . esc_html__('Support', 'complete-paypal-payments-for-woocommerce') . '</a>',
            'reviews' => '<a href="' . esc_url('https://wordpress.org/support/plugin/complete-paypal-payments-for-woocommerce/reviews/#new-post') . '" aria-label="' . esc_attr__('Write a Review', 'complete-paypal-payments-for-woocommerce') . '">' . esc_html__('Write a Review', 'complete-paypal-payments-for-woocommerce') . '</a>',
        );
        return array_merge($custom_actions, $actions);
    }

    public function cpp_admin_notice() {
        global $current_user;
        $user_id = $current_user->ID;
        if (isset($_GET['tab']) && 'checkout' === $_GET['tab'] && isset($_GET['section']) && 'complete_paypal_payments' === $_GET['section']) {
            if (!get_user_meta($user_id, 'ppc_wp_billing_phone_notice')) {
                ?>
                <div class="ppc_wp_billing_phone_notice notice notice-warning is-dismissible">
                    <p><?php _e('<b>Enable Customer Phone Number: </b>', 'sample-text-domain'); ?>
                        <?php esc_html_e('If you require a billing phone number during checkout, we recommend you enable the contact phone number option in your PayPal account. When enabled, PayPal will provide the customer\'s phone number, which the plugin will use to populate the checkout page. It will improve conversion rates since the customer won\'t have to enter their phone number.', 'pymntp-paypal-woocommerce'); ?>
                <?php printf(__('%1$sEnable phone number instructions%2$s', ''), '<a target="_blank" href="https://wordpress.org/plugins/complete-paypal-payments-for-woocommerce/#how%20to%20get%20phone%20numbers%20for%20paypal%20checkout%20orders%3F">', '</a>') ?>
                    </p>
                </div>
                <?php
            }
        }
        if (false === ( $ppc_wp_review_request = get_transient('ppc_wp_review_request') ) && !get_user_meta($user_id, 'ppc_wp_review_request')) :
            ?>
            <div class="ppc_wp_review_request notice notice-info is-dismissible">
                <h3 style="margin: 10px 0;"><?php _e('Complete PayPal Payments For WooCommerce', 'sample-text-domain'); ?></h3>
                <p><?php esc_html_e('Hey, We would like to thank you for using our plugin. We would appreciate it if you could take a moment to drop a quick review that will inspire us to keep going.', 'pymntp-paypal-woocommerce'); ?></p>
                <p>
                    <a class="button button-secondary" style="color:#333; border-color:#ccc; background:#efefef;" data-type="later">Remind me later</a>
                    <a class="button button-primary" data-type="add_review">Review now</a>
                </p>
            </div>
        <?php endif; ?>
        <script type="text/javascript">
            (function ($) {
                "use strict";
                var data_obj = {
                    action: 'ppc_admin_notice_action',
                    ppc_admin_notice_action_type: ''
                };
                $(document).on('click', '.ppc_wp_review_request a.button', function (e) {
                    e.preventDefault();
                    var elm = $(this);
                    var btn_type = elm.attr('data-type');
                    if (btn_type == 'add_review') {
                        window.open('https://wordpress.org/support/plugin/complete-paypal-payments-for-woocommerce/reviews/');
                    }
                    elm.parents('.ppc_wp_review_request').hide();

                    data_obj['ppc_admin_notice_action_type'] = btn_type;
                    $.ajax({
                        url: ajaxurl,
                        data: data_obj,
                        type: 'POST'
                    });

                }).on('click', '.ppc_wp_review_request .notice-dismiss', function (e) {
                    e.preventDefault();
                    var elm = $(this);
                    elm.parents('.ppc_wp_review_request').hide();
                    data_obj['ppc_admin_notice_action_type'] = 'review_closed';
                    $.ajax({
                        url: ajaxurl,
                        data: data_obj,
                        type: 'POST'
                    });

                }).on('click', '.ppc_wp_billing_phone_notice .notice-dismiss', function (e) {
                    e.preventDefault();
                    var elm = $(this);
                    elm.parents('.ppc_wp_billing_phone_notice').hide();
                    data_obj['ppc_admin_notice_action_type'] = 'ppc_wp_billing_phone_notice';
                    $.ajax({
                        url: ajaxurl,
                        data: data_obj,
                        type: 'POST'
                    });

                });
            })(jQuery);
        </script>
        <?php
    }

    public function ppc_admin_notice_action() {
        if (isset($_POST['ppc_admin_notice_action_type']) && 'later' === $_POST['ppc_admin_notice_action_type']) {
            set_transient('ppc_wp_review_request', 'yes', MONTH_IN_SECONDS);
        } elseif (isset($_POST['ppc_admin_notice_action_type']) && 'add_review' === $_POST['ppc_admin_notice_action_type']) {
            global $current_user;
            $user_id = $current_user->ID;
            add_user_meta($user_id, 'ppc_wp_review_request', 'true', true);
        } elseif (isset($_POST['ppc_admin_notice_action_type']) && 'review_closed' === $_POST['ppc_admin_notice_action_type']) {
            set_transient('ppc_wp_review_request', 'yes', MONTH_IN_SECONDS);
        } elseif (isset($_POST['ppc_admin_notice_action_type']) && 'ppc_wp_billing_phone_notice' === $_POST['ppc_admin_notice_action_type']) {
            global $current_user;
            $user_id = $current_user->ID;
            add_user_meta($user_id, 'ppc_wp_billing_phone_notice', 'true', true);
        }
    }

    public function ppc_dismiss_notice() {
        global $current_user;
        $user_id = $current_user->ID;
        if (!empty($_POST['action']) && $_POST['action'] == 'ppc_dismiss_notice') {
            if (!empty($_POST['action']) && $_POST['action'] === 'ppc_dismiss_notice') {
                add_user_meta($user_id, $_POST['data'], 'true', true);
                wp_send_json_success();
            }
        }
    }

}
