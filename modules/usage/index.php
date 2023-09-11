<?php

/* ========================================================================
 * Open eClass 3.14
 * E-learning and Course Management System
 * ========================================================================
 * Copyright 2003-2023  Greek Universities Network - GUnet
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
 * ======================================================================== */

/**
 * @file index.php
 * @brief Main script for the usage statistics module
 */

if (!isset($_REQUEST['t']) || $_REQUEST['t'] == 'c') { // course statistics
    $require_current_course = true;
    $require_course_reviewer = true;
    $helpTopic = 'course_stats';
    $stats_type = 'course';
} elseif(isset($_REQUEST['t'])) { // admin statistics
    if ($_REQUEST['t'] == 'a') {
        $require_admin = true;
        $stats_type = 'admin';
        $helpTopic = 'course_stats';
    } else if ($_REQUEST['t'] == 'u') { // user statistics
        $require_valid_uid = TRUE;
        $stats_type = 'user';
        $helpTopic = 'portfolio';
        $helpSubTopic = 'personal_stats';
    }
}

$require_help = true;
$require_login = true;
require_once '../../include/baseTheme.php';
require_once 'modules/usage/usage.lib.php';

load_js('tools.js');
load_js('bootstrap-datetimepicker');
$head_content .= "<link rel='stylesheet' type='text/css' href='{$urlAppend}js/c3-0.4.10/c3.css' />";
load_js('d3/d3.min.js');
load_js('c3-0.4.10/c3.min.js');
load_js('bootstrap-datepicker');

$head_content .= "
<script type='text/javascript'>
    var lang = '" . js_escape($language) . "';
    var langHits = '" . js_escape($langHits) . "';
    var langDuration = '" . js_escape($langDuration) . "';
    var langDay = '" . js_escape($langDay) . "';
    var langWeek = '" . js_escape($langWeek) . "';
    var langMonth = '" . js_escape($langMonth) . "';
    var langYear = '" . js_escape($langYear) . "';
    var langDepartment = '" . js_escape($langFaculty) . "';
    var langCourses = '" . js_escape($langCourses) . "';
    var langUsers = '" . js_escape($langUsers) . "';
    var maxintervals = 20;
    var views = {plots:{class: 'fa fa-bar-chart', title: '" . js_escape($langPlots) . "'}, list:{class: 'fa fa-list', title: '" . js_escape($langDetails) . "'}};
    var langNoResult = '" . js_escape($langNoResult) . "';
    var langDisplay ='" . js_escape($langDisplay) . "';
    var langResults = '" . js_escape($langResults2) . "';    
    var langTotalResults = '" . js_escape($langTotalResults) . "';
    var langDisplayed= '" . js_escape($langDisplayed) . "';
    var langTill = '" . js_escape($langTill) . "';
    var langFrom = '" . js_escape($langFrom2) . "';
    var langSearch = '" . js_escape($langSearch) . "';
    var langActions = '" . js_escape($langActions) . "';
    var langRegs = '" . js_escape($langRegisterActions) . "';
    var langUnregs = '" . js_escape($langUnregisterActions) . "';
    var langCopy = '" . js_escape($langCopy) . "';
    var langPrint = '" . js_escape($langPrint) . "';
    var langExport = '" . js_escape($langSaveAs) . "';
    var langFavouriteModule = '" . js_escape($langFavourite) . "';
    var langFavouriteCourse = '" . js_escape($langFavouriteCourse) . "';
    var langLoginUser = '" . js_escape($langLoginUser) . "';
    var langHours = '" . js_escape($langHours) . "';
</script>";
load_js('datatables');
load_js('datatables_bootstrap');
load_js('datatables_buttons');
load_js('datatables_buttons_jqueryui');
load_js('datatables_buttons_bootstrap');
load_js('datatables_buttons_print');
load_js('jszip');
load_js('pdfmake');
load_js('vfs_fonts');
load_js('datatables_buttons_html5');
load_js('bootstrap-datetimepicker');
load_js('statistics.js');
//Remove space between consecutive pagination buttons if datatables
$head_content .= "<style>
.dataTables_wrapper .dataTables_paginate .paginate_button {
    padding : 0px;
    margin-left: 0px;
    display: inline;
    border: 0px;
}
.dataTables_wrapper .dataTables_paginate .paginate_button:hover {
    border: 0px;
}
.mynowrap {
    white-space: nowrap;
}
</style>";

$pageName = $langUsage;

if (isset($_REQUEST['u'])) {
    if (!$is_admin) { // security check
        redirect_to_home_page();
    } else {
        $uid_stats = intval($_REQUEST['u']);
    }
} else {
    $uid_stats = $uid;
}

if (isset($_GET['per_course_dur'])) {
    if ($is_admin) {
        $toolName .= "$langUserStats: " . uid_to_name($uid_stats)." (".uid_to_name($uid_stats, 'username').")";
        $pageName = "$langUserStats: " . uid_to_name($uid_stats)." (".uid_to_name($uid_stats, 'username').")";
        $navigation[] = array('url' => '../admin/index.php', 'name' => $langAdmin);
        $navigation[] = array('url' => '../admin/listusers.php', 'name' => $langListUsers);
        $tool_content .= action_bar(array(
            array('title' => $langPersonalStats,
                'url' => "$_SERVER[SCRIPT_NAME]?t=u&amp;u=$uid_stats",
                'level' => 'primary-label'),
            array('title' => $langBack,
                'url' => '../admin/listusers.php',
                'icon' => 'fa-reply',
                'level' => 'primary-label')
        ),false);
    } else {
        $tool_content .= action_bar(array(
            array('title' => $langPersonalStats,
                'url' => "../usage/?t=u",
                'level' => 'primary-label'),
            array('title' => $langBack,
                'url' => "../../main/portfolio.php",
                'icon' => 'fa-reply',
                'level' => 'primary-label')
        ),false);
    }
    $tool_content .= user_duration_per_course($uid_stats);
    $tool_content .= user_last_logins($uid_stats);
} else {
    if ($stats_type == 'course' && isset($course_id) && ($is_course_reviewer || $is_admin)) { // course statistics
        if (isset($_REQUEST['gc_stats'])) {
            require_once 'modules/usage/general_course_stats.php';
        } else {
            require_once 'modules/usage/course.php';
        }
    } elseif($stats_type == 'admin' && $is_admin) { // admin statistics
        if (isset($_REQUEST['g_stats'])) {
            require_once 'modules/usage/general_admin_stats.php';
        } else {
            require_once 'modules/usage/admin.php';
        }
    } else { // user statistics
        require_once 'modules/usage/user.php';
        $stats_type = 'user';
    }
}

add_units_navigation(true);

if ($stats_type == 'admin' || ($stats_type == 'user' && isset($_REQUEST['u']))) {
    $navigation[] = array('url' => '../admin/', 'name' => $langAdmin);
    draw($tool_content, 3, null, $head_content);
} elseif ($stats_type == 'course') {
    draw($tool_content, 2, null, $head_content);
} else {
    draw($tool_content, 1, null, $head_content);
}
