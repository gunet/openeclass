import { minimalSetup } from 'codemirror'
import { EditorView, keymap, lineNumbers } from '@codemirror/view'
import { EditorState, Prec } from '@codemirror/state'
import { indentWithTab, insertNewlineKeepIndent } from '@codemirror/commands'
import { indentUnit, StreamLanguage } from '@codemirror/language'

// Each language is a separate chunk, loaded only when an editor needs it.
const languages = {
    'text/x-c++src': () => import('@codemirror/legacy-modes/mode/clike').then(m => m.cpp),
    'text/x-csrc': () => import('@codemirror/legacy-modes/mode/clike').then(m => m.c),
    'text/x-java': () => import('@codemirror/legacy-modes/mode/clike').then(m => m.java),
    'sql': () => import('@codemirror/legacy-modes/mode/sql').then(m => m.standardSQL),
    'python': () => import('@codemirror/legacy-modes/mode/python').then(m => m.python),
}

// The platform theme sets the font on every <span> (default.css: "p, span"),
// which would break the monospace font of highlighted tokens.
const platformFixes = EditorView.theme({
    '.cm-content span': { fontFamily: 'inherit' },
})

// Replaces a <textarea> with a CodeMirror editor. The textarea stays in the
// form (hidden) and is kept in sync, so form submission is unchanged.
// Exercise answers get syntax highlighting only: no autocompletion, bracket
// closing/matching or smart indentation (Enter just keeps the line's indent).
async function fromTextArea(textarea, { language = 'text/x-c++src', onChange = null } = {}) {
    const mode = await (languages[language] ?? languages['text/x-c++src'])()
    const view = new EditorView({
        state: EditorState.create({
            doc: textarea.value,
            extensions: [
                Prec.high(keymap.of([{ key: 'Enter', run: insertNewlineKeepIndent }, indentWithTab])),
                lineNumbers(),
                minimalSetup,
                platformFixes,
                indentUnit.of('    '),
                EditorState.tabSize.of(4),
                StreamLanguage.define(mode),
                EditorView.updateListener.of(update => {
                    if (update.docChanged) {
                        textarea.value = update.state.doc.toString()
                        if (onChange) {
                            onChange(textarea.value)
                        }
                    }
                }),
            ],
        }),
    })
    textarea.parentNode.insertBefore(view.dom, textarea.nextSibling)
    textarea.style.display = 'none'
    return view
}

export { fromTextArea, languages, EditorView }
