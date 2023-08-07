
@extends('layouts.default')

@section('content')


<div class="col-12 main-section">
    <div class='{{ $container }}'>
        <div class="row rowMargin">

                    @if($isCommonGroup == 1)
                        <nav class='breadcrumb_mentoring' style="--bs-breadcrumb-divider: url(&#34;data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' width='8' height='8'%3E%3Cpath d='M2.5 0L1 1.5 3.5 4 1 6.5 2.5 8l4-4-4-4z' fill='currentColor'/%3E%3C/svg%3E&#34;);" aria-label="breadcrumb">
                            <ol class="breadcrumb">
                                <li class="breadcrumb-item"><a class='TextSemiBold' href="{{ $urlAppend }}modules/mentoring/mentoring_platform_home.php"><span class='fa fa-home'></span>&nbsp{{ trans('langHomeMentoringPlatform') }}</a></li>
                                <li class="breadcrumb-item"><a class='TextSemiBold showProgramsBtn' href="{{ $urlAppend }}modules/mentoring/programs/show_programs.php">{{ trans('langOurMentoringPrograms') }}</a></li>
                                <li class="breadcrumb-item"><a class='TextSemiBold' href="{{ $urlAppend }}modules/mentoring/programs/myprograms.php">{{ trans('langMyPrograms') }}</a></li>
                                <li class="breadcrumb-item"><a class='TextSemiBold' href="{{ $urlAppend }}mentoring_programs/{{ $mentoring_program_code }}/index.php">{!! show_mentoring_program_title($mentoring_program_code) !!}</a></li>
                                <li class="breadcrumb-item"><a class='TextSemiBold' href="{{ $urlAppend }}modules/mentoring/programs/group/select_group.php">{{ trans('langMentoringSpace')}}</a></li>
                                <li class="breadcrumb-item"><a class='TextSemiBold' href="{{ $urlAppend }}modules/mentoring/programs/group/group_space.php?space_group_id={!! getInDirectReference($group_id) !!}">{!! show_mentoring_program_group_name($group_id) !!}</a></li>
                                <li class="breadcrumb-item active TextMedium" aria-current="page">{{ $toolName }}</li>
                            </ol>
                        </nav>
                    @else
                        <nav class='breadcrumb_mentoring' style="--bs-breadcrumb-divider: url(&#34;data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' width='8' height='8'%3E%3Cpath d='M2.5 0L1 1.5 3.5 4 1 6.5 2.5 8l4-4-4-4z' fill='currentColor'/%3E%3C/svg%3E&#34;);" aria-label="breadcrumb">
                            <ol class="breadcrumb">
                                <li class="breadcrumb-item"><a class='TextSemiBold' href="{{ $urlAppend }}modules/mentoring/mentoring_platform_home.php"><span class='fa fa-home'></span>&nbsp{{ trans('langHomeMentoringPlatform') }}</a></li>
                                <li class="breadcrumb-item"><a class='TextSemiBold showProgramsBtn' href="{{ $urlAppend }}modules/mentoring/programs/show_programs.php">{{ trans('langOurMentoringPrograms') }}</a></li>
                                <li class="breadcrumb-item"><a class='TextSemiBold' href="{{ $urlAppend }}modules/mentoring/programs/myprograms.php">{{ trans('langMyPrograms') }}</a></li>
                                <li class="breadcrumb-item"><a class='TextSemiBold' href="{{ $urlAppend }}mentoring_programs/{{ $mentoring_program_code }}/index.php">{!! show_mentoring_program_title($mentoring_program_code) !!}</a></li>
                                <li class="breadcrumb-item"><a class='TextSemiBold' href="{{ $urlAppend }}modules/mentoring/programs/group/select_group.php">{{ trans('langMentoringSpace')}}</a></li>
                                <li class="breadcrumb-item"><a class='TextSemiBold' href="{{ $urlAppend }}modules/mentoring/programs/group/index.php">{{ trans('langGroupMentorsMentees') }}</a></li>
                                <li class="breadcrumb-item"><a class='TextSemiBold' href="{{ $urlAppend }}modules/mentoring/programs/group/group_space.php?space_group_id={!! getInDirectReference($group_id) !!}">{!! show_mentoring_program_group_name($group_id) !!}</a></li>
                                <li class="breadcrumb-item active TextMedium" aria-current="page">{{ $toolName }}</li>
                            </ol>
                        </nav>
                    @endif

                    @include('modules.mentoring.common.common_current_title')
                    
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
                            
                            <button type="button" class="btn-close btn-close-white" data-bs-dismiss="alert" aria-label="Close"></button>
                        </div>
                    </div>
                    @endif
                   
                    {!! $action_bar !!}

                    <div class='col-12'>
                        <ul>
                            <li><p>{!! trans('langRedInfoRentezvousOtherProgram') !!}</p></li>
                            <li><p>{!! trans('langInfoInfoRentezvous') !!}</p></li>
                            <li><p>{!! trans('langWarningInfoRentezvous') !!}</p></li>
                            <li><p>{!! trans('langSuccessInfoRentezvous') !!}</p></li>
                            <li><p>{!! trans('langPinkInfoRentezvous') !!}</p></li>
                        </ul>
                     
                    </div>
                    
                    <div class="col-12 mt-3 overflow-auto">
                        <div id='calendarBookings' class='calendarBookingsClass'></div> 
                    </div>


                    <div id="createEventModal" class="modal fade in" role="dialog">
                        <div class="modal-dialog modal-md modal-success">

                            <!-- Modal content-->
                                <div class="modal-content">
                                    <div class="modal-header">
                                        <h5 class="modal-title">{{ trans('langAdd') }}</h5>
                                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                                    </div>
                                    <div class="modal-body">
                                        <div class='form-wrapper form-edit rounded'>

                                            
                                            <div class="controls controls-row TextSemiBold text-secondary" id="when"></div>
                                            
                                            <input type="hidden" id="startTime">
                                            <input type="hidden" id="endTime">
                                            <input type="hidden" id="mentoring_program_id" value="{{ $mentoring_program_id }}">
                                            <input type="hidden" id="user" value="{{ $mentor_uid }}">
                                            <input type="hidden" id="group_id" value="{{ $group_id }}">
                                            
                                        </div>
                                    </div>
                                
                                
                                    <div class="modal-footer">
                                        <button class="btn btn-outline-secondary small-text rounded-2" data-bs-dismiss="modal" aria-hidden="true">{{trans('langCancel')}}</button>
                                        <button type="submit" class="btn btn-success small-text rounded-2" id="submitButton">{{trans('langSubmit')}}</button>
                                    </div>
                                </div>

                        </div>
                    </div>

                    <div id="deleteEventModal" class="modal fade in" role="dialog">
                        <div class="modal-dialog modal-md modal-danger">

                            <!-- Modal content-->
                            <div class="modal-content">
                            <div class="modal-header">
                                
                                <h5 class="modal-title">{{ trans('langDeleteRentezvous') }}</h5>
                                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                            </div>
                            <div class="modal-body">

                                <p>{{ trans('langDeleteAvailableDate') }}</p>
                                
                            </div>
                            <div class="modal-footer">
                                <button id="cancelModal" class="btn btn-outline-secondary small-text rounded-2" data-bs-dismiss="modal" aria-hidden="true">{{trans('langCancel')}}</button>
                                <button type="submit" class="btn btn-danger small-text rounded-2" id="deleteButton">{{trans('langDelete')}}</button>
                            </div>
                            </div>

                        </div>
                    </div>
                    
                    
                    <!--------------------------------------------------------------------------------------------------------------------------->

                    

                

        </div>
      
    </div>
