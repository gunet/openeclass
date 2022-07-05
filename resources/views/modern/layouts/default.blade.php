<!DOCTYPE HTML>
<html lang="{{ $language }}">
<head>

    <meta http-equiv="Content-Type" content="text/html; charset=utf-8">

    <title>{{ $pageTitle }}</title>

    <meta charset="UTF-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <!-- jQuery -->
    <script type="text/javascript" src="{{$urlAppend}}template/modern/js/jquery3-6-0.min.js"></script>


    <!-- Bootstrap v5 -->
    <link rel="stylesheet" type="text/css" href="{{ $urlAppend }}template/modern/css/bootstrap.min.css"/>
    <script type="text/javascript" src="{{$urlAppend}}template/modern/js/bootstrap.bundle.min.js"></script>


    <!-- new link for input icon -->
    <!-- Font Awesome - A font of icons -->
    <link rel="stylesheet" href="{{ $urlAppend }}template/modern/css/all.css">
    <link href="{{ $urlAppend }}template/modern/css/font-awesome-4.7.0/css/font-awesome.css" rel="stylesheet">
    <link rel="shortcut icon" href="{{ $urlAppend }}template/modern/favicon/favicon.ico">


    <!-- DataTables and Checkitor -->
    <link rel="stylesheet" href="https://cdn.datatables.net/1.10.19/css/jquery.dataTables.min.css">
    <script src="https://cdn.datatables.net/1.10.19/js/jquery.dataTables.min.js"></script>
    <script src="https://cdn.ckeditor.com/ckeditor5/11.0.1/classic/ckeditor.js"></script>


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
    <!-- <script src="{{ $urlAppend }}js/clipboard.js/clipboard.min.js"></script> -->

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


     <!-- Our css modern if we need it -->
    <link rel="stylesheet" type="text/css" href="{{ $urlAppend }}template/modern/css/slick.css"/>
    <link rel="stylesheet" type="text/css" href="{{ $urlAppend }}template/modern/css/sidebar.css"/>
    <link rel="stylesheet" type="text/css" href="{{ $urlAppend }}template/modern/css/new_calendar.css"/>
    <link rel="stylesheet" type="text/css" href="{{ $urlAppend }}template/modern/css/slick-theme.css"/>
    <link rel="stylesheet" type="text/css" href="{{ $urlAppend }}template/modern/css/default.css"/>
    <link rel="stylesheet" type="text/css" href="{{ $urlAppend }}template/modern/css/custom.css"/>

    <link rel="stylesheet" href="{{ $urlAppend }}template/modern/css/bootstrap.min.css?donotcache">
    <link rel="stylesheet" href="{{ $urlAppend }}template/modern/css/custom.css?donotcache">
    <link rel="stylesheet" href="{{ $urlAppend }}template/modern/css/sidebar.css?donotcache">
    <link rel="stylesheet" href="{{ $urlAppend }}template/modern/css/new_calendar.css?donotcache">
    <link rel="stylesheet" href="{{ $urlAppend }}template/modern/css/default.css?donotcache">

    <!-- if change eclass theme then put styles css of this theme -->
    @if (isset($styles_str) && $styles_str)
    <style>
        {!! $styles_str !!}
    </style>
    @endif
    @stack('head_styles')

    <!-- Our js modern -->
    <script type="text/javascript" src="{{ $urlAppend }}template/modern/js/slick.min.js"></script>
    <script type="text/javascript" src="{{ $urlAppend }}template/modern/js/custom.js"></script>
    <script type="text/javascript" src="{{ $urlAppend }}template/modern/js/viewStudentTeacher.js"></script>
    <script type="text/javascript" src="{{ $urlAppend }}template/modern/js/sidebar_slider_action.js"></script>

    {!! $head_content !!}

    @stack('head_scripts')

</head>

<body>
    <div class="d-flex flex-column min-vh-100 {{ $container }}">
        <!-- Desktop navbar -->
        <div class="d-none d-sm-block d-sm-none d-md-block d-md-none d-lg-block">
            @include('layouts.partials.navheadDesktop',['logo_img' => $logo_img])
        </div>

        <!-- Mobile navbar -->
        <div class="d-lg-none mr-auto">
            @include('layouts.partials.navheadMobile',['logo_img_small' => $logo_img_small])
        </div>


        @yield('content')


        <!-- Desktop navbar -->
        <div class="d-none d-sm-block d-sm-none d-md-block d-md-none d-lg-block">
            @include('layouts.partials.footerDesktop')
        </div>

        <!-- Mobile navbar -->
        <div class="d-lg-none">
            @include('layouts.partials.footerMobile')
        </div>
    </div>
 </body>
</html>
