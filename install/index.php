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
require_once '../modules/db/database.php';
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

if (function_exists('date_default_timezone_set')) { // only valid if PHP > 5.1
    date_default_timezone_set('Europe/Athens');
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

if (file_exists('../config/config.php')) {
    // title = $langWelcomeWizard
    $tool_content .= "
        <div class='panel panel-info'>
          <div class='panel-heading'>$langWarnConfig3!</div>
          <div class='panel-body'>
              $langWarnConfig1. $langWarnConfig2.
          </div>
        </div>";
    draw($tool_content, array('no-menu' => true));
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
    return "<input class='form-control' type='text' size='$size' name='$name' value='" .
            q($GLOBALS[$name]) . "' />";
}

function textarea_input($name, $rows, $cols) {
    $GLOBALS['input_fields'][$name] = true;
    return "<textarea class='form-control' rows='$rows' cols='$cols' name='$name'>" .
            q($GLOBALS[$name]) . "</textarea>";
}

function selection_input($entries, $name) {
    $GLOBALS['input_fields'][$name] = true;
    return selection($entries, $name, q($GLOBALS[$name]), "class='form-control'");
}

$all_vars = array('dbHostForm', 'dbUsernameForm', 'dbNameForm', 'dbMyAdmin',
    'dbPassForm', 'urlForm', 'nameForm', 'emailForm', 'loginForm', 'lang',
    'passForm', 'campusForm', 'helpdeskForm', 'helpdeskmail', 'eclass_stud_reg', 'eclass_prof_reg',
    'institutionForm', 'institutionUrlForm', 'faxForm', 'postaddressForm');

// Check for db connection after settings submission
$GLOBALS['mysqlServer'] = $dbHostForm;
$GLOBALS['mysqlUser'] = $dbUsernameForm;
$GLOBALS['mysqlPassword'] = $dbPassForm;
if (isset($_POST['install4'])) {
    try {
        Debug::setLevel(Debug::ALWAYS);
        Database::core();
        if (!check_engine()) {
            $tool_content .= "<div class='alert alert-warning'>$langInnoDBMissing</div>";
            unset($_POST['install4']);
            $_POST['install3'] = true;
        } else {
            $GLOBALS['mysqlMainDb'] = $dbNameForm;
            try {
                Database::get();
                $tool_content .= "<div class='alert alert-info'>" .
                    sprintf($langDatabaseExists, '<b>' . q($dbNameForm) . '</b>') .
                    "</div>";
            } catch (Exception $e) {
                // no problem, database doesn't exist
            }
        }
    } catch (Exception $e) {
        $tool_content .= "<div class='alert alert-danger'><p>" . 
            $langErrorConnectDatabase . '</p><p><i>' .
            q($e->getMessage()) . "</i></p><p>$langCheckDatabaseSettings</p></div>";
        unset($_POST['install4']);
        $_POST['install3'] = true;
    }
}

// step 2 license
if (isset($_POST['install2'])) {
    $langStepTitle = $langLicense;
    $langStep = $langStep2;
    $_SESSION['step'] = 2;
    $gpl_link = '../info/license/gpl_print.txt';
    $tool_content .= "
       <div class='alert alert-info'>$langInfoLicence</div>
       <form class='form-horizontal' role='form' action='$_SERVER[SCRIPT_NAME]' method='post'>
         <fieldset>
           <div class='form-group'>
             <pre class='pre-scrollable' style='col-sm-12'>" . q(wordwrap(file_get_contents('../info/license/gpl.txt'))) . "</pre>
           </div>
           <div class='form-group'>
             <div class='col-sm-12'>" . icon('fa-print') . " <a href='$gpl_link'>$langPrintVers</a></div>
           </div>
           <div class='form-group'>
              <div class='col-sm-10 col-offset-2 text-left'>
                <input type='submit' class='btn btn-default' name='install1' value='&laquo; $langPreviousStep'>
                <input type='submit' class='btn btn-primary' name='install3' value='$langAccept'>
              </div>
           </div>
         </fieldset>" . hidden_vars($all_vars) . "</form>";

    draw($tool_content);
}

// step 3 mysql database settings
elseif (isset($_POST['install3'])) {
    $langStepTitle = $langDBSetting;
    $langStep = $langStep3;
    $_SESSION['step'] = 3;
    $tool_content .= "
       <div class='alert alert-info'>$langWillWrite $langDBSettingIntro</div>
       <form class='form-horizontal' role='form' action='$_SERVER[SCRIPT_NAME]' method='post'>
         <fieldset>
           <div class='form-group'>
	         <label for='dbHostForm' class='col-sm-2 control-label'>$langdbhost</label>
             <div class='col-sm-8'>" . text_input('dbHostForm', 25) . "</div>
             <div class='col-sm-2'>$langEG localhost</div>
           </div>
           <div class='form-group'>
	         <label for='dbUsernameForm' class='col-sm-2 control-label'>$langDBLogin</label>
             <div class='col-sm-8'>" . text_input('dbUsernameForm', 25) . "</div>
             <div class='col-sm-2'>$langEG root</div>
           </div>
           <div class='form-group'>
	         <label for='dbPassForm' class='col-sm-2 control-label'>$langDBPassword</label>
             <div class='col-sm-8'>" . text_input('dbPassForm', 25) . "</div>
           </div>
           <div class='form-group'>
	         <label for='dbNameForm' class='col-sm-2 control-label'>$langMainDB</label>
             <div class='col-sm-8'>" . text_input('dbNameForm', 25) . "</div>
             <div class='col-sm-2'>$langNeedChangeDB</div>
           </div>
           <div class='form-group'>
	         <label for='dbMyAdmin' class='col-sm-2 control-label'>$langphpMyAdminURL</label>
             <div class='col-sm-8'>" . text_input('dbMyAdmin', 25) . "</div>
             <div class='col-sm-2'>$langOptional</div>
           </div>
           <div class='form-group'>
             <input type='submit' class='btn btn-default' name='install2' value='&laquo; $langPreviousStep'>
		     <input type='submit' class='btn btn-primary' name='install4' value='$langNextStep &raquo;'>
           </div>
           <div class='form-group'>
             <div class='col-sm-12'>$langAllFieldsRequired</div>
           </div>
         </fieldset>" . hidden_vars($all_vars) . "</form>";

    draw($tool_content);
}

// step 4 basic config settings
elseif (isset($_POST['install4'])) {
    $langStepTitle = $langBasicCfgSetting;
    $langStep = $langStep4;
    $_SESSION['step'] = 4;
    if (empty($helpdeskmail)) {
        $helpdeskmail = '';
    }    
    $tool_content .= "
       <form class='form-horizontal' role='form' action='$_SERVER[SCRIPT_NAME]' method='post'>
         <fieldset>" .
           form_entry('urlForm', text_input('urlForm', 40), "$langSiteUrl (*)") .
           form_entry('nameForm', text_input('nameForm', 40), "$langAdminName (*)") .
           form_entry('emailForm', text_input('emailForm', 40), "$langAdminEmail (*)") .
           form_entry('loginForm', text_input('loginForm', 40), "$langAdminLogin (*)") .
           form_entry('passForm', text_input('passForm', 40), "$langAdminPass (*)") .
           form_entry('campusForm', text_input('campusForm', 40), $langCampusName) .
           form_entry('helpdeskForm', text_input('helpdeskForm', 40), $langHelpDeskPhone) .
           form_entry('faxForm', text_input('faxForm', 40), $langHelpDeskFax) .
           form_entry('helpdeskmail', text_input('helpdeskmail', 40), "$langHelpDeskEmail (**)") .
           form_entry('institutionForm', text_input('institutionForm', 40), $langInstituteShortName) .
           form_entry('institutionUrlForm', text_input('institutionUrlForm', 40), $langInstituteName) .
           form_entry('postaddressForm', textarea_input('postaddressForm', 3, 40), $langInstitutePostAddress) .
           form_entry('eclass_stud_reg',
                      selection_input(array('2' => $langDisableEclassStudRegType,
                                            '1' => $langReqRegUser,
                                            '0' => $langDisableEclassStudReg),
                                      'eclass_stud_reg'),
                      "$langUserAccount $langViaeClass") .
           form_entry('eclass_prof_reg',
                      selection_input(array('1' => $langReqRegProf,
                                            '0' => $langDisableEclassProfReg),
                                      'eclass_prof_reg'), 
                      "$langProfAccount $langViaeClass") . "
           <div class='form-group'>
             <input type='submit' class='btn btn-default' name='install3' value='&laquo; $langPreviousStep'>
		     <input type='submit' class='btn btn-primary' name='install5' value='$langNextStep &raquo;'>
           </div>
           <div class='form-group'>
             <div class='col-sm-12'>$langRequiredFields</div>
	         <div class='col-sm-12'>(**) $langWarnHelpDesk</div></td>
           </div>
         </fieldset>" . hidden_vars($all_vars) . "</form>";

    draw($tool_content);
}

// step 5 last check before install
elseif (isset($_POST['install5'])) {
    $langStepTitle = $langLastCheck;
    $langStep = $langStep5;
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
       <div class='alert alert-info'>$langReviewSettings</div>
       <form class='form-horizontal' role='form' action='$_SERVER[SCRIPT_NAME]' method='post'>
         <fieldset>" .
           display_entry(q($dbHostForm), $langdbhost) .
           display_entry(q($dbUsernameForm), $langDBLogin) .
           display_entry(q($dbNameForm), $langMainDB) .
           display_entry(q($dbMyAdmin), 'PHPMyAdmin URL') .
           display_entry(q($urlForm), $langSiteUrl) .
           display_entry(q($emailForm), $langAdminEmail) .
           display_entry(q($nameForm), $langAdminName) .
           display_entry(q($loginForm), $langAdminLogin) .
           display_entry(q($passForm), $langAdminPass) .
           display_entry(q($campusForm), $langCampusName) .
           display_entry(q($helpdeskForm), $langHelpDeskPhone) .
           display_entry(q($helpdeskmail), $langHelpDeskEmail) .
           display_entry(q($institutionForm), $langInstituteShortName) .
           display_entry(q($institutionUrlForm), $langInstituteName) .
           display_entry(nl2br(q($postaddressForm)), $langInstitutePostAddress) .
           display_entry(q($disable_eclass_stud_reg_info), $langDisableEclassStudRegType) .
           display_entry(q($disable_eclass_prof_reg_info), $langDisableEclassProfRegType) . "
           <div class='form-group'>
             <input type='submit' class='btn btn-default' name='install4' value='&laquo; $langPreviousStep'>
		     <input type='submit' class='btn btn-primary' name='install6' value='$langInstall &raquo;'>
           </div>
         </fieldset>" . hidden_vars($all_vars) . "</form>";

    draw($tool_content);
}

// step 6 installation successful
elseif (isset($_POST['install6'])) {
    // database creation
    $langStepTitle = $langInstallEnd;
    $langStep = $langStep6;
    $_SESSION['step'] = 6;
    if (mysql_errno() > 0) { // problem with server
        $no = mysql_errno();
        $msg = mysql_error();
        $tool_content .= "[" . $no . "] - " . $msg . "
		<div class='alert alert-warning'>$langErrorMysql</div>
		<ul class='installBullet'>
		<li>$langdbhost: $dbHostForm</li>
		<li>$langDBLogin: $dbUsernameForm</li>
		<li>$langDBPassword: " . q($dbPassForm) . "</li>
		</ul>
		<p>$langBackStep3_2</p><br />
		<form action='$_SERVER[SCRIPT_NAME]' method='post'>
		<input class='btn btn-primary' type='submit' name='install3' value='&lt; $langBackStep3'>"
                . hidden_vars($all_vars) .
                "</form>";
        draw($tool_content);
        exit();
    }
    $mysqlMainDb = $dbNameForm;
    //$active_ui_languages = implode(' ', active_subdirs('../lang', 'messages.inc.php'));
    $active_ui_languages = 'el en';

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
	<div class='alert alert-success'>$langInstallSuccess</div>

	<br />
	<div>$langProtect</div>
	<br /><br />
	<form action='../'><input class='btn btn-primary' type='submit' value='$langEnterFirstTime' /></form>";
    }
    $_SESSION['langswitch'] = $lang;
    draw($tool_content);
}

