<?php if (!defined('D')) {
    die();
} ?>
<?= show_header("Home"); ?>
<div class="p-2"></div>
<div id="homepage" class="row">
    <div id="homepage-left" class="col-xs-12 col-sm-2">
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
            // Show additional links 
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
        // Show the boxes
        $results = DB::query("SELECT `content` FROM sideboxes WHERE `location` = 'homepage_left'");

        foreach ($results as $row) {
            echo "<div class='box mb-3'>" . $row['content'] . "</div>";
        }
        ?>
    </div>
    <div id="homepage-middle" class="col-xs-12 col-sm-8">
        <?php
        /**
         * Find the active page
         */
        if (isset($_GET['route'])) {
            if ($_GET['route'] == "feed") {
                $active = "feed";
            }
            /**
             * Trending page
             */
            else if ($_GET['route'] == "trending" or strpos($_GET['route'], "trending") !== false) {
                $active = "trending";
            }
            /**
             * Latest posts page
             */
            else if ($_GET['route'] == "new" or strpos($_GET['route'], "new") !== false) {
                $active = "new";
            }
            /**
             * Random posts page
             */
            else if ($_GET['route'] == "random" or strpos($_GET['route'], "random") !== false) {
                $active = "random";
            }
            /**
             * 404 page
             */
            else {
                require "../pages/404.php";
                die();
            }
        } else {
            $active = "feed";
        }
        ?>
        <div id="homepage-toggle">
            <a href="<?= BASEPATH ?>/" class="btn <?= ($active == "feed") ? "" : "ghost" ?>  small">Feed</a>
            <a href="<?= BASEPATH ?>/filter/trending" class="btn small <?= ($active == "trending") ? "" : "ghost" ?>">Trending</a>
            <a href="<?= BASEPATH ?>/filter/new" class="btn small <?= ($active == "new") ? "" : "ghost" ?>">New</a>
            <a href="<?= BASEPATH ?>/filter/random" class="btn small <?= ($active == "random") ? "" : "ghost" ?>">Random</a>
        </div>
        <div>
            <?php
            /**
             * Show posts
             */
            if (!logged_in()) {
                $results = DB::query("SELECT * FROM posts");
            } else {
                /**
                 * Customize the feed
                 */
                $results = DB::query("SELECT * FROM posts");
            }
            foreach ($results as $row) {
            ?>
                <div class="post-box">
                    <?php
                    /* Post URL */
                    $post_url = BASEPATH . "/post/" . str_replace(" ", "-", $row['title']) . "-" . $row['purl'];
                    ?>
                    <a href="<?= $post_url  ?>"><?= $row['title'] ?></a>
                    <?php
                    /**
                     * Get Post user data
                     */
                    $account = DB::queryFirstRow("SELECT * FROM users WHERE id=%s", $row['creator']);
                    ?>
                    <div class="post-box-bottom">
                        <?php
                        $tags = array_unique_multidimensional(explode(",", $row["tags"]));
                        foreach ($tags as $tag) {
                        ?>
                            <a href="<?= BASEPATH ?>/tag/<?= urlencode(htmlspecialchars($tag)) ?>" class="btn p-0 ghost small">#<?= htmlspecialchars($tag) ?></a>
                        <?php } ?>
                        <div class="profile-small">
                            <div class="col-2">
                                <img src="<?= get_gravatar(htmlspecialchars($account["email"])); ?>" class="rounded" title="<?= htmlspecialchars($account["username"]); ?> Profile Image">
                            </div>
                            <div class="col-10">
                                <a href="<?= BASEPATH . "/account/" . $account["uname"] ?>">
                                    <?= htmlspecialchars($account["username"]) ?>
                                </a>
                            </div>
                        </div>
                    </div>
                </div>
            <?php
            }
            ?>
        </div>
    </div>
    <div id="homepage-right" class="col-xs-12 col-sm-2">
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