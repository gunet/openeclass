<?php




require_once 'CssEventHandler.php';



interface CssEventHandler {
  
  const isExactly = 0; 
  
  const containsWithSpace = 1; 
  
  const containsWithHyphen = 2; 
  
  const containsInString = 3; 
  
  const beginsWith = 4; 
  
  const endsWith = 5; 
  
  const anyElement = '*';
  
  
  public function elementID($id); 
  
  public function element($name); 
  
  public function elementNS($name, $namespace = NULL);
  
  public function anyElement(); 
  
  public function anyElementInNS($ns); 
  
  public function elementClass($name); 
  
  public function attribute($name, $value = NULL, $operation = CssEventHandler::isExactly); 
  
  public function attributeNS($name, $ns, $value = NULL, $operation = CssEventHandler::isExactly);
  
  public function pseudoClass($name, $value = NULL); 
  
  public function pseudoElement($name); 
  
  public function directDescendant(); 
  
  public function adjacent(); 
  
  public function anotherSelector(); 
  
  public function sibling(); 
  
  public function anyDescendant(); 
  
}


final class CssToken {
  const char = 0;
  const star = 1;
  const rangle = 2;
  const dot = 3;
  const octo = 4;
  const rsquare = 5;
  const lsquare = 6;
  const colon = 7;
  const rparen = 8;
  const lparen = 9;
  const plus = 10;
  const tilde = 11;
  const eq = 12;
  const pipe = 13;
  const comma = 14;
  const white = 15;
  const quote = 16;
  const squote = 17;
  const bslash = 18;
  const carat = 19;
  const dollar = 20;
  const at = 21; 
  
  
  const stringLegal = 99;
  
    
  static function name($const_int) {
    $a = array('character', 'star', 'right angle bracket', 
      'dot', 'octothorp', 'right square bracket', 'left square bracket',
      'colon', 'right parenthesis', 'left parenthesis', 'plus', 'tilde',
      'equals', 'vertical bar', 'comma', 'space', 'quote', 'single quote',
      'backslash', 'carat', 'dollar', 'at');
    if (isset($a[$const_int]) && is_numeric($const_int)) {
      return $a[$const_int];
    }
    elseif ($const_int == 99) {
      return 'a legal non-alphanumeric character';
    }
    elseif ($const_int == FALSE) {
      return 'end of file';
    }
    return sprintf('illegal character (%s)', $const_int);
  }
}


class CssParser {
  protected $scanner = NULL;
  protected $buffer = '';
  protected $handler = NULL;
  protected $strict = FALSE;
  
