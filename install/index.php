<?php
session_start();
header('Content-Type: text/html; charset=UTF-8');
/* ========================================================================
 * Open eClass 4.0
 * E-learning and Course Management System
 * ========================================================================
 * Copyright 2003-2023 Greek Universities Network - GUnet
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
 * @brief Installation wizard of eclass.
 */

$command_line = (php_sapi_name() == 'cli' && !isset($_SERVER['REMOTE_ADDR']));
$webDir = get_base_path();
chdir($webDir);

require_once 'vendor/autoload.php';
require_once 'include/main_lib.php';
require_once 'include/lib/pwgen.inc.php';
require_once 'include/mailconfig.php';
require_once 'modules/db/database.php';
require_once 'upgrade/functions.php';
require_once 'install/functions.php';

require_once 'modules/h5p/classes/H5PHubUpdater.php';

$autoinstall = false;
if ($command_line and getenv('BASE_URL') and getenv('MYSQL_LOCATION')) {
    // Setup global variables for automated installation
    $autoinstall = true;
    $urlForm = getenv('BASE_URL');
    $host = parse_url($urlForm, PHP_URL_HOST);
    $_SESSION['lang'] = 'en';
    $_POST['welcomeScreen'] = true;
    $_POST['email_transport'] = 'mail';
    $_POST['email_announce'] = '';
    $_POST['email_bounces'] = '';
    ini_set('display_errors', '1');
    create_directories();
}

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

date_default_timezone_set('Europe/Athens');

// get installation language. Greek is the default language.
if (isset($_REQUEST['lang'])) {
    $lang = $_POST['lang'] = $_SESSION['lang'] = $_REQUEST['lang'];
} elseif (isset($_SESSION['lang'])) {
    $lang = $_SESSION['lang'];
} else {
    $lang = 'el';
}
if (!isset($language_codes[$lang])) {
    $lang = 'el';
}

if ($lang == 'el') {
    $install_info_file = "http://docs.openeclass.org/el/install";
    $readme_file =  "http://docs.openeclass.org/el/general";
} else {
    $install_info_file = "http://docs.openeclass.org/en/install";
    $readme_file = "http://docs.openeclass.org/en/general";
}

// include_messages
require_once "lang/$lang/common.inc.php";
$extra_messages = "config/{$language_codes[$lang]}.inc.php";
if (file_exists($extra_messages)) {
    include $extra_messages;
} else {
    $extra_messages = false;
}
require_once "lang/$lang/messages.inc.php";
if (file_exists('config/config.php')) {
  if(get_config('show_always_collaboration') and get_config('show_collaboration')){
    require_once "lang/$lang/messages_collaboration.inc.php";
  }
}
if ($extra_messages) {
    include $extra_messages;
}

if (file_exists('config/config.php')) {
    if ($autoinstall) {
        die("$langWarnConfig1. $langWarnConfig2\n");
    } else {
        $tool_content .= "
            <div class='panel panel-info'>
              <div class='panel-heading'>$langWarnConfig3!</div>
              <div class='panel-body'>
                  $langWarnConfig1. $langWarnConfig2.
              </div>
            </div>";
        draw($tool_content, array('no-menu' => true));
    }
}

