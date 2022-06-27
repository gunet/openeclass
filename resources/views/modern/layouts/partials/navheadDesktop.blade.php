<nav id="bgr-cheat-header" class="navbar navbar-eclass">


    <div class="col-xxl-12 col-xl-12 col-lg-12 col-md-12 col-sm-12 col-12">

        @if(get_config('enable_search'))
            <form action='{{ $urlAppend }}modules/search/{{ $search_action }}' method='post' >
                <div class="float-end mt-3">

                        <input type="text" class="w-50 input-group-input inputSearch" id="search_terms" name="search_terms" placeholder="{{ trans('langSearch') }}...">
                        <button id="btn-search" class="eclass-nav-link mt-0 me-4 border-0" type="submit" name="quickSearch"><i class="fa fa-search text-white" style='margin-left:-15px;'></i></button>
                        <div class="btn-group d-inline me-2">
                            <a onclick="changeLang('el')" href="{{$urlAppend}}index.php?localize=el" class="GreekButton btn btn-primary rounded-circle">el</a>
                            <a onclick="changeLang('en')" href="{{$urlAppend}}index.php?localize=en" class="EnglishButton btn btn-transparent border-primary rounded-circle text-white">en</a>
                        </div>

                </div>
            </form>
        @endif

        @if ((isset($is_admin) and $is_admin) or
        (isset($is_power_user) and $is_power_user) or
        (isset($is_usermanage_user) and ($is_usermanage_user)) or
        (isset($is_departmentmanage_user) and $is_departmentmanage_user))
            <a id="AdminToolBtn" type="button" class="float-end eclass-nav-link" aria-haspopup="true"
                    aria-expanded="false" data-bs-toggle="tooltip" data-bs-placement="left"
                    title="{{trans('langAdminTool')}}" href="{{ $urlAppend }}modules/admin/index.php"><i class="fas fa-wrench text-white"></i>
            </a>
        @endif



        <!-- <div class="dropdown">
            <a class="mt-0 float-end eclass-nav-link" type="button" href="#dropdownLanguage"
                    data-bs-toggle="dropdown" aria-expanded="false" aria-haspopup="true"
                    data-bs-toggle-second="tooltip" data-bs-placement="left" title="{{trans('langLanguage')}}">
                <i class="fa fa-globe"></i>
            </a>
            <ul class="dropdown-menu dropdown-language-ul" aria-labelledby="dropdownLanguage">
                <li class="language-li">
                    <a class="language-item" href="{{$urlAppend}}index.php?localize=el"><i class="fas fa-language"></i> {{ trans('langGreek') }}</a>
                </li>
                <li class="language-li">
                    <a class="language-item" href="{{$urlAppend}}index.php?localize=en"><i class="fas fa-language"></i> {{ trans('langEnglish') }}</a>
                </li>
                <li class="language-li">
                    <a class="language-item" href="{{$urlAppend}}index.php?localize=fr"><i class="fas fa-language"></i> {{ trans('langFrench') }}</a>
                </li>
                <li class="language-li">
                    <a class="language-item" href="{{$urlAppend}}index.php?localize=de"><i class="fas fa-language"></i> {{ trans('langGerman') }}</a>
                </li>
                <li class="language-li">
                    <a class="language-item" href="{{$urlAppend}}index.php?localize=it"><i class="fas fa-language"></i> {{ trans('langItalian') }}</a>
                </li>
                <li class="language-li">
                    <a class="language-item" href="{{$urlAppend}}index.php?localize=es"><i class="fas fa-language"></i> {{ trans('langSpanish') }}</a>
                </li>
            </ul>
        </div> -->

    </div>



    <div class="container-fluid mobileDefaultToolsNav">

        <div class="col-xxl-1 col-xl-1 col-lg-1 col-md-2 col-sm-2 col-2">
            <a class="navbar-brand" href="{{ $urlAppend }}"><img class="eclass-nav-icon" src="{{ $logo_img }}"/></a>
        </div>

        <div class="col-xxl-11 col-xl-11 col-lg-11 col-md-10 col-sm-10 col-10 col-nav-tools">
            <div class='row p-2'>

                <div class="col-xl-9 col-lg-9 col-md-6 col-sm-12 col-12">
                    @if (isset($_SESSION['uid']))
                        <a class="eclass-nav-link text-white" href="{{ $urlServer }}"> <i class="fas fa-home"></i> {{ trans('langHome') }}</a>
                    @endif
                    <a class="eclass-nav-link text-white" href="{{ $urlServer }}modules/auth/registration.php"><i class="fas fa-pen-nib"></i> {{ trans('registration') }}</a>
                    @if (isset($_SESSION['uid']))
                        @if($_SESSION['status'] == USER_TEACHER or $_SESSION['status'] == ADMIN_USER)
                            <a class="eclass-nav-link register_class_header" href="{{ $urlAppend }}modules/create_course/create_course.php"><i class="fas fa-plus-circle"></i>{{ trans('langCourseCreate') }}</a>
                        @endif
                    @endif
                    <a class="eclass-nav-link courses_class_header" href="{{ $urlServer }}modules/auth/courses.php"><i class="fas fa-book"></i> {{ trans('langCourses') }}</a>
                </div>

                <div class='col-xl-3 col-lg-3 col-md-6 col-sm-12 col-12'>
                    @if (isset($_SESSION['uid']))
                        <div class="dropdown">
                            <button class="btn btn-primary dropdown-toggle float-end dropdown_user_menu" type="button"
                                    id="dropdownMenuButton1" data-bs-toggle="dropdown" aria-expanded="false">
                                <span><img class="user-icon-filename" src="{{ user_icon($uid, IMAGESIZE_LARGE) }}"
                                        alt="{{ $uname }}">{{uid_to_am($uid)}}</span>
                            </button>
                            @if ((isset($is_admin) and $is_admin) or
                            (isset($is_power_user) and $is_power_user) or
                            (isset($is_usermanage_user) and ($is_usermanage_user)) or
                            (isset($is_departmentmanage_user) and $is_departmentmanage_user))
                                <ul class="dropdown-menu me-0 mt-5 dropdown-user-menu-ul"
                                    aria-labelledby="dropdownMenuButton1">
                            @else
                                <ul class="dropdown-menu me-0 mt-5 dropdown-user-menu-ul" aria-labelledby="dropdownMenuButton1">
                            @endif
                                <li class="user-menu-li">
                                    <a class="user-item text-white" href="{{ $urlAppend }}main/portfolio.php"><i class="fas fa-home bg-transparent text-white"></i> {{ trans('langMyPortfolio') }}</a>
                                </li>
                                <li class="user-menu-li">
                                    <a class="user-item text-white" href="{{ $urlAppend }}main/my_courses.php"><i class="fas fa-graduation-cap"></i> {{trans('mycourses')}}</a>
                                </li>
                                <li class="user-menu-li">
                                    <a class="user-item text-white"
                                    href="{{ $urlAppend }}modules/announcements/myannouncements.php"><i class="fas fa-bell"></i> {{ trans('langMyAnnouncements') }}</a>
                                </li>
                                <li class="user-menu-li">
                                    <a class="user-item text-white" href="{{ $urlAppend }}main/notes/index.php"><i class="fas fa-sticky-note"></i> {{ trans('langNotes') }}</a>
                                </li>
                                <li class="user-menu-li">
                                    <a class="user-item text-white"
                                    href="{{ $urlAppend }}main/eportfolio/index.php?id={{$uid}}&token={{ token_generate('eportfolio'.$uid) }}"><i class="fas fa-briefcase"></i> {{ trans('langMyePortfolio') }}</a>
                                </li>
                                <li class="user-menu-li">
                                    <a class="user-item text-white" href="{{ $urlAppend }}modules/usage/index.php?t=u"><i class="fas fa-chart-bar"></i> {{ trans('langMyStats') }}</a>
                                </li>
                                <li class="user-menu-li">
                                    <a class="user-item text-white"
                                    href="{{ $urlAppend }}modules/blog/index.php?user_id={{$uid}}&token={{ token_generate('personal_blog'.$uid) }}"><i
                                                class="fas fa-location-arrow"></i> {{ trans('langMyBlog') }}</a>
                                </li>
                                <li class="user-menu-li">
                                    <a class="user-item text-white" href="{{ $urlAppend }}modules/message/index.php"><i class="fas fa-envelope"></i> {{ trans('langMyDropBox') }}</a>
                                </li>
                                <li class="user-menu-li">
                                    <a class="user-item text-white" href="{{ $urlAppend }}main/personal_calendar/index.php"><i class="fas fa-bell"></i> {{ trans('langMyAgenda') }}</a>
                                </li>
                                <li class="user-menu-li">
                                    <a class="user-item text-white" href="{{ $urlAppend }}main/profile/display_profile.php"><i class="fas fa-user"></i> {{ trans('langMyProfile') }}</a>
                                </li>
                                <li class="user-menu-li">
                                    <a class="user-item text-white" href="{{ $urlAppend }}main/mydocs/index.php"><i class="fas fa-folder"></i> {{ trans('langMyDocs') }}</a>
                                </li>
                                <li class="user-menu-li">
                                    <a class="LogoutButton user-item text-white" href="{{ $urlAppend }}?logout=yes"><i class="fas fa-unlock"></i> {{ trans('langLogout') }}</a>
                                </li>
                            </ul>
                        </div>
                   @endif
                </div>
            </div>
        </div>
    </div>
</nav>
