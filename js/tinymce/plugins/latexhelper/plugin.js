/**
 * latexhelper plugin for TinyMCE 8+
 * Open eClass 3.0 Compatibility
 */
(function() {
    "use strict";

    tinymce.PluginManager.add("latexhelper", function(editor, url) {

        const openDialog = () => {
            let selectedText = editor.selection.getContent({ format: "text" });
            selectedText = selectedText.replace(/^\\\(|\\\)$/g, "").replace(/^\[m\]|\[\/m\]$/g, "");

            const lang = window.latexHelperLang || { title: "Insert LaTeX" };

            editor.windowManager.openUrl({
                title: lang.title,
                url: url + "/dialog.html",
                width: 900,
                height: 700,
                onMessage: (api, message) => {

                    if (message.mceAction === "latexhelper-insert") {
                        let content = message.content.trim();

                        content = content.replace(/<\/?[^>]+(>|$)/g, "");

                        if (content !== "") {
                            editor.insertContent("\\(" + content + "\\)");
                        }
                        api.close();
                    }

                    if (message.mceAction === "latexhelper-cancel") {
                        api.close();
                    }

                    if (message.mceAction === "ready") {
                        api.sendMessage({
                            mceAction: "initialData",
                            content: selectedText
                        });
                    }
                }
            });
        };

        editor.ui.registry.addButton("latexhelper", {
            text: "LaTeX",
            tooltip: "Insert Math",
            onAction: openDialog
        });

        editor.ui.registry.addMenuItem("latexhelper", {
            text: "LaTeX Math",
            icon: "latex",
            onAction: openDialog
        });

        return {
            getMetadata: function() {
                return {
                    name: "LaTeX Helper (v8 Compatible)",
                    url: "https://www.openeclass.org"
                };
            }
        };
    });
})();