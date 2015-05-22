<?php

if (!defined('__DIR__')) {
    define('__DIR__', dirname(__FILE__));
}

global $filedb_settings;
$filedb_settings = parse_ini_file(__DIR__."/config/settings.ini", true);

require_once __DIR__.'/lib/FileDB.php';
require_once __DIR__.'/lib/dds/FileDBA.php';
