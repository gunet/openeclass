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

$nameTools = $m['grades'];

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
    global $m, $langGradeOk, $tool_content, $course_code;
    $sub = Database::get()->querySingle("SELECT * FROM assignment_submit WHERE id = ?d",$sid);
    if (count($sub)>0) {
        $uid_2_name = display_user($sub->uid);
        if (!empty($sub->group_id)) {
            $group_submission = "($m[groupsubmit] " .
                    "<a href='../group/group_space.php?course=$course_code&amp;group_id=$sub->group_id'>" .
                    "$m[ofgroup] " . gid_to_name($sub->group_id) . "</a>)";
        } else {
            $group_submission = '';
        }

        $tool_content .= "
                <form method='post' action='index.php?course=$course_code'>
                <input type='hidden' name='assignment' value='$id'>
                <input type='hidden' name='submission' value='$sid'>
                <fieldset>
                <legend>$m[addgradecomments]</legend>
                <table width='99%' class='tbl'>
                <tr>
                    <th class='left' width='180'>${m['username']}:</th>
                    <td>${uid_2_name} ".q($group_submission)."</td>
                </tr>
                <tr>
                    <th class='left'>${m['sub_date']}:</th>
                    <td>".q($sub->submission_date)."</td></tr>
                <tr>
                    <th class='left'>${m['filename']}:</th>
                    <td><a href='index.php?course=$course_code&amp;get=$sub->id'>".q($sub->file_name)."</a></td>
                </tr>
                <tr>
                    <th class='left'>$m[grade]:</th>
                    <td><input type='text' name='grade' maxlength='3' size='3' value='".q($sub->grade)."'></td></tr>
                <tr>
                    <th class='left'>$m[gradecomments]:</th>
                    <td><textarea cols='60' rows='3' name='comments'>".q($sub->grade_comments)."</textarea></td>
                </tr>
                <tr>
                    <th><label for='email_button'>$m[email_users]:</label></th>
                    <td><input type='checkbox' value='1' id='email_button' name='email'></td>
                </tr>
                <tr>
                    <th class='left'>&nbsp;</th>
                    <td><input type='submit' name='grade_comments' value='$langGradeOk'></td>
                </tr>
                </table>
                </fieldset>
                </form><br>";
    } else {
        Session::Messages($m['WorkNoSubmission'], 'caution');
        redirect_to_home_page('modules/work/index.php?course='.$course_code.'&id='.$id);
    }
}
