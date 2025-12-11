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

/**
 * @file results.php
 * @brief produce statistic analysis results in JSON
 */


if (isset($_REQUEST['t'])) {
    switch ($_REQUEST['t']) {
        case 'cg':
        case 'cmp':
        case 'cm':
        case 'cd':
        case 'crs':
        case 'ocs':
        case 'crd':
        case 'cad':
            $require_current_course = true;
            $require_course_reviewer = true;
        break;
        case 'du':
        case 'dc':
        case 'pcs':
        case 'ul':
        case 'ols':
            $require_admin = true;
        break;
        case 'ucp':
        case 'uc':
        case 'uld':
        case 'ud':
        case 'ug':
            $require_login = true;
        break;
    }
}

require_once '../../include/baseTheme.php';
require_once 'modules/usage/usage.lib.php';

$result = null;
$intervals = array(1=>'day', 7=>'week', 30=>'month', 365=>'year');
$interval = (isset($_REQUEST['i']) && isset($intervals[$_REQUEST['i']]))? $intervals[$_REQUEST['i']] : 'month';
$plotuser = (isset($_REQUEST['u']) && is_numeric($_REQUEST['u']) && $_REQUEST['u']>0)? $_REQUEST['u'] : null;
if (!is_null($plotuser) && $plotuser != $uid && !$is_admin) { // security check
    die();
}
$plotcourse = (isset($_REQUEST['c']) && is_numeric($_REQUEST['c']) && $_REQUEST['c']>0)? $_REQUEST['c'] : null;
$plotmodule = (isset($_REQUEST['m']) && is_numeric($_REQUEST['m']) && $_REQUEST['m']>0)? $_REQUEST['m'] : null;
$department = (isset($_REQUEST['d']) && is_numeric($_REQUEST['d']) && $_REQUEST['d']>0)? $_REQUEST['d'] : null;
$total = isset($_REQUEST['o']) && is_numeric($_REQUEST['o']) && $_REQUEST['o']>0;

$ds = DateTime::createFromFormat('Y-n-j', $_REQUEST['s']);
$de = DateTime::createFromFormat('Y-n-j', $_REQUEST['e']);
if(($ds && $ds->format('Y-n-j') == $_REQUEST['s']) && ($de && $de->format('Y-n-j') == $_REQUEST['e'])){
    $enddate = $_REQUEST['e'];
    $startdate = $_REQUEST['s'];
}
else{
    $endDate_obj = new DateTime();
    $enddate = $endDate_obj->format('Y-n-j');
    $startDate_obj = $endDate_obj->sub(new DateInterval('P1Y'));
    $startdate = $startDate_obj->format('Y-n-j');
}

if(isset($_REQUEST['t'])){
    switch($_REQUEST['t']){
        case 'cg':
            $result = get_course_stats($plotcourse, $interval, $startdate, $enddate, $plotuser);
            break;
        case 'cmp':
            $result = get_module_preference_stats($plotcourse, $startdate, $enddate, $plotuser);
            break;
        case 'cm':
            $result = get_course_module_stats($plotcourse, $plotmodule, $interval, $startdate, $enddate, $plotuser);
            break;
        case 'cd':
            $result = get_course_details($plotcourse, $startdate, $enddate, $plotuser);
            break;
        case 'ug':
            $result = get_user_stats($plotuser, $interval, $startdate, $enddate, $plotcourse);
            break;
        case 'ucp':
            $result = get_course_preference_stats($plotuser, $startdate, $enddate, $plotcourse);
            break;
        case 'uc':
            $result = get_user_course_stats($plotuser, $plotcourse, $plotmodule, $interval, $startdate, $enddate);
            break;
        case 'ud':
            $result = get_user_details($plotuser, $startdate, $enddate, $plotcourse);
            break;
        case 'du':
            $result = get_department_user_stats($department, $total);
            break;
        case 'dc':
            $result = get_department_course_stats($department);
            break;
        case 'ul':
            $result = get_user_login_stats($interval, $startdate, $enddate, $department);
            break;
        case 'uld':
            $result = get_user_login_details($startdate, $enddate, $department);
            break;
        case 'pcs':
            $result = get_popular_courses_stats($startdate, $enddate, $department);
            break;
        case 'crd':
            $result = get_course_registration_details($plotcourse, $startdate, $enddate);
            break;
        case 'cad':
            $result = get_course_activity_details($plotuser, $plotcourse, $startdate, $enddate, $plotmodule);
            break;
        case 'crs':
            $result = get_course_registration_stats($plotcourse, $interval, $startdate, $enddate);
            break;
        case 'ocs':
            $result = get_course_old_stats($plotcourse, $plotmodule, $startdate, $enddate);
            break;
        case 'ols':
            $result = get_login_old_stats();
            break;
    }

}
echo json_encode($result);
