<!DOCTYPE HTML>
<html lang="{{ $language }}">
<head>

    <meta http-equiv="Content-Type" content="text/html; charset=utf-8">

    <title>{{ $pageTitle }}</title>

    <meta charset="UTF-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <!-- Favicon for various devices -->
    <link rel="shortcut icon" href="{{ $favicon_img }}" />
    <link rel="apple-touch-icon-precomposed" href="{{ $favicon_img }}" />
    <link rel="icon" type="image/png" href="{{ $favicon_img }}" />

    <!-- Bootstrap v5 -->
    <link rel="stylesheet" type="text/css" href="{{ $urlAppend }}template/modern/css/bootstrap.min.css?v={{ $cache_suffix }}"/>

    <!-- new link for input icon -->
    <!-- Font Awesome - A font of icons -->
    <link href="{{ $urlAppend }}template/modern/css/font-awesome-6.4.0/css/all.css?v={{ $cache_suffix }}" rel="stylesheet"/>

    <!-- Font Manrope -->
    <link href="{{ $urlAppend }}template/modern/css/font-Manrope/css/Manrope.css?v={{ $cache_suffix }}" rel="stylesheet"/>

    <!-- fullcalendar v3.10.2-->
    <link rel="stylesheet" type="text/css" href="{{ $urlAppend }}js/fullcalendar/fullcalendar.css?v={{ $cache_suffix }}"/>

    <!-- DataTables 1.10.19 version -->
    <link rel="stylesheet" href="{{ $urlAppend }}template/modern/css/jquery.dataTables.min.css?v={{ $cache_suffix }}"/>

    <!-- Owl-carousel -->
    <link rel="stylesheet" href="{{ $urlAppend }}template/modern/css/owl-carousel.css?v={{ $cache_suffix }}"/>
    <link rel="stylesheet" href="{{ $urlAppend }}template/modern/css/owl-theme-default.css?v={{ $cache_suffix }}"/>

    <!-- Our css modern if we need it -->
    <link rel="stylesheet" type="text/css" href="{{ $urlAppend }}template/modern/css/slick.css?v={{ $cache_suffix }}"/>
    <link rel="stylesheet" type="text/css" href="{{ $urlAppend }}template/modern/css/slick-theme.css?v={{ $cache_suffix }}"/>
    <link rel="stylesheet" type="text/css" href="{{ $urlAppend }}template/modern/css/sidebar.css?v={{ $cache_suffix }}"/>
    <link rel="stylesheet" type="text/css" href="{{ $urlAppend }}template/modern/css/new_calendar.css?v={{ $cache_suffix }}"/>
    <link rel="stylesheet" type="text/css" href="{{ $urlAppend }}template/modern/css/default.css?v={{ $cache_suffix }}"/>

    <!-- if change eclass theme then put styles css of this theme -->
    @if (isset($styles_str) && $styles_str)
    <style>
        {!! $styles_str !!}
    </style>
    @endif
    @stack('head_styles')


    <!-- jQuery -->
    <script type="text/javascript" src="{{ $urlAppend }}js/jquery-3.6.0.min.js"></script>

    <!-- Bootstrap v5 js -->
    <script type="text/javascript" src="{{ $urlAppend }}js/bootstrap.bundle.min.js?v={{ $cache_suffix }}"></script>

    <!-- DataTables v1.10.19 and Checkitor v11.0.1 js-->
    <script src="{{ $urlAppend }}js/jquery.dataTables.min.js"></script>
    <script src="{{ $urlAppend }}js/classic-ckeditor.js"></script>

    <!-- Bootbox -->
    <script src="{{ $urlAppend }}js/bootbox/bootboxV6.min.js"></script>
    <!-- SlimScroll -->
    <script src="{{ $urlAppend }}js/jquery.slimscroll.min.js"></script>
    <!-- BlockUI -->
    <script src="{{ $urlAppend }}js/blockui-master/jquery.blockUI.js"></script>
    <!-- Tinymce -->
    <script src="{{ $urlAppend }}js/tinymce/tinymce.min.js"></script>
    <!-- Screenfull -->
    <script src="{{ $urlAppend }}js/screenfull/screenfull.min.js"></script>
    <!-- cLICKbOARD -->
    <script src="{{ $urlAppend }}js/clipboard.js/clipboard.min.js"></script>
    <!-- fullcalendar v3.10.2 and moment v 2.29.1-->
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
        var notificationsCourses = { getNotifications: '{{ $urlAppend }}main/ajax_sidebar.php' };
    </script>

    <!-- owl-carousel js -->
    <script src="{{ $urlAppend }}js/owl-carousel.min.js"></script>


    <!-- Our js modern -->
    <script type="text/javascript" src="{{ $urlAppend }}js/slick.min.js"></script>
    <script type="text/javascript" src="{{ $urlAppend }}js/custom.js?v={{ $cache_suffix }}"></script>
    <script type="text/javascript" src="{{ $urlAppend }}js/viewStudentTeacher.js?v={{ $cache_suffix }}"></script>
    <script type="text/javascript" src="{{ $urlAppend }}js/sidebar_slider_action.js?v={{ $cache_suffix }}"></script>
    <script type="text/javascript" src="{{ $urlAppend }}js/notification_bar.js?v={{ $cache_suffix }}"></script>

    {!! $head_content !!}

    @stack('head_scripts')

    @if (get_config('ext_analytics_enabled') and get_config('ext_analytics_code'))
        {!! get_config('ext_analytics_code') !!}
    @endif

    @if (get_config('ext_userway_code') and get_config('ext_userway_enabled'))
        {!! get_config('ext_userway_code') !!}
    @endif

    @if (file_exists('node_modules/mathjax/es5/tex-chtml.js'))
        <script type="text/javascript" id="MathJax-script" async src="{{ $urlAppend }}node_modules/mathjax/es5/tex-chtml.js"></script>
    @endif

