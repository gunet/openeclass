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
<!DOCTYPE html PUBLIC \"-//W3C//DTD XHTML 1.0 Transitional//EN\" \"http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd\">
<html xmlns=\"http://www.w3.org/1999/xhtml\">
<head>
  <meta http-equiv=\"Content-Type\" content=\"text/html; charset=UTF-8\" />
  <title>$langWelcomeWizard</title>
  <link href=\"../template/classic/tool_content.css\" rel=\"stylesheet\" type=\"text/css\" />
  <link href=\"./install.css\" rel=\"stylesheet\" type=\"text/css\" />
</head>
<body>

  <p>&nbsp;</p>
  <table width=\"65%\" class=\"FormData\" align=\"center\" style=\"border: 1px solid #edecdf;\">
  <thead>
  <tr>
    <td>&nbsp;</td>
  </tr>
  <tr>
    <td><div align=\"center\"><img style='border:0px;' src='../template/classic/img/caution_alert.gif' title='caution-alert'></div></td>
  </tr>
  <tr>
    <td><div align=\"center\"><h4>$langWarnConfig3 !</h4></div></td>
  </tr>
  <tr>
    <td>

    <table width=\"100%\" class=\"FormInput\" align=\"center\">
    <tbody>
    <tr>
      <td width=\"40%\" class=\"odd\"><b>Πιθανοί λόγοι</b></td>
      <td><b>Αντιμετώπιση</b></td>
    </tr>
    <tr>
      <td class=\"odd\" class=\"left\">$langWarnConfig1</td>
      <td>$langWarnConfig2</td>
    </tr>
    </tbody>
    </table>

    </td>
  </tr>
  </thead>
  </table>

</body>
</html>";
	exit($tool_content);
}

//  step 0 initialise variables
if(isset($welcomeScreen) )
{
	$dbHostForm="localhost";
	$dbUsernameForm="root";
	$dbNameForm="eclass";
	$dbMyAdmin="../admin/mysql/";
	$phpSysInfoURL="../admin/sysinfo/";
	// extract the path to append to the url if it is not installed on the web root directory
	$urlAppendPath = str_replace('/install/index.php', '', $_SERVER['PHP_SELF']);
	$urlForm = "http://".$_SERVER['SERVER_NAME'].$urlAppendPath."/";
	$pathForm = realpath("../")."/";
	$emailForm = $_SERVER['SERVER_ADMIN'];
	$nameForm = "Διαχειριστής";
	$surnameForm = "Πλατφόρμας";
	$loginForm = "admin";
	$passForm = create_pass();
	$campusForm = "Open eClass";
	$helpdeskForm = "+30 2xx xxxx xxx";
	$faxForm = "";
	$postaddressForm = "";
	$institutionForm = "Ακαδημαϊκό Διαδίκτυο GUNet ";
	$institutionUrlForm = "http://www.gunet.gr/";
}

if (isset($alreadyVisited)) {
	$tool_content .= "<form action=".$_SERVER['PHP_SELF']."?alreadyVisited=1 method=\"post\">";
	$tool_content .= "
            <input type=\"hidden\" name=\"urlAppendPath\" value=\"$urlAppendPath\">
            <input type=\"hidden\" name=\"pathForm\" value=\"".str_replace("\\","/",realpath($pathForm)."/")."\" >
            <input type=\"hidden\" name=\"dbHostForm\" value=\"$dbHostForm\">
            <input type=\"hidden\" name=\"dbUsernameForm\" value=\"$dbUsernameForm\">
            <input type=\"hidden\" name=\"dbNameForm\" value=\"$dbNameForm\">
            <input type=\"hidden\" name=\"dbMyAdmin\" value=\"$dbMyAdmin\">
            <input type=\"hidden\" name=\"dbPassForm\" value=\"".@$dbPassForm."\">
            <input type=\"hidden\" name=\"urlForm\" value=\"$urlForm\">
            <input type=\"hidden\" name=\"emailForm\" value=\"$emailForm\">
            <input type=\"hidden\" name=\"nameForm\" value=\"$nameForm\">
            <input type=\"hidden\" name=\"surnameForm\" value=\"$surnameForm\">
            <input type=\"hidden\" name=\"loginForm\" value=\"$loginForm\">
            <input type=\"hidden\" name=\"passForm\" value=\"$passForm\">
            <input type=\"hidden\" name=\"phpSysInfoURL\" value=\"$phpSysInfoURL\">
            <input type=\"hidden\" name=\"campusForm\" value=\"$campusForm\">
            <input type=\"hidden\" name=\"helpdeskForm\" value=\"$helpdeskForm\">
            <input type=\"hidden\" name=\"helpdeskmail\" value=\"".@$helpdeskmail."\">
            <input type=\"hidden\" name=\"institutionForm\" value=\"$institutionForm\">
            <input type=\"hidden\" name=\"institutionUrlForm\" value=\"$institutionUrlForm\">
            <input type=\"hidden\" name=\"faxForm\" value=\"".@$faxForm."\">
            <input type=\"hidden\" name=\"postaddressForm\" value=\"".@$postaddressForm."\">
            <input type=\"hidden\" name=\"reguser\" value=\"".@$reguser."\">
            <input type=\"hidden\" name=\"vodServer\" value=\"".@$vodServerForm."\">";
}

