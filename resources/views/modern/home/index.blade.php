@extends('layouts.default')

@section('content')

<link href="{{ $urlAppend }}template/modern/css/homepage.css" rel="stylesheet" type="text/css">

<div class="container-login">

    <div class="container-fluid main-container">

        <div class="row rowMedium">

            <div class="col-12 bgEclass col_maincontent_active_HomepageStart HomepageStartMobile">

                <div class="row">
                    <div class='jumbotron jumbotron-login'>
                        @if(!(get_config('upgrade_begin') || get_config('dont_display_login_form')))
                            @if($PositionFormLogin == 0)
                            <div class='col-xxl-3 offset-xxl-9 col-xl-4 offset-xl-8 col-lg-4 offset-lg-8 col-md-8 offset-md-2 col-12 d-flex justify-content-end align-items-center bg-transparent px-lg-4 px-1 py-lg-0 py-4'>
                            @else
                            <div class='col-xxl-4 col-lg-5 col-md-6 col-12 d-lg-flex justify-content-lg-center align-items-lg-center ms-auto me-auto mt-lg-5 pt-3 pb-3'>
                            @endif
                                <div class='card-body cardLogin Borders' style='z-index:2;'>
                                    <div class='card-header bg-transparent border-0'>
                                        @if($warning)
                                            {!! $warning !!}
                                        @endif
                                        <img class="UserLoginIcon m-auto d-block" src="{{ $urlAppend }}template/modern/img/user_login.svg"> 
                                        <p class="fs-5 TextBold mb-0 text-center blackBlueText text-capitalize">{{ trans('langUserLogin') }}</p>
                                    </div>
                                    <form class='mt-0' action="{{ $urlAppend }}" method="post">
                                        <div class="login-form-spacing mt-3">
                                            <input id='username_id' class="rounded-pill login-input w-75" placeholder="{{ trans('langUsername') }}" type="text" id="uname" name="uname" autocomplete="on">
                                            <input id='password_id' class="rounded-pill login-input w-75 mt-2" placeholder="{{ trans('langPassword') }}" type="password" id="pass" name="pass" autocomplete="on">
                                            <input class="rounded-pill btn w-75 login-form-submit TextBold mt-md-4 mb-md-0 mt-4 mb-4" type="submit" name="submit" value="{{ trans('langLogin') }}">
                                        </div>
                                    </form>
                                    <div class='col-12 text-center mt-3'>
                                        <a class="orangeText btnlostpass" href="{{$urlAppend}}modules/auth/lostpass.php">{{ trans('lang_forgot_pass') }}</a>
                                    </div>
                                    <div class='col-12 text-center mt-2 mb-1'>
                                        <a class="vsmall-text TextSemiBold text-uppercase lightBlueText" href="{{$urlAppend}}main/login_form.php">{{ trans('langMoreLogin') }}</a>
                                    </div>
                                </div>
                            </div>
                        @else
                        <div class='col-xxl-3 offset-xxl-9 col-xl-4 offset-xl-8 col-lg-4 offset-lg-8 col-md-8 offset-md-2 col-12 d-lg-flex justify-content-lg-end align-items-lg-end bg-transparent px-4 py-lg-0 py-4'>
                            <div class='card-body p-0' style='z-index:2;'>
                                <div class='card-header bg-transparent border-0 d-flex justify-content-lg-end justify-content-center p-0'>
                                    <a class='text-uppercase TextSemiBold text-white small-text' href="{{$urlAppend}}main/login_form.php">
                                        <img class="UserLoginIcon2" src="{{ $urlAppend }}template/modern/img/user_login_2.svg"> 
                                        {{ trans('langUserLogin') }}
                                    </a>
                                </div>
                                
                            </div>
                        </div>
                        @endif
                    </div>
                    
                    @if(get_config('homepage_title') or get_config('homepage_intro') or get_config('enable_mobileapi') or get_config('opencourses_enable') or ($eclass_banner_value == 0) or $announcements)
                    <div class='d-none d-lg-block'>
                        <div class='SectionMenu ms-auto me-auto bg-transparent @if(!get_config("homepage_title") and !get_config("homepage_intro")) pt-3 @endif'>
                            @if(get_config('homepage_title') or get_config('homepage_intro'))
                                <div class='col-12 d-flex justify-content-center homepage_intro-margin @if($warning) mt-3 @endif mb-3'>
                                    <div class="panel panel-default homepageIntroPanel w-100 border-0 shadow-none">
                                        <div class="panel-body blackBlueText bg-body @if(get_config('homepage_title')) NoBorderTop @else Borders @endif p-5">
                                            {!! get_config('homepage_intro') !!}
                                        </div>
                                        @if(get_config('homepage_title'))
                                            <div class='panel-footer'>
                                                <p class='text-center text-uppercase fs-6 TextExtraBold normalBlueText pb-4'> {!! get_config('homepage_title') !!}</p>
                                            </div>
                                        @endif
                                    </div>
                                </div>
                            @endif

                            <!-- only eclass-banner -->
                            @if (!get_config('enable_mobileapi') and !get_config('opencourses_enable') and $eclass_banner_value == 1)
                                <div class='col-12 d-flex justify-content-center mb-3'>
                                    <div class='w-100 panel panel-admin panel-banner border-0 shadow-none ps-1 pe-1'>
                                        <div class='panel-body rounded-Home d-flex justify-content-center align-items-center'>
                                            <a href="http://www.openeclass.org/" target="_blank">
                                                <img class="img-responsive center-block m-auto d-block" src="{{ $themeimg }}/open_eclass_banner.png" alt="Open eClass Banner">
                                            </a>
                                        </div>
                                    </div>
                                </div>
                            <!-- only eclass-banner and mobileapi -->
                            @elseif (get_config('enable_mobileapi') and !get_config('opencourses_enable') and $eclass_banner_value == 1)
                                <div class='col-12 mb-3'>
                                    <div class='row'>
                                        <div class='col-lg-6 pe-1'>
                                            <div class='panel panel-admin panel-banner border-0 shadow-none ps-1 pe-1'>
                                                <div class='panel-body rounded-Home d-flex justify-content-center align-items-center'>
                                                    <a href="http://www.openeclass.org/" target="_blank">
                                                        <img class="img-responsive center-block m-auto d-block" src="{{ $themeimg }}/open_eclass_banner.png" alt="Open eClass Banner">
                                                    </a>
                                                </div>
                                            </div>
                                        </div>
                                        <div class='col-lg-6'>
                                            <div class='panel panel-admin panel-social-media border-0 ps-1 pe-1'>
                                                <div class='panel-body rounded-Home d-flex justify-content-center align-items-center'>
                                                    <div class='col-12'>
                                                        <div class='row'>
                                                            <div class='col-6'>
                                                                <a href='https://itunes.apple.com/us/app/open-eclass-mobile/id1398319489' target=_blank>
                                                                    <img src='template/modern/images/appstore.png' class='img-responsive center-block m-auto d-block' alt='Available on the App Store'>
                                                                </a>
                                                            </div>
                                                            <div class='col-6'>
                                                                <a href='https://play.google.com/store/apps/details?id=gr.gunet.eclass' target=_blank>
                                                                    <img src='template/modern/images/playstore.png' class='img-responsive center-block m-auto d-block' alt='Available on the Play Store'>
                                                                </a>
                                                            </div>
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            <!-- only eclass-banner and openecourses -->
                            @elseif (!get_config('enable_mobileapi') and $eclass_banner_value == 1)
                                <div class='col-12 mb-3'>
                                    <div class='row'>
                                        <div class='col-lg-12 mb-3'>
                                            <div class='panel panel-admin panel-banner border-0 shadow-none ps-1 pe-1'>
                                                <div class='panel-body rounded-Home d-flex justify-content-center align-items-center'>
                                                    <a href="http://www.openeclass.org/" target="_blank">
                                                        <img class="img-responsive center-block m-auto d-block" src="{{ $themeimg }}/open_eclass_banner.png" alt="Open eClass Banner">
                                                    </a>
                                                </div>
                                            </div>
                                        </div>
                                        <div class='col-lg-12'>
                                            @if (!isset($openCoursesExtraHTML))
                                                @php $openCoursesExtraHTML = ''; @endphp
                                                {!! setOpenCoursesExtraHTML() !!}
                                            @endif
                                            @if (get_config('opencourses_enable'))
                                                <div class='row'>
                                                    <div class='col-12'>
                                                        <div class='panel panel-admin panel-open-courses border-0 shadow-none ps-1 pe-1'>
                                                            <div class='panel-body rounded-Home w-100 @if($openCoursesExtraHTML) pe-5 d-flex justify-content-center align-items-center @endif'>
                                                                @if ($openCoursesExtraHTML)
                                                                    {!! $openCoursesExtraHTML !!}
                                                                @else
                                                                    <div class='col-12 d-flex justify-content-center align-items-center mt-3'>
                                                                        <img class='w-50' style='height:100px;' src='{{$urlAppend}}template/modern/img/banner_open_courses.png' alt="{!! q($langListOpenCourses) !!}">
                                                                    </div>
                                                                    <div class='col-12 d-flex justify-content-center align-items-center mt-3'>
                                                                        <a class='btn rounded-pill opencourses_btn TextBold d-flex justify-content-center align-items-center' href='http://opencourses.gr' target='_blank'>
                                                                            {{ trans('langNationalOpenCourses') }}
                                                                            <span class='fa fa-chevron-right ms-2'></span>
                                                                        </a>
                                                                    </div>
                                                                @endif
                                                            </div>
                                                        </div>
                                                    </div>
                                                </div>
                                            @endif
                                        </div>
                                    </div>
                                </div>
                            <!-- only eclass-banner , mobileapi , openecourses -->
                            @elseif (get_config('enable_mobileapi') and get_config('opencourses_enable') and $eclass_banner_value == 1)
                                <div class='col-12 d-flex justify-content-center mb-3'>
                                    <div class='row'>
                                        <div class='col-lg-3 pe-1'>
                                            <div class='row'>
                                                <div class='col-12'>
                                                    <div class='panel panel-admin panel-banner border-0 shadow-none ps-1 pe-1'>
                                                        <div class='panel-body rounded-Home d-flex justify-content-center align-items-center'>
                                                            <a class='d-flex justify-content-center align-items-center' href="http://www.openeclass.org/" target="_blank">
                                                                <img src="{{ $themeimg }}/open_eclass_banner.png" alt="Open eClass Banner">
                                                            </a>
                                                        </div>
                                                    </div>
                                                </div>
                                                <div class='col-12 mt-3'>
                                                    <div class='panel panel-admin panel-social-media border-0 shadow-none ps-1 pe-1'>
                                                        <div class='panel-body rounded-Home d-flex justify-content-center align-items-center'>
                                                            <div class='col-12'>
                                                                <div class='row'>
                                                                    <div class='col-6'>
                                                                        <a href='https://itunes.apple.com/us/app/open-eclass-mobile/id1398319489' target=_blank>
                                                                            <img src='template/modern/images/appstore.png' class='img-responsive center-block m-auto d-block' alt='Available on the App Store'>
                                                                        </a>
                                                                    </div>
                                                                    <div class='col-6'>
                                                                        <a href='https://play.google.com/store/apps/details?id=gr.gunet.eclass' target=_blank>
                                                                            <img src='template/modern/images/playstore.png' class='img-responsive center-block m-auto d-block' alt='Available on the Play Store'>
                                                                        </a>
                                                                    </div>
                                                                </div>
                                                            </div>
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                        <div class='col-lg-9'>
                                            @if (!isset($openCoursesExtraHTML))
                                                @php $openCoursesExtraHTML = ''; @endphp
                                                {!! setOpenCoursesExtraHTML() !!}
                                            @endif
                                            @if (get_config('opencourses_enable'))
                                                <div class='row'>
                                                    <div class='col-12'>
                                                        <div class='panel panel-admin panel-open-courses border-0 shadow-none ps-1 pe-1'>
                                                            <div class='panel-body rounded-Home w-100 @if($openCoursesExtraHTML) pe-5 d-flex justify-content-center align-items-center @endif'>
                                                                @if ($openCoursesExtraHTML)
                                                                    {!! $openCoursesExtraHTML !!}
                                                                @else
                                                                    <div class='col-12 d-flex justify-content-center align-items-center mt-3'>
                                                                        <img class='w-50' style='height:100px;' src='{{$urlAppend}}template/modern/img/banner_open_courses.png' alt="{!! q($langListOpenCourses) !!}">
                                                                    </div>
                                                                    <div class='col-12 d-flex justify-content-center align-items-center mt-3'>
                                                                        <a class='btn rounded-pill opencourses_btn TextBold d-flex justify-content-center align-items-center' href='http://opencourses.gr' target='_blank'>
                                                                            {{ trans('langNationalOpenCourses') }}
                                                                            <span class='fa fa-chevron-right ms-2'></span>
                                                                        </a>
                                                                    </div>
                                                                @endif
                                                            </div>
                                                        </div>
                                                    </div>
                                                </div>
                                            @endif
                                        </div>
                                    </div>
                                </div>
                            <!-- only mobileapi , openecourses -->
                            @elseif (get_config('enable_mobileapi') and get_config('opencourses_enable') and $eclass_banner_value == 0)
                                <div class='col-12 mb-3'>
                                    <div class='row'>
                                        <div class='col-lg-4 pe-1'>
                                            <div class='row'>
                                                <div class='col-12'>
                                                    <div class='panel panel-admin panel-social-media border-0 shadow-none ps-1 pe-1'>
                                                        <div class='panel-body rounded-Home d-flex justify-content-center align-items-center'>
                                                            <a href='https://itunes.apple.com/us/app/open-eclass-mobile/id1398319489' target=_blank>
                                                                <img src='template/modern/images/appstore.png' class='img-responsive center-block m-auto d-block' alt='Available on the App Store'>
                                                            </a>
                                                        </div>
                                                    </div>
                                                </div>
                                                <div class='col-12 mt-3'>
                                                    <div class='panel panel-admin panel-social-media border-0 shadow-none ps-1 pe-1'>
                                                        <div class='panel-body rounded-Home d-flex justify-content-center align-items-center'>
                                                            <a href='https://play.google.com/store/apps/details?id=gr.gunet.eclass' target=_blank>
                                                                <img src='template/modern/images/playstore.png' class='img-responsive center-block m-auto d-block' alt='Available on the Play Store'>
                                                            </a>
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                        <div class='col-lg-8'>
                                            @if (!isset($openCoursesExtraHTML))
                                            @php $openCoursesExtraHTML = ''; @endphp
                                                {!! setOpenCoursesExtraHTML() !!}
                                            @endif
                                            @if (get_config('opencourses_enable'))
                                                <div class='row'>
                                                    <div class='col-12'>
                                                        <div class='panel panel-admin panel-open-courses border-0 shadow-none ps-1 pe-1'>
                                                            <div class='panel-body rounded-Home w-100 @if($openCoursesExtraHTML) pe-5 d-flex justify-content-center align-items-center @endif'>
                                                                @if ($openCoursesExtraHTML)
                                                                    {!! $openCoursesExtraHTML !!}
                                                                @else
                                                                    <div class='col-12 d-flex justify-content-center align-items-center mt-3'>
                                                                        <img class='w-50' style='height:100px;' src='{{$urlAppend}}template/modern/img/banner_open_courses.png' alt="{!! q($langListOpenCourses) !!}">
                                                                    </div>
                                                                    <div class='col-12 d-flex justify-content-center align-items-center mt-3'>
                                                                        <a class='btn rounded-pill opencourses_btn TextBold d-flex justify-content-center align-items-center' href='http://opencourses.gr' target='_blank'>
                                                                            {{ trans('langNationalOpenCourses') }}
                                                                            <span class='fa fa-chevron-right ms-2'></span>
                                                                        </a>
                                                                    </div>
                                                                @endif
                                                            </div>
                                                        </div>
                                                    </div>
                                                </div>
                                            @endif
                                        </div>           
                                    </div>
                                </div>
                            <!-- only mobileapi -->
                            @elseif (get_config('enable_mobileapi') and !get_config('opencourses_enable') and $eclass_banner_value == 0)
                                <div class='col-12 mb-3'>
                                    <div class='row'>
                                        <div class='col-6 pe-1'>
                                            <div class='panel panel-admin panel-social-media border-0 shadow-none ps-1 pe-1'>
                                                <div class='panel-body rounded-Home d-flex justify-content-center align-items-center'>
                                                    <a href='https://itunes.apple.com/us/app/open-eclass-mobile/id1398319489' target=_blank>
                                                        <img src='template/modern/images/appstore.png' class='img-responsive center-block m-auto d-block' alt='Available on the App Store'>
                                                    </a>
                                                </div>
                                            </div>
                                        </div>
                                        <div class='col-6'>
                                            <div class='panel panel-admin panel-social-media border-0 shadow-none ps-1 pe-1'>
                                                <div class='panel-body rounded-Home d-flex justify-content-center align-items-center'>
                                                    <a href='https://play.google.com/store/apps/details?id=gr.gunet.eclass' target=_blank>
                                                        <img src='template/modern/images/playstore.png' class='img-responsive center-block m-auto d-block' alt='Available on the Play Store'>
                                                    </a>
                                                </div>
                                            </div>
                                        </div>         
                                    </div>
                                </div>
                            <!-- only openecourses -->
                            @elseif (!get_config('enable_mobileapi') and get_config('opencourses_enable') and $eclass_banner_value == 0)
                                <div class='col-12 mb-3'>
                                    <div class='row'>
                                        @if (!isset($openCoursesExtraHTML))
                                        @php $openCoursesExtraHTML = ''; @endphp
                                            {!! setOpenCoursesExtraHTML() !!}
                                        @endif
                                        @if (get_config('opencourses_enable'))
                                            <div class='col-12'>
                                                <div class='panel panel-admin panel-open-courses border-0 shadow-none ps-1 pe-1'>
                                                    <div class='panel-body rounded-Home w-100 @if($openCoursesExtraHTML) d-flex justify-content-center align-items-center @endif'>
                                                        @if ($openCoursesExtraHTML)
                                                            {!! $openCoursesExtraHTML !!}
                                                        @else
                                                            <div class='col-12 d-flex justify-content-center align-items-center mt-3'>
                                                                <img class='w-50' style='height:100px;' src='{{$urlAppend}}template/modern/img/banner_open_courses.png' alt="{!! q($langListOpenCourses) !!}">
                                                            </div>
                                                            <div class='col-12 d-flex justify-content-center align-items-center mt-3'>
                                                                <a class='btn rounded-pill opencourses_btn TextBold d-flex justify-content-center align-items-center' href='http://opencourses.gr' target='_blank'>
                                                                    {{ trans('langNationalOpenCourses') }}
                                                                    <span class='fa fa-chevron-right ms-2'></span>
                                                                </a>
                                                            </div>
                                                        @endif
                                                    </div>
                                                </div>
                                            </div>
                                        @endif
                                    </div>
                                </div>
                            @endif

                            @if($popular_courses)
                                <div class="col-12 mb-3">
                                    <div class='panel panel-admin border-0 shadow-none'>
                                        <div class='panel-body rounded-Home px-5 py-4'>
                                            <div class="news">
                                                <div class='row'>
                                                    <div class='col-lg-12'>
                                                        <h4 class="block-title TextExtraBold text-uppercase pb-0 mt-2">{{trans('langPopularCourse')}}</h4>
                                                    </div>
                                                </div>
                                                <div class="row news-list m-auto">
                                                    
                                                    @foreach ($popular_courses as $pop_course)
                                                       
                                                        <div class="col-lg-3 news-list-item">
                                                            <div class="col-12 d-flex justify-content-center align-items-center">
                                                                <a href='{{$urlAppend}}courses/{{$pop_course->code}}/index.php'>
                                                                    @if($pop_course->course_image)
                                                                        <img class='popular_course_img' src='{{$urlAppend}}courses/{{$pop_course->code}}/image/{{$pop_course->course_image}}' alt='Course Banner'/>
                                                                    @else
                                                                        <img class='popular_course_img' src='{{$urlAppend}}template/modern/img/ph1.jpg'/>
                                                                    @endif
                                                                </a>
                                                            </div>
                                                            <div class="col-12 text-center mt-2">
                                                                <a class='lightBlueText TextSemiBold text-capitalize' href='{{$urlAppend}}courses/{{$pop_course->code}}/index.php'>
                                                                    {{$pop_course->title}} ({{$pop_course->public_code}})<br>
                                                                    <p class='textgreyColor small-text text-capitalize TextMedium'>{{$pop_course->prof_names}}</p>
                                                                </a>
                                                            </div>
                                                           
                                                        </div>
                                                    
                                                
                                                    @endforeach
                                                </div>
                                                <div class="more-link"><a class="all_courses mt-3 float-end text-uppercase" href="{{ $urlAppend }}modules/auth/opencourses.php?fc=1">{{ trans('langAllCourses') }} <span class='fa fa-angle-right fs-6 ms-1 fw-bold'></span></a></div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            @endif

                            @if ($announcements)
                                <div class="col-12 mb-3">
                                    <div class='panel panel-admin panel-announcements border-0 shadow-none'>
                                        <div class='panel-body rounded-Home px-5 py-4'>
                                            <div class="news">
                                                <div class='row'>
                                                    <div class='col-lg-1 pe-0'>
                                                        <img class='announcement-image' src="{{$urlAppend}}template/modern/img/announcement.svg">
                                                    </div>
                                                    <div class='col-lg-11'>
                                                        <h4 class="block-title TextExtraBold text-uppercase pb-0 mt-2">{{ trans('langAnnouncements') }}
                                                            <a href='{{ $urlServer }}rss.php'>
                                                                <span class='fa fa-rss-square'></span>
                                                            </a>
                                                        </h4>
                                                    </div>
                                                </div>
                                                <div class="row news-list m-auto">
                                                    @php $counterAn = 0; @endphp
                                                    @foreach ($announcements as $announcement)
                                                        @if($counterAn < 6)
                                                        <div class="col-12 news-list-item ps-2">
                                                            <div class="col-12">
                                                                <a href='modules/announcements/main_ann.php?aid={{ $announcement->id }}'>
                                                                    <span class='TextSemiBold lightBlueText fs-6'>{{$announcement->title}}</span>
                                                                </a>
                                                            </div>
                                                            <div class="date">
                                                                <small class='textgreyColor TextSemiBold'>{{ format_locale_date(strtotime($announcement->date)) }}</small>
                                                            </div>
                                                        </div>
                                                        @endif
                                                    @php $counterAn++; @endphp
                                                    @endforeach
                                                </div>
                                                <div class="more-link"><a class="all_announcements mt-3 float-end" href="{{ $urlAppend }}main/system_announcements.php">{{ trans('langAllAnnouncements') }} <span class='fa fa-angle-right fs-6 ms-1 fw-bold'></span></a></div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            @endif
                        </div>
                    </div>
                    @endif

                    @if(get_config('homepage_title') or get_config('homepage_intro') or get_config('enable_mobileapi') or get_config('opencourses_enable') or ($eclass_banner_value == 0) or $announcements)
                    <div class='d-block d-lg-none bgEclass pb-3 pt-0 ps-3 pe-3'>
                        @if(get_config('homepage_title') or get_config('homepage_intro'))
                            <div class='col-12 mt-3'>
                                <div class="panel panel-default homepageIntroPanel w-100 border-0">
                                    <div class="panel-body blackBlueText bg-body @if(get_config('homepage_title')) NoBorderTop @else Borders @endif">
                                        {!! get_config('homepage_intro') !!}
                                    </div>
                                    @if(get_config('homepage_title'))
                                        <div class='panel-footer'>
                                            <p class='text-center text-uppercase fs-6 TextExtraBold normalBlueText pb-4'> {!! get_config('homepage_title') !!}</p>
                                        </div>
                                    @endif
                                </div>
                            </div>
                        @endif

                        <!-- only eclass-banner -->
                        @if (!get_config('enable_mobileapi') and !get_config('opencourses_enable') and $eclass_banner_value == 1)
                            <div class='col-12 mt-3'>
                                <div class='panel panel-admin panel-banner border-0'>
                                    <div class='panel-body'>
                                        <a href="http://www.openeclass.org/" target="_blank">
                                            <img class="img-responsive center-block m-auto d-block" src="{{ $themeimg }}/open_eclass_banner.png" alt="Open eClass Banner">
                                        </a>
                                    </div>
                                </div>
                            </div>
                        <!-- only eclass-banner and mobileapi -->
                        @elseif (get_config('enable_mobileapi') and !get_config('opencourses_enable') and $eclass_banner_value == 1)
                            <div class='col-12 mt-3'>
                                <div class='row'>
                                    <div class='col-6 pe-1'>
                                        <div class='panel panel-admin panel-banner border-0'>
                                            <div class='panel-body'>
                                                <a href="http://www.openeclass.org/" target="_blank">
                                                    <img class="img-responsive center-block m-auto d-block" src="{{ $themeimg }}/open_eclass_banner.png" alt="Open eClass Banner">
                                                </a>
                                            </div>
                                        </div>
                                    </div>
                                    <div class='col-6'>
                                        <div class='panel panel-admin panel-social-media border-0'>
                                            <div class='panel-body'>
                                                <div class='col-12'>
                                                    <div class='row'>
                                                        <div class='col-6'>
                                                            <a class='d-flex justify-content-center align-items-center' href='https://itunes.apple.com/us/app/open-eclass-mobile/id1398319489' target=_blank>
                                                                <img src='template/modern/images/appstore.png' alt='Available on the App Store'>
                                                            </a>
                                                        </div>
                                                        <div class='col-6'>
                                                            <a class='d-flex justify-content-center align-items-center' href='https://play.google.com/store/apps/details?id=gr.gunet.eclass' target=_blank>
                                                                <img src='template/modern/images/playstore.png' alt='Available on the Play Store'>
                                                            </a>
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                         <!-- only eclass-banner and openecourses -->
                         @elseif (!get_config('enable_mobileapi') and $eclass_banner_value == 1)
                            <div class='col-12 mt-3'>
                                <div class='row'>
                                    <div class='col-12 mb-3'>
                                        <div class='panel panel-admin panel-banner border-0'>
                                            <div class='panel-body'>
                                                <a href="http://www.openeclass.org/" target="_blank">
                                                    <img class="img-responsive center-block m-auto d-block" src="{{ $themeimg }}/open_eclass_banner.png" alt="Open eClass Banner">
                                                </a>
                                            </div>
                                        </div>
                                    </div>
                                    <div class='col-lg-12'>
                                        @if (!isset($openCoursesExtraHTML))
                                            @php $openCoursesExtraHTML = ''; @endphp
                                            {!! setOpenCoursesExtraHTML() !!}
                                        @endif
                                        @if (get_config('opencourses_enable'))
                                            <div class='row'>
                                                <div class='col-12'>
                                                    <div class='panel panel-admin panel-open-courses border-0'>
                                                        <div class='panel-body w-100 @if($openCoursesExtraHTML) d-flex justify-content-center align-items-center @endif'>
                                                            @if ($openCoursesExtraHTML)
                                                                {!! $openCoursesExtraHTML !!}
                                                            @else
                                                                <div class='col-12 d-flex justify-content-center align-items-center mt-3'>
                                                                    <img class='w-50' style='height:100px;' src='{{$urlAppend}}template/modern/img/banner_open_courses.png' alt="{!! q($langListOpenCourses) !!}">
                                                                </div>
                                                                <div class='col-12 d-flex justify-content-center align-items-center mt-3'>
                                                                    <a class='btn rounded-pill opencourses_btn TextBold d-flex justify-content-center align-items-center' href='http://opencourses.gr' target='_blank'>
                                                                        {{ trans('langNationalOpenCourses') }}
                                                                        <span class='fa fa-chevron-right ms-2'></span>
                                                                    </a>
                                                                </div>
                                                            @endif
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>
                                        @endif
                                    </div>

                                </div>
                            </div>
                        <!-- only eclass-banner , mobileapi , openecourses -->
                        @elseif (get_config('enable_mobileapi') and get_config('opencourses_enable') and $eclass_banner_value == 1)
                            <div class='col-12 mt-3'>
                                <div class='row'>
                                    <div class='col-12'>
                                        <div class='panel panel-admin panel-banner border-0'>
                                            <div class='panel-body d-flex justify-content-center align-items-center'>
                                                <a class='d-flex justify-content-center align-items-center' href="http://www.openeclass.org/" target="_blank">
                                                    <img src="{{ $themeimg }}/open_eclass_banner.png" alt="Open eClass Banner">
                                                </a>
                                            </div>
                                        </div>
                                    </div>
                                    <div class='col-12 mt-3'>
                                        <div class='panel panel-admin panel-social-media border-0'>
                                            <div class='panel-body d-flex justify-content-center align-items-center'>
                                                <div class='col-12'>
                                                    <div class='row'>
                                                        <div class='col-6'>
                                                            <a href='https://itunes.apple.com/us/app/open-eclass-mobile/id1398319489' target=_blank>
                                                                <img src='template/modern/images/appstore.png' class='img-responsive center-block m-auto d-block' alt='Available on the App Store'>
                                                            </a>
                                                        </div>
                                                        <div class='col-6'>
                                                            <a href='https://play.google.com/store/apps/details?id=gr.gunet.eclass' target=_blank>
                                                                <img src='template/modern/images/playstore.png' class='img-responsive center-block m-auto d-block' alt='Available on the Play Store'>
                                                            </a>
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                    @if (!isset($openCoursesExtraHTML))
                                        @php $openCoursesExtraHTML = ''; @endphp
                                        {!! setOpenCoursesExtraHTML() !!}
                                    @endif
                                    @if (get_config('opencourses_enable'))
                                        <div class='col-12 mt-3'>
                                            <div class='panel panel-admin panel-open-courses border-0'>
                                                <div class='panel-body w-100 @if($openCoursesExtraHTML) d-flex justify-content-center align-items-center @endif'>
                                                    @if ($openCoursesExtraHTML)
                                                        {!! $openCoursesExtraHTML !!}
                                                    @else
                                                        <div class='col-12 d-flex justify-content-center align-items-center mt-3'>
                                                            <img class='w-50' style='height:100px;' src='{{$urlAppend}}template/modern/img/banner_open_courses.png' alt="{!! q($langListOpenCourses) !!}">
                                                        </div>
                                                        <div class='col-12 d-flex justify-content-center align-items-center mt-3'>
                                                            <a class='btn rounded-pill opencourses_btn TextBold d-flex justify-content-center align-items-center' href='http://opencourses.gr' target='_blank'>
                                                                {{ trans('langNationalOpenCourses') }}
                                                                <span class='fa fa-chevron-right ms-2'></span>
                                                            </a>
                                                        </div>
                                                    @endif
                                                </div>
                                            </div>
                                        </div>
                                    @endif
                                            
                                </div>
                            </div>
                        <!-- only mobileapi , openecourses -->
                        @elseif (get_config('enable_mobileapi') and get_config('opencourses_enable') and $eclass_banner_value == 0)
                            <div class='col-12 mt-3'>
                                <div class='row'>          
                                    <div class='col-md-6 col-12 pe-md-1'>
                                        <div class='panel panel-admin panel-social-media border-0'>
                                            <div class='panel-body d-flex justify-content-center align-items-center'>
                                                <a href='https://itunes.apple.com/us/app/open-eclass-mobile/id1398319489' target=_blank>
                                                    <img src='template/modern/images/appstore.png' class='img-responsive center-block m-auto d-block' alt='Available on the App Store'>
                                                </a>
                                            </div>
                                        </div>
                                    </div>
                                    <div class='col-md-6 col-12 mt-md-0 mt-3'>
                                        <div class='panel panel-admin panel-social-media border-0'>
                                            <div class='panel-body d-flex justify-content-center align-items-center'>
                                                <a href='https://play.google.com/store/apps/details?id=gr.gunet.eclass' target=_blank>
                                                    <img src='template/modern/images/playstore.png' class='img-responsive center-block m-auto d-block' alt='Available on the Play Store'>
                                                </a>
                                            </div>
                                        </div>
                                    </div>
                                    @if (!isset($openCoursesExtraHTML))
                                    @php $openCoursesExtraHTML = ''; @endphp
                                        {!! setOpenCoursesExtraHTML() !!}
                                    @endif
                                    @if (get_config('opencourses_enable'))
                                        <div class='col-12 mt-3'>
                                            <div class='panel panel-admin panel-open-courses border-0'>
                                                <div class='panel-body w-100 @if($openCoursesExtraHTML) d-flex justify-content-center align-items-center @endif'>
                                                    @if ($openCoursesExtraHTML)
                                                        {!! $openCoursesExtraHTML !!}
                                                    @else
                                                        <div class='col-12 d-flex justify-content-center align-items-center mt-3'>
                                                            <img class='w-50' style='height:100px;' src='{{$urlAppend}}template/modern/img/banner_open_courses.png' alt="{!! q($langListOpenCourses) !!}">
                                                        </div>
                                                        <div class='col-12 d-flex justify-content-center align-items-center mt-3'>
                                                            <a class='btn rounded-pill opencourses_btn TextBold d-flex justify-content-center align-items-center' href='http://opencourses.gr' target='_blank'>
                                                                {{ trans('langNationalOpenCourses') }}
                                                                <span class='fa fa-chevron-right ms-2'></span>
                                                            </a>
                                                        </div>
                                                    @endif
                                                </div>
                                            </div>
                                        </div>
                                    @endif      
                                </div>
                            </div>
                        <!-- only mobileapi -->
                        @elseif (get_config('enable_mobileapi') and !get_config('opencourses_enable') and $eclass_banner_value == 0)
                            <div class='col-12 mt-3'>
                                <div class='row'>
                                    <div class='col-6 pe-1'>
                                        <div class='panel panel-admin panel-social-media border-0'>
                                            <div class='panel-body d-flex justify-content-center align-items-center'>
                                                <a href='https://itunes.apple.com/us/app/open-eclass-mobile/id1398319489' target=_blank>
                                                    <img src='template/modern/images/appstore.png' class='img-responsive center-block m-auto d-block' alt='Available on the App Store'>
                                                </a>
                                            </div>
                                        </div>
                                    </div>
                                    <div class='col-6'>
                                        <div class='panel panel-admin panel-social-media border-0'>
                                            <div class='panel-body d-flex justify-content-center align-items-center'>
                                                <a href='https://play.google.com/store/apps/details?id=gr.gunet.eclass' target=_blank>
                                                    <img src='template/modern/images/playstore.png' class='img-responsive center-block m-auto d-block' alt='Available on the Play Store'>
                                                </a>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                         <!-- only openecourses -->
                         @elseif (!get_config('enable_mobileapi') and get_config('opencourses_enable') and $eclass_banner_value == 0)
                            <div class='col-12 mt-3'>
                                <div class='row'>
                                    @if (!isset($openCoursesExtraHTML))
                                    @php $openCoursesExtraHTML = ''; @endphp
                                        {!! setOpenCoursesExtraHTML() !!}
                                    @endif
                                    @if (get_config('opencourses_enable'))
                                        <div class='col-12'>
                                            <div class='panel panel-admin panel-open-courses border-0'>
                                                <div class='panel-body w-100 @if($openCoursesExtraHTML) d-flex justify-content-center align-items-center @endif'>
                                                    @if ($openCoursesExtraHTML)
                                                        {!! $openCoursesExtraHTML !!}
                                                    @else
                                                        <div class='col-12 d-flex justify-content-center align-items-center mt-3'>
                                                            <img class='w-50' style='height:100px;' src='{{$urlAppend}}template/modern/img/banner_open_courses.png' alt="{!! q($langListOpenCourses) !!}">
                                                        </div>
                                                        <div class='col-12 d-flex justify-content-center align-items-center mt-3'>
                                                            <a class='btn rounded-pill opencourses_btn TextBold d-flex justify-content-center align-items-center' href='http://opencourses.gr' target='_blank'>
                                                                {{ trans('langNationalOpenCourses') }}
                                                                <span class='fa fa-chevron-right ms-2'></span>
                                                            </a>
                                                        </div>
                                                    @endif
                                                </div>
                                            </div>
                                        </div>
                                    @endif
                                </div>
                            </div>
                        @endif

                        @if($popular_courses)
                                <div class="col-12 mt-3">
                                    <div class='panel panel-admin'>
                                        <div class='panel-body'>
                                            <div class="news">
                                                <div class='row'>
                                                    <div class='col-lg-12'>
                                                        <h4 class="block-title TextExtraBold text-uppercase pb-0 mt-2">{{trans('langPopularCourse')}}</h4>
                                                    </div>
                                                </div>
                                                <div class="row news-list m-auto">
                                                   
                                                    @foreach ($popular_courses as $pop_course)
                                                        
                                                        <div class="col-md-6 col-12 news-list-item">
                                                            <div class="col-12 d-flex justify-content-center align-items-center">
                                                                <a href='{{$urlAppend}}courses/{{$pop_course->code}}/index.php'>
                                                                    @if($pop_course->course_image)
                                                                        <img class='popular_course_img' src='{{$urlAppend}}courses/{{$pop_course->code}}/image/{{$pop_course->course_image}}' alt='Course Banner'/>
                                                                    @else
                                                                        <img class='popular_course_img' src='{{$urlAppend}}template/modern/img/ph1.jpg'/>
                                                                    @endif
                                                                </a>
                                                            </div>
                                                            <div class="col-12 text-center mt-2">
                                                                <a class='lightBlueText TextSemiBold text-capitalize' href='{{$urlAppend}}courses/{{$pop_course->code}}/index.php'>
                                                                    {{$pop_course->title}} ({{$pop_course->public_code}})<br>
                                                                    <p class='textgreyColor small-text text-capitalize TextMedium'>{{$pop_course->prof_names}}</p>
                                                                </a>
                                                            </div>
                                                           
                                                        </div>
                                                        
                                                    
                                                    @endforeach
                                                </div>
                                                <div class="more-link"><a class="all_courses mt-3 float-end text-uppercase" href="{{ $urlAppend }}modules/auth/opencourses.php?fc=1">{{ trans('langAllCourses') }} <span class='fa fa-angle-right fs-6 ms-1 fw-bold'></span></a></div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            @endif

                        @if ($announcements)
                            <div class='col-12 mt-3'>
                                
                                        <div class='panel panel-admin border-0'>
                                            <div class='panel-body'>
                                                <div class="news">
                                                    <div class='row'>
                                                        <div class='col-2 pe-0'>
                                                            <img class='announcement-image' src="{{$urlAppend}}template/modern/img/announcement.svg">
                                                        </div>
                                                        <div class='col-10'>
                                                            <h4 class="block-title TextExtraBold text-uppercase pb-0 mt-2">{{ trans('langAnnouncements') }}
                                                                <a href='{{ $urlServer }}rss.php'>
                                                                    <span class='fa fa-rss-square'></span>
                                                                </a>
                                                            </h4>
                                                        </div>
                                                    </div>
                                                    <div class="row news-list m-auto">
                                                        @php $counterAn = 0; @endphp
                                                        @foreach ($announcements as $announcement)
                                                            @if($counterAn < 6)
                                                            <div class="col-12 news-list-item ps-2">
                                                                <div class="col-12">
                                                                    <a href='modules/announcements/main_ann.php?aid={{ $announcement->id }}'>
                                                                        <span class='TextSemiBold lightBlueText fs-6'>{{$announcement->title}}</span>
                                                                    </a>
                                                                </div>
                                                                <div class="date">
                                                                    <small class='textgreyColor TextSemiBold'>{{ format_locale_date(strtotime($announcement->date)) }}</small>
                                                                </div>
                                                            </div>
                                                            @endif
                                                        @php $counterAn++; @endphp
                                                        @endforeach
                                                    </div>
                                                    <div class="more-link"><a class="all_announcements mt-3 float-end" href="{{ $urlServer }}main/system_announcements.php">{{ trans('langAllAnnouncements') }} <span class='fa fa-angle-right fs-6 ms-1 fw-bold'></span></a></div>
                                                </div>
                                            </div>
                                        </div>
                                   
                            </div>
                        @endif
                    </div>
                    @endif


                </div>
            </div>
        </div>
    </div>

