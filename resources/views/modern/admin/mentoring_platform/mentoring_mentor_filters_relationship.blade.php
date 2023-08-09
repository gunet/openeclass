
@extends('layouts.default')

@push('head_styles')
    <link rel='stylesheet' type='text/css' href="{{ $urlServer }}/template/modern/css/mentoringCssAdmin.css" />
@endpush

@section('content')

<div class="col-12 main-section">
    <div class='{{ $container }}'>
        <div class="row rowMargin">
                    @include('layouts.partials.legend_view')
                    
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

                    @if(count($list_skills_mentors) > 0)
                        

                        <div class="accordion mb-4" id="accordionExample">
                            <div class="accordion-item">
                                <h2 class="accordion-header" id="headingOne">
                                    <button class="accordion-button bg-white TextBold blackBlueText" type="button" data-bs-toggle="collapse" data-bs-target="#collapseOne" aria-expanded="true" aria-controls="collapseOne">
                                        {{ trans('langMentorsHasAdded')}}
                                    </button>
                                </h2>
                                <div id="collapseOne" class="accordion-collapse collapse" aria-labelledby="headingOne" data-bs-parent="#accordionExample">
                                    <div class="accordion-body">
                                         <div class='col-12 mb-4'>
                                            <table class='table table-default rounded-2' id="table_filters_mentors">
                                                <thead>
                                                    <tr class='list-header'>
                                                        <th>{{ trans('langMentoringMentors') }}</th>
                                                        <th>{{ trans('langSkills') }}</th>
                                                        <th class='text-center'>{{ trans('langSpecializations') }}</th>
                                                    </tr>
                                                </thead>
                                                <tbody>


                                                    @foreach($list_skills_mentors as $result => $key)
                                                        <tr>
                                                            <td>
                                                                @php 
                                                                    $userMentors = Database::get()->queryArray("SELECT id,user_id FROM mentoring_mentor_skills
                                                                                                                WHERE specialization_id = ?d AND skill_id = ?d",$key->specialization_id,$key->skill_id); 
                                                                    
                                                                @endphp
                                                                <ul>
                                                                    @foreach($userMentors as $m)
                                                                        @php 
                                                                            $profile_img = profile_image($m->user_id, IMAGESIZE_SMALL, 'img-responsive img-circle img-profile'); 
                                                                            $ifExistUserInDb = Database::get()->querySingle("SELECT *FROM user WHERE id = ?d",$m->user_id);
                                                                            if($ifExistUserInDb){
                                                                                $username = Database::get()->querySingle("SELECT givenname FROM user WHERE id = ?d",$m->user_id)->givenname;
                                                                                $surname = Database::get()->querySingle("SELECT surname FROM user WHERE id = ?d",$m->user_id)->surname;
                                                                            }else{
                                                                                $username = '';
                                                                                $surname = '';
                                                                            }
                                                                            

                                                                            
                                                                        @endphp
                                                                        <li class='mb-2'>
                                                                            {!! $profile_img !!}&nbsp
                                                                            {{ $username }}&nbsp
                                                                            {{ $surname }}&nbsp

                                                                            <button class="btn btn-sm rounded-2 TextSemiBold text-uppercase"
                                                                                data-bs-toggle="modal" data-bs-target="#DeleteMentorModal{{ $m->id }}">
                                                                                <span class='fa fa-times text-danger'></span>
                                                                            </button>

                                                                            <div class="modal fade" id="DeleteMentorModal{{ $m->id }}" tabindex="-1" aria-labelledby="DeleteMentorModalLabel{{ $m->id }}" aria-hidden="true">
                                                                                <form method="post" action="{{ $_SERVER['SCRIPT_NAME'] }}">
                                                                                    <div class="modal-dialog modal-md">
                                                                                        <div class="modal-content">
                                                                                            <div class="modal-header">
                                                                                                <h5 class="modal-title" id="DeleteMentorModalLabel{{ $m->id }}">{{ trans('langDelete') }}</h5>
                                                                                                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                                                                                            </div>
                                                                                            <div class="modal-body">
                                                                                                {{ trans('langDelMentorFromSkill') }}
                                                                                                <input type='hidden' name='UserdelMentorFromFilter' value='{{ $m->user_id }}'>
                                                                                                <input type='hidden' name='delMentorFromFilter' value='{{ $m->id }}'>
                                                                                            </div>
                                                                                            <div class="modal-footer">
                                                                                                <a class="btn btn-outline-secondary small-text rounded-2" href="" data-bs-dismiss="modal">{{ trans('langCancel') }}</a>
                                                                                                <button type='submit' class="btn btn-danger small-text rounded-2" name="deleteUserMentorFromSkill">
                                                                                                    {{ trans('langDelete') }}
                                                                                                </button>

                                                                                            </div>
                                                                                        </div>
                                                                                    </div>
                                                                                </form>
                                                                            </div>

                                                                        </li>
                                                                    @endforeach
                                                                </ul>
                                                            </td>
                                                            <td>
                                                                @php $skillName = Database::get()->querySingle("SELECT name FROM mentoring_skills WHERE id = ?d",$key->skill_id)->name; @endphp
                                                                
                                                                @php 
                                                                    $checkTranslationSkill = Database::get()->querySingle("SELECT *FROM mentoring_skills_translations
                                                                                                                                    WHERE skill_id = ?d AND lang = ?s",$key->skill_id, $language);
                                                                @endphp

                                                                @if($checkTranslationSkill)
                                                                    {{ $checkTranslationSkill->name }}
                                                                @else
                                                                    {{ $skillName }}
                                                                @endif
                                                            </td>
                                                            <td>
                                                                @php $specializationName = Database::get()->querySingle("SELECT name FROM mentoring_specializations WHERE id = ?d",$key->specialization_id)->name; @endphp
                                                                
                                                                @php 
                                                                    $checkTranslationSpecialization = Database::get()->querySingle("SELECT *FROM mentoring_specializations_translations
                                                                                                                                    WHERE specialization_id = ?d AND lang = ?s",$key->specialization_id, $language);
                                                                @endphp

                                                                @if($checkTranslationSpecialization)
                                                                    {{ $checkTranslationSpecialization->name }}&nbsp
                                                                @else
                                                                    {{ $specializationName }}
                                                                @endif
                                                            </td>
                                                        </tr>
                                                    @endforeach

                                                </tbody>
                                            </table>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>


                        
                    @endif


                    <div class='col-12'>
                        <div class='form-wrapper form-edit rounded-2 p-3' style='border:solid 1px #e8e8e8;'>
                            <form method='post' action="{{ $_SERVER['SCRIPT_NAME'] }}">

                                <div class='form-group'>
                                    <label class='col-12 control-label-notes mb-2'>{{ trans('langOurMentors') }} - &nbsp{!! trans('langChooser') !!}</label>
                                    <label class='help-block'><span class='text-info fs-6'>(*)</span>&nbsp{{ trans('langShowAllMentors') }}</label>
                                    <div class='col-12'>
                                        @if(count($all_mentors) > 0)
                                            <select class='form-select rounded-2' name='mentor_ids[]' id='select-mentors' multiple>
                                                @foreach($all_mentors as $m)
                                                   <option value='{{ getIndirectReference($m->id) }}'>{{ $m->givenname }}&nbsp{{ $m->surname }}</option>
                                                @endforeach
                                            </select>
                                        @endif
                                    </div>
                                </div>

                                @if(count($list_specializations) > 0)
                                    <div class='form-group mt-5'>
                                        <div class='col-12'>
                                            <label class='col-12 control-label-notes mb-2'>{{ trans('langAvailableSpecializations') }} - &nbsp{!! trans('langChooser') !!}</label>
                                            <div class='table-responsive'>
                                                <table class='table table-default rounded-2'>
                                                    <thead>
                                                        <tr class='list-header'>
                                                            <th>{{ trans('langSpecializations') }}</th>
                                                            <th>{{ trans('langSkillCanOfferMentor') }}</th>
                                                        </tr>
                                                    </thead>
                                                    <tbody>
                                                        @foreach($list_specializations as $l)
                                                            
                                                            <tr>
                                                                
                                                                <td>
                                                                    <div class='d-flex justify-content-start align-items-center mb-2'>
                                                                        <div>
                                                                            
                                                                            @php 
                                                                                $checkTranslationSpecialization = Database::get()->querySingle("SELECT *FROM mentoring_specializations_translations
                                                                                                                                                WHERE specialization_id = ?d AND lang = ?s",$l->id, $language);
                                                                            @endphp

                                                                            @if($checkTranslationSpecialization)
                                                                                {{ $checkTranslationSpecialization->name }}
                                                                            @else
                                                                                {{ $l->name }}
                                                                            @endif
                                                                        </div>
                                                                    </div>
                                                                </td>
                                                                <td>
                                                                    @php 
                                                                    

                                                                        $skills = Database::get()->queryArray("SELECT *FROM mentoring_skills 
                                                                                                WHERE id IN (SELECT skill_id FROM mentoring_specializations_skills 
                                                                                                            WHERE specialization_id = ?d)",$l->id);
                                                                    @endphp
                                                                    @if(count($skills) > 0)
                                                                        <ul>
                                                                            
                                                                            @foreach($skills as $s)
                                                                                <label class='label-container'>
                                                                                    
                                                                                    <input type='checkbox' name='skills[{{ $l->id }}][]' value='{{ $s->id }}'>
                                                                                    <span class='checkmark'></span>
                                                                                    @php 
                                                                                        $checkTranslationSkill = Database::get()->querySingle("SELECT *FROM mentoring_skills_translations
                                                                                                                                                        WHERE skill_id = ?d AND lang = ?s",$s->id, $language);
                                                                                    @endphp

                                                                                    @if($checkTranslationSkill)
                                                                                        {{ $checkTranslationSkill->name }}
                                                                                    @else
                                                                                        {{ $s->name }}
                                                                                    @endif
                                                                                   
                                                                                </label>
                                                                            @endforeach
                                                                        </ul>
                                                                    @endif
                                                                </td>
                                                                
                                                            </tr>
                                                        @endforeach
                                                    </tbody>
                                                </table>
                                            </div>
                                        </div>
                                    </div>
                                @endif

                                <div class='form-group mt-5'>
                                    <div class='col-12 d-flex justify-content-center align-items-center'>
                                        <input type='submit' class='btn submitAdminBtn' name='submitSkills' value='{{ trans('langSubmit')}}'>
                                    </div>
                                </div>
                                

                            </form>
                        </div>
                    </div>

                    
                    
                    
               

        </div>
      
    </div>
</div>

<script type="text/javascript">
    $(document).ready( function () {
        $('#table_filters_mentors').DataTable();
        $('#tablet_filters').DataTable();
    } );
</script>

@endsection