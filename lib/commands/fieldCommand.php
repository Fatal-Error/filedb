<?php

class fieldCommand extends FileDBCommand{

  /**
   * @FDB\required --table The table name
   * @FDB\required --field Field structure
   * @FDB\optional --default optional default value
   */
  public function add() {
    $this->requireArgs(array('table', 'field'));
      
    foreach(explode('|', $this->getArg('field')) as $info) {
      list($name, $value) = explode(':', $info);

      if ($name == 'name') {
        $field = new FileDBField($value);
      }
      else{
        $method = 'set'.ucwords($name);
        call_user_func_array(array($field, $method), array($value));
      }
    }
    
    $default = $this->getArg('default') ? $this->getArg('default') : NULL;
    
    try {
      FileDBA::addField($this->getArg('table'), $field, $default);
      print self::LIGHTGREEN.'Table field added successfully!'.PHP_EOL;
    }
    catch (Exception $e) {
      print self::LIGHTRED.$e->getMessage().PHP_EOL;
    }
  }
  
  /**
   * @FDB\required --table The table name
   * @FDB\required --field The field name
   */
  public function drop() {
    $this->requireArgs(array('table', 'field'));
    
    try {
      FileDBA::dropField($this->getArg('table'), $this->getArg('field'));
      print self::LIGHTGREEN.'Table field dropped successfully!'.PHP_EOL;
    }
    catch (Exception $e) {
      print self::LIGHTRED.$e->getMessage().PHP_EOL;
    }
  }
}

