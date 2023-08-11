
<nav id="bgr-cheat-header" class="navbar navbar-eclass py-0">

    <div class='col-12 h-100 d-flex justify-content-between align-items-center'>

        <div class='d-flex justify-content-start align-items-center h-100'>

            <a class='ms-lg-4 me-lg-4 me-xl-5' href="{{ $urlAppend }}?goToMentoring=false">
                <img class="eclass-nav-icon m-auto d-block" src="{{ $logo_img }}"/>
            </a>

            <ul class="container-items nav">
                @if(!get_config('hide_login_link'))
                    <li class="nav-item">
                        <a id="link-home" class="nav-link menu-item mx-lg-2 @if (!isset($_SESSION['uid'])) active2 @endif" href="{{ $urlServer }}"> 
                            {{ trans('langHome') }}
                        </a>
                    </li>
                @endif
                @if (!isset($_SESSION['uid']))
                    <li class="nav-item">
                        <a id="link-register" class="nav-link menu-item mx-lg-2 @if(get_config('registration_link')=='hide') d-none @endif" href="{{ $urlServer }}modules/auth/registration.php">
                            {{ trans('langRegistration') }}
                        </a>
                    </li>
                @endif
                <li class="nav-item">
                    <a id="link-lessons" class="nav-link menu-item mx-lg-2" href="{{ $urlServer }}modules/auth/listfaculte.php">
                        {{ trans('langCourses') }}
                    </a>
                </li>
                
                <li class="nav-item">
                    <a id="link-faq" class="nav-link menu-item mx-lg-2 " href="{{$urlAppend}}info/faq.php">
                        {{ trans('langFaq') }}
                    </a>
                </li>
            </ul>
                
        </div>

        <div class='d-flex justify-content-end align-items-center h-100 pe-4'>
            
                    
            @if(get_config('enable_search'))
                <div class='h-100 d-flex justify-content-start align-items-center'>
                    <div class='h-30px'>
                        <form id='submitSearch' class="d-flex justify-content-start align-items-center h-30px" action='{{ $urlAppend }}modules/search/{{ $search_action }}' method='post'>
                            <div>
                                <button id="btn-search" class="btn d-flex justify-content-center align-items-center bg-white border-0 p-0 rounded-0" type="button" name="quickSearch">
                                    <i class="fa-solid fa-magnifying-glass"></i>
                                </button>
                            </div>
                            <input id="search_terms" type="text" class="inputSearch form-control rounded-0" placeholder='Search...' name="search_terms" aria-describedby="basic-inputSearch"/>
                        </form> 
                    </div>
                    <div class='split-left h-30px ps-0 pe-3'></div>
                </div>
            @endif

            @if(!isset($_SESSION['uid']))
                <div class='h-100 d-flex justify-content-start align-items-center'>
                    <div class="d-flex justify-content-start align-items-center h-30px @if(get_config('dont_display_login_form')) pe-3 @endif">
                        {!! lang_selections_Desktop() !!}
                    </div>
                    @if(get_config('dont_display_login_form'))
                        <div class='split-left h-30px ps-0 pe-3'></div>
                    @endif
                </div>
            @endif

            <div class='user-menu-content h-100 d-flex justify-content-start align-items-center'>
                <div class='d-flex justify-content-start align-items-center h-80px'>
                
                    @if(!isset($_SESSION['uid']) and get_config('dont_display_login_form'))
                        <a class='d-flex align-items-center text-uppercase TextBold userLoginMobile ms-0' href="{{$urlAppend}}main/login_form.php">
                            <i class="fa-solid fa-user loginText pe-1"></i>
                            <span class='loginText small-text hidden-lg text-capitalize'>{{ trans('langUserLogin') }}</span>
                        </a>
                    @endif

                    @if(isset($_SESSION['uid']))
                        <div class='d-flex justify-content-end p-0 h-80px'>
                            <div class="btn-group" role="group" aria-label="Button group with nested dropdown">
                                

                                <div class="btn-group" role="group">
                                    <button id="btnGroupDrop1" type="button" class="btn user-menu-btn rounded-0 d-flex justify-content-center align-items-center gap-2 rounded-0" data-bs-toggle="dropdown" aria-expanded="false">
                                            <img class="user-icon-filename" src="{{ user_icon($_SESSION['uid'], IMAGESIZE_LARGE) }}" alt="{{ $uname }}">
                                            <span class='TextBold user-name'>{{ $_SESSION['uname'] }}</span>
                                            <i class="fa-solid fa-chevron-down"></i>
                                    </button>
                                    <div class="m-0 p-3 dropdown-menu dropdown-menu-end contextual-menu contextual-menu-user" aria-labelledby="btnGroupDrop1">
                                        <ul class="list-group list-group-flush">

                                            @if ((isset($is_admin) and $is_admin) or
                                                (isset($is_power_user) and $is_power_user) or
                                                (isset($is_usermanage_user) and ($is_usermanage_user)) or
                                                (isset($is_departmentmanage_user) and $is_departmentmanage_user))
                                                <li>
                                                    <a id="AdminToolBtn" class="list-group-item d-flex justify-content-start align-items-center gap-2 py-3"
                                                            href="{{ $urlAppend }}modules/admin/index.php">
                                                            <i class="fa-solid fa-gear settings-icons"></i>
                                                            {{ trans('langAdminTool') }}
                                                    </a>
                                                </li>
                                            @endif

                                            @if(get_config('mentoring_platform'))
                                            <li>
                                                <a id="goToMentoring" class="list-group-item d-flex justify-content-start align-items-center gap-2 py-3" href="{{ $urlAppend }}modules/mentoring/mentoring_platform_home.php">
                                                   <i class="fa-solid fa-wand-magic settings-icons"></i>
                                                   {{trans('langMentoringPlatform')}}
                                                </a>
                                            </li>
                                            @endif


                                            @if ($_SESSION['status'] == USER_TEACHER or $is_power_user or $is_departmentmanage_user)
                                            <li>
                                                <a class="list-group-item d-flex justify-content-start align-items-center gap-2 py-3" href="{{ $urlAppend }}modules/create_course/create_course.php">
                                                    <i class="fa-solid fa-circle-plus settings-icons"></i>
                                                    {{ trans('langCourseCreate') }}
                                                </a>
                                            </li>
                                            @endif

                                            <li>
                                                <a class="list-group-item d-flex justify-content-start align-items-center gap-2 py-3" href="{{ $urlAppend }}main/portfolio.php">
                                                    <i class="fa-solid fa-house settings-icons"></i>
                                                    {{ trans('langMyPortfolio') }}  
                                                </a>
                                            </li>

                                            <li>
                                                <a class="list-group-item d-flex justify-content-start align-items-center gap-2 py-3" href="{{ $urlAppend }}main/my_courses.php">
                                                    <i class="fa-solid fa-book-open settings-icons"></i>
                                                    {{ trans('langMyCoursesSide') }}
                                                </a>
                                            </li>

                                            <li>
                                                <a class="list-group-item d-flex justify-content-start align-items-center gap-2 py-3" href="{{ $urlAppend }}modules/announcements/myannouncements.php">
                                                    <i class="fa-regular fa-bell settings-icons"></i>
                                                    {{ trans('langMyAnnouncements') }}
                                                </a>
                                            </li>

                                            <li>
                                                <a class="list-group-item d-flex justify-content-start align-items-center gap-2 py-3" href="{{ $urlAppend }}main/notes/index.php">
                                                    <i class="fa-regular fa-file-lines settings-icons"></i>
                                                    {{ trans('langNotes') }}
                                                </a>
                                            </li>
                                            @if (get_config('eportfolio_enable'))
                                                <li>
                                                    <a class="list-group-item d-flex justify-content-start align-items-center gap-2 py-3" href="{{ $urlAppend }}main/eportfolio/index.php?id={{$uid}}&token={{ token_generate('eportfolio'.$uid) }}">
                                                        <i class="fa-regular fa-address-card settings-icons"></i>
                                                        {{ trans('langMyePortfolio') }}   
                                                    </a>
                                                </li>
                                            @endif
                                            <li>
                                                <a class="list-group-item d-flex justify-content-start align-items-center gap-2 py-3" href="{{ $urlAppend }}modules/usage/index.php?t=u">
                                                    <i class="fa-solid fa-chart-line settings-icons"></i>
                                                    {{ trans('langMyStats') }}
                                                </a>
                                            </li>
                                            @if (get_config('personal_blog'))
                                                <li>
                                                    <a class="list-group-item d-flex justify-content-start align-items-center gap-2 py-3" href="{{ $urlAppend }}modules/blog/index.php?user_id={{$uid}}&token={{ token_generate('personal_blog'.$uid) }}">
                                                        <i class="fa-solid fa-globe settings-icons"></i>
                                                        {{ trans('langMyBlog') }}
                                                    </a>
                                                </li>
                                            @endif
                                            <li>
                                                <a class="list-group-item d-flex justify-content-start align-items-center gap-2 py-3" href="{{ $urlAppend }}modules/message/index.php">
                                                    <i class="fa-regular fa-envelope settings-icons"></i>
                                                    {{ trans('langMyDropBox') }}
                                                </a>
                                            </li>
                                            <li>
                                                <a class="list-group-item d-flex justify-content-start align-items-center gap-2 py-3" href="{{ $urlAppend }}main/personal_calendar/index.php">
                                                    <i class="fa-regular fa-calendar settings-icons"></i>
                                                    {{ trans('langMyAgenda') }}
                                                </a>
                                            </li>
                                            <li>
                                                <a class="list-group-item d-flex justify-content-start align-items-center gap-2 py-3" href="{{ $urlAppend }}main/profile/display_profile.php">
                                                    <i class="fa-solid fa-user settings-icons"></i>
                                                    {{ trans('langMyProfile') }}
                                                </a>
                                            </li>
                                            <li>
                                                <a class="list-group-item d-flex justify-content-start align-items-center gap-2 py-3" href="{{ $urlAppend }}main/my_widgets.php">
                                                    <i class="fa-solid fa-layer-group settings-icons"></i>
                                                    {{ trans('langMyWidgets') }}
                                                </a>
                                            </li>
                                            <li>
                                                <a class="list-group-item d-flex justify-content-start align-items-center gap-2 py-3" href="{{ $urlAppend }}main/gradebookUserTotal/index.php">
                                                    <i class="fa-solid fa-a settings-icons"></i>
                                                    {{ trans('langGradeTotal') }}  
                                                </a>
                                            </li>
                                            <li>
                                                <a class="list-group-item d-flex justify-content-start align-items-center gap-2 py-3" href="{{ $urlAppend }}main/mycertificates.php">
                                                    <i class="fa-solid fa-award settings-icons"></i>
                                                    {{ trans('langMyCertificates') }} 
                                                </a>
                                            </li>
                                            @if (($session->status == USER_TEACHER and get_config('mydocs_teacher_enable')) or ($session->status == USER_STUDENT and get_config('mydocs_student_enable')) or ($session->status == ADMIN_USER and get_config('mydocs_teacher_enable')))
                                                <li>
                                                    <a class="list-group-item d-flex justify-content-start align-items-center gap-2 py-3" href="{{ $urlAppend }}main/mydocs/index.php">
                                                        <i class="fa-regular fa-file settings-icons"></i>
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
                    @endif
                </div>
            </div>

        </div>
    </div>
  
