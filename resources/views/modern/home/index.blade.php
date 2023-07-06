@extends('layouts.default')

@section('content')

<link href="{{ $urlAppend }}template/modern/css/homepage.css" rel="stylesheet" type="text/css">

<div class="col-12 container-Homepage bgEclass col_maincontent_active_HomepageStart HomepageStartMobile">

        @if($warning)
            <input id='showWarningModal' type='hidden' value='1'>
            <div class="modal fade bg-light" id="WarningModal" aria-hidden="true" tabindex="-1">
                <div class="modal-dialog modal-dialog-centered">
                    <div class="modal-content shadow-sm border-0">
                        <div class="modal-header bgOrange">
                            <h5 class="modal-title text-white">{{ trans('langError') }}</h5>
                            <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                        </div>
                        <div class="modal-body bgEclass">
                            {!! $warning !!}
                        </div>
                    </div>
                </div>
            </div>
        @else
            <input id='showWarningModal' type='hidden' value='0'>
        @endif
       
    
        <div class="jumbotron jumbotron-login @if($PositionFormLogin == 1 and !get_config('dont_display_login_form')) rebuiltCenterJumpotron @endif">
            <div class='container-fluid'>
                <div class='col-12 p-lg-5 p-3'>
                    <div class='row rowMargin row-cols-1 row-cols-lg-2 g-5'>
                        <div class='col-lg-6 col-12'>
                            <h1>{{ trans('langEclass') }}</h1>
                            <p>{{ trans('langEclassInfo')}}</p>
                            @if(!(get_config('upgrade_begin') || get_config('dont_display_login_form')))
                                <div class='card cardLogin border-0 p-3'>
                                    <div class='card-header bg-transparent border-0'>
                                        <h2>{{ trans('langUserLogin') }}</h2>
                                    </div>
                                    <div class='card-body'>
                                        <form class='mt-0' action="{{ $urlAppend }}" method="post">
                                            <div>
                                                <label for='username_id' class='form-label'>{{ trans('langUsername') }}</label>
                                                <input id='username_id' class="login-input w-100" placeholder="" type="text" id="uname" name="uname" autocomplete="on">
                                                <label for='password_id' class='form-label mt-4'>{{ trans('langPassword') }}&nbsp(password)</label>
                                                <input id='password_id' class="login-input w-100" placeholder="" type="password" id="pass" name="pass" autocomplete="on">
                                                <input class="btn w-100 login-form-submit Primary-500-bg text-white mt-4" type="submit" name="submit" value="{{ trans('langLogin') }}">
                                            </div>
                                        </form>
                                    </div>
                                    <div class='card-footer border-0 bg-transparent'>
                                        <div class='col-12 text-start'>
                                            <a class="text-decoration-underline" href="{{$urlAppend}}modules/auth/lostpass.php">{{ trans('lang_forgot_pass') }}</a>
                                        </div>
                                        <div class='col-12 text-start mt-3'>
                                            <a class="vsmall-text TextBold lightBlueText" href="{{$urlAppend}}main/login_form.php">{{ trans('langMoreLogin') }}</a>
                                        </div>
                                    </div>
                                </div>
                            @endif
                            <div class='col-12 d-flex justify-content-start align-items-center'>
                                <a class='pe-3' href='https://itunes.apple.com/us/app/open-eclass-mobile/id1398319489' target=_blank>
                                    <img style='height:100px; width:150px;' src='template/modern/img/GooglePlay.svg' class='img-responsive center-block m-auto d-block' alt='Available on the App Store'>
                                </a>
                                <a href='https://play.google.com/store/apps/details?id=gr.gunet.eclass' target=_blank>
                                    <img style='height:100px; width:150px;' src='template/modern/img/AppStore.svg' class='img-responsive center-block m-auto d-block' alt='Available on the Play Store'>
                                </a>
                            </div>
                        </div>
                        <div class='col-lg-6 col-12 d-none d-lg-block'>
                            <img class='jumbotron-image-default' src='{{ $urlAppend }}template/modern/img/jumbotron-eclass-4.0.png'>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class='container-fluid bg-white'>
            <div class='col-12 p-lg-5 p-3 bg-white'>
                <div class='row rowMargin row-cols-1 row-cols-lg-2 g-5'>
                    <div class='col-lg-6 col-12'>
                        <div class='card border-0'>
                            <div class='card-header border-bottom-card-header bg-white d-flex justify-content-between align-items-center px-0 py-0'>
                                <div class='d-flex justify-content-start align-items-center'>
                                    <h3 class='pe-2'>{{ trans('langAnnouncements') }}</h3>
                                    <a href='{{ $urlServer }}rss.php'><i class="fa-solid fa-rss"></i></a>
                                </div>
                                <div class='d-flex justify-content-end align-items-center'>
                                    <a class='TextRegular text-decoration-underline msmall-text Primary-500-cl text-lowercase' href="{{ $urlServer }}main/system_announcements.php">{{ trans('langAllAnnouncements') }}...</a>
                                </div>
                            </div>
                            <div class='card-body px-0 py-0'>
                                @php $counterAn = 0; @endphp
                                @if(count($announcements) > 0)
                                    <ul class='list-group list-group-flush'>
                                        @foreach ($announcements as $announcement)
                                            @if($counterAn < 6)
                                                <li class='li-unstyled border-bottom-list-group px-0 py-3'>
                                                    <a class='list-group-item border-0 px-0 py-0 TextSemiBold msmall-text Primary-500-cl' href='modules/announcements/main_ann.php?aid={{ $announcement->id }}'>
                                                        {{$announcement->title}}
                                                    </a>
                                                    <div class='TextRegular msmall-text Neutral-800-cl mt-1'>{{ format_locale_date(strtotime($announcement->date)) }}</div>
                                                </li>
                                            @endif
                                            @php $counterAn++; @endphp
                                        @endforeach
                                    </ul>
                                @else
                                    <ul class='list-group list-group-flush'>
                                        <li class='li-unstyled border-bottom-list-group px-0 py-3'>
                                            <div class='TextRegular msmall-text Neutral-800-cl mt-1'>{{ trans('langNoInfoAvailable') }}</div>
                                        </li>
                                    </ul>
                                @endif
                            </div>
                        </div>
                    </div>
                    <div class='col-lg-6 col-12'>
                        <div class='card border-0'>
                            <div class='card-header border-bottom-card-header bg-white d-flex justify-content-between align-items-center px-0 py-0'>
                                <div class='d-flex justify-content-start align-items-center'>
                                    <h3 class='pe-2'>{{ trans('langViewStatics') }}</h3>
                                </div>
                            </div>
                            <div class='card-body px-0 py-3'>
                                <div class='col-12'>
                                    <div class='row rowMargin row-cols-1 row-cols-md-3 g-5'>
                                        <div class='col'>
                                            <div class='card border-card drop-shadow h-100'>
                                                <div class='card-body d-flex justify-content-center align-items-center'>
                                                    <div class='d-flex justify-content-center align-items-center'>
                                                        <img src='{{ $urlAppend }}template/modern/images/Icons_book-open.svg'>
                                                        @php $course_inactive = Database::get()->querySingle("SELECT COUNT(*) as count FROM course WHERE visible != ?d", COURSE_INACTIVE)->count; @endphp
                                                        <h1 class='mb-0 ms-2'>{{ $course_inactive }}</h1>
                                                    </div>
                                                </div>
                                                <div class='card-footer d-flex justify-content-center align-items-center pt-0 bg-white border-0'>
                                                    <p class='Neutral-900-cl mb-3'>{{ trans('langCourses') }}</p>
                                                </div>
                                            </div>
                                        </div>
                                        <div class='col'>
                                            <div class='card border-card drop-shadow h-100'>
                                                <div class='card-body d-flex justify-content-center align-items-center'>
                                                    <div class='d-flex justify-content-center align-items-center'>
                                                        <img src='{{ $urlAppend }}template/modern/images/Icons_user.svg'>
                                                        <h1 class='mb-0 ms-2'>10K+</h1>
                                                    </div>
                                                </div>
                                                <div class='card-footer d-flex justify-content-center align-items-center pt-0 bg-white border-0'>
                                                    <p class='Neutral-900-cl mb-3 text-center'>{{trans('langUserLogins')}}/</br>{{trans('langWeek')}}</p>
                                                </div>
                                            </div>
                                        </div>
                                        <div class='col'>
                                            <div class='card border-card drop-shadow h-100'>
                                                <div class='card-body d-flex justify-content-center align-items-center'>
                                                    <div class='d-flex justify-content-center align-items-center'>
                                                        <img src='{{ $urlAppend }}template/modern/images/Icons_user.svg'>
                                                        <h1 class='mb-0 ms-2'>{{ getOnlineUsers() }}</h1>
                                                    </div>
                                                </div>
                                                <div class='card-footer d-flex justify-content-center align-items-center pt-0 bg-white border-0'>
                                                    <p class='Neutral-900-cl mb-3 text-center'>{{trans('langOnlineUsers')}}</p>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        
                                                    
        @if(get_config('homepage_title') or get_config('homepage_intro') or get_config('enable_mobileapi') or get_config('opencourses_enable') or ($eclass_banner_value == 0) or $announcements or $popular_courses or $texts)
        <div class='d-none d-lg-block px-4 bg-white'>
            <div class='SectionMenu ms-auto me-auto bg-transparent pt-4 pb-3'>
                
                @if(get_config('homepage_title') or get_config('homepage_intro'))
                    <div class="col-12 d-flex justify-content-center homepage_intro-margin @if($PositionFormLogin == 1 and !$warning and !get_config('dont_display_login_form')) rebuiltHomepageIntro @endif mb-3">
                        <div class="panel panel-default homepageIntroPanel w-100 border-0 shadow-none">
                            <div class="panel-body blackBlueText bg-body @if(get_config('homepage_title')) NoBorderTop @else Borders @endif p-5">
                                @if(get_config('homepage_title'))
                                    <p class="text-center fs-4 normalBlueText TextBold  @if(get_config('homepage_intro')) mb-4 @else mb-0 @endif">{!! get_config('homepage_title') !!}</p>
                                @endif
                                @if(get_config('homepage_intro'))
                                    {!! get_config('homepage_intro') !!}
                                @endif
                            
                            </div>
                            
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
                            <div class='col-lg-3 mb-lg-0 pe-lg-1'>
                                <div class='panel panel-admin panel-banner h-100 border-0 shadow-none ps-1 pe-1'>
                                    <div class='panel-body rounded-Home d-flex justify-content-center align-items-center'>
                                        <a href="http://www.openeclass.org/" target="_blank">
                                            <img class="img-responsive center-block m-auto d-block" src="{{ $themeimg }}/open_eclass_banner.png" alt="Open eClass Banner">
                                        </a>
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
                                                                <span class='fa-solid fa-chevron-right ms-2'></span>
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
                                                                <span class='fa-solid fa-chevron-right ms-2'></span>
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
                            <div class='col-lg-3 pe-1'>
                                
                                    
                                <div class='panel panel-admin panel-social-media h-100 border-0 shadow-none ps-1 pe-1'>
                                    <div class='panel-body h-100 d-flex justify-content-center align-items-center'>
                                        <div>
                                            <a href='https://itunes.apple.com/us/app/open-eclass-mobile/id1398319489' target=_blank>
                                                <img src='template/modern/images/appstore.png' class='img-responsive center-block m-auto d-block mb-lg-4' alt='Available on the App Store'>
                                            </a>
                                            <a href='https://play.google.com/store/apps/details?id=gr.gunet.eclass' target=_blank>
                                                <img src='template/modern/images/playstore.png' class='img-responsive center-block m-auto d-block' alt='Available on the Play Store'>
                                            </a>
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
                                                                <span class='fa-solid fa-chevron-right ms-2'></span>
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
                                <div class='panel panel-admin panel-social-media h-100 border-0 shadow-none ps-1 pe-1'>
                                    <div class='panel-body h-100 rounded-Home d-flex justify-content-center align-items-center p-3'>
                                        <a href='https://itunes.apple.com/us/app/open-eclass-mobile/id1398319489' target=_blank>
                                            <img style='height:100px; width:150px;' src='template/modern/images/appstore.png' class='img-responsive center-block m-auto d-block' alt='Available on the App Store'>
                                        </a>
                                    </div>
                                </div>
                            </div>
                            <div class='col-6'>
                                <div class='panel panel-admin panel-social-media h-100 border-0 shadow-none ps-1 pe-1'>
                                    <div class='panel-body h-100 rounded-Home d-flex justify-content-center align-items-center p-3'>
                                        <a href='https://play.google.com/store/apps/details?id=gr.gunet.eclass' target=_blank>
                                            <img style='height:100px; width:150px;' src='template/modern/images/playstore.png' class='img-responsive center-block m-auto d-block' alt='Available on the Play Store'>
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
                                                        <span class='fa-solid fa-chevron-right ms-2'></span>
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
                                                    <a class='lightBlueText TextSemiBold text-capitalize fs-6' href='{{$urlAppend}}courses/{{$pop_course->code}}/index.php'>
                                                        {{$pop_course->title}} ({{$pop_course->public_code}})<br>
                                                        <p class='textgreyColor small-text text-capitalize TextMedium'>{{$pop_course->prof_names}}</p>
                                                    </a>
                                                </div>
                                                
                                            </div>
                                        
                                    
                                        @endforeach
                                    </div>
                                    <div class="more-link"><a class="all_courses mt-3 float-end text-uppercase" href="{{ $urlAppend }}modules/auth/listfaculte.php">{{ trans('langAllCourses') }} <span class='fa fa-angle-right fs-6 ms-1 fw-bold'></span></a></div>
                                </div>
                            </div>
                        </div>
                    </div>
                @endif


                @if($texts)
                    @foreach($texts as $text)
                        <div class="col-12 mb-3">
                            <div class='panel panel-admin border-0 shadow-none'>
                                <div class='panel-body rounded-Home px-5 py-4'>
                                    <div class="news">
                                        <div class='row'>
                                            <div class='col-lg-12'>
                                                <h4 class="block-title TextExtraBold text-uppercase pb-0 mt-2">{!! $text->title !!}</h4>
                                            </div>
                                        </div>
                                        
                                        {!! $text->body !!}

                                    </div>
                                </div>
                            </div>
                        </div>
                    @endforeach
                @endif

            </div>
        </div>
        @endif



        @if(get_config('homepage_title') or get_config('homepage_intro') or get_config('enable_mobileapi') or get_config('opencourses_enable') or ($eclass_banner_value == 0) or $announcements or $popular_courses or $texts)
        <div class='d-block d-lg-none bg-white pb-3 pt-0 ps-3 pe-3'>
            @if(get_config('homepage_title') or get_config('homepage_intro'))
                <div class='col-12 mt-3'>
                    <div class="panel panel-default homepageIntroPanel w-100 border-0 shadow-none">
                        <div class="panel-body blackBlueText bg-body @if(get_config('homepage_title')) NoBorderTop @else Borders @endif p-3">
                                @if(get_config('homepage_title'))
                                    <p class="text-center fs-4 normalBlueText TextSemiBold @if(get_config('homepage_intro')) mb-3 @else mb-0 @endif">{!! get_config('homepage_title') !!}</p>
                                @endif
                                @if(get_config('homepage_intro'))
                                    {!! get_config('homepage_intro') !!}
                                @endif
                        </div>
                    
                    </div>
                </div>
            @endif

            <!-- only eclass-banner -->
            @if (!get_config('enable_mobileapi') and !get_config('opencourses_enable') and $eclass_banner_value == 1)
                <div class='col-12 mt-3'>
                    <div class='panel panel-admin panel-banner border-0 shadow-none'>
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
                        <div class='col-md-6 col-12 pe-md-1'>
                            <div class='panel panel-admin panel-banner border-0 shadow-none'>
                                <div class='panel-body'>
                                    <a href="http://www.openeclass.org/" target="_blank">
                                        <img class="img-responsive center-block m-auto d-block" src="{{ $themeimg }}/open_eclass_banner.png" alt="Open eClass Banner">
                                    </a>
                                </div>
                            </div>
                        </div>
                        <div class='col-md-6 col-12 mt-md-0 mt-3'>
                            <div class='panel panel-admin panel-social-media border-0 shadow-none'>
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
                        <div class='col-md-3 col-12 mb-md-0 mb-3 pe-md-1'>
                            <div class='panel panel-admin panel-banner h-100 border-0 shadow-none'>
                                <div class='panel-body'>
                                    <a href="http://www.openeclass.org/" target="_blank">
                                        <img style='height:50px;' class="img-responsive center-block m-auto d-block" src="{{ $themeimg }}/open_eclass_banner.png" alt="Open eClass Banner">
                                    </a>
                                </div>
                            </div>
                        </div>
                        <div class='col-md-9 col-lg-12'>
                            @if (!isset($openCoursesExtraHTML))
                                @php $openCoursesExtraHTML = ''; @endphp
                                {!! setOpenCoursesExtraHTML() !!}
                            @endif
                            @if (get_config('opencourses_enable'))
                                <div class='row'>
                                    <div class='col-12'>
                                        <div class='panel panel-admin panel-open-courses border-0 shadow-none'>
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
                                                            <span class='fa-solid fa-chevron-right ms-2'></span>
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
                        <div class='col-md-6 col-12 pe-md-1'>
                            <div class='panel panel-admin panel-banner border-0 shadow-none'>
                                <div class='panel-body d-flex justify-content-center align-items-center'>
                                    <a class='d-flex justify-content-center align-items-center' href="http://www.openeclass.org/" target="_blank">
                                        <img src="{{ $themeimg }}/open_eclass_banner.png" alt="Open eClass Banner">
                                    </a>
                                </div>
                            </div>
                        </div>
                        <div class='col-md-6 col-12 mt-md-0 mt-3'>
                            <div class='panel panel-admin panel-social-media border-0 shadow-none'>
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
                                <div class='panel panel-admin panel-open-courses border-0 shadow-none'>
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
                                                    <span class='fa-solid fa-chevron-right ms-2'></span>
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
                        <div class='col-md-3 col-12 pe-md-1'>
                            <div class='panel panel-admin panel-social-media border-0 shadow-none h-100'>
                                <div class='panel-body'>
                                    <div class='d-md-block d-none'>
                                        <a href='https://itunes.apple.com/us/app/open-eclass-mobile/id1398319489' target=_blank>
                                            <img src='template/modern/images/appstore.png' class='img-responsive center-block m-auto d-block mb-md-5' alt='Available on the App Store'>
                                        </a>
                                        <a href='https://play.google.com/store/apps/details?id=gr.gunet.eclass' target=_blank>
                                            <img src='template/modern/images/playstore.png' class='img-responsive center-block m-auto d-block' alt='Available on the Play Store'>
                                        </a>
                                    </div>
                                    <div class='d-block d-md-none d-flex justify-content-center align-items-center'>
                                        <a href='https://itunes.apple.com/us/app/open-eclass-mobile/id1398319489' target=_blank>
                                            <img src='template/modern/images/appstore.png' class='img-responsive center-block m-auto d-block' alt='Available on the App Store'>
                                        </a>
                                        <a href='https://play.google.com/store/apps/details?id=gr.gunet.eclass' target=_blank>
                                            <img src='template/modern/images/playstore.png' class='img-responsive center-block m-auto d-block' alt='Available on the Play Store'>
                                        </a>
                                    </div>
                                </div>
                            </div>
                        </div>

                        @if (!isset($openCoursesExtraHTML))
                            @php $openCoursesExtraHTML = ''; @endphp
                            {!! setOpenCoursesExtraHTML() !!}
                        @endif
                        
                        <div class='col-md-9 col-12 mt-md-0 mt-3'>
                            <div class='panel panel-admin panel-open-courses border-0 shadow-none'>
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
                                                <span class='fa-solid fa-chevron-right ms-2'></span>
                                            </a>
                                        </div>
                                    @endif
                                </div>
                            </div>
                        </div>
                                
                    </div>
                </div>
            <!-- only mobileapi -->
            @elseif (get_config('enable_mobileapi') and !get_config('opencourses_enable') and $eclass_banner_value == 0)
                <div class='col-12 mt-3'>
                    <div class='row'>
                        <div class='col-6 pe-1'>
                            <div class='panel panel-admin panel-social-media border-0 shadow-none'>
                                <div class='panel-body d-flex justify-content-center align-items-center'>
                                    <a href='https://itunes.apple.com/us/app/open-eclass-mobile/id1398319489' target=_blank>
                                        <img src='template/modern/images/appstore.png' class='img-responsive center-block m-auto d-block' alt='Available on the App Store'>
                                    </a>
                                </div>
                            </div>
                        </div>
                        <div class='col-6'>
                            <div class='panel panel-admin panel-social-media border-0 shadow-none'>
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
                                <div class='panel panel-admin panel-open-courses border-0 shadow-none'>
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
                                                    <span class='fa-solid fa-chevron-right ms-2'></span>
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
                        <div class='panel panel-admin shadow-none'>
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
                                                    <a class='lightBlueText TextSemiBold text-capitalize fs-6' href='{{$urlAppend}}courses/{{$pop_course->code}}/index.php'>
                                                        {{$pop_course->title}} ({{$pop_course->public_code}})<br>
                                                        <p class='textgreyColor small-text text-capitalize TextMedium'>{{$pop_course->prof_names}}</p>
                                                    </a>
                                                </div>
                                                
                                            </div>
                                            
                                        
                                        @endforeach
                                    </div>
                                    <div class="more-link"><a class="all_courses mt-3 float-end text-uppercase" href="{{ $urlAppend }}modules/auth/listfaculte.php">{{ trans('langAllCourses') }} <span class='fa fa-angle-right fs-6 ms-1 fw-bold'></span></a></div>
                                </div>
                            </div>
                        </div>
                    </div>
                @endif

            

                @if($texts)
                    @foreach($texts as $text)
                        <div class="col-12 mt-3">
                            <div class='panel panel-admin shadow-none'>
                                <div class='panel-body'>
                                    <div class="news">
                                        <div class='row'>
                                            <div class='col-lg-12'>
                                                <h4 class="block-title TextExtraBold text-uppercase pb-0 mt-2">{!! $text->title !!}</h4>
                                            </div>
                                        </div>
                                        
                                        {!! $text->body !!}

                                    </div>
                                </div>
                            </div>
                        </div>
                    @endforeach
                @endif

        </div>
        @endif
    
</div>
        

@if(!get_config('dont_display_testimonials'))
<div class="d-flex justify-content-center">
    <div class="col-12 testimonials mt-lg-0 mb-lg-0 mt-0 mb-0">
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

    $('.basic-content').removeClass('container');
   
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


    if($('#showWarningModal').val() == 1){
        var myModal = new bootstrap.Modal(document.getElementById('WarningModal'));
        myModal.show();
    }
   

</script>

@endsection
