<?php 
define('D', 'D');
require 'init.php';

if (isset($_GET['postname'])) {
    if (!empty($_GET['postname'])) {
           require 'pages/post.php';
    }
    else {
        require 'pages/homepage.php';
    }
}