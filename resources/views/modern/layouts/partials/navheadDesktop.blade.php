<nav id="bgr-cheat-header" class="navbar navbar-eclass">

    <div class="container-fluid">

            <div class="col-xxl-2 col-xl-2 col-lg-2 col-md-0 col-sm-0 col-0">
                <a class="navbar-brand" href="{{ $urlAppend }}"><img class="eclass-nav-icon" src="{{ $logo_img }}"/></a>
            </div>

            <div class="col-xxl-10 col-xl-10 col-lg-10 col-md-0 col-sm-0 col-0">

                <div class='row p-2'>
                    <div class='d-flex justify-content-end'>
                        <div class="col-3">
                            @if(get_config('enable_search'))
                                <form action='{{ $urlAppend }}modules/search/{{ $search_action }}' method='post' >
                                    <div class="input-group mb-3">
                                        <input id="search_terms" type="text" class="border-0 form-control outline-0 text-white inputSearch " name="search_terms" placeholder="{{ trans('langSearch') }}..." aria-describedby="basic-inputSearch">
                                        <button id="btn-search" class="btn btn-primary" type="submit" name="quickSearch"><i class="fa fa-search text-white"></i></button>
                                        {!! lang_selections() !!}
                                    </div>
                                </form>
                            @endif
                        </div>
                    </div>
                </div>

                <div class='row p-2 mt-3'>

                    <div class="col-xl-8 col-lg-8 col-md-12 col-sm-12 col-12">
                        @if (isset($_SESSION['uid']))
                            <a class="eclass-nav-link text-white pt-5" href="{{ $urlServer }}"> <i class="fa fa-home"></i> {{ trans('langHome') }}</a>
                        @endif
                        <a class="eclass-nav-link text-white" href="{{ $urlServer }}modules/auth/registration.php"><i class="fas fa-pen-nib"></i> {{ trans('langRegistration') }}</a>
                        <a class="eclass-nav-link text-white" href="{{ $urlServer }}modules/auth/courses.php"><i class="fas fa-book"></i> {{ trans('langMyCoursesSide') }}</a>
                    </div>


                    <div class='col-xl-4 col-lg-4 col-md-12 col-sm-12 col-12 d-flex justify-content-end'>
                        @if (isset($_SESSION['uid']))
                            <div class="btn-group" role="group" aria-label="Button group with nested dropdown">
                                @if ((isset($is_admin) and $is_admin) or
                                    (isset($is_power_user) and $is_power_user) or
                                    (isset($is_usermanage_user) and ($is_usermanage_user)) or
                                    (isset($is_departmentmanage_user) and $is_departmentmanage_user))
                                    <a id="AdminToolBtn" type="button" class="btn btn-primary tempBtnButton" aria-haspopup="true"
                                            aria-expanded="false" data-bs-toggle="tooltip" data-bs-placement="left"
                                            title="{{trans('langAdminTool')}}" href="{{ $urlAppend }}modules/admin/index.php"><i class="fas fa-wrench text-white pt-1"></i>
                                    </a>
                                @endif

                                <div class="btn-group" role="group">
                                    <button id="btnGroupDrop1" type="button" class="btn btn-primary dropdown-toggle tempBtnButton"
                                            data-bs-toggle="dropdown" aria-expanded="false">
                                        <span><img class="user-icon-filename" src="{{ user_icon($uid, IMAGESIZE_LARGE) }}"
                                                alt="{{ $uname }}">{{ $uname }}</span>
                                    </button>
                                    <ul class="dropdown-menu dropdown-menu-end dropdown_menu_user" aria-labelledby="btnGroupDrop1">

                                        @if($_SESSION['status'] == USER_TEACHER or $_SESSION['status'] == ADMIN_USER)
                                        <li class='p-1'>
                                            <a class="dropdown-item" href="{{ $urlAppend }}modules/create_course/create_course.php">
                                                <div class='row'>
                                                    <div class='col-1'>
                                                        <span class="fas fa-plus-circle text-warning pt-1"></span>
                                                    </div>
                                                    <div class='col-10 ps-3 pe-3'>
                                                        <span class='text-white user_menu_tool'>
                                                            {{ trans('langCourseCreate') }}
                                                        </span>
                                                    </div>
                                                </div>
                                            </a>
                                        </li>
                                        @endif
                                        <li class='p-1'>
                                            <a class="dropdown-item" href="{{ $urlAppend }}main/portfolio.php">
                                                <div class='row'>
                                                    <div class='col-1'>
                                                        <span class="fas fa-home bg-transparent text-warning pt-1"></span>
                                                    </div>
                                                    <div class='col-10 ps-3 pe-3'>
                                                        <span class='text-white user_menu_tool'>
                                                            {{ trans('langMyPortfolio') }}
                                                        </span>
                                                    </div>
                                                </div>
                                            </a>
                                        </li>

                                        <li class='p-1'>
                                            <a class="dropdown-item" href="{{ $urlAppend }}main/my_courses.php">
                                                <div class='row'>
                                                    <div class='col-1'>
                                                        <span class="fas fa-graduation-cap text-warning pt-1"></span>
                                                    </div>
                                                    <div class='col-10 ps-3 pe-3'>
                                                        <span class='text-white user_menu_tool'>
                                                            {{ trans('langMyCoursesSide') }}
                                                        </span>
                                                    </div>
                                                </div>
                                            </a>
                                        </li>

                                        <li class='p-1'>
                                            <a class="dropdown-item" href="{{ $urlAppend }}modules/announcements/myannouncements.php">
                                                <div class='row'>
                                                    <div class='col-1'>
                                                        <span class="fas fa-bell text-warning pt-1"></span>
                                                    </div>
                                                    <div class='col-10 ps-3 pe-3'>
                                                        <span class='text-white user_menu_tool'>
                                                            {{ trans('langMyAnnouncements') }}
                                                        </span>
                                                    </div>
                                                </div>
                                            </a>
                                        </li>

                                        <li class='p-1'>
                                            <a class="dropdown-item" href="{{ $urlAppend }}main/notes/index.php">
                                                <div class='row'>
                                                    <div class='col-1'>
                                                        <span class="fas fa-sticky-note text-warning pt-1"></span>
                                                    </div>
                                                    <div class='col-10 ps-3 pe-3'>
                                                        <span class='text-white user_menu_tool'>
                                                            {{ trans('langNotes') }}
                                                        </span>
                                                    </div>
                                                </div>
                                            </a>
                                        </li>
                                        @if (get_config('eportfolio_enable'))
                                            <li class='p-1'>
                                                <a class="dropdown-item" href="{{ $urlAppend }}main/eportfolio/index.php?id={{$uid}}&token={{ token_generate('eportfolio'.$uid) }}">
                                                    <div class='row'>
                                                        <div class='col-1'>
                                                            <span class="fas fa-briefcase text-warning pt-1"></span>
                                                        </div>
                                                        <div class='col-10 ps-3 pe-3'>
                                                            <span class='text-white user_menu_tool'>
                                                                {{ trans('langMyePortfolio') }}
                                                            </span>
                                                        </div>
                                                    </div>
                                                </a>
                                            </li>
                                        @endif
                                        <li class='p-1'>
                                            <a class="dropdown-item" href="{{ $urlAppend }}modules/usage/index.php?t=u">
                                                <div class='row'>
                                                    <div class='col-1'>
                                                        <span class="fas fa-chart-bar text-warning pt-1"></span>
                                                    </div>
                                                    <div class='col-10 ps-3 pe-3'>
                                                        <span class='text-white user_menu_tool'>
                                                            {{ trans('langMyStats') }}
                                                        </span>
                                                    </div>
                                                </div>
                                            </a>
                                        </li>
                                        @if (get_config('personal_blog'))
                                            <li class='p-1'>
                                                <a class="dropdown-item" href="{{ $urlAppend }}modules/blog/index.php?user_id={{$uid}}&token={{ token_generate('personal_blog'.$uid) }}">
                                                    <div class='row'>
                                                        <div class='col-1'>
                                                            <span class="fas fa-location-arrow text-warning pt-1"></span>
                                                        </div>
                                                        <div class='col-10 ps-3 pe-3'>
                                                            <span class='text-white user_menu_tool'>
                                                                {{ trans('langMyBlog') }}
                                                            </span>
                                                        </div>
                                                    </div>
                                                </a>
                                            </li>
                                        @endif
                                        <li class='p-1'>
                                            <a class="dropdown-item" href="{{ $urlAppend }}modules/message/index.php">
                                                <div class='row'>
                                                    <div class='col-1'>
                                                        <span class="fas fa-envelope text-warning pt-1"></span>
                                                    </div>
                                                    <div class='col-10 ps-3 pe-3'>
                                                        <span class='text-white user_menu_tool'>
                                                            {{ trans('langMyDropBox') }}
                                                        </span>
                                                    </div>
                                                </div>
                                            </a>
                                        </li>
                                        <li class='p-1'>
                                            <a class="dropdown-item" href="{{ $urlAppend }}main/personal_calendar/index.php">
                                                <div class='row'>
                                                    <div class='col-1'>
                                                        <span class="fas fa-bell text-warning pt-1"></span>
                                                    </div>
                                                    <div class='col-10 ps-3 pe-3'>
                                                        <span class='text-white user_menu_tool'>
                                                            {{ trans('langMyAgenda') }}
                                                        </span>
                                                    </div>
                                                </div>
                                            </a>
                                        </li>
                                        <li class='p-1'>
                                            <a class="dropdown-item" href="{{ $urlAppend }}main/profile/display_profile.php">
                                                <div class='row'>
                                                    <div class='col-1'>
                                                        <span class="fas fa-user text-warning pt-1"></span>
                                                    </div>
                                                    <div class='col-10 ps-3 pe-3'>
                                                        <span class='text-white user_menu_tool'>
                                                            {{ trans('langMyProfile') }}
                                                        </span>
                                                    </div>
                                                </div>
                                            </a>
                                        </li>
                                        <li class='p-1'>
                                            <a class="dropdown-item" href="{{ $urlAppend }}main/my_widgets.php">
                                                <div class='row'>
                                                    <div class='col-1'>
                                                        <span class="fa fa-magic fa-fw text-warning pt-1"></span>
                                                    </div>
                                                    <div class='col-10 ps-3 pe-3'>
                                                        <span class='text-white user_menu_tool'>
                                                            {{ trans('langMyWidgets') }}
                                                        </span>
                                                    </div>
                                                </div>
                                            </a>
                                        </li>
                                        <li class='p-1'>
                                            <a class="dropdown-item" href="{{ $urlAppend }}main/gradebookUserTotal/index.php">
                                                <div class='row'>
                                                    <div class='col-1'>
                                                        <span class="fa fa-sort-numeric-desc fa-fw text-warning pt-1"></span>
                                                    </div>
                                                    <div class='col-10 ps-3 pe-3'>
                                                        <span class='text-white user_menu_tool'>
                                                            {{ trans('langGradeTotal') }}
                                                        </span>
                                                    </div>
                                                </div>
                                            </a>
                                        </li>
                                        <li class='p-1'>
                                            <a class="dropdown-item" href="{{ $urlAppend }}main/mycertificates.php">
                                                <div class='row'>
                                                    <div class='col-1'>
                                                        <span class="fa fa-trophy fa-fw text-warning pt-1"></span>
                                                    </div>
                                                    <div class='col-10 ps-3 pe-3'>
                                                        <span class='text-white user_menu_tool'>
                                                            {{ trans('langMyCertificates') }}
                                                        </span>
                                                    </div>
                                                </div>
                                            </a>
                                        </li>
                                        @if (($session->status == USER_TEACHER and get_config('mydocs_teacher_enable')) or ($session->status == USER_STUDENT and get_config('mydocs_student_enable')) or ($session->status == ADMIN_USER and get_config('mydocs_teacher_enable')))
                                            <li class='p-1'>
                                                <a class="dropdown-item" href="{{ $urlAppend }}main/mydocs/index.php">
                                                    <div class='row'>
                                                        <div class='col-1'>
                                                            <span class="fas fa-folder text-warning pt-1"></span>
                                                        </div>
                                                        <div class='col-10 ps-3 pe-3'>
                                                            <span class='text-white user_menu_tool'>
                                                                {{ trans('langMyDocs') }}
                                                            </span>
                                                        </div>
                                                    </div>
                                                </a>
                                            </li>
                                        @endif


                                        <form method='post' action='{{ $urlAppend }}modules/auth/logout.php' style='height:14px;'>
                                            <input type='hidden' name='token' value='{{ $_SESSION['csrf_token'] }}'>
                                            <li>
                                                <button type='submit' class='btn btn-secondary w-100 mt-3' name='submit'><i class="fas fa-sign-out-alt"></i><span class='ps-2 fs-6 text-white'>{{ trans('langLogout') }}</span></button>
                                            </li>
                                        </form>

                                    </ul>
                                </div>
                            </div>
                        @endif
                    </div>
                </div>
            </div>
    </div>
</nav>
