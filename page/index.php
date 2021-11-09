<?php
require "../init.php";

if (empty($_GET['route'])) {
    require "../pages/404.php";
}

$page = DB::queryFirstRow("SELECT * FROM pages WHERE title = %s",$_GET['route']);
if (!isset($page['title'])) {
    require "../pages/404.php";
}
else {
    echo show_header($page['title2']);
    echo $page['content'];
    echo show_footer();
}