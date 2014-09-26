<?php

session_start();
header('Content-Type: text/html; charset=UTF-8');
/* ========================================================================
 * Open eClass 3.0
 * E-learning and Course Management System
 * ========================================================================
 * Copyright 2003-2014 Greek Universities Network - GUnet
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

require_once '../include/main_lib.php';
require_once '../include/lib/pwgen.inc.php';
require_once '../upgrade/functions.php';
require_once 'functions.php';

$tool_content = '';
if (!isset($siteName)) {
    $siteName = '';
}
if (!isset($InstitutionUrl)) {
    $InstitutionUrl = '';
}
if (!isset($Institution)) {
    $Institution = '';
}

if (function_exists("date_default_timezone_set")) { // only valid if PHP > 5.1
    date_default_timezone_set("Europe/Athens");
}

// get installation language. Greek is the default language.
if (isset($_POST['lang'])) {
    $lang = $_SESSION['lang'] = $_POST['lang'];
} elseif (isset($_SESSION['lang'])) {
    $lang = $_SESSION['lang'];
} else {
    $lang = 'el';
}
if (!isset($language_codes[$lang])) {
    $lang = 'el';
}

if ($lang == 'el') {
    $install_info_file = "http://wiki.openeclass.org/doku.php?id=el:install_doc";
} else {
    $install_info_file = "http://wiki.openeclass.org/doku.php?id=en:install_doc";
}

// include_messages
require_once "../lang/$lang/common.inc.php";
$extra_messages = "../config/{$language_codes[$lang]}.inc.php";
if (file_exists($extra_messages)) {
    include $extra_messages;
} else {
    $extra_messages = false;
}
require_once "../lang/$lang/messages.inc.php";
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
        <p><img src='../template/classic/img/logo_openeclass.png' alt='logo'></p>
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
$phpSysInfoURL = '../admin/sysinfo/';
// step 0 initialise variables
if (isset($_POST['welcomeScreen'])) {
    $dbHostForm = 'localhost';
    $dbUsernameForm = 'root';
    $dbNameForm = 'eclass';
    $dbMyAdmin = '';
    $urlForm = ((isset($_SERVER['HTTPS']) and $_SERVER['HTTPS']) ? 'https://' : 'http://') .
            $_SERVER['SERVER_NAME'] .
            str_replace('/install/index.php', '/', $_SERVER['SCRIPT_NAME']);
    $emailForm = $_SERVER['SERVER_ADMIN'];
    $nameForm = $langDefaultAdminName;
    $loginForm = 'admin';
    $passForm = genPass();
    $campusForm = 'Open eClass';
    $helpdeskForm = '+30 2xx xxxx xxx';
    $institutionForm = $langDefaultInstitutionName;
    $institutionUrlForm = 'http://www.gunet.gr/';    
    $dbPassForm = $helpdeskmail = $faxForm = $postaddressForm = '';
    $eclass_stud_reg = 2;
    $eclass_prof_reg = 1;
    
} else {
    register_posted_variables(array(
        'lang' => true,
        'dbHostForm' => true,
        'dbUsernameForm' => true,
        'dbNameForm' => true,
        'dbPassForm' => true,
        'dbMyAdmin' => true,
        'urlForm' => true,        
        'nameForm' => true,
        'loginForm' => true,
        'passForm' => true,
        'campusForm' => true,
        'helpdeskForm' => true,
        'helpdeskmail' => true,
        'faxForm' => true,
        'postaddressForm' => true,
        'eclass_stud_reg' => true,
        'eclass_prof_reg' => true,
        'emailForm' => true,
        'lang' => true,
        'institutionForm' => true,
        'institutionUrlForm' => true));   
}

function hidden_vars($names) {
    $out = '';
    foreach ($names as $name) {
        if (isset($GLOBALS[$name]) and
                !isset($GLOBALS['input_fields'][$name])) {
            $out .= "<input type='hidden' name='$name' value='" . q($GLOBALS[$name]) . "' />\n";
        }
    }
    return $out;
}

function checkbox_input($name) {
    $GLOBALS['input_fields'][$name] = true;
    return "<input type='checkbox' name='$name' value='1'" .
            ($GLOBALS[$name] ? ' checked="1"' : '') . " />";
}

function text_input($name, $size) {
    $GLOBALS['input_fields'][$name] = true;
    return "<input type='text' class='FormData_InputText' size='$size' name='$name' value='" .
            q($GLOBALS[$name]) . "' />";
}

function textarea_input($name, $rows, $cols) {
    $GLOBALS['input_fields'][$name] = true;
    return "<textarea rows='$rows' cols='$cols' class='FormData_InputText' name='$name'>" .
            q($GLOBALS[$name]) . "</textarea>";
}

function selection_input($entries, $name) {
    $GLOBALS['input_fields'][$name] = true;
    return selection($entries, $name, q($GLOBALS[$name]));
}

$all_vars = array('dbHostForm', 'dbUsernameForm', 'dbNameForm', 'dbMyAdmin',
    'dbPassForm', 'urlForm', 'nameForm', 'emailForm', 'loginForm', 'lang',
    'passForm', 'campusForm', 'helpdeskForm', 'helpdeskmail', 'eclass_stud_reg', 'eclass_prof_reg',
    'institutionForm', 'institutionUrlForm', 'faxForm', 'postaddressForm');

// step 2 license
if (isset($_REQUEST['install2']) OR isset($_REQUEST['back2'])) {
    $langStepTitle = $langLicence;
    $langStep = $langStep2;
    $_SESSION['step'] = 2;
    $tool_content .= "<form action='$_SERVER[SCRIPT_NAME]?alreadyVisited=1' method='post'>
             <table width='100%' class='tbl'>
                   <tr><td>$langInfoLicence</td></tr>
                <tr><td><textarea cols='92' rows='15' class='FormData_InputText'>" .
            file_get_contents('../info/license/gpl.txt') . "
                        </textarea></td></tr>
                <tr><td><img src='../template/classic/img/printer.png' alt='print'>
                        <a href='../info/license/gpl_print.txt'>$langPrintVers</a></td></tr>
                <tr><td class='right'>
                        <input type='submit' name='back1' value='&laquo; $langPreviousStep' />
                        <input type='submit' name='install3' value='$langAccept' /></td></tr>
             </table>" . hidden_vars($all_vars) . "</form>";
    draw($tool_content);
}

// step 3 mysql database settings
elseif (isset($_REQUEST['install3']) OR isset($_REQUEST['back3'])) {
    $langStepTitle = $langDBSetting;
    $langStep = $langStep3;
    $_SESSION['step'] = 3;
    $tool_content .= "
	<div class='info'>$langWillWrite $langDBSettingIntro</div>
	<br />
        <form action='$_SERVER[SCRIPT_NAME]?alreadyVisited=1' method='post'>
	<table width='100%' class='tbl smaller'>
	<tr>
	  <th width='220' class='left'>$langdbhost</th>
	  <td>" . text_input('dbHostForm', 25) . "&nbsp;&nbsp;$langEG localhost</td>
	  </tr>
	<tr>
	<th class='left'>$langDBLogin</th>
	<td>" . text_input('dbUsernameForm', 25) . "&nbsp;&nbsp;$langEG root </td>
	</tr>
	<tr>
	<th class='left'>$langDBPassword</th>
	<td>" . text_input('dbPassForm', 25) . "</td>
	</tr>
	<tr>
	<th class='left'>$langMainDB</th>
	<td>" . text_input('dbNameForm', 25) . "&nbsp;&nbsp;($langNeedChangeDB)</td>
	</tr>
        <tr>
	<th class='left'>$langphpMyAdminURL</th>
	<td>" . text_input('dbMyAdmin', 25) . "&nbsp;&nbsp;$langUncompulsory</td>
	</tr>
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
elseif (isset($_REQUEST['install4']) OR isset($_REQUEST['back4'])) {
    $langStepTitle = $langBasicCfgSetting;
    $langStep = $langStep4;
    $_SESSION['step'] = 4;
    if (empty($helpdeskmail)) {
        $helpdeskmail = '';
    }    
    $tool_content .= "
                <form action='$_SERVER[SCRIPT_NAME]?alreadyVisited=1' method='post'>
                <table width='100%' class='tbl smaller'>
                <tr><th class='left' width='220'>$langSiteUrl</th>
                    <td>" . text_input('urlForm', 40) . "&nbsp;&nbsp;(*)</td></tr>
                <tr><th class='left'>$langAdminName</th>
                    <td>" . text_input('nameForm', 40) . "</td></tr>
                <tr><th class='left'>$langAdminEmail</th>
                    <td>" . text_input('emailForm', 40) . "</td></tr>
                <tr><th class='left'>$langAdminLogin</th>
                    <td>" . text_input('loginForm', 40) . "</td></tr>
                <tr><th class='left'>$langAdminPass</th>
                    <td>" . text_input('passForm', 40) . "</td></tr>
                <tr><th class='left'>$langCampusName</th>
                    <td>" . text_input('campusForm', 40) . "</td></tr>
                <tr><th class='left'>$langHelpDeskPhone</th>
                    <td>" . text_input('helpdeskForm', 40) . "</td></tr>
                <tr><th class='left'>$langHelpDeskFax</th>
                    <td>" . text_input('faxForm', 40) . "</td></tr>
                <tr><th class='left'>$langHelpDeskEmail</th>
                    <td>" . text_input('helpdeskmail', 40) . "&nbsp;&nbsp;(**)</td></tr>
                <tr><th class='left'>$langInstituteShortName</th>
                    <td>" . text_input('institutionForm', 40) . "</td></tr>
                <tr><th class='left'>$langInstituteName</th>
                    <td>" . text_input('institutionUrlForm', 40) . "</td></tr>
                <tr><th class='left'>$langInstitutePostAddress</th>
                    <td>" . textarea_input('postaddressForm', 3, 40) . "</td></tr>
		<tr><th class='left'>$langUserAccount $langViaeClass</th>
                        <td>" . selection_input(array('2' => $langDisableEclassStudRegType,
                '1' => $langReqRegUser,
                '0' => $langDisableEclassStudReg), 'eclass_stud_reg') . "</td></tr>
		<tr><th class='left'>$langProfAccount $langViaeClass</th>
			<td>" . selection_input(array('1' => $langReqRegProf,
                '0' => $langDisableEclassProfReg), 'eclass_prof_reg') . "</td></tr>
	<tr><td colspan='2' class='right'>
	  <input type='submit' name='back3' value='&laquo; " . q($langPreviousStep) . "' />
	  <input type='submit' name='install5' value='" . q($langNextStep) . " &raquo;' />
	  <div class='smaller'>$langRequiredFields.</div>
	  <div class='smaller'>(**) $langWarnHelpDesk</div></td>
	</tr>
        </table>" . hidden_vars($all_vars) . "</form>";

    draw($tool_content);
}

// step 5 last check before install
elseif (isset($_REQUEST['install5'])) {
    $langStepTitle = $langLastCheck;
    $langStep = $langStep6;
    $_SESSION['step'] = 5;

    switch ($eclass_stud_reg) {
        case '0': $disable_eclass_stud_reg_info = $langDisableEclassStudRegYes;
            break;
        case '1': $disable_eclass_stud_reg_info = $langDisableEclassStudRegViaReq;
            break;
        case '2': $disable_eclass_stud_reg_info = $langDisableEclassStudRegNo;
            break;
    }
    if (!$eclass_prof_reg) {
        $disable_eclass_prof_reg_info = $langDisableEclassProfRegYes;
    } else {
        $disable_eclass_prof_reg_info = $langDisableEclassProfRegNo;
    }
    
    $tool_content .= "
        <form action='$_SERVER[SCRIPT_NAME]?alreadyVisited=1' method='post'>
	<div class='info'>$langReviewSettings</div> <br />
		<table width='100%' class='tbl smaller'>
	<tr>
	<th class='left'>$langdbhost:</th>
	<td>" . q($dbHostForm) . "</td>
	</tr>
	<tr>
	<th class='left'>$langDBLogin:</th>
	<td>" . q($dbUsernameForm) . "</td>
	</tr>
	<tr>
	<th class='left'>$langMainDB: </th>
	<td>" . q($dbNameForm) . "</td>
	</tr>
	<tr>
	<th class='left'>PHPMyAdmin URL:</th>
	<td>" . q($dbMyAdmin) . "</td>
	</tr>
	<tr>
	<th class='left'>$langSiteUrl:</th>
	<td>" . q($urlForm) . "</td>
	</tr>
	<tr>
	<th class='left'>$langAdminEmail:</th>
	<td>" . q($emailForm) . "</td>
	</tr>
	<tr>
	<th class='left'>$langAdminName:</th>
	<td>" . q($nameForm) . "</td>
	</tr>
	<tr>
	<th class='left'>$langAdminLogin:</th>
	<td>" . q($loginForm) . "</td>
	</tr>
	<tr>
	<th class='left'>$langAdminPass:</th>
	<td>" . q($passForm) . "</td>
	</tr>
	<tr>
	<th class='left'>$langCampusName:</th>
	<td>" . q($campusForm) . "</td>
	</tr>
	<tr>
	<th class='left'>$langHelpDeskPhone: </th>
	<td>" . q($helpdeskForm) . "</td>
	</tr>
	<tr>
	<th class='left'>$langHelpDeskEmail:</th>
	<td>" . q($helpdeskmail) . "</td>
	</tr>
	<tr>
	<th class='left'>$langInstituteShortName:</th>
	<td>" . q($institutionForm) . "</td>
	</tr>
	<tr>
	<th class='left'>$langInstituteName:</th>
	<td>" . q($institutionUrlForm) . "</td>
	</tr>
	<tr>
	<th class='left'>$langInstitutePostAddress:</th>
	<td>" . nl2br(q($postaddressForm)) . "</td>
	</tr>	
	<tr>
	<th class='left'>$langDisableEclassStudRegType</th>
	<td>" . q($disable_eclass_stud_reg_info) . "</td>
	</tr>
	<tr>
	<th class='left'>$langDisableEclassProfRegType</th>
	<td>" . q($disable_eclass_prof_reg_info) . "</td>
	</tr>";
    $tool_content .= "<tr><td class='right'>&nbsp;</td>
	<td class='right'>
		<input type='submit' name='back4' value='&laquo; $langPreviousStep' />
		<input type='submit' name='install6' value='$langInstall &raquo;' />
	</td>
	</tr>
	</table>" . hidden_vars($all_vars) . "</form>";

    draw($tool_content);
}

// step 6 installation successful
elseif (isset($_REQUEST['install6'])) {
    // database creation
    $langStepTitle = $langInstallEnd;
    $langStep = $langStep7;
    $_SESSION['step'] = 6;
    $GLOBALS['mysqlServer'] = $dbHostForm;
    $GLOBALS['mysqlUser'] = $dbUsernameForm;
    $GLOBALS['mysqlPassword'] = $dbPassForm;
    $GLOBALS['mysqlMainDb'] = $dbNameForm;
    if (mysql_errno() > 0) { // problem with server
        $no = mysql_errno();
        $msg = mysql_error();
        $tool_content .= "[" . $no . "] - " . $msg . "
		<div class='alert1'>$langErrorMysql</div>
		<ul class='installBullet'>
		<li>$langdbhost: $dbHostForm</li>
		<li>$langDBLogin: $dbUsernameForm</li>
		<li>$langDBPassword: " . q($dbPassForm) . "</li>
		</ul>
		<p>$langBackStep3_2</p><br />
		<form action='$_SERVER[SCRIPT_NAME]?alreadyVisited=1' method='post'>
		<input type='submit' name='install3' value='&lt; $langBackStep3' />"
                . hidden_vars($all_vars) .
                "</form>";
        draw($tool_content);
        exit();
    }
    $mysqlMainDb = $dbNameForm;
    $active_ui_languages = implode(' ', active_subdirs('../lang', 'messages.inc.php'));

    // create main database
    require 'install_db.php';

    // create config.php
    $stringConfig = '<?php
/* ========================================================
 * Open eClass 3.0 configuration file
 * Created by install on ' . date('Y-m-d H:i') . '
 * ======================================================== */

