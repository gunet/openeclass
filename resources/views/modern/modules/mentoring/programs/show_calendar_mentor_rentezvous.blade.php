
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
                                <li class="breadcrumb-item"><a class='TextSemiBold' href="{{ $urlAppend }}modules/mentoring/programs/group/meeting_space.php?group_id={!! getInDirectReference($group_id) !!}">Meetings&nbsp({!! show_mentoring_program_group_name($group_id) !!})</a></li>
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

                    <div class='col-12'>
                        <ul>
                            <li><p>{!! trans('langRedInfoMeeting') !!}</p></li>
                            <li><p>{!! trans('langInfoInfoMeeeting') !!}</p></li>
                            <li><p>{!! trans('langWarningInfoMeeting') !!}</p></li>
                            <li><p>{!! trans('langSuccessInfoMeeting') !!}</p></li>
                        </ul>
                     
                    </div>
                    
                    <div class="col-12 overflow-auto">
                        <div id='calendarMeetings' class='MeetingsCalendar'></div>
                    </div>


                    <div id="createEventModal" class="modal fade in" role="dialog">
                        <div class="modal-dialog modal-md- modal-success">

                            <!-- Modal content-->
                                <div class="modal-content">
                                    <div class="modal-header">
                                   
                                        <h5 class="modal-title">{{ trans('langAdd')}}</h5> 
                                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                                    </div>
                                    <div class="modal-body">
                                        <div class='form-wrapper form-edit rounded'>
                                            <div class='col-12'><p class='TextBold text-end'>(<span class='text-danger'>*</span>) {{trans('langCPFFieldRequired')}}</p></div>
                                            <div class="form-group">
                                                <label class="control-label-notes">{{trans('langUntil')}}:</label>
                                                <div class="controls controls-row TextSemiBold text-secondary" id="when"></div>
                                            </div>

                                            <div class="form-group mt-4">
                                                <label class="control-label-notes" for="title">{{trans('langSubject')}}:(<span class='text-danger'>*</span>)</label>
                                                <input class='form-control' id="title" name="title" type="text">
                                            </div>

                                            <div class="form-group mt-4">
                                                <label class="control-label-notes">{{trans('langType')}}:(<span class='text-danger'>*</span>)</label>
                                                <div class='col-12 d-inline-flex'>
                                                    <select name='tc_type' class='form-select' id='tc_type_id'>
                                                        <option value='0'>-- {{ trans('langWelcomeSelect') }} --</option>
                                                        <option value='1'>Google Meeting</option>
                                                        <option value='2'>Zoom Meeting</option>
                                                        <option value='3'>Skype Meeting</option>
                                                    </select>
                                                    <a id='google_meet_id' class='btn btn-success btn-sm small-text rounded-pill w-50 mt-2' href='https://meet.google.com/' target="_blank">
                                                        Google Meeting
                                                    </a>
                                                    <a id='zoom_meet_id' class='btn btn-info btn-sm small-text rounded-pill w-50 mt-2' href='https://zoom.us/signin#/login' target="_blank">
                                                        Zoom Meeting
                                                    </a>
                                                    <a id='skype_meet_id' class='btn btn-primary btn-sm small-text rounded-pill w-50 mt-2' href='https://www.skype.com/el/' target="_blank">
                                                        Skype Meeting
                                                    </a>
                                                </div>

                                                <div class="form-group mt-4" id='url_form'>
                                                    <label class="control-label-notes" for="url">URL:(<span class='text-danger'>*</span>)</label>
                                                    <input class='form-control rounded-pill h-30px bgEclass' id="url" name="url" type="url">
                                                </div>

                                                <div class="form-group mt-4" id='meeting_id_form'>
                                                    <label class="control-label-notes" for="meet_id">Meeting ID:</label>
                                                    <input class='form-control' id="meet_id" name="meeting_id" type="text">
                                                </div>

                                                <div class="form-group mt-4" id='passcode_id'>
                                                    <label class="control-label-notes" for="pass_id">Passcode:</label>
                                                    <input class='form-control' id="pass_id" name="passcode_id" type="text">
                                                </div>

                                                <div class="form-group mt-4" id='members_box_form'>
                                                    <label class="control-label-notes" for="members_box">{{ trans('langParticipants')}}:(<span class='text-danger'>*</span>)</label>
                                                    <select class='form-select h-25 rounded-2' id='members_box' name='ingroup[]' size='15' multiple>
                                                        
                                                        @php
                                                            $q = Database::get()->queryArray("SELECT user.id, user.surname, user.givenname, user.am
                                                                                                FROM user, mentoring_group_members
                                                                                                WHERE mentoring_group_members.user_id = user.id AND
                                                                                                        mentoring_group_members.group_id = ?d AND
                                                                                                        mentoring_group_members.status_request = 1 AND
                                                                                                        mentoring_group_members.is_tutor = 0
                                                                                                ORDER BY user.surname, user.givenname", $group_id);
                                                        @endphp

                                                        
                                                        @foreach ($q as $member)
                                                            <option value='{{ $member->id }}'>{!! q("$member->surname $member->givenname") !!} {!! (!empty($member->am) ? $member->am : '') !!}</option>
                                                        @endforeach
                                                    </select>
                                                </div>
                                            </div>


                                            
                                            <input type="hidden" id="startTime">
                                            <input type="hidden" id="endTime">
                                            <input type="hidden" id="mentoring_program_id" value="{{ $mentoring_program_id }}">
                                            <input type="hidden" id="user" value="{{ $mentor_id }}">
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
                                <button class="btn btn-outline-secondary small-text rounded-2" data-bs-dismiss="modal" aria-hidden="true">{{trans('langCancel')}}</button>
                                <button type="submit" class="btn btn-danger small-text rounded-2" id="deleteButton">{{trans('langDelete')}}</button>
                            </div>
                            </div>

                        </div>
                    </div>
                   
                 

        </div>
      
    </div>
</div>


<script type='text/javascript'>
    $(document).ready(function () {

        $('#google_meet_id').css('display','none');
        $('#zoom_meet_id').css('display','none');
        $('#url_form').css('display','none');
        $('#meeting_id_form').css('display','none');
        $('#passcode_id').css('display','none');
        $('#members_box_form').css('display','none');
        $('#skype_meet_id').css('display','none');

        $('#tc_type_id').on('click',function(){
           console.log($('#tc_type_id').val());
           if($('#tc_type_id').val() == 1){
                $('#google_meet_id').css('display','block');
                $('#zoom_meet_id').css('display','none');
                $('#skype_meet_id').css('display','none');
           }else if($('#tc_type_id').val() == 2){
                $('#google_meet_id').css('display','none');
                $('#zoom_meet_id').css('display','block');
                $('#skype_meet_id').css('display','none');
           }else if($('#tc_type_id').val() == 3){
                $('#google_meet_id').css('display','none');
                $('#zoom_meet_id').css('display','none');
                $('#skype_meet_id').css('display','block');
           }else{
                $('#google_meet_id').css('display','none');
                $('#zoom_meet_id').css('display','none');
                $('#url_form').css('display','none');
                $('#meeting_id_form').css('display','none');
                $('#passcode_id').css('display','none');
                $('#members_box_form').css('display','none');
                $('#skype_meet_id').css('display','none');
           }

           if($('#tc_type_id').val() != 0){
            $('#url_form').css('display','block');
            $('#meeting_id_form').css('display','block');
            $('#passcode_id').css('display','block');
            $('#members_box_form').css('display','block');
           }
        })

        //////////////////////////////////////////////////////////////////////////////////////////////////////////

        var calendar = $('#calendarMeetings').fullCalendar({
            header:{
                left: 'prev,next today',
                center: 'title',
                right: 'agendaDay,agendaWeek'
            },
            defaultView: 'agendaWeek',
            slotDuration: '00:30' ,
            minTime: '08:00:00',
            maxTime: '23:00:00',
            editable: true,
            contentHeight:"auto",
            selectable: true,
            allDaySlot: false,
            displayEventTime: true,
            events: "{{ $urlAppend }}modules/mentoring/programs/show_create_update_delete_mentor_rentezvous.php?view=1&show_m={{ $mentor_id }}&show_g={{ $group_id }}",
            
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
                if(confirm("{{ js_escape(trans('langDeleteAvailableMeeting')) }}")){
                    $.ajax({
                        url: '{{ $urlAppend }}modules/mentoring/programs/show_create_update_delete_mentor_rentezvous.php',
                        data: 'action=delete&id='+id,
                        type: "POST",
                        success: function(json) {
                            if(json == 1){
                                $("#deleteEventModal").modal('hide');
                                alert("{{ js_escape(trans('langDeleteRentezvousSuccess')) }}");
                                window.location.reload();
                            } 
                            else{
                                alert("{{ js_escape(trans('langDeleteRentezvousNoUser')) }}");
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
                    alert("{{ js_escape(trans('langRentezvousMaxHour')) }}");
                }
                
            },

            eventDrop: function(event){
                
                $.ajax({
                    url: '{{ $urlAppend }}modules/mentoring/programs/show_create_update_delete_mentor_rentezvous.php',
                    data: 'action=update&id='+event.id+'&title='+event.title+'&start='+moment(event.start).format()+'&end='+moment(event.end).format()+'&program_id='+event.program+'&user_id='+event.user_id+'&group_id='+event.group_id,
                    type: "POST",
                    success: function(json) {
                        if(json == 1){
  
                            alert("{{ js_escape(trans('langChangeRentezvousSuccess')) }}");
                            window.location.reload();
                            
                        } 
                        else{
                            alert("{{ js_escape(trans('langNoChangeRentezvousOtherUser')) }}");
                            window.location.reload();
                            
                        }   
                    }
                });
            },

            eventResize: function(event) {
                $.ajax({
                    url: '{{ $urlAppend }}modules/mentoring/programs/show_create_update_delete_mentor_rentezvous.php',
                    data: 'action=update&id='+event.id+'&title='+event.title+'&start='+moment(event.start).format()+'&end='+moment(event.end).format()+'&program_id='+event.program+'&user_id='+event.user_id+'&group_id='+event.group_id,
                    type: "POST",
                    success: function(json) {
                        if(json == 1){
                            
                            alert("{{ js_escape(trans('langChangeRentezvousSuccess')) }}");
                            window.location.reload();
                           
                        } 
                        else{
                            alert("{{ js_escape(trans('langNoChangeRentezvousOtherUser')) }}");
                            window.location.reload();
                            
                        }   
                    }
                });
            }
            
        });

        $('#submitButton').on('click', function(e){
            e.preventDefault();
            $('#createEventModal').modal('hide');
            $('#loaderMeeting').modal('toggle');
            doSubmit();
        });

        
    });


    function doDelete(eventID){
        
        $.ajax({
            url: '{{ $urlAppend }}modules/mentoring/programs/show_create_update_delete_mentor_rentezvous.php',
            data: 'action=delete&id='+eventID,
            type: "POST",
            success: function(json) {
                if(json == 1){
                    
                    $("#deleteEventModal").modal('hide');
                    alert("{{ js_escape(trans('langDeleteRentezvousSuccess')) }}");
                    window.location.reload();
                } 
                else{
                    alert("{{ js_escape(trans('langDeleteRentezvousNoUser')) }}");
                    window.location.reload();
                    
                }   
            }
        });
    }

    function doSubmit(){
        $("#createEventModal").modal('hide');
        var title = $('#title').val();
        var startTime = $('#startTime').val();
        var endTime = $('#endTime').val();
        var program_id = $('#mentoring_program_id').val();
        var user_id = $('#user').val();
        var group_id = $('#group_id').val();
        var tc_type_id = $('#tc_type_id').val();
        var url = $('#url').val();
        var meeting_id = $('#meet_id').val();
        var passcode_id = $('#pass_id').val();
        var members_box = $('#members_box').val();

        
        
        if(title.length == 0  || url.length == 0 || members_box.length == 0 || tc_type_id == 0){
            alert("{{ js_escape(trans('langConfirmEmptyInput')) }}");
            window.location.reload();
        }else{
            $.ajax({
                url: '{{ $urlAppend }}modules/mentoring/programs/show_create_update_delete_mentor_rentezvous.php',
                data: 'action=add&title='+title+'&start='+startTime+'&end='+endTime+'&user='+user_id+'&program_id='+program_id+'&group_id='+group_id+'&tc_type_id='+tc_type_id+'&url='+url+'&meeting_id='+meeting_id+'&passcode_id='+passcode_id+'&members_box='+members_box,
                type: "POST",
                success: function(json) {
                    $("#calendarMeetings").fullCalendar('renderEvent',
                    {
                        id: json.id,
                        title: title,
                        start: startTime,
                        end: endTime,
                        user: user_id
                    },true);
                    if(json == 1){
                        $('#loaderMeeting').modal('hide');
                        alert("{{ js_escape(trans('langAddRentezvousSuccess')) }}");
                        window.location.reload();
                    }
                    
                }
            });
        }
        
        
    }

</script>

@endsection