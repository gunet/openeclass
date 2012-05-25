<?php
define('DEBUG_MYSQL', true);
session_start();
header('Content-Type: text/html; charset=UTF-8');
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

/*
 * Installation wizard
 *
 * @author Evelthon Prodromou <eprodromou@upnet.gr>
 * @version $Id$
 *
 * @abstract This is the installation wizard of eclass.
 *
 */

if(function_exists("date_default_timezone_set")) { // only valid if PHP > 5.1
	date_default_timezone_set("Europe/Athens");
}

$tool_content = "";
if (!isset($siteName)) $siteName = "";
if (!isset($InstitutionUrl)) $InstitutionUrl = "";
if (!isset($Institution)) $Institution = "";
// greek is the default language
if (!isset($lang)) {
	$_SESSION['lang'] = 'el';
}
// get installation language
if (isset($_POST['lang'])) {
	$_SESSION['lang'] = $_POST['lang'];
}

$lang = $_SESSION['lang'];
if ($lang == 'en') {
	$install_info_file = "install_info_en.php";
} else {
	$install_info_file = "install_info.php";
}

include "../include/lib/main.lib.php";
include "install_functions.php";
// include_messages
include("../lang/$lang/common.inc.php");
$extra_messages = "../config/$lang.inc.php";
if (file_exists($extra_messages)) {
        include $extra_messages;
} else {
        $extra_messages = false;
}
include("../lang/$lang/messages.inc.php");
if ($extra_messages) {
        include $extra_messages;
}

if (file_exists("../config/config.php")) {
	$tool_content .= "
<!DOCTYPE html PUBLIC '-//W3C//DTD XHTML 1.0 Transitional//EN' 'http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd'>
<html xmlns='http://www.w3.org/1999/xhtml'>
<head>
  <meta http-equiv='Content-Type' content='text/html; charset=UTF-8' />
  <title>$langWelcomeWizard</title>
  <link href='./install.css' rel='stylesheet' type='text/css' />
</head>
<body>

  <div class='install_container'>
  <p><img src='../template/classic/img/logo_openeclass.png' alt='logo' /></p>
  <div class='alert' align='center'>$langWarnConfig3!</div>
  <table width='600' align='center' cellpadding='5' cellspacing='5' class='tbl_alt'>
    <tr>
      <th><b>$langPossibleReasons</b></th>
      <th><b>$langTroubleshooting</b></th>
    </tr>
    <tr>
      <td>$langWarnConfig1</td>
      <td>$langWarnConfig2</td>
    </tr>
  </table>
</div>
</body>
</html>";
	exit($tool_content);
}

// Input fields that have already been included in the form, either as hidden or as normal inputs
$input_fields = array();

// step 0 initialise variables
if (isset($_POST['welcomeScreen'])) {
	$dbHostForm = 'localhost';
	$dbUsernameForm = 'root';
	$dbNameForm = 'eclass';
	$dbMyAdmin = '../admin/mysql/';
	$phpSysInfoURL = '../admin/sysinfo/';
	// extract the path to append to the url if it is not installed on the web root directory
	$urlAppendPath = str_replace('/install/index.php', '', $_SERVER['PHP_SELF']);
	$urlForm = "http://".$_SERVER['SERVER_NAME']."$urlAppendPath/";
	$pathForm = realpath('../') . '/';
	$emailForm = $_SERVER['SERVER_ADMIN'];
	$nameForm = $langDefaultAdminName;
	$surnameForm = $langDefaultAdminSurname;
	$loginForm = 'admin';
	$passForm = create_pass();
	$campusForm = 'Open eClass';
	$helpdeskForm = '+30 2xx xxxx xxx';
	$institutionForm = $langDefaultInstitutionName;
        $institutionUrlForm = 'http://www.gunet.gr/';
	$doc_quota = 200;
	$video_quota = 100;
	$group_quota = 100;
	$dropbox_quota = 100;
        $dbPassForm = $helpdeskmail = $faxForm = $postaddressForm = '';
	$email_required = $am_required = $dropbox_allow_student_to_student = $dont_display_login_form = '';
	$display_captcha = $block_username_change = $insert_xml_metadata = $betacms = $enable_mobileapi = '';
	$disable_eclass_stud_reg = $disable_eclass_prof_reg = $email_verification_required = $dont_mail_unverified_mails = '';
        $course_multidep = $user_multidep = '';
        $email_from = 1;
        $close_user_registration = '';
} else {
	register_posted_variables(array(
                'dbHostForm' => true,
                'dbUsernameForm' => true,
                'dbNameForm' => true,
                'dbMyAdmin' => true,
                'dbPassForm' => true,        
                'phpSysInfoURL' => true,
                'urlAppendPath' => true,
                'urlForm' => true,
                'pathForm' => true,
                'emailForm' => true,
                'nameForm' => true,
                'surnameForm' => true,
                'loginForm' => true,
                'passForm' => true,
                'campusForm' => true,
                'helpdeskForm' => true,
                'helpdeskmail' => true,
                'faxForm' => true,
                'postaddressForm' => true,
                'institutionForm' => true,
                'institutionUrlForm' => true,
		'email_required' => true,
		'email_verification_required' => true,
		'dont_mail_unverified_mails' => true,
                'email_from' => true,
		'am_required' => true,
		'dropbox_allow_student_to_student' => true,
		'dont_display_login_form' => true,
		'block_username_change' => true,
		'display_captcha' => true,
		'insert_xml_metadata' => true,
		'betacms' => true,
		'enable_mobileapi' => true,
		'disable_eclass_stud_reg' => true,
		'disable_eclass_prof_reg' => true,
                'close_user_registration' => true,
                'course_multidep' => true,
                'user_multidep' => true));
	
	register_posted_variables(array(
		'doc_quota' => true,
		'group_quota' => true,
		'dropbox_quota' => true,
		'video_quota' => true), 'all', 'intval');
}

$pathForm = str_replace("\\", '/', realpath($pathForm) . '/');

function hidden_vars($names)
{
        $out = '';
        foreach ($names as $name) {
                if (isset($GLOBALS[$name]) and
                    !isset($GLOBALS['input_fields'][$name])) {
                        $out .= "<input type='hidden' name='$name' value='" . q($GLOBALS[$name]) . "' />\n";
                }
        }
        return $out;
}

function checkbox_input($name)
{
        $GLOBALS['input_fields'][$name] = true;
        return "<input type='checkbox' name='$name' value='1'" .
               ($GLOBALS[$name]? ' checked="1"': '') . " />";
}

function text_input($name, $size)
{
        $GLOBALS['input_fields'][$name] = true;
        return "<input type='text' class='FormData_InputText' size='$size' name='$name' value='" .
                q($GLOBALS[$name]) . "' />";
}

function textarea_input($name, $rows, $cols)
{
        $GLOBALS['input_fields'][$name] = true;
        return "<textarea rows='$rows' cols='$cols' class='FormData_InputText' name='$name'>" .
               q($GLOBALS[$name]) . "</textarea>";
}

$all_vars = array('pathForm', 'urlAppendPath', 'dbHostForm', 'dbUsernameForm', 'dbNameForm', 'dbMyAdmin',
                  'dbPassForm', 'urlForm', 'emailForm', 'nameForm', 'surnameForm', 'loginForm',
                  'passForm', 'phpSysInfoURL', 'campusForm', 'helpdeskForm', 'helpdeskmail',
                  'institutionForm', 'institutionUrlForm', 'faxForm', 'postaddressForm',
		  'doc_quota', 'video_quota', 'group_quota', 'dropbox_quota',
                  'email_required', 'email_verification_required', 'dont_mail_unverified_mails', 'email_from', 'am_required', 
                  'dropbox_allow_student_to_student', 'dont_display_login_form', 'block_username_change', 'display_captcha',
		  'insert_xml_metadata', 'betacms', 'enable_mobileapi', 'disable_eclass_stud_reg', 
                  'disable_eclass_prof_reg', 'close_user_registration', 'course_multidep', 'user_multidep');

// step 2 license
if(isset($_REQUEST['install2']) OR isset($_REQUEST['back2']))
{
	$langStepTitle = $langLicence;
	$langStep = $langStep2;
	$_SESSION['step'] = 2;
	$tool_content .= "<form action='$_SERVER[PHP_SELF]?alreadyVisited=1' method='post'>
             <table width='100%' class='tbl'>
                   <tr><td>$langInfoLicence</td></tr>
                <tr><td><textarea cols='92' rows='15' class='FormData_InputText'>" .
	                file_get_contents('../info/license/gpl.txt') . "
                        </textarea></td></tr>
                <tr><td><img src='../template/classic/img/printer.png' alt='print' />
                        <a href='../info/license/gpl_print.txt'>$langPrintVers</a></td></tr>
                <tr><td class='right'>
                        <input type='submit' name='back1' value='&laquo; $langPreviousStep' />
                        <input type='submit' name='install3' value='$langAccept' /></td></tr>
             </table>" . hidden_vars($all_vars) . "</form>";
	draw($tool_content);
}

