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
if (isset($_GET["verifyemail"])) {

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
                header("Refresh:5");

                die("Login Success. <b>Please Wait 5 seconds</b>");
            } else {
                die("Invalid login link");
            }
        }
    } else {
        die("Please login with the same device you requested the login link");
    }
} elseif (!logged_in()) {
    die("You must be logged in");
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
    isset($_POST['m'])
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
            ob_start();
            echo $th;
            $m = ob_get_clean();
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
 * 
 * 
 * END all API stuff
 * 
 * 
 * 
 */
else {
    /**
     * 
     * Dashboard start
     * 
     */

    echo show_header("Dashboard");

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
                <label for="meta" class="input-label">Your meta(Optional)</label>
                <textarea id="meta" rows="4" class="input"></textarea>
            </div>
            <button class="btn" type="submit">Submit</button>
        </form>

    <?php
    } else {
        /**
         * 
         * 
         * The routing system
         * 
         * 
         */


        /**
         * Normal Dashboard
         */
        if (current_url() == BASEPATH . "/pages/dashboard.php/" or current_url() == BASEPATH . "/pages/dashboard.php") {
            $page = "normal";
        }
        /**
         * Reading List
         */
        elseif (current_url() == BASEPATH . "/pages/dashboard.php/reading-list") {
            $page = "reading-list";
        }
        /**
         * Notifications
         */
        elseif (current_url() == BASEPATH . "/pages/dashboard.php/notifications") {
            $page = "notifications";
        }
        /**
         * Account
         */
        elseif (current_url() == BASEPATH . "/pages/dashboard.php/account") {
            $page = "account";
        }
        /**
         * Posts
         */
        elseif (current_url() == BASEPATH . "/pages/dashboard.php/posts") {
            $page = "posts";
        }
        /**
         * Email
         */
        elseif (current_url() == BASEPATH . "/pages/dashboard.php/email") {
            $page = "email";
        }
        /**
         * Appearance
         */
        elseif (current_url() == BASEPATH . "/pages/dashboard.php/appearance") {
            $page = "appearance";
        }
        /**
         * 404 
         */
        else {
            die("<h1>Page Not Found</h1>");
        }
    ?>
        <div class="p-2"></div>

        <div class="row">
            <div class="col-md-2">
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
                        <span class="list-item-title">Notifications <code class="rounded"><b>1</b></code></span>
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
                    <?php
                        break;
                        /**
                         * Notifications
                         */
                    case "notifications":
                    ?>
                        <h1>Notifications</h1>
                        <p>Here you can see all the notifications you have.</p>
                    <?php
                        break;
                        /**
                         * Account
                         */
                    case "account":
                    ?>
                        <h1>Account</h1>
                        <p>Here you can change your account settings.</p>

                        <div class="p-2"></div>

                        <div class="row">
                            <form action="<?= BASEPATH ?>/pages/dashboard.php/account" id="personalinfoform" method="POST" class="col-md-10 box">
                                <div class="input-container">
                                    <label class="input-label">Profile picture</label>
                                    <img class="rounded" src="<?= get_gravatar($account["email"]) ?>" alt="Your profile picture">
                                    <a href="https://en.gravatar.com/support/faq/" target="_blank" class="btn small mt-3 outlined" style="width: max-content;">Change profile picture in Gravatar <i class="ms-1 mdi mdi-open-in-new"></i> </a>
                                </div>

                                <div class="input-container">
                                    <label for="name" class="input-label">Name</label>
                                    <input type="text" class="input" id="name" name="name" required="" min="4" value="<?= $account["username"] ?>">
                                </div>

                                <div class="input-container">
                                    <label for="meta" class="input-label">Meta</label>
                                    <textarea id="meta" rows="4" class="input"><?= $account["meta"] ?></textarea>
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
                    ?>
                        <h1>Appearance</h1>
                        <p>Here you can change your appearance.</p>
                        <?php
                        break;
                        ?>
            </div>
        </div>
<?php
                }
            }


            ob_start();
?>
<script src="<?= BASEPATH ?>/src/dist/js/dashboard.js"></script>
<?php
    $footer = ob_get_clean();
    echo show_footer($footer);
}
