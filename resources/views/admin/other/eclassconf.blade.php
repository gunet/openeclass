@extends('layouts.default')

@section('content')

    <div class="col-12 main-section">
        <div class='{{ $container }} main-container'>

            <div class="row m-auto">

                @include('layouts.common.breadcrumbs', ['breadcrumbs' => $breadcrumbs])

                @include('layouts.partials.legend_view')

                <div class='mt-4'></div>

                @include('layouts.partials.show_alert')

                @if (Session::get('scheduleIndexing'))
                    <!--schedule indexing if necessary-->
                    <div class='col-sm-12'>
                        <div class='alert alert-warning'>
                            <i class='fa-solid fa-triangle-exclamation fa-lg'></i>
                            <span>
                                {{ trans('langIndexingNeeded') }}
                                <a id='idxpbut' href='../search/idxpopup.php' onclick="return idxpopup('../search/idxpopup.php', 600, 500)">
                                {{ trans('langHere') }}.
                                </a>
                            </span>
                        </div>
                    </div>
                @endif

                <div class='col-xl-3 col-lg-4 col-md-0 col-sm-0 col-0 float-end mb-4' id=''>


                    <div class="sticky-top" style="z-index: 1;top:5rem;">
                        <div id="navbar-card-affixed" class="card card-affixed flex-column align-items-stretch p-3 " style="z-index:0;">
                            <nav class="nav nav-pills flex-column flex-xs-row flex-sm-row flex-md-row">
                                <a class="nav-link nav-link-adminTools Neutral-900-cl active" data-menuID="one" href="#">{{ trans('langBasicCfgSetting') }}</a>
                                <a class="nav-link nav-link-adminTools Neutral-900-cl" data-menuID="two" href="#">{{ trans('langUpgReg') }}</a>
                                <a class="nav-link nav-link-adminTools Neutral-900-cl" data-menuID="three" href="#">{{ trans('langSupportedLanguages') }}</a>

                                <a class="nav-link nav-link-adminTools Neutral-900-cl" data-menuID="five" href="#">{{ trans('langEmailSettings') }}</a>
                                <a class="nav-link nav-link-adminTools Neutral-900-cl" data-menuID="six" href="#">{{ trans('langCourseSettings') }}</a>
                                @if((isset($collaboration_platform) and !$collaboration_platform) or is_null($collaboration_platform))
                                    <a class="nav-link nav-link-adminTools Neutral-900-cl" data-menuID="seven" href="#">{{ trans('langMetaCommentary') }}</a>
                                @endif
                                <a class="nav-link nav-link-adminTools Neutral-900-cl" data-menuID="eight" href="#">{{ trans('langOtherOptions') }}</a>
                                <a class="nav-link nav-link-adminTools Neutral-900-cl" data-menuID="nine" href="#">{{ trans('langDocumentSettings') }}</a>
                                <a class="nav-link nav-link-adminTools Neutral-900-cl" data-menuID="ten" href="#">{{ trans('langDefaultQuota') }}</a>
                                <a class="nav-link nav-link-adminTools Neutral-900-cl" data-menuID="eleven" href="#">{{ trans('langUploadWhitelist') }}</a>
                                <a class="nav-link nav-link-adminTools Neutral-900-cl" data-menuID="twelve" href="#">{{ trans('langLogActions') }}</a>
                                <a class="nav-link nav-link-adminTools Neutral-900-cl" data-menuID="thirteen" href="#">{{ trans('langLoginFailCheck') }}</a>

                                <a class="nav-link nav-link-adminTools Neutral-900-cl" data-menuID="fourteen" href="#">{{ trans('langMaintenance') }}</a>
                            </nav>

                        </div>

                    </div>

                </div>

                <div class='col-xl-9 col-lg-8 col-md-12 col-sm-12 col-12 forms-panels-admin'>
                    <form class='form-horizontal' role='form' action='{{ $_SERVER['SCRIPT_NAME'] }}' method='post'>
                        <div data-bs-spy="scroll" data-bs-target="#navbar-card-affixed" data-bs-offset="0" tabindex="0">
                            <div class='card panelCard card-default px-lg-4 py-lg-3' data-id="one" id='one'>
                                <div class='card-header border-0 d-flex justify-content-between align-items-center'>
                                    <h3>{{ trans('langBasicCfgSetting') }}</h3>

                                </div>
                                <div class='card-body'>
                                    <fieldset>
                                        <legend class='mb-0' aria-label="{{ trans('langForm') }}"></legend>
                                        <div class='form-group'>
                                            <label for='formurlServer' class='col-sm-6 col-sm-offset-1 control-label-notes'>{{ trans('langSiteUrl') }}:</label>
                                            <div class='col-sm-12 col-md-12'>
                                                <input class='form-control form-control-admin' type='text' name='formurlServer' id='formurlServer' value='{{ $urlServer }}'>
                                            </div>
                                        </div>
                                        <div class='form-group mt-4'>
                                            <label for='formphpMyAdminURL' class='col-sm-12 control-label-notes'>{{ trans('langphpMyAdminURL') }}:</label>
                                            <div class='col-sm-12'>
                                                <input class='form-control form-control-admin' type='text' name='formphpMyAdminURL' id='formphpMyAdminURL' value='{{ get_config('phpMyAdminURL') }}'>
                                            </div>
                                        </div>
                                        <div class='form-group mt-4'>
                                            <label for='formphpSysInfoURL' class='col-sm-12 control-label-notes'>{{ trans('langSystemInfoURL') }}:</label>
                                            <div class='col-sm-12'>
                                                <input class='form-control form-control-admin' type='text' name='formphpSysInfoURL' id='formphpSysInfoURL' value='{{ get_config('phpSysInfoURL') }}'>
                                            </div>
                                        </div>
                                        <div class='form-group mt-4'>
                                            <label for='formemailAdministrator' class='col-sm-12 control-label-notes'>{{ trans('langAdminEmail') }}:</label>
                                            <div class='col-sm-12'>
                                                <input class='form-control form-control-admin' type='text' name='formemailAdministrator' id='formemailAdministrator' value='{{ get_config('email_sender') }}'>
                                            </div>
                                        </div>
                                        <div class='form-group mt-4'>
                                            <label for='formadministratorName' class='col-sm-12 control-label-notes'>{{ trans('langDefaultAdminName') }}:</label>
                                            <div class='col-sm-12'>
                                                <input class='form-control form-control-admin' type='text' name='formadministratorName' id='formadministratorName' value='{{ get_config('admin_name') }}'>
                                            </div>
                                        </div>
                                        <div class='form-group mt-4'>
                                            <label for='formsiteName' class='col-sm-12 control-label-notes'>{{ trans('langCampusName') }}:</label>
                                            <div class='col-sm-12'>
                                                <input class='form-control form-control-admin' type='text' name='formsiteName' id='formsiteName' value='{{ get_config('site_name') }}'>
                                            </div>
                                        </div>
                                        <div class='form-group mt-4'>
                                            <label for='formInstitution' class='col-sm-12 control-label-notes'>{{ trans('langInstituteShortName') }}:</label>
                                            <div class='col-sm-12'>
                                                <input class='form-control form-control-admin' type='text' name='formInstitution' id='formInstitution' value='{{ get_config('institution') }}'>
                                            </div>
                                        </div>
                                        <div class='form-group mt-4'>
                                            <label for='formInstitutionUrl' class='col-sm-12 control-label-notes'>{{ trans('langInstituteName') }}:</label>
                                            <div class='col-sm-12'>
                                                <input class='form-control form-control-admin' type='text' name='formInstitutionUrl' id='formInstitutionUrl' value='{{ get_config('institution_url') }}'>
                                            </div>
                                        </div>
                                    </fieldset>
                                </div>
                            </div>

                            <div class='card panelCard card-default px-lg-4 py-lg-3 d-none' data-id="two" id='two'>
                                <div class='card-header border-0 d-flex justify-content-between align-items-center'>
                                    <h3>{{ trans('langUpgReg') }}</h3>
                                </div>
                                <div class='card-body'>
                                    <fieldset>
                                        <legend class='mb-0' aria-label="{{ trans('langForm') }}"></legend>
                                        <div class='form-group'>
                                            <label for='user_registration' class='col-sm-12 control-label-notes'>{{ trans('langUserRegistration') }}:</label>
                                            <div class='col-sm-12'>
                                                {!! selection(
                                                                [
                                                                '1' => trans('langActivate'),
                                                                '0' => trans('langDeactivate')
                                                                ],
                                                                'user_registration',
                                                                get_config('user_registration'),
                                                                "class='form-control form-control-admin' id='user_registration'"
                                                            ) !!}
                                            </div>
                                        </div>
                                        <div class='form-group mt-4'>
                                            <label for='registration_link' class='col-sm-12 control-label-notes'>{{ trans('langRegistrationLink') }}:</label>
                                            <div class='col-sm-12'>
                                                {!! selection(
                                                                $registration_link_options,
                                                                'registration_link',
                                                                get_config('registration_link', 'show'),
                                                                "class='form-control form-control-admin' id='registration_link'"
                                                            ) !!}
                                            </div>
                                        </div>
                                        <div class='form-group mt-4' id='registration-info-block'>
                                            <label for='registration_info' class='col-sm-12 control-label-notes'>{{ trans('langRegistrationInfo') }}:</label>
                                            <div class='col-sm-12'>
                                                {!! $registration_info_textarea !!}
                                            </div>
                                        </div>
                                        <div class='form-group mt-4'>
                                            <label for='eclass_stud_reg' class='col-sm-12 control-label-notes'>{{ trans('langUserAccount') }}  {{ trans('langViaeClass') }}:</label>
                                            <div class='col-sm-12'>
                                                {!! selection(
                                                                [
                                                                    '0' => trans('langDisableEclassStudReg'),
                                                                    '1' => trans('langReqRegUser'),
                                                                    '2' => trans('langDisableEclassStudRegType')
                                                                ],
                                                                'eclass_stud_reg',
                                                                get_config('eclass_stud_reg'),
                                                                "class='form-control form-control-admin' id='eclass_stud_reg'"
                                                            ) !!}
                                            </div>
                                        </div>
                                        <div class='form-group mt-4'>
                                            <label for='alt_auth_stud_reg' class='col-sm-12 control-label-notes'>{{ trans('langUserAccount') }} {{ trans('langViaAltAuthMethods') }}:</label>
                                            <div class='col-sm-12'>
                                                {!! selection(
                                                            [
                                                                '0' => trans('langDisableEclassStudReg'),
                                                                '1' => trans('langReqRegUser'),
                                                                '2' => trans('langDisableEclassStudRegType')
                                                            ],
                                                            'alt_auth_stud_reg',
                                                            get_config('alt_auth_stud_reg'),
                                                            "class='form-control form-control-admin' id='alt_auth_stud_reg'"
                                                        ) !!}
                                            </div>
                                        </div>
                                        <div class='form-group mt-4'>
                                            <label for='eclass_prof_reg' class='col-sm-12 control-label-notes'>{{ trans('langProfAccount') }} {{ trans('langViaeClass') }}:</label>
                                            <div class='col-sm-12'>
                                                {!! selection(
                                                    array(
                                                        '0' => trans('langDisableEclassProfReg'),
                                                        '1' => trans('langReqRegProf')
                                                    ),
                                                    'eclass_prof_reg',
                                                    get_config('eclass_prof_reg'),
                                                    "class='form-control form-control-admin' id='eclass_prof_reg'"
                                                ) !!}
                                            </div>
                                        </div>
                                        <div class='form-group mt-4'>
                                            <label for='alt_auth_prof_reg' class='col-sm-12 control-label-notes'>{{ trans('langProfAccount') }} {{ trans('langViaAltAuthMethods') }}:</label>
                                            <div class='col-sm-12'>
                                                {!! selection(
                                                        array(
                                                                '0' => trans('langDisableEclassProfReg'),
                                                                '1' => trans('langReqRegProf')
                                                            ),
                                                        'alt_auth_prof_reg',
                                                        get_config('alt_auth_prof_reg'),
                                                        "class='form-control form-control-admin' id='alt_auth_prof_reg'"
                                                    ) !!}
                                            </div>
                                        </div>
                                        <div class='form-group mt-4'>
                                            <div class='col-sm-12 control-label-notes mb-1'>{{ trans('lang_block_duration_account') }}:</div>
                                            <div class='checkbox col-sm-12'>
                                                <label class='label-container' aria-label="{{ trans('langSelect') }}">
                                                    <input type='checkbox' name='block_duration_account' value='1' {{ $cbox_block_duration_account }}>
                                                    <span class='checkmark'></span>
                                                    {{ trans('lang_message_block_duration_account') }}
                                                </label>
                                            </div>
                                        </div>



                                        <div class='form-group mt-4'>
                                            <div class='col-sm-12 control-label-notes mb-1'>{{ trans('lang_block_duration_alt_account') }}:</div>
                                            <div class='checkbox col-sm-12'>
                                                <label class='label-container' aria-label="{{ trans('langSelect') }}">
                                                    <input type='checkbox' name='block_duration_alt_account' value='1' {{ $cbox_block_duration_alt_account }}>
                                                    <span class='checkmark'></span>
                                                    {{ trans('lang_message_block_duration_account') }}
                                                </label>
                                            </div>
                                        </div>

                                        <div class='form-group mt-4'>
                                            <label for='formdurationAccount' class='col-sm-12 control-label-notes'>{{ trans('langUserDurationAccount') }} ({{ trans('langMonthsUnit') }}): </label>
                                            <div class='col-sm-12'>
                                                <input type='text' class='form-control form-control-admin' name='formdurationAccount' id='formdurationAccount' maxlength='3' value='{{ get_config('account_duration') / MONTHS }}'>
                                            </div>
                                        </div>


                                        <div class='form-group mt-4'>
                                            <div class='col-sm-12 control-label-notes mb-1'>{{ trans('lang_display_captcha_label') }}:</div>
                                            <div class='checkbox col-sm-12'>
                                                <label class='label-container' aria-label="{{ trans('langSelect') }}">
                                                    <input type='checkbox' name='display_captcha' value='1' {{ $cbox_display_captcha }} {{ $disable_display_captcha }}>
                                                    <span class='checkmark'></span>
                                                    {{ trans('lang_display_captcha') }}
                                                </label>
                                                {!! $message_display_captcha !!}
                                            </div>
                                        </div>

                                        <div class='form-group mt-4'>
                                            <div class='col-sm-12 control-label-notes mb-1'>{{ trans('langRequiredFieldUserRegistration') }}:</div>
                                            <div class='checkbox'>
                                                <label class='label-container' aria-label="{{ trans('langSelect') }}">
                                                    <input type='checkbox' name='am_required' value='1' {{ $cbox_am_required }}>
                                                    <span class='checkmark'></span>
                                                    {{ trans('langAm') }}
                                                </label>
                                            </div>

                                            <div class='checkbox'>
                                                <label class='label-container' aria-label="{{ trans('langSelect') }}">
                                                    <input type='checkbox' name='email_required' value='1' {{ $cbox_email_required }}>
                                                    <span class='checkmark'></span>
                                                    {{ trans('langEmail') }}
                                                </label>
                                            </div>
                                        </div>


                                        <div class='form-group mt-4'>
                                            <label for='GuestLoginId' class='col-sm-12 control-label-notes'>{{ trans('langGuestLoginLabel') }}:</label>
                                            <div class='col-sm-12'>
                                                {!! selection([
                                                    'off' => trans('langDeactivate'),
                                                    'on' => trans('langActivate'),
                                                    'link' => trans('langGuestLoginLinks')
                                                    ],
                                                    'course_guest',
                                                    get_config('course_guest', 'on'),
                                                    "class='form-control form-control-admin' id='GuestLoginId'"
                                                ) !!}
                                            </div>
                                        </div>


                                        <div class='form-group mt-4'>
                                            <div class='col-sm-12 control-label-notes'>{{ trans('langcourseExternalUsersInviation') }}:</div>
                                            <div class='checkbox col-sm-12'>
                                                <label class='label-container' aria-label="{{ trans('langSelect') }}">
                                                    <input type='checkbox' name='course_invitation' value='1' {{ $cbox_course_invitation }}>
                                                    <span class='checkmark'></span>
                                                    {{ trans('langCourseInvitationHelp') }}
                                                </label>
                                            </div>
                                        </div>

                                        <div class='form-group mt-4'>
                                            <div class='checkbox col-sm-12'>
                                                <div class='checkbox'>
                                                    <label class='label-container' aria-label="{{ trans('langSelect') }}">
                                                        <input type='checkbox' name='double_login_lock' value='1' {{ $cbox_double_login_lock }}>
                                                        <span class='checkmark'></span>
                                                        {{ trans('langDoubleLoginLockOption') }}
                                                    </label>
                                                </div>
                                            </div>
                                        </div>

                                    </fieldset>
                                </div>
                            </div>

                            <div class='card panelCard card-default px-lg-4 py-lg-3 d-none' data-id="three" id='three'>
                                <div class='card-header border-0 d-flex justify-content-between align-items-center'>
                                    <h3>{{ trans('langSupportedLanguages') }}</h3>
                                </div>
                                <div class='card-body'>
                                    <fieldset>
                                        <legend class='mb-0' aria-label="{{ trans('langForm') }}"></legend>
                                        <div class='form-group'>
                                            <label for='default_language' class='col-sm-12 control-label-notes'>{{ trans('langMainLang') }}: </label>
                                            <div class='col-sm-12'>
                                                {!! selection(
                                                            $selectable_langs,
                                                            'default_language',
                                                            get_config('default_language'),
                                                            "class='form-control form-control-admin' id='default_language'"
                                                        ) !!}
                                            </div>
                                        </div>


                                        <div class='form-group mt-4'>
                                            <div class='col-sm-12 control-label-notes mb-1'>{{ trans('langSupportedLanguages') }}:</div>
                                            <div class='col-sm-12'>
                                                {!! implode(' ', $sel) !!}
                                            </div>
                                        </div>
                                    </fieldset>
                                </div>
                            </div>


                            <div class='card panelCard card-default px-lg-4 py-lg-3 d-none' data-id="five" id='five'>
                                <div class='card-header border-0 d-flex justify-content-between align-items-center'>

                                    <h3>{{ trans('langEmailSettings') }}</h3>

                                </div>
                                <div class='card-body'>
                                    <fieldset><legend class='mb-0' aria-label="{{ trans('langForm') }}"></legend>

                                        <div class='form-group'>
                                            <div class='col-sm-12'>
                                                <div class='checkbox'>
                                                    <label class='label-container' aria-label="{{ trans('langSelect') }}">
                                                        <input type='checkbox' name='dont_mail_unverified_mails' value='1' {!! $cbox_dont_mail_unverified_mails !!}>
                                                        <span class='checkmark'></span>
                                                        {{ trans('lang_dont_mail_unverified_mails') }}
                                                    </label>
                                                </div>
                                                <div class='checkbox'>
                                                    <label class='label-container' aria-label="{{ trans('langSelect') }}">
                                                        <input type='checkbox' name='email_from' value='1' {!! $cbox_email_from !!}>
                                                        <span class='checkmark'></span>
                                                        {{ trans('lang_email_from') }}
                                                    </label>
                                                </div>
                                            </div>
                                        </div>

                                        <div class='form-group mt-4' id='formEmailAnnounceGroup'>
                                            <label for='formEmailAnnounce' class='col-sm-12 control-label-notes'>{{ trans('langEmailAnnounce') }}:</label>
                                            <div class='col-sm-12'>
                                                <input type='text' class='form-control' name='email_announce' id='formEmailAnnounce' value="{!! q(get_var('email_announce')) !!}">
                                                <span class='help-block' id='emailSendWarn'>{{ trans('langEmailSendWarn') }}</span>
                                            </div>
                                        </div>

                                        <div class='form-group mt-4'>
                                            <label for='formEmailBounces' class='col-sm-12 control-label-notes'>{{ trans('langEmailBounces') }}:</label>
                                            <div class='col-sm-12'>
                                                <input type='text' class='form-control' name='email_bounces' id='formEmailBounces' value="{!! q(get_var('email_bounces')) !!}">
                                            </div>
                                        </div>

                                        <div class='form-group mt-4'>
                                            <label for='formEmailTransport' class='col-sm-12 control-label-notes'>{{ trans('langEmailTransport') }}:</label>
                                            <div class='col-sm-12'>
                                                {!! selection($emailTransports, 'email_transport', $email_transport,
                                                            "class='form-select' id='formEmailTransport'") !!}
                                            </div>
                                        </div>

                                        <div class='form-group SMTP-settings mt-4'>
                                            <label for='formSMTPServer' class='col-sm-12 control-label-notes'>{{ trans('langEmailSMTPServer') }}:</label>
                                            <div class='col-sm-12'>
                                                <input type='text' class='form-control' name='smtp_server' id='formSMTPServer' value="{!! q(get_var('smtp_server')) !!}">
                                            </div>
                                        </div>

                                        <div class='form-group SMTP-settings mt-4'>
                                            <label for='formSMTPPort' class='col-sm-12 control-label-notes'>{{trans('langEmailSMTPPort')}}:</label>
                                            <div class='col-sm-12'>
                                                <input type='text' class='form-control' name='smtp_port' id='formSMTPPort' value="{!! q(get_var('smtp_port', 25)) !!}">
                                            </div>
                                        </div>

                                        <div class='form-group SMTP-settings mt-4'>
                                            <label for='formEmailEncryption' class='col-sm-12 control-label-notes'>{{ trans('langEmailEncryption') }}:</label>
                                            <div class='col-sm-12'>
                                                {!! selection($emailEncryption, 'smtp_encryption', $smtp_encryption,
                                                            "class='form-select' id='formEmailEncryption'") !!}
                                            </div>
                                        </div>

                                        <div class='form-group SMTP-settings mt-4'>
                                            <label for='formSMTPUsername' class='col-sm-12 control-label-notes'>{{trans('langUsername')}}:</label>
                                            <div class='col-sm-12'>
                                                <input type='text' class='form-control' name='smtp_username' id='formSMTPUsername' value="{!! q(get_var('smtp_username')) !!}">
                                            </div>
                                        </div>

                                        <div class='form-group SMTP-settings mt-4'>
                                            <label for='formSMTPPassword' class='col-sm-12 control-label-notes'>{{trans('langPassword')}}:</label>
                                            <div class='col-sm-12'>
                                                <div class='input-group'>
                                                    <input type='password' class='form-control mt-0 border-end-0' name='smtp_password' id='formSMTPPassword' value="{!! q(get_var('smtp_password')) !!}">
                                                    <span id='revealPass' class='input-group-text input-group-addon h-40px bg-input-default input-border-color'><span class='fa-solid fa-eye'></span></span>
                                                </div>
                                            </div>
                                        </div>

                                        <div class='form-group Sendmail-settings mt-4'>
                                            <label for='formSendmailCommand' class='col-sm-12 control-label-notes'>{{trans('langEmailSendmail')}}:</label>
                                            <div class='col-sm-12'>
                                                <input type='text' class='form-control' name='sendmail_command' id='formSendmailCommand' value="{!! q(get_var('sendmail_command', ini_get('sendmail_path'))) !!}">
                                                <span class='help-text'>{{trans('langEG')}} <code>/usr/sbin/sendmail -t -i</code></span>
                                            </div>
                                        </div>

                                    </fieldset>
                                </div>
                            </div>

                            <div class='card panelCard card-default px-lg-4 py-lg-3 d-none' data-id="six" id='six'>
                                <div class='card-header border-0 d-flex justify-content-between align-items-center'>
                                    <h3>{{ trans('langCourseSettings') }}</h3>
                                </div>
                                <div class='card-body'>
                                    <fieldset>
                                        <legend class='mb-0' aria-label="{{ trans('langForm') }}"></legend>
                                        <div class='form-group'>
                                            <div class='col-sm-12'>
                                                <div class='checkbox'>
                                                    <label class='label-container' aria-label="{{ trans('langSelect') }}">
                                                        <input type='checkbox' name='course_multidep' value='1' {{ $cbox_course_multidep }}>
                                                        <span class='checkmark'></span>
                                                        {{ trans('lang_course_multidep') }}
                                                    </label>
                                                </div>
                                                <div class='checkbox'>
                                                    <label class='label-container' aria-label="{{ trans('langSelect') }}">
                                                        <input type='checkbox' name='user_multidep' value='1' {{ $cbox_user_multidep }}>
                                                        <span class='checkmark'></span>
                                                        {{ trans('lang_user_multidep') }}
                                                    </label>
                                                </div>
                                                <div class='checkbox'>
                                                    <label class='label-container' aria-label="{{ trans('langSelect') }}">
                                                        <input id='uown' type='checkbox' name='restrict_owndep' value='1' {{ $cbox_restrict_owndep }}>
                                                        <span class='checkmark'></span>
                                                        {{ trans('lang_restrict_owndep') }}
                                                    </label>
                                                </div>
                                                <div class='checkbox'>
                                                    <label class='label-container' aria-label="{{ trans('langSelect') }}">
                                                        <input id='town' type='checkbox' name='restrict_teacher_owndep' value='1' {{ $town_dis }} {{ $cbox_restrict_teacher_owndep }}>
                                                        <span class='checkmark'></span>
                                                        {{ trans('lang_restrict_teacher_owndep') }}
                                                    </label>
                                                </div>
                                                <div class='checkbox'>
                                                    <label class='label-container' aria-label="{{ trans('langSelect') }}">
                                                        <input type='checkbox' name='allow_teacher_clone_course' value='1' {{ $cbox_allow_teacher_clone_course }}>
                                                        <span class='checkmark'></span>
                                                        {{ trans('lang_allow_teacher_clone_course') }}
                                                    </label>
                                                </div>
                                                <div class='checkbox'>
                                                    <label class='label-container' aria-label="{{ trans('langSelect') }}">
                                                        <input type='checkbox' name='offline_course' value='1' {{ $cbox_offline_course }}>
                                                        <span class='checkmark'></span>
                                                        {{ trans('langCourseOfflineSettings') }}
                                                    </label>
                                                </div>
                                                <div class='checkbox'>
                                                    <label class='label-container' aria-label="{{ trans('langSelect') }}">
                                                        <input type='checkbox' name='allow_rec_video' value='1' {{ $cbox_allow_rec_video }}>
                                                        <span class='checkmark'></span>
                                                        {{ trans('lang_allow_rec_video') }}
                                                    </label>
                                                </div>
                                                <div class='checkbox'>
                                                    <label class='label-container' aria-label="{{ trans('langSelect') }}">
                                                        <input type='checkbox' name='allow_rec_audio' value='1' {{ $cbox_allow_rec_audio }}>
                                                        <span class='checkmark'></span>
                                                        {{ trans('lang_allow_rec_audio') }}
                                                    </label>
                                                </div>
                                                <div class='checkbox'>
                                                    <label class='label-container' aria-label="{{ trans('langSelect') }}">
                                                        <input id='disable_student_unregister_cours' type='checkbox' name='disable_student_unregister_cours' value='1' {{ $cbox_disable_student_unregister_cours }}>
                                                        <span class='checkmark'></span>
                                                        {{ trans('langUnsubscribeCourse') }}
                                                    </label>
                                                </div>
                                                <div class='checkbox'>
                                                    <label class='label-container' aria-label="{{ trans('langSelect') }}">
                                                        <input type='checkbox' name='show_modal_openCourses' value='1' {{ $cbox_allow_modal_courses }}>
                                                        <span class='checkmark'></span>
                                                        {{ trans('lang_openCourse_inModal') }}
                                                    </label>
                                                    <span class="help-block">{{ trans('lang_openCourse_inModal_Info') }}</span>
                                                </div>
                                                <div class='checkbox'>
                                                    <label class='label-container' aria-label="{{ trans('langSelect') }}">
                                                        <input type='checkbox' name='enable_user_consent' value='1' {{ $cbox_user_consent }}>
                                                        <span class='checkmark'></span>
                                                        {{ trans('lang_enable_user_consent') }}
                                                    </label>
                                                </div>

                                                <div class='form-group mt-1'>
                                                    <label for='default_course_access' class='col-sm-12 control-label-notes'>{{
                                                        trans('langDefaultCourseAccess')}}:</label>
                                                    <div class='col-sm-12'>
                                                        <select class="form-select" name="default_course_access">
                                                            <option value="{{ COURSE_CLOSED }}"
                                                                @if ($default_course_access === COURSE_CLOSED) selected @endif>{{
                                                                    trans('langTypeOpen') }}</option>
                                                            <option value="{{ COURSE_REGISTRATION }}"
                                                                @if ($default_course_access === COURSE_REGISTRATION) selected @endif>{{
                                                                    trans('langTypeRegistration') }}</option>
                                                            <option value="{{ COURSE_OPEN }}"
                                                                @if ($default_course_access === COURSE_OPEN) selected @endif>{{
                                                                    trans('langTypeClosed') }}</option>
                                                            <option value="{{ COURSE_INACTIVE }}"
                                                                @if ($default_course_access === COURSE_INACTIVE) selected @endif>{{
                                                                    trans('langTypeInactive') }}</option>
                                                        </select>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    </fieldset>
                                </div>
                            </div>

                            @if((isset($collaboration_platform) and !$collaboration_platform) or is_null($collaboration_platform))
                                <div class='card panelCard card-default px-lg-4 py-lg-3 d-none' data-id="seven" id='seven'>

                                    <div class='card-header border-0 d-flex justify-content-between align-items-center'>

                                        <h3>{{ trans('langMetaCommentary') }}</h3>

                                    </div>
                                    <div class='card-body'>
                                        <fieldset>
                                            <legend class='mb-0' aria-label="{{ trans('langForm') }}"></legend>
                                            <div class='form-group'>
                                                <div class='col-sm-12'>
                                                    <div class='checkbox'>
                                                        <label class='label-container' aria-label="{{ trans('langSelect') }}">
                                                            <input type='checkbox' name='insert_xml_metadata' value='1' {{ $cbox_insert_xml_metadata }}>
                                                            <span class='checkmark'></span>
                                                            {{ trans('lang_insert_xml_metadata') }}
                                                        </label>
                                                    </div>
                                                    <div class='checkbox'>
                                                        <label class='label-container' aria-label="{{ trans('langSelect') }}">
                                                            <input type='checkbox' id='course_metadata' name='course_metadata' value='1' {{ $cbox_course_metadata }}>
                                                            <span class='checkmark'></span>
                                                            {{ trans('lang_course_metadata') }}
                                                        </label>
                                                    </div>
                                                    <div class='checkbox'>
                                                        <label class='label-container' aria-label="{{ trans('langSelect') }}">
                                                            <input type='checkbox' id='opencourses_enable' name='opencourses_enable' value='1' {{ $cbox_opencourses_enable }}>
                                                            <span class='checkmark'></span>
                                                            {{ trans('lang_opencourses_enable') }}
                                                        </label>
                                                    </div>
                                                </div>
                                            </div>
                                        </fieldset>
                                    </div>
                                </div>
                            @endif

                            <div class='card panelCard card-default px-lg-4 py-lg-3 d-none' data-id="eight" id='eight'>
                                <div class='card-header border-0 d-flex justify-content-between align-items-center'>

                                    <h3>{{ trans('langOtherOptions') }}</h3>

                                </div>
                                <div class='card-body'>
                                    <fieldset>
                                        <legend class='mb-0' aria-label="{{ trans('langForm') }}"></legend>
                                        <div class='form-group'>
                                            <div class='col-sm-12'>
                                                <div class='checkbox'>
                                                    <label class='label-container' aria-label="{{ trans('langSelect') }}">
                                                        <input type='checkbox' name='case_insensitive_usernames' value='1' {{ $cbox_case_insensitive_usernames }}>
                                                        <span class='checkmark'></span>
                                                        {{ trans('langCaseInsensitiveUsername') }}
                                                    </label>
                                                </div>
                                                <div class='checkbox'>
                                                    <label class='label-container' aria-label="{{ trans('langSelect') }}">
                                                        <input type='checkbox' name='email_verification_required' value='1' {{ $cbox_email_verification_required }}>
                                                        <span class='checkmark'></span>
                                                        {{ trans('lang_email_verification_required') }}
                                                    </label>
                                                </div>

                                                <div class='checkbox'>
                                                    <label class='label-container' aria-label="{{ trans('langSelect') }}">
                                                        <input id='index_enable' type='checkbox' name='enable_indexing' value='1' {{ $cbox_enable_indexing }}>
                                                        <span class='checkmark'></span>
                                                        {{ trans('langEnableIndexing') }}
                                                    </label>
                                                </div>
                                                <div class='checkbox'>
                                                    <label class='label-container' aria-label="{{ trans('langSelect') }}">
                                                        <input id='search_enable' type='checkbox' name='enable_search' value='1' {{ $cbox_enable_search }}>
                                                        <span class='checkmark'></span>
                                                        {{ trans('langEnableSearch') }}
                                                    </label>
                                                </div>
                                                <div class='checkbox'>
                                                    <label class='label-container' aria-label="{{ trans('langSelect') }}">
                                                        <input type='checkbox' name='dropbox_allow_student_to_student' value='1' {{ $cbox_dropbox_allow_student_to_student }}>
                                                        <span class='checkmark'></span>
                                                        {{ trans('lang_dropbox_allow_student_to_student') }}
                                                    </label>
                                                </div>
                                                <div class='checkbox'>
                                                    <label class='label-container' aria-label="{{ trans('langSelect') }}">
                                                        <input type='checkbox' name='dropbox_allow_personal_messages' value='1' {{ $cbox_dropbox_allow_personal_messages }}>
                                                        <span class='checkmark'></span>
                                                        {{ trans('lang_dropbox_allow_personal_messages') }}
                                                    </label>
                                                </div>
                                                <div class='checkbox'>
                                                    <label class='label-container' aria-label="{{ trans('langSelect') }}">
                                                        <input id='personal_blog_enable' type='checkbox' name='personal_blog' value='1' {{ $cbox_personal_blog }}>
                                                        <span class='checkmark'></span>
                                                        {{ trans('lang_personal_blog') }}
                                                    </label>
                                                </div>
                                                <div class='checkbox'>
                                                    <label class='label-container' aria-label="{{ trans('langSelect') }}">
                                                        <input id='personal_blog_commenting_enable' type='checkbox' name='personal_blog_commenting' value='1' {{ $cbox_personal_blog_commenting }}>
                                                        <span class='checkmark'></span>
                                                        {{ trans('lang_personal_blog_commenting') }}
                                                    </label>
                                                </div>
                                                <div class='checkbox'>
                                                    <label class='label-container' aria-label="{{ trans('langSelect') }}">
                                                        <input id='personal_blog_rating_enable' type='checkbox' name='personal_blog_rating' value='1' {{ $cbox_personal_blog_rating }}>
                                                        <span class='checkmark'></span>
                                                        {{ trans('lang_personal_blog_rating') }}
                                                    </label>
                                                </div>
                                                <div class='checkbox'>
                                                    <label class='label-container' aria-label="{{ trans('langSelect') }}">
                                                        <input id='personal_blog_sharing_enable' type='checkbox' name='personal_blog_sharing' value='1' {{ $cbox_personal_blog_sharing }}>
                                                        <span class='checkmark'></span>
                                                        {{ trans('lang_personal_blog_sharing') }}
                                                    </label>
                                                </div>

                                                <div class='checkbox'>
                                                    <label class='label-container' aria-label="{{ trans('langSelect') }}">
                                                        <input id='eportfolio_enable' type='checkbox' name='eportfolio_enable' value='1' {{ $cbox_eportfolio_enable }}>
                                                        <span class='checkmark'></span>
                                                        {{ trans('lang_eportfolio_enable') }}
                                                    </label>
                                                </div>
                                                <div class='checkbox'>
                                                    <label class='label-container' aria-label="{{ trans('langSelect') }}">
                                                        <input type='checkbox' name='block_username_change' value='1' {{ $cbox_block_username_change }}>
                                                        <span class='checkmark'></span>
                                                        {{ trans('lang_block_username_change') }}
                                                    </label>
                                                </div>
                                                <div class='checkbox'>
                                                    <label class='label-container' aria-label="{{ trans('langSelect') }}">
                                                        <input type='checkbox' name='disable_name_surname_change' value='1' {{ $cbox_disable_name_surname_change }}>
                                                        <span class='checkmark'></span>
                                                        {{ trans('lang_disable_name_surname_change') }}
                                                    </label>
                                                </div>
                                                <div class='checkbox'>
                                                    <label class='label-container' aria-label="{{ trans('langSelect') }}">
                                                        <input type='checkbox' name='disable_email_change' value='1' {{ $cbox_disable_email_change }}>
                                                        <span class='checkmark'></span>
                                                        {{ trans('lang_disable_email_change') }}
                                                    </label>
                                                </div>
                                                <div class='checkbox'>
                                                    <label class='label-container' aria-label="{{ trans('langSelect') }}">
                                                        <input type='checkbox' name='disable_am_change' value='1' {{ $cbox_disable_am_change }}>
                                                        <span class='checkmark'></span>
                                                        {{ trans('lang_disable_am_change') }}
                                                    </label>
                                                </div>
                                                <div class='checkbox'>
                                                    <label class='label-container' aria-label="{{ trans('langSelect') }}">
                                                        <input {!! $id_enable_mobileapi !!} type='checkbox' name='enable_mobileapi' value='1' {{ $cbox_enable_mobileapi }}>
                                                        <span class='checkmark'></span>
                                                        {{ trans('lang_enable_mobileapi') }}
                                                    </label>
                                                </div>
                                                <div class='checkbox'>
                                                    <label class='label-container' aria-label="{{ trans('langSelect') }}">
                                                        <input id='social_sharing_links' type='checkbox' name='enable_social_sharing_links' value='1' {{ $cbox_enable_social_sharing_links }}>
                                                        <span class='checkmark'></span>
                                                        {{ trans('langEnableSocialSharingLiks') }}
                                                    </label>
                                                </div>
                                                <div class='checkbox'>
                                                    <label class='label-container' aria-label="{{ trans('langSelect') }}">
                                                        <input id='strong_passwords' type='checkbox' name='enable_strong_passwords' value='1' {{ $cbox_enable_strong_passwords }}>
                                                        <span class='checkmark'></span>
                                                        {{ trans('langEnableStrongPasswords') }}
                                                    </label>
                                                </div>
                                                <div class='checkbox'>
                                                    <label class='label-container' aria-label="{{ trans('langSelect') }}">
                                                        <input id='individual_group_bookings' type='checkbox' name='individual_group_bookings' value='1' {{ $cbox_individual_group_bookings }}>
                                                        <span class='checkmark'></span>
                                                        {{ trans('langIndividualGroupBookings') }}
                                                    </label>
                                                </div>
                                                <div class='checkbox'>
                                                    <label class='label-container' aria-label="{{ trans('langSelect') }}">
                                                        <input id='enable_quick_note' type='checkbox' name='enable_quick_note' value='1' {{ $cbox_enable_quick_note }}>
                                                        <span class='checkmark'></span>
                                                        {{ trans('langEnableQuickNote') }}
                                                    </label>
                                                </div>
                                            </div>
                                        </div>
                                        <hr>

                                        <div class='form-group mt-4'>
                                            <h5>{{ trans('langUserNotifications') }}</h5>
                                            <div class='form-group'>
                                                <div class='radio mb-2'>
                                                    <label>
                                                        <input type='radio' name='user_notifications' value='0' {!! $user_notifications0 !!}>
                                                        {{ trans('langDisableUserNotifications') }}
                                                    </label>
                                                </div>
                                                <div class='radio mb-2'>
                                                    <label>
                                                        <input type='radio' name='user_notifications' value='1' {!! $user_notifications1 !!}>
                                                        {{ trans('langEnableUserNotifications') }}
                                                    </label>
                                                </div>
                                                <div class='radio mb-0'>
                                                    <label>
                                                        <input type='radio' id='user_notifications_interval' name='user_notifications' value='2' {!! $user_notifications2 !!}>
                                                        {{ trans('langCustomEnableUserNotifications') }}
                                                    </label>
                                                </div>
                                                <div class='col-sm-3 col-sm-offset-2' id='notifications_interval'>
                                                    <label class='mb-0' for='notifications_interval_id' aria-label='{{ trans('langCustomEnableUserNotifications') }}'></label>
                                                    {!! $user_notifications_interval !!}
                                                </div>
                                            </div>
                                        </div>


                                        <hr>
                                        <div class='form-group mt-4'>
                                            <label for='min_password_len' class='col-sm-12 control-label-notes'>{{ trans('langMinPasswordLen') }}: </label>
                                            <div class='col-sm-12'>
                                                <input type='text' class='form-control form-control-admin' name='min_password_len' id='min_password_len' value='{{ get_config('min_password_len') }}'>
                                            </div>
                                        </div>


                                        <div class='form-group mt-4'>
                                            <label for='max_glossary_terms_id' class='col-sm-12 control-label-notes'>{{ trans('lang_max_glossary_terms') }} </label>
                                            <div class='col-sm-12'>
                                                <input id='max_glossary_terms_id' class='form-control form-control-admin' type='text' name='max_glossary_terms' value='{{ $max_glossary_terms }}'>
                                            </div>
                                        </div>



                                        <div class='form-group mt-4'>
                                            <label for='actions_expire_interval_id' class='col-sm-12 control-label-notes'>{{ trans('langActionsExpireInterval') }} ({{ trans('langMonthsUnit') }}):</label>
                                            <div class='col-sm-12'>
                                                <input id='actions_expire_interval_id' type='text' class='form-control form-control-admin' name='actions_expire_interval' value='{{ get_config('actions_expire_interval') }}'>
                                            </div>
                                        </div>
                                    </fieldset>
                                </div>
                            </div>

                            <div class='card panelCard card-default px-lg-4 py-lg-3 d-none' data-id="nine" id='nine'>

                                <div class='card-header border-0 d-flex justify-content-between align-items-center'>

                                    <h3>{{ trans('langDocumentSettings') }}</h3>

                                </div>
                                <div class='card-body'>
                                    <fieldset>
                                        <legend class='mb-0' aria-label="{{ trans('langForm') }}"></legend>
                                        <div class='form-group'>
                                            <div class='col-sm-12 control-label-notes'>{{ trans('langEnableMyDocs') }}:</div>
                                            <div class='col-sm-12'>
                                                <div class='checkbox'>
                                                    <label class='label-container' aria-label="{{ trans('langSelect') }}">
                                                        <input type='checkbox' name='mydocs_teacher_enable' id='mydocs_teacher_enable_id' value='1' {{ $cbox_mydocs_teacher_enable }}>
                                                        <span class='checkmark'></span>
                                                        {{ trans('langTeachers') }}
                                                    </label>
                                                </div>
                                                <div class='checkbox'>
                                                    <label class='label-container' aria-label="{{ trans('langSelect') }}">
                                                        <input type='checkbox' name='mydocs_student_enable' id='mydocs_student_enable_id' value='1' {{ $cbox_mydocs_student_enable }}>
                                                        <span class='checkmark'></span>
                                                        {{ trans('langStudents') }}
                                                    </label>
                                                </div>
                                            </div>
                                        </div>



                                        <div class='form-group mt-4'>
                                            <div class='col-sm-12 control-label-notes mb-2'>{{ trans('langMyDocsQuota') }} (MB):</div>
                                            <div class='col-sm-12'>
                                                <div>
                                                    <label for='mydocs_teacher_quota_id' class='col-sm-12 control-label-notes'>{{ trans('langTeachers') }}</label>
                                                    <input class='form-control' type='text' name='mydocs_teacher_quota' id='mydocs_teacher_quota_id' value='{{ $mydocs_teacher_quota }}'>
                                                </div>
                                                <div>
                                                    <label for='mydocs_student_quota_id' class='col-sm-12 control-label-notes'>{{ trans('langStudents') }}</label>
                                                    <input class='form-control' type='text' name='mydocs_student_quota' id='mydocs_student_quota_id' value='{{ $mydocs_student_quota }}'>
                                                </div>
                                            </div>
                                        </div>



                                        <div class='form-group mt-4'>
                                            <label for='bio_quota' class='col-sm-12 control-label-notes'>{{ trans('langBioQuota') }} (MB):</label>
                                            <div class='col-sm-12'>
                                                <input class='form-control form-control-admin' type='text' name='bio_quota' id='bio_quota' value='{{ get_config('bio_quota') }}'>
                                            </div>
                                        </div>
                                        <div class='checkbox mt-4'>
                                            <label class='label-container' aria-label="{{ trans('langSelect') }}">
                                                <input type='checkbox' name='enable_common_docs' value='1' {{ $cbox_enable_common_docs }}>
                                                <span class='checkmark'></span>
                                                {{ trans('langEnableCommonDocs') }}
                                            </label>
                                        </div>
                                        <div class='checkbox'>
                                            <label class='label-container' aria-label="{{ trans('langSelect') }}">
                                                <input type='checkbox' name='enable_docs_public_write' value='1' {{ $cbox_enable_docs_public_write }}>
                                                <span class='checkmark'></span>
                                                {{ trans('langEnableDocsPublicWrite') }}
                                            </label>
                                            <div class='checkbox'>
                                                <label class='label-container' aria-label="{{ trans('langSelect') }}">
                                                    <input type='checkbox' name='enable_prevent_download_url' value='1' {{ $cbox_enable_prevent_download_url }}>
                                                    <span class='checkmark'></span>
                                                    {{ trans('langEnable_prevent_download_url') }}
                                                </label>
                                            </div>
                                        </div>
                                    </fieldset>
                                </div>
                            </div>

                            <div class='card panelCard card-default px-lg-4 py-lg-3 d-none' data-id="ten" id='ten'>
                                <div class='card-header border-0 d-flex justify-content-between align-items-center'>

                                    <h3>{{ trans('langDefaultQuota') }}</h3>

                                </div>
                                <div class='card-body'>
                                    <fieldset>
                                        <legend class='mb-0' aria-label="{{ trans('langForm') }}"></legend>
                                        <div class='form-group'>
                                            <label for='doc_quota' class='col-sm-12 control-label-notes'>{{ trans('langDocQuota') }} (MB):</label>
                                            <div class='col-sm-12'>
                                                <input class='form-control form-control-admin' type='text' name='doc_quota' id='doc_quota' value='{{ get_config('doc_quota') }}'>
                                            </div>
                                        </div>
                                        <div class='form-group mt-4'>
                                            <label for='video_quota' class='col-sm-12 control-label-notes'>{{ trans('langVideoQuota') }} (MB):</label>
                                            <div class='col-sm-12'>
                                                <input class='form-control form-control-admin' type='text' name='video_quota' id='video_quota' value='{{ get_config('video_quota') }}'>
                                            </div>
                                        </div>
                                        <div class='form-group mt-4'>
                                            <label for='group_quota' class='col-sm-12 control-label-notes'>{{ trans('langGroupQuota') }} (MB):</label>
                                            <div class='col-sm-12'>
                                                <input class='form-control form-control-admin' type='text' name='group_quota' id='group_quota' value='{{ get_config('group_quota') }}'>
                                            </div>
                                        </div>
                                        <div class='form-group mt-4'>
                                            <label for='dropbox_quota' class='col-sm-12 control-label-notes'>{{ trans('langDropboxQuota') }} (MB):</label>
                                            <div class='col-sm-12'>
                                                <input class='form-control form-control-admin' type='text' name='dropbox_quota' id='dropbox_quota' value='{{ get_config('dropbox_quota') }}'>
                                            </div>
                                        </div>
                                    </fieldset>
                                </div>
                            </div>

                            <div class='card panelCard card-default px-lg-4 py-lg-3 d-none' data-id="eleven" id='eleven'>

                                <div class='card-header border-0 d-flex justify-content-between align-items-center'>

                                    <h3>{{ trans('langUploadWhitelist') }}</h3>

                                </div>
                                <div class='card-body'>
                                    <fieldset>
                                        <legend class='mb-0' aria-label="{{ trans('langForm') }}"></legend>
                                        <div class='form-group'>
                                            <label for='student_upload_whitelist' class='col-sm-12 control-label-notes'>{{ trans('langStudentUploadWhitelist') }}:</label>
                                            <div class='col-sm-12'>
                                                <textarea class='form-control form-control-admin' rows='6' name='student_upload_whitelist' id='student_upload_whitelist'>{{ get_config('student_upload_whitelist') }}</textarea>
                                            </div>
                                        </div>



                                        <div class='form-group mt-4'>
                                            <label for='teacher_upload_whitelist' class='col-sm-12 control-label-notes'>{{ trans('langTeacherUploadWhitelist') }}:</label>
                                            <div class='col-sm-12'>
                                                <textarea class='form-control form-control-admin' rows='6' name='teacher_upload_whitelist' id='teacher_upload_whitelist'>{{ get_config('teacher_upload_whitelist') }}</textarea>
                                            </div>
                                        </div>
                                    </fieldset>
                                </div>
                            </div>

                            <div class='card panelCard card-default px-lg-4 py-lg-3 d-none' data-id="twelve" id='twelve'>

                                <div class='card-header border-0 d-flex justify-content-between align-items-center'>

                                    <h3>{{ trans('langLogActions') }}</h3>

                                </div>
                                <div class='card-body'>
                                    <fieldset>
                                        <legend class='mb-0' aria-label="{{ trans('langForm') }}"></legend>
                                        <div class='form-group'>
                                            <div class='col-sm-12'>
                                                <div class='checkbox'>
                                                    <label class='label-container' aria-label="{{ trans('langSelect') }}">
                                                        <input type='checkbox' name='disable_log_actions' value='1' {{ $cbox_disable_log_actions }}>
                                                        <span class='checkmark'></span>
                                                        {{ trans('lang_disable_log_actions') }}
                                                    </label>
                                                </div>
                                                <div class='checkbox'>
                                                    <label class='label-container' aria-label="{{ trans('langSelect') }}">
                                                        <input type='checkbox' name='disable_log_course_actions' value='1' {{ $cbox_disable_log_course_actions }}>
                                                        <span class='checkmark'></span>
                                                        {{ trans('lang_disable_log_course_actions') }}
                                                    </label>
                                                </div>
                                                <div class='checkbox'>
                                                    <label class='label-container' aria-label="{{ trans('langSelect') }}">
                                                        <input type='checkbox' name='disable_log_system_actions' value='1' {{ $cbox_disable_log_system_actions }}>
                                                        <span class='checkmark'></span>
                                                        {{ trans('lang_disable_log_system_actions') }}
                                                    </label>
                                                </div>
                                            </div>
                                        </div>

                                        <hr>
                                        <div class='form-group mt-4'>
                                            <label for='log_expire_interval' class='col-sm-12 control-label-notes'>{{ trans('langLogExpireInterval') }} ({{ trans('langMonthsUnit') }}):</label>
                                            <div class='col-sm-12'>
                                                <input class='form-control form-control-admin' type='text' name='log_expire_interval' id='log_expire_interval' value='{{ get_config('log_expire_interval') }}'>
                                            </div>
                                        </div>



                                        <div class='form-group mt-4'>
                                            <label for='log_purge_interval' class='col-sm-12 control-label-notes'>{{ trans('langLogPurgeInterval') }} ({{ trans('langMonthsUnit') }}):</label>
                                            <div class='col-sm-12'>
                                                <input class='form-control form-control-admin' type='text' name='log_purge_interval' id='log_purge_interval' value='{{ get_config('log_purge_interval') }}'>
                                            </div>
                                        </div>
                                    </fieldset>
                                </div>
                            </div>

                            <div class='card panelCard card-default px-lg-4 py-lg-3 d-none' data-id="thirteen" id='thirteen'>

                                <div class='card-header border-0 d-flex justify-content-between align-items-center'>

                                    <h3>{{ trans('langLoginFailCheck') }}</h3>

                                </div>
                                <div class='card-body'>
                                    <fieldset>
                                        <legend class='mb-0' aria-label="{{ trans('langForm') }}"></legend>
                                        <div class='form-group'>
                                            <div class='col-sm-12'>
                                                <div class='checkbox'>
                                                    <label class='label-container' aria-label="{{ trans('langSelect') }}">
                                                        <input id='login_fail_check' type='checkbox' name='login_fail_check' value='1' {{ $cbox_login_fail_check }}>
                                                        <span class='checkmark'></span>
                                                        {{ trans('langEnableLoginFailCheck') }}
                                                    </label>
                                                </div>
                                            </div>
                                        </div>



                                        <div class='form-group mt-4' id='login_fail_threshold'>
                                            <label for='login_fail_threshold' class='col-sm-12 control-label-notes'>{{ trans('langLoginFailThreshold') }}:</label>
                                            <div class='col-sm-12'>
                                                <input class='form-control form-control-admin' type='text' name='login_fail_threshold' id='login_fail_threshold' value='{{ get_config('login_fail_threshold') }}'>
                                            </div>
                                        </div>



                                        <div class='form-group mt-4' id='login_fail_deny_interval'>
                                            <label for='login_fail_deny_interval' class='col-sm-12 control-label-notes'>{{ trans('langLoginFailDenyInterval') }} ({{ trans('langMinute') }}):</label>
                                            <div class='col-sm-12'>
                                                <input class='form-control form-control-admin' type='text' name='login_fail_deny_interval' id='login_fail_deny_interval' value='{{ get_config('login_fail_deny_interval') }}'>
                                            </div>
                                        </div>



                                        <div class='form-group mt-4' id='login_fail_forgive_interval'>
                                            <label for='login_fail_forgive_interval' class='col-sm-12 control-label-notes'>{{ trans('langLoginFailForgiveInterval') }} ({{ trans('langHours') }}):</label>
                                            <div class='col-sm-12'>
                                                <input class='form-control form-control-admin' type='text' name='login_fail_forgive_interval' id='login_fail_forgive_interval' value='{{ get_config('login_fail_forgive_interval') }}'>
                                            </div>
                                        </div>
                                    </fieldset>
                                </div>
                            </div>

                            <div class='card panelCard card-default px-lg-4 py-lg-3 d-none' data-id="fourteen" id='fourteen'>

                                <div class='card-header border-0 d-flex justify-content-between align-items-center'>
                                    <h3>{{ trans('langMaintenance') }}</h3>
                                </div>
                                <div class='card-body'>
                                    <fieldset>
                                        <legend class='mb-0' aria-label="{{ trans('langForm') }}"></legend>
                                        <div class='form-group'>
                                            <div class='col-sm-12'>
                                                <div class='checkbox'>
                                                    <label class='label-container' aria-label="{{ trans('langSelect') }}">
                                                        <input type='checkbox' name='maintenance' value='1' {{ $cbox_maintenance }}>
                                                        <span class='checkmark'></span>
                                                        {{ trans('langMaintenanceMode') }}
                                                    </label>
                                                </div>
                                            </div>
                                        </div>
                                        <div class='col-sm-12 control-label-notes mt-4'>{{ trans('langMaintenanceChange') }}</div>
                                        @foreach ($selectable_langs as $langCode => $langName)
                                            @php $maintenance_text = get_config('maintenance_text_' . $langCode) @endphp
                                            <div class='form-group mt-4'>
                                                <label for='maintenance_text_{{ $langCode }}' class='col-sm-12 control-label-notes'>{{ trans('langText') }}:({{ $langName }})</label>
                                                <div class='col-sm-12'>
                                                    {!! rich_text_editor('maintenance_text_'.$langCode, 5, 20, $maintenance_text) !!}
                                                </div>
                                            </div>
                                        @endforeach
                                        <hr>
                                        <div class='mt-4'>
                                            <div class='col-sm-12 control-label-notes mb-3'>{{ trans('langThemes') }}</div>
                                            <div class='row'>
                                                <div class='col-sm-6'>
                                                    <div class='form-check'>
                                                        <input class='form-check-input' type='radio' name='maintenance_theme' id='image1' value='1' {!! checkMaintenanceTheme($maintenance_theme, 1) !!}>
                                                        <label class='form-check-label' for='image1'>
                                                            <img style='max-width: 300px;' src='{{ $urlServer }}maintenance/preview_img/theme_1.png' class='img-fluid' alt='Image preview 1'>
                                                        </label>
                                                    </div>
                                                </div>
                                                <div class='col-sm-6'>
                                                    <div class='form-check'>
                                                        <input class='form-check-input' type='radio' name='maintenance_theme' id='image2' value='2' {!! checkMaintenanceTheme($maintenance_theme, 2) !!}>
                                                        <label class='form-check-label' for='image2'>
                                                            <img style='max-width: 300px;' src='{{ $urlServer }}/maintenance/preview_img/theme_2.png' class='img-fluid' alt='Image preview 2'>
                                                        </label>
                                                    </div>
                                                </div>
                                            </div>
                                            <div class='row'>
                                                <div class='col-sm-6'>
                                                    <div class='form-check'>
                                                        <input class='form-check-input' type='radio' name='maintenance_theme' id='image3' value='3' {!! checkMaintenanceTheme($maintenance_theme, 3) !!}>
                                                        <label class='form-check-label' for='image3'>
                                                            <img style='max-width: 300px;' src='{{ $urlServer }}maintenance/preview_img/theme_3.png' class='img-fluid' alt='Image preview 3'>
                                                        </label>
                                                    </div>
                                                </div>
                                                <div class='col-sm-6'>
                                                    <div class='form-check'>
                                                        <input class='form-check-input' type='radio' name='maintenance_theme' id='image4' value='4' {!! checkMaintenanceTheme($maintenance_theme, 4) !!}>
                                                        <label class='form-check-label' for='image4'>
                                                            <img style='max-width: 300px;' src='{{ $urlServer }}maintenance/preview_img/theme_4.png' class='img-fluid' alt='Image preview 4'>
                                                        </label>
                                                    </div>
                                                </div>
                                            </div>
                                            <div class='row'>
                                                <div class='col-sm-6'>
                                                    <div class='form-check'>
                                                        <input class='form-check-input' type='radio' name='maintenance_theme' id='image5' value='5' {!! checkMaintenanceTheme($maintenance_theme, 5) !!}>
                                                        <label class='form-check-label' for='image5'>
                                                            <img style='max-width: 300px;' src='{{ $urlServer }}maintenance/preview_img/theme_5.png' class='img-fluid' alt='Image preview 5'>
                                                        </label>
                                                    </div>
                                                </div>
                                                <div class='col-sm-6'>
                                                    <div class='form-check'>
                                                        <input class='form-check-input' type='radio' name='maintenance_theme' id='image6' value='6' {!! checkMaintenanceTheme($maintenance_theme, 6) !!}>
                                                        <label class='form-check-label' for='image6'>
                                                            <img style='max-width: 300px;' src='{{ $urlServer }}maintenance/preview_img/theme_6.png' class='img-fluid' alt='Image preview 6'>
                                                        </label>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    </fieldset>
                                </div>
                            </div>

                            {!! showSecondFactorChallenge() !!}

                            <div class='form-group mt-5'>
                                <div class='col-12 d-flex justify-content-end align-items-center gap-2'>
                                    <input id="submitAdminBtn" class='btn submitAdminBtn' type='submit' name='submit' value='{{ trans('langSubmit') }}'>
                                    <a class='btn cancelAdminBtn' href='index.php'>{{ trans('langCancel') }}</a>
                                </div>
                            </div>
                            {!! generate_csrf_token_form_field() !!}
                        </div> <!-- end scroll-spy -->

                    </form>
                </div>





                {!! modalConfirmation('confirmIndexDialog', 'confirmIndexLabel', trans('langConfirmEnableIndexTitle'), trans('langConfirmEnableIndex'), 'confirmIndexCancel', 'confirmIndexOk') !!}
                {!! modalConfirmation('confirmMobileAPIDialog', 'confirmMobileAPILabel', trans('langConfirmEnableMobileAPITitle'), trans('langConfirmEnableMobileAPI'), 'confirmMobileAPICancel', 'confirmMobileAPIOk') !!}

            </div>
        </div>
    </div>



    @if (Session::get('scheduleIndexing'))
        <script>
            var idxwindow = null;

            function idxpopup(url, w, h) {
                var left = screen.width/2 - w/2,
                    top = screen.height/2 - h/2;

                if (idxwindow == null || idxwindow.closed) {
                    idxwindow = window.open(url, 'idxpopup', 'resizable=yes, scrollbars=yes, status=yes, width='+w+', height='+h+', top='+top+', left='+left);
                    if (window.focus && idxwindow !== null) {
                        idxwindow.focus();
                    }
                } else {
                    idxwindow.focus();
                }

                return false;
            }

            $(function() { $('#idxpbut').click(); });
        </script>
    @endif

    <script>

        function loginFailPanel(e) {
            duration = null;
            if (e) {
                duration = 400;
            }

            if ($('#login_fail_check').is(":checked")) {
                $('#login_fail_threshold').show(duration);
                $('#login_fail_deny_interval').show(duration);
                $('#login_fail_forgive_interval').show(duration);
            }
            else {
                $('#login_fail_threshold').hide(duration);
                $('#login_fail_deny_interval').hide(duration);
                $('#login_fail_forgive_interval').hide(duration);
            }
        }

        $(function() {

            // Course Settings checkboxes
            $('#uown').click(function(event) {
                if (!$('#uown').is(":checked")) {
                    $('#town').prop('checked', false);
                }
                $('#town').prop('disabled', !$('#uown').is(":checked"));
            });

            // Login screen / link checkboxes
            $('#hide_login_check').click(function(event) {
                if (!$('#hide_login_check').is(":checked")) {
                    $('#hide_login_link_check').prop('checked', false);
                }
                $('#hide_login_link_check').prop('disabled', !$('#hide_login_check').is(":checked"));
            });

            // Login Fail Panel
            loginFailPanel();
            $('#login_fail_check').click(function(event) {
                loginFailPanel(true);
            });

            // Open Courses checkboxes
            $('#opencourses_enable').click(function(event) {
                if ($('#opencourses_enable').is(":checked")) {
                    if ($('#course_metadata').is(":checked")) {
                        $('#course_metadata').prop('disabled', true);
                    } else {
                        $('#course_metadata')
                            .prop('checked', true)
                            .prop('disabled', true)
                            .change();
                    }
                } else {
                    $('#course_metadata').prop('disabled', false);
                }
            });

            if ($('#opencourses_enable').is(":checked")) {
                $('#course_metadata').prop('disabled', true);
            }

            // MyDocs checkboxes and inputs
            function mydocsCheckboxQuota(checkbox, input) {
                $(checkbox).change(function (event) {
                    $(input).prop('disabled', !$(this).is(':checked'));
                }).change();
            }
            mydocsCheckboxQuota('#mydocs_teacher_enable_id', '#mydocs_teacher_quota_id');
            mydocsCheckboxQuota('#mydocs_student_enable_id', '#mydocs_student_quota_id');

            // Search Engine checkboxes
            $('#confirmIndexDialog').modal({
                show: false,
                keyboard: false,
                backdrop: 'static'
            });

            $("#confirmIndexCancel").click(function() {
                $('#index_enable')
                    .prop('checked', false)
                    .prop('disabled', false);
                $('#search_enable').prop('checked', false);
                $("#confirmIndexDialog").modal("hide");
            });

            $("#confirmIndexOk").click(function() {
                $("#confirmIndexDialog").modal("hide");
            });

            $('#search_enable').change(function(event) {
                if ($('#search_enable').is(":checked")) {
                    if ($('#index_enable').is(":checked")) {
                        $('#index_enable').prop('disabled', true);
                    } else {
                        $('#index_enable')
                            .prop('checked', true)
                            .prop('disabled', true)
                            .change();
                    }
                } else {
                    $('#index_enable').prop('disabled', false);
                }
            });

            if ($('#search_enable').is(":checked")) {
                $('#index_enable').prop('disabled', true);
            }

            $('#index_enable').change(function(event) {
                if ($('#index_enable').is(":checked")) {
                    $("#confirmIndexDialog").modal("show");
                }
            });

            $('#social_sharing_links').change(function(event) {
                if ($('#social_sharing_links').is(":checked")) {
                    if ($('#personal_blog_enable').is(":checked")) {
                        $('#personal_blog_sharing_enable').prop('disabled', false);
                    }
                } else {
                    $('#personal_blog_sharing_enable').prop('disabled', true);
                }
            });

            if (!$('#social_sharing_links').is(":checked")) {
                $('#personal_blog_sharing_enable').prop('disabled', true);
            }

            $('#personal_blog_enable').change(function(event) {
                if ($('#personal_blog_enable').is(":checked")) {
                    $('#personal_blog_public').prop('disabled', false);
                    $('#personal_blog_commenting_enable').prop('disabled', false);
                    $('#personal_blog_rating_enable').prop('disabled', false);
                    if ($('#social_sharing_links').is(":checked")) {
                        $('#personal_blog_sharing_enable').prop('disabled', false);
                    }
                } else {
                    $('#personal_blog_public').prop('disabled', true);
                    $('#personal_blog_commenting_enable').prop('disabled', true);
                    $('#personal_blog_rating_enable').prop('disabled', true);
                    $('#personal_blog_sharing_enable').prop('disabled', true);
                }
            });

            if (!$('#personal_blog_enable').is(":checked")) {
                $('#personal_blog_public').prop('disabled', true);
                $('#personal_blog_commenting_enable').prop('disabled', true);
                $('#personal_blog_rating_enable').prop('disabled', true);
                $('#personal_blog_sharing_enable').prop('disabled', true);
            }

            $('input[name=submit]').click(function() {
                $('#personal_blog_commenting_enable').prop('disabled', false);
                $('#personal_blog_rating_enable').prop('disabled', false);
                $('#personal_blog_sharing_enable').prop('disabled', false);
            });

            // Mobile API Confirmations
            $('#confirmMobileAPIDialog').modal({
                show: false,
                keyboard: false,
                backdrop: 'static'
            });

            $("#confirmMobileAPICancel").click(function() {
                $('#mobileapi_enable').prop('checked', false);
                $("#confirmMobileAPIDialog").modal("hide");
            });

            $("#confirmMobileAPIOk").click(function() {
                $("#confirmMobileAPIDialog").modal("hide");
            });

            $('#mobileapi_enable').change(function(event) {
                if ($('#mobileapi_enable').is(":checked")) {
                    $("#confirmMobileAPIDialog").modal("show");
                }
            });

            $('#registration_link').change(function() {
                var type = $(this).val();
                if (type == 'show_text') {
                    $('#registration-info-block').show();
                } else {
                    $('#registration-info-block').hide();
                }
            }).change();

            {!! $mail_form_js !!}

            $('.default_checkbox').on('click',function(){
                $('#collapse-defaultHomepage').collapse('show');
                $('#collapse-toolboxHomepage').collapse('hide');
                $('#collapse-externalHomepage').collapse('hide');
            })

            //Side Nav Menu
            $('[data-menuID]').on('click', function() {
                if ($(this).hasClass('active')) {
                    return;
                }
                var menuID = $(this).data('menuid');
                $('[data-id]').not('[data-id="' + menuID + '"]').addClass('d-none');
                $('#' + menuID).toggleClass('d-none');
                $('[data-menuID]').removeClass('active');
                $(this).addClass('active');
            });

            $('input[name=user_notifications]').change(function () {
                if ($('#user_notifications_interval').is(":checked")) {
                    $('#notifications_interval').show();
                } else {
                    $('#notifications_interval').hide();
                }
            }).change();

        });

    </script>

@endsection
