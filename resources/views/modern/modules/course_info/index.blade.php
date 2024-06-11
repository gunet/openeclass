
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

<div class="col-12 main-section">
<div class='{{ $container }} module-container py-lg-0'>
            <div class="course-wrapper d-lg-flex align-items-lg-strech w-100">

              <div id="background-cheat-leftnav" class="col_sidebar_active d-flex justify-content-start align-items-strech ps-lg-0 pe-lg-0">
                  <div class="d-none d-sm-block d-sm-none d-md-block d-md-none d-lg-block ContentLeftNav">
                      @include('layouts.partials.sidebar',['is_editor' => $is_editor])
                  </div>
              </div>

                <div class="col_maincontent_active">

                    <div class="row">


                          @include('layouts.common.breadcrumbs', ['breadcrumbs' => $breadcrumbs])

                          <div class="offcanvas offcanvas-start d-lg-none" tabindex="-1" id="collapseTools">
                              <div class="offcanvas-header">
                                  <button type="button" class="btn-close text-reset" data-bs-dismiss="offcanvas" aria-label="Close"></button>
                              </div>
                              <div class="offcanvas-body">
                                  @include('layouts.partials.sidebar',['is_editor' => $is_editor])
                              </div>
                          </div>


                          @include('layouts.partials.legend_view')

                          <div id='operations_container'>
                                {!! $action_bar !!}
                          </div>

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


                          <div class='d-lg-flex gap-4 mt-4'>
                          <div class='flex-grow-1'>
                            <div class='form-wrapper form-edit rounded'>

                              <form class='form-horizontal' role='form' method='post' action="{{ $form_url }}" onsubmit='return validateNodePickerForm();'>
                                <fieldset>

                                    <div class='form-group'>
                                        <label for='fcode' class='col-sm-12 control-label-notes'>{{ trans('langCode') }}</label>
                                        <div class='col-sm-12'>
                                            <input type='text' class='form-control' name='fcode' id='fcode' value='{{ $public_code }}'>
                                        </div>
                                    </div>

                                    <div class='form-group mt-4'>
                                        <label for='title' class='col-sm-12 control-label-notes'>{{ trans('langCourseTitle') }}</label>
                                        <div class='col-sm-12'>
                                            <input type='text' class='form-control' name='title' id='title' value='{{ $title }}'>
                                        </div>
                                    </div>


                                    <div class='form-group mt-4'>
                                        <label for='teacher_name' class='col-sm-12 control-label-notes'>{{ trans('langTeachers') }}</label>
                                        <div class='col-sm-12'>
                                            <input type='text' class='form-control' name='teacher_name' id='teacher_name' value='{{ $teacher_name }} '>
                                        </div>
                                    </div>



                                    <div class='form-group mt-4'>
                                        <label for='Faculty' class='col-sm-12 control-label-notes'>{{ trans('langFaculty') }}</label>
                                        <div class='col-sm-12'>
                                            {!! $buildusernode !!}
                                        </div>
                                    </div>




                                    <div class='form-group mt-4'>
                                        <label for='course_keywords' class='col-sm-12 control-label-notes'>{{ trans('langCourseKeywords') }}</label>
                                        <div class='col-sm-12'>
                                            <input type='text' class='form-control' name='course_keywords' id='course_keywords' value='{{ $course_keywords }}'>
                                        </div>
                                    </div>

                                    <div class='form-group mt-4'>
                                        <p class='col-sm-12 control-label-notes mb-2'> {{ trans('langCourseFormat') }}</p>
                                        <div class='col-sm-12'>
                                            <div class="radio mb-2 @if(get_config('show_collaboration') and get_config('show_always_collaboration')) pe-none opacity-help @endif">
                                                <label>
                                                    <input type='radio' name='view_type' value='simple' id='simple' {{ $course_type_simple }}>
                                                    {{ trans('langCourseSimpleFormat') }}
                                                </label>
                                            </div>
                                            <div class="radio mb-2 @if(get_config('show_collaboration') and get_config('show_always_collaboration')) pe-none opacity-help @endif">
                                                <label>
                                                    <input type='radio' name='view_type' value='units' id='units' {{ $course_type_units }}>
                                                    {{ trans('langWithCourseUnits') }}
                                                </label>
                                            </div>
                                            
                                            <div class="radio mb-2 @if(get_config('show_collaboration') and get_config('show_always_collaboration')) pe-none opacity-help @endif">
                                                <label>
                                                    <input type='radio' name='view_type' value='activity' id='activity' {{ $course_type_activity }}>
                                                    {{ trans('langCourseActivityFormat') }}
                                                </label>
                                            </div>
                                            <div class="radio mb-2 @if(get_config('show_collaboration') and get_config('show_always_collaboration')) pe-none opacity-help @endif">
                                                <label>
                                                    <input type='radio' name='view_type' value='wall' id='wall' {{ $course_type_wall }}>
                                                    {{ trans('langCourseWallFormat') }}
                                                </label>
                                            </div>
                                            <div class="radio mb-2 @if(get_config('show_collaboration') and get_config('show_always_collaboration')) pe-none opacity-help @endif">
                                                <label>
                                                    <input type='radio' name='view_type' value='flippedclassroom' id='flippedclassroom' {{ $course_type_flipped_classroom }}>
                                                    {{ trans('langFlippedClassroom') }}
                                                </label>
                                            </div>
                                            <div class="radio @if(!get_config('show_collaboration') and !get_config('show_always_collaboration')) d-none @endif">
                                                <label>
                                                    <input type='radio' name='view_type' value='sessions' id='sessions' {{ $course_type_sessions }}>
                                                    {{ trans('langSessionType') }}
                                                </label>
                                            </div>
                                        </div>
                                    </div>


                                    @if (isset($isOpenCourseCertified)) {
                                        <input type='hidden' name='course_license' value='{{ getIndirectReference($course_license) }}'>
                                    @endif


                                    <div class='form-group mt-4'>
                                        <p class='col-sm-12 control-label-notes mb-2'>{{ trans('langOpenCoursesLicense') }}</p>
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




                                    <div class='form-group mt-4'>
                                        <div class='col-sm-12 col-sm-offset-2' id='cc'>
                                            {!! $license_selection !!}
                                        </div>
                                    </div>


                                    <div class='form-group mt-4'>
                                        <p class='col-sm-12 control-label-notes mb-2'>{{ trans('langConfidentiality') }}</p>
                                        <div class='col-sm-12'>

                                            <div class='radio mb-3'>
                                                <label>
                                                    <input class='input-StatusCourse' id='courseopen' type='radio' name='formvisible' value='2' {{ $course_open }}>
                                                    <label for="courseopen">{!! course_access_icon(COURSE_OPEN) !!}</label>
                                                    {{ trans('langOpenCourse') }}
                                                </label>
                                                <div class='help-block'>{{ trans('langPublic') }}</div>
                                            </div>


                                            <div class='radio mb-3'>
                                                <label>
                                                    <input class='input-StatusCourse' id='coursewithregistration' type='radio' name='formvisible' value='1' {{ $course_registration }}>
                                                    <label for="coursewithregistration">{!! course_access_icon(COURSE_REGISTRATION) !!}</label>
                                                    {{ trans('langTypeRegistration') }}
                                                </label>
                                                <div class='help-block'>{{ trans('langPrivOpen') }}</div>
                                            </div>


                                            <div class='radio mb-3'>
                                                <label>
                                                    <input class='input-StatusCourse' id='courseclose' type='radio' name='formvisible' value='0' {{ $course_closed }} {{ $disable_visibility }}>
                                                    <label for="courseclose">{!! course_access_icon(COURSE_CLOSED) !!}</label>
                                                    {{ trans('langClosedCourse') }}
                                                </label>
                                                <div class='help-block'>{{ trans('langClosedCourseShort') }}</div>
                                            </div>


                                            <div class='radio'>
                                                <label>
                                                    <input class='input-StatusCourse' id='courseinactive' type='radio' name='formvisible' value='3' {{ $course_inactive }} {{ $disable_visibility }}>
                                                    <label for="courseinactive">{!! course_access_icon(COURSE_INACTIVE) !!}</label>
                                                    {!! trans('langInactiveCourse') !!}
                                                </label>
                                                <div class='help-block'>{{ trans('langCourseInactive') }}</div>
                                            </div>

                                        </div>
                                    </div>


                                    <div class='form-group mt-4'>
                                        <label for='coursepassword' class='col-sm-12 control-label-notes'>{{ trans('langOptPassword') }}</label>
                                        <div class='col-sm-12'>
                                              <input class='form-control' id='coursepassword' type='text' name='password' value='{{ $password }}' autocomplete='off'>
                                        </div>
                                        <div class='col-sm-12 text-center padding-thin'>
                                            <span id='result'></span>
                                        </div>
                                    </div>


                                    <div class='form-group mt-4'>
                                        <label for='Options' class='col-sm-12 control-label-notes'>{{ trans('langLanguage') }}</label>
                                        <div class='col-sm-12'>
                                            {!! $lang_select_options !!}
                                        </div>
                                    </div>


                                    <div class='form-group mt-4'>
                                        <p for='courseoffline' class='col-sm-12 control-label-notes mb-2'>{{ trans('langCourseOfflineSettings') }}</p>
                                        <div class="col-sm-12">
                                            <div class="radio mb-2">
                                                <label>
                                                    <input type='radio' value='1' name='enable_offline_course' {{ $log_offline_course_enable }} {{ $log_offline_course_inactive }}>
                                                    {{ trans('langActivate') }}
                                                </label>
                                                <div class='help-block'>{{ trans('langCourseOfflineLegend') }}</div>
                                            </div>
                                            <div class="radio">
                                                <label>
                                                    <input type='radio' value='0' name='enable_offline_course' {{ $log_offline_course_disable }} {{ $log_offline_course_inactive }}>
                                                    {{ trans('langDeactivate') }}
                                                </label>
                                            </div>
                                        </div>
                                    </div>


                                    <div class='form-group mt-4'>
                                        <p class='col-sm-12 control-label-notes mb-2'>{{ trans('langCourseUserRequests') }}</p>
                                        <div class='col-sm-12'>
                                            <div class='radio mb-2'>
                                                <label>
                                                    <input type='radio' value='0' name='disable_log_course_user_requests' {{ $log_course_user_requests_enable }} {{ $log_course_user_requests_inactive }}>
                                                    {{ trans('langActivate') }}
                                                </label>
                                                <div class='help-block'>{{ $log_course_user_requests_disable }}</div>
                                            </div>
                                            <div class='radio'>
                                                <label>
                                                    <input type='radio' value='1' name='disable_log_course_user_requests' {{ $log_course_user_requests_disable }} {{ $log_course_user_requests_inactive }}>
                                                    {{ trans('langDeactivate') }}
                                                </label>
                                            </div>
                                        </div>
                                    </div>

                                    <div class='form-group mt-4'>
                                        <p class='col-sm-12 control-label-notes mb-2'>{{ trans('langUsersListAccess') }}</p>
                                        <div class='col-sm-12'>
                                            <div class='radio mb-2'>
                                                <label>
                                                    <input type='radio' value='1' name='enable_access_users_list' {{ $check_enable_access_users_list }} >
                                                    {{ trans('langActivate') }}
                                                </label>
                                                <div class='help-block'>{{ trans('langUsersListAccessInfo') }}</div>
                                            </div>
                                            <div class='radio'>
                                                <label>
                                                    <input type='radio' value='0' name='enable_access_users_list' {{ $check_disable_access_users_list}} >
                                                    {{ trans('langDeactivate') }}

                                                </label>
                                            </div>
                                        </div>
                                    </div>

                                    <div class='form-group mt-4'>
                                        <p class='col-sm-12 control-label-notes mb-2'>{{ trans('langCourseSharing') }}</p>
                                        <div class='col-sm-12'>
                                            <div class='radio mb-2'>
                                              <label>
                                                    <input type='radio' value='1' name='s_radio' {{ $checkSharingEn }} {{ $sharing_radio_dis }}>
                                                    {{ trans('langSharingEn') }}
                                              </label>
                                            </div>
                                            <div class='radio'>
                                              <label>
                                                    <input type='radio' value='0' name='s_radio' {{ $checkSharingDis }} {{ $sharing_radio_dis }}>
                                                    {{ trans('langSharingDis') }}

                                              </label>
                                              <div class='help-block'>{{ $sharing_dis_label }}</div>
                                            </div>
                                        </div>
                                    </div>




                                    <div class='form-group mt-4'>
                                        <p class='col-sm-12 control-label-notes mb-2'>{{ trans('langForum') }}</p>
                                        <div class='col-sm-12'>
                                            <div class='radio mb-2'>
                                              <label>
                                                    <input type='radio' value='1' name='f_radio' {{ $checkForumEn }}>
                                                    {{ trans('langDisableForumNotifications') }}
                                              </label>
                                            </div>
                                            <div class='radio'>
                                              <label>
                                                    <input type='radio' value='0' name='f_radio' {{ $checkForumDis }}>
                                                    {{ trans('langActivateForumNotifications') }}
                                              </label>
                                            </div>
                                        </div>
                                    </div>




                                    <div class='form-group mt-4'>
                                        <p class='col-sm-12 control-label-notes mb-2'>{{ trans('langCourseRating') }}:</p>
                                        <div class='col-sm-12'>
                                            <div class='radio mb-2'>
                                                <label>
                                                    <input type='radio' value='1' name='r_radio' {{ $checkRatingEn }}>
                                                    {{ trans('langRatingEn') }}
                                                </label>
                                            </div>
                                            <div class='radio'>
                                                <label>
                                                    <input type='radio' value='0' name='r_radio' {{ $checkRatingDis }}>
                                                    {{ trans('langRatingDis') }}
                                                </label>
                                            </div>
                                        </div>
                                    </div>



                                    <div class='form-group mt-4'>
                                        <p class='col-sm-12 control-label-notes mb-2'>{{ trans('langCourseAnonymousRating') }}</p>
                                        <div class='col-sm-12'>
                                            <div class='radio mb-2'>
                                              <label>
                                                    <input type='radio' value='1' name='ran_radio' {{ $checkAnonRatingEn }} {{ $anon_rating_radio_dis }}>
                                                    {{ trans('langRatingAnonEn') }}
                                              </label>
                                            </div>
                                            <div class='radio'>
                                              <label>
                                                    <input type='radio' value='0' name='ran_radio' {{ $checkAnonRatingDis }} {{ $anon_rating_radio_dis }}>
                                                    {{ trans('langRatingAnonDis') }}

                                              </label>
                                              <div class='help-block'>{{ $anon_rating_dis_label }}</div>
                                            </div>
                                        </div>
                                    </div>




                                    <div class='form-group mt-4'>
                                        <p class='col-sm-12 control-label-notes mb-2'>{{ trans('langCourseCommenting') }}</p>
                                        <div class='col-sm-12'>
                                            <div class='radio mb-2'>
                                              <label>
                                                    <input type='radio' value='1' name='c_radio' {{ $checkCommentEn }}>
                                                    {{ trans('langCommentsEn') }}
                                              </label>
                                            </div>
                                            <div class='radio'>
                                              <label>
                                                    <input type='radio' value='0' name='c_radio' {{ $checkCommentDis }}>
                                                    {{ trans('langCommentsDis') }}
                                              </label>
                                            </div>
                                        </div>
                                    </div>

                                    <div class='form-group mt-4'>
                                        <p class='col-sm-12 control-label-notes mb-2'>{{ trans('langAbuseReport') }}</p>
                                        <div class='col-sm-12'>
                                            <div class='radio mb-2'>
                                              <label>
                                                    <input type='radio' value='1' name='ar_radio' {{ $checkAbuseReportEn }}>
                                                    {{ trans('langAbuseReportEn') }}
                                              </label>
                                            </div>
                                            <div class='radio'>
                                              <label>
                                                    <input type='radio' value='0' name='ar_radio' {{ $checkAbuseReportDis }}>
                                                    {{ trans('langAbuseReportDis') }}
                                              </label>
                                            </div>
                                        </div>
                                    </div>
                                    {!! showSecondFactorChallenge() !!}

                                    <div class='form-group mt-5 mb-1 d-flex justify-content-end align-items-center'>
                                        <input class='btn submitAdminBtn' type='submit' name='submit' value='{{ trans('langSubmit') }}'>
                                    </div>
                                </fieldset>
                                {!! generate_csrf_token_form_field() !!}
                              </form>
                            </div>
                          </div>
                          <div class='d-none d-lg-block'>
                                <img class='form-image-modules' src='{!! get_form_image() !!}' alt='form-image'>
                            </div>
                        </div>

                    </div>
                </div>
            </div>

</div>
</div>
@endsection
