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

require_once 'work_functions.php';
require_once '../../include/baseTheme.php';
require_once 'include/pclzip/pclzip.lib.php';
require_once 'include/lib/fileManageLib.inc.php';
require_once 'include/lib/forcedownload.php';

define('GROUP_DOCUMENTS', true);
$group_id = intval($_REQUEST['group_id']);
require_once 'modules/document/doc_init.php';

$coursePath = $webDir . '/courses/' . $course_code;
if (!file_exists($coursePath))
    mkdir($coursePath, 0777);

$workPath = $coursePath . '/work';
$groupPath = $coursePath . '/group/' . group_secret($group_id);

$pageName = $langGroupSubmit;

if (isset($_GET['submit'])) {
    $tool_content .= "<div class='alert alert-info'>$langGroupWorkIntro</div>";
    show_assignments();
    draw($tool_content, 2);
} elseif (isset($_POST['assign'])) {
    submit_work($uid, $group_id, $_POST['assign'], $_POST['file']);
    draw($tool_content, 2);
} else {
    header("Location: index.php?course=$course_code");
}

// show non-expired assignments list to allow selection
function show_assignments() {
    global $m, $uid, $group_id, $langSubmit, $langDays, $langNoAssign, $tool_content,
    $langWorks, $course_id, $course_code, $themeimg, $langCancel, $urlServer;

    $res = Database::get()->queryArray("SELECT *, (TO_DAYS(deadline) - TO_DAYS(NOW())) AS days
		 FROM assignment WHERE course_id = ?d", $course_id);
    if (count($res) == 0) {
        $tool_content .= $langNoAssign;
        return;
    }
    $table_content = '';
    foreach ($res as $row) {
        if (!$row->active) {
            continue;
        }

        $table_content .= "<tr><td width=\"1%\">
			<img style='padding-top:2px;' src='$themeimg/arrow.png' alt=''></td>
			<td><div align='left'><a href='index.php?course=$course_code&amp;id=$row->id'>" . q($row->title) . "</a></td>
			<td align='center'>" . nice_format($row->deadline);
        if ($row->days > 1) {
            $table_content .= " ($m[in]&nbsp;$row->days&nbsp;$langDays";
        } elseif ($row->days < 0) {
            $table_content .= " ($m[expired])";
        } elseif ($row->days == 1) {
            $table_content .= " ($m[tomorrow])";
        } else {
            $table_content .= " ($m[today])";
        }

        $table_content .= "</div></td>\n      <td align=\"center\">";
        $subm = was_submitted($uid, $group_id, $row->id);
        if ($subm == 'user') {
            $table_content .= $m['yes'];
        } elseif ($subm == 'group') {
            $table_content .= $m['by_groupmate'];
        } else {
            $table_content .= $m['no'];
        }
        $table_content .= "</td><td align=\"center\">";
        if ($row->days >= 0 and !was_graded($uid, $row->id) and is_group_assignment($row->id)) {
            $table_content .= "<input type='radio' name='assign' value='$row->id'>";
        } else {
            $table_content .= '-';
        }
        $table_content .= "</td>\n    </tr>";
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
                                    <th class='left' colspan='2'>$m[title]</th>
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
