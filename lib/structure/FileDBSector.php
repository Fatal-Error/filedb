<?php

class FileDBSector extends FileDBFile {

  const EXTENSION = 'fsc';

  private $index;
  private $tablename;
  protected $rows;

  /**
   * @param FileDBTable $table
   * @param int $index
   */
  public function __construct(FileDBTable $table, $index = 0) {
    $this->tablename = $table->getTableName();
    $this->index = $index;
    $this->rows = array();
  }

  /**
   * @return \FileDBTable
   */
  public function getTable() {
    return FileDB::getTable($this->tablename);
  }

  /**
   * @return string
   */
  public function getTableName() {
    return $this->tablename;
  }

  /**
   * @return int
   */
  public function getIndex() {
    return $this->index;
  }

  public function getName() {
    return $this->getTableName() . '_' . $this->getIndex();
  }

  /**
   * @return string
   */
  public function getFilename() {
    return $this->getName() . '.' . self::EXTENSION;
  }

  /**
   * @param $row
   * @param $rows
   * @param bool $id
   * @return int
   * @throws Exception
   */
  public function setRow($row, $rows, $id = FALSE) {
    if ((array_values($this->getTable()->getFields()->getNames()) == array_keys($row)) && $this->validateFields($row, $rows, $id)) {
      $sector_rows = $this->getRows();
      $existing_index = array_search($row, $sector_rows);

      if ($existing_index === FALSE || $existing_index === $id) {
        if ($id === FALSE) {
          global $settings;
          $rows_keys = array_keys($sector_rows);
          $id = !empty($sector_rows) ? end($rows_keys) + 1 : $this->getIndex() * $settings['sectors']['max_rows'];
        }

        return $this->addRow($id, $row);
      }
      else {
        throw new \Exception(sprintf('Integrity constraint violation. Duplicate entry for row "%s"', print_r($row, TRUE)));
      }
    }
    else {
      throw new \Exception(sprintf('Cannot add row into table "%s". Fields mismatch.', $this->getTableName()));
    }
  }

  /**
   * @return array
   */
  public function getRows() {
    if ($this->getTable()->isLocked()) {
      global $in_memory_rows;

      if (!empty($in_memory_rows[$this->getName()])) {
        foreach ($in_memory_rows[$this->getName()] as $id => $row) {
          $this->rows[$id] = $row;
        }
      }
    }

    return $this->rows;
  }

  /**
   * @param $id
   * @param $row
   * @return int
   */
  private function addRow($id, $row) {
    $rows = $this->getRows();

    if ($this->getTable()->isLocked()) {
      $sector = FileDBHandler::getDBFolder() . '/' . $this->getFilename();
      if (!FileDBHandler::fileExists($sector)) {
        $this->write();
      }

      global $in_memory_rows;
      $in_memory_rows[$this->getName()][$id] = $row;
    }
    else {
      $rows[$id] = $row;
      ksort($rows);
      $this->rows = $rows;

      return $this->write();
    }
  }

  public function saveInMemoryRows() {
    global $in_memory_rows;
    if (empty($in_memory_rows)) {
      return;
    }

    $rows = $this->getRows();
    $this->rows = $rows;
    $this->write();
  }

  /**
   * @param $row
   * @param $total_rows
   * @param $id
   * @return bool
   * @throws Exception
   */
  private function validateFields(&$row, $total_rows, $id) {
    $table = & $this->getTable();
    $fields = $table->getFields();

    foreach ($row as $field_name => $value) {
      $field = & $fields->getField($field_name);

      //check autoincrement
      if (($id === FALSE) && $field->isAutoincrement() && is_null($value)) {
        $row[$field_name] = $field->getAutoincrementValue();
      }

      //check pk
      if (($id === FALSE) && $field->isPrimaryKey() && !$field->addPrimaryKey($row[$field_name])) {
        throw new \Exception(sprintf('Duplicate entry "%s" for key "PRIMARY" %s', $row[$field_name], $field_name));
      }

      //check nullable
      if (is_null($row[$field_name]) && !$field->getNullable()) {
        throw new \Exception(sprintf('Column "%s" cannot be null.', $field_name));
      }

      //check type
      if (!is_null($row[$field_name]) && (gettype($row[$field_name]) != $field->getType())) {
        throw new \Exception(sprintf('Cannot instert value of type "%s" into column "%s" of type "%s". Types mismatch.', gettype($row[$field_name]), $field_name, $field->getType()));
      }
    }

    return TRUE;
  }

  /**
   * @param int $id
   * @throws \Exception
   */
  public function removeRow($id) {
    if (array_key_exists($id, $this->rows)) {
      unset($this->rows[$id]);
      $this->write();
    }
    else {
      throw new \Exception(sprintf('Cannot remove row with index "%s" from table "%s".', $id, $this->getTableName()));
    }
  }
}