<nav id="bgr-cheat-header" class="navbar h-auto navbar-eclass">

        <div class='col-12 d-flex justify-content-center'>

            <div class="btn-group w-100" role="group" aria-label="Basic example">

                @if(!get_config('hide_login_link'))
                <a type="button" class="btn btn-transparent text-white" href="{{ $urlServer }}"><i class="fa fa-home"></i></a>
                @endif
                <a type="button" class="btn btn-transparent text-white" href="{{ $urlServer }}modules/auth/registration.php"><i class="fas fa-pen-nib"></i></a>
                <a type='button' class="btn btn-transparent text-white" href="{{ $urlServer }}modules/auth/listfaculte.php"><i class="fas fa-university"></i></a>
                @if(!$_SESSION['uid'])
                <a class='btn btn-transparent text-white' href="{{$urlAppend}}main/login_form.php"><span class="fa fa-lock"></span></a>
                @endif   

                {!! lang_selections() !!}
                

                @if($_SESSION['uid'])
                    
                        <button class="btn btn-transparent dropdown-toogle text-warning" type="button"
                                id="dropdownMenuButton1" data-bs-toggle="dropdown" aria-expanded="false">
                                <i class="fa fa-user"></i>                           
                        </button>

                        <ul class="dropdown-menu dropdown-menu-end dropdown_menu_user me-1 shadow-lg" aria-labelledby="dropdownMenuButton1">
                            <div class="d-flex justify-content-center text-white bg-primary p-2 mb-2" style='margin-top:-8px;'>
                                <i class="fa fa-user mt-1 pe-2"></i> <span class='text-white'>{{uid_to_am($uid)}}</span>
                            </div>
                            @if ((isset($is_admin) and $is_admin) or
                                (isset($is_power_user) and $is_power_user) or
                                (isset($is_usermanage_user) and ($is_usermanage_user)) or
                                (isset($is_departmentmanage_user) and $is_departmentmanage_user))
                                <li class='ps-1 pe-3 pt-2'>
                                    <a id="AdminToolBtn" type="button" class="dropdown-item fw-bold" href="{{ $urlAppend }}modules/admin/index.php"><i class="fas fa-wrench fs-6 text-warning pe-2"></i>{{trans('langAdminTool')}}</a>
                                </li>
                            @endif
                            @if($_SESSION['status'] == USER_TEACHER or $_SESSION['status'] == ADMIN_USER)
                               <li class='ps-1 pe-3 pt-2'><a class="dropdown-item fw-bold" href="{{ $urlAppend }}modules/create_course/create_course.php"><i class="fas fa-plus-circle fs-6 text-warning pe-2"></i>{{ trans('langCourseCreate') }}</a></li>
                            @endif
                            <li class='ps-1 pe-3 pt-2'>
                                <a class="dropdown-item fw-bold" href="{{ $urlAppend }}main/portfolio.php"><i class="fa fa-home fs-6 text-warning pe-2"></i>{{ trans('langMyPortfolio') }}</a>
                            </li>
                            <li class='ps-1 pe-3 pt-2'>
                                <a class="dropdown-item fw-bold" href="{{ $urlAppend }}main/my_courses.php"><i class="fas fa-graduation-cap fs-6 text-warning pe-2"></i>{{trans('langMyCoursesSide')}}</a>
                            </li>
                            <li class='ps-1 pe-3 pt-2'>
                                <a class="dropdown-item fw-bold" href="{{ $urlAppend }}modules/announcements/myannouncements.php"><i class="fas fa-bell fs-6 text-warning pe-2"></i>{{ trans('langMyAnnouncements') }}</a>
                            </li>
                            <li class='ps-1 pe-3 pt-2'>
                                <a class="dropdown-item fw-bold" href="{{ $urlAppend }}main/notes/index.php"><i class="fas fa-sticky-note fs-6 text-warning pe-2"></i>{{ trans('langNotes') }}</a>
                            </li>
                            @if (get_config('eportfolio_enable'))
                            <li class='ps-1 pe-3 pt-2'>
                                <a class="dropdown-item fw-bold" href="{{ $urlAppend }}main/eportfolio/index.php?id={{$uid}}&token={{ token_generate('eportfolio'.$uid) }}"><i class="fas fa-briefcase fs-6 text-warning pe-2"></i>{{ trans('langMyePortfolio') }}</a>
                            </li>
                            @endif
                            <li class='ps-1 pe-3 pt-2'>
                                <a class="dropdown-item fw-bold" href="{{ $urlAppend }}modules/usage/index.php?t=u"><i class="fas fa-chart-bar fs-6 text-warning pe-2"></i>{{ trans('langMyStats') }}</a>
                            </li>
                            @if (get_config('personal_blog'))
                            <li class='ps-1 pe-3 pt-2'>
                                <a class="dropdown-item fw-bold" href="{{ $urlAppend }}modules/blog/index.php?user_id={{$uid}}&token={{ token_generate('personal_blog'.$uid) }}"><i class="fas fa-location-arrow fs-6 text-warning pe-2"></i>{{ trans('langMyBlog') }}</a>
                            </li>
                            @endif
                            <li class='ps-1 pe-3 pt-2'>
                                <a class="dropdown-item fw-bold" href="{{ $urlAppend }}modules/message/index.php"><i class="fas fa-envelope fs-6 text-warning pe-2"></i>{{ trans('langMyDropBox') }}</a>
                            </li>
                            <li class='ps-1 pe-3 pt-2'>
                                <a class="dropdown-item fw-bold" href="{{ $urlAppend }}main/personal_calendar/index.php"><i class="fas fa-bell fs-6 text-warning pe-2"></i>{{ trans('langMyAgenda') }}</a>
                            </li>
                            <li class='ps-1 pe-3 pt-2'>
                                <a class="dropdown-item fw-bold" href="{{ $urlAppend }}main/profile/display_profile.php"><i class="fas fa-user fs-6 text-warning pe-2"></i> {{ trans('langMyProfile') }}</a>
                            </li>
                            <li class='ps-1 pe-3 pt-2'>
                                <a class="dropdown-item fw-bold" href="{{ $urlAppend }}main/my_widgets.php"><i class="fa fa-magic fa-fw fs-6 text-warning pe-2"></i> {{ trans('langMyWidgets') }}</a>
                            </li>
                            <li class='ps-1 pe-3 pt-2'>
                                <a class="dropdown-item fw-bold" href="{{ $urlAppend }}main/gradebookUserTotal/index.php"><i class="fa fa-sort-numeric-desc fa-fw fs-6 text-warning pe-2"></i> {{ trans('langGradeTotal') }}</a>
                            </li>
                            <li class='ps-1 pe-3 pt-2'>
                                <a class="dropdown-item fw-bold" href="{{ $urlAppend }}main/mycertificates.php"><i class="fa fa-trophy fa-fw fs-6 text-warning pe-2"></i> {{ trans('langMyCertificates') }}</a>
                            </li>
                            @if (($session->status == USER_TEACHER and get_config('mydocs_teacher_enable')) or ($session->status == USER_STUDENT and get_config('mydocs_student_enable')) or ($session->status == ADMIN_USER and get_config('mydocs_teacher_enable')))
                            <li class='ps-1 pe-3 pt-2'>
                                <a class="dropdown-item fw-bold" href="{{ $urlAppend }}main/mydocs/index.php"><i class="fas fa-folder fs-6 text-warning pe-2"></i> {{ trans('langMyDocs') }}</a>
                            </li>
                            @endif
                            <li>
                                <form method='post' action='{{ $urlAppend }}modules/auth/logout.php' style='height:14px;'>
                                    <input type='hidden' name='token' value='{{ $_SESSION['csrf_token'] }}'>
                                    <li>
                                        <button type='submit' class='d-flex justify-content-center btn btn-secondary w-100 mt-3' name='submit'><i class="fas fa-sign-out-alt pt-1"></i><span class='ps-2 fs-6 fw-bold text-white text-uppercase'>{{ trans('langLogout') }}</span></button>
                                    </li>
                                </form>
                            </li>
                        </ul>
                    
                @endif
            </div>
        </div>
</nav>