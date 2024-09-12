@extends('layouts.default')
@push('head_styles')
    <link href="{{ $urlAppend }}js/jstree3/themes/proton/style.min.css" type='text/css' rel='stylesheet'>
@endpush

@push('head_scripts')
    <script type='text/javascript' src='{{ $urlAppend }}js/jstree3/jstree.min.js'></script>
    <script type="text/javascript" src="{{ $urlAppend }}js/pwstrength.js"></script>
    <script type="text/javascript">
        var lang = {
            pwStrengthTooShort: '{!! js_escape(trans('langPwStrengthTooShort')) !!}',
            pwStrengthWeak: '{!! js_escape(trans('langPwStrengthWeak')) !!}',
            pwStrengthGood: '{!! js_escape(trans('langPwStrengthGood')) !!}',
            pwStrengthStrong: '{!! js_escape(trans('langPwStrengthStrong')) !!}',
        };

        $(document).ready(function() {
            $('#password').keyup(function() {
                $('#result').html(checkStrength($('#password').val()))
            });
        });

    </script>
@endpush

@section('content')

<div class="col-12 main-section">
    <div class='{{ $container }} main-container'>
        <div class="row m-auto">

            <div class='col-12'>
                <h1>{!! $toolName !!}</h1>
            </div>

            @include('layouts.partials.show_alert') 

            @if (!$user_registration)
                <div class='col-12 mt-4'>
                    <div class='alert alert-info'><i class='fa-solid fa-circle-info fa-lg'></i><span>
                        {{ trans('langStudentCannotRegister') }}</span>
                    </div>
                </div>
            @else
                @if (isset($_POST['submit']))
                    @if ($email_errors)
                        <div class='alert alert-warning'>
                            {{ trans('langMailErrorMessage') }}&nbsp;{{ trans('emailhelpdesk') }}.
                        </div>
                    @endif
                    @if ($vmail)
                        <div class='col-sm-12 mt-4'>
                            <div class='alert alert-info'>
                                <i class='fa-solid fa-circle-info fa-lg'></i><span> {{ trans('langMailVerificationSuccess') }} {{ trans('langMailVerificationSuccess2') }}
                                    <br><br><small> {{ trans('langMailVerificationNote') }} </small>
                                    <br><br>{{ trans('langClick') }} <a href='{{ $urlServer }}' class='mainpage'>{{ trans('langHere') }}</a> {{ trans('langBackPage') }}</span>
                            </div>
                        </div>
                    @else
                        @if ($eclass_stud_reg == 2)
                            <div class='col-sm-12 mt-4'>
                                <div class='alert alert-success'><i class='fa-solid fa-circle-check fa-lg'></i></div>
                                    <p>{{ $user_msg }}</p>
                                    <p>{{ trans('langClick') }} <a href='../../'>{{ trans('langHere') }}</a> {{ trans('langPersonalSettingsMore') }}
                                        <ul>
                                            <li>{{ trans('langPersonalSettingsMore1') }}</li>
                                            <li>{{ trans('langPersonalSettingsMore2') }}</li>
                                        </ul>
                                    </p>
                            </div>
                        @elseif ($eclass_stud_reg == 1)
                            <div class='col-sm-12 mt-4'>
                                <div class='alert alert-success'>{{ trans('langRequestSuccess') }}</div>
                            </div>
                        @endif
                    @endif
                @else
                    <div class='col-12 mt-4'>
                        <div class='row row-cols-1 row-cols-lg-2 g-lg-5 g-4'>

                            <div class='col-lg-6 col-12'>
                                <div class='form-wrapper form-edit rounded px-0 border-0'>
                                    <form class='form-horizontal' role='form' action='newuser.php' method='post' onsubmit='return validateNodePickerForm();'>

                                            <div class='row'>
                                                <div class='col-lg-6 col-12 px-3'>
                                                    <div class='form-group'>
                                                        <label for='NameID' class='col-sm-12 control-label-notes'>{{ trans('langName') }} <span class='asterisk Accent-200-cl'>(*)</span></label>
                                                        <div class='col-sm-12'>
                                                            <input id="NameID" class='form-control' type='text' name='givenname_form' size='30' maxlength='100' value = '{{ $user_data_firstname }}'  placeholder='{{ trans('langName') }}...'>
                                                        </div>
                                                    </div>
                                                </div>
                                                <div class='col-lg-6 col-12 px-3'>
                                                    <div class='form-group mt-lg-0 mt-4'>
                                                        <label for='SurNameID' class='col-sm-12 control-label-notes'>{{ trans('langSurname') }} <span class='asterisk Accent-200-cl'>(*)</span></label>
                                                        <div class='col-sm-12'>
                                                            <input id="SurNameID" class='form-control' type='text' name='surname_form' size='30' maxlength='100' value = '{{ $user_data_lastname }}' placeholder='{{ trans('langSurname') }}...'>
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>

                                            <div class='row'>
                                                <div class='col-lg-6 col-12 px-3'>
                                                    <div class='form-group mt-4'>
                                                        <label for='UserNameID' class='col-sm-12 control-label-notes'>{{ trans('langUsername') }} <span class='asterisk Accent-200-cl'>(*)</span></label>
                                                        <div class='col-sm-12'>
                                                            <input id="UserNameID" class='form-control' type='text' name='uname' value = '{{ $user_data_displayName }}' accept="" size='30' maxlength='100' autocomplete='off' placeholder='{{ trans('langUserNotice') }}...'>
                                                        </div>
                                                    </div>
                                                </div>
                                                <div class='col-lg-6 col-12 px-3'>
                                                    <div class='form-group mt-4'>
                                                        <label for='UserEmailID' class='col-sm-12 control-label-notes'>{{ trans('langEmail') }}</label>
                                                        <div class='col-sm-12'>
                                                            <input id="UserEmailID" class='form-control' type='text' name='email' size='30' maxlength='100' value = '{{ $user_data_email }}' placeholder='{{ trans('email_message') }}...'>
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>

                                            @if ($eclass_stud_reg == 2)
                                                <div class='row'>
                                                    <div class='col-lg-6 col-12 px-3'>
                                                        <div class='form-group mt-4'>
                                                            <label for='UserPassID' class='col-sm-12 control-label-notes'>{{ trans('langPass') }} <span class='asterisk Accent-200-cl'>(*)</span></label>
                                                            <div class='col-sm-12'>
                                                                <input id="UserPassID" class='form-control' type='password' name='password1' size='30' maxlength='30' autocomplete='off' id='password' placeholder='{{ trans('langUserNotice') }}...'><span id='result'></span>
                                                            </div>
                                                        </div>
                                                    </div>

                                                    <div class='col-lg-6 col-12 px-3'>
                                                        <div class='form-group mt-4'>
                                                            <label for='UserPass2ID' class='col-sm-12 control-label-notes'>{{ trans('langConfirmation') }}</label>
                                                            <div class='col-sm-12'>
                                                                <input id="UserPass2ID" class='form-control' type='password' name='password' size='30' maxlength='30' autocomplete='off'/>
                                                            </div>
                                                        </div>
                                                    </div>
                                                </div>
                                            @endif

                                            <div class='row'>

                                                <div class='col-lg-6 col-12 px-3'>
                                                    <div class='form-group mt-4'>
                                                        <label for='UserAmID' class='col-sm-12 control-label-notes'>{{ trans('langAm') }}</label>
                                                        <div class='col-sm-12'>
                                                            <input id="UserAmID" class='form-control' type='text' name='am' size='20' maxlength='20' value = '{{ $user_data_am }}' placeholder='{{trans ('am_message') }}...'>
                                                        </div>
                                                    </div>
                                                </div>

                                                <div class='col-lg-6 col-12 px-3'>
                                                    <div class='form-group mt-4'>
                                                        <label for='UserPhoneID' class='col-sm-6 control-label-notes'>{{ trans('langPhone') }}</label>
                                                        <div class='col-sm-12'>
                                                            <input id="UserPhoneID" class='form-control' type='text' name='phone' size='20' maxlength='20' value = '{{ $user_data_phone }}' placeholder='{{ trans('langOptional') }}...'>
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>


                                            <div class='row'>

                                                <div class='col-lg-6 col-12 px-3'>
                                                    <div class='form-group mt-4'>
                                                    <label for='dialog-set-value' class='col-sm-12 control-label-notes'>{{ trans('langFaculty') }} <span class='asterisk Accent-200-cl'>(*)</span></label>
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

                                            @if ($eclass_stud_reg == 1)
                                                <div class='row'>
                                                    <div class='col-12 px-3'>
                                                        <div class='form-group mt-4'>
                                                            <label for='ProfComments' class='col-sm-12 control-label-notes'>{{ trans('langComments') }}</label>
                                                            <div class='col-sm-12'>
                                                                <textarea id='ProfComments' class='form-control' name='usercomment' cols='30' rows='4' placeholder='{{ trans('langReason') }}...'></textarea>
                                                            </div>
                                                        </div>
                                                    </div>
                                                </div>
                                            @endif

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
                                                                <img src='{{ $themeimg }}/{{ $provider_name }}.png' alt='{{ ($provider_name) }}'>&nbsp;{{ q(ucfirst($provider_name))  }}<br /><small>{{ trans('langProviderConnectWithTooltip') }}</small></p>
                                                            </div>
                                                            <input type='hidden' name='provider' value= '{{ $provider_name }}'>
                                                            <input type='hidden' name='provider_id' value='{{ $provider_id }}'>
                                                        </div>
                                                    </div>
                                                </div>
                                            @endif


                                            <div class='row'>
                                                <div class='col-12 px-3'>
                                                    <div class='form-group mt-5'>
                                                        @if ($eclass_stud_reg == 2)
                                                            <input class='btn w-100 secondary-submit' type='submit' name='submit' value='{{ trans('langRegistration') }}'>
                                                        @else
                                                            <input class='btn w-100 secondary-submit' type='submit' name='submit' value='{{ trans('langSubmit') }}'>
                                                        @endif
                                                    </div>
                                                </div>
                                            </div>

                                            @if ($eclass_stud_reg == 1)
                                                <input type="hidden" name="account_request" value="1">
                                            @endif

                                    </form>
                                </div>
                            </div>

                            <div class='col-lg-6 col-12 d-none d-lg-block'>
                                <img class='form-image-modules' src='{!! get_registration_form_image() !!}' alt='{{ trans('langRegistration') }}'>
                            </div>
                        </div>
                    </div>

                @endif
            @endif

        </div>
    </div>
</div>
@endsection
