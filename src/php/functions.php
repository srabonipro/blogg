<?php

function encrypt__($data, $encryptionKey)
{
    $iv = openssl_random_pseudo_bytes(openssl_cipher_iv_length('aes-256-gcm'));
    $encrypted = openssl_encrypt($data, 'aes-256-gcm', $encryptionKey, OPENSSL_RAW_DATA, $iv, $tag);
    return base64_encode($iv . $tag . $encrypted);
}

function decrypt__($data, $encryptionKey)
{
    $c = base64_decode($data);
    $ivlen = openssl_cipher_iv_length($cipher = "AES-256-GCM");
    $iv = substr($c, 0, $ivlen);
    $tag = substr($c, $ivlen, $taglen = 16);
    $ciphertext_raw = substr($c, $ivlen + $taglen);
    return openssl_decrypt($ciphertext_raw, 'aes-256-gcm', $encryptionKey, OPENSSL_RAW_DATA, $iv, $tag);
}

function hash__($string, $action = 'encrypt')
{
    $secret = sha1(sha1(md5(SALT)));
    if ($action === "decrypt") {
        $output = decrypt__($string, $secret);
    } else {
        $output = encrypt__($string, $secret);
    }
    return $output;
}

function rand_id($length = 10)
{
    $characters = '0123456789abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ';
    $charactersLength = strlen($characters);
    $randomString = '';
    for ($i = 0; $i < $length; $i++) {
        $randomString .= $characters[rand(0, $charactersLength - 1)];
    }
    return $randomString;
}

function logged_in()
{
    /**
     * Check if cookie there
     */
    if (isset($_COOKIE['_loggedin__hash'])) {
        /**
         * Check if cookie empty
         */
        if (empty($_COOKIE['_loggedin__hash'])) {
            $l = false;
        }
        /**
         * Check if encryptrd string an email
         */
        elseif (filter_var(hash__($_COOKIE['_loggedin__hash'], "decrypt"), FILTER_VALIDATE_EMAIL)) {
            /**
             * Decrypt Email
             */
            $email = hash__($_COOKIE['_loggedin__hash'], "decrypt");
            /**
             * See if account there
             */
            $account = DB::queryFirstRow("SELECT * FROM users WHERE email=%s", $email);

            if (isset($account["id"])) {
                $l = true;
            } else {
                $l = false;
            }
        } else {
            $l = false;
        }
    } else {
        $l = false;
    }
    return $l;
}
function completed_setup()
{
    /**
     * Check if logged in
     */
    if (logged_in()) {
        /**
         * Decrypt Email
         */
        $email = hash__($_COOKIE['_loggedin__hash'], "decrypt");
        $account = DB::queryFirstRow("SELECT * FROM users WHERE email=%s", $email);

        if ($account["firstrun"] == "true") {
            $r = false;
        } else {
            $r = true;
        }
    } else {
        $r = false;
    }
    return $r;
}
function hexbrightness($hex, $steps)
{
    // Steps should be between -255 and 255. Negative = darker, positive = lighter
    $steps = max(-255, min(255, $steps));

    // Normalize into a six character long hex string
    $hex = str_replace('#', '', $hex);
    if (strlen($hex) == 3) {
        $hex = str_repeat(substr($hex, 0, 1), 2) . str_repeat(substr($hex, 1, 1), 2) . str_repeat(substr($hex, 2, 1), 2);
    }

    // Split into three parts: R, G and B
    $color_parts = str_split($hex, 2);
    $return = '#';

    foreach ($color_parts as $color) {
        $color   = hexdec($color); // Convert to decimal
        $color   = max(0, min(255, $color + $steps)); // Adjust color
        $return .= str_pad(dechex($color), 2, '0', STR_PAD_LEFT); // Make two char hex code
    }

    return $return;
}
function get_gravatar($email, $s = 80, $d = 'mp', $r = 'g', $img = false, $atts = array())
{
    $url = 'https://www.gravatar.com/avatar/';
    $url .= md5(strtolower(trim($email)));
    $url .= "?s=$s&d=$d&r=$r";
    if ($img) {
        $url = '<img src="' . $url . '"';
        foreach ($atts as $key => $val)
            $url .= ' ' . $key . '="' . $val . '"';
        $url .= ' />';
    }
    return $url;
}

