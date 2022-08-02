@extends('layouts.default')

@section('content')

<div class="pb-lg-3 pt-lg-3 pb-0 pt-0">

    <div class="container-fluid main-container">

        <div class="row rowMedium">

            <div class="col-xl-12 col-lg-12 col-md-12 col-sm-12 col-12 justify-content-center col_maincontent_active_Homepage">
                    
                <div class="row p-lg-5 p-md-5 ps-1 pe-1 pt-5 pb-5">

                    @include('layouts.common.breadcrumbs', ['breadcrumbs' => $breadcrumbs])

                    @include('layouts.partials.legend_view',['is_editor' => $is_editor, 'course_code' => $course_code])

                    
                    @if(Session::has('message'))
                    <div class='col-xxl-12 col-xl-12 col-lg-12 col-md-12 col-sm-12 col-12 mt-5'>
                        <p class="alert {{ Session::get('alert-class', 'alert-info') }} alert-dismissible fade show" role="alert">
                            @if(is_array(Session::get('message')))
                                @php $messageArray = array(); $messageArray = Session::get('message'); @endphp
                                @foreach($messageArray as $message)
                                    {!! $message !!}
                                @endforeach
                            @else
                                {!! Session::get('message') !!}
                            @endif
                            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                        </p>
                    </div>
                    @endif
                    
                     {!! isset($action_bar) ?  $action_bar : '' !!}
                     
                    <div class='col-xxl-12 col-xl-12 col-lg-12 col-md-12 col-sm-12 col-12'>
                        <div class='form-wrapper shadow-sm p-3 rounded'>
                       
                        <form class='form-horizontal' role='form' action='{{ $_SERVER['SCRIPT_NAME'] . $params }}' method='post' onsubmit='return validateNodePickerForm();'>
                        <fieldset>
                            <div class='row p-2'></div>
                            <div class="form-group{{ Session::hasError('givenname_form') ? ' has-error' : '' }}">
                                <label for="givenname_form" class="col-sm-6 control-label-notes">{{ trans('langName') }}:</label>
                                <div class="col-sm-12">
                                    <input class='form-control' id='givenname_form' type='text' name='givenname_form' value='{{ getValue('givenname_form', $pn) }}' placeholder="{{ trans('langName') }}">
                                    @if (Session::hasError('givenname_form'))
                                    <span class="help-block">{{ Session::getError('givenname_form') }}</span>
                                    @endif
                                </div>                
                            </div>
                            <div class='row p-2'></div>
                            <div class="form-group{{ Session::hasError('surname_form') ? ' has-error' : '' }}">
                                <label for="surname_form" class="col-sm-6 control-label-notes">{{ trans('langSurname') }}:</label>
                                <div class="col-sm-12">
                                    <input class='form-control' id='surname_form' type='text' name='surname_form' value='{{ getValue('surname_form', $ps) }}' placeholder="{{ trans('langSurname') }}">
                                    @if (Session::hasError('surname_form'))
                                    <span class="help-block">{{ Session::getError('surname_form') }}</span>
                                    @endif
                                </div>                
                            </div>
                            <div class='row p-2'></div>
                            <div class="form-group{{ Session::hasError('uname_form') ? ' has-error' : '' }}">
                                <label for="uname_form" class="col-sm-6 control-label-notes">{{ trans('langUsername') }}:</label>
                                <div class="col-sm-12">
                                    <input class='form-control' id='uname_form' type='text' name='uname_form' value='{{ getValue('uname_form', $pu) }}' placeholder="{{ trans('langUsername') }}">
                                    @if (Session::hasError('uname_form'))
                                    <span class="help-block">{{ Session::getError('uname_form') }}</span>
                                    @endif
                                </div>                
                            </div>             
                            @if ($eclass_method_unique)
                                <input type='hidden' name='auth_form' value='1'>
                            @else
                            <div class='row p-2'></div>
                                <div class="form-group{{ Session::hasError('auth_selection') ? ' has-error' : '' }}">
                                    <label for="auth_selection" class="col-sm-6 control-label-notes">{{ trans('langEditAuthMethod') }}:</label>
                                    <div class="col-sm-12">
                                    {!! selection($auth_m, 'auth_form', '', "id='auth_selection' class='form-control'") !!}
                                        @if (Session::hasError('auth_selection'))
                                        <span class="help-block">{{ Session::getError('auth_selection') }}</span>
                                        @endif
                                    </div>                
                                </div>
                            @endif
                            <div class='row p-2'></div>
                            <div class="form-group{{ Session::hasError('password') ? ' has-error' : '' }}">
                                <label for="passsword_form" class="col-sm-6 control-label-notes">{{ trans('langPass') }}:</label>
                                <div class="col-sm-12">
                                    <input class='form-control' id='passsword_form' type='text' name='password' value='{{ getValue('password', choose_password_strength()) }}' autocomplete='off' placeholder="{{ trans('langPass') }}">
                                    @if (Session::hasError('password'))
                                    <span class="help-block">{{ Session::getError('password') }}</span>
                                    @endif
                                </div>                
                            </div>
                            <div class='row p-2'></div>
                            <div class="form-group{{ Session::hasError('email_form') ? ' has-error' : '' }}">
                                <label for="email_form" class="col-sm-6 control-label-notes">{{ trans('langEmail') }}:</label>
                                <div class="col-sm-12">
                                    <input class='form-control' id='email_form' type='text' name='email_form' value='{{ getValue('email_form', $pe) }}' autocomplete='off' placeholder="{{ trans('langEmail') }} {{ get_config('email_required') ? trans('langCompulsory') : trans('langOptional') }}">
                                    @if (Session::hasError('email_form'))
                                    <span class="help-block">{{ Session::getError('email_form') }}</span>
                                    @endif
                                </div>                
                            </div>
                            <div class='row p-2'></div>
                            <div class="form-group{{ Session::hasError('verified_mail_form') ? ' has-error' : '' }}">
                                <label for="verified_mail_form" class="col-sm-6 control-label-notes">{{ trans('langEmailVerified') }}:</label>
                                <div class="col-sm-12">
                                    {!! selection($verified_mail_data, "verified_mail_form", $pv, "class='form-control'") !!}
                                    @if (Session::hasError('verified_mail_form'))
                                    <span class="help-block">{{ Session::getError('verified_mail_form') }}</span>
                                    @endif
                                </div>                
                            </div>
                            <div class='row p-2'></div>
                            <div class="form-group{{ Session::hasError('phone_form') ? ' has-error' : '' }}">
                                <label for="phone_form" class="col-sm-6 control-label-notes">{{ trans('langPhone') }}:</label>
                                <div class="col-sm-12">
                                    <input class='form-control' id='phone_form' type='text' name='phone_form' value='{{ getValue('phone_form', $pphone) }}' placeholder="{{ trans('langPhone') }}">
                                    @if (Session::hasError('phone_form'))
                                    <span class="help-block">{{ Session::getError('phone_form') }}</span>
                                    @endif
                                </div>                
                            </div>  
                            <div class='row p-2'></div>                      
                            <div class="form-group{{ Session::hasError('faculty') ? ' has-error' : '' }}">
                                <label for="faculty" class="col-sm-6 control-label-notes">{{ trans('langFaculty') }}:</label>
                                <div class="col-sm-12">
                                    {!! $tree_html !!}
                                    @if (Session::hasError('faculty'))
                                    <span class="help-block">{{ Session::getError('faculty') }}</span>
                                    @endif
                                </div>                
                            </div>
                            @if ($pstatus == 5)
                                <!--only for students-->
                                <div class='row p-2'></div>
                                <div class="form-group{{ Session::hasError('am_form') ? ' has-error' : '' }}">
                                    <label for="am_form" class="col-sm-6 control-label-notes">{{ trans('langAm') }}:</label>
                                    <div class="col-sm-12">
                                        <input class='form-control' id='am_form' type='text' name='am_form' value='{{ getValue('am_form', $pam) }}' placeholder="{{ get_config('am_required') ? trans('langCompulsory') : trans('langOptional') }}">
                                        @if (Session::hasError('am_form'))
                                        <span class="help-block">{{ Session::getError('am_form') }}</span>
                                        @endif
                                    </div>                
                                </div>                
                            @endif
                            @if (get_config('block_duration_account'))
                            <div class='row p-2'></div>
                                <div class='input-append date form-group'>
                                    <label class='col-sm-6 control-label-notes'>{{ trans('langExpirationDate') }}:</label>
                                    <div class='col-sm-12'>
                                        <span class='help-block'>{{ trans('lang_message_block_duration_account') }}</span>
                                    </div>
                                </div>
                            @else
                            <div class='row p-2'></div>
                                <div class='input-append date form-group'>
                                    <label class='col-sm-6 control-label-notes'>{{ trans('langExpirationDate') }}:</label>
                                    <div class='col-sm-12'>
                                        <div class='input-group'>
                                            <input class='form-control' id='user_date_expires_at' name='user_date_expires_at' type='text' value='{{ $expirationDatevalue }}'>
                                            <span class='input-group-addon'><i class='fa fa-calendar'></i></span>
                                        </div>
                                    </div>
                                </div>
                            @endif
                            <div class='row p-2'></div>
                            <div class="form-group{{ Session::hasError('language_form') ? ' has-error' : '' }}">
                                <label for="language_form" class="col-sm-6 control-label-notes">{{ trans('langLanguage') }}:</label>
                                <div class="col-sm-12">
                                    {!! lang_select_options('language_form', "class='form-control'", Session::has('language_form') ? Session::get('language_form'): $language) !!}
                                    @if (Session::hasError('language_form'))
                                    <span class="help-block">{{ Session::getError('language_form') }}</span>
                                    @endif
                                </div>                
                            </div>
                            @if ($ext_uid)
                            <div class='row p-2'></div>
                                <div class="form-group">
                                    <label for="provider" class="col-sm-6 control-label-notes">{{ trans('langProviderConnectWith') }}:</label>
                                    <div class="col-sm-12">
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
                            <div class='row p-2'></div>
                                <div class="form-group">
                                    <label for="comments" class="col-sm-6 control-label-notes">{{ trans('langComments') }}:</label>
                                    <div class="col-sm-12">
                                        <p class='form-control-static'>
                                            {{ $pcom }}
                                        </p>
                                    </div>
                                </div>
                                <div class='row p-2'></div>
                                <div class="form-group">
                                    <label for="date" class="col-sm-6 control-label-notes">{{ trans('langDate') }}:</label>
                                    <div class="col-sm-12">
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
                            <div class='row p-2'></div>
                            {!! render_profile_fields_form($cpf_context, true) !!}
                            {!! showSecondFactorChallenge() !!}
                            <div class='row p-2'></div>   
                            <div class='col-sm-offset-2 col-sm-10'>
                            <input class='btn btn-primary' type='submit' name='submit' value='{{ trans('langRegistration') }}'>
                            </div>        
                        </fieldset>
                        {!! generate_csrf_token_form_field() !!}
                        </form>
                    </div></div>    
                </div>
            </div>
        </div>
    </div>
</div>    
@endsection
