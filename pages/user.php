<?php
if (!defined("D")) {
    die();
}

$account = DB::queryFirstRow("SELECT * FROM users WHERE uname=%s", $_GET["route"]);
if (!isset($account["id"])) {
    require "../pages/404.php";
    die();
} else {
    $account = $account;
}
?>
<?php
echo show_header($account["username"] . " on " . FNAME);
?>
<div class="p-2"></div>
<div class="container">
    <div class="row">
        <div class="col-12">
            <div class="box">
                <img src="<?= get_gravatar(htmlspecialchars($account["email"])); ?>" class="rounded" title="<?= htmlspecialchars($account["username"]); ?> Profile Image">
                <h1><?= htmlspecialchars($account["username"]) ?></h1>
                <p><?= htmlspecialchars($account["meta"]) ?></p>
                <?php
                $fbtext = "Follow";
                if (logged_in()) {
                    $user = DB::queryFirstRow("SELECT * FROM users WHERE email = %s", hash__($_COOKIE['_loggedin__hash'], "decrypt"));
                    if ($user["email"] == $account["email"]) {
                        $fbtext = "Edit profile";
                    }
                }
                ?>
                <a <?= ($fbtext == "Edit profile") ? " href=\"".BASEPATH."/pages/dashboard.php/account\" " : "" ?> data-follow="" class="btn w-100" style="width: 100%;"><?= $fbtext ?></a>
            </div>
        </div>
    </div>
    <div class="row">
        <div class="col-4 mt-3">
            <div class="box">
                <h2>About Me</h2>
                <?= htmlspecialchars($account["meta"]) ?>
            </div>
        </div>
        <div class="col-8 mt-3">
            <div class="box">
                <?php
                $results = DB::query("SELECT * FROM posts WHERE `creator` = %s", $account["id"]);

                if (!isset($results[0]["id"])) {
                    echo "<h2>" . htmlspecialchars(strtok($account["username"], " ")) . " hasn't created posts yet<h2>";
                }
                else {
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