// Input fields that have already been included in the form, either as hidden or as normal inputs
$input_fields = array();
$phpSysInfoURL = '../admin/sysinfo/';
$availableThemes = array('Default','Crimson','Emerald','Dark','Wood','Neutral','Soft_light');
// step 0 initialise variables
if (isset($_POST['welcomeScreen'])) {
    // Get DB credentials from environment for Docker image or automated installation
    $dbHostForm = getenv_default('MYSQL_LOCATION', 'localhost');
    $dbUsernameForm = getenv_default('MYSQL_ROOT_USER', 'root');
    $dbPassForm = getenv_default('MYSQL_ROOT_PASSWORD', '');
    $dbNameForm = getenv_default('MYSQL_DB', 'eclass');
    $dbMyAdmin = $emailForm = '';
    if (!isset($urlForm)) {
        $urlForm = ((isset($_SERVER['HTTPS']) and $_SERVER['HTTPS']) ? 'https://' : 'http://') .
                $_SERVER['SERVER_NAME'] .
                str_replace('/install/index.php', '/', $_SERVER['SCRIPT_NAME']);
    }
    if (isset($_SERVER['SERVER_ADMIN'])) { // only for apache
        $emailForm = $_SERVER['SERVER_ADMIN'];
    }
    $nameForm = $langDefaultAdminName;
    $loginForm = getenv_default('ADMIN_USERNAME', 'admin');
    $passForm = getenv_default('ADMIN_PASSWORD', genPass());
    $campusForm = 'Open eClass';
    $helpdeskForm = '+30 2xx xxxx xxx';
    $institutionForm = $langDefaultInstitutionName;
    $institutionUrlForm = 'https://www.gunet.gr/';
    $helpdeskmail = $postaddressForm = '';

    $eclass_stud_reg = 2;

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
        'postaddressForm' => true,
        'eclass_stud_reg' => true,
        'emailForm' => true,
        'lang' => true,
        'institutionForm' => true,
        'institutionUrlForm' => true,
        'dont_mail_unverified_mails' => true,
        'email_from' => true,
        'email_announce' => true,
        'email_bounces' => true,
        'email_transport' => true,
        'smtp_server' => true,
        'smtp_port' => true,
        'smtp_encryption' => true,
        'smtp_username' => true,
        'smtp_password' => true,
        'theme_selection' => true,
        'homepage_intro' => true,
        'sendmail_command' => true));
}

function hidden_vars($names) {
    $out = '';
    foreach ($names as $name) {
        if (isset($GLOBALS[$name]) and
                !isset($GLOBALS['input_fields'][$name])) {
            $out .= "<input type='hidden' name='$name' value='" . q($GLOBALS[$name]) . "'>\n";
        }
    }
    return $out;
}

function text_input($name, $size) {
    $GLOBALS['input_fields'][$name] = true;
    return "<input class='form-control' type='text' size='$size' name='$name' value='" .
            q($GLOBALS[$name]) . "'>";
}

function textarea_input($name, $rows, $cols) {
    $GLOBALS['input_fields'][$name] = true;
    return "<textarea class='form-control' rows='$rows' cols='$cols' name='$name'>" .
            q($GLOBALS[$name]) . "</textarea>";
}

function selection_input($entries, $name) {
    $GLOBALS['input_fields'][$name] = true;
    return selection($entries, $name, q($GLOBALS[$name]), "class='form-select'");
}

$all_vars = array('dbHostForm', 'dbUsernameForm', 'dbNameForm', 'dbMyAdmin',
    'dbPassForm', 'urlForm', 'nameForm', 'emailForm', 'loginForm', 'lang',
    'passForm', 'campusForm', 'helpdeskForm', 'helpdeskmail',
    'eclass_stud_reg', 'institutionForm',
    'institutionUrlForm', 'postaddressForm',
    'dont_mail_unverified_mails', 'email_from', 'email_announce', 'email_bounces',
    'email_transport', 'smtp_server', 'smtp_port', 'smtp_encryption',
    'smtp_username', 'smtp_password', 'sendmail_command', 'theme_selection', 'homepage_intro');

// Check for db connection after settings submission
$GLOBALS['mysqlServer'] = $dbHostForm;
$GLOBALS['mysqlUser'] = $dbUsernameForm;
$GLOBALS['mysqlPassword'] = $dbPassForm;
if (isset($_POST['install4'])) {
    try {
        Debug::setLevel(Debug::ALWAYS);
        Database::core();
        if (!check_engine()) {
          $tool_content .= "<div class='alert alert-warning'><i class='fa-solid fa-triangle-exclamation fa-lg'></i><span>$langInnoDBMissing</span></div>";
            unset($_POST['install4']);
            $_POST['install3'] = true;
        } else {
            $GLOBALS['mysqlMainDb'] = $dbNameForm;
            try {
                Database::get();
                $tool_content .= "<div class='alert alert-info'><i class='fa-solid fa-circle-info fa-lg'></i><span>" .
                sprintf($langDatabaseExists, '<b>' . q($dbNameForm) . '</b>') .
                "</span></div>";
            } catch (Exception $e) {
                // no problem, database doesn't exist
            }
        }
    } catch (Exception $e) {
          $tool_content .= "<div class='alert alert-danger'><i class='fa-solid fa-circle-xmark fa-lg'></i><span><p>" .
          $langErrorConnectDatabase . '</p><p><i>' .
          q($e->getMessage()) . "</i></p><p>$langCheckDatabaseSettings</p></span></div>";
      unset($_POST['install4']);
      $_POST['install3'] = true;
    }
} elseif ($autoinstall) {
    Debug::setLevel(Debug::ALWAYS);
    try {
        Database::core();
        if (!check_engine()) {
            die("Error: $langInnoDBMissing\n");
        }
        $GLOBALS['mysqlMainDb'] = $dbNameForm;
        try {
            Database::get();
            echo 'Note: ', sprintf($langDatabaseExists, '"' . $dbNameForm . '"'), "\n";
        } catch (Exception $e) {
            // no problem, database doesn't exist
        }
    } catch (Exception $e) {
        die("Error: $langErrorConnectDatabase\n" .
            $e->getMessage() . "\n$langCheckDatabaseSettings\n");
    }
    $_POST['install7'] = true; // Move to final installation steps
}

