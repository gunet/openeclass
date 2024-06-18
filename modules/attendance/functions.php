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

require_once 'modules/progress/AttendanceEvent.php';

/**
 * @brief admin available attendances
 */
function display_attendances() {

    global $course_id, $tool_content, $course_code,
           $langDelete, $langConfirmDelete, $langCreateDuplicate,
           $langAvailableAttendances, $langNoAttendances, $is_editor, $is_course_reviewer,
           $langViewHide, $langViewShow, $langEditChange, $langStart, $langFinish, $uid;

    if ($is_course_reviewer) {
        $result = Database::get()->queryArray("SELECT * FROM attendance WHERE course_id = ?d", $course_id);
    } else {
        $result = Database::get()->queryArray("SELECT attendance.* "
                . "FROM attendance, attendance_users "
                . "WHERE attendance.active = 1 "
                . "AND attendance.course_id = ?d "
                . "AND attendance.id = attendance_users.attendance_id AND attendance_users.uid = ?d", $course_id, $uid);
    }
    if (count($result) == 0) { // no attendances
        $tool_content .= "
            <div class='col-12'>
                <div class='alert alert-warning'><i class='fa-solid fa-triangle-exclamation fa-lg'></i><span>$langNoAttendances</span></div></div>";
    } else {
        $tool_content .= "<div class='table-responsive'>";
        $tool_content .= "<table class='table-default'>";
        $tool_content .= "<thead><tr class='list-header'>
                            <th style='width:46%;'>$langAvailableAttendances</th>
                            <th style='width:22%;'>$langStart</th>
                            <th style='width:22%;'>$langFinish</th>";
        if( $is_editor) {
            $tool_content .= "<th style='width:10%;'>" . icon('fa-gears') . "</th>";
        }
        $tool_content .= "</tr></thead>";
        foreach ($result as $a) {
            $start_date = DateTime::createFromFormat('Y-m-d H:i:s', $a->start_date)->format('d-m-Y H:i');
            $end_date = DateTime::createFromFormat('Y-m-d H:i:s', $a->end_date)->format('d-m-Y H:i');
            $row_class = !$a->active ? "class='not_visible'" : "";
            $tool_content .= "
                    <tr $row_class>
                        <td style='width:46%;'>
                            <a href='$_SERVER[SCRIPT_NAME]?course=$course_code&amp;attendance_id=$a->id'>".q($a->title)."</a>
                        </td>
                        <td style='width:22%;'>$start_date</td>
                        <td style='width:22%;'>$end_date</td>";
            if( $is_editor) {
                $tool_content .= "<td style='width:10%;' class='option-btn-cell text-end'>";
                $tool_content .= action_button(array(
                                    array('title' => $langEditChange,
                                          'url' => "$_SERVER[SCRIPT_NAME]?course=$course_code&amp;attendance_id=$a->id&amp;editSettings=1",
                                          'icon' => 'fa-cogs'),
                                    array('title' => $a->active ? $langViewHide : $langViewShow,
                                          'url' => "$_SERVER[SCRIPT_NAME]?course=$course_code&amp;attendance_id=$a->id&amp;vis=" .
                                                  ($a->active ? '0' : '1'),
                                          'icon' => $a->active ? 'fa-eye-slash' : 'fa-eye'),
                                    array('title' => $langCreateDuplicate,
                                          'url' => "$_SERVER[SCRIPT_NAME]?course=$course_code&amp;attendance_id=$a->id&amp;dup=1",
                                          'icon' => 'fa-copy'),
                                    array('title' => $langDelete,
                                          'url' => "$_SERVER[SCRIPT_NAME]?course=$course_code&amp;delete_at=$a->id",
                                          'icon' => 'fa-xmark',
                                          'class' => 'delete',
                                          'confirm' => $langConfirmDelete))
                                        );
                $tool_content .= "</td>";
            }
            $tool_content .= "</tr>";
        }
        $tool_content .= "</table></div>";
    }
}

/**
 * @brief display attendance users
 * @param int $attendance_id
 * @param int $actID
 */
