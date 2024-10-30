jQuery(function ($) {
    'use strict';
    $(function () {
        if (typeof cpp_manager === 'undefined') {
            return false;
        }
        var selector = cpp_manager.button_selector;
        if ($('.variations_form').length) {
            $('.variations_form').on('show_variation', function () {
                $(selector).show();
            }).on('hide_variation', function () {
                $(selector).hide();
            });
        }
        
        console.log(selector);
        
        $.cpp_scroll_to_notices = function () {
            var scrollElement = $('.woocommerce-NoticeGroup-updateOrderReview, .woocommerce-NoticeGroup-checkout');
            if (!scrollElement.length) {
                scrollElement = $('form.checkout');
            } 
            if(!scrollElement.length) {
                scrollElement = $('form#order_review');
            }
            if ( scrollElement.length ) {
			$( 'html, body' ).animate( {
				scrollTop: ( scrollElement.offset().top - 100 )
			}, 1000 );
		}
            
        };
        var showError = function (error_message) {
            $('.woocommerce-NoticeGroup-checkout, .woocommerce-error, .woocommerce-message').remove();
            $('.woocommerce').prepend('<div class="woocommerce-NoticeGroup woocommerce-NoticeGroup-checkout">' + error_message + '</div>');
            $('.woocommerce').removeClass('processing').unblock();
            $('.woocommerce').find('.input-text, select, input:checkbox').trigger('validate').trigger('blur');
            $.cpp_scroll_to_notices();
        };
        var is_from_checkout = 'checkout' === cpp_manager.page;
        var is_from_product = 'product' === cpp_manager.page;
        var is_sale = 'capture' === cpp_manager.paymentaction;
        var smart_button_render = function () {
            $.each(selector, function (key, ccp_button_selector) {
                if (!$(ccp_button_selector).length || $(ccp_button_selector).children().length) {
                    return;
                }
                if (typeof paypal === 'undefined') {
                    return false;
                }
                var cpp_style = {
                    layout: cpp_manager.style_layout,
                    color: cpp_manager.style_color,
                    shape: cpp_manager.style_shape,
                    label: cpp_manager.style_label
                };
                if (cpp_manager.style_layout !== 'vertical') {
                    cpp_style['tagline'] = (cpp_manager.style_tagline === 'yes') ? true : false;
                }
                paypal.Buttons({
                    style: cpp_style,
                    createOrder: function (data, actions) {
                        var data;
                        if (is_from_checkout) {
                            data = $(ccp_button_selector).closest('form').serialize();
                        } else if (is_from_product) {
                            var add_to_cart = $("[name='add-to-cart']").val();
                            $('<input>', {
                                type: 'hidden',
                                name: 'psb-add-to-cart',
                                value: add_to_cart
                            }).appendTo('form.cart');
                            data = $('form.cart').serialize();
                        } else {
                            data = $('form.woocommerce-cart-form').serialize();
                        }
                        return fetch(cpp_manager.create_order_url, {
                            method: 'POST',
                            headers: {
                                'Content-Type': 'application/x-www-form-urlencoded'
                            },
                            body: data
                        }).then(function (res) {
                            return res.json();
                        }).then(function (data) {
                            if (typeof data.success !== 'undefined') {
                                var messages = data.data.messages ? data.data.messages : data.data;
                                if ('string' === typeof messages) {
                                    showError('<ul class="woocommerce-error" role="alert">' + messages + '</ul>', $('form'));
                                } else {
                                    var messageItems = messages.map(function (message) {
                                        return '<li>' + message + '</li>';
                                    }).join('');
                                    showError('<ul class="woocommerce-error" role="alert">' + messageItems + '</ul>', $('form'));
                                }
                                return null;
                            } else {
                                return data.orderID;
                            }
                        });
                    },
                    onApprove: function (data, actions) {
                        $('.woocommerce').block({message: null, overlayCSS: {background: '#fff', opacity: 0.6}});
                        if (is_from_checkout) {
                            $.post(cpp_manager.cc_capture + "&paypal_order_id=" + data.orderID + "&woocommerce-process-checkout-nonce=" + cpp_manager.woocommerce_process_checkout, function (data) {
                                window.location.href = data.data.redirect;
                            });
                        } else {
                            actions.redirect(cpp_manager.checkout_url + '?paypal_order_id=' + data.orderID + '&paypal_payer_id=' + data.payerID + '&from=' + cpp_manager.page);
                        }
                    },
                    onCancel: function (data, actions) {
                        $('.woocommerce').unblock();
                    },
                    onError: function (err) {
                        console.log(err);
                        $('.woocommerce').unblock();
                    }
                }).render(ccp_button_selector);

            });



        };



        $('form.checkout').on('checkout_place_order_complete_paypal_payments', function (event) {
            if (is_cpp_selected()) {
                if (is_hosted_field_eligible() === true) {
                    event.preventDefault();
                    if ($('form.checkout').is('.paypal_cc_submiting')) {
                        return false;
                    } else {
                        $('form.checkout').addClass('paypal_cc_submiting');
                        $(document.body).trigger('submit_paypal_cc_form');
                    }
                    return false;
                }
            }
            return true;
        });
        var hosted_button_render = function () {
            if ($('form.checkout').is('.HostedFields')) {
                return false;
            }
            if (typeof paypal === 'undefined') {
                return false;
            }
            $('form.checkout').addClass('HostedFields');
            paypal.HostedFields.render({
                createOrder: function () {
                    if ($('form.checkout').is('.createOrder') === false) {
                        $('form.checkout').addClass('createOrder');
                        var data;
                        if (is_from_checkout) {
                            data = $('form.checkout').serialize();
                        }
                        return fetch(cpp_manager.create_order_url, {
                            method: 'POST',
                            headers: {
                                'Content-Type': 'application/x-www-form-urlencoded'
                            },
                            body: data
                        }).then(function (res) {
                            return res.json();
                        }).then(function (data) {
                            if (typeof data.success !== 'undefined') {
                                var messages = data.data.messages ? data.data.messages : data.data;
                                if ('string' === typeof messages) {
                                    showError('<ul class="woocommerce-error" role="alert">' + messages + '</ul>', $('form'));
                                } else {
                                    var messageItems = messages.map(function (message) {
                                        return '<li>' + message + '</li>';
                                    }).join('');
                                    showError('<ul class="woocommerce-error" role="alert">' + messageItems + '</ul>', $('form'));
                                }
                                return null;
                            } else {
                                return data.orderID;
                            }
                        });
                    }
                },
                onCancel: function (data, actions) {
                    actions.redirect(cpp_manager.cancel_url);
                },
                onError: function (err) {
                    $('#place_order, #wc-complete_paypal_payments-cc-form').unblock();
                },
                styles: {
                    'input': {
                        'font-size': '1.3em'
                    }
                },
                fields: {
                    number: {
                        selector: '#complete_paypal_payments-card-number',
                        placeholder: '•••• •••• •••• ••••',
                        addClass: 'input-text wc-credit-card-form-card-number'
                    },
                    cvv: {
                        selector: '#complete_paypal_payments-card-cvc',
                        placeholder: 'CVC'
                    },
                    expirationDate: {
                        selector: '#complete_paypal_payments-card-expiry',
                        placeholder: 'MM / YY'
                    }
                }
            }).then(function (hf) {
                hf.on('cardTypeChange', function (event) {
                    if (event.cards.length === 1) {
                        $('#complete_paypal_payments-card-number').removeClass().addClass(event.cards[0].type.replace("master-card", "mastercard").replace("american-express", "amex").replace("diners-club", "dinersclub").replace("-", ""));
                        $('#complete_paypal_payments-card-number').addClass("input-text wc-credit-card-form-card-number hosted-field-braintree braintree-hosted-fields-valid");
                    }
                });
                $(document.body).on('submit_paypal_cc_form', function (event) {
                    event.preventDefault();
                    var state = hf.getState();
                    var contingencies = [];
                    contingencies = [cpp_manager.threed_secure_contingency];
                    $('form.checkout').addClass('processing').block({
                        message: null,
                        overlayCSS: {
                            background: '#fff',
                            opacity: 0.6
                        }
                    });
                    $.cpp_scroll_to_notices($('#order_review'));
                    hf.submit({
                        contingencies: contingencies,
                        cardholderName: document.getElementById('billing_first_name').value,
                        billingAddress: {
                            streetAddress: document.getElementById('billing_address_1').value,
                            extendedAddress: document.getElementById('billing_address_2').value,
                            region: document.getElementById('billing_state').value,
                            locality: document.getElementById('billing_city').value,
                            postalCode: document.getElementById('billing_postcode').value,
                            countryCodeAlpha2: document.getElementById('billing_country').value
                        }
                    }).then(
                            function (payload) {
                                if (payload.orderId) {
                                    $.post(cpp_manager.cc_capture + "&paypal_order_id=" + payload.orderId + "&woocommerce-process-checkout-nonce=" + cpp_manager.woocommerce_process_checkout, function (data) {
                                        window.location.href = data.data.redirect;
                                    });
                                }
                            }, function (error) {
                        $('#place_order, #wc-complete_paypal_payments-cc-form').unblock();
                        $('form.checkout').removeClass('processing paypal_cc_submiting HostedFields createOrder').unblock();
                        var error_message = '';
                        if (error.details[0]['description']) {
                            error_message = error.details[0]['description'];
                        } else {
                            error_message = error.message;
                        }
                        if (error.details[0]['issue'] === 'INVALID_RESOURCE_ID') {
                            error_message = '';
                        }
                        if (error_message !== '') {
                            showError('<ul class="woocommerce-error" role="alert">' + error_message + '</ul>', $('form'));
                        }
                    }
                    );
                });
            }).catch(function (err) {
                console.log('error: ', JSON.stringify(err));
            });
        };

        if (is_from_checkout === false) {
            smart_button_render();
        }
        if (cpp_manager.is_pay_page === 'yes') {
            smart_button_render();
        }
        var hide_show_place_order_button = function () {
            console.log('hi');
            if (is_hosted_field_eligible() === true) {
                $('#place_order').show();
            } else {
                if (is_cpp_selected()) {
                    $('#place_order').hide();
                } else {
                    $('#place_order').show();
                }
            }
        };
        $(document.body).on('updated_cart_totals updated_checkout', function () {
            hide_show_place_order_button();
            setTimeout(function () {
                smart_button_render();
                if (is_hosted_field_eligible() === true) {
                    $('.checkout_cc_separator').show();
                    $('#wc-complete_paypal_payments-cc-form').show();
                    hosted_button_render();
                } else {
                    $('.checkout_cc_separator').hide();
                    $('#wc-complete_paypal_payments-cc-form').hide();
                }
            }, 300);
        });

        $('form.checkout').on('click', 'input[name="payment_method"]', function () {
            hide_show_place_order_button();
        });
        function is_hosted_field_eligible() {
            if (is_from_checkout) {
                if (cpp_manager.advanced_card_payments === 'yes') {
                    if (typeof paypal === 'undefined') {
                        return false;
                    }
                    if (paypal.HostedFields.isEligible()) {
                        return true;
                    }
                }
            }
            return false;
        }
        function is_cpp_selected() {
            if ($('#payment_method_complete_paypal_payments').is(':checked')) {
                return true;
            } else {
                return false;
            }
        }
    });
});