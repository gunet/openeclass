@extends('layouts.default')

@section('content')

<div class="pb-lg-3 pt-lg-3 pb-0 pt-0">

    <div class="container-fluid main-container">

        <div class="row rowMedium">

            <div class="col-xl-12 col-lg-12 col-md-12 col-sm-12 col-12 justify-content-center col_maincontent_active_Homepage">

                <div class="row ps-lg-5 pt-lg-5 pe-lg-5">

                    <div class='col-lg-12 mt-2'>{!! $warning !!}</div>

                    <div class='col-sm-12 mt-2 ps-4 pe-4'>
                        <div class='row'>

                            @if(!get_config('dont_display_login_form'))
                                <div class='col-lg-7 col-md-6 d-none d-sm-none d-md-block jumbotron jumbotron-login'></div>

                                <div class='col-lg-5 col-md-6 col-12 p-3 login-form-Homepage'>
                                    <div class='col-12 mt-3 mb-3'>
                                        <div class='control-label-notes fs-5 text-center'><img src="template/modern/img/user2.png" class='user-icon'> {{ trans('langUserLogin') }}</div>
                                    </div>
                                    <form action="{{ $urlAppend }}" method="post">
                                        <div class="login-form-spacing">
                                            <h4 class='text-secondary fs-5'>{{ trans('langUsername') }}</h4>
                                            <input class="login-input bg-body border border-secondary w-50" placeholder="&#xf007;" type="text" id="uname" name="uname" >
                                            <h4 class="text-secondary mt-3 fs-5">{{ trans('langPassword') }} (password)</h4>
                                            <input class="login-input bg-body border border-secondary w-50" placeholder="&#xf023;" type="password" id="pass" name="pass">
                                            <input class="btn btn-primary text-white w-50 login-form-submit mt-md-4 mb-md-0 mt-4 mb-4" type="submit" name="submit" value="{{ trans('langLogin') }}">
                                        </div>
                                    
                                    </form>
                                    <div class="login-form-spacing2 mb-3">
                                        <a class="text-primary btnlostpass" href="{{$urlAppend}}modules/auth/lostpass.php">{{ trans('lang_forgot_pass') }}</a>
                                    </div>
                                </div>
                            @else
                                <div class='col-md-12 jumbotron jumbotron-login border-15px'></div>
                            @endif
                        </div>
                    </div>
                    

                    
                    <div class='col-12 mt-4'>    
                        <div class="panel panel-default w-100">
                            <div class="panel-heading text-center p-3">
                                <span class='fs-5 text-start control-label-notes'>
                                    {!! get_config('homepage_title') !!}
                                </span>
                            </div>
                            <div class='panel-body bg-body p-5'>
                                {!! get_config('homepage_intro') !!}
                            </div>
                        </div>  
                    </div>

                    
                    @if (get_config('enable_mobileapi'))
                    <div class='col-12 mt-4'>
                        <div class='row'>
                            <div class='col-md-6 col-12'>
                                <div class='panel panel-default'>
                                    <div class='panel-body'>
                                        <a href="http://www.openeclass.org/" target="_blank">
                                            <img class="img-responsive center-block m-auto d-block" src="/template/modern/img/open_eclass_banner.png" alt="Open eClass Banner">
                                        </a>
                                    </div>
                                </div>
                            </div>
                            <div class='col-md-6 col-12 mt-md-0 mt-4'>
                                <div class='panel panel-default'>
                                    <div class='panel-body'>
                                        <div class='row'>
                                            <div class='col-md-6 col-12'>
                                                <a href='https://itunes.apple.com/us/app/open-eclass-mobile/id1398319489' target=_blank>
                                                    <img src='template/modern/images/appstore.png' class='img-responsive center-block m-auto d-block' alt='Available on the App Store'>
                                                </a>
                                            </div>
                                            <div class='col-md-6 col-12 mt-md-0 mt-3'>
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
                    @else
                    <div class='col-12 mt-4'>
                        <div class='panel panel-default'>
                            <div class='panel-body'>
                                <a href="http://www.openeclass.org/" target="_blank">
                                    <img class="img-responsive center-block m-auto d-block" src="/template/modern/img/open_eclass_banner.png" alt="Open eClass Banner">
                                </a>
                            </div>
                        </div>
                    </div>
                    @endif
                        
                    

                
                    <div class="col-lg-12 d-flex justify-content-end mt-3 mb-4">
                        <button class="btnMoreHomePage" data-bs-toggle="collapse" data-bs-target="#collapse_main_section" aria-expanded="false" aria-controls="collapse_main_section">
                            <span class='fa fa-arrow-down'></span>
                        </button>
                    </div>
                </div>
            </div>
        </div>
    </div>

