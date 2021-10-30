<?php
require "../init.php";
?>
<?php
/**
 * redirect if logged in
 */
if (logged_in()) {
    header("Location: " . BASEPATH);
    die();
}
?>
<?php
if (isset($_POST["e"]) and isset($_POST["c"])) {

    /**
     * 
     * Set variables
     * 
     */

    $email = $_POST["e"];
    $captcha = $_POST["c"];
    $m = "";

    /**
     * 
     * Validation rules
     * 
     */

    $v = new Valitron\Validator(
        array(
            'email' => $email,
            'captcha' => $captcha,
        )
    );

    $v->rule('required', 'email');
    $v->rule('required', 'captcha');
    $v->rule('emailDNS', 'email');

    /**
     * 
     * Check if valid
     * 
     */

    if ($v->validate()) {
        if (str_contains($email, "@gmail.com")) {
            $s = true;
        } else {
            /**
             * Not a gmail email
             */
            $s = false;
            $m = "Only gmail.com emails accepted";
        }
    } else {
        /**
         * Validation errors
         */
        $s = false;
        ob_start();
        print("<h2>Fix these errors to sign up</h2>");
        foreach ($v->errors() as $error) {
            foreach ($error as $value) {
                print("<p>" . $value . "</p>");
            }
        }
        $m = ob_get_clean();
    }

    if ($s) {
        /**
         * Captcha validation
         */
        $url = 'https://hcaptcha.com/siteverify';
        $data = array(
            'response' => $captcha,
            'secret' => CAPSECRET
        );

        $options = array(
            'http' => array(
                'header'  => "Content-type: application/x-www-form-urlencoded\r\n",
                'method'  => 'POST',
                'content' => http_build_query($data)
            )
        );
        $context  = stream_context_create($options);
        $result = file_get_contents($url, false, $context);

        if ($result === FALSE) {
            /**
             * Unknown captcha error
             */
            $s = false;
            $m = "Something went wrong while verifying captcha. Try reloading the page";
        } else {
            $response = json_decode($result, true);

            if ($response['success']) {
                $s = true;

                $account = DB::queryFirstRow("SELECT * FROM users WHERE email=%s", $email);

                if (!isset($account['id'])) {


                    /**
                     * 
                     * Add user to DB and send an email with the instructions
                     */

                    /**
                     * Add to DB
                     */
                    $emailhash = sha1(md5($email . $captcha . rand(1, 100)));
                    try {
                        DB::insert('users', [
                            'email' => $email,
                            'id' => $emailhash
                        ]);
                        $s = true;
                    } catch (Throwable $th) {
                        $s = false;
                        $m = "Server Error. Plase Try Again Later";
                    }

                    if ($s) {
                        /**
                         * Send Email
                         * 
                         */
                        try {

                            $mail->addAddress($email);
                            $mail->isHTML(true);
                            $mail->Subject = 'Verify Email to create your ' . FNAME . " Account";
                            $mail->Body = 'To verify your email and to create your ' . FNAME . " account, you need to click this link and follow the instructions provided. <br> \n ". 
                            "<a href='".BASEPATH."/pages/dashboard.php?verifyemail=".base64_encode($emailhash)."'>".BASEPATH."/pages/dashboard.php?verifyemail=".base64_encode($emailhash)."</a>";
                            $mail->send();

                            $s = true;
                        } catch (Throwable $th) {
                            $s = false;
                            $m = "Error sending email :( Please contact us";
                        }
                    }


                    /**
                     * @see END
                     */
                } else {
                    /**
                     * Email Used Error
                     */
                    $s = false;
                    $m = "Email already used in an account";
                }
            } else {
                /**
                 * Wrong Captcha error
                 */
                $s = false;
                $m = "Captcha wrong. Try reloading the page";
            }
        }
    }

    /**
     * 
     * Shows the data
     * 
     */

    $data = array(
        "success" => $s,
        "message" => $m
    );

    header('Content-Type: application/json; charset=utf-8');
    echo json_encode($data);

    die();
} else {
    header("HTTP/1.0 400 Invalid request");
    die("<pre>400</pre>");
}
