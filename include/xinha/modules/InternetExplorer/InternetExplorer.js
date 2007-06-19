
  /*--------------------------------------:noTabs=true:tabSize=2:indentSize=2:--
    --  Xinha (is not htmlArea) - http://xinha.gogo.co.nz/
    --
    --  Use of Xinha is granted by the terms of the htmlArea License (based on
    --  BSD license)  please read license.txt in this package for details.
    --
    --  Xinha was originally based on work by Mihai Bazon which is:
    --      Copyright (c) 2003-2004 dynarch.com.
    --      Copyright (c) 2002-2003 interactivetools.com, inc.
    --      This copyright notice MUST stay intact for use.
    --
    -- This is the Internet Explorer compatability plugin, part of the 
    -- Xinha core.
    --
    --  The file is loaded as a special plugin by the Xinha Core when
    --  Xinha is being run under an Internet Explorer based browser.
    --
    --  It provides implementation and specialisation for various methods
    --  in the core where different approaches per browser are required.
    --
    --  Design Notes::
    --   Most methods here will simply be overriding Xinha.prototype.<method>
    --   and should be called that, but methods specific to IE should 
    --   be a part of the InternetExplorer.prototype, we won't trample on 
    --   namespace that way.
    --
    --  $HeadURL: http://svn.xinha.python-hosting.com/tags/0.92beta/modules/InternetExplorer/InternetExplorer.js $
    --  $LastChangedDate: 2007-02-15 00:47:47 +0100 (Do, 15 Feb 2007) $
    --  $LastChangedRevision: 737 $
    --  $LastChangedBy: ray $
    --------------------------------------------------------------------------*/
                                                    
InternetExplorer._pluginInfo = {
  name          : "Internet Explorer",
  origin        : "Xinha Core",
  version       : "$LastChangedRevision: 737 $".replace(/^[^:]*: (.*) \$$/, '$1'),
  developer     : "The Xinha Core Developer Team",
  developer_url : "$HeadURL: http://svn.xinha.python-hosting.com/tags/0.92beta/modules/InternetExplorer/InternetExplorer.js $".replace(/^[^:]*: (.*) \$$/, '$1'),
  sponsor       : "",
  sponsor_url   : "",
  license       : "htmlArea"
};

function InternetExplorer(editor) {
  this.editor = editor;  
  editor.InternetExplorer = this; // So we can do my_editor.InternetExplorer.doSomethingIESpecific();
}

/** Allow Internet Explorer to handle some key events in a special way.
 */
  
InternetExplorer.prototype.onKeyPress = function(ev)
{
  // Shortcuts
  if(this.editor.isShortCut(ev))
  {
    switch(this.editor.getKey(ev).toLowerCase())
    {
      case 'n':
      {
        this.editor.execCommand('formatblock', false, '<p>');        
        Xinha._stopEvent(ev);
        return true;
      }
      break;
      
      case '1':
      case '2':
      case '3':
      case '4':
      case '5':
      case '6':
      {
        this.editor.execCommand('formatblock', false, '<h'+this.editor.getKey(ev).toLowerCase()+'>');
        Xinha._stopEvent(ev);
        return true;
      }
      break;
    }
  }
  
  switch(ev.keyCode) 
  {
    case 8: // KEY backspace
    case 46: // KEY delete
    {
      if(this.handleBackspace())
      {
        Xinha._stopEvent(ev);
        return true;
      }
    }
    break;
  }
  
  return false;
}

/** When backspace is hit, the IE onKeyPress will execute this method.
 *  It preserves links when you backspace over them and apparently 
 *  deletes control elements (tables, images, form fields) in a better
 *  way.
 *
 *  @returns true|false True when backspace has been handled specially
 *   false otherwise (should pass through). 
 */

InternetExplorer.prototype.handleBackspace = function()
{
  var editor = this.editor;
  var sel = editor.getSelection();
  if ( sel.type == 'Control' )
  {
    var elm = editor.activeElement(sel);
    Xinha.removeFromParent(elm);
    return true;
  }

  // This bit of code preseves links when you backspace over the
  // endpoint of the link in IE.  Without it, if you have something like
  //    link_here |
  // where | is the cursor, and backspace over the last e, then the link
  // will de-link, which is a bit tedious
  var range = editor.createRange(sel);
  var r2 = range.duplicate();
  r2.moveStart("character", -1);
  var a = r2.parentElement();
  // @fixme: why using again a regex to test a single string ???
  if ( a != range.parentElement() && ( /^a$/i.test(a.tagName) ) )
  {
    r2.collapse(true);
    r2.moveEnd("character", 1);
    r2.pasteHTML('');
    r2.select();
    return true;
  }
};

