<?php
require "parsedown/parsedown.php";
/**
 * Init
 */
$Parsedown = new Parsedown();
/**
 * Security
 */
$Parsedown->setSafeMode(true);
$Parsedown->setMarkupEscaped(true);

