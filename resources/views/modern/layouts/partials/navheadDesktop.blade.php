<nav id="bgr-cheat-header" class="navbar navbar-eclass">

    <div class="container-fluid h-100">

        <div class='row w-100 h-100'> 

            <div class="col-lg-9 d-flex justify-content-start">
                <div class="row w-100">
                    <div class='col-xxl-2 col-lg-3 d-flex justify-content-center align-items-center'>
                        <a href="{{ $urlAppend }}">
                            <img class="eclass-nav-icon" src="{{ $logo_img }}"/>
                        </a>
                    </div>
                    <div class="col-xxl-10 col-lg-9 d-flex justify-content-start align-items-end ps-lg-3 ps-xl-5">
                        <div class='d-none d-xl-block w-100'>
                            @if(!get_config('hide_login_link'))
                                <a id="link-home" class="eclass-nav-link m-auto d-block me-3 d-inline-flex justify-content-center align-items-top" href="{{ $urlServer }}" onclick="clickHome(this)" onmouseover="hoverHome(this);" onmouseout="unhoverHome(this);"> 
                                    <img class='HeaderIcon me-1' src="{{ $urlAppend }}template/modern/img/home_1.svg">
                                    <p class='small-text text-white text-uppercase mb-0 mt-xl-1 mt-lg-1'>{{ trans('langHome') }}</p>
                                </a>
                            @endif
                            @if (!isset($_SESSION['uid']))
                                <a id="link-register" class="eclass-nav-link m-auto d-block me-3 d-inline-flex justify-content-center align-items-top @if(get_config('registration_link')=='hide') d-none @endif" href="{{ $urlServer }}modules/auth/registration.php" onmouseover="hoverRegister(this);" onmouseout="unhoverRegister(this);">
                                    <img class='HeaderIcon me-1' src="{{ $urlAppend }}template/modern/img/register_1.svg">
                                    <p class='small-text text-white text-uppercase mb-0 mt-xl-1 mt-lg-1'>{{ trans('langRegistration') }}</p>
                                </a>
                            @endif
                            <a id="link-lessons" class="eclass-nav-link m-auto d-block me-3 d-inline-flex justify-content-center align-items-top" href="{{ $urlServer }}modules/auth/listfaculte.php" onmouseover="hoverLessons(this);" onmouseout="unhoverLessons(this);">
                                <img class='HeaderIcon me-1' src="{{ $urlAppend }}template/modern/img/lessons_1.svg">
                                <p class='small-text text-white text-uppercase mb-0 mt-xl-1 mt-lg-1'>{{ trans('langCourses') }}</p>
                            </a>
                            @if (!isset($_SESSION['uid']))
                                <a id="link-faq" class="eclass-nav-link m-auto d-block me-3 d-inline-flex justify-content-center align-items-top" href="{{$urlAppend}}info/faq.php" onmouseover="hoverQuestions(this);" onmouseout="unhoverQuestions(this);">
                                    <img class='HeaderIcon me-1' src="{{ $urlAppend }}template/modern/img/faq_1.svg">
                                    <p class='small-text text-white text-uppercase mt-xl-1 mt-lg-1 mb-0'>{{ trans('langFaq') }}</p>
                                </a>
                            @endif
                        </div>

                        <div class='d-none d-lg-block d-xl-none'>
                            <div class='row w-100'>
                                <div class='col-lg-6 d-flex justify-content-start'>
                                    <a id="link-home" class="eclass-nav-link @if (!isset($_SESSION['uid'])) ms-5 @endif mb-2 d-inline-flex justify-content-center align-items-top" href="{{ $urlServer }}" onclick="clickHome(this)" onmouseover="hoverHome(this);" onmouseout="unhoverHome(this);"> 
                                        <img class='HeaderIcon me-1' src="{{ $urlAppend }}template/modern/img/home_1.svg">
                                        <p class='small-text text-white text-uppercase mb-0 mt-xl-1 mt-lg-1'>{{ trans('langHome') }}</p>
                                    </a>
                                </div>
                                @if (!isset($_SESSION['uid']))
                                    <div class='col-lg-6 d-flex justify-content-start'>
                                        <a id="link-register" class="eclass-nav-link mb-2 d-inline-flex justify-content-center align-items-top @if(get_config('registration_link')=='hide') d-none @endif" href="{{ $urlServer }}modules/auth/registration.php" onmouseover="hoverRegister(this);" onmouseout="unhoverRegister(this);">
                                            <img class='HeaderIcon me-1' src="{{ $urlAppend }}template/modern/img/register_1.svg">
                                            <p class='small-text text-white text-uppercase mb-0 mt-xl-1 mt-lg-1'>{{ trans('langRegistration') }}</p>
                                        </a>
                                    </div>
                                @endif
                                <div class='col-lg-6 d-flex justify-content-start'>
                                    <a id="link-lessons" class="eclass-nav-link @if (!isset($_SESSION['uid'])) ms-5 @endif mb-2 d-inline-flex justify-content-center align-items-top" href="{{ $urlServer }}modules/auth/listfaculte.php" onmouseover="hoverLessons(this);" onmouseout="unhoverLessons(this);">
                                        <img class='HeaderIcon me-1' src="{{ $urlAppend }}template/modern/img/lessons_1.svg">
                                        <p class='small-text text-white text-uppercase mb-0 mt-xl-1 mt-lg-1'>{{ trans('langCourses') }}</p>
                                    </a>
                                </div>
                                @if (!isset($_SESSION['uid']))
                                <div class='col-lg-6 d-flex justify-content-start'>
                                    <a id="link-faq" class="eclass-nav-link mb-2 d-inline-flex justify-content-center align-items-top" href="{{$urlAppend}}info/faq.php" onmouseover="hoverQuestions(this);" onmouseout="unhoverQuestions(this);">
                                        <img class='HeaderIcon me-1' src="{{ $urlAppend }}template/modern/img/faq_1.svg">
                                        <p class='small-text text-white text-uppercase mt-xl-1 mt-lg-1 mb-0'>{{ trans('langFaq') }}</p>
                                    </a>
                                </div>
                                @endif
                            </div>
                        </div>
                        
                       
                    </div>
                </div>
            </div>
            

            <div class="col-lg-3 ps-0">

                <div class='row mt-0'>
                    <form class='d-flex justify-content-end d-inline-flex ps-0' action='{{ $urlAppend }}modules/search/{{ $search_action }}' method='post' >
                        @if(get_config('enable_search'))
                            <button id="btn-search" class="rounded-0 inputSearch d-flex justify-content-center align-items-center btn btn-transparent p-0" type="submit" name="quickSearch">
                                 <img class='search-icon' src="{{$urlAppend}}template/modern/img/search.svg">
                            </button>
                            <input id="search_terms" type="text" class="inputSearch inputSearchbtn text-white me-3" name="search_terms" aria-describedby="basic-inputSearch">
                        @endif
                        {!! lang_selections_Desktop() !!}
                    </form>
                </div>

                @if (isset($_SESSION['uid']))
                    <div class="row mt-5 pe-0">
                        <div class='col-12 d-flex justify-content-end p-0 mt-xl-3 mt-lg-2'>
                            <div class="btn-group" role="group" aria-label="Button group with nested dropdown">
                                @if ((isset($is_admin) and $is_admin) or
                                    (isset($is_power_user) and $is_power_user) or
                                    (isset($is_usermanage_user) and ($is_usermanage_user)) or
                                    (isset($is_departmentmanage_user) and $is_departmentmanage_user))
                                    <a id="AdminToolBtn" type="button" class="btn submitAdminBtn d-flex justify-content-center align-items-center" aria-haspopup="true"
                                            aria-expanded="false" data-bs-toggle="tooltip" data-bs-placement="left"
                                            title="{{trans('langAdminTool')}}" href="{{ $urlAppend }}modules/admin/index.php"><i class="fas fa-wrench text-white"></i>
                                    </a>
                                @endif

                                <div class="btn-group" role="group">
                                    <button id="btnGroupDrop1" type="button" class="d-flex justify-content-center align-items-center btn submitAdminBtn dropdown-toggle user-menu-btn text-white text-capitalize"
                                            data-bs-toggle="dropdown" aria-expanded="false">
                                        <span><img class="user-icon-filename" src="{{ user_icon($_SESSION['uid'], IMAGESIZE_LARGE) }}"
                                                alt="{{ $uname }}">{{ $_SESSION['uname'] }}</span>
                                    </button>
                                    <ul class="m-0 p-0 dropdown-menu dropdown-menu-end dropdown_menu_user shadow-lg bg-body border-0" aria-labelledby="btnGroupDrop1">

                                        @if ($_SESSION['status'] == USER_TEACHER or $is_power_user or $is_departmentmanage_user)
                                        <li>
                                            <a class="list-group-item border border-top-0 border-bottom-secondary" href="{{ $urlAppend }}modules/create_course/create_course.php">
                                                <div class='row'>
                                                    <div class='col-1'>
                                                        <span class="fas fa-plus-circle pt-1"></span>
                                                    </div>
                                                    <div class='col-10 ps-3 pe-3'>
                                                        <span class='TextMedium'>
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
                                                        <span class="fas fa-home bg-transparent pt-1"></span>
                                                    </div>
                                                    <div class='col-10 ps-3 pe-3'>
                                                        <span class='TextMedium'>
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
                                                        <span class="fas fa-graduation-cap pt-1"></span>
                                                    </div>
                                                    <div class='col-10 ps-3 pe-3'>
                                                        <span class='TextMedium'>
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
                                                        <span class="fas fa-bell pt-1"></span>
                                                    </div>
                                                    <div class='col-10 ps-3 pe-3'>
                                                        <span class='TextMedium'>
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
                                                        <span class="fas fa-sticky-note pt-1"></span>
                                                    </div>
                                                    <div class='col-10 ps-3 pe-3'>
                                                        <span class='TextMedium'>
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
                                                            <span class="fas fa-briefcase pt-1"></span>
                                                        </div>
                                                        <div class='col-10 ps-3 pe-3'>
                                                            <span class='TextMedium'>
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
                                                        <span class="fas fa-chart-bar pt-1"></span>
                                                    </div>
                                                    <div class='col-10 ps-3 pe-3'>
                                                        <span class='TextMedium'>
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
                                                            <span class="fas fa-location-arrow pt-1"></span>
                                                        </div>
                                                        <div class='col-10 ps-3 pe-3'>
                                                            <span class='TextMedium'>
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
                                                        <span class="fas fa-envelope pt-1"></span>
                                                    </div>
                                                    <div class='col-10 ps-3 pe-3'>
                                                        <span class='TextMedium'>
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
                                                        <span class="fas fa-bell pt-1"></span>
                                                    </div>
                                                    <div class='col-10 ps-3 pe-3'>
                                                        <span class='TextMedium'>
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
                                                        <span class="fas fa-user pt-1"></span>
                                                    </div>
                                                    <div class='col-10 ps-3 pe-3'>
                                                        <span class='TextMedium'>
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
                                                        <span class="fa fa-magic fa-fw pt-1"></span>
                                                    </div>
                                                    <div class='col-10 ps-3 pe-3'>
                                                        <span class='TextMedium'>
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
                                                        <span class="fa fa-sort-numeric-desc fa-fw pt-1"></span>
                                                    </div>
                                                    <div class='col-10 ps-3 pe-3'>
                                                        <span class='TextMedium'>
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
                                                        <span class="fa fa-trophy fa-fw pt-1"></span>
                                                    </div>
                                                    <div class='col-10 ps-3 pe-3'>
                                                        <span class='TextMedium'>
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
                                                            <span class="fas fa-folder pt-1"></span>
                                                        </div>
                                                        <div class='col-10 ps-3 pe-3'>
                                                            <span class='TextMedium'>
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
                                                    <span class='TextMedium text-dark'>{{ trans('langLogout') }}</span>
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

    if(current_url.includes('/modules/auth/registration.php')
       || current_url.includes('/modules/auth/formuser.php')
       || current_url.includes('/modules/auth/altnewuser.php')){
        $('#link-register'+'>'+'img').attr("src","{{ $urlAppend }}template/modern/img/register_2.svg");
    }else{
        $('#link-register'+'>'+'img').attr("src","{{ $urlAppend }}template/modern/img/register_1.svg");
    }
    if(current_url.includes('/modules/auth/opencourses.php')){
        $('#link-lessons'+'>'+'img').attr("src","{{ $urlAppend }}template/modern/img/lessons_2.svg");
    }else{
        $('#link-lesson'+'>'+'img').attr("src","{{ $urlAppend }}template/modern/img/lessons_1.svg");
    }
    if(current_url.includes('/main/portfolio.php')){
        $('#link-home'+'>'+'img').attr("src","{{ $urlAppend }}template/modern/img/home_2.svg");
    }
    if(current_url.includes('/info/faq.php')){
        $('#link-faq'+'>'+'img').attr("src","{{ $urlAppend }}template/modern/img/faq_2.svg");
    }

    function clickHome(obj){
        $('#'+obj.id+'>'+'img').attr("src","{{ $urlAppend }}template/modern/img/home_2.svg");
    }

    function hoverHome(obj) {
        $('#'+obj.id+'>'+'img').attr("src","{{ $urlAppend }}template/modern/img/home_2.svg");
    }
    function unhoverHome(obj) {
        if(!current_url.includes('/main/portfolio.php')){
            $('#'+obj.id+'>'+'img').attr("src","{{ $urlAppend }}template/modern/img/home_1.svg");
        }
    }

    function hoverRegister(obj) {
        $('#'+obj.id+'>'+'img').attr("src","{{ $urlAppend }}template/modern/img/register_2.svg");
    }
    function unhoverRegister(obj) {
        if(!current_url.includes('/modules/auth/registration.php')
        && !current_url.includes('/modules/auth/formuser.php')
        && !current_url.includes('/modules/auth/altnewuser.php')){
            $('#'+obj.id+'>'+'img').attr("src","{{ $urlAppend }}template/modern/img/register_1.svg");
        }
    }

    function hoverLessons(obj) {
        $('#'+obj.id+'>'+'img').attr("src","{{ $urlAppend }}template/modern/img/lessons_2.svg");
    }
    function unhoverLessons(obj) {
        if(!current_url.includes('/modules/auth/opencourses.php')){
            $('#'+obj.id+'>'+'img').attr("src","{{ $urlAppend }}template/modern/img/lessons_1.svg");
        }
    }


    function hoverQuestions(obj) {
        $('#'+obj.id+'>'+'img').attr("src","{{ $urlAppend }}template/modern/img/faq_2.svg");
    }
    function unhoverQuestions(obj) {
        if(!current_url.includes('/info/faq.php')){
            $('#'+obj.id+'>'+'img').attr("src","{{ $urlAppend }}template/modern/img/faq_1.svg");
        }
    }

</script>
