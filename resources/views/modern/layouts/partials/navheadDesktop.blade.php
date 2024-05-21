<nav id="bgr-cheat-header" class="navbar navbar-eclass py-0 fixed-top">
<div class='{{ $container }} header-container py-0'>


    <div class='d-none d-lg-block w-100 header-large-screen'>
        <div class='col-12 h-100 d-flex justify-content-between align-items-center'>
            <div class='d-flex justify-content-start align-items-center h-100'>
                <a class='me-lg-4 me-xl-5' href="{{ $urlAppend }}" aria-label='Homepage'>
                    <img class="eclass-nav-icon m-auto d-block" src="{{ $logo_img }}" alt="Logo"/>
                </a>
                @if(!get_config('show_only_loginScreen'))
                    <ul class="container-items nav">
                        @if(!get_config('hide_login_link'))
                            <li class="nav-item">
                                <a id="link-home" class="nav-link menu-item mx-lg-2 @if (!isset($_SESSION['uid']) && empty($pageName)) active2 @endif" href="{{ $urlServer }}?show_home=true">
                                    {{ trans('langHome') }}
                                </a>
                            </li>
                        @endif
                        @if (!isset($_SESSION['uid']))
                            <li class="nav-item">
                                <a id="link-register" class="nav-link menu-item mx-lg-2 @if(get_config('registration_link')=='hide') d-none @endif" href="{{ $urlServer }}modules/auth/registration.php">
                                    {{ trans('langRegistration') }}
                                </a>
                            </li>
                        @endif
                        @if (isset($_SESSION['uid']))
                            <li class="nav-item">
                                <a id="link-portfolio" class="nav-link menu-item mx-lg-2" href="{{ $urlServer }}main/portfolio.php">
                                    {{ trans('langPortfolio') }}
                                </a>
                            </li>
                        @endif
                        @if (!get_config('dont_display_courses_menu'))
                            <li class="nav-item">
                                <a id="link-lessons" class="nav-link menu-item mx-lg-2" href="{{ $urlServer }}modules/auth/listfaculte.php">
                                    {{ trans('langCourses') }}
                                </a>
                            </li>
                        @endif
                        @if (faq_exist())
                            <li class="nav-item">
                                <a id="link-faq" class="nav-link menu-item mx-lg-2 " href="{{$urlAppend}}info/faq.php">
                                    {{ trans('langFaq') }}
                                </a>
                            </li>
                        @endif
                    </ul>
                @endif
            </div>
            <div class='d-flex justify-content-end align-items-center h-100 pe-0 gap-3'>
                @if(get_config('enable_search'))
                    <div class='h-100 d-flex justify-content-start align-items-center'>
                        <div class='h-40px'>
                            <form id='submitSearch' class="d-flex justify-content-start align-items-center h-40px gap-2" action='{{ $urlAppend }}modules/search/{{ $search_action }}' method='post' role='search'>
                                <div>
                                    <a id="btn-search" class="btn d-flex justify-content-center align-items-center bg-transparent border-0 p-0 rounded-0" type="button" name="quickSearch" aria-label="Quick search">
                                        <i class="fa-solid fa-magnifying-glass fa-lg"></i>
                                    </a>
                                </div>
                                <input id="search_terms" type="text" class="inputSearch form-control rounded-0 px-0" placeholder='{{ trans('langSearch') }}...' name="search_terms" aria-label="Search terms"/>
                            </form>
                        </div>
                    </div>
                @endif
                @if(!isset($_SESSION['uid']) && count($session->active_ui_languages) > 1)
                    <div class='h-40 d-flex justify-content-start align-items-center split-left'>
                        <div class="d-flex justify-content-start align-items-center h-40px">
                            {!! lang_selections_Desktop('idLangSelectionDesktop') !!}
                        </div>
                    </div>
                @endif
                @if(isset($_SESSION['uid']) && get_config('enable_search'))
                    <div class='split-content'></div>
                @endif
                <div class='user-menu-content h-100 d-flex justify-content-start align-items-center'>
                    <div class='d-flex justify-content-start align-items-center h-80px'>
                        @if(!isset($_SESSION['uid']) and get_config('dont_display_login_form'))
                            <div class='d-flex justify-content-center align-items-center split-left h-40px'>
                                @if($authCase)
                                    @if(!empty($authNameEnabled))
                                        @if($authNameEnabled == 'cas')
                                            <a class='header-login-text' href="{{ $urlServer }}modules/auth/cas.php">
                                                {{ trans('langUserLogin') }}
                                            </a>
                                        @else
                                            <a class='header-login-text' href="{{ $urlServer }}secure/">
                                                {{ trans('langUserLogin') }}
                                            </a>
                                        @endif
                                    @endif
                                @else
                                    <a class='header-login-text' href="{{ $urlServer }}main/login_form.php">
                                        {{ trans('langUserLogin') }}
                                    </a>
                                @endif
                            </div>
                        @elseif(!isset($_SESSION['uid']) and !get_config('dont_display_login_form'))
                             @if(!empty($authNameEnabled))
                                <div class='d-flex justify-content-center align-items-center split-left h-40px'>
                                    @if($authNameEnabled == 'cas')
                                        <a class='header-login-text' href="{{ $urlServer }}modules/auth/cas.php">
                                            {{ trans('langUserLogin') }}
                                        </a>
                                    @else
                                        <a class='header-login-text' href="{{ $urlServer }}secure/">
                                            {{ trans('langUserLogin') }}
                                        </a>
                                    @endif
                                </div>
                            @endif
                        @endif
                        @if(isset($_SESSION['uid']))
                            <div class='d-flex justify-content-end p-0 h-80px'>
                                <div class="btn-group" role="group" aria-label="Button group with nested dropdown">
                                    <div class="btn-group" role="group">
                                        <button id="btnGroupDrop1" type="button" class="btn user-menu-btn rounded-0 d-flex justify-content-center align-items-center gap-2 rounded-0" data-bs-toggle="dropdown" aria-expanded="false">
                                                <img class="user-icon-filename" src="{{ user_icon($_SESSION['uid'], IMAGESIZE_LARGE) }}" alt="{{ $uname }}">
                                                <span class='TextBold user-name'>{{ $_SESSION['uname'] }}</span>
                                                <i class="fa-solid fa-chevron-down"></i>
                                        </button>
                                        <div class="m-0 p-3 dropdown-menu dropdown-menu-end contextual-menu contextual-menu-user" aria-labelledby="btnGroupDrop1">
                                            <ul class="list-group list-group-flush">

                                                <li>
                                                    <a class="list-group-item d-flex justify-content-start align-items-start gap-2 px-1 pe-none">
                                                        <img class="user-icon-filename" src="{{ user_icon($_SESSION['uid'], IMAGESIZE_LARGE) }}" alt="{{ $uname }}">
                                                        <div>
                                                            <h4 class='truncate-text username-text mb-0'>{{ $_SESSION['givenname'] }}&nbsp;{{ $_SESSION['surname'] }}</h4>
                                                            <p class='small-text username-paragraph'>{{ $_SESSION['uname'] }}</p>
                                                        </div>

                                                    </a>
                                                </li>
                                                @if ((isset($is_admin) and $is_admin) or
                                                    (isset($is_power_user) and $is_power_user) or
                                                    (isset($is_usermanage_user) and ($is_usermanage_user)) or
                                                    (isset($is_departmentmanage_user) and $is_departmentmanage_user))
                                                    <li>
                                                        <a class="list-group-item d-flex justify-content-start align-items-center gap-2 py-0"
                                                                href="{{ $urlAppend }}modules/admin/index.php">
                                                                <i class="fa-solid fa-gear settings-icons"></i>
                                                                {{ trans('langAdminTool') }}
                                                        </a>
                                                    </li>
                                                @endif
                                                @if ($_SESSION['status'] == USER_TEACHER or $is_power_user or $is_departmentmanage_user)
                                                <li>
                                                    <a class="list-group-item d-flex justify-content-start align-items-center gap-2 py-0" href="{{ $urlAppend }}modules/create_course/create_course.php">
                                                        <i class="fa-solid fa-circle-plus settings-icons"></i>
                                                        {{ trans('langCourseCreate') }}
                                                    </a>
                                                </li>
                                                @endif
                                                <li>
                                                    <a class="list-group-item d-flex justify-content-start align-items-center gap-2 py-0" href="{{ $urlAppend }}main/portfolio.php">
                                                        <i class="fa-solid fa-house settings-icons"></i>
                                                        {{ trans('langMyPortfolio') }}
                                                    </a>
                                                </li>
                                                <li>
                                                    <a class="list-group-item d-flex justify-content-start align-items-center gap-2 py-0" href="{{ $urlAppend }}main/profile/display_profile.php">
                                                        <i class="fa-solid fa-user settings-icons"></i>
                                                        {{ trans('langMyProfile') }}
                                                    </a>
                                                </li>

                                                <li>
                                                    <a class="list-group-item d-flex justify-content-start align-items-center gap-2 py-0" href="{{ $urlAppend }}main/my_courses.php">
                                                        <i class="fa-solid fa-book-open settings-icons"></i>
                                                        {{ trans('langMyCourses') }}
                                                    </a>
                                                </li>
                                                @if ($_SESSION['status'] == USER_STUDENT && get_config('eclass_prof_reg'))
                                                    <li>
                                                        <a class="list-group-item d-flex justify-content-start align-items-center gap-2 py-0" href="{{ $urlAppend }}modules/auth/formuser.php">
                                                            <i class="fa-regular fa-hand"></i>
                                                            {{ trans('langMyRequests') }}
                                                        </a>
                                                    </li>
                                                @endif
                                                <li>
                                                    <a class="list-group-item d-flex justify-content-start align-items-center gap-2 py-0" href="{{ $urlAppend }}modules/announcements/myannouncements.php">
                                                        <i class="fa-regular fa-bell settings-icons"></i>
                                                        {{ trans('langMyAnnouncements') }}
                                                    </a>
                                                </li>
                                                <li>
                                                    <a class="list-group-item d-flex justify-content-start align-items-center gap-2 py-0" href="{{ $urlAppend }}main/notes/index.php">
                                                        <i class="fa-regular fa-file-lines settings-icons"></i>
                                                        {{ trans('langNotes') }}
                                                    </a>
                                                </li>
                                                @if (get_config('eportfolio_enable'))
                                                    <li>
                                                        <a class="list-group-item d-flex justify-content-start align-items-center gap-2 py-0" href="{{ $urlAppend }}main/eportfolio/index.php?id={{$uid}}&token={{ token_generate('eportfolio'.$uid) }}">
                                                            <i class="fa-regular fa-address-card settings-icons"></i>
                                                            {{ trans('langMyePortfolio') }}
                                                        </a>
                                                    </li>
                                                @endif
                                                @if((isset($collaboration_platform) and !$collaboration_platform) or is_null($collaboration_platform))
                                                <li>
                                                    <a class="list-group-item d-flex justify-content-start align-items-center gap-2 py-0" href="{{ $urlAppend }}modules/usage/index.php?t=u">
                                                        <i class="fa-solid fa-chart-line settings-icons"></i>
                                                        {{ trans('langMyStats') }}
                                                    </a>
                                                </li>
                                                @endif
                                                @if((isset($collaboration_platform) and !$collaboration_platform) or is_null($collaboration_platform))
                                                @if (get_config('personal_blog'))
                                                    <li>
                                                        <a class="list-group-item d-flex justify-content-start align-items-center gap-2 py-0" href="{{ $urlAppend }}modules/blog/index.php?user_id={{$uid}}&token={{ token_generate('personal_blog'.$uid) }}">
                                                            <i class="fa-solid fa-globe settings-icons"></i>
                                                            {{ trans('langMyBlog') }}
                                                        </a>
                                                    </li>
                                                @endif
                                                @endif
                                                <li>
                                                    <a class="list-group-item d-flex justify-content-start align-items-center gap-2 py-0" href="{{ $urlAppend }}modules/message/index.php">
                                                        <i class="fa-regular fa-envelope settings-icons"></i>
                                                        {{ trans('langMyDropBox') }}
                                                    </a>
                                                </li>
                                                <li>
                                                    <a class="list-group-item d-flex justify-content-start align-items-center gap-2 py-0" href="{{ $urlAppend }}main/personal_calendar/index.php">
                                                        <i class="fa-regular fa-calendar settings-icons"></i>
                                                        {{ trans('langMyAgenda') }}
                                                    </a>
                                                </li>
                                                @if((isset($collaboration_platform) and !$collaboration_platform) or is_null($collaboration_platform))
                                                <li>
                                                    <a class="list-group-item d-flex justify-content-start align-items-center gap-2 py-0" href="{{ $urlAppend }}main/my_widgets.php">
                                                        <i class="fa-solid fa-layer-group settings-icons"></i>
                                                        {{ trans('langMyWidgets') }}
                                                    </a>
                                                </li>
                                                @endif
                                                @if((isset($collaboration_platform) and !$collaboration_platform) or is_null($collaboration_platform))
                                                <li>
                                                    <a class="list-group-item d-flex justify-content-start align-items-center gap-2 py-0" href="{{ $urlAppend }}main/gradebookUserTotal/index.php">
                                                        <i class="fa-solid fa-a settings-icons"></i>
                                                        {{ trans('langGradeTotal') }}
                                                    </a>
                                                </li>
                                                @endif
                                                @if((isset($collaboration_platform) and !$collaboration_platform) or is_null($collaboration_platform))
                                                <li>
                                                    <a class="list-group-item d-flex justify-content-start align-items-center gap-2 py-0" href="{{ $urlAppend }}main/mycertificates.php">
                                                        <i class="fa-solid fa-award settings-icons"></i>
                                                        {{ trans('langMyCertificates') }}
                                                    </a>
                                                </li>
                                                @endif
                                                @if (($session->status == USER_TEACHER and get_config('mydocs_teacher_enable')) or ($session->status == USER_STUDENT and get_config('mydocs_student_enable')) or ($session->status == ADMIN_USER and get_config('mydocs_teacher_enable')))
                                                    <li>
                                                        <a class="list-group-item d-flex justify-content-start align-items-center gap-2 py-0" href="{{ $urlAppend }}main/mydocs/index.php">
                                                            <i class="fa-regular fa-file settings-icons"></i>
                                                            {{ trans('langMyDocs') }}
                                                        </a>
                                                    </li>
                                                @endif
                                                <li>
                                                    <form method='post' action='{{ $urlAppend }}modules/auth/logout.php' style='height:40px;'>
                                                        <input type='hidden' name='token' value='{{ $_SESSION['csrf_token'] }}'>
                                                        <button class='list-group-item d-flex justify-content-start align-items-center gap-2 py-0 w-100 h-100 text-end rounded-0 logout-list-item' type='submit' name='submit'>
                                                            <i class="fa-solid fa-arrow-right-from-bracket Accent-200-cl "></i>
                                                            <span class='Accent-200-cl TextBold'>{{ trans('langLogout2') }}</span>
                                                        </button>
                                                    </form>
                                                </li>
                                            </ul>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        @endif
                    </div>
                </div>
            </div>
        </div>
    </div>




    <div class='d-block d-lg-none w-100 header-small-screen'>
        <div class='col-12 h-100 d-flex justify-content-between align-items-center'>

            <div class='d-flex justify-content-start align-items-center gap-2'>

                <a class="p-0 small-basic-size d-flex justify-content-center align-items-center link-bars-options" type="button" data-bs-toggle="offcanvas" href="#offcanvasScrollingTools" aria-controls="offcanvasScrollingTools" aria-label="Registration and available courses">
                    <i class="fa-solid fa-ellipsis-vertical fa-lg"></i>
                </a>

                <a class='d-flex justify-content-start align-items-center' type="button" href="{{ $urlServer }}" aria-label='Homepage'>
                    <img class="eclass-nav-icon px-2 bg-transparent" src="{{ $logo_img_small }}" alt="logo">
                </a>
            </div>

            @if (!isset($_SESSION['uid']))
                <div class='d-flex justify-content-start align-items-center gap-3'>
                    {!! lang_selections_Desktop('idLangSelectionMobile') !!}
                    @if(get_config('dont_display_login_form'))
                        <a class='header-login-text' href="{{ $urlAppend }}main/login_form.php">
                            {{ trans('langUserLogin') }}
                        </a>
                    @endif
                </div>
            @endif


            @if(isset($_SESSION['uid']))
                <div>
                    <button class="btn btn-transparent p-0 dropdown-toogle d-flex justify-content-end align-items-center" type="button"
                            id="dropdownMenuButton1" data-bs-toggle="dropdown" aria-expanded="false">
                            <img class="user-icon-filename mt-0" src="{{ user_icon($_SESSION['uid'], IMAGESIZE_LARGE) }}" alt="{{ $uname }}">
                    </button>

                    <div class="m-0 py-3 px-3 dropdown-menu dropdown-menu-end contextual-menu contextual-menu-user contextual-border" aria-labelledby="dropdownMenuButton1">
                        <ul class="list-group list-group-flush dropdown_menu_user">
                            <li>
                                <a class="list-group-item d-flex justify-content-start align-items-start gap-2 py-2 px-2 pe-none">
                                    <img class="user-icon-filename" src="{{ user_icon($_SESSION['uid'], IMAGESIZE_LARGE) }}" alt="{{ $uname }}">
                                    <div>
                                        <h4 class='truncate-text username-text mb-0'>{{ $_SESSION['givenname'] }}&nbsp;{{ $_SESSION['surname'] }}</h4>
                                        <p class='small-text username-paragraph'>{{ $_SESSION['uname'] }}</p>
                                    </div>

                                </a>
                            </li>
                            @if ((isset($is_admin) and $is_admin) or
                                (isset($is_power_user) and $is_power_user) or
                                (isset($is_usermanage_user) and ($is_usermanage_user)) or
                                (isset($is_departmentmanage_user) and $is_departmentmanage_user))
                                <li>
                                    <a type="button" class="list-group-item d-flex justify-content-start align-items-start py-3 gap-2" href="{{ $urlAppend }}modules/admin/index.php"><i class="fa-solid fa-gear settings-icons"></i>{{trans('langAdminTool')}}</a>
                                </li>
                            @endif

                            @if ($_SESSION['status'] == USER_TEACHER or $is_power_user or $is_departmentmanage_user)
                                <li><a class="list-group-item d-flex justify-content-start align-items-start py-3 gap-2" href="{{ $urlAppend }}modules/create_course/create_course.php"><i class="fa-solid fa-circle-plus settings-icons"></i>{{ trans('langCourseCreate') }}</a></li>
                            @endif
                            <li>
                                <a class="list-group-item d-flex justify-content-start align-items-start py-3 gap-2" href="{{ $urlAppend }}main/portfolio.php"><i class="fa-solid fa-house settings-icons"></i>{{ trans('langMyPortfolio') }}</a>
                            </li>
                            <li>
                                <a class="list-group-item d-flex justify-content-start align-items-start py-3 gap-2" href="{{ $urlAppend }}main/my_courses.php"><i class="fa-solid fa-book-open settings-icons"></i>{{trans('langMyCoursesSide')}}</a>
                            </li>
                            @if ($_SESSION['status'] == USER_STUDENT && get_config('eclass_prof_reg'))
                                <li>
                                    <a class="list-group-item d-flex justify-content-start align-items-start py-3 gap-2" href="{{ $urlAppend }}modules/auth/formuser.php"><i class="fa-regular fa-hand"></i>{{ trans('langMyRequests') }}</a>
                                </li>
                            @endif
                            <li>
                                <a class="list-group-item d-flex justify-content-start align-items-start py-3 gap-2" href="{{ $urlAppend }}modules/announcements/myannouncements.php"><i class="fa-regular fa-bell settings-icons"></i>{{ trans('langMyAnnouncements') }}</a>
                            </li>
                            <li>
                                <a class="list-group-item d-flex justify-content-start align-items-start py-3 gap-2" href="{{ $urlAppend }}main/notes/index.php"><i class="fa-regular fa-file-lines settings-icons"></i>{{ trans('langNotes') }}</a>
                            </li>
                            @if (get_config('eportfolio_enable'))
                            <li>
                                <a class="list-group-item d-flex justify-content-start align-items-start py-3 gap-2" href="{{ $urlAppend }}main/eportfolio/index.php?id={{$uid}}&token={{ token_generate('eportfolio'.$uid) }}"><i class="fa-regular fa-address-card settings-icons"></i>{{ trans('langMyePortfolio') }}</a>
                            </li>
                            @endif
                            @if((isset($collaboration_platform) and !$collaboration_platform) or is_null($collaboration_platform))
                            <li>
                                <a class="list-group-item d-flex justify-content-start align-items-start py-3 gap-2" href="{{ $urlAppend }}modules/usage/index.php?t=u"><i class="fa-solid fa-chart-line settings-icons"></i>{{ trans('langMyStats') }}</a>
                            </li>
                            @endif
                            @if (get_config('personal_blog'))
                            @if((isset($collaboration_platform) and !$collaboration_platform) or is_null($collaboration_platform))
                            <li>
                                <a class="list-group-item d-flex justify-content-start align-items-start py-3 gap-2" href="{{ $urlAppend }}modules/blog/index.php?user_id={{$uid}}&token={{ token_generate('personal_blog'.$uid) }}"><i class="fa-solid fa-globe settings-icons"></i>{{ trans('langMyBlog') }}</a>
                            </li>
                            @endif
                            @endif
                            <li>
                                <a class="list-group-item d-flex justify-content-start align-items-start py-3 gap-2" href="{{ $urlAppend }}modules/message/index.php"><i class="fa-regular fa-envelope settings-icons"></i>{{ trans('langMyDropBox') }}</a>
                            </li>
                            <li>
                                <a class="list-group-item d-flex justify-content-start align-items-start py-3 gap-2" href="{{ $urlAppend }}main/personal_calendar/index.php"><i class="fa-regular fa-calendar settings-icons"></i>{{ trans('langMyAgenda') }}</a>
                            </li>
                            <li>
                                <a class="list-group-item d-flex justify-content-start align-items-start py-3 gap-2" href="{{ $urlAppend }}main/profile/display_profile.php"><i class="fa-solid fa-user settings-icons"></i> {{ trans('langMyProfile') }}</a>
                            </li>
                            @if((isset($collaboration_platform) and !$collaboration_platform) or is_null($collaboration_platform))
                            <li>
                                <a class="list-group-item d-flex justify-content-start align-items-start py-3 gap-2" href="{{ $urlAppend }}main/my_widgets.php"><i class="fa-solid fa-layer-group settings-icons"></i> {{ trans('langMyWidgets') }}</a>
                            </li>
                            @endif
                            @if((isset($collaboration_platform) and !$collaboration_platform) or is_null($collaboration_platform))
                            <li>
                                <a class="list-group-item d-flex justify-content-start align-items-start py-3 gap-2" href="{{ $urlAppend }}main/gradebookUserTotal/index.php"><i class="fa-solid fa-a settings-icons"></i> {{ trans('langGradeTotal') }}</a>
                            </li>
                            @endif
                            @if((isset($collaboration_platform) and !$collaboration_platform) or is_null($collaboration_platform))
                            <li>
                                <a class="list-group-item d-flex justify-content-start align-items-start py-3 gap-2" href="{{ $urlAppend }}main/mycertificates.php"><i class="fa-solid fa-award settings-icons"></i> {{ trans('langMyCertificates') }}</a>
                            </li>
                            @endif
                            @if (($session->status == USER_TEACHER and get_config('mydocs_teacher_enable')) or ($session->status == USER_STUDENT and get_config('mydocs_student_enable')) or ($session->status == ADMIN_USER and get_config('mydocs_teacher_enable')))
                            <li>
                                <a class="list-group-item d-flex justify-content-start align-items-start py-3 gap-2" href="{{ $urlAppend }}main/mydocs/index.php"><i class="fa-regular fa-file settings-icons"></i> {{ trans('langMyDocs') }}</a>
                            </li>
                            @endif
                            <li>
                                <form method='post' action='{{ $urlAppend }}modules/auth/logout.php' style='height:49px;'>
                                    <input type='hidden' name='token' value='{{ $_SESSION['csrf_token'] }}'>
                                    <button type='submit' class='list-group-item d-flex justify-content-start align-items-center py-3 w-100 text-end gap-2 logout-list-item' name='submit'><i class="fa-solid fa-arrow-right-from-bracket Accent-200-cl"></i>
                                    <span class='Accent-200-cl TextBold'>{{ trans('langLogout2') }}</span>
                                    </button>
                                </form>
                            </li>
                        </ul>
                    </div>
                </div>
            @endif

        </div>
    </div>






    <div class="offcanvas offcanvas-start d-lg-none offCanvas-Tools" tabindex="-1" id="offcanvasScrollingTools">
        <div class="offcanvas-header">
            <button type="button" class="btn-close text-reset" data-bs-dismiss="offcanvas" aria-label="Close"></button>
        </div>
        <div class="offcanvas-body px-4">
            <div class='col-12 d-flex justify-content-center align-items-center' aria-label='Logo'>
                <img src="{{ $logo_img_small }}" alt="Logo">
            </div>
            @if(get_config('enable_search'))
                <div class='col-12 mt-5'>
                    @if(isset($course_code) and $course_code)
                        <form action="{{ $urlAppend }}modules/search/search_incourse.php?all=true">
                    @else
                        <form action="{{ $urlAppend }}modules/search/search.php">
                    @endif
                            {{--
                            <input id='search-mobile' type="text" class="form-control w-100" placeholder="{{ trans('langGiveText')}}..." name="search_terms">
                            <button class="btn submitAdminBtnDefault mt-3 ms-auto me-auto" type="submit" name="quickSearch" aria-label='Quick search'>
                                {{ trans('langSubmit') }}
                            </button>--}}

                            <div class="input-group">
                                <input id='search-mobile' type="text" class="form-control mt-0" 
                                        placeholder="{{ trans('langSearch')}}..." name="search_terms" aria-label="Search terms" aria-describedby="search-btn-mobile">
                                <button class="btn btn-primary btn-mobile-quick-search" type="submit" id="search-btn-mobile" name="quickSearch" aria-label='Quick search'>
                                    <i class="fa-solid fa-magnifying-glass-arrow-right fa-lg"></i>
                                </button>
                            </div>

                        </form>
                </div>
            @endif
            <div class='col-12 mt-5'>

                    @if(!get_config('hide_login_link'))
                        <p class='py-2 px-0'>
                            <a id='homeId' class='header-mobile-link d-flex justify-content-start align-items-start gap-2 flex-wrap TextBold' type='button' href="{{ $urlServer }}?show_home=true" aria-label='Homepage'>
                                <i class="fa-solid fa-home"></i>{{ trans('langHome') }}
                            </a>
                        </p>
                    @endif
                    @if(!isset($_SESSION['uid']))
                        @if(get_config('registration_link')!='hide')
                            <p class='py-2 px-0'>
                                <a id='registrationId' type="button" class='header-mobile-link d-flex justify-content-start align-items-start gap-2 flex-wrap TextBold' href="{{ $urlAppend }}modules/auth/registration.php" aria-label='Registration'>
                                    <i class="fa-solid fa-pencil"></i>{{ trans('langRegistration') }}
                                </a>
                            </p>
                        @endif
                    @endif
                    @if (isset($_SESSION['uid']))
                        <p class='py-2 px-0'>
                            <a id='portfolioId' type="button" class='header-mobile-link d-flex justify-content-start align-items-start gap-2 flex-wrap TextBold' href="{{ $urlAppend }}main/portfolio.php" aria-label='Registration'>
                                <i class="fa-solid fa-pencil"></i>{{ trans('langPortfolio') }}
                            </a>
                        </p>
                    @endif

                    @if(!get_config('dont_display_courses_menu'))
                        <p class='py-2 px-0'>
                            <a id='coursesId' type='button' class='header-mobile-link d-flex justify-content-start align-items-start gap-2 flex-wrap TextBold' href="{{ $urlAppend }}modules/auth/listfaculte.php" aria-label='List of courses'>
                                <i class="fa-solid fa-book"></i>{{ trans('langCourses') }}
                            </a>
                        </p>
                    @endif

                    @if (faq_exist())
                        <p class='py-2 px-0'>
                            <a id='faqId' type='button' class='header-mobile-link d-flex justify-content-start align-items-start gap-2 flex-wrap TextBold' href="{{ $urlAppend }}info/faq.php" aria-label='Frequent questions'>
                                <i class="fa-solid fa-question-circle"></i>{{ trans('langFaq') }}
                            </a>
                        </p>
                    @endif

            </div>

        </div>
    </div>

