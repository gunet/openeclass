@extends('layouts.default')

@section('content')
    {!! isset($action_bar) ?  $action_bar : '' !!}
    <div class='form-wrapper'>
       <form class='form-horizontal' name='authmenu' method='post' action='{{ $_SERVER['SCRIPT_NAME'] }}'>
        <input type='hidden' name='auth' value='{{ $auth }}'>
        <fieldset>  
            <div class='alert alert-info'>{{ trans('langTestAccount') }} ({{ $auth_ids[$auth] }})</div>
            <div class='form-group'>
                <label for='test_username' class='col-sm-2 control-label'>{{ trans('langUsername') }}:</label>
                <div class='col-sm-10'>
                    <input class='form-control' type='text' name='test_username' id='test_username' value='{{ canonicalize_whitespace($test_username) }}' autocomplete='off'>
                </div>
            </div>
            <div class='form-group'>
                <label for='test_password' class='col-sm-2 control-label'>{{ trans('langPass') }}:</label>
                <div class='col-sm-10'>
                    <input class='form-control' type='password' name='test_password' id='test_password' value='{{ $test_password }}' autocomplete='off'>
                </div>
            </div>
            <div class='form-group'>
                <div class='col-sm-10 col-sm-offset-2'>
                    <input class='btn btn-primary' type='submit' name='submit' value='{{ trans('langConnTest') }}'>
                    <a class='btn btn-default' href='auth.php'>{{ trans('langCancel') }}</a>
                </div>
            </div>
        </fieldset>
        {!! generate_csrf_token_form_field() !!}
        </form>
    </div>       
@endsection