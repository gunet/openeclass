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

/**
 * @file refresh_course.php 
 * @brief course clean up
 */

$require_current_course = TRUE;
$require_course_admin = TRUE;
$require_login = TRUE;

require_once '../../include/baseTheme.php';
require_once 'modules/work/work_functions.php';
require_once 'include/lib/fileManageLib.inc.php';

load_js('bootstrap-datepicker');

$head_content .= "
<script type='text/javascript'>
$(function() {
$('#reg_date').datepicker({
        format: 'dd-mm-yyyy',
        language: '".$language."',
        autoclose: true
    });
});
</script>";

$from_user = FALSE;
if (isset($_GET['from_user'])) { // checks if we are coming from /modules/user/index.php
    $from_user = $_GET['from_user'];
}

if (!$from_user) {
    $toolName = $langCourseInfo;
    $pageName = $langRefreshCourse;
    $navigation[] = array('url' => "index.php?course=$course_code", 'name' => $langCourseInfo);
} else {
    $toolName = $langUsers;
    $pageName = $langDelUsers;
    $navigation[] = array('url' => "../user/index.php?course=$course_code", 'name' => $langUsers);        
}

if (isset($_POST['reg_flag'])) {
    $reg_flag = $_POST['reg_flag'];
} else {
    $reg_flag = 'before';
}
if (isset($_POST['submit'])) {
    if (!isset($_POST['token']) || !validate_csrf_token($_POST['token'])) csrf_token_error();
    if (!$from_user) {
        $url = "$_SERVER[SCRIPT_NAME]?course=$course_code";
    } else {
        $url = "../user/index.php?course=$course_code";
    }
    
    $tool_content .= action_bar(array(
            array('title' => $langBack,
                  'url' => $url,
                  'icon' => 'fa-reply',
                  'level' => 'primary-label'
                 )));
       
    $output = array();
    if (isset($_POST['delusers'])) {
        if (isset($_POST['reg_date']) and isset($_POST['reg_flag'])) {
            $date_obj = DateTime::createFromFormat('d-m-Y', $_POST['reg_date']);
            $date = $date_obj->format('Y-m-d');            
            $output[] = delete_users(q($date), $_POST['reg_flag']);
        } else {
            $output[] = delete_users();
        }
    }
    if (isset($_POST['delannounces'])) {
        $output[] = delete_announcements();
    }
    
    if (isset($_POST['delagenda'])) {
        $output[] = delete_agenda();
    }
    if (isset($_POST['hideworks'])) {
        $output[] = hide_work();
    }
    if (isset($_POST['delworkssubs'])) {
	$output[] = del_work_subs();
    }            
    if (isset($_POST['purgeexercises'])) {
        $output[] = purge_exercises();
    }
    if (isset($_POST['clearstats'])) {
        $output[] = clear_stats();
    }

    if (($count_events = count($output)) > 0) {
        $tool_content .= "<div class='alert alert-success'>";
        if (!$from_user) {
            $tool_content .= $langRefreshSuccess;
        }
        $tool_content .= "<ul class='listBullet'>";
        for ($i = 0; $i < $count_events; $i++) {
            $tool_content .= "<li>$output[$i]</li>";
        }
        $tool_content .= "</ul></div>";
    }    
} else {
    
    $tool_content .= action_bar(array(
            array('title' => $langBack,
                  'url' => "#",
                  'class' => 'back_btn',
                  'icon' => 'fa-reply',
                  'level' => 'primary-label'
                 )));
    
    if (!$from_user) {
        $tool_content .= "<div class='alert alert-info'>$langRefreshInfo $langRefreshInfo_A</div>";
    }
    $tool_content .= "<div class='form-wrapper'>";
            if (!$from_user) {
                $tool_content .= "<form class='form-horizontal' role='form' action='$_SERVER[SCRIPT_NAME]?course=$course_code' method='post'>";
            } else {
                $tool_content .= "<form class='form-horizontal' role='form' action='$_SERVER[SCRIPT_NAME]?course=$course_code&amp;from_user=true' method='post'>";
            }
             $tool_content .= "<fieldset>
            <div class='form-group'>
                <label for='delusers' class='col-sm-2 control-label'>$langUsers</label>
                <div class='col-sm-4 checkbox'>
                    <label><input type='checkbox' name='delusers'>$langUserDelCourse:</label>
                </div>
                <div class='col-sm-3'>
                    " . selection(array('before' => $langBefore, 'after' => $langAfter), 'reg_flag', $reg_flag, 'class="form-control"') . "
                </div>
                <div class='col-sm-3'>
                    <input type='text' name='reg_date' id='reg_date' value='" .date("d-m-Y", time()) ."'>
                </div>                
            </div>";
    if (!$from_user) {
            $tool_content .= "<div class='form-group'>
                <label for='delannounces' class='col-sm-2 control-label'>$langAnnouncements</label>
                <div class='col-sm-10 checkbox'><label><input type='checkbox' name='delannounces'>$langAnnouncesDel</label></div>
            </div>
            <div class='form-group'>
              <label for-'delagenda' class='col-sm-2 control-label'>$langAgenda</label>
              <div class='col-sm-10 checkbox'><label><input type='checkbox' name='delagenda'>$langAgendaDel</label></div>
            </div>
            <div class='form-group'>
              <label for='hideworks' class='col-sm-2 control-label'>$langWorks</label>
                <div class='col-sm-10 checkbox'>
                    <label><input type='checkbox' name='hideworks'>$langHideWork</label>
                  </div>
                <div class='col-sm-offset-2 col-sm-10 checkbox'>
                    <label><input type='checkbox' name='delworkssubs'>$langDelAllWorkSubs</label>
                </div>
            </div>
            <div class='form-group'>
              <label for='purgeexercises' class='col-sm-2 control-label'>$langExercises</label>
              <div class='col-sm-10 checkbox'><label><input type='checkbox' name='purgeexercises'>$langPurgeExercisesResults</label></div>
            </div>
            <div class='form-group'>
              <label for='clearstats' class='col-sm-2 control-label'>$langUsage</label>
              <div class='col-sm-10 checkbox'><label><input type='checkbox' name='clearstats'>$langClearStats</label></div>
            </div>";
            }
        $tool_content .= "
            <div class='col-sm-offset-2 col-sm-10'>
            <input class='btn btn-primary' type='submit' value='$langSubmitActions' name='submit'>
            </div>
            </fieldset>
            ". generate_csrf_token_form_field() ."                              
            </form>
            </div>";    
}

