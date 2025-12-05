@extends('layouts.default')

@push('head_styles')
    <style>
        td.opacity-25 {
            opacity: 0.45 !important;
        }
    </style>
@endpush

@push('head_scripts')
    <!-- About deletion -->
    <script type="text/javascript">
        function choose_user_consultant() {
            const option = document.getElementById('chooseSelection');
            if (option && option.value == '1') { // users
                $('#showUsersSelection').removeClass('d-block').addClass('d-none');
                $('#showConsultantsSelection').removeClass('d-none').addClass('d-block');
            } else if (option && option.value == '2') { // consultants
                $('#showUsersSelection').removeClass('d-none').addClass('d-block');
                $('#showConsultantsSelection').removeClass('d-block').addClass('d-none');
            } else if (option && option.value == '0') { // Neither users nor consultants
                $('#showUsersSelection').removeClass('d-block').addClass('d-none');
                $('#showConsultantsSelection').removeClass('d-block').addClass('d-none');
            }
        }

        $(document).ready(function() {
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
            
            $('#session-indexes-tb').DataTable ({
                "columns":  [
                                @if(!$is_simple_user)
                                    null, null, null, null, null, null, null, null
                                @else
                                    null, null, null, null, null, null, null
                                @endif
                            ],
                "columnDefs": [
                    { "type": "date", "targets": [1] } // or "date" if it's a date
                ],
                "sPaginationType": 'full_numbers',
                "bAutoWidth": true,
                "searchDelay": 1000,
                "order" : [[1, 'desc']],
                "oLanguage": {
                        "sLengthMenu":   "{{ trans('langDisplay') }} _MENU_ {{ trans('langResults2') }}",
                        "sZeroRecords":  "{{ trans('langNoResult') }}",
                        "sInfo":         " {{ trans('langDisplayed') }} _START_ {{ trans('langTill') }} _END_ {{ trans('langFrom2') }} _TOTAL_ {{ trans('langToralResults') }}",
                        "sInfoEmpty":    " {{ trans('langDisplayed') }} 0 {{ trans('langTill') }} 0 {{ trans('langFrom2') }} 0 {{ trans('langResults2') }}",
                        "sInfoFiltered": '',
                        "sInfoPostFix":  '',
                        "sSearch":       "{{ trans('langSearch') }}",
                        "sUrl":          '',
                        "oPaginate": {
                        "sFirst":    '&laquo;',
                            "sPrevious": '&lsaquo;',
                            "sNext":     '&rsaquo;',
                            "sLast":     '&raquo;'
                        }
                }
            });

            choose_user_consultant();

            $('#session-indexes-tb').on('page.dt', function() {
                // Find all popovers within the table and hide them
                $('#session-indexes-tb [data-bs-toggle="popover"]').each(function() {
                    $(this).popover('hide');
                });
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
                            <button type="button" class="btn-close text-reset" data-bs-dismiss="offcanvas" aria-label="{{ trans('langClose') }}"></button>
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

                                {{-- FROM COORDINATOR VIEW --}}
                                @if($is_coordinator)
                                    <select id='chooseSelection' class='form-select mb-0 mt-0' name='chooseSelection' onchange='choose_user_consultant();'>
                                        <option value='0' {{ $chooseSelectionVal == 0 ? 'selected' : '' }}>{{ trans('langSelect') }}</option>
                                        <option value='1' {{ $chooseSelectionVal == 1 ? 'selected' : '' }}>{{ trans('langConsultants') }}</option>
                                        <option value='2' {{ $chooseSelectionVal == 2 ? 'selected' : '' }}>{{ trans('langUsers') }}</option>
                                    </select>
                                    <select id='showConsultantsSelection' class='form-select mb-0 mt-0 d-none' name='forConsultant' aria-label="{{ trans('langSelect') }}">
                                        <option value='0'>{{ trans('langSelect') }}</option>
                                        @if(count($all_consultants) > 0)
                                            @foreach($all_consultants as $c)
                                                <option value='{{ $c->creator }}' {!! $for_consultant==$c->creator ? 'selected' : '' !!}>{{ $c->givenname }}&nbsp;{{ $c->surname }}</option>
                                            @endforeach
                                        @endif
                                    </select>
                                    <select id='showUsersSelection' class='form-select mb-0 mt-0 d-none' name='forUserSearch' aria-label="{{ trans('langSelect') }}">
                                        <option value='0'>{{ trans('langSelect') }}</option>
                                        @if(count($usersInCoordinatorView) > 0)
                                            @foreach($usersInCoordinatorView as $c)
                                                <option value='{{ $c->id }}' {{ $searchUserId == $c->id ? 'selected' : ''}}>{{ $c->givenname }}&nbsp;{{ $c->surname }}</option>
                                            @endforeach
                                        @endif
                                    </select>
                                {{-- FROM CONSULTANT VIEW --}}
                                @elseif ($is_consultant)
                                    <input type='hidden' name='showSelection' value='1'>
                                    <select id='showUsersSelection' class='form-select mb-0 mt-0' name='forUserSearch' aria-label="{{ trans('langSelect') }}">
                                        <option value='0'>{{ trans('langSelect') }}</option>
                                        @if(count($usersInConsultantView) > 0)
                                            @foreach($usersInConsultantView as $c)
                                                <option value='{{ $c->id }}' {{ $searchUserId == $c->id ? 'selected' : ''}}>{{ $c->givenname }}&nbsp;{{ $c->surname }}</option>
                                            @endforeach
                                        @endif
                                    </select>
                                @endif
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
                                <button class='w-75 mt-0 gap-1' type='submit'>{{ trans('langSearch') }}</button>
                            </form>
                        </div>
                    </div>

                    <div class='col-12'>
                        @if(count($current_sessions) > 0)
                            @foreach($current_sessions as $c)
                                @if($c->start < $current_time && $c->finish > $current_time)
                                    <div class='alert alert-success' data-bs-toggle='tooltip' data-bs-original-title="{{ trans('langSessionInProgress') }}">
                                        <i class='fa-solid fa-circle-info fa-lg'></i>
                                        <span class='d-flex justify-content-between align-items-start gap-3 flex-wrap w-100'>
                                            <div>
                                                <strong>{{ $c->title }}</strong>&nbsp;<span>({{ uid_to_name($c->creator) }})</span>
                                                &nbsp;<strong><i class="fa-regular fa-clock fa-lg"></i></strong>&nbsp;{{ format_locale_date(strtotime($c->start), 'short') }}
                                            </div>
                                            <div>
                                                <a href='{{ $urlAppend }}modules/session/session_space.php?course={{ $course_code }}&session={{ $c->id }}'>
                                                    {{ trans('langEnter') }}
                                                </a>
                                            </div>
                                        </span>
                                    </div>
                                @endif
                            @endforeach
                        @endif
                        @if(count($next_session) > 0)
                            @foreach($next_session as $ns)
                                <div class='alert alert-info' data-bs-toggle='tooltip' data-bs-original-title="{{ trans('langNextSession') }}">
                                    <i class='fa-solid fa-circle-info fa-lg'></i>
                                    <span class='d-flex justify-content-between align-items-start gap-3 flex-wrap w-100'>
                                        <div>
                                            <strong>{{ $ns->title }}</strong>&nbsp;<span>({{ uid_to_name($ns->creator) }})</span>
                                            &nbsp;<strong><i class="fa-regular fa-clock fa-lg"></i></strong>&nbsp;{{ format_locale_date(strtotime($ns->start), 'short') }}
                                        </div>
                                        <div>
                                            <a href='{{ $urlAppend }}modules/session/session_space.php?course={{ $course_code }}&session={{ $ns->id }}'>
                                                {{ trans('langEnter') }}
                                            </a>
                                        </div>
                                    </span>
                                </div>
                            @endforeach
                        @endif
                        @if(count($individuals_group_sessions) > 0)
                            <div class='row row-cols-1 m-auto'>
                                <div class="col-12 m-0 p-0">
                                    <table id="session-indexes-tb" class="table-default" style="border-collapse: separate;">
                                        <thead>
                                            <tr>
                                                <th>{{ trans('langTitle') }}</th>
                                                <th>{{ trans('langDate') }}</th>
                                                <th>{{ trans('langSSession') }}</th>
                                                <th>{{ trans('langTypeRemote') }}</th>
                                                <th>{{ trans('langStatement') }}</th>
                                                <th>{{ trans('langUsers') }}</th>
                                                <th>{{ trans('langEnter') }}</th>
                                                @if(!$is_simple_user)
                                                <th></th>
                                                @endif
                                            </tr>
                                        </thead>
                                        <tbody>
                                            @foreach($individuals_group_sessions as $s)
                                                <tr style='border-bottom: 0px !important;'>
                                                    <td class='@if(!$s->visible) opacity-25 @endif'>
                                                        <p class='mb-2' style='line-height:16px;'>{{ $s->title }}</p>
                                                        <p>{!! display_user($s->creator) !!}</p>
                                                    </td>
                                                    <td class='@if(!$s->visible) opacity-25 @endif'>
                                                        <p>{{ format_locale_date(strtotime($s->start), 'short', false, 'dd/MM/yyyy') }}</p>
                                                        <p>
                                                            <small>
                                                                {{ format_locale_date(strtotime($s->start), null, true, 'HH:mm') }}
                                                                &nbsp;-&nbsp;
                                                                {{ format_locale_date(strtotime($s->finish), null, true, 'HH:mm') }}
                                                            </small>
                                                        </p>
                                                    </td>
                                                    <td class='@if(!$s->visible) opacity-25 @endif'>
                                                        @if($s->type=='one')
                                                            <div class='text-nowrap'>{{ trans('langIndividualS') }}</div>
                                                        @else
                                                            <div class='text-nowrap'>{{ trans('langGroupS') }}</div>
                                                        @endif
                                                    </td>
                                                    <td class='@if(!$s->visible) opacity-25 @endif'>
                                                        @if($s->type_remote)
                                                            <div class='text-nowrap'>{{ trans('langRemote') }}</div>
                                                        @else
                                                            <div class='text-nowrap'>{{ trans('langNotRemote') }}</div>
                                                        @endif
                                                    </td>
                                                    <td class='@if(!$s->visible) opacity-25 @endif'>
                                                        <div class='session-info'>
                                                            <ul class='list-group list-group-flush'>
                                                                <li class='list-group-item list-group-item-action secondary d-flex justify-content-between align-items-center gap-3 flex-wrap p-0'>
                                                                    <div class='secondary-text' style='line-height: 16px;'>
                                                                        @if($s->start < $current_time && $current_time < $s->finish)
                                                                            <div class="d-flex align-items-start gap-2">
                                                                                <div class="spinner-grow text-success" role="status" style='width:20px; height:15px;'>
                                                                                    <span class="visually-hidden"></span>
                                                                                </div>
                                                                                {{ trans('langInProgress') }}
                                                                            </div>
                                                                        @elseif($current_time < $s->start)
                                                                            <div class="d-flex align-items-start gap-2">
                                                                                <div class="spinner-border text-warning" role="status" style='width:20px; height:15px;'>
                                                                                    <span class="visually-hidden"></span>
                                                                                </div>
                                                                                {{ trans('langSessionHasNotStarted') }}
                                                                            </div>
                                                                        @else
                                                                            <span class='text-danger TextBold' style='line-height: 16px;'>{{ trans('langSessionHasExpired') }}</span>
                                                                        @endif
                                                                    </div>
                                                                </li>
                                                            </ul>
                                                        </div>
                                                    </td>
                                                    <td class='@if(!$s->visible) opacity-25 @endif'>
                                                        <button type="button" class="btn submitAdminBtn text-nowrap" 
                                                                data-bs-container="body" 
                                                                data-bs-toggle="popover" 
                                                                data-bs-placement="top"
                                                                data-bs-html="true"
                                                                data-bs-content="
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
                                                                ">
                                                            {{ trans('langUsers') }}
                                                        </button>
                                                    </td>
                                                    <td class='@if(!$s->visible) opacity-25 @endif'>
                                                        <div>
                                                            <a class="btn successAdminBtn
                                                                        @if(!$is_coordinator && !$is_consultant && $s->display == 'not_visible') pe-none opacity-help @endif
                                                                        @if(!$is_coordinator && !$is_consultant && !$s->is_accepted_user) d-none @endif text-nowrap"
                                                                href='{{ $urlAppend }}modules/session/session_space.php?course={{ $course_code }}&session={{ $s->id }}'>
                                                                {{ trans('langEnter') }}
                                                            </a>
                                                            @if(!$is_coordinator && !$is_consultant && !$s->is_accepted_user && $s->consent && get_config('enable_user_consent'))
                                                                <a data-id='{{ $s->id }}' class="btn submitAdminBtnDefault do-acceptance" data-bs-toggle="modal" data-bs-target="#RegistrationInSession">
                                                                    {{ trans('langConfirmParticipation') }}
                                                                </a>
                                                            @endif
                                                        </div>    
                                                    </td>
                                                    @if(!$is_simple_user)
                                                        <td>
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
                                                            
                                                            @if (!$s->visible) 
                                                                <div class='mt-3'>
                                                                    <span class='Accent-200-bg p-2 rounded-2' data-bs-toggle='tooltip' title="{{ trans('langNotDisplay') }}">
                                                                        <i class="fa-solid fa-eye-low-vision fa-lg text-white"></i>
                                                                    </span>
                                                                </div>
                                                            @endif
                                                        </td>
                                                    @endif
                                                </tr>
                                            @endforeach
                                        </tbody>
                                    </table>
                                </div>
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
                        <div class="modal-title-default text-center mb-0 mt-2" id="RegistrationInSessionLabel">{!! trans('langParticipate') !!}</div>
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
                        {{ trans('langParticipate') }}
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
