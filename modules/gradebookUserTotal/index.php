<?php

/* ========================================================================
 * Open eClass 3.0
 * E-learning and Course Management System
 * ========================================================================
 * Copyright 2003-2012  Greek Universities Network - GUnet
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
$require_current_course = true;
$require_help = true;
$helpTopic = 'Gradebook';

require_once '../../include/baseTheme.php';
require_once 'include/lib/textLib.inc.php';
require_once 'modules/admin/admin.inc.php';


//Module name
$nameTools = $langGradebook;

$userID = $uid;

$courses = Database::get()->queryArray("SELECT course_id,code,title FROM course_user,course WHERE course_id=id AND visible > 0 AND user_id = ?d", $userID);


$tool_content .= "<table class='sortable'  width='100%' id='t2'><tr><th>$langCourse</th><th>$langGradebookGrade</th><th>$langMore</th></tr>";

foreach ($courses as $course1) {

    $course_id = $course1->course_id;

    $gradebook = Database::get()->querySingle("SELECT id, students_semester,`range` FROM gradebook WHERE course_id = ?d ", $course_id);
    $gradebook_id = $gradebook->id;
    
    $tool_content .= "<tr><td>".$course1->title."</td><td>".userGradeTotal($gradebook_id, $userID)." (μέγιστο: ".$gradebook->range.")</td><td><a href='../gradebook/?course=".$course1->code."'>περισσότερα</a></td></tr>";
    
}

$tool_content .= "</table><br><br>";



//Function to get the total attend number for a user in a course gradebook
function userGradeTotal ($gradebook_id, $userID){

    $userGradeTotal = Database::get()->querySingle("SELECT SUM(grade * weight) as count FROM gradebook_book, gradebook_activities WHERE gradebook_book.uid = ?d AND  gradebook_book.gradebook_activity_id = gradebook_activities.id AND gradebook_activities.gradebook_id = ?d", $userID, $gradebook_id)->count;

    if($userGradeTotal){
        return round($userGradeTotal/100, 2);
    }else{
        return "-";
    }
}


draw($tool_content, 2, null, $head_content);




  