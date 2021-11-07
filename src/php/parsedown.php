<?php
require "parsedown/parsedown.php";
/**
 * Init
 */
$Parsedown = new Parsedown();
/**
 * Security & others
 */
$Parsedown->setSafeMode(true);
$Parsedown->setMarkupEscaped(true);
$Parsedown->setUrlsLinked(true);
$Parsedown->setBreaksEnabled(true);