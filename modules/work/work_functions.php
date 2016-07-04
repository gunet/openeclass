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

// Print a two-cell table row with that title, if the content is non-empty
function table_row($title, $content, $html = false) {
    global $tool_content;

    if ($html) {
        $content = standard_text_escape($content);
    } else {
        $content = htmlspecialchars($content);
    }
    if (strlen(trim($content))) {
        $tool_content .= "
                        <div class='row margin-bottom-fat'>
                            <div class='col-sm-3'>
                                <strong>$title:</strong>
                            </div>
                            <div class='col-sm-9'>$content
                            </div>
                        </div>";
    }
}

// Find secret subdir of this assignment - if a secret subdir isn't set,
// use the assignment's id instead. Also insures that secret subdir exists
function work_secret($id) {
    global $course_id, $workPath, $coursePath;

    $res =  Database::get()->querySingle("SELECT secret_directory FROM assignment WHERE course_id = ?d AND id = ?d", $course_id, $id);
    if ($res) {
        if (!empty($res->secret_directory)) {
            $s = $res->secret_directory;
        } else {
            $s = $id;
        }
        if (!is_dir("$workPath/$s")) {
            make_dir("$workPath/$s");
        }
        return $s;
    } else {
        die("Error: group $gid doesn't exist");
    }
}

// Is this a group assignment?
function is_group_assignment($id) {
    global $course_id;

    $res = Database::get()->querySingle("SELECT group_submissions FROM assignment WHERE course_id = ?d AND id = ?d", $course_id, $id);
    if ($res) {
        if ($res->group_submissions == 0) {
            return FALSE;
        } else {
            return TRUE;
        }
    } else {
        die("Error: assignment $id doesn't exist");
    }
}

