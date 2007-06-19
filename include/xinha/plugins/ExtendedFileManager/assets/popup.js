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

function __dlg_onclose() {
    if(opener.Dialog._return)
        opener.Dialog._return(null);
}

function __dlg_translate(context) {
    var types = ["span", "option", "td", "th", "button", "div", "label", "a","img", "legend"];
    for (var type = 0; type < types.length; type++) {
        var spans = document.getElementsByTagName(types[type]);
        for (var i = spans.length; --i >= 0;) {
            var span = spans[i];
            if (span.firstChild && span.firstChild.data) {
                var txt = Xinha._lc(span.firstChild.data, context);
                if (txt)
                    span.firstChild.data = txt;
            }
            if (span.title) {
                var txt = Xinha._lc(span.title, context);
                if (txt)
                    span.title = txt;
            }
            if (span.alt) {
                var txt = Xinha._lc(span.alt, context);
                if (txt)
                    span.alt = txt;
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