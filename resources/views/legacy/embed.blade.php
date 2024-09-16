<!-- BEGIN mainBlock -->
<!DOCTYPE HTML>
<html style="height: 100%;">
<head>

    <meta http-equiv="Content-Type" content="text/html; charset=utf-8">

    <title>{{ $pageTitle }}</title>

    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <!-- jQuery -->
    <script type="text/javascript" src="{{ $urlAppend }}js/jquery-3.6.0.min.js"></script>

    <!-- Latest compiled and minified JavaScript -->
    <script src="{{ $template_base }}/js/bootstrap.min.js"></script>

     <!-- BootBox -->
    <script src="{{ $urlAppend }}js/bootbox/bootbox.min.js"></script>
    <script>
    bootbox.setDefaults({
      locale: "{{ $language }}"
    });
    </script>
    <!-- Our javascript -->
    <script type="text/javascript" src="{{ $template_base }}/js/main.js"></script>

    <!-- Latest compiled and minified CSS -->
    <link rel="stylesheet" href="{{ $template_base }}/css/bootstrap.min.css">
    <link rel="stylesheet" href="{{ $template_base }}/css/sidebar.css"/>
    <link rel="stylesheet" href="{{ $template_base }}/css/default.css"/>

    <!-- Font Awesome - A font of icons -->
    <link href="{{ $template_base }}/css/font-awesome-4.7.0/css/font-awesome.css" rel="stylesheet">

    @if (isset($styles_str) && $styles_str)
    <style>
        {!! $styles_str !!}
    </style>
    @endif
    {!! $head_content !!}

</head>
<body style="height: 100%;">
    <div class="container" style="height: 100%;">
        <div class="row" id="Frame" style="height: 100%;">
            <div id="background-cheat" class="col-xs-10 col-xs-push-2" style="height: 100%;"></div>
            <div id="main-content" class="col-xs-10 col-xs-push-2" style="height: 100%; overflow: scroll;">
                <div class="row row-main">
                    <div class="col-md-12 add-gutter">
                        @if ($messages)
                            <div class='row'>
                                <div class='col-xs-12'>{!! $messages !!}</div>
                            </div>
                        @endif
                        {!! $tool_content !!}
                    </div>
                </div>
            </div>
            <div id="leftnav" class="col-xs-2 col-xs-pull-10 sidebar embeded" style="top: 0px; height: 100%; overflow: visible;">
                <div class="panel-group accordion" id="sidebar-accordion">
                    <div class="panel">
                        @foreach ($toolArr as $key => $tool_group)
                        <div id="collapse{{ $key }}" class="panel-collapse list-group accordion-collapse collapse{{ $tool_group[0]['class'] }}">
                            @foreach ($tool_group[1] as $key2 => $tool)
                            <a href="{{ $tool_group[2][$key2] }}" class="leftMenuToolCourse list-group-item {{ module_path($tool_group[2][$key2]) == $current_module_dir ? " active" : ""}}">
                                <div class='d-flex align-items-start'>
                                    <span class="fa {{ $tool_group[3][$key2] }} toolSidebarTxt pe-2"></span>
                                    <span class="toolSidebarTxt">{!! $tool !!}</span>
                                </div>
                            </a>
                            @endforeach
                        </div>
                        @endforeach
                    </div>
                </div>
            </div>
        </div>
    </div>
</body>
</html>
