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
        interval = 1;
        enddate = null;
        module = null;
        user = null;
        course = null;
        stats = 'a';
    </script>";

/**** Summary info    ****/

$tool_content .= action_bar(array(
    array('title' => $langBack,
        'url' => "../usage/index.php?t=a",
        'icon' => 'fa-reply',
        'level' => 'primary')
),false);

/****   Form   ****/
require_once 'modules/usage/form.php';

/****   Plots   ****/
$tool_content .= "<div class='plotscontainer mt-4'>";
$tool_content .= "<div id='userlogins_container' class='col-lg-12'>";
$tool_content .= plot_placeholder("userlogins_stats", $langNbLogin.' '.$langAndTotalCourseVisits);
$tool_content .= "</div>";
$tool_content .= "<div id='favcourses_container' class='col-lg-12 mt-3'>";
$tool_content .= plot_placeholder("popular_courses", $langFavouriteCourses);
$tool_content .= "</div>";
$tool_content .= "</div>";

$tool_content .= "<div class='plotscontainer mt-4'>";
$tool_content .= "<div id='modulepref_pie_container' class='col-sm-12'>";
$tool_content .= plot_placeholder("depuser_stats", $langUsers);
$tool_content .= "</div>";
$tool_content .= "<div id='module_container' class='col-sm-12 mt-3'>";
$tool_content .= plot_placeholder("depcourse_stats", $langCourses);
$tool_content .= "</div>";
$tool_content .= "</div>";

/****   Datatables   ****/

$tool_content .= "<div class='col-sm-12 mt-4'><div class='panel panel-default detailscontainer'>";
$tschema = "<thead><tr class='list-header'>"
    . "<th rowspan='2'>$langCategory</th>"
    . "<th colspan='3'>$langUsers</th>"
    . "<th colspan='4'>$langCourses</th>"
    . "</tr><tr>";
foreach(array($langTeachers, $langStudents, $langGuests) as $us) {
    $tschema .= "<th>" . q($us) . "</th>";
}
foreach(array($langTypesInactive, $langTypesAccessControlled, $langTypesOpen, $langTypesClosed) as $ct) {
    $tschema .= "<th>" . q($ct) . "</th>";
}
$tschema .= "</tr></thead>"
    . "<tbody></tbody>"
    . "<tfoot><tr><th>$langTotal</th><th></th><th></th><th></th><th></th><th></th><th></th></tr></tfoot>";
$tool_content .= table_placeholder("adetails1", "table table-default dataTable", $tschema, "$langUsers $langAnd $langCourses");
$tool_content .= "</div></div>";

$tool_content .= "<div class='col-sm-12 mt-4'><div class='panel panel-default detailscontainer'>";
$tschema = "<thead><tr class='list-header'>"
    . "<th>$langDate $langAnd $langHour</th>"
    . "<th>$langUser</th>"
    . "<th>$langCourse</th>"
    . "<th>$langIpAddress</th>"
    . "<th>$langUsername</th>"
    . "<th>$langEmail</th>"
    . "</tr>"
    . "</thead>"
    . "<tbody></tbody>";
$tool_content .= table_placeholder("adetails2", "table-default table-striped table-bordered", $tschema, $langNbLogin);
$tool_content .= "</div></div>";

