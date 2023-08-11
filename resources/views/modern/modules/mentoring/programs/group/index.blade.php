@extends('layouts.default')

@section('content')


<div class="col-12 main-section">
    <div class='{{ $container }}'>
        <div class="row m-auto">

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
                    
                    @include('modules.mentoring.common.common_current_title')

                    <div class='col-12 mb-4 ps-3 pe-3'>
                        <div class='col-lg-7 col-md-9 col-12 ms-auto me-auto ps-3 pe-3'>
                            <p class='TextMedium text-center text-justify'>{!! trans('langInfoDivGroupSpaceText')!!}</p>
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
                   
                    
                    
                    <div class='col-3 d-flex justify-content-start align-items-center'>
                        {!! $action_bar !!}
                    </div>
                    

                    @if($isCommonGroup == 0)
                        <!-- only tutor of program can create a group -->
                        @if($is_editor_mentoring_program or $is_admin)

                            <div class='col-9 d-flex justify-content-end align-items-start'>
                                <div class="dropdown">
                                    <button class="btn submitAdminBtn dropdown-toggle manageGroupButton" type="button" id="dropdownToolsGroupTutorOfProgram" data-bs-display='static' data-bs-toggle="dropdown" aria-expanded="false">
                                        {{ trans('langManageGroup')}}
                                    </button>
                                    <ul class="dropdown-menu dropdown-menu-end m-0 p-0 GroupsToolsMentoringDropdown" aria-labelledby="dropdownToolsGroupTutorOfProgram">
                                        <li>
                                            <a href='{{ $urlAppend }}modules/mentoring/programs/group/create_group.php' class='list-group-item border-bottom-0 small-text TextSemiBold text-start w-100'>
                                                <span class='fa fa-plus'></span>&nbsp
                                                {{ trans('langCreateMentoringGroup') }}
                                            </a>
                                        </li>
                                        <li>
                                            <button class="list-group-item border-bottom-0 small-text TextSemiBold text-start w-100"
                                                data-bs-toggle="modal" data-bs-target="#SettingsRegistrationModal" >
                                                <span class='fa fa-cogs'></span>&nbsp{{ trans('langSettingsRegistrationOfGroup2') }}
                                            </button>
                                        </li>
                                        <li>
                                            <button class="list-group-item deleteGroups border-bottom-0 small-text TextSemiBold text-start w-100"
                                                data-bs-toggle="modal" data-bs-target="#DeleteAllGroupsModal" >
                                                <span class='fa fa-times'></span>&nbsp{{ trans('langDeleteAllMentoringGroups') }}
                                            </button>
                                        </li>
                                    </ul>
                                </div>


                                <div class="modal fade" id="SettingsRegistrationModal" tabindex="-1" aria-labelledby="SettingsRegistrationModalLabel" aria-hidden="true">
                                    <form method="post" action="{{ $urlAppend }}modules/mentoring/programs/group/group_space.php?fromRegModals=true">
                                        <div class="modal-dialog modal-md modal-primary">
                                            <div class="modal-content">
                                                <div class="modal-header">
                                                    <h5 class="modal-title" id="SettingsRegistrationModalLabel">{{ trans('langSettingsRegistrationOfGroup') }}</h5>
                                                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close">
                                                    </button>
                                                </div>
                                                <div class="modal-body">
                                                    <div class='form-group'>
                                                        <label class='col-12 control-label-notes mb-2'>{{ trans('langAbilityRegisterToOneGroup') }}</label>
                                                        <div class='col-sm-12'>
                                                            <div class='checkbox'>
                                                                <label class='label-container'>
                                                                    <input id='id_reg_one' type='checkbox' name='reg_one' value='1' {!! $setting_reg == 1 ? 'checked' : '' !!}>
                                                                    <span class='checkmark'></span>{{ trans('langMenteeRegisterToOneGroup') }}
                                                                </label>
                                                            </div>
                                                        </div>
                                                    </div>

                                                    <div class='form-group mt-4'>
                                                        <label class='col-12 control-label-notes mb-2'>{{ trans('langAbilityRegisterToManyGroup') }}</label>
                                                        <div class='col-sm-12'>
                                                            <div class='checkbox'>
                                                                <label class='label-container'>
                                                                    <input id='id_reg_many' type='checkbox' name='reg_many' value='0' {!! $setting_reg == 0 ? 'checked' : '' !!}>
                                                                    <span class='checkmark'></span>{{ trans('langMenteeRegisterToManyGroup') }}
                                                                </label>
                                                            </div>
                                                        </div>
                                                    </div>
                                                    
                                                </div>
                                                <div class="modal-footer">
                                                    <a class="btn btn-outline-secondary small-text rounded-2" href="" data-bs-dismiss="modal">{{ trans('langCancel') }}</a>
                                                    <button type='submit' class="btn btn-primary small-text rounded-2" name="action_settings_registration_of_mentee_to_group" value="settings">
                                                        {{ trans('langSubmit') }}
                                                    </button>
                                                    
                                                </div>
                                            </div>
                                        </div>
                                    </form>
                                </div>

                                <div class="modal fade" id="DeleteAllGroupsModal" tabindex="-1" aria-labelledby="DeleteAllGroupsModalLabel" aria-hidden="true">
                                    <form method="post" action="{{ $urlAppend }}modules/mentoring/programs/group/index.php">
                                        <div class="modal-dialog modal-md modal-danger">
                                            <div class="modal-content">
                                                <div class="modal-header">
                                                    <h5 class="modal-title" id="DeleteAllGroupsModalLabel">{{ trans('langDeleteAllMentoringGroups') }}</h5>
                                                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close">
                                                    </button>
                                                </div>
                                                <div class="modal-body">
                                                    @if(count($groups) > 0)
                                                        {!! trans('langQuestionDeleteAllMentoringGroups') !!}
                                                    @else
                                                        {{ trans('langNoGroupMentees') }}
                                                    @endif
                                                </div>
                                                <div class="modal-footer">
                                                    <a class="btn btn-outline-secondary small-text rounded-2" href="" data-bs-dismiss="modal">{{ trans('langCancel') }}</a>
                                                    <button type='submit' class="btn btn-danger small-text @if(count($groups) == 0) pe-none opacity-help @endif rounded-2" name="deleteAllGroups" value="deleteGroups">
                                                        {{ trans('langDelete') }}
                                                    </button>
                                                    
                                                </div>
                                            </div>
                                        </div>
                                    </form>
                                </div>
                            </div>
                        @endif
                    @endif

       
                    @if(count($groups) > 0)
                       <div class='col-12'>
                           <div class='row'>
                            
                                @foreach($groups as $g)
                                    @php
                                        $html_group = register_in_group_by_settings($g->id,$mentoring_program_id,$mentoring_program_code);
                                        $is_common_group = Database::get()->querySingle("SELECT common FROM mentoring_group WHERE id = ?d AND mentoring_program_id = ?d",$g->id,$mentoring_program_id)->common;
                                        $mentor = get_group_mentor_for_mentoring_program($g->group_id);
                                        $is_editor_current_group = get_editor_for_current_group($uid,$g->group_id);
                                        $is_mentee_for_current_group = check_if_uid_is_mentee_for_current_group($uid,$g->group_id);
                                    @endphp

                                    <div class='@if($is_common_group) col-12 @else col-lg-12 col-12 @endif mb-3'>
                                        <div class='panel panel-admin rounded-2 border-1 BorderSolid bg-white py-md-4 px-md-4 py-3 px-3 shadow-none'>
                                            <div class='panel-body p-3 rounded-2'>
                                                <div class='col-12'>
                                                    @if($is_editor_current_group or $is_tutor_of_mentoring_program or $is_mentee_for_current_group)
                                                        <a class='text-start TextSemiBold fs-6 d-inline-flex justify-conten-start align-items-start' href='{{ $urlAppend }}modules/mentoring/programs/group/group_space.php?space_group_id={!! getIndirectReference($g->group_id) !!}'>
                                                            {{ $g->name }}
                                                            @if($is_editor_current_group)
                                                                &nbsp
                                                                <button type="button" class="btn btn-transparent btn-sm border-0 p-0 ms-2 text-success" data-bs-toggle="tooltip" data-bs-placement="top" title="{{ trans('langEditorOfGroup') }}">
                                                                    <span class='badge bg-success rounded-pill'><span class='fa fa-check small-text text-white'></span></span>
                                                                </button>
                                                            @endif
                                                            @if($is_tutor_of_mentoring_program)
                                                                &nbsp
                                                                <button type="button" class="btn btn-transparent btn-sm border-0 p-0 ms-2 text-success" data-bs-toggle="tooltip" data-bs-placement="top" title="{{ trans('langMentoringTutor') }}">
                                                                    <span class='badge bg-success rounded-pill'><span class='fa fa-check small-text text-white'></span></span>
                                                                </button>
                                                            @endif
                                                            @if($is_mentee_for_current_group)
                                                                &nbsp
                                                                <button type="button" class="btn btn-transparent btn-sm border-0 p-0 ms-2 text-success" data-bs-toggle="tooltip" data-bs-placement="top" title="{{ trans('langMyGroup') }}">
                                                                    <span class='badge bg-success rounded-pill'><span class='fa fa-check small-text text-white'></span></span>
                                                                </button>
                                                            @endif
                                                        </a>
                                                        
                                                    @else
                                                        <p class='text-start TextSemiBold fs-6 d-inline'>{{ $g->name }}</p>
                                                    @endif
                                                    @php 
                                                        $members_of_current_group = Database::get()->querySingle("SELECT COUNT(user_id) as ui FROM mentoring_group_members
                                                                                        WHERE group_id = ?d AND is_tutor = ?d AND status_request = ?d",$g->id,0,1)->ui;
                                                                    
                                                        $max_members_of_current_group = Database::get()->querySingle("SELECT max_members FROM mentoring_group
                                                                                        WHERE id = ?d AND mentoring_program_id = ?d",$g->id,$mentoring_program_id)->max_members;
                                                    @endphp
                                                    <p class='d-inline float-end'>
                                                        @if($max_members_of_current_group == 0)
                                                            <span class='badge bg-info text-white'>{{ trans('langGroupMentoringMembers') }}:--</span>
                                                        @else
                                                            @if($members_of_current_group < $max_members_of_current_group)
                                                                <span class='badge bg-info text-white'>{{ trans('langGroupMentoringMembers') }}:&nbsp{{ $members_of_current_group }}/{{ $max_members_of_current_group }}</span>
                                                            @else
                                                                <span class='badge bg-warning text-white'>{{ trans('langGroupMentoringMembers') }}:&nbsp{{ $members_of_current_group }}/{{ $max_members_of_current_group }}</span>
                                                            @endif
                                                        @endif
                                                    </p>
                                                </div>
                                                <div class='col-12 mt-3'>
                                                    @if($mentor)
                                                        <p class='TextBold text-start mb-2 small-text'>{{ trans('langEditorsOfGroup') }}</p>
                                                        <ul>
                                                        @foreach($mentor as $m)
                                                        <li>
                                                            <button type="button" class="btn btn-transparent btn-sm border-0 p-0 mb-2" data-bs-toggle="tooltip" data-bs-placement="top" title="{{ trans('langEditorOfGroup') }}">
                                                                @php $profile_img = profile_image($m->id, IMAGESIZE_SMALL, 'img-responsive img-circle img-profile'); @endphp
                                                                <span>{!! $profile_img !!}</span>
                                                                <span class='TextSemiBold help-block'>{{ $m->givenname }}&nbsp{{ $m->surname }}</span>
                                                            </button>
                                                        </li>
                                                        @endforeach
                                                        </ul>
                                                    @endif
                                                </div>
                                                   
                                                <p class='text-start TextSemiBold small-text mt-3'>{!! $g->description !!}</p>
                                            </div>
                                            <div class='panel-footer rounded-2 bg-white'>
                                                @php 
                                                    $is_mentee_of_program = Database::get()->querySingle("SELECT COUNT(user_id) as ui FROM mentoring_programs_user
                                                                                                         WHERE user_id = ?d AND status = ?d AND tutor = ?d AND mentor = ?d 
                                                                                                         AND is_guided = ?d",$uid,USER_STUDENT,0,0,1)->ui;

                                                    $is_mentor_of_program = Database::get()->querySingle("SELECT COUNT(user_id) as ui FROM mentoring_programs_user
                                                                                                         WHERE user_id = ?d AND status = ?d AND tutor = ?d AND mentor = ?d 
                                                                                                         AND is_guided = ?d",$uid,USER_TEACHER,0,1,0)->ui;
                                                @endphp
                                                @if($is_mentee_of_program > 0 or ($is_mentor_of_program > 0 and $is_common_group))
                                                    {!! $html_group !!}
                                                @endif
                                            </div>
                                        </div>
                                    </div>
                                @endforeach
                           </div>
                       </div>
                    @else
                        <div class='col-12'>
                            <div class='col-12 bg-white p-3 rounded-2 solidPanel'><div class='alert alert-warning rounded-2'>{{ trans('langNoGroupMentees') }}</div></div>
                        </div>
                    @endif
                  
                

        </div>
      
    </div>
</div>



<script type='text/javascript'>
    $(document).ready(function(){
      
        if($('#id_reg_one').is(":checked")){
           $('#id_reg_many').attr('disabled',true);
        }
        $('#id_reg_one').on('click',function(){
            if($('#id_reg_one').is(":checked")){
                $('#id_reg_many').attr('disabled',true);
            }else{
                $('#id_reg_many').prop("disabled", false);
            }
            
        });


        if($('#id_reg_many').is(":checked")){
           $('#id_reg_one').attr('disabled',true);
        }
        $('#id_reg_many').on('click',function(){
            if($('#id_reg_many').is(":checked")){
                $('#id_reg_one').attr('disabled',true);
            }else{
                $('#id_reg_one').prop("disabled", false);
            }
            
        });

        $('.showProgramsBtn').on('click',function(){
            localStorage.setItem("MenuMentoring","program");
        });

    })
   
</script>
@endsection