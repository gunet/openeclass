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

if (isset($_REQUEST['u']) and $is_admin) {
    $statsuser = intval($_REQUEST['u']);
    $add_link = "&amp;u=$statsuser";
} else {
    $statsuser = $uid;
    $add_link = '';
}

$urlback = "../../main/portfolio.php";
if ($is_admin) {
    $urlback = "../admin/listusers.php";
}
$tool_content .= action_bar(array(
    array('title' => $langDurationVisitsPerCourse,
        'url' => "$_SERVER[SCRIPT_NAME]?t=u$add_link&amp;per_course_dur=TRUE",
        'level' => 'primary-label'),
    array('title' => $langBack,
        'url' => $urlback,
        'icon' => 'fa-reply',
        'level' => 'primary-label')
),false);

if ($is_admin) {
    $pageName = "$langUserStats: " . q(uid_to_name($statsuser)) . " (" . q(uid_to_name($statsuser, 'username')) . ")";
    $toolName .= $pageName;
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

require_once 'modules/usage/form.php';

/****   Plots   ****/
$tool_content .= "<div class='row plotscontainer'><div class='col-xs-12'>";
$tool_content .= plot_placeholder("generic_userstats", "$langHits $langAnd $langDuration");
$tool_content .= "</div></div>";

$tool_content .= "<div class='row plotscontainer'><div class='col-xs-12'><div id='coursepref_pie_container'>";
$tool_content .= plot_placeholder("coursepref_pie", $langFavouriteCourse);
$tool_content .= "</div></div></div>";

$tool_content .= "<div class='row plotscontainer'><div class='col-xs-12'><div id='module_container'>";
$tool_content .= plot_placeholder("course_stats", $langModule);
$tool_content .= "</div></div></div>";

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
