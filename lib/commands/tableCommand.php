<?php

class tableCommand extends FileDBCommand {

  /**
   * @FDB\required --table The name of the table to create (e.g. --table="mytable")
   * @FDB\required --fields A list of fields structures (e.g. --fields="name:id|type:int|autoincrement:true name:fieldname|type:string")
   */
  public function create() {
    $this->requireArgs(array('table', 'fields'));
    $table = $this->getArg('table');

    $fields_info = explode(' ', $this->getArg('fields'));
    $fields = new FileDBFieldCollection();

    foreach ($fields_info as $field_info) {
      if (!empty($field)) {
        unset($field);
      }

      foreach (explode('|', $field_info) as $info) {
        list($name, $value) = explode(':', $info);

        if ($name == 'name') {
          $field = new FileDBField($value);
        }
        else {
          $method = 'set' . ucwords($name);
          call_user_func_array(array($field, $method), array($value));
        }
      }

      $fields->setField($field);
    }

    try {
      FileDBA::createTable($table, $fields);
      print self::LIGHTGREEN . 'Table "' . $table . '" created successfully!' . PHP_EOL;
    }
    catch (Exception $e) {
      print self::LIGHTRED . $e->getMessage() . PHP_EOL;
    }
  }

  /**
   * @FDB\required --table The name of the table to drop (e.g. --table="mytable")
   */
  public function drop() {
    $this->requireArgs(array('table'));
    $table = $this->getArg('table');

    try {
      FileDBA::dropTable($table);
      print self::LIGHTGREEN . 'Table "' . $table . '" dropped successfully!' . PHP_EOL;
    }
    catch (Exception $e) {
      print self::LIGHTRED . $e->getMessage() . PHP_EOL;
    }
  }

  public function show() {
    $data = array();
    $tables = FileDBA::showTables();

    foreach ($tables as $table) {
      $data[] = array('Tables' => $table);
    }

    $render = new ArrayToTextTable($data);
    $render->showHeaders(TRUE);

    print $render->render() . PHP_EOL;
    print count($tables) . ' tables' . PHP_EOL;
  }

  /**
   * @FDB\required --table The name of the table to truncate (e.g. --table="mytable")
   */
  public function truncate() {
    $this->requireArgs(array('table'));
    $table = $this->getArg('table');

    try {
      FileDBA::truncateTable($table);
      print self::LIGHTGREEN . 'Table "' . $table . '" truncated successfully!' . PHP_EOL;
    }
    catch (Exception $e) {
      print self::LIGHTRED . $e->getMessage() . PHP_EOL;
    }
  }

  /**
   * @FDB\required --table The name of the table to describe
   */
  public function describe() {
    $this->requireArgs(array('table'));
    $tableName = $this->getArg('table');

    try {
      $table = FileDB::getTable($tableName);

      $data = array();
      $fields = $table->getFields();

      foreach ($fields->getFields() as $field) {
        $data[] = array(
          'field' => $field->getName(),
          'type' => $field->getType(),
          'null' => $field->getNullable(),
          'primary_key' => $field->isPrimaryKey(),
          'max_length' => $field->getMaxLength(),
          'auto_increment' => $field->isAutoincrement()
        );
      }

      $render = new ArrayToTextTable($data);
      $render->showHeaders(TRUE);
      $output = array(
        $render->render(),
        '- Rows: ' . count($table->getRows()),
        '- Sectors: ' . count($table->getSectors()),
        '- Free indexes: ' . count($table->getFreeIndexes())
      );

      print implode(PHP_EOL, $output) . PHP_EOL;
    }
    catch (Exception $e) {
      print self::LIGHTRED . $e->getMessage() . PHP_EOL;
    }
  }

  public function test() {
    list($usec, $sec) = explode(" ", microtime());
    $start = ((float) $usec + (float) $sec);

    $lock = FileDBA::lockTable('votes');

    for ($i = 1; $i < 400; $i++) {
      FileDB::insert('votes', array(
        'id' => NULL,
        'fbuser_id' => $i,
        'match_id' => 456 + ($i * 2),
        'vote' => 3
      ));
    }

    FileDBA::unlockTable('votes', $lock);

    list($usec, $sec) = explode(" ", microtime());
    $end = ((float) $usec + (float) $sec);
    $seconds = $end - $start;

    print self::LIGHTGREEN . 'Data written in ' . $seconds . ' seconds!' . PHP_EOL;
  }

  public function testUpdate() {
    list($usec, $sec) = explode(" ", microtime());
    $start = ((float) $usec + (float) $sec);

    FileDB::update('votes')
      ->fields(array('vote' => 110))
      ->execute();

    list($usec, $sec) = explode(" ", microtime());
    $end = ((float) $usec + (float) $sec);
    $seconds = $end - $start;

    print self::LIGHTGREEN . 'Data updated in ' . $seconds . ' seconds!' . PHP_EOL;
  }

  public function testDelete() {
    FileDB::delete('votes')
      ->condition('id', 10, FIleDBConditionTypes::LESS_THAN)
      ->execute();
  }
}