  protected $DEBUG = FALSE;
  
  
  public function __construct($string, CssEventHandler $handler) {
    $this->originalString = $string;
    $is = new CssInputStream($string);
    $this->scanner = new CssScanner($is);
    $this->handler = $handler;
  }
  
  
  public function parse() {

    $this->scanner->nextToken();
    while ($this->scanner->token !== FALSE) {
      
      $position = $this->scanner->position();
      
      if ($this->DEBUG) {
        print "PARSE " . $this->scanner->token. "\n";
      }
      $this->selector();
      
      $finalPosition = $this->scanner->position();
      
      if ($this->scanner->token !== FALSE && $finalPosition == $position) {
        
        
        
        
        throw new CssParseException('CSS selector is not well formed.');
      }
      
    }
    
  }
  
  
  
  
  private function selector() {
    if ($this->DEBUG) print "SELECTOR{$this->scanner->position()}\n";
    $this->consumeWhitespace(); 
    $this->simpleSelectors();
    $this->combinator();
  }
  
  
  private function consumeWhitespace() {
    if ($this->DEBUG) print "CONSUME WHITESPACE\n";
    $white = 0;
    while ($this->scanner->token == CssToken::white) {
      $this->scanner->nextToken();
      ++$white;
    }
    return $white;
  }
  
  
  private function combinator() {
    if ($this->DEBUG) print "COMBINATOR\n";
    
    
    
    $inCombinator = FALSE; 
    $white = $this->consumeWhitespace();
    $t = $this->scanner->token;    
        
    if ($t == CssToken::rangle) {
      $this->handler->directDescendant();
      $this->scanner->nextToken();
      $inCombinator = TRUE;
      
    }
    elseif ($t == CssToken::plus) {
      $this->handler->adjacent();
      $this->scanner->nextToken();
      $inCombinator = TRUE;
      
    }
    elseif ($t == CssToken::comma) {
      $this->handler->anotherSelector();
      $this->scanner->nextToken();
      $inCombinator = TRUE;
      
    }
    elseif ($t == CssToken::tilde) {
      $this->handler->sibling();
      $this->scanner->nextToken();
      $inCombinator = TRUE;
    }

    
    if ($inCombinator) {
      $white = 0;
      if ($this->DEBUG) print "COMBINATOR: " . CssToken::name($t) . "\n";
      $this->consumeWhitespace();
      if ($this->isCombinator($this->scanner->token)) {
        throw new CssParseException("Illegal combinator: Cannot have two combinators in sequence.");
      }
    }
    
    elseif ($white > 0) {
      if ($this->DEBUG) print "COMBINATOR: any descendant\n";
      $inCombinator = TRUE;
      $this->handler->anyDescendant();
    }
    else {
      if ($this->DEBUG) print "COMBINATOR: no combinator found.\n";
    }
  }
  
  
  private function isCombinator($tok) {
    $combinators = array(CssToken::plus, CssToken::rangle, CssToken::comma, CssToken::tilde);
    return in_array($tok, $combinators);
  }
  
  
  private function simpleSelectors() {
    if ($this->DEBUG) print "SIMPLE SELECTOR\n";
    $this->allElements();
    $this->elementName();
    $this->elementClass();
    $this->elementID();
    $this->pseudoClass();
    $this->attribute();
  }
  
  
  private function elementID() {
    if ($this->DEBUG) print "ELEMENT ID\n";
    if ($this->scanner->token == CssToken::octo) {
      $this->scanner->nextToken();
      if ($this->scanner->token !== CssToken::char) {
        throw new CssParseException("Expected string after #");
      }
      $id = $this->scanner->getNameString();
      $this->handler->elementID($id);
    }
  }
  
  
  private function elementClass() {
    if ($this->DEBUG) print "ELEMENT CLASS\n";
    if ($this->scanner->token == CssToken::dot) {
      $this->scanner->nextToken();
      $this->consumeWhitespace(); 
      $cssClass = $this->scanner->getNameString();
      $this->handler->elementClass($cssClass);
    }
  }
  
  
  private function pseudoClass($restricted = FALSE) {
    if ($this->DEBUG) print "PSEUDO-CLASS\n";
    if ($this->scanner->token == CssToken::colon) {

      
      $isPseudoElement = FALSE;
      if ($this->scanner->nextToken() === CssToken::colon) {
        $isPseudoElement = TRUE;
        $this->scanner->nextToken();
      }
      
      $name = $this->scanner->getNameString();
      if ($restricted && $name == 'not') {
        throw new CssParseException("The 'not' pseudo-class is illegal in this context.");
      }
      
      $value = NULL;
      if ($this->scanner->token == CssToken::lparen) {
        if ($isPseudoElement) {
          throw new CssParseException("Illegal left paren. Pseudo-Element cannot have arguments.");
        }
        $value = $this->pseudoClassValue();
      }
      
      
      if ($isPseudoElement) {
        if ($restricted) {
          throw new CssParseException("Pseudo-Elements are illegal in this context.");
        }
        $this->handler->pseudoElement($name);
        $this->consumeWhitespace();
        
        
        
        
        if ($this->scanner->token !== FALSE && $this->scanner->token !== CssToken::comma) {
          throw new CssParseException("A Pseudo-Element must be the last item in a selector.");
        }
      } 
      else {
        $this->handler->pseudoClass($name, $value);
      }
    }
  }
  
  
  private function pseudoClassValue() {
    if ($this->scanner->token == CssToken::lparen) {
      $buf = '';

      
      
      $buf .= $this->scanner->getQuotedString();
      return $buf;
    }
  }
  
  
  private function elementName() {
    if ($this->DEBUG) print "ELEMENT NAME\n";
    if ($this->scanner->token === CssToken::pipe) {
      
      $this->scanner->nextToken();
      $this->consumeWhitespace();
      $elementName =  $this->scanner->getNameString();
      $this->handler->element($elementName);
    }
    elseif ($this->scanner->token === CssToken::char) {
      $elementName =  $this->scanner->getNameString();
      if ($this->scanner->token == CssToken::pipe) {
        
        $elementNS = $elementName;
        $this->scanner->nextToken();
        $this->consumeWhitespace();
        if ($this->scanner->token === CssToken::star) {
          
          $this->handler->anyElementInNS($elementNS);
          $this->scanner->nextToken();
        }
        elseif ($this->scanner->token !== CssToken::char) {
          $this->throwError(CssToken::char, $this->scanner->token);
        }
        else {
          $elementName = $this->scanner->getNameString();
          
          $this->handler->elementNS($elementName, $elementNS);
        }
        
      }
      else {
        $this->handler->element($elementName);
      }
    }
  }
  
  
  private function allElements() {
    if ($this->scanner->token === CssToken::star) {
      $this->scanner->nextToken();
      if ($this->scanner->token === CssToken::pipe) {
        $this->scanner->nextToken();
        if ($this->scanner->token === CssToken::star) {
          
          
          
          $this->scanner->nextToken();
          $this->handler->anyElementInNS('*');
        }
        else {
          
          
          $name = $this->scanner->getNameString();
          $this->handler->elementNS($name, '*');
        }
      }
      else {
        $this->handler->anyElement();
      }
    }
  }
  
  
  private function attribute() {
    if($this->scanner->token == CssToken::lsquare) {
      $attrVal = $op = $ns = NULL;
      
      $this->scanner->nextToken();
      $this->consumeWhitespace();
      
      if ($this->scanner->token === CssToken::at) {
        if ($this->strict) {
          throw new CssParseException('The @ is illegal in attributes.');
        }
        else {
          $this->scanner->nextToken();
          $this->consumeWhitespace();
        }
      }
      
      if ($this->scanner->token === CssToken::star) {
        
        
        $ns = '*';
        $this->scanner->nextToken();
      }
      if ($this->scanner->token === CssToken::pipe) {
        
        $this->scanner->nextToken();
        $this->consumeWhitespace();
      }
      
      $attrName = $this->scanner->getNameString();
      $this->consumeWhitespace();
      
      
      
      if ($this->scanner->token === CssToken::pipe && $this->scanner->peek() !== '=') {
        
        $ns = $attrName;
        $this->scanner->nextToken();
        $attrName = $this->scanner->getNameString();
        $this->consumeWhitespace();
      }
      
      
      

      
      switch ($this->scanner->token) {
        case CssToken::eq:
          $this->consumeWhitespace();
          $op = CssEventHandler::isExactly;
          break;
        case CssToken::tilde:
          if ($this->scanner->nextToken() !== CssToken::eq) {
            $this->throwError(CssToken::eq, $this->scanner->token);
          }
          $op = CssEventHandler::containsWithSpace;
          break;
        case CssToken::pipe:
          if ($this->scanner->nextToken() !== CssToken::eq) {
            $this->throwError(CssToken::eq, $this->scanner->token);
          }
          $op = CssEventHandler::containsWithHyphen;
          break;
        case CssToken::star:
          if ($this->scanner->nextToken() !== CssToken::eq) {
            $this->throwError(CssToken::eq, $this->scanner->token);
          }
          $op = CssEventHandler::containsInString;
          break;
        case CssToken::dollar;
          if ($this->scanner->nextToken() !== CssToken::eq) {
            $this->throwError(CssToken::eq, $this->scanner->token);
          }
          $op = CssEventHandler::endsWith;
          break;
        case CssToken::carat:
          if ($this->scanner->nextToken() !== CssToken::eq) {
            $this->throwError(CssToken::eq, $this->scanner->token);
          }
          $op = CssEventHandler::beginsWith;
          break;
      }
      
      if (isset($op)) {
        
        $this->scanner->nextToken();
        $this->consumeWhitespace();
        
        
        
        
        
        
        
        
        
        
        if ($this->scanner->token === CssToken::quote || $this->scanner->token === CssToken::squote) {
          $attrVal = $this->scanner->getQuotedString();
        }
        else {
          $attrVal = $this->scanner->getNameString();
        }
        
        if ($this->DEBUG) {
          print "ATTR: $attrVal AND OP: $op\n";
        }
      }
      
      $this->consumeWhitespace();
      
      if ($this->scanner->token != CssToken::rsquare) {
        $this->throwError(CssToken::rsquare, $this->scanner->token);
      }
      
      if (isset($ns)) {
        $this->handler->attributeNS($attrName, $ns, $attrVal, $op);
      }
      elseif (isset($attrVal)) {
        $this->handler->attribute($attrName, $attrVal, $op);
      }
      else {
        $this->handler->attribute($attrName);
      }
      $this->scanner->nextToken();
    }
  }
  
  
  private function throwError($expected, $got) {
    $filter = sprintf('Expected %s, got %s', CssToken::name($expected), CssToken::name($got));
    throw new CssParseException($filter);
  }
  
}


