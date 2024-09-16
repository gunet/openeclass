@extends('layouts.default')

@section('content')

    <div class="col-12 main-section">
        <div class='{{ $container }} main-container'>
            <div class="row m-auto">
                @include('layouts.common.breadcrumbs', ['breadcrumbs' => $breadcrumbs])

                @include('layouts.partials.legend_view')

                <div class='col-lg-6 col-12'>
                    <div class='form-wrapper form-edit border-0 px-0'>
                        <div class='col-12 control-label-notes'>{{ trans('langActivateConference') }}</div>
                        <form action='{{ $_SERVER['SCRIPT_NAME']}}?list={{$tc_server }}' method='post' class='form-horizontal' role='form'>
                            <div class='form-group mt-3'>
                                <label for='code_to_assign' class='col-12 control-label-notes'>{{ trans('langCourseCode') }} :</label>
                                <div class='col-12'>
                                    <input type='text' class='form-control' name='code_to_assign' id='code_to_assign'>
                                </div>
                            </div>

                            <div class='form-group mt-5'>
                                <div class='col-12 d-flex justify-content-center'>
                                    <input class='btn submitAdminBtn' type='submit' value='{{ trans('langAdd') }}'>
                                    <a class='btn cancelAdminBtn' href='{{ $_SERVER['SCRIPT_NAME'] }}'>{{ trans('langBack') }}</a>
                                </div>
                            </div>
                            <input type='hidden' name='tc_server' value='{{ $tc_server }}'>
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
