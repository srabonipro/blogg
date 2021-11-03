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
<div class="container">
    <div class="box">
        <img src="<?= get_gravatar(htmlspecialchars($account["email"])); ?>" class="rounded" title="<?= htmlspecialchars($account["username"]); ?> Profile Image">
        <h1><?= htmlspecialchars($account["username"]) ?></h1>
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