</head>

<body>
    <div class="ContentEclass d-flex flex-column min-vh-100 @if($pinned_announce_id > 0 && !isset($_COOKIE['CookieNotification'])) fixed-announcement @endif">
        @if($pinned_announce_id > 0 && !empty($pinned_announce_title) && !empty($pinned_announce_body))
            @if(!isset($_COOKIE['CookieNotification']))
                <div class="notification-top-bar d-flex justify-content-center align-items-center px-3">
                    <div class='{{ $container }} padding-default'>
                        <div class='d-flex justify-content-center align-items-center gap-2'>
                            <button class='btn hide-notification-bar' id='closeNotificationBar' data-bs-toggle='tooltip' data-bs-placement='bottom' title="{{ trans('langDontDisplayAgain') }}" aria-label="{{ trans('langDontDisplayAgain') }}">
                                <i class='fa-solid fa-xmark link-delete fa-lg me-2'></i>
                            </button>
                            <i class='fa-regular fa-bell fa-xl d-block'></i>
                            <span class='d-inline-block text-truncate TextBold title-announcement' style="max-width: auto;">
                                @php echo strip_tags($pinned_announce_title); @endphp
                            </span>
                            <a class='link-color TextBold msmall-text text-decoration-underline ps-1' href="{{ $urlAppend }}main/system_announcements.php?an_id={{ $pinned_announce_id }}">{!! trans('langDisplayAnnouncement') !!}</a>
                        </div>
                    </div>
                </div>
            @endif
        @endif
        @include('layouts.partials.navheadDesktop',['logo_img' => $logo_img])
        <main id="main">@yield('content')</main>
        @include('layouts.partials.footerDesktop')
    </div>
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
                            <div class='modal-title-default text-center mb-0'>{{ trans('langQuickNotesSide') }}</div>
                        </div>
                    </div>
                    <div class="modal-body">
                        <div class='form-wrapper form-edit'>
                            <form action='{{ $urlAppend }}main/notes/index.php' method='post'>
                                <div class="mb-3">
                                    <label for="title-note" class="control-label-notes">{{ trans('langTitle') }}&nbsp<span class='text-danger'>(*)</span></label>
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
    <button class="btnScrollToTop" data-bs-scroll="up">
        <i class="fa-solid fa-arrow-up-from-bracket"></i>
    </button>
    <script>
        $(function() {
            $(".datetimepicker table > thead > tr").find("th.prev").each(function() {
                $(this).attr("aria-label", "{{ trans('langPrevious') }}");
            });
            $(".datetimepicker table > thead > tr").find("th.next").each(function() {
                $(this).attr("aria-label", "{{ trans('langNext') }}");
            });
            $(".datepicker table > thead > tr").find("th.prev").each(function() {
                $(this).attr("aria-label", "{{ trans('langPrevious') }}");
            });
            $(".datepicker table > thead > tr").find("th.next").each(function() {
                $(this).attr("aria-label", "{{ trans('langNext') }}");
            });
            $("#cboxPrevious").attr("aria-label","{{ trans('langPrevious') }}");
            $("#cboxNext").attr("aria-label","{{ trans('langNext') }}");
            $("#cboxSlideshow").attr("aria-label","{{ trans('langShowTo') }}");
            $(".table-default thead tr th:last-child:has(.fa-gears)").attr("aria-label","{{ trans('langCommands') }}");
            $(".table-default thead tr th:last-child:has(.fa-cogs)").attr("aria-label","{{ trans('langCommands') }}");
            $(".table-default thead tr th:last-child:not(:has(.fa-gears))").attr("aria-label","{{ trans('langCommands') }} / {{ trans('langResults') }}");
            $(".table-default thead tr th:last-child:not(:has(.fa-cogs))").attr("aria-label","{{ trans('langCommands') }} / {{ trans('langResults') }}");
            $(".sp-input-container .sp-input").attr("aria-label","{{ trans('langOptForColor') }}");
            $("ul").find(".select2-search__field").attr("aria-label","{{ trans('langSearch') }}");
            $("#cal-slide-content ul li .event-item").attr("aria-label","{{ trans('langEvent') }}");
            $("#cal-day-box .event-item").attr("aria-label","{{ trans('langEvent') }}");
        });
    </script>
    @stack('bottom_scripts')
 </body>
</html>
