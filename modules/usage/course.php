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
$head_content .=
    "<script type='text/javascript'>
        startdate = null;
        interval = null;
        enddate = null;
        module = null;
        user = null;
        course = $course_id;
        stats = 'c';
    </script>";

if (isset($_GET['id'])) {
    $tool_content .= action_bar(array(
        array('title' => $langUsers,
            'url' => "../user/index.php",
            'icon' => 'fa-user',
            'level' => 'primary-label'),
        array('title' => $langBack,
            'url' => "{$urlServer}courses/{$course_code}",
            'icon' => 'fa-reply',
            'level' => 'primary-label')
    ),false);
} else {
    $tool_content .= action_bar(array(
        array('title' => $langUsersLog,
            'url' => "displaylog.php?course=$course_code",
            'icon' => 'fa-user',
            'level' => 'primary-label',
            'button-class' => 'btn-success'),
        array('title' => $langPlatformGenStats,
            'url' => "index.php?course=$course_code&gc_stats=true",
            'icon' => 'fa-bar-chart',
            'level' => 'primary-label'),
        array('title' => $langStatsReports,
            'url' => "userduration.php?course=$course_code",
            'icon' => 'fa-vcard-o',
            'level' => 'primary-label'),
        array('title' => $langOldStats,
            'url' => "old_stats.php",
            'icon' => 'fa-bar-chart',
            'level' => 'primary-label'),
        array('title' => $langBack,
            'url' => "{$urlServer}courses/{$course_code}",
            'icon' => 'fa-reply',
            'level' => 'primary')
    ),false);
}
/**** Summary info    ****/
if (isset($_GET['id'])) {
    $hits = course_hits($course_id, $_GET['id']);
} else {
    $hits = course_hits($course_id);
}
if (isset($_GET['id'])) {
    $regdate = Database::get()->querySingle("SELECT DATE_FORMAT(DATE(reg_date),'%e-%c-%Y') AS reg_date
                                FROM course_user
                                WHERE course_id = ?d AND user_id = ?d ORDER BY reg_date ASC LIMIT 1", $course_id, $_GET['id'])->reg_date;
    $tool_content .= "
            <div class='col-12'>                
                <div class='panel panel-default'>
                    <div class='panel-heading'>$langUserStats: ". uid_to_name($_GET['id'], 'fullname') ."</div>
                    <div class='panel-body'>
                        <div class='row'>
                            <div class='col-md-6 col-12'>
                                <ul class='list-group'>
                                    <li class='list-group-item'><strong>$langCourseRegistrationDate</strong><span class='badge rounded bg-success text-white float-end'>".$regdate."</span></li>
                                    <li class='list-group-item'><strong>$langHits</strong><span class='badge rounded bg-success text-white float-end'>".$hits['hits']."</span></li>
                                </ul>
                            </div>
                            <div class='col-md-6 col-12 mt-md-0 mt-3'>
                                <ul class='list-group'>                            
                                    <li class='list-group-item'><strong>$langDuration</strong><span class='badge rounded bg-success text-white float-end'>".$hits['duration']."</span></li>
                            </ul>
                        </div>
                    </div>
                </div>
            </div>                
        </div>";
} else {
    $tool_content .= "
            <div class='col-12'>
                <div class='panel panel-default'>
                    <div class='panel-heading'>$langUsage</div>
                    <div class='panel-body'>
                        <div class='row'>
                            <div class='col-md-6 col-12'>
                                <ul class='list-group list-group-flush'>
                                    <li class='list-group-item'><strong>$langUsageUsers</strong><span class='badge rounded bg-success text-white float-end'>".count_course_users($course_id)."</span></li>
                                    <li class='list-group-item li-indented'>&nbsp;&nbsp;-&nbsp;&nbsp;$langTeachers<span class='badge rounded bg-secondary text-white float-end'>".count_course_users($course_id,USER_TEACHER)."</span></li>
                                    <li class='list-group-item li-indented'>&nbsp;&nbsp;-&nbsp;&nbsp;$langStudents<span class='badge rounded bg-secondary text-white float-end'>".count_course_users($course_id,USER_STUDENT)."</span></li>
                                </ul>
                            </div>
                            <div class='col-md-6 col-12 mt-md-0 mt-3'>
                                <ul class='list-group list-group-flush'>
                                    <li class='list-group-item'><strong>$langGroups</strong><span class='badge rounded bg-success text-white float-end'>".count_course_groups($course_id)."</span></li>
                                    <li class='list-group-item'><strong>$langTotalVisits</strong><span class='badge rounded bg-success text-white float-end'>".course_visits($course_id)."</span></li>
                                    <li class='list-group-item'><strong>$langTotalHits</strong><span class='badge rounded bg-success text-white float-end'>".$hits['hits']."</span></li>
                                    <li class='list-group-item'><strong>$langTotalDuration</strong><span class='badge rounded bg-success text-white float-end'>".$hits['duration']."</span></li>
                                </ul>
                            </div>
                        </div>
                    </div>                
                </div>
            </div>";
}
