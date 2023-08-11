
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
                            <li class="breadcrumb-item"><a class='TextSemiBold' href="{{ $urlAppend }}modules/mentoring/programs/group/index.php">{{ trans('langGroupMentorsMentees') }}</a></li>
                           
                            <li class="breadcrumb-item active TextMedium" aria-current="page">{{ $toolName }}</li>
                        </ol>
                    </nav>

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
                   
                   
                    <div class='col-lg-6 col-12'>
                        <div class='form-wrapper form-edit rounded-2 p-3 solidPanel'>
                          <form class='form-horizontal' role='form' method='post' name='createform' action="{{ $_SERVER['SCRIPT_NAME'] }}" enctype="multipart/form-data">
                            <div class='col-12'><p class='TextBold text-end'>(<span class='text-danger'>*</span>) {{trans('langCPFFieldRequired')}}</p></div>
                        

                            <div class='form-group'>
                                <label class='col-sm-6 control-label-notes'>{{ trans('langGroupName') }}<span class='text-danger'>*</span></label>
                                <div class='col-sm-12'>
                                    <input class='form-control' type=text name='group_name' size='40' required>
                                    <span class='help-block'>{!! Session::getError('group_name') !!}</span>
                                </div>
                            </div>

                            <div class='form-group mt-4'>
                                <label class='col-sm-6 control-label-notes'>{{ trans('langDescription') }} {{ trans('langOptional') }}</label>
                                <div class='col-sm-12'><textarea class='form-control' name='description' rows='2' cols='60'></textarea></div>
                            </div>


                            <div class="form-group mt-4">
                                <label for='group_quantity' class='col-sm-6 control-label-notes'>{{ trans('langNewGroups') }}</label>
                                <div class='col-sm-12'>
                                    <input name='group_quantity' type='text' class='form-control pe-none opacity-help' id='group_quantity' value='{!! $group_quantity_value !!}' placeholder="{{ trans('langNewGroups') }}">
                                    <span class='help-block'>{!! Session::getError('group_quantity') !!}</span>
                                </div>
                            </div>

                            <div class="form-group mt-4">
                                <label for='group_max' class='col-sm-6 control-label-notes'>{{ trans('langNewGroupMembers') }}</label>
                                <div class='col-sm-12'>
                                    <input name='group_max' type='text' class='form-control' id='group_max' value='{!! $group_max_value !!}' placeholder="{{ trans('langNewGroupMembers') }}">
                                    <span class='help-block'>{{ trans('langGroupInfiniteUsers') }}</span>
                                </div>
                            </div>

                            <!-- upeuthinos omadas o mentor -->
                            <div class='form-group mt-4'>
                                <label class='col-sm-12 control-label-notes'>{{ trans('langGroupTutor') }}<span class='text-danger'>*</span></label>
                                <div class='col-sm-12'>
                                    <select name='tutor[]' multiple id='select-tutor' class='form-select h-100' required>
                                        @php 
                                            $q = Database::get()->queryArray("SELECT user.id AS user_id, surname, givenname
                                                            FROM mentoring_programs_user, user
                                                            WHERE mentoring_programs_user.user_id = user.id AND
                                                                mentoring_programs_user.mentor = 1 AND
                                                                mentoring_programs_user.mentoring_program_id = ?d
                                                            ORDER BY surname, givenname, user_id", $mentoring_program_id);
                                        @endphp
                                        @foreach($q as $row)
                                            <option value='{{ $row->user_id }}'>{{ $row->surname }}&nbsp{{ $row->givenname }}
                                        @endforeach
                                    </select>
                                </div>
                            </div>


                            <div class='form-group mt-4'>
                                <label class='col-sm-12 control-label-notes'>{{ trans('langGroupMembers') }}</label>
                                <div class='col-sm-12'>
                                    <div class='table-responsive mt-0'>
                                        <table class='table-default rounded-2'>
                                            <thead>
                                                <tr class='list-header'>
                                                    <th>{{ trans('langMentees') }}</th>
                                                    <th width='100' class='text-center'>{{ trans('langMove') }}</th>
                                                    <th class='right'>{{ trans('langGroupMembers') }}</th>
                                                </tr>
                                            </thead>
                                            <tbody>
                                                <tr>
                                                <td>
                                                    <select class='form-select h-100 rounded-0' id='users_box' name='nogroup[]' size='15' multiple>
                                                     @php
                                                        
                                                        $tool_content_not_Member = $tool_content_group_members = '';

                                                        $reg_many_or_one_group = Database::get()->querySingle("SELECT other_groups_reg FROM mentoring_programs WHERE id = ?d",$mentoring_program_id)->other_groups_reg;

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
                                                                                                            mpu.is_guided = 1 AND
                                                                                                            mpu.status = " . USER_STUDENT . "
                                                                                                            GROUP BY u.id, u.surname, u.givenname, u.am
                                                                                                            ORDER BY u.surname, u.givenname", $mentoring_program_id);
                                                        }
                                                       
                                                     @endphp
                                                     @foreach ($resultNotMember as $myNotMember) {
                                                             <option value='{{ $myNotMember->id }}'>{!! q("$myNotMember->surname $myNotMember->givenname") . (!empty($myNotMember->am) ? q(" ($myNotMember->am)") : "") !!}</option>
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
                                                    <select class='form-select h-100 rounded-0' id='members_box' name='ingroup[]' size='15' multiple>
                                                      {!! $tool_content_group_members !!}
                                                    </select>
                                                </td>
                                                </tr>
                                            </tbody>
                                        </table>
                                    </div>
                                </div>
                            </div>

                            <div class='form-group mt-4'>
                                <label class='col-sm-6 control-label-notes mb-2'>{{ trans('langGroupStudentRegistrationType') }}</label>
                                <div class='col-sm-12'>
                                    <div class='checkbox'>
                                        <label class='label-container'>
                                            <input id='id_self_reg' type='checkbox' name='self_reg' checked>
                                            <span class='checkmark'></span>{{ trans('langGroupAllowMenteeRegistration') }}
                                        </label>
                                    </div>
                                </div>
                            </div>

                            <div class='form-group mt-4'>
                                <label class='col-sm-6 control-label-notes mb-2'>{{ trans('langOpRegReq')}}</label>
                                <div class='col-sm-12'>
                                    <div class='checkbox'>
                                    <label class='label-container'>
                                            <input id='id_self_request' type='checkbox' name='self_request'>
                                            <span class='checkmark'></span>{{ trans('langMenteeCanSendGroupRequest')}}
                                        </label>
                                    </div>
                                </div>
                            </div>

                            <div class='form-group mt-4'>
                                <label class='col-sm-6 control-label-notes mb-2'>{{ trans('langGroupAllowUnregister') }}</label>
                                <div class='col-sm-12'>
                                    <div class='checkbox'>
                                    <label class='label-container'>
                                            <input type='checkbox' name='allow_unreg'> 
                                            <span class='checkmark'></span>{{ trans('langGroupAllowMenteeUnregister') }}
                                        </label>
                                    </div>
                                </div>
                            </div>

                            <div class='form-group mt-4'>
                                <label class='col-sm-12 control-label-notes mb-2'>{{ trans('langPrivate_1') }}</label>
                                <div class='col-sm-12'>
                                    <div class='radio'>
                                    <label>
                                        <input type='radio' name='private_forum' value='1' checked=''>
                                        {{ trans('langPrivate_2') }}
                                    </label>
                                    </div>
                                    <div class='radio mt-2'>
                                    <label>
                                        <input type='radio' name='private_forum' value='0' disabled>
                                        {{ trans('langPrivate_3') }}
                                    </label>
                                    </div>
                                </div>
                            </div>


                            <div class='form-group mt-4'>
                                    <div class='col-12'>
                                        <div class='checkbox'>
                                            <label class='label-container'>
                                                <input type='checkbox' name='forum'>
                                                <span class='checkmark'></span>
                                                {{ trans('langGroupForum') }}
                                            </label>
                                        </div>
                                    </div>
                            </div>

                            <div class='form-group mt-2'>
                            
                                    <div class='col-12'>
                                        <div class='checkbox'>
                                        <label class='label-container'>
                                            <input type='checkbox' name='documents'>
                                            <span class='checkmark'></span>
                                            {{ trans('langDoc') }}
                                        </label>
                                        </div>
                                    </div>
                               
                            </div>

                            <div class='form-group mt-2'>
                               
                                    <div class='col-12'>
                                        <div class='checkbox'>
                                        <label class='label-container'>
                                            <input type='checkbox' name='announcements'>
                                            <span class='checkmark'></span>
                                            {{ trans('langAnnouncements') }}
                                        </label>
                                        </div>
                                    </div>
                               
                            </div>

                            <div class='form-group mt-2'>
                               
                                    <div class='col-12'>
                                        <div class='checkbox'>
                                            <label class='label-container'>
                                                <input type='checkbox' name='wall'>
                                                <span class='checkmark'></span>
                                                {{ trans('langWall') }}
                                            </label>
                                        </div>
                                    </div>
                                
                            </div>

                            <input type='hidden' name='group_quantity' value='1'>

                            <div class='form-group mt-5'>
                                <div class='col-12 d-flex justify-content-center align-items-center'>
                                    <input class='btn submitAdminBtn' type='submit' value='{{ trans('langCreate') }}' name='creation' onClick=\"selectAll('members_box', true)\" >
                                    <a class='btn cancelAdminBtn ms-1' href=''>{{ trans('langCancel') }}</a>
                                </div>
                            </div>

                          </form>
                        </div>
                    </div>
                    <div class='col-lg-6 col-12 d-none d-md-none d-lg-block'>
                          <div class='col-12 h-100 left-form'></div>
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

    })
   
</script>

@endsection