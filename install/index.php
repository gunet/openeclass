<?php session_start();
header('Content-Type: text/html; charset=UTF-8');
/*===========================================================================
*              GUnet eClass 2.0
*       E-learning and Course Management Program
* ===========================================================================
*	Copyright(c) 2003-2006  Greek Universities Network - GUnet
*	A full copyright notice can be read in "/info/copyright.txt".
*
*  Authors:	Costas Tsibanis <k.tsibanis@noc.uoa.gr>
*		Yannis Exidaridis <jexi@noc.uoa.gr>
*		Alexandros Diamantidis <adia@noc.uoa.gr>
*
*	For a full list of contributors, see "credits.txt".
*
*	This program is a free software under the terms of the GNU
*	(General Public License) as published by the Free Software
*	Foundation. See the GNU License for more details.
*	The full license can be read in "license.txt".
*
*	Contact address: 	GUnet Asynchronous Teleteaching Group,
*				Network Operations Center, University of Athens,
*				Panepistimiopolis Ilissia, 15784, Athens, Greece
*				eMail: eclassadmin@gunet.gr
============================================================================*/
/*
 * Installation wizard
 *
 * @author Evelthon Prodromou <eprodromou@upnet.gr>
 * @version $Id$
 *
 * @abstract This is the installation wizard of eclass.
 *
 */

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

include "../include/lib/main.lib.php";
include "install_functions.php";
include "../modules/lang/$lang/common.inc.php";
include "../modules/lang/$lang/messages.inc.php";

if (file_exists("../config/config.php")) {
	$tool_content .= "
	<!DOCTYPE html PUBLIC \"-//W3C//DTD XHTML 1.0 Transitional//EN\" \"http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd\">
	<html xmlns=\"http://www.w3.org/1999/xhtml\"><head>
	<meta http-equiv=\"Content-Type\" content=\"text/html; charset=UTF-8\" />
    	<title>$langWelcomeWizard</title>
    	<link href=\"../template/classic/tool_content.css\" rel=\"stylesheet\" type=\"text/css\" />
    	<link href=\"./install.css\" rel=\"stylesheet\" type=\"text/css\" />
  	</head>
  	<body><table width = \"99%\"><tbody><tr><td class=\"extraMessage\">$langWarnConfig
	</td></tr></tbody></table>
	</body></html>";
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
	$urlAppendPath = ereg_replace ("/install/index.php", "", $_SERVER['PHP_SELF']);
	$urlForm = "http://".$_SERVER['SERVER_NAME'].$urlAppendPath."/";
	$pathForm = realpath("../")."/";
	$emailForm = $_SERVER['SERVER_ADMIN'];
	$nameForm = "Διαχειριστής";
	$surnameForm = "Πλατφόρμας";
	$loginForm = "admin";
	$passForm = generePass(8);
	$campusForm = "GUNet eClass";
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
            <input type=\"hidden\" name=\"ldapserver\" value=\"".@$ldapserver."\">
            <input type=\"hidden\" name=\"dnldapserver\" value=\"".@$dnldapserver."\">
	    <input type=\"hidden\" name=\"reguser\" value=\"".@$reguser."\">
            <input type=\"hidden\" name=\"vodServer\" value=\"".@$vodServerForm."\">
            <input type=\"hidden\" name=\"MCU\" value=\"".@$MCUForm."\">
            <input type=\"hidden\" name=\"persoIsActive\" value=\"".@$persoIsActive."\">";
}


// step 2 license

if(isset($_REQUEST['install2']) OR isset($_REQUEST['back2']))
{
	$langStepTitle = $langLicence;
	$langStep = $langStep2;
	$_SESSION['step']=2;
	$tool_content .= "<p>$langInfoLicence<a href=\"../info/license/gpl_print.txt\">(".$langPrintVers.")</a></p>
        <textarea wrap=\"virtual\" cols=\"65\" rows=\"15\">";
	$tool_content .= file_get_contents('../info/license/gpl.txt');
	$tool_content .= "</textarea><br/><br/>
        <input type=\"submit\" name=\"back1\" value=\"< $langPreviousStep\">
        <input type=\"submit\" name=\"install3\" value=\"$langAccept >\" ></form>";
	draw($tool_content);
}

