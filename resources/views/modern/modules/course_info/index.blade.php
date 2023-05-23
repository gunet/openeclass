
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

              <div id="background-cheat-leftnav" class="col-xl-2 col-lg-3 col_sidebar_active d-flex justify-content-start align-items-strech ps-lg-0 pe-lg-3">
                  <div class="d-none d-sm-block d-sm-none d-md-block d-md-none d-lg-block ContentLeftNav">
                      @include('layouts.partials.sidebar',['is_editor' => $is_editor])
                  </div>
              </div>

                <div class="col-xl-10 col-lg-9 col-12 col_maincontent_active">

                    <div class="row p-lg-5 p-md-5 ps-1 pe-1 pt-5 pb-5">


                          @include('layouts.common.breadcrumbs', ['breadcrumbs' => $breadcrumbs])

                          <div class="offcanvas offcanvas-start d-lg-none" tabindex="-1" id="collapseTools" aria-labelledby="offcanvasExampleLabel">
                              <div class="offcanvas-header">
                                  <button type="button" class="btn-close text-reset" data-bs-dismiss="offcanvas" aria-label="Close"></button>
                              </div>
                              <div class="offcanvas-body">
                                  @include('layouts.partials.sidebar',['is_editor' => $is_editor])
                              </div>
                          </div>


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

                          <div id='operations_container'>
                                {!! $action_bar !!}
                          </div>

                          <div class='col-12'>
                            <div class='form-wrapper form-edit py-3 rounded'>

                              <form class='form-horizontal' role='form' method='post' action="{{ $form_url }}" onsubmit='return validateNodePickerForm();'>
                                <fieldset>
                                    <div class='row'>
                                        <div class='col-md-6 col-12'>
                                            <div class='form-group'>
                                                <label for='fcode' class='col-sm-6 control-label-notes'>{{ trans('langCode') }}</label>
                                                <div class='col-sm-12'>
                                                    <input type='text' class='form-control' name='fcode' id='fcode' value='{{ $public_code }}'>
                                                </div>
                                            </div>
                                        </div>
                                        <div class='col-md-6 col-12'>
                                            <div class='form-group mt-md-0 mt-4'>
                                                <label for='title' class='col-sm-12 control-label-notes'>{{ trans('langCourseTitle') }}</label>
                                                <div class='col-sm-12'>
                                                    <input type='text' class='form-control' name='title' id='title' value='{{ q($title) }}'>
                                                </div>
                                            </div>
                                        </div>
                                    </div>



                                    <div class='row'>
                                        <div class='col-md-6 col-12'>
                                            <div class='form-group mt-4'>
                                                <label for='teacher_name' class='col-sm-12 control-label-notes'>{{ trans('langTeachers') }}</label>
                                                <div class='col-sm-12'>
                                                    <input type='text' class='form-control' name='teacher_name' id='teacher_name' value='{{ $teacher_name }} '>
                                                </div>
                                            </div>
                                        </div>

                                        <div class='col-md-6 col-12'>
                                            <div class='form-group mt-4'>
                                                <label for='Faculty' class='col-sm-12 control-label-notes'>{{ trans('langFaculty') }}</label>
                                                <div class='col-sm-12'>
                                                    {!! $buildusernode !!}
                                                </div>
                                            </div>
                                        </div>
                                    </div>


                                    <div class='form-group mt-4'>
                                        <label for='course_keywords' class='col-sm-12 control-label-notes'>{{ trans('langCourseKeywords') }}</label>
                                        <div class='col-sm-12'>
                                            <input type='text' class='form-control' name='course_keywords' id='course_keywords' value='{{ $course_keywords }}'>
                                        </div>
                                    </div>

                                    <div class='row'>
                                        <div class='col-md-6 col-12'>
                                            <div class='form-group mt-4'>
                                                <label class='col-sm-12 control-label-notes mb-2'> {{ trans('langCourseFormat') }}</label>
                                                <div class='col-sm-12'>
                                                    <div class='radio mb-2'>
                                                    <label>
                                                        <input type='radio' name='view_type' value='simple' id='simple' {{ $course_type_simple }}>
                                                        {{ trans('langCourseSimpleFormat') }}
                                                    </label>
                                                    </div>
                                                    <div class='radio mb-2'>
                                                    <label>
                                                        <input type='radio' name='view_type' value='units' id='units' {{ $course_type_units }}>
                                                        {{ trans('langWithCourseUnits') }}
                                                    </label>
                                                    </div>
                                                    <div class='radio mb-2'>
                                                    <label>
                                                        <input type='radio' name='view_type' value='activity' id='activity' {{ $course_type_activity }}>
                                                        {{ trans('langCourseActivityFormat') }}
                                                    </label>
                                                    </div>
                                                    <div class='radio mb-2'>
                                                    <label>
                                                        <input type='radio' name='view_type' value='wall' id='wall' {{ $course_type_wall }}>
                                                        {{ trans('langCourseWallFormat') }}
                                                    </label>
                                                    </div>
                                                    <div class='radio mb-2'>
                                                       <label>
                                                          <input type='radio' name='view_type' value='flippedclassroom' id='flippedclassroom' {{ $course_type_flipped_classroom }}>
                                                          {{ trans('langFlippedClassroom') }}
                                                        </label>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>

                                        @if (isset($isOpenCourseCertified)) {
                                            <input type='hidden' name='course_license' value='{{ getIndirectReference($course_license) }}'>
                                        @endif

                                        <div class='col-md-6 col-12'>
                                            <div class='form-group mt-4'>
                                                <label class='col-sm-12 control-label-notes mb-2'>{{ trans('langOpenCoursesLicense') }}</label>
                                                <div class='col-sm-12'>
                                                    <div class='radio mb-2'>
                                                    <label>
                                                        <input type='radio' name='l_radio' value='0' {{ $license_checked0 }} $disabledVisibility>
                                                        {{ trans('langLicenseUnset') }}
                                                    </label>
                                                    </div>
                                                    <div class='radio mb-2'>
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
                                        </div>
                                    </div>


                                    <div class='form-group mt-4'>
                                        <div class='col-sm-12 col-sm-offset-2' id='cc'>
                                            {!! $license_selection !!}
                                        </div>
                                    </div>


                                    <div class='form-group mt-4'>
                                        <label class='col-sm-12 control-label-notes mb-2'>{{ trans('langConfidentiality') }}</label>
                                        <div class='col-sm-12'>

                                            <div class='radio mb-3'>
                                                <label>
                                                    <input class='input-StatusCourse' id='courseopen' type='radio' name='formvisible' value='2' {{ $course_open }}>
                                                    <label for="courseopen">{!! course_access_icon(COURSE_OPEN) !!}</label>
                                                    {{ trans('langOpenCourse') }}
                                                </label>
                                                <div class='help-block ps-4 ms-3'>{{ trans('langPublic') }}</div>
                                            </div>


                                            <div class='radio mb-3'>
                                                <label>
                                                    <input class='input-StatusCourse' id='coursewithregistration' type='radio' name='formvisible' value='1' {{ $course_registration }}>
                                                    <label for="coursewithregistration">{!! course_access_icon(COURSE_REGISTRATION) !!}</label>
                                                    {{ trans('langTypeRegistration') }}
                                                </label>
                                                <div class='help-block ps-4 ms-3'>{{ trans('langPrivOpen') }}</div>
                                            </div>


                                            <div class='radio mb-3'>
                                                <label>
                                                    <input class='input-StatusCourse' id='courseclose' type='radio' name='formvisible' value='0' {{ $course_closed }} {{ $disable_visibility }}>
                                                    <label for="courseclose">{!! course_access_icon(COURSE_CLOSED) !!}</label>
                                                    {{ trans('langClosedCourse') }}
                                                </label>
                                                <div class='help-block ps-4 ms-3'>{{ trans('langClosedCourseShort') }}</div>
                                            </div>


                                            <div class='radio'>
                                                <label>
                                                    <input class='input-StatusCourse' id='courseinactive' type='radio' name='formvisible' value='3' {{ $course_inactive }} {{ $disable_visibility }}>
                                                    <label for="courseinactive">{!! course_access_icon(COURSE_INACTIVE) !!}</label>
                                                    {!! trans('langInactiveCourse') !!}
                                                </label>
                                                <div class='help-block ps-4 ms-3'>{{ trans('langCourseInactive') }}</div>
                                            </div>

                                        </div>
                                    </div>


                                    <div class='form-group mt-4'>
                                        <label for='coursepassword' class='col-sm-12 control-label-notes'>{{ trans('langOptPassword') }}</label>
                                        <div class='col-sm-12'>
                                              <input class='form-control' id='coursepassword' type='text' name='password' value='{{ q($password) }}' autocomplete='off'>
                                        </div>
                                        <div class='col-sm-12 text-center padding-thin'>
                                            <span id='result' class='text-dark'></span>
                                        </div>
                                    </div>


                                    <div class='form-group mt-4'>
                                        <label for='Options' class='col-sm-12 control-label-notes'>{{ trans('langLanguage') }}</label>
                                        <div class='col-sm-12'>
                                            {!! $lang_select_options !!}
                                        </div>
                                    </div>


                                    <div class='form-group mt-4'>
                                        <label for='courseoffline' class='col-sm-12 control-label-notes mb-2'>{{ trans('langCourseOfflineSettings') }}</label>
                                        <div class="col-sm-12">
                                            <div class="radio mb-2">
                                                <label>
                                                    <input type='radio' value='1' name='enable_offline_course' {{ $log_offline_course_enable }} {{ $log_offline_course_inactive }}>
                                                    <span class='ps-2'>{{ trans('langActivate') }}</span>
                                                </label>
                                                <div class='help-block ps-5'>{{ trans('langCourseOfflineLegend') }}</div>
                                            </div>
                                            <div class="radio">
                                                <label>
                                                    <input type='radio' value='0' name='enable_offline_course' {{ $log_offline_course_disable }} {{ $log_offline_course_inactive }}>
                                                    <span class='ps-2'>{{ trans('langDeactivate') }}</span>
                                                </label>
                                            </div>
                                        </div>
                                    </div>


                                    <div class='form-group mt-4'>
                                        <label class='col-sm-12 control-label-notes mb-2'>{{ trans('langCourseUserRequests') }}</label>
                                        <div class='col-sm-12'>
                                            <div class='radio mb-2'>
                                                <label>
                                                        <input type='radio' value='0' name='disable_log_course_user_requests' {{ $log_course_user_requests_enable }} {{ $log_course_user_requests_inactive }}>
                                                        <span class='ps-2'>{{ trans('langActivate') }}</span>
                                                </label>
                                                <div class='help-block ps-5'>{{ $log_course_user_requests_disable }}</div>
                                            </div>
                                            <div class='radio'>
                                                <label>
                                                        <input type='radio' value='1' name='disable_log_course_user_requests' {{ $log_course_user_requests_disable }} {{ $log_course_user_requests_inactive }}>
                                                        <span class='ps-2'>{{ trans('langDeactivate') }}</span>
                                                </label>
                                            </div>
                                        </div>
                                    </div>

                                    <div class='form-group mt-4'>
                                        <label class='col-sm-12 control-label-notes mb-2'>{{ trans('langUsersListAccess') }}</label>
                                        <div class='col-sm-12'>
                                            <div class='radio mb-2'>
                                                <label>
                                                    <input type='radio' value='1' name='enable_access_users_list' {{ $check_enable_access_users_list }} >
                                                    <span class='ps-2'> {{ trans('langActivate') }}</span>
                                                </label>
                                                <div class='help-block ps-5'>{{ trans('langUsersListAccessInfo') }}</div>
                                            </div>
                                            <div class='radio'>
                                                <label>
                                                    <input type='radio' value='0' name='enable_access_users_list' {{ $check_disable_access_users_list}} >
                                                    <span class='ps-2'>{{ trans('langDeactivate') }}</span>

                                                </label>
                                            </div>
                                        </div>
                                    </div>

                                    <div class='form-group mt-4'>
                                        <label class='col-sm-12 control-label-notes mb-2'>{{ trans('langCourseSharing') }}</label>
                                        <div class='col-sm-12'>
                                            <div class='radio mb-2'>
                                              <label>
                                                    <input type='radio' value='1' name='s_radio' {{ $checkSharingEn }} {{ $sharing_radio_dis }}>
                                                    <span class='ps-2'>{{ trans('langSharingEn') }}</span>
                                              </label>
                                            </div>
                                            <div class='radio'>
                                              <label>
                                                    <input type='radio' value='0' name='s_radio' {{ $checkSharingDis }} {{ $sharing_radio_dis }}>
                                                    <span class='ps-2'>{{ trans('langSharingDis') }}</span>

                                              </label><div class='help-block ps-5'>{{ $sharing_dis_label }}</div>
                                            </div>
                                        </div>
                                    </div>




                                    <div class='form-group mt-4'>
                                        <label class='col-sm-12 control-label-notes mb-2'>{{ trans('langForum') }}</label>
                                        <div class='col-sm-12'>
                                            <div class='radio mb-2'>
                                              <label>
                                                    <input type='radio' value='1' name='f_radio' {{ $checkForumEn }}>
                                                    <span class='ps-2'>{{ trans('langDisableForumNotifications') }}</span>
                                              </label>
                                            </div>
                                            <div class='radio'>
                                              <label>
                                                    <input type='radio' value='0' name='f_radio' {{ $checkForumDis }}>
                                                    <span class='ps-2'>{{ trans('langActivateForumNotifications') }}</span>
                                              </label>
                                            </div>
                                        </div>
                                    </div>




                                    <div class='form-group mt-4'>
                                        <label class='col-sm-12 control-label-notes mb-2'>
                                            {{ trans('langCourseRating') }}:
                                        </label>
                                        <div class='col-sm-12'>
                                                <div class='radio mb-2'>
                                                  <label>
                                                        <input type='radio' value='1' name='r_radio' {{ $checkRatingEn }}>
                                                        <span class='ps-2'>{{ trans('langRatingEn') }}</span>
                                                  </label>
                                                </div>
                                                <div class='radio'>
                                                  <label>
                                                        <input type='radio' value='0' name='r_radio' {{ $checkRatingDis }}>
                                                        <span class='ps-2'>{{ trans('langRatingDis') }}</span>
                                                  </label>
                                                </div>
                                            </div>
                                    </div>



                                    <div class='form-group mt-4'>
                                        <label class='col-sm-12 control-label-notes mb-2'>{{ trans('langCourseAnonymousRating') }}</label>
                                        <div class='col-sm-12'>
                                            <div class='radio mb-2'>
                                              <label>
                                                    <input type='radio' value='1' name='ran_radio' {{ $checkAnonRatingEn }} {{ $anon_rating_radio_dis }}>
                                                    <span class='ps-2'>{{ trans('langRatingAnonEn') }}</span>
                                              </label>
                                            </div>
                                            <div class='radio'>
                                              <label>
                                                    <input type='radio' value='0' name='ran_radio' {{ $checkAnonRatingDis }} {{ $anon_rating_radio_dis }}>
                                                    <span class='ps-2'>{{ trans('langRatingAnonDis') }}</span>

                                              </label><div class='help-block ps-5'>{{ $anon_rating_dis_label }}</div>
                                            </div>
                                        </div>
                                    </div>




                                    <div class='form-group mt-4'>
                                        <label class='col-sm-12 control-label-notes mb-2'>{{ trans('langCourseCommenting') }}</label>
                                        <div class='col-sm-12'>
                                            <div class='radio mb-2'>
                                              <label>
                                                    <input type='radio' value='1' name='c_radio' {{ $checkCommentEn }}>
                                                    <span class='ps-2'>{{ trans('langCommentsEn') }}</span>
                                              </label>
                                            </div>
                                            <div class='radio'>
                                              <label>
                                                    <input type='radio' value='0' name='c_radio' {{ $checkCommentDis }}>
                                                    <span class='ps-2'>{{ trans('langCommentsDis') }}</span>
                                              </label>
                                            </div>
                                        </div>
                                    </div>

                                    <div class='form-group mt-4'>
                                        <label class='col-sm-12 control-label-notes mb-2'>{{ trans('langAbuseReport') }}</label>
                                        <div class='col-sm-12'>
                                            <div class='radio mb-2'>
                                              <label>
                                                    <input type='radio' value='1' name='ar_radio' {{ $checkAbuseReportEn }}>
                                                    <span class='ps-2'>{{ trans('langAbuseReportEn') }}</span>
                                              </label>
                                            </div>
                                            <div class='radio'>
                                              <label>
                                                    <input type='radio' value='0' name='ar_radio' {{ $checkAbuseReportDis }}>
                                                    <span class='ps-2'>{{ trans('langAbuseReportDis') }}</span>
                                              </label>
                                            </div>
                                        </div>
                                    </div>
                                    {!! showSecondFactorChallenge() !!}

                                    <div class='form-group mt-5 d-flex justify-content-center align-items-center'>
                                        <input class='btn submitAdminBtn' type='submit' name='submit' value='{{ trans('langSubmit') }}'>
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
