@extends('layouts.default')

@section('content')

<link href="{{ $urlAppend }}template/modern/css/homepage.css" rel="stylesheet" type="text/css">

<div class="container-login">

    <div class="col-12">

        <div class="row m-auto">

            <div class="col-12 bgEclass MentoringHomePageContent mt-0">

                <div class="row">
                    <div class='jumbotronMentoring jumbotron-login statistics-wrapper'>
                        <div class='container-fluid'>
                            <div class='row'>
                                <div class='col-xl-10 col-12 ms-auto me-auto'>
                                    <div class='col-12'>
                                        <p class='TextBold text-white text-md-center fs-2'>{{ trans('langWelcomeMentoring')}}</p>
                                    </div>
                                    
                                    <div class='col-xxl-6 col-lg-8 col-12 ms-auto me-auto mt-3'>
                                        <p class='TextSemiBold text-md-center text-white fs-6'>{!! trans('langInfoWelcomeMentoring') !!}</p>
                                    </div>
                                    
                                    <div class='col-lg-6 col-12 ms-auto me-auto mt-4'>
                                        <p class='text-md-center TextMedium fs-6 mt-3' style='color:#A9A9A9;'>{!! trans('langInfoWelcomeMentoring2') !!}</p>
                                    </div>

                                    {{--
                                    <div class='col-lg-6 col-12 ms-auto me-auto mt-4 d-flex justify-content-md-center justify-content-start align-items-center mb-lg-0 mb-2'>
                                            
                                        <a class='d-flex justify-content-center align-items-center me-3' href='https://itunes.apple.com/us/app/open-eclass-mobile/id1398319489' target=_blank>
                                            <img class='socialHomePage' src='template/modern/images/appstore.png' alt='Available on the App Store'>
                                        </a>
                                    
                                        <a class='d-flex justify-content-center align-items-center ms-3' href='https://play.google.com/store/apps/details?id=gr.gunet.eclass' target=_blank>
                                            <img class='socialHomePage' src='template/modern/images/playstore.png' alt='Available on the Play Store'>
                                        </a>
                                            
                                    </div>
                                    --}}

                                </div>
                            </div>
                        </div>
                    </div>

                    
                   
                    <div class='d-none d-lg-block bg-white'>
                        <div style='z-index:-1;' class='Mentoring_SectionMenu bg-white ms-auto me-auto'>

                            @php $intro = "mentoring_homepage_intro_$language"; @endphp

                            @if(get_config($intro))
                                <div class='container-fluid pt-4 pb-4'>
                                    <div class='row justify-content-center'>
                                        <div class="col-12 mb-3">
                                            <div class='panel panel-admin border-0 shadow-none '>
                                                <div class='panel-body rounded-Home px-5 py-4'>
                                                    {!! get_config($intro) !!}
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            @endif


                            @if ($announcements)
                            <div class='container-fluid pt-3 pb-4'>
                                <div class='row justify-content-center'>
                                    <div class="col-12 mb-3">
                                        <div class='panel panel-admin panel-announcements border-0 shadow-none mb-5'>
                                            <div class='panel-body rounded-Home px-5 py-4'>
                                                <div class='card announcements-card shadow-sm'>
                                                    <div class='card-body announcements-card-body pb-5'>
                                                        <div class='row'>
                                                            <div class='col-9'>
                                                                <p class='card-title normalBlueText fs-4 TextBold text-uppercase ps-2'>
                                                                    {{ trans('langAnnouncements') }}&nbsp
                                                                    <a href='{{ $urlServer }}rss.php'>
                                                                        <span class='fa fa-rss-square normalBlueText'></span>
                                                                    </a>
                                                                </p>
                                                                <p class='card-text'>
                                                                    @php $counterAn = 0; @endphp
                                                                    @foreach ($announcements as $announcement)
                                                                        @if($counterAn < 6)
                                                                            <div class="col-12 news-list-item ps-2 mt-5 d-flex">
                                                                                <div class="date announcement-date-home">
                                                                                    <small class='textgreyColor TextSemiBold'>{{ format_locale_date(strtotime($announcement->date),'short',false) }}</small>
                                                                                </div>
                                                                                    
                                                                                <div class='AnnounceLine ms-3 me-3'></div>

                                                                                <a class='showAnn normalBlueText' href='{{ $urlAppend }}modules/mentoring/announcements/main_ann.php?aid={{ $announcement->id }}'>
                                                                                    <span class='TextSemiBold fs-6'>{{ $announcement->title }}</span>
                                                                                </a>
                                                                                
                                                                            </div>
                                                                        @endif
                                                                    @php $counterAn++; @endphp
                                                                    @endforeach
                                                                </p>
                                                            </div>
                                                            <div class='col-3 d-flex justify-content-end'>
                                                                <img class='announcement-image2' src="{{$urlAppend}}template/modern/img/announcement3.svg">
                                                            </div>
                                                        </div>
                                                    </div>
                                                    <div class='card-footer announcement-card-footer bg-white border-0 p-3'>
                                                        <div class="more-link">
                                                            <a class="all_announcements float-end normalBlueText" href="{{ $urlAppend }}modules/mentoring/announcements/system_announcements.php">
                                                                {{ trans('langAllAnnouncements') }} <span class='fa fa-angle-right fs-6 ms-1 fw-bold'></span>
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
                        </div>
                    </div>
                    

                    
                    <div class='d-block d-lg-none bg-white pb-5 pt-0 ps-3 pe-3 Mentoring_SectionMenu'>

                        @php $introMobile = "mentoring_homepage_intro_$language"; @endphp

                        @if(get_config($introMobile))
                            <div class="col-12 mt-3">
                                <div class='panel panel-admin shadow-none border-0'>
                                    <div class='panel-body'>
                                        {!! get_config($introMobile) !!}
                                    </div>
                                </div>
                            </div>
                        @endif                               


                        @if ($announcements)
                            <div class="col-12 mt-3">
                                <div class='panel panel-admin panel-announcements border-0 shadow-none mb-3'>
                                    <div class='panel-body'>
                                        <div class='card announcements-card shadow-sm'>
                                            <div class='card-body announcements-card-body pb-5'>
                                                <div class='row'>
                                                    <div class='col-12'>
                                                        <p class='card-title normalBlueText fs-4 TextBold text-uppercase ps-2'>
                                                            {{ trans('langAnnouncements') }}&nbsp
                                                            <a href='{{ $urlServer }}rss.php'>
                                                                <span class='fa fa-rss-square normalBlueText'></span>
                                                            </a>
                                                            <img class='announcement-image2 float-end' src="{{$urlAppend}}template/modern/img/announcement3.svg">
                                                        </p>
                                                        
                                                    </div>
                                                    <div class='col-12 mt-3'>
                                                        <p class='card-text'>
                                                            @php $counterAn = 0; @endphp
                                                            @foreach ($announcements as $announcement)
                                                                @if($counterAn < 6)
                                                                    <div class="col-12 news-list-item ps-2 mt-4 d-flex">
                                                                        <div class="date announcement-date-home">
                                                                            <small class='textgreyColor TextSemiBold'>{{ format_locale_date(strtotime($announcement->date),'short',false) }}</small>
                                                                        </div>
                                                                            
                                                                        <div class='AnnounceLine ms-3 me-3'></div>

                                                                        <a class='showAnn normalBlueText' href='{{ $urlAppend }}modules/mentoring/announcements/main_ann.php?aid={{ $announcement->id }}'>
                                                                            <span class='TextSemiBold fs-6'>{{ $announcement->title }}</span>
                                                                        </a>
                                                                        
                                                                    </div>
                                                                @endif
                                                            @php $counterAn++; @endphp
                                                            @endforeach
                                                        </p>
                                                    </div>
                                                    
                                                </div>
                                            </div>
                                            <div class='card-footer announcement-card-footer bg-white border-0 p-3'>
                                                <div class="more-link">
                                                    <a class="all_announcements float-end normalBlueText" href="{{ $urlAppend }}modules/mentoring/announcements/system_announcements.php">
                                                        {{ trans('langAllAnnouncements') }} <span class='fa fa-angle-right fs-6 ms-1 fw-bold'></span>
                                                    </a>
                                                </div>
                                            </div>
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

