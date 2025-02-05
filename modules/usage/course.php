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


$action_bar = action_bar(array(
    array('title' => $langUsersLog,
        'url' => "displaylog.php?course=$course_code",
        'icon' => 'fa-user',
        'show' => $is_course_admin),
    array('title' => $langCharts,
        'url' => "index.php?course=$course_code&gc_stats=true",
        'icon' => 'fa-bar-chart'),
    array('title' => $langStatsReports,
        'url' => "userduration.php?course=$course_code",
        'icon' => 'fa-address-card')
    ), false);

$tool_content .= $action_bar;
/**** Summary info    ****/
$hits = course_hits($course_id);
$tool_content .= "
    <div class='col-12'>
        <div class='card panelCard card-default px-lg-4 py-lg-3'>
            <div class='card-body'>
                <div class='row row-cols-1 row-cols-md-2 g-3 g-md-4'>
                    <div class='col'>
                        <ul class='list-group list-group-flush'>
                            <li class='list-group-item list-group-item-action d-flex justify-content-between align-items-center gap-3 flex-wrap'>
                                <div>$langUsageUsers</div>
                                <div>
                                    ".count_course_users($course_id)."
                                </div>
                            </li>
                            <li class='list-group-item element d-flex justify-content-between align-items-center gap-3 flex-wrap'>
                                <div>$langTeachers</div>
                                <div>".count_course_users($course_id,USER_TEACHER)."</div>
                            </li>
                            <li class='list-group-item element d-flex justify-content-between align-items-center gap-3 flex-wrap'>
                                <div>$langStudents</div>
                                <div>".count_course_users($course_id,USER_STUDENT)."</div>
                            </li>
                        </ul>
                    </div>
                    <div class='col'>
                        <ul class='list-group list-group-flush'>
                            <li class='list-group-item element d-flex justify-content-between align-items-center gap-3 flex-wrap'>
                                <div>$langGroups</div>
                                <div>".count_course_groups($course_id)."</div>
                            </li>
                            <li class='list-group-item element d-flex justify-content-between align-items-center gap-3 flex-wrap'>
                                <div>$langTotalVisits</div>
                                <div>".course_visits($course_id)."</div>
                            </li>
                            <li class='list-group-item element d-flex justify-content-between align-items-center gap-3 flex-wrap'>
                                <div>$langTotalHits</div>
                                <div>".$hits['hits']."</div>
                            </li>
                            <li class='list-group-item element d-flex justify-content-between align-items-center gap-3 flex-wrap'>
                                <div>$langTotalDuration</div>
                                <div>".$hits['duration']."</div>
                            </li>
                        </ul>
                    </div>
                </div>
            </div>
        </div>
    </div>";
