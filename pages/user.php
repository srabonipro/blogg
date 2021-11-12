<?php
if (!defined("D") and !isset($_POST["getprofiledata"]) and !isset($_POST["uid"]) and !isset($_POST["follow"])) {
    die();
}

if (isset($_POST["getprofiledata"]) and $_POST["uid"]) {
    require "../init.php";
    /**
     * Get profile data
     */
    header("Cache-Control: max-age=2592000");
    $account = $_account = DB::queryFirstRow("SELECT * FROM users WHERE id=%s", $_POST["uid"]);
    $s = $name = $color = $avatar = $profile = $posts = $about = "";
    if (!isset($account["id"])) {
        $s = false;
    } else {
        foreach ($account as $key => $value) {
            $account[$key] = htmlspecialchars($value);
        }
        $name = $account["username"];
        $profile = $account["uname"];
        $posts = count(DB::query("SELECT id FROM posts WHERE creator=%s", $account["id"]));
        $about = $account["meta"];
        $color = $account["color"];
        $avatar = get_gravatar($account["email"]);
        $s = true;
    }
    $account = array(
        "success" => $s,
        "name" => $name,
        "profile" => BASEPATH . "/account/" . $profile,
        "color" => $color,
        "avatar" => $avatar,
        "posts" => $posts,
        "about" => $about,
    );

    echo json_encode($account);
    die();
} elseif (isset($_POST["follow"]) and isset($_POST["uid"])) {
    require "../init.php";
    /**
     * Follow user
     */

    $s = follow_user($_POST["uid"]);

    if (isset($s["status"])) {
        $st = $s["status"];
        $s = true;
    } else {
        $s = false;
    }


    $stats = array(
        "success" => $s,
        "status" => $st,
    );

    echo json_encode($stats);
    die();
}
$account = DB::queryFirstRow("SELECT * FROM users WHERE uname=%s", get_route_url($_GET["route"]));
if (!isset($account["id"])) {
    require "../pages/404.php";
    die();
} else {
    $account = $account;
    foreach ($account as $key => $value) {
        $account[$key] = htmlspecialchars($value);
    }
}

