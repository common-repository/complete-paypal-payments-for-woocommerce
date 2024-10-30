jQuery(function ($) {
    if (typeof cpp_pay_later_messaging === 'undefined') {
        return false;
    }
    var front_end_shortcode_page_pay_later_messaging_preview = function () {
        var shortcode_style_object = {};
        shortcode_style_object['layout'] = cpp_pay_later_messaging.style;
        if (shortcode_style_object['layout'] === 'text') {
            shortcode_style_object['logo'] = {};
            shortcode_style_object['logo']['type'] = cpp_pay_later_messaging.logotype;
            if (shortcode_style_object['logo']['type'] === 'primary' || cpp_pay_later_messaging.logotype === 'alternative') {
                shortcode_style_object['logo']['position'] = cpp_pay_later_messaging.logoposition;
            }
            shortcode_style_object['text'] = {};
            shortcode_style_object['text']['size'] = parseInt(cpp_pay_later_messaging.textsize);
            shortcode_style_object['text']['color'] = cpp_pay_later_messaging.textcolor;
        } else {
            shortcode_style_object['color'] = cpp_pay_later_messaging.color;
            shortcode_style_object['ratio'] = cpp_pay_later_messaging.ratio;
        }
        if (typeof paypal !== 'undefined') {
            paypal.Messages({
                amount: cpp_pay_later_messaging.amount,
                placement: cpp_pay_later_messaging.placement,
                style: shortcode_style_object
            }).render('.cpp_message_shortcode');
        }
    };
    front_end_shortcode_page_pay_later_messaging_preview();
});