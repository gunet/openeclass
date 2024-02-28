<!-- BEGIN mainBlock -->
<!DOCTYPE HTML>
<html style="height: 100%;" lang="{{ $language }}">
<head>

    <meta http-equiv="Content-Type" content="text/html; charset=utf-8">

    <title>{{ $pageTitle }}</title>

    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <!-- jQuery -->
    <script type="text/javascript" src="{{ $urlAppend }}js/jquery-3.6.0.min.js"></script>

    <!-- Latest compiled and minified JavaScript -->
    <!-- <script src="{{ $template_base }}/js/bootstrap.min.js"></script> -->
    <!-- Bootstrap v5 -->
    <link rel="stylesheet" type="text/css" href="{{ $urlAppend }}template/modern/css/bootstrap.min.css"/>
    <script type="text/javascript" src="{{$urlAppend}}js/bootstrap.bundle.min.js"></script>

    <!-- new link for input icon -->
    <!-- Font Awesome - A font of icons -->
    <link rel="stylesheet" href="{{ $urlAppend }}template/modern/css/all.css">
    <link href="{{ $urlAppend }}template/modern/css/font-awesome-4.7.0/css/font-awesome.css" rel="stylesheet">

    <!-- DataTables and Checkitor -->
    <link rel="stylesheet" href="{{ $urlAppend }}template/modern/css/jquery.dataTables.min.css">
    <script src="{{ $urlAppend }}js/jquery.dataTables.min.js"></script>
    <script src="{{ $urlAppend }}js/classic-ckeditor.js"></script>

     <!-- BootBox -->
    <script src="{{ $urlAppend }}js/bootbox/bootbox.min.js"></script>
    <!-- SlimScroll -->
    <script src="{{ $urlAppend }}js/jquery.slimscroll.min.js"></script>
    <!-- BlockUI -->
    <script src="{{ $urlAppend }}js/blockui-master/jquery.blockUI.js"></script>
    <!-- Tinymce -->
    <script src="{{ $urlAppend }}js/tinymce/tinymce.min.js"></script>
    <!-- Screenfull -->
    <script src="{{ $urlAppend }}js/screenfull/screenfull.min.js"></script>

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


    @if (isset($styles_str) && $styles_str)
    <style>
        {!! $styles_str !!}
    </style>
    @endif

    <!-- Our js modern -->
    <script type="text/javascript" src="{{ $urlAppend }}js/slick.min.js"></script>
    <script type="text/javascript" src="{{ $urlAppend }}js/custom.js"></script>
    <script type="text/javascript" src="{{ $urlAppend }}js/viewStudentTeacher.js"></script>
    <script type="text/javascript" src="{{ $urlAppend }}js/sidebar_slider_action.js"></script>

    {!! $head_content !!}

    @stack('head_styles')

</head>
<body class='py-5 px-3'>
    <div class="container p-0">
        <div class="row m-auto" id="Frame">
            <div id="leftnav" class="col-2 sidebar embeded bg-transparent pt-5 ps-0">
                <div class="panel-group accordion mt-1" id="sidebar-accordion">
                    <div class="m-0 p-0 contextual-sidebar w-auto border-0">
                        <ul class="list-group list-group-flush">
                            @foreach ($toolArr as $key => $tool_group)
                                <div id="collapse{{ $key }}" class="panel-collapse list-group accordion-collapse collapse{{ $tool_group[0]['class'] }}">
                                    @foreach ($tool_group[1] as $key2 => $tool)
                                        <a href="{{ $tool_group[2][$key2] }}" class="list-group-item d-flex justify-content-start align-items-start module-tool rounded-0 gap-2 py-1 border-0 {{ module_path($tool_group[2][$key2]) == $current_module_dir ? " active" : ""}}">
                                            <span class='menu-items TextBold w-100'>{!! $tool !!}</span>
                                        </a>
                                    @endforeach
                                </div>
                            @endforeach
                        </ul>
                    </div>
                </div>
            </div>
            <div id="main-content" class="col-10 px-0">
                @if ($messages)
                    {!! $messages !!}
                @endif
                @yield('content')
            </div>
        </div>
    </div>
</body>
</html>
