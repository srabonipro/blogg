<?php
if (!defined("D")) {
    die();
}

$account = DB::queryFirstRow("SELECT * FROM organizations WHERE username=%s",  get_route_url($_GET["route"]));
if (!isset($account["id"])) {
    require "../pages/404.php";
    die();
} else {
    /**
     * Sanitize the data
     */
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
                        <img src="<?php echo get_gravatar($account["email"]); ?>" alt="<?php echo $account["name"]; ?>">
                    </div>
                </div>
                <div class="bottom">
                    <div class="name">
                        <h1><?php echo $account["name"]; ?></h1>
                    </div>
                    <div class="description">
                        <p><?php echo $account["about"]; ?></p>
                    </div>
                    <div class="stats">
                        <?php
                        if (!empty($account["website"])) {
                        ?>
                            <div class="stat">
                                <div class="number"><i class="mdi mdi-web"></i></div>
                                <div class="text"><?= $account["website"] ?></div>
                            </div>
                        <?php
                        }
                        ?>
                        <?php
                        if (!empty($account["location"])) {
                        ?>
                            <div class="stat">
                                <div class="number"><i class="mdi mdi-map-marker"></i></div>
                                <div class="text"><?= $account["location"] ?></div>
                            </div>
                        <?php
                        }
                        ?>
                        <?php
                        $members = count(DB::query("SELECT * FROM orgmembers WHERE orgid=%s", $account["id"]));
                        ?>
                            <div class="stat">
                                <div class="number"><i class="mdi mdi-account"></i></div>
                                <div class="text"><?= $members ?> Member<?= ($members > 1) ? "s" : "" ?></div>
                            </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <div class="row">
        <div class="col-4 mt-3">
            <div class="box">
                <h2>Members</h2>
                <?php
                $members = DB::query("SELECT * FROM orgmembers WHERE orgid=%s", $account["id"]);

                foreach ($members as $member) {
                    $user = DB::queryFirstRow("SELECT * FROM users WHERE id=%s", $member["user"]);
                    foreach ($user as $key => $value) {
                        $user[$key] = htmlspecialchars($value);
                    }
                    echo '<a data-tooltip="'.$user["username"].'" data-username="'.$user["id"].'" href="'.BASEPATH.'/account/'.$user["uname"].'">';
                    echo '<img class="rounded me-2" src="' . get_gravatar($user["email"],40) . '" alt="' . $user["username"] . '">';
                    echo '</a>';
                }
                ?>
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