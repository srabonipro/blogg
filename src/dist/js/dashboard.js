(function($) {
    $("#gettingstarted").submit(function(e) {
        e.preventDefault();
        $("input , button, textarea").prop("disabled", true);

        $.ajax({
            type: "POST",
            url: basepath + "/pages/dashboard.php",
            data: {
                updatecontactinfo: "",
                n: $("#name").val(),
                jt: $("#job").val(),
                c: $("#company").val(),
                co: $("#color").val(),
                m: $("#meta").val(),
            },
            success: function(r) {
                if (r.success) {
                    window.location.reload();
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