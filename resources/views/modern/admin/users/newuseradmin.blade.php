@extends('layouts.default')

@section('content')

<div class="col-12 main-section">
<div class='{{ $container }}'>
        <div class="row rowMargin">

                    @include('layouts.common.breadcrumbs', ['breadcrumbs' => $breadcrumbs])

                    @include('layouts.partials.legend_view',['is_editor' => $is_editor, 'course_code' => $course_code])

                    @if(isset($action_bar))
                        {!! $action_bar !!}
                    @else
                        <div class='mt-4'></div>
                    @endif

                    
                    @if(Session::has('message'))
                    <div class='col-12 all-alerts'>
                        <div class="alert {{ Session::get('alert-class', 'alert-info') }} alert-dismissible fade show" role="alert">
                            @php 
                                $alert_type = '';
                                if(Session::get('alert-class', 'alert-info') == 'alert-success'){
                                    $alert_type = "<i class='fa-solid fa-circle-check fa-lg'></i>";
                                }elseif(Session::get('alert-class', 'alert-info') == 'alert-info'){
                                    $alert_type = "<i class='fa-solid fa-circle-info fa-lg'></i>";
                                }elseif(Session::get('alert-class', 'alert-info') == 'alert-warning'){
                                    $alert_type = "<i class='fa-solid fa-triangle-exclamation fa-lg'></i>";
                                }else{
                                    $alert_type = "<i class='fa-solid fa-circle-xmark fa-lg'></i>";
                                }
                            @endphp
                            
                            @if(is_array(Session::get('message')))
                                @php $messageArray = array(); $messageArray = Session::get('message'); @endphp
                                {!! $alert_type !!}<span>
                                @foreach($messageArray as $message)
                                    {!! $message !!}
                                @endforeach</span>
                            @else
                                {!! $alert_type !!}<span>{!! Session::get('message') !!}</span>
                            @endif
                            
                            <button type="button" class="btn-close btn-close-white" data-bs-dismiss="alert" aria-label="Close"></button>
                        </div>
                    </div>
                    @endif
                    
                     

                     <div class='col-lg-6 col-12 d-none d-md-none d-lg-block'>
                        <div class='col-12 h-100 left-form'></div>
                    </div>
                     
                    <div class='col-lg-6 col-12'>
                        <div class='form-wrapper form-edit rounded'>
                       
                        <form class='form-horizontal' role='form' action='{{ $_SERVER['SCRIPT_NAME'] . $params }}' method='post' onsubmit='return validateNodePickerForm();'>
                        <fieldset>
                           
                            <div class="form-group{{ Session::hasError('givenname_form') ? ' has-error' : '' }}">
                                <label for="givenname_form" class="col-sm-12 control-label-notes">{{ trans('langName') }}</label>
                                <div class="col-sm-12">
                                    <input class='form-control' placeholder="{{ trans('langName') }}..." id='givenname_form' type='text' name='givenname_form' value='{{ getValue('givenname_form', $pn) }}'>
                                    @if (Session::hasError('givenname_form'))
                                    <span class="help-block Accent-200-cl">{{ Session::getError('givenname_form') }}</span>
                                    @endif
                                </div>                
                            </div>
                           
                            <div class="form-group{{ Session::hasError('surname_form') ? ' has-error' : '' }} mt-4">
                                <label for="surname_form" class="col-sm-12 control-label-notes">{{ trans('langSurname') }}</label>
                                <div class="col-sm-12">
                                    <input class='form-control' id='surname_form' type='text' name='surname_form' value='{{ getValue('surname_form', $ps) }}' placeholder="{{ trans('langSurname') }}...">
                                    @if (Session::hasError('surname_form'))
                                    <span class="help-block Accent-200-cl">{{ Session::getError('surname_form') }}</span>
                                    @endif
                                </div>                
                            </div>
                         
                            <div class="form-group{{ Session::hasError('uname_form') ? ' has-error' : '' }} mt-4">
                                <label for="uname_form" class="col-sm-12 control-label-notes">{{ trans('langUsername') }}</label>
                                <div class="col-sm-12">
                                    <input class='form-control' id='uname_form' type='text' name='uname_form' value='{{ getValue('uname_form', $pu) }}' placeholder="{{ trans('langUsername') }}...">
                                    @if (Session::hasError('uname_form'))
                                    <span class="help-block Accent-200-cl">{{ Session::getError('uname_form') }}</span>
                                    @endif
                                </div>                
                            </div>             
                            @if ($eclass_method_unique)
                                <input type='hidden' name='auth_form' value='1'>
                            @else
                        
                                <div class="form-group{{ Session::hasError('auth_selection') ? ' has-error' : '' }} mt-4">
                                    <label for="auth_selection" class="col-sm-12 control-label-notes">{{ trans('langEditAuthMethod') }}</label>
                                    <div class="col-sm-12">
                                    {!! selection($auth_m, 'auth_form', '', "id='auth_selection' class='form-control'") !!}
                                        @if (Session::hasError('auth_selection'))
                                        <span class="help-block Accent-200-cl">{{ Session::getError('auth_selection') }}</span>
                                        @endif
                                    </div>                
                                </div>
                            @endif
                            
                            <div class="form-group{{ Session::hasError('password') ? ' has-error' : '' }} mt-4">
                                <label for="passsword_form" class="col-sm-12 control-label-notes">{{ trans('langPass') }}</label>
                                <div class="col-sm-12">
                                    <input class='form-control' id='passsword_form' type='text' name='password' value='{{ getValue('password', choose_password_strength()) }}' autocomplete='off' placeholder="{{ trans('langPass') }}...">
                                    @if (Session::hasError('password'))
                                    <span class="help-block Accent-200-cl">{{ Session::getError('password') }}</span>
                                    @endif
                                </div>                
                            </div>
                 
                            <div class="form-group{{ Session::hasError('email_form') ? ' has-error' : '' }} mt-4">
                                <label for="email_form" class="col-sm-12 control-label-notes">{{ trans('langEmail') }}</label>
                                <div class="col-sm-12">
                                    <input class='form-control' id='email_form' type='text' name='email_form' value='{{ getValue('email_form', $pe) }}' autocomplete='off' placeholder="{{ trans('langEmail') }} {{ get_config('email_required') ? trans('langCompulsory') : trans('langOptional') }}">
                                    @if (Session::hasError('email_form'))
                                    <span class="help-block Accent-200-cl">{{ Session::getError('email_form') }}</span>
                                    @endif
                                </div>                
                            </div>
                   
                            <div class="form-group{{ Session::hasError('verified_mail_form') ? ' has-error' : '' }} mt-4">
                                <label for="verified_mail_form" class="col-sm-12 control-label-notes">{{ trans('langEmailVerified') }}</label>
                                <div class="col-sm-12">
                                    {!! selection($verified_mail_data, "verified_mail_form", $pv, "class='form-control'") !!}
                                    @if (Session::hasError('verified_mail_form'))
                                    <span class="help-block Accent-200-cl">{{ Session::getError('verified_mail_form') }}</span>
                                    @endif
                                </div>                
                            </div>
                           
                            <div class="form-group{{ Session::hasError('phone_form') ? ' has-error' : '' }} mt-4">
                                <label for="phone_form" class="col-sm-12 control-label-notes">{{ trans('langPhone') }}</label>
                                <div class="col-sm-12">
                                    <input class='form-control' id='phone_form' type='text' name='phone_form' value='{{ getValue('phone_form', $pphone) }}' placeholder="{{ trans('langPhone') }}...">
                                    @if (Session::hasError('phone_form'))
                                    <span class="help-block Accent-200-cl">{{ Session::getError('phone_form') }}</span>
                                    @endif
                                </div>                
                            </div>  
                                    
                            <div class="form-group{{ Session::hasError('faculty') ? ' has-error' : '' }} mt-4">
                                <label for="faculty" class="col-sm-12 control-label-notes">{{ trans('langFaculty') }}</label>
                                <div class="col-sm-12">
                                    {!! $tree_html !!}
                                    @if (Session::hasError('faculty'))
                                    <span class="help-block Accent-200-cl">{{ Session::getError('faculty') }}</span>
                                    @endif
                                </div>                
                            </div>
                            @if ($pstatus == 5)
                                <!--only for students-->
                                @if((get_config('mentoring_platform') and !get_config('mentoring_always_active')) or (!get_config('mentoring_platform')))
                                <div class="form-group{{ Session::hasError('am_form') ? ' has-error' : '' }} mt-4">
                                    <label for="am_form" class="col-sm-12 control-label-notes">{{ trans('langAm') }}</label>
                                    <div class="col-sm-12">
                                        <input class='form-control' id='am_form' type='text' name='am_form' value='{{ getValue('am_form', $pam) }}' placeholder="{{ get_config('am_required') ? trans('langCompulsory') : trans('langOptional') }}">
                                        @if (Session::hasError('am_form'))
                                        <span class="help-block Accent-200-cl">{{ Session::getError('am_form') }}</span>
                                        @endif
                                    </div>                
                                </div>   
                                @endif             
                            @endif
                            @if (get_config('block_duration_account'))
                            
                                <div class='input-append date form-group mt-4'>
                                    <label class='col-sm-12 control-label-notes'>{{ trans('langExpirationDate') }}</label>
                                    <div class='col-sm-12'>
                                        <span class='help-block'>{{ trans('lang_message_block_duration_account') }}</span>
                                    </div>
                                </div>
                            @else
               
                                <div class='input-append date form-group mt-4'>
                                    <label class='col-sm-12 control-label-notes'>{{ trans('langExpirationDate') }}</label>
                                    <div class='col-sm-12'>
                                        <div class='input-group'>
                                            <span class='add-on input-group-text h-40px bg-white input-border-color border-end-0'><i class='fa-regular fa-calendar Neutral-600-cl'></i></span>
                                            <input class='form-control mt-0 border-start-0' id='user_date_expires_at' name='user_date_expires_at' type='text' value='{{ $expirationDatevalue }}'>
                                            
                                        </div>
                                    </div>
                                </div>
                            @endif
                         
                            <div class="form-group{{ Session::hasError('language_form') ? ' has-error' : '' }} mt-4">
                                <label for="language_form" class="col-sm-12 control-label-notes">{{ trans('langLanguage') }}</label>
                                <div class="col-sm-12">
                                    {!! lang_select_options('language_form', "class='form-control'", Session::has('language_form') ? Session::get('language_form'): $language) !!}
                                    @if (Session::hasError('language_form'))
                                    <span class="help-block Accent-200-cl">{{ Session::getError('language_form') }}</span>
                                    @endif
                                </div>                
                            </div>
                            @if ($ext_uid)
                   
                                <div class="form-group mt-4">
                                    <label for="provider" class="col-sm-12 control-label-notes">{{ trans('langProviderConnectWith') }}</label>
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
                      
                                <div class="form-group mt-4">
                                    <label for="comments" class="col-sm-12 control-label-notes">{{ trans('langComments') }}</label>
                                    <div class="col-sm-12">
                                        <p class='form-control-static'>
                                            {{ $pcom }}
                                        </p>
                                    </div>
                                </div>
                               
                                <div class="form-group mt-4">
                                    <label for="date" class="col-sm-12 control-label-notes">{{ trans('langDate') }}</label>
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
                            <div class='mt-3'></div>
                            <div class='row'>{!! render_profile_fields_form($cpf_context, true) !!}</div>
                            {!! showSecondFactorChallenge() !!}
                           
                            <div class='col-12 mt-5 d-flex justify-content-center align-items-center'>
                            <input class='btn submitAdminBtn' type='submit' name='submit' value='{{ trans('langRegistration') }}'>
                            </div>        
                        </fieldset>
                        {!! generate_csrf_token_form_field() !!}
                        </form>
                    </div></div>    
                
        </div>
</div>
</div>    
@endsection
