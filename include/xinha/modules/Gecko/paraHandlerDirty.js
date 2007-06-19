
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
    --  This is the standard implementation of the Xinha.prototype._insertImage method,
    --  which provides the functionality to insert an image in the editor.
    --
    --  The file is loaded by the Xinha Core when no alternative method (plugin) is loaded.
    --
    --
    --  $HeadURL: http://svn.xinha.python-hosting.com/trunk/modules/Gecko/paraHandlerDirty.js $
    --  $LastChangedDate: 2007-01-23 09:22:22 +1300 (Tue, 23 Jan 2007) $
    --  $LastChangedRevision: 688 $
    --  $LastChangedBy: ray $
    --------------------------------------------------------------------------*/
EnterParagraphs._pluginInfo = {
  name          : "EnterParagraphs",
  origin        : "Xinha Core",
  version       : "$LastChangedRevision: 688 $".replace(/^[^:]*: (.*) \$$/, '$1'),
  developer     : "The Xinha Core Developer Team",
  developer_url : "$HeadURL: http://svn.xinha.python-hosting.com/trunk/modules/Gecko/paraHandlerDirty.js $".replace(/^[^:]*: (.*) \$$/, '$1'),
  sponsor       : "",
  sponsor_url   : "",
  license       : "htmlArea"
};

function EnterParagraphs(editor) {
	this.editor = editor;
}                                      

EnterParagraphs.prototype.onKeyPress = function (ev)
{
  if( ev.keyCode == 13 && !ev.shiftKey )
  {
    this.dom_checkInsertP();
    Xinha._stopEvent(ev);
  } 
}
/** The idea here is
 * 1. See if we are in a block element
 * 2. If we are not, then wrap the current "block" of text into a paragraph
 * 3. Now that we have a block element, select all the text between the insertion point
 *    and just AFTER the end of the block
 *    eg <p>The quick |brown fox jumped over the lazy dog.</p>|
 *                     ---------------------------------------
 * 4. Extract that from the document, making
 *       <p>The quick </p>
 *    and a document fragment with
 *       <p>brown fox jumped over the lazy dog.</p>
 * 5. Reinsert it just after the block element
 *       <p>The quick </p><p>brown fox jumped over the lazy dog.</p>
 *
 * Along the way, allow inserting blank paragraphs, which will look like <p><br/></p>
 */

EnterParagraphs.prototype.dom_checkInsertP = function()
{
  var editor = this.editor;
  var p, body;
  // Get the insertion point, we'll scrub any highlighted text the user wants rid of while we are there.
  var sel = editor.getSelection();
  var range = editor.createRange(sel);
  if ( !range.collapsed )
  {
    range.deleteContents();
  }
  editor.deactivateEditor();
  //sel.removeAllRanges();
  //sel.addRange(range);

  var SC = range.startContainer;
  var SO = range.startOffset;
  var EC = range.endContainer;
  var EO = range.endOffset;

  // If the insertion point is character 0 of the
  // document, then insert a space character that we will wrap into a paragraph
  // in a bit.
  if ( SC == EC && SC == body && !SO && !EO )
  {
    p = editor._doc.createTextNode(" ");
    body.insertBefore(p, body.firstChild);
    range.selectNodeContents(p);
    SC = range.startContainer;
    SO = range.startOffset;
    EC = range.endContainer;
    EO = range.endOffset;
  }

  // See if we are in a block element, if so, great.
  p = editor.getAllAncestors();

  var block = null;
  body = editor._doc.body;
  for ( var i = 0; i < p.length; ++i )
  {
    if ( Xinha.isParaContainer(p[i]) )
    {
      break;
    }
    else if ( Xinha.isBlockElement(p[i]) && ! ( /body|html/i.test(p[i].tagName) ) )
    {
      block = p[i];
      break;
    }
  }

  // If not in a block element, we'll have to turn some stuff into a paragraph
  if ( !block )
  {
    // We want to wrap as much stuff as possible into the paragraph in both directions
    // from the insertion point.  We start with the start container and walk back up to the
    // node just before any of the paragraph containers.
    var wrap = range.startContainer;
    while ( wrap.parentNode && !Xinha.isParaContainer(wrap.parentNode) )
    {
      wrap = wrap.parentNode;
    }
    var start = wrap;
    var end   = wrap;

    // Now we walk up the sibling list until we hit the top of the document
    // or an element that we shouldn't put in a p (eg other p, div, ul, ol, table)
    while ( start.previousSibling )
    {
      if ( start.previousSibling.tagName )
      {
        if ( !Xinha.isBlockElement(start.previousSibling) )
        {
          start = start.previousSibling;
        }
        else
        {
          break;
        }
      }
      else
      {
        start = start.previousSibling;
      }
    }

    // Same down the list
    while ( end.nextSibling )
    {
      if ( end.nextSibling.tagName )
      {
        if ( !Xinha.isBlockElement(end.nextSibling) )
        {
          end = end.nextSibling;
        }
        else
        {
          break;
        }
      }
      else
      {
        end = end.nextSibling;
      }
    }

    // Select the entire block
    range.setStartBefore(start);
    range.setEndAfter(end);

    // Make it a paragraph
    range.surroundContents(editor._doc.createElement('p'));

    // Which becomes the block element
    block = range.startContainer.firstChild;

    // And finally reset the insertion point to where it was originally
    range.setStart(SC, SO);
  }

  // The start point is the insertion point, so just move the end point to immediatly
  // after the block
  range.setEndAfter(block);

  // Extract the range, to split the block
  // If we just did range.extractContents() then Mozilla does wierd stuff
  // with selections, but if we clone, then remove the original range and extract
  // the clone, it's quite happy.
  var r2 = range.cloneRange();
  sel.removeRange(range);
  var df = r2.extractContents();

  if ( df.childNodes.length === 0 )
  {
    df.appendChild(editor._doc.createElement('p'));
    df.firstChild.appendChild(editor._doc.createElement('br'));
  }

  if ( df.childNodes.length > 1 )
  {
    var nb = editor._doc.createElement('p');
    while ( df.firstChild )
    {
      var s = df.firstChild;
      df.removeChild(s);
      nb.appendChild(s);
    }
    df.appendChild(nb);
  }

  // If the original block is empty, put a &nsbp; in it.
  // @fixme: why using a regex instead of : if (block.innerHTML.trim() == '') ?
  if ( ! ( /\S/.test(block.innerHTML) ) )
  {
    block.innerHTML = "&nbsp;";
  }

  p = df.firstChild;
  // @fixme: why using a regex instead of : if (p.innerHTML.trim() == '') ?
  if ( ! ( /\S/.test(p.innerHTML) ) )
  {
    p.innerHTML = "<br />";
  }

  // If the new block is empty and it's a heading, make it a paragraph
  // note, the new block is empty when you are hitting enter at the end of the existing block
  if ( ( /^\s*<br\s*\/?>\s*$/.test(p.innerHTML) ) && ( /^h[1-6]$/i.test(p.tagName) ) )
  {
    df.appendChild(editor.convertNode(p, "p"));
    df.removeChild(p);
  }

  var newblock = block.parentNode.insertBefore(df.firstChild, block.nextSibling);

  // Select the range (to set the insertion)
  // collapse to the start of the new block
  //  (remember the block might be <p><br/></p>, so if we collapsed to the end the <br/> would be noticable)

  //range.selectNode(newblock.firstChild);
  //range.collapse(true);

  editor.activateEditor();

  sel = editor.getSelection();
  sel.removeAllRanges();
  sel.collapse(newblock,0);

  // scroll into view
  editor.scrollToElement(newblock);

  //editor.forceRedraw();

};