// step 2 license
if (isset($_POST['install2'])) {
    $langStepTitle = $langLicense;
    $langStep = sprintf($langStep1, 2, 8);
    $_SESSION['step'] = 2;
    $gpl_link = '../info/license/gpl_print.txt';
    $tool_content .= "
    <div class='alert alert-info'><i class='fa-solid fa-circle-info fa-lg'></i><span>$langInfoLicence</span></div>
        <div class='card panelCard px-lg-4 py-lg-3'>
        <div class='card-body'>
       <form class='form-horizontal form-wrapper' role='form' action='$_SERVER[SCRIPT_NAME]' method='post'>
         <fieldset>
           <div class='form-group step2-form'>
             <pre class='pre-scrollable' style='col-sm-12'>" . q(wordwrap(file_get_contents('info/license/gpl.txt'))) . "</pre>
           </div>
           <div class='form-group mt-3'>
             <div class='col-sm-12'>" . icon('fa-print') . " <a href='$gpl_link'>$langPrintVers</a></div>
           </div>
           <div class='form-group mt-5'>
              <div class='col-12'>
                <div class='row'>
                  <div class='col-lg-6 col-12'>
                    <input type='submit' class='btn cancelAdminBtn w-100' name='install1' value='&laquo; $langPreviousStep'>
                  </div>
                  <div class='col-lg-6 col-12 mt-lg-0 mt-3'>
                    <input type='submit' class='btn w-100' name='install3' value='$langAccept'>
                  </div>
                </div>
              </div>
           </div>
         </fieldset>" . hidden_vars($all_vars) . "</form></div></div>";

    draw($tool_content);
}

// step 3 mysql database settings
elseif (isset($_POST['install3'])) {
    $langStepTitle = $langDBSetting;
    $langStep = sprintf($langStep1, 3, 8);
    $_SESSION['step'] = 3;
    $tool_content .= "
    <div class='alert alert-info'><i class='fa-solid fa-circle-info fa-lg'></i><span>$langWillWrite $langDBSettingIntro</span></div>
       <form class='form-horizontal form-wrapper form-edit p-3 rounded' role='form' action='$_SERVER[SCRIPT_NAME]' method='post'>
         <fieldset>
           <div class='form-group'>
             <label for='dbHostForm' class='col-sm-12 control-label-notes'>$langdbhost</label>
             <div class='row'>
              <div class='col-sm-12'>" . text_input('dbHostForm', 25) . "</div>
              <div class='col-sm-12 help-block'>$langEG localhost</div>
             </div>
           </div>

           <div class='form-group mt-3'>
             <label for='dbUsernameForm' class='col-sm-12 control-label-notes'>$langDBLogin</label>
             <div class='row'>
              <div class='col-sm-12'>" . text_input('dbUsernameForm', 25) . "</div>
              <div class='col-sm-12 help-block'>$langEG root</div>
            </div>
           </div>

           <div class='form-group mt-3'>
             <label for='dbPassForm' class='col-sm-12 control-label-notes'>$langDBPassword</label>
             <div class='col-sm-12'>" . text_input('dbPassForm', 25) . "</div>
           </div>

           <div class='form-group mt-3'>
             <label for='dbNameForm' class='col-sm-12 control-label-notes'>$langMainDB</label>
             <div class='row'>
              <div class='col-sm-12'>" . text_input('dbNameForm', 25) . "</div>
              <div class='col-sm-12 help-block'>$langNeedChangeDB</div>
            </div>
           </div>
           <div class='form-group mt-3'>
             <label for='dbMyAdmin' class='col-sm-12 control-label-notes'>$langphpMyAdminURL</label>
             <div class='row'>
              <div class='col-sm-12'>" . text_input('dbMyAdmin', 25) . "</div>
              <div class='col-sm-12 help-block'>$langOptional</div>
          </div>

           </div>

           <div class='form-group mt-5'>
             <div class='col-12'>
              <div class='row'>
                  <div class='col-lg-6 col-12'>
                    <input type='submit' class='btn cancelAdminBtn w-100' name='install2' value='&laquo; $langPreviousStep'>
                  </div>
                  <div class='col-lg-6 col-12 mt-lg-0 mt-3'>
                    <input type='submit' class='btn w-100' name='install4' value='$langNextStep &raquo;'>
                  </div>
                </div>
            </div>

           </div>
           <div class='form-group mt-3'>
             <div class='col-sm-12'>$langAllFieldsRequired</div>
           </div>
         </fieldset>" . hidden_vars($all_vars) . "</form>";

    draw($tool_content);
}

