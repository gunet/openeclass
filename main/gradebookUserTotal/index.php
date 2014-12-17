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

//Module name
$pageName = $langGradebook;
$userID = $uid;
$content = false;
$grade_content = '';
$courses = Database::get()->queryArray("SELECT course.id course_id, code, title FROM course, course_user, user 
                                            WHERE course.id = course_user.course_id
                                            AND course_user.user_id = ?d 
                                            AND user.id = ?d
                                            AND course.visible != " . COURSE_INACTIVE . "", $userID, $userID);
if (count($courses) > 0) {
    $grade_content .= "<div class ='table-responsive'>
            <table class='table-default'><tr><th>$langCourse</th><th>$langGradebookGrade</th></tr>";
    foreach ($courses as $course1) {
        $course_id = $course1->course_id;
        $course_code = $course1->code;
        $gradebook = Database::get()->querySingle("SELECT id, students_semester,`range` FROM gradebook WHERE course_id = ?d", $course_id);        
        if ($gradebook) {            
            $gradebook_id = $gradebook->id;
            $grade = userGradeTotal($gradebook_id, $userID, $course_code);
            if ($grade) {
                $content = true;
                $grade_content .= "<tr><td>".$course1->title."</td>
                    <td><a href='../../modules/gradebook/index.php?course=$course_code'>".$grade."</a> <small>($langMax: ".$gradebook->range.")</small></td></tr>";
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


/**
 * @brief get total number of user attend in a course gradebook
 * @param type $gradebook_id
 * @param type $userID
 * @return string
 */
function userGradeTotal ($gradebook_id, $userID, $course_code) {
    
    $visible = 1;
    
    $userGradeTotal = Database::get()->querySingle("SELECT SUM(grade * weight) AS count FROM gradebook_book, gradebook_activities
                                        WHERE gradebook_book.uid = ?d AND  gradebook_book.gradebook_activity_id = gradebook_activities.id 
                                        AND gradebook_activities.gradebook_id = ?d 
                                        AND gradebook_activities.visible = ?d", $userID, $gradebook_id, $visible)->count;

    if ($userGradeTotal) {
        return round($userGradeTotal/100, 2);
    } else {
        return false;
    }
}

draw($tool_content, 1, null, $head_content);




  