<?php
require "../init.php";
?>
<?php
/**
 * redirect if logged in
 */
if (logged_in()) {
    header("Location: " . BASEPATH);
    die();
}
?>
<?php
echo show_header("Login");
?>

<div id="user-auth-box">
    <h1>Login</h1>
    <p class="text-muted">Type your email below</p>
    <form action="!" method="POST" id="form">
        <div class="input-container">
            <label for="email" class="input-label">Email</label>
            <input type="email" class="input" id="email" name="email" required min="4">
        </div>

        <div class="input-container">
            <label>Please Prove your humanity</label>
            <div id="h-captcha" data-sitekey="<?= CAPCLIENTID ?>"></div>
        </div>

        <button type="submit" class="btn">Email me the login link</button>
    </form>
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