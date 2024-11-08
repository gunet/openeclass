<?php
/*
 *  ========================================================================
 *  * Open eClass
 *  * E-learning and Course Management System
 *  * ========================================================================
 *  * Copyright 2003-2024, Greek Universities Network - GUnet
 *  *
 *  * Open eClass is an open platform distributed in the hope that it will
 *  * be useful (without any warranty), under the terms of the GNU (General
 *  * Public License) as published by the Free Software Foundation.
 *  * The full license can be read in "/info/license/license_gpl.txt".
 *  *
 *  * Contact address: GUnet Asynchronous eLearning Group
 *  *                  e-mail: info@openeclass.org
 *  * ========================================================================
 *
 */

session_start();
header('Content-Type: text/html; charset=UTF-8');

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

$viewsDir = 'resources/views/install';
$cacheDir = 'storage/views/';
if (!is_dir($cacheDir)) {
    $tempDir = $cacheDir;
    $cacheDir = null;
    if (mkdir($tempDir, 0755, true)) {
        $cacheDir = $tempDir;
    }
}
if (!is_writable($cacheDir) or !$cacheDir) {
    $cacheDir = sys_get_temp_dir() . '/storage';
    if (!(is_dir($cacheDir) or mkdir($cacheDir, 0755, true))) {
        die("Error: Unable to find a writable storage directory - tried '$cacheDir'.");
    }
}

use Jenssegers\Blade\Blade;
$blade = new Blade($viewsDir, $cacheDir);

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
    $lang = $_SESSION['lang'] = $_REQUEST['lang'];
} elseif (isset($_SESSION['lang'])) {
    $lang = $_SESSION['lang'];
} else {
    $lang = 'el';
}
if (!isset($language_codes[$lang])) {
    $lang = 'el';
}

if ($lang == 'el') {
    $data['install_info_file'] = "http://docs.openeclass.org/el/install";
    $data['readme_file'] =  "http://docs.openeclass.org/el/general";
} else {
    $data['install_info_file'] = "http://docs.openeclass.org/en/install";
    $data['readme_file'] = "http://docs.openeclass.org/en/general";
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

$data['lang'] = $lang;
$data['title'] = $langTitleInstall . ' - ' . ECLASS_VERSION;

if (file_exists('config/config.php')) {
  if(get_config('show_always_collaboration') and get_config('show_collaboration')){
    require_once "lang/$lang/messages_collaboration.inc.php";
  }
}
if ($extra_messages) {
    include $extra_messages;
}

// error - config.php exists
if (file_exists('config/config.php')) {
    if ($autoinstall) {
        die("$langWarnConfig1. $langWarnConfig2\n");
    } else {
        echo $blade->make('config_exists')->render();
        exit;
    }
}

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
    $helpdeskmail = $postaddressForm = $homepage_intro = '';
    $theme_selection = 0;
    $eclass_stud_reg = 2;

    // Check for db connection after settings submission
    $mysqlServer = $GLOBALS['dbHostForm'] = $dbHostForm;
    $mysqlUser = $GLOBALS['dbUsernameForm'] = $dbUsernameForm;
    $mysqlPassword = $GLOBALS['dbPassForm'] = $dbPassForm;
}

$all_vars = [
    'dbHostForm',
    'dbUsernameForm',
    'dbNameForm',
    'dbPassForm',
    'dbMyAdmin',
    'urlForm',
    'nameForm',
    'loginForm',
    'passForm',
    'campusForm',
    'helpdeskForm',
    'helpdeskmail',
    'postaddressForm',
    'eclass_stud_reg',
    'emailForm',
    'institutionForm',
    'institutionUrlForm',
    'dont_mail_unverified_mails',
    'email_from',
    'email_announce',
    'email_bounces',
    'email_transport',
    'smtp_server',
    'smtp_port',
    'smtp_encryption',
    'smtp_username',
    'smtp_password',
    'theme_selection',
    'homepage_intro',
    'sendmail_command'
];

// Pass language through post only after welcome screen
if (isset($_SESSION['step'])) {
    $all_vars[] = 'lang';
}