// step 4 basic config settings
elseif (isset($_POST['install4'])) {
    $langStepTitle = $langBasicCfgSetting;
    $langStep = sprintf($langStep1, 4, 8);
    $_SESSION['step'] = 4;
    if (empty($helpdeskmail)) {
        $helpdeskmail = '';
    }
    $tool_content .= "
       <form class='form-horizontal form-wrapper form-edit p-3 rounded' role='form' action='$_SERVER[SCRIPT_NAME]' method='post'>
         <fieldset>" .
           form_entry('urlForm', text_input('urlForm', 40), "$langSiteUrl (*)") .
           form_entry('nameForm', text_input('nameForm', 40), "$langAdminName (*)") .
           form_entry('emailForm', text_input('emailForm', 40), "$langAdminEmail (*)") .
           form_entry('loginForm', text_input('loginForm', 40), "$langAdminLogin (*)") .
           form_entry('passForm', text_input('passForm', 40), "$langAdminPass (*)") .
           form_entry('campusForm', text_input('campusForm', 40), $langCampusName) .
           form_entry('helpdeskForm', text_input('helpdeskForm', 40), $langHelpDeskPhone) .
           form_entry('helpdeskmail', text_input('helpdeskmail', 40), "$langHelpDeskEmail (**)") .
           form_entry('institutionForm', text_input('institutionForm', 40), $langInstituteShortName) .
           form_entry('institutionUrlForm', text_input('institutionUrlForm', 40), $langInstituteName) .
           form_entry('postaddressForm', textarea_input('postaddressForm', 3, 40), $langInstitutePostAddress) .
           form_entry('eclass_stud_reg',
                      selection_input(array('2' => $langUserRegistration,
                                            '1' => $langReqRegUser,
                                            '0' => $langDisableEclassStudReg),
                                          'eclass_stud_reg'),
                      "$langUsersAccount") . "
           <div class='form-group mt-5'>
            <div class='col-12'>
              <div class='row'>
                  <div class='col-lg-6 col-12'>
                     <input type='submit' class='btn cancelAdminBtn w-100' name='install3' value='&laquo; $langPreviousStep'>
                  </div>
                  <div class='col-lg-6 col-12 mt-lg-0 mt-3'>
                     <input type='submit' class='btn w-100' name='install5' id='install5' value='$langNextStep &raquo;'>
                  </div>
              </div>
            </div>
            
             
           </div>
           <div class='form-group mt-3'>
             <div class='col-sm-12'>$langRequiredFields</div>
             <div class='col-sm-12'>(**) $langWarnHelpDesk</div></td>
           </div>
         </fieldset>" . hidden_vars($all_vars) . "</form>";
    draw($tool_content);
}