</div>

<script type="text/javascript">
    $(document).ready( function () {

        var calendar = $('#calendarBookings').fullCalendar({
            header:{
                left: 'prev,next today',
                center: 'title',
                right: 'agendaDay,agendaWeek'
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
            events: "{{ $urlAppend }}modules/mentoring/programs/group/datesMentor/create_update_deleteDate.php?view=1&show_m={{ $mentor_uid }}&show_g={{ $group_id }}",
         
            eventRender: function( event, element, view ) {
                var title = element.find( '.fc-title' );
                title.html( title.text() );

                var timee = element.find( '.fc-time span' );
                //console.log(timee[0].innerText);
                
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
                        url: '{{ $urlAppend }}modules/mentoring/programs/group/datesMentor/create_update_deleteDate.php',
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
                //alert(max_1hour);

                if(max_1hour <= 1){
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
                        alert("{{ js_escape(trans('langDateHasExpire')) }}");
                    }
                }else{
                    alert("{{ js_escape(trans('langDateMaxHour')) }}");
                }
            },

            eventDrop: function(event){
                
                $.ajax({
                    url: '{{ $urlAppend }}modules/mentoring/programs/group/datesMentor/create_update_deleteDate.php',
                    data: 'action=update&id='+event.id+'&start='+moment(event.start).format()+'&end='+moment(event.end).format()+'&program_id='+event.program+'&user_id='+event.user_id+'&group_id='+event.group_id,
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
                $.ajax({
                    url: '{{ $urlAppend }}modules/mentoring/programs/group/datesMentor/create_update_deleteDate.php',
                    data: 'action=update&id='+event.id+'&start='+moment(event.start).format()+'&end='+moment(event.end).format()+'&program_id='+event.program+'&user_id='+event.user_id+'&group_id='+event.group_id,
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
            }
            
        });

        $('#submitButton').on('click', function(e){
            e.preventDefault();
            doSubmit();
        });

    } );

    function doDelete(eventID){
        
        $.ajax({
            url: '{{ $urlAppend }}modules/mentoring/programs/group/datesMentor/create_update_deleteDate.php',
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
        var program_id = $('#mentoring_program_id').val();
        var user_id = $('#user').val();
        var group_id = $('#group_id').val();

    
        $.ajax({
            url: '{{ $urlAppend }}modules/mentoring/programs/group/datesMentor/create_update_deleteDate.php',
            data: 'action=add&start='+startTime+'&end='+endTime+'&user='+user_id+'&program_id='+program_id+'&group_id='+group_id,
            type: "POST",
            success: function(json) {
                $("#calendarBookings").fullCalendar('renderEvent',
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
