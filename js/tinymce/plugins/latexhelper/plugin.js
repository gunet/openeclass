/**
 * LaTeX Helper Plugin for TinyMCE
 * Iframe Wrapper Version (Secure & Isolated)
 */
(function() {
    'use strict';

    tinymce.PluginManager.add('latexhelper', function(editor, url) {
        
        function openDialog() {
            // Get current content
            var content = editor.selection.getContent({format: 'text'});
            content = content.replace(/^\\\(|\\\)$/g, '').replace(/^\[m\]|\[\/m\]$/g, '');

            // Open dialog.html in an Iframe
            var win = editor.windowManager.open({
                title: 'Εισαγωγή LaTeX',
                url: url + '/dialog.html',
                width: 900,
                height: 700,
                buttons: [], // Dialog handles its own buttons
                inline: 1
            }, {
                // Pass data to the iframe
                initialCode: content
            });
        }

        window.addEventListener('message', function(event) {
            if (event.origin !== window.location.origin) {
                return;
            }

            var data = event.data;

            if (data.mceAction === 'latexhelper-insert') {
                var cleanCode = data.content.trim();
                
                // Basic Sanitization
                cleanCode = cleanCode.replace(/<\/?[^>]+(>|$)/g, "");

                if (cleanCode !== '') {
                    editor.insertContent('\\(' + cleanCode + '\\)');
                }
                editor.windowManager.close();
            }

            if (data.mceAction === 'latexhelper-cancel') {
                editor.windowManager.close();
            }
        });

        editor.addButton('latexhelper', {
            text: 'LaTeX',
            tooltip: 'Insert Math',
            onclick: openDialog
        });
        
        editor.addMenuItem('latexhelper', {
            icon: 'latex',
            text: 'LaTeX Math',
            context: 'insert',
            onclick: openDialog
        });

        return {
            getMetadata: function() {
                return { name: 'LaTeX Helper', url: 'https://www.openeclass.org' };
            }
        };
    });
})();