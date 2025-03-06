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

include '../../include/baseTheme.php';
require_once 'include/course_settings.php';
require_once 'include/log.class.php';
require_once 'include/lib/hierarchy.class.php';
require_once 'include/lib/user.class.php';
require_once 'modules/course_metadata/CourseXML.php';

load_js('tools.js');

$toolName = $langListCourses;
$countCallback = null;
$data['isInOpenCoursesMode'] = (defined('LISTING_MODE') && LISTING_MODE === 'COURSE_METADATA');
$tree = new Hierarchy();
$courses_list = [];
$showEmpty = true;
$unlock_all_courses = false;

if (isset($_SESSION['uid'])) {
    if ($is_power_user) {
        $unlock_all_courses = true;
    } elseif ($is_departmentmanage_user) {
        $user = new User();
        $subtrees = $tree->buildSubtrees($user->getAdminDepartmentIds($uid));
        $unlock_all_courses = in_array($facid, $subtrees);
    }

    $restrictedCourses = array();
    if (isset($_POST['changeCourse']) and is_array($_POST['changeCourse'])) {
        $changeCourse = $_POST['changeCourse'];
    } else {
        $changeCourse = array();
    }
    if (isset($_POST['selectCourse']) and is_array($_POST['selectCourse'])) {
        $selectCourse = $_POST['selectCourse'];
    } else {
        $selectCourse = array();
    }

    if (isset($_POST['submit'])) {
        foreach ($changeCourse as $key => $value) {
            $cid = intval($value);
            if (!in_array($cid, $selectCourse)) {
                Database::get()->query("DELETE FROM course_user "
                    . " WHERE status <> ?d AND status <> ?d AND user_id = ?d "
                    . " AND course_id = ?d", USER_TEACHER, USER_GUEST, $uid, $cid);
                // logging
                Log::record($cid, MODULE_ID_USERS, LOG_DELETE, array('uid' => $uid, 'right' => 0));
            }
        }

        foreach ($selectCourse as $key => $value) {
            $cid = intval($value);
            $course_info = Database::get()->querySingle("SELECT public_code, password, visible FROM course WHERE id = ?d", $cid);
            if ($course_info) {
                if (($course_info->visible == COURSE_REGISTRATION or
                        $course_info->visible == COURSE_OPEN) and !empty($course_info->password) and
                    $course_info->password !== $_POST['pass' . $cid]) {
                    $restrictedCourses[] = $course_info->public_code;
                    continue;
                }
                if (is_restricted($cid) and !in_array($cid, $selectCourse)) { // do not allow registration to restricted course
                    $restrictedCourses[] = $course_info->public_code;
                } else {
                    Database::get()->query("INSERT IGNORE INTO `course_user` (`course_id`, `user_id`, `status`, `reg_date`)
                                        VALUES (?d, ?d, ?d, NOW())", $cid, intval($uid), USER_STUDENT);
                }
            }
        }
    }
}


if ($data['isInOpenCoursesMode']) {
    $navigation[] = array('url' => '../auth/listfaculties.php', 'name' => $langSelectFac);
    require_once 'modules/course_metadata/CourseXML.php';
    $countCallback = CourseXMLElement::getCountCallback();
    $showEmpty = false;
    // exit if feature disabled
    if (!get_config('opencourses_enable')) {
        header("Location: {$urlServer}");
        exit();
    }
} else {
    //$navigation[] = array('url' => 'listfaculties.php', 'name' => $langSelectFac);
}

if (isset($_GET['fc'])) { // fetch specific department
    $data['fc'] = intval($_GET['fc']);
} else if (isset($_SESSION['uid'])) { // fetch user department (default) if user logged in
    $fc = getfcfromuid($_SESSION['uid']);
}

// parse the faculty id in a session
// This is needed in case the user decides to switch language.
if (isset($data['fc'])) {
    $_SESSION['fc_memo'] = $data['fc'];
} elseif (isset($_SESSION['fc_memo'])) {
    $data['fc'] = $_SESSION['fc_memo'];
} else {
    redirect_to_home_page();
}


$fac = Database::get()->querySingle("SELECT id, name, visible FROM hierarchy WHERE id = ?d", $data['fc']);
if (!$fac) {
    die("ERROR: no faculty with id $data[fc]");
}
// validate department
if (!$tree->checkVisibilityRestrictions($fac->id, $fac->visible)) {
    redirect_to_home_page();
}
if (count($tree->buildRootsArray()) > 1) {
    $data['buildRoots'] = $tree->buildRootsSelectForm($data['fc']);
}

list($childCount, $childHTML) = $tree->buildDepartmentChildrenNavigationHtml($data['fc'], 'opencourses', $countCallback, array('showEmpty' => $showEmpty, 'respectVisibility' => true));;

$queryCourseIds = '';
$queryExtraSelect = '';
$queryExtraJoin = '';
$queryExtraJoinWhere = '';
$runQuery = true;
$data['courses'] = [];
$data['course_data'] = [];

if ($data['isInOpenCoursesMode']) { // find sub node's certified open courses
    $opencourses = array();
    Database::get()->queryFunc("SELECT course.id, course.code
                                  FROM course, course_department, course_review
                                 WHERE course.id = course_department.course
                                   AND course.id = course_review.course_id
                                   AND course_department.department = ?d
                                   AND course_review.is_certified = 1", function($course) use (&$opencourses) {
        $opencourses[$course->id] = $course->code;
    }, $data['fc']);

    // construct comma seperated string with open courses ids
    $commaIds = "";
    $i = 0;
    foreach ($opencourses as $courseId => $courseCode) {
        if ($i != 0) {
            $commaIds .= ",";
        }
        $commaIds .= $courseId;
        $i++;
    }

    if (count($opencourses) > 0) {
        $queryCourseIds = " AND course.id IN ($commaIds) ";
        $queryExtraJoin = ", course_review ";
        $queryExtraJoinWhere = " AND course.id = course_review.course_id ";
        $queryExtraSelect = " , course_review.level level ";
    } else {
        $runQuery = false; // left the rest of the code fail safely
    }
}

