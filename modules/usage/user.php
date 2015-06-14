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
        user = $uid;
        course = null;
        stats = 'u';
    </script>";
require_once('form.php');
/****   Plots   ****/
$tool_content .= "<div class='row plotscontainer'><div class='col-xs-12'><div class='panel'><div class='panel-body'>";
$tool_content .= "<div id='generic_userstats'></div>";
$tool_content .= "</div></div></div></div>";
$tool_content .= "<div class='row plotscontainer'><div class='col-xs-12'><div class='panel'><div class='panel-body'>";
$tool_content .= "<div id='coursepref_pie' style='width:40%;float:left;'></div>";
//$tool_content .= "<div id='module' style='width:60%;float:left;'><div id='moduletitle' style='margin:auto;'></div><div id='module_stats'></div></div>";
$tool_content .= "<div id='course' style='width:60%;float:left;'>".
        "<ul class='list-group'><li class='list-group-item'><label id='coursetitle'></label></li><li class='list-group-item'><div id='course_stats'></div></li></ul>".
        "</div>";
$tool_content .= "</div></div></div></div>";
/****   Datatables   ****/
$tool_content .= "<div class='row detailscontainer'><div class='col-xs-12'><div class='panel'><div class='panel-body'>";
$tool_content .= "<table id='udetails1' class='table table-striped table-bordered'><caption class='well'>$langHits $langAnd $langDuration</caption>"
        . "<thead><tr>"
        . "<th>$langDate</th>"
        . "<th>$langCourse</th>"
        . "<th>$langModule</th>"
        . "<th>$langHits</th>"
        . "<th>$langDuration</th>"
        . "</tr></thead>"
        . "<tbody></tbody>"
        . "<tfoot><tr><th>$langTotal</th><th></th><th></th><th></th><th></th></tr></tfoot>"       
        . "</table>";
$tool_content .= "</div></div></div></div>";

