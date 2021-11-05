<?php 
require "../init.php";
/**
 * Check if empty
 */
if(empty($_GET['route'])){
    require "../pages/404.php";
}
else {
    // escape all special characters
    $tagname = htmlspecialchars($_GET['route']);

    ?>
    <?= show_header("Tag ".$tagname); ?>
    <?php
    echo show_footer();
}