@extends('layouts.default')

@section('content')
    {!! isset($action_bar) ?  $action_bar : '' !!}
    @if (Session::get('scheduleIndexing'))
        <!--schedule indexing if necessary-->
        <div class='alert alert-warning'>
            {{ trans('langIndexingNeeded') }}
            <a id='idxpbut' href='../search/idxpopup.php' onclick="return idxpopup('../search/idxpopup.php', 600, 500)">
               {{ trans('langHere') }}.
            </a>
        </div>
    @endif
    <div class='row'>
        <div class='col-sm-9'>
            <form class='form-horizontal' role='form' action='{{ $_SERVER['SCRIPT_NAME'] }}' method='post'>
                <div class='panel panel-default' id='one'>
                    <div class='panel-heading'>
                        <h2 class='panel-title'>{{ trans('langBasicCfgSetting') }}</h2>
                    </div>
                    <div class='panel-body'>
                        <fieldset>
                            <div class='form-group'>
                               <label for='formurlServer' class='col-sm-2 control-label'>{{ trans('langSiteUrl') }}:</label>
                               <div class='col-sm-10'>
                                    <input class='form-control' type='text' name='formurlServer' id='formurlServer' value='{{ $urlServer }}'>
                               </div>
                            </div>
                            <div class='form-group'>
                               <label for='formphpMyAdminURL' class='col-sm-2 control-label'>{{ trans('langphpMyAdminURL') }}:</label>
                               <div class='col-sm-10'>
                                    <input class='form-control' type='text' name='formphpMyAdminURL' id='formphpMyAdminURL' value='{{ get_config('phpMyAdminURL') }}'>
                               </div>
                            </div>
                            <div class='form-group'>
                               <label for='formphpSysInfoURL' class='col-sm-2 control-label'>{{ trans('langSystemInfoURL') }}:</label>
                               <div class='col-sm-10'>
                                   <input class='form-control' type='text' name='formphpSysInfoURL' id='formphpSysInfoURL' value='{{ get_config('phpSysInfoURL') }}'>
                               </div>
                            </div>
                            <div class='form-group'>
                               <label for='formemailAdministrator' class='col-sm-2 control-label'>{{ trans('langAdminEmail') }}:</label>
                               <div class='col-sm-10'>
                                   <input class='form-control' type='text' name='formemailAdministrator' id='formemailAdministrator' value='{{ get_config('email_sender') }}'>
                               </div>
                            </div>
                            <div class='form-group'>
                               <label for='formemailAdministrator' class='col-sm-2 control-label'>{{ trans('langDefaultAdminName') }}:</label>
                               <div class='col-sm-10'>
                                   <input class='form-control' type='text' name='formadministratorName' id='formadministratorName' value='{{ get_config('admin_name') }}'>
                               </div>
                            </div>
                            <div class='form-group'>
                               <label for='formsiteName' class='col-sm-2 control-label'>{{ trans('langCampusName') }}:</label>
                               <div class='col-sm-10'>
                                   <input class='form-control' type='text' name='formsiteName' id='formsiteName' value='{{ get_config('site_name') }}'>
                               </div>
                            </div>
                            <div class='form-group'>
                               <label for='formpostaddress' class='col-sm-2 control-label'>{{ trans('langPostMail') }}</label>
                               <div class='col-sm-10'>
                                   <textarea class='form-control' name='formpostaddress' id='formpostaddress'>{{ get_config('postaddress') }}</textarea>
                               </div>
                            </div>
                            <div class='form-group'>
                               <label for='formtelephone' class='col-sm-2 control-label'>{{ trans('langPhone') }}:</label>
                               <div class='col-sm-10'>
                                   <input class='form-control' type='text' name='formtelephone' id='formtelephone' value='{{ get_config('phone') }}'>
                               </div>
                            </div>
                            <div class='form-group'>
                               <label for='formfax' class='col-sm-2 control-label'>{{ trans('langFax') }}</label>
                               <div class='col-sm-10'>
                                   <input class='form-control' type='text' name='formfax' id='formfax' value='{{ get_config('fax') }}'>
                               </div>
                            </div>
                            <div class='form-group'>
                               <label for='formemailhelpdesk' class='col-sm-2 control-label'>{{ trans('langHelpDeskEmail') }}:</label>
                               <div class='col-sm-10'>
                                   <input class='form-control' type='text' name='formemailhelpdesk' id='formemailhelpdesk' value='{{ get_config('email_helpdesk') }}'>
                               </div>
                            </div>
                            <div class='form-group'>
                               <label for='formInstitution' class='col-sm-2 control-label'>{{ trans('langInstituteShortName') }}:</label>
                               <div class='col-sm-10'>
                                   <input class='form-control' type='text' name='formInstitution' id='formInstitution' value='{{ get_config('institution') }}'>
                               </div>
                            </div>
                            <div class='form-group'>
                               <label for='formInstitutionUrl' class='col-sm-2 control-label'>{{ trans('langInstituteName') }}:</label>
                               <div class='col-sm-10'>
                                   <input class='form-control' type='text' name='formInstitutionUrl' id='formInstitutionUrl' value='{{ get_config('institution_url') }}'>
                               </div>
                            </div>
                        </fieldset>
                    </div>
                </div>
                <div class='panel panel-default' id='two'>
                    <div class='panel-heading'>
                        <h2 class='panel-title'>{{ trans('langUpgReg') }}</h2>
                    </div>
                    <div class='panel-body'>
                        <fieldset>
                            <div class='form-group'>
                               <label for='user_registration' class='col-sm-3 control-label'>{{ trans('langUserRegistration') }}:</label>
                               <div class='col-sm-9'>
                                    {!! selection(
                                                    [
                                                    '1' => trans('langActivate'),
                                                    '0' => trans('langDeactivate')
                                                    ],
                                                    'user_registration',
                                                    get_config('user_registration'),
                                                    "class='form-control' id='user_registration'"
                                                ) !!}
                               </div>
                            </div>
                            <div class='form-group'>
                               <label for='registration_link' class='col-sm-3 control-label'>{{ trans('langRegistrationLink') }}:</label>
                               <div class='col-sm-9'>
                                    {!! selection(
                                                    $registration_link_options,
                                                    'registration_link',
                                                    get_config('registration_link', 'show'),
                                                    "class='form-control' id='registration_link'"
                                                ) !!}
                               </div>
                            </div>
                            <div class='form-group' id='registration-info-block'>
                               <label for='registration_info' class='col-sm-3 control-label'>{{ trans('langRegistrationInfo') }}:</label>
                               <div class='col-sm-9'>
                                   {!! $registration_info_textarea !!}
                               </div>
                            </div>
                            <div class='form-group'>
                               <label for='eclass_stud_reg' class='col-sm-3 control-label'>{{ trans('langUserAccount') }}  {{ trans('langViaeClass') }}:</label>
                               <div class='col-sm-9'>
                                    {!! selection(
                                                    [
                                                        '0' => trans('langDisableEclassStudReg'),
                                                        '1' => trans('langReqRegUser'),
                                                        '2' => trans('langDisableEclassStudRegType')
                                                    ],
                                                    'eclass_stud_reg',
                                                    get_config('eclass_stud_reg'),
                                                    "class='form-control' id='eclass_stud_reg'"
                                                ) !!}
                               </div>
                            </div>
                            <div class='form-group'>
                               <label for='alt_auth_stud_reg' class='col-sm-3 control-label'>{{ trans('langUserAccount') }} {{ trans('langViaAltAuthMethods') }}:</label>
                               <div class='col-sm-9'>
                                    {!! selection(
                                                [
                                                    '0' => trans('langDisableEclassStudReg'),
                                                    '1' => trans('langReqRegUser'),
                                                    '2' => trans('langDisableEclassStudRegType')
                                                ],
                                                'alt_auth_stud_reg',
                                                get_config('alt_auth_stud_reg'),
                                                "class='form-control' id='alt_auth_stud_reg'"
                                            ) !!}
                               </div>
                            </div>
                            <div class='form-group'>
                               <label for='eclass_prof_reg' class='col-sm-3 control-label'>{{ trans('langProfAccount') }} {{ trans('langViaeClass') }}:</label>
                               <div class='col-sm-9'>
                                        {!! selection(
                                            array(
                                                '0' => trans('langDisableEclassProfReg'),
                                                '1' => trans('langReqRegProf')
                                            ),
                                            'eclass_prof_reg',
                                            get_config('eclass_prof_reg'),
                                            "class='form-control' id='eclass_prof_reg'"
                                        ) !!}
                               </div>
                            </div>
                            <div class='form-group'>
                               <label for='alt_auth_prof_reg' class='col-sm-3 control-label'>{{ trans('langProfAccount') }} {{ trans('langViaAltAuthMethods') }}:</label>
                               <div class='col-sm-9'>
                                    {!! selection(
                                            array(
                                                    '0' => trans('langDisableEclassProfReg'),
                                                    '1' => trans('langReqRegProf')
                                                ),
                                            'alt_auth_prof_reg',
                                            get_config('alt_auth_prof_reg'),
                                            "class='form-control' id='alt_auth_prof_reg'"
                                        ) !!}
                               </div>
                            </div>
                            <div class='form-group'>
                               <label for='formdurationAccount' class='col-sm-3 control-label'>{{ trans('langUserDurationAccount') }} ({{ trans('langMonthsUnit') }}): </label>
                               <div class='col-sm-9'>
                                    <input type='text' class='form-control' name='formdurationAccount' id='formdurationAccount' maxlength='3' value='{{ get_config('account_duration') / MONTHS }}'>
                               </div>
                            </div>
                            <div class='form-group'>
                                <label class='col-sm-3 control-label'>{{ trans('lang_display_captcha_label') }}:</label>
                                <div class='checkbox col-sm-9'>
                                    <label>
                                        <input type='checkbox' name='display_captcha' value='1' {{ $cbox_display_captcha }} {{ $disable_display_captcha }}>
                                        {{ trans('lang_display_captcha') }}
                                    </label>
                                    {!! $message_display_captcha !!}
                                </div>
                            </div>
                            <div class='form-group'>
                                <label class='col-sm-3 control-label'>{{ trans('langGuestLoginLabel') }}:</label>
                                <div class='col-sm-9'>
                                    {!! selection([
                                                    'off' => trans('m[deactivate]'),
                                                    'on' => trans('m[activate]'),
                                                    'link' => trans('langGuestLoginLinks')
                                                    ],
                                                    'course_guest',
                                                    get_config('course_guest', 'on'),
                                                    "class='form-control'"
                                                ) !!}
                                </div>
                            </div>
                        </fieldset>
                    </div>
                </div>
                <div class='panel panel-default' id='three'>
                    <div class='panel-heading'>
                        <h2 class='panel-title'>{{ trans('langEclassThemes') }}</h2>
                    </div>
                    <div class='panel-body'>
                        <fieldset>
                            <div class='form-group'>
                               <label for='default_language' class='col-sm-3 control-label'>{{ trans('langMainLang') }}: </label>
                               <div class='col-sm-9'>
                                   {!! selection(
                                                    $selectable_langs,
                                                    'default_language',
                                                    get_config('default_language'),
                                                    "class='form-control' id='default_language'"
                                                ) !!}
                               </div>
                            </div>
                            <div class='form-group'>
                                <label class='col-sm-3 control-label'>{{ trans('langSupportedLanguages') }}:</label>
                                <div class='col-sm-9'>
                                {!! implode(' ', $sel) !!}
                                </div>
                            </div>
                            <div class='form-group'>
                               <label for='theme' class='col-sm-3 control-label'>{{ trans('lang_login_form') }}: </label>
                               <div class='col-sm-9'>
                                    <div class='checkbox'>
                                        <label>
                                            <input id='hide_login_check' type='checkbox' name='dont_display_login_form' value='1' {{ $cbox_dont_display_login_form }}>
                                            {{ trans('lang_dont_display_login_form') }}
                                        </label>
                                    </div>
                                    <div class='checkbox'>
                                        <label>
                                            <input id='hide_login_link_check' type='checkbox' name='hide_login_link' value='1' {{ $cbox_hide_login_link }}>
                                            {{ trans('lang_hide_login_link') }}
                                        </label>
                                    </div>
                               </div>
                            </div>
                        </fieldset>
                    </div>
                </div>

                {!! mail_settings_form() !!}

                <div class='panel panel-default' id='five'>
                    <div class='panel-heading'>
                        <h2 class='panel-title'>{{ trans('langCourseSettings') }}</h2>
                    </div>
                    <div class='panel-body'>
                        <fieldset>
                            <div class='form-group'>
                               <div class='col-sm-12'>
                                    <div class='checkbox'>
                                        <label>
                                            <input type='checkbox' name='course_multidep' value='1' {{ $cbox_course_multidep }}>
                                            {{ trans('lang_course_multidep') }}
                                        </label>
                                    </div>
                                    <div class='checkbox'>
                                        <label>
                                            <input type='checkbox' name='user_multidep' value='1' {{ $cbox_user_multidep }}>
                                            {{ trans('lang_user_multidep') }}
                                        </label>
                                    </div>
                                    <div class='checkbox'>
                                        <label>
                                            <input id='uown' type='checkbox' name='restrict_owndep' value='1' {{ $cbox_restrict_owndep }}>
                                            {{ trans('lang_restrict_owndep') }}
                                        </label>
                                    </div>
                                    <div class='checkbox'>
                                        <label>
                                            <input id='town' type='checkbox' name='restrict_teacher_owndep' value='1' {{ $town_dis }} {{ $cbox_restrict_teacher_owndep }}>
                                            {{ trans('lang_restrict_teacher_owndep') }}
                                        </label>
                                    </div>
                                    <div class='checkbox'>
                                        <label>
                                            <input type='checkbox' name='allow_teacher_clone_course' value='1' {{ $cbox_allow_teacher_clone_course }}>
                                            {{ trans('lang_allow_teacher_clone_course') }}
                                        </label>
                                    </div>
                               </div>
                            </div>
                        </fieldset>
                    </div>
                </div>
                <div class='panel panel-default' id='six'>
                    <div class='panel-heading'>
                        <h2 class='panel-title'>{{ trans('langMetaCommentary') }}</h2>
                    </div>
                    <div class='panel-body'>
                        <fieldset>
                            <div class='form-group'>
                               <div class='col-sm-12'>
                                    <div class='checkbox'>
                                        <label>
                                            <input type='checkbox' name='insert_xml_metadata' value='1' {{ $cbox_insert_xml_metadata }}>
                                            {{ trans('lang_insert_xml_metadata') }}
                                        </label>
                                    </div>
                                    <div class='checkbox'>
                                        <label>
                                            <input type='checkbox' id='course_metadata' name='course_metadata' value='1' {{ $cbox_course_metadata }}>
                                            {{ trans('lang_course_metadata') }}
                                        </label>
                                    </div>
                                    <div class='checkbox'>
                                        <label>
                                            <input type='checkbox' id='opencourses_enable' name='opencourses_enable' value='1' {{ $cbox_opencourses_enable }}>
                                            {{ trans('lang_opencourses_enable') }}
                                        </label>
                                    </div>
                                </div>
                            </div>
                        </fieldset>
                    </div>
                </div>



                <div class='panel panel-default' id='seven'>
                    <div class='panel-heading'>
                        <h2 class='panel-title'>{{ trans('langOtherOptions') }}</h2>
                    </div>
                    <div class='panel-body'>
                        <fieldset>
                            <div class='form-group'>
                               <div class='col-sm-12'>
                                    <div class='checkbox'>
                                        <label>
                                            <input type='checkbox' name='case_insensitive_usernames' value='1' {{ $cbox_case_insensitive_usernames }}>
                                            {{ trans('langCaseInsensitiveUsername') }}
                                        </label>
                                    </div>
                                    <div class='checkbox'>
                                        <label>
                                            <input type='checkbox' name='email_required' value='1' {{ $cbox_email_required }}>
                                            {{ trans('lang_email_required') }}
                                        </label>
                                    </div>
                                    <div class='checkbox'>
                                        <label>
                                            <input type='checkbox' name='email_verification_required' value='1' {{ $cbox_email_verification_required }}>
                                            {{ trans('lang_email_verification_required') }}
                                        </label>
                                    </div>
                                    <div class='checkbox'>
                                        <label>
                                            <input type='checkbox' name='am_required' value='1' {{ $cbox_am_required }}>
                                            {{ trans('lang_am_required') }}
                                        </label>
                                    </div>
                                    <div class='checkbox'>
                                        <label>
                                            <input id='index_enable' type='checkbox' name='enable_indexing' value='1' {{ $cbox_enable_indexing }}>
                                            {{ trans('langEnableIndexing') }}
                                        </label>
                                    </div>
                                    <div class='checkbox'>
                                        <label>
                                            <input id='search_enable' type='checkbox' name='enable_search' value='1' {{ $cbox_enable_search }}>
                                            {{ trans('langEnableSearch') }}
                                        </label>
                                    </div>
                                    <div class='checkbox'>
                                        <label>
                                            <input type='checkbox' name='dropbox_allow_student_to_student' value='1' {{ $cbox_dropbox_allow_student_to_student }}>
                                            {{ trans('lang_dropbox_allow_student_to_student') }}
                                        </label>
                                    </div>
                                    <div class='checkbox'>
                                        <label>
                                            <input type='checkbox' name='dropbox_allow_personal_messages' value='1' {{ $cbox_dropbox_allow_personal_messages }}>
                                            {{ trans('lang_dropbox_allow_personal_messages') }}
                                        </label>
                                    </div>
                                    <div class='checkbox'>
                                        <label>
                                            <input id='personal_blog_enable' type='checkbox' name='personal_blog' value='1' {{ $cbox_personal_blog }}>
                                            {{ trans('lang_personal_blog') }}
                                        </label>
                                    </div>
                                    <div class='checkbox'>
                                        <label>
                                            <input id='personal_blog_commenting_enable' type='checkbox' name='personal_blog_commenting' value='1' {{ $cbox_personal_blog_commenting }}>
                                            {{ trans('lang_personal_blog_commenting') }}
                                        </label>
                                    </div>
                                    <div class='checkbox'>
                                        <label>
                                            <input id='personal_blog_rating_enable' type='checkbox' name='personal_blog_rating' value='1' {{ $cbox_personal_blog_rating }}>
                                            {{ trans('lang_personal_blog_rating') }}
                                        </label>
                                    </div>
                                    <div class='checkbox'>
                                        <label>
                                            <input id='personal_blog_sharing_enable' type='checkbox' name='personal_blog_sharing' value='1' {{ $cbox_personal_blog_sharing }}>
                                            {{ trans('lang_personal_blog_sharing') }}
                                        </label>
                                    </div>
                                    <div class='checkbox'>
                                        <label>
                                            <input id='eportfolio_enable' type='checkbox' name='eportfolio_enable' value='1' {{ $cbox_eportfolio_enable }}>
                                            {{ trans('lang_eportfolio_enable') }}
                                        </label>
                                    </div>
                                    <div class='checkbox'>
                                        <label>
                                            <input type='checkbox' name='block_username_change' value='1' {{ $cbox_block_username_change }}>
                                            {{ trans('lang_block_username_change') }}
                                        </label>
                                    </div>
                                    <div class='checkbox'>
                                        <label>
                                            <input {!! $id_enable_mobileapi !!} type='checkbox' name='enable_mobileapi' value='1' {{ $cbox_enable_mobileapi }}>
                                            {{ trans('lang_enable_mobileapi') }}
                                        </label>
                                    </div>
                                    <div class='checkbox'>
                                        <label>
                                            <input id='social_sharing_links' type='checkbox' name='enable_social_sharing_links' value='1' {{ $cbox_enable_social_sharing_links }}>
                                            {{ trans('langEnableSocialSharingLiks') }}
                                        </label>
                                    </div>
                                   <div class='checkbox'>
                                        <label>
                                            <input id='strong_passwords' type='checkbox' name='enable_strong_passwords' value='1' {{ $cbox_enable_strong_passwords }}>
                                            {{ trans('langEnableStrongPasswords') }}
                                        </label>
                                    </div>
                               </div>
                            </div>
                            <hr><br>
                            <div class='form-group'>
                               <label for='min_password_len' class='col-sm-4 control-label'>{{ trans('langMinPasswordLen') }}: </label>
                               <div class='col-sm-8'>
                                    <input type='text' class='form-control' name='min_password_len' id='min_password_len' value='{{ get_config('min_password_len') }}'>
                               </div>
                            </div>
                            <div class='form-group'>
                               <label for='min_password_len' class='col-sm-4 control-label'>{{ trans('lang_max_glossary_terms') }} </label>
                               <div class='col-sm-8'>
                                    <input class='form-control' type='text' name='max_glossary_terms' value='{{ $max_glossary_terms }}'>
                               </div>
                            </div>
                            <div class='form-group'>
                               <label for='min_password_len' class='col-sm-4 control-label'>{{ trans('langActionsExpireInterval') }} ({{ trans('langMonthsUnit') }}):</label>
                               <div class='col-sm-8'>
                                    <input type='text' class='form-control' name='actions_expire_interval' value='{{ get_config('actions_expire_interval') }}'>
                               </div>
                            </div>
                        </fieldset>
                    </div>
                </div>

                <div class='panel panel-default' id='eight'>
                    <div class='panel-heading'>
                        <h2 class='panel-title'>{{ trans('langDocumentSettings') }}</h2>
                    </div>
                    <div class='panel-body'>
                        <fieldset>
                            <div class='form-group'>
                               <label class='col-sm-4 control-label'>{{ trans('langEnableMyDocs') }}:</label>
                               <div class='col-sm-8'>
                                    <div class='checkbox'>
                                        <label>
                                            <input type='checkbox' name='mydocs_teacher_enable' id='mydocs_teacher_enable_id' value='1' {{ $cbox_mydocs_teacher_enable }}>
                                            {{ trans('langTeachers') }}
                                        </label>
                                    </div>
                                    <div class='checkbox'>
                                        <label>
                                            <input type='checkbox' name='mydocs_student_enable' id='mydocs_student_enable_id' value='1' {{ $cbox_mydocs_student_enable }}>
                                            {{ trans('langStudents') }}
                                        </label>
                                    </div>
                               </div>
                            </div>
                            <div class='form-group'>
                               <label class='col-sm-4 control-label'>{{ trans('langMyDocsQuota') }} (MB):</label>
                               <div class='col-sm-8'>
                                    <label>
                                        <input type='text' name='mydocs_teacher_quota' id='mydocs_teacher_quota_id' value='{{ $mydocs_teacher_quota }}'>
                                        {{ trans('langTeachers') }}
                                    </label>
                                    <label>
                                        <input type='text' name='mydocs_student_quota' id='mydocs_student_quota_id' value='{{ $mydocs_student_quota }}'>
                                        {{ trans('langStudents') }}
                                    </label>
                               </div>
                            </div>
                            <div class='form-group'>
                               <label for='bio_quota' class='col-sm-4 control-label'>{{ trans('langBioQuota') }} (MB):</label>
                               <div class='col-sm-4'>
                                    <input class='form-control' type='text' name='bio_quota' id='bio_quota' value='{{ get_config('bio_quota') }}'>
                               </div>
                            </div>
                            <div class='checkbox'>
                                <label>
                                    <input type='checkbox' name='enable_common_docs' value='1' {{ $cbox_enable_common_docs }}>
                                    {{ trans('langEnableCommonDocs') }}
                                </label>
                            </div>
                        </fieldset>
                    </div>
                </div>

                <div class='panel panel-default' id='nine'>
                    <div class='panel-heading'>
                        <h2 class='panel-title'>{{ trans('langDefaultQuota') }}</h2>
                    </div>
                    <div class='panel-body'>
                        <fieldset>
                            <div class='form-group'>
                               <label for='doc_quota' class='col-sm-4 control-label'>{{ trans('langDocQuota') }} (MB):</label>
                               <div class='col-sm-8'>
                                    <input class='form-control' type='text' name='doc_quota' id='doc_quota' value='{{ get_config('doc_quota') }}'>
                               </div>
                            </div>
                            <div class='form-group'>
                               <label for='video_quota' class='col-sm-4 control-label'>{{ trans('langVideoQuota') }} (MB):</label>
                               <div class='col-sm-8'>
                                    <input class='form-control' type='text' name='video_quota' id='video_quota' value='{{ get_config('video_quota') }}'>
                               </div>
                            </div>
                            <div class='form-group'>
                               <label for='group_quota' class='col-sm-4 control-label'>{{ trans('langGroupQuota') }} (MB):</label>
                               <div class='col-sm-8'>
                                    <input class='form-control' type='text' name='group_quota' id='group_quota' value='{{ get_config('group_quota') }}'>
                               </div>
                            </div>
                            <div class='form-group'>
                               <label for='dropbox_quota' class='col-sm-4 control-label'>{{ trans('langDropboxQuota') }} (MB):</label>
                               <div class='col-sm-8'>
                                    <input class='form-control' type='text' name='dropbox_quota' id='dropbox_quota' value='{{ get_config('dropbox_quota') }}'>
                               </div>
                            </div>
                        </fieldset>
                    </div>
                </div>
                <div class='panel panel-default' id='ten'>
                    <div class='panel-heading'>
                        <h2 class='panel-title'>{{ trans('langUploadWhitelist') }}</h2>
                    </div>
                    <div class='panel-body'>
                        <fieldset>
                            <div class='form-group'>
                               <label for='student_upload_whitelist' class='col-sm-4 control-label'>{{ trans('langStudentUploadWhitelist') }}:</label>
                               <div class='col-sm-8'>
                                    <textarea class='form-control' rows='6' name='student_upload_whitelist' id='student_upload_whitelist'>{{ get_config('student_upload_whitelist') }}</textarea>
                               </div>
                            </div>
                            <div class='form-group'>
                               <label for='teacher_upload_whitelist' class='col-sm-4 control-label'>{{ trans('langTeacherUploadWhitelist') }}:</label>
                               <div class='col-sm-8'>
                                    <textarea class='form-control' rows='6' name='teacher_upload_whitelist' id='teacher_upload_whitelist'>{{ get_config('teacher_upload_whitelist') }}</textarea>
                               </div>
                            </div>
                        </fieldset>
                    </div>
                </div>


                <div class='panel panel-default' id='eleven'>
                    <div class='panel-heading'>
                        <h2 class='panel-title'>{{ trans('langLogActions') }}</h2>
                    </div>
                    <div class='panel-body'>
                        <fieldset>
                            <div class='form-group'>
                               <div class='col-sm-12'>
                                    <div class='checkbox'>
                                        <label>
                                            <input type='checkbox' name='disable_log_actions' value='1' {{ $cbox_disable_log_actions }}>
                                            {{ trans('lang_disable_log_actions') }}
                                        </label>
                                    </div>
                                    <div class='checkbox'>
                                        <label>
                                            <input type='checkbox' name='disable_log_course_actions' value='1' {{ $cbox_disable_log_course_actions }}>
                                            {{ trans('lang_disable_log_course_actions') }}
                                        </label>
                                    </div>
                                    <div class='checkbox'>
                                        <label>
                                            <input type='checkbox' name='disable_log_system_actions' value='1' {{ $cbox_disable_log_system_actions }}>
                                            {{ trans('lang_disable_log_system_actions') }}
                                        </label>
                                    </div>
                                </div>
                            </div>
                            <hr><br>
                            <div class='form-group'>
                               <label for='log_expire_interval' class='col-sm-4 control-label'>{{ trans('langLogExpireInterval') }} ({{ trans('langMonthsUnit') }}):</label>
                               <div class='col-sm-8'>
                                    <input class='form-control' type='text' name='log_expire_interval' id='log_expire_interval' value='{{ get_config('log_expire_interval') }}'>
                               </div>
                            </div>
                            <div class='form-group'>
                               <label for='log_purge_interval' class='col-sm-4 control-label'>{{ trans('langLogPurgeInterval') }} ({{ trans('langMonthsUnit') }}):</label>
                               <div class='col-sm-8'>
                                    <input class='form-control' type='text' name='log_purge_interval' id='log_purge_interval' value='{{ get_config('log_purge_interval') }}'>
                               </div>
                            </div>
                        </fieldset>
                    </div>
                </div>
                <div class='panel panel-default' id='twelve'>
                    <div class='panel-heading'>
                        <h2 class='panel-title'>{{ trans('langLoginFailCheck') }}</h2>
                    </div>
                    <div class='panel-body'>
                        <fieldset>
                            <div class='form-group'>
                               <div class='col-sm-12'>
                                    <div class='checkbox'>
                                        <label>
                                            <input id='login_fail_check' type='checkbox' name='login_fail_check' value='1' {{ $cbox_login_fail_check }}>
                                            {{ trans('langEnableLoginFailCheck') }}
                                        </label>
                                    </div>
                                </div>
                            </div>
                            <div class='form-group' id='login_fail_threshold'>
                               <label for='login_fail_threshold' class='col-sm-4 control-label'>{{ trans('langLoginFailThreshold') }}:</label>
                               <div class='col-sm-8'>
                                    <input class='form-control' type='text' name='login_fail_threshold' id='login_fail_threshold' value='{{ get_config('login_fail_threshold') }}'>
                               </div>
                            </div>
                            <div class='form-group' id='login_fail_deny_interval'>
                               <label for='login_fail_deny_interval' class='col-sm-4 control-label'>{{ trans('langLoginFailDenyInterval') }} ({{ trans('langMinute') }}):</label>
                               <div class='col-sm-8'>
                                    <input class='form-control' type='text' name='login_fail_deny_interval' id='login_fail_deny_interval' value='{{ get_config('login_fail_deny_interval') }}'>
                               </div>
                            </div>
                            <div class='form-group' id='login_fail_forgive_interval'>
                               <label for='login_fail_forgive_interval' class='col-sm-4 control-label'>{{ trans('langLoginFailForgiveInterval') }} ({{ trans('langHours') }}):</label>
                               <div class='col-sm-8'>
                                    <input class='form-control' type='text' name='login_fail_forgive_interval' id='login_fail_forgive_interval' value='{{ get_config('login_fail_forgive_interval') }}'>
                               </div>
                            </div>
                        </fieldset>
                    </div>
                </div>
                {!! showSecondFactorChallenge() !!}
                <div class='form-group'>
                    <div class='col-sm-12'>
                        <input class='btn btn-primary' type='submit' name='submit' value='{{ trans('langModify') }}'>
                        <a class='btn btn-default' href='index.php'>{{ trans('langCancel') }}</a>
                    </div>
                </div>
                {!! generate_csrf_token_form_field() !!}


            </form>
        </div>

        <div class='col-sm-3 hidden-xs' id='affixedSideNav'>
            <ul id='floatMenu' class='nav nav-pills nav-stacked well well-sm' role='tablist'>
                <li class='active'><a href='#one'>{{ trans('langBasicCfgSetting') }}</a></li>
                <li><a href='#two'>{{ trans('langUpgReg') }}</a></li>
                <li><a href='#three'>{{ trans('langEclassThemes') }}</a></li>
                <li><a href='#four'>{{ trans('langEmailSettings') }}</a></li>
                <li><a href='#five'>{{ trans('langCourseSettings') }}</a></li>
                <li><a href='#six'>{{ trans('langMetaCommentary') }}</a></li>
                <li><a href='#seven'>{{ trans('langOtherOptions') }}</a></li>
                <li><a href='#eight'>{{ trans('langDocumentSettings') }}</a></li>
                <li><a href='#nine'>{{ trans('langDefaultQuota') }}</a></li>
                <li><a href='#ten'>{{ trans('langUploadWhitelist') }}</a></li>
                <li><a href='#eleven'>{{ trans('langLogActions') }}</a></li>
                <li><a href='#twelve'>{{ trans('langLoginFailCheck') }}</a></li>
            </ul>
        </div>

    </div>
    {!! modalConfirmation('confirmIndexDialog', 'confirmIndexLabel', trans('langConfirmEnableIndexTitle'), trans('langConfirmEnableIndex'), 'confirmIndexCancel', 'confirmIndexOk') !!}
    {!! modalConfirmation('confirmMobileAPIDialog', 'confirmMobileAPILabel', trans('langConfirmEnableMobileAPITitle'), trans('langConfirmEnableMobileAPI'), 'confirmMobileAPICancel', 'confirmMobileAPIOk') !!}
@endsection
