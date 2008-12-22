/**=============================================================================
       	GUnet eClass 2.0 
        E-learning and Course Management Program  
/*===========================================================================
*   Open eClass 2.1
*   E-learning and Course Management System
* ===========================================================================
*	Copyright(c) 2003-2008  Greek Universities Network - GUnet
*	A full copyright notice can be read in "/info/copyright.txt".
*
*  	Authors:	Costas Tsibanis <k.tsibanis@noc.uoa.gr>
*				Yannis Exidaridis <jexi@noc.uoa.gr>
*				Alexandros Diamantidis <adia@noc.uoa.gr>
*
*	For a full list of contributors, see "credits.txt".
*
*	This program is a free software under the terms of the GNU
*	(General Public License) as published by the Free Software
*	Foundation. See the GNU License for more details.
*	The full license can be read in "license.txt".
*
*	Contact address: 	GUnet Asynchronous Teleteaching Group,
*						Network Operations Center, University of Athens,
*						Panepistimiopolis Ilissia, 15784, Athens, Greece
*						eMail: eclassadmin@gunet.gr
============================================================================*/

/**===========================================================================
	toolbar.js
	@last update: 15-05-2007 by Thanos Kyritsis
	@authors list: Thanos Kyritsis <atkyritsis@upnet.gr>
	               
	based on DotClear licensed under GPL
	      copyright (c) 2004 Olivier Meunier and contributors
	      
	      original file: toolbar Revision:
	      
==============================================================================        
    @Description: 

    @Comments:
 
    @todo: 
==============================================================================
*/

/* 	Usage
    # Toolbar
	echo
	'<script type="text/javascript" src="js/toolbar.js"></script>'.
	'<script type="text/javascript">'.
	"if (document.getElementById) {
		var tb = new dcToolBar(document.getElementById('p_content'),
		document.getElementById('p_format'),'images/');

		tb.btStrong('Strong emphasis');
		tb.btEm('Emphasis'');
		tb.btIns('Inserted');
		tb.btDel('Deleted');
		tb.btQ('Inline quote');
		tb.btCode('Code');
		tb.addSpace(10);
		tb.btBr('Line break');
		tb.addSpace(10);
		tb.btBquote('Blockquote');
		tb.btPre('Preformated text');
		tb.btList('Unordered list');
		tb.btList('Ordered list');
		tb.addSpace(10);
		tb.btLink('Link','URL?','Language?','fr');
		tb.btImgLink('External image','URL?');
		tb.addSpace(10);
		# create image manager from document tool
		tb.btImg('Internal image','images-popup.php');
		tb.draw('You can use the following shortcuts to refine your layout.');
	}
	</script>";
	# Fin toolbar */
var img_path = "toolbar";
 
