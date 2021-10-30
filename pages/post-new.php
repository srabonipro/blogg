<?php
require "../init.php";
?>
<?php
/**
 * Redirect if not logged in
 */
if (!logged_in()) {
    header("Location: " . BASEPATH);
}
else if (!completed_setup()) {
    echo show_header("Error");
    echo "<div class=\"notification error\"> Please complete the setup in the dashboard to create a post.</div>";
    echo show_footer();
    die();
}
?>
<?php
/**
 * API's
 */
/**
 * Check if POST post there
 */
if (
    isset($_POST['post']) and
    isset($_POST['t']) and
    isset($_POST['c']) and
    isset($_POST['ta'])
) {

    $title = $_POST["t"];
    $title = preg_replace('/[^\da-z ]/i', '',  $title);
    $content = $_POST["c"];
    $tags = $_POST["ta"];
    $m = $purl = "";

    /**
     * 
     * Validation rules
     * 
     */

    $v = new Valitron\Validator(
        array(
            'title' => $title,
            'content' => $content,
            'tags' => $tags
        )
    );

    $v->rule('required', 'title');
    $v->rule('required', 'content');
    $v->rule('required', 'tags');

    /**
     * 
     * Check if valid
     * 
     */

    if ($v->validate()) {
        $tagslength = explode(",", $tags);

        if (count($tagslength) > 4) {
            $s = false;
            $m = "Too much tags. Only 4 allowed";
        } else {
            $email = hash__($_COOKIE["_loggedin__hash"], "decrypt");
            $id = DB::queryFirstRow("SELECT * FROM users WHERE email=%s", $email);
            $a = rand_id(4);
            try {
                DB::insert('posts', [
                    'id' => $a . md5($title),
                    'title' => $title,
                    'purl' => $a,
                    'tags' => $tags,
                    'content' => $content,
                    'creator' => $id["id"]
                ]);
                $s = true;
                $purl = BASEPATH . "/" . str_replace(" ", "-", $title) . "-" . $a;
            } catch (Exception $e) {
                $s = false;
                $m = "Unknown Error";
            }
        }
    } else {
        /**
         * Validation errors
         */
        $s = false;
        ob_start();
        print("<h2>Fix these errors</h2>");
        foreach ($v->errors() as $error) {
            foreach ($error as $value) {
                print("<p>" . $value . "</p>");
            }
        }
        $m = ob_get_clean();
    }

    /**
     * 
     * Shows the data
     * 
     */

    $data = array(
        "success" => $s,
        "message" => $m,
        "posturl" => $purl
    );

    header('Content-Type: application/json; charset=utf-8');
    echo json_encode($data);
    /**
     * Do and die
     */
    die();
}
/**
 * END API
 */
echo show_header("Create post");
?>
<div id="post">
    <h1>
        <input type="text" id="title" style="all:inherit" placeholder="Post Title">
    </h1>
    <h4>
        <input type="text" id="tags" style="all:inherit" placeholder="T, a, g, s">
    </h4>

    <button class="btn ghost small" id="previewbtn">Preview</button>
    <br>

    <textarea id="editor">

    </textarea>

    <hr>

    <div class="btn-group">
        <button class="btn" id="publishbtn">Publish</button>
        <button class="btn ghost" id="savedraft">Save Draft</button>
        <button class="btn ghost" id="options"><i class="mdi mdi-cog"></i></button>
    </div>
</div>

<?php
ob_start();
?>
<script src="<?= BASEPATH ?>/src/dist/js/create-post.js"></script>
<?php
$footer = ob_get_clean();
echo show_footer($footer);
?>