// step 2 license

if(isset($_REQUEST['install2']) OR isset($_REQUEST['back2']))
{
	$langStepTitle = $langLicence;
	$langStep = $langStep2;
	$_SESSION['step']=2;
	$tool_content .= "<table width=\"99%\" class='FormData' align='left'>
	<tbody>
	<tr>
	<th class='left' width='50'></th>
	<td>$langInfoLicence<a href=\"../info/license/gpl_print.txt\">(".$langPrintVers.")</a></td>
	</tr>
	<tr>
	<th class='left'></th>
	<td><textarea wrap=\"virtual\" cols=\"75\" rows=\"15\" class='FormData_InputText'>";
	$tool_content .= file_get_contents('../info/license/gpl.txt');
	$tool_content .= "</textarea></td>
	</tr>
	<tr>
	<th class='left'></th>
	<td>
	<input type=\"submit\" name=\"back1\" value=\"< $langPreviousStep\">
	<input type=\"submit\" name=\"install3\" value=\"$langAccept\" >
	</td></tr>
	</tbody>
	</table>
	</form>";
	draw($tool_content);
}

elseif(isset($_REQUEST['install3']) OR isset($_REQUEST['back3'])) {
	// step 3 mysql database settings
	$langStepTitle = $langDBSetting;
	$langStep = $langStep3;
	$_SESSION['step']=3;
	$tool_content .= "<p></p>
	<table width=\"99%\" class='FormData' align='left'>
	<tbody>
	<tr>
	<th width=\"220\" class=\"left\"></th>
	<td>".$langDBSettingIntro."</td>
	</tr>
	<tr>
	<th class=\"left\">".$langdbhost."</th>
	<td><input type=\"text\" class='FormData_InputText' size=\"25\" name=\"dbHostForm\" value=\"".$dbHostForm."\">&nbsp;&nbsp;".$langEG." localhost</td>
	</tr>
	<tr>
	<th class=\"left\">".$langDBLogin."</th>
	<td><input type=\"text\" class='FormData_InputText' size=\"25\" name=\"dbUsernameForm\" value=\"".$dbUsernameForm."\">&nbsp;&nbsp;".$langEG." root </td>
	</tr>
	<tr>
	<th class=\"left\">".$langDBPassword."</th>
	<td><input type=\"text\" class='FormData_InputText' size=\"25\" name=\"dbPassForm\" value=\"$dbPassForm\">&nbsp;&nbsp;".$langEG." ".create_pass()."</td>
	</tr>
	<tr>
	<th class=\"left\">".$langMainDB."</th>
	<td><input type=\"text\" class='FormData_InputText' size=\"25\" name=\"dbNameForm\" value=\"$dbNameForm\">&nbsp;&nbsp;($langNeedChangeDB)</td>
	</tr>
	<tr>
	<th class=\"left\">URL του phpMyAdmin</th>
	<td><input type=\"text\" class='FormData_InputText' size=\"25\" name=\"dbMyAdmin\" value=\"".$dbMyAdmin."\">&nbsp;&nbsp;$langNotNeedChange</td>
	</tr>
	<tr>
	<th class=\"left\">URL του System info</th>
	<td><input type=\"text\" class='FormData_InputText' size=\"25\" name=\"phpSysInfoURL\" value=\"".$phpSysInfoURL."\">&nbsp;&nbsp;$langNotNeedChange</td>
	</tr>
	<tr>
	<th class=\"left\">&nbsp;</th>
	<td><input type=\"submit\" name=\"back2\" value=\"< $langPreviousStep\">&nbsp;<input type=\"submit\" name=\"install5\" value=\"$langNextStep >\"><div align=\"right\">(*) ".$langAllFieldsRequired."</div></td>
	</tr>
	</tbody>
	</table>
	</form>";
	draw($tool_content);
}	 // install3

