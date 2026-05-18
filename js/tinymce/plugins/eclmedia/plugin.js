/**
 * plugin.js
 *
 * ========================================================================
 * Open eClass 3.0
 * E-learning and Course Management System
 * ========================================================================
 * Copyright 2003-2026  Greek Universities Network - GUnet
 * A full copyright notice can be read in "/info/copyright.txt".
 * For a full list of contributors, see "credits.txt".
 *
 * Open eClass is an open platform distributed in the hope that it will
 * be useful (without any warranty), under the terms of the GNU (General
 * Public License) as published by the Free Software Foundation.
 * The full license can be read in "/info/license/license_gpl.txt".
 *
 * Contact address: GUnet Asynchronous eLearning Group,
 * Network Operations Center, University of Athens,
 * Panepistimiopolis Ilissia, 15784, Athens, Greece
 * e-mail: info@openeclass.org
 * ======================================================================== */

/**
 * eclmedia/plugin.js - Version for TinyMCE 8 (Open eClass)
 */
tinymce.PluginManager.add('eclmedia', function(editor, url) {

    const openDialog = () => {
        const selectedElm = editor.selection.getNode();
        const anchorElm = editor.dom.getParent(selectedElm, 'a.colorboxframe');

        const initialData = {
            href: anchorElm ? editor.dom.getAttrib(anchorElm, 'href') : '',
            text: anchorElm ? (anchorElm.innerText || anchorElm.textContent) : editor.selection.getContent({ format: 'text' }),
            title: anchorElm ? editor.dom.getAttrib(anchorElm, 'title') : '',
            target: anchorElm ? editor.dom.getAttrib(anchorElm, 'target') : ''
        };

        editor.windowManager.open({
            title: 'Insert/Edit Pop-Up Media',
            size: 'normal',
            body: {
                type: 'panel',
                items: [
                    {
                        type: 'input',
                        name: 'href',
                        label: 'Διεύθυνση (URL)'
                    },
                    {
                        type: 'button',
                        name: 'browse',
                        text: 'Αναζήτηση στη βιβλιοθήκη...',
                        icon: 'browse',
                        border: true
                    },
                    {
                        type: 'input',
                        name: 'text',
                        label: 'Κείμενο για εμφάνιση'
                    },
                    {
                        type: 'input',
                        name: 'title',
                        label: 'Τίτλος (Tooltip)'
                    },
                    {
                        type: 'selectbox',
                        name: 'target',
                        label: 'Προορισμός',
                        items: [
                            { text: 'Καμία', value: '' },
                            { text: 'Νέο παράθυρο', value: '_blank' }
                        ]
                    }
                ]
            },
            buttons: [
                { type: 'cancel', text: 'Κλείσιμο' },
                { type: 'submit', text: 'Αποθήκευση', primary: true }
            ],
            initialData: initialData,

            onAction: (api, details) => {
                if (details.name === 'browse') {
                    const filePicker = editor.options.get('file_picker_callback');

                    if (filePicker) {
                        filePicker((fileUrl) => {
                            api.setData({ href: fileUrl });
                            if (!api.getData().text) {
                                const fileName = fileUrl.split('/').pop();
                                api.setData({ text: fileName });
                            }
                        }, '', { filetype: 'file' });
                    } else {
                        console.error("File picker callback is not defined in TinyMCE init.");
                    }
                }
            },

            onSubmit: (api) => {
                const data = api.getData();

                if (!data.href) {
                    editor.execCommand('unlink');
                    api.close();
                    return;
                }

                const linkText = data.text || data.href;

                const linkAttrs = {
                    href: data.href,
                    target: data.target ? data.target : null,
                    class: 'colorboxframe',
                    title: data.title ? data.title : null
                };

                if (anchorElm) {
                    editor.focus();
                    anchorElm.innerText = linkText;
                    editor.dom.setAttribs(anchorElm, linkAttrs);
                    editor.selection.select(anchorElm);
                } else {
                    const html = editor.dom.createHTML('a', linkAttrs, editor.dom.encode(linkText));
                    editor.insertContent(html);
                }

                editor.undoManager.add();
                api.close();
            }
        });
    };

    editor.ui.registry.addIcon('ecl-video-icon', '<svg width="24" height="24" viewBox="0 0 24 24"><path d="M10 15l5.19-3L10 9v6m11.56-7.83c.13.47.22 1.1.28 1.9.07.8.1 1.49.1 2.09L22 12c0 .61-.03 1.3-.1 2.1-.06.8-.15 1.43-.28 1.9-.13.47-.36.87-.7 1.18-.33.32-.77.53-1.32.64-.54.1-1.41.18-2.6.24-1.2.06-2.13.1-2.8.1L14 18.2c-.67 0-1.6-.04-2.8-.1-1.19-.06-2.06-.14-2.6-.24-.55-.11-.99-.32-1.32-.64-.34-.31-.57-.71-.7-1.18-.13-.47-.22-1.1-.28-1.9-.07-.8-.1-1.49-.1-2.09L6 12c0-.61.03-1.3.1-2.1.06-.8.15-1.43.28-1.9.13-.47.36-.87.7-1.18.33-.32.77-.53 1.32-.64.54-.1 1.41-.18 2.6-.24 1.2-.06 2.13-.1 2.8-.1L14 5.8c.67 0 1.6.04 2.8.1 1.19.06 2.06.14 2.6.24.55.11.99.32 1.32.64.34.31.57.71.7 1.18z" fill-rule="evenodd"/></svg>');

    editor.ui.registry.addToggleButton('eclmedia', {
        icon: 'ecl-video-icon',
        tooltip: 'Insert/Edit Pop-Up Media',
        onAction: openDialog,
        onSetup: (buttonApi) => {
            const nodeChangeHandler = () => {
                const isMedia = !!editor.dom.getParent(editor.selection.getNode(), 'a.colorboxframe');
                buttonApi.setActive(isMedia);
            };
            editor.on('NodeChange', nodeChangeHandler);
            return () => editor.off('NodeChange', nodeChangeHandler);
        }
    });

    editor.ui.registry.addMenuItem('eclmedia', {
        text: 'Pop-Up Media',
        icon: 'ecl-video-icon',
        onAction: openDialog
    });
});