<?php
/* ========================================================================
 * Open eClass 3.6
 * E-learning and Course Management System
 * ========================================================================
 * Copyright 2003-2018  Greek Universities Network - GUnet
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

function checkMaintenanceTheme($maintenance_theme, $number) {
    if ($maintenance_theme == $number) {
        return "checked";
    }
}

$head_content .= <<<EOF
<script type='text/javascript'>
/* <![CDATA[ */

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

$(document).ready(function() {
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

    $("#privacyPolicyConsent").click(function() {
        if ($(this).is(':checked')) {
            $('#privacyPolicyLink')
                .prop('checked', true)
                .prop('disabled', true);
        } else {
            $('#privacyPolicyLink')
                .prop('disabled', false);
        }
    });
    $('#privacyPolicyLink')
        .prop('disabled', $("#privacyPolicyConsent").is(':checked'));

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
            $('#personal_blog_commenting_enable').prop('disabled', false);
            $('#personal_blog_rating_enable').prop('disabled', false);
            if ($('#social_sharing_links').is(":checked")) {
                $('#personal_blog_sharing_enable').prop('disabled', false);
            }
        } else {
            $('#personal_blog_commenting_enable').prop('disabled', true);
            $('#personal_blog_rating_enable').prop('disabled', true);
            $('#personal_blog_sharing_enable').prop('disabled', true);
        }
    });

    if (!$('#personal_blog_enable').is(":checked")) {
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

    $mail_form_js
});

/* ]]> */
</script>
EOF;

define('MONTHS', 30 * 24 * 60 * 60);