// step 3 mysql database settings
elseif(isset($_REQUEST['install3']) OR isset($_REQUEST['back3'])) {	
	$langStepTitle = $langDBSetting;
	$langStep = $langStep3;
	$_SESSION['step']=3;
	$tool_content .= "
	<div>$langDBSettingIntro</div>
	<br />
        <form action='$_SERVER[PHP_SELF]?alreadyVisited=1' method='post'>
	<table width='100%' class='tbl smaller'>
	<tr>
	  <th width='220' class='left'>$langdbhost</th>
	  <td>".text_input('dbHostForm', 25)."&nbsp;&nbsp;$langEG localhost</td>
	  </tr>
	<tr>
	<th class='left'>$langDBLogin</th>
	<td>".text_input('dbUsernameForm', 25)."&nbsp;&nbsp;$langEG root </td>
	</tr>
	<tr>
	<th class='left'>$langDBPassword</th>
	<td>".text_input('dbPassForm', 25)."</td>
	</tr>
	<tr>
	<th class='left'>$langMainDB</th>
	<td>".text_input('dbNameForm', 25)."&nbsp;&nbsp;($langNeedChangeDB)</td>
	</tr>
	<tr>
	<th class='left'>URL του phpMyAdmin</th>
	<td>".text_input('dbMyAdmin', 25)."&nbsp;&nbsp;$langNotNeedChange</td>
	</tr>
	<tr>
	<th class='left'>URL του System info</th>
	<td>".text_input('phpSysInfoURL', 25)."&nbsp;&nbsp;$langNotNeedChange</td>
	</tr>
	<tr>
	<td colspan='2' class='right'>
		<input type='submit' name='back2' value='&laquo; $langPreviousStep' />
		&nbsp;<input type='submit' name='install4' value='$langNextStep &raquo;' />
	</td>
	</tr>
	</table>
        <div class='right smaller'>(*) $langAllFieldsRequired</div>" .
        hidden_vars($all_vars) . "</form>";
	draw($tool_content);
}	 

