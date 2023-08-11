
@extends('layouts.default')

@push('head_styles')
    <link rel='stylesheet' type='text/css' href="{{ $urlServer }}/template/modern/css/mentoringCssAdmin.css" />
@endpush

@section('content')

<div class="col-12 main-section">
    <div class='{{ $container }}'>
        <div class="row m-auto">
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

                    

                    <div class='col-lg-6 col-12'>
                        <div class='form-wrapper form-edit rounded p-3'>
                            <form method='post' action="{{ $_SERVER['SCRIPT_NAME'] }}">


                                <div class='form-group'>
                                    <label class='col-12 control-label-notes mb-1'>{{ trans('langFieldsExpertise') }}</label>
                                    <label class='help-block'><span class='text-info fs-6'>(*)</span>&nbsp{{ trans('langInsertFieldsExpertise') }}</label>
                                    <div class='col-12'>
                                        <input type='text' class='form-control' name='Expertise' required>
                                    </div>
                                </div>



                                <div class='form-group mt-4'>
                                    <label class='col-12 control-label-notes mb-1'>{{ trans('langMentoringSkills') }}</label>
                                    <label class='help-block'><span class='text-info fs-6'>(*)</span>&nbsp{{ trans('langInsertMentoringSkills') }}</label>
                                    <div class='col-12'>
                                        <select id='tag-skills' class='form-select' name='SkillsTags[]' multiple required></select>
                                    </div>
                                </div>

                                

                                <div class='form-group mt-4'>
                                    <label class='col-12 control-label-notes mb-1'>{{ trans('langMentoringKeyWords') }}</label>
                                    <label class='help-block'><span class='text-info fs-6'>(*)</span>&nbsp{{ trans('langInsertMentoringKeyWords') }}</label>
                                    <div class='col-12'>
                                        <select id='tag-keywords' class='form-select' name='KeywordsTags[]' multiple required></select>
                                    </div>
                                </div>


                                <div class="form-group mt-4">
                                    <label for="language_form" class="col-sm-12 control-label-notes">{{ trans('langLanguage') }}</label>
                                    <div class="col-sm-12">
                                        {!! lang_select_options('language_form', "class='form-control'", Session::has('language_form') ? Session::get('language_form'): $language) !!}
                                        
                                    </div>                
                                </div>

                                <div class='form-group mt-5'>
                                    <div class='col-12 d-flex justify-content-center align-items-center'>
                                        <input type='submit' class='btn submitAdminBtn' name='submitFilters' value='{{ trans('langSubmit')}}'>
                                    </div>
                                </div>

                            </form>
                        </div>
                    </div>

                    <div class='col-lg-6 col-12 d-none d-md-none d-lg-block'>
                          <div class='col-12 h-100 left-form'></div>
                    </div>

                    @if(count($list_specializations) > 0)
                        <div class='col-12 mt-4'>
                            <table class='table table-default rounded-2' id="table_filters">
                                <thead>
                                    <tr class='list-header'>
                                        <th>{{ trans('langSpecializations') }}</th>
                                        <th>{{ trans('langSkills') }}</th>
                                        <th>{{ trans('langKeyWord') }}</th>
                                        <th class='text-center'>{{ trans('langDelete') }}</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach($list_specializations as $l)
                                        <tr>
                                            <td>
                                                
                                                @php 
                                                    $checkTranslationSpecialization = Database::get()->querySingle("SELECT *FROM mentoring_specializations_translations
                                                                                                                    WHERE specialization_id = ?d AND lang = ?s",$l->id, $language);
                                                @endphp

                                                @if($checkTranslationSpecialization)
                                                    {{ $checkTranslationSpecialization->name }}&nbsp
                                                @else
                                                    {{ $l->name }}&nbsp
                                                @endif
                                                <button class="btn btn-sm rounded-2 TextSemiBold text-uppercase mt-0"
                                                    data-bs-toggle="modal" data-bs-target="#TranslationSpecializationModal{{ $l->id }}">
                                                    <span class='fa fa-language text-primary'></span>
                                                </button>

                                                <div class="modal fade" id="TranslationSpecializationModal{{ $l->id }}" tabindex="-1" aria-labelledby="TranslationSpecializationModalLabel{{ $l->id }}" aria-hidden="true">
                                                    <form method="post" action="{{ $_SERVER['SCRIPT_NAME'] }}">
                                                        <div class="modal-dialog modal-md">
                                                            <div class="modal-content">
                                                                <div class="modal-header">
                                                                    <h5 class="modal-title" id="TranslationSpecializationModal{{ $l->id }}"><span class='TextBold'>
                                                                        {{ trans('langTranslation') }}</span> &nbsp 
                                                                        @if($checkTranslationSpecialization)
                                                                            ({{ $checkTranslationSpecialization->name }})
                                                                        @else
                                                                            ({{ $l->name }})
                                                                        @endif
                                                                    </h5>
                                                                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                                                                </div>
                                                                <div class="modal-body text-start">

                                                                    @php 
                                                                        $hasTranslated = Database::get()->queryArray("SELECT *FROM mentoring_specializations_translations
                                                                                                                        WHERE specialization_id = ?d",$l->id); 
                                                                    @endphp

                                                                    @if(count($hasTranslated) > 0)
                                                                        <p class='TextBold text-start mb-1'>{{ trans('langHasTranslated')}}</p>
                                                                        <ul>
                                                                            
                                                                            @foreach($hasTranslated as $h)
                                                                                @if($h->lang == 'el')
                                                                                    <li>{{ trans('langGreek')}}</li>
                                                                                @endif

                                                                                @if($h->lang == 'en')
                                                                                    <li>{{ trans('langEnglish')}}</li>
                                                                                @endif

                                                                                @if($h->lang == 'de')
                                                                                    <li>{{ trans('langGerman')}}</li>
                                                                                @endif

                                                                                @if($h->lang == 'es')
                                                                                    <li>{{ trans('langSpanish')}}</li>
                                                                                @endif

                                                                                @if($h->lang == 'fr')
                                                                                    <li>{{ trans('langFrench')}}</li>
                                                                                @endif

                                                                                @if($h->lang == 'it')
                                                                                    <li>{{ trans('langItalian')}}</li>
                                                                                @endif
                                                                            @endforeach
                                                                        </ul>
                                                                    @endif

                                                                    <input type='hidden' name='translationSpecializationId' value='{{ $l->id }}'>
                                                                    <label class='control-label-notes'>{{ trans('langName') }}</label>
                                                                    <input class='form-control mb-3' type='text' name='specializationNameTranslation' required>
                                                                    <label class='control-label-notes'>{{ trans('langLanguage') }}</label>
                                                                    {!! lang_select_options('language_form', "class='form-control'", Session::has('language_form') ? Session::get('language_form'): $language) !!}
                                                                </div>
                                                                <div class="modal-footer">
                                                                    <a class="btn btn-outline-secondary small-text rounded-2" href="" data-bs-dismiss="modal">{{ trans('langCancel') }}</a>
                                                                    <button type='submit' class="btn btn-primary small-text rounded-2" name="submitTranslationSpecialization">
                                                                        {{ trans('langSubmit') }}
                                                                    </button>

                                                                </div>
                                                            </div>
                                                        </div>
                                                    </form>
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
                                                            <li>
                                                                
                                                                @php 
                                                                    $checkTranslationSkill = Database::get()->querySingle("SELECT *FROM mentoring_skills_translations
                                                                                                                                    WHERE skill_id = ?d AND lang = ?s",$s->id, $language);
                                                                @endphp

                                                                @if($checkTranslationSkill)
                                                                    {{ $checkTranslationSkill->name }}
                                                                @else
                                                                    {{ $s->name }}
                                                                @endif
                                                            </li>
                                                        @endforeach
                                                    </ul>
                                                @endif
                                                <div class='d-flex justify-content-start align-items-start'>
                                                    <span class='badge bg-info'><span class='fa fa-info'></span></span>
                                                    <span class='TextBold small-text blackBlueText ms-1'>{{ trans('langAddSkillIfYouWant') }}</span>
                                                </div>
                                                <button class="btn btn-sm rounded-2 TextBold" data-bs-toggle="collapse" data-bs-target="#collapse{{ $l->id }}" aria-expanded="false" aria-controls="collapse{{ $l->id }}">
                                                    <span class='text-primary TextBold'>{{ trans('langClickHere') }}</span>
                                                </button>
                                                <div class="col-12 collapse" id="collapse{{ $l->id }}">
                                                    <div class="card card-body p-3 bg-white rounded-2 shadow-sm">


                                                        <div class='col-12'>
                                                            <label class='control-label-notes'>{{ trans('langNameSkill') }}</label>
                                                            <form action="{{ $_SERVER['SCRIPT_NAME'] }}" method='post'>
                                                                <input type='hidden' name='specialization_id' value='{{ $l->id }}'>
                                                                <input class='form-control mb-3' type='text' name='skillName' required>
                                                                <label class='control-label-notes'>{{ trans('langLanguage') }}</label>
                                                                {!! lang_select_options('language_form', "class='form-control'", Session::has('language_form') ? Session::get('language_form'): $language) !!}
                                                                <input class='btn btn-outline-success btn-sm small-text mt-2' type='submit' name='submitAddSkill' value="{{ trans('langAdd') }}">
                                                            </form>
                                                        </div><hr>

                                                        <div class='col-12'>
                                                            @php 
                                                                $skills = Database::get()->queryArray("SELECT *FROM mentoring_skills 
                                                                                            WHERE id IN (SELECT skill_id FROM mentoring_specializations_skills 
                                                                                                        WHERE specialization_id = ?d)",$l->id);
                                                            @endphp
                                                            @if(count($skills) > 0)
                                                                <label class='control-label-notes mb-2'>{{ trans('langTranslationExisting') }}</label>
                                                                <ul style='list-style-type: none;' class='ps-0'>
                                                                    
                                                                    @foreach($skills as $s)
                                                                        <li class='mb-2'>
                                                                            
                                                                                <span>
                                                                                    
                                                                                    @php 
                                                                                        $checkTranslationSkill = Database::get()->querySingle("SELECT *FROM mentoring_skills_translations
                                                                                                                                                        WHERE skill_id = ?d AND lang = ?s",$s->id, $language);
                                                                                    @endphp

                                                                                    @if($checkTranslationSkill)
                                                                                        {{ $checkTranslationSkill->name }}
                                                                                    @else
                                                                                        {{ $s->name }}
                                                                                    @endif
                                                                                </span>

                                                                                <button class="btn btn-sm rounded-2 TextSemiBold text-uppercase mt-0"
                                                                                    data-bs-toggle="modal" data-bs-target="#TranslationSkillModal{{ $l->id }}{{ $s->id }}">
                                                                                    <span class='fa fa-language text-primary'></span>
                                                                                </button>
                                                                            
                                                                        </li>

                                                                        <div class="modal fade" id="TranslationSkillModal{{ $l->id }}{{ $s->id }}" tabindex="-1" aria-labelledby="TranslationSkillModalLabel{{ $l->id }}{{ $s->id }}" aria-hidden="true">
                                                                            <form method="post" action="{{ $_SERVER['SCRIPT_NAME'] }}">
                                                                                <div class="modal-dialog modal-md">
                                                                                    <div class="modal-content">
                                                                                        <div class="modal-header">
                                                                                            <h5 class="modal-title" id="TranslationSkillModalLabel{{ $l->id }}{{ $s->id }}">
                                                                                                <span class='TextBold'>{{ trans('langTranslation') }}</span> &nbsp 
                                                                                                @if($checkTranslationSkill)
                                                                                                    ({{ $checkTranslationSkill->name }})
                                                                                                @else
                                                                                                    ({{ $s->name }})
                                                                                                @endif
                                                                                               
                                                                                            </h5>
                                                                                            <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                                                                                        </div>
                                                                                        <div class="modal-body text-start">

                                                                                            @php 
                                                                                                $hasTranslated = Database::get()->queryArray("SELECT *FROM mentoring_skills_translations
                                                                                                                                                WHERE skill_id = ?d",$s->id); 
                                                                                            @endphp

                                                                                            @if(count($hasTranslated) > 0)
                                                                                                <p class='TextBold text-start mb-1'>{{ trans('langHasTranslated')}}</p>
                                                                                                <ul class='mb-3'>
                                                                                                    
                                                                                                    @foreach($hasTranslated as $h)
                                                                                                        @if($h->lang == 'el')
                                                                                                            <li>{{ trans('langGreek')}}</li>
                                                                                                        @endif

                                                                                                        @if($h->lang == 'en')
                                                                                                            <li>{{ trans('langEnglish')}}</li>
                                                                                                        @endif

                                                                                                        @if($h->lang == 'de')
                                                                                                            <li>{{ trans('langGerman')}}</li>
                                                                                                        @endif

                                                                                                        @if($h->lang == 'es')
                                                                                                            <li>{{ trans('langSpanish')}}</li>
                                                                                                        @endif

                                                                                                        @if($h->lang == 'fr')
                                                                                                            <li>{{ trans('langFrench')}}</li>
                                                                                                        @endif

                                                                                                        @if($h->lang == 'it')
                                                                                                            <li>{{ trans('langItalian')}}</li>
                                                                                                        @endif
                                                                                                    @endforeach
                                                                                                </ul>
                                                                                            @endif


                                                                                            <input type='hidden' name='translationSkillId' value='{{ $s->id }}'>
                                                                                            <label class='control-label-notes'>{{ trans('langNameSkill') }}</label>
                                                                                            <input class='form-control mb-3' type='text' name='skillNameTranslation' required>
                                                                                            <label class='control-label-notes'>{{ trans('langLanguage') }}</label>
                                                                                            {!! lang_select_options('language_form', "class='form-control'", Session::has('language_form') ? Session::get('language_form'): $language) !!}
                                                                                        </div>
                                                                                        <div class="modal-footer">
                                                                                            <a class="btn btn-outline-secondary small-text rounded-2" href="" data-bs-dismiss="modal">{{ trans('langCancel') }}</a>
                                                                                            <button type='submit' class="btn btn-primary small-text rounded-2" name="submitTranslationSkill">
                                                                                                {{ trans('langSubmit') }}
                                                                                            </button>

                                                                                        </div>
                                                                                    </div>
                                                                                </div>
                                                                            </form>
                                                                        </div>

                                                                    @endforeach
                                                                       
                                                                </ul>
                                                            @endif
                                                        </div><hr>

                                                        <div class='col-12'>
                                                            @php 
                                                                $skills = Database::get()->queryArray("SELECT *FROM mentoring_skills 
                                                                                            WHERE id IN (SELECT skill_id FROM mentoring_specializations_skills 
                                                                                                        WHERE specialization_id = ?d)",$l->id);
                                                            @endphp
                                                            @if(count($skills) > 0)
                                                                <label class='control-label-notes mb-2'>{{ trans('langdelSkill') }}</label>
                                                                <ul style='list-style-type: none;' class='ps-0'>
                                                                    <form action="{{ $_SERVER['SCRIPT_NAME'] }}" method='post'>
                                                                        <input type='hidden' name='specialization_id' value='{{ $l->id }}'>
                                                                        @foreach($skills as $s)
                                                                            <li class='mb-2'>
                                                                                <label class='label-container'>
                                                                                    <input type='checkbox' name='delSkillsIds[]' value='{{ $s->id }}'>
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
                                                                            </li>
                                                                        @endforeach
                                                                        <input class='btn btn-outline-danger btn-sm small-text mt-2' type='submit' name='submitdelSkill' value="{{ trans('langDelete') }}">
                                                                    </form>
                                                                </ul>
                                                            @endif
                                                        </div>
                                                    </div>
                                                </div>
                                            </td>
                                            <td>
                                                @php 
                                                    $keywords = Database::get()->queryArray("SELECT DISTINCT name, COUNT(*) AS nm FROM mentoring_keywords 
                                                                                            WHERE specialization_id = ?d
                                                                                            GROUP BY name
                                                                                            HAVING nm > 0 ORDER BY nm DESC",$l->id);
                                                @endphp
                                                
                                                @if(count($keywords) > 0)
                                                    <ul>
                                                        @foreach($keywords as $k)
                                                            <li>{{ $k->name }}</li>
                                                        @endforeach
                                                    </ul>
                                                @endif

                                                <div class='d-flex justify-content-start align-items-start'>
                                                    <span class='badge bg-info'><span class='fa fa-info'></span></span>
                                                    <span class='TextBold small-text blackBlueText ms-1'>{{ trans('langAddKeyIfYouWant') }}</span>
                                                </div>
                                                <button class="btn btn-sm rounded-2 TextBold" data-bs-toggle="collapse" data-bs-target="#Keycollapse{{ $l->id }}" aria-expanded="false" aria-controls="Keycollapse{{ $l->id }}">
                                                    <span class='text-primary TextBold'>{{ trans('langClickHere') }}</span>
                                                </button>
                                                <div class="col-12 collapse" id="Keycollapse{{ $l->id }}">
                                                    <div class="card card-body p-3 bg-white rounded-2 shadow-sm">
                                                        <div class='col-12'>
                                                            <label class='control-label-notes'>{{ trans('langKeyWord1') }}</label>
                                                            <form action="{{ $_SERVER['SCRIPT_NAME'] }}" method='post'>
                                                                <input type='hidden' name='specialization_id' value='{{ $l->id }}'>
                                                                <input class='form-control' type='text' name='KeyName' required>
                                                                <input class='btn btn-outline-success btn-sm small-text mt-2' type='submit' name='submitAddKey' value="{{ trans('langAdd') }}">
                                                            </form>
                                                        </div><hr>
                                                        <div class='col-12'>
                                                            
                                                            @php 
                                                                $allKeysOfSpacialization = Database::get()->queryArray("SELECT DISTINCT name FROM mentoring_keywords 
                                                                                            WHERE specialization_id = ?d",$l->id);
                                                            @endphp
                                                            @if(count($allKeysOfSpacialization) > 0)
                                                                <label class='control-label-notes mb-2'>{{ trans('langDelete') }}</label>
                                                                <ul style='list-style-type: none;' class='ps-0'>
                                                                    <form action="{{ $_SERVER['SCRIPT_NAME'] }}" method='post'>
                                                                        <input type='hidden' name='specialization_id' value='{{ $l->id }}'>
                                                                        @foreach($allKeysOfSpacialization as $k)
                                                                            <li class='mb-2'>
                                                                                <label class='label-container'>
                                                                                    <input type='checkbox' name='delKeyNames[]' value='{{ $k->name }}'>
                                                                                    <span class='checkmark'></span>
                                                                                    {{ $k->name }}
                                                                                </label>
                                                                            </li>
                                                                        @endforeach
                                                                        <input class='btn btn-outline-danger btn-sm small-text mt-2' type='submit' name='submitDelKey' value="{{ trans('langDelete') }}">
                                                                    </form>
                                                                </ul>
                                                            @endif
                                                        </div>
                                                    </div>
                                                </div>
                                               
                                            </td>
                                            <td class='text-center'>
                                               
                                                <button class="btn btn-sm rounded-2 TextSemiBold text-uppercase"
                                                    data-bs-toggle="modal" data-bs-target="#DeleteSpecializationModal{{ $l->id }}">
                                                    <span class='fa fa-times text-danger'></span>
                                                </button>

                                                <div class="modal fade" id="DeleteSpecializationModal{{ $l->id }}" tabindex="-1" aria-labelledby="DeleteSpecializationModalLabel{{ $l->id }}" aria-hidden="true">
                                                    <form method="post" action="{{ $_SERVER['SCRIPT_NAME'] }}">
                                                        <div class="modal-dialog modal-md">
                                                            <div class="modal-content">
                                                                <div class="modal-header">
                                                                    <h5 class="modal-title" id="DeleteSpecializationModalLabel{{ $l->id }}">{{ trans('langDelete') }}</h5>
                                                                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                                                                </div>
                                                                <div class="modal-body text-start">
                                                                    {!! trans('langDelSpecialization') !!}
                                                                    <input type='hidden' name='delSpecialization' value='{{ $l->id }}'>
                                                                </div>
                                                                <div class="modal-footer">
                                                                    <a class="btn btn-outline-secondary small-text rounded-2" href="" data-bs-dismiss="modal">{{ trans('langCancel') }}</a>
                                                                    <button type='submit' class="btn btn-danger small-text rounded-2" name="deleteSpecialization">
                                                                        {{ trans('langDelete') }}
                                                                    </button>

                                                                </div>
                                                            </div>
                                                        </div>
                                                    </form>
                                                </div>
                                            </td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                    @endif
                    
                    
               

        </div>
      
    </div>
</div>

<script type="text/javascript">
    $(document).ready( function () {
   
        $('#table_filters').DataTable();

    } );
</script>

@endsection