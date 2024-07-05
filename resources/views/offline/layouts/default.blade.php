<!DOCTYPE HTML>
<html lang="{{ $language }}">
<head>

    <meta http-equiv="Content-Type" content="text/html; charset=utf-8">

    <title>{{ $pageTitle }}</title>

    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <!-- Favicon for various devices -->
    <link rel="shortcut icon" href="{{ $urlAppend }}template/favicon/favicon.ico" />
    <link rel="apple-touch-icon-precomposed" href="{{ $urlAppend }}template/favicon/openeclass_128x128.png" />
    <link rel="icon" type="image/png" href="{{ $urlAppend }}template/favicon/openeclass_128x128.png" />

    <!-- Latest compiled and minified CSS -->
    <link rel="stylesheet" href="{{ $template_base }}/css/bootstrap.min.css?v={{ $eclass_version }}">

    <!-- Font Awesome - A font of icons -->
    {{--<link href="{{ $template_base }}/css/font-awesome/css/font-awesome.css" rel="stylesheet">--}}
    {{--Template modification between default and 3.6--}}
    <link href="{{ $template_base }}/css/font-awesome-6.4.0/css/font-awesome.css" rel="stylesheet">

    @if (isset($styles_str) && $styles_str)
        <style>
            {!! $styles_str !!}
        </style>
    @endif

    @stack('head_styles')

    <!-- jQuery -->
    <script type="text/javascript" src="{{ $urlAppend }}js/jquery-3.6.0.min.js"></script>

    <!-- Latest compiled and minified JavaScript -->
    <script src="{{ $template_base }}/js/bootstrap.min.js?v={{ $eclass_version }}"></script>

     <!-- BootBox -->
    <script src="{{ $urlAppend }}js/bootbox/bootbox.min.js?v={{ $eclass_version }}"></script>

    <!-- SlimScroll -->
    <script src="{{ $urlAppend }}js/jquery.slimscroll.min.js"></script>
    <!-- BlockUI -->
    <script src="{{ $urlAppend }}js/blockui-master/jquery.blockUI.js"></script>
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
    var sidebarConfig = { notesLink: '{{ $urlAppend }}main/notes/index.php', messagesLink: '{{ $urlAppend }}main/ajax_sidebar.php', courseCode: '{{ isset($course_code) ? $course_code : ""}}', courseId: '{{ isset($course_id) ? $course_id : "" }}', note_fail_messge: '{!! trans("langFieldsRequ") !!}' };
    </script>
    <!-- Our javascript -->
    <script type="text/javascript" src="{{ $template_base }}/js/main.js?v={{ $eclass_version }}"></script>
    {!! $head_content !!}
    @stack('head_scripts')

</head>

