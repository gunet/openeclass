@extends('layouts.default')

@push('head_scripts')
    <script type="text/javascript">
        $(document).ready(function() {
            $('#creators').on('click', function(e){
                e.preventDefault();
                $('#selectedConsultant').val($(this).val());
            });
            $('#openSessionCal').on('click', function(e){
                e.preventDefault();
                var currentSession = $('#current_session').val();
                var consultant_choosen = $('#selectedConsultant').val();
                $('#calendarAddSessionDate').fullCalendar('destroy');
                var calendar = $('#calendarAddSessionDate').fullCalendar({
                    header:{
                        left: 'prev,next ',
                        center: 'title',
                        right: ''
                    },
                    defaultView: 'agendaWeek',
                    slotDuration: '00:05',
                    minTime: '08:00:00',
                    maxTime: '23:55:00',
                    contentHeight:"auto",
                    editable: true,
                    selectable: true,
                    allDaySlot: false,
                    displayEventTime: true,
                    events: "{{ $urlAppend }}modules/session/disabled_session_slots.php?course={{ $course_id }}&show_sessions=true&edit=true&session={{ $sessionID }}&from_coordinator={{ $tmp_coordinator }}&selectedConsultant="+consultant_choosen,
                
                    eventRender: function( event, element, view ) {
                        var title = element.find( '.fc-title' );
                        title.html( title.text() );
                        var timee = element.find( '.fc-time span' );
                        element.popover({
                            title: '</br>'+timee[0].innerText+'</br>'+event.title,
                            trigger: 'hover',
                            placement: 'top',
                            container: 'body',
                            html: true,
                            sanitize: false
                        });
                    },

                    select: function(start, end) {
                        $('.popover').each(function () {
                            $(this).removeClass('show');
                        });
                        var startDay =  moment(start).format('DD');
                        var endDay = moment(end).format('DD');
                        if(parseInt(startDay)==parseInt(endDay)){
                            var CurrentDateTime = moment().format('YYYY-MM-DD HH:mm');
                            var StartDateTime = moment(start).format('YYYY-MM-DD HH:mm');
                            if(StartDateTime >= CurrentDateTime){
                                var day_start = moment(start).format('YYYY-MM-DD HH:mm');
                                var day_end = moment(end).format('YYYY-MM-DD HH:mm');
                                var starttime = moment(start).format('dddd, Do MMMM YYYY, HH:mm');
                                var endtime = moment(end).format('HH:mm');
                                var mywhen = starttime + ' - ' + endtime;
                                $('#startTimeTmp').val(day_start);
                                $('#endTimeTmp').val(day_end);
                                $('#whenTmp').val(mywhen);
                                $('#createEventSession #when').text(mywhen);
                                $('#createEventSession').modal('toggle');
                            }else{
                                alert("{{ js_escape(trans('langDateHasExpired')) }}");
                            }
                        }else{
                            alert("{{ js_escape(trans('langChooseDayAgain')) }}");
                        }
                    },

                    eventResize: function(event) {
                        $('.popover').each(function () {
                            $(this).removeClass('show');
                        });
                        if(currentSession == event.id){
                            var startDay =  moment(event.start).format('DD');
                            var endDay = moment(event.end).format('DD');
                            if(parseInt(startDay)==parseInt(endDay)){
                                var CurrentDateTime = moment().format('YYYY-MM-DD HH:mm');
                                var StartDateTime = moment(event.start).format('YYYY-MM-DD HH:mm');
                                if(StartDateTime >= CurrentDateTime){
                                    var day_start = moment(event.start).format('YYYY-MM-DD HH:mm');
                                    var day_end = moment(event.end).format('YYYY-MM-DD HH:mm');
                                    var starttime = moment(event.start).format('dddd, Do MMMM YYYY, HH:mm');
                                    var endtime = moment(event.end).format('HH:mm');
                                    var mywhen = starttime + ' - ' + endtime;
                                    $('#startTimeTmp').val(day_start);
                                    $('#endTimeTmp').val(day_end);
                                    $('#whenTmp').val(mywhen);
                                    $('#createEventSession #when').text(mywhen);
                                    $('#createEventSession').modal('toggle');
                                }else{
                                    alert("{{ js_escape(trans('langDateHasExpired')) }}");
                                }
                            }else{
                                alert("{{ js_escape(trans('langChooseDayAgain')) }}");
                            }
                        }else{
                            alert("{{ js_escape(trans('langChooseOtherSession')) }}");
                            $('#openSessionCal').trigger('click');
                        }

                    },

                    eventDrop: function(event){
                        $('.popover').each(function () {
                            $(this).removeClass('show');
                        });
                        if(currentSession == event.id){
                            var startDay =  moment(event.start).format('DD');
                            var endDay = moment(event.end).format('DD');
                            if(parseInt(startDay)==parseInt(endDay)){
                                var CurrentDateTime = moment().format('YYYY-MM-DD HH:mm');
                                var StartDateTime = moment(event.start).format('YYYY-MM-DD HH:mm');
                                if(StartDateTime >= CurrentDateTime){
                                    var day_start = moment(event.start).format('YYYY-MM-DD HH:mm');
                                    var day_end = moment(event.end).format('YYYY-MM-DD HH:mm');
                                    var starttime = moment(event.start).format('dddd, Do MMMM YYYY, HH:mm');
                                    var endtime = moment(event.end).format('HH:mm');
                                    var mywhen = starttime + ' - ' + endtime;
                                    $('#startTimeTmp').val(day_start);
                                    $('#endTimeTmp').val(day_end);
                                    $('#whenTmp').val(mywhen);
                                    $('#createEventSession #when').text(mywhen);
                                    $('#createEventSession').modal('toggle');
                                }else{
                                    alert("{{ js_escape(trans('langDateHasExpired')) }}");
                                    $('#openSessionCal').trigger('click');
                                }
                            }else{
                                alert("{{ js_escape(trans('langChooseDayAgain')) }}");
                            }
                        }else{
                            alert("{{ js_escape(trans('langChooseOtherSession')) }}");
                            $('#openSessionCal').trigger('click');
                        }
                    },

                    eventClick: function(event) {
                        $('.popover').each(function () {
                            $(this).removeClass('show');
                        });
                        if(event.className == 'exist_event_session'){
                            return false;
                        }
                    }
 
                });

                // $('#calendarAddSessionDate').removeClass('d-none');
                // $('#calendarAddSessionDate').removeClass('d-block');

                $('.fc-next-button').trigger('click');
                $('.fc-prev-button').trigger('click');
            });

            $('#addDateTimeBtn').on('click', function(e){
                e.preventDefault();
                $('#startTime').val(document.getElementById('startTimeTmp').value);
                $('#endTime').val(document.getElementById('endTimeTmp').value);
                $('#startDateValue').val(document.getElementById('whenTmp').value);
                $("#createEventSession").modal('hide');
                $('#staticDateTimeSession').modal('hide');
            });

            if ($('#one_session').is(':checked')){
                $('#select_one_session').removeClass('d-none');
                $('#select_one_session').addClass('d-block');
                $('#select_group_session').removeClass('d-block');
                $('#select_group_session').addClass('d-none');
            }

            if ($('#group_session').is(':checked')){
                $('#select_users_group_session').select2();
                $('#select_one_session').removeClass('d-block');
                $('#select_one_session').addClass('d-none');
                $('#select_group_session').removeClass('d-none');
                $('#select_group_session').addClass('d-block');
            }

            $('#one_session').on('change',function(){
                $('#select_one_session').removeClass('d-none');
                $('#select_one_session').addClass('d-block');
                $('#select_group_session').removeClass('d-block');
                $('#select_group_session').addClass('d-none');
            });
            
            $('#group_session').on('change',function(){
                $('#select_users_group_session').select2();
                $('#select_one_session').removeClass('d-block');
                $('#select_one_session').addClass('d-none');
                $('#select_group_session').removeClass('d-none');
                $('#select_group_session').addClass('d-block');
            });


            $('#selectAll').click(function(e) {
                e.preventDefault();
                var stringVal = [];
                $('#select_users_group_session').find('option').each(function(){
                    stringVal.push($(this).val());
                });
                $('#select_users_group_session').val(stringVal).trigger('change');
            });
            $('#removeAll').click(function(e) {
                e.preventDefault();
                var stringVal = [];
                $('#select_users_group_session').val(stringVal).trigger('change');
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

                    <div class='d-lg-flex gap-4 mt-4'>
                        <div class='flex-grow-1'>
                            <div class='form-wrapper form-edit rounded'>
                                <form role='form' class='form-horizontal' action="{{ $_SERVER['SCRIPT_NAME'] }}?course={{ $course_code }}&session={{ $sessionID }}" method='post'>
                                    <fieldset>
                                        <legend class='mb-0' aria-label="{{ trans('langForm') }}"></legend>
                                        <input type='hidden' id='current_session' value='{{ $sessionID }}'>
                                        <div class="form-group">
                                            <label for='creators' class='control-label-notes'>{{ trans('langResponsibleOfSession') }} <span class='asterisk Accent-200-cl'>(*)</span></label>
                                            <select class='form-select' name='creators' id='creators'>
                                                @if($is_coordinator)
                                                    <option value=''>
                                                        {{ trans('langSelectConsultant') }}
                                                    </option>
                                                    @foreach($creators as $c)
                                                        <option value='{{ $c->user_id }}' {!! $c->user_id == $creator ? 'selected' : '' !!}>
                                                            {{ $c->givenname }}&nbsp;{{ $c->surname }}
                                                        </option>
                                                    @endforeach
                                                @else
                                                    @foreach($creators as $c)
                                                        <option value='{{ $c->id }}'>
                                                            {{ $c->givenname }}&nbsp;{{ $c->surname }}
                                                        </option>
                                                    @endforeach
                                                @endif
                                            </select>
                                            @if(Session::getError('creators'))
                                                <span class='help-block Accent-200-cl'>{!! Session::getError('creators') !!}</span>
                                            @endif
                                            <input type='hidden' id='selectedConsultant' value="@if($is_coordinator) 0 @else {{ $uid }} @endif">
                                        </div>

                                        <div class="form-group mt-4">
                                            <label for='title' class='col-12 control-label-notes'>{{ trans('langTitle')}} <span class='asterisk Accent-200-cl'>(*)</span></label>
                                            <div class='col-12'>
                                                <input id='title' type='text' name='title' class='form-control' value='{{ $title }}'>
                                                @if(Session::getError('title'))
                                                    <span class='help-block Accent-200-cl'>{!! Session::getError('title') !!}</span>
                                                @endif
                                            </div>
                                        </div>

                                        <div class='form-group mt-4'>
                                            <label for='comments' class='col-12 control-label-notes'>{{ trans('langDescription')}}</label>
                                            {!! $comments !!}
                                        </div>

                                        <div class='form-group mt-4'>
                                            <div class="radio">
                                                <label>
                                                    <input type='radio' name='session_type' value='one' id='one_session' {!! $session_type=='one' ? 'checked' : '' !!}>
                                                    {{ trans('langIndividualSession') }}
                                                </label>
                                            </div>
                                            <div class="radio mt-2">
                                                <label>
                                                    <input type='radio' name='session_type' value='group' id='group_session' {!! $session_type=='group' ? 'checked' : '' !!}>
                                                    {{ trans('langGroupSession') }}
                                                </label>
                                            </div>

                                            <p class='control-label-notes mb-0 mt-3'>{{ trans('langSessionParticipants') }} <span class='asterisk Accent-200-cl'>(*)</span></p>
                                            <div id='select_one_session' class='d-block mt-1'>
                                                <select name='one_participant' class='form-select' aria-label="{{ trans('langSessionParticipants') }}">
                                                    <option value=''>{{ trans('langSelectUser') }}</option>
                                                    @foreach($simple_users as $u)
                                                        <option value='{{ $u->user_id }}' {!! in_array($u->user_id,$participants_arr) ? 'selected' : '' !!}>
                                                            {{ $u->givenname }}&nbsp;{{ $u->surname }}
                                                        </option>
                                                    @endforeach
                                                </select>
                                                @if(Session::getError('one_participant'))
                                                    <span class='help-block Accent-200-cl'>{!! Session::getError('one_participant') !!}</span>
                                                @endif
                                            </div>
                                            <div id='select_group_session' class='d-none mt-1'>
                                                <select aria-label="{{ trans('langSessionParticipants') }}" id='select_users_group_session' name='many_participants[]' class='form-select' multiple>
                                                    @foreach($simple_users as $u)
                                                        <option value='{{ $u->user_id }}' {!! in_array($u->user_id,$participants_arr) ? 'selected' : '' !!}>
                                                            {{ $u->givenname }}&nbsp;{{ $u->surname }}
                                                        </option>
                                                    @endforeach
                                                </select>
                                                <a href='#' id='selectAll'>{{ trans('langJQCheckAll') }}</a> | <a href='#' id='removeAll'>{{ trans('langJQUncheckAll') }}</a>
                                                @if(Session::getError('many_participants'))
                                                    <span class='help-block Accent-200-cl'>{!! Session::getError('many_participants') !!}</span>
                                                @endif
                                            </div>
                                            <small>({{ trans('langInfoAboutDelUser') }})</small>
                                        </div>

                                        <div class='form-group mt-4'>
                                            <p class='control-label-notes mb-2'>{{ trans('langStartEndSessionDateTime') }} <span class='asterisk Accent-200-cl'>(*)</span></p>
                                            <div class="input-group mb-3 rounded-2 border-0 gap-2">
                                                <span class="input-group-text p-0 border-0 bg-transparent" id="start-end-datetime-session">
                                                    <a type="button" class="btn submitAdminBtn d-inline-flex gap-1 rounded-2" 
                                                        data-bs-toggle="modal" data-bs-target="#staticDateTimeSession" id='openSessionCal' aria-label="{{ trans('langStartEndSessionDateTime') }}">
                                                        <i class='fa-solid fa-calendar'></i>
                                                    </a>
                                                </span>
                                                <input aria-label="{{ trans('langStartEndSessionDateTime') }}" id='startDateValue' type="text" class="form-control mt-0 pe-none rounded-2" aria-describedby="start-end-datetime-session" value='{{ $start }} -- {{ $finish_text }}'>
                                                <input type="hidden" id="startTimeTmp">
                                                <input type="hidden" id="endTimeTmp">
                                                <input type="hidden" id="whenTmp">
                                                <input type="hidden" id="startTime" name='start_session' value='{{ $start }}'>
                                                <input type="hidden" id="endTime" name='end_session' value='{{ $finish }}'>
                                            </div>
                                            @if(Session::getError('start_session') or Session::getError('end_session'))
                                                @if(Session::getError('start_session'))
                                                    <span class='help-block Accent-200-cl'>{!! Session::getError('start_session') !!}</span>
                                                @else
                                                    <span class='help-block Accent-200-cl'>{!! Session::getError('end_session') !!}</span>
                                                @endif
                                            @endif
                                        </div>

                                        <div class='form-group mt-4'>
                                            <label for='typeRemoteId' class='control-label-notes mb-0 mt-3'>{{ trans('langTypeRemote') }} <span class='asterisk Accent-200-cl'>(*)</span></label>
                                            <select class='form-select' name='type_remote' id='typeRemoteId'>
                                                <option value='0' {!! $type_remote==0 ? 'selected' : '' !!} {{ $tc_disabled }}>{{ trans('langNotRemote') }}</option>
                                                <option value='1' {!! $type_remote==1 ? 'selected' : '' !!} {{ $meeting_disabled }}>{{ trans('langRemote') }}</option>
                                            </select>
                                            @if($tc_disabled)
                                                <span class='help-block Accent-200-cl'>{{ trans('langInfoIfTcExists') }}</span>
                                            @endif
                                            @if($meeting_disabled)
                                                <span class='help-block Accent-200-cl'>{{ trans('langInfoIfMeetingExists') }}</span>
                                            @endif
                                        </div>

                                        <div class='form-group mt-4'>
                                            <div class='checkbox'>
                                                <label class='label-container' aria-label="{{ trans('langVisible') }}">
                                                    <input type='checkbox' name='session_visible' {!! $visible==1 ? 'checked' : '' !!}>
                                                    <span class='checkmark'></span>
                                                    {{ trans('langVisibleToUser') }}
                                                </label>
                                            </div>
                                        </div>

                                        <div class='form-group mt-4'>
                                            <div class='col-12'>
                                                <div class='checkbox'>
                                                    <label class='label-container' aria-label="{{ trans('langWithConsent') }}">
                                                        <input type='checkbox' name='with_consent' {!! $withConsent==1 ? 'checked' : '' !!}>
                                                        <span class='checkmark'></span>
                                                        {{ trans('langWithConsent')}}
                                                    </label>
                                                </div>
                                                <small>({{ trans('langInfoWithConsent') }})</small>
                                            </div>
                                        </div>

                                        {!! generate_csrf_token_form_field() !!}    

                                        <div class='form-group mt-5'>
                                            <div class='col-12 d-flex justify-content-end aling-items-center'>
                                                <input class='btn submitAdminBtn' type='submit' name='modify' value='{{ trans('langModify') }}'>
                                            </div>
                                        </div>

                                    </fieldset>
                                </form>
                            </div>
                        </div>
                        <div class='d-none d-lg-block'>
                            <img class='form-image-modules' src='{!! get_form_image() !!}' alt="{{ trans('langImgFormsDes') }}">
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>




<!-- Show Calendar for adding session datetime -->
<div class="modal fade" id="staticDateTimeSession" tabindex="-1" aria-labelledby="staticDateTimeSessionLabel" aria-hidden="true">
    <div class="modal-dialog modal-fullscreen mt-0">
        <div class="modal-content">
            <div class="modal-header">
                <div class="modal-title" id="staticDateTimeSessionLabel">{{ trans('langStartEndSessionDateTime') }}</div>
                <button type="button" class="close" data-bs-dismiss="modal" aria-label="{{ trans('langClose') }}"></button>
            </div>
            <div class="modal-body">
                <div class='alert alert-info'>
                    <i class='fa-solid fa-circle-info fa-lg'></i>
                    <span>@if($is_coordinator) {!! trans('langInfoEditSessionCoordinator') !!} @else {!! trans('langInfoEditSession') !!} @endif</span>
                </div>
                @if($is_coordinator)
                    @if(count($view_sessions) > 0)
                        <div class='panel'>
                            <div class='panel-group group-section' id='accordion' role='tablist' aria-multiselectable='true'>
                                <ul class="list-group list-group-flush mt-4">
                                    <li class="list-group-item px-0 mb-4 bg-transparent">
                                        <a class='accordion-btn d-flex justify-content-start align-items-start' role='button' data-bs-toggle='collapse' href='#showSession' aria-expanded='true'>
                                            <span class='fa-solid fa-chevron-down'></span>
                                            {{ trans('langViewAllSession') }}
                                        </a>

                                        <div id='showSession' class='panel-collapse accordion-collapse collapse show border-0 rounded-0' role='tabpanel' data-bs-parent='#accordion'>
                                            <div class='panel-body bg-transparent Neutral-900-cl px-4'>
                                                <ul class='list-group list-group-flush'>
                                                    @foreach($view_sessions as $s)
                                                        <li class='list-group-item element'>
                                                            <div>{{ $s->givenname }}&nbsp;{{ $s->surname }}</div>
                                                            <div>{{ $s->title }}</div>
                                                            <div>{{ format_locale_date(strtotime($s->start), 'short') }}&nbsp;&nbsp;/&nbsp;&nbsp;{{ format_locale_date(strtotime($s->finish), 'short') }}</div>
                                                        </li>
                                                    @endforeach
                                                </ul>
                                            </div>
                                        </div>
                                    </li>
                                </ul>
                            </div>
                        </div>
                    @endif
                @endif
                <div id='calendarAddSessionDate' class='calendarAddDaysCl'></div>
            </div>
        </div>
    </div>
</div>

<!-- Show selected slot -->
<div id="createEventSession" class="modal fade in" role="dialog">
    <div class="modal-dialog modal-md">
        <div class="modal-content">
            <div class="modal-header">
                <div class="modal-title">{{ trans('langAdd') }}</div>
                <button type="button" class="close" data-bs-dismiss="modal" aria-label="{{ trans('langClose') }}"></button>
            </div>
            <div class="modal-body">
                <div class='form-wrapper form-edit rounded'>
                    <div class="controls controls-row" id="when"></div>
                </div>
            </div>
            <div class='modal-footer'>
                <button class='btn btn-primary' id='addDateTimeBtn'>{{ trans('langAdd') }}</button>
            </div>
        </div>
    </div>
</div>

@endsection