// step 5 basic ui
elseif(isset($_POST['install5'])){
  $langStepTitle = $langThemeSettings;
  $langStep = sprintf($langStep1, 5, 8);
  $_SESSION['step'] = 5;

  // Get all images from dir screenshots
  $dir_screens = getcwd();
  $dir_screens = $dir_screens . '/template/modern/images/screenshots';
  $dir_themes_images = scandir($dir_screens);

  $tool_content .= "
          <div class='col-12'>
            <div class='form-wrapper form-edit p-3 rounded'>
              <form class='form-horizontal form-wrapper form-edit p-3 rounded' role='form' action='$_SERVER[SCRIPT_NAME]' method='post'>
                <div class='card panelCard px-lg-4 py-lg-3'>
                  <div class='card-header border-0 d-flex justify-content-between align-items-center'>
                      <h3>$langThemeSettings</h2>
                  </div>
                  <div class='card-body'>
                      <fieldset>
                          <div class='form-group'>
                              " . form_entry('homepage_intro', textarea_input('homepage_intro', 3, 40), $langHomePageIntroTextHelp) . "
                          </div>
                          <div class='form-group mt-4'>
                              <div class='col-sm-12'>
                                  <a class='link-color TextBold' type='button' href='#view_themes_screens' data-bs-toggle='modal'>$langViewScreensThemes</a></br></br>
                                  <label for='themeSelection' class='control-label-notes'>$langAvailableThemes:</label>
                                  ".  selection_input($availableThemes, 'theme_selection')."
                              </div>
                          </div>
                          <div class='form-group mt-4'>
                              <div class='col-12 d-flex justify-content-between'>
                                  <input class='btn btn-primary' name='install4' value='&laquo; $langBack' type='submit'>
                                  <input class='btn btn-primary' name='install6' value='$langContinue &raquo;' type='submit'>
                              </div>
                          </div>
                      </fieldset>
                  </div>
                </div>
                " . hidden_vars($all_vars) . "
              </form>
            </div>
          </div>
          
          
          <div class='modal fade' id='view_themes_screens' tabindex='-1' aria-labelledby='view_themes_screensLabel' aria-hidden='true'>
              <div class='modal-dialog modal-fullscreen' style='margin-top:0px;'>
                  <div class='modal-content'>
                      <div class='modal-header'>
                          <div class='modal-title' id='view_themes_screensLabel'>$langAvailableThemes</div>
                          <button type='button' class='close' data-bs-dismiss='modal' aria-label='Close'></button>
                      </div>
                      <div class='modal-body'>
                          <div class='row row-cols-1 g-4'>";
                                  foreach($dir_themes_images as $image) {
                                      $extension = pathinfo($image, PATHINFO_EXTENSION);
                                      $imgExtArr = ['jpg', 'jpeg', 'png', 'PNG'];
                                      if(in_array($extension, $imgExtArr)){
                                          $tool_content .= "
                                              <div class='col-lg-8 col-md-10 m-auto py-4'>
                                                  <div class='card panelCard h-100'>
                                                      <img style='width:100%; height:auto; object-fit:cover; object-position:50% 50%;' class='card-img-top' src='../template/modern/images/screenshots/$image' alt='Image for current theme'/>
                                                      <div class='card-footer'>
                                                          <p> " . strtok($image, '.') . " </p>
                                                      </div>
                                                  </div>
                                              </div>
                                          ";
                                      }
                                  }
          $tool_content .= "
                          </div>
                      </div>
                  </div>
              </div>
          </div>";
          draw($tool_content);
}

