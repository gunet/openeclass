<?php

/* ========================================================================
 * Open eClass 4.0
 * E-learning and Course Management System
 * ========================================================================
 * Copyright 2003-2016  Greek Universities Network - GUnet
 * A full copyright notice can be read in "/info/copyright.txt".
 * For a full list of contributors, see "credits.txt".
 *
 * Open eClass is an open platform distributed in the hope that it will
 * be useful (without any warranty), under the terms of the GNU (General
 * Public License) as published by the Free Software Foundation.
 * The full license can be read in "/info/license/license_gpl.txt".
 *
 * Contact address: GUnet Asynchronous eLearning Group,
 *                  Network Operations Center, University of Athens,
 *                  Panepistimiopolis Ilissia, 15784, Athens, Greece
 *                  e-mail: info@openeclass.org
 * ======================================================================== */


$require_admin = true;
require_once '../../include/baseTheme.php';
require_once 'modules/auth/auth.inc.php';
require_once 'modules/admin/modalconfirmation.php';
require_once 'include/mailconfig.php';

if (isset($_POST['updatePrivacyPolicy'])) {
    set_config('privacy_policy_timestamp', date('Y-m-d H:i:s'));
    redirect_to_home_page('modules/admin/eclassconf.php');
}

$toolName = $langEclassConf;
$navigation[] = array('url' => 'index.php', 'name' => $langAdmin);

define('MONTHS', 30 * 24 * 60 * 60);

$data['mail_form_js'] = $mail_form_js;
$data['registration_link_options'] = $registration_link_options = [
    'show' => $langViewShow,
    'hide' => $langViewHide,
    'show_text' => $langRegistrationShowText];

