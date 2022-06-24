<nav class="navbar h-auto navbar-eclass">
    <div class="btn-group w-100 ps-4 pe-4" role="group" aria-label="Basic example">

        <a type="button" class="btn btn-transparent ps-2 pe-4 text-white" href="{{ $urlServer }}"><i class="fa fa-home"></i></a>

        <a type="button" class="btn btn-transparent ps-2 pe-4 text-white" href="{{ $urlServer }}modules/auth/registration.php"><i class="fas fa-pen-nib"></i></a>
               
        @if($_SESSION['uid'])
            @if($_SESSION['status'] == USER_TEACHER or $_SESSION['status'] == ADMIN_USER)
                <a class="eclass-nav-link ps-2 pe-2" href="{{ $urlAppend }}modules/create_course/create_course.php"><i class="fas fa-plus-circle"></i></a>
            @endif
        @endif

        @if ((isset($is_admin) and $is_admin) or
            (isset($is_power_user) and $is_power_user) or
            (isset($is_usermanage_user) and ($is_usermanage_user)) or
            (isset($is_departmentmanage_user) and $is_departmentmanage_user))
                <a type="button" class="btn btn-transparent ps-2 pe-3" href="{{ $urlAppend }}modules/admin/index.php"><i class="fas fa-wrench text-white"></i></a>
        @endif

        <div class="dropdown d-inline">
            <a class="ps-2 pe-2 eclass-nav-link" type="button" href="#dropdownLanguage"
                    data-bs-toggle="dropdown" aria-expanded="false" aria-haspopup="true"
                    data-bs-toggle-second="tooltip" data-bs-placement="left" title="{{trans('langLanguage')}}">
                <i class="fa fa-globe"></i>
            </a>
            <ul class="dropdown-menu dropdown-language-ul" aria-labelledby="dropdownLanguage">
                <li class="language-li">
                    <a class="language-item" href="{{$urlAppend}}index.php?localize=el"><i class="fas fa-language"></i> {{ trans('langGreek') }}</a>
                </li>
                <li class="language-li">
                    <a class="language-item" href="{{$urlAppend}}index.php?localize=en"><i class="fas fa-language"></i> {{ trans('langEnglish') }}</a>
                </li>
                <li class="language-li">
                    <a class="language-item" href="{{$urlAppend}}index.php?localize=fr"><i class="fas fa-language"></i> {{ trans('langFrench') }}</a>
                </li>
                <li class="language-li">
                    <a class="language-item" href="{{$urlAppend}}index.php?localize=de"><i class="fas fa-language"></i> {{ trans('langGerman') }}</a>
                </li>
                <li class="language-li">
                    <a class="language-item" href="{{$urlAppend}}index.php?localize=it"><i class="fas fa-language"></i> {{ trans('langItalian') }}</a>
                </li>
                <li class="language-li">
                    <a class="language-item" href="{{$urlAppend}}index.php?localize=es"><i class="fas fa-language"></i> {{ trans('langSpanish') }}</a>
                </li>
            </ul>
        </div>

        <?php if (isset($_SESSION['uid'])) { ?>
            <div class="dropdown d-inline ps-2 pe-2">
                <button class="btn btn-transparent dropdown-toogle text-warning" type="button"
                        id="dropdownMenuButton1" data-bs-toggle="dropdown" aria-expanded="false">
                        <i class="fa fa-user"></i>
                    
                </button>

                    <ul class="dropdown-menu dropdown-user-menu-ul mt-3" aria-labelledby="dropdownMenuButton1">
                        <li class="user-menu-li bg-warning w-100 h-25" style="margin-top:-8px;">
                            <a class="ps-5 pe-5 ms-4 text-white"><i class="fa fa-user"></i> {{uid_to_am($uid)}}</a>
                        </li>
                        <li class="user-menu-li">
                            <a class="user-item text-white" href="{{ $urlAppend }}main/portfolio.php"><i class="fas fa-home bg-transparent text-white"></i> {{ trans('langMyPortfolio') }}</a>
                        </li>
                        <li class="user-menu-li">
                            <a class="user-item text-white" href="{{ $urlAppend }}main/my_courses.php"><i class="fas fa-graduation-cap text-white"></i> {{trans('mycourses')}}</a>
                        </li>
                        <li class="user-menu-li">
                            <a class="user-item text-white"
                            href="{{ $urlAppend }}modules/announcements/myannouncements.php"><i class="fas fa-bell text-white"></i> {{ trans('langMyAnnouncements') }}</a>
                        </li>
                        <li class="user-menu-li">
                            <a class="user-item text-white" href="{{ $urlAppend }}main/notes/index.php"><i class="fas fa-sticky-note text-white"></i> {{ trans('langNotes') }}</a>
                        </li>
                        <li class="user-menu-li">
                            <a class="user-item text-white"
                            href="{{ $urlAppend }}main/eportfolio/index.php?id={{$uid}}&token={{ token_generate('eportfolio'.$uid) }}"><i class="fas fa-briefcase text-white"></i> {{ trans('langMyePortfolio') }}</a>
                        </li>
                        <li class="user-menu-li">
                            <a class="user-item text-white" href="{{ $urlAppend }}modules/usage/index.php?t=u"><i class="fas fa-chart-bar text-white"></i> {{ trans('langMyStats') }}</a>
                        </li>
                        <li class="user-menu-li">
                            <a class="user-item text-white"
                            href="{{ $urlAppend }}modules/blog/index.php?user_id={{$uid}}&token={{ token_generate('personal_blog'.$uid) }}"><i
                                        class="fas fa-location-arrow text-white"></i> {{ trans('langMyBlog') }}</a>
                        </li>
                        <li class="user-menu-li">
                            <a class="user-item text-white" href="{{ $urlAppend }}modules/message/index.php"><i class="fas fa-envelope text-white"></i> {{ trans('langMyDropBox') }}</a>
                        </li>
                        <li class="user-menu-li">
                            <a class="user-item text-white" href="{{ $urlAppend }}main/personal_calendar/index.php"><i class="fas fa-bell text-white"></i> {{ trans('langMyAgenda') }}</a>
                        </li>
                        <li class="user-menu-li">
                            <a class="user-item text-white" href="{{ $urlAppend }}main/profile/display_profile.php"><i class="fas fa-user text-white"></i> {{ trans('langMyProfile') }}</a>
                        </li>
                        <li class="user-menu-li">
                            <a class="user-item text-white" href="{{ $urlAppend }}main/mydocs/index.php"><i class="fas fa-folder text-white"></i> {{ trans('langMyDocs') }}</a>
                        </li>
                        <li class="user-menu-li">
                            <a class="user-item text-white" href="{{ $urlAppend }}?logout=yes"><i class="fas fa-unlock text-white"></i> {{ trans('langLogout') }}</a>
                        </li>
                    </ul>
            </div>
        <?php } ?>
    </div>
</nav>