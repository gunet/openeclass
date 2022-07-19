<?php

/* ========================================================================
 * Open eClass
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
 * ========================================================================
 */


/**** Summary info    ****/

$tool_content .= action_bar(array(
                array('title' => $langSystemActions,
                    'url' => "../usage/displaylog.php?from_other=TRUE",
                    'icon' => 'fa-bar-chart',
                    'level' => 'primary-label',
                    'button-class' => 'btn-success'),
                array('title' => $langPlatformGenStats,
                    'url' => "index.php?t=a&g_stats",
                    'icon' => 'fa-bar-chart',
                    'level' => 'primary-label'),
                array('title' => $langStatOfFaculty,
                    'url' => "faculty_stats.php",
                    'icon' => 'fa-bar-chart',
                    'level' => 'primary-label'),
                array('title' => $langMonthlyReport,
                    'url' => "../admin/monthlyReport.php",
                    'icon' => 'fa-bar-chart',
                    'level' => 'primary-label'),
                array('title' => $langOldStats,
                    'url' => "../admin/old_stats.php",
                    'icon' => 'fa-bar-chart',
                    'level' => 'primary-label'),
                array('title' => $langBack,
                    'url' => "../admin/",
                    'icon' => 'fa-reply',
                    'level' => 'primary')
            ),false);

$tool_content .= "

        <div class='col-12'>
            <div class='panel-body'>
                <div class='col-sm-6'>
                    <ul class='list-group'>
                        <li class='list-group-item'><strong>$langUsageCoursesHeader</strong><span class='badge'>".count_courses()."</span></li>
                        <li class='list-group-item li-indented'>&nbsp;&nbsp;-&nbsp;&nbsp;$langOpenCoursesShort<span class='badge'>".count_courses(COURSE_OPEN)."</span></li>
                        <li class='list-group-item li-indented'>&nbsp;&nbsp;-&nbsp;&nbsp;$langOpenCourseWithRegistration<span class='badge'>".count_courses(COURSE_REGISTRATION)."</span></li>
                        <li class='list-group-item li-indented'>&nbsp;&nbsp;-&nbsp;&nbsp;$langClosedCourses<span class='badge'>".count_courses(COURSE_CLOSED)."</span></li>
                        <li class='list-group-item li-indented'>&nbsp;&nbsp;-&nbsp;&nbsp;$langCourseInactiveShort<span class='badge'>".count_courses(COURSE_INACTIVE)."</span></li>
                    </ul>
                </div>
                <div class='col-sm-6'>
                    <ul class='list-group'>
                        <li class='list-group-item'><strong>$langUsageUsers</strong><span class='badge'>".count_users()."</span></li>
                        <li class='list-group-item li-indented'>&nbsp;&nbsp;-&nbsp;&nbsp;$langTeachers<span class='badge'>".count_users(USER_TEACHER)."</span></li>
                        <li class='list-group-item li-indented'>&nbsp;&nbsp;-&nbsp;&nbsp;$langStudents<span class='badge'>".count_users(USER_STUDENT)."</span></li>
                        <li class='list-group-item li-indented'>&nbsp;&nbsp;-&nbsp;&nbsp;$langGuest<span class='badge'>".count_users(USER_GUEST)."</span></li>
                    </ul>
                </div>
                <div class='col-sm-12'>
                    <ul class='list-group'>
                        <li class='list-group-item'><a href='../admin/otheractions.php?stats=failurelogin'>$langLoginFailures</a><small> ($langLast15Days)</small></li>
                        <li class='list-group-item'><a href='../admin/otheractions.php?stats=musers'>$langMultipleUsers</a></li>
                        <li class='list-group-item'><a href='../admin/otheractions.php?stats=memail'>$langMultipleAddr e-mail</a></li>
                        <li class='list-group-item'><a href='../admin/otheractions.php?stats=mlogins'>$langMultiplePairs LOGIN - PASS</a></li>
                        <li class='list-group-item'><a href='../admin/otheractions.php?stats=cusers'>$langMultipleCourseUsers</a><small> ($langLast30Entries)</small></li>
                        <li class='list-group-item'><a href='../admin/otheractions.php?stats=vmusers'>$langMailVerification</a></li>
                        <li class='list-group-item'><a href='../admin/otheractions.php?stats=unregusers'>$langUnregUsers</a><small> ($langLastMonth)</small></li>
                    </ul>
                </div>
            </div>
        </div>";
