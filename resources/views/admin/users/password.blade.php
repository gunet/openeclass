@extends('layouts.default')

@section('content')
    {!! isset($action_bar) ?  $action_bar : '' !!}
    <div class='form-wrapper'>
        <form class='form-horizontal' role='form' method='post' action='{{ $urlServer }}modules/admin/password.php'>
        <fieldset>      
          <input type='hidden' name='userid' value='{{ $_GET['userid'] }}'>
          <div class='form-group'>
          <label class='col-sm-3 control-label'>{{ trans('langNewPass1') }}</label>
            <div class='col-sm-9'>
                <input class='form-control' type='password' size='40' name='password_form' value='' id='password' autocomplete='off'>
                &nbsp;
                <span id='result'></span>
            </div>
          </div>
          <div class='form-group'>
            <label class='col-sm-3 control-label'>{{ trans('langNewPass2') }}</label>
            <div class='col-sm-9'>
                <input class='form-control' type='password' size='40' name='password_form1' value='' autocomplete='off'>
            </div>
          </div>
          <div class='col-sm-offset-3 col-sm-9'>
            {!! showSecondFactorChallenge() !!}
            <input class='btn btn-primary' type='submit' name='changePass' value='{{ trans('langModify') }}'>
            <a class='btn btn-default' href='{{ $urlServer }}modules/admin/edituser.php?u={{ urlencode(getDirectReference($_REQUEST['userid'])) }}'>{{ trans('langCancel') }}</a>
          </div>      
        </fieldset>
        {!! generate_csrf_token_form_field() !!}    
        </form>
    </div>            
@endsection