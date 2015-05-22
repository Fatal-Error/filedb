<?php

require_once __DIR__ . '/../conditions/FileDBCondition.php';

abstract class FileDBJoin extends FileDBConditionQuery {

  protected $left_table;
  protected $left_alias;
  protected $left_field;
  protected $right_table;
  protected $right_alias;
  protected $right_field;
  protected $conditions;

  public function __construct($left_table, $left_alias, $left_field, $right_table, $right_alias, $right_field) {
    $this->left_table = $left_table;
    $this->left_alias = $left_alias;
    $this->left_field = $left_field;
    $this->right_table = $right_table;
    $this->right_alias = $right_alias;
    $this->right_field = $right_field;
    $this->conditions = array();
  }

  public function getLeftTable() {
    return $this->left_table;
  }

  public function setLeftTable($left_table) {
    $this->left_table = $left_table;
  }

  public function getLeftAlias() {
    return $this->left_alias;
  }

  public function setLeftAlias($left_alias) {
    $this->left_alias = $left_alias;
  }

  public function getLeftField() {
    return $this->left_field;
  }

  public function setLeftField($left_field) {
    $this->left_field = $left_field;
  }

  public function getRightTable() {
    return $this->right_table;
  }

  public function setRightTable($right_table) {
    $this->right_table = $right_table;
  }

  public function getRightAlias() {
    return $this->right_alias;
  }

  public function setRightAlias($right_alias) {
    $this->right_alias = $right_alias;
  }

  public function getRightField() {
    return $this->right_field;
  }

  public function setRightField($right_field) {
    $this->right_field = $right_field;
  }

  public function getConditions() {
    return $this->conditions;
  }
  
  /**
   * @param FileDBCondition $condition
   */
  public function addCondition(FileDBCondition $condition, $group = 0, $method = FileDBConditionMethods::CONDITION_AND) {
    $this->conditions[$group][$method][] = $condition;
  }
  
  abstract public function doJoin($rows, $fields);
}