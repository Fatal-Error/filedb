<?php

require_once __DIR__ . '/commons/FileDBHandler.php';
require_once __DIR__ . '/structure/FileDBFile.php';
require_once __DIR__ . '/structure/FileDBField.php';
require_once __DIR__ . '/structure/FileDBFieldCollection.php';
require_once __DIR__ . '/structure/FileDBTable.php';
require_once __DIR__ . '/structure/FileDBSector.php';
require_once __DIR__ . '/conditions/FileDBConditionQuery.php';
require_once __DIR__ . '/conditions/FileDBConditionTypes.php';
require_once __DIR__ . '/conditions/FileDBConditionMethods.php';
require_once __DIR__ . '/conditions/FileDBConditions.php';
require_once __DIR__ . '/conditions/FileDBConditionFactory.php';
require_once __DIR__ . '/conditions/FileDBCondition.php';
require_once __DIR__ . '/dms/FileDBQueryInterface.php';
require_once __DIR__ . '/dms/FileDBSelect.php';
require_once __DIR__ . '/dms/FileDBUpdate.php';
require_once __DIR__ . '/dms/FileDBDelete.php';

class FileDB {

  private function __construct() {

  }

  /**
   * @param string $table
   * @param string $alias
   * @return \FileDBSelect
   */
  public static function select($table, $alias) {
    return new FileDBSelect($table, $alias);
  }

  /**
   * @param $tableName
   * @param array $row
   * @param bool $id
   */
  public static function insert($tableName, array $row, $id = FALSE) {
    self::getTable($tableName)->setRow($row, $id);
  }

  /**
   * @param string $table
   * @return \FileDBUpdate
   */
  public static function update($table) {
    return new FileDBUpdate($table);
  }

  /**
   * @param string $table
   * @return \FileDBDelete
   */
  public static function delete($table) {
    return new FileDBDelete($table);
  }

  /**
   * @param string $tableName
   * @return \FileDBTable object
   */
  public static function getTable($tableName) {
    $filename = FileDBHandler::getDBFolder() . '/' . $tableName . '.' . FileDBTable::EXTENSION;
    return FileDBHandler::readFile($filename);
  }

  public static function getSettings() {
    return $GLOBALS['filedb_settings'];
  }
}