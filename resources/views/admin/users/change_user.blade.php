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

                    <form class='form-horizontal' role='form' action='{{ $_SERVER['SCRIPT_NAME'] }}' method='post'>

                        <div class='form-group'>
                        <label for='username' class='col-sm-12 control-label-notes'>{{ trans('langUsername') }} <span class='asterisk Accent-200-cl'>(*)</span></label>
                            <div class='col-sm-12'>
                                <input id='username' class='form-control' type='text' name='username' placeholder='{{ trans('langUsername') }}...'>
                            </div>
                        </div>

                        <div class='form-group mt-5'>
                            <div class='col-12 d-flex justify-content-end align-items-center'>
                                {!! showSecondFactorChallenge() !!}
                                <input class='btn submitAdminBtn' type='submit' value='{{ trans('langSubmit') }}'>
                            </div>
                        </div>
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
