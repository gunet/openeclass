@extends('layouts.default')

@section('content')

<div class="col-12 main-section">
    <div class='{{ $container }} main-container'>
        <div class="row m-auto">

            @include('layouts.common.breadcrumbs', ['breadcrumbs' => $breadcrumbs])

            @include('layouts.partials.legend_view')

            @include('layouts.partials.show_alert')

            <div class='col-12'>
                <div class='row'>
                    <div class='col-lg-6 col-12'>
                        <div class='form-wrapper form-edit border-0 px-0'>
                            <form class='form-horizontal' role='form' action='{{ $urlAppend }}modules/admin/eduapiconf.php' method='post'>
                                <div class='form-group'>
                                    <label for='eduapi_base_url' class='col-sm-12 control-label-notes'>{{ trans('langEduApiBaseUrl') }}</label>
                                    <div class='col-sm-12'>
                                        <input id='eduapi_base_url' class='form-control' type='text' name='eduapi_base_url' value='{{ $base_url }}' placeholder='https://example.org'>
                                    </div>
                                </div>
                                <div class='form-group mt-4'>
                                    <label for='eduapi_token_url' class='col-sm-12 control-label-notes'>{{ trans('langEduApiTokenUrl') }}</label>
                                    <div class='col-sm-12'>
                                        <input id='eduapi_token_url' class='form-control' type='text' name='eduapi_token_url' value='{{ $token_url }}' placeholder='https://example.org/realms/realm/protocol/openid-connect/token'>
                                    </div>
                                </div>
                                <div class='form-group mt-4'>
                                    <label for='eduapi_client_id' class='col-sm-12 control-label-notes'>{{ trans('langEduApiClientId') }}</label>
                                    <div class='col-sm-12'>
                                        <input id='eduapi_client_id' class='form-control' type='text' name='eduapi_client_id' value='{{ $client_id }}'>
                                    </div>
                                </div>
                                <div class='form-group mt-4'>
                                    <label for='eduapi_client_secret' class='col-sm-12 control-label-notes'>{{ trans('langEduApiClientSecret') }}</label>
                                    <div class='col-sm-12'>
                                        <input id='eduapi_client_secret' class='form-control' type='password' name='eduapi_client_secret' value='{{ $client_secret }}'>
                                    </div>
                                </div>
                                <div class='form-group mt-4'>
                                    <div class='col-sm-offset-2 col-sm-10'>
                                        <div class='checkbox'>
                                            <label class='label-container' aria-label='{{ trans('langSelect') }}'>
                                                <input type='checkbox' name='enabled' value='1' {{ $enabled ? 'checked' : '' }}>
                                                <span class='checkmark'></span>
                                                {{ trans('langActivate') }}
                                            </label>
                                        </div>
                                    </div>
                                </div>
                                <div class='form-group mt-4'>
                                    <div class='d-flex justify-content-end gap-2 flex-wrap'>
                                        <button class='btn submitAdminBtn' type='submit' name='submit'>{{ trans('langSubmit') }}</button>
                                        <button class='btn deleteAdminBtn' type='submit' name='submit' value='clear'>{{ trans('langClearSettings') }}</button>
                                        <a href='extapp.php' class='btn cancelAdminBtn'>{{ trans('langCancel') }}</a>
                                    </div>
                                </div>
                                {!! generate_csrf_token_form_field() !!}
                            </form>
                        </div>
                    </div>
                    <div class='col-lg-6 col-12 d-none d-md-none d-lg-block text-end'>
                        <img class='form-image-modules' src='{{ $form_image }}' alt='{{ trans('langImgFormsDes') }}'>
                    </div>
                </div>
            </div>

        </div>
    </div>
</div>

@endsection
