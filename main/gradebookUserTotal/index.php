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

$require_login = true;
$require_help = true;
$helpTopic = 'Gradebook';

require_once '../../include/baseTheme.php';
require_once 'modules/gradebook/functions.php';

if (is_module_disable(MODULE_ID_GRADEBOOK,MODULE_ID_GRADEBOOK)) {
    redirect_to_home_page();
}

$toolName = $langPortfolio;
$pageName = $langGradebook;
$content = false;
$grade_content = '';
$courses = Database::get()->queryArray('SELECT course.id course_id, code, title
                FROM course, course_user, user, course_module
                    WHERE course.id = course_user.course_id
                      AND course.visible <> ' . COURSE_INACTIVE . '
                      AND course_module.course_id = course_user.course_id
                      AND module_id = ' . MODULE_ID_GRADEBOOK . '
                      AND course_module.visible <> 0
                      AND course_user.user_id = ?d
                      AND user.id = ?d', $uid, $uid);


if (count($courses) > 0) {
    $grade_content .= "<div class ='table-responsive'>
            <table class='table-default'><thead><tr class='list-header'><th>$langCourse</th><th>$langGradebookGrade</th></tr></thead>";
    foreach ($courses as $course1) {
        $course_id = $course1->course_id;
        $code = $course1->code;
        $gradebook = Database::get()->queryArray("SELECT * FROM gradebook_users WHERE uid = ?d
                                    AND gradebook_id IN (SELECT id FROM gradebook WHERE active = 1 AND course_id = ?d)", $uid, $course_id);
        foreach ($gradebook as $gd) {
            $gradebook_id = $gd->gradebook_id; // if course has one gradebook
            $range = get_gradebook_range($gradebook_id);
            $gd_title = get_gradebook_title($gradebook_id);
            $grade = userGradeTotal($gradebook_id, $uid, $code, true);
            if ($grade) {
                $content = true;
                $grade_content .= "<tr><td>" . $course1->title . " ($gd_title)</td>
                    <td><a href='../../modules/gradebook/index.php?course=$code&amp;gradebook_id=" . getIndirectReference($gradebook_id). "'>" . $grade ." / " . $range . "</a></td></tr>";
            }
        }
    }
    $grade_content .= "</table></div>";
    if (!$content) {
        $tool_content .= "<div class='col-12'>
        <div class='alert alert-warning'><i class='fa-solid fa-triangle-exclamation fa-lg'></i><span>$langNoGradebook</span></div></div>";
    } else {
        $tool_content .= $grade_content;
    }
} else {
    $tool_content .= "<div class='col-12'><div class='alert alert-warning'><i class='fa-solid fa-triangle-exclamation fa-lg'></i><span>$langNoGradebook</span></div></div>";
}

draw($tool_content, 1, null, $head_content);
