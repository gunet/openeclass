@extends('layouts.default')

@section('content')

    <div class="col-12 main-section">
        <div class='{{ $container }} main-container'>
            <div class="row m-auto">
                <div class='col-12 mb-4'>
                    <h3 class="d-flex justify-content-center">{{ trans('langUpgDetails') }}</h3>
                </div>
                <div class='col-xl-6 col-lg-8 col-md-8 col-12 ms-auto me-auto'>
                    <div class='form-wrapper form-edit Borders shadow-sm p-3 wrapper-lostpass'>
                        <form class='form-horizontal' role='form' action='{{ $urlServer }}' method='post'>
                            <input type='hidden' name='admin_login' value='true'>
                            <div class='form-group mt-4'>
                                <div class='col-12'>
                                    <label class='form-label'>{{ trans('langUsername') }}</label>
                                    <input class='login-input w-100' placeholder='&#xf007' name='uname' autofocus autocomplete='on'>
                                </div>
                            </div>
                            <div class='form-group mt-4'>
                                <div class='col-12'>
                                    <label class='form-label mt-4'>{{ trans('langPassword') }}&nbsp;(password)</label>
                                    <input class='login-input w-100' placeholder='&#xf084' name='pass' type='password' autocomplete='on'>
                                </div>
                            </div>
                            <div class='form-group mt-4'>
                                <div class='col-12 d-flex justify-content-md-start justify-content-center'>
                                    <button class='btn submitAdminBtn margin-bottom-fat' type='submit' name='submit' value='submit'>{{ trans('langAdminLoginPage') }}</button>
                                </div>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection
