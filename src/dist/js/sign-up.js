(function($) {
    const sitekey = $("#h-captcha").data("sitekey");
    hcaptcha.render('h-captcha', {
        sitekey: sitekey
    });

    $("#form").submit(function(e) {
        e.preventDefault();
        $("input , button").prop("disabled", true);

        $.ajax({
            type: "POST",
            url: basepath + "/pages/auth.php",
            data: {
                e: $("#email").val(),
                c: hcaptcha.getResponse()
            },
            success: function(r) {
                if (r.success) {
                    Swal.fire(
                        'Check your email',
                        'Check your email for the instructions.',
                        'success'
                    );
                    $("#form").fadeOut(300);
                    setTimeout(() => {
                        $("#form").remove();
                    }, 300);
                } else {
                    Swal.fire(
                        'Error',
                        r.message,
                        'error'
                    );
                    hcaptcha.render('h-captcha', {
                        sitekey: sitekey
                    });
                    $("input , button").prop("disabled", false);
                }
            }
        });

    });

}(jQuery));