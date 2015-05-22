<?php

abstract class FileDBCondition {
  
  protected $field;
  protected $value;
  
  function __construct($field, $value) {
    $this->field = $field;
    $this->value = $value;
  }

  public function getField() {
    return $this->field;
  }

  public function setField($field) {
    $this->field = $field;
    return $this;
  }

  public function getValue() {
    return $this->value;
  }

  public function setValue($value) {
    $this->value = $value;
    return $this;
  }

  /**
   * @param type $value1
   * @param type $value2
   * @return bool $match
   */
  abstract public function compare($row);
}

