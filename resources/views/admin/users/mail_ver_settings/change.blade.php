@extends('layouts.default')

@section('content')
    {!! isset($action_bar) ?  $action_bar : '' !!}
    @if (isset($sub))
        <div class='form-wrapper'>
            <form class='form-horizontal' role='form' name='mail_verification_change' method='post' action='{{ $_SERVER['SCRIPT_NAME'] }}'>
		<fieldset>		
                <div class='form-group'>
                    <label class='col-sm-2 control-label'>{{ trans('langChangeTo') }}:</label>
                    <div class='col-sm-10'>
                        {!! selection($mail_ver_data, "new_mail_ver", $sub, "class='form-control'") !!}
                    </div>
		</div>
                {!! showSecondFactorChallenge() !!}
		<div class='col-sm-offset-2 col-sm-10'>
                    <input class='btn btn-primary' type='submit' name='submit' value='{{ trans('langEdit') }}'>
                </div>
		<input type='hidden' name='old_mail_ver' value='{{ $sub }}'>		
		</fieldset>
                {!! generate_csrf_token_form_field() !!}
            </form>
        </div>
    @endif    
    @include('admin.users.mail_ver_settings.messages')
@endsection