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
    <link rel="stylesheet" href="{{ $template_base }}/CSS/bootstrap-custom.css?v={{ $eclass_version }}">

    <!-- Font Awesome - A font of icons -->
    <link href="{{ $template_base }}/CSS/font-awesome/css/font-awesome.css" rel="stylesheet">

    @if (isset($styles_str) && $styles_str)
        <style>
            {!! $styles_str !!}
        </style>
    @endif

    @stack('head_styles')

    <!-- jQuery -->
    <script type="text/javascript" src="{{ $urlAppend }}js/jquery-2.1.1.min.js"></script>

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


    <!--[if lt IE 9]>
      <script type="text/javascript" src="{{ $template_base }}/js/html5shiv.min.js"></script>
      <script type="text/javascript" src="{{ $template_base }}/js/respond.min.js"></script>
    <![endif]-->

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
                            <span class="sr-only">{{ $langMenu }}</span>
                        </button>
                        <a href='{{ $urlAppend }}' class="navbar-brand small-logo">
                            <img class="img-responsive hidden-md hidden-lg" src="{{ $logo_img_small }}" style="height: 36px;margin-top:8px;" alt='{{ $pageTitle }} logo'>
                        </a>
                        <ul class="nav navbar-nav navbar-right">
                            @if ($uid && !defined('UPGRADE'))
                                <li>
                                    <a href="{{ $urlAppend }}main/portfolio.php">
                                        <span class="fa fa-home"></span>
                                        <span class="sr-only">{{ trans('langPortfolio') }}</span>
                                    </a>
                                </li>
                                <li id="profile_menu_dropdown" class="dropdown">
                                   <a class="dropdown-toggle clearfix" role="button" id="dropdownMenu1" data-toggle="dropdown">
                                       <img alt="{{ trans('langProfileMenu') }}" class="img-circle user-icon" src="{{ user_icon($uid) }}" style="display: block; float: left; max-height: 20px;">
                                       <div style="display: block; float: left;">{{ $uname }}</div>
                                    </a>

                                    <ul class="dropdown-menu pull-right" role="menu" aria-labelledby="dropdownMenu1">
                                        <li role="presentation">
                                            <a role="menuitem" tabindex="-1" href="{{ $urlAppend }}main/my_courses.php"><span class="fa fa-graduation-cap fa-fw"></span>{!! trans('langMyCourses') !!}</a>
                                        </li>
                                        <li role="presentation">
                                            <a role="menuitem" tabindex="-1" href="{{ $urlAppend }}modules/message/index.php"><span class="fa fa-envelope-o fa-fw"></span>{!! trans('langMyDropBox') !!}</a>
                                        </li>
                                        <li role="presentation">
                                            <a role="menuitem" tabindex="-1" href="{{ $urlAppend }}modules/announcements/myannouncements.php"><span class="fa fa-bullhorn fa-fw"></span>{!! trans('langMyAnnouncements') !!}</a>
                                        </li>
                                        <li role="presentation">
                                            <a role="menuitem" tabindex="-1" href="{{ $urlAppend }}main/personal_calendar/index.php"><span class="fa fa-calendar fa-fw"></span>{!! trans('langMyAgenda') !!}</a>
                                        </li>
                                        <li role="presentation">
                                            <a role="menuitem" tabindex="-1" href="{{ $urlAppend }}main/notes/index.php"><span class="fa fa-edit fa-fw"></span>{!! trans('langNotes') !!}</a>
                                        </li>
                                        @if (get_config('personal_blog'))
                                        <li role="presentation">
                                            <a role="menuitem" tabindex="-1" href="{{ $urlAppend }}modules/blog/index.php?user_id={{ $uid }}&token={{ token_generate('personal_blog' . $uid) }}"><span class="fa fa-columns fa-fw"></span>{!! trans('langMyBlog') !!}</a>
                                        </li>
                                        @endif
                                        @if (get_config('eportfolio_enable'))
                                        <li role="presentation">
                                            <a role="menuitem" tabindex="-1" href="{{ $urlAppend }}main/eportfolio/index.php?id={{ $uid }}&token={{ token_generate('eportfolio' . $uid) }}"><span class="fa fa-star fa-fw"></span>{{ trans('langMyePortfolio') }}</a>
                                        </li>
                                        @endif
                                        @if (($session->status == USER_TEACHER and get_config('mydocs_teacher_enable')) or ($session->status == USER_STUDENT and get_config('mydocs_student_enable')))
                                        <li role="presentation">
                                            <a role="menuitem" tabindex="-1" href="{{ $urlAppend }}main/mydocs/index.php"><span class="fa fa-folder-open-o fa-fw"></span>{!! trans('langMyDocs') !!}</a>
                                        </li>
                                        @endif
                                        <li role="presentation">
                                            <a role="menuitem" tabindex="-1" href="{{ $urlAppend }}main/profile/display_profile.php"><span class="fa fa-user fa-fw"></span>{!! trans('langMyProfile') !!}</a>
                                        </li>
                                        <li role="presentation">
                                            <a role="menuitem" tabindex="-1" href="{{ $urlAppend }}main/my_widgets.php"><span class="fa fa-magic fa-fw"></span>{!! trans('langMyWidgets') !!}</a>
                                        </li>
                                        <li role="presentation">
                                            <a role="menuitem" tabindex="-1" href="{{ $urlAppend }}modules/usage/?t=u"><span class="fa fa-area-chart fa-fw"></span>{!! trans('langMyStats') !!}</a>
                                        </li>
                                        <li role="presentation" style="border-top: 1px solid #ddd">
                                            <a role="menuitem" tabindex="-1" href="{{ $urlAppend }}index.php?logout=yes"><span class="fa fa-unlock fa-fw"></span>{!! trans('langLogout') !!}</a>
                                        </li>
                                    </ul>
                                </li>
                                <li><a id="toggle-sidebar"><span class="fa fa-sliders"></span></a></li>
                                @else
                                {!! lang_selections() !!}
                                @if (!get_config('hide_login_link'))
                                    <li>
                                        <a href="{{ $urlAppend }}main/login_form.php{{ $nextParam }}">
                                            <span class="fa fa-lock"></span>
                                            <span class="sr-only">{!! trans('langLogin') !!}</span>
                                        </a>
                                    </li>
                                @endif
                            @endif
                        </ul>
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
                                    @if ($menuTypeID == 2 && $pageName)
                                        <a href='{{ $urlServer }}courses/{{ $course_code }}/'>{!! $section_title !!}</a>
                                    @else
                                        {!! $section_title !!}
                                    @endif
                                </h1>
                                @if (isset($course_id) && isset($professor))
                                    <h2 class='page-subtitle'>{!! $professor !!}</h2>
                                @endif
                                @if (!defined('HIDE_TOOL_TITLE'))
                                <div class='row'>
                                    <div class='col-md-12'>
                                        <h2 class='page-subtitle'>
                                            {{ $toolName }}
                                            @if ($require_help)
                                                <a id='help-btn' href='{{ $urlAppend }}modules/help/help.php?topic={{ $helpTopic }}&amp;language={{ $language }}&amp;subtopic={{ $helpSubTopic }}'>
                                                    <span class='fa fa-question-circle tiny-icon' data-toggle='tooltip' data-placement='top' title='{{ trans('langHelp') }}'></span>
                                                </a>
                                            @endif
                                            @if(defined('RSS'))
                                                <a href='{{ $urlAppend.RSS }}'>
                                                    <span class='fa fa-rss-square tiny-icon tiny-icon-rss' data-toggle='tooltip' data-placement='top' title='RSS Feed'></span>
                                                </a>
                                            @endif
                                            @if ($is_editor and isset($course_code) and display_activation_link($module_id))
                                                <a href='{{$urlAppend . 'main/module_toggle.php?course='. $course_code . '&amp;module_id='. $module_id }}' id='module_toggle' data-state='{{ $module_visibility ? 0 : 1 }}' data-toggle='tooltip' data-placement='top' title='{{ $module_visibility ? trans('langDeactivate') : trans('langActivate') }}'><span class='fa tiny-icon {{ $module_visibility ? 'fa-minus-square tiny-icon-red' : 'fa-check-square tiny-icon-green' }}'></span></a>
                                            @endif
                                        </h2>
                                    </div>
                                </div>
                                @endif
                            </div>


                            <div class='col-xs-3 hidden-print'>
                                @if ($show_toggle_student_view)
                                <div class='pull-right'>
                                    <form method='post' action='{{ $urlAppend }}main/student_view.php?course={{ $course_code }}' id='student-view-form'>
                                        <button class='btn-toggle{{ !$is_editor ? " btn-toggle-on" : "" }}' data-toggle='tooltip' data-placement='top' title='{{ $is_editor ? trans('langStudentViewEnable') : trans('langStudentViewDisable')}}'>
                                            <span class='on'><span class='fa fa-users'></span></span>
                                            <span class='off'><span class='fa fa-graduation-cap'></span></span>
                                        </button>
                                    </form>
                                </div>
                                @endif
                            </div>
                        </div>
                        @if ($messages)
                            <div class='row'>
                                <div class='col-xs-12'>{!! $messages !!}</div>
                            </div>
                        @endif
                        @yield('content')
                    </div>
                </div>
            </div>

            <div id="leftnav" class="col-md-2 col-xs-pull-10 col-sm-pull-10 col-md-pull-10 sidebar float-menu">
                <div class="logo">
                    <a href='{{ $urlAppend }}'>
                    <img class="img-responsive hidden-xs hidden-sm" src="{{ $logo_img }}" alt='{{ $pageTitle }} logo'>
                    </a>
                </div>
                @if (get_config('enable_search'))
                <div id="quick-search-wrapper">
                    <form action='{{ $urlAppend }}modules/search/{{ $search_action }}' method='post' >
                        <div class="input-group">
                            <label class='sr-only' for='search_terms'>{{ trans('langSearch') }}</label>
                            <input type="text" class="form-control input-sm" id="search_terms" name="search_terms" placeholder="{{ trans('langSearch') }}...">
                            <span class="input-group-btn">
                                <button id="btn-search" class="btn btn-sm" type="submit" name="quickSearch"><span class="fa fa-search"></span><span class="sr-only">{{ trans('langSearch') }}</span></button>
                            </span>
                        </div>
                    </form>
                </div>
                @endif
                <div class="panel-group" id="sidebar-accordion">
                    <div class="panel">
                        @foreach ($toolArr as $key => $tool_group)

                        <a class="collapsed parent-menu" data-toggle="collapse" data-parent="#sidebar-accordion" href="#collapse{{ $key }}">
                            <div class="panel-heading">
                                <div class="panel-title h3">
                                    <span class="fa fa-chevron-right"></span>
                                    <span>{{ $tool_group[0]['text'] }}</span>
                                </div>
                            </div>
                        </a>
                        <div id="collapse{{ $key }}" class="panel-collapse list-group collapse {{ $tool_group[0]['class'] }}{{ $key == $default_open_group? ' in': '' }}">
                            @foreach ($tool_group[1] as $key2 => $tool)
                            <a href="{{ $tool_group[2][$key2] }}" class="list-group-item {{ module_path($tool_group[2][$key2]) == $current_module_dir ? " active" : ""}}" {{ is_external_link($tool_group[2][$key2]) || $tool_group[3][$key2] == 'fa-external-link' ? ' target="_blank"' : "" }}>
                                <span class="fa {{ $tool_group[3][$key2] }} fa-fw"></span>
                                <span>{!! $tool !!}</span>
                            </a>
                            @endforeach
                        </div>
                        @endforeach
                    </div>
                    {{ isset($eclass_leftnav_extras) ? $eclass_leftnav_extras : "" }}
                </div>
            </div>
            <div id="sidebar-container">
                <aside id="sidebar" class="outer">
                    <div class="panel-group outerpanel" id="accordion-right-parent">
                        <div class="panel panel-default">
                            <div class="panel-heading" data-toggle="collapse" data-parent="#accordion-right-parent" data-target="#collapseCourses">
                                <div class="panel-title title h4">
                                    <span class="fa fa-list-alt"></span><span class="txt" >{{ trans('langMyCoursesSide') }}</span>
                                </div>
                            </div>
                            <div id="collapseCourses" class="panel-collapse collapse side-list">
                                <div class="panel-body">
                                    <div class="panel-group innerpanel" id="accordion-right">
                                        <div id="innerpanel-container">
                                        @foreach ($sidebar_courses as $key => $sidebar_course)
                                        <div class="panel panel-default">
                                            <div class="panel-heading" data-toggle="collapse" data-parent="#accordion-right" data-target="#collapse{{ $key }}-right">
                                                <div class="panel-title lesson-title clearfix">
                                                    <a href="{{ $urlAppend }}courses/{{ $sidebar_course->code }}/" class="lesson-title-link">{{ $sidebar_course->title }}</a><span class="fa fa-caret-down lesson-title-caret"></span>
                                                </div>
                                            </div>
                                            <div id="collapse{{ $key }}-right" class="panel-collapse collapse">
                                                <div class="panel-body">
                                                    <ul>
                                                        <li class="lesson-professor">{{ $sidebar_course->prof_names }}</li>
                                                        <li class="lesson-notifications" data-id="{{ $sidebar_course->id }}">
                                                        </li>
                                                    </ul>
                                                </div>
                                            </div>
                                        </div>
                                        @endforeach
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="panel panel-default">
                            <div class="panel-heading" data-toggle="collapse" data-parent="#accordion-right-parent" data-target="#collapseMessages">
                                <div class="panel-title title h4">
                                    <span class="fa fa-envelope"></span><span class="txt">{{ trans('langNewMyMessagesSide') }}</span><span class="num-msgs"></span>
                                </div>
                            </div>
                            <div id="collapseMessages" class="panel-collapse collapse side-list overlayed">
                                <div class="panel-body">
                                    <ul class="sidebar-mymessages">
                                    </ul>
                                    <div style="padding-top: 5px; border-top: 1px solid #ccc; margin-top: 15px;">
                                        <a class="goto" href="{{ $urlAppend }}modules/message/index.php">{{ trans('langAllMessages') }}</a>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="panel panel-default">
                            <div class="panel-heading" data-toggle="collapse" data-parent="#accordion-right-parent" data-target="#collapseNotices">
                                <div class="panel-title title h4">
                                    <span class="fa fa-pencil"></span><span class="txt">{{ trans('langQuickNotesSide') }}</span>
                                </div>
                            </div>
                            <div id="collapseNotices" class="panel-collapse collapse side-list overlayed">
                                <div class="spinner-div hidden">
                                    <img src="{{ $template_base }}/img/ajax-loader.gif" alt=''>
                                    <p class="hidden"></p>
                                </div>
                                <div class="panel-body">
                                    <div class="input-parent input-group">
                                        <span class="input-group-addon"><span class="fa fa-pencil"></span></span>
                                        <label class="sr-only" for="title-note">{{ trans('langNoteTitle') }}</label>
                                        <input id="title-note" class="form-control" type="text" placeholder="{{ trans('langNoteTitle') }}...">
                                    </div>
                                    <div class="input-parent input-group">
                                        <span class="input-group-addon"><span class="fa fa-pencil"></span></span>
                                        <label class="sr-only" for="text-note">{{ trans('langNote') }}</label>
                                        <textarea id="text-note" class="form-control" rows="6" placeholder="{{ trans('langEnterNote') }}..."></textarea>
                                    </div>
                                    <button id="save_note" data-label="Save" class="btn btn-default btn-xs">{{ trans('langSave') }}</button>
                                    <a class="goto" href="{{ $urlAppend }}main/notes/index.php">{{ trans('langAllNotes') }}</a>
                                </div>
                            </div>
                        </div>
                    </div>
                </aside>
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
                    <span><a href='{{ $urlAppend }}info/copyright.php'>Open eClass © 2003-{{ date('Y') }}</a> &mdash; <a href="{{ $urlAppend }}info/terms.php">{{ trans('langUsageTerms') }}</a></span>
                </footer>
            </div>
        </div>
    </div>

    @stack('footer_scripts')
    @if (get_config('ext_analytics_enabled') and $html_footer = get_config('ext_analytics_code')) {
        {!! get_config('ext_analytics_code') !!}
    @endif
</body>
</html>

