<nav id="bgr-cheat-header" class="navbar navbar-eclass" style='z-index:1;'>

    <div class='col-12 h-100 d-flex justify-content-between align-items-center'>

        <div class='d-flex justify-content-start align-items-center h-100'>
            <div class='d-flex justify-content-start align-items-center flex-wrap'>
                <a class='IconBtn ms-lg-4 me-xl-5 me-lg-4' href="{{ $urlAppend }}">
                    <img class="eclass-nav-icon m-auto d-block" src="{{ $logo_img }}"/>
                </a>
                @if(!get_config('hide_login_link'))
                    <a id="link-home" class="menu-item ms-xl-3 me-3 d-flex justify-content-center align-items-center @if (!isset($_SESSION['uid'])) active2 @endif" href="{{ $urlServer }}"> 
                        {{ trans('langHome') }}
                    </a>
                @endif
                @if (!isset($_SESSION['uid']))
                    <a id="link-register" class="menu-item ms-xl-3 me-3 d-flex justify-content-center align-items-center @if(get_config('registration_link')=='hide') d-none @endif" href="{{ $urlServer }}modules/auth/registration.php">
                        {{ trans('langRegistration') }}
                    </a>
                @endif
                <a id="link-lessons" class="menu-item ms-xl-3 me-3 d-flex justify-content-center align-items-center" href="{{ $urlServer }}modules/auth/listfaculte.php">
                    {{ trans('langCourses') }}
                </a>
                
                <a id="link-faq" class="menu-item ms-xl-3 d-flex justify-content-center align-items-center" href="{{$urlAppend}}info/faq.php">
                    {{ trans('langFaq') }}
                </a>
                
            </div>
        </div>

        <div class='d-flex justify-content-end align-items-center h-100 pe-4'>
            
                    
            @if(get_config('enable_search'))
            <div>
                <form class="d-flex justify-content-end d-inline-flex @if(!isset($_SESSION['uid'])) me-2 @else me-0 @endif" action='{{ $urlAppend }}modules/search/{{ $search_action }}' method='post' >
                    <div class="input-group input-group-search rounded-0">
                        <span class="input-group-text rounded-0 bg-white border-0" id="basic-inputSearch">
                            <button id="btn-search" class="btn d-flex justify-content-center align-items-center bg-white border-0 p-0 rounded-0" type="submit" name="quickSearch">
                                <i class="fa-solid fa-magnifying-glass Neutral-700-cl"></i>
                            </button>
                        </span>
                        <input id="search_terms" type="text" class="inputSearch inputSearchbtn form-control rounded-0" placeholder='Search...' name="search_terms" aria-describedby="basic-inputSearch"/>
                    </div>
                </form> 
            </div>
            @endif

            @if(!isset($_SESSION['uid']))<div>{!! lang_selections_Desktop() !!}</div>@endif

             <div>
               
                    @if(!isset($_SESSION['uid']) and get_config('dont_display_login_form'))
                        <a class='d-flex align-items-center text-uppercase TextSemiBold userLoginMobile ms-2' href="{{$urlAppend}}main/login_form.php">
                            <span class="fa-solid fa-user basic-value-cl pe-2"></span>
                            <span class='ms-2 ms-lg-0 loginText basic-value-cl small-text'>{{ trans('langUserLogin') }}</span>
                        </a>
                    @endif

                    @if(isset($_SESSION['uid']))
                        <div class='d-flex justify-content-end p-0 themeId'>
                            <div class="btn-group" role="group" aria-label="Button group with nested dropdown">
                                @if ((isset($is_admin) and $is_admin) or
                                    (isset($is_power_user) and $is_power_user) or
                                    (isset($is_usermanage_user) and ($is_usermanage_user)) or
                                    (isset($is_departmentmanage_user) and $is_departmentmanage_user))
                                    <a id="AdminToolBtn" type="button" class="btn d-flex justify-content-center align-items-center" aria-haspopup="true"
                                            aria-expanded="false" data-bs-toggle="tooltip" data-bs-placement="left"
                                            title="{{trans('langAdminTool')}}" href="{{ $urlAppend }}modules/admin/index.php"><i class="fas fa-wrench basic-value-cl"></i>
                                    </a>
                                @endif

                                <div class="btn-group" role="group">
                                    <button id="btnGroupDrop1" type="button" class="btn user-menu-btn d-flex justify-content-center align-items-center" data-bs-toggle="dropdown" aria-expanded="false">
                                           <img class="user-icon-filename mt-0" src="{{ user_icon($_SESSION['uid'], IMAGESIZE_LARGE) }}" alt="{{ $uname }}">
                                            {{ $_SESSION['uname'] }}
                                            <i class="fa-solid fa-chevron-down ps-2"></i>
                                    </button>
                                    <div class="m-0 p-3 dropdown-menu dropdown-menu-end contextual-menu" aria-labelledby="btnGroupDrop1">
                                        <ul class="list-group list-group-flush">

                                            @if ($_SESSION['status'] == USER_TEACHER or $is_power_user or $is_departmentmanage_user)
                                            <li>
                                                <a class="list-group-item d-flex justify-content-start align-items-start py-3" href="{{ $urlAppend }}modules/create_course/create_course.php">
                                                    <i class="fa-solid fa-circle-plus pe-2"></i>
                                                    {{ trans('langCourseCreate') }}
                                                </a>
                                            </li>
                                            @endif
                                            <li>
                                                <a class="list-group-item d-flex justify-content-start align-items-start py-3" href="{{ $urlAppend }}main/portfolio.php">
                                                    <i class="fa-solid fa-house pe-2"></i>
                                                    {{ trans('langMyPortfolio') }}  
                                                </a>
                                            </li>

                                            <li>
                                                <a class="list-group-item d-flex justify-content-start align-items-start py-3" href="{{ $urlAppend }}main/my_courses.php">
                                                    <i class="fa-solid fa-graduation-cap pe-2"></i>
                                                    {{ trans('langMyCoursesSide') }}
                                                </a>
                                            </li>

                                            <li>
                                                <a class="list-group-item d-flex justify-content-start align-items-start py-3" href="{{ $urlAppend }}modules/announcements/myannouncements.php">
                                                    <i class="fa-solid fa-bell pe-2"></i>
                                                    {{ trans('langMyAnnouncements') }}
                                                </a>
                                            </li>

                                            <li>
                                                <a class="list-group-item d-flex justify-content-start align-items-start py-3" href="{{ $urlAppend }}main/notes/index.php">
                                                    <i class="fa-solid fa-note-sticky pe-2"></i>
                                                    {{ trans('langNotes') }}
                                                </a>
                                            </li>
                                            @if (get_config('eportfolio_enable'))
                                                <li>
                                                    <a class="list-group-item d-flex justify-content-start align-items-start py-3" href="{{ $urlAppend }}main/eportfolio/index.php?id={{$uid}}&token={{ token_generate('eportfolio'.$uid) }}">
                                                        <i class="fa-solid fa-briefcase pe-2"></i>
                                                        {{ trans('langMyePortfolio') }}   
                                                    </a>
                                                </li>
                                            @endif
                                            <li>
                                                <a class="list-group-item d-flex justify-content-start align-items-start py-3" href="{{ $urlAppend }}modules/usage/index.php?t=u">
                                                    <i class="fa-solid fa-chart-bar pe-2"></i>
                                                    {{ trans('langMyStats') }}
                                                </a>
                                            </li>
                                            @if (get_config('personal_blog'))
                                                <li>
                                                    <a class="list-group-item d-flex justify-content-start align-items-start py-3" href="{{ $urlAppend }}modules/blog/index.php?user_id={{$uid}}&token={{ token_generate('personal_blog'.$uid) }}">
                                                        <i class="fa-solid fa-location-arrow pe-2"></i>
                                                        {{ trans('langMyBlog') }}
                                                    </a>
                                                </li>
                                            @endif
                                            <li>
                                                <a class="list-group-item d-flex justify-content-start align-items-start py-3" href="{{ $urlAppend }}modules/message/index.php">
                                                    <i class="fa-solid fa-envelope pe-2"></i>
                                                    {{ trans('langMyDropBox') }}
                                                </a>
                                            </li>
                                            <li>
                                                <a class="list-group-item d-flex justify-content-start align-items-start py-3" href="{{ $urlAppend }}main/personal_calendar/index.php">
                                                    <i class="fa-solid fa-bell pe-2"></i>
                                                    {{ trans('langMyAgenda') }}
                                                </a>
                                            </li>
                                            <li>
                                                <a class="list-group-item d-flex justify-content-start align-items-start py-3" href="{{ $urlAppend }}main/profile/display_profile.php">
                                                    <i class="fa-solid fa-user pe-2"></i>
                                                    {{ trans('langMyProfile') }}
                                                </a>
                                            </li>
                                            <li>
                                                <a class="list-group-item d-flex justify-content-start align-items-start py-3" href="{{ $urlAppend }}main/my_widgets.php">
                                                   <i class="fa-solid fa-wand-magic pe-2"></i>
                                                    {{ trans('langMyWidgets') }}
                                                </a>
                                            </li>
                                            <li>
                                                <a class="list-group-item d-flex justify-content-start align-items-start py-3" href="{{ $urlAppend }}main/gradebookUserTotal/index.php">
                                                    <i class="fa-solid fa-arrow-down-9-1 pe-2"></i>
                                                    {{ trans('langGradeTotal') }}  
                                                </a>
                                            </li>
                                            <li>
                                                <a class="list-group-item d-flex justify-content-start align-items-start py-3" href="{{ $urlAppend }}main/mycertificates.php">
                                                    <i class="fa-solid fa-trophy pe-2"></i>
                                                    {{ trans('langMyCertificates') }} 
                                                </a>
                                            </li>
                                            @if (($session->status == USER_TEACHER and get_config('mydocs_teacher_enable')) or ($session->status == USER_STUDENT and get_config('mydocs_student_enable')) or ($session->status == ADMIN_USER and get_config('mydocs_teacher_enable')))
                                                <li>
                                                    <a class="list-group-item d-flex justify-content-start align-items-start py-3" href="{{ $urlAppend }}main/mydocs/index.php">
                                                        <i class="fa-solid fa-folder pe-2"></i>
                                                        {{ trans('langMyDocs') }}
                                                    </a>
                                                </li>
                                            @endif


                                            <li>
                                                <form method='post' action='{{ $urlAppend }}modules/auth/logout.php' style='height:49px;'>
                                                    <input type='hidden' name='token' value='{{ $_SESSION['csrf_token'] }}'>
                                                    <button class='list-group-item d-flex justify-content-end align-items-center py-3 w-100 text-end' type='submit' name='submit'>
                                                        <i class="fa-solid fa-right-from-bracket pe-2 Primary-600-cl"></i>
                                                        {{ trans('langLogout') }}
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

    // if(localStorage.getItem("menu-item") == "register"){
    //     $('#link-register').addClass('active');
    // }

    // $('.menu-item').on('click',function(){
    //     if(localStorage.getItem("menu-item") == "register"){

    //     }
    // });

</script>