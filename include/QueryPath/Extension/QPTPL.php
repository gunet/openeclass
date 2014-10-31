<?php


class QPTPL implements QueryPathExtension {
  protected $qp;
  public function __construct(QueryPath $qp) {
    $this->qp = $qp;
  }
  
  
  public function tpl($template, $object, $options = array()) {
    

    
    $tqp = qp($template);
    
    if (is_array($object) || $object instanceof Traversable) {
      $this->tplArrayR($tqp, $object, $options);
      return $this->qp->append($tqp->top());
    }
    elseif (is_object($object)) {
      $this->tplObject($tqp, $object, $options);
    }
    
    return $this->qp->append($tqp->top());
  }
  
  
  public function tplAll($template, $objects, $options = array()) {
    $tqp = qp($template, ':root');
    foreach ($objects as $object) {
      if (is_array($object)) 
        $tqp = $this->tplArrayR($tqp, $object, $options);
      elseif (is_object($object)) 
        $tqp = $this->tplObject($tqp, $object, $options);
    }
    return $this->qp->append($tqp->top());
  }
  
  
  
  
  protected function tplObject($tqp, $object, $options = array()) {
    $ref = new ReflectionObject($object);
    $methods = $ref->getMethods();
    foreach ($methods as $method) {
      if (strpos($method->getName(), 'get') === 0) {
        $cssClass = $this->method2class($method->getName());
        if ($tqp->top()->find($cssClass)->size() > 0) {
          $tqp->append($method->invoke($object));
        }
        else {
          
          $tqp->end();
        }
      }
    }
    
    return $tqp;
  }
  
  
  public function tplArrayR($qp, $array, $options = NULL) {
    
    if (!is_array($array) && !($array instanceof Traversable)) {
      $qp->append($array);
    }
    
    
    elseif ($this->isAssoc($array)) {
      
      foreach ($array as $k => $v) {
        
        
        $first = substr($k,0,1);
        if ($first != '.' && $first != '#') $k = '.' . $k;
        
        
        if (is_array($v)) {
          
          
          
          $this->tplArrayR($qp->top($k), $v, $options);
        }
        
        else {
          $qp->branch()->children($k)->append($v);
        }
      }
    }
    
    
    else {
      
      foreach ($array as $entry) {
        $eles = $qp->get();
        $template = array();
        
        
        foreach ($eles as $ele) {
          $template = $ele->cloneNode(TRUE);
        }
        $tpl = qp($template);
        $tpl = $this->tplArrayR($tpl, $entry, $options);
        $qp->before($tpl);
      }
      
      
      $dead = $qp->branch();
      $qp->parent();
      $dead->remove();
      unset($dead);
    }
    return $qp;
  }
  
  
  public function isAssoc($array) {
    $i = 0;
    foreach ($array as $k => $v) if ($k !== $i++) return TRUE;
    
    return FALSE;
  }

  
  protected function method2class($mname) {
    return '.' . substr($mname, 3);
  }
}
QueryPathExtensionRegistry::extend('QPTPL');