// step 6 email settings
elseif (isset($_POST['install6'])) {
    $langStepTitle = $langEmailSettings;
    $langStep = sprintf($langStep1, 6, 8);
    $_SESSION['step'] = 6;
    foreach (array('dont_mail_unverified_mails', 'email_from', 'email_announce', 'email_bounces',
                   'email_transport', 'smtp_server', 'smtp_port', 'smtp_encryption',
                   'smtp_username', 'smtp_password', 'sendmail_command') as $name) {
       $GLOBALS['input_fields'][$name] = true;
    }
    $tool_content .= "<div class='col-12'><div class='form-wrapper form-edit p-3 rounded'>
       <form class='form-horizontal' role='form' action='$_SERVER[SCRIPT_NAME]' method='post'>
         <fieldset>";
    mail_settings_form();
    $tool_content .= "
           <div class='form-group mt-5'>
             <div class='col-12'>
               <div class='row'>
                <div class='col-lg-6 col-12'>
                  <input type='submit' class='btn cancelAdminBtn w-100' name='install5' value='&laquo; $langPreviousStep'>
                </div>
                <div class='col-lg-6 col-12 mt-lg-0 mt-3'>
                  <input type='submit' class='btn w-100' name='install7' value='$langNextStep &raquo;'>
                </div>
              </div>
             </div>
           </div>
         </fieldset>" .
         hidden_vars($all_vars) . "
       </form></div></div>
       <script>$(function () {" . $mail_form_js . '});</script>';
    draw($tool_content);
}

// step 7 last check before install
elseif (isset($_POST['install7'])) {
    $langStepTitle = $langLastCheck;
    $langStep = sprintf($langStep1, 7, 8);
    $_SESSION['step'] = 7;

    switch ($eclass_stud_reg) {
        case '0': $disable_eclass_stud_reg_info = $langDisableEclassStudRegYes;
                    break;
        case '1': $disable_eclass_stud_reg_info = $langDisableEclassStudRegViaReq;
                    break;
        case '2': $disable_eclass_stud_reg_info = $langDisableEclassStudRegNo;
                    break;
    }

    $head_content = "
    <script type='text/javascript'>
        $(function() {
            $('#install6').on( 'click', function() {
                bootbox.dialog({
                  closeButton: false,
                  message:  '<div><p>$langInstallMsg</p></div>'+
                            '<div class=\"progress\">'+
                                '<div class=\"progress-bar progress-bar-striped active\" role=\"progressbar\" aria-valuenow=\"100\" aria-valuemin=\"0\" aria-valuemax=\"100\" style=\"width: 100%\">'+
                                  '<span class=\"sr-only\">$langCheckNotOk1</span>'+
                                '</div>'+
                            '</div>',
                  title: '$langCheckNotOk1'
                });
            });
        });
    </script>
    ";
    $tool_content .= "
    <div class='alert alert-info'><i class='fa-solid fa-circle-info fa-lg'></i><span>$langReviewSettings</span></div>
       <form class='form-horizontal form-wrapper form-edit p-3 rounded' role='form' action='$_SERVER[SCRIPT_NAME]' method='post'>
         <fieldset>" .
           display_entry(q($dbHostForm), $langdbhost) .
           display_entry(q($dbUsernameForm), $langDBLogin) .
           display_entry(q($dbNameForm), $langMainDB) .
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
           display_entry(q($disable_eclass_stud_reg_info), $langDisableEclassStudRegType) .
           display_entry(nl2br(q($postaddressForm)), $langInstitutePostAddress) .
           display_entry(q($homepage_intro), $langHomePageIntroTextHelp) .
           display_entry(q($availableThemes[$theme_selection]), $langActiveTheme) ."
           <div class='form-group mt-5'>
             <div class='col-12'>
              <div class='row'>
                <div class='col-lg-5 col-12'>
                  <input type='submit' class='btn cancelAdminBtn w-100' name='install6' value='&laquo; $langPreviousStep'>
                </div>
                <div class='col-lg-7 col-12 mt-lg-0 mt-3'>
                 <input type='submit' class='btn w-100' name='install8' id='install8' value='$langInstall &raquo;'>
                </div>
              </div>
             </div>                        
           </div>
         </fieldset>" . hidden_vars($all_vars) . "</form>";

    draw($tool_content, null, $head_content);
}

// step 8 installation successful
elseif (isset($_POST['install8'])) {
    // database creation
    $langStepTitle = $langInstallEnd;
    $langStep = sprintf($langStep1, 8, 8);
    $_SESSION['step'] = 8;
    $mysqlMainDb = $dbNameForm;
    $active_ui_languages = 'el en';

    // create main database
    require 'install_db.php';

    // default home page settings
    set_config('dont_display_statistics', 1);
    set_config('dont_display_testimonials', 1);
    set_config('dont_display_popular_courses', 1);
    set_config('dont_display_open_courses', 1);
    set_config('dont_display_texts', 1);
    set_config('dont_display_login_form', 0);
    $selectedTheme = Database::get()->querySingle('SELECT id FROM theme_options WHERE name = ?s',$availableThemes[$_POST['theme_selection']]);
    if($selectedTheme){
      $selectedThemeId = $selectedTheme->id;
    }else{
      $selectedThemeId = 0;
    }
    set_config('theme_options_id', $selectedThemeId);
    set_config('homepage_intro', $_POST['homepage_intro']);

    // update departments info
    update_minedu_deps();

    // create config.php
    $stringConfig = '<?php
/* ========================================================
 * Open eClass 3.x configuration file
 * Created by install on ' . date('Y-m-d H:i') . '
 * ======================================================== */

$mysqlServer = ' . quote($dbHostForm) . ';
$mysqlUser = ' . quote($dbUsernameForm) . ';
$mysqlPassword = ' . quote($dbPassForm) . ';
$mysqlMainDb = ' . quote($mysqlMainDb) . ';
';
    $fd = @fopen('config/config.php', 'w');
    if (!$fd) {
        $config_dir = dirname(__DIR__) . '/config';
        $tool_content .= "<p class='alert'>$langErrorConfig</p>" .
                "<p class='info'>" . sprintf($langErrorConfigAlt, $config_dir) .
                "</p><pre class='config'>" . q($stringConfig) . "</pre>";
    } else {
        // write to file
        fwrite($fd, $stringConfig);

        $installDir = dirname(dirname(__FILE__));
        // install certificate templates
        installCertTemplates($installDir);
        // install badge icons
        installBadgeIcons($installDir);
        chdir(dirname(__FILE__));
        // install h5p content
        $hubUpdater = new H5PHubUpdater();
        $hubUpdater->fetchLatestContentTypes();
        set_config('h5p_update_content_ts', date('Y-m-d H:i', time()));
        chdir('..');

        // message
        if ($autoinstall) {
            die("Success: Open eClass installation complete\n" .
                "Base URL: $urlForm\n" .
                "Admin username: $loginForm\n" .
                "Admin password: $passForm\n");
        } else {
            $tool_content .= "
                <div class='alert alert-success'><i class='fa-solid fa-circle-check fa-lg'></i><span>$langInstallSuccess</span></div>
                <br>
                <div>$langProtect</div>
                <br /><br />
                <form action='../'><input class='btn btn-sm btn-primary submitAdminBtn w-100 text-white' type='submit' value='$langEnterFirstTime' /></form>";
        }

    }
    $_SESSION['langswitch'] = $lang;
    draw($tool_content);
}

// step 1 requirements
elseif (isset($_POST['install1'])) {
    $langStepTitle = $langRequirements;
    $langStep = sprintf($langStep1, 1, 7);
    $_SESSION['step'] = 1;
    $configErrorExists = false;


    create_directories();


    if ($configErrorExists) {
      $tool_content .= "<div class='alert alert-danger'><i class='fa-solid fa-circle-xmark fa-lg'></i><span>" . implode('', $errorContent) . "</span></div>" .
      "<div class='alert alert-warning'><i class='fa-solid fa-triangle-exclamation fa-lg'></i><span>$langWarnInstallNotice1 <a href='$install_info_file'>$langHere</a> $langWarnInstallNotice2</span></div>";
        draw($tool_content);
        exit();
    }

    $tool_content .= "<div class='card panelCard px-lg-4 py-lg-3'>
                        <div class='card-body'><form class='form-wrapper' action='$_SERVER[SCRIPT_NAME]' method='post'>
    <h3>$langCheckReq</h3>";

    $tool_content .= "<ul class='list-group list-group-flush'>
        <li class='list-group-item element'>" . icon('fa-check') . " <strong>Webserver</strong> <em>" . q($_SERVER['SERVER_SOFTWARE']) . "</em></li>
    </ul>";

    $tool_content .= "<ul class='list-group list-group-flush'>";
    $tool_content .= "<strong>$langPHPVersion</strong>";
    checkPHPVersion('8.0');
    $tool_content .= "</ul>";
    $tool_content .= "<h3>$langRequiredPHP</h3>";
    $tool_content .= "<ul class='list-group list-group-flush'>";

    warnIfExtNotLoaded('pdo_mysql');
    warnIfExtNotLoaded('gd');
    warnIfExtNotLoaded('mbstring');
    warnIfExtNotLoaded('xml');
    warnIfExtNotLoaded('zlib');
    warnIfExtNotLoaded('pcre');
    warnIfExtNotLoaded('curl');
    warnIfExtNotLoaded('zip');
    warnIfExtNotLoaded('intl');
    $tool_content .= "</ul><h5 class='control-label-notes'>$langOptionalPHP</h5>";
    $tool_content .= "<ul class='list-group list-group-flush'>";
    warnIfExtNotLoaded('soap');
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
    <div class='smaller'>$langBeforeInstall2<a href='$readme_file' target=_blank>$langHere</a>.</div></div><br />
    <div class='col-12 d-flex justify-content-center mt-5'><input type='submit' class='btn w-100' name='install2' value='$langNextStep &raquo;' /></div>" .
            hidden_vars($all_vars) . "</form></div></div>\n";
    draw($tool_content);
} elseif (!$autoinstall) {
    $langLanguages = array(
        'el' => 'Ελληνικά (el)',
        'en' => 'English (en)');

    $tool_content .= "
    <div class='row'>
      <div class='col-sm-12 text-center'>        
        <h3 class='mt-3'>$langWelcomeWizard</h3>
        <div class='col-12 col-md-6 m-auto d-block mt-3'>
          <div class='card panelCard px-lg-4 py-lg-3'>
            <div class='card-header border-0 d-flex justify-content-between align-items-center'>
              <h3>$langThisWizard</h3>
            </div>
            <div class='card-body'>
              <ul class='list-group list-group-flush'>
                  <li class='list-group-item element text-start'>$langWizardHelp1</li>
                  <li class='list-group-item element text-start'>$langWizardHelp2</li>
                  <li class='list-group-item element text-start'>$langWizardHelp3</li>
              </ul>
            </div>
          </div>
        </div>
        <div class='col-12 col-md-6 m-auto d-block mt-3'>
          <div class='card panelCard px-lg-4 py-lg-3'>
            <div class='card-body'>
              <form class='form-horizontal form-wrapper' role='form' method='post' action='$_SERVER[SCRIPT_NAME]'>
                <fieldset>
                  <div class='form-group'>
                    <label for='lang' class='col-sm-12 control-label-notes text-start'>$langChooseLang:</label>
                    <div class='col-sm-12'>" . selection($langLanguages, 'lang', $lang, 'class="form-control" onChange=\"document.langform.submit();\"') . "</div>
                  </div>
                  <div class='form-group mt-4'>
                    <div class='col-12'>
                      <input type='submit' class='btn w-100' name='install1' value='$langNextStep &raquo;'>
                      <input type='hidden' name='welcomeScreen' value='true'>
                    </div>
                  </div>
                <fieldset>
              </form>
            </div>
          </div>
        </div>
      </div>
    </div>";
    draw($tool_content, array('no-menu' => true));
}

function get_base_path() {
    $path = dirname(dirname(__FILE__));
    if (DIRECTORY_SEPARATOR !== '/') {
        return(str_replace(DIRECTORY_SEPARATOR, '/', $path));
    } else {
        return $path;
    }
}

// Create config, courses directories etc.
function create_directories() {
    mkdir_try('config');
    touch_try('config/index.php');
    mkdir_try('storage');
    mkdir_try('storage/views');
    mkdir_try('courses');
    touch_try('courses/index.php');
    mkdir_try('courses/temp');
    touch_try('courses/temp/index.php');
    mkdir_try('courses/temp/pdf');
    mkdir_try('courses/userimg');
    touch_try('courses/userimg/index.php');
    mkdir_try('courses/faculytimg');
    mkdir_try('courses/commondocs');
    touch_try('courses/commondocs/index.php');
    mkdir_try('video');
    touch_try('video/index.php');
    mkdir_try('courses/user_progress_data');
    mkdir_try('courses/user_progress_data/cert_templates');
    touch_try('courses/user_progress_data/cert_templates/index.php');
    mkdir_try('courses/user_progress_data/badge_templates');
    touch_try('courses/user_progress_data/badge_templates/index.php');
    mkdir_try('courses/eportfolio');
    touch_try('courses/eportfolio/index.php');
    mkdir_try('courses/eportfolio/userbios');
    touch_try('courses/eportfolio/userbios/index.php');
    mkdir_try('courses/eportfolio/work_submissions');
    touch_try('courses/eportfolio/work_submissions/index.php');
    mkdir_try('courses/eportfolio/mydocs');
    touch_try('courses/eportfolio/mydocs/index.php');
}

function getenv_default($name, $default) {
    $value = getenv($name);
    if ($value === false) {
        return $default;
    } else {
        return $value;
    }
}
