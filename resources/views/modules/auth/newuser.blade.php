@extends('layouts.default')

@section('content')

{!! $action_bar !!}

    @if (!$user_registration or $eclass_stud_reg != 2)
        <div class='alert alert-info'>
            {{ trans('langStudentCannotRegister') }}
        </div>
    @else
        @if (isset($_POST['submit']))
            @if ($vmail)
                <div class='alert alert-info'> {{ trans('langMailVerificationSuccess') }} {{ trans('langMailVerificationSuccess2') }} <br><br><small> {{ trans('langMailVerificationNote') }} </small> <br><br>{{ trans('langClick') }} <a href='{{ $urlServer }}' class='mainpage'>{{ trans('langHere') }}</a> {{ trans('langBackPage') }}</div>
            @else
                <div class='alert alert-success'>
                    <p>{{ $user_msg }}</p>
                    <p>{{ trans('langClick') }} <a href='../../'>{{ trans('langHere') }}</a> {{ trans('langPersonalSettingsMore') }}
                    <ul>
                        <li>{{ trans('langPersonalSettingsMore1') }}</li>
                        <li>{{ trans('langPersonalSettingsMore2') }}</li>
                    </ul>
                    </p>
                </div>
            @endif
        @else
            <div class='form-wrapper'>
                <form class='form-horizontal' role='form' action='newuser.php' method='post' onsubmit='return validateNodePickerForm();'>
                <div class='form-group'>
                    <label for='Name' class='col-sm-2 control-label'>{{ trans('langName') }}:</label>
                    <div class='col-sm-10'>
                        <input class='form-control' type='text' name='givenname_form' size='30' maxlength='100' value = '{{ $user_data_firstname }}'  placeholder='{{ trans('langName') }}'>
                    </div>
                </div>
                <div class='form-group'>
                    <label for='SurName' class='col-sm-2 control-label'>{{ trans('langSurname') }}:</label>
                    <div class='col-sm-10'>
                        <input class='form-control' type='text' name='surname_form' size='30' maxlength='100' value = '{{ $user_data_lastname }}' placeholder='{{ trans('langSurname') }}'>
                    </div>
                </div>
                <div class='form-group'>
                    <label for='UserName' class='col-sm-2 control-label'>{{ trans('langUsername') }}:</label>
                    <div class='col-sm-10'>
                        <input class='form-control' type='text' name='uname' value = '{{ $user_data_displayName }}' accept="" size='30' maxlength='100' autocomplete='off' placeholder='{{ trans('langUserNotice') }}'>
                    </div>
                </div>
                @if (empty($provider_name) && empty($provider_id))
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
                @endif
                <div class='form-group'>
                    <label for='UserEmail' class='col-sm-2 control-label'>{{ trans('langEmail') }}:</label>
                    <div class='col-sm-10'>
                        <input class='form-control' type='text' name='email' size='30' maxlength='100' value = '{{ $user_data_email }}' placeholder='{{ trans('email_message') }}'>
                    </div>
                </div>
                <div class='form-group'>
                    <label for='UserAm' class='col-sm-2 control-label'>{{ trans('langAm') }}:</label>
                    <div class='col-sm-10'>
                        <input class='form-control' type='text' name='am' size='20' maxlength='20' value = '{{ $user_data_am }}' placeholder='{{trans ('am_message') }}'>
                    </div>
                </div>
                <div class='form-group'>
                    <label for='UserPhone' class='col-sm-2 control-label'>{{ trans('langPhone') }}:</label>
                    <div class='col-sm-10'>
                        <input class='form-control' type='text' name='phone' size='20' maxlength='20' value = '{{ $user_data_phone }}' placeholder='{{ trans('langOptional') }}'>
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
                        <label for='captcha_code' class='col-sm-2 control-label'>{{ trans('langCaptcha') }}:</label>
                        <div class='col-sm-10'>{!! $captcha !!}</div>
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
                        <input type='hidden' name='provider' value= ' {{ $provider_name }}'>
                        <input type='hidden' name='provider_id' value=' {{ $provider_id }}'>
                      </div>
                      </div>
                @endif
                <div class='form-group'>
                    <div class='col-sm-offset-2 col-sm-10'>
                        <input class='btn btn-primary' type='submit' name='submit' value='{{ trans('langRegistration') }}'>
                    </div>
                </div>
              </form>
          </div>
        @endif
    @endif
@endsection
