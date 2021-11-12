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
 * Get notifications count
 * 
 */
function get_notifications_count()
{
    /**
     * Check if logged in
     */
    if (logged_in()) {
        /**
         * Decrypt Email
         */
        $user = DB::queryFirstRow("SELECT `id` FROM users WHERE email=%s", hash__($_COOKIE['_loggedin__hash'], "decrypt"));

        $notifications = DB::query("SELECT `id` FROM notifications WHERE user=%s AND `seen`='false'", $user["id"]);

        $count = count($notifications);

        if ($count == 0) {
            $count = 0;
        } else {
            $count = $count;
        }
    } else {
        $count = 0;
    }
    return $count;
}
/**
 * 
 * 
 * Follow / Unfollow user
 * 
 */
function follow_user($user)
{
    /**
     * Check if logged in
     */
    if (!logged_in()) {
        return "Not logged in";
    } else {
        /**
         * Decrypt Email and get user data
         */
        $current = DB::queryFirstRow("SELECT * FROM users WHERE email=%s", hash__($_COOKIE['_loggedin__hash'], "decrypt"));

        /**
         * Following user data
         */
        $following = DB::queryFirstRow("SELECT * FROM users WHERE id=%s", $user);

        /**
         * Sanitize
         */
        foreach ($following as $key => $value) {
            $following[$key] = htmlspecialchars($value);
        }

        foreach ($current as $key => $value) {
            $current[$key] = htmlspecialchars($value);
        }
        /**
         * Account not found
         */
        if (!isset($following["id"])) {
            return "Account not found";
        }
        /**
         * Trying to follow self
         */
        elseif ($following["id"] == $current["id"]) {
            return "Method not allowed";
        } else {
            /**
             * Check if already following
             */
            $following_user = DB::queryFirstRow("SELECT * FROM `followers` WHERE `user`=%s AND `following`=%s LIMIT 1", $current["id"], $following["id"]);

            /**
             * Already following
             */
            if (isset($following_user["user"])) {
                DB::query('DELETE FROM `followers` WHERE `user`=%s AND `following`=%s', $current["id"], $following["id"]);
                return array("status" => "Follow");
            }

            /**
             * Not following
             */
            else {
                DB::insert('followers', array(
                    'user' => $current["id"],
                    'following' => $following["id"]
                ));
                $message = "
                <div>
                <img class=\"rounded\" src=\"".get_gravatar($current["email"],40)."\">
                <br/>
                  <a href=\"".BASEPATH.'\\'.'user'.'\\'.$current["uname"]."\" data-username=\"".$current["id"]."\">
                  ".$current["username"]."
                  </a>
                   Started following you!
                </div>
                ";
                add_notification($message, $following["id"]);
                return array("status" => "Unfollow");
            }
        }
    }
}
/**
 * 
 * 
 * Check if follows
 * 
 */
function is_following($who, $user = "")
{
   if(empty($user)) {
       if(!logged_in()) {
           return false;
       }
       else {
           $user = DB::queryFirstrow("SELECT `id` FROM `users` WHERE `email`=%s", hash__($_COOKIE["_loggedin__hash"],"decrypt"));
       }
   }

    $follower = DB::queryFirstrow("SELECT `id` FROM `users` WHERE `id`=%s", $who);
    
    /**
     * Checks if accounts exist
     */
    if (!isset($follower["id"]) or !isset($user["id"])) {
        return false;
    } 
    else {
        /**
         * Following
         */
        $follows = DB::queryFirstrow("SELECT * FROM `followers` WHERE `user` = %s AND `following` = %s", $user["id"], $follower["id"]);

        if(isset($follows["user"])) {
            return true;
        }
    }
    return false;
}
/**
 * 
 * 
 * Get routing URL
 * 
 */
function get_route_url($url)
{
    $url = rtrim($url, '/');
    $url = filter_var($url, FILTER_SANITIZE_URL);
    return $url;
}
/**
 * 
 * 
 * Obfuscate email
 * 
 */
function obfuscate_email($email)
{
    $em   = explode("@", $email);
    $name = implode('@', array_slice($em, 0, count($em) - 1));
    $len  = floor(strlen($name) / 2);

    return substr($name, 0, $len) . str_repeat('*', $len) . "@" . end($em);
}
/**
 * 
 * Time elapsed
 * 
 */
