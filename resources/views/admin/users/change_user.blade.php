@extends('layouts.default')

@section('content')
    {!! isset($action_bar) ?  $action_bar : '' !!}
    <div class='form-wrapper'>
        <form class='form-horizontal' role='form' action='{{ $_SERVER['SCRIPT_NAME'] }}' method='post'>
            <div class='form-group'>
            <label for = 'username' class='col-sm-3 control-label'>{{ trans('langUsername') }}:</label>
                <div class='col-sm-9'>
                    <input id='username' class='form-control' type='text' name='username' placeholder='{{ trans('langUsername') }}'>
                </div>
            </div>
            <div class='form-group'>
                <div class='col-sm-9 col-sm-offset-3'>
                    {!! showSecondFactorChallenge() !!}
                    <input class='btn btn-primary' type='submit' value='{{ trans('langSubmit') }}'>
                </div>
            </div>
            {!! generate_csrf_token_form_field() !!}            
        </form>
    </div>
@endsection