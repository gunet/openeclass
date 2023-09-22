<?php

/* ========================================================================
 * Open eClass 3.10
 * E-learning and Course Management System
 * ========================================================================
 * Copyright 2003-2021  Greek Universities Network - GUnet
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

/**
 * @file portfolio.php
 * @brief This component creates the content of the start page when the user is logged in
 */

$require_login = true;
$require_help = true;
$helpTopic = 'portfolio';
$helpSubTopic = 'my_courses';
include '../include/baseTheme.php';
require_once 'perso_functions.php';

$toolName = $langMyCourses;

//  Get user's course list
$myCourses = Database::get()->queryArray("SELECT course.id course_id,
                     course.code code,
                     course.public_code,
                     course.title title,
                     course.prof_names professor,
                     course.lang,
                     course.visible visible,
                     course_user.status status,
                     course_user.favorite favorite
               FROM course JOIN course_user
                    ON course.id = course_user.course_id
                    AND course_user.user_id = ?d
                    AND (course.visible != " . COURSE_INACTIVE . " OR course_user.status = " . USER_TEACHER . ")
                ORDER BY favorite DESC, status ASC, visible ASC, title ASC", $uid);

$tool_content .= action_bar([
    [ 'title' => $langRegCourses,
      'url' => $urlAppend . 'modules/auth/courses.php',
      'icon' => 'fa-check',
      'level' => 'primary-label',
      'button-class' => 'btn-success' ],
    [ 'title' => $langCourseCreate,
      'url' => $urlAppend . 'modules/create_course/create_course.php',
      'show' => $_SESSION['status'] == USER_TEACHER,
      'icon' => 'fa-plus-circle',
      'level' => 'primary-label',
      'button-class' => 'btn-success' ],
    [ 'title' => $langBack,
      'icon' => 'fa-reply',
      'level' => 'primary-label',
      'url' => 'portfolio.php' ],
], false);

if ($myCourses) {
    $action_button = '';
    $tool_content .= "
        <div class='table-responsive'>
            <table class='table-default'>
                <thead class='list-header'>
                    <th>$langCourse</th>
                    <th class='text-center'>".icon('fa-gears')."</th>
                </thead>
                <tbody>";
    foreach ($myCourses as $course) {
        if (isset($course->favorite)) {
            $favorite_icon = 'fa-star';
            $fav_status = 0;
            $fav_message = '';
        } else {
            $favorite_icon = 'fa-bookmark-o';
            $fav_status = 1;
            $fav_message = $langFavorite;
        }
        $favorite_button = icon($favorite_icon, $fav_message, "course_favorite.php?course=" . $course->code . "&amp;fav=" . $fav_status. "&amp;from_ext_view=1");
        if ($course->status == USER_STUDENT) {
            if (get_config('disable_student_unregister_cours') == 0) {
                $action_button = icon('fa-minus-circle', $langUnregCourse, "{$urlServer}main/unregcours.php?cid=$course->course_id&amp;uid=$uid");
            }
        } elseif ($course->status == USER_TEACHER) {
            $action_button = icon('fa-wrench', $langAdm, "{$urlServer}modules/course_info/?from_home=true&amp;course=" . $course->code);
        }
        $visclass = '';
        if ($course->visible == COURSE_INACTIVE) {
            $visclass = "not_visible";
        }
        $tool_content .= "
                    <tr class='$visclass'>
                        <td><strong><a href='{$urlServer}courses/$course->code/'>".q($course->title)."</a></strong> (".q($course->public_code).")
                            <div><small>" . q($course->professor) . "</small></div>
                        </td>
                        <td class='text-center'>
                            $favorite_button &nbsp;
                            $action_button
                        </td>
                    </tr>";
    }
    $tool_content .= "
                </tbody>
            </table>
        </div>";
} else {
    $tool_content .= "<div class='alert alert-warning'>$langNoCourses</div>";
}
draw($tool_content, 1, null, $head_content, null, null);
