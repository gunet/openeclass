/*------------------------------------------*\
 AsciiMathML Formula Editor for Xinha
 _______________________
 
 Based on AsciiMathML by Peter Jipsen http://www.chapman.edu/~jipsen
 
 Including a table with math symbols for easy input modified from CharacterMap for ASCIIMathML by Peter Jipsen
 HTMLSource based on HTMLArea XTD 1.5 (http://mosforge.net/projects/htmlarea3xtd/) modified by Holger Hees
 Original Author - Bernhard Pfeifer novocaine@gmx.net
 
 See readme.txt
 
 This program is free software; you can redistribute it and/or modify
 it under the terms of the GNU Lesser General Public License as published by
 the Free Software Foundation; either version 2.1 of the License, or (at
 your option) any later version.

 This program is distributed in the hope that it will be useful, 
 but WITHOUT ANY WARRANTY; without even the implied warranty of
 MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the GNU
 Lesser General Public License (at http://www.gnu.org/licenses/lgpl.html) 
 for more details.

 Raimund Meyer  11/23/2006
     
\*------------------------------------------*/
function Equation(editor) {
	this.editor = editor;

	var cfg = editor.config;
	var self = this;
	

	// register the toolbar buttons provided by this plugin
	cfg.registerButton({
	id       : "equation",
	tooltip  : this._lc("Formula Editor"),
	image    : editor.imgURL("equation.gif", "Equation"),
	textMode : false,
	action   : function(editor, id) {
			self.buttonPress(editor, id);
		}
	});
	cfg.addToolbarElement("equation", "inserthorizontalrule", -1);
	
	mathcolor = cfg.Equation.mathcolor;       // change it to "" (to inherit) or any other color
	mathfontfamily = cfg.Equation.mathfontfamily;
	
	//if (Xinha.is_ie) return;
	if (!Xinha.is_ie)
	{	
		editor.notifyOn( 'modechange',
			function( e, args )
				{
					self.onModeChange( args );
				}
			);
    	Xinha.prependDom0Event (editor._textArea.form,'submit',function () {self.unParse();self.reParse = true});
	}
	
	if (typeof  AMprocessNode != "function")
	{
		Xinha._loadback(_editor_url + "plugins/Equation/ASCIIMathML.js", function () { translate(); });
	}
}

Xinha.Config.prototype.Equation =
{
	"mathcolor" : "red",       // change it to "" (to inherit) or any other color
	"mathfontfamily" : "serif" // change to "" to inherit (works in IE) 
                               // or another family (e.g. "arial")
}

Equation._pluginInfo = {
	name          : "ASCIIMathML Formula Editor",
	version       : "2.0",
	developer     : "Raimund Meyer",
	developer_url : "http://rheinaufCMS.de",
	c_owner       : "",
	sponsor       : "Rheinauf",
	sponsor_url   : "http://rheinaufCMS.de",
	license       : "GNU/LGPL"
};

Equation.prototype._lc = function(string) 
{
    return Xinha._lc(string, 'Equation');
};
Equation.prototype.onGenerate = function() 
{
	this.parse();
};
Equation.prototype.onUpdateToolbar = function() 
{
	if (!Xinha.is_ie && this.reParse) AMprocessNode(this.editor._doc.body, false);
};

Equation.prototype.onModeChange = function( args )
{
	var doc = this.editor._doc;
	switch (args.mode)
	{
		case 'text':
			this.unParse();
		break;
		case 'wysiwyg':
			this.parse();
		break;
	}
};

Equation.prototype.parse = function ()
{
	if (!Xinha.is_ie)
	{
		var doc = this.editor._doc;
		var spans = doc.getElementsByTagName("span");
		for (var i = 0;i<spans.length;i++)
		{
			var node = spans[i];
			if (node.className != 'AM') continue;
			node.title = node.innerHTML;
			AMprocessNode(node, false);
		}
	}
}

Equation.prototype.unParse = function ()
{
	var doc = this.editor._doc;
	var spans = doc.getElementsByTagName("span");
	for (var i = 0;i<spans.length;i++)
	{
		var node = spans[i];
		if (node.className.indexOf ("AM") == -1) continue;
		var formula = node.getAttribute("title");
		node.innerHTML = formula;
		node.setAttribute("title", null);
		this.editor.setHTML(this.editor.getHTML());
	}
}

Equation.prototype.buttonPress = function() 
{
	var self = this;
	var editor = this.editor;
	var args = {};
	
	args['editor'] = editor;
	
	var parent = editor._getFirstAncestor(editor.getSelection(),['span']);
	if (parent)
	{
		args["editedNode"] = parent;
	}
	editor._popupDialog("plugin://Equation/dialog", function(params) {
				self.insert(params);
			}, args);
};

Equation.prototype.insert = function (param)
{
	if (typeof param["formula"] != "undefined")
	{
		var formula = (param["formula"] != '') ? param["formula"].replace(/^`?(.*)`?$/m,"`$1`") : '';

		if (param["editedNode"] && (param["editedNode"].tagName.toLowerCase() == 'span')) 
		{
			var span = param["editedNode"]; 
			if (formula != '')
			{
				span.innerHTML = formula;
				span.title = formula;
			}
			else
			{
				span.parentNode.removeChild(span);
			}
			
		}
		else if (!param["editedNode"] && formula != '')
		{
			if (!Xinha.is_ie)
			{			
				var span = document.createElement('span');
				span.className = 'AM';
				this.editor.insertNodeAtSelection(span);
				span.innerHTML = formula;
				span.title = formula;
			}
			else
			{
				this.editor.insertHTML('<span class="AM" title="'+formula+'">'+formula+'</span>');
			}
		}
		if (!Xinha.is_ie) AMprocessNode(this.editor._doc.body, false);
	}
}