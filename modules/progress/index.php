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
$require_user_registration = true;
$require_help = true;
$helpTopic = 'progress';

require_once '../../include/baseTheme.php';
require_once 'functions.php';
require_once 'process_functions.php';
require_once 'ExerciseEvent.php';
require_once 'AssignmentEvent.php';
require_once 'AssignmentSubmitEvent.php';
require_once 'CommentEvent.php';
require_once 'BlogEvent.php';
require_once 'WikiEvent.php';
require_once 'ForumEvent.php';
require_once 'ForumTopicEvent.php';
require_once 'LearningPathEvent.php';
require_once 'LearningPathDurationEvent.php';
require_once 'RatingEvent.php';
require_once 'ViewingEvent.php';
require_once 'CourseParticipationEvent.php';
require_once 'GradebookEvent.php';
require_once 'CourseCompletionEvent.php';
require_once 'AttendanceEvent.php';

$toolName = $langProgress;

load_js('tools.js');

$display = TRUE;
if (isset($_REQUEST['certificate_id'])) {
    $param_name = 'certificate_id';
    $element_id = $_REQUEST['certificate_id'];
    $element = 'certificate';
    $element_title = get_cert_title($element, $element_id);
    $navigation[] = array("url" => "$_SERVER[SCRIPT_NAME]?course=$course_code", "name" => $langProgress);
}

if (isset($_REQUEST['badge_id'])) {
    $param_name = 'badge_id';
    $element_id = $_REQUEST['badge_id'];
    $element = 'badge';
    $element_title = get_cert_title($element, $element_id);
    $navigation[] = array("url" => "$_SERVER[SCRIPT_NAME]?course=$course_code", "name" => $langProgress);
}

if ($is_editor) {

    // Top menu
    $tool_content .= "<div class='col-sm-12'>";
    if(isset($_GET['edit'])) {
        $navigation[] = array("url" => "$_SERVER[SCRIPT_NAME]?course=$course_code&amp;$param_name=$element_id", "name" => $element_title);
        $pageName = $langConfig;
    } elseif (isset($_GET['act_mod'])) { // modify certificate activity
        $navigation[] = array("url" => "$_SERVER[SCRIPT_NAME]?course=$course_code&amp;$param_name=$element_id", "name" => $element_title);
        $pageName = $langEditChange;
    } elseif(isset($_GET['add'])) { // add certificate activity
        $navigation[] = array("url" => "$_SERVER[SCRIPT_NAME]?course=$course_code&amp;$param_name=$element_id", "name" => $element_title);
        $pageName = "$langAdd $langOfGradebookActivity";
    } elseif (isset($_GET['newcert'])) { // new certificate activity
        $navigation[] = array("url" => "$_SERVER[SCRIPT_NAME]?course=$course_code", "name" => $langProgress);
        $pageName = $langNewCertificate;
    } elseif (isset($_GET['newbadge'])) { // new badge activity
        $navigation[] = array("url" => "$_SERVER[SCRIPT_NAME]?course=$course_code", "name" => $langProgress);
        $pageName = $langNewBadge;
    } elseif (isset($_GET['u'])) {
        $navigation[] = array("url" => "$_SERVER[SCRIPT_NAME]?course=$course_code&amp;$param_name=$element_id", "name" => $element_title);
        $navigation[] = array("url" => "$_SERVER[SCRIPT_NAME]?course=$course_code&amp;$param_name=$element_id&amp;progressall=true", "name" => $langUsers);
        $pageName = uid_to_name($_GET['u']);
        $action_bar = action_bar(array(
            array('title' => $langBack,
                  'url' => "$_SERVER[SCRIPT_NAME]?course=$course_code&amp;$param_name=$element_id&amp;progressall=true",
                  'icon' => 'fa-reply',
                  'level' => 'primary')));
        $tool_content .= $action_bar;
    } elseif (isset($_GET['progressall'])) {
        $navigation[] = array("url" => "$_SERVER[SCRIPT_NAME]?course=$course_code&amp;$param_name=$element_id", "name" => $element_title);
        $pageName = "$langProgress $langsOfStudents";
        $info_title = $langRefreshProgressInfo;
        $action_bar = action_bar(array(
            array('title' => $langBack,
                  'url' => "$_SERVER[SCRIPT_NAME]?course=$course_code&amp;$param_name=$element_id",
                  'icon' => 'fa-reply',
                  'level' => 'primary'),
            array('title' => $langRefreshProgress,
                  'url' => "$_SERVER[SCRIPT_NAME]?course=$course_code&amp;$param_name=$element_id&amp;refresh=true",
                  'icon' => 'fa-refresh',
                  'link-attrs' => "title='$info_title'",
                  'level' => 'primary-label'),
            array('title' => "$langExport",
                'url' => "dumpcertificateresults.php?course=$course_code&amp;$param_name=$element_id",
                'icon' => 'fa-file-excel',
                'level' => 'primary-label')
            ));
        $tool_content .= $action_bar;

    } elseif (isset($_GET['preview'])) { // certificate preview
        cert_output_to_pdf($element_id, $uid, null, null, null, null, null, null);
    } elseif (!(isset($_REQUEST['certificate_id']) or (isset($_REQUEST['badge_id'])))) {
        $action_bar = action_bar(array(
            array('title' => $langBack,
                  'url' => "{$urlServer}courses/$course_code/index.php",
                  'icon' => 'fa-reply',
                  'level' => 'primary'),
            array('title' => $langCourseCompletion,
                  'url' => "$_SERVER[SCRIPT_NAME]?course=$course_code&amp;newcc=1",
                  'icon' => 'fa-navicon',
                  'level' => 'primary-label',
                  'show' => !is_course_completion_enabled())
            ));
        $tool_content .= $action_bar;
    }
    $tool_content .= "</div>";
    //end of the top menu

    if (isset($_GET['vis'])) { // activate or deactivate certificate / badge
        if (has_activity($element, $element_id) > 0) {
            update_visibility($element, $element_id, $_GET['vis']);
            Session::flash('message',$langGlossaryUpdated);
            Session::flash('alert-class', 'alert-success');
        } else {
            Session::flash('message',$langNotActivated);
            Session::flash('alert-class', 'alert-warning');
        }
        redirect_to_home_page("modules/progress/index.php?course=$course_code");
    }
    if (isset($_POST['newCertificate']) or isset($_POST['newBadge'])) {  // add a new certificate / badge
        $v = new Valitron\Validator($_POST);
        $v->rule('required', array('title'));
        $v->labels(array(
            'title' => "$langTheField $langTitle",
        ));

        if($v->validate()) {
            $table = (isset($_POST['newCertificate'])) ? 'certificate' : 'badge';
            $icon  = $_POST['template'];
            $expires = null;
            if (isset($_POST['enablecertdeadline'])) {
                $expires = date_format(date_create_from_format('d-m-Y H:i', $_POST['enddatepicker']), 'Y-m-d H:i');
            }
            add_certificate($table, $_POST['title'], $_POST['description'], $_POST['message'], $icon, $_POST['issuer'], 0, 0, $expires);
            Session::flash('message',$langNewCertificateSuc);
            Session::flash('alert-class', 'alert-success');
            redirect_to_home_page("modules/progress/index.php?course=$course_code");
        } else {
            Session::flashPost()->Messages($langFormErrors)->Errors($v->errors());
            redirect_to_home_page("modules/progress/index.php?course=$course_code&new=1");
        }
    } elseif (isset($_POST['edit_element'])) { // modify certificate / badge
        $v = new Valitron\Validator($_POST);
        $v->rule('required', array('title'));
        $v->labels(array(
            'title' => "$langTheField $langTitle",
        ));
        if($v->validate()) {
            modify($element, $element_id, $_POST['title'], $_POST['description'], $_POST['message'], $_POST['template'], $_POST['issuer']);
            Session::flash('message',$langQuotaSuccess);
            Session::flash('alert-class', 'alert-success');
            redirect_to_home_page("modules/progress/index.php?course=$course_code");
        } else {
            Session::flashPost()->Messages($langFormErrors)->Errors($v->errors());
            redirect_to_home_page("modules/progress/index.php?course=$course_code&edit=1");
        }
    } elseif (isset($_POST['mod_cert_activity'])) { // modify certificate activity
        modify_certificate_activity($element, $element_id, $_POST['activity_id']);
        Session::flash('message',$langQuotaSuccess);
        Session::flash('alert-class', 'alert-success');
        redirect_to_home_page("modules/progress/index.php?course=$course_code&$param_name=$element_id");
    }
        // add resources to certificate
    elseif(isset($_POST['add_assignment'])) { // add assignment activity in certificate
        add_assignment_to_certificate($element, $element_id, AssignmentEvent::ACTIVITY);
        Session::flash('message', $langQuotaSuccess);
        Session::flash('alert-class', 'alert-success');
        redirect_to_home_page("modules/progress/index.php?course=$course_code&$param_name=$element_id");
    } elseif (isset($_POST['add_assignment_participation'])) { // add assignment participation activity in certificate
        add_assignment_to_certificate($element, $element_id, AssignmentSubmitEvent::ACTIVITY);
        Session::flash('message', $langQuotaSuccess);
        Session::flash('alert-class', 'alert-success');
        redirect_to_home_page("modules/progress/index.php?course=$course_code&$param_name=$element_id");
    } elseif (isset($_POST['add_exercise'])) { // add exercise activity in certificate
        add_exercise_to_certificate($element, $element_id);
        Session::flash('message',$langQuotaSuccess);
        Session::flash('alert-class', 'alert-success');
        redirect_to_home_page("modules/progress/index.php?course=$course_code&$param_name=$element_id");
    } elseif (isset($_POST['add_lp'])) { // add learning path activity in certificate
        add_lp_to_certificate($element, $element_id, LearningPathEvent::ACTIVITY);
        Session::flash('message',$langQuotaSuccess);
        Session::flash('alert-class', 'alert-success');
        redirect_to_home_page("modules/progress/index.php?course=$course_code&$param_name=$element_id");
    } elseif (isset($_POST['add_lpduration'])) { // add learning path duration activity in certificate
        add_lp_to_certificate($element, $element_id, LearningPathDurationEvent::ACTIVITY);
        Session::flash('message',$langQuotaSuccess);
        Session::flash('alert-class', 'alert-success');
        redirect_to_home_page("modules/progress/index.php?course=$course_code&$param_name=$element_id");
    } elseif (isset($_POST['add_document'])) { // add document activity in certificate
        add_document_to_certificate($element, $element_id);
        Session::flash('message',$langQuotaSuccess);
        Session::flash('alert-class', 'alert-success');
        redirect_to_home_page("modules/progress/index.php?course=$course_code&$param_name=$element_id");
    } elseif (isset($_POST['add_multimedia'])) { // add multimedia activity in certificate
        add_multimedia_to_certificate($element, $element_id);
        Session::flash('message',$langQuotaSuccess);
        Session::flash('alert-class', 'alert-success');
        redirect_to_home_page("modules/progress/index.php?course=$course_code&$param_name=$element_id");
    } elseif (isset($_POST['add_poll'])) { // add poll activity in certificate
        add_poll_to_certificate($element, $element_id);
        Session::flash('message',$langQuotaSuccess);
        Session::flash('alert-class', 'alert-success');
        redirect_to_home_page("modules/progress/index.php?course=$course_code&$param_name=$element_id");
    } elseif (isset($_POST['add_wiki'])) { // add wiki activity in certificate
        add_wiki_to_certificate($element, $element_id);
        Session::flash('message',$langQuotaSuccess);
        Session::flash('alert-class', 'alert-success');
        redirect_to_home_page("modules/progress/index.php?course=$course_code&$param_name=$element_id");
    } elseif (isset($_POST['add_ebook'])) { // add ebook activity in certificate
        add_ebook_to_certificate($element, $element_id);
        Session::flash('message',$langQuotaSuccess);
        Session::flash('alert-class', 'alert-success');
        redirect_to_home_page("modules/progress/index.php?course=$course_code&$param_name=$element_id");
    } elseif (isset($_POST['add_forum'])) { // add forum activity in certificate
        add_forum_to_certificate($element, $element_id);
        Session::flash('message',$langQuotaSuccess);
        Session::flash('alert-class', 'alert-success');
        redirect_to_home_page("modules/progress/index.php?course=$course_code&$param_name=$element_id");
    } elseif (isset($_POST['add_forumtopic'])) { // add forum activity in certificate
        add_forumtopic_to_certificate($element, $element_id);
        Session::flash('message',$langQuotaSuccess);
        Session::flash('alert-class', 'alert-success');
        redirect_to_home_page("modules/progress/index.php?course=$course_code&$param_name=$element_id");
    } elseif (isset($_POST['add_blog'])) {
        add_blog_to_certificate($element, $element_id);
        Session::flash('message',$langQuotaSuccess);
        Session::flash('alert-class', 'alert-success');
        redirect_to_home_page("modules/progress/index.php?course=$course_code&$param_name=$element_id");
    } elseif (isset($_POST['add_blogcomment'])) {
        add_blogcomment_to_certificate($element, $element_id);
        Session::flash('message',$langQuotaSuccess);
        Session::flash('alert-class', 'alert-success');
        redirect_to_home_page("modules/progress/index.php?course=$course_code&$param_name=$element_id");
    } elseif (isset($_POST['add_participation'])) {
        add_courseparticipation_to_certificate($element, $element_id);
        Session::flash('message',$langQuotaSuccess);
        Session::flash('alert-class', 'alert-success');
        redirect_to_home_page("modules/progress/index.php?course=$course_code&$param_name=$element_id");
    } elseif (isset($_POST['add_gradebook'])) {
        add_gradebook_to_certificate($element, $element_id);
        Session::flash('message',$langQuotaSuccess);
        Session::flash('alert-class', 'alert-success');
        redirect_to_home_page("modules/progress/index.php?course=$course_code&$param_name=$element_id");
    } elseif (isset($_POST['add_coursecompletiongrade'])) {
        add_coursecompletiongrade_to_certificate($element, $element_id);
        Session::flash('message',$langQuotaSuccess);
        Session::flash('alert-class', 'alert-success');
        redirect_to_home_page("modules/progress/index.php?course=$course_code&$param_name=$element_id");
    } elseif (isset($_POST['add_attendance'])) {
        add_attendance_to_certificate($element, $element_id);
        Session::Messages("$langQuotaSuccess", 'alert-success');
        redirect_to_home_page("modules/progress/index.php?course=$course_code&$param_name=$element_id");
    }
    // actions
    elseif (isset($_GET['del_cert_res'])) { // delete certificate / badge activity
        if (resource_usage($element, $_GET['del_cert_res'])) { // check if resource has been used by user
            Session::flash('message',$langUsedCertRes);
            Session::flash('alert-class', 'alert-warning');
            redirect_to_home_page("modules/progress/index.php?course=$course_code&$param_name=$element_id");
        } else { // delete it otherwise
            delete_activity($element, $element_id, $_GET['del_cert_res']);
            Session::flash('message',$langAttendanceDel);
            Session::flash('alert-class', 'alert-success');
            redirect_to_home_page("modules/progress/index.php?course=$course_code&$param_name=$element_id");
        }
    } elseif (isset($_GET['del_cert'])) {  //  delete certificate
        if (delete_certificate('certificate', $_GET['del_cert'])) {
            Session::flash('message',$langGlossaryDeleted);
            Session::flash('alert-class', 'alert-success');
            redirect_to_home_page("modules/progress/index.php?course=$course_code");
        } else {
            Session::flash('message',$langUsedCertRes);
            Session::flash('alert-class', 'alert-warning');
        }
    } elseif (isset($_GET['del_badge'])) {  //  delete badge
        if (delete_certificate('badge', $_GET['del_badge'])) {
            Session::flash('message',$langGlossaryDeleted);
            Session::flash('alert-class', 'alert-success');
            redirect_to_home_page("modules/progress/index.php?course=$course_code");
        } else {
            Session::flash('message',$langUsedCertRes);
            Session::flash('alert-class', 'alert-warning');
        }
    } elseif (isset($_GET['purge_cc'])) { // purge badge
        if (purge_certificate('badge', $_GET['purge_cc'])) {
            Session::flash('message',$langGlossaryDeleted);
            Session::flash('alert-class', 'alert-success');
        }
        redirect_to_home_page("modules/progress/index.php?course=$course_code");
    } elseif (isset($_GET['purge_cert'])) { // purge certificate
        if (purge_certificate('certificate', $_GET['purge_cert'])) {
            Session::flash('message',$langGlossaryDeleted);
            Session::flash('alert-class', 'alert-success');
        }
        redirect_to_home_page("modules/progress/index.php?course=$course_code");
    } elseif (isset($_GET['newcert'])) {  // create new certificate
        certificate_settings('certificate');
        $display = FALSE;
    }  elseif (isset($_GET['newbadge'])) {  // create new badge
        certificate_settings('badge');
        $display = FALSE;
    } elseif (isset($_GET['newcc'])) { // create course completion (special type of badge)
        add_certificate('badge', $langCourseCompletion, '', $langCourseCompletionMessage, '', q(get_config('institution')), 0, -1, null);
        Session::flash('message',$langCourseCompletionCreated);
        Session::flash('alert-class', 'alert-success');
        redirect_to_home_page("modules/progress/index.php?course=$course_code");
        $display = FALSE;
    } elseif (isset($_GET['edit'])) { // edit certificate / badge settings
        certificate_settings($element, $element_id);
        $display = FALSE;
    } elseif (isset($_GET['add']) and isset($_GET['act'])) { // insert certificate / badge activity
        insert_activity($element, $element_id, $_GET['act']);
        $display = FALSE;
    } elseif (isset($_GET['act_mod'])) { // modify certificate / badge activity
        display_modification_activity($element, $element_id, $_GET['act_mod']);
        $display = FALSE;
    } elseif (isset($_GET['progressall'])) { // display users progress (teacher view)
        display_users_progress($element, $element_id);
        $display = FALSE;
    } elseif (isset($_GET['u'])) { // display detailed user progress
        display_user_progress_details($element, $element_id, $_GET['u']);
        $display = FALSE;
    } elseif (isset($_GET['refresh'])) {
        refresh_user_progress($element, $element_id);
        Session::Messages("$langRefreshProgressResults", "alert-success");
        redirect_to_home_page("modules/progress/index.php?course=$course_code&$param_name=$element_id&progressall=true");
    }
} else if ($is_course_reviewer) {
    if (isset($_GET['progressall'])) { // display users progress (course reviewer view)
        $navigation[] = array("url" => "$_SERVER[SCRIPT_NAME]?course=$course_code&amp;$param_name=$element_id", "name" => $element_title);
        $pageName = "$langProgress $langsOfStudents";
        $tool_content .= action_bar(array(
            array('title' => $langBack,
                'url' => "$_SERVER[SCRIPT_NAME]?course=$course_code&amp;$param_name=$element_id",
                'icon' => 'fa-reply',
                'level' => 'primary-label')));
        display_users_progress($element, $element_id);
        $display = FALSE;
    } elseif (isset($_GET['u'])) { // display detailed user progress
        $navigation[] = array("url" => "$_SERVER[SCRIPT_NAME]?course=$course_code&amp;$param_name=$element_id", "name" => $element_title);
        $navigation[] = array("url" => "$_SERVER[SCRIPT_NAME]?course=$course_code&amp;$param_name=$element_id&amp;progressall=true", "name" => $langUsers);
        $pageName = uid_to_name($_GET['u']);
        $action_bar = action_bar(array(
            array('title' => $langBack,
                'url' => "$_SERVER[SCRIPT_NAME]?course=$course_code&amp;$param_name=$element_id&amp;progressall=true",
                'icon' => 'fa-reply',
                'level' => 'primary-label')));
        $tool_content .= $action_bar;
        display_user_progress_details($element, $element_id, $_GET['u']);
        $display = FALSE;
    }
} elseif (isset($_GET['u'])) { // student view
        $action_bar = action_bar(array(
            array('title' => $langBack,
                      'url' => "$_SERVER[SCRIPT_NAME]?course=$course_code",
                      'icon' => 'fa-reply',
                      'level' => 'primary'),
	        array('title' => $langPrint,
	              'url' => "$_SERVER[SCRIPT_NAME]?course=$course_code&$param_name=$element_id&u=".$_GET['u']."&p=1",
	              'icon' => 'fa-print',
	              'level' => 'primary-label',
	              'show' => has_certificate_completed($_GET['u'], $element, $element_id) and $element == "certificate")
            ));
        $tool_content .= $action_bar;
}

if (isset($display) and $display == TRUE) {
    if ($is_course_reviewer) {
        if (isset($element_id)) {
            $pageName = $element_title;
            // display certificate settings and resources
            display_activities($element, $element_id);
        } else { // display all certificate
            display_course_completion();
	        display_badges();
	        display_certificates();
        }
    } else {
        check_user_details($uid); // security check
        if (isset($element_id)) {
            $certificate_expiration_date = get_cert_expiration_day($element, $element_id); // security check
            if (!is_null($certificate_expiration_date) and $certificate_expiration_date < date('Y-m-d H:i:s')) {
                redirect_to_home_page();
            }
            if (isset($_GET['p']) and $_GET['p']) { // printable view
                if (!has_certificate_completed($uid, $element, $element_id)) { // security check
                    redirect_to_home_page();
                }
                cert_output_to_pdf($element_id, $uid, null, null, null, null, null, null);
            } else {
                if (!is_cert_visible($element, $element_id)) { // security check
                    redirect_to_home_page();
                }
                $pageName = $element_title;
                // display detailed user progress
                display_user_progress_details($element, $element_id, $uid);
            }
        } else {
            // display certificate (student view)
            student_view_progress();
        }
    }
}

draw($tool_content, 2, null, $head_content);
