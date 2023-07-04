<nav id="bgr-cheat-header" class="navbar h-auto navbar-eclass fixed-top pt-0 pb-0">

        <div class='col-12 d-flex justify-content-between align-items-center'>

            <div class='d-flex justify-content-start align-items-center'>
                
                <button class="btn btn-transparent ms-2" type="button" data-bs-toggle="offcanvas" data-bs-target="#offcanvasScrollingTools" aria-controls="offcanvasScrolling">
                    <i class='fa-solid fa-bars'></i>
                </button>

                <a class='d-flex justify-content-center align-items-center' type="button" href="{{ $urlServer }}">
                    <img class="eclass-nav-icon ms-1 px-2 bg-transparent" src="{{ $logo_img_small }}">
                </a>
            </div>
            
            @if (!isset($_SESSION['uid']))
                <div class='d-flex justify-content-start align-items-center'>
                    @if(get_config('dont_display_login_form'))
                        <a class='d-flex align-items-center text-uppercase TextSemiBold small-text me-2' href="{{ $urlAppend }}main/login_form.php">
                            <i class="fa-solid fa-user basic-value-cl"></i>
                            <span class='ms-2 loginText basic-value-cl TextMedium hidden-xs'>{{ trans('langUserLogin') }}</span>
                        </a>
                    @endif
                    {!! lang_selections_Mobile() !!}
                </div>
            @endif
            
            
            @if(isset($_SESSION['uid']))
                <div>
                    <button class="btn btn-transparent dropdown-toogle d-flex justify-content-end align-items-center" type="button"
                            id="dropdownMenuButton1" data-bs-toggle="dropdown" aria-expanded="false">
                            <img class="user-icon-filename mt-0 me-2" src="{{ user_icon($_SESSION['uid'], IMAGESIZE_LARGE) }}" alt="{{ $uname }}">
                    </button>

                    <ul class="m-0 p-0 dropdown-menu dropdown-menu-end dropdown_menu_user bg-body border-0 shadow-lg" aria-labelledby="dropdownMenuButton1">
                        <li>
                            <a class='list-group-item pe-none bg-light text-center'><span class='normalBlueText fs-6 fw-bold'>{{ $_SESSION['uname'] }}</span></a>
                        </li>
                        @if ((isset($is_admin) and $is_admin) or
                            (isset($is_power_user) and $is_power_user) or
                            (isset($is_usermanage_user) and ($is_usermanage_user)) or
                            (isset($is_departmentmanage_user) and $is_departmentmanage_user))
                            <li>
                                <a id="AdminToolBtn" type="button" class="list-group-item border border-top-0 border-bottom-secondary TextMedium" href="{{ $urlAppend }}modules/admin/index.php"><i class="fas fa-wrench pe-2"></i>{{trans('langAdminTool')}}</a>
                            </li>
                        @endif
                        @if ($_SESSION['status'] == USER_TEACHER or $is_power_user or $is_departmentmanage_user)
                            <li><a class="list-group-item border border-top-0 border-bottom-secondary TextMedium" href="{{ $urlAppend }}modules/create_course/create_course.php"><i class="fas fa-plus-circle pe-2"></i>{{ trans('langCourseCreate') }}</a></li>
                        @endif
                        <li>
                            <a class="list-group-item border border-top-0 border-bottom-secondary TextMedium" href="{{ $urlAppend }}main/portfolio.php"><i class="fa fa-home pe-2"></i>{{ trans('langMyPortfolio') }}</a>
                        </li>
                        <li>
                            <a class="list-group-item border border-top-0 border-bottom-secondary TextMedium" href="{{ $urlAppend }}main/my_courses.php"><i class="fas fa-graduation-cap pe-2"></i>{{trans('langMyCoursesSide')}}</a>
                        </li>
                        <li>
                            <a class="list-group-item border border-top-0 border-bottom-secondary TextMedium" href="{{ $urlAppend }}modules/announcements/myannouncements.php"><i class="fas fa-bell pe-2"></i>{{ trans('langMyAnnouncements') }}</a>
                        </li>
                        <li>
                            <a class="list-group-item border border-top-0 border-bottom-secondary TextMedium" href="{{ $urlAppend }}main/notes/index.php"><i class="fas fa-sticky-note pe-2"></i>{{ trans('langNotes') }}</a>
                        </li>
                        @if (get_config('eportfolio_enable'))
                        <li>
                            <a class="list-group-item border border-top-0 border-bottom-secondary TextMedium" href="{{ $urlAppend }}main/eportfolio/index.php?id={{$uid}}&token={{ token_generate('eportfolio'.$uid) }}"><i class="fas fa-briefcase pe-2"></i>{{ trans('langMyePortfolio') }}</a>
                        </li>
                        @endif
                        <li>
                            <a class="list-group-item border border-top-0 border-bottom-secondary TextMedium" href="{{ $urlAppend }}modules/usage/index.php?t=u"><i class="fas fa-chart-bar pe-2"></i>{{ trans('langMyStats') }}</a>
                        </li>
                        @if (get_config('personal_blog'))
                        <li>
                            <a class="list-group-item border border-top-0 border-bottom-secondary TextMedium" href="{{ $urlAppend }}modules/blog/index.php?user_id={{$uid}}&token={{ token_generate('personal_blog'.$uid) }}"><i class="fas fa-location-arrow pe-2"></i>{{ trans('langMyBlog') }}</a>
                        </li>
                        @endif
                        <li>
                            <a class="list-group-item border border-top-0 border-bottom-secondary TextMedium" href="{{ $urlAppend }}modules/message/index.php"><i class="fas fa-envelope pe-2"></i>{{ trans('langMyDropBox') }}</a>
                        </li>
                        <li>
                            <a class="list-group-item border border-top-0 border-bottom-secondary TextMedium" href="{{ $urlAppend }}main/personal_calendar/index.php"><i class="fas fa-bell pe-2"></i>{{ trans('langMyAgenda') }}</a>
                        </li>
                        <li>
                            <a class="list-group-item border border-top-0 border-bottom-secondary TextMedium" href="{{ $urlAppend }}main/profile/display_profile.php"><i class="fas fa-user pe-2"></i> {{ trans('langMyProfile') }}</a>
                        </li>
                        <li>
                            <a class="list-group-item border border-top-0 border-bottom-secondary TextMedium" href="{{ $urlAppend }}main/my_widgets.php"><i class="fa fa-magic fa-fw pe-2"></i> {{ trans('langMyWidgets') }}</a>
                        </li>
                        <li>
                            <a class="list-group-item border border-top-0 border-bottom-secondary TextMedium" href="{{ $urlAppend }}main/gradebookUserTotal/index.php"><i class="fa fa-sort-numeric-desc fa-fw pe-2"></i> {{ trans('langGradeTotal') }}</a>
                        </li>
                        <li>
                            <a class="list-group-item border border-top-0 border-bottom-secondary TextMedium" href="{{ $urlAppend }}main/mycertificates.php"><i class="fa fa-trophy fa-fw pe-2"></i> {{ trans('langMyCertificates') }}</a>
                        </li>
                        @if (($session->status == USER_TEACHER and get_config('mydocs_teacher_enable')) or ($session->status == USER_STUDENT and get_config('mydocs_student_enable')) or ($session->status == ADMIN_USER and get_config('mydocs_teacher_enable')))
                        <li>
                            <a class="list-group-item border border-top-0 border-bottom-secondary TextMedium" href="{{ $urlAppend }}main/mydocs/index.php"><i class="fas fa-folder pe-2"></i> {{ trans('langMyDocs') }}</a>
                        </li>
                        @endif
                        <li>
                            <form method='post' action='{{ $urlAppend }}modules/auth/logout.php' style='height:14px;'>
                                <input type='hidden' name='token' value='{{ $_SESSION['csrf_token'] }}'>
                                <button type='submit' class='w-100 list-group-item border border-top-0 border-bottom-0 bg-light text-end bg-light' name='submit'><i class="fas fa-sign-out-alt fw-bold lightBlueText"></i>
                                <span class='fs-6 fw-bold text-dark'>{{ trans('langLogout') }}</span></button>
                            </form>
                        </li>
                    </ul>
                </div>
            @endif
            
        </div>
</nav>




<script type='text/javascript'>
    $(document).ready(function() {
        $('.toolHomePage').on('click',function(){
            var btnId = $(this).attr('id');
            $('#'+btnId).css("background-color",'#ffffff');
        });
    });
</script>