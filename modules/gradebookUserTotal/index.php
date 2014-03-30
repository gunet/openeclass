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

//define('COURSE_USERS_PER_PAGE', 15);

//activity types (new)
define('GRADE_ORAL', "Προφορικός βαθμός"); //1
define('GRADE_LAB', "Βαθμός εργαστηρίου"); //2
define('GRADE_PROGRESS', "Βαθμός προόδου"); //3
define('GRADE_EXAMS', "Γραπτές εξατάσεις"); //4
define('GRADE_OTHER_TYPE', "Άλλη δραστηριότητα"); //5


//Module name
$nameTools = $langGradebook;

$userID = $uid;

$courses = Database::get()->queryArray("SELECT course_id,code,title FROM course_user,course WHERE course_id=id AND visible > 0 AND user_id = ?d", $userID);


$tool_content .= "<table class='sortable'  width='100%' id='t2'><tr><th>Μάθημα</th><th>Βαθμός</th><th>Πληροφορίες</th></tr>";

foreach ($courses as $course1) {

    $course_id = $course1->course_id;

    //gradebook_id for the course: check if there is an gradebook module for the course. If not insert it
    $gradebook = Database::get()->querySingle("SELECT id, students_semester,`range` FROM gradebook WHERE course_id = ?d ", $course_id);
    $gradebook_id = $gradebook->id;
    
    
   $tool_content .= "<tr><td>".$course1->title."</td><td>".userGradeTotal($gradebook_id, $userID)." (μέγιστο: ".$gradebook->range.")</td><td><a href='../gradebook/?course=".$course1->code."'>περισσότερα</a></td></tr>";
 
    
    
}

$tool_content .= "</table><br><br>";





foreach ($courses as $course) {

    $course_id = $course->course_id;

    //gradebook_id for the course: check if there is an gradebook module for the course. If not insert it
    $gradebook = Database::get()->querySingle("SELECT id, students_semester,`range` FROM gradebook WHERE course_id = ?d ", $course_id);
    $gradebook_id = $gradebook->id;
    $gradebook_range = $gradebook->range;
    $showSemesterParticipants = $gradebook->students_semester;

    
    //check if there are grade records for the user, otherwise alert message that there is no input
    $checkForRecords = Database::get()->querySingle("SELECT COUNT(gradebook_book.id) as count FROM gradebook_book, gradebook_activities WHERE gradebook_book.gradebook_activity_id = gradebook_activities.id AND uid = ?d AND gradebook_activities.gradebook_id = ?d", $userID, $gradebook_id)->count;
    if (!$checkForRecords) {
        $tool_content .="<div class='alert1'>Δεν έχει γίνει ακόμη καταχώρηση βαθμών</div>";
    }


    $result = Database::get()->queryArray("SELECT * FROM gradebook_activities  WHERE gradebook_id = ?d  ORDER BY `DATE` DESC", $gradebook_id);
    $announcementNumber = count($result);

    if ($announcementNumber > 0) {
        $tool_content .= "<fieldset><legend>$course->title</legend>";
        $tool_content .= "<div class='center'>Συνολικός βαθμός: " . userGradeTotal($gradebook_id, $userID) . " </div><br>";

        if(weightleft($gradebook_id, 0) != 0){
            $tool_content .= "<p class='alert1'>Προσοχή το βαθμολόγιο είναι σε επεξεργασία και μπορεί οι βαθμοί να αλλάξουν</p>";
        }


        $tool_content .= "<script type='text/javascript' src='../auth/sorttable.js'></script>
                            <table width='100%' class='sortable' id='t2'>";
        $tool_content .= "<tr><th  colspan='2'>Τίτλος</th><th >Ημερομηνία</th><th>Περιγραφή</th><th>Βαρύτητα</th><th>Βαθμός</th></tr>";
    } else {
        $tool_content .= "<p class='alert1'>Δεν υπάρχουν δραστηριότητες στο παρουσίολόγιο</p>";
    }
    $k = 0;

    if ($result)
        foreach ($result as $announce) {

            //check if the user has attend for this activity
            $userAttend = Database::get()->querySingle("SELECT grade FROM gradebook_book  WHERE gradebook_activity_id = ?d AND uid = ?d", $announce->id, $userID)->grade;

            $content = standard_text_escape($announce->description);
            $announce->date = claro_format_locale_date($dateFormatLong, strtotime($announce->date));

            if ($k % 2 == 0) {
                $tool_content .= "<tr class='even'>";
            } else {
                $tool_content .= "<tr class='odd'>";
            }

            $tool_content .= "<td width='16' valign='top'>
                        <img style='padding-top:3px;' src='$themeimg/arrow.png' title='bullet' /></td>
                        <td><b>";

            if (empty($announce->title)) {
                $tool_content .= $langAnnouncementNoTille;
            } else {
                $tool_content .= q($announce->title);
            }
            $tool_content .= "</b>";
            $tool_content .= "</td>"
                    . "<td><div class='smaller'>" . nice_format($announce->date) . "</div></td>"
                    . "<td>" . $content . "</td>"
                    . "<td>" . q($announce->weight) . "%</td>";

            $tool_content .= "
                <td width='70' class='center'>";

            if ($userAttend) {
                $tool_content .= $userAttend;
            }else{
                $tool_content .= "Δεν υπάρχει καταχώρηση";
            }
            $tool_content .= "</td>";

            $k++;
        } // end of while
    $tool_content .= "</table></fieldset>";
}


