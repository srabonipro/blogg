(function ($) {
    $(document).ready(function () {
        // Add a delay
        setTimeout(() => {
            $(".c-d-n").removeClass("c-d-n");
            $("#loading-screen").remove();
        }, 500);
    });
}(jQuery));