// step 4 config settings

elseif(isset($_REQUEST['install5']) OR isset($_REQUEST['back4']))
{
	// Added by vagpits

	$langStepTitle = $langCfgSetting;
	$langStep = $langStep4;
	$_SESSION['step']=4;
	$tool_content .= "<table width=\"99%\" class='FormData' align='left'>
	<tbody>
	<tr>
	<th width=\"220\" class=\"left\">&nbsp;</th>
	<td>$langWillWrite.</td>
	</tr>
	<tr>
	<th class=\"left\">$langSiteUrl</th>
	<td><input type=\"text\" class=\"FormData_InputText\" size=\"40\" name=\"urlForm\" value=\"$urlForm\">&nbsp;&nbsp;(*)</td>
	</tr>
	<tr>
	<th class=\"left\">".$langLocalPath."</th>
	<td><input type=text size=40 class=\"FormData_InputText\" name=\"pathForm\" value=\"".realpath($pathForm)."/\">&nbsp;&nbsp;(*)</td>
	</tr>
	<tr>
	<th class=\"left\">".$langAdminName."</th>
	<td><input type=\"text\" class=\"FormData_InputText\" size=\"40\" name=\"nameForm\" value=\"$nameForm\"></td>
	</tr>
	<tr>
	<th class=\"left\">".$langAdminSurname."</th>
	<td><input type=\"text\" class=\"FormData_InputText\" size=\"40\" name=\"surnameForm\" value=\"$surnameForm\"></td>
	</tr>
	<tr>
	<th class=\"left\">".$langAdminEmail."</th>
	<td><input type=text class=\"FormData_InputText\" size=40 name=\"emailForm\" value=\"$emailForm\"></td>
	</tr>
	<tr>
	<th class=\"left\">".$langAdminLogin."</th>
	<td><input type=\"text\" class=\"FormData_InputText\" size=\"40\" name=\"loginForm\" value=\"$loginForm\"></td>
	</tr>
	<tr>
	<th class=\"left\">".$langAdminPass."</th>
	<td><input type=\"text\" class=\"FormData_InputText\" size=\"40\" name=\"passForm\" value=\"$passForm\"></td>
	</tr>
	<tr>
	<th class=\"left\">".$langCampusName."</th>
	<td><input type=\"text\" class=\"FormData_InputText\" size=\"40\" name=\"campusForm\" value=\"$campusForm\"><td>
	</tr>
	<tr>
	<th class=\"left\">".$langHelpDeskPhone."</th>
	<td><input type=\"text\" class=\"FormData_InputText\" size=\"40\" name=\"helpdeskForm\" value=\"$helpdeskForm\"></td>
	</tr>
	<tr>
	<th class=\"left\">".$langHelpDeskFax."</th>
	<td><input type=\"text\" class=\"FormData_InputText\" size=\"40\" name=\"faxForm\" value=\"$faxForm\"></td>
	</tr>
	<tr>
	<th class=\"left\">".$langHelpDeskEmail."</th>
	<td><input type=text class=\"FormData_InputText\" size=40 name=\"helpdeskmail\" value=\"$helpdeskmail\">&nbsp;&nbsp;(**)</td>
	</tr>
	<tr>
	<th class=\"left\">".$langInstituteShortName."</th>
	<td><input type=text class=\"FormData_InputText\" size=40 name=\"institutionForm\" value=\"$institutionForm\"></td>
	</tr>
	<tr>
	<th class=\"left\">".$langInstituteName."</th>
	<td><input type=\"text\" class=\"FormData_InputText\" size=\"40\" name=\"institutionUrlForm\" value=\"$institutionUrlForm\"></td>
	</tr>
	<tr>
	<th class=\"left\">".$langInstitutePostAddress."</th>
	<td><textarea rows='3' class=\"FormData_InputText\" cols='40' name=\"postaddressForm\" value=\"".@$postaddressForm."\"></textarea></td>
	</tr>
	<tr>
	<th class=\"left\">$langViaReq</th>
	<td><input type='checkbox' name='reguser'></td>
	</tr>";
	/*
    <tr>
      <th class=\"left\">$langVod</th>
      <td>
        <script>
function set_video_input()
	{
		if(document.getElementById(\"video_check\").checked==true)
		{
			document.getElementById(\"video_input_div_text\").innerHTML='Πρόθεμα του τελικού URL με το οποίο θα εξυπηρετούνται τα αποθηκευμένα στον εξυπηρέτη video streaming αρχεία';
			document.getElementById(\"video_input_div_input\").innerHTML='<input type=\"text\" class=\"FormData_InputText\" size=\"20\" name=\"vodServerForm\" value=\"$vodServer\">&nbsp;&nbsp;(*)<br>Πχ. mms://windows_media.server.gr/, rtsp://real.server.gr';
		}
		else{ document.getElementById(\"video_input_div_text\").innerHTML='';
		      document.getElementById(\"video_input_div_input\").innerHTML='';
		}
	}
        </script>
	    <input type=\"checkbox\" id=\"video_check\" onclick=\"set_video_input();\"/>
      </td>
    </tr>
    <tr>
	  <th class=\"left\"><div id=\"video_input_div_text\"></div></th>
	  <td><div id=\"video_input_div_input\"></td>
	</tr>
    <tr>
      <th class=\"left\">$langMCU</th><td>
        <script>
function set_MCU()
	{
		if(document.getElementById(\"MCU_check\").checked==true)
		{
			document.getElementById(\"MCU_div_text\").innerHTML='Διεύθυνση MCU';
			document.getElementById(\"MCU_div_input\").innerHTML='<input type=\"text\" class=\"FormData_InputText\" size=\"20\" name=\"MCUForm\" value=\"$MCU\">&nbsp;&nbsp;(*)<br>Πχ. rts.grnet.gr';
		}
		else{ document.getElementById(\"MCU_div_text\").innerHTML='';
		      document.getElementById(\"MCU_div_input\").innerHTML='';
		}
	}
        </script>
        <input type=\"checkbox\" id=\"MCU_check\" onclick=\"set_MCU();\"/><br>
      </td>
    </tr>
	<tr>
      <th class=\"left\"><div id=\"MCU_div_text\"></div></th>
	  <td><div id=\"MCU_div_input\"></td>
    </tr>*/
    $tool_content .= "<tr><th class=\"left\">&nbsp;</th>
	<td><input type=\"submit\" name=\"back3\" value=\"< $langPreviousStep \">
	<input type=\"submit\" name=\"install6\" value='$langNextStep >'>
	<div align=\"left\">$langRequiredFields.</div>
	<div align=\"left\">(**) ".$langWarnHelpDesk."</div></td>
	</tr>
	</tbody>
	</table>";
	draw($tool_content);
}

