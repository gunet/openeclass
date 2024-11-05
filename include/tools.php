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

/**
 * @author Evelthon Prodromou <eprodromou@upnet.gr>
 * @abstract This component creates an array of the tools that are displayed on the left side column
 */

require_once 'modules/tc/functions.php';
require_once 'modules/message/class.mailbox.php';
require_once 'modules/lti_consumer/lti-functions.php';

/**
 * @brief Queries the database for tool information in accordance
 * to the parameter passed.
 * @param string $cat Type of lesson tools
 * @return array of course_module objects
 * @see function lessonToolsMenu
 */
function getToolsArray($cat) {
    global $course_code, $is_collaborative_course;

    if ((isset($is_collaborative_course) and $is_collaborative_course)) {
        $table_modules = 'module_disable_collaboration';
    } else {
        $table_modules = 'module_disable';
    }
    $cid = course_code_to_id($course_code);

    switch ($cat) {
        case 'Public':
            $sql = "SELECT * FROM course_module
                        WHERE visible = 1 AND
                        course_id = $cid AND
                        module_id NOT IN (" . MODULE_ID_CHAT . ",
                                          " . MODULE_ID_ASSIGN . ",
                                          " . MODULE_ID_MESSAGE . ",
                                          " . MODULE_ID_FORUM . ",
                                          " . MODULE_ID_GROUPS . ",
                                          " . MODULE_ID_ATTENDANCE . ",
                                          " . MODULE_ID_GRADEBOOK . ",
                                          " . MODULE_ID_REQUEST . ",
                                          " . MODULE_ID_PROGRESS . ",
                                          " . MODULE_ID_LP . ",
                                          " . MODULE_ID_TC . ") AND
                        module_id NOT IN (SELECT module_id FROM $table_modules)";
            if (!check_guest()) {
                if (isset($_SESSION['uid']) and $_SESSION['uid']) {
                    $result = Database::get()->queryArray("SELECT * FROM course_module
                            WHERE visible = 1 AND
                                  module_id NOT IN (SELECT module_id FROM $table_modules) AND
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
                                               module_id NOT IN (SELECT module_id FROM $table_modules) AND
                                         course_id = ?d", $cid);
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
 * @brief get course external links
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
 * @brief Creates a multidimensional array of the user's tools
 * in regard to the user's user level
 * (student | professor | platform administrator)
 * @param bool $rich Whether to include rich text notifications in title
 * @return array
 */
function lessonToolsMenu(bool $rich=true): array
{
    global $uid, $is_editor, $is_course_admin, $is_course_reviewer,
           $course_code, $modules, $urlAppend, $status, $course_id, $langCourseOptions,
           $langActiveTools, $langInactiveTools, $langLocale, $is_collaborative_course;

    $current_module_dir = module_path($_SERVER['REQUEST_URI']);

    $sideMenuGroup = array();
    $arrMenuType = array();
    $arrMenuType['type'] = 'none';

    if ($is_editor || $is_course_admin || $is_course_reviewer) {
        $tools_sections =
            array(array('type' => 'Public',
                        'title' => $langActiveTools,
                        'class' => 'active'),
                  array('type' => 'PublicButHide',
                        'title' => $langInactiveTools,
                        'class' => 'inactive'));
    } else {
        $tools_sections =
            array(array('type' => 'Public',
                        'title' => $langCourseOptions,
                        'class' => 'active'));
    }

    // Check if a course is collaborative and its view-type is different than session-view-type.
    // If it's true then hide the module session.
    $view_course = Database::get()->querySingle("SELECT view_type FROM course WHERE id = ?d", $course_id);

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

        if ($current_module_dir == 'course_home' && $section['class'] == 'active') {
            $arrMenuType['class'] = ' in';
        }
        $sideMenuSubGroup[] = $arrMenuType;

        setlocale(LC_COLLATE, $langLocale);
        usort($result, function ($a, $b) {
            global $modules;
            return strcoll($modules[$a->module_id]['title'], $modules[$b->module_id]['title']);
        });

        // check if we have defined mail address and want to receive messages
        if ($uid and $status != USER_GUEST and !get_user_email_notification($uid, $course_id)) {
            $mail_status = '&nbsp;' . icon('fa-exclamation-triangle');
        }

        foreach ($result as $toolsRow) {
            $mid = $toolsRow->module_id;

            // hide teleconference when no tc servers are enabled
            if ($mid == MODULE_ID_TC and count(get_enabled_tc_services()) == 0) {
                continue;
            }

            if ($mid == MODULE_ID_SESSION and $is_collaborative_course and 
                (is_null($view_course->view_type) or $view_course->view_type != 'sessions')) {
                    continue;
            }

            // if we are in messages or announcements add (if needed) mail address status
            if ($rich and ($mid == MODULE_ID_MESSAGE or $mid == MODULE_ID_ANNOUNCE)) {
                if ($mid == MODULE_ID_MESSAGE) {
                    $mbox = new Mailbox($uid, course_code_to_id($course_code));
                    $new_msgs = $mbox->unreadMsgsNumber();
                    if ($new_msgs != 0) {
                        $sideMenuText[] = '<b class=>' . q($modules[$mid]['title']) .
                            " $mail_status<span class='badge Neutral-900-bg new-badge-item rounded-circle float-end d-flex justify-content-center align-items-center'>$new_msgs</span></b>";

                    } else {
                        $sideMenuText[] = q($modules[$mid]['title']) . ' ' . $mail_status;
                    }
                } else {
                    $sideMenuText[] = q($modules[$mid]['title']) . ' ' . $mail_status;
                }
            } elseif ($rich and $mid == MODULE_ID_DOCS and ($new_docs = get_new_document_count($course_id))) {
                $sideMenuText[] = '<b class=>' . q($modules[$mid]['title']) .
                    "<span class='badge Neutral-900-bg new-badge-item rounded-circle float-end d-flex justify-content-center align-items-center'>$new_docs</span></b>";
            } else {
                $sideMenuText[] = q($modules[$mid]['title']);
            }

            $module_link = $urlAppend . 'modules/' . $modules[$mid]['link'] .
                            '/index.php?course=' . $course_code;
            if (module_path($module_link) == $current_module_dir) {
                $sideMenuSubGroup[0]['class'] = ' in';
            }
            $sideMenuLink[] = $module_link;
            $sideMenuImg[] = $modules[$mid]['image'];
            $sideMenuID[] = $mid;

        }

        if ($section['type'] == 'Public') {
            $result2 = getExternalLinks();
            if ($result2) { // display external links (if any)
                foreach ($result2 as $ex_link) {
                    $sideMenuText[] = q($ex_link->title);
                    $sideMenuLink[] = $ex_link->url;
                    $sideMenuImg[] = 'fa-external-link';
                    $sideMenuID[] = -1;
                }
            }
        }

        if ($section['type'] == 'Public') {
            $result3 = getLTILinksForTools();
            if ($result3) { // display lti apps as links (if any)
                foreach ($result3 as $lti_link) {
                    $sideMenuText[] = q($lti_link->title);
                    $sideMenuLink[] = $lti_link->url;
                    $sideMenuImg[] = q($lti_link->menulink);
                    $sideMenuID[] = -1;
                }
            }
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

    global $urlServer, $course_code, $course_id, $is_editor, $is_course_admin, $modules, $session,
           $uid, $is_collaborative_course, $langBasicOptions, $langMyDocs, $langCommonDocs;

    if( isset($is_collaborative_course) and $is_collaborative_course) {
        $table = 'module_disable_collaboration';
    } else {
        $table = 'module_disable';
    }

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
    $arrMenuType['text'] = $langBasicOptions;
    $arrMenuType['class'] = 'picker';
    $sideMenuSubGroup[] = $arrMenuType;

    if (isset($course_id) and $course_id >= 1) {
        $visible = ($is_editor) ? '' : 'AND visible = 1';
        $result = Database::get()->queryArray("SELECT * FROM course_module
                               WHERE course_id = ?d AND
                                     module_id IN (" . MODULE_ID_DOCS . ', ' . MODULE_ID_VIDEO . ', ' . MODULE_ID_LINKS . ") AND
                                     module_id NOT IN (SELECT module_id FROM $table)
                                     $visible
                               ORDER BY module_id", $course_id);

        foreach ($result as $module) {
            $mid = $module->module_id;
            $sideMenuText[] = q($modules[$mid]['title']);
            $sideMenuLink[] = $urlServer . 'modules/' . $modules[$mid]['link'] . '/index.php' . $params;
            $sideMenuImg[] = $modules[$mid]['image'] . "_on.png";
        }
    }

    // link for common documents
    if (get_config('enable_common_docs')) {
        $sideMenuText[] = $langCommonDocs;
        $sideMenuLink[] = $urlServer . 'modules/admin/commondocs.php' . $params;
        $sideMenuImg[] = 'fa-folder-open';
    }

    // link for my documents
    if (($session->status == USER_TEACHER and get_config('mydocs_teacher_enable')) or
        ($session->status == USER_STUDENT and get_config('mydocs_student_enable'))) {
            $sideMenuText[] = $langMyDocs;
            $sideMenuLink[] = $urlServer . 'main/mydocs/index.php' . $params;
            $sideMenuImg[] = 'fa-folder-open';
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
                $group_members = Database::get()->queryArray("SELECT user_id FROM group_members WHERE group_id = ?d", $result->id);
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
                $sideMenuImg[] = 'fa-folder-open';
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
 * @brief Get number of new documents for current user
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