</div>
</nav>


<script>
    let current_url = document.URL;

    localStorage.setItem("menu-item", "homepage");

    if(current_url.includes('/?redirect_home')){
        localStorage.setItem("menu-item", "homepage");
    }
    if(current_url.includes('/modules/auth/registration.php')
       || current_url.includes('/modules/auth/formuser.php')
       || current_url.includes('/modules/auth/newuser.php')
       || current_url.includes('/modules/auth/altnewuser.php')){
        localStorage.setItem("menu-item", "register");
    }
    if(current_url.includes('/modules/auth/opencourses.php')
        || current_url.includes('/modules/auth/listfaculte.php')){
        localStorage.setItem("menu-item", "lessons");
    }
    if(current_url.includes('/main/portfolio.php')){
        localStorage.setItem("menu-item", "portfolio");
    }
    if(current_url.includes('/info/faq.php')){
        localStorage.setItem("menu-item", "faq");
    }
    if(!current_url.includes('/modules/auth/registration.php')
       && !current_url.includes('/modules/auth/formuser.php')
       && !current_url.includes('/modules/auth/newuser.php')
       && !current_url.includes('/modules/auth/altnewuser.php')
       && !current_url.includes('/modules/auth/opencourses.php')
       && !current_url.includes('/modules/auth/listfaculte.php')
       && !current_url.includes('/main/portfolio.php')
       && !current_url.includes('/info/faq.php')
       && !current_url.includes('/?redirect_home')){
            localStorage.setItem("menu-item", "none");
    }



    if(localStorage.getItem("menu-item") == "homepage"){
        $('#link-home').addClass('active');
    }
    if(localStorage.getItem("menu-item") == "register"){
        $('#link-register').addClass('active');
    }
    if(localStorage.getItem("menu-item") == "portfolio"){
        $('#link-portfolio').addClass('active');
    }
    if(localStorage.getItem("menu-item") == "lessons"){
        $('#link-lessons').addClass('active');
    }
    if(localStorage.getItem("menu-item") == "faq"){
        $('#link-faq').addClass('active');
    }

    if($('#link-register').hasClass('active') || $('#link-portfolio').hasClass('active') || $('#link-lessons').hasClass('active') || $('#link-faq').hasClass('active')){
        $('#link-home').removeClass('active2');
    }

</script>



<script type='text/javascript'>
    $(document).ready(function() {

        $('.inputSearch').on('focus',function(){
            $('.container-items').addClass('d-none');
        });
        $('#btn-search').on('click',function(){
            setTimeout(function () {
                $('.container-items').removeClass('d-none');
            }, 500);
            setTimeout(function () {
                $('#submitSearch').submit();
            }, 200);
        });
        $(".inputSearch").focusout(function(){
            setTimeout(function () {
                $('.container-items').removeClass('d-none');
            }, 500);

        });
    });
</script>