foreach ($all_vars as $name) {
    if (isset($_POST[$name])) {
        $GLOBALS[$name] = $_POST[$name];
    }
}

//db error flags
$data['db_error_db_engine'] = false;
$data['db_error_connection'] = false;
$data['db_error_db_exists'] = false;
$data['db_error_message'] = '';
$data['config_error'] = false;

$data['lang_selection'] = [
    'el' => 'Ελληνικά (el)',
    'en' => 'English (en)',
];
$availableThemes = [
    'Default',
    'Crimson',
    'Emerald',
    'Light Purple Pink',
    'Dark',
    'Dark Purple',
    'Wood',
    'Neutral',
    'Soft_light',
    'Collaboration',
    'Consulting',
    'Elearning Brown',
    'Elearning Brown (small image)',
    'Elearning White Blue',
    'Elearning White Blue (small image)',
    'Elearning Dark White Blue',
    'Elearning Dark White Blue (small image)',
    'Education Light Blue',
    'Education Light Blue (small image)',
    'Education Light Yellow',
    'Education Light Yellow (small image)',
    'Education',
    'School Green White',
    'School Red Blue'
];

$data['user_registration_selection'] = selection(
    [
        '2' => $langUserRegistration,
        '1' => $langReqRegUser,
        '0' => $langDisableEclassStudReg
    ],
    'eclass_stud_reg',
    $langUsersAccount
);

$data['all_vars'] = $all_vars;

if ($autoinstall) {
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

if (isset($_POST['install1'])) { // step 1 requirements
    $data['StepTitle'] = $langRequirements;
    $_SESSION['step'] = 1;
    $configErrorExists = false;

    create_directories();
    $data['configErrorExists'] = $configErrorExists;

    if (!is_null($errorContent)) {
        $data['errorContent'] = implode(' ', $errorContent);
    }

} elseif (isset($_POST['install2'])) { // step 2 license
    $data['StepTitle'] = $langLicense;
    $_SESSION['step'] = 2;
} elseif (isset($_POST['install3'])) { // step 3 mysql database settings
    $data['StepTitle'] = $langDBSetting;
    $_SESSION['step'] = 3;
} elseif (isset($_POST['install4'])) { // step 4 basic config settings
    $data['StepTitle'] = $langBasicCfgSetting;
    $_SESSION['step'] = 4;
    if (empty($helpdeskmail)) {
        $helpdeskmail = '';
    }
    try {
        $mysqlServer = $GLOBALS['dbHostForm'];
        $mysqlUser = $GLOBALS['dbUsernameForm'];
        $mysqlPassword = $GLOBALS['dbPassForm'];
        Debug::setLevel(Debug::ALWAYS);
        Database::core();
        if (!check_engine()) {
            $data['db_error_db_engine'] = true;
            unset($_POST['install4']);
            $_POST['install3'] = true;
        } else {
            $mysqlMainDb = $GLOBALS['mysqlMainDb'] = $dbNameForm;
            try {
                Database::get();
                $data['db_error_db_exists'] = true;
                $data['dbNameForm'] = $GLOBALS['dbNameForm'];
            } catch (Exception $e) {
                // no problem, database doesn't exist
            }
        }
    } catch (Exception $e) {
        $data['db_error_connection'] = true;
        $data['db_error_message'] = $e->getMessage();
        unset($_POST['install4']);
        $_POST['install3'] = true;
    }

} elseif(isset($_POST['install5'])) { // step 5 basic ui
  $data['StepTitle'] = $langThemeSettings;
  $_SESSION['step'] = 5;
  // Get all images from dir screenshots
  $theme_images = '';
  $dir_screens = getcwd();
  $dir_screens = $dir_screens . '/template/modern/images/screenshots';
  $dir_themes_images = scandir($dir_screens);

  foreach($dir_themes_images as $image) {
          $extension = pathinfo($image, PATHINFO_EXTENSION);
          $imgExtArr = ['jpg', 'jpeg', 'png', 'PNG'];
          if (in_array($extension, $imgExtArr)) {
              $theme_images .= "
                  <div class='col-lg-8 col-md-10 m-auto py-4'>
                      <div class='card panelCard card-default h-100'>
                          <div class='card-header border-0 d-flex justify-content-between align-items-center'>
                                <h3>
                                    " . strtok($image, '.') . "
                                </h3>
                          </div>
                          <div class='card-body'>
                              <img style='width:100%; height:auto; object-fit:cover; object-position:50% 50%;' class='card-img-top' src='../template/modern/images/screenshots/$image' alt='Image for current theme'/>
                          </div>
                      </div>
                  </div>";
          }
      }
  $data['theme_images'] = $theme_images;
  $data['theme_selection'] = selection($availableThemes, 'theme_selection', $GLOBALS['theme_selection'], "class='form-select'");
} elseif (isset($_POST['install6'])) { // step 6 email settings
    $data['StepTitle'] = $langEmailSettings;
    $_SESSION['step'] = 6;
} elseif (isset($_POST['install7'])) {// step 7 last check before install
    $data['StepTitle'] = $langLastCheck;
    $_SESSION['step'] = 7;

    switch ($eclass_stud_reg) {
        case '0': $disable_eclass_stud_reg_info = $langDisableEclassStudRegYes;
                    break;
        case '1': $disable_eclass_stud_reg_info = $langDisableEclassStudRegViaReq;
                    break;
        case '2': $disable_eclass_stud_reg_info = $langDisableEclassStudRegNo;
                    break;
    }
    $data['available_theme'] = $availableThemes[$GLOBALS['theme_selection']];

} elseif (isset($_POST['install8'])) { // step 8 install db
    $data['StepTitle'] = $langInstallEnd;
    $_SESSION['step'] = 8;

    foreach ($all_vars as $name) {
        if (isset($_POST[$name])) {
            $name = $_POST[$name];
        } else {
            $name = '';
        }
    }

    $mysqlServer = $dbHostForm;
    $mysqlUser = $dbUsernameForm;
    $mysqlPassword = $dbPassForm;
    $mysqlMainDb = $dbNameForm;
    $phpSysInfoURL = $_POST['dbMyAdmin'];

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
    $selectedTheme = Database::get()->querySingle('SELECT id FROM theme_options WHERE name = ?s', $availableThemes[$_POST['theme_selection']]);
    if($selectedTheme) {
        $selectedThemeId = $selectedTheme->id;
    } else {
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
        $data['config_dir'] = dirname(__DIR__) . '/config';
        $data['config_error'] = true;
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
        }
    }
    $_SESSION['langswitch'] = $lang;
}

