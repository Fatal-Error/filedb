<?php

class FileDBA {

  /**
   * @return array
   */
  public static function showTables() {
    $dir = FileDBHandler::getDBFolder();
    $tables = FileDBHandler::scanDir($dir, '/\.' . FileDBTable::EXTENSION . '$/');

    if (!empty($tables)) {
      foreach ($tables as $i => $tablename) {
        $table = explode('.', $tablename);
        $tables[$i] = reset($table);
      }
    }

    return $tables;
  }

  /**
   * @param $tableName
   * @param FileDBFieldCollection $fields
   * @return FileDBTable
   */
  public static function createTable($tableName, FileDBFieldCollection $fields) {
    return new FileDBTable($tableName, $fields);
  }

  /**
   * @param string $tableName
   * @return \FileDBTable
   */
  public static function truncateTable($tableName) {
    $table = FileDB::getTable($tableName);
    $dir = FileDBHandler::getDBFolder();
    $sectors = $table->getSectors();

    foreach ($sectors as $sector) {
      unlink($dir . '/' . $sector);
    }
  }

  /**
   * @param string $tableName
   * @return bool
   * @throws \Exception
   */
  public static function dropTable($tableName) {
    $filename = FileDBHandler::getTableFile($tableName);

    if (!file_exists($filename)) {
      throw new \Exception(sprintf('Cannot drop table "%s". It doesn\'t exists.', $tableName));
    }

    self::truncateTable($tableName);

    return unlink($filename);
  }

  /**
   * @param string $tableName
   * @param FileDBField $field
   * @param mixed $default
   */
  public static function addField($tableName, FileDBField $field, $default = NULL) {
    FileDB::getTable($tableName)->addField($field, $default);
  }

  /**
   * @param string $tableName
   * @param string $field
   */
  public static function dropField($tableName, $field) {
    FileDB::getTable($tableName)->dropField($field);
  }

  /**
   * @param string $tableName
   * @param string $fieldName
   * @param array $fieldInfo
   */
  public static function alterField($tableName, $fieldName, $fieldInfo) {
    FileDB::getTable($tableName)->alterField($fieldName, $fieldInfo);
  }

  public static function lockTable($tableName) {
    return FileDB::getTable($tableName)->setLock();
  }

  public static function unlockTable($tableName, $lock) {
    return FileDB::getTable($tableName)->removeLock($lock);
  }
}
