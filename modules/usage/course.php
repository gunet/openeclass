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
            'level' => 'primary-label'),
        array('title' => $langUserDuration,
            'url' => "userduration.php?course=$course_code",
            'icon' => 'fa-clock-o',
            'level' => 'primary-label'),
        array('title' => $langOldStats,
            'url' => "oldStats.php",
            'icon' => 'fa-bar-chart',
            'level' => 'primary-label'),
        array('title' => $langBack,
            'url' => "{$urlServer}courses/{$course_code}",
            'icon' => 'fa-reply',
            'level' => 'primary-label')
    ),false);

}
/**** Summary info    ****/
if (isset($_GET['id'])) {
    $visits = course_visits($course_id, $_GET['id']);
} else {
    $visits = course_visits($course_id);
}
if (isset($_GET['id'])) {
    $regdate = Database::get()->querySingle("SELECT DATE_FORMAT(DATE(reg_date),'%e-%c-%Y') AS reg_date
                                FROM course_user
                                WHERE course_id = ?d AND user_id = ?d ORDER BY reg_date ASC LIMIT 1", $course_id, $_GET['id'])->reg_date;
    $tool_content .= "
    <div class='row'>
        <div class='col-xs-12'>
            <div class='panel panel-default'>
                <div class='panel-body'>
                    <div class='inner-heading'>$langUserStats: ". uid_to_name($_GET['id'], 'fullname') ."</div>
                    <div class='row'>
                    <div class='col-sm-6'>
                        <ul class='list-group'>
                            <li class='list-group-item'><strong>$langCourseRegistrationDate</strong><span class='badge'>".$regdate."</span></li>
                            <li class='list-group-item'><strong>$langHits</strong><span class='badge'>".$visits['hits']."</span></li>
                        </ul>
                        </div>
                        <div class='col-sm-6'>
                            <ul class='list-group'>                            
                            <li class='list-group-item'><strong>$langDuration</strong><span class='badge'>".$visits['duration']."</span></li>
                        </ul>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>";
} else {
    $tool_content .= "
    <div class='row'>
        <div class='col-xs-12'>
            <div class='panel panel-default'>
                <div class='panel-body'>
                    <div class='inner-heading'>$langPlatformGenStats</div>
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
                            <li class='list-group-item'><strong>$langHits</strong><span class='badge'>".$visits['hits']."</span></li>
                            <li class='list-group-item'><strong>$langDuration</strong><span class='badge'>".$visits['duration']."</span></li>
                        </ul>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>";
}
require_once 'modules/usage/form.php';

/****   Plots   ****/
$tool_content .= "<div class='row plotscontainer'><div class='col-xs-12'>";
$tool_content .= plot_placeholder("generic_stats", "$langHits $langAnd $langDuration");
$tool_content .= "</div></div>";

$tool_content .= "<div class='row plotscontainer'><div class='col-xs-12'>";
$tool_content .= "<div id='modulepref_pie_container' style='width:40%;float:left;margin-right:2%;'>";
$tool_content .= plot_placeholder("modulepref_pie", $langFavourite);
$tool_content .= "</div>"
              . "<div id='module_container' style='width:58%;float:left;'>";
$tool_content .= plot_placeholder("module_stats", $langModule);
$tool_content .= "</div>"
              . "</div></div>";

if (!isset($_GET['id'])) {
    $tool_content .= "<div class='row plotscontainer'><div class='col-xs-12'>";
    $tool_content .= plot_placeholder("coursereg_stats", $langMonthlyCourseRegistrations);
    $tool_content .= "</div></div>";
}

/****   Datatables   ****/
$tool_content .= "<div class='panel panel-default detailscontainer'>";
$tschema = "<thead><tr>"
        . "<th>$langDate</th>"
        . "<th>$langModule</th>"
        . "<th>$langUser</th>"
        . "<th>$langHits</th>"
        . "<th>$langDuration</th>"
        . "<th>$langUsername</th>"
        . "<th>$langEmail</th>"
        . "</tr></thead>"
        . "<tbody></tbody>"
        . "<tfoot><tr><th>$langTotal</th><th></th><th></th><th></th><th></th></tr></tfoot>";
$tool_content .= table_placeholder("cdetails1", "table table-striped table-bordered", $tschema, "$langHits $langAnd $langDuration");
$tool_content .= "</div>";

if (!isset($_GET['id'])) {
    $tool_content .= "<div class='panel panel-default detailscontainer'>";
    $tschema = "<thead><tr>"
            . "<th>$langDate</th>"
            . "<th>$langUser</th>"
            . "<th>$langAction</th>"
            . "<th>$langUsername</th>"
            . "<th>$langEmail</th>"
            . "</tr></thead>"
            . "<tbody></tbody>";
    $tool_content .= table_placeholder("cdetails2", "table table-striped table-bordered", $tschema, $langMonthlyCourseRegistrations);
    $tool_content .= "</div>";
}

$tool_content .= "<div class='panel panel-default logscontainer'>";
$tschema = "<thead><tr>"
        . "<th>$langDate - $langHour</th>"
        . "<th>$langUser</th>"
        . "<th>$langModule</th>"
        . "<th>$langAction</th>"
        . "<th>$langDetail</th>"
        . "<th>$langIpAddress</th>"
        . "<th>$langUsername</th>"
        . "<th>$langEmail</th>"
        . "</tr></thead>"
        . "<tbody></tbody>";
$tool_content .= table_placeholder("cdetails3", "table table-striped table-bordered", $tschema, $langUsersLog);
$tool_content .= "</div>";
