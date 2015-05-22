<?php

interface FileDBConditionFactoryInterface {
  
  /**
   * @param string $field
   * @param mixed $value
   * @param FileDBConditionTypes $conditionType
   */
  public static function getCondition($field, $value, $conditionType);
}
