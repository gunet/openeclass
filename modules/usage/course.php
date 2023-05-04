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


$tool_content .= action_bar(array(
    array('title' => $langUsersLog,
        'url' => "displaylog.php?course=$course_code",
        'icon' => 'fa-user',
        'level' => 'primary-label'),
    array('title' => $langPlatformGenStats,
        'url' => "index.php?course=$course_code&gc_stats=true",
        'icon' => 'fa-bar-chart',
        'level' => 'primary-label'),
    array('title' => $langStatsReports,
        'url' => "userduration.php?course=$course_code",
        'icon' => 'fa-vcard-o',
        'level' => 'primary-label'),
    array('title' => $langBack,
        'url' => "{$urlServer}courses/{$course_code}",
        'icon' => 'fa-reply',
        'level' => 'primary-label'),
),false);

/**** Summary info    ****/

$hits = course_hits($course_id);
$tool_content .= "
    <div class='row'>
        <div class='col-xs-12'>
            <div class='panel-body'>
                <div class='row'>
                    <div class='col-sm-6'>
                        <ul class='list-group'>
                            <li class='list-group-item'><strong>$langUsageUsers</strong><span class='badge'>".count_course_users($course_id)."</span></li>
                            <li class='list-group-item li-indented'>&nbsp;&nbsp;-&nbsp;&nbsp;$langTeachers<span class='badge'>".count_course_users($course_id,USER_TEACHER)."</span></li>
                            <li class='list-group-item li-indented'>&nbsp;&nbsp;-&nbsp;&nbsp;$langStudents<span class='badge'>".count_course_users($course_id,USER_STUDENT)."</span></li>
                        </ul>
                    </div>
                    <div class='col-sm-6'>
                        <ul class='list-group'>
                        <li class='list-group-item'><strong>$langGroups</strong><span class='badge'>".count_course_groups($course_id)."</span></li>
                        <li class='list-group-item'><strong>$langTotalVisits</strong><span class='badge'>".course_visits($course_id)."</span></li>
                        <li class='list-group-item'><strong>$langTotalHits</strong><span class='badge'>".$hits['hits']."</span></li>
                        <li class='list-group-item'><strong>$langTotalDuration</strong><span class='badge'>".$hits['duration']."</span></li>
                        </ul>
                    </div>
                </div>                
            </div>
        </div>
    </div>";

