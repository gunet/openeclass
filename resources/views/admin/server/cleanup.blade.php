@extends('layouts.default')

@section('content')
    {!! isset($action_bar) ?  $action_bar : '' !!}
    <div class='alert alert-danger'>
        {{ trans('langCleanupInfo') }}
    </div>
    <div class='col-sm-12 col-sm-offset-5'>
        <form method='post' action='{{ $_SERVER['SCRIPT_NAME'] }}'>
            {!! showSecondFactorChallenge() !!}
            <input class='btn btn-primary' type='submit' name='submit' value='{{ trans('langCleanup')}}'>
            {!! generate_csrf_token_form_field() !!}
        </form>
    </div>
@endsection