$myCourses = [];
if (isset($_SESSION['uid'])) { // get user courses
    Database::get()->queryFunc("SELECT course.code course_code, course.public_code public_code,
                                       course.id course_id, status
                                  FROM course_user, course
                                 WHERE course_user.course_id = course.id
                                   AND user_id = ?d", function ($course) use (&$myCourses) {
        $myCourses[$course->course_id] = $course;
    }, intval($uid));
}

if ($runQuery) {
    $data['courses'] = Database::get()->queryArray("SELECT course.code k,
                               course.public_code c,
                               course.title i,
                               course.visible visible,
                               course.course_license cls,
                               course.prof_names t,
                               course.description de,
                               course.course_image img,
                               course.popular_course p,
                               course.is_collaborative clb,
                               course.password password,
                               course.id id
                               $queryExtraSelect
                          FROM course, course_department $queryExtraJoin
                         WHERE course.id = course_department.course
                           $queryExtraJoinWhere
                           AND course_department.department = ?d
                           AND course.visible != " . COURSE_INACTIVE . "
                           $queryCourseIds
                      ORDER BY course.title, course.prof_names", $data['fc']);
}

if (count($data['courses']) > 0) {
    $data['displayGuestLoginLinks'] = ($uid == 0) && (get_config('course_guest') == 'link');
    foreach ($data['courses'] as $mycours) {
        $courses_list[$mycours->id] = array($mycours->k, $mycours->visible);
        if ($data['displayGuestLoginLinks']) {
            $data['course_data'][$mycours->id]['userguest'] =  Database::get()->querySingle('SELECT username, password FROM course_user, user
                WHERE course_user.user_id = user.id AND user.status = ?d and course_id = ?d',
                USER_GUEST, $mycours->id);
        }
    }
}

$data['tree'] = $tree;
$data['childHTML'] = $childHTML;
$data['myCourses'] = $myCourses;
$data['courses_list'] = $courses_list;
$data['unlock_all_courses'] = $unlock_all_courses;

view('modules.auth.opencourses', $data);

/**
 * @brief get user department id
 * @param $uid
 * @return int
 */
function getfcfromuid($uid) {

    $res = Database::get()->querySingle("SELECT department FROM user_department WHERE user = ?d LIMIT 1", $uid);
    if ($res) {
        return $res->department;
    } else {
        return 0;
    }
}

/**
 * @brief get course prerequisites
 * @param $course_id
 * @return string
 */
function getCoursePrerequisites($course_id) {
    global $langCoursePrerequisites;

    $coursePrerequisites = "";
    $prereqsCnt = 0;
    $result = Database::get()->queryArray("SELECT c.*
                                 FROM course_prerequisite cp
                                 JOIN course c on (c.id = cp.prerequisite_course)
                                 WHERE cp.course_id = ?d
                                 ORDER BY c.title", $course_id);
    foreach ($result as $row) {
        $prereqTitle = q($row->title . " (" . $row->public_code . ")");
        if ($prereqsCnt > 0) {
            $coursePrerequisites .= ", ";
        }
        $coursePrerequisites .= $prereqTitle;
        $prereqsCnt++;
    }
    if ($prereqsCnt > 0) {
        $coursePrerequisites = "<br/><small class='text-muted'>$langCoursePrerequisites: " . $coursePrerequisites . "</small>";
    }
    return $coursePrerequisites;
}
