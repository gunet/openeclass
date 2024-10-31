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

$require_login = true;
require_once '../include/baseTheme.php';

$courses = [];
$myCourses = Database::get()->queryArray("SELECT course.id                                                                                                                                                          
                                                   FROM course, course_user
                                                   WHERE course.id = course_user.course_id 
                                                        AND course_user.user_id = ?d ",
                                                   $uid);
if ($myCourses) {
    foreach ($myCourses as $myCourse) {
        $courses[] = $myCourse->id;
    }
}

$cid = course_code_to_id($_GET['course']);
if (isset($_GET['from_ext_view'])) {
    $from_ext_view = intval($_GET['from_ext_view']);
}

if (isset($_GET['fav']) and in_array($cid, $courses)) { // mark course as favorite.
    $fav = intval($_GET['fav']);
    if ($fav == 1) {
        Database::get()->query("UPDATE course_user SET favorite = " . DBHelper::timeAfter() ." WHERE user_id = ?d AND course_id = ?d", $uid, $cid);
    } else {
        Database::get()->query("UPDATE course_user SET favorite = NULL WHERE user_id = ?d AND course_id = ?d", $uid, $cid);
    }
    if (isset($from_ext_view)) {
        redirect_to_home_page('main/my_courses.php');
    } else {
        redirect_to_home_page('main/portfolio.php');
    }
}
