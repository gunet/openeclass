/*
 *  ========================================================================
 *  * Open eClass
 *  * E-learning and Course Management System
 *  * ========================================================================
 *  * Copyright 2003-2026, Greek Universities Network - GUnet
 *  *
 *  * Open eClass is an open platform distributed in the hope that it will
 *  * be useful (without any warranty), under the terms of the GNU (General
 *  * Public License) as published by the Free Software Foundation.
 *  * The full license can be read in "/info/license/license_gpl.txt".
 *  *
 *  * Contact address: GUnet Asynchronous eLearning Group
 *  *                  e-mail: info@openeclass.org
 *  * ========================================================================
 *
 *  Live preview for the wiki editor.
 *
 *  While typing in #wiki_content, the content is POSTed (one request in flight at a time) to
 *  page.php with live_preview=1, which returns a bare HTML fragment rendered
 *  with the same Wiki2xhtmlRenderer used for saved pages. Nothing is saved.
 *  The classic "Preview" submit button stays as the no-JS fallback.
 */

(function () {
    'use strict';

    // same cap as the server side (page.php live preview branch)
    var MAX_LENGTH = 131072;

    function l10n(key, fallback) {
        if (window.wikiPreviewL10n && window.wikiPreviewL10n[key]) {
            return window.wikiPreviewL10n[key];
        }
        return fallback;
    }

    function queryParam(url, name) {
        var m = url.match(new RegExp('[?&]' + name + '=([^&]*)'));
        return m ? decodeURIComponent(m[1].replace(/\+/g, ' ')) : '';
    }

    function insertAtCursor(el, text) {
        var start = (typeof el.selectionStart === 'number') ? el.selectionStart : el.value.length;
        var end = (typeof el.selectionEnd === 'number') ? el.selectionEnd : el.value.length;
        el.value = el.value.slice(0, start) + text + el.value.slice(end);
        el.selectionStart = el.selectionEnd = start + text.length;
        el.focus();
        var ev;
        try {
            ev = new Event('input', { bubbles: true });
        } catch (e) {
            ev = document.createEvent('Event');
            ev.initEvent('input', true, true);
        }
        el.dispatchEvent(ev);
    }

    function escapeHtml(s) {
        return String(s)
            .replace(/&/g, '&amp;')
            .replace(/</g, '&lt;')
            .replace(/>/g, '&gt;')
            .replace(/"/g, '&quot;');
    }

    document.addEventListener('DOMContentLoaded', function () {
        var textarea = document.getElementById('wiki_content');
        var form = document.getElementById('editform');
        if (!textarea || !form) {
            return;
        }

        var tokenField = form.querySelector('input[name="token"]');
        var wikiIdField = form.querySelector('input[name="wikiId"]');
        if (!tokenField || !wikiIdField) {
            return;
        }
        var formAction = form.getAttribute('action') || window.location.href;
        var title = queryParam(formAction, 'title');

        var STR = {
            livePreview: l10n('livePreview', 'Live preview'),
            editTab: l10n('editTab', 'Edit'),
            previewTab: l10n('previewTab', 'Preview'),
            denied: l10n('denied', 'Live preview is not available.'),
            error: l10n('error', 'Live preview failed to load. It will retry as you type.')
        };

        // ---- build split view: move the editor block into the left column ----
        var editorBlock = textarea.closest('.form-group') || textarea.parentNode;

        var tabs = document.createElement('div');
        tabs.className = 'btn-group d-lg-none mb-2 wiki-live-tabs';
        tabs.setAttribute('role', 'group');
        var editTab = document.createElement('button');
        editTab.type = 'button';
        editTab.className = 'btn btn-sm btn-primary';
        editTab.textContent = STR.editTab;
        var previewTab = document.createElement('button');
        previewTab.type = 'button';
        previewTab.className = 'btn btn-sm btn-outline-primary';
        previewTab.textContent = STR.previewTab;
        tabs.appendChild(editTab);
        tabs.appendChild(previewTab);

        var row = document.createElement('div');
        row.className = 'row wiki-live-row';
        row.setAttribute('data-view', 'edit');

        var editCol = document.createElement('div');
        editCol.className = 'col-lg-6 wiki-live-edit-col';
        var previewCol = document.createElement('div');
        previewCol.className = 'col-lg-6 wiki-live-preview-col';

        var previewCard = document.createElement('div');
        previewCard.className = 'card wiki-live-card';
        var previewHeader = document.createElement('div');
        previewHeader.className = 'card-header d-flex justify-content-between align-items-center';
        var previewTitle = document.createElement('span');
        previewTitle.innerHTML = '<i class="fa-solid fa-eye"></i> ' + escapeHtml(STR.livePreview);
        var previewStatus = document.createElement('small');
        previewStatus.className = 'text-muted wiki-live-status';
        previewHeader.appendChild(previewTitle);
        previewHeader.appendChild(previewStatus);
        var previewBody = document.createElement('div');
        previewBody.className = 'card-body wiki-live-body';
        var previewPane = document.createElement('div');
        previewPane.id = 'wiki-live-preview';
        previewPane.className = 'wiki2xhtml';
        previewBody.appendChild(previewPane);
        previewCard.appendChild(previewHeader);
        previewCard.appendChild(previewBody);
        previewCol.appendChild(previewCard);

        editorBlock.parentNode.insertBefore(tabs, editorBlock);
        editorBlock.parentNode.insertBefore(row, editorBlock);
        editCol.appendChild(editorBlock);
        row.appendChild(editCol);
        row.appendChild(previewCol);

        function setView(view) {
            row.setAttribute('data-view', view);
            var onEdit = (view === 'edit');
            editTab.className = 'btn btn-sm ' + (onEdit ? 'btn-primary' : 'btn-outline-primary');
            previewTab.className = 'btn btn-sm ' + (onEdit ? 'btn-outline-primary' : 'btn-primary');
        }
        editTab.addEventListener('click', function () { setView('edit'); });
        previewTab.addEventListener('click', function () {
            setView('preview');
            update();
        });

        // ---- live updates ----
        // No debounce: a keystroke is sent at once if no request is running;
        // keystrokes typed while one is running are coalesced and sent as soon
        // as it returns. At most one request per editor is ever in flight, so
        // the preview follows typing as fast as the server answers without
        // queueing requests.
        var inFlight = false;
        var lastSent = null;
        var retryTimer = null;
        // sentinel for a server throttle (HTTP 429); compared by reference so
        // it can never collide with actual rendered HTML
        var THROTTLED = {};
        var THROTTLE_RETRY_MS = 300;

        function setStatus(text, isError) {
            previewStatus.textContent = text;
            previewStatus.className = 'wiki-live-status ' + (isError ? 'text-danger' : 'text-muted');
        }

        function showDenied() {
            previewPane.innerHTML = '<div class="alert alert-warning">' + escapeHtml(STR.denied) + '</div>';
            setStatus('', false);
        }

        // server limit is in bytes (UTF-8), textarea length is in UTF-16 units
        function byteLength(s) {
            if (window.TextEncoder) {
                return new TextEncoder().encode(s).length;
            }
            return unescape(encodeURIComponent(s)).length;
        }

        // Scripts inserted through innerHTML never run. The only ones the
        // renderer emits are for the table of contents ("""toc""" macro),
        // so drop them and build the TOC inside the preview instead.
        function renderToc() {
            var scripts = previewPane.querySelectorAll('script');
            var wantsToc = false;
            for (var i = 0; i < scripts.length; i++) {
                if (/createTOC\(/.test(scripts[i].textContent)) {
                    wantsToc = true;
                }
                scripts[i].parentNode.removeChild(scripts[i]);
            }
            var content = previewPane.firstElementChild;
            if (wantsToc && content && typeof window.createTOC === 'function') {
                content.id = 'wiki-live-preview-content';
                window.createTOC(content.id);
            }
        }

        function done() {
            inFlight = false;
            if (textarea.value !== lastSent) {
                update();
            }
        }

        function update() {
            var content = textarea.value;
            if (inFlight || content === lastSent) {
                return;
            }
            if (byteLength(content) > MAX_LENGTH) {
                setStatus(STR.error, true);
                return;
            }
            inFlight = true;
            lastSent = content;
            var params = 'live_preview=1'
                + '&title=' + encodeURIComponent(title)
                + '&wikiId=' + encodeURIComponent(wikiIdField.value)
                + '&token=' + encodeURIComponent(tokenField.value)
                + '&wiki_content=' + encodeURIComponent(content);
            fetch(formAction, {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/x-www-form-urlencoded; charset=UTF-8',
                    'X-Requested-With': 'XMLHttpRequest'
                },
                body: params
            }).then(function (resp) {
                if (resp.status === 429) {
                    // server throttled us: back off and resend the latest text
                    return THROTTLED;
                }
                if (!resp.ok) {
                    if (resp.status === 403) {
                        showDenied();
                    } else {
                        setStatus(STR.error, true);
                    }
                    return null;
                }
                if (resp.headers.get('X-Wiki-Preview') !== '1') {
                    // e.g. a login redirect slipped through: don't inject it
                    showDenied();
                    return null;
                }
                return resp.text();
            }).then(function (result) {
                if (result === THROTTLED) {
                    inFlight = false;
                    lastSent = null; // force a resend of the current content
                    if (retryTimer) {
                        clearTimeout(retryTimer);
                    }
                    retryTimer = setTimeout(update, THROTTLE_RETRY_MS);
                    return;
                }
                if (result !== null) {
                    previewPane.innerHTML = result;
                    renderToc();
                    setStatus('', false);
                }
                done();
            }).catch(function () {
                // keep the last good preview, note the failure; let the next
                // keystroke retry instead of looping on a dead connection
                setStatus(STR.error, true);
                inFlight = false;
            });
        }

        textarea.addEventListener('input', update);
        // initial render
        update();

        // ---- syntax help: click an example to insert it ----
        document.addEventListener('click', function (ev) {
            var btn = ev.target.closest ? ev.target.closest('[data-wiki-insert]') : null;
            if (!btn) {
                return;
            }
            ev.preventDefault();
            insertAtCursor(textarea, btn.getAttribute('data-wiki-insert') || '');
        });
    });
})();