</div>


<div class="container-fluid statistics mt-0 @if(get_config('dont_display_testimonials')) mb-3 @endif">
    <div class='row rowMedium'>
        <div class="statistics-wrapper">
            <h2 class="TextExtraBold text-center pt-lg-0 pt-4">
                {{trans('langViewStatics')}}
            </h2>
            <div class="row row_pad_courses">
                <div class="col-sm-4 text-center">
                        <img class="statistics-icon m-auto d-block" src="{{ $urlAppend }}template/modern/img/statistics_1.svg">
                        @php $course_inactive = Database::get()->querySingle("SELECT COUNT(*) as count FROM course WHERE visible != ?d", COURSE_INACTIVE)->count; @endphp
                        <div class="num TextBold">{{ $course_inactive }}</div>
                        <div class="num-text text-lowercase TextLight">{{trans('langsCourses')}}</div>
                </div>
                <div class="col-sm-4 text-center">
                        
                        <img class="statistics-icon m-auto d-block" src="{{ $urlAppend }}template/modern/img/statistics_2.svg">
                        <div class="num TextBold">10<span class='num-plus TextRegular'>K+</span></div>
                        <div class="num-text text-lowercase TextLight">{{trans('langUserLogins')}}/{{trans('langWeek')}}</div>
                </div>
                <div class="col-sm-4 text-center">
                        <img class="statistics-icon m-auto d-block" src="{{ $urlAppend }}template/modern/img/statistics_3.svg">
                        <div class="num TextBold">{{ getOnlineUsers() }}</div>
                        <div class="num-text text-lowercase TextLight">{{trans('langOnlineUsers')}}</div>
                </div>
            </div>
        </div>
    </div>
