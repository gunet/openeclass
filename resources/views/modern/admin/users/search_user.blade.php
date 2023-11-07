@extends('layouts.default')

@push('head_scripts')
    <script type='text/javascript'>
        $(function() {
            $('#id_user_registered_at').datepicker({
                format: 'dd-mm-yyyy',
                pickerPosition: 'bottom-right',
                language: '{{ js_escape($language) }}',
                autoclose: true
            });
            $('#id_user_expires_until').datepicker({
                format: 'dd-mm-yyyy',
                pickerPosition: 'bottom-right',
                language: '{{ js_escape($language) }}',
                autoclose: true
            });
            $('#id_user_last_login').datepicker({
                format: 'dd-mm-yyyy',
                pickerPosition: 'bottom-right',
                language: '{{ js_escape($language) }}',
                autoclose: true
            });
        });
    </script>
@endpush

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

                @if(Session::has('message'))
                <div class='col-12 all-alerts'>
                    <div class="alert {{ Session::get('alert-class', 'alert-info') }} alert-dismissible fade show" role="alert">
                        @php
                            $alert_type = '';
                            if(Session::get('alert-class', 'alert-info') == 'alert-success'){
                                $alert_type = "<i class='fa-solid fa-circle-check fa-lg'></i>";
                            }elseif(Session::get('alert-class', 'alert-info') == 'alert-info'){
                                $alert_type = "<i class='fa-solid fa-circle-info fa-lg'></i>";
                            }elseif(Session::get('alert-class', 'alert-info') == 'alert-warning'){
                                $alert_type = "<i class='fa-solid fa-triangle-exclamation fa-lg'></i>";
                            }else{
                                $alert_type = "<i class='fa-solid fa-circle-xmark fa-lg'></i>";
                            }
                        @endphp

                        @if(is_array(Session::get('message')))
                            @php $messageArray = array(); $messageArray = Session::get('message'); @endphp
                            {!! $alert_type !!}<span>
                            @foreach($messageArray as $message)
                                {!! $message !!}
                            @endforeach</span>
                        @else
                            {!! $alert_type !!}<span>{!! Session::get('message') !!}</span>
                        @endif

                        <button type="button" class="btn-close btn-close-white" data-bs-dismiss="alert" aria-label="Close"></button>
                    </div>
                </div>
                @endif

                <div class='col-lg-6 col-12'>
                    <div class='form-wrapper form-edit border-0 px-0'>

                    <form class='form-horizontal' role='form' action='listusers.php' method='get' name='user_search'>
                    <fieldset>

                        <div class='form-group'>
                            <label for='uname' class='col-sm-12 control-label-notes'>{{ trans('langUsername') }}</label>
                            <div class='col-sm-12'>
                                <input class='form-control' placeholder="{{ trans('langUsername') }}" type='text' name='uname' id='uname' value='{{ $uname }}'>
                            </div>
                        </div>

                        <div class='form-group mt-4'>
                            <label for='fname' class='col-sm-12 control-label-notes'>{{ trans('langName') }}</label>
                            <div class='col-sm-12'>
                                <input class='form-control' placeholder="{{ trans('langName') }}" type='text' name='fname' id='fname' value='{{ $fname }}'>
                            </div>
                        </div>

                        <div class='form-group mt-4'>
                            <label for='lname' class='col-sm-12 control-label-notes'>{{ trans('langSurname') }}</label>
                            <div class='col-sm-12'>
                                <input class='form-control' placeholder="{{ trans('langSurname') }}" type='text' name='lname' id='lname' value='{{ $lname }}'>
                            </div>
                        </div>

                        <div class='form-group mt-4'>
                            <label for='email' class='col-sm-12 control-label-notes'>{{ trans('langEmail') }}</label>
                            <div class='col-sm-12'>
                                <input class='form-control' placeholder="{{ trans('langEmail') }}" type='text' name='email' id='email' value='{{ $email }}'>
                            </div>
                        </div>

                        <div class='form-group mt-4'>
                            <label for='am' class='col-sm-12 control-label-notes'>{{ trans('langAm') }}</label>
                            <div class='col-sm-12'>
                                <input class='form-control' placeholder="{{ trans('langAm') }}" type='text' name='am' id='am' value='{{ $am }}'>
                            </div>
                        </div>

                        <div class='form-group mt-4'>
                            <label class='col-sm-12 control-label-notes'>{{ trans('langUserType') }}</label>
                            <div class='col-sm-12'>
                                {!! selection($usertype_data, 'user_type', 0, 'class="form-select"') !!}
                            </div>
                        </div>

                        <div class="form-group mt-4">
                            <label class='col-sm-12 control-label-notes mb-2'> {{ trans('langAccountStatus') }}</label>
                            <div class="radio mb-2 d-flex justify-content-start align-items-center">
                                <input type='radio' name='search' value='all' id='all-option' checked>
                                {{ trans('langAllUsers') }}
                            </div>
                            <div class="radio mb-2 d-flex justify-content-start align-items-center">
                                <input type='radio' name='search' value='active' id='active-option'>
                                {{ trans('langActiveUsers') }}
                            </div>
                            <div class="radio d-flex justify-content-start align-items-center">
                                <input type='radio' name='search' value='inactive' id='inactive-option'>
                                {{ trans('langInactiveUsers') }}
                            </div>
                        </div>

                        <div class='form-group mt-4'>
                            <label class='col-sm-12 control-label-notes'>{{ trans('langAuthMethod') }}</label>
                            <div class='col-sm-12'>
                                {!! selection($authtype_data, 'auth_type', 0, 'class="form-select"') !!}
                            </div>
                        </div>

                        <div class='form-group mt-4'>
                            <label class='col-sm-12 control-label-notes'>{{ trans('langRegistrationDate') }}</label>
                                <div class='row'>
                                    <div class='col-6'>
                                        {!! selection(['1' => trans('langAfter'), '2' => trans('langBefore')], 'reg_flag', $reg_flag, 'class="form-select"') !!}
                                    </div>
                                    <div class='col-6'>
                                        <input class='form-control' name='user_registered_at' id='id_user_registered_at' type='text' value='{{ $user_registered_at }}' placeholder='{{ trans('langRegistrationDate') }}'>
                                    </div>
                                </div>
                        </div>

                        <div class='form-group mt-4'>
                            <label class='col-sm-12 control-label-notes'>{{ trans('langExpirationDate') }}</label>
                            <div class='col-sm-12'>
                                <input class='form-control' name='user_expires_until' id='id_user_expires_until' type='text' value='{{ $user_expires_until }}' data-date-format='dd-mm-yyyy' placeholder='{{ trans('langUntil') }}'>
                            </div>
                        </div>

                        <div class='form-group mt-4'>
                            <label class='col-sm-12 control-label-notes'>{{ trans('langEmailVerified') }}</label>
                            <div class='col-sm-12'>
                                {!! selection($verified_mail_data, 'verified_mail', $verified_mail, 'class="form-select"') !!}
                            </div>
                        </div>

                        <div class='form-group mt-4'>
                            <label for='dialog-set-value' class='col-sm-12 control-label-notes'>{{ trans('langFaculty') }}:</label>
                            <div class='col-sm-12'>
                                {!! $html !!}
                            </div>
                        </div>

                        <div class='form-group mt-4'>
                            <label for='search_type' class='col-sm-12 control-label-notes'>{{ trans('langSearchFor') }}</label>
                            <div class='col-sm-12'>
                                <select class='form-select' name='search_type' id='search_type'>
                                <option value='exact'>{{ trans('langSearchExact') }}</option>
                                <option value='begin'>{{ trans('langSearchStartsWith') }}</option>
                                <option value='contains' selected>{{ trans('langSearchSubstring') }}</option>
                                </select>
                            </div>
                        </div>

                        <div class='form-group mt-5 d-flex justify-content-end align-items-center'>
                            <input class='btn submitAdminBtn' type='submit' value='{{ trans('langSearch') }}'>
                            <a class='btn cancelAdminBtn ms-2' href='index.php'>{{ trans('langCancel') }}</a>
                        </div>
                    </fieldset>
                    </form>
                </div></div>
                <div class='col-lg-6 col-12 d-none d-md-none d-lg-block text-end'>
                    <img class='form-image-modules' src='{{$urlAppend}}template/modern/img/form-image.png' alt='form-image'>
                </div>
        </div>
    </div>
</div>
@endsection
