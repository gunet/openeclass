@extends('layouts.default')

@push('head_scripts')
    <script type='text/javascript'>
            $(function() {
                $('#startdate, #enddate').datetimepicker({
                    format: 'yyyy-mm-dd hh:ii:ss',
                    pickerPosition: 'bottom-right',
                    language: '{{ $language }}',
                    autoclose: true
                });            
            });
    </script>            
@endpush

@section('content')


<div class="col-12 main-section">
    <div class='{{ $container }}'>
        <div class="row rowMargin">

                    <nav class='breadcrumb_mentoring' style="--bs-breadcrumb-divider: url(&#34;data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' width='8' height='8'%3E%3Cpath d='M2.5 0L1 1.5 3.5 4 1 6.5 2.5 8l4-4-4-4z' fill='currentColor'/%3E%3C/svg%3E&#34;);" aria-label="breadcrumb">
                        <ol class="breadcrumb">
                            <li class="breadcrumb-item"><a class='TextSemiBold' href="{{ $urlAppend }}modules/mentoring/mentoring_platform_home.php"><span class='fa fa-home'></span>&nbsp{{ trans('langHomeMentoringPlatform') }}</a></li>
                            <li class="breadcrumb-item"><a class='TextSemiBold showProgramsBtn' href="{{ $urlAppend }}modules/mentoring/programs/show_programs.php">{{ trans('langOurMentoringPrograms') }}</a></li>
                            <li class="breadcrumb-item active TextMedium" aria-current="page">{{ $toolName }}</li>
                        </ol>
                    </nav>

                    @include('modules.mentoring.common.common_current_title')

                    <div class='col-12 mb-4'>
                        <div class='col-lg-7 col-md-9 col-12 ms-auto me-auto ps-3 pe-3'>
                            <p class='TextMedium text-center text-justify'>{!! trans('langInfoCreateProgramText')!!}</p>
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

                    

                    <div class='col-lg-6 col-12'>

                        <div class='form-wrapper form-edit rounded-2 p-md-0 p-3 solidPanel'>
                          <form id='myFormCreate' class='form-horizontal' role='form' method='post' name='createform' action="{{ $_SERVER['SCRIPT_NAME'] }}" enctype="multipart/form-data">

                            <div id="carouselProgramIndicators" class="carousel slide p-md-3 " data-bs-ride="carousel" data-bs-interval="false">
                               
                                <div class="carousel-inner">
                                    <div class="carousel-item active">
                                              
                                        <p class='badge bgNormalBlueText fs-6 text-uppercase TextBold mb-3'>{{ trans('langStepMentoring') }}&nbsp1&nbsp<span class='small-text'>->&nbsp{{trans('langUserFillData')}}</span></p>

                                        <div class='d-none d-md-block mb-3'>
                                            <div class='col-12 d-flex justify-content-md-center align-items-md-center'>
                                                <div class='mentoring-step-1 badge bgNormalBlueText d-flex justify-content-center align-items-center fs-4 rounded-pill'>1</div>
                                                <div class='mentoring-line'></div>
                                                <div class='mentoring-step-2 badge bg-white d-flex justify-content-center align-items-center fs-4 normalBlueText rounded-pill'>2</div>
                                                <div class='mentoring-line'></div>
                                                <div class='mentoring-step-2 badge bg-white d-flex justify-content-center align-items-center fs-4 normalBlueText rounded-pill'>3</div>
                                            </div>
                                        </div>

                                        <div class='col-12'><p class='TextBold text-end'>(<span class='text-danger'>*</span>) {{trans('langCPFFieldRequired')}}</p></div>
                                
                                       
                                       
                                        <div class='form-group mt-4'>
                                            <div for='title' class='col-12 control-label-notes'>{{ trans('langTitle') }}<span class='text-danger'>*</span></div>
                                            <div class='col-12'>
                                                <input name='title' id='title' type='text' class='form-control' value="{{ $title }}" placeholder="{{ trans('langTitle') }}" required />
                                            </div>
                                        </div>
                                    
                                        <div class='form-group mt-4'>
                                            <div for='code' class='col-12 control-label-notes'>{{ trans('langCode') }}<span class='text-danger'>*</span></div>
                                            <div class='col-12'>
                                                <input name='code' id='code' type='text' class='form-control' value="{{ $code }}" placeholder="{{ trans('langCode') }}" required />
                                            </div>
                                        </div>
                                     
                                       
        
                                        
                                            
                                        <div class='form-group mt-4'>
                                            <div for='select-tutors' class='col-12 control-label-notes'>{{ trans('langMentoringTutor') }}<span class='text-danger'>*</span></div>
                                            <div class='col-12'>
                                            
                                            
                                                    @php 
                                                        $UsersTutors = Database::get()->queryArray("SELECT id,givenname,surname FROM user 
                                                                                                    WHERE status = ?d AND is_mentor = ?d",USER_TEACHER,0);
                                                        $tmp_user_tutors = array();
                                                        foreach($UsersTutors as $t){
                                                            $tmp_user_tutors[] = $t->id;
                                                        }
                                                    @endphp
                                                    @if(count($UsersTutors) > 0)
                                                        <select id='select-tutors' name='mentoring_tutor[]' multiple required>
                                                            @foreach($UsersTutors as $user)
                                                                <option value='{{ $user->id }}' {{ ($uid == $user->id) ? 'selected' : '' }}>{{ $user->givenname }}&nbsp{{ $user->surname }}</option>
                                                                
                                                            @endforeach
                                                            @if(!in_array($uid,$tmp_user_tutors))
                                                                @php 
                                                                    $uid_name = Database::get()->querySingle("SELECT givenname FROM user WHERE id = ?d",$uid)->givenname;
                                                                    $uid_surname = Database::get()->querySingle("SELECT surname FROM user WHERE id = ?d",$uid)->surname;
                                                                @endphp
                                                                <option value='{{ $uid }}' selected>{{ $uid_name }}&nbsp{{ $uid_surname }}</option>
                                                            @endif
                                                        </select>
                                                    @endif
                                            
                                            </div>
                                        </div>
                                    
                                                
                                    
                                        <div class='form-group mt-4'>
                                            <div for='localize' class='col-12 control-label-notes'>{{ trans('langLanguage') }}</div>
                                            <div class='col-sm-12'>
                                                    {!! $lang_select_options !!}
                                            </div>
                                        </div>
                                       
                                       
                                         
                                        <div class='form-group mt-4'>
                                            <label for='description' class='col-12 control-label-notes'>{{trans('langDescrMentoringProgram')}} <small>{{trans('langOptional')}}</small></label>
                                            <div class='col-12'>
                                                {!! $rich_text_editor !!}
                                            </div>
                                        </div>

                                        <div class='mt-4 form-group'>
                                            <label for='startdate' class='col-sm-12 control-label-notes'>{{ trans('langStartDate') }}</label>
                                            <div class='col-sm-12'>
                                                <div class='input-group'>
                                                    <input class='form-control mt-0' name='startdate' id='startdate' type='text' value='{{ $startdate }}'>
                                                    <div class='input-group-addon input-group-text h-30px border-0 BordersRightInput bgEclass'><span class='fa fa-calendar'></span></div>
                                                </div>
                                            </div>
                                        </div>

                                        <div class='mt-4 form-group'>
                                            <label for='enddate' class='col-sm-12 control-label-notes'>{{ trans('langEndDate') }}</label>
                                            <div class='col-sm-12'>
                                                <div class='input-group'>
                                                    <input class='form-control mt-0' name='enddate' id='enddate' type='text' value='{{ $enddate }}'>
                                                    <div class='input-group-addon input-group-text h-30px border-0 BordersRightInput bgEclass'><span class='fa fa-calendar'></span></div>
                                                </div>
                                            </div>
                                        </div>

                                        <div class='mt-4 form-group'>
                                            <label for='image_mentoring_program' class='col-sm-12 control-label-notes'>{{ trans('langAddPicture') }}</label>
                                            <div class='col-sm-12'>
                                            <input class='form-control' name='image_mentoring_program' id='image_mentoring_program' type='file'>
                                            </div>
                                        </div>

                                        <div class='form-group mt-4'>
                                            <label for='allow_unreg_mentee_yes' class='col-sm-12 control-label-notes mb-2'>{{ trans('langAllowUnregMenteeFromProgram') }}</label>
                                            <div class='col-sm-12 d-flex justify-content-start align-items-center'>
                                                {{ trans('langYes') }}&nbsp<input id='allow_unreg_mentee_yes' type='checkbox' name='yes_allow_unreg' value='1'>
                                                &nbsp&nbsp&nbsp{{ trans('langNo') }}&nbsp<input id='allow_unreg_mentee_no' type='checkbox' name='yes_allow_unreg' value='0' checked>
                                            </div>
                                        </div>

                                    </div>
                                    
                                    <div class="carousel-item">
                                        <p class='badge bgNormalBlueText fs-6 text-uppercase TextBold mb-3'>{{ trans('langStepMentoring') }}&nbsp2&nbsp<span class='small-text'>->&nbsp{{trans('langMentoringMentor')}}</span></p>

                                        <div class='d-none d-md-block mb-5'>
                                            <div class='col-12 d-flex justify-content-md-center align-items-md-center'>
                                                <div class='mentoring-step-2 badge bg-white normalBlueText d-flex justify-content-center align-items-center fs-4 rounded-pill'>1</div>
                                                <div class='mentoring-line'></div>
                                                <div class='mentoring-step-1 badge bgNormalBlueText d-flex justify-content-center align-items-center fs-4 text-white rounded-pill'>2</div>
                                                <div class='mentoring-line'></div>
                                                <div class='mentoring-step-2 badge bg-white normalBlueText d-flex justify-content-center align-items-center fs-4 rounded-pill'>3</div>
                                                
                                            </div>
                                        </div>

                                        @if(count($all_specializations))
                                            <p class='TextBold small-text blackBlueText mb-3 d-flex justify-content-start align-items-start'>
                                                <span class='badge bg-info me-2'><span class='fa fa-info'></span></span>
                                                {{ trans('langSearchMentorsForCreateProgram') }}
                                            </p>
                                            <div class='panel panel-admin border-top-1 border-start-1 border-end-1 border-bottom-1 bg-white py-md-3 px-md-3 py-3 px-3 rounded-0'>
                                                <div class='panel-heading bg-body p-0'>
                                                    <div class='col-12 Help-panel-heading'>
                                                        <span class='panel-title text-uppercase Help-text-panel-heading'>{{ trans('langSpecializations') }}</span>
                                                    </div>
                                                </div>
                                                <div class='panel-body p-0 rounded-0'>
                                                    <div class="accordion specializationsAccordion" id="accordionSpeciliazations">
                                                        @foreach($all_specializations as $tag)
                                                            <div class="accordion-item bg-transparent border-0">
                                                                <h2 class="accordion-header" id="heading{{ $tag->id }}">
                                                                    <button class="accordion-button ps-2 pe-2" type="button" data-bs-toggle="collapse" data-bs-target="#collapse{{ $tag->id }}" aria-expanded="true" aria-controls="collapse{{ $tag->id }}">
                                                                    
                                                                        @php 
                                                                            $checkTranslationSpecialization = Database::get()->querySingle("SELECT *FROM mentoring_specializations_translations
                                                                                                                                            WHERE specialization_id = ?d AND lang = ?s",$tag->id, $language);
                                                                        @endphp

                                                                        @if($checkTranslationSpecialization)
                                                                            {{ $checkTranslationSpecialization->name }}
                                                                        @else
                                                                            {{ $tag->name }}
                                                                        @endif
                                                                    </button>
                                                                </h2>
                                                                <div id="collapse{{ $tag->id }}" class="accordion-collapse collapse" aria-labelledby="heading{{ $tag->id }}" data-bs-parent="#accordionSpeciliazations">
                                                                    <div class="accordion-body">
                                                                        @php 
                                                                            

                                                                            $skills = Database::get()->queryArray("SELECT *FROM mentoring_skills 
                                                                                            WHERE id IN (SELECT skill_id FROM mentoring_specializations_skills 
                                                                                                        WHERE specialization_id = ?d)",$tag->id);
                                                                        @endphp
                                                                        @if(count($skills) > 0)
                                                                            <ul class='p-0' style='list-style-type: none;'>
                                                                                @foreach($skills as $sk)
                                                                                    <li class='d-flex justify-content-start align-items-start mb-3'>
                                                                                        <input id='TheSkillIds{{ $sk->id }}{{ $tag->id }}' class='tagClick' type='checkbox' value='{{ $sk->id }},{{ $tag->id }}'>
                                                                                        <span class='TextSemiBold small-text'>
                                                                                            
                                                                                            @php 
                                                                                                $checkTranslationSkill = Database::get()->querySingle("SELECT *FROM mentoring_skills_translations
                                                                                                                                                                WHERE skill_id = ?d AND lang = ?s",$sk->id, $language);
                                                                                            @endphp

                                                                                            @if($checkTranslationSkill)
                                                                                                {{ $checkTranslationSkill->name }}
                                                                                            @else
                                                                                                {{ $sk->name }}
                                                                                            @endif
                                                                                        
                                                                                        </span>
                                                                                    </li>
                                                                                @endforeach
                                                                            </ul>
                                                                        @endif
                                                                    </div>
                                                                </div>
                                                            </div>
                                                        @endforeach
                                                    </div>

                                                </div>
                                                <div class='panel-footer rounded-0 d-flex justify-content-center align-items-center mt-3'>
                                                    <button id='SearchMentors' type='button' class='btn btn-outline-primary small-text mt-3 mb-3'>
                                                        <span class='fa fa-search'></span>&nbsp{{ trans('langSearch')}}
                                                    </button>
                                                </div>
                                            </div>

                                            <div class='col-12 colMentorsChoosing mt-3'>
                                                <table id='MentorsTable' class='table-default rounded-2 w-100 mt-3'>
                                                    <thead class='list-header'>
                                                        <th>{{ trans('langName') }}</th>
                                                        <th>Email</th>
                                                        <th class='text-center'>{{ trans('langSelect') }}</th>
                                                    </thead>
                                                    <tbody></tbody>
                                                </table>
                                            </div>

                                            <input id='selectedMentors' type='hidden' name='check_mentor[]'>

                                        @endif

                                       

                                    </div>

                                    <div class='carousel-item'>
                                            <p class='badge bgNormalBlueText fs-6 text-uppercase TextBold mb-3'>{{ trans('langStepMentoring') }}&nbsp3&nbsp<span class='small-text'>->&nbsp{{trans('langCommonGroup')}}</span></p>

                                            <div class='d-none d-md-block mb-3'>
                                                <div class='col-12 d-flex justify-content-md-center align-items-md-center'>
                                                    <div class='mentoring-step-2 badge bg-white d-flex justify-content-center align-items-center fs-4 normalBlueText rounded-pill'>1</div>
                                                    <div class='mentoring-line'></div>
                                                    <div class='mentoring-step-2 badge bg-white d-flex justify-content-center align-items-center fs-4 normalBlueText rounded-pill'>2</div>
                                                    <div class='mentoring-line'></div>
                                                    <div class='mentoring-step-2 badge bgNormalBlueText d-flex justify-content-center align-items-center fs-4 text-white rounded-pill'>3</div>
                                                </div>
                                            </div>


                                            

                                            <div class='col-12'><p class='TextBold text-end'>(<span class='text-danger'>*</span>) {{trans('langCPFFieldRequired')}}</p></div>
                        

                                            <div class='form-group'>
                                                <p class='col-sm-6 control-label-notes mb-1'>{{ trans('langGroupName') }}<span class='text-danger'>*</span></p>
                                                <div class='col-sm-12'>
                                                    <input class='form-control' type='text' name='group_name' size='40' required />
                                                    <span class='help-block'>{!! Session::getError('group_name') !!}</span>
                                                </div>
                                            </div>

                                            <div class='form-group mt-4'>
                                                <p class='col-sm-6 control-label-notes mb-1'>{{ trans('langDescription') }} {{ trans('langOptional') }}</p>
                                                {!! $rich_text_editor2 !!}
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
                                                    <input name='group_max' type='text' class='form-control pe-none opacity-help' id='group_max' value='{!! $group_max_value !!}' placeholder="{{ trans('langNewGroupMembers') }}">
                                                    <span class='help-block'>{{ trans('langGroupInfiniteUsers') }}</span>
                                                </div>
                                            </div>

                                            <!-- upeuthinos omadas o mentor -->
                                            <div class='form-group mt-4'>
                                                <p class='col-sm-12 control-label-notes mb-1'>{{ trans('langGroupTutor') }}</p>
                                                <div class='col-sm-12'>
                                                    {{-- @php 
                                                       $nameTutor = Database::get()->querySingle("SELECT givenname FROM user WHERE id = ?d",$uid)->givenname;
                                                       $surnameTutor = Database::get()->querySingle("SELECT surname FROM user WHERE id = ?d",$uid)->surname;
                                                    @endphp
                                                    <input type='text' class='pe-none opacity-help w-100' name='common-tutor' value='{{ $nameTutor }} {{ $surnameTutor }}' required>--}}
                                                    <p class='help-block'>{{ trans('langTheTutorsOfProgram') }}</p>
                                                    <input type='hidden' id='select-common-tutor' name='common_tutor[]' multiple>
                                                </div>
                                            </div>


                                            

                                            <div class='form-group mt-4'>
                                                <label for='id_self_reg' class='col-sm-6 control-label-notes mb-2'>{{ trans('langGroupStudentRegistrationType') }}</label>
                                                <div class='col-sm-12'>
                                                    <div class='checkbox'>
                                                        <label>
                                                            <input id='id_self_reg' type='checkbox' name='self_reg' checked>{{ trans('langMenteeAllowRegister') }}
                                                        </label>
                                                    </div>
                                                </div>
                                            </div>

                                            <div class='form-group mt-4'>
                                                <label for='id_self_request' class='col-sm-6 control-label-notes mb-2'>{{ trans('langCanRegisterToGroup') }}</label>
                                                <div class='col-sm-12'>
                                                    <div class='checkbox'>
                                                        <label>
                                                            <input id='id_self_request' type='checkbox' name='self_request'>{{ trans('langMenteeSendRequest') }}
                                                        </label>
                                                    </div>
                                                </div>
                                            </div>

                                            <div class='form-group mt-4'>
                                                <p class='col-sm-6 control-label-notes mb-1'>{{ trans('langGroupAllowUnregister') }}</p>
                                                <div class='col-sm-12'>
                                                    <div class='checkbox'>
                                                        <label>
                                                            <input type='checkbox' name='allow_unreg'>{{ trans('langMenteeAllowUnregister') }}
                                                        </label>
                                                    </div>
                                                </div>
                                            </div>

                                            <div class='form-group mt-4'>
                                                <p class='col-sm-12 control-label-notes mb-1'>{{ trans('langPrivate_1') }}</p>
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
                                                <div class='row'>
                                                    <p class='col-auto control-label-notes mb-1'>{{ trans('langGroupForum') }}:</p>
                                                    <div class='col-auto pt-1'>
                                                        <div class='checkbox'>
                                                        <label>
                                                            <input type='checkbox' name='forum'>
                                                        </label>
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>

                                            <div class='form-group mt-4'>
                                                <div class='row'>
                                                    <p class='col-auto control-label-notes mb-1'>{{ trans('langDoc') }}:</p>
                                                    <div class='col-auto pt-1'>
                                                        <div class='checkbox'>
                                                        <label>
                                                            <input type='checkbox' name='documents'>
                                                        </label>
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>

                                            <div class='form-group mt-4'>
                                                <div class='row'>
                                                    <p class='col-auto control-label-notes mb-1'>{{ trans('langAnnouncements') }}:</p>
                                                    <div class='col-auto pt-1'>
                                                        <div class='checkbox'>
                                                        <label>
                                                            <input type='checkbox' name='announcements'>
                                                        </label>
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>

                                            <div class='form-group mt-4'>
                                                <div class='row'>
                                                    <p class='col-auto control-label-notes mb-1'>{{ trans('langWall') }}:</p>
                                                    <div class='col-auto pt-1'>
                                                        <div class='checkbox'>
                                                        <label>
                                                            <input type='checkbox' name='wall'>
                                                        </label>
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>

                                            <input type='hidden' name='group_quantity' value='1'>

                                            








                                        <div class='form-group mt-5 d-flex justify-content-center align-items-center'>
                                            @if($check_mentor_edit) 
                                                <input type="hidden" name="program_id" value="{{ $mentoring_program_id }}">
                                                <input type="hidden" name="do_edit_mentoring_program" value="{{ $code }}"> 
                                            @endif
                                            <input class='btn submitAdminBtn submitCreate' name='create_mentoring_program' value="{{ trans('langCreate') }}">                          
                                            <a href='{{ $urlAppend }}modules/mentoring/mentoring_platform_home.php' class='btn cancelAdminBtn ms-1'>{{ trans('langCancel') }}</a> 
                                        </div>   

                                    </div>
                                </div>
                                
                                
                                <div class='col-12 bg-transparent mt-5'>
                                    <button class="carousel-prev-btn" type="button" data-bs-target="#carouselProgramIndicators" data-bs-slide="prev">
                                        <span class='d-flex justify-content-center align-items-center fa fa-arrow-left lightBlueText fs-5'></span>
                                    </button>
                                    <button class="carousel-next-btn float-end" type="button" data-bs-target="#carouselProgramIndicators" data-bs-slide="next">
                                        <span class='d-flex justify-content-center align-items-center fa fa-arrow-right lightBlueText fs-5'></span>
                                    </button>
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

<script type="text/javascript">

    $(document).ready(function(){

        if($('#allow_unreg_mentee_yes').is(":checked")){
           $('#allow_unreg_mentee_no').attr('disabled',true);
        }
        $('#allow_unreg_mentee_yes').on('click',function(){
            if($('#allow_unreg_mentee_yes').is(":checked")){
                $('#allow_unreg_mentee_no').attr('disabled',true);
            }else{
                $('#allow_unreg_mentee_no').prop("disabled", false);
            }
            
        });


        if($('#allow_unreg_mentee_no').is(":checked")){
           $('#allow_unreg_mentee_yes').attr('disabled',true);
        }
        $('#allow_unreg_mentee_no').on('click',function(){
            if($('#allow_unreg_mentee_no').is(":checked")){
                $('#allow_unreg_mentee_yes').attr('disabled',true);
            }else{
                $('#allow_unreg_mentee_yes').prop("disabled", false);
            }
            
        });

        $('.showProgramsBtn').on('click',function(){
            localStorage.setItem("MenuMentoring","program");
        });

        ///////////////////////////////////about common-group////////////////////////////////////
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
        ////////////////////////////////////////////////////////////////////////////////////////////

        const array_tags = [];
        const array_specializations = [];
        sendTags(array_tags,array_specializations);

        $('#SearchMentors').click(function(){

            $('#MentorsTable').DataTable().clear().destroy();
            var jsonString = JSON.stringify(array_tags);
            var mentorString = JSON.stringify(array_mentor_ids);
            var specializations = JSON.stringify(array_specializations);
            var table = $('#MentorsTable').dataTable({
                ajax:{
                    type: "POST",
                    url: "{{ $urlAppend }}modules/mentoring/programs/getMentorsByTags.php",
                    data: {dataa : jsonString, program : 'create', Mentors : mentorString, Specialization : specializations, FirstLoop : 'false'}, 
                    cache: true,
                    dataSrc:""
                },
                columns:[
                    {data:"name"},
                    {data:"email"},
                    {data:"choose"}
                ]
            });
            
           
        })
        ///////////////////////////////////////////////////////////////////////////////////////////////

        const array_mentor_ids = [];
        var initialIdsMentors = 0;

        $(document).on("click",'.clickerMentor',function(){
            if ($(this).is(':checked')) {
                var mentor_id = ($(this).val());
                array_mentor_ids.push(mentor_id);
            } else {
                var mentor_id = ($(this).val());
                array_mentor_ids.splice($.inArray(mentor_id, array_mentor_ids),1);
            }
        });
         

        $(document).on("click",'.submitCreate',function(){
            var selectedTutors = $('#select-tutors').val();
            let elem = document.getElementById('select-common-tutor').value = selectedTutors;
            elem.value = selectedTutors;

            document.getElementById("selectedMentors").value = array_mentor_ids;
            array_mentor_ids.splice(0,array_mentor_ids.length);
            document.getElementById("myFormCreate").submit();
        });
        
      
        
    })

    function sendTags(array_tags,array_specializations){
        $(".tagClick").change(function(){
            if ($(this).is(':checked')) {
                var tag_id = ($(this).val());

                var new_tag_id = tag_id.split(',');
                var new_tag_id2 = tag_id.split(',');

                tag_id = new_tag_id[0];
                var specialization_id = new_tag_id2[1];

                

                array_tags.push(tag_id);
                array_specializations.push(specialization_id);
            } else {

                var tag_id = ($(this).val());

                var new_tag_id = tag_id.split(',');
                var new_tag_id2 = tag_id.split(',');

                tag_id = new_tag_id[0];
                var specialization_id = new_tag_id2[1];

                array_tags.splice($.inArray(tag_id, array_tags),1);
                array_specializations.splice($.inArray(specialization_id, array_specializations),1);
            }
            
        });  
    }

   
    

</script>

@endsection