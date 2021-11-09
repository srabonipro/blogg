(function($) {
    /**
     * Copy to clipboard the new way
     */
    function fallbackCopyTextToClipboard(text) {
        var textArea = document.createElement("textarea");
        textArea.value = text;

        // Avoid scrolling to bottom
        textArea.style.top = "0";
        textArea.style.left = "0";
        textArea.style.position = "fixed";

        document.body.appendChild(textArea);
        textArea.focus();
        textArea.select();

        try {
            var successful = document.execCommand('copy');
        } catch (err) {
            console.error('Fallback: Oops, unable to copy', err);
        }

        document.body.removeChild(textArea);
    }

    function copyTextToClipboard(text) {
        if (!navigator.clipboard) {
            fallbackCopyTextToClipboard(text);
            return;
        }
        navigator.clipboard.writeText(text).then(function() {
            console.log('Async: Copying to clipboard was successful!');
        }, function(err) {
            console.error('Async: Could not copy text: ', err);
        });
    }

    $('#copy').click(function(e) {
        e.preventDefault();
        var text = $("#invitecode").val();
        copyTextToClipboard(text);
        showSnackbar('Copied to clipboard', "Dismiss");

        $("#copy").text("Copied!");
        setTimeout(function() {
            $("#copy").text("Copy code");
        }, 1000);
    });

    $("#generatenew").click(function(e) {
        e.preventDefault();
        $(this).prop("disabled", true);
        $(this).text("Generating...");

        var t = $(this);

        /**
         * Send ajax and get new invite code
         */

        $.ajax({
            type: "POST",
            url: basepath + "/pages/org.php?id=" +
                t.data("id") +
                "&action=generatenew",
            success: function(r) {
                setTimeout(function() {
                    $("#invitecode").val(r.code).attr("type", "text");
                    setTimeout(function() {
                        $("#invitecode").attr("type", "password");
                    }, 3000);
                    t.prop("disabled", false);
                    t.text("Generate New Invite Code");
                }, 2000);
            }
        });

    });

}(jQuery));