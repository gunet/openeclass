@extends('layouts.default')

@section('content')

<div class="col-12 main-section">

        @if($warning)
            <input id='showWarningModal' type='hidden' value='1'>
            <div class="modal fade" id="WarningModal" aria-hidden="true" tabindex="-1">
                <div class="modal-dialog modal-dialog-centered">
                    <div class="modal-content shadow-sm border-0 p-0">
                        <div class="modal-header d-flex justify-content-between align-items-center">
                            <div class="modal-title">{{ trans('langError') }}</div>
                            <button aria-label="{{ trans('langClose') }}" type='button' class='close border-0 bg-transparent' data-bs-dismiss='modal'>
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

        @include('layouts.partials.show_alert')

        @if ($display_login_form != 1)
            <div class='row m-auto'>
                <div class="col-12 jumbotron jumbotron-login">
                    <div class='{{ $container }} padding-default'>
                        <div class='row row-cols-1 g-4'>
                            <div class='col'>
                                <div class='card bg-transparent card-transparent border-0'>
                                    <div class='card-header border-0 bg-transparent d-flex justify-content-between align-items-center px-0 py-0 gap-3 flex-wrap'>
                                        <div class='jumbotron-intro-text'>
                                            @if(get_config('homepage_title_'.$language_code))
                                                <h1 class='eclass-title' aria-label="{{ trans('langEclass') }}">{{ get_config('homepage_title_'.$language_code) }}</h1>
                                            @endif

                                            @if(get_config('homepage_intro_'.$language_code))
                                                <p class='eclassInfo mb-0' aria-label="{{ trans('langInfo') }}">{!! get_config('homepage_intro_'.$language_code) !!}</p>
                                            @endif
                                        </div>
                                    </div>
                                    <div class='card-body px-0'>
                                        @if(get_config('enable_mobileapi') || $eclass_banner_value == 1)
                                            <div class="d-flex justify-content-between align-items-center flex-wrap gap-4">
                                                @if(get_config('enable_mobileapi'))
                                                    <div class='d-flex gap-3 pe-3'>
                                                        <a href='https://play.google.com/store/apps/details?id=gr.gunet.eclass3' target='_blank' aria-label='Google Play'>
                                                            <img style='width:150px;' src='resources/img/GooglePlay.svg' class='img-responsive center-block m-auto d-block' alt='Get it on Google Play'>
                                                        </a>
                                                        <a href='https://itunes.apple.com/us/app/open-eclass-mobile/id1398319489' target='_blank' aria-label='App Store'>
                                                            <img style='width:150px;' src='resources/img/AppStore.svg' class='img-responsive center-block m-auto d-block' alt='Download on the App Store'>
                                                        </a>
                                                    </div>
                                                @endif
                                                @if($eclass_banner_value == 1)
                                                    <div>
                                                        <a class='banner-link' href="{!! get_config('banner_link') !!}" target="_blank" aria-label='Banner'>
                                                            <img style='width:134px;' src="{{ $logo_img }}" alt="This is the banner of platform">
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

        {{-- login form --}}
        <div class='row m-auto'>
            @if(!isset($_GET['redirect_home']))
                @if ($display_login_form != 0)
                    <!-- only one auth_method is enabled and this method is not eclass -->
                    @if(!$authCase)
                        <div class="col-12 order-first homepage-login-container">
                            <div class='{{ $container }} padding-default padding-default-form-login'>
                                <div class='row row-cols-1 row-cols-lg-2 g-4'>
                                    <div class="col @if($PositionFormLogin or $display_login_form == 1) ms-auto me-auto @endif">

                                        @if($auth_enabled_method == 1)
                                            @if(count($authLinks) > 0)
                                                <div class='card form-homepage-login border-card h-100 px-lg-4 py-lg-3 p-3'>
                                                    <div class='card-body d-flex justify-content-center align-items-center p-1 p-md-2'>
                                                        @php $i = 0; @endphp
                                                        <div class='w-100 h-100'>
                                                            <div class='col-12 container-pages d-flex align-items-center h-100'>

                                                                @foreach($authLinks as $auth => $key)

                                                                    @if($i > 0 && $primary_method >= 2 && count($authLinks) == 2)
                                                                        @break
                                                                    @endif

                                                                    <div class="col-12 page @if($i == 0) slide-page @elseif($i == 1) next-page-1 @else next-page-2 @endif h-100">
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
                                                                                                        <button type='button' class='close' data-bs-dismiss='modal' aria-label="{{ trans('langClose') }}">
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

                                                                            <div class='col-12 align-self-center' @if ($primary_method >= 3 && count($authLinks) == 2) style="height: 100px; display: flex; align-items:center; justify-content: center;" @endif>
                                                                                <div class='text-center w-100'>{!! $key['html'] !!}</div>
                                                                            </div>



                                                                            <div class='col-12 align-self-end'>
                                                                                @if(count($authLinks) > 1)
                                                                                    <div id="or" class='ms-auto me-auto mb-2'>{{ trans('langOr')}}</div>
                                                                                @endif
                                                                                @if(count($authLinks) == 2)
                                                                                    <div class="d-flex justify-content-center align-items-center gap-3 flex-wrap">
                                                                                        {!! $authLinks[1]['html'] !!}
                                                                                    </div>
                                                                                @endif

                                                                                @if(count($authLinks) >= 3)
                                                                                    <div class="d-flex justify-content-md-between justify-content-center align-items-center gap-3 flex-wrap">

                                                                                            @if($i==0)
                                                                                                <button class="btn submitAdminBtn firstNext next">
                                                                                                    @if(!empty($authLinks[$i+1]['title']))
                                                                                                        {!! $authLinks[$i+1]['title'] !!}
                                                                                                    @else
                                                                                                        {{ trans('langLogin') }}
                                                                                                    @endif
                                                                                                </button>
                                                                                                <button class="btn submitAdminBtn next-1 next">
                                                                                                    @if(!empty($authLinks[$i+2]['title']))
                                                                                                        {!! $authLinks[$i+2]['title'] !!}
                                                                                                    @else
                                                                                                        {{ trans('langLogin') }}
                                                                                                    @endif
                                                                                                </button>

                                                                                            @endif

                                                                                            @if($i==1)
                                                                                                <button class="btn submitAdminBtn prev-1 next">
                                                                                                    @if(!empty($authLinks[$i-1]['title']))
                                                                                                        {!! $authLinks[$i-1]['title'] !!}
                                                                                                    @else
                                                                                                        {{ trans('langLogin') }}
                                                                                                    @endif
                                                                                                </button>
                                                                                                <button class="btn submitAdminBtn next-2 next">
                                                                                                    @if(!empty($authLinks[$i+1]['title']))
                                                                                                        {!! $authLinks[$i+1]['title'] !!}
                                                                                                    @else
                                                                                                        {{ trans('langLogin') }}
                                                                                                    @endif
                                                                                                </button>

                                                                                            @endif

                                                                                            @if($i==2)
                                                                                                <button class="btn submitAdminBtn prev-2 next">
                                                                                                    @if(!empty($authLinks[$i-1]['title']))
                                                                                                        {!! $authLinks[$i-1]['title'] !!}
                                                                                                    @else
                                                                                                        {{ trans('langLogin') }}
                                                                                                    @endif
                                                                                                </button>
                                                                                                <button class="btn submitAdminBtn next-3 next">
                                                                                                    @if(!empty($authLinks[$i-2]['title']))
                                                                                                        {!! $authLinks[$i-2]['title'] !!}
                                                                                                    @else
                                                                                                        {{ trans('langLogin') }}
                                                                                                    @endif
                                                                                                </button>

                                                                                            @endif


                                                                                            @if(count($authLinks) > 3)
                                                                                                <div class='col-12'>
                                                                                                    <div id='oreven' class='ms-auto me-auto mb-2'>{{ trans('langOrYet') }}</div>
                                                                                                </div>

                                                                                                <div class='col-12 d-flex justify-content-center align-items-center'>
                                                                                                    <button type='button' class='btn submitAdminBtn border-0 text-decoration-underline bg-transparent' data-bs-toggle='modal' data-bs-target='#LoginAnotherOption-{{ $i }}'>
                                                                                                        @if(!empty($authLinks[count($authLinks)-1]['title']))
                                                                                                            {!! $authLinks[count($authLinks)-1]['title'] !!}
                                                                                                        @else
                                                                                                            {{ trans('langLogin') }}
                                                                                                        @endif
                                                                                                    </button>


                                                                                                    <div class='modal fade' id='LoginAnotherOption-{{ $i }}' data-bs-backdrop='static' data-bs-keyboard='false' tabindex='-1' aria-labelledby='LoginAnotherOptionLabel-{{ $i }}' aria-hidden='true'>
                                                                                                        <div class='modal-dialog'>
                                                                                                            <div class='modal-content'>
                                                                                                                <div class='modal-header'>
                                                                                                                    <div class='modal-title' id='LoginAnotherOptionLabel-{{ $i }}'>
                                                                                                                        @if(!empty($authLinks[count($authLinks)-1]['title']))
                                                                                                                            {!! $authLinks[count($authLinks)-1]['title'] !!}
                                                                                                                        @else
                                                                                                                            {{ trans('langLogin') }}
                                                                                                                        @endif
                                                                                                                    </div>
                                                                                                                    <button type='button' class='close' data-bs-dismiss='modal' aria-label="{{ trans('langClose') }}">
                                                                                                                    </button>
                                                                                                                </div>
                                                                                                                <div class='modal-body d-flex justify-content-center align-items-center'>
                                                                                                                    <div>
                                                                                                                        {!! $authLinks[count($authLinks)-1]['html'] !!}
                                                                                                                    </div>
                                                                                                                </div>
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
                                                            <span>{{ trans('langAllAuthMethodsAreDisabled') }}</span>
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>
                                        @endif

                                    </div>

                                    @if ($display_login_form != 1)
                                        <div class="col card-login-img d-none @if($PositionFormLogin) d-lg-none @else d-lg-block @endif"
                                        role="img" aria-label="{{ trans('langLoginImg') }}" style="background: url({{ $loginIMG }});"></div>
                                    @endif
                                </div>
                            </div>
                        </div>
                    @endif
                @endif
            @endif

            {{-- display announcements --}}
            @if ($display_login_form != 1)
                @if (!get_config('dont_display_announcements'))
                    <div class="col-12 order-{{ $announcements_priority }} homepage-annnouncements-container @if(get_config('dont_display_login_form')) drop-shadow @endif">
                        <div class='{{ $container }} padding-default'>
                            <div class='row row-cols-1 g-4'>
                                <div class='col'>
                                    <div class='card card-transparent bg-transparent border-0'>
                                        <div class='card-header border-0 bg-transparent d-flex justify-content-between align-items-center px-0 py-0 gap-2 flex-wrap'>
                                            <div class='d-flex justify-content-start align-items-center gap-2 flex-wrap'>
                                                <h2 class='text-heading-h3 mb-0'>{{ trans('langAnnouncements') }}</h2>
                                                <h3 class='mb-0' aria-label='Rss'><a href='{{ $urlServer }}rss.php' aria-label='Rss'><i class="fa-solid fa-rss"></i></a></h3>
                                        </div>
                                            @if(count($announcements) > 0)
                                                <div class='d-flex justify-content-end align-items-center'>
                                                    <h3 class='mb-0'><a class='TextRegular text-decoration-underline msmall-text mb-2' href="{{ $urlAppend }}main/system_announcements.php">{{ trans('langAllAnnouncements') }}...</a></h3>
                                                </div>
                                            @endif
                                        </div>
                                        <div class='card-body px-0 py-0'>
                                            @php $counterAn = 0; @endphp
                                            @if(count($announcements) > 0)

                                                    @foreach ($announcements as $announcement)
                                                        @if($counterAn < 6)
                                                            <div class='row mt-4'>
                                                                <div class='col-md-2'>
                                                                    <div class='card card-announcement-date text-center'>
                                                                        <p class='TextBold largest-text'>{!! date('j', strtotime($announcement->date)) !!}</p>
                                                                        <p class='mt-2'>
                                                                            @php
                                                                                $lg = $_GET['localize'] ?? $language;
                                                                                $string_date = datefmt_format(datefmt_create($lg, IntlDateFormatter::LONG, IntlDateFormatter::NONE, 'Europe/Athens', IntlDateFormatter::TRADITIONAL), strtotime($announcement->date));
                                                                                $finalDate = preg_replace('/^\d+\s+/', '', $string_date);
                                                                            @endphp
                                                                            {!! $finalDate !!}
                                                                        </p>
                                                                    </div>
                                                                </div>
                                                                <div class='col-md-10 mt-md-0 mt-2'>
                                                                    <h3 class='mb-0'><a class='TextBold' style='font-size: 16px;' href='{{ $urlAppend }}main/system_announcements.php?an_id={{ $announcement->id }}'>
                                                                        {!! $announcement->title !!}
                                                                    </a></h3>
                                                                    <div class='truncate-announcement'>{!! $announcement->body !!}</div>
                                                                </div>
                                                            </div>
                                                        @endif
                                                        @php $counterAn++; @endphp
                                                    @endforeach

                                            @else
                                                <ul class='list-group list-group-flush'>
                                                    <li class='list-group-item element'>
                                                        <div class='TextRegular msmall-text text-content'>{{ trans('langNoAnnouncementsExist') }}</div>
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
            @endif


            {{-- display popular courses --}}
            @if ($display_login_form != 1)
                @if(!get_config('dont_display_popular_courses'))
                    @if($popular_courses)
                        <div class='col-12 order-{{ $popular_courses_priority }} homepage-popoular-courses-container'>
                            <div class='{{ $container }} padding-default'>
                                <div class="row row-cols-1 g-4">
                                    <div class='col'>
                                        <div class='card card-transparent bg-transparent border-0'>
                                            <div class='card-header border-0 bg-transparent d-flex justify-content-between align-items-center px-0 py-0 mb-3'>
                                                <div class='d-flex justify-content-start align-items-center'>
                                                    <h2 class='text-heading-h3 mb-0'>
                                                        {{trans('langPopularCourse')}}
                                                    </h2>
                                                </div>
                                            </div>
                                            <div class='card-body px-0 py-0'>
                                                <div class='row row-cols-1 row-cols-md-2 row-cols-lg-3 row-cols-xl-4 g-lg-5 g-4'>
                                                    @foreach ($popular_courses as $pop_course)
                                                        <div class="col mb-lg-0 mb-4">
                                                            <div class='card border-card h-100 card-default'>
                                                                <a href='{{$urlAppend}}courses/{{$pop_course->code}}/index.php'>
                                                                    @if($pop_course->course_image)
                                                                        <img class='card-img-top popular_course_img' src='{{$urlAppend}}courses/{{$pop_course->code}}/image/{{$pop_course->course_image}}' alt="{{ $pop_course->title }}" />
                                                                    @else
                                                                        @if($pop_course->is_collaborative)
                                                                            <img class='card-img-top popular_course_img' src='{{$urlAppend}}template/modern/images/default-collaboration.jpg' alt="{{ $pop_course->title }}" />
                                                                        @else
                                                                            <img class='card-img-top popular_course_img' src='{{$urlAppend}}resources/img/ph1.jpg' alt="{{ $pop_course->title }}" />
                                                                        @endif
                                                                    @endif
                                                                </a>
                                                                <div class='card-body'>
                                                                    <div class="col-12 text-center mt-2 line-height-default">
                                                                        <h3 class='mb-0'>
                                                                            <div class='line-height-default'>
                                                                                <a class='TextBold msmall-text' href='{{$urlAppend}}courses/{{$pop_course->code}}/index.php'>
                                                                                    {{$pop_course->title}} ({{$pop_course->public_code}})
                                                                                </a>
                                                                            </div>
                                                                        </h3>
                                                                        <p class='TextRegular msmall-text Neutral-900-cl mt-2'>{{$pop_course->prof_names}}</p>
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
            @endif


            {{-- display texts --}}
            @if ($display_login_form != 1)
                @if(!get_config('dont_display_texts'))
                    @if($texts)
                        <div class='col-12 order-{{ $texts_priority }} homepage-texts-container'>
                            <div class='{{ $container }} padding-default'>
                                <div class="row row-cols-1 @if(count($texts) > 1) row-cols-lg-2 @endif g-4">
                                    @foreach($texts as $text)
                                        <div class='col'>
                                            <div class='card card-transparent bg-transparent border-0'>
                                                <div class='card-header border-0 bg-transparent d-flex justify-content-between align-items-center px-0 py-0'>
                                                    <div class='d-flex justify-content-start align-items-center'>
                                                        <h2 class='text-heading-h3 mb-0'>
                                                            {!! $text->title !!}
                                                        </h2>
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
            @endif

            {{-- display testimonials --}}
            @if ($display_login_form != 1)
                @if(!get_config('dont_display_testimonials') && count($testimonials) > 0)
                    <div class='col-12 order-{{ $testimonials_priority }} homepage-testimonials-container'>
                        <div class='{{ $container }} padding-default'>
                            <div class="row row-cols-1 g-4">
                                <div class='col'>
                                    <div class='card card-transparent bg-transparent border-0'>
                                        <div class='card-header border-0 bg-transparent d-flex justify-content-between align-items-center px-0 py-0'>
                                            <h2 class='text-heading-h3 mb-0'>
                                                {!! get_config('homepage_testimonial_title_'.$language_code) !!}
                                            </h2>
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

            {{-- display statistics --}}
            @if ($display_login_form != 1)
                @if(!get_config('dont_display_statistics'))
                    <div class='col-12 order-{{ $statistics_priority }} homepage-statistics-container'>
                        <div class='{{ $container }} padding-default'>
                            <div class="row row-cols-1 g-4">
                                <div class='col'>
                                    <div class='card card-transparent bg-transparent border-0'>
                                        <div class='card-header border-0 bg-transparent d-flex justify-content-between align-items-center px-0 py-0'>
                                            <div class='d-flex justify-content-start align-items-center'>
                                                <h2 class='text-heading-h3 mb-0'>{{ trans('langViewStatics') }}</h2>
                                            </div>
                                        </div>
                                        <div class='card-body px-0 py-3'>
                                            <div class='col-12'>
                                                <div class='row row-cols-1 row-cols-md-3 g-lg-5 g-3'>
                                                    <div class='col mb-lg-0 mb-4'>
                                                        <div class='card statistics-card drop-shadow card-default'>
                                                            <div class='card-body d-flex justify-content-center align-items-center'>
                                                                <div>
                                                                    <div class='d-flex justify-content-center'>
                                                                        <i class="fa-solid fa-book-open fa-xl mt-4 pt-1" role="presentation"></i>
                                                                        <div class='TextBold largest-text mb-0 ms-2'>
                                                                            @if((isset($collaboration_platform) and !$collaboration_platform) or is_null($collaboration_platform))
                                                                                {{ number_format(intval(get_config('total_courses')), 0, '', $digit_separator) }}
                                                                            @else
                                                                                {{ number_format(intval($total_collaboration_courses), 0 ,'', $digit_separator) }}
                                                                            @endif
                                                                        </div>
                                                                    </div>
                                                                    <p class='form-label text-center'>{{ trans('langCourses') }}</p>
                                                                </div>
                                                            </div>
                                                        </div>
                                                    </div>
                                                    <div class='col mb-lg-0 mb-4'>
                                                        <div class='card statistics-card drop-shadow card-default'>
                                                            <div class='card-body d-flex justify-content-center align-items-center'>
                                                                <div>
                                                                    <div class='d-flex justify-content-center'>
                                                                        <i class="fa-solid fa-globe fa-xl mt-4 pt-1" role="presentation"></i>
                                                                        <div class='TextBold largest-text mb-0 ms-2'>{{ number_format(intval(get_config('visits_per_week')), 0, '', $digit_separator) }}</div>
                                                                    </div>
                                                                    <p class='form-label text-center'>{{trans('langUserLogins')}} {{trans('langPerMonth')}}</p>
                                                                </div>
                                                            </div>
                                                        </div>
                                                    </div>
                                                    <div class='col mb-lg-0 mb-4'>
                                                        <div class='card statistics-card drop-shadow card-default'>
                                                            <div class='card-body d-flex justify-content-center align-items-center'>
                                                                <div>
                                                                    <div class='d-flex justify-content-center'>
                                                                        <i class="fa-solid fa-user fa-xl mt-4 pt-1" role="presentation"></i>
                                                                        <div class='TextBold largest-text mb-0 ms-2'>{{ number_format(intval(get_config('users_registered')), 0, '', $digit_separator) }}</div>
                                                                    </div>
                                                                    <p class='form-label text-center'>{{ trans('langRegisteredUsers') }}</p>
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
            @endif


            {{-- display open courses --}}
            @if ($display_login_form != 1)
                @if(!get_config('dont_display_open_courses'))
                    @if(get_config('opencourses_enable') && ((isset($collaboration_platform) and !$collaboration_platform) or is_null($collaboration_platform)))
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
