<?php if (!defined('D')) {
    die();
} ?>
<?= show_header("Home"); ?>

<div id="homepage">
    <div id="homepage-left">
        <div class="list">
            <a class="list-item active" href="<?= BASEPATH ?>">
                <i src="<?= BASEPATH ?>/uploads/icon/home.png" alt="Icon" class="list-item-icon mdi mdi-home"></i>
                <span class="list-item-title">Home</span>
            </a>
            <a class="list-item" href="<?= BASEPATH ?>/pages/dashboard.php/reading-list">
                <i src="<?= BASEPATH ?>/uploads/icon/home.png" alt="Icon" class="list-item-icon mdi mdi-bookmark"></i>
                <span class="list-item-title">Saved posts</span>
            </a>
            <a class="list-item" href="<?= BASEPATH ?>/pages/dashboard.php">
                <i src="<?= BASEPATH ?>/uploads/icon/home.png" alt="Icon" class="list-item-icon mdi mdi-account"></i>
                <span class="list-item-title">Account</span>
            </a>
        </div>
    </div>
    <div id="homepage-middle">
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
    <div id="homepage-right">

    </div>
</div>

<?= show_footer(); ?>