<?php
/* ========================================================================
 * Open eClass 2.4
 * E-learning and Course Management System
 * ========================================================================
 * Copyright 2003-2011  Greek Universities Network - GUnet
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
$require_admin = TRUE;
include '../../include/baseTheme.php';
$nameTools = $langEclassConf;
$navigation[] = array('url' => 'index.php', 'name' => $langAdmin);

$available_themes = active_subdirs("$webDir/template", 'theme.html');

// Save new config.php
if (isset($_POST['submit']))  {
	// Make config directory writable
	@chmod("../../config", 777);
	@chmod("../../config", 0777);
	// Create backup file
	if (isset($_POST['backupfile']) and $_POST['backupfile'] == "on") {
		// If a backup already exists delete it
		if (file_exists("../../config/config_backup.php"))
			unlink("../../config/config_backup.php");
		// Create the backup
		copy("../../config/config.php","../../config/config_backup.php");
	}
	// Open config.php empty
	$fd=@fopen("../../config/config.php", "w");
	if (!$fd) {
		$tool_content .= $langFileError;
        } else {

                if ($_POST['formcloseuserregistration'] == 'false') {
                        $user_reg = 'FALSE';
                } else {
                        $user_reg = 'TRUE';
                }
                if (defined('UTF8')) {
                        $utf8define = "define('UTF8', true);";
                }

                $active_lang_codes = array();
                if (isset($_POST['av_lang'])) { 
                        foreach ($_POST['av_lang'] as $langname => $langvalue) {
                                $active_lang_codes[] = autoquote($langvalue);
                        }
                }
                if (!count($active_lang_codes)) {
                        $active_lang_codes = array("'el'");
                }
                $string_active_ui_languages = 'array(' . implode(', ', $active_lang_codes) . ');';

                // Prepare config.php content
                $stringConfig='<?php
/*===========================================================================
 *   Open eClass 2.4
 *   E-learning and Course Management System
 *===========================================================================

 config.php automatically generated on '.date('c').'

 */

'.$utf8define.'
$urlServer	=	'.autoquote($_POST['formurlServer']).';
$urlAppend	=	'.autoquote($_POST['formurlAppend']).';
$webDir		=	"'.str_replace("\\","/",realpath($_POST['formwebDir'])."/").'" ;

$mysqlServer='.autoquote($_POST['formmysqlServer']).';
$mysqlUser='.autoquote($_POST['formmysqlUser']).';
$mysqlPassword= '.autoquote($_POST['formmysqlPassword']).';
$mysqlMainDb='.autoquote($_POST['formmysqlMainDb']).';
$phpMyAdminURL='.autoquote($_POST['formphpMyAdminURL']).';
$phpSysInfoURL='.autoquote($_POST['formphpSysInfoURL']).';
$emailAdministrator='.autoquote($_POST['formemailAdministrator']).';
$administratorName='.autoquote($_POST['formadministratorName']).';
$administratorSurname='.autoquote($_POST['formadministratorSurname']).';
$siteName='.autoquote($_POST['formsiteName']).';

$telephone='.autoquote($_POST['formtelephone']).';
$emailhelpdesk='.autoquote($_POST['formemailhelpdesk']).';
$Institution='.autoquote($_POST['formInstitution']).';
$InstitutionUrl='.autoquote($_POST['formInstitutionUrl']).';

// available: greek and english
$language = "'.$_POST['formlanguage'].'";

$postaddress = '.autoquote($_POST['formpostaddress']).';
$fax = '.autoquote($_POST['formfax']).';

$close_user_registration = '.$user_reg.';
$encryptedPasswd = "true";
$persoIsActive = TRUE;

