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
//$require_course_admin = true;
$require_help = true;
$helpTopic = 'User';//SOS should change

require_once '../../include/baseTheme.php';
require_once 'include/lib/textLib.inc.php';
require_once 'modules/admin/admin.inc.php';

//load_js('tools.js');

require_once 'modules/work/jscalendar.inc.php'; // For using with the pop-up calendar

define('COURSE_USERS_PER_PAGE', 15);

//Module name
$nameTools = $langAttendance;

//attendance_id for the course: check if there is an attendance module for the course. If not insert it
$attendance = Database::get()->querySingle("SELECT id,`limit` FROM attendance WHERE course_id = ?d ", $course_id);
$attendance_id = $attendance->id;
$attendance_limit = $attendance->limit;
if(!$attendance_id){
    $attendance_id = Database::get()->query("INSERT INTO attendance SET course_id = ?d ", $course_id)->lastInsertID;
}


//tutor view
if ($is_editor) {  

    // Admin attendance, booking (list of users), Add new attendance activity
    $tool_content .= "

    <div id='operations_container'>
      <ul id='opslist'>
        <li><a href='$_SERVER[SCRIPT_NAME]?course=$course_code'>$langAttendanceManagement</a></li>
        <li><a href='$_SERVER[SCRIPT_NAME]?course=$course_code&amp;attendanceBook=1'>$langAttendanceBook</a></li>";
    if(!isset($_GET['addActivity'])){
        $tool_content .= "
        <li><a href='$_SERVER[SCRIPT_NAME]?course=$course_code&amp;addActivity=1'>$langAttendanceAddActivity</a></li>";
    }
    $tool_content .= "    
      </ul>
    </div>";

    //FLAG: flag to show the activities
    $showAttendanceActivities = 1;

    //FORM: new activity (or edit) form to attendance module
    if(isset($_GET['addActivity']) OR isset($_GET['modify'])){

        $tool_content .= "
            <form method='post' action='$_SERVER[SCRIPT_NAME]?course=$course_code'>
            <fieldset>
            <legend>$langAttendanceAddActivity</legend>
            <table class='tbl' width='100%'>";
        
        if (isset($_GET['modify'])) { //edit an existed activity
            $langAdd = $nameTools = "Αλλαγή της δραστηριότητας";
            $id = intval($_GET['modify']);
            
            //all activity data (check if it is in this attendance)
            $mofifyActivity = Database::get()->querySingle("SELECT * FROM attendance_activities WHERE id = ?d AND attendance_id = ?d", $id, $attendance_id);
            $titleToModify = $mofifyActivity->title;
            $contentToModify = $mofifyActivity->description;
            $attendanceActivityToModify = $id;
            $date = getJsDeadline($mofifyActivity->date);
            $module_auto_id = $mofifyActivity->module_auto_id;
            $auto = $mofifyActivity->auto;

        } else { //new activity 
            $nameTools = $langAddAnn;
            $attendanceActivityToModify = "";
            $date = $end_cal_Work;
        }

        $tool_content .= "
            <tr><th>$langAttendanceActivityTitle:</th></tr>
            <tr>
              <td><input type='text' name='actTitle' value='$titleToModify' size='50' /></td>
            </tr>
            <tr><th>$langAttendanceActivityDate:</th></tr>
            <tr>
              <td>" . $date . "</td>
            </tr>
            <tr><th>$langAttendanceActivityDescription:</th></tr>
            <tr>
              <td>" . rich_text_editor('actDesc', 4, 20, $contentToModify) . "</td>
            </tr>";
        if($module_auto_id){ //accept the auto booking mechanism
            $nameTools = "Επεξεργασία δραστηριότητας από το σύστημα";
            $tool_content .= "
                <tr>
                  <td>Αυτόματη καταχώρηση παρουσίας: <input type='checkbox' value='1' name='auto' ";
            if($auto){
                $tool_content .= " checked";
            }
            $tool_content .= "
                /></td>";
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

    //ADD: new activity from exersices or assignments
    elseif(isset($_GET['addCourseActivity'])){

        $id = intval($_GET['addCourseActivity']);
        
        //check the type of the modlue 
        if($_GET['type']=1){
            //checking if it is new or not
            $checkForExer = Database::get()->querySingle("SELECT * FROM assignment WHERE assignment.course_id = ?d AND  assignment.active = 1 AND assignment.id NOT IN (SELECT module_auto_id FROM attendance_activities) AND assignment.id = ?d",function ($errormsg) {
                echo "An error has occured: " . $errormsg;
            }, $course_id, $id);
        }
        if($checkForExer){
            $module_auto_id = $checkForExer->id;
            $module_auto_type = 1; 
            if($_GET['type']=1){ //one for assignments
                $module_auto = 1;
            }
            $actTitle = $checkForExer->title;
            $actDate = $checkForExer->deadline;
            $actDesc = $checkForExer->description;

            Database::get()->query("INSERT INTO attendance_activities SET attendance_id = ?d, title = ?s, `date` = ?t, description = ?s, module_auto_id = ?d, auto = ?d, module_auto_type = ?d", $attendance_id, $actTitle, $actDate, $actDesc, $module_auto_id, $module_auto, $module_auto_type);
        }
        

        $showAttendanceActivities = 1;
    }

    //ADD-EDIT: add or edit activity to attendance module (edit concerns and course activities)
    elseif(isset($_POST['submitAttendanceActivity'])){

        $actTitle = $_POST['actTitle'];  
        $actDesc = purify($_POST['actDesc']);
        $actDate = $_POST['WorkEnd'];
        $auto = intval($_POST['auto']);
        
        if ($_POST['id']) {
            //update
            $id = intval($_POST['id']);
            Database::get()->query("UPDATE attendance_activities SET `title` = ?s, date = ?t, description = ?s, `auto` = ?d WHERE id = ?d", $actTitle, $actDate, $actDesc, $auto, $id);
            $langAnnDel = "Επιτυχής αλλαγή";
            $message = "<p class='success'>$langAnnDel</p>";
            $tool_content .= $message . "<br/>";
        }
        else{
            //insert
            $insertAct = Database::get()->query("INSERT INTO attendance_activities SET attendance_id = ?d, title = ?s, `date` = ?t, description = ?s", $attendance_id, $actTitle, $actDate, $actDesc);
            $langAnnDel = "Επιτυχής εισαγωγή δραστηριότητας";
            $message = "<p class='success'>$langAnnDel</p>";
            $tool_content .= $message . "<br/>";
        }
        //show activities list
        $showAttendanceActivities = 1;
    }

    //ADD-EDIT: add or edit attendance limit
    elseif(isset($_POST['submitAttendanceLimit'])){
        $attendance_limit = intval($_POST['limit']);
        Database::get()->querySingle("UPDATE attendance SET `limit` = ?d WHERE id = ?d ", $attendance_limit, $attendance_id);
        $langAnnDel = "Επιτυχής ενημέρωση αριθμού παρουσιών";
        $message = "<p class='success'>$langAnnDel</p>";
        $tool_content .= $message . "<br/>";
    }

    //FORM: delete activity form to attendance module !!!!!SOS sth diagrafh na svhnontai kai oi parouseies twn mathitwn
    elseif (isset($_GET['delete'])) {
            $delete = intval($_GET['delete']);
            $delAct = Database::get()->query("DELETE FROM attendance_activities WHERE id = ?d AND attendance_id = ?d", $delete, $attendance_id)->affectedRows;
            $delActBooks = Database::get()->query("DELETE FROM attendance_book WHERE attendance_activity_id = ?d", $delete)->affectedRows;
            $showAttendanceActivities = 1; //show list activities
            if($delAct && $delActBooks){
                $langAnnDel = "Επιτυχής διαγραφή δραστηριότητας";
                $message = "<p class='success'>$langAnnDel</p>";
            }else{
                $langAnnDel = "Δεν υπάρχει η δραστηριότητα που προσπαθείτε να διαγράψετε";
                $message = "<p class='alert1'>$langAnnDel</p>";
            }
            $tool_content .= $message . "<br/>";
        }

    //FORM-DISPLAY: list of users for booking and form for each user
    elseif(isset($_GET['attendanceBook']) || isset($_GET['book'])){
        
        //record booking
        if(isset($_POST['bookUser'])){
            
            $userID = intval($_POST['userID']); //user
            //get all the activies
            $result = Database::get()->queryArray("SELECT * FROM attendance_activities  WHERE attendance_id = ?d", $attendance_id);

            if ($result){
                foreach ($result as $announce) {
                    $attend = intval($_POST[$announce->id]); //get the record from the teacher (inut name is the activity id)

                    //check if there is record for the user for this activity
                    $checkForBook = Database::get()->querySingle("SELECT COUNT(id) as count, id FROM attendance_book  WHERE attendance_activity_id = ?d AND uid = ?d", $announce->id, $userID);
                    
                    if($checkForBook->count){
                        //update
                        Database::get()->query("UPDATE attendance_book SET attend = ?d WHERE id = ?d ", $attend, $checkForBook->id);
                        
                    }else{
                        //insert
                        Database::get()->query("INSERT INTO attendance_book SET uid = ?d, attendance_activity_id = ?d, attend = ?d", $userID, $announce->id, $attend);
                    }
                }
                $langAnnDel = "Επιτυχής ενημέρωση";
                $message = "<p class='success'>$langAnnDel</p>";
                $tool_content .= $message . "<br/>";
            }
        }

        //View acivities for a user - (check for auto mechanism) 
        if(isset($_GET['book'])){

$limit = isset($_REQUEST['limit']) ? intval($_REQUEST['limit']) : 0;


            $userID = intval($_GET['book']); //user
            
            //check if there are booking records for the user, otherwise alert message for first input
            $checkForRecords = Database::get()->querySingle("SELECT COUNT(attendance_book.id) as count FROM attendance_book, attendance_activities WHERE attendance_book.attendance_activity_id = attendance_activities.id AND uid = ?d AND attendance_activities.attendance_id = ?d", $userID, $attendance_id)->count;
            if(!$checkForRecords){
                $tool_content .="<div class='alert1'>Θα πρέπει να κάνετε κλικ στο καταχώρηση για να δημιουργηθεί καρτέλα παρουσιολογίου για το χρήστη</div>";
            }
            
            //get all the activities
            $result = Database::get()->queryArray("SELECT * FROM attendance_activities  WHERE attendance_id = ?d  ORDER BY `DATE` DESC", $attendance_id);
            $announcementNumber = count($result);

            if ($announcementNumber > 0) {
                $tool_content .= "<fieldset><legend>" . display_user($userID) . "</legend>";
                $tool_content .= "<script type='text/javascript' src='../auth/sorttable.js'></script>
                                    <form method='post' action='$_SERVER[SCRIPT_NAME]?course=$course_code&book=$userID' onsubmit=\"return checkrequired(this, 'antitle');\">
                                  <table width='100%' class='sortable' id='t2'>";
                $tool_content .= "<tr><th  colspan='2'>Τίτλος</th><th >Ημερομηνία</th><th>Περιγραφή</th><th>Τύπος</th>";
                $tool_content .= "<th width='60' colspan='$colsNum' class='center'>Καταχώρηση</th>";
                $tool_content .= "</tr>";
            } else {
                $tool_content .= "<p class='alert1'>Δεν υπάρχουν δραστηριότητες στο παρουσίολόγιο<br>Μπορείτε να προσθέσετε μία από <a href='$_SERVER[SCRIPT_NAME]?course=$course_code&amp;addActivity=1'>εδώ</a> ή να είσαγετε από τις προτινόμενες</p>\n";
            }
            //ui counter 
            $k = 0;

            if ($result)
                foreach ($result as $announce) {
                    
                    //check if there is auto mechanism
                    if($announce->auto == 1){
                        
                        //check for assignements (if there is already a record do not propose)
                        $checkForAuto = Database::get()->querySingle("SELECT id FROM attendance_book WHERE attendance_activity_id = ?d AND uid = ?d", $announce->id, $userID);
                        if($announce->module_auto_type == 1 && !$checkForAuto){
                            $checkAuto = attendForExersice($userID, $announce->module_auto_id, 1);
                            if($checkAuto){
                                $checkAuto = 1;
                                //Database::get()->query("UPDATE attendance_book SET attend = 1 WHERE attendance_activity_id = ?d AND uid = ?d", $announce->id, $userID);
                            }else{
                                $checkAuto = 0;
                            }
                        }
                    }


                    //check if there is a new record for this activity to show
                    $userAttend = Database::get()->querySingle("SELECT attend FROM attendance_book  WHERE attendance_activity_id = ?d AND uid = ?d", $announce->id, $userID)->attend;

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
                            . "<td>" . $content . "</td>";

                    if ($announce->module_auto_id) {
                        $tool_content .= "<td class='smaller'>Δραστηριότητα μαθήματος";
                        if ($announce->auto) {
                            $tool_content .= "<br>(αυτόματη καταχώρηση παρουσίας)";
                        } else {
                            $tool_content .= "<br>(μη αυτόματη καταχώρηση παρουσίας)";
                        }
                        $tool_content .= "</td>";
                    } else {
                        $tool_content .= "<td class='smaller'>Δραστηριότητα παρουσιολογίου</td>";
                    }

                    $tool_content .= "
                <td width='70' class='center'>
                    <input type='checkbox' value='1' name='" . $announce->id . "'";
                    if($userAttend || $checkAuto){
                        $tool_content .= " checked";
                    }    
                    $tool_content .= ">
                    <input type='hidden' value='" . $userID . "' name='userID'>    
                </td>

                ";
                    $k++;
                } // end of while
            $tool_content .= "<tr><td colspan=6 class='right'><input type='submit' name='bookUser' value='Καταχώρηση' /></td></tr></table></form></fieldset>";
        }

        
        //show all the students
        $limit = isset($_REQUEST['limit']) ? $_REQUEST['limit'] : 0;
        
        //Count only students base on their initial record (not the course)
        $countUser = Database::get()->querySingle("SELECT COUNT(user.id) as count FROM course_user, user WHERE course_user.course_id = ?d AND course_user.user_id = user.id AND user.status = ?d ", $course_id, USER_STUDENT)->count;

        
        $limit_sql = '';

        // display navigation links if users > COURSE_USERS_PER_PAGE
        if ($countUser > COURSE_USERS_PER_PAGE and !isset($_GET['all'])) {
            $limit_sql = "LIMIT $limit, " . COURSE_USERS_PER_PAGE;
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
      <th class='center'>$langAttendanceEdit</th>
    </tr>";


    // Numerating the items in the list to show: starts at 1 and not 0
        $i = $limit + 1;
        $ord = isset($_GET['ord']) ? $_GET['ord'] : '';

        switch ($ord) {
            case 's': $order_sql = 'ORDER BY surname';
                break;
            case 'e': $order_sql = 'ORDER BY email';
                break;
            case 'am': $order_sql = 'ORDER BY am';
                break;
            case 'rd': $order_sql = 'ORDER BY course_user.reg_date DESC';
                break;
            default: $order_sql = 'ORDER BY user.status, editor DESC, tutor DESC, surname, givenname';
                break;
        }

        DataBase::get()->queryFunc("SELECT user.id as userID, user.surname , user.givenname, user.email,
                               user.am, user.has_icon, course_user.status as courseUserStatus,
                               course_user.tutor, course_user.editor, course_user.reviewer, course_user.reg_date
                               FROM course_user, user
                               WHERE `user`.id = `course_user`.`user_id`
                               AND `course_user`.`course_id` = ?d
                               AND user.status = ?d 
                               $order_sql $limit_sql", function($myrow) use(&$tool_content, $course_id, &$i, $langAm, $attendance_limit, $course_code, $userAttendTotal, $attendance_id) {

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
            $tool_content .= "<td class='$class center' width='30'>";

            // tutor right
            if ($myrow->tutor == '1') {
                $tool_content .= "tutor - ";
            }
            // editor right
            if ($myrow->editor == '1') {
                $tool_content .= "editor";
            }

            $tool_content .= "</td>";

            $tool_content .= "<td class='center'>" . userAttendTotal($attendance_id, $myrow->userID). " (από " . $attendance_limit . ")</td>";
            $tool_content .= "<td class='center'><a href='$_SERVER[SCRIPT_NAME]?course=$course_code&amp;book=$myrow->userID'>Επεξεργασία</a></td>";

            $i++;
        }, $course_id, USER_STUDENT, $order_sql);

        $tool_content .= "</table>";

        // display number of users
        $tool_content .= "
    <div class='info'><b>$langTotal</b>: <span class='grey'><b>$countUser </b><em>$langStudents &nbsp;</em></span><br />
      <b>$langDumpUser $langCsv</b>: 1. <a href='dumpuser.php?course=$course_code'>$langcsvenc2</a>
           2. <a href='dumpuser.php?course=$course_code&amp;enc=1253'>$langcsvenc1</a>
      </div>";
        
        
        //do not show activities list
        $showAttendanceActivities = 0;
    }

    //FORM-DISPLAY: list of attendance activities
    if($showAttendanceActivities == 1){
        
        //1.ergasies
        //$checkForExer = Database::get()->queryArray("SELECT assignment.id FROM assignment LEFT OUTER JOIN attendance_activities ON assignment.id = attendance_activities.module_auto_id WHERE assignment.course_id = ?d AND  assignment.active = 1", $course_id);

        
        //get all the availiable activities
        $result = Database::get()->queryArray("SELECT * FROM attendance_activities  WHERE attendance_id = ?d  ORDER BY `DATE` DESC", $attendance_id);
        $announcementNumber = count($result);

        if ($announcementNumber > 0) {
            $tool_content .= "<fieldset><legend>Δραστηριότητες του παρουσιολογίου</legend>";
            $tool_content .= "<script type='text/javascript' src='../auth/sorttable.js'></script>
                              <table width='100%' class='sortable' id='t2'>";
            $tool_content .= "<tr><th  colspan='2'>Τίτλος</th><th >Ημερομηνία</th><th>Περιγραφή</th><th>Τύπος</th>";
            $tool_content .= "<th width='60' colspan='$colsNum' class='center'>$langActions</th>";
            $tool_content .= "</tr>";
        }
        else{
            $tool_content .= "<p class='alert1'>Δεν υπάρχουν δραστηριότητες στο παρουσίολόγιο<br>Μπορείτε να προσθέσετε μία από <a href='$_SERVER[SCRIPT_NAME]?course=$course_code&amp;addActivity=1'>εδώ</a> ή να είσαγετε από τις προτινόμενες</p>\n";
        }
        $k = 0;
        if ($result)
            foreach ($result as $announce) {
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
                        . "<td>" . $content . "</td>";

                if($announce->module_auto_id){
                    $tool_content .= "<td class='smaller'>Δραστηριότητα μαθήματος";
                    if($announce->auto){
                        $tool_content .= "<br>(αυτόματη καταχώρηση παρουσίας)";
                    }else{
                        $tool_content .= "<br>(μη αυτόματη καταχώρηση παρουσίας)";
                    }
                    $tool_content .= "</td>";
                }else{
                    $tool_content .= "<td class='smaller'>Δραστηριότητα παρουσιολογίου</td>";
                }

                $tool_content .= "
                <td width='70' class='right'>
                      <a href='$_SERVER[SCRIPT_NAME]?course=$course_code&amp;modify=$announce->id'>
                      <img src='$themeimg/edit.png' title='" . $langModify . "' /></a>&nbsp;
                      <a href='$_SERVER[SCRIPT_NAME]?course=$course_code&amp;delete=$announce->id' onClick=\"return confirm('$langSureToDelAnnounce');\">
                      <img src='$themeimg/delete.png' title='" . $langDelete . "' /></a>&nbsp;</td>";
                $k++;
            } // end of while
        $tool_content .= "</table></fieldset>";



        //Course activities availiable for the attendance
        $checkForExer = Database::get()->queryArray("SELECT * FROM assignment WHERE assignment.course_id = ?d AND  assignment.active = 1 AND assignment.id NOT IN (SELECT module_auto_id FROM attendance_activities)", $course_id);

        $checkForExerNumber = count($checkForExer);
        
        $tool_content .= "<br><br>";

        if ($checkForExerNumber > 0) {
            $tool_content .= "<fieldset><legend>Δραστηριότητες προς εισαγωγή στο παρουσιολόγιο</legend>";
            $tool_content .= "<script type='text/javascript' src='../auth/sorttable.js'></script>
                              <table width='100%' class='sortable' id='t1'>";
            $tool_content .= "<tr><th  colspan='2'>Τίτλος</th><th >Ημερομηνία</th><th>Περιγραφή</th>";
            $tool_content .= "<th width='60' colspan='$colsNum' class='center'>$langActions</th>";
            $tool_content .= "</tr>";
        }
        else{
            $tool_content .= "<p class='alert1'>Δεν υπάρχουν δραστηριότητες για αυτόματη καταχώρηση</p>\n";
        }

        $k = 0;
        if ($checkForExer)
            foreach ($checkForExer as $newExerToAttendance) {
                $content = standard_text_escape($newExerToAttendance->description);
                $newExerToAttendance->deadline = claro_format_locale_date($dateFormatLong, strtotime($newExerToAttendance->deadline));

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
                        . "<td><div class='smaller'>" . nice_format($newExerToAttendance->deadline) . "</div></td>"
                        . "<td>" . $content . "</td>";

                $tool_content .= "
                <td width='70' class='right'>
                      <a href='$_SERVER[SCRIPT_NAME]?course=$course_code&amp;addCourseActivity=$newExerToAttendance->id&amp;type=1'>
                      ΠΡΟΣΘΗΚΗ</a>&nbsp;";

                $k++;
            } // end of while
        $tool_content .= "</table></fieldset>";


        //attendance limit
        $tool_content .= "<br>
            <form method='post' action='$_SERVER[SCRIPT_NAME]?course=$course_code' onsubmit=\"return checkrequired(this, 'antitle');\">
            <fieldset>
            <legend>Υποχρεωτικές παρουσιές</legend>
            <table class='tbl' width='40%'>
                <tr>
                  <th>Αριθμός υποχρεωτικών παρουσιών:</th><td><input type='text' name='limit' value='$attendance_limit' size='5' /></td>
                </tr>
                <tr>
                  <td class='left'><input type='submit' name='submitAttendanceLimit' value='Ενημέρωση' /></td>
                </tr>
            </table>
            </fieldset>
            </form>";

    }

    
}else{ //Student View
    
    $userID = $uid;
    
    //check if there are booking records for the user, otherwise alert message that there is no input
    $checkForRecords = Database::get()->querySingle("SELECT COUNT(attendance_book.id) as count FROM attendance_book, attendance_activities WHERE attendance_book.attendance_activity_id = attendance_activities.id AND uid = ?d AND attendance_activities.attendance_id = ?d", $userID, $attendance_id)->count;
    if (!$checkForRecords) {
        $tool_content .="<div class='alert1'>Δεν έχει γίνει ακόμη καταχώρηση παρουσιών</div>";
    }


    $result = Database::get()->queryArray("SELECT * FROM attendance_activities  WHERE attendance_id = ?d  ORDER BY `DATE` DESC", $attendance_id);
    $announcementNumber = count($result);

    if ($announcementNumber > 0) {
        $tool_content .= "<fieldset><legend>Παρουσίες</legend>";
        $tool_content .= "<div class='center'>" . userAttendTotal($attendance_id, $userID) . " παρουσίες από τις " . $attendance_limit. " υποχρεωτικές του μαθήματος</div><br>";
        $tool_content .= "<script type='text/javascript' src='../auth/sorttable.js'></script>
                            <table width='100%' class='sortable' id='t2'>";
        $tool_content .= "<tr><th  colspan='2'>Τίτλος</th><th >Ημερομηνία</th><th>Περιγραφή</th><th>Παρουσία/απουσία</th></tr>";
    } else {
        $tool_content .= "<p class='alert1'>Δεν υπάρχουν δραστηριότητες στο παρουσίολόγιο</p>";
    }
    $k = 0;

    if ($result)
        foreach ($result as $announce) {
            
            //check if the user has attend for this activity
            $userAttend = Database::get()->querySingle("SELECT attend FROM attendance_book  WHERE attendance_activity_id = ?d AND uid = ?d", $announce->id, $userID)->attend;

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
                    . "<td>" . $content . "</td>";

            $tool_content .= "
                <td width='70' class='center'>";
                    
            if ($userAttend) {
                $tool_content .= "Παρουσία";
            }elseif($announce->date > date("Y-m-d")){
                $tool_content .= "-";
            }else{
                $tool_content .= "Απουσία";
            }
            $tool_content .= "</td>";
            
            $k++;
        } // end of while
    $tool_content .= "</table></fieldset>";
}


//Function to return attend if the user has submit an exercise
function attendForExersice($userID, $exeID, $exeType){
    //echo $attendance_id."<br>".$userID."<br>".$exeID."<br>"."$exeType";
    if($exeType == 1){ //asignments: valid submission!
       $autoAttend = Database::get()->querySingle("SELECT COUNT(id) as count FROM assignment_submit WHERE uid = ?d AND assignment_id = ?d", $userID, $exeID)->count; 
       if($autoAttend){
           return 1;
       }else{
           return 0;
       }
    }
}

//Function to get the total attend number for a user in a course attendance
function userAttendTotal ($attendance_id, $userID){

    $userAttendTotal = Database::get()->querySingleNT("SELECT SUM(attend) as count FROM attendance_book, attendance_activities WHERE attendance_book.uid = ?d AND  attendance_book.attendance_activity_id = attendance_activities.id AND attendance_activities.attendance_id = ?d", $userID, $attendance_id)->count;

    if($userAttendTotal){
        return $userAttendTotal;
    }else{
        return 0;
    }
}

draw($tool_content, 2, null, $head_content);




  