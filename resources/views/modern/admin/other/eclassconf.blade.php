<?php

    //////////////////////////////////// five ///////////////////////////////////////
    $install = isset($GLOBALS['input_fields']);

    $emailTransports = array(0 => 'PHP mail()', 1 => 'SMTP', 2 => 'sendmail');
    $email_transport = get_var('email_transport');
    if (!is_numeric($email_transport)) {
        if ($email_transport == 'smtp') {
            $email_transport = 1;
        } elseif ($email_transport == 'sendmail') {
            $email_transport = 2;
        } else {
            $email_transport = 0;
        }
    }
    $emailEncryption = array(0 => 'Όχι', 1 => 'SSL', 2 => 'TLS');
    $smtp_encryption = get_var('smtp_encryption');
    if ($smtp_encryption == 'ssl') {
        $smtp_encryption = 1;
    } elseif ($smtp_encryption == 'tls') {
        $smtp_encryption = 2;
    } else {
        $smtp_encryption = 0;
    }
    $cbox_dont_mail_unverified_mails = get_var('dont_mail_unverified_mails') ? 'checked' : '';
    $cbox_email_from = get_var('email_from') ? 'checked' : '';

    //////////////////////////////////// four ///////////////////////////////////////
    $defaultHomepage = $toolboxHomepage = $externalHomepage = '';
    $homepageSet = get_config('homepage');
    if ($homepageSet == 'toolbox') {
        $toolboxHomepage = 'checked';
    } elseif ($homepageSet == 'external') {
        $externalHomepage = 'checked';
    } else {
        $defaultHomepage = 'checked';
    }
?>


@extends('layouts.default')

@section('content')

