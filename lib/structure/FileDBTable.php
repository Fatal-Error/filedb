<?php

class FileDBTable extends FileDBFile {

  const EXTENSION = 'ftb';

  private $tableName;
  protected $fields;
  private $freeIndexes;
  private $lock;

  public function __construct($tableName, FileDBFieldCollection $fields) {
    $filename = FileDBHandler::getDBFolder() . '/' . $tableName . '.' . self::EXTENSION;

    if (FileDBHandler::fileExists($filename)) {
      throw new \Exception(sprintf('Cannot create table "%s". It already exists.', $tableName));
    }

    $this->tableName = $tableName;
    $this->setFields($fields);
    $this->freeIndexes = array();
    $this->lock = FALSE;

    $this->write();
  }

  public function getTableName() {
    return $this->tableName;
  }

  /**
   * @return \FileDBFieldCollection
   */
  public function getFields() {
    return $this->fields;
  }

  /**
   * @param FileDBFieldCollection $fields
   * @return \FileDBTable
   */
  private function setFields(FileDBFieldCollection $fields) {
    $this->fields = $fields;
    $this->write();

    return $this;
  }

  public function alterField($fieldName, array $fieldInfo) {
    $field = $this->getFields()->getField($fieldName);

    foreach ($fieldInfo as $name => $value) {
      $method = 'set' . ucwords($name);
      call_user_func_array(array($field, $method), array($value));
    }

    $newField = clone $field;
    $this->getFields()->dropField($fieldName)->setField($newField);

    $this->write();

    return $this;
  }

  /**
   * @param FileDBField $field
   * @param type $default
   */
  public function addField(FileDBField $field, $default = NULL) {
    $fields = $this->getFields();
    $fields->setField($field);

    $table = $this->setFields($fields);
    $table->addNewField($field, $default);
  }

  /**
   * @param FileDBField $field
   * @param type $default
   */
  private function addNewField(FileDBField $field, $default) {
    $rows = $this->getRows();

    foreach (array_keys($rows) as $id) {
      $rows[$id][$field->getName()] = $default;
    }

    $this->setRows($rows);
  }

  /**
   * @param string $name
   */
  public function dropField($name) {
    $fields = $this->getFields()->dropField($name);
    $table = $this->setFields($fields);
    $table->removeDeletedField($name);
  }

  /**
   * @param string $name
   */
  private function removeDeletedField($name) {
    $rows = $this->getRows();

    foreach (array_keys($rows) as $id) {
      unset($rows[$id][$name]);
    }

    $this->setRows($rows);
  }

  public function isLocked() {
    return !empty($this->lock);
  }

  public function setLock() {
    if (!$this->isLocked()) {
      $this->lock = $this->getTableName() . '_' . md5(time());
      $this->write();

      return $this->lock;
    }

    return FALSE;
  }

  private function getLock() {
    return $this->lock;
  }

  public function removeLock($lock) {
    if ($this->isLocked() && ($this->getLock() == $lock)) {
      $dir = FileDBHandler::getDBFolder();

      foreach ($this->getSectors() as $sector) {
        $sector = FileDBHandler::readFile($dir . '/' . $sector);
        $sector->saveInMemoryRows();
      }

      $this->lock = FALSE;
      $this->write();

      return TRUE;
    }

    return FALSE;
  }

  public function getRows() {
    $rows = & FileDBHandler::_static(__FUNCTION__);

    if (empty($rows)) {
      $dir = FileDBHandler::getDBFolder();
      $sectors = $this->getSectors();
      $rows = array();

      if (!empty($sectors)) {
        foreach ($sectors as $sector) {
          $sector = FileDBHandler::readFile($dir . '/' . $sector);
          $rows = $rows + $sector->getRows();
        }
      }
    }

    return $rows;
  }

  private function setRows($rows) {
    foreach ($rows as $id => $row) {
      $this->setRow($row, $id);
    }
  }

  public function setRow($row, $id = FALSE) {
    $rows = $this->getRows();

    //if $id is FALSE this is an update.
    if (array_search($row, $rows) && ($id === FALSE)) {
      throw new \Exception(sprintf('Integrity constraint violation. Duplicate entry for row "%s"', print_r($row, TRUE)));
    }

    $use_free_index = FALSE;
    $free_index = $id;
    /*
    if ($id === FALSE) {
      $free_index = $this->getFreeIndex();

      if ($free_index !== FALSE) {
        $index = $free_index;
        $use_free_index = TRUE;
      }
      else {
        $index = $id;
      }
    }
    else {
      $index = $id;
    }
    */
    $index = $id;

    $sector = $this->getCorrectSector($index);

    if (!$sector->setRow($row, $rows, $index) && $use_free_index) {
      $this->addFreeIndex($free_index);
      $this->write();
    }
  }

  public function removeRow($id, $row) {
    foreach($this->getFields()->getFields() as $field) {
      if ($field->isPrimaryKey()) {
        $field->removePrimaryKey($row[$field->getName()]);
      }
    }

    $this->getCorrectSector($id)->removeRow($id);
    $this->addFreeIndex($id);
  }

  public function getFreeIndexes() {
    return $this->freeIndexes;
  }

  private function getFreeIndex() {
    return !empty($this->freeIndexes) ? array_shift($this->freeIndexes) : FALSE;
  }

  private function addFreeIndex($index) {
    $this->freeIndexes[] = $index;
    $this->freeIndexes = array_unique($this->freeIndexes);
    sort($this->freeIndexes, SORT_NUMERIC);

    $this->write();
  }

  private function getCorrectSector($id = FALSE) {
    global $settings;

    $rows = $this->getRows();
    $db_folder = $settings['db']['folder'];
    $max_rows = $settings['sectors']['max_rows'];

    if (is_numeric($id)) {
      $index = floor($id / $max_rows);
      $sector_file = $db_folder . '/' . $this->getTableName() . '_' . $index . '.' . FileDBSector::EXTENSION;

      if (FileDBHandler::fileExists($sector_file)) {
        return FileDBHandler::readFile($sector_file);
      }
      else {
        return new FileDBSector($this, $index);
      }
    }
    elseif (!$id && empty($rows)) {
      return new FileDBSector($this, 0);
    }
    else {
      $sectors = FileDBHandler::scanDir($db_folder, '/^' . preg_quote($this->getTableName()) . '_\d+\.' . FileDBSector::EXTENSION . '$/');
      $sector = FileDBHandler::readFile($db_folder . '/' . end($sectors));
      $rows = $sector->getRows();

      if (count($rows) < $max_rows) {
        return $sector;
      }
      else {
        return $this->getCorrectSector($max_rows * (1 + $sector->getIndex()));
      }
    }
  }

  public function getSectors() {
    return FileDBHandler::scanDir(FileDBHandler::getDBFolder(), '/^' . $this->getTableName() . '_\d+\.' . FileDBSector::EXTENSION . '$/');
  }

  protected function getFilename() {
    return $this->getTableName() . '.' . self::EXTENSION;
  }
}