$mysqlServer = ' . quote($dbHostForm) . ';
$mysqlUser = ' . quote($dbUsernameForm) . ';
$mysqlPassword = ' . quote($dbPassForm) . ';
$mysqlMainDb = ' . quote($mysqlMainDb) . ';
';
    $fd = @fopen("../config/config.php", "w");
    if (!$fd) {
        $config_dir = dirname(__DIR__) . '/config';
        $tool_content .= "<p class='alert'>$langErrorConfig</p>" .
                "<p class='info'>" . sprintf($langErrorConfigAlt, $config_dir) .
                "</p><pre class='config'>" . q($stringConfig) . "</pre>";
    } else {
        // write to file
        fwrite($fd, $stringConfig);
        // message
        $tool_content .= "
	<div class='success'>$langInstallSuccess</div>

	<br />
	<div>$langProtect</div>
	<br /><br />
	<form action='../'><input type='submit' value='$langEnterFirstTime' /></form>";
    }
    draw($tool_content);
}

// step 1 requirements
elseif (isset($_REQUEST['install1']) || isset($_REQUEST['back1'])) {
    $langStepTitle = $langRequirements;
    $langStep = $langStep1;
    $_SESSION['step'] = 1;
    $configErrorExists = false;

    $tool_content .= "<form action='$_SERVER[SCRIPT_NAME]?alreadyVisited=1' method='post'>";

    // create config, courses directories etc.
    mkdir_try('config');
    touch_try('config/index.php');
    mkdir_try('courses');
    touch_try('courses/index.php');
    mkdir_try('courses/temp');
    touch_try('courses/temp/index.php');
    mkdir_try('courses/userimg');
    touch_try('courses/userimg/index.php');
    mkdir_try('courses/commondocs');
    touch_try('courses/commondocs/index.php');
    mkdir_try('video');
    touch_try('video/index.php');

    if ($configErrorExists) {
        $tool_content .= implode("<br />", $errorContent);
        $tool_content .= "</form>";
        draw($tool_content);
        exit();
    }

    $tool_content .= "
	<p class='sub_title1'>$langCheckReq</p>
	<ul class='installBullet'>
        <li><b>Webserver</b> <br /> <em>$langFoundIt " . $_SERVER['SERVER_SOFTWARE'] . "</em>)
        $langWithPHP (<em>$langFoundIt PHP " . phpversion() . "</em>).";
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
    warnIfExtNotLoaded("curl");
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
	<div class='info'>$langBeforeInstall1<a href='$install_info_file' target=_blank>$langInstallInstr</a>.
	<div class='smaller'>$langBeforeInstall2<a href='../README.txt' target=_blank>$langHere</a>.</div></div><br />
	<div class='right'><input type='submit' name='install2' value='$langNextStep &raquo;' /></div>" .
            hidden_vars($all_vars) . "</form>\n";
    draw($tool_content);
} else {
    $langLanguages = array(
        'el' => 'Ελληνικά (el)',
        'en' => 'English (en)');

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
          <th><div>$langChooseLang:</div> <form name='langform' action='$_SERVER[SCRIPT_NAME]' method='post' class='form_field'>" .
            selection($langLanguages, 'lang', $lang, 'onChange="document.langform.submit();"') .
            "</form></th>
        </tr>
        <tr>
          <td colspan='2' align='right'><form action='$_SERVER[SCRIPT_NAME]?alreadyVisited=1' method='post'>
            <input type='hidden' name='welcomeScreen' value='welcomeScreen' />
            <input type='hidden' name='lang' value='$lang' />
            <input type='submit' name='install1' value='$langNextStep &raquo;' />
          </form></td>
          </tr>
        </table>
	</div>
	</body>
	</html>";
    echo $tool_content;
}