// step 4 basic config settings
elseif(isset($_REQUEST['install4']) OR isset($_REQUEST['back4']))
{
	$langStepTitle = $langBasicCfgSetting;
	$langStep = $langStep4;
	$_SESSION['step']=4;
        if (empty($helpdeskmail)) {
                $helpdeskmail = '';
        }
	$tool_content .= "<div> $langWillWrite</div><br />
                <form action='$_SERVER[PHP_SELF]?alreadyVisited=1' method='post'>
                <table width='100%' class='tbl smaller'>
                <tr><th class='left' width='220'>$langSiteUrl</th>
                    <td>".text_input('urlForm', 40)."&nbsp;&nbsp;(*)</td></tr>
                <tr><th class='left'>$langLocalPath</th>
                    <td>".text_input('pathForm', 40)."&nbsp;&nbsp;(*)</td></tr>
                <tr><th class='left'>$langAdminName</th>
                    <td>".text_input('nameForm', 40)."</td></tr>
                <tr><th class='left'>$langAdminSurname</th>
                    <td>".text_input('surnameForm', 40)."</td></tr>
                <tr><th class='left'>$langAdminEmail</th>
                    <td>".text_input('emailForm', 40)."</td></tr>
                <tr><th class='left'>$langAdminLogin</th>
                    <td>".text_input('loginForm', 40)."</td></tr>
                <tr><th class='left'>$langAdminPass</th>
                    <td>".text_input('passForm', 40)."</td></tr>
                <tr><th class='left'>$langCampusName</th>
                    <td>".text_input('campusForm', 40)."</td></tr>
                <tr><th class='left'>$langHelpDeskPhone</th>
                    <td>".text_input('helpdeskForm', 40)."</td></tr>
                <tr><th class='left'>$langHelpDeskFax</th>
                    <td>".text_input('faxForm', 40)."</td></tr>
                <tr><th class='left'>$langHelpDeskEmail</th>
                    <td>".text_input('helpdeskmail', 40)."&nbsp;&nbsp;(**)</td></tr>
                <tr><th class='left'>$langInstituteShortName</th>
                    <td>".text_input('institutionForm', 40)."</td></tr>
                <tr><th class='left'>$langInstituteName</th>
                    <td>".text_input('institutionUrlForm', 40)."</td></tr>
                <tr><th class='left'>$langInstitutePostAddress</th>
                    <td>".textarea_input('postaddressForm', 3, 40)."</td></tr>
		<tr><th class='left'>$langDocQuota</th>
			<td>".text_input('doc_quota', 5)."&nbsp;(Mb)</td></tr>
		<tr><th class='left'>$langVideoQuota</th>
			<td>".text_input('video_quota', 5)."&nbsp;(Mb)</td></tr>
		<tr><th class='left'>$langGroupQuota</th>
			<td>".text_input('group_quota', 5)."&nbsp;(Mb)</td></tr>
		<tr><th class='left'>$langDropboxQuota</th>
			<td>".text_input('dropbox_quota', 5)."&nbsp;(Mb)</td></tr>
		<tr><th class='left'>$langViaReq</th>
			<td>".checkbox_input('close_user_registration')."</td></tr>                
		<tr><th class='left'>$langDisableEclassStudReg</th>
			<td>".checkbox_input('disable_eclass_stud_reg')."</td></tr>
		<tr><th class='left'>$langDisableEclassProfReg</th>
			<td>".checkbox_input('disable_eclass_prof_reg')."</td></tr>
	<tr><td colspan='2' class='right'>
	  <input type='submit' name='back3' value='&laquo; $langPreviousStep' />
	  <input type='submit' name='install5' value='$langNextStep &raquo;' />
	  <div class='smaller'>$langRequiredFields.</div>
	  <div class='smaller'>(**) $langWarnHelpDesk</div></td>
	</tr>
        </table>" . hidden_vars($all_vars) . "</form>";

	draw($tool_content);
}

// step 5 optional config settings
elseif(isset($_REQUEST['install5']) OR isset($_REQUEST['back5']))
{
	$langStepTitle = $langOptionalCfgSetting;
	$langStep = $langStep5;
	$_SESSION['step'] = 5;
	$tool_content .= "<div>$langWillWriteConfig</div><br />
        <form action='$_SERVER[PHP_SELF]?alreadyVisited=1' method='post'>
	<table width='100%' class='tbl smaller'>
	  <tr>
		<th class='left' width='550'><b>$lang_email_required</b></th>
		<td>".checkbox_input('email_required')."</td>
	  </tr>
	  <tr>
		<th class='left' width='550'><b>$lang_email_verification_required</b></th>
		<td>".checkbox_input('email_verification_required')."</td>
	  </tr>
	  <tr>
		<th class='left' width='550'><b>$lang_dont_mail_unverified_mails</b></th>
		<td>".checkbox_input('dont_mail_unverified_mails')."</td>
	  </tr>
          <tr>
		<th class='left' width='550'><b>$lang_email_from</b></th>
		<td>".checkbox_input('email_from')."</td>
	  </tr>
	  <tr>
		<th class='left'><b>$lang_am_required</b></th>
		<td>".checkbox_input('am_required')."</td>
	  </tr>
	  <tr>
		<th class='left'><b>$lang_dropbox_allow_student_to_student</b></th>
		<td>".checkbox_input('dropbox_allow_student_to_student')."</td>
	  </tr>
	  <tr>
		<th class='left'><b>$lang_dont_display_login_form</b></th>
		<td>".checkbox_input('dont_display_login_form')."</td>
	  </tr>
	  <tr>
		<th class='left'><b>$lang_block_username_change</b></th>
		<td>".checkbox_input('block_username_change')."</td>
	  </tr>
	  <tr>
		<th class='left'><b>$lang_display_captcha</b></th>
		<td>".checkbox_input('display_captcha')."</td>
	  </tr>
	  <tr>
		<th class='left'><b>$lang_insert_xml_metadata</b></th>
		<td>".checkbox_input('insert_xml_metadata')."</td>
	  </tr>
	  <tr>
		<th class='left'><b>$lang_betacms</b></th>
		<td>".checkbox_input('betacms')."</td>
	  </tr>
	  <tr>
		<th class='left'><b>$lang_enable_mobileapi</b></th>
		<td>".checkbox_input('enable_mobileapi')."</td>
	  </tr>
	  <tr>
		<th class='left'><b>$lang_course_multidep</b></th>
		<td>".checkbox_input('course_multidep')."</td>
	  </tr>
	  <tr>
		<th class='left'><b>$lang_user_multidep</b></th>
		<td>".checkbox_input('user_multidep')."</td>
	  </tr>
	  <tr><td colspan='2' class='right'>
	  <input type='submit' name='back4' value='&laquo; $langPreviousStep' />
	  <input type='submit' name='install6' value='$langNextStep &raquo;' />
	  </td>
	</tr>
	</table>" . hidden_vars($all_vars) . "</form>";
	draw($tool_content);
}

