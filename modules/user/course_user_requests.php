<?php

/* ========================================================================
 * Open eClass 
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
 * ======================================================================== 
 */

$require_current_course = true;
$require_course_admin = true;

require_once '../../include/baseTheme.php';

$toolName = $langUserRequests;
$navigation[] = array('url' => "index.php?course=$course_code", 'name' => $langUsers);

$tool_content .= action_bar(array(
        array('title' => "$langBackRequests",
            'url' => "$_SERVER[SCRIPT_NAME]?course=$course_code",
            'icon' => 'fa-reply',
            'level' => 'primary-label',
            'show' => isset($_GET['rid'])),
        array('title' => "$langBack",
            'url' => "index.php?course=$course_code",
            'icon' => 'fa-reply',
            'level' => 'primary')
        ));

if (isset($_GET['rid'])) {
    if (isset($_GET['reg'])) {
        $sql = Database::get()->query("INSERT INTO course_user SET user_id = ?d, course_id = ?d,
                                status = " . USER_STUDENT . ",
                                reg_date = " . DBHelper::timeAfter() . ", 
                                document_timestamp = " . DBHelper::timeAfter() . "", $_GET['u'], $course_id);
        if ($sql) {
            Database::get()->query("UPDATE course_user_request SET status = 2 WHERE id = ?d", $_GET['rid']);
            $tool_content .= "<div class='alert alert-success'>$langCourseUserRegDone</div>";
        } else {
            $tool_content .= "<div class='alert alert-danger'>$langCourseUserRegError</div>";
        }
    } else {
            Database::get()->query("UPDATE course_user_request SET status = 0 WHERE id = ?d", $_GET['rid']);
            $tool_content .= "<div class='alert alert-success'>$langRequestReject</div>";
    }
} else { // display course user requests
    $sql = Database::get()->queryArray("SELECT id, uid, course_id, comments FROM course_user_request WHERE course_id = ?d AND status = 1", $course_id);
    if ($sql) {  
        $tool_content .= "<table class='table-default'>";
        $tool_content .= "<tr>";
        $tool_content .= "<th width='300'>$langSurnameName</th><th>$langComments</th><th width='80' class='text-center'>".icon('fa-gears')."</th>";
        $tool_content .= "</tr>";
        foreach ($sql as $udata) {
            $tool_content .= "<tr>";
            $tool_content .= "<td>" . display_user($udata->uid, false)."</td><td>" . q($udata->comments) . "</td>";
            $tool_content .= "<td>".
                            action_button(array(
                                array('title' => $langRegistration, 
                                  'url' => "$_SERVER[SCRIPT_NAME]?course=$course_code&amp;u=$udata->uid&amp;rid=$udata->id&amp;reg=true",
                                  'icon' => 'fa-plus',
                                  'level' => 'primary'),
                                array('title' => $langRejectRequest, 
                                  'url' => "$_SERVER[SCRIPT_NAME]?course=$course_code&amp;u=$udata->uid&amp;rid=$udata->id&amp;rej=true",
                                  'icon' => 'fa-times',
                                  'level' => 'primary')                                 
                                 )).
                        "</td>";
            $tool_content .= "</tr>";
        }
        $tool_content .= "</table>";
    } else {
        $tool_content .= "<div class='alert alert-warning'>$langUserNoRequests</div>";
    }
}

draw($tool_content, 2);