final class CssScanner {
  var $is = NULL;
  public $value = NULL;
  public $token = NULL;
  
  var $recurse = FALSE;
  var $it = 0;
  
  
  public function __construct(CssInputStream $in) {
    $this->is = $in;
  }
  
  
  public function position() {
    return $this->is->position;
  }
  
  
  public function peek() {
    return $this->is->peek();
  }
  
  
  public function nextToken() {
    $tok = -1;
    ++$this->it;
    if ($this->is->isEmpty()) {
      if ($this->recurse) {
        throw new Exception("Recursion error detected at iteration " . $this->it . '.');
        exit();
      }
      
      $this->recurse = TRUE;
      $this->token = FALSE;
      return FALSE;
    }
    $ch = $this->is->consume();
    
    if (ctype_space($ch)) {
      $this->value = ' '; 
      $this->token = $tok = CssToken::white;
      
      return $tok;
    }
    
    if (ctype_alnum($ch) || $ch == '-' || $ch == '_') {
      
      $this->value = $ch; 
      $this->token = $tok = CssToken::char;
      return $tok;
    }
    
    $this->value = $ch;
    
    switch($ch) {
      case '*':
        $tok = CssToken::star;
        break;
      case chr(ord('>')):
        $tok = CssToken::rangle;
        break;
      case '.':
        $tok = CssToken::dot;
        break;
      case '#':
        $tok = CssToken::octo;
        break;
      case '[':
        $tok = CssToken::lsquare;
        break;
      case ']':
        $tok = CssToken::rsquare;
        break;
      case ':':
        $tok = CssToken::colon;
        break;
      case '(':
        $tok = CssToken::lparen;
        break;
      case ')':
        $tok = CssToken::rparen;
        break;
      case '+':
        $tok = CssToken::plus;
        break;
      case '~':
        $tok = CssToken::tilde;
        break;
      case '=':
        $tok = CssToken::eq;
        break;
      case '|':
        $tok = CssToken::pipe;
        break;
      case ',':
        $tok = CssToken::comma;
        break;
      case chr(34):
        $tok = CssToken::quote;
        break;
      case "'":
        $tok = CssToken::squote;
        break;
      case '\\':
        $tok = CssToken::bslash;
        break;
      case '^':
        $tok = CssToken::carat;
        break;
      case '$':
        $tok = CssToken::dollar;
        break;
      case '@':
        $tok = CssToken::at;
        break;
    }
    
    
    
    if ($tok == -1) {
      
      
      

      $ord = ord($ch);
      
      
      
      if (($ord >= 32 && $ord <= 126) || ($ord >= 128 && $ord <= 255)) {
        $tok = CssToken::stringLegal;
      }
      else {
        throw new CSSParseException('Illegal character found in stream: ' . $ord);
      }
    }
    
    $this->token = $tok;
    return $tok;
  }
  
  
  public function getNameString() {
    $buf = '';
    while ($this->token === CssToken::char) {
      $buf .= $this->value;
      $this->nextToken();
      
    }
    return $buf;
  }
  
  
  public function getQuotedString() {
    if ($this->token == CssToken::quote || $this->token == CssToken::squote || $this->token == CssToken::lparen) {
      $end = ($this->token == CssToken::lparen) ? CssToken::rparen : $this->token;
      $buf = '';
      $escape = FALSE;
      
      $this->nextToken(); 
      
      
      while ($this->token !== FALSE && $this->token > -1) {
        
        if ($this->token == CssToken::bslash && !$escape) {
          
          
          
          $escape = TRUE;
        }
        elseif ($escape) {
          
          $buf .= $this->value;
          $escape = FALSE;
        }
        elseif ($this->token === $end) {
          
          $this->nextToken();
          break;
        }
        else {
          
          $buf .= $this->value;
        }
        $this->nextToken();
      }
      return $buf;
    }
  }
  
  
  
}


class CssInputStream {
  protected $stream = NULL;
  public $position = 0;
  
  function __construct($string) {
    $this->stream = str_split($string);
  }
  
  function peek() {
    return $this->stream[0];
  }
  
  function consume() {
    $ret = array_shift($this->stream);
    if (!empty($ret)) {
      $this->position++;
    }
    return $ret;
  }
  
  function isEmpty() {
    return count($this->stream) == 0;
  }
}


class CSSParseException extends EXCEPTION {}