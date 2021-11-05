<?php
header("HTTP/1.0 404 Not Found");
echo show_header("404 Not Found ")
?>

<div style="height: 60vh;display: flex;align-items: center;justify-content: center;">
    <div style="text-align: center;">
        <h1>404</h1>
        <hr>
        <p class="text-muted">It seems that we can't find what you are looking for :/</p>
    </div>
</div>

<?php
echo show_footer();
die();
