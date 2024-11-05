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

use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;

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
    $extra = $html? '': ' style="white-space: pre-wrap"';
    if (strlen(trim($content))) {
        $tool_content .= "
                        <div class='row p-2 margin-bottom-fat'>
                            <div class='col-sm-3'>
                                <strong class='title-default'>$title:</strong>
                            </div>
                            <div class='col-sm-9'$extra>$content
                            </div>
                        </div>";
    }
}

/**
 * @brief Show a table header which is a link with the appropriate sorting
    parameters - $attrib should contain any extra attributes requered in
    the <th> tags
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
// Doesn't delete files if they are are one of the $files_to_keep
function delete_submissions_by_uid($uid, $gid, $id, $files_to_keep = []) {
    global $m, $workPath;

    $return = '';
    $res = Database::get()->queryArray("SELECT id, file_path, file_name, uid, group_id
        FROM assignment_submit
        WHERE assignment_id = ?d AND (uid = ?d OR group_id = ?d)", $id, $uid, $gid);
    foreach ($res as $row) {
        if (!in_array($row->file_path, $files_to_keep)) {
            @unlink("$workPath/$row->file_path");
            // try to remove parent dir if this was part of a multifile submission
            $parts = explode('/', $row->file_path);
            if (count($parts) == 3) {
                $parent = "$workPath/$parts[0]/$parts[1]";
                if (is_dir($parent)) {
                    @rmdir($parent);
                }
            }
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

/**
 * @param $id
 * @brief function delete gia pinaka assignment_grade_review, to delete ginetai apo ton kathhghth
 * @return string
 */
