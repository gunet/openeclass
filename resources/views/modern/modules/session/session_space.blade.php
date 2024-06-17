@extends('layouts.default')

@section('content')


<div class="col-12 main-section">
    <div class='{{ $container }} module-container py-lg-0'>
        <div class="course-wrapper d-lg-flex align-items-lg-strech w-100">

            <div id="background-cheat-leftnav" class="col_sidebar_active d-flex justify-content-start align-items-strech ps-lg-0 pe-lg-0"> 
                <div class="d-none d-sm-block d-sm-none d-md-block d-md-none d-lg-block ContentLeftNav">
                    @include('layouts.partials.sidebar',['is_editor' => $is_editor])
                </div>
            </div>

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

                    @if(Session::has('message'))
                    <div class='col-12 all-alerts'>
                        <div class="alert {{ Session::get('alert-class', 'alert-info') }} alert-dismissible fade show" role="alert">
                            @php 
                                $alert_type = '';
                                if(Session::get('alert-class', 'alert-info') == 'alert-success'){
                                    $alert_type = "<i class='fa-solid fa-circle-check fa-lg'></i>";
                                }elseif(Session::get('alert-class', 'alert-info') == 'alert-info'){
                                    $alert_type = "<i class='fa-solid fa-circle-info fa-lg'></i>";
                                }elseif(Session::get('alert-class', 'alert-info') == 'alert-warning'){
                                    $alert_type = "<i class='fa-solid fa-triangle-exclamation fa-lg'></i>";
                                }else{
                                    $alert_type = "<i class='fa-solid fa-circle-xmark fa-lg'></i>";
                                }
                            @endphp
                            
                            @if(is_array(Session::get('message')))
                                @php $messageArray = array(); $messageArray = Session::get('message'); @endphp
                                {!! $alert_type !!}<span>
                                @foreach($messageArray as $message)
                                    {!! $message !!}
                                @endforeach</span>
                            @else
                                {!! $alert_type !!}<span>{!! Session::get('message') !!}</span>
                            @endif
                            
                            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                        </div>
                    </div>
                    @endif


                    {!! 
                        action_bar(array(
                            array(
                                'title' => trans('langBack'),
                                'url' => $urlAppend . 'modules/session/index.php?course=' . $course_code,
                                'icon' => 'fa-reply',
                                'button-class' => 'btn-success',
                                'level' => 'primary-label'
                                
                            ),
                            array('title' => trans('langEditUnitSection'),
                                'url' => $urlAppend . 'modules/session/edit.php?course=' . $course_code . '&session=' . $sessionID,
                                'icon' => 'fa fa-edit',
                                'level' => 'primary-label',
                                'button-class' => 'btn-success',
                                'show' => $is_consultant
                            ),
                            array('title' => trans('langCompleteSession'),
                                'url' => $urlAppend . 'modules/session/complete.php?course=' . $course_code . '&session=' . $sessionID . '&manage=1',
                                'icon' => 'fa fa-gear',
                                'button-class' => 'btn-success',
                                'show' => $is_consultant
                            ),
                            array('title' => trans('langDownloadFile'),
                                'url' => $urlAppend . 'modules/session/resource.php?course=' . $course_code . '&session=' . $sessionID . '&type=doc_upload',
                                'icon' => 'fa-solid fa-file',
                                'level' => 'secondary'
                            ),
                            array('title' => trans('langSelect') . ' ' . trans('langInsertDoc'),
                                'url' => $urlAppend . 'modules/session/resource.php?course=' . $course_code . '&session=' . $sessionID . '&type=doc',
                                'icon' => 'fa-regular fa-folder',
                                'level' => 'secondary',
                                'show' => (!is_module_disable(MODULE_ID_DOCS) && $is_consultant)
                            ),
                            array('title' => trans('langSelect') . ' ' . trans('langInsertWork'),
                                'url' => $urlAppend . 'modules/session/resource.php?course=' . $course_code . '&session=' . $sessionID . '&type=work',
                                'icon' => 'fa fa-upload',
                                'level' => 'secondary',
                                'show' => (!is_module_disable(MODULE_ID_ASSIGN) && $is_consultant)
                            ),
                            array('title' => trans('langAdd') . ' ' . trans('langInsertTcMeeting'),
                                'url' => $urlAppend . 'modules/session/resource.php?course=' . $course_code . '&session=' . $sessionID . '&type=add_tc' . '&token=' . $_SESSION['csrf_token'],
                                'class' => "add-session",
                                'data_action_class' => 'submitAdminBtn',
                                'confirm' => trans('langAdd') . ' ' . trans('langInsertTcMeeting'),
                                'icon' => 'fa-solid fa-users-rectangle',
                                'confirm_title' => trans('langAddTcSession'),
                                'confirm_button' => trans('langSubmit'),
                                'show' => (!is_module_disable(MODULE_ID_TC) && $is_consultant && is_remote_session($course_id,$sessionID))
                            ),
                        ))
                    !!}

                    @if(count($all_session) > 0)
                        <div class='col-12'>
                            <div class="card panelCard card-units px-lg-4 py-lg-3 p-3">
                                <div class='card-body p-0'>
                                    <ul class="tree-units">
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
                            <li class='list-group-item element'>{!! display_user($p->participants, false, false, '', $course_code) !!}</li>
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