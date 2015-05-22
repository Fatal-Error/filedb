<?php

class FileDBDelete extends FileDBConditionQuery implements FileDBQueryInterface{
  
  private $table;
  private $alias;
  
  public function __construct($tableName){
    $this->table = FileDb::getTable($tableName);
    $this->alias = 't';
  }

  /**
   * @return \FileDBTable
   */
  public function getTable(){
    return $this->table;
  }
  
  private function getAlias() {
    return $this->alias;
  }

  /**
   * @param $field
   * @param $value
   * @param string $conditionType
   * @return \FileDBDelete
   * @throws Exception
   */
  public function condition($field, $value, $conditionType = FIleDBConditionTypes::EQUALS) {
    $table = $this->getTable();
    
    if(!$table->getFields()->getField($field)) {
      throw new \Exception(sprintf('Unable to set a condition for field "%s" of table "%s".', $field, $table->getTableName()));
    }
    
    $condition = FileDBConditionFactory::getCondition($this->getAlias().'.'.$field, $value, $conditionType);
    $this->addCondition($condition);
    
    return $this;
  }

  public function execute() {
    $table = $this->getTable();
    
    $query = FileDB::select($table->getTableName(), $this->getAlias())
        ->fields($this->getAlias());

    foreach($this->getConditions() as $group) {
      foreach ($group as $conditionMethod => $conditions) {
        foreach ($conditions as $condition) {
          $query->addCondition($condition);
        }
      }
    }
    
    $rows = $query->execute()->fetchAll(TRUE, FALSE);
    
    if($rows) {
      foreach ($rows as $id => $row) {
        $table->removeRow($id, $row);
      }
    }
  }
}

