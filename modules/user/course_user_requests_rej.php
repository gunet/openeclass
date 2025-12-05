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

$require_current_course = true;
$require_course_admin = true;

require_once '../../include/baseTheme.php';
require_once 'include/sendMail.inc.php';

$toolName = $langRejectedRequests;
$navigation[] = array('url' => "index.php?course=$course_code", 'name' => $langUsers);

$action_bar = action_bar(array(
    array('title' => "$langUsers",
        'url' => "index.php?course=$course_code",
        'icon' => 'fa-users',
        'level' => 'primary-label'),
    array('title' => "$langUserRequests",
        'url' => "course_user_requests.php?course=$course_code",
        'icon' => 'fa-users',
        'level' => 'secondary'),
    array('title' => "$langAcceptedRequests",
        'url' => "course_user_requests_appr.php?course=$course_code",
        'icon' => 'fa-users',
        'level' => 'secondary'),
    array('title' => "$langBackRequests",
        'url' => "$_SERVER[SCRIPT_NAME]?course=$course_code",
        'icon' => 'fa-reply',
        'level' => 'primary-label',
        'show' => isset($_GET['rid']))
));
$tool_content .= $action_bar;

$sql_rej = Database::get()->queryArray("SELECT * FROM course_user_request WHERE course_id = ?d AND status = 0 ORDER BY ts_update DESC", $course_id);

if ($sql_rej) {
    $tool_content .= "<div class='col-sm-12'><div class='table-responsive'><table class='table-default'>";
    $tool_content .= "<thead><tr class='list-header'>";
    $tool_content .= "<th width='320'>$langSurnameName</th><th>$langComments</th><th>$langReasonReject</th><th>$langDateRequest</th><th>$langDateReject</th>";
    $tool_content .= "</tr></thead>";
    foreach ($sql_rej as $udata) {
        $am_message = '';
        $user_am = uid_to_am($udata->uid);
        if ($user_am) {
            $am_message = "$langAm: $user_am";
        }

        $tool_content .= "<tr>";
        $tool_content .= "<td>" . display_user($udata->uid, false)."<br>&nbsp;&nbsp;<small>$am_message</small></td>";
        $tool_content .= "<td>" . q($udata->comments) . "</td>";
        $tool_content .= "<td>" . q($udata->comment_rejected) . "</td>";
        $tool_content .= "<td>" . format_locale_date(strtotime($udata->ts)) . "</td>";
        $tool_content .= "<td>" . format_locale_date(strtotime($udata->ts_update)) . "</td>";
        $tool_content .= "</tr>";
    }
    $tool_content .= "</table></div></div>";
}

draw($tool_content, 2);