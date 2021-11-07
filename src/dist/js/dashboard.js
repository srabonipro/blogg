(function ($) {
    $("#gettingstarted").submit(function (e) {
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
            success: function (r) {
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

    /**
     * Mark as read when button is clicked
     */
    $(".markasread[data-id]").click(function (e) {
        e.preventDefault();
        $(this).prop("disabled", true);
        var id = $(this).data("id");
        $.ajax({
            type: "POST",
            url: basepath + "/pages/dashboard.php",
            data: {
                markasread: "",
                id: id
            },
            success: function (r) {
                if (r.success) {
                    window.location.reload();
                } else {
                    Swal.fire(
                        'Error',
                        "Something went wrong",
                        'error'
                    );
                }
            }
        });
    });

}(jQuery));