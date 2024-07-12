
@extends('layouts.default')

@section('content')


<div class="col-12 main-section" >

    <div class="{{ $container }} main-container">

        <div class="row m-auto">


            @include('layouts.common.breadcrumbs', ['breadcrumbs' => $breadcrumbs])

            @include('layouts.partials.legend_view')

            @if(isset($action_bar) and $action_bar)
                {!! $action_bar !!}
            @else
                <div class='mt-4'></div>
            @endif

            @include('layouts.partials.show_alert') 

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
                                        <li class='mb-2'><p>{!! trans('langSuccessBookingUser') !!}</p></li>
                                        <li><p>{!! trans('langPinkBookingUser') !!}</p></li>
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
                                <button type="button" class='close' data-bs-dismiss="modal" aria-label="Close">
                                </button>
                            </div>
                            <div class="modal-body">
                                <div class='form-wrapper form-edit rounded'>

                                    
                                    <div class="controls controls-row" id="when"></div>
                                    
                                    <input type="hidden" id="startTime">
                                    <input type="hidden" id="endTime">
                                    <input type="hidden" id="user" value="{{ $uid }}">
                                    
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
            events: "{{ $urlAppend }}main/profile/create_update_delete_date.php?view=1&show_m={{ $uid }}",
         
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
                        url: '{{ $urlAppend }}main/profile/create_update_delete_date.php',
                        data: 'action=delete&id='+id,
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
                                alert("{{ js_escape(trans('langExistBookingForThisDateDelete')) }}");
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
                    url: '{{ $urlAppend }}main/profile/create_update_delete_date.php',
                    data: 'action=update&id='+event.id+'&start='+moment(event.start).format()+'&end='+moment(event.end).format()+'&user_id='+event.user_id,
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
                        url: '{{ $urlAppend }}main/profile/create_update_delete_date.php',
                        data: 'action=update&id='+event.id+'&start='+moment(event.start).format()+'&end='+moment(event.end).format()+'&user_id='+event.user_id,
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
            url: '{{ $urlAppend }}main/profile/create_update_delete_date.php',
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
                    alert("{{ js_escape(trans('langExistBookingForThisDateDelete')) }}");
                    window.location.reload();
                }
            }
        });
    }

    function doSubmit(){
        $("#createEventModal").modal('hide');

        var startTime = $('#startTime').val();
        var endTime = $('#endTime').val();
        var user_id = $('#user').val();
    
        $.ajax({
            url: '{{ $urlAppend }}main/profile/create_update_delete_date.php',
            data: 'action=add&start='+startTime+'&end='+endTime+'&user='+user_id,
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
