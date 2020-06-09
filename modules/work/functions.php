<?php

/* ========================================================================
 * Open eClass 3.0
 * E-learning and Course Management System
 * ========================================================================
 * Copyright 2003-2017  Greek Universities Network - GUnet
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
 * @brief Print a two-cell table row with that title, if the content is non-empty
 * @global type $tool_content
 * @param type $title
 * @param type $content
 * @param type $html
 */
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

/**
 * @brief Show a table header which is a link with the appropriate sorting
    parameters - $attrib should contain any extra attributes requered in
    the <th> tags
 * @global type $tool_content
 * @global type $course_code
 * @param type $title
 * @param type $opt
 * @param type $attrib
 */
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
        $tool_content .= "<th $attrib><a href='$_SERVER[SCRIPT_NAME]?course=$course_code&amp;sort=$opt&rev=$r$i'>" . "$title</a></th>";
    } else {
        $tool_content .= "<th $attrib><a href='$_SERVER[SCRIPT_NAME]?course=$course_code&amp;sort=$opt$i'>$title</a></th>";
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
        $ass_cid = Database::get()->querySingle("SELECT course_id FROM assignment WHERE id = ?d", $id)->course_id;
        Database::get()->query("DELETE FROM assignment_submit WHERE id = ?d", $row->id);
        triggerGame($ass_cid, $row->uid, $id);
        triggerAssignmentAnalytics($ass_cid, $row->uid, $id, AssignmentAnalyticsEvent::ASSIGNMENTDL);
        triggerAssignmentAnalytics($ass_cid, $row->uid, $id, AssignmentAnalyticsEvent::ASSIGNMENTGRADE);
        if ($GLOBALS['uid'] == $row->uid) {
            $return .= $m['deleted_work_by_user'];
        } else {
            $return .= $m['deleted_work_by_group'];
        }
        $return .= ' "<i>' . q($row->file_name) . '</i>". ';
    }
    return $return;
}

//function delete gia pinaka assignment_grade_review, to delete ginetai apo ton kathhghth
function delete_submissions($id) {

	$return = '';
	$res = Database::get()->queryArray("SELECT * FROM assignment_grading_review WHERE assignment_id = ?d ", $id);
	if ($res){
		$return = "Οι εργασίες ανατέθηκαν σε χρήστες. Διαγράφτηκαν οι παλιές αναθέσεις.";
	}
	else{
		$return = "Οι εργασίες ανατέθηκαν.";
	}
	foreach ($res as $row) {
		Database::get()->query("DELETE FROM  assignment_grading_review WHERE assignment_id = ?d ", $id);
	}
	return $return;
}


//$del_submission_msg = delete_submissions_by_quserid($quserid, -1, $sid, $grade);
/*function delete_submissions_by_quserid($uid, $sid, $gid) {

	global $m;
	$res = Database::get()->queryArray("SELECT id, grade,id_assignment_submit, assignment_id, user_id,gid FROM assignment_grading_review WHERE id_assignment_submit = ?d AND user_id = ?d ", $sid, $uid);
	foreach ($res as $row) {
		Database::get()->query("DELETE FROM  assignment_grading_review WHERE id = ?d", $row->id);
        /*if ($row->grade != $grade) {
            @unlink("$GLOBALS[workPath]/$row->file_path");
        }*/
        //$ass_cid = Database::get()->querySingle("SELECT id FROM assignment_submit WHERE id = ?d", $sid)->id;
      //  Database::get()->query("DELETE FROM  assignment_grading_review WHERE id = ?d", $row->id)
		//triggerGame($row->assignment_id, $row->uid, $id);
		//if ($GLOBALS['uid'] == $row->user_id) {
		/*if (gid != 1 ){
			$return .= $m['deleted_work_by_user'];//den emfanizoun mnm na ta ksana dw
		} else {
			$return .= $m['deleted_work_by_group'];
		}
		//$return .= ' "<i>' . q($row->grade) . '</i>". ';
	}
    return $return;
}*/

