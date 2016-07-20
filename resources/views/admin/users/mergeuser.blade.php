@extends('layouts.default')

@section('content')
    {!! isset($action_bar) ?  $action_bar : '' !!}
    @if (isset($_REQUEST['u']))
    <div class='form-wrapper'>
        <form class='form-horizontal' role='form' method='post' action='{{ $_SERVER['SCRIPT_NAME'] }}'>
        <fieldset>                                    
            <div class='form-group'>
                <label class='col-sm-3 control-label'>{{ trans('langUser') }}:</label>
                <div class='col-sm-9'>
                    {!! display_user($info['id']) !!}
                </div>
            </div>
            <div class='form-group'>
                <label class='col-sm-3 control-label'>{{ trans('langEditAuthMethod') }}:</label>
                <div class='col-sm-9'>{{ get_auth_info($auth_id) }}</div>
            </div>
            <div class='form-group'>
                <label class='col-sm-3 control-label'>{{ trans('langProperty') }}:</label>                     
                <div class='col-sm-9'>{{ $status_names[$info['status']] }}</div>
            </div>                    
            {!! $target_field !!}
            <input type='hidden' name='u' value='{{ getIndirectReference($u) }}'>
            {!! showSecondFactorChallenge() !!}
            <div class='col-sm-offset-3 col-sm-9'>                                                  
                <input class='btn btn-primary' type='submit' name='submit' value='{{ $submit_button }}'>
            </div>                                                  
        </fieldset>
        {!! $target_user_input !!}
        {!! generate_csrf_token_form_field() !!}
        </form>
    </div>
    @else
        <div class='alert alert-danger'>
            {{ trans('langError') }}<br>
            <a href='search_user.php'>{{ trans('langBack') }}
        </div>
    @endif
@endsection