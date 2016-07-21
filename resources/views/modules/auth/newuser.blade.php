@extends('layouts.default')

@section('content')

{!! $action_bar !!}

    @if (!$user_registration or $eclass_stud_reg != 2)
        <div class='alert alert-info'>
            {{ trans('langStudentCannotRegister') }}
        </div>
    @elseif (!isset($_POST['submit']))
        <div class='form-wrapper'>
            <form class='form-horizontal' role='form' action='newuser.php' method='post' onsubmit='return validateNodePickerForm();'>
            <fieldset>
            <div class='form-group'>
                <label for='Name' class='col-sm-2 control-label'>{{ trans('langName') }}:</label>
                <div class='col-sm-10'>
                    <input class='form-control' type='text' name='givenname_form' size='30' maxlength='100'" .
                      ({{ $user_data_firstname }}) . " placeholder='{{ trans('langName') }}'>
                </div>
            </div>
            <div class='form-group'>
                <label for='SurName' class='col-sm-2 control-label'>{{ trans('langSurname') }}:</label>
                <div class='col-sm-10'>
                    <input class='form-control' type='text' name='surname_form' size='30' maxlength='100'" .
                      ({{ $user_data_lastname }}) . " placeholder='{{ trans('langSurname') }}'>
                </div>
            </div>
            <div class='form-group'>
                <label for='UserName' class='col-sm-2 control-label'>{{ trans('langUsername') }}:</label>
                <div class='col-sm-10'>
                    <input class='form-control' type='text' name='uname'" .
                      ({{ $user_data_displayName }}) . " size='30' maxlength='100' autocomplete='off' placeholder='{{ trans('langUserNotice') }}'>
                </div>
            </div>
            <div class='form-group'>
                <label for='UserPass' class='col-sm-2 control-label'>{{ trans('langPass') }}:</label>
                <div class='col-sm-10'>
                    <input class='form-control' type='password' name='password1' size='30' maxlength='30' autocomplete='off' id='password' placeholder='{{ trans('langUserNotice') }}'><span id='result'></span>
                </div>
            </div>
            <div class='form-group'>
              <label for='UserPass2' class='col-sm-2 control-label'>{{ trans('langConfirmation') }}:</label>
                <div class='col-sm-10'>
                    <input class='form-control' type='password' name='password' size='30' maxlength='30' autocomplete='off'/>
                </div>
            </div>
            <div class='form-group'>
                <label for='UserEmail' class='col-sm-2 control-label'>{{ trans('langEmail') }}:</label>
                <div class='col-sm-10'>
                    <input class='form-control' type='text' name='email' size='30' maxlength='100'" .
                      ({{ $user_data_email }}) . " placeholder='{{ trans('email_message') }}'>
                </div>
            </div>
            <div class='form-group'>
                <label for='UserAm' class='col-sm-2 control-label'>{{ trans('langAm') }}:</label>
                <div class='col-sm-10'>
                    <input class='form-control' type='text' name='am' size='20' maxlength='20' placeholder='{{trans ('am_message') }}'>
                </div>
            </div>
            <div class='form-group'>
                <label for='UserPhone' class='col-sm-2 control-label'>{{ trans('langPhone') }}:</label>
                <div class='col-sm-10'>
                    <input class='form-control' type='text' name='phone' size='20' maxlength='20'" .
                      ({{ $user_data_phone }})  . " placeholder='{{ trans('langOptional') }}'>
                </div>
            </div>
            <div class='form-group'>
              <label for='UserFac' class='col-sm-2 control-label'>{{ trans('langFaculty') }}:</label>
                <div class='col-sm-10'>
                    {!! $buildusernode !!}
                </div>
            </div>
            <div class='form-group'>
              <label for='UserLang' class='col-sm-2 control-label'>{{ trans('langLanguage') }}:</label>
                <div class='col-sm-10'>
                    {!! $lang_select_options !!}
                </div>
            </div>
            @if ($display_captcha) 
                <div class='form-group'>
                    <div class='col-sm-offset-2 col-sm-10'><img id='captcha' src='{{ $captcha }}' alt='CAPTCHA Image' /></div><br>
                        <label for='Captcha' class='col-sm-2 control-label'>{{ trans('langCaptcha') }}:</label>
                    <div class='col-sm-10'><input type='text' name='captcha_code' maxlength='6'/></div>
                </div>
            @endif
        <!-- add custom profile fields -->
        {!! $render_profile_fields_form !!}

        <!-- check if provider_id from an authenticated user and a valid provider name are set so as to show the relevant form -->
        @if(!empty($provider_name) && !empty($provider_id)) 
            <div class='form-group'>
              <label for='UserLang' class='col-sm-2 control-label'>{{ trans('langProviderConnectWith') }}:</label>
              <div class='col-sm-10'><p class='form-control-static'>
                <img src='$themeimg/" . q($provider_name) . ".png' alt='" . q($provider_name) . "'>&nbsp;" . q(ucfirst($provider_name)) . "<br /><small>{{ trans('langProviderConnectWithTooltip') }}</small></p>
              </div>
              <div class='col-sm-offset-2 col-sm-10'>
                <input type='hidden' name='provider' value='" . $provider_name . "' />
                <input type='hidden' name='provider_id' value='" . $provider_id . "' />
              </div>
              </div>
        @endif
        <div class='form-group'>
            <div class='col-sm-offset-2 col-sm-10'>
                <input class='btn btn-primary' type='submit' name='submit' value='{{ trans('langRegistration') }}'>
            </div>
        </div>
      </fieldset>
      </form>
      </div>
    @endif
@endsection
