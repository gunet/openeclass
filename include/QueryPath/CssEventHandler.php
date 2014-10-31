<?php



require_once 'CssParser.php';


class QueryPathCssEventHandler implements CssEventHandler {
  protected $dom = NULL; 
  protected $matches = NULL; 
  protected $alreadyMatched = NULL; 
  protected $findAnyElement = TRUE;
  
  
  
  public function __construct($dom) {
    $this->alreadyMatched = new SplObjectStorage();
    $matches = new SplObjectStorage();
    
    
    if (is_array($dom) || $dom instanceof SplObjectStorage) {
      
      foreach($dom as $item) {
        if ($item instanceof DOMNode && $item->nodeType == XML_ELEMENT_NODE) {
          
          $matches->attach($item);
        }
      }
      
      if ($matches->count() > 0) {
        $matches->rewind();
        $this->dom = $matches->current();
      }
      else {
        
        $this->dom = NULL;
      }
      $this->matches = $matches;
    }
    
    elseif ($dom instanceof DOMDocument) {
      $this->dom = $dom->documentElement;
      $matches->attach($dom->documentElement);
    }
    
    elseif ($dom instanceof DOMElement) {
      $this->dom = $dom;
      $matches->attach($dom);
    }
    
    elseif ($dom instanceof DOMNodeList) {
      $a = array(); 
      foreach ($dom as $item) {
        if ($item->nodeType == XML_ELEMENT_NODE) {
          $matches->attach($item);
          $a[] = $item; 
        }
      }
      $this->dom = $a;
    }
    
    
    else {
      throw new Exception("Unhandled type: " . get_class($dom));
    }
    $this->matches = $matches;
  }
  
  
  public function find($filter) {
    $parser = new CssParser($filter, $this);
    $parser->parse();
    return $this;
  }
  
  
  public function getMatches() {
    
    $result = new SplObjectStorage();
    foreach($this->alreadyMatched as $m) $result->attach($m);
    foreach($this->matches as $m) $result->attach($m);
    return $result;
  }
  
  
  public function elementID($id) {
    $found = new SplObjectStorage();
    $matches = $this->candidateList();
    foreach ($matches as $item) {
      
      if ($item->hasAttribute('id') && $item->getAttribute('id') === $id) {
        $found->attach($item);
        break;
      }
    }
    $this->matches = $found;
    $this->findAnyElement = FALSE;
  }
  
  
  public function element($name) {
    $matches = $this->candidateList();
    $this->findAnyElement = FALSE;
    $found = new SplObjectStorage();
    foreach ($matches as $item) {
      
      
      
      if ($item->tagName == $name) {
        $found->attach($item);
      }
      
      
      
    }
    
    $this->matches = $found;
  }
  
  
  public function elementNS($lname, $namespace = NULL) {
    $this->findAnyElement = FALSE;
    $found = new SplObjectStorage();
    $matches = $this->candidateList();
    foreach ($matches as $item) {
      
      
      
      
      
      
      
      $nsuri = $this->dom->lookupNamespaceURI($namespace);
      
      
      
      
      
      
      if ($item instanceof DOMNode 
          && $item->namespaceURI == $nsuri 
          && $lname == $item->localName) {
        $found->attach($item);
      }
      
      if (!empty($nsuri)) {
        $nl = $item->getElementsByTagNameNS($nsuri, $lname);
        
        
        if (!empty($nl)) $this->attachNodeList($nl, $found);
      }
      else {
        
        $nl = $item->getElementsByTagName($lname);
        $tagname = $namespace . ':' . $lname;
        $nsmatches = array();
        foreach ($nl as $node) {
          if ($node->tagName == $tagname) {
            
            $found->attach($node);
          }
        }
        
        
      }
    }
    $this->matches = $found;
  }
  
