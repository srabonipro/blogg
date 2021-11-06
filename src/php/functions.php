<?php

/**
 * Used in functions
 */
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
/**
 * End functions
 */

/**
 * 
 * Encrypt and decrypt data
 * 
 */
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

/**
 * 
 * Generate a random string
 * 
 */
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

/**
 * 
 * Check if logged in
 * 
 */
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
/**
 * 
 * Check if completed profile setup
 * 
 */
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
/**
 * 
 * Get gravatar
 * 
 */
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
/**
 * 
 * Validate image
 * 
 */
function check_base64_image($base64)
{
    $file = $base64;
    $file_data = base64_decode($file);
    $f = finfo_open();
    $mime_type = finfo_buffer($f, $file_data, FILEINFO_MIME_TYPE);
    $file_type = explode('/', $mime_type)[0];

    $acceptable_mimetypes = [
        'image/png',
        'image/gif',
        'image/jpeg',
    ];

    if (!in_array($mime_type, $acceptable_mimetypes)) {
        return false;
    }

    if ($file_type !== 'image') {
        return false;
    }

    return true;
}
/**
 * 
 * Upload image to imgbb and return url
 * 
 */
function upload_imgbb($data)
{
    $url = 'https://api.imgbb.com/1/upload';
    $data = array('image' => $data, 'key' => IMGBBAPI);

    $options = array(
        'http' => array(
            'header'  => "Content-type: application/x-www-form-urlencoded\r\n",
            'method'  => 'POST',
            'content' => http_build_query($data)
        )
    );
    $context  = stream_context_create($options);
    $result = file_get_contents($url, false, $context);
    $json = json_decode($result, true);
    return $json["data"]["url"];
}
/**
 * 
 * Remove duplicates from array
 * 
 */
function array_unique_multidimensional($array)
{
    $result = array_map("unserialize", array_unique(array_map("serialize", $array)));
    foreach ($result as $key => $value) {
        if (is_array($value)) {
            $result[$key] = array_unique_multidimensional($value);
        }
    }
    return $result;
}
/**
 * 
 * Share buttons
 * 
 */
function share_buttons()
{
    ob_start();
?>
    <div class="list">
        <button class="list-item">
            <i class="list-item-icon mdi mdi-facebook"></i>
            Share to Facebook
        </button>
        <button class="list-item">
            <i class="list-item-icon mdi mdi-reddit"></i>
            Share to Reddit
        </button>
        <button class="list-item">
            <i class="list-item-icon mdi mdi-pinterest"></i>
            Pin This
        </button>
        <button class="list-item">
            <i class="list-item-icon mdi mdi-clipboard"></i>
            Copy link
        </button>
    </div>
<?php
    return ob_get_clean();
}
/**
 * 
 * 
 * Get current page url
 * 
 */
function current_url()
{
    $pageURL = 'http';
    if (isset($_SERVER["HTTPS"]) && $_SERVER["HTTPS"] == "on") {
        $pageURL .= "s";
    }
    $pageURL .= "://";
    if ($_SERVER["SERVER_PORT"] != "80") {
        $pageURL .= $_SERVER["SERVER_NAME"] . ":" . $_SERVER["SERVER_PORT"] . $_SERVER["REQUEST_URI"];
    } else {
        $pageURL .= $_SERVER["SERVER_NAME"] . $_SERVER["REQUEST_URI"];
    }
    return $pageURL;
}
/**
 * 
 * Header
 * 
 */
function show_header($title = "", $additional = "")
{
    ob_start(); ?>
    <!DOCTYPE html>
    <html>

    <head>
        <meta charset="utf-8">
        <meta http-equiv="X-UA-Compatible" content="IE=edge">
        <meta name="title" content="<?= $title . " | " . FNAME ?>">
        <meta name="viewport" content="width=device-width, initial-scale=1">

        <title><?= $title . " | " . FNAME ?></title>
        <base href="<?= BASEPATH ?>">

        <?= $additional ?>

        <link rel="icon" href="<?= BASEPATH ?>/uploads/favicon.png">
        <link rel="stylesheet" href="<?= BASEPATH ?>/src/dist/css/styles-main.css">
        <link rel="stylesheet" href="<?= BASEPATH ?>/src/dist/css/sweetalert2.css">
        <link rel="stylesheet" href="<?= BASEPATH ?>/src/dist/css/materialdesignicons.css">

        <style>
            :root {
                --p-color: <?= FCOLOR ?>;
            }
        </style>

        <script>
            const basepath = "<?= BASEPATH ?>";
        </script>
    </head>

    <?php
    /**
     * For themes
     */
    $bodyclass = (logged_in() == true) ? DB::queryFirstRow("SELECT `theme` FROM `users` WHERE `email` = %s", hash__($_COOKIE['_loggedin__hash'], "decrypt"))["theme"] : 'logged-out';
    ?>

    <body class="<?= $bodyclass ?>">
        <header id="header">
            <div class="start">
                <a href="<?= BASEPATH ?>">
                    <img id="logo" src="<?= BASEPATH ?>/uploads/main/logo.png" alt="<?= FNAME ?> logo">
                </a>
                <input type="text" id="search" placeholder="Search">
            </div>
            <div class="end">
                <?php if (!logged_in()) {
                ?>
                    <a href="<?= BASEPATH ?>/pages/login.php" data-no-instant class="btn ghost">Login</a>
                    <a href="<?= BASEPATH ?>/pages/sign-up.php" data-no-instant class="btn">Sign Up</a>
                <?php
                } else {
                ?>
                    <a href="<?= BASEPATH ?>/pages/post-new.php" data-no-instant class="btn lg">Create Post</a>
                    <a href="<?= BASEPATH ?>/pages/dashboard.php/notifications" class="btn lg ghost rounded"><i class="mdi mdi-bell"></i></a>
                    <a href="<?= BASEPATH ?>/pages/dashboard.php" class="btn lg ghost rounded"><i class="mdi mdi-account"></i></a>
                <?php } ?>
            </div>
        </header>

        <div class="container-fluid">
        <?php
        return ob_get_clean();
    }

    /**
     * 
     * Footer
     * 
     */
    function show_footer($additional = "")
    {
        ob_start(); ?>
        </div>
        <footer id="footer">
            <?php
            $links = DB::query("SELECT * FROM links");

            foreach ($links as $link) {
            ?>
                <a class="btn small ghost" href="<?= $link["value"] ?>">
                    <i alt="Icon" class="list-item-icon mdi mdi-<?= $link["icon"] ?>"></i>
                    <span class="list-item-title"><?= $link["title"] ?></span>
                </a>
            <?php
            }
            ?>
            <div class="p-2"></div>
            <p>&copy; <?= date("Y"); ?>
                <a href="<?= BASEPATH ?>"><?= FNAME ?></a> All rights reserved
            </p>
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