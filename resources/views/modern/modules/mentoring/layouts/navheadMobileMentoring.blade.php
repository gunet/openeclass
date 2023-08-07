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
                    <span class='fa fa-reply'></span><span class='hidden-xs-mentoring hidden-md-mentoring'>&nbsp{{ trans('langExitMentoring')}}</span>
                </a>
                @endif
                <a id='goToLoginForm' class="text-uppercase TextSemiBold text-white small-text me-2 @if(!get_config('mentoring_always_active')) mb-1 @endif" href="{{ $urlAppend }}main/login_form.php">
                    <img class="UserLoginIcon2" src="{{ $urlAppend }}template/modern/img/user_login_mentoring.svg"> 
                    <span class="hidden-lg @if(!get_config('mentoring_always_active')) hidden-md-mentoring hidden-xs-mentoring @endif loginText">{{ trans('langLogIn') }}</span>
                </a>
                <div class="me-1 @if(get_config('mentoring_always_active')) languageMenuUser @endif">{!! lang_selections_Mobile() !!}</div>
            @endif
            @if(isset($_SESSION['uid']))

                <button class="btn btn-transparent dropdown-toogle d-inline-flex justify-content-center align-items-center me-1" type="button"
                        id="dropdownMenuButton1" data-bs-toggle="dropdown" aria-expanded="false">
                        
                        <img class="user-icon-filename mt-0" src="{{ user_icon($_SESSION['uid'], IMAGESIZE_LARGE) }}" alt="{{ $uname }}">
                </button>

                <ul class="m-0 p-0 dropdown-menu dropdown-menu-end dropdown_menu_user_mentoring bg-body border-0 shadow-lg" aria-labelledby="dropdownMenuButton1">
                    <li>
                        <a class='list-group-item pe-none bg-light text-center'><i class="fa fa-user lightBlueText mt-1 pe-2"></i> <span class='lightBlueText fs-6 fw-bold text-uppercase'>{{ $_SESSION['uname'] }}</span></a>
                    </li>
                    @if ((isset($is_admin) and $is_admin) or
                        (isset($is_power_user) and $is_power_user) or
                        (isset($is_usermanage_user) and ($is_usermanage_user)) or
                        (isset($is_departmentmanage_user) and $is_departmentmanage_user))
                        <li>
                            <a id="AdminToolBtn" type="button" class="list-group-item border border-top-0 border-bottom-secondary TextMedium" href="{{ $urlAppend }}modules/admin/index.php?goToMentoring=false"><i class="fas fa-wrench pe-2"></i>{{trans('langAdminTool')}}</a>
                        </li>
                    @endif

                    
                    @if (get_config('eportfolio_enable'))
                        <li>
                            <a id='MentoringgoToEportfolio' class="list-group-item border border-top-0 border-bottom-secondary TextMedium" href="{{ $urlAppend }}main/eportfolio/index.php?id={{$uid}}&token={{ token_generate('eportfolio'.$uid) }}&fromMentoring=true"><i class="fas fa-briefcase pe-2"></i>{{ trans('langMyePortfolio') }}</a>
                        </li>
                    @endif

                    
                    <li>
                        <a id="MentoringgoToMyProgram" class="list-group-item border border-top-0 border-bottom-secondary TextMedium" href="{{ $urlAppend }}modules/mentoring/programs/myprograms.php"><i class="fas fa-tasks pe-2"></i>{{ trans('langMyPrograms') }}</a>
                    </li>

                    
                    <li>
                        <a id="MentoringgoToMyProfile" class="list-group-item border border-top-0 border-bottom-secondary TextMedium" href="{{ $urlAppend }}modules/mentoring/profile/user_profile.php"><i class="fas fa-user pe-2"></i>{{ trans('langMyProfile') }}</a>
                    </li>

                    
                    @if (($session->status == USER_TEACHER and get_config('mydocs_teacher_enable')) or ($session->status == USER_STUDENT and get_config('mydocs_student_enable')) or ($session->status == ADMIN_USER and get_config('mydocs_teacher_enable')))
                        <li>
                            <a id='MentoringgoToMyDocs' class="list-group-item border border-top-0 border-bottom-secondary TextMedium" href="{{ $urlAppend }}modules/mentoring/programs/group/document/mydoc.php?mydocs=true"><i class="fas fa-folder pe-2"></i> {{ trans('langMyDocs') }}</a>
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
