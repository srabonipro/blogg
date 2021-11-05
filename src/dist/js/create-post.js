(function ($) {
    //Remove loader after 1s
    setTimeout(function () {
        $('#loading').remove();
        $(".c-d-n").removeClass("c-d-n");
    }, 1000);

    var editor = new SimpleMDE({ element: $("#editor")[0] });

    $("#publishbtn").click(function (e) {
        e.preventDefault();
        $("input , button, textarea").prop("disabled", true);

        $.ajax({
            type: "POST",
            url: basepath + "/pages/post-new.php",
            data: {
                post: "\n",
                t: $("#title").val(),
                ta: $("#tags").val(),
                c: editor.value()
            },
            success: function (r) {
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


    $("#file").on("change", function () {
        var files = !!this.files ? this.files : [];
        if (!files.length || !window.FileReader) return; // no file selected, or no FileReader support

        if (/^image/.test(files[0].type)) { // only image file
            var reader = new FileReader(); // instance of the FileReader
            reader.readAsDataURL(files[0]); // read the local file

            reader.onloadend = function () { // set image data as background of div
                $("#imagePreview").css("background-image", "url(" + this.result + ")");
            }
        }
    });

}(jQuery));