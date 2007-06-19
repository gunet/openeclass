// htmlArea v3.0 - Copyright (c) 2002, 2003 interactivetools.com, inc.
// This copyright notice MUST stay intact for use (see license.txt).
//
// Portions (c) dynarch.com, 2003
//
// A free WYSIWYG editor replacement for <textarea> fields.
// For full source code and docs, visit http://www.interactivetools.com/
//
// Version 3.0 developed by Mihai Bazon.
//   http://dynarch.com/mishoo
//
// $Id$
Xinha = window.opener.Xinha;
// Backward compatibility will be removed some time or not?
HTMLArea = window.opener.Xinha;

function getAbsolutePos(el) {
	var r = { x: el.offsetLeft, y: el.offsetTop };
	if (el.offsetParent) {
		var tmp = getAbsolutePos(el.offsetParent);
		r.x += tmp.x;
		r.y += tmp.y;
	}
	return r;
}

function comboSelectValue(c, val) {
	var ops = c.getElementsByTagName("option");
	for (var i = ops.length; --i >= 0;) {
		var op = ops[i];
		op.selected = (op.value == val);
	}
	c.value = val;
}

function __dlg_onclose() {
	opener.Dialog._return(null);
}
// ray: I mark this on deprecated, because bottom is never used
function __dlg_init( bottom, win_dim ) {
  __xinha_dlg_init(win_dim);
}

function __xinha_dlg_init( win_dim ) {
  if(window.__dlg_init_done) return true;
  
  if(window.opener._editor_skin != "") {
    var head = document.getElementsByTagName("head")[0];
    var link = document.createElement("link");
    link.type = "text/css";
    link.href = window.opener._editor_url + 'skins/' + window.opener._editor_skin + '/skin.css';
    link.rel = "stylesheet";
    head.appendChild(link);
  }
	window.dialogArguments = opener.Dialog._arguments;

  var body = document.body;
  if ( !win_dim )
  {
   var dim = Xinha.viewportSize(window);
    win_dim = {width:dim.x, height: body.scrollHeight};
  }
  window.resizeTo(win_dim.width, win_dim.height);

  var dim = Xinha.viewportSize(window);
  window.resizeBy(0, body.scrollHeight - dim.y);

  if(win_dim.top && win_dim.left)
  {
    window.moveTo(win_dim.left,win_dim.top);
  }
  else
  {
    if (!Xinha.is_ie)
    {
      var x = opener.screenX + (opener.outerWidth - win_dim.width) / 2;
      var y = opener.screenY + (opener.outerHeight - win_dim.height) / 2;
    }
    else
    {//IE does not have window.outer... , so center it on the screen at least
      var x =  (self.screen.availWidth - win_dim.width) / 2;
      var y =  (self.screen.availHeight - win_dim.height) / 2;	
    }
    window.moveTo(x,y);
  }
  
  Xinha.addDom0Event(document.body, 'keypress', __dlg_close_on_esc);
  window.__dlg_init_done = true;
}

function __dlg_translate(context) {
	var types = ["input", "select", "legend", "span", "option", "td", "th", "button", "div", "label", "a", "img"];
	for (var type = 0; type < types.length; ++type) {
		var spans = document.getElementsByTagName(types[type]);
		for (var i = spans.length; --i >= 0;) {
			var span = spans[i];
			if (span.firstChild && span.firstChild.data) {
				var txt = Xinha._lc(span.firstChild.data, context);
				if (txt) {
					span.firstChild.data = txt;
				}
			}
			if (span.title) {
				var txt = Xinha._lc(span.title, context);
				if (txt) {
					span.title = txt;
				}
			}
			if (span.tagName.toLowerCase() == 'input' && 
					(/^(button|submit|reset)$/i.test(span.type))) {
				var txt = Xinha._lc(span.value, context);
				if (txt) {
					span.value = txt;
				}
			}
		}
	}
	document.title = Xinha._lc(document.title, context);
}

// closes the dialog and passes the return info upper.
function __dlg_close(val) {
	opener.Dialog._return(val);
	window.close();
}

function __dlg_close_on_esc(ev) {
	ev || (ev = window.event);
	if (ev.keyCode == 27) {
		__dlg_close(null);
		return false;
	}
	return true;
}
