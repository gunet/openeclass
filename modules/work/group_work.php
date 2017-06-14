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
$require_login = true;

require_once 'functions.php';
require_once '../../include/baseTheme.php';
require_once 'include/lib/fileManageLib.inc.php';
require_once 'include/lib/forcedownload.php';
require_once 'modules/document/doc_init.php';

define('GROUP_DOCUMENTS', true);
$group_id = intval($_REQUEST['group_id']);
doc_init();

$coursePath = $webDir . '/courses/' . $course_code;
$workPath = $coursePath . '/work';
$groupPath = $coursePath . '/group/' . group_secret($group_id);
if (!file_exists($workPath)) {
    make_dir($workPath);
}
if (!file_exists($groupPath)) {
    make_dir($groupPath);
}

$pageName = $langGroupSubmit;

if (isset($_GET['submit'])) {
    $tool_content .= "<div class='alert alert-info'>$langGroupWorkIntro</div>";
    show_assignments();
    draw($tool_content, 2);
} elseif (isset($_POST['assign'])) {
    submit_work($uid, $group_id, $_POST['assign'], getDirectReference($_POST['file']));
    draw($tool_content, 2);
} else {
    header("Location: index.php?course=$course_code");
}


/**
 * @brief show non-expired assignments list to allow selection
 * @global type $m
 * @global type $uid
 * @global type $group_id
 * @global type $langSubmit
 * @global type $langNoAssign
 * @global type $tool_content
 * @global type $langWorks
 * @global type $course_id
 * @global type $course_code
 * @global type $themeimg
 * @global type $langCancel
 * @global type $urlServer
 * @global type $langTitle
 * @global type $langYes
 * @global type $langNo
 * @return type
 */
