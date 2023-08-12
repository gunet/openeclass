
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
                                <li class="breadcrumb-item active TextMedium" aria-current="page">{{ $toolName }}</li>
                            </ol>
                        </nav>
                    @endif

                    @include('modules.mentoring.common.common_current_title')

                    <div class='col-12 mb-4'>
                        <div class='col-lg-7 col-md-9 col-12 ms-auto me-auto'>
                            <p class='TextMedium text-center text-justify'>{!! trans('langInfoUserGroupText')!!}</p>
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
                    
                    <!--------------------------------------------------------------------------------------------------------------------------->

                    <div class='col-12'>
                        
                        @if(count($mentees_for_current_group) > 0)
                            <table class='table-default rounded-2' id="table_mentees_for_current_group">
                                <thead>
                                    <tr class='list-header'>
                                        <th>{{ trans('langSurnameName') }}</th>
                                        <th>Email</th>
                                        @if($is_editor_current_group or $is_tutor_of_mentoring_program or $is_admin)<th>{{ trans('langDeleteMenteeFromGroup')}}</th>@endif
                                    </tr>
                                </thead>
                                <tbody>
                                @foreach($mentees_for_current_group as $mentee)
                                    <tr>
                                        <td class='d-flex justify-content-start align-items-center'>
                                            @php $profile_img = profile_image($mentee->id, IMAGESIZE_SMALL, 'img-responsive img-circle img-profile'); @endphp
                                            {!! $profile_img !!}&nbsp
                                            <a class='TextSemiBold' href='{{ $urlAppend }}modules/mentoring/profile/user_profile.php?user_id={!! getInDirectReference($mentee->id) !!}'>{{ $mentee->givenname }}&nbsp{{ $mentee->surname }}</a>
                                        </td>
                                        <td>
                                            @if(!empty($mentee->email))
                                                {{ $mentee->email }}
                                            @else
                                                <p class='TextSemiBold small-text'>{{ trans('langNoEmail')}} </p>
                                            @endif
                                        </td>
                                        @if($is_editor_current_group or $is_tutor_of_mentoring_program or $is_admin)
                                            <td>
                                                <button class="btn deleteAdminBtn ms-2 rounded-2"
                                                    data-bs-toggle="modal" data-bs-target="#DeleteMenteeModal{{ $mentee->id }}" >
                                                    <span class='fa-solid fa-trash-can'></span>
                                                </button>

                                                <div class="modal fade" id="DeleteMenteeModal{{ $mentee->id }}" tabindex="-1" aria-labelledby="DeleteMenteeModalLabel{{ $mentee->id }}" aria-hidden="true">
                                                    <form method="post" action="{{ $_SERVER['SCRIPT_NAME'] }}?group_id={!! getIndirectReference($group_id) !!}">
                                                        <div class="modal-dialog modal-md modal-danger">
                                                            <div class="modal-content">
                                                                <div class="modal-header">
                                                                    <h5 class="modal-title" id="DeleteMenteeModalLabel{{ $mentee->id }}">
                                                                        {{ trans('langDelete') }}</h5>
                                                                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close">
                                                                    </button>
                                                                </div>
                                                                <div class="modal-body text-start">
                                                                    {!! trans('langDeleteMenteeFromGroupCheck') !!}
                                                                    <input type='hidden' name='del_user_id' value='{{ $mentee->id }}'>
                                                                </div>
                                                                <div class="modal-footer">
                                                                    <a class="btn cancelAdminBtn" href="" data-bs-dismiss="modal">{{ trans('langCancel') }}</a>
                                                                    <button type='submit' class="btn deleteAdminBtn" name="delete_mentee_from_group">
                                                                        {{ trans('langDelete') }}
                                                                    </button>

                                                                </div>
                                                            </div>
                                                        </div>
                                                    </form>
                                                </div>
                                            </td>
                                        @endif
                                    </tr>
                                @endforeach
                                </tbody>
                            </table>
                        @else
                            <div class='col-12 mt-4'>
                                <div class='alert alert-warning'>
                                <i class='fa-solid fa-triangle-exclamation fa-lg'></i>
                                <span>{{ trans('langNoMenteesExistInGroup') }}</span></div></div>
                        @endif
                    </div>

                

        </div>
      
    </div>
</div>


<script type="text/javascript">
    $(document).ready( function () {
   
        $('#table_mentees_for_current_group').DataTable();

        $('.showProgramsBtn').on('click',function(){
            localStorage.setItem("MenuMentoring","program");
        });

    } );
</script>

@endsection
