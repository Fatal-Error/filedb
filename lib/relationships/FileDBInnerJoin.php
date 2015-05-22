<?php

require_once __DIR__ . '/FileDBJoin.php';

class FileDBInnerJoin extends FileDBJoin {

  public function doJoin($rows, $fields) {
    foreach ($rows as $id => $row) {
      $left_field = $this->getLeftAlias() . '.' . $this->getLeftField();
      $right_field = $this->getRightAlias() . '.' . $this->getRightField();

      $query = FileDB::select($this->getLeftTable(), $this->getLeftAlias())
          ->fields($this->getLeftAlias())
          ->condition($left_field, $row[$right_field], FIleDBConditionTypes::EQUALS);

      foreach (parent::getConditions() as $group_id => $group) {
        foreach ($group as $method => $conditions) {
          if ($method == FileDBConditionMethods::CONDITION_AND) {
            $query->setAnd();
          }
          else {
            $query->setOr();
          }
          
          foreach ($conditions as $condition) {
            $query->condition($condition->getField(), $condition->getValue(), get_class($condition), $group_id);
          }
        }
      }
      
      $rs = $query->execute()->fetchAll();

      if ($rs) {
        foreach ($rs as $j_row) {
          $rows[] = array_merge($row, $j_row);
        }

        unset($rows[$id]);
      }
      else {
        unset($rows[$id]);
      }
    }

    return $rows;
  }

  public function condition($field, $value, $conditionType = FIleDBConditionTypes::EQUALS) {}
}