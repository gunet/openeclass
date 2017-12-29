<?php

/* ========================================================================
 * Open eClass 4.0
 * E-learning and Course Management System
 * ========================================================================
 * Copyright 2003-2014  Greek Universities Network - GUnet
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

/**
 * @file init.php
 * @brief initialisation of variables, includes security checks and serves language switching.
 *        It is included in every file via baseTheme.php
 */

/**
 * Escape HTML entities in a string.
 *
 * Override Blade's e() from vendor/illuminate/support/helpers.php
 *
 * @param  \Illuminate\Contracts\Support\Htmlable|string  $value
 * @return string
 */
function e($value) {
    if ($value instanceof Htmlable) {
        return $value->toHtml();
    }

    return htmlspecialchars($value, ENT_QUOTES, 'UTF-8', false);
}

// set default time zone
date_default_timezone_set("Europe/Athens");
mb_internal_encoding('UTF-8');
$webDir = dirname(dirname(__FILE__));
chdir($webDir);
require 'vendor/autoload.php';
require_once 'include/main_lib.php';

// If session isn't started, start it
if (!session_id()) {
    session_start();
}

header('Content-Type: text/html; charset=UTF-8');

// Will add headers to prevent against clickjacking.
add_framebusting_headers();

add_xxsfilter_headers();

add_nosniff_headers();

//add_hsts_headers();
if (is_readable('config/config.php')) {
    require_once 'config/config.php';
} else {
    require_once 'include/not_installed.php';
}
if (!isset($mysqlServer)) {
    require_once 'include/not_installed.php';
}

// Initialize global debug mechanism
require_once 'modules/admin/debug.php';

// Connect to database
require_once 'modules/db/database.php';

try {
    Database::get();
} catch (Exception $ex) {
    require_once 'include/not_installed.php';
}
require_once 'modules/admin/extconfig/externals.php';
$connector = WafApp::getWaf();
if ($connector->isEnabled() == true ){
    $output = $connector->check();
    if ($output->status == $output::STATUS_BLOCKED){
        WafApp::block($output->output);
    }
}

if (isset($language)) {
    // Old-style config.php, redirect to upgrade
    $language = langname_to_code($language);
    if (isset($_SESSION['langswitch'])) {
        $_SESSION['langswitch'] = langname_to_code($_SESSION['langswitch']);
        $_SESSION['givenname'] = $_SESSION['surname'] = '';
    }
    $session = new Session();
    $uid = $session->user_id;
    $session->active_ui_languages = array($language);
    if (!defined('UPGRADE')) {
        redirect_to_home_page('upgrade/');
    }
} else {
    // Global configuration
    $siteName = get_config('site_name');
    $Institution = get_config('institution');
    $InstitutionUrl = get_config('institution_url');
    $urlServer = get_config('base_url');
    $session = new Session();
    $uid = $session->user_id;
    $language = $session->language;
}
//Initializing Valitron (form validation library)
use Valitron\Validator as V;
V::langDir($webDir.'/vendor/vlucas/valitron/lang'); // always set langDir before lang.
V::lang($language);

// Managing Session Flash Data
if (isset($_SESSION['flash_old'])){
    foreach($_SESSION['flash_old'] as $row){
        unset($_SESSION[$row]);
    }
    unset($_SESSION['flash_old']);
}

if (isset($_SESSION['flash_new'])) {
    $_SESSION['flash_old'] = $_SESSION['flash_new'];
    unset($_SESSION['flash_new']);
}

