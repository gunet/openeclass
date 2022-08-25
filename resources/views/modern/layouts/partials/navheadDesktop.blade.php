<nav id="bgr-cheat-header" class="navbar navbar-eclass">

    <div class="container-fluid">

            <div class="col-xxl-2 col-xl-2 col-lg-2 col-md-0 col-sm-0 col-0">
                <a class="navbar-brand" href="{{ $urlAppend }}"><img class="eclass-nav-icon" src="{{ $logo_img }}"/></a>
            </div>

            <div class="col-xxl-10 col-xl-10 col-lg-10 col-md-0 col-sm-0 col-0">

                <div class='row p-2'>
                    <div class='d-flex justify-content-end'>
                        <div class="col-lg-4 col-xl-3 d-flex justify-content-end">
                            <form action='{{ $urlAppend }}modules/search/{{ $search_action }}' method='post' >
                                <div class="input-group mb-3">
                                    @if(get_config('enable_search'))
                                        <input id="search_terms" type="text" class="border-0 form-control outline-0 text-white inputSearch " name="search_terms" placeholder="{{ trans('langSearch') }}..." aria-describedby="basic-inputSearch">
                                        <button id="btn-search" class="btn btn-primary" type="submit" name="quickSearch"><i class="fa fa-search text-white"></i></button>
                                    @endif
                                    @if (!isset($_SESSION['uid']))
                                        <a class='btn btn-primary' href="{{$urlAppend}}main/login_form.php"><span class="fa fa-lock pt-1"></span></a>
                                    @endif
                                    {!! lang_selections() !!}
                                </div>
                            </form>
                        </div>
                    </div>
                </div>


                <div class='row p-2 mt-3'>

                    <div class="col-xl-8 col-lg-8 col-md-12 col-sm-12 col-12">
                        @if(!get_config('hide_login_link'))
                            <a class="eclass-nav-link text-white pt-5" href="{{ $urlServer }}"> <i class="fa fa-home"></i> {!! get_config('homepage_name') !!}</a>
                        @endif
                        <a class="eclass-nav-link text-white @if(get_config('registration_link')=='hide') d-none @endif" href="{{ $urlServer }}modules/auth/registration.php"><i class="fas fa-pen-nib"></i> {{ trans('langRegistration') }}</a>
                        <a class="eclass-nav-link text-white" href="{{ $urlServer }}modules/auth/listfaculte.php"><i class="fas fa-university"></i> {{ trans('langCourses') }}</a>
                    </div>


                    <div class='col-xl-4 col-lg-4 col-md-12 col-sm-12 col-12 d-flex justify-content-end'>
                        @if (isset($_SESSION['uid']))
                            <div class="btn-group" role="group" aria-label="Button group with nested dropdown">
                                @if ((isset($is_admin) and $is_admin) or
                                    (isset($is_power_user) and $is_power_user) or
                                    (isset($is_usermanage_user) and ($is_usermanage_user)) or
                                    (isset($is_departmentmanage_user) and $is_departmentmanage_user))
                                    <a id="AdminToolBtn" type="button" class="btn btn-primary" aria-haspopup="true"
                                            aria-expanded="false" data-bs-toggle="tooltip" data-bs-placement="left"
                                            title="{{trans('langAdminTool')}}" href="{{ $urlAppend }}modules/admin/index.php"><i class="fas fa-wrench text-white pt-1"></i>
                                    </a>
                                @endif

                                <div class="btn-group" role="group">
                                    <button id="btnGroupDrop1" type="button" class="btn btn-primary dropdown-toggle user-menu-btn"
                                            data-bs-toggle="dropdown" aria-expanded="false">
                                        <span><img class="user-icon-filename" src="{{ user_icon($uid, IMAGESIZE_LARGE) }}"
                                                alt="{{ $uname }}">{{ $uname }}</span>
                                    </button>
                                    <ul class="m-0 p-0 dropdown-menu dropdown-menu-end dropdown_menu_user shadow-lg bg-body border-0" aria-labelledby="btnGroupDrop1">
                                        @if($_SESSION['status'] == USER_TEACHER)
                                        <li>
                                            <a class="list-group-item border border-top-0 border-bottom-secondary" href="{{ $urlAppend }}modules/create_course/create_course.php">
                                                <div class='row'>
                                                    <div class='col-1'>
                                                        <span class="fas fa-plus-circle text-warning pt-1"></span>
                                                    </div>
                                                    <div class='col-10 ps-3 pe-3'>
                                                        <span class='fs-6 fw-bold'>
                                                            {{ trans('langCourseCreate') }}
                                                        </span>
                                                    </div>
                                                </div>
                                            </a>
                                        </li>
                                        @endif
                                        <li>
                                            <a class="list-group-item border border-top-0 border-bottom-secondary" href="{{ $urlAppend }}main/portfolio.php">
                                                <div class='row'>
                                                    <div class='col-1'>
                                                        <span class="fas fa-home bg-transparent text-warning pt-1"></span>
                                                    </div>
                                                    <div class='col-10 ps-3 pe-3'>
                                                        <span class='fs-6 fw-bold'>
                                                            {{ trans('langMyPortfolio') }}
                                                        </span>
                                                    </div>
                                                </div>
                                            </a>
                                        </li>

                                        <li>
                                            <a class="list-group-item border border-top-0 border-bottom-secondary" href="{{ $urlAppend }}main/my_courses.php">
                                                <div class='row'>
                                                    <div class='col-1'>
                                                        <span class="fas fa-graduation-cap text-warning pt-1"></span>
                                                    </div>
                                                    <div class='col-10 ps-3 pe-3'>
                                                        <span class='fs-6 fw-bold'>
                                                            {{ trans('langMyCoursesSide') }}
                                                        </span>
                                                    </div>
                                                </div>
                                            </a>
                                        </li>

                                        <li>
                                            <a class="list-group-item border border-top-0 border-bottom-secondary" href="{{ $urlAppend }}modules/announcements/myannouncements.php">
                                                <div class='row'>
                                                    <div class='col-1'>
                                                        <span class="fas fa-bell text-warning pt-1"></span>
                                                    </div>
                                                    <div class='col-10 ps-3 pe-3'>
                                                        <span class='fs-6 fw-bold'>
                                                            {{ trans('langMyAnnouncements') }}
                                                        </span>
                                                    </div>
                                                </div>
                                            </a>
                                        </li>

                                        <li>
                                            <a class="list-group-item border border-top-0 border-bottom-secondary" href="{{ $urlAppend }}main/notes/index.php">
                                                <div class='row'>
                                                    <div class='col-1'>
                                                        <span class="fas fa-sticky-note text-warning pt-1"></span>
                                                    </div>
                                                    <div class='col-10 ps-3 pe-3'>
                                                        <span class='fs-6 fw-bold'>
                                                            {{ trans('langNotes') }}
                                                        </span>
                                                    </div>
                                                </div>
                                            </a>
                                        </li>
                                        @if (get_config('eportfolio_enable'))
                                            <li>
                                                <a class="list-group-item border border-top-0 border-bottom-secondary" href="{{ $urlAppend }}main/eportfolio/index.php?id={{$uid}}&token={{ token_generate('eportfolio'.$uid) }}">
                                                    <div class='row'>
                                                        <div class='col-1'>
                                                            <span class="fas fa-briefcase text-warning pt-1"></span>
                                                        </div>
                                                        <div class='col-10 ps-3 pe-3'>
                                                            <span class='fs-6 fw-bold'>
                                                                {{ trans('langMyePortfolio') }}
                                                            </span>
                                                        </div>
                                                    </div>
                                                </a>
                                            </li>
                                        @endif
                                        <li>
                                            <a class="list-group-item border border-top-0 border-bottom-secondary" href="{{ $urlAppend }}modules/usage/index.php?t=u">
                                                <div class='row'>
                                                    <div class='col-1'>
                                                        <span class="fas fa-chart-bar text-warning pt-1"></span>
                                                    </div>
                                                    <div class='col-10 ps-3 pe-3'>
                                                        <span class='fs-6 fw-bold'>
                                                            {{ trans('langMyStats') }}
                                                        </span>
                                                    </div>
                                                </div>
                                            </a>
                                        </li>
                                        @if (get_config('personal_blog'))
                                            <li>
                                                <a class="list-group-item border border-top-0 border-bottom-secondary" href="{{ $urlAppend }}modules/blog/index.php?user_id={{$uid}}&token={{ token_generate('personal_blog'.$uid) }}">
                                                    <div class='row'>
                                                        <div class='col-1'>
                                                            <span class="fas fa-location-arrow text-warning pt-1"></span>
                                                        </div>
                                                        <div class='col-10 ps-3 pe-3'>
                                                            <span class='fs-6 fw-bold'>
                                                                {{ trans('langMyBlog') }}
                                                            </span>
                                                        </div>
                                                    </div>
                                                </a>
                                            </li>
                                        @endif
                                        <li>
                                            <a class="list-group-item border border-top-0 border-bottom-secondary" href="{{ $urlAppend }}modules/message/index.php">
                                                <div class='row'>
                                                    <div class='col-1'>
                                                        <span class="fas fa-envelope text-warning pt-1"></span>
                                                    </div>
                                                    <div class='col-10 ps-3 pe-3'>
                                                        <span class='fs-6 fw-bold'>
                                                            {{ trans('langMyDropBox') }}
                                                        </span>
                                                    </div>
                                                </div>
                                            </a>
                                        </li>
                                        <li>
                                            <a class="list-group-item border border-top-0 border-bottom-secondary" href="{{ $urlAppend }}main/personal_calendar/index.php">
                                                <div class='row'>
                                                    <div class='col-1'>
                                                        <span class="fas fa-bell text-warning pt-1"></span>
                                                    </div>
                                                    <div class='col-10 ps-3 pe-3'>
                                                        <span class='fs-6 fw-bold'>
                                                            {{ trans('langMyAgenda') }}
                                                        </span>
                                                    </div>
                                                </div>
                                            </a>
                                        </li>
                                        <li>
                                            <a class="list-group-item border border-top-0 border-bottom-secondary" href="{{ $urlAppend }}main/profile/display_profile.php">
                                                <div class='row'>
                                                    <div class='col-1'>
                                                        <span class="fas fa-user text-warning pt-1"></span>
                                                    </div>
                                                    <div class='col-10 ps-3 pe-3'>
                                                        <span class='fs-6 fw-bold'>
                                                            {{ trans('langMyProfile') }}
                                                        </span>
                                                    </div>
                                                </div>
                                            </a>
                                        </li>
                                        <li>
                                            <a class="list-group-item border border-top-0 border-bottom-secondary" href="{{ $urlAppend }}main/my_widgets.php">
                                                <div class='row'>
                                                    <div class='col-1'>
                                                        <span class="fa fa-magic fa-fw text-warning pt-1"></span>
                                                    </div>
                                                    <div class='col-10 ps-3 pe-3'>
                                                        <span class='fs-6 fw-bold'>
                                                            {{ trans('langMyWidgets') }}
                                                        </span>
                                                    </div>
                                                </div>
                                            </a>
                                        </li>
                                        <li>
                                            <a class="list-group-item border border-top-0 border-bottom-secondary" href="{{ $urlAppend }}main/gradebookUserTotal/index.php">
                                                <div class='row'>
                                                    <div class='col-1'>
                                                        <span class="fa fa-sort-numeric-desc fa-fw text-warning pt-1"></span>
                                                    </div>
                                                    <div class='col-10 ps-3 pe-3'>
                                                        <span class='fs-6 fw-bold'>
                                                            {{ trans('langGradeTotal') }}
                                                        </span>
                                                    </div>
                                                </div>
                                            </a>
                                        </li>
                                        <li>
                                            <a class="list-group-item border border-top-0 border-bottom-secondary" href="{{ $urlAppend }}main/mycertificates.php">
                                                <div class='row'>
                                                    <div class='col-1'>
                                                        <span class="fa fa-trophy fa-fw text-warning pt-1"></span>
                                                    </div>
                                                    <div class='col-10 ps-3 pe-3'>
                                                        <span class='fs-6 fw-bold'>
                                                            {{ trans('langMyCertificates') }}
                                                        </span>
                                                    </div>
                                                </div>
                                            </a>
                                        </li>
                                        @if (($session->status == USER_TEACHER and get_config('mydocs_teacher_enable')) or ($session->status == USER_STUDENT and get_config('mydocs_student_enable')) or ($session->status == ADMIN_USER and get_config('mydocs_teacher_enable')))
                                            <li>
                                                <a class="list-group-item border border-top-0 border-bottom-secondary" href="{{ $urlAppend }}main/mydocs/index.php">
                                                    <div class='row'>
                                                        <div class='col-1'>
                                                            <span class="fas fa-folder text-warning pt-1"></span>
                                                        </div>
                                                        <div class='col-10 ps-3 pe-3'>
                                                            <span class='fs-6 fw-bold'>
                                                                {{ trans('langMyDocs') }}
                                                            </span>
                                                        </div>
                                                    </div>
                                                </a>
                                            </li>
                                        @endif


                                        <li>
                                            <form method='post' action='{{ $urlAppend }}modules/auth/logout.php' style='height:15px;'>
                                                <input type='hidden' name='token' value='{{ $_SESSION['csrf_token'] }}'>
                                                <button class='w-100 list-group-item border border-top-0 border-bottom-0 bg-light text-end bg-light' type='submit' name='submit'>
                                                    <i class="fas fa-sign-out-alt fw-bold text-primary"></i>
                                                    <span class='fs-6 fw-bold text-dark'>{{ trans('langLogout') }}</span>
                                                </button>
                                            </form>
                                        </li>


                                    </ul>
                                </div>
                            </div>
                        @endif
                    </div>
                </div>
            </div>
    </div>
</nav>