function register_user_presences($attendance_id, $actID) {

    global $tool_content, $course_id, $course_code,
           $langName, $langSurname, $langRegistrationDateShort, $langAttendanceAbsences,
           $langAmShort, $langAttendanceBooking, $langID, $langQuotaSuccess, $langCancel;

    $result = Database::get()->querySingle("SELECT * FROM attendance_activities WHERE id = ?d", $actID);
    $act_type = $result->auto; // type of activity
    $tool_content .= "
    <div class='col-12'>
        <div class='alert alert-info'><i class='fa-solid fa-circle-info fa-lg'></i><span>" . q($result->title) . "</span></div></div>";

    if (isset($_POST['bookUsersToAct'])) {
        if (!isset($_POST['token']) || !validate_csrf_token($_POST['token'])) {
            csrf_token_error();
        }
        //get all the active users
        $activeUsers = Database::get()->queryArray("SELECT uid as userID FROM attendance_users WHERE attendance_id = ?d", $attendance_id);
        if ($activeUsers) {
            foreach ($activeUsers as $result) {
                $userID = $result->userID;
                if (isset($_POST['updateUser'][$userID])) {
                    $attend = isset($_POST['attend'][$userID])? 1: 0;
                    $checkForBook = Database::get()->querySingle("SELECT id, attend FROM attendance_book
                        WHERE attendance_activity_id = ?d AND uid = ?d", $actID, $userID);
                    if ($checkForBook) {
                        if ($checkForBook->attend != $attend) {
                            Database::get()->query("UPDATE attendance_book SET attend = ?d
                                 WHERE id = ?d", $attend, $checkForBook->id);
                        }
                    } else {
                        Database::get()->query("INSERT INTO attendance_book
                            SET uid = ?d, attendance_activity_id = ?d, attend = ?d, comments = ''",
                            $userID, $actID, $attend);
                    }
                    triggerAttendanceGame($course_id, $userID, $attendance_id, AttendanceEvent::UPDATE);
                }
            }
            Session::flash('message', $langQuotaSuccess);
            Session::flash('alert-class', 'alert-success');
            redirect_to_home_page("modules/attendance/index.php?course=$course_code&attendance_id=$attendance_id&ins=" . getIndirectReference($actID));
        }
    }

    //display users
    $resultUsers = Database::get()->queryArray("SELECT attendance_users.id as recID,
                                                    attendance_users.uid AS userID, user.surname AS surname,
                                                    user.givenname AS name, user.am AS am,
                                                    DATE(course_user.reg_date) AS reg_date
                                                FROM attendance_users
                                                JOIN user ON attendance_users.uid = user.id AND attendance_id = ?d
                                                LEFT JOIN course_user ON user.id = course_user.user_id
                                                    AND `course_user`.`course_id` = ?d
                                                ORDER BY surname, name", $attendance_id, $course_id);
    $attendUsers = Database::get()->queryArray('SELECT uid, attend FROM attendance_book
        WHERE attendance_activity_id = ?d', $actID);
    $attended = [];
    foreach ($attendUsers as $attendUser) {
        $attended[$attendUser->uid] = $attendUser->attend;
    }
    if ($resultUsers) {
        //table to display the users
        $tool_content .= "<div class='col-12'><div class='form-wrapper form-edit rounded'>
        <form class='form-horizontal' id='user_attendances_form' method='post' action='$_SERVER[SCRIPT_NAME]?course=$course_code&amp;attendance_id=$attendance_id&amp;ins=" . getIndirectReference($actID) . "'>
        <div class='table-responsive'>
        <table id='users_table{$course_id}' class='table-default custom_list_order'>
            <thead>
                <tr class='list-header'>
                  <th class='count-col'>$langID</th>
                  <th>$langName $langSurname</th>
                  <th>$langAmShort</th>
                  <th>$langRegistrationDateShort</th>
                  <th>$langAttendanceAbsences</th>

                </tr>
            </thead>
            <tbody>";


        $cnt = 0;
        foreach ($resultUsers as $resultUser) {
            $userID = $resultUser->userID;
            $classvis = '';
            if (is_null($resultUser->reg_date)) {
                $classvis = 'not_visible';
            }
            $cnt++;
            $checked = (isset($attended[$userID]) && $attended[$userID])? 'checked': '';
            $reg_date = is_null($resultUser->reg_date)? '': format_locale_date(strtotime($resultUser->reg_date), 'short', false);
            $tool_content .= "
              <tr class='$classvis'>
                <td class='count-col'>$cnt</td>
                <td>" . display_user($userID) . "</td>
                <td>" . q($resultUser->am) . "</td>
                <td>$reg_date</td>
                <td>
                    <label class='label-container'>
                        <input type='checkbox' value='1' name='attend[$userID]' $checked>
                        <span class='checkmark'></span>
                    </label>
                    <input type='hidden' name='updateUser[$userID]' value='1'>
                </td>
              </tr>";
        }
        $tool_content .= "</tbody></table>";
        $tool_content .= "<div class='form-group'>";
        $tool_content .= "<div class='col-12 d-flex justify-content-end align-items-center mt-5'>" .
                        form_buttons(array(
                            array(
                                'class' => 'submitAdminBtn',
                                'text' => $langAttendanceBooking,
                                'name' => 'bookUsersToAct',
                                'value'=> $langAttendanceBooking
                                ))).

        "<a href='index.php?course=$course_code&amp;attendance_id=" . $attendance_id . "' class='btn cancelAdminBtn ms-2'>$langCancel</a>";

        $tool_content .= "</div></div>";
        $tool_content .= generate_csrf_token_form_field() ."</form></div></div>";
        $tool_content .= "</tbody></table></div>";
    }
}

/**
 * @brief display attendance activities
 * @param type $attendance_id
 */
function display_attendance_activities($attendance_id) {

    global $tool_content, $course_code, $attendance, $langAttendanceInsMan,
           $langAttendanceActList, $langTitle, $langType, $langDate, $action_bar,
           $langGradebookNoTitle, $langAssignment,$langAttendanceInsAut,
           $langDelete, $langEditChange, $langConfirmDelete, $langAttendanceNoActMessage1,
           $langHere, $langAttendanceNoActMessage3, $langAttendanceActivity,
           $langConfig, $langStudents, $langGradebookAddActivity, $langInsertWorkCap, $langExercise,
           $langAdd, $langExport, $langBack, $langNoStudentsInAttendance, $langBBB,
           $is_editor, $is_course_reviewer, $is_collaborative_course;

    $attendance_id_ind = getIndirectReference($attendance_id);
    if ($is_editor) {
        $action_bar = action_bar(
            array(
                array('title' => $langBack,
                  'url' => "$_SERVER[SCRIPT_NAME]?course=$course_code",
                  'icon' => 'fa-reply',
                  'level' => 'primary'),
                array('title' => $langStudents,
                    'url' => "$_SERVER[SCRIPT_NAME]?course=$course_code&amp;attendance_id=$attendance_id&amp;attendanceBook=1",
                    'level' => 'primary-label',
                    'icon' => 'fa-users'),
                array('title' => $langAdd,
                    'level' => 'primary-label',
                    'options' => array(
                        array('title' => $langGradebookAddActivity,
                            'url' => "$_SERVER[SCRIPT_NAME]?course=$course_code&amp;attendance_id=$attendance_id&amp;addActivity=1",
                            'icon' => 'fa fa-plus space-after-icon',
                            'class' => ''),
                        array('title' => "$langInsertWorkCap",
                            'url' => "$_SERVER[SCRIPT_NAME]?course=$course_code&amp;attendance_id=$attendance_id&amp;addActivityAs=1",
                            'icon' => 'fa fa-flask space-after-icon',
                            'class' => ''),
                        array('title' => "$langExercise",
                            'url' => "$_SERVER[SCRIPT_NAME]?course=$course_code&amp;attendance_id=$attendance_id&amp;addActivityEx=1",
                            'icon' => 'fa fa-edit space-after-icon',
                            'class' => ''),
                        array('title' => "$langBBB",
                            'url' => "$_SERVER[SCRIPT_NAME]?course=$course_code&amp;attendance_id=$attendance_id&amp;addActivityTc=1",
                            'icon' => 'fa fa-exchange space-after-icon',
                            'class' => '')),
                    'icon' => 'fa-plus',
                    'show' => (isset($is_collaborative_course) and !$is_collaborative_course)),
                array('title' => $langAdd,
                    'level' => 'primary-label',
                    'options' => array(
                        array('title' => $langGradebookAddActivity,
                            'url' => "$_SERVER[SCRIPT_NAME]?course=$course_code&amp;attendance_id=$attendance_id&amp;addActivity=1",
                            'icon' => 'fa fa-plus space-after-icon',
                            'class' => ''),
                        array('title' => "$langInsertWorkCap",
                            'url' => "$_SERVER[SCRIPT_NAME]?course=$course_code&amp;attendance_id=$attendance_id&amp;addActivityAs=1",
                            'icon' => 'fa fa-flask space-after-icon',
                            'class' => ''),
                        array('title' => "$langBBB",
                            'url' => "$_SERVER[SCRIPT_NAME]?course=$course_code&amp;attendance_id=$attendance_id&amp;addActivityTc=1",
                            'icon' => 'fa fa-exchange space-after-icon',
                            'class' => '')),
                    'icon' => 'fa-plus',
                    'show' => (isset($is_collaborative_course) and $is_collaborative_course)),
                array('title' => $langConfig,
                    'url' => "$_SERVER[SCRIPT_NAME]?course=$course_code&amp;attendance_id=$attendance_id&amp;editSettings=1",
                    'icon' => 'fa-cog'),
                array('title' => "$langExport",
                    'url' => "dumpattendancebook.php?course=$course_code&amp;attendance_id=$attendance_id_ind",
                    'icon' => 'fa-file-excel')
            ),
            true
        );
        $tool_content .= $action_bar;
    }

    $participantsNumber = Database::get()->querySingle("SELECT COUNT(id) AS count
                                            FROM attendance_users WHERE attendance_id=?d ", $attendance_id)->count;
    if ($participantsNumber == 0) {
        $tool_content .= "
        <div class='col-12'>
            <div class='alert alert-warning'><i class='fa-solid fa-triangle-exclamation fa-lg'></i><span>$langNoStudentsInAttendance <a href='$_SERVER[SCRIPT_NAME]?course=$course_code&amp;attendance_id=" . $attendance->id . "&amp;editUsers=1'>$langHere</a>.</span></div></div>";
    }
    //get all the available activities
    $result = Database::get()->queryArray("SELECT * FROM attendance_activities WHERE attendance_id = ?d  ORDER BY `DATE` DESC", $attendance_id);
    if (count($result) > 0) {
        $tool_content .= "
        <p class='form-label'>$langAttendanceActList</p>
                        <div class='table-responsive'>
                        <table class='table-default'>
                        <thead>
                        <tr class='list-hader'>
                            <th>$langTitle</th>
                            <th>$langDate</th>
                            <th>$langType</th>
                            <th>$langStudents</th>
                            <th></th>
                        </tr></thead>";

        foreach ($result as $details) {
            $tool_content .= "<tr><td>";
            if ($is_editor) {
                $tool_content .= "<a href='$_SERVER[SCRIPT_NAME]?course=$course_code&amp;attendance_id=$attendance_id&amp;ins=" . getIndirectReference($details->id). "'>" . (!empty($details->title) ? q($details->title) : $langGradebookNoTitle) . "</a>";
            } else if ($is_course_reviewer) {
                $tool_content .= "<a href='$_SERVER[SCRIPT_NAME]?course=$course_code&amp;attendance_id=$attendance_id&amp;attendanceBook=1'>" . (!empty($details->title) ? q($details->title) : $langGradebookNoTitle) . "</a>";
            }
            $tool_content .= "</td><td>";
             if (!is_null($details->date)) {
                 $tool_content .= format_locale_date(strtotime($details->date), 'short', false);
             } else {
                 $tool_content .= " -- ";
             }
            $tool_content .= "</td><td>";

            if($details->module_auto_id) {
                if($details->module_auto_type == GRADEBOOK_ACTIVITY_EXERCISE) {
                        $tool_content .= $langExercise;
                } elseif($details->module_auto_type == GRADEBOOK_ACTIVITY_ASSIGNMENT) {
                        $tool_content .= $langAssignment;
                }
                if($details->auto) {
                    $tool_content .= "<small class='help-block'>($langAttendanceInsAut)</small>";
                } else {
                    $tool_content .= "<small class='help-block'>($langAttendanceInsMan)</small>";
                }
            } else {
                $tool_content .= $langAttendanceActivity;
            }
            $tool_content .= "</td>";
            $tool_content .= "<td>" . userAttendTotalActivityStats($details->id, $participantsNumber, $attendance_id) . "</td>";
            if ($is_editor) {
                $tool_content .= "<td class='option-btn-cell text-end'>" .
                    action_button(array(
                        array('title' => $langEditChange,
                            'icon' => 'fa-edit',
                            'url' => "$_SERVER[SCRIPT_NAME]?course=$course_code&amp;attendance_id=$attendance_id&amp;modify=" . getIndirectReference($details->id)
                        ),
                        array('title' => $langDelete,
                            'icon' => 'fa-xmark',
                            'url' => "$_SERVER[SCRIPT_NAME]?course=$course_code&amp;&amp;attendance_id=$attendance_id&amp;delete=" . getIndirectReference($details->id),
                            'confirm' => $langConfirmDelete,
                            'class' => 'delete'))) .
                    "</td>";
            }
            $tool_content .= "</tr>";
        } // end of foreach
        $tool_content .= "</table></div>";
    } else {
        $tool_content .= "
        <div class='col-12'>
            <div class='alert alert-warning'><i class='fa-solid fa-triangle-exclamation fa-lg'></i><span>$langAttendanceNoActMessage1 $langAttendanceNoActMessage3</span></div></div>";
    }
}

/**
 * @brief display available exercises for adding them to attendance
 * @param int $attendance_id
 */
function attendance_display_available_exercises($attendance_id) {

    global $course_id, $course_code, $tool_content, $langDescription, $langAdd, $langAttendanceNoActMessageExe4, $langTitle, $urlServer;

    $checkForExer = Database::get()->queryArray("SELECT * FROM exercise WHERE exercise.course_id = ?d
                                AND exercise.active = 1 AND exercise.id
                                NOT IN (SELECT module_auto_id FROM attendance_activities WHERE module_auto_type = 2 AND attendance_id = ?d)", $course_id, $attendance_id);
    $checkForExerNumber = count($checkForExer);
    if ($checkForExerNumber > 0) {
        $tool_content .= "<div class='table-responsive'>";
        $tool_content .= "<table class='table-default'>";
        $tool_content .= "<thead><tr class='list-header'><th>$langTitle</th><th>$langDescription</th>";
        $tool_content .= "<th></th>";
        $tool_content .= "</tr></thead>";

        foreach ($checkForExer as $newExerToGradebook) {
            $content = ellipsize_html($newExerToGradebook->description, 50);
            $tool_content .= "<tr>";
            $tool_content .= "<td><a href='{$urlServer}modules/exercise/admin.php?course=$course_code&amp;exerciseId=$newExerToGradebook->id&amp;preview=1'>" . q($newExerToGradebook->title) . "</a></td>";
            $tool_content .= "<td>" . $content . "</td>";
            $tool_content .= "<td class='text-end'>" . icon('fa-plus', $langAdd, "$_SERVER[SCRIPT_NAME]?course=$course_code&amp;attendance_id=$attendance_id&amp;addCourseActivity=" . $newExerToGradebook->id . "&amp;type=2");
            $tool_content .= "</td></tr>";
        }
        $tool_content .= "</table></div>";
    } else {
        $tool_content .= "
        <div class='col-12'>
        <div class='alert alert-warning'><i class='fa-solid fa-triangle-exclamation fa-lg'></i><span>$langAttendanceNoActMessageExe4</span></div></div>";
    }
}

/**
 * @brief display available assignments for adding them to attendance
 * @param int $attendance_id
 */
function attendance_display_available_assignments($attendance_id) {

    global $course_id, $course_code, $tool_content, $urlServer,
           $m, $langDescription, $langAttendanceNoActMessageAss4,
           $langAdd, $langTitle;

    $checkForAss = Database::get()->queryArray("SELECT * FROM assignment WHERE assignment.course_id = ?d
                                                AND assignment.active = 1
                                                AND assignment.id NOT IN
                                            (SELECT module_auto_id FROM attendance_activities WHERE module_auto_type = 1
                                                        AND attendance_id = ?d)", $course_id, $attendance_id);

    $checkForAssNumber = count($checkForAss);

    if ($checkForAssNumber > 0) {
        $tool_content .= "<div class='table-responsive'>
                            <table class='table-default'>";
        $tool_content .= "<thead><tr class='list-header'><th>$langTitle</th><th>$langDescription</th>";
        $tool_content .= "<th></th>";
        $tool_content .= "</tr></thead>";
        foreach ($checkForAss as $newAssToGradebook) {
            $content = ellipsize_html($newAssToGradebook->description, 50);
            if ($newAssToGradebook->assign_to_specific) {
                $content .= "$m[WorkAssignTo]:<br>";
                $checkForAssSpec = Database::get()->queryArray("SELECT user_id, user.surname, user.givenname
                                                    FROM `assignment_to_specific`, user
                                                    WHERE user_id = user.id AND assignment_id = ?d", $newAssToGradebook->id);
                foreach ($checkForAssSpec as $checkForAssSpecR) {
                    $content .= q($checkForAssSpecR->surname) . " " . q($checkForAssSpecR->givenname) . "<br>";
                }
            }
            $tool_content .= "<tr>";
            $tool_content .= "<td><a href='{$urlServer}modules/work/index.php?course=$course_code&amp;id=$newAssToGradebook->id'>" . q($newAssToGradebook->title) . "</a></td>";
            $tool_content .= "<td>" . $content . "</td>";
            $tool_content .= "<td class='text-end'>".icon('fa-plus', $langAdd, "$_SERVER[SCRIPT_NAME]?course=$course_code&amp;attendance_id=$attendance_id&amp;addCourseActivity=" . $newAssToGradebook->id . "&amp;type=1");
            $tool_content .= "</td></tr>";
        } // end of while
        $tool_content .= "</table></div>";
    } else {
        $tool_content .= "
        <div class='col-12'>
        <div class='alert alert-warning'><i class='fa-solid fa-triangle-exclamation fa-lg'></i><span>$langAttendanceNoActMessageAss4</span></div></div>";
    }
}

/**
 * @brief display available tc sessions for adding them to attendance
 * @param type $attendance_id
 */
function attendance_display_available_tc($attendance_id) {

    global $tool_content, $course_code, $course_id, $langGradebookActivityDate,
            $langTitle, $langAdd, $langAttendanceNoActMessageTc;

    $checkForTc = Database::get()->queryArray("SELECT * FROM tc_session WHERE course_id = ?d
                                                AND active = '1'
                                                AND (end_date IS NULL OR end_date >= " . DBHelper::timeAfter() . ")
                                                AND id NOT IN
                                            (SELECT module_auto_id FROM attendance_activities WHERE module_auto_type = 1
                                                        AND attendance_id = ?d)", $course_id, $attendance_id);

    $checkForTcNumber = count($checkForTc);

    if ($checkForTcNumber > 0) {
        $tool_content .= "<div class='table-responsive'>
                            <table class='table-default'>";
        $tool_content .= "<thead><tr class='list-header'><th>$langTitle</th><th>$langGradebookActivityDate</th>";
        $tool_content .= "<th></th>";
        $tool_content .= "</tr></thead>";
        foreach ($checkForTc as $data) {
            $tool_content .= "<tr><td>" . q($data->title) . "</td>";
            $tool_content .= "<td>". format_locale_date(strtotime($data->start_date)) . "</td>";
            $tool_content .= "<td class='text-end'>".icon('fa-plus', $langAdd, "$_SERVER[SCRIPT_NAME]?course=$course_code&amp;attendance_id=$attendance_id&amp;addCourseActivity=" . $data->id . "&amp;type=4");
        } // end of while
        $tool_content .= "</tr></table></div>";
    } else {
        $tool_content .= "
        <div class='col-12'>
        <div class='alert alert-warning'><i class='fa-solid fa-triangle-exclamation fa-lg'></i><span>$langAttendanceNoActMessageTc</span></div></div>";
    }
}

/**
 * @brief add other attendance activity
 * @param type $attendance_id
 */
function add_attendance_other_activity($attendance_id) {

    global $tool_content, $course_code, $langDescription,
           $langTitle, $langAttendanceInsAut, $langAdd, $langSave,
           $langAttendanceActivityDate, $language, $head_content, $urlAppend;

    load_js('bootstrap-datetimepicker');
    $head_content .= "
    <script type='text/javascript'>
    $(function() {
        $('#startdatepicker').datetimepicker({
            format: 'dd-mm-yyyy hh:ii',
            pickerPosition: 'bottom-right',
            language: '$language',
            autoclose: true
        });
    });
    </script>";

    $date_error = Session::getError('date');
    $tool_content .= "
    <div class='d-lg-flex gap-4 mt-4'>
        <div class='flex-grow-1'>
            <div class='form-wrapper form-edit rounded px-0 border-0'>
                <form class='form-horizontal' role='form' method='post' action='$_SERVER[SCRIPT_NAME]?course=$course_code&amp;attendance_id=$attendance_id'>
                    <fieldset>";
                    if (isset($_GET['modify'])) { // modify an existing attendance activity

                        $id  = filter_var(getDirectReference($_GET['modify']), FILTER_VALIDATE_INT);
                        //All activity data (check if it's in this attendance)
                        $modifyActivity = Database::get()->querySingle("SELECT * FROM attendance_activities WHERE id = ?d AND attendance_id = ?d", $id, $attendance_id);
                        $titleToModify = Session::has('actTitle') ? Session::get('actTitle') : $modifyActivity->title;
                        $contentToModify = Session::has('actDesc') ? Session::get('actDesc') : $modifyActivity->description;
                        $attendanceActivityToModify = $id;
                        if (Session::has('date')) {
                            $date = Session::get('date');
                        }
                        if ($modifyActivity->date) {
                            $date = DateTime::createFromFormat('Y-m-d H:i:s', $modifyActivity->date)->format('d-m-Y H:i:s');
                        }
                        $module_auto_id = $modifyActivity->module_auto_id;
                        $auto = $modifyActivity->auto;
                    }  else { //new activity
                        $attendanceActivityToModify = "";
                        $titleToModify = Session::has('actTitle') ? Session::get('actTitle') : '';
                        $contentToModify = Session::has('actDesc') ? Session::get('actDesc') : '';
                        $date = Session::has('date') ? Session::get('date') : '';
                    }
                    if (!isset($contentToModify)) $contentToModify = "";
                    @$tool_content .= "
                        <div class='form-group'>
                            <label for='actTitle' class='col-sm-6 control-label-notes'>$langTitle</label>
                            <div class='col-sm-12'>
                                <input type='text' class='form-control' name='actTitle' value='$titleToModify'/>
                            </div>
                        </div>
                        <div class='form-group".($date_error ? " has-error" : "")." mt-4'>
                            <label for='date' class='col-sm-6 control-label-notes'>$langAttendanceActivityDate</label>
                            <div class='col-sm-12'>
                                <input type='text' class='form-control' name='date' id='startdatepicker' value='" . datetime_remove_seconds($date) . "'/>
                                <span class='help-block'>$date_error</span>
                            </div>
                        </div>
                        <div class='form-group mt-4'>
                            <label for='actDesc' class='col-sm-6 control-label-notes'>$langDescription</label>
                            <div class='col-sm-12'>
                                " . rich_text_editor('actDesc', 4, 20, $contentToModify) . "
                            </div>
                        </div>";
                    if (isset($module_auto_id) && $module_auto_id != 0) { //accept the auto attendance mechanism
                        $tool_content .= "<div class='form-group mt-4'>
                                <div class='col-sm-12'>
                                    <label class='label-container'><input type='checkbox' value='1' name='auto' ";
                        if ($auto) {
                            $tool_content .= " checked";
                        }
                        $tool_content .= "/><span class='checkmark'></span>$langAttendanceInsAut</label></div>";
                    }
                    $tool_content .= "


                    <div class='form-group mt-5'>
                    <div class='col-12 d-flex justify-content-end align-items-center'>".form_buttons(array(
                        array(
                            'class' => 'submitAdminBtn',
                            'text' => $langSave,
                            'name' => 'submitAttendanceActivity',
                            'value'=> $langAdd
                        ),
                        array(
                            'class' => 'cancelAdminBtn ms-1',
                            'href' => "$_SERVER[SCRIPT_NAME]?course=$course_code"
                        )
                    ))."</div></div>";
                    if (isset($_GET['modify'])) {
                        $tool_content .= "<input type='hidden' name='id' value='" . $attendanceActivityToModify . "'>";
                    } else {
                        $tool_content .= " <input type='hidden' name='id' value=''>";
                    }
                    $tool_content .= "</fieldset>
                </form>
            </div>
        </div>
        <div class='d-none d-lg-block'>
            <img class='form-image-modules' src='".get_form_image()."' alt='form-image'>
        </div>
    </div>";
}



/**
 * @brief add available activity in attendance
 * @global type $course_id
 * @param type $attendance_id
 * @param type $id
 * @param type $type
 */
function add_attendance_activity($attendance_id, $id, $type) {

    global $course_id;
    $actTitle = "";
    if ($type == GRADEBOOK_ACTIVITY_ASSIGNMENT) { //  add  assignments
        //checking if it's new or not
        $checkForAss = Database::get()->querySingle("SELECT * FROM assignment WHERE assignment.course_id = ?d
                                                        AND assignment.active = 1 AND assignment.id
                                            NOT IN (SELECT module_auto_id FROM attendance_activities
                                                    WHERE module_auto_type = 1
                                                    AND attendance_id = ?d)
                                                    AND assignment.id = ?d", $course_id, $attendance_id, $id);
        if ($checkForAss) {
            $module_auto_id = $checkForAss->id;
            $module_auto_type = 1;
            $module_auto = 1; //auto grade enabled by default
            $actTitle = $checkForAss->title;
            $actDate = $checkForAss->deadline;
            $actDesc = $checkForAss->description;
            Database::get()->query("INSERT INTO attendance_activities
                                        SET attendance_id = ?d, title = ?s, `date` = ?t, description = ?s,
                                        module_auto_id = ?d, auto = ?d, module_auto_type = ?d",
                                    $attendance_id, $actTitle, $actDate, $actDesc, $module_auto_id, $module_auto, $module_auto_type);
            $sql = Database::get()->queryArray("SELECT uid FROM attendance_users WHERE attendance_id = ?d", $attendance_id);
            foreach ($sql as $u) {
                $grd = Database::get()->querySingle("SELECT * "
                        . "FROM assignment_submit "
                        . "WHERE assignment_id =?d "
                        . "AND uid = $u->uid", $id);
                if($grd) {
                    update_attendance_book($u->uid, $id, GRADEBOOK_ACTIVITY_ASSIGNMENT);
                }
            }
        }
    }

    if ($type == GRADEBOOK_ACTIVITY_EXERCISE) { // add exercises
        //checking if it is new or not
        $checkForExe = Database::get()->querySingle("SELECT * FROM exercise WHERE exercise.course_id = ?d
                                                            AND exercise.active = 1 AND exercise.id
                                                    NOT IN (SELECT module_auto_id FROM attendance_activities
                                                                WHERE module_auto_type = 2 AND attendance_id = ?d)
                                                    AND exercise.id = ?d", $course_id, $attendance_id, $id);
        if ($checkForExe) {
            $module_auto_id = $checkForExe->id;
            $module_auto_type = 2; //2 for exercises
            $module_auto = 1;
            $actTitle = $checkForExe->title;
            $actDate = $checkForExe->end_date;
            $actDesc = $checkForExe->description;

            Database::get()->query("INSERT INTO attendance_activities
                                        SET attendance_id = ?d, title = ?s, `date` = ?t, description = ?s,
                                        module_auto_id = ?d, auto = ?d, module_auto_type = ?d",
                                    $attendance_id, $actTitle, $actDate, $actDesc, $module_auto_id, $module_auto, $module_auto_type);
            $sql = Database::get()->queryArray("SELECT uid FROM attendance_users WHERE attendance_id = ?d", $attendance_id);
            foreach ($sql as $u) {
                $exerciseUserRecord = Database::get()->querySingle("SELECT * FROM exercise_user_record WHERE eid = ?d AND uid = $u->uid AND attempt_status != ?s AND attempt_status != ?s LIMIT 1", $id, ATTEMPT_PAUSED, ATTEMPT_CANCELED);
                if ($exerciseUserRecord) {
                    update_attendance_book($u->uid, $id, GRADEBOOK_ACTIVITY_EXERCISE);
                }
            }
        }
    }

    if ($type == GRADEBOOK_ACTIVITY_TC) { // add tc
        $checkForTc = Database::get()->querySingle("SELECT * FROM tc_session WHERE course_id = ?d
                                                AND active = '1'
                                                AND (end_date IS NULL OR end_date >= " . DBHelper::timeAfter() . ")
                                                AND id NOT IN
                                            (SELECT module_auto_id FROM attendance_activities WHERE module_auto_type = 4
                                                        AND attendance_id = ?d) AND id = ?d", $course_id, $attendance_id, $id);

        if ($checkForTc) {
            $module_auto_id = $checkForTc->id;
            $module_auto_type = 4; // 4 for tc
            $module_auto = 1;
            $actTitle = $checkForTc->title;
            $actDate = $checkForTc->start_date;
            $actDesc = $checkForTc->description;
            $meetingid = $checkForTc->meeting_id;

            Database::get()->query("INSERT INTO attendance_activities
                                        SET attendance_id = ?d, title = ?s, `date` = ?t, description = ?s,
                                        module_auto_id = ?d, auto = ?d, module_auto_type = ?d",
                                    $attendance_id, $actTitle, $actDate, $actDesc, $module_auto_id, $module_auto, $module_auto_type);
            $sql = Database::get()->queryArray("SELECT uid FROM attendance_users WHERE attendance_id = ?d", $attendance_id);
            foreach ($sql as $u) {
                $TcUserRecord = Database::get()->querySingle("SELECT * FROM tc_attendance WHERE meetingid = ?s "
                                                            . "AND bbbuserid = (SELECT bbbuserid "
                                                                                . "FROM tc_log "
                                                                                . "WHERE fullName = '" . uid_to_name($u->uid) . "' "
                                                                                . "AND meetingid = ?s ORDER BY `date` LIMIT 1)", $meetingid, $meetingid);
                if ($TcUserRecord) {
                    update_attendance_book($u->uid, $id, GRADEBOOK_ACTIVITY_TC);
                }
            }
        }
    }
    return $actTitle;
}


/**
 *
 * @param type $attendance_id
 * @param type $uid
 */
function update_user_attendance_activities($attendance_id, $uid) {
    $attendanceActivities = Database::get()->queryArray("SELECT * FROM attendance_activities WHERE attendance_id = ?d AND auto = 1", $attendance_id);
    foreach ($attendanceActivities as $attendanceActivity) {
        if ($attendanceActivity->module_auto_type == GRADEBOOK_ACTIVITY_EXERCISE) {
            $exerciseUserRecord = Database::get()->querySingle("SELECT * FROM exercise_user_record WHERE eid = ?d AND uid = $uid AND attempt_status != ?d AND attempt_status != ?d LIMIT 1", $attendanceActivity->module_auto_id, ATTEMPT_PAUSED, ATTEMPT_CANCELED);
            if ($exerciseUserRecord) {
                $allow_insert = TRUE;
            }
        } elseif ($attendanceActivity->module_auto_type == GRADEBOOK_ACTIVITY_ASSIGNMENT) {
            $grd = Database::get()->querySingle("SELECT * FROM assignment_submit WHERE assignment_id = ?d AND uid = $uid", $attendanceActivity->module_auto_id);
            if ($grd) {
                $allow_insert = TRUE;
            }
        }
        if (isset($allow_insert) && $allow_insert) {
            update_attendance_book($uid, $attendanceActivity->module_auto_id, $attendanceActivity->module_auto_type, $attendance_id);
        }
        unset($allow_insert);
    }
}

/**
 * @brief create new attendance
 * @global string $tool_content
 * @global type $course_code
 * @global type $langNewAttendance2
 * @global type $langTitle
 * @global type $langSave
 * @global type $langInsert
 */
function new_attendance() {

    global $tool_content, $course_code, $langNewAttendance2, $head_content,
           $langTitle, $langSave, $langInsert, $langAttendanceLimitNumber,
           $attendance_limit, $langStart, $langEnd, $language, $urlAppend;

    load_js('bootstrap-datetimepicker');
    $head_content .= "
    <script type='text/javascript'>
        $(function() {
            $('#start_date, #end_date').datetimepicker({
                format: 'dd-mm-yyyy hh:ii',
                pickerPosition: 'bottom-right',
                language: '".$language."',
                autoclose: true
            });
        });
    </script>";
    $title_error = Session::getError('title');
    $title = Session::has('title') ? Session::get('title') : '';
    $start_date_error = Session::getError('start_date');
    $start_date = Session::has('start_date') ? Session::get('start_date') : '';
    $end_date_error = Session::getError('end_date');
    $end_date = Session::has('end_date') ? Session::get('end_date') : '';
    $limit_error  = Session::getError('limit');
    $limit = Session::has('limit') ? Session::get('limit') : '';

    $tool_content .= "
    <div class='d-lg-flex gap-4 mt-4'>  
        <div class='flex-grow-1'>
            <div class='form-wrapper form-edit rounded border-0 px-0'>
                <form class='form-horizontal' role='form' method='post' action='$_SERVER[SCRIPT_NAME]?course=$course_code' onsubmit=\"return checkrequired(this, 'antitle');\">
                    <div class='form-group'>
                    <label class='col-12 control-label-notes'>$langNewAttendance2</label></div>
                    <div class='form-group".($title_error ? " has-error" : "")."'>
                        <div class='col-12'>
                            <input class='form-control' type='text' placeholder='$langTitle' name='title'>
                            <span class='help-block'>$title_error</span>
                        </div>
                    </div>
                   
                        
                            <div class='form-group mt-4".($start_date_error ? " has-error" : "")."'>
                                <div class='col-12'>
                                    <label class='control-label-notes'>$langStart</label>
                                </div>
                                <div class='col-12'>
                                    <input class='form-control' placeholder='$langStart' type='text' name='start_date' id='start_date' value='$start_date'>
                                    <span class='help-block'>$start_date_error</span>
                                </div>
                            </div>
                        
                      
                            <div class='form-group mt-4".($end_date_error ? " has-error" : "")."'>
                                <div class='col-12'>
                                    <label class='control-label-notes'>$langEnd</label>
                                </div>
                                <div class='col-12'>
                                    <input class='form-control' placeholder='$langEnd' type='text' name='end_date' id='end_date' value='$end_date'>
                                    <span class='help-block'>$end_date_error</span>
                                </div>
                            </div>
                       
                   
                    <div class='form-group mt-4".($limit_error ? " has-error" : "")."'>
                        <label class='col-12 control-label-notes'>$langAttendanceLimitNumber</label>
                        <div class='col-sm-12'>
                            <input class='form-control' placeholder='$langAttendanceLimitNumber' type='text' name='limit' value='$attendance_limit'>
                            <span class='help-block'>$limit_error</span>
                        </div>
                    </div>
                    <div class='form-group mt-5 d-flex justify-content-end align-items-center'>
                        "

                                    .form_buttons(array(
                                        array(
                                                'class'=> 'submitAdminBtn',
                                                'text' => $langSave,
                                                'name' => 'newAttendance',
                                                'value'=> $langInsert
                                        ),
                                            array(
                                                'class'=> 'cancelAdminBtn ms-1',
                                                'href' => "$_SERVER[SCRIPT_NAME]?course=$course_code"
                                                )
                                        )).



                        "
                    </div>
                </form>
            </div>
        </div>
        <div class='d-none d-lg-block'>
            <img class='form-image-modules' src='".get_form_image()."' alt='form-image'>
        </div>
    </div>";
}

/**
 * @brief display user presences
 * @param int $attendance_id
 */
function display_user_presences($attendance_id) {

    global $course_code, $tool_content,
           $langTitle, $langType, $langAttendanceNewBookRecord, $langDate,
           $langAttendanceNoActMessage1, $langAttendanceActAttend, $langAttendanceActCour,
           $langAttendanceInsAut, $langAttendanceInsMan, $langAttendanceBooking;

        $userID = intval($_GET['book']); //user
        //check if there are booking records for the user, otherwise alert message for first input
        $checkForRecords = Database::get()->querySingle("SELECT COUNT(attendance_book.id) AS count FROM attendance_book, attendance_activities
                            WHERE attendance_book.attendance_activity_id = attendance_activities.id
                            AND uid = ?d AND attendance_activities.attendance_id = ?d", $userID, $attendance_id)->count;
        if(!$checkForRecords) {
            $tool_content .="<div class='col-sm-12'><div class='alert alert-success'><i class='fa-solid fa-circle-check fa-lg'></i><span>$langAttendanceNewBookRecord</span></div></div>";
        }

        //get all the activities
        $result = Database::get()->queryArray("SELECT * FROM attendance_activities WHERE attendance_id = ?d  ORDER BY `DATE` DESC", $attendance_id);
        $actNumber = count($result);
        if ($actNumber > 0) {
            $tool_content .= "<h5>". display_user($userID) ."</h5>";
            $tool_content .= "<form method='post' action='$_SERVER[SCRIPT_NAME]?course=$course_code&amp;attendance_id=$attendance_id&amp;book=" . $userID . "' onsubmit=\"return checkrequired(this, 'antitle');\">
                              <div class='table-responsive'><table class='table-default'>";
            $tool_content .= "<thead><tr class='list-header'><th>$langTitle</th><th>$langDate</th><th>$langType</th>";
            $tool_content .= "<th>$langAttendanceBooking</th>";
            $tool_content .= "</tr></thead>";
        } else {
            $tool_content .= "
            <div class='col-12'>
            <div class='alert alert-warning'><i class='fa-solid fa-triangle-exclamation fa-lg'></i><span>$langAttendanceNoActMessage1</span></div></div>";
        }

        if ($result) {
            foreach ($result as $activity) {
                //check if there is auto mechanism
                if($activity->auto == 1) {
                    if($activity->module_auto_type) { //assignments, exercises, lp(scorms)
                        $userAttend = attendForAutoActivities($userID, $activity->module_auto_id, $activity->module_auto_type);
                        if ($userAttend == 0) {
                            $q = Database::get()->querySingle("SELECT attend FROM attendance_book WHERE attendance_activity_id = ?d AND uid = ?d", $activity->id, $userID);
                            if ($q) {
                                $userAttend = $q->attend;
                            }
                        }
                    }
                } else {
                    $q = Database::get()->querySingle("SELECT attend FROM attendance_book
                                                        WHERE attendance_activity_id = ?d AND uid = ?d", $activity->id, $userID);
                    if ($q) {
                        $userAttend = $q->attend;
                    } else {
                        $userAttend = 0;
                    }
                }
                $tool_content .= "<tr><td>";

                if (!empty($activity->title)) {
                    $tool_content .= q($activity->title);
                }

                $tool_content .= "</td>";
                if($activity->date){
                    $tool_content .= "<td><div class='smaller'><span class='day'>" . format_locale_date(strtotime($activity->date), 'short', false) . "</div></td>";
                } else {
                    $tool_content .= "<td>-</td>";
                }
                if ($activity->module_auto_id) {
                    $tool_content .= "<td class='smaller'>$langAttendanceActCour";
                    if ($activity->auto) {
                        $tool_content .= "<br>($langAttendanceInsAut)";
                    } else {
                        $tool_content .= "<br>($langAttendanceInsMan)";
                    }
                    $tool_content .= "</td>";
                } else {
                    $tool_content .= "<td class='smaller'>$langAttendanceActAttend</td>";
                }
                $tool_content .= "<td>
                <label class='label-container'><input type='checkbox' value='1' name='" . $activity->id . "'";
                if(isset($userAttend) && $userAttend) {
                    $tool_content .= " checked";
                }
                $tool_content .= "><span class='checkmark'></span></label>
                <input type='hidden' value='" . $userID . "' name='userID'>
                </td></tr>";
            } // end of while
        }
        $tool_content .= "</table></div>";
        $tool_content .= "<div class='float-end mt-3'><input class='btn submitAdminBtn' type='submit' name='bookUser' value='$langAttendanceBooking'></div>";
}


/**
 * @brief display all users presences
 * @param int $attendance_id
 */
function display_all_users_presences($attendance_id) {

    global $course_id, $course_code, $tool_content, $langName, $langSurname,
           $langID, $langAmShort, $langRegistrationDateShort, $langAttendanceAbsences,
           $langAttendanceBook, $langAttendanceDelete, $langConfirmDelete,
           $langNoStudentsInAttendance, $langHere, $is_editor;

    $attendance_limit = get_attendance_limit($attendance_id);

    $resultUsers = Database::get()->queryArray("SELECT attendance_users.id as recID,
                                                    attendance_users.uid AS userID, user.surname AS surname,
                                                    user.givenname AS name, user.am AS am,
                                                    DATE(course_user.reg_date) AS reg_date
                                                FROM attendance_users
                                                JOIN user ON attendance_users.uid = user.id AND attendance_id = ?d
                                                LEFT JOIN course_user ON user.id = course_user.user_id
                                                    AND `course_user`.`course_id` = ?d
                                                ORDER BY surname, name", $attendance_id, $course_id);
    if (count($resultUsers)) {
        //table to display the users
        $tool_content .= "<table id='users_table{$course_id}' class='table-default custom_list_order'>
            <thead class='list-header'>
                <tr>
                  <th class='count-col'>$langID</th>
                  <th>$langName $langSurname</th>
                  <th>$langAmShort</th>
                  <th>$langRegistrationDateShort</th>
                  <th>$langAttendanceAbsences</th>
                  <th></th>
                </tr>
            </thead>
            <tbody>";
        $cnt = 0;
        foreach ($resultUsers as $resultUser) {
            $classvis = '';
            if (is_null($resultUser->reg_date)) {
                $classvis = 'not_visible';
            }
            $cnt++;
            $tool_content .= "<tr class='$classvis'>
                <td class='count-col'>$cnt</td>
                <td>" . display_user($resultUser->userID) . "</td>
                <td>$resultUser->am</td>
                <td>";
            if (!is_null($resultUser->reg_date)) {
                $tool_content .= format_locale_date(strtotime($resultUser->reg_date), 'short', false);
            } else {
                $tool_content .= "";
            }
            $tool_content .= "</td>
                <td>" . userAttendTotal($attendance_id, $resultUser->userID) . "/" . $attendance_limit . "</td>";
                if ($is_editor) {
                        $tool_content .= "<td class='option-btn-cell text-end'>"
                            . action_button(array(
                                array('title' => $langAttendanceBook,
                                    'icon' => 'fa-plus',
                                    'url' => "$_SERVER[SCRIPT_NAME]?course=$course_code&amp;attendance_id=$attendance_id&amp;book=" . $resultUser->userID),
                                array('title' => $langAttendanceDelete,
                                    'icon' => 'fa-xmark',
                                    'url' => "$_SERVER[SCRIPT_NAME]?course=$course_code&amp;at=$attendance_id&amp;ruid=$resultUser->userID&amp;deleteuser=yes",
                                    'confirm' => $langConfirmDelete,
                                    'class' => 'delete'))) . "</td>";
                }
            }
            $tool_content .= "</tr>";
            $tool_content .= "</tbody></table>";
        } else {
            $tool_content .= "
            <div class='col-12'><div class='alert alert-warning'><i class='fa-solid fa-triangle-exclamation fa-lg'></i><span>$langNoStudentsInAttendance <a href='$_SERVER[SCRIPT_NAME]?course=$course_code&amp;attendance_id=$attendance_id&amp;editUsers=1'>$langHere</a>.</span></div></div>";
    }
}

/**
 * @brief insert/modify attendance settings
 * @param int $attendance_id
 */
function attendance_settings($attendance_id) {

    global $tool_content, $course_code, $language,
           $langTitle, $langAttendanceLimitNumber,
           $langAttendanceUpdate, $langSave, $head_content,
           $attendance, $langStart, $langEnd, $urlAppend;

    load_js('bootstrap-datetimepicker');
    $head_content .= "
    <script type='text/javascript'>
        $(function() {
            $('#start_date, #end_date').datetimepicker({
                format: 'dd-mm-yyyy hh:ii',
                pickerPosition: 'bottom-right',
                language: '".$language."',
                autoclose: true
            });
        });
    </script>";
    $title_error = Session::getError('title');
    $title = Session::has('title') ? Session::get('title') : $attendance->title;
    $start_date_error = Session::getError('start_date');
    $start_date = Session::has('start_date') ? Session::get('start_date') : DateTime::createFromFormat('Y-m-d H:i:s', $attendance->start_date)->format('d-m-Y H:i');
    $end_date_error = Session::getError('end_date');
    $end_date = Session::has('end_date') ? Session::get('end_date') : DateTime::createFromFormat('Y-m-d H:i:s', $attendance->end_date)->format('d-m-Y H:i');
    $limit_error  = Session::getError('limit');
    $limit = Session::has('limit') ? Session::get('limit') : get_attendance_limit($attendance_id);
    // update attendance title
    $tool_content .= "

    <div class='d-lg-flex gap-4 mt-4'>
        <div class='flex-grow-1'>
            <div class='form-wrapper form-edit rounded border-0 px-0'>
                <form class='form-horizontal' role='form' method='post' action='$_SERVER[SCRIPT_NAME]?course=$course_code&attendance_id=$attendance_id'>
                    <div class='form-group".($title_error ? " has-error" : "")."'>
                        <label class='col-12 control-label-notes'>$langTitle</label>
                        <div class='col-12'>
                            <input class='form-control' type='text' placeholder='$langTitle' name='title' value='".q($title)."'>
                            <span class='help-block'>$title_error</span>
                        </div>
                    </div>
                    
                       
                            <div class='form-group".($start_date_error ? " has-error" : "")." mt-4'>
                                <div class='col-12'>
                                    <label class='control-label-notes'>$langStart</label>
                                </div>
                                <div class='col-12'>
                                    <input class='form-control' type='text' name='start_date' id='start_date' value='$start_date'>
                                    <span class='help-block'>$start_date_error</span>
                                </div>
                            </div>
                        
                       
                            <div class='form-group".($end_date_error ? " has-error" : "")." mt-4'>
                                <div class='col-12'>
                                    <label class='control-label-notes'>$langEnd</label>
                                </div>
                                <div class='col-12'>
                                    <input class='form-control' type='text' name='end_date' id='end_date' value='$end_date'>
                                    <span class='help-block'>$end_date_error</span>
                                </div>
                            </div>
                        
                    
                    <div class='form-group".($limit_error ? " has-error" : "")." mt-4'>
                        <label class='col-12 control-label-notes'>$langAttendanceLimitNumber:</label>
                        <div class='col-sm-12'>
                            <input class='form-control' type='text' name='limit' value='$limit'/>
                            <span class='help-block'>$limit_error</span>
                        </div>
                    </div>
                    <div class='form-group mt-5 d-flex justify-content-end align-items-center'>



                                 ".form_buttons(array(
                                    array(
                                        'class' => 'submitAdminBtn',
                                        'text' => $langSave,
                                        'name' => 'submitAttendanceBookSettings',
                                        'value'=> $langAttendanceUpdate
                                    ),
                                    array(
                                        'class' => 'cancelAdminBtn ms-1',
                                        'href' => "$_SERVER[SCRIPT_NAME]?course=$course_code&amp;attendance_id=$attendance_id"
                                    )
                                ))."




                    </div>
                    </fieldset>
                </form>
            </div>
        </div>
        <div class='d-none d-lg-block'>
            <img class='form-image-modules' src='".get_form_image()."' alt='form-image'>
        </div>
    </div>";
}

/**
 * @brief modify user attendance settings
 * @param int $attendance_id
 */
function user_attendance_settings($attendance_id) {

    global $tool_content, $course_code, $langGroups, $language,
           $langAttendanceUpdate, $langAttendanceInfoForUsers,
           $langRegistrationDate, $langFrom2, $langTill, $langRefreshList,
           $langUserDuration, $langGradebookAllBetweenRegDates, $langSpecificUsers, $head_content,
           $langStudents, $langMove, $langParticipate, $attendance, $urlAppend;

    load_js('bootstrap-datetimepicker');
    $head_content .= "
    <script type='text/javascript'>
        $(function() {
            $('#UsersStart, #UsersEnd').datetimepicker({
                format: 'dd-mm-yyyy hh:ii',
                pickerPosition: 'bottom-right',
                language: '".$language."',
                autoclose: true
            });
        });
    </script>";

    // default values
    $start_date = DateTime::createFromFormat('Y-m-d H:i:s', $attendance->start_date)->format('d-m-Y H:i');
    $end_date = DateTime::createFromFormat('Y-m-d H:i:s', $attendance->end_date)->format('d-m-Y H:i');
    $tool_content .= "

<div class='col-12'>
    <div class='d-lg-flex gap-4 mt-4'>
        <div class='flex-grow-1'>
            <div class='form-wrapper form-edit rounded border-0 px-0'>
                <form class='form-horizontal' role='form' method='post' action='$_SERVER[SCRIPT_NAME]?course=$course_code&attendance_id=$attendance_id&editUsers=1'>
                    <div class='form-group'>
                        <label class='col-12'><span class='help-block'>$langAttendanceInfoForUsers</span></label>
                    </div>
                    <div class='form-group mt-4'>
                    <label class='col-sm-6 control-label-notes mb-2'>$langUserDuration</label>
                        <div class='col-sm-12'>
                            <div class='radio mb-2'>
                              <label>
                                <input type='radio' id='button_some_users' name='specific_attendance_users' value='1'>
                                <span id='button_some_users_text'>$langSpecificUsers</span>
                              </label>
                            </div>
                            <div class='radio mb-2'>
                              <label>
                                <input type='radio' id='button_groups' name='specific_attendance_users' value='2'>
                                <span id='button_groups_text'>$langGroups</span>
                              </label>
                            </div>
                            <div class='radio'>
                              <label>
                                <input type='radio' id='button_all_users' name='specific_attendance_users' value='0' checked>
                                <span id='button_all_users_text'>$langGradebookAllBetweenRegDates</span>
                              </label>
                            </div>
                        </div>
                    </div>
                    <div class='form-group mt-3' id='all_users'>
                        <div class='input-append date form-group' id='startdatepicker'>
                            <label for='UsersStart' class='col-sm-6 control-label-notes'>$langRegistrationDate $langFrom2:</label>

                                <div class='input-group'>
                                    <span class='add-on input-group-text h-40px bg-input-default input-border-color border-end-0'><i class='fa-regular fa-calendar'></i></span>
                                    <input class='form-control mt-0 border-start-0' name='UsersStart' id='UsersStart' type='text' value='$start_date'>

                                    
                                </div>


                        </div>

                        <div class='input-append date form-group mt-4' id='enddatepicker'>
                            <label for='UsersEnd' class='col-sm-6 control-label-notes'>$langTill</label>

                                <div class='input-group'>
                                    <span class='add-on input-group-text h-40px bg-input-default input-border-color border-end-0'><i class='fa-regular fa-calendar'></i></span>
                                    <input class='form-control mt-0 border-start-0' name='UsersEnd' id='UsersEnd' type='text' value='$end_date'>

                                    
                                </div>


                        </div>
                    </div>
                    <div class='form-group mt-4'>
                        <div class='col-sm-12 col-sm-offset-2'>
                            <div class='table-responsive'>
                                <table id='participants_tbl' class='table-default hide'>
                                    <thead>
                                        <tr class='title1 list-header'>
                                        <td id='users' class='form-label'>$langStudents</td>
                                        <td class='text-center form-label'>$langMove</td>
                                        <td class='form-label'>$langParticipate</td>
                                        </tr>
                                    </thead>
                                    <tr>
                                      <td>
                                        <select class='form-select h-100 rounded-0' id='users_box' size='10' multiple></select>
                                      </td>
                                      <td>
                                        <div class='d-flex align-items-center flex-column gap-2'>
                                            <input class='btn submitAdminBtn submitAdminBtnClassic rounded-2 h-40px' type='button' onClick=\"move('users_box','participants_box')\" value='   &gt;&gt;   ' /><br />
                                            <input class='btn submitAdminBtn submitAdminBtnClassic rounded-2 h-40px mt-2' type='button' onClick=\"move('participants_box','users_box')\" value='   &lt;&lt;   ' />
                                        </div>
                                      </td>
                                      <td width='40%'>
                                        <select class='form-select h-100 rounded-0' id='participants_box' name='specific[]' size='10' multiple></select>
                                      </td>
                                    </tr>
                                </table>
                            </div>
                        </div>
                    </div>
                    <div class='form-group mt-5'>
                        <div class='col-12 d-flex justify-content-end align-items-center'>".form_buttons(array(
                        array(
                            'class' => 'submitAdminBtn',
                            'text' => $langRefreshList,
                            'name' => 'resetAttendanceUsers',
                            'value'=> $langAttendanceUpdate,
                            'javascript' => "selectAll('participants_box',true)"
                        ),
                        array(
                            'class' => 'cancelAdminBtn ms-1',
                            'href' => "$_SERVER[SCRIPT_NAME]?course=$course_code&amp;attendance_id=$attendance_id&amp;attendanceBook=1"
                        )
                    ))."</div>
                    </div>
                </form>
            </div>
        </div>
        <div class='d-none d-lg-block'>
            <img class='form-image-modules' src='".get_form_image()."' alt='form-image'>
        </div>
    </div>
</div>";

}

/**
 * @brief display user presences (student view)
 * @param int $attendance_id
 */
function student_view_attendance($attendance_id) {

    global $tool_content, $uid, $langAttendanceAbsencesNo, $langAttendanceAbsencesFrom,
           $langAttendanceAbsencesFrom2, $langAttendanceStudentFailure,
           $langTitle, $langDate, $langDescription,
           $langAttendanceAbsencesYes, $langBack, $course_code;

    $attendance_limit = get_attendance_limit($attendance_id);
    //check if there are attendance records for the user, otherwise alert message that there is no input
    $checkForRecords = Database::get()->querySingle("SELECT COUNT(attendance_book.id) AS count
                                            FROM attendance_book, attendance_activities
                                        WHERE attendance_book.attendance_activity_id = attendance_activities.id
                                            AND uid = ?d
                                            AND attendance_activities.attendance_id = ?d", $uid, $attendance_id)->count;

    if (!$checkForRecords) {
        $tool_content .="
        <div class='col-12'><div class='alert alert-warning'><i class='fa-solid fa-triangle-exclamation fa-lg'></i><span>$langAttendanceStudentFailure</span></div></div>";
    }

    $result = Database::get()->queryArray("SELECT * FROM attendance_activities WHERE attendance_id = ?d ORDER BY `DATE` DESC", $attendance_id);
    $results = count($result);

    if ($results > 0) {
        if ($checkForRecords) {
            $range = Database::get()->querySingle("SELECT `limit` FROM attendance WHERE id = ?d", $attendance_id)->limit;
            $tool_content .= "
            <div class='col-12'><div class='alert alert-info'><i class='fa-solid fa-circle-info fa-lg'></i><span>" . userAttendTotal($attendance_id, $uid) ." ". $langAttendanceAbsencesFrom . " ". q($attendance_limit) . " " . $langAttendanceAbsencesFrom2. " </span></div></div>";
        }

        $tool_content .= " <div class='col-12'>
        <div class='table-responsive'><table class='table-default'>";
        $tool_content .= "<thead><tr class='list-header'><th style='width:30%;'>$langTitle</th>
                              <th style='width:15%;'>$langDate</th>
                              <th style='width:45%;'>$langDescription</th>
                              <th style='width:10%;' class='text-center'>$langAttendanceAbsencesYes</th>
                          </tr></thead>";
    }
    if ($result) {
        foreach ($result as $details) {
            $content = standard_text_escape($details->description);
            $tool_content .= "<tr><td>";
            if (!empty($details->title)) {
                $tool_content .= q($details->title);
            }

            $tool_content .= "</td>"
                    . "<td><div class='smaller'>" . format_locale_date(strtotime($details->date), 'short', false) . "</div></td>"
                    . "<td>" . $content . "</td>";
            $tool_content .= "<td class='text-center'>";
            //check user grade for this activity
            $sql = Database::get()->querySingle("SELECT attend FROM attendance_book
                                                            WHERE attendance_activity_id = ?d
                                                                AND uid = ?d", $details->id, $uid);
            if ($sql) {
                $attend = $sql->attend;
                if ($attend) {
                    $tool_content .= icon('fa-check-circle', $langAttendanceAbsencesYes);
                } else {
                    $auto_activity = Database::get()->querySingle("SELECT auto FROM attendance_activities WHERE id = ?d", $details->id)->auto;
                    if (!$auto_activity and ($details->date > date("Y-m-d"))) {
                        $tool_content .= icon('fa-question-circle', $langAttendanceStudentFailure);
                    } else {
                        $tool_content .= icon('fa-times-circle', $langAttendanceAbsencesNo);
                    }
                }
            } else {
                $tool_content .= icon('fa-question-circle', $langAttendanceStudentFailure);
            }
            $tool_content .= "</td></tr>";
        } // end of while
    }
    $tool_content .= "</table></div></div>";
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
function userAttendTotalActivityStats ($activityID, $participantsNumber, $attendance_id){

    $sumAtt = 0;
    $userAttTotalActivity = Database::get()->queryArray("SELECT attend, attendance_book.uid FROM attendance_book, attendance_users
                                                            WHERE attendance_activity_id = ?d
                                                        AND attendance_users.uid=attendance_book.uid
                                                        AND attendance_users.attendance_id=?d", $activityID, $attendance_id);
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


/**
 * @brief check for attend in auto activities
 * @param type $userID
 * @param type $exeID
 * @param type $exeType
 * @return int
 */
function attendForAutoActivities($userID, $exeID, $exeType) {

    if ($exeType == 1) { //asignments: valid submission!
       $autoAttend = Database::get()->querySingle("SELECT COUNT(id) AS count FROM assignment_submit
                                    WHERE uid = ?d AND assignment_id = ?d", $userID, $exeID)->count;
       if ($autoAttend) {
           return 1;
       } else {
           return 0;
       }
    }
    if ($exeType == 2) { //exercises: valid submission!
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
 * @brief update attendance about user activities
 * @param type $id
 * @param type $activity
 * @return type
 */
function update_attendance_book($uid, $id, $activity, $attendance_id = 0) {
    $params = [$activity, $id];
    $sql = "SELECT attendance_activities.id, attendance_activities.attendance_id, attendance.course_id
                            FROM attendance_activities, attendance
                            WHERE attendance.start_date < NOW()
                            AND attendance.end_date > NOW()
                            AND attendance_activities.module_auto_type = ?d
                            AND attendance_activities.module_auto_id = ?d
                            AND attendance_activities.auto = 1
                            AND attendance_activities.attendance_id = attendance.id
                            AND attendance_activities.attendance_id ";
    if ($attendance_id) {
        $sql .= "= ?d";
        array_push($params, $attendance_id);
    } else {
        $sql .= "IN (
                    SELECT attendance_id
                    FROM attendance_users
                    WHERE uid = ?d)";
        array_push($params, $uid);
    }
    // This query gets the attendance activities that:
    // 1) belong to attendancebooks (or specific attendancebook if $attendance_id != 0)
    // withing the date constraints
    // 2) of a specifc module and have grade auto-submission enabled
    // 3) attended by a specifc user
    $attendanceActivities = Database::get()->queryArray($sql, $params);

    foreach ($attendanceActivities as $attendanceActivity) {
            $attendance_book = Database::get()->querySingle("SELECT attend FROM attendance_book WHERE attendance_activity_id = $attendanceActivity->id AND uid = ?d", $uid);
            if(!$attendance_book) {
                Database::get()->query("INSERT INTO attendance_book SET attendance_activity_id = $attendanceActivity->id, uid = ?d, attend = 1, comments = ''", $uid);
                triggerAttendanceGame($attendanceActivity->course_id, $uid, $attendanceActivity->attendance_id, AttendanceEvent::UPDATE);
            }

    }

    return;
}

/**
 * @brief delete attendance
 * @global type $course_id
 * @global type $langAttendanceDeleted
 * @param type $attendance_id
 */
function delete_attendance($attendance_id) {

    global $course_id, $langAttendanceDeleted, $langAttendanceDelFailure;

    $r = Database::get()->queryArray("SELECT id FROM attendance_activities WHERE attendance_id = ?d", $attendance_id);
    foreach ($r as $act) {
        delete_attendance_activity($attendance_id, $act->id);
    }
    Database::get()->query("DELETE FROM attendance_users WHERE attendance_id = ?d", $attendance_id);
    $action = Database::get()->query("DELETE FROM attendance WHERE id = ?d AND course_id = ?d", $attendance_id, $course_id);
    if ($action) {
        //Session::Messages("$langAttendanceDeleted", "alert-success");
        Session::flash('message',$langAttendanceDeleted);
        Session::flash('alert-class', 'alert-success');
    } else {
        //Session::Messages("$langAttendanceDelFailure", "alert-danger");
        Session::flash('message',$langAttendanceDelFailure);
        Session::flash('alert-class', 'alert-danger');
    }
}

/**
 * @brief delete attendance activity
 * @global type $langAttendanceDel
 * @global type $langAttendanceDelFailure
 * @param type $attendance_id
 * @param type $activity_id
 */
function delete_attendance_activity($attendance_id, $activity_id) {

    global $langAttendanceDel, $langAttendanceDelFailure;

    $delAct = Database::get()->query("DELETE FROM attendance_activities WHERE id = ?d AND attendance_id = ?d", $activity_id, $attendance_id)->affectedRows;
    Database::get()->query("DELETE FROM attendance_book WHERE attendance_activity_id = ?d", $activity_id)->affectedRows;
    if($delAct) {
        //Session::Messages("$langAttendanceDel", "alert-success");
        Session::flash('message',$langAttendanceDel);
        Session::flash('alert-class', 'alert-success');
    } else {
        //Session::Messages("$langAttendanceDelFailure", "alert-danger");
        Session::flash('message',$langAttendanceDelFailure);
        Session::flash('alert-class', 'alert-danger');
    }
}


/**
 * @brief delete user from attendance
 * @global type $langGradebookEdit
 * @param type $attendance_id
 * @param type $userid
 */
function delete_attendance_user($attendance_id, $userid) {

    global $langGradebookEdit;

    Database::get()->query("DELETE FROM attendance_book WHERE uid = ?d AND attendance_activity_id IN
                                (SELECT id FROM attendance_activities WHERE attendance_id = ?d)", $userid, $attendance_id);
    Database::get()->query("DELETE FROM attendance_users WHERE uid = ?d AND attendance_id = ?d", $userid, $attendance_id);
    //Session::Messages($langGradebookEdit,"alert-success");
    Session::flash('message',$langGradebookEdit);
    Session::flash('alert-class', 'alert-success');
}


/**
 * @brief clone attendance
 * @global type $course_id
 * @param type $attendance_id*
 */
function clone_attendance($attendance_id) {

    global $course_id, $langCopyDuplicate;
    $attendance = Database::get()->querySingle("SELECT * FROM attendance WHERE id = ?d", $attendance_id);
    $newTitle = $attendance->title.' '.$langCopyDuplicate;
    $new_attendance_id = Database::get()->query("INSERT INTO attendance SET course_id = ?d,
                                                      students_semester = 1, `limit` = ?d,
                                                      active = 1, title = ?s, start_date = ?t, end_date = ?t", $course_id, $attendance->limit, $newTitle, $attendance->start_date, $attendance->end_date)->lastInsertID;
    Database::get()->query("INSERT INTO attendance_activities (attendance_id, title, date, description, module_auto_id, module_auto_type, auto)
                                SELECT $new_attendance_id, title, " . DBHelper::timeAfter() . ", description, module_auto_id, module_auto_type, auto
                                 FROM attendance_activities WHERE attendance_id = ?d", $attendance_id);
}

/**
 * @brief get attendance title
 * @param type $attendance_id
 * @return type
 */
function get_attendance_title($attendance_id) {

    $at_title = Database::get()->querySingle("SELECT title FROM attendance WHERE id = ?d", $attendance_id)->title;

    return $at_title;
}


/**
 * @brief get activity title from an attendance
 * @param type $attendance_id
 * @param type $activity_id
 * @return type
 */
function get_attendance_activity_title($attendance_id, $activity_id) {

    $at_act_title = Database::get()->querySingle("SELECT title FROM attendance_activities
                    WHERE id = ?d AND attendance_id = ?d", $activity_id, $attendance_id)->title;

    return $at_act_title;
}

/**
 * @brief get attendance limit
 * @param type $attendance_id
 * @return type
 */
function get_attendance_limit($attendance_id) {

    $at_limit = Database::get()->querySingle("SELECT `limit` FROM attendance WHERE id = ?d", $attendance_id)->limit;

    return $at_limit;

}

function triggerAttendanceGame($courseId, $uid, $attendanceId, $eventName) {
    $eventData = new stdClass();
    $eventData->courseId = $courseId;
    $eventData->uid = $uid;
    $eventData->activityType = AttendanceEvent::ACTIVITY;
    $eventData->module = MODULE_ID_ATTENDANCE;
    $eventData->resource = intval($attendanceId);
    AttendanceEvent::trigger($eventName, $eventData);
}
