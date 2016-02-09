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

$tool_content .= action_bar(array(
    array('title' => $langDurationVisitsPerCourse,
        'url' => "$_SERVER[SCRIPT_NAME]?t=u&amp;per_course_dur=TRUE",
        'level' => 'primary-label'),
    array('title' => $langBack,
        'url' => "/main/portfolio.php",
        'icon' => 'fa-reply',
        'level' => 'primary-label')
),false);

$statsuser = (isset($_REQUEST['u']) && intval($_REQUEST['u'])>0)? intval($_REQUEST['u']):$uid;
if ($statsuser != $uid) { 
    $toolName .= "$langUserStats: " . uid_to_name($statsuser)." (".uid_to_name($statsuser, 'username').")";
    $pageName = "$langUserStats: " . uid_to_name($statsuser)." (".uid_to_name($statsuser, 'username').")";
    $navigation[] = array('url' => '../admin/index.php', 'name' => $langAdmin);
    $navigation[] = array('url' => '../admin/listusers.php', 'name' => $langListUsers);
}
$head_content .= 
    "<script type='text/javascript'>
        startdate = null;
        interval = 1;
        enddate = null;
        module = null;
        user = $statsuser;
        course = null;
        stats = 'u';
    </script>";
require_once(dirname(__FILE__) . '/form.php');


/****   Plots   ****/
$tool_content .= "<div class='row plotscontainer'><div class='col-xs-12'>";
$tool_content .= plot_placeholder("generic_userstats", "$langHits $langAnd $langDuration");
$tool_content .= "</div></div>";

$tool_content .= "<div class='row plotscontainer'><div class='col-xs-12'>";
$tool_content .= "<div id='coursepref_pie_container' style='width:40%;float:left;margin-right:2%;'>";
$tool_content .= plot_placeholder("coursepref_pie", $langFavouriteCourse);
$tool_content .= "</div>"
              . "<div id='module_container' style='width:58%;float:left;'>";
$tool_content .= plot_placeholder("course_stats", $langModule);
$tool_content .= "</div>"
              . "</div></div>";

/****   Datatables   ****/
$tool_content .= "<div class='panel panel-default detailscontainer'>";
$tschema = "<thead><tr>"
        . "<th>$langDate</th>"
        . "<th>$langCourse</th>"
        . "<th>$langModule</th>"
        . "<th>$langHits</th>"
        . "<th>$langDuration</th>"
        . "</tr></thead>"
        . "<tbody></tbody>"
        . "<tfoot><tr><th>$langTotal</th><th></th><th></th><th></th><th></th></tr></tfoot>";
$tool_content .= table_placeholder("udetails1", "table table-striped table-bordered", $tschema, "$langHits $langAnd $langDuration");
$tool_content .= "</div>";

