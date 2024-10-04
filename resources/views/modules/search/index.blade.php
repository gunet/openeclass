@extends('layouts.default')

@section('content')
    <div class="col-12 main-section">
        <div class='{{ $container }} module-container py-lg-0'>
            <div class="course-wrapper d-lg-flex align-items-lg-strech w-100">

                <div class="col_maincontent_active @if(!isset($course_code)) search-content @endif">

                    <div class="row">

                        @include('layouts.common.breadcrumbs', ['breadcrumbs' => $breadcrumbs])

                        @include('layouts.partials.legend_view')

                        <div id='operations_container'>
                            {!! $action_bar !!}
                        </div>

                        @include('layouts.partials.show_alert')

                        @if (!get_config('enable_search'))
                            <div class='alert alert-info'>
                                <i class='fa-solid fa-circle-info fa-lg'></i>
                                <span>{{ trans('langSearchDisabled') }}</span>
                            </div>
                        @else
                            <div class='row'>
                                <div class='col-lg-6 col-12'>
                                    <div class='form-wrapper form-edit border-0 px-0'>
                                        <form class='form-horizontal' role='form' method='post' action='{{ $_SERVER['SCRIPT_NAME'] }}'>
                                            <fieldset>
                                                <legend class='mb-0' aria-label='{{ trans('langForm') }}'></legend>
                                                <div class='form-group'>
                                                    <label for='title' class='col-sm-6 control-label-notes'>{{ trans('langTitle') }}</label>
                                                    <div class='col-sm-12'>
                                                        <input id='title' class='form-control' name='search_terms_title' type='text' placeholder='{{ trans('langTitle_Descr') }}'>
                                                    </div>
                                                </div>
                                                <div class='form-group mt-4'>
                                                    <label for='description' class='col-sm-6 control-label-notes'>{{ trans('langDescription') }}</label>
                                                    <div class='col-sm-12'>
                                                        <input id='description' class='form-control' name='search_terms_description' type='text' placeholder='{{ trans('langDescription_Descr') }}'>
                                                    </div>
                                                </div>
                                                <div class='form-group mt-4'>
                                                    <label for='keywords' class='col-sm-6 control-label-notes'>{{ trans('langKeywords') }}</label>
                                                    <div class='col-sm-12'>
                                                        <input id='keywords' class='form-control' name='search_terms_keywords' type='text' placeholder='{{ trans('langKeywords_Descr') }}'>
                                                    </div>
                                                </div>
                                                <div class='form-group mt-4'>
                                                    <label for='teacher' class='col-sm-6 control-label-notes'>{{ trans('langTeacher') }}</label>
                                                    <div class='col-sm-12'>
                                                        <input id='teacher' class='form-control' name='search_terms_instructor' type='text' placeholder='{{ trans('langInstructor_Descr') }}'>
                                                    </div>
                                                </div>
                                                <div class='form-group mt-4'>
                                                    <label for='code' class='col-sm-6 control-label-notes'>{{ trans('langCourseCode') }}</label>
                                                    <div class='col-sm-12'>
                                                        <input id='code' class='form-control' name='search_terms_coursecode' type='text' placeholder='{{ trans('langCourseCode_Descr') }}'>
                                                    </div>
                                                </div>
                                                <div class='col-12 mt-5 d-flex justify-content-end align-items-center gap-2 flex-wrap'>
                                                    <input class='btn submitAdminBtn' type='submit' name='submit' value='{{  trans('langDoSearch') }}'>
                                                    <input class='btn cancelAdminBtn' type='reset' name='reset' value='{{ trans('langNewSearch') }}'>
                                                </div>
                                            </fieldset>
                                        </form>
                                    </div>
                                </div>
                                <div class='col-lg-6 col-12 d-none d-md-none d-lg-block text-end'>
                                    <img class='form-image-modules' src='{!! get_form_image() !!}' alt='{{ trans('langImgFormsDes') }}'>
                                </div>
                            </div>
                        @endif
                    </div>

                </div>
            </div>
        </div>
    </div>
@endsection

