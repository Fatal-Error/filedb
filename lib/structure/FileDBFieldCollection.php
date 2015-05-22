<?php

class FileDBFieldCollection {
  
  protected $fields;
  
  public function __construct() {
    $this->fields = array();
  }
  
  /**
   * @param \FileDBField $field
   * @throws \Exception
   */
  public function setField(FileDBField $field) {
    if(!array_key_exists($field->getName(), $this->fields)) {
      $this->fields[$field->getName()] = $field;
    }
    else{
      throw new \Exception(sprintf('Cannot add field "%s". It alredy exists.', $field->getName()));
    }
  }
  
  /**
   * @param string $name
   * @return \FileDBFieldCollection
   * @throws \Exception
   */
  public function dropField($name) {
    if ($this->getField($name)) {
      unset($this->fields[$name]);
      
      return $this;
    }
    else {
      throw new \Exception(sprintf('Cannot remove field "%s". It deosn\'t exists.', $name));
    }
  }
  
  /**
   * @return array
   */
  public function getFields() {
    return $this->fields;
  }
  
  /**
   * @param string $name
   * @return \FileDBField
   */
  public function getField($name) {
    return !empty($this->fields[$name]) ? $this->fields[$name] : FALSE;
  }
  
  /**
   * @return array of fields names
   */
  public function getNames() {
    return array_keys($this->getFields());
  }
}

