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
    <link rel="stylesheet" type="text/css" href="{{ $urlAppend }}template/modern/css/bootstrap.min.css"/>

    <!-- new link for input icon -->
    <!-- Font Awesome - A font of icons -->
    <link href="{{ $urlAppend }}template/modern/css/font-awesome-6.4.0/css/all.css" rel="stylesheet"/>

    
    <!-- Font Manrope -->
    <link href="{{ $urlAppend }}template/modern/css/font-Manrope/css/Manrope.css" rel="stylesheet"/>

    <!-- fullcalendar v3.10.2-->
    <link rel="stylesheet" type="text/css" href="{{ $urlAppend }}js/fullcalendar/fullcalendar.css"/>

    <!-- DataTables 1.10.19 version -->
    <link rel="stylesheet" href="{{ $urlAppend }}template/modern/css/jquery.dataTables.min.css"/>

    <!-- Owl-carousel -->
    <link rel="stylesheet" href="{{ $urlAppend }}template/modern/css/owl-carousel.css"/>
    <link rel="stylesheet" href="{{ $urlAppend }}template/modern/css/owl-theme-default.css"/>

    <!-- Our css modern if we need it -->
    <link rel="stylesheet" type="text/css" href="{{ $urlAppend }}template/modern/css/slick.css"/>
    <link rel="stylesheet" type="text/css" href="{{ $urlAppend }}template/modern/css/slick-theme.css"/>
    <link rel="stylesheet" type="text/css" href="{{ $urlAppend }}template/modern/css/sidebar.css"/>
    <link rel="stylesheet" type="text/css" href="{{ $urlAppend }}template/modern/css/new_calendar.css"/>
    <link rel="stylesheet" type="text/css" href="{{ $urlAppend }}template/modern/css/default.css"/>

    <link rel="stylesheet" href="{{ $urlAppend }}template/modern/css/bootstrap.min.css?donotcache"/>
    <link rel="stylesheet" href="{{ $urlAppend }}template/modern/css/sidebar.css?donotcache"/>
    <link rel="stylesheet" href="{{ $urlAppend }}template/modern/css/new_calendar.css?donotcache"/>
    <link rel="stylesheet" href="{{ $urlAppend }}template/modern/css/default.css?donotcache"/>

    @if((get_config('mentoring_platform') and $mentoring_platform) or (get_config('mentoring_always_active')))
        <link rel='stylesheet' type='text/css' href="{{ $urlAppend }}js/datatables/media/css/override_jquery.dataTables.css" />
        <link rel='stylesheet' type='text/css' href="{{ $urlAppend }}template/modern/css/mentoringCss.css" />
        <link rel="stylesheet" href="{{ $urlAppend }}template/modern/css/mentoringCss.css?donotcache">
        
    @endif

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
    <script src="{{ $urlAppend }}js/bootbox/bootbox.min.js"></script>
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
    <script type="text/javascript" src="{{ $urlAppend }}js/sidebar_slider_action.js"></script>

    {!! $head_content !!}

    @stack('head_scripts')

    @if(!isset($course_code) && !$course_code)
        <link rel="stylesheet" type="text/css" href="{{ $urlAppend }}template/modern/css/outsideFromCourse.css"/>
        <link rel="stylesheet" href="{{ $urlAppend }}template/modern/css/outsideFromCourse.css?donotcache">
    @endif

</head>

<body>


    <div class="ContentEclass d-flex flex-column min-vh-100">

        <!-- Desktop navbar -->
        <div class="d-none d-lg-block">
            @if(get_config('mentoring_platform') and $mentoring_platform and !get_config('mentoring_always_active'))
                @include('modules.mentoring.layouts.navheadDesktopMentoring')
            @elseif(get_config('mentoring_platform') and $mentoring_platform and get_config('mentoring_always_active'))
                @include('modules.mentoring.layouts.navheadDesktopMentoring')
            @else
                @include('layouts.partials.navheadDesktop',['logo_img' => $logo_img])
            @endif
        </div>

        <!-- Mobile navbar -->
        <div class="d-block d-lg-none">
            @if(get_config('mentoring_platform') and $mentoring_platform and !get_config('mentoring_always_active'))
                @include('modules.mentoring.layouts.navheadMobileMentoring',['logo_img_small' => $logo_img_small])
                @include('modules.mentoring.common.common_menu_list_mobile')
            @elseif(get_config('mentoring_platform') and $mentoring_platform and get_config('mentoring_always_active'))
                @include('modules.mentoring.layouts.navheadMobileMentoring',['logo_img_small' => $logo_img_small])
                @include('modules.mentoring.common.common_menu_list_mobile')
            @else
                @include('layouts.partials.navheadMobile',['logo_img_small' => $logo_img_small])
                @include('layouts.partials.off-canvas-mobile-header')
            @endif
        </div>

        @yield('content')

        <!-- Desktop navbar -->
        <div class="d-none d-lg-block">
            @include('layouts.partials.footerDesktop')
        </div>

        <!-- Mobile navbar -->
        <div class="d-block d-lg-none">
            @include('layouts.partials.footerMobile')
        </div>
    </div>

 </body>
</html>
