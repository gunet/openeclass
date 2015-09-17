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

$require_current_course = true;
require_once '../../include/baseTheme.php';
require_once 'work_functions.php';
require_once 'modules/group/group_functions.php';

$pageName = $m['grades'];

if ($is_editor && isset($_GET['assignment']) && isset($_GET['submission'])) {
    $as_id = intval($_GET['assignment']);
    $sub_id = intval($_GET['submission']);
    $assign = get_assignment_details($as_id);

    $navigation[] = array("url" => "index.php?course=$course_code", "name" => $langWorks);
    $navigation[] = array("url" => "index.php?course=$course_code&amp;id=$as_id", "name" => q($assign->title));
    show_edit_form($as_id, $sub_id, $assign);
    draw($tool_content, 2);
} else {
    redirect_to_home_page('modules/work/index.php?course='.$course_code);
}

// Returns an array of the details of assignment $id
function get_assignment_details($id) {
    global $course_id;
    return Database::get()->querySingle("SELECT * FROM assignment WHERE course_id = ?d AND id = ?d", $course_id, $id);
}

// Show to professor details of a student's submission and allow editing of fields
// $assign contains an array with the assignment's details
function show_edit_form($id, $sid, $assign) {
    global $m, $langGradeOk, $tool_content, $course_code, $langCancel,
           $langBack, $assign, $langWorkOnlineText, $course_id;
    $sub = Database::get()->querySingle("SELECT * FROM assignment_submit WHERE id = ?d",$sid);
    if (count($sub)>0) {
        $uid_2_name = display_user($sub->uid);
        if (!empty($sub->group_id)) {
            $group_submission = "($m[groupsubmit] $m[ofgroup] " .
                    "<a href='../group/group_space.php?course=$course_code&amp;group_id=$sub->group_id'>"
                     . gid_to_name($sub->group_id) . "</a>)";
        } else {
            $group_submission = '';
        }

        $grade = Session::has('grade') ? Session::get('grade') : $sub->grade;
        $comments = Session::has('comments') ? Session::get('comments') : q($sub->grade_comments);
        $email_status = !Session::has('email') ?: " checked";

        $pageName = $m['addgradecomments'];
        if($assign->submission_type){
            $submission = "
                    <div class='form-group'>
                        <label class='col-sm-3 control-label'>$langWorkOnlineText:</label>
                        <div class='col-sm-9'>
                            $sub->submission_text
                        </div>
                    </div>";
        } else {
            $submission = "
                    <div class='form-group'>
                        <label class='col-sm-3 control-label'>$m[filename]:</label>
                        <div class='col-sm-9'>
                            <a href='index.php?course=$course_code&amp;get=$sub->id'>".q($sub->file_name)."</a>
                        </div>
                    </div>";
        }
        if ($assign->grading_scale_id) {
            $serialized_scale_data = Database::get()->querySingle('SELECT scales FROM grading_scale WHERE id = ?d AND course_id = ?d', $assign->grading_scale_id, $course_id)->scales;
            $scales = unserialize($serialized_scale_data);
            $scale_options = "<option value> - </option>";
            $scale_values = array_value_recursive('scale_item_value', $scales);
            if (!in_array($sub->grade, $scale_values) && !is_null($sub->grade)) {
                $sub->grade = closest($sub->grade, $scale_values)['value'];
            }
            foreach ($scales as $scale) {
                $scale_options .= "<option value='$scale[scale_item_value]'".($sub->grade == $scale['scale_item_value'] ? " selected" : "").">$scale[scale_item_name]</option>";
            }
            $grade_field = "
                    <select name='grade' class='form-control' id='scales'>
                        $scale_options
                    </select>";
        } else {
            $grade_field = "<input class='form-control' type='text' name='grade' maxlength='4' size='3' value='$sub->grade'> ($m[max_grade]: $assign->max_grade)";
        }
        $tool_content .= action_bar(array(
                array(
                    'title' => $langBack,
                    'url' => "index.php?course=$course_code&id=$sub->assignment_id",
                    'icon' => "fa-reply",
                    'level' => 'primary-label'
                )
            ))."
            <div class='form-wrapper'>
                <form class='form-horizontal' role='form' method='post' action='index.php?course=$course_code'>
                <input type='hidden' name='assignment' value='$id'>
                <input type='hidden' name='submission' value='$sid'>
                <fieldset>
                    <div class='form-group'>
                        <label class='col-sm-3 control-label'>$m[username]:</label>
                        <div class='col-sm-9'>
                        $uid_2_name $group_submission
                        </div>
                    </div>
                    <div class='form-group'>
                        <label class='col-sm-3 control-label'>$m[sub_date]:</label>
                        <div class='col-sm-9'>
                            <span>".q($sub->submission_date)."</span>
                        </div>
                    </div>
                    $submission
                    <div class='form-group".(Session::getError('grade') ? " has-error" : "")."'>
                        <label for='grade' class='col-sm-3 control-label'>$m[grade]:</label>
                        <div class='col-sm-4'>
                            $grade_field
                            <span class='help-block'>".(Session::hasError('grade') ? Session::getError('grade') : "")."</span>
                        </div>
                    </div>
                    <div class='form-group'>
                        <label for='comments' class='col-sm-3 control-label'>$m[gradecomments]:</label>
                        <div class='col-sm-9'>
                            <textarea class='form-control' rows='3' name='comments'  id='comments'>$comments</textarea>
                        </div>
                    </div>
                    <div class='form-group'>
                        <div class='col-sm-9 col-sm-offset-3'>
                            <div class='checkbox'>
                                <label>
                                    <input type='checkbox' value='1' id='email_button' name='email'$email_status>
                                    $m[email_users]
                                </label>
                            </div>
                        </div>
                    </div>
                    <div class='form-group'>
                        <div class='col-sm-9 col-sm-offset-3'>
                            <input class='btn btn-primary' type='submit' name='grade_comments' value='$langGradeOk'>
                            <a class='btn btn-default' href='index.php?course=$course_code&id=$sub->assignment_id'>$langCancel</a>
                        </div>
                    </div>
                </fieldset>
                </form>
            </div>";
    } else {
        Session::Messages($m['WorkNoSubmission'], 'alert-danger');
        redirect_to_home_page('modules/work/index.php?course='.$course_code.'&id='.$id);
    }
}
