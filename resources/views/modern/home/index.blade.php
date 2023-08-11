@extends('layouts.default')

@section('content')

<link href="{{ $urlAppend }}template/modern/css/homepage.css" rel="stylesheet" type="text/css">

<div class="col-12 main-section">

        @if($warning)
            <input id='showWarningModal' type='hidden' value='1'>
            <div class="modal fade bg-light" id="WarningModal" aria-hidden="true" tabindex="-1">
                <div class="modal-dialog modal-dialog-centered">
                    <div class="modal-content shadow-sm border-0 p-0">
                        <div class="modal-header bgOrange d-flex justify-content-between align-items-center">
                            <h5 class="modal-title text-white">{{ trans('langError') }}</h5>
                            <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
                        </div>
                        <div class="modal-body bg-white">
                            {!! $warning !!}
                        </div>
                    </div>
                </div>
            </div>
        @else
            <input id='showWarningModal' type='hidden' value='0'>
        @endif
       
    
        <div class="jumbotron jumbotron-login">
            <div class='{{ $container }}'>
                <div class='row m-auto'>
                    <div class='col-12'>
                        <div class='row row-cols-1 row-cols-lg-2 g-lg-5'>
                            <div class='col-xxl-6 col-lg-5 col-12 @if($PositionFormLogin) ms-auto me-auto @endif'>
                                <h1 class='eclass-title'>{{ trans('langEclass') }}</h1>
                                <p class='eclassInfo'>{{ trans('langEclassInfo')}}</p>
                                @if(!(get_config('upgrade_begin') || get_config('dont_display_login_form')))
                                    <div class='card cardLogin border-0 px-xxl-5 pt-xxl-5 pb-xxl-3 p-lg-3 mt-3'>
                                        <div class='card-header bg-transparent border-0 text-center'>
                                            <h2>{{ trans('langUserLogin') }}</h2>
                                        </div>
                                        <div class='card-body'>
                                            <form class='mt-0' action="{{ $urlAppend }}" method="post">
                                                <div>
                                                    <label for='username_id' class='form-label'>{{ trans('langUsername') }}</label>
                                                    <input id='username_id' class="login-input w-100" placeholder="&#xf007" type="text" id="uname" name="uname" autocomplete="on" />
                                                    <label for='password_id' class='form-label mt-4'>{{ trans('langPassword') }}&nbsp(password)</label>
                                                    <input id='password_id' class="login-input w-100" placeholder="&#xf084" type="password" id="pass" name="pass" autocomplete="on" />
                                                    <input class="btn w-100 login-form-submit mt-4" type="submit" name="submit" value="{{ trans('langLogin') }}" />
                                                </div>
                                            </form>
                                        </div>
                                        <div class='card-footer border-0 bg-transparent'>
                                            <div class='col-12 text-center'>
                                                <a class="text-decoration-underline" href="{{$urlAppend}}modules/auth/lostpass.php">{{ trans('lang_forgot_pass') }}</a>
                                            </div>
                                            <div class='col-12 text-center mt-2 mb-lg-0 mb-2'>
                                                <a class="vsmall-text TextBold lightBlueText" href="{{$urlAppend}}main/login_form.php">{{ trans('langMoreLogin') }}</a>
                                            </div>
                                        </div>
                                    </div>
                                @endif
                                @if(get_config('enable_mobileapi'))
                                    <div class="col-12 mobileAPI d-flex @if(get_config('dont_display_login_form')) justify-content-start @else justify-content-center @endif align-items-start mb-lg-0 mt-3 mb-3">
                                        <a class='pe-3' href='https://itunes.apple.com/us/app/open-eclass-mobile/id1398319489' target=_blank>
                                            <img style='width:150px;' src='template/modern/img/GooglePlay.svg' class='img-responsive center-block m-auto d-block' alt='Available on the App Store'>
                                        </a>
                                        <a href='https://play.google.com/store/apps/details?id=gr.gunet.eclass' target=_blank>
                                            <img style='width:150px;' src='template/modern/img/AppStore.svg' class='img-responsive center-block m-auto d-block' alt='Available on the Play Store'>
                                        </a>
                                    </div>
                                @endif

                            </div>
                            <div class='col-xxl-6 col-lg-7 col-12 d-none @if($PositionFormLogin) d-lg-none @else d-lg-block @endif'>
                                <img class='jumbotron-image-default' src='{{ $urlAppend }}template/modern/img/jumbotron-eclass4.png'>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>


        @if(get_config('homepage_title') or get_config('homepage_intro'))
            <div class='{{ $container }}'>
                <div class='row m-auto'>
                    <div class='col-12'>
                        <div class="row row-cols-1 g-5">
                            @if(get_config('homepage_title') or get_config('homepage_intro'))
                                <div class='col'>
                                    <div class='card border-0 bg-transparent'>
                                        <div class='card-header border-0 bg-transparent d-flex justify-content-between align-items-center px-0 py-0'>
                                            <div class='d-flex justify-content-start align-items-center'>
                                                <h3 class='pe-2'>
                                                    @if(get_config('homepage_title'))
                                                        {!! get_config('homepage_title') !!}
                                                    @else
                                                        {{ trans('langHomePageIntroText') }}
                                                    @endif
                                                </h3>
                                            </div>
                                        </div>
                                        <div class='card-body px-0 py-0'>
                                            @if(get_config('homepage_intro'))
                                                <div class='TextRegular msmall-text Neutral-800-cl mt-3'>{!! get_config('homepage_intro') !!}</div>
                                            @else
                                                <div class='TextRegular msmall-text Neutral-800-cl mt-3'>{{ trans('langNoInfoAvailable') }}</div>
                                            @endif
                                        </div>
                                    </div>
                                </div>
                            @endif
                        </div>
                    </div>
                </div>
            </div>
        @endif



        <div class='{{ $container }}'>
            <div class='row m-auto'>
                <div class='col-12'>
                    <div class='row row-cols-1 row-cols-lg-2 g-5'>
                        <div class='col-lg-6 col-12'>
                            <div class='card bg-transparent border-0'>
                                <div class='card-header border-0 bg-transparent d-flex justify-content-between align-items-center px-0 py-0'>
                                    <div class='d-flex justify-content-start align-items-center'>
                                        <h3 class='pe-2'>{{ trans('langViewStatics') }}</h3>
                                    </div>
                                </div>
                                <div class='card-body px-0 py-3'>
                                    <div class='col-12'>
                                        <div class='row row-cols-1 row-cols-md-3 g-lg-3'>
                                            <div class='col mb-lg-0 mb-4'>
                                                <div class='card statistics-card border-default-card drop-shadow'>
                                                    <div class='card-body Primary-200-bg d-flex justify-content-center align-items-center'>
                                                        <div>
                                                            <div class='d-flex justify-content-center'>
                                                                <img src='{{ $urlAppend }}template/modern/images/Icons_book-open.svg'>
                                                                @php $course_inactive = Database::get()->querySingle("SELECT COUNT(*) as count FROM course WHERE visible != ?d", COURSE_INACTIVE)->count; @endphp
                                                                <h1 class='mb-0 ms-2'>{{ $course_inactive }}</h1>
                                                            </div>
                                                            <p class='form-label text-center'>{{ trans('langCourses') }}</p>
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>
                                            <div class='col mb-lg-0 mb-4'>
                                                <div class='card statistics-card border-default-card drop-shadow'>
                                                    <div class='card-body Primary-200-bg d-flex justify-content-center align-items-center'>
                                                        <div>
                                                            <div class='d-flex justify-content-center'>
                                                                <img src='{{ $urlAppend }}template/modern/images/Icons_globe.svg'>
                                                                <h1 class='mb-0 ms-2'>10K+</h1>
                                                            </div>
                                                            <p class='form-label text-center'>{{trans('langUserLogins')}}/</br>{{trans('langWeek')}}</p>
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>
                                            <div class='col mb-lg-0 mb-4'>
                                                <div class='card statistics-card border-default-card drop-shadow'>
                                                    <div class='card-body Primary-200-bg d-flex justify-content-center align-items-center'>
                                                        <div>
                                                            <div class='d-flex justify-content-center'>
                                                                <img src='{{ $urlAppend }}template/modern/images/Icons_user.svg'>
                                                                <h1 class='mb-0 ms-2'>{{ getOnlineUsers() }}</h1>
                                                            </div>
                                                            <p class='form-label text-center'>{{trans('langOnlineUsers')}}</p>
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class='col-lg-6 col-12'>
                            <div class='card bg-transparent border-0'>
                                <div class='card-header border-0 bg-transparent d-flex justify-content-between align-items-center px-0 py-0'>
                                    <div class='d-flex justify-content-start align-items-center'>
                                        <h3 class='pe-2'>{{ trans('langAnnouncements') }}</h3>
                                        <a href='{{ $urlServer }}rss.php'><i class="fa-solid fa-rss"></i></a>
                                    </div>
                                    <div class='d-flex justify-content-end align-items-center'>
                                        <a class='TextRegular text-decoration-underline msmall-text' href="{{ $urlServer }}main/system_announcements.php">{{ trans('langAllAnnouncements') }}...</a>
                                    </div>
                                </div>
                                <div class='card-body px-0 py-0'>
                                    @php $counterAn = 0; @endphp
                                    @if(count($announcements) > 0)
                                        <ul class='list-group list-group-flush'>
                                            @foreach ($announcements as $announcement)
                                                @if($counterAn < 3)
                                                    <li class='li-unstyled border-bottom-list-group px-0 py-3'>
                                                        <a class='list-group-item announce-link-homepage bg-transparent border-0 px-0 py-0 TextBold msmall-text' href='modules/announcements/main_ann.php?aid={{ $announcement->id }}'>
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
                    </div>
                </div>
            </div>
        </div>


        @if(!get_config('dont_display_testimonials') or $eclass_banner_value == 1 or get_config('opencourses_enable'))
            <div class='{{ $container }}'>
                <div class='row m-auto'>
                    <div class='col-12'>
                        <div class='row row-cols-1 row-cols-lg-2 g-5'>
                            @if(!get_config('dont_display_testimonials'))
                                <div class='col-lg-6 col-12'>
                                    <div class='card bg-transparent border-0'>
                                        <div class='card-header border-0 bg-transparent d-flex justify-content-between align-items-center px-0 py-0'>
                                            <h3>{{ trans('langSaidForUs') }}</h3>
                                        </div>
                                        <div class='card-body px-0'>
                                            <div id="carouselHomepage" class="carousel slide" data-bs-ride="carousel">
                                                <div class="carousel-inner">
                                                    <?php for($i=0; $i<3; $i++){ ?>
                                                        <div class="carousel-item @if($i==0) active @endif">
                                                            <div class='col-12 d-md-flex gap-3 px-5'>
                                                                <div class='card cardTestimonial bg-transparent border-0 d-flex align-items-strech'>
                                                                    <div class='card-body Primary-200-bg'>
                                                                        <p class="Neutral-800-cl">Lorem Ipsum är en utfyllnadstext från tryck- och förlagsindustrin. Lorem ipsum har varit standard ända sedan 1500-talet.</p>
                                                                    </div>
                                                                    <div class='card-footer text-end border-0 Primary-200-bg'>
                                                                        <div class="form-label">John Smith</div>
                                                                    </div>
                                                                </div>
                                                                <div class='card cardTestimonial bg-transparent border-0 d-flex align-items-strech '>
                                                                    <div class='card-body Primary-200-bg'>
                                                                        <p class="Neutral-800-cl">Lorem Ipsum är en utfyllnadstext från tryck- och förlagsindustrin. Lorem ipsum har varit standard ända sedan 1500-talet..</p>
                                                                    </div>
                                                                    <div class='card-footer text-end border-0 Primary-200-bg'>
                                                                        <div class="form-label">John Smith</div>
                                                                    </div>
                                                                </div>
                                                            </div>
                                                        </div>
                                                    <?php } ?>
                                                </div>
                                                <button class="carousel-control-prev" type="button" data-bs-target="#carouselHomepage" data-bs-slide="prev">
                                                    <span class="carousel-control-prev-icon" aria-hidden="true"></span>
                                                </button>
                                                <button class="carousel-control-next" type="button" data-bs-target="#carouselHomepage" data-bs-slide="next">
                                                    <span class="carousel-control-next-icon" aria-hidden="true"></span>
                                                </button>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            @endif
                            @if($eclass_banner_value == 1 or get_config('opencourses_enable'))
                                <div class='col-lg-6 col-12'>
                                    <div class='col-12'>
                                        <div class='row row-cols-1 row-cols-lg-2 g-3'>
                                            @if($eclass_banner_value == 1)
                                                <div class="@if((get_config('dont_display_testimonials') and  !get_config('opencourses_enable')) or (!get_config('opencourses_enable'))) col-xl-8 ms-auto me-auto @else col-xl-4 @endif col-12 banner_openCourses">
                                                    <div class='card border-card h-100'>
                                                        <div class='card-body d-flex justify-content-center align-items-center'>
                                                            <a href="http://www.openeclass.org/" target="_blank">
                                                                <img class="img-responsive banner-img" src="{{ $themeimg }}/Open-Eclass-Banner.svg" alt="Open eClass Banner">
                                                            </a>
                                                        </div>
                                                    </div>
                                                </div>
                                            @endif
                                            @if(get_config('opencourses_enable'))
                                                @if ($openCoursesExtraHTML)
                                                    {!! $openCoursesExtraHTML !!}
                                                @endif
                                            @endif
                                        </div>
                                    </div>
                                </div>
                            @endif
                        </div>
                    </div>
                </div>
            </div>
        @endif


        @if(get_config('mentoring_platform'))
            <div class='{{ $container }}'>
                <div class='row m-auto'>
                    <div class='col-12'>
                        <div class="row row-cols-lg-2">
                            <div class='col-lg-6'>
                                <div class='card border-card bg-transparent'>
                                    <div class='card-header border-0 bg-transparent d-flex justify-content-between align-items-center px-3 py-2 mb-0'>
                                        <div class='d-flex justify-content-start align-items-center'>
                                            <h3>
                                                {{trans('langMentoringPlatform')}}
                                            </h3>
                                        </div>
                                    </div>
                                    <div class='card-body px-3'>
                                        <div class="col-12 mt-0">
                                            {!! trans('MentoringEnableHomepageEclass') !!}
                                        
                                            <a id='goToMentoringPlatformBtn' class='btn submitAdminBtn w-auto float-end mt-3' 
                                                href='{{ $urlAppend }}?goToMentoring=true'>
                                                {{ trans('langPlatformMentoring') }}<span class="fa fa-chevron-right ms-2"></span>
                                            </a>
                                            
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        @endif


        @if($popular_courses)
            <div class='{{ $container }}'>
                <div class='row m-auto'>
                    <div class='col-12'>
                        <div class="row row-cols-1">
                            <div class='col'>
                                <div class='card bg-transparent border-0'>
                                    <div class='card-header border-0 bg-transparent d-flex justify-content-between align-items-center px-0 py-0 mb-3'>
                                        <div class='d-flex justify-content-start align-items-center'>
                                            <h3 class='pe-2'>
                                                {{trans('langPopularCourse')}}
                                            </h3>
                                        </div>
                                    </div>
                                    <div class='card-body px-0 py-0'>
                                        <div class='row rowMargin row-cols-1 row-cols-md-2 row-cols-lg-3 g-lg-5'>
                                            @foreach ($popular_courses as $pop_course)
                                                <div class="col mb-lg-0 mb-4">
                                                    <div class='card border-card h-100'>
                                                        <div class='card-body'>
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
                                                                <a class='TextBold msmall-text' href='{{$urlAppend}}courses/{{$pop_course->code}}/index.php'>
                                                                    {{$pop_course->title}} ({{$pop_course->public_code}})<br>
                                                                    <p class='TextRegular msmall-text Neutral-800-cl'>{{$pop_course->prof_names}}</p>
                                                                </a>
                                                            </div>
                                                        </div>
                                                    </div>
                                                </div>
                                            @endforeach
                                        </div>
                                    </div>
                                </div>
                            </div>
                            
                        </div>
                    </div>
                </div>
            </div>
        @endif



        @if($texts)
            <div class='{{ $container }}'>
                <div class='row m-auto'>
                    <div class='col-12'>
                        <div class="row row-cols-1 @if(count($texts) > 1) row-cols-lg-2 @endif g-5">
                            @foreach($texts as $text)
                                <div class='col'>
                                    <div class='card bg-transparent border-0'>
                                        <div class='card-header border-0 bg-transparent d-flex justify-content-between align-items-center px-0 py-0'>
                                            <div class='d-flex justify-content-start align-items-center'>
                                                <h3 class='pe-2'>
                                                    {!! $text->title !!}
                                                </h3>
                                            </div>
                                        </div>
                                        <div class='card-body px-0 py-0'>
                                            <div class='TextRegular msmall-text Neutral-800-cl mt-3'>{!! $text->body !!}</div>
                                        </div>
                                    </div>
                                </div>
                            @endforeach
                        </div>
                    </div>
                </div>
            </div>
        @endif

</div>
        
{{--
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
--}}



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
