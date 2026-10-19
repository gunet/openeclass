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
                                          " . MODULE_ID_STICKY_NOTES . ",
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
function lessonToolsMenu(bool $rich=true, $display_admin_menu = false): array
{
    global $uid, $is_editor, $is_course_admin, $is_course_reviewer,
           $course_code, $modules, $urlAppend, $status, $course_id, $langCourseOptions,
           $langActiveTools, $langInactiveTools, $langLocale, $is_collaborative_course,
           $admin_modules, $langAdministrationTools, $langSyllabus;

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
                        $sideMenuText[] = '<b>' . q($modules[$mid]['title']) .
                            " $mail_status<span tabindex='0' class='badge Neutral-900-bg new-badge-item rounded-circle float-end d-flex justify-content-center align-items-center'>$new_msgs</span></b>";

                    } else {
                        $sideMenuText[] = q($modules[$mid]['title']) . ' ' . $mail_status;
                    }
                } else {
                    $sideMenuText[] = q($modules[$mid]['title']) . ' ' . $mail_status;
                }
            } elseif ($rich and $mid == MODULE_ID_DOCS and ($new_docs = get_new_document_count($course_id))) {
                $sideMenuText[] = '<b>' . q($modules[$mid]['title']) .
                    "<span tabindex='0' class='badge Neutral-900-bg new-badge-item rounded-circle float-end d-flex justify-content-center align-items-center'>$new_docs</span></b>";
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
 * @brief construct course tool menu
 * @return array[]
 */
function courseToolsMenu(): array
{
    global $modules, $urlAppend, $course_code, $course_id, $uid, $status, $is_collaborative_course,
           $is_editor, $is_course_reviewer, $is_course_admin, $langLocale;

    $view_course = Database::get()->querySingle("SELECT view_type FROM course WHERE id = ?d", $course_id);

    $course_tools = $inactive_course_tools = $external_course_tools = $lti_tools = [];
    $mail_status = '';
    $not_public_modules = [ MODULE_ID_CHAT, MODULE_ID_ASSIGN, MODULE_ID_MESSAGE, MODULE_ID_FORUM,
                            MODULE_ID_GROUPS, MODULE_ID_ATTENDANCE, MODULE_ID_GRADEBOOK, MODULE_ID_REQUEST,
                            MODULE_ID_STICKY_NOTES, MODULE_ID_PROGRESS, MODULE_ID_LP, MODULE_ID_TC ];

    $current_module_dir = module_path($_SERVER['REQUEST_URI']);

    if ((isset($is_collaborative_course) and $is_collaborative_course)) {
        $table_modules = 'module_disable_collaboration';
    } else {
        $table_modules = 'module_disable';
    }
    // check if we have defined mail address and want to receive messages
    if ($uid and $status != USER_GUEST and !get_user_email_notification($uid, $course_id)) {
        $mail_status = '&nbsp;' . icon('fa-exclamation-triangle');
    }

    // get all course modules except disabled ones
    $q = Database::get()->queryArray("SELECT module_id, visible FROM course_module WHERE course_id = ?d 
                                                    AND module_id NOT IN (SELECT module_id FROM $table_modules) 
                                                 ORDER BY visible 
                                                DESC
                                                ", $course_id);
    foreach ($q as $tool) {
        $mid = $tool->module_id;
        if ($mid == MODULE_ID_TC and count(get_enabled_tc_services()) == 0) { // don't display teleconference when no tc servers are enabled
            continue;
        }
        if ($mid == MODULE_ID_SESSION and $is_collaborative_course and (is_null($view_course->view_type) or $view_course->view_type != 'sessions')) { // don't display sessions if not enabled
            continue;
        }
        if ((!isset($uid) || $uid == 0) && in_array($mid, $not_public_modules)) { // don't display not_public_modules to unregistered users
            continue;
        }

        if (isset($modules[$mid]['title'])) {
            $item = [
                'visible'     => $tool->visible,
                'title'       => $modules[$mid]['title'],
                'link'        => $urlAppend . 'modules/' . $modules[$mid]['link'] . '/index.php?course=' . $course_code,
                'icon'        => $modules[$mid]['image'],
                'extra_text'  => '',
                'extra'       => '',
            ];

            if ($mid == MODULE_ID_MESSAGE) {
                $mbox = new Mailbox($uid, course_code_to_id($course_code));
                $new_msgs = $mbox->unreadMsgsNumber();
                if ($new_msgs != 0) {
                    $item['extra_text'] =
                    "<strong>
                        $mail_status<span tabindex='0' class='badge Neutral-900-bg new-badge-item rounded-circle float-end d-flex justify-content-center align-items-center'>$new_msgs</span>
                    </strong>";
                } else {
                    $item['extra_text'] = $mail_status;
                }
            } elseif ($mid == MODULE_ID_ANNOUNCE) {
                $item['extra_text'] = $mail_status;
            } elseif ($mid == MODULE_ID_DOCS && ($new_docs = get_new_document_count($course_id))) {
                $item['extra_text'] = "<strong><span tabindex='0' class='badge Neutral-900-bg new-badge-item rounded-circle float-end d-flex justify-content-center align-items-center'>$new_docs</span></strong>";
            }

            if (module_path($item['link']) == $current_module_dir) {
                $item['extra'] = ' in';
            }

            if ($tool->visible == 1) {
                $course_tools[] = $item;
            } else if ($is_editor || $is_course_admin || $is_course_reviewer) {
                $inactive_course_tools[] = $item;
            }
        }
    }

    // display external links (if any)
    $external_links = getExternalLinks();
    if ($external_links) {
        foreach ($external_links as $link) {
            $item =
                [
                    'visible' => 1,
                    'title' => $link->title,
                    'link' => $link->url,
                    'icon' => 'fa fa-external-link',
                    'extra_text' => '',
                    'extra' => 'target="_blank"',
                ];
            $external_course_tools[] = $item;
        }
    }

    // display lti apps as links (if any)
    $lti_result = getLTILinksForTools();
    if ($lti_result) {
        foreach ($lti_result as $lti_link) {
            $item =
            [
                'visible' => 1,
                'title' => $lti_link->title,
                'link' => $lti_link->url,
                'icon' => $lti_link->menulink,
                'extra_text' => '',
                'extra' => '',
            ];
            $lti_tools[] = $item;
        }
    }

    // order course modules according to module title
    setlocale(LC_COLLATE, $langLocale);
    usort($course_tools, function ($a, $b) {
        return strcoll($a['title'], $b['title']);
    });
    usort($inactive_course_tools, function ($a, $b) {
        return strcoll($a['title'], $b['title']);
    });

    // merge all
    return [
        'active'   => $course_tools + $external_course_tools + $lti_tools, // active modules (including external and lti tools)
        'inactive' => $inactive_course_tools, // inactive modules
    ];
}

/**
 * @brief construct picker tool menu (called by TinyMCE buttons)
 * @return array
 */
function pickerToolsMenu(): array
{
    global $modules, $urlAppend, $course_code, $course_id, $is_collaborative_course,
           $session, $langCommonDocs, $langMyDocs,
           $is_editor, $is_course_reviewer, $is_course_admin;

    $picker_tools = $inactive_picker_tools = [];

    if( isset($is_collaborative_course) and $is_collaborative_course) {
        $table = 'module_disable_collaboration';
    } else {
        $table = 'module_disable';
    }

    // params
    $originating_module = isset($_REQUEST['originating_module']) ? intval($_REQUEST['originating_module']) : null;
    $append_module = $originating_module ? "&originating_module=$originating_module" : '';
    $originating_forum = isset($_REQUEST['originating_forum']) ? intval($_REQUEST['originating_forum']) : null;
    $append_forum = $originating_forum ? "&originating_forum=$originating_forum" : '';
    $docsfilter = isset($_REQUEST['docsfilter']) ? ('&docsfilter=' . q($_REQUEST['docsfilter'])) : '';
    $append_course = $course_code ? "&course=$course_code" : '';
    $params = "?embedtype=tinymce" . $append_course . $append_module . $append_forum . $docsfilter;

    $q = Database::get()->queryArray("SELECT module_id, visible FROM course_module
                               WHERE course_id = ?d AND
                                     module_id IN (" . MODULE_ID_DOCS . ', ' . MODULE_ID_VIDEO . ', ' . MODULE_ID_LINKS . ") AND
                                     module_id NOT IN (SELECT module_id FROM $table)                                     
                               ORDER BY visible", $course_id);

    foreach ($q as $tool) {
        $mid = $tool->module_id;
        $item = [
            'visible' => $tool->visible,
            'title' => $modules[$mid]['title'],
            'link' => $urlAppend . 'modules/' . $modules[$mid]['link'] . '/index.php' . $params,
            'icon' => $modules[$mid]['image'],
            'extra_text' => '',
            'extra' => '',
        ];
        if ($tool->visible == 1) {
            $picker_tools[] = $item;
        } else if ($is_editor || $is_course_admin || $is_course_reviewer) {
            $inactive_picker_tools[] = $item;
        }
    }

    if (get_config('enable_common_docs')) {
        $picker_tools[] = [
                'visible' => 1,
                'title' => $langCommonDocs,
                'link' => $urlAppend . 'modules/admin/commondocs.php' . $params,
                'icon' => 'fa-folder-open',
                'extra_text' => '',
                'extra' => '',
        ];
    }

    // link for my documents
    if (($session->status == USER_TEACHER and get_config('mydocs_teacher_enable')) or
        ($session->status == USER_STUDENT and get_config('mydocs_student_enable'))) {
        $picker_tools[] = [
            'visible' => 1,
            'title' => $langMyDocs,
            'link' => $urlAppend . 'main/mydocs/index.php' . $params,
            'icon' => 'fa-folder-open',
            'extra_text' => '',
            'extra' => '',
        ];
    }

    // merge all
    return [
        'active'    => $picker_tools,
        'inactive'  => $inactive_picker_tools,
    ];
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
function get_new_document_count($course_id): int
{
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