function dcToolBar(textarea,format,img_path)
{
	this.addButton		= function() {}
	this.addSpace		= function() {}
	this.draw		= function() {}
	this.btStrong		= function() {}
	this.btEm		= function() {}
	this.btIns		= function() {}
	this.btDel		= function() {}
	this.btQ		= function() {}
	this.btCode		= function() {}
	this.btBr		= function() {}
	this.btBquote		= function() {}
	this.btPre		= function() {}
	this.btList		= function() {}
	this.btLink		= function() {}
	this.btImgLink		= function() {}
	this.btImg		= function() {}
	this.insImg		= function() {}
	
    if (!document.createElement) {
		return;
	}

	if ((typeof(document["selection"]) == "undefined")
	&& (typeof(textarea["setSelectionRange"]) == "undefined")) {
		return;
	}
	
	var toolbar = document.createElement("div");
	toolbar.id = "dctoolbar";
	
	function getFormat() {
		if (format == 'wiki') {
			return 'wiki';
		} else {
			return 'html';
		}
	}
	
	function addButton(src, title, fn) {
		var i = document.createElement('img');
		i.src = src;
		i.title = title;
		i.onclick = function() { try { fn() } catch (e) { } return false };
		i.tabIndex = 400;
		toolbar.appendChild(i);
		addSpace(2);
	}
	
	function addSpace(w)
	{
		var s = document.createElement('span');
		s.style.padding='0 '+w+'px 0 0';
		s.appendChild(document.createTextNode(' '));
		toolbar.appendChild(s);
	}
	
	function encloseSelection(prefix, suffix, fn) {
		textarea.focus();
		var start, end, sel, scrollPos, subst;
		
		if (typeof(document["selection"]) != "undefined") {
			sel = document.selection.createRange().text;
		} else if (typeof(textarea["setSelectionRange"]) != "undefined") {
			start = textarea.selectionStart;
			end = textarea.selectionEnd;
			scrollPos = textarea.scrollTop;
			sel = textarea.value.substring(start, end);
		}
		
		if (sel.match(/ $/)) { // exclude ending space char, if any
			sel = sel.substring(0, sel.length - 1);
			suffix = suffix + " ";
		}
		
		if (typeof(fn) == 'function') {
			var res = (sel) ? fn(sel) : fn('');
		} else {
			var res = (sel) ? sel : '';
		}
		
		subst = prefix + res + suffix;
		
		if (typeof(document["selection"]) != "undefined") {
			var range = document.selection.createRange().text = subst;
			textarea.caretPos -= suffix.length;
		} else if (typeof(textarea["setSelectionRange"]) != "undefined") {
			textarea.value = textarea.value.substring(0, start) + subst +
			textarea.value.substring(end);
			if (sel) {
				textarea.setSelectionRange(start + subst.length, start + subst.length);
			} else {
				textarea.setSelectionRange(start + prefix.length, start + prefix.length);
			}
			textarea.scrollTop = scrollPos;
		}
	}
	
	function draw(msg) {
		var p = document.createElement('em');
		p.style.display='block';
		p.style.margin='-0.5em 0 0.5em 0';
		p.appendChild(document.createTextNode(msg));
		textarea.parentNode.insertBefore(p, textarea);
		textarea.parentNode.insertBefore(toolbar, textarea);
	}
	
	
	// ---
	function singleTag(wtag,htag,wetag) {
		if (getFormat() == 'wiki') {
			var stag = wtag;
			var etag = (wetag) ? wetag : wtag;
		} else {
			var stag = '<'+htag+'>';
			var etag = '</'+htag+'>';
		}
		encloseSelection(stag,etag);
	}
	
	function btStrong(label) {
		addButton(img_path+'bt_strong.png',label,
		function() { singleTag("'''",'strong'); });
	}
	
	function btEm(label) {
		addButton(img_path+'bt_em.png',label,
		function() { singleTag("''",'em'); });
	}
	
	function btIns(label) {
		addButton(img_path+'bt_ins.png',label,
		function() { singleTag('__','u'); });
	}
	
	function btDel(label) {
		addButton(img_path+'bt_del.png',label,
		function() { singleTag('--','stroke'); });
	}
	
	function btQ(label) {
		addButton(img_path+'bt_quote.png',label,
		function() { singleTag('{{','q','}}'); });
	}
	
	function btCode(label) {
		addButton(img_path+'bt_code.png',label,
		function() { singleTag('@@','code'); });
	}
	
	function btBr(label) {
		addButton(img_path+'bt_br.png',label,
		function() {
			var tag = getFormat() == 'wiki' ? "%%%\n" : "<br />\n";
			encloseSelection('',tag);
		});
	}
	
	function btBquote(label) {
		addButton(img_path+'bt_bquote.png',label,
		function() {
			encloseSelection("\n",'',
			function(str) {
				if (getFormat() == 'wiki') {
					str = str.replace(/\r/g,'');
					return '> '+str.replace(/\n/g,"\n> ");
				} else {
					return "<blockquote>"+str+"</blockquote>\n";
				}
			});
		});
	}
	
	function btPre(label) {
		addButton(img_path+'bt_pre.png',label,
		function() {
			encloseSelection("\n",'',
			function(str) {
				if (getFormat() == 'wiki') {
					str = str.replace(/\r/g,'');
					return ' '+str.replace(/\n/g,"\n ");
				} else {
					return "<pre>"+str+"</pre>\n";
				}
			});
		});
	}
	
	function btList(label,type) {
		var img = (type == 'ul') ? 'bt_ul.png' : 'bt_ol.png';
		var wtag = (type == 'ul') ? '*' : '#';
		
		addButton(img_path+img,label,
		function() {
			encloseSelection("",'',
			function(str) {
				if (getFormat() == 'wiki') {
					str = str.replace(/\r/g,'');
					return wtag+' '+str.replace(/\n/g,"\n"+wtag+' ');
				} else {
					str = str.replace(/\r/g,'');
					str = str.replace(/\n/g,"</li>\n <li>");
					return "<"+type+">\n <li>"+str+"</li>\n</"+type+">";
				}
			});
		});
	}
	
	function btLink(label,msg_url,msg_lang,default_lang) {
		addButton(img_path+'bt_link.png',label,
		function() {
			var href = window.prompt(msg_url,'');
			if (!href) { return; }
			
			var hreflang = window.prompt(msg_lang,default_lang);
			
			if (getFormat() == 'wiki') {
				stag = '[';
				var etag = href;
				if (hreflang) { etag = etag+'|'+hreflang; }
				etag = etag+']';
			} else {
				var stag = '<a href="'+href+'"';
				if (hreflang) { stag = stag+' hreflang="'+hreflang+'"'; }
				stag = stag+'>';
				etag = '</a>';
			}
			
			encloseSelection(stag,etag);
		});
	}
	
	function btImgLink(label,msg_src)
	{
		addButton(img_path+'bt_img_link.png',label,
		function() {
			encloseSelection('','',
			function(str) {
				var src = window.prompt(msg_src,'');
				if (!src) { return str; }
				
				if (getFormat() == 'wiki') {
					if (str) {
						return '(('+src+'|'+str+'))';
					} else {
						return '(('+src+'))';
					}
				} else {
					if (str) {
						return '<img src="'+src+'" alt="'+str+'" />';
					} else {
						return '<img src="'+src+'" alt="" />';
					}
				}
			});
		});
	}
	
	function btImg(label,url)
	{
		addButton(img_path+'bt_img.png',label,
		function() {
			popup(url);
		});
	}
	
	function insImg(src)
	{
		if (document.all) {
			textarea.focus();
			if (getFormat() == 'wiki') {
				textarea.value = textarea.value+'(('+src+'))';
			} else {
				textarea.value = textarea.value+'<img src="'+src+'" alt="" />';
			}
		} else {
			encloseSelection('','',
			function(str) {
				if (getFormat() == 'wiki') {
					if (str) {
						return '(('+src+'|'+str+'))';
					} else {
						return '(('+src+'))';
					}
				} else {
					if (str) {
						return '<img src="'+src+'" alt="'+str+'" />';
					} else {
						return '<img src="'+src+'" alt="" />';
					}
				}
			});
		}
	}
	
	// methods
	this.addButton	= addButton;
	this.addSpace	= addSpace;
	this.draw	= draw;
	this.btStrong	= btStrong;
	this.btEm	= btEm;
	this.btIns	= btIns;
	this.btDel	= btDel;
	this.btQ	= btQ;
	this.btCode	= btCode;
	this.btBr	= btBr;
	this.btBquote	= btBquote;
	this.btPre	= btPre;
	this.btList	= btList;
	this.btLink	= btLink;
	this.btImgLink	= btImgLink;
	this.btImg	= btImg;
	this.insImg	= insImg;
}
