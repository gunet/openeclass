@extends('layouts.default')

@section('content')

<div class="col-12 main-section">
    <div class='{{ $container }} main-container'>
        <div class="row m-auto">

            <div class='col-12'>
                <h1>{!! $toolName !!}</h1>
            </div>

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
                        
                        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                    </div>
                </div>
            @endif
            
            
            @if (!$user_registration or $eclass_stud_reg != 2)
                <div class='col-12 mt-4'>
                    <div class='alert alert-info'><i class='fa-solid fa-circle-info fa-lg'></i><span>
                        {{ trans('langStudentCannotRegister') }}</span>
                    </div>
                </div>
            @else
                @if (isset($_POST['submit']))
                    @if ($vmail)
                        <div class='col-sm-12 mt-4'><div class='alert alert-info'><i class='fa-solid fa-circle-info fa-lg'></i><span> {{ trans('langMailVerificationSuccess') }} {{ trans('langMailVerificationSuccess2') }} <br><br><small> {{ trans('langMailVerificationNote') }} </small> <br><br>{{ trans('langClick') }} <a href='{{ $urlServer }}' class='mainpage'>{{ trans('langHere') }}</a> {{ trans('langBackPage') }}</span></div></div>
                    @else
                        <div class='col-sm-12 mt-4'>
                            <div class='alert alert-success'><i class='fa-solid fa-circle-check fa-lg'></i><span>
                                <p>{{ $user_msg }}</p>
                                <p>{{ trans('langClick') }} <a href='../../'>{{ trans('langHere') }}</a> {{ trans('langPersonalSettingsMore') }}
                                <ul>
                                    <li>{{ trans('langPersonalSettingsMore1') }}</li>
                                    <li>{{ trans('langPersonalSettingsMore2') }}</li>
                                </ul>
                                </p></span>
                            </div>
                        </div>
                    @endif
                @else

                    <div class='col-12 mt-4'>
                        <div class='row row-cols-1 row-cols-lg-2 g-lg-5 g-4'>
                
                            <div class='col-lg-6 col-12'>
                                <div class='form-wrapper form-edit rounded px-0 border-0'>
                                    <form class='form-horizontal' role='form' action='newuser.php' method='post' onsubmit='return validateNodePickerForm();'>
                                        <fieldset>

                                            <div class='row'>
                                                <div class='col-lg-6 col-12 px-3'>
                                                    <div class='form-group'>
                                                        <label for='Name' class='col-sm-12 control-label-notes'>{{ trans('langName') }}</label>
                                                        <div class='col-sm-12'>
                                                            <input class='form-control' type='text' name='givenname_form' size='30' maxlength='100' value = '{{ $user_data_firstname }}'  placeholder='{{ trans('langName') }}...'>
                                                        </div>
                                                    </div>
                                                </div>
                                                <div class='col-lg-6 col-12 px-3'>
                                                    <div class='form-group mt-lg-0 mt-4'>
                                                        <label for='SurName' class='col-sm-12 control-label-notes'>{{ trans('langSurname') }}</label>
                                                        <div class='col-sm-12'>
                                                            <input class='form-control' type='text' name='surname_form' size='30' maxlength='100' value = '{{ $user_data_lastname }}' placeholder='{{ trans('langSurname') }}...'>
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>

                                            
                                            <div class='row'>
                                                <div class='col-lg-6 col-12 px-3'>
                                                    <div class='form-group mt-4'>
                                                        <label for='UserName' class='col-sm-12 control-label-notes'>{{ trans('langUsername') }}</label>
                                                        <div class='col-sm-12'>
                                                            <input class='form-control' type='text' name='uname' value = '{{ $user_data_displayName }}' accept="" size='30' maxlength='100' autocomplete='off' placeholder='{{ trans('langUserNotice') }}...'>
                                                        </div>
                                                    </div>
                                                </div>
                                                <div class='col-lg-6 col-12 px-3'>
                                                    <div class='form-group mt-4'>
                                                        <label for='UserPass' class='col-sm-12 control-label-notes'>{{ trans('langPass') }}</label>
                                                        <div class='col-sm-12'>
                                                            <input class='form-control' type='password' name='password1' size='30' maxlength='30' autocomplete='off' id='password' placeholder='{{ trans('langUserNotice') }}...'><span id='result'></span>
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>

                                            
                                            <div class='row'>
                                                <div class='col-lg-6 col-12 px-3'>
                                                    <div class='form-group mt-4'>
                                                        <label for='UserPass2' class='col-sm-12 control-label-notes'>{{ trans('langConfirmation') }}</label>
                                                        <div class='col-sm-12'>
                                                            <input class='form-control' type='password' name='password' size='30' maxlength='30' autocomplete='off'/>
                                                        </div>
                                                    </div>
                                                </div>
                                                <div class='col-lg-6 col-12 px-3'>
                                                    <div class='form-group mt-4'>
                                                        <label for='UserEmail' class='col-sm-12 control-label-notes'>{{ trans('langEmail') }}</label>
                                                        <div class='col-sm-12'>
                                                            <input class='form-control' type='text' name='email' size='30' maxlength='100' value = '{{ $user_data_email }}' placeholder='{{ trans('email_message') }}...'>
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>

                                            
                                            <div class='row'>
                                                
                                                <div class='col-lg-6 col-12 px-3'>
                                                    <div class='form-group mt-4'>
                                                        <label for='UserAm' class='col-sm-12 control-label-notes'>{{ trans('langAm') }}</label>
                                                        <div class='col-sm-12'>
                                                            <input class='form-control' type='text' name='am' size='20' maxlength='20' value = '{{ $user_data_am }}' placeholder='{{trans ('am_message') }}...'>
                                                        </div>
                                                    </div>
                                                </div>
                                                
                                                <div class='col-lg-6 col-12 px-3'>
                                                    <div class='form-group mt-4'>
                                                        <label for='UserPhone' class='col-sm-6 control-label-notes'>{{ trans('langPhone') }}</label>
                                                        <div class='col-sm-12'>
                                                            <input class='form-control' type='text' name='phone' size='20' maxlength='20' value = '{{ $user_data_phone }}' placeholder='{{ trans('langOptional') }}...'>
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>

                                        
                                            <div class='row'>
                                                
                                                <div class='col-lg-6 col-12 px-3'>
                                                    <div class='form-group mt-4'>
                                                    <label for='UserFac' class='col-sm-12 control-label-notes'>{{ trans('langFaculty') }}</label>
                                                        <div class='col-sm-12'>
                                                            {!! $buildusernode !!}
                                                        </div>
                                                    </div>
                                                </div>
                                               

                                                <div class='col-lg-6 col-12 px-3'>
                                                    <div class='form-group mt-4'>
                                                        <label for='UserLang' class='col-sm-12 control-label-notes'>{{ trans('langLanguage') }}</label>
                                                        <div class='col-sm-12'>
                                                            {!! $lang_select_options !!}
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>



                                            @if ($display_captcha)
                                                <div class='row'>
                                                    <div class='col-lg-6 col-12 px-3'>
                                                        <div class='form-group mt-4'>
                                                            <label for='captcha_code' class='col-sm-12 control-label-notes'>{{ trans('langCaptcha') }}</label>
                                                            <div class='col-sm-12'>{!! $captcha !!}</div>
                                                        </div>
                                                    </div>
                                                </div>
                                            @endif
                                            <!-- add custom profile fields -->

                                            <div class='row'>{!! $render_profile_fields_form !!}</div>

                                            <!-- check if provider_id from an authenticated user and a valid provider name are set so as to show the relevant form -->
                                            @if(!empty($provider_name) && !empty($provider_id))
                                            
                                                <div class='row'>
                                                    <div class='col-lg-6 col-12 px-3'>
                                                        <div class='form-group mt-4'>
                                                            <label for='UserLang' class='col-sm-12 control-label-notes'>{{ trans('langProviderConnectWith') }}</label>
                                                            <div class='col-sm-12'><p class='form-control-static'>
                                                                <img src='$themeimg/" . q($provider_name) . ".png' alt='" . q($provider_name) . "'>&nbsp;" . q(ucfirst($provider_name)) . "<br /><small>{{ trans('langProviderConnectWithTooltip') }}</small></p>
                                                            </div>
                                                            <input type='hidden' name='provider' value= ' {{ $provider_name }}'>
                                                            <input type='hidden' name='provider_id' value=' {{ $provider_id }}'>
                                                        </div>
                                                    </div>
                                                </div>
                                            @endif

                                            
                                            <div class='row'>
                                                <div class='col-12 px-3'>
                                                    <div class='form-group mt-5'>
                                                    
                                                        <input class='btn w-100' type='submit' name='submit' value='{{ trans('langRegistration') }}'>
                                                    </div>
                                                </div>
                                            </div>

                                        </fieldset>
                                    </form>
                                </div>
                            </div>

                            <div class='col-lg-6 col-12'>
                                <img class='form-image' src='{{ $urlAppend }}template/modern/img/RegImg.png' />
                            </div>

                        </div>
                    </div>

                @endif
            @endif
               
        </div>
    </div>
</div>
@endsection
