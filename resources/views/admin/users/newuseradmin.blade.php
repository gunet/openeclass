@extends('layouts.default')

@section('content')
    {!! isset($action_bar) ?  $action_bar : '' !!}
    <div class='form-wrapper'>
        <form class='form-horizontal' role='form' action='{{ $_SERVER['SCRIPT_NAME'] . $params }}' method='post' onsubmit='return validateNodePickerForm();'>
        <fieldset>
            <div class="form-group{{ Session::hasError('givenname_form') ? ' has-error' : '' }}">
                <label for="givenname_form" class="col-sm-2 control-label">{{ trans('langName') }}:</label>
                <div class="col-sm-10">
                    <input class='form-control' id='givenname_form' type='text' name='givenname_form' value='{{ getValue('givenname_form', $pn) }}' placeholder="{{ trans('langName') }}">
                    @if (Session::hasError('givenname_form'))
                    <span class="help-block">{{ Session::getError('givenname_form') }}</span>
                    @endif
                </div>                
            </div>
            <div class="form-group{{ Session::hasError('surname_form') ? ' has-error' : '' }}">
                <label for="surname_form" class="col-sm-2 control-label">{{ trans('langSurname') }}:</label>
                <div class="col-sm-10">
                    <input class='form-control' id='surname_form' type='text' name='surname_form' value='{{ getValue('surname_form', $ps) }}' placeholder="{{ trans('langSurname') }}">
                    @if (Session::hasError('surname_form'))
                    <span class="help-block">{{ Session::getError('surname_form') }}</span>
                    @endif
                </div>                
            </div>
            <div class="form-group{{ Session::hasError('uname_form') ? ' has-error' : '' }}">
                <label for="uname_form" class="col-sm-2 control-label">{{ trans('langUsername') }}:</label>
                <div class="col-sm-10">
                    <input class='form-control' id='uname_form' type='text' name='uname_form' value='{{ getValue('uname_form', $pu) }}' placeholder="{{ trans('langUsername') }}">
                    @if (Session::hasError('uname_form'))
                    <span class="help-block">{{ Session::getError('uname_form') }}</span>
                    @endif
                </div>                
            </div>             
            @if ($eclass_method_unique)
                <input type='hidden' name='auth_form' value='1'>
            @else
                <div class="form-group{{ Session::hasError('auth_selection') ? ' has-error' : '' }}">
                    <label for="auth_selection" class="col-sm-2 control-label">{{ trans('langEditAuthMethod') }}:</label>
                    <div class="col-sm-10">
                       {!! selection($auth_m, 'auth_form', '', "id='auth_selection' class='form-control'") !!}
                        @if (Session::hasError('auth_selection'))
                        <span class="help-block">{{ Session::getError('auth_selection') }}</span>
                        @endif
                    </div>                
                </div>
            @endif
            <div class="form-group{{ Session::hasError('password') ? ' has-error' : '' }}">
                <label for="passsword_form" class="col-sm-2 control-label">{{ trans('langPass') }}:</label>
                <div class="col-sm-10">
                    <input class='form-control' id='passsword_form' type='text' name='password' value='{{ getValue('password', genPass()) }}' autocomplete='off' placeholder="{{ trans('langPass') }}">
                    @if (Session::hasError('password'))
                    <span class="help-block">{{ Session::getError('password') }}</span>
                    @endif
                </div>                
            </div>
            <div class="form-group{{ Session::hasError('email_form') ? ' has-error' : '' }}">
                <label for="email_form" class="col-sm-2 control-label">{{ trans('langEmail') }}:</label>
                <div class="col-sm-10">
                    <input class='form-control' id='email_form' type='text' name='email_form' value='{{ getValue('email_form', $pe) }}' autocomplete='off' placeholder="{{ trans('langEmail') }} {{ get_config('email_required') ? trans('langCompulsory') : trans('langOptional') }}">
                    @if (Session::hasError('email_form'))
                    <span class="help-block">{{ Session::getError('email_form') }}</span>
                    @endif
                </div>                
            </div>
            <div class="form-group{{ Session::hasError('verified_mail_form') ? ' has-error' : '' }}">
                <label for="verified_mail_form" class="col-sm-2 control-label">{{ trans('langEmailVerified') }}:</label>
                <div class="col-sm-10">
                    {!! selection($verified_mail_data, "verified_mail_form", $pv, "class='form-control'") !!}
                    @if (Session::hasError('verified_mail_form'))
                    <span class="help-block">{{ Session::getError('verified_mail_form') }}</span>
                    @endif
                </div>                
            </div>
            <div class="form-group{{ Session::hasError('phone_form') ? ' has-error' : '' }}">
                <label for="phone_form" class="col-sm-2 control-label">{{ trans('langPhone') }}:</label>
                <div class="col-sm-10">
                    <input class='form-control' id='phone_form' type='text' name='phone_form' value='{{ getValue('phone_form', $pphone) }}' placeholder="{{ trans('langPhone') }}">
                    @if (Session::hasError('phone_form'))
                    <span class="help-block">{{ Session::getError('phone_form') }}</span>
                    @endif
                </div>                
            </div>                        
            <div class="form-group{{ Session::hasError('faculty') ? ' has-error' : '' }}">
                <label for="faculty" class="col-sm-2 control-label">{{ trans('langFaculty') }}:</label>
                <div class="col-sm-10">
                    {!! $tree_html !!}
                    @if (Session::hasError('faculty'))
                    <span class="help-block">{{ Session::getError('faculty') }}</span>
                    @endif
                </div>                
            </div>
            @if ($pstatus == 5)
                <!--only for students-->
                <div class="form-group{{ Session::hasError('am_form') ? ' has-error' : '' }}">
                    <label for="am_form" class="col-sm-2 control-label">{{ trans('langAm') }}:</label>
                    <div class="col-sm-10">
                        <input class='form-control' id='am_form' type='text' name='am_form' value='{{ getValue('am_form', $pam) }}' placeholder="{{ get_config('am_required') ? trans('langCompulsory') : trans('langOptional') }}">
                        @if (Session::hasError('am_form'))
                        <span class="help-block">{{ Session::getError('am_form') }}</span>
                        @endif
                    </div>                
                </div>                
            @endif
            <div class="form-group{{ Session::hasError('language_form') ? ' has-error' : '' }}">
                <label for="language_form" class="col-sm-2 control-label">{{ trans('langLanguage') }}:</label>
                <div class="col-sm-10">
                    {!! lang_select_options('language_form', "class='form-control'", Session::has('language_form') ? Session::get('language_form'): $language) !!}
                    @if (Session::hasError('language_form'))
                    <span class="help-block">{{ Session::getError('language_form') }}</span>
                    @endif
                </div>                
            </div>
            @if ($ext_uid)
                <div class="form-group">
                    <label for="provider" class="col-sm-2 control-label">{{ trans('langProviderConnectWith') }}:</label>
                    <div class="col-sm-10">
                        <p class='form-control-static'>
                            <img src='{{ $themeimg }}/{{ $auth_ids[$ext_uid->auth_id] . '.png' }}'>&nbsp;
                            {{ $authFullName[$ext_uid->auth_id] }}
                            <br>
                            <small>{{ trans('langProviderConnectWithTooltip') }}</small>
                        </p>                        
                    </div>                
                </div>                
            @endif
            @if (isset($_GET['id']))
                <div class="form-group">
                    <label for="comments" class="col-sm-2 control-label">{{ trans('langComments') }}:</label>
                    <div class="col-sm-10">
                        <p class='form-control-static'>
                            {{ $pcom }}
                        </p>
                    </div>
                </div>
                <div class="form-group">
                    <label for="date" class="col-sm-2 control-label">{{ trans('langDate') }}:</label>
                    <div class="col-sm-10">
                        <p class='form-control-static'>
                            {{ $pdate }}
                        </p>
                    </div>
                </div>            
                <input type='hidden' name='rid' value='$id'>
            @endif
            @if (isset($pstatus))
                <input type='hidden' name='pstatus' value='{{ $pstatus }}'>
            @endif
            {!! render_profile_fields_form($cpf_context, true) !!}
            {!! showSecondFactorChallenge() !!}
            <div class='col-sm-offset-2 col-sm-10'>
              <input class='btn btn-primary' type='submit' name='submit' value='{{ trans('langRegistration') }}'>
            </div>        
        </fieldset>
        {!! generate_csrf_token_form_field() !!}
        </form>
  </div>        
@endsection