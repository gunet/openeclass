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
$tool_content .= "<div class='row'><div class='col-xs-12'><div class='panel-body'>";
$tool_content .="<table class='table-default' style='border:0;'>"
        . "<tr class='even' style='border:0;'>"
        . "<td style='border:0;'>"
        . "<div class='row' style='margin-bottom:1px;'><div class='col-sm-4'><strong>$langCoursesHeader</strong></div><div class='col-sm-2'><span class='badge'>".count_courses()."</span></div></div>"
        . "<div class='row' style='margin-bottom:1px;'><div class='col-sm-4'>$langOpenCoursesShort</div><div class='col-sm-2'><span class='badge'>".count_courses(COURSE_OPEN)."</span></div></div>"
        . "<div class='row' style='margin-bottom:1px;'><div class='col-sm-4'>$langOpenCourseWithRegistration</div><div class='col-sm-2'><span class='badge'>".count_courses(COURSE_REGISTRATION)."</span></div></div>"
        . "<div class='row' style='margin-bottom:1px;'><div class='col-sm-4'>$langClosedCourses</div><div class='col-sm-2'><span class='badge'>".count_courses(COURSE_CLOSED)."</span></div></div></td>"
        . "<td style='border:0;'>"
        . "<div class='row' style='margin-bottom:1px;'><div class='col-sm-4'><strong>$langUsers</strong></div><div class='col-sm-2'><span class='badge'>".count_users()."</span></div></div>"
        . "<div class='row' style='margin-bottom:1px;'><div class='col-sm-4'>$langTeachers</div><div class='col-sm-2'><span class='badge'>".count_users(USER_TEACHER)."</span></div></div>"
        . "<div class='row' style='margin-bottom:1px;'><div class='col-sm-4'>$langStudents</div><div class='col-sm-2'><span class='badge'>".count_users(USER_STUDENT)."</span></div></div>"
        . "<div class='row' style='margin-bottom:1px;'><div class='col-sm-4'>$langGuest</div><div class='col-sm-2'><span class='badge'>".count_users(USER_GUEST)."</span></div></div></td>"
        . "</tr>"
        . "</table>";
$tool_content .= "</div></div></div>";


/****   Form   ****/
require_once('form.php');

/****   Plots   ****/
$tool_content .= "<div class='row plotscontainer'><div class='col-xs-12'>";
$tool_content .= plot_placeholder("userlogins_stats", $langNbLogin);
$tool_content .= "</div></div>";

$tool_content .= "<div class='row plotscontainer'><div class='col-xs-12'>";
$tool_content .= "<div id='modulepref_pie_container' style='width:49%;float:left;margin-right:2%;'>";
$tool_content .= plot_placeholder("depuser_stats", $langUsers);
$tool_content .= "</div>"
              . "<div id='module_container' style='width:49%;float:left;'>";
$tool_content .= plot_placeholder("depcourse_stats", $langCoursesHeader);
$tool_content .= "</div>"
              . "</div></div>";

/****   Datatables   ****/

$tool_content .= "<div class='panel panel-default detailscontainer'>";
$tschema = "<thead><tr>"
        . "<th rowspan='2'>$langCategory</th>"
        . "<th colspan='2'>$langUsers</th>"
        . "<th colspan='4'>$langCoursesHeader</th>"
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
$tool_content .= table_placeholder("adetails1", "table table-striped table-bordered", $tschema, "$langUsers $langAnd $langCourses");
$tool_content .= "</div>";

$tool_content .= "<div class='panel panel-default detailscontainer'>";
$tschema = "<thead><tr>"
        . "<th>$langDate $langAnd $langHour</th>"
        . "<th>$langUser</th>"
        . "<th>$langCourse</th>"
        . "<th>IP address</th>"
        . "<th>$langUsername</th>"
        . "<th>$langEmail</th>"
        . "</tr>"
        . "</thead>"
        . "<tbody></tbody>";
$tool_content .= table_placeholder("adetails2", "table table-striped table-bordered", $tschema, $langNbLogin);
$tool_content .= "</div>";
