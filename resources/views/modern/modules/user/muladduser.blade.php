@extends('layouts.default')

@section('content')

    <div class="col-12 main-section">
        <div class='{{ $container }} module-container py-lg-0'>
            <div class="course-wrapper d-lg-flex align-items-lg-strech w-100">

                @include('layouts.partials.left_menu')

                <div class="col_maincontent_active">
                    <div class="row">
                        @include('layouts.common.breadcrumbs', ['breadcrumbs' => $breadcrumbs])
                        @include('layouts.partials.legend_view')

                        {!! $results !!}

                        <div class='d-lg-flex gap-4 mt-4'>
                            <div class='flex-grow-1'>
                                <div class='alert alert-info text-md-start'>
                                    <i class='fa-solid fa-circle-info fa-lg'></i>
                                    <h4 class='alert-heading'>
                                        {{ trans('langNote') }}:
                                    </h4>
                                    <p>
                                        {{ trans('langAskManyUsers') }}
                                    </p>
                                </div>
                                <div class='form-wrapper form-edit rounded'>
                                    <form method='post' action='{{ $_SERVER['SCRIPT_NAME'] }}?course={{ $course_code }}'>
                                        <legend class='mb-0' aria-label='{{ trans('langForm') }}'></legend>
                                        <div class='form-group'>
                                            <div class='col-sm-12 radio mb-2'>
                                                <label>
                                                    <input type='radio' name='type' value='uname' checked>
                                                    {{ trans('langUsername') }}
                                                </label>
                                            </div>
                                            <div class='col-sm-12 radio'>
                                                <label>
                                                    <input type='radio' name='type' value='am'>
                                                    {{ trans('langAm') }}
                                                </label>
                                            </div>
                                        </div>

                                        <div class='form-group mt-4'>
                                            <textarea aria-label='{{ trans('langTypeOutMessage') }}' class='auth_input w-100' name='user_info' rows='10'></textarea>
                                        </div>
                                        {!! showSecondFactorChallenge() !!}

                                        <div class='col-12 mt-5 d-flex justify-content-end align-items-center'>
                                            <input class='btn submitAdminBtn' type='submit' name='submit' value='{{ trans('langAdd') }}'>
                                        </div>
                                        {!! generate_csrf_token_form_field() !!}
                                    </form>
                                </div>
                            </div>
                            <div class='d-none d-lg-block'>
                                <img class='form-image-modules' src='{!! get_form_image() !!}' alt='{{ trans('langImgFormsDes') }}'>
                            </div>
                        </div>
                    </div>

                </div>
            </div>
        </div>
    </div>
@endsection
