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
 * ========================================================================

  ============================================================================
  @Description: Main script for the work tool
  ============================================================================
 */


$require_current_course = true;
$require_login = true;
$require_help = true;
$helpTopic = 'Work';

require_once '../../include/baseTheme.php';
require_once 'include/lib/forcedownload.php';

// For using with the pop-up calendar
require_once 'jscalendar.inc.php';
require_once 'include/lib/modalboxhelper.class.php';
require_once 'include/lib/multimediahelper.class.php';
ModalBoxHelper::loadModalBox();

/* * ** The following is added for statistics purposes ** */
require_once 'include/action.php';
$action = new action();
$action->record(MODULE_ID_ASSIGN);
/* * *********************************** */

require_once 'work_functions.php';
require_once 'modules/group/group_functions.php';
require_once 'include/lib/fileUploadLib.inc.php';
require_once 'include/lib/fileManageLib.inc.php';
require_once 'include/sendMail.inc.php';
require_once 'modules/graphics/plotter.php';
require_once 'include/log.php';

$workPath = $webDir . "/courses/" . $course_code . "/work";

if (isset($_GET['get'])) {
    if (!send_file(intval($_GET['get']))) {
        $tool_content .= "<p class='caution'>$langFileNotFound</p>";
    }
}

// Only course admins can download all assignments in a zip file
if ($is_editor) {    
    if (isset($_GET['download'])) {
        include 'include/pclzip/pclzip.lib.php';
        // Allow unlimited time for creating the archive
        set_time_limit(0);
        download_assignments(intval($_GET['download']));
    }
}

$nameTools = $langWorks;

//-------------------------------------------
// main program
//-------------------------------------------

$works_url = array('url' => "$_SERVER[SCRIPT_NAME]?course=$course_code", 'name' => $langWorks);

