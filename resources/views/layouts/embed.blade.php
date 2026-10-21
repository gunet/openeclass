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
        <link rel="stylesheet" href="{{ $urlAppend }}js/datatables/datatables.min.css">
        <script src="{{ $urlAppend }}js/datatables/datatables.min.js"></script>
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

        {{-- Override the default.css and all .css files from load_js function with the currect theme.css file --}}
        @if ($theme_id && file_exists($theme_css))
            <link rel="stylesheet" type="text/css" href="{{ $urlAppend }}courses/theme_data/{{ $theme_id }}/style_str.css?v={{ $cache_suffix }}"/>
        @endif

        <!-- Our js modern -->
        <script type="text/javascript" src="{{ $urlAppend }}js/slick.min.js"></script>
        <script type="text/javascript" src="{{ $urlAppend }}js/custom.js"></script>
        <script type="text/javascript" src="{{ $urlAppend }}js/viewStudentTeacher.js"></script>
        <script type="text/javascript" src="{{ $urlAppend }}js/sidebar_slider_action.js"></script>

        {!! $head_content !!}

        @stack('head_styles')
    </head>

    <body class='py-1 px-3'>
        <div class="container p-0">
            <div class="row m-auto" id="Frame">
                <div id="leftnav" class="col-3 sidebar embeded bg-transparent pt-5 ps-0">
                    <div class="panel-group mt-1">
                        <div class="panel">
                            @foreach ($toolArr as $key => $tool_group)
                                @if ($key == 'inactive' && !$is_editor && !$is_course_reviewer)
                                    @continue
                                @endif
                                <div class="list-group rounded-0 mt-3" aria-labelledby="Tool{{ $key }}">
                                    <div class="m-0 p-0 contextual-sidebar w-auto border-0">
                                        <ul class="list-group list-group-flush">
                                            @foreach ($tool_group as $tool)
                                                <li>
                                                    <a aria-label="{{ $tool['title'] }}" href="{{ $tool['link'] }}" class='list-group-item d-flex justify-content-start align-items-start module-tool px-3 py-1 border-0 {{ module_path($tool['link']) == $current_module_dir ? " active" : ""}}'
                                                       data-bs-toggle="tooltip" data-bs-html="true" data-bs-placement="right" title="{{ $tool['title'] }}" style='gap:1rem;'>
                                                        <i class="{{ $tool['icon'] }} mt-1"></i>
                                                        <span class='menu-items TextBold w-100'>
                                                        {{ $tool['title'] }} {!! $tool['extra_text'] !!}
                                                    </span>
                                                    </a>
                                                </li>
                                            @endforeach
                                        </ul>
                                    </div>
                                </div>
                            @endforeach
                        </div>
                    </div>
                </div>
                <div id="main-content" class="col-9 px-0">
                    @yield('content')
                </div>
            </div>
        </div>
    </body>
</html>