InternetExplorer.prototype.inwardHtml = function(html)
{
   // Both IE and Gecko use strike internally instead of del (#523)
   // Xinha will present del externally (see Xinha.prototype.outwardHtml
   html = html.replace(/<(\/?)del(\s|>|\/)/ig, "<$1strike$2");
   
   return html;
}

/*--------------------------------------------------------------------------*/
/*------- IMPLEMENTATION OF THE ABSTRACT "Xinha.prototype" METHODS ---------*/
/*--------------------------------------------------------------------------*/

/** Insert a node at the current selection point. 
 * @param toBeInserted DomNode
 */

Xinha.prototype.insertNodeAtSelection = function(toBeInserted)
{
  this.insertHTML(toBeInserted.outerHTML);
};

  
/** Get the parent element of the supplied or current selection. 
 *  @param   sel optional selection as returned by getSelection
 *  @returns DomNode
 */
 
Xinha.prototype.getParentElement = function(sel)
{
  if ( typeof sel == 'undefined' )
  {
    sel = this.getSelection();
  }
  var range = this.createRange(sel);
  switch ( sel.type )
  {
    case "Text":
      // try to circumvent a bug in IE:
      // the parent returned is not always the real parent element
      var parent = range.parentElement();
      while ( true )
      {
        var TestRange = range.duplicate();
        TestRange.moveToElementText(parent);
        if ( TestRange.inRange(range) )
        {
          break;
        }
        if ( ( parent.nodeType != 1 ) || ( parent.tagName.toLowerCase() == 'body' ) )
        {
          break;
        }
        parent = parent.parentElement;
      }
      return parent;
    case "None":
      // It seems that even for selection of type "None",
      // there _is_ a parent element and it's value is not
      // only correct, but very important to us.  MSIE is
      // certainly the buggiest browser in the world and I
      // wonder, God, how can Earth stand it?
      return range.parentElement();
    case "Control":
      return range.item(0);
    default:
      return this._doc.body;
  }
};
  
/**
 * Returns the selected element, if any.  That is,
 * the element that you have last selected in the "path"
 * at the bottom of the editor, or a "control" (eg image)
 *
 * @returns null | DomNode
 */
 
Xinha.prototype.activeElement = function(sel)
{
  if ( ( sel === null ) || this.selectionEmpty(sel) )
  {
    return null;
  }

  if ( sel.type.toLowerCase() == "control" )
  {
    return sel.createRange().item(0);
  }
  else
  {
    // If it's not a control, then we need to see if
    // the selection is the _entire_ text of a parent node
    // (this happens when a node is clicked in the tree)
    var range = sel.createRange();
    var p_elm = this.getParentElement(sel);
    if ( p_elm.innerHTML == range.htmlText )
    {
      return p_elm;
    }
    /*
    if ( p_elm )
    {
      var p_rng = this._doc.body.createTextRange();
      p_rng.moveToElementText(p_elm);
      if ( p_rng.isEqual(range) )
      {
        return p_elm;
      }
    }

    if ( range.parentElement() )
    {
      var prnt_range = this._doc.body.createTextRange();
      prnt_range.moveToElementText(range.parentElement());
      if ( prnt_range.isEqual(range) )
      {
        return range.parentElement();
      }
    }
    */
    return null;
  }
};

/** 
 * Determines if the given selection is empty (collapsed).
 * @param selection Selection object as returned by getSelection
 * @returns true|false
 */
 
Xinha.prototype.selectionEmpty = function(sel)
{
  if ( !sel )
  {
    return true;
  }

  return this.createRange(sel).htmlText === '';
};

/**
 * Selects the contents of the given node.  If the node is a "control" type element, (image, form input, table)
 * the node itself is selected for manipulation.
 *
 * @param node DomNode 
 * @param pos  Set to a numeric position inside the node to collapse the cursor here if possible. 
 */
 
