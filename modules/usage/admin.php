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
        interval = 1;
        enddate = null;
        module = null;
        user = null;
        course = null;
        stats = 'a';
    </script>";

/**** Summary info    ****/

$tool_content .= action_bar(array(
                array('title' => $langStatOfFaculty,
                    'url' => "faculty_stats.php",
                    'icon' => 'fa-bar-chart',
                    'level' => 'primary-label'),
                array('title' => $langOldStats,
                    'url' => "../admin/oldStats.php",
                    'icon' => 'fa-bar-chart',
                    'level' => 'primary-label'),
                array('title' => $langBack,
                    'url' => "../admin/",
                    'icon' => 'fa-reply',
                    'level' => 'primary-label')
            ),false);

$tool_content .= "
<div class='row'>
    <div class='col-xs-12'>
        <div class='panel panel-default'>
            <div class='panel-body'>
                <div class='inner-heading'><strong>$langPlatformGenStats</strong></div>
                <div class='row'>
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
                </div>
            </div>
        </div>
    </div>
</div>";


/****   Form   ****/
require_once('form.php');

/****   Plots   ****/
$tool_content .= "<div class='row plotscontainer'>";
$tool_content .= "<div id='userlogins_container' class='col-lg-12'>";
$tool_content .= plot_placeholder("userlogins_stats", $langNbLogin);
$tool_content .= "</div>";
$tool_content .= "<div id='favcourses_container' class='col-lg-12'>";
$tool_content .= plot_placeholder("popular_courses", $langFavouriteCourses);
$tool_content .= "</div>";
$tool_content .= "</div>";

$tool_content .= "<div class='row plotscontainer'>";
$tool_content .= "<div id='modulepref_pie_container' class='col-sm-6'>";
$tool_content .= plot_placeholder("depuser_stats", $langUsers);
$tool_content .= "</div>";
$tool_content .= "<div id='module_container' class='col-sm-6'>";
$tool_content .= plot_placeholder("depcourse_stats", $langCourses);
$tool_content .= "</div>";
$tool_content .= "</div>";

/****   Datatables   ****/

$tool_content .= "<div class='panel panel-default detailscontainer'>";
$tschema = "<thead><tr>"
        . "<th rowspan='2'>$langCategory</th>"
        . "<th colspan='3'>$langUsers</th>"
        . "<th colspan='4'>$langCourses</th>"
        . "</tr><tr>";
foreach($langStatsUserStatus as $us){
    $tschema .= "<th>$us</th>";
}
foreach($langCourseVisibility as $ct){
    $tschema .= "<th>$ct</th>";
}
$tschema .= "</tr></thead>"
        . "<tbody></tbody>"
        . "<tfoot><tr><th>$langTotal</th><th></th><th></th><th></th><th></th><th></th><th></th></tr></tfoot>";
$tool_content .= table_placeholder("adetails1", "table table-default dataTable", $tschema, "$langUsers $langAnd $langCourses");
$tool_content .= "</div>";

$tool_content .= "<div class='panel panel-default detailscontainer'>";
$tschema = "<thead><tr>"
        . "<th>$langDate $langAnd $langHour</th>"
        . "<th>$langUser</th>"
        . "<th>$langCourse</th>"
        . "<th>$langIpAddress</th>"
        . "<th>$langUsername</th>"
        . "<th>$langEmail</th>"
        . "</tr>"
        . "</thead>"
        . "<tbody></tbody>";
$tool_content .= table_placeholder("adetails2", "table table-striped table-bordered", $tschema, $langNbLogin);
$tool_content .= "</div>";
