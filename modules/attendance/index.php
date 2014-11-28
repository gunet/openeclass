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
$require_current_course = true;
$require_help = true;
$helpTopic = 'Attendance';

require_once '../../include/baseTheme.php';
require_once 'include/lib/textLib.inc.php';

//Datepicker
load_js('tools.js');
load_js('jquery');
load_js('jquery-ui');
load_js('jquery-ui-timepicker-addon.min.js');
load_js('datatables');
load_js('datatables_filtering_delay');

$head_content .= "<link rel='stylesheet' type='text/css' href='{$urlAppend}js/jquery-ui-timepicker-addon.min.css'>
<script type='text/javascript'>
$(function() {
    $('input[name=date]').datetimepicker({
        dateFormat: 'yy-mm-dd', 
        timeFormat: 'hh:mm'
        });
    var oTable = $('#users_table{$course_id}').DataTable ({
        'aLengthMenu': [
                   [10, 15, 20 , -1],
                   [10, 15, 20, '$langAllOfThem'] // change per page values here
               ],'aLengthMenu': [
                   [10, 15, 20 , -1],
                   [10, 15, 20, '$langAllOfThem'] // change per page values here
               ],
               'sPaginationType': 'full_numbers',              
                'bSort': true,
                'oLanguage': {                       
                       'sLengthMenu':   '$langDisplay _MENU_ $langResults2',
                       'sZeroRecords':  '".$langNoResult."',
                       'sInfo':         '$langDisplayed _START_ $langTill _END_ $langFrom2 _TOTAL_ $langTotalResults',
                       'sInfoEmpty':    '$langDisplayed 0 $langTill 0 $langFrom2 0 $langResults2',
                       'sInfoFiltered': '',
                       'sInfoPostFix':  '',
                       'sSearch':       '".$langSearch."',
                       'sUrl':          '',
                       'oPaginate': {
                           'sFirst':    '&laquo;',
                           'sPrevious': '&lsaquo;',
                           'sNext':     '&rsaquo;',
                           'sLast':     '&raquo;'
                       }
                   }
    });
});
</script>";
    
