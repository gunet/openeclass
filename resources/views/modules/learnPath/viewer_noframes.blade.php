@extends('layouts.learnpath_player')

@push('head_styles')
    <style>
        body.lp-fullscreen {
            margin: 0;
            padding: 0;
            overflow: hidden;
        }
        .lp-player {
            display: flex;
            flex-direction: column;
            height: 100vh;
        }
        .lp-body {
            display: grid;
            grid-template-columns: 1fr;
            flex: 1;
            min-height: 0;
        }
        .lp-body.sidebar-open {
            grid-template-columns: 1fr 280px;
        }
        .lp-sidebar {
            overflow: auto;
            border-left: 1px solid #e0e0e0;
            display: none;
            order: 1;
        }
        .lp-body.sidebar-open .lp-sidebar {
            display: block;
        }
        .lp-content {
            min-width: 0;
            min-height: 0;
            order: 0;
        }
        .lp-content iframe {
            display: block;
            width: 100%;
            height: 100%;
            border: 0;
        }
        @media (max-width: 900px) {
            .lp-body.sidebar-open {
                grid-template-columns: 1fr;
            }
            .lp-body.sidebar-open .lp-sidebar {
                position: absolute;
                right: 0;
                z-index: 10;
                width: 80%;
                max-width: 320px;
                height: calc(100% - 50px);
                background: #fff;
                box-shadow: -2px 0 12px rgba(0,0,0,0.15);
            }
        }
        #lp-stale-warning {
            display: none;
            background: #fff3cd;
            color: #856404;
            padding: 4px 12px;
            font-size: 13px;
            text-align: center;
            border-bottom: 1px solid #ffc107;
        }
        #lp-stale-warning.is-visible {
            display: block;
        }
    </style>
@endpush

@section('content')
<div id="lp-player" class="lp-player">
    <header id="lp-header">
        {!! $headerFragment !!}
    </header>
    <div id="lp-stale-warning">{{ trans('langLearningPathProgressOutdated') }}</div>

    <div id="lp-body" class="lp-body">
        <main id="lp-content" class="lp-content">
            <iframe id="lp-iframe" name="scoFrame" src="{{ $moduleStartAssetPage }}" frameborder="0" allowfullscreen></iframe>
        </main>

        <aside id="lp-sidebar" class="lp-sidebar">
            {!! $sidebarFragment !!}
        </aside>
    </div>
</div>
@endsection