function time_elapsed_string($datetime, $full = false)
{
    $now = new DateTime;
    $ago = new DateTime($datetime);
    $diff = $now->diff($ago);

    $diff->w = floor($diff->d / 7);
    $diff->d -= $diff->w * 7;

    $string = array(
        'y' => 'year',
        'm' => 'month',
        'w' => 'week',
        'd' => 'day',
        'h' => 'hour',
        'i' => 'minute',
        's' => 'second',
    );
    foreach ($string as $k => &$v) {
        if ($diff->$k) {
            $v = $diff->$k . ' ' . $v . ($diff->$k > 1 ? 's' : '');
        } else {
            unset($string[$k]);
        }
    }

    if (!$full) $string = array_slice($string, 0, 1);
    return $string ? implode(', ', $string) . ' ago' : 'just now';
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
 * Email template
 * 
 */
function email_template($title, $content, $heading)
{
    ob_start(); ?>
    <!DOCTYPE html>
    <html lang="en">

    <head>
        <meta charset="UTF-8">
        <meta http-equiv="X-UA-Compatible" content="IE=edge">
        <meta name="viewport" content="width=device-width, initial-scale=1.0">
        <title><?= $title . " " . FNAME ?></title>
    </head>

    <body style="background: #d7d9de;">
        <table style='width: 80%;margin: 40px auto;font-family:"Roboto","Verdana",sans-serif;background: white;min-width: 300px;border-radius: 4px;padding: 10px 30px;'>
            <tbody>
                <tr>
                    <td>
                        <h1 style="font-size: 30px;font-weight: 500;"><?= $heading ?></h1>
                    </td>
                </tr>
                <tr>
                    <td style="font-size: 16px;font-weight: 500;word-break:break-all">
                        <?= $content ?>
                    </td>
                </tr>
                <tr>
                    <td>
                        <p style="color: #5c5c5c;"><?= FNAME ?></p>
                    </td>
                </tr>
            </tbody>
        </table>


    </body>

    </html>
<?php
    return ob_get_clean();
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
 * Generate placeholder text
 * 
 */
function placeholder_text($length = 10)
{
    $quotes = array(
        "Be yourself; everyone else is already taken.",
        "I love deadlines. I love the whooshing noise they make as they go by.",
        "The most important thing is to enjoy your life - to be happy - it's all that matters.",
        "I think you can have a lot of fun with a book. After all, you're reading it.",
        "The truth is, everyone is going to hurt you. You just got to find the ones worth suffering for.",
        "I love deadlines. I like the whooshing noise they make as they go by.",
        "If you look at what you have in life, you'll always have more. If you look at what you don't have in life, you'll never have enough.",
        "Life is what happens when you're busy making other plans.",
        "The most common way people give up their power is by thinking they don't have any.",
        "The best revenge is massive success.",
        "People often say that motivation doesn't last. Well, neither does bathing.  That's why we recommend it daily.",
        "Life shrinks or expands in proportion to one's courage.",
        "Silence is golden"
    );
    return $quotes[array_rand($quotes)];
}
/**
 * 
 * 
 * Add notification
 * 
 */
function add_notification($message, $user_id = "")
{
    /**
     * No account & not logged in
     */
    if ($user_id == "" and !logged_in()) {
        return false;
        exit();
    }
    /**
     * Logged in
     */
    elseif ($user_id == "" and logged_in()) {
        $account = DB::queryFirstrow(
            "SELECT * FROM `users` WHERE email=%s",
            hash__($_COOKIE['_loggedin__hash'], "decrypt")
        );
        $user_id = $account["id"];
        $message = $message;
    }
    /**
     * Not / logged in 
     */
    else {
        $account = $user_id;
        $message = $message;
    }
    try {
        DB::insert('notifications', [
            'user' => $user_id,
            'message' => $message,
            "date" => date('d.m.Y H:i:s'),
            'seen' => "false",
            "id" => rand_id(5) . sha1(time()),
        ]);
        return true;
    } catch (Exception $e) {
        return false;
    }
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
 * 
 * Nonce    
 * 
 */
/**
 * Generate nonce
 */
function nonce_generator()
{
    $nonce = md5(uniqid(rand(), true));
    $_SESSION["nonce"] = $nonce;
    return $nonce;
}
/**
 * Verify nonce
 */
function nonce_verify($nonce)
{
    if (isset($_SESSION["nonce"])) {
        if ($_SESSION["nonce"] == $nonce) {
            return true;
        } else {
            return false;
        }
    } else {
        return false;
    }
}
/**
 * 
 * 
 * Add badge to user
 * 
 */
function add_badge($user_id, $badge_id)
{
    try {
        if (count(DB::query("SELECT * FROM `earnedbadges` WHERE `user`=%s AND `badge`=%s", $user_id, $badge_id)) == 0) {
            $badge = DB::queryFirstrow("SELECT * FROM `badges` WHERE `id`=%s", $badge_id);
            if (count($badge) == 0) {
                return false;
            } else {
                DB::insert('earnedbadges', [
                    'user' => $user_id,
                    'badge' => $badge_id,
                    "date" => date('d.m.Y H:i:s'),
                ]);
                add_notification("<h3>You earned the " . $badge["name"]  . " badge!</h3><p>" . $badge["about"] . "</p>", $user_id);
                return true;
            }
        } else {
            return false;
        }
    } catch (Exception $e) {
        return false;
    }
}
/**
 * 
 * 
 * Convert links into special content
 * 
 */
function convert_links_to_special($string) {
    /**
     * Convert links to special content
     * Don't convert links that are already in special content
     * You can add more special content by adding more regular expressions
     * WARNING! This is a very advanced feature and you should only use it if you know what you're doing 
     */

    /**
     * Youtube
     */
    $string = preg_replace(
        "/\<a href=\"\^youtube\.([a-zA-Z0-9_]*)\"\>(.*)\<\/a\>/",
        "<iframe class=\"mt-2 mb-2\" width='100%' height='315' src='https://www.youtube.com/embed/$1' frameborder='0' loading=\"lazy\" allowfullscreen class=\"iframe-click-to-load\">
        Loading video
        </iframe>",
        $string
    );

    /**
     * Github gist
     */
    $string = preg_replace(
        "/\<a href=\"\^gist\.([a-zA-Z0-9_]*)\"\>(.*)\<\/a\>/",
        "<div data-github-gist-id=\"$1\" class=\"box\"><div class=\"loader\"></div>Loading gist</div>",
        $string
    );

    /**
     * Codepen
     */
    $string = preg_replace(
        "/\<a href=\"\^codepen\.([a-zA-Z0-9_]*)\.([a-zA-Z0-9_]*)\"\>(.*)\<\/a\>/",
        '<iframe class="mb-2 mt-2" height="400" style="width: 100%;" src="https://codepen.io/$1/embed/preview/$2?default-tab=result&theme-id=dark" frameborder="no" loading="lazy" allowtransparency="true" allowfullscreen="true"></iframe>',
        $string
    );

    /**
     * Codesandbox
     */
    $string = preg_replace(
        "/\<a href=\"\^codesandbox\.([a-zA-Z0-9_-]*)\"\>(.*)\<\/a\>/",
        '<iframe class="mb-2 mt-2" height="400" style="width: 100%;" src="https://codesandbox.io/embed/$1?fontsize=14&hidenavigation=1&theme=dark" frameborder="no" loading="lazy" allowtransparency="true" allowfullscreen="true"></iframe>',
        $string
    );

    /**
     * Tweet
     */
    $string = preg_replace(
        "/\<a href=\"\^tweet\.([a-zA-Z0-9_]*)\.([a-zA-Z0-9_]*)\"\>(.*)\<\/a\>/",
        '<blockquote class="twitter-tweet" data-dnt="true">
        <p lang="en" dir="ltr"><a href="https://twitter.com/$1/status/$2">Loading tweet</a>
    </blockquote>
    <script async src="https://platform.twitter.com/widgets.js" charset="utf-8"></script>',
        $string
    );

    return $string;
}
/**
 * 
 * 
 * Validate hex color
 * 
 */
function validate_hex_color($color)
{
    return preg_match('/^#[a-f0-9]{6}$/i', $color);
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
        <a href="<?= current_url() ?>/#main-content" id="skip-to-content">Skip To Content</a>
        <div id="snackbar-container"></div>
        <header id="header">
            <div class="start">
                <a href="<?= BASEPATH ?>">
                    <img id="logo" src="<?= BASEPATH ?>/uploads/main/logo.png" alt="<?= FNAME ?> logo">
                </a>
                <input type="text" id="search" placeholder="Search">
            </div>
            <div class="end">
                <?php if (!logged_in()) {
                    /**
                     * If not logged in
                     */
                ?>
                    <a href="<?= BASEPATH ?>/pages/login.php" data-no-instant class="btn ghost">Login</a>
                    <a href="<?= BASEPATH ?>/pages/sign-up.php" data-no-instant class="btn">Sign Up</a>
                <?php
                } else {
                    /**
                     * If logged in
                     */

                    /**
                     * Check notifications
                     */
                    if (get_notifications_count() == 0) {
                        $not = "";
                    } elseif (get_notifications_count() > 9) {
                        $not = 'data-notifications="9+"';
                    } else {
                        $not = 'data-notifications="' . get_notifications_count() . '"';
                    }
                ?>
                    <a href="<?= BASEPATH ?>/pages/post-new.php" data-no-instant class="btn lg">Create Post</a>
                    <a href="<?= BASEPATH ?>/pages/dashboard.php/notifications" class="btn lg ghost rounded" <?= $not ?>><i class="mdi mdi-bell"></i></a>
                    <a href="<?= BASEPATH ?>/pages/dashboard.php" class="btn lg ghost rounded"><i class="mdi mdi-account"></i></a>
                <?php } ?>
            </div>
        </header>

        <div class="container-fluid" id="main-content">
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
                    <?= $link["title"] ?>
                </a>
            <?php
            }
            ?>
            <div class="p-2"></div>
            <p>&copy; <?= date("Y"); ?></p>
            <?php
            $config = DB::queryFirstRow("SELECT * FROM config WHERE `name`='ftagline'");
            ?>

            <p><a href="<?= BASEPATH ?>"><?= FNAME ?></a> - <?= $config["value"] ?>.</p>
            <p>All rights reserved</p>
        </footer>
        <script src="<?= BASEPATH ?>/src/dist/js/jquery.js" data-no-instant></script>
        <script src="<?= BASEPATH ?>/src/dist/js/sweetalert2.min.js"></script>
        <script src="<?= BASEPATH ?>/src/dist/js/twemoji.min.js" data-no-instant></script>
        <script src="<?= BASEPATH ?>/src/dist/js/popper.min.js"></script>
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