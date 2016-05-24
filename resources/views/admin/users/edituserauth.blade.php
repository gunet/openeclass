@extends('layouts.default')

@section('content')
    {!! isset($action_bar) ?  $action_bar : '' !!}
    <div class='form-wrapper'>
        <form class='form-horizontal' role='form' method='post' action='{{ $_SERVER['SCRIPT_NAME'] }}'>
            <fieldset>
                <div class='form-group'>
                    <label class='col-sm-2 control-label'>{{ trans('langEditAuthMethod') }}:</label>
                    <div class='col-sm-10'>
                        {!! selection($auth_names, 'auth', intval($current_auth), "class='form-control'") !!}
                    </div>
                </div>
                {!! showSecondFactorChallenge() !!}
                <div class='col-sm-offset-2 col-sm-10'>
                    <input class='btn btn-primary' type='submit' name='submit_editauth' value='{{ trans('langModify') }}'>
                </div>
                <input type='hidden' name='u' value='{{ $u }}'>
            </fieldset>
            {!! generate_csrf_token_form_field() !!}
        </form>
    </div>
@endsection