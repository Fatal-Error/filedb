<?php
require_once __DIR__ . '/../../filedb.inc';
require_once __DIR__ . '/../../lib/dds/FileDBA.php';

try {
  $tables = FileDBA::showTables();
}
catch (Exception $e) {
  $error = $e->getMessage();
}

$function_name = 'filedbadmin_' . filter_input(INPUT_GET, 'op', FILTER_SANITIZE_STRING);

if (function_exists($function_name)) {
  $start = _filedbadmin_get_time();
  $result = array_merge($_GET, $function_name($_GET));
  $end = _filedbadmin_get_time();

  $result['execution_time'] = $end - $start;

  print json_encode($result);
}

function filedbadmin_showtables() {
  $result['rows'] = FileDBA::showTables();
  return $result;
}

function filedbadmin_show($vars) {
  $rows = FileDB::select($vars['table'], $vars['table'])
    ->fields($vars['table'])
    ->execute()
    ->fetchAll(FALSE, FALSE);

  $result['rows'] = $rows;

  return $result;
}

function filedbadmin_structure($vars) {
  $tableName = $vars['table'];

  $table = FileDB::getTable($tableName);
  $dir = FileDBHandler::getDBFolder();
  $sectors = $table->getSectors();

  $result['sectors'] = array();
  $total_rows = 0;

  if (!empty($sectors)) {
    foreach ($sectors as $sector) {
      $sector = FileDBHandler::readFile($dir . '/' . $sector);
      $sector_rows = count($sector->getRows());
      $total_rows += $sector_rows;
      $result['sectors'][] = array(
        'index' => $sector->getIndex(),
        'rows' => $sector_rows
      );
    }
  }

  $result['total_rows'] = $total_rows;
  $result['rows'] = array();
  $fields = $table->getFields();

  foreach ($fields->getFields() as $field) {
    $result['rows'][] = array(
      'field' => $field->getName(),
      'type' => $field->getType(),
      'null' => $field->getNullable(),
      'primary_key' => $field->isPrimaryKey(),
      'max_length' => $field->getMaxLength(),
      'auto_increment' => $field->isAutoincrement(),
    );
  }

  return $result;
}

function filedbadmin_tables() {
  $result = array('tables' => array());
  foreach (FileDBA::showTables() as $table) {
    $result['tables'][] = array_merge(array('table' => $table), filedbadmin_structure(array('table' => $table)));
  }

  return $result;
}

function _filedbadmin_get_time() {
  list($usec, $sec) = explode(" ", microtime());
  return ((float) $usec + (float) $sec);
}