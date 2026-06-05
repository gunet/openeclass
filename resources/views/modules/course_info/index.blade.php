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
            pwStrengthTooShort: "{{ js_escape(trans('langPwStrengTooShort')) }}",
            pwStrengthWeak: "{{ js_escape(trans('langPwStrengthWeak')) }}",
            pwStrengthGood: "{{ js_escape(trans('langPwStrengthGood')) }}",
            pwStrengthStrong: "{{ js_escape(trans('langPwStrengthStrong')) }}"
        }

        function displayCoursePassword() {
            var isDeactivated = $('#courseclose, #courseiactive').is(":checked");
            $('#coursepassword, #faculty_users_registration').prop('disabled', isDeactivated);
            $('#course_password_panel').toggleClass('d-none', isDeactivated);
        }

        function updateVisibility() {
            const isChecked = $('#type_collab').is(":checked");
            $("#radio_flippedclassroom, #radio_activity, #radio_wall").toggle(!isChecked);
            $("#radio_collaborative").toggle(isChecked);
        }

        function registrationDateVisibility() {
            var isRegistrationSelected = $('#coursewithregistration').is(':checked');
            $('#course_registration_date').toggle(isRegistrationSelected);
        }

        function displayImages(images, type, contentId) {
            let html = '<div class="row">';
            images.forEach(function (image) {
                html += `
                        <div class="col-md-3 col-sm-4 col-6 mb-3">
                            <div class="card print-image-card" style="cursor: pointer;" data-image-path="${image.path}" data-image-name="${image.name}" data-type="${type}" data-image-id="${image.id}">
                                <img src="${image.url}" class="card-img-top" style="height: 150px; object-fit: cover;" alt="${image.name}">
                                <div class="card-body p-2">
                                    <small class="text-muted">${image.name}</small>
                                </div>
                            </div>
                        </div>
                    `;
            });

            html += '</div>';
            $(contentId).html(html);

            // Handle image selection
            $(contentId).on('click', '.print-image-card', function () {
                const imagePath = $(this).data('image-path');
                const imageName = $(this).data('image-name');
                const imageType = $(this).data('type');
                const imageID = $(this).data('image-id');

                // Remove previous selection styling
                $(contentId).find('.print-image-card').removeClass('border-primary');

                // Add selection styling
                $(this).addClass('border-primary');

                // Update hidden input and display
                if (imageType === 'header') {
                    $('#choose_print_header_from_list').val(imageID);
                    $('#selectedPrintHeaderImage').html(`<small class="text-success"><i class="fa fa-check"></i> Selected: ${imageName}</small>`);
                } else {
                    $('#choose_print_footer_from_list').val(imageID);
                    $('#selectedPrintFooterImage').html(`<small class="text-success"><i class="fa fa-check"></i> Selected: ${imageName}</small>`);
                }

                // Close modal after selection
                setTimeout(function () {
                    $(imageType === 'header' ? '#PrintHeaderImagesModal' : '#PrintFooterImagesModal').modal('hide');
                }, 500);
            });
        }

        function loadPrintImages(type) {
            const modalId = type === 'header' ? '#PrintHeaderImagesModal' : '#PrintFooterImagesModal';
            const contentId = type === 'header' ? '#printHeaderImagesContent' : '#printFooterImagesContent';

            $(contentId).html('<div class="text-center"><i class="fa fa-spinner fa-spin"></i> Loading images...</div>');

            $.ajax({
                url: '{{ $urlAppend }}modules/course_info/ajax_load_images.php',
                method: 'GET',
                data: {
                    course_id: '{{ $course_id ?? "" }}',
                    type: type
                },
                dataType: 'json',
                success: function (response) {
                    if (response.success && response.images) {
                        displayImages(response.images, type, contentId);
                    } else {
                        $(contentId).html('<div class="alert alert-info">{{ trans("langNoImagesFound") }}</div>');
                    }
                },
                error: function (xhr, status, error) {
                    console.error('Error loading images:', error);
                    $(contentId).html('<div class="alert alert-danger">Error loading images. Please try again.</div>');
                }
            });
        }

        {{-- Ready --}}
        $(document).ready(function () {
            displayCoursePassword();
            registrationDateVisibility();
            updateVisibility();

            $('#coursepassword').keyup(function () {
                $('#result').html(checkStrength($('#coursepassword').val()))
            });
            
            $('input[name="formvisible"]').change(function() {
                displayCoursePassword();
                registrationDateVisibility();
            });
            
            $('input[name=l_radio]').change(function () {
                $('#cc').toggle($('#cc_license').is(":checked"));
            }).change();

            const $helper = $("#radio_collaborative_helper");
            if ($helper.length > 0) {
                $("#radio_collaborative").toggle($helper.val() != 0);
            }

            $('#type_collab').on('change', updateVisibility);

            $('#courseEndDate').datepicker({
                format: 'dd-mm-yyyy',
                pickerPosition: 'bottom-right',
                language: '{{ $language }}',
                autoclose: true
            });

            $('#courseStartDate').datepicker({
                format: 'dd-mm-yyyy',
                pickerPosition: 'bottom-right',
                language: '{{ $language }}',
                autoclose: true
            });

            $('#course_enableEndDate').change(function() {
                var dateType = $(this).prop('id').replace('_enable', '');
                var $dateInput = $('input#' + dateType);

                if($(this).prop('checked')) {
                    $dateInput.prop('disabled', false);
                    $('#courseEndDate').datepicker('show');
                } else {
                    $dateInput.prop('disabled', true);
                    $('#courseEndDate').datepicker('hide');
                }
            });

            $('#course_enableStartDate').change(function() {
                var dateType = $(this).prop('id').replace('_enable', '');
                var $dateInput = $('input#' + dateType);

                if($(this).prop('checked')) {
                    $dateInput.prop('disabled', false);
                    $('#courseStartDate').datepicker('show');
                } else {
                    $dateInput.prop('disabled', true);
                    $('#courseStartDate').datepicker('hide');
                }
            });

            $('#courseRegEndDate').datepicker({
                format: 'dd-mm-yyyy',
                pickerPosition: 'bottom-right',
                language: '{{ $language }}',
                autoclose: true
            });

            $('#courseRegStartDate').datepicker({
                format: 'dd-mm-yyyy',
                pickerPosition: 'bottom-right',
                language: '{{ $language }}',
                autoclose: true
            });

            $('#course_enableRegEndDate').change(function() {
                var dateType = $(this).prop('id').replace('_enable', '');
                var $dateInput = $('input#' + dateType);

                if($(this).prop('checked')) {
                    $dateInput.prop('disabled', false);
                    $('#courseRegEndDate').datepicker('show');
                } else {
                    $dateInput.prop('disabled', true);
                    $('#courseRegEndDate').datepicker('hide');
                }
            });

            $('#course_enableRegStartDate').change(function() {
                var dateType = $(this).prop('id').replace('_enable', '');
                var $dateInput = $('input#' + dateType);

                if($(this).prop('checked')) {
                    $dateInput.prop('disabled', false);
                    $('#courseRegStartDate').datepicker('show');
                } else {
                    $dateInput.prop('disabled', true);
                    $('#courseRegStartDate').datepicker('hide');
                }
            });

            var hasImported = {{ $course_has_import? 'true' : 'false' }};
            $('.importCourse').on('click', function (e) {
                e.preventDefault();
                bootbox.dialog({
                    title: '{{ trans('langImportCourse') }}',
                    message: "<form action='{{ $_SERVER['SCRIPT_NAME'] }}' method='post' id='import_course_form'>" +
                                "<select class='form-select' id='course_id' name='import_course_id'>" +
                                    {!! $courses_options !!}
                                + "</select>" +
                             "</form>",
                    buttons: {
                        cancel: {
                            label: '{{ trans('langCancel') }}',
                            className: 'cancelAdminBtn'
                        },
                        success: {
                            label: '{{ trans('langImport') }}',
                            className: 'submitAdminBtn',
                            callback: function () {
                                if (hasImported) {
                                    bootbox.confirm({
                                        message: "<h4>{{ trans('langCourseHasAlreadyImported') }}</h4><span class='help-block'>{{ trans('langCourseHasAlreadyImportedExplain') }}</span>",
                                        buttons: {
                                            confirm: {
                                                label: '{{ trans('langImport') }}',
                                                className: 'submitAdminBtn'
                                            },
                                            cancel: {
                                                label: '{{ trans('langCancel') }}',
                                                className: 'cancelAdminBtn'
                                            }
                                        },
                                        callback: function(result) {
                                            if (result) {
                                                $('#import_course_form').attr('action', 'import_course.php?course={{ $course_code }}&do_fetch=1');
                                                $('#import_course_form').submit();
                                            }
                                        }
                                    });
                                    return false;
                                } else {
                                    $('#import_course_form').attr('action', 'import_course.php?course={{ $course_code }}&do_fetch=1');
                                    $('#import_course_form').submit();
                                }
                            }
                        }
                    }
                });
            });

            // Print Header Images functionality
            $('#loadPrintHeaderImages').click(function () {
                loadPrintImages('header');
            });

            // Print Footer Images functionality
            $('#loadPrintFooterImages').click(function () {
                loadPrintImages('footer');
            });

            // Delete Print Header Image
            $('#deletePrintHeaderImage').click(function () {
                $('#choose_print_header_from_list').val('0');
                $('#printHeaderImagePreview').remove();
                $('#selectedPrintHeaderImage').html('');
            });

            // Delete Print Footer Image
            $('#deletePrintFooterImage').click(function () {
                $('#choose_print_footer_from_list').val('0');
                $('#printFooterImagePreview').remove();
                $('#selectedPrintFooterImage').html('');
            });
        });

    </script>
