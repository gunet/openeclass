<?php session_start();
header('Content-Type: text/html; charset=UTF-8');
/*========================================================================
*   Open eClass 2.3
*   E-learning and Course Management System
* ========================================================================
*  Copyright(c) 2003-2010  Greek Universities Network - GUnet
*  A full copyright notice can be read in "/info/copyright.txt".
*
*  Developers Group:	Costas Tsibanis <k.tsibanis@noc.uoa.gr>
*			Yannis Exidaridis <jexi@noc.uoa.gr>
*			Alexandros Diamantidis <adia@noc.uoa.gr>
*			Tilemachos Raptis <traptis@noc.uoa.gr>
*
*  For a full list of contributors, see "credits.txt".
*
*  Open eClass is an open platform distributed in the hope that it will
*  be useful (without any warranty), under the terms of the GNU (General
*  Public License) as published by the Free Software Foundation.
*  The full license can be read in "/info/license/license_gpl.txt".
*
*  Contact address: 	GUnet Asynchronous eLearning Group,
*  			Network Operations Center, University of Athens,
*  			Panepistimiopolis Ilissia, 15784, Athens, Greece
*  			eMail: info@openeclass.org
* =========================================================================*/
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
	$_SESSION['lang'] = 'greek';
}
// get installation language
if (isset($_POST['lang'])) {
	$_SESSION['lang'] = $_POST['lang'];
}

$lang = $_SESSION['lang'];

include "../include/lib/main.lib.php";
include "install_functions.php";
if ($lang == 'english') {
	$install_info_file = "install_info_en.php";
} else {
	$install_info_file = "install_info.php";
}
// include_messages
include("../modules/lang/$lang/common.inc.php");
$extra_messages = "../config/$lang.inc.php";
if (file_exists($extra_messages)) {
        include $extra_messages;
} else {
        $extra_messages = false;
}
include("../modules/lang/$lang/messages.inc.php");
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
      <th><b>Πιθανοί λόγοι</b></th>
      <th><b>Αντιμετώπιση</b></th>
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
	$nameForm = 'Διαχειριστής';
	$surnameForm = 'Πλατφόρμας';
	$loginForm = 'admin';
	$passForm = create_pass();
	$campusForm = 'Open eClass';
	$helpdeskForm = '+30 2xx xxxx xxx';
	$institutionForm = 'Ακαδημαϊκό Διαδίκτυο GUNet ';
        $institutionUrlForm = 'http://www.gunet.gr/';
        $reguser = $dbPassForm = $helpdeskmail = $faxForm = $postaddressForm = '';
	$email_required = $am_required = $dropbox_allow_student_to_student = $dont_display_login_form = '';
	$block_username_change = $betacms = '';
} else {
       register_posted_variables(array(
                'dbHostForm' => true,
                'dbUsernameForm' => true,
                'dbNameForm' => true,
                'dbMyAdmin' => true,
                'dbPassForm' => true,
                'reguser' => true,
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
		'am_required' => true,
		'dropbox_allow_student_to_student' => true,
		'dont_display_login_form' => true,
		'block_username_change' => true,
		'betacms' => true), 'all');
}

if (isset($_GET['alreadyVisited'])) {
	$tool_content .= "<form action='$_SERVER[PHP_SELF]?alreadyVisited=1' method='post'>";
	$tool_content .= "
	<input type='hidden' name='urlAppendPath' value='$urlAppendPath' />
	<input type='hidden' name='pathForm' value='".str_replace("\\", "/", realpath($pathForm) . "/") . "' />
	<input type='hidden' name='dbHostForm' value='$dbHostForm' />
	<input type='hidden' name='dbUsernameForm' value='$dbUsernameForm' />
	<input type='hidden' name='dbNameForm' value='$dbNameForm' />
	<input type='hidden' name='dbMyAdmin' value='$dbMyAdmin' />
	<input type='hidden' name='dbPassForm' value='" . q($dbPassForm) . "' />
	<input type='hidden' name='urlForm' value='$urlForm' />
	<input type='hidden' name='emailForm' value='$emailForm' />
	<input type='hidden' name='nameForm' value='$nameForm' />
	<input type='hidden' name='surnameForm' value='$surnameForm' />
	<input type='hidden' name='loginForm' value='$loginForm' />
	<input type='hidden' name='passForm' value='" . q($passForm) . "' />
	<input type='hidden' name='phpSysInfoURL' value='$phpSysInfoURL' />
	<input type='hidden' name='campusForm' value='$campusForm' />
	<input type='hidden' name='helpdeskForm' value='$helpdeskForm' />
	<input type='hidden' name='helpdeskmail' value='$helpdeskmail' />
	<input type='hidden' name='institutionForm' value='$institutionForm' />
	<input type='hidden' name='institutionUrlForm' value='$institutionUrlForm' />
	<input type='hidden' name='faxForm' value='$faxForm' />
	<input type='hidden' name='postaddressForm' value='$postaddressForm' />
	<input type='hidden' name='reguser' value='$reguser' />
	<input type='hidden' name='email_required'  value='$email_required' />
	<input type='hidden' name='am_required' value='$am_required' />
	<input type='hidden' name='dropbox_allow_student_to_student' value='$dropbox_allow_student_to_student' />
	<input type='hidden' name='dont_display_login_form' value='$dont_display_login_form' /> 
	<input type='hidden' name='block_username_change' value='$block_username_change' />
	<input type='hidden' name='betacms' value='$betacms' />";
}

