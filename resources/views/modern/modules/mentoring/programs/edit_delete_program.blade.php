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
                            <li class="breadcrumb-item"><a class='TextSemiBold' href="{{ $urlAppend }}modules/mentoring/programs/myprograms.php">{{ trans('langMyPrograms') }}</a></li>
                            <li class="breadcrumb-item"><a class='TextSemiBold' href="{{ $urlAppend }}mentoring_programs/{{ $mentoring_program_code }}/index.php">{!! show_mentoring_program_title($mentoring_program_code) !!}</a></li>
                            <li class="breadcrumb-item active TextMedium" aria-current="page">{{ $toolName }}</li>
                        </ol>
                    </nav>

                    @include('modules.mentoring.common.common_current_title')

                    <div class='col-12 mb-4'>
                        <div class='col-lg-7 col-md-9 col-12 ms-auto me-auto ps-3 pe-3'>
                            <p class='TextMedium text-center text-justify'>{!! trans('langInfoEditProgramText')!!}</p>
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

                    

                    <div class='col-lg-6 col-12'>
                        <div class='form-wrapper form-edit rounded-2 p-md-0 p-3'>
                          <form id='myForm' class='form-horizontal' role='form' method='post' name='createform' action="{{ $_SERVER['SCRIPT_NAME'] }}" enctype="multipart/form-data">

                            <div id="carouselProgramIndicators" class="carousel slide p-md-3" data-bs-ride="carousel" data-bs-interval="false">
                                <div class="carousel-inner">
                                    <div class="carousel-item active">
                                        
                                        <p class='badge bgNormalBlueText fs-6 text-uppercase TextBold mb-3'>{{ trans('langStepMentoring') }}&nbsp1</p>

                                        <div class='d-none d-md-block mb-3'>
                                            <div class='col-12 d-flex justify-content-md-center align-items-md-center'>
                                                <div class='mentoring-step-1 badge bgNormalBlueText d-flex justify-content-center align-items-center fs-4 rounded-pill'>1</div>
                                                <div class='mentoring-line'></div>
                                                <div class='mentoring-step-2 badge bg-white d-flex justify-content-center align-items-center fs-4 normalBlueText rounded-pill'>2</div>
                                            </div>
                                        </div>

                                        <div class='col-12'><p class='TextBold text-end'>(<span class='text-danger'>*</span>) {{trans('langCPFFieldRequired')}}</p></div>
                                        
                                        <div class='form-group mt-4'>
                                            <div for='title' class='col-12 control-label-notes'>{{ trans('langTitle') }}<span class='text-danger'>*</span></div>
                                            <div class='col-12'>
                                                <input name='title' id='title' type='text' class='form-control' value="{{ $title }}" required />
                                            </div>
                                        </div>
                                    
                                        <div class='form-group mt-4'>
                                            <div for='code' class='col-12 control-label-notes'>{{ trans('langCode') }}<span class='text-danger'>*</span></div>
                                            <div class='col-12'>
                                                <input name='code' id='code' type='text' class='form-control' value="{{ $public_code }}" required />
                                            </div>
                                        </div>
                                        

                                        <div class='form-group mt-4'>
                                            <div for='edit-select-tutors' class='col-sm-12 control-label-notes'>{{ trans('langMentoringTutor') }}<span class='text-danger'>*</span></div>
                                            <div class='col-sm-12'>
                                            
                                                @php 
                                                    $old_tutors = Database::get()->queryArray("SELECT user_id FROM mentoring_programs_user
                                                                                        WHERE mentoring_program_id = ?d
                                                                                        AND status = ?d
                                                                                        AND tutor = ?d",$mentoring_program_id,USER_TEACHER,1);
                                                    $tmp_old_tutors = array();
                                                    foreach($old_tutors as $m){
                                                        $tmp_old_tutors[] = $m->user_id;
                                                    }
                                                    $new_tutors = Database::get()->queryArray("SELECT id,givenname,surname FROM user
                                                                                                WHERE status = ?d AND is_mentor = ?d",USER_TEACHER,0);
                                                @endphp
                                                <!-- Get two foreach because a tutor can be a mentor of program -->
                                                
                                                <select id='edit-select-tutors' name='mentoring_tutor[]' multiple required>
                                                    @foreach($old_tutors as $t)
                                                        @php 
                                                        $name_tutor = Database::get()->querySingle("SELECT givenname FROM user WHERE id = ?d",$t->user_id)->givenname;
                                                        $surname_tutor = Database::get()->querySingle("SELECT surname FROM user WHERE id = ?d",$t->user_id)->surname;
                                                        @endphp
                                                        <option value='{{ $t->user_id }}' selected>{{ $name_tutor }}&nbsp{{ $surname_tutor }}</option>
                                                    @endforeach
                                                    @foreach($new_tutors as $t)
                                                        @if(!in_array($t->id,$tmp_old_tutors))
                                                            <option value='{{ $t->id }}'>{{ $t->givenname }}&nbsp{{ $t->surname }}</option>
                                                        @endif
                                                    @endforeach
                                                </select>
                                            </div>
                                        </div>

                                                
                                        
                                        <div class='form-group mt-4'>
                                            <div class='col-sm-12 control-label-notes'>{{ trans('langLanguage') }}</div>
                                            <div class='col-sm-12'>
                                                    {!! $lang_select_options !!}
                                            </div>
                                        </div>
                                            
                                        
                                                    
                                        <div class='form-group mt-4'>
                                            <label for='description' class='col-sm-12 control-label-notes'>{{trans('langDescrMentoringProgram')}} <small>{{trans('langOptional')}}</small></label>
                                            <div class='col-sm-12'>
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
                                                <input type="hidden" name="old_program_image" value="{{ $program_image }}">
                                                <input class='form-control mb-2' name='image_mentoring_program' id='image_mentoring_program' type='file'>
                                                @if($program_image)
                                                <div class='d-flex justify-content-center align-items-center'>
                                                    <span>{{ trans('langPathUploadFile') }}:</span> 
                                                    <img width="100" height="100" src="{{ $urlAppend }}mentoring_programs/{{ $mentoring_program_code }}/image/{{ $program_image }}">
                                                    <a class='btn deleteAdminBtn ms-2' href="{{ $_SERVER['SCRIPT_NAME'] }}?del_img={{ $program_image }}">{{ trans('langDelete') }}</a>
                                                </div>
                                                @endif
                                            </div>
                                        </div>

                                        
                                        <div class='form-group mt-4'>
                                                <div class='col-sm-12 control-label-notes mb-2'>{{ trans('langAllowUnregMenteeFromProgram') }}</div>
                                                <label class='label-container'>
                                                    <input id='allow_unreg_mentee_yes' type='checkbox' name='yes_allow_unreg' value='1' {{ $allow_unreg_mentee_from_program == 1 ? 'checked' : '' }}>
                                                    <span class='checkmark'></span>
                                                    {{ trans('langYes') }}
                                                </label>

                                                <label class='label-container'>
                                                    <input id='allow_unreg_mentee_no' type='checkbox' name='yes_allow_unreg' value='0' {{ $allow_unreg_mentee_from_program == 0 ? 'checked' : '' }}>
                                                    <span class='checkmark'></span>
                                                    {{ trans('langNo') }}
                                                </label>
                                            
                                        </div>

                                    </div>

                                    <div class="carousel-item">
                                        <p class='badge bgNormalBlueText fs-6 text-uppercase TextBold mb-3'>{{ trans('langStepMentoring') }}&nbsp2</p>

                                        <div class='d-none d-md-block mb-3'>
                                            <div class='col-12 d-flex justify-content-md-center align-items-md-center'>
                                                <div class='mentoring-step-2 badge bg-white normalBlueText d-flex justify-content-center align-items-center fs-4 rounded-pill'>1</div>
                                                <div class='mentoring-line'></div>
                                                <div class='mentoring-step-1 badge bgNormalBlueText d-flex justify-content-center align-items-center fs-4 text-white rounded-pill'>2</div>
                                            </div>
                                        </div>


                                        


                                        @if(count($all_specializations))
                                            
                                            <p class='TextBold small-text blackBlueText mt-5 mb-3 d-flex justify-content-start align-items-start'>
                                                <span class='badge bg-info me-2'><span class='fa fa-info'></span></span>
                                                <span class='d-inline'>{!! trans('langSearchMentorsForEditProgram') !!}</span>
                                            </p>

                                            <select id='allTagsSelect' class='d-none' multiple>
                                                @foreach($all_specializations as $tag)
                                                    @php 

                                                        $skills = Database::get()->queryArray("SELECT *FROM mentoring_skills 
                                                                                            WHERE id IN (SELECT skill_id FROM mentoring_specializations_skills 
                                                                                                        WHERE specialization_id = ?d)",$tag->id);
                                                    @endphp
                                                    @if(count($skills) > 0)
                                                        <ul class='p-0' style='list-style-type: none;'>
                                                            @foreach($skills as $sk)
                                                                <option value='{{ $sk->id }}' selected></option>
                                                            @endforeach
                                                        </ul>
                                                    @endif
                                                @endforeach
                                            </select>
                                           

                                            <div class="accordion accordion-flush specializationsAccordion" id="accordionSpeciliazations">
                                                @foreach($all_specializations as $tag)
                                                    <div class="accordion-item border-0 bg-transparent">
                                                        <h2 class="accordion-header" id="heading{{ $tag->id }}">
                                                            <button class="accordion-button ps-2 pe-2 text-uppercase" type="button" data-bs-toggle="collapse" data-bs-target="#collapse{{ $tag->id }}" aria-expanded="true" aria-controls="collapse{{ $tag->id }}">
                                                                
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
                                                        <div id="collapse{{ $tag->id }}" class="accordion-collapse collapse show" aria-labelledby="heading{{ $tag->id }}" data-bs-parent="#accordionSpeciliazations">
                                                            <div class="accordion-body">
                                                                @php 
                                                                    

                                                                    $skills = Database::get()->queryArray("SELECT *FROM mentoring_skills 
                                                                                            WHERE id IN (SELECT skill_id FROM mentoring_specializations_skills 
                                                                                                        WHERE specialization_id = ?d)",$tag->id);
                                                                @endphp
                                                                @if(count($skills) > 0)
                                                                    <div class='col-12'>
                                                                        @foreach($skills as $sk)
                                                                            <input id='loopOneAllTags' type='hidden' name='loopOne[]' value='{{ $sk->id }}'>
                                                                            <label class='label-container'>
                                                                                <input id='TheSkillIdS{{ $sk->id }}{{ $tag->id }}' class='tagClick' type='checkbox' value='{{ $sk->id }},{{ $tag->id }}'>
                                                                                <span class='checkmark'></span>
                                                                                
                                                                                @php 
                                                                                    $checkTranslationSkill = Database::get()->querySingle("SELECT *FROM mentoring_skills_translations
                                                                                                                                                    WHERE skill_id = ?d AND lang = ?s",$sk->id, $language);
                                                                                @endphp

                                                                                @if($checkTranslationSkill)
                                                                                    {{ $checkTranslationSkill->name }}
                                                                                @else
                                                                                    {{ $sk->name }}
                                                                                @endif
                                                                                
                                                                            </label>
                                                                        @endforeach
                                                                    </div>
                                                                @endif
                                                            </div>
                                                        </div>
                                                    </div>
                                                @endforeach
                                            </div>

                                            <div class='col-12 d-flex justify-content-center align-items-center mt-3 mb-3'>
                                                <button id='SearchMentors' type='button' class='btn btn-outline-primary clickSearchMentors small-text'>
                                                    <span class='fa fa-search'></span>&nbsp{{ trans('langSearch')}}
                                                </button>
                                                <button class='btn btn-outline-danger uncheckBtn ms-2 small-text' type='button'><span class='fa fa-times'></span></button>
                                            </div>
                                           
                                            <div class='col-12 mt-3 colMentorsChoosing'>
                                                <table id='MentorsTable' class='table-default rounded-2 w-100'>
                                                    <thead class='list-header'>
                                                        <th>{{ trans('langName') }}</th>
                                                        <th>Email</th>
                                                        <th class='text-start'>{{ trans('langSelect') }}</th>
                                                    </thead>
                                                    <tbody></tbody>
                                                </table>
                                            </div>
                                          
                                        @endif


                                        <input id='selectedMentors' type='hidden' name='check_mentor[]'>
                                        <div class='form-group mt-5 d-flex justify-content-center align-items-center'>
                                            <input class='btn submitAdminBtn submitCreate' name='edit_mentoring_program' value="{{ trans('langCreate') }}">                          
                                            <a href='{{ $urlAppend }}mentoring_programs/{{ $mentoring_program_code }}/index.php' class='btn cancelAdminBtn ms-1'>{{ trans('langCancel') }}</a> 
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

<script>

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


        const array_mentor_ids = [];
        const array_specializations = [];
        const array_tags = []; var loop = 1; var initialIdsMentors = 0; var initialIdsMentors2 = 0;
        sendAllTags(array_tags,loop,array_mentor_ids,array_mentor_ids);
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
                    data: {dataa : jsonString, program : 'edit', Mentors : mentorString, Specialization : specializations, FirstLoop : 'false'}, 
                    cache: true,
                    dataSrc:""
                },
                columns:[
                    {data:"name"},
                    {data:"email"},
                    {data:"choose"}
                ]
            });
        });



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
            if(initialIdsMentors == 0){
                array_mentor_ids.splice(0,array_mentor_ids.length);
                var myTable = $('#MentorsTable').dataTable();
                var rowcollection = myTable.$(".clickerMentor:checked", {"page": "all"});
                rowcollection.each(function(index,elem){
                    var checkbox_value = $(elem).val();
                    array_mentor_ids.push(checkbox_value);
                });
                initialIdsMentors = 1;
            }
            document.getElementById("selectedMentors").value = array_mentor_ids;
        
            document.getElementById("myForm").submit();
        });

        $('.uncheckBtn').on('click',function(){
            uncheckAll(array_tags,array_specializations);
        });

        $('.showProgramsBtn').on('click',function(){
            localStorage.setItem("MenuMentoring","program");
        });

    });

    function sendTags(array_tags,array_specializations,loop){
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

            console.log(array_tags);
            console.log(array_specializations);
        });  
    }

    function sendAllTags(array_tags,loop,array_mentor_ids){
        if(loop == 1){
            $(".tagClick").trigger("click");
            var all_tags = [];
            all_tags.push($('#allTagsSelect').val());
            for(i=0; i<all_tags.length; i++){
                for(j=0; j<$('#allTagsSelect').val().length; j++){
                    array_tags.push(all_tags[i][j]);
                }
            }

            var jsonString = JSON.stringify(array_tags);
            var mentorString = JSON.stringify(array_mentor_ids);
            var table = $('#MentorsTable').dataTable({
                ajax:{
                    type: "POST",
                    url: "{{ $urlAppend }}modules/mentoring/programs/getMentorsByTags.php",
                    data: {dataa : jsonString, program : 'edit', Mentors : mentorString, FirstLoop : 'true'}, 
                    dataSrc:""
                },
                columns:[
                    {data:"name"},
                    {data:"email"},
                    {data:"choose"} 
                    
                ]
            });
            loop++;
            $('.tagClick').prop('checked',false);
            array_tags.splice(0,array_tags.length);
        }
    }

    function uncheckAll(array_tags,array_specializations){
        $('.tagClick').prop('checked',false);
        array_tags.splice(0,array_tags.length);
        array_specializations.splice(0,array_specializations.length);
    }

</script>

@endsection