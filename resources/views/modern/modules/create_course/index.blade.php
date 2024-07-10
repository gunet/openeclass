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

        $('.chooseCourseImage').on('click',function(){
            var id_img = this.id;
            alert('{{ js_escape(trans('langImageSelected')) }}!');
            document.getElementById('choose_from_list').value = id_img;
            $('#CoursesImagesModal').modal('hide');
            document.getElementById('selectedImage').value = '{{ trans('langSelect') }}:'+id_img;
        });

        $('#type_collab').on('click',function(){
            if($('#type_collab').is(":checked")){
                document.getElementById("radio_flippedclassroom").style.display="none";
                document.getElementById("radio_activity").style.display="none";
                document.getElementById("radio_wall").style.display="none";
            }else{
                document.getElementById("radio_flippedclassroom").style.display="block";
                document.getElementById("radio_activity").style.display="block";
                document.getElementById("radio_wall").style.display="block";
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
                      <form class='form-horizontal' role='form' method='post' name='createform' action="{{ $_SERVER['SCRIPT_NAME'] }}" enctype="multipart/form-data" onsubmit=\"return validateNodePickerForm();\">

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

                        <div id='image_field' class='row form-group mt-4'>
                            <label for='course_image' class='col-12 control-label-notes'>{{ trans('langCourseImage') }}</label>
                            <div class='col-12'>
                                {!! fileSizeHidenInput() !!}
                                <ul class='nav nav-tabs' id='nav-tab' role='tablist'>
                                    <li class='nav-item' role='presentation'>
                                        <button class='nav-link active' id='tabs-upload-tab' data-bs-toggle='tab' data-bs-target='#tabs-upload' type='button' role='tab' aria-controls='tabs-upload' aria-selected='true'> {{ trans('langUpload') }}</button>
                                    </li>
                                    <li class='nav-item' role='presentation'>
                                        <button class='nav-link' id='tabs-selectImage-tab' data-bs-toggle='tab' data-bs-target='#tabs-selectImage' type='button' role='tab' aria-controls='tabs-selectImage' aria-selected='false'>{{ trans('langAddPicture') }}</button>
                                    </li>
                                </ul>
                                <div class='tab-content mt-3' id='tabs-tabContent'>
                                    <div class='tab-pane fade show active' id='tabs-upload' role='tabpanel' aria-labelledby='tabs-upload-tab'>
                                        <input type='file' name='course_image' id='course_image'>
                                    </div>
                                    <div class='tab-pane fade' id='tabs-selectImage' role='tabpanel' aria-labelledby='tabs-selectImage-tab'>
                                        <button type='button' class='btn submitAdminBtn' data-bs-toggle='modal' data-bs-target='#CoursesImagesModal'>
                                            <i class='fa-solid fa-image settings-icons'></i>&nbsp;{{ trans('langSelect') }}
                                        </button>
                                        <input type='hidden' id='choose_from_list' name='choose_from_list'>
                                        <input type='text'class='form-control border-0 pe-none px-0' id='selectedImage'>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div class='form-group mt-4'>
                            <label for='description' class='col-sm-12 control-label-notes'>{{trans('langDescrInfo')}} <small>{{trans('langOptional')}}</small></label>
                            <div class='col-sm-12'>
                                  {!! $rich_text_editor !!}
                            </div>
                        </div>

                        @if(get_config('show_collaboration') && !get_config('show_always_collaboration'))
                            <div class='form-group mt-4'>
                                <div class='col-sm-12'>
                                    <label class='control-label-notes' for='type_collab'>{!! trans('langWhatTypeOfCourse') !!}</label>
                                    <div class='checkbox'>
                                        <label class='label-container'>
                                            <input type='checkbox' id='type_collab' name='is_type_collaborative'>
                                            <span class='checkmark'></span>
                                            {!! trans('langTypeCollaboration') !!}
                                        </label>
                                    </div>
                                </div>
                            </div>
                        @endif


                            <div class='form-group mt-4'>
                               <label class='col-sm-12 control-label-notes mb-2'>{{ trans('langCourseFormat') }}</label>


                                <div class="radio mb-2">
                                  <label>
                                      <input type='radio' name='view_type' value='simple' id='simple'>
                                      {{ trans('langCourseSimpleFormat') }}
                                  </label>
                                </div>
                                <div class="radio mb-2">
                                  <label>
                                    <input type='radio' name='view_type' value='units' id='units' checked>
                                    {{ trans('langWithCourseUnits') }}
                                    </label>
                                </div>
                                <div class="radio mb-2 @if(get_config('show_collaboration') and get_config('show_always_collaboration')) d-none @endif" id="radio_activity">
                                  <label>
                                    <input type="radio" name="view_type" value="activity" id="activity">
                                    {{trans('langCourseActivityFormat') }}
                                  </label>
                                </div>
                                <div class="radio mb-2 @if(get_config('show_collaboration') and get_config('show_always_collaboration')) d-none @endif" id="radio_wall">
                                  <label>
                                    <input type='radio' name='view_type' value='wall' id='wall'>
                                    {{ trans('langCourseWallFormat') }}
                                  </label>
                                </div>
                                <div class="radio mb-2 @if(get_config('show_collaboration') and get_config('show_always_collaboration')) d-none @endif" id="radio_flippedclassroom">
                                    <label>
                                        <input type='radio' name='view_type' value='flippedclassroom' id='flippedclassroom'>
                                        {{ trans('langFlippedClassroom') }}
                                    </label>
                                </div>

                                <div class="radio 
                                            @if(!get_config('show_collaboration') and !get_config('show_always_collaboration')) 
                                                d-none 
                                            @elseif(is_module_disable(MODULE_ID_SESSION)) 
                                                d-none 
                                            @endif">
                                    <label>
                                        <input type='radio' name='view_type' value='sessions' id='sessions'>
                                        {{ trans('langSessionType') }}
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

                              <div class='text-start mt-4 fw-bold'>
                                  <small>
                                      {{ trans('langFieldsOptionalNote') }}
                                  </small>
                              </div>

                              <div class='form-group mt-5 d-flex justify-content-end align-items-center gap-2'>
                                  <input class='btn submitAdminBtn' type='submit' name='create_course' value='{{ trans('langCourseCreate') }}'>
                                  <a href='{{ $cancel_link }}' class='btn cancelAdminBtn'>{{ trans('langCancel') }}</a>
                              </div>

                            <div class='modal fade' id='CoursesImagesModal' tabindex='-1' aria-labelledby='CoursesImagesModalLabel' aria-hidden='true'>
                                <div class='modal-dialog modal-lg'>
                                    <div class='modal-content'>
                                        <div class='modal-header'>
                                            <div class='modal-title' id='CoursesImagesModalLabel'>{{ trans('langCourseImage') }}</div>
                                            <button type='button' class='close' data-bs-dismiss='modal' aria-label='Close'></button>
                                        </div>
                                        <div class='modal-body'>
                                            <div class='row row-cols-1 row-cols-md-2 g-4'>
                                                {!! $image_content !!}
                                            </div>
                                        </div>
                                    </div>
                                </div>
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
