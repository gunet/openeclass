<?php

/* ========================================================================
 * Open eClass 3.0
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
 * ======================================================================== */

$require_login = true;
$require_help = true;
$helpTopic = 'Gradebook';

require_once '../../include/baseTheme.php';
require_once 'modules/gradebook/functions.php';

//Module name
$toolName = $langGradebook;
$content = false;
$grade_content = '';
$courses = Database::get()->queryArray("SELECT course.id course_id, code, title FROM course, course_user, user 
                                            WHERE course.id = course_user.course_id
                                            AND course_user.user_id = ?d 
                                            AND user.id = ?d
                                            AND course.visible != " . COURSE_INACTIVE . "", $uid, $uid);
if (count($courses) > 0) {
    $grade_content .= "<div class ='table-responsive'>
            <table class='table-default'><tr><th>$langCourse</th><th>$langGradebookGrade</th></tr>";
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
        $tool_content .= "<div class='alert alert-warning'>$langNoGradebook</div>";
    } else {
        $tool_content .= $grade_content;
    }
} else {
    $tool_content .= "<div class='alert alert-warning'>$langNoGradebook</div>";
}

draw($tool_content, 1, null, $head_content);