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

{!! $action_bar !!}
    <div class='form-wrapper'>
        <form class='form-horizontal' role='form' method='post' name='createform' action="{{ $_SERVER['SCRIPT_NAME'] }}" onsubmit=\"return validateNodePickerForm();\">
        <fieldset>
            <div class='form-group'>
                <label for='title' class='col-sm-2 control-label'>{{ trans('langTitle') }}:</label>
                <div class='col-sm-10'>
                  <input name='title' id='title' type='text' class='form-control' value=' {{ trans('title') }} ' placeholder='{{ trans('langTitle') }}'>
                </div>
            </div>
            <div class='form-group'>
                <label for='title' class='col-sm-2 control-label'>{{ trans('langCode') }}:</label>
                <div class='col-sm-10'>
                  <input name='public_code' id='public_code' type='text' class='form-control' value = '{{ trans('public_code') }}'  placeholder='{{ trans('langOptional') }}'>
                </div>
            </div>
            <div class='form-group'>
                <label  class='col-sm-2 control-label'>{{ trans('langFaculty') }}:</label>
                <div class='col-sm-10'>
                  {!! $buildusernode !!}
                </div>
            </div>
            <div class='form-group'>
                <label for='prof_names' class='col-sm-2 control-label'>{{ trans('langTeachers') }}:</label>
                <div class='col-sm-10'>
                      <input class='form-control' type='text' name='prof_names' id='prof_names' value= ' {{ trans('prof_names') }} '>
                </div>
            </div>
            <div class='form-group'>
                <label for='localize' class='col-sm-2 control-label'>{{ trans('langLanguage') }}:</label>
                <div class='col-sm-10'>
                      {!! $lang_select_options !!}
                </div>
            </div>
            <div class='form-group'>
                <label for='description' class='col-sm-2 control-label'>{{ trans('langDescrInfo') }} <small>{{ trans('langOptional') }}</small>:</label>
                <div class='col-sm-10'>
                      {!! $rich_text_editor !!}
                </div>
            </div>
            <div class='form-group'>
                <label class='col-sm-2 control-label'>{{ trans('langCourseFormat') }}:</label>
                <div class='col-sm-10'>
                    <div class='radio'>
                      <label>
                        <input type='radio' name='view_type' value='simple' id='simple'>
                        {{ trans('langCourseSimpleFormat') }}
                      </label>
                    </div>
                    <div class='radio'>
                      <label>
                        <input type='radio' name='view_type' value='units' id='units' checked>
                        {{ trans('langWithCourseUnits') }}
                      </label>
                    </div>
                    <div class='radio'>
                      <label>
                        <input type='radio' name='view_type' value='wall' id='wall'>
                        {{ trans('langCourseWallFormat') }}
                      </label>
                    </div>                         
                </div>
            </div>            
            <div class='form-group'>
                <label class='col-sm-2 control-label'>{{ trans('langOpenCoursesLicense') }}:</label>
                <div class='col-sm-10'>
                    <div class='radio'>
                      <label>
                        <input type='radio' name='l_radio' value='0' checked>
                        {{ $license_0 }}
                      </label>
                    </div>
                    <div class='radio'>
                      <label>
                        <input type='radio' name='l_radio' value='10'>
                        {{ $license_10 }}
                      </label>
                    </div>
                    <div class='radio'>
                      <label>
                        <input id='cc_license' type='radio' name='l_radio' value='cc'>
                        {{ trans("langCMeta['course_license']") }}
                      </label>
                    </div>                         
                </div>
            </div>
            <div class='form-group' id='cc'>
                <div class='col-sm-10 col-sm-offset-2'>
                      {!! $selection_license !!}
                </div>              
            </div>
            <div class='form-group'>
                <label for='localize' class='col-sm-2 control-label'>{{ trans('langAvailableTypes') }}:</label>
                <div class='col-sm-10'>
                    <div class='radio'>
                      <label>
                        <input id='courseopen' type='radio' name='formvisible' value='2' checked>
                        {!! $icon_course_open !!} {{ trans('langOpenCourse') }}
                        <span class='help-block'><small>{{ trans('langPublic') }}</small></span>
                      </label>
                    </div>
                    <div class='radio'>
                      <label>
                        <input id='coursewithregistration' type='radio' name='formvisible' value='1'>
                        {!! $icon_course_registration !!} {{ trans('langRegCourse') }}
                        <span class='help-block'><small>{{ trans('langPrivOpen') }}</small></span>
                      </label>
                    </div>
                    <div class='radio'>
                      <label>
                        <input id='courseclose' type='radio' name='formvisible' value='0'>
                        {!! $icon_course_closed !!} {{ trans('langClosedCourse') }}
                        <span class='help-block'><small>{{ trans('langClosedCourseShort') }}</small></span>
                      </label>
                    </div>
                    <div class='radio'>
                      <label>
                        <input id='courseinactive' type='radio' name='formvisible' value='3'>
                        {!! $icon_course_inactive !!} {{ trans('langInactiveCourse') }}
                        <span class='help-block'><small>{{ trans('langCourseInactive') }}</small></span>
                      </label>
                    </div>                   
                </div>
                <div class='form-group'>
                    <label for='coursepassword' class='col-sm-2 control-label'>{{ trans('langOptPassword') }}:</label>
                    <div class='col-sm-10'>
                          <input class='form-control' id='coursepassword' type='text' name='password' value='{{ trans('password') }}' autocomplete='off'>                        
                    </div>
                    <div class='col-sm-2' text-center padding-thin>
                        <span id='result'></span>
                    </div>
                    
                </div>
                <div class='form-group'>
                    <div class='col-sm-10 col-sm-offset-2'>
                          <input class='btn btn-primary' type='submit' name='create_course' value='{{ trans('langCourseCreate') }}'>                          
                          <a href='{{ $cancel_link }}' class='btn btn-default'>{{ trans('langCancel') }}</a>
                    </div>
                </div>                 
            </div>
            <div class='text-right'><small>{{ trans('langFieldsOptionalNote') }}</small></div>
        </fieldset>
        {!! generate_csrf_token_form_field() !!}
    </form>
</div>

@endsection