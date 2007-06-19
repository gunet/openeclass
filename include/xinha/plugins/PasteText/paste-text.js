// Paste Plain Text plugin for Xinha

// Distributed under the same terms as Xinha itself.
// This notice MUST stay intact for use (see license.txt).

function PasteText(editor) {
	this.editor = editor;
	var cfg = editor.config;
	var self = this;
        
	cfg.registerButton({
                id       : "pastetext",
                tooltip  : this._lc("Paste as Plain Text"),
                image    : editor.imgURL("ed_paste_text.gif", "PasteText"),
                textMode : false,
                action   : function(editor) {
                             self.buttonPress(editor);
                           }
            });

	cfg.addToolbarElement("pastetext", ["paste", "killword"], 1);
}

PasteText._pluginInfo = {
	name          : "PasteText",
	version       : "1.2",
	developer     : "Michael Harris",
	developer_url : "http://www.jonesadvisorygroup.com",
	c_owner       : "Jones Advisory Group",
	sponsor       : "Jones International University",
	sponsor_url   : "http://www.jonesinternational.edu",
	license       : "htmlArea"
};

PasteText.prototype._lc = function(string) {
    return Xinha._lc(string, 'PasteText');
};

Xinha.Config.prototype.PasteText =
{
	showParagraphOption : true,
	newParagraphDefault :true
}

PasteText.prototype.buttonPress = function(editor) {

	var editor = this.editor;
	var outparam = editor.config.PasteText; 
	var action = function( ret ) {
		var html = ret.text;
		var insertParagraphs = ret.insertParagraphs;
		html = html.replace(/</g, "&lt;");
  		html = html.replace(/>/g, "&gt;");
  		if ( ret.insertParagraphs)
  		{
  			html = html.replace(/\t/g,"&nbsp;&nbsp;&nbsp;&nbsp;");
			html = html.replace(/\n/g,"</p><p>");
			html="<p>" + html + "</p>";
			if (Xinha.is_ie)
			{
				editor.insertHTML(html);
			}
			else
			{
				editor.execCommand("inserthtml",false,html);
			}
		}
		else
		{
			html = html.replace(/\n/g,"<br />");
			editor.insertHTML(html);
		}
	}
	Dialog( _editor_url+ "plugins/PasteText/popups/paste_text.html", action, outparam);
};