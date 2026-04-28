@extends('layouts.default')

@section('content')

<main id="main" class="col-12 main-section">
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
                        <div class='alert alert-info'><i class='fa-solid fa-circle-info fa-lg'></i><span>{{ trans('langMultiCourseDeleteInfo') }}</span></div>
                    </div>

                    <div class='col-lg-6 col-12'>
                        <div class='form-wrapper form-edit border-0 px-0'>
                            <form role='form' class='form-horizontal' method='post' action='{{ $_SERVER['SCRIPT_NAME'] }}' onsubmit="return validateNodePickerForm();">
                                <fieldset>
                                    <div class='form-group mt-4'>
                                        <label for='courses_id' class='col-sm-12 control-label-notes'>{{ trans('langMultiCourseCodes') }}</label>
                                        <div class='col-sm-12'>{!! text_area('courses', 20, 80, '', 'id="courses_id"') !!}</div>
                                    </div>

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
</main>
@endsection
