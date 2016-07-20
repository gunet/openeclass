@extends('layouts.default')

@section('content')
    {!! isset($action_bar) ?  $action_bar : '' !!}
    <div class='form-wrapper'>
        <form class='form-horizontal' role='form' action='{{ $_SERVER['SCRIPT_NAME'] }}' method='post'>
        <fieldset>
            <div class='form-group'>
                <label for='email_title' class='col-sm-2 control-label'>{{ trans('langTitle') }}</label>
                <div class='col-sm-10'>
                    <input class='form-control' type='text' name='email_title' value=''>
                </div>
            </div>
            <div class='form-group'>
              <label for='body_mail' class='col-sm-2 control-label'>{{ trans('typeyourmessage') }}</label>
                  <div class='col-sm-10'>
                  {!! $body_mail_rich_text !!}
                  </div>
            </div>
            <div class='form-group'>
              <label for='sendTo' class='col-sm-2 control-label'>{{ trans('langSendMessageTo') }}</label>
                <div class='col-sm-10'>
                    <select class='form-control' name='sendTo' id='sendTo'>
                        <option value='1'>{{ trans('langProfOnly') }}</option>
                        <option value='2'>{{ trans('langStudentsOnly') }}</option>
                        <option value='0'>{{ trans('langToAllUsers') }}</option>
                    </select>	    
                </div>
            </div>
            {!! showSecondFactorChallenge() !!}
            <div class='col-sm-offset-2 col-sm-10'>	
              <input class='btn btn-primary' type='submit' name='submit' value='{{ trans('langSend') }}'>          
            </div>	
        </fieldset>
        {!! generate_csrf_token_form_field() !!}
        </form>
    </div>
@endsection