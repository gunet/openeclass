<?php
/*
 *  ========================================================================
 *  * Open eClass
 *  * E-learning and Course Management System
 *  * ========================================================================
 *  * Copyright 2003-2024, Greek Universities Network - GUnet
 *  *
 *  * Open eClass is an open platform distributed in the hope that it will
 *  * be useful (without any warranty), under the terms of the GNU (General
 *  * Public License) as published by the Free Software Foundation.
 *  * The full license can be read in "/info/license/license_gpl.txt".
 *  *
 *  * Contact address: GUnet Asynchronous eLearning Group
 *  *                  e-mail: info@openeclass.org
 *  * ========================================================================
 *
 */


$require_admin = true;
$require_help = true;
$helpTopic = 'system_settings';
$helpSubTopic = 'general_settings';

require_once '../../include/baseTheme.php';
require_once 'modules/auth/auth.inc.php';
require_once 'modules/admin/modalconfirmation.php';
require_once 'include/mailconfig.php';

$toolName = $langAdmin;
$pageName = $langEclassConf;
$navigation[] = array('url' => 'index.php', 'name' => $langAdmin);

const MONTHS = 30 * 24 * 60 * 60;

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

    if (isset($_POST['maintenance_theme'])) {
        set_config('maintenance_theme', $_POST['maintenance_theme']);
    }
    set_config('active_ui_languages', implode(' ', $active_lang_codes));
    set_config('base_url', $_POST['formurlServer']);
    set_config('phpMyAdminURL', $_POST['formphpMyAdminURL']);
    set_config('phpSysInfoURL', $_POST['formphpSysInfoURL']);
    set_config('email_sender', $_POST['formemailAdministrator']);
    set_config('admin_name', $_POST['formadministratorName']);
    set_config('site_name', $_POST['formsiteName']);
    set_config('institution', $_POST['formInstitution']);
    set_config('institution_url', $_POST['formInstitutionUrl']);
    set_config('account_duration', MONTHS * $_POST['formdurationAccount']);
    set_config('min_password_len', intval($_POST['min_password_len']));
    set_config('student_upload_whitelist', $_POST['student_upload_whitelist']);
    set_config('teacher_upload_whitelist', $_POST['teacher_upload_whitelist']);
    set_config('show_modal_openCourses', $_POST['show_modal_openCourses']);
    set_config('individual_group_bookings', $_POST['individual_group_bookings']);
    set_config('enable_quick_note', $_POST['enable_quick_note']);
    set_config('user_notifications', $_POST['user_notifications']);
    if ($_POST['user_notifications'] == 2) {
        set_config('user_notifications_interval', $_POST['user_notifications_interval']);
    } else {
        set_config('user_notifications_interval', '');
    }

    //Maintenance Text set
    foreach ($session->active_ui_languages as $langcode) {
        $langVar = 'maintenance_text_' . $langcode;
        if (isset($_POST[$langVar])) {
            $oldText = get_config($langVar);
            $newText = purify(trim($_POST[$langVar]));
            if ($oldText != $newText) {
                set_config($langVar, purify(trim($_POST[$langVar])));
            }
        }
    }

    $config_vars = [
        'email_required' => true,
        'email_verification_required' => true,
        'am_required' => true,
        'dropbox_allow_student_to_student' => true,
        'dropbox_allow_personal_messages' => true,
        'eportfolio_enable' => true,
        'personal_blog' => true,
        'personal_blog_commenting' => true,
        'personal_blog_rating' => true,
        'personal_blog_sharing' => true,
        'block_username_change' => true,
        'disable_name_surname_change' => true,
        'disable_email_change' => true,
        'disable_am_change' => true,
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
        'disable_cron_jobs' => true,
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
        'enable_prevent_download_url' => true,
        'enable_social_sharing_links' => true,
        'enable_strong_passwords' => true,
        'disable_student_unregister_cours' => true,
        'double_login_lock' => true,
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
        'activate_privacy_policy_consent' => true,
        'maintenance' => true,
        'allow_rec_video' => true,
        'allow_rec_audio' => true,
        'course_invitation' => true,
        'show_modal_openCourses' => true,
        'individual_group_bookings' => true,
        'enable_quick_note' => true,
        'user_notifications' => true,
        'default_course_access' => true,
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

    // restrict_owndep and restrict_teacher_owndep are independent
    if ($GLOBALS['restrict_owndep'] == 0) {
        $GLOBALS['restrict_teacher_owndep'] = 0;
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
        require_once 'modules/search/lucene/indexer.class.php';
        Indexer::deleteAll();
    }
    // update table `config`
    foreach ($config_vars as $varname => $what) {
        set_config($varname, $GLOBALS[$varname]);
    }

    // Display result message
    Session::flash('scheduleIndexing', $scheduleIndexing);
    Session::flash('message',$langFileUpdatedSuccess);
    Session::flash('alert-class', 'alert-success');
    redirect_to_home_page('modules/admin/eclassconf.php');

} // end of if($submit)
else {     // Display config.php edit form

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
    $data['maintenance_theme'] = $maintenance_theme = get_config('maintenance_theme');
    $data['sel'] = [];
    $data['selectable_langs'] = [];
    foreach ($language_codes as $langcode => $langname) {
        if (in_array($langcode, $langdirs)) {
            $loclangname = $langNameOfLang[$langname];
            if (in_array($langcode, $active_ui_languages)) {
                $data['selectable_langs'][$langcode] = $loclangname;
            }
            $checked = in_array($langcode, $active_ui_languages) ? ' checked' : '';
            $data['sel'][] = "
                        <div class='checkbox'>
                            <label class='label-container' aria-label='$langSelect'>
                                <input type='checkbox' name='av_lang[]' value='$langcode' $checked>
                                <span class='checkmark'></span>
                                $loclangname
                            </label>
                        </div>";
        }
    }


    $defaultHomepage = $toolboxHomepage = $externalHomepage = '';
    $homepageSet = get_config('homepage');
    if ($homepageSet == 'toolbox') {
        $toolboxHomepage = 'checked';
    } elseif ($homepageSet == 'external') {
        $externalHomepage = 'checked';
    } else {
        $defaultHomepage = 'checked';
    }
    $data['defaultHomepage'] = $defaultHomepage;

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
    $data['cbox_email_from'] = get_var('email_from') ? 'checked' : '';
    $data['cbox_dont_mail_unverified_mails'] = get_var('dont_mail_unverified_mails') ? 'checked' : '';
    $data['cbox_am_required'] = get_config('am_required') ? 'checked' : '';
    $data['cbox_dropbox_allow_student_to_student'] = get_config('dropbox_allow_student_to_student') ? 'checked' : '';
    $data['cbox_dropbox_allow_personal_messages'] = get_config('dropbox_allow_personal_messages') ? 'checked' : '';
    $data['cbox_personal_blog'] = get_config('personal_blog') ? 'checked' : '';
    $data['cbox_personal_blog_commenting'] = get_config('personal_blog_commenting') ? 'checked' : '';
    $data['cbox_personal_blog_rating'] = get_config('personal_blog_rating') ? 'checked' : '';
    $data['cbox_personal_blog_sharing'] = get_config('personal_blog_sharing') ? 'checked' : '';
    $data['cbox_block_username_change'] = get_config('block_username_change') ? 'checked' : '';
    $data['cbox_disable_name_surname_change'] = get_config('disable_name_surname_change') ? 'checked' : '';
    $data['cbox_disable_email_change'] = get_config('disable_email_change') ? 'checked' : '';
    $data['cbox_disable_am_change'] = get_config('disable_am_change') ? 'checked' : '';
    $data['cbox_enable_mobileapi'] = get_config('enable_mobileapi') ? 'checked' : '';
    $data['max_glossary_terms'] = get_config('max_glossary_terms');
    $data['cbox_enable_indexing'] = get_config('enable_indexing') ? 'checked' : '';
    $data['cbox_enable_search'] = get_config('enable_search') ? 'checked' : '';
    $data['cbox_enable_common_docs'] = get_config('enable_common_docs') ? 'checked' : '';
    $data['cbox_enable_docs_public_write'] = get_config('enable_docs_public_write') ? 'checked' : '';
    $data['cbox_enable_prevent_download_url'] = get_config('enable_prevent_download_url') ? 'checked' : '';
    $data['cbox_mydocs_student_enable'] = get_config('mydocs_student_enable') ? 'checked' : '';
    $data['cbox_mydocs_teacher_enable'] = get_config('mydocs_teacher_enable') ? 'checked' : '';
    $data['mydocs_student_quota'] = floatval(get_config('mydocs_student_quota'));
    $data['mydocs_teacher_quota'] = floatval(get_config('mydocs_teacher_quota'));
    $data['cbox_enable_social_sharing_links'] = get_config('enable_social_sharing_links') ? 'checked' : '';
    $data['cbox_enable_strong_passwords'] = get_config('enable_strong_passwords') ? 'checked' : '';
    $data['cbox_disable_student_unregister_cours'] = get_config('disable_student_unregister_cours') ? 'checked' : '';
    $data['cbox_double_login_lock'] = get_config('double_login_lock') ? 'checked' : '';
    $data['cbox_login_fail_check'] = get_config('login_fail_check') ? 'checked' : '';
    $data['id_enable_mobileapi'] = (check_auth_active(7) || check_auth_active(6)) ? "id='mobileapi_enable'" : '';
    $data['cbox_block_duration_account'] = get_config('block_duration_account') ? 'checked' : '';
    $data['cbox_block_duration_alt_account'] = get_config('block_duration_alt_account') ? 'checked' : '';
    $data['cbox_disable_cron_jobs'] = get_config('disable_cron_jobs') ? 'checked' : '';
    $data['cbox_disable_log_actions'] = get_config('disable_log_actions') ? 'checked' : '';
    $data['cbox_disable_log_course_actions'] = get_config('disable_log_course_actions') ? 'checked' : '';
    $data['cbox_disable_log_system_actions'] = get_config('disable_log_system_actions') ? 'checked' : '';
    $data['cbox_offline_course'] = get_config('offline_course') ? 'checked' : '';
    $data['cbox_maintenance'] = get_config('maintenance') ? 'checked' : '';
    $data['cbox_allow_rec_video'] = get_config('allow_rec_video') ? 'checked' : '';
    $data['cbox_allow_rec_audio'] = get_config('allow_rec_audio') ? 'checked' : '';
    $data['cbox_allow_modal_courses'] = get_config('show_modal_openCourses') ? 'checked' : '';
    $data['cbox_course_invitation'] = get_config('course_invitation') ? 'checked' : '';
    $data['cbox_individual_group_bookings'] = get_config('individual_group_bookings') ? 'checked' : '';
    $data['cbox_enable_quick_note'] = get_config('enable_quick_note') ? 'checked' : '';
    $data['default_course_access'] = intval(get_config('default_course_access', COURSE_REGISTRATION));

    $user_notifications_interval = get_config('user_notifications_interval');
    $user_notifications = get_config('user_notifications');
    if ($user_notifications == 0) {
        $data['user_notifications0'] = 'checked';
        $data['user_notifications1'] = '';
        $data['user_notifications2'] = '';
    } else if ($user_notifications == 1) {
        $data['user_notifications0'] = '';
        $data['user_notifications1'] = 'checked';
        $data['user_notifications2'] = '';
    } else if ($user_notifications == 2) {
        $data['user_notifications0'] = '';
        $data['user_notifications1'] = '';
        $data['user_notifications2'] = 'checked';
    }

    $data['user_notifications_interval'] = selection([
                                                    '5' => $langLast5Days,
                                                    '10' => $langLast10Days,
                                                    '15' => $langLast15Days,
                                                    '30' => $langLast30Days
                                                    ], 'user_notifications_interval', $user_notifications_interval, 'id="user_notification_id" class="form-control"');

    $data['emailTransports'] = array(1 => 'SMTP', 2 => 'sendmail');
    $email_transport = 1;
    if (get_var('email_transport') == 'sendmail') {
        $email_transport = 2;
    }
    $data['email_transport'] = $email_transport;

    $data['emailEncryption'] = array(0 => "$langNo", 1 => 'SSL', 2 => 'TLS');
    $smtp_encryption = get_var('smtp_encryption');
    if ($smtp_encryption == 'ssl') {
        $smtp_encryption = 1;
    } elseif ($smtp_encryption == 'tls') {
        $smtp_encryption = 2;
    } else {
        $smtp_encryption = 0;
    }
    $data['smtp_encryption'] = $smtp_encryption;

}

view('admin.other.eclassconf', $data);


function checkMaintenanceTheme($maintenance_theme, $number) {
    if ($maintenance_theme == $number) {
        return "checked";
    }
}