// Delete submissions to assignment $id if submitted by user $uid or group $gid
// Doesn't delete files if they are the same with $new_filename
function delete_submissions_by_uid($uid, $gid, $id, $new_filename = '') {
    global $m;

    $return = '';
    $res = Database::get()->queryArray("SELECT id, file_path, file_name, uid, group_id
				FROM assignment_submit
                                WHERE assignment_id = ?d AND
				      (uid = ?d OR group_id = ?d)", $id, $uid, $gid);
    foreach ($res as $row) {
        if ($row->file_path != $new_filename) {
            @unlink("$GLOBALS[workPath]/$row->file_path");
        }
        Database::get()->query("DELETE FROM assignment_submit WHERE id = ?d", $row->id);
        if ($GLOBALS['uid'] == $row->uid) {
            $return .= $m['deleted_work_by_user'];
        } else {
            $return .= $m['deleted_work_by_group'];
        }
        $return .= ' "<i>' . q($row->file_name) . '</i>". ';
    }
    return $return;
}

// Find submissions by a user (or the user's groups)
function find_submissions($is_group_assignment, $uid, $id, $gids) {

    if ($is_group_assignment AND count($gids)) {
        $groups_sql = join(', ', array_keys($gids));
        $res = Database::get()->queryArray("SELECT id, uid, group_id, submission_date,
					file_path, file_name, comments, grade,
					grade_comments, grade_submission_date
					FROM assignment_submit
                                        WHERE assignment_id = ?d AND
                                        group_id IN ($groups_sql)", $id);
    } else {
        $res = Database::get()->queryArray("SELECT id, grade FROM assignment_submit
                                        WHERE assignment_id = ?d AND uid = ?d", $id ,$uid);
    }
    $subs = array();
    if ($res) {
        foreach ($res as $row) {
            $subs[] = $row;
        }
    }
    return $subs;
}

// Returns grade, if submission has been graded, or "Yes" (translated) if
// there is a comment by the professor but no grade, or FALSE if neither
// grade or professor comment is set
function submission_grade($subid) {
    global $m;

    $res = Database::get()->querySingle("SELECT grade, grade_comments
                                                FROM assignment_submit
                                                WHERE id = ?d", $subid);
    if ($res) {
        $grade = trim($res->grade);
        if (!empty($grade)) {
            return $grade;
        } elseif (!empty($res->grade_comments)) {
            return $m['yes'];
        } else {
            return FALSE;
        }
    } else {
        return FALSE;
    }
}

// Check if a file has been submitted by user uid or by the user's group,
// and has been graded. Returns the submission id or the whole
// submission details row (depending on ret_val), or FALSE if no graded
// assignments were found.
function was_graded($uid, $id, $ret_val = FALSE) {
    global $course_id;
    $res =Database::get()->queryArray("SELECT * FROM assignment_submit
                                  WHERE assignment_id = ?d AND (uid = ?d OR
                                    group_id IN (SELECT group_id FROM `group` AS grp,
                                        group_members AS members
                                        WHERE grp.id = members.group_id AND
                                        user_id = ?d AND course_id = ?d))", $id, $uid, $uid, $course_id);
    if ($res) {
        foreach ($res as $row) {
            if ($row->grade) {
                if ($ret_val) {
                    return $row;
                } else {
                    return $row->id;
                }
            }
        }
    } else {
        return FALSE;
    }
}

// Show details of a submission
function show_submission_details($id) {
    global $uid, $m, $langSubmittedAndGraded, $tool_content, $course_code,
           $langAutoJudgeEnable, $langAutoJudgeShowWorkResultRpt;
    $sub = Database::get()->querySingle("SELECT * FROM assignment_submit WHERE id = ?d", $id);
    if (!$sub) {
        die("Error: submission $id doesn't exist.");
    }
    if (!empty($sub->grade) or !empty($sub->grade_comment)) {
        $graded = TRUE;
        $notice = $langSubmittedAndGraded;
    } else {
        $graded = FALSE;
        $notice = $GLOBALS['langSubmitted'];
    }

    if ($sub->uid != $uid) {
        $notice .= "<br>$m[submitted_by_other_member] " .
                "<a href='../group/group_space.php?course=$course_code&amp;group_id=$sub->group_id'>" .
                "$m[your_group] " . gid_to_name($sub->group_id) . "</a> (" . display_user($sub->uid) . ")";
    } elseif ($sub->group_id) {
        $notice .= "<br>$m[groupsubmit] " .
                "<a href='../group/group_space.php?course=$course_code&amp;group_id=$sub->group_id'>" .
                "$m[ofgroup] " . gid_to_name($sub->group_id) . "</a>";
    }

    $tool_content .= "
        <div class='panel panel-default'>
            <div class='panel-heading list-header'>
                <h3 class='panel-title'>$m[SubmissionWorkInfo]</h3>
            </div>
            <div class='panel-body'>
                <div class='row margin-bottom-fat'>
                    <div class='col-sm-3'>
                        <strong>".$m['SubmissionStatusWorkInfo'].":</strong>
                    </div>
                    <div class='col-sm-9'>$notice
                    </div>
                </div>
                <div class='row margin-bottom-fat'>
                    <div class='col-sm-3'>
                        <strong>" . $m['grade'] . ":</strong>
                    </div>
                    <div class='col-sm-9'>" . $sub->grade . "
                    </div>
                </div>
                <div class='row margin-bottom-fat'>
                    <div class='col-sm-3'>
                        <strong>" . $m['gradecomments'] . ":</strong>
                    </div>
                    <div class='col-sm-9'>" . $sub->grade_comments . "
                    </div>
                </div>
                <div class='row margin-bottom-fat'>
                    <div class='col-sm-3'>
                        <strong>" . $m['sub_date'] . ":</strong>
                    </div>
                    <div class='col-sm-9'>" . $sub->submission_date . "
                    </div>
                </div>
                <div class='row margin-bottom-fat'>
                    <div class='col-sm-3'>
                        <strong>" . $m['filename'] . ":</strong>
                    </div>
                    <div class='col-sm-9'><a href='$_SERVER[SCRIPT_NAME]?course=$course_code&amp;get=$sub->id'>" . q($sub->file_name) . "</a>
                    </div>
                </div>";
                if(AutojudgeApp::getAutojudge()->isEnabled()) {
                $reportlink = "work_result_rpt.php?course=$course_code&amp;assignment=$sub->assignment_id&amp;submission=$sub->id";
                $tool_content .= "
                <div class='row margin-bottom-fat'>
                    <div class='col-sm-3'>
                        <strong>" . $langAutoJudgeEnable . ":</strong>
                    </div>
                    <div class='col-sm-9'><a href='$reportlink'> $langAutoJudgeShowWorkResultRpt</a>
                    </div>
                </div>";
                }
            table_row($m['comments'], $sub->comments, true);
$tool_content .= "
            </div>
        </div>
            ";
}

// Check if a file has been submitted by user uid or group gid
// for assignment id. Returns 'user' if by user, 'group' if by group
function was_submitted($uid, $gid, $id) {

    $q = Database::get()->querySingle("SELECT uid, group_id
			      FROM assignment_submit
			      WHERE assignment_id = ?d AND
				    (uid = ?d or group_id = ?d)", $id, $uid, $gid);
    if ($q) {
        if ($q->uid == $uid) {
            return 'user';
        } else {
            return 'group';
        }
    } else {
        return false;
    }
}

// Remove extension and directory from filename
function basename_noext($f) {
    return preg_replace('{\.[^\.]*$}', '', basename($f));
}

// Disallow '..' and initial '/' in filenames
function cleanup_filename($f) {
    if (preg_match('{/\.\./}', $f) or
            preg_match('{^\.\./}', $f)) {
        die("Error: up-dir detected in filename: $f");
    }
    $f = preg_replace('{^/+}', '', $f);
    return preg_replace('{//}', '/', $f);
}


/**
 * @brief Export assignment's grades to CSV file
 * @global type $course_code
 * @global type $course_id
 * @global type $langSurname
 * @global type $langName
 * @global type $langAm
 * @global type $langUsername
 * @global type $langEmail
 * @global type $langGradebookGrade
 * @param type $id
 */
function export_grades_to_csv($id) {
    
    global $course_code, $course_id,
           $langSurname, $langName, $langAm, 
           $langUsername, $langEmail, $langGradebookGrade;
    
    $csv = new CSV();    
    $csv->filename = $course_code . "_" . $id . "_grades_list.csv";
    $csv->outputHeaders();
    // additional security
    $q = Database::get()->querySingle("SELECT id, title FROM assignment 
                            WHERE id = ?d AND course_id = ?d", $id, $course_id);
    if ($q) {
        $assignment_id = $q->id;
        $title = $q->title;
        $csv->outputRecord($title);
        $csv->outputRecord();
        $csv->outputRecord($langSurname, $langName, $langAm, $langUsername, $langEmail, $langGradebookGrade);
        $sql = Database::get()->queryArray("SELECT uid, grade FROM assignment_submit
                        WHERE assignment_id = ?d", $assignment_id);
        foreach ($sql as $data) {
            $entries = Database::get()->querySingle('SELECT surname, givenname, username, am, email 
                        FROM user
                        WHERE id = ?d',
                        $data->uid);
            $csv->outputRecord($entries->surname, $entries->givenname, $entries->am,
                    $entries->username, $entries->email, $data->grade);
            $csv->outputRecord();
        }
    }
    exit;
}