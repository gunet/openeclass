<head>
    <title>{{ $page_title }}</title>
    <meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <!-- jQuery -->
    <script type="text/javascript" src="{{ $url_path }}js/jquery-3.6.0.min.js"></script>

    <!-- Latest compiled and minified JavaScript -->
    <script src="{{ $url_path }}js/bootstrap.bundle.min.js"></script>

    <!-- Latest compiled and minified CSS -->
    <link rel="stylesheet" href="{{ $template_base }}css/bootstrap.min.css">

    <!-- Font Awesome - A font of icons -->
    <link href="{{ $template_base }}css/font-awesome-6.4.0/css/all.css" rel="stylesheet">
    <style>
        .navbar-inverse {background-color: #4a4a4a;}
        .navbar-inverse .navbar-nav > li > a {color: whitesmoke;}
        .navbar-inverse .navbar-nav > li > a.inactive, .navbar-inverse .navbar-nav > li > a.inactive:hover, .navbar-inverse .navbar-nav > li > a.inactive:focus {color: #9d9d9d; cursor: default;}
        .navbar-inverse .navbar-nav > li > a:hover, .navbar-inverse .navbar-nav > li > a:focus { color: #9BCCF7; }
        .navbar-inverse ul li{padding: 1px 7px;}
        .navbar-inverse ul li:last-child{border-left: 1px solid #999; margin-left: 3px; padding-left: 10px; font-family: sans-serif;}
        .navbar-inverse ul li a {color: whitesmoke;}
        .navbar-inverse ul li a:hover {text-decoration: none;}
        .navbar-inverse ul li a {vertical-align: middle;}
    </style>

    {!! $ebook_head !!}

    <script type='text/javascript'>
        function change_section() {
            top.location = '{{ $ebook_url_base }}' + document.getElementsByName('section')[0].value + '/{{ $unit_parameter }}';
        }
        $(function() {
            $("body").keydown(function(e) {
                if(e.keyCode == 37) { // left
                    top.location = '{{ $ebook_url_base . $back_section_id . '/' . $unit_parameter }}';
                }
                if(e.keyCode == 39) { //  right
                    top.location = '{{ $ebook_url_base . $next_section_id . '/' . $unit_parameter }}';
                }
                if (e.keyCode == 27) { // esc
                    top.location = '{{ $exit_fullscreen_link }}';
                }
            });
        });

        $(window).load(function() {
            var bookHeight = $("#book-container").height();
            var windowHeight = $(window).height();
            var offsetHeight = $("nav").height() + 150;
            var newBookHeight = windowHeight - offsetHeight;

            if ( bookHeight < newBookHeight ){
                $("#book-container").height(newBookHeight);
            }
        });

    </script>
</head>

<body style="background:whitesmoke;">
    <nav class="navbar navbar-inverse navbar-static-top" role="navigation" style="margin: 0px;">
        <div class="container-fluid" style="margin-bottom: 0px;">
            <div class="col-12 d-flex justify-content-between align-items-center flex-wrap gap-2">
                <a class="navbar-brand pull-left" href="#" style="padding:3px 3px 6px 3px; border-radius: 5px;"><img class="img-responsive" style='height:30px;' src="{{ $logo_img_small }}"></a>

                <div class=" pull-left">
                    <div class='form-group'>
                        <select class='form-select' name='section' onChange="change_section();" style='width: 250px; white-space: nowrap; overflow: hidden; text-overflow: ellipsis;'>
                            {!! $chapter_select_options !!}
                        </select>
                    </div>
                </div>
                <ul class="d-flex list-inline  pull-right mb-0" >
                    @if (!$show_orphan_file))
                        @if (isset($back_section_id))
                            <li><a href='{{ $ebook_url_base . $back_section_id . '/' . $unit_parameter }}' title='{{ $back_title }}'><i class='fa fa-arrow-circle-left fa-lg'></i></a></li>
                        @else
                            <li><a class='inactive' href='#'><i class='fa fa-arrow-circle-right' ></i></a></li>
                        @endif
                        @if (isset($next_section_id))
                            <li><a href='{{ $ebook_url_base . $next_section_id . '/' . $unit_parameter }}' title='{{ $next_title }}'><i class='fa fa-arrow-circle-right fa-lg'></i></a></li>
                        @else
                            <li><a class='inactive' href='#'><i class='fa fa-arrow-circle-right' ></i></a></li>
                        @endif
                    @endif
                    <li><a href='{{ $exit_fullscreen_link }}' title='{{ trans('langBackCourse') }}'><i class='fa fa-times fa-sm nofullscreen text-danger'></i> {{ trans('langClose') }}</a></li>
                </ul>
            </div>
        </div>
    </nav>
    <div class="container-fluid" style="padding:15px; background-color: #f0f0f0; margin: 0px;">
        <div class='row'>
            <div class='col-lg-8 col-12 ms-auto me-auto'>
                <nav aria-label="breadcrumb">
                    <ol class="breadcrumb">
                        <li class="breadcrumb-item"><a href="{{ $course_home_link }}">{{ $course_title_short }}</a></li>
                        <li class="breadcrumb-item"><a href="{{ $course_ebook_link }}">{{ $course_ebook }}</a></li>
                        <li class="breadcrumb-item active">{{ $ebook_title_short }}</li>
                    </ol>
                </nav>
                <div id="book-container" style="border: 1px solid #ccc; border-radius: 2px; padding: 20px; background-color: white;">
                    {!! $ebook_body !!}
                </div>
            </div>
        </div>
    </div>
    {!! $html_footer !!}
    </body>
</html>
