<?php

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

$tool_content .= action_bar(array(
        array('title' => $langOldStats,
            'url' => "old_stats.php?course=$course_code",
            'icon' => 'fa-bar-chart',
            'level' => 'primary-label',
            'button-class' => 'btn-success'),
        array('title' => $langBack,
            'url' => "{$urlServer}modules/usage/index.php?course=$course_code",
            'icon' => 'fa-reply',
            'level' => 'primary-label')
    ),false);

require_once 'modules/usage/form.php';

/****   Plots   ****/
$tool_content .= "<div class='row plotscontainer'><div class='col-xs-12'>";
$tool_content .= plot_placeholder("generic_stats", "$langHits $langAnd $langDuration");
$tool_content .= "</div></div>";

$tool_content .= "<div class='row plotscontainer'><div class='col-xs-12'><div id='modulepref_pie_container'>";
$tool_content .= plot_placeholder("modulepref_pie", $langFavourite);
$tool_content .= "</div></div></div>";

$tool_content .= "<div class='row plotscontainer'><div class='col-xs-12'><div id='module_container'>";
$tool_content .= plot_placeholder("module_stats", $langModule);
$tool_content .= "</div></div/></div>";

if (!isset($_GET['id'])) {
    $tool_content .= "<div class='row plotscontainer'><div class='col-xs-12'>";
    $tool_content .= plot_placeholder("coursereg_stats", $langMonthlyCourseRegistrations);
    $tool_content .= "</div></div>";
}

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

if (!isset($_GET['id'])) {
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
}

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