if ($is_editor) {
    load_js('tools.js');
    $email_notify = isset($_POST['email']) and $_POST['email'];
    if (isset($_POST['grade_comments'])) {
        $work_title = db_query_get_single_value("SELECT title FROM assignment WHERE id = $_POST[assignment]");
        $nameTools = $work_title;
        $navigation[] = $works_url;
        submit_grade_comments($_POST['assignment'], $_POST['submission'], $_POST['grade'], $_POST['comments'], $email_notify);
    } elseif (isset($_GET['add'])) {
        $nameTools = $langNewAssign;
        $navigation[] = $works_url;
        new_assignment();
    } elseif (isset($_POST['sid'])) {
        show_submission(intval($_POST['sid']));
    } elseif (isset($_POST['new_assign'])) {
        add_assignment($_POST['title'], $_POST['desc'], $_POST['WorkEnd'], $_POST['group_submissions']);
        show_assignments();
    } elseif (isset($_GET['as_id'])) {
        $as_id = $_GET['as_id'];
        delete_user_assignment($as_id);
    } elseif (isset($_POST['grades'])) {
        $nameTools = $langWorks;
        $navigation[] = $works_url;
        submit_grades(intval($_POST['grades_id']), $_POST['grades'], $email_notify);
    } elseif (isset($_REQUEST['id'])) {
        $id = intval($_REQUEST['id']);
        $work_title = db_query_get_single_value("SELECT title FROM assignment
                                                                WHERE id = $id");
        $work_id_url = array('url' => "$_SERVER[SCRIPT_NAME]?course=$course_code&id=$id",
            'name' => $work_title);
        if (isset($_POST['on_behalf_of'])) {
            if (isset($_POST['user_id'])) {
                $user_id = intval($_POST['user_id']);
            } else {
                $user_id = $uid;
            }
            $nameTools = $langAddGrade;
            $navigation[] = $works_url;
            $navigation[] = $work_id_url;
            submit_work($id, $user_id);
        } elseif (isset($_REQUEST['choice'])) {
            $choice = $_REQUEST['choice'];
            if ($choice == 'disable') {
                db_query("UPDATE assignment SET active = 0 WHERE id = $id");
                show_assignments($langAssignmentDeactivated);
            } elseif ($choice == 'enable') {
                db_query("UPDATE assignment SET active = 1 WHERE id = $id");
                show_assignments($langAssignmentActivated);
            } elseif ($choice == 'delete') {
                die("invalid option");
            } elseif ($choice == 'do_delete') {
                $nameTools = $m['WorkDelete'];
                $navigation[] = $works_url;
                delete_assignment($id);
            } elseif ($choice == 'edit') {
                $nameTools = $m['WorkEdit'];
                $navigation[] = $works_url;
                $navigation[] = $work_id_url;
                show_edit_assignment($id);
            } elseif ($choice == 'do_edit') {
                $nameTools = $langWorks;
                $navigation[] = $works_url;
                $navigation[] = $work_id_url;
                edit_assignment($id);
            } elseif ($choice = 'add') {
                $nameTools = $langAddGrade;
                $navigation[] = $works_url;
                $navigation[] = $work_id_url;
                show_submission_form($id, groups_with_no_submissions($id), true);
            } elseif ($choice = 'plain') {
                show_plain_view($id);
            }
        } else {
            $nameTools = $work_title;
            $navigation[] = $works_url;
            if (isset($_GET['disp_results'])) {
                show_assignment($id, false, true);
            } else {
                show_assignment($id);
            }
        }
    } else {
        $nameTools = $langWorks;
        show_assignments();
    }
} else {
    if (isset($_REQUEST['id'])) {
        $id = intval($_REQUEST['id']);
        if (isset($_POST['work_submit'])) {
            $nameTools = $m['SubmissionStatusWorkInfo'];
            $navigation[] = $works_url;
            $navigation[] = array('url' => "$_SERVER[SCRIPT_NAME]?course=$course_code&amp;id=$id", 'name' => $langWorks);
            submit_work($id);
        } else {
            $work_title = db_query_get_single_value("SELECT title FROM assignment WHERE id = $id");
            $nameTools = $work_title;
            $navigation[] = $works_url;
            show_student_assignment($id);
        }
    } else {
        show_student_assignments();
    }
}

add_units_navigation(TRUE);
draw($tool_content, 2, null, $head_content);

//-------------------------------------
// end of main program
//-------------------------------------
// Show details of a student's submission to professor
function show_submission($sid) {
    global $tool_content, $works_url, $langSubmissionDescr, $langNotice3, $course_code;

    $nameTools = $langWorks;
    $navigation[] = $works_url;

    if ($sub = mysql_fetch_array(db_query("SELECT * FROM assignment_submit WHERE id = '$sid'"))) {

        $tool_content .= "<p>$langSubmissionDescr" .
                q(uid_to_name($sub['uid'])) .
                $sub['submission_date'] .
                "<a href='$GLOBALS[urlServer]$GLOBALS[currentCourseID]" .
                "/work/$sub[file_path]'>$sub[file_name]</a>";
        if (!empty($sub['comments'])) {
            $tool_content .= " $langNotice3: $sub[comments]";
        }
        $tool_content .= "</p>\n";
    } else {
        $tool_content .= "<p class='caution'>error - no such submission with id $sid</p>\n";
    }
}

// insert the assignment into the database
function add_assignment($title, $desc, $deadline, $group_submissions) {
    global $tool_content, $workPath, $course_id;

    $secret = uniqid('');
    $desc = purify($desc);
    if (@mkdir("$workPath/$secret", 0777)) {
        db_query("INSERT INTO assignment
                        (course_id, title, description, deadline, comments, submission_date, secret_directory,
                        group_submissions) VALUES
                        ($course_id, " . quote($title) . ", " . quote($desc) . ", " . quote($deadline) . ", ' ', NOW(),
                         '$secret', " . quote($group_submissions) . ")");
        $id = mysql_insert_id();
        Log::record($course_id, MODULE_ID_ASSIGN, LOG_INSERT, array('id' => $id,
            'title' => $title,
            'description' => $desc,
            'deadline' => $deadline,
            'secret' => $secret,
            'group' => $group_submissions));
        return true;
    } else {
        return false;
    }
}

function submit_work($id, $on_behalf_of = null) {
    global $tool_content, $workPath, $uid, $course_id, $works_url,
    $langUploadSuccess, $langBack, $langUploadError,
    $langExerciseNotPermit, $langUnwantedFiletype, $course_code,
    $langOnBehalfOfUserComment, $langOnBehalfOfGroupComment, $course_id;

    if (isset($on_behalf_of)) {
        $user_id = $on_behalf_of;
    } else {
        $user_id = $uid;
    }
    $submit_ok = FALSE; // Default do not allow submission
    if (isset($uid) && $uid) { // check if logged-in
        if ($GLOBALS['status'] == 10) { // user is guest
            $submit_ok = FALSE;
        } else { // user NOT guest
            if (isset($_SESSION['courses']) && isset($_SESSION['courses'][$_SESSION['dbname']])) {
                // user is registered to this lesson
                $res = db_query("SELECT CAST(UNIX_TIMESTAMP(deadline)-UNIX_TIMESTAMP(NOW()) AS SIGNED) AS time
                                        FROM assignment WHERE id = " . intval($id));
                $row = mysql_fetch_array($res);
                if ($row['time'] < 0 and !$on_behalf_of) {
                    $submit_ok = FALSE; // after assignment deadline
                } else {
                    $submit_ok = TRUE; // before deadline
                }
            } else {
                //user NOT registered to this lesson
                $submit_ok = FALSE;
            }
        }
    } //checks for submission validity end here

    $res = db_query("SELECT title, group_submissions FROM assignment WHERE course_id = " . intval($course_id) . " AND id = " . intval($id));
    $row = mysql_fetch_array($res);
    $title = $row['title'];
    $group_sub = $row['group_submissions'];
    $nav[] = $works_url;
    $nav[] = array('url' => "$_SERVER[SCRIPT_NAME]?id=$id", 'name' => $row['title']);

    if ($submit_ok) {
        if ($group_sub) {
            $group_id = isset($_POST['group_id']) ? intval($_POST['group_id']) : -1;
            $gids = user_group_info($on_behalf_of ? null : $user_id, $course_id);
            $local_name = isset($gids[$group_id]) ? greek_to_latin($gids[$group_id]) : '';
        } else {
            $group_id = 0;
            $local_name = uid_to_name($user_id);
            $am = mysql_fetch_array(db_query("SELECT am FROM user WHERE id = $user_id"));
            if (!empty($am[0])) {
                $local_name .= $am[0];
            }
            $local_name = greek_to_latin($local_name);
        }
        $local_name = replace_dangerous_char($local_name);
        if (isset($on_behalf_of) and
                (!isset($_FILES) or !$_FILES['userfile']['size'])) {
            $_FILES['userfile']['name'] = '';
            $_FILES['userfile']['tmp_name'] = '';
            $no_files = true;
        } else {
            $no_files = false;
        }

        validateUploadedFile($_FILES['userfile']['name'], 2);

        if (preg_match('/\.(ade|adp|bas|bat|chm|cmd|com|cpl|crt|exe|hlp|hta|' . 'inf|ins|isp|jse|lnk|mdb|mde|msc|msi|msp|mst|pcd|pif|reg|scr|sct|shs|' . 'shb|url|vbe|vbs|wsc|wsf|wsh)$/', $_FILES['userfile']['name'])) {
            $tool_content .= "<p class=\"caution\">$langUnwantedFiletype: {$_FILES['userfile']['name']}<br />";
            $tool_content .= "<a href=\"$_SERVER[SCRIPT_NAME]?course=$course_code&amp;id=$id\">$langBack</a></p><br />";
            return;
        }
        $secret = work_secret($id);
        $ext = get_file_extension($_FILES['userfile']['name']);
        $filename = "$secret/$local_name" . (empty($ext) ? '' : '.' . $ext);
        if (!isset($on_behalf_of)) {
            $msg1 = delete_submissions_by_uid($user_id, -1, $id);
            if ($group_sub) {
                if (array_key_exists($group_id, $gids)) {
                    $msg1 = delete_submissions_by_uid(-1, $group_id, $id);
                }
            }
        } else {
            $msg1 = '';
        }
        if ($no_files or move_uploaded_file($_FILES['userfile']['tmp_name'], "$workPath/$filename")) {
            if ($no_files) {
                $filename = '';
            } else {
                @chmod("$workPath/$filename", 0644);
            }
            $msg2 = $langUploadSuccess;
            $submit_ip = quote($_SERVER['REMOTE_ADDR']);
            if (isset($on_behalf_of)) {
                if ($group_sub) {
                    $auto_comments = sprintf($langOnBehalfOfGroupComment, uid_to_name($uid), $gids[$group_id]);
                } else {
                    $auto_comments = sprintf($langOnBehalfOfUserComment, uid_to_name($uid), uid_to_name($user_id));
                }
                $stud_comments = quote($auto_comments);
                $grade_comments = quote($_POST['stud_comments']);
                $grade = quote($_POST['grade']);
                $grade_ip = $submit_ip;
            } else {
                $stud_comments = $_POST['stud_comments'];
                $grade_comments = $grade = $grade_ip = "''";
            }
            if (!$group_sub or array_key_exists($group_id, $gids)) {
                $file_name = $_FILES['userfile']['name'];
                db_query("INSERT INTO assignment_submit
                                                 (uid, assignment_id, submission_date, submission_ip, file_path,
                                                  file_name, comments, grade, grade_comments, grade_submission_ip,
                                                  grade_submission_date, group_id)
                                          VALUES ($user_id, $id, NOW(), $submit_ip,
                                                        " . quote($filename) . ",
                                                        " . quote($file_name) . ",
                                                        " . quote($stud_comments) . ",
                                                        $grade, $grade_comments, $grade_ip, NOW(), $group_id)");
                $sid = mysql_insert_id();
                Log::record($course_id, MODULE_ID_ASSIGN, LOG_INSERT, array('id' => $sid,
                    'title' => $title,
                    'assignment_id' => $id,
                    'filepath' => $filename,
                    'filename' => $file_name,
                    'comments' => $stud_comments,
                    'group_id' => $group_id));
                if ($on_behalf_of and isset($_POST['email'])) {
                    $email_grade = autounquote($_POST['grade']);
                    $email_comments = "\n$auto_comments\n\n" .
                            autounquote($_POST['stud_comments']);
                    grade_email_notify($id, $sid, $email_grade, $email_comments);
                }
            }
            $tool_content .= "<p class='success'>$msg2<br />$msg1<br /><a href='$_SERVER[SCRIPT_NAME]?course=$course_code&amp;id=$id'>$langBack</a></p><br />";
        } else {
            $tool_content .= "<p class='caution'>$langUploadError<br /><a href='$_SERVER[SCRIPT_NAME]?course=$course_code'>$langBack</a></p><br />";
        }
    } else { // not submit_ok
        $tool_content .="<p class='caution'>$langExerciseNotPermit<br /><a href='$_SERVER[SCRIPT_NAME]?course=$course_code'>$langBack</a></p></br>";
    }
}

//  assignment - prof view only
function new_assignment() {
    global $tool_content, $m, $langAdd, $course_code;
    global $desc;
    global $end_cal_Work;
    global $langBack;

    $day = date("d");
    $month = date("m");
    $year = date("Y");

    $tool_content .= "
        <form action='$_SERVER[SCRIPT_NAME]?course=$course_code' method='post' onsubmit='return checkrequired(this, \"title\");'>
        <fieldset>
        <legend>$m[WorkInfo]</legend>
        <table class='tbl' width='100%'>
        <tr>
          <th>$m[title]:</th>
          <td><input type='text' name='title' size='55' /></td>
        </tr>
        <tr>
          <th>$m[description]:</th>
          <td>" . rich_text_editor('desc', 4, 20, $desc) . " </td>
        </tr>
        <tr>
          <th>$m[deadline]:</th>
          <td>$end_cal_Work</td>
        </tr>
        <tr>
          <th>$m[group_or_user]:</th>
          <td><input type='radio' id='user_button' name='group_submissions' value='0' checked='1' /><label for='user_button'>$m[user_work]</label>
          <br /><input type='radio' id='group_button' name='group_submissions' value='1' /><label for='group_button'>$m[group_work]</label></td>
        </tr>
        <tr>
          <th>&nbsp;</th>
          <td class='right'><input type='submit' name='new_assign' value='$langAdd' /></td>
        </tr>
        </table>
        </fieldset>
        </form>
        <br />";
    $tool_content .= "<p align='right'><a href='$_SERVER[SCRIPT_NAME]?course=$course_code'>$langBack</a></p>";
}

//form for editing
function show_edit_assignment($id) {
    global $tool_content, $m, $langEdit, $langBack, $course_code,
    $urlAppend, $works_url, $end_cal_Work_db;

    $res = db_query("SELECT * FROM assignment WHERE id = " . intval($id));
    $row = mysql_fetch_array($res);

    $deadline = $row['deadline'];

    $textarea = rich_text_editor('desc', 4, 20, $row['description']);
    $tool_content .= <<<cData
    <form action="$_SERVER[SCRIPT_NAME]?course=$course_code" method="post" onsubmit="return checkrequired(this, 'title');">
    <input type="hidden" name="id" value="$id" />
    <input type="hidden" name="choice" value="do_edit" />
    <fieldset>
    <legend>$m[WorkInfo]</legend>
    <table class='tbl'>
    <tr>
      <th>$m[title]:</th>
      <td><input type="text" name="title" size="45" value="${row['title']}" /></td>
    </tr>
    <tr>
      <th valign='top'>$m[description]:</th>
      <td>$textarea</td>
    </tr>
cData;
    $comments = trim($row['comments']);
    if (!empty($comments)) {
        $tool_content .= "
                <tr>
                <th>$m[comments]:</th>
                <td>" . rich_text_editor('comments', 5, 65, $comments) . "</td>
                </tr>";
    }

    if ($row['group_submissions'] == '0') {
        $group_checked_0 = ' checked="1"';
        $group_checked_1 = '';
    } else {
        $group_checked_0 = '';
        $group_checked_1 = ' checked="1"';
    }
    $tool_content .= "
    <tr>
      <th valign='top'>$m[deadline]:</th>
      <td>" . getJsDeadline($deadline) . "</td>
    </tr>
    <tr>
      <th valign='top'>$m[group_or_user]:</th>
      <td><input type='radio' id='user_button' name='group_submissions' value='0'$group_checked_0 />
          <label for='user_button'>$m[user_work]</label><br />
          <input type='radio' id='group_button' name='group_submissions' value='1'$group_checked_1 />
          <label for='group_button'>$m[group_work]</label></td>
    </tr>
    <tr>
      <th>&nbsp;</th>
      <td><input type='submit' name='do_edit' value='$langEdit' /></td>
    </tr>
    </table>
    </fieldset>
    </form>";

    $tool_content .= "<br /><div align='right'><a href='$_SERVER[SCRIPT_NAME]?course=$course_code'>$langBack</ul></div>";
}

// edit assignment
function edit_assignment($id) {

    global $tool_content, $langBackAssignment, $langEditSuccess,
    $langEditError, $course_code, $works_url, $course_id;

    $nav[] = $works_url;
    $nav[] = array("url" => "$_SERVER[SCRIPT_NAME]?id=$id", "name" => $_POST['title']);

    $title = trim($_POST['title']);
    $description = purify($_POST['desc']);
    $deadline = $_POST['WorkEnd'];
    $group_submissions = $_POST['group_submissions'];

    if (!isset($_POST['comments'])) {
        $comments = "''";
    } else {
        $comments = quote(purify($_POST['comments']));
    }
    if (db_query("UPDATE assignment SET
                                title = " . quote($title) . ",
                                description = " . quote($description) . ",
                                group_submissions = " . quote($group_submissions) . ",
                                comments = $comments,
                                deadline = " . quote($deadline) . "
                        WHERE course_id = $course_id AND id='$id'")) {
        $title = autounquote($_POST['title']);
        $tool_content .= "<p class='success'>$langEditSuccess<br />
                                        <a href='$_SERVER[SCRIPT_NAME]?course=$course_code&amp;id=$id'>$langBackAssignment '$title'</a>
                                        </p><br />";

        Log::record($course_id, MODULE_ID_ASSIGN, LOG_MODIFY, array('id' => $id,
            'title' => $title,
            'description' => $description,
            'deadline' => $deadline,
            'group' => $group_submissions));
    } else {
        $tool_content .="<p class='caution'>$langEditError<br /><a href='$_SERVER[SCRIPT_NAME]?course=$course_code&id=$id'>$langBackAssignment '$title'</a></p><br />";
    }
}

/**
 * @brief delete assignment
 * @global type $tool_content
 * @global string $workPath
 * @global type $course_code
 * @global type $webDir
 * @global type $langBack
 * @global type $langDeleted
 * @global type $course_id
 * @param type $id
 */
function delete_assignment($id) {

    global $tool_content, $workPath, $course_code, $webDir, $langBack, $langDeleted, $course_id;

    $secret = work_secret($id);
    $q = db_query("SELECT title FROM assignment WHERE course_id = " . quote($course_id) . " AND id = " . quote($id));
    $row = mysql_fetch_row($q);
    $title = $row[0];
    db_query("DELETE FROM assignment WHERE course_id = $course_id AND id = $id");
    db_query("DELETE FROM assignment_submit WHERE assignment_id = $id");
    move_dir("$workPath/$secret", "$webDir/courses/garbage/${course_code}_work_${id}_$secret");

    Log::record($course_id, MODULE_ID_ASSIGN, LOG_DELETE, array('id' => $id,
        'title' => $title));

    $tool_content .= "<p class='success'>$langDeleted<br />
                        <a href='$_SERVER[SCRIPT_NAME]?course=$course_code'>$langBack</a></p>";
}

/**
 * @brief delete user assignment
 * @global string $tool_content
 * @global type $course_id
 * @global type $course_code
 * @global type $webDir
 * @global type $langBack
 * @global type $langDeleted
 * @param type $id
 */
function delete_user_assignment($id) {
    global $tool_content, $course_code, $webDir, $langBack, $langDeleted;

    $filename = db_query_get_single_value("SELECT file_path FROM assignment_submit WHERE id = $id");
    $file = $webDir . "/courses/" . $course_code . "/work/" . $filename;
    if (my_delete($file)) {
        db_query("DELETE FROM assignment_submit WHERE id = $id");
        $tool_content .= "<p class='success'>$langDeleted<br />
                        <a href='$_SERVER[SCRIPT_NAME]?course=$course_code'>" . $langBack . "</a></p>";
    }
}

/**
 * @brief display user assignment
 * @global type $tool_content
 * @global type $m
 * @global type $uid
 * @global type $langUserOnly
 * @global type $langBack
 * @global type $course_code
 * @global type $course_id
 * @global type $course_code
 * @param type $id
 */
function show_student_assignment($id) {
    global $tool_content, $m, $uid, $langUserOnly, $langBack,
    $course_code, $course_id, $course_code;

    $user_group_info = user_group_info($uid, $course_id);
    $res = db_query("SELECT *, CAST(UNIX_TIMESTAMP(deadline)-UNIX_TIMESTAMP(NOW()) AS SIGNED) AS time
                                 FROM assignment WHERE course_id = $course_id AND id = $id");

    $row = mysql_fetch_array($res);

    assignment_details($id, $row);

    $submit_ok = ($row['time'] > 0);

    if (!$uid) {
        $tool_content .= "<p>$langUserOnly</p>";
        $submit_ok = FALSE;
    } elseif ($GLOBALS['status'] == 10) {
        $tool_content .= "\n  <p class='alert1'>$m[noguest]</p>";
        $submit_ok = FALSE;
    } else {
        foreach (find_submissions($row['group_submissions'], $uid, $id, $user_group_info) as $sub) {
            if ($sub['grade'] != '') {
                $submit_ok = false;
            }
            show_submission_details($sub['id']);
        }
    }
    if ($submit_ok) {
        show_submission_form($id, $user_group_info);
    }
    $tool_content .= "<br/><p align='right'><a href='$_SERVER[SCRIPT_NAME]?course=$course_code'>$langBack</a></p>";
}

function show_submission_form($id, $user_group_info, $on_behalf_of = false) {
    global $tool_content, $m, $langWorkFile, $langSendFile, $langSubmit, $uid, $langNotice3, $gid, $is_member,
    $urlAppend, $langGroupSpaceLink, $langOnBehalfOf, $course_code;

    $group_select_hidden_input = $group_select_form = '';
    $is_group_assignment = is_group_assignment($id);
    if ($is_group_assignment) {
        if (!$on_behalf_of) {
            if (count($user_group_info) == 1) {
                $gids = array_keys($user_group_info);
                $group_link = $urlAppend . '/modules/group/document.php?gid=' . $gids[0];
                $group_select_hidden_input = "<input type='hidden' name='group_id' value='$gids[0]' />";
            } elseif ($user_group_info) {
                $group_select_form = "<tr><th class='left'>$langGroupSpaceLink:</th><td>" .
                        selection($user_group_info, 'group_id') . "</td></tr>";
            } else {
                $group_link = $urlAppend . '/modules/group/group.php';
                $tool_content .= "<p class='alert1'>$m[this_is_group_assignment] <br />" .
                        sprintf(count($user_group_info) ?
                                        $m['group_assignment_publish'] :
                                        $m['group_assignment_no_groups'], $group_link) .
                        "</p>\n";
            }
        } else {
            $group_select_form = "<tr><th class='left'>$langGroupSpaceLink:</th><td>" .
                    selection(groups_with_no_submissions($id), 'group_id') . "</td></tr>";
        }
    }
    $notice = $on_behalf_of ? '' : "<br />$langNotice3";
    $extra = $on_behalf_of ? "<tr><th class='left'>$m[grade]</th>
                                     <td><input type='text' name='grade' maxlength='3' size='3'>
                                         <input type='hidden' name='on_behalf_of' value='1'></td></tr>
                                 <tr><th><label for='email_button'>$m[email_users]:</label></th>
                                     <td><input type='checkbox' value='1' id='email_button' name='email'></td></tr>" : '';
    if (!$is_group_assignment or count($user_group_info) or $on_behalf_of) {
        $tool_content .= "
                     <form enctype='multipart/form-data' action='$_SERVER[SCRIPT_NAME]?course=$course_code' method='post'>
                        <input type='hidden' name='id' value='$id' />$group_select_hidden_input
                        <fieldset>
                        <legend>$langSubmit</legend>
                        <table width='100%' class='tbl'>
                        $group_select_form
                        <tr>
                          <th class='left' width='150'>$langWorkFile:</th>
                          <td><input type='file' name='userfile' /></td>
                        </tr>
                        <tr>
                          <th class='left'>$m[comments]:</th>
                          <td><textarea name='stud_comments' rows='5' cols='55'></textarea></td>
                        </tr>
                        $extra
                        <tr>
                          <th>&nbsp;</th>
                          <td align='right'><input type='submit' value='$langSubmit' name='work_submit' />$notice</td>
                        </tr>
                        </table>
                        </fieldset>
                     </form>
                     <p align='right'><small>$GLOBALS[langMaxFileSize] " .
                ini_get('upload_max_filesize') . "</small></p>";
    }
}

// Print a box with the details of an assignment
function assignment_details($id, $row, $message = null) {
    global $tool_content, $is_editor, $course_code, $themeimg, $m, $langDaysLeft,
    $langDays, $langWEndDeadline, $langNEndDeadLine, $langNEndDeadline,
    $langEndDeadline, $langDelAssign, $langAddGrade, $langZipDownload,
    $langSaved, $langGraphResults, $langConfirmDelete;

    if ($is_editor) {
        $tool_content .= "
            <div id='operations_container'>
              <ul id='opslist'>
              <li><a href='$_SERVER[SCRIPT_NAME]?course=$course_code&amp;id=$id&amp;choice=do_delete' onClick='return confirmation(\"" . $langConfirmDelete . "\");'>$langDelAssign</a></li>
                <li><a href='$_SERVER[SCRIPT_NAME]?course=$course_code&amp;download=$id'>$langZipDownload</a></li>
		<li><a href='$_SERVER[SCRIPT_NAME]?course=$course_code&amp;id=$id&amp;disp_results=true'>$langGraphResults</a></li>
		<li><a href='$_SERVER[SCRIPT_NAME]?course=$course_code&amp;id=$id&amp;choice=add'>$langAddGrade</a></li>
              </ul>
            </div>";
    }

    if (isset($message)) {
        $tool_content .= "<p class='success'>$langSaved</p>";
    }
    $tool_content .= "
        <fieldset>
        <legend>" . $m['WorkInfo'];
    if ($is_editor) {
        $tool_content .= "&nbsp;
                 <a href='$_SERVER[SCRIPT_NAME]?course=$course_code&amp;id=$id&amp;choice=edit'>
                 <img src='$themeimg/edit.png' alt='$m[edit]' />
                 </a>";
    }
    $tool_content .= "</legend>
        <table class='tbl'>
        <tr>
          <th width='150'>$m[title]:</th>
          <td>$row[title]</td>
        </tr>";
    $tool_content .= "
        <tr>
          <th valign='top'>$m[description]:</th>
          <td>$row[description]</td>
        </tr>";
    if (!empty($row['comments'])) {
        $tool_content .= "
                <tr>
                  <th class='left'>$m[comments]:</th>
                  <td>$row[comments]</td>
                </tr>";
    }
    $tool_content .= "
        <tr>
          <th>$m[start_date]:</th>
          <td>" . nice_format($row['submission_date'], true) . "</td>
        </tr>
        <tr>
          <th valign='top'>$m[deadline]:</th>
          <td>" . nice_format($row['deadline'], true) . " <br />";

    if ($row['time'] > 0) {
        $tool_content .= "<span>($langDaysLeft " . format_time_duration($row['time']) . ")</span></td>
                </tr>";
    } else {
        $tool_content .= "<span class='expired'>$langEndDeadline</span></td>
                </tr>";
    }
    $tool_content .= "
        <tr>
          <th>$m[group_or_user]:</th>
          <td>";
    if ($row['group_submissions'] == '0') {
        $tool_content .= "$m[user_work]</td>
        </tr>";
    } else {
        $tool_content .= "$m[group_work]</td>
        </tr>";
    }
    $tool_content .= "
        </table>
        </fieldset>";
}

// Show a table header which is a link with the appropriate sorting
// parameters - $attrib should contain any extra attributes requered in
// the <th> tags
function sort_link($title, $opt, $attrib = '') {
    global $tool_content, $course_code;
    $i = '';
    if (isset($_REQUEST['id'])) {
        $i = "&id=$_REQUEST[id]";
    }
    if (@($_REQUEST['sort'] == $opt)) {
        if (@($_REQUEST['rev'] == 1)) {
            $r = 0;
        } else {
            $r = 1;
        }
        $tool_content .= "
                  <th $attrib><a href='$_SERVER[SCRIPT_NAME]?course=$course_code&amp;sort=$opt&rev=$r$i'>" . "$title</a></th>";
    } else {
        $tool_content .= "
                  <th $attrib><a href='$_SERVER[SCRIPT_NAME]?course=$course_code&amp;sort=$opt$i'>$title</a></th>";
    }
}

// show assignment - prof view only
// the optional message appears instead of assignment details
function show_assignment($id, $message = false, $display_graph_results = false) {
    global $tool_content, $m, $langBack, $langNoSubmissions, $langSubmissions,
    $langEndDeadline, $langWEndDeadline, $langNEndDeadline,
    $langDays, $langDaysLeft, $langGradeOk, $course_code, $webDir, $urlServer,
    $langGraphResults, $m, $course_code, $themeimg, $works_url, $course_id, $langDelWarnUserAssignment;


    $res = db_query("SELECT *, CAST(UNIX_TIMESTAMP(deadline)-UNIX_TIMESTAMP(NOW()) AS SIGNED) AS time
                                 FROM assignment
                                 WHERE course_id = $course_id AND id = $id");
    $row = mysql_fetch_array($res);

    $nav[] = $works_url;
    if ($message) {
        assignment_details($id, $row, $message);
    } else {
        assignment_details($id, $row);
    }

    $rev = (@($_REQUEST['rev'] == 1)) ? ' DESC' : '';
    if (isset($_REQUEST['sort'])) {
        if ($_REQUEST['sort'] == 'am') {
            $order = 'am';
        } elseif ($_REQUEST['sort'] == 'date') {
            $order = 'submission_date';
        } elseif ($_REQUEST['sort'] == 'grade') {
            $order = 'grade';
        } elseif ($_REQUEST['sort'] == 'filename') {
            $order = 'file_name';
        } else {
            $order = 'surname';
        }
    } else {
        $order = 'surname';
    }

    $result = db_query("SELECT *
		FROM assignment_submit AS assign, user
		WHERE assign.assignment_id = $id AND user.id = assign.uid
		ORDER BY $order $rev");

    // Used to display grades distribution chart
    list($graded_submissions_count) = mysql_fetch_row(
            db_query("SELECT COUNT(*)
                                                 FROM assignment_submit AS assign, user
                                                 WHERE assign.assignment_id = $id AND
                                                       user.id = assign.uid AND
                                                       assign.grade <> ''"));

    $num_results = mysql_num_rows($result);
    if ($num_results > 0) {
        if ($num_results == 1) {
            $num_of_submissions = $m['one_submission'];
        } else {
            $num_of_submissions = sprintf("$m[more_submissions]", $num_results);
        }

        $gradeOccurances = array(); // Named array to hold grade occurances/stats
        $gradesExists = 0;
        while ($row = mysql_fetch_array($result)) {
            $theGrade = $row['grade'];
            if ($theGrade) {
                $gradesExists = 1;
                if (!isset($gradeOccurances[$theGrade])) {
                    $gradeOccurances[$theGrade] = 1;
                } else {
                    if ($gradesExists) {
                        ++$gradeOccurances[$theGrade];
                    }
                }
            }
        }
        if (!$display_graph_results) {
            $result = db_query("SELECT * FROM assignment_submit AS assign, user
                                                   WHERE assign.assignment_id='$id' AND
                                                         user.id = assign.uid
                                                   ORDER BY $order $rev");

            $tool_content .= "
                        <form action='$_SERVER[SCRIPT_NAME]?course=$course_code' method='post'>
                        <input type='hidden' name='grades_id' value='$id' />
                        <p><div class='sub_title1'>$langSubmissions:</div><p>
                        <p>$num_of_submissions</p>
                        <table width='100%' class='sortable'>
                        <tr>
                      <th width='3'>&nbsp;</th>";
            sort_link($m['username'], 'username');
            sort_link($m['am'], 'am');
            sort_link($m['filename'], 'filename');
            sort_link($m['sub_date'], 'date');
            sort_link($m['grade'], 'grade');
            $tool_content .= "</tr>";

            $i = 1;
            while ($row = mysql_fetch_array($result)) {
                //is it a group assignment?
                if (!empty($row['group_id'])) {
                    $subContentGroup = "$m[groupsubmit] " .
                            "<a href='../group/group_space.php?course=$course_code&amp;group_id=$row[group_id]'>" .
                            "$m[ofgroup] " . gid_to_name($row['group_id']) . "</a>";
                } else {
                    $subContentGroup = '';
                }

                $uid_2_name = display_user($row['uid']);
                $stud_am = mysql_fetch_array(db_query("SELECT am FROM user WHERE id = $row[uid]"));
                if ($i % 2 == 1) {
                    $row_color = "class='even'";
                } else {
                    $row_color = "class='odd'";
                }
                $filelink = empty($row['file_name']) ? '&nbsp;' :
                        ("<a href='$_SERVER[SCRIPT_NAME]?course=$course_code&amp;get=$row[id]'>" .
                        q($row['file_name']) . "</a>");
                $tool_content .= "
                                <tr $row_color>
                                <td align='right' width='4' rowspan='2' valign='top'>$i.</td>
                                <td>${uid_2_name}</td>
                                <td width='85'>" . q($stud_am[0]) . "</td>
                                <td width='180'>$filelink
                                <a href='$_SERVER[SCRIPT_NAME]?course=$course_code&amp;as_id=$row[id]' onClick='return confirmation(\"$langDelWarnUserAssignment\");'>
                                 <img src='$themeimg/delete.png' title='$m[WorkDelete]' />
                                </a>                                        
                                </td>
                                <td width='100'>" . nice_format($row['submission_date'], TRUE) . "</td>
                                <td width='5'>
                                <div align='center'><input type='text' value='{$row['grade']}' maxlength='3' size='3' name='grades[{$row['id']}]'></div>
                                </td>
                                </tr>
                                <tr $row_color>
                                <td colspan='5'>
                                <div>$subContentGroup</div>";
                if (trim($row['comments'] != '')) {
                    $tool_content .= "<div style='margin-top: .5em;'><b>$m[comments]:</b> " .
                            q($row['comments']) . '</div>';
                }
                //professor comments
                $gradelink = "grade_edit.php?course=$course_code&amp;assignment=$id&amp;submission=$row[id]";
                if (trim($row['grade_comments'])) {
                    $label = $m['gradecomments'] . ':';
                    $icon = 'edit.png';
                    $comments = "<div class='smaller'>" . standard_text_escape($row['grade_comments']) . "</div>";
                } else {
                    $label = $m['addgradecomments'];
                    $icon = 'add.png';
                    $comments = '';
                }
                if ($row['grade_comments'] or $row['grade'] != '') {
                    $comments .= "<div class='smaller'><i>($m[grade_comment_date]: " .
                            nice_format($row['grade_submission_date']) . ")</i></div>";
                }
                $tool_content .= "<div style='padding-top: .5em;'><a href='$gradelink'><b>$label</b></a>
				  <a href='$gradelink'><img src='$themeimg/$icon'></a>
				  $comments
                                </td>
                                </tr>";
                $i++;
            } //END of While

            $tool_content .= "</table>
                        <p class='smaller right'><img src='$themeimg/email.png' alt='' >
                                $m[email_users]: <input type='checkbox' value='1' name='email'></p>
                        <p><input type='submit' name='submit_grades' value='$langGradeOk'></p>
                        </form>";
        }

        if ($display_graph_results) { // display pie chart with grades results
            if ($gradesExists) {
                $chart = new Plotter();
                $chart->setTitle("$langGraphResults");
                foreach ($gradeOccurances as $gradeValue => $gradeOccurance) {
                    $percentage = 100.0 * $gradeOccurance / $graded_submissions_count;
                    $chart->growWithPoint("$gradeValue ($percentage)", $percentage);
                }
                $tool_content .= $chart->plot();
            }
        }
    } else {
        $tool_content .= "
                      <p class='sub_title1'>$langSubmissions:</p>
                      <p class='alert1'>$langNoSubmissions</p>";
    }
    $tool_content .= "<br/>
                <p align='right'><a href='$_SERVER[SCRIPT_NAME]?course=$course_code'>$langBack</a></p>";
}

// show all the assignments - student view only
function show_student_assignments() {
    global $tool_content, $m, $uid, $course_id, $course_code,
    $langDaysLeft, $langDays, $langNoAssign, $urlServer,
    $course_code, $themeimg;

    $gids = user_group_info($uid, $course_id);

    $result = db_query("SELECT *, CAST(UNIX_TIMESTAMP(deadline)-UNIX_TIMESTAMP(NOW()) AS SIGNED) AS time
                                   FROM assignment
                                           WHERE course_id = $course_id AND active = 1
                                           ORDER BY deadline");

    if (mysql_num_rows($result)) {
        $tool_content .= "<table class='tbl_alt' width='100%'>
                                  <tr>
                                      <th colspan='2'>$m[title]</th>
                                      <th class='center'>$m[deadline]</th>
                                      <th class='center'>$m[submitted]</th>
                                      <th>$m[grade]</th>
                                  </tr>";
        $k = 0;
        while ($row = mysql_fetch_array($result)) {
            $title_temp = q($row['title']);
            $class = $k % 2 ? 'odd' : 'even';
            $tool_content .= "
                                <tr class='$class'>
                                    <td width='16'><img src='$themeimg/arrow.png' title='bullet' /></td>
                                    <td><a href='$_SERVER[SCRIPT_NAME]?course=$course_code&amp;id=$row[id]'>$title_temp</a></td>
                                    <td width='150' align='center'>" . nice_format($row['deadline'], TRUE);
            if ($row['time'] > 0) {
                $tool_content .= " (<span>$langDaysLeft" . format_time_duration($row['time']) . ")</span>";
            } else {
                $tool_content .= " (<span class='expired'>$m[expired]</span>)";
            }
            $tool_content .= "</td><td width='170' align='center'>";

            if ($submission = find_submissions(is_group_assignment($row['id']), $uid, $row['id'], $gids)) {
                foreach ($submission as $sub) {
                    if (isset($sub['group_id'])) { // if is a group assignment
                        $tool_content .= "<div style='padding-bottom: 5px;padding-top:5px;font-size:9px;'>($m[groupsubmit] " .
                                "<a href='../group/group_space.php?course=$course_code&amp;group_id=$sub[group_id]'>" .
                                "$m[ofgroup] " . gid_to_name($sub['group_id']) . "</a>)</div>";
                    }
                    $tool_content .= "<img src='$themeimg/checkbox_on.png' alt='$m[yes]' /><br />";
                }
            } else {
                $tool_content .= "<img src='$themeimg/checkbox_off.png' alt='$m[no]' />";
            }
            $tool_content .= "</td>
                                    <td width='30' align='center'>";
            foreach ($submission as $sub) {
                $grade = submission_grade($sub['id']);
                if (!$grade) {
                    $grade = "<div style='padding-bottom: 5px;padding-top:5px;'> - </div>";
                }
                $tool_content .= "<div style='padding-bottom: 5px;padding-top:5px;'>$grade</div>";
            }
            $tool_content .= "</td>
                                  </tr>";
            $k++;
        }
        $tool_content .= '
                                  </table>';
    } else {
        $tool_content .= "<p class='alert1'>$langNoAssign</p>";
    }
}

// show all the assignments
function show_assignments($message = null) {
    global $tool_content, $m, $langNoAssign, $langNewAssign, $langCommands,
    $course_code, $themeimg, $course_id, $langConfirmDelete;

    $result = db_query("SELECT * FROM assignment WHERE course_id = $course_id ORDER BY deadline");

    if (isset($message)) {
        $tool_content .="<p class='success'>$message</p><br />";
    }

    $tool_content .="
            <div id='operations_container'>
              <ul id='opslist'>
                <li><a href='$_SERVER[SCRIPT_NAME]?course=$course_code&amp;add=1'>$langNewAssign</a></li>
              </ul>
            </div>";

    if (mysql_num_rows($result)) {
        $tool_content .= "
                    <table width='100%' class='tbl_alt'>
                    <tr>
                      <th>$m[title]</th>
                      <th width='60'>$m[subm]</th>
                      <th width='60'>$m[nogr]</th>
                      <th width='130'>$m[deadline]</th>
                      <th width='60'>$langCommands</th>
                    </tr>";
        $index = 0;
        while ($row = mysql_fetch_array($result)) {
            // Check if assignement contains submissions
            $AssignementId = $row['id'];

            $num_submitted = db_query_get_single_value("SELECT COUNT(*) FROM assignment_submit
                                                WHERE assignment_id = $AssignementId");
            if (!$num_submitted) {
                $num_submitted = '&nbsp;';
            }

            $num_ungraded = db_query_get_single_value("SELECT COUNT(*) FROM assignment_submit
                                                WHERE assignment_id = $AssignementId AND grade=''");
            if (!$num_ungraded) {
                $num_ungraded = '&nbsp;';
            }
            if (!$row['active']) {
                $tool_content .= "\n<tr class = 'invisible'>";
            } else {
                if ($index % 2 == 0) {
                    $tool_content .= "\n<tr class='even'>";
                } else {
                    $tool_content .= "\n<tr class='odd'>";
                }
            }

            $tool_content .= "
			  <td><img src='$themeimg/arrow.png' alt=''>
			      <a href='$_SERVER[SCRIPT_NAME]?course=$course_code&amp;id=${row['id']}' ";
            $tool_content .= ">";
            $tool_content .= $row_title = q($row['title']);
            $tool_content .= "</a></td>
			  <td class='center'>$num_submitted</td>
			  <td class='center'>$num_ungraded</td>
			  <td class='center'>" . nice_format($row['deadline'], true) . "</td>
			  <td class='right'><a href='$_SERVER[SCRIPT_NAME]?course=$course_code&amp;id=$row[id]&amp;choice=edit'>
			  <img src='$themeimg/edit.png' alt='$m[edit]' />
			  </a> <a href='$_SERVER[SCRIPT_NAME]?course=$course_code&amp;id=$row[id]&amp;choice=do_delete' onClick='return confirmation(\"" . $langConfirmDelete . "\");'>
			  <img src='$themeimg/delete.png' alt='$m[delete]' /></a>";
            if ($row['active']) {
                $deactivate_temp = htmlspecialchars($m['deactivate']);
                $activate_temp = htmlspecialchars($m['activate']);
                $tool_content .= "<a href='$_SERVER[SCRIPT_NAME]?course=$course_code&amp;choice=disable&amp;id=$row[id]'><img src='$themeimg/visible.png' title='$deactivate_temp' /></a>";
            } else {
                $activate_temp = htmlspecialchars($m['activate']);
                $tool_content .= "<a href='$_SERVER[SCRIPT_NAME]?course=$course_code&amp;choice=enable&amp;id=$row[id]'><img src='$themeimg/invisible.png' title='$activate_temp' /></a>";
            }
            $tool_content .= "&nbsp;</td></tr>";
            $index++;
        }
        $tool_content .= '</table>';
    } else {
        $tool_content .= "\n<p class='alert1'>$langNoAssign</p>";
    }
}

// submit grade and comment for a student submission
function submit_grade_comments($id, $sid, $grade, $comment, $email) {
    global $tool_content, $langGrades, $langWorkWrongInput, $course_id;

    $stupid_user = 0;

    /*  If check expression is changed by nikos, in order to give to teacher the ability to
     * assign comments to a work without assigning grade. */
    if (!is_numeric($grade) && '' != $grade) {
        $tool_content .= $langWorkWrongInput;
        $stupid_user = 1;
    } else {
        db_query("UPDATE assignment_submit
                                 SET grade = " . quote($grade) . ",
                                     grade_comments = " . quote($comment) . ",
                                     grade_submission_date = NOW(),
                                     grade_submission_ip = '$_SERVER[REMOTE_ADDR]'
                                 WHERE id = $sid");
        list($title) = mysql_fetch_row(db_query("SELECT title FROM assignment WHERE id = $id"));
        Log::record($course_id, MODULE_ID_ASSIGN, LOG_MODIFY, array('id' => $sid,
            'title' => $title,
            'grade' => $grade,
            'comments' => $comment));
        if ($email) {
            grade_email_notify($id, $sid, $grade, $comment);
        }
    }
    if (!$stupid_user) {
        show_assignment($id, $langGrades);
    }
}

// submit grades to students
function submit_grades($grades_id, $grades, $email = false) {
    global $tool_content, $langGrades, $langWorkWrongInput, $course_id;

    $stupid_user = 0;

    foreach ($grades as $sid => $grade) {
        $sid = intval($sid);
        $val = mysql_fetch_row(db_query("SELECT grade from assignment_submit WHERE id = $sid"));
        if ($val[0] != $grade) {
            /*  If check expression is changed by nikos, in order to give to teacher
             * the ability to assign comments to a work without assigning grade. */
            if (!is_numeric($grade) && '' != $grade) {
                $stupid_user = 1;
            }
        }
    }

    if (!$stupid_user) {
        foreach ($grades as $sid => $grade) {
            $sid = intval($sid);
            $val = mysql_fetch_row(db_query("SELECT grade from assignment_submit WHERE id = $sid"));
            if ($val[0] != $grade) {
                db_query("UPDATE assignment_submit
                                                 SET grade = " . quote($grade) . ",
                                                     grade_submission_date = NOW(),
                                                     grade_submission_ip = '$_SERVER[REMOTE_ADDR]'
                                                 WHERE id = $sid");
                list($assign_id) = mysql_fetch_row(db_query("SELECT assignment_id FROM assignment_submit WHERE id = $sid"));
                list($title) = mysql_fetch_row(db_query("SELECT title FROM assignment
                                                                        WHERE assignment.id = $assign_id"));
                Log::record($course_id, MODULE_ID_ASSIGN, LOG_MODIFY, array('id' => $sid,
                    'title' => $title,
                    'grade' => $grade));
                if ($email) {
                    grade_email_notify($grades_id, $sid, $grade, '');
                }
            }
        }
        show_assignment($grades_id, $langGrades);
    } else {
        $tool_content .= "<div class='alert1'>$langWorkWrongInput</div>";
    }
}

// functions for downloading
function send_file($id) {
    global $course_code, $uid, $is_editor;

    $q = db_query("SELECT * FROM assignment_submit WHERE id = $id");
    if (!$q or !mysql_num_rows($q)) {
        return false;
    }
    $info = mysql_fetch_array($q);
    if ($info['group_id']) {
        initialize_group_info($info['group_id']);
    }
    if (!($is_editor or $info['uid'] == $uid or $GLOBALS['is_member'])) {
        return false;
    }
    send_file_to_client("$GLOBALS[workPath]/$info[file_path]", $info['file_name'], null, true);
    exit;
}

// Zip submissions to assignment $id and send it to user
function download_assignments($id) {
    
    global $workPath, $course_code;
    
    $secret = work_secret($id);
    $filename = "{$course_code}_work_$id.zip";  
    chdir($workPath);
    create_zip_index("$secret/index.html", $id);
    $zip = new PclZip($filename);
    $flag = $zip->create($secret, "work_$id", $secret);
    header("Content-Type: application/x-zip");
    header("Content-Disposition: attachment; filename=$filename");
    stop_output_buffering();
    @readfile($filename);
    @unlink($filename);
    exit;
}

// Create an index.html file for assignment $id listing user submissions
// Set $online to TRUE to get an online view (on the web) - else the
// index.html works for the zip file
function create_zip_index($path, $id, $online = FALSE) {
    global $charset, $m;

    $fp = fopen($path, "w");
    if (!$fp) {
        die("Unable to create assignment index file - aborting");
    }
    fputs($fp, '
<!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.01 Transitional//EN">
<html>
	<head>
		<meta http-equiv="Content-Type" content="text/html; charset=' . $charset . '">
                <style type="text/css">
                .sep td, th { border: 1px solid; }
                td { border: none; }
                table { border-collapse: collapse; border: 2px solid; }
                .sep { border-top: 2px solid black; }
                </style>
	</head>
	<body>
		<table width="95%" class="tbl">
			<tr>
				<th>' . $m['username'] . '</th>
				<th>' . $m['am'] . '</th>
				<th>' . $m['filename'] . '</th>
				<th>' . $m['sub_date'] . '</th>
				<th>' . $m['grade'] . '</th>
			</tr>');

    $result = db_query("SELECT * FROM assignment_submit
		WHERE assignment_id='$id' ORDER BY id");


    while ($row = mysql_fetch_array($result)) {
        $filename = basename($row['file_path']);
        $filelink = empty($filename) ? '&nbsp;' :
                ("<a href='$filename'>" . htmlspecialchars($filename) . '</a>');
        fputs($fp, '
			<tr class="sep">
				<td>' . q(uid_to_name($row['uid'])) . '</td>
				<td>' . q(uid_to_am($row['uid'])) . '</td>
				<td align="center">' . $filelink . '</td>
				<td align="center">' . $row['submission_date'] . '</td>
				<td align="center">' . $row['grade'] . '</td>
			</tr>');
        if (trim($row['comments'] != '')) {
            fputs($fp, "
			<tr><td colspan='6'><b>$m[comments]: " .
                    "</b>$row[comments]</td></tr>");
        }
        if (trim($row['grade_comments'] != '')) {
            fputs($fp, "
			<tr><td colspan='6'><b>$m[gradecomments]: " .
                    "</b>$row[grade_comments]</td></tr>");
        }
        if (!empty($row['group_id'])) {
            fputs($fp, "<tr><td colspan='6'>$m[groupsubmit] " .
                    "$m[ofgroup] $row[group_id]</td></tr>\n");
        }
    }
    fputs($fp, ' </table></body></html>');
    fclose($fp);
}

// Show a simple html page with grades and submissions
function show_plain_view($id) {
    global $workPath, $charset;

    $secret = work_secret($id);
    create_zip_index("$secret/index.html", $id, TRUE);
    header("Content-Type: text/html; charset=$charset");
    readfile("$workPath/$secret/index.html");
    exit;
}

// Notify students by email about grade/comment submission
// Send to single user for individual submissions or group members for group
// submissions
function grade_email_notify($assignment_id, $submission_id, $grade, $comments) {
    global $m, $currentCourseName, $urlServer, $course_code;
    static $title, $group;

    if (!isset($title)) {
        $res = db_query("SELECT title, group_submissions FROM assignment WHERE id = $assignment_id");
        list($title, $group) = mysql_fetch_row($res);
    }
    $res = db_query("SELECT uid, group_id, file_name, grade, grade_comments
                                FROM assignment_submit
                                WHERE id = $submission_id");
    $info = mysql_fetch_assoc($res);
    $subject = sprintf($m['work_email_subject'], $title);
    $body = sprintf($m['work_email_message'], $title, $currentCourseName) . "\n\n";
    if ($grade != '') {
        $body .= "$m[grade]: $grade\n";
    }
    if ($comments) {
        $body .= "$m[gradecomments]: $comments\n";
    }
    $body .= "\n$m[link_follows]\n{$urlServer}modules/work/work.php?course=$course_code&id=$assignment_id\n";
    if (!$group or !$info['group_id']) {
        send_mail_to_user_id($info['uid'], $subject, $body);
    } else {
        send_mail_to_group_id($info['group_id'], $subject, $body);
    }
}

function send_mail_to_group_id($gid, $subject, $body) {
    global $charset;
    $res = db_query("SELECT surname, givenname, email
                                FROM user, group_members AS members
                                WHERE members.group_id = $gid AND
                                      user.id = members.user_id");
    while ($info = mysql_fetch_assoc($res)) {
        send_mail('', '', "$info[givenname] $info[surname]", $info['email'], $subject, $body, $charset);
    }
}

function send_mail_to_user_id($uid, $subject, $body) {
    global $charset;
    list($surname, $givenname, $email) = mysql_fetch_row(db_query("SELECT surname, givenname, email
                FROM user WHERE id = $uid"));
    send_mail('', '', "$givenname $surname", $email, $subject, $body, $charset);
}

// Return a list of users with no submissions for assignment $id
function users_with_no_submissions($id) {
    global $course_id;

    $q = db_query("SELECT user.id AS id, surname, givenname
                              FROM user, course_user
                              WHERE user.id = course_user.user_id AND
                                    course_user.course_id = $course_id AND
                                    course_user.status = 5 AND
                                    user.id NOT IN (SELECT uid FROM assignment_submit
                                                          WHERE assignment_id = $id)");
    $users = array();
    while ($row = mysql_fetch_row($q)) {
        $users[$row[0]] = "$row[1] $row[2]";
    }
    return $users;
}

// Return a list of groups with no submissions for assignment $id
function groups_with_no_submissions($id) {
    global $course_id;

    $groups = user_group_info(null, $course_id);
    $q = db_query("SELECT group_id FROM assignment_submit
                              WHERE assignment_id = $id");
    while ($row = mysql_fetch_row($q)) {
        unset($groups[$row[0]]);
    }
    return $groups;
}
