var MediaDialog = {
	preInit : function() {
		var url;
                
                tinyMCEPopup.requireLangPack();

		if (url = tinyMCEPopup.getParam("media_external_list_url"))
			document.write('<script language="javascript" type="text/javascript" src="' + tinyMCEPopup.editor.documentBaseURI.toAbsolute(url) + '"></script>');
	},

	init : function() {
		var f = document.forms[0], ed = tinyMCEPopup.editor;

		// Setup browse button
		document.getElementById('hrefbrowsercontainer').innerHTML = getBrowserHTML('hrefbrowser', 'href', 'eclmedia', 'eclmedia');
		if (isVisible('hrefbrowser'))
			document.getElementById('href').style.width = '230px';

		this.fillFileList('media_list', 'tinyMCEMediaList');
		this.fillTargetList('target_list');

		if (e = ed.dom.getParent(ed.selection.getNode(), 'A')) {
			f.href.value = ed.dom.getAttrib(e, 'href');
			f.mediatitle.value = ed.dom.getAttrib(e, 'title');
			f.insert.value = ed.getLang('update');
			selectByValue(f, 'media_list', f.href.value);
			selectByValue(f, 'target_list', ed.dom.getAttrib(e, 'target'));
		}
	},

	update : function() {
		var f = document.forms[0], ed = tinyMCEPopup.editor, e, b, href = f.href.value.replace(/ /g, '%20');

		tinyMCEPopup.restoreSelection();
		e = ed.dom.getParent(ed.selection.getNode(), 'A');

		// Remove element if there is no href
                if (!f.href.value) {
                        if (e) {
				b = ed.selection.getBookmark();
				ed.dom.remove(e, 1);
				ed.selection.moveToBookmark(b);
				tinyMCEPopup.execCommand("mceEndUndoLevel");
				tinyMCEPopup.close();
				return;
			}

			tinyMCEPopup.close();
			return;
		}

		// Create new anchor elements
		if (e == null) {
			ed.getDoc().execCommand("unlink", false, null);
			tinyMCEPopup.execCommand("mceInsertLink", false, "#mce_temp_url#", {skip_undo : 1});

			tinymce.each(ed.dom.select("a"), function(n) {
				if (ed.dom.getAttrib(n, 'href') == '#mce_temp_url#') {
					e = n;

					ed.dom.setAttribs(e, {
						href : href,
						title : f.mediatitle.value,
						target : f.target_list ? getSelectValue(f, "target_list") : null,
						'class' : 'colorboxframe'
					});
                                        //ed.selection.setContent(f.mediatitle.value);
                                        e.innerHTML = f.mediatitle.value;
				}
			});
		} else {
			ed.dom.setAttribs(e, {
				href : href,
				title : f.mediatitle.value,
				target : f.target_list ? getSelectValue(f, "target_list") : null,
				'class' : 'colorboxframe'
			});
                        e.innerHTML = f.mediatitle.value;
		}
                
                if (!f.mediatitle.value) {
                        tinyMCEPopup.alert(tinyMCEPopup.getLang('media_dlg.missing_mediatitle'));

                        return;
                }

		tinyMCEPopup.execCommand("mceEndUndoLevel");
		tinyMCEPopup.close();
	},

	checkPrefix : function(n) {
		if (/^\s*www\./i.test(n.value) && confirm(tinyMCEPopup.getLang('media_dlg.link_is_external')))
			n.value = 'http://' + n.value;
	},

	fillFileList : function(id, l) {
		var dom = tinyMCEPopup.dom, lst = dom.get(id), v, cl;

		l = window[l];

		if (l && l.length > 0) {
			lst.options[lst.options.length] = new Option('', '');

			tinymce.each(l, function(o) {
				lst.options[lst.options.length] = new Option(o[0], o[1]);
			});
		} else
			dom.remove(dom.getParent(id, 'tr'));
	},

	fillTargetList : function(id) {
		var dom = tinyMCEPopup.dom, lst = dom.get(id), v;

		lst.options[lst.options.length] = new Option(tinyMCEPopup.getLang('not_set'), '');
		lst.options[lst.options.length] = new Option(tinyMCEPopup.getLang('media_dlg.link_target_same'), '_self');
		lst.options[lst.options.length] = new Option(tinyMCEPopup.getLang('media_dlg.link_target_blank'), '_blank');

		if (v = tinyMCEPopup.getParam('theme_advanced_link_targets')) {
			tinymce.each(v.split(','), function(v) {
				v = v.split('=');
				lst.options[lst.options.length] = new Option(v[0], v[1]);
			});
		}
	}
};

MediaDialog.preInit();
tinyMCEPopup.onInit.add(MediaDialog.init, MediaDialog);
