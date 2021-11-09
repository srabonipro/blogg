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
            <a class="list-item" href="<?= BASEPATH ?>/pages/dashboard.php/account">
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
    <div id="homepage-middle" class="col-xs-12 col-sm-7">
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
                <?php
                /**
                 * Get Post user data
                 */
                $account = DB::queryFirstRow("SELECT * FROM users WHERE id=%s", $row['creator']);
                ?>
                <div class="post-box">
                    <div class="profile-small">
                        <div class="col-2">
                            <img src="<?= get_gravatar(htmlspecialchars($account["email"])); ?>" class="rounded" title="<?= htmlspecialchars($account["username"]); ?> Profile Image">
                        </div>
                        <div class="col-10">
                            <a data-username="<?= htmlspecialchars($account["id"]) ?>" href="<?= BASEPATH . "/account/" . $account["uname"] ?>">
                                <?= htmlspecialchars($account["username"]) ?>
                            </a>
                            <p class="muted"><sup>
                                    <?= time_elapsed_string($row['date']); ?>
                                </sup></p>
                        </div>
                    </div>
                    <div></div>
                    <?php
                    /* Post URL */
                    $post_url = BASEPATH . "/post/" . str_replace(" ", "-", $row['title']) . "-" . $row['purl'];
                    ?>
                    <a href="<?= $post_url  ?>"><?= $row['title'] ?></a>
                    <div class="post-box-bottom">
                        <?php
                        $tags = array_unique_multidimensional(explode(",", $row["tags"]));
                        foreach ($tags as $tag) {
                        ?>
                            <a href="<?= BASEPATH ?>/tag/<?= urlencode(htmlspecialchars($tag)) ?>" class="btn muted p-0 ghost small">#<?= htmlspecialchars($tag) ?></a>
                        <?php } ?>
                        <div class="p-2"></div>
                        <a href="<?= $post_url ?>/#comments" class="btn small muted black ps-0 pe-0 ghost">
                            <i class="mdi mdi-comment-outline me-2"></i>
                            Add Comment
                        </a>
                        <?php
                        /**
                         * Reactions
                         */
                        $reactions = DB::query("SELECT `forid` FROM reactions WHERE forid =%s", $row['id']);
                        ?>
                        <a href="<?= $post_url ?>" class="ms-2 btn small muted black ps-0 pe-0 ghost">
                            <i class="mdi mdi-heart-outline me-2"></i>
                            <?= count($reactions) ?> Reactions
                        </a>
                    </div>
                </div>
            <?php
            }
            ?>
        </div>
    </div>
    <div id="homepage-right" class="col-xs-12 col-sm-3">
    <button id="myButton">My Button</button>
        <?php
        $results = DB::query("SELECT `content` FROM sideboxes WHERE `location` = 'homepage_right'");

        foreach ($results as $row) {
            echo "<div class='box mb-3'>" . $row['content'] . "</div>";
        }
        ?>
        <?php
        if (!logged_in()) {
        ?>
            <div class="box" style="position: sticky;top: 75px;">
                <?php
                $config = DB::queryFirstRow("SELECT * FROM config WHERE `name`='ftagline'");
                ?>

                <h4 class="mb-1"><a href="<?= BASEPATH ?>"><?= FNAME ?></a></h4>
                <h6 class="m-0">
                    <?= $config["value"] ?>
                </h6>
                <div class="p-2"></div>
                <p class="muted">Create your account or join the community</p>
                <div class="p-2"></div>

                <a href="<?= BASEPATH ?>/pages/sign-up.php" class="btn">Sign Up</a>
                <a href="<?= BASEPATH ?>/pages/login.php" class="btn ghost">Login</a>
            </div>
        <?php
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