// step 6 last check before install
elseif(isset($_REQUEST['install6']))
{
	$langStepTitle = $langLastCheck;
	$langStep = $langStep6;
	$_SESSION['step'] = 6;
	if (!$close_user_registration) {
      		$mes_add = $langToReqOpen;
  	} else {
      		$mes_add = $langToReq;
  	}
	if (!$disable_eclass_stud_reg) {
		$disable_eclass_stud_reg_info = $langDisableEclassStudRegNo;
	} else {
		$disable_eclass_stud_reg_info = $langDisableEclassStudRegYes;
	}
	if (!$disable_eclass_prof_reg) {
		$disable_eclass_prof_reg_info = $langDisableEclassProfRegNo;
	} else {
		$disable_eclass_prof_reg_info = $langDisableEclassProfRegYes;
	}

	$tool_content .= "
        <form action='$_SERVER[PHP_SELF]?alreadyVisited=1' method='post'>
	<div>$langReviewSettings</div> <br />
		<table width='100%' class='tbl smaller'>
	<tr>
	<th class='left'>$langdbhost:</th>
	<td>$dbHostForm</td>
	</tr>
	<tr>
	<th class='left'>$langDBLogin:</th>
	<td>$dbUsernameForm</td>
	</tr>
	<tr>
	<th class='left'>$langMainDB: </th>
	<td>$dbNameForm</td>
	</tr>
	<tr>
	<th class='left'>PHPMyAdmin URL:</th>
	<td>$dbMyAdmin</td>
	</tr>
	<tr>
	<th class='left'>$langSiteUrl:</th>
	<td>$urlForm</td>
	</tr>
	<tr>
	<th class='left'>$langLocalPath:</th>
	<td>$pathForm</td>
	</tr>
	<tr>
	<th class='left'>$langAdminEmail:</th>
	<td>$emailForm</td>
	</tr>
	<tr>
	<th class='left'>$langAdminName:</th>
	<td>$nameForm</td>
	</tr>
	<tr>
	<th class='left'>$langAdminSurname:</th>
	<td>$surnameForm</td>
	</tr>
	<tr>
	<th class='left'>$langAdminLogin:</th>
	<td>$loginForm</td>
	</tr>
	<tr>
	<th class='left'>$langAdminPass:</th>
	<td>" . q($passForm) . "</td>
	</tr>
	<tr>
	<th class='left'>$langCampusName:</th>
	<td>$campusForm</td>
	</tr>
	<tr>
	<th class='left'>$langHelpDeskPhone: </th>
	<td>$helpdeskForm</td>
	</tr>
	<tr>
	<th class='left'>$langHelpDeskEmail:</th>
	<td>$helpdeskmail</td>
	</tr>
	<tr>
	<th class='left'>$langInstituteShortName:</th>
	<td>$institutionForm</td>
	</tr>
	<tr>
	<th class='left'>$langInstituteName:</th>
	<td>$institutionUrlForm</td>
	</tr>
	<tr>
	<th class='left'>$langInstitutePostAddress:</th>
	<td>" . nl2br(q($postaddressForm)) . "</td>
	</tr>
	<tr>
	<th class='left'>$langGroupStudentRegistrationType</th>
	<td>$mes_add</td>
	</tr>
	<tr>
	<th class='left'>$langDisableEclassStudRegType</th>
	<td>$disable_eclass_stud_reg_info</td>
	</tr>
	<tr>
	<th class='left'>$langDisableEclassProfRegType</th>
	<td>$disable_eclass_prof_reg_info</td>
	</tr>";
	$tool_content .= "<tr><td class='right'>&nbsp;</td>
	<td class='right'>
		<input type='submit' name='back5' value='&laquo; $langPreviousStep' />
		<input type='submit' name='install7' value='$langInstall &raquo;' />
	</td>
	</tr>
	</table>" . hidden_vars($all_vars) . "</form>";

	draw($tool_content);
}

