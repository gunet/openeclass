
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

                    {!! isset($action_bar) ?  $action_bar : '' !!}

                    @include('layouts.partials.show_alert') 

                    <div id="loaderBooking" class="modal fade in" role="dialog">
                        <div class="modal-dialog">
                            <div class="modal-content">
                                <div class="modal-body bg-transparent d-flex justify-content-center align-items-center">
                                    <img src='{{ $urlAppend }}template/modern/img/ajax-loader.gif' alt='Loading'>
                                    <span>{{ trans('langPlsWait') }}</span>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class='col-12'>
                        <a class='btn submitAdminBtn d-inline-flex' href='#' data-bs-toggle='modal' data-bs-target='#infoEvents'>{{ trans('langInfoColourEvent') }}</a>
                        <div class='modal fade' id='infoEvents' tabindex='-1' role='dialog' aria-labelledby='infoEventsLabel' aria-hidden='true'>
                            <div class='modal-dialog'>
                                <div class='modal-content'>
                                    <div class='modal-header'>
                                        <div class='modal-title' id='infoEventsLabel'>{{ trans('langInfoColourEvent') }}</div>
                                        <button type='button' class='close' data-bs-dismiss='modal' aria-label='Close'></button>
                                    </div>
                                    <div class='modal-body'>
                                        <div class='col-12'>
                                            <ul>
                                                <li class='mb-2'><p>{!! trans('langBlueInfoBooking') !!}</p></li>
                                                <li class='mb-2'><p>{!! trans('langSuccessInfoBooking') !!}</p></li>
                                                <li class='mb-2'><p>{!! trans('langPinkInfoBooking') !!}</p></li>
                                                <li><p>{!! trans('langWarningInfoBooking') !!}</p></li>
                                            </ul>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    
                    <div class="col-12 mt-3 bookings-content">
                        <div class='card panelCard px-lg-4 py-lg-3 h-100'>
                            <div class='card-body'>
                                <div id='calendarBooking' class='bookingCalendarByUser'></div>
                            </div>
                        </div>
                    </div>

                    <input type="hidden" id="titleSimpleUser" value="{{ $booking_by_username }} {{ $booking_by_surname }}">
                    <input type="hidden" id="startTime">
                    <input type="hidden" id="endTime">
                    <input type="hidden" id="course_Id" value="{{ $course_id }}">
                    <input type="hidden" id="tutor_Id" value="{{ $tutor_id_for_booking }}">
                    <input type="hidden" id="group_Id" value="{{ $group_id }}">
                   
                </div>
            </div>
        </div>
    </div>
</div>



<script type='text/javascript'>
    $(document).ready(function () {

        var calendar = $('#calendarBooking').fullCalendar({
            header:{
                left: 'prev,next ',
                center: 'title',
                right: ''
            },
            defaultView: 'agendaWeek',
            slotDuration: '00:30' ,
            editable: false,
            minTime: '08:00:00',
            maxTime: '23:00:00',
            contentHeight:"auto",
            selectable: true,
            allDaySlot: false,
            displayEventTime: true,
            events: "{{ $urlAppend }}modules/group/booking_create_delete.php?view=1&show_tutor={{ $tutor_id_for_booking }}&show_group={{ $group_id }}",
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

            },
            eventClick:  function(event) { 
                start = moment(event.start).format('YYYY-MM-DD HH:mm');
                end = moment(event.end).format('YYYY-MM-DD HH:mm');
                
                if(event.className == 'bookingAdd'){
                    
                    if(confirm("{{ js_escape(trans('langdobookingwithtutor')) }}")){
                        var startTime = start;
                        var endTime = end;
                        var title = $('#titleSimpleUser').val();
                        var course_Id = $('#course_Id').val();
                        var user_id = $('#tutor_Id').val();
                        var group_id = $('#group_Id').val();

                        $('#loaderBooking').modal('toggle');
                        
                        $.ajax({
                            url: '{{ $urlAppend }}modules/group/booking_create_delete.php',
                            data: 'action=add&title='+title+'&start='+startTime+'&end='+endTime+'&tutor_Id='+user_id+'&course_Id='+course_Id+'&group_Id='+group_id,
                            type: "POST",
                            success: function(json) {
                                $('#loaderBooking').modal('hide');
                                if(json == 1){
                                    alert("{{ js_escape(trans('langAddBookingSuccess')) }}");
                                    window.location.reload();
                                }else if(json == 0){
                                    alert("{{ js_escape(trans('langAddBookingNoSuccess')) }}");
                                    window.location.reload();
                                }else if(json == 2){
                                    alert("{{ js_escape(trans('langTutorHasRemovedTheDate')) }}");
                                    window.location.reload();
                                }
                                
                            },
                            error:function(error){
                                console.log(error)
                            },
                        });
                        
                    }

                }
                   
                if(event.className == 'bookingDelete'){
                   
                    var id = event.id;
                    if(confirm("{{ js_escape(trans('langdelbookingwithtutor')) }}")){
                        $('#loaderBooking').modal('toggle');
                        $.ajax({
                            url: '{{ $urlAppend }}modules/group/booking_create_delete.php',
                            data: 'action=delete&id='+id,
                            type: "POST",
                            success: function(json) {
                                $('#loaderBooking').modal('hide');
                                if(json == 1){
                                    alert("{{ js_escape(trans('langDeleteBookingSuccess')) }}");
                                    window.location.reload();
                                }    
                            },
                            error:function(error){
                                console.log(error)
                            },
                        });
                        
                    }
                }
            }
        });

    });

</script>

@endsection