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
        $gradebook = Database::get()->querySingle("SELECT id, students_semester,`range` FROM gradebook WHERE course_id = ?d", $course_id);        
        if ($gradebook) {            
            $gradebook_id = $gradebook->id;
            $grade = userGradeTotal($gradebook_id, $uid, $code, true);
            if ($grade) {
                $content = true;
                $grade_content .= "<tr><td>".$course1->title."</td>
                    <td><a href='../../modules/gradebook/index.php?course=$code'>" . $grade ." / " . $gradebook->range . "</a></td></tr>";
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