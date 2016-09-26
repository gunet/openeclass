@extends('layouts.default')

@push('head_scripts')
<script type='text/javascript' src='{{ $urlAppend }}js/pwstrength.js'></script>
<script type='text/javascript'>
    var lang =
    {
        pwStrengthTooShort: js_escape({{ trans('langAddPicture') }}),
        pwStrengthWeak: js_escape({{ trans('langAddPicture') }}),
        pwStrengthGood: js_escape({{ trans('langAddPicture') }}),
        pwStrengthStrong: js_escape({{ trans('langAddPicture') }})
    };

    $(document).ready(function() {
        $('#password_form').keyup(function() {
            $('#result').html(checkStrength($('#password_form').val()))
        });
    });

</script>
@endpush

@section('content')

    {!! $action_bar !!}

    <div class='form-wrapper'>
        <form class='form-horizontal' role='form' method='post' action='{{ $passUrl }}'>
            <fieldset>
                <div class='form-group{{ $old_pass_error ? " has-error" : "" }}'>
                    <label for='old_pass' class='col-sm-2 control-label'>{{ trans('langOldPass') }}: </label>
                    <div class='col-sm-8'>
                        <input type='password' class='form-control' id='old_pass' name='old_pass' value='{{ $old_pass }}' autocomplete='off'>
                        <span class='help-block'>{{ $old_pass_error }}</span>
                    </div>
                </div>
                <div class='form-group{{ $password_form_error ? " has-error" : "" }}'>
                    <label for='password_form' class='col-sm-2 control-label'>{{ trans('langNewPass1') }}: </label>
                    <div class='col-sm-8'>
                        <input type='password' class='form-control' id='password_form' name='password_form' value='{{ $password_form }}' autocomplete='off'>
                        <span class='help-block'>{{ $password_form_error }}</span>
                    </div>
                    <div class='col-sm-2 text-center padding-thin'>
                        <span id='result'></span>
                    </div>
                </div>
                <div class='form-group{{ $password_form1_error ? " has-error" : "" }}'>
                    <label for='password_form1' class='col-sm-2 control-label'>{{ trans('langNewPass2') }}: </label>
                    <div class='col-sm-8'>
                        <input type='password' class='form-control' id='password_form1' name='password_form1' value='{{ $password_form1 }}' autocomplete='off'>
                        <span class='help-block'>{{ $password_form1_error }}</span>
                    </div>
                </div>
                {!! showSecondFactorChallenge() !!}
                <div class='form-group'>
                    <div class='col-sm-offset-2 col-sm-8'>
                        <input type='submit' class='btn btn-primary' name='submit' value='{{ trans('langModify') }}'>
                        <a href='display_profile.php' class='btn btn-default'>{{ trans('langCancel') }}</a>
                    </div>
                </div>
            </fieldset>
            {!! generate_csrf_token_form_field() !!}
        </form>
    </div>

@endsection