// Find submissions by a user (or the user's groups)
function find_submissions($is_group_assignment, $uid, $id, $gids) {

    if ($is_group_assignment AND count($gids)) {
        $groups_sql = join(', ', array_keys($gids));
        $res = Database::get()->queryArray("SELECT id, uid, group_id, submission_date,
                file_path, file_name, comments, grade, grade_comments, grade_submission_date
            FROM assignment_submit
            WHERE assignment_id = ?d AND
                  group_id IN ($groups_sql)", $id);
        if (!$res) {
            return [];
        } else {
            return array_filter($res, function ($item) {
                static $seen = [];

                $return = !isset($seen[$item->group_id]);
                $seen[$item->group_id] == true;
                return $return;
            });
        }
    } else {
        $res = Database::get()->querySingle("SELECT id, grade
            FROM assignment_submit
            WHERE assignment_id = ?d AND uid = ?d
            ORDER BY id LIMIT 1", $id, $uid);
        if (!$res) {
            return [];
        } else {
            return [$res];
        }
    }
}

// Returns grade, if submission has been graded, or "Yes" (translated) if
// there is a comment by the professor but no grade, or FALSE if neither
// grade or professor comment is set
function submission_grade($subid) {
    global $langYes;

    $res = Database::get()->querySingle("SELECT grade, grade_comments
                                                FROM assignment_submit
                                                WHERE id = ?d", $subid);
    if ($res) {
        $grade = trim($res->grade);
        if (!empty($grade)) {
            return $grade;
        } elseif (!empty($res->grade_comments)) {
            return $langYes;
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

/**
 * @brief Show details of a submission
 * @global type $uid
 * @global type $m
 * @global type $langSubmittedAndGraded
 * @global type $tool_content
 * @global type $course_code
 * @global type $langAutoJudgeEnable
 * @global type $langAutoJudgeShowWorkResultRpt
 * @global type $langGradebookGrade
 * @global type $langFileName
 * @global type $langWorkOnlineText
 * @global type $langCriteria
 * @param type $id
 */
function show_submission_details($id) {

    global $uid, $m, $course_id, $langSubmittedAndGraded, $tool_content, $course_code, $autojudge,
           $langAutoJudgeEnable, $langAutoJudgeShowWorkResultRpt, $langQuestionView, $urlServer,
           $langGradebookGrade, $langWorkOnlineText, $langFileName, $head_content, $langCriteria,
           $langOpenCoursesFiles;

    load_js('tools.js');
    $head_content .= "<script type='text/javascript'>";
    $head_content .= "$(function() {
        $('.onlineText').click( function(e){
            e.preventDefault();
            var sid = $(this).data('id');
            var assignment_title = $('#assignment_title').text();
            $.ajax({
              type: 'POST',
              url: '',
              datatype: 'json',
              data: {
                 sid: sid
              },
              success: function(data){
                data = $.parseJSON(data);
                bootbox.alert({
                    title: assignment_title,
                    size: 'large',
                    message: data.submission_text? data.submission_text: '',
                });
              },
              error: function(xhr, textStatus, error){
                  console.log(xhr.statusText);
                  console.log(textStatus);
                  console.log(error);
              }
            });
        });
    })";
    $head_content .= "</script>";

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

    // Κατάσταση υποβολής εργασίας από ομάδες
    if ($sub->uid != $uid) {
        $notice .= "<br>$m[submitted_by_other_member] " .
                "<a href='../group/group_space.php?course=$course_code&amp;group_id=$sub->group_id'>" .
                "$m[your_group] " . gid_to_name($sub->group_id) . "</a> (" . display_user($sub->uid) . ")";
    } elseif ($sub->group_id) {
        $notice .= "<br>$m[groupsubmit] " .
                "<a href='../group/group_space.php?course=$course_code&amp;group_id=$sub->group_id'>" .
                "$m[ofgroup] " . gid_to_name($sub->group_id) . "</a>";
    }
    $sel_criteria = unserialize($sub->grade_rubric);
    $assignment_id = $sub->assignment_id;
    $assignment = Database::get()->querySingle("SELECT * FROM assignment WHERE id = ?d", $assignment_id);

    $rubric_id = $assignment -> grading_scale_id;
    $preview_rubric = '';
    $rubric = Database::get()->querySingle("SELECT * FROM rubric WHERE course_id = ?d AND id = ?d", $course_id, $rubric_id);
    if ($rubric) {
        $rubric_name =  $rubric -> name;
        $rubric_desc = $rubric -> description;
        $criteria = unserialize($rubric->scales);
        $criteria_list = "";
        foreach ($criteria as $ci => $criterio) {
            $criteria_list .= "<li><b>$criterio[title_name] ($criterio[crit_weight]%)</b></li>";
            if(is_array($criterio['crit_scales'])){
                $criteria_list .= "<li><ul>";
                foreach ($criterio['crit_scales'] as $si=>$scale) {
                    if($sel_criteria[$ci]==$si)
                        $criteria_list .= "<li><strong>$scale[scale_item_name] ( $scale[scale_item_value] )</strong></li>";
                    else
                        $criteria_list .= "<li>$scale[scale_item_name] ( $scale[scale_item_value] )</li>";
                }
                $criteria_list .= "</ul></li>";
            }
        }
        $preview_rubric = $rubric -> preview_rubric;
        $points_to_graded = $rubric -> points_to_graded;
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
                    <strong>" . $langGradebookGrade . ":</strong>
                </div>
               <div class='col-sm-9'>";
    if ($preview_rubric == 1 AND $points_to_graded == 1) {
        $tool_content .= "
                            <a role='button' data-toggle='collapse' href='#collapseGrade' aria-expanded='false' aria-controls='collapseGrade'>"
                            . $sub->grade .
                            "</a>
                            <div class='table-responsive  collapse' id='collapseGrade'>
                            <table class='table-default'>
                            <thead>
                                    <th>$langCriteria</th>
                            </thead>
                            <tr>
                                <td>
                                    <ul class='list-unstyled'>
                                        $criteria_list
                                    </ul>
                                </td>
                            </tr>
                    </table>
                    </div>";
    } else {
        $tool_content .= $sub->grade;
    }
    if (isset($_GET['unit'])) {
        $unit = intval($_GET['unit']);
        $file_comments_link = "../units/view.php?course=$course_code&amp;res_type=assignment&amp;getcomment=$sub->id&amp;id=$unit";
    } else {
        $file_comments_link = "{$urlServer}modules/work/?course=$course_code&amp;getcomment=$sub->id";
    }
    $tool_content .= "</div>
                </div>
                <div class='row margin-bottom-fat'>
                    <div class='col-sm-3'>
                        <strong>" . $m['gradecomments'] . ":</strong>
                    </div>
                    <div class='col-sm-9'>" . $sub->grade_comments . "&nbsp;&nbsp;<a href='$file_comments_link'>" . $sub->grade_comments_filename . "</a>
                    </div>
                </div>
                <div class='row margin-bottom-fat'>
                    <div class='col-sm-3'>
                        <strong>" . $m['sub_date'] . ":</strong>
                    </div>
                    <div class='col-sm-9'>" . nice_format($sub->submission_date, true) . "
                    </div>
                </div>";

    if ($assignment->submission_type == 2) {
        // multiple files
        $links = implode('<br>',
            array_map(function ($item) {
                global $course_code, $urlAppend;
                if (isset($_GET['unit'])) {
                    $url = "modules/units/view.php?course=$course_code&amp;res_type=assignment&amp;get=$item->id";
                } else {
                    $url = "modules/work/?course=$course_code&amp;get=$item->id";
                }
                return "<a href='{$urlAppend}$url'>" . q($item->file_name) . '</a>';
            }, Database::get()->queryArray('SELECT id, file_name FROM assignment_submit
                    WHERE assignment_id = ?d AND uid = ?d AND group_id = ?d ORDER BY id',
                    $sub->assignment_id, $sub->uid, $sub->group_id)));
        $tool_content .= "<div class='row margin-bottom-fat'>
                <div class='col-sm-3'>
                    <strong>$langOpenCoursesFiles:</strong>
                </div>
                <div class='col-sm-9'>$links</div>";
    } elseif ($assignment->submission_type == 0) {
        // single file
        if (isset($_GET['unit'])) {
            $get_link = "<a href='../units/view.php?course=$course_code&amp;res_type=assignment&amp;get=$sub->id'>";
        } else {
            $get_link = "<a href='{$urlServer}modules/work/?course=$course_code&amp;get=$sub->id'>";
        }
        $tool_content .= "<div class='row margin-bottom-fat'>
                    <div class='col-sm-3'>
                        <strong>$langFileName:</strong>
                    </div>
                    <div class='col-sm-9'>$get_link" . q($sub->file_name) . "</a></div>";
    } else {
        // online text
        $tool_content .= "<div class='row margin-bottom-fat'>
                    <div class='col-sm-3'>
                        <strong>$langWorkOnlineText:</strong>
                    </div>
                    <div class='col-sm-9'><a href='#' class='onlineText btn btn-xs btn-default' data-id='$sub->id'>$langQuestionView</a>";
    }
    $tool_content .= "</div>";

    if ($assignment->auto_judge and $autojudge->isEnabled()) {
        $reportlink = "{$urlServer}modules/work/work_result_rpt.php?course=$course_code&amp;assignment=$sub->assignment_id&amp;submission=$sub->id";
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

    $tool_content .= "</div></div>";
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

function triggerGame($courseId, $uid, $assignId) {
    $eventData = new stdClass();
    $eventData->courseId = $courseId;
    $eventData->uid = $uid;
    $eventData->activityType = AssignmentEvent::ACTIVITY;
    $eventData->module = MODULE_ID_ASSIGN;
    $eventData->resource = intval($assignId);
    AssignmentEvent::trigger(AssignmentEvent::UPDGRADE, $eventData);
}


function triggerAssignmentAnalytics($courseId, $uid, $assignmentId, $eventname) {
    $data = new stdClass();
    $data->course_id = $courseId;
    $data->uid = $uid;
    $data->resource = $assignmentId;

    if ($eventname == AssignmentAnalyticsEvent::ASSIGNMENTGRADE) {
        $data->element_type = 40;
    } else if ($eventname == AssignmentAnalyticsEvent::ASSIGNMENTDL) {
        $data->element_type = 41;
    }
    AssignmentAnalyticsEvent::trigger($eventname, $data, true);
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

/**
 * @brief notify (via email) course admin about assignment submission
 * @global type $logo
 * @global type $langAssignmentPublished
 * @global type $langAssignmentHasPublished
 * @global type $urlServer
 * @global type $course_code
 * @global type $langSender
 * @global type $langAssignment
 * @global type $course_id
 * @global type $langHasAssignmentPublished
 * @param type $title
 */
function notify_for_assignment_submission($title) {

    global $logo, $langAssignmentPublished, $langTo, $course_id,
           $langsCourse, $langHasAssignmentPublished,
           $urlServer, $course_code, $langSender, $langAssignment;

    $emailSubject = "$logo - $langAssignmentPublished";
    $emailHeaderContent = "
        <!-- Header Section -->
        <div id='mail-header'>
            <br>
            <div>
                <div id='header-title'>$langHasAssignmentPublished $langTo $langsCourse <a href='{$urlServer}courses/$course_code/'>" . q(course_id_to_title($course_id)) . "</a>.</div>
                <ul id='forum-category'>
                    <li><span><b>$langSender:</b></span> <span class='left-space'>" . q($_SESSION['givenname']) . " " . q($_SESSION['surname']) . "</span></li>
                </ul>
            </div>
        </div>";

    $emailBodyContent = "
        <!-- Body Section -->
        <div id='mail-body'>
            <br>
            <div><b>$langAssignment:</b> <span class='left-space'>".q($title)."</span></div><br>
        </div>";

    $emailContent = $emailHeaderContent . $emailBodyContent;
    $emailBody = html2text($emailContent);

    $profs = Database::get()->queryArray("SELECT user.id AS prof_uid, user.email AS email,
                              user.surname, user.givenname
                           FROM course_user JOIN user ON user.id = course_user.user_id
                           WHERE course_id = ?d AND course_user.status = " . USER_TEACHER . "", $course_id);

    foreach ($profs as $prof) {
        if (!get_user_email_notification_from_courses($prof->prof_uid) or (!get_user_email_notification($prof->prof_uid, $course_id))) {
            continue;
        } else {
            $to_name = $prof->givenname . " " . $prof->surname;
            if (!send_mail_multipart("$_SESSION[givenname] $_SESSION[surname]", $_SESSION['email'], $to_name, $prof->email, $emailSubject, $emailBody, $emailContent)) {
                continue;
            }
        }
    }
}


/**
 * @brief send file for plagiarism check
 * @param type $assign_id
 * @param type $file_id
 * @param type $true_file_path
 * @param type $true_file_name
 * @global type $course_code
 */
function send_file_for_plagiarism($assign_id, $file_id, $true_file_path, $true_file_name) {

    global $course_code, $langPlagiarismAlreadyCheck, $langPlagiarismFileSent;

    if (!Plagiarism::get()->isFileSubmitted($file_id)) {
        Plagiarism::get()->submitFile($file_id, $true_file_path, $true_file_name);
        Session::Messages($langPlagiarismFileSent, 'alert-success');
    } else {
        Session::Messages($langPlagiarismAlreadyCheck, 'alert-warning');
    }
    redirect_to_home_page("modules/work/index.php?course=$course_code&id=$assign_id");
}


/**
 * @brief check for valid plagiarism file type
 * @param type $file_id
 * @return boolean
 */
function valid_plagiarism_file_type($file_id) {

    $unplag_allowable_file_extensions = array('doc', 'docx', 'rtf', 'txt', 'odt', 'html', 'pdf');

    $file_details = Database::get()->querySingle("SELECT file_name FROM assignment_submit WHERE id = ?d", $file_id);
    if ($file_details) {
        $file_type = get_file_extension($file_details->file_name);
        if (in_array($file_type, $unplag_allowable_file_extensions)) {
            return TRUE;
        }
    }
    return FALSE;
}


/**
 * @brief Auto Judge function
 * @param $scenarionAssertion
 * @param $scenarioInputResult
 * @param $scenarioOutputExpectation
 * @return bool
 */
function doScenarioAssertion($scenarionAssertion, $scenarioInputResult, $scenarioOutputExpectation) {
    switch($scenarionAssertion) {
        case 'eq':
            $assertionResult = ($scenarioInputResult == $scenarioOutputExpectation);
            break;
        case 'same':
            $assertionResult = ($scenarioInputResult === $scenarioOutputExpectation);
            break;
        case 'notEq':
            $assertionResult = ($scenarioInputResult != $scenarioOutputExpectation);
            break;
        case 'notSame':
            $assertionResult = ($scenarioInputResult !== $scenarioOutputExpectation);
            break;
        case 'integer':
            $assertionResult = (is_int($scenarioInputResult));
            break;
        case 'float':
            $assertionResult = (is_float($scenarioInputResult));
            break;
        case 'digit':
            $assertionResult = (ctype_digit($scenarioInputResult));
            break;
        case 'boolean':
            $assertionResult = (is_bool($scenarioInputResult));
            break;
        case 'notEmpty':
            $assertionResult = (empty($scenarioInputResult) === false);
            break;
        case 'notNull':
            $assertionResult = ($scenarioInputResult !== null);
            break;
        case 'string':
            $assertionResult = (is_string($scenarioInputResult));
            break;
        case 'startsWith':
            $assertionResult = (mb_strpos($scenarioInputResult, $scenarioOutputExpectation, null, 'utf8') === 0);
            break;
        case 'endsWith':
            $stringPosition  = mb_strlen($scenarioInputResult, 'utf8') - mb_strlen($scenarioOutputExpectation, 'utf8');
            $assertionResult = (mb_strripos($scenarioInputResult, $scenarioOutputExpectation, null, 'utf8') === $stringPosition);
            break;
        case 'contains':
            $assertionResult = (mb_strpos($scenarioInputResult, $scenarioOutputExpectation, null, 'utf8'));
            break;
        case 'numeric':
            $assertionResult = (is_numeric($scenarioInputResult));
            break;
        case 'isArray':
            $assertionResult = (is_array($scenarioInputResult));
            break;
        case 'true':
            $assertionResult = ($scenarioInputResult === true);
            break;
        case 'false':
            $assertionResult = ($scenarioInputResult === false);
            break;
        case 'isJsonString':
            $assertionResult = (json_decode($value) !== null && JSON_ERROR_NONE === json_last_error());
            break;
        case 'isObject':
            $assertionResult = (is_object($scenarioInputResult));
            break;
    }

    return $assertionResult;
}
