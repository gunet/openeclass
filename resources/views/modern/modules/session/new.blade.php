@extends('layouts.default')

@push('head_scripts')
    <script type="text/javascript">
        $(document).ready(function() {

            $('#openSessionCal').on('click', function(e){
                e.preventDefault();
                var calendar = $('#calendarAddSessionDate').fullCalendar({
                    header:{
                        left: 'prev,next ',
                        center: 'title',
                        right: ''
                    },
                    defaultView: 'agendaWeek',
                    slotDuration: '00:45' ,
                    minTime: '08:00:00',
                    maxTime: '23:00:00',
                    contentHeight:"auto",
                    editable: false,
                    selectable: true,
                    allDaySlot: false,
                    displayEventTime: true,
                    events: "{{ $urlAppend }}modules/session/disabled_session_slots.php?course={{ $course_id }}&show_sessions=true",
                
                    eventRender: function( event, element, view ) {
                        var title = element.find( '.fc-title' );
                        title.html( title.text() );
                        var timee = element.find( '.fc-time span' );
                        element.popover({
                            title: timee[0].innerText+'</br>'+event.title,
                            trigger: 'hover',
                            placement: 'top',
                            container: 'body',
                            html: true,
                            sanitize: false
                        });
                    },

                    //header and other values
                    select: function(start, end) {

                        var dateStart = $.fullCalendar.moment(start).format('hh:mm');
                        var dateEnd = $.fullCalendar.moment(end).format('hh:mm');

                        var startDate_hour = $.fullCalendar.moment(start).format('hh');
                        startDate_hour = parseInt(startDate_hour);
                        var startDate_min = $.fullCalendar.moment(start).format('mm');
                        startDate_min = parseInt(startDate_min);

                        var endDate_hour = $.fullCalendar.moment(end).format('hh');
                        endDate_hour = parseInt(endDate_hour);
                        var endDate_min = $.fullCalendar.moment(end).format('mm');
                        endDate_min = parseInt(endDate_min);

                        var start_day = $.fullCalendar.moment(start).format('dddd, Do MMMM YYYY');
                        var end_day = $.fullCalendar.moment(end).format('dddd, Do MMMM YYYY');

                        var is_hour_ok = 0;
                        if(endDate_hour==startDate_hour+1){
                            is_hour_ok = 1;
                        }
                        if(endDate_hour==1 && startDate_hour==12){
                            is_hour_ok = 1;
                        }
                        if(endDate_hour==startDate_hour){
                            is_hour_ok = 1;
                        }

                        var is_minute_ok = 0;
                        if(startDate_min>=30){
                            var diffStartMin = 60 - startDate_min;
                        }else{
                            var diffStartMin = 30 - startDate_min;
                        }

                        if(endDate_min>=30){
                            var diffEndMin = 60 - endDate_min;
                        }else{
                            var diffEndMin = 30 - endDate_min;
                        }

                        var sumDiffMin = diffStartMin+diffEndMin;

                        if(sumDiffMin<=45){
                            is_minute_ok = 1;
                        }
                        if(endDate_hour==1 && startDate_hour==12){
                            is_minute_ok = 1;
                        }
                        if(endDate_hour==startDate_hour){
                            is_minute_ok = 1;
                        }

                        // Special cases for slot duration
                        if( (dateStart=='10:15' && dateEnd=='11:45') ||
                            (dateStart=='01:15' && dateEnd=='02:45') ||
                            (dateStart=='04:15' && dateEnd=='05:45') ||
                            (dateStart=='07:15' && dateEnd=='08:45')  ){
                                is_minute_ok = 0;
                            }

                        if(is_hour_ok==1 && is_minute_ok==1 && start_day==end_day){
                            if(!start.isBefore(moment())){
                                endtime = $.fullCalendar.moment(end).format('h:mm');
                                starttime = $.fullCalendar.moment(start).format('dddd, Do MMMM YYYY, h:mm');
                                var mywhen = starttime + ' - ' + endtime;

                                start = moment(start).format('YYYY-MM-DD HH:mm');
                                end = moment(end).format('YYYY-MM-DD HH:mm');

                                $('#startTimeTmp').val(start);
                                $('#endTimeTmp').val(end);
                                $('#whenTmp').val(mywhen);
                                $('#createEventSession #when').text(mywhen);
                                $('#createEventSession').modal('toggle');
                                
                            }else{
                                alert("{{ js_escape(trans('langDateHasExpired')) }}");
                            }
                        }else{
                            alert("{{ js_escape(trans('langDateMaxMinutes')) }}");
                            //window.location.reload();
                        }
                    },

                    eventClick: function(event) {
                        if(event.className == 'exist_event_session'){
                            return false;
                        }
                    }
 
                });

                $('#calendarAddSessionDate').removeClass('d-none');
                $('#calendarAddSessionDate').removeClass('d-block');

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


            // Regarding session type (individual or group session)
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
                    
                    {!! $action_bar !!}

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

                    <div class='d-lg-flex gap-4 mt-4'>
                        <div class='flex-grow-1'>
                            <div class='form-wrapper form-edit rounded'>
                                <form role='form' class='form-horizontal' action="{{ $_SERVER['SCRIPT_NAME'] }}?course={{ $course_code }}" method='post'>
                                    <fieldset>

                                        <div class="form-group">
                                            <label for='creators' class='control-label-notes'>{{ trans('langCreator') }}&nbsp;<span class='Accent-200-cl'>(*)</span></label>
                                            <select class='form-select' name='creators' id='creators'>
                                                @if($is_tutor_course)
                                                    <option value=''>
                                                        {{ trans('langSelectConsultant') }}
                                                    </option>
                                                    @foreach($creators as $c)
                                                        <option value='{{ $c->user_id }}' {!! $c->user_id == $uid ? 'selected' : '' !!}>
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
                                        </div>

                                        <div class="form-group mt-4">
                                            <label for='title' class='col-12 control-label-notes'>{{ trans('langTitle')}}&nbsp;<span class='Accent-200-cl'>(*)</span></label>
                                            <div class='col-12'>
                                                <input id='title' type='text' name='title' class='form-control'>
                                                @if(Session::getError('title'))
                                                    <span class='help-block Accent-200-cl'>{!! Session::getError('title') !!}</span>
                                                @endif
                                            </div>
                                        </div>

                                        <div class='form-group mt-4'>
                                            <label for='comments' class='col-12 control-label-notes'>{{ trans('langComments')}}</label>
                                            {!! $comments !!}
                                        </div>

                                        <div class='form-group mt-4'>
                                            <div class="radio">
                                                <label>
                                                    <input type='radio' name='session_type' value='one' id='one_session' checked>
                                                    {{ trans('langIndividualSession') }}
                                                </label>
                                            </div>
                                            <div class="radio mt-2">
                                                <label>
                                                    <input type='radio' name='session_type' value='group' id='group_session'>
                                                    {{ trans('langGroupSession') }}
                                                </label>
                                            </div>

                                            <p class='control-label-notes mb-0 mt-3'>{{ trans('langParticipants') }}&nbsp;<span class='Accent-200-cl'>(*)</span></p>
                                            <div id='select_one_session' class='d-block mt-1'>
                                                <select name='one_participant' class='form-select'>
                                                    <option value='' selected>{{ trans('langSelectUser') }}</option>
                                                    @foreach($simple_users as $u)
                                                        <option value='{{ $u->user_id }}'>{{ $u->givenname }}&nbsp;{{ $u->surname }}</option>
                                                    @endforeach
                                                </select>
                                                @if(Session::getError('one_participant'))
                                                    <span class='help-block Accent-200-cl'>{!! Session::getError('one_participant') !!}</span>
                                                @endif
                                            </div>
                                            <div id='select_group_session' class='d-none mt-1'>
                                                <select id='select_users_group_session' name='many_participants[]' class='form-select' multiple>
                                                    @foreach($simple_users as $u)
                                                        <option value='{{ $u->user_id }}'>{{ $u->givenname }}&nbsp;{{ $u->surname }}</option>
                                                    @endforeach
                                                </select>
                                                <a href='#' id='selectAll'>{{ trans('langJQCheckAll') }}</a> | <a href='#' id='removeAll'>{{ trans('langJQUncheckAll') }}</a>
                                                @if(Session::getError('many_participants'))
                                                    <span class='help-block Accent-200-cl'>{!! Session::getError('many_participants') !!}</span>
                                                @endif
                                            </div>
                                        </div>

                                        <div class='form-group mt-4'>
                                            <p class='control-label-notes mb-2'>{{ trans('langStartEndSessionDateTime') }}&nbsp;<span class='Accent-200-cl'>(*)</span></p>
                                            <div class="input-group mb-3 rounded-2 border-0 gap-2">
                                                <span class="input-group-text p-0 border-0 bg-transparent" id="start-end-datetime-session">
                                                    <a type="button" class="btn submitAdminBtn d-inline-flex gap-1 rounded-2" 
                                                        data-bs-toggle="modal" data-bs-target="#staticDateTimeSession" id='openSessionCal'>
                                                        <i class='fa-solid fa-calendar'></i>
                                                    </a>
                                                </span>
                                                <input id='startDateValue' type="text" class="form-control mt-0 pe-none rounded-2" aria-describedby="start-end-datetime-session">
                                                <input type="hidden" id="startTimeTmp">
                                                <input type="hidden" id="endTimeTmp">
                                                <input type="hidden" id="whenTmp">
                                                <input type="hidden" id="startTime" name='start_session'>
                                                <input type="hidden" id="endTime" name='end_session'>
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
                                            <p class='control-label-notes mb-0 mt-3'>{{ trans('langTypeRemote') }}</p>
                                            <select class='form-select' name='type_remote'>
                                                <option value='0'>{{ trans('langNotRemote') }}</option>
                                                <option value='1'>{{ trans('langRemote') }}</option>
                                            </select>
                                        </div>

                                        <div class='form-group mt-4'>
                                            <div class='checkbox'>
                                                <label class='label-container'>
                                                    <input type='checkbox' name='session_visible'>
                                                    <span class='checkmark'></span>
                                                    {{ trans('langVisible') }}
                                                </label>
                                            </div>
                                        </div>

                                        {!! generate_csrf_token_form_field() !!}    

                                        <div class='form-group mt-5'>
                                            <div class='col-12 d-flex justify-content-end aling-items-center'>
                                                <input class='btn submitAdminBtn' type='submit' name='submit' value='{{ trans('langSubmit') }}'>
                                            </div>
                                        </div>

                                    </fieldset>
                                </form>
                            </div>
                        </div>
                        <div class='d-none d-lg-block'>
                            <img class='form-image-modules' src='{!! get_form_image() !!}' alt='form-image'>
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
                <h5 class="modal-title" id="staticDateTimeSessionLabel">{{ trans('langStartEndSessionDateTime') }}</h5>
                <button type="button" class="close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <div class='alert alert-info'>
                    <i class='fa-solid fa-circle-info fa-lg'></i>
                    <span>{!! trans('langInfoNewSession') !!}</span>
                </div>
                <div id='calendarAddSessionDate' class='calendarAddDaysCl d-none'></div>
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
                <button type="button" class="close" data-bs-dismiss="modal" aria-label="Close"></button>
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