if (!isset($session)) {
    $session = new Session();
}
$uid = $session->user_id;
// construct $urlAppend from $urlServer
$urlAppend = preg_replace('|^https?://[^/]+/|', '/', $urlServer);
// HTML Purifier
require_once 'include/lib/multimediahelper.class.php';
$purifier = new HTMLPurifier();
$purifier->config->set('Cache.SerializerPath', $webDir . '/courses/temp');
$purifier->config->set('Attr.AllowedFrameTargets', array('_blank'));
$purifier->config->set('HTML.SafeObject', true);
$purifier->config->set('Output.FlashCompat', true);
$purifier->config->set('HTML.FlashAllowFullScreen', true);
// iframes config: http://stackoverflow.com/questions/4739284/htmlpurifier-iframe-vimeo-and-youtube-video
$purifier->config->set('HTML.SafeIframe', true);
$purifier->config->set('URI.SafeIframeRegexp', MultimediaHelper::getPurifierSafeIframeRegexp());
$purifier->config->set('HTML.AllowedElements', array('iframe'));
$purifier->config->set('HTML.AllowedAttributes','iframe@src,iframe@allowfullscreen,iframe@width,iframe@height');
$purifier->config->set('HTML.DefinitionID', 'html5-definitions');
if (($def = $purifier->config->maybeGetRawHTMLDefinition())) {
    // http://htmlpurifier.org/phorum/read.php?2,7417,7417
    $def->addElement('video', 'Block', 'Optional: (source, Flow) | (Flow, source) | Flow', 'Common', array(
      'src' => 'URI',
      'type' => 'Text',
      'width' => 'Length',
      'height' => 'Length',
      'poster' => 'URI',
      'preload' => 'Enum#auto,metadata,none',
      'controls' => 'Text',
    ));
    $def->addElement('source', 'Block', 'Flow', 'Common', array(
      'src' => 'URI',
      'type' => 'Text',
    ));
    // iframes config: http://stackoverflow.com/questions/4739284/htmlpurifier-iframe-vimeo-and-youtube-video
    $def->addAttribute('iframe', 'allowfullscreen', 'Bool');
    $def->addAttribute('iframe', 'width', 'Length');
    $def->addAttribute('iframe', 'height', 'Length');
}
// PHP Math Publisher
require_once 'include/phpmathpublisher/mathpublisher.php';
// temp directory for pclzip
define('PCLZIP_TEMPORARY_DIR', $webDir . '/courses/temp/');

// include_messages
require "$webDir/lang/$language/common.inc.php";
$extra_messages = "config/{$language_codes[$language]}.inc.php";
if (file_exists($extra_messages)) {
    include $extra_messages;
} else {
    $extra_messages = false;
}
require "$webDir/lang/$language/messages.inc.php";
if ($extra_messages) {
    include $extra_messages;
}



if (!isset($_SESSION['csrf_token']) || empty($_SESSION['csrf_token'])) {
    $_SESSION['csrf_token'] = generate_csrf_token();
}

if (($upgrade_begin = get_config('upgrade_begin'))) {
    if (!defined('UPGRADE')) {
        Session::Messages(sprintf($langUpgradeInProgress, format_time_duration(time() - $upgrade_begin)), 'alert-warning');
        if (!isset($guest_allowed) or !$guest_allowed) {
            redirect_to_home_page();
        }
    }
}

// check if we are admin or power user or manageuser_user
if (isset($_SESSION['is_admin']) and $_SESSION['is_admin']) {
    $is_admin = true;
    $is_power_user = true;
    $is_usermanage_user = true;
    $is_departmentmanage_user = true;
} elseif (isset($_SESSION['is_power_user']) and $_SESSION['is_power_user']) {
    $is_power_user = true;
    $is_usermanage_user = true;
    $is_departmentmanage_user = true;
    $is_admin = false;
} elseif (isset($_SESSION['is_usermanage_user']) and $_SESSION['is_usermanage_user']) {
    $is_usermanage_user = true;
    $is_power_user = false;
    $is_admin = false;
    $is_departmentmanage_user = false;
} elseif (isset($_SESSION['is_departmentmanage_user']) and $_SESSION['is_departmentmanage_user']) {
    $is_departmentmanage_user = true;
    $is_usermanage_user = true;
    $is_power_user = false;
    $is_admin = false;
} else {
    $is_admin = false;
    $is_power_user = false;
    $is_usermanage_user = false;
    $is_departmentmanage_user = false;
}

$theme = $_SESSION['theme'] = 'default';
$themeimg = $urlAppend . 'template/' . $theme . '/img';

