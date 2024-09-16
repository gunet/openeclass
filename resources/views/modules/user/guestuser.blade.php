@push('head_scripts')
    <script type='text/javascript'>
        var lang = {
            'pwStrengthTooShort': '{{ js_escape(trans('langPwStrengthTooShort')) }}',
            'pwStrengthWeak': '{{ js_escape(trans('langPwStrengthWeak')) }}',
            'pwStrengthGood': '{{ js_escape(trans('langPwStrengthGood')) }}',
            'pwStrengthStrong': '{{ js_escape(trans('langPwStrengthStrong')) }}'
        };

        $(document).ready(function() {
            $('#password').keyup(function() {
                $('#result').html(checkStrength($('#password').val()))
            });
        });
        $(function() {
            $('#user_date_expires_at').datetimepicker({
                format: 'dd-mm-yyyy hh:ii',
                pickerPosition: 'bottom-right',
                language: '{{ $language }}',
                minuteStep: 10,
                autoclose: true
            });
        });
    </script>
@endpush

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

                        {!! $action_bar !!}
                        {!! $guest_info_message !!}

                        <div class='d-lg-flex gap-4 mt-4'>
                            <div class='flex-grow-1'>
                                <div class='form-wrapper form-edit rounded'>
                                <form class='form-horizontal' role='form' method='post' action='{{ $_SERVER['SCRIPT_NAME']}}?course={{ $course_code }}'>
                                    <fieldset>
                                        <legend class='mb-0' aria-label="{{ trans('langForm') }}"></legend>
                                        <div class='row form-group'>
                                            <label for='nameID' class='col-12 control-label-notes'>{{ trans('langName') }}</label>
                                            <div class='col-12'>
                                                <input id='nameID' class='form-control h-40px' value='{!! q($guest_info->givenname) !!}' disabled>
                                            </div>
                                        </div>
                                        <div class='row form-group mt-4'>
                                            <label for='surnameID' class='col-12 control-label-notes'>{{ trans('langSurname') }}</label>
                                            <div class='col-12'>
                                                <input id='surnameID' class='form-control h-40px' value='{!! q($guest_info->surname) !!}' disabled>
                                            </div>
                                        </div>
                                        <div class='row form-group mt-4'>
                                            <label for='usernameID' class='col-12 control-label-notes'>{{ trans('langUsername') }}</label>
                                            <div class='col-12'>
                                                <input id='usernameID' class='form-control h-40px' value='{!! q($guest_info->username) !!}' disabled>
                                            </div>
                                        </div>
                                        <div class='row form-group mt-4'>
                                            <label for='password' class='col-12 control-label-notes'>{{ trans('langPass') }} <span class='asterisk Accent-200-cl'>(*)</span></label>
                                            <div class='col-12'>
                                                <input class='form-control' type='text' name='guestpassword' value='' id='password' autocomplete='off' placeholder='{{ trans('langAskGuest') }}'>
                                                <span id='result'></span>
                                            </div>
                                        </div>
                                        <div class='input-append date form-group mt-4'>
                                            <label for='user_date_expires_at' class='col-12 control-label-notes'>{{ trans('langExpirationDate') }} <span class='asterisk Accent-200-cl'>(*)</span></label>
                                            <div class='col-12'>
                                                <div class='input-group'>
                                                    <span class='add-on2 input-group-text h-40px input-border-color border-end-0'><i class='fa-regular fa-calendar Neutral-600-cl'></i></span>
                                                    <input class='form-control mt-0 border-start-0' id='user_date_expires_at' name='user_date_expires_at' type='text' value='{{ $expirationDate->format("d-m-Y H:i") }}'>
                                                </div>
                                            </div>
                                        </div>
                                        <div class='col-12 mt-5 d-flex justify-content-end align-items-center gap-2'>
                                            <input class='btn submitAdminBtn' type='submit' name='submit' value='{{ $submit_label }}'>
                                            <a href='index.php?course={{ $course_code }}' class='btn cancelAdminBtn'>{{ trans('langCancel') }}</a>
                                        </div>
                                    </fieldset>
                                    {!! generate_csrf_token_form_field() !!}
                                </form>
                            </div>
                        </div>
                        <div class='d-none d-lg-block'>
                            <img class='form-image-modules' src='{!! get_form_image() !!}' alt="{{ trans('langImgFormsDes') }}">
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection
