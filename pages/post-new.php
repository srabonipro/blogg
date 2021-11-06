<?php
require "../init.php";
?>
<?php
/**
 * Redirect if not logged in
 */
if (!logged_in()) {
    header("Location: " . BASEPATH);
} else if (!completed_setup()) {
    echo show_header("Error");
    echo "<div class=\"notification error\"> Please complete the setup in the dashboard to create a post.</div>";
    echo show_footer();
    die();
}
?>
<?php
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
        /**
         * Remove duplicate tags
         */
        $tags = array_unique_multidimensional(explode(",", $tags));

        /**
         * Only allow 4 tags
         */
        if (count($tags) > 4) {
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
                $purl = BASEPATH . "/post/" . str_replace(" ", "-", $title) . "-" . $a;
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
ob_start();
?>
<link rel="stylesheet" href="<?= BASEPATH ?>/src/dist/css/simplemde.min.css">
<style>
    #loading {
        height: 60vh;
        display: flex !important;
        align-items: center;
        justify-content: center;
    }

    #imagePreview {
        height: 160px;
        width: 100%;
        background-size: cover;
        background-repeat: no-repeat;
        margin-bottom: 10px;
    }
</style>
</link>
<?php
$a = ob_get_clean();
echo show_header("Create post", $a);
?>
<div id="post" class="row c-d-n">

    <div id="loading" style="display: block;">
        <div class="loader" style="display: block;"></div>
    </div>

    <div class="col-md-9">
        <div class="box">
            <h1>
                <input type="text" id="title" style="all:inherit" placeholder="Post Title">
            </h1>
            <input type="text" id="imagdata" hidden>
            <h4>
                <input type="text" id="tags" style="all:inherit" placeholder="T, a, g, s">
            </h4>
            <textarea id="editor" placeholder="Post Content"></textarea>
        </div>
    </div>

    <div class="col-md-3">
        <div class="box">
            <h2>Upload cover image</h2>
            <div id="imagePreview"></div>
            <label for="file" class="btn">Upload Image</label>
            <input type="file" id="file" >
        </div>
    </div>

    <div class="btn-group mt-3">
        <button class="btn" id="publishbtn">Publish</button>
        <button class="btn ghost" id="savedraft">Save Draft</button>
        <button class="btn ghost" id="options"><i class="mdi mdi-cog"></i></button>
    </div>
</div>

<?php
ob_start();
?>
<script src="<?= BASEPATH ?>/src/dist/js/simplemde.min.js"></script>
<script src="<?= BASEPATH ?>/src/dist/js/create-post.js"></script>
<?php
$footer = ob_get_clean();
echo show_footer($footer);
?>