@endpush

@section('content')

    <div class='{{ $container }} module-container py-lg-0'>
        <div class="course-wrapper d-lg-flex align-items-lg-strech w-100">
            <aside class='aside-sidebar'>@include('layouts.partials.left_menu')</aside>
            <main id="main" class="col-12 main-maincontent col_maincontent_active">

                <div class="row">


                    @include('layouts.common.breadcrumbs', ['breadcrumbs' => $breadcrumbs])

                    <div class="offcanvas offcanvas-start d-lg-none" tabindex="-1" id="collapseTools">
                        <div class="offcanvas-header">
                            <button type="button" class="btn-close text-reset" data-bs-dismiss="offcanvas"
                                    aria-label="{{ trans('langClose') }}"></button>
                        </div>
                        <div class="offcanvas-body">
                            @include('layouts.partials.sidebar',['is_editor' => $is_editor])
                        </div>
                    </div>


                    @include('layouts.partials.legend_view')

                    <div id='operations_container'>
                        {!! $action_bar !!}
                    </div>

                    @include('layouts.partials.show_alert')

                    <div class='d-lg-flex gap-4 mt-4'>
                        <div class='flex-grow-1'>
                            <div class='form-wrapper form-edit rounded'>

                                <form class='form-horizontal' role='form' method='post' action="{{ $_SERVER['SCRIPT_NAME'] }}?course_code={{ $course_code }}"
                                      onsubmit='return validateNodePickerForm();'>
                                    <fieldset>
                                        <legend class='mb-0' aria-label="{{ trans('langForm') }}"></legend>
                                        <div class='form-group'>
                                            <label for='fcode'
                                                   class='col-sm-12 control-label-notes'>{{ trans('langCode') }}</label>
                                            <div class='col-sm-12'>
                                                <input type='text' class='form-control' name='fcode' id='fcode'
                                                       value='{{ $public_code }}'>
                                            </div>
                                        </div>

                                        <div class='form-group mt-4'>
                                            <label for='title' class='col-sm-12 control-label-notes'>{{ trans('langCourseTitle') }}</label>
                                            <div class='col-sm-12'>
                                                <input type='text' class='form-control' name='title' id='title' value='{{ $title }}'>
                                            </div>
                                        </div>

                                        <div class='form-group mt-4'>
                                            <label for='teacher_name'
                                                   class='col-sm-12 control-label-notes'>{{ trans('langTeachers') }}</label>
                                            <div class='col-sm-12'>
                                                <input type='text' class='form-control' name='teacher_name' id='teacher_name' value='{{ $teacher_name }} '>
                                            </div>
                                        </div>

                                        <div class='form-group mt-4'>
                                            <label for='dialog-set-value' class='col-sm-12 control-label-notes'>{{ trans('langFaculty') }}</label>
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

                                        @if(get_config('show_collaboration') && !get_config('show_always_collaboration'))
                                            <div class='form-group mt-4'>
                                                <div class='col-sm-12'>
                                                    <label class='control-label-notes' for='type_collab'>{!! trans('langWhatTypeOfCourse') !!}</label>
                                                    <div class='checkbox'>
                                                        <label class='label-container' aria-label="{{ trans('langSelect') }}">
                                                            <input type='checkbox' id='type_collab' name='is_type_collaborative' {{ $is_type_collaborative }}>
                                                            <span class='checkmark'></span>
                                                            {!! trans('langTypeCollaboration') !!}
                                                        </label>
                                                    </div>
                                                </div>
                                            </div>
                                        @endif

                                        <div class='form-group mt-4'>
                                            <div class='col-sm-12 control-label-notes mb-2'>
                                                {{ trans('langCourseFormat') }}
                                            </div>
                                            <div class='col-sm-12'>
                                                <div class="radio mb-2">
                                                    <label>
                                                        <input type='radio' name='view_type' value='simple' id='simple' {{ $course_type_simple }}>
                                                        {{ trans('langCourseSimpleFormat') }}
                                                    </label>
                                                </div>
                                                <div class="radio mb-2">
                                                    <label>
                                                        <input type='radio' name='view_type' value='units' id='units' {{ $course_type_units }}>
                                                        {{ trans('langWithCourseUnits') }}
                                                    </label>
                                                </div>

                                                <div class="radio mb-2 @if(get_config('show_collaboration') and get_config('show_always_collaboration')) d-none @endif" id="radio_activity">
                                                    <label>
                                                        <input type='radio' name='view_type' value='activity' id='activity' {{ $course_type_activity }}>
                                                        {{ trans('langCourseActivityFormat') }}
                                                    </label>
                                                </div>
                                                <div class="radio mb-2 @if(get_config('show_collaboration') and get_config('show_always_collaboration')) d-none @endif" id="radio_wall">
                                                    <label>
                                                        <input type='radio' name='view_type' value='wall' id='wall' {{ $course_type_wall }}>
                                                        {{ trans('langCourseWallFormat') }}
                                                    </label>
                                                </div>
                                                <div class="radio mb-2 @if(get_config('show_collaboration') and get_config('show_always_collaboration')) d-none @endif" id="radio_flippedclassroom">
                                                    <label>
                                                        <input type='radio' name='view_type' value='flippedclassroom' id='flippedclassroom' {{ $course_type_flipped_classroom }}>
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
                                                        <input type='radio' name='view_type' value='sessions'
                                                               id='sessions' {{ $course_type_sessions }}>
                                                        {{ trans('langSessionType') }}
                                                    </label>
                                                </div>
                                            </div>
                                            @if(get_config('show_collaboration') and !get_config('show_always_collaboration'))
                                                <input type="hidden" id="radio_collaborative_helper" value="{{ $is_type_collaborative }}">
                                            @endif
                                        </div>

                                        @if (isset($isOpenCourseCertified))
                                            <input type='hidden' name='course_license' value='{{ getIndirectReference($course_license) }}'>
                                        @endif

                                        <div class='form-group mt-4'>
                                            <div class='col-sm-12 control-label-notes mb-2'>{{ trans('langConfidentiality') }}</div>
                                            <div class='col-sm-12'>

                                                <div class='radio mb-3'>
                                                    <label>
                                                        <input class='input-StatusCourse' id='courseopen' type='radio' name='formvisible' value='2' {{ $course_open }}>
                                                        <label for="courseopen" aria-label="{{ trans('langOpenCourse') }}">{!! course_access_icon(COURSE_OPEN) !!}</label>
                                                        {{ trans('langOpenCourse') }}
                                                    </label>
                                                    <div class='help-block'>{{ trans('langPublic') }}</div>
                                                </div>


                                                <div class='radio mb-3'>
                                                    <label>
                                                        <input class='input-StatusCourse' id='coursewithregistration' type='radio' name='formvisible' value='1' {{ $course_registration }}>
                                                        <label for="coursewithregistration" aria-label="{{ trans('langTypeRegistration') }}">{!! course_access_icon(COURSE_REGISTRATION) !!}</label>
                                                        {{ trans('langTypeRegistration') }}
                                                    </label>
                                                    <div class='help-block'>{{ trans('langPrivOpen') }}</div>
                                                </div>


                                                <div class='radio mb-3'>
                                                    <label>
                                                        <input class='input-StatusCourse' id='courseclose' type='radio' name='formvisible' value='0' {{ $course_closed }} {!! $disable_visibility !!}>
                                                        <label for="courseclose" aria-label="{{ trans('langClosedCourse') }}">{!! course_access_icon(COURSE_CLOSED) !!}</label>
                                                        {{ trans('langClosedCourse') }}
                                                    </label>
                                                    <div class='help-block'>
                                                        {{ trans('langClosedCourseShort') }}
                                                    </div>
                                                </div>


                                                <div class='radio'>
                                                    <label>
                                                        <input class='input-StatusCourse' id='courseinactive' type='radio' name='formvisible' value='3' {{ $course_inactive }} {!! $disable_visibility !!}>
                                                        <label for="courseinactive" aria-label="{{ trans('langInactiveCourse') }}">
                                                            {!! course_access_icon(COURSE_INACTIVE) !!}
                                                        </label>
                                                        {!! trans('langInactiveCourse') !!}
                                                    </label>
                                                    <div class='help-block'>
                                                        {{ trans('langCourseInactive') }}
                                                    </div>
                                                </div>

                                            </div>
                                        </div>

                                        <div class='form-group mt-3' id="course_password_panel">
                                            <div class='checkbox mb-2 mt-4'>
                                                <label class='label-container' aria-label="{{ trans('langSelect') }}">
                                                    <input type='checkbox' id='faculty_users_registration' name='faculty_users_registration' @if (setting_get(SETTING_FACULTY_USERS_REGISTRATION, $course_id) == 1) checked @endif>
                                                    <span class='checkmark'></span>{{ trans('langFacultyUsersRegistrationLegend') }}
                                                </label>
                                            </div>
                                            <label for='coursepassword' class='col-sm-12 control-label-notes'>{{ trans('langOptPassword') }}</label>
                                            <div class='col-sm-12'>
                                                <input class='form-control' id='coursepassword' type='text' name='password' value='{{ $password }}' autocomplete='off'>
                                            </div>
                                            <div class='col-sm-12 text-center padding-thin'>
                                                <span id='result'></span>
                                            </div>
                                        </div>

                                        <div class='form-group mt-3' id="course_registration_date">
                                            <div class='row input-append date form-group mt-4'>
                                                <label for='courseRegStartDate' class='col-12 control-label-notes mb-1'>
                                                    {{ trans('langCourseRegStartDate') }}
                                                </label>
                                                <div class='col-12'>
                                                    <div class='input-group'>
                                                        <span class='input-group-addon'>
                                                            <label class='label-container' aria-label='{{ trans('langSelect') }}'>
                                                                <input class='mt-0' type='checkbox' id='course_enableRegStartDate' name='course_enableRegStartDate' value='1' @if ($course_enableRegStartDate) checked @endif>
                                                                <span class='checkmark'></span>
                                                            </label>
                                                        </span>
                                                        <span class='add-on2'><i class='fa-regular fa-calendar Neutral-600-cl'></i></span>
                                                        <input class='form-control mt-0' name='courseRegStartDate' id='courseRegStartDate' type='text' value='{{ $courseRegStartDate }}' @if (!$course_enableRegStartDate) disabled @endif>
                                                    </div>
                                                    <span class='help-block'><i class='fa fa-share fa-rotate-270 p-2'></i>
                                                        {{ trans('langCourseRegStartDateLegend') }}
                                                    </span>
                                                </div>
                                            </div>

                                            <div class='row input-append date form-group mt-4'>
                                                <label for='courseRegEndDate' class='col-12 control-label-notes mb-1'>
                                                    {{ trans('langCourseRegEndDate') }}
                                                </label>
                                                <div class='col-12'>
                                                    <div class='input-group'>
                                                        <span class='input-group-addon'>
                                                            <label class='label-container' aria-label='{{ trans('langSelect') }}'>
                                                                <input class='mt-0' type='checkbox' id='course_enableRegEndDate' name='course_enableRegEndDate' value='1' @if ($course_enableRegEndDate) checked @endif>
                                                                <span class='checkmark'></span>
                                                            </label>
                                                        </span>
                                                        <span class='add-on2'><i class='fa-regular fa-calendar Neutral-600-cl'></i></span>
                                                        <input class='form-control mt-0' name='courseRegEndDate' id='courseRegEndDate' type='text' value='{{ $courseRegEndDate }}' @if (!$course_enableRegEndDate) disabled @endif>
                                                    </div>
                                                    <span class='help-block'>
                                                        <i class='fa fa-share fa-rotate-270 p-2'></i>{{ trans('langCourseRegEndDateLegend') }}
                                                    </span>
                                                </div>
                                            </div>
                                        </div>

                                        <div class='form-group mt-4'>
                                            <label for='course_language_id'
                                                   class='col-sm-12 control-label-notes'>{{ trans('langLanguage') }}</label>
                                            <div class='col-sm-12'>
                                                {!! $lang_select_options !!}
                                            </div>
                                        </div>

                                        <div class='form-group mt-4'>
                                            <div class='col-sm-12 control-label-notes mb-2'>{{ trans('langOpenCoursesLicense') }}</div>
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
                                                <label class='mb-0' for='course_license_id' aria-label="{{ trans('langOpenCoursesLicense') }}"></label>
                                                {!! $license_selection !!}
                                            </div>
                                        </div>

                                        <div class='row input-append date form-group mt-4'>
                                            <label for='courseStartDate' class='col-12 control-label-notes mb-1'>
                                                {{ trans('langStart') }} {{ trans('langsOfCourse') }}
                                            </label>
                                            <div class='col-12'>
                                                <div class='input-group'>
                                                    <span class='input-group-addon'>
                                                        <label class='label-container' aria-label='{{ trans('langSelect') }}'>
                                                             <input class='mt-0' type='checkbox' id='course_enableStartDate' name='course_enableStartDate' value='1' @if ($course_enableStartDate) checked @endif>
                                                             <span class='checkmark'></span>
                                                        </label>
                                                    </span>
                                                    <span class='add-on2'><i class='fa-regular fa-calendar Neutral-600-cl'></i></span>
                                                    <input class='form-control mt-0' name='courseStartDate' id='courseStartDate' type='text' value='{{ $courseStartDate }}' @if (!$course_enableStartDate) disabled @endif>
                                                </div>
                                                <span class='help-block'><i class='fa fa-share fa-rotate-270 p-2'></i>
                                                    {{ trans('langCourseStartDateLegend') }}
                                                </span>
                                            </div>
                                        </div>

                                        <div class='row input-append date form-group mt-4'>
                                            <label for='courseEndDate' class='col-12 control-label-notes mb-1'>
                                                {{ trans('langFinish') }} {{ trans('langsOfCourse') }}
                                            </label>
                                            <div class='col-12'>
                                                <div class='input-group'>
                                                    <span class='input-group-addon'>
                                                        <label class='label-container' aria-label='{{ trans('langSelect') }}'>
                                                             <input class='mt-0' type='checkbox' id='course_enableEndDate' name='course_enableEndDate' value='1' @if ($course_enableEndDate) checked @endif>
                                                             <span class='checkmark'></span>
                                                        </label>
                                                    </span>
                                                    <span class='add-on2'><i class='fa-regular fa-calendar Neutral-600-cl'></i></span>
                                                    <input class='form-control mt-0' name='courseEndDate' id='courseEndDate' type='text' value='{{ $courseEndDate }}' @if (!$course_enableEndDate) disabled @endif>
                                                </div>
                                                <span class='help-block'><i class='fa fa-share fa-rotate-270 p-2'></i>
                                                    {{ trans('langCourseEndDateLegend') }}
                                                </span>
                                            </div>
                                        </div>

                                        <h2 class='text-heading-h3 mt-4'>
                                            {{ trans('langOtherOptions') }}
                                        </h2>

                                        @if (get_config('offline_course'))
                                            <div class='checkbox mb-2 mt-2'>
                                                <label class='label-container' aria-label="{{ trans('langSelect') }}">
                                                    <input type='checkbox' name='enable_offline_course' @if (setting_get(SETTING_OFFLINE_COURSE, $course_id) == 1) checked @endif>
                                                    <span class='checkmark'></span>{{ trans('langDownloadCourse') }}
                                                    <span class='fa-solid fa-circle-info ps-1' data-bs-toggle='tooltip' data-bs-placement='right' title='{{ trans('langCourseOfflineLegend') }}' style='margin-top: 5px;'></span>
                                                </label>
                                            </div>
                                        @endif

                                        @if (course_status($course_id) == COURSE_CLOSED)
                                            <div class='checkbox mb-2 mt-2'>
                                                <label class='label-container' aria-label="{{ trans('langSelect') }}">
                                                    <input type='checkbox' name='disable_log_course_user_requests' @unless (!setting_get(SETTING_COURSE_USER_REQUESTS_DISABLE, $course_id)) checked @endunless >
                                                    <span class='checkmark'></span>{{ trans('langCourseUserRequests') }}
                                                </label>
                                            </div>
                                        @endif

                                        <div class='checkbox mb-2 mt-2'>
                                            <label class='label-container' aria-label="{{ trans('langSelect') }}">
                                                <input type='checkbox' name='enable_access_users_list' @if (setting_get(SETTING_USERS_LIST_ACCESS, $course_id) == 1) checked @endif >
                                                <span class='checkmark'></span>{{ trans('langUsersListAccess') }}
                                                <span class='fa-solid fa-circle-info ps-1' data-bs-toggle='tooltip' data-bs-placement='right' title='{{ trans('langUsersListAccessInfo') }}' style='margin-top: 5px;'></span>
                                            </label>
                                        </div>

                                        @if (is_sharing_allowed($course_id) && (course_status($course_id) == COURSE_OPEN))
                                            <div class='checkbox mb-2 mt-2'>
                                                <label class='label-container' aria-label="{{ trans('langSelect') }}">
                                                    <input type='checkbox' name='s_radio' @if (setting_get(SETTING_COURSE_SHARING_ENABLE, $course_id) == 1) checked @endif >
                                                    <span class='checkmark'></span>{{ trans('langCourseSharing') }}
                                                </label>
                                            </div>
                                        @endif

                                        <div class='checkbox mb-2 mt-2'>
                                            <label class='label-container' aria-label="{{ trans('langSelect') }}">
                                                <input type='checkbox' name='f_radio' @if (setting_get(SETTING_COURSE_FORUM_NOTIFICATIONS, $course_id) == 1) checked @endif >
                                                <span class='checkmark'></span>{{ trans('langActivateForumNotifications') }}
                                            </label>
                                        </div>

                                        <div class='checkbox mb-2 mt-2'>
                                            <label class='label-container' aria-label="{{ trans('langSelect') }}">
                                                <input type='checkbox' name='r_radio' @if (setting_get(SETTING_COURSE_RATING_ENABLE, $course_id) == 1) checked @endif >
                                                <span class='checkmark'></span>{{ trans('langCourseRating') }}
                                            </label>
                                        </div>

                                        @if (course_status($course_id) == COURSE_OPEN)
                                            <div class='checkbox mb-2 mt-2'>
                                                <label class='label-container' aria-label="{{ trans('langSelect') }}">
                                                    <input type='checkbox' name='ran_radio' @if (setting_get(SETTING_COURSE_ANONYMOUS_RATING_ENABLE, $course_id) == 1) checked @endif >
                                                    <span class='checkmark'></span>{{ trans('langCourseAnonymousRating') }}
                                                </label>
                                            </div>
                                        @endif

                                        <div class='checkbox mb-2 mt-2'>
                                            <label class='label-container' aria-label="{{ trans('langSelect') }}">
                                                <input type='checkbox' name='c_radio' @if (setting_get(SETTING_COURSE_COMMENT_ENABLE, $course_id) == 1) checked @endif >
                                                <span class='checkmark'></span>{{ trans('langCourseCommenting') }}
                                            </label>
                                        </div>

                                        <div class='checkbox mb-2 mt-2'>
                                            <label class='label-container' aria-label="{{ trans('langSelect') }}">
                                                <input type='checkbox' name='h5p_radio' @if (setting_get(SETTING_COURSE_H5P_USERS_UPLOADING_ENABLE, $course_id) == 1) checked @endif >
                                                <span class='checkmark'></span>{{ trans('langH5PUploadingEnabled') }}
                                            </label>
                                        </div>

                                        <div class='checkbox mb-2 mt-2'>
                                            <label class='label-container' aria-label="{{ trans('langSelect') }}">
                                                <input type='checkbox' name='ar_radio' @if (setting_get(SETTING_COURSE_ABUSE_REPORT_ENABLE, $course_id) == 1) checked @endif>
                                                <span class='checkmark'></span>{{ trans('langAbuseReport') }}
                                            </label>
                                        </div>

                                        @if (get_config('enable_docs_public_write'))
                                            <div class='checkbox mb-2 mt-2'>
                                                <label class='label-container' aria-label="{{ trans('langSelect') }}">
                                                    <input type='checkbox' name='docs_public_write' @if (setting_get(SETTING_DOCUMENTS_PUBLIC_WRITE)) checked @endif>
                                                    <span class='checkmark'></span>{{ trans('langPublicDocumentManagementExplanation') }}
                                                </label>
                                            </div>
                                        @endif

                                        <div class='checkbox mb-2 mt-2'>
                                            <label class='label-container' aria-label="{{ trans('langSelect') }}">
                                                <input type='checkbox' name='enable_agenda_announcement_widget_courseCompletion' @if (setting_get(SETTING_AGENDA_ANNOUNCEMENT_COURSE_COMPLETION, $course_id) == 1) checked @endif >
                                                <span class='checkmark'></span>{{ trans('langDisplayRightContentInCPage') }}
                                            </label>
                                        </div>

                                        <div class='form-group mt-4'>
                                            <h2 class='text-heading-h3'>
                                                {{ trans('langCoursePrintSetting') }}
                                            </h2>
                                            <div class='col-sm-12 control-label-notes mt-3'>
                                                {{ trans('langCoursePrintHeaderImage') }}
                                                <span class="ms-2"><small>{{ trans('langReportImageNotFound') }}</small></span>
                                                @if($print_header_image_url)
                                                    <div class="mt-2" id="printHeaderImagePreview">
                                                        <img src="{{ $print_header_image_url }}"
                                                             alt="Print Header Image"
                                                             style="max-width: 200px; max-height: 100px;"
                                                             class="img-thumbnail">
                                                    </div>
                                                @endif
                                                <div class="d-flex gap-2 align-items-center mt-2">
                                                    <button type="button" class="btn btn-secondary"
                                                            id="loadPrintHeaderImages" data-bs-toggle="modal"
                                                            data-bs-target="#PrintHeaderImagesModal">
                                                        <i class="fa fa-images"></i> {{ trans('langSelectFromGallery') }}
                                                    </button>
                                                    <button type="button" class="btn deleteAdminBtn btn-sm"
                                                            id="deletePrintHeaderImage">
                                                        <i class="fa fa-trash"></i>
                                                    </button>
                                                </div>
                                                <div class="d-flex gap-2 align-items-center mt-2">
                                                    <label class="col-sm-2" for="header_image_alignment">{{ trans('langAlignment') }}</label>
                                                    <select name="header_image_alignment" id="header_image_alignment" class="form-select">
                                                        <option value="0" {{ setting_get(SETTING_COURSE_IMAGE_PRINT_HEADER_ALIGNMENT, $course_id) == '0' ? 'selected' : '' }}>{{ trans('langAlignLeft') }}</option>
                                                        <option value="1" {{ setting_get(SETTING_COURSE_IMAGE_PRINT_HEADER_ALIGNMENT, $course_id) == '1' ? 'selected' : '' }}>{{ trans('langAlignCenter') }}</option>
                                                        <option value="2" {{ setting_get(SETTING_COURSE_IMAGE_PRINT_HEADER_ALIGNMENT, $course_id) == '2' ? 'selected' : '' }}>{{ trans('langAlignRight') }}</option>
                                                    </select>
                                                </div>
                                                <div class="d-flex gap-2 align-items-center mt-2">
                                                    <label class="col-sm-2" for="header_image_width">
                                                        {{ trans('langHeight') }}(mm)
                                                    </label>
                                                    <input type="number" name="header_image_width"
                                                           id="header_image_width"
                                                           value="{{ setting_get(SETTING_COURSE_IMAGE_PRINT_HEADER_WIDTH, $course_id) }}"
                                                           placeholder="Width (px)" class="form-control">
                                                </div>
                                                <input type="hidden" name="choose_print_header_from_list"
                                                       id="choose_print_header_from_list"
                                                       value="{{ $print_header_image_url ? setting_get(SETTING_COURSE_IMAGE_PRINT_HEADER, $course_id) : '' }}">
                                                <div id="selectedPrintHeaderImage" class="mt-2 text-muted"></div>
                                            </div>

                                            <div class='col-sm-12 control-label-notes mt-3'>
                                                {{ trans('langCoursePrintFooterImage') }}
                                                <span class="ms-2"><small>{{ trans('langReportImageNotFound') }}</small></span>
                                                @if($print_footer_image_url)
                                                    <div class="mt-2" id="printFooterImagePreview">
                                                        <img src="{{ $print_footer_image_url }}"
                                                             alt="Print Footer Image"
                                                             style="max-width: 200px; max-height: 100px;"
                                                             class="img-thumbnail">
                                                    </div>
                                                @endif
                                                <div class="d-flex gap-2 align-items-center mt-2">
                                                    <button type="button" class="btn btn-secondary"
                                                            id="loadPrintFooterImages" data-bs-toggle="modal"
                                                            data-bs-target="#PrintFooterImagesModal">
                                                        <i class="fa fa-images"></i> {{ trans('langSelectFromGallery') }}
                                                    </button>
                                                    <button type="button" class="btn deleteAdminBtn btn-sm"
                                                            id="deletePrintFooterImage">
                                                        <i class="fa fa-trash"></i>
                                                    </button>
                                                </div>
                                                <div class="d-flex gap-2 align-items-center mt-2">
                                                    <label class="col-sm-2" for="header_image_alignment">
                                                        {{ trans('langAlignment') }}
                                                    </label>
                                                    <select name="footer_image_alignment" id="footer_image_alignment" class="form-select">
                                                        <option value="0" {{ setting_get(SETTING_COURSE_IMAGE_PRINT_FOOTER_ALIGNMENT, $course_id) == '0' ? 'selected' : '' }}>{{ trans('langAlignLeft') }}</option>
                                                        <option value="1" {{ setting_get(SETTING_COURSE_IMAGE_PRINT_FOOTER_ALIGNMENT, $course_id) == '1' ? 'selected' : '' }}>{{ trans('langAlignCenter') }}</option>
                                                        <option value="2" {{ setting_get(SETTING_COURSE_IMAGE_PRINT_FOOTER_ALIGNMENT, $course_id) == '2' ? 'selected' : '' }}>{{ trans('langAlignRight') }}</option>
                                                    </select>
                                                </div>
                                                <div class="d-flex gap-2 align-items-center mt-2">
                                                    <label class="col-sm-2"
                                                           for="header_image_width">{{ trans('langHeight') }}
                                                        (mm)</label>
                                                    <input type="number" name="footer_image_width"
                                                           id="footer_image_width"
                                                           value="{{ setting_get(SETTING_COURSE_IMAGE_PRINT_FOOTER_WIDTH, $course_id) }}"
                                                           placeholder="Width (px)" class="form-control">
                                                </div>
                                                <input type="hidden" name="choose_print_footer_from_list"
                                                       id="choose_print_footer_from_list"
                                                       value="{{ $print_footer_image_url ? setting_get(SETTING_COURSE_IMAGE_PRINT_FOOTER, $course_id) : '' }}">
                                                <div id="selectedPrintFooterImage" class="mt-2 text-muted"></div>
                                            </div>
                                        </div>

                                        {!! showSecondFactorChallenge() !!}

                                        <div class='form-group mt-5 mb-1 d-flex justify-content-end align-items-center gap-2 flex-wrap'>
                                            <input class='btn submitAdminBtn' type='submit' name='submit' value='{{ trans('langSubmit') }}'>
                                            <a href='{{ $urlServer }}courses/{{ $course_code }}/' class='btn cancelAdminBtn text-nowrap'>{{ trans('langCancel') }}</a>
                                        </div>
                                    </fieldset>
                                    {!! generate_csrf_token_form_field() !!}
                                </form>
                            </div>
                        </div>
                        <div class='d-none d-lg-block'>
                            <img class='form-image-modules' src='{!! get_form_image() !!}' alt="{{ trans('langImgFormsDes') }}">
                        </div>
                    </div>
                </div>
            </main>
        </div>
    </div>

    <!-- Print Images Modal -->
    <div class="modal fade" id="PrintHeaderImagesModal" tabindex="-1" aria-labelledby="PrintHeaderImagesModalLabel"
         aria-hidden="true">
        <div class="modal-dialog modal-xl">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title"
                        id="PrintHeaderImagesModalLabel">{{ trans('langCoursePrintHeaderImage') }}</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <div id="printHeaderImagesContent">
                        <!-- Images will be loaded here via AJAX -->
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Print Footer Images Modal -->
    <div class="modal fade" id="PrintFooterImagesModal" tabindex="-1" aria-labelledby="PrintFooterImagesModalLabel"
         aria-hidden="true">
        <div class="modal-dialog modal-xl">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title"
                        id="PrintFooterImagesModalLabel">{{ trans('langCoursePrintFooterImage') }}</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <div id="printFooterImagesContent">
                        <!-- Images will be loaded here via AJAX -->
                    </div>
                </div>
            </div>
        </div>
    </div>

@endsection
