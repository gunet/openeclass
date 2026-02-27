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

$require_course_admin = true;
$require_current_course = true;

require_once '../../include/baseTheme.php';
require_once 'archive_functions.php';
require_once 'restore_functions.php';

$toolName = $langImportCourse;

if (!($is_admin || get_config('allow_teacher_import_course'))) {
    header("Location:" . $urlServer . "index.php");
    exit();
}

if (isset($_GET['do_fetch'])) {
    set_time_limit(0);
    $old_course_id = $_POST['import_course_id'];
    $old_course_code = course_id_to_code($old_course_id);
    // archive the old course
    doArchive($old_course_id, $old_course_code);

    $backup_date_short = date('Ymd');
    $backup_file = "$webDir/courses/archive/$_SESSION[csrf_token]/$old_course_code-$backup_date_short.zip";

    $retArr = unpack_zip_inner($backup_file, false);
    foreach ($retArr as $entry) {
        $path = $entry['path'];
        $file = q($entry['file']);
        $course = q($entry['course']);
        $restoreThis = q($path);
    }
    $base = $restoreThis;

    if (($data = get_serialized_file('course'))) {
        $data = $data[0];
        $course_title = $data['title'];
        $course_units = get_serialized_file('course_units');
        $unit_resources = get_serialized_file('unit_resources');
        if (isset($data['description'])) {
            $description = $data['description'];
        } elseif (($unit_data = search_table_dump($course_units, 'order', -1))) {
            if (($resource_data = search_table_dump($unit_resources, 'order', -1))) {
                $description = purify($resource_data['comments']);
            }
        } else {
            $description = '';
        }
        $course_desc = $description;
        $course_lang = $data['lang'];
        $course_prof = $data['prof_names'];
        $course_vis = $data['visible'];
        // fetch and create course
        create_restored_course($tool_content, $restoreThis, $course_code, $course_lang, $course_title, $course_desc, $course_vis, $course_prof, false, true);
    }

    $tool_content .= "<div class='col-12'>
                        <div class='alert alert-info'>
                            <i class='fa-solid fa-circle-info fa-lg'></i><span>$langImportCourseCompleted</span>
                        </div>
                     </div>";

    stop_output_buffering();
}

draw($tool_content, 2, null, $head_content);