function share_buttons()
{
    ob_start();
?>

    <a class="share-btn share-btn-branded share-btn-twitter" title="Share on Twitter">
        <span class="share-btn-icon"></span>
        <span class="share-btn-text">Twitter</span>
    </a>

    <!-- Branded Facebook button -->
    <a class="share-btn share-btn-branded share-btn-facebook" title="Share on Facebook">
        <span class="share-btn-icon"></span>
        <span class="share-btn-text">Facebook</span>
    </a>

    <!-- Branded Google+ button -->
    <a class="share-btn share-btn-branded share-btn-googleplus" title="Share on Google+">
        <span class="share-btn-icon"></span>
        <span class="share-btn-text">Google+</span>
    </a>

    <!-- Branded Reddit button -->
    <a class="share-btn share-btn-branded share-btn-reddit" title="Share on Reddit+">
        <span class="share-btn-icon"></span>
        <span class="share-btn-text">Reddit</span>
    </a>

    <!-- Branded Tumblr button -->
    <a class="share-btn share-btn-branded share-btn-tumblr" title="Share on Tumblr">
        <span class="share-btn-icon"></span>
        <span class="share-btn-text">Tumblr</span>
    </a>

    <!-- Divider only used for demo, don't copy this -->
    <div class="divider" role="presentation"></div>

    <!-- Branded LinkedIn button -->
    <a class="share-btn share-btn-branded share-btn-linkedin" title="Share on LinkedIn">
        <span class="share-btn-icon"></span>
        <span class="share-btn-text">LinkedIn</span>
    </a>

    <!-- Branded Pinterest button -->
    <a class="share-btn share-btn-branded share-btn-pinterest" title="Share on Pinterest">
        <span class="share-btn-icon"></span>
        <span class="share-btn-text">Pinterest</span>
    </a>

    <!-- Branded StumbleUpon button -->
    <a class="share-btn share-btn-branded share-btn-stumbleupon" title="Share on StumbleUpon">
        <span class="share-btn-icon"></span>
        <span class="share-btn-text">StumbleUpon</span>
    </a>

    <!-- Branded Delicious button -->
    <a class="share-btn share-btn-branded share-btn-delicious" title="Share on Delicious">
        <span class="share-btn-icon"></span>
        <span class="share-btn-text">Delicious</span>
    </a>
<?php
    return ob_get_clean();
}


function show_header($title = "", $additional = "")
{
    ob_start(); ?>
    <!DOCTYPE html>
    <!--[if lt IE 7]>      <html class="no-js lt-ie9 lt-ie8 lt-ie7"> <![endif]-->
    <!--[if IE 7]>         <html class="no-js lt-ie9 lt-ie8"> <![endif]-->
    <!--[if IE 8]>         <html class="no-js lt-ie9"> <![endif]-->
    <!--[if gt IE 8]>      <html class="no-js"> <!--<![endif]-->
    <html>

    <head>
        <meta charset="utf-8">
        <meta http-equiv="X-UA-Compatible" content="IE=edge">
        <title><?= $title . " | " . FNAME ?></title>
        <meta name="title" content="<?= $title . " | " . FNAME ?>">
        <?= $additional ?>
        <link rel="preconnect" href="https://fonts.googleapis.com">
        <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
        <link href="https://fonts.googleapis.com/css2?family=Inter:wght@100;200;300;400;500;600;700;800;900&display=swap" rel="stylesheet"> 
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <style>
            :root {
                --p-color: <?= FCOLOR ?>;
            }
        </style>
        <script>
            const basepath = "<?= BASEPATH ?>";
            const pcolor = "<?= FCOLOR ?>";
        </script>
        <link rel="stylesheet" href="<?= BASEPATH ?>/src/dist/css/styles-main.css">
        <link rel="stylesheet" href="<?= BASEPATH ?>/src/dist/css/sweetalert2.css">
        <link rel="stylesheet" href="<?= BASEPATH ?>/src/dist/css/materialdesignicons.css">
    </head>

    <body>
        <header id="header">
            <div class="start">
                <a style="min-width: 200px;" href="<?= BASEPATH ?>"><img src="<?= BASEPATH ?>/uploads/main/logo.png" alt="<?= FNAME ?> logo"></a>
                <input type="text" id="search" placeholder="Search" class="input ms-2">
            </div>
            <div class="end">
                <?php if (!logged_in()) {
                ?>
                    <a href="<?= BASEPATH ?>/pages/login.php" data-no-instant class="btn ghost">Login</a>
                    <a href="<?= BASEPATH ?>/pages/sign-up.php" data-no-instant class="btn">Sign Up</a>
                <?php
                } else {
                ?>
                    <a href="<?= BASEPATH ?>/pages/post-new.php" class="btn lg">Create Post</a>
                    <a href="<?= BASEPATH ?>/pages/dashboard.php/notifications" class="btn lg ghost rounded"><i class="mdi mdi-bell"></i></a>
                    <a href="<?= BASEPATH ?>/pages/dashboard.php" class="btn lg ghost rounded"><i class="mdi mdi-account"></i></a>
                <?php } ?>
            </div>
        </header>

        <div class="container-xl">

            <!--[if lt IE 7]>
                <h2>You are using an <strong>outdated</strong> browser. Please <a href="#">upgrade your browser</a> to improve your experience.</h2>
            <![endif]-->
        <?php
        return ob_get_clean();
    }

    function show_footer($additional = "")
    {
        ob_start(); ?>
        </div>
        <footer id="footer">
            <p>&copy; <a href="<?= BASEPATH ?>"><?= FNAME ?></a> All rights reserved</p>
        </footer>
        <script src="<?= BASEPATH ?>/src/dist/js/jquery.js"></script>
        <script src="<?= BASEPATH ?>/src/dist/js/sweetalert2.min.js"></script>
        <script src="<?= BASEPATH ?>/src/dist/js/scripts-main.js"></script>
        <script src="<?= BASEPATH ?>/src/dist/js/instantclick.min.js" data-no-instant></script>
        <script data-no-instant>
            InstantClick.init();
        </script>
        <?= $additional ?>
    </body>

    </html>
<?php
        return ob_get_clean();
    }


?>