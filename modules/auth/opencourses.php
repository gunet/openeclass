<?php

/* ========================================================================
 * Open eClass 3.0
 * E-learning and Course Management System
 * ========================================================================
 * Copyright 2003-2014 Greek Universities Network - GUnet
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

include '../../include/baseTheme.php';
require_once 'include/lib/hierarchy.class.php';

$countCallback = null;
$data['isInOpenCoursesMode'] = (defined('LISTING_MODE') && LISTING_MODE === 'COURSE_METADATA');
$showEmpty = true;

if ($data['isInOpenCoursesMode']) {
    require_once 'modules/course_metadata/CourseXML.php';
    $countCallback = CourseXMLElement::getCountCallback();
    $showEmpty = false;
    // exit if feature disabled
    if (!get_config('opencourses_enable')) {
        header("Location: {$urlServer}");
        exit();
    }
}

$data['tree'] = new Hierarchy();

$toolName = $langListCourses;
if ($data['isInOpenCoursesMode']) {
    $navigation[] = array('url' => '../auth/listfaculte.php', 'name' => $langSelectFac);
} else {
    $navigation[] = array('url' => 'listfaculte.php', 'name' => $langSelectFac);
}

if (isset($_GET['fc'])) {
    $data['fc'] = intval($_GET['fc']);
}

// parse the faculte id in a session
// This is needed in case the user decides to switch language.
if (isset($data['fc'])) {
    $_SESSION['fc_memo'] = $data['fc'];
} else {
    $data['fc'] = $_SESSION['fc_memo'];
}


$fac = Database::get()->querySingle("SELECT id, name, visible FROM hierarchy WHERE id = ?d", $data['fc']);
if (!$fac) {
    die("ERROR: no faculty with id $data[fc]");
}
// validate department
if (!$data['tree']->checkVisibilityRestrictions($fac->id, $fac->visible, array('respectVisibility' => true))) {
    redirect_to_home_page();
}


// use the following array for the legend icons
$data['icons'] = array(
    2 => "<img src='$themeimg/lock_open.png' alt='" . $langOpenCourse . "' title='" . $langOpenCourse . "' width='16' height='16' />",
    1 => "<img src='$themeimg/lock_registration.png' alt='" . $langRegCourse . "' title='" . $langRegCourse . "' width='16' height='16' />",
    0 => "<img src='$themeimg/lock_closed.png' alt='" . $langClosedCourse . "' title='" . $langClosedCourse . "' width='16' height='16' />"
);

$data['action_bar'] = action_bar(array(
                                array('title' => $langBack,
                                      'url' => $urlServer,
                                      'icon' => 'fa-reply',
                                      'level' => 'primary-label',
                                      'button-class' => 'btn-default')
                            ),false);
if (count($data['tree']->buildRootsArray()) > 1) {
    $data['buildRoots'] = $data['tree']->buildRootsSelectForm($data['fc']);
}


list($childCount, $childHTML) = $data['tree']->buildDepartmentChildrenNavigationHtml($data['fc'], 'opencourses', $countCallback, array('showEmpty' => $showEmpty, 'respectVisibility' => true));;
$data['childHTML'] = $childHTML;

$queryCourseIds = '';
$queryExtraSelect = '';
$queryExtraJoin = '';
$queryExtraJoinWhere = '';
$runQuery = true;

if ($data['isInOpenCoursesMode']) {
    // find subnode's certified opencourses
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

$data['courses'] = array();

if ($runQuery) {
    $data['courses'] = Database::get()->queryArray("SELECT course.code k,
                               course.public_code c,
                               course.title i,
                               course.visible visible,
                               course.prof_names t,
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

$data['course_data'] = array();
if (count($data['courses']) > 0) {

    $data['displayGuestLoginLinks'] = ($uid == 0) && (get_config('course_guest') == 'link');

    foreach ($data['courses'] as $mycours) {

        if ($data['displayGuestLoginLinks']) {
            $data['course_data'][$mycours->id]['userguest'] =  Database::get()->querySingle('SELECT username, password FROM course_user, user
                WHERE course_user.user_id = user.id AND user.status = ?d and course_id = ?d',
                USER_GUEST, $mycours->id);
        }
    }
}



$data['menuTypeID'] = isset($uid) && $uid ? 1 : 0 ;
view('modules.auth.opencourses', $data);

