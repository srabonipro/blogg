<?php
if (!defined("D")) {
    die();
} else {
    $str = $_GET['postname'];
    $pattern = "/.{4}$/";
    if (!preg_match($pattern, $str)) {
        require "pages/404.php";
        die();
    } else {
        /**
         * Post Start
         */
        $str = $_GET['postname'];
        $pattern = "/-.{4}$/";
        $postnamea = preg_replace($pattern, "", $str);
        $postname = str_replace("-", " ", $postnamea);
        $postid = str_replace($postnamea, "", $str);
        $postid = str_replace("-", "", $postid);

        $post = DB::queryFirstRow("SELECT * FROM posts WHERE title=%s AND purl=%s", $postname, $postid);

        if (!isset($post["creator"])) {
            require "pages/404.php";
            die();
        } else {
            ob_start();
?>
            <meta name="title" content="<?= $post["title"] ?>">
            <meta name="keywords" content="<?= $post["tags"] ?>">
            <meta name="robots" content="index, follow">
            <meta http-equiv="Content-Type" content="text/html; charset=utf-8">
            <meta name="language" content="English">
            <meta name="revisit-after" content="7 days">
            <meta name="author" content="<?= $post["creator"] ?>">

            <?php
            $additional = ob_get_clean();
            echo show_header($post["title"], $additional);
            /**
             * Start Post
             */
            $user = DB::queryFirstRow("SELECT * FROM users WHERE id = %s", $post["creator"]);
            ?>
            <article id="article-<?= $post["id"] ?>" class="row">
                <main class="col-md-9" id="post-content">
                    <h1 id="title">
                        <?= htmlspecialchars($post["title"]) ?>
                    </h1>
                    <?php echo $Parsedown->text($post["content"]); ?>
                </main>
                <aside class="col-md-3" id="post-sidebar" style="height: max-content;position:sticky;">
                    <div class="box">
                        <img src="<?= get_gravatar(htmlspecialchars($user["email"])); ?>" class="rounded" alt="<?= htmlspecialchars($user["username"]); ?> Profile Image">
                        <h2><a href="<?= BASEPATH ?>/user.php/?id=<?= $user["id"] ?>"><?= htmlspecialchars($user["username"]); ?></a></h2>
                        <p><?= htmlspecialchars($user['meta']); ?></p>

                        <?php
                        if (logged_in()) {
                            if (!$user['email'] === hash__($_COOKIE['_loggedin__hash'], "decrypt")) {
                        ?>
                                <br>
                                <button data-follow="" class="btn w-100" style="width: 100%;">Follow</button>
                        <?php
                            }
                        }
                        ?>
                    </div>
                    <div class="p-2"></div>
                    <div class="box" style="text-align: left;">
                        <?php
                        if (logged_in()) {
                            if ($user['email'] === hash__($_COOKIE['_loggedin__hash'], "decrypt")) {
                                /**
                                 * It's the author
                                 */
                        ?>
                                <h2>Post Settings</h2>
                                <a href="" class="btn small mb-5">Edit Post</a>
                            <?php
                            } else {
                                /**
                                 * Show reactions
                                 */
                            ?>
                                <button class="reaction"><i class="mdi mdi-heart"></i></button>
                                <button class="reaction"><i class="mdi mdi-unicorn-variant"></i></button>
                                <button class="reaction"><i class="mdi mdi-content-save"></i></button>
                        <?php
                            }
                        }
                        ?>
                        <br>
                        <h2>Share this</h2>
                        <?= share_buttons(); ?>
                    </div>
                </aside>
            </article>

            <?php
            ob_start();
            ?>
            <script src="<?= BASEPATH ?>/src/dist/js/create-post.js"></script>
<?php
            $footer = ob_get_clean();
            echo show_footer($footer);
        }
    }
}
