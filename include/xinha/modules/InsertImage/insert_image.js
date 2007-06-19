
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
    --  he file is loaded as a special plugin by the Xinha Core when no alternative method (plugin) is loaded.
    --
    --
    --  $HeadURL: http://svn.xinha.python-hosting.com/tags/0.92beta/modules/InsertImage/insert_image.js $
    --  $LastChangedDate: 2007-02-13 13:54:39 +0100 (Di, 13 Feb 2007) $
    --  $LastChangedRevision: 733 $
    --  $LastChangedBy: htanaka $
    --------------------------------------------------------------------------*/
InsertImage._pluginInfo = {
  name          : "InsertImage",
  origin        : "Xinha Core",
  version       : "$LastChangedRevision: 733 $".replace(/^[^:]*: (.*) \$$/, '$1'),
  developer     : "The Xinha Core Developer Team",
  developer_url : "$HeadURL: http://svn.xinha.python-hosting.com/tags/0.92beta/modules/InsertImage/insert_image.js $".replace(/^[^:]*: (.*) \$$/, '$1'),
  sponsor       : "",
  sponsor_url   : "",
  license       : "htmlArea"
};

function InsertImage(editor) {
}                                      

// Called when the user clicks on "InsertImage" button.  If an image is already
// there, it will just modify it's properties.
Xinha.prototype._insertImage = function(image)
{
  var editor = this;	// for nested functions
  var outparam;
  if ( typeof image == "undefined" )
  {
    image = this.getParentElement();
    if ( image && image.tagName.toLowerCase() != 'img' )
    {
      image = null;
    }
  }
  
  var base;
  if ( typeof editor.config.baseHref != 'undefined' && editor.config.baseHref !== null ) {
    base = editor.config.baseHref;
  }
  else {
    var bdir = window.location.toString().split("/");
    bdir.pop();
    base = bdir.join("/");
  }
  
  if ( image )
  {
    outparam =
    {
      f_base   : base,
      f_url    : Xinha.is_ie ? editor.stripBaseURL(image.src) : image.getAttribute("src"),
      f_alt    : image.alt,
      f_border : image.border,
      f_align  : image.align,
      f_vert   : (image.vspace!=-1 ? image.vspace : ""), //FireFox reports -1 when this attr has no value.
      f_horiz  : (image.hspace!=-1 ? image.hspace : ""), //FireFox reports -1 when this attr has no value.
      f_width  : image.width,
      f_height : image.height
    };
  }
  else{
  	outparam =
  	{
      f_base   : base,
      f_url    : ""      
  	};
  }
  
  Dialog(
    editor.config.URIs.insert_image,
    function(param)
    {
      // user must have pressed Cancel
      if ( !param )
      {
        return false;
      }
      var img = image;
      if ( !img )
      {
        if ( Xinha.is_ie )
        {
          var sel = editor.getSelection();
          var range = editor.createRange(sel);
          editor._doc.execCommand("insertimage", false, param.f_url);
          img = range.parentElement();
          // wonder if this works...
          if ( img.tagName.toLowerCase() != "img" )
          {
            img = img.previousSibling;
          }
        }
        else
        {
          img = document.createElement('img');
          img.src = param.f_url;
          editor.insertNodeAtSelection(img);
          if ( !img.tagName )
          {
            // if the cursor is at the beginning of the document
            img = range.startContainer.firstChild;
          }
        }
      }
      else
      {
        img.src = param.f_url;
      }

      for ( var field in param )
      {
        var value = param[field];
        switch (field)
        {
          case "f_alt":
            if (value)
              img.alt = value;
            else
              img.removeAttribute("alt");
            break;
          case "f_border":
            if (value)
              img.border = parseInt(value || "0");
            else
              img.removeAttribute("border");
            break;
          case "f_align":
            if (value)
              img.align = value;
            else
              img.removeAttribute("align");
            break;
          case "f_vert":
            if (value)
              img.vspace = parseInt(value || "0");
            else
              img.removeAttribute("vspace");
            break;
          case "f_horiz":
            if (value)
              img.hspace = parseInt(value || "0");
            else
              img.removeAttribute("hspace");
            break;
          case "f_width":
            if (value)
              img.width = parseInt(value || "0");
            else
              img.removeAttribute("width");
            break;
          case "f_height":
            if (value)
              img.height = parseInt(value || "0");
            else
              img.removeAttribute("height");
            break;
        }
      }
    },
    outparam);
};
