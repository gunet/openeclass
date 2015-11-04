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
$tool_content .="<table class='table-default' style='border:0;'>"
        . "<tr class='even' style='border:0;'>"
        . "<td style='border:0;'>"
        . "<div class='row' style='margin-bottom:1px;'><div class='col-sm-4'><strong>$langUsers</strong></div><div class='col-sm-2'><span class='badge'>".count_course_users($course_id)."</span></div></div>"
        . "<div class='row' style='margin-bottom:1px;'><div class='col-sm-4'>$langTeachers</div><div class='col-sm-2'><span class='badge'>".count_course_users($course_id,USER_TEACHER)."</span></div></div>"
        . "<div class='row' style='margin-bottom:1px;'><div class='col-sm-4'>$langStudents</div><div class='col-sm-2'><span class='badge'>".count_course_users($course_id,USER_STUDENT)."</span></div></div>"
        . "</td>"
        . "<td style='border:0;'>"
        . "<div class='row' style='margin-bottom:1px;'><div class='col-sm-4'><strong>$langGroups</strong></div><div class='col-sm-2'><span class='badge'>".count_course_groups($course_id)."</span></div></div>"
        . "<div class='row' style='margin-bottom:1px;'><div class='col-sm-4'><strong>$langHits</strong></div><div class='col-sm-2'><span class='badge'>".$visits['hits']."</span></div></div>"
        . "<div class='row' style='margin-bottom:1px;'><div class='col-sm-4'>$langDuration</div><div class='col-sm-2'><span class='badge'>".$visits['duration']."</span></div></div>"
        . "</td>"
        . "</tr>"
        . "</table>"
        . "</div></div></div>";

require_once('form.php');
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

$tool_content .= "<div class='row plotscontainer'><div class='col-xs-12'>";
$tool_content .= plot_placeholder("coursereg_stats", $langMonthlyCourseRegistrations);
$tool_content .= "</div></div>";

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