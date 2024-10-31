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


$require_mlogin = true;
$require_noerrors = true;
require_once('minit.php');

$courses = array();

if ($_SESSION['status'] == USER_TEACHER or $_SESSION['status'] == USER_STUDENT) {
    $courses = Database::get()->queryArray("
        SELECT course.code,
               course.lang,
               course.title,
               course.keywords,
               course.visible,
               course.prof_names,
               course.public_code,
               course_user.status AS status,
               course_user.favorite favorite
          FROM course JOIN course_user 
            ON course.id = course_user.course_id 
            AND course_user.user_id = ?d 
            AND (course.visible != " . COURSE_INACTIVE . " OR course_user.status = " . USER_TEACHER . ")
      ORDER BY favorite DESC, status ASC, visible ASC, title ASC", $uid);
} else {
    echo RESPONSE_FAILED;
    exit();
}

list($coursesDom, $coursesDomRoot) = createCoursesDom($courses);

if (!defined('M_NOTERMINATE')) {
    echo $coursesDom->saveXML();
    exit();
}

//////////////////////////////////////////////////////////////////////////////////////

function createCoursesDom($coursesArr) {
    global $langMyCoursesProf, $langMyCoursesUser;

    $dom = new DomDocument('1.0', 'utf-8');

    if (defined('M_ROOT')) {
        $root0 = $dom->appendChild($dom->createElement(M_ROOT));
        $root = $root0->appendChild($dom->createElement('courses'));
        $retroot = $root0;
    } else {
        $root = $dom->appendChild($dom->createElement('courses'));
        $retroot = $root;
    }

    if (isset($coursesArr) && count($coursesArr) > 0) {

        $k = 0;
        $this_status = 0;

        foreach ($coursesArr as $course) {

            $old_status = $this_status;
            $this_status = $course->status;

            if ($k == 0 || ($old_status != $this_status)) {
                $cg = $root->appendChild($dom->createElement('coursegroup'));
                $gname = ($this_status == 1) ? $langMyCoursesProf : $langMyCoursesUser;
                $cg->appendChild(new DOMAttr('name', $gname));
            }

            $c = $cg->appendChild($dom->createElement('course'));

            $titleStr = ($course->code === $course->public_code) ? $course->title : $course->title . ' - ' . $course->public_code;

            $c->appendChild(new DOMAttr('code', $course->code));
            $c->appendChild(new DOMAttr('title', $titleStr));
            $c->appendChild(new DOMAttr('description', ""));

            $k++;
        }
    }

    $dom->formatOutput = true;
    return array($dom, $retroot);
}
