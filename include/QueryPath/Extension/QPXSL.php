<?php

 

class QPXSL implements QueryPathExtension {
  
  protected $src = NULL;
  
  public function __construct(QueryPath $qp) {
    $this->src = $qp;
  }
  
  
  public function xslt($style) {
    if (!($style instanceof QueryPath)) {
      $style = qp($style);
    }
    $sourceDoc = $this->src->top()->get(0)->ownerDocument;
    $styleDoc = $style->get(0)->ownerDocument;
    $processor = new XSLTProcessor();
    $processor->importStylesheet($styleDoc);
    return qp($processor->transformToDoc($sourceDoc));
  }
}
QueryPathExtensionRegistry::extend('QPXSL');