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

define('REQUEST_STATE_NEW', 1);
define('REQUEST_STATE_ASSIGNED', 2);
define('REQUEST_STATE_LOCKED', 3);
define('REQUEST_STATE_CLOSED', 4);
define('REQUEST_ASSIGNED', 1);
define('REQUEST_WATCHER', 2);

define('REQUEST_FIELD_TEXTBOX', 1);
define('REQUEST_FIELD_TEXTAREA', 2);
define('REQUEST_FIELD_DATE', 3);
define('REQUEST_FIELD_MENU', 4);
define('REQUEST_FIELD_LINK', 5);
define('REQUEST_FIELD_MENU_EDITABLE', 6);

$stateLabels = [
    REQUEST_STATE_NEW => $langRequestStateNew,
    REQUEST_STATE_ASSIGNED => $langRequestStateAssigned,
    REQUEST_STATE_LOCKED => $langRequestStateLocked,
    REQUEST_STATE_CLOSED => $langRequestStateClosed
];

function getWatchers($rid, $type) {
    return array_map(function ($item) {
            return $item->user_id;
        }, Database::get()->queryArray("SELECT user_id
                FROM request_watcher
                WHERE request_id = ?d AND type = ?d
                ORDER BY id", $rid, $type));
}

function commentFileLink($comment) {
    global $urlAppend, $course_code;

    return $urlAppend . 'modules/request/file.php?course=' . $course_code . '&id=' . $comment->id;
}

function format_ts($datetime) {
    $ts = strtotime($datetime);
    $hour = 60 * 60;
    $day = 60 * 60 * 24;
    $output = '';
    $diff = time() - $ts;
    if ($diff > $day) {
        $days = intval($diff / $day);
        $output = sprintf($days == 1? trans('langDayAgo'): trans('langDaysAgo'), $days);
    } elseif ($diff > $hour) {
        $hours = ceil($diff / 60 / 60);
        $output = sprintf($hours == 1? trans('langHourAgo'): trans('langHoursAgo'), $hours);
    } elseif ($diff > 0) {
        $mins = ceil($diff / 60 / 60);
        $output = sprintf($mins == 1? trans('langMinuteAgo'): trans('langMinutesAgo'), $mins);
    }

    if ($output) {
        $output = '<br><span class="small">' . $output . '</span>';
    }

    return date('Y-m-d', $ts) . $output;
}
