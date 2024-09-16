@extends('layouts.default')

@section('content')

<div class="col-12 main-section">
    <div class='{{ $container }} main-container'>
            <div class="row m-auto">

                    @include('layouts.common.breadcrumbs', ['breadcrumbs' => $breadcrumbs])

                    @include('layouts.partials.legend_view')

                    <div class='mt-4'></div>

                    @include('layouts.partials.show_alert') 

                    <div class='col-lg-6 col-12'>
                        <div class='form-wrapper form-edit border-0 px-0'>

                        <form class='form-horizontal' name='authmenu' method='post' action='{{ $_SERVER['SCRIPT_NAME'] }}'>
                            <input type='hidden' name='auth' value='{{ $auth }}'>
                            <fieldset>
                                <legend class='mb-0' aria-label="{{ trans('langForm') }}"></legend>
                                <div class='alert alert-info'><i class='fa-solid fa-circle-info fa-lg'></i><span>{{ trans('langTestAccount') }} ({{ $auth_ids[$auth] }})</span></div>

                                <div class='form-group mt-4'>
                                    <label for='test_username' class='col-sm-12 control-label-notes'>{{ trans('langUsername') }}:</label>
                                    <div class='col-sm-12'>
                                        <input class='form-control' type='text' name='test_username' id='test_username' value='{{ canonicalize_whitespace($test_username) }}' autocomplete='off'>
                                    </div>
                                </div>



                                <div class='form-group mt-4'>
                                    <label for='test_password' class='col-sm-12 control-label-notes'>{{ trans('langPass') }}:</label>
                                    <div class='col-sm-12'>
                                        <input class='form-control' type='password' name='test_password' id='test_password' value='{{ $test_password }}' autocomplete='off'>
                                    </div>
                                </div>



                                <div class='form-group mt-5'>
                                    <div class='col-12 d-flex justify-content-end align-items-center gap-2'>
                                       <input class='btn submitAdminBtn' type='submit' name='submit' value='{{ trans('langConnTest') }}'>
                                        <a class='btn cancelAdminBtn' href='auth.php'>{{ trans('langCancel') }}</a>
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
