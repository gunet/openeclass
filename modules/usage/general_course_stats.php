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
        interval = null;
        enddate = null;
        module = null;
        user = null;
        course = $course_id;
        stats = 'c';
    </script>";

$pageName = $langPlatformGenStats;
$navigation[] = array('url' => "index.php?course=$course_code", 'name' => $langUsage);

$action_bar = action_bar(array(
        array('title' => $langOldStats,
            'url' => "old_stats.php?course=$course_code",
            'icon' => 'fa-bar-chart',
            'level' => 'primary',
            'button-class' => 'btn-success')
    ),false);

$tool_content .= $action_bar;

require_once 'modules/usage/form.php';

/****   Plots   ****/
$tool_content .= "<div class='plotscontainer'><div class='col-12 mt-4'>";
$tool_content .= plot_placeholder("generic_stats", "$langHits $langAnd $langDuration");
$tool_content .= "</div></div>";

$tool_content .= "<div class='plotscontainer'><div class='col-12 mt-4'><div id='modulepref_pie_container'>";
$tool_content .= plot_placeholder("modulepref_pie", $langFavourite);
$tool_content .= "</div></div></div>";

$tool_content .= "<div class='plotscontainer'><div class='col-12 mt-4'><div id='module_container'>";
$tool_content .= plot_placeholder("module_stats", $langModule);
$tool_content .= "</div></div/></div>";

if (!isset($_GET['id'])) {
    $tool_content .= "<div class='plotscontainer'><div class='col-12 mt-4'>";
    $tool_content .= plot_placeholder("coursereg_stats", $langMonthlyCourseRegistrations);
    $tool_content .= "</div></div>";
}

/****   Datatables   ****/
$tool_content .= "<div class='col-sm-12 mt-4'><div class='panel panel-default detailscontainer px-lg-4 py-lg-3'>";
$tschema = "<thead><tr class='list-header'>"
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
$tool_content .= table_placeholder("cdetails1", "table-default table-striped table-logs", $tschema, "$langHits $langAnd $langDuration");
$tool_content .= "</div></div>";

if (!isset($_GET['id'])) {
    $tool_content .= "<div class='col-sm-12 mt-4'><div class='panel panel-default detailscontainer px-lg-4 py-lg-3'>";
    $tschema = "<thead><tr class='list-header'>"
        . "<th>$langDate</th>"
        . "<th>$langUser</th>"
        . "<th>$langAction</th>"
        . "<th>$langUsername</th>"
        . "<th>$langEmail</th>"
        . "</tr></thead>"
        . "<tbody></tbody>";
    $tool_content .= table_placeholder("cdetails2", "table-default table-striped table-logs", $tschema, $langMonthlyCourseRegistrations);
    $tool_content .= "</div></div>";
}

$tool_content .= "<div class='col-sm-12 mt-3'><div class='panel panel-default logscontainer px-lg-4 py-lg-3'>";
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
$tool_content .= table_placeholder("cdetails3", "table-default table-striped table-logs", $tschema, $langUsersLog);
$tool_content .= "</div></div>";