// step 2 license
if(isset($_REQUEST['install2']) OR isset($_REQUEST['back2']))
{
	$langStepTitle = $langLicence;
	$langStep = $langStep2;
	$_SESSION['step'] = 2;
	$tool_content .= "<form action='$_SERVER[PHP_SELF]?alreadyVisited=1' method='post'>
	<table width='100%' class='tbl'>
	<tr>
	<td>$langInfoLicence</td>
	</tr>
	<tr>
	<td><textarea cols='92' rows='15' class='FormData_InputText'>";
	$tool_content .= file_get_contents('../info/license/gpl.txt');
	$tool_content .= "</textarea></td>
	</tr>
		<tr>
	<td><img src='../template/classic/img/printer.png' alt='print' /> <a href='../info/license/gpl_print.txt'>$langPrintVers</a></td>
	</tr>
	<tr>
	<td class='right'>
	  <input type='submit' name='back1' value='&laquo; $langPreviousStep' />
	  <input type='submit' name='install3' value='$langAccept' />
	  </td></tr>
	</table>
	</form>";
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
	<table width='100%' class='tbl smaller'>
	<tr>
	  <th width='220' class='left'>$langdbhost</th>
	  <td><input type='text' class='FormData_InputText' size='25' name='dbHostForm' value='$dbHostForm' />&nbsp;&nbsp;$langEG localhost</td>
	  </tr>
	<tr>
	<th class='left'>$langDBLogin</th>
	<td><input type='text' class='FormData_InputText' size='25' name='dbUsernameForm' value='$dbUsernameForm' />&nbsp;&nbsp;$langEG root </td>
	</tr>
	<tr>
	<th class='left'>$langDBPassword</th>
	<td><input type='text' class='FormData_InputText' size='25' name='dbPassForm' value='" . q($dbPassForm) . "' />&nbsp;&nbsp;$langEG ".create_pass()."</td>
	</tr>
	<tr>
	<th class='left'>$langMainDB</th>
	<td><input type='text' class='FormData_InputText' size='25' name='dbNameForm' value='$dbNameForm' />&nbsp;&nbsp;($langNeedChangeDB)</td>
	</tr>
	<tr>
	<th class='left'>URL του phpMyAdmin</th>
	<td><input type='text' class='FormData_InputText' size='25' name='dbMyAdmin' value='$dbMyAdmin' />&nbsp;&nbsp;$langNotNeedChange</td>
	</tr>
	<tr>
	<th class='left'>URL του System info</th>
	<td><input type='text' class='FormData_InputText' size='25' name='phpSysInfoURL' value='$phpSysInfoURL' />&nbsp;&nbsp;$langNotNeedChange</td>
	</tr>
	<tr>
	<td colspan='2' class='right'>
		<input type='submit' name='back2' value='&laquo; $langPreviousStep' />
		&nbsp;<input type='submit' name='install4' value='$langNextStep &raquo;' />
	</td>
	</tr>
	</table>
	<div class='right smaller'>(*) $langAllFieldsRequired</div>
	</form>";
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
	<table width='100%' class='tbl smaller'>
	<tr>
	<th class='left' width='220'>$langSiteUrl</th>
	<td><input type='text' class='FormData_InputText' size='40' name='urlForm' value='$urlForm' />&nbsp;&nbsp;(*)</td>
	</tr>
	<tr>
	<th class='left'>$langLocalPath</th>
	<td><input type='text' size='40' class='FormData_InputText' name='pathForm' value='" . realpath($pathForm) . "/' />&nbsp;&nbsp;(*)</td>
	</tr>
	<tr>
	<th class='left'>$langAdminName</th>
	<td><input type='text' class='FormData_InputText' size='40' name='nameForm' value='$nameForm' /></td>
	</tr>
	<tr>
	<th class='left'>$langAdminSurname</th>
	<td><input type='text' class='FormData_InputText' size='40' name='surnameForm' value='$surnameForm' /></td>
	</tr>
	<tr>
	<th class='left'>$langAdminEmail</th>
	<td><input type=text class='FormData_InputText' size=40 name='emailForm' value='$emailForm' /></td>
	</tr>
	<tr>
	<th class='left'>$langAdminLogin</th>
	<td><input type='text' class='FormData_InputText' size='40' name='loginForm' value='$loginForm' /></td>
	</tr>
	<tr>
	<th class='left'>$langAdminPass</th>
	<td><input type='text' class='FormData_InputText' size='40' name='passForm' value='" . q($passForm) . "'/></td>
	</tr>
	<tr>
	<th class='left'>$langCampusName</th>
	<td><input type='text' class='FormData_InputText' size='40' name='campusForm' value='$campusForm' /><td>
	</tr>
	<tr>
	<th class='left'>$langHelpDeskPhone</th>
	<td><input type='text' class='FormData_InputText' size='40' name='helpdeskForm' value='$helpdeskForm' /></td>
	</tr>
	<tr>
	<th class='left'>$langHelpDeskFax</th>
	<td><input type='text' class='FormData_InputText' size='40' name='faxForm' value='$faxForm' /></td>
	</tr>
	<tr>
	<th class='left'>$langHelpDeskEmail</th>
	<td><input type='text' class='FormData_InputText' size='40' name='helpdeskmail' value='$helpdeskmail' />&nbsp;&nbsp;(**)</td>
	</tr>
	<tr>
	<th class='left'>$langInstituteShortName</th>
	<td><input type=text class='FormData_InputText' size='40' name='institutionForm' value='$institutionForm' /></td>
	</tr>
	<tr>
	<th class='left'>$langInstituteName</th>
	<td><input type='text' class='FormData_InputText' size='40' name='institutionUrlForm' value='$institutionUrlForm' /></td>
	</tr>
	<tr>
	<th class='left'>$langInstitutePostAddress</th>
	<td><textarea rows='3' class='FormData_InputText' cols='40' name='postaddressForm'>" . q($postaddressForm) . "</textarea></td>
	</tr>
	<tr>
	<th class='left'>$langViaReq</th>
	<td><input type='checkbox' name='reguser' /></td>
	</tr>";
	$tool_content .= "<tr><td colspan='2' class='right'>
	  <input type='submit' name='back3' value='&laquo; $langPreviousStep' />
	  <input type='submit' name='install5' value='$langNextStep &raquo;' />
	  <div class='smaller'>$langRequiredFields.</div>
	  <div class='smaller'>(**) $langWarnHelpDesk</div></td>
	</tr>
	</table>
	</form>";
	draw($tool_content);
}

