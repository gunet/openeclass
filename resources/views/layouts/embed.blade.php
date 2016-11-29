<!-- BEGIN mainBlock -->
<!DOCTYPE HTML>
<html style="height: 100%;" lang="{{ $language }}">
<head>

    <meta http-equiv="Content-Type" content="text/html; charset=utf-8">

    <title>{{ $pageTitle }}</title>

    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <!-- jQuery -->
    <script type="text/javascript" src="{{ $urlAppend }}js/jquery-2.1.1.min.js"></script>

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
    <link rel="stylesheet" href="{{ $template_base }}/CSS/bootstrap-custom.css">

    <!-- Font Awesome - A font of icons -->
    <link href="{{ $template_base }}/CSS/font-awesome/css/font-awesome.css" rel="stylesheet">

    @if (isset($styles_str) && $styles_str)
    <style>
        {!! $styles_str !!}
    </style>
    @endif
    {!! $head_content !!}

    @stack('head_styles')

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
                        @yield('content')
                    </div>
                </div>
            </div>
            <div id="leftnav" class="col-xs-2 col-xs-pull-10 sidebar embeded" style="top: 0px; height: 100%; overflow: visible;">
                <div class="panel-group" id="sidebar-accordion">
                    <div class="panel">
                        @foreach ($toolArr as $key => $tool_group)
                        <div id="collapse{{ $key }}" class="panel-collapse list-group collapse{{ $tool_group[0]['class'] }}">
                            @foreach ($tool_group[1] as $key2 => $tool)
                            <a href="{{ $tool_group[2][$key2] }}" class="list-group-item {{ module_path($tool_group[2][$key2]) == $current_module_dir ? " active" : ""}}">
                                <span class="fa {{ $tool_group[3][$key2] }} fa-fw"></span>
                                <span>{!! $tool !!}</span>
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
