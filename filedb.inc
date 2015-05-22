<?php

if (!defined('__DIR__')) {
  define('__DIR__', dirname(__FILE__));
}

if (empty($GLOBALS['filedb_root'])) {
  $GLOBALS['filedb_root'] = __DIR__ . "/config";
}

$GLOBALS['filedb_settings'] = parse_ini_file($GLOBALS['filedb_root'] . "/settings.ini", TRUE);

require_once __DIR__ . '/lib/FileDB.php';
require_once __DIR__ . '/lib/dds/FileDBA.php';
