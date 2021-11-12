<?php

/**
 * 
 * Dashboard 
 * 
 */
require "../init.php";

/**
 * Check if email verification
 */
if (isset($_GET["verifyemail"]) and !logged_in()) {

    /**
     * Check if code empty
     */
    if (!empty($_GET["verifyemail"])) {


        $account = DB::queryFirstRow("SELECT * FROM users WHERE id=%s", base64_decode($_GET["verifyemail"]));

        if (isset($account["id"])) {
            if ($account["verified"] === "true") {
                die("Already verified email");
            } else {
                /**
                 * Update DB
                 * 
                 */
                DB::update(
                    'users',
                    ['verified' => "true"],
                    "id=%s",
                    base64_decode($_GET["verifyemail"])
                );

                die("Verification successful go to <a href='" . BASEPATH . "/pages/login.php'>Login Page</a>");
            }
        } else {
            die("Verification failed");
        }
    } else {
        die("Verification failed");
    }
}
/**
 * Login
 */
elseif (isset($_GET['login']) and !logged_in()) {
    /**
     * Cookie Test
     */
    if (isset($_COOKIE['_loggedin__verification'])) {
        /**
         * $_GET empty test
         */
        if (empty($_GET['login'])) {
            die("Invalid login link");
        } else {
            /**
             * Check if hash == hash ;D
             */
            if (hash__(base64_decode($_GET['login']), "decrypt") == $_COOKIE['_loggedin__verification']) {

                $account = DB::queryFirstRow("SELECT * FROM users WHERE loginhash=%s", hash__(base64_decode($_GET['login']), "decrypt"));

                if (!isset($account['id'])) {
                    die("Login failed");
                } else {
                    /**
                     * Remove loginhash from db
                     */
                    DB::update('users', ['loginhash' => ""], "email=%s", $account['email']);
                    /**
                     * Set login cookie
                     */
                    setcookie(
                        "_loggedin__hash",
                        hash__($account['email']),
                        strtotime('+90 days'),
                        "/",
                        "",
                        true,
                        true
                    );

                    setcookie(
                        "_loggedin__verification",
                        "",
                        strtotime('-90 days'),
                        "/",
                        "",
                        true,
                        true
                    );
                }
                header("Location: " . BASEPATH . "/pages/dashboard.php");

                die("Login Success. <b>Please Wait 5 seconds</b>");
            } else {
                die("Invalid login link");
            }
        }
    } else {
        die("Please login with the same device you requested the login link");
    }
} elseif (!logged_in()) {
    /**
     * Redirect to login page
     */
    header("Location:" . BASEPATH . "/pages/login.php");
}
/**
 * Update User info
 */
elseif (
    isset($_POST['updatecontactinfo']) and
    isset($_POST['n']) and
    isset($_POST['jt']) and
    isset($_POST['c']) and
    isset($_POST['co']) and
    isset($_POST['m']) and
    isset($_POST['u']) and
    logged_in()
) {
    /**
     * 
     * Set variables
     * 
     */

    $name = $_POST["n"];
    $job_title = $_POST["jt"];
    $company = $_POST["c"];
    $color = $_POST["co"];
    $meta = $_POST["m"];
    $username = $_POST["u"];

    /**
     * Remove all characters except letters and numbers
     */
    $username = preg_replace("/[^A-Za-z0-9 ]/", '', $username);

    $m = "";

    /**
     * 
     * Validation rules
     * 
     */

    $v = new Valitron\Validator(
        array(
            'name' => $name,
            'work' => $job_title,
            'company' => $company,
            'color' => $color,
            'meta' => $meta,
            'username' => $username,
        )
    );

    $v->rule('required', 'name');
    $v->rule('required', 'color');


    /**
     * 
     * Check if valid
     * 
     */

    if ($v->validate()) {
        $s = true;
    } else {
        /**
         * Validation errors
         */
        $s = false;
        ob_start();
        print("<h2>Fix these errors</h2>");
        foreach ($v->errors() as $error) {
            foreach ($error as $value) {
                print("<p>" . $value . "</p>");
            }
        }
        $m = ob_get_clean();
    }

    if ($s) {
        /**
         * Check if username is taken
         */
        $username_check = DB::queryFirstRow("SELECT * FROM users WHERE uname=%s", $username);

        if (isset($username_check['id'])) {
            $s = false;
            $m = "Username already taken";
        } else {
            if (validate_hex_color($color)) {
                try {
                    DB::update(
                        'users',
                        [
                            'username' => $name,
                            'meta' => $meta,
                            'firstrun' => "false",
                            'color' => $color,
                            'job' => $job_title,
                            'company' => $company
                        ],
                        "email=%s",
                        hash__($_COOKIE['_loggedin__hash'], "decrypt")
                    );
                    $s = true;
                } catch (Exception $th) {
                    $s = false;
                    $m = "Server Error";
                }
            } else {
                $s = false;
                $m = "Invalid color";
            }
        }
    }

    /**
     * 
     * Shows the data
     * 
     */

    $data = array(
        "success" => $s,
        "message" => $m
    );

    header('Content-Type: application/json; charset=utf-8');
    echo json_encode($data);

    die();
}
/**
 * 
 * Notifications mark as read 
 * 
 */
