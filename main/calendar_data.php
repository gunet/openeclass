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


$require_login = TRUE;

$require_valid_uid = true;
require_once '../include/lib/textLib.inc.php';
if (!session_id()) {
    session_start();
}
if (isset($_GET['course'])){
    $require_current_course = true;
}
require_once '../include/init.php';
require_once 'personal_calendar/calendar_events.class.php';

if (isset($uid)) {
    Calendar_Events::get_calendar_settings();
}
if (isset($_GET['from']) && isset($_GET['to'])){
    header('Content-Type: application/json; charset=UTF-8');
    echo Calendar_Events::bootstrap_events($_GET['from'], $_GET['to']);
}
elseif (isset($_GET['caltype']) && $_GET['caltype'] == 'small'){
    $day = (isset($_GET['day']))? intval($_GET['day']):null;
    $month = (isset($_GET['month']))? intval($_GET['month']):null;
    $year = (isset($_GET['year']))? intval($_GET['year']):null;
    echo Calendar_Events::calendar_view($day, $month, $year, 'small');
}
elseif (isset($_GET['caltype']) && $_GET['caltype'] == 'week'){
   $day = (isset($_GET['day']))? intval($_GET['day']):null;
   $month = (isset($_GET['month']))? intval($_GET['month']):null;
   $year = (isset($_GET['year']))? intval($_GET['year']):null;
   echo Calendar_Events::calendar_view($day, $month, $year, 'week');
}
elseif (isset($_GET['caltype']) && $_GET['caltype'] == 'day'){
   $day = (isset($_GET['day']))? intval($_GET['day']):null;
   $month = (isset($_GET['month']))? intval($_GET['month']):null;
   $year = (isset($_GET['year']))? intval($_GET['year']):null;
   echo Calendar_Events::calendar_view($day, $month, $year, 'day');
}
else {
   $day = (isset($_GET['day']))? intval($_GET['day']):null;
   $month = (isset($_GET['month']))? intval($_GET['month']):null;
   $year = (isset($_GET['year']))? intval($_GET['year']):null;
   echo Calendar_Events::calendar_view($day, $month, $year, 'month');
}
