<!DOCTYPE HTML>
<html lang="{{ $language }}">
<head>

    <meta http-equiv="Content-Type" content="text/html; charset=utf-8">

    <title>{{ $pageTitle }}</title>

    <meta charset="UTF-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    {{-- Favicon for various devices --}}
    <link rel="shortcut icon" href="{{ $favicon_img }}" />
    <link rel="apple-touch-icon-precomposed" href="{{ $favicon_img }}" />
    <link rel="icon" type="image/png" href="{{ $favicon_img }}" />

    {{-- Bootstrap v5 --}}
    <link rel="stylesheet" type="text/css" href="{{ $urlAppend }}template/modern/css/bootstrap.min.css?v={{ $cache_suffix }}"/>

    {{-- new link for input icon --}}
    {{-- Font Awesome - A font of icons --}}
    <link href="{{ $urlAppend }}template/modern/css/font-awesome-6.4.0/css/all.css?v={{ $cache_suffix }}" rel="stylesheet"/>

    {{-- Bundled fonts --}}
    <link href="{{ $urlAppend }}template/modern/css/fonts_all/typography.css?v={{ $cache_suffix }}" rel="stylesheet"/>

    {{-- fullcalendar v3.10.2--}}
    <link rel="stylesheet" type="text/css" href="{{ $urlAppend }}js/fullcalendar/fullcalendar.css?v={{ $cache_suffix }}"/>

    {{-- Owl-carousel --}}
    <link rel="stylesheet" href="{{ $urlAppend }}template/modern/css/owl-carousel.css?v={{ $cache_suffix }}"/>
    <link rel="stylesheet" href="{{ $urlAppend }}template/modern/css/owl-theme-default.css?v={{ $cache_suffix }}"/>

    {{-- Our css modern if we need it --}}
    <link rel="stylesheet" type="text/css" href="{{ $urlAppend }}template/modern/css/slick.css?v={{ $cache_suffix }}"/>
    <link rel="stylesheet" type="text/css" href="{{ $urlAppend }}template/modern/css/slick-theme.css?v={{ $cache_suffix }}"/>
    <link rel="stylesheet" type="text/css" href="{{ $urlAppend }}template/modern/css/sidebar.css?v={{ $cache_suffix }}"/>
    <link rel="stylesheet" type="text/css" href="{{ $urlAppend }}template/modern/css/new_calendar.css?v={{ $cache_suffix }}"/>
    <link rel="stylesheet" type="text/css" href="{{ $urlAppend }}template/modern/css/default.css?v={{ $cache_suffix }}"/>

    @stack('head_styles')

    {{-- jQuery --}}
    <script type="text/javascript" src="{{ $urlAppend }}js/jquery-3.6.0.min.js"></script>
    {{-- Bootstrap v5 js --}}
    <script type="text/javascript" src="{{ $urlAppend }}js/bootstrap.bundle.min.js?v={{ $cache_suffix }}"></script>
    {{--  CK Editor v11.0.1 --}}
    <script src="{{ $urlAppend }}js/classic-ckeditor.js"></script>
    {{-- Bootbox --}}
    <script src="{{ $urlAppend }}js/bootbox/bootboxV6.min.js"></script>
    {{-- SlimScroll --}}
    <script src="{{ $urlAppend }}js/jquery.slimscroll.min.js"></script>
    {{-- BlockUI --}}
    <script src="{{ $urlAppend }}js/blockui-master/jquery.blockUI.js"></script>
    {{-- Tinymce --}}
    <script src="{{ $urlAppend }}js/tinymce/tinymce.min.js"></script>
    {{-- Screenfull --}}
    <script src="{{ $urlAppend }}js/screenfull/screenfull.min.js"></script>
    {{-- cLICKbOARD --}}
    <script src="{{ $urlAppend }}js/clipboard.js/clipboard.min.js"></script>
    {{-- fullcalendar v3.10.2 and moment v 2.29.1--}}
    <script src="{{ $urlAppend }}js/fullcalendar/moment.min.js"></script>
    <script src="{{ $urlAppend }}js/fullcalendar/fullcalendar.min.js"></script>
    <script src="{{ $urlAppend }}js/fullcalendar/locales/fullcalendar.{{ $language }}.js"></script>

    <script>
        $(function() {
            $('.blockUI').click(function() {
                $.blockUI({ message: "<div class='card'><h4><span class='fa fa-refresh fa-spin'></span> {{ trans('langPleaseWait') }}</h4></div>" });
            });
        });
    </script>

    <script>
        bootbox.setDefaults({
            locale: "{{ $language }}"
        });
        var notificationsCourses = { getNotifications: '{{ $urlAppend }}main/notifications.php' };
    </script>

    {{-- owl-carousel js --}}
    <script src="{{ $urlAppend }}js/owl-carousel.min.js"></script>

    {{-- Our js modern --}}
    <script type="text/javascript" src="{{ $urlAppend }}js/slick.min.js"></script>
    <script type="text/javascript" src="{{ $urlAppend }}js/custom.js?v={{ $cache_suffix }}"></script>
    <script type="text/javascript" src="{{ $urlAppend }}js/viewStudentTeacher.js?v={{ $cache_suffix }}"></script>
    <script type="text/javascript" src="{{ $urlAppend }}js/sidebar_slider_action.js?v={{ $cache_suffix }}"></script>

    {{-- This script below runs before any datatables are initialized --}}
    <script>
        $(document).ready(function() {
            $(document).on('init.dt', function(e, settings) {
                $('.dt-paging nav').attr('aria-label', '{{ js_escape(trans('langPagination')) }}');
                $('li.dt-paging-button button.first').attr('aria-label', '{{ js_escape(trans('langDtFirstPage')) }}');
                $('li.dt-paging-button button.previous').attr('aria-label', '{{ js_escape(trans('langDtPrevPage')) }}');
                $('li.dt-paging-button button.next').attr('aria-label', '{{ js_escape(trans('langDtNextPage')) }}');
                $('li.dt-paging-button button.last').attr('aria-label', '{{ js_escape(trans('langDtLastPage')) }}');
            });
        });
    </script>

    {!! $head_content !!}

    @stack('head_scripts')

    @if (get_config('ext_analytics_enabled') and get_config('ext_analytics_code'))
        {!! get_config('ext_analytics_code') !!}
    @endif

    @if (get_config('ext_userway_code') and get_config('ext_userway_enabled'))
        {!! get_config('ext_userway_code') !!}
    @endif

    @if (file_exists('js/mathjax/tex-chtml.js'))
        <script type="text/javascript" id="MathJax-script" async src="{{ $urlAppend }}js/mathjax/tex-chtml.js"></script>
    @endif

    {{-- Override the default.css and all .css files from load_js function with the currect theme.css file --}}
    @if ($theme_id && file_exists($theme_css))
        <link rel="stylesheet" type="text/css" href="{{ $urlAppend }}courses/theme_data/{{ $theme_id }}/style_str.css?v={{ $cache_suffix }}"/>
    @endif

    <script>
        $(function() {
            $(document).on('click', '.action-button-dropdown', function() {
                $(this).find('.fa-gear').removeClass('fa-gear').addClass('fa-chevron-left');
                if (!$(this).hasClass('show')) {
                    $(this).find('.fa-chevron-left').removeClass('fa-chevron-left').addClass('fa-gear');
                }
                $('.action-button-dropdown.show').not(this).each(function() {
                    $(this).dropdown('hide');
                });
                $('.table-responsive').addClass('no-overflow');
                $('.dt-scroll-head').addClass('no-overflow');
                $('.dt-scroll-body').addClass('no-overflow');
            });
            $(document).on('hide.bs.dropdown', '.action-button-dropdown', function() {
                $(this).find('.fa-chevron-left').removeClass('fa-chevron-left').addClass('fa-gear');
                $('.table-responsive').removeClass('no-overflow');
                $('.dt-scroll-head').removeClass('no-overflow');
                $('.dt-scroll-body').removeClass('no-overflow');
            });
        });
    </script>

