@extends('layouts.default')

@section('content')
    {!! isset($action_bar) ?  $action_bar : '' !!}
    <div class='alert alert-info'>{!! $infoText !!}</div>
        <div class='form-wrapper'>
        <form role='form' class='form-horizontal' method='post' action='{{ $_SERVER['SCRIPT_NAME'] }}'>
            <fieldset>
                {!! $monthsField !!}
                <div class='form-group'>
                    <label class='col-sm-2 control-label'>{{ trans('langMultiDelUserData') }}:</label>
                    <div class='col-sm-9'>
                        <textarea class='auth_input form-control' name='user_names' rows='30'>{{ $usernames }}</textarea>
                    </div>
                </div>
                {!! showSecondFactorChallenge() !!}
                <div class='form-group'>
                    <div class='col-sm-10 col-sm-offset-2'>
                        <input class='btn btn-primary' type='submit' name='submit' value='{{ trans('langSubmit') }}'{!! $confirm !!}>
                        <a href='index.php' class='btn btn-default'>{{ trans('langCancel') }}</a>
                    </div>
                </div>
            </fieldset>
            {!! generate_csrf_token_form_field() !!}
        </form>
    </div>
@endsection