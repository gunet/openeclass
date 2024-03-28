@extends('layouts.default')

@section('content')

    <div class="col-12 main-section">
        <div class='{{ $container }} main-container'>
            <div class="row m-auto">
                <div class='col-lg-6 col-12 ms-auto me-auto'>
                    <div class="card panelCard px-lg-4 py-lg-3 h-100">
                        <div class="card-header border-0 d-flex justify-content-between align-items-center">
                            <h3>{{ trans('langUpgDetails') }} </h3>
                        </div>
                        <div class='card-body'>
                            
                                <form role='form' action='{{ $urlServer }}' method='post'>
                                    <input type='hidden' name='admin_login' value='true'>
                                    <div class='form-group'>
                                        <div class='col-12'>
                                            <label for='Uname' class='form-label'>{{ trans('langUsername') }}</label>
                                            <input id='Uname' class='login-input w-100' placeholder='&#xf007' name='uname' autofocus autocomplete='on'>
                                        </div>
                                    </div>
                                    <div class='form-group mt-4'>
                                        <div class='col-12'>
                                            <label for='Pass' class='form-label mt-4'>{{ trans('langPassword') }}&nbsp;(password)</label>
                                            <input id='Pass' class='login-input w-100' placeholder='&#xf084' name='pass' type='password' autocomplete='on'>
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
    </div>
@endsection
