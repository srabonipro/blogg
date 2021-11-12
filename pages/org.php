<?php
require "../init.php";

if (!isset($_GET['id']) or !logged_in()) {
    require "../pages/404.php";
} else {
    $org = DB::queryFirstRow("SELECT * FROM organizations WHERE id=%s", $_GET['id']);
    if (!isset($org["id"])) {
        require "../pages/404.php";
    } else {
        $account = DB::queryFirstRow("SELECT * FROM users WHERE email=%s", hash__($_COOKIE['_loggedin__hash'], "decrypt"));

        if (($account["id"] === $org["owner"]) == true) {
            /**
             * We have a owner
             */
            if (isset($_GET["action"])) {
                if ($_GET["action"] == "generatenew") {
                    /**
                     * Generate new invite code
                     */
                    $key = hash__(rand(0, 999999999), "encrypt");
                    DB::query("UPDATE organizations SET invitecode=%s WHERE id=%s", $key, $org["id"]);

                    /**
                     * Show the invite code
                     */

                    header("Content-Type: application/json");
                    echo json_encode(array("code" => $key));
                    die();
                }
            } 
            /**
             * Update org info
             */
            elseif (
                isset($_POST["name"])
                and isset($_POST["about"])
                and isset($_POST["location"])
                and isset($_POST["website"])
                and isset($_POST["email"])
            ) {
                /**
                 * Update the organization
                 */
                $message = $success = "";

                /**
                 * Validate the data
                 */
                $name = $_POST["name"];
                $about = $_POST["about"];
                $location = $_POST["location"];
                $website = $_POST["website"];
                $email = $_POST["email"];
            

                $v = new Valitron\Validator(
                    array(
                        'name' => $name,
                        'about' => $about,
                        'location' => $location,
                        'website' => $website,
                        'email' => $email
                    )
                );
            
                $v->rule('required', 'name');
                $v->rule('required', 'email');
                $v->rule('email', 'email');
                $v->rule('url', "website");
            
                /**
                 * 
                 * Check if valid
                 * 
                 */
            
                if ($v->validate()) {
                    $s = true;
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

                if ($s == true) {
                    /**
                     * Update the database
                     */
                    DB::update(
                        "organizations",
                        array(
                            "name" => $name,
                            "about" => $about,
                            "location" => $location,
                            "website" => $website,
                            "email" => $email,
                        ),
                        "id=%s",
                        $org["id"]
                    );

                    /**
                     * Show success
                     */
                    $s = true;
                    $m = "";
                } 
            }

            echo show_header("Organization: " . htmlspecialchars($org["name"]) . " Management");

            /**
             * Sanitize the data
             */
            foreach ($org as $key => $value) {
                $org[$key] = htmlspecialchars($value);
            }

            if (empty($org["invitecode"])) {
                DB::update("organizations", array(
                    "invitecode" => hash__(rand(0, 999999999), "encrypt")
                ), "id=%s", $org["id"]);
            }
?>
            <div class="box">
                <?php 
                if(isset($s) and isset($m))
                {
                    if (!$s == false) {
                        /**
                         * Refresh the page
                         */
                        echo "<div class='notification success'>";
                        echo "Saved <a class='btn ms-1' href='".current_url()."'>Refresh</a>";
                    } else {
                        echo "<div class='notification error'>";
                    }
                    echo $m;
                    echo "</div>";
                    echo "<div class=\"p-2\"></div>";
                }
                ?>
                <h1>Organsiation - <b> <?= $org["name"] ?> </b> - Management</h1>
                <p><i class="mdi mdi-lock"></i> Only the owner (you) can manage this Organization</p>
                <div class="p-2"></div>
                <a href="<?= BASEPATH ?>/org/<?= $org["username"] ?>" target="_blank" class="btn outlined"><i class="mdi mdi-open-in-new me-2"></i> View </a>
                <div class="p-2"></div>
                <h4>Information</h4>

                <form action="<?= BASEPATH . "/pages/org.php?id=" . $org["id"] ?>" method="POST">
                    <div class="input-container">
                        <label class="input-label" for="name">Name</label>
                        <input class="input" id="name" name="name" type="text" value="<?= $org["name"] ?>">
                    </div>

                    <div class="input-container">
                        <label class="input-label" for="about">About</label>
                        <textarea class="input" id="about" name="about" rows="5"><?= $org["about"] ?></textarea>
                    </div>

                    <div class="input-container">
                        <label class="input-label" for="username">Username (Read only)</label>
                        <input class="input" id="username" readonly value="<?= $org["username"] ?>" />
                    </div>

                    <div class="input-container">
                        <label class="input-label" for="website">Website</label>
                        <input class="input" id="website" name="website" type="text" value="<?= $org["website"] ?>">
                    </div>

                    <div class="input-container">
                        <label class="input-label" for="email">Contact Email</label>
                        <input class="input" id="email" name="email" type="text" value="<?= $org["email"] ?>">
                    </div>

                    <div class="input-container">
                        <label class="input-label" for="location">Location</label>
                        <input class="input" id="location" name="location" type="text" value="<?= $org["location"] ?>">
                    </div>

                    <div class="input-container">
                        <label class="input-label" for="invitecode">Invite Code</label>
                        <input class="input" readonly id="invitecode" name="invitecode" type="password" value="<?= $org["invitecode"] ?>">
                        <div class="row ms-1 mt-2 me-2" style="max-width: 400px;">
                            <button class="btn small outlined col" type="button" id="generatenew" data-id="<?= $org["id"] ?>">Generate New Invite Code</button>
                            <button class="btn ms-2 small outlined col" type="button" id="copy">Copy code</button>
                        </div>
                    </div>


                    <button type="submit" class="btn mt-4">Save</button>
                </form>
                <div class="p-2"></div>
                <h4>Members</h4>
                <table>
                    <thead>
                        <tr>
                            <td>
                                <b>Name</b>
                            </td>
                            <td>
                                <b>Email</b>
                            </td>
                            <td>
                                <b>Role</b>
                            </td>
                            <td>
                                <b>Posts created</b>
                            </td>
                            <td>
                                <b>View profile</b>
                            </td>
                            <td>
                                <b>Actions</b>
                            </td>
                        </tr>
                    </thead>
                    <tbody>
                        <?php
                        $members = DB::query("SELECT * FROM orgmembers WHERE orgid = %s", $org["id"]);
                        foreach ($members as $member) {
                            $user = DB::queryFirstRow("SELECT * FROM users WHERE id=%s", $member["user"]);
                            foreach ($user as $key => $value) {
                                $user[$key] = htmlspecialchars($value);
                            }
                            echo "<tr>";

                            /**
                             * Name
                             */
                            echo "<td>" . $user["username"] . "</td>";

                            /**
                             * Email
                             */
                            echo "<td><a href='#' data-username=\"".$user["id"]."\">" . obfuscate_email($user["email"]) . "</a></td>";

                            /**
                             * Role
                             */
                            echo "<td>";
                            if ($user["id"] === $org["owner"]) {
                                echo "Owner";
                            } else {
                                echo "Member";
                            }
                            echo "</td>";

                            /** *
                             * Posts created
                             */
                            echo "<td>";
                            $posts = DB::query("SELECT `id` FROM posts WHERE creator=%s", $user["id"]);
                            echo count($posts);
                            echo "</td>";

                            /**
                             * View profile
                             */
                            echo "<td><a class='btn small ghost rounded' href=\"" . BASEPATH . "\\account\\" . $user["uname"] . "\" target=\"_blank\"><i class=\"mdi mdi-open-in-new\"></i></a></td>";

                            /**
                             * Actions
                             */
                            echo "<td>";
                            if ($user["id"] !== $org["owner"]) {
                                echo "<button class=\"btn error small ghost\" data-remove-member=\"" . base64_encode($user["id"]) . "\">Remove</button>";
                            } else {
                                echo "<i>Owner</i>";
                            }
                            echo "</td>";

                            echo "</tr>";
                        }
                        ?>
                    </tbody>
                </table>
            </div>

            <?php
            ob_start();
            ?>
            <script src="<?= BASEPATH ?>/src/dist/js/org.js"></script>
<?php
            echo show_footer(ob_get_clean());
        } else {
            /**
             * We have a hacker
             */
            require "../pages/404.php";
        }
    }
}
