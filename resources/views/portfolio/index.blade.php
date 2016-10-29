@extends('layouts.default')

@section('content')
    {!! isset($action_bar) ?  $action_bar : '' !!}
    <div class='row'>
        <div id='my-courses' class='col-md-7'>
            <div class='row'>
                <div class='col-md-12'>
                    <div class='content-title h2'>{{ trans('langMyCourses') }}</div>
                    <div class='panel'>
                        <div class='panel-body'>
                            {!! $perso_tool_content['lessons_content'] !!}                       
                        </div>
                    </div>
                </div>
            </div>
            <div class='row'>
                <div class='col-md-12 my-announcement-list'>
                    <div class='content-title h2'>{{ trans('langMyPersoAnnouncements') }}</div>
                    <div class='panel'>
                        <div class='panel-body'>
                            <ul class='tablelist'>
                                @if(!empty($user_announcements))
                                    {!! $user_announcements !!}
                                @else
                                    <li class='list-item' style='border-bottom:none;'>
                                        <div class='text-title not_visible'> - {{ trans('langNoRecentAnnounce') }} - </div>
                                    </li>
                                @endif
                            </ul>
                        </div>
                        <div class='panel-footer clearfix'>
                            <div class='pull-right'>
                                <a href='../modules/announcements/myannouncements.php'>
                                    <small>{{ trans('langMore') }}&hellip;</small>
                                </a>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            {!! $portfolio_page_main_widgets !!}          
        </div>

        <div class='col-md-5'>
            <div class='row'>
                <div class='col-md-12'>
                    <div class='content-title h2'>{!! trans('langMyAgenda') !!}</div>
                    <div class='panel'>
                        <div class='panel-body'>
                            {!! $perso_tool_content['personal_calendar_content'] !!}
                        </div>
                        <div class='panel-footer'>
                            <div class='row'>
                                <div class='col-sm-6 event-legend'>
                                    <div>
                                        <span class='event event-important'></span>
                                        <span>{{ trans('langAgendaDueDay') }}</span>
                                    </div>
                                    <div>
                                        <span class='event event-info'></span>
                                        <span>{{ trans('langAgendaCourseEvent') }}</span>
                                    </div>
                                </div>
                                <div class='col-sm-6 event-legend'>
                                    <div>
                                        <span class='event event-success'></span>
                                        <span>{{ trans('langAgendaSystemEvent') }}</span>
                                    </div>
                                    <div>
                                        <span class='event event-special'></span>
                                        <span>{{ trans('langAgendaPersonalEvent') }}</span>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>        
            <div class='row'>
                <div class='col-md-12 my-messages-list'>
                    <div class='content-title h2'>{{ trans('langMyPersoMessages') }}</div>
                    <div class='panel'>
                        <div class='panel-body'>
                            <ul class='tablelist'>
                            @if(!empty($user_messages))
                                {!! $user_messages !!}
                            @else
                                <li class='list-item' style='border-bottom:none;'>
                                    <div class='text-title not_visible'> - {{ trans('langDropboxNoMessage') }} - </div>
                                </li>
                            @endif
                            </ul>
                        </div>
                        <div class='panel-footer clearfix'>
                            <div class='pull-right'>
                                <a href='{{ $urlAppend }}modules/message/'>
                                    <small>{{ trans('langMore') }}&hellip;</small>
                                </a>
                            </div>
                        </div>
                    </div>
                </div>
            </div>        
            {!! $portfolio_page_sidebar_widgets !!}      
        </div>
    </div>
    <div id='profile_box' class='row'>
        <div class='col-md-12'>
            <div class='content-title h2'>{{ trans('langCompactProfile') }}</div>
            <div class='panel'>
                <div class='panel-body'>
                    <div class='row'>
                        <div class='col-xs-4 col-sm-2'>
                            <img src='{{ user_icon($uid, IMAGESIZE_LARGE) }}' style='width:80px;' class='img-circle center-block img-responsive' alt='{{ trans('langProfileImage') }}'>
                            <br>
                            <div class='not_visible text-center' style='margin:0px;'>
                                {{ $_SESSION['uname'] }}
                            </div>
                        </div>
                        <div class='col-xs-8 col-sm-5'>
                            <div class='h3' style='font-size: 18px; margin: 10px 0 10px 0;'>
                                <a href='{{ $urlServer }}main/profile/display_profile.php'>{{ $_SESSION['givenname'] . " " . $_SESSION['surname'] }}</a>
                            </div>
                            <div>
                                <div class='h5'>
                                    <span class='tag'>{{ trans('langFaculty') }}: </span>
                                </div>
                                <span class='tag-value text-muted'>
                                    @foreach ($departments as $key => $dep)
                                        {!! $key+1 < count($departments) ? '<br>' : '' !!}
                                        {!! $tree->getFullPath($dep) !!}
                                    @endforeach
                                </span>
                            </div>
                            @if ($lastVisit)
                                <br>
                                <span class='tag'>{{ trans('langProfileLastVisit') }}: </span>
                                <span class='tag-value text-muted'>
                                    {{ claro_format_locale_date(trans('dateFormatLong'), strtotime($lastVisit->when)) }}
                                </span>
                            @endif
                        </div>
                        <div class='col-xs-12 col-sm-5'>
                            <ul class='list-group'>
                                <li class='list-group-item'>
                                  <span class='badge'>{{ $student_courses_count }}</span>
                                  <span class='text-muted'>{{ trans('langSumCoursesEnrolled') }}</span>
                                </li>
                                @if (!$is_editor && $teacher_courses_count > 0)
                                    <li class='list-group-item'>
                                        <span class='badge'>{{ $teacher_courses_count }}</span>
                                        <span class='text-muted'>{{ trans('langSumCoursesSupport') }}</span>
                                    </li>
                                @endif
                            </ul>
                            @if ($_SESSION['canChangePassword'])
                                <div class='pull-right'>
                                    <a href='{{ $urlServer }}main/profile/password.php'>
                                        <small>{{ trans('langProfileQuickPassword') }}</small>
                                    </a>
                                </div>
                            @endif
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>    
@endsection
