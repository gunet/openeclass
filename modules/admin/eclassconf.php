<?php
/* ========================================================================
 * Open eClass 3.0
 * E-learning and Course Management System
 * ========================================================================
 * Copyright 2003-2012  Greek Universities Network - GUnet
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


/*===========================================================================
	eclassconf.php
	@last update: 31-05-2006 by Pitsiougas Vagelis
	@authors list: Karatzidis Stratos <kstratos@uom.gr>
		       Pitsiougas Vagelis <vagpits@uom.gr>
==============================================================================
        @Description: Change configuration file settings

 	This script allows the administrator to change all values in the config.php,
 	to make a backup of the orginal and restore values from backup config.php

 	The user can : - Change settings in config.php
 	               - Create a backup file of the original config.php
 	               - Restore values from backup config.php
                 - Return to course list

 	@Comments: The script is organised in three sections.

  1) Display values from config.php
  2) Restore values from backup config.php
  3) Save new config.php
  4) Create a backup file of config.php
  5) Display all on an HTML page

==============================================================================*/

// Check if user is administrator and if yes continue
// Othewise exit with appropriate message
$require_admin = true;
require_once '../../include/baseTheme.php';
$nameTools = $langEclassConf;
$navigation[] = array('url' => 'index.php', 'name' => $langAdmin);

load_js('jquery');
$head_content .= <<<EOF
<script type='text/javascript'>
$(document).ready(function() {

    $('#uown').click(function(event) {
    
        if (!this.checked)
            $('#town').attr('checked', false);
        
        $('#town').attr('disabled', !this.checked);
        
    });

});
</script>
EOF;

$available_themes = active_subdirs("$webDir/template", 'theme.html');

define('MONTHS', 30 * 24 * 60 * 60);

// Save new config.php
if (isset($_POST['submit']))  {
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
                        'block_username_change' => true,
                        'close_user_registration' => true,
                        'display_captcha' => true,
                        'insert_xml_metadata' => true,
                        'betacms' => true,
                        'enable_mobileapi' => true,
                        'doc_quota' => true,
                        'group_quota' => true,
                        'video_quota' => true,
                        'dropbox_quota' => true,
                        'max_glossary_terms' => true,
                        'theme' => true,
                        'alt_auth_student_req' => true,
                        'disable_eclass_stud_reg' => true,
                        'disable_eclass_prof_reg' => true,
                        'case_insensitive_usernames' => true,
                        'course_multidep' => true,
                        'user_multidep' => true,
                        'restrict_owndep' => true,
                        'restrict_teacher_owndep' => true,
                        'disable_log_user_actions' => true);

        register_posted_variables($config_vars, 'all', 'intval');
        $_SESSION['theme'] = $theme = $available_themes[$theme];
        
        // restrict_owndep and restrict_teacher_owndep are interdependent
        if ($GLOBALS['restrict_owndep'] == 0) {
            $GLOBALS['restrict_teacher_owndep'] = 0;
        }

        // update table `config`
        foreach ($config_vars as $varname => $what) {
                set_config($varname, $GLOBALS[$varname]);
        }

        // Display result message
        $tool_content .= "<p class='success'>".$langFileUpdatedSuccess."</p>";

        // Display link to go back to index.php
        $tool_content .= "<p class='right'><a href=\"index.php\">".$langBack."</a></p>";

} // end of if($submit)