</div>

@if(!get_config('dont_display_testimonials'))
<div class="d-flex justify-content-center">
    <div class="container-fluid testimonials mt-lg-0 mb-lg-0 mt-0 mb-0">
        <div class="testimonial">
            <div class="testimonial-body">Lorem ipsum dolor sit amet, consectetur adipiscing elit, sed do eiusmod tempor incididunt ut labore et dolore magna aliqua. Ut enim ad minim veniam, quis nostrud exercitation ullamco laboris nisi ut aliquip ex ea commodo consequat. Duis aute irure dolor in reprehenderit in voluptate velit esse cillum dolore eu fugiat nulla pariatur. Excepteur sint occaecat cupidatat non proident, sunt in culpa qui officia deserunt mollit anim id est laborum</div>
            <div class="testimonial-person mt-3 fst-italic"><small>- Λυδία Καλομοίρη -</small></div>
        </div>
        <div class="testimonial">
            <div class="testimonial-body">Sed ut perspiciatis unde omnis iste natus error sit voluptatem accusantium doloremque laudantium, totam rem aperiam, eaque ipsa quae ab illo inventore veritatis et quasi architecto beatae vitae dicta sunt explicabo. Nemo enim ipsam voluptatem quia voluptas sit aspernatur aut odit aut fugit, sed quia consequuntur magni dolores eos qui ratione voluptatem sequi nesciunt. Neque porro quisquam est, qui dolorem ipsum quia dolor sit amet, consectetur, adipisci velit, sed quia non numquam eius modi tempora incidunt ut labore et dolore magnam aliquam quaerat voluptatem.</div>
            <div class="testimonial-person mt-3 fst-italic"><small>- Γιάννης Ιωάννου -</small></div>
        </div>
        <div class="testimonial">
            <div class="testimonial-body">Lorem ipsum dolor sit amet, consectetur adipiscing elit, sed do eiusmod tempor incididunt ut labore et dolore magna aliqua. Ut enim ad minim veniam.</div>
            <div class="testimonial-person mt-3 fst-italic"><small>- Νίκος Παπαδάκης -</small></div>
        </div>
    </div>
