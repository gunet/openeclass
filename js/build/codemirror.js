import { minimalSetup } from 'codemirror'
import { EditorView, keymap, lineNumbers } from '@codemirror/view'
import { EditorState, Prec } from '@codemirror/state'
import { indentWithTab, insertNewlineKeepIndent } from '@codemirror/commands'
import { defaultHighlightStyle, indentUnit, StreamLanguage, syntaxHighlighting } from '@codemirror/language'

// Each language is loaded on demand.
const languages = {
    'text/x-c++src': () => import('@codemirror/legacy-modes/mode/clike').then(m => m.cpp),
    'text/x-csrc': () => import('@codemirror/legacy-modes/mode/clike').then(m => m.c),
    'text/x-java': () => import('@codemirror/legacy-modes/mode/clike').then(m => m.java),
    'sql': () => import('@codemirror/legacy-modes/mode/sql').then(m => m.standardSQL),
    'python': () => import('@codemirror/legacy-modes/mode/python').then(m => m.python),
}
const defaultLanguage = 'text/x-c++src'

// hasOwn: ignore Object.prototype keys like "constructor"
function loadLanguage(language) {
    const load = Object.hasOwn(languages, language) ? languages[language] : languages[defaultLanguage]
    return load().then(mode => StreamLanguage.define(mode))
}

// default.css styles every span; Bootstrap here lacks --bs-border-*
const platformFixes = EditorView.theme({
    '&': {
        border: '1px solid var(--bs-border-color, #ddd)',
        borderRadius: 'var(--bs-border-radius, 4px)',
        fontSize: '0.9rem',
    },
    '.cm-content span': { fontFamily: 'inherit', fontSize: 'inherit', lineHeight: 'inherit' },
})

// Editor for a hidden, synced <textarea>. Highlighting only, no autocomplete.
async function fromTextArea(textarea, { language = defaultLanguage, onChange = null } = {}) {
    const lang = await loadLanguage(language)
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
                lang,
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

// Read-only highlighted view of an element's text (e.g. <pre>).
async function viewCode(element, { language = defaultLanguage } = {}) {
    const lang = await loadLanguage(language)
    const view = new EditorView({
        state: EditorState.create({
            doc: element.textContent,
            extensions: [
                lineNumbers(),
                syntaxHighlighting(defaultHighlightStyle, { fallback: true }),
                platformFixes,
                EditorState.readOnly.of(true),
                EditorView.editable.of(false),
                EditorState.tabSize.of(4),
                lang,
            ],
        }),
    })
    element.replaceWith(view.dom)
    return view
}

export { fromTextArea, viewCode, languages, EditorView }
