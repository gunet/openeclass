@extends('layouts.default')

@push('head_scripts')
    <!-- About deletion -->
    <script>
        $(function() {
            $(document).on('click', '.delete-session', function(e){
                e.preventDefault();
                var sessionID = $(this).attr('data-id');
                document.getElementById("deleteSession").value = sessionID;
            });
            $(document).on('click', '.leave-session', function(e){
                e.preventDefault();
                var sessionLeaveID = $(this).attr('data-id');
                document.getElementById("leaveSession").value = sessionLeaveID;
            });
            $(document).on('click', '.do-acceptance', function(e){
                e.preventDefault();
                var session_id = $(this).attr('data-id');
                document.getElementById("about_session").value = session_id;
            });
        });
    </script>
@endpush

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
                    
                    {!! $action_bar !!}

                    @include('layouts.partials.show_alert') 

                    <div class='col-12 d-flex justify-content-between align-items-lg-center align-items-start gap-3 flex-wrap mb-4'>
                        <div class='control-label-notes mt-lg-0 mt-2'>{{ trans('langSearch') }}:</div>
                        <div class='flex-fill'>
                            <form class='d-flex gap-3 flex-wrap flex-lg-nowrap' method="post" action="{{ $_SERVER['SCRIPT_NAME'] }}?course={{ $course_code }}">
                                <select class='form-select mb-0 mt-0' name='remoteType' aria-label="{{ trans('langSelect') }}">
                                    <option value='-1'>{{ trans('langALLSessions') }}</option>
                                    <option value='0' {!! $remoteType==0 ? 'selected' : '' !!}>{{ trans('langNotRemote') }}</option>
                                    <option value='1' {!! $remoteType==1 ? 'selected' : '' !!}>{{ trans('langRemote') }}</option>
                                </select>
                                <select class='form-select mb-0 mt-0' name='sessionType' aria-label="{{ trans('langSelect') }}">
                                    <option value='other'>{{ trans('langALLSessions') }}</option>
                                    <option value='one' {!! $sessionType=='one' ? 'selected' : '' !!}>{{ trans('langIndividualS') }}</option>
                                    <option value='group' {!! $sessionType=='group' ? 'selected' : '' !!}>{{ trans('langGroupS') }}</option>
                                </select>
                                <button class='w-50 mt-0 gap-1' type='submit'><i class="fa-solid fa-magnifying-glass"></i>{{ trans('langSearch') }}</button>
                            </form>
                        </div>
                    </div>

                    <div class='col-12'>
                        @if(count($individuals_group_sessions) > 0)
                            <div class='row row-cols-1 m-auto'>
                                @foreach($individuals_group_sessions as $s)
                                    <div class='col px-0 mb-4'>
                                        <div class='card panelCard border-card-left-default px-lg-4 py-lg-3'>
                                            <div class='card-header border-0 d-flex justify-content-between align-items-center gap-3 flex-wrap'>
                                                <h3>{{ $s->title }}</h3>
                                                <div>
                                                    @if(!$is_simple_user)
                                                        {!! action_button(array(
                                                            array('title' => trans('langEditUnitSection'),
                                                                    'url' => $urlAppend . "modules/session/edit.php?course=" . $course_code . "&session=" . $s->id,
                                                                    'icon-class' => "edit-session",
                                                                    'icon' => 'fa-edit',
                                                                    'show' => ($is_editor || !$is_course_reviewer)),
                                                            array('title' => trans('langDelete'),
                                                                    'url' => "#",
                                                                    'icon-class' => "delete-session",
                                                                    'icon-extra' => "data-id='{$s->id}' data-bs-toggle='modal' data-bs-target='#SessionDelete'",
                                                                    'icon' => 'fa-xmark',
                                                                    'show' => ($is_editor || !$is_course_reviewer))
                                                            )
                                                        ) !!}
                                                    @endif
                                                </div>
                                            </div>
                                            <div class='card-body'>
                                                <div class='d-flex justify-content-between align-items-center gap-3 flex-wrap'>
                                                    <div class='session-info'>
                                                        <ul class='list-group list-group-flush'>
                                                            <li class='list-group-item list-group-item-action secondary d-flex justify-content-between align-items-center gap-3 flex-wrap'>
                                                                <div>{{ trans('langStart') }}:</div>
                                                                <div class='secondary-text'>{{ format_locale_date(strtotime($s->start), 'short') }}</div>
                                                            </li>
                                                            <li class='list-group-item list-group-item-action secondary d-flex justify-content-between align-items-center gap-3 flex-wrap'>
                                                                <div>{{ trans('langFinish') }}:</div>
                                                                <div class='secondary-text'>{{ format_locale_date(strtotime($s->finish), 'short') }}</div>
                                                            </li>
                                                        </ul>
                                                    </div>
                                                    <div class='session-info'>
                                                        <ul class='list-group list-group-flush'>
                                                            <li class='list-group-item list-group-item-action secondary d-flex justify-content-between align-items-center gap-3 flex-wrap'>
                                                                <div>{{ trans('langSSession') }}:</div>
                                                                <div class='secondary-text'>
                                                                    @if($s->type=='one')
                                                                        {{ trans('langIndividualS') }}
                                                                    @else
                                                                        {{ trans('langGroupS') }}
                                                                    @endif
                                                                </div>
                                                            </li>
                                                            <li class='list-group-item list-group-item-action secondary d-flex justify-content-between align-items-center gap-3 flex-wrap'>
                                                                <div>{{ trans('langTypeRemote') }}:</div>
                                                                <div class='secondary-text'>
                                                                    @if($s->type_remote)
                                                                        {{ trans('langRemote') }}
                                                                    @else
                                                                        {{ trans('langNotRemote') }}
                                                                    @endif
                                                                </div>
                                                            </li>
                                                        </ul>
                                                    </div>
                                                    <div class='session-info'>
                                                        <ul class='list-group list-group-flush'>
                                                            <li class='list-group-item list-group-item-action secondary d-flex justify-content-between align-items-center gap-3 flex-wrap'>
                                                                <div>{{ trans('langStatement') }}:</div>
                                                                <div class='secondary-text'>
                                                                    @if($s->start < $current_time && $current_time < $s->finish)
                                                                        <div class="d-flex align-items-center gap-2">
                                                                            <div class="spinner-grow text-success" role="status" style='width:20px; height:20px;'>
                                                                                <span class="visually-hidden">Loading...</span>
                                                                            </div>
                                                                            {{ trans('langInProgress') }}
                                                                        </div>
                                                                    @elseif($current_time < $s->start)
                                                                        <div class="d-flex align-items-center gap-2">
                                                                            <div class="spinner-border text-warning" role="status" style='width:20px; height:20px;'>
                                                                                <span class="visually-hidden">Loading...</span>
                                                                            </div>
                                                                            {{ trans('langSessionHasNotStarted') }}
                                                                        </div>
                                                                        
                                                                    @else
                                                                        <span class='text-danger TextBold'>{{ trans('langSessionHasExpired') }}</span>
                                                                    @endif
                                                                </div>
                                                            </li>
                                                            <li class='list-group-item list-group-item-action secondary d-flex justify-content-between align-items-center gap-3 flex-wrap'>
                                                                <div>{{ trans('langVisible') }}:</div>
                                                                <div class='secondary-text'>
                                                                    @if(!$s->visible)
                                                                        {{ trans('langNo')}}
                                                                    @else
                                                                        {{ trans('langYes')}}
                                                                    @endif
                                                                </div>
                                                            </li>
    
                                                        </ul>
                                                    </div>
                                                </div>
                                                <div class='d-flex justify-content-between align-items-center gap-3 flex-wrap mt-4'>
                                                    <div>
                                                        <p class='TextBold'>{{ trans('langConsultant') }}:&nbsp;<span>{{ $s->consultant }}</span></p>
                                                        <p class='TextBold'>{{ trans('langStudents') }}:&nbsp;
                                                            <a class="link-color" data-bs-toggle="collapse" href="#students{{ $s->id }}" role="button" aria-expanded="false" aria-controls="students{{ $s->id }}">
                                                                {{ trans('langViewShow') }}
                                                            </a>
                                                        </p>
                                                    </div>
                                                    <div>
                                                        <a class="btn successAdminBtn 
                                                                    @if(!$is_coordinator && !$is_consultant && $s->display == 'not_visible') pe-none opacity-help @endif
                                                                    @if(!$is_coordinator && !$is_consultant && !$s->is_accepted_user) d-none @endif" 
                                                            href='{{ $urlAppend }}modules/session/session_space.php?course={{ $course_code }}&session={{ $s->id }}'>
                                                            {{ trans('langEnter') }}
                                                        </a>
                                                        @if(!$is_coordinator && !$is_consultant && !$s->is_accepted_user)
                                                            <a data-id='{{ $s->id }}' class="btn submitAdminBtnDefault do-acceptance" data-bs-toggle="modal" data-bs-target="#RegistrationInSession">
                                                                {{ trans('langRegister') }}
                                                            </a>
                                                        @endif
                                                    </div>
                                                </div>
                                                <div class="col-12 mt-4 collapse" id="students{{ $s->id }}">
                                                    <div class="card card-body">
                                                        @if(count($s->user_participant) > 0)
                                                            <div class='d-flex justify-content-start align-items-center gap-3 flex-wrap'>
                                                                @foreach($s->user_participant as $user)
                                                                    <div>{!! display_user($user) !!}</div>
                                                                @endforeach
                                                            </div>
                                                        @else
                                                            <div class='alert alert-warning'>
                                                                <i class='fa-solid fa-triangle-exclamation fa-lg'></i>
                                                                <span>{{ trans('langNotExistUsers') }}</span>
                                                            </div>
                                                        @endif
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                @endforeach
                            </div>
                        @else
                            <div class='alert alert-warning'>
                                <i class='fa-solid fa-triangle-exclamation fa-lg'></i>
                                <span>{{ trans('langNoInfoAvailable') }}</span>
                            </div>
                        @endif
                    </div>

                </div>
            </div>

        </div>
    
    </div>