function delete_submissions($id) {

    global $langPeerReviewAssignmentInfo1, $langPeerReviewAssignmentInfo2;

    $res = Database::get()->queryArray("SELECT * FROM assignment_grading_review WHERE assignment_id = ?d ", $id);
    if ($res) {
        $msg = $langPeerReviewAssignmentInfo1;
    } else {
        $msg = $langPeerReviewAssignmentInfo2;
    }
    foreach ($res as $row) {
        Database::get()->query("DELETE FROM assignment_grading_review WHERE assignment_id = ?d ", $id);
    }
    return $msg;
}

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
                $seen[$item->group_id] = true;
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
        $grade = $res->grade;
        if (!empty($grade)) {
            return trim($grade);
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
 * @param type $id
 */
function show_submission_details($id) {

    global $uid, $m, $course_id, $langSubmittedAndGraded, $tool_content, $course_code, $autojudge,
           $langAutoJudgeEnable, $langAutoJudgeShowWorkResultRpt, $langQuestionView, $urlAppend,
           $langGradebookGrade, $langWorkOnlineText, $langFileName, $head_content, $langCriteria,
           $langOpenCoursesFiles, $langDownload, $langPrint, $langFullScreen, $langNewTab, $langCancel;

    load_js('tools.js');
    load_js('screenfull/screenfull.min.js');

    $head_content .= "<script type='text/javascript'>
    $(function() {
        $('.onlineText').click(function(e){
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
        initialize_filemodal({
            download: '$langDownload',
            print: '$langPrint',
            fullScreen: '$langFullScreen',
            newTab: '$langNewTab',
            cancel: '$langCancel'});
        });
        </script>";

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
        $notice .= "<br>$m[groupsubmit] $m[ofgroup] <em>" . gid_to_name($sub->group_id) . "</em>.";
    }
    if (!empty($sub->grade_rubric)) {
        $sel_criteria = unserialize($sub->grade_rubric);
    } else {
        $sel_criteria = [];
    }

    $assignment_id = $sub->assignment_id;
    $assignment = Database::get()->querySingle("SELECT * FROM assignment WHERE id = ?d", $assignment_id);

    $rubric_id = $assignment -> grading_scale_id;
    $preview_rubric = '';
    $rubric = Database::get()->querySingle("SELECT * FROM rubric WHERE course_id = ?d AND id = ?d", $course_id, $rubric_id);
    if ($rubric) {
        $rubric_name =  $rubric->name;
        $rubric_desc = $rubric->description;
        $criteria = unserialize($rubric->scales);
        $criteria_list = "";
        foreach ($criteria as $ci => $criterio) {
            $criteria_list .= "<li><b>$criterio[title_name] ($criterio[crit_weight]%)</b></li>";
            if(is_array($criterio['crit_scales'])){
                $criteria_list .= "<li><ul>";
                foreach ($criterio['crit_scales'] as $si=>$scale) {
                    if (!isset($sel_criteria[$ci])) {
                        $sel_criteria[$ci] = '';
                    }
                    if ($sel_criteria[$ci]==$si) {
                        $criteria_list .= "<li><strong>$scale[scale_item_name] ( $scale[scale_item_value] )</strong></li>";
                    } else {
                        $criteria_list .= "<li>$scale[scale_item_name] ( $scale[scale_item_value] )</li>";
                    }
                }
                $criteria_list .= "</ul></li>";
            }
        }
        $preview_rubric = $rubric->preview_rubric;
        $points_to_graded = $rubric->points_to_graded;
    }

    $tool_content .= "
    <div class='col-12 mt-4'>
        <div class='card panelCard border-card-left-default px-lg-4 py-lg-3'>
            <div class='card-header border-0 d-flex justify-content-between align-items-center'>
                <h3>$m[SubmissionWorkInfo]</h3>
            </div>
            <div class='card-body'>
            <ul class='list-group list-group-flush'>
                <li class='list-group-item element'>
                    <div class='row row-cols-1 row-cols-md-2 g-1'>
                        <div class='col-md-3 col-12'>
                            <div class='title-default'>".$m['SubmissionStatusWorkInfo']."</div>
                        </div>
                        <div class='col-md-9 col-12 title-default-line-height'>$notice</div>
                    </div>
                </li>
                <li class='list-group-item element'>
                <div class='row row-cols-1 row-cols-md-2 g-1'>
                    <div class='col-md-3 col-12'>
                        <div class='title-default'>" . $langGradebookGrade . "</div>
                    </div>
                   <div class='col-md-9 col-12 title-default-line-height'>";
    if ($preview_rubric == 1 AND $points_to_graded == 1) {
        $tool_content .= "
                        <a role='button' data-bs-toggle='collapse' href='#collapseGrade' aria-expanded='false' aria-controls='collapseGrade'>"
                        . $sub->grade .
                        "</a>
                        <div class='table-responsive collapse' id='collapseGrade'>
                            <table class='table-default'>
                                <thead class='list-header'>
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
                        </div>
                    </div>";
    } else {
        $tool_content .= $sub->grade;
    }
    if ($sub->grade_comments_filename) {
        if (isset($_GET['unit'])) {
            $unit = intval($_GET['unit']);
            $file_comments_url = "{$urlAppend}modules/units/view.php?course=$course_code&amp;res_type=assignment&amp;getcomment=$sub->id&amp;id=$unit";
        } else {
            $file_comments_url = "{$urlAppend}modules/work/index.php?course=$course_code&amp;getcomment=$sub->id";
        }
        $file_comments_link = '<br>' . MultimediaHelper::chooseMediaAhrefRaw($file_comments_url, $file_comments_url, $sub->grade_comments_filename, $sub->grade_comments_filename);
    } else {
        $file_comments_link = '';
    }
    $tool_content .= "</div>
                </div>
                </li>
                <li class='list-group-item element'>
                    <div class='row row-cols-1 row-cols-md-2 g-1'>
                        <div class='col-md-3 col-12'>
                            <div class='title-default'>" . $m['gradecomments'] . "</div>
                        </div>
                        <div class='col-md-9 col-12 title-default-line-height' style='white-space: pre-wrap'>" . q($sub->grade_comments) . $file_comments_link . "
                        </div>
                    </div>
                </li>
                <li class='list-group-item element'>
                    <div class='row row-cols-1 row-cols-md-2 g-1'>
                        <div class='col-md-3 col-12'>
                            <div class='title-default'>" . $m['sub_date'] . "</div>
                        </div>
                        <div class='col-md-9 col-12 title-default-line-height'>" . format_locale_date(strtotime($sub->submission_date)) . "</div>
                    </div>
                </li>";

    if ($assignment->submission_type == ASSIGNMENT_RUBRIC_GRADE) {
        // multiple files
        $links = implode('<br>',
            array_map(function ($item) {
                global $course_code, $urlAppend;
                if (isset($_GET['unit'])) {
                    $url = $urlAppend . "modules/units/view.php?course=$course_code&amp;res_type=assignment&amp;get=$item->id";
                } else {
                    $url = $urlAppend . "modules/work/index.php?course=$course_code&amp;get=$item->id";
                }
                return MultimediaHelper::chooseMediaAhrefRaw($url, $url, $item->file_name, $item->file_name);
            }, Database::get()->queryArray('SELECT id, file_name FROM assignment_submit
                    WHERE assignment_id = ?d AND uid = ?d AND group_id = ?d ORDER BY id',
                    $sub->assignment_id, $sub->uid, $sub->group_id)));
        $tool_content .= "
        <li class='list-group-item element'>
            <div class='row row-cols-1 row-cols-md-2 g-1'>
                <div class='col-md-3 col-12'>
                    <div class='title-default'>$langOpenCoursesFiles</div>
                </div>
                <div class='col-md-9 col-12 title-default-line-height'>
                    $links
                </div>
            </div>
        </li>";
    } elseif ($assignment->submission_type == ASSIGNMENT_STANDARD_GRADE) {
        // single file
        if (isset($_GET['unit'])) {
            $url = "{$urlAppend}modules/units/view.php?course=$course_code&amp;res_type=assignment&amp;get=$sub->id";
        } else {
            $url = "{$urlAppend}modules/work/index.php?course=$course_code&amp;get=$sub->id";
        }
        $filelink = MultimediaHelper::chooseMediaAhrefRaw($url, $url, $sub->file_name, $sub->file_name);
        $tool_content .= "
            <li class='list-group-item element'>
                <div class='row row-cols-1 row-cols-md-2 g-1'>
                    <div class='col-md-3 col-12'>
                        <div class='title-default'>$langFileName</div>
                    </div>
                    <div class='col-md-9 col-12 title-default-line-height'>
                        $filelink
                    </div>
                </div>
            </li>";
    } else {
        // online text
        $tool_content .= "
            <li class='list-group-item element'>
                <div class='row row-cols-1 row-cols-md-2 g-1'>
                    <div class='col-md-3 col-12'>
                        <div class='title-default'>$langWorkOnlineText</div>
                    </div>
                    <div class='col-sm-9 col-12 title-default-line-height'>
                        <a href='#' class='onlineText btn submitAdminBtn d-inline-flex' data-id='$sub->id'>$langQuestionView</a>
                    </div>
                </div>
            </li>";
    }
    $tool_content .= "";

    if ($assignment->auto_judge and $autojudge->isEnabled()) {
        $reportlink = $urlAppend."modules/work/work_result_rpt.php?course=$course_code&amp;assignment=$sub->assignment_id&amp;submission=$sub->id";
        $tool_content .= "
                <li class='list-group-item element'>
                    <div class='row row-cols-1 row-cols-md-2 g-1'>
                        <div class='col-md-3 col-12'>
                            <div class='title-default'>" . $langAutoJudgeEnable . "</div>
                        </div>
                        <div class='col-md-9 col-12 title-default-line-height'>
                            <a href='$reportlink'> $langAutoJudgeShowWorkResultRpt</a>
                        </div>
                    </div>
                </li>";
    }

    table_row($m['comments'], $sub->comments);

    $tool_content .= "</ul></div></div></div>";
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
    AssignmentEvent::trigger(AssignmentEvent::UPGRADE, $eventData);
}

function triggerAssignmentSubmit($courseId, $uid, $assignId) {
    $eventData = new stdClass();
    $eventData->courseId = $courseId;
    $eventData->uid = $uid;
    $eventData->activityType = AssignmentSubmitEvent::ACTIVITY;
    $eventData->module = MODULE_ID_ASSIGN;
    $eventData->resource = intval($assignId);
    AssignmentSubmitEvent::trigger(AssignmentSubmitEvent::UPDATE, $eventData);
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
 * @param type $id
 */
function export_grades_to_csv($id) {

    global $course_code, $course_id, $langNoDeadline, $langNotice2,
           $langSurname, $langName, $langAm, $langGroup, $langScore,
           $langUsername, $langEmail, $langStartDate, $langGradebookGrade,
           $langGroupWorkDeadline_of_Submission;

    $spreadsheet = new Spreadsheet();
    $sheet = $spreadsheet->getActiveSheet();
    $sheet->setTitle($langScore);
    $sheet->getDefaultColumnDimension()->setWidth(30);

    $filename = $course_code . "_" . $id . "_grades_list.xlsx";

    // additional security
    $q = Database::get()->querySingle("SELECT id, title, submission_date, deadline FROM assignment
                            WHERE id = ?d AND course_id = ?d", $id, $course_id);
    if ($q) {
        $assignment_id = $q->id;
        if (!is_null($q->deadline)) {
            $deadline_message = "$langGroupWorkDeadline_of_Submission: " . format_locale_date(strtotime($q->deadline), 'full');
        } else {
            $deadline_message = $langNoDeadline;
        }
        $message = $q->title . " (" . $langStartDate .": ". format_locale_date(strtotime($q->submission_date), 'full') . "  ". $deadline_message .")";
        $data[] = [ $message ];
        $data[] = [];
        $data[] = [ $langSurname, $langName, $langNotice2, $langAm, $langGroup, $langUsername, $langEmail, $langGradebookGrade ];
        $data[] = [];
        $sql = Database::get()->queryFunc("SELECT MAX(uid) AS uid,
                                            CAST(MAX(grade) AS DECIMAL(10,2)) AS grade,
                                            MAX(submission_date) AS submission_date,
                                            MAX(surname) AS surname,
                                            MAX(givenname) AS givenname,
                                            username,
                                            MAX(am) AS am,
                                            MAX(email) AS email
                                           FROM assignment_submit JOIN user
                                               ON uid = user.id
                                           WHERE assignment_id = ?d
                                            GROUP BY username
                                            ORDER BY surname, givenname",
                function ($item)  use (&$data, $course_id) {
                    $ug = user_groups($course_id, $item->uid, 'txt');
                    $sub_date = format_locale_date(strtotime($item->submission_date));
                    $data[] = [ $item->surname, $item->givenname, $sub_date, $item->am, $ug, $item->username, $item->email, $item->grade ];
                }, $assignment_id);
    }
    $sheet->mergeCells("A1:H1");
    $sheet->getCell('A1')->getStyle()->getFont()->setItalic(true);
    for ($i = 1; $i <= 8; $i++) {
        $cells = [$i, 3];
        $sheet->getCell($cells)->getStyle()->getFont()->setBold(true);
    }
    // create spreadsheet
    $sheet->fromArray($data, NULL);
    // file output
    $writer = new Xlsx($spreadsheet);
    header("Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet");
    set_content_disposition('attachment', $filename);
    $writer->save("php://output");
    exit;
}

/**
 * @brief notify (via email) course admin about assignment submission
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
        Session::flash('message',$langPlagiarismFileSent);
        Session::flash('alert-class', 'alert-success');
    } else {
        Session::flash('message',$langPlagiarismAlreadyCheck);
        Session::flash('alert-class', 'alert-warning');
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

/**
 * @brief Count number of submissions to an assignment
 * @param int $assignment_id
 * @return int
 */
function countSubmissions($assignment_id) {
    $num_submitted = Database::get()->querySingle('SELECT COUNT(*) AS count FROM (
            SELECT uid, group_id FROM assignment_submit
            WHERE assignment_id = ?d GROUP BY uid, group_id
        ) AS distinct_submissions', $assignment_id);
    if ($num_submitted) {
        return $num_submitted->count;
    } else {
        return 0;
    }
}


/**
 * @brief check if rubrics exist in course
 * @return bool
 */
function rubrics_exist() {

    global $course_id;

    $q = Database::get()->querySingle("SELECT * FROM rubric WHERE course_id = ?d", $course_id);

    if ($q) {
        return true;
    } else {
        return false;
    }
}


/**
 * @brief check if grading scales exist in course
 * @return bool
 */
function grading_scales_exist() {

    global $course_id;

    $q = Database::get()->querySingle("SELECT * FROM grading_scale WHERE course_id = ?d", $course_id);

    if ($q) {
        return true;
    } else {
        return false;
    }
}

/**
 * @brief display assignment submissions results in graph
 * @param $id
 * @return void
 */
function display_assignment_submissions_graph_results($id)
{
    global $tool_content, $course_id, $langGraphResults;

    $assign = Database::get()->querySingle("SELECT *, CAST(UNIX_TIMESTAMP(deadline)-UNIX_TIMESTAMP(NOW()) AS SIGNED) AS time,
                                                        CAST(UNIX_TIMESTAMP(start_date_review)-UNIX_TIMESTAMP(NOW()) AS SIGNED) AS time_start,
                                                        CAST(UNIX_TIMESTAMP(due_date_review)-UNIX_TIMESTAMP(NOW()) AS SIGNED) AS time_due,
                                                        auto_judge
                                                    FROM assignment
                                                      WHERE course_id = ?d AND id = ?d", $course_id, $id);

    assignment_details($id, $assign);

    $result1 = Database::get()->queryArray("SELECT grade FROM assignment_submit WHERE assignment_id = ?d ORDER BY grade ASC", $id);
    $gradeOccurances = array(); // Named array to hold grade occurrences/stats
    $gradesExists = 0;
    foreach ($result1 as $row) {
        $theGrade = $row->grade;
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
    // display pie chart with grades results
    if ($gradesExists) {
        // Used to display grades distribution chart
        $graded_submissions_count = Database::get()->querySingle("SELECT COUNT(*) AS count FROM assignment_submit AS assign
                                                                 WHERE assign.assignment_id = ?d AND
                                                                 assign.grade <> ''", $id)->count;

        if ($assign->grading_scale_id and $assign->grading_type == 1) {
            $serialized_scale_data = Database::get()->querySingle('SELECT scales FROM grading_scale WHERE id = ?d AND course_id = ?d', $assign->grading_scale_id, $course_id)->scales;
            $scales = unserialize($serialized_scale_data);
            $scale_values = array_value_recursive('scale_item_value', $scales);
        }
        foreach ($gradeOccurances as $gradeValue => $gradeOccurance) {
            $percentage = round((100.0 * $gradeOccurance / $graded_submissions_count),2);
            if ($assign->grading_scale_id and $assign->grading_type == 1) {
                $key = closest($gradeValue, $scale_values, true)['key'];
                $gradeValue = $scales[$key]['scale_item_name'];
            }
            $this_chart_data['grade'][] = "$gradeValue";
            $this_chart_data['percentage'][] = $percentage;
        }
        $tool_content .= "<script type = 'text/javascript'>gradesChartData = ".json_encode($this_chart_data).";</script>";
        /****   C3 plot   ****/
        $tool_content .= "<div class='row plotscontainer'>";
        $tool_content .= "<div class='col-lg-12 mt-4'>";
        $tool_content .= plot_placeholder("grades_chart", $langGraphResults);
        $tool_content .= "</div></div>";
    }
}
