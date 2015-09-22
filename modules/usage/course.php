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

/**** Summary info    ****/
$visits = course_visits($course_id);
$tool_content .= "<div class='row'><div class='col-xs-12'><div class='panel-body'>";
$tool_content .="<table class='table-default'>"
        . "<tr class='even'>"
        . "<td><div class='row margin-bottom-thin'><div class='col-sm-4'><strong>$langUsers</strong></div><div class='col-sm-2'><span class='badge'>".count_course_users($course_id)."</span></div></div>"
        . "<div class='row margin-bottom-thin'><div class='col-sm-4'>$langTeachers</div><div class='col-sm-2'><span class='badge'>".count_course_users($course_id,USER_TEACHER)."</span></div></div>"
        . "<div class='row margin-bottom-thin'><div class='col-sm-4'>$langStudents</div><div class='col-sm-2'><span class='badge'>".count_course_users($course_id,USER_STUDENT)."</span></div></div>"
        . "</td>"
        . "<td><div class='row margin-bottom-thin'><div class='col-sm-4'><strong>$langHits</strong></div><div class='col-sm-2'><span class='badge'>".$visits['hits']."</span></div></div>"
        . "<div class='row margin-bottom-thin'><div class='col-sm-4'>$langDuration</div><div class='col-sm-2'><span class='badge'>".$visits['duration']."</span></div></div>"
        . "</td>"
        . "</tr>"
        . "</table></div></div></div>";


require_once('form.php');
/****   Plots   ****/
$tool_content .= "<div class='row plotscontainer'><div class='col-xs-12'><div class='panel'><div class='panel-body'>";
$tool_content .= "<div id='generic_stats'></div>";
$tool_content .= "</div></div></div></div>";
$tool_content .= "<div class='row plotscontainer'><div class='col-xs-12'><div class='panel'><div class='panel-body'>";
$tool_content .= "<div id='modulepref_pie' style='width:40%;float:left;'></div>";
//$tool_content .= "<div id='module' style='width:60%;float:left;'><div id='moduletitle' style='margin:auto;'></div><div id='module_stats'></div></div>";
$tool_content .= "<div id='module' style='width:60%;float:left;'>".
        "<ul class='list-group'><li class='list-group-item'><label id='moduletitle'></label></li><li class='list-group-item'><div id='module_stats'></div></li></ul>".
        "</div>";
$tool_content .= "</div></div></div></div>";

$tool_content .= "<div class='row plotscontainer'><div class='col-xs-12'><div class='panel'><div class='panel-body'>";
$tool_content .= "<div id='coursereg_stats'></div>";
$tool_content .= "</div></div></div></div>";

/****   Datatables   ****/
$tool_content .= "<div class='row detailscontainer'><div class='col-xs-12'><div class='panel'><div class='panel-body'>";
$tool_content .= "<table id='cdetails1' class='table table-striped table-bordered'><caption class='well'>$langHits $langAnd $langDuration</caption>"
        . "<thead><tr>"
        . "<th>$langDate</th>"
        . "<th>$langUser</th>"
        . "<th>$langModule</th>"
        . "<th>$langHits</th>"
        . "<th>$langDuration</th>"
        . "</tr></thead>"
        . "<tbody></tbody>"
        . "<tfoot><tr><th>$langTotal</th><th></th><th></th><th></th><th></th></tr></tfoot>"       
        . "</table>";
$tool_content .= "</div></div></div></div>";

$tool_content .= "<div class='row detailscontainer'><div class='col-xs-12'><div class='panel'><div class='panel-body'>";
$tool_content .= "<table id='cdetails2' class='table table-striped table-bordered'><caption class='well'>$langMonthlyCourseRegistrations</caption>"
        . "<thead><tr>"
        . "<th>$langDate</th>"
        . "<th>$langUser</th>"
        . "<th>$langAction</th>"
        . "</tr></thead>"
        . "<tbody></tbody>"      
        . "</table>";
$tool_content .= "</div></div></div></div>";

