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
 * @brief display attendance users
 * @global type $tool_content
 * @global type $course_id
 * @global type $course_code
 * @global type $actID
 * @global type $langName
 * @global type $langSurname
 * @global type $langRegistrationDateShort
 * @global type $langAttendanceAbsences
 * @global type $langAm
 * @global type $langAttendanceEdit
 * @global type $langAttendanceBooking
 * @global type $langID
 * @param type $attendance_id
 */
function display_attendance_users($attendance_id) {

    global $tool_content, $course_id, $course_code, $actID,
           $langName, $langSurname, $langRegistrationDateShort, $langAttendanceAbsences,
           $langAm, $langAttendanceBooking, $langID, $langAttendanceEdit;
    
    //record booking
    if(isset($_POST['bookUsersToAct'])) {

        //get all the active users 
        $activeUsers = Database::get()->queryArray("SELECT uid as userID FROM attendance_users WHERE attendance_id = ?d", $attendance_id);

        if ($activeUsers){                
            foreach ($activeUsers as $result) {
                $userInp = intval(@$_POST[$result->userID]); //get the record from the teacher (input name is the user id)    
                // //check if there is record for the user for this activity
                $checkForBook = Database::get()->querySingle("SELECT COUNT(id) as count, id FROM attendance_book 
                                                        WHERE attendance_activity_id = ?d AND uid = ?d", $actID, $result->userID);
                if($checkForBook->count) {
                    //update
                    Database::get()->query("UPDATE attendance_book SET attend = ?d WHERE id = ?d ", $userInp, $checkForBook->id);
                } else {
                    //insert
                    Database::get()->query("INSERT INTO attendance_book SET uid = ?d, 
                                                    attendance_activity_id = ?d, attend = ?d, comments = ?s", $result->userID, $actID, $userInp, '');
                }
            }
            Session::Messages($langAttendanceEdit,"alert-success");
            redirect_to_home_page("modules/attendance/index.php");
        }
    }
    //display users
    $resultUsers = Database::get()->queryArray("SELECT attendance_users.id AS recID, attendance_users.uid AS userID,
                                                user.surname AS surname, user.givenname AS name, user.am AS am, course_user.reg_date AS reg_date 
                                            FROM attendance_users, user, course_user 
                                                WHERE attendance_id = ?d 
                                                AND attendance_users.uid = user.id 
                                                AND `user`.id = `course_user`.`user_id` 
                                                AND `course_user`.`course_id` = ?d ", $attendance_id, $course_id);

    if ($resultUsers) {
        //table to display the users
        $tool_content .= "
        <form method='post' action='$_SERVER[SCRIPT_NAME]?course=$course_code&ins=" . $actID . "'>
        <table id='users_table{$course_id}' class='table-default custom_list_order'>
            <thead>
                <tr>
                  <th class='text-center' width='10%'>$langID</th>
                  <th class='text-left' width='75%'>$langName $langSurname</th>
                  <th class='text-center' width='5%'>$langRegistrationDateShort</th>
                  <th class='text-center'>$langAttendanceAbsences</th>
                </tr>
            </thead>
            <tbody>";

        $cnt = 0;   
        foreach ($resultUsers as $resultUser) {
            $cnt++;
            
            if (empty($resultUser->am)) {
                $am_text = "($langAm: $resultUser->am)";
            } else {
                $am_text = '';
            }
            $tool_content .= "<tr>
                <td>$cnt</td>
                <td> " . display_user($resultUser->userID). " $am_text </td>
                <td>" . nice_format($resultUser->reg_date, true, true) . "</td>
                <td class='text-center'><input type='checkbox' value='1' name='" . $resultUser->userID . "'";
                //check if the user has attendace for this activity already OR if it should be automatically inserted here

                $q = Database::get()->querySingle("SELECT attend FROM attendance_book WHERE attendance_activity_id = ?d AND uid = ?d", $actID, $resultUser->userID);
                if(isset($q->attend) && $q->attend == 1) {
                    $tool_content .= " checked";
                }    
                $tool_content .= "><input type='hidden' value='" . $actID . "' name='actID'></td>";
                $tool_content .= "</tr>";
        }
        $tool_content .= "</tbody></table> <input type='submit' class='btn btn-primary' name='bookUsersToAct' value='$langAttendanceBooking' /></form>";
    }
}


function display_attendance_activities($attendance_id) {
    
    global $tool_content, $course_code, $participantsNumber,
           $langAttendanceActList, $langTitle, $langType, $langAttendanceActivityDate, $langAttendanceAbsences,
           $langAttendanceNoTitle, $langExercise, $langAssignment,$langAttendanceInsAut, $langAttendanceInsMan,
           $langDelete, $langEditChange, $langConfirmDelete, $langAttendanceNoActMessage1, $langAttendanceActivity,
           $langHere, $langAttendanceNoActMessage3;
    
    //get all the available activities
    $result = Database::get()->queryArray("SELECT * FROM attendance_activities WHERE attendance_id = ?d  ORDER BY `DATE` DESC", $attendance_id);  
    if (count($result) > 0) {
        $tool_content .= "<div class='row'><div class='col-sm-12'><div class='table-responsive'>
                        <table class='table-default'>
                        <tr><th class='text-center' colspan='5'>$langAttendanceActList</th></tr>
                        <tr>                            
                            <th>$langTitle</th>
                            <th>$langAttendanceActivityDate</th>
                            <th>$langType</th>
                            <th>$langAttendanceAbsences</th>
                            <th class='text-center'><i class='fa fa-cogs'></i></th>
                        </tr>";
        foreach ($result as $details) {            
            $content = ellipsize_html($details->description, 50);            
            $tool_content .= "<tr><td>";
             if (empty($details->title)) {
                $tool_content .= "<a href='$_SERVER[SCRIPT_NAME]?course=$course_code&amp;ins=$details->id'>$langAttendanceNoTitle</a>";
            } else {
                $tool_content .= "<a href='$_SERVER[SCRIPT_NAME]?course=$course_code&amp;ins=$details->id'>".q($details->title)."</a>";
            }
            $tool_content .= "</td>
                    <td>" . nice_format($details->date, true, true) . "</td>";
            $tool_content .= "<td class='smaller'>";
            if($details->module_auto_id) {
                if($details->module_auto_id == 1) {
                        $tool_content .= $langExercise;
                }elseif($details->module_auto_id == 2) {
                        $tool_content .= $langAssignment;
                }
                if($details->auto){
                    $tool_content .= "<small class='help-block'>($langAttendanceInsAut)</small>";
                } else {
                    $tool_content .= "<small class='help-block'>($langAttendanceInsMan)</small>";
                }                 
            } else {
                $tool_content .= $langAttendanceActivity;
            }
            $tool_content .= "</td>";
            $tool_content .= "<td>" . userAttendTotalActivityStats($details->id, $participantsNumber, $attendance_id) . "</td>";
            $tool_content .= "<td class='text-center option-btn-cell'>".                        
                    action_button(array(
                                array('title' => $langEditChange,
                                    'icon' => 'fa-edit',
                                    'url' => "$_SERVER[SCRIPT_NAME]?course=$course_code&amp;modify=$details->id"
                                    ),                            
                                array('title' => $langDelete,
                                    'icon' => 'fa-times',
                                    'url' => "$_SERVER[SCRIPT_NAME]?course=$course_code&amp;delete=$details->id",
                                    'confirm' => $langConfirmDelete,
                                    'class' => 'delete'))).
                    "</td></tr>";
        } // end of while
        $tool_content .= "</table></div></div></div>";
    } else {
        $tool_content .= "<div class='alert alert-warning'>$langAttendanceNoActMessage1 <a href='$_SERVER[SCRIPT_NAME]?course=$course_code&amp;addActivity=1'>$langHere</a> $langAttendanceNoActMessage3</div>";
    }
}