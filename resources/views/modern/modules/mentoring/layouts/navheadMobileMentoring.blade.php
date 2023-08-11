<nav class="navbar h-auto navbar-eclass-mentoring fixed-top pt-0 pb-0">

        <div class='col-6 d-flex justify-content-space-between align-items-center'>
            
            <a class="btn btnMentoringMenu ms-2 rounded-1" type="button" data-bs-toggle="offcanvas" href="#mobileMenu" role="button" aria-controls="mobileMenu">
                <i class="fa fa-bars"></i>
            </a>
            <a id='MentoringgoToMentoringImg' class='d-flex justify-content-center align-items-center backToEclass' type="button" href="{{ $urlServer }}?mentoring_logout=yes">
                <img class="eclass-nav-icon ms-2 ps-2 pe-2" src="{{ $logo_img_small }}">
            </a>
        </div>

        <div class='col-6 d-flex justify-content-end align-items-end'>
            @if(!isset($_SESSION['uid']))
                @if(!get_config('mentoring_always_active'))
                <a id='MentoringgoToEclassPlatformBtnMobile' class='btn btnBackOpenEclass rounded-2 TextSemiBold me-4' href='{{ $urlAppend }}?goToMentoring=false'>
                    <span class='fa fa-reply'></span><span class='hidden-xs-mentoring hidden-md-mentoring TextBold'>&nbsp{{ trans('langExitMentoring')}}</span>
                </a>
                @endif
                <a id='goToLoginForm' class="text-uppercase TextSemiBold text-white small-text me-2 @if(!get_config('mentoring_always_active')) mb-1 @endif" href="{{ $urlAppend }}main/login_form.php">
                    <img class="UserLoginIcon2" src="{{ $urlAppend }}template/modern/img/user_login_mentoring.svg"> 
                    <span class="hidden-lg @if(!get_config('mentoring_always_active')) hidden-md-mentoring hidden-xs-mentoring TextBold @endif loginText">{{ trans('langLogIn') }}</span>
                </a>
                <div class="me-1 @if(get_config('mentoring_always_active')) languageMenuUser @endif">{!! lang_selections_Mobile() !!}</div>
            @endif
            @if(isset($_SESSION['uid']))

                <button class="btn btn-transparent dropdown-toogle d-inline-flex justify-content-center align-items-center me-1" type="button"
                        id="dropdownMenuButton1" data-bs-toggle="dropdown" aria-expanded="false">
                        <img class="user-icon-filename mt-0" src="{{ user_icon($_SESSION['uid'], IMAGESIZE_LARGE) }}" alt="{{ $uname }}">
                </button>

                <div class="m-0 p-3 dropdown-menu dropdown-menu-end contextual-menu contextual-border" aria-labelledby="dropdownMenuButton1">
                    <ul class="list-group list-group-flush dropdown_menu_user">
                        <li class='pe-none'>
                            <a class='list-group-item d-flex justify-content-center align-items-center py-3'><h4 class='mb-0'>{{ $_SESSION['uname'] }}</h4></a>
                        </li>

                        @if(isset($mentoring_platform) and $mentoring_platform and !get_config('mentoring_always_active'))
                            <li>
                                <a class="list-group-item d-flex justify-content-start align-items-start py-3 gap-2" href="{{ $urlAppend }}?goToMentoring=false">
                                    <i class="fa-solid fa-reply settings-icons"></i>
                                    Open Eclass
                                </a>
                            </li>
                        @endif

                        @if ((isset($is_admin) and $is_admin) or
                            (isset($is_power_user) and $is_power_user) or
                            (isset($is_usermanage_user) and ($is_usermanage_user)) or
                            (isset($is_departmentmanage_user) and $is_departmentmanage_user))
                            <li>
                                <a id="AdminToolBtn" type="button" class="list-group-item d-flex justify-content-start align-items-start py-3 gap-2" href="{{ $urlAppend }}modules/admin/index.php?goToMentoring=false"><i class="fa-solid fa-gear settings-icons"></i>{{trans('langAdminTool')}}</a>
                            </li>
                        @endif

                        
                        @if (get_config('eportfolio_enable'))
                            <li>
                                <a id='MentoringgoToEportfolio' class="list-group-item d-flex justify-content-start align-items-start py-3 gap-2" href="{{ $urlAppend }}main/eportfolio/index.php?id={{$uid}}&token={{ token_generate('eportfolio'.$uid) }}&fromMentoring=true"><i class="fa-regular fa-address-card settings-icons"></i>{{ trans('langMyePortfolio') }}</a>
                            </li>
                        @endif

                        
                        <li>
                            <a id="MentoringgoToMyProgram" class="list-group-item d-flex justify-content-start align-items-start py-3 gap-2" href="{{ $urlAppend }}modules/mentoring/programs/myprograms.php"><i class="fa-solid fa-tasks settings-icons"></i>{{ trans('langMyPrograms') }}</a>
                        </li>

                        
                        <li>
                            <a id="MentoringgoToMyProfile" class="list-group-item d-flex justify-content-start align-items-start py-3 gap-2" href="{{ $urlAppend }}modules/mentoring/profile/user_profile.php"><i class="fa-solid fa-user settings-icons"></i>{{ trans('langMyProfile') }}</a>
                        </li>

                        
                        @if (($session->status == USER_TEACHER and get_config('mydocs_teacher_enable')) or ($session->status == USER_STUDENT and get_config('mydocs_student_enable')) or ($session->status == ADMIN_USER and get_config('mydocs_teacher_enable')))
                            <li>
                                <a id='MentoringgoToMyDocs' class="list-group-item d-flex justify-content-start align-items-start py-3 gap-2" href="{{ $urlAppend }}modules/mentoring/programs/group/document/mydoc.php?mydocs=true"><i class="fa-regular fa-file settings-icons"></i> {{ trans('langMyDocs') }}</a>
                            </li>
                        @endif

                        <li>
                            <form method='post' action='{{ $urlAppend }}modules/auth/logout.php' style='height:14px;'>
                                <input type='hidden' name='token' value='{{ $_SESSION['csrf_token'] }}'>
                                <button type='submit' class='list-group-item d-flex justify-content-end align-items-center py-3 w-100 text-end gap-2' name='submit'><i class="fa-solid fa-arrow-right-from-bracket Accent-200-cl settings-icons"></i>
                                <span class='Accent-200-cl TextBold'>{{ trans('langLogout2') }}</span>
                                </button>
                            </form>
                        </li>
                    </ul>
                </div>

            @endif
        </div>

        
</nav>

<script>
    $('.MentoringgoToEclassPlatformBtnMobile').on('click',function(){
        localStorage.removeItem("MenuMentoring");
    });
    $('#MentoringgoToMentoringImg').on('click',function(){
        localStorage.setItem("MenuMentoring","home");
    });

    $('#MentoringgoToEportfolio,#MentoringgoToMyDocs,#AdminToolBtn').on('click',function(){
        localStorage.removeItem("MenuMentoring");
    });
    $('#MentoringgoToMyProgram').on('click',function(){
        localStorage.setItem("MenuMentoring","program");
    });
    $('#MentoringgoToMyProfile').on('click',function(){
        localStorage.setItem("MenuMentoring","profile");
    });
</script>
