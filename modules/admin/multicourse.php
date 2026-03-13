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

$require_departmentmanage_user = true;
$require_help = true;
$helpTopic = 'users_administration';
$helpSubTopic = 'mass_courses_creation';

require_once '../../include/baseTheme.php';
require_once 'include/log.class.php';
require_once 'include/lib/course.class.php';
require_once 'include/lib/user.class.php';
require_once 'include/lib/hierarchy.class.php';
require_once 'modules/admin/hierarchy_validations.php';
require_once 'modules/create_course/functions.php';

if (isset($_POST['submit'])) {
    if (!isset($_POST['token']) || !validate_csrf_token($_POST['token'])) csrf_token_error();
    checkSecondFactorChallenge();
    $line = strtok($_POST['courses'], "\n");

    // $departments = isset($_POST['department']) ? arrayValuesDirect($_POST['department']) : array();
    $departments = $_POST['department'] ?? array();
    // validation in case it skipped JS validation for department(s)
    if (count($departments) < 1 || empty($departments[0])) {
        Session::flash('message',$langEmptyAddNode);
        Session::flash('alert-class', 'alert-success');
        header("Location:" . $urlServer . "modules/admin/multicourse.php");
        exit;
    }

    $vis = intval($_POST['formvisible']);
    $sucess_messages = [];
    $error_messages = [];
    while ($line !== false) {
        $line = canonicalize_whitespace($line);
        if (!empty($line)) {
            $info = explode('|', $line);
            $title = $info[0];
            $prof_uid = null;
            $prof_not_found = true;
            if (isset($info[1])) {
                $prof_info = trim($info[1]);
                $prof_uid = find_prof(trim($info[1]));
                if ($prof_info and $prof_uid > 0) {
                    $prof_not_found = false;
                }
            }
            list($code, $cid) = create_course('', $_POST['lang'], $title, '', $departments, $vis, $prof_name, $_POST['password']);
            if ($cid) {
                Database::get()->query("UPDATE course SET is_collaborative = ?d WHERE id = ?d",$_POST['courseType'],$cid);
                if ($prof_uid) {
                    Database::get()->query("INSERT INTO course_user
                                SET course_id = $cid,
                                    user_id = $prof_uid,
                                    status = 1,
                                    tutor = 1,
                                    reg_date = " . DBHelper::timeAfter() . " ,
                                    document_timestamp = " . DBHelper::timeAfter() . "");
                }
                create_modules($cid);
            }
            if ($code) {
                course_index($code);
            }
            if ($prof_not_found) {
                $message[] = "<strong>" . q($title) . "</strong>: $langMultiCourseCreated<br><br>";
            } else {
                $message[] = "<strong>" . q($title) . "</strong>: $langMultiCourseCreated -- $langTeacher : <strong>" . q(uid_to_name($prof_uid)) . "</strong><br><br>";
            }
        }
        $line = strtok("\n");
    }
    Session::flash('message', $message);
    Session::flash('alert-class', 'alert-success');
    redirect_to_home_page('modules/admin/multicourse.php');
}

$toolName = $langAdmin;
$pageName = $langMultiCourse;
$navigation[] = array('url' => 'index.php', 'name' => $langAdmin);

$tree = new hierarchy();
$course = new course();
$user = new user();

load_js('jstree3');

$options = $is_admin ? array() : array('allowables' => $user->getDepartmentIds($uid));
list($js, $html) = $tree->buildCourseNodePicker($options);
$head_content .= $js;
$data['html'] = $html;
$data['default_access'] = intval(get_config('default_course_access', COURSE_REGISTRATION));

view('admin.courses.multicourse', $data);


/**
 * @brief find a professor by name ("Name surname") or username
 * @param type $uname
 * @return boolean
 */
function find_prof($uname) {

    $names = explode(' ', $uname);
    if (count($names) == 1) {
        $q = Database::get()->querySingle("SELECT id FROM user WHERE status = " . USER_TEACHER . " AND username = ?s", $names[0]);
        if ($q) {
            return $q->id;
        }
    } else if (count($names) == 2) {
        $q = Database::get()->querySingle("SELECT id FROM user WHERE status = " . USER_TEACHER . " AND 
                    (surname = ?s AND givenname = ?s) OR (givenname = ?s AND surname = ?s)'", $names[0], $names[1], $names[0], $names[1]);
        if ($q) {
            return $q->id;
        }
    }
    return 0;
}