// schedule indexing if necessary
if (Session::get('scheduleIndexing')) {
    $tool_content .= "<div class='alert alert-warning'>{$langIndexingNeeded} <a id='idxpbut' href='../search/idxpopup.php' onclick=\"return idxpopup('../search/idxpopup.php', 600, 500)\">{$langHere}.</a></div>";

    $head_content .= <<<EOF
<script type='text/javascript'>
/* <![CDATA[ */

var idxwindow = null;

function idxpopup(url, w, h) {
    var left = (screen.width/2)-(w/2);
    var top = (screen.height/2)-(h/2);

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

$(document).ready(function() {

    $('#idxpbut').click();

});

/* ]]> */
</script>
EOF;
}

$registration_link_options = array('show' => $langViewShow, 'hide' => $langViewHide, 'show_text' => $langRegistrationShowText);

// Save new `config` table
if (isset($_POST['submit'])) {
    if (!isset($_POST['token']) || !validate_csrf_token($_POST['token'])) csrf_token_error();
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

    set_config('maintenance_theme', $_POST['maintenance_theme']);

    set_config('active_ui_languages', implode(' ', $active_lang_codes));
    set_config('base_url', $_POST['formurlServer']);
    set_config('phpMyAdminURL', $_POST['formphpMyAdminURL']);
    set_config('phpSysInfoURL', $_POST['formphpSysInfoURL']);
    if (valid_email($_POST['formemailAdministrator'])) {
        set_config('email_sender', $_POST['formemailAdministrator']);
    } else {
        Session::Messages("$langAdminEmail: $langInvalidEmail: " . q($_POST['formemailAdministrator']),
            'alert-warning');
    }
    set_config('admin_name', $_POST['formadministratorName']);
    set_config('site_name', $_POST['formsiteName']);
    set_config('phone', $_POST['formtelephone']);
    if (valid_email($_POST['formemailhelpdesk'])) {
        set_config('email_helpdesk', $_POST['formemailhelpdesk']);
    } else {
        Session::Messages("$langHelpDeskEmail: $langInvalidEmail: " . q($_POST['formemailhelpdesk']),
            'alert-warning');
    }
    set_config('institution', $_POST['formInstitution']);
    set_config('institution_url', $_POST['formInstitutionUrl']);
    set_config('postaddress', $_POST['formpostaddress']);
    set_config('fax', $_POST['formfax']);
    set_config('account_duration', MONTHS * $_POST['formdurationAccount']);
    set_config('min_password_len', intval($_POST['min_password_len']));
    set_config('student_upload_whitelist', $_POST['student_upload_whitelist']);
    set_config('teacher_upload_whitelist', $_POST['teacher_upload_whitelist']);

    $privacyPolicyChanged = false;
    foreach ($session->active_ui_languages as $langcode) {
        $langVar = 'privacy_policy_text_' . $langcode;
        if (isset($_POST[$langVar])) {
            $oldText = get_config($langVar);
            $newText = purify(trim($_POST[$langVar]));
            if ($oldText != $newText) {
                set_config($langVar, purify(trim($_POST[$langVar])));
                $privacyPolicyChanged = true;
            }
        }
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

    $config_vars = array('email_required' => true,
        'email_verification_required' => true,
        'email_prevent_autoset_change' => true,
        'am_prevent_autoset_change' => true,
        'am_required' => true,
        'dont_display_login_form' => true,
        'hide_login_link' => true,
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
        'activate_privacy_policy_text' => true,
        'activate_privacy_policy_consent' => true,
        'maintenance' => true,
        'dont_display_courses_menu' => true,
        'dont_display_about_menu' => true,
        'dont_display_contact_menu' => true,
        'dont_display_manual_menu' => true,
        'course_invitation' => true,
        'allow_rec_video' => true,
        'allow_rec_audio' => true
        );

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
        Session::Messages($langPrivacyPolicyConsentAskAgain .
            "<form method='post' action='$_SERVER[SCRIPT_NAME]'>
                <button type='submit' class='btn btn-default' name='updatePrivacyPolicy' value='true'>
                    $langPrivacyPolicyConsentRedisplay
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
    // Display link to index.php
    $tool_content .= action_bar(array(
        array('title' => $langBack,
            'url' => "index.php",
            'icon' => 'fa-reply',
            'level' => 'primary-label')));

    if (function_exists('imagettfbbox')) {
        $cbox_display_captcha = get_config('display_captcha') ? 'checked' : '';
        $message_display_captcha = $disable_display_captcha = '';
    } else {
        $cbox_display_captcha = '';
        $disable_display_captcha = 'disabled';
        $message_display_captcha = '<div>' . $lang_display_captcha_unsupported . '</div>';
    }

    $cbox_block_duration_account = get_config('block_duration_account') ? 'checked' : '';
    $cbox_block_duration_alt_account = get_config('block_duration_alt_account') ? 'checked' : '';
    $cbox_course_invitation = get_config('course_invitation') ? 'checked' : '';

    $maintenance_theme = get_config('maintenance_theme');

    $tool_content .= "
<div class='row'>
    <div class='col-sm-9'>
        <form class='form-horizontal' role='form' action='$_SERVER[SCRIPT_NAME]' method='post'>
            <div class='panel panel-primary' id='one'>
                <div class='panel-heading'>
                    <h2 class='panel-title'>$langBasicCfgSetting</h2>
                </div>
                <div class='panel-body'>
                    <fieldset>
                        <div class='form-group'>
                           <label for='formurlServer' class='col-sm-2 col-sm-offset-1 control-label'>$langSiteUrl:</label>
                           <div class='col-sm-9'>
                                <input class='form-control' type='text' name='formurlServer' id='formurlServer' value='" . q($urlServer) . "'>
                           </div>
                        </div>
                        <div class='form-group'>
                           <label for='formphpMyAdminURL' class='col-sm-2 col-sm-offset-1 control-label'>$langphpMyAdminURL</label>
                           <div class='col-sm-9'>
                                <input class='form-control' type='text' name='formphpMyAdminURL' id='formphpMyAdminURL' value='" . q(get_config('phpMyAdminURL')) . "'>
                           </div>
                        </div>
                        <div class='form-group'>
                           <label for='formphpSysInfoURL' class='col-sm-2 col-sm-offset-1 control-label'>$langSystemInfoURL:</label>
                           <div class='col-sm-9'>
                               <input class='form-control' type='text' name='formphpSysInfoURL' id='formphpSysInfoURL' value='" . q(get_config('phpSysInfoURL')) . "'>
                           </div>
                        </div>
                        <div class='form-group'>
                           <label for='formemailAdministrator' class='col-sm-2 col-sm-offset-1 control-label'>$langAdminEmail:</label>
                           <div class='col-sm-9'>
                               <input class='form-control' type='text' name='formemailAdministrator' id='formemailAdministrator' value='" . q(get_config('email_sender')) . "'>
                           </div>
                        </div>
                        <div class='form-group'>
                           <label for='formadministratorName' class='col-sm-2 col-sm-offset-1 control-label'>$langDefaultAdminName:</label>
                           <div class='col-sm-9'>
                               <input class='form-control' type='text' name='formadministratorName' id='formadministratorName' value='" . q(get_config('admin_name')) . "'>
                           </div>
                        </div>
                        <div class='form-group'>
                           <label for='formsiteName' class='col-sm-2 col-sm-offset-1 control-label'>$langCampusName:</label>
                           <div class='col-sm-9'>
                               <input class='form-control' type='text' name='formsiteName' id='formsiteName' value='" . q(get_config('site_name')) . "'>
                           </div>
                        </div>
                        <div class='form-group'>
                           <label for='formpostaddress' class='col-sm-2 col-sm-offset-1 control-label'>$langPostMail</label>
                           <div class='col-sm-9'>
                               <textarea class='form-control' name='formpostaddress' id='formpostaddress'>" . q(get_config('postaddress')) . "</textarea>
                           </div>
                        </div>
                        <div class='form-group'>
                           <label for='formtelephone' class='col-sm-2 col-sm-offset-1 control-label'>$langPhone:</label>
                           <div class='col-sm-9'>
                               <input class='form-control' type='text' name='formtelephone' id='formtelephone' value='" . q(get_config('phone')) . "'>
                           </div>
                        </div>
                        <div class='form-group'>
                           <label for='formfax' class='col-sm-2 col-sm-offset-1 control-label'>$langFax</label>
                           <div class='col-sm-9'>
                               <input class='form-control' type='text' name='formfax' id='formfax' value='" . q(get_config('fax')) . "'>
                           </div>
                        </div>
                        <div class='form-group'>
                           <label for='formemailhelpdesk' class='col-sm-2 col-sm-offset-1 control-label'>$langHelpDeskEmail:</label>
                           <div class='col-sm-9'>
                               <input class='form-control' type='text' name='formemailhelpdesk' id='formemailhelpdesk' value='" . q(get_config('email_helpdesk')) . "'>
                           </div>
                        </div>
                        <div class='form-group'>
                           <label for='formInstitution' class='col-sm-2 col-sm-offset-1 control-label'>$langInstituteShortName:</label>
                           <div class='col-sm-9'>
                               <input class='form-control' type='text' name='formInstitution' id='formInstitution' value='" . $Institution . "'>
                           </div>
                        </div>
                        <div class='form-group'>
                           <label for='formInstitutionUrl' class='col-sm-2 col-sm-offset-1 control-label'>$langInstituteName:</label>
                           <div class='col-sm-9'>
                               <input class='form-control' type='text' name='formInstitutionUrl' id='formInstitutionUrl' value='" . $InstitutionUrl . "'>
                           </div>
                        </div>
                        <hr>
                        <div class='form-group'>
                            <div class='col-sm-12'>
                                <input class='btn btn-default' type='submit' name='submit' value='$langSave'>
                            </div>
                        </div>
                    </fieldset>
                </div>
            </div>
            <div class='panel panel-primary' id='two'>
                <div class='panel-heading'>
                    <h2 class='panel-title'>$langUpgReg</h2>
                </div>
                <div class='panel-body'>
                    <fieldset>
                        <div class='form-group'>
                           <label for='user_registration' class='col-sm-3 control-label'>$langUserRegistration:</label>
                           <div class='col-sm-9'>
                                " . selection(array('1' => $langActivate, '0' => $langDeactivate), 'user_registration', get_config('user_registration'), "class='form-control' id='user_registration'") . "
                           </div>
                        </div>
                        <div class='form-group'>
                           <label for='registration_link' class='col-sm-3 control-label'>$langRegistrationLink:</label>
                           <div class='col-sm-9'>
                                ". selection($registration_link_options, 'registration_link', get_config('registration_link', 'show'), "class='form-control' id='registration_link'"). "
                           </div>
                        </div>
                        <div class='form-group' id='registration-info-block'>
                           <label for='registration_info' class='col-sm-3 control-label'>$langRegistrationInfo:</label>
                           <div class='col-sm-9'>
                               " . rich_text_editor('registration_info', 4, 80, get_config('registration_info', '')) . "
                           </div>
                        </div>
                        <div class='form-group'>
                           <label for='eclass_stud_reg' class='col-sm-3 control-label'>$langUserAccount $langViaeClass:</label>
                           <div class='col-sm-9'>
                                ". selection(array('0' => $langDisableEclassStudReg, '1' => $langReqRegUser, '2' => $langDisableEclassStudRegType), 'eclass_stud_reg', get_config('eclass_stud_reg'), "class='form-control' id='eclass_stud_reg'") ."
                           </div>
                        </div>
                        <div class='form-group'>
                           <label for='alt_auth_stud_reg' class='col-sm-3 control-label'>$langUserAccount $langViaAltAuthMethods:</label>
                           <div class='col-sm-9'>
                                ". selection(array('0' => $langDisableEclassStudReg, '1' => $langReqRegUser, '2' => $langDisableEclassStudRegType), 'alt_auth_stud_reg', get_config('alt_auth_stud_reg'), "class='form-control' id='alt_auth_stud_reg'") ."
                           </div>
                        </div>
                        <div class='form-group'>
                           <label for='eclass_prof_reg' class='col-sm-3 control-label'>$langProfAccount $langViaeClass:</label>
                           <div class='col-sm-9'>
                                ". selection(
                                        array(
                                            '0' => $langDisableEclassProfReg,
                                            '1' => $langReqRegProf
                                        ),
                                        'eclass_prof_reg',
                                        get_config('eclass_prof_reg'),
                                        "class='form-control' id='eclass_prof_reg'"
                                    ) ."
                           </div>
                        </div>
                        <div class='form-group'>
                           <label for='alt_auth_prof_reg' class='col-sm-3 control-label'>$langProfAccount $langViaAltAuthMethods:</label>
                           <div class='col-sm-9'>
                                ". selection(
                                        array(
                                                '0' => $langDisableEclassProfReg,
                                                '1' => $langReqRegProf
                                            ),
                                        'alt_auth_prof_reg',
                                        get_config('alt_auth_prof_reg'),
                                        "class='form-control' id='alt_auth_prof_reg'"
                                    ) ."
                           </div>
                        </div>
                        <div class='form-group'>
                            <label class='col-sm-3 control-label'>$lang_block_duration_account:</label>
                            <div class='checkbox col-sm-9'>
                            <label>
                                <input type='checkbox' name='block_duration_account' value='1' $cbox_block_duration_account>
                                $lang_message_block_duration_account
                            </label>
                            </div>
                        </div>
                        <div class='form-group'>
                            <label class='col-sm-3 control-label'>$lang_block_duration_alt_account:</label>
                            <div class='checkbox col-sm-9'>
                            <label>
                                <input type='checkbox' name='block_duration_alt_account' value='1' $cbox_block_duration_alt_account>
                                $lang_message_block_duration_account
                            </label>
                            </div>
                        </div>
                        <div class='form-group'>
                           <label for='formdurationAccount' class='col-sm-3 control-label'>$langUserDurationAccount&nbsp;($langMonthsUnit): </label>
                           <div class='col-sm-9'>
                                <input type='text' class='form-control' name='formdurationAccount' id='formdurationAccount' maxlength='3' value='" . intval(get_config('account_duration') / MONTHS) . "'>
                           </div>
                        </div>
                        <div class='form-group'>
                            <label class='col-sm-3 control-label'>$lang_display_captcha_label:</label>
                            <div class='checkbox col-sm-9'>
                                <label>
                                    <input type='checkbox' name='display_captcha' value='1' $cbox_display_captcha $disable_display_captcha>
                                    $lang_display_captcha
                                </label>$message_display_captcha
                            </div>
                        </div>
                        <div class='form-group'>
                            <label class='col-sm-3 control-label'>$langGuestLoginLabel:</label>
                            <div class='col-sm-9'>" .
                                selection(array('off' => $langDeactivate,
                                                'on' => $langActivate,
                                                'link' => $langGuestLoginLinks),
                                    'course_guest',
                                    get_config('course_guest', 'on'),
                                    "class='form-control'") . "
                            </div>
                        </div>
                        <div class='form-group'>
                            <label class='col-sm-3 control-label'>$langcourseExternalUsersInviation:</label>
                            <div class='checkbox col-sm-9'>
                                <label>
                                    <input type='checkbox' name='course_invitation' value='1' $cbox_course_invitation>
                                    $langCourseInvitationHelp
                                </label>
                            </div>
                        </div>
                        <hr>
                        <div class='form-group'>
                            <div class='col-sm-12'>
                                <input class='btn btn-default' type='submit' name='submit' value='$langSave'>
                            </div>
                        </div>
                    </fieldset>
                </div>
            </div>";
        $active_ui_languages = explode(' ', get_config('active_ui_languages'));
        $langdirs = active_subdirs($webDir . '/lang', 'messages.inc.php');
        $sel = array();
        $selectable_langs = array();
        $cbox_dont_display_login_form = get_config('dont_display_login_form') ? 'checked' : '';
        $cbox_dont_display_courses_menu = get_config('dont_display_courses_menu') ? 'checked' : '';
        $cbox_dont_display_about_menu = get_config('dont_display_about_menu') ? 'checked' : '';
        $cbox_dont_display_manual_menu = get_config('dont_display_manual_menu') ? 'checked' : '';
        $cbox_dont_display_contact_menu = get_config('dont_display_contact_menu') ? 'checked' : '';
        $cbox_hide_login_link = get_config('hide_login_link') ? 'checked' : '';
        foreach ($language_codes as $langcode => $langname) {
            if (in_array($langcode, $langdirs)) {
                $loclangname = $langNameOfLang[$langname];
                if (in_array($langcode, $active_ui_languages)) {
                    $selectable_langs[$langcode] = $loclangname;
                }
                $checked = in_array($langcode, $active_ui_languages) ? ' checked' : '';
                $sel[] = "<div class='checkbox'>
                            <label>
                                <input type='checkbox' name='av_lang[]' value='$langcode'$checked>
                                $loclangname
                            </label>
                        </div>";

            }
        }
        $tool_content .= "
            <div class='panel panel-primary' id='three'>
                <div class='panel-heading'>
                    <h2 class='panel-title'>$langChooseLang</h2>
                </div>
                <div class='panel-body'>
                    <fieldset>
                        <div class='form-group'>
                           <label for='default_language' class='col-sm-3 control-label'>$langMainLang: </label>
                           <div class='col-sm-9'>" .
                               selection($selectable_langs, 'default_language', get_config('default_language'),
                                         "class='form-control' id='default_language'") .
                           "</div>
                        </div>
                        <div class='form-group'>
                            <label class='col-sm-3 control-label'>$langSupportedLanguages:</label>
                            <div class='col-sm-9'>
                            " . implode(' ', $sel) . "
                            </div>
                        </div>

                        <hr>
                        <div class='form-group'>
                            <div class='col-sm-12'>
                                <input class='btn btn-default' type='submit' name='submit' value='$langSave'>
                            </div>
                        </div>
                    </fieldset>
                </div>
            </div>";

    $defaultHomepage = $toolboxHomepage = $externalHomepage = '';
    $homepageSet = get_config('homepage');
    if ($homepageSet == 'toolbox') {
        $toolboxHomepage = 'checked';
    } elseif ($homepageSet == 'external') {
        $externalHomepage = 'checked';
    } else {
        $defaultHomepage = 'checked';
    }

    $tool_content .= "
            <div class='panel panel-primary' id='four'>
                <div class='panel-heading'>
                    <h2 class='panel-title'>$langHomePageSettings</h2>
                </div>
                <div class='panel-body'>
                    <div class='margin-bottom-fat margin-top-fat'><strong>$langSelectHomePage :</strong></div>
                    <fieldset>
                        <div class='landing-default'>
                            <div class='radio margin-bottom-fat'>
                                <label>
                                    <input $defaultHomepage class='homepageSet' name='homepageSet' value='default' data-collapse='collapse-defaultHomepage' type='radio'> $langHomePageDefault
                                </label>
                            </div>
                            <div id='collapse-defaultHomepage' class='collapse homepage-inputs margin-bottom-fat'>
                            <hr class='margin-bottom-fat'>
                            <div class='form-group'>
                                <label for='defaultHomepageTitle' class='col-sm-2 col-sm-offset-1 control-label'>$langHomePageIntroTitle</label>
                                <div class='col-sm-9'>
                                    <input class='form-control' type='text' name='homepage_title' id='defaultHomepageTitle' value='".q(get_config('homepage_title', $langEclass))."'>
                                    <p class='help-block'>$langHomePageTitleHelpText</p>
                                </div>
                            </div>
                            <div class='form-group'>
                                <label for='defaultHomepageBcrmp' class='col-sm-2 col-sm-offset-1 control-label'>$langHomePageIntroBcrmp</label>
                                <div class='col-sm-9'>
                                    <input class='form-control' type='text' name='homepage_name' id='defaultHomepageBcrmp' value='".q(get_config('homepage_name', $langHomePage))."'>
                                    <p class='help-block'>$langHomePageNavTitleHelp</p>
                                </div>
                            </div>
                            <div class='form-group'>
                                <label for='defaultHomepageIntro' class='col-sm-2 col-sm-offset-1 control-label'>$langHomePageIntroText:</label>
                                <div class='col-sm-9'>
                                    ".rich_text_editor('homepage_intro', 5, 20, get_config('homepage_intro', $langInfoAbout))."
                                    <p class='help-block'>$langHomePageIntroTextHelp</p>
                                </div>
                            </div>
                            <div class='form-group'>
                           <label for='theme' class='col-sm-2 col-sm-offset-1 control-label'>$lang_login_form: </label>
                           <div class='col-sm-9'>
                                <div class='checkbox'>
                                    <label>
                                        <input id='hide_login_check' type='checkbox' name='dont_display_login_form' value='1' $cbox_dont_display_login_form>
                                        $lang_dont_display_login_form
                                    </label>
                                </div>
                                <div class='checkbox'>
                                    <label>
                                        <input id='hide_login_link_check' type='checkbox' name='hide_login_link' value='1' $cbox_hide_login_link>
                                        $lang_hide_login_link
                                    </label>
                                </div>
                           </div>

                           <label for='theme' class='col-sm-2 col-sm-offset-1 control-label'>$langHomePageMenuSettings: </label>
                           <div class='col-sm-9'>
                                <div class='checkbox'>
                                    <label>
                                        <input type='checkbox' name='dont_display_courses_menu' value='1' $cbox_dont_display_courses_menu>
                                        $lang_dont_display_courses_menu
                                    </label>
                                </div>
                                <div class='checkbox'>
                                    <label>
                                        <input type='checkbox' name='dont_display_contact_menu' value='1' $cbox_dont_display_contact_menu>
                                        $lang_dont_display_contact_menu
                                    </label>
                                </div>
                                <div class='checkbox'>
                                    <label>
                                        <input type='checkbox' name='dont_display_about_menu' value='1' $cbox_dont_display_about_menu>
                                        $lang_dont_display_about_menu
                                    </label>
                                </div>
                                <div class='checkbox'>
                                    <label>
                                        <input type='checkbox' name='dont_display_manual_menu' value='1' $cbox_dont_display_manual_menu>
                                        $lang_dont_display_manual_menu
                                    </label>
                                </div>
                           </div>

                        </div>
                            </div>
                        </div>
                        <div class='landing-toolbox'>
                            <div class='radio margin-bottom-fat'>
                                <label>
                                    <input $toolboxHomepage class='homepageSet' name='homepageSet' value='toolbox' data-collapse='collapse-toolboxHomepage' type='radio'> $langHomePageToolbox
                                </label>
                            </div>
                            <div id='collapse-toolboxHomepage' class='collapse homepage-inputs margin-bottom-fat'>
                            <hr class='margin-bottom-fat'>
                                <div class='form-group'>
                                    <label for='toolboxHomepageTitle' class='col-sm-2 col-sm-offset-1 control-label'>$langHomePageIntroTitle</label>
                                    <div class='col-sm-9'>
                                        <input class='form-control' type='text' name='toolbox_title' id='toolboxHomepageTitle' value='".q(get_config('toolbox_title', $langEclass))."'>
                                        <p class='help-block'>$langHomePageTitleHelpText</p>
                                    </div>
                                </div>
                                <div class='form-group'>
                                <label for='toolboxHomepageBcrmp' class='col-sm-2 col-sm-offset-1 control-label'>$langHomePageIntroBcrmp</label>
                                <div class='col-sm-9'>
                                    <input class='form-control' type='text' name='toolbox_name' id='toolboxHomepageBcrmp' value='".q(get_config('toolbox_name', $langHomePage))."'>
                                    <p class='help-block'>$langHomePageNavTitleHelp</p>
                                </div>
                                </div>
                                <div class='form-group'>
                                    <label for='toolboxHomepageIntro' class='col-sm-2 col-sm-offset-1 control-label'>$langHomePageIntroText:</label>
                                    <div class='col-sm-9'>
                                        ".rich_text_editor('toolbox_intro', 5, 20, get_config('toolbox_intro', $langInfoAbout))."
                                        <p class='help-block'>$langHomePageIntroTextHelp</p>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class='landing-external'>
                            <div class='radio margin-bottom-fat'>
                                <label>
                                    <input $externalHomepage class='homepageSet' type='radio' name='homepageSet' value='external' data-collapse='collapse-externalHomepage'> $langHomePageExternal
                                </label>
                            </div>
                            <div id='collapse-externalHomepage' class='collapse homepage-inputs margin-bottom-fat'>
                            <hr class='margin-bottom-fat'>
                                <div class='form-group'>
                                    <label for='externalHomepageBcrmp' class='col-sm-2 col-sm-offset-1 control-label'>$langHomePageIntroBcrmp:</label>
                                    <div class='col-sm-9'>
                                        <input class='form-control' type='text' name='landing_name' id='externalHomepageBcrmp' value='".q(get_config('landing_name'))."'>
                                        <p class='help-block'>$langHomePageNavTitleHelp</p>
                                    </div>
                                </div>
                                <div class='form-group'>
                                    <label for='externalHomepageUrl' class='col-sm-2 col-sm-offset-1 control-label'>$langHomePageIntroUrl:</label>
                                    <div class='col-sm-9'>
                                        <input class='form-control' type='text' name='landing_url' id='externalHomepageUrl' value='".q(get_config('landing_url'))."'>
                                        <p class='help-block'>$langHomePageExtUrlHelp</p>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <hr>
                        <div class='form-group'>
                            <div class='col-sm-12'>
                                <input class='btn btn-default' type='submit' name='submit' value='$langSave'>
                            </div>
                        </div>
                    </fieldset>
                </div>
            </div>";

    mail_settings_form();

    $cbox_course_multidep = get_config('course_multidep') ? 'checked' : '';
    $cbox_user_multidep = get_config('user_multidep') ? 'checked' : '';
    $cbox_restrict_owndep = get_config('restrict_owndep') ? 'checked' : '';
    $cbox_restrict_teacher_owndep = get_config('restrict_teacher_owndep') ? 'checked' : '';
    $cbox_allow_teacher_clone_course = get_config('allow_teacher_clone_course') ? 'checked' : '';
    $town_dis = get_config('restrict_owndep') ? '' : 'disabled';
    $cbox_insert_xml_metadata = get_config('insert_xml_metadata') ? 'checked' : '';
    $cbox_course_metadata = get_config('course_metadata') ? 'checked' : '';
    $cbox_opencourses_enable = get_config('opencourses_enable') ? 'checked' : '';
    $cbox_offline_course = get_config('offline_course') ? 'checked' : '';
    $cbox_allow_rec_video = get_config('allow_rec_video') ? 'checked' : '';
    $cbox_allow_rec_audio = get_config('allow_rec_audio') ? 'checked' : '';

    $tool_content .= "
            <div class='panel panel-primary' id='six'>
                <div class='panel-heading'>
                    <h2 class='panel-title'>$langCourseSettings</h2>
                </div>
                <div class='panel-body'>
                    <fieldset>
                        <div class='form-group'>
                           <div class='col-sm-12'>
                                <div class='checkbox'>
                                    <label>
                                        <input type='checkbox' name='course_multidep' value='1' $cbox_course_multidep>
                                        $lang_course_multidep
                                    </label>
                                </div>
                                <div class='checkbox'>
                                    <label>
                                        <input type='checkbox' name='user_multidep' value='1' $cbox_user_multidep>
                                        $lang_user_multidep
                                    </label>
                                </div>
                                <div class='checkbox'>
                                    <label>
                                        <input id='uown' type='checkbox' name='restrict_owndep' value='1' $cbox_restrict_owndep>
                                        $lang_restrict_owndep
                                    </label>
                                </div>
                                <div class='checkbox'>
                                    <label>
                                        <input id='town' type='checkbox' name='restrict_teacher_owndep' value='1' $town_dis $cbox_restrict_teacher_owndep>
                                        $lang_restrict_teacher_owndep
                                    </label>
                                </div>
                                <div class='checkbox'>
                                    <label>
                                        <input type='checkbox' name='allow_teacher_clone_course' value='1' $cbox_allow_teacher_clone_course>
                                        $lang_allow_teacher_clone_course
                                    </label>
                                </div>
                                <div class='checkbox'>
                                    <label>
                                        <input type='checkbox' name='offline_course' value='1' $cbox_offline_course>
                                        $langCourseOfflineSettings
                                    </label>
                                </div>
                                <div class='checkbox'>
                                    <label>
                                        <input type='checkbox' name='allow_rec_video' value='1' $cbox_allow_rec_video>
                                        $lang_allow_rec_video
                                    </label>
                                </div>
                                <div class='checkbox'>
                                    <label>
                                        <input type='checkbox' name='allow_rec_audio' value='1' $cbox_allow_rec_audio>
                                        $lang_allow_rec_audio
                                    </label>
                                </div>
                           </div>
                        </div>
                        <hr>
                        <div class='form-group'>
                            <div class='col-sm-12'>
                                <input class='btn btn-default' type='submit' name='submit' value='$langSave'>
                            </div>
                        </div>
                    </fieldset>
                </div>
            </div>
            <div class='panel panel-primary' id='seven'>
                <div class='panel-heading'>
                    <h2 class='panel-title'>$langMetaCommentary</h2>
                </div>
                <div class='panel-body'>
                    <fieldset>
                        <div class='form-group'>
                           <div class='col-sm-12'>
                                <div class='checkbox'>
                                    <label>
                                        <input type='checkbox' name='insert_xml_metadata' value='1' $cbox_insert_xml_metadata>
                                        $lang_insert_xml_metadata
                                    </label>
                                </div>
                                <div class='checkbox'>
                                    <label>
                                        <input type='checkbox' id='course_metadata' name='course_metadata' value='1' $cbox_course_metadata>
                                        $lang_course_metadata
                                    </label>
                                </div>
                                <div class='checkbox'>
                                    <label>
                                        <input type='checkbox' id='opencourses_enable' name='opencourses_enable' value='1' $cbox_opencourses_enable>
                                        $lang_opencourses_enable
                                    </label>
                                </div>
                            </div>
                        </div>
                        <hr>
                        <div class='form-group'>
                            <div class='col-sm-12'>
                                <input class='btn btn-default' type='submit' name='submit' value='$langSave'>
                            </div>
                        </div>
                    </fieldset>
                </div>
            </div>";

    $cbox_case_insensitive_usernames = get_config('case_insensitive_usernames') ? 'checked' : '';
    $cbox_email_required = get_config('email_required') ? 'checked' : '';
    $cbox_email_verification_required = get_config('email_verification_required') ? 'checked' : '';
    $cbox_email_prevent_autoset_change = get_config('email_prevent_autoset_change') ? 'checked' : '';
    $cbox_am_required = get_config('am_required') ? 'checked' : '';
    $cbox_am_prevent_autoset_change = get_config('am_prevent_autoset_change') ? 'checked' : '';
    $cbox_dropbox_allow_student_to_student = get_config('dropbox_allow_student_to_student') ? 'checked' : '';
    $cbox_dropbox_allow_personal_messages = get_config('dropbox_allow_personal_messages') ? 'checked' : '';
    $cbox_eportfolio_enable = get_config('eportfolio_enable') ? 'checked' : '';
    $cbox_personal_blog = get_config('personal_blog') ? 'checked' : '';
    $cbox_personal_blog_commenting = get_config('personal_blog_commenting') ? 'checked' : '';
    $cbox_personal_blog_rating = get_config('personal_blog_rating') ? 'checked' : '';
    $cbox_personal_blog_sharing = get_config('personal_blog_sharing') ? 'checked' : '';
    $cbox_block_username_change = get_config('block_username_change') ? 'checked' : '';
    $cbox_disable_name_surname_change = get_config('disable_name_surname_change') ? 'checked' : '';
    $cbox_disable_email_change = get_config('disable_email_change') ? 'checked' : '';
    $cbox_disable_am_change = get_config('disable_am_change') ? 'checked' : '';
    $cbox_enable_mobileapi = get_config('enable_mobileapi') ? 'checked' : '';
    $max_glossary_terms = get_config('max_glossary_terms');
    $cbox_enable_indexing = get_config('enable_indexing') ? 'checked' : '';
    $cbox_enable_search = get_config('enable_search') ? 'checked' : '';
    $cbox_enable_common_docs = get_config('enable_common_docs') ? 'checked' : '';
    $cbox_enable_docs_public_write = get_config('enable_docs_public_write') ? 'checked' : '';
    $cbox_mydocs_student_enable = get_config('mydocs_student_enable') ? 'checked' : '';
    $cbox_mydocs_teacher_enable = get_config('mydocs_teacher_enable') ? 'checked' : '';
    $mydocs_student_quota = floatval(get_config('mydocs_student_quota'));
    $mydocs_teacher_quota = floatval(get_config('mydocs_teacher_quota'));
    $cbox_enable_social_sharing_links = get_config('enable_social_sharing_links') ? 'checked' : '';
    $cbox_enable_strong_passwords = get_config('enable_strong_passwords') ? 'checked' : '';
    $cbox_disable_student_unregister_cours = get_config('disable_student_unregister_cours') ? 'checked' : '';
    $cbox_double_login_lock = get_config('double_login_lock') ? 'checked' : '';
    $cbox_login_fail_check = get_config('login_fail_check') ? 'checked' : '';
    $cbox_maintenance = get_config('maintenance') ? 'checked' : '';

    $id_enable_mobileapi = (check_auth_active(7) || check_auth_active(6)) ? "id='mobileapi_enable'" : '';

        $tool_content .= "
            <div class='panel panel-primary' id='eight'>
                <div class='panel-heading'>
                    <h2 class='panel-title'>$langOtherOptions</h2>
                </div>
                <div class='panel-body'>
                    <fieldset>
                        <div class='form-group'>
                           <div class='col-sm-12'>
                                <div class='checkbox'>
                                    <label>
                                        <input type='checkbox' name='case_insensitive_usernames' value='1' $cbox_case_insensitive_usernames>
                                        $langCaseInsensitiveUsername
                                    </label>
                                </div>
                                <div class='checkbox'>
                                    <label>
                                        <input type='checkbox' name='email_required' value='1' $cbox_email_required>
                                        $lang_email_required
                                    </label>
                                </div>
                                <div class='checkbox'>
                                    <label>
                                        <input type='checkbox' name='email_verification_required' value='1' $cbox_email_verification_required>
                                        $lang_email_verification_required
                                    </label>
                                </div>
                                <div class='checkbox'>
                                    <label>
                                        <input type='checkbox' name='email_prevent_autoset_change' value='1' $cbox_email_prevent_autoset_change>
                                        $lang_email_prevent_autoset_change
                                    </label>
                                </div>
                                <div class='checkbox'>
                                    <label>
                                        <input type='checkbox' name='am_required' value='1' $cbox_am_required>
                                        $lang_am_required
                                    </label>
                                </div>
                                <div class='checkbox'>
                                    <label>
                                        <input type='checkbox' name='am_prevent_autoset_change' value='1' $cbox_am_prevent_autoset_change>
                                        $lang_am_prevent_autoset_change
                                    </label>
                                </div>
                                <div class='checkbox'>
                                    <label>
                                        <input id='index_enable' type='checkbox' name='enable_indexing' value='1' $cbox_enable_indexing>
                                        $langEnableIndexing
                                    </label>
                                </div>
                                <div class='checkbox'>
                                    <label>
                                        <input id='search_enable' type='checkbox' name='enable_search' value='1' $cbox_enable_search>
                                        $langEnableSearch
                                    </label>
                                </div>
                                <div class='checkbox'>
                                    <label>
                                        <input type='checkbox' name='dropbox_allow_student_to_student' value='1' $cbox_dropbox_allow_student_to_student>
                                        $lang_dropbox_allow_student_to_student
                                    </label>
                                </div>
                                <div class='checkbox'>
                                    <label>
                                        <input type='checkbox' name='dropbox_allow_personal_messages' value='1' $cbox_dropbox_allow_personal_messages>
                                        $lang_dropbox_allow_personal_messages
                                    </label>
                                </div>
                                <div class='checkbox'>
                                    <label>
                                        <input id='personal_blog_enable' type='checkbox' name='personal_blog' value='1' $cbox_personal_blog>
                                        $lang_personal_blog
                                    </label>
                                </div>
                                <div class='checkbox'>
                                    <label>
                                        <input id='personal_blog_commenting_enable' type='checkbox' name='personal_blog_commenting' value='1' $cbox_personal_blog_commenting>
                                        $lang_personal_blog_commenting
                                    </label>
                                </div>
                                <div class='checkbox'>
                                    <label>
                                        <input id='personal_blog_rating_enable' type='checkbox' name='personal_blog_rating' value='1' $cbox_personal_blog_rating>
                                        $lang_personal_blog_rating
                                    </label>
                                </div>
                                <div class='checkbox'>
                                    <label>
                                        <input id='personal_blog_sharing_enable' type='checkbox' name='personal_blog_sharing' value='1' $cbox_personal_blog_sharing>
                                        $lang_personal_blog_sharing
                                    </label>
                                </div>
                                <div class='checkbox'>
                                    <label>
                                        <input id='eportfolio_enable' type='checkbox' name='eportfolio_enable' value='1' $cbox_eportfolio_enable>
                                        $lang_eportfolio_enable
                                    </label>
                                </div>
                                <div class='checkbox'>
                                    <label>
                                        <input type='checkbox' name='block_username_change' value='1' $cbox_block_username_change>
                                        $lang_block_username_change
                                    </label>
                                </div>
                                <div class='checkbox'>
                                    <label>
                                        <input type='checkbox' name='disable_name_surname_change' value='1' $cbox_disable_name_surname_change>
                                        $lang_disable_name_surname_change
                                    </label>
                                </div>
                                <div class='checkbox'>
                                    <label>
                                        <input type='checkbox' name='disable_email_change' value='1' $cbox_disable_email_change>
                                        $lang_disable_email_change
                                    </label>
                                </div>
                                <div class='checkbox'>
                                    <label>
                                        <input type='checkbox' name='disable_am_change' value='1' $cbox_disable_am_change>
                                        $lang_disable_am_change
                                    </label>
                                </div>
                                <div class='checkbox'>
                                    <label>
                                        <input $id_enable_mobileapi type='checkbox' name='enable_mobileapi' value='1' $cbox_enable_mobileapi>
                                        $lang_enable_mobileapi
                                    </label>
                                </div>
                                <div class='checkbox'>
                                    <label>
                                        <input id='social_sharing_links' type='checkbox' name='enable_social_sharing_links' value='1' $cbox_enable_social_sharing_links>
                                        $langEnableSocialSharingLiks
                                    </label>
                                </div>
                                <div class='checkbox'>
                                    <label>
                                        <input id='strong_passwords' type='checkbox' name='enable_strong_passwords' value='1' $cbox_enable_strong_passwords>
                                        $langEnableStrongPasswords
                                    </label>
                                </div>
                                <div class='checkbox'>
                                    <label>
                                        <input id='disable_student_unregister_cours' type='checkbox' name='disable_student_unregister_cours' value='1' $cbox_disable_student_unregister_cours>
                                        $langUnsubscribeCourse
                                    </label>
                                </div>
                                <div class='checkbox'>
                                    <label>
                                        <input id='double_login_lock' type='checkbox' name='double_login_lock' value='1' $cbox_double_login_lock>
                                        $langDoubleLoginLockOption
                                    </label>
                                </div>
                           </div>
                        </div>
                        <hr><br>
                        <div class='form-group'>
                           <label for='min_password_len' class='col-sm-4 control-label'>$langMinPasswordLen: </label>
                           <div class='col-sm-8'>
                                <input type='text' class='form-control' name='min_password_len' id='min_password_len' value='" . intval(get_config('min_password_len')) . "'>
                           </div>
                        </div>
                        <div class='form-group'>
                           <label for='min_password_len' class='col-sm-4 control-label'>$lang_max_glossary_terms </label>
                           <div class='col-sm-8'>
                                <input class='form-control' type='text' name='max_glossary_terms' value='$max_glossary_terms'>
                           </div>
                        </div>
                        <div class='form-group'>
                           <label for='min_password_len' class='col-sm-4 control-label'>$langActionsExpireInterval ($langMonthsUnit):</label>
                           <div class='col-sm-8'>
                                <input type='text' class='form-control' name='actions_expire_interval' value='" . get_config('actions_expire_interval') . "'>
                           </div>
                        </div>
                        <hr>
                        <div class='form-group'>
                            <div class='col-sm-12'>
                                <input class='btn btn-default' type='submit' name='submit' value='$langSave'>
                            </div>
                        </div>
                    </fieldset>
                </div>
            </div>

            <div class='panel panel-primary' id='nine'>
                <div class='panel-heading'>
                    <h2 class='panel-title'>$langDocumentSettings</h2>
                </div>
                <div class='panel-body'>
                    <fieldset>
                        <div class='form-group'>
                           <label class='col-sm-4 control-label'>$langEnableMyDocs:</label>
                           <div class='col-sm-8'>
                                <div class='checkbox'>
                                    <label>
                                        <input type='checkbox' name='mydocs_teacher_enable' id='mydocs_teacher_enable_id' value='1' $cbox_mydocs_teacher_enable>
                                        $langTeachers
                                    </label>
                                </div>
                                <div class='checkbox'>
                                    <label>
                                        <input type='checkbox' name='mydocs_student_enable' id='mydocs_student_enable_id' value='1' $cbox_mydocs_student_enable>
                                        $langStudents
                                    </label>
                                </div>
                           </div>
                        </div>
                        <div class='form-group'>
                           <label class='col-sm-4 control-label'>$langMyDocsQuota (MB):</label>
                           <div class='col-sm-8'>
                                <label>
                                    <input type='text' name='mydocs_teacher_quota' id='mydocs_teacher_quota_id' value='$mydocs_teacher_quota'>
                                    $langTeachers
                                </label>
                                <label>
                                    <input type='text' name='mydocs_student_quota' id='mydocs_student_quota_id' value='$mydocs_student_quota'>
                                    $langStudents
                                </label>
                           </div>
                        </div>
                        <div class='form-group'>
                               <label for='bio_quota' class='col-sm-4 control-label'>$langBioQuota (MB):</label>
                               <div class='col-sm-4'>
                                    <input class='form-control' type='text' name='bio_quota' id='bio_quota' value='".get_config('bio_quota')."'>
                               </div>
                            </div>
                        <div class='checkbox'>
                            <label>
                                <input type='checkbox' name='enable_common_docs' value='1' $cbox_enable_common_docs>
                                $langEnableCommonDocs
                            </label>
                        </div>
                        <div class='checkbox'>
                            <label>
                                <input type='checkbox' name='enable_docs_public_write' value='1' $cbox_enable_docs_public_write>
                                $langEnableDocsPublicWrite
                            </label>
                        </div>
                        <hr>
                        <div class='form-group'>
                            <div class='col-sm-12'>
                                <input class='btn btn-default' type='submit' name='submit' value='$langSave'>
                            </div>
                        </div>
                    </fieldset>
                </div>
            </div>

            <div class='panel panel-primary' id='ten'>
                <div class='panel-heading'>
                    <h2 class='panel-title'>$langDefaultQuota</h2>
                </div>
                <div class='panel-body'>
                    <fieldset>
                        <div class='form-group'>
                           <label for='doc_quota' class='col-sm-4 control-label'>$langDocQuota (MB):</label>
                           <div class='col-sm-8'>
                                <input class='form-control' type='text' name='doc_quota' id='doc_quota' value='" . get_config('doc_quota') . "'>
                           </div>
                        </div>
                        <div class='form-group'>
                           <label for='video_quota' class='col-sm-4 control-label'>$langVideoQuota (MB):</label>
                           <div class='col-sm-8'>
                                <input class='form-control' type='text' name='video_quota' id='video_quota' value='" . get_config('video_quota') . "'>
                           </div>
                        </div>
                        <div class='form-group'>
                           <label for='group_quota' class='col-sm-4 control-label'>$langGroupQuota (MB):</label>
                           <div class='col-sm-8'>
                                <input class='form-control' type='text' name='group_quota' id='group_quota' value='" . get_config('group_quota') . "'>
                           </div>
                        </div>
                        <div class='form-group'>
                           <label for='dropbox_quota' class='col-sm-4 control-label'>$langDropboxQuota (MB):</label>
                           <div class='col-sm-8'>
                                <input class='form-control' type='text' name='dropbox_quota' id='dropbox_quota' value='" . get_config('dropbox_quota') . "'>
                           </div>
                        </div>
                        <hr>
                        <div class='form-group'>
                            <div class='col-sm-12'>
                                <input class='btn btn-default' type='submit' name='submit' value='$langSave'>
                            </div>
                        </div>
                    </fieldset>
                </div>
            </div>
            <div class='panel panel-primary' id='eleven'>
                <div class='panel-heading'>
                    <h2 class='panel-title'>$langUploadWhitelist</h2>
                </div>
                <div class='panel-body'>
                    <fieldset>
                        <div class='form-group'>
                           <label for='student_upload_whitelist' class='col-sm-4 control-label'>$langStudentUploadWhitelist:</label>
                           <div class='col-sm-8'>
                                <textarea class='form-control' rows='6' name='student_upload_whitelist' id='student_upload_whitelist'>" . get_config('student_upload_whitelist') . "</textarea>
                           </div>
                        </div>
                        <div class='form-group'>
                           <label for='teacher_upload_whitelist' class='col-sm-4 control-label'>$langTeacherUploadWhitelist:</label>
                           <div class='col-sm-8'>
                                <textarea class='form-control' rows='6' name='teacher_upload_whitelist' id='teacher_upload_whitelist'>" . get_config('teacher_upload_whitelist') . "</textarea>
                           </div>
                        </div>
                        <hr>
                        <div class='form-group'>
                            <div class='col-sm-12'>
                                <input class='btn btn-default' type='submit' name='submit' value='$langSave'>
                            </div>
                        </div>
                    </fieldset>
                </div>
            </div>";

    $cbox_disable_cron_jobs = get_config('disable_cron_jobs') ? 'checked' : '';
    $cbox_disable_log_actions = get_config('disable_log_actions') ? 'checked' : '';
    $cbox_disable_log_course_actions = get_config('disable_log_course_actions') ? 'checked' : '';
    $cbox_disable_log_system_actions = get_config('disable_log_system_actions') ? 'checked' : '';

$tool_content .= "
            <div class='panel panel-primary' id='twelve'>
                <div class='panel-heading'>
                    <h2 class='panel-title'>$langUsage / $langLogActions</h2>
                </div>
                <div class='panel-body'>
                    <div class='form-group'>
                       <div class='col-sm-12'>
                            <div class='checkbox'>
                                <label>
                                    <input type='checkbox' name='disable_cron_jobs' value='1' $cbox_disable_cron_jobs>
                                    $lang_disable_cron_jobs
                                </label>
                            </div>
                            <div class='checkbox'>
                                <label>
                                    <input type='checkbox' name='disable_log_actions' value='1' $cbox_disable_log_actions>
                                    $lang_disable_log_actions
                                </label>
                            </div>
                            <div class='checkbox'>
                                <label>
                                    <input type='checkbox' name='disable_log_course_actions' value='1' $cbox_disable_log_course_actions>
                                    $lang_disable_log_course_actions
                                </label>
                            </div>
                            <div class='checkbox'>
                                <label>
                                    <input type='checkbox' name='disable_log_system_actions' value='1' $cbox_disable_log_system_actions>
                                    $lang_disable_log_system_actions
                                </label>
                            </div>
                        </div>
                    </div>
                    <hr><br>
                    <div class='form-group'>
                       <label for='log_expire_interval' class='col-sm-4 control-label'>$langLogExpireInterval ($langMonthsUnit):</label>
                       <div class='col-sm-8'>
                            <input class='form-control' type='text' name='log_expire_interval' id='log_expire_interval' value='" . get_config('log_expire_interval') . "'>
                       </div>
                    </div>
                    <div class='form-group'>
                       <label for='log_purge_interval' class='col-sm-4 control-label'>$langLogPurgeInterval ($langMonthsUnit):</label>
                       <div class='col-sm-8'>
                            <input class='form-control' type='text' name='log_purge_interval' id='log_purge_interval' value='" . get_config('log_purge_interval') . "'>
                       </div>
                    </div>
                    <hr>
                    <div class='form-group'>
                        <div class='col-sm-12'>
                            <input class='btn btn-default' type='submit' name='submit' value='$langSave'>
                        </div>
                    </div>
                </div>
            </div>
            <div class='panel panel-primary' id='thirteen'>
                <div class='panel-heading'>
                    <h2 class='panel-title'>$langLoginFailCheck</h2>
                </div>
                <div class='panel-body'>
                    <fieldset>
                        <div class='form-group'>
                           <div class='col-sm-12'>
                                <div class='checkbox'>
                                    <label>
                                        <input id='login_fail_check' type='checkbox' name='login_fail_check' value='1' $cbox_login_fail_check>
                                        $langEnableLoginFailCheck
                                    </label>
                                </div>
                            </div>
                        </div>
                        <div class='form-group' id='login_fail_threshold'>
                           <label for='login_fail_threshold' class='col-sm-4 control-label'>$langLoginFailThreshold:</label>
                           <div class='col-sm-8'>
                                <input class='form-control' type='text' name='login_fail_threshold' id='login_fail_threshold' value='" . get_config('login_fail_threshold') . "'>
                           </div>
                        </div>
                        <div class='form-group' id='login_fail_deny_interval'>
                           <label for='login_fail_deny_interval' class='col-sm-4 control-label'>$langLoginFailDenyInterval ($langMinute):</label>
                           <div class='col-sm-8'>
                                <input class='form-control' type='text' name='login_fail_deny_interval' id='login_fail_deny_interval' value='" . get_config('login_fail_deny_interval') . "'>
                           </div>
                        </div>
                        <div class='form-group' id='login_fail_forgive_interval'>
                           <label for='login_fail_forgive_interval' class='col-sm-4 control-label'>$langLoginFailForgiveInterval ($langHours):</label>
                           <div class='col-sm-8'>
                                <input class='form-control' type='text' name='login_fail_forgive_interval' id='login_fail_forgive_interval' value='" . get_config('login_fail_forgive_interval') . "'>
                           </div>
                        </div>
                        <hr>
                        <div class='form-group'>
                            <div class='col-sm-12'>
                                <input class='btn btn-default' type='submit' name='submit' value='$langSave'>
                            </div>
                        </div>
                    </fieldset>
                </div>
            </div>";

    $cbox_activate_privacy_policy_text = get_config('activate_privacy_policy_text') ? 'checked' : '';
    $cbox_activate_privacy_policy_consent = get_config('activate_privacy_policy_consent') ? 'checked' : '';
    $tool_content .= "<div class='panel panel-primary' id='fourteen'>
                        <div class='panel-heading'>
                            <h2 class='panel-title'>$langPrivacyPolicy</h2>
                        </div>
                        <div class='panel-body'>
                            <div class='margin-bottom-fat margin-top-fat'><strong>$langPrivacyPolicyLegend</strong></div>
                            <fieldset>
                                <div class='landing-default'>";

    foreach ($session->active_ui_languages as $langcode) {
        $langname = $langNameOfLang[langcode_to_name($langcode)];
        $policy = get_config('privacy_policy_text_' . $langcode);
        if (!$policy) {
            $policyFile = "lang/$langcode/privacy.html";
            if (file_exists($policyFile)) {
                $policy = file_get_contents($policyFile);
            } else {
                $policy = get_config('privacy_policy_text_en');
                if (!$policy) {
                    $policyFile = "lang/en/privacy.html";
                }
            }
        }
        $tool_content .= "
                                    <div class='form-group'>
                                        <label for='privacy_policy_text_$langcode' class='col-sm-2 control-label'>$langText:<br>($langname)</label>
                                        <div class='col-sm-10'>" .
                                            rich_text_editor("privacy_policy_text_$langcode", 5, 20, $policy) . "
                                        </div>
                                    </div>";
    }
    $tool_content .= "
                                    <div class='form-group'>
                                        <label for='theme' class='col-sm-2 control-label'>$langViewShow: </label>
                                        <div class='col-sm-10'>
                                            <div class='checkbox'>
                                                <label>
                                                    <input id='privacyPolicyLink' type='checkbox' name='activate_privacy_policy_text' value='1' $cbox_activate_privacy_policy_text> $langDisplayPrivacyPolicyLink
                                                </label>
                                            </div>
                                            <div class='checkbox'>
                                                <label>
                                                    <input id='privacyPolicyConsent' type='checkbox' name='activate_privacy_policy_consent' value='1' $cbox_activate_privacy_policy_consent> $langAskPrivacyPolicyConsent
                                                </label>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                                <div class='form-group'>
                                    <div class='col-sm-12'>
                                        <input class='btn btn-default' type='submit' name='submit' value='$langSave'>
                                    </div>
                                </div>
                            </fieldset>
                        </div>
                    </div>";

            $tool_content .= "<div class='panel panel-primary' id='fifteen'>
                <div class='panel-heading'>
                    <h2 class='panel-title'>$langMaintenance</h2>
                </div>
                <div class='panel-body'>
                        <div class='form-group'>

                            <div class='col-sm-9'>
                                <div class='checkbox'>
                                    <label>
                                        <input type='checkbox' name='maintenance' value='1' $cbox_maintenance>
                                        $langMaintenanceMode
                                    </label>
                                </div>
                            </div>

                        </div>
                        <hr>
                        <div class='margin-bottom-fat margin-top-fat'><strong>$langMaintenanceChange</strong></div>

                        <fieldset>
                            <div class='landing-default'>";

            foreach ($session->active_ui_languages as $langcode) {
                $langname = $langNameOfLang[langcode_to_name($langcode)];
                $maintenance_text = get_config('maintenance_text_' . $langcode);
                $tool_content .= "
                                            <div class='form-group'>
                                                <label for='privacy_policy_text_$langcode' class='col-sm-2 control-label'>$langText:<br>($langname)</label>
                                                <div class='col-sm-10'>" .
                    rich_text_editor("maintenance_text_$langcode", 5, 20, $maintenance_text) . "
                                                </div>
                                            </div>";
            }

            $tool_content .= "</div>
                            <hr>
                            <div>
                                <div class='margin-bottom-fat margin-top-fat'><strong>$langThemes</strong></div>
                                <div class='row'>
                                    <div class='col-sm-6'>
                                        <div class='form-check'>
                                            <input class='form-check-input' type='radio' name='maintenance_theme' id='image1' value='1' ".checkMaintenanceTheme($maintenance_theme, 1).">
                                            <label class='form-check-label' for='image1'>
                                                <img style='max-width: 300px;' src='../../maintenance/preview_img/theme_1.png' class='img-fluid' alt='Image preview 1'>
                                            </label>
                                        </div>
                                    </div>
                                    <div class='col-sm-6'>
                                        <div class='form-check'>
                                            <input class='form-check-input' type='radio' name='maintenance_theme' id='image2' value='2' ".checkMaintenanceTheme($maintenance_theme, 2).">
                                            <label class='form-check-label' for='image2'>
                                                <img style='max-width: 300px;' src='../../maintenance/preview_img/theme_2.png' class='img-fluid' alt='Image preview 2'>
                                            </label>
                                        </div>
                                    </div>

                                </div>
                                <div class='row'>
                                    <div class='col-sm-6'>
                                        <div class='form-check'>
                                            <input class='form-check-input' type='radio' name='maintenance_theme' id='image3' value='3' ".checkMaintenanceTheme($maintenance_theme, 3).">
                                            <label class='form-check-label' for='image3'>
                                                <img style='max-width: 300px;' src='../../maintenance/preview_img/theme_3.png' class='img-fluid' alt='Image preview 3'>
                                            </label>
                                        </div>
                                    </div>
                                    <div class='col-sm-6'>
                                        <div class='form-check'>
                                            <input class='form-check-input' type='radio' name='maintenance_theme' id='image4' value='4' ".checkMaintenanceTheme($maintenance_theme, 4).">
                                            <label class='form-check-label' for='image4'>
                                                <img style='max-width: 300px;' src='../../maintenance/preview_img/theme_4.png' class='img-fluid' alt='Image preview 4'>
                                            </label>
                                        </div>
                                    </div>
                                </div>
                                <div class='row'>
                                    <div class='col-sm-6'>
                                        <div class='form-check'>
                                            <input class='form-check-input' type='radio' name='maintenance_theme' id='image5' value='5' ".checkMaintenanceTheme($maintenance_theme, 5).">
                                            <label class='form-check-label' for='image5'>
                                                <img style='max-width: 300px;' src='../../maintenance/preview_img/theme_5.png' class='img-fluid' alt='Image preview 5'>
                                            </label>
                                        </div>
                                    </div>
                                    <div class='col-sm-6'>
                                        <div class='form-check'>
                                            <input class='form-check-input' type='radio' name='maintenance_theme' id='image6' value='6' ".checkMaintenanceTheme($maintenance_theme, 6).">
                                            <label class='form-check-label' for='image6'>
                                                <img style='max-width: 300px;' src='../../maintenance/preview_img/theme_6.png' class='img-fluid' alt='Image preview 6'>
                                            </label>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <hr>
                                <div class='form-group'>
                                    <div class='col-sm-12'>
                                        <input class='btn btn-default' type='submit' name='submit' value='$langSave'>
                                    </div>
                                </div>
                        </fieldset>
                    </div>
                </div>
            ";
            $tool_content .= generate_csrf_token_form_field() ."
        </form>
    </div>";
    $head_content .= "
        <script>
        $(document).ready(function(){

            var toCollapse = '#' + $(\"input[name='homepageSet']:checked\").data('collapse');

            $(toCollapse).collapse('show');
            $('.homepageSet').change(function() {
                $('.collapse.in').collapse('hide');
                var newCollapse = '#' + $(this).data('collapse');
                $(newCollapse).collapse('show');
            });

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
        });
        </script>";
    $tool_content .= "
        <div class='col-sm-3 hidden-xs' id='affixedSideNav'>
            <ul id='floatMenu' class='nav nav-pills nav-stacked well well-sm' role='tablist'>
                <li class='active'><a href='#one'>$langBasicCfgSetting</a></li>
                <li><a href='#two'>$langUpgReg</a></li>
                <li><a href='#three'>$langChooseLang</a></li>
                <li><a href='#four'>$langHomePageSettings</a></li>
                <li><a href='#five'>$langEmailSettings</a></li>
                <li><a href='#six'>$langCourseSettings</a></li>
                <li><a href='#seven'>$langMetaCommentary</a></li>
                <li><a href='#eight'>$langOtherOptions</a></li>
                <li><a href='#nine'>$langDocumentSettings</a></li>
                <li><a href='#ten'>$langDefaultQuota</a></li>
                <li><a href='#eleven'>$langUploadWhitelist</a></li>
                <li><a href='#twelve'>$langUsage / $langLogActions</a></li>
                <li><a href='#thirteen'>$langLoginFailCheck</a></li>
                <li><a href='#fourteen'>$langPrivacyPolicy</a></li>
                <li><a href='#fifteen'>$langMaintenance</a></li>
            </ul>
        </div>
    </div>";
    // Modal dialogs
    $tool_content .= modalConfirmation('confirmIndexDialog', 'confirmIndexLabel', $langConfirmEnableIndexTitle, $langConfirmEnableIndex, 'confirmIndexCancel', 'confirmIndexOk');
    $tool_content .= modalConfirmation('confirmMobileAPIDialog', 'confirmMobileAPILabel', $langConfirmEnableMobileAPITitle, $langConfirmEnableMobileAPI, 'confirmMobileAPICancel', 'confirmMobileAPIOk');

}

draw($tool_content, 3, null, $head_content);
