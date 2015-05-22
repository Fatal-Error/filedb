<?php

final class FileDBField {

  private $name;
  private $type;
  private $maxLength;
  private $autoincrement;
  private $autoincrement_value;
  private $nullable;
  private $primaryKey;
  private $primaryKeys;

  function __construct($name, $type = 'string', $maxLength = NULL, $autoincrement = FALSE, $nullable = TRUE, $primaryKey = FALSE) {
    $this->setName($name);
    $this->setType($type);
    $this->setMaxLength($maxLength);
    $this->setAutoincrement($autoincrement);
    $this->setNullable($nullable);
    $this->setPrimaryKey($primaryKey);

    $this->autoincrement_value = 0;
    $this->primaryKeys = array();
  }

  public function getName() {
    return $this->name;
  }

  public function setName($name) {
    $this->name = (string) $name;
  }

  public function getType() {
    return $this->type;
  }

  public function setType($type) {
    $this->type = (string) $type;
  }

  public function getMaxLength() {
    return $this->maxLength;
  }

  public function setMaxLength($maxLength) {
    $this->maxLength = (int) $maxLength;
  }

  public function isAutoincrement() {
    return $this->autoincrement;
  }

  public function setAutoincrement($autoincrement) {
    $this->autoincrement = (boolean) $autoincrement;
  }

  public function getAutoincrementValue() {
    $this->autoincrement_value++;
    return $this->autoincrement_value;
  }

  public function getNullable() {
    return $this->nullable;
  }

  public function setNullable($nullable) {
    $this->nullable = (boolean) $nullable;
  }

  public function isPrimaryKey() {
    return $this->primaryKey;
  }

  public function setPrimaryKey($primaryKey) {
    $this->primaryKey = (boolean) $primaryKey;
  }

  public function getPrimaryKeys() {
    return $this->primaryKeys;
  }

  public function addPrimaryKey($key) {
    if (!in_array($key, $this->primaryKeys)) {
      $this->primaryKeys[] = $key;
      return TRUE;
    }

    return FALSE;
  }

  public function removePrimaryKey($key) {
    if (in_array($key, $this->primaryKeys) && ($index = array_search($key, $this->primaryKeys))) {
      unset($this->primaryKeys[$index]);
      return TRUE;
    }

    return FALSE;
  }

}