if (isset($require_login) and $require_login and ! $uid) {
    $toolContent_ErrorExists = $langSessionIsLost;
}

if (isset($require_admin) && $require_admin) {
    if (!($is_admin)) {
        $toolContent_ErrorExists = $langCheckAdmin;
    }
}

if (isset($require_power_user) && $require_power_user) {
    if (!($is_admin or $is_power_user)) {
        $toolContent_ErrorExists = $langCheckPowerUser;
    }
}

if (isset($require_usermanage_user) && $require_usermanage_user) {
    if (!($is_admin or $is_power_user or $is_usermanage_user)) {
        $toolContent_ErrorExists = $langCheckUserManageUser;
    }
}

if (isset($require_departmentmanage_user) && $require_departmentmanage_user) {
    if (!($is_admin or $is_departmentmanage_user)) {
        $toolContent_ErrorExists = $langCheckDepartmentManageUser;
    }
}

if (!isset($guest_allowed) || $guest_allowed != true) {
    if (check_guest()) {
        $toolContent_ErrorExists = $langCheckGuest;
    }
}

if (isset($_SESSION['mail_verification_required']) && !isset($mail_ver_excluded)) {
    // don't redirect to mail verification on logout
    if (!isset($_GET['logout'])) {
        redirect_to_home_page('modules/auth/mail_verify_change.php');
    }
}

// Restore saved old_dbname function
function restore_dbname_override($do_unset = false) {
    if (defined('old_dbname')) {
        $_SESSION['dbname'] = old_dbname;
    } elseif ($do_unset) {
        unset($_SESSION['dbname']);
    }
}

// Temporary dbname override
if (isset($_GET['course'])) {
    if (isset($_SESSION['dbname'])) {
        define('old_dbname', $_SESSION['dbname']);
    }
    $_SESSION['dbname'] = $_GET['course'];
}
register_shutdown_function('restore_dbname_override');

