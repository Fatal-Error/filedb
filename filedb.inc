<?php

if (!defined('__DIR__')) {
    define('__DIR__', dirname(__FILE__));
}

global $settings;
$settings = parse_ini_file(__DIR__."/config/settings.ini", true);

require_once __DIR__.'/lib/FileDB.php';