</div>
@endif

<script>

    $('.ContentEclass').removeClass('container');
    $('.ContentEclass').addClass('container-fluid');
    $('.ContentEclass.container-fluid').css('padding-left','0px');
    $('.ContentEclass.container-fluid').css('padding-right','0px');
    
    $('#link-home'+'>'+'img').attr("src","{{ $urlAppend }}template/modern/img/home_2.svg");
    function unhoverHome(obj) {
        if(!current_url.includes('/main/portfolio.php')){
            $('#'+obj.id+'>'+'img').attr("src","{{ $urlAppend }}template/modern/img/home_2.svg");
        }
    }
   
    $('.testimonials').slick({
		autoplay:true,
		autoplaySpeed:4000,
		centerMode: true,
		slidesToShow: 1,
		responsive: [
            {
                breakpoint: 768,
                settings: { centerPadding: '0vw' }
		    },
            {
                breakpoint: 2561,
                settings: { centerPadding: '15vw' }
		    },
            {
                breakpoint: 3561,
                settings: { centerPadding: '10vw' }
		    },
            {
                breakpoint: 4561,
                settings: { centerPadding: '7vw' }
		    },
            {
                breakpoint: 5561,
                settings: { centerPadding: '5vw' }
		    },
            {
                breakpoint: 10000,
                settings: { centerPadding: '3vw' }
		    }
        ]
	});

    

</script>

@endsection
