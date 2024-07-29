@extends('layouts.default')

@section('content')


<div class="col-12 main-section">
    <div class='{{ $container }} module-container py-lg-0'>
        <div class="course-wrapper d-lg-flex align-items-lg-strech w-100">

            @include('layouts.partials.left_menu')
            <div class="col_maincontent_active">
                    
                <div class="row">

                    @include('layouts.common.breadcrumbs', ['breadcrumbs' => $breadcrumbs])

                    <div class="offcanvas offcanvas-start d-lg-none" tabindex="-1" id="collapseTools">
                        <div class="offcanvas-header">
                            <button type="button" class="btn-close text-reset" data-bs-dismiss="offcanvas" aria-label="Close"></button>
                        </div>
                        <div class="offcanvas-body">
                            @include('layouts.partials.sidebar',['is_editor' => $is_editor])
                        </div>
                    </div>

                    @include('layouts.partials.legend_view')

                    @include('layouts.partials.show_alert') 

                    {!! 
                        action_bar(array(
                            array('title' => trans('langUserReferences'),
                                'url' => $urlAppend . 'modules/session/user_report.php?course=' . $course_code . '&session=' . $sessionID,
                                'icon' => 'fa-solid fa-address-card',
                                'level' => 'primary-label',
                                'show' => ($is_consultant or $is_course_reviewer)
                            ),
                            array('title' => trans('langEditUnitSection'),
                                'url' => $urlAppend . 'modules/session/edit.php?course=' . $course_code . '&session=' . $sessionID,
                                'icon' => 'fa fa-edit',
                                'level' => 'secondary',
                                'show' => $is_consultant
                            ),
                            array('title' => trans('langUserConsent'),
                                'url' => $urlAppend . 'modules/session/session_users.php?course=' . $course_code . '&session=' . $sessionID,
                                'icon' => 'fa fa-users',
                                'button-class' => 'btn-success',
                                'level' => 'secondary',
                                'show' => ($is_consultant or $is_course_reviewer)
                            ),
                            array('title' => trans('langCompleteSession'),
                                'url' => $urlAppend . 'modules/session/complete.php?course=' . $course_code . '&session=' . $sessionID . '&manage=1',
                                'icon' => 'fa fa-trophy',
                                'button-class' => 'btn-success',
                                'show' => $is_consultant
                            ),
                            array('title' => trans('langDownloadFile'),
                                'url' => $urlAppend . 'modules/session/resource.php?course=' . $course_code . '&session=' . $sessionID . '&type=doc_upload',
                                'icon' => 'fa-solid fa-file-arrow-up',
                                'level' => 'secondary',
                                'show' => (!is_module_disable(MODULE_ID_DOCS) && $is_consultant)
                            ),
                            array('title' => trans('langAttendance') . ' ' . trans('langUsersOf'),
                                'url' => $urlAppend . 'modules/session/resource.php?course=' . $course_code . '&session=' . $sessionID . '&type=attendance',
                                'icon' => 'fa-solid fa-clipboard-user',
                                'level' => 'secondary',
                                'show' => $is_consultant
                            ),
                            array('title' => trans('langSelect') . ' ' . trans('langInsertPoll'),
                                'url' => $urlAppend . 'modules/session/resource.php?course=' . $course_code . '&session=' . $sessionID . '&type=poll',
                                'icon' => 'fa-solid fa-question',
                                'level' => 'secondary',
                                'show' => (!is_module_disable(MODULE_ID_QUESTIONNAIRE) && $is_consultant)
                            ),
                            array('title' => trans('langSelect') . ' ' . trans('langInsertDoc'),
                                'url' => $urlAppend . 'modules/session/resource.php?course=' . $course_code . '&session=' . $sessionID . '&type=doc',
                                'icon' => 'fa-regular fa-folder',
                                'level' => 'secondary',
                                'show' => (!is_module_disable(MODULE_ID_DOCS) && $is_consultant)
                            ),
                            array('title' => trans('langSelect') . ' ' . trans('langInsertLink'),
                                'url' => $urlAppend . 'modules/session/resource.php?course=' . $course_code . '&session=' . $sessionID . '&type=link',
                                'icon' => 'fa-solid fa-link',
                                'level' => 'secondary',
                                'show' => (!is_module_disable(MODULE_ID_LINKS) && $is_consultant)
                            ),
                            array('title' => trans('langSelect') . ' ' . trans('langInsertTcMeeting'),
                                'url' => $urlAppend . 'modules/session/resource.php?course=' . $course_code . '&session=' . $sessionID . '&type=tc',
                                'icon' => 'fa-solid fa-users-rectangle',
                                'level' => 'secondary',
                                'show' => (!is_module_disable(MODULE_ID_TC) && $is_consultant && is_remote_session($course_id,$sessionID) && count($participants) > 0 && get_config('ext_zoom_enabled'))
                            ),
                            array('title' => trans('langInsertPassage'),
                                'url' => $urlAppend . 'modules/session/resource.php?course=' . $course_code . '&session=' . $sessionID . '&type=passage',
                                'icon' => 'fa-regular fa-keyboard',
                                'level' => 'secondary',
                                'show' => $is_consultant
                            ),
                            array('title' => trans('langAdd') . ' ' . trans('langInsertTcMeeting'),
                                'url' => $urlAppend . 'modules/session/resource.php?course=' . $course_code . '&session=' . $sessionID . '&type=add_tc' . '&token=' . $_SESSION['csrf_token'],
                                'class' => "add-session",
                                'data_action_class' => 'submitAdminBtn',
                                'confirm' => trans('langAdd') . ' ' . trans('langInsertTcMeeting'),
                                'icon' => 'fa-solid fa-users-rectangle',
                                'confirm_title' => trans('langAddTcSession'),
                                'confirm_button' => trans('langSubmit'),
                                'show' => (!is_module_disable(MODULE_ID_TC) && $is_consultant && is_remote_session($course_id,$sessionID) && count($participants) > 0 && !get_config('ext_zoom_enabled') && get_config('ext_bigbluebutton_enabled'))
                            ),
                            array('title' => trans('langAdd') . ' ' . trans('langInsertTcMeeting'),
                                'url' => $urlAppend . 'modules/tc/index.php?course=' . $course_code . '&new=1' . '&for_session=' . $sessionID,
                                'class' => "add-session",
                                'data_action_class' => 'submitAdminBtn',
                                'icon' => 'fa-solid fa-users-rectangle',
                                'show' => (!is_module_disable(MODULE_ID_TC) && $is_consultant && is_remote_session($course_id,$sessionID) && count($participants) > 0 && get_config('ext_zoom_enabled'))
                            ),
                        ))
                    !!}

                    @if(count($all_session) > 0)
                        <div class='col-12'>
                            <div class="card panelCard card-sessions px-lg-4 py-lg-3 p-3">
                                <div class='card-body p-0'>
                                    <ul class="tree-sessions">
                                        <li>
                                            <details>
                                                <summary>
                                                    <h3 class='mb-0'>
                                                        @if($is_consultant)
                                                            {{ trans('langAllSessions') }}
                                                        @else
                                                            {{ trans('langMySessions')}}
                                                        @endif
                                                    </h3>
                                                </summary>
                                                <ul>
                                                    @foreach ($all_session as $cu)
                                                        <li>
                                                            <a class='TextBold 
                                                                @if($is_consultant && ($cu->finish < $current_time or !$cu->visible)) opacity-help @endif
                                                                @if(!$is_consultant && ($cu->start > $current_time)) pe-none opacity-help @endif
                                                                @if(!$is_consultant && ($cu->finish < $current_time)) opacity-help @endif'
                                                                href='{{ $urlServer }}modules/session/session_space.php?course={{ $course_code }}&amp;session={{ $cu->id }}'>
                                                                {{ $cu->title }}
                                                            </a>
                                                            <br>
                                                            @if (!is_null($cu->start))
                                                                <small>
                                                                    <span class='help-block'>
                                                                        {{ trans('langStart')}}:&nbsp;{!! format_locale_date(strtotime($cu->start), 'short', false) !!} &nbsp;&nbsp; -- &nbsp;&nbsp;
                                                                        {{ trans('langEnd')}}:&nbsp;{!! format_locale_date(strtotime($cu->finish), 'short', false) !!} </br>
                                                                    </span>
                                                                </small>
                                                            @endif
                                                            @if($cu->finish < $current_time or !$cu->visible or $cu->start > $current_time) 
                                                                @if($cu->finish < $current_time)
                                                                    <span class='badge Accent-200-bg'>{{ trans('langHasExpired') }}</span>
                                                                @elseif(!$cu->visible)
                                                                    <span class='badge Accent-200-bg'>{{ trans('langNotDisplay') }}</span>
                                                                @elseif($cu->start > $current_time)
                                                                    <span class='badge Neutral-900-bg'>{{ trans('langSessionNotStarted') }}</span>
                                                                @endif
                                                            @endif
                                                        </li>
                                                    @endforeach
                                                </ul>
                                            </details>
                                        </li>
                                    </ul>
                                </div>
                            </div>
                        </div>
                    @endif


                    <div class='col-12 mt-4'>
                        <div class="card panelCard px-lg-4 py-lg-3">
                            <div class='card-header border-0'>
                                <div class='d-flex justify-content-between align-items-center gap-3 flex-wrap'>
                                    <h3>{{ $pageName }}</h3>
                                    <a class='link-color' data-bs-toggle='modal' data-bs-target='#session-participants'>
                                        {{ trans('langParticipants') }}
                                    </a>
                                </div>
                                <div class='mt-2'>{!! $is_session_completed_message !!}</div>
                                @if($prereq_session)
                                    <p class='TextBold'>{{ trans('langSessionPrerequisites') }}:&nbsp;<span>{{ $prereq_session->title }}</span></p>
                                @endif
                                @if($comments)
                                    <div>{!! $comments->comments !!}</div>
                                @endif
                            </div>
                            <div class="card-body">
                                {!! $tool_content_sessions !!}
                            </div>
                        </div>
                    </div>

                </div>
            </div>

        </div>
    
    </div>
</div>



<div class='modal fade' id='session-participants' tabindex='-1' role='dialog' aria-labelledby='ParticipantsLabel' aria-hidden='true'>
    <div class='modal-dialog'>
        <div class='modal-content'>
            <div class='modal-header'>
                <div class='modal-title' id='ParticipantsLabel'>{{ trans('langParticipants') }}</div>
                <button type='button' class='close' data-bs-dismiss='modal' aria-label='Close'>
                </button>
            </div>
            <div class='modal-body'>
                <div class='col-12'>
                    @if(count($participants)>0)
                    <ul class='list-group list-group-flush'>
                        @foreach($participants as $p)
                            <li class='list-group-item element'>{!! display_user($p->participants) !!}</li>
                        @endforeach
                    </ul>
                    @else
                        <div class='alert alert-warning'>
                            <i class='fa-solid fa-triangle-exclamation fa-lg'></i>
                            <span>{{ trans('langNoInfoAvailable')}}</span>
                        </div>
                    @endif
                </div>
            </div>
        </div>
    </div>
</div>


@endsection
