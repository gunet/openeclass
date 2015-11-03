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
 * @file results.php
 * @brief produce statistic analysis results in JSON
 */

$require_login = TRUE;

require_once '../../include/init.php';
require_once 'usage.lib.php';

$result = null;
$intervals = array(1=>'day', 7=>'week', 30=>'month', 365=>'year');
$interval = (isset($_REQUEST['i']) && isset($intervals[$_REQUEST['i']]))? $intervals[$_REQUEST['i']] : 'month';
$plotuser = (isset($_REQUEST['u']) && is_numeric($_REQUEST['u']) && $_REQUEST['u']>0)? $_REQUEST['u'] : null;
$plotcourse = (isset($_REQUEST['c']) && is_numeric($_REQUEST['c']) && $_REQUEST['c']>0)? $_REQUEST['c'] : null;
$plotmodule = (isset($_REQUEST['m']) && is_numeric($_REQUEST['m']) && $_REQUEST['m']>0)? $_REQUEST['m'] : null;
$department = (isset($_REQUEST['d']) && is_numeric($_REQUEST['d']) && $_REQUEST['d']>0)? $_REQUEST['d'] : null;
$total = (isset($_REQUEST['o']) && is_numeric($_REQUEST['o']) && $_REQUEST['o']>0)? true : false;

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
            $result = get_course_stats($startdate, $enddate,$interval, $plotcourse, $plotuser);
            break;
        case 'cmp':
            $result = get_module_preference_stats($startdate, $enddate, $plotcourse, $plotuser);
            break;
        case 'cm':
            $result = get_course_module_stats($startdate, $enddate, $interval, $plotcourse, $plotmodule, $plotuser);
            break;
        case 'cd':
            $result = get_course_details($startdate, $enddate,$interval, $plotcourse, $plotuser);
            break;
        case 'ug':
            $result = get_user_stats($startdate, $enddate, $interval, $plotuser, $plotcourse);
            break;
        case 'ucp':
            $result = get_course_preference_stats($startdate, $enddate, $plotuser, $plotcourse);
            break;
        case 'uc':
            $result = get_user_course_stats($startdate, $enddate, $interval, $plotuser, $plotcourse, $plotmodule);
            break;
        case 'ud':
            $result = get_user_details($startdate, $enddate, $interval, $plotuser, $plotcourse);
            break;
        case 'du':
            $result = get_department_user_stats($department, $total);
            break;
        case 'dc':
            $result = get_department_course_stats($department);
            break;
        case 'ul':
            $result = get_user_login_stats($startdate, $enddate, $interval, $plotuser);
            break;
        case 'uld':
            $result = get_user_login_details($startdate, $enddate, $plotuser);
            break;
        case 'crd':
            $result = get_course_registration_details($startdate, $enddate, $plotcourse);
            break;
        case 'cad':
            $result = get_course_activity_details($startdate, $enddate, $plotuser, $plotcourse, $plotmodule);
            break;
        case 'crs':
            $result = get_course_registration_stats($startdate, $enddate, $interval, $plotcourse);
            break;
    }
    
}
echo json_encode($result);