// step 5 last check before install

elseif(isset($_REQUEST['install6']))
{
	$pathForm = str_replace("\\\\", "/", $pathForm);
	$langStepTitle = $langLastCheck;
	$langStep = $langStep5;
	$_SESSION['step']=5;

	if (!$reguser) {
      		$mes_add ="";
  	} else {
      		$mes_add = "<br>$langToReq<br>";
  	}

	$tool_content .= "
		<table width=\"99%\" class='FormData' align='left'>
	<tbody>
	<tr>
	<th width=\"220\" class=\"left\">&nbsp;</th>
	<td>$langReviewSettings</td>
	</tr>
	<tr>
	<th class=\"left\">$langdbhost:</th>
	<td>$dbHostForm</td>
	</tr>
	<tr>
	<th class=\"left\">$langDBLogin:</th>
	<td>$dbUsernameForm</td>
	</tr>
	<tr>
	<th class=\"left\">$langMainDB: </th>
	<td>$dbNameForm</td>
	</tr>
	<tr>
	<th class=\"left\">PHPMyAdmin URL:</th>
	<td>$dbMyAdmin</td>
	</tr>
	<tr>
	<th class=\"left\">$langSiteUrl:</th>
	<td>$urlForm</td>
	</tr>
	<tr>
	<th class=\"left\">$langLocalPath:</th>
	<td>$pathForm</td>
	</tr>
	<tr>
	<th class=\"left\">$langAdminEmail:</th>
	<td>$emailForm</td>
	</tr>
	<tr>
	<th class=\"left\">$langAdminName:</th>
	<td>$nameForm</td>
	</tr>
	<tr>
	<th class=\"left\">$langAdminSurname:</th>
	<td>$surnameForm</td>
	</tr>
	<tr>
	<th class=\"left\">$langAdminLogin:</th>
	<td>$loginForm</td>
	</tr>
	<tr>
	<th class=\"left\">$langAdminPass:</th>
	<td>$passForm</td>
	</tr>
	<tr>
	<th class=\"left\">$langCampusName:</th>
	<td>$campusForm</td>
	</tr>
	<tr>
	<th class=\"left\">$langHelpDeskPhone: </th>
	<td>$helpdeskForm</td>
	</tr>
	<tr>
	<th class=\"left\">$langHelpDeskEmail:</th>
	<td>$helpdeskmail</td>
	</tr>
	<tr>
	<th class=\"left\">$langInstituteShortName:</th>
	<td>$institutionForm</td>
	</tr>
	<tr>
	<th class=\"left\">$langInstituteName:</th>
	<td>$institutionUrlForm</td>
	</tr>
	<tr>
	<th class=\"left\">$langInstitutePostAddress:</th>
	<td>$postaddressForm</td>
	</tr>
	<tr>
	<th class=\"left\">$langGroupStudentRegistrationType</th>
	<td>".$mes_add."</td>
	</tr>";
    /*<tr>
	  <th class=\"left\">MCU:</th>
	  <td>".@$MCUForm."</td>
	</tr>
    <tr>
	  <th class=\"left\">$langVod:</th>
	  <td>".@$vodServerForm."</td>
	</tr> */
    $tool_content .= "<tr><th class=\"left\">&nbsp;</th>
	<td><input type=\"submit\" name=\"back4\" value=\"< $langPreviousStep\">
	<input type=\"submit\" name=\"install7\" value=\"$langInstall >\"></td>
	</tr>
	</tbody>
	</table>
	</form>";

draw($tool_content);
}
// step 6 installation successful

