<?php if (!defined('D')) {
    die();
} ?>
<?= show_header("Home"); ?>
<div class="p-2"></div>
<div id="homepage" class="row">
    <div id="homepage-left" class="col-2">
        <div class="list mb-3">
            <a class="list-item active" href="<?= BASEPATH ?>">
                <i alt="Icon" class="list-item-icon mdi mdi-home"></i>
                <span class="list-item-title">Home</span>
            </a>
            <a class="list-item" href="<?= BASEPATH ?>/pages/dashboard.php/reading-list">
                <i alt="Icon" class="list-item-icon mdi mdi-bookmark"></i>
                <span class="list-item-title">Saved posts</span>
            </a>
            <a class="list-item" href="<?= BASEPATH ?>/pages/dashboard.php">
                <i alt="Icon" class="list-item-icon mdi mdi-account"></i>
                <span class="list-item-title">Account</span>
            </a>
            <?php
            $links = DB::query("SELECT * FROM links");

            foreach ($links as $link) {
            ?>
                <a class="list-item" href="<?= $link["value"] ?>">
                    <i alt="Icon" class="list-item-icon mdi mdi-<?= $link["icon"] ?>"></i>
                    <span class="list-item-title"><?= $link["title"] ?></span>
                </a>
            <?php
            }
            ?>
        </div>
        <?php
        $results = DB::query("SELECT `content` FROM sideboxes WHERE `location` = 'homepage_left'");

        foreach ($results as $row) {
            echo "<div class='box mb-3'>" . $row['content'] . "</div>";
        }
        ?>
    </div>
    <div id="homepage-middle" class="col-7 c-d-n">
        <div class="loader" style="display: block !important;margin:auto"></div>
        <div id="homepage-toggle">
            <a href="#" class="btn small">Feed</a>
            <a href="#" class="btn small ghost">Trending</a>
            <a href="#" class="btn small ghost">New</a>
            <a href="#" class="btn small ghost">Random</a>
        </div>
        <?php
        $results = DB::query("SELECT * FROM posts");

        foreach ($results as $row) {
        ?>
            <div class="post-box">
                <a href="<?= BASEPATH ?>/post/<?= str_replace(" ", "-", $row['title']); ?>-<?= $row['purl'] ?>"><?= $row['title'] ?></a>
            </div>
        <?php
        }
        ?>
    </div>
    <div id="homepage-right" class="col-3">
        <?php
        $results = DB::query("SELECT `content` FROM sideboxes WHERE `location` = 'homepage_right'");

        foreach ($results as $row) {
            echo "<div class='box mb-3'>" . $row['content'] . "</div>";
        }
        ?>
    </div>
</div>

<?php
ob_start();
?>
<script src="<?= BASEPATH ?>/src/dist/js/homepage.js"></script>
<?php
$footer = ob_get_clean();
echo show_footer($footer);
?>