function show_assignments() {

    global $m, $uid, $group_id, $langSubmit, $langNoAssign, $tool_content, $langYes, $langNo,
           $langWorks, $course_id, $course_code, $themeimg, $langCancel, $urlServer, $langTitle;
    
    $gids = user_group_info($uid, $course_id);
    if (!empty($gids)) {
        $gids_sql_ready = implode(',',array_keys($gids));
    } else {
        $gids_sql_ready = "''";
    }
    
    $res = Database::get()->queryArray("SELECT *, CAST(UNIX_TIMESTAMP(deadline)-UNIX_TIMESTAMP(NOW()) AS SIGNED) AS time
                                 FROM assignment WHERE course_id = ?d AND active = '1' AND
                                 (assign_to_specific = '0' OR assign_to_specific = '1' AND id IN
                                    (SELECT assignment_id FROM assignment_to_specific WHERE user_id = ?d UNION SELECT assignment_id FROM assignment_to_specific WHERE group_id IN ($gids_sql_ready))
                                 )
                                 ORDER BY CASE WHEN CAST(deadline AS UNSIGNED) = '0' THEN 1 ELSE 0 END, deadline", $course_id, $uid);

    if (count($res) == 0) {
        $tool_content .= $langNoAssign;
        return;
    }
    $table_content = '';
    foreach ($res as $row) {
        if (!$row->active) {
            continue;
        }
        $table_content .= "<tr><td width='1%'>
			<img style='padding-top:2px;' src='$themeimg/arrow.png' alt=''></td>
			<td><div align='left'><a href='index.php?course=$course_code&amp;id=$row->id'>" . q($row->title) . "</a></td>
			<td align='center'>" . nice_format($row->deadline);
                        if ($row->time > 0) {
                            $table_content .= "<br>(<small>$langDaysLeft" . format_time_duration($row->time) . "</small>)";
                        } else if($row->deadline){
                            $table_content .= "<br> (<small><span class='expired'>$m[expired]</span></small>)";
                        }

        $table_content .= "</div></td><td align='center'>";
        $subm = was_submitted($uid, $group_id, $row->id);
        if ($subm == 'user') {
            $table_content .= $langYes;
        } elseif ($subm == 'group') {
            $table_content .= $m['by_groupmate'];
        } else {
            $table_content .= $langNo;
        }
        $table_content .= "</td><td align='center'>";
        if ($row->time >= 0 and !was_graded($uid, $row->id) and is_group_assignment($row->id)) {
            $table_content .= "<input type='radio' name='assign' value='$row->id'>";
        } else {
            $table_content .= '-';
        }
        $table_content .= "</td></tr>";
    }
    $tool_content .= "
            <div class='form-wrapper'>
                <form class='form-horizontal' action='$_SERVER[SCRIPT_NAME]?course=$course_code' method='post'>
                <fieldset>
                    <input type='hidden' name='file' value='" . q($_GET['submit']) . "'>
                    <input type='hidden' name='group_id' value='$group_id'>
                    <div class='form-group'>
                        <label for='title' class='col-sm-2 control-label'>$langWorks ($m[select]):</label>
                        <div class='col-sm-10'>
                            <table class='table-default'>
                                <tr>
                                    <th class='left' colspan='2'>$langTitle</th>
                                    <th align='center' width='30%'>$m[deadline]</th>
                                    <th align='center' width='10%'>$m[submitted]</th>
                                    <th align='center' width='10%'>$m[select]</th>
                                </tr>
                                $table_content
                            </table>
                        </div>
                    </div>
                    <div class='form-group'>
                        <label for='title' class='col-sm-2 control-label'>$m[comments]:</label>
                        <div class='col-sm-10'>
                            <textarea name='comments' rows='4' cols='60' class='form-control'></textarea>
                        </div>
                    </div>
                    <div class='form-group'>
                        <div class='col-sm-10 col-sm-offset-2'>
                            <input class='btn btn-primary' type='submit' name='submit' value='$langSubmit'>
                            <a class='btn btn-default' href='$urlServer/modules/group/document.php?course=$course_code&group_id=$group_id'>$langCancel</a>
                        </div>
                    </div>
                </fieldset>
            </form>
        </div>";
}

// Insert a group work submitted by user uid to assignment id
function submit_work($uid, $group_id, $id, $file) {

    global $groupPath, $langUploadError, $langUploadSuccess,
    $langBack, $m, $tool_content, $workPath,
    $group_sql, $webDir, $course_code, $is_editor;

    $ext = get_file_extension($file);
    $local_name = greek_to_latin('Group ' . $group_id . (empty($ext) ? '' : '.' . $ext));
    $original_filename = Database::get()->querySingle("SELECT filename FROM document WHERE $group_sql AND path = ?s", $file)->filename;
    $source = $groupPath . $file;
    $destination = work_secret($id) . "/$local_name";

    delete_submissions_by_uid($uid, $group_id, $id, $destination);

    if (is_dir($source)) {
        $original_filename = $original_filename . '.zip';
        $zip_filename = $webDir . 'courses/temp/' . safe_filename('zip');
        zip_documents_directory($zip_filename, $file, $is_editor);
        $source = $zip_filename;
    }
    if (copy($source, "$workPath/$destination")) {
        Database::get()->query("INSERT INTO assignment_submit (uid, assignment_id, submission_date,
                                submission_ip, file_path, file_name, comments, group_id, grade_comments)
                                VALUES (?d, ?d, NOW(), '$_SERVER[REMOTE_ADDR]', ?s, ?s, ?s, ?d, ''", $uid, $id, $destination, $original_filename, $_POST['comments'], $group_id);

        $tool_content .="<div class='alert alert-success'>$langUploadSuccess
			<br>$m[the_file] \"$original_filename\" $m[was_submitted]<br>
			<a href='index.php?course=$course_code'>$langBack</a></div><br>";
    } else {
        $tool_content .="<div class='alert alert-danger'>$langUploadError<br>
		<a href='index.php?course=$course_code'>$langBack</a></div><br>";
    }
}
