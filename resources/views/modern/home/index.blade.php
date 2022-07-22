@extends('layouts.default')

@section('content')

<!-- Laptop-Desktop -->

<div class='d-none d-sm-none d-md-none d-lg-block'>

    <div class="pb-3 pt-3">

        <div class="container-fluid main-container">

            <div class="row">

                <div class="col-xl-12 col-lg-12 col-md-12 col-sm-12 col-12 justify-content-center col_maincontent_active_Homepage">

                    <div class="row ps-lg-5 pt-lg-5 pe-lg-5">

                        <div class="col-lg-12 p-5 mt-3 w-100 jumbotron jumbotron-login">

                            <nav class="navbar navbar-expand-lg">
                                <span class="control-label-notes fs-5 ps-3" style='margin-top:-10px;'><span class='fa fa-home'></span> {{trans('langEclass')}}</span>
                            </nav>

                            <div class='col-lg-12 mt-3'>{!! $warning !!}</div>

                            <div class='col-xl-7 col-lg-9'>
                                <div class="panel panel-default w-auto d-flex justify-content-center">
                                    <div class='panel-heading w-25 text-center borderHeadingPanel' style='border-top-right-radius:0px;'>
                                        <img src="template/modern/img/user2.png" class='user-icon m-auto d-block mt-5'>
                                        <strong class='fs-6 control-label-notes pt-2'>{{ trans('langUserLogin') }}</strong>
                                    </div>
                                    <div class="panel-body w-75 bg-light ps-5 pe-5 pb-4 borderBodyPanel">
                                        <form action="{{ $urlAppend }}" method="post">
                                            <div class="login-form-spacing">
                                                <h4 class='control-label-notes'>{{ trans('langUsername') }}</h4>
                                                <input class="login-input w-75" type="text" id="uname" name="uname" >
                                                <h4 class="control-label-notes mt-3">{{ trans('langPassword') }}</h4>
                                                <input class="login-input w-75" type="password" id="pass" name="pass">
                                                
                                            </div>
                                            <div class="login-form-spacing">
                                                <input class="btn btn-default w-75 login-form-submit mt-3" type="submit" name="submit" value="{{ trans('langLogin') }}">
                                            </div>
                                        </form>
                                        <div class="login-form-spacing2">
                                            <a class="login-forgot" href="{{$urlAppend}}modules/auth/lostpass.php">{{ trans('lang_forgot_pass') }}</a>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                        

                        <div class="col-lg-12 mt-5">   
                            <div class='row'>     
                                <div class='col-lg-8'>        
                                    <div class="panel panel-default w-100" style='margin-left:-10px;'>
                                        <div class="panel-heading text-center">
                                            <div class='row'>
                                                <div class='col-2'>
                                                    <a href="http://www.openeclass.org/" target="_blank">
                                                        <img class="img-responsive center-block w-auto" src="/template/modern/img/open_eclass_banner.png" alt="Open eClass Banner">
                                                    </a>
                                                </div>
                                                <div class='col-9'>
                                                    <span class='control-label-notes'>{{trans('langWelcomeTo')}} 
                                                        <strong class='text-primary'>{{trans('langEclass')}}</strong>
                                                    </span>
                                                </div>
                                            </div>
                                        </div>
                                        <div class='panel-body bg-light p-5' style='border-bottom-right-radius:15px; border-bottom-left-radius:15px;'>
                                            {!! trans('langInfoAbout') !!}
                                        </div>
                                    </div>  
                                </div>
                                <div class='col-lg-4'>
                                    @if (get_config('enable_mobileapi'))
                                        <div class='col-lg-12' style='margin-left:10px;'>
                                            <div class='panel panel-default'>
                                                <div class='panel-heading text-center'>
                                                    <span class='control-label-notes'>App Store</span>
                                                </div>
                                                <div class='panel-body'>
                                                    <a href='https://itunes.apple.com/us/app/open-eclass-mobile/id1398319489' target=_blank>
                                                        <img src='template/modern/images/appstore.png' class='img-responsive center-block m-auto d-block' alt='Available on the App Store'>
                                                    </a>
                                                </div>
                                            </div>
                                        </div>
                                        <div class='col-lg-12 mt-4' style='margin-left:10px;'>
                                            <div class='panel panel-default'>
                                                <div class='panel-heading text-center'>
                                                    <span class='control-label-notes'>Play Store</span>
                                                </div>
                                                <div class='panel-body'>
                                                    <a href='https://play.google.com/store/apps/details?id=gr.gunet.eclass' target=_blank>
                                                        <img src='template/modern/images/playstore.png' class='img-responsive center-block m-auto d-block' alt='Available on the Play Store'>
                                                    </a>
                                                </div>
                                            </div>
                                        </div>
                                    @endif
                                </div>
                            </div>
                        </div>

                    
                        <div class="col-12 mt-5 mb-5">
                            <a class="btn btn-default m-auto d-block" data-bs-toggle="collapse" href="#collapse_main_section" role="button" aria-expanded="false" aria-controls="collapse_main_section">
                                <span class='control-label-notes text-center'>{{trans('langReadMore')}}</span>
                            </a>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    
    </div>