//
function actType($actType){
    switch ($actType) {
        case 1: return "(Προφορικός βαθμός)";
        case 2: return "(Βαθμός εργαστηρίου)";
        case 3: return "(Βαθμός προόδου)";
        case 4: return "(Γραπτές εξατάσεις)";
        case 5: return "(Άλλη δραστηριότητα)";
        default : return "";
            
    }
}

//
function typeSelected($type, $optionType){
    if($type == $optionType){
        return "selected";
    }
}

//
function weightleft($gradebook_id, $currentActivity){
    if($currentActivity){
        $left = Database::get()->querySingleNT("SELECT SUM(weight) as count FROM gradebook_activities WHERE gradebook_id = ?d AND id != ?d", $gradebook_id, $currentActivity)->count;
    }else{
        $left = Database::get()->querySingleNT("SELECT SUM(weight) as count FROM gradebook_activities WHERE gradebook_id = ?d", $gradebook_id)->count;
    }
    if($left > 0 ){
        return 100-$left;
    }else{
        return 0;
    }
    
}


//Function to return auto grades
function attendForAutoGrades($userID, $exeID, $exeType, $range){
    if($exeType == 1){ //asignments: valid submission!
       $autoAttend = Database::get()->querySingle("SELECT grade FROM assignment_submit WHERE uid = ?d AND assignment_id = ?d", $userID, $exeID)->grade; 
       if($autoAttend >= 0){
           return $autoAttend;
       }else{
           return "";
       }
    }else if($exeType == 2){ //exercises (if there are more than one attemps we take the last)
       $autoAttend = Database::get()->querySingle("SELECT total_score, total_weighting FROM exercise_user_record WHERE uid = ?d AND eid = ?d ORDER BY `record_end_date` DESC LIMIT 1", $userID, $exeID); 
       $score = $autoAttend->total_score;
       $scoreMax = $autoAttend->total_weighting;
       if($score >= 0){
           if($scoreMax){
               return ($range * $score) / $scoreMax;
           }else{
               return $score;
           }
       }else{
           return "";
       }
    }else if($exeType == 3){ //lps (exes and scorms)
       $autoAttend = Database::get()->querySingle("SELECT raw, scoreMax
               FROM lp_user_module_progress, lp_rel_learnPath_module, lp_module 
               WHERE lp_module.module_id = ?d  
               AND lp_user_module_progress.user_id = ?d 
               AND lp_module.module_id = lp_rel_learnPath_module.module_id
               AND lp_rel_learnPath_module.learnPath_module_id = lp_user_module_progress.learnPath_module_id
               AND (lp_user_module_progress.lesson_status = 'FAILED' OR lp_user_module_progress.lesson_status = 'PASSED')
               ", $exeID, $userID);
       $score = $autoAttend->raw;
       $scoreMax = $autoAttend->scoreMax;
       if($score >= 0){ //to avoid the -1 for no score
           if($scoreMax){
               return ($range * $score) / $scoreMax;
           }else{
               return $score;
           }
       }else{
           return "";
       }
    }
}



//Function to get the total attend number for a user in a course gradebook
function userGradeTotal ($gradebook_id, $userID){

    $userGradeTotal = Database::get()->querySingleNT("SELECT SUM(grade * weight) as count FROM gradebook_book, gradebook_activities WHERE gradebook_book.uid = ?d AND  gradebook_book.gradebook_activity_id = gradebook_activities.id AND gradebook_activities.gradebook_id = ?d", $userID, $gradebook_id)->count;

    if($userGradeTotal){
        return round($userGradeTotal/100, 2);
    }else{
        return "-";
    }
}


//Function to get the total gradebook number 
function userGradebookTotalActivityStats ($activityID, $participantsNumber, $showSemesterParticipants, $courseID, $limitDate){
    //check who to include in the stats
    if($showSemesterParticipants){
        
        $sumGrade = "";
        $userGradebookTotalActivity = Database::get()->queryArray("SELECT grade, uid FROM gradebook_book WHERE gradebook_activity_id = ?d ", $activityID);
        foreach ($userGradebookTotalActivity as $module) {
            $check = Database::get()->querySingleNT("SELECT id FROM actions_daily WHERE actions_daily.day > ?t AND actions_daily.`course_id` = ?d AND actions_daily.user_id =?d ", $limitDate, $courseID, $module->uid);
            if($check){
                $sumGrade += $module->grade;
            }
        }
        
        $userGradebookTotalActivityMin = Database::get()->querySingleNT("SELECT grade
                FROM gradebook_book,course_user, user, actions_daily
                WHERE uid = `user`.id 
                AND `user`.id = `course_user`.`user_id`
                AND `user`.id = actions_daily.user_id
                AND actions_daily.day > ?t
                AND `course_user`.`course_id` = ?d
                AND gradebook_activity_id = ?d 
                GROUP BY actions_daily.user_id ORDER BY grade ASC limit 1 ", $limitDate, $courseID, $activityID)->grade;
        $userGradebookTotalActivityMax = Database::get()->querySingleNT("SELECT grade
                FROM gradebook_book,course_user, user, actions_daily
                WHERE uid = `user`.id 
                AND `user`.id = `course_user`.`user_id`
                AND `user`.id = actions_daily.user_id
                AND actions_daily.day > ?t
                AND `course_user`.`course_id` = ?d
                AND gradebook_activity_id = ?d 
                GROUP BY actions_daily.user_id ORDER BY grade DESC limit 1", $limitDate, $courseID, $activityID)->grade;
    }else{
        $sumGrade = Database::get()->querySingleNT("SELECT SUM(grade) as count FROM gradebook_book WHERE gradebook_activity_id = ?d ", $activityID)->count;
        $userGradebookTotalActivityMin = Database::get()->querySingleNT("SELECT grade FROM gradebook_book WHERE gradebook_activity_id = ?d ORDER BY grade ASC limit 1", $activityID)->grade;
        $userGradebookTotalActivityMax = Database::get()->querySingleNT("SELECT grade FROM gradebook_book WHERE gradebook_activity_id = ?d ORDER BY grade DESC limit 1", $activityID)->grade;
    }
    
//check if participantsNumber is zero
    if($participantsNumber){
        $mean = round($sumGrade/$participantsNumber, 2);
        return "<i>n: </i>$participantsNumber<br>min: $userGradebookTotalActivityMin<br> max: $userGradebookTotalActivityMax<br> <i>M: </i>$mean";
    }else{
        return "-";
    }
    
    
}

draw($tool_content, 2, null, $head_content);




  