elseif (isset($_POST['markasread']) and isset($_POST['id']) and logged_in()) {
    header('Content-Type: application/json; charset=utf-8');
    /**
     * Get user id
     */
    $query = "DELETE FROM `notifications` WHERE user=%s AND id=%s";
    $user = DB::queryFirstRow("SELECT `id` FROM users WHERE email=%s", hash__($_COOKIE['_loggedin__hash'], "decrypt"));

    /**
     * Mark as read
     */
    try {
        DB::query(
            $query,
            $user['id'],
            base64_decode($_POST['id'])
        );
        $s = true;
    }
    /**
     * Error
     */
    catch (Exception $th) {
        $s = false;
    }

    /**
     * 
     * Shows the data
     * 
     */
    $result = array("success" => $s);
    echo json_encode($result);
    die();
}
/**
 * 
 * 
 * 
 * END all API stuff
 * 
 * 
 * 
 */
else {
    /**
     * Get Info
     */

    $email = hash__($_COOKIE['_loggedin__hash'], "decrypt");

    /**
     * DB
     */

    $account = DB::queryFirstRow("SELECT * FROM users WHERE email=%s", $email);

    /**
     * Check if first run
     */

    if (!completed_setup()) {
        echo show_header("Complete Setup");
        /**
         * The setup 
         */
?>
        <h1>Finish setting up your account.</h1>
        <p>Fill these details to become a member of <?= FNAME ?></p>
        <form action="#" method="POST" id="gettingstarted">
            <div class="input-container">
                <label for="name" class="input-label">Your name*</label>
                <input type="text" id="name" class="input" required>
            </div>
            <div class="input-container">
                <label for="job" class="input-label">Your work title (Optional)</label>
                <input type="text" id="job" class="input">
            </div>
            <div class="input-container">
                <label for="company" class="input-label">Your company (Optional)</label>
                <input type="text" id="company" class="input">
            </div>
            <div class="input-container">
                <label for="color" class="input-label">Your favorite color*</label>
                <input type="color" id="color" class="input" required>
            </div>
            <div class="input-container">
                <label for="username" class="input-label">Username* (Can't change again)</label>
                <input type="text" id="username" class="input" required>
            </div>
            <div class="input-container">
                <label for="meta" class="input-label">Your meta(Optional)</label>
                <textarea id="meta" rows="4" class="input"></textarea>
            </div>
            <button class="btn" type="submit">Submit</button>
        </form>

        <?php
        ob_start();
        ?>
        <script src="<?= BASEPATH ?>/src/dist/js/dashboard.js"></script>
    <?php
        $footer = ob_get_clean();
        echo show_footer($footer);
    }

    /**
     * 
     */
    else {
        /**
         * 
         * 
         * The routing system
         * 
         * 
         */


        /**
         * Reading List
         */
        if (
            current_url() == BASEPATH . "/pages/dashboard.php/reading-list"
            or
            strpos(current_url(), 'reading-list') !== false
        ) {
            $page = "reading-list";
        }
        /**
         * Organizations
         */
        elseif (
            current_url() == BASEPATH . "/pages/dashboard.php/my-orgs"
            or
            strpos(current_url(), 'my-orgs') !== false
        ) {
            $page = "my-orgs";
        }
        /**
         * Notifications
         */
        elseif (
            current_url() == BASEPATH . "/pages/dashboard.php/notifications"
            or strpos(current_url(), 'notifications') !== false
        ) {
            $page = "notifications";
        }
        /**
         * Account
         */
        elseif (
            current_url() == BASEPATH . "/pages/dashboard.php/account"
            or strpos(current_url(), 'account') !== false
        ) {
            $page = "account";
        }
        /**
         * Posts
         */
        elseif (
            current_url() == BASEPATH . "/pages/dashboard.php/posts"
            or strpos(current_url(), 'posts') !== false
        ) {
            $page = "posts";
        }
        /**
         * Email
         */
        elseif (
            current_url() == BASEPATH . "/pages/dashboard.php/email"
            or strpos(current_url(), 'email') !== false
        ) {
            $page = "email";
        }
        /**
         * Appearance
         */
        elseif (
            current_url() == BASEPATH . "/pages/dashboard.php/appearance"
            or strpos(current_url(), 'appearance') !== false
        ) {
            $page = "appearance";
        }
        /**
         * Badges
         */
        elseif (
            current_url() == BASEPATH . "/pages/dashboard.php/badges"
            or strpos(current_url(), 'badges') !== false
        ) {
            $page = "badges";
        }
        /**
         * Social
         */
        elseif (
            current_url() == BASEPATH . "/pages/dashboard.php/social"
            or strpos(current_url(), 'social') !== false
        ) {
            $page = "social";
        }
        /**
         * Normal 
         */
        else {
            $page = "normal";
        }

        /**
         * Show header
         */
        echo show_header("Dashboard");
    ?>
        <div class="p-2"></div>

        <section class="row">
            <?php /*** ***/ ?>
            <div class="col-md-2">
                <?php /*** Sidebar ***/ ?>
                <div class="list">
                    <a class="list-item <?= ($page == "normal") ? "active" : "" ?>" href="<?= BASEPATH ?>/pages/dashboard.php/">
                        <i alt="Icon" class="list-item-icon mdi mdi-home"></i>
                        <span class="list-item-title">Overview</span>
                    </a>

                    <a class="list-item <?= ($page == "reading-list") ? "active" : "" ?>" href="<?= BASEPATH ?>/pages/dashboard.php/reading-list">
                        <i alt="Icon" class="list-item-icon mdi mdi-bookmark"></i>
                        <span class="list-item-title">Saved posts</span>
                    </a>

                    <a class="list-item <?= ($page == "notifications") ? "active" : "" ?>" href="<?= BASEPATH ?>/pages/dashboard.php/notifications">
                        <i alt="Icon" class="list-item-icon mdi mdi-bell"></i>
                        <span class="list-item-title">Notifications <?= (get_notifications_count() > 0) ? "<code class=\"rounded\"><b>" . get_notifications_count() . "</b></code>" : "" ?></span>
                    </a>

                    <a class="list-item <?= ($page == "account") ? "active" : "" ?>" href="<?= BASEPATH ?>/pages/dashboard.php/account">
                        <i alt="Icon" class="list-item-icon mdi mdi-account"></i>
                        <span class="list-item-title">Account</span>
                    </a>

                    <a class="list-item <?= ($page == "posts") ? "active" : "" ?>" href="<?= BASEPATH ?>/pages/dashboard.php/posts">
                        <i alt="Icon" class="list-item-icon mdi mdi-note-text-outline"></i>
                        <span class="list-item-title">Posts</span>
                    </a>

                    <a class="list-item <?= ($page == "email") ? "active" : "" ?>" href="<?= BASEPATH ?>/pages/dashboard.php/email">
                        <i alt="Icon" class="list-item-icon mdi mdi-shield-account"></i>
                        <span class="list-item-title">Email Settings</span>
                    </a>

                    <a class="list-item <?= ($page == "my-orgs") ? "active" : "" ?>" href="<?= BASEPATH ?>/pages/dashboard.php/my-orgs">
                        <i alt="Icon" class="list-item-icon mdi mdi-account-group"></i>
                        <span class="list-item-title">My Organizations</span>
                    </a>

                    <a class="list-item <?= ($page == "social") ? "active" : "" ?>" href="<?= BASEPATH ?>/pages/dashboard.php/social">
                        <i alt="Icon" class="list-item-icon mdi mdi-human-greeting-proximity"></i>
                        <span class="list-item-title">Social</span>
                    </a>

                    <a class="list-item <?= ($page == "badges") ? "active" : "" ?>" href="<?= BASEPATH ?>/pages/dashboard.php/badges">
                        <i alt="Icon" class="list-item-icon mdi mdi-shield-star"></i>
                        <span class="list-item-title">Badges</span>
                    </a>

                    <a class="list-item <?= ($page == "appearance") ? "active" : "" ?>" href="<?= BASEPATH ?>/pages/dashboard.php/appearance">
                        <i alt="Icon" class="list-item-icon mdi mdi-palette"></i>
                        <span class="list-item-title">Appearance</span>
                    </a>
                </div>
                <div class="p-2"></div>
            </div>
            <?php
            /**
             * 
             * 
             * The content
             * 
             * 
             */
            ?>
            <div class="col-md-10">
                <div class="p-2"></div>
                <?php
                /**
                 * Sanitizes the data
                 */
                foreach ($account as $key => $value) {
                    $account[$key] = htmlspecialchars($value);
                }
                ?>


                <?php
                /**
                 * 
                 * 
                 * The pages
                 * 
                 * 
                 */
                switch ($page) {
                        /**
                     * Normal Dashboard
                     */
                    case "normal":
                ?>
                        <h1><?= strtok($account["username"], " ") ?>'s dashboard</h1>
                        <p>Change settings and other stuff!</p>



                    <?php
                        break;
                        /**
                         * Reading list
                         */
                    case "reading-list":
                    ?>
                        <h1>Reading list</h1>
                        <p>Here you can see all the posts you have saved.</p>
                        <div class="p-2"></div>
                    <?php
                        break;
                        /**
                         * Badges
                         */
                    case "badges":
                    ?>
                        <h1>Badges</h1>
                        <p>Here you can see all the badges you have earned.</p>
                        <div class="p-2"></div>
                        <?php
                        $badges = DB::query("SELECT * FROM earnedbadges WHERE user=%s", $account["id"]);

                        if (count($badges) == 0) {
                        ?>
                            <div class="notification">
                                <p>You have not earned any badges yet.</p>
                            </div>
                            <?php
                        } else {
                            echo "<div class=\"row p-2\">";
                            foreach ($badges as $badge) {
                                $_badge = DB::queryFirstRow("SELECT * FROM badges WHERE id=%s", $badge["badge"]);
                            ?>
                                <div class="box hoverable col" style="max-width: max-content;">
                                    <i style="background: hsla(var(--p-color),50%,50%,10%);color: var(--primary-color);border-radius: 100px;font-size: 1.8rem;max-width: 50px;max-height: 50px;display: flex;min-height: 50px;min-width: 50px;align-items: center;justify-content: center;border: 1px solid #e2e2e2;" class="mdi mdi-<?= $_badge["icon"] ?>"></i>
                                    <h3>
                                        <?= $_badge["name"] ?>
                                    </h3>
                                    <p>
                                        <?= $_badge["about"] ?>
                                    </p>
                                    <p class="muted" data-tooltip="<?= time_elapsed_string($badge["date"]) ?>">
                                        You earned this badge on
                                        <?php
                                        $s = $badge["date"];
                                        $dt = new DateTime($s);

                                        $date = $dt->format('m/d/Y');


                                        echo $date;
                                        ?></p>
                                </div>
                        <?php
                            }
                            echo "</div>";
                        }
                        ?>
                    <?php
                        break;
                        /**
                         * Social
                         */
                    case "social":

                        if (
                            isset($_POST["facebook"]) &&
                            isset($_POST["twitter"]) &&
                            isset($_POST["instagram"]) &&
                            isset($_POST["youtube"]) &&
                            isset($_POST["github"])
                        ) {
                            $facebook = $_POST["facebook"];
                            $twitter = $_POST["twitter"];
                            $instagram = $_POST["instagram"];
                            $youtube = $_POST["youtube"];
                            $github = $_POST["github"];

                            if (
                                !empty($facebook) ||
                                !empty($twitter) ||
                                !empty($instagram) ||
                                !empty($youtube) ||
                                !empty($github)
                            ) {
                                try {
                                    DB::update("users", array(
                                        "facebook" => $facebook,
                                        "twitter" => $twitter,
                                        "instagram" => $instagram,
                                        "youtube" => $youtube,
                                        "github" => $github
                                    ), "id=%s", $account["id"]);
                                    $s = true;
                                } catch (Exception $e) {
                                    $s = false;
                                }
                            }
                        }
                    ?>
                        <h1>Social</h1>
                        <p>Here you can see all the social accounts you have connected.</p>
                        <div class="p-2"></div>
                        <?php
                        if (isset($s) && $s) {
                        ?>
                            <div class="notification success mb-2">
                                <p>Your social accounts have been updated! <a href="<?= current_url() ?>" class="btn small">Refresh page</a></p>
                            </div>
                        <?php
                        }
                        ?>
                        <form class="box" action="<?= BASEPATH ?>/pages/dashboard.php/social" method="POST">
                            <div class="input-container">
                                <label for="facebook">Facebook Username <i class="mdi mdi-facebook"></i></label>
                                <input placeholder="username" type="text" name="facebook" class="input" id="facebook" value="<?= $account["facebook"] ?>">
                            </div>

                            <div class="input-container">
                                <label for="twitter">Twitter Username <i class="mdi mdi-twitter"></i></label>
                                <input placeholder="username" type="text" name="twitter" class="input" id="twitter" value="<?= $account["twitter"] ?>">
                            </div>

                            <div class="input-container">
                                <label for="instagram">Instagram Username <i class="mdi mdi-instagram"></i></label>
                                <input placeholder="username" type="text" name="instagram" class="input" id="instagram" value="<?= $account["instagram"] ?>">
                            </div>

                            <div class="input-container">
                                <label for="youtube">Youtube Username <i class="mdi mdi-youtube"></i></label>
                                <input placeholder="username" type="text" name="youtube" class="input" id="youtube" value="<?= $account["youtube"] ?>">
                            </div>
                            
                            <div class="input-container">
                                <label for="github">Github Username <i class="mdi mdi-github"></i></label>
                                <input placeholder="username" type="text" name="github" class="input" id="github" value="<?= $account["github"] ?>">
                            </div>

                            <button type="submit" class="btn">Save</button>
                        </form>
                    <?php
                        break;
                        /**
                         * Notifications
                         */
                    case "notifications":
                    ?>
                        <h1>Notifications</h1>
                        <p>Here you can see all the notifications you have.</p>
                        <div class="p-2"></div>
                        <div class="mt-2">
                            <?php
                            /**
                             * 
                             * 
                             * The notifications
                             * 
                             * 
                             */
                            $notifications = DB::query("SELECT * FROM notifications WHERE user=%s ORDER BY `date` DESC", $account["id"]);


                            /**
                             * Check if no notifications
                             */
                            if (count($notifications) == 0) {
                            ?>

                                <div class="box">
                                    <h1><i class="mdi mdi-bell-sleep"></i></h1>
                                    <h5 class="m-0">You have no notifications.</h5>
                                    <?= placeholder_text() ?>
                                </div>

                            <?php
                            } else {
                            ?>
                                <div class="pt-4"></div>
                            <?php
                            }

                            foreach ($notifications as $notification) {
                                $notification["date"] = time_elapsed_string($notification["date"]);
                            ?>
                                <div class="new-notification-header mb-2 p-3">
                                    <div style="flex: 10;">
                                        <?= $notification["message"] ?>
                                    </div>

                                    <div class="new-notification-meta" style="flex: 2;text-align: right;">
                                        <p><?= $notification["date"] ?>
                                            <button class="btn small rounded markasread" data-id="<?= base64_encode($notification["id"]) ?>"><i class="mdi mdi-delete"></i></button>

                                    </div>
                                </div>
                            <?php
                            }
                            ?>
                        </div>
                    <?php
                        break;
                        /**
                         * Organsiation
                         */
                    case "my-orgs":
                    ?>
                        <h1>My Organizations</h1>
                        <p>Here you can see all the organizations you created and part of.</p>
                        <div class="p-2"></div>

                        <h3>Joined</h3>
                        <?php
                        $orgs = DB::query("SELECT * FROM orgmembers WHERE `user` = %s", $account["id"]);

                        /**
                         * No organizations joined
                         */
                        if (count($orgs) == 0) {
                        ?>
                            <div class="box">
                                <h1><i class="mdi mdi-account-multiple-plus"></i></h1>
                                <h5 class="m-0">You have no organizations.</h5>
                                <?= placeholder_text() ?>
                            </div>
                            <?php
                        } else {
                            /**
                             * Show the organizations
                             */
                            foreach ($orgs as $org) {
                                $org = DB::queryFirstRow("SELECT * FROM organizations WHERE id=%s", $org["orgid"]);

                                /**
                                 * Sanitize the data
                                 */
                                foreach ($org as $key => $value) {
                                    $org[$key] = htmlspecialchars($value);
                                }
                            ?>
                                <div class="box">
                                    <h5> <?= $org["name"] ?> </h5>
                                    <?= (!empty($org["website"])) ? "<p>Website: " . $org["website"] . "</p>" : "" ?>
                                    <?= (!empty($org["email"])) ? "<p>Contact Email: " . $org["email"] . "</p>" : "" ?>
                                    <?php
                                    $members = DB::query("SELECT * FROM orgmembers WHERE orgid=%s", $org["id"]);

                                    $members = count($members);
                                    ?>
                                    <p>Members: <?= $members ?></p>
                                    <div class="p-2"></div>

                                    <?php
                                    /**
                                     * Check if the user is the owner
                                     */
                                    if (!($account["id"] === $org["owner"]) == true) {
                                    ?>
                                        <button class="btn error" data-leave-org="<?= base64_encode($org["id"]) ?>">Leave Organsiation</button>
                                    <?php
                                    } else {
                                        /**
                                         * The user is the owner
                                         */
                                    ?>
                                        <a class="btn" href="<?= BASEPATH . "/pages/org.php?id=" . $org["id"] ?>" data-nonce="<?= nonce_generator() ?>">Manage Organsiation</a>
                                    <?php
                                        $orgowner = true;
                                    }
                                    ?>
                                </div>
                        <?php
                            }
                        }
                        ?>


                    <?php
                        break;
                        /**
                         * Account
                         */
                    case "account":
                    if(
                        isset($_POST["name"]) &&
                        isset($_POST["meta"]) &&
                        isset($_POST["color"]) &&
                        isset($_POST["company"]) &&
                        isset($_POST["job"]) &&
                        isset($_POST["location"]) &&
                        isset($_POST["workingon"]) 
                    ) 
                    {

                    }
                    
                    ?>
                        <h1>Account</h1>
                        <p>Here you can change your account settings.</p>
                        <div class="p-2"></div>
                        <div class="box mt-2">
                            <a target="_blank" href="<?= BASEPATH ?>/account/<?= $account["uname"] ?>" class="btn small outlined" style="width: max-content;">View profile <i class="ms-1 mdi mdi-open-in-new"></i> </a>

                            <div class="p-2"></div>

                            <form action="<?= BASEPATH ?>/pages/dashboard.php/account" id="personalinfoform" method="POST">
                                <div class="input-container">
                                    <label class="input-label">Profile picture</label>
                                    <img class="rounded" src="<?= get_gravatar($account["email"]) ?>" alt="Your profile picture">
                                    <a href="https://en.gravatar.com/support/faq/" target="_blank" class="btn small mt-3 outlined" style="width: max-content;">Change profile picture in Gravatar <i class="ms-1 mdi mdi-open-in-new"></i> </a>
                                </div>

                                <div class="input-container">
                                    <label for="name" class="input-label">Name</label>
                                    <input type="text" class="input" id="name" name="name" placeholder="Psst.. Don't keep me blank" required="" min="4" value="<?= $account["username"] ?>">
                                </div>

                                <div class="input-container">
                                    <label for="meta" class="input-label">Meta</label>
                                    <textarea placeholder="I'm <?= $account["username"] ?>. I love dogs. I eat bananas..." id="meta" rows="4" class="input"><?= $account["meta"] ?></textarea>
                                </div>

                                <div class="input-container">
                                    <label for="color" class="input-label">Color</label>
                                    <input type="color" id="color" class="input" required="" value="<?= $account["color"] ?>">
                                </div>

                                <!-- Hey! don't try to change this. It will not work-->
                                <div class="input-container">
                                    <label for="username" class="input-label">Username (read only)</label>
                                    <input type="text" readonly class="input" id="username" name="username" value="<?= $account["uname"] ?>">
                                </div>

                                <div class="input-container">
                                    <label for="company" class="input-label">Company</label>
                                    <input type="text" class="input" id="company" name="company" value="<?= $account["company"] ?>">
                                </div>

                                <div class="input-container">
                                    <label for="job" class="input-label">Job title</label>
                                    <input type="text" class="input" id="job" name="job" value="<?= $account["job"] ?>" placeholder="Your job title">
                                </div>

                                <div class="input-container">
                                    <label for="workingon" class="input-label">Currently working on</label>
                                    <input type="text" class="input" id="workingon" name="workingon" value="<?= $account["workingon"] ?>" placeholder="What are you working on?">
                                </div>

                                <div class="input-container">
                                    <label for="location" class="input-label">Location</label>
                                    <input type="text" class="input" id="location" name="location" value="<?= $account["location"] ?>" placeholder="Your location">
                                </div>

                                <button type="submit" class="btn">Save Settings</button>

                                <div class="p-5 m-5"></div>
                                <div class="box" style="border: 1px solid;">
                                    <h2>Delete Account</h2>
                                    <p>If you want to disable your account, you can do it here. This will delete all your posts and all your data. You can't undo this action.</p>
                                    <div class="p-2"></div>
                                    <a href="#NeverGonnaGiveUp" id="deleteme" class="btn error" data-uid="<?= nonce_generator() ?>">Disable Account</a>
                                </div>
                                <div class="p-2"></div>
                            </form>
                        </div>

                    <?php
                        break;
                        /**
                         * Posts
                         */
                    case "posts":
                    ?>
                        <h1>Posts</h1>
                        <p>Here you can see all the posts you have created.</p>
                    <?php
                        break;
                        /**
                         * Email
                         */
                    case "email":
                    ?>
                        <h1>Email Settings</h1>
                        <p>Here you can change your email settings.</p>
                    <?php
                        break;
                        /**
                         * Appearance
                         */
                    case "appearance":
                        if (isset($_POST["t"])) {
                            switch ($_POST["t"]) {
                                case "1":
                                    $theme = "light";
                                    break;
                                case "2":
                                    $theme = "system";
                                    break;
                                case "3":
                                    $theme = "dark";
                                    break;
                                default:
                                    $theme = "light";
                                    break;
                            }

                            DB::query("UPDATE users SET theme = %s WHERE id = %s", $theme, $account["id"]);

                            $m = "<div class=\"notification success\">Saved Settings, <a class=\"btn small\" href=\"" . BASEPATH . "\\pages\\dashboard.php\\appearance\">Reload page</a></div>";
                        }
                    ?>
                        <h1>Appearance</h1>
                        <p>Here you can change your appearance.</p>
                        <div class="p-2"></div>
                        <?php

                        if (isset($m)) {
                            echo $m;
                        }

                        switch ($account["theme"]) {
                            case "light":
                                $theme = "light";
                                break;
                            case "dark":
                                $theme = "dark";
                                break;
                            case "system":
                                $theme = "system";
                                break;
                            default:
                                $theme = "light";
                                break;
                        }
                        ?>
                        <form action="<?= BASEPATH . "/pages/dashboard.php/appearance" ?>" method="POST">
                            <h3>Theme</h3>
                            <div class="row">
                                <label class="theme-radio" for="t-1">
                                    <img src="<?= BASEPATH ?>/uploads/light-theme.png" alt="Light Theme">
                                    <input type="radio" name="t" id="t-1" <?= ($theme == "light") ? "checked" : "" ?> value="1">
                                </label>

                                <label class="theme-radio" for="t-2">
                                    <img src="<?= BASEPATH ?>/uploads/system-theme.png" alt="System Theme">
                                    <input type="radio" name="t" id="t-3" <?= ($theme == "system") ? "checked" : "" ?> value="2">
                                </label>

                                <label class="theme-radio" for="t-2">
                                    <img src="<?= BASEPATH ?>/uploads/dark-theme.png" alt="Dark Theme">
                                    <input type="radio" name="t" id="t-2" <?= ($theme == "dark") ? "checked" : "" ?> value="3">
                                </label>
                            </div>

                            <button type="submit" class="btn mt-2">Save</button>
                        </form>
                        <?php
                        break;
                        ?>

                <?php
                }
                ?>
            </div>
        </section>
    <?php
    }

    /**
     * Show footer
     */

    ob_start();
    ?>
    <script src="<?= BASEPATH ?>/src/dist/js/dashboard.js"></script>
<?php
    $footer = ob_get_clean();
    echo show_footer($footer);
}
