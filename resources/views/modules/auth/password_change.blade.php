
@extends('layouts.default')

@push('head_scripts')
    <script type="text/javascript" src="{{ $urlAppend }}js/pwstrength.js"></script>
    <script type="text/javascript">

        var lang = {
            pwStrengthTooShort: '{!! js_escape(trans('langPwStrengthTooShort')) !!}',
            pwStrengthWeak: '{!! js_escape(trans('langPwStrengthWeak')) !!}',
            pwStrengthGood: '{!! js_escape(trans('langPwStrengthGood')) !!}',
            pwStrengthStrong: '{!! js_escape(trans('langPwStrengthStrong')) !!}',
        };

        $(document).ready(function() {
            $('#password').keyup(function() {
                $('#result').html(checkStrength($('#password').val()))
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

                @include('layouts.partials.show_alert')

                <div class='col-lg-6 col-12'>
                    <div class='form-wrapper form-edit border-0 px-0'>

                        <form role="form" method='post' action='{{ $_SERVER['SCRIPT_NAME'] }}'>
                            <input type='hidden' name='u' value='{{ $_SESSION['uid'] }}'>

                            <div class='form-group{{ $password_form_error ? " has-error" : "" }} mt-4'>
                                <label for='password' class='col-sm-12 control-label-notes'>{!! trans('langNewPass1') !!} <span class='asterisk Accent-200-cl'>(*)</span></label>
                                <div class="col-sm-12">
                                    <input type='password' placeholder="{!! trans('langNewPass1') !!}" class='form-control' size='40' name='password_form' value='{{ $password_form }}' id='password' autocomplete='off'>
                                    &nbsp;<span id='result'></span>
                                    <span class='help-block'>{{ $password_form_error }}</span>
                                </div>
                            </div>

                            <div class='form-group{{ $password_form1_error ? " has-error" : "" }} mt-4'>
                                <label for='new_pass_word' class="col-sm-12 control-label-notes">{!! trans('langNewPass2') !!} <span class='asterisk Accent-200-cl'>(*)</span></label>
                                <div class="col-sm-12">
                                    <input type='password' placeholder="{!! trans('langNewPass2') !!}" class='form-control' size='40' name='password_form1' value='{{ $password_form1 }}' id='new_pass_word' autocomplete='off'>
                                    <span class='help-block'>{{ $password_form1_error }}</span>
                                </div>
                            </div>

                            <div class='form-group mt-4'>
                                <div class='col-12 d-flex justify-content-end align-items-center'>
                                    <input class='btn submitAdminBtn' type='submit' name='submit' value="{{ trans('langModify') }}">
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