  public function anyElement() {
    $found = new SplObjectStorage();
    
    $matches = $this->candidateList();
    foreach ($matches as $item) {
      $found->attach($item); 
      
      
      
      
    }
    
    $this->matches = $found;
    $this->findAnyElement = FALSE;
  }
  public function anyElementInNS($ns) {
    
    $nsuri = $this->dom->lookupNamespaceURI($ns);
    $found = new SplObjectStorage();
    if (!empty($nsuri)) {
      $matches = $this->candidateList();
      foreach ($matches as $item) {
        if ($item instanceOf DOMNode && $nsuri == $item->namespaceURI) {
          $found->attach($item);
        }
      }
    }
    $this->matches = $found;
    $this->findAnyElement = FALSE;
  }
  public function elementClass($name) {
    
    $found = new SplObjectStorage();
    $matches = $this->candidateList();
    foreach ($matches as $item) {
      if ($item->hasAttribute('class')) {
        $classes = explode(' ', $item->getAttribute('class'));
        if (in_array($name, $classes)) $found->attach($item);
      }
    }
    
    $this->matches = $found;
    $this->findAnyElement = FALSE;
  }
  
  public function attribute($name, $value = NULL, $operation = CssEventHandler::isExactly) {
    $found = new SplObjectStorage();
    $matches = $this->candidateList();
    foreach ($matches as $item) {
      if ($item->hasAttribute($name)) {
        if (isset($value)) {
          
          if($this->attrValMatches($value, $item->getAttribute($name), $operation)) {
            $found->attach($item);
          }
        }
        else {
          
          $found->attach($item);
        }
      }
    }
    $this->matches = $found; 
    $this->findAnyElement = FALSE;
  }

  
  protected function searchForAttr($name, $value = NULL) {
    $found = new SplObjectStorage();
    $matches = $this->candidateList();
    foreach ($matches as $candidate) {
      if ($candidate->hasAttribute($name)) {
        
        if (isset($value) && $value == $candidate->getAttribute($name)) {
          $found->attach($candidate);
        }
        
        else {
          $found->attach($candidate);
        }
      }
    }
    
    $this->matches = $found;
  }
  
