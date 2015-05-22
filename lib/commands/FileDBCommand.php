<?php

abstract class FileDBCommand {
  
  const BLACK="\33[0;30m";
  const DARKGRAY="\33[1;30m";
  const BLUE="\33[0;34m";
  const LIGHTBLUE="\33[1;34m";
  const GREEN="\33[0;32m";
  const LIGHTGREEN="\33[1;32m";
  const CYAN="\33[0;36m";
  const LIGHTCYAN="\33[1;36m";
  const RED="\33[0;31m";
  const LIGHTRED="\33[1;31m";
  const PURPLE="\33[0;35m";
  const LIGHTPURPLE="\33[1;35m";
  const BROWN="\33[0;33m";
  const YELLOW="\33[1;33m";
  const LIGHTGRAY="\33[0;37m";
  const WHITE="\33[1;37m";
  
  protected $args;
  
  public function __construct($args) {
    $this->setArgs($args);
  }
  
  protected function setArgs($args) {
    $params = array();
    
    foreach($args as $arg) {
      list($key, $value) = explode('=', $arg);
      
      $key = preg_replace("/^\-\-/", '', $key);
      $params[trim($key)] = trim($value);
    }
    
    $this->args = $params;
  }
  
  protected function getArg($name) {
    return !empty($this->args[$name]) ? trim($this->args[$name]) : FALSE;
  }
  
  protected function requireArgs($args) {
    foreach ($args as $arg) {
      if (!$this->getArg($arg)) {
        print self::YELLOW.'Argument --'.$arg.' is required.'.PHP_EOL;
        exit();
      }
    }
  }
}