// If $require_current_course is true, initialise course settings
// Read properties of current course
if (isset($require_current_course) and $require_current_course) {
    if (!isset($_SESSION['dbname'])) {
        $toolContent_ErrorExists = $langSessionIsLost;
    } else {
        $dbname = $_SESSION['dbname'];
        Database::get()->queryFunc("SELECT course.id as cid, course.code as code, course.public_code as public_code,
                course.title as title, course.prof_names as prof_names, course.lang as lang,
                course.visible as visible, hierarchy.name AS faculte
                                           FROM course, course_department, hierarchy
                                           WHERE course.id = course_department.course AND
                                                 hierarchy.id = course_department.department AND
                                                 course.code=?s"
                , function ($course_info) use (&$course_id, &$public_code, &$course_code, &$fac, &$titulaires, &$languageInterface, &$visible, &$currentCourseName, &$currentCourseLanguage ) {
            $course_id = $course_info->cid;
            $public_code = $course_info->public_code;
            $course_code = $course_info->code;
            $fac = $course_info->faculte;
            $titulaires = $course_info->prof_names;
            $languageInterface = $course_info->lang;
            $visible = $course_info->visible;
            // New variables
            $currentCourseName = $course_info->title;
            $currentCourseLanguage = $languageInterface;
        }
                , function ($errormsg) use($urlServer) {
            if (defined('M_INIT')) {
                echo RESPONSE_FAILED . " Error: " . $errormsg;
                exit();
            } else {
                restore_dbname_override(true);
                header('Location: ' . $urlServer);
                exit();
            }
        }
                , $dbname);

        if (!isset($course_code) or empty($course_code)) {
            $toolContent_ErrorExists = $langLessonDoesNotExist;
        }

        $fac_lower = strtolower($fac);

        // Check for course visibility by current user
        $status = 0;
        // The admin and power users can see all courses as adminOfCourse
        if ($is_admin or $is_power_user) {
            $status = USER_TEACHER;
        } else {
            $stat = Database::get()->querySingle("SELECT status FROM course_user
                                                           WHERE user_id = ?d AND
                                                           course_id = ?d", $uid, $course_id);
            if ($stat) {
                $status = $stat->status;
            } else {
                // the department manager has rights to the courses of his department(s)
                if ($is_departmentmanage_user && $is_usermanage_user && !$is_power_user && !$is_admin && isset($course_code)) {
                    require_once 'include/lib/hierarchy.class.php';
                    require_once 'include/lib/course.class.php';
                    require_once 'include/lib/user.class.php';

                    $treeObj = new Hierarchy();
                    $courseObj = new Course();
                    $userObj = new User();

                    $atleastone = false;
                    $subtrees = $treeObj->buildSubtrees($userObj->getDepartmentIds($uid));
                    $depIds = $courseObj->getDepartmentIds($course_id);
                    foreach ($depIds as $depId) {
                        if (in_array($depId, $subtrees)) {
                            $atleastone = true;
                            break;
                        }
                    }

                    if ($atleastone) {
                        $status = 1;
                        $is_course_admin = true;
                        $_SESSION['courses'][$course_code] = USER_DEPARTMENTMANAGER;
                    }
                }
            }

        }

        if ($visible != COURSE_OPEN) {
            if (!$uid) {
                $toolContent_ErrorExists = $langNoAdminAccess;
            } elseif ($status == 0 and ( $visible == COURSE_REGISTRATION or $visible == COURSE_CLOSED)) {
                $toolContent_ErrorExists = $langLoginRequired;
            } elseif ($status == 5 and $visible == COURSE_INACTIVE) {
                $toolContent_ErrorExists = $langCheckProf;
            }
        }
        $_SESSION['courses'][$course_code] = $courses[$course_code] = $status;
    }

    # force a specific interface language
    if (!empty($currentCourseLanguage)) {
        $languageInterface = $currentCourseLanguage;
        // If course language is different from global language,
        // include more messages
        if ($language != $languageInterface) {
            $language = $languageInterface;
            // include_messages
            include "lang/$language/common.inc.php";
            $extra_messages = "config/{$language_codes[$language]}.inc.php";
            if (file_exists($extra_messages)) {
                include $extra_messages;
            } else {
                $extra_messages = false;
            }
            include "lang/$language/messages.inc.php";
            if ($extra_messages) {
                include $extra_messages;
            }
        }
    }
}

// get message array for copyright info
require_once "license_info.php";
// ----------------------------------------
// Course modules array
// user modules
// ----------------------------------------
$modules = array(
    MODULE_ID_AGENDA => array('title' => $langAgenda, 'link' => 'agenda', 'image' => 'fa-calendar-o'),
    MODULE_ID_LINKS => array('title' => $langLinks, 'link' => 'link', 'image' => 'fa-link'),
    MODULE_ID_DOCS => array('title' => $langDoc, 'link' => 'document', 'image' => 'fa-folder-open-o'),
    MODULE_ID_VIDEO => array('title' => $langVideo, 'link' => 'video', 'image' => 'fa-film'),
    MODULE_ID_ASSIGN => array('title' => $langWorks, 'link' => 'work', 'image' => 'fa-flask'),
    MODULE_ID_ANNOUNCE => array('title' => $langAnnouncements, 'link' => 'announcements', 'image' => 'fa-bullhorn'),
    MODULE_ID_FORUM => array('title' => $langForums, 'link' => 'forum', 'image' => 'fa-comments'),
    MODULE_ID_EXERCISE => array('title' => $langExercises, 'link' => 'exercise', 'image' => 'fa-pencil-square-o'),
    MODULE_ID_GROUPS => array('title' => $langGroups, 'link' => 'group', 'image' => 'fa-users'),
    MODULE_ID_MESSAGE => array('title' => $langDropBox, 'link' => 'message', 'image' => 'fa-envelope-o'),
    MODULE_ID_GLOSSARY => array('title' => $langGlossary, 'link' => 'glossary', 'image' => 'fa-list'),
    MODULE_ID_EBOOK => array('title' => $langEBook, 'link' => 'ebook', 'image' => 'fa-book'),
    MODULE_ID_CHAT => array('title' => $langChat, 'link' => 'chat', 'image' => 'fa-exchange'),
    MODULE_ID_DESCRIPTION => array('title' => $langCourseDescription, 'link' => 'course_description', 'image' => 'fa-info-circle'),
    MODULE_ID_QUESTIONNAIRE => array('title' => $langQuestionnaire, 'link' => 'questionnaire', 'image' => 'fa-question-circle'),
    MODULE_ID_LP => array('title' => $langLearnPath, 'link' => 'learnPath', 'image' => 'fa-ellipsis-h'),
    MODULE_ID_WIKI => array('title' => $langWiki, 'link' => 'wiki', 'image' => 'fa-wikipedia-w'),
    MODULE_ID_BLOG => array('title' => $langBlog, 'link' => 'blog', 'image' => 'fa-columns'),
    MODULE_ID_WALL => array('title' => $langWall, 'link' => 'wall', 'image' => 'fa-list'),
    MODULE_ID_GRADEBOOK => array('title' => $langGradebook, 'link' => 'gradebook', 'image' => 'fa-sort-numeric-desc'),
    MODULE_ID_ATTENDANCE => array('title' => $langAttendance, 'link' => 'attendance', 'image' => 'fa-check-square-o'),
    MODULE_ID_TC => array('title' => $langBBB, 'link' => 'tc', 'image' => 'fa-exchange'),    
    MODULE_ID_LTI_CONSUMER => array('title' => $langLtiConsumer, 'link' => 'lti_consumer', 'image' => 'fa-link'),
    MODULE_ID_PROGRESS => array('title' => $langProgress, 'link' => 'progress', 'image' => 'fa-trophy'),
    MODULE_ID_MINDMAP => array('title' => $langMindmap, 'link' => 'mindmap', 'image' => 'fa-map'),
    MODULE_ID_REQUEST => array('title' => $langRequests, 'link' => 'request', 'image' => 'fa-ticket')
);
// ----------------------------------------
// course admin modules
// ----------------------------------------
$admin_modules = array(
    MODULE_ID_COURSEINFO => array('title' => $langCourseInfo, 'link' => 'course_info', 'image' => 'fa-cogs'),
    MODULE_ID_USERS => array('title' => $langUsers, 'link' => 'user', 'image' => 'fa-user'),
    MODULE_ID_USAGE => array('title' => $langUsage, 'link' => 'usage', 'image' => 'fa-area-chart'),
    MODULE_ID_COURSE_WIDGETS => array('title' => $langWidgets, 'link' => 'course_widgets', 'image' => 'fa-magic'),
    MODULE_ID_TOOLADMIN => array('title' => $langToolManagement, 'link' => 'course_tools', 'image' => 'fa-cogs'),
    MODULE_ID_ABUSE_REPORT => array('title' => $langAbuseReports, 'link' => 'abuse_report', 'image' => 'fa-flag'),
);
// -------------------------------------------
// modules which can't be enabled or disabled
// -------------------------------------------
$static_modules = array(
    MODULE_ID_USERS => array('title' => $langUsers, 'link' => 'user'),
    MODULE_ID_USAGE => array('title' => $langUsage, 'link' => 'usage'),
    MODULE_ID_COURSEINFO => array('title' => $langCourseInfo, 'link' => 'course_info'),
    MODULE_ID_COURSE_WIDGETS => array('title' => $langWidgets, 'link' => 'course_widgets'),
    MODULE_ID_TOOLADMIN => array('title' => $langCourseTools, 'link' => 'course_tools'),
    MODULE_ID_UNITS => array('title' => $langUnits, 'link' => 'units'),
    MODULE_ID_WEEKS => array('title' => $langCourseWeeklyFormat, 'link' => 'weeks'),
    MODULE_ID_SEARCH => array('title' => $langSearch, 'link' => 'search'),
    MODULE_ID_CONTACT => array('title' => $langContact, 'link' => 'contact'),
    MODULE_ID_COMMENTS => array('title' => $langComments, 'link' => 'comments'),
    MODULE_ID_RATING => array('title' => $langCourseRating, 'link' => 'rating'),
    MODULE_ID_SHARING => array('title' => $langCourseSharing, 'link' => 'sharing'),
    MODULE_ID_ABUSE_REPORT => array('title' => $langAbuseReport, 'link' => 'abuse_report'),
    MODULE_ID_NOTES => array('title' => $langNotes, 'link' => 'notes'));


// the system admin and power users have rights to all courses
if ($is_admin or $is_power_user) {
    $is_course_admin = true;
    if (isset($course_code)) {
        $_SESSION['courses'][$course_code] = USER_TEACHER;
    }
} else {
    $is_course_admin = false;
}

$is_editor = false;
if (isset($_SESSION['courses'])) {
    if (isset($course_code)) {
        if (check_editor()) { // check if user is editor of course
            $is_editor = true;
        }
        if (@$_SESSION['courses'][$course_code] == USER_TEACHER or @$_SESSION['courses'][$course_code] == USER_DEPARTMENTMANAGER) {
            $is_course_admin = true;
            $is_editor = true;
        }
    }
} else {
    unset($status);
}

// Temporary student view
if (isset($_SESSION['student_view'])) {
    if (isset($course_code) and $_SESSION['student_view'] === $course_code) {
        $_SESSION['courses'][$course_code] = $courses[$course_code] = USER_STUDENT;
        $saved_is_editor = $is_editor;
        $is_admin = $is_editor = $is_course_admin = false;
    } else {
        unset($_SESSION['student_view']);
    }
}

$is_opencourses_reviewer = FALSE;
if (get_config('opencourses_enable') && isset($course_code) && check_opencourses_reviewer()) {
    $is_opencourses_reviewer = TRUE;
}

if (isset($require_course_admin) and $require_course_admin) {
    if (!$is_course_admin) {
        $toolContent_ErrorExists = $langCheckCourseAdmin;
    }
}

if (isset($require_editor) and $require_editor) {
    if (!$is_editor) {
        $toolContent_ErrorExists = $langCheckProf;
    }
}

$module_id = current_module_id();

// Security check:: Users must not be able to access inactive (if students) or disabled tools.
if (isset($course_id) and $module_id and !defined('STATIC_MODULE')) {
    if (!$uid or check_guest()) {
        $moduleIDs = Database::get()->queryArray("SELECT module_id FROM course_module
                        WHERE visible = 1 AND
                              course_id = ?d AND
                              module_id NOT IN (SELECT module_id FROM module_disable) AND
                              module_id NOT IN (" . MODULE_ID_CHAT . ",
                                                " . MODULE_ID_ASSIGN . ",
                                                " . MODULE_ID_LTI_CONSUMER . ",
                                                " . MODULE_ID_TC . ",
                                                " . MODULE_ID_MESSAGE . ",
                                                " . MODULE_ID_FORUM . ",
                                                " . MODULE_ID_GROUPS . ",
                                                " . MODULE_ID_GRADEBOOK . ",
                                                " . MODULE_ID_ATTENDANCE . ",
                                                " . MODULE_ID_MINDMAP . ",
                                                " . MODULE_ID_REQUEST . ",
                                                " . MODULE_ID_PROGRESS . ",
                                                " . MODULE_ID_LP . ")", $course_id);
    } elseif ($is_editor) {
        $moduleIDs = Database::get()->queryArray("SELECT module_id FROM course_module
                        WHERE module_id NOT IN (SELECT module_id FROM module_disable) AND
                              course_id = ?d", $course_id);
    } else {
        $moduleIDs = Database::get()->queryArray("SELECT module_id FROM course_module
                        WHERE visible = 1 AND
                              module_id NOT IN (SELECT module_id FROM module_disable) AND
                              course_id = ?d", $course_id);
    }
    $publicModules = array();
    foreach ($moduleIDs as $module) {
        $publicModules[] = $module->module_id;
    }

    if (!in_array($module_id, $publicModules)) {
        $toolContent_ErrorExists = $langCheckPublicTools;
    }
}

set_glossary_cache();

$tool_content = $head_content = '';

