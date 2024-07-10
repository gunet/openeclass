<?php

/* ========================================================================
 * Open eClass 4.0
 * E-learning and Course Management System
 * ========================================================================
 * Copyright 2003-2019  Greek Universities Network - GUnet
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
if (!function_exists('e')) {
    function e($value, $doubleEncode = true) {
        if ($value instanceof Htmlable) {
            return $value->toHtml();
        }
        return htmlspecialchars($value ?? '', ENT_QUOTES, 'UTF-8', $doubleEncode);
    }
}

// set default time zone
date_default_timezone_set("Europe/Athens");
mb_internal_encoding('UTF-8');
$webDir = fix_directory_separator(dirname(dirname(__FILE__)));
chdir($webDir);
require 'vendor/autoload.php';
require_once 'include/main_lib.php';

// If session hasn't started, start it
if (!session_id()) {
    session_start();
}

header('Content-Type: text/html; charset=UTF-8');

// Will add headers to prevent against clickjacking.
//add_framebusting_headers();

add_xxsfilter_headers();

add_nosniff_headers();

//add_hsts_headers();

if (file_exists('config/config.php')) { // read config file
    include_once 'config/config.php';
} else {
    redirect('include/not_installed.php?err_config=true');
}

// appended to JS and CSS links to break caching - changes per second in debug mode
define('CACHE_SUFFIX', ECLASS_VERSION . (defined('DEBUG') && DEBUG ? ('-' . time()): ''));

// Initialize global debug mechanism
require_once 'modules/admin/debug.php';

// Connect to database
require_once 'modules/db/database.php';

try {
    Database::get();
} catch (Exception $ex) { // db credentials are wrong
    redirect('include/not_installed.php?err_db=true');
}

require_once 'modules/admin/extconfig/externals.php';

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
        redirect_to_home_page('upgrade/index.php');
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
require_once 'include/HTMLPurifier_Filter_MyIframe.php';
$purifier = new HTMLPurifier();
$purifier->config->set('Cache.SerializerPath', $webDir . '/courses/temp');
$purifier->config->set('Attr.AllowedFrameTargets', array('_blank'));
$purifier->config->set('HTML.SafeObject', true);
$purifier->config->set('Output.FlashCompat', true);
$purifier->config->set('HTML.FlashAllowFullScreen', true);
$purifier->config->set('Filter.Custom', array(new HTMLPurifier_Filter_MyIframe()));
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
    $def->addElement('audio', 'Block', 'Optional: (source, Flow) | (Flow, source) | Flow', 'Common', array(
        'src' => 'URI',
        'type' => 'Text',
        'preload' => 'Enum#auto,metadata,none',
        'controls' => 'Text',
    ));
    $def->addElement('source', 'Block', 'Flow', 'Common', array(
      'src' => 'URI',
      'type' => 'Text',
    ));
}
// PHP Math Publisher
require_once 'include/phpmathpublisher/mathpublisher.php';
// include_messages
require "$webDir/lang/$language/common.inc.php";
$extra_messages = "config/{$language_codes[$language]}.inc.php";
if (file_exists($extra_messages)) {
    include $extra_messages;
} else {
    $extra_messages = false;
}
require "$webDir/lang/$language/messages.inc.php";
if (file_exists('config/config.php')) {
    if(get_config('show_always_collaboration') and get_config('show_collaboration')){
        require "$webDir/lang/$language/messages_collaboration.inc.php";
    }
}
if ($extra_messages) {
    include $extra_messages;
}



if (!isset($_SESSION['csrf_token']) || empty($_SESSION['csrf_token'])) {
    $_SESSION['csrf_token'] = generate_csrf_token();
}

if (($upgrade_begin = get_config('upgrade_begin'))) {
    if (!defined('UPGRADE')) {
        Session::flash('message', sprintf($langUpgradeInProgress, format_time_duration(time() - $upgrade_begin)));
        Session::flash('alert-class', 'alert-warning');
        if (!isset($guest_allowed) or !$guest_allowed) {
           redirect_to_home_page();
        }
    }
}

// ----------------- sso transition ------------------
if (isset($_SESSION['SSO_USER_TRANSITION']) and !isset($transition_script)) {
    header("Location: {$urlServer}modules/auth/transition/auth_transition.php");
}
// ----------------------------------------------------

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

//Maintenance redirect
if (get_config('maintenance') == 1 ) {
    if (!$is_admin and !defined('MAINTENANCE_PAGE')) {
        redirect_to_home_page('maintenance/');
    }
}

$theme = $_SESSION['theme'] = get_config('theme');
$themeimg = $urlAppend . 'template/' . $theme . '/img';
if (file_exists("template/$theme/settings.php")) {
    require_once "template/$theme/settings.php";
}

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


//Regarding activation of collaboration
$is_enabled_collaboration = false;
if (file_exists('config/config.php')) {
    if(!get_config('show_always_collaboration') and get_config('show_collaboration')){
        $is_enabled_collaboration = true;
    }
    if(get_config('show_always_collaboration') and get_config('show_collaboration')){ //always enabled
        $collaboration_platform = $collaboration_value = 1;
    }else{
        $collaboration_platform = $collaboration_value = 0;
    }
}

// Regarding session docs
$is_session_doc = false;
if(isset($_SESSION['fileSessionId']) and $_SESSION['fileSessionId'] && isset($require_current_course) and $require_current_course){
    $q = Database::get()->querySingle("SELECT course_id FROM mod_session WHERE id = ?d",$_SESSION['fileSessionId']);
    $course_id = $q->course_id;
    $_SESSION['dbname'] = course_id_to_code($course_id);
    $sessionID = $_SESSION['fileSessionId'];
    $is_session_doc = true;
    unset($_SESSION['fileSessionId']);
}

// Regarding uploaded docs by users in a session completion
$user_uploader = 0;
$uploaded_docs_by_users = false;
if(isset($_SESSION['CurrentSessionId']) && $_SESSION['CurrentSessionId'] && isset($require_current_course) && $require_current_course){
    $q = Database::get()->querySingle("SELECT course_id FROM mod_session WHERE id = ?d",$_SESSION['CurrentSessionId']);
    $course_id = $q->course_id;
    $_SESSION['dbname'] = course_id_to_code($course_id);
    $sessionID = $_SESSION['CurrentSessionId'];
    $uploaded_docs_by_users = true;
    $user_uploader = $_SESSION['userId_uploader'];
    unset($_SESSION['CurrentSessionId']);
    unset($_SESSION['userId_uploader']);
}

// If $require_current_course is true, initialise course settings
// Read properties of current course
$is_editor = false;
$is_course_reviewer = false;
$is_coordinator = false;
$is_consultant = false;
$is_simple_user = false;
if (isset($require_current_course) and $require_current_course) {
    if (!isset($_SESSION['dbname'])) {
        $toolContent_ErrorExists = $langSessionIsLost;
    } else {
        $dbname = $_SESSION['dbname'];
        Database::get()->queryFunc("SELECT course.id as cid, course.code as code, course.public_code as public_code,
                course.title as title, course.prof_names as prof_names, course.lang as lang, view_type, course.course_license as course_license,
                course.visible as visible, course.is_collaborative as is_collaborative, hierarchy.name AS faculte
            FROM course
                LEFT JOIN course_department ON course.id = course_department.course
                LEFT JOIN hierarchy ON hierarchy.id = course_department.department
            WHERE course.code = ?s",
            function ($course_info) {
                global $course_id, $public_code, $course_code, $fac, $course_prof_names, $course_view_type,
                    $languageInterface, $visible, $currentCourseName, $currentCourseLanguage, $courseLicense, $is_collaborative_course;
                $course_id = $course_info->cid;
                $public_code = $course_info->public_code;
                $course_code = $course_info->code;
                $fac = $course_info->faculte;
                $courseLicense = $course_info->course_license;
                $course_prof_names = $course_info->prof_names;
                $course_view_type = $course_info->view_type;
                $languageInterface = $course_info->lang;
                $visible = $course_info->visible;
                $currentCourseName = $course_info->title;
                $currentCourseLanguage = $languageInterface;
                $is_collaborative_course = $course_info->is_collaborative;
            },
            function ($errormsg) use($urlServer) {
                if (defined('M_INIT')) {
                    echo RESPONSE_FAILED . " Error: " . $errormsg;
                    exit();
                } else {
                    restore_dbname_override(true);
                    header('Location: ' . $urlServer);
                    exit();
                }
            },
            $dbname);

        if (!isset($course_code) or empty($course_code)) {
            $toolContent_ErrorExists = $langLessonDoesNotExist;
        }

        // Get essential messages when a course is collaborative course
        if(isset($is_collaborative_course) and $is_collaborative_course){
            if (file_exists('config/config.php') && (!get_config('show_always_collaboration') and get_config('show_collaboration'))) {
                include "lang/$language/messages_collaboration.inc.php";
            }
        }

        // Check for course visibility by current user
        $status = 0;
        // The admin and power users can see all courses as adminOfCourse
        if ($is_admin or $is_power_user) {
            $status = USER_TEACHER;
            $is_coordinator = $is_consultant = true;
        } elseif ($uid) {
            $stat = Database::get()->querySingle("SELECT status, tutor, editor, course_reviewer FROM course_user
                                                           WHERE user_id = ?d AND
                                                           course_id = ?d", $uid, $course_id);
            if ($stat) {
                $status = $stat->status;
                $is_editor = $stat->editor;
                $is_course_reviewer = $stat->course_reviewer;
                if($stat->status == USER_STUDENT && $stat->tutor && !$stat->editor && !$stat->course_reviewer){
                    $is_consultant = true;
                    $is_coordinator = false;
                }elseif($stat->status == USER_TEACHER or $is_editor){
                    $is_coordinator = $is_consultant = true;
                }elseif($stat->status == USER_STUDENT && !$stat->tutor && !$stat->editor && !$stat->course_reviewer){
                    $is_simple_user = true;
                    $is_consultant = false;
                    $is_coordinator = false;
                    $is_course_reviewer = false;
                }
            }
            if ($is_departmentmanage_user and isset($course_code)) {
                // the department manager has rights to the courses of his department(s)
                require_once 'include/lib/hierarchy.class.php';
                require_once 'include/lib/course.class.php';
                require_once 'include/lib/user.class.php';

                $treeObj = new Hierarchy();
                $courseObj = new Course();
                $userObj = new User();

                $atleastone = false;
                $subtrees = $treeObj->buildSubtrees($userObj->getAdminDepartmentIds($uid));
                $depIds = $courseObj->getDepartmentIds($course_id);
                foreach ($depIds as $depId) {
                    if (in_array($depId, $subtrees)) {
                        $atleastone = true;
                        break;
                    }
                }

                if ($atleastone) {
                    $status = USER_TEACHER;
                    $is_editor = $is_course_admin = $is_course_reviewer = true;
                    $_SESSION['courses'][$course_code] = USER_DEPARTMENTMANAGER;
                    $is_coordinator = $is_consultant = true;
                }
            }
        }
        if ($visible != COURSE_OPEN) {
            if (!$uid) {
                $toolContent_ErrorExists = $langNoAdminAccess;
            } elseif ($status == 0 and ($visible == COURSE_REGISTRATION or $visible == COURSE_CLOSED) and !@$course_guest_allowed) {
                //Session::Messages($langLoginRequired, 'alert-info');
                Session::flash('message',$langLoginRequired);
                Session::flash('alert-class', 'alert-info');
                redirect_to_home_page('modules/course_home/register.php?course=' . $course_code);
            } elseif ($status != USER_TEACHER and !$is_editor and !$is_course_reviewer and $visible == COURSE_INACTIVE) {
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
            if (file_exists('config/config.php')) {
                if(get_config('show_always_collaboration') and get_config('show_collaboration')){
                    include "lang/$language/messages_collaboration.inc.php";
                }
            }
            if ($extra_messages) {
                include $extra_messages;
            }
        }
    }
}

if (isset($require_user_registration) && $require_user_registration) {
    if (!($is_admin or $is_editor or user_is_registered_to_course($uid, $course_id))) {
        $toolContent_ErrorExists = $langCheckUserRegistration;
    }
}

// get message array for copyright info
require_once "license_info.php";
// ----------------------------------------
// Course modules array
// user modules
// ----------------------------------------

if(isset($is_collaborative_course) and $is_collaborative_course){
    $modules = $modules_collaborations = array(
        MODULE_ID_AGENDA => array('title' => $langAgenda, 'link' => 'agenda', 'image' => 'fa-regular fa-calendar'),
        MODULE_ID_LINKS => array('title' => $langLinks, 'link' => 'link', 'image' => 'fa-solid fa-link'),
        MODULE_ID_DOCS => array('title' => $langDoc, 'link' => 'document', 'image' => 'fa-regular fa-folder'),
        MODULE_ID_VIDEO => array('title' => $langVideo, 'link' => 'video', 'image' => 'fa-solid fa-film'),
        MODULE_ID_ANNOUNCE => array('title' => $langAnnouncements, 'link' => 'announcements', 'image' => 'fa-regular fa-bell'),
        MODULE_ID_FORUM => array('title' => $langForums, 'link' => 'forum', 'image' => 'fa-regular fa-comment'),
        MODULE_ID_GROUPS => array('title' => $langGroups, 'link' => 'group', 'image' => 'fa-solid fa-user-group'),
        MODULE_ID_MESSAGE => array('title' => $langDropBox, 'link' => 'message', 'image' => 'fa-regular fa-envelope'),
        MODULE_ID_CHAT => array('title' => $langChat, 'link' => 'chat', 'image' => 'fa-regular fa-comment-dots'),
        MODULE_ID_QUESTIONNAIRE => array('title' => $langQuestionnaire, 'link' => 'questionnaire', 'image' => 'fa-solid fa-question'),
        MODULE_ID_WALL => array('title' => $langWall, 'link' => 'wall', 'image' => 'fa-solid fa-quote-left'),
        MODULE_ID_TC => array('title' => $langBBB, 'link' => 'tc', 'image' => 'fa-solid fa-users-rectangle'),
        MODULE_ID_REQUEST => array('title' => $langRequests, 'link' => 'request', 'image' => 'fa-regular fa-clipboard'),
        MODULE_ID_ASSIGN => array('title' => $langWorks, 'link' => 'work', 'image' => 'fa-solid fa-upload'),
        MODULE_ID_GRADEBOOK => array('title' => $langGradebook, 'link' => 'gradebook', 'image' => 'fa-solid fa-a'),
        MODULE_ID_ATTENDANCE => array('title' => $langAttendance, 'link' => 'attendance', 'image' => 'fa-solid fa-clipboard-user'),
        MODULE_ID_SESSION => array('title' => $langSession, 'link' => 'session', 'image' => 'fa-solid fa-handshake')

    );
}else{
    $modules = array(
        MODULE_ID_AGENDA => array('title' => $langAgenda, 'link' => 'agenda', 'image' => 'fa-regular fa-calendar'),
        MODULE_ID_LINKS => array('title' => $langLinks, 'link' => 'link', 'image' => 'fa-solid fa-link'),
        MODULE_ID_DOCS => array('title' => $langDoc, 'link' => 'document', 'image' => 'fa-regular fa-folder'),
        MODULE_ID_VIDEO => array('title' => $langVideo, 'link' => 'video', 'image' => 'fa-solid fa-film'),
        MODULE_ID_ASSIGN => array('title' => $langWorks, 'link' => 'work', 'image' => 'fa-solid fa-upload'),
        MODULE_ID_ANNOUNCE => array('title' => $langAnnouncements, 'link' => 'announcements', 'image' => 'fa-regular fa-bell'),
        MODULE_ID_FORUM => array('title' => $langForums, 'link' => 'forum', 'image' => 'fa-regular fa-comment'),
        MODULE_ID_EXERCISE => array('title' => $langExercises, 'link' => 'exercise', 'image' => 'fa-solid fa-file-pen'),
        MODULE_ID_GROUPS => array('title' => $langGroups, 'link' => 'group', 'image' => 'fa-solid fa-user-group'),
        MODULE_ID_MESSAGE => array('title' => $langDropBox, 'link' => 'message', 'image' => 'fa-regular fa-envelope'),
        MODULE_ID_GLOSSARY => array('title' => $langGlossary, 'link' => 'glossary', 'image' => 'fa-solid fa-list-ul'),
        MODULE_ID_EBOOK => array('title' => $langEBook, 'link' => 'ebook', 'image' => 'fa-solid fa-book-atlas'),
        MODULE_ID_CHAT => array('title' => $langChat, 'link' => 'chat', 'image' => 'fa-regular fa-comment-dots'),
        MODULE_ID_QUESTIONNAIRE => array('title' => $langQuestionnaire, 'link' => 'questionnaire', 'image' => 'fa-solid fa-question'),
        MODULE_ID_LP => array('title' => $langLearnPath, 'link' => 'learnPath', 'image' => 'fa-solid fa-timeline'),
        MODULE_ID_WIKI => array('title' => $langWiki, 'link' => 'wiki', 'image' => 'fa-solid fa-w'),
        MODULE_ID_BLOG => array('title' => $langBlog, 'link' => 'blog', 'image' => 'fa-solid fa-globe'),
        MODULE_ID_WALL => array('title' => $langWall, 'link' => 'wall', 'image' => 'fa-solid fa-quote-left'),
        MODULE_ID_GRADEBOOK => array('title' => $langGradebook, 'link' => 'gradebook', 'image' => 'fa-solid fa-a'),
        MODULE_ID_ATTENDANCE => array('title' => $langAttendance, 'link' => 'attendance', 'image' => 'fa-solid fa-clipboard-user'),
        MODULE_ID_TC => array('title' => $langBBB, 'link' => 'tc', 'image' => 'fa-solid fa-users-rectangle'),
        MODULE_ID_PROGRESS => array('title' => $langProgress, 'link' => 'progress', 'image' => 'fa-solid fa-arrow-trend-up'),
        MODULE_ID_REQUEST => array('title' => $langRequests, 'link' => 'request', 'image' => 'fa-regular fa-clipboard'),
        MODULE_ID_H5P => array('title' => $langH5p, 'link' => 'h5p', 'image' => 'fa-solid fa-arrow-pointer')

    );
}

$icons_map = array(
    'icon_map' => array(
        MODULE_ID_ANNOUNCE => 'fa-regular fa-bell',
        MODULE_ID_AGENDA => 'fa-regular fa-calendar',
        MODULE_ID_DOCS => 'fa-regular fa-folder',
        MODULE_ID_LINKS => 'fa-solid fa-link',
        MODULE_ID_FORUM => 'fa-regular fa-comment',
        MODULE_ID_ASSIGN => 'fa-solid fa-upload',
        MODULE_ID_EXERCISE => 'fa-solid fa-file-pen',
        MODULE_ID_QUESTIONNAIRE => 'fa-solid fa-question',
        MODULE_ID_EBOOK => 'fa-solid fa-book-atlas',
        MODULE_ID_VIDEO => 'fa-solid fa-film',
        MODULE_ID_GROUPS => 'fa-solid fa-user-group',
        MODULE_ID_LP => 'fa-solid fa-timeline',
        MODULE_ID_TC => 'fa-solid fa-users-rectangle',
        MODULE_ID_GLOSSARY => 'fa-solid fa-list-ul',
        MODULE_ID_WIKI => 'fa-solid fa-w',
        MODULE_ID_BLOG => 'fa-solid fa-globe',
        MODULE_ID_ATTENDANCE => 'fa-solid fa-clipboard-user',
        MODULE_ID_GRADEBOOK => 'fa-solid fa-a',
        MODULE_ID_SESSION => 'fa-solid fa-handshake',
    ),
);

// ----------------------------------------
// Course activities array
// user activities
// ----------------------------------------
$activities = array(
    MODULE_ID_EBOOK_READ => array('title' => $langFCEbook, 'tools' => array(MODULE_ID_EBOOK, MODULE_ID_GLOSSARY, MODULE_ID_LINKS, MODULE_ID_DOCS, MODULE_ID_WALL,MODULE_ID_H5P)),
    MODULE_ID_VIDEO_WATCH => array('title' => $langFCVideo,'tools' => array(MODULE_ID_VIDEO, MODULE_ID_WALL, MODULE_ID_LINKS,MODULE_ID_H5P)),
    MODULE_ID_VIDEO_INTERACTION => array('title' => $langFCVideoInteract,'tools' => array(MODULE_ID_LINKS, MODULE_ID_WALL,MODULE_ID_H5P)),
    MODULE_ID_REVISION => array('title' => $langFCRevision,'tools' => array(MODULE_ID_LP, MODULE_ID_MINDMAP,MODULE_ID_H5P)),
    MODULE_ID_GAMES => array('title' => $langFCGames,'tools' => array(MODULE_ID_LINKS,MODULE_ID_H5P)),
    MODULE_ID_DISCUSS => array('title' => $langFCDiscuss,'tools' => array(MODULE_ID_FORUM,
                                                                          MODULE_ID_CHAT,
                                                                          MODULE_ID_LINKS,
                                                                          MODULE_ID_BLOG,
                                                                          MODULE_ID_WALL,
                                                                          MODULE_ID_WIKI,
                                                                          MODULE_ID_TC,
                                                                          MODULE_ID_H5P)),
    MODULE_ID_PROJECT => array('title' => $langFCProject,'tools' => array(MODULE_ID_ASSIGN,MODULE_ID_EXERCISE, MODULE_ID_WIKI, MODULE_ID_LINKS,MODULE_ID_H5P)),
    MODULE_ID_BRAINSTORMING => array('title' => $langFCBrainstorming,'tools' => array(MODULE_ID_WALL, MODULE_ID_BLOG, MODULE_ID_FORUM, MODULE_ID_CHAT,MODULE_ID_H5P)),
    MODULE_ID_WORK_PAPER => array('title' => $langFCWorkPaper,'tools' => array(MODULE_ID_DOCS,
                                                                               MODULE_ID_WALL,
                                                                               MODULE_ID_EXERCISE,
                                                                               MODULE_ID_LP,
                                                                               MODULE_ID_LINKS,MODULE_ID_H5P)),
    MODULE_ID_ROLE_PLAY => array('title' => $langFCRolePlay,'tools' => array(MODULE_ID_FORUM,
                                                                             MODULE_ID_CHAT,
                                                                             MODULE_ID_LINKS,
                                                                             MODULE_ID_BLOG,
                                                                             MODULE_ID_WALL,
                                                                             MODULE_ID_WIKI,
                                                                             MODULE_ID_TC,MODULE_ID_H5P)),
    MODULE_ID_SIMULATE => array('title' => $langFCSimulate,'tools' => array(MODULE_ID_LINKS,MODULE_ID_H5P)),
    MODULE_ID_PROBLEM_SOLVING => array('title' => $langFCProblemSolving,'tools' => array(MODULE_ID_ASSIGN, MODULE_ID_WIKI, MODULE_ID_EXERCISE,MODULE_ID_H5P)),
    MODULE_ID_MINDMAP_FC => array('title' => $langFCMindMap,'tools' => array(MODULE_ID_MINDMAP,MODULE_ID_H5P)),
    MODULE_ID_EVALUATE=> array('title' => $langFCEvaluate,'tools' => array(MODULE_ID_QUESTIONNAIRE, MODULE_ID_LINKS, MODULE_ID_PROGRESS, MODULE_ID_GRADEBOOK,MODULE_ID_H5P)),
    MODULE_ID_DISCUSS_AC => array('title' => $langFCDiscuss,'tools' => array(MODULE_ID_FORUM,
                                                                             MODULE_ID_CHAT,
                                                                             MODULE_ID_LINKS,
                                                                             MODULE_ID_BLOG,
                                                                             MODULE_ID_WALL,
                                                                             MODULE_ID_WIKI,
                                                                             MODULE_ID_TC,
                                                                             MODULE_ID_COMMENTS,
                                                                             MODULE_ID_H5P)),
    MODULE_ID_DIGITAL_STORYTELLING => array('title' => $langFCDigitalStorytelling,'tools' => array(MODULE_ID_LINKS,MODULE_ID_H5P)),
    MODULE_ID_SUPPORTING_MATERIAL => array('title' => $langFCSupportingMaterial,'tools' => array(MODULE_ID_LINKS,
                                                                             MODULE_ID_DOCS,
                                                                             MODULE_ID_H5P))
);

// ----------------------------------------
// course admin modules
// ----------------------------------------
$admin_modules = array(
    MODULE_ID_COURSEINFO => array('title' => $langCourseInfo, 'link' => 'course_info', 'image' => 'fa-cogs'),
    MODULE_ID_USERS => array('title' => $langUsers, 'link' => 'user', 'image' => 'fa-user'),
    MODULE_ID_USAGE => array('title' => $langUsage, 'link' => 'usage', 'image' => 'fa-area-chart'),
    MODULE_ID_COURSE_WIDGETS => array('title' => $langWidgets, 'link' => 'course_widgets', 'image' => 'fa-magic'),
    MODULE_ID_TOOLADMIN => array('title' => $langToolManagement, 'link' => 'course_tools', 'image' => 'fa-wrench'),
    MODULE_ID_ABUSE_REPORT => array('title' => $langAbuseReports, 'link' => 'abuse_report', 'image' => 'fa-flag'),
    MODULE_ID_COURSEPREREQUISITE => array('title' => $langCoursePrerequisites, 'link' => 'course_prerequisites', 'image' => 'fa-university'),
    MODULE_ID_LTI_CONSUMER => array('title' => $langLtiConsumer, 'link' => 'lti_consumer', 'image' => 'fa-link'),
    MODULE_ID_ANALYTICS => array('title' => $langLearningAnalytics, 'link' => 'analytics', 'image' => 'fa-line-chart')
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
    MODULE_ID_SEARCH => array('title' => $langSearch, 'link' => 'search'),
    MODULE_ID_CONTACT => array('title' => $langContact, 'link' => 'contact'),
    MODULE_ID_COMMENTS => array('title' => $langComments, 'link' => 'comments'),
    MODULE_ID_RATING => array('title' => $langCourseRating, 'link' => 'rating'),
    MODULE_ID_SHARING => array('title' => $langCourseSharing, 'link' => 'sharing'),
    MODULE_ID_ABUSE_REPORT => array('title' => $langAbuseReport, 'link' => 'abuse_report'),
    MODULE_ID_NOTES => array('title' => $langNotes, 'link' => 'notes'));


// -------------------------------------------
// modules for offline course
// -------------------------------------------
if(isset($is_collaborative_course) and $is_collaborative_course){
    $offline_course_modules = array(
       /*MODULE_ID_AGENDA => array('title' => $langAgenda, 'link' => 'agenda', 'image' => 'fa-calendar'), */
        MODULE_ID_LINKS => array('title' => $langLinks, 'link' => 'link', 'image' => 'fa-solid fa-link'),
        MODULE_ID_DOCS => array('title' => $langDoc, 'link' => 'document', 'image' => 'fa-regular fa-folder'),
        MODULE_ID_VIDEO => array('title' => $langVideo, 'link' => 'video', 'image' => 'fa-film'),
        MODULE_ID_ANNOUNCE => array('title' => $langAnnouncements, 'link' => 'announcements', 'image' => 'fa-regular fa-bell')
    );
}else{
    $offline_course_modules = array(
        /*MODULE_ID_AGENDA => array('title' => $langAgenda, 'link' => 'agenda', 'image' => 'fa-calendar'), */
        MODULE_ID_LINKS => array('title' => $langLinks, 'link' => 'link', 'image' => 'fa-solid fa-link'),
        MODULE_ID_DOCS => array('title' => $langDoc, 'link' => 'document', 'image' => 'fa-regular fa-folder'),
        MODULE_ID_VIDEO => array('title' => $langVideo, 'link' => 'video', 'image' => 'fa-film'),
        MODULE_ID_ANNOUNCE => array('title' => $langAnnouncements, 'link' => 'announcements', 'image' => 'fa-regular fa-bell'),
        MODULE_ID_EXERCISE => array('title' => $langExercises, 'link' => 'exercise', 'image' => 'fa-solid fa-file-pen'),
        MODULE_ID_GLOSSARY => array('title' => $langGlossary, 'link' => 'glossary', 'image' => 'fa-solid fa-list-ul'),
        /*MODULE_ID_EBOOK => array('title' => $langEBook, 'link' => 'ebook', 'image' => 'fa-book'), */
    /*MODULE_ID_WIKI => array('title' => $langWiki, 'link' => 'wiki', 'image' => 'fa-wikipedia'),*/
    /*MODULE_ID_BLOG => array('title' => $langBlog, 'link' => 'blog', 'image' => 'fa-columns')*/
    );
}

