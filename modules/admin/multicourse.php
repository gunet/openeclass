<?php

/* ========================================================================
 * Open eClass 3.0
 * E-learning and Course Management System
 * ========================================================================
 * Copyright 2003-2014  Greek Universities Network - GUnet
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

$require_departmentmanage_user = true;
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

    $departments = isset($_POST['department']) ? arrayValuesDirect($_POST['department']) : array();
    // validation in case it skipped JS validation for department(s)
    if (count($departments) < 1 || empty($departments[0])) {
        Session::Messages($langEmptyAddNode);
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
            $prof_not_found = false;
            if (isset($info[1])) {
                $prof_info = trim($info[1]);
                $prof_uid = find_prof(trim($info[1]));
                if ($prof_info and ! $prof_uid) {
                    $prof_not_found = true;
                }
            }
            if ($prof_uid) {
                $prof_name = uid_to_name($prof_uid);
            } else {
                $prof_name = '';
            }
            list($code, $cid) = create_course('', $_POST['lang'], $title, '', $departments, $vis, $prof_name, $_POST['password']);
            if ($cid) {
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
                $error_messages[] = "<b>" . q($title) . '</b>: ' . q($langMultiCourseCreated).'<br>' . 
                        q($langTeacher) . ': <b>' .
                        q($prof_info) . '</b>: ' . q($langNoUsersFound2);
            } else {
                $sucess_messages[] = "<b>" . q($title) . '</b>: ' . q($langMultiCourseCreated).'<br>' 
                        . q($langTeacher) . ': <b>' . q($prof_name) . '</b>';
            }
        }
        $line = strtok("\n");
    }
    if (!empty($sucess_messages)) Session::Messages ($sucess_messages, 'alert-success');
    if (!empty($error_messages)) Session::Messages ($error_messages);
    redirect_to_home_page('modules/admin/multicourse.php');
}

$toolName = $langMultiCourse;
$navigation[] = array('url' => 'index.php', 'name' => $langAdmin);

$data['action_bar'] = action_bar(array(
    array('title' => $langBack,
        'url' => "index.php",
        'icon' => 'fa-reply',
        'level' => 'primary-label')));    
$tree = new hierarchy();
$course = new course();
$user = new user();

load_js('jstree3');


list($js, $html) = $tree->buildCourseNodePickerIndirect(array('allowables' => $user->getDepartmentIds($uid)));
$head_content .= $js;
$data['html'] = $html;

$data['menuTypeID'] = 3; 
view('admin.courses.multicourse', $data);
        


/**
 * @brief helper function
 * @param type $sql
 * @param type $terms
 * @return boolean
 */
function prof_query($sql, $terms) {
    $result = Database::get()->querySingle("SELECT id FROM user WHERE status = 1 AND ( $sql )", $terms);
    if ($result) {
        return $result->id;
    } else {
        return false;
    }
}

/**
 * @brief find a professor by name ("Name surname") or username
 * @param type $uname
 * @return boolean
 */
function find_prof($uname) {
    if (($uid = prof_query('username = ?s', array($uname)))) {
        return $uid;
    } else {
        $names = explode(' ', $uname);
        if (count($names) == 2 and
                $uid = prof_query('(surname = ?s AND givenname = ?s) OR (givenname = ?s AND surname = ?s)', array($names[0], $names[1], $names[0], $names[1]))) {
            return $uid;
        }
    }
    return false;
}