// step 7 installation successful
elseif(isset($_REQUEST['install7']))
{
	// database creation
	$langStepTitle = $langInstallEnd;
	$langStep = $langStep7;
	$_SESSION['step']=7;
	$db = @mysql_connect($dbHostForm, $dbUsernameForm, autounquote($dbPassForm));
	if (mysql_errno() > 0) { // problem with server
		$no = mysql_errno();
		$msg = mysql_error();
		$tool_content .= "[".$no."] - ".$msg."
		<div class='alert1'>$langErrorMysql</div>
		<ul class='installBullet'>
		<li>$langdbhost: $dbHostForm</li>
		<li>$langDBLogin: $dbUsernameForm</li>
		<li>$langDBPassword: " . q($dbPassForm) . "</li>
		</ul>
		<p>$langBackStep3_2</p><br />
		<form action='$_SERVER[PHP_SELF]?alreadyVisited=1' method='post'>
		<input type='submit' name='install3' value='&lt; $langBackStep3' />"
		. hidden_vars($all_vars) .
		"</form>";
		draw($tool_content);
		exit();
	}
	$mysqlMainDb = $dbNameForm;
	// create main database
	require "install_db.php";
	// create config.php
	$fd=@fopen("../config/config.php", "w");
	if (!$fd) {
		$tool_content .= $langErrorConfig;
	} else {		
		$stringConfig='<?php
/* ========================================================
 * OpeneClass 2.5 configuration file
 * Automatically created by install on '.date('Y-m-d H:i').'
 * ======================================================== */

$urlServer = "'.$urlForm.'";
$urlAppend = "'.$urlAppendPath.'";
$webDir    = "'.$pathForm.'";

$mysqlServer = "'.$dbHostForm.'";
$mysqlUser = "'.$dbUsernameForm.'";
$mysqlPassword = '.autoquote($dbPassForm).';
$mysqlMainDb = "'.$mysqlMainDb.'";
$phpMyAdminURL = "'.$dbMyAdmin.'";
$phpSysInfoURL = "'.$phpSysInfoURL.'";
$emailAdministrator = '.autoquote($emailForm).';
$administratorName = '.autoquote($nameForm).';
$administratorSurname = '.autoquote($surnameForm).';
$siteName = '.autoquote($campusForm).';

$telephone = '.autoquote($helpdeskForm).';
$fax = '.autoquote($faxForm).';
$emailhelpdesk = '.autoquote($helpdeskmail).';

$language = "greek";

$Institution = '.autoquote($institutionForm).';
$InstitutionUrl = '.autoquote($institutionUrlForm).';
$postaddress = '.autoquote($postaddressForm).';

$persoIsActive = TRUE;
$durationAccount = "126144000";

define("UTF8", true);

$encryptedPasswd = true;
';
	// write to file
	fwrite($fd, $stringConfig);
	// message
	$tool_content .= "
	<div class='success'>$langInstallSuccess</div>
	
	<br />
	<div>$langProtect</div>
	<br /><br />
	</form>
	<form action='../'><input type='submit' value='$langEnterFirstTime' /></form>";
	draw($tool_content);
	}
}	