<div class="col-12 main-section">
    <div class='{{ $container }} main-container'>

        <div class="row m-auto">

                    @include('layouts.common.breadcrumbs', ['breadcrumbs' => $breadcrumbs])

                    @include('layouts.partials.legend_view')

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

                            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                        </div>
                    </div>
                    @endif



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


                        <div class='col-xl-9 col-lg-8 col-md-12 col-sm-12 col-12 forms-panels-admin'>
                            <form class='form-horizontal' role='form' action='{{ $_SERVER['SCRIPT_NAME'] }}' method='post'>
                                <div data-bs-spy="scroll" data-bs-target="#navbar-example3" data-bs-offset="0" tabindex="0">
                                    <div class='card panelCard px-lg-4 py-lg-3' id='one'>
                                        <div class='card-header border-0 d-flex justify-content-between align-items-center'>
                                            <h3>{{ trans('langBasicCfgSetting') }}</h3>

                                        </div>
                                        <div class='card-body'>
                                            <fieldset>
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
                                                    <label for='formemailAdministrator' class='col-sm-12 control-label-notes'>{{ trans('langDefaultAdminName') }}:</label>
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



                                    <div class='card panelCard px-lg-4 py-lg-3 mt-4' id='two'>
                                        <div class='card-header border-0 d-flex justify-content-between align-items-center'>

                                               <h3>{{ trans('langUpgReg') }}</h3>

                                        </div>
                                        <div class='card-body'>
                                            <fieldset>
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
                                                    <label class='col-sm-12 control-label-notes mb-1'>{{ trans('lang_block_duration_account') }}:</label>
                                                    <div class='checkbox col-sm-12'>
                                                        <label class='label-container'>
                                                            <input type='checkbox' name='block_duration_account' value='1' {{ $cbox_block_duration_account }}>
                                                            <span class='checkmark'></span>
                                                            {{ trans('lang_message_block_duration_account') }}
                                                        </label>
                                                    </div>
                                                </div>



                                                <div class='form-group mt-4'>
                                                    <label class='col-sm-12 control-label-notes mb-1'>{{ trans('lang_block_duration_alt_account') }}:</label>
                                                    <div class='checkbox col-sm-12'>
                                                    <label class='label-container'>
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
                                                    <label class='col-sm-12 control-label-notes mb-1'>{{ trans('lang_display_captcha_label') }}:</label>
                                                    <div class='checkbox col-sm-12'>
                                                        <label class='label-container'>
                                                            <input type='checkbox' name='display_captcha' value='1' {{ $cbox_display_captcha }} {{ $disable_display_captcha }}>
                                                            <span class='checkmark'></span>
                                                            {{ trans('lang_display_captcha') }}
                                                        </label>
                                                        {!! $message_display_captcha !!}
                                                    </div>
                                                </div>




                                                <div class='form-group mt-4'>
                                                    <label class='col-sm-12 control-label-notes'>{{ trans('langGuestLoginLabel') }}:</label>
                                                    <div class='col-sm-12'>
                                                        {{-- {!! selection([
                                                                        'off' => trans('m[deactivate]'),
                                                                        'on' => trans('m[activate]'),
                                                                        'link' => trans('langGuestLoginLinks')
                                                                        ],
                                                                        'course_guest',
                                                                        get_config('course_guest', 'on'),
                                                                        "class='form-control form-control-admin'"
                                                                    ) !!} --}}

                                                        {!! selection([
                                                            'off' => trans('langDeactivate'),
                                                            'on' => trans('langActivate'),
                                                            'link' => trans('langGuestLoginLinks')
                                                            ],
                                                            'course_guest',
                                                            get_config('course_guest', 'on'),
                                                            "class='form-control form-control-admin'"
                                                        ) !!}
                                                    </div>
                                                </div>


                                                <div class='form-group mt-4'>
                                                    <label class='col-sm-12 control-label-notes'>{{ trans('langcourseExternalUsersInviation') }}:</label>
                                                    <div class='checkbox col-sm-12'>
                                                        <label class='label-container'>
                                                            <input type='checkbox' name='course_invitation' value='1' {{ $cbox_course_invitation }}>
                                                            <span class='checkmark'></span>
                                                            {{ trans('langCourseInvitationHelp') }}
                                                        </label>
                                                    </div>
                                                </div>

                                            </fieldset>
                                        </div>
                                    </div>



                                    <div class='card panelCard px-lg-4 py-lg-3 mt-4' id='three'>
                                        <div class='card-header border-0 d-flex justify-content-between align-items-center'>

                                                <h3>{{ trans('langSupportedLanguages') }}</h3>

                                        </div>
                                        <div class='card-body'>
                                            <fieldset>
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
                                                    <label class='col-sm-12 control-label-notes mb-1'>{{ trans('langSupportedLanguages') }}:</label>
                                                    <div class='col-sm-12'>
                                                    {!! implode(' ', $sel) !!}
                                                    </div>
                                                </div>
                                            </fieldset>
                                        </div>
                                    </div>


                                    <div class='card panelCard px-lg-4 py-lg-3 mt-4' id='four'>

                                        <div class='card-header border-0 d-flex justify-content-between align-items-center'>

                                                <h3>{{trans('langHomePageSettings')}}</h3>

                                        </div>
                                        <div class='card-body'>
                                            <div class='margin-bottom-fat margin-top-fat fs-5 mb-3'><strong>{{trans('langSelectHomePage')}} :</strong></div>
                                            <fieldset>
                                                <div class='landing-default'>
                                                    <div class='radio margin-bottom-fat'>
                                                        <label class='d-inline-flex align-items-top'>
                                                            <input {{$defaultHomepage}} class='homepageSet default_checkbox' name='homepageSet' value='default' type='radio'> <span>{{trans('langHomePageDefault')}}</span>
                                                        </label>
                                                    </div>
                                                    <div id='collapse-defaultHomepage' class='collapse homepage-inputs margin-bottom-fat show'>
                                                        <hr class='margin-bottom-fat'>
                                                        <div class='form-group mt-4'>
                                                            <label for='defaultHomepageTitle' class='col-sm-12 control-label-notes'>{{trans('langHomePageIntroTitle')}}</label>
                                                            <div class='col-sm-12'>
                                                                <input class='form-control' type='text' name='homepage_title' id='defaultHomepageTitle' value="{!! q(get_config('homepage_title', trans('langEclass'))) !!}">
                                                                <p class='help-block mt-1'>{{trans('langHomePageTitleHelpText')}}</p>
                                                            </div>
                                                        </div>
                                                        <div class='form-group mt-4'>
                                                            <label for='defaultHomepageBcrmp' class='col-sm-12 control-label-notes'>{{trans('langHomePageIntroBcrmp')}}</label>
                                                            <div class='col-sm-12'>
                                                                <input class='form-control' type='text' name='homepage_name' id='defaultHomepageBcrmp' value="{!! q(get_config('homepage_name', trans('langHomePage'))) !!}">
                                                                <p class='help-block mt-1'>{{trans('langHomePageNavTitleHelp')}}</p>
                                                            </div>
                                                        </div>
                                                        <div class='form-group mt-4'>
                                                            <label for='defaultHomepageIntro' class='col-sm-12 control-label-notes'>{{trans('langHomePageIntroText')}}:</label>
                                                            <div class='col-sm-12'>
                                                                {!! rich_text_editor('homepage_intro', 5, 20, get_config('homepage_intro', trans('langInfoAbout'))) !!}
                                                                <p class='help-block mt-1'>{{trans('langHomePageIntroTextHelp')}}</p>
                                                            </div>
                                                        </div>
                                                        <div class='form-group mt-4'>
                                                            <label for='theme' class='col-sm-12 control-label-notes mb-1'>{{trans('lang_login_form')}}: </label>
                                                            <div class='col-sm-12'>
                                                                    <div class='checkbox'>
                                                                    <label class='label-container'>
                                                                            <input id='hide_login_check' type='checkbox' name='dont_display_login_form' value='1' {{ $cbox_dont_display_login_form }}>
                                                                            <span class='checkmark'></span>
                                                                            {{trans('lang_dont_display_login_form')}}
                                                                        </label>
                                                                    </div>
                                                                    <div class='checkbox'>
                                                                    <label class='label-container'>
                                                                            <input id='hide_login_link_check' type='checkbox' name='hide_login_link' value='1' {{ $cbox_hide_login_link }}>
                                                                            <span class='checkmark'></span>
                                                                            {{trans('lang_hide_login_link')}}
                                                                        </label>
                                                                    </div>
                                                            </div>
                                                        </div>

                                                        <div class='form-group mt-4'>
                                                            <label for='theme' class='col-sm-12 control-label-notes mb-1'>{{trans('langHomePageMenuSettings')}}: </label>
                                                        </div>
                                                        <div class='col-sm-12'>
                                                            <div class='checkbox'>
                                                                <label class='label-container'>
                                                                    <input id='hide_login_check' type='checkbox' name='dont_display_courses_menu' value='1' {{ $cbox_dont_display_courses_menu }}>
                                                                    <span class='checkmark'></span>
                                                                    {{trans('lang_dont_display_courses_menu')}}
                                                                </label>
                                                            </div>
                                                            <div class='checkbox'>
                                                                <label class='label-container'>
                                                                    <input id='hide_login_check' type='checkbox' name='dont_display_contact_menu' value='1' {{ $cbox_dont_display_contact_menu }}>
                                                                    <span class='checkmark'></span>
                                                                    {{trans('lang_dont_display_contact_menu')}}
                                                                </label>
                                                            </div>
                                                            <div class='checkbox'>
                                                                <label class='label-container'>
                                                                    <input id='hide_login_check' type='checkbox' name='dont_display_about_menu' value='1' {{ $cbox_dont_display_about_menu }}>
                                                                    <span class='checkmark'></span>
                                                                    {{trans('lang_dont_display_about_menu')}}
                                                                </label>
                                                            </div>
                                                            <div class='checkbox'>
                                                                <label class='label-container'>
                                                                    <input id='hide_login_check' type='checkbox' name='dont_display_manual_menu' value='1' {{ $cbox_dont_display_manual_menu }}>
                                                                    <span class='checkmark'></span>
                                                                    {{trans('lang_dont_display_manual_menu')}}
                                                                </label>
                                                            </div>
                                                        </div>

                                                        
                                                    </div>
                                                </div>

                                            </fieldset>
                                        </div>
                                    </div>



                                    @if(!$install)
                                    <div class='card panelCard px-lg-4 py-lg-3 mt-4' id='five'>
                                        <div class='card-header border-0 d-flex justify-content-between align-items-center'>

                                               <h3>{{ trans('langEmailSettings') }}</h3>

                                        </div>
                                        <div class='card-body'>
                                            <fieldset>
                                    @endif
                                                <div class='form-group'>
                                                    <div class='col-sm-12'>
                                                            <div class='checkbox'>
                                                            <label class='label-container'>
                                                                    <input type='checkbox' name='dont_mail_unverified_mails' value='1' {!! $cbox_dont_mail_unverified_mails !!}>
                                                                    <span class='checkmark'></span>
                                                                    {{ trans('lang_dont_mail_unverified_mails') }}
                                                                </label>
                                                            </div>
                                                            <div class='checkbox'>
                                                            <label class='label-container'>
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

                                    @if(!$install)
                                            </fieldset>
                                        </div>
                                    </div>
                                    @endif


                                    <div class='card panelCard px-lg-4 py-lg-3 mt-4' id='six'>
                                        <div class='card-header border-0 d-flex justify-content-between align-items-center'>

                                                <h3>{{ trans('langCourseSettings') }}</h3>
                                        </div>
                                        <div class='card-body'>
                                            <fieldset>
                                                <div class='form-group'>
                                                    <div class='col-sm-12'>
                                                            <div class='checkbox'>
                                                            <label class='label-container'>
                                                                    <input type='checkbox' name='course_multidep' value='1' {{ $cbox_course_multidep }}>
                                                                    <span class='checkmark'></span>
                                                                    {{ trans('lang_course_multidep') }}
                                                                </label>
                                                            </div>
                                                            <div class='checkbox'>
                                                            <label class='label-container'>
                                                                    <input type='checkbox' name='user_multidep' value='1' {{ $cbox_user_multidep }}>
                                                                    <span class='checkmark'></span>
                                                                    {{ trans('lang_user_multidep') }}
                                                                </label>
                                                            </div>
                                                            <div class='checkbox'>
                                                            <label class='label-container'>
                                                                    <input id='uown' type='checkbox' name='restrict_owndep' value='1' {{ $cbox_restrict_owndep }}>
                                                                    <span class='checkmark'></span>
                                                                    {{ trans('lang_restrict_owndep') }}
                                                                </label>
                                                            </div>
                                                            <div class='checkbox'>
                                                            <label class='label-container'>
                                                                    <input id='town' type='checkbox' name='restrict_teacher_owndep' value='1' {{ $town_dis }} {{ $cbox_restrict_teacher_owndep }}>
                                                                    <span class='checkmark'></span>
                                                                    {{ trans('lang_restrict_teacher_owndep') }}
                                                                </label>
                                                            </div>
                                                            <div class='checkbox'>
                                                            <label class='label-container'>
                                                                    <input type='checkbox' name='allow_teacher_clone_course' value='1' {{ $cbox_allow_teacher_clone_course }}>
                                                                    <span class='checkmark'></span>
                                                                    {{ trans('lang_allow_teacher_clone_course') }}
                                                                </label>
                                                            </div>
                                                        <div class='checkbox'>
                                                            <label class='label-container'>
                                                                <input type='checkbox' name='offline_course' value='1' {{ $cbox_offline_course }}>
                                                                <span class='checkmark'></span>
                                                                {{ trans('langCourseOfflineSettings') }}
                                                            </label>
                                                        </div>
                                                        <div class='checkbox'>
                                                            <label class='label-container'>
                                                                <input type='checkbox' name='allow_rec_video' value='1' {{ $cbox_allow_rec_video }}>
                                                                <span class='checkmark'></span>
                                                                {{ trans('lang_allow_rec_video') }}
                                                            </label>
                                                        </div>
                                                        <div class='checkbox'>
                                                            <label class='label-container'>
                                                                <input type='checkbox' name='allow_rec_audio' value='1' {{ $cbox_allow_rec_audio }}>
                                                                <span class='checkmark'></span>
                                                                {{ trans('lang_allow_rec_audio') }}
                                                            </label>
                                                        </div>
                                                        <div class='checkbox'>
                                                            <label class='label-container'>
                                                                <input type='checkbox' name='show_modal_openCourses' value='1' {{ $cbox_allow_modal_courses }}>
                                                                <span class='checkmark'></span>
                                                                {!! trans('lang_openCourse_inModal') !!}
                                                            </label>
                                                        </div>
                                                    </div>
                                                </div>
                                            </fieldset>
                                        </div>
                                    </div>




                                    <div class='card panelCard px-lg-4 py-lg-3 mt-4' id='seven'>

                                        <div class='card-header border-0 d-flex justify-content-between align-items-center'>

                                              <h3>{{ trans('langMetaCommentary') }}</h3>

                                        </div>
                                        <div class='card-body'>
                                            <fieldset>
                                                <div class='form-group'>
                                                    <div class='col-sm-12'>
                                                        <div class='checkbox'>
                                                        <label class='label-container'>
                                                                <input type='checkbox' name='insert_xml_metadata' value='1' {{ $cbox_insert_xml_metadata }}>
                                                                <span class='checkmark'></span>
                                                                {{ trans('lang_insert_xml_metadata') }}
                                                            </label>
                                                        </div>
                                                        <div class='checkbox'>
                                                        <label class='label-container'>
                                                                <input type='checkbox' id='course_metadata' name='course_metadata' value='1' {{ $cbox_course_metadata }}>
                                                                <span class='checkmark'></span>
                                                                {{ trans('lang_course_metadata') }}
                                                            </label>
                                                        </div>
                                                        <div class='checkbox'>
                                                        <label class='label-container'>
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




                                    <div class='card panelCard px-lg-4 py-lg-3 mt-4' id='eight'>
                                        <div class='card-header border-0 d-flex justify-content-between align-items-center'>

                                                <h3>{{ trans('langOtherOptions') }}</h3>

                                        </div>
                                        <div class='card-body'>
                                            <fieldset>
                                                <div class='form-group'>
                                                    <div class='col-sm-12'>
                                                        <div class='checkbox'>
                                                        <label class='label-container'>
                                                                <input type='checkbox' name='case_insensitive_usernames' value='1' {{ $cbox_case_insensitive_usernames }}>
                                                                <span class='checkmark'></span>
                                                                {{ trans('langCaseInsensitiveUsername') }}
                                                            </label>
                                                        </div>
                                                        <div class='checkbox'>
                                                        <label class='label-container'>
                                                                <input type='checkbox' name='email_required' value='1' {{ $cbox_email_required }}>
                                                                <span class='checkmark'></span>
                                                                {{ trans('lang_email_required') }}
                                                            </label>
                                                        </div>
                                                        <div class='checkbox'>
                                                        <label class='label-container'>
                                                                <input type='checkbox' name='email_verification_required' value='1' {{ $cbox_email_verification_required }}>
                                                                <span class='checkmark'></span>
                                                                {{ trans('lang_email_verification_required') }}
                                                            </label>
                                                        </div>
                                                        <div class='checkbox'>
                                                        <label class='label-container'>
                                                                <input type='checkbox' name='am_required' value='1' {{ $cbox_am_required }}>
                                                                <span class='checkmark'></span>
                                                                {{ trans('lang_am_required') }}
                                                            </label>
                                                        </div>

                                                        <div class='checkbox'>
                                                        <label class='label-container'>
                                                                <input id='index_enable' type='checkbox' name='enable_indexing' value='1' {{ $cbox_enable_indexing }}>
                                                                <span class='checkmark'></span>
                                                                {{ trans('langEnableIndexing') }}
                                                            </label>
                                                        </div>
                                                        <div class='checkbox'>
                                                        <label class='label-container'>
                                                                <input id='search_enable' type='checkbox' name='enable_search' value='1' {{ $cbox_enable_search }}>
                                                                <span class='checkmark'></span>
                                                                {{ trans('langEnableSearch') }}
                                                            </label>
                                                        </div>
                                                        <div class='checkbox'>
                                                        <label class='label-container'>
                                                                <input type='checkbox' name='dropbox_allow_student_to_student' value='1' {{ $cbox_dropbox_allow_student_to_student }}>
                                                                <span class='checkmark'></span>
                                                                {{ trans('lang_dropbox_allow_student_to_student') }}
                                                            </label>
                                                        </div>
                                                        <div class='checkbox'>
                                                        <label class='label-container'>
                                                                <input type='checkbox' name='dropbox_allow_personal_messages' value='1' {{ $cbox_dropbox_allow_personal_messages }}>
                                                                <span class='checkmark'></span>
                                                                {{ trans('lang_dropbox_allow_personal_messages') }}
                                                            </label>
                                                        </div>
                                                        <div class='checkbox'>
                                                        <label class='label-container'>
                                                                <input id='personal_blog_enable' type='checkbox' name='personal_blog' value='1' {{ $cbox_personal_blog }}>
                                                                <span class='checkmark'></span>
                                                                {{ trans('lang_personal_blog') }}
                                                            </label>
                                                        </div>
                                                        <div class='checkbox'>
                                                        <label class='label-container'>
                                                                <input id='personal_blog_commenting_enable' type='checkbox' name='personal_blog_commenting' value='1' {{ $cbox_personal_blog_commenting }}>
                                                                <span class='checkmark'></span>
                                                                {{ trans('lang_personal_blog_commenting') }}
                                                            </label>
                                                        </div>
                                                        <div class='checkbox'>
                                                        <label class='label-container'>
                                                                <input id='personal_blog_rating_enable' type='checkbox' name='personal_blog_rating' value='1' {{ $cbox_personal_blog_rating }}>
                                                                <span class='checkmark'></span>
                                                                {{ trans('lang_personal_blog_rating') }}
                                                            </label>
                                                        </div>
                                                        <div class='checkbox'>
                                                        <label class='label-container'>
                                                                <input id='personal_blog_sharing_enable' type='checkbox' name='personal_blog_sharing' value='1' {{ $cbox_personal_blog_sharing }}>
                                                                <span class='checkmark'></span>
                                                                {{ trans('lang_personal_blog_sharing') }}
                                                            </label>
                                                        </div>

                                                        <div class='checkbox'>
                                                            <label class='label-container'>
                                                                <input id='eportfolio_enable' type='checkbox' name='eportfolio_enable' value='1' {{ $cbox_eportfolio_enable }}>
                                                                <span class='checkmark'></span>
                                                                {{ trans('lang_eportfolio_enable') }}
                                                            </label>
                                                        </div>
                                                        <div class='checkbox'>
                                                            <label class='label-container'>
                                                                <input type='checkbox' name='block_username_change' value='1' {{ $cbox_block_username_change }}>
                                                                <span class='checkmark'></span>
                                                                {{ trans('lang_block_username_change') }}
                                                            </label>
                                                        </div>
                                                        <div class='checkbox'>
                                                            <label class='label-container'>
                                                                <input type='checkbox' name='disable_name_surname_change' value='1' {{ $cbox_disable_name_surname_change }}>
                                                                <span class='checkmark'></span>
                                                                {{ trans('lang_disable_name_surname_change') }}
                                                                </label>
                                                        </div>
                                                        <div class='checkbox'>
                                                            <label class='label-container'>
                                                                <input type='checkbox' name='disable_email_change' value='1' {{ $cbox_disable_email_change }}>
                                                                <span class='checkmark'></span>
                                                                {{ trans('lang_disable_email_change') }}
                                                            </label>
                                                        </div>
                                                        <div class='checkbox'>
                                                            <label class='label-container'>
                                                                <input type='checkbox' name='disable_am_change' value='1' {{ $cbox_disable_am_change }}>
                                                                <span class='checkmark'></span>
                                                                {{ trans('lang_disable_am_change') }}
                                                            </label>
                                                        </div>
                                                        <div class='checkbox'>
                                                            <label class='label-container'>
                                                                <input {!! $id_enable_mobileapi !!} type='checkbox' name='enable_mobileapi' value='1' {{ $cbox_enable_mobileapi }}>
                                                                <span class='checkmark'></span>
                                                                {{ trans('lang_enable_mobileapi') }}
                                                            </label>
                                                        </div>
                                                        <div class='checkbox'>
                                                            <label class='label-container'>
                                                                <input id='social_sharing_links' type='checkbox' name='enable_social_sharing_links' value='1' {{ $cbox_enable_social_sharing_links }}>
                                                                <span class='checkmark'></span>
                                                                {{ trans('langEnableSocialSharingLiks') }}
                                                            </label>
                                                        </div>
                                                        <div class='checkbox'>
                                                            <label class='label-container'>
                                                                <input id='strong_passwords' type='checkbox' name='enable_strong_passwords' value='1' {{ $cbox_enable_strong_passwords }}>
                                                                <span class='checkmark'></span>
                                                                {{ trans('langEnableStrongPasswords') }}
                                                            </label>
                                                        </div>
                                                        <div class='checkbox'>
                                                            <label class='label-container'>
                                                                <input id='disable_student_unregister_cours' type='checkbox' name='disable_student_unregister_cours' value='1' {{ $cbox_disable_student_unregister_cours }}>
                                                                <span class='checkmark'></span>
                                                                {{ trans('langUnsubscribeCourse') }}
                                                            </label>
                                                        </div>
                                                        <div class='checkbox'>
                                                            <label class='label-container'>
                                                                <input id='individual_group_bookings' type='checkbox' name='individual_group_bookings' value='1' {{ $cbox_individual_group_bookings }}>
                                                                <span class='checkmark'></span>
                                                                {{ trans('langIndividualGroupBookings') }}
                                                            </label>
                                                        </div>
                                                    </div>
                                                </div>
                                                <hr><br>
                                                <div class='form-group mt-4'>
                                                    <label for='min_password_len' class='col-sm-12 control-label-notes'>{{ trans('langMinPasswordLen') }}: </label>
                                                    <div class='col-sm-12'>
                                                            <input type='text' class='form-control form-control-admin' name='min_password_len' id='min_password_len' value='{{ get_config('min_password_len') }}'>
                                                    </div>
                                                </div>



                                                <div class='form-group mt-4'>
                                                    <label for='min_password_len' class='col-sm-12 control-label-notes'>{{ trans('lang_max_glossary_terms') }} </label>
                                                    <div class='col-sm-12'>
                                                            <input class='form-control form-control-admin' type='text' name='max_glossary_terms' value='{{ $max_glossary_terms }}'>
                                                    </div>
                                                </div>



                                                <div class='form-group mt-4'>
                                                    <label for='min_password_len' class='col-sm-12 control-label-notes'>{{ trans('langActionsExpireInterval') }} ({{ trans('langMonthsUnit') }}):</label>
                                                    <div class='col-sm-12'>
                                                            <input type='text' class='form-control form-control-admin' name='actions_expire_interval' value='{{ get_config('actions_expire_interval') }}'>
                                                    </div>
                                                </div>
                                            </fieldset>
                                        </div>
                                    </div>

                                    <div class='card panelCard px-lg-4 py-lg-3 mt-4' id='nine'>

                                        <div class='card-header border-0 d-flex justify-content-between align-items-center'>

                                                <h3>{{ trans('langDocumentSettings') }}</h3>

                                        </div>
                                        <div class='card-body'>
                                            <fieldset>
                                                <div class='form-group'>
                                                <label class='col-sm-12 control-label-notes'>{{ trans('langEnableMyDocs') }}:</label>
                                                    <div class='col-sm-12'>
                                                        <div class='checkbox'>
                                                        <label class='label-container'>
                                                                <input type='checkbox' name='mydocs_teacher_enable' id='mydocs_teacher_enable_id' value='1' {{ $cbox_mydocs_teacher_enable }}>
                                                                <span class='checkmark'></span>
                                                                {{ trans('langTeachers') }}
                                                            </label>
                                                        </div>
                                                        <div class='checkbox'>
                                                        <label class='label-container'>
                                                                <input type='checkbox' name='mydocs_student_enable' id='mydocs_student_enable_id' value='1' {{ $cbox_mydocs_student_enable }}>
                                                                <span class='checkmark'></span>
                                                                {{ trans('langStudents') }}
                                                            </label>
                                                        </div>
                                                    </div>
                                                </div>



                                                <div class='form-group mt-4'>
                                                    <label class='col-sm-12 control-label-notes'>{{ trans('langMyDocsQuota') }} (MB):</label>
                                                    <div class='col-sm-12'>
                                                            <label>
                                                                <input class='form-control' type='text' name='mydocs_teacher_quota' id='mydocs_teacher_quota_id' value='{{ $mydocs_teacher_quota }}'>
                                                                {{ trans('langTeachers') }}
                                                            </label>
                                                            <label class='mt-md-0 mt-2 ms-md-3 ms-0'>
                                                                <input class='form-control' type='text' name='mydocs_student_quota' id='mydocs_student_quota_id' value='{{ $mydocs_student_quota }}'>
                                                                {{ trans('langStudents') }}
                                                            </label>
                                                    </div>
                                                </div>



                                                <div class='form-group mt-4'>
                                                    <label for='bio_quota' class='col-sm-12 control-label-notes'>{{ trans('langBioQuota') }} (MB):</label>
                                                    <div class='col-sm-12'>
                                                            <input class='form-control form-control-admin' type='text' name='bio_quota' id='bio_quota' value='{{ get_config('bio_quota') }}'>
                                                    </div>
                                                </div>
                                                <div class='checkbox mt-4'>
                                                <label class='label-container'>
                                                        <input type='checkbox' name='enable_common_docs' value='1' {{ $cbox_enable_common_docs }}>
                                                        <span class='checkmark'></span>
                                                        {{ trans('langEnableCommonDocs') }}
                                                    </label>
                                                </div>
                                                <div class='checkbox'>
                                                <label class='label-container'>
                                                    <input type='checkbox' name='enable_docs_public_write' value='1' {{ $cbox_enable_docs_public_write }}>
                                                    <span class='checkmark'></span>
                                                    {{ trans('langEnableDocsPublicWrite') }}
                                                </label>
                                            </div>
                                            </fieldset>
                                        </div>
                                    </div>

                                    <div class='card panelCard px-lg-4 py-lg-3 mt-4' id='ten'>
                                        <div class='card-header border-0 d-flex justify-content-between align-items-center'>

                                               <h3>{{ trans('langDefaultQuota') }}</h3>

                                        </div>
                                        <div class='card-body'>
                                            <fieldset>
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


                                    <div class='card panelCard px-lg-4 py-lg-3 mt-4' id='eleven'>

                                        <div class='card-header border-0 d-flex justify-content-between align-items-center'>

                                               <h3>{{ trans('langUploadWhitelist') }}</h3>

                                        </div>
                                        <div class='card-body'>
                                            <fieldset>
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



                                    <div class='card panelCard px-lg-4 py-lg-3 mt-4' id='twelve'>

                                        <div class='card-header border-0 d-flex justify-content-between align-items-center'>

                                               <h3>{{ trans('langLogActions') }}</h3>

                                        </div>
                                        <div class='card-body'>
                                            <fieldset>
                                                <div class='form-group'>
                                                    <div class='col-sm-12'>
                                                        <div class='checkbox'>
                                                        <label class='label-container'>
                                                                <input type='checkbox' name='disable_log_actions' value='1' {{ $cbox_disable_log_actions }}>
                                                                <span class='checkmark'></span>
                                                                {{ trans('lang_disable_log_actions') }}
                                                            </label>
                                                        </div>
                                                        <div class='checkbox'>
                                                        <label class='label-container'>
                                                                <input type='checkbox' name='disable_log_course_actions' value='1' {{ $cbox_disable_log_course_actions }}>
                                                                <span class='checkmark'></span>
                                                                {{ trans('lang_disable_log_course_actions') }}
                                                            </label>
                                                        </div>
                                                        <div class='checkbox'>
                                                        <label class='label-container'>
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



                                    <div class='card panelCard px-lg-4 py-lg-3 mt-4' id='thirteen'>

                                        <div class='card-header border-0 d-flex justify-content-between align-items-center'>

                                                <h3>{{ trans('langLoginFailCheck') }}</h3>

                                        </div>
                                        <div class='card-body'>
                                            <fieldset>
                                                <div class='form-group'>
                                                    <div class='col-sm-12'>
                                                        <div class='checkbox'>
                                                        <label class='label-container'>
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


                                    <div class='card panelCard px-lg-4 py-lg-3 mt-4' id='fourteen'>

                                        <div class='card-header border-0 d-flex justify-content-between align-items-center'>
                                            <h3>{{ trans('langMaintenance') }}</h3>
                                        </div>
                                        <div class='card-body'>
                                            <fieldset>
                                                <div class='form-group'>
                                                    <div class='col-sm-12'>
                                                        <div class='checkbox'>
                                                            <label class='label-container'>
                                                                <input type='checkbox' name='maintenance' value='1' {{ $cbox_maintenance }}>
                                                                <span class='checkmark'></span>
                                                                {{ trans('langMaintenanceMode') }}
                                                            </label>
                                                        </div>
                                                    </div>
                                                </div>
                                                <label class='col-sm-12 control-label-notes'>{{ trans('langMaintenanceChange') }}</label>
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
                                                    <label class='col-sm-12 control-label-notes mb-3'>{{ trans('langThemes') }}</label>
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
                                        <div class='col-12 d-flex justify-content-center'>
                                            <input class='btn submitAdminBtn' type='submit' name='submit' value='{{ trans('langModify') }}'>
                                            <a class='btn cancelAdminBtn ms-1' href='index.php'>{{ trans('langCancel') }}</a>
                                        </div>
                                    </div>
                                    {!! generate_csrf_token_form_field() !!}
                                </div> <!-- end scroll-spy -->

                            </form>
                        </div>


                        <div class='col-xl-3 col-lg-4 col-md-0 col-sm-0 col-0 d-none d-sm-block d-sm-none d-md-block d-md-none d-lg-block float-end hidden-xs' id='affixedSideNav'>


                            <nav id="navbar-example3" class="navbar navbar-light bg-transparent flex-column align-items-stretch p-3 sticky-top BorderSolidDes Borders admin-nav" style="z-index:0;">
                                <nav class="nav nav-pills flex-column">
                                    <a class="nav-link nav-link-adminTools Neutral-900-cl" href="#one">{{ trans('langBasicCfgSetting') }}</a>
                                    <a class="nav-link nav-link-adminTools Neutral-900-cl" href="#two">{{ trans('langUpgReg') }}</a>
                                    <a class="nav-link nav-link-adminTools Neutral-900-cl" href="#three">{{ trans('langSupportedLanguages') }}</a>
                                    <a class="nav-link nav-link-adminTools Neutral-900-cl" href="#four">{{ trans('langHomePageSettings') }}</a>

                                    <a class="nav-link nav-link-adminTools Neutral-900-cl" href="#five">{{ trans('langEmailSettings') }}</a>
                                    <a class="nav-link nav-link-adminTools Neutral-900-cl" href="#six">{{ trans('langCourseSettings') }}</a>
                                    <a class="nav-link nav-link-adminTools Neutral-900-cl" href="#seven">{{ trans('langMetaCommentary') }}</a>

                                    <a class="nav-link nav-link-adminTools Neutral-900-cl" href="#eight">{{ trans('langOtherOptions') }}</a>
                                    <a class="nav-link nav-link-adminTools Neutral-900-cl" href="#nine">{{ trans('langDocumentSettings') }}</a>
                                    <a class="nav-link nav-link-adminTools Neutral-900-cl" href="#ten">{{ trans('langDefaultQuota') }}</a>
                                    <a class="nav-link nav-link-adminTools Neutral-900-cl" href="#eleven">{{ trans('langUploadWhitelist') }}</a>
                                    <a class="nav-link nav-link-adminTools Neutral-900-cl" href="#twelve">{{ trans('langLogActions') }}</a>
                                    <a class="nav-link nav-link-adminTools Neutral-900-cl" href="#thirteen">{{ trans('langLoginFailCheck') }}</a>

                                    <a class="nav-link nav-link-adminTools Neutral-900-cl" href="#fourteen">{{ trans('langMaintenance') }}</a>
                                </nav>
                            </nav>
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

        /* Check if we are in safari and fix Bootstrap Affix*/
        if (navigator.userAgent.indexOf('Safari') != -1 && navigator.userAgent.indexOf('Chrome') == -1) {
            var stickywidget = $('#floatMenu');
            var explicitlySetAffixPosition = function() {
                stickywidget.css('left',stickywidget.offset().left+'px');
            };
            /* Before the element becomes affixed, add left CSS that is equal to the distance of the element from the left of the screen */
            stickywidget.on('affix.bs.affix',function(){
                stickywidget.removeAttr('style');
                explicitlySetAffixPosition();
            });
            stickywidget.on('affixed-bottom.bs.affix',function(){
                stickywidget.css('left', 'auto');
            });
            /* On resize of window, un-affix affixed widget to measure where it should be located, set the left CSS accordingly, re-affix it */
            $(window).resize(function(){
                if(stickywidget.hasClass('affix')) {
                    stickywidget.removeClass('affix');
                    explicitlySetAffixPosition();
                    stickywidget.addClass('affix');
                }
            });
        }

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
    });

</script>

@endsection
