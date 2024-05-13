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
        pwStrengthTooShort: "{{ js_escape(trans('langPwStrengthTooShort')) }}",
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

        var type_course = $('#typeCourses').val();
        if (type_course == 1){
          $('.typeCourse').removeClass('d-block');
          $('.typeCourse').addClass('d-none');
          const simple = document.getElementById('simple');
          simple.checked = true;
        }else{
          $('.typeCourse').removeClass('d-none');
          $('.typeCourse').addClass('d-block');
          const units = document.getElementById('units');
          units.checked = true;
        }

        $('#typeCourses').change(function(){
            
            var item = $(this).val();
            if (item == 1){
              $('.typeCourse').removeClass('d-block');
              $('.typeCourse').addClass('d-none');
              const simple = document.getElementById('simple');
              simple.checked = true;
            }else{
              $('.typeCourse').removeClass('d-none');
              $('.typeCourse').addClass('d-block');
              const units = document.getElementById('units');
              units.checked = true;
            }
        });
    });

</script>
@endpush


@section('content')

<div class="col-12 main-section">
<div class='{{ $container }} main-container'>
        <div class="row m-auto">

                  @include('layouts.common.breadcrumbs', ['breadcrumbs' => $breadcrumbs])

                  @include('layouts.partials.legend_view')

                  {!! $action_bar !!}

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
                            
                            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                        </div>
                    </div>
                    @endif

                 
                  <div class='col-lg-6 col-12'>
                    <div class='form-wrapper form-edit border-0 px-0'>
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
                          <label for="typeCourse" class='col-sm-12 control-label-notes'>{{ trans('langType') }}</label>
                          @if(get_config('show_collaboration') and get_config('show_always_collaboration'))
                              <select id='typeCourses' name='typeCourse' class='form-select'>
                                <option value='1' selected>{{ trans('langTypeCollaboration') }}</option>
                              </select>
                          @elseif(get_config('show_collaboration') and !get_config('show_always_collaboration'))
                              <select id='typeCourses' name='typeCourse' class='form-select'>
                                <option value='0' selected>{{ trans('langTypeCourse') }}</option>
                                <option value='1'>{{ trans('langTypeCollaboration') }}</option>
                              </select>
                          @else
                              <select id='typeCourses' name='typeCourse' class='form-select'>
                                <option value='0' selected>{{ trans('langCourse') }}</option>
                              </select>
                          @endif
                        </div>

                            <div class='form-group mt-4'>
                               <label class='col-sm-12 control-label-notes mb-2'>{{ trans('langCourseFormat') }}</label>

                                <div class='radio mb-2 typeCourse1'>
                                  <label>
                                      <input type='radio' name='view_type' value='simple' id='simple'>
                                      {{ trans('langCourseSimpleFormat') }}
                                  </label>
                                </div>

                                <div class='radio mb-2 typeCourse1'>
                                  <label>
                                    <input type='radio' name='view_type' value='units' id='units' checked>
                                    {{ trans('langWithCourseUnits') }}
                                    </label>
                                </div>
                                <div class="radio mb-2 typeCourse">
                                  <label>
                                    <input type="radio" name="view_type" value="activity" id="activity">
                                    {{trans('langCourseActivityFormat') }}
                                  </label>
                                </div>
                                <div class='radio mb-2 typeCourse'>
                                  <label>
                                    <input type='radio' name='view_type' value='wall' id='wall'>
                                    {{ trans('langCourseWallFormat') }}
                                  </label>
                                </div>
                                <div class='radio typeCourse'>
                                    <label>
                                        <input type='radio' name='view_type' value='flippedclassroom' id='flippedclassroom'>
                                        {{ trans('langFlippedClassroom') }}
                                    </label>
                                </div>
                            </div>

                            <div class='form-group mt-4'>
                              <label class='col-sm-12 control-label-notes mb-2'>{{ trans('langOpenCoursesLicense') }}</label>

                              <div class='radio mb-2'>
                                <label>
                                  <input type='radio' name='l_radio' value='0' checked>
                                  {{ $license_0 }}
                                </label>
                              </div>

                              <div class='radio mb-2'>
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


                            <div class='form-group mt-4' id='cc'>
                                <div class='col-sm-12 col-sm-offset-2'>
                                      {!! $selection_license !!}
                                </div>
                            </div>

                            <div class='form-group mt-4'>

                                   <label for='localize' class='col-sm-12 control-label-notes mb-2'>{{ trans('langAvailableTypes') }}</label>

                                    <div class='radio mb-3'>
                                      <label>
                                        <input class='input-StatusCourse' id='courseopen' type='radio' name='formvisible' value='2'>
                                        <label for="courseopen">{!! $icon_course_open !!}</label>
                                        {{ trans('langOpenCourse') }}
                                      </label>
                                      <div class='help-block'>{{ trans('langPublic') }}</div>
                                    </div>

                                    <div class='radio mb-3'>
                                      <label>
                                        <input class='input-StatusCourse' id='coursewithregistration' type='radio' name='formvisible' value='1' checked>
                                        <label for="coursewithregistration">{!! $icon_course_registration !!}</label>
                                        {{ trans('langRegCourse') }}
                                      </label>
                                      <div class='help-block'>{{ trans('langPrivOpen') }}</div>
                                    </div>

                                    <div class='radio mb-3'>
                                      <label>
                                        <input class='input-StatusCourse' id='courseclose' type='radio' name='formvisible' value='0'>
                                        <label for="courseclose">{!! $icon_course_closed !!}</label>
                                        {{ trans('langClosedCourse') }}
                                      </label>
                                      <div class='help-block'>{{ trans('langClosedCourseShort') }}</div>
                                    </div>

                                    <div class='radio'>
                                      <label>
                                          <input class='input-StatusCourse' id='courseinactive' type='radio' name='formvisible' value='3'>
                                          <label for="courseinactive">{!! $icon_course_inactive !!}</label>
                                          {{ trans('langInactiveCourse') }}
                                      </label>
                                      <div class='help-block'>{{ trans('langCourseInactive') }}</div>
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
                              
                              <div class='text-start mt-4 fw-bold'><small>{{ trans('langFieldsOptionalNote') }}</small></div>

                              <div class='form-group mt-5 d-flex justify-content-end align-items-center gap-2'>

                                  <input class='btn submitAdminBtn' type='submit' name='create_course' value='{{ trans('langCourseCreate') }}'>
                                  <a href='{{ $cancel_link }}' class='btn cancelAdminBtn'>{{ trans('langCancel') }}</a>

                              </div>

                        

                    {!! generate_csrf_token_form_field() !!}
                  </form>
                </div>
              </div>
              <div class='col-lg-6 col-12 d-none d-md-none d-lg-block text-end'>
                <img class='form-image-modules' src='{!! get_form_image() !!}' alt='form-image'>
              </div>
              
              
        </div>
    
</div>
</div>
@endsection
