<nav id="bgr-cheat-header" class="navbar navbar-eclass py-0">
    <div class='col-12 h-100 d-flex justify-content-between align-items-center'>
        <div class='d-flex justify-content-start align-items-center h-100'>
            <a id='imgEclassBtn' class="navbar-brand ms-lg-4 me-lg-4 me-xl-5" href="{{ $urlAppend }}?goToMentoring=true">
                <img class="eclass-nav-icon m-auto d-block" src="{{ $logo_img }}"/>
            </a>
            @include('modules.mentoring.common.common_menu_list')
        </div>
        <div class='d-flex justify-content-end align-items-center h-100 pe-4'>
            @if(!isset($_SESSION['uid']))
                @if(!get_config('mentoring_always_active'))
                    <a id='goToEclassPlatformBtn' class='btn btnBackOpenEclass rounded-2 TextSemiBold me-4' href='{{ $urlAppend }}?goToMentoring=false'>
                        <span class='fa fa-reply'></span><span class='hidden-lg'>&nbsp{{ trans('langExitMentoring')}}</span>
                    </a>
                @endif
                <a id='goToLoginForm' class='text-uppercase TextSemiBold text-white small-text me-4' href="{{ $urlAppend }}main/login_form.php">
                    <img class="UserLoginIcon2" src="{{ $urlAppend }}template/modern/img/user_login_mentoring.svg"> 
                    <span class='hidden-lg loginText'>{{ trans('langLogIn') }}</span>
                </a>
                {!! lang_selections_Desktop() !!}
            @else
                <div class='user-menu-content h-100 d-flex justify-content-start align-items-center'>
                    <div class='d-flex justify-content-start align-items-center h-80px'>
                        <div class='d-flex justify-content-end p-0 h-80px'>
                            <div class="btn-group" role="group" aria-label="Button group with nested dropdown">
                                <button id="btnGroupDrop1" type="button" class="btn user-menu-btn rounded-0 d-flex justify-content-center align-items-center gap-2 rounded-0" data-bs-toggle="dropdown" aria-expanded="false">
                                        <img class="user-icon-filename" src="{{ user_icon($_SESSION['uid'], IMAGESIZE_LARGE) }}" alt="{{ $uname }}">
                                        <span class='TextBold user-name'>{{ $_SESSION['uname'] }}</span>
                                        <i class="fa-solid fa-chevron-down"></i>
                                </button>

                                <div class="m-0 p-3 dropdown-menu dropdown-menu-end contextual-menu contextual-menu-user" aria-labelledby="btnGroupDrop1">
                                    
                                    <ul class="list-group list-group-flush">

                                        @if(isset($mentoring_platform) and $mentoring_platform and !get_config('mentoring_always_active'))
                                            <li>
                                                <a class="list-group-item d-flex justify-content-start align-items-center gap-2 py-3" href="{{ $urlAppend }}?goToMentoring=false">
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
                                                <a id="AdminToolBtn" class="list-group-item d-flex justify-content-start align-items-center gap-2 py-3"
                                                        href="{{ $urlAppend }}modules/admin/index.php?goToMentoring=false">
                                                        <i class="fa-solid fa-gear settings-icons"></i>
                                                        {{ trans('langAdminTool') }}
                                                </a>
                                            </li>
                                        @endif
                                        
                                        @if (get_config('eportfolio_enable'))
                                            <li>
                                                <a id='goToEportfolioHeaderMentoring' class="list-group-item d-flex justify-content-start align-items-center gap-2 py-3" href="{{ $urlAppend }}main/eportfolio/index.php?id={{$uid}}&token={{ token_generate('eportfolio'.$uid) }}&fromMentoring=true">
                                                    <i class="fas fa-briefcase settings-icons"></i>
                                                    {{ trans('langMyePortfolio') }}
                                                </a>
                                            </li>
                                        @endif
                                        
                                        <li>
                                            <a id="goToMyProgramHeaderMentoring" class="list-group-item d-flex justify-content-start align-items-center gap-2 py-3" href="{{ $urlAppend }}modules/mentoring/programs/myprograms.php">
                                             
                                                <i class="fas fa-tasks settings-icons"></i>
                                                {{ trans('langMyPrograms') }}

                                            </a>
                                        </li>

                                        <li>
                                            <a id="goToMyProfileHeaderMentoring" class="list-group-item d-flex justify-content-start align-items-center gap-2 py-3" href="{{ $urlAppend }}modules/mentoring/profile/user_profile.php">
                                                <i class="fas fa-user settings-icons"></i>
                                                {{ trans('langMyProfile') }}
                                                        
                                               
                                            </a>
                                        </li>


                                        @if (($session->status == USER_TEACHER and get_config('mydocs_teacher_enable')) or ($session->status == USER_STUDENT and get_config('mydocs_student_enable')) or ($session->status == ADMIN_USER and get_config('mydocs_teacher_enable')))
                                            <li>
                                                <a id="goToMyDocsHeaderMentoring" class="list-group-item d-flex justify-content-start align-items-center gap-2 py-3" href="{{ $urlAppend }}modules/mentoring/programs/group/document/mydoc.php?mydocs=true">
                                                   <i class="fas fa-folder settings-icons"></i>
                                                    {{ trans('langMyDocs') }}
                                                         
                                                   
                                                </a>
                                            </li>
                                        @endif


                                        <li>
                                            <form method='post' action='{{ $urlAppend }}modules/auth/logout.php' style='height:49px;'>
                                                <input type='hidden' name='token' value='{{ $_SESSION['csrf_token'] }}'>
                                                <button class='list-group-item d-flex justify-content-end align-items-center gap-2 py-3 w-100 text-end' type='submit' name='submit'>
                                                    <i class="fa-solid fa-arrow-right-from-bracket Accent-200-cl settings-icons"></i>
                                                    <span class='Accent-200-cl TextBold'>{{ trans('langLogout2') }}</span>
                                                </button>
                                            </form>
                                        </li>


                                    </ul>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            @endif
        </div>
  </div>
</nav>

@if(get_config('mentoring_always_active'))
    <script>
        $('#AdminToolBtn').on('click',function(){
            localStorage.removeItem("MenuMentoring");
        });
    </script>
@endif
<script>
    $('#goToEclassPlatformBtn,#goToLoginForm,#goToEportfolioHeaderMentoring,#goToMyDocsHeaderMentoring,#logoutMentoringBtn').on('click',function(){
        localStorage.removeItem("MenuMentoring");
    });
    $('#goToMyProgramHeaderMentoring').on('click',function(){
        localStorage.setItem("MenuMentoring","program");
    });
    $('#goToMyProfileHeaderMentoring').on('click',function(){
        localStorage.setItem("MenuMentoring","profile");
    });
</script>