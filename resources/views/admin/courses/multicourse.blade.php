@extends('layouts.default')

@section('content')

<div class="col-12 main-section">
    <div class='{{ $container }} main-container'>
        <div class="row m-auto">


                    @include('layouts.common.breadcrumbs', ['breadcrumbs' => $breadcrumbs])

                    @include('layouts.partials.legend_view')

                    @if(isset($action_bar))
                        {!! $action_bar !!}
                    @else
                        <div class='mt-4'></div>
                    @endif

                    @include('layouts.partials.show_alert')

                    <div class='col-12 mb-4'>
                        <div class='alert alert-info'><i class='fa-solid fa-circle-info fa-lg'></i><span>{{ trans('langMultiCourseInfo') }}</span></div>
                    </div>

                    <div class='col-lg-6 col-12'>
                        <div class='form-wrapper form-edit border-0 px-0'>
                            <form role='form' class='form-horizontal' method='post' action='{{ $_SERVER['SCRIPT_NAME'] }}' onsubmit="return validateNodePickerForm();">
                                <fieldset>
                                    <legend class='mb-0' aria-label="{{ trans('langForm') }}"></legend>
                                    <div class='form-group'>
                                        <label for="type_course" class='col-sm-12 control-label-notes'>{{ trans('langType') }}</label>
                                        @if(get_config('show_collaboration') and get_config('show_always_collaboration'))
                                            <select id='type_course' name='courseType' class='form-select'>
                                                <option value='1' selected>{{ trans('langTypeCollaboration') }}</option>
                                            </select>
                                        @elseif(get_config('show_collaboration') and !get_config('show_always_collaboration'))
                                            <select id='type_course' name='courseType' class='form-select'>
                                                <option value='0' selected>{{ trans('langTypeCourse') }}</option>
                                                <option value='1'>{{ trans('langTypeCollaboration') }}</option>
                                            </select>
                                        @else
                                            <select id='type_course' name='courseType' class='form-select'>
                                                <option value='0' selected>{{ trans('langCourse') }}</option>
                                            </select>
                                        @endif
                                    </div>

                                    <div class='form-group mt-4'>
                                        <label for='courses_id' class='col-sm-12 control-label-notes'>{{ trans('langMultiCourseTitles') }}</label>
                                        <div class='col-sm-12'>{!! text_area('courses', 20, 80, '', 'id="courses_id"') !!}</div>
                                    </div>

                                    <div class='form-group mt-4'>
                                        <label for='dialog-set-value' class='col-sm-12 control-label-notes'>{{ trans('langFaculty') }}</label>
                                        <div class='col-sm-12'>
                                            {!! $html !!}
                                        </div>
                                    </div>

                                    <div class='form-group mt-4'>
                                        <label for='password' class='col-sm-12 control-label-notes'>{{ trans('langConfidentiality') }}</label>
                                        <div class='col-sm-12'>
                                            <input class='form-control' type='text' name='password' id='password' autocomplete='off'>
                                            <div class='help-block'>({{ trans('langOptPassword') }})</div>
                                        </div>
                                    </div>

                                    <div class='form-group mt-4'>
                                        <div class='col-sm-12 control-label-notes mb-2'>{{ trans('langOpenCourse') }}</div>
                                        <div class='col-sm-12 radio'>
                                            <label>
                                                <input id='courseopen' type='radio' name='formvisible' value='2'
                                                    @if ($default_access === COURSE_OPEN) checked @endif> {{ trans('langPublic') }}
                                            </label>
                                        </div>
                                    </div>

                                    <div class='form-group mt-4'>
                                        <div class='col-sm-12 control-label-notes mb-2'>{{ trans('langRegCourse') }}</div>
                                        <div class='col-sm-12 radio'>
                                            <label>
                                                <input id='coursewithregistration' type='radio' name='formvisible' value='1'
                                                    @if ($default_access === COURSE_REGISTRATION) checked @endif> {{ trans('langPrivOpen') }}
                                            </label>
                                        </div>
                                    </div>

                                    <div class='form-group mt-4'>
                                        <div class='col-sm-12 control-label-notes mb-2'>{{ trans('langClosedCourse') }}</div>
                                        <div class='col-sm-12 radio'>
                                            <label>
                                                <input id='courseclose' type='radio' name='formvisible' value='0'
                                                    @if ($default_access === COURSE_CLOSED) checked @endif> {{ trans('langClosedCourseShort') }}
                                            </label>
                                        </div>
                                    </div>

                                    <div class='form-group mt-4'>
                                        <div class='col-sm-12 control-label-notes mb-2'>{{ trans('langInactiveCourse') }}</div>
                                        <div class='col-sm-12 radio'>
                                            <label>
                                                <input id='courseinactive' type='radio' name='formvisible' value='3'
                                                    @if ($default_access === COURSE_INACTIVE) checked @endif> {{ trans('langCourseInactiveShort') }}
                                            </label>
                                        </div>
                                    </div>

                                    <div class='form-group mt-4'>
                                        <div class='col-sm-12 control-label-notes'>{{ trans('langLanguage') }}</div>
                                        <div class='col-sm-12'>{!! lang_select_options('lang') !!}</div>
                                    </div>

                                    {!! showSecondFactorChallenge() !!}
                                    <div class='form-group mt-5'>
                                        <div class='col-12 d-flex justify-content-end align-items-center gap-2'>
                                            <input class='btn submitAdminBtn' type='submit' name='submit' value='{{ trans('langSubmit') }}'>
                                            <a href='index.php' class='btn cancelAdminBtn'>{{ trans('langCancel') }}</a>
                                        </div>
                                    </div>
                                </fieldset>
                            {!! generate_csrf_token_form_field() !!}
                            </form>
                        </div>
                    </div>

                    <div class='col-lg-6 col-12 d-none d-md-none d-lg-block text-end'>
                    <img class='form-image-modules' src='{!! get_form_image() !!}' alt="{{ trans('langImgFormsDes') }}">
                    </div>

        </div>
    </div>
</div>
@endsection
