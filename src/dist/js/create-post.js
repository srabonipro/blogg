(function($) {
    $("#publishbtn").click(function(e) {
        e.preventDefault();
        $("input , button, textarea").prop("disabled", true);

        $.ajax({
            type: "POST",
            url: basepath + "/pages/post-new.php",
            data: {
                post: "Blah blah",
                t: $("#title").val(),
                ta: $("#tags").val(),
                c: $("#editor").val()
            },
            success: function(r) {
                if (r.success) {
                    window.location = r.posturl;
                } else {
                    Swal.fire(
                        'Error',
                        r.message,
                        'error'
                    );
                    $("input , button, textarea").prop("disabled", false);
                }
            }
        });

    });

}(jQuery));