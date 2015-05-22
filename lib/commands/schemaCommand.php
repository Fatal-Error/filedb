<?php

class schemaCommand extends FileDBCommand {

  public function createfromfile() {
    $schema_file = __DIR__ . "/../../config/schema.json";

    if (file_exists($schema_file) && ($schema = json_decode(file_get_contents($schema_file)))) {

      foreach ($schema as $table) {
        $fields = new FileDBFieldCollection();

        foreach ($table->fields as $field) {
          $fields->setField(new FileDBField($field->name, $field->type, $field->maxLength, $field->autoincrement, $field->nullable, $field->primaryKey));
        }

        try {
          FileDBA::createTable($table->table, $fields);
          print self::YELLOW . 'Table "' . $table->table . '" created!' . PHP_EOL;
        } catch (Exception $e) {
          print self::LIGHTRED . $e->getMessage() . PHP_EOL;
        }
      }

      print self::LIGHTGREEN . 'Dtabase Schema created successfully!' . PHP_EOL;
    }
    else {
      print self::LIGHTRED . 'No schema was found.' . PHP_EOL;
    }
  }

  public function truncate() {
    $tables = FileDBA::showTables();

    foreach ($tables as $table) {
      try {
        FileDBA::dropTable($table);
        print self::YELLOW . 'Table "' . $table . '" dropped!' . PHP_EOL;
      } catch (Exception $e) {
        print self::LIGHTRED . $e->getMessage() . PHP_EOL;
      }
    }

    print self::LIGHTGREEN . 'Dtabase Schema truncated successfully!' . PHP_EOL;
  }

}