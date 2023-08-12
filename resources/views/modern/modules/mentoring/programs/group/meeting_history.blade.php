
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

                    <div class='col-12 mb-4'>
                        <div class='col-lg-7 col-md-9 col-12 ms-auto me-auto'>
                            <p class='TextMedium text-center text-justify'>{!! trans('langInfoHistoryMeetingsText')!!}</p>
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
                   
                    
                    {!! $action_bar !!}
                  
                    
                    @if(count($history_rentezvous) > 0)
                        <div class='col-12'>
                            <table class='table-default rounded-2' id="table_history_rentezvous">
                                <thead>
                                    <tr class='list-header'>
                                        <th>{{ trans('langSubject') }}</th>
                                        <th>{{ trans('langParticipants') }}</th>
                                        <th>{{ trans('langFrom') }}</th>
                                        <th>{{ trans('langUntil') }}</th>
                                        <th>{{ trans('lampAnalyticsStatus') }}</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach($history_rentezvous as $r)
                                        <tr>
                                            <td>
                                                {{ $r->title }}
                                            </td>
                                            <td>
                                                @php 
                                                    $givenname_mentor = Database::get()->querySingle("SELECT givenname FROM user WHERE id = ?d",$r->mentor_id)->givenname;
                                                    $surname_mentor = Database::get()->querySingle("SELECT surname FROM user WHERE id = ?d",$r->mentor_id)->surname;

                                                    $mentees_ids = Database::get()->queryArray("SELECT mentee_id FROM mentoring_rentezvous_user WHERE mentoring_rentezvous_id = ?d",$r->id);

                                                @endphp
                                                {{ $givenname_mentor }}&nbsp{{ $surname_mentor }}
                                                @foreach($mentees_ids as $mentee)
                                                    @php 
                                                        $givenname_mentee = Database::get()->querySingle("SELECT givenname FROM user WHERE id = ?d",$mentee->mentee_id)->givenname;
                                                        $surname_mentee = Database::get()->querySingle("SELECT surname FROM user WHERE id = ?d",$mentee->mentee_id)->surname;
                                                    @endphp
                                                    ,&nbsp{{ $givenname_mentee }}&nbsp{{ $surname_mentee }}
                                                @endforeach
                                            </td>
                                            <td> {!! format_locale_date(strtotime($r->start)) !!}</td>
                                            <td> {!! format_locale_date(strtotime($r->end)) !!}</td>
                                            <td>
                                                @php $now = date('Y-m-d H:i:s', strtotime('now')); @endphp
                                                @if($now > $r->end) 
                                                    <div class='d-flex'>
                                                        <span class='text-danger TextSemiBold'>{{ trans('langHasExpired') }}</span>
                                                    
                                                        @if($is_editor_current_group or $is_tutor_of_mentoring_program or $is_admin)
                                                            &nbsp
                                                            <a href="{{ $urlAppend }}modules/mentoring/programs/group/meeting_space.php?group_id={!! getInDirectReference($group_id) !!}&del_meeting_id={!! getInDirectReference($r->id) !!}&show_history">
                                                                <span class="fa-solid fa-trash-can text-danger" data-bs-toggle="tooltip" data-bs-placement="bottom" title data-bs-original-title="{{ trans('langDelete') }}"></span>
                                                            </a>
                                                        @endif
                                                    </div>
                                                @else
                                                        <span class='text-success TextSemiBold text-capitalize'>{{ trans('langActive') }}</span>
                                                @endif
                                            </td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                    @else
                        <div class='col-12 mt-4'>
                            <div class='alert alert-warning'>
                            <i class='fa-solid fa-triangle-exclamation fa-lg'></i>
                            <span>{{ trans('langNoExistHistoryRentezvous')}}</span></div>
                        </div>
                    @endif

                   
                
               

        </div>
      
    </div>
</div>

<script type="text/javascript">
    $(document).ready( function () {

        $('#table_history_rentezvous').DataTable();

        $('.showProgramsBtn').on('click',function(){
            localStorage.setItem("MenuMentoring","program");
        });
    } );
</script>

@endsection