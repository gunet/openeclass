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


function display_student_gradebook($gradebook_id) {

    global $tool_content, $uid,
           $langGradebookTotalGradeNoInput, $langGradebookTotalGrade, 
           $langTitle, $langGradebookActivityDate2, $langGradebookActivityDescription,
           $langGradebookActivityWeight, $langGradebookGrade, $langGradebookAlertToChange;
                
    //visible flag
    $visible = 1;
    //check if there are grade records for the user, otherwise alert message that there is no input
    $checkForRecords = Database::get()->querySingle("SELECT COUNT(gradebook_book.id) AS count 
                                            FROM gradebook_book, gradebook_activities 
                                        WHERE gradebook_book.gradebook_activity_id = gradebook_activities.id 
                                            AND gradebook_activities.visible = ?d 
                                            AND uid = ?d 
                                            AND gradebook_activities.gradebook_id = ?d", $visible, $uid, $gradebook_id)->count;
    if (!$checkForRecords) {
        $tool_content .="<div class='alert alert-warning'>$langGradebookTotalGradeNoInput</div>";
    }

    $result = Database::get()->queryArray("SELECT * FROM gradebook_activities
                                WHERE gradebook_activities.visible = ?d AND gradebook_id = ?d  ORDER BY `DATE` DESC", $visible, $gradebook_id);
    $results = count($result);

    if ($results > 0) {
        $tool_content .= "<div class='alert alert-info'>$langGradebookTotalGrade: <b>" . userGradeTotal($gradebook_id, $uid) . "</b> </div>";
        if(weightleft($gradebook_id, 0) != 0) {
            $tool_content .= "<div class='alert alert-warning'>$langGradebookAlertToChange</p>";
        }
        $tool_content .= "<table class='table-default' >";
        $tool_content .= "<tr><th>$langTitle</th>
                              <th>$langGradebookActivityDate2</th>
                              <th>$langGradebookActivityDescription</th>
                              <th>$langGradebookActivityWeight</th>
                              <th>$langGradebookGrade</th>
                          </tr>";
    }
    if ($result) {
        foreach ($result as $details) {            
            $content = standard_text_escape($details->description);            
            $tool_content .= "<tr><td><b>";
            if (!empty($details->title)) {                
                $tool_content .= q($details->title);
            }
            $tool_content .= "</b>";
            $tool_content .= "</td>"
                    . "<td><div class='smaller'>" . nice_format($details->date, true, true) . "</div></td>"
                    . "<td>" . $content . "</td>"
                    . "<td>" . q($details->weight) . "%</td>";
            $tool_content .= "<td width='70' class='text-center'>";
            //check user grade for this activity
            $sql = Database::get()->querySingle("SELECT grade FROM gradebook_book 
                                                            WHERE gradebook_activity_id = ?d 
                                                                AND uid = ?d", $details->id, $uid);
            if ($sql) {
                $tool_content .= $sql->grade;
            } else {
                $tool_content .= "&mdash;";
            }
            $tool_content .= "</td>";
        } // end of while
    }
    $tool_content .= "</table>";
}

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
 * @brief display users of gradebook
 * @global type $tool_content
 * @global type $course_id
 * @global type $course_code
 * @global type $gradebook_range
 * @global type $langID
 * @global type $langName
 * @global type $langSurname
 * @global type $langAm
 * @global type $langRegistrationDateShort
 * @global type $langGradebookGrade
 * @global type $langGradebookGradeAlert
 * @global type $langAttendanceBooking
 * @global type $langGradebookBooking
 * @global type $langGradebookOutRange
 * @global type $langGradebookUpdate
 * @param type $gradebook_id
 * @param type $actID
 */
function display_gradebook_users($gradebook_id, $actID) {
            
    global $tool_content, $course_id, $course_code, $gradebook_range,
            $langID, $langName, $langSurname, $langAm, $langRegistrationDateShort, $langGradebookGrade, $langGradebookUpdate,
            $langGradebookGradeAlert, $langAttendanceBooking, $langGradebookBooking, $langGradebookOutRange;
    
    //display form and list
    $result = Database::get()->querySingle("SELECT * FROM gradebook_activities WHERE id = ?d", $actID);
    $tool_content .= "<div class='alert alert-info'>" . $result->title . "</div>";
    //display users
    $resultUsers = Database::get()->queryArray("SELECT gradebook_users.id as recID, gradebook_users.uid as userID, user.surname as surname,
                                                    user.givenname as name, user.am as am, DATE(course_user.reg_date) AS reg_date 
                                                FROM gradebook_users, user, course_user
                                                WHERE gradebook_id = ?d AND gradebook_users.uid = user.id 
                                                    AND `user`.id = `course_user`.`user_id` 
                                                    AND `course_user`.`course_id` = ?d ", $gradebook_id, $course_id);
    if ($resultUsers) {        
        $tool_content .= "<div class='form-wrapper'>
        <form method='post' action='$_SERVER[SCRIPT_NAME]?course=$course_code&ins=" . $actID . "'>
        <table id='users_table{$course_id}' class='table-default custom_list_order'>
            <thead>
                <tr>
                  <th width='1'>$langID</th>
                  <th><div align='left' width='150'>$langName $langSurname</div></th>
                  <th class='text-center' width='80'>$langRegistrationDateShort</th>
                  <th class='text-center'>$langGradebookGrade</th>
                  <th class='text-center'>$langAttendanceBooking</th>
                </tr>
            </thead>
            <tbody>";

        $cnt = 0;   
        foreach ($resultUsers as $resultUser) {
        $cnt++;
        $am_message = '';
        if (!empty($resultUser->am)) {
            $am_message = "($langAm: $resultUser->am)";
        }
        $tool_content .= "<tr><td>$cnt</td>
            <td>" . display_user($resultUser->userID). " $am_message</td>
            <td>" . nice_format($resultUser->reg_date) . "</td>";                        
            $tool_content .= "<td>";
            if(weightleft($gradebook_id, 0) == 0) {                            
                $tool_content .= userGradeTotal($gradebook_id, $resultUser->userID);
            } elseif (userGradeTotal($gradebook_id, $resultUser->userID) != "-") { //alert message only when grades have been submitted
                $tool_content .= userGradeTotal($gradebook_id, $resultUser->userID) . " (<small>" . $langGradebookGradeAlert . "</small>)";
            }
            if (userGradeTotal($gradebook_id, $resultUser->userID) > $gradebook_range) {                
                $tool_content .= "<span class='help-block'><small>$langGradebookOutRange</small></span>";
            }
            $tool_content .= "<td class='text-center'>
                <input type='text' name='" . $resultUser->userID . "'";
                //check if the user has attendace for this activity already OR if it should be automatically inserted here
                $q = Database::get()->querySingle("SELECT grade FROM gradebook_book 
                                                        WHERE gradebook_activity_id = ?d 
                                                    AND uid = ?d", $actID, $resultUser->userID);
                if(isset($q->grade)) {
                    $tool_content .= " value = '$q->grade'";
                } else{
                    $tool_content .= " value = ''";
                }

            $tool_content .= "><input type='hidden' value='" . $actID . "' name='actID'></td>";
            $tool_content .= "</tr>";                        
        }
        $tool_content .= "</tbody></table>";
        $tool_content .= "<div class='form-group'>";
        $tool_content .= "<div class='col-xs-12'>" . 
                        form_buttons(array(
                            array(
                                'text' => $langGradebookBooking,
                                'name' => 'bookUsersToAct',
                                'value'=> $langGradebookBooking
                                ),
                            array(
                                'text' => $langGradebookUpdate,
                                'name' => 'updateUsersToAct',
                                'value'=> $langGradebookUpdate
                                )
                        ))."</div></div>";        
        $tool_content .= "</form></div>";    
    }
}

/**
 * @brief add available activity in gradebook
 * @global type $course_id
 * @param type $gradebook_id
 * @param type $id
 * @param type $type
 */
function add_gradebook_activity($gradebook_id, $id, $type) {
    
    global $course_id;
    
    if ($type == 1) { //  add  assignments
        //checking if it's new or not
        $checkForAss = Database::get()->querySingle("SELECT * FROM assignment WHERE assignment.course_id = ?d 
                                                        AND assignment.active = 1 AND assignment.id 
                                            NOT IN (SELECT module_auto_id FROM gradebook_activities 
                                                    WHERE module_auto_type = 1 
                                                    AND gradebook_id = ?d) 
                                                    AND assignment.id = ?d", $course_id, $gradebook_id, $id);        
        if ($checkForAss) {
            $module_auto_id = $checkForAss->id;
            $module_auto_type = 1;
            $module_auto = 1; //auto grade enabled by default
            $actTitle = $checkForAss->title;
            $actDate = $checkForAss->deadline;
            $actDesc = $checkForAss->description;
            Database::get()->query("INSERT INTO gradebook_activities 
                                        SET gradebook_id = ?d, title = ?s, `date` = ?t, description = ?s, 
                                        module_auto_id = ?d, auto = ?d, module_auto_type = ?d", 
                                    $gradebook_id, $actTitle, $actDate, $actDesc, $module_auto_id, $module_auto, $module_auto_type);
            $sql = Database::get()->queryArray("SELECT uid FROM gradebook_users WHERE gradebook_id = ?d", $gradebook_id);
                foreach ($sql as $u) {
                    $grd = Database::get()->querySingle("SELECT grade FROM assignment_submit WHERE assignment_id = ?d AND uid = $u->uid", $id);
                    if ($grd) {
                        update_gradebook_book($u->uid, $id, $grd->grade, GRADEBOOK_ACTIVITY_ASSIGNMENT);
                    }
            }
        }
    }

    if ($type == 2) { // add exercises
        //checking if it is new or not
        $checkForExe = Database::get()->querySingle("SELECT * FROM exercise WHERE exercise.course_id = ?d
                                                            AND exercise.active = 1 AND exercise.id 
                                                    NOT IN (SELECT module_auto_id FROM gradebook_activities 
                                                                WHERE module_auto_type = 2 AND gradebook_id = ?d) 
                                                    AND exercise.id = ?d", $course_id, $gradebook_id, $id);        
        if ($checkForExe) {
            $module_auto_id = $checkForExe->id;
            $module_auto_type = 2; //2 for exercises
            $module_auto = 1;
            $actTitle = $checkForExe->title;
            $actDate = $checkForExe->end_date;
            $actDesc = $checkForExe->description;

            Database::get()->query("INSERT INTO gradebook_activities 
                                        SET gradebook_id = ?d, title = ?s, `date` = ?t, description = ?s, 
                                        module_auto_id = ?d, auto = ?d, module_auto_type = ?d", 
                                    $gradebook_id, $actTitle, $actDate, $actDesc, $module_auto_id, $module_auto, $module_auto_type);            
            $sql = Database::get()->queryArray("SELECT uid FROM gradebook_users WHERE gradebook_id = ?d", $gradebook_id);
            foreach ($sql as $u) {
                update_gradebook_book($u->uid, $id, 0, GRADEBOOK_ACTIVITY_EXERCISE);                                	
            }
        }
    }
    
    if ($type == 3) {    // add learning path        
        //checking if it is new or not
        $checkForLp = Database::get()->querySingle("SELECT * FROM lp_learnPath WHERE course_id = ?d 
                                                    AND learnPath_id NOT IN 
                                                (SELECT module_auto_id FROM gradebook_activities 
                                                    WHERE module_auto_type = 3 
                                                    AND gradebook_id = ?d) 
                                                    AND learnPath_id = ?d", $course_id, $gradebook_id, $id);                        
        if ($checkForLp) {
            $module_auto_id = $checkForLp->learnPath_id;
            $module_auto_type = 3; //3 for lp
            $module_auto = 1;
            $actTitle = $checkForLp->name;
            $actDate = date("Y-m-d");
            $actDesc = $langLearningPath . ": " . $checkForLp->name;
            Database::get()->query("INSERT INTO gradebook_activities 
                            SET gradebook_id = ?d, title = ?s, `date` = ?t, description = ?s, 
                                module_auto_id = ?d, auto = ?d, module_auto_type = ?d", 
                            $gradebook_id, $actTitle, $actDate, $actDesc, $module_auto_id, $module_auto, $module_auto_type);
        }
    }
}

/**
 * @brief insert grades for activity
 * @global string $tool_content
 * @global type $gradebook_range
 * @global boolean $error
 * @global type $langGradebookEdit
 * @param type $gradebook_id
 * @param type $actID
 */
function insert_grades($gradebook_id, $actID) {
      
    global $tool_content, $gradebook_range, $error, $langGradebookEdit, $langFormErrors;
    
    //get all the active users 
    $activeUsers = Database::get()->queryArray("SELECT uid as userID FROM gradebook_users WHERE gradebook_id = ?d", $gradebook_id);
    if ($activeUsers) {
        foreach ($activeUsers as $result) {
            $userInp = @$_POST[$result->userID]; //get the record from the teacher (input name is the user id)                        
            $v = new Valitron\Validator($_POST);
            $v->rule('numeric', $result->userID);
            $v->rule('min', $result->userID, 0);
            $v->rule('max', $result->userID, $gradebook_range);
            if ($v->validate()) {
                // //check if there is record for the user for this activity
                $checkForBook = Database::get()->querySingle("SELECT COUNT(id) AS count, id FROM gradebook_book 
                                            WHERE gradebook_activity_id = ?d AND uid = ?d", $actID, $result->userID);                    
                if ($checkForBook->count) { // update
                    Database::get()->query("UPDATE gradebook_book SET grade = ?d WHERE id = ?d ", $userInp, $checkForBook->id);
                } else { // insert
                    Database::get()->query("INSERT INTO gradebook_book SET uid = ?d, gradebook_activity_id = ?d, grade = ?d, comments = ?s", $result->userID, $actID, $userInp, '');
                }
            } else {
                @Session::Messages($langFormErrors)->Errors($v->errors());
                $error = true;
            }
        }
        if (!$error) {
            $message = "<div class='alert alert-success'>$langGradebookEdit</div>";
            $tool_content .= $message . "<br/>";
        }
    }
}

/**
 * @brief update grades from modules for given activity
 * @param type $gradebook_id
 * @param type $actID
 */
function update_grades($gradebook_id, $actID) {
        
    $sql = Database::get()->querySingle("SELECT module_auto_type, module_auto_id
                            FROM gradebook_activities WHERE id = ?d", $actID);
    if ($sql) {
        $activity_type = $sql->module_auto_type;
        $id = $sql->module_auto_id;
    }
    //get all the active users 
    $q = Database::get()->queryArray("SELECT uid FROM gradebook_users WHERE gradebook_id = ?d", $gradebook_id);
    if ($q) {
        foreach ($q as $activeUsers) {
            update_gradebook_book($activeUsers->uid, $id, 0, $activity_type);
        }
    }
}


/**
 * @brief update gradebook about user grade
 * @param type $uid
 * @param type $id
 * @param type $grade
 * @param type $activity
 */
function update_gradebook_book($uid, $id, $grade, $activity)
{
    global $course_id;
   
    $q = Database::get()->querySingle("SELECT id, gradebook_id FROM gradebook_activities WHERE module_auto_type = ?d
                            AND module_auto_id = ?d
                            AND auto = 1", $activity, $id);
    if ($q) {
        $u = Database::get()->querySingle("SELECT id FROM gradebook_users WHERE uid = ?d
                                AND gradebook_id = ?d", $uid, $q->gradebook_id);
        if($u) {            
            if ($activity == GRADEBOOK_ACTIVITY_EXERCISE) { // exercises                
                $sql = Database::get()->querySingle("SELECT MAX(total_score) AS total_score, total_weighting FROM exercise_user_record
                                                        WHERE uid = ?d AND eid = ?d", $uid, $id);
                if ($sql) {
                   $range = Database::get()->querySingle("SELECT `range` FROM gradebook WHERE id = $q->gradebook_id AND course_id = ?d", $course_id)->range;
                   $score = $sql->total_score;
                   $scoreMax = $sql->total_weighting;
                    if($scoreMax) {
                       $grade = round(($range * $score) / $scoreMax, 2);
                    } else {
                       $grade = $score;
                    }
                }
            }
            if ($grade == '') {
                $grade = 0;
            }
            
            $q2 = Database::get()->querySingle("SELECT grade FROM gradebook_book WHERE gradebook_activity_id = $q->id AND uid = ?d", $uid);
            if ($q2) { // update grade if exists
                Database::get()->query("UPDATE gradebook_book SET grade = ?d WHERE gradebook_activity_id = $q->id AND uid = ?d", $grade, $uid);
            } else { // insert grade
                Database::get()->query("INSERT INTO gradebook_book SET gradebook_activity_id = $q->id, uid = ?d, grade = ?d, comments = ''", $uid, $grade);
            }
        }
    }
    return;
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
