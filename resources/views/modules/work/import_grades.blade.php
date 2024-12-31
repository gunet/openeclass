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

                        @include('layouts.partials.show_alert')

                        <div class='d-lg-flex gap-4 mt-4'>
                            <div class='flex-grow-1'>
                                <div class='form-wrapper form-edit rounded'>
                                    <form class='form-horizontal' enctype='multipart/form-data' method='post' action='{{ $_SERVER['SCRIPT_NAME'] }}?course={{ $course_code }}&id={{ $id }}'>
                                        <fieldset>
                                            <legend class='mb-0' aria-label='{{ trans('langForm') }}'></legend>
                                            <div class='form-group'>
                                                <div class='col-sm-12'>
                                                    <p class='form-control-static'>{{ trans('langImportGradesHelp') }}</p>
                                                </div>
                                            </div>
                                            <div class='form-group mt-4'>
                                                <label for='userfile' class='col-sm-12 control-label-notes'>{{ trans('langWorkFile') }}:</label>
                                                <div class='col-sm-12'>
                                                    {!! fileSizeHidenInput() !!}
                                                    <input type='file' id='userfile' name='userfile'>
                                                </div>
                                            </div>
                                            <div class='form-group mt-4'>
                                                <div class='col-12 d-flex justify-content-end'>
                                                    {!! $form_buttons !!}
                                                </div>
                                            </div>
                                        </fieldset>
                                    </form>
                                </div>
                            </div><div class='d-none d-lg-block'>
                                <img class='form-image-modules' src='{!! get_form_image() !!}' alt='{{ trans('langImgFormDes') }}'>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

@endsection