  public function attributeNS($lname, $ns, $value = NULL, $operation = CssEventHandler::isExactly) {
    $matches = $this->candidateList();
    $found = new SplObjectStorage();
    if (count($matches) == 0) {
      $this->matches = $found;
      return;
    }
    
    
    
    $matches->rewind();
    $e = $matches->current();
    $uri = $e->lookupNamespaceURI($ns);
    
    foreach ($matches as $item) {
      
      
      
      if ($item->hasAttributeNS($uri, $lname)) {
        if (isset($value)) {
          if ($this->attrValMatches($value, $item->getAttributeNS($uri, $lname), $operation)) {
            $found->attach($item);
          }
        }
        else {
          $found->attach($item);
        }
      }
    }
    $this->matches = $found;
    $this->findAnyElement = FALSE;
  }
  
  
  public function pseudoClass($name, $value = NULL) {
    $name = strtolower($name);
    
    switch($name) {
      case 'visited':
      case 'hover':
      case 'active':
      case 'focus':
      case 'animated': 
      case 'visible':
      case 'hidden':
        
      case 'target':
        
        $this->matches = new SplObjectStorage();
        break;
      case 'indeterminate':
        
        
        throw new NotImplementedException(":indeterminate is not implemented.");
        break;
      case 'lang':
        
        if (!isset($value)) {
          throw new NotImplementedException("No handler for lang pseudoclass without value.");
        }
        $this->lang($value);
        break;
      case 'link':
        $this->searchForAttr('href');
        break;
      case 'root':
        $found = new SplObjectStorage();
        if (empty($this->dom)) {
          $this->matches = $found;
        }
        elseif (is_array($this->dom)) {
          $found->attach($this->dom[0]->ownerDocument->documentElement);
          $this->matches = $found;
        }
        elseif ($this->dom instanceof DOMNode) {
          $found->attach($this->dom->ownerDocument->documentElement);
          $this->matches = $found;
        }
        elseif ($this->dom instanceof DOMNodeList && $this->dom->length > 0) {
          $found->attach($this->dom->item(0)->ownerDocument->documentElement);
          $this->matches = $found;
        }
        else {
          
          $found->attach($this->dom);
          $this->matches = $found;
        }
        break;
      
      
      
      case 'x-root':
      case 'x-reset':
        $this->matches = new SplObjectStorage();
        $this->matches->attach($this->dom);
        break;        
      
      
      
      case 'even':
        $this->nthChild(2, 0);
        break;
      case 'odd':
        $this->nthChild(2, 1);
        break;
      
      
      case 'nth-child':
        list($aVal, $bVal) = $this->parseAnB($value);
        $this->nthChild($aVal, $bVal);
        break;
      case 'nth-last-child':
        list($aVal, $bVal) = $this->parseAnB($value);
        $this->nthLastChild($aVal, $bVal);
        break;
      case 'nth-of-type':
        list($aVal, $bVal) = $this->parseAnB($value);
        $this->nthOfTypeChild($aVal, $bVal, FALSE);
        break;
      case 'nth-last-of-type':
        list($aVal, $bVal) = $this->parseAnB($value);
        $this->nthLastOfTypeChild($aVal, $bVal);
        break;
      case 'first-child':
        $this->nthChild(0, 1);
        break;
      case 'last-child':
        $this->nthLastChild(0, 1);
        break;
      case 'first-of-type':
        $this->firstOfType();
        break;
      case 'last-of-type':
        $this->lastOfType();
        break;
      case 'only-child':
        $this->onlyChild();
        break;
      case 'only-of-type':
        $this->onlyOfType();
        break;
      case 'empty':
        $this->emptyElement();
        break;  
      case 'not':
        if (empty($value)) {
          throw new CssParseException(":not() requires a value.");
        }
        $this->not($value);
        break;
      
      case 'lt':
      case 'gt':
      case 'nth':
      case 'eq':
      case 'first':
      case 'last':
      
      
        $this->getByPosition($name, $value);  
        break;
      case 'parent':
        $matches = $this->candidateList();
        $found = new SplObjectStorage();
        foreach ($matches as $match) {
          if (!empty($match->firstChild)) {
            $found->attach($match);
          }
        }
        $this->matches = $found;
        break;
      
      case 'enabled':  
      case 'disabled':  
      case 'checked':  
        $this->attribute($name);
        break;
      case 'text':
      case 'radio':
      case 'checkbox':
      case 'file':
      case 'password':
      case 'submit':
      case 'image':
      case 'reset':
      case 'button':
        $this->attribute('type', $name);
        break;

      case 'header':
        $matches = $this->candidateList();
        $found = new SplObjectStorage();
        foreach ($matches as $item) {
          $tag = $item->tagName;
          $f = strtolower(substr($tag, 0, 1));
          if ($f == 'h' && strlen($tag) == 2 && ctype_digit(substr($tag, 1, 1))) {
            $found->attach($item);
          }
        }
        $this->matches = $found;
        break;
      case 'has':
        $this->has($value);
        break;
      
      
      case 'contains':
        $value = $this->removeQuotes($value);
    
        $matches = $this->candidateList();
        $found = new SplObjectStorage();
        foreach ($matches as $item) {
          if (strpos($item->textContent, $value) !== FALSE) {
            $found->attach($item);
          }
        }
        $this->matches = $found;
        break;
        
      
      case 'contains-exactly':
        $value = $this->removeQuotes($value);
      
        $matches = $this->candidateList();
        $found = new SplObjectStorage();
        foreach ($matches as $item) {
          if ($item->textContent == $value) {
            $found->attach($item);
          }
        }
        $this->matches = $found;
        break;
      default:
        throw new CssParseException("Unknown Pseudo-Class: " . $name);
    }
    $this->findAnyElement = FALSE;
  }
  
  
  private function removeQuotes($str) {
    $f = substr($str, 0, 1);
    $l = substr($str, -1);
    if ($f === $l && ($f == '"' || $f == "'")) {
      $str = substr($str, 1, -1);
    }
    return $str;
  }
  
  
  private function getByPosition($operator, $pos) {
    $matches = $this->candidateList();
    $found = new SplObjectStorage();
    if ($matches->count() == 0) {
      return;
    }
    
    switch ($operator) {
      case 'nth':
      case 'eq':
        if ($matches->count() >= $pos) {
          
          foreach ($matches as $match) {
            
            if ($matches->key() + 1 == $pos) {
              $found->attach($match);
              break;
            }
          }
        }
        break;
      case 'first':
        if ($matches->count() > 0) {
          $matches->rewind(); 
          $found->attach($matches->current());
        }
        break;
      case 'last':
        if ($matches->count() > 0) {
          
          
          foreach ($matches as $item) {};
         
          $found->attach($item);
        }
        break;
      
      
      
      
      
      
      
      
      
      
      
      
      
      
      case 'lt':
        $i = 0;
        foreach ($matches as $item) {
          if (++$i < $pos) {
            $found->attach($item);
          }
        }
        break;
      case 'gt':
        $i = 0;
        foreach ($matches as $item) {
          if (++$i > $pos) {
            $found->attach($item);
          }
        }
        break;
    }
    
    $this->matches = $found;
  }
  
  
  protected function parseAnB($rule) {
    if ($rule == 'even') {
      return array(2, 0);
    }
    elseif ($rule == 'odd') {
      return array(2, 1);
    }
    elseif ($rule == 'n') {
      return array(1, 0);
    }
    elseif (is_numeric($rule)) {
      return array(0, (int)$rule);
    }
    
    $rule = explode('n', $rule);
    if (count($rule) == 0) {
      throw new CssParseException("nth-child value is invalid.");
    }
    
    
    $aVal = trim($rule[0]);
    $aVal = ($aVal == '-') ? -1 : (int)$aVal;
    
    $bVal = !empty($rule[1]) ? (int)trim($rule[1]) : 0;
    return array($aVal, $bVal);
  }
  
  
  protected function nthChild($groupSize, $elementInGroup, $lastChild = FALSE) {
    
    
    
    $parents = new SplObjectStorage();
    $matches = new SplObjectStorage();
    
    $i = 0;
    foreach ($this->matches as $item) {
      $parent = $item->parentNode;
      
      
      
      
      if (!$parents->contains($parent)) {
        
        $c = 0;
        foreach ($parent->childNodes as $child) {
          
          
          
          
          
          if ($child->nodeType == XML_ELEMENT_NODE && ($this->findAnyElement || $child->tagName == $item->tagName)) {
            
            $child->nodeIndex = ++$c;
          }
        }
        
        $parent->numElements = $c;
        $parents->attach($parent);
      }
      
      
      
      if ($lastChild) {
        $indexToMatch = $item->parentNode->numElements  - $item->nodeIndex + 1;
      }
      
      else {
        $indexToMatch = $item->nodeIndex;
      }
      
      
      if ($groupSize == 0) {
        if ($indexToMatch == $elementInGroup) 
          $matches->attach($item);
      }
      
      
      else {
        if (($indexToMatch - $elementInGroup) % $groupSize == 0 
            && ($indexToMatch - $elementInGroup) / $groupSize >= 0) {
          $matches->attach($item);
        }
      }
      
      
      ++$i;
    }
    $this->matches = $matches;
  }
  
  
  
  
  protected function nthLastChild($groupSize, $elementInGroup) {
    
    $this->nthChild($groupSize, $elementInGroup, TRUE);
  }
  
  
  
   
  
  
  protected function nthOfTypeChild($groupSize, $elementInGroup, $lastChild) {
    
    
    
    $parents = new SplObjectStorage();
    $matches = new SplObjectStorage();
    
    $i = 0;
    foreach ($this->matches as $item) {
      $parent = $item->parentNode;
      
      
      
      
      if (!$parents->contains($parent)) {
        
        $c = 0;
        foreach ($parent->childNodes as $child) {
          
          
          if ($child->nodeType == XML_ELEMENT_NODE && $child->tagName == $item->tagName) {
            
            $child->nodeIndex = ++$c;
          }
        }
        
        $parent->numElements = $c;
        $parents->attach($parent);
      }
      
      
      
      if ($lastChild) {
        $indexToMatch = $item->parentNode->numElements  - $item->nodeIndex + 1;
      }
      
      else {
        $indexToMatch = $item->nodeIndex;
      }
      
      
      if ($groupSize == 0) {
        if ($indexToMatch == $elementInGroup) 
          $matches->attach($item);
      }
      
      
      else {
        if (($indexToMatch - $elementInGroup) % $groupSize == 0 
            && ($indexToMatch - $elementInGroup) / $groupSize >= 0) {
          $matches->attach($item);
        }
      }
      
      
      ++$i;
    }
    $this->matches = $matches;
  }
  
  
  protected function nthLastOfTypeChild($groupSize, $elementInGroup) {
    $this->nthOfTypeChild($groupSize, $elementInGroup, TRUE);    
  }
  
  
  protected function lang($value) {
    
    
    
    $operator = (strpos($value, '-') !== FALSE) ? self::isExactly : self::containsWithHyphen;
    
    $orig = $this->matches;
    $origDepth = $this->findAnyElement;
    
    
    $this->attribute('lang', $value, $operator);
    $lang = $this->matches; 
    
    
    $this->matches = $orig;
    $this->findAnyElement = $origDepth;
    
    
    $this->attributeNS('lang', 'xml', $value, $operator);
    
    
    
    
    
    
    foreach ($this->matches as $added) $lang->attach($added);
    $this->matches = $lang;
  }
  
  
  protected function not($filter) {
    $matches = $this->candidateList();
    
    $found = new SplObjectStorage();
    foreach ($matches as $item) {
      $handler = new QueryPathCssEventHandler($item);
      $not_these = $handler->find($filter)->getMatches();
      if ($not_these->count() == 0) {
        $found->attach($item);
      }
    }
    
    
    $this->matches = $found;    
  }
  
  
  public function has($filter) {
    $matches = $this->candidateList();
    
    $found = new SplObjectStorage();
    foreach ($matches as $item) {
      $handler = new QueryPathCssEventHandler($item);
      $these = $handler->find($filter)->getMatches();
      if (count($these) > 0) {
        $found->attach($item);
      }      
    }
    $this->matches = $found;
    return $this;
  }
  
  
  protected function firstOfType() {
    $matches = $this->candidateList();
    $found = new SplObjectStorage();
    foreach ($matches as $item) {
      $type = $item->tagName;
      $parent = $item->parentNode;
      foreach ($parent->childNodes as $kid) {
        if ($kid->nodeType == XML_ELEMENT_NODE && $kid->tagName == $type) {
          if (!$found->contains($kid)) {
            $found->attach($kid);
          }
          break;
        }
      }
    }
    $this->matches = $found;
  }
  
  
  protected function lastOfType() {
    $matches = $this->candidateList();
    $found = new SplObjectStorage();
    foreach ($matches as $item) {
      $type = $item->tagName;
      $parent = $item->parentNode;
      for ($i = $parent->childNodes->length - 1; $i >= 0; --$i) {
        $kid = $parent->childNodes->item($i);
        if ($kid->nodeType == XML_ELEMENT_NODE && $kid->tagName == $type) {
          if (!$found->contains($kid)) {
            $found->attach($kid);
          }
          break;
        }
      }
    }
    $this->matches = $found;
  }
  
  
  protected function onlyChild() {
    $matches = $this->candidateList();
    $found = new SplObjectStorage();
    foreach($matches as $item) {
      $parent = $item->parentNode;
      $kids = array();
      foreach($parent->childNodes as $kid) {
        if ($kid->nodeType == XML_ELEMENT_NODE) {
          $kids[] = $kid;
        }
      }
      
      
      if (count($kids) == 1 && $kids[0] === $item) {
        $found->attach($kids[0]);
      }
    }
    $this->matches = $found;
  }
  
  
  protected function emptyElement() {
    $found = new SplObjectStorage();
    $matches = $this->candidateList();
    foreach ($matches as $item) {
      $empty = TRUE;
      foreach($item->childNodes as $kid) {
        
        
        if ($kid->nodeType == XML_ELEMENT_NODE || $kid->nodeType == XML_TEXT_NODE) {
          $empty = FALSE;
          break;
        }
      }
      if ($empty) {
        $found->attach($item);
      }
    }
    $this->matches = $found;
  }
  
  
  protected function onlyOfType() {
    $matches = $this->candidateList();
    $found = new SplObjectStorage();
    foreach ($matches as $item) {
      if (!$item->parentNode) {
        $this->matches = new SplObjectStorage();
      }
      $parent = $item->parentNode;
      $onlyOfType = TRUE;
      
      
      foreach($parent->childNodes as $kid) {
        if ($kid->nodeType == XML_ELEMENT_NODE 
            && $kid->tagName == $item->tagName 
            && $kid !== $item) {
          
          $onlyOfType = FALSE;
          break;
        }
      }
      
      
      if ($onlyOfType) $found->attach($item);
    }
    $this->matches = $found;
  }
  
  
  protected function attrValMatches($needle, $haystack, $operation) {
    
    if (strlen($haystack) < strlen($needle)) return FALSE;
    
    
    
    
    
    switch ($operation) {
      case CssEventHandler::isExactly:
        return $needle == $haystack;
      case CssEventHandler::containsWithSpace:
        return in_array($needle, explode(' ', $haystack));
      case CssEventHandler::containsWithHyphen:
        return in_array($needle, explode('-', $haystack));
      case CssEventHandler::containsInString:
        return strpos($haystack, $needle) !== FALSE;
      case CssEventHandler::beginsWith:
        return strpos($haystack, $needle) === 0;
      case CssEventHandler::endsWith:
        
        return preg_match('/' . $needle . '$/', $haystack) == 1;
    }
    return FALSE; 
  }
  
  
  public function pseudoElement($name) {
    
    switch ($name) {
      
      
      case 'first-line':
        $matches = $this->candidateList();
        $found = new SplObjectStorage();
        $o = new stdClass();
        foreach ($matches as $item) {
          $str = $item->textContent;
          $lines = explode("\n", $str);
          if (!empty($lines)) {
            $line = trim($lines[0]);
            if (!empty($line))
              $o->textContent = $line;
              $found->attach($o);
          }
        }
        $this->matches = $found;
        break;
      
      
      case 'first-letter':
        $matches = $this->candidateList();
        $found = new SplObjectStorage();
        $o = new stdClass();
        foreach ($matches as $item) {
          $str = $item->textContent;
          if (!empty($str)) {
            $str = substr($str,0, 1);
            $o->textContent = $str;
            $found->attach($o);
          }
        }
        $this->matches = $found;
        break;
      case 'before':
      case 'after':
        
        
      case 'selection':
        
        throw new NotImplementedException("The $name pseudo-element is not implemented.");
        break;
    }
    $this->findAnyElement = FALSE;  
  }
  public function directDescendant() {
    $this->findAnyElement = FALSE;
        
    $kids = new SplObjectStorage();
    foreach ($this->matches as $item) {
      $kidsNL = $item->childNodes;
      foreach ($kidsNL as $kidNode) {
        if ($kidNode->nodeType == XML_ELEMENT_NODE) {
          $kids->attach($kidNode);
        }
      }
    }
    $this->matches = $kids;
  }
  
