<?php

/* ========================================================================
 * Open eClass 3.14
 * E-learning and Course Management System
 * ========================================================================
 * Copyright 2003-2023  Greek Universities Network - GUnet
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

require_once '../../../modules/create_course/functions.php';


function api_method($access) {


    if ( isset($_GET['create']) ) {

        $missingInputs = [];

        if (!isset($_GET['category'])) {
            $missingInputs[] = 'category';
        }

        if (!isset($_GET['title'])) {
            $missingInputs[] = 'title';
        }

        if (!empty($missingInputs)) {
            Access::error(20, 'The following inputs are mandatory: ' . implode(', ', $missingInputs));
        }

        $title = $_GET['title'];
        $category = $_GET['category'];

        $description = isset($_GET['description']) ? $_GET['description'] : "";
        $lang = isset($_GET['lang']) ? $_GET['lang'] : "el";
        $password = isset($_GET['password']) ? $_GET['password'] : "";
        $prof_names = isset($_GET['prof_names']) ? $_GET['prof_names'] : '';
        $flipped_flag = isset($_GET['flipped_flag']) ? $_GET['flipped_flag'] : 0;

        $doc_quota = get_config('doc_quota');
        $group_quota = get_config('group_quota');
        $video_quota = get_config('video_quota');
        $dropbox_quota = get_config('dropbox_quota');

        if (isset($_GET['visible'])) {
            $valid_visible = ['0','1','2','3'];
            if (in_array($_GET['visible'], $valid_visible)) {
                $visible = $_GET['visible'];
            } else {
                Access::error(20, 'visible input must be one of the following: 0 (Closed course) / 1 (Registration is required) / 2 (Open course) / 3 (Inactive course)');
            }
        } else {
            $visible = '2';
        }

        if (isset($_GET['course_license'])) {
            $valid_course_license = ['0', '10', 'cc'];
            if (in_array($_GET['course_license'], $valid_course_license)) {
                $course_license = $_GET['course_license'];
            } else {
                Access::error(20, 'course_license input must be one of the following: 0 (Not defined) / 10 (All rights reserved) / cc (Creative Commons license-CC)');
            }
        } else {
            $course_license = '0';
        }

        if (isset($_GET['view_type'])) {
            $valid_view_types = ['simple', 'units', 'wall', 'flippedclassroom'];
            if (in_array($_GET['view_type'], $valid_view_types)) {
                $view_type = $_GET['view_type'];
            } else {
                Access::error(20, 'view_type input must be one of the following: simple (Simple form) / units (Course with modules (weekly, thematic)) / wall (Wall form) / flippedclassroom (Inverted Classroom Model)');
            }
        } else {
            $view_type = 'simple';
        }

        $code = strtoupper(new_code($category));
        $code = str_replace(' ', '', $code);

        if (!create_course_dirs($code)) {
            Access::error(20, 'An error has occurred. Please contact the platform administrator.');
        }

        $result = Database::get()->query("INSERT INTO course SET
                        code = ?s,
                        lang = ?s,
                        title = ?s,
                        visible = ?d,
                        course_license = ?d,
                        prof_names = ?s,
                        public_code = ?s,
                        doc_quota = ?f,
                        video_quota = ?f,
                        group_quota = ?f,
                        dropbox_quota = ?f,
                        password = ?s,
						flipped_flag = ?s,
                        view_type = ?s,
                        start_date = " . DBHelper::timeAfter() . ",
                        keywords = '',
                        created = " . DBHelper::timeAfter() . ",
                        glossary_expand = 0,
                        glossary_index = 1,
                        description = ?s",
            $code, $lang, $title, $visible,
            $course_license, $prof_names, $code, $doc_quota * 1024 * 1024,
            $video_quota * 1024 * 1024, $group_quota * 1024 * 1024,
            $dropbox_quota * 1024 * 1024, $password, $flipped_flag, $view_type, $description);

        $new_course_id = $result->lastInsertID;
        create_modules($new_course_id);

        course_index($code);

        header('Content-Type: application/json');
        echo json_encode('Course with title ' . $title . ' in category with id ' . $category . ' created', JSON_UNESCAPED_UNICODE);
        exit();


    }


    if ( isset($_GET['course_id']) && isset($_GET['uname']) ) {

        $courseCode = $_GET['course_id'];
        $userName = $_GET['uname'];

        $course = Database::get()->queryArray("SELECT cu.status, cu.editor, cu.reg_date, cu.course_id, cu.user_id
            FROM course_user cu
            JOIN user u ON cu.user_id = u.id
            JOIN course c ON cu.course_id = c.id
            WHERE c.code = ?s AND u.username = ?s",$courseCode, $userName );

        $result = array();

        if (count($course) > 0) {

            $result['registered'] = true;
            $result['course_code'] = $courseCode;
            $result['course_id'] = $course[0]->course_id;
            $result['uname'] = $userName;
            $result['user_id'] = $course[0]->user_id;


            switch ($course[0]->status) {
                case 1:
                    $result['status'] = 'teacher';
                    break;
                case 5:
                    switch ($course[0]->editor) {
                        case 1:
                            $result['status'] = 'teacher_assistant';
                            break;
                        default:
                            $result['status'] = 'student';
                            break;
                    }
                    break;
            }

            $result['reg_date'] = $course[0]->reg_date;

        } else {
            $result['registered'] = false;
        }

        header('Content-Type: application/json');
        echo json_encode($result, JSON_UNESCAPED_UNICODE);
        exit();
    }

    if ( isset($_GET['course_id']) ) {

        $courseCode = $_GET['course_id'];

        $course = Database::get()->queryArray("SELECT * FROM course WHERE code = ?s",$courseCode );

        header('Content-Type: application/json');
        echo json_encode($course, JSON_UNESCAPED_UNICODE);
        exit();
    }

    if ( isset($_GET['uname']) ) {

        $userName = $_GET['uname'];

        $course = Database::get()->queryArray("SELECT c.*
                        FROM course c
                        JOIN course_user cu ON cu.course_id = c.id
                        JOIN user u ON u.id = cu.user_id
                        WHERE cu.status = 1 AND u.username = ?s",$userName );

        header('Content-Type: application/json');
        echo json_encode($course, JSON_UNESCAPED_UNICODE);
        exit();
    }

    $courses = Database::get()->queryArray('SELECT course.code AS id,
            course.title AS name,
            course.title AS shortname,
            course_department.department AS categoryid,
            "" AS summary,
            course.description AS description,
            created AS timecreated,
            created AS timemodified
        FROM course
            JOIN course_department ON course.id = course_department.course
        WHERE visible <> ' . COURSE_INACTIVE . '
        ORDER BY course.title');

    header('Content-Type: application/json');
    echo json_encode($courses, JSON_UNESCAPED_UNICODE);
    exit();
}

chdir('..');
//require_once 'index.php';
require_once 'apiCall.php';