elseif(isset($_REQUEST['install3']) OR isset($_REQUEST['back3'])) {
	// step 3 mysql database settings
	$langStepTitle = $langDBSetting;
	$langStep = $langStep3;
	$_SESSION['step']=3;
	$tool_content .= "<p>".$langDBSettingIntro.".  ".$langAllFieldsRequired."</p>
	<table width=\"99%\">
	<thead><tr>
	<th>".$langDBHost."</th>
	<td><input type=\"text\" size=\"25\" name=\"dbHostForm\" value=\"".$dbHostForm."\">".$langEG." localhost
	</td></tr>
	<tr>
	<th>".$langDBLogin."</th>
	<td>
	<input type=\"text\"  size=\"25\" name=\"dbUsernameForm\" value=\"".$dbUsernameForm."\">".$langEG." root
	</td>
	</tr>
	<tr>
	<th>".$langDBPassword."</th>
	<td>
	<input type=\"text\"  size=\"25\" name=\"dbPassForm\" value=\"$dbPassForm\">".$langEG." ".generePass(8)."
	</td>
	</tr>
	<tr>
	<th>".$langMainDB."</th>
	<td>
	<input type=\"text\"  size=\"25\" name=\"dbNameForm\" value=\"$dbNameForm\">($langNeedChangeDB)
	</td>
	</tr>
	<tr><th>URL του phpMyAdmin</th>
	<td>
	<input type=\"text\" size=\"25\" name=\"dbMyAdmin\" value=\"".$dbMyAdmin."\">$langNotNeedChange
	</td>
	</tr><tr>
	<th>URL του System info</th>
	<td>
	<input type=\"text\" size=\"25\" name=\"phpSysInfoURL\" value=\"".$phpSysInfoURL."\">$langNotNeedChange
	</td>
	</tr></thead>
	</table><br/><br/>
	<input type=\"submit\" name=\"back2\" value=\"< $langPreviousStep\">
	<input type=\"submit\" name=\"install5\" value=\"$langNextStep >\">
	</form>";
	draw($tool_content);
}	 // install3

// step 4 config settings

