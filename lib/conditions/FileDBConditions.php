<?php

require_once __DIR__.'/FileDBCondition.php';

class FileDBConditionEquals extends FileDBCondition {
  
  /**
   * @param array $row
   * @return bool $match
   */
  public function compare($row) {
    return !($row[$this->getField()] == $this->getValue());
  }
}

class FileDBConditionNotEquals extends FileDBCondition {
  
  /**
   * @param array $row
   * @return bool $match
   */
  public function compare($row) {
    return !($row[$this->getField()] != $this->getValue());
  }
}

class FileDBConditionGreaterThan extends FileDBCondition {
  
  /**
   * @param array $row
   * @return bool $match
   */
  public function compare($row) {
    return !($row[$this->getField()] > $this->getValue());
  }
}

class FileDBConditionGreaterThanEquals extends FileDBCondition {
  
  /**
   * @param array $row
   * @return bool $match
   */
  public function compare($row) {
    return !($row[$this->getField()] >= $this->getValue());
  }
}

class FileDBConditionLessThan extends FileDBCondition {
  
  /**
   * @param array $row
   * @return bool $match
   */
  public function compare($row) {
    return !($row[$this->getField()] < $this->getValue());
  }
}

class FileDBConditionLessThanEquals extends FileDBCondition {
  
  /**
   * @param array $row
   * @return bool $match
   */
  public function compare($row) {
    return !($row[$this->getField()] <= $this->getValue());
  }
}

class FileDBConditionBetween extends FileDBCondition {
  
  /**
   * @param array $row
   * @return bool $match
   */
  public function compare($row) {
    return !(($row[$this->getField()] >= reset($this->getValue())) && ($row[$this->getField()] <= end($this->getValue())));
  }
}

class FileDBConditionNotBetween extends FileDBCondition {
  
  /**
   * @param array $row
   * @return bool $match
   */
  public function compare($row) {
    return !(($row[$this->getField()] < reset($this->getValue())) && ($row[$this->getField()] > end($this->getValue())));
  }
}

class FileDBConditionIn extends FileDBCondition {
  
  /**
   * @param array $row
   * @return bool $match
   */
  public function compare($row) {
    return !(in_array($row[$this->getField()], $this->getValue()));
  }
}

class FileDBConditionNotIn extends FileDBCondition {
  
  /**
   * @param array $row
   * @return bool $match
   */
  public function compare($row) {
    return !(!in_array($row[$this->getField()], $this->getValue()));
  }
}

class FileDBConditionLike extends FileDBCondition {
  
  /**
   * @param array $row
   * @return bool $match
   */
  public function compare($row) {
		$pattern = str_replace('%', '.*?', preg_quote($this->getValue()));
    
		return !(preg_match("/^" . $pattern . "$/", $row[$this->getField()]));
  }
}

class FileDBConditionNotLike extends FileDBCondition {
  
  /**
   * @param array $row
   * @return bool $match
   */
  public function compare($row) {
    $pattern = str_replace('%', '.*?', preg_quote($this->getValue()));
    
		return (preg_match("/^" . $pattern . "$/", $row[$this->getField()]));
  }
}
