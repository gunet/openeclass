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

/**
 * @file portfolio.php
 * @brief This component creates the content of the start page when the user is logged in 
 */

$require_login = true;

include '../include/baseTheme.php';
require_once 'perso_functions.php';

$pageName = $langMyPersoLessons;
//  Get user's course info
    if ($session->status == USER_TEACHER) {
        $myCourses = Database::get()->queryArray("SELECT course.id course_id,
                             course.code code,
                             course.public_code,
                             course.title title,
                             course.prof_names professor,
                             course.lang,
                             course_user.status status	                        
                       FROM course, course_user, user
                       WHERE course.id = course_user.course_id AND
                             course_user.user_id = ?d AND
                             user.id = ?d
                       ORDER BY course.title, course.prof_names", $uid, $uid);
    } else {
        $myCourses = Database::get()->queryArray("SELECT course.id course_id,
                             course.code code,
                             course.public_code,
                             course.title title,
                             course.prof_names professor,
                             course.lang,
                             course_user.status status                                
                       FROM course, course_user, user
                       WHERE course.id = course_user.course_id AND
                             course_user.user_id = ?d AND
                             user.id = ?d AND
                             course.visible != ?d
                       ORDER BY course.title, course.prof_names", $uid, $uid, COURSE_INACTIVE);
    }
    $tool_content .= action_bar(array(
        array(
            'title' => $langBack,
            'icon' => 'fa-reply',
            'level' => 'primary-label',
            'url' => 'portfolio.php'
        )
    ));
    if($myCourses) {
        $tool_content .= "
            <div class='table-responsive'>
                <table class='table-default'>
                    <thead class='list-header'>
                        <th>$langTitle</th>
                        <th>$langTeacher</th>
                        <th class='text-center'>".icon('fa-gears')."</th>
                    </thead>
                    <tbody>";
        foreach($myCourses as $course) {
            if ($course->status == USER_STUDENT) { 
              $action_button = icon('fa-sign-out', $langUnregCourse, "${urlServer}main/unregcours.php?cid=$course->course_id&amp;uid=$uid");
            } elseif ($course->status == USER_TEACHER) {
                $action_button = icon('fa-wrench', $langAdm, "${urlServer}modules/course_info/?from_home=true&amp;course=" . $course->code);
            }
            $tool_content .= "
                    <tr>
                        <td><strong><a href='{$urlServer}courses/$course->code'>".q($course->title)."</a></strong> (".q($course->public_code).")</td>
                        <td>".q($course->professor)."</td>
                        <td class='text-center'>
                            $action_button
                        </td>
                    </tr>
            ";
        }
        $tool_content .= "
                    </tbody>
                </table>
            </div>";         
    } else {
       $tool_content .= "<div class='alert alert-warning'>$langNoCourses</div>"; 
    }
draw($tool_content, 1, null, $head_content, null, null);