</div>

<div class='modal fade' id='RegistrationInSession' tabindex='-1' aria-labelledby='RegistrationInSessionLabel' aria-hidden='true'>
    <form method='post' action="{{ $_SERVER['SCRIPT_NAME'] }}?course={{ $course_code }}">
        <div class='modal-dialog modal-md'>
            <div class='modal-content'>
                <div class='modal-header'>
                    <div class='modal-title'>
                        <div class='icon-modal-default'><i class='fa-solid fa-comments fa-xl Neutral-500-cl'></i></div>
                        <div class="modal-title-default text-center mb-0 mt-2" id="RegistrationInSessionLabel">{!! trans('langRegistration') !!}</div>
                    </div>
                </div>
                <div class='modal-body text-start'>
                    <p>{{ trans('langContinueToRegistrationSession') }}</p>
                    <input type='hidden' name='about_session' id='about_session'>
                    <input type='hidden' name='token' value="{{ $_SESSION['csrf_token'] }}">
                </div>
                <div class='modal-footer d-flex justify-content-center align-items-center'>
                    <a class="btn cancelAdminBtn" href="" data-bs-dismiss="modal">{{ trans('langCancel') }}</a>
                    <button type='submit' class="btn submitAdminBtn" name="user_registration">
                        {{ trans('langRegister') }}
                    </button>
                </div>
            </div>
        </div>
    </form>
