@extends('layouts.default')

@section('content')
    {!! isset($action_bar) ?  $action_bar : '' !!}
    <div class='form-wrapper'>
        <form class='form-horizontal' role='form' action='{{ $_SERVER['SCRIPT_NAME'] }}' method='post'>
            <div class='form-group'>
                <label for='email_title' class='col-sm-2 control-label'>{{ trans('langTitle') }}</label>
                <div class='col-sm-10'>
                    <input class='form-control' type='text' name='email_title' value='{{ $email_title }}' size='50' />
                </div>
            </div>
            <div class='form-group'>
              <label for='body_mail' class='col-sm-2 control-label'>{{ trans('typeyourmessage') }}</label>
                  <div class='col-sm-10'>
                  {!! $body_mail_rich_text !!}
                  </div>
            </div>
            <div class='form-group'>
                <label class='col-sm-2 control-label'>{{ trans('langFaculty') }}:</label>
                <div class='col-sm-10'>
                    {!! $buildusernode !!}
                </div>
            </div>
            <div class='form-group'>
              <label for='sendTo' class='col-sm-2 control-label'>{{ trans('langSendMessageTo') }}</label>
                <div class='col-sm-10'>
                    <div class='checkbox'>
                        <label>
                            <input type='checkbox' name='send_to_prof' value='1'>{{ trans('langProfOnly') }}
                        </label>
                        <label>
                            <input type='checkbox' name='send_to_users' value='1'>{{ trans('langStudentsOnly') }}
                        </label>
                    </div>
                </div>
            </div>
            {!! showSecondFactorChallenge() !!}
            <div class='form-group'>
                <div class='col-sm-offset-2 col-sm-10'>
                  <input class='btn btn-primary' type='submit' name='submit' value='{{ trans('langSend') }}'>
                </div>
                    {!! generate_csrf_token_form_field() !!}
            </div>
        </form>
    </div>
@endsection
