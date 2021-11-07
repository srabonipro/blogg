<?php
/*
*
* Base Path
*
*/
define('BASEPATH', 'http://127.0.0.1/forum');

/*
*
* Database Config
*
*/
define('DBSERVER', 'localhost');
define('DBUSER', 'root');
define('DBPASS', '');
define('DBNAME', 'forum');

DB::$user = DBUSER;
DB::$password = DBPASS;
DB::$dbName = DBNAME;
DB::$host = DBSERVER; //defaults to localhost if omitted
DB::$port = '3306'; // defaults to 3306 if omitted
DB::$encoding = 'utf8mb4';

/*
*
* ReCaptcha Config
*
*/
define('CAPCLIENTID', 'd09d63cc-d2c4-46fd-ad19-5283d2a4633c');
define('CAPSECRET', '0xDC58576C2c0B8d1E4d982D472F12DFEC394Fd4AA');

/**
 * 
 * Cookies
 * 
 */
define('SALT', '8780OP&(&^#%#%&RYR^TG&&7%%7DH$$GFHF');

/**
 * 
 * Imgbb Config
 * 
 */
define('IMGBBAPI', 'a947079d11d7e4d3969a5a53bb72169f');


try {
    $test = DB::query("SELECT * FROM config WHERE name='878787'");

    $cmd = DB::queryFirstRow("SELECT * FROM config WHERE name='fname'");
    define('FNAME', $cmd['value']);

    $cmd = DB::queryFirstRow("SELECT * FROM config WHERE name='fcolor'");
    define('FCOLOR', $cmd['value']);

} catch (Exception $e) {
    die("<pre><code>" . $e . "<code></pre>");
}
