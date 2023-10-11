@extends('layouts.default')

@section('content')

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
       
    
        @if(!get_config('show_only_loginScreen'))
            <div class="jumbotron jumbotron-login">
                <div class='{{ $container }} jumbotron-container'>
                    <div class='row m-auto'>
                        <div class='col-12 mt-lg-4'>
                            <div class='row row-cols-1 row-intro'>
                                <div class='col-12'>
                                    <div class='intro-content'>
                                        @if(get_config('homepage_title'))
                                            <h1 class='eclass-title'>{!! get_config('homepage_title') !!}</h1>
                                        @else
                                            <h1 class='eclass-title'>{{ trans('langEclass') }}</h1>
                                        @endif

                                        @if(get_config('homepage_intro'))
                                            <p class='eclassInfo'>{!! get_config('homepage_intro') !!}</p>
                                        @else
                                            <p class='eclassInfo'>{{ trans('langEclassInfo')}}</p>
                                        @endif
                                    </div>
                                </div>
                            </div>
                            <div class='row row-cols-1'>
                                @if(get_config('enable_mobileapi') || $eclass_banner_value == 1)
                                    <div class="col-12 d-flex justify-content-between align-items-center mt-3">
                                        
                                        @if(get_config('enable_mobileapi'))
                                            <div class='d-flex gap-3 pe-3'>
                                                <a href='https://itunes.apple.com/us/app/open-eclass-mobile/id1398319489' target=_blank>
                                                    <img style='width:150px;' src='template/modern/img/GooglePlay.svg' class='img-responsive center-block m-auto d-block' alt='Available on the App Store'>
                                                </a>
                                                <a href='https://play.google.com/store/apps/details?id=gr.gunet.eclass' target=_blank>
                                                    <img style='width:150px;' src='template/modern/img/AppStore.svg' class='img-responsive center-block m-auto d-block' alt='Available on the Play Store'>
                                                </a>
                                            </div>
                                        @endif

                                        @if($eclass_banner_value == 1)
                                            <div>
                                                <div class='card border-0 border-radius-default'>
                                                    <div class='card-body banner-body border-0 bg-white border-radius-default py-lg-2 py-1'>
                                                        <a href="http://www.openeclass.org/" target="_blank">
                                                            <img style='width:150px;' src="{{ $themeimg }}/Open-Eclass-Banner.svg" alt="Open eClass Banner">
                                                        </a> 
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
        @endif



        
        <div class="{{ $container }} homepage-container @if(get_config('show_only_loginScreen')) onlyLoginContainer pt-5 @endif my-0">
            <div class='row m-auto'>

                @if(!(get_config('upgrade_begin') || get_config('dont_display_login_form')))
                    <div class='col-12 mb-5 order-first'>
                        <div class='row row-cols-1 row-cols-lg-2 g-lg-5'>
                            <div class="col-lg-6 col-12 @if($PositionFormLogin or get_config('show_only_loginScreen')) ms-auto me-auto @endif">
                                <div class='card cardLogin p-3'>
                                    <div class='card-header bg-transparent border-0'>
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
                            </div>
                            @if(!get_config('show_only_loginScreen'))
                                <div class='col-lg-6 col-12 d-none @if($PositionFormLogin) d-lg-none @else d-lg-block d-flex justify-content-end align-items-center @endif'>
                                    <img class='jumbotron-image-default' src='{{ $loginIMG }}'>
                                </div>
                            @endif
                        </div>
                    </div>
                @endif



                @if(!get_config('show_only_loginScreen'))
                    <div class='col-12 mb-5 order-{{ $announcements_priority }}'>
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
                @endif




                @if(!get_config('show_only_loginScreen'))
                    @if($popular_courses)
                        <div class='col-12 mb-5 order-{{ $popular_courses_priority }}'>
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
                                            <div class='row row-cols-1 row-cols-md-2 row-cols-lg-3 row-cols-xl-4 g-lg-5 g-4'>
                                                @foreach ($popular_courses as $pop_course)
                                                    <div class="col mb-lg-0 mb-4">
                                                        <div class='card border-card h-100'>
                                                            <a href='{{$urlAppend}}courses/{{$pop_course->code}}/index.php'>
                                                                @if($pop_course->course_image)
                                                                    <img class='card-img-top popular_course_img' src='{{$urlAppend}}courses/{{$pop_course->code}}/image/{{$pop_course->course_image}}' alt='Course Banner'/>
                                                                @else
                                                                    <img class='card-img-top popular_course_img' src='{{$urlAppend}}template/modern/img/ph1.jpg'/>
                                                                @endif
                                                            </a>
                                                            <div class='card-body'>
                                                                <div class="col-12 text-center mt-2">
                                                                    <a class='TextBold msmall-text' href='{{$urlAppend}}courses/{{$pop_course->code}}/index.php'>
                                                                        {{$pop_course->title}} ({{$pop_course->public_code}})
                                                                        
                                                                    </a>
                                                                    <p class='TextRegular msmall-text Neutral-800-cl'>{{$pop_course->prof_names}}</p>
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
                    @endif
                @endif




                @if(!get_config('show_only_loginScreen'))
                    @if($texts)
                        <div class='col-12 mb-5 order-{{ $texts_priority }}'>
                            <div class="row row-cols-1 @if(count($texts) > 1) row-cols-lg-2 @endif g-lg-5 g-3">
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
                    @endif
                @endif




                @if(!get_config('show_only_loginScreen'))
                    @if(!get_config('dont_display_testimonials') && count($testimonials) > 0)
                        <div class='col-12 mb-5 order-{{ $testimonials_priority }}'>
                            <div class='card bg-transparent border-0'>
                                <div class='card-header border-0 bg-transparent d-flex justify-content-between align-items-center px-0 py-0'>
                                    <h3>{{ trans('langSaidForUs') }}</h3>
                                </div>
                                <div class='card-body px-3'>
                                    
                                    <div class="d-flex justify-content-center">
                                        <div class="col-12 testimonials my-0">
                                            @foreach($testimonials as $t)
                                                <div class="d-flex align-items-start flex-column testimonial">
                                                    
                                                        <div class="testimonial-body mb-auto">
                                                            <p class="Neutral-800-cl">{!! $t->body !!}</p>
                                                        </div>
                                                        <div class="testimonial-person w-100">
                                                            <div class="form-label text-end mt-4">{!! $t->title !!}</div>
                                                        </div>
                                                    </div>
                                                
                                            @endforeach
                                        </div>
                                    </div>

                                </div>

                            </div>
                        </div>
                    @endif
                @endif




                @if(!get_config('show_only_loginScreen'))
                    <div class='col-12 mb-5 order-{{ $statistics_priority }}'>
                        <div class='card bg-transparent border-0'>
                            <div class='card-header border-0 bg-transparent d-flex justify-content-between align-items-center px-0 py-0'>
                                <div class='d-flex justify-content-start align-items-center'>
                                    <h3 class='pe-2'>{{ trans('langViewStatics') }}</h3>
                                </div>
                            </div>
                            <div class='card-body px-0 py-3'>
                                <div class='col-12'>
                                    <div class='row row-cols-1 row-cols-md-3 g-lg-5 g-3'>
                                        <div class='col mb-lg-0 mb-4'>
                                            <div class='card statistics-card border-default-card drop-shadow'>
                                                <div class='card-body Primary-200-bg d-flex justify-content-center align-items-center'>
                                                    <div>
                                                        <div class='d-flex justify-content-center'>
                                                            <img src='{{ $urlAppend }}template/modern/images/Icons_book-open.svg'>
                                                            <h1 class='mb-0 ms-2'>{{ get_config('total_courses') }}</h1>
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
                                                            <h1 class='mb-0 ms-2'>{{ get_config('visits_per_week')}}K+</h1>
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
                @endif

                


                @if(!get_config('show_only_loginScreen'))
                    @if(get_config('opencourses_enable'))
                        <div class='col-12 mb-5 order-{{ $open_courses_priority }}'>
                            <div class='row row-cols-1'>
                                <div class='col-lg-6 col-12'>
                                    <div class='row row-cols-1'>
                                        
                                        @if ($openCoursesExtraHTML)
                                            <h3 class='mb-4'>{{ trans('langOpenCourses') }}</h3>
                                            {!! $openCoursesExtraHTML !!}
                                        @endif
                                    </div>
                                </div>
                            </div>
                        </div>
                    @endif
                @endif


                


            </div>
        </div>
        

</div>
        


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
