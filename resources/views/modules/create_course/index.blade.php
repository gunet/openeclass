@extends('layouts.default')

@push('head_styles')
    <link href="{{ $urlAppend }}js/jstree3/themes/proton/style.min.css" type='text/css' rel='stylesheet'>
@endpush

@push('head_scripts')
    <script type='text/javascript' src='{{ $urlAppend }}js/jstree3/jstree.min.js'></script>
    <script type='text/javascript' src='{{ $urlAppend }}js/pwstrength.js'></script>
    <script type='text/javascript' src='{{ $urlAppend }}js/tools.js'></script>

    <script type='text/javascript'>
        var lang = {
            pwStrengthTooShort: "{{ js_escape(trans('langPwStrengthTooShort')) }}",
            pwStrengthWeak: "{{ js_escape(trans('langPwStrengthWeak')) }}",
            pwStrengthGood: "{{ js_escape(trans('langPwStrengthGood')) }}",
            pwStrengthStrong: "{{ js_escape(trans('langPwStrengthStrong')) }}"
        }

        function deactivate_input_password () {
            $('#coursepassword, #faculty_users_registration').attr('disabled', 'disabled');
            $('#coursepassword').closest('div.form-group').addClass('invisible');
        }

        function activate_input_password () {
            $('#coursepassword, #faculty_users_registration').removeAttr('disabled', 'disabled');
            $('#coursepassword').closest('div.form-group').removeClass('invisible');
        }

        function displayCoursePassword() {
            if ($('#courseclose, #courseiactive').is(":checked")) {
                deactivate_input_password ();
            } else {
                activate_input_password ();
            }
        }

        $(document).ready(function() {

            $('#coursepassword').keyup(function() {
                $('#result').html(checkStrength($('#coursepassword').val()))
            });

            displayCoursePassword();

            $('#courseopen, #coursewithregistration').click(function(event) {
                activate_input_password();
            });

            $('#courseclose, #courseinactive').click(function(event) {
                deactivate_input_password();
            });

            $('input[name=l_radio]').change(function () {
                if ($('#cc_license').is(":checked")) {
                    $('#cc').show();
                } else {
                    $('#cc').hide();
                }
            }).change();

            $('.chooseCourseImage').on('click',function(){
                var id_img = this.id;
                alert('{{ js_escape(trans('langImageSelected')) }}!');
                document.getElementById('choose_from_list').value = id_img;
                $('#CoursesImagesModal').modal('hide');
                document.getElementById('selectedImage').value = '{{ trans('langSelect') }}:'+id_img;
            });

            if ($("#radio_collaborative_helper").length > 0) {
                if(document.getElementById("radio_collaborative_helper").value == 0){
                    document.getElementById("radio_collaborative").style.display="none";
                }
            }
            $('#type_collab').on('click',function(){
                if($('#type_collab').is(":checked")){
                    document.getElementById("radio_flippedclassroom").style.display="none";
                    document.getElementById("radio_activity").style.display="none";
                    document.getElementById("radio_wall").style.display="none";
                    document.getElementById("radio_collaborative").style.display="block";
                }else{
                    document.getElementById("radio_flippedclassroom").style.display="block";
                    document.getElementById("radio_activity").style.display="block";
                    document.getElementById("radio_wall").style.display="block";
                    document.getElementById("radio_collaborative").style.display="none";
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

              @include('layouts.partials.show_alert')

             <div class='col-12'>
                <div class='alert alert-info'>
                    <i class='fa-solid fa-circle-info fa-lg'></i>
                    <span>{{ trans('langFieldsOptionalNote') }}</span>
                </div>
             </div>

              <div class='col-lg-8 col-12'>
                <div class='form-wrapper form-edit border-0 px-0'>
                  <form class='form-horizontal' role='form' method='post' name='createform' action="{{ $_SERVER['SCRIPT_NAME'] }}" enctype="multipart/form-data" onsubmit=\"return validateNodePickerForm();\">
                    <fieldset>
                    <legend class='mb-0' aria-label="{{ trans('langForm') }}"></legend>
                    <div class='form-group'>
                        <label for='title' class='col-12 control-label-notes'>{{ trans('langTitle') }} <span class='asterisk Accent-200-cl'>(*)</span></label>
                        <div class='col-12'>
                          <input name='title' id='title' type='text' class='form-control' value="{{ $title }}" placeholder="{{ trans('langCourseTitle') }}">
                            <span class='help-block Accent-200-cl'>{{ Session::getError('title') }}</span>
                        </div>
                    </div>
                    <div class='form-group mt-4'>
                        <label for='public_code' class='col-12 control-label-notes'>{{ trans('langCode') }}</label>
                        <div class='col-sm-12'>
                          <input name='public_code' id='public_code' type='text' class='form-control' value = "{{ $public_code }}"  placeholder="{{ trans('langOptional') }}">
                        </div>
                    </div>
                    <div class='form-group mt-4'>
                        <label for='dialog-set-value' class='col-sm-12 control-label-notes'>{{ trans('langFaculty') }} <span class='asterisk Accent-200-cl'>(*)</span></label>
                        <div class='col-sm-12'>
                          {!! $buildusernode !!}
                        </div>
                    </div>
                    <div class='form-group mt-4'>
                        <label for='prof_names' class='col-sm-12 control-label-notes'>{{ trans('langTeachers') }}</label>
                        <div class='col-sm-12'>
                              <input class='form-control' type='text' name='prof_names' id='prof_names' value= "{{ $prof_names }}">
                        </div>
                    </div>
                    <div class='form-group mt-4'>
                        <label for='lang_selected' class='col-sm-12 control-label-notes'>{{ trans('langLanguage') }}</label>
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
                                    <label for='selectedImage'>{{ trans('langImageSelected')}}:</label>
                                    <input type='text'class='form-control border-0 pe-none px-0' id='selectedImage'>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class='form-group mt-4'>
                        <label for='description' class='col-sm-12 control-label-notes'>
                            {{ trans('langDescrInfo') }}
                            <small>{{trans('langOptional')}}</small>
                        </label>
                        <div class='col-sm-12'>
                              {!! $rich_text_editor !!}
                        </div>
                    </div>

                    @if(get_config('show_collaboration') && !get_config('show_always_collaboration'))
                        <div class='form-group mt-4'>
                            <div class='col-sm-12'>
                                <label class='control-label-notes' for='type_collab'>{!! trans('langWhatTypeOfCourse') !!}</label>
                                <div class='checkbox'>
                                    <label class='label-container' aria-label="{{ trans('langSelect') }}">
                                        <input type='checkbox' id='type_collab' name='is_type_collaborative'>
                                        <span class='checkmark'></span>
                                        {!! trans('langTypeCollaboration') !!}
                                    </label>
                                </div>
                            </div>
                        </div>
                    @endif

                    <div class='form-group mt-4'>
                       <div class='col-sm-12 control-label-notes mb-2'>{{ trans('langCourseFormat') }}</div>
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
                            @endif" id="radio_collaborative">
                            <label>
                                <input type='radio' name='view_type' value='sessions' id='sessions'>
                                {{ trans('langSessionType') }}
                            </label>
                        </div>

                        @if(get_config('show_collaboration') and !get_config('show_always_collaboration'))
                            <input type="hidden" id="radio_collaborative_helper" value="0">
                        @endif
                    </div>

                    <div class='form-group mt-4'>
                      <div class='col-sm-12 control-label-notes mb-2'>{{ trans('langOpenCoursesLicense') }}</div>

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
                            <label class='mb-0' for='course_license_id' aria-label="{{ trans('langOpenCoursesLicense') }}"></label>
                              {!! $selection_license !!}
                        </div>
                    </div>

                    <div class='form-group mt-4'>

                           <div class='col-sm-12 control-label-notes mb-2'>{{ trans('langAvailableTypes') }}</div>

                            <div class='radio mb-3'>
                              <label>
                                <input class='input-StatusCourse' id='courseopen' type='radio' name='formvisible' value='2'
                                    @if ($default_access === COURSE_OPEN) checked @endif>
                                <label for="courseopen" aria-label="{{ trans('langOpenCourse') }}">{!! $icon_course_open !!}</label>
                                {{ trans('langOpenCourse') }}
                              </label>
                              <div class='help-block'>{{ trans('langPublic') }}</div>
                            </div>

                            <div class='radio mb-3'>
                              <label>
                                <input class='input-StatusCourse' id='coursewithregistration' type='radio' name='formvisible' value='1'
                                    @if ($default_access === COURSE_REGISTRATION) checked @endif>
                                <label for="coursewithregistration" aria-label="{{ trans('langRegCourse') }}">{!! $icon_course_registration !!}</label>
                                {{ trans('langRegCourse') }}
                              </label>
                              <div class='help-block'>{{ trans('langPrivOpen') }}</div>
                            </div>

                            <div class='radio mb-3'>
                              <label>
                                <input class='input-StatusCourse' id='courseclose' type='radio' name='formvisible' value='0'
                                  @if ($default_access === COURSE_CLOSED) checked @endif>
                                <label for="courseclose" aria-label="{{ trans('langClosedCourse') }}">{!! $icon_course_closed !!}</label>
                                {{ trans('langClosedCourse') }}
                              </label>
                              <div class='help-block'>{{ trans('langClosedCourseShort') }}</div>
                            </div>

                            <div class='radio'>
                              <label>
                                  <input class='input-StatusCourse' id='courseinactive' type='radio' name='formvisible' value='3'
                                    @if ($default_access === COURSE_INACTIVE) checked @endif>
                                  <label for="courseinactive" aria-label="{{ trans('langInactiveCourse') }}">{!! $icon_course_inactive !!}</label>
                                  {{ trans('langInactiveCourse') }}
                              </label>
                              <div class='help-block'>{{ trans('langCourseInactive') }}</div>
                            </div>
                      </div>

                     <div class='form-group mt-3'>
                         <div class='checkbox mb-2 mt-4'>
                             <label class='label-container' aria-label="{{ trans('langSelect') }}">
                                 <input type='checkbox' id='faculty_users_registration' name='faculty_users_registration'>
                                 <span class='checkmark'></span>{{ trans('langFacultyUsersRegistrationLegend') }}
                             </label>
                         </div>
                        <label for='coursepassword' class='col-sm-12 control-label-notes'>{{ trans('langOptPassword') }}</label>
                        <div class='col-sm-12'>
                              <input class='form-control' id='coursepassword' type='text' name='password' value='{{ trans('password') }}' autocomplete='off'>
                        </div>
                        <div class='col-sm-12' text-center padding-thin>
                            <span id='result'></span>
                        </div>
                     </div>

                     <div class='form-group mt-5 d-flex justify-content-end align-items-center gap-2 flex-wrap'>
                          <input class='btn submitAdminBtn text-nowrap' type='submit' name='create_course' value='{{ trans('langCourseCreate') }}'>
                          <a href='{{ $cancel_link }}' class='btn cancelAdminBtn text-nowrap'>{{ trans('langCancel') }}</a>
                      </div>

                    <div class='modal fade' id='CoursesImagesModal' tabindex='-1' aria-labelledby='CoursesImagesModalLabel' aria-hidden='true'>
                        <div class='modal-dialog modal-lg'>
                            <div class='modal-content'>
                                <div class='modal-header'>
                                    <div class='modal-title' id='CoursesImagesModalLabel'>{{ trans('langCourseImage') }}</div>
                                    <button type='button' class='close' data-bs-dismiss='modal' aria-label="{{ trans('langClose') }}"></button>
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
                </fieldset>
              </form>
            </div>
          </div>
          <div class='col-lg-4 col-12 d-none d-md-none d-lg-block text-end'>
            <img class='form-image-modules' src='{!! get_form_image() !!}' alt="{{ trans('langImgFormsDes') }}">
          </div>
        </div>
    </div>
</div>
@endsection