Xinha.prototype.selectNodeContents = function(node, pos)
{
  this.focusEditor();
  this.forceRedraw();
  var range;
  var collapsed = typeof pos == "undefined" ? true : false;
  // Tables and Images get selected as "objects" rather than the text contents
  if ( collapsed && node.tagName && node.tagName.toLowerCase().match(/table|img|input|select|textarea/) )
  {
    range = this._doc.body.createControlRange();
    range.add(node);
  }
  else
  {
    range = this._doc.body.createTextRange();
    range.moveToElementText(node);
    //(collapsed) && range.collapse(pos);
  }
  range.select();
};
  
/** Insert HTML at the current position, deleting the selection if any. 
 *  
 *  @param html string
 */
 
Xinha.prototype.insertHTML = function(html)
{
  this.focusEditor();
  var sel = this.getSelection();
  var range = this.createRange(sel);
  range.pasteHTML(html);
};


/** Get the HTML of the current selection.  HTML returned has not been passed through outwardHTML.
 *
 * @returns string
 */
 
Xinha.prototype.getSelectedHTML = function()
{
  var sel = this.getSelection();
  var range = this.createRange(sel);
  
  // Need to be careful of control ranges which won't have htmlText
  if( range.htmlText )
  {
    return range.htmlText;
  }
  else if(range.length >= 1)
  {
    return range.item(0).outerHTML;
  }
  
  return '';
};
  
/** Get a Selection object of the current selection.  Note that selection objects are browser specific.
 *
 * @returns Selection
 */
 
Xinha.prototype.getSelection = function()
{
  return this._doc.selection;
};

/** Create a Range object from the given selection.  Note that range objects are browser specific.
 *
 *  @param sel Selection object (see getSelection)
 *  @returns Range
 */
 
Xinha.prototype.createRange = function(sel)
{
  return sel.createRange();
};

/** Determine if the given event object is a keydown/press event.
 *
 *  @param event Event 
 *  @returns true|false
 */
 
Xinha.prototype.isKeyEvent = function(event)
{
  return event.type == "keydown";
}

/** Return the character (as a string) of a keyEvent  - ie, press the 'a' key and
 *  this method will return 'a', press SHIFT-a and it will return 'A'.
 * 
 *  @param   keyEvent
 *  @returns string
 */
                                   
Xinha.prototype.getKey = function(keyEvent)
{
  return String.fromCharCode(keyEvent.keyCode);
}


/** Return the HTML string of the given Element, including the Element.
 * 
 * @param element HTML Element DomNode
 * @returns string
 */
 
Xinha.getOuterHTML = function(element)
{
  return element.outerHTML;
};

// Control character for retaining edit location when switching modes
Xinha.prototype.cc = String.fromCharCode(0x2009);

Xinha.prototype.setCC = function ( target )
{
  if ( target == "textarea" )
  {
    var ta = this._textArea;
    var pos = document.selection.createRange();
    pos.collapse();
    pos.text = this.cc;
    var index = ta.value.indexOf( this.cc );
    var before = ta.value.substring( 0, index );
    var after  = ta.value.substring( index + this.cc.length , ta.value.length );
    
    if ( after.match(/^[^<]*>/) )
    {
      var tagEnd = after.indexOf(">") + 1;
      ta.value = before + after.substring( 0, tagEnd ) + this.cc + after.substring( tagEnd, after.length );
    }
    else ta.value = before + this.cc + after;
  }
  else
  {
    var sel = this.getSelection();
    var r = sel.createRange(); 
    if ( sel.type == 'Control' )
    {
      var control = r.item(0);
      control.outerHTML += this.cc;
    }
    else
    {
      r.collapse();
      r.text = this.cc;
    }
  }
};

Xinha.prototype.findCC = function ( target )
{
  var findIn = ( target == 'textarea' ) ? this._textArea : this._doc.body;
  range = findIn.createTextRange();
  // in case the cursor is inside a link automatically created from a url
  // the cc also appears in the url and we have to strip it out additionally 
  if( range.findText( escape(this.cc) ) )
  {
    range.select();
    range.text = '';
  }
  if( range.findText( this.cc ) )
  {
    range.select();
    range.text = '';
  }
  if ( target == 'textarea' ) this._textArea.focus();
};