</head>

<body @if(isset($_SESSION['mobile'])) class='mobile' @endif>
    <div class="ContentEclass d-flex flex-column min-vh-100 @if ($pinned_announce) fixed-announcement @endif">
        @if ($pinned_announce)
            <div class="notification-top-bar d-flex justify-content-center align-items-center px-3">
                <div class='{{ $container }} padding-default'>
                    <div class='d-flex justify-content-center align-items-center gap-2'>
                        <button class='btn hide-notification-bar' id='closeNotificationBar' data-bs-toggle='tooltip' data-bs-placement='bottom' title="{{ trans('langDontDisplayAgain') }}" aria-label="{{ trans('langDontDisplayAgain') }}">
                            <i class='fa-solid fa-xmark link-delete fa-lg me-2'></i>
                        </button>
                        <i class='fa-regular fa-bell fa-xl d-block'></i>
                        <span class='d-inline-block text-truncate TextBold title-announcement' style="max-width: auto;">
                            {{ strip_tags($pinned_announce->title) }}
                        </span>
                        <a class='link-color TextBold msmall-text text-decoration-underline ps-1 text-nowrap' href="{{ $urlAppend }}main/system_announcements.php?an_id={{ $pinned_announce->id }}">{!! trans('langDisplayAnnouncement') !!}</a>
                    </div>
                </div>
            </div>
        @endif

        @if(isset($_SESSION['safe_exam_browser_view']) || !isset($_SESSION['mobile']))
            @include('layouts.partials.navheadDesktop', ['logo_img' => $logo_img])
        @endif

        @yield('content')

        @unless(isset($_SESSION['mobile']) || isset($_SESSION['safe_exam_browser_view']))
            @include('layouts.partials.footerDesktop')
        @endunless
    </div>

    {{-- Quick note button --}}
    @unless(isset($_SESSION['mobile']) || isset($_SESSION['safe_exam_browser_view']))
        @if(isset($_SESSION['uid']) && get_config('enable_quick_note'))
            <a type="button" class="btn btn-quick-note submitAdminBtnDefault" data-bs-toggle="modal" href="#quickNote" aria-label="{{ trans('langQuickNotesSide') }}">
                <span class="fa-solid fa-paperclip" data-bs-toggle='tooltip'
                        data-bs-placement='bottom' data-bs-title="{{ trans('langQuickNotesSide') }}"></span>
            </a>
            <div class="modal fade" id="quickNote" tabindex="-1" aria-hidden="true">
                <div class="modal-dialog">
                    <div class="modal-content">
                        <div class="modal-header">
                            <div class='modal-title'>
                                <div class='icon-modal-default'><i class='fa-solid fa-cloud-arrow-up fa-xl Neutral-500-cl'></i></div>
                                <h2 class='modal-title-default text-center mb-0'>{{ trans('langQuickNotesSide') }}</h2>
                            </div>
                        </div>
                        <div class="modal-body">
                            <div class='form-wrapper form-edit'>
                                <form action='{{ $urlAppend }}main/notes/index.php' method='post'>
                                    <div class="mb-3">
                                        <label for="title-note" class="control-label-notes">{{ trans('langTitle') }}&nbsp;<span class='text-danger'>(*)</span></label>
                                        <input type="text" class="form-control" name='newTitle' id="title-note">
                                    </div>
                                    <div class="mb-3">
                                        <label for="content-note" class="control-label-notes">{{ trans('langContent') }}</label>
                                        <textarea class="form-control" id="content-note" name='newContent'></textarea>
                                    </div>
                                    <div class="mb-5">
                                        <a class='small-text text-decoration-underline' href='{{ $urlAppend }}main/notes/index.php'>{{ trans('langAllNotes') }}</a>
                                    </div>
                                    {!! generate_csrf_token_form_field() !!}
                                    <div class='d-flex justify-content-end align-items-center gap-2 flex-wrap'>
                                        <button type="button" class="btn cancelAdminBtn" data-bs-dismiss="modal">{{ trans('langClose') }}</button>
                                        <button type="submit" class="btn submitAdminBtn" name='submitNote'>{{ trans('langSubmit') }}</button>
                                    </div>
                                </form>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        @endif
    @endunless

    <button class="btnScrollToTop" data-bs-scroll="up" aria-label="{{ trans('langScrollToTop') }}">
        <i class="fa-solid fa-arrow-up-from-bracket"></i>
    </button>
    <script>
        $(function() {

            $('.focusable-alert').focus();

            var inputTreeModal = document.getElementById('dialog-set-value');
            if (inputTreeModal) {
                $('#treeModal').on('hidden.bs.modal', function () {
                    inputTreeModal.focus();
                });
            }
            
            document.addEventListener('keydown', function(event) {
                const activeElement = document.activeElement;
                const modalBootBox = document.querySelector('.bootbox.show');
                const modal = document.querySelector('.modal.show');
                if (activeElement && (activeElement.type === 'checkbox' || activeElement.type === 'radio')) {
                    if (event.key === 'Enter') {
                        event.preventDefault();
                        activeElement.checked = !activeElement.checked;
                        activeElement.dispatchEvent(new Event('change'));
                    }
                }
                if (event.key === 'Escape' || event.key === 'Esc') {
                    if (modalBootBox) {
                        $(modalBootBox).modal('hide');
                    }
                    if (modal) {
                        $(modal).modal('hide');
                    }
                }
            });

            $(".datetimepicker table > thead > tr").find("th.prev").each(function() {
                if ($(this).find('.visually-hidden').length === 0) {
                    $(this).append('<span class="visually-hidden">{{ trans("langPrevious") }}</span>');
                }
            });

            $(".datetimepicker table > thead > tr").find("th.next").each(function() {
                if ($(this).find('.visually-hidden').length === 0) {
                    $(this).append('<span class="visually-hidden">{{ trans("langNext") }}</span>');
                }
            });

            $(".datepicker table > thead > tr").find("th.prev").each(function() {
                if ($(this).find('.visually-hidden').length === 0) {
                    $(this).append('<span class="visually-hidden">{{ trans("langPrevious") }}</span>');
                }
            });

            $(".datepicker table > thead > tr").find("th.next").each(function() {
                if ($(this).find('.visually-hidden').length === 0) {
                    $(this).append('<span class="visually-hidden">{{ trans("langNext") }}</span>');
                }
            });

            if ($("#cboxPrevious").find('.visually-hidden').length === 0) {
                $("#cboxPrevious").append('<span class="visually-hidden">{{ trans("langPrevious") }}</span>');
            }

            if ($("#cboxNext").find('.visually-hidden').length === 0) {
                $("#cboxNext").append('<span class="visually-hidden">{{ trans("langNext") }}</span>');
            }

            if ($("#cboxSlideshow").find('.visually-hidden').length === 0) {
                $("#cboxSlideshow").append('<span class="visually-hidden">{{ trans("langShowTo") }}</span>');
            }

            if ($(".table-default thead tr th:last-child:has(.fa-gears)").find('.visually-hidden').length === 0) {
                $(".table-default thead tr th:last-child:has(.fa-gears)").append('<span class="visually-hidden">{{ trans("langSettingSelect") }}</span>');
            }

            if ($(".table-default thead tr th:last-child:has(.fa-cogs)").find('.visually-hidden').length === 0) {
                $(".table-default thead tr th:last-child:has(.fa-cogs)").append("<span class='visually-hidden'>{{ trans('langSettingSelect') }}</span>");
            }

            if ($(".table-default thead tr th:last-child:not(:has(.fa-gears))").find('.visually-hidden').length === 0) {
                $(".table-default thead tr th:last-child:not(:has(.fa-gears))").append("<span class='visually-hidden'>{{ trans('langSettingSelect') }} / {{ trans('langResults') }}</span>");
            }

            if ($(".table-default thead tr th:last-child:not(:has(.fa-cogs))").find('.visually-hidden').length === 0) {
                $(".table-default thead tr th:last-child:not(:has(.fa-cogs))").append("<span class='visually-hidden'>{{ trans('langSettingSelect') }} / {{ trans('langResults') }}</span>");
            }

            if ($(".sp-input-container .sp-input").find('.visually-hidden').length === 0) {
                $(".sp-input-container .sp-input").append("<span class='visually-hidden'>{{ trans('langOptForColor') }}</span>");
            }

            if ($("ul").find(".select2-search__field").find('.visually-hidden').length === 0) {
                $("ul").find(".select2-search__field").append("<span class='visually-hidden'>{{ trans('langSearch') }}</span>");
            }

            if ($("#cal-slide-content ul li .event-item").find('.visually-hidden').length === 0) {
                $("#cal-slide-content ul li .event-item").append("<span class='visually-hidden'>{{ trans('langEvent') }}</span>");
            }

            if ($("#cal-day-box .event-item").find('.svisually-hidden').length === 0) {
                $("#cal-day-box .event-item").append("<span class='visually-hidden'>{{ trans('langEvent') }}</span>");
            }

            @if ($pinned_announce)
                $('#closeNotificationBar').click(function () {
                    setNewCookie("CookieNotification", "{{ $max_pinned_announce_id }}", 30, "{{ $urlAppend }}");
                    $('.ContentEclass').removeClass('fixed-announcement');
                    $('.notification-top-bar').hide();
                });
            @endif

            document.addEventListener('click', (event) => {
                const dropdownsActionButtons = document.querySelectorAll('.contextual-menu-action-button');
                const dropdownsActionBars = document.querySelectorAll('.contextual-menu-action-bar');
                let isAnyOpenActionButton = false;
                let isAnyOpenActionBar = false;

                dropdownsActionButtons.forEach((dropdownActionButton) => {
                    if (dropdownActionButton.classList.contains('show')) {
                        isAnyOpenActionButton = true;
                    }
                });
                dropdownsActionBars.forEach((dropdownActionBar) => {
                    if (dropdownActionBar.classList.contains('show')) {
                        isAnyOpenActionBar = true;
                    }
                });

                if (isAnyOpenActionButton) {
                    $('.col_maincontent_active').addClass('action-button-on');
                } else {
                    $('.col_maincontent_active').removeClass('action-button-on');
                }

                if (isAnyOpenActionBar) {
                    $('.col_maincontent_active').addClass('action-bar-on');
                } else {
                    $('.col_maincontent_active').removeClass('action-bar-on');
                }
            });

        });
    </script>
    @stack('bottom_scripts')
    @if(isset($_SESSION['uid']) && get_config('enable_idle_detection'))
        <script type="text/javascript">

            (function() {
                let WARNING_TIME = '{{ get_config('idle_warning_time') }}';
                if (WARNING_TIME < 60000) {
                    WARNING_TIME = 60000;
                }
                let LOGOUT_TIME = '{{ get_config('idle_logout_time') }}';
                if (LOGOUT_TIME <  60000) {
                    LOGOUT_TIME = 60000;
                }

                const THROTTLE_WAIT = 2000;

                const sessionToken = '{{ $_SESSION['csrf_token'] ?? '' }}';
                const baseUrl = '{{ $urlAppend }}';
                const LOGOUT_URL = '{{ $urlAppend . 'modules/auth/logout.php' }}';


                let warningTimeout;
                let logoutTimeout;
                let lastActivity = Date.now();
                let isWarningVisible = false;

                const modalHTML = `
                      <div class="modal fade" id="idleWarningModal" tabindex="-1" role="dialog" aria-hidden="true" data-bs-backdrop="static" data-bs-keyboard="false">
                           <div class="modal-dialog modal-dialog-centered" role="document">
                                <div class="modal-content">
                                <div class="modal-header">
                                     <h4 class="modal-title">{{trans('langIdleWarningTitle')}}</h4>
                                </div>
                                <div class="modal-body">
                                     <p>{{trans('langIdleExpireSoon')}}</p>
                                     <p>{{trans('langIdleStayLoggedIn')}}</p>
                                </div>
                                <div class="modal-footer">
                                     <button type="button" class="btn btn-primary" id="extendSessionBtn">{{trans('langIdleExtendSession')}}</button>
                                </div>
                                </div>
                           </div>
                      </div>
                 `;

                document.body.insertAdjacentHTML('beforeend', modalHTML);

                function resetTimers() {
                    if (isWarningVisible) return;
                    clearTimeout(warningTimeout);
                    clearTimeout(logoutTimeout);
                    warningTimeout = setTimeout(showWarning, WARNING_TIME);
                }

                function showWarning() {
                    isWarningVisible = true;

                    if (typeof $ !== 'undefined' && $.fn.modal) {
                        $('#idleWarningModal').modal('show');
                    } else {
                        const modalEl = document.getElementById('idleWarningModal');
                        if (modalEl) {
                            modalEl.style.display = 'block';
                            modalEl.classList.add('show');
                            document.body.classList.add('modal-open');
                            modalEl.style.backgroundColor = 'rgba(0,0,0,0.5)';
                        }
                    }

                    logoutTimeout = setTimeout(forceLogout, LOGOUT_TIME);
                }

                function forceLogout() {
                    document.getElementById('logoutForm').constructor.prototype.submit.call(document.getElementById('logoutForm'));
                }

                document.getElementById('extendSessionBtn').addEventListener('click', function() {
                    isWarningVisible = false;

                    if (typeof $ !== 'undefined' && $.fn.modal) {
                        $('#idleWarningModal').modal('hide');
                    } else {
                        const modalEl = document.getElementById('idleWarningModal');
                        if (modalEl) {
                            modalEl.style.display = 'none';
                            modalEl.classList.remove('show');
                            document.body.classList.remove('modal-open');
                        }
                    }
                    fetch(baseUrl + 'main/portfolio.php', { method: 'HEAD', cache: 'no-store' });
                    lastActivity = Date.now();
                    resetTimers();
                });
                function onUserActivity() {
                    const now = Date.now();
                    if (now - lastActivity > THROTTLE_WAIT) {
                        lastActivity = now;
                        resetTimers();
                    }
                };
                const events = ['mousedown', 'keydown', 'touchstart', 'wheel'];
                events.forEach(event => {
                    document.addEventListener(event, onUserActivity, { passive: true });
                });
                resetTimers();
            })();
        </script>
    @endif
    {{-- Navigation to hierarchy tree using keyboard for accessibility --}}
    <script type="text/javascript">
        (function () {
            $('#js-tree').on(
                'keydown',
                '.jstree-anchor',
                function (event) {
                    if (event.key !== 'Enter') {
                        return;
                    }
                    const $anchor = $(this);
                    const $li = $anchor.closest('li.jstree-node');

                    if ($li.hasClass('jstree-leaf')) {
                        return;
                    }
                    event.preventDefault();
                    event.stopPropagation();

                    const tree = $('#js-tree').jstree(true);
                    const nodeId = $li.attr('id');

                    if (tree.is_open(nodeId)) {
                        tree.close_node(nodeId);
                    } else {
                        tree.open_node(nodeId);
                    }
                }
            );
        })();
    </script>
    {{-- Navigation to blog tree using keyboard for accessibility --}}
    <script>
        (function () {
            $('#blog_tree').on(
                'keydown',
                '.jstree-anchor',
                function (event) {
                    if (event.key !== 'Enter') {
                        return;
                    }

                    const $anchor = $(this);
                    const $li = $anchor.closest('li.jstree-node');

                    if ($li.hasClass('jstree-leaf')) {
                        return;
                    }

                    event.preventDefault();
                    event.stopPropagation();

                    const tree = $('#blog_tree').jstree(true);
                    const nodeId = $li.attr('id');

                    if (tree.is_open(nodeId)) {
                        tree.close_node(nodeId);
                    } else {
                        tree.open_node(nodeId);
                    }
                }
            );
        })();
        </script>
        {{-- Add aria-label to the delete button of slimSelect item for accessibility --}}
        <script>
            document.addEventListener('DOMContentLoaded', function () {
                function fixSlimSelectAccessibility() {
                    document.querySelectorAll('.ss-value-delete').forEach(function (element) {
                        element.setAttribute('role', 'button');
                        element.setAttribute(
                            "aria-label",
                            "{{ trans('langDelete') }}"
                        );

                        element.setAttribute(
                            "title",
                            "{{ trans('langDelete') }}"
                        );

                        let svg = element.querySelector('svg');

                        if (svg) {
                            svg.setAttribute(
                                'aria-hidden',
                                'true'
                            );
                        }
                    });
                }

                fixSlimSelectAccessibility();
                const observer = new MutationObserver(function () {
                    fixSlimSelectAccessibility();
                });
                observer.observe(document.body, {
                    childList: true,
                    subtree: true
                });
            });
        </script>
        {{-- Navigation with prev-next buttons in the exercise using keyboard for accessibility --}}
        <script>
            document.addEventListener('DOMContentLoaded', function () {
                document.addEventListener('keydown', function (event) {

                    const focusedButton = document.activeElement;
                    if (!focusedButton.matches || !focusedButton.matches('input.btn-exercise-nav[type="submit"]')) {
                        return;
                    }

                    const buttons = Array.from(
                        document.querySelectorAll(
                            'input.btn-exercise-nav[type="submit"]'
                        )
                    );

                    if (buttons.length === 0) {
                        return;
                    }

                    if (event.key === 'Enter') {
                        event.preventDefault();
                        focusedButton.click();
                        return;
                    }

                    if (event.key === 'ArrowRight') {
                        event.preventDefault();
                        let index = buttons.indexOf(focusedButton);
                        let next = index + 1;
                        if (next >= buttons.length) {
                            next = 0;
                        }
                        buttons[next].focus();
                        return;
                    }

                    if (event.key === 'ArrowLeft') {
                        event.preventDefault();
                        let index = buttons.indexOf(focusedButton);
                        let previous = index - 1;
                        if (previous < 0) {
                            previous = buttons.length - 1;
                        }
                        buttons[previous].focus();
                    }
                });
            });
        </script>
 </body>
</html>
