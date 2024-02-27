@extends('layouts.default')

@section('content')

<div class="col-12 main-section">

        @if($warning)
            <input id='showWarningModal' type='hidden' value='1'>
            <div class="modal fade" id="WarningModal" aria-hidden="true" tabindex="-1">
                <div class="modal-dialog modal-dialog-centered">
                    <div class="modal-content shadow-sm border-0 p-0">
                        <div class="modal-header d-flex justify-content-between align-items-center">
                            <h4 class="modal-title">{{ trans('langError') }}</h4>
                            <button aria-label='Close' type='button' class='close border-0 bg-transparent' data-bs-dismiss='modal'>
                                <i class='fa-solid fa-xmark fa-lg Accent-200-cl'></i>
                            </button>
                        </div>
                        <div class="modal-body">
                            {!! $warning !!}
                        </div>
                    </div>
                </div>
            </div>
        @else
            <input id='showWarningModal' type='hidden' value='0'>
        @endif


        @if(!get_config('show_only_loginScreen'))
            <div class='row m-auto'>
                <div class="col-12 jumbotron jumbotron-login">
                    <div class='{{ $container }} padding-default'>
                        <div class='row row-cols-1 g-4'>
                            <div class='col'>
                                <div class='card bg-transparent card-transparent border-0'>
                                    <div class='card-header border-0 bg-transparent d-flex justify-content-between align-items-center px-0 py-0 gap-3 flex-wrap'>
                                        <div class='jumbotron-intro-text'>
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
                                    <div class='card-body px-0'>
                                        @if(get_config('enable_mobileapi') || $eclass_banner_value == 1)
                                            <div class="d-flex justify-content-between align-items-center">
                                                @if(get_config('enable_mobileapi'))
                                                    <div class='d-flex gap-3 pe-3'>
                                                        <a href='https://itunes.apple.com/us/app/open-eclass-mobile/id1398319489' target='_blank' aria-label='App Store'>
                                                            <img style='width:150px;' src='template/modern/img/GooglePlay.svg' class='img-responsive center-block m-auto d-block' alt='Available on the App Store'>
                                                        </a>
                                                        <a href='https://play.google.com/store/apps/details?id=gr.gunet.eclass' target='_blank' aria-label='Play Store'>
                                                            <img style='width:150px;' src='template/modern/img/AppStore.svg' class='img-responsive center-block m-auto d-block' alt='Available on the Play Store'>
                                                        </a>
                                                    </div>
                                                @endif
                                                @if($eclass_banner_value == 1)
                                                    <div>
                                                        <a href="http://www.openeclass.org/" target="_blank" aria-label='Banner'>
                                                            <img style='width:150px;' src="{{ $logo_img }}" alt="This is the banner of platform">
                                                        </a>
                                                    </div>
                                                @endif
                                            </div>
                                        @endif
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        @endif



        <div class='row m-auto'>
            @if(!(get_config('upgrade_begin') || get_config('dont_display_login_form')))
                <div class="col-12 order-first homepage-login-container @if(get_config('show_only_loginScreen')) onlyLoginContainer @endif">
                    <div class='{{ $container }} padding-default'>
                        <div class='row row-cols-1 row-cols-lg-2 g-4'>
                            <div class="col @if($PositionFormLogin or get_config('show_only_loginScreen')) ms-auto me-auto @endif">

                                @if($auth_enabled_method == 1)
                                    @if(count($authLinks) == 1)
                                        <div class='card cardLogin h-100 px-lg-4 py-lg-3 p-3'>
                                            @foreach($authLinks as $auth => $key)
                                            <div class="card-header border-0 d-flex justify-content-between align-items-center gap-2 flex-wrap">
                                                <h2 class='mb-0'>
                                                    @if(!empty($key['title']))
                                                        {!! $key['title'] !!}
                                                    @else
                                                        {{ trans('langLogin') }}
                                                    @endif
                                                </h2>
                                                @if(!empty($key['authInstructions']))
                                                    <a href='#' class='text-decoration-underline vsmall-text mb-0' data-bs-toggle='modal' data-bs-target="#authInstruction{{ $key['authId'] }}">
                                                        {{ trans('langInstructions') }}
                                                    </a>
                                                    <div class='modal fade' id="authInstruction{{ $key['authId']}}" tabindex='-1' role='dialog' aria-labelledby='authInstructionLabel' aria-hidden='true'>
                                                        <div class='modal-dialog'>
                                                            <div class='modal-content'>
                                                                <div class='modal-header'>
                                                                    <div class='modal-title' id='authInstructionLabel'>{{ trans('langInstructionsAuth') }}</div>
                                                                    <button type='button' class='close' data-bs-dismiss='modal' aria-label='Close'>
                                                                        <span class='fa-solid fa-xmark fa-lg Accent-200-cl' aria-hidden='true'></span>
                                                                    </button>
                                                                </div>
                                                                <div class='modal-body'>
                                                                    <div class='col-12'>
                                                                        <div class='alert alert-info'>
                                                                            <i class='fa-solid fa-circle-info fa-lg'></i>
                                                                            <span>{!! $key['authInstructions'] !!}</span>
                                                                        </div>
                                                                    </div>
                                                                </div>
                                                            </div>
                                                        </div>
                                                    </div>
                                                @endif
                                            </div>
                                            <div class='card-body d-flex justify-content-center align-items-center'>
                                                @if(strpos($key['class'], 'login-option-sso') !== false)
                                                    <div class='w-100 d-flex justify-content-center align-items-center'>
                                                @else
                                                    <div class='w-100'>
                                                @endif
                                                        {!! $key['html'] !!}
                                                    </div>
                                            </div>
                                            @endforeach
                                        </div>
                                    @elseif(count($authLinks) > 1)
                                        <div class='card form-homepage-login border-card h-100 px-lg-4 py-lg-3 p-3'>
                                            <div class='card-body d-flex justify-content-center align-items-center'>
                                                @php $i = 0; @endphp
                                                <div class='w-100 h-100'>
                                                    <div class='col-12 container-pages d-flex align-items-center h-100'>
                                                        @foreach($authLinks as $auth => $key)
                                                            <div class="col-12 page @if($i == 0) slide-page @else current-page @endif h-100">
                                                                <div class="row h-100">
                                                                    <div class='col-12 align-self-start'>
                                                                        <div class='d-flex justify-content-between align-items-center flex-wrap gap-2'>
                                                                            <h2 class='mb-3'>
                                                                                @if(!empty($key['title']))
                                                                                    {!! $key['title'] !!}
                                                                                @else
                                                                                    {{ trans('langLogin') }}
                                                                                @endif
                                                                            </h2>
                                                                            @if(!empty($key['authInstructions']))
                                                                                <a href='#' class='text-decoration-underline vsmall-text mb-3' data-bs-toggle='modal' data-bs-target="#authInstruction{{ $key['authId'] }}">
                                                                                    {{ trans('langInstructions') }}
                                                                                </a>
                                                                                <div class='modal fade' id="authInstruction{{ $key['authId']}}" tabindex='-1' role='dialog' aria-labelledby='authInstructionLabel' aria-hidden='true'>
                                                                                    <div class='modal-dialog'>
                                                                                        <div class='modal-content'>
                                                                                            <div class='modal-header'>
                                                                                                <div class='modal-title' id='authInstructionLabel'>{{ trans('langInstructionsAuth') }}</div>
                                                                                                <button type='button' class='close' data-bs-dismiss='modal' aria-label='Close'>
                                                                                                    <span class='fa-solid fa-xmark fa-lg Accent-200-cl' aria-hidden='true'></span>
                                                                                                </button>
                                                                                            </div>
                                                                                            <div class='modal-body'>
                                                                                                <div class='col-12'>
                                                                                                    <div class='alert alert-info'>
                                                                                                        <i class='fa-solid fa-circle-info fa-lg'></i>
                                                                                                        <span>{!! $key['authInstructions'] !!}</span>
                                                                                                    </div>
                                                                                                </div>
                                                                                            </div>
                                                                                        </div>
                                                                                    </div>
                                                                                </div>
                                                                            @endif
                                                                        </div>
                                                                    </div>
                                                               
                                                                    <div class='col-12 align-self-center'>
                                                                        <div class='text-center'>{!! $key['html'] !!}</div>
                                                                    </div>
                                                                
                                                             
                                                                    <div class='col-12 align-self-end pt-4'>
                                                                        @if($i == 0) 
                                                                            <div class="d-flex justify-content-md-end justify-content-center align-items-center">
                                                                                <button class="btn submitAdminBtn firstNext next">
                                                                                    {{ trans('langNextAuthMethod') }}
                                                                                    <i class='fa-solid fa-chevron-right settings-icons'></i>
                                                                                </button>
                                                                            </div>
                                                                        @else
                                                                            
                                                                            <div class="d-flex justify-content-md-between justify-content-center align-items-center gap-3 flex-wrap">
                                                                                @if($i == 1 or $i == (count($authLinks)-1))
                                                                                    <button class="btn submitAdminBtn prev-{{ $i }} prev">
                                                                                        <i class='fa-solid fa-chevron-left settings-icons'></i>
                                                                                        {{ trans('langPrevStep') }}
                                                                                    </button>
                                                                                @endif
                                                                                @if($i+1 <= (count($authLinks)-1))
                                                                                    <button class="btn submitAdminBtn next-{{ $i }} next">
                                                                                        {{ trans('langNextAuthMethod') }}
                                                                                        <i class='fa-solid fa-chevron-right settings-icons'></i>
                                                                                    </button>
                                                                                @endif
                                                                            </div>
                                                                        @endif
                                                                    </div>
                                                                 </div>
                                                            </div>
                                                            @php $i++; @endphp
                                                        @endforeach
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    @endif

                                @else
                                    <div class='card cardLogin h-100 p-3'>
                                        <div class='card-body py-1'>
                                            <h2>{{ trans('langUserLogin') }}</h2>
                                            <div class='col-12 mt-3'>
                                                <div class='alert alert-warning'>
                                                    <i class='fa-solid fa-triangle-exclamation fa-lg'></i>
                                                    <span>{{trans('langAllAuthMethodsAreDisabled')}}</span>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                @endif

                            </div>
                            
                            @if(!get_config('show_only_loginScreen'))
                                <div class='col d-none @if($PositionFormLogin) d-lg-none @else d-lg-block @endif'>
                                    <div class='card h-100 border-0 p-0'>
                                        <div class='card-body d-flex justify-content-center align-items-center p-0'>
                                            <img class='jumbotron-image-default @if($auth_enabled_method == 1 && count($authLinks) > 1) jumbotron-image-auth-default @endif' src='{{ $loginIMG }}' alt="{{ trans('langLogin') }}" />
                                        </div>
                                    </div>
                                </div>
                            @endif
                        </div>
                    </div>
                </div>
            @endif


           
            @if(!get_config('show_only_loginScreen'))
                <div class="col-12 order-{{ $announcements_priority }} homepage-annnouncements-container @if(get_config('dont_display_login_form')) drop-shadow @endif">
                    <div class='{{ $container }} padding-default'>
                        <div class='row row-cols-1 g-4'>
                            <div class='col'>
                                <div class='card card-transparent bg-transparent border-0'>
                                    <div class='card-header border-0 bg-transparent d-flex justify-content-between align-items-center px-0 py-0 gap-2 flex-wrap'>
                                        <div class='d-flex justify-content-start align-items-center gap-2 flex-wrap'>
                                            <h3>{{ trans('langAnnouncements') }}</h3>
                                            <a href='{{ $urlServer }}rss.php' aria-label='Rss'><i class="fa-solid fa-rss"></i></a>
                                        </div>
                                        <div class='d-flex justify-content-end align-items-center'>
                                            <a class='TextRegular text-decoration-underline msmall-text mb-2' href="{{ $urlServer }}main/system_announcements.php">{{ trans('langAllAnnouncements') }}...</a>
                                        </div>
                                    </div>
                                    <div class='card-body px-0 py-0'>
                                        @php $counterAn = 0; @endphp
                                        @if(count($announcements) > 0)
                                            <ul class='list-group list-group-flush'>
                                                @foreach ($announcements as $announcement)
                                                    @if($counterAn < 3)
                                                        <li class='list-group-item element'>
                                                            <a class='TextBold' href='modules/announcements/main_ann.php?aid={{ $announcement->id }}'>
                                                                {{$announcement->title}}
                                                            </a>
                                                            <div class='TextRegular msmall-text Neutral-900-cl mt-1'>{{ format_locale_date(strtotime($announcement->date)) }}</div>
                                                        </li>
                                                    @endif
                                                    @php $counterAn++; @endphp
                                                @endforeach
                                            </ul>
                                        @else
                                            <ul class='list-group list-group-flush'>
                                                <li class='list-group-item element'>
                                                    <div class='TextRegular msmall-text'>{{ trans('langNoInfoAvailable') }}</div>
                                                </li>
                                            </ul>
                                        @endif
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            @endif


            @if(!get_config('show_only_loginScreen'))
                @if($popular_courses)
                    <div class='col-12 order-{{ $popular_courses_priority }} homepage-popoular-courses-container'>
                        <div class='{{ $container }} padding-default'>
                            <div class="row row-cols-1 g-4">
                                <div class='col'>
                                    <div class='card card-transparent bg-transparent border-0'>
                                        <div class='card-header border-0 bg-transparent d-flex justify-content-between align-items-center px-0 py-0 mb-3'>
                                            <div class='d-flex justify-content-start align-items-center'>
                                                <h3>
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
                                                                    <img class='card-img-top popular_course_img' src='{{$urlAppend}}courses/{{$pop_course->code}}/image/{{$pop_course->course_image}}' alt='This is the images of popular course'/>
                                                                @else
                                                                    <img class='card-img-top popular_course_img' src='{{$urlAppend}}template/modern/img/ph1.jpg' alt='This is the images of popular course'/>
                                                                @endif
                                                            </a>
                                                            <div class='card-body'>
                                                                <div class="col-12 text-center mt-2 line-height-default">
                                                                    <a class='TextBold msmall-text' href='{{$urlAppend}}courses/{{$pop_course->code}}/index.php'>
                                                                        {{$pop_course->title}} ({{$pop_course->public_code}})

                                                                    </a>
                                                                    <p class='TextRegular msmall-text Neutral-900-cl mt-1'>{{$pop_course->prof_names}}</p>
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
                @endif
            @endif


            @if(!get_config('show_only_loginScreen'))
                @if($texts)
                    <div class='col-12 order-{{ $texts_priority }} homepage-texts-container'>
                        <div class='{{ $container }} padding-default'>
                            <div class="row row-cols-1 @if(count($texts) > 1) row-cols-lg-2 @endif g-4">
                                @foreach($texts as $text)
                                    <div class='col'>
                                        <div class='card card-transparent bg-transparent border-0'>
                                            <div class='card-header border-0 bg-transparent d-flex justify-content-between align-items-center px-0 py-0'>
                                                <div class='d-flex justify-content-start align-items-center'>
                                                    <h3>
                                                        {!! $text->title !!}
                                                    </h3>
                                                </div>
                                            </div>
                                            <div class='card-body px-0 py-0'>
                                                <div class='TextRegular msmall-text mt-3'>{!! $text->body !!}</div>
                                            </div>
                                        </div>
                                    </div>
                                @endforeach
                            </div>
                        </div>
                    </div>
                @endif
            @endif

            @if(!get_config('show_only_loginScreen'))
                @if(!get_config('dont_display_testimonials') && count($testimonials) > 0)
                    <div class='col-12 order-{{ $testimonials_priority }} homepage-testimonials-container'>
                        <div class='{{ $container }} padding-default'>
                            <div class="row row-cols-1 g-4">
                                <div class='col'>
                                    <div class='card card-transparent bg-transparent border-0'>
                                        <div class='card-header border-0 bg-transparent d-flex justify-content-between align-items-center px-0 py-0'>
                                            <h3>{{ trans('langSaidForUs') }}</h3>
                                        </div>
                                        <div class='card-body px-3'>
                                            <div class="d-flex justify-content-center">
                                                <div class="col-12 testimonials my-0">
                                                    @foreach($testimonials as $t)
                                                        <div class="d-flex align-items-start flex-column testimonial">
                                                            <div class="testimonial-body mb-auto">
                                                                <p>{!! $t->body !!}</p>
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
                            </div>
                        </div>
                    </div>
                @endif
            @endif

            @if(!get_config('show_only_loginScreen'))
                <div class='col-12 order-{{ $statistics_priority }} homepage-statistics-container'>
                    <div class='{{ $container }} padding-default'>
                        <div class="row row-cols-1 g-4">
                            <div class='col'>
                                <div class='card card-transparent bg-transparent border-0'>
                                    <div class='card-header border-0 bg-transparent d-flex justify-content-between align-items-center px-0 py-0'>
                                        <div class='d-flex justify-content-start align-items-center'>
                                            <h3>{{ trans('langViewStatics') }}</h3>
                                        </div>
                                    </div>
                                    <div class='card-body px-0 py-3'>
                                        <div class='col-12'>
                                            <div class='row row-cols-1 row-cols-md-3 g-lg-5 g-3'>
                                                <div class='col mb-lg-0 mb-4'>
                                                    <div class='card statistics-card drop-shadow'>
                                                        <div class='card-body d-flex justify-content-center align-items-center'>
                                                            <div>
                                                                <div class='d-flex justify-content-center'>
                                                                    <i class="fa-solid fa-book-open fa-xl mt-4 pt-1" role="presentation"></i>
                                                                    <div class='TextBold largest-text mb-0 ms-2'>{{ get_config('total_courses') }}</div>
                                                                </div>
                                                                <p class='form-label text-center'>{{ trans('langCourses') }}</p>
                                                            </div>
                                                        </div>
                                                    </div>
                                                </div>
                                                <div class='col mb-lg-0 mb-4'>
                                                    <div class='card statistics-card drop-shadow'>
                                                        <div class='card-body d-flex justify-content-center align-items-center'>
                                                            <div>
                                                                <div class='d-flex justify-content-center'>
                                                                    <i class="fa-solid fa-globe fa-xl mt-4 pt-1" role="presentation"></i>
                                                                    <div class='TextBold largest-text mb-0 ms-2'>{{ get_config('visits_per_week')}}K+</div>
                                                                </div>
                                                                <p class='form-label text-center'>{{trans('langUserLogins')}}/</br>{{trans('langWeek')}}</p>
                                                            </div>
                                                        </div>
                                                    </div>
                                                </div>
                                                <div class='col mb-lg-0 mb-4'>
                                                    <div class='card statistics-card drop-shadow'>
                                                        <div class='card-body d-flex justify-content-center align-items-center'>
                                                            <div>
                                                                <div class='d-flex justify-content-center'>
                                                                    <i class="fa-solid fa-user fa-xl mt-4 pt-1" role="presentation"></i>
                                                                    <div class='TextBold largest-text mb-0 ms-2'>{{ getOnlineUsers() }}</div>
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
                        </div>
                    </div>
                </div>
            @endif



            @if(!get_config('show_only_loginScreen'))
                @if(get_config('opencourses_enable'))
                    <div class='col-12 order-{{ $open_courses_priority }} homepage-opencourses-container'>
                        <div class='{{ $container }} padding-default'>
                            <div class='row row-cols-1 g-4'>
                                <div class='col'>
                                    @if ($openCoursesExtraHTML)
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





<script>

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
