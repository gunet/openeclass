
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

                    <div class='col-12 mb-4'>
                        <div class='col-lg-7 col-md-9 col-12 ms-auto me-auto ps-3 pe-3'>
                            <p class='TextMedium text-center text-justify'>{!! trans('langInfoMeetingsText')!!}</p>
                        </div>
                    </div>
                    
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
                   
                    
                    <div class='col-6'>
                        {!! $action_bar !!}
                    </div>
                    <div class='col-6 d-flex justify-content-end align-items-start'>
                        <a class='btn btn-outline-primary btn-sm small-text rounded-2 TextSemiBold text-uppercase' 
                            href='{{ $urlAppend }}modules/mentoring/programs/group/meeting_space.php?group_id={!! getIndirectReference($group_id) !!}&show_history'>
                            <span class='fa fa-history'></span><span class='hidden-xs-mentoring'>&nbsp{{ trans('langHistoryRentezvous') }}</span>
                        </a>
                    </div>

                    <div class='col-12'>
                        <div class='col-12 p-0'>

                            @if($is_editor_current_group or $is_editor_mentoring_program or $is_admin)

                                @if($is_editor_mentoring_program or $is_admin)
                                    @php $details_editor_group = Database::get()->queryArray("SELECT *FROM user
                                                                WHERE id IN (SELECT user_id FROM mentoring_group_members
                                                                WHERE group_id = ?d AND is_tutor = ?d AND status_request = ?d)",$group_id,1,1);
                                    @endphp
                                @else
                                    @php $details_editor_group = get_details_of_editor_for_current_group_with_uid($group_id,$uid); @endphp
                                @endif
                                
                                @if(count($details_editor_group) > 0)

                                    <div class='card-group'>
                                        <div class='row ms-0'>
                                            @foreach($details_editor_group as $editor)
                                                <div class='col-12 ps-0 pe-0'>
                                                    <div class='card bgNormalBlueText solidPanel mb-4'>
                                                        <div class="row g-0">
                                                            <div class='col-lg-3 col-md-4 col-12 colMeetingInfoMentor p-3'>
                                                                @php $profile_img = profile_image($editor->id, IMAGESIZE_LARGE, 'img-responsive img-circle img-profile rounded-2 card-img-top ProfileMeetingCard'); @endphp
                                                                {!! $profile_img !!}
                                                                
                                                                <p class='TextBold text-white fs-5 text-center mt-3 mb-3'>{{ $editor->givenname }} {{ $editor->surname }}</p>

                                                                @php 
                                                                    $NextRentezvous = getNextMeetingForMentor($editor->id,$group_id,$mentoring_program_id);
                                                                @endphp
                                                                @if(count($NextRentezvous) > 0)
                                                                    @foreach($NextRentezvous as $n)
                                                                        <p class='orangeText TextMedium text-center fs-6 mt-3 mb-1'>{{ trans('langNextMeeting') }}</p>
                                                                        <p class='text-center text-white TextMedium small-text'>{!! format_locale_date(strtotime($n['start'])) !!}</p>
                                                                        <p class='orangeText TextMedium text-center fs-6 mt-3 mb-1'>{{ trans('langLink') }}</p>
                                                                        <p class='text-center text-white TextMedium small-text'>{{ $n['api_url'] }}</p>
                                                                        <p class='orangeText TextMedium text-center fs-6 mt-3 mb-1'>{{ trans('langCode') }}</p>
                                                                        @if(!empty($n['passcode']))
                                                                            <p class='text-center text-white TextMedium small-text'>{{ $n['passcode'] }}</p>
                                                                        @else
                                                                            <p class='text-center text-white TextMedium small-text'>{{ trans('langFreeMeeting')}}</p>
                                                                        @endif
                                                                    @endforeach
                                                                @endif


                                                                @if(($is_editor_current_group and $uid == $editor->id) or $is_tutor_of_mentoring_program or $is_admin)
                                                                    <a class='btn TextReqular bgLightBlue small-text text-white m-auto d-block' href='{{ $urlAppend }}mentoring_programs/{{ $mentoring_program_code }}/index.php?showcal={!! getIndirectReference($editor->id) !!}&group_id={!! getIndirectReference($group_id) !!}'>
                                                                       {{ trans('langAddDateAvailableMentor') }}
                                                                    </a>
                                                                @endif

                                                            </div>


                                                            <div class='col-lg-9 col-md-8 col-12 d-flex justify-content-center align-items-center bg-white'>
                                                                <div class='card-body p-0 border-0'>
                                                                    @php  
                                                                        $now = date('Y-m-d', strtotime('now'));
                                                                        $end = date('Y-m-d',strtotime('now + 30days'));

                                                                        $now_time = date('H:i:s', strtotime('now'));
                                                                        $now_day = date('Y-m-d');
                                                                        $available_rentezvous_of_mentor_id = Database::get()->queryArray("SELECT id,mentoring_program_id,group_id,title,start,end,type_tc,api_url,passcode,meeting_id FROM mentoring_rentezvous
                                                                                                                                WHERE mentoring_program_id = ?d
                                                                                                                                AND mentor_id = ?d
                                                                                                                                AND group_id = ?d
                                                                                                                                AND start >= ?t
                                                                                                                                AND end <= ?t ORDER BY start ASC",$mentoring_program_id,$editor->id,$group_id,$now,$end);
                                                                    @endphp
                                                                    @if(count($available_rentezvous_of_mentor_id) > 0)

                                                                        <div id="carouselExampleDark{{ $editor->id }}" class="carousel slide meetingCarousel" data-bs-ride="carousel">

                                                                            <div class="carousel-indicators">
                                                                                @php $counter_meetings = 0; @endphp
                                                                                @foreach($available_rentezvous_of_mentor_id as $a)
                                                                                    
                                                                                    @if($counter_meetings == 0)
                                                                                        <button type="button" data-bs-target="#carouselExampleDark{{ $editor->id }}" data-bs-slide-to="{{ $counter_meetings }}" class="active" aria-current="true" aria-label="Slide {{ $tmp_counter_meetings }}"></button>
                                                                                    @else
                                                                                        <button type="button" data-bs-target="#carouselExampleDark{{ $editor->id }}" data-bs-slide-to="{{ $counter_meetings }}" aria-label="Slide {{ $tmp_counter_meetings }}"></button>
                                                                                    @endif

                                                                                    @php $counter_meetings++; @endphp

                                                                                @endforeach
                                                                                
                                                                            </div>

                                                                            <div class="carousel-inner">
                                                                                @php $counter_meetings = 0; $countPage = 1; @endphp
                                                                                @foreach($available_rentezvous_of_mentor_id as $a)
                                                                                    @php 
                                                                                        $old_now = date('Y-m-d', strtotime($a->start));
                                                                                        $old_time = date('H:i:s', strtotime($a->end));
                                                                                        $old_start = date('H:i:s', strtotime($a->start))
                                                                                    @endphp
                                                                                    <div class="carousel-item @if($counter_meetings == 0) active @endif" data-bs-interval="20000">
                                                                                        <div class='panel panel-default border-0 rounded-0'>
                                                                                            <div class='panel-body rounded-0 ps-lg-4 pe-lg-4'>


                                                                                                <div class='col-12 mb-3'>
                                                                                                    @if($now == $old_now and $now_time <= $old_time and $now_time >= $old_start)
                                                                                                        <span class='badge bg-info text-white'>{{ trans('langMeetingIsActive') }}</span>
                                                                                                    @elseif($now == $old_now and $now_time > $old_time) 
                                                                                                        <span class='badge bg-danger'>{{ trans('langMeetingIsDeactivate') }}</span>
                                                                                                        
                                                                                                        <a href="{{ $urlAppend }}modules/mentoring/programs/group/meeting_space.php?group_id={!! getInDirectReference($group_id) !!}&del_meeting_id={!! getInDirectReference($a->id) !!}" class='ms-3'>
                                                                                                            <span class="fa fa-trash text-danger fs-5" data-bs-toggle="tooltip" data-bs-placement="bottom" title data-bs-original-title="{{ trans('langDelete') }}"></span>
                                                                                                        </a>
                                                                                                    @else
                                                                                                        <span class='badge bg-secondary'>{{ trans('langMeetingNotStartedYet') }}</span>
                                                                                                    @endif
                                                                                                    <span class='badge pageCounter float-end fs-3 rounded-circle'>{{ $countPage }}</span>
                                                                                                </div>

                                                                                                <div class='col-12'>
                                                                                                    <p class='blackBlueText fs-5 TextBold'>{{ trans('langSubject') }}</p>
                                                                                                    <p>{{ $a->title }}</p>
                                                                                                </div>

                                                                                                <div class='col-12 mt-3'>
                                                                                                    <p class='blackBlueText fs-5 TextBold'>{{ trans('langType') }}</p>
                                                                                                    <p class="badge @if($a->type_tc == 'googlemeet') bg-success @elseif ($a->type_tc == 'zoommeet') bg-info @elseif ($a->type_tc == 'skypemeet') bg-primary @else bg-light @endif">{{ $a->type_tc }}</p>
                                                                                                </div>

                                                                                                <div class='col-12 mt-3'>
                                                                                                    <p class='blackBlueText fs-5 TextBold'>{{ trans('langParticipants') }}</p>
                                                                                                    @php 
                                                                                                        $mentees_ids = Database::get()->queryArray("SELECT mentee_id FROM mentoring_rentezvous_user
                                                                                                                                                    WHERE mentoring_rentezvous_id = ?d",$a->id);
                                                                                                        
                                                                                                    @endphp 
                                                                                                    <div class='d-flex justify-content-start align-items-center flex-wrap'>
                                                                                                        @foreach($mentees_ids as $mentee)
                                                                                                            @php
                                                                                                                $profile_img_mentee = profile_image($mentee->mentee_id, IMAGESIZE_SMALL, 'img-responsive img-circle img-profile'); 
                                                                                                                $mentee_name = Database::get()->querySingle("SELECT givenname FROM user WHERE id = ?d",$mentee->mentee_id)->givenname;
                                                                                                                $mentee_surname = Database::get()->querySingle("SELECT surname FROM user WHERE id = ?d",$mentee->mentee_id)->surname;
                                                                                                            @endphp
                                                                                                            
                                                                                                                <div class='me-2 mb-2'>{!! $profile_img_mentee !!}</div>
                                                                                                                <div class='me-2 mb-2'>{{ $mentee_name }}&nbsp{{ $mentee_surname }}</div>
                                                                                                            
                                                                                                        @endforeach
                                                                                                    </div>
                                                                                                </div>

                                                                                                <div class='col-12 mt-3'>
                                                                                                    <p class='blackBlueText fs-5 TextBold'>{{ trans('langStartDate') }}</p>
                                                                                                    @if($now == $old_now and $now_time > $old_time)
                                                                                                        <p class='text-danger TextMedium'>{!! format_locale_date(strtotime($a->start)) !!}&nbsp<span class='fa fa-times'></span></p>
                                                                                                    @else
                                                                                                        <p class='text-success TextMedium'>
                                                                                                            {!! format_locale_date(strtotime($a->start)) !!}&nbsp<span class='fa fa-check'></span></span>
                                                                                                        </p>
                                                                                                    @endif
                                                                                                </div>

                                                                                                <div class='col-12 mt-3'>
                                                                                                    <p class='blackBlueText fs-5 TextBold'>{{ trans('langEndDate') }}</p>
                                                                                                    @if($now == $old_now and $now_time > $old_time)
                                                                                                        <p class='text-danger TextMedium'>{!! format_locale_date(strtotime($a->end)) !!}&nbsp<span class='fa fa-times'></span></p>
                                                                                                    @else
                                                                                                        <p class='text-success TextMedium'>
                                                                                                            {!! format_locale_date(strtotime($a->end)) !!}&nbsp<span class='fa fa-check'></span></span>
                                                                                                        </p>
                                                                                                    @endif
                                                                                                </div>

                                                                                                <div class='col-12 mt-3'>
                                                                                                    <p class='blackBlueText fs-5 TextBold'>{{ trans('langLink') }}</p>
                                                                                                    <p><a href='{{ $a->api_url }}' target='_blank'>{{ $a->api_url }}</a></p>
                                                                                                </div>

                                                                                                <div class='col-12 mt-3 pb-5'>
                                                                                                    <p class='blackBlueText fs-5 TextBold'>{{ trans('langCode') }}</p>
                                                                                                    <p>
                                                                                                        @if(empty($a->passcode))
                                                                                                            {{ trans('langFreeMeeting') }}
                                                                                                        @else
                                                                                                            {{ $a->passcode }}
                                                                                                        @endif
                                                                                                    </p>
                                                                                                </div>
                                                                                            </div>
                                                                                        </div>
                                                                                    </div>
                                                                                    @php $counter_meetings++; $countPage++; @endphp
                                                                                @endforeach
                                                                            </div>
                                                                        </div>

                                                                    @else
                                                                        <div class='col-12 text-center p-3'><p class='fs-4 blackBlueText TextBold'>{{ trans('langNoExistMeetings')}}</p></div>
                                                                    @endif
   
                                                                </div>
                                                            </div>
                                                        
                                                        </div>
                                                    </div>
                                                </div>

                                            @endforeach
                                        </div>
                                    </div>
                                @endif
                            @endif

                            @if($is_mentee)
                                @php 
                                    $rentezvous = get_rentezvous_of_mentee_for_current_group_with_uid($mentoring_program_id,$group_id,$uid);
                                @endphp
                                
                                <div class='card-group'>
                                    <div class='row ms-0'>
                                        @if(count($rentezvous) > 0)
                                            @php $nextMenteeRentezvous = getNextMeetingForMentee($uid,$group_id,$mentoring_program_id); @endphp
                                            <div class='col-12 ps-0 pe-0'>
                                                <div class='card bgNormalBlueText solidPanel mb-4'>
                                                    <div class="row g-0">

                                                        <div class='col-lg-3 col-md-4 col-12 colMeetingInfoMentor p-3'>
                                                            @php $profile_img = profile_image($uid, IMAGESIZE_LARGE, 'img-responsive img-circle img-profile rounded-2 card-img-top ProfileMeetingCard'); @endphp
                                                            {!! $profile_img !!}
                                                            @php 
                                                                $nameMentee = Database::get()->querySingle("SELECT givenname FROM user WHERE id = ?d",$uid)->givenname; 
                                                                $surnameMentee = Database::get()->querySingle("SELECT surname FROM user WHERE id = ?d",$uid)->surname; 
                                                            @endphp
                                                            <p class='TextBold text-white fs-5 text-center mt-3 mb-3'>{{ $nameMentee }} {{ $surnameMentee }}</p>
                                                            @if(count($nextMenteeRentezvous) > 0) 
                                                                <p class='orangeText TextMedium text-center fs-6 mt-3 mb-1'>{{ trans('langNextMeeting') }}</p>
                                                                @foreach($nextMenteeRentezvous as $n)
                                                                    <p class='text-center text-white TextMedium small-text mb-0'>{!! format_locale_date(strtotime($n['start'])) !!}</p>
                                                                    <p class='text-center text-white TextMedium small-text'>
                                                                        {{ trans('langWithMentor')}}
                                                                        @php 
                                                                            $nameTutor = Database::get()->querySingle("SELECT givenname FROM user WHERE id = ?d",$n['mentor_id'])->givenname; 
                                                                            $surnameTutor = Database::get()->querySingle("SELECT surname FROM user WHERE id = ?d",$n['mentor_id'])->surname; 
                                                                        @endphp
                                                                        {{ $nameTutor }} {{ $surnameTutor }}
                                                                    </p>
                                                                    <p class='orangeText TextMedium text-center fs-6 mt-3 mb-1'>{{ trans('langLink') }}</p>
                                                                    <p class='text-center text-white TextMedium small-text'>{{ $n['api_url'] }}</p>
                                                                    <p class='orangeText TextMedium text-center fs-6 mt-3 mb-1'>{{ trans('langCode') }}</p>
                                                                    @if(!empty($n['passcode']))
                                                                        <p class='text-center text-white TextMedium small-text'>{{ $n['passcode'] }}</p>
                                                                    @else
                                                                        <p class='text-center text-white TextMedium small-text'>{{ trans('langFreeMeeting')}}</p>
                                                                    @endif
                                                                @endforeach
                                                            @endif
                                                        </div>
                                                        <div class='col-lg-9 col-md-8 col-12 bg-white p-0'>
                                                            @php $countPage = 1; @endphp
                                                            @foreach($rentezvous as $r)
                                                                @php 
                                                                    $now = date('Y-m-d', strtotime('now'));
                                                                    $end = date('Y-m-d',strtotime('now + 30days'));

                                                                    $now_time = date('H:i:s', strtotime('now'));
                                                                    $now_day = date('Y-m-d');
                                                
                                                                    $old_now = date('Y-m-d', strtotime($r->start));
                                                                    $old_time = date('H:i:s', strtotime($r->end));
                                                                    $old_start = date('H:i:s', strtotime($r->start))
                                                                    
                                                                @endphp
                                                            
                                                                <div class='panel panel-default rounded-2 mb-3 border-0 overflow-auto'>
                                                                    <div class='panel-body rounded-2 bg-white p-lg-5'>

                                                                        <div class='col-12'>
                                                                            @if($now == $old_now and $now_time <= $old_time and $now_time >= $old_start)
                                                                                <span class='badge bg-info text-white'>{{ trans('langMeetingIsActive') }}</span>
                                                                            @elseif($now == $old_now and $now_time > $old_time) 
                                                                                <span class='badge bg-danger'>{{ trans('langMeetingIsDeactivate') }}</span>
                                                                            @else
                                                                                <span class='badge bg-secondary'>{{ trans('langMeetingNotStartedYet') }}</span>
                                                                            @endif
                                                                            <span class='badge pageCounter float-end fs-3 rounded-circle'>{{ $countPage }}</span>
                                                                        </div>

                                                                        <div class='col-12 mt-3'>
                                                                            <p class='blackBlueText fs-5 TextBold'>{{ trans('langSubject') }}</p>
                                                                            <p>{{ $r->title }}</p>
                                                                        </div>

                                                                        <div class='col-12 mt-3'>
                                                                            <p class='blackBlueText fs-5 TextBold'>{{ trans('langType') }}</p>
                                                                            <p><span class="badge @if($r->type_tc == 'googlemeet') bg-success @elseif ($r->type_tc == 'zoommeet') bg-success @elseif ($r->type_tc == 'skypemeet') bg-primary @else bg-light @endif">{{ $r->type_tc }}</span></p>
                                                                        </div>

                                                                        <div class='col-12 mt-3'>
                                                                            <p class='blackBlueText fs-5 TextBold'>{{ trans('langParticipants') }}</p>
                                                                            <div class='col-12 d-flex justify-content-start align-items-center flex-wrap'>
                                                                                @php 
                                                                                    $profile_img_mentor = profile_image($r->mentor_id, IMAGESIZE_SMALL, 'img-responsive img-circle img-profile'); 
                                                                                    $mentor_name = Database::get()->querySingle("SELECT givenname FROM user WHERE id = ?d",$r->mentor_id)->givenname;
                                                                                    $mentor_surname = Database::get()->querySingle("SELECT surname FROM user WHERE id = ?d",$r->mentor_id)->surname;

                                                                                    $other_mentees_in_same_rentezvous = Database::get()->queryArray("SELECT mentee_id FROM mentoring_rentezvous_user
                                                                                                                                                    WHERE mentoring_rentezvous_id = ?d",$r->id);
                                                                                @endphp  
                                                                                
                                                                                <div class='me-2 mb-2'>{!! $profile_img_mentor !!}</div>
                                                                                <div class='me-2 mb-2'>{{ $mentor_name }}&nbsp{{ $mentor_surname }}</div>
                                                                                    
                                                                                @foreach($other_mentees_in_same_rentezvous as $other) 
                                                                                    @php 
                                                                                        $profile_img_mentee = profile_image($other->mentee_id, IMAGESIZE_SMALL, 'img-responsive img-circle img-profile'); 
                                                                                        $mentee_name = Database::get()->querySingle("SELECT givenname FROM user WHERE id = ?d",$other->mentee_id)->givenname;
                                                                                        $mentee_surname = Database::get()->querySingle("SELECT surname FROM user WHERE id = ?d",$other->mentee_id)->surname;
                                                                                    @endphp 

                                                                                    <div class='me-2 mb-2'>{!! $profile_img_mentee !!}</div>
                                                                                    <div class='me-2 mb-2'>{{ $mentee_name }}&nbsp{{ $mentee_surname }}</div> 
                                                                                @endforeach
                                                                            </div>
                                                                        </div>

                                                                        <div class='col-12 mt-3'>
                                                                            <p class='blackBlueText fs-5 TextBold'>{{ trans('langStartDate') }}</p>
                                                                        
                                                                            @if($now == $old_now and $now_time > $old_time)
                                                                                <p class='text-danger TextMedium'>{!! format_locale_date(strtotime($r->start)) !!}&nbsp<span class='fa fa-times'></span></p>
                                                                            @else
                                                                                <p class='text-success TextMedium'>
                                                                                    {!! format_locale_date(strtotime($r->start)) !!}&nbsp<span class='fa fa-check'></span></span>
                                                                                </p>
                                                                            @endif
                                                                        </div>

                                                                        <div class='col-12 mt-3'>
                                                                            <p class='blackBlueText fs-5 TextBold'>{{ trans('langEndDate') }}</p>

                                                                            @if($now == $old_now and $now_time > $old_time)
                                                                                <p class='text-danger TextMedium'>{!! format_locale_date(strtotime($r->end)) !!}&nbsp<span class='fa fa-times'></span></p>
                                                                            @else
                                                                                <p class='text-success TextMedium'>
                                                                                    {!! format_locale_date(strtotime($r->end)) !!}&nbsp<span class='fa fa-check'></span></span>
                                                                                </p>
                                                                            @endif

                                                                        </div>

                                                                        <div class='col-12 mt-3'>
                                                                            <p class='blackBlueText fs-5 TextBold'>{{ trans('langLink') }}</p>
                                                                            <p><a href='{{ $r->api_url }}' target='_blank'>{{ $r->api_url }}</a></p>
                                                                        </div>

                                                                        <div class='col-12 mt-3'>
                                                                            <p class='blackBlueText fs-5 TextBold'>{{ trans('langCode') }}</p>
                                                                            <p>
                                                                                @if(empty($a->passcode))
                                                                                    &nbsp{{ trans('langFreeMeeting') }}
                                                                                @else
                                                                                    &nbsp{{ $r->passcode }}
                                                                                @endif
                                                                            </p>
                                                                        </div>

                                                                        <div class='col-12 mt-3'>
                                                                            <button class='btn btn-outline-danger small-text rounded-2'
                                                                                    data-bs-toggle="modal" data-bs-target="#WithDrawAlModal{{ $r->id }}">
                                                                                    {{ trans('langWithDrawAlForMentee') }}
                                                                                    @if($now == $old_now and $now_time > $old_time)&nbsp--&nbsp{{ trans('langDelete') }}&nbsp<span class='fa fa-trash'></span> @endif
                                                                            </button>
                                                                        </div>
                                                                    </div>
                                                                </div>
                                                            
                                                        

                                                                <div class="modal fade" id="WithDrawAlModal{{ $r->id }}" tabindex="-1" aria-labelledby="WithDrawAlModalLabel{{ $r->id }}" aria-hidden="true">
                                                                    <form method="post" action="{{ $_SERVER['SCRIPT_NAME'] }}?group_id={!! getInDirectReference($r->group_id) !!}">
                                                                        <div class="modal-dialog modal-md modal-danger">
                                                                            <div class="modal-content">
                                                                                <div class="modal-header">
                                                                                    <h5 class="modal-title" id="WithDrawAlModalLabel{{ $r->id }}">{{ trans('langWithDrawAlForMentee') }}</h5>
                                                                                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                                                                                </div>
                                                                                <div class="modal-body">
                                                                                    {{ trans('MsgWithDrawAlMentee')}}
                                                                                    <input type='hidden' name='rentezvous_id' value="{{ $r->id }}">
                                                                                    <input type='hidden' name='mentee_id' value="{{ $uid }}">

                                                                                </div>
                                                                                <div class="modal-footer">
                                                                                    <a class="btn btn-outline-secondary small-text rounded-2" href="" data-bs-dismiss="modal">{{ trans('langCancel') }}</a>
                                                                                    <button type='submit' class="btn btn-danger small-text rounded-2" name="withDrawAlMentee">
                                                                                        {{ trans('langSubmit') }}
                                                                                    </button>

                                                                                </div>
                                                                            </div>
                                                                        </div>
                                                                    </form>
                                                                </div>

                                                                @php $countPage++; @endphp
                                                            @endforeach
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>
                                        @else
                                        <div class='col-12 p-0'>
                                            <div class='col-12 bg-white p-3 rounded-2 solidPanel'>
                                                <div class='alert alert-warning rounded-2'>{{ trans('langNoExistMeetings')}}</div>
                                            </div>
                                        </div>
                                        @endif
                                    </div>
                                </div>
                            @endif
                            
                        </div>
                    </div>

                   
                
               

        </div>
      
    </div>
</div>

<script>

    $('.showProgramsBtn').on('click',function(){
            localStorage.setItem("MenuMentoring","program");
        });

</script>

@endsection