$durationAccount = '.autoquote($_POST['formdurationAccount']).';
$active_ui_languages = '.$string_active_ui_languages."\n";

                // Save new config.php
                fwrite($fd, $stringConfig);

                $config_vars = array('email_required' => true,
                                     'am_required' => true,
                                     'dont_display_login_form' => true,
                                     'dropbox_allow_student_to_student' => true,
                                     'block_username_change' => true,
                                     'display_captcha' => true,
                                     'insert_xml_metadata' => true,
                                     'betacms' => true,
				     'doc_quota' => true,
				     'group_quota' => true,
				     'video_quota' => true,
                                     'dropbox_quota' => true,
                                     'theme' => true,
                                     'alt_auth_student_req' => true);

		register_posted_variables($config_vars, 'all', 'intval');
                $_SESSION['theme'] = $theme = $available_themes[$theme];

                foreach ($config_vars as $varname => $what) {
                        set_config($varname, $GLOBALS[$varname]);
                }
		
                // Display result message
                $tool_content .= "<p class='success'>".$langFileUpdatedSuccess."</p>";
        }
	// Display link to go back to index.php
	$tool_content .= "<p class='right'><a href=\"index.php\">".$langBack."</a></p>";

}
// Display config.php edit form
else {
	// Check if a backup file exists
	if (file_exists("../../config/config_backup.php")) {
  	// Give option to restore values from backup file
		$tool_content .= "<div id='operations_container'>
		<ul id='opslist'>
		<li><a href=\"eclassconf.php?restore=yes\">$langRestoredValues</a></li>
		</ul>
		</div>";
	}
	$titleextra = "config.php";
	// Check if restore has been selected
	if (isset($_GET['restore']) && $_GET['restore'] == "yes") {
		// Substitute variables with those from backup file
		$titleextra = " ($langRestoredValues)";
		@include("../../config/config_backup.php");
	}
	$tool_content .= "<fieldset><legend>$langFileEdit $titleextra</legend>";
	$tool_content .= "<form action=\"".$_SERVER['PHP_SELF']."\" method=\"post\">";
	$tool_content .= "
	<table class='tbl' width=\"100%\">
	<tbody>
	<tr>
	  <th width='200' class=\"left\"><b>\$urlServer:</b></th>
	  <td><input class=\"FormData_InputText\" type='text' name=\"formurlServer\" size='40' value=\"".$urlServer."\"></td>
	</tr>
	<tr>
	  <th class=\"left\"><b>\$urlAppend:</b></th>
	  <td><input class=\"FormData_InputText\" type='text' name=\"formurlAppend\" size='40' value=\"".$urlAppend."\"></td>
	</tr>
	<tr>
	  <th class=\"left\"><b>\$webDir:</b></th>
	  <td><input class=\"FormData_InputText\" type='text' name=\"formwebDir\" size='40' value=\"".$webDir."\"></td>
	</tr>
	<tr>
	  <th class=\"left\"><b>\$mysqlServer:</b></th>
	  <td><input class=\"FormData_InputText\" type='text' name=\"formmysqlServer\" size='40' value=\"".$mysqlServer."\"></td>
	</tr>
	<tr>
	  <th class=\"left\"><b>\$mysqlUser:</b></th>
	  <td><input class=\"FormData_InputText\" type='text' name=\"formmysqlUser\" size='40' value=\"".$mysqlUser."\"></td>
	</tr>
	<tr>
	  <th class=\"left\"><b>\$mysqlPassword:</b></th>
	  <td><input class=\"FormData_InputText\" type=\"password\" name=\"formmysqlPassword\" size='40' value=\"".$mysqlPassword."\"></td>
	</tr>
	<tr>
	  <th class=\"left\"><b>\$mysqlMainDb:</b></th>
	  <td><input class=\"FormData_InputText\" type='text' name=\"formmysqlMainDb\" size='40' value=\"".$mysqlMainDb."\"></td>
	</tr>";
	      $tool_content .= "  <tr>
	  <th class=\"left\"><b>\$phpMyAdminURL:</b></th>
	  <td><input class=\"FormData_InputText\" type='text' name=\"formphpMyAdminURL\" size='40' value=\"".$phpMyAdminURL."\"></td>
	</tr>
	<tr>
	  <th class=\"left\"><b>\$phpSysInfoURL:</b></th>
	  <td><input class=\"FormData_InputText\" type='text' name=\"formphpSysInfoURL\" size='40' value=\"".$phpSysInfoURL."\"></td>
	</tr>
	<tr>
	  <th class=\"left\"><b>\$emailAdministrator:</b></th>
	  <td><input class=\"FormData_InputText\" type='text' name=\"formemailAdministrator\" size='40' value=\"".$emailAdministrator."\"></td>
	</tr>
	<tr>
	  <th class=\"left\"><b>\$administratorName:</b></th>
	  <td><input class=\"FormData_InputText\" type='text' name=\"formadministratorName\" size='40' value=\"".$administratorName."\"></td>
	</tr>
	<tr>
	  <th class=\"left\"><b>\$administratorSurname:</b></th>
	  <td><input class=\"FormData_InputText\" type='text' name=\"formadministratorSurname\" size='40' value=\"".$administratorSurname."\"></td>
	</tr>
	<tr>
	  <th class=\"left\"><b>\$siteName:</b></th>
	  <td><input class=\"FormData_InputText\" type='text' name=\"formsiteName\" size='40' value=\"".$siteName."\"></td>
	</tr>
	<tr>
	  <th class=\"left\"><b>\$postaddress:</b></th>
	      <td><textarea rows='3' cols='40' name='formpostaddress'>$postaddress</textarea></td>
	</tr>
	<tr>
	  <th class=\"left\"><b>\$telephone:</b></th>
	  <td><input class=\"FormData_InputText\" type='text' name=\"formtelephone\" size='40' value=\"".$telephone."\"></td>
	</tr>
	<tr>
	  <th class=\"left\"><b>\$fax:</b></th>
	  <td><input class=\"FormData_InputText\" type='text' name=\"formfax\" size='40' value=\"".$fax."\"></td>
	</tr>
	<tr>
	  <th class=\"left\"><b>\$emailhelpdesk:</b></th>
	  <td><input class=\"FormData_InputText\" type='text' name=\"formemailhelpdesk\" size='40' value=\"".$emailhelpdesk."\"></td>
	</tr>
	<tr>
	  <th class=\"left\"><b>\$Institution:</b></th>
	  <td><input class=\"FormData_InputText\" type='text' name=\"formInstitution\" size='40' value=\"".$Institution."\"></td>
	</tr>
	<tr>
	  <th class=\"left\"><b>\$InstitutionUrl:</b></th>
	  <td><input class=\"FormData_InputText\" type='text' name=\"formInstitutionUrl\" size='40' value=\"".$InstitutionUrl."\"></td>
	</tr>";
	if ($language=="greek") {
		$grSel = "selected";
		$enSel = "";
	} else {
		$grSel = "";
		$enSel = "selected";
	}
	$tool_content .= "
	<tr>
	  <th class='left'><b>\$language:</b></th>
	  <td><select name='formlanguage'>
	    <option value='greek' ".$grSel.">greek</option>
	    <option value='english' ".$enSel.">english</option>
	  </select></td>
	</tr>";

        if ($close_user_registration=="true") {
                $close_user_registrationSelTrue = "selected";
                $close_user_registrationSelFalse = "";
        } else {
                $close_user_registrationSelTrue = "";
                $close_user_registrationSelFalse = "selected";
        }
        $cbox_alt_auth_student_req = get_config('alt_auth_student_req')? 'checked': '';
	$tool_content .= "
	  <tr>
	    <th class='left'>\$close_user_registration:</th>
	    <td><select name='formcloseuserregistration'>
	      <option value='true' ".$close_user_registrationSelTrue.">true</option>
	      <option value='false' ".$close_user_registrationSelFalse.">false</option>
	    </select>&nbsp;&nbsp;$langViaReq</td>
          </tr>
          <tr>
            <th class='left'>alt_auth_student_req</th>
            <td><input type='checkbox' name='alt_auth_student_req' value='1'
                       $cbox_alt_auth_student_req>&nbsp;$langAltAuthStudentReq</td>
          </tr>";

        $langdirs = active_subdirs($webDir.'modules/lang', 'messages.inc.php');
        $sel = array();
        foreach ($language_codes as $langcode => $langname) {
                if (in_array($langname, $langdirs)) {
                        $loclangname = $langNameOfLang[$langname];
                        $checked = in_array($langcode, $active_ui_languages)? ' checked': '';
                        $sel[] = "<input type='checkbox' name='av_lang[]' value='$langcode'$checked>$loclangname";
                }
        }
        $tool_content .= "
          <tr><th class='left'>$langSupportedLanguages</th>
              <td>" . implode(' ', $sel) . "</td></tr>
	  <tr>
	    <th class='left'><b>\$durationAccount:</b></th>
            <td><input type='text' name='formdurationAccount' size='15' value='$durationAccount'>&nbsp;&nbsp;$langUserDurationAccount</td></tr>
          <tr><th class='left'><b>Theme:</b></th>
              <td>" . selection($available_themes, 'theme',
                                array_search($theme, $available_themes)) . "</td></tr>";
	
	$cbox_email_required = get_config('email_required')?'checked':'';
	$cbox_am_required = get_config('email_required')?'checked':'';
	$cbox_dont_display_login_form = get_config('dont_display_login_form')?'checked':'';
	$cbox_dropbox_allow_student_to_student = get_config('dropbox_allow_student_to_student')?'checked':'';
	$cbox_block_username_change = get_config('block_username_change')?'checked':'';
	$cbox_display_captcha = get_config('display_captcha')?'checked':'';
	$cbox_insert_xml_metadata = get_config('insert_xml_metadata')?'checked':'';
	$cbox_betacms = get_config('betacms')?'checked':'';
	
	$tool_content .= "
	  <tr>
	    <th class=\"left\"><b>\$encryptedPasswd:</b></th>
	    <td><input type=\"checkbox\" checked disabled> ".$langencryptedPasswd."</td>
	  </tr>
	  <tr>
		<th class='left'><b>email_required</b></th>
		<td><input type='checkbox' name='email_required' value='1' $cbox_email_required />&nbsp;$lang_email_required</td>
	  </tr>
	  <tr>
		<th class='left'><b>am_required</b></th>
		<td><input type='checkbox' name='am_required' value='1' $cbox_am_required />&nbsp;$lang_am_required</td>
	  </tr>
	  <tr>
		<th class='left'><b>dropbox_allow_student_to_student</b></th>
		<td><input type='checkbox' name='dropbox_allow_student_to_student' value='1' $cbox_dropbox_allow_student_to_student />&nbsp;$lang_dropbox_allow_student_to_student</td>
	  </tr>
	  <tr>
		<th class='left'><b>dont_display_login_form</b></th>
		<td><input type='checkbox' name='dont_display_login_form' value='1' $cbox_dont_display_login_form />&nbsp;$lang_dont_display_login_form</td>
	  </tr>
	  <tr>
		<th class='left'><b>block_username_change</b></th>
		<td><input type='checkbox' name='block_username_change' value='1' $cbox_block_username_change />&nbsp;$lang_block_username_change</td>
	  </tr>
	  <tr>
		<th class='left'><b>display_captcha</b></th>
		<td><input type='checkbox' name='display_captcha' value='1' $cbox_display_captcha />&nbsp;$lang_display_captcha</td>
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
	  </tr>
	  <tr><td colspan='2'><hr></td></tr>
	  <tr>
	    <th class='left'>$langReplaceBackupFile</th>
	    <td><input type='checkbox' name='backupfile' checked></td>
	  </tr>
	  <tr>
	    <th class=\"left\">&nbsp;</th>
	    <td class='right'><input type='submit' name='submit' value='$langModify'></td>
	  </tr>
	  </tbody>
	  </table>
	  </form></fieldset>";
	// Display link to index.php
	$tool_content .= "<p align='right'><a href='index.php'>$langBack</a></p>";
	// After restored values have been inserted into form then bring back
	// values from original config.php, so the rest of the page can be played correctly
	if (isset($_GET['restore']) && $_GET['restore'] == "yes") {
		@include("../../config/config.php");
	}
}

draw($tool_content, 3);

// Return a list of all subdirectories of $base which contain a file named $filename
function active_subdirs($base, $filename)
{
        $dir = opendir($base);
        $out = array();
        while (($f = readdir($dir)) !== false) {
                if (is_dir($base . '/' . $f) and $f != '.' and $f != '..' and
                    file_exists($base . '/' . $f . '/' . $filename)) {
                        $out[] = $f;
                }
        }
        closedir($dir);
        return $out;
}