<body>
    @if (!$is_mobile)
        <div class="{{ $container }} header_container">
            <div class="row" id="header_section">
                <div id="bgr-cheat-header" class="hidden-xs hidden-sm col-md-2"></div>
                <div class="col-xs-12 col-sm-12 col-md-10 nav-container">
                    <nav id="header" class="navbar navbar-default" role="navigation">
                        <button class="navbar-toggle pull-left">
                            <span class="fa fa-bars" style='color: #777;'></span>
                            <span class="sr-only">$langMenu</span>
                        </button>
                        <a href='{{ $urlAppend }}' class="navbar-brand small-logo">
                            <img class="img-responsive hidden-md hidden-lg" src="{{ $logo_img_small }}" style="height: 36px;margin-top:8px;" alt='{{ $pageTitle }} logo'>
                        </a>
                    </nav>
                </div>
            </div>
        </div>
    @endif

        <!-- LeftNav & Main Content Section -->

        <div class="{{ $container }} body_container">
        <div class="row" id="Frame">
        @if (!$is_mobile)
            <div id="background-cheat-leftnav" class="hidden-xs hidden-sm col-md-2 pull-left"></div>
            <div id="background-cheat" class="col-xs-12 col-sm-12 col-md-10 pull-right"></div>
            <div id="main-content" class="col-xs-12 col-sm-12 col-md-10 col-md-push-2">
        @else
            <div id="background-cheat" class="col-xs-12"></div>
            <div id="main-content" class="col-xs-12">
        @endif
                <div class="row row-main">
                    <div class="col-md-12 add-gutter">
                        @include('layouts.common.breadcrumbs')
                        <div class="row title-row margin-top-thin">
                            <div class="col-xs-9">
                                <h1 class='page-title'>
                                    <a href='{{ $urlAppend }}index.html'>{!! $section_title !!}</a>
                                </h1>
                                <h2 class='page-subtitle'>{{ $professor }}</h2>
                                <h6 class='help-block'>{{ $course_date }}</h6>
                                @if (!defined('HIDE_TOOL_TITLE'))
                                    <div class='row'>
                                        <div class='col-md-12'>
                                            <h2 class='page-subtitle'>
                                                {{ $toolName }}
                                            </h2>
                                        </div>
                                    </div>
                                @endif
                            </div>
                        </div>
                        @yield('content')
                    </div>
                </div>
            </div>

            <div id="leftnav" class="col-md-2 col-xs-pull-10 col-sm-pull-10 col-md-pull-10 sidebar float-menu">
                <div class="logo">
                    <a href='{{ $urlAppend }}index.html'>
                    <img class="img-responsive hidden-xs hidden-sm" src="{{ $logo_img }}" alt='{{ $pageTitle }} logo'>
                    </a>
                </div>

                <div class="panel-group" id="sidebar-accordion">
                    <div class="panel">
                        @foreach ($toolArr as $key => $tool_group)
                            <a class="collapsed parent-menu" data-bs-toggle="collapse" data-bs-parent="#sidebar-accordion">
                                <div class="panel-heading">
                                    <div class="panel-title h3">
                                        <span class="fa-solid fa-chevron-right"></span>
                                        <span>{{ $tool_group[0]['text'] }}</span>
                                    </div>
                                </div>
                            </a>
                            @foreach ($tool_group[1] as $key2 => $tool)
                                <a href="{{ $tool_group[2][$key2] }}" class="list-group-item {{ module_path($tool_group[2][$key2]) == $current_module_dir ? " active" : ""}}" {{ is_external_link($tool_group[2][$key2]) || $tool_group[3][$key2] == 'fa-external-link' ? ' target="_blank"' : ""}}>
                                    <span class="fa {{ $tool_group[3][$key2] }} fa-fw"></span>
                                    <span>{!! $tool !!}</span>
                                </a>
                            @endforeach

                        @endforeach
                    </div>
                    {{ isset($eclass_leftnav_extras) ? $eclass_leftnav_extras : "" }}
                </div>
            </div>

            <!-- END sideBarBlock -->
        </div>

        <!-- BEGIN footerBlock -->
        <div class="row" id="footer_section">
        @if (!$is_mobile)
            <!-- BEGIN normalViewOpenDiv -->
            <div id="bgr-cheat-footer" class="hidden-xs hidden-sm col-md-2"></div>
            <div class="col-xs-12 col-sm-12 col-md-10">
            <!-- END normalViewOpenDiv -->
        @else
            <!-- BEGIN mobileViewOpenDiv -->
            <div class="col-xs-12">
            <!-- END mobileViewOpenDiv -->
        @endif
                <div id="scrollToTop">
                    <span class='fa fa-caret-square-o-up fa-2x'></span>
                </div>
                <footer class="footer">
                    <span><a href='{{ $urlAppend }}info/copyright.php'>Open eClass © {{ date('Y') }}</a> &mdash; <a href="{{ $urlAppend }}info/terms.php">{{ trans('langUsageTerms') }}</a></span>
                </footer>
            </div>
        </div>
    </div>

    @stack('footer_scripts')

</body>
</html>