$data['installer_menu'] = installer_menu();

echo $blade->make('index', $data)->render();

function get_base_path() {
    $path = dirname(dirname(__FILE__));
    if (DIRECTORY_SEPARATOR !== '/') {
        return(str_replace(DIRECTORY_SEPARATOR, '/', $path));
    } else {
        return $path;
    }
}

/**
 * @brief transfer global variables
 * @param $names
 * @param $form_parameters
 * @return string
 */
function hidden_vars($names, $form_parameters = []) {
    $out = '';
    foreach ($names as $name) {
        if (isset($GLOBALS[$name]) and !(in_array($name, $form_parameters))) {
            $out .= "<input type='hidden' name='$name' value='" . $GLOBALS[$name] . "'>";
        }
    }
    return $out;
}


/**
 * @brief display right menu
 * @return string
 */
function installer_menu()
{
    global $langRequirements, $langLicense, $langDBSetting, $langBasicCfgSetting,
    $langThemeSettings, $langEmailSettings, $langLastCheck, $langInstallEnd;

    $step_messages = [
                      1 => $langRequirements,
                      2 => $langLicense,
                      3 => $langDBSetting,
                      4 => $langBasicCfgSetting,
                      5 => $langThemeSettings,
                      6 => $langEmailSettings,
                      7 => $langLastCheck,
                      8 => $langInstallEnd
                    ];

    $menu = '';
    foreach ($step_messages as $step => $title) {
        if (isset($_SESSION['step']) and $step == $_SESSION['step']) {
            $class = 'active';
        } else {
            $class = '';
        }
        $menu .= "<a href='#' class='list-group-item $class'>";
        $menu .= "<span>$title</span>";
        $menu .= "</a>";
    }
    return $menu;
}