</div>


<script>

    $('#profile_menu').on('click',function(){
        localStorage.setItem("MenuMentoring","profile");
    });
    $('.program_menu').on('click',function(){
        localStorage.setItem("MenuMentoring","program");
    });
    $('.mentors_menu,#ViewMentorsClick').on('click',function(){
        localStorage.setItem("MenuMentoring","mentors");
    });
    $('.home_menu').on('click',function(){
        localStorage.setItem("MenuMentoring","home");
    });
    $('#reg_menu').on('click',function(){
        localStorage.setItem("MenuMentoring","register");
    });
    $('.all_announcements').on('click',function(){
        localStorage.removeItem("MenuMentoring");
    });
    $('.showAnn').on('click',function(){
        localStorage.removeItem("MenuMentoring");
    });


    if(localStorage.getItem("MenuMentoring") == "profile"){
        $('.profile_menu').addClass('active');
    }else if(localStorage.getItem("MenuMentoring") == "program"){
        $('.program_menu').addClass('active');
    }else if(localStorage.getItem("MenuMentoring") == "mentors"){
        $('.mentors_menu').addClass('active');
    }else if(localStorage.getItem("MenuMentoring") == "home"){
        $('.home_menu').addClass('active');
    }else if(localStorage.getItem("MenuMentoring") == "register"){
        $('.register_menu').addClass('active');
    }else{
        localStorage.removeItem("MenuMentoring");
    }

    

</script>

@endsection
