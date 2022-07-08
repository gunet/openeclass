@extends('layouts.default')

@section('content')

<div class="container-fluid jumbotron jumbotron-login h-auto">
    <div class="container-fluid intro-row">
    {!! $warning !!}
        <div class="row pb-5">
            <div class="col-lg-7 col-md-12 col-sm-12 col-12 d-none d-sm-none d-md-none d-lg-block">                
                <div class="intro welcome-intro p-lg-5">
                    <h1 class="welcome" >
                        {{trans('langWelcomeTo')}} <strong>{{trans('langEclass')}}</strong>
                    </h1>
                    {!! trans('langInfoAbout') !!}
                    @if (get_config('enable_mobileapi'))
                        <div class="row mt-2">
                            <div class='col-sm-6'><a href='https://itunes.apple.com/us/app/open-eclass-mobile/id1398319489' target=_blank><img src='template/modern/images/appstore.png' class='img-responsive center-block' alt='Available on the App Store'></a></div>
                            <div class='col-sm-6'><a href='https://play.google.com/store/apps/details?id=gr.gunet.eclass' target=_blank><img src='template/modern/images/playstore.png' class='img-responsive center-block' alt='Available on the Play Store'></a></div>
                        </div>
                    @endif
                </div>  
            </div>

            <div class="col-lg-5 col-md-12 col-sm-12 col-12">
                <div class="row">
                    <div class="col-xxl-12 col-xl-12 col-lg-12 col-md-9 col-sm-12 col-12 m-lg-0 d-block-0 m-auto d-block">
                        <div id="idform" class="w-auto float-lg-end m-lg-0 d-block-0 m-auto d-block" >
                            <div class="login-form-icon mt-1">

                                <img class="user-bg" src="template/modern/images/iconbg2.png" alt="iconbg">
                                <div class="icon-border">
                                    <i class="fa fa-user user-account-icon"></i>
                                </div>
                            </div>

                            <div class="login-form-body">
                                <h3 class="login-form-title">
                                    <strong>{{ trans('langUserLogin') }}</strong>
                                </h3>
                                <form action="{{ $urlAppend }}" method="post">
                                    <div class="login-form-spacing">
                                        <h4>{{ trans('langUsername') }}</h4>
                                        <input class="login-input" type="text" id="uname" name="uname" >
                                        <h4 class="password_h4">{{ trans('langPassword') }}</h4>
                                        <input class="login-input" type="password" id="pass" name="pass">
                                    </div>
                                    <div class="login-form-spacing">
                                        <input class="login-form-submit" type="submit" name="submit" value="{{ trans('langLogin') }}">
                                    </div>
                                </form>
                                <div class="login-form-spacing2">
                                    <a class="login-forgot" href="{{$urlAppend}}modules/auth/lostpass.php">{{ trans('lang_forgot_pass') }}</a>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="col-12 mt-3">
                        <a class="button_collapse_main_section" data-bs-toggle="collapse" href="#collapse_main_section" role="button" aria-expanded="false" aria-controls="collapse_main_section">
                                <i class="homes fas fa-chevron-up"></i>
                                <script>$('.button_collapse_main_section .fas.fa-chevron-up').hide();</script>
                                <i class="homes fas fa-chevron-down"></i>
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<div class="collapse" id="collapse_main_section">
    <div class="container-fluid main-section">
        <div class="row">
            <div class="col-lg-8">
                <div class="popular-lessons">
                    <h2 class="block-title">Δημοφιλή μαθήματα</h2>
                    <!-- <div class="row">
                        //@foreach($cources as $cource )
                            <div class="col-sm-4 lesson text-center">
                                <a href="#"><img src="template/modern/images/img1.jpg" alt="" height="100" width="100" /></a>
                                <h3 class="lesson-title"><a class="lesson-title-a" href="courses/{{ $cource->code }}/">{{ $cource->title }}</a> <span class="lesson-id">({{ $cource->public_code }})</span></h3>
                                <div class="lesson-professor">{{ $cource->prof_names }}</div>
                            </div>
                        //@endforeach
                    </div> -->
                    <div class="more-link"><a class="all_courses_btn" href="/modules/auth/listfaculte.php">ΔΕΙΤΕ ΟΛΑ ΤΑ ΜΑΘΗΜΑΤΑ</a></div>
                </div>
            </div>
            <div class="col-lg-4 sidebar" style="background-color:#E8E8E8;">
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


    <div class="container-fluid statistics">
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
        <div class="container-fluid testimonials">
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
