@extends('layouts.default')

@push('head_styles')
<link href="{{ $urlAppend }}js/jstree3/themes/proton/style.min.css" type='text/css' rel='stylesheet'>
@endpush

@push('head_scripts')
<script type='text/javascript' src='{{ $urlAppend }}js/jstree3/jstree.min.js'></script>
<script type='text/javascript' src='{{ $urlAppend }}js/pwstrength.js'></script>
<script type='text/javascript' src='{{ $urlAppend }}js/tools.js'></script>

<script type='text/javascript'>

    function deactivate_input_password () {
            $('#coursepassword').attr('disabled', 'disabled');
            $('#coursepassword').closest('div.form-group').addClass('invisible');
    }

    function activate_input_password () {
            $('#coursepassword').removeAttr('disabled', 'disabled');
            $('#coursepassword').closest('div.form-group').removeClass('invisible');
    }

    function displayCoursePassword() {

            if ($('#courseclose,#courseiactive').is(":checked")) {
                    deactivate_input_password ();
            } else {
                    activate_input_password ();
            }
    }
    
    var lang = {
        pwStrengthTooShort: "{{ js_escape(trans('langPwStrengTooShort')) }}",
        pwStrengthWeak: "{{ js_escape(trans('langPwStrengthWeak')) }}",
        pwStrengthGood: "{{ js_escape(trans('langPwStrengthGood')) }}", 
        pwStrengthStrong: "{{ js_escape(trans('langPwStrengthStrong')) }}"
    }

    function showCCFields() {
        $('#cc').show();
    }
    function hideCCFields() {
        $('#cc').hide();
    }

    $(document).ready(function() {        
                        
        $('#coursepassword').keyup(function() {
            $('#result').html(checkStrength($('#coursepassword').val()))
        });

        displayCoursePassword();

        $('#courseopen').click(function(event) {
                activate_input_password();
        });
        $('#coursewithregistration').click(function(event) {
                activate_input_password();
        });
        $('#courseclose').click(function(event) {
                deactivate_input_password();
        });
        $('#courseinactive').click(function(event) {
                deactivate_input_password();
        });

        $('input[name=l_radio]').change(function () {
            if ($('#cc_license').is(":checked")) {
                showCCFields();
            } else {
                hideCCFields();
            }
        }).change();
    });

</script>
@endpush


@section('content')

