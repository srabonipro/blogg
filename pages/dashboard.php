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
} else {
    /**
     * 
     * Normal Dashboard
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
    }



    ob_start();
    ?>
    <script src="https://js.hcaptcha.com/1/api.js"></script>
    <script src="<?= BASEPATH ?>/src/dist/js/dashboard.js"></script>
<?php
    $footer = ob_get_clean();
    echo show_footer($footer);
}