draw($tool_content, 2, null, $head_content);

/**
 * 
 * @global type $course_id
 * @global type $langUsersDeleted
 * @param type $date
 * @param type $duration
 * @return type
 */
function delete_users($date = '', $duration = '') {
    global $course_id, $langUsersDeleted;

    if (isset($date)) {
         if ($duration == 'before') {
             $operator = '<';
         } else {
             $operator = '>';
         }
                  
        Database::get()->query("DELETE FROM course_user WHERE course_id = ?d AND
                                status != ". USER_TEACHER ." AND
                                reg_date $operator ?t", $course_id, $date);
    } else {
        Database::get()->query("DELETE FROM course_user WHERE course_id = ?d AND status != " . USER_TEACHER . "", $course_id);
    }
    Database::get()->query("DELETE FROM group_members
                         WHERE group_id IN (SELECT id FROM `group` WHERE course_id = ?d) AND
                               user_id NOT IN (SELECT user_id FROM course_user WHERE course_id = ?d)", $course_id, $course_id);
    
    return "<p>$langUsersDeleted</p>";
}

/**
 * 
 * @global type $course_id
 * @global type $langAnnDeleted
 * @return type
 */
function delete_announcements() {
    global $course_id, $langAnnDeleted;

    Database::get()->query("DELETE FROM announcement WHERE course_id = ?d", $course_id);
    return "<p>$langAnnDeleted</p>";
}

/**
 * 
 * @global type $langAgendaDeleted
 * @global type $course_id
 * @return type
 */
function delete_agenda() {
    global $langAgendaDeleted, $course_id;

    Database::get()->query("DELETE FROM agenda WHERE course_id = ?d", $course_id);
    return "<p>$langAgendaDeleted</p>";
}

/**
 * 
 * @global type $langDocsDeleted
 * @global type $course_id
 * @return type
 */
function hide_doc() {
    global $langDocsDeleted, $course_id;

    Database::get()->query("UPDATE document SET visible=0, public=0 WHERE course_id = ?d", $course_id);
    return "<p>$langDocsDeleted</p>";
}

/**
 * 
 * @global type $langWorksDeleted
 * @global type $course_id
 * @return type
 */
function hide_work() {
    global $langWorksDeleted, $course_id;

    Database::get()->query("UPDATE assignment SET active=0 WHERE course_id = ?d", $course_id);
    return "<p>$langWorksDeleted</p>";
}
/**
 * 
 * @global type $langAllAssignmentSubsDeleted
 * @global type $webDir
 * @global type $course_id
 * @global type $course_code
 * @return type
 */
function del_work_subs()  {
	global $langAllAssignmentSubsDeleted, $webDir, $course_id, $course_code;
        
        $workPath = $webDir."/courses/".$course_code."/work";

        $result = Database::get()->queryArray("SELECT id FROM assignment WHERE course_id = ?d", $course_id);
        
        foreach ($result as $row) {  
            $secret = work_secret($row->id);
            Database::get()->query("DELETE FROM assignment_submit WHERE assignment_id = ?d", $row->id);
            move_dir("$workPath/$secret",
            "$webDir/courses/garbage/${course_code}_work_".$row->id."_$secret");
        }
	return "<p>$langAllAssignmentSubsDeleted</p>";
}

/**
 * 
 * @global type $langPurgeExercisesResults
 * @return type
 */
function purge_exercises() {
    global $langPurgeExercisesResults, $course_id;
    
    Database::get()->query("DELETE FROM exercise_answer_record WHERE eurid IN (
                                SELECT eurid FROM exercise_user_record WHERE eid IN 
                                    (SELECT id FROM exercise WHERE course_id = ?d))", $course_id);
    Database::get()->query("DELETE FROM exercise_user_record WHERE eid IN
                                (SELECT id FROM exercise WHERE course_id = ?d)",$course_id);

    return "<p>$langPurgeExercisesResults</p>";
}

/**
 * 
 * @global type $langStatsCleared
 * @return type
 */
function clear_stats() {
    global $langStatsCleared;

    require_once 'include/action.php';
    $action = new action();
    $action->summarizeAll();

    return "<div class='alert alert-info'>$langStatsCleared</div>";
}