// step 1 requirements
elseif (isset($_REQUEST['install1']) || isset($_REQUEST['back1']))
{
	$langStepTitle = $langRequirements;
	$langStep = $langStep1;
	$_SESSION['step'] = 1;
	$configErrorExists = false;

        $tool_content .= "<form action='$_SERVER[PHP_SELF]?alreadyVisited=1' method='post'>";

	// create config, courses and video catalogs
        mkdir_or_error('config');
	mkdir_or_error('courses');
	mkdir_or_error('courses/temp');
	mkdir_or_error('courses/userimg');
        mkdir_or_error('video');
	
	if($configErrorExists) {
		$tool_content .= implode("<br />", $errorContent);
		$tool_content .= "</form>";
		draw($tool_content);
		exit();
	}

	$tool_content .= "
	<p class='sub_title1'>$langCheckReq</p>
	<ul class='installBullet'>
        <li><b>Webserver</b> <br /> <em>$langFoundIt ".$_SERVER['SERVER_SOFTWARE']."</em>)
        $langWithPHP (<em>$langFoundIt PHP ".phpversion()."</em>).";
	$tool_content .= "</li></ul>";
	$tool_content .= "<p class='sub_title1'>$langRequiredPHP</p>";
	$tool_content .= "<ul class='installBullet'>";
	warnIfExtNotLoaded('standard');
	warnIfExtNotLoaded('session');
	warnIfExtNotLoaded('mysql');
	warnIfExtNotLoaded('gd');
	warnIfExtNotLoaded('mbstring');
	warnIfExtNotLoaded('zlib');
	warnIfExtNotLoaded('pcre');
	$tool_content .= "</ul><p class='sub_title1'>$langOptionalPHP</p>";
	$tool_content .= "<ul class='installBullet'>";
	warnIfExtNotLoaded("ldap");
	$tool_content .= "</ul>";
        if (ini_get('register_globals')) { // check if register globals is Off     
                $tool_content .= "<div class='caution'>$langWarningInstall1</div>";
        }
        if (ini_get('short_open_tag')) { // check if short_open_tag is Off
                $tool_content .= "<div class='caution'>$langWarningInstall2</div>";
        }                
	$tool_content .= "
	<p class='sub_title1'>$langOtherReq</p>
	<ul class='installBullet'>
	<li>$langInstallBullet1</li>
	<li>$langInstallBullet3</li>
	</ul>
	<p class='sub_title1'>$langAddOnStreaming:</p>
	<ul class='installBullet'>
	<li>$langExpPhpMyAdmin</li></ul>
	<div class='info'>$langBeforeInstall1<a href='$install_info_file' target=_blank>$langInstallInstr</a>.
	<div class='smaller'>$langBeforeInstall2<a href='../README.txt' target=_blank>$langHere</a>.</div></div><br />
	<div class='right'><input type='submit' name='install2' value='$langNextStep &raquo;' /></div>" .
        hidden_vars($all_vars) . "</form>\n";
	draw($tool_content);
} else {
	$langLanguages = array(
		'greek' => 'Ελληνικά (el)',
		'english' => 'English (en)');

	$tool_content .= "<!DOCTYPE html PUBLIC '-//W3C//DTD XHTML 1.0 Transitional//EN' 'http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd'>
	<html>
	<head>
        <title>$langWelcomeWizard</title>
        <meta http-equiv='Content-Type' content='text/html; charset=UTF-8' />
        <link href='./install.css' rel='stylesheet' type='text/css' />
	</head>
	<body>
	<div class='install_container' align='center'>
        <table class='tbl_alt' width='400'>
        <tr><th colspan='2' align='center' ><div class='welcomeImg'></div></th></tr>
        <tr><td colspan='2'><div class='title'>$langWelcomeWizard</div>
               <div class='sub_title'>$langThisWizard</div>
               <ul class='installBullet'>
                  <li>$langWizardHelp1</li>
                  <li>$langWizardHelp2</li>
                  <li>$langWizardHelp3</li>
               </ul></td></tr>
        <tr>
          <th><div>$langChooseLang:</div> <form name='langform' action='$_SERVER[PHP_SELF]' method='post' class='form_field'>" .
                      selection($langLanguages, 'lang', $lang, 'onChange="document.langform.submit();"') .
                      "</form></th>
        </tr>
        <tr>
          <td colspan='2' align='right'><form action='$_SERVER[PHP_SELF]?alreadyVisited=1' method='post'>
            <input type='hidden' name='welcomeScreen' value='welcomeScreen' />
            <input type='submit' name='install1' value='$langNextStep &raquo;' />
          </form></td>
          </tr>
        </table>
	</div>
	</body>
	</html>";
	echo $tool_content;
}


// -----------------------
// functions
// -----------------------
function mkdir_or_error($dirname) {
                global $errorContent, $configErrorExists, $langWarningInstall3, 
                       $langWarnInstallNotice1, $langWarnInstallNotice2,
                       $install_info_file, $langHere;
                if (!is_dir('../' . $dirname)) {
                        if (@!mkdir('../' . $dirname, 0777)) {
                                $errorContent[] = sprintf("<p class='caution'>$langWarningInstall3 $langWarnInstallNotice1 <a href='$install_info_file'>$langHere</a> $langWarnInstallNotice2</p>", $dirname);
                                $configErrorExists = true;
                        }
                }
        }