</div>

<div class='modal fade' id='SessionDelete' tabindex='-1' aria-labelledby='SessionDeleteLabel' aria-hidden='true'>
    <form method='post' action="{{ $_SERVER['SCRIPT_NAME'] }}?course={{ $course_code }}">
        <div class='modal-dialog modal-md'>
            <div class='modal-content'>
                <div class='modal-header'>
                    <div class='modal-title'>
                        <div class='icon-modal-default'><i class='fa-regular fa-trash-can fa-xl Accent-200-cl'></i></div>
                        <div class="modal-title-default text-center mb-0 mt-2" id="SessionDeleteLabel">{!! trans('langDelete') !!}</div>
                    </div>
                </div>
                <div class='modal-body text-center'>
                    {{ trans('langContinueToDelSession') }}
                    <input id="deleteSession" type='hidden' name='session_id'>
                </div>
                <div class='modal-footer d-flex justify-content-center align-items-center'>
                    <a class="btn cancelAdminBtn" href="" data-bs-dismiss="modal">{{ trans('langCancel') }}</a>
                    <button type='submit' class="btn deleteAdminBtn" name="delete_session">
                        {{ trans('langDelete') }}
                    </button>
                </div>
            </div>
        </div>
    </form>
</div>

<div class='modal fade' id='SessionLeave' tabindex='-1' aria-labelledby='SessionLeaveLabel' aria-hidden='true'>
    <form method='post' action="{{ $_SERVER['SCRIPT_NAME'] }}?course={{ $course_code }}">
        <div class='modal-dialog modal-md'>
            <div class='modal-content'>
                <div class='modal-header'>
                    <div class='modal-title'>
                        <div class='icon-modal-default'><i class='fa-regular fa-trash-can fa-xl Accent-200-cl'></i></div>
                        <div class="modal-title-default text-center mb-0 mt-2" id="SessionLeaveLabel">{!! trans('langLeaveSession') !!}</div>
                    </div>
                </div>
                <div class='modal-body text-center'>
                    {{ trans('langContinueToLeaveSession') }}
                    <input id="leaveSession" type='hidden' name='session_leave_id'>
                </div>
                <div class='modal-footer d-flex justify-content-center align-items-center'>
                    <a class="btn cancelAdminBtn" href="" data-bs-dismiss="modal">{{ trans('langCancel') }}</a>
                    <button type='submit' class="btn deleteAdminBtn" name="leave_session">
                        {{ trans('langDelete') }}
                    </button>
                </div>
            </div>
        </div>
    </form>
</div>

@endsection