// step 5 optional config settings
elseif(isset($_REQUEST['install5']) OR isset($_REQUEST['back5']))
{
	$langStepTitle = $langOptionalCfgSetting;
	$langStep = $langStep5;
	$_SESSION['step'] = 5;
	$tool_content .= "<div>$langWillWriteConfig</div><br />
	<table width='100%' class='tbl smaller'>
	  <tr>
		<th class='left' width='550'><b>$lang_email_required</b></th>
		<td><input type='checkbox' name='email_required' /></td>
	  </tr>
	  <tr>
		<th class='left'><b>$lang_am_required</b></th>
		<td><input type='checkbox' name='am_required' /></td>
	  </tr>
	  <tr>
		<th class='left'><b>$lang_dropbox_allow_student_to_student</b></th>
		<td><input type='checkbox' name='dropbox_allow_student_to_student' /></td>
	  </tr>
	  <tr>
		<th class='left'><b>$lang_dont_display_login_form</b></th>
		<td><input type='checkbox' name='dont_display_login_form' /></td>
	  </tr>
	  <tr>
		<th class='left'><b>$lang_block_username_change</b></th>
		<td><input type='checkbox' name='block_username_change' /></td>
	  </tr>
	  <tr>
		<th class='left'><b>$lang_betacms</b></th>
		<td><input type='checkbox' name='betacms' /></td>
	  </tr>";
	$tool_content .= "<tr><td colspan='2' class='right'>
	  <input type='submit' name='back4' value='&laquo; $langPreviousStep' />
	  <input type='submit' name='install6' value='$langNextStep &raquo;' />
	  </td>
	</tr>
	</table>
	</form>";
	draw($tool_content);
}

