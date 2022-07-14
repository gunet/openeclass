<nav id="bgr-cheat-header" class="navbar h-auto navbar-eclass">

        <div class='col-12 d-flex justify-content-center'>

            <div class="btn-group w-100" role="group" aria-label="Basic example">

            @if (isset($_SESSION['uid']))
                <a type="button" class="btn btn-transparent text-white" href="{{ $urlServer }}"><i class="fa fa-home"></i></a>
            @endif

            <a type="button" class="btn btn-transparent text-white" href="{{ $urlServer }}modules/auth/registration.php"><i class="fas fa-pen-nib"></i></a>
            <a type='button' class="btn btn-transparent text-white" href="{{ $urlServer }}modules/auth/courses.php"><i class="fas fa-book"></i></a>

            {!! lang_selections() !!}

            @if($_SESSION['uid'])
                <ul class="dropdown-menu dropdown-menu-end dropdown_menu_user" aria-labelledby="dropdownMenuButton1">
                    <li class="bg-warning w-100" style="margin-top:-8px; height:35px;">
                        <a class="d-flex justify-content-center text-white pt-1"><i class="fa fa-user mt-1 pe-2"></i> {{uid_to_am($uid)}}</a>
                    </li>
                    <div class='row p-2'></div>
                    @if ((isset($is_admin) and $is_admin) or
                        (isset($is_power_user) and $is_power_user) or
                        (isset($is_usermanage_user) and ($is_usermanage_user)) or
                        (isset($is_departmentmanage_user) and $is_departmentmanage_user))
                        <li>
                            <a id="AdminToolBtn" type="button" class="dropdown-item text-white" href="{{ $urlAppend }}modules/admin/index.php"><i class="fas fa-wrench fs-6 text-warning pe-2"></i>{{trans('langAdminTool')}}</a>
                        </li>
                    @endif
                    @if($_SESSION['status'] == USER_TEACHER or $_SESSION['status'] == ADMIN_USER)
                       <a class="dropdown-item text-white" href="{{ $urlAppend }}modules/create_course/create_course.php"><i class="fas fa-plus-circle fs-6 text-warning pe-2"></i>{{ trans('langCourseCreate') }}</a>
                    @endif
                    <li>
                        <a class="dropdown-item text-white" href="{{ $urlAppend }}main/portfolio.php"><i class="fa fa-home fs-6 text-warning pe-2"></i>{{ trans('langMyPortfolio') }}</a>
                    </li>
                    <li>
                        <a class="dropdown-item text-white" href="{{ $urlAppend }}main/my_courses.php"><i class="fas fa-graduation-cap fs-6 text-warning pe-2"></i>{{trans('langMyCoursesSide')}}</a>
                    </li>
                    <li>
                        <a class="dropdown-item text-white" href="{{ $urlAppend }}modules/announcements/myannouncements.php"><i class="fas fa-bell fs-6 text-warning pe-2"></i>{{ trans('langMyAnnouncements') }}</a>
                    </li>
                    <li>
                        <a class="dropdown-item text-white" href="{{ $urlAppend }}main/notes/index.php"><i class="fas fa-sticky-note fs-6 text-warning pe-2"></i>{{ trans('langNotes') }}</a>
                    </li>
                    @if (get_config('eportfolio_enable'))
                        <li>
                            <a class="dropdown-item text-white" href="{{ $urlAppend }}main/eportfolio/index.php?id={{$uid}}&token={{ token_generate('eportfolio'.$uid) }}"><i class="fas fa-briefcase fs-6 text-warning pe-2"></i>{{ trans('langMyePortfolio') }}</a>
                        </li>
                    @endif
                    <li>
                        <a class="dropdown-item text-white" href="{{ $urlAppend }}modules/usage/index.php?t=u"><i class="fas fa-chart-bar fs-6 text-warning pe-2"></i>{{ trans('langMyStats') }}</a>
                    </li>
                    @if (get_config('personal_blog'))
                        <li>
                            <a class="dropdown-item text-white" href="{{ $urlAppend }}modules/blog/index.php?user_id={{$uid}}&token={{ token_generate('personal_blog'.$uid) }}"><i class="fas fa-location-arrow fs-6 text-warning pe-2"></i>{{ trans('langMyBlog') }}</a>
                        </li>
                    @endif
                    <li>
                        <a class="dropdown-item text-white" href="{{ $urlAppend }}modules/message/index.php"><i class="fas fa-envelope fs-6 text-warning pe-2"></i>{{ trans('langMyDropBox') }}</a>
                    </li>
                    <li>
                        <a class="dropdown-item text-white" href="{{ $urlAppend }}main/personal_calendar/index.php"><i class="fas fa-bell fs-6 text-warning pe-2"></i>{{ trans('langMyAgenda') }}</a>
                    </li>
                    <li>
                        <a class="dropdown-item text-white" href="{{ $urlAppend }}main/profile/display_profile.php"><i class="fas fa-user fs-6 text-warning pe-2"></i> {{ trans('langMyProfile') }}</a>
                    </li>
                    @if (($session->status == USER_TEACHER and get_config('mydocs_teacher_enable')) or ($session->status == USER_STUDENT and get_config('mydocs_student_enable')) or ($session->status == ADMIN_USER and get_config('mydocs_teacher_enable')))
                        <li>
                            <a class="dropdown-item text-white" href="{{ $urlAppend }}main/mydocs/index.php"><i class="fas fa-folder fs-6 text-warning pe-2"></i> {{ trans('langMyDocs') }}</a>
                        </li>
                    @endif
                    <li>
                        <form method='post' action='{{ $urlAppend }}modules/auth/logout.php' style='height:14px;'>
                            <input type='hidden' name='token' value='{{ $_SESSION['csrf_token'] }}'>
                            <li>
                                <button type='submit' class='d-flex justify-content-center btn btn-dark w-100 mt-3' name='submit'><i class="fas fa-sign-out-alt pt-1"></i><span class='ps-2 fs-6 text-white'>{{ trans('langLogout') }}</span></button>
                            </li>
                        </form>
                    </li>
                </ul>
            @endif
        </div>
    </div>
</nav>
