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

/**
 * @brief display gradebook list
 * @global type $course_code
 * @global type $urlServer
 * @global type $tool_content
 * @global type $langGradebookGradeAlert
 * @global type $langGradebookNoActMessage1
 * @global type $langTitle
 * @global type $langView
 * @global type $langScore
 * @global type $langGradebookActList
 * @global type $langGradebookActivityDate2
 * @global type $langGradebookWeight
 * @global type $langGradebookNoTitle
 * @global type $langGradebookType
 * @global type $langExercise
 * @global type $langGradebookInsAut
 * @global type $langGradebookInsMan
 * @global type $langAttendanceActivity
 * @global type $langDelete
 * @global type $langConfirmDelete
 * @global type $langEditChange
 * @global type $langYes
 * @global type $langNo
 * @param type $gradebook_id
 */
function display_gradebook($gradebook_id) {
    
    global $course_code, $urlServer, $tool_content;
    global $langGradebookGradeAlert, $langGradebookNoActMessage1, 
           $langTitle, $langView, $langScore, $langGradebookActList,
           $langGradebookActivityDate2, $langGradebookWeight, $langGradebookNoTitle, $langGradebookType, $langExercise, 
           $langGradebookInsAut, $langGradebookInsMan, $langAttendanceActivity, $langDelete, $langConfirmDelete, 
           $langEditChange, $langYes, $langNo, $langPreview,
           $gradebook_exams, $gradebook_labs, $gradebook_oral, $gradebook_progress, $gradebook_other_type;
    
    //check if there is spare weight
        if(weightleft($gradebook_id, 0)) {
            $weightLeftMessage = "<div class='alert alert-warning'>$langGradebookGradeAlert (" . weightleft($gradebook_id, 0) . "%)</div>";
        } else {
            $weightLeftMessage = "";
        }
        //get all the available activities
        $result = Database::get()->queryArray("SELECT * FROM gradebook_activities WHERE gradebook_id = ?d ORDER BY `DATE` DESC", $gradebook_id);
        $activityNumber = count($result);

        if (!$result or $activityNumber == 0) {
            $tool_content .= "<div class='alert alert-warning'>$langGradebookNoActMessage1</a></div>";
        } else {            
            $tool_content .= $weightLeftMessage;
            $tool_content .= "<div class='row'><div class='col-sm-12'><div class='table-responsive'>
                              <table class='table-default'>
                              <tr class='list-header'><th colspan='7' class='text-center'>$langGradebookActList</th></tr>
                              <tr class='list-header'><th>$langTitle</th><th >$langGradebookActivityDate2</th><th>$langGradebookType</th><th>$langGradebookWeight</th>
                              <th class='text-center'>$langView</th>
                              <th class='text-center'>$langScore</th>
                              <th class='text-center'><i class='fa fa-cogs'></i></th>
                              </tr>";
            foreach ($result as $details) {
                $content = ellipsize_html($details->description, 50);                
                $tool_content .= "<tr><td><b>";
                if (empty($details->title)) {
                    $tool_content .= "$langGradebookNoTitle<br>";
                    $tool_content .= "<small class='help-block'>";
                    switch ($details->activity_type) {
                        case 1: $tool_content .= "($gradebook_oral)"; break;
                        case 2: $tool_content .= "($gradebook_labs)"; break;
                        case 3: $tool_content .= "($gradebook_progress)"; break;
                        case 4: $tool_content .= "($gradebook_exams)"; break;
                        case 5: $tool_content .= "($gradebook_other_type)"; break;
                        default : $tool_content .= "";
                    }
                    $tool_content .= "</small";
                } else {                    
                    $tool_content .= "<a href='$_SERVER[SCRIPT_NAME]?course=$course_code&amp;ins=$details->id'>" . q($details->title) . "</a>";                    
                    $tool_content .= "<small class='help-block'>";
                    switch ($details->activity_type) {
                        case 1: $tool_content .= "($gradebook_oral)"; break;
                        case 2: $tool_content .= "($gradebook_labs)"; break;
                        case 3: $tool_content .= "($gradebook_progress)"; break;
                        case 4: $tool_content .= "($gradebook_exams)"; break;
                        case 5: $tool_content .= "($gradebook_other_type)"; break;
                        default : $tool_content .= "";
                    }
                    $tool_content .= "</small>";
                }
                $tool_content .= "</b>";                
                $tool_content .= "</td><td><div class='smaller'>" . nice_format($details->date, true, true) . "</div></td>";

                if ($details->module_auto_id) {
                    if ($details->module_auto_type == 1) {
                        $tool_content .= "<td class='smaller'>$langGradebookAss";
                    }
                    if ($details->module_auto_type == 2) {
                        $tool_content .= "<td class='smaller'>$langExercise ";
                    }
                    if ($details->module_auto_type == 3) {
                        $tool_content .= "<td class='smaller'>$langGradebookActivityAct";
                    }

                    if ($details->auto) {
                        $tool_content .= "<small class='help-block'>($langGradebookInsAut)</small>";
                    } else {
                        $tool_content .= "<small class='help-block'>($langGradebookInsMan)</small>";
                    }
                    $tool_content .= "</td>";
                } else {
                    $tool_content .= "<td class='smaller'>$langAttendanceActivity</td>";
                }

                $tool_content .= "<td class='text-center'>" . $details->weight . "%</td>";
                $tool_content .= "<td width='' class='text-center'>";
                if ($details->visible) {
                    $tool_content .= $langYes;
                } else {
                    $tool_content .= $langNo;
                }
                $tool_content .= "</td>";
                $tool_content .= "<td width='120' class='text-center'>" . userGradebookTotalActivityStats($details->id, $gradebook_id) . "</td>";
                if ($details->module_auto_id and $details->module_auto_type == 2) {
                    $preview_link = "${urlServer}modules/exercise/results.php?course=$course_code&amp;exerciseId=$details->module_auto_id";
                } elseif ($details->module_auto_id and $details->module_auto_type == 1) {
                    $preview_link = "${urlServer}modules/work/index.php?course=$course_code&amp;id=$details->module_auto_id";
                } elseif ($details->module_auto_id and $details->module_auto_type == 3) {
                    $preview_link = "${urlServer}modules/learnPath/detailsAll.php?course=$course_code";
                } else {
                    $preview_link = '';
                }
                $tool_content .= "<td class='option-btn-cell text-center'>".
                    action_button(array(
                                array('title' => $langEditChange,
                                    'icon' => 'fa-edit',
                                    'url' => "$_SERVER[SCRIPT_NAME]?course=$course_code&amp;modify=$details->id"),
                                array('title' => $langPreview,
                                    'icon' => 'fa-plus',
                                    'url' => $preview_link,
                                    'show' => (!empty($preview_link))),
                                array('title' => $langDelete,
                                    'icon' => 'fa-times',
                                    'url' => "$_SERVER[SCRIPT_NAME]?course=$course_code&amp;delete=$details->id",
                                    'confirm' => $langConfirmDelete,
                                    'class' => 'delete')                            
                        )).
                    "</td>";
            } // end of while
            $tool_content .= "</table></div></div></div>";       
        }
}

