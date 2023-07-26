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



function api_method($access) {


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