// Save new `config` table
if (isset($_POST['submit'])) {
    if (!isset($_POST['token']) || !validate_csrf_token($_POST['token'])) csrf_token_error();
    checkSecondFactorChallenge();
    $active_lang_codes = array();
    if (isset($_POST['av_lang'])) {
        foreach ($_POST['av_lang'] as $langcode) {
            if (isset($language_codes[$langcode])) {
                $active_lang_codes[] = $langcode;
            }
        }
    }
    if (!count($active_lang_codes)) {
        $active_lang_codes = array('el');
    }
    if (isset($_POST['default_language']) and
            isset($language_codes[$_POST['default_language']])) {
        set_config('default_language', $_POST['default_language']);
    }

    $homepageSet = $_POST['homepageSet'];
    if ($homepageSet == 'external') {
        set_config('homepage', 'external');
        set_config('landing_name', $_POST['landing_name']);
        set_config('landing_url', $_POST['landing_url']);
    } elseif ($homepageSet == 'toolbox') {
        set_config('homepage', 'toolbox');
        set_config('toolbox_name', $_POST['toolbox_name']);
        set_config('toolbox_title', $_POST['toolbox_title']);
        set_config('toolbox_intro', $_POST['toolbox_intro']);
    } else {
        set_config('homepage', 'default');
        set_config('homepage_title', $_POST['homepage_title']);
        set_config('homepage_name', $_POST['homepage_name']);
        set_config('homepage_intro', purify($_POST['homepage_intro']));
    }

    set_config('active_ui_languages', implode(' ', $active_lang_codes));
    set_config('base_url', $_POST['formurlServer']);
    set_config('phpMyAdminURL', $_POST['formphpMyAdminURL']);
    set_config('phpSysInfoURL', $_POST['formphpSysInfoURL']);
    set_config('email_sender', $_POST['formemailAdministrator']);
    set_config('admin_name', $_POST['formadministratorName']);
    set_config('site_name', $_POST['formsiteName']);
    set_config('phone', $_POST['formtelephone']);
    set_config('email_helpdesk', $_POST['formemailhelpdesk']);
    set_config('institution', $_POST['formInstitution']);
    set_config('institution_url', $_POST['formInstitutionUrl']);
    set_config('postaddress', $_POST['formpostaddress']);
    set_config('fax', $_POST['formfax']);
    set_config('account_duration', MONTHS * $_POST['formdurationAccount']);
    set_config('min_password_len', intval($_POST['min_password_len']));
    set_config('student_upload_whitelist', $_POST['student_upload_whitelist']);
    set_config('teacher_upload_whitelist', $_POST['teacher_upload_whitelist']);

    $privacyPolicyChanged = false;
    foreach ($session->active_ui_languages as $langCode) {
        $langVar = 'privacy_policy_text_' . $langCode;
        if (isset($_POST[$langVar])) {
            $oldText = get_config($langVar);
            $newText = purify(trim($_POST[$langVar]));
            if ($oldText != $newText) {
                set_config($langVar, purify(trim($_POST[$langVar])));
                $privacyPolicyChanged = true;
            }
        }
    }

    $config_vars = [
        'email_required' => true,
        'email_verification_required' => true,
        'am_required' => true,
        'dont_display_login_form' => true,
        'hide_login_link' => true,
        'dropbox_allow_student_to_student' => true,
        'dropbox_allow_personal_messages' => true,
        'eportfolio_enable' => true,
        'personal_blog' => true,
        'personal_blog_public' => true,
        'personal_blog_commenting' => true,
        'personal_blog_rating' => true,
        'personal_blog_sharing' => true,
        'block_username_change' => true,
        'block_duration_account' => true,
        'block_duration_alt_account' => true,
        'display_captcha' => true,
        'insert_xml_metadata' => true,
        'enable_mobileapi' => true,
        'doc_quota' => true,
        'bio_quota' => true,
        'group_quota' => true,
        'video_quota' => true,
        'dropbox_quota' => true,
        'max_glossary_terms' => true,
        'case_insensitive_usernames' => true,
        'course_multidep' => true,
        'user_multidep' => true,
        'restrict_owndep' => true,
        'restrict_teacher_owndep' => true,
        'allow_teacher_clone_course' => true,
        'disable_log_actions' => true,
        'disable_log_course_actions' => true,
        'disable_log_system_actions' => true,
        'user_registration' => true,
        'eclass_stud_reg' => true,
        'alt_auth_stud_reg' => true,
        'eclass_prof_reg' => true,
        'alt_auth_prof_reg' => true,
        'enable_indexing' => true,
        'enable_search' => true,
        'enable_common_docs' => true,
        'enable_docs_public_write' => true,
        'enable_social_sharing_links' => true,
        'enable_strong_passwords' => true,
        'login_fail_check' => true,
        'login_fail_threshold' => true,
        'login_fail_deny_interval' => true,
        'login_fail_forgive_interval' => true,
        'actions_expire_interval' => true,
        'log_expire_interval' => true,
        'log_purge_interval' => true,
        'course_metadata' => true,
        'opencourses_enable' => true,
        'mydocs_student_enable' => true,
        'mydocs_teacher_enable' => true,
        'offline_course' => true,
        'activate_privacy_policy_text' => true,
        'activate_privacy_policy_consent' => true,
    ];

    register_posted_variables($config_vars, 'all', 'intval');

    if (isset($_POST['registration_link']) and
        isset($registration_link_options[$_POST['registration_link']])) {
        set_config('registration_link', $_POST['registration_link']);
    } else {
        set_config('registration_link', 'show');
    }

    if (isset($_POST['registration_info'])) {
        set_config('registration_info', purify($_POST['registration_info']));
    }

    store_mail_config();

    if (isset($_POST['mydocs_student_quota'])) {
        set_config('mydocs_student_quota', floatval($_POST['mydocs_student_quota']));
    }
    if (isset($_POST['mydocs_teacher_quota'])) {
        set_config('mydocs_teacher_quota', floatval($_POST['mydocs_teacher_quota']));
    }

    if (!in_array($_POST['course_guest'], array('on', 'off', 'link'))) {
        set_config('course_guest', 'off');
    } else {
        set_config('course_guest', $_POST['course_guest']);
    }

    if ($GLOBALS['opencourses_enable'] == 1) {
        $GLOBALS['course_metadata'] = 1;
    }

    if ($GLOBALS['enable_search'] == 1) {
        $GLOBALS['enable_indexing'] = 1;
    }

    // restrict_owndep and restrict_teacher_owndep are interdependent
    if ($GLOBALS['restrict_owndep'] == 0) {
        $GLOBALS['restrict_teacher_owndep'] = 0;
    }

    if ($GLOBALS['activate_privacy_policy_consent']) {
        $GLOBALS['activate_privacy_policy_text'] = 1;
    }

    $scheduleIndexing = false;
    // indexing was previously off, but now set to on, need to schedule re-indexing
    if (!get_config('enable_indexing') && $enable_indexing) {
        $scheduleIndexing = true;
        Database::get()->query("DELETE FROM idx_queue");
        Database::get()->queryFunc("SELECT id FROM course", function($r) {
            Database::get()->query("INSERT INTO idx_queue (course_id) VALUES (?d)", $r->id);
        });
    }

    // indexing was previously on, but now set to off, need to empty it
    if (get_config('enable_indexing') && !$enable_indexing) {
        require_once 'modules/search/indexer.class.php';
        Indexer::deleteAll();
    }

    // update table `config`
    foreach ($config_vars as $varname => $what) {
        set_config($varname, $GLOBALS[$varname]);
    }

    if ($privacyPolicyChanged and $activate_privacy_policy_consent) {
        Session::Messages(trans('langPrivacyPolicyConsentAskAgain') .
            "<form method='post' action='$_SERVER[SCRIPT_NAME]'>
                <button type='submit' class='btn btn-default' name='updatePrivacyPolicy' value='true'>" .
                    trans('langPrivacyPolicyConsentRedisplay') . "
                </button>
            </form>", 'alert-info');
    }

    // Display result message
    Session::flash('scheduleIndexing', $scheduleIndexing);
    Session::Messages($langFileUpdatedSuccess, 'alert-success');
    redirect_to_home_page('modules/admin/eclassconf.php');

} // end of if($submit)
else {
    // Display config.php edit form
    $head_content .= "
        <script>
        $(function() {
            $('body').scrollspy({ target: '#affixedSideNav' });
        });
        </script>
    ";
    $head_content .= "
        <script>
        $(function() {
            $('#floatMenu').affix({
              offset: {
                top: 230,
                bottom: function () {
                  return (this.bottom = $('.footer').outerHeight(true))
                }
              }
            })
        });
        </script>";
    // Display link to index.php
    $data['action_bar'] = action_bar([
                        [
                            'title' => $langBack,
                            'url' => "index.php",
                            'icon' => 'fa-reply',
                            'level' => 'primary-label'
                        ]
                    ]);
    $data['registration_info_textarea'] = rich_text_editor('registration_info', 4, 80, get_config('registration_info', ''));
    if (function_exists('imagettfbbox')) {
        $data['cbox_display_captcha'] = get_config('display_captcha') ? 'checked' : '';
        $data['message_display_captcha'] = $data['disable_display_captcha'] = '';
    } else {
        $data['cbox_display_captcha'] = '';
        $data['disable_display_captcha'] = 'disabled';
        $data['message_display_captcha'] = '<div>' . $lang_display_captcha_unsupported . '</div>';
    }
    $active_ui_languages = explode(' ', get_config('active_ui_languages'));
    $langdirs = active_subdirs($webDir . '/lang', 'messages.inc.php');
    $data['sel'] = [];
    $data['selectable_langs'] = [];
    $data['cbox_dont_display_login_form'] = get_config('dont_display_login_form') ? 'checked' : '';
    $data['cbox_hide_login_link'] = get_config('hide_login_link') ? 'checked' : '';
    foreach ($language_codes as $langcode => $langname) {
        if (in_array($langcode, $langdirs)) {
            $loclangname = $langNameOfLang[$langname];
            if (in_array($langcode, $active_ui_languages)) {
                $data['selectable_langs'][$langcode] = $loclangname;
            }
            $checked = in_array($langcode, $active_ui_languages) ? ' checked' : '';
            $data['sel'][] = "
                        <div class='checkbox'>
                            <label>
                                <input type='checkbox' name='av_lang[]' value='$langcode'$checked>
                                $loclangname
                            </label>
                        </div>";

        }
    }

    $data['cbox_course_multidep'] = get_config('course_multidep') ? 'checked' : '';
    $data['cbox_user_multidep']  = get_config('user_multidep') ? 'checked' : '';
    $data['cbox_restrict_owndep']  = get_config('restrict_owndep') ? 'checked' : '';
    $data['cbox_restrict_teacher_owndep']  = get_config('restrict_teacher_owndep') ? 'checked' : '';
    $data['cbox_allow_teacher_clone_course']  = get_config('allow_teacher_clone_course') ? 'checked' : '';
    $data['town_dis']  = get_config('restrict_owndep') ? '' : 'disabled';
    $data['cbox_insert_xml_metadata']  = get_config('insert_xml_metadata') ? 'checked' : '';
    $data['cbox_course_metadata']  = get_config('course_metadata') ? 'checked' : '';
    $data['cbox_opencourses_enable']  = get_config('opencourses_enable') ? 'checked' : '';
    $data['cbox_eportfolio_enable'] = get_config('eportfolio_enable') ? 'checked' : '';
    $data['cbox_case_insensitive_usernames'] = get_config('case_insensitive_usernames') ? 'checked' : '';
    $data['cbox_email_required'] = get_config('email_required') ? 'checked' : '';
    $data['cbox_email_verification_required'] = get_config('email_verification_required') ? 'checked' : '';
    $data['cbox_am_required'] = get_config('am_required') ? 'checked' : '';
    $data['cbox_dropbox_allow_student_to_student'] = get_config('dropbox_allow_student_to_student') ? 'checked' : '';
    $data['cbox_dropbox_allow_personal_messages'] = get_config('dropbox_allow_personal_messages') ? 'checked' : '';
    $data['cbox_personal_blog'] = get_config('personal_blog') ? 'checked' : '';
    $data['cbox_personal_blog_public'] = get_config('personal_blog_public') ? 'checked' : '';
    $data['cbox_personal_blog_commenting'] = get_config('personal_blog_commenting') ? 'checked' : '';
    $data['cbox_personal_blog_rating'] = get_config('personal_blog_rating') ? 'checked' : '';
    $data['cbox_personal_blog_sharing'] = get_config('personal_blog_sharing') ? 'checked' : '';
    $data['cbox_block_username_change'] = get_config('block_username_change') ? 'checked' : '';
    $data['cbox_enable_mobileapi'] = get_config('enable_mobileapi') ? 'checked' : '';
    $data['max_glossary_terms'] = get_config('max_glossary_terms');
    $data['cbox_enable_indexing'] = get_config('enable_indexing') ? 'checked' : '';
    $data['cbox_enable_search'] = get_config('enable_search') ? 'checked' : '';
    $data['cbox_enable_common_docs'] = get_config('enable_common_docs') ? 'checked' : '';
    $data['cbox_enable_docs_public_write'] = get_config('enable_docs_public_write') ? 'checked' : '';
    $data['cbox_mydocs_student_enable'] = get_config('mydocs_student_enable') ? 'checked' : '';
    $data['cbox_mydocs_teacher_enable'] = get_config('mydocs_teacher_enable') ? 'checked' : '';
    $data['mydocs_student_quota'] = floatval(get_config('mydocs_student_quota'));
    $data['mydocs_teacher_quota'] = floatval(get_config('mydocs_teacher_quota'));
    $data['cbox_enable_social_sharing_links'] = get_config('enable_social_sharing_links') ? 'checked' : '';
    $data['cbox_enable_strong_passwords'] = get_config('enable_strong_passwords') ? 'checked' : '';
    $data['cbox_login_fail_check'] = get_config('login_fail_check') ? 'checked' : '';
    $data['id_enable_mobileapi'] = (check_auth_active(7) || check_auth_active(6)) ? "id='mobileapi_enable'" : '';
    $data['cbox_block_duration_account'] = get_config('block_duration_account') ? 'checked' : '';
    $data['cbox_block_duration_alt_account'] = get_config('block_duration_alt_account') ? 'checked' : '';
    $data['cbox_disable_log_actions'] = get_config('disable_log_actions') ? 'checked' : '';
    $data['cbox_disable_log_course_actions'] = get_config('disable_log_course_actions') ? 'checked' : '';
    $data['cbox_disable_log_system_actions'] = get_config('disable_log_system_actions') ? 'checked' : '';
    $data['cbox_offline_course'] = get_config('offline_course') ? 'checked' : '';

    foreach ($session->active_ui_languages as $langCode) {
        $policy = get_config('privacy_policy_text_' . $langCode);
        if (!$policy) {
            $policyFile = "lang/$langCode/privacy.html";
            if (file_exists($policyFile)) {
                $policy = file_get_contents($policyFile);
            } else {
                $policy = get_config('privacy_policy_text_en');
                if (!$policy) {
                    $policy = file_get_contents('lang/en/privacy.html');
                }
            }
        }
        $data['policyText'][$langCode] = $policy;
    }
}

$data['menuTypeID'] = 3;
view('admin.other.eclassconf', $data);
