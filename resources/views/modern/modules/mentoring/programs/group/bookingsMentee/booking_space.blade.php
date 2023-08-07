
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

                    <div id="loaderMeeting" class="modal fade in" role="dialog">
                        <div class="modal-dialog">
                            <div class="modal-content">
                                <div class="modal-body bg-light d-flex justify-content-center align-items-center">
                                    <img src='{{ $urlAppend }}template/modern/img/ajax-loader.gif'>
                                    <span>{{ trans('langPlsWait') }}</span>
                                </div>
                            </div>
                        </div>
                    </div>
                    
                    <div class="col-12 mt-3 overflow-auto">
                        <div id='calendarBookingMentee' class='bookingCalendarMentee'></div>
                    </div>

                    @php 
                        $booking_by_mentee = Database::get()->queryArray("SELECT givenname,surname FROM user WHERE id = ?d",$uid);
                    @endphp
                    @foreach($booking_by_mentee as $mentee)
                        <input type="hidden" id="titleMentee" value="{{ $mentee->givenname }} {{ $mentee->surname }}">
                    @endforeach
                    <input type="hidden" id="startTime">
                    <input type="hidden" id="endTime">
                    <input type="hidden" id="mentoring_program_id" value="{{ $mentoring_program_id }}">
                    <input type="hidden" id="mentor_id" value="{{ $mentor_id_for_booking }}">
                    <input type="hidden" id="group_id" value="{{ $group_id }}">
                   
                

        </div>
      
    </div>
</div>


<script type='text/javascript'>
    $(document).ready(function () {

        var calendar = $('#calendarBookingMentee').fullCalendar({
            header:{
                left: 'prev,next today',
                center: 'title',
                right: 'agendaDay,agendaWeek'
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
            events: "{{ $urlAppend }}modules/mentoring/programs/group/bookingsMentee/booking_create_update_delete.php?view=1&show_mentor={!! getInDirectReference($mentor_id_for_booking) !!}&show_group={!! getInDirectReference($group_id) !!}",
            eventRender: function( event, element, view ) {
                var title = element.find( '.fc-title' );
                title.html( title.text() );
            },
            eventClick:  function(event) { 
                start = moment(event.start).format('YYYY-MM-DD HH:mm');
                end = moment(event.end).format('YYYY-MM-DD HH:mm');
                
                if(event.className == 'bookingAdd'){
                    
                    if(confirm("{{ js_escape(trans('langdobookingwithmentor')) }}")){
                        var startTime = start;
                        var endTime = end;
                        var title = $('#titleMentee').val();
                        var program_id = $('#mentoring_program_id').val();
                        var user_id = $('#mentor_id').val();
                        var group_id = $('#group_id').val();

                        $('#loaderMeeting').modal('toggle');
                        
                        $.ajax({
                            url: '{{ $urlAppend }}modules/mentoring/programs/group/bookingsMentee/booking_create_update_delete.php',
                            data: 'action=add&title='+title+'&start='+startTime+'&end='+endTime+'&mentor_id='+user_id+'&program_id='+program_id+'&group_id='+group_id,
                            type: "POST",
                            success: function(json) {
                                $('#loaderMeeting').modal('hide');
                                if(json == 1){
                                    alert("{{ js_escape(trans('langAddBookingSuccess')) }}");
                                    window.location.reload();
                                }else if(json == 0){
                                    alert("{{ js_escape(trans('langAddBookingNoSuccess')) }}");
                                    window.location.reload();
                                }else if(json == 2){
                                    alert("{{ js_escape(trans('langMentorHasRemovedTheDate')) }}");
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
                    if(confirm("{{ js_escape(trans('langdelbookingwithmentor')) }}")){
                        $('#loaderMeeting').modal('toggle');
                        $.ajax({
                            url: '{{ $urlAppend }}modules/mentoring/programs/group/bookingsMentee/booking_create_update_delete.php',
                            data: 'action=delete&id='+id,
                            type: "POST",
                            success: function(json) {
                                $('#loaderMeeting').modal('hide');
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