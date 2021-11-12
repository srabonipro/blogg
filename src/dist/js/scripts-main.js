function escapeHtml(text) {
    var map = {
        '&': '&amp;',
        '<': '&lt;',
        '>': '&gt;',
        '"': '&quot;',
        "'": '&#039;'
    };

    return text.replace(/[&<>"']/g, function(m) { return map[m]; });
}
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
        animation: "fade",
        trigger: "click",
        allowHTML: true,
        arrow: false,
        placement: 'bottom-start',
        appendTo: () => document.body,
        onShow(instance) {
            instance._user = instance.reference.dataset.username;
            twemoji.parse(document.body);
            console.log(instance);

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
                        twemoji.parse(document.body);
                        instance.setContent(
                            `
                            <div class="profile" style="--color:${r.color}">
                                <div class="top">
                                    <div class="avatar">
                                        <img src="${r.avatar}" alt="${r.name}">
                                    </div>
                                </div>
                                <div class="bottom">
                                    <a href="${r.profile}" class="color-light-not-hover">
                                        <h6>${r.name}</h6>
                                    </a>
                                    <div class="description">
                                        <p>${r.about}</p>
                                    </div>
                                    <div class="mt-2">
                                        <p>${r.posts} Posts</p>
                                    </div>
                                 </div>
                            </div>
                            `
                        );
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

    /**
     * Follow user
     **/
    $("[data-follow]").click(function(e) {
        e.preventDefault();
        var t = $(this);
        t.prop("disabled", true).addClass("disabled");
        $.ajax({
            type: "POST",
            url: basepath + "/pages/user.php",
            datatype: "json",
            data: {
                uid: t.data("follow"),
                follow: ""
            },
            success: function(r) {
                r = JSON.parse(r);
                console.log(r);
                if (r.success) {
                    t.prop("disabled", false).removeClass("disabled");
                    t.html(r.status);
                } else {
                    showSnackbar("Something went wrong")
                }
            }
        });
    });

    $("[data-github-gist-id]").each(function() {
        var t = $(this);
        $.ajax({
            type: "GET",
            url: `https://api.github.com/gists/${t.data("github-gist-id")}`,
            success: function(r) {
                if (undefined == r.files) {
                    t.html("<p>Gist not found</p>");
                    return;
                }
                t.removeClass("box").html("");
                for (var k in r.files) {
                    console.log(k, r.files[k]);
                    var file = r.files[k];
                    var html = `
                    <div>
                    <h6>${file.filename} ${file.truncated ? "(Truncated)" : ""}</h6>
                    <pre><code>${file.content}</code></pre>
                    <a class="btn mt-2 small" href="${file.raw_url}" target="_blank">View raw</a>
                    </div>
                    `;
                    t.append(html);
                }
            },
            error: function() {
                t.removeClass("box").html("<p>Gist not found</p>");
            }
        });
    });
})(jQuery);
/**
 * Snackbar notifications 
 */
function showSnackbar(message, actiontext = "", action = "", timeout = 3000) {
    var id = "snack" + Math.floor((Math.random() * 100) + 1) + new Date().getTime();
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