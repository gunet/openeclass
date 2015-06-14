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
require_once('form.php');
/****   Plots   ****/
$tool_content .= "<div class='row plotscontainer'><div class='col-xs-12'><div class='panel'><div class='panel-body'>";
$tool_content .= "<ul class='list-group'><li class='list-group-item'><label id='userlogins_title'>$langNbLogin</label></li><li class='list-group-item'><div id='userlogins_stats'></div></li></ul>";
$tool_content .= "</div></div></div></div>";
$tool_content .= "<div class='row plotscontainer'><div class='col-xs-12'><div class='panel'><div class='panel-body'>";
$tool_content .= "<div id='depuser' style='width:49%;float:left;margin-right:2%;'><ul class='list-group' style=''><li class='list-group-item'><label id='depuser_title'>$langUsers</label></li><li class='list-group-item'><div id='depuser_stats'></div></li></ul></div>";
$tool_content .= "<div id='depcourse' style='width:49%;float:left;'><ul class='list-group'><li class='list-group-item'><label id='depcourse_title'>$langCoursesHeader</label></li><li class='list-group-item'><div id='depcourse_stats'></div></li></ul></div>";
$tool_content .= "</div></div></div></div>";
/****   Datatables   ****/
$tool_content .= "<div class='row detailscontainer'><div class='col-xs-12'><div class='panel'><div class='panel-body'>";
$tool_content .= "<table id='adetails1' class='table table-striped table-bordered'><caption class='well'>$langUsers $langAnd $langCourses</caption>"
        . "<thead><tr>"
        . "<th rowspan='2'>$langCategory</th>"
        . "<th colspan='2'>$langUsers</th>"
        . "<th colspan='4'>$langCoursesHeader</th>"
        . "</tr><tr>";
foreach($langStatsUserStatus as $us){
    $tool_content .= "<th>$us</th>";
}
foreach($langCourseVisibility as $ct){
    $tool_content .= "<th>$ct</th>";
}
$tool_content .= "</tr></thead>"
        . "<tbody></tbody>"
        . "<tfoot><tr><th>$langTotal</th><th></th><th></th><th></th><th></th><th></th><th></th></tr></tfoot>"       
        . "</table>";
$tool_content .= "</div></div></div></div>";
$tool_content .= "<div class='row detailscontainer'><div class='col-xs-12'><div class='panel'><div class='panel-body'>";
$tool_content .= "<table id='adetails2' class='table table-striped table-bordered'><caption class='well'>$langNbLogin</caption>"
        . "<thead><tr>"
        . "<th>$langDate $langAnd $langHour</th>"
        . "<th>$langUser</th>"
        . "<th>$langCourse</th>"
        . "<th>IP address</th>"
        . "</tr>"
        . "</thead>"
        . "<tbody></tbody>"      
        . "</table>";
$tool_content .= "</div></div></div></div>";