elseif(isset($_REQUEST['install7']))
{
	// database creation
	$langStepTitle = $langInstallEnd;
	$langStep = $langStep6;
	$_SESSION['step']=6;
	$db = @mysql_connect("$dbHostForm", "$dbUsernameForm", "$dbPassForm");
	if (mysql_errno() > 0) // problem with server
	{
		$no = mysql_errno();
		$msg = mysql_error();
		$tool_content .= "
		<table width=\"99%\">
		<thead>
		<tr>
		<td><div align=\"center\"><img style='border:0px;' src='../template/classic/img/caution_alert.gif' title='caution-alert'></div></td>
		</tr>
		<tr>
		<td>
		<div align=\"center\"><h4>[".$no."] - ".$msg."</div></h4>
		<p>$langErrorMysql</p>
		<ul id=\"installBullet\">
		<li>$langdbhost: ".$dbHostForm."</li>
		<li>$langDBLogin: ".$dbUsernameForm."</li>
		<li>$langDBPassword: ".$dbPassForm."</li>
		</ul>
		<p>$langBackStep3_2</p></td>
		</td>
		</tr>
		</thead>
		</table>
		<input type=\"submit\" name=\"install3\" value=\"< $langBackStep3\"></form>";
		draw($tool_content);
		exit();
	}

	$mysqlMainDb = $dbNameForm;
                  die('lala');
	// create main database
	require "install_db.php";

	// create config.php
	$fd=@fopen("../config/config.php", "w");
	$langStepTitle = $langInstallEnd;
	$langStep = $langStep6;
	if (!$fd) {
		$tool_content .= $langErrorConfig;
	} else {
		if (!$reguser) $user_registration = 'FALSE';
    		else $user_registration = 'TRUE';
		$stringConfig='<?php
/* ========================================================
 * OpeneClass 2.2 configuration file
 * Automatically created by install on '.date('Y-m-d H:i').'
 * ======================================================== */

$urlServer = "'.$urlForm.'";
$urlAppend = "'.$urlAppendPath.'";
$webDir    = "'.str_replace("\\","/",realpath($pathForm)."/").'" ;

$mysqlServer = "'.$dbHostForm.'";
$mysqlUser = "'.$dbUsernameForm.'";
$mysqlPassword = "'.$dbPassForm.'";
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
$postaddress = "'.$postaddressForm.'";

$have_latex = FALSE;
$close_user_registration = '.$user_registration.';

$persoIsActive = TRUE;
$durationAccount = "126144000";

define("UTF8", true);


$encryptedPasswd = true;
';

// was in config
//'.($vodServer==''?'//':'').'$vodServer = "'.$vodServer.'";
//'.($MCU==''?'//':'').'$MCU = "'.$MCU.'";

	// write to file
	fwrite($fd, $stringConfig);
	// message
	$tool_content .= "
    <p class=\"extraMessageOK\">$langInstallSuccess
    <br />
    <br />
    <b>$langProtect</b></p>
    </form>
    <form action=\"../\"><input type=\"submit\" value=\"$langEnterFirstTime\"></form>";

	draw($tool_content);
	}
}	// end of step 6

