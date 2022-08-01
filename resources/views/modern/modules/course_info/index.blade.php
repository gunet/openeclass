
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

<div class="pb-lg-3 pt-lg-3 pb-0 pt-0">

        <div class="container-fluid main-container">

            <div class="row rowMedium">

              <div id="background-cheat-leftnav" class="col-xl-2 col-lg-3 col-md-0 col-sm-0 col-0 justify-content-center col_sidebar_active"> 
                  <div class="d-none d-sm-block d-sm-none d-md-block d-md-none d-lg-block">
                      @include('layouts.partials.sidebar',['is_editor' => $is_editor])
                  </div>
              </div>

                <div class="col-xl-10 col-lg-9 col-md-12 col-sm-12 col-12 justify-content-center col_maincontent_active">
                    
                    <div class="row p-lg-5 p-md-5 ps-1 pe-2 pt-5 pb-5">


                          <nav class="navbar navbar-expand-lg navrbar_menu_btn">
                            <button type="button" id="menu-btn" class="d-none d-sm-block d-sm-none d-md-block d-md-none d-lg-block btn btn-primary menu_btn_button">
                                <i class="fas fa-align-left"></i>
                                <span></span>
                            </button>
                            
                          
                            <a class="btn btn-primary d-lg-none mr-auto" type="button" data-bs-toggle="offcanvas" href="#collapseTools" role="button" aria-controls="collapseTools" style="margin-top:-10px;">
                                <i class="fas fa-tools"></i>
                            </a>
                          </nav>

                          @include('layouts.common.breadcrumbs', ['breadcrumbs' => $breadcrumbs])

                          <div class="offcanvas offcanvas-start d-lg-none mr-auto" tabindex="-1" id="collapseTools" aria-labelledby="offcanvasExampleLabel">
                              <div class="offcanvas-header">
                                  <button type="button" class="btn-close text-reset" data-bs-dismiss="offcanvas" aria-label="Close"></button>
                              </div>
                              <div class="offcanvas-body">
                                  @include('layouts.partials.sidebar',['is_editor' => $is_editor])
                              </div>
                          </div>


                          @include('layouts.partials.legend_view',['is_editor' => $is_editor, 'course_code' => $course_code])


                          

                          

                          @if(Session::has('message'))
                          <div class='col-xxl-12 col-xl-12 col-lg-12 col-md-12 col-sm-12 col-12 mt-5'>
                              <p class="alert {{ Session::get('alert-class', 'alert-info') }} alert-dismissible fade show" role="alert">
                                  {{ Session::get('message') }}
                                  <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                              </p>
                          </div>
                          @endif

                          <div id='operations_container'>
                                {!! $action_bar !!}
                          </div>

                          <div class='col-12'>
                            <div class='form-wrapper shadow-sm p-3 rounded'>
                              
                              <form class='form-horizontal' role='form' method='post' action="{{ $form_url }}" onsubmit='return validateNodePickerForm();'>
                                <fieldset>
                                    <div class='form-group'>
                                        <label for='fcode' class='col-sm-4 control-label-notes'>{{ trans('langCode') }}</label>
                                        <div class='col-sm-12'>
                                            <input type='text' class='form-control' name='fcode' id='fcode' value='{{ $public_code }}'>
                                        </div>
                                    </div>

                                    <div class="row p-2"></div>


                                    <div class='form-group'>
                                        <label for='title' class='col-sm-4 control-label-notes'>{{ trans('langCourseTitle') }}:</label>
                                        <div class='col-sm-12'>
                                            <input type='text' class='form-control' name='title' id='title' value='{{ q($title) }}'>
                                        </div>
                                    </div>

                                    <div class="row p-2"></div>


                                    <div class='form-group'>
                                        <label for='teacher_name' class='col-sm-4 control-label-notes'>{{ trans('langTeachers') }}:</label>
                                        <div class='col-sm-12'>
                                            <input type='text' class='form-control' name='teacher_name' id='teacher_name' value='{{ $teacher_name }} '>
                                        </div>
                                    </div>

                                    <div class="row p-2"></div>


                                    <div class='form-group'>
                                        <label for='Faculty' class='col-sm-4 control-label-notes'>{{ trans('langFaculty') }}:</label>
                                        <div class='col-sm-12'>
                                            {!! $buildusernode !!}
                                        </div>
                                    </div>

                                    


                                    <div class='form-group'>
                                        <label for='course_keywords' class='col-sm-4 control-label-notes'>{{ trans('langCourseKeywords') }}</label>
                                        <div class='col-sm-12'>
                                            <input type='text' class='form-control' name='course_keywords' id='course_keywords' value='{{ $course_keywords }}'>
                                        </div>
                                    </div>

                                    <div class="row p-2"></div>


                                    <div class='form-group'>
                                        <label class='col-sm-4 control-label-notes'> {{ trans('langCourseFormat') }}:</label>
                                        <div class='col-sm-12'>
                                            <div class='radio'>
                                              <label>
                                                <input type='radio' name='view_type' value='simple' id='simple' {{ $course_type_simple }}>
                                                {{ trans('langCourseSimpleFormat') }}
                                              </label>
                                            </div>
                                            <div class='radio'>
                                              <label>
                                                <input type='radio' name='view_type' value='units' id='units' {{ $course_type_units }}>
                                                {{ trans('langWithCourseUnits') }}
                                              </label>
                                            </div>
                                            <div class='radio'>
                                              <label>
                                                <input type='radio' name='view_type' value='wall' id='wall' {{ $course_type_wall }}>
                                                {{ trans('langCourseWallFormat') }}
                                              </label>
                                            </div>
                                        </div>
                                    </div>

                                    @if (isset($isOpenCourseCertified)) {
                                        <input type='hidden' name='course_license' value='{{ getIndirectReference($course_license) }}'>
                                    @endif

                                    <div class="row p-2"></div>

                                    <div class='form-group'>
                                        <label class='col-sm-4 control-label-notes'>{{ trans('langOpenCoursesLicense') }}:</label>
                                        <div class='col-sm-12'>
                                            <div class='radio'>
                                              <label>
                                                <input type='radio' name='l_radio' value='0' {{ $license_checked0 }} $disabledVisibility>
                                                {{ trans('langLicenseUnset') }}
                                              </label>
                                            </div>
                                            <div class='radio'>
                                              <label>
                                                <input type='radio' name='l_radio' value='10' {{ $license_checked10 }} $disabledVisibility>
                                                {{ trans('langCopyrightedNotFree') }}
                                              </label>
                                            </div>
                                            <div class='radio'>
                                              <label>
                                                <input id='cc_license' type='radio' name='l_radio' value='cc' {{ $cc_checked}} $disabledVisibility>
                                                {{ trans("langCMeta['course_license']") }}
                                              </label>
                                            </div>
                                        </div>
                                    </div>

                                    


                                    <div class='form-group'>
                                        <div class='col-sm-12 col-sm-offset-2' id='cc'>
                                            {!! $license_selection !!}
                                        </div>
                                    </div>

                                    <div class="row p-2"></div>


                                    <div class='form-group'>
                                        <label class='col-sm-4 control-label-notes'>{{ trans('langConfidentiality') }}:</label>
                                        <div class='col-sm-12'>
                                            <div class='row mt-2'>
                                              <div class='col-sm-1'>
                                                <input class='mt-1' id='courseopen' type='radio' name='formvisible' value='2' {{ $course_open }}>
                                              </div>
                                              <div class='col-sm-1'>
                                                {!! course_access_icon(COURSE_OPEN) !!}
                                              </div>
                                              <div class='col-sm-10'>
                                                {{ trans('langOpenCourse') }}
                                                <span class='d-inline'><small>{{ trans('langPublic') }}</small></span>
                                              </div>
                                            </div>
                                            <div class='row mt-2'>
                                              <div class='col-sm-1'>
                                                <input class='mt-1' id='coursewithregistration' type='radio' name='formvisible' value='1' {{ $course_registration }}>
                                              </div>
                                              <div class='col-sm-1'>
                                                {!! course_access_icon(COURSE_REGISTRATION) !!} 
                                              </div>
                                              <div class='col-sm-10'>
                                                {{ trans('langTypeRegistration') }}
                                                <span class='d-inline'><small>{{ trans('langPrivOpen') }}</small></span>
                                              </div>
                                            </div>
                                            <div class='row mt-2'>
                                              <div class='col-sm-1'>
                                                <input class='mt-1' id='courseclose' type='radio' name='formvisible' value='0' {{ $course_closed }} {{ $disable_visibility }}>
                                              </div>
                                              <div class='col-sm-1'>
                                                {!! course_access_icon(COURSE_CLOSED) !!}
                                              </div>
                                              <div class='col-sm-10'> 
                                                {{ trans('langClosedCourse') }}
                                                <span class='d-inline'><small>{{ trans('langClosedCourseShort') }}</small></span>
                                              </div>
                                            </div>
                                            <div class='row mt-2'>
                                              <div class='col-sm-1'>
                                                <input class='mt-1' id='courseinactive' type='radio' name='formvisible' value='3' {{ $course_inactive }} {{ $disable_visibility }}>
                                              </div>
                                              <div class='col-sm-1'>
                                                  {!! course_access_icon(COURSE_INACTIVE) !!}
                                              </div>
                                              <div class='col-sm-10'>
                                                {!! trans('langInactiveCourse') !!}
                                                <span class='d-inline'><small>{{ trans('langCourseInactive') }}</small></span>
                                              </div>
                                            </div>
                                        </div>
                                    </div>

                                    <div class="row p-2"></div>


                                    <div class='form-group'>
                                        <label for='coursepassword' class='col-sm-4 control-label-notes'>{{ trans('langOptPassword') }}:</label>
                                        <div class='col-sm-10'>
                                              <input class='form-control' id='coursepassword' type='text' name='password' value='{{ q($password) }}' autocomplete='off'>
                                        </div>
                                        <div class='col-sm-2 text-center padding-thin'>
                                            <span id='result'></span>
                                        </div>
                                    </div>

                                    <div class="row p-2"></div>


                                    <div class='form-group'>
                                        <label for='Options' class='col-sm-4 control-label-notes'>{{ trans('langLanguage') }}:</label>
                                        <div class='col-sm-12'>
                                            {!! $lang_select_options !!}
                                        </div>
                                    </div>

                                    <div class="row p-2"></div>


                                    <div class='form-group'>
                                        <label for='courseoffline' class='col-sm-4 control-label-notes'>{{ trans('langCourseOfflineSettings') }}:</label>
                                        <div class="col-sm-12">
                                            <div class="radio">
                                                <label>
                                                    <input type='radio' value='1' name='enable_offline_course' {{ $log_offline_course_enable }} {{ $log_offline_course_inactive }}> {{ trans('langActivate') }}
                                                    <span class='help-block'><small>{{ trans('langCourseOfflineLegend') }}</small></span>
                                                </label>
                                            </div>
                                            <div class="radio">
                                                <label>
                                                    <input type='radio' value='0' name='enable_offline_course' {{ $log_offline_course_disable }} {{ $log_offline_course_inactive }}> {{ trans('langDeactivate') }}
                                                </label>
                                            </div>
                                        </div>
                                    </div>

                                    <div class="row p-2"></div>


                                    <div class='form-group'>
                                        <label class='col-sm-4 control-label-notes'>{{ trans('langCourseUserRequests') }}:</label>
                                        <div class='col-sm-12'>
                                            <div class='radio'>
                                            <div class='radio'>
                                              <label>
                                                    <input type='radio' value='0' name='disable_log_course_user_requests' {{ $log_course_user_requests_enable }} {{ $log_course_user_requests_inactive }}> {{ trans('langActivate') }}
                                                    <span class='help-block'><small>{{ $log_course_user_requests_disable }}</small></span>
                                              </label>
                                            </div>
                                              <label>
                                                    <input type='radio' value='1' name='disable_log_course_user_requests' {{ $log_course_user_requests_disable }} {{ $log_course_user_requests_inactive }}> {{ trans('langDeactivate') }}
                                              </label>
                                            </div>
                                        </div>
                                    </div>

                                    <div class="row p-2"></div>


                                    <div class='form-group'>
                                        <label class='col-sm-12 control-label-notes'>{{ trans('langCourseSharing') }}:</label>
                                        <div class='col-sm-12'>
                                            <div class='radio'>
                                              <label>
                                                    <input type='radio' value='1' name='s_radio' {{ $checkSharingEn }} {{ $sharing_radio_dis }}> {{ trans('langSharingEn') }}
                                              </label>
                                            </div>
                                            <div class='radio'>
                                              <label>
                                                    <input type='radio' value='0' name='s_radio' {{ $checkSharingDis }} {{ $sharing_radio_dis }}> {{ trans('langSharingDis') }}
                                                    <span class='help-block'><small>{{ $sharing_dis_label }}</small></span>
                                              </label>
                                            </div>
                                        </div>
                                    </div>

                                    <div class="row p-2"></div>


                                    <div class='form-group'>
                                        <label class='col-sm-4 control-label-notes'>{{ trans('langForum') }}:</label>
                                        <div class='col-sm-12'>
                                            <div class='radio'>
                                              <label>
                                                    <input type='radio' value='1' name='f_radio' {{ $checkForumEn }}> {{ trans('langDisableForumNotifications') }}
                                              </label>
                                            </div>
                                            <div class='radio'>
                                              <label>
                                                    <input type='radio' value='0' name='f_radio' {{ $checkForumDis }}> {{ trans('langActivateForumNotifications') }}
                                              </label>
                                            </div>
                                        </div>
                                    </div>

                                    <div class="row p-2"></div>


                                    <div class='form-group'>
                                        <label class='col-sm-4 control-label-notes'>
                                            {{ trans('langCourseRating') }}:
                                        </label>
                                        <div class='col-sm-12'>
                                                <div class='radio'>
                                                  <label>
                                                        <input type='radio' value='1' name='r_radio' {{ $checkRatingEn }}> {{ trans('langRatingEn') }}
                                                  </label>
                                                </div>
                                                <div class='radio'>
                                                  <label>
                                                        <input type='radio' value='0' name='r_radio' {{ $checkRatingDis }}> {{ trans('langRatingDis') }}
                                                  </label>
                                                </div>
                                            </div>
                                    </div>

                                    <div class="row p-2"></div>


                                    <div class='form-group'>
                                        <label class='col-sm-4 control-label-notes'>{{ trans('langCourseAnonymousRating') }}:</label>
                                        <div class='col-sm-12'>
                                            <div class='radio'>
                                              <label>
                                                    <input type='radio' value='1' name='ran_radio' {{ $checkAnonRatingEn }} {{ $anon_rating_radio_dis }}> {{ trans('langRatingAnonEn') }}
                                              </label>
                                            </div>
                                            <div class='radio'>
                                              <label>
                                                    <input type='radio' value='0' name='ran_radio' {{ $checkAnonRatingDis }} {{ $anon_rating_radio_dis }}> {{ trans('langRatingAnonDis') }}
                                                    <span class='help-block'><small>{{ $anon_rating_dis_label }}</small></span>
                                              </label>
                                            </div>
                                        </div>
                                    </div>

                                    <div class="row p-2"></div>


                                    <div class='form-group'>
                                        <label class='col-sm-4 control-label-notes'>{{ trans('langCourseCommenting') }}:</label>
                                        <div class='col-sm-12'>
                                            <div class='radio'>
                                              <label>
                                                    <input type='radio' value='1' name='c_radio' {{ $checkCommentEn }}> {{ trans('langCommentsEn') }}
                                              </label>
                                            </div>
                                            <div class='radio'>
                                              <label>
                                                    <input type='radio' value='0' name='c_radio' {{ $checkCommentDis }}> {{ trans('langCommentsDis') }}
                                              </label>
                                            </div>
                                        </div>
                                    </div>

                                    <div class="row p-2"></div>


                                    <div class='form-group'>
                                        <label class='col-sm-4 control-label-notes'>{{ trans('langAbuseReport') }}:</label>
                                        <div class='col-sm-12'>
                                            <div class='radio'>
                                              <label>
                                                    <input type='radio' value='1' name='ar_radio' {{ $checkAbuseReportEn }}> {{ trans('langAbuseReportEn') }}
                                              </label>
                                            </div>
                                            <div class='radio'>
                                              <label>
                                                    <input type='radio' value='0' name='ar_radio' {{ $checkAbuseReportDis }}> {{ trans('langAbuseReportDis') }}
                                              </label>
                                            </div>
                                        </div>
                                    </div>
                                    {!! showSecondFactorChallenge() !!}

                                    <div class="row p-2"></div>
                                    
                                    <div class='form-group'>
                                        <div class='col-sm-10 col-sm-offset-2'>
                                            <input class='btn btn-primary' type='submit' name='submit' value='{{ trans('langSubmit') }}'>
                                        </div>
                                    </div>
                                </fieldset>
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
