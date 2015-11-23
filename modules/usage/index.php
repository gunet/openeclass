<?php

/* ========================================================================
 * Open eClass 3.0
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
 * ======================================================================== */

/**
 * @file index.php
 * @brief Main script for the usage statistics module
 */

$require_help = true;
if(!isset($_REQUEST['t']) || $_REQUEST['t'] == 'c'){
    $require_current_course = true;
    $require_course_admin = true;
    $stats_type = 'course';
}
elseif(isset($_REQUEST['t']) && $_REQUEST['t'] == 'a'){
    $require_admin = true;
    $stats_type = 'admin';
}
else{ // expecting $_REQUEST['t'] == 'u'
    $require_valid_uid = TRUE;
    $stats_type = 'user';
}
$helpTopic = 'Usage';
$require_login = true;
require_once '../../include/baseTheme.php';
require_once 'usage.lib.php';

load_js('tools.js');
load_js('bootstrap-datetimepicker');
$head_content .= "
<link rel='stylesheet' type='text/css' href='{$urlAppend}js/c3-0.4.10/c3.css' />";
load_js('d3/d3.min.js');
load_js('c3-0.4.10/c3.min.js');
load_js('bootstrap-datepicker');
    
$head_content .= "
<script type='text/javascript'>
    var lang = '$language'; 
    var langHits = '$langHits';
    var langDuration = '$langDuration';
    var langDay = '$langDay';
    var langWeek = '$langWeek';
    var langMonth = '$langMonth';
    var langYear = '$langYear';
    var langDepartment = '$langFaculty';
    var langCourses = '$langCourses';
    var langUsers = '$langUsers';
    var maxintervals = 20;
    var views = {plots:{class: 'fa fa-bar-chart', title: '$langPlots'}, list:{class: 'fa fa-list', title: '$langDetails'}};
    var langNoResult = '$langNoResult';
    var langDisplay ='$langDisplay';
    var langResults = '$langResults2';
    var langNoResult = '$langNoResult';
    var langTotalResults = '$langTotalResults';
    var langDisplayed= '$langDisplayed';   
    var langTill = '$langTill'; 
    var langFrom = '$langFrom2';
    var langSearch = '$langSearch';
    var langActions = '$langActions';
    var langRegs = '$langRegisterActions';
    var langUnregs = '$langUnregisterActions';
    var langCopy = '$langCopy';
    var langPrint = '$langPrint';
    var langExport = '$langSaveAs';
    var langFavouriteModule = '$langFavourite';
    var langFavouriteCourse = '$langFavouriteCourse';
    var langLoginUser = '$langLoginUser';
    var langHours = '$langHours';
</script>";
load_js('datatables');
load_js('datatables_filtering_delay');
load_js('datatables_bootstrap');
//load_js('datatables_tabletools');
load_js('datatables_buttons');
load_js('datatables_buttons_jqueryui');
load_js('datatables_buttons_bootstrap');
load_js('datatables_buttons_print');
//load_js('datatables_buttons_flash');
load_js('jszip');
load_js('pdfmake');
load_js('vfs_fonts');
load_js('datatables_buttons_html5');
//load_js('datatables_buttons_colVis');
//load_js('datatables_buttons_foundation');
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

if($stats_type == 'course' && isset($course_id) && ($is_editor || $is_admin)){
    require_once "course.php";
}
elseif($stats_type == 'admin' && $is_admin){
    require_once "admin.php";
}
else{
    require_once "user.php";
    $stats_type = 'user';
}

add_units_navigation(true);

if($stats_type == 'admin'){
    draw($tool_content, 3, null, $head_content);
}
elseif($stats_type == 'course'){
    draw($tool_content, 2, null, $head_content);
}
else{
    draw($tool_content, 1, null, $head_content);
}