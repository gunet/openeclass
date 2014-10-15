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
require_once 'modules/admin/admin.inc.php';

define('COURSE_USERS_PER_PAGE', 15);

//Module name
$nameTools = $langAttendance;

//Datepicker
load_js('tools.js');
load_js('bootstrap-datetimepicker');
load_js('datatables');
load_js('datatables_filtering_delay');

$head_content .= "
<script type='text/javascript'>
$(function() {
    $('#date').datetimepicker({
        format: 'dd-mm-yyyy hh:ii', pickerPosition: 'bottom-left', 
        language: '".$language."',
        autoclose: true
    });
    var oTable = $('#users_table{$course_id}').dataTable ({
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

function showCCFields() {
        $('.assignShow').show();
}
function hideCCFields() {
        $('.assignShow').hide();
}

$(document).ready(function() {
    $('.assignCheck').change(function () {
        if ($('.assignCheck').is(':checked')) {
            showCCFields();
        } else {
            hideCCFields();
        }
    }).change();
    
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
    $newUsersQuery = Database::get()->queryArray("SELECT user.id as userID FROM course_user, user, actions_daily
                               WHERE `user`.id = `course_user`.`user_id`
                               AND (`course_user`.reg_date > ?t)
                               AND `course_user`.`course_id` = ?d
                               AND user.status = ?d 
                               GROUP BY actions_daily.user_id", $limitDate, $course_id, USER_STUDENT);

    if ($newUsersQuery) {
        foreach ($newUsersQuery as $newUsers) {
            Database::get()->querySingle("INSERT INTO attendance_users (attendance_id, uid) VALUES (?d, ?d)", $attendance_id, $newUsers->userID);
        }
    }
    $participantsNumber = Database::get()->querySingle("SELECT COUNT(id) as count FROM attendance_users WHERE attendance_id=?d ", $attendance_id)->count;
}

//===================
//tutor view
//===================
if ($is_editor) {  

    // TOP MENU
    $tool_content .= "<div id='operations_container'>" .
            action_bar(array(
                array('title' => $langAttendanceManagement,
                    'url' => "$_SERVER[SCRIPT_NAME]?course=$course_code",
                    'icon' => 'fa-check-square-o',
                    'level' => 'primary',
                    'show' => isset($_GET['editUsers']) || isset($_GET['addActivity']) || isset($_GET['attendanceBook']) || isset($_GET['modify']) || isset($_GET['book']) || isset($_GET['statsAttendance'])),
                array('title' => $langAdminUsers,
                    'url' => "$_SERVER[SCRIPT_NAME]?course=$course_code&amp;editUsers=1",
                    'icon' => 'fa-users',
                    'level' => 'primary',
                    'show' => !isset($_GET['editUsers'])),
                array('title' => $langAttendanceBook,
                    'url' => "$_SERVER[SCRIPT_NAME]?course=$course_code&amp;attendanceBook=1",
                    'icon' => 'fa-pencil',
                    'level' => 'primary',
                    'show' => !isset($_GET['attendanceBook'])),
                array('title' => $langAttendanceAddActivity,
                    'url' => "$_SERVER[SCRIPT_NAME]?course=$course_code&amp;addActivity=1",
                    'icon' => 'fa-plus-circle',
                    'level' => 'primary',
                    'show' => !isset($_GET['addActivity'])),
                array('title' => $langStat,
                    'url' => "$_SERVER[SCRIPT_NAME]?course=$course_code&amp;statsAttendance=1",
                    'icon' => 'fa-area-chart',
                    'level' => 'primary',
                    'show' => !isset($_GET['statsAttendance'])),
            )) .
            "</div>";

    //FLAG: flag to show the activities
    $showAttendanceActivities = 1;       
    
    //EDIT DB: edit users only semester
    /*
    if(isset($_POST['submitAttendanceActiveUsers'])) {
        $attendance_users_limit = intval($_POST['usersLimit']);
        if($attendance_users_limit ==1 || $attendance_users_limit == 0){
            Database::get()->querySingle("UPDATE attendance SET `students_semester` = ?d WHERE id = ?d ", $attendance_users_limit, $attendance_id);
            $message = "<p class='success'>$langAttendanceEdit</p>";
            $tool_content .= $message . "<br/>";
        }
        //update value for the check box and the users query
        $showSemesterParticipants = $attendance_users_limit;
    }
    
    //number of students for this attendance book (depends on the limit of the last semester selection)
    if ($showSemesterParticipants) {
        //six months limit
        $limitDate = date('Y-m-d', strtotime(' -6 month'));
        $participantsNumber = Database::get()->querySingle("SELECT COUNT(DISTINCT user_id) as count FROM actions_daily, user WHERE actions_daily.user_id = user.id AND user.status = ?d AND course_id = ?d AND actions_daily.day > ?t ", USER_STUDENT, $course_id, $limitDate)->count;
    } else {
        $limitDate = "0000-00-00";
        $participantsNumber = Database::get()->querySingle("SELECT COUNT(user.id) as count FROM course_user, user WHERE course_user.course_id = ?d AND course_user.user_id = user.id AND user.status = ?d ", $course_id, USER_STUDENT)->count;
    }
    */
    
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
            $actDate_obj = DateTime::createFromFormat('Y-m-d H:i:s',$mofifyActivity->date);
            $date = $actDate_obj->format('d-m-Y H:i');            
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
              <td><input type='text' name='date' id='date' value='$date'></td>
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
            </table>
            <input type='hidden' name='id' value='$attendanceActivityToModify' />
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

                Database::get()->query("INSERT INTO attendance_activities SET attendance_id = ?d, title = ?s, `date` = ?t, description = ?s, module_auto_id = ?d, auto = ?d, module_auto_type = ?d", $attendance_id, $actTitle, $actDate, $actDesc, $module_auto_id, $module_auto, $module_auto_type);
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

                Database::get()->query("INSERT INTO attendance_activities SET attendance_id = ?d, title = ?s, `date` = ?t, description = ?s, module_auto_id = ?d, auto = ?d, module_auto_type = ?d", $attendance_id, $actTitle, $actDate, $actDesc, $module_auto_id, $module_auto, $module_auto_type);
            }
        }
        $showAttendanceActivities = 1;
    }

    //EDIT DB: add or edit activity to attendance module (edit concerns and course automatic activities)
    elseif(isset($_POST['submitAttendanceActivity'])){

        if (ctype_alnum($_POST['actTitle'])) {
            $actTitle = $_POST['actTitle'];
        } else {
            $actTitle = "";
        }
        $actDesc = purify($_POST['actDesc']);
        $actDate_obj = DateTime::createFromFormat('d-m-Y H:i',$_POST['date']);
        $actDate = $actDate_obj->format('Y-m-d H:i:s');        
        if (isset($_POST['auto'])) {
            $auto = intval($_POST['auto']);
        } else {
            $auto = ' ';
        }
        
        
        if ($_POST['id']) {
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
                $message = "<div class='alert alert-success'>$langAttendanceDel</div>";
            }else{
                $message = "<div class='alert alert-warning'>$langAttendanceDelFailure</div>";
            }
            $tool_content .= $message . "<br/>";
        }

    //DISPLAY: general stats for the attendance    
    elseif(isset($_GET['statsAttendance'])){
        $result = Database::get()->queryArray("SELECT * FROM attendance_activities  WHERE attendance_id = ?d  ORDER BY `DATE` DESC", $attendance_id);
        $announcementNumber = count($result);

        if ($announcementNumber > 0) {
            $tool_content .= "<fieldset><legend>$langStat - $langAttendanceActList</legend>";
            $tool_content .= "<script type='text/javascript' src='../auth/sorttable.js'></script>
                              <table width='100%' class='sortable' id='t2'>";
            $tool_content .= "<tr><th  colspan='2'>$langTitle</th><th >$langAttendanceActivityDate</th><th>$langDescription</th><th>$langType</th>";
            $tool_content .= "<th width='60' class='center'>$langAttendanceMEANS</th>";
            $tool_content .= "</tr>";
        } else {
            $tool_content .= "<div class='alert alert-warning'>$langAttendanceNoActMessage1 <a href='$_SERVER[SCRIPT_NAME]?course=$course_code&amp;addActivity=1'>$langHere</a> $langAttendanceNoActMessage3</div>\n";
        }
        $k = 0;
        if ($result){
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

                if ($announce->module_auto_id) {
                    $tool_content .= "<td class='smaller'>$langAttendanceActCour";
                    if ($announce->auto) {
                        $tool_content .= "<br>($langAttendanceInsAut)";
                    } else {
                        $tool_content .= "<br>($langAttendanceInsMan)";
                    }
                    $tool_content .= "</td>";
                } else {
                    $tool_content .= "<td class='smaller'>$langAttendanceActAttend</td>";
                }

                $tool_content .= "
                <td width='70' class='center'>" . userAttendTotalActivityStats($announce->id, $participantsNumber). "</td>";
                $k++;
            } // end of while
        }
        $tool_content .= "</table></fieldset>";
        $showAttendanceActivities = 0;
    }    
        
    //DISPLAY: list of users for booking and form for each user
    elseif(isset($_GET['attendanceBook']) || isset($_GET['book'])){
        
        //record booking
        if(isset($_POST['bookUser'])){
            
            $userID = intval($_POST['userID']); //user
            //get all the activies
            $result = Database::get()->queryArray("SELECT * FROM attendance_activities  WHERE attendance_id = ?d", $attendance_id);
            if ($result){                
                foreach ($result as $announce) {
                    $attend = intval(@$_POST[$announce->id]); //get the record from the teacher (input name is the activity id)    
                    //check if there is record for the user for this activity
                    $checkForBook = Database::get()->querySingle("SELECT COUNT(id) as count, id FROM attendance_book  WHERE attendance_activity_id = ?d AND uid = ?d", $announce->id, $userID);
                    
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

        //View acivities for one user - (check for auto mechanism) 
        if(isset($_GET['book'])){

            $limit = isset($_REQUEST['limit']) ? intval($_REQUEST['limit']) : 0;
            
            $userID = intval($_GET['book']); //user
            
            //check if there are booking records for the user, otherwise alert message for first input
            $checkForRecords = Database::get()->querySingle("SELECT COUNT(attendance_book.id) as count FROM attendance_book, attendance_activities WHERE attendance_book.attendance_activity_id = attendance_activities.id AND uid = ?d AND attendance_activities.attendance_id = ?d", $userID, $attendance_id)->count;
            if(!$checkForRecords){
                $tool_content .="<div class='alert1'>$langAttendanceNewBookRecord</div>";
            }
            
            //get all the activities
            $result = Database::get()->queryArray("SELECT * FROM attendance_activities  WHERE attendance_id = ?d  ORDER BY `DATE` DESC", $attendance_id);
            $announcementNumber = count($result);

            if ($announcementNumber > 0) {
                $tool_content .= "<fieldset><legend>" . display_user($userID) . "</legend>";
                $tool_content .= "<script type='text/javascript' src='../auth/sorttable.js'></script>
                                    <form method='post' action='$_SERVER[SCRIPT_NAME]?course=$course_code&book=$userID' onsubmit=\"return checkrequired(this, 'antitle');\">
                                  <table width='100%' class='sortable' id='t2'>";
                $tool_content .= "<tr><th  colspan='2'>" . $m['title'] . "</th>"
                                . "<th >" . $langdate . "</th>"
                                . "<th>$langDescription</th>"
                                . "<th>$langType</th>";
                $tool_content .= "<th width='60' class='center'>" . $langAttendanceBooking . "</th>";
                $tool_content .= "</tr>";
            } else {
                $tool_content .= "<div class='alert alert-warning'>$langAttendanceNoActMessage1 <a href='$_SERVER[SCRIPT_NAME]?course=$course_code&amp;addActivity=1'>$langHere</a> $langAttendanceNoActMessage3</div>\n";
            }
            
            //ui counter 
            $k = 0;

            if ($result){                
                foreach ($result as $activ) {                    
                    //check if there is auto mechanism
                    if($activ->auto == 1){                        
                        //check for assignements (if there is already a record do not propose)
                        $checkForAuto = Database::get()->querySingle("SELECT id FROM attendance_book WHERE attendance_activity_id = ?d AND uid = ?d", $activ->id, $userID);
                        if ($activ->module_auto_type && !$checkForAuto){
                            $userAttend = attendForExersice($userID, $activ->module_auto_id, $activ->module_auto_type);
                        } else {
                            $q = Database::get()->querySingle("SELECT attend FROM attendance_book WHERE attendance_activity_id = ?d AND uid = ?d", $activ->id, $userID);
                            if ($q) {
                                $userAttend = $q->attend;
                            }
                        }
                    } else {               
                        $q = Database::get()->querySingle("SELECT attend FROM attendance_book WHERE attendance_activity_id = ?d AND uid = ?d", $activ->id, $userID);
                        if ($q) {
                                $userAttend = $q->attend;
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
                            . "<td><div class='smaller'>" . nice_format($activ->date) . "</div></td>"
                            . "<td>" . $content . "</td>";

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

                    $tool_content .= "
                <td width='70' class='center'>
                    <input type='checkbox' value='1' name='" . $activ->id . "'";
                    if($userAttend){
                        $tool_content .= " checked";
                    }    
                    $tool_content .= ">
                    <input type='hidden' value='" . $userID . "' name='userID'>    
                </td>";
                    $k++;
                } // end of while
            }
            $tool_content .= "<tr><td colspan=6 class='right'><input type='submit' name='bookUser' value='$langAttendanceBooking' /></td></tr></table></form></fieldset>";
        }
        
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
                        <td> " . display_user($resultUser->userID). " ($langAm: $resultUser->am)</td>
                        <td>" . nice_format($resultUser->reg_date) . "</td>
                        <td>". userAttendTotal($attendance_id, $resultUser->userID). "/" . $attendance_limit . "</td>    
                        <td class='center'>". icon('fa-edit', $langEdit, "$_SERVER[SCRIPT_NAME]?course=$course_code&amp;book=$resultUser->userID"). "</td>
                    </tr>";
            }

            $tool_content .= "
                </tbody>
            </table>";

        }


        /*
        $limit = isset($_REQUEST['limit']) ? $_REQUEST['limit'] : 0;
        
        //Count only students base on their initial record (not the course)
        $countUser = $participantsNumber;
        
        $limit_sql = '';

        // display navigation links if users > COURSE_USERS_PER_PAGE
        if ($countUser > COURSE_USERS_PER_PAGE and !isset($_GET['all'])) {
            $limit_sql = "LIMIT $limit, " . COURSE_USERS_PER_PAGE;
            $search_params = "&course=".$course_code."&attendanceBook=".$attendance_id;
            $tool_content .= show_paging($limit, COURSE_USERS_PER_PAGE, $countUser, $_SERVER['SCRIPT_NAME'], $search_params, TRUE);
        }

        if (isset($_GET['all'])) {
            $extra_link = '&amp;all=true';
        } else {
            $extra_link = '&amp;limit=' . $limit;
        }
        
        $tool_content .= "
        <table width='100%' class='tbl_alt custom_list_order'>
        <tr>
          <th width='1'>$langID</th>
          <th><div align='left'><a href='$_SERVER[SCRIPT_NAME]?course=$course_code&amp;ord=s$extra_link'>$langSurnameName</a></div></th>
          <th class='center' width='90'><a href='$_SERVER[SCRIPT_NAME]?course=$course_code&amp;ord=rd$extra_link'>$langRegistrationDateShort</a></th>
          <th class='center'>$langRole</th>
          <th class='center'>$langAttendanceΑbsences</th>
          <th class='center'>$langActions</th>
        </tr>";


    // Numerating the items in the list to show: starts at 1 and not 0
        $i = $limit + 1;
        $ord = isset($_GET['ord']) ? $_GET['ord'] : '';

        switch ($ord) {
            case 's': $order_sql = 'ORDER BY surname';
                break;
            case 'rd': $order_sql = 'ORDER BY course_user.reg_date DESC';
                break;
            default: $order_sql = 'ORDER BY user.status, editor DESC, tutor DESC, surname, givenname';
                break;
        }
        
        DataBase::get()->queryFunc("SELECT user.id as userID, user.surname , user.givenname, user.email,
                               user.am, user.has_icon, course_user.status as courseUserStatus,
                               course_user.tutor, course_user.editor, course_user.reviewer, course_user.reg_date
                               FROM course_user, user, actions_daily
                               WHERE `user`.id = `course_user`.`user_id`
                               AND `user`.id = actions_daily.user_id
                               AND actions_daily.day > ?t
                               AND `course_user`.`course_id` = ?d
                               AND user.status = ?d 
                               GROUP BY actions_daily.user_id
                               $order_sql $limit_sql", function($myrow) use(&$tool_content, $course_id, &$i, $attendance_limit, $course_code, $userAttendTotal, $attendance_id) {
                                    
                                    global $langEdit, $langAm;
                                    
                                    // bi colored table
                                    if ($i % 2 == 0) {
                                        $tool_content .= "<tr class='odd'>";
                                    } else {
                                        $tool_content .= "<tr class='even'>";
                                    }
                                    // show public list of users
                                    $am_message = empty($myrow->am) ? '' : ("<div class='right'>($langAm: " . q($myrow->am) . ")</div>");
                                    $tool_content .= "
                                        <td class='smaller right'>$i.</td>\n" .
                                            "<td class='smaller'>"
                                            . display_user($myrow->userID) 
                                            . "&nbsp;&nbsp;(" . mailto($myrow->email) . ")  $am_message</td>\n";
                                    $tool_content .= "\n" .
                                            "\n" .
                                            "<td class='smaller center'>";
                                    if ($myrow->reg_date == '0000-00-00') {
                                        $tool_content .= $langUnknownDate;
                                    } else {
                                        $tool_content .= nice_format($myrow->reg_date);
                                    }
                                    $tool_content .= "</td>";
                                    $tool_content .= "<td class='center' width='30'>";

                                    // tutor right
                                    if ($myrow->tutor == '1') {
                                        $tool_content .= "tutor - ";
                                    }
                                    // editor right
                                    if ($myrow->editor == '1') {
                                        $tool_content .= "editor";
                                    }

                                    $tool_content .= "</td>";

                                    $tool_content .= "<td class='center'>". userAttendTotal($attendance_id, $myrow->userID). "/" . $attendance_limit . "</td>";
                                    $tool_content .= "<td class='center'>". icon('fa-edit', $langEdit, "$_SERVER[SCRIPT_NAME]?course=$course_code&amp;book=$myrow->userID"). "</td>";
                                    $i++;
                                }, 
                        $limitDate, $course_id, USER_STUDENT, $order_sql);

        $tool_content .= "</table>";

        // display number of users
        $tool_content .= "<div class='info'><b>$langTotal</b>: <span class='grey'><b>$countUser </b><em>$langStudents &nbsp;</em></span><br />
                        <b>$langDumpUser $langCsv</b>: 1. <a href='dumpuser.php?course=$course_code'>$langcsvenc2</a>
                        2. <a href='dumpuser.php?course=$course_code&amp;enc=1253'>$langcsvenc1</a>
        </div>";
        
        */
        
        
        //do not show activities list
        $showAttendanceActivities = 0;
    }
    
    //EDIT DB: display all the attendance users (reset the list, remove users)
    elseif(isset($_GET['editUsers'])){
        
        //delete users from attendance list
        if (isset($_POST['deleteSelectedUsers'])) {
            foreach ($_POST['recID'] as $value) {
                $value = intval($value);
                //delete users from attendance users table
                Database::get()->query("DELETE FROM attendance_users WHERE id=?d ", $value);
            }
        }
        
        //query to reset users in attedance list
        if (isset($_POST['resetAttendance'])) {
            $usersLimit = intval($_POST['usersLimit']);
            
            if($usersLimit == 1){
                $limitDate = date('Y-m-d', strtotime(' -6 month'));
            }elseif($usersLimit == 2){
                $limitDate = date('Y-m-d', strtotime(' -3 month'));
            }elseif($usersLimit == 3){
                $limitDate = "0000-00-00";
            }
            
            //update the main attendance table
            Database::get()->querySingle("UPDATE attendance SET `students_semester` = ?d WHERE id = ?d ", $usersLimit, $attendance_id);
            //clear attendance users table
            Database::get()->querySingle("DELETE FROM attendance_users WHERE attendance_id = ?d", $attendance_id);
            
            //check the rest value and rearrange the table
            $newUsersQuery = Database::get()->queryArray("SELECT user.id as userID FROM course_user, user, actions_daily
                               WHERE `user`.id = `course_user`.`user_id`
                               AND (`course_user`.reg_date > ?t)
                               AND `course_user`.`course_id` = ?d
                               AND user.status = ?d 
                               GROUP BY user.id", $limitDate, $course_id, USER_STUDENT);
            
            if($newUsersQuery){
                foreach ($newUsersQuery as $newUsers){
                    Database::get()->querySingle("INSERT INTO attendance_users (attendance_id, uid) VALUES (?d, ?d)", $attendance_id, $newUsers->userID);
                }
            }else{
                $tool_content .= "<div class='alert1'>$langNoStudents</div>";
            }
            
        }
        
        
        //section to reset the attendance users list
        $tool_content .= "
        <form method='post' action='$_SERVER[SCRIPT_NAME]?course=$course_code&editUsers=1' onsubmit=\"return checkrequired(this, 'antitle');\">
            <fieldset>
            <h3>$langRefreshList</h3>
            <select name='usersLimit'>
                <option value=''>$langChoice</option>
                <option value='1'>$langAttendanceActiveUsersSemester</option>
                <option value='2'>$langStudLastSemester</option>
                <option value='3'>$langAllRegStudents</option>
            </select>
            <input type='submit' name='resetAttendance' value='$langAttendanceUpdate'>
            </fieldset>
        </form>";
        
        
        //attendance users
        $tool_content .= "<h3>$langAttendanceActiveUsers</h3><br><form method='post' action='$_SERVER[SCRIPT_NAME]?course=$course_code&editUsers=1' onsubmit=\"return checkrequired(this, 'antitle');\">";
        
        $resultUsers = Database::get()->queryArray("SELECT attendance_users.id as recID, attendance_users.uid, user.surname as surname, user.givenname as name, user.am as am, course_user.reg_date as reg_date   FROM attendance_users, user, course_user  WHERE attendance_id = ?d AND attendance_users.uid = user.id AND `user`.id = `course_user`.`user_id` AND `course_user`.`course_id` = ?d ", $attendance_id, $course_id);
        
        if($resultUsers){
            //table to display the users
            $tool_content .= "
            <table width='100%' id='users_table{$course_id}' class='tbl_alt custom_list_order'>
                <thead>
                    <tr>
                      <th width='1'>$langID</th>
                      <th><div align='left' width='100'>$langName $langSurname</div></th>
                      <th class='center' width='80'>$langRegistrationDateShort</th>
                      <th class='center' width='100'>$langSelect</th>
                    </tr>
                </thead>
                <tbody>";
            
            $cnt = 0;
            foreach ($resultUsers as $resultUser) {  
                $cnt++;
                $tool_content .= "
                    <tr>
                        <td>$cnt</td>
                        <td>" . q($resultUser->name) . " " . q($resultUser->surname) . " ($langAm: " . q($resultUser->am) . ")</td>
                        <td>" . nice_format($resultUser->reg_date) . "</td>
                        <td class='center'><input type='checkbox' name='recID[]' value='$resultUser->recID'></td>
                    </tr>";
            }
                    
            $tool_content .= "
                </tbody>
            </table>";

            $tool_content .= "<input type='Submit' name='deleteSelectedUsers' value='$langDelete'>";

            $tool_content .= "</form>";
        }else{
            $tool_content .= "<div class='alert1'>$langNoStudentsInAttendance</div>";
        }
        
        
        //do not show activities list 
        $showAttendanceActivities = 0;
    }
    
    //DISPLAY: list of attendance activities
    if($showAttendanceActivities == 1){
        
        //get all the availiable activities
        $result = Database::get()->queryArray("SELECT * FROM attendance_activities  WHERE attendance_id = ?d  ORDER BY `DATE` DESC", $attendance_id);
        $announcementNumber = count($result);

        if ($announcementNumber > 0) {
            $tool_content .= "<fieldset><legend>$langAttendanceActList</legend>";
            $tool_content .= "<script type='text/javascript' src='../auth/sorttable.js'></script>
                              <table width='100%' class='sortable' id='t2'>";
            $tool_content .= "<tr><th  colspan='2'>$langTitle</th><th >$langAttendanceActivityDate</th><th>$langDescription</th><th>$langType</th>";
            $tool_content .= "<th width='60' class='center'>$langActions</th>";
            $tool_content .= "</tr>";
        }
        else{
            $tool_content .= "<div class='alert alert-warning'>$langAttendanceNoActMessage1 <a href='$_SERVER[SCRIPT_NAME]?course=$course_code&amp;addActivity=1'>$langHere</a> $langAttendanceNoActMessage3</div>\n";
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
                        . "<td><div class='smaller'><span class='day'>" . ucfirst(claro_format_locale_date($dateFormatLong, $d)) . "</span> ($langHour: " . ucfirst(date('H:i', $d)) . ")</div></td>"
                        . "<td>" . $content . "</td>";

                if($announce->module_auto_id) {
                    $tool_content .= "<td class='smaller'>$langAttendanceActCour";
                    if($announce->auto){
                        $tool_content .= "<br>($langAttendanceInsAut)";
                    }else{
                        $tool_content .= "<br>($langAttendanceInsMan)";
                    }
                    $tool_content .= "</td>";
                } else {
                    $tool_content .= "<td class='smaller'>$langAttendanceActAttend</td>";
                }

                $tool_content .= "<td width='70' class='right'>" .
                        action_button(array(
                            array('title' => $langModify,
                                'url' => "$_SERVER[SCRIPT_NAME]?course=$course_code&amp;modify=$announce->id",
                                'icon' => 'fa-edit'),
                            array('title' => $langDelete,
                                'url' => "$_SERVER[SCRIPT_NAME]?course=$course_code&amp;delete=$announce->id",
                                'class' => 'delete',
                                'confirm' => $langSureToDelAnnounce,
                                'icon' => 'fa-times')
                        )) .
                        "</td>";
                $k++;
            } // end of while
        }
        $tool_content .= "</table></fieldset>";

        //Assignments
        //Course activities available for the attendance
        $checkForAss = Database::get()->queryArray("SELECT * FROM assignment WHERE assignment.course_id = ?d AND  assignment.active = 1 AND assignment.id NOT IN (SELECT module_auto_id FROM attendance_activities WHERE module_auto_type = 1)", $course_id);

        $checkForAssNumber = count($checkForAss);
        
        $tool_content .= "<br><br>";
        $tool_content .= "<fieldset><legend>$langAttendanceActToAdd</legend>";
        
        if ($checkForAssNumber > 0) {            
            $tool_content .= "$checkForAssNumber $langWorks - <small>$langDisplay <input type='checkbox' class='assignCheck'></small>";
            
            
            $tool_content .= "<table width='100%' class='sortable assignShow' id='t1'>";
            $tool_content .= "<tr><th>$langWorks</th></tr>";
            
            $tool_content .= "<tr ><th colspan='2'>$langTitle</th><th >$langAttendanceActivityDate2</th><th>$langDescription</th>";
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

                $tool_content .= "<td width='70' class='center'>".icon('fa-plus', $langAdd, "$_SERVER[SCRIPT_NAME]?course=$course_code&amp;addCourseActivity=$newAssToAttendance->id&amp;type=1")."&nbsp;";
                $k++;         
            }
            $tool_content .= "</table>";
        }        
        
        
        //Exercises
        //Course activities available for the attendance
        $checkForExer = Database::get()->queryArray("SELECT * FROM exercise WHERE exercise.course_id = ?d AND  exercise.active = 1 AND exercise.id NOT IN (SELECT module_auto_id FROM attendance_activities WHERE module_auto_type = 2)", $course_id);
        $checkForExerNumber = count($checkForExer);

        $tool_content .= "<br><br>";

        if ($checkForExerNumber > 0) {            
            $tool_content .= "<script type='text/javascript' src='../auth/sorttable.js'></script>
                              <table width='100%' class='sortable' id='t1'>";
            $tool_content .= "<tr><th colspan='2'>$langExercises</th></tr>";
            $tool_content .= "<tr><th  colspan='2'>$langTitle</th><th >$langAttendanceActivityDate2</th><th>$langDescription</th>";
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

                $tool_content .= "<td width='70' class='center'>".icon('fa-plus', $langAdd, "$_SERVER[SCRIPT_NAME]?course=$course_code&amp;addCourseActivity=$newExerToAttendance->id&amp;type=2")."&nbsp;";                     
                $k++;
            } // end of while
            $tool_content .= "</table></fieldset>";
        }        
        

        //=================
        //attendance limit
        //=================
        @$tool_content .= "<br>
            <form method='post' action='$_SERVER[SCRIPT_NAME]?course=$course_code' onsubmit=\"return checkrequired(this, 'antitle');\">
            <fieldset>
            <legend>$langAttendanceLimitTitle</legend>
            <table class='tbl' width='40%'>
                <tr>
                  <th>$langAttendanceLimitNumber:</th><td><input type='text' name='limit' value='$attendance_limit' size='5' /></td>
                </tr>
                <tr>
                  <td class='left'><input type='submit' name='submitAttendanceLimit' value='$langAttendanceUpdate' /></td>
                </tr>
            </table>
            </fieldset>
            </form>";
        
        //=======================
        //show active users limit
        //=======================
        /*
        $tool_content .= "<br>
            <form method='post' action='$_SERVER[SCRIPT_NAME]?course=$course_code' onsubmit=\"return checkrequired(this, 'antitle');\">
            <fieldset>
            <legend>$langAttendanceActiveUsers</legend>
            <table class='tbl' width='40%'>
                <tr>
                  <th>$langAttendanceActiveUsersSemester:</th><td><input type='checkbox' name='usersLimit' value=1";
              if ($showSemesterParticipants) {
                  $tool_content .= " checked";
              }                
               $tool_content .= " /></td>
                </tr>
                <tr>
                  <td class='left'><input type='submit' name='submitAttendanceActiveUsers' value='$langAttendanceUpdate' /></td>
                </tr>
            </table>
            </fieldset>
            </form>";
         */
    }

    
} else { //============Student View==================
    
    $userID = $uid;
    
    //check if there are booking records for the user, otherwise alert message that there is no input
    $checkForRecords = Database::get()->querySingle("SELECT COUNT(attendance_book.id) as count FROM attendance_book, attendance_activities WHERE attendance_book.attendance_activity_id = attendance_activities.id AND uid = ?d AND attendance_activities.attendance_id = ?d", $userID, $attendance_id)->count;
    if (!$checkForRecords) {
        $tool_content .="<div class='alert1'>$langAttendanceStudentFailure</div>";
    }

    $result = Database::get()->queryArray("SELECT * FROM attendance_activities  WHERE attendance_id = ?d  ORDER BY `DATE` DESC", $attendance_id);
    $announcementNumber = count($result);

    if ($announcementNumber > 0) {
        $tool_content .= "<fieldset><legend>$langAttendanceΑbsences</legend>";
        $tool_content .= "<div class='info'>" . userAttendTotal($attendance_id, $userID) ." ". $langAttendanceΑbsencesFrom . " ". $attendance_limit . " " . $langAttendanceΑbsencesFrom2. " </div><br>";
        $tool_content .= "<script type='text/javascript' src='../auth/sorttable.js'></script>
                            <table width='100%' class='sortable' id='t2'>";
        $tool_content .= "<tr><th colspan='2'>$langTitle</th><th>$langAttendanceActivityDate2</th><th>$langDescription</th><th>$langAttendanceΑbsencesYesNo</th></tr>";
    } else {
        $tool_content .= "<div class='alert alert-warning'>$langAttendanceNoActMessage5</div>";
    }
    $k = 0;

    if ($result) {
        foreach ($result as $announce) {            
            //check if the user has attend for this activity
            $userAttend = Database::get()->querySingle("SELECT attend FROM attendance_book  "
                                                     . "WHERE attendance_activity_id = ?d AND uid = ?d", $announce->id, $userID);

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
                    
            if ($userAttend) {
                $tool_content .= icon('fa-check-square-o', $langAttendanceΑbsencesYes); 
            } elseif($announce->date > date("Y-m-d")) {
                $tool_content .= "-";
            } else {
                $tool_content .= icon('fa-times', $langAttendanceΑbsencesΝο);
            }
            $tool_content .= "</td>";            
            $k++;
        } // end of while
    }
    $tool_content .= "</table></fieldset>";
}


//Function to return attend for auto activities
function attendForExersice($userID, $exeID, $exeType){
    if($exeType == 1){ //asignments: valid submission!
       $autoAttend = Database::get()->querySingle("SELECT COUNT(id) as count FROM assignment_submit WHERE uid = ?d AND assignment_id = ?d", $userID, $exeID)->count; 
       if($autoAttend){
           return 1;
       }else{
           return 0;
       }
    }
    if($exeType == 2){ //exercises: valid submission!
       $autoAttend = Database::get()->querySingle("SELECT count(eurid) as count FROM exercise_user_record WHERE uid = ?d AND eid = ?d AND total_score > 0 ", $userID, $exeID)->count;
        if ($autoAttend) {
            return 1;
        }else{
            return 0;
        }
    }
}

//Function to get the total attend number for a user in a course attendance
function userAttendTotal ($attendance_id, $userID){

    $userAttendTotal = Database::get()->querySingle("SELECT SUM(attend) as count FROM attendance_book, attendance_activities WHERE attendance_book.uid = ?d AND  attendance_book.attendance_activity_id = attendance_activities.id AND attendance_activities.attendance_id = ?d", $userID, $attendance_id)->count;

    if($userAttendTotal){
        return $userAttendTotal;
    }else{
        return 0;
    }
}

//Function to get the total attend number for a user in a course attendance
function userAttendTotalActivityStats ($activityID, $participantsNumber){
    
    $sumAtt = "";
    $userAttTotalActivity = Database::get()->queryArray("SELECT attend, attendance_book.uid FROM attendance_book, attendance_users WHERE attendance_activity_id = ?d AND attendance_users.uid=attendance_book.uid", $activityID);
    foreach ($userAttTotalActivity as $module) {
        $sumAtt += $module->attend;
    }

    //check if participantsNumber is zero
    if ($participantsNumber) {
        $mean = round(100 * $sumAtt / $participantsNumber, 2);
        return $sumAtt . " (" . $mean . "%)";
    } else {
        return "-";
    }

    /*
    if($showSemesterParticipants){
        $sumAtt = "";
        $userAttTotalActivity = Database::get()->queryArray("SELECT attend, uid FROM attendance_book WHERE attendance_activity_id = ?d ", $activityID);
        foreach ($userAttTotalActivity as $module) {
            $check = Database::get()->querySingle("SELECT id FROM actions_daily WHERE actions_daily.day > ?t AND actions_daily.`course_id` = ?d AND actions_daily.user_id =?d ", $limitDate, $courseID, $module->uid);
            if ($check) {
                $sumAtt += $module->attend;
            }
        }
    }else{
        $sumAtt = Database::get()->querySingle("SELECT SUM(attend) as count FROM attendance_book WHERE attendance_activity_id = ?d ", $activityID)->count;
    }
    */
          
}

//Display content in template
draw($tool_content, 2, null, $head_content);