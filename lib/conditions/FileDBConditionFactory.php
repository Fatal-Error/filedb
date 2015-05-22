<?php

require_once __DIR__.'/FileDBConditionFactoryInterface.php';

class FileDBConditionFactory implements FileDBConditionFactoryInterface{
  
  public static function getCondition($field, $value, $conditionType) {
    if(class_exists($conditionType)) {
      return new $conditionType($field, $value);
    }
    else {
      throw new \Exception(sprintf("Cannot find class with name %s", $conditionType));
    }
  }
}