/**
 * @brief display available exercises for adding them to gradebook
 * @global type $course_id
 * @global type $course_code
 * @global type $tool_content
 * @global type $langGradebookActivityDate2
 * @global type $langDescr
 * @global type $langAdd
 * @global type $langAttendanceNoActMessageExe4
 * @global type $langTitle
 * @param type $gradebook_id
 */
function display_available_exercises($gradebook_id) {
    
    global $course_id, $course_code, $tool_content,
           $langGradebookActivityDate2, $langDescr, $langAdd, $langAttendanceNoActMessageExe4, $langTitle;

    $checkForExer = Database::get()->queryArray("SELECT * FROM exercise WHERE exercise.course_id = ?d 
                                AND exercise.active = 1 AND exercise.id 
                                NOT IN (SELECT module_auto_id FROM gradebook_activities WHERE module_auto_type = 2 AND gradebook_id = ?d)", $course_id, $gradebook_id);
    $checkForExerNumber = count($checkForExer);
    if ($checkForExerNumber > 0) {
        $tool_content .= "<div class='row'><div class='col-sm-12'><div class='table-responsive'>";
        $tool_content .= "<table class='table-default'>";
        $tool_content .= "<tr><th>$langTitle</th><th>$langGradebookActivityDate2</th><th>$langDescr</th>";
        $tool_content .= "<th class='text-center'><i class='fa fa-cogs'></i></th>";
        $tool_content .= "</tr>";

        foreach ($checkForExer as $newExerToGradebook) {            
            $content = ellipsize_html($newExerToGradebook->description, 50);            
            $tool_content .= "<tr><td><b>";
            if (!empty($newExerToGradebook->title)) {              
                $tool_content .= q($newExerToGradebook->title);
            }
            $tool_content .= "</b>";
            $tool_content .= "</td>"
                    . "<td><div class='smaller'><span class='day'>" . nice_format($newExerToGradebook->start_date, true, true) . " </div></td>"
                    . "<td>" . $content . "</td>";
            $tool_content .= "<td width='70' class='text-center'>" . icon('fa-plus', $langAdd, "$_SERVER[SCRIPT_NAME]?course=$course_code&amp;addCourseActivity=" . $newExerToGradebook->id . "&amp;type=2");
        } 
        $tool_content .= "</td></tr></table></div></div></div>";
    } else {
        $tool_content .= "<div class='alert alert-warning'>$langAttendanceNoActMessageExe4</div>";
    }    
}

/**
 * @brief display available assignments for adding them to gradebook
 * @global type $course_id
 * @global type $course_code
 * @global type $tool_content
 * @global type $dateFormatLong
 * @global type $langWorks
 * @global type $m
 * @global type $langDescription
 * @global type $langAttendanceNoActMessageAss4
 * @global type $langAdd
 * @global type $langTitle
 * @global type $langHour
 * @param type $gradebook_id
 */
function display_available_assignments($gradebook_id) {

    global $course_id, $course_code, $tool_content, $dateFormatLong, 
           $langWorks, $m, $langDescription, $langAttendanceNoActMessageAss4, 
           $langAdd, $langTitle, $langHour;
    
    $checkForAss = Database::get()->queryArray("SELECT * FROM assignment WHERE assignment.course_id = ?d AND  assignment.active = 1 AND assignment.id NOT IN (SELECT module_auto_id FROM gradebook_activities WHERE module_auto_type = 1 AND gradebook_id = ?d)", $course_id, $gradebook_id);

    $checkForAssNumber = count($checkForAss);

    if ($checkForAssNumber > 0) {
        $tool_content .= "
            <div class='row'><div class='col-sm-12'><div class='table-responsive'>
            <h4>$langWorks</h4>
                          <table class='table-default'";
        $tool_content .= "<tr><th>$langTitle</th><th>$m[deadline]</th><th>$langDescription</th>";
        $tool_content .= "<th class='text-center'><i class='fa fa-cogs'></i></th>"; 
        $tool_content .= "</tr>";           
        foreach ($checkForAss as $newAssToGradebook) {
            $content = ellipsize_html($newAssToGradebook->description, 50);
            if($newAssToGradebook->assign_to_specific){
                $content .= "($langGradebookAssignSpecific)<br>";
                $checkForAssSpec = Database::get()->queryArray("SELECT user_id, user.surname , user.givenname FROM `assignment_to_specific`, user WHERE user_id = user.id AND assignment_id = ?d", $newAssToGradebook->id);
                foreach ($checkForAssSpec as $checkForAssSpecR) {
                    $content .= q($checkForAssSpecR->surname). " " . q($checkForAssSpecR->givenname) . "<br>";
                }
            }
            if ((int) $newAssToGradebook->deadline){
                $d = strtotime($newAssToGradebook->deadline);
                $date_str = ucfirst(claro_format_locale_date($dateFormatLong, $d));
                $hour_str = "($langHour: " . ucfirst(date('H:i', $d)).")";
            } else {
                $date_str = $m['no_deadline'];
                $hour_str = "";
            }
            $tool_content .= "<tr><td><b>";

            if (!empty($newAssToGradebook->title)) {                   
                $tool_content .= q($newAssToGradebook->title);
            }
            $tool_content .= "</b>";
            $tool_content .= "</td>"
                    . "<td><div class='smaller'><span class='day'>$date_str</span> $hour_str </div></td>"
                    . "<td>" . $content . "</td>";
            $tool_content .= "<td width='70' class='text-center'>".icon('fa-plus', $langAdd, "$_SERVER[SCRIPT_NAME]?course=$course_code&amp;addCourseActivity=" . $newAssToGradebook->id . "&amp;type=1");
        } // end of while        
        $tool_content .= "</tr></table></div></div></div>";
    } else {
        $tool_content .= "<div class='alert alert-warning'>$langAttendanceNoActMessageAss4</div>";
    }
}

/**
 * @brief display available learning paths
 * @global type $course_id
 * @global type $course_code
 * @global type $tool_content
 * @global type $langLearningPath
 * @global type $langAdd
 * @global type $langAttendanceNoActMessageLp4
 * @global type $langTitle
 * @global type $langDescription
 * @global type $langActions
 * @param type $gradebook_id
 */
function display_available_lps($gradebook_id) {
    
    global $course_id, $course_code, $tool_content,
           $langLearningPath, $langAdd, $langAttendanceNoActMessageLp4, $langTitle, $langDescription, $langActions;
    
    $checkForLp = Database::get()->queryArray("SELECT * FROM lp_learnPath WHERE course_id = ?d ORDER BY name 
                        AND learnPath_id NOT IN (SELECT module_auto_id FROM gradebook_activities WHERE module_auto_type = 3 AND gradebook_id = ?d)", $course_id, $gradebook_id);
                
    $checkForLpNumber = count($checkForLp);        
    if ($checkForLpNumber > 0) {
        $tool_content .= "<div class='row'><div class='col-sm-12'><div class='table-responsive'>";
        $tool_content .= "<h4>$langLearningPath</h4>";
        $tool_content .= "<table class='table-default'>";            
        $tool_content .= "<tr class='list-header'><th>$langTitle</th><th>$langDescription</th>";
        $tool_content .= "<th class='text-center'>$langActions</th>";
        $tool_content .= "</tr>";
        foreach ($checkForLp as $newExerToGradebook) {
            $tool_content .= "<tr>";
            $tool_content .= "<td>". q($newExerToGradebook->name) ."</td>";
            $tool_content .= "<td>" .$newExerToGradebook->comment. "</td>";
            $tool_content .= "<td class='text-center'>".icon('fa-plus', $langAdd, "$_SERVER[SCRIPT_NAME]?course=$course_code&amp;addCourseActivity=$newExerToGradebook->learnPath_id&amp;type=3")."</td>";
            $tool_content .= "</tr>";
        } // end of while        
        $tool_content .= "</table></div></div></div>";
    } else {
        $tool_content .= "<div class='alert alert-warning'>$langAttendanceNoActMessageLp4</div>";
    }
}
/**
 * @brief function to help selected option
 * @param type $type
 * @param type $optionType
 * @return string
 */
function typeSelected($type, $optionType){
    if($type == $optionType){
        return "selected";
    }
}


/**
 * @brief calculate the weight left
 * @param type $gradebook_id
 * @param type $currentActivity
 * @return int
 */
function weightleft($gradebook_id, $currentActivity){
    
    if($currentActivity){
        $left = Database::get()->querySingle("SELECT SUM(weight) as count FROM gradebook_activities WHERE gradebook_id = ?d AND id != ?d", $gradebook_id, $currentActivity)->count;         
    } else {
        $left = Database::get()->querySingle("SELECT SUM(weight) as count FROM gradebook_activities WHERE gradebook_id = ?d", $gradebook_id)->count;
    }    
    if($left >= 0 ){
        return 100-$left;
    } else {
        return 0;
    }
    
}

/**
 * @brief return auto grades
 * @param type $userID
 * @param type $exeID
 * @param type $exeType
 * @param type $range
 * @return string
 */
function attendForAutoGrades($userID, $exeID, $exeType, $range) {
    
    if ($exeType == 1) { //asignments: valid submission!
       $autoAttend = Database::get()->querySingle("SELECT grade, max_grade FROM assignment_submit,assignment  WHERE assignment.id = assignment_id AND uid = ?d AND assignment_id = ?d", $userID, $exeID);
       if ($autoAttend) {
           $score = $autoAttend->grade;
           $scoreMax = $autoAttend->max_grade;
           if ($score >= 0) {
                if ($scoreMax) {
                    return round(($range * $score) / $scoreMax, 2);
                } else {
                    return $score;
                }
            } else {
                return "";
            }
       }
    } else if($exeType == 2){ //exercises (if there are more than one attemps we take the last)
       $autoAttend = Database::get()->querySingle("SELECT total_score, total_weighting FROM exercise_user_record WHERE uid = ?d AND eid = ?d ORDER BY `record_end_date` DESC LIMIT 1", $userID, $exeID); 
       if ($autoAttend) {
           $score = $autoAttend->total_score;
           $scoreMax = $autoAttend->total_weighting;
           if($score >= 0) {
                if($scoreMax) {
                    return round(($range * $score) / $scoreMax, 2);
                } else {
                    return $score;
                }
            } else {
                return "";
            }
       }
    } else if($exeType == 3){ //lps (exes and scorms)
       $autoAttend = Database::get()->querySingle("SELECT raw, scoreMax
               FROM lp_user_module_progress, lp_rel_learnPath_module, lp_module 
               WHERE lp_module.module_id = ?d  
               AND lp_user_module_progress.user_id = ?d 
               AND lp_module.module_id = lp_rel_learnPath_module.module_id
               AND lp_rel_learnPath_module.learnPath_module_id = lp_user_module_progress.learnPath_module_id
               AND (lp_user_module_progress.lesson_status = 'FAILED' OR lp_user_module_progress.lesson_status = 'PASSED' OR lp_user_module_progress.lesson_status = 'COMPLETED')
               ", $exeID, $userID);
       if ($autoAttend) {
           $score = $autoAttend->raw;
           $scoreMax = $autoAttend->scoreMax;
           if($score >= 0){ //to avoid the -1 for no score
                if ($scoreMax) {
                    return round(($range * $score) / $scoreMax, 2);
                } else {
                    return $score;
                }
            } else {
                return "";
            }
       }
    }
}


/**
 * @brief get total number of user attend in a course gradebook
 * @param type $gradebook_id
 * @param type $userID
 * @return string
 */
function userGradeTotal ($gradebook_id, $userID, $student_view = 'false') {
    
    if ($student_view) {
        $visible = 1;
    }
    
    $userGradeTotal = Database::get()->querySingle("SELECT SUM(grade * weight) AS count FROM gradebook_book, gradebook_activities
                                                    WHERE gradebook_book.uid = ?d 
                                                        AND gradebook_book.gradebook_activity_id = gradebook_activities.id 
                                                        AND gradebook_activities.gradebook_id = ?d 
                                                        AND gradebook_activities.visible = ?d", $userID, $gradebook_id, $visible)->count;

    if ($userGradeTotal) {
        return round($userGradeTotal/100, 2);
    } else {
        return false;
    }
}

/**
 * @brief function to get the total gradebook number 
 * @global type $langUsers
 * @global type $langMeanValue
 * @global type $langMinValue
 * @global type $langMaxValue
 * @param type $activityID
 * @param type $gradebook_id
 * @return string
 */
function userGradebookTotalActivityStats ($activityID, $gradebook_id) {
    
    global $langUsers, $langMeanValue, $langMinValue, $langMaxValue;
    
    $users = Database::get()->querySingle("SELECT SUM(grade) as count, COUNT(gradebook_users.uid) as users FROM gradebook_book, gradebook_users WHERE  gradebook_users.uid=gradebook_book.uid AND gradebook_activity_id = ?d AND gradebook_users.gradebook_id = ?d ", $activityID, $gradebook_id);
    
    $sumGrade = $users->count;
    //this is different than global participants number (it is limited to those that have taken degree)
    $participantsNumber = $users->users;
    

    $q = Database::get()->querySingle("SELECT grade FROM gradebook_book, gradebook_users WHERE  gradebook_users.uid=gradebook_book.uid AND gradebook_activity_id = ?d AND gradebook_users.gradebook_id = ?d ORDER BY grade ASC limit 1 ", $activityID, $gradebook_id);
    if ($q) {
        $userGradebookTotalActivityMin = $q->grade;
    }
    $q = Database::get()->querySingle("SELECT grade FROM gradebook_book, gradebook_users WHERE  gradebook_users.uid=gradebook_book.uid AND gradebook_activity_id = ?d AND gradebook_users.gradebook_id = ?d ORDER BY grade DESC limit 1 ", $activityID, $gradebook_id);
    if ($q) {
        $userGradebookTotalActivityMax = $q->grade;
    }    
    
//check if participantsNumber is zero
    if ($participantsNumber) {
        $mean = round($sumGrade/$participantsNumber, 2);
        return "<i>$langUsers:</i> $participantsNumber<br>$langMinValue: $userGradebookTotalActivityMin<br> $langMaxValue: $userGradebookTotalActivityMax<br> <i>$langMeanValue:</i> $mean";
    } else {
        return "-";
    }        
}