  public function adjacent() {
    $this->findAnyElement = FALSE;
    
    
    $found = new SplObjectStorage();
    foreach ($this->matches as $item) {
      while (isset($item->nextSibling)) {
        if (isset($item->nextSibling) && $item->nextSibling->nodeType === XML_ELEMENT_NODE) {
          $found->attach($item->nextSibling);
          break;
        }
        $item = $item->nextSibling;
      }
    }
    $this->matches = $found;
  }
  
  public function anotherSelector() {
    $this->findAnyElement = FALSE;
    
    if ($this->matches->count() > 0) {
      
      foreach ($this->matches as $item) $this->alreadyMatched->attach($item);
    }
    
    
    $this->findAnyElement = TRUE; 
    $this->matches = new SplObjectStorage();
    $this->matches->attach($this->dom);
  }
  
  
  public function sibling() {
    $this->findAnyElement = FALSE;
    
    
    if ($this->matches->count() > 0) {
      $sibs = new SplObjectStorage();
      foreach ($this->matches as $item) {
        
        while ($item->nextSibling != NULL) {
          $item = $item->nextSibling;
          if ($item->nodeType === XML_ELEMENT_NODE) $sibs->attach($item);
        }
      }
      $this->matches = $sibs;
    }
  }
  
  
  public function anyDescendant() {
    
    $found = new SplObjectStorage();
    foreach ($this->matches as $item) {
      $kids = $item->getElementsByTagName('*');
      
      $this->attachNodeList($kids, $found);
    }
    $this->matches = $found;
    
    
    $this->findAnyElement = TRUE;
  }
  
  
  private function candidateList() {
    if ($this->findAnyElement) {
      return $this->getAllCandidates($this->matches);
    }
    return $this->matches;
  }
  
  
  private function getAllCandidates($elements) {
    $found = new SplObjectStorage();
    foreach ($elements as $item) {
      $found->attach($item); 
      $nl = $item->getElementsByTagName('*');
      
      $this->attachNodeList($nl, $found);
    }
    return $found;
  }
  
  
  
  public function attachNodeList(DOMNodeList $nodeList, SplObjectStorage $splos) {
    foreach ($nodeList as $item) $splos->attach($item);
  }
  
}


class NotImplementedException extends Exception {}
