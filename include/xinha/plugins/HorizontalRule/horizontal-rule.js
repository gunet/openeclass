
HorizontalRule._pluginInfo = {
	name          : "HorizontalRule",
	version       : "1.0",
	developer     : "Nelson Bright",
	developer_url : "http://www.brightworkweb.com/",
	c_owner       : "Nelson Bright",
	sponsor       : "BrightWork, Inc.",
	sponsor_url   : "http://www.brightworkweb.com/",
	license       : "htmlArea"
};

function HorizontalRule(editor) {
    this.editor = editor;

    var cfg = editor.config;
	var toolbar = cfg.toolbar;
	var self = this;
        
	cfg.registerButton({
		id       : "edithorizontalrule",
		tooltip  : this._lc("Insert/edit horizontal rule"),
	//	image    : editor.imgURL("ed_hr.gif", "HorizontalRule"),
		image    : [_editor_url + "images/ed_buttons_main.gif",6,0],
		textMode : false,
		action   : function(editor) {
						self.buttonPress(editor);
				   }
	});

	cfg.addToolbarElement("edithorizontalrule","inserthorizontalrule",0);
}

HorizontalRule.prototype._lc = function(string) {
    return  Xinha._lc(string, 'HorizontalRule');
};

HorizontalRule.prototype.buttonPress = function(editor) {
	this.editor = editor;
	this._editHorizontalRule();
};

HorizontalRule.prototype._editHorizontalRule = function(rule) {
	editor = this.editor;
	var sel = editor._getSelection();
	var range = editor._createRange(sel);
	var outparam = null;
	if (typeof rule == "undefined") {
		rule = editor.getParentElement();
		if (rule && !/^hr$/i.test(rule.tagName))
			rule = null;
	}
	if (rule) {
		var f_widthValue    = rule.style.width || rule.width;
		outparam = {
			f_size      : parseInt(rule.style.height,10) || rule.size,
			f_widthUnit : (/(%|px)$/.test(f_widthValue)) ? RegExp.$1 : 'px',
			f_width     : parseInt (f_widthValue,10),
			f_color     : Xinha._colorToRgb(rule.style.backgroundColor) || rule.color,
			f_align     : rule.style.textAlign || rule.align,
			f_noshade   : (parseInt(rule.style.borderWidth,10) == 0) || rule.noShade
		};
	}
	editor._popupDialog("plugin://HorizontalRule/edit_horizontal_rule.html", function(param) {
		if (!param) {	// user pressed Cancel
			return false;
		}
		var hr = rule;
		if (!hr) {
			var hrule = editor._doc.createElement("hr");
			for (var field in param) {
				var value = param[field];
				if(value == "") continue;
				switch (field) { 
				case "f_width" :
					if(param["f_widthUnit"]=="%")
					{
						hrule.style.width = value + "%";
					}
					else
					{
						hrule.style.width = value + "px";
					}
				break;
				case "f_size" :
					hrule.style.height = value + "px"; 
				break;
				case "f_align" : // Gecko needs the margins for alignment
					hrule.style.textAlign = value;
					switch (value) {
						case 'left':
							hrule.style.marginLeft = "0";
						break;
						case 'right':
							hrule.style.marginRight = "0";
						break;
						case 'center':
							hrule.style.marginLeft = "auto";
							hrule.style.marginRight = "auto";
						break;
					}
				break;
				case "f_color" :
					hrule.style.backgroundColor = value; 
				break;
				case "f_noshade" :
					hrule.style.border = "0"; 
				break;
				}
			}
			if ( Xinha.is_gecko )
			{   // If I use editor.insertNodeAtSelection(hrule) here I get get a </hr> closing tag
				editor.execCommand("inserthtml",false,Xinha.getOuterHTML(hrule));
			}
			else editor.insertNodeAtSelection(hrule);
			
		} else {
			for (var field in param) {
			  var value = param[field];
			  switch (field) {
				case "f_width" :
					if(param["f_widthUnit"]=="%")
					{
						hr.style.width = value + "%";
					}
					else
					{
						hr.style.width = value + "px";
					}
				break;
				case "f_size" :
					hr.style.height = value + "px"; 
				break;
				case "f_align" :
					hr.style.textAlign = value;
					switch (value) {
						case 'left':
							hr.style.marginLeft = "0";
							hr.style.marginRight = null;
						break;
						case 'right':
							hr.style.marginRight = "0";
							hr.style.marginLeft = null;
						break;
						case 'center':
							hr.style.marginLeft = "auto";
							hr.style.marginRight = "auto";
						break;
					}
				break;
				case "f_color" :
					hr.style.backgroundColor = value; 
				break;
				case "f_noshade" :
					
				break;
			  }
			  hr.style.border = (param["f_noshade"]) ? "0" : null; 
			}
		}
	}, outparam);
};
	