</div>

<!-- Mobile-tablet -->

<div class="container-fluid mt-3 mb-3 d-block d-md-block d-lg-none">
    <div class='row'>
        <div class='col-12 p-5 jumbotron jumbotron-login mt-3' style='border-radius:15px;'>

            <div class='col-sm-12 text-center'>
                <span class="control-label-notes"><span class='fa fa-home text-primary'></span> {{trans('langEclass')}}</span>
            </div>

            <div id="idform" class="panel panel-default WelcomeEclassPanelDefault mt-3">
                <div class="panel-heading text-center WelcomeEclassHeading">
                    <img src="template/modern/img/user.jpg" class='user-icon m-auto d-block'>
                    <strong class='fs-5 control-label-notes'>{{ trans('langUserLogin') }}</strong>
                </div>
                <div class="panel-body bg-light WelcomeEclassBody">
                    <form action="{{ $urlAppend }}" method="post">
                        <div class="login-form-spacing">
                            <h4 class='control-label-notes'>{{ trans('langUsername') }}</h4>
                            <input class="login-input" type="text" id="uname" name="uname" >
                            <h4 class="control-label-notes mt-3">{{ trans('langPassword') }}</h4>
                            <input class="login-input" type="password" id="pass" name="pass">
                            <input class="login-form-submit w-75 mt-4" type="submit" name="submit" value="{{ trans('langLogin') }}">
                        </div>
                    </form>
                    <div class="login-form-spacing2">
                        <a class="login-forgot" href="{{$urlAppend}}modules/auth/lostpass.php">{{ trans('lang_forgot_pass') }}</a>
                    </div>
                </div>
            </div>
            <div class='mt-3'>{!! $warning !!}</div>
        </div>
        <div class='col-12 mt-4'>
            <div class="panel panel-default WelcomeEclassPanelDefault">
                <div class="panel-heading text-center WelcomeEclassHeading">
                    <span class='control-label-notes'>{{trans('langWelcomeTo')}} {{trans('langEclass')}}</span>
                </div>
                <div class="panel-body p-5 bg-light WelcomeEclassBody">
                    {!! trans('langInfoAbout') !!}
                </div>
            </div>  
        </div>
        <div class='col-12 mt-4'>
            @if (get_config('enable_mobileapi'))
            <div class="panel panel-default WelcomeEclassPanelDefault">
                <div class="panel-heading text-center WelcomeEclassHeading">
                    <span class='control-label-notes'>Social Media</span>
                </div>
                <div class="panel-body p-5 bg-light WelcomeEclassBody">
                    <div class="row">
                        <div class='col-6'><a class='float-md-end float-start' href='https://itunes.apple.com/us/app/open-eclass-mobile/id1398319489' target=_blank><img src='template/modern/images/appstore.png' class='img-responsive center-block' alt='Available on the App Store'></a></div>
                        <div class='col-6'><a class="float-md-start float-end" href='https://play.google.com/store/apps/details?id=gr.gunet.eclass' target=_blank><img src='template/modern/images/playstore.png' class='img-responsive center-block' alt='Available on the Play Store'></a></div>
                    </div>
                </div>
            </div>
            @endif
        </div>
        <div class="col-12 mt-4 mb-3">
            <a class="btn btn-default m-auto d-block" data-bs-toggle="collapse" href="#collapse_main_section" role="button" aria-expanded="false" aria-controls="collapse_main_section">
            <span class='control-label-notes text-center'>{{trans('langReadMore')}}</span>
            </a>
        </div>
    </div>
