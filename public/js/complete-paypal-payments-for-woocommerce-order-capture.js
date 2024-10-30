(function ($) {
    'use strict';
    $(function () {
        if ($('#place_order').length) {
            $('html, body').animate({
                scrollTop: ($('#place_order').offset().top - 500)
            }, 1000);
        }
    });
})(jQuery);