// step 6 last check before install
elseif(isset($_REQUEST['install6']))
{
	$pathForm = str_replace("\\\\", "/", $pathForm);
	$langStepTitle = $langLastCheck;
	$langStep = $langStep6;
	$_SESSION['step'] = 6;
	if (!$reguser) {
      		$mes_add ="";
  	} else {
      		$mes_add = "<br />$langToReq<br />";
  	}

	$tool_content .= "
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
	</tr>";
	$tool_content .= "<tr><td class='right'>&nbsp;</td>
	<td class='right'>
		<input type='submit' name='back5' value='&laquo; $langPreviousStep' />
		<input type='submit' name='install7' value='$langInstall &raquo;' />
	</td>
	</tr>
	</table>
	</form>";
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
		$tool_content .= "
		<table width='99%'>
		<thead>
		<tr>
		<td><div align='center'><img style='border:0px;' src='../template/classic/img/caution_alert.gif' title='caution-alert'></div></td>
		</tr>
		<tr>
		<td>
		<div align='center'><h4>[".$no."] - ".$msg."</div></h4>
		<p>$langErrorMysql</p>
		<ul class='installBullet'>
		<li>$langdbhost: $dbHostForm</li>
		<li>$langDBLogin: $dbUsernameForm</li>
		<li>$langDBPassword: " . q($dbPassForm) . "</li>
		</ul>
		<p>$langBackStep3_2</p></td>
		</td>
		</tr>
		</thead>
		</table>
		<input type='submit' name='install3' value='&lt; $langBackStep3' /></form>";
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
		$user_registration = $reguser? 'TRUE': 'FALSE';
		$stringConfig='<?php
/* ========================================================
 * OpeneClass 2.4 configuration file
 * Automatically created by install on '.date('Y-m-d H:i').'
 * ======================================================== */

$urlServer = "'.$urlForm.'";
$urlAppend = "'.$urlAppendPath.'";
$webDir    = "'.str_replace("\\","/",realpath($pathForm)."/").'" ;

$mysqlServer = "'.$dbHostForm.'";
$mysqlUser = "'.$dbUsernameForm.'";
$mysqlPassword = '.autoquote($dbPassForm).';
$mysqlMainDb = "'.$mysqlMainDb.'";
$phpMyAdminURL = "'.$dbMyAdmin.'";
$phpSysInfoURL = "'.$phpSysInfoURL.'";
$emailAdministrator = "'.$emailForm.'";
$administratorName = "'.$nameForm.'";
$administratorSurname = "'.$surnameForm.'";
$siteName = "'.$campusForm.'";

$telephone = "'.$helpdeskForm.'";
$fax = "'.$faxForm.'";
$emailhelpdesk = "'.$helpdeskmail.'";

$language = "greek";

$Institution = "'.$institutionForm.'";
$InstitutionUrl = "'.$institutionUrlForm.'";
$postaddress = "'.addslashes($postaddressForm).'";

$have_latex = FALSE;
$close_user_registration = '.$user_registration.';

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

	if (!ini_get('short_open_tag')) {
		$errorContent[]= "<p class='caution'>$langWarningInstall2 $langWarnInstallNotice1
		<a href='$install_info_file'>$langHere</a> $langWarnInstallNotice2</p>";
			$configErrorExists = true;
        }
	$tool_content .= "<form action='$_SERVER[PHP_SELF]?alreadyVisited=1' method='post'>";

	// create config, courses and video catalogs
        mkdir_or_error('../config', $langWarningInstall3);
	mkdir_or_error('../courses', $langWarningInstall4);
	mkdir_or_error('../courses/temp', $langWarningInstall4);
	mkdir_or_error('../courses/userimg', $langWarningInstall4);
        mkdir_or_error('../video', $langWarningInstall5);
	
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

	$tool_content .= "
	<p class='sub_title1'>$langOtherReq</p>
	<ul class='installBullet'>
	<li>$langInstallBullet1</li>
	<li>$langInstallBullet2</li>
	<li>$langInstallBullet3</li>
	</ul>
	<p class='sub_title1'>$langAddOnStreaming:</p>
	<ul class='installBullet'>
	<li>$langExpPhpMyAdmin</li></ul>
	<div class='info'>$langBeforeInstall1<a href='$install_info_file' target=_blank>$langInstallInstr</a>.
	<div class='smaller'>$langBeforeInstall2<a href='../README.txt' target=_blank>$langHere</a>.</div></div><br />
	<div class='right'><input type='submit' name='install2' value='$langNextStep &raquo;' /></div>
        </form>\n";
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
function mkdir_or_error($dirname, $warn_message) {
                global $errorContent, $configErrorExists, $langWarnInstallNotice1,
                       $install_info_file, $langHere, $langWarnInstallNotice2;
                if (!is_dir($dirname)) {
                        if (@!mkdir($dirname, 0777)) {
                                $errorContent[] = "<p class='caution'>$warn_message $langWarnInstallNotice1 <a href='$install_info_file'>$langHere</a> $langWarnInstallNotice2</p>";
                                $configErrorExists = true;
                        }
                }
        }



