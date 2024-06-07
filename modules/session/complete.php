<?php

/* ========================================================================
 * Open eClass 4.0
 * E-learning and Course Management System
 * ========================================================================
 * Copyright 2003-2018  Greek Universities Network - GUnet
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
 *  @file manage.php
 *  @brief manage unit completion and/or add unit prerequisites
 */

$require_current_course = true;
$require_consultant = true;
$require_help = true;
$helpTopic = 'ManageCourseSessions';
require_once '../../include/baseTheme.php';
require_once 'include/main_lib.php';
require_once 'modules/tags/moduleElement.class.php';
require_once 'modules/progress/ExerciseEvent.php';
require_once 'modules/progress/process_functions.php';
require_once 'modules/progress/functions.php';
require_once 'functions.php';
require_once 'modules/progress/ExerciseEvent.php';
require_once 'modules/progress/AssignmentEvent.php';
require_once 'modules/progress/AssignmentSubmitEvent.php';
require_once 'modules/progress/CommentEvent.php';
require_once 'modules/progress/BlogEvent.php';
require_once 'modules/progress/WikiEvent.php';
require_once 'modules/progress/ForumEvent.php';
require_once 'modules/progress/ForumTopicEvent.php';
require_once 'modules/progress/LearningPathEvent.php';
require_once 'modules/progress/LearningPathDurationEvent.php';
require_once 'modules/progress/RatingEvent.php';
require_once 'modules/progress/ViewingEvent.php';
require_once 'modules/progress/CourseParticipationEvent.php';
require_once 'modules/progress/GradebookEvent.php';
require_once 'modules/progress/CourseCompletionEvent.php';
require_once 'modules/progress/AttendanceEvent.php';

$toolName = $langManageSession;
$element = "badge";
$display = TRUE;
$localhostUrl = localhostUrl();

