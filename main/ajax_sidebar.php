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
require_once 'perso_functions.php';
require_once 'template/template.inc.php';
require_once 'main/notifications/notifications.inc.php';

header('Content-Type: application/json; charset=UTF-8');

function getSidebarNotifications() {
    global $modules, $admin_modules, $theme_settings, $urlAppend;
    $notifications_html = array();
    if (isset($_GET['courseIDs']) and count($_GET['courseIDs'])) {
        $t = new Template();
        $t->set_var('sideBarCourseNotifyBlock', $_SESSION['template']['sideBarCourseNotifyBlock']);
        foreach ($_GET['courseIDs'] as $id) {
            $course_notifications = '';
            $notifications = get_course_notifications($id);
            $course_code = course_id_to_code($id);
            foreach ($notifications as $n) {
                $modules_array = (isset($modules[$n->module_id]))? $modules: $admin_modules;
                if (isset($modules_array[$n->module_id]) &&
                    isset($modules_array[$n->module_id]['image']) &&
                    isset($theme_settings['icon_map'][$modules_array[$n->module_id]['image']])) {
                    
                    $sideBarCourseNotifyCount = $n->notcount;
                    $sideBarCourseNotifyIcon = $theme_settings['icon_map'][$modules_array[$n->module_id]['image']];
                    $sideBarCourseNotifyURL = $urlAppend . 'modules/' . $modules_array[$n->module_id]['link'] .'/?course=' . $course_code;
                    $sideBarCourseNotifyTitle = q($modules_array[$n->module_id]['title']);
                    $course_notifications .= "<a href='$sideBarCourseNotifyURL' class='item' title='$sideBarCourseNotifyTitle' data-toggle='tooltip'><i class='fa $sideBarCourseNotifyIcon'></i><span class='count'>$sideBarCourseNotifyCount</span></a>";
                }
            }
            $notifications_html[$id] = $course_notifications;
        }
    }
    return $notifications_html;
}

function getSidebarMessages() {
    global $uid, $urlServer, $langFrom, $dateFormatLong, $langDropboxNoMessage, $langMailSubject, $langCourse;

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

            $message_date = claro_format_locale_date($dateFormatLong, $message->timestamp);
            $message_content .= "<li class='list-item'>
                            <span class='item-wholeline'>
                                <div class='text-title'>$langFrom: " .
                                    display_user($message->author_id, false, false) . "<br>
                                    $langMailSubject: <a href='{$urlServer}modules/dropbox/index.php?mid=$message->id'>" .
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

$json_obj = array(
    'messages' => getSidebarMessages(),
    'notifications' => getSidebarNotifications(),
    'langNotificationsExist' => $langNotificationsExist,
);
echo json_encode($json_obj, JSON_UNESCAPED_UNICODE);
