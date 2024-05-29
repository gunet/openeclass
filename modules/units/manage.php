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
$require_editor = true;
$require_help = true;
$helpTopic = 'ManageCourseUnits';
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

$toolName = $langManage;
$element = "badge";
$display = TRUE;
$localhostUrl = localhostUrl();

if ($is_editor) {
    if (isset($_GET['course']) and isset($_GET['unit_id'])) {
        if (isset($_GET['unit_res_id'])) {
            $unit_resource_id = $_GET['unit_res_id'];
        } else {
            $unit_resource_id = 0;
        }
        $unit_id = $_GET['unit_id'];
        $course_code = $_GET['course'];
        $currentUnit = Database::get()->querySingle("SELECT * FROM course_units 
                                                    WHERE course_id = ?d AND id = ?d", $course_id, $unit_id);
    } else {
        //Session::Messages($langNoExercises);
        Session::flash('message',$langNoExercises);
        Session::flash('alert-class', 'alert-warning');
        redirect_to_home_page("courses/$course_code/");
    }

    if ( isset($_GET['badge_id']) ) {
        $badge_id = $_GET['badge_id'];
    }

    // Top Menu Start
    if ( (isset($_GET['add']) and isset($_GET['act'])) ) {
        $previousUrl = "$_SERVER[SCRIPT_NAME]?course=$course_code&amp;manage=1&amp;unit_id=$unit_id";
        $pageName = $langAdd.' '.$langUnitActivity;
    } elseif ( isset($_GET['act_mod']) ) {
        $previousUrl = "$_SERVER[SCRIPT_NAME]?course=$course_code&amp;manage=1&amp;unit_id=$unit_id";
        $pageName = $langUnitManage . ' ' . $currentUnit->title;
    } else {
        $previousUrl = "$_SERVER[SCRIPT_NAME]/../index.php?course=$course_code&id=$unit_id";
        $pageName = $langUnitManage . ' ' . $currentUnit->title;
    }

    if ( $element_id = is_unit_completion_enabled($unit_id) ) {
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
            'url'   =>  "$_SERVER[SCRIPT_NAME]?course=$course_code&amp;manage=1&amp;unit_id=$unit_id&amp;newuc=1",
            'icon'  =>  'fa fa-navicon',
            'level' =>  'primary-label',
            'show'  =>  $show_completion_button,
        ),
        array('title'   =>  $langUnitCompletionDeactivate,
            'url'   =>  "$_SERVER[SCRIPT_NAME]?course=$course_code&amp;manage=1&amp;unit_id=$unit_id&amp;deluc=1",
            'icon'  =>  'fa fa-navicon',
            'level' =>  'primary-label',
            'show'  =>  !$show_completion_button,
        )

    ));
    //Top Menu End

    $allCourseUnits = Database::get()->queryArray("SELECT * FROM course_units 
                                                    WHERE course_id = ?d", $course_id);
    if (!$allCourseUnits) {
        Session::flash('message',$langNoUnits);
        Session::flash('alert-class', 'alert-warning');
        redirect_to_home_page("courses/$course_code/");
    }

    if (isset($_POST['mod_cert_activity'])) { // modify certificate activity
        modify_certificate_activity($element, $element_id, $_POST['activity_id']);
        Session::flash('message',"$langQuotaSuccess");
        Session::flash('alert-class', 'alert-success');
        redirect($localhostUrl.$_SERVER['SCRIPT_NAME']."?course=$course_code&manage=1&unit_id=$unit_id");
    }
    // Add resources
    elseif ( isset($_POST['add_exercise']) ) {
        add_exercise_to_certificate($element, $element_id);
        Session::flash('message',"$langQuotaSuccess");
        Session::flash('alert-class', 'alert-success');
        redirect($localhostUrl.$_SERVER['SCRIPT_NAME']."?course=$course_code&manage=1&unit_id=$unit_id");
    } elseif(isset($_POST['add_assignment'])) { // add assignment activity in certificate
        add_assignment_to_certificate($element, $element_id, AssignmentEvent::ACTIVITY);
        Session::flash('message',"$langQuotaSuccess");
        Session::flash('alert-class', 'alert-success');
        redirect($localhostUrl.$_SERVER['SCRIPT_NAME']."?course=$course_code&manage=1&unit_id=$unit_id");
    } elseif (isset($_POST['add_blogcomment'])) {
        add_blogcomment_to_certificate($element, $element_id);
        Session::flash('message',"$langQuotaSuccess");
        Session::flash('alert-class', 'alert-success');
        redirect($localhostUrl.$_SERVER['SCRIPT_NAME']."?course=$course_code&manage=1&unit_id=$unit_id");
    }  elseif (isset($_POST['add_participation'])) {
        add_courseparticipation_to_certificate($element, $element_id);
        Session::flash('message',"$langQuotaSuccess");
        Session::flash('alert-class', 'alert-success');
        redirect($localhostUrl.$_SERVER['SCRIPT_NAME']."?course=$course_code&manage=1&unit_id=$unit_id");
    } elseif (isset($_POST['add_gradebook'])) {
        add_gradebook_to_certificate($element, $element_id);
        Session::flash('message',"$langQuotaSuccess");
        Session::flash('alert-class', 'alert-success');
        redirect($localhostUrl.$_SERVER['SCRIPT_NAME']."?course=$course_code&manage=1&unit_id=$unit_id");
    } elseif (isset($_POST['add_multimedia'])) { // add multimedia activity in certificate
        add_multimedia_to_certificate($element, $element_id);
        Session::flash('message',"$langQuotaSuccess");
        Session::flash('alert-class', 'alert-success');
        redirect($localhostUrl.$_SERVER['SCRIPT_NAME']."?course=$course_code&manage=1&unit_id=$unit_id");
    } elseif (isset($_POST['add_forum'])) { // add forum activity in certificate
        add_forum_to_certificate($element, $element_id);
        Session::flash('message',"$langQuotaSuccess");
        Session::flash('alert-class', 'alert-success');
        redirect($localhostUrl.$_SERVER['SCRIPT_NAME']."?course=$course_code&manage=1&unit_id=$unit_id");
    } elseif (isset($_POST['add_forumtopic'])) { // add forum activity in certificate
        add_forumtopic_to_certificate($element, $element_id);
        Session::flash('message',"$langQuotaSuccess");
        Session::flash('alert-class', 'alert-success');
        redirect($localhostUrl.$_SERVER['SCRIPT_NAME']."?course=$course_code&manage=1&unit_id=$unit_id");
    } elseif (isset($_POST['add_document'])) { // add document activity in certificate
        add_document_to_certificate($element, $element_id);
        Session::flash('message',"$langQuotaSuccess");
        Session::flash('alert-class', 'alert-success');
        redirect($localhostUrl.$_SERVER['SCRIPT_NAME']."?course=$course_code&manage=1&unit_id=$unit_id");
    } elseif (isset($_POST['add_lp'])) { // add learning path activity in certificate
        add_lp_to_certificate($element, $element_id, LearningPathEvent::ACTIVITY);
        Session::flash('message',"$langQuotaSuccess");
        Session::flash('alert-class', 'alert-success');
        redirect($localhostUrl.$_SERVER['SCRIPT_NAME']."?course=$course_code&manage=1&unit_id=$unit_id");
    } elseif (isset($_POST['add_lpduration'])) { // add learning path activity in certificate
        add_lp_to_certificate($element, $element_id, LearningPathDurationEvent::ACTIVITY);
        Session::Messages("$langQuotaSuccess", 'alert-success');
        redirect($localhostUrl.$_SERVER['SCRIPT_NAME']."?course=$course_code&manage=1&unit_id=$unit_id");
    } elseif (isset($_POST['add_poll'])) { // add poll activity in certificate
        add_poll_to_certificate($element, $element_id);
        Session::flash('message',"$langQuotaSuccess");
        Session::flash('alert-class', 'alert-success');
        redirect($localhostUrl.$_SERVER['SCRIPT_NAME']."?course=$course_code&manage=1&unit_id=$unit_id");
    } elseif (isset($_POST['add_ebook'])) { // add ebook activity in certificate
        add_ebook_to_certificate($element, $element_id);
        Session::flash('message',"$langQuotaSuccess");
        Session::flash('alert-class', 'alert-success');
        redirect($localhostUrl.$_SERVER['SCRIPT_NAME']."?course=$course_code&manage=1&unit_id=$unit_id");
    } elseif (isset($_POST['add_wiki'])) { // add wiki activity in certificate
        add_wiki_to_certificate($element, $element_id);
        Session::flash('message',"$langQuotaSuccess");
        Session::flash('alert-class', 'alert-success');
        redirect($localhostUrl.$_SERVER['SCRIPT_NAME']."?course=$course_code&manage=1&unit_id=$unit_id");
    } elseif (isset($_POST['add_coursecompletiongrade'])) {
        add_coursecompletiongrade_to_certificate($element, $element_id);
        Session::flash('message',"$langQuotaSuccess");
        Session::flash('alert-class', 'alert-success');
        redirect_to_home_page("modules/progress/index.php?course=$course_code&$param_name=$element_id");
    }    // End add resources
    if ( isset($_GET['manage']) ) {
        if ( isset($_GET['newuc']) ) {
            add_certificate('badge', $langUnitCompletion, '', $langUnitCompletionMessage,'', q(get_config('institution')), 1, -1, null, $unit_id);
            $badge = Database::get()->querySingle("SELECT * FROM badge WHERE course_id = ?d AND unit_id=?d", $course_id, $unit_id);
            $element_id = $badge->id;
            $display = FALSE;
            Session::flash('message',"$langUnitCompletionActivated");
            Session::flash('alert-class', 'alert-success');
            redirect($localhostUrl.$_SERVER['SCRIPT_NAME']."?course=$course_code&manage=1&unit_id=$unit_id&badge_id=".$element_id);
        } elseif ( isset($_GET['deluc'])) {
            Session::flash('message',"$langGlossaryDeleted");
            Session::flash('alert-class', 'alert-success');
            $badge = Database::get()->querySingle("SELECT * FROM badge WHERE course_id = ?d AND unit_id = ?d", $course_id, $unit_id);
            if (purge_certificate('badge', $badge->id, $unit_id)) {
                delete_unit_prerequisite($unit_id);
                Session::flash('message',"$langGlossaryDeleted");
                Session::flash('alert-class', 'alert-success');
            }
            redirect($localhostUrl.$_SERVER['SCRIPT_NAME']."?course=$course_code&manage=1&unit_id=$unit_id");
        } elseif ( isset($_GET['add']) and isset($_GET['act']) ) {
            insert_activity($element, $element_id, $_GET['act'], $unit_id, $unit_resource_id);
            $display = FALSE;
        } elseif (isset($_GET['act_mod'])) { // modify certificate / badge activity
            display_modification_activity($element, $element_id, $_GET['act_mod'], $unit_id);
            $display = FALSE;
        } elseif (isset($_GET['del_cert_res'])) { // delete certificate / badge activity
            if (resource_usage($element, $_GET['del_cert_res'])) { // check if resource has been used by user
                Session::flash('message',"$langUsedCertRes");
                Session::flash('alert-class', 'alert-warning');
                redirect($localhostUrl.$_SERVER['SCRIPT_NAME']."?course=$course_code&manage=1&unit_id=$unit_id");
            } else { // delete it otherwise
                delete_activity($element, $element_id, $_GET['del_cert_res']);
                Session::flash('message',"$langAttendanceDel");
                Session::flash('alert-class', 'alert-success');
                redirect($localhostUrl.$_SERVER['SCRIPT_NAME']."?course=$course_code&manage=1&unit_id=$unit_id");
            }
        }
    } elseif ( isset($_GET['prereq']) ) {
        insert_prerequisite_unit($unit_id, $_GET['prereq']);
        redirect($localhostUrl.$_SERVER['SCRIPT_NAME']."?course=$course_code&manage=1&unit_id=$unit_id");
    } elseif ( isset($_GET['del_un_prereq']) ) {
        delete_unit_prerequisite($unit_id);
        Session::flash('message',"$langDelUnitPrerequisiteSuccess");
        Session::flash('alert-class', 'alert-success');
        redirect($localhostUrl.$_SERVER['SCRIPT_NAME']."?course=$course_code&manage=1&unit_id=$unit_id");
    } else {
        Session::flash('message',"$langGeneralError");
        Session::flash('alert-class', 'alert-danger');
        redirect($localhostUrl."/courses/$course_code/");
    }
}

if ($show_completion_button) {
    $tool_content .= "<div class='col-sm-12'><div class='text-center alert alert-warning'><i class='fa-solid fa-triangle-exclamation fa-lg'></i><span>$langInvalidCourseUnitPrerequisites</span></div></div>";
}

if (isset($display) and $display == TRUE) {
    if ($is_editor) {
        if (isset($element_id) and ($element_id != 0)) {
            $pageName = $element_title;
            // display certificate settings and resources
            display_activities($element, $element_id, $unit_id);
        }
    }
}

draw($tool_content, 2, null, $head_content);