<div class="pb-lg-3 pt-lg-3 pb-0 pt-0" >

    <div class="container-fluid main-container">

        <div class="row rowMedium">

          <div class="col-12 justify-content-center col_maincontent_active_Homepage">

              <div class="row p-lg-5 p-md-5 ps-1 pe-1 pt-5 pb-5">

                  @include('layouts.common.breadcrumbs', ['breadcrumbs' => $breadcrumbs])

                  @include('layouts.partials.legend_view',['is_editor' => $is_editor, 'course_code' => $course_code])
                 
                    @if(Session::has('message'))
                    <div class='col-12 all-alerts'>
                        <div class="alert {{ Session::get('alert-class', 'alert-info') }} alert-dismissible fade show" role="alert">
                            @if(is_array(Session::get('message')))
                                @php $messageArray = array(); $messageArray = Session::get('message'); @endphp
                                @foreach($messageArray as $message)
                                    {!! $message !!}
                                @endforeach
                            @else
                                {!! Session::get('message') !!}
                            @endif
                            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                        </div>
                    </div>
                    @endif
                      
                    
                      <div class='col-lg-6 col-12 d-none d-md-none d-lg-block'>
                          <div class='col-12 h-100 left-form'></div>
                      </div>
                      <div class='col-lg-6 col-12'>
                        <div class='form-wrapper form-edit rounded'>
                          <form class='form-horizontal' role='form' method='post' name='createform' action="{{ $_SERVER['SCRIPT_NAME'] }}" onsubmit=\"return validateNodePickerForm();\">
                        
                                    
                                     
                                        <div class='form-group'>
                                            <h3 for='title' class='col-12 control-label-notes'>{{ trans('langTitle') }}</h3>
                                            <div class='col-12'>
                                              <input name='title' id='title' type='text' class='form-control' value="{{ trans('title') }}" placeholder="{{ trans('langTitle') }}">
                                            </div>
                                        </div>
                                    

                                      
                                        <div class='form-group mt-4'>
                                            <h3 for='title' class='col-12 control-label-notes'>{{ trans('langCode') }}</h3>
                                            <div class='col-sm-12'>
                                              <input name='public_code' id='public_code' type='text' class='form-control' value = "{{ trans('public_code') }}"  placeholder="{{ trans('langOptional') }}">
                                            </div>
                                        </div>
                                    


                                   
                                      
                                        <div class='form-group mt-4'>
                                            <h3  class='col-sm-12 control-label-notes'>{{ trans('langFaculty') }}</h3>
                                            <div class='col-sm-12'>
                                              {!! $buildusernode !!}
                                            </div>
                                        </div>
                                     

                                      
                                        <div class='form-group mt-4'>
                                            <h3 for='prof_names' class='col-sm-12 control-label-notes'>{{ trans('langTeachers') }}</h3>
                                            <div class='col-sm-12'>
                                                  <input class='form-control' type='text' name='prof_names' id='prof_names' value= "{{ trans('prof_names') }}">
                                            </div>
                                        </div>
                                     

                                      
                                        <div class='form-group mt-4'>
                                            <h3 for='localize' class='col-sm-12 control-label-notes'>{{ trans('langLanguage') }}</h3>
                                            <div class='col-sm-12'>
                                                  {!! $lang_select_options !!}
                                            </div>
                                        </div>
                                     
                                    

                                   
                                     
                                        <div class='form-group mt-4'>
                                            <label for='description' class='col-sm-12 control-label-notes'>{{trans('langDescrInfo')}} <small>{{trans('langOptional')}}</small></label>
                                            <div class='col-sm-12'>
                                                  {!! $rich_text_editor !!}
                                            </div>
                                        </div>
                                     
                                   
                                

                                    <div class='form-group mt-4'>
                                      
                                           <label class='col-sm-12 control-label-notes mb-2'>{{ trans('langCourseFormat') }}</label>
                                       
                                            <div class='radio mb-2'>
                                              <label>
                                                  <input type='radio' name='view_type' value='simple' id='simple'>
                                                  <span class='ps-2'>{{ trans('langCourseSimpleFormat') }}</span>
                                              </label>
                                            </div>
                                         

                                          
                                            <div class='radio mb-2'>
                                              <label>
                                                <input type='radio' name='view_type' value='units' id='units' checked>
                                                <span class='ps-2'>{{ trans('langWithCourseUnits') }}</span>
                                                </label>
                                            </div>
                                        

                                       
                                            <div class="radio mb-2">
                                              <label>
                                                <input type="radio" name="view_type" value="activity" id="activity">
                                                <span class='ps-2'>{{trans('langCourseActivityFormat') }}</span>
                                              </label>
                                            </div>
                                        

                                        
                                            <div class='radio'>
                                              <label>
                                                <input type='radio' name='view_type' value='wall' id='wall'>
                                                <span class='ps-2'>{{ trans('langCourseWallFormat') }}</span>
                                              </label>
                                            </div>   
                                    </div>
                                        
                                    
                                    <div class='form-group mt-4'>
                                      
                                              <label class='col-sm-12 control-label-notes mb-2'>{{ trans('langOpenCoursesLicense') }}</label>
                                       
                                          
                                              <div class='radio mb-2'>
                                                <label>
                                                  <input type='radio' name='l_radio' value='0' checked>
                                                  <span class='ps-2'>{{ $license_0 }}</span>
                                                </label>
                                              </div>
                                         
                                         
                                              <div class='radio mb-2'>
                                                <label>
                                                  <input type='radio' name='l_radio' value='10'>
                                                  <span class='ps-2'>{{ $license_10 }}</span>
                                                </label>
                                              </div>
                                         
                                          
                                              <div class='radio'>
                                                <label>
                                                  <input id='cc_license' type='radio' name='l_radio' value='cc'>
                                                  <span class='ps-2'>{{ trans("langCMeta['course_license']") }}</span>
                                                </label>
                                              </div>       
                                          
                                    </div>

                                  
                                    <div class='form-group mt-4' id='cc'>
                                        <div class='col-sm-12 col-sm-offset-2'>
                                              {!! $selection_license !!}
                                        </div>              
                                    </div>

                                    <div class='row form-group mt-4'>
                                          <label for='localize' class='col-sm-12 control-label-notes mb-2'>{{trans('langFlippedClassroom')}}</label>
                                      
                                          <div class='col-6 radio'>
                                            <label>
                                              <input id='flippedenabled' type='radio' name='flippedclassroom' value='2'>
                                                  {{trans('langCΕnabled')}}
                                            </label>
                                          </div>

                                          <div class='col-6 radio'>
                                            <label>
                                              <input id='flippeddisabled' type='radio' name='flippedclassroom' value='1' checked>
                                                  {{trans('langTypeInactive')}}
                                            </label>
                                          </div>
                                     
                                    </div>

                                    <div class='form-group mt-4'>
                                      
                                           <label for='localize' class='col-sm-12 control-label-notes mb-2'>{{ trans('langAvailableTypes') }}</label>
                                    
                                            <div class='radio mb-2'>
                                              <label>
                                                <input id='courseopen' type='radio' name='formvisible' value='2' checked>
                                                <span class='ps-1'>{!! $icon_course_open !!}</span> 
                                                {{ trans('langOpenCourse') }}
                                              </label>
                                              <div class='help-block ps-5'>{{ trans('langPublic') }}</div>
                                            </div>
                                         

                                            <div class='radio mb-2'>
                                              <label>
                                                <input id='coursewithregistration' type='radio' name='formvisible' value='1'>
                                                <span class='ps-1'>{!! $icon_course_registration !!}</span>
                                                {{ trans('langRegCourse') }}
                                              </label>
                                              <div class='help-block ps-5'>{{ trans('langPrivOpen') }}</div>
                                            </div>
                                      

                                            <div class='radio mb-2'>
                                              <label>
                                                <input id='courseclose' type='radio' name='formvisible' value='0'>
                                                <span class='ps-1'>{!! $icon_course_closed !!}</span>
                                                {{ trans('langClosedCourse') }}
                                              </label>
                                              <div class='help-block ps-5'>{{ trans('langClosedCourseShort') }}</div>
                                            </div>
                                         

                                            <div class='radio mb-2'>
                                              <label>
                                                  <input id='courseinactive' type='radio' name='formvisible' value='3'>
                                                  <span class='ps-1'>{!! $icon_course_inactive !!}</span>
                                                  {{ trans('langInactiveCourse') }}
                                              </label>
                                              <div class='help-block ps-5'>{{ trans('langCourseInactive') }}</div>
                                            </div>
                                                          
                                        
                                      </div>


                                      <div class='form-group mt-4'>
                                            <label for='coursepassword' class='col-sm-12 control-label-notes'>{{ trans('langOptPassword') }}</label>
                                            <div class='col-sm-12'>
                                                  <input class='form-control' id='coursepassword' type='text' name='password' value='{{ trans('password') }}' autocomplete='off'>                        
                                            </div>
                                            <div class='col-sm-12' text-center padding-thin>
                                                <span id='result'></span>
                                            </div>
                                            
                                      </div>



                                      <div class='form-group mt-5 d-flex justify-content-center align-items-center'>
                                            
                                                  <input class='btn submitAdminBtn' type='submit' name='create_course' value='{{ trans('langCourseCreate') }}'>                          
                                                  <a href='{{ $cancel_link }}' class='btn btn-outline-secondary cancelAdminBtn ms-1'>{{ trans('langCancel') }}</a>
                                           
                                      </div>     
                                        
                                        
                                    

                                <div class='text-center mt-3 fw-bold'><small>{{ trans('langFieldsOptionalNote') }}</small></div>
                      
                            {!! generate_csrf_token_form_field() !!}
                          </form>
                        </div>
                      </div>
                    
                     
                    

              </div>

           

          </div>

        </div>
      
    </div>
</div>

@endsection