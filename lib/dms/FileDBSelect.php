<?php

require_once __DIR__.'/../relationships/FileDBJoin.php';
require_once __DIR__.'/../relationships/FileDBInnerJoin.php';
require_once __DIR__.'/../relationships/FileDBLeftJoin.php';

class FileDBSelect extends FileDBConditionQuery implements FileDBQueryInterface, Countable {

  protected $base;
  protected $tables;
  protected $joins;
  protected $fields;
  protected $limit;
  protected $orderBy;
  private   $distinct;
  private   $rows;

  /**
   * @param string $table
   * @param string $alias
   */
  public function __construct($table, $alias) {
    $this->distinct = FALSE;
    $this->setBase($alias);
    $this->addTable($table, $alias);
  }

  /**
   * @return string
   */
  public function getBase() {
    return $this->base;
  }

  /**
   * @param string $base
   */
  private function setBase($base) {
    $this->base = $base;
  }

  /**
   * @param string $table
   * @param string $alias
   */
  private function addTable($table, $alias) {
    $tables = $this->getTables();

    if (empty($tables[$alias])) {
      $this->tables[$alias] = FileDB::getTable($table);
    }
  }

  /**
   * @param string $alias
   * @return \FileDBTable
   * @throws \Exception
   */
  public function getTable($alias) {
    $tables = $this->getTables();

    if (!isset($tables[$alias])) {
      throw new \Exception(sprintf('Cannot find table with unknown alias: "%s".', $alias));
    }

    return $tables[$alias];
  }

  /**
   * @return array
   */
  public function getTables() {
    return $this->tables;
  }
  
  /**
   * @return \FileDBSelect
   */
  public function distinct() {
    $this->distinct = TRUE;
    
    return $this;
  }
  
  /**
   * @return boolean
   */
  public function isDistinct() {
    return $this->distinct;
  }

  /**
   * @param string $alias
   * @param array $fields
   * @return \FileDBSelect
   * @throws \Exception
   */
  public function fields($alias, array $fields = array()) {
    $table = $this->getTable($alias);
    
    if (empty($fields)) {
      $fields = $table->getFields()->getNames();
    }
    
    if (count(array_intersect($table->getFields()->getNames(), $fields)) == count($fields)) {
      foreach ($fields as $field) {
        $this->fields[] = $alias . '.' . $field;
      }
    }
    else {
      throw new \Exception(sprintf('Unable to select specified fields "%s" from table "%s".', implode(', ', $fields), $table->getTableName()));
    }

    return $this;
  }

  /**
   * @return array table fields
   */
  public function getFields() {
    return $this->fields;
  }
  
  /**
   * @param string $field
   * @param mixed $value
   * @param FileDBConditionTypes $conditionType
   * @return \FileDBSelect
   * @throws \Exception
   */
  public function condition($field, $value, $conditionType = FIleDBConditionTypes::EQUALS, $group = 0) {
    list($alias, $fieldName) = explode('.', $field);
    
    $table = $this->getTable($alias);
    if(!$table->getFields()->getField($fieldName)) {
      throw new \Exception(sprintf('Unable to set a condition for field "%s" of table "%s".', $field, $table->getTableName()));
    }
    
    $condition = FileDBConditionFactory::getCondition($field, $value, $conditionType);
    
    if ($alias == $this->getBase()) {
      $this->addCondition($condition, $group);
    }
    else {
      $this->getJoin($alias)->addCondition($condition, $group, $this->getConditionMethod());
    }
    
    return $this;
  }
  
  /**
   * @param FileDBJoin $join
   */
  public function addJoin(FileDBJoin $join) {
    $this->joins[$join->getLeftAlias()] = $join;
  }

  /**
   * @param string $leftTable
   * @param string $leftAlias
   * @param string $leftField
   * @param string $rightTable
   * @param string $rightAlias
   * @param string $rightField
   * @return \FileDBSelect
   */
  public function innerJoin($leftTable, $leftAlias, $leftField, $rightTable, $rightAlias, $rightField) {
    try {
      if ($this->getTable($leftAlias)) {
        throw new \Exception(sprintf('Alias table "%s" for Inner Join is already in use.', $leftAlias));
      }
    } catch (Exception $exc) { ; }

    $this->addJoin(new FileDBInnerJoin($leftTable, $leftAlias, $leftField, $rightTable, $rightAlias, $rightField));
    $this->addTable($leftTable, $leftAlias);

    return $this;
  }

  /**
   * @param string $leftTable
   * @param string $leftAlias
   * @param string $leftField
   * @param string $rightTable
   * @param string $rightAlias
   * @param string $rightField
   * @return \FileDBSelect
   */
  public function leftJoin($leftTable, $leftAlias, $leftField, $rightTable, $rightAlias, $rightField) {
    try {
      if ($this->getTable($leftAlias)) {
        throw new \Exception(sprintf('Alias table "%s" for Left Join is already in use.', $leftAlias));
      }
    } catch (Exception $exc) { ; }
    
    $this->addJoin(new FileDBLeftJoin($leftTable, $leftAlias, $leftField, $rightTable, $rightAlias, $rightField));
    $this->addTable($leftTable, $leftAlias);

    return $this;
  }

