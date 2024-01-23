<?php

/* ========================================================================
 * Open eClass 3.6
 * E-learning and Course Management System
 * ========================================================================
 * Copyright 2003-2017  Greek Universities Network - GUnet
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
 * @author Evelthon Prodromou <eprodromou@upnet.gr>
 * @abstract This component creates an array of the tools that are displayed on the left side column
 */

require_once 'modules/tc/functions.php';
require_once 'modules/message/class.mailbox.php';
require_once 'modules/lti_consumer/lti-functions.php';

/**
 * @brief Offers an upper-layer logic. Decides what function should be called to
 * create the needed tools array
 * @param int $menuTypeID Type of menu to generate
 * @param bool $rich Whether to include rich text notifications in title
 */

function getSideMenu($menuTypeID, $rich=true) {
    switch ($menuTypeID) {
        case 0: { //logged out
                $menu = loggedOutMenu();
                break;
            }

        case 1: { //logged in
                $menu = loggedInMenu($rich);
                break;
            }

        case 2: { //course home (lesson tools)
                $menu = lessonToolsMenu($rich);
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

/**
 * @brief Queries the database for tool information in accordance
 * to the parameter passed.
 * @param string $cat Type of lesson tools
 * @return array of course_module objects
 * @see function lessonToolsMenu
 */
function getToolsArray($cat) {
    global $course_id, $course_view_type;

    switch ($cat) {
        case 'Public':
            if (check_guest()) {
                $sql = "SELECT * FROM course_module
                            WHERE visible = 1 AND
                            course_id = ?d AND
                            module_id NOT IN (" . MODULE_ID_CHAT . ",
                                              " . MODULE_ID_ASSIGN . ",
                                              " . MODULE_ID_MESSAGE . ",
                                              " . MODULE_ID_FORUM . ",
                                              " . MODULE_ID_GROUPS . ",
                                              " . MODULE_ID_ATTENDANCE . ",
                                              " . MODULE_ID_GRADEBOOK . ",
                                              " . MODULE_ID_MINDMAP . ",
                                              " . MODULE_ID_PROGRESS . ",
                                              " . MODULE_ID_LP . ",
                                              " . MODULE_ID_TC . ") AND
                            module_id NOT IN (SELECT module_id FROM module_disable)";
                $result = Database::get()->queryArray($sql, $course_id);
            } else {
                $result = Database::get()->queryArray("SELECT * FROM course_module
                        WHERE visible = 1 AND
                              module_id NOT IN (SELECT module_id FROM module_disable) AND
                        course_id = ?d", $course_id);
            }
            break;
        case 'PublicButHide':
            $result = Database::get()->queryArray("SELECT * FROM course_module
                         WHERE visible = 0 AND
                               module_id NOT IN (SELECT module_id FROM module_disable) AND
                         course_id = ?d", $course_id);
            break;
    }
    // Ignore items not listed in $modules array
    // (for development, when moving to a branch with fewer modules)
    return array_filter($result, function ($item) {
        global $course_view_type;
        if ($item->module_id == MODULE_ID_WALL and $course_view_type == 'wall') {
            return false;
        }
        return isset($GLOBALS['modules'][$item->module_id]);
    });
}

/**
 * @brief Get course pages
 * @global type $course_id
 * @return array
 */
function getCoursePages($course_id) {
    $result = Database::get()->queryArray('SELECT id, title
        FROM page WHERE course_id = ?d AND visible = 1',
        $course_id);
    if ($result) {
        return $result;
    } else {
        return [];
    }
}


/**
 * @brief get course external links
 * @global type $course_id
 * @return array
 */
function getExternalLinks() {
    global $course_id;

    $result = Database::get()->queryArray("SELECT url, title FROM link
        WHERE category = -1 AND course_id = ?d", $course_id);
    if ($result) {
        return $result;
    } else {
        return [];
    }
}

/**
 *
 * @brief Creates a multi-dimensional array of the user's tools
 * when the user is signed in, and not at a lesson specific tool,
 * in regard to the user's user level.
 * (student | professor | platform administrator)
 * @param bool $rich Whether to include rich text notifications in title
 * @return array
 */
function loggedInMenu($rich=true) {
    global $uid, $is_admin, $is_power_user, $is_usermanage_user,
    $is_departmentmanage_user, $urlServer, $course_code, $session;

    $sideMenuGroup = array();

    $sideMenuSubGroup = array();
    $sideMenuText = array();
    $sideMenuLink = array();
    $sideMenuImg = array();

    $arrMenuType = array();
    $arrMenuType['type'] = 'text';
    $arrMenuType['text'] = $GLOBALS['langBasicOptions'];
    $arrMenuType['class'] = 'basic';
    $sideMenuSubGroup[] = $arrMenuType;

    if (!get_config('dont_display_courses_menu')) {
        $sideMenuText[] = $GLOBALS['langListCourses'];
        $sideMenuLink[] = $urlServer . "modules/auth/courses.php";
        $sideMenuImg[] = "fa-graduation-cap";
    }

    if (!get_config('dont_display_manual_menu')) {
        $sideMenuText[] = $GLOBALS['langManuals'];
        $sideMenuLink[] = $urlServer . "info/manual.php";
        $sideMenuImg[] = "fa-file-video-o";
    }

    if (!get_config('dont_display_about_menu')) {
        $sideMenuText[] = $GLOBALS['langPlatformIdentity'];
        $sideMenuLink[] = $urlServer . "info/about.php";
        $sideMenuImg[] = "fa-credit-card";
    }

    if (faq_exist()) {
        $sideMenuText[] = $GLOBALS['langFaq'];
        $sideMenuLink[] = $urlServer . "info/faq.php";
        $sideMenuImg[] = "fa-question-circle";
    }
    if (!get_config('dont_display_contact_menu')) {
        $sideMenuText[] = $GLOBALS['langContact'];
        $sideMenuLink[] = $urlServer . "info/contact.php";
        $sideMenuImg[] = "fa-phone";
    }

    $sideMenuSubGroup[] = $sideMenuText;
    $sideMenuSubGroup[] = $sideMenuLink;
    $sideMenuSubGroup[] = $sideMenuImg;
    $sideMenuGroup[] = $sideMenuSubGroup;

    $sideMenuSubGroup = array();
    $sideMenuText = array();
    $sideMenuLink = array();
    $sideMenuImg = array();

    $arrMenuType = array();
    $arrMenuType['type'] = 'text';
    $arrMenuType['text'] = $GLOBALS['langUserOptions'];
    $arrMenuType['class'] = 'user';
    $sideMenuSubGroup[] = $arrMenuType;

    $res2 = Database::get()->querySingle("SELECT status FROM user WHERE id = ?d", $uid);
    if ($res2) {
        $status = $res2->status;
    }
    if ((isset($status) and $status == USER_TEACHER) or $is_departmentmanage_user) {
        $sideMenuText[] = $GLOBALS['langCourseCreate'];
        $sideMenuLink[] = $urlServer . "modules/create_course/create_course.php";
        $sideMenuImg[] = "fa-plus-circle";
    }

    require_once 'modules/message/class.mailbox.php';

    $sideMenuText[] = $GLOBALS['langMyCourses'];
    $sideMenuLink[] = $urlServer . "main/my_courses.php";
    $sideMenuImg[] = "fa-graduation-cap";

    $mbox = new Mailbox($uid, 0);
    $new_msgs = $mbox->unreadMsgsNumber();
    if (!$rich or $new_msgs == 0) {
        $sideMenuText[] = $GLOBALS['langMyDropBox'];
    } else {
        $sideMenuText[] = "<b>$GLOBALS[langMyDropBox]<span class='badge pull-right'>$new_msgs</span></b>";
    }
    $sideMenuLink[] = $urlServer . "modules/message/index.php";
    $sideMenuImg[] = "fa-envelope-o";

    $sideMenuText[] = $GLOBALS['langMyAnnouncements'];
    $sideMenuLink[] = $urlServer . "modules/announcements/myannouncements.php";
    $sideMenuImg[] = "fa-bullhorn";

    $sideMenuText[] = $GLOBALS['langMyAgenda'];
    $sideMenuLink[] = $urlServer . "main/personal_calendar/index.php";
    $sideMenuImg[] = "fa-calendar";

    $sideMenuText[] = $GLOBALS['langNotes'];
    $sideMenuLink[] = $urlServer . "main/notes/index.php";
    $sideMenuImg[] = "fa-edit";

    if (isset($status) and $status == USER_STUDENT and !is_module_disable(MODULE_ID_GRADEBOOK)) {
        $sideMenuText[] = $GLOBALS['langGradeTotal'];
        $sideMenuLink[] = $urlServer . "main/gradebookUserTotal/index.php";
        $sideMenuImg[] = "gradebook";
    }

    if (isset($status) and $status == USER_STUDENT and !is_module_disable(MODULE_ID_PROGRESS)) {
        $sideMenuText[] = $GLOBALS['langMyCertificates'];
        $sideMenuLink[] = $urlServer . "main/mycertificates.php";
        $sideMenuImg[] = "fa-trophy";
    }

    if (get_config('personal_blog')) {
        $sideMenuText[] = $GLOBALS['langMyBlog'];
        $sideMenuLink[] = $urlServer . "modules/blog/index.php";
        $sideMenuImg[] = "blog";
    }

    if (get_config('eportfolio_enable')) {
        $sideMenuText[] = $GLOBALS['langMyePortfolio'];
        $sideMenuLink[] = $urlServer . "main/eportfolio/index.php?id=$uid&amp;token=" . token_generate('eportfolio' . $uid);
        $sideMenuImg[] = "fa-star";
    }

    // link for my documents
    if (($session->status == USER_TEACHER and get_config('mydocs_teacher_enable')) or
        ($session->status == USER_STUDENT and get_config('mydocs_student_enable'))) {
        $sideMenuText[] = q($GLOBALS['langMyDocs']);
        $sideMenuLink[] = q($urlServer . 'main/mydocs/index.php');
        $sideMenuImg[] = 'docs.png';
    }

    $sideMenuText[] = $GLOBALS['langMyProfile'];
    $sideMenuLink[] = $urlServer . "main/profile/display_profile.php";
    $sideMenuImg[] = "fa-user";

    $sideMenuText[] = $GLOBALS['langMyStats'];
    $sideMenuLink[] = $urlServer . "modules/usage/?t=u";
    $sideMenuImg[] = "fa-area-chart";

    $sideMenuSubGroup[] = $sideMenuText;
    $sideMenuSubGroup[] = $sideMenuLink;
    $sideMenuSubGroup[] = $sideMenuImg;
    $sideMenuGroup[] = $sideMenuSubGroup;

    if ($openCoursesSubGroup = openCoursesExtra()) {
        $sideMenuGroup[] = $openCoursesSubGroup;
    }

    displayExtrasLeft();

    return $sideMenuGroup;
}

/**
 * @brief Creates a multi-dimensional array of the user's tools/links
 * for the menu presented when the user is not logged in.
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
    $sideMenuSubGroup[] = $arrMenuType;

    if (!get_config('dont_display_courses_menu')) {
        $sideMenuText[] = $GLOBALS['langListCourses'];
        $sideMenuLink[] = $urlServer . "modules/auth/listfaculte.php";
        $sideMenuImg[] = "fa-graduation-cap";
    }

    if (get_config('user_registration') and get_config('registration_link') != 'hide') {
        $sideMenuText[] = $GLOBALS['langRegistration'];
        $sideMenuLink[] = $urlServer . "modules/auth/registration.php";
        $sideMenuImg[] = "fa-pencil-square-o";
    }
    if (!get_config('dont_display_manual_menu')) {
        $sideMenuText[] = $GLOBALS['langManuals'];
        $sideMenuLink[] = $urlServer . "info/manual.php";
        $sideMenuImg[] = "fa-file-video-o";
    }

    if (!get_config('dont_display_about_menu')) {
        $sideMenuText[] = $GLOBALS['langPlatformIdentity'];
        $sideMenuLink[] = $urlServer . "info/about.php";
        $sideMenuImg[] = "fa-credit-card";
    }

    if (faq_exist()) {
        $sideMenuText[] = $GLOBALS['langFaq'];
        $sideMenuLink[] = $urlServer . "info/faq.php";
        $sideMenuImg[] = "fa-question-circle";
    }

    if (!get_config('dont_display_contact_menu')) {
        $sideMenuText[] = $GLOBALS['langContact'];
        $sideMenuLink[] = $urlServer . "info/contact.php";
        $sideMenuImg[] = "fa-phone";
    }

    $sideMenuSubGroup[] = $sideMenuText;
    $sideMenuSubGroup[] = $sideMenuLink;
    $sideMenuSubGroup[] = $sideMenuImg;

    $sideMenuGroup[] = $sideMenuSubGroup;

    if ($openCoursesSubGroup = openCoursesExtra()) {
        $sideMenuGroup[] = $openCoursesSubGroup;
    }

    displayExtrasLeft();

    return $sideMenuGroup;
}

/**
 * @brief Creates the administrator menu
 * @return array
 */
function adminMenu() {

    global $language, $urlServer;
    global $is_admin, $is_power_user, $is_departmentmanage_user;

    $sideMenuGroup = array();

    $sideMenuSubGroup = array();
    $sideMenuText = array();
    $sideMenuLink = array();
    $sideMenuImg = array();

    // user administration
    $sideMenuSubGroup[] = array(
        'type' => 'text',
        'text' => $GLOBALS['langAdminUsers'],
        'class' => 'user_admin');

    $sideMenuText[] = $GLOBALS['langSearchUser'];
    $sideMenuLink[] = '../admin/search_user.php';
    $sideMenuImg[] = 'fa-caret-right';

    // link to prof requests if enabled, else directly to new prof page
    if (get_config('eclass_prof_reg') == 1 or get_config('alt_auth_prof_reg') == 1) {
        $sideMenuText[] = $GLOBALS['langProfOpen'];
        $sideMenuLink[] = '../admin/listreq.php';
        $sideMenuImg[] = 'fa-caret-right';
    } else {
        $sideMenuText[] = $GLOBALS['langProfReg'];
        $sideMenuLink[] = '../admin/newuseradmin.php';
        $sideMenuImg[] = 'fa-caret-right';
    }

    // link to user requests if enabled, else directly to new user page
    if (get_config('eclass_stud_reg') == 1 or get_config('alt_auth_stud_reg') == 1) {
        $sideMenuText[] = $GLOBALS['langUserOpen'];
        $sideMenuLink[] = '../admin/listreq.php?type=user';
        $sideMenuImg[] = 'fa-caret-right';
    } else {
        $sideMenuText[] = $GLOBALS['langUserDetails'];
        $sideMenuLink[] = '../admin/newuseradmin.php?type=user';
        $sideMenuImg[] = 'fa-caret-right';
    }

    if (isset($is_admin) and $is_admin) {
        $sideMenuText[] = $GLOBALS['langUserAuthentication'];
        $sideMenuLink[] = '../admin/auth.php';
        $sideMenuImg[] = 'fa-caret-right';

        $sideMenuText[] = $GLOBALS['langMailVerification'];
        $sideMenuLink[] = '../admin/mail_ver_settings.php';
        $sideMenuImg[] = 'fa-caret-right';

        $sideMenuText[] = $GLOBALS['langChangeUser'];
        $sideMenuLink[] = '../admin/change_user.php';
        $sideMenuImg[] = 'fa-caret-right';

        $sideMenuText[] = $GLOBALS['langCPFAdminSideMenuLink'];
        $sideMenuLink[] = '../admin/custom_profile_fields.php';
        $sideMenuImg[] = 'fa-caret-right';

        $sideMenuText[] = $GLOBALS['langEPFAdminSideMenuLink'];
        $sideMenuLink[] = '../admin/eportfolio_fields.php';
        $sideMenuImg[] = 'fa-caret-right';
    }

    $sideMenuText[] = $GLOBALS['langMultiRegUser'];
    $sideMenuLink[] = '../admin/multireguser.php';
    $sideMenuImg[] = 'fa-caret-right';

    $sideMenuText[] = $GLOBALS['langMultiRegCourseUser'];
    $sideMenuLink[] = '../admin/multicourseuser.php';
    $sideMenuImg[] = 'fa-caret-right';

    $sideMenuText[] = $GLOBALS['langMultiDelUser'];
    $sideMenuLink[] = '../admin/multiedituser.php';
    $sideMenuImg[] = 'fa-caret-right';

    array_push($sideMenuText, $GLOBALS['langInfoMail']);
    array_push($sideMenuLink, '../admin/mailtoprof.php');
    array_push($sideMenuImg, 'fa-caret-right');

    if ($is_admin) {
        array_push($sideMenuText, $GLOBALS['langAdmins']);
        array_push($sideMenuLink, '../admin/addadmin.php');
        array_push($sideMenuImg, 'fa-caret-right');
    }

    array_push($sideMenuSubGroup, $sideMenuText);
    array_push($sideMenuSubGroup, $sideMenuLink);
    array_push($sideMenuSubGroup, $sideMenuImg);
    array_push($sideMenuGroup, $sideMenuSubGroup);

    if ($is_power_user or $is_departmentmanage_user) {
        // lesson administration
        // reset sub-arrays so that we do not have duplicate entries
        $sideMenuSubGroup = array();
        $sideMenuText = array();
        $sideMenuLink = array();
        $sideMenuImg = array();

        array_push($sideMenuSubGroup, array(
            'type' => 'text',
            'text' => $GLOBALS['langAdminCours'],
            'class' => 'course_admin'));

        array_push($sideMenuText, $GLOBALS['langSearchCourse']);
        array_push($sideMenuLink, '../admin/searchcours.php');
        array_push($sideMenuImg, 'fa-caret-right');

        array_push($sideMenuText, $GLOBALS['langRestoreCourse']);
        array_push($sideMenuLink, '../course_info/restore_course.php');
        array_push($sideMenuImg, 'fa-caret-right');

        if ($is_admin) {
            array_push($sideMenuText, $GLOBALS['langHierarchy']);
            array_push($sideMenuLink, '../admin/hierarchy.php');
            array_push($sideMenuImg, 'fa-caret-right');
        }

        array_push($sideMenuText, $GLOBALS['langMultiCourse']);
        array_push($sideMenuLink, '../admin/multicourse.php');
        array_push($sideMenuImg, 'fa-caret-right');

        if ($is_admin) {
            array_push($sideMenuText, $GLOBALS['langAutoEnroll']);
            array_push($sideMenuLink, '../admin/autoenroll.php');
            array_push($sideMenuImg, 'fa-caret-right');

            array_push($sideMenuText, $GLOBALS['langDisableModules']);
            array_push($sideMenuLink, "../admin/modules.php");
            array_push($sideMenuImg, "fa-caret-right");

            array_push($sideMenuText, $GLOBALS['langCertBadge']);
            array_push($sideMenuLink, '../admin/certbadge.php');
            array_push($sideMenuImg, 'fa-caret-right');

            array_push($sideMenuText, $GLOBALS['langActivityCourse']);
            array_push($sideMenuLink, '../admin/activity.php');
            array_push($sideMenuImg, 'fa-caret-right');

            array_push($sideMenuText, $GLOBALS['langCourseCategoryActions']);
            array_push($sideMenuLink, '../admin/coursecategory.php');
            array_push($sideMenuImg, 'arrow.png');
        }
        array_push($sideMenuSubGroup, $sideMenuText);
        array_push($sideMenuSubGroup, $sideMenuLink);
        array_push($sideMenuSubGroup, $sideMenuImg);
        array_push($sideMenuGroup, $sideMenuSubGroup);
    }

    // server administration
    // reset sub-arrays so that we do not have duplicate entries
    $sideMenuSubGroup = array();
    $sideMenuText = array();
    $sideMenuLink = array();
    $sideMenuImg = array();

    if (isset($is_admin) and $is_admin) {

        array_push($sideMenuSubGroup, array(
            'type' => 'text',
            'text' => $GLOBALS['langAdminTool'],
            'class' => 'server_admin'));

        array_push($sideMenuText, $GLOBALS['langConfig']);
        array_push($sideMenuLink, "../admin/eclassconf.php");
        array_push($sideMenuImg, "fa-caret-right");

        array_push($sideMenuText, $GLOBALS['langExtAppConfig']);
        array_push($sideMenuLink, "../admin/extapp.php");
        array_push($sideMenuImg, "fa-caret-right");

        array_push($sideMenuText, $GLOBALS['langThemeSettings']);
        array_push($sideMenuLink, "../admin/theme_options.php");
        array_push($sideMenuImg, "fa-caret-right");

        if (get_config('phpMyAdminURL')) {
            array_push($sideMenuText, $GLOBALS['langDBaseAdmin']);
            array_push($sideMenuLink, get_config('phpMyAdminURL'));
            array_push($sideMenuImg, "fa-caret-right");
        }

        array_push($sideMenuText, $GLOBALS['langUpgradeBase']);
        array_push($sideMenuLink, $urlServer . "upgrade/");
        array_push($sideMenuImg, "fa-caret-right");

        array_push($sideMenuText, $GLOBALS['langUsage']);
        array_push($sideMenuLink, "../../modules/usage/?t=a");
        array_push($sideMenuImg, "fa-caret-right");

        array_push($sideMenuText, $GLOBALS['langAdminAn']);
        array_push($sideMenuLink, "../admin/adminannouncements.php");
        array_push($sideMenuImg, "fa-caret-right");

        array_push($sideMenuText, $GLOBALS['langAdminCreateFaq']);
        array_push($sideMenuLink, "../admin/faq_create.php");
        array_push($sideMenuImg, "fa-caret-right");

        if (get_config('enable_common_docs')) {
            array_push($sideMenuText, $GLOBALS['langCommonDocs']);
            array_push($sideMenuLink, "../admin/commondocs.php");
            array_push($sideMenuImg, "fa-caret-right");
        }

        array_push($sideMenuText, $GLOBALS['langCleanUp']);
        array_push($sideMenuLink, "../admin/cleanup.php");
        array_push($sideMenuImg, "fa-caret-right");

        if (get_config('phpSysInfoURL')) {
            array_push($sideMenuText, $GLOBALS['langSysInfo']);
            array_push($sideMenuLink, get_config('phpSysInfoURL'));
            array_push($sideMenuImg, "fa-caret-right");
        }

        array_push($sideMenuText, $GLOBALS['langPHPInfo']);
        array_push($sideMenuLink, "../admin/phpInfo.php");
        array_push($sideMenuImg, "fa-caret-right");

        array_push($sideMenuText, $GLOBALS['langAdminManual']);
        $manual_language = ($language == 'el')? $language: 'en';
        array_push($sideMenuLink, "http://docs.openeclass.org/$manual_language/admin");
        array_push($sideMenuImg, "fa-caret-right");

        array_push($sideMenuSubGroup, $sideMenuText);
        array_push($sideMenuSubGroup, $sideMenuLink);
        array_push($sideMenuSubGroup, $sideMenuImg);
        array_push($sideMenuGroup, $sideMenuSubGroup);
    }

    return $sideMenuGroup;
}

/**
 * @brief Creates a multidimensional array of the user's tools
 * in regard to the user's user level
 * (student | professor | platform administrator)
 * @param bool $rich Whether to include rich text notifications in title
 * @return array
 */
function lessonToolsMenu($rich=true): array
{
    global $uid, $is_editor, $is_course_admin, $is_course_reviewer, $courses,
           $course_code, $langAdministrationTools,
           $modules, $admin_modules, $urlAppend, $status, $course_id;

    $sideMenuGroup = array();
    $arrMenuType = array();
    $arrMenuType['type'] = 'none';

    if ($is_editor || $is_course_admin || $is_course_reviewer) {
        $tools_sections =
            array(array('type' => 'Public',
                        'title' => $GLOBALS['langActiveTools'],
                        'class' => 'active'),
                  array('type' => 'PublicButHide',
                        'title' => $GLOBALS['langInactiveTools'],
                        'class' => 'inactive'));
    } else {
        $tools_sections =
            array(array('type' => 'Public',
                        'title' => $GLOBALS['langCourseOptions'],
                        'class' => 'active'));
    }

    foreach ($tools_sections as $section) {

        $result = getToolsArray($section['type']);

        $sideMenuSubGroup = array();
        $sideMenuText = array();
        $sideMenuLink = array();
        $sideMenuImg = array();
        $sideMenuID = array();
        $mail_status = '';
        $arrMenuType = array('type' => 'text',
                             'text' => $section['title'],
                             'class' => $section['class']);
        $sideMenuSubGroup[] = $arrMenuType;

        setlocale(LC_COLLATE, $GLOBALS['langLocale']);
        usort($result, function ($a, $b) {
            global $modules;
            return strcoll($modules[$a->module_id]['title'], $modules[$b->module_id]['title']);
        });

        // check if we have define mail address and want to receive messages
        if ($uid and $status != USER_GUEST and !get_user_email_notification($uid, $course_id)) {
            $mail_status = '&nbsp;' . icon('fa-exclamation-triangle');
        }

        foreach ($result as $toolsRow) {
            $mid = $toolsRow->module_id;

            // hide groups for unregistered users
            if ($mid == MODULE_ID_GROUPS and !$courses[$course_code]) {
                continue;
            }

            // hide teleconference when no tc servers are enabled
            if ($mid == MODULE_ID_TC and count(get_enabled_tc_services()) == 0) {
                continue;
            }

            // if we are in messages or announcements add (if needed) mail address status
            if ($rich and ($mid == MODULE_ID_MESSAGE or $mid == MODULE_ID_ANNOUNCE)) {
                if ($mid == MODULE_ID_MESSAGE) {
                    $mbox = new Mailbox($uid, course_code_to_id($course_code));
                    $new_msgs = $mbox->unreadMsgsNumber();
                    if ($new_msgs != 0) {
                        $sideMenuText[] = '<b>' . q($modules[$mid]['title']) .
                            " $mail_status<span class='badge pull-right'>$new_msgs</span></b>";
                    } else {
                        $sideMenuText[] = q($modules[$mid]['title']) . ' ' . $mail_status;
                    }
                } else {
                    $sideMenuText[] = q($modules[$mid]['title']) . ' ' . $mail_status;
                }
            } elseif ($rich and $mid == MODULE_ID_DOCS and ($new_docs = get_new_document_count($course_id))) {
                $sideMenuText[] = '<b>' . q($modules[$mid]['title']) .
                    "<span class='badge pull-right'>$new_docs</span></b>";
            } else {
                $sideMenuText[] = q($modules[$mid]['title']);
            }

            $sideMenuLink[] = q($urlAppend . 'modules/' . $modules[$mid]['link'] .
                '/?course=' . $course_code);
            $sideMenuImg[] = $modules[$mid]['image'];

            $sideMenuID[] = $mid;
        }

        if ($section['type'] == 'Public') {

            // display course pages links (if any)
            foreach (getCoursePages($course_id) as $page) {
                $sideMenuText[] = q($page->title);
                $sideMenuLink[] = $urlAppend . "modules/course_home/page.php?course=$course_code&amp;id=" . $page->id;
                $sideMenuImg[] = 'fa-caret-right';
                $sideMenuID[] = -1;
            }

            // display external links (if any)
            foreach (getExternalLinks() as $ex_link) {
                $sideMenuText[] = q($ex_link->title);
                $sideMenuLink[] = q($ex_link->url);
                $sideMenuImg[] = 'fa-external-link';
                $sideMenuID[] = -1;
            }

            foreach (getLTILinksForTools() as $lti_link) {
                $sideMenuText[] = q($lti_link->title);
                $sideMenuLink[] = q($lti_link->url);
                $sideMenuImg[] = q($lti_link->menulink);
                $sideMenuID[] = -1;
            }
        }

        $sideMenuSubGroup[] = $sideMenuText;
        $sideMenuSubGroup[] = $sideMenuLink;
        $sideMenuSubGroup[] = $sideMenuImg;
        $sideMenuSubGroup[] = $sideMenuID;
        $sideMenuGroup[] = $sideMenuSubGroup;
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
        $sideMenuSubGroup[] = $arrMenuType;

        foreach ($admin_modules as $adm_mod) {
            $sideMenuText[] = $adm_mod['title'];
            $sideMenuLink[] = q($urlAppend . 'modules/' . $adm_mod['link'] .
                '/?course=' . $course_code);
            $sideMenuImg[] = $adm_mod['image'];
        }

        $sideMenuSubGroup[] = $sideMenuText;
        $sideMenuSubGroup[] = $sideMenuLink;
        $sideMenuSubGroup[] = $sideMenuImg;
        $sideMenuSubGroup[] = $sideMenuID;
        $sideMenuGroup[] = $sideMenuSubGroup;
    }
    return $sideMenuGroup;
}

/**
 *
 * @brief Creates a multidimensional array of the user's tools/links
 * for the menu presented for the embedded theme.
 * @return array
 */
function pickerMenu() {

    global $urlServer, $course_code, $course_id, $is_editor, $is_course_admin, $modules, $session, $uid;

    // params
    $originating_module = isset($_REQUEST['originating_module']) ? intval($_REQUEST['originating_module']) : null;
    $originating_forum = isset($_REQUEST['originating_forum']) ? intval($_REQUEST['originating_forum']) : null;
    $append_module = $originating_module ? "&originating_module=$originating_module" : '';
    $append_forum = $originating_forum ? "&originating_forum=$originating_forum" : '';
    $docsfilter = isset($_REQUEST['docsfilter']) ? ('&docsfilter=' . q($_REQUEST['docsfilter'])) : '';
    $params = "?course=" . $course_code . "&embedtype=tinymce" . $append_module . $append_forum . $docsfilter;

    $sideMenuGroup = array();

    $sideMenuSubGroup = array();
    $sideMenuText = array();
    $sideMenuLink = array();
    $sideMenuImg = array();

    $arrMenuType = array();
    $arrMenuType['type'] = 'text';
    $arrMenuType['text'] = $GLOBALS['langBasicOptions'];
    $arrMenuType['class'] = 'picker';
    $sideMenuSubGroup[] = $arrMenuType;

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
            $sideMenuText[] = q($modules[$mid]['title']);
            $sideMenuLink[] = q($urlServer . 'modules/' .
                $modules[$mid]['link'] . '/' . $params);
            $sideMenuImg[] = $modules[$mid]['image'] . "_on.png";
        }
    }

    // link for common documents
    if (get_config('enable_common_docs')) {
        $sideMenuText[] = q($GLOBALS['langCommonDocs']);
        $sideMenuLink[] = q($urlServer . 'modules/admin/commondocs.php' . $params);
        $sideMenuImg[] = 'docs.png';
    }

    // link for my documents
    if (($session->status == USER_TEACHER and get_config('mydocs_teacher_enable')) or
        ($session->status == USER_STUDENT and get_config('mydocs_student_enable'))) {
        $sideMenuText[] = q($GLOBALS['langMyDocs']);
        $sideMenuLink[] = q($urlServer . 'main/mydocs/index.php' . $params);
        $sideMenuImg[] = 'docs.png';
    }

    // link for group documents
    if ($originating_module === MODULE_ID_FORUM && !empty($originating_forum)) {
        $result = Database::get()->querySingle("SELECT * FROM `group` WHERE forum_id = ?d LIMIT 1", $originating_forum);
        if ($result) {
            // check user access: editors and admins PASS, group_members also
            $usercheck = false;
            if ($is_editor || $is_course_admin) {
                $usercheck = true;
            } else {
                $group_members = Database::get()->queryArray("select user_id from group_members where group_id = ?d", $result->id);
                foreach ($group_members as $member) {
                    if (intval($member->user_id) === intval($uid)) {
                        $usercheck = true;
                        break;
                    }
                }
            }

            if ($usercheck) {
                // display extra menu entry for group documents
                $sideMenuText[] = q($GLOBALS['langGroupDocumentsLink'] . "'" . $result->name . "'");
                $sideMenuLink[] = q($urlServer . 'modules/group/document.php' . $params . "&group_id=" . q($result->id));
                $sideMenuImg[] = 'fa-folder-open-o';
            }
        }
    }

    $sideMenuSubGroup[] = $sideMenuText;
    $sideMenuSubGroup[] = $sideMenuLink;
    $sideMenuSubGroup[] = $sideMenuImg;

    $sideMenuGroup[] = $sideMenuSubGroup;
    return $sideMenuGroup;
}

/**
 * @brief display number of open courses
 * @global type $urlServer
 */
function openCoursesExtra() {
    global $urlAppend, $openCoursesExtraHTML;

    if (!isset($openCoursesExtraHTML) and !defined('UPGRADE')) {
        setOpenCoursesExtraHTML();
    }
    $menuGroup = false;
    if (get_config('opencourses_enable') and $openCoursesExtraHTML) {
        $openCoursesNum = Database::get()->querySingle("SELECT COUNT(id) AS count FROM course_review WHERE is_certified = 1")->count;

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
 * @brief display extras left
 * @global type $langExtrasLeft
 * @global type $leftNavExtras
 */
function displayExtrasLeft() {

    global $langExtrasLeft, $leftNavExtras;

    if (isset($langExtrasLeft) and !empty($langExtrasLeft)) {
        $leftNavExtras .= $langExtrasLeft;
    }
}

/**
 * @brief Get number of new documents for current user
 * @global type $session
 * @param type $course_id
 * @return int
 */
function get_new_document_count($course_id) {
    global $session;

    $document_timestamp = $session->getDocumentTimestamp($course_id, false);

    if ($document_timestamp) {
        $count = Database::get()->querySingle('SELECT COUNT(*) AS count
            FROM document WHERE course_id = ?d AND
                date_modified > ?t AND
                subsystem = ?d AND
                format <> ?s',
            $course_id, $document_timestamp, MAIN, '.dir');
        if ($count) {
            return $count->count;
        }
    }
    return 0;
}
