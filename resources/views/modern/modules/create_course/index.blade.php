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

          <div class="col-xl-12 col-lg-12 col-md-12 col-sm-12 col-12 justify-content-center col_maincontent_active_Homepage">

              <div class="row p-lg-5 p-md-5 ps-1 pe-2 pt-5 pb-5">

                  @include('layouts.common.breadcrumbs', ['breadcrumbs' => $breadcrumbs])

                  @include('layouts.partials.legend_view',['is_editor' => $is_editor, 'course_code' => $course_code])
                 
                    @if(Session::has('message'))
                    <div class='col-xxl-12 col-xl-12 col-lg-12 col-md-12 col-sm-12 col-12 mt-5'>
                        <p class="alert {{ Session::get('alert-class', 'alert-info') }} alert-dismissible fade show" role="alert">
                            @if(is_array(Session::get('message')))
                                @php $messageArray = array(); $messageArray = Session::get('message'); @endphp
                                @foreach($messageArray as $message)
                                    {!! $message !!}
                                @endforeach
                            @else
                                {!! Session::get('message') !!}
                            @endif
                            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                        </p>
                    </div>
                    @endif
                      
                    <div class='col-12'>
                      <div class='form-wrapper shadow-sm p-3 rounded'>
                        <form class='form-horizontal' role='form' method='post' name='createform' action="{{ $_SERVER['SCRIPT_NAME'] }}" onsubmit=\"return validateNodePickerForm();\">
                       
                                  <div class="row p-2">
                                    <div class="col-lg-6 col-12">
                                      <div class='form-group'>
                                          <h3 for='title' class='col-sm-6 control-label-notes'>{{ trans('langTitle') }}:</h3>
                                          <div class='col-sm-12'>
                                            <input name='title' id='title' type='text' class='form-control' value=" {{ trans('title') }} " placeholder="{{ trans('langTitle') }}">
                                          </div>
                                      </div>
                                    </div>

                                    <div class="col-lg-6 col-12 mt-lg-0 mt-3">
                                      <div class='form-group'>
                                          <h3 for='title' class='col-sm-6 control-label-notes'>{{ trans('langCode') }}:</h3>
                                          <div class='col-sm-12'>
                                            <input name='public_code' id='public_code' type='text' class='form-control' value = "{{ trans('public_code') }}"  placeholder="{{ trans('langOptional') }}">
                                          </div>
                                      </div>
                                    </div>
                                  </div>


                                  <div class="row p-2">
                                    <div class="col-lg-4 col-12">
                                      <div class='form-group'>
                                          <h3  class='col-sm-4 control-label-notes'>{{ trans('langFaculty') }}:</h3>
                                          <div class='col-sm-12'>
                                            {!! $buildusernode !!}
                                          </div>
                                      </div>
                                    </div>

                                    <div class="col-lg-4 col-12">
                                      <div class='form-group'>
                                          <h3 for='prof_names' class='col-sm-4 control-label-notes'>{{ trans('langTeachers') }}:</h3>
                                          <div class='col-sm-12'>
                                                <input class='form-control' type='text' name='prof_names' id='prof_names' value= "{{ trans('prof_names') }}">
                                          </div>
                                      </div>
                                    </div>

                                    <div class="col-lg-4 col-12 mt-lg-0 mt-3">
                                      <div class='form-group'>
                                          <h3 for='localize' class='col-sm-4 control-label-notes'>{{ trans('langLanguage') }}:</h3>
                                          <div class='col-sm-12'>
                                                {!! $lang_select_options !!}
                                          </div>
                                      </div>
                                    </div>
                                  </div>

                                  <div class="row p-2">
                                    <div class="col-lg-12 col-12">
                                      <div class='form-group'>
                                          <label for='description' class='col-sm-12 control-label-notes'>Περιγραφή μαθήματος (προαιρετικό)</label>
                                          <div class='col-sm-12'>
                                                {!! $rich_text_editor !!}
                                          </div>
                                      </div>
                                    </div>
                                  </div>
                              

                                  <div class='form-group mt-3'>
                                    <div class="col-xl-12 col-md-12 col-sm-6 col-xs-6">
                                      <label class='col-sm-12 control-label-notes'>{{ trans('langCourseFormat') }}:</label>
                                      <div class="row p-2">

                                        <div class="col-xl-4 col-md-4 col-sm-6 col-xs-6">
                                          
                                          <div class='radio'>
                                            <div class='d-inline-flex align-items-center'>
                                                <input type='radio' name='view_type' value='simple' id='simple'>
                                                <span class='ps-2'>{{ trans('langCourseSimpleFormat') }}</span>
                                            </div>
                                          </div>
                                        </div>

                                        <div class="col-xl-4 col-md-4 col-sm-6 col-xs-6">
                                          <div class='radio'>
                                            <div class='d-inline-flex align-items-center'>
                                              <input type='radio' name='view_type' value='units' id='units' checked>
                                              <span class='ps-2'>{{ trans('langWithCourseUnits') }}</span>
                                            </div>
                                          </div>
                                        </div>

                                        <div class="col-xl-4 col-md-4 col-sm-6 col-xs-6">
                                          <div class='radio'>
                                            <div class='d-inline-flex align-items-center'>
                                              <input type='radio' name='view_type' value='wall' id='wall'>
                                              <span class='ps-2'>{{ trans('langCourseWallFormat') }}</span>
                                            </div>
                                          </div>   
                                        </div>
                                      </div>
                                    </div>
                                  </div>
                                      
                                  
                                  <div class='form-group mt-3'>
                                    <div class="col-xl-12 col-md-12 col-12">
                                      <label class='col-sm-12 control-label-notes'>{{ trans('langOpenCoursesLicense') }}:</label>
                                      <div class="row p-2">
                                        <div class="col-xl-4 col-md-4 col-12">
                                            <div class='radio'>
                                              <div class='d-inline-flex align-items-center'>
                                                <input type='radio' name='l_radio' value='0' checked>
                                                <span class='ps-2'>{{ $license_0 }}</span>
                                              </div>
                                            </div>
                                        </div>
                                        <div class="col-xl-4 col-md-4 col-12">
                                            <div class='radio'>
                                              <div class='d-inline-flex align-items-center'>
                                                <input type='radio' name='l_radio' value='10'>
                                                <span class='ps-2'>{{ $license_10 }}</span>
                                              </div>
                                            </div>
                                        </div>
                                        <div class="col-xl-4 col-md-4 col-12">
                                            <div class='radio'>
                                              <div class='d-inline-flex align-items-center'>
                                                <input id='cc_license' type='radio' name='l_radio' value='cc'>
                                                <span class='ps-2'>{{ trans("langCMeta['course_license']") }}</span>
                                              </div>
                                            </div>       
                                        </div>
                                      </div>
                                    </div>
                                  </div>


                                  <div class='form-group mt-3' id='cc'>
                                      <div class='col-sm-12 col-sm-offset-2'>
                                            {!! $selection_license !!}
                                      </div>              
                                  </div>

                                  <div class='form-group mt-3'>
                                    <div class="col-xl-12 col-md-12 col-sm-12 col-12">
                                      <label for='localize' class='col-sm-12 control-label-notes'>{{ trans('langAvailableTypes') }}:</label>
                                      <div class='row p-2'>

                                        <div class="col-xl-3 col-md-3 col-12">
                                          <div class='d-inline-flex align-items-center'>
                                            <input id='courseopen' type='radio' name='formvisible' value='2' checked>
                                            <span class='ps-2'>{!! $icon_course_open !!}</span> 
                                            <span class='ps-2'>{{ trans('langOpenCourse') }}</span>
                                          </div><br>
                                          <small class='text-warning'>{{ trans('langPublic') }}</small>
                                        </div>

                                        <div class="col-xl-3 col-md-3 col-12 mt-md-0 mt-3">
                                          <div class='d-inline-flex align-items-center'>
                                            <input id='coursewithregistration' type='radio' name='formvisible' value='1'>
                                            <span class='ps-2'>{!! $icon_course_registration !!}</span>
                                            <span class='ps-3'>{{ trans('langRegCourse') }}</span>
                                          </div><br>
                                          <small class='text-warning'>{{ trans('langPrivOpen') }}</small>
                                        </div>

                                        <div class="col-xl-3 col-md-3 col-12 mt-md-0 mt-3">
                                          <div class='d-inline-flex align-items-center'>
                                            <input id='courseclose' type='radio' name='formvisible' value='0'>
                                            <span class='ps-2'>{!! $icon_course_closed !!}</span>
                                            <span class='ps-2'>{{ trans('langClosedCourse') }}</span>
                                          </div><br>
                                          <small class='text-warning'>{{ trans('langClosedCourseShort') }}</small>
                                        </div>

                                        <div class="col-xl-3 col-md-3 col-12 mt-md-0 mt-3">
                                            <div class='d-inline-flex align-items-center'>
                                                <input id='courseinactive' type='radio' name='formvisible' value='3'>
                                                <span class='ps-2'>{!! $icon_course_inactive !!}</span>
                                                <span class='ps-2'>{{ trans('langInactiveCourse') }}</span>
                                            </div><br>
                                            <small class='text-warning'>{{ trans('langCourseInactive') }}</small>
                                        </div>                   
                                      </div>
                                    </div>


                                    <div class='form-group mt-3'>
                                          <label for='coursepassword' class='col-sm-12 control-label-notes'>{{ trans('langOptPassword') }}:</label>
                                          <div class='col-sm-12'>
                                                <input class='form-control' id='coursepassword' type='text' name='password' value='{{ trans('password') }}' autocomplete='off'>                        
                                          </div>
                                          <div class='col-sm-12' text-center padding-thin>
                                              <span id='result'></span>
                                          </div>
                                          
                                    </div>



                                    <div class='form-group mt-3'>
                                          <div class='col-sm-10 col-sm-offset-2'>
                                                <input class='btn btn-primary' type='submit' name='create_course' value='{{ trans('langCourseCreate') }}'>                          
                                                <a href='{{ $cancel_link }}' class='btn btn-secondary'>{{ trans('langCancel') }}</a>
                                          </div>
                                    </div>     
                                      
                                      
                                  </div>

                              <div class='text-end'><small>{{ trans('langFieldsOptionalNote') }}</small></div>
                     
                          {!! generate_csrf_token_form_field() !!}
                        </form></div></div>
                     
                    














               

              </div>

           

          </div>

        </div>
      
    </div>
</div>

@endsection