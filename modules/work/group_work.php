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

require_once '../../include/baseTheme.php';
require_once 'utilities.php';
require_once 'functions.php';
require_once 'modules/progress/AssignmentEvent.php';
require_once 'modules/progress/AssignmentSubmitEvent.php';
require_once 'modules/analytics/AssignmentAnalyticsEvent.php';
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
    $gids = user_group_info($uid, $course_id);
    if (!empty($gids)) {
        $gids_sql_ready = implode(',',array_keys($gids));
    } else {
        $gids_sql_ready = "''";
    }
    $res = Database::get()->queryArray("SELECT *, CAST(UNIX_TIMESTAMP(deadline)-UNIX_TIMESTAMP(NOW()) AS SIGNED) AS time,
                                                         CAST(UNIX_TIMESTAMP(start_date_review)-UNIX_TIMESTAMP(NOW()) AS SIGNED) AS time_start,
                                                         CAST(UNIX_TIMESTAMP(due_date_review)-UNIX_TIMESTAMP(NOW()) AS SIGNED) AS time_due
                                                     FROM assignment
                                                     WHERE course_id = ?d                                                        
                                                        AND active = 1
                                                        AND (assign_to_specific = 0 OR
                                                             id IN
                                                               (SELECT assignment_id FROM assignment_to_specific WHERE user_id = ?d
                                                                UNION
                                                                SELECT assignment_id FROM assignment_to_specific
                                                                   WHERE group_id != 0 AND group_id IN ($gids_sql_ready)))",
                        $course_id, $uid);
    $data['res'] = $res;
    $data['group_id'] = $group_id;

    view('modules.work.group_work', $data);
} elseif (isset($_POST['assign'])) {
    group_submit_work($uid, $group_id, $_POST['assign'], $_POST['file']);
} else {
    header("Location: index.php?course=$course_code");
}

/**
 * @brief Insert a group work submitted by user uid to assignment id
 * @param $uid
 * @param $group_id
 * @param $id
 * @param $file
 * @return void
 */
function group_submit_work($uid, $group_id, $id, $file) {

    global $groupPath, $langUploadError, $langUploadSuccess, $workPath, $langTheFile,
            $course_id, $langWasSubmitted, $group_sql, $webDir, $course_code, $is_editor;

    $ext = get_file_extension($file);
    $local_name = greek_to_latin('Group ' . $group_id . (empty($ext) ? '' : '.' . $ext));
    $q = Database::get()->querySingle("SELECT path, filename FROM document WHERE $group_sql AND course_id = ?d", $course_id);
    $original_filename = $q->filename;
    $file = $q->path;
    $source = $groupPath . $file;
    $destination = work_secret($id) . "/$local_name";

    delete_submissions_by_uid($uid, $group_id, $id, [$destination]);

    if (is_dir($source)) {
        $original_filename = $original_filename . '.zip';
        $zip_filename = $webDir . 'courses/temp/' . safe_filename('zip');
        zip_documents_directory($zip_filename, $file, $is_editor);
        $source = $zip_filename;
    }
    if (copy($source, "$workPath/$destination")) {
        Database::get()->query("INSERT INTO assignment_submit(uid, assignment_id, submission_date, submission_ip, file_path, file_name, comments, group_id)
                                VALUES (?d, ?d,  ". DBHelper::timeAfter() . ", ?s, ?s, ?s, ?s, ?d)",
                            $uid, $id, $_SERVER['REMOTE_ADDR'], $destination, $original_filename, $_POST['comments'], $group_id);

        Session::Messages("$langUploadSuccess <br>$langTheFile \"$original_filename\" $langWasSubmitted", "alert-success");
    } else {
        Session::Messages("$langUploadError", "alert-danger");
    }
    redirect_to_home_page("modules/group/document.php?course=$course_code&group_id=$group_id");
}
