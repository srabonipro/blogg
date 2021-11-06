(function ($) {

    $(window).scroll(function () {
        if ($(window).scrollTop() > 20) {
            $("#header").addClass('sticky');
        } else {
            $("#header").removeClass('sticky');
        }
    });

    /**
     * Keep footer at the bottom of the page
     */
    function keepFooterAtBottom() {
        var footer = $('#footer');
        var footerHeight = footer.outerHeight();
        var footerTop = footer.position().top + footerHeight;
        var windowHeight = $(window).height();
        var bodyHeight = $('body').height();

        if (footerTop < windowHeight) {
            footer.css('margin-top', (windowHeight - footerTop) - 60 + 'px');
        }
    }
    $(window).resize(function() {
        keepFooterAtBottom();
    });
    keepFooterAtBottom();
})(jQuery);