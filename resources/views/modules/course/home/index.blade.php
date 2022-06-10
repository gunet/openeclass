@extends('layouts.default')

@section('content')
<style>
    #collapseDescription {
        background-color: #f5f5f5;
    }
    #collapseDescription > div {
        padding: 5px 20px;
    }

    .course_completion_panel_percentage
        {
            bottom:15px;
            right:15px;
            font-size:20px;
            padding: 10px 10px;
            width: 72px; /* original was width: 40px; */
            height: 72px; /* original was height: 40px;*/
            border: 6px solid #AAAAAA;
            border-radius: 40px;
            background: #FFFFFF;
            color: #AAAAAA;
            line-height: 38px;
            font-weight: 600;
        }
        .state_success
        {
            color: #11D888;
        }
</style>
    {!! $action_bar !!}
    <div class='row margin-top-thin margin-bottom-fat'>
        <div class='col-md-12'>
            <div class='panel panel-default'>

                <div class='panel-body'>
                    <div id='course-title-wrapper' class='course-info-title clearfix'>
                        <div class='pull-left h4'>{{ trans('langDescription') }}</div>{!! $edit_link !!}
                        <ul class="course-title-actions clearfix pull-right list-inline">
                            @if ($is_course_admin)
                                <li class="access pull-right"><a href="{{$urlAppend}}modules/course_info/?course={{$course_code}}" style="color: #23527C;"><span class="fa fa-wrench fa-fw" data-toggle="tooltip" data-placement="top" title='{{trans('langCourseInfo')}}'></span></a></li>
                            @endif
                            <li class="access pull-right">
                                <a href='javascript:void(0);'>{!! course_access_icon($visible) !!}</a>
                            </li>
                            <li class="access pull-right">
                                <a data-modal='citation' data-toggle='modal' data-target='#citation' href='javascript:void(0);'><span class='fa fa-paperclip fa-fw' data-toggle='tooltip' data-placement='top' title='{{ trans('langCitation') }}'></span><span class='hidden'>.</span></a>
                            </li>
                            @if ($uid)
                                <li class="access pull-right">
                                 @if ($is_course_admin)
                                     <a href="{{ $urlAppend }}modules/user/index.php?course={{$course_code}}"><span class="fa fa-users fa-fw" data-toggle="tooltip" data-placement="top" title="{{ $numUsers }}&nbsp;{{ trans('langRegistered') }}"></span><span class="hidden">.</span></a>
                                 @else
                                     @if ($visible == COURSE_CLOSED)
                                            <a href="{{ $urlAppend }}modules/user/userslist.php?course={{ $course_code }}"><span class="fa fa-users fa-fw" data-toggle="tooltip" data-placement="top" title="{{ $numUsers }}&nbsp;{{ trans('langRegistered') }}"></span></a>
                                     @endif
                                 @endif
                                </li>
                            @endif
                            @if ($offline_course)
                                <li class='access pull-right'>
                                    <a href="{{ $urlAppend }}modules/offline/index.php?course={{ $course_code }}">
                                        <span class="fa fa-download fa-fw" data-toggle="tooltip" data-placement="top" title="{{ trans('langDownloadCourse') }}"></span>
                                    </a>
                                </li>
                            @endif
                        </ul>
                    </div>
                    @if ($course_info->home_layout == 1)
                        <div class='banner-image-wrapper col-md-5 col-sm-5 col-xs-12'>
                            <div>
                                <img class='banner-image img-responsive' src='{{ isset($course_info->course_image) ? "{$urlAppend}courses/$course_code/image/" . rawurlencode($course_info->course_image) : "$themeimg/ph1.jpg" }}' alt='Course Banner'/>
                            </div>
                        </div>
                    @endif
                    <div class='col-xs-12{{ $course_info->home_layout == 1 ? ' col-sm-7' : ''}}'>
                        <div class=''>
                            <div class='course_info'>
                                @if ($course_info->description)
                                    <!--Hidden html text to store the full description text & the truncated desctiption text so as to be accessed by javascript-->
                                    <div id='not_truncated' class='hidden'>
                                        {!! $full_description !!}
                                    </div>
                                    <div id='truncated' class='hidden'>
                                        {!! $truncated_text !!}
                                    </div>
                                    <!--Show the description text-->
                                    <div id='descr_content' class='is_less'>
                                        {!! $truncated_text !!}
                                    </div>
                                @else
                                    <p class='not_visible'> - {{ trans('langThisCourseDescriptionIsEmpty') }} - </p>
                                @endif
                            </div>
                        </div>
                    </div>

                    @if ((!$is_editor) and (!$courseDescriptionVisible))
                        @if ($course_info->course_license)
                            <span class='pull-right' style="margin-top: 15px;">{!! copyright_info($course_id) !!}</span>
                        @endif
                    @else
                        <div class='col-xs-12 course-below-wrapper'>
                            <div class='row text-muted course-below-info'>
                                <a role='button' id='btn-syllabus' data-toggle='collapse' href='#collapseDescription' aria-expanded='false' aria-controls='collapseDescription'>
                                        <span class='fa fa-chevron-right fa-fw'></span><span style='padding-left: 5px;'>{{ trans('langCourseDescription') }}</span></a>
                                {!! $edit_course_desc_link !!}
                                @if ($course_info->course_license)
                                    <span class="pull-right">{!! copyright_info($course_id) !!}</span>
                                @endif
                                <div class='collapse' id='collapseDescription' style='margin-top: 20px; padding-top: 20px;'>
                                    @foreach ($course_descriptions as $row)
                                        <div style='margin-top: 1px;'><strong>{{ q($row->title) }}</strong></div>
                                        <div>{!! standard_text_escape($row->comments) !!}</div>
                                    @endforeach
                                </div>
                            </div>
                        </div>
                    @endif

                </div>
                @if(isset($rating_content) || isset($social_content) || isset($comment_content))
                    <div class='panel-footer'>
                        <div class='row'>
                        @if(isset($rating_content))
                            <div class='col-sm-6'>
                                {!! $rating_content !!}
                            </div>
                        @endif
                        @if(isset($social_content) || isset($comment_content))
                           <div class='text-right{{ isset($rating_content) ? " col-xs-6" : " col-xs-12" }}'>
                                @if(isset($comment_content))
                                    {!! $comment_content !!}
                                @endif
                                @if(isset($social_content) && isset($comment_content))
                                    &nbsp;&nbsp;&nbsp;-&nbsp;&nbsp;&nbsp;
                                @endif
                                @if(isset($social_content))
                                    {!! $social_content !!}
                                @endif
                            </div>
                        @endif
                    </div>
                </div>
            @endif
            </div>
        </div>
    </div>
    <div class='row'>
        @if (!$alter_layout)
            <div class='col-md-8 course-units'>
                <div class='row'>
                    <div class='col-md-12'>
                        <div class='content-title pull-left h3'>
                            {{ trans('langCourseUnits') }}
                        </div>
                            <a class='pull-left add-unit-btn' id='help-btn' href='{{ $urlAppend }}modules/help/help.php?language={{ $language}}&topic=course_units' data-toggle='tooltip' data-placement='top' title='{{ trans('langHelp') }}'>
                                <span class='fa fa-question-circle'></span>
                            </a>
                        @if ($is_editor and $course_info->view_type == 'units')
                            <a href='{{ $urlServer }}modules/units/info.php?course={{ $course_code }}' class='pull-left add-unit-btn' data-toggle='tooltip' data-placement='top' title='{{ trans('langAddUnit') }}'>
                                <span class='fa fa-plus-circle'></span>
                                <span class='hidden'>.</span>
                            </a>
                        @endif
                    </div>
                </div>
                {!! $cunits_content !!}
                {!! $course_home_main_area_widgets !!}
            </div>
        @endif

        <div class='col-md-{{ $cunits_sidebar_columns }}'>
            <div class='row'>
                @if (isset($course_completion_id) and $course_completion_id > 0)
                    <div class='col-md-{{ $cunits_sidebar_subcolumns }}'>
                        <div class='content-title'>{{ trans('langCourseCompletion') }}</div>
                        <div class='panel'>
                            <div class='panel-body'>
                                <div class='text-center'>
                                    <div class='col-sm-12'>
                                        <div class="center-block" style="display:inline-block;">
                                            <a style="text-decoration:none;" href='{{ $urlServer }}modules/progress/index.php?course={{ $course_code }}&badge_id={{ $course_completion_id}}&u={{ $uid }}'>
                                        @if ($percentage == '100%')
                                            <i class='fa fa-check-circle fa-5x state_success'></i>
                                        @else
                                            <div class='course_completion_panel_percentage'>
                                                {{ $percentage }}
                                            </div>
                                        @endif
                                        </a>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                @endif
                @if (isset($level) && !empty($level))
                    <div class='col-md-{{ $cunits_sidebar_subcolumns }}'>
                        <div class='content-title'>{{ trans('langOpenCourseShort') }}</div>
                        <div class='panel'>
                            <div class='panel-body'>
                                {!! $opencourses_level !!}
                            </div>
                            <div class='panel-footer'>
                                {!! $opencourses_level_footer !!}
                            </div>
                        </div>
                    </div>
                @endif
                <div class='col-md-{{ $cunits_sidebar_subcolumns }}'>
                    <div class='content-title'>{{ trans('langCalendar') }}</div>
                    <div class='panel'>
                        <div class='panel-body'>
                            {!! $user_personal_calendar !!}
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
                <div class='col-md-{{ $cunits_sidebar_subcolumns }}'>
                    <div class='content-title'>{{ trans('langAnnouncements') }}</div>
                    <div class='panel'>
                        <div class='panel-body'>
                            <ul class='tablelist'>
                                {!! course_announcements() !!}
                            </ul>
                        </div>
                        <div class='panel-footer clearfix'>
                            <div class='pull-right'>
                                <a href='{{ $urlAppend }}modules/announcements/?course={{ $course_code}}'>
                                    <small>{{ trans('langMore') }}&hellip;</small>
                                </a>
                            </div>
                        </div>
                    </div>
                </div>
                <div class='col-md-{{ $cunits_sidebar_subcolumns }}'>
                    {!! $course_home_sidebar_widgets !!}
                </div>
            </div>
        </div>
    </div>
    <div class='modal fade' id='citation' tabindex='-1' role='dialog' aria-labelledby='myModalLabel' aria-hidden='true'>
        <div class='modal-dialog'>
            <div class='modal-content'>
                <div class='modal-header'>
                    <button type='button' class='close' data-dismiss='modal' aria-label='Close'>
                        <span aria-hidden='true'>&times;</span>
                    </button>
                    <div class='modal-title h4' id='myModalLabel'>{{ trans('langCitation') }}</div>
                </div>
                <div class='modal-body'>
                    {{ $course_info->prof_names }}&nbsp;
                    <span>{{ $currentCourseName }}</span>&nbsp;
                    {{ trans('langAccessed') }} {{ claro_format_locale_date(trans('dateFormatLong'), strtotime('now')) }}&nbsp;
                    {{ trans('langFrom2') }} {{ $urlServer }}courses/{{$course_code}}/
                </div>
            </div>
        </div>
    </div>
    @if (!$registered)
        <script type='text/javascript'>
            $(function() {
                $('#passwordModal').on('click', function(e){
                    var registerUrl = this.href;
                    e.preventDefault();
                    @if ($course_info->password !== '')
                        bootbox.dialog({
                            title: '{{ js_escape(trans('langLessonCode')) }}',
                            message: '<form class="form-horizontal" role="form" action="' + registerUrl + '" method="POST" id="password_form">' +
                                        '<div class="form-group">' +
                                            '<div class="col-sm-12">' +
                                                '<input type="text" class="form-control" id="password" name="password">' +
                                                '<input type="hidden" id="register" name="register" value="from-home">' +
                                                "{!! generate_csrf_token_form_field() !!}" +
                                            '</div>' +
                                        '</div>' +
                                      '</form>',
                            buttons: {
                                cancel: {
                                    label: '{{ js_escape(trans('langCancel')) }}',
                                    className: 'btn-default'
                                },
                                success: {
                                    label: '{{ js_escape(trans('langSubmit')) }}',
                                    className: 'btn-success',
                                    callback: function (d) {
                                        var password = $('#password').val();
                                        if(password != '') {
                                            $('#password_form').submit();
                                        } else {
                                            $('#password').closest('.form-group').addClass('has-error');
                                            $('#password').after('<span class="help-block">{{ js_escape(trans('langTheFieldIsRequired')) }}</span>');
                                            return false;
                                        }
                                    }
                                }
                            }
                        });
                    @else
                        $('<form method="POST" action="' + registerUrl + '">' +
                              '<input type="hidden" name="register" value="from-home">' +
                              "{!! generate_csrf_token_form_field() !!}" +
                          '</form>').appendTo('body').submit();
                    @endif
                });
            });
        </script>
    @endif
@endsection