</div>

<!-- collapse menu -->

<div class="collapse" id="collapse_main_section">
    <div class="container-fluid main-section">
        <div class="row">
            <div class="col-lg-12 sidebar" style="border-radius:15px; background-color:#E8E8E8;">
                @if ($announcements)
                    <div class="news">
                        <h2 class="block-title">{{ trans('langAnnouncements') }}</h2>
                        <a href='{{ $urlServer }}rss.php' style='padding-left:5px;'>
                            <span class='fa fa-rss-square'></span>
                        </a>
                        <div class="row news-list">
                            @foreach ($announcements as $announcement)
                                <div class="col-sm-12 news-list-item">
                                    <div class="date">
                                        {{ claro_format_locale_date($dateFormatLong, strtotime($announcement->date)) }}
                                    </div>
                                    <div class="title"><a class="announcement-title-a" href='modules/announcements/main_ann.php?aid={{ $announcement->id }}'>{{$announcement->title}}</a></div>
                                </div>
                            @endforeach
                        </div>
                        <div class="more-link"><a class="all_announcements" href="{{ $urlServer }}main/system_announcements.php">ΟΛΕΣ ΟΙ ΑΝΑΚΟΙΝΩΣΕΙΣ</a></div>
                    </div>
                @endif
            </div>
        </div>
    </div>


    <div class="container-fluid statistics mt-3" style="border-radius:15px;">
        <div class="statistics-wrapper">
            <h2 class="text-center">
                Στατιστικά Επισκεψιμότητας
            </h2>
            <div class="row row_pad_courses">
                <div class="col-lg-4 text-center">
                        <i class="fas fa-book"></i>
                        <div class="num">10</div>
                        <div class="num-text">μαθήματα</div>
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

     <div class="d-flex justify-content-center">
        <div class="container-fluid testimonials bg-light">
            <div class="testimonial">
                <div class="testimonial-body">Lorem ipsum dolor sit amet, consectetuer adipiscing elit, sed diam nonummy nibh euismod tincidunt ut laoreet dolore magna aliquam erat volutpat.
                    Ut wisi enim ad minim veniam.</div>
                <div class="testimonial-person">Λυδία Καλομοίρη</div>
            </div>
            <div class="testimonial">
                <div class="testimonial-body">Lorem ipsum dolor sit amet, consectetuer adipiscing elit, sed diam nonummy nibh euismod tincidunt ut laoreet dolore magna aliquam erat volutpat.
                    Ut wisi enim ad minim veniam.</div>
                <div class="testimonial-person">Λυδία Καλομοίρη</div>
            </div>
            <div class="testimonial">
                <div class="testimonial-body">Lorem ipsum dolor sit amet, consectetuer adipiscing elit, sed diam nonummy nibh euismod tincidunt ut laoreet dolore magna aliquam erat volutpat.
                    Ut wisi enim ad minim veniam.</div>
                <div class="testimonial-person">Λυδία Καλομοίρη</div>
            </div>
        </div>
    </div>

</div>

@endsection
