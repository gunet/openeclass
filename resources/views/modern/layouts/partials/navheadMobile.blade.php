<nav id="bgr-cheat-header" class="navbar h-auto navbar-eclass fixed-top py-0">
    <div class='{{ $container }} header-container py-0'>
        <div class='col-12 h-100 d-flex justify-content-between align-items-center'>

            <div class='d-flex justify-content-start align-items-center gap-2'>

                <button class="btn small-basic-size mobile-btn bg-default d-flex justify-content-center align-items-center" type="button" data-bs-toggle="offcanvas" data-bs-target="#offcanvasScrollingTools" aria-controls="offcanvasScrolling">
                    <i class='fa-solid fa-bars'></i>
                </button>

                <a class='d-flex justify-content-start align-items-center' type="button" href="{{ $urlServer }}">
                    <img class="eclass-nav-icon px-2 bg-transparent" src="{{ $logo_img_small }}">
                </a>
            </div>

            @if (!isset($_SESSION['uid']))
                <div class='d-flex justify-content-start align-items-center'>
                    @if(get_config('dont_display_login_form'))
                        <a class='d-flex align-items-center text-uppercase TextBold small-text me-2' href="{{ $urlAppend }}main/login_form.php">
                            <i class="fa-solid fa-user loginText"></i>
                            <span class='ms-2 loginText TextMedium hidden-xs'>{{ trans('langUserLogin') }}</span>
                        </a>
                    @endif
                    {!! lang_selections_Mobile() !!}
                </div>
            @endif


            @if(isset($_SESSION['uid']))
                <div>
                    <button class="btn btn-transparent p-0 dropdown-toogle d-flex justify-content-end align-items-center" type="button"
                            id="dropdownMenuButton1" data-bs-toggle="dropdown" aria-expanded="false">
                            <img class="user-icon-filename mt-0" src="{{ user_icon($_SESSION['uid'], IMAGESIZE_LARGE) }}" alt="{{ $uname }}">
                    </button>

                    <div class="m-0 p-3 dropdown-menu dropdown-menu-end contextual-menu contextual-menu-user contextual-border" aria-labelledby="dropdownMenuButton1">
                        <ul class="list-group list-group-flush dropdown_menu_user">
                            <li>
                                <a class="list-group-item d-flex justify-content-start align-items-start gap-2 py-2 px-1 pe-none">
                                    <img class="user-icon-filename" src="{{ user_icon($_SESSION['uid'], IMAGESIZE_LARGE) }}" alt="{{ $uname }}">
                                    <div>
                                        <h4 class='mb-0'>{{ $_SESSION['givenname'] }}&nbsp{{ $_SESSION['surname'] }}</h4>
                                        <p class='small-text Neutral-600-cl'>{{ $_SESSION['uname'] }}</p>
                                    </div>

                                </a>
                            </li>
                            @if ((isset($is_admin) and $is_admin) or
                                (isset($is_power_user) and $is_power_user) or
                                (isset($is_usermanage_user) and ($is_usermanage_user)) or
                                (isset($is_departmentmanage_user) and $is_departmentmanage_user))
                                <li>
                                    <a id="AdminToolBtn" type="button" class="list-group-item d-flex justify-content-start align-items-start py-3 gap-2" href="{{ $urlAppend }}modules/admin/index.php"><i class="fa-solid fa-gear settings-icons"></i>{{trans('langAdminTool')}}</a>
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
                            <li>
                                <a class="list-group-item d-flex justify-content-start align-items-start py-3 gap-2" href="{{ $urlAppend }}modules/usage/index.php?t=u"><i class="fa-solid fa-chart-line settings-icons"></i>{{ trans('langMyStats') }}</a>
                            </li>
                            @if (get_config('personal_blog'))
                            <li>
                                <a class="list-group-item d-flex justify-content-start align-items-start py-3 gap-2" href="{{ $urlAppend }}modules/blog/index.php?user_id={{$uid}}&token={{ token_generate('personal_blog'.$uid) }}"><i class="fa-solid fa-globe settings-icons"></i>{{ trans('langMyBlog') }}</a>
                            </li>
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
                            <li>
                                <a class="list-group-item d-flex justify-content-start align-items-start py-3 gap-2" href="{{ $urlAppend }}main/my_widgets.php"><i class="fa-solid fa-layer-group settings-icons"></i> {{ trans('langMyWidgets') }}</a>
                            </li>
                            <li>
                                <a class="list-group-item d-flex justify-content-start align-items-start py-3 gap-2" href="{{ $urlAppend }}main/gradebookUserTotal/index.php"><i class="fa-solid fa-a settings-icons"></i> {{ trans('langGradeTotal') }}</a>
                            </li>
                            <li>
                                <a class="list-group-item d-flex justify-content-start align-items-start py-3 gap-2" href="{{ $urlAppend }}main/mycertificates.php"><i class="fa-solid fa-award settings-icons"></i> {{ trans('langMyCertificates') }}</a>
                            </li>
                            @if (($session->status == USER_TEACHER and get_config('mydocs_teacher_enable')) or ($session->status == USER_STUDENT and get_config('mydocs_student_enable')) or ($session->status == ADMIN_USER and get_config('mydocs_teacher_enable')))
                            <li>
                                <a class="list-group-item d-flex justify-content-start align-items-start py-3 gap-2" href="{{ $urlAppend }}main/mydocs/index.php"><i class="fa-regular fa-file settings-icons"></i> {{ trans('langMyDocs') }}</a>
                            </li>
                            @endif
                            <li>
                                <form method='post' action='{{ $urlAppend }}modules/auth/logout.php' style='height:49px;'>
                                    <input type='hidden' name='token' value='{{ $_SESSION['csrf_token'] }}'>
                                    <button type='submit' class='list-group-item d-flex justify-content-start align-items-center py-3 w-100 text-end gap-2' name='submit'><i class="fa-solid fa-arrow-right-from-bracket Accent-200-cl"></i>
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
</nav>

<script type='text/javascript'>
    $(document).ready(function() {
        $('.toolHomePage').on('click',function(){
            var btnId = $(this).attr('id');
            $('#'+btnId).css("background-color",'#ffffff');
        });
    });
</script>
