<?php

/* ========================================================================
 * Open eClass 3.0
 * E-learning and Course Management System
 * ========================================================================
 * Copyright 2003-2012  Greek Universities Network - GUnet
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
 * Tool Component
 *
 * @author Evelthon Prodromou <eprodromou@upnet.gr>
 * @version $Id$
 *
 * @abstract This component creates an array of the tools that are displayed on the left
 * side column .
 *
 */

require_once 'modules/bbb/functions.php';
require_once 'modules/dropbox/class.mailbox.php';

/*
 * Function getSideMenu
 *
 * Offers an upper-layer logic. Decides what function should be called to
 * create the needed tools array
 *
 * @param int $menuTypeID Type of menu to generate
 *
 */

function getSideMenu($menuTypeID) {

    switch ($menuTypeID) {
        case 0: { //logged out
                $menu = loggedOutMenu();
                break;
            }

        case 1: { //logged in
                $menu = loggedInMenu();
                break;
            }

        case 2: { //course home (lesson tools)
                $menu = lessonToolsMenu();
                break;
            }

        case 3: { // admin tools
                $menu = adminMenu();
                break;
            }

        case 4: { // custom tools
                $menu = customMenu();
                break;
            }

        case 5: { // tools when embedded in tinymce
                $menu = pickerMenu();
                break;
            }
    }
    return $menu;
}

/*
 * Function getToolsArray
 *
 * Queries the database for tool information in accordance
 * to the parameter passed.
 *
 * @param string $cat Type of lesson tools
 * @return array of course_module objects
 * @see function lessonToolsMenu
 */

