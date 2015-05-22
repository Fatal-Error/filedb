<?php

class FileDBUpdate extends FileDBConditionQuery implements FileDBQueryInterface {

  private $table;
  private $alias;
  private $fields;

  public function __construct($tableName) {
    $this->table = FileDb::getTable($tableName);
    $this->alias = 't';
  }

  /**
   * @return \FileDBTable
   */
  public function getTable() {
    return $this->table;
  }

  private function getAlias() {
    return $this->alias;
  }

  public function getFields() {
    return $this->fields;
  }

  /**
   * @param array $fields
   * @return \FileDBSelect
   * @throws \Exception
   */
  public function fields(array $fields) {
    $table = $this->getTable();

    if (count(array_intersect($table->getFields()->getNames(), array_keys($fields))) == count($fields)) {
      foreach ($fields as $field => $value) {
        $this->fields[$field] = $value;
      }
    }
    else {
      throw new \Exception(sprintf('Unable to select specified fields "%s" from table "%s".', implode(', ', $fields), $table->getTableName()));
    }

    return $this;
  }

  /**
   * @param $field
   * @param $value
   * @param string $conditionType
   * @return \FileDBUpdate
   * @throws \Exception
   */
  public function condition($field, $value, $conditionType = FIleDBConditionTypes::EQUALS) {
    $table = $this->getTable();

    if (!$table->getFields()->getField($field)) {
      throw new \Exception(sprintf('Unable to set a condition for field "%s" of table "%s".', $field, $table->getTableName()));
    }

    $condition = FileDBConditionFactory::getCondition($this->getAlias() . '.' . $field, $value, $conditionType);
    parent::addCondition($condition);

    return $this;
  }

  /**
   * @return mixed|void
   */
  public function execute() {
    $table = $this->getTable();

    $query = FileDB::select($table->getTableName(), $this->getAlias())
      ->fields($this->getAlias());

    foreach($this->getConditions() as $group) {
      foreach ($group as $conditions) {
        foreach ($conditions as $condition) {
          $query->addCondition($condition);
        }
      }
    }

    $rows = $query->execute()->fetchAll(TRUE);
    $fields = $this->getTable()->getFields()->getNames();
    $update = $this->getFields();

    $lock = FileDBA::lockTable($table->getTableName());
    try {
      foreach ($rows as $id => $row) {
        $row = array_combine($fields, array_values($row));
        $updated = array_merge($row, $update);

        $table->setRow($updated, $id);
      }
    }
    catch (Exception $e) {
      FileDBA::unlockTable($table->getTableName(), $lock);
      throw new Exception($e->getMessage());
    }

    FileDBA::unlockTable($table->getTableName(), $lock);
  }
}