// Display config.php edit form
else {
        $tool_content .= "<form action='$_SERVER[SCRIPT_NAME]' method='post'>
                <fieldset><legend>$langBasicCfgSetting</legend>
	<table class='tbl' width='100%'>
	<tr>
	  <th width='200' class='left'><b>\$urlServer:</b></th>
	  <td><input class='FormData_InputText' type='text' name='formurlServer' size='40' value='".q($urlServer)."'></td>
	</tr>
        <tr>
	  <th class='left'><b>\$phpMyAdminURL:</b></th>
	  <td><input class='FormData_InputText' type='text' name='formphpMyAdminURL' size='40' value='".q(get_config('phpMyAdminURL'))."'></td>
	</tr>
	<tr>
	  <th class='left'><b>\$phpSysInfoURL:</b></th>
	  <td><input class='FormData_InputText' type='text' name='formphpSysInfoURL' size='40' value='".q(get_config('phpSysInfoURL'))."'></td>
	</tr>
	<tr>
	  <th class='left'><b>\$emailAdministrator:</b></th>
	  <td><input class='FormData_InputText' type='text' name='formemailAdministrator' size='40' value='".q(get_config('email_sender'))."'></td>
	</tr>
	<tr>
	  <th class='left'><b>\$administratorName:</b></th>
	  <td><input class='FormData_InputText' type='text' name='formadministratorName' size='40' value='".q(get_config('admin_name'))."'></td>
	</tr>
	<tr>
	  <th class='left'><b>\$siteName:</b></th>
	  <td><input class='FormData_InputText' type='text' name='formsiteName' size='40' value='".q(get_config('site_name'))."'></td>
	</tr>
	<tr>
	  <th class='left'><b>\$postaddress:</b></th>
	      <td><textarea rows='3' cols='40' name='formpostaddress'>".q(get_config('postaddress'))."</textarea></td>
	</tr>
	<tr>
	  <th class='left'><b>\$telephone:</b></th>
	  <td><input class='FormData_InputText' type='text' name='formtelephone' size='40' value='".q(get_config('phone'))."'></td>
	</tr>
	<tr>
	  <th class='left'><b>\$fax:</b></th>
	  <td><input class='FormData_InputText' type='text' name='formfax' size='40' value='".q(get_config('fax'))."'></td>
	</tr>
	<tr>
	  <th class='left'><b>\$emailhelpdesk:</b></th>
	  <td><input class='FormData_InputText' type='text' name='formemailhelpdesk' size='40' value='".q(get_config('email_helpdesk'))."'></td>
	</tr>
	<tr>
	  <th class='left'><b>\$Institution:</b></th>
	  <td><input class='FormData_InputText' type='text' name='formInstitution' size='40' value='".$Institution."'></td>
	</tr>
	<tr>
	  <th class='left'><b>\$InstitutionUrl:</b></th>
	  <td><input class='FormData_InputText' type='text' name='formInstitutionUrl' size='40' value='".$InstitutionUrl."'></td>
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
	$cbox_disable_eclass_stud_reg = get_config('disable_eclass_stud_reg')? 'checked': '';
	$tool_content .= "
	<tr>
	  <th class='left'>disable_eclass_stud_reg</th>
	  <td><input type='checkbox' name='disable_eclass_stud_reg' value='1'
	    $cbox_disable_eclass_stud_reg>&nbsp;$langDisableEclassStudReg</td>
	</tr>";

	$cbox_disable_eclass_prof_reg = get_config('disable_eclass_prof_reg')? 'checked': '';
	$tool_content .= "
	<tr>
	  <th class='left'>disable_eclass_prof_reg</th>
	  <td><input type='checkbox' name='disable_eclass_prof_reg' value='1'
	    $cbox_disable_eclass_prof_reg>&nbsp;$langDisableEclassProfReg</td>
	</tr>";

        $cbox_close_user_registration = get_config('close_user_registration')? 'checked': '';
	$tool_content .= "
	<tr>
	  <th class='left'>close_user_registration</th>
          <td>
	  <input type=checkbox name='close_user_registration' value='1'
        $cbox_close_user_registration>&nbsp;$langViaReq</td>
	</tr>";

        $cbox_alt_auth_student_req = get_config('alt_auth_student_req')? 'checked': '';
	$tool_content .= "<tr>
	  <th class='left'>alt_auth_student_req</th>
	  <td><input type='checkbox' name='alt_auth_student_req' value='1'
	    $cbox_alt_auth_student_req>&nbsp;$langAltAuthStudentReq</td>
	</tr>";

        $cbox_case_insensitive_usernames = get_config('case_insensitive_usernames')? 'checked': '';
	$tool_content .= "<tr>
	  <th class='left'>case_insensitive_usernames</th>
	  <td><input type='checkbox' name='case_insensitive_usernames' value='1'
	    $cbox_case_insensitive_usernames>&nbsp;$langCaseInsensitiveUsername</td>
	</tr>";
        $cbox_email_required = get_config('email_required')?'checked':'';
	$cbox_email_verification_required = get_config('email_verification_required')?'checked':'';
        $tool_content .= "<tr>
		<th class='left'><b>email_required</b></th>
		<td><input type='checkbox' name='email_required' value='1' $cbox_email_required />&nbsp;$lang_email_required</td>
	  </tr>
	  <tr>
		<th class='left'><b>email_verification_required</b></th>
		<td><input type='checkbox' name='email_verification_required' value='1' $cbox_email_verification_required />&nbsp;$lang_email_verification_required</td>
	  </tr>";
          $cbox_am_required = get_config('am_required')?'checked':'';
          $tool_content .= "<tr>
		<th class='left'><b>am_required</b></th>
		<td><input type='checkbox' name='am_required' value='1' $cbox_am_required />&nbsp;$lang_am_required</td>
	  </tr>";
          $cbox_display_captcha = get_config('display_captcha')?'checked':'';
          $tool_content .= "<tr>
		<th class='left'><b>display_captcha</b></th>
		<td><input type='checkbox' name='display_captcha' value='1' $cbox_display_captcha />&nbsp;$lang_display_captcha</td>
	  </tr>";

	$tool_content .= "
        <tr>
        <td class='left'><b>account_duration</b></td>
        <td><input type='text' name='formdurationAccount' size='5' value='".intval(get_config('account_duration') / MONTHS)."'>&nbsp;&nbsp;$langUserDurationAccount&nbsp;($langIn $langMonthsUnit)</td></tr>
        <tr>
        <td class='left'><b>min_password_len</b></td>
        <td><input type='text' name='min_password_len' size='15' value='".intval(get_config('min_password_len'))."'>&nbsp;&nbsp;$langMinPasswordLen</td></tr>
        </table></fieldset>
        <fieldset><legend>$langEclassThemes</legend>
        <table class='tbl' width='100%'>
        <tr>
	  <th class='left'><b>$langMainLang</b></th>
	  <td><select name='default_language'>
	    <option value='el' $grSel>$langGreek</option>
	    <option value='en' $enSel>$langEnglish</option>
	  </select></td>
	</tr>";
	$langdirs = active_subdirs($webDir.'/lang', 'messages.inc.php');
	$sel = array();
	foreach ($language_codes as $langcode => $langname) {
		if (in_array($langcode, $langdirs)) {
			$loclangname = $langNameOfLang[$langname];
			$checked = in_array($langcode, $active_ui_languages)? ' checked': '';
			$sel[] = "<input type='checkbox' name='av_lang[]' value='$langcode'$checked>$loclangname";
		}
	}
	$tool_content .= "<tr><th class='left'>$langSupportedLanguages</th>
	    <td>" . implode(' ', $sel) . "</td></tr>";

	$tool_content .= "
	  <tr><td class='left'><b>$langThemes</b></td>
	    <td>" . selection($available_themes, 'theme',
		 array_search($theme, $available_themes)) . "</td></tr>";
        $cbox_dont_display_login_form = get_config('dont_display_login_form')?'checked':'';
        $tool_content .= "<tr>
		<th class='left'><b>dont_display_login_form</b></th>
		<td><input type='checkbox' name='dont_display_login_form' value='1' $cbox_dont_display_login_form />&nbsp;$lang_dont_display_login_form</td>
	  </tr>";
	$tool_content .= "</table></fieldset>";

        $cbox_dont_mail_unverified_mails = get_config('dont_mail_unverified_mails')?'checked':'';
        $cbox_email_from = get_config('email_from')?'checked':'';
        $tool_content .= "<fieldset>
        <legend>$langEmailSettings</legend>
        <table class='tbl' width='100%'>
        <tr>
		<th class='left'><b>dont_mail_unverified_mails</b></th>
		<td><input type='checkbox' name='dont_mail_unverified_mails' value='1' $cbox_dont_mail_unverified_mails />&nbsp;$lang_dont_mail_unverified_mails</td>
	  </tr>
          <tr>
		<th class='left'><b>email_from</b></th>
		<td><input type='checkbox' name='email_from' value='1' $cbox_email_from />&nbsp;$lang_email_from</td>
	  </tr>
        </table></fieldset>";


        $cbox_course_multidep = get_config('course_multidep')?'checked':'';
        $cbox_user_multidep = get_config('user_multidep')?'checked':'';
        $cbox_restrict_owndep = get_config('restrict_owndep') ? 'checked' : '';
        $cbox_restrict_teacher_owndep = get_config('restrict_teacher_owndep')?'checked':'';
        $town_dis = get_config('restrict_owndep') ? '' : 'disabled';

        $tool_content .= "<fieldset>
        <legend>$langCourseSettings</legend>
        <table class='tbl' width='100%'>
        <tr>
		<th class='left'><b>course_multidep</b></th>
		<td><input type='checkbox' name='course_multidep' value='1' $cbox_course_multidep />&nbsp;$lang_course_multidep</td>
	  </tr>
	  <tr>
		<th class='left'><b>user_multidep</b></th>
		<td><input type='checkbox' name='user_multidep' value='1' $cbox_user_multidep />&nbsp;$lang_user_multidep</td>
	  </tr>
	  <tr>
		<th class='left'><b>restrict_owndep</b></th>
		<td><input id='uown' type='checkbox' name='restrict_owndep' value='1' $cbox_restrict_owndep />&nbsp;$lang_restrict_owndep</td>
	  </tr>
	  <tr>
		<th class='left'><b>restrict_teacher_owndep</b></th>
		<td><input id='town' type='checkbox' name='restrict_teacher_owndep' value='1' $town_dis $cbox_restrict_teacher_owndep />&nbsp;$lang_restrict_teacher_owndep</td>
	  </tr>
        </table></fieldset>";

        $cbox_dropbox_allow_student_to_student = get_config('dropbox_allow_student_to_student')?'checked':'';
	$cbox_block_username_change = get_config('block_username_change')?'checked':'';
	$cbox_insert_xml_metadata = get_config('insert_xml_metadata')?'checked':'';
	$cbox_betacms = get_config('betacms')?'checked':'';
	$cbox_enable_mobileapi = get_config('enable_mobileapi')?'checked':'';
        $max_glossary_terms = get_config('max_glossary_terms');
        $cbox_disable_log_user_actions = get_config('disable_log_user_actions')?'checked':'';

        $tool_content .= "<fieldset>
        <legend>$langOtherOptions</legend>
        <table class='tbl' width='100%'>
          <tr>
                <th class='left'><b>disable_log_user_actions</b></th>
                <td><input type='checkbox' name='disable_log_user_actions' value='1' $cbox_disable_log_user_actions />&nbsp;$lang_disable_log_user_actions</td>
          </tr>
	  <tr>
		<th class='left'><b>max_glossary_terms</b></th>
		<td><input type='text' name='max_glossary_terms' value='$max_glossary_terms' size='5' />&nbsp;$lang_max_glossary_terms</td>
	  </tr>
	  <tr>
		<th class='left'><b>dropbox_allow_student_to_student</b></th>
		<td><input type='checkbox' name='dropbox_allow_student_to_student' value='1' $cbox_dropbox_allow_student_to_student />&nbsp;$lang_dropbox_allow_student_to_student</td>
	  </tr>
	  <tr>
		<th class='left'><b>block_username_change</b></th>
		<td><input type='checkbox' name='block_username_change' value='1' $cbox_block_username_change />&nbsp;$lang_block_username_change</td>
	  </tr>
	  <tr>
		<th class='left'><b>insert_xml_metadata</b></th>
		<td><input type='checkbox' name='insert_xml_metadata' value='1' $cbox_insert_xml_metadata />&nbsp;$lang_insert_xml_metadata</td>
	  </tr>
	  <tr>
		<th class='left'><b>betacms</b></th>
		<td><input type='checkbox' name='betacms' value='1' $cbox_betacms />&nbsp;$lang_betacms</td>
	  </tr>
	  <tr>
		<th class='left'><b>enable_mobileapi</b></th>
		<td><input type='checkbox' name='enable_mobileapi' value='1' $cbox_enable_mobileapi />&nbsp;$lang_enable_mobileapi</td>
	  </tr>
        </table></fieldset>";

        $tool_content .= "<fieldset>
        <legend>$langDefaultQuota</legend>
        <table class='tbl' width='100%'>
	  <tr>
		<th class='left'><b>$langDocQuota</b></th>
		<td><input class='FormData_InputText' type='text' name='doc_quota' value='".get_config('doc_quota')."' size='5'/>&nbsp;(Mb)</td>
	  </tr>
	  <tr>
		<th class='left'><b>$langVideoQuota</b></th>
		<td><input class='FormData_InputText' type='text' name='video_quota' value='".get_config('video_quota')."' size='5' />&nbsp;(Mb)</td>
	  </tr>
	  <tr>
		<th class='left'><b>$langGroupQuota</b></th>
		<td><input class='FormData_InputText' type='text' name='group_quota' value='".get_config('group_quota')."' size='5' />&nbsp;(Mb)</td>
	  </tr>
	  <tr>
		<th class='left'><b>$langDropboxQuota</b></th>
		<td><input class='FormData_InputText' type='text' name='dropbox_quota' value='".get_config('dropbox_quota')."' size='5' />&nbsp;(Mb)</td>
	  </tr></table>
	  </fieldset>
	  <fieldset><legend>$langUploadWhitelist</legend>
	  <table class='tbl' width='100%'>
	  <tr>
	  <th class='left'>$langStudentUploadWhitelist</th>
	  <td><textarea rows='6' cols='60' name='student_upload_whitelist'>".get_config('student_upload_whitelist')."</textarea></td>
	  </tr>
	  <tr>
	  <th class='left'>$langTeacherUploadWhitelist</th>
	  <td><textarea rows='6' cols='60' name='teacher_upload_whitelist'>".get_config('teacher_upload_whitelist')."</textarea></td>
	  </tr>
	  </table>
	  </fieldset>
	    <input type='submit' name='submit' value='$langModify'>
        </form>";
	// Display link to index.php
	$tool_content .= "<p align='right'><a href='index.php'>$langBack</a></p>";
	// After restored values have been inserted into form then bring back
	// values from original config.php, so the rest of the page can be displayed correctly
	if (isset($_GET['restore']) && $_GET['restore'] == "yes") {
		@include('config/config.php');
	}
}

draw($tool_content, 3, null, $head_content);