//attendance_id for the course: check if there is an attendance module for the course. If not insert it and create list of users
$attendance = Database::get()->querySingle("SELECT id,`limit`, `students_semester` FROM attendance WHERE course_id = ?d ", $course_id);
if ($attendance) {
    $attendance_id = $attendance->id;
    $attendance_limit = $attendance->limit;
    $showSemesterParticipants = $attendance->students_semester;  
    $participantsNumber = Database::get()->querySingle("SELECT COUNT(id) as count FROM attendance_users WHERE attendance_id=?d ", $attendance_id)->count;
}else{
    //new attendance
    $attendance_id = Database::get()->query("INSERT INTO attendance SET course_id = ?d ", $course_id)->lastInsertID;    
    //create attendance users (default the last six months)
    $limitDate = date('Y-m-d', strtotime(' -6 month'));
    Database::get()->query("INSERT INTO attendance_users (attendance_id, uid) 
                            SELECT $attendance_id, user_id FROM course_user
                            WHERE course_id = ?d AND status = ".USER_STUDENT." AND reg_date > ?s",
                                    $course_id, $limitDate);
    $participantsNumber = Database::get()->querySingle("SELECT COUNT(id) AS count
                                        FROM attendance_users WHERE attendance_id = ?d ", $attendance_id)->count;
}

//===================
//tutor view
//===================
if ($is_editor) {
    
    //delete users from attendance users table        
    if (isset($_GET['deleteuser']) and isset($_GET['ruid'])) {
        Database::get()->query("DELETE FROM attendance_users WHERE uid = ?d AND attendance_id = ?d", $_GET['ruid'], $_GET['ab']);
    }
        
    // action and navigation bar
    $tool_content .= "<div id='operations_container'><ul id='opslist'>";
    if(isset($_GET['editUsers'])){
        $navigation[] = array("url" => "$_SERVER[SCRIPT_NAME]?course=$course_code", "name" => $langAttendance);
        $nameTools = $langConfig;
        $tool_content .= action_bar(array(
            array('title' => $langBack,
                  'url' => "$_SERVER[SCRIPT_NAME]?course=$course_code",
                  'icon' => 'fa fa-reply space-after-icon',
                  'level' => 'primary-label')
            ));
    } elseif (isset($_GET['attendanceBook'])) {
        $navigation[] = array("url" => "$_SERVER[SCRIPT_NAME]?course=$course_code", "name" => $langAttendance);
        $nameTools = $langUsers;
        $tool_content .= action_bar(array(
            array('title' => $langBack,
                  'url' => "$_SERVER[SCRIPT_NAME]?course=$course_code",
                  'icon' => 'fa fa-reply space-after-icon',
                  'level' => 'primary-label')
            ));
    } elseif (isset($_GET['modify'])) {
        $navigation[] = array("url" => "$_SERVER[SCRIPT_NAME]?course=$course_code", "name" => $langAttendance);
        $nameTools = $langModify;
        $tool_content .= action_bar(array(
            array('title' => $langBack,
                  'url' => "$_SERVER[SCRIPT_NAME]?course=$course_code",
                  'icon' => 'fa fa-reply space-after-icon',
                  'level' => 'primary-label')
            ));
    } elseif (isset($_GET['ins'])) {
        $navigation[] = array("url" => "$_SERVER[SCRIPT_NAME]?course=$course_code", "name" => $langAttendance);
        $nameTools = $langAttendanceBook;
        $tool_content .= action_bar(array(
            array('title' => $langBack,
                  'url' => "$_SERVER[SCRIPT_NAME]?course=$course_code",
                  'icon' => 'fa fa-reply space-after-icon',
                  'level' => 'primary-label')
            ));
    } elseif(isset($_GET['addActivity']) or isset($_GET['addActivityAs']) or isset($_GET['addActivityEx'])) {
        $navigation[] = array("url" => "$_SERVER[SCRIPT_NAME]?course=$course_code", "name" => $langAttendance);
        if (isset($_GET['addActivityAs'])) {
            $nameTools = "$langAdd $langInsertWork";
        } elseif (isset($_GET['addActivityEx'])) {
            $nameTools = "$langAdd $langInsertExercise";
        } else {
            $nameTools = $langAttendanceAddActivity;
        }
        $tool_content .= action_bar(array(
            array('title' => $langBack,
                  'url' => "$_SERVER[SCRIPT_NAME]?course=$course_code",
                  'icon' => 'fa fa-reply space-after-icon',
                  'level' => 'primary-label')
            ));
    } elseif (isset($_GET['book'])) {
        $navigation[] = array("url" => "$_SERVER[SCRIPT_NAME]?course=$course_code", "name" => $langAttendance);
        $nameTools = $langAttendanceBook;
        $tool_content .= "<li><a href='$_SERVER[SCRIPT_NAME]?course=$course_code&amp;attendanceBook=1'>$langAttendanceBook</a></li>";
        $tool_content .= action_bar(array(
            array('title' => $langAttendanceBook,
                  'url' => "$_SERVER[SCRIPT_NAME]?course=$course_code&amp;attendanceBook=1",
                  'icon' => 'fa fa-reply space-after-icon',
                  'level' => 'primary-label'),
            array('title' => $langBack,
                  'url' => "$_SERVER[SCRIPT_NAME]?course=$course_code",
                  'icon' => 'fa fa-reply space-after-icon',)
            ));
    } else {
        $nameTools = $langAttendance;
        $tool_content .= action_bar(array(
            array('title' => $langConfig,
                  'url' => "$_SERVER[SCRIPT_NAME]?course=$course_code&amp;editUsers=1",
                  'icon' => 'fa fa-cog space-after-icon',
                  'level' => 'primary-label'),
            array('title' => $langUsers,
                  'url' => "$_SERVER[SCRIPT_NAME]?course=$course_code&amp;attendanceBook=1",
                  'icon' => 'fa fa-plus'),
            array('title' => $langGradebookAddActivity,
                  'url' => "$_SERVER[SCRIPT_NAME]?course=$course_code&amp;addActivity=1",
                  'icon' => 'fa fa-plus'),
            array('title' => "$langAdd $langInsertWork",
                  'url' => "$_SERVER[SCRIPT_NAME]?course=$course_code&amp;addActivityAs=1",
                  'icon' => 'fa fa-plus'),
            array('title' => "$langAdd $langInsertExercise",
                  'url' => "$_SERVER[SCRIPT_NAME]?course=$course_code&amp;addActivityEx=1",
                  'icon' => 'fa fa-plus')));
        
    }       
    
    $tool_content .= "</ul></div>";

    //FLAG: flag to show the activities
    $showAttendanceActivities = 1;       
    
    //DISPLAY: new (or edit) activity form to attendance module
    if(isset($_GET['addActivity']) OR isset($_GET['modify'])){

        $tool_content .= "
            <form method='post' action='$_SERVER[SCRIPT_NAME]?course=$course_code'>
            <fieldset>
            <legend>$langAttendanceActivity</legend>
            <table class='tbl' width='100%'>";
        
        if (isset($_GET['modify'])) { //edit an existed activity
            $id = intval($_GET['modify']);
            
            //all activity data (check if it is in this attendance)
            $mofifyActivity = Database::get()->querySingle("SELECT * FROM attendance_activities WHERE id = ?d AND attendance_id = ?d", $id, $attendance_id);
            $titleToModify = $mofifyActivity->title;
            $contentToModify = $mofifyActivity->description;
            $attendanceActivityToModify = $id;
            $date = $mofifyActivity->date;
            $module_auto_id = $mofifyActivity->module_auto_id;
            $auto = $mofifyActivity->auto;

        } else { //new activity 
            $attendanceActivityToModify = "";
            $titleToModify = '';
            $contentToModify = '';
        }

        $tool_content .= "
            <tr><th>$langTitle:</th></tr>
            <tr>
              <td><input type='text' name='actTitle' value='$titleToModify' size='50' /></td>
            </tr>
            <tr><th>$langAttendanceActivityDate:</th></tr>
            <tr>
              <td><input type='text' name='date' value='" . @datetime_remove_seconds($date) . "'></td>
            </tr>
            <tr><th>$langDescription:</th></tr>
            <tr>
              <td>" . rich_text_editor('actDesc', 4, 20, $contentToModify) . "</td>
            </tr>";
        if (isset($module_auto_id) and $module_auto_id) { //accept the auto booking mechanism            
            $tool_content .= "<tr><td>$langAttendanceAutoBook: <input type='checkbox' value='1' name='auto' ";
            if ($auto) {
                $tool_content .= " checked";
            }
            $tool_content .= " /></td>";
        }    
        $tool_content .= "
                <tr>
                  <td class='right'><input type='submit' name='submitAttendanceActivity' value='$langAdd' /></td>
                </tr>
            </table>";
        if (isset($_GET['modify'])) { 
            $tool_content .= "
                            <input type='hidden' name='id' value='" . $attendanceActivityToModify . "' />";
        }
         $tool_content .= "
            </fieldset>
            </form>";
        
        //do not show the activities list
        $showAttendanceActivities = 0;
    }

    //EDIT DB: add to the attendance module new activity from exersices or assignments
    elseif(isset($_GET['addCourseActivity'])){
        $id = intval($_GET['addCourseActivity']);
        $type = intval($_GET['type']);
        
        //check the type of the module (assignments)
        if($type == 1) {
            //checking if it is new or not
            $checkForAss = Database::get()->querySingle("SELECT * FROM assignment WHERE assignment.course_id = ?d AND  assignment.active = 1 AND assignment.id NOT IN (SELECT module_auto_id FROM attendance_activities WHERE module_auto_type = 1) AND assignment.id = ?d",function ($errormsg) {
                echo "An error has occured: " . $errormsg;
            }, $course_id, $id);
        
            if($checkForAss){
                $module_auto_id = $checkForAss->id;
                $module_auto_type = 1; 
                $module_auto = 1;
                $actTitle = $checkForAss->title;
                $actDate = $checkForAss->deadline;
                $actDesc = $checkForAss->description;

                $sql = Database::get()->query("INSERT INTO attendance_activities SET attendance_id = ?d, title = ?s, `date` = ?t, description = ?s, module_auto_id = ?d, auto = ?d, module_auto_type = ?d", $attendance_id, $actTitle, $actDate, $actDesc, $module_auto_id, $module_auto, $module_auto_type);
                
                //check if there is assignment for any use
                $checkForExerGradeUsers = Database::get()->queryArray("SELECT uid FROM attendance_users WHERE attendance_id = ?d", $attendance_id);
                if($checkForExerGradeUsers){
                    foreach($checkForExerGradeUsers as $checkForExerGradeResultUser){
                        $checkForAsAttend = Database::get()->querySingle("SELECT uid FROM assignment_submit WHERE uid = ?d AND assignment_id = ?d", $checkForExerGradeResultUser->uid, $module_auto_id);
                        if($checkForAsAttend){                            
                            Database::get()->query("INSERT INTO attendance_book SET attendance_activity_id = ?d, uid = ?d, attend = 1", $sql->lastInsertID, $checkForExerGradeResultUser->uid);
                        }
                    }
                }
            }
        }
        //check the type of the module (exercises)
        if($type == 2){
            //checking if it is new or not
            $checkForExer = Database::get()->querySingle("SELECT * FROM exercise WHERE exercise.course_id = ?d "
                    . "AND exercise.active = 1 AND exercise.id NOT IN (SELECT module_auto_id FROM attendance_activities WHERE module_auto_type = 2) "
                    . "AND exercise.id = ?d", $course_id, $id);        
            if($checkForExer){
                $module_auto_id = $checkForExer->id;
                $module_auto_type = 2; 
                $module_auto = 1;
                $actTitle = $checkForExer->title;
                $actDate = $checkForExer->end_date;
                $actDesc = $checkForExer->description;

                $lastInsertID = Database::get()->query("INSERT INTO attendance_activities SET attendance_id = ?d, title = ?s, `date` = ?t, description = ?s, module_auto_id = ?d, auto = ?d, module_auto_type = ?d", $attendance_id, $actTitle, $actDate, $actDesc, $module_auto_id, $module_auto, $module_auto_type)->lastInsertID;
                
                //check if there is exercise for any use
                $checkForExerGradeUsers = Database::get()->queryArray("SELECT uid FROM attendance_users WHERE attendance_id = ?d", $attendance_id);
                if($checkForExerGradeUsers){
                    foreach($checkForExerGradeUsers as $checkForExerGradeResultUser){
                        $checkForExerAttend = Database::get()->querySingle("SELECT uid FROM exercise_user_record WHERE uid = ?d AND attempt_status = " . ATTEMPT_COMPLETED . " AND eid = ?d", $checkForExerGradeResultUser->uid, $module_auto_id);
                        if($checkForExerAttend){
                            Database::get()->query("INSERT INTO attendance_book SET attendance_activity_id = ?d, uid = ?d, attend = 1", $lastInsertID, $checkForExerGradeResultUser->uid);
                        }
                    }
                }
            }
        }
        $showAttendanceActivities = 1;
    }

    //EDIT DB: add or edit activity to attendance module (edit concerns and course automatic activities)
    elseif(isset($_POST['submitAttendanceActivity'])){

        if (strlen($_POST['actTitle'])) {
            $actTitle = $_POST['actTitle'];
        } else {
            $actTitle = "";
        }
        $actDesc = purify($_POST['actDesc']);
        $actDate = $_POST['date'];
        if (isset($_POST['auto'])) {
            $auto = intval($_POST['auto']);
        } else {
            $auto = ' ';
        }
        
        
        if (isset($_POST['id'])) {
            //update
            $id = intval($_POST['id']);
            Database::get()->query("UPDATE attendance_activities SET `title` = ?s, date = ?t, description = ?s, `auto` = ?d WHERE id = ?d", $actTitle, $actDate, $actDesc, $auto, $id);            
            $message = "<p class='success'>$langAttendanceEdit</p>";
            $tool_content .= $message . "<br/>";
        }
        else{
            //insert
            $insertAct = Database::get()->query("INSERT INTO attendance_activities SET attendance_id = ?d, title = ?s, `date` = ?t, description = ?s", $attendance_id, $actTitle, $actDate, $actDesc);            
            $message = "<p class='success'>$langAttendanceSucInsert</p>";
            $tool_content .= $message . "<br/>";
        }
        //show activities list
        $showAttendanceActivities = 1;
    }

    //EDIT DB: add or edit attendance limit
    elseif(isset($_POST['submitAttendanceLimit'])){
        $attendance_limit = intval($_POST['limit']);
        Database::get()->querySingle("UPDATE attendance SET `limit` = ?d WHERE id = ?d ", $attendance_limit, $attendance_id);
        
        $message = "<p class='success'>$langAttendanceLimit</p>";
        $tool_content .= $message . "<br/>";
    }

    //DELETE DB: delete activity form to attendance module
    elseif (isset($_GET['delete'])) {
            $delete = intval($_GET['delete']);
            $delAct = Database::get()->query("DELETE FROM attendance_activities WHERE id = ?d AND attendance_id = ?d", $delete, $attendance_id)->affectedRows;
            $delActBooks = Database::get()->query("DELETE FROM attendance_book WHERE attendance_activity_id = ?d", $delete)->affectedRows;
            $showAttendanceActivities = 1; //show list activities
            
            if($delAct){
                $message = "<p class='success'>$langAttendanceDel</p>";
            }else{
                $message = "<p class='alert1'>$langAttendanceDelFailure</p>";
            }
            $tool_content .= $message . "<br/>";
    }
   
    //DISPLAY: list of users for booking and form for each user
    elseif(isset($_GET['attendanceBook']) || isset($_GET['book'])){
        if (isset($_GET['update']) and $_GET['update']) {
            $tool_content .= "<div class='success'>$langAttendanceUsers</div>";
        }        
        //record booking
        if(isset($_POST['bookUser'])){                        
            $userID = intval($_POST['userID']); //user
            //get all the activies
            $result = Database::get()->queryArray("SELECT * FROM attendance_activities WHERE attendance_id = ?d", $attendance_id);
            if ($result){                
                foreach ($result as $announce) {
                    $attend = intval(@$_POST[$announce->id]); //get the record from the teacher (input name is the activity id)    
                    //check if there is record for the user for this activity
                    $checkForBook = Database::get()->querySingle("SELECT COUNT(id) as count, id FROM attendance_book WHERE attendance_activity_id = ?d AND uid = ?d", $announce->id, $userID);
                    
                    if($checkForBook->count){                        
                        //update
                        Database::get()->query("UPDATE attendance_book SET attend = ?d WHERE id = ?d ", $attend, $checkForBook->id);
                    }else{                        
                        //insert
                        Database::get()->query("INSERT INTO attendance_book SET uid = ?d, attendance_activity_id = ?d, attend = ?d, comments = ?s", $userID, $announce->id, $attend, '');
                    }
                }
                
                $message = "<p class='success'>$langAttendanceEdit</p>";
                $tool_content .= $message . "<br/>";
            }
        }

        //View activities for one user - (check for auto mechanism) 
        if(isset($_GET['book'])) {
            $limit = isset($_REQUEST['limit']) ? intval($_REQUEST['limit']) : 0;            
            $userID = intval($_GET['book']); //user
            //check if there are booking records for the user, otherwise alert message for first input
            $checkForRecords = Database::get()->querySingle("SELECT COUNT(attendance_book.id) as count FROM attendance_book, attendance_activities WHERE attendance_book.attendance_activity_id = attendance_activities.id AND uid = ?d AND attendance_activities.attendance_id = ?d", $userID, $attendance_id)->count;
            if(!$checkForRecords){
                $tool_content .="<div class='info'>$langAttendanceNewBookRecord</div>";
            }
            
            //get all the activities
            $result = Database::get()->queryArray("SELECT * FROM attendance_activities  WHERE attendance_id = ?d  ORDER BY `DATE` DESC", $attendance_id);
            $announcementNumber = count($result);

            if ($announcementNumber > 0) {
                $tool_content .= "<fieldset><legend>" . display_user($userID) . "</legend>";
                $tool_content .= "<script type='text/javascript' src='../auth/sorttable.js'></script>
                                    <form method='post' action='$_SERVER[SCRIPT_NAME]?course=$course_code&book=" . $userID . "'>
                                  <table width='100%' class='sortable' id='t2'>";
                $tool_content .= "<tr><th  colspan='2'>" . $m['title'] . "</th>"
                                . "<th >" . $langdate . "</th>"                                
                                . "<th>$langType</th>";
                $tool_content .= "<th width='60' class='center'>" . $langAttendanceBooking . "</th>";
                $tool_content .= "</tr>";
            } else {
                $tool_content .= "<p class='alert1'>$langAttendanceNoActMessage1 <a href='$_SERVER[SCRIPT_NAME]?course=$course_code&amp;addActivity=1'>$langHere</a> $langAttendanceNoActMessage3</p>\n";
            }
            
            //ui counter 
            $k = 0;
            if ($result){                
                foreach ($result as $activ) {                    
                    //check if there is auto mechanism
                    if($activ->auto == 1){                        
                        //check for auto activities
                        if ($activ->module_auto_type){
                            $userAttend = attendForAutoActivities($userID, $activ->module_auto_id, $activ->module_auto_type);
                            if ($userAttend == 0) {
                                $q = Database::get()->querySingle("SELECT attend FROM attendance_book WHERE attendance_activity_id = ?d AND uid = ?d", $activ->id, $userID);
                                if ($q) {
                                    $userAttend = $q->attend;
                                }
                            }
                        }
                    } else {
                        $q = Database::get()->querySingle("SELECT attend FROM attendance_book WHERE attendance_activity_id = ?d AND uid = ?d", $activ->id, $userID);
                        if ($q) {
                            $userAttend = $q->attend;
                        }else{
                            $userAttend = 0;
                        }
                    }

                    $content = standard_text_escape($activ->description);
                    $activ->date = claro_format_locale_date($dateFormatLong, strtotime($activ->date));

                    if ($k % 2 == 0) {
                        $tool_content .= "<tr class='even'>";
                    } else {
                        $tool_content .= "<tr class='odd'>";
                    }

                    $tool_content .= "<td width='16' valign='top'>
                        <img style='padding-top:3px;' src='$themeimg/arrow.png' title='bullet' /></td>
                        <td><b>";

                    if (empty($activ->title)) {
                        $tool_content .= $langAnnouncementNoTille;
                    } else {
                        $tool_content .= q($activ->title);
                    }
                    $tool_content .= "</b>";
                    $tool_content .= "</td>"
                            . "<td><div class='smaller'>" . nice_format($activ->date) . "</div></td>";

                    if ($activ->module_auto_id) {
                        $tool_content .= "<td class='smaller'>$langAttendanceActCour";
                        if ($activ->auto) {
                            $tool_content .= "<br>($langAttendanceInsAut)";
                        } else {
                            $tool_content .= "<br>($langAttendanceInsMan)";
                        }
                        $tool_content .= "</td>";
                    } else {
                        $tool_content .= "<td class='smaller'>$langAttendanceActAttend</td>";
                    }

                    $tool_content .= "<td width='70' class='center'>
                    <input type='checkbox' value='1' name='" . $activ->id . "'";
                    if(isset($userAttend) && $userAttend) {
                        $tool_content .= " checked";
                    }    
                    $tool_content .= ">
                    <input type='hidden' value='" . $userID . "' name='userID'>    
                    </td>";
                    $k++;
                } // end of while
            }
            $tool_content .= "<tr><td colspan=6 class='right'><input type='submit' name='bookUser' value='$langAttendanceBooking' /></td></tr>";
            $tool_content .= "</table></form></fieldset>";
        } elseif (isset($_GET['attendanceBook'])) {        
            //======================
            //show all the students
            //======================
            $resultUsers = Database::get()->queryArray("SELECT attendance_users.id as recID, attendance_users.uid as userID, user.surname as surname, user.givenname as name, user.am as am, course_user.reg_date as reg_date   FROM attendance_users, user, course_user  WHERE attendance_id = ?d AND attendance_users.uid = user.id AND `user`.id = `course_user`.`user_id` AND `course_user`.`course_id` = ?d ", $attendance_id, $course_id);

            if ($resultUsers) {
                //table to display the users
                $tool_content .= "
                <table width='100%' id='users_table{$course_id}' class='tbl_alt custom_list_order'>
                    <thead>
                        <tr>
                          <th width='1'>$langID</th>
                          <th><div align='left' width='100'>$langName $langSurname</div></th>
                          <th class='center' width='80'>$langRegistrationDateShort</th>
                          <th class='center'>$langAttendanceAbsences</th>
                          <th class='center'>$langActions</th>
                        </tr>
                    </thead>
                    <tbody>";

                $cnt = 0;   
                foreach ($resultUsers as $resultUser) {
                    $cnt++;
                    $tool_content .= "
                        <tr>
                            <td>$cnt</td>
                            <td>" . display_user($resultUser->userID). " ($langAm: $resultUser->am)</td>
                            <td>" . nice_format($resultUser->reg_date) . "</td>
                            <td>". userAttendTotal($attendance_id, $resultUser->userID). "/" . $attendance_limit . "</td>    
                            <td class='center'>"
                                .icon('add', $langAttendanceBook, "$_SERVER[SCRIPT_NAME]?course=$course_code&amp;book=" . $resultUser->userID). "&nbsp;" 
                                .icon('delete', $langAttendanceDelete, "$_SERVER[SCRIPT_NAME]?course=$course_code&amp;ab=$attendance_id&amp;ruid=$resultUser->userID&amp;deleteuser=yes", "onClick=\"return confirmation('$langConfirmDelete');\"")."</td>
                        </tr>";
                }
                $tool_content .= "</tbody></table>";
            }
        }
		
		//do not show activities list
        $showAttendanceActivities = 0;
    }
    
    //EDIT DB: display all the attendance users (reset the list, remove users)
    elseif(isset($_GET['editUsers'])){        
        //query to reset users in attendance list
        if (isset($_POST['resetAttendance'])) {
            $usersLimit = intval($_POST['usersLimit']);            
            if($usersLimit == 1){
                $limitDate = date('Y-m-d', strtotime(' -6 month'));
                $usersMessage = $langAttendance6Months;
            }elseif($usersLimit == 2){
                $limitDate = date('Y-m-d', strtotime(' -3 month'));
                $usersMessage = $langAttendance3Months;
            }elseif($usersLimit == 3){
                $limitDate = "0000-00-00";
                $usersMessage = $langAttendanceAllMonths;
            }
            
            //update the main attendance table
            Database::get()->querySingle("UPDATE attendance SET `students_semester` = ?d WHERE id = ?d ", $usersLimit, $attendance_id);
            //clear attendance users table
            Database::get()->querySingle("DELETE FROM attendance_users WHERE attendance_id = ?d", $attendance_id);            
            // rearrange the table
            $newUsersQuery = Database::get()->query("INSERT INTO attendance_users (attendance_id, uid) 
                            SELECT $attendance_id, user_id FROM course_user
                            WHERE course_id = ?d AND status = ".USER_STUDENT." AND reg_date > ?s",
                                    $course_id, $limitDate);
            if($newUsersQuery) {
                redirect_to_home_page('modules/attendance/index.php?course=' . $course_code . '&attendanceBook=1&update=true');
            } else {
                $tool_content .= "<div class='alert1'>$langNoStudents</div>";
            }            
        }
        
        if($attendance_limit == 1){
            $usersMessage = $langAttendance6Months;
        }elseif($attendance_limit == 2){
            $usersMessage = $langAttendance3Months;
        }elseif($attendance_limit == 3){
            $usersMessage = $langAttendanceAllMonths;
        }
        
        //section to reset the attendance users list
        $tool_content .= "
        <form method='post' action='$_SERVER[SCRIPT_NAME]?course=$course_code&editUsers=1' onsubmit=\"return checkrequired(this, 'antitle');\">
            <fieldset>
            <h3>$langRefreshList</h3><small>($langAttendanceInfoForUsers)</small><br><br>
            <select name='usersLimit'>                
                <option value='1'>$langAttendanceActiveUsers6</option>
                <option value='2'>$langAttendanceActiveUsers3</option>
                <option value='3'>$langAttendanceActiveUsersAll</option>
            </select>
            <input type='submit' name='resetAttendance' value='$langAttendanceUpdate'>
            </fieldset>
        </form>";
                               
        //=================
        //attendance limit
        //=================
        $tool_content .= "<fieldset><legend>$langAttendanceLimitTitle</legend>";
        $tool_content .= "
            <form method='post' action='$_SERVER[SCRIPT_NAME]?course=$course_code' onsubmit=\"return checkrequired(this, 'antitle');\">            
            <table class='tbl' width='50%'>
                <tr>
                  <th>$langAttendanceLimitNumber:</th><td><input type='text' name='limit' value='$attendance_limit' size='5' /></td>
                </tr>
                <tr>
                  <td class='left'><input type='submit' name='submitAttendanceLimit' value='$langAttendanceUpdate' /></td>
                </tr>
            </table>            
            </form>
            </fieldset>";
        //do not show activities list 
        $showAttendanceActivities = 0;
    }
    
    //
    elseif (isset($_GET['addActivityAs'])) {
        //Assignments
        //Course activities available for the attendance
        $checkForAss = Database::get()->queryArray("SELECT * FROM assignment WHERE assignment.course_id = ?d AND  assignment.active = 1 AND assignment.id NOT IN (SELECT module_auto_id FROM attendance_activities WHERE module_auto_type = 1)", $course_id);

        $checkForAssNumber = count($checkForAss);        
        
        if ($checkForAssNumber > 0) {
            $tool_content .= "<table width='100%' class='sortable' id='t1'>";
            $tool_content .= "<tr><th colspan='2'>$langWorks</th></tr>";
            $tool_content .= "<tr><th colspan='2'>$langTitle</th><th >$langAttendanceActivityDate2</th><th>Περιγραφή</th>";
            $tool_content .= "<th width='60' class='center'>$langActions</th>";
            $tool_content .= "</tr>";
            $k = 0;        
            foreach ($checkForAss as $newAssToAttendance) {
                $content = ellipsize_html($newAssToAttendance->description, 50);
                $d = strtotime($newAssToAttendance->deadline);
                if ($k % 2 == 0) {
                    $tool_content .= "<tr class='even'>";
                } else {
                    $tool_content .= "<tr class='odd'>";
                }

                $tool_content .= "<td width='16' valign='top'>
                        <img style='padding-top:3px;' src='$themeimg/arrow.png' title='bullet' /></td>
                        <td><b>";

                if (empty($newAssToAttendance->title)) {
                    $tool_content .= $langAnnouncementNoTille;
                } else {
                    $tool_content .= q($newAssToAttendance->title);
                }
                $tool_content .= "</b>";
                $tool_content .= "</td>"
                        . "<td><div class='smaller'><span class='day'>" . ucfirst(claro_format_locale_date($dateFormatLong, $d)) . "</span> ($langHour: " . ucfirst(date('H:i', $d)) . ")</div></td>"
                        . "<td>" . $content . "</td>";

                $tool_content .= "<td width='70' class='center'>".icon('add', $langAdd, "$_SERVER[SCRIPT_NAME]?course=$course_code&amp;addCourseActivity=" . $newAssToAttendance->id . "&amp;type=1")."&nbsp;";
                $k++;         
            }
            $tool_content .= "</table>";
        } else {
            $tool_content .= "<p class='alert1'>$langAttendanceNoActMessageAss4</p>";
        }
        
        $showAttendanceActivities = 0;
    }
    
    //
    elseif (isset($_GET['addActivityEx'])){
        //Exercises
        //Course activities available for the attendance
        $checkForExer = Database::get()->queryArray("SELECT * FROM exercise WHERE exercise.course_id = ?d AND  exercise.active = 1 AND exercise.id NOT IN (SELECT module_auto_id FROM attendance_activities WHERE module_auto_type = 2)", $course_id);
        $checkForExerNumber = count($checkForExer);
        
        if ($checkForExerNumber > 0) {            
            $tool_content .= "<script type='text/javascript' src='../auth/sorttable.js'></script>
                              <table width='100%' class='sortable' id='t1'>";
            $tool_content .= "<tr><th colspan='2'>$langExercises</th></tr>";
            $tool_content .= "<tr><th  colspan='2'>$langTitle</th><th >$langAttendanceActivityDate2</th><th>Περιγραφή</th>";
            $tool_content .= "<th width='60' class='center'>$langActions</th>";
            $tool_content .= "</tr>";
            $k = 0;        
            foreach ($checkForExer as $newExerToAttendance) {
                $content = ellipsize_html($newExerToAttendance->description, 50);
                $d = strtotime($newExerToAttendance->end_date);

                if ($k % 2 == 0) {
                    $tool_content .= "<tr class='even'>";
                } else {
                    $tool_content .= "<tr class='odd'>";
                }

                $tool_content .= "<td width='16' valign='top'>
                        <img style='padding-top:3px;' src='$themeimg/arrow.png' title='bullet' /></td>
                        <td><b>";

                if (empty($newExerToAttendance->title)) {
                    $tool_content .= $langAnnouncementNoTille;
                } else {
                    $tool_content .= q($newExerToAttendance->title);
                }
                $tool_content .= "</b>";
                $tool_content .= "</td>"
                        . "<td><div class='smaller'><span class='day'>" . ucfirst(claro_format_locale_date($dateFormatLong, $d)) . "</span> ($langHour: " . ucfirst(date('H:i', $d)) . ")</div></td>"
                        . "<td>" . $content . "</td>";

                $tool_content .= "<td width='70' class='center'>".icon('add', $langAdd, "$_SERVER[SCRIPT_NAME]?course=$course_code&amp;addCourseActivity=" . $newExerToAttendance->id . "&amp;type=2")."&nbsp;";                     
                $k++;
            } // end of while
            $tool_content .= "</table></fieldset>";
        } else {
            $tool_content .= "<p class='alert1'>$langAttendanceNoActMessageExe4</p>";
        }    
        $showAttendanceActivities = 0;
    }
    
    //DISPLAY - EDIT DB: insert attendances for each activity
    elseif (isset($_GET['ins'])){

        $actID = intval($_GET['ins']);

        //record booking
        if(isset($_POST['bookUsersToAct'])){                        

            //get all the active users 
            $activeUsers = Database::get()->queryArray("SELECT uid as userID FROM attendance_users WHERE attendance_id = ?d", $attendance_id);

            if ($activeUsers){                
                foreach ($activeUsers as $result) {
                    
                    $userInp = intval(@$_POST[$result->userID]); //get the record from the teacher (input name is the user id)    
                    
                    // //check if there is record for the user for this activity
                    $checkForBook = Database::get()->querySingle("SELECT COUNT(id) as count, id FROM attendance_book WHERE attendance_activity_id = ?d AND uid = ?d", $actID, $result->userID);
                    
                    if($checkForBook->count){                        
                        //update
                        Database::get()->query("UPDATE attendance_book SET attend = ?d WHERE id = ?d ", $userInp, $checkForBook->id);
                    }else{                        
                        //insert
                        Database::get()->query("INSERT INTO attendance_book SET uid = ?d, attendance_activity_id = ?d, attend = ?d, comments = ?s", $result->userID, $actID, $userInp, '');
                    }
                }
                
                $message = "<p class='success'>$langAttendanceEdit</p>";
                $tool_content .= $message . "<br/>";
            }
        }

        //display the form and the list
        
        $result = Database::get()->querySingle("SELECT * FROM attendance_activities  WHERE id = ?d", $actID);
        
        $tool_content .= "<h3>" . $langAttendanceBook . ": " . $result->title . "</h3><br>";

        //show all the students
        $resultUsers = Database::get()->queryArray("SELECT attendance_users.id as recID, attendance_users.uid as userID, user.surname as surname, user.givenname as name, user.am as am, course_user.reg_date as reg_date   FROM attendance_users, user, course_user  WHERE attendance_id = ?d AND attendance_users.uid = user.id AND `user`.id = `course_user`.`user_id` AND `course_user`.`course_id` = ?d ", $attendance_id, $course_id);

        if ($resultUsers) {
            //table to display the users
            $tool_content .= "
            <form method='post' action='$_SERVER[SCRIPT_NAME]?course=$course_code&ins=" . $actID . "'>
            <table width='100%' id='users_table{$course_id}' class='tbl_alt custom_list_order'>
                <thead>
                    <tr>
                      <th width='1'>$langID</th>
                      <th><div align='left' width='100'>$langName $langSurname</div></th>
                      <th class='center' width='80'>$langRegistrationDateShort</th>
                      <th class='center'>$langAttendanceAbsences</th>
                      <th class='center'>$langActions</th>
                    </tr>
                </thead>
                <tbody>";

            $cnt = 0;   
            foreach ($resultUsers as $resultUser) {
                $cnt++;
                $tool_content .= "
                    <tr>
                        <td>$cnt</td>
                        <td> " . display_user($resultUser->userID). " ($langAm: $resultUser->am)</td>
                        <td>" . nice_format($resultUser->reg_date) . "</td>
                        <td>". userAttendTotal($attendance_id, $resultUser->userID). "/" . $attendance_limit . "</td>
                        <td width='70' class='center'>
                            <input type='checkbox' value='1' name='" . $resultUser->userID . "'";
                            //check if the user has attendace for this activity already OR if it should be automatically inserted here

                            $q = Database::get()->querySingle("SELECT attend FROM attendance_book WHERE attendance_activity_id = ?d AND uid = ?d", $actID, $resultUser->userID);
                            if(isset($q->attend) && $q->attend == 1) {
                                $tool_content .= " checked";
                            }    
                        $tool_content .= ">
                            <input type='hidden' value='" . $actID . "' name='actID'>
                        </td>";   
                        $tool_content .= "
                    </tr>";
            }
            $tool_content .= "</tbody></table> <input type='submit' name='bookUsersToAct' value='$langAttendanceBooking' /></form>";
        }
        $showAttendanceActivities = 0;
    }
    



    //DISPLAY: list of attendance activities
    if($showAttendanceActivities == 1){
        //get all the available activities
        $result = Database::get()->queryArray("SELECT * FROM attendance_activities  WHERE attendance_id = ?d  ORDER BY `DATE` DESC", $attendance_id);
        $announcementNumber = count($result);
        if ($announcementNumber > 0) {
            $tool_content .= "<fieldset><legend>$langAttendanceActList</legend>";
            $tool_content .= "<script type='text/javascript' src='../auth/sorttable.js'></script>
                              <table width='100%' class='sortable' id='t2'>";
            $tool_content .= "<tr><th  colspan='2'>$langTitle</th><th >$langAttendanceActivityDate</th><th>$langType</th>";
            $tool_content .= "<th width='80'>$langAttendanceAbsences</th>";
            $tool_content .= "<th width='60' class='center'>$langActions</th>";
            $tool_content .= "</tr>";
        } else {
            $tool_content .= "<p class='alert1'>$langAttendanceNoActMessage1 <a href='$_SERVER[SCRIPT_NAME]?course=$course_code&amp;addActivity=1'>$langHere</a> $langAttendanceNoActMessage3</p>\n";
        }
        $k = 0;
        if ($result){
            foreach ($result as $announce) {               
                $content = ellipsize_html($announce->description, 50);
                
                $d = strtotime($announce->date);
                
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
                        . "<td><div class='smaller'><span class='day'>" . ucfirst(claro_format_locale_date($dateFormatLong, $d)) . "</span> ($langHour: " . ucfirst(date('H:i', $d)) . ")</div></td>";
                $tool_content .= "<td class='smaller'>";
                if($announce->module_auto_id) {
                	if($announce->module_auto_id == 1) {
                		$tool_content .= $langExercise;
                	}elseif($announce->module_auto_id == 2) {
                		$tool_content .= $langAssignment;
                	}
                    if($announce->auto){
                        $tool_content .= "<br>($langAttendanceInsAut)";
                    } else {
                        $tool_content .= "<br>($langAttendanceInsMan)";
                    }                 
                } else {
                    $tool_content .= $langAttendanceActivity;
                }
                $tool_content .= "</td>";
                $tool_content .= "<td width='70' class='center'>" . userAttendTotalActivityStats($announce->id, $participantsNumber) . "</td>";
                $tool_content .= "<td width='70' class='right'>".
                        icon('add', $langAttendanceBook, "$_SERVER[SCRIPT_NAME]?course=$course_code&amp;ins=$announce->id")."&nbsp;"
                       .icon('edit', $langModify, "$_SERVER[SCRIPT_NAME]?course=$course_code&amp;modify=$announce->id")."&nbsp;"
                       .icon('delete', $langDelete, "$_SERVER[SCRIPT_NAME]?course=$course_code&amp;delete=$announce->id", "onClick=\"return confirmation('$langConfirmDelete');\"")
                      ."</td>";
                $k++;
            } // end of while
        }
        $tool_content .= "</table></fieldset>";

        
    }    
} else { //============Student View==================    
    $nameTools = $langAttendance;
    $userID = $uid;        
    $result = Database::get()->queryArray("SELECT * FROM attendance_activities  WHERE attendance_id = ?d  ORDER BY `DATE` DESC", $attendance_id);
    $announcementNumber = count($result);

    if ($announcementNumber > 0) {
        $tool_content .= "<fieldset><legend>$langAttendanceAbsences</legend>";
        $tool_content .= "<div class='info'>" . userAttendTotal($attendance_id, $userID) ." ". $langAttendanceAbsencesFrom . " ". q($attendance_limit) . " " . $langAttendanceAbsencesFrom2. " </div><br>";
        $tool_content .= "<script type='text/javascript' src='../auth/sorttable.js'></script>
                            <table width='100%' class='sortable' id='t2'>";
        $tool_content .= "<tr><th colspan='2'>$langTitle</th><th>$langAttendanceActivityDate2</th><th>$langDescription</th><th>$langAttendanceAbsencesYes</th></tr>";
    } else {
        $tool_content .= "<p class='alert1'>$langAttendanceNoActMessage5</p>";
    }    
    $k = 0;
    if ($result) {        
        foreach ($result as $announce) {
            $content = standard_text_escape($announce->description);
            $d = strtotime($announce->date);
            
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
                    . "<td><div class='smaller'><span class='day'>" . ucfirst(claro_format_locale_date($dateFormatLong, $d)) . "</span> ($langHour: " . ucfirst(date('H:i', $d)) . ")</div></td>"
                    . "<td>" . $content . "</td>";

            $tool_content .= "<td width='70' class='center'>";
            //check if the user has attend for this activity
            $userAttend = Database::get()->querySingle("SELECT attend FROM attendance_book
                                                        WHERE attendance_activity_id = ?d AND uid = ?d", $announce->id, $userID);
            if ($userAttend) {
                $attend = $userAttend->attend;            
                if ($attend) {
                    $tool_content .= icon('tick', $langAttendanceAbsencesYes);
                } else {
                    $auto_activity = Database::get()->querySingle("SELECT auto FROM attendance_activities WHERE id = ?d", $announce->id)->auto;
                    if (!$auto_activity and ($announce->date > date("Y-m-d"))) {
                        $tool_content .= icon('error', $langAttendanceStudentFailure);
                    } else {
                        $tool_content .= icon('delete', $langAttendanceAbsencesNo);
                    }
                }
            } else {
                $tool_content .= icon('error', $langAttendanceStudentFailure);
            }
            $tool_content .= "</td>";
            $k++;
        } // end of while
    }
    $tool_content .= "</table></fieldset>";
}


/**
 * @brief check for attend in auto activities
 * @param type $userID
 * @param type $exeID
 * @param type $exeType
 * @return int
 */
function attendForAutoActivities($userID, $exeID, $exeType){
    if($exeType == 1){ //asignments: valid submission!
       $autoAttend = Database::get()->querySingle("SELECT COUNT(id) AS count FROM assignment_submit
                                    WHERE uid = ?d AND assignment_id = ?d", $userID, $exeID)->count; 
       if ($autoAttend) {
           return 1;
       } else {
           return 0;
       }
    }
    if($exeType == 2){ //exercises: valid submission!       
       $autoAttend = Database::get()->querySingle("SELECT COUNT(eurid) AS count FROM exercise_user_record
                                            WHERE uid = ?d AND eid = ?d 
                                            AND total_score > 0 AND attempt_status != ".ATTEMPT_PAUSED."", $userID, $exeID)->count;
        if ($autoAttend) {
            return 1;
        }else{
            return 0;
        }
    }
}

/**
 * @brief Function to get the total attend number for a user in a course attendance
 * @param type $attendance_id
 * @param type $userID
 * @return int
 */
function userAttendTotal ($attendance_id, $userID){

    $userAttendTotal = Database::get()->querySingle("SELECT SUM(attend) as count FROM attendance_book, attendance_activities
                                            WHERE attendance_book.uid = ?d 
                                            AND attendance_book.attendance_activity_id = attendance_activities.id 
                                            AND attendance_activities.attendance_id = ?d", $userID, $attendance_id)->count;

    if($userAttendTotal){
        return $userAttendTotal;
    } else {
        return 0;
    }
}

/**
 * @brief Function to get the total attend number for a user in a course attendance
 * @param type $activityID
 * @param type $participantsNumber
 * @return string
 */
function userAttendTotalActivityStats ($activityID, $participantsNumber){
        
    $sumAtt = 0;
    $userAttTotalActivity = Database::get()->queryArray("SELECT attend, attendance_book.uid FROM attendance_book, attendance_users WHERE attendance_activity_id = ?d AND attendance_users.uid=attendance_book.uid", $activityID);
    foreach ($userAttTotalActivity as $module) {
        $sumAtt += $module->attend;
    }

    //check if participantsNumber is zero
    if ($participantsNumber) {
        $mean = round(100 * $sumAtt / $participantsNumber, 2);
        return $sumAtt."/". $participantsNumber . " (" . $mean . "%)";
    } else {
        return "-";
    }
          
}

//Display content in template
draw($tool_content, 2, null, $head_content);
