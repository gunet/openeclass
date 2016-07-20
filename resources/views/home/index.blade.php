@extends('layouts.default')

@section('content')
    {!! $warning !!}
    <div class='row margin-top-fat'>
        <div class='col-md-12 remove-gutter'>
            <div class='jumbotron jumbotron-login'>
                <div class='row'>
                @if (!(get_config('upgrade_begin') || get_config('dont_display_login_form')))
                    <div class='col-xs-12 col-sm-6 col-md-5 col-lg-4 pull-right login-form'>
                        <div class='wrapper-login-option'>
                            @foreach ($authLinks as $i =>$authLink)
                            <div class='{{ $authLink['class'] }}'>
                                <h2>{{ trans('langUserLogin') }}</h2>
                                <div>
                                    @if ($authLink['showTitle'])
                                        <span class='head-text' style='font-size:14px;'>$l[title]</span>
                                    @endif
                                    {!! $authLink['html'] !!}
                                </div>
                                @if (count($authLinks) > 1)
                                <div class='login-settings row'>
                                    <div class='or-separator'>
                                        <span>{{ trans('langOr') }}</span>
                                    </div>
                                    <div class='alt_login text-center'>
                                        <span>
                                            @if (count($authLinks) <= 3)
                                                @foreach ($authLinks as $j => $otherAuth)
                                                    @if ($j != $i)
                                                        <button type='button' data-target='{{ $j }}' class='option-btn-login hide'>{{ $otherAuth['title'] }}</button>
                                                    @endif
                                                @endforeach                                            
                                            @else
                                            <a href='{{ $urlAppend }}main/login_form.php' class='btn btn-default option-btn-login'>{{ trans('langAlternateLogin') }}</a>
                                            @endif
                                        </span>
                                    </div>
                                </div>
                                @endif
                            </div>    
                            @endforeach
                        </div>
                    </div>
                @endif
                </div>
            </div>
        </div>
    </div>
    <div class='row'>
        <div class='col-md-8'>
            <div class='panel'>
                <div class='panel-body'>
                    {!! trans('langInfoAbout') !!}
                </div>
            </div>
            {!! $home_main_area_widgets !!}
            @if ($announcements)
                <div class='content-title h3'>
                    <a href='{{$urlServer}}main/system_announcements.php'>{{ trans('langAnnouncements') }}</a>
                    <a href='{{ $urlServer }}rss.php' style='padding-left:5px;'>
                        <span class='fa fa-rss-square'></span>
                    </a>
                </div>
                <div class='panel'>
                    <div class='panel-body'>
                        <ul class='front-announcements'>
                            @foreach ($announcements as $announcement) 
                                <li>
                                    <div>
                                        <a class='announcement-title' href='modules/announcements/main_ann.php?aid={{ $announcement->id }}'>
                                            {{ $announcement->title }}
                                        </a>
                                    </div>
                                    <span class='announcement-date'>
                                        - {{ claro_format_locale_date($dateFormatLong, strtotime($announcement->date)) }} -
                                    </span>
                                    {!! standard_text_escape(ellipsize_html("<div class='announcement-main'>$announcement->body</div>", 500, "<div class='announcements-more'><a href='modules/announcements/main_ann.php?aid=$announcement->id'>".trans('langMore')." &hellip;</a></div>")) !!}
                                </li>                                
                            @endforeach
                        </ul>
                    </div>
                </div>                
            @endif
        </div>
        <div class='col-md-4'>
            @if ($extra_right)
                <div class='panel'>
                    <div class='panel-body'>
                        {{ trans('langExtrasRight') }}
                    </div>
                </div>
            @endif           
            @if ($online_users > 0)
                <div class='panel'>
                    <div class='panel-body'>
                        <span class='fa fa-group space-after-icon'></span> &nbsp;{{ trans('langOnlineUsers') }}: {{ $online_users }}
                    </div>
                </div>
            @endif            
            @if (get_config('opencourses_enable'))
                @if ($openCoursesExtraHTML) {
                    <div class='panel opencourses'>
                        <div class='panel-body'>
                            {!! $openCoursesExtraHTML !!}
                        </div>
                    </div>
                @endif               
                <div class='panel opencourses-national'>
                    <a href='http://opencourses.gr' target='_blank'>
                        {{ trans('langNationalOpenCourses') }}
                    </a>
                </div>
            @endif            
            <div class='panel' id='openeclass-banner'>
                <div class='panel-body'>
                    <a href='http://www.openeclass.org/' target='_blank'>
                        <img class='img-responsive center-block' src='{{ $themeimg }}/open_eclass_banner.png' alt='Open eClass Banner'>
                    </a>
                </div>
            </div>            
            @if (get_config('enable_mobileapi'))
            <div class='panel mobile-apps'>
                <div class='panel-body'>
                    <div class='row'>
                        <div class='col-xs-6'>
                            <a href='https://itunes.apple.com/us/app/open-eclass-mobile/id796936702' target=_blank><img src='{{ $themeimg }}/appstore.png' class='img-responsive center-block' alt='Available on the App Store'></a>
                        </div>
                        <div class='col-xs-6'>
                            <a href='https://play.google.com/store/apps/details?id=gr.gunet.eclass' target=_blank><img src='{{ $themeimg }}/playstore.png' class='img-responsive center-block' alt='Available on the Play Store'></a>
                        </div>
                    </div>
                </div>
            </div>
            @endif
            {!! $home_page_sidebar_widgets !!}
        </div>
    </div>
@endsection