// --------------------------------------------------
// deprecated modules (used ONLY for old statistics)
// --------------------------------------------------
$deprecated_modules = array(
    MODULE_ID_DESCRIPTION => array('title' => $langCourseDescription, 'link' => 'course_description', 'image' => 'fa-info-circle'),
    MODULE_ID_LTI_CONSUMER => array('title' => $langLtiConsumer, 'link' => '', 'image' => '')
);

// the system admin and power users have rights to all courses
if ($is_admin or $is_power_user) {
    $is_course_admin = true;
    if (isset($course_code)) {
        $_SESSION['courses'][$course_code] = USER_TEACHER;
    }
} else {
    $is_course_admin = false;
}

if (isset($_SESSION['courses'])) {
    if (isset($course_code)) {
        if (check_course_reviewer()) { // check if user course reviewer
            $is_course_reviewer = true;
        }
        if (check_editor()) { // check if user is editor of course
            $is_editor = true;
            $is_course_reviewer = true;
        }
        if (@$_SESSION['courses'][$course_code] == USER_TEACHER or @$_SESSION['courses'][$course_code] == USER_DEPARTMENTMANAGER) {
            $is_course_admin = true;
            $is_editor = true;
            $is_course_reviewer = true;
            $is_coordinator = true;
            $is_consultant = true;
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
        $is_admin = $is_editor = $is_course_admin = $is_course_reviewer = $is_coordinator = $is_consultant = false;
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

if (isset($require_course_reviewer) and $require_course_reviewer) {
    if (!$is_course_reviewer) {
        $toolContent_ErrorExists = $langCheckProf;
    }
}

if(isset($require_consultant) and $require_consultant){
    if(!$is_consultant){
        $toolContent_ErrorExists = $langCheckProf;
    }
}

$module_id = current_module_id();

// disable collaboration's modules
if (file_exists('config/config.php') && get_config('show_collaboration')){
    $sizeCheck = Database::get()->queryArray('SELECT * FROM module_disable_collaboration LIMIT 1');
    if (!$sizeCheck) {
        $tools_to_disable = [
            MODULE_ID_MINDMAP,
            MODULE_ID_PROGRESS,
            MODULE_ID_LP,
            MODULE_ID_EXERCISE,
            MODULE_ID_GLOSSARY,
            MODULE_ID_EBOOK,
            MODULE_ID_WIKI,
            MODULE_ID_ABUSE_REPORT,
            MODULE_ID_COURSEPREREQUISITE,
            MODULE_ID_LTI_CONSUMER,
            MODULE_ID_ANALYTICS,
            MODULE_ID_H5P,
            MODULE_ID_COURSE_WIDGETS];
        $optArray = implode(', ', array_fill(0, count($tools_to_disable), '(?d)'));
        Database::get()->query('INSERT INTO module_disable_collaboration (module_id) VALUES ' . $optArray,
            $tools_to_disable);
    }
}

// Security check:: Users must not be able to access inactive (if students) or disabled tools.
if (isset($course_id) and $module_id and !defined('STATIC_MODULE')) {

    $table_modules = '';
    if(isset($is_collaborative_course) and $is_collaborative_course){
        $table_modules = 'module_disable_collaboration';
    }else{
        $table_modules = 'module_disable';
    }

    if ($is_course_reviewer or $is_editor) {
        $moduleIDs = Database::get()->queryArray("SELECT module_id FROM course_module
                    WHERE module_id NOT IN (SELECT module_id FROM $table_modules) AND
                            course_id = ?d", $course_id);

    } elseif (!$uid or check_guest()) {
            $moduleIDs = Database::get()->queryArray("SELECT module_id FROM course_module
                            WHERE visible = 1 AND
                                course_id = ?d AND
                                module_id NOT IN (SELECT module_id FROM $table_modules) AND
                                module_id NOT IN (" . MODULE_ID_CHAT . ",
                                                    " . MODULE_ID_ASSIGN . ",
                                                    " . MODULE_ID_LTI_CONSUMER . ",
                                                    " . MODULE_ID_TC . ",
                                                    " . MODULE_ID_MESSAGE . ",
                                                    " . MODULE_ID_FORUM . ",
                                                    " . MODULE_ID_GROUPS . ",
                                                    " . MODULE_ID_GRADEBOOK . ",
                                                    " . MODULE_ID_ATTENDANCE . ",
                                                    " . MODULE_ID_REQUEST . ",
                                                    " . MODULE_ID_PROGRESS . ",
                                                    " . MODULE_ID_LP . ")", $course_id);
    } else {
            $moduleIDs = Database::get()->queryArray("SELECT module_id FROM course_module
                            WHERE visible = 1 AND
                                module_id NOT IN (SELECT module_id FROM $table_modules) AND
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

$tinymce_color_text = '#687DA3';
get_tinymce_color_text();

// Regarding the course reviewer in a session 
if(isset($is_collaborative_course) and $is_collaborative_course){
    if($is_coordinator){
        $is_course_reviewer = true;
    }elseif($is_course_reviewer){
        $is_consultant = false;
        $is_coordinator = true;
    }
}


function fix_directory_separator($path) {
    if (DIRECTORY_SEPARATOR !== '/') {
        return(str_replace(DIRECTORY_SEPARATOR, '/', $path));
    } else {
        return $path;
    }
}
