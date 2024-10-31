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


require_once '../../../modules/course_info/archive_functions.php';
require_once '../../../modules/course_info/restore_functions.php';
//require_once '../../../include/lib/fileUploadLib.inc.php';

function api_method($access) {
    global $webDir;

    //Clone course with post request
    if ($_SERVER['REQUEST_METHOD'] == 'POST') {

//        if (!$access->isValid) {
//            Access::error(100, "Authentication required");
//        }

        $ok = register_posted_variables([
            'id'                => true,
            'department_id'     => true,
            'add_users'         => false,
            'title'             => false,
        ]);

        if (!$ok) {
            Access::error(2, 'For clone a cousre, the following inputs are mandatory: id, department_id');
        }

        $course_id  = $GLOBALS['id'];
        $_POST['department'][0]   = $GLOBALS['department_id'];

        $course = Database::get()->queryArray("SELECT * FROM course WHERE id = ?s",$course_id );

        $course_code    = $course[0]->code;
        $course_lang    = $course[0]->lang;

        if (isset($_POST['title'])) {
            $course_title = $_POST['title'];
        } else {
            $course_title   = $course[0]->title . " (copy)";
        }

        $course_desc    = $course[0]->description;
        $course_vis     = $course[0]->visible;
        $course_prof    = $course[0]->prof_names;

        if (isset($_POST['add_users'])) {
            if (!in_array($_POST['add_users'], ['all', 'prof', 'none'])) {
                Access::error(2, 'add_users must be one of the following: all, prof, none');
            }
        } else {
            $_POST['add_users']='none';
        }

        $GLOBALS['currentCourseCode'] = $course_code;


        $restoreThis = $webDir . '/courses/tmpUnzipping/api_' . time() . '/' . safe_filename();
        make_dir($restoreThis);
        archiveTables($course_id, $restoreThis);
        recurse_copy($webDir . '/courses/' . $course_code,
            $restoreThis . '/html');


        $tool_content = '';

        create_restored_course($tool_content, $restoreThis, $course_code, $course_lang, $course_title, $course_desc, $course_vis, $course_prof, TRUE);

        $course_code = $GLOBALS['currentCourseCode'];

        header('Content-Type: application/json');
        echo json_encode([
            'id'                => $course_id,
            'title'             => $course_title,
            'code'              => $course_code,
            'department id'     => $GLOBALS['department_id'],
        ], JSON_UNESCAPED_UNICODE);
        exit();

    }

}

chdir('..');
require_once 'apiCall.php';