@push('body_scripts')
<script type="text/javascript">
    window.eclassLP = window.eclassLP || {};
    window.eclassLP.closeUrl = document.querySelector('#lp-header [data-close-url]')?.getAttribute('data-close-url') || '';

    function bindHeaderEvents() {
        let toggle = document.getElementById('lp-sidebar-toggle');
        if (toggle) {
            toggle.addEventListener('click', function() {
                let body = document.getElementById('lp-body');
                if (body) {
                    body.classList.toggle('sidebar-open');
                }
            });
        }

        let exitBtn = document.getElementById('lp-exit');
        if (exitBtn) {
            exitBtn.addEventListener('click', function(e) {
                e.preventDefault();
                let dest = exitBtn.getAttribute('href') || window.eclassLP.closeUrl;
                if (dest) {
                    window.location.href = dest;
                }
            });
        }
    }

    /**
     * _refreshFragment — fetch an HTML fragment into a container.
     * On failure: retry once after 2 s, then show #lp-stale-warning.
     * On success: auto-clear #lp-stale-warning if visible.
     */
    function _refreshFragment(url, containerId, afterInsert) {
        let staleEl = document.getElementById('lp-stale-warning');

        function doFetch(isRetry) {
            fetch(url)
                .then(function(resp) {
                    if (!resp.ok) throw new Error('HTTP ' + resp.status);
                    return resp.text();
                })
                .then(function(html) {
                    document.getElementById(containerId).innerHTML = html;
                    if (typeof afterInsert === 'function') afterInsert();
                    // Clear stale indicator on success
                    if (staleEl) staleEl.classList.remove('is-visible');
                })
                .catch(function(err) {
                    console.warn('[eclassLP] Fragment refresh failed (' + containerId + '):', err);
                    if (!isRetry) {
                        setTimeout(function() { doFetch(true); }, 2000);
                    } else {
                        // Both attempts failed — show stale warning
                        console.warn('[eclassLP] Fragment refresh retry also failed (' + containerId + '). Showing stale indicator.');
                        if (staleEl) staleEl.classList.add('is-visible');
                    }
                });
        }

        doFetch(false);
    }

    function refreshHeader() {
        _refreshFragment(
            '{{ $urlAppend }}modules/learnPath/viewer_noframes.php?course={{ $course_code }}&fragment=header{!! $unitParamPlain !!}',
            'lp-header',
            function() {
                window.eclassLP.closeUrl = document.querySelector('#lp-header [data-close-url]')?.getAttribute('data-close-url') || '';
                bindHeaderEvents();
            }
        );
    }

    function refreshToc() {
        _refreshFragment(
            '{{ $urlAppend }}modules/learnPath/viewer_noframes.php?course={{ $course_code }}&fragment=toc{!! $unitParamPlain !!}',
            'lp-sidebar'
        );
    }

    window.eclassLP.onCommit = function() {
        refreshHeader();
        refreshToc();
    };

    bindHeaderEvents();

    // ====================================================
    // AJAX-based module navigation
    // ====================================================

    let _navInProgress = false; // double-click guard

    /**
     * Extract module_id from a viewer URL
     */
    function _extractModuleId(href) {
        if (!href) return null;
        let match = href.match(/[?&]module_id=(\d+)/);
        return match ? match[1] : null;
    }

    /**
     * Build the prepareModule endpoint URL for a given module_id
     */
    function _buildPrepareUrl(moduleId) {
        return '{{ $urlAppend }}modules/learnPath/viewer_noframes.php?course={{ $course_code }}{!! $unitParamPlain !!}&action=prepareModule&module_id=' + moduleId;
    }

    /**
     * navigateToModule — AJAX navigation to a new module.
     * Fires SCORM commit and prepareModule fetch in parallel (they are
     * data-independent: commit writes CURRENT module's progress row,
     * prepareModule reads TARGET module's data), then updates the UI.
     * Falls back to full page reload on any failure.
     */
    function navigateToModule(moduleId, fullHref, pushHistory) {
        if (_navInProgress) return;
        _navInProgress = true;

        // Safety timeout: reset nav lock if neither .then() nor .catch() fires
        let navSafetyTimer = setTimeout(function() {
            if (_navInProgress) {
                _navInProgress = false;
                console.warn('[eclassLP] Navigation safety timeout — resetting lock');
            }
        }, 15000);

        // Stop media and abort in-flight video range requests to free connections
        try {
            let iframeDoc = document.getElementById('lp-iframe').contentDocument;
            if (iframeDoc) {
                iframeDoc.querySelectorAll('video, audio').forEach(function(el) {
                    el.pause();
                    el.removeAttribute('src');
                    el.load();
                });
            }
        } catch(e) { /* cross-origin iframe — ignore */ }

        // Blank iframe to release all network connections from old module
        document.getElementById('lp-iframe').src = 'about:blank';

        // Fire SCORM commit (best-effort, don't block navigation on it)
        if (window.eclassLP && window.eclassLP.isScorm && typeof doCommitAwaitable === 'function') {
            doCommitAwaitable();
        }

        // AbortController timeout: abort fetch after 10s to prevent indefinite hang
        let controller = new AbortController();
        let fetchTimer = setTimeout(function() { controller.abort(); }, 10000);

        fetch(_buildPrepareUrl(moduleId), { signal: controller.signal })
            .then(function(resp) {
                clearTimeout(fetchTimer);
                if (!resp.ok) throw new Error('HTTP ' + resp.status);
                return resp.json();
            }).then(function(data) {
            clearTimeout(navSafetyTimer);
            if (!data.ok) throw new Error(data.error || 'prepareModule failed');

            // Update iframe
            let iframe = document.getElementById('lp-iframe');
            iframe.src = data.moduleStartAssetPage;

            // Update SCORM API state
            if (data.isScorm && data.scormData && typeof resetScormApi === 'function') {
                resetScormApi({
                    sco: data.scormData.sco,
                    ump_id: data.scormData.ump_id,
                    commitUrl: data.commitUrl
                });
            } else if (typeof deactivateScormApi === 'function') {
                deactivateScormApi();
            }

            // Refresh TOC and header
            refreshHeader();
            refreshToc();

            // Update browser history
            if (pushHistory !== false && fullHref) {
                history.pushState({ moduleId: moduleId }, '', fullHref);
            }

            _navInProgress = false;
        }).catch(function(err) {
            clearTimeout(navSafetyTimer);
            clearTimeout(fetchTimer);
            console.warn('[eclassLP] AJAX navigation failed, falling back to full page reload:', err);
            _navInProgress = false;
            // Graceful fallback: full page reload
            if (fullHref) {
                window.location.href = fullHref;
            }
        });
    }

    // Intercept TOC and prev/next links: AJAX navigation
    document.addEventListener('click', function(e) {
        let link = e.target.closest('a.lp-nav-link');
        if (!link) return;
        e.preventDefault();

        let href = link.getAttribute('href');
        let moduleId = _extractModuleId(href);
        if (!moduleId) return;

        navigateToModule(moduleId, href, true);
    });

    // Browser back/forward via popstate
    // Store initial state
    (function() {
        let initialModuleId = '{{ $initialModuleId }}';
        if (!history.state) {
            history.replaceState({ moduleId: initialModuleId }, '', window.location.href);
        }
    })();

    window.addEventListener('popstate', function(e) {
        if (e.state && e.state.moduleId) {
            navigateToModule(e.state.moduleId, window.location.href, false);
        }
    });
</script>

{!! $scormApiScript !!}

<script type="text/javascript">
    // If the initial module is not SCORM, deactivate the API immediately.
    // The API objects persist so they can be re-activated by resetScormApi() during AJAX navigation.
    @if (!$isScorm)
    if (typeof deactivateScormApi === 'function') {
        deactivateScormApi();
    }
    @endif
</script>

<script type="text/javascript">
    // beforeunload — fire-and-forget commit via sendBeacon
    // Registered unconditionally: with AJAX navigation, isScorm can change dynamically.
    window.addEventListener('beforeunload', function() {
        if (typeof do_commit_beacon === 'function') {
            do_commit_beacon();
        }
    });

    // User-presence recorder — ping every 5 minutes
    (function() {
        let presenceUrl = '{{ $presenceUrl }}';
        setInterval(function() {
            let xhr = new XMLHttpRequest();
            xhr.open('GET', presenceUrl, true);
            xhr.send();
        }, 300000); // 5 minutes
    })();
</script>
@endpush
