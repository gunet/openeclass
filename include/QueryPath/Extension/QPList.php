<?php


 
class QPList implements QueryPathExtension {
  const UL = 'ul';
  const OL = 'ol';
  const DL = 'dl';
  
  protected $qp = NULL;
  public function __construct(QueryPath $qp) {
    $this->qp = $qp;
  }
  
  public function appendTable($items, $options = array()) {
    $opts = $options + array(
      'table class' => 'qptable',
    );
    $base = '<?xml version="1.0"?>
    <table>
    <tbody>
      <tr></tr>
    </tbody>
    </table>';
    
    $qp = qp($base, 'table')->addClass($opts['table class'])->find('tr');
    if ($items instanceof TableAble) {
      $headers = $items->getHeaders();
      $rows = $items->getRows();
    }
    elseif ($items instanceof Traversable) {
      $headers = array();
      $rows = $items;
    }
    else {
      $headers = $items['headers'];
      $rows = $items['rows'];
    }
    
    
    foreach ($headers as $header) {
      $qp->append('<th>' . $header . '</th>');
    }
    $qp->top()->find('tr:last');
    
    
    foreach ($rows as $row) {
      $qp->after('<tr/>')->next();
      foreach($row as $cell) $qp->append('<td>' . $cell . '</td>');
    }
    
    $this->qp->append($qp->top());
    
    return $this->qp;
  }
  
  
  public function appendList($items, $type = self::UL, $options = array()) {
    $opts = $options + array(
      'list class' => 'qplist',
    );
    if ($type == self::DL) {
      $q = qp('<?xml version="1.0"?><dl></dl>', 'dl')->addClass($opts['list class']);
      foreach ($items as $dt => $dd) {
        $q->append('<dt>' . $dt . '</dt><dd>' . $dd . '</dd>');
      }
      $q->appendTo($this->qp);
    }
    else {
      $q = $this->listImpl($items, $type, $opts);
      $this->qp->append($q->find(':root'));
    }
    
    return $this->qp;
  }
  
  
  protected function listImpl($items, $type, $opts, $q = NULL) {
    $ele = '<' . $type . '/>';
    if (!isset($q))
      $q = qp()->append($ele)->addClass($opts['list class']);
          
    foreach ($items as $li) {
      if ($li instanceof QueryPath) {
        $q = $this->listImpl($li->get(), $type, $opts, $q);
      }
      elseif (is_array($li) || $li instanceof Traversable) {
        $q->append('<li><ul/></li>')->find('li:last > ul');
        $q = $this->listImpl($li, $type, $opts, $q);
        $q->parent();
      }
      else {
        $q->append('<li>' . $li . '</li>');
      }
    }
    return $q;
  }
  
  
  protected function isAssoc($array) {
    
    return count(array_diff_key($array, range(0, count($array) - 1))) != 0; 
  }
}
QueryPathExtensionRegistry::extend('QPList');


interface TableAble {
  public function getHeaders();
  public function getRows();
  public function size();
}


class QPTableData implements TableAble, IteratorAggregate {
  
  protected $headers;
  protected $rows;
  protected $caption;
  protected $p = -1;
  
  public function setHeaders($array) {$this->headers = $array; return $this;}
  public function getHeaders() {return $this->headers; }
  public function setRows($array) {$this->rows = $array; return $this;}
  public function getRows() {return $this->rows;}
  public function size() {return count($this->rows);}
  public function getIterator() {
    return new ArrayIterator($rows);
  }
}


class QPTableTextData extends QPTableData {
  public function setHeaders($array) {
    $headers = array();
    foreach ($array as $header) {
      $headers[] = htmlentities($header);
    }
    parent::setHeaders($headers);
    return $this;
  }
  public function setRows($array) {
    $count = count($array);
    for ($i = 0; $i < $count; ++$i) {
      $cols = array();
      foreach ($data[$i] as $datum) {
        $cols[] = htmlentities($datum);
      }
      $data[$i] = $cols;
    }
    parent::setRows($array);
    return $this;
  }
}