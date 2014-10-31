<?php

 

class QPDB implements QueryPathExtension {
  protected $qp;
  protected $dsn;
  protected $db;
  protected $opts;
  protected $row = NULL;
  protected $stmt = NULL;
  
  protected static $con = NULL;
  
  
  static function baseDB($dsn, $options = array()) {
    
    $opts = $options + array(
      'username' => NULL,
      'password' => NULL,
      'db params' => array(PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION),
    );
    
    
    
    if ($dsn instanceof PDO) {
      self::$con = $dsn;
      return;
    }
    self::$con = new PDO($dsn, $opts['username'], $opts['password'], $opts['db params']);
  }
  
  
  static function getBaseDB() {return self::$con;}
  
  
  protected $cycleRows = FALSE;
  
  
  public function __construct(QueryPath $qp) {
    $this->qp = $qp;
    
    $this->db = self::$con;
  }
  
  
  public function dbInit($dsn, $options = array()) {
    $this->opts = $options + array(
      'username' => NULL,
      'password' => NULL,
      'db params' => array(PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION),
    );
    $this->dsn = $dsn;
    $this->db = new PDO($dsn, $this->opts['username'], $this->opts['password'], $this->opts['db params']);
    
    
    return $this->qp;
  }
  
  
  public function query($sql, $args = array()) {
    $this->stmt = $this->db->prepare($sql);
    $this->stmt->execute($args);
    return $this->qp;
  }
  
  
  public function queryInto($sql, $args = array(), $template = NULL) {
    $stmt = $this->db->prepare($sql);
    $stmt->setFetchMode(PDO::FETCH_ASSOC);
    $stmt->execute($args);
    
    
    if (empty($template)) {
      foreach ($stmt as $row) foreach ($row as $datum) $this->qp->append($datum);
    }
    
    else {
      foreach ($stmt as $row) $this->qp->tpl($template, $row);
    }
    
    $stmt->closeCursor();
    return $this->qp;
  }
  
  
  public function doneWithQuery() {
    if (isset($this->stmt) && $this->stmt instanceof PDOStatement) {
      
      
      $this->stmt->closeCursor();
    }
      
    unset($this->stmt);
    $this->row = NULL;
    $this->cycleRows = FALSE;
    return $this->qp;
  }
  
  
  public function exec($sql) {
    $this->db->exec($sql);
    return $this->qp;
  }
  
  
  public function nextRow() {
    $this->row = $this->stmt->fetch(PDO::FETCH_ASSOC);
    return $this->qp;
  }
  
  
  public function withEachRow() {
    $this->cycleRows = TRUE;
    return $this->qp;
  }
  
  
  protected function addData($columnName, $qpFunc = 'append', $wrap = NULL) {
    $columns = is_array($columnName) ? $columnName : array($columnName);
    $hasWrap = !empty($wrap);
    if ($this->cycleRows) {
      while (($row = $this->stmt->fetch(PDO::FETCH_ASSOC)) !== FALSE) {
        foreach ($columns as $col) {
          if (isset($row[$col])) {
            $data = $row[$col];
            if ($hasWrap) 
              $data = qp()->append($wrap)->deepest()->append($data)->top();
            $this->qp->$qpFunc($data);
          }
        }
      }
      $this->cycleRows = FALSE;
      $this->doneWithQuery();
    }
    else {
      if ($this->row !== FALSE) {
        foreach ($columns as $col) {
          if (isset($this->row[$col])) {
            $data = $this->row[$col];
            if ($hasWrap) 
              $data = qp()->append($wrap)->deepest()->append($data)->top();
            $this->qp->$qpFunc($data);
          }
        }
      }
    }
    return $this->qp;
  }
  
  
  public function getStatement() {
    return $this->stmt;
  }
  
  
  public function getLastInsertID() {
    $con = self::$con;
    return $con->lastInsertId();
  }
  
  
  public function appendColumn($columnName, $wrap = NULL) {
    return $this->addData($columnName, 'append', $wrap); 
  }
  
  
  public function prependColumn($columnName, $wrap = NULL) {
    return $this->addData($columnName, 'prepend', $wrap);
  }
  
  
  public function columnBefore($columnName, $wrap = NULL) {
    return $this->addData($columnName, 'before', $wrap);
  }
  
  
  public function columnAfter($columnName, $wrap = NULL) {
    return $this->addData($columnName, 'after', $wrap);
  }
  
}


if (!defined('QPDB_OVERRIDE'))
  QueryPathExtensionRegistry::extend('QPDB');