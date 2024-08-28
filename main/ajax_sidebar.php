<?php

/* ========================================================================
 * Open eClass 3.0
 * E-learning and Course Management System
 * ========================================================================
 * Copyright 2003-2015  Greek Universities Network - GUnet
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
 * @file ajax_sidebar.php
 * @brief Sidebar AJAX handler
 */
$require_login = true;

require_once '../include/baseTheme.php';
require_once 'portfolio_functions.php';
require_once 'template/template.inc.php';
require_once 'main/notifications/notifications.inc.php';

header('Content-Type: application/json; charset=UTF-8');

function getSidebarNotifications() {
    global $modules, $theme_settings, $urlAppend;

    $notifications_html = array();
    if (isset($_GET['courseIDs']) and count($_GET['courseIDs']) > 0) {
        $t = new Template();
        $t->set_var('sideBarCourseNotifyBlock', $_SESSION['template']['sideBarCourseNotifyBlock']);
        foreach ($_GET['courseIDs'] as $id) {
            $t->set_var('sideBarCourseNotify', '');
            $notifications = get_course_notifications($id);
            $course_code = course_id_to_code($id);
            foreach ($notifications as $n) {
                $modules_array = (isset($modules[$n->module_id]))? $modules : '';
                if (isset($modules_array[$n->module_id]) &&
                    (!is_module_visible($n->module_id, $id))) {
                        continue;
                }
                if (isset($modules_array[$n->module_id]) &&
                    isset($modules_array[$n->module_id]['image']) &&
                    isset($theme_settings['icon_map'][$modules_array[$n->module_id]['image']])) {
                    $t->set_var('sideBarCourseNotifyIcon', $theme_settings['icon_map'][$modules_array[$n->module_id]['image']]);
                    $t->set_var('sideBarCourseNotifyCount', $n->notcount);
                    $t->set_var('sideBarCourseNotifyTitle', q($modules_array[$n->module_id]['title']));
                    $t->set_var('sideBarCourseNotifyURL', $urlAppend . 'modules/' . $modules_array[$n->module_id]['link'] .
                                                    '/index.php?course=' . $course_code);
                    $t->parse('sideBarCourseNotify', 'sideBarCourseNotifyBlock', true);
                }
            }
            $notifications_html[$id] = $t->get_var('sideBarCourseNotify');
        }
    }
    return $notifications_html;
}

function getCoursesNotifications() {
    global $modules, $icons_map, $urlAppend, $langNoExistNotifications;

    $notifications_arr = array();
    $notification_content = array();
    if (isset($_GET['courseIDs']) and count($_GET['courseIDs']) > 0) {

        foreach ($_GET['courseIDs'] as $id) {
            $existNotification = 0;
            $notifications = get_course_notifications($id);
            $course_code = course_id_to_code($id);
            $course_title = course_id_to_title($id);

            $notification_content['notification_content'] = "
                <div class='panel panelCard border-0 mt-2'>
                    <div class='card-body p-lg-2 p-3'>
                        <div class='d-flex justify-content-start align-items-center gap-4 flex-wrap'>";
            foreach ($notifications as $n) {
                $modules_array = (isset($modules[$n->module_id]))? $modules : '';
                if (isset($modules_array[$n->module_id]) &&
                    (!is_module_visible($n->module_id, $id))) {
                        continue;
                }

                if (isset($modules_array[$n->module_id]['image']) && isset($icons_map['icon_map'][$n->module_id])) {
                    $sideBarCourseNotifyIcon = $icons_map['icon_map'][$n->module_id];
                    $sideBarCourseNotifyCount = $n->notcount;
                    $sideBarCourseNotifyTitle = q($modules_array[$n->module_id]['title']);
                    $sideBarCourseNotifyURL = $urlAppend . 'modules/' . $modules_array[$n->module_id]['link'] .
                                                    '/index.php?course=' . $course_code;

                    $notification_content['notification_content'] .= "
                            <a type='button' class='btn btn-sm btn-portfolio-notifications text-decoration-none position-relative link-color' href='$sideBarCourseNotifyURL'
                                data-bs-toggle='tooltip' data-bs-placement='bottom' data-bs-original-title='$sideBarCourseNotifyTitle' aria-label='$sideBarCourseNotifyTitle'>
                                <i class='$sideBarCourseNotifyIcon fa-md'></i>
                                <span class='position-absolute top-0 start-100 translate-middle badge rounded-pill Accent-200-bg vsmall-text'>
                                    $sideBarCourseNotifyCount
                                    <span class='visually-hidden'></span>
                                </span>
                            </a>
                    ";

                    $existNotification++;

                }
            }
            if($existNotification ==  0){
                $notification_content['notification_content'] .= "<p class='no_exist_notification_{$id}'>$langNoExistNotifications</p>";
            }
            $notification_content['notification_content'] .= "
                        </div>
                    </div>
                </div>";

            $notifications_arr[$id] = $notification_content;
        }
    }

    return $notifications_arr;
}

function getSidebarMessages() {
    global $uid, $urlServer, $langFrom, $langDropboxNoMessage, $langMailSubject, $langCourse;

    $message_content = '';

    $mbox = new Mailbox($uid, 0);
    $msgs = $mbox->getInboxMsgs('');

    $msgs = array_filter($msgs, function ($msg) { return !$msg->is_read; });
    if (!count($msgs)) {
        $message_content .= "<li class='list-item no-messages'>" .
                            "<span class='item-wholeline'>" .
                                $langDropboxNoMessage .
                            "</span>" .
                         "</li>";
    } else {
        foreach ($msgs as $message) {
            if ($message->course_id > 0) {
                $course_title = q(ellipsize(course_id_to_title($message->course_id), 30));
            } else {
                $course_title = '';
            }

            $message_date = format_locale_date($message->timestamp);
            $message_content .= "<li class='list-item'>
                            <span class='item-wholeline'>
                                <div class='text-title'>$langFrom: " .
                                    display_user($message->author_id, false, false) . "<br>
                                    $langMailSubject: <a href='{$urlServer}modules/message/index.php?mid=$message->id'>" .
                                        q($message->subject) . "</a>
                                </div>";
                                    if ($course_title) {
                                       $message_content .= "<div class='text-grey'>$langCourse: $course_title</div>";
                                    }
                                $message_content .= "<div>$message_date</div>
                                </span>
                            </li>";
        }
    }
    return $message_content;
}


function is_module_visible($mid, $cid) {

    $v = Database::get()->querySingle("SELECT visible FROM course_module
                                WHERE module_id = ?d AND
                                course_id = ?d", $mid, $cid)->visible;

    if ($v == 1) {
        return true;
    } else {
        return false;
    }
}


$json_obj = array(
    'messages' => getSidebarMessages(),
    //'notifications' => getSidebarNotifications(),
    'notifications_courses' => getCoursesNotifications(),
    'langNotificationsExist' => $langNotificationsExist,
);

echo json_encode($json_obj, JSON_UNESCAPED_UNICODE);