if ($is_consultant) {
    if (isset($_GET['course']) and isset($_GET['session'])) {
        if (isset($_GET['session_res_id'])) {
            $session_resource_id = $_GET['session_res_id'];
        } else {
            $session_resource_id = 0;
        }
        $sessionID = $_GET['session'];
        $course_code = $_GET['course'];
        $course_id = course_code_to_id($course_code);
        session_exists($sessionID);
        $sessionTitle = title_session($course_id,$sessionID);
        $pageName = $langSessionCompletion;
        $navigation[] = array('url' => 'index.php?course=' . $course_code, 'name' => $langSession);
        $navigation[] = array('url' => 'session_space.php?course=' . $course_code . "&session=" . $sessionID , 'name' => $sessionTitle);
        $currentSession = Database::get()->querySingle("SELECT * FROM mod_session 
                                                    WHERE course_id = ?d AND id = ?d", $course_id, $sessionID);
    } else {
        redirect_to_home_page("courses/$course_code/");
    }

    if ( isset($_GET['badge_id']) ) {
        $badge_id = $_GET['badge_id'];
    }

    // Top Menu Start
    if ( (isset($_GET['add']) and isset($_GET['act'])) ) {
        $previousUrl = "$_SERVER[SCRIPT_NAME]?course=$course_code&amp;manage=1&amp;session=$sessionID";
        $pageName = $langAdd.' '.$langUnitActivity;
    } elseif ( isset($_GET['act_mod']) ) {
        $previousUrl = "$_SERVER[SCRIPT_NAME]?course=$course_code&amp;manage=1&amp;session=$sessionID";
        $pageName = $currentSession->title;
    } else {
        $previousUrl = $urlAppend . "modules/session/session_space.php?course=$course_code&session=$sessionID";
        $pageName = $currentSession->title;
    }

    if ( $element_id = is_session_completion_enabled($sessionID) ) {
        $element_title = get_cert_title($element, $element_id);
    }

    if ($element_id) {
        $show_completion_button = false;

    } else {
        $show_completion_button = true;
    }

    $tool_content .= action_bar(array(
        array('title' => $langBack,
            'url' => $previousUrl,
            'icon' => 'fa fa-reply ',
            'level' => 'primary',
        ),
        array('title'   =>  $langUnitCompletionActivate,
            'url'   =>  "$_SERVER[SCRIPT_NAME]?course=$course_code&amp;manage=1&amp;session=$sessionID&amp;newuc=1",
            'icon'  =>  'fa fa-navicon',
            'level' =>  'primary-label',
            'show'  =>  $show_completion_button,
        ),
        array('title'   =>  $langUnitCompletionDeactivate,
            'url'   =>  "$_SERVER[SCRIPT_NAME]?course=$course_code&amp;manage=1&amp;session=$sessionID&amp;deluc=1",
            'icon'  =>  'fa fa-navicon',
            'level' =>  'primary-label',
            'show'  =>  !$show_completion_button,
        )

    ));
    //Top Menu End

    $allCourseSessions = Database::get()->queryArray("SELECT * FROM mod_session 
                                                    WHERE course_id = ?d", $course_id);
    if (!$allCourseSessions) {
        Session::flash('message',$langNoSessions);
        Session::flash('alert-class', 'alert-warning');
        redirect_to_home_page("courses/$course_code/");
    }

    if (isset($_POST['mod_cert_activity'])) { // modify certificate activity
        modify_certificate_activity($element, $element_id, $_POST['activity_id']);
        Session::flash('message',"$langQuotaSuccess");
        Session::flash('alert-class', 'alert-success');
        redirect($localhostUrl.$_SERVER['SCRIPT_NAME']."?course=$course_code&manage=1&session=$sessionID");
    }



    // Add resources
     elseif(isset($_POST['add_assignment'])) { // add assignment activity in certificate
        add_assignment_to_certificate($element, $element_id, AssignmentEvent::ACTIVITY);
        Session::flash('message',"$langQuotaSuccess");
        Session::flash('alert-class', 'alert-success');
        redirect($localhostUrl.$_SERVER['SCRIPT_NAME']."?course=$course_code&manage=1&session=$sessionID");
    } elseif (isset($_POST['add_assignment_participation'])){
        add_assignment_to_certificate($element, $element_id, AssignmentSubmitEvent::ACTIVITY);
        Session::flash('message',"$langQuotaSuccess");
        Session::flash('alert-class', 'alert-success');
        redirect($localhostUrl.$_SERVER['SCRIPT_NAME']."?course=$course_code&manage=1&session=$sessionID");
    } elseif (isset($_POST['add_document'])) { // add document activity in certificate
        add_document_to_certificate($element, $element_id);
        Session::flash('message',"$langQuotaSuccess");
        Session::flash('alert-class', 'alert-success');
        redirect($localhostUrl.$_SERVER['SCRIPT_NAME']."?course=$course_code&manage=1&session=$sessionID");
    } elseif (isset($_POST['add_poll'])) { // add poll activity in certificate
        add_poll_to_certificate($element, $element_id);
        Session::flash('message',"$langQuotaSuccess");
        Session::flash('alert-class', 'alert-success');
        redirect($localhostUrl.$_SERVER['SCRIPT_NAME']."?course=$course_code&manage=1&session=$sessionID");
    }    // End add resources





    if ( isset($_GET['manage']) ) {
        if ( isset($_GET['newuc']) ) {
            add_certificate('badge', $langSessionCompletion, '', $langSessionCompletionMessage,'', q(get_config('institution')), 1, -1, null, 0, $sessionID);
            $badge = Database::get()->querySingle("SELECT * FROM badge WHERE course_id = ?d AND session_id=?d", $course_id, $sessionID);
            $element_id = $badge->id;
            $display = FALSE;
            Session::flash('message',"$langSessionCompletionActivated");
            Session::flash('alert-class', 'alert-success');
            redirect($localhostUrl.$_SERVER['SCRIPT_NAME']."?course=$course_code&manage=1&session=$sessionID&badge_id=".$element_id);
        } elseif ( isset($_GET['deluc'])) {
            Session::flash('message',"$langGlossaryDeleted");
            Session::flash('alert-class', 'alert-success');
            $badge = Database::get()->querySingle("SELECT * FROM badge WHERE course_id = ?d AND session_id = ?d", $course_id, $sessionID);
            if (purge_certificate('badge', $badge->id, 0, $sessionID)) {
                Session::flash('message',"$langGlossaryDeleted");
                Session::flash('alert-class', 'alert-success');
            }
            redirect($localhostUrl.$_SERVER['SCRIPT_NAME']."?course=$course_code&manage=1&session=$sessionID");
        } elseif ( isset($_GET['add']) and isset($_GET['act']) ) {
            insert_session_activity($element, $element_id, $_GET['act'], $sessionID, $session_resource_id);
            $display = FALSE;
        } elseif (isset($_GET['act_mod'])) { // modify certificate / badge activity
            display_session_modification_activity($element, $element_id, $_GET['act_mod'], $sessionID);
            $display = FALSE;
        } elseif (isset($_GET['del_cert_res'])) { // delete certificate / badge activity
            if (resource_usage($element, $_GET['del_cert_res'])) { // check if resource has been used by user
                Session::flash('message',"$langUsedCertRes");
                Session::flash('alert-class', 'alert-warning');
                redirect($localhostUrl.$_SERVER['SCRIPT_NAME']."?course=$course_code&manage=1&session=$sessionID");
            } else { // delete it otherwise
                delete_activity($element, $element_id, $_GET['del_cert_res']);
                Session::flash('message',"$langAttendanceDel");
                Session::flash('alert-class', 'alert-success');
                redirect($localhostUrl.$_SERVER['SCRIPT_NAME']."?course=$course_code&manage=1&session=$sessionID");
            }
        }
    } elseif ( isset($_GET['prereq']) ) {
        insert_session_prerequisite_unit($sessionID, $_GET['prereq']);
        redirect($localhostUrl.$_SERVER['SCRIPT_NAME']."?course=$course_code&manage=1&session=$sessionID");
    } elseif ( isset($_GET['del_un_prereq']) ) {
        delete_session_prerequisite($sessionID);
        Session::flash('message',"$langDelUnitPrerequisiteSuccess");
        Session::flash('alert-class', 'alert-success');
        redirect($localhostUrl.$_SERVER['SCRIPT_NAME']."?course=$course_code&manage=1&session=$sessionID");
    } else {
        Session::flash('message',"$langGeneralError");
        Session::flash('alert-class', 'alert-danger');
        redirect($localhostUrl."/courses/$course_code/");
    }
}

if ($show_completion_button) {
    $tool_content .= "<div class='col-sm-12'><div class='text-center alert alert-warning'><i class='fa-solid fa-triangle-exclamation fa-lg'></i><span>$langInvalidCourseSessionPrerequisites</span></div></div>";
}

if (isset($display) and $display == TRUE) {
    if ($is_consultant) {
        if (isset($element_id) and ($element_id != 0)) {
            $pageName = $element_title;
            // display certificate settings and resources
            display_session_activities($element, $element_id, $sessionID);
        }
    }
}

draw($tool_content, 2, null, $head_content);
