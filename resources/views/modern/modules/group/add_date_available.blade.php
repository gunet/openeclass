
@extends('layouts.default')

@section('content')


<div class="col-12 main-section" >

    <div class="{{ $container }} module-container py-lg-0">

        <div class="course-wrapper d-lg-flex align-items-lg-strech w-100">

            <div id="background-cheat-leftnav" class="col_sidebar_active d-flex justify-content-start align-items-strech ps-lg-0 pe-lg-0">
                <div class="d-none d-sm-block d-sm-none d-md-block d-md-none d-lg-block ContentLeftNav">
                    @include('layouts.partials.sidebar',['is_editor' => $is_editor])
                </div>
            </div>

            <div class="col_maincontent_active">

                <div class='row'>

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

                    @if($is_editor)
                        {!! isset($action_bar) ?  $action_bar : '' !!}
                    @endif

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

                    <div class='col-12'>
                        <a class='btn submitAdminBtn d-inline-flex' href='#' data-bs-toggle='modal' data-bs-target='#infoEvents'>{{ trans('langInfoColourEvent') }}</a>
                        <div class='modal fade' id='infoEvents' tabindex='-1' role='dialog' aria-labelledby='infoEventsLabel' aria-hidden='true'>
                            <div class='modal-dialog'>
                                <div class='modal-content'>
                                    <div class='modal-header'>
                                        <div class='modal-title' id='infoEventsLabel'>{{ trans('langInfoColourEvent') }}</div>
                                        <button type='button' class='close' data-bs-dismiss='modal' aria-label='Close'>
                                        </button>
                                    </div>
                                    <div class='modal-body'>
                                        <div class='col-12'>
                                            <ul>
                                                <li class='mb-2'><p>{!! trans('langRedInfoRentezvousOtherProgram') !!}</p></li>
                                                <li class='mb-2'><p>{!! trans('langInfoInfoRentezvous') !!}</p></li>
                                                <li class='mb-2'><p>{!! trans('langWarningInfoRentezvous') !!}</p></li>
                                                <li class='mb-2'><p>{!! trans('langSuccessInfoRentezvous') !!}</p></li>
                                                <li><p>{!! trans('langPinkInfoRentezvous') !!}</p></li>
                                            </ul>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    
                    <div class="col-12 mt-3">
                        <div class='card panelCard px-lg-4 py-lg-3 h-100'>
                            <div class='card-body'>
                                <div id='calendarAddDays' class='calendarAddDaysCl'></div> 
                            </div>
                        </div>
                    </div>


                    <div id="createEventModal" class="modal fade in" role="dialog">
                        <div class="modal-dialog modal-md modal-success">

                            <!-- Modal content-->
                                <div class="modal-content">
                                    <div class="modal-header">
                                        <div class="modal-title">{{ trans('langAdd') }}</div>
                                        <button type="button" class="close" data-bs-dismiss="modal" aria-label="Close">
                                        </button>
                                    </div>
                                    <div class="modal-body">
                                        <div class='form-wrapper form-edit rounded'>

                                            
                                            <div class="controls controls-row" id="when"></div>
                                            
                                            <input type="hidden" id="startTime">
                                            <input type="hidden" id="endTime">
                                            <input type="hidden" id="lesson" value="{{ $lesson_id }}">
                                            <input type="hidden" id="user" value="{{ $tutor_id }}">
                                            <input type="hidden" id="group_id" value="{{ $group_id }}">
                                            
                                        </div>
                                    </div>
                                
                                
                                    <div class="modal-footer">
                                        <button class="btn cancelAdminBtn" data-bs-dismiss="modal" aria-hidden="true">{{trans('langCancel')}}</button>
                                        <button type="submit" class="btn submitAdminBtnDefault" id="submitButton">{{trans('langSubmit')}}</button>
                                    </div>
                                </div>

                        </div>
                    </div>

                    <div id="deleteEventModal" class="modal fade in" role="dialog">
                        <div class="modal-dialog modal-md modal-danger">

                            <!-- Modal content-->
                            <div class="modal-content">
                            <div class="modal-header">
                                
                                <div class="modal-title">{{ trans('langDeleteRentezvous') }}</div>
                                <button type="button" class="close" data-bs-dismiss="modal" aria-label="Close"></button>
                            </div>
                            <div class="modal-body">

                                <p>{{ trans('langDeleteAvailableDate') }}</p>
                                
                            </div>
                            <div class="modal-footer">
                                <button id="cancelModal" class="btn cancelAdminBtn" data-bs-dismiss="modal" aria-hidden="true">{{trans('langCancel')}}</button>
                                <button type="submit" class="btn deleteAdminBtn" id="deleteButton">{{trans('langDelete')}}</button>
                            </div>
                            </div>

                        </div>
                    </div>
                    
                

                </div>  

                

            </div>

        </div>
      
    </div>
