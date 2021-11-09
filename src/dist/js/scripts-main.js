(function($) {

    $(window).scroll(function() {
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

    $("a[data-username]").each(function() {
        $(this).attr("data-no-instant", "");
        $(this).click(function(e) {
            e.preventDefault();
        });
    });
    tippy("a[data-username]", {
        content: "<div class=\"loader\"></div>",
        interactive: true,
        animation: "",
        allowHTML: true,
        arrow: false,
        placement: 'bottom-start',
        onShow(instance) {
            instance._user = instance.reference.dataset.username;

            if (instance._fetched) {
                return;
            }

            $.ajax({
                type: "POST",
                url: basepath + "/pages/user.php",
                datatype: "json",
                data: {
                    uid: instance._user,
                    getprofiledata: ""
                },
                success: function(r) {
                    var r = JSON.parse(r);
                    if (r.success == true) {
                        instance.setContent(
                            `
                            <img src="${r.avatar}" class="rounded" />
                            <h6 class="m-0 mb-1">
                                <a href="${r.profile}">${r.name}</a>
                            </h6>
                            <p class="m-0">
                                ${r.about}
                            </p>
                            <br/>
                            <b>Posts</b>
                            ${r.posts}
                            <br/>
                            <b>Color</b>
                            <span style="color: ${r.color};">${r.color}</span>
                            <br/>
                            `
                        );
                        instance._isFetching = true;
                        instance._fetched = true;
                    } else {
                        instance.setContent(`Something went wrong`);
                        instance._fetched = false;
                    }
                },
                error: function() {
                    instance.setContent(`Connection lost`);
                    instance._fetched = false;
                }
            });
        }
    });
})(jQuery);

/**
 * Snackbar notifications 
 */
function showSnackbar(message, actiontext = "", action = "", timeout = 3000) {
    var id = "snack" + Math.floor((Math.random() * 100) + 1);
    $("#snackbar-container").append(`<div id="${id}" class="snackbar">${message} ${actiontext !== "" ? "<button class=\"btn ghost\">" + actiontext + "</button>" : ""}</div>`);
    $("body").on("click", "#" + id + " button", function() {
        $("#" + id).remove();
        action == "" ? "" : action;
    });
    setTimeout(function() {
        $("#" + id).addClass("show");
    }, 100);
    setTimeout(function() {
        $("#" + id).removeClass("show");
        setTimeout(function() {
            $("#" + id).remove();
        }, 500);
    }, timeout);
}