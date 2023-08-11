
@extends('layouts.default')

@section('content')


<div class="col-12 main-section">
    <div class='{{ $container }}'>
        <div class="row m-auto">

                    @if($isCommonGroup == 1)
                        <nav class='breadcrumb_mentoring' style="--bs-breadcrumb-divider: url(&#34;data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' width='8' height='8'%3E%3Cpath d='M2.5 0L1 1.5 3.5 4 1 6.5 2.5 8l4-4-4-4z' fill='currentColor'/%3E%3C/svg%3E&#34;);" aria-label="breadcrumb">
                            <ol class="breadcrumb">
                                <li class="breadcrumb-item"><a class='TextSemiBold' href="{{ $urlAppend }}modules/mentoring/mentoring_platform_home.php"><span class='fa fa-home'></span>&nbsp{{ trans('langHomeMentoringPlatform') }}</a></li>
                                <li class="breadcrumb-item"><a class='TextSemiBold showProgramsBtn' href="{{ $urlAppend }}modules/mentoring/programs/show_programs.php">{{ trans('langOurMentoringPrograms') }}</a></li>
                                <li class="breadcrumb-item"><a class='TextSemiBold' href="{{ $urlAppend }}modules/mentoring/programs/myprograms.php">{{ trans('langMyPrograms') }}</a></li>
                                <li class="breadcrumb-item"><a class='TextSemiBold' href="{{ $urlAppend }}mentoring_programs/{{ $mentoring_program_code }}/index.php">{!! show_mentoring_program_title($mentoring_program_code) !!}</a></li>
                                <li class="breadcrumb-item"><a class='TextSemiBold' href="{{ $urlAppend }}modules/mentoring/programs/group/select_group.php">{{ trans('langMentoringSpace')}}</a></li>
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
                                <li class="breadcrumb-item active TextMedium" aria-current="page">{{ $toolName }}</li>
                            </ol>
                        </nav>
                    @endif

                    @include('modules.mentoring.common.common_current_title')

                    <div class='col-12 mb-4 ps-3 pe-3'>
                        <div class='col-lg-7 col-md-9 col-12 ms-auto me-auto ps-3 pe-3'>
                            @if($isCommonGroup == 1)
                            <p class='TextMedium text-center text-justify'>{!! trans('langInfoWallGroupSpaceText')!!}</p>
                            @else
                            <p class='TextMedium text-center text-justify'>{!! trans('langInfoGroupSpaceText')!!}</p>
                            @endif
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
                   
                   <div class='col-lg-12 col-6'>{!! $action_bar !!}</div>
                   <div class='col-6 d-block d-lg-none'>
                        <!-- TOOLS OF GROUP MOBILE-->
                        <a class="ShowSidebarToolsGroup btn btn-sm bgEclass blackBlueText float-end mb-3">
                            <span class='fa fa-bars fs-5'></span>
                        </a>
                        <div class='SidebarToolsGroup'>
                            <a id='closeSidebarToolsGroup' class='btn float-end mb-3 d-block d-lg-none'><span class='fa fa-times fs-3 text-danger'></span></a>
                            <div class='panel panel-admin rounded-2 border-1 BorderSolid bg-white mt-5 py-md-4 px-md-4 py-3 px-3 shadow-none'>
                                <div class='panel-heading bg-body p-0'>
                                    <span class='text-uppercase blackBlueText TextBold fs-6'>{{ trans('langToolsGroup') }}</span>
                                </div>
                                <div class='panel-body p-0 rounded-2'>
                                    <div class='col-12'>
                                        @include('modules.mentoring.layouts.group_tools',['mentoring_program_id' => $mentoring_program_id , 'group_id' => $group_id])
                                    </div>
                                </div>
                            </div>
                        </div>
                   </div>
                    
                    <!-- DESCRIPTION -->
                    @php $des = Database::get()->querySingle("SELECT description FROM mentoring_group WHERE id = ?d",$group_id); @endphp
                    @if($des)
                        <div class='col-12 mb-0'>
                            <div class='panel panel-admin rounded-2 border-1 BorderSolid bg-white py-md-4 px-md-4 py-3 px-3 shadow-none'>
                                <div class='panel-heading bg-body p-0'>
                                    <span class='text-uppercase blackBlueText TextBold fs-6'>{{ trans('langDescription') }}</span>
                                </div>
                                <div class='panel-body p-0 rounded-2'>
                                    <div class='col-12'>
                                        @if(!empty($des->description))
                                            {!!  $des->description !!}
                                        @else
                                            {{ trans('langNoInfoAvailable') }}
                                        @endif
                                    </div>
                                </div>
                            </div>
                        </div>
                    @endif
                    
                    <!-- CONTACT WITH TUTOR OF GROUP -->
                    @if($isCommonGroup != 1)
                        <div class='col-xl-9 col-lg-8 col-12 pe-lg-3 d-lg-flex align-items-lg-strech'>
                            <div class='panel panel-admin w-100 rounded-2 border-1 BorderSolid bg-white mt-lg-3 mt-3 py-md-4 px-md-4 py-3 px-3 shadow-none'>
                                <div class='panel-heading bg-body p-0'>
                                    <span class='text-uppercase blackBlueText TextBold fs-6'>{{ trans('langContact') }}</span>
                                </div>
                                <div class='panel-body p-0 rounded-2'>
                                    <div class='col-12'>
                                        <div class='card-group'>
                                                @php 
                                                    $details_editor_group = get_details_of_editor_for_current_group($group_id);
                                                    $counter_hr = 0;
                                                @endphp

                                                @if(count($details_editor_group) > 0)
                                                
                                                    @foreach($details_editor_group as $editor)
                                                        @php 
                                                            
                                                            $dates_availability_for_current_mentor = Database::get()->queryArray("SELECT *FROM mentoring_mentor_availability_group
                                                                                                                                WHERE user_id = ?d AND group_id = ?d AND end > NOW()
                                                                                                                                ORDER BY start ASC",$editor->id,$group_id);
                                                        @endphp
                                                        
                                                        <div class='col-12'>
                                                            <div class='card w-100 border-0'>
                                                                    @php $profile_img = profile_image($editor->id, IMAGESIZE_LARGE, 'img-responsive img-profile card-img-top cardImage ImageTutorGroup'); @endphp
                                                                    <div class="row g-0 mb-5">
                                                                        <div class='col-xl-3 col-md-4'>{!! $profile_img !!}</div>
                                                                        <div class='col-xl-9 col-md-8'>
                                                                            <div class='card-body border-0 pt-md-0 pb-md-0'>

                                                                                <p class='small-text normalBlueText text-md-start text-center mb-0'>{{ trans('langTutor') }}</p>
                                                                                <p class="card-title TextBold fs-5 blackBlueText text-md-start text-center mb-3">{{ $editor->givenname }}&nbsp{{ $editor->surname }}</p>
                                                                                
                                                                                
                                                                                @if(($is_editor_current_group and $uid == $editor->id) or $is_tutor_of_mentoring_program or $is_admin)
                                                                                    <p class='card-text blackBlueText text-md-start text-center mb-1'>{!! trans('langCreateMeeting') !!}</p>
                                                                                    <a class='btn btn-sm viewProgramWidth text-uppercase small-text TextBold rounded-2 MobileButton' href='{{ $urlAppend }}mentoring_programs/{{ $mentoring_program_code }}/index.php?showcal={!! getIndirectReference($editor->id) !!}&group_id={!! getIndirectReference($group_id) !!}'>
                                                                                        {{ trans('langCreate') }}
                                                                                    </a>
                                                                                    <p class='card-text blackBlueText text-md-start text-center mt-3 mb-1'>{!! trans('langAddAvailableDateHour') !!}</p>
                                                                                    <a class='btn btn-sm viewProgramWidth text-uppercase small-text TextBold rounded-2 MobileButton' href='{{ $urlAppend }}modules/mentoring/programs/group/datesMentor/add_date_by_mentor.php?showcal={!! getIndirectReference($editor->id) !!}&group_id={!! getIndirectReference($group_id) !!}'>
                                                                                        {!! trans('langAdd') !!}
                                                                                    </a>
                                                                                @endif

                                                                                <!-- if is mentee of group then create booking for current editor of group -->
                                                                                @if($is_mentee)
                                                                                    @php 
                                                                                        $existDateForTutorOfGroup = Database::get()->querySingle("SELECT COUNT(id) as c FROM mentoring_mentor_availability_group
                                                                                                                    WHERE user_id = ?d AND group_id = ?d
                                                                                                                    AND mentoring_program_id = ?d",$editor->id,$group_id,$mentoring_program_id)->c; 
                                                                                    @endphp
                                                                                    @if($existDateForTutorOfGroup > 0)
                                                                                        <p class='card-text blackBlueText text-md-start text-center mb-2'>{{ trans('langBelowDaysMentor') }}</p>
                                                                                        <a class='btn btn-sm viewProgramWidth text-uppercase small-text TextBold rounded-2 DoBookingMentee'
                                                                                            href='{{ $urlAppend }}modules/mentoring/programs/group/bookingsMentee/booking_space.php?group_id={!! getIndirectReference($group_id) !!}&mentor_id={!! getInDirectReference($editor->id) !!}'>{!! trans('langAddBooking') !!}
                                                                                        </a>
                                                                                    @endif
                                                                                @endif
                                                                                
                                                                            </div>
                                                                        </div>
                                                                    </div>
                                                                
                                                                
                                                                

                                                                    @if(count($dates_availability_for_current_mentor) > 0)
                                                                    <div class='card-footer border-0 p-0 bg-white' style=' z-index: 0 !important;'>
                                                                        <div class='col-12 overflow-auto mt-3 mb-0'>
                                                                            <p class='fs-6 normalBlueText text-center TextBold'>{{ trans('langDatesHourTutorAvailability')}}</p>
                                                                            @include('modules.mentoring.common.viewDatesOfEditorGroup',['editorId' => $editor->id, 'group_id' => $group_id])
                                                                        </div>
                                                                    </div>
                                                                    @endif

                                                                    @if($counter_hr < (count($details_editor_group)-1))<div class='mb-5'></div>@endif
                                                                   

                                                            </div>
                                                    
                                                            @if($counter_hr < (count($details_editor_group)-1))<div class='@if (count($dates_availability_for_current_mentor) > 0) mt-5 @else mt-3 @endif'></div>@endif
                                                            @php $counter_hr++ @endphp
                                                        </div>
                                                    @endforeach
                                                @endif
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    @else
                        <div class='col-xl-9 col-lg-8 col-12 pe-lg-1 d-lg-flex align-items-lg-strech'>
                            <div class='panel panel-admin rounded-2 border-1 w-100 BorderSolid bg-white mt-lg-3 mt-3 py-md-4 px-md-4 py-3 px-3 shadow-none'>
                                <div class='panel-heading bg-body p-0'>
                                    <span class='text-uppercase blackBlueText TextBold fs-6'>{{ trans('langWall') }}</span>
                                </div>
                                <div class='panel-body p-0 rounded-2'>
                                    @include('modules.mentoring.layouts.group_wall_function',['is_editor_wall_common_group' => $is_editor_wall_common_group , 'group_id' => $group_id])
                                </div>
                            </div>
                        </div>
                    @endif

                    <!-- TOOLS OF GROUP DESKTOP-->
                    <div class='col-xl-3 col-lg-4 @if($isCommonGroup == 0) ps-lg-0 @endif mt-lg-0 d-none d-lg-block'>
                        <div class='panel panel-admin rounded-2 border-1 BorderSolid bg-white mt-lg-3 mt-3 py-md-4 px-md-4 py-3 px-3 shadow-none'>
                            <div class='panel-heading bg-body p-0'>
                                <span class='text-uppercase blackBlueText TextBold fs-6'>{{ trans('langToolsGroup') }}</span>
                            </div>
                            <div class='panel-body p-0 rounded-2'>
                                <div class='col-12'>
                                    @include('modules.mentoring.layouts.group_tools',['mentoring_program_id' => $mentoring_program_id , 'group_id' => $group_id])
                                </div>
                            </div>
                        </div>
                    </div>

                    @if($isCommonGroup == 1)
                        @php 
                            $posts_per_page = 10;
                            $posts = Database::get()->queryArray("SELECT id, user_id, content, extvideo, FROM_UNIXTIME(timestamp) as datetime, pinned  FROM mentoring_wall_post WHERE mentoring_program_id = ?d AND group_id = ?d ORDER BY pinned DESC, timestamp DESC LIMIT ?d", $mentoring_program_id, $group_id, $posts_per_page);
                        @endphp

                        <div class='col-xl-9 col-lg-8 col-12 pe-lg-1'>
                            <div class='panel panel-admin rounded-2 border-1 BorderSolid bg-white mt-lg-3 mt-3 py-md-4 px-md-4 py-3 px-3 shadow-none'>
                                <div class='panel-heading bg-body p-0'>
                                    <span class='text-uppercase blackBlueText TextBold fs-6'>{{ trans('langPostWall') }}</span>
                                </div>
                                <div class='panel-body p-0 rounded-2'>
                                    @if (count($posts) == 0)
                                        <p class="TextRegular blackBlueText">{{ trans('langNoWallPostsMentoring') }}</p>
                                    @else
                                        {!! generate_infinite_container_html($posts, $posts_per_page, 2) !!}<div class='mb-4'></div>
                                    @endif
                                </div>
                            </div>
                        </div>
                    @endif

                    <div class="modal fade" id="DeleteGroupModal" tabindex="-1" aria-labelledby="DeleteGroupModalLabel" aria-hidden="true">
                        <form method="post" action="{{ $_SERVER['SCRIPT_NAME'] }}?space_group_id={!! getInDirectReference($group_id) !!}">
                            <div class="modal-dialog modal-md modal-danger">
                                <div class="modal-content">
                                    <div class="modal-header">
                                        <h5 class="modal-title" id="DeleteGroupModalLabel">{{ trans('langDeleteMentoringGroup') }}</h5>
                                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                                    </div>
                                    <div class="modal-body">
                                        {!! trans('langDeleteMentoringGroupMsg') !!}
                                        

                                    </div>
                                    <div class="modal-footer">
                                        <a class="btn btn-outline-secondary small-text rounded-2" href="" data-bs-dismiss="modal">{{ trans('langCancel') }}</a>
                                        <button type='submit' class="btn btn-danger small-text rounded-2" name="delete_group">
                                            {{ trans('langDelete') }}
                                        </button>

                                    </div>
                                </div>
                            </div>
                        </form>
                    </div>
                        

                    <!--------------------------------------------------------------------------------------------------------------------------->

                    

                

        </div>
      
    </div>
</div>

<script type="text/javascript">
    $(document).ready( function () {
   
        $('#table_mentees_for_current_group').DataTable();

        $('#startdate,#enddate').datetimepicker({
            format: 'yyyy-mm-dd hh:ii',
            pickerPosition: 'bottom-right',
            language: '{{ $language }}',
            autoclose: true
        });

        $('.showProgramsBtn').on('click',function(){
            localStorage.setItem("MenuMentoring","program");
        });

        $('.ShowSidebarToolsGroup').on('click',function(){
            $('.SidebarToolsGroup').addClass('SidebarOn');
        });
        $('#closeSidebarToolsGroup').on('click',function(){
            $('.SidebarToolsGroup').removeClass('SidebarOn');
        });

    } );
</script>
@endsection