</div>

<script type="text/javascript">
    $(document).ready( function () {

        var calendar = $('#calendarAddDays').fullCalendar({
            header:{
                left: 'prev,next ',
                center: 'title',
                right: ''
            },
            defaultView: 'agendaWeek',
            slotDuration: '00:30' ,
            minTime: '08:00:00',
            maxTime: '23:00:00',
            contentHeight:"auto",
            editable: true,
            selectable: true,
            allDaySlot: false,
            displayEventTime: true,
            events: "{{ $urlAppend }}modules/group/create_update_deleteDate.php?view=1&show_m={{ $tutor_id }}&show_g={{ $group_id }}&show_l={{ $lesson_id }}",
         
            eventRender: function( event, element, view ) {
                var title = element.find( '.fc-title' );
                title.html( title.text() );

                var timee = element.find( '.fc-time span' );
                
                element.popover({
                    title: timee[0].innerText+event.title,
                    trigger: 'hover',
                    placement: 'top',
                    container: 'body',
                    html: true,
		            sanitize: false
                });

                calendar.fullCalendar('removeEvents',function(event){
                    return event.className == "d-none";
                });
            },

            eventClick:  function(event) {


                var id = event.id;
                if(confirm("{{ js_escape(trans('langDeleteAvailableDate')) }}")){
                    $.ajax({
                        url: '{{ $urlAppend }}modules/group/create_update_deleteDate.php',
                        data: 'action=delete&id='+id+'&group_id='+event.group_id,
                        type: "POST",
                        success: function(json) {
                            if(json == 1){
                                alert("{{ js_escape(trans('langDeleteDateSuccess')) }}");
                                window.location.reload();
                            } 
                            else if(json == 0){
                                alert("{{ js_escape(trans('langDeleteDateNoUser')) }}");
                                window.location.reload();
                            }
                            else if(json == 2){
                                alert("{{ js_escape(trans('langExistBookingForThisDateMessageDetlete')) }}");
                                window.location.reload();
                            }
                        },
                        error:function(error){
                            console.log(error)
                        },
                    });
                }


            },
                
            //header and other values
            select: function(start, end) {

                var max_start = $.fullCalendar.moment(start).format('h:mm');
                var result_start = max_start.replace(":", ".");
                var max_end = $.fullCalendar.moment(end).format('h:mm');
                var result_end = max_end.replace(":", ".");

                var start1 = parseFloat(result_start);
                var end1 = parseFloat(result_end);

                var max_1hour = end1 - start1;
                max_1hour = max_1hour.toFixed(2);

                var start_day = $.fullCalendar.moment(start).format('dddd, Do MMMM YYYY');
                var end_day = $.fullCalendar.moment(end).format('dddd, Do MMMM YYYY');

                var keepgoing = 0;
                if((start1 == 12.3 && end1 == 1.3) || (start1 == 12.3 && end1 == 1) || (start1 == 12 && end1 == 1)){
                    keepgoing = 1;
                }

                if((Math.abs(max_1hour) <= 1 || keepgoing == 1) && start_day == end_day){
                    if(!start.isBefore(moment())){
                        endtime = $.fullCalendar.moment(end).format('h:mm');
                        starttime = $.fullCalendar.moment(start).format('dddd, Do MMMM YYYY, h:mm');
                        var mywhen = starttime + ' - ' + endtime;

                        start = moment(start).format('YYYY-MM-DD HH:mm');
                        end = moment(end).format('YYYY-MM-DD HH:mm');

                        $('#createEventModal #startTime').val(start);
                        $('#createEventModal #endTime').val(end);
                        $('#createEventModal #when').text(mywhen);
                        $('#createEventModal').modal('toggle');
                    }else{
                        alert("{{ js_escape(trans('langDateHasExpired')) }}");
                    }
                }else{
                    alert("{{ js_escape(trans('langDateMaxHour')) }}");
                    window.location.reload();
                }
            },

            eventDrop: function(event){
                
                $.ajax({
                    url: '{{ $urlAppend }}modules/group/create_update_deleteDate.php',
                    data: 'action=update&id='+event.id+'&start='+moment(event.start).format()+'&end='+moment(event.end).format()+'&idCourse='+event.idCourse+'&user_id='+event.user_id+'&group_id='+event.group_id,
                    type: "POST",
                    success: function(json) {
                        if(json == 1){
                            
                            alert("{{ js_escape(trans('langChangeDateSuccess')) }}");
                            window.location.reload();
                            
                        }else if(json == 0){
                            alert("{{ js_escape(trans('langNoChangeDateOtherUser')) }}");
                            window.location.reload();
                            
                        }else if(json == 2){
                            alert("{{ js_escape(trans('langExistBookingForThisDate')) }}");
                            window.location.reload();
                        }   
                    },
                    error:function(error){
                        console.log(error)
                    },
                });
            },

            eventResize: function(event) {

                var max_start = moment(event.start).format('h:mm');
                var result_start = max_start.replace(":", ".");
                var max_end = moment(event.end).format('h:mm');
                var result_end = max_end.replace(":", ".");

                var start1 = parseFloat(result_start);
                var end1 = parseFloat(result_end);

                var max_1hour = end1 - start1;
                max_1hour = max_1hour.toFixed(2);

                var start_day = moment(event.start).format('dddd, Do MMMM YYYY');
                var end_day = moment(event.end).format('dddd, Do MMMM YYYY');

                var keepgoing = 0;
                if((start1 == 12.3 && end1 == 1.3) || (start1 == 12.3 && end1 == 1) || (start1 == 12 && end1 == 1)){
                    keepgoing = 1;
                }

                if((Math.abs(max_1hour) <= 1 || keepgoing == 1) && start_day == end_day){

                    $.ajax({
                        url: '{{ $urlAppend }}modules/group/create_update_deleteDate.php',
                        data: 'action=update&id='+event.id+'&start='+moment(event.start).format()+'&end='+moment(event.end).format()+'&idCourse='+event.idCourse+'&user_id='+event.user_id+'&group_id='+event.group_id,
                        type: "POST",
                        success: function(json) {
                            if(json == 1){
                                
                                alert("{{ js_escape(trans('langChangeDateSuccess')) }}");
                                window.location.reload();
                            
                            }else if(json == 0){
                                alert("{{ js_escape(trans('langNoChangeDateOtherUser')) }}");
                                window.location.reload();
                                
                            }else if(json == 2){
                                alert("{{ js_escape(trans('langExistBookingForThisDate')) }}");
                                window.location.reload();
                            }   
                        },
                        error:function(error){
                            console.log(error)
                        },
                    });
                }else{
                    alert("{{ js_escape(trans('langDateMaxHour')) }}");
                    window.location.reload();
                }
            }
            
        });

        $('#submitButton').on('click', function(e){
            e.preventDefault();
            doSubmit();
        });

    } );

    function doDelete(eventID){
        
        $.ajax({
            url: '{{ $urlAppend }}modules/group/create_update_deleteDate.php',
            data: 'action=delete&id='+eventID,
            type: "POST",
            success: function(json) {
                if(json == 1){
                   
                    $("#deleteEventModal").modal('hide');
                    alert("{{ js_escape(trans('langDeleteDateSuccess')) }}");
                    window.location.reload();
                } 
                else if(json == 0){
                    alert("{{ js_escape(trans('langDeleteDateNoUser')) }}");
                    window.location.reload();
                    
                }
                else if(json == 2){
                    alert("{{ js_escape(trans('langExistBookingForThisDateMessageDetlete')) }}");
                    window.location.reload();
                }
            }
        });
    }

    function doSubmit(){
        $("#createEventModal").modal('hide');

        var startTime = $('#startTime').val();
        var endTime = $('#endTime').val();
        var idCourse = $('#lesson').val();
        var user_id = $('#user').val();
        var group_id = $('#group_id').val();

    
        $.ajax({
            url: '{{ $urlAppend }}modules/group/create_update_deleteDate.php',
            data: 'action=add&start='+startTime+'&end='+endTime+'&user='+user_id+'&idCourse='+idCourse+'&group_id='+group_id,
            type: "POST",
            success: function(json) {
                $("#calendarAddDays").fullCalendar('renderEvent',
                {
                    id: json.id,
                    start: startTime,
                    end: endTime,
                    user: user_id
                },true);
                if(json == 1){
                    alert("{{ js_escape(trans('langAddDatesSuccess')) }}");
                    window.location.reload();
                }
                
            }
        });
        
        
        
    }

</script>
@endsection
