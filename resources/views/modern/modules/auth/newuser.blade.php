@extends('layouts.default')

@section('content')

<div class="pb-lg-3 pt-lg-3 pb-0 pt-0">

    <div class="container-fluid main-container">

        <div class="row rowMedium">

            <div class="col-xl-12 col-lg-12 col-md-12 col-sm-12 col-12 justify-content-center col_maincontent_active_Homepage">
                    
                <div class="row p-lg-5 p-md-5 ps-1 pe-2 pt-5 pb-5">

                    @include('layouts.common.breadcrumbs', ['breadcrumbs' => $breadcrumbs])

                    @include('layouts.partials.legend_view',['is_editor' => $is_editor, 'course_code' => $course_code])

                    @if(Session::has('message'))
                    <div class='col-xxl-12 col-xl-12 col-lg-12 col-md-12 col-sm-12 col-12 all-alerts'>
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
                    
                    {!! $action_bar !!}
                    @if (!$user_registration or $eclass_stud_reg != 2)
                        <div class='col-sm-12'>
                            <div class='alert alert-info'>
                                {{ trans('langStudentCannotRegister') }}
                            </div>
                        </div>
                    @else
                        @if (isset($_POST['submit']))
                            @if ($vmail)
                                <div class='col-sm-12'><div class='alert alert-info'> {{ trans('langMailVerificationSuccess') }} {{ trans('langMailVerificationSuccess2') }} <br><br><small> {{ trans('langMailVerificationNote') }} </small> <br><br>{{ trans('langClick') }} <a href='{{ $urlServer }}' class='mainpage'>{{ trans('langHere') }}</a> {{ trans('langBackPage') }}</div></div>
                            @else
                                <div class='col-sm-12'>
                                    <div class='alert alert-success'>
                                        <p>{{ $user_msg }}</p>
                                        <p>{{ trans('langClick') }} <a href='../../'>{{ trans('langHere') }}</a> {{ trans('langPersonalSettingsMore') }}
                                        <ul>
                                            <li>{{ trans('langPersonalSettingsMore1') }}</li>
                                            <li>{{ trans('langPersonalSettingsMore2') }}</li>
                                        </ul>
                                        </p>
                                    </div>
                                </div>
                            @endif
                        @else

                        <div class='col-lg-6 col-12 d-none d-md-none d-lg-block'>
                            <div class='col-12 h-100 left-form'></div>
                        </div>
                        <div class='col-lg-6 col-12'>
                            <div class='form-wrapper shadow-sm p-3 rounded'>
                           
                            <form class='form-horizontal' role='form' action='newuser.php' method='post' onsubmit='return validateNodePickerForm();'>
                            <fieldset>
                            <div class='form-group'>
                                <label for='Name' class='col-sm-12 control-label-notes'>{{ trans('langName') }}</label>
                                <div class='col-sm-12'>
                                    <input class='form-control' type='text' name='givenname_form' size='30' maxlength='100' value = '{{ $user_data_firstname }}'  placeholder='{{ trans('langName') }}...'>
                                </div>
                            </div>

                            

                            <div class='form-group mt-3'>
                                <label for='SurName' class='col-sm-12 control-label-notes'>{{ trans('langSurname') }}</label>
                                <div class='col-sm-12'>
                                    <input class='form-control' type='text' name='surname_form' size='30' maxlength='100' value = '{{ $user_data_lastname }}' placeholder='{{ trans('langSurname') }}...'>
                                </div>
                            </div>

                            

                            <div class='form-group mt-3'>
                                <label for='UserName' class='col-sm-12 control-label-notes'>{{ trans('langUsername') }}</label>
                                <div class='col-sm-12'>
                                    <input class='form-control' type='text' name='uname' value = '{{ $user_data_displayName }}' accept="" size='30' maxlength='100' autocomplete='off' placeholder='{{ trans('langUserNotice') }}...'>
                                </div>
                            </div>

                            

                            <div class='form-group mt-3'>
                                <label for='UserPass' class='col-sm-12 control-label-notes'>{{ trans('langPass') }}</label>
                                <div class='col-sm-12'>
                                    <input class='form-control' type='password' name='password1' size='30' maxlength='30' autocomplete='off' id='password' placeholder='{{ trans('langUserNotice') }}...'><span id='result'></span>
                                </div>
                            </div>

                            

                            <div class='form-group mt-3'>
                            <label for='UserPass2' class='col-sm-12 control-label-notes'>{{ trans('langConfirmation') }}</label>
                                <div class='col-sm-12'>
                                    <input class='form-control' type='password' name='password' size='30' maxlength='30' autocomplete='off'/>
                                </div>
                            </div>

                            

                            <div class='form-group mt-3'>
                                <label for='UserEmail' class='col-sm-12 control-label-notes'>{{ trans('langEmail') }}</label>
                                <div class='col-sm-12'>
                                    <input class='form-control' type='text' name='email' size='30' maxlength='100' value = '{{ $user_data_email }}' placeholder='{{ trans('email_message') }}...'>
                                </div>
                            </div>

                            

                            <div class='form-group mt-3'>
                                <label for='UserAm' class='col-sm-12 control-label-notes'>{{ trans('langAm') }}</label>
                                <div class='col-sm-12'>
                                    <input class='form-control' type='text' name='am' size='20' maxlength='20' value = '{{ $user_data_am }}' placeholder='{{trans ('am_message') }}...'>
                                </div>
                            </div>

                            

                            <div class='form-group mt-3'>
                                <label for='UserPhone' class='col-sm-6 control-label-notes'>{{ trans('langPhone') }}</label>
                                <div class='col-sm-12'>
                                    <input class='form-control' type='text' name='phone' size='20' maxlength='20' value = '{{ $user_data_phone }}' placeholder='{{ trans('langOptional') }}...'>
                                </div>
                            </div>

                           

                            <div class='form-group mt-3'>
                            <label for='UserFac' class='col-sm-12 control-label-notes'>{{ trans('langFaculty') }}</label>
                                <div class='col-sm-12'>
                                    {!! $buildusernode !!}
                                </div>
                            </div>

                            

                            <div class='form-group mt-3'>
                            <label for='UserLang' class='col-sm-12 control-label-notes'>{{ trans('langLanguage') }}</label>
                                <div class='col-sm-12'>
                                    {!! $lang_select_options !!}
                                </div>
                            </div>
                            @if ($display_captcha)
                            
                                <div class='form-group mt-3'>
                                    <label for='captcha_code' class='col-sm-12 control-label-notes'>{{ trans('langCaptcha') }}</label>
                                    <div class='col-sm-12'>{!! $captcha !!}</div>
                                </div>
                            @endif
                            <!-- add custom profile fields -->

                            {!! $render_profile_fields_form !!}

                            <!-- check if provider_id from an authenticated user and a valid provider name are set so as to show the relevant form -->
                            @if(!empty($provider_name) && !empty($provider_id))
                            
                                <div class='form-group mt-3'>
                                <label for='UserLang' class='col-sm-12 control-label-notes'>{{ trans('langProviderConnectWith') }}</label>
                                <div class='col-sm-12'><p class='form-control-static'>
                                    <img src='$themeimg/" . q($provider_name) . ".png' alt='" . q($provider_name) . "'>&nbsp;" . q(ucfirst($provider_name)) . "<br /><small>{{ trans('langProviderConnectWithTooltip') }}</small></p>
                                </div>

                               

                                <div class='col-sm-12 mt-3'>
                                    <input type='hidden' name='provider' value= ' {{ $provider_name }}'>
                                    <input type='hidden' name='provider_id' value=' {{ $provider_id }}'>
                                </div>
                                </div>
                            @endif

                            
                            
                            <div class='form-group mt-5'>
                                <div class='col-12'>
                                    <input class='btn btn-sm btn-primary submitAdminBtn w-100' type='submit' name='submit' value='{{ trans('langRegistration') }}'>
                                </div>
                            </div>
                        </fieldset>
                        </form>
                        </div></div>
                        @endif
                    @endif
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