elseif(isset($_REQUEST['install5']) OR isset($_REQUEST['back4']))
{
	// Added by vagpits
	// Global variable persoIsActive
	if ($persoIsActive == "true") {
		$persoIsActiveSelTrue = "selected";
		$persoIsActiveSelFalse = "";
	} else {
		$persoIsActiveSelTrue = "";
		$persoIsActiveSelFalse = "selected";
	}
	$langStepTitle = $langCfgSetting;
	$langStep = $langStep4;
	$_SESSION['step']=4;
	$tool_content .= "<p>$langWillWrite</b>. $langRequiredFields.</p></p>
	<table width=\"99%\"><thead><tr>
	<th>$langSiteUrl<font color=\"red\">*</font></th>
	<td>
	<input type=\"text\" size=\"40\" name=\"urlForm\" value=\"$urlForm\">
	</td>
	</tr>
	<tr>
	<th>".$langLocalPath."<font color=red>*</font></th>
	<td>
	<input type=text size=40 name=\"pathForm\" value=\"".realpath($pathForm)."/\">
	</td>
	</tr>
	<tr>
	<th>".$langAdminName."</th>
	<td><input type=\"text\" size=\"40\" name=\"nameForm\" value=\"$nameForm\"></td>
	</tr>
	<tr>
	<th>".$langAdminSurname."</th>
	<td><input type=\"text\" size=\"40\" name=\"surnameForm\" value=\"$surnameForm\"></td>
	</tr>
        <tr><th>".$langAdminEmail."</th>
        <td><input type=text size=40 name=\"emailForm\" value=\"$emailForm\"></td>
	</tr>
        <tr><th>".$langAdminLogin."</th>
        <td><input type=\"text\" size=\"40\" name=\"loginForm\" value=\"$loginForm\">
        </td>
        </tr>
        <tr><th>".$langAdminPass."</th>
        <td><input type=\"text\" size=\"40\" name=\"passForm\" value=\"$passForm\"></td>
        </tr>
        <tr><th>".$langCampusName."</th>
        <td><input type=\"text\" size=\"40\" name=\"campusForm\" value=\"$campusForm\">
        <td>
        </tr>
        <tr><th>".$langHelpDeskPhone."</th>
        <td><input type=\"text\" size=\"40\" name=\"helpdeskForm\" value=\"$helpdeskForm\"></td>
        </tr>
        <tr><th>".$langHelpDeskFax."</th>
        <td><input type=\"text\" size=\"40\" name=\"faxForm\" value=\"$faxForm\"></td>
        </tr>
        <tr>
        <th>".$langHelpDeskEmail."
        <font color=\"red\">**</font color>
        </th>
        <td><input type=text size=40 name=\"helpdeskmail\" value=\"$helpdeskmail\"></td>
        </tr>
        <tr><th>".$langInstituteShortName."</th>
        <td><input type=text size=40 name=\"institutionForm\" value=\"$institutionForm\"></td>
        </tr>
        <tr><th>".$langInstituteName."</th>
        <td><input type=\"text\" size=\"40\" name=\"institutionUrlForm\" value=\"$institutionUrlForm\"></td>
        </tr>
        <tr><th>".$langInstitutePostAddress."</th>
        <td><textarea rows='3' cols='40' name=\"postaddressForm\" value=\"".@$postaddressForm."\"></textarea></td>
	</tr>
        <tr><th>$langldap_host_url</th>
        <td><input type=\"text\" size=\"40\" name=\"ldapserver\" value=\"$ldapserver\"></td>
        </tr>
        <tr><th>$langLDAPBaseDn</th>
        <td><input type=\"text\" size=\"40\" name=\"dnldapserver\" value=\"$dnldapserver\"></td>
        </tr>
	<tr><th>$langViaReq</th>
	<td><input type='checkbox' name='reguser'></td>
	</tr>
        <tr><th>$langVod</th>
        <td>
<script>
function set_video_input()
	{
		if(document.getElementById(\"video_check\").checked==true)
		{
			document.getElementById(\"video_input_div_text\").innerHTML='Πρόθεμα του τελικού URL με το οποίο θα εξυπηρετούνται τα αποθηκευμένα στον εξυπηρέτη video streaming αρχεία<font color=\"red\">*';
			document.getElementById(\"video_input_div_input\").innerHTML='<input type=\"text\" size=\"20\" name=\"vodServerForm\" value=\"$vodServer\"><br>Πχ. mms://windows_media.server.gr/, rtsp://real.server.gr';
		}
		else{ document.getElementById(\"video_input_div_text\").innerHTML='';
		      document.getElementById(\"video_input_div_input\").innerHTML='';
		}
	}
</script>
	<input type=\"checkbox\" id=\"video_check\" onclick=\"set_video_input();\"/><br>
     </td>
     </tr><tr>
	<th><div id=\"video_input_div_text\"></div></th>
	<td><div id=\"video_input_div_input\"></td>
	</tr>
      <tr><th>$langMCU</th><td>
<script>
function set_MCU()
	{
		if(document.getElementById(\"MCU_check\").checked==true)
		{
			document.getElementById(\"MCU_div_text\").innerHTML='<font size=\"2\" face=\"arial, helvetica\">Διεύθυνση MCU</font><font color=\"red\">*</font>';
			document.getElementById(\"MCU_div_input\").innerHTML='<input type=\"text\" size=\"20\" name=\"MCUForm\" value=\"$MCU\"><br>Πχ. rts.grnet.gr';
		}
		else{ document.getElementById(\"MCU_div_text\").innerHTML='';
		      document.getElementById(\"MCU_div_input\").innerHTML='';
		}
	}
</script>
	<input type=\"checkbox\" id=\"MCU_check\" onclick=\"set_MCU();\"/><br>
     </td>
     </tr>
	<tr><th><div id=\"MCU_div_text\"></div></th>
	<td>
	<div id=\"MCU_div_input\">
	</td></tr>
           <tr><th>$langPerso</th>
            <td>
            <select name=\"persoIsActive\">
            	<option value=\"true\" ".$persoIsActiveSelTrue.">true</option>
            	<option value=\"false\" ".$persoIsActiveSelFalse.">false</option>
            	</select>
             </td>
             </tr>
             </thead>
             </table>
          <p><font color=\"red\">**</font>".$langWarnHelpDesk."</p><br/>
         <input type=\"submit\" name=\"back3\" value=\"< $langPreviousStep \">
         <input type=\"submit\" name=\"install6\" value='$langNextStep >'>";
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

	$tool_content .= "<p>$langReviewSettings</p>
        <ul id=\"installBullet\">
        <li>$langDBHost: $dbHostForm</li>
        <li>$langDBLogin: $dbUsernameForm</li>
        <li>$langDBPassword: $dbPassForm</li>
        <li>$langMainDB: $dbNameForm</li>
        <li>PHPMyAdmin URL: $dbMyAdmin</li>
        <li>$langSiteUrl: $urlForm</li>
        <li>$langLocalPath: $pathForm</li>
        <li>$langAdminEmail: $emailForm</li>
        <li>$langAdminName: $nameForm</li>
        <li>$langAdminSurname: $surnameForm</li>
        <li><b>$langAdminLogin: </b>$loginForm</li>
        <li><b>$langAdminPass: </b>$passForm</li>
        <li>$langCampusName: $campusForm</li>
        <li>$langHelpDeskPhone: $helpdeskForm</li>
        <li>$langHelpDeskFax: $faxForm</li>
        <li>$langHelpDeskEmail: $helpdeskmail</li>
        <li>$langInstituteShortName: $institutionForm</li>
        <li>$langInstituteName: $institutionUrlForm</li>
        <li>$langInstitutePostAddress: $postaddressForm</li>
        <li>$langldap_host_url: $ldapserver</li>
        <li>$langLDAPBaseDn: $dnldapserver </li>
	<li>".$mes_add."</li>
	<li>MCU: ".@$MCUForm." </li>
	<li>$langVod: ".@$vodServerForm." </li>
        </ul>
        <input type=\"submit\" name=\"back4\" value=\"< $langPreviousStep\">
        <input type=\"submit\" name=\"install7\" value=\"$langInstall >\">
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
		$tool_content .= "<table width = \"99%\"><tbody><tr><td class=\"extraMessage\">
		<u><b>[".$no."] - ".$msg."</b></u><br/>
		<p>$langErrorMysql</p>
        	<ul id=\"installBullet\">
        	<li>$langDBHost: ".$dbHostForm."</li>
        	<li>$langDBLogin: ".$dbUsernameForm."</li>
        	<li>$langDBPassword: ".$dbPassForm."</li>
        	</ul>
        	<p>$langBackStep3_2</p></td>
		</tr></tbody></table><br/>
		<input type=\"submit\" name=\"install3\" value=\"< $langBackStep3\"></form>";
		draw($tool_content);
		exit();
	}

	$mysqlMainDb = $dbNameForm;
	
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
 * GUnet eClass 2.0 configuration file
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

$persoIsActive = '.$persoIsActive.';
$durationAccount = "126144000";

define("UTF8", true);

'.($vodServer==''?'//':'').'$vodServer = "'.$vodServer.'";
'.($MCU==''?'//':'').'$MCU = "'.$MCU.'";
$encryptedPasswd = true;
';
	// write to file
	fwrite($fd, $stringConfig);
	// message

	$tool_content .= "<table width = \"99%\"><tbody><tr><td class=\"extraMessageOK\">
	<p>$langInstallSuccess</p>
        <br><p><b>$langProtect</b></p>
	</td></tr></tbody></table><br>
        </form>
    	<form action=\"../\"><input type=\"submit\" value=\"$langEnterFirstTime\"></form>";
	draw($tool_content);
	}       // τέλος ελέγχου για δικαιώματα
}	// end of step 6

// step 1 requirements

elseif (isset($_REQUEST['install1']) || isset($_REQUEST['back1']))
{
	$langStepTitle = $langRequirements;
	$langStep = $langStep1;
	$_SESSION['step']=1;
	$configErrorExists = false;

	if (empty($SERVER_SOFTWARE)) {
		$errorContent[]= "
		<table width = \"99%\"><tbody><tr>
		<td class=\"extraMessage\">
        	<p>$langWarningInstall1</p>
		</td></tr></tbody></table>";
		$configErrorExists = true;
	}

	if (!ini_get('short_open_tag')) {
		$errorContent[]= "
		<table width = \"99%\"><tbody>
		<tr><td class=\"extraMessage\">
        	<p>$langWarningInstall2</p>
		</td></tr></tbody></table>";
		$configErrorExists = true;
	}

	// create config, courses and video catalogs
	//config directory
	if (!is_dir("../config")) {
		$mkd=@mkdir("../config", 0777);
		if(!$mkd) {
			$errorContent[]= "<table width = \"99%\">
			<tbody><tr><td class=\"extraMessage\">
		  	<p>$langWarningInstall3</p>
			</td></tr></tbody></table>";
			$configErrorExists = true;
		}
	}
	// courses directory

	if (!is_dir("../courses")) {
		$mkd = @mkdir("../courses", 0777);
	if(!$mkd){
		$errorContent[]= "
		<table width = \"99%\"><tbody><tr><td class=\"extraMessage\">
        	<p>$langWarningInstall4</p>
		</td></tr></tbody></table>";
		$configErrorExists = true;
		}
	}

	if (!is_dir("../video")) {
		$mkd=@mkdir("../video", 0777);
		if(!$mkd) {
    			$errorContent[]= "<table width = \"99%\">
        		<tbody><tr><td class=\"extraMessage\">
        		<p>$langWarningInstall5</p></td>
          		</tr></tbody></table>";
    			$configErrorExists = true;
  		}
	}

	if($configErrorExists) {
		$tool_content .= implode("<br/>", $errorContent);
		$tool_content .= "</form>";
		draw($tool_content);
		exit();
	}

	$tool_content .= "<u>$langCheckReq</u><p>
        Webserver (<em>$langFoundIt <b>".$_SERVER['SERVER_SOFTWARE']."</b></em>)
        $langWithPHP (<em>$langFoundIt <b>PHP ".phpversion()."</b></em>).</p>";
	$tool_content .= "<u>$langRequiredPHP</u>";
	$tool_content .= "<ul id=\"installBullet\">";
	warnIfExtNotLoaded("standard");
	warnIfExtNotLoaded("session");
	warnIfExtNotLoaded("mysql");
	warnIfExtNotLoaded("gd");
	warnIfExtNotLoaded("mbstring");
	warnIfExtNotLoaded("zlib");
	warnIfExtNotLoaded("pcre");
	$tool_content .= "</ul><u>$langOptionalPHP</u>";
	$tool_content .= "<ul id=\"installBullet\">";
	warnIfExtNotLoaded("ldap");
	$tool_content .= "</ul>";
	$tool_content .= "<u>$langOtherReq</u><ul id=\"installBullet\">
    		<li>$langInstallBullet1</li>
    		<li>$langInstallBullet2</li>
    		<li>$langInstallBullet3</li>
    		</ul>
    		<u>$langAddOnStreaming:</u>
		<ul id=\"installBullet\">
     		<li>$langAddOnExpl</li>
    		<li>$langExpPhpMyAdmin</li></ul>
		<p>$langBeforeInstall1<a href=\"install.html\" target=_blank>$langInstallInstr</a>.</p>
		<p>$langBeforeInstall2<a href=\"../README.txt\" target=_blank>$langHere</a>.</p>
		<br><input type=\"submit\" name=\"install2\" value=\"$langNextStep >\">
		</form>";
		draw($tool_content);
} else {
		$langLanguages = array(
			'greek' => 'Ελληνικά (el)',
			'english' => 'English (en)');

		$tool_content .= "<!DOCTYPE html PUBLIC \"-//W3C//DTD XHTML 1.0 Transitional//EN\" \"http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd\">
		<html><head><title>$langWelcomeWizard</title>
    		<meta http-equiv=\"Content-Type\" content=\"text/html; charset=UTF-8\" />
    		<link href=\"./install.css\" rel=\"stylesheet\" type=\"text/css\" />
  		</head>
  		<body><div class=\"outer\">
    		<div class=\"welcomeImg\"></div>
		<form name='langform' action='$_SERVER[PHP_SELF]' method=\"post\">
		<p>$langChooseLang:&nbsp;&nbsp;
		".selection($langLanguages, 'lang', $lang, 'onChange="document.langform.submit();"')."
		</p>
		</form>
		$langWelcomeWizard $langThisWizard
    		<ul id=\"installBullet\">
    		<li>$langWizardHelp1</li>
    		<li>$langWizardHelp2</li>
    		<li>$langWizardHelp3</li>
    		</ul>
		<p>
		<form action='$_SERVER[PHP_SELF]?alreadyVisited=1' method=\"post\">
		<input type=\"hidden\" name=\"welcomeScreen\" value=\"welcomeScreen\">
  		<input type=\"submit\" name=\"install1\" value=\"$langNextStep >\">
		</p>
 		</div></form>
		</body></html>";
		echo $tool_content;
	}
?>