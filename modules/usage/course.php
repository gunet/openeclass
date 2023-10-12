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
        'show' => $is_course_admin),
    array('title' => $langPlatformGenStats,
        'url' => "index.php?course=$course_code&gc_stats=true",
        'icon' => 'fa-bar-chart'),
    array('title' => $langStatsReports,
        'url' => "userduration.php?course=$course_code",
        'icon' => 'fa-address-card')
    ), false);

/**** Summary info    ****/
$hits = course_hits($course_id);
$tool_content .= "
    <div class='col-12'>
        <div class='card panelCard px-lg-4 py-lg-3'>
            <div class='card-header border-0 bg-default d-flex justify-content-between align-items-center'>
                <h3>$langUsage</h3>
            </div>
            <div class='card-body'>
                <div class='row'>
                    <div class='col-md-6 col-12'>
                        <ul class='list-group list-group-flush'>
                            <li class='list-group-item px-0'><strong>$langUsageUsers</strong><span class='badge rounded Primary-600-bg text-white float-end'>".count_course_users($course_id)."</span></li>
                            <li class='list-group-item li-indented px-0'>&nbsp;&nbsp;-&nbsp;&nbsp;$langTeachers<span class='badge rounded bgEclass normalColorBlueText float-end'>".count_course_users($course_id,USER_TEACHER)."</span></li>
                            <li class='list-group-item li-indented px-0'>&nbsp;&nbsp;-&nbsp;&nbsp;$langStudents<span class='badge rounded bgEclass normalColorBlueText float-end'>".count_course_users($course_id,USER_STUDENT)."</span></li>
                        </ul>
                    </div>
                    <div class='col-md-6 col-12 mt-md-0 mt-3'>
                        <ul class='list-group list-group-flush'>
                            <li class='list-group-item px-0'><strong>$langGroups</strong><span class='badge rounded Primary-600-bg text-white float-end'>".count_course_groups($course_id)."</span></li>
                            <li class='list-group-item px-0'><strong>$langTotalVisits</strong><span class='badge rounded Primary-600-bg text-white float-end'>".course_visits($course_id)."</span></li>
                            <li class='list-group-item px-0'><strong>$langTotalHits</strong><span class='badge rounded Primary-600-bg text-white float-end'>".$hits['hits']."</span></li>
                            <li class='list-group-item px-0'><strong>$langTotalDuration</strong><span class='badge rounded Primary-600-bg text-white float-end'>".$hits['duration']."</span></li>
                        </ul>
                    </div>
                </div>
            </div>
        </div>
    </div>";
