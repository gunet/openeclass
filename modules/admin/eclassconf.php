<?php

/* ========================================================================
 * Open eClass 3.0
 * E-learning and Course Management System
 * ========================================================================
 * Copyright 2003-2013  Greek Universities Network - GUnet
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


// Check if user is administrator and if yes continue
// Othewise exit with appropriate message
$require_admin = true;
require_once '../../include/baseTheme.php';
require_once 'modules/auth/auth.inc.php';
$nameTools = $langEclassConf;
$navigation[] = array('url' => 'index.php', 'name' => $langAdmin);

load_js('jquery');
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

    // Course Settings checkboxes
    $('#uown').click(function(event) {
        if (!$('#uown').is(":checked")) {
            $('#town').prop('checked', false);
        }
        $('#town').prop('disabled', !$('#uown').is(":checked"));
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

});

/* ]]> */
</script>
EOF;

$available_themes = active_subdirs("$webDir/template", 'theme.html');

define('MONTHS', 30 * 24 * 60 * 60);

// Save new config.php
if (isset($_POST['submit'])) {
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
        set_config('language', $_POST['default_language']);
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

    $config_vars = array('email_required' => true,
        'email_verification_required' => true,
        'dont_mail_unverified_mails' => true,
        'email_from' => true,
        'am_required' => true,
        'dont_display_login_form' => true,
        'dropbox_allow_student_to_student' => true,
        'dropbox_allow_personal_messages' => true,
        'block_username_change' => true,
        'display_captcha' => true,
        'insert_xml_metadata' => true,        
        'enable_mobileapi' => true,
        'doc_quota' => true,
        'group_quota' => true,
        'video_quota' => true,
        'dropbox_quota' => true,
        'max_glossary_terms' => true,
        'theme' => true,
        'case_insensitive_usernames' => true,
        'course_multidep' => true,
        'user_multidep' => true,
        'restrict_owndep' => true,
        'restrict_teacher_owndep' => true,
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
        'enable_social_sharing_links' => true,
        'login_fail_check' => true,
        'login_fail_threshold' => true,
        'login_fail_deny_interval' => true,
        'login_fail_forgive_interval' => true,
        'actions_expire_interval' => true,
        'log_expire_interval' => true,
        'log_purge_interval' => true,
        'course_metadata' => true,
        'opencourses_enable' => true);

    register_posted_variables($config_vars, 'all', 'intval');
    $_SESSION['theme'] = $theme = $available_themes[$theme];

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
        $idx = new Indexer();
        $idx->deleteAll();
    }

    // update table `config`
    foreach ($config_vars as $varname => $what) {
        set_config($varname, $GLOBALS[$varname]);
    }
    
    // Display result message
    $tool_content .= "<p class='success'>$langFileUpdatedSuccess</p>";
    
    // schedule indexing if necessary
    if ($scheduleIndexing) {
        $tool_content .= "<p class='alert1'>{$langIndexingNeeded} <a id='idxpbut' href='../search/idxpopup.php' onclick=\"return idxpopup('../search/idxpopup.php', 600, 500)\">{$langHere}.</a></p>";
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
    
    // Display link to go back to index.php
    $tool_content .= "<p class='right'><a href='index.php'>$langBack</a></p>";
} // end of if($submit)
// Display config.php edit form
else {
    $tool_content .= "<form action='$_SERVER[SCRIPT_NAME]' method='post'>
                <fieldset><legend>$langBasicCfgSetting</legend>
	<table class='tbl' width='100%'>
	<tr>
	  <th width='200' class='left'><b>$langSiteUrl:</b></th>
	  <td><input class='FormData_InputText' type='text' name='formurlServer' size='40' value='" . q($urlServer) . "'></td>
	</tr>
        <tr>
	  <th class='left'><b>$langphpMyAdminURL:</b></th>
	  <td><input class='FormData_InputText' type='text' name='formphpMyAdminURL' size='40' value='" . q(get_config('phpMyAdminURL')) . "'></td>
	</tr>
	<tr>
	  <th class='left'><b>$langSystemInfoURL:</b></th>
	  <td><input class='FormData_InputText' type='text' name='formphpSysInfoURL' size='40' value='" . q(get_config('phpSysInfoURL')) . "'></td>
	</tr>
	<tr>
	  <th class='left'><b>$langAdminEmail:</b></th>
	  <td><input class='FormData_InputText' type='text' name='formemailAdministrator' size='40' value='" . q(get_config('email_sender')) . "'></td>
	</tr>
	<tr>
	  <th class='left'><b>$langDefaultAdminName:</b></th>
	  <td><input class='FormData_InputText' type='text' name='formadministratorName' size='40' value='" . q(get_config('admin_name')) . "'></td>
	</tr>
	<tr>
	  <th class='left'><b>$langCampusName:</b></th>
	  <td><input class='FormData_InputText' type='text' name='formsiteName' size='40' value='" . q(get_config('site_name')) . "'></td>
	</tr>
	<tr>
	  <th class='left'>$langPostMail</th>
	      <td><textarea rows='3' cols='40' name='formpostaddress'>" . q(get_config('postaddress')) . "</textarea></td>
	</tr>
	<tr>
	  <th class='left'><b>$langPhone:</b></th>
	  <td><input class='FormData_InputText' type='text' name='formtelephone' size='40' value='" . q(get_config('phone')) . "'></td>
	</tr>
	<tr>
	  <th class='left'>$langFax</th>
	  <td><input class='FormData_InputText' type='text' name='formfax' size='40' value='" . q(get_config('fax')) . "'></td>
	</tr>
	<tr>
	  <th class='left'><b>$langHelpDeskEmail:</b></th>
	  <td><input class='FormData_InputText' type='text' name='formemailhelpdesk' size='40' value='" . q(get_config('email_helpdesk')) . "'></td>
	</tr>
	<tr>
	  <th class='left'><b>$langInstituteShortName:</b></th>
	  <td><input class='FormData_InputText' type='text' name='formInstitution' size='40' value='" . $Institution . "'></td>
	</tr>
	<tr>
	  <th class='left'><b>$langInstituteName:</b></th>
	  <td><input class='FormData_InputText' type='text' name='formInstitutionUrl' size='40' value='" . $InstitutionUrl . "'></td>
	</tr>";
    if ($language == "el") {
        $grSel = "selected";
        $enSel = "";
    } else {
        $grSel = "";
        $enSel = "selected";
    }
    $tool_content .= "</table></fieldset>";

    $tool_content .= "<fieldset>
        <legend>$langUpgReg</legend>
        <table class='tbl' width='100%'>";
    $tool_content .= "<tr><th width='300' class='left'>$langUserRegistration</th><td>";
    $tool_content .= selection(array('1' => $langActivate,
        '0' => $langDeactivate), 'user_registration', get_config('user_registration'));
    $tool_content .= "</td></tr>";
    $tool_content .= "<tr><th class='left'>$langUserAccount $langViaeClass</th><td>";
    $tool_content .= selection(array('0' => $langDisableEclassStudReg,
        '1' => $langReqRegUser,
        '2' => $langDisableEclassStudRegType), 'eclass_stud_reg', get_config('eclass_stud_reg'));
    $tool_content .= "</td></tr>";
    $tool_content .= "<tr><th class='left'>$langUserAccount $langViaAltAuthMethods</th><td>";
    $tool_content .= selection(array('0' => $langDisableEclassStudReg,
        '1' => $langReqRegUser,
        '2' => $langDisableEclassStudRegType), 'alt_auth_stud_reg', get_config('alt_auth_stud_reg'));
    $tool_content .= "</td></tr>";

    $tool_content .= "<tr><th class='left'>$langProfAccount $langViaeClass</th><td>";
    $tool_content .= selection(array('0' => $langDisableEclassProfReg,
        '1' => $langReqRegProf), 'eclass_prof_reg', get_config('eclass_prof_reg'));
    $tool_content .= "</td></tr>";

    $tool_content .= "<tr><th class='left'>$langProfAccount $langViaAltAuthMethods</th><td>";
    $tool_content .= selection(array('0' => $langDisableEclassProfReg,
        '1' => $langReqRegProf), 'alt_auth_prof_reg', get_config('alt_auth_prof_reg'));
    $tool_content .= "</td></tr>";

    $tool_content .= "<tr>        
                <td>$langUserDurationAccount&nbsp;($langMonthsUnit)&nbsp;&nbsp;<input type='text' name='formdurationAccount' size='3' maxlength='3' value='" . intval(get_config('account_duration') / MONTHS) . "'></td>
                </tr>";
    $tool_content .= "</table></fieldset>";

    $tool_content .= "<fieldset><legend>$langEclassThemes</legend>
        <table class='tbl' width='100%'>
        <tr>
	  <th class='left'><b>$langMainLang</b></th>
	  <td><select name='default_language'>
	    <option value='el' $grSel>$langGreek</option>
	    <option value='en' $enSel>$langEnglish</option>
	  </select></td>
	</tr>";
    $active_ui_languages = explode(' ', get_config('active_ui_languages'));
    $langdirs = active_subdirs($webDir . '/lang', 'messages.inc.php');
    $sel = array();
    foreach ($language_codes as $langcode => $langname) {
        if (in_array($langcode, $langdirs)) {
            $loclangname = $langNameOfLang[$langname];
            $checked = in_array($langcode, $active_ui_languages) ? ' checked' : '';
            $sel[] = "<input type='checkbox' name='av_lang[]' value='$langcode'$checked>$loclangname";
        }
    }
    $tool_content .= "<tr><th class='left'>$langSupportedLanguages</th>
	    <td>" . implode(' ', $sel) . "</td></tr>";

    $tool_content .= "
	  <tr><td class='left'><b>$langThemes</b></td>
	    <td>" . selection($available_themes, 'theme', array_search($theme, $available_themes)) . "</td></tr>";
    $cbox_dont_display_login_form = get_config('dont_display_login_form') ? 'checked' : '';
    $tool_content .= "<tr>		
		<td colspan='2'><input type='checkbox' name='dont_display_login_form' value='1' $cbox_dont_display_login_form />&nbsp;$lang_dont_display_login_form</td>
	  </tr>";
    $tool_content .= "</table></fieldset>";

    $cbox_dont_mail_unverified_mails = get_config('dont_mail_unverified_mails') ? 'checked' : '';
    $cbox_email_from = get_config('email_from') ? 'checked' : '';
    $tool_content .= "<fieldset>
        <legend>$langEmailSettings</legend>
        <table class='tbl' width='100%'>
        <tr>	
		<td><input type='checkbox' name='dont_mail_unverified_mails' value='1' $cbox_dont_mail_unverified_mails />&nbsp;$lang_dont_mail_unverified_mails</td>
	  </tr>
          <tr>	
		<td><input type='checkbox' name='email_from' value='1' $cbox_email_from />&nbsp;$lang_email_from</td>
	  </tr>
        </table></fieldset>";

    $cbox_course_multidep = get_config('course_multidep') ? 'checked' : '';
    $cbox_user_multidep = get_config('user_multidep') ? 'checked' : '';
    $cbox_restrict_owndep = get_config('restrict_owndep') ? 'checked' : '';
    $cbox_restrict_teacher_owndep = get_config('restrict_teacher_owndep') ? 'checked' : '';
    $town_dis = get_config('restrict_owndep') ? '' : 'disabled';
    $cbox_insert_xml_metadata = get_config('insert_xml_metadata') ? 'checked' : '';
    $cbox_course_metadata = get_config('course_metadata') ? 'checked' : '';
    $cbox_opencourses_enable = get_config('opencourses_enable') ? 'checked' : '';

    $tool_content .= "<fieldset>
        <legend>$langCourseSettings</legend>
        <table class='tbl' width='100%'>
        <tr>
		<td><input type='checkbox' name='course_multidep' value='1' $cbox_course_multidep />&nbsp;$lang_course_multidep</td>
	  </tr>
	  <tr>	
		<td><input type='checkbox' name='user_multidep' value='1' $cbox_user_multidep />&nbsp;$lang_user_multidep</td>
	  </tr>
	  <tr>		
		<td><input id='uown' type='checkbox' name='restrict_owndep' value='1' $cbox_restrict_owndep />&nbsp;$lang_restrict_owndep</td>
	  </tr>
	  <tr>		
		<td><input id='town' type='checkbox' name='restrict_teacher_owndep' value='1' $town_dis $cbox_restrict_teacher_owndep />&nbsp;$lang_restrict_teacher_owndep</td>
	  </tr>
        </table></fieldset>";

    $tool_content .= "<fieldset>
        <legend>$langMetaCommentary</legend>
        <table class='tbl' width='100%'>
	  <tr>		
                <td><input type='checkbox' name='insert_xml_metadata' value='1' $cbox_insert_xml_metadata />&nbsp;$lang_insert_xml_metadata</td>
          </tr>
          <tr>
                <td><input type='checkbox' id='course_metadata' name='course_metadata' value='1' $cbox_course_metadata />&nbsp;$lang_course_metadata</td>
          </tr>
          <tr>
                <td><input type='checkbox' id='opencourses_enable' name='opencourses_enable' value='1' $cbox_opencourses_enable />&nbsp;$lang_opencourses_enable</td>
          </tr>
        </table></fieldset>";

    $cbox_case_insensitive_usernames = get_config('case_insensitive_usernames') ? 'checked' : '';
    $cbox_email_required = get_config('email_required') ? 'checked' : '';
    $cbox_email_verification_required = get_config('email_verification_required') ? 'checked' : '';
    $cbox_am_required = get_config('am_required') ? 'checked' : '';
    $cbox_display_captcha = get_config('display_captcha') ? 'checked' : '';
    $cbox_dropbox_allow_student_to_student = get_config('dropbox_allow_student_to_student') ? 'checked' : '';
    $cbox_dropbox_allow_personal_messages = get_config('dropbox_allow_personal_messages') ? 'checked' : '';
    $cbox_block_username_change = get_config('block_username_change') ? 'checked' : '';    
    $cbox_enable_mobileapi = get_config('enable_mobileapi') ? 'checked' : '';
    $max_glossary_terms = get_config('max_glossary_terms');
    $cbox_enable_indexing = get_config('enable_indexing') ? 'checked' : '';
    $cbox_enable_search = get_config('enable_search') ? 'checked' : '';
    $cbox_enable_common_docs = get_config('enable_common_docs') ? 'checked' : '';
    $cbox_enable_social_sharing_links = get_config('enable_social_sharing_links') ? 'checked' : '';
    $cbox_login_fail_check = get_config('login_fail_check') ? 'checked' : '';
    $id_enable_mobileapi = (check_auth_active(7) || check_auth_active(6)) ? "id='mobileapi_enable'" : '';

    $tool_content .= "<fieldset>
        
        <legend>$langOtherOptions</legend>
        <table class='tbl' width='100%'>
        <tr>
                <td><input type='checkbox' name='case_insensitive_usernames' value='1' $cbox_case_insensitive_usernames>&nbsp;$langCaseInsensitiveUsername</td>
        </tr>
        <tr>
                <td><input type='checkbox' name='email_required' value='1' $cbox_email_required />&nbsp;$lang_email_required</td>
        </tr>
        <tr>	
                <td><input type='checkbox' name='email_verification_required' value='1' $cbox_email_verification_required />&nbsp;$lang_email_verification_required</td>
        </tr>
        <tr>		
                <td><input type='checkbox' name='am_required' value='1' $cbox_am_required />&nbsp;$lang_am_required</td>
        </tr>
        <tr>		
                <td><input type='checkbox' name='display_captcha' value='1' $cbox_display_captcha />&nbsp;$lang_display_captcha</td>
        </tr>
        <tr>        
                <td>$langMinPasswordLen&nbsp;&nbsp;<input type='text' name='min_password_len' size='15' value='" . intval(get_config('min_password_len')) . "'></td></tr>        
        <tr>
                <td><input id='index_enable' type='checkbox' name='enable_indexing' value='1' $cbox_enable_indexing />&nbsp;$langEnableIndexing</td>
        </tr>
        <tr>
                <td><input id='search_enable' type='checkbox' name='enable_search' value='1' $cbox_enable_search />&nbsp;$langEnableSearch</td>
        </tr>
        <tr>		
                <td>$lang_max_glossary_terms&nbsp;<input type='text' name='max_glossary_terms' value='$max_glossary_terms' size='5' /></td>
        </tr>
        <tr>		
                <td><input type='checkbox' name='dropbox_allow_student_to_student' value='1' $cbox_dropbox_allow_student_to_student />&nbsp;$lang_dropbox_allow_student_to_student</td>
        </tr>
        <tr>		
                <td><input type='checkbox' name='dropbox_allow_personal_messages' value='1' $cbox_dropbox_allow_personal_messages />&nbsp;$lang_dropbox_allow_personal_messages</td>
        </tr>
        <tr>		
                <td><input type='checkbox' name='block_username_change' value='1' $cbox_block_username_change />&nbsp;$lang_block_username_change</td>
        </tr>        
        <tr>		
                <td><input $id_enable_mobileapi type='checkbox' name='enable_mobileapi' value='1' $cbox_enable_mobileapi />&nbsp;$lang_enable_mobileapi</td>
        </tr>
        <tr>
                <td><input type='checkbox' name='enable_common_docs' value='1' $cbox_enable_common_docs />&nbsp;$langEnableCommonDocs</td>
        </tr>
        <tr>
                <td><input type='checkbox' name='enable_social_sharing_links' value='1' $cbox_enable_social_sharing_links />&nbsp;$langEnableSocialSharingLiks</td>
        </tr>
        <tr>
                <td>$langActionsExpireInterval&nbsp;<input type='text' name='actions_expire_interval' value='" . get_config('actions_expire_interval') . "' size='5' />&nbsp;($langMonthsUnit)</td>
        </tr>
        </table></fieldset>";

    $tool_content .= "<fieldset>
        <legend>$langDefaultQuota</legend>
        <table class='tbl' width='100%'>
	  <tr>
		<th class='left'><b>$langDocQuota</b></th>
		<td><input class='FormData_InputText' type='text' name='doc_quota' value='" . get_config('doc_quota') . "' size='5'/>&nbsp;(Mb)</td>
	  </tr>
	  <tr>
		<th class='left'><b>$langVideoQuota</b></th>
		<td><input class='FormData_InputText' type='text' name='video_quota' value='" . get_config('video_quota') . "' size='5' />&nbsp;(Mb)</td>
	  </tr>
	  <tr>
		<th class='left'><b>$langGroupQuota</b></th>
		<td><input class='FormData_InputText' type='text' name='group_quota' value='" . get_config('group_quota') . "' size='5' />&nbsp;(Mb)</td>
	  </tr>
	  <tr>
		<th class='left'><b>$langDropboxQuota</b></th>
		<td><input class='FormData_InputText' type='text' name='dropbox_quota' value='" . get_config('dropbox_quota') . "' size='5' />&nbsp;(Mb)</td>
	  </tr></table>
	  </fieldset>
	  <fieldset><legend>$langUploadWhitelist</legend>
	  <table class='tbl' width='100%'>
	  <tr>
	  <th class='left'>$langStudentUploadWhitelist</th>
	  <td><textarea rows='6' cols='60' name='student_upload_whitelist'>" . get_config('student_upload_whitelist') . "</textarea></td>
	  </tr>
	  <tr>
	  <th class='left'>$langTeacherUploadWhitelist</th>
	  <td><textarea rows='6' cols='60' name='teacher_upload_whitelist'>" . get_config('teacher_upload_whitelist') . "</textarea></td>
	  </tr>
	  </table>
	  </fieldset>";

    $cbox_disable_log_actions = get_config('disable_log_actions') ? 'checked' : '';
    $cbox_disable_log_course_actions = get_config('disable_log_course_actions') ? 'checked' : '';
    $cbox_disable_log_system_actions = get_config('disable_log_system_actions') ? 'checked' : '';
    $tool_content .= "<fieldset>
                <legend>$langLogActions</legend>
                <table class='tbl' width='100%'>                    
                <tr>
                      <td><input type='checkbox' name='disable_log_actions' value='1' $cbox_disable_log_actions />&nbsp;$lang_disable_log_actions</td>
                </tr>
                <tr>
                      <td><input type='checkbox' name='disable_log_course_actions' value='1' $cbox_disable_log_course_actions />&nbsp;$lang_disable_log_course_actions</td>
                </tr>
                <tr>
                      <td><input type='checkbox' name='disable_log_system_actions' value='1' $cbox_disable_log_system_actions />&nbsp;$lang_disable_log_system_actions</td>
                </tr>
                <tr>
                        <td>$langLogExpireInterval&nbsp;<input type='text' name='log_expire_interval' value='" . get_config('log_expire_interval') . "' size='5' />&nbsp;($langMonthsUnit)</td>
                </tr>
                <tr>
                        <td>$langLogPurgeInterval&nbsp;<input type='text' name='log_purge_interval' value='" . get_config('log_purge_interval') . "' size='5' />&nbsp;($langMonthsUnit)</td>
                </tr>
                </table>
                </fieldset>";

    $tool_content .= "<fieldset><legend>$langLoginFailCheck</legend>
          <table class='tbl' width='100%'>
          <tr>
          <td><input id='login_fail_check' type='checkbox' name='login_fail_check' value='1' $cbox_login_fail_check />&nbsp;$langEnableLoginFailCheck</td>
          </tr>
          <tr id='login_fail_threshold'>
          <th class='left'>$langLoginFailThreshold</th>
          <td><input class='FormData_InputText' type='text' name='login_fail_threshold' value='" . get_config('login_fail_threshold') . "' size='5' /></td>
          </tr>
          <tr id='login_fail_deny_interval'>
          <th class='left'>$langLoginFailDenyInterval</th>
          <td><input class='FormData_InputText' type='text' name='login_fail_deny_interval' value='" . get_config('login_fail_deny_interval') . "' size='5' />&nbsp;($langMinute)</td>
          </tr>
          <tr id='login_fail_forgive_interval'>
          <th class='left'>$langLoginFailForgiveInterval</th>
          <td><input class='FormData_InputText' type='text' name='login_fail_forgive_interval' value='" . get_config('login_fail_forgive_interval') . "' size='5' />&nbsp;($langHours)</td>
          </tr>
          </table>
          </fieldset>
	    <input type='submit' name='submit' value='$langModify'>
        </form>";
    // Display link to index.php
    $tool_content .= "<p align='right'><a href='index.php'>$langBack</a></p>";
    
    // Modal dialogs
    $tool_content .= modalConfirmation('confirmIndexDialog', 'confirmIndexLabel', $langConfirmEnableIndexTitle, $langConfirmEnableIndex, 'confirmIndexCancel', 'confirmIndexOk');
    $tool_content .= modalConfirmation('confirmMobileAPIDialog', 'confirmMobileAPILabel', $langConfirmEnableMobileAPITitle, $langConfirmEnableMobileAPI, 'confirmMobileAPICancel', 'confirmMobileAPIOk');
    
    // After restored values have been inserted into form then bring back
    // values from original config.php, so the rest of the page can be displayed correctly
    if (isset($_GET['restore']) && $_GET['restore'] == "yes") {
        @include('config/config.php');
    }
}

draw($tool_content, 3, null, $head_content);

function modalConfirmation($id, $labelId, $title, $body, $cancelId, $okId) {
    global $langCancel, $langOk;
    return <<<htmlEOF
<div class='modal fade' id='$id' tabindex='-1' role='dialog' aria-labelledby='$labelId' aria-hidden='true'>
    <div class='modal-dialog'>
        <div class='modal-content'>
            <div class='modal-header'>
                <h4 class='modal-title' id='$labelId'>$title</h4>
            </div>
            <div class='modal-body'><p>$body</p></div>
            <div class='modal-footer'>
                <button id='$cancelId' type='button' class='btn btn-default'>$langCancel</button>
                <button id='$okId' type='button' class='btn btn-primary'>$langOk</button>
            </div>
        </div>
    </div>
</div>
htmlEOF;
}