/**
 * @brief make directories
 * @global type $errorContent
 * @global boolean $configErrorExists
 * @global type $langWarningInstall3
 * @global type $langWarnInstallNotice1
 * @global type $langWarnInstallNotice2
 * @global type $install_info_file
 * @global type $langHere
 * @param type $dirname
 */
function mkdir_try($dirname) {
    global $errorContent, $configErrorExists, $langWarningInstall3,
        $langWarnInstallNotice1, $langWarnInstallNotice2,
        $install_info_file, $langHere;
    
    if (!is_dir('../' . $dirname)) {
        if (!mkdir('../' . $dirname, 0775)) {
            $errorContent[] = sprintf("<p class='caution'>$langWarningInstall3 $langWarnInstallNotice1 <a href='$install_info_file'>$langHere</a> $langWarnInstallNotice2</p>", $dirname);
            $configErrorExists = true;
        }
    }
}

/**
 * @brief create files
 * @global type $errorContent
 * @global boolean $configErrorExists
 * @global type $langWarningInstall3
 * @global type $langWarnInstallNotice1
 * @global type $langWarnInstallNotice2
 * @global type $install_info_file
 * @global type $langHere
 * @param type $filename
 */
function touch_try($filename) {
    global $errorContent, $configErrorExists, $langWarningInstall3,
        $langWarnInstallNotice1, $langWarnInstallNotice2,
        $install_info_file, $langHere;
    
    if (@!touch('../' . $filename)) {
        $errorContent[] = sprintf("<p class='caution'>$langWarningInstall3 $langWarnInstallNotice1 <a href='$install_info_file'>$langHere</a> $langWarnInstallNotice2</p>", $filename);
        $configErrorExists = true;
    }
}
