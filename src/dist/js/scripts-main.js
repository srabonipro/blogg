(function ($) {

    $(window).scroll(function () {
        if ($(window).scrollTop() > 20) {
            $("#header").addClass('sticky');
        } else {
            $("#header").removeClass('sticky');
        }
    });

    /**
     * Parse emoji
     */

    twemoji.parse(document.body);
})(jQuery);

