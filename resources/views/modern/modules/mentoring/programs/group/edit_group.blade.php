
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
                   
                    <div class='col-lg-6 col-12 d-none d-md-none d-lg-block'>
                          <div class='col-12 h-100 left-form'></div>
                    </div>
                    <div class='col-lg-6 col-12'>
                        <div class='form-wrapper form-edit rounded p-3'>
                          <form class='form-horizontal' role='form' method='post' name='createform' action="{{ $_SERVER['SCRIPT_NAME'] }}" enctype="multipart/form-data">
                            <div class='col-12'><p class='TextBold text-end'>(<span class='text-danger'>*</span>) {{trans('langCPFFieldRequired')}}</p></div>
                        
                            <div class="form-group">
                                <label class='col-sm-6 control-label-notes'>{{ trans('langGroupName') }}<span class='text-danger'>*</span></label>
                                <div class='col-sm-12'>
                                    <input class='form-control' type=text name='name' size='40' value='{!! q($group_name) !!}' required>
                                </div>
                            </div>

                            <div class='form-group mt-4'>
                                <label class='col-sm-6 control-label-notes'>{{ trans('langDescription') }}&nbsp{{ trans('langOptional') }}</label>
                                <div class='col-sm-12'>
                                    <textarea class='form-control' name='description' rows='2' cols='60'>
                                        {!! q($group_description) !!}
                                    </textarea>
                                </div>
                            </div>

                            
                            @php
                                $is_common_group = Database::get()->querySingle("SELECT common FROM mentoring_group WHERE id = ?d AND mentoring_program_id = ?d",$group_id,$mentoring_program_id)->common;
                            @endphp

                            <div class="form-group mt-4">
                                <label class='col-sm-12 control-label-notes'>{{ trans('langMax') }}&nbsp{{ trans('langGroupPlacesThis') }}</label>
                                <div class='col-sm-12'>
                                    <input class='form-control @if($is_common_group) pe-none opacity-help @endif' type=text name='maxStudent' size=2 value='{!! $max_members !!}'>
                                </div>
                            </div>
                            
                            
                            @if(!$is_common_group)
                                <div class='form-group mt-4'>
                                    <label class='col-sm-12 control-label-notes'>{{ trans('langGroupTutor') }}<span class='text-danger'>*</span></label>
                                    <div class='col-sm-12'>
                                        <select name='tutor[]' multiple id='select-tutor' class='form-select' required>
                                            @php
                                                $q = Database::get()->queryArray("SELECT user.id AS user_id, surname, givenname,
                                                        user.id IN (SELECT user_id FROM mentoring_group_members
                                                                                    WHERE group_id = ?d AND
                                                                                            is_tutor = 1) AS is_tutor
                                                                    FROM mentoring_programs_user, user
                                                                    WHERE mentoring_programs_user.user_id = user.id AND
                                                                            mentoring_programs_user.mentor = 1 AND
                                                                            mentoring_programs_user.mentoring_program_id = ?d
                                                                    ORDER BY surname, givenname, user_id", $group_id, $mentoring_program_id);

                                                                
                                            @endphp

                                            @foreach ($q as $row)
                                                @php $selected = $row->is_tutor ? ' selected="selected"' : ''; @endphp
                                                <option value='{{ $row->user_id }}' {!! $selected !!}>{!! q($row->surname) !!}&nbsp{!! q($row->givenname) !!}
                                                </option>
                                            @endforeach
                                        </select>
                                    </div>
                                </div>
                            @endif
                               
                           
                            


                            <div class='form-group mt-4'>
                                <label class='col-sm-12 control-label-notes'>{{ trans('langGroupMembers') }}</label>
                                <div class='col-sm-12'>
                                    <div class='table-responsive mt-0'>
                                        <table class='table-default rounded-2'>
                                            <thead>
                                                <tr class='list-header'>
                                                    @if(!$is_common_group)
                                                        <th>{{ trans('langNoGroupRegMentees') }}</th>
                                                    @else
                                                        <th>{{ trans('langNoGroupRegUsers') }}</th>
                                                    @endif
                                                    <th width='100' class='text-center'>{{ trans('langMove') }}</th>
                                                    <th class='right'>{{ trans('langGroupMembers') }}</th>
                                                </tr>
                                            </thead>
                                            <tbody>
                                                <tr>
                                                    <td>
                                                        <select class='form-select h-100 rounded-2' id='users_box' name='nogroup[]' size='15' multiple>
                                                        
                                                            @php

                                                                $reg_many_or_one_group = Database::get()->querySingle("SELECT other_groups_reg FROM mentoring_programs WHERE id = ?d",$mentoring_program_id)->other_groups_reg;

                                                                if($is_common_group == 0)
                                                                    if($reg_many_or_one_group == 1){
                                                                        $resultNotMember = Database::get()->queryArray("SELECT u.id, u.surname, u.givenname, u.am
                                                                                                                        FROM (user u, mentoring_programs_user mpu)
                                                                                                                        WHERE mpu.mentoring_program_id = ?d AND
                                                                                                                            mpu.user_id = u.id AND
                                                                                                                            mpu.is_guided = 1 AND
                                                                                                                            mpu.status = " . USER_STUDENT . " 
                                                                                                                            AND u.id NOT IN (SELECT user_id FROM mentoring_group_members 
                                                                                                                                            WHERE status_request = 1 
                                                                                                                                            AND group_id IN (SELECT id FROM mentoring_group WHERE mentoring_program_id = ?d AND common = 0)) 
                                                                                                                        GROUP BY u.id, u.surname, u.givenname, u.am
                                                                                                                        ORDER BY u.surname, u.givenname
                                                                                                                        ",$mentoring_program_id, $mentoring_program_id);
                                                                    }else{
                                                                        $resultNotMember = Database::get()->queryArray("SELECT u.id, u.surname, u.givenname, u.am
                                                                                                                        FROM user u, mentoring_programs_user mpu
                                                                                                                        WHERE mpu.mentoring_program_id = ?d AND
                                                                                                                            mpu.user_id = u.id AND
                                                                                                                            u.id NOT IN (SELECT user_id FROM mentoring_group_members WHERE group_id = ?d AND status_request = ?d AND is_tutor = ?d) AND
                                                                                                                            mpu.is_guided = 1 AND
                                                                                                                            mpu.status = " . USER_STUDENT . "
                                                                                                                        GROUP BY u.id, u.surname, u.givenname, u.am
                                                                                                                        ORDER BY u.surname, u.givenname", $mentoring_program_id, $group_id, 1, 0);
                                                                    }
                                                                else{
                                                                    $resultNotMember = Database::get()->queryArray("SELECT u.id, u.surname, u.givenname, u.am
                                                                                                                        FROM user u, mentoring_programs_user mpu
                                                                                                                        WHERE mpu.mentoring_program_id = ?d AND
                                                                                                                            mpu.user_id = u.id AND
                                                                                                                            u.id NOT IN (SELECT user_id FROM mentoring_group_members WHERE status_request = 1 AND group_id IN (SELECT id FROM mentoring_group WHERE common = 1 AND mentoring_program_id = ?d)) AND
                                                                                                                            u.id IN (SELECT user_id FROM mentoring_programs_user WHERE mentoring_program_id = ?d) AND
                                                                                                                            mpu.is_guided = 1 AND
                                                                                                                            mpu.status = " . USER_STUDENT . "
                                                                                                                        GROUP BY u.id, u.surname, u.givenname, u.am
                                                                                                                        ORDER BY u.surname, u.givenname", $mentoring_program_id, $mentoring_program_id, $mentoring_program_id);
                                                                }

         
                                                            @endphp

                                                            @foreach ($resultNotMember as $myNotMember)
                                                                <option value='{{ $myNotMember->id }}'>{!! q("$myNotMember->surname $myNotMember->givenname") !!} {!! (!empty($myNotMember->am) ? q(" ($myNotMember->am)") : "") !!}</option>
                                                            @endforeach

                                                        </select>
                                                    </td>
                                                    <td class='text-center'>
                                                        <div class='form-group'>
                                                            <input class='btn btn-outline-primary small-text rounded-2' type='button' onClick="move('users_box','members_box')" value='   &gt;&gt;   ' />
                                                        </div>
                                                        <div class='form-group mt-2'>
                                                            <input class='btn btn-outline-primary small-text rounded-2' type='button' onClick="move('members_box','users_box')" value='   &lt;&lt;   ' />
                                                        </div>
                                                    </td>
                                                    <td class='text-end'>
                                                        <select class='form-select h-100 rounded-2' id='members_box' name='ingroup[]' size='15' multiple>
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
                                                    </td>
                                                </tr>
                                            </tbody>
                                        </table>
                                    </div>
                                </div>
                            </div>


                            <div class='form-group mt-4'>
                                <label class='col-sm-12 control-label-notes mb-2'>{{ trans('langGroupStudentRegistrationType') }}</label>
                                <div class='col-sm-12'>
                                    <div class='checkbox'>
                                        <label>
                                            <input id='id_self_reg' type='checkbox' name='self_reg' {!! $self_reg !!}>
                                            {{ trans('langGroupAllowMenteeRegistration') }}
                                        </label>
                                    </div>
                                </div>
                            </div>


                            <div class='form-group mt-4'>
                                <label class='col-sm-12 control-label-notes mb-2'>{{ trans('langGroupAllowUnregister') }}</label>
                                <div class='col-sm-12'>
                                    <div class='checkbox'>
                                        <label>
                                            <input id='id_allow_unreg' type='checkbox' name='allow_unreg' {!! $allow_unreg !!}>
                                                {{ trans('langGroupAllowMenteeUnregister') }}
                                        </label>
                                    </div>
                                </div>
                            </div>

                            <div class='form-group mt-4'>
                                <label class='col-sm-6 control-label-notes mb-2'>{{ trans('langOpRegReq')}}</label>
                                <div class='col-sm-12'>
                                    <div class='checkbox'>
                                        <label>
                                            <input id='id_self_request' type='checkbox' name='self_request' {!! $self_req !!}>
                                            
                                            {{ trans('langMenteeCanSendGroupRequest')}}
                                        </label>
                                    </div>
                                </div>
                            </div>

                            <div class='form-group mt-4'>
                                <label class='col-sm-12 control-label-notes mb-2'>{{ trans('langPrivate_1') }}</label>
                                <div class='col-sm-12'>
                                    <div class='radio mb-2'>
                                        <label>
                                            <input type='radio' name='private_forum' value='1' {!! $private_forum_yes !!}>
                                             {{ trans('langPrivate_2') }}
                                        </label>
                                    </div>
                                    <div class='radio'>
                                        <label>
                                            <input type='radio' name='private_forum' value='0' {!! $private_forum_no !!} disabled>
                                             {{ trans('langPrivate_3') }}
                                        </label>
                                    </div>
                                </div>
                            </div>

                            
                            <div class='form-group mt-4 d-inline-flex'>
                                <label class='col-auto control-label-notes'>{{ trans('langGroupForum') }}:</label>
                                <div class='col-auto ms-2'>
                                    <div class='checkbox'>
                                        <label>
                                            <input type='checkbox' name='forum' {!! $has_forum !!}>
                                        </label>
                                    </div>
                                </div>
                            </div>


                            <div class='form-group mt-4 d-inline-flex'>
                                <label class='col-auto control-label-notes'>{{ trans('langDoc') }}:</label>
                                <div class='col-auto ms-2'>
                                    <div class='checkbox'>
                                    <label>
                                        <input type='checkbox' name='documents' {!! $documents !!}>
                                    </label>
                                    </div>
                                </div>
                            </div>

                            <div class='form-group mt-4 d-inline-flex'>
                                <label class='col-auto control-label-notes'>{{ trans('langAnnouncements') }}:</label>
                                <div class='col-auto ms-2'>
                                    <div class='checkbox'>
                                    <label>
                                        <input type='checkbox' name='announcements' {!! $announcements !!}>
                                    </label>
                                    </div>
                                </div>
                            </div>

                            <div class='form-group mt-4 d-inline-flex'>
                                <label class='col-auto control-label-notes'>{{ trans('langWall') }}:</label>
                                <div class='col-auto ms-2'>
                                    <div class='checkbox'>
                                    <label>
                                        <input type='checkbox' name='wall' {!! $wall !!}>
                                    </label>
                                    </div>
                                </div>
                            </div>


                            <input type='hidden' name='group_id' value='{{ $group_id }}'></input>

                            <div class='form-group mt-5'>
                                <div class='col-12 d-flex justify-content-center align-items-center'>
                                    {!!
                                        form_buttons(array(
                                            array(
                                                'class' => 'submitAdminBtn',
                                                'text'  =>  trans('langSave'),
                                                'name'  =>  'modify',
                                                'value' =>  trans('langModify'),
                                                'javascript' => "selectAll('members_box',true)"
                                            )
                                        ))
                                    !!}
                                </div>
                            </div>



                          </form>
                        </div>
                    </div>
                  
                

        </div>
      
    </div>
</div>


<script type='text/javascript'>
    $(document).ready(function(){

        if($('#id_self_reg').is(":checked")){
           $('#id_self_request').attr('disabled',true);
        }
        $('#id_self_reg').on('click',function(){
            if($('#id_self_reg').is(":checked")){
                $('#id_self_request').attr('disabled',true);
            }else{
                $('#id_self_request').prop("disabled", false);
            }
            
        });


        if($('#id_self_request').is(":checked")){
           $('#id_self_reg').attr('disabled',true);
        }
        $('#id_self_request').on('click',function(){
            if($('#id_self_request').is(":checked")){
                $('#id_self_reg').attr('disabled',true);
            }else{
                $('#id_self_reg').prop("disabled", false);
            }
            
        });

        $('.showProgramsBtn').on('click',function(){
            localStorage.setItem("MenuMentoring","program");
        });

    })
   
</script>

@endsection