// step 1 requirements
elseif (isset($_POST['install1'])) {
    $langStepTitle = $langRequirements;
    $langStep = $langStep1;
    $_SESSION['step'] = 1;
    $configErrorExists = false;

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
        $tool_content .= "<div class='alert alert-danger'>" . implode('', $errorContent) . "</div>" .
            "<div class='alert alert-warning'>$langWarnInstallNotice1 <a href='$install_info_file'>$langHere</a> $langWarnInstallNotice2</div>";
        draw($tool_content);
        exit();
    }

    $tool_content .= "<form action='$_SERVER[SCRIPT_NAME]' method='post'>
	<h3>$langCheckReq</h3>
	<ul class='list-unstyled'>
        <li>" . icon('fa-check') . " <b>Webserver</b> $langFoundIt <em>" . q($_SERVER['SERVER_SOFTWARE']) . "</em></li>";
    if (version_compare(PHP_VERSION, '5.4.0') >= 0) {
        $info_icon = icon('fa-check');
        $info_text = '';
    } else {
        $info_icon = icon('fa-ban');
        $info_text = "<div class='alert alert-danger'>$langWarnAboutPHP</div>";
    }
    $tool_content .= "<li>$info_icon <b>$langPHPVersion</b> $langFoundIt <em>" . PHP_VERSION . "</em></li>";
    $tool_content .= "</ul>";
    $tool_content .= $info_text;
    $tool_content .= "<h3>$langRequiredPHP</h3>";
    $tool_content .= "<ul class='list-unstyled'>";
    warnIfExtNotLoaded('standard');
    warnIfExtNotLoaded('session');
    warnIfExtNotLoaded('pdo');
    warnIfExtNotLoaded('pdo_mysql');
    warnIfExtNotLoaded('gd');
    warnIfExtNotLoaded('mbstring');
    warnIfExtNotLoaded('xml');
    warnIfExtNotLoaded('dom');
    warnIfExtNotLoaded('zlib');
    warnIfExtNotLoaded('pcre');
    warnIfExtNotLoaded("curl");
    $tool_content .= "</ul><h3>$langOptionalPHP</h3>";
    $tool_content .= "<ul class='list-unstyled'>";
    warnIfExtNotLoaded('ldap');    
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
	<div class='right'><input type='submit' class='btn btn-primary' name='install2' value='$langNextStep &raquo;' /></div>" .
            hidden_vars($all_vars) . "</form>\n";
    draw($tool_content);
} else {
    $langLanguages = array(
        'el' => 'Ελληνικά (el)',
        'en' => 'English (en)');

    // <title>$langWelcomeWizard</title>
    $tool_content .= "
    <div class='row'>
      <div class='col-sm-12 text-center'>
        <img src='welcome.png' alt=''>
        <h1>$langWelcomeWizard</h1>
        <div class='panel panel-info text-left'>
          <div class='panel-heading'>$langThisWizard</div>
          <div class='panel-body'>
             <ul>
                <li>$langWizardHelp1</li>
                <li>$langWizardHelp2</li>
                <li>$langWizardHelp3</li>
             </ul>
          </div>
        </div>
        <form class='form-horizontal' role='form' method='post' action='$_SERVER[SCRIPT_NAME]'>
          <fieldset>
            <div class='form-group'>
              <label for='lang' class='col-sm-2 control-label'>$langChooseLang:</label>
              <div class='col-sm-10'>" . selection($langLanguages, 'lang', $lang, 'class="form-control" onChange=\"document.langform.submit();\"') . "</div>
            </div>
            <div class='form-group'>
              <div class='col-sm-offset-2 col-sm-10 text-left'>
                <input type='submit' class='btn btn-primary' name='install1' value='$langNextStep &raquo;'>
                <input type='hidden' name='welcomeScreen' value='true'>
              </div>
            </div>
          <fieldset>
        </form>
      </div>
    </div>";
    draw($tool_content, array('no-menu' => true));
}