function getToolsArray($cat) {
    global $currentCourse, $course_code;

    $currentCourse = $course_code;
    $cid = course_code_to_id($currentCourse);

    switch ($cat) {
        case 'Public':
            $sql = "SELECT * FROM course_module
                        WHERE visible = 1 AND
                        course_id = $cid AND
                        module_id NOT IN (" . MODULE_ID_CHAT . ",
                                          " . MODULE_ID_ASSIGN . ",
                                          " . MODULE_ID_DROPBOX . ",
                                          " . MODULE_ID_QUESTIONNAIRE . ",
                                          " . MODULE_ID_FORUM . ",
                                          " . MODULE_ID_GROUPS . ",
                                          " . MODULE_ID_WIKI . ",
                                          " . MODULE_ID_ATTENDANCE . ",
                                          " . MODULE_ID_GRADEBOOK . ",
                                          " . MODULE_ID_LP . ") AND
                        module_id NOT IN (SELECT module_id FROM module_disable)";
            if (!check_guest()) {
                if (isset($_SESSION['uid']) and $_SESSION['uid']) {
                    $result = Database::get()->queryArray("SELECT * FROM course_module
                            WHERE visible = 1 AND
                                  module_id NOT IN (SELECT module_id FROM module_disable) AND
                            course_id = ?d", $cid);
                } else {
                    $result = Database::get()->queryArray($sql);
                }
            } else {
                $result = Database::get()->queryArray($sql);
            }
            break;
        case 'PublicButHide':
            $result = Database::get()->queryArray("SELECT * FROM course_module
                                         WHERE visible = 0 AND
                                               module_id NOT IN (SELECT module_id FROM module_disable) AND
                                         course_id = ?d", $cid);
            break;
    }
    return $result;
}


/**
 * get course external links
 * @global type $course_id
 * @return boolean
 */
function getExternalLinks() {

    global $course_id;

    $result = Database::get()->queryArray("SELECT url, title FROM link
                                            WHERE category = -1 AND
                                        course_id = ?d", $course_id);
    if ($result) {
        return $result;
    } else {
        return false;
    }
}

/**
 * Function loggedInMenu
 *
 * Creates a multi-dimensional array of the user's tools
 * when the user is signed in, and not at a lesson specific tool,
 * in regard to the user's user level.
 *
 * (student | professor | platform administrator)
 *
 * @return array
 */
function loggedInMenu() {
    global $uid, $is_admin, $is_power_user, $is_usermanage_user,
    $is_departmentmanage_user, $urlServer, $course_code;

    $sideMenuGroup = array();

    if ((isset($is_admin) and $is_admin) or
            (isset($is_power_user) and $is_power_user) or
            (isset($is_usermanage_user) and ($is_usermanage_user)) or
            (isset($is_departmentmanage_user) and $is_departmentmanage_user)) {
        $sideMenuSubGroup = array();
        $sideMenuText = array();
        $sideMenuLink = array();
        $sideMenuImg = array();

        $arrMenuType = array();
        $arrMenuType['type'] = 'text';
        $arrMenuType['text'] = $GLOBALS['langAdminOptions'];
        $arrMenuType['class'] = 'admin';
        array_push($sideMenuSubGroup, $arrMenuType);

        if ((isset($is_admin) and $is_admin) or
                (isset($is_power_user) and $is_power_user) or
                (isset($is_usermanage_user) and ($is_usermanage_user)) or
                (isset($is_departmentmanage_user) and $is_departmentmanage_user)) {
            array_push($sideMenuText, "$GLOBALS[langAdminTool]");
            array_push($sideMenuLink, $urlServer . 'modules/admin/');
        }

        array_push($sideMenuImg, "arrow.png");

        array_push($sideMenuSubGroup, $sideMenuText);
        array_push($sideMenuSubGroup, $sideMenuLink);
        array_push($sideMenuSubGroup, $sideMenuImg);
        array_push($sideMenuGroup, $sideMenuSubGroup);
    }

    $sideMenuSubGroup = array();
    $sideMenuText = array();
    $sideMenuLink = array();
    $sideMenuImg = array();

    $arrMenuType = array();
    $arrMenuType['type'] = 'text';
    $arrMenuType['text'] = $GLOBALS['langBasicOptions'];
    $arrMenuType['class'] = 'basic';
    array_push($sideMenuSubGroup, $arrMenuType);

    array_push($sideMenuText, $GLOBALS['langListCourses']);
    array_push($sideMenuLink, $urlServer . "modules/auth/courses.php");
    array_push($sideMenuImg, "fa-graduation-cap");

    array_push($sideMenuText, $GLOBALS['langManuals']);
    array_push($sideMenuLink, $urlServer . "info/manual.php");
    array_push($sideMenuImg, "fa-file-video-o");

    array_push($sideMenuText, $GLOBALS['langPlatformIdentity']);
    array_push($sideMenuLink, $urlServer . "info/about.php");
    array_push($sideMenuImg, "fa-credit-card");

    array_push($sideMenuText, $GLOBALS['langContact']);
    array_push($sideMenuLink, $urlServer . "info/contact.php");
    array_push($sideMenuImg, "fa-phone");

    array_push($sideMenuSubGroup, $sideMenuText);
    array_push($sideMenuSubGroup, $sideMenuLink);
    array_push($sideMenuSubGroup, $sideMenuImg);
    array_push($sideMenuGroup, $sideMenuSubGroup);

    $sideMenuSubGroup = array();
    $sideMenuText = array();
    $sideMenuLink = array();
    $sideMenuImg = array();

    $arrMenuType = array();
    $arrMenuType['type'] = 'text';
    $arrMenuType['text'] = $GLOBALS['langUserOptions'];
    $arrMenuType['class'] = 'user';
    array_push($sideMenuSubGroup, $arrMenuType);

    $res2 = Database::get()->querySingle("SELECT status FROM user WHERE id = ?d", $uid);
    if ($res2) {
        $status = $res2->status;
    }
    if ((isset($status) and ($status === USER_TEACHER)) || $is_departmentmanage_user) {
        array_push($sideMenuText, $GLOBALS['langCourseCreate']);
        array_push($sideMenuLink, $urlServer . "modules/create_course/create_course.php");
        array_push($sideMenuImg, "fa-plus-circle");
    }

    require_once 'modules/dropbox/class.mailbox.php';

    $mbox = new Mailbox($uid, 0);
    $new_msgs = $mbox->unreadMsgsNumber();
    if ($new_msgs == 0) {
        array_push($sideMenuText, $GLOBALS['langMyDropBox']);
    } else {
        array_push($sideMenuText, "<b>".$GLOBALS['langMyDropBox']." (".$new_msgs.")</b>");
    }
    array_push($sideMenuLink, $urlServer . "modules/dropbox/index.php");
    array_push($sideMenuImg, "fa-envelope-o");

    array_push($sideMenuText, $GLOBALS['langMyPersoLessons']);
    array_push($sideMenuLink, $urlServer . "main/my_courses.php");
    array_push($sideMenuImg, "fa-graduation-cap");

    array_push($sideMenuText, $GLOBALS['langMyAnnouncements']);
    array_push($sideMenuLink, $urlServer . "modules/announcements/myannouncements.php");
    array_push($sideMenuImg, "fa-bullhorn");

    array_push($sideMenuText, $GLOBALS['langMyAgenda']);
    array_push($sideMenuLink, $urlServer . "main/personal_calendar/index.php");
    array_push($sideMenuImg, "fa-calendar");

    array_push($sideMenuText, $GLOBALS['langNotes']);
    array_push($sideMenuLink, $urlServer . "main/notes/index.php");
    array_push($sideMenuImg, "fa-edit");

    if (isset($status) and ($status == USER_STUDENT)) {
        array_push($sideMenuText, $GLOBALS['langGradeTotal']);
        array_push($sideMenuLink, $urlServer . "main/gradebookUserTotal/index.php");
        array_push($sideMenuImg, "gradebook");
    }

    if (get_config('personal_blog')) {
        array_push($sideMenuText, $GLOBALS['langMyBlog']);
        array_push($sideMenuLink, $urlServer . "modules/blog/index.php");
        array_push($sideMenuImg, "blog");
    }
    
    array_push($sideMenuText, $GLOBALS['langMyProfile']);
    array_push($sideMenuLink, $urlServer . "main/profile/display_profile.php");
    array_push($sideMenuImg, "fa-user");

    array_push($sideMenuText, $GLOBALS['langMyStats']);
    array_push($sideMenuLink, $urlServer . "main/profile/personal_stats.php");
    array_push($sideMenuImg, "fa-area-chart");

    array_push($sideMenuSubGroup, $sideMenuText);
    array_push($sideMenuSubGroup, $sideMenuLink);
    array_push($sideMenuSubGroup, $sideMenuImg);
    array_push($sideMenuGroup, $sideMenuSubGroup);

    if ($openCoursesSubGroup = openCoursesExtra()) {
        array_push($sideMenuGroup, $openCoursesSubGroup);
    }

    displayExtrasLeft();

    return $sideMenuGroup;
}

/**
 * Function loggedOutMenu
 *
 * Creates a multi-dimensional array of the user's tools/links
 * for the menu presented when the user is not logged in.
 * *
 * @return array
 */
function loggedOutMenu() {

    global $urlServer;

    $sideMenuGroup = array();

    $sideMenuSubGroup = array();
    $sideMenuText = array();
    $sideMenuLink = array();
    $sideMenuImg = array();

    $arrMenuType = array();
    $arrMenuType['type'] = 'text';
    $arrMenuType['text'] = $GLOBALS['langBasicOptions'];
    $arrMenuType['class'] = 'basic';
    array_push($sideMenuSubGroup, $arrMenuType);

    array_push($sideMenuText, $GLOBALS['langListCourses']);
    array_push($sideMenuLink, $urlServer . "modules/auth/listfaculte.php");
    array_push($sideMenuImg, "fa-graduation-cap");

    if (get_config('user_registration')) {
        array_push($sideMenuText, $GLOBALS['langNewUser']);
        array_push($sideMenuLink, $urlServer . "modules/auth/registration.php");
        array_push($sideMenuImg, "fa-pencil-square-o");
    }
    array_push($sideMenuText, $GLOBALS['langManuals']);
    array_push($sideMenuLink, $urlServer . "info/manual.php");
    array_push($sideMenuImg, "fa-file-video-o");

    array_push($sideMenuText, $GLOBALS['langPlatformIdentity']);
    array_push($sideMenuLink, $urlServer . "info/about.php");
    array_push($sideMenuImg, "fa-credit-card");

    array_push($sideMenuText, $GLOBALS['langContact']);
    array_push($sideMenuLink, $urlServer . "info/contact.php");
    array_push($sideMenuImg, "fa-phone");

    array_push($sideMenuSubGroup, $sideMenuText);
    array_push($sideMenuSubGroup, $sideMenuLink);
    array_push($sideMenuSubGroup, $sideMenuImg);

    array_push($sideMenuGroup, $sideMenuSubGroup);

    if ($openCoursesSubGroup = openCoursesExtra()) {
        array_push($sideMenuGroup, $openCoursesSubGroup);
    }

    displayExtrasLeft();

    return $sideMenuGroup;
}

/**
 * Creates the administrator menu
 *
 * @global type $language
 * @global type $phpSysInfoURL
 * @global type $phpMyAdminURL
 * @global type $urlServer
 * @global type $is_admin
 * @global type $is_power_user
 * @global type $is_departmentmanage_user
 * @return array
 */
function adminMenu() {

    global $language, $phpSysInfoURL, $phpMyAdminURL;
    global $urlServer;
    global $is_admin, $is_power_user, $is_departmentmanage_user;

    $sideMenuGroup = array();

    $sideMenuSubGroup = array();
    $sideMenuText = array();
    $sideMenuLink = array();
    $sideMenuImg = array();

    //user administration
    $arrMenuType = array();
    $arrMenuType['type'] = 'text';
    $arrMenuType['text'] = $GLOBALS['langAdminUsers'];
    $arrMenuType['class'] = 'user_admin';

    array_push($sideMenuSubGroup, $arrMenuType);
    array_push($sideMenuText, $GLOBALS['langSearchUser']);
    array_push($sideMenuLink, "../admin/search_user.php");
    array_push($sideMenuImg, "arrow.png");
    array_push($sideMenuText, $GLOBALS['langProfOpen']);
    array_push($sideMenuLink, "../admin/listreq.php");
    array_push($sideMenuImg, "arrow.png");

    // check for close user registration
    if ((get_config('eclass_stud_reg') == 1) or get_config('alt_auth_stud_reg') == 0) {
        array_push($sideMenuText, $GLOBALS['langUserOpen']);
        array_push($sideMenuLink, "../admin/listreq.php?type=user");
        array_push($sideMenuImg, "arrow.png");
    } else {
        array_push($sideMenuText, $GLOBALS['langUserDetails']);
        array_push($sideMenuLink, "../admin/newuseradmin.php?type=user");
        array_push($sideMenuImg, "arrow.png");
    }

    if (isset($is_admin) and $is_admin) {
        array_push($sideMenuText, $GLOBALS['langUserAuthentication']);
        array_push($sideMenuLink, "../admin/auth.php");
        array_push($sideMenuImg, "arrow.png");
        array_push($sideMenuText, $GLOBALS['langMailVerification']);
        array_push($sideMenuLink, "../admin/mail_ver_settings.php");
        array_push($sideMenuImg, "arrow.png");
        array_push($sideMenuText, $GLOBALS['langChangeUser']);
        array_push($sideMenuLink, "../admin/change_user.php");
        array_push($sideMenuImg, "arrow.png");
    }

    array_push($sideMenuText, $GLOBALS['langMultiRegUser']);
    array_push($sideMenuLink, "../admin/multireguser.php");
    array_push($sideMenuImg, "arrow.png");
    array_push($sideMenuText, $GLOBALS['langMultiRegCourseUser']);
    array_push($sideMenuLink, "../admin/multicourseuser.php");
    array_push($sideMenuImg, "arrow.png");
    array_push($sideMenuText, $GLOBALS['langMultiDelUser']);
    array_push($sideMenuLink, "../admin/multideluser.php");
    array_push($sideMenuImg, "arrow.png");
    array_push($sideMenuText, $GLOBALS['langInfoMail']);
    array_push($sideMenuLink, "../admin/mailtoprof.php");
    array_push($sideMenuImg, "arrow.png");

    if (isset($is_admin) and $is_admin) {
        array_push($sideMenuText, $GLOBALS['langAdmins']);
        array_push($sideMenuLink, "../admin/addadmin.php");
        array_push($sideMenuImg, "arrow.png");
    }

    array_push($sideMenuSubGroup, $sideMenuText);
    array_push($sideMenuSubGroup, $sideMenuLink);
    array_push($sideMenuSubGroup, $sideMenuImg);
    array_push($sideMenuGroup, $sideMenuSubGroup);

    if ((isset($is_power_user) and $is_power_user) or
            (isset($is_departmentmanage_user) and $is_departmentmanage_user)) {
        //lesson administration
        //reset sub-arrays so that we do not have duplicate entries
        $sideMenuSubGroup = array();
        $sideMenuText = array();
        $sideMenuLink = array();
        $sideMenuImg = array();

        $arrMenuType = array();
        $arrMenuType['type'] = 'text';
        $arrMenuType['text'] = $GLOBALS['langAdminCours'];
        $arrMenuType['class'] = 'course_admin';
        array_push($sideMenuSubGroup, $arrMenuType);

        array_push($sideMenuText, $GLOBALS['langSearchCourse']);
        array_push($sideMenuLink, "../admin/searchcours.php");
        array_push($sideMenuImg, "arrow.png");
        array_push($sideMenuText, $GLOBALS['langRestoreCourse']);
        array_push($sideMenuLink, "../course_info/restore_course.php");
        array_push($sideMenuImg, "arrow.png");
        array_push($sideMenuText, $GLOBALS['langHierarchy']);
        array_push($sideMenuLink, "../admin/hierarchy.php");
        array_push($sideMenuImg, "arrow.png");
        array_push($sideMenuText, $GLOBALS['langMultiCourse']);
        array_push($sideMenuLink, "../admin/multicourse.php");
        array_push($sideMenuImg, "arrow.png");
        array_push($sideMenuSubGroup, $sideMenuText);
        array_push($sideMenuSubGroup, $sideMenuLink);
        array_push($sideMenuSubGroup, $sideMenuImg);
        array_push($sideMenuGroup, $sideMenuSubGroup);
    }

    //server administration
    //reset sub-arrays so that we do not have duplicate entries
    $sideMenuSubGroup = array();
    $sideMenuText = array();
    $sideMenuLink = array();
    $sideMenuImg = array();

    if (isset($is_admin) and $is_admin) {
        $arrMenuType = array();
        $arrMenuType['type'] = 'text';
        $arrMenuType['text'] = $GLOBALS['langState'];
        $arrMenuType['class'] = 'server_admin';
        array_push($sideMenuSubGroup, $arrMenuType);
        array_push($sideMenuText, $GLOBALS['langCleanUp']);
        array_push($sideMenuLink, "../admin/cleanup.php");
        array_push($sideMenuImg, "arrow.png");

        if (isset($phpSysInfoURL) && PHP_OS == "Linux") {
            array_push($sideMenuText, $GLOBALS['langSysInfo']);
            array_push($sideMenuLink, $phpSysInfoURL);
            array_push($sideMenuImg, "arrow.png");
        }
        array_push($sideMenuText, $GLOBALS['langPHPInfo']);
        array_push($sideMenuLink, "../admin/phpInfo.php");
        array_push($sideMenuImg, "arrow.png");

        if (isset($phpMyAdminURL)) {
            array_push($sideMenuText, $GLOBALS['langDBaseAdmin']);
            array_push($sideMenuLink, $phpMyAdminURL);
            array_push($sideMenuImg, "arrow.png");
        }

        array_push($sideMenuText, $GLOBALS['langUpgradeBase']);
        array_push($sideMenuLink, $urlServer . "upgrade/");
        array_push($sideMenuImg, "arrow.png");

        array_push($sideMenuSubGroup, $sideMenuText);
        array_push($sideMenuSubGroup, $sideMenuLink);
        array_push($sideMenuSubGroup, $sideMenuImg);
        array_push($sideMenuGroup, $sideMenuSubGroup);
    }

    //other tools
    //reset sub-arrays so that we do not have duplicate entries
    $sideMenuSubGroup = array();
    $sideMenuText = array();
    $sideMenuLink = array();
    $sideMenuImg = array();

    if (isset($is_admin) and $is_admin) {
        $arrMenuType = array();
        $arrMenuType['type'] = 'text';
        $arrMenuType['text'] = $GLOBALS['langGenAdmin'];
        $arrMenuType['class'] = 'other_admin';
        array_push($sideMenuSubGroup, $arrMenuType);

        array_push($sideMenuText, $GLOBALS['langConfig']);
        array_push($sideMenuLink, "../admin/eclassconf.php");
        array_push($sideMenuImg, "arrow.png");

        array_push($sideMenuText, $GLOBALS['langExtAppConfig']);
        array_push($sideMenuLink, "../admin/extapp.php");
        array_push($sideMenuImg, "arrow.png");

        array_push($sideMenuText, $GLOBALS['langThemeSettings']);
        array_push($sideMenuLink, "../admin/theme_options.php");
        array_push($sideMenuImg, "arrow.png");

        array_push($sideMenuText, $GLOBALS['langDisableModules']);
        array_push($sideMenuLink, "../admin/modules.php");
        array_push($sideMenuImg, "arrow.png");

        array_push($sideMenuText, $GLOBALS['langStat']);
        array_push($sideMenuLink, "../admin/stateclass.php");
        array_push($sideMenuImg, "arrow.png");

        if (get_config('enable_common_docs')) {
            array_push($sideMenuText, $GLOBALS['langCommonDocs']);
            array_push($sideMenuLink, "../admin/commondocs.php");
            array_push($sideMenuImg, "arrow.png");
        }

        array_push($sideMenuText, $GLOBALS['langAdminAn']);
        array_push($sideMenuLink, "../admin/adminannouncements.php");
        array_push($sideMenuImg, "arrow.png");

        array_push($sideMenuText, $GLOBALS['langAdminManual']);
        $manual_language = ($language == 'el')? $language: 'en';
        array_push($sideMenuLink, "http://wiki.openeclass.org/doku.php?id=$manual_language:admin_doc");
        array_push($sideMenuImg, "arrow.png");

        array_push($sideMenuSubGroup, $sideMenuText);
        array_push($sideMenuSubGroup, $sideMenuLink);
        array_push($sideMenuSubGroup, $sideMenuImg);
        array_push($sideMenuGroup, $sideMenuSubGroup);
    }

    return $sideMenuGroup;
}

/**
 * Function lessonToolsMenu
 *
 * Creates a multi-dimensional array of the user's tools
 * in regard to the user's user level
 * (student | professor | platform administrator)
 *
 * @return array
 */
function lessonToolsMenu() {
    global $uid, $is_editor, $is_course_admin, $courses,
           $course_code, $langAdministrationTools, $langExternalLinks,
           $modules, $admin_modules, $urlAppend;

    $sideMenuGroup = array();
    $sideMenuSubGroup = array();
    $sideMenuText = array();
    $sideMenuLink = array();
    $sideMenuImg = array();
    $sideMenuID = array();

    $arrMenuType = array();
    $arrMenuType['type'] = 'none';

    if ($is_editor || $is_course_admin) {
        $tools_sections =
            array(array('type' => 'Public',
                        'title' => $GLOBALS['langActiveTools'],
                        'iconext' => '_on.png',
                        'class' => 'active'),
                  array('type' => 'PublicButHide',
                        'title' => $GLOBALS['langInactiveTools'],
                        'iconext' => '_off.png',
                        'class' => 'inactive'));
    } else {
        $tools_sections =
            array(array('type' => 'Public',
                        'title' => $GLOBALS['langCourseOptions'],
                        'iconext' => '_on.png',
                        'class' => 'active'));
    }

    foreach ($tools_sections as $section) {
        $result = getToolsArray($section['type']);

        $sideMenuSubGroup = array();
        $sideMenuText = array();
        $sideMenuLink = array();
        $sideMenuImg = array();
        $sideMenuID = array();

        $arrMenuType = array('type' => 'text',
                             'text' => $section['title'],
                             'class' => $section['class']);
        array_push($sideMenuSubGroup, $arrMenuType);

        setlocale(LC_COLLATE, $GLOBALS['langLocale']);
        usort($result, function ($a, $b) {
            global $modules;
            return strcoll($modules[$a->module_id]['title'], $modules[$b->module_id]['title']);
        });

        foreach ($result as $toolsRow) {
            $mid = $toolsRow->module_id;

            // hide groups for unregistered users
            if ($mid == MODULE_ID_GROUPS and !$courses[$course_code]) {
                continue;
            }

            // hide teleconference when no BBB servers are enabled
            if ($mid == MODULE_ID_BBB and !get_total_bbb_servers()) {
                continue;
            }

            if ($mid == MODULE_ID_DROPBOX) {
                $mbox = new Mailbox($uid, course_code_to_id($course_code));
                $new_msgs = $mbox->unreadMsgsNumber();
                if ($new_msgs != 0) {
                    array_push($sideMenuText, "<b>".q($modules[$mid]['title'])." (".$new_msgs.")</b>");
                } else {
                    array_push($sideMenuText, q($modules[$mid]['title']));
                }
            } else {
                array_push($sideMenuText, q($modules[$mid]['title']));
            }

            array_push($sideMenuLink, q($urlAppend . 'modules/' . $modules[$mid]['link'] .
                            '/?course=' . $course_code));
            array_push($sideMenuImg, $modules[$mid]['image'] . $section['iconext']);
            array_push($sideMenuID, $mid);
        }
        array_push($sideMenuSubGroup, $sideMenuText);
        array_push($sideMenuSubGroup, $sideMenuLink);
        array_push($sideMenuSubGroup, $sideMenuImg);
        array_push($sideMenuSubGroup, $sideMenuID);
        array_push($sideMenuGroup, $sideMenuSubGroup);
    }
    $result2 = getExternalLinks();
    if ($result2) { // display external link (if any)
        $sideMenuSubGroup = array();
        $sideMenuText = array();
        $sideMenuLink = array();
        $sideMenuImg = array();
        $arrMenuType = array('type' => 'text',
                             'text' => $langExternalLinks,
                             'class' => 'external');
        array_push($sideMenuSubGroup, $arrMenuType);
        foreach ($result2 as $ex_link) {
            array_push($sideMenuText, q($ex_link->title));
            array_push($sideMenuLink, q($ex_link->url));
            array_push($sideMenuImg, 'fa-external-link');
        }
        array_push($sideMenuSubGroup, $sideMenuText);
        array_push($sideMenuSubGroup, $sideMenuLink);
        array_push($sideMenuSubGroup, $sideMenuImg);
        array_push($sideMenuGroup, $sideMenuSubGroup);
    }
    if ($is_course_admin) {  // display course admin tools
        $sideMenuSubGroup = array();
        $sideMenuText = array();
        $sideMenuLink = array();
        $sideMenuImg = array();
        $sideMenuID = array();
        $arrMenuType = array('type' => 'text',
                             'text' => $langAdministrationTools,
                             'class' => 'course_admin');
        array_push($sideMenuSubGroup, $arrMenuType);
        foreach ($admin_modules as $adm_mod) {
            array_push($sideMenuText, $adm_mod['title']);
            array_push($sideMenuLink, q($urlAppend . 'modules/' . $adm_mod['link'] .
                            '/?course=' . $course_code));
            array_push($sideMenuImg, $adm_mod['image'] . $section['iconext']);
        }
        array_push($sideMenuSubGroup, $sideMenuText);
        array_push($sideMenuSubGroup, $sideMenuLink);
        array_push($sideMenuSubGroup, $sideMenuImg);
        array_push($sideMenuSubGroup, $sideMenuID);
        array_push($sideMenuGroup, $sideMenuSubGroup);
    }
    return $sideMenuGroup;
}

/**
 * Function pickerMenu
 *
 * Creates a multi-dimensional array of the user's tools/links
 * for the menu presented for the embedded theme.
 * *
 * @return array
 */
function pickerMenu() {

    global $urlServer, $course_code, $course_id, $is_editor, $modules;

    $docsfilter = (isset($_REQUEST['docsfilter'])) ? '&docsfilter=' . q($_REQUEST['docsfilter']) : '';
    $params = "?course=$course_code&embedtype=tinymce" . $docsfilter;

    $sideMenuGroup = array();

    $sideMenuSubGroup = array();
    $sideMenuText = array();
    $sideMenuLink = array();
    $sideMenuImg = array();

    $arrMenuType = array();
    $arrMenuType['type'] = 'text';
    $arrMenuType['text'] = $GLOBALS['langBasicOptions'];
	$arrMenuType['class'] = 'picker';
    array_push($sideMenuSubGroup, $arrMenuType);

    if (isset($course_id) and $course_id >= 1) {
        $visible = ($is_editor) ? '' : 'AND visible = 1';
        $result = Database::get()->queryArray("SELECT * FROM course_module
                               WHERE course_id = ?d AND
                                     module_id IN (" . MODULE_ID_DOCS . ', ' . MODULE_ID_VIDEO . ', ' . MODULE_ID_LINKS . ") AND
                                     module_id NOT IN (SELECT module_id FROM module_disable)
                                     $visible
                               ORDER BY module_id", $course_id);

        foreach ($result as $module) {
            $mid = $module->module_id;
            array_push($sideMenuText, q($modules[$mid]['title']));
            array_push($sideMenuLink, q($urlServer . 'modules/' .
                            $modules[$mid]['link'] . '/' . $params));
            array_push($sideMenuImg, $modules[$mid]['image'] . "_on.png");
        }
    }
    /* link for common documents */
    if (get_config('enable_common_docs')) {
        array_push($sideMenuText, q($GLOBALS['langCommonDocs']));
        array_push($sideMenuLink, q($urlServer . 'modules/admin/commondocs.php' . $params));
        array_push($sideMenuImg, 'docs.png');
    }

    array_push($sideMenuSubGroup, $sideMenuText);
    array_push($sideMenuSubGroup, $sideMenuLink);
    array_push($sideMenuSubGroup, $sideMenuImg);

    array_push($sideMenuGroup, $sideMenuSubGroup);
    return $sideMenuGroup;
}

/**
 * display number of open courses
 * @global type $urlServer
 */
function openCoursesExtra() {
    global $urlAppend, $themeimg, $openCoursesExtraHTML;

    if (!isset($openCoursesExtraHTML)) {
        setOpenCoursesExtraHTML();
    }
    $menuGroup = false;
    if (get_config('opencourses_enable') and $openCoursesExtraHTML) {
        $openCoursesNum = Database::get()->querySingle("SELECT COUNT(id) as count FROM course_review WHERE is_certified = 1")->count;

        if ($openCoursesNum > 0) {
            $openFacultiesUrl = $urlAppend . 'modules/course_metadata/openfaculties.php';
            $menuGroup = array(
                array('type' => 'text',
                      'text' => $GLOBALS['langOpenCoursesShort'],
                      'class' => 'basic'),
                array($GLOBALS['langListOpenCoursesShort']),
                array($openFacultiesUrl),
                array('fa-caret-right'));
        }
    }

    return $menuGroup;
}

/**
 * display extras left
 * @global type $langExtrasLeft
 * @global type $leftNavExtras
 */
function displayExtrasLeft() {

    global $langExtrasLeft, $leftNavExtras;

    if (isset($langExtrasLeft) and !empty($langExtrasLeft)) {
        $leftNavExtras .= $langExtrasLeft;
    }
}
