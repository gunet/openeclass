
@extends('layouts.default')

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
                    
                    <div class='col-12'>
                        <div class='col-md-9 col-12 ms-auto me-auto'>{!! $action_bar !!}</div>
                    </div>

                    <div class='col-lg-6 col-12 d-none d-md-none d-lg-block'>
                          <div class='col-12 h-100 left-form'></div>
                    </div>
                    <div class='col-lg-6 col-12'>
                        <div class='form-wrapper form-edit rounded p-3'>
                            <form method='post' action="{{ $_SERVER['SCRIPT_NAME'] }}">
                                <div class='form-group'>
                                    <label class='col-12 mb-2'>{{ trans('langEnableMentoringPlatform') }}</label>
                                    <div class='col-12'>
                                        <div class='checkbox'>
                                            <label>
                                                <input id='enable_mentoring' type='checkbox' name='enable_mentoring' value='1' {!! get_config('mentoring_platform') == 1 ? 'checked' : '' !!}>{{ trans('langActivate') }}
                                            </label>
                                            <label class='ms-3'>
                                                <input id='disable_mentoring' type='checkbox' name='enable_mentoring' value='0' {!! get_config('mentoring_platform') == 0 ? 'checked' : '' !!}>{{ trans('langDeactivate') }}
                                            </label>
                                        </div>
                                    </div>
                                </div>

                                <div class='form-group mt-4'>
                                    <label class='col-12 mb-2'>{{ trans('langAlwaysActiveMentoringPlatform') }}</label>
                                    <div class='col-12'>
                                        <div class='checkbox'>
                                            <label>
                                                <input id='always_mentoring' type='checkbox' name='always_active_mentoring' value='1' {!! get_config('mentoring_always_active') == 1 ? 'checked' : '' !!}>{{ trans('langYes')}}
                                            </label>
                                            <label class='ms-3'>
                                                <input id='no_always_mentoring' type='checkbox' name='always_active_mentoring' value='0' {!! get_config('mentoring_always_active') == 0 ? 'checked' : '' !!}>{{ trans('langNo')}}
                                            </label>
                                        </div>
                                    </div>
                                </div>

                                <div class='form-group mt-4'>
                                    <label class='col-12 mb-2'>{{ trans('langTutorAsMenteeInPlatform') }}</label>
                                    <div class='col-12'>
                                        <div class='checkbox'>
                                            <label>
                                                <input id='yes_tutor_mentee' type='checkbox' name='tutor_as_mentee' value='1' {!! get_config('mentoring_tutor_as_mentee') == 1 ? 'checked' : '' !!}>{{ trans('langYes')}}
                                            </label>
                                            <label class='ms-3'>
                                                <input id='no_tutor_mentee' type='checkbox' name='tutor_as_mentee' value='0' {!! get_config('mentoring_tutor_as_mentee') == 0 ? 'checked' : '' !!}>{{ trans('langNo')}}
                                            </label>
                                        </div>
                                    </div>
                                </div>

                                <div class='form-group mt-4'>
                                    <label class='col-12 mb-2'>{{ trans('langMentorAsTutorProgram') }}</label>
                                    <div class='col-12'>
                                        <div class='checkbox'>
                                            <label>
                                                <input id='yes_mentor_tutor' type='checkbox' name='mentor_as_tutorProgram' value='1' {!! get_config('mentoring_mentor_as_tutorProgram') == 1 ? 'checked' : '' !!}>{{ trans('langYes')}}
                                            </label>
                                            <label class='ms-3'>
                                                <input id='no_mentor_tutor' type='checkbox' name='mentor_as_tutorProgram' value='0' {!! get_config('mentoring_mentor_as_tutorProgram') == 0 ? 'checked' : '' !!}>{{ trans('langNo')}}
                                            </label>
                                        </div>
                                    </div>
                                </div>

                                <div class='form-group mt-5'>
                                    <div class='col-12 d-flex justify-content-center align-items-center'>
                                        <input class="btn submitAdminBtn @if(!get_config('creation_mentoring_tables')) pe-none opacity-help @endif" type='submit' value='{{ trans('langSubmit') }}' name='enable_disable_mentoring'>
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

        if($('#enable_mentoring').is(":checked")){
           $('#disable_mentoring').attr('disabled',true);
           $('#always_mentoring').attr('disabled',false);
           $('#no_always_mentoring').attr('disabled',false);
           $('#yes_tutor_mentee').attr('disabled',false);
            $('#no_tutor_mentee').attr('disabled',false);
            $('#yes_mentor_tutor').attr('disabled',false);
            $('#no_mentor_tutor').attr('disabled',false);
        }
        $('#enable_mentoring').on('click',function(){
            if($('#enable_mentoring').is(":checked")){
                $('#disable_mentoring').attr('disabled',true);
                $('#always_mentoring').attr('disabled',false);
                $('#no_always_mentoring').attr('disabled',false);
                $('#yes_tutor_mentee').attr('disabled',false);
                $('#no_tutor_mentee').attr('disabled',false);
                $('#yes_mentor_tutor').attr('disabled',false);
                $('#no_mentor_tutor').attr('disabled',false);
            }else{
                $('#disable_mentoring').prop("disabled", false);
            }
            
        })


        if($('#disable_mentoring').is(":checked")){
           $('#enable_mentoring').attr('disabled',true);
           $('#always_mentoring').attr('disabled',true);
           $('#no_always_mentoring').attr('disabled',true);
           $('#yes_tutor_mentee').attr('disabled',true);
           $('#no_tutor_mentee').attr('disabled',true);
           $('#yes_mentor_tutor').attr('disabled',true);
            $('#no_mentor_tutor').attr('disabled',true);
        }
        $('#disable_mentoring').on('click',function(){
            if($('#disable_mentoring').is(":checked")){
                $('#enable_mentoring').attr('disabled',true);
                $('#always_mentoring').attr('disabled',true);
                $('#no_always_mentoring').attr('disabled',true);
                $('#yes_tutor_mentee').attr('disabled',true);
                $('#no_tutor_mentee').attr('disabled',true);
                $('#yes_mentor_tutor').attr('disabled',true);
                $('#no_mentor_tutor').attr('disabled',true);
            }else{
                $('#enable_mentoring').prop("disabled", false);
            }
            
        })

    })
   
</script>

@endsection