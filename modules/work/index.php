<?php
/*
 *  ========================================================================
 *  * Open eClass
 *  * E-learning and Course Management System
 *  * ========================================================================
 *  * Copyright 2003-2024, Greek Universities Network - GUnet
 *  *
 *  * Open eClass is an open platform distributed in the hope that it will
 *  * be useful (without any warranty), under the terms of the GNU (General
 *  * Public License) as published by the Free Software Foundation.
 *  * The full license can be read in "/info/license/license_gpl.txt".
 *  *
 *  * Contact address: GUnet Asynchronous eLearning Group
 *  *                  e-mail: info@openeclass.org
 *  * ========================================================================
 *
 */

$require_current_course = true;
$require_login = true;
$require_user_registration = true;
$require_help = true;
$helpTopic = 'assignments';

require_once '../../include/baseTheme.php';
require_once 'include/lib/forcedownload.php';
require_once 'utilities.php';
require_once 'functions.php';
require_once 'modules/group/group_functions.php';
require_once 'modules/gradebook/functions.php';
require_once 'modules/attendance/functions.php';
require_once 'include/lib/fileUploadLib.inc.php';
require_once 'include/lib/fileManageLib.inc.php';
require_once 'include/sendMail.inc.php';
require_once 'include/log.class.php';
require_once 'modules/tags/moduleElement.class.php';
require_once 'modules/admin/extconfig/externals.php';
require_once 'modules/plagiarism/plagiarism.php';
require_once 'modules/progress/AssignmentEvent.php';
require_once 'modules/progress/AssignmentSubmitEvent.php';
require_once 'modules/analytics/AssignmentAnalyticsEvent.php';
require_once 'modules/lti_consumer/lti-functions.php';
require_once 'modules/admin/extconfig/turnitinapp.php';

// For colorbox, fancybox, shadowbox use
require_once 'include/lib/modalboxhelper.class.php';
require_once 'include/lib/multimediahelper.class.php';
ModalBoxHelper::loadModalBox();
/* * ** The following is added for statistics purposes ** */
require_once 'include/action.php';
$action = new action();
$action->record(MODULE_ID_ASSIGN);
/* * *********************************** */

$unit = $unit ?? null;

load_js('datatables');
load_js('tools.js');

// D3 / C3 used to display assignment grade graph
if ($is_editor and isset($_GET['id']) and isset($_GET['disp_results'])) {
    require_once 'modules/usage/usage.lib.php';
    load_js('d3/d3.min.js');
    load_js('c3-0.4.10/c3.min.js');
}

$workPath = $webDir . "/courses/" . $course_code . "/work";
$works_url = array('url' => "{$urlAppend}modules/work/index.php?course=$course_code", 'name' => $langWorks);
$toolName = $langWorks;

// Auto Judge settings (if any)
$autojudge = new AutojudgeApp();
if ($autojudge->isConfigured()) {
    $autojudge = AutojudgeApp::getAutojudge();
}
// ajax requests
if(!empty($_SERVER['HTTP_X_REQUESTED_WITH']) && strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) == 'xmlhttprequest') {
    if ($is_course_reviewer) {
        // info about assignments assigned to users and groups
        if (isset($_GET['ass_info_assigned_to'])) {
            echo "<ul>";
            $q = Database::get()->queryArray("SELECT user_id, group_id FROM assignment_to_specific WHERE assignment_id = ?d", $_GET['ass_id']);
            foreach ($q as $user_data) {
                if ($user_data->user_id == 0) { // assigned to group
                    $q_group = Database::get()->querySingle("SELECT name FROM `group` WHERE id = ?d", $user_data->group_id);
                    if ($q_group) {
                        echo "<li>" . q($q_group->name) . "</li>";
                    }
                } else { // assigned to user
                    echo "<li>" . q(uid_to_name($user_data->user_id)) . "</li>";
                }
            }
            echo "</ul>";
            exit;
        }
    }
    $data = [];
    if (isset($_POST['sid'])) {
        $sid = $_POST['sid'];
        $data['submission_text'] = Database::get()->querySingle("SELECT submission_text FROM assignment_submit WHERE id = ?d", $sid)->submission_text;
    } elseif (isset($_POST['assign_g_type'])) {
        if ($_POST['assign_g_type'] == 2) {
            $data = Database::get()->queryArray("SELECT name, id FROM `group` WHERE course_id = ?d ORDER BY name", $course_id);
        } else {
            $data = Database::get()->queryArray("SELECT user.id AS id, surname, givenname
                                FROM user, course_user
                                WHERE user.id = course_user.user_id
                                AND course_user.course_id = ?d
                                AND course_user.status = " . USER_STUDENT . "
                                AND user.id
                                ORDER BY surname", $course_id);
        }
    }
    echo json_encode($data);
    exit;
}


//Gets the student's assignment file ($file_type=NULL)
//or the teacher's assignment ($file_type=1)
if (isset($_GET['get']) or isset($_GET['getcomment'])) {
    if (isset($_GET['getcomment'])) {
        $file_type = 2;
        $get = $_GET['getcomment'];
    } else {
        if (isset($_GET['file_type']) && $_GET['file_type']==1) {
            $file_type = intval($_GET['file_type']);
        } else {
            $file_type = NULL;
        }
        $get = $_GET['get'];
    }
    if (!send_file(intval($get), $file_type)) {
        Session::flash('message',$langFileNotFound);
        Session::flash('alert-class', 'alert-danger');
    }
}

if (isset($_GET['chk'])) { // plagiarism check
    $file_id = $_GET['chk'];
    $file_details = Database::get()->querySingle("SELECT assignment_id, file_path, file_name FROM assignment_submit WHERE id = ?d", $file_id);
    if ($file_details) {
        $assign_id = $file_details->assignment_id;
        $true_file_name = $file_details->file_name;
        $secret = work_secret($file_details->assignment_id);
        $true_file_path = $workPath . "/" . $file_details->file_path;
    } else {
        Session::flash('message',$langFileNotFound);
        Session::flash('alert-class', 'alert-danger');
    }
    send_file_for_plagiarism($assign_id, $file_id, $true_file_path, $true_file_name);
}


// Only course admins can download all assignments in a zip file
if ($is_editor) {
    if (isset($_GET['download'])) {
        $as_id = intval($_GET['download']);
        // Allow unlimited time for creating the archive
        set_time_limit(0);
        if (!download_assignments($as_id)) {
            Session::flash('message',$langNoAssignmentsExist);
            Session::flash('alert-class', 'alert-danger');
            redirect_to_home_page('modules/work/index.php?course='.$course_code.'&id='.$as_id);
        }
    }
}

if ($is_editor) {
    $email_notify = (isset($_POST['send_email']) && $_POST['send_email']);
    if (isset($_POST['grade_comments'])) {
        $work_title = Database::get()->querySingle("SELECT title FROM assignment WHERE id = ?d", intval($_POST['assignment']))->title;
        $pageName = $work_title;
        $navigation[] = $works_url;
        submit_grade_comments($_POST);
    } elseif (isset($_POST['ass_review'])) {
        $id = intval($_POST['assign']);
        submit_review_per_ass($id);
    } elseif (isset($_GET['add'])) {
        $pageName = $langNewAssign;
        $navigation[] = $works_url;
        new_edit_assignment();
    } elseif (isset($_POST['new_assign'])) {
        add_assignment();
    } elseif (isset($_GET['as_id'])) {
        $as_id = intval($_GET['as_id']);
        $id = intval($_GET['id']);
        if (delete_user_assignment($as_id)){
            Session::flash('message',$langDeleted);
            Session::flash('alert-class', 'alert-success');
        } else {
            Session::flash('message',$langDelError);
            Session::flash('alert-class', 'alert-danger');
        }
        redirect_to_home_page('modules/work/index.php?course='.$course_code.'&id='.$id);
    } elseif (isset($_POST['grades']) || isset($_POST['grade_rubric']) ) {
        $navigation[] = $works_url;
        submit_grades(intval($_POST['grades_id']), $_POST, $email_notify);
    } elseif (isset($_REQUEST['id'])) {
        $id = intval($_REQUEST['id']);
        $work_title_raw = Database::get()->querySingle("SELECT title FROM assignment WHERE id = ?d", $id)->title;
        $work_title = q($work_title_raw);
        $work_id_url = array('url' => "$_SERVER[SCRIPT_NAME]?course=$course_code&id=$id",
            'name' => $work_title);
        if (isset($_POST['on_behalf_of'])) {
            if (isset($_POST['user_id'])) {
                $user_id = intval($_POST['user_id']);
            } else {
                $user_id = $uid;
            }
            $pageName = $langAddGrade;
            $navigation[] = $works_url;
            $navigation[] = $work_id_url;
            submit_work($id, $user_id);
        } elseif (isset($_REQUEST['choice'])) {
            $choice = $_REQUEST['choice'];
            if ($choice == 'disable') {
                if (!resource_belongs_to_progress_data(MODULE_ID_ASSIGN, $id)) {
                    if (Database::get()->query("UPDATE assignment SET active = '0' WHERE id = ?d", $id)->affectedRows > 0) {
                        Session::flash('message',$langAssignmentDeactivated);
                        Session::flash('alert-class', 'alert-success');
                    }
                } else {
                    Session::flash('message',$langResourceBelongsToCert);
                    Session::flash('alert-class', 'alert-warning');
                }
                redirect_to_home_page('modules/work/index.php?course='.$course_code);
            } elseif ($choice == 'enable') {
                if (Database::get()->query("UPDATE assignment SET active = '1' WHERE id = ?d", $id)->affectedRows > 0) {
                    Session::flash('message',$langAssignmentActivated);
                    Session::flash('alert-class', 'alert-success');
                }
                redirect_to_home_page('modules/work/index.php?course='.$course_code);
            } elseif ($choice == 'do_delete') {
                if (!resource_belongs_to_progress_data(MODULE_ID_ASSIGN, $id)) {
                    if(delete_assignment($id)) {
                        Session::flash('message',$langDeleted);
                        Session::flash('alert-class', 'alert-success');
                    } else {
                        Session::flash('message',$langDelError);
                        Session::flash('alert-class', 'alert-danger');
                    }
                } else {
                    Session::flash('message',$langResourceBelongsToCert);
                    Session::flash('alert-class', 'alert-warning');
                }
                redirect_to_home_page('modules/work/index.php?course='.$course_code);
            } elseif ($choice == 'do_delete_file') {
                if(delete_teacher_assignment_file($id)){
                    Session::flash('message',$langDelF);
                    Session::flash('alert-class', 'alert-success');
                } else {
                    Session::flash('message',$langDelF);
                    Session::flash('alert-class', 'alert-danger');
                }
                redirect_to_home_page("modules/work/index.php?course=$course_code&id=$id&choice=edit");
            } elseif ($choice == 'do_purge') {
                if (!resource_belongs_to_progress_data(MODULE_ID_ASSIGN, $id)) {
                    if (purge_assignment_subs($id)) {
                        Session::flash('message',$langAssignmentSubsDeleted);
                        Session::flash('alert-class', 'alert-success');
                    }
                } else {
                    Session::flash('message',$langResourceBelongsToCert);
                    Session::flash('alert-class', 'alert-warning');
                }
                redirect_to_home_page('modules/work/index.php?course='.$course_code);
            } elseif ($choice == 'edit') {
                $pageName = $m['WorkEdit'];
                $navigation[] = $works_url;
                $navigation[] = $work_id_url;
                new_edit_assignment($id);
            } elseif ($choice == 'do_edit') {
                $pageName = $langWorks;
                $navigation[] = $works_url;
                $navigation[] = $work_id_url;
                edit_assignment($id);
            } elseif ($choice == 'add') {
                $pageName = $langAddGrade;
                $navigation[] = $works_url;
                $navigation[] = $work_id_url;
                display_student_assignment($id, true);
            } elseif ($choice == 'plain') {
                show_plain_view($id);
            } elseif ($choice == 'export') {
                export_grades_to_csv($id);
            }
        } else {
            $pageName = $work_title_raw;
            $navigation[] = $works_url;
            if (isset($_GET['disp_results'])) {
                display_assignment_submissions_graph_results($id);
            } elseif (isset($_GET['disp_non_submitted'])) {
                display_not_submitted($id);
            } else {
                display_assignment_submissions($id);
            }
        }
    } else {
        $pageName = $langWorks;
        display_assignments();
    }
}  elseif ($is_course_reviewer) { // course reviewer view
    if (isset($_REQUEST['id'])) {
        $id = intval($_REQUEST['id']);
        $work_title_raw = Database::get()->querySingle("SELECT title FROM assignment WHERE id = ?d", $id)->title;
        $pageName = q($work_title_raw);
        $navigation[] = $works_url;
        display_assignment_submissions($id);
    } else {
        $pageName = $langWorks;
        display_assignments();
    }
} else {
    if (isset($_REQUEST['id'])) {
        $id = intval($_REQUEST['id']);
        if (isset($_POST['work_submit'])) {
            $pageName = $m['SubmissionStatusWorkInfo'];
            if (!$unit) {
                $navigation[] = $works_url;
            }
            $navigation[] = array('url' => "$_SERVER[SCRIPT_NAME]?course=$course_code&amp;id=$id", 'name' => $langWorks);
            submit_work($id);
        } else {
            $work_title_raw = Database::get()->querySingle("SELECT title FROM assignment WHERE id = ?d", $id)->title;
            $pageName = q($work_title_raw);
            if (!isset($unit)) {
                $navigation[] = $works_url;
            }
            display_student_assignment($id);
        }
    } else {
        display_assignments(false);
    }
    //call  submit_grade_reviews
    if (isset($_POST['grade_comments_review'])) {
        $work_title_raw = Database::get()->querySingle("SELECT title FROM assignment WHERE id = ?d", intval($_POST['assignment']))->title;
        $pageName = q($work_title_raw);
        if (!isset($unit)) {
            $navigation[] = $works_url;
        }
        submit_grade_reviews($_POST);
    }
}

add_units_navigation(TRUE);
