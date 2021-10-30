<?php if (!defined('D')) {
    die();
} ?>

<?php
echo show_header("Home");

// string, integer, and decimal placeholders
$results = DB::query("SELECT * FROM posts");

foreach ($results as $row) {
    ?>
    <div class="post-box">
        <a href="<?=BASEPATH?>/<?=str_replace(" ","-",$row['title']);?>-<?=$row['purl']?>"><?=$row['title']?></a>
    </div>
    <?php
}


echo show_footer(); 
?>

