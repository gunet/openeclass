<!DOCTYPE HTML>
<html lang="{{ $language }}">
<head>

    <meta http-equiv="Content-Type" content="text/html; charset=utf-8">

    <title>{{ $pageTitle }}</title>

    <meta charset="UTF-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <!-- Favicon for various devices -->
    <link rel="shortcut icon" href="{{ $urlAppend }}template/modern/favicon/favicon.ico" />
    <link rel="apple-touch-icon-precomposed" href="{{ $urlAppend }}template/modern/favicon/openeclass_128x128.png" />
    <link rel="icon" type="image/png" href="{{ $urlAppend }}template/modern/favicon/openeclass_128x128.png" />

    <!-- Bootstrap v5 -->
    <link rel="stylesheet" type="text/css" href="{{ $urlAppend }}template/modern/css/bootstrap.min.css?v=4.0-dev"/>

    <!-- new link for input icon -->
    <!-- Font Awesome - A font of icons -->
    <link href="{{ $urlAppend }}template/modern/css/font-awesome-6.4.0/css/all.css?v=4.0-dev" rel="stylesheet"/>

    
    <!-- Font Manrope -->
    <link href="{{ $urlAppend }}template/modern/css/font-Manrope/css/Manrope.css?v=4.0-dev" rel="stylesheet"/>

    <!-- fullcalendar v3.10.2-->
    <link rel="stylesheet" type="text/css" href="{{ $urlAppend }}js/fullcalendar/fullcalendar.css?v=4.0-dev"/>

    <!-- DataTables 1.10.19 version -->
    <link rel="stylesheet" href="{{ $urlAppend }}template/modern/css/jquery.dataTables.min.css?v=4.0-dev"/>

    <!-- Owl-carousel -->
    <link rel="stylesheet" href="{{ $urlAppend }}template/modern/css/owl-carousel.css?v=4.0-dev"/>
    <link rel="stylesheet" href="{{ $urlAppend }}template/modern/css/owl-theme-default.css?v=4.0-dev"/>

    <!-- Our css modern if we need it -->
    <link rel="stylesheet" type="text/css" href="{{ $urlAppend }}template/modern/css/slick.css?v=4.0-dev"/>
    <link rel="stylesheet" type="text/css" href="{{ $urlAppend }}template/modern/css/slick-theme.css?v=4.0-dev"/>
    <link rel="stylesheet" type="text/css" href="{{ $urlAppend }}template/modern/css/sidebar.css?<?php echo time(); ?>"/>
    <link rel="stylesheet" type="text/css" href="{{ $urlAppend }}template/modern/css/new_calendar.css?<?php echo time(); ?>"/>
    <link rel="stylesheet" type="text/css" href="{{ $urlAppend }}template/modern/css/default.css?<?php echo time(); ?>"/>

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
    <script type="text/javascript" src="{{ $urlAppend }}js/bootstrap.bundle.min.js"></script>

    <!-- DataTables v1.10.19 and Checkitor v11.0.1 js-->
    <script src="{{ $urlAppend }}js/jquery.dataTables.min.js"></script>
    <script src="{{ $urlAppend }}js/classic-ckeditor.js"></script>

    <!-- Bootbox -->
    <!-- <script src="{{ $urlAppend }}js/bootbox/bootbox.min.js"></script> -->
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
                $.blockUI({ message: "<h4><span class='fa fa-refresh fa-spin'></span> {{ trans('langPleaseWait') }}</h4>" });
            });
        });
    </script>

    <script>
        bootbox.setDefaults({
            locale: "{{ $language }}"
        });
    </script>

    <!-- owl-carousel js -->
    <script src="{{ $urlAppend }}js/owl-carousel.min.js"></script>


    <!-- Our js modern -->
    <script type="text/javascript" src="{{ $urlAppend }}js/slick.min.js"></script>
    <script type="text/javascript" src="{{ $urlAppend }}js/custom.js"></script>
    <script type="text/javascript" src="{{ $urlAppend }}js/viewStudentTeacher.js"></script>
    <script type="text/javascript" src="{{ $urlAppend }}js/sidebar_slider_action.js?<?php echo time(); ?>"></script>
    <script type="text/javascript" src="{{ $urlAppend }}js/notification_bar.js?<?php echo time(); ?>"></script>

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

        <!-- important announcement -->
        @if($pinned_announce_id > 0 && !empty($pinned_announce_title) && !empty($pinned_announce_body))
            @if(!isset($_COOKIE['CookieNotification']))
                <div class="notification-top-bar d-flex justify-content-center align-items-center px-3">
                    <div class='{{ $container }} padding-default'>
                        <div class='d-flex justify-content-center align-items-center gap-2'>
                            <button class='btn hide-notification-bar' id='closeNotificationBar' data-bs-toggle='tooltip' data-bs-placement='bottom' title="{{ trans('langDontDisplayAgain') }}">
                                <span class='fa-solid fa-xmark link-delete fa-md h-auto w-auto me-1'></span>
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
        
        <!-- Header -->
        @include('layouts.partials.navheadDesktop',['logo_img' => $logo_img])

         <!-- Main content -->
        @yield('content')

        <!-- Footer -->
        @include('layouts.partials.footerDesktop')
    </div>


    @if(isset($_SESSION['uid']) && get_config('enable_quick_note'))
        <!-- Quick note -->
        <a type="button" class="btn btn-quick-note submitAdminBtnDefault" data-bs-toggle="modal" href="#quickNote">
            <span class="fa-solid fa-paperclip" data-bs-toggle='tooltip' 
                    data-bs-placement='bottom' data-bs-title="{{ trans('langQuickNotesSide') }}"></span>
        </a>

        <div class="modal fade" id="quickNote" tabindex="-1" aria-labelledby="quickNotelLabel" aria-hidden="true">
            <div class="modal-dialog">
                <div class="modal-content">
                    <div class="modal-header">
                        <div class='modal-title'>
                            <div class='icon-modal-default'><i class='fa-solid fa-cloud-arrow-up fa-xl Neutral-500-cl'></i></div>
                            <h3 class='modal-title-default text-center mb-0'>{{ trans('langQuickNotesSide') }}</h3>
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

 </body>
</html>