</div>


<!-- collapse menu -->

<div class="collapse" id="collapse_main_section">
    <div class="container-fluid main-section">
        <div class="row rowMedium">
            <div class="col-lg-12 bg-white border-15px sidebar Announcements-Homepage">
                @if ($announcements)
                    <div class="news">
                        <h2 class="block-title">{{ trans('langAnnouncements') }}
                            <a href='{{ $urlServer }}rss.php' style='padding-left:5px;'>
                                <span class='fa fa-rss-square'></span>
                            </a>
                        </h2>
                        <div class="row news-list">
                            @php $counterAn = 0; @endphp
                            @foreach ($announcements as $announcement)
                                @if($counterAn < 6)
                                <div class="col-sm-12 news-list-item">
                                    <div class="date">
                                        {{ claro_format_locale_date($dateFormatLong, strtotime($announcement->date)) }}
                                    </div>
                                    <div class="title"><a class="announcement-title-a" href='modules/announcements/main_ann.php?aid={{ $announcement->id }}'>{{$announcement->title}}</a></div>
                                </div>
                                @endif
                            @php $counterAn++; @endphp
                            @endforeach
                        </div>
                        <div class="more-link"><a class="all_announcements mt-3" href="{{ $urlServer }}main/system_announcements.php">ΟΛΕΣ ΟΙ ΑΝΑΚΟΙΝΩΣΕΙΣ <span class='fa fa-arrow-right'></span></a></div>
                    </div>
                @endif
            </div>
        </div>
    </div>


    <div class="container-fluid statistics mt-lg-3 mt-0">
        <div class="statistics-wrapper">
            <h2 class="text-center">
                Στατιστικά Επισκεψιμότητας
            </h2>
            <div class="row row_pad_courses">
                <div class="col-lg-4 text-center">
                        <i class="fas fa-book"></i>
                        <div class="num">10</div>
                        <div class="num-text">{{trans('langsCourses')}}</div>
                </div>
                <div class="col-lg-4 text-center">
                        <i class="fas fa-mouse-pointer"></i>
                        <div class="num">10<span>K+</span></div>
                        <div class="num-text">επισκέψεις/εβδομάδα</div>
                </div>
                <div class="col-lg-4 text-center">
                        <i class="fas fa-user"></i>
                        <div class="num">10
                            <!-- <span>K+</span> -->
                        </div>
                        <div class="num-text">ενεργοί χρήστες</div>
                </div>
            </div>
        </div>
    </div>

     <div class="d-flex justify-content-center mt-lg-3 mb-lg-3 mt-0 mb-0">
        <div class="container-fluid testimonials bg-light">
            <div class="testimonial">
                <div class="testimonial-body">{!! get_config('homepage_intro') !!}</div>
                <div class="testimonial-person">Λυδία Καλομοίρη</div>
            </div>
            <div class="testimonial">
                <div class="testimonial-body">{!! get_config('homepage_intro') !!}</div>
                <div class="testimonial-person">Λυδία Καλομοίρη</div>
            </div>
            <div class="testimonial">
                <div class="testimonial-body">{!! get_config('homepage_intro') !!}</div>
                <div class="testimonial-person">Λυδία Καλομοίρη</div>
            </div>
        </div>
    </div>

</div>

<script>
    $('.testimonials').slick({
		autoplay:true,
		autoplaySpeed:1500,
		centerMode: true,
		centerPadding: '25vw',
		slidesToShow: 1,
		responsive: [{
			breakpoint: 3840,
			settings: { centerPadding: '15vw', }
		}]
	});
</script>

@endsection