</nav>






<script>
    let current_url = document.URL;

    localStorage.setItem("menu-item", "homepage");

    if(current_url.includes('/modules/auth/registration.php')
       || current_url.includes('/modules/auth/formuser.php')
       || current_url.includes('/modules/auth/newuser.php')
       || current_url.includes('/modules/auth/altnewuser.php')){
        localStorage.setItem("menu-item", "register");
    }
    if(current_url.includes('/modules/auth/opencourses.php')
        || current_url.includes('/modules/auth/listfaculte.php')){
        localStorage.setItem("menu-item", "lessons");
    }
    if(current_url.includes('/main/portfolio.php')){
        localStorage.setItem("menu-item", "homepage");
    }
    if(current_url.includes('/info/faq.php')){
        localStorage.setItem("menu-item", "faq");
    }
    if(!current_url.includes('/modules/auth/registration.php')
       && !current_url.includes('/modules/auth/formuser.php')
       && !current_url.includes('/modules/auth/newuser.php')
       && !current_url.includes('/modules/auth/altnewuser.php')
       && !current_url.includes('/modules/auth/opencourses.php')
       && !current_url.includes('/modules/auth/listfaculte.php')
       && !current_url.includes('/main/portfolio.php')
       && !current_url.includes('/info/faq.php')){
            localStorage.setItem("menu-item", "none");
    }



    if(localStorage.getItem("menu-item") == "homepage"){
        $('#link-home').addClass('active');
    }
    if(localStorage.getItem("menu-item") == "register"){
        $('#link-register').addClass('active');
    }
    if(localStorage.getItem("menu-item") == "lessons"){
        $('#link-lessons').addClass('active');
    }
    if(localStorage.getItem("menu-item") == "faq"){
        $('#link-faq').addClass('active');
    }

    if($('#link-register').hasClass('active') || $('#link-lessons').hasClass('active') || $('#link-faq').hasClass('active')){
        $('#link-home').removeClass('active2');
    }

</script>



<script type='text/javascript'>
    $(document).ready(function() {
        
        $('.inputSearch').on('focus',function(){
            $('.container-items').addClass('d-none');
        });
        $('#btn-search').on('focus',function(){
            setTimeout(function () {
                $('.container-items').removeClass('d-none');
            }, 500);
            setTimeout(function () {
                $('#submitSearch').submit();
            }, 200);
        });
        $(".inputSearch").focusout(function(){
            setTimeout(function () {
                $('.container-items').removeClass('d-none');
            }, 500);
            
        });
    });
</script>