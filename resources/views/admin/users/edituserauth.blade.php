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

            <div class='col-lg-6 col-12'>
                <div class='form-wrapper form-edit border-0 px-0'>

                    <form class='form-horizontal' role='form' method='post' action='{{ $_SERVER['SCRIPT_NAME'] }}'>
                        <fieldset>
                            <legend class='mb-0' aria-label="{{ trans('langForm') }}"></legend>
                            <div class='form-group'>
                                <label for='auth_names_id' class='col-sm-12 control-label-notes'>{{ trans('langEditAuthMethod') }}:</label>
                                <div class='col-sm-12'>
                                    {!! selection($auth_names, 'auth', intval($current_auth), "class='form-control' id='auth_names_id'") !!}
                                </div>
                            </div>
                            {!! showSecondFactorChallenge() !!}
                            <div class='col-12 mt-5 d-flex justify-content-end align-items-center'>
                                <input class='btn submitAdminBtn' type='submit' name='submit_editauth' value='{{ trans('langSubmit') }}'>
                            </div>
                            <input type='hidden' name='u' value='{{ $u }}'>
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