// step 1 requirements

elseif (isset($_REQUEST['install1']) || isset($_REQUEST['back1']))
{
	$langStepTitle = $langRequirements;
	$langStep = $langStep1;
	$_SESSION['step']=1;
	$configErrorExists = false;

	if (empty($SERVER_SOFTWARE)) {
		$errorContent[]= "<p class='caution_small'>$langWarningInstall1 $langWarnInstallNotice1 <a href='$install_info_file'>$langHere</a> $langWarnInstallNotice2</p>";
		$configErrorExists = true;
	}

	if (!ini_get('short_open_tag')) {
		$errorContent[]= "<p class=\"caution_small\">$langWarningInstall2 $langWarnInstallNotice1 <a href='$install_info_file'>$langHere</a> $langWarnInstallNotice2</p>";
		$configErrorExists = true;
	}

	// create config, courses and video catalogs
	//config directory
	if (!is_dir("../config")) {
		$mkd=@mkdir("../config", 0777);
		if(!$mkd) {
			$errorContent[]= "<p class=\"caution_small\">$langWarningInstall3 $langWarnInstallNotice1 <a href='$install_info_file'>$langHere</a> $langWarnInstallNotice2</p>";
			$configErrorExists = true;
		}
	}
	// courses directory

	if (!is_dir("../courses")) {
		$mkd = @mkdir("../courses", 0777);
	if(!$mkd){
		$errorContent[]= "<p class=\"caution_small\">$langWarningInstall4 $langWarnInstallNotice1 <a href='$install_info_file'>$langHere</a> $langWarnInstallNotice2</p>";
		$configErrorExists = true;
		}
	}

	if (!is_dir("../video")) {
		$mkd=@mkdir("../video", 0777);
		if(!$mkd) {
    			$errorContent[]= "<p class=\"caution_small\">$langWarningInstall5 $langWarnInstallNotice1 <a href='$install_info_file'>$langHere</a> $langWarnInstallNotice2</p>";
    			$configErrorExists = true;
  		}
	}

	if($configErrorExists) {
		$tool_content .= implode("<br/>", $errorContent);
		$tool_content .= "</form>";
		draw($tool_content);
		exit();
	}

	$tool_content .= "
    <b>$langCheckReq</b>
    <ul id=\"installBullet\">
        <li>Webserver (<em>$langFoundIt <b>".$_SERVER['SERVER_SOFTWARE']."</b></em>)
        $langWithPHP (<em>$langFoundIt <b>PHP ".phpversion()."</b></em>).";
	$tool_content .= "</li></ul>";
	$tool_content .= "<b>$langRequiredPHP</b>";
	$tool_content .= "<ul id=\"installBullet\">";
	warnIfExtNotLoaded("standard");
	warnIfExtNotLoaded("session");
	warnIfExtNotLoaded("mysql");
	warnIfExtNotLoaded("gd");
	warnIfExtNotLoaded("mbstring");
	warnIfExtNotLoaded("zlib");
	warnIfExtNotLoaded("pcre");
	$tool_content .= "</ul><b>$langOptionalPHP</b>";
	$tool_content .= "<ul id=\"installBullet\">";
	warnIfExtNotLoaded("ldap");
	$tool_content .= "</ul>";

	$tool_content .= "
	<b>$langOtherReq</b>
	<ul id=\"installBullet\">
	<li>$langInstallBullet1</li>
	<li>$langInstallBullet2</li>
	<li>$langInstallBullet3</li>
	</ul>
	<b>$langAddOnStreaming:</b>
	<ul id=\"installBullet\">
	<li>$langAddOnExpl</li>
	<li>$langExpPhpMyAdmin</li></ul>
	<p>$langBeforeInstall1<a href=\"$install_info_file\" target=_blank>$langInstallInstr</a>.</p>
	<p>$langBeforeInstall2<a href=\"../README.txt\" target=_blank>$langHere</a>.</p>
	<br><input type=\"submit\" name=\"install2\" value=\"$langNextStep >\">
	</form>";
	draw($tool_content);
} else {
	$langLanguages = array(
		'greek' => 'Ελληνικά (el)',
		'english' => 'English (en)');

	$tool_content .= "<!DOCTYPE html PUBLIC \"-//W3C//DTD XHTML 1.0 Transitional//EN\" \"http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd\">
	<html>
	<head>
	<title>$langWelcomeWizard</title>
	<meta http-equiv=\"Content-Type\" content=\"text/html; charset=UTF-8\" />
	<link href=\"./install.css\" rel=\"stylesheet\" type=\"text/css\" />
	<link href=\"../template/classic/tool_content.css\" rel=\"stylesheet\" type=\"text/css\" />
	<link href=\"../template/classic/perso.css\" rel=\"stylesheet\" type=\"text/css\" />
	</head>
	<body>
	<table class=\"FormInput\" align=\"center\" style=\"border: 1px solid #edecdf;\">
	<thead>
	<tr>
	<td colspan=\"2\"><div align=\"center\" class=\"welcomeImg\"></div></td>
	</tr>
	<tr>
	<td colspan=\"2\"><div align=\"center\"><h4>$langWelcomeWizard</h4></div>$langThisWizard
	<ul id=\"installBullet\">
	<li>$langWizardHelp1</li>
	<li>$langWizardHelp2</li>
	<li>$langWizardHelp3</li>
	</ul>
	</td>
	</tr>
	<tr>
	<td colspan=\"2\">
	<table width=\"100%\" class=\"FormInput\" align=\"center\">
	<tbody>
	<tr>
	<td width=\"40%\" class=\"odd\">&nbsp;</td>
	<td><b>$langOptions</b></td>
	</tr>
	<tr>
	<td class=\"odd\" class=\"left\">$langChooseLang:</td>
	<td><form name='langform' action='$_SERVER[PHP_SELF]' method=\"post\">".selection($langLanguages, 'lang', $lang, 'onChange="document.langform.submit();"')."</form></td>
	</tr>
	<tr>
	<td class=\"odd\" class=\"left\">&nbsp;</td>
	<td>
	<form action='$_SERVER[PHP_SELF]?alreadyVisited=1' method=\"post\">
	<input type=\"hidden\" name=\"welcomeScreen\" value=\"welcomeScreen\">
	<input type=\"submit\" name=\"install1\" value=\"$langNextStep >\">
	</form>
	</td>
	</tr>
	</thead>
	</table>
	</td>
	</tr>
	</thead>
	</table>
	</body>
	</html>";
	echo $tool_content;
}
