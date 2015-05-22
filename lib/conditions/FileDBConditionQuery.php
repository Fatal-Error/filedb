<?php

require_once __DIR__.'/FileDBCondition.php';

abstract class FileDBConditionQuery {
  
  const CONDITION_AND = 1;
  const CONDITION_OR = 2;
  
  private $conditionMethod = self::CONDITION_AND;
  protected $conditions;

  /**
   * @param FileDBCondition $condition
   * @param int $group
   */
  public function addCondition(FileDBCondition $condition, $group = 0) {
    $this->conditions[$group][$this->getConditionMethod()][] = $condition;
  }

  public function getConditions() {
    return !empty($this->conditions) ? $this->conditions : array();
  }
  
  public function setAnd() {
    $this->conditionMethod = self::CONDITION_AND;
    return $this;
  }
  
  public function setOr() {
    $this->conditionMethod = self::CONDITION_OR;
    return $this;
  }
  
  public function getConditionMethod() {
    return $this->conditionMethod;
  }
  
  abstract public function condition($field, $value, $conditionType = FIleDBConditionTypes::EQUALS);
}