?>
<?php
echo show_header($account["username"] . " on " . FNAME);
?>
<div class="p-2"></div>
<div class="container">
    <div class="row">
        <div class="col-12">
            <div class="profile" style="--color:<?= $account["color"] ?>">
                <div class="top">
                    <div class="avatar">
                        <img src="<?= get_gravatar(htmlspecialchars($account["email"])); ?>" alt="Profile Picture">
                    </div>
                </div>
                <div class="bottom">
                    <div class="name">
                        <h1><?= $account["username"] ?></h1>
                    </div>
                    <?php
                    if (!empty($account["meta"])) {
                        echo '<div class="description">
                            <p>' . $account["meta"] . '</p>
                        </div>';
                    }
                    ?>
                    <div class="stats">
                        <?php
                        if (!empty($account["job"])) {
                        ?>
                            <div class="stat">
                                <div class="number"><i class="mdi mdi-rename-box"></i></div>
                                <div class="text"><?= $account["job"] ?> <?= (!empty($account["company"]) ? " at " . $account["company"] : "") ?></div>
                            </div>
                        <?php
                        }
                        ?>
                    </div>
                    <?php
                    $fbtext = "Follow";
                    if (logged_in()) {
                        $user = DB::queryFirstRow("SELECT * FROM users WHERE email = %s", hash__($_COOKIE['_loggedin__hash'], "decrypt"));
                        if ($user["email"] == $account["email"]) {
                            $fbtext = "Edit profile";
                        }
                    }
                    if (is_following($account["id"])) {
                        $fbtext = "Unfollow";
                    }
                    ?>
                    <a <?= ($fbtext == "Edit profile") ? " href=\"" . BASEPATH . "/pages/dashboard.php/account\" " : "data-follow='" . $account["id"] . "'" ?> class="btn mt-3 mb-2 w-100"><?= $fbtext ?></a>
                </div>
            </div>
        </div>
    </div>
    <div class="row">
        <div class="col-4 mt-3">
            <?php
            /**
             * Social Media
             */
            if (
                !empty($account["facebook"]) or
                !empty($account["twitter"]) or
                !empty($account["instagram"]) or
                !empty($account["youtube"]) or
                !empty($account["github"])

            ) {
            ?>
                <div class="box mb-2">
                    <h2>Find me on</h2>
                    <?php
                    if (!empty($account["facebook"])) {
                    ?>
                        <p><span class="muted">Facebook:</span> <?= htmlspecialchars($account["facebook"]) ?></p>
                    <?php
                    }
                    ?>
                    <?php
                    if (!empty($account["twitter"])) {
                    ?>
                        <p><span class="muted">Twitter:</span> <?= htmlspecialchars($account["twitter"]) ?></p>
                    <?php
                    }
                    ?>
                    <?php
                    if (!empty($account["instagram"])) {
                    ?>
                        <p><span class="muted">Instagram:</span> <?= htmlspecialchars($account["instagram"]) ?></p>
                    <?php
                    }
                    ?>
                    <?php
                    if (!empty($account["youtube"])) {
                    ?>
                        <p><span class="muted">Youtube:</span> <?= htmlspecialchars($account["youtube"]) ?></p>
                    <?php
                    }
                    ?>
                    <?php
                    if (!empty($account["github"])) {
                    ?>
                        <p><span class="muted">Github:</span> <?= htmlspecialchars($account["github"]) ?></p>
                    <?php
                    }
                    ?>
                </div>
            <?php
            }
            ?>
            <?php
            /**
             * Organizations
             */
            $orgs = DB::query("SELECT * FROM orgmembers WHERE user=%s", $account["id"]);
            if (count($orgs) > 0) {
            ?>
                <div class="box mb-2">
                    <h2>Organizations</h2>
                    <?php
                    foreach ($orgs as $org) {
                        $o = DB::queryFirstRow("SELECT * FROM organizations WHERE id=%s", $org["orgid"]);
                        foreach ($o as $key => $value) {
                            $o[$key] = htmlspecialchars($value);
                        }
                    ?>
                        <div class="list">
                            <a href="<?= BASEPATH ?>/org/<?= $o["username"] ?>" class="list-item p-0">
                                <img src="<?= get_gravatar($o["email"], 40) ?>" alt="Profile Picture" class="rounded me-2">
                                <span><?= $o["name"] ?></span>
                            </a>
                        </div>
                    <?php
                    }
                    ?>
                </div>
            <?php
            }
            ?>
            <?php
            /**
             * Badges
             */
            $badges = DB::query("SELECT * FROM earnedbadges WHERE user=%s", $account["id"]);
            if (count($badges) > 0) {
            ?>
                <div class="box mb-2">
                    <h2>Badges</h2>
                    <?php
                    foreach ($badges as $badge) {
                        $b = DB::queryFirstRow("SELECT * FROM badges WHERE id=%s", $badge["badge"]);
                    ?>
                        <div class="mb-2 animated animation-fadeup box hoverable btn outlined">
                            <i style="font-size: 2rem;" class="mdi animated animation-fadeup mdi-<?= $b["icon"] ?>"></i>
                            <h3 class="animated m-0 ms-1 me-1 animation-fadeup"><?= $b["name"] ?></h3>
                        </div>
                    <?php
                    }
                    ?>
                </div>
            <?php
            }
            ?>
            <?php
            /**
             * Stats
             */
            $postscreated = DB::query("SELECT `id` FROM posts WHERE creator=%s", $account["id"]);
            $postscreated = count($postscreated);

            $followers = count(DB::query("SELECT `user` FROM followers WHERE following=%s", $account["id"]));

            $following = count(DB::query("SELECT `user` FROM followers WHERE user=%s", $account["id"]));

            ?>
            <div class="box mb-2">
                <p class="btn black ghost p-0 mb-2"><?= $postscreated ?> posts created</p>
                <br>
                <p class="btn black ghost p-0 mb-2"><?= $followers ?> follower<?= ($followers > 1) ? "s" : "" ?></p>
                <br>
                <p class="btn black ghost p-0 mb-2"><?= $following ?> following</p>
            </div>
        </div>
        <div class="col-8 mt-3">
            <div class="box">
                <?php
                $results = DB::query("SELECT * FROM posts WHERE `creator` = %s", $account["id"]);

                if (!isset($results[0]["id"])) {
                    echo "<h2>" . htmlspecialchars(strtok($account["username"], " ")) . " hasn't created posts yet<h2>";
                } else {
                    echo "<h2>Posts created by " . htmlspecialchars(strtok($account["username"], " ")) . "<h2><div class='p-2'></div>";
                }


                foreach ($results as $row) {
                ?>
                    <div class="post-box">
                        <a href="<?= BASEPATH ?>/post/<?= str_replace(" ", "-", $row['title']); ?>-<?= $row['purl'] ?>"><?= $row['title'] ?></a>
                    </div>
                <?php
                }
                ?>
            </div>
        </div>
    </div>
</div>
<?php
ob_start();
?>
<script src="https://js.hcaptcha.com/1/api.js"></script>
<script src="<?= BASEPATH ?>/src/dist/js/login.js"></script>
<?php
$footer = ob_get_clean();
echo show_footer($footer);
?>