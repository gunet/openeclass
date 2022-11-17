@extends('layouts.default')

@section('content')


<div class="pb-lg-3 pt-lg-3 pb-0 pt-0">

    <div class="container-fluid main-container">

        <div class="row rowMedium">

            <div class="col-12 justify-content-center col_maincontent_active_HomepageStart">

                <div class="row">

                    @if(!(get_config('upgrade_begin') || get_config('dont_display_login_form')))
                        <div class='@if($leftsideImg) col-md-8 offset-md-2 col-12 @if(!get_config("homepage_title") and !get_config("homepage_intro") and !get_config("enable_mobileapi") and !get_config("opencourses_enable") and $eclass_banner_value == 0) mb-md-5 @endif mt-md-5 mt-0 rounded-0 border-0 @else @if(!get_config("homepage_title") and !get_config("homepage_intro") and !get_config("enable_mobileapi") and !get_config("opencourses_enable") and $eclass_banner_value == 0) Borders @else BordersTop @endif col-12 @endif card jumbotron jumbotron-login'>
                            <div class='row'>
                                @if($warning)<div class='col-12 mt-4 mb-0'>{!! $warning !!}</div>@endif
                                <div class='@if($leftsideImg) col-12 @else col-xl-6 col-lg-7 col-md-8 col-12 @endif'>

                                    @php 
                                        $q = Database::get()->queryArray("SELECT auth_id, auth_name, auth_default, auth_title
                                             FROM auth WHERE auth_default <> 0
                                             ORDER BY auth_default DESC, auth_id");
                                    @endphp

                                    @if($q)
                                        <div id="carouselLoginAuthControls" class="carousel slide" data-bs-ride="carousel">
                                            {{--@if(count($q)>1)
                                                <div class="carousel-indicators pb-4">
                                                    @php $ccount = 0; @endphp
                                                    @foreach($q as $l)
                                                        @if($ccount == 0)
                                                            <button type="button" data-bs-target="#carouselLoginAuthControls" data-bs-slide-to="{{$ccount}}" class="active" aria-current="true" aria-label="Slide {{$ccount}}"></button>
                                                        @else
                                                            <button type="button" data-bs-target="#carouselLoginAuthControls" data-bs-slide-to="{{$ccount}}" aria-label="Slide {{$ccount}}"></button>
                                                        @endif
                                                        @php $ccount++ @endphp
                                                    @endforeach
                                                </div>
                                            @endif--}}
                                            <div class="carousel-inner">
                                                @foreach($q as $l)
                                                    @if($l->auth_name == 'eclass' and $l->auth_default == 1)
                                                        <div class="carousel-item active">
                                                            <div class='card-body mt-md-5 mb-md-5 me-md-5 ms-md-5 mt-5 mb-5 ms-3 me-3 Borders cardLogin'>
                                                                <div class='card-header d-flex justify-content-center bg-transparent border-0'>
                                                                    <img class="UserLoginIcon me-1" src="{{ $urlAppend }}template/modern/img/user_login.svg"> 
                                                                    <span class="fs-5 TextBold blackBlueText text-capitalize">{{ trans('langUserLogin') }}</span>
                                                                </div>
                                                                <form action="{{ $urlAppend }}" method="post">
                                                                    <div class="login-form-spacing mt-2">
                                                                        <input id='username_id' class="rounded-pill login-input @if(count($q)>1) w-75 @else w-100 @endif" placeholder="{{ trans('langUsername') }}" type="text" id="uname" name="uname" autocomplete="on">
                                                                        <input id='password_id' class="rounded-pill login-input @if(count($q)>1) w-75 @else w-100 @endif mt-4" placeholder="{{ trans('langPassword') }}" type="password" id="pass" name="pass" autocomplete="on">
                                                                        <input class="rounded-pill btn @if(count($q)>1) w-75 @else w-100 @endif login-form-submit TextBold mt-md-4 mb-md-0 mt-4 mb-4" type="submit" name="submit" value="{{ trans('langLogin') }}">
                                                                    </div>
                                                                </form>
                                                                <div class='col-sm-12 d-flex justify-content-center'>
                                                                    <a class="orangeText btnlostpass mb-2 mt-md-4" href="{{$urlAppend}}modules/auth/lostpass.php">{{ trans('lang_forgot_pass') }}</a>
                                                                </div>
                                                            </div>
                                                        </div>
                                                    @endif
                                                    @if($l->auth_name == 'pop3' and $l->auth_default == 1)
                                                       
                                                        <div class="carousel-item">
                                                            <div class='card-body mt-md-5 mb-md-5 me-md-5 ms-md-5 mt-5 mb-5 ms-3 me-3 Borders cardLogin d-flex justify-content-center align-items-center'>
                                                                <div class='row'>
                                                                    <div class='col-12 mb-3'>
                                                                        <div class='card-header bg-transparent border-0'>
                                                                            <img class="UserLoginIcon m-auto d-block" src="{{ $urlAppend }}template/modern/img/user_login.svg">
                                                                            <p class="fs-5 TextBold mb-0 text-center blackBlueText text-capitalize">{{ get_auth_info(2) }}</p>
                                                                        </div>
                                                                    </div>
                                                                    <div class='col-12 text-center'>
                                                                        <a class="rounded-pill btn login-form-submit TextBold w-75"  href='{{$urlAppend}}modules/auth/altnewuser.php?auth=2'>{{ trans('langUserLogin') }}</a>
                                                                    </div>
                                                                </div>
                                                            </div>
                                                        </div>
                                                       
                                                    @endif
                                                    @if($l->auth_name == 'ldap' and $l->auth_default == 1)
                                                       
                                                        <div class="carousel-item">
                                                            <div class='card-body mt-md-5 mb-md-5 me-md-5 ms-md-5 mt-5 mb-5 ms-3 me-3 Borders cardLogin d-flex justify-content-center align-items-center'>
                                                                <div class='row'>
                                                                    <div class='col-12 mb-3'>
                                                                        <div class='card-header bg-transparent border-0'>
                                                                            <img class="UserLoginIcon m-auto d-block" src="{{ $urlAppend }}template/modern/img/user_login.svg">
                                                                            <p class="fs-5 TextBold mb-0 text-center blackBlueText text-capitalize">{{ get_auth_info(4) }}</p>
                                                                        </div>
                                                                    </div>
                                                                    <div class='col-12 text-center'>
                                                                        <a class="rounded-pill btn login-form-submit TextBold w-75"  href='{{$urlAppend}}modules/auth/altnewuser.php?auth=4'>{{ trans('langUserLogin') }}</a>
                                                                    </div>
                                                                </div>
                                                            </div>
                                                        </div>
                                                       
                                                    @endif
                                                    @if($l->auth_name == 'imap' and $l->auth_default == 1)
                                                        <div class="carousel-item">
                                                            <div class='card-body mt-md-5 mb-md-5 me-md-5 ms-md-5 mt-5 mb-5 ms-3 me-3 Borders cardLogin d-flex justify-content-center align-items-center'>
                                                                <div class='row'>
                                                                    <div class='col-12 mb-3'>
                                                                        <div class='card-header bg-transparent border-0'>
                                                                            <img class="UserLoginIcon m-auto d-block" src="{{ $urlAppend }}template/modern/img/user_login.svg">
                                                                            <p class="fs-5 TextBold mb-0 text-center blackBlueText text-capitalize">{{ get_auth_info(3) }}</p>
                                                                        </div>
                                                                    </div>
                                                                    <div class='col-12 text-center'>
                                                                        <a class="rounded-pill btn login-form-submit TextBold w-75"  href='{{$urlAppend}}modules/auth/altnewuser.php?auth=3'>{{ trans('langUserLogin') }}</a>
                                                                    </div>
                                                                </div>
                                                            </div>
                                                        </div>
                                                    @endif
                                                    @if($l->auth_name == 'db' and $l->auth_default == 1)
                                                        <div class="carousel-item">
                                                            <div class='card-body mt-md-5 mb-md-5 me-md-5 ms-md-5 mt-5 mb-5 ms-3 me-3 Borders cardLogin d-flex justify-content-center align-items-center'>
                                                                <div class='row'>
                                                                    <div class='col-12 mb-3'>
                                                                        <div class='card-header bg-transparent border-0'>
                                                                            <img class="UserLoginIcon m-auto d-block" src="{{ $urlAppend }}template/modern/img/user_login.svg">
                                                                            <p class="fs-5 TextBold mb-0 text-center blackBlueText text-capitalize">{{ get_auth_info(5) }}</p>
                                                                        </div>
                                                                    </div>
                                                                    <div class='col-12 text-center'>
                                                                        <a class="rounded-pill btn login-form-submit TextBold w-75"  href='{{$urlAppend}}modules/auth/altnewuser.php?auth=5'>{{ trans('langUserLogin') }}</a>
                                                                    </div>
                                                                </div>
                                                            </div>
                                                        </div>
                                                    @endif
                                                    @if($l->auth_name == 'shibboleth' and $l->auth_default == 1)
                                                        <div class="carousel-item">
                                                            <div class='card-body mt-md-5 mb-md-5 me-md-5 ms-md-5 mt-5 mb-5 ms-3 me-3 Borders cardLogin d-flex justify-content-center align-items-center'>
                                                                <div class='row'>
                                                                    <div class='col-12 mb-3'>
                                                                        <div class='card-header bg-transparent border-0'>
                                                                            <img class="UserLoginIcon m-auto d-block" src="{{ $urlAppend }}template/modern/img/user_login.svg">
                                                                            <p class="fs-5 TextBold mb-0 text-center blackBlueText text-capitalize">{!! q(getSerializedMessage($l->auth_title)) !!}</p>
                                                                        </div>
                                                                    </div>
                                                                    <div class='col-12 text-center'>
                                                                        <a class='rounded-pill btn login-form-submit TextBold w-75' href='{{$urlAppend}}secure/'>{{ trans('langUserLogin') }}</a>
                                                                    </div>
                                                                </div>   
                                                            </div>
                                                        </div>
                                                    @endif
                                                    @if($l->auth_name == 'cas' and $l->auth_default == 1)
                                                        <div class="carousel-item">
                                                            <div class='card-body mt-md-5 mb-md-5 me-md-5 ms-md-5 mt-5 mb-5 ms-3 me-3 Borders cardLogin d-flex justify-content-center align-items-center'>
                                                                 <div class='row'>
                                                                    <div class='col-12 mb-3'>
                                                                        <div class='card-header bg-transparent border-0'>
                                                                            <img class="UserLoginIcon m-auto d-block" src="{{ $urlAppend }}template/modern/img/user_login.svg">
                                                                            <p class="fs-5 TextBold mb-0 text-center blackBlueText text-capitalize">{!! q(getSerializedMessage($l->auth_title)) !!}</p>
                                                                        </div>
                                                                    </div>
                                                                    <div class='col-12 text-center'>
                                                                        <a class='rounded-pill btn login-form-submit TextBold w-75' href='{{$urlAppend}}modules/auth/cas.php'>{{ trans('langUserLogin') }}</a>
                                                                    </div>
                                                                </div>
                                                            </div>
                                                        </div>
                                                    @endif
                                                @endforeach
      
                                            </div>
                                            @if(count($q)>1)
                                                <button class="carousel-control-prev" type="button" data-bs-target="#carouselLoginAuthControls" data-bs-slide="prev">
                                                    <span class="fa fa-chevron-left carouselPrevHome" aria-hidden="true"></span>
                                                </button>
                                                <button class="carousel-control-next" type="button" data-bs-target="#carouselLoginAuthControls" data-bs-slide="next">
                                                    <span class="fa fa-chevron-right carouselNextHome" aria-hidden="true"></span>
                                                </button>
                                            @endif
                                        </div>
                                    @endif
                                    
                                </div>
                                <div class='@if($leftsideImg) col-0 @else col-xl-6 col-lg-5 col-md-4 col-0 @endif'></div>
                            </div>
                        </div>
                      
                    @else
                        <div class='d-none d-md-none d-lg-block'>
                            <div class='row'>
                                <div class='card h-400px jumbotron jumbotron-login BordersTop'></div>
                            </div>
                        </div>
                        <div class='d-block d-md-block d-lg-none'>
                            <div class='row'>
                                <div class='card h-400px jumbotron jumbotron-login NoBorders'></div>
                            </div>
                        </div>
                    @endif


                    @if(get_config('homepage_title') or get_config('homepage_intro'))
                        <div class='d-none d-sm-none d-md-block'>
                            <div class='row rowMedium'>
                                <div class='col-12 ps-md-5 pe-md-5 pt-md-5 pb-md-5'>
                                    <div class="panel panel-default homepageIntroPanel w-100 border-0 rounded-0">
                                        @if(get_config('homepage_title'))
                                        <div class="panel-heading text-center rounded-0">
                                            {!! get_config('homepage_title') !!}
                                        </div>
                                        @endif
                                        <div class="panel-body blackBlueText bg-body @if(get_config('homepage_title')) NoBorderTop @else Borders @endif rounded-0 p-5">
                                            {!! get_config('homepage_intro') !!}
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    @endif

                    @if(get_config('homepage_title') or get_config('homepage_intro'))
                        <div class='d-block d-md-none'>
                            <div class='row rowMedium'>
                                <div class='col-12 pt-5 @if(!get_config("enable_mobileapi") and $eclass_banner_value == 0) pb-5 @endif)'>
                                    <div class="panel panel-default w-100 border-0 rounded-0">
                                        @if(get_config('homepage_title'))
                                        <div class="panel-heading text-center rounded-0">
                                            {!! get_config('homepage_title') !!}
                                        </div>
                                        @endif
                                        <div class='panel-body blackBlueText rounded-0'>
                                            {!! get_config('homepage_intro') !!}
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    @endif


                    @if (get_config('enable_mobileapi'))
                        @if(!get_config('homepage_title') and !get_config('homepage_intro'))
                        <div class='col-12 ps-md-5 pe-md-5 pt-md-5 pt-5 mb-5'>
                        @else
                        <div class='col-12 ps-md-5 pe-md-5 pt-md-0 pt-5 mb-5'>
                        @endif
                            <div class='row rowMedium'>
                                <div class='col-md-6 col-12' id='openeclass-banner'>
                                    <div class='panel panel-admin border-0 rounded-0'>
                                        <div class='panel-body rounded-0'>
                                            <a href="http://www.openeclass.org/" target="_blank">
                                                <img class="img-responsive center-block m-auto d-block" src="{{ $themeimg }}/open_eclass_banner.png" alt="Open eClass Banner">
                                            </a>
                                        </div>
                                    </div>
                                </div>
                                <div class='@if($eclass_banner_value != 0) col-md-6 col-12 @else col-12 @endif mt-md-0 pt-md-0 @if($eclass_banner_value == 0) pt-0 @else pt-5 @endif'>
                                    <div class='panel panel-admin border-0 rounded-0 panel-social-homepage'>
                                        <div class='panel-body rounded-0'>
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
                    @else
                        @if(!get_config('homepage_title') and !get_config('homepage_intro'))
                        <div class='col-12 ps-md-5 pe-md-5 pt-md-5 pt-5 mb-5' id='openeclass-banner'>
                        @else
                        <div class='col-12 ps-md-5 pe-md-5 pt-md-0 pt-5 mb-5' id='openeclass-banner'>
                        @endif
                            <div class='row rowMedium'>
                                <div class='col-12'>
                                    <div class='panel panel-admin border-0 rounded-0'>
                                        <div class='panel-body rounded-0'>
                                            <a href="http://www.openeclass.org/" target="_blank">
                                                <img class="img-responsive center-block m-auto d-block" src="{{ $themeimg }}/open_eclass_banner.png" alt="Open eClass Banner">
                                            </a>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    @endif


                    @if (!isset($openCoursesExtraHTML))
                        @php $openCoursesExtraHTML = ''; @endphp
                        {!! setOpenCoursesExtraHTML() !!}
                    @endif
                    @if (get_config('opencourses_enable'))
                        @if(!get_config("homepage_title") and !get_config("homepage_intro") and !get_config("enable_mobileapi") and $eclass_banner_value == 0)
                          <div class='mt-5'></div>
                        @endif
                        @if ($openCoursesExtraHTML)
                        <div class='col-12 ps-md-5 pe-md-5 pt-md-0 mb-5'>
                            <div class='row rowMedium'>
                                <div class='col-12'>
                                    <div class='panel panel-admin border-0 rounded-0'>
                                        <div class='panel-body rounded-0'>
                                            {!! $openCoursesExtraHTML !!}
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                        @endif
                        <div class='col-12 ps-md-5 pe-md-5 pt-md-0 mb-5'>
                            <div class='row rowMedium'>
                                <div class='col-12'>
                                    <div class='panel panel-default openCoursesPanel text-center p-2 shadow-sm border-0'>
                                        <a class='text-white' href='http://opencourses.gr' target='_blank'>
                                            {{ trans('langNationalOpenCourses') }}
                                        </a>
                                    </div>
                                </div>
                            </div>
                        </div>
                    @endif
                </div>
            </div>
        </div>
    </div>

</div>


<!-- collapse menu -->

    @if ($announcements)
    <div class="container-fluid main-section">
        <div class="row rowMedium">
            <div class="col-lg-12 border-15px p-md-5 ps-4 pe-4 pt-5 pb-5 Announcements-Homepage">
                <div class="news">
                    <h3 class="block-title TextExtraBold">{{ trans('langAnnouncements') }}
                        <a href='{{ $urlServer }}rss.php'>
                            <span class='fa fa-rss-square'></span>
                        </a>
                    </h3>
                    <div class="row news-list m-auto">
                        @php $counterAn = 0; @endphp
                        @foreach ($announcements as $announcement)
                            @if($counterAn < 6)
                            <div class="col-sm-12 news-list-item">
                                <div class="title">
                                    <a class="d-inline-flex align-items-top" href='modules/announcements/main_ann.php?aid={{ $announcement->id }}'>
                                        <span class="fa fa-bullhorn me-1 mt-1 normalBlueText" aria-hidden="true"></span>
                                        <span class='TextSemiBold normalBlueText fs-6'>{{$announcement->title}}</span>
                                    </a>
                                </div>
                                <div class="date ms-3">
                                    <small class='text-secondary TextSemiBold'>{{ format_locale_date(strtotime($announcement->date)) }}</small>
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


    
    <div class="container-fluid statistics @if($announcements) mt-lg-3 @else mt-lg-0 @endif mt-0">
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

    <div class="d-flex justify-content-center mt-lg-3 mb-lg-3">
        <div class="container-fluid testimonials mt-lg-0 mb-lg-0 mt-0 mb-0 bg-light">
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

<script>
   
    document.body.addEventListener("click", function (e) {
        clickerUsernamePassword = 0;
        if (!e.target.classList.contains('login-input')) {
            $('.carousel-control-prev').css('display','block');
            $('.carousel-control-next').css('display','block'); 
            $('.owl-carousel').owlCarousel({
                autoplay:true
            });
        }
    });

    $('.login-input').on('click',function(){
        $('.owl-carousel').owlCarousel({
            autoplay:false,
            rewindNav : true,
            rewindSpeed: 0
        });
        $('.carousel-control-prev').css('display','none');
        $('.carousel-control-next').css('display','none');

	});
    
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