  /**
   * @return array FileDBJoin
   */
  public function getJoins() {
    return !empty($this->joins) ? $this->joins : array();
  }
  
  /**
   * @param string $alias
   * @return \FileDBJoin
   */
  public function getJoin($alias) {
    $joins = $this->getJoins();
    return !empty($joins[$alias]) ? $joins[$alias] : FALSE;
  }

  /**
   * @param int $limit
   * @param int $offset
   * @return \FileDBSelect
   */
  public function limit($limit, $offset = 0) {
    $this->limit = array(
      'limit' => $limit,
      'offset' => $offset
    );

    return $this;
  }

  /**
   * @return array an array containing info about 'limit' and 'offset'
   */
  public function getLimit() {
    return $this->limit;
  }

  /**
   * @param string $field
   * @param type $sort
   * @return \FileDBSelect
   * @throws \Exception
   */
  public function orderBy($field, $sort) {
    if (in_array($field, $this->getFields())) {
      $this->orderBy[] = array(
        'field' => $field,
        'sort' => $sort
      );

      return $this;
    }
    else {
      throw new \Exception(sprintf('Cannot sort by field "%s". Fields mismatch.', $field));
    }
  }

  /**
   * @return array
   */
  public function getOrder() {
    return !empty($this->orderBy) ? $this->orderBy : array();
  }

  /**
   * @return \FileDBSelect
   */
  public function execute() {
    $rows = $this->getTable($this->getBase())->getRows();
    $this->setFieldAlias($this->getBase(), $rows);
    $this->rows = $rows;
    
    /* CONDITIONS */
    foreach($this->getConditions() as $group) {
      foreach ($group as $conditionMethod => $conditions) {
        switch ($conditionMethod) {
          case self::CONDITION_AND:
            foreach ($this->rows as $id => $row) {
              foreach ($conditions as $condition) {
                if($condition->compare($row)) {
                  unset($this->rows[$id]);
                  break;
                }
              }
            }
          break;

          case self::CONDITION_OR:
            foreach ($this->rows as $id => $row) {
              $satisfied = 0;

              foreach ($conditions as $condition) {
                if($condition->compare($row)) {
                  $satisfied++;
                }
              }

              if (count($conditions) == $satisfied) {
                unset($this->rows[$id]);
              }
            }
          break;
        }
      }
    }
    /* END CONDITIONS */

    /* JOINS */
    foreach ($this->getJoins() as $join) {
      $this->rows = $join->doJoin($this->rows, $this->getFields());
    }
    /* END JOINS */
    
    /* FIELDS */
    $selectedFields = $this->getFields();
    $fields = array_combine(array_values($selectedFields), array_values($selectedFields));
    
    foreach ($this->rows as $id => $row) {
      $this->rows[$id] = array_intersect_key($row, $fields);
    }
    /* END FIELDS */
    
    /* ORDER BY */
    foreach ($this->getOrder() as $order) {
      $grouped_rows = $this->fetchAllGrouped();
      if (!empty($grouped_rows[$order['field']])) {
        array_multisort($grouped_rows[$order['field']], $order['sort'], $this->rows);
      }
    }
    /* END ORDER BY */
    
    /* LIMIT */
    $limit = $this->getLimit();
    if ($limit) {
      $this->rows = array_slice($this->rows, $limit['offset'], $limit['limit']);
    }
    /* END LIMIT */
    
    /* DISTINCT */
    if ($this->isDistinct()) {
      $this->rows = array_intersect_key($this->rows, array_unique(array_map('serialize', $this->rows)));
    }
    /* END DISTINCT */
    
    return $this;
  }
  
  /**
   * @return mixed
   */
  public function fetch(){
    return !empty($this->rows) ? array_shift($this->rows) : FALSE;
  }
  
  /**
   * @param boolean $index Default: FALSE. Set it to TRUE to get results keyed by index
   * @return mixed An array containing rows extracted.
   * Returns FALSE if there aren't rows to fetch.
   */
  public function fetchAll($index = FALSE, $aliased = TRUE) {
    if (empty($this->rows)) {
      return FALSE;
    }
    
    if (!$aliased) {
      foreach ($this->rows as $i => $row) {
        $unaliased = array();

        foreach($row as $field => $value) {
          $data = explode('.', $field);
          unset($data[0]);
          $unaliased[implode('.', $data)] = $value;
        }

        $this->rows[$i] = $unaliased;
      }
    }

    return $index ? $this->rows : array_values($this->rows);
  }
  
  public function fetchAllGrouped() {
    if (empty($this->rows)) {
      return FALSE;
    }

    $rows = array_values($this->rows);
    $grouped_rows = array();

    foreach ($rows as $id => $row) {
      foreach ($row as $field => $value) {
        $grouped_rows[$field][$id] = $value;
      }
    }

    return $grouped_rows;
  }
  
  /**
   * @return int
   */
  public function count() {
    return count($this->rows);
  }

  /**
   * @param string $alias
   * @param array $rows
   */
  private function setFieldAlias($alias, &$rows) {
    $aliased = array();
    
    foreach ($rows as $id => $row) {
      foreach ($row as $field => $value) {
        $aliased[$id][$alias . '.' . $field] = $value;
      }
    }

    $rows = $aliased;
  }

}
