
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
    --  This is the standard implementation of the Xinha.prototype._insertTable method,
    --  which provides the functionality to insert an image in the editor.
    --
    --  he file is loaded as a special plugin by the Xinha Core when no alternative method (plugin) is loaded.
    --
    --
    --  $HeadURL: http://svn.xinha.python-hosting.com/trunk/modules/InsertTable/insert_table.js $
    --  $LastChangedDate: 2007-01-23 09:22:22 +1300 (Tue, 23 Jan 2007) $
    --  $LastChangedRevision: 688 $
    --  $LastChangedBy: ray $
    --------------------------------------------------------------------------*/
InsertTable._pluginInfo = {
  name          : "InsertTable",
  origin        : "Xinha Core",
  version       : "$LastChangedRevision: 688 $".replace(/^[^:]*: (.*) \$$/, '$1'),
  developer     : "The Xinha Core Developer Team",
  developer_url : "$HeadURL: http://svn.xinha.python-hosting.com/trunk/modules/InsertTable/insert_table.js $".replace(/^[^:]*: (.*) \$$/, '$1'),
  sponsor       : "",
  sponsor_url   : "",
  license       : "htmlArea"
};

function InsertTable(editor) {
}                                      

Xinha.prototype._insertTable = function()
{
  var sel = this.getSelection();
  var range = this.createRange(sel);
  var editor = this;	// for nested functions
  Dialog(
    editor.config.URIs.insert_table,
    function(param)
    {
      // user must have pressed Cancel
      if ( !param )
      {
        return false;
      }
      var doc = editor._doc;
      // create the table element
      var table = doc.createElement("table");
      // assign the given arguments

      for ( var field in param )
      {
        var value = param[field];
        if ( !value )
        {
          continue;
        }
        switch (field)
        {
          case "f_width":
            table.style.width = value + param.f_unit;
          break;
          case "f_align":
            table.align = value;
          break;
          case "f_border":
            table.border = parseInt(value, 10);
          break;
          case "f_spacing":
            table.cellSpacing = parseInt(value, 10);
          break;
          case "f_padding":
            table.cellPadding = parseInt(value, 10);
          break;
        }
      }
      var cellwidth = 0;
      if ( param.f_fixed )
      {
        cellwidth = Math.floor(100 / parseInt(param.f_cols, 10));
      }
      var tbody = doc.createElement("tbody");
      table.appendChild(tbody);
      for ( var i = 0; i < param.f_rows; ++i )
      {
        var tr = doc.createElement("tr");
        tbody.appendChild(tr);
        for ( var j = 0; j < param.f_cols; ++j )
        {
          var td = doc.createElement("td");
          // @todo : check if this line doesnt stop us to use pixel width in cells
          if (cellwidth)
          {
            td.style.width = cellwidth + "%";
          }
          tr.appendChild(td);
          // Browsers like to see something inside the cell (&nbsp;).
          td.appendChild(doc.createTextNode('\u00a0'));
        }
      }
      if ( Xinha.is_ie )
      {
        range.pasteHTML(table.outerHTML);
      }
      else
      {
        // insert the table
        editor.insertNodeAtSelection(table);
      }
      return true;
    },
    null
  );
};