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

require_once '../../../include/lib/course.class.php';
require_once '../../../modules/create_course/functions.php';

function api_method($access) {

    if (!$access->isValid) {
        Access::error(100, "Authentication required");
    }

    //Create course with post request
    if ($_SERVER['REQUEST_METHOD'] == 'POST') {

        $course = new Course();

        $ok = register_posted_variables([
            'title'             => true,
            'department_id'     => true,
            'description'       => false,
            'password'          => false,
            'prof_names'        => false,
            'flipped_flag'      => false,
            'public_code'       => false,
            'doc_quota'         => false,
            'group_quota'       => false,
            'video_quota'       => false,
            'dropbox_quota'     => false,
            'lang'              => false,
            'visible'           => false,
            'course_license'    => false,
            'view_type'         => false,
        ]);

        if (!$ok) {
            Access::error(2, 'For creating a cousre, the following inputs are mandatory: title, department_id');
        }

        $title = $GLOBALS['title'];
        $department_id = $GLOBALS['department_id'];

        $code = strtoupper(new_code($department_id));
        $code = str_replace(' ', '', $code);

        $description = isset($_POST['description']) ? $GLOBALS['description'] : "";
        $password = isset($_POST['password']) ? $_POST['password'] : "";
        $prof_names = isset($_POST['prof_names']) ? $GLOBALS['prof_names'] : '';
        $flipped_flag = isset($_POST['flipped_flag']) ? $GLOBALS['flipped_flag'] : 0;
        $public_code = isset($_POST['public_code']) ? $GLOBALS['public_code'] : $code;

        $doc_quota = isset($_POST['doc_quota']) && intval($GLOBALS['doc_quota']) > 0 ? $GLOBALS['doc_quota'] : get_config('doc_quota');
        if (isset($_POST['doc_quota']) && intval($GLOBALS['doc_quota']) <= 0) {
            Access::error(20, 'doc_quota input must be a number and higher than zero');
        }

        $group_quota = isset($_POST['group_quota']) && intval($GLOBALS['group_quota']) > 0 ? $GLOBALS['group_quota'] : get_config('group_quota');
        if (isset($_POST['group_quota']) && intval($GLOBALS['group_quota']) <= 0) {
            Access::error(20, 'group_quota input must be a number and higher than zero');
        }

        $video_quota = isset($_POST['video_quota']) && intval($GLOBALS['video_quota']) > 0 ? $GLOBALS['video_quota'] : get_config('video_quota');
        if (isset($_POST['video_quota']) && intval($GLOBALS['video_quota']) <= 0) {
            Access::error(20, 'video_quota input must be a number and higher than zero');
        }

        $dropbox_quota = isset($_POST['dropbox_quota']) && intval($GLOBALS['dropbox_quota']) > 0 ? $GLOBALS['dropbox_quota'] : get_config('dropbox_quota');
        if (isset($_POST['dropbox_quota']) && intval($GLOBALS['dropbox_quota']) <= 0) {
            Access::error(20, 'dropbox_quota input must be a number and higher than zero');
        }

        if (isset($_POST['lang'])) {
            $session = new Session();
            $valid_languages = $session->active_ui_languages;
            if (in_array($_POST['lang'], $valid_languages)) {
                $lang = $_POST['lang'];
            } else {
                Access::error(20, 'lang input must be one of the following: ' . implode(", ", $valid_languages));
            }
        } else {
            $lang = 'el';
        }

        if (isset($_POST['visible'])) {
            $visible_mapping = array(
                'closed' => 0,
                'registration' => 1,
                'open' => 2,
                'inactive' => 3
            );
            if (array_key_exists($_POST['visible'], $visible_mapping)) {
                $visible = $visible_mapping[$_POST['visible']];
            } else {
                Access::error(20, 'visible input must be one of the following: closed / registration / open / inactive');
            }
        } else {
            $visible = '2';
        }

        if (isset($_POST['course_license'])) {
            $license_mapping = array(
                'No' => 0,
                'CC' => 1,
                'CC-ShareAlike' => 2,
                'CC-NoDerivatives' => 3,
                'CC-NonCommercial' => 4,
                'CC-NonCommercialShareAlike' => 5,
                'CC-NonCommercialNoDerivatives' => 6,
                'AllRights' => 10
            );
            if (array_key_exists($_POST['course_license'], $license_mapping)) {
                $course_license = $license_mapping[$_POST['course_license']];
            } else {
                Access::error(20, 'course_license input must be one of the following: No (No license specified) / AllRights (All rights reserved) / CC (CC - Attribution) / CC-ShareAlike (CC - Attribution-ShareAlike) / CC-NoDerivatives (CC - Attribution-NoDerivatives) / CC-NonCommercial (CC - Attribution-NonCommercial) / CC-NonCommercialShareAlike (CC - Attribution-NonCommercial-ShareAlike) / CC-NonCommercialNoDerivatives (CC - Attribution-NonCommercial-NoDerivatives)');
            }

        } else {
            $course_license = '0';
        }

        if (isset($_POST['view_type'])) {
            $valid_view_types = ['simple', 'units', 'wall', 'flippedclassroom'];
            if (in_array($_POST['view_type'], $valid_view_types)) {
                $view_type = $_POST['view_type'];
            } else {
                Access::error(20, 'view_type input must be one of the following: simple (Simple form) / units (Course with modules (weekly, thematic)) / wall (Wall form) / flippedclassroom (Inverted Classroom Model)');
            }
        } else {
            $view_type = 'simple';
        }

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
            $course_license, $prof_names, $public_code, $doc_quota * 1024 * 1024,
            $video_quota * 1024 * 1024, $group_quota * 1024 * 1024,
            $dropbox_quota * 1024 * 1024, $password, $flipped_flag, $view_type, $description);

        $new_course_id = $result->lastInsertID;
        create_modules($new_course_id);

        $course->refresh($new_course_id, [$department_id]);

        course_index($code);

        header('Content-Type: application/json');
        echo json_encode([
            'id'                => $new_course_id,
            'title'             => $title,
            'code'              => $code,
            'department id'     => $department_id,
            'password'          => $password,
            'lang'              => $lang,
            'visible'           => $visible,
            'course_license'    => $course_license,
            'prof_names'        => $prof_names,
            'public_code'       => $public_code,
            'view_type'         => $view_type,
            'description'       => $description,
            'doc_quota'         => $doc_quota,
            'group_quota'       => $group_quota,
            'video_quota'       => $video_quota,
            'dropbox_quota'     => $dropbox_quota,


        ], JSON_UNESCAPED_UNICODE);
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
