<?php


class QPXML implements QueryPathExtension {

  protected $qp;

  public function __construct(QueryPath $qp) {
    $this->qp = $qp;
  }

  public function schema($file) {
    $doc = $this->qp->branch()->top()->get(0)->ownerDocument;

    if (!$doc->schemaValidate($file)) {
      throw new QueryPathException('Document did not validate against the schema.');
    }
  }

  
  public function cdata($text = NULL) {
    if (isset($text)) {
      
      foreach ($this->qp->get() as $element) {
        $cdata = $element->ownerDocument->createCDATASection($text);
        $element->appendChild($cdata);
      }
      return $this->qp;;
    }

    
    foreach ($this->qp->get() as $ele) {
      foreach ($ele->childNodes as $node) {
        if ($node->nodeType == XML_CDATA_SECTION_NODE) {
          
          return $node->textContent;
        }
      }
    }
    return NULL;
    
  }

  
  public function comment($text = NULL) {
    if (isset($text)) {
      foreach ($this->qp->get() as $element) {
        $comment = $element->ownerDocument->createComment($text);
        $element->appendChild($comment);
      }
      return $this->qp;
    }
    foreach ($this->qp->get() as $ele) {
      foreach ($ele->childNodes as $node) {
        if ($node->nodeType == XML_COMMENT_NODE) {
          
          return $node->textContent;
        }
      }
    }
  }

  
  public function pi($prefix = NULL, $text = NULL) {
    if (isset($text)) {
      foreach ($this->qp->get() as $element) {
        $comment = $element->ownerDocument->createProcessingInstruction($prefix, $text);
        $element->appendChild($comment);
      }
      return $this->qp;
    }
    foreach ($this->qp->get() as $ele) {
      foreach ($ele->childNodes as $node) {
        if ($node->nodeType == XML_PI_NODE) {

          if (isset($prefix)) {
            if ($node->tagName == $prefix) {
              return $node->textContent;
            }
          }
          else {
            
            return $node->textContent;
          }
        }
      } 
    } 
  }
  public function toXml() {
      return $this->qp->document()->saveXml();
  }

  
  public function createNilElement($text, $value) {
    $value = ($value)? 'true':'false';
    $element = $this->qp->createElement($text);
    $element->attr('xsi:nil', $value);
    return $element;
  }

  
  public function createElement($text, $nsUri = null) {
    if (isset ($text)) {
      foreach ($this->qp->get() as $element) {
        if ($nsUri === null && strpos($text, ':') !== false) {
          $ns = array_shift(explode(':', $text));
          $nsUri = $element->ownerDocument->lookupNamespaceURI($ns);

          if ($nsUri === null) {
            throw new QueryPathException("Undefined namespace for: " . $text);
          }
      }

      $node = null;
      if ($nsUri !== null) {
        $node = $element->ownerDocument->createElementNS(
          $nsUri,
          $text
        );
      } else {
        $node = $element->ownerDocument->createElement($text);
      }
        return qp($node);
      }
    }
    return;
  }

  
  public function appendElement($text) {
    if (isset ($text)) {
      foreach ($this->qp->get() as $element) {
        $node = $this->qp->createElement($text);
        qp($element)->append($node);
      }
    }
    return $this->qp;
  }
}
QueryPathExtensionRegistry::extend('QPXML');
