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

// Support public iCal links authorized via token
if (!isset($_GET['uid']) and !isset($_GET['token'])) {
    $require_login = true;
}

require_once '../../include/baseTheme.php';
require_once 'calendar_events.class.php';

if (empty($require_login)) {
    $uid = $_GET['uid'];
    if (!token_validate('ical' . $uid, $_GET['token'])) {
        forbidden();
    }
}

header('Content-Type:text/calendar; charset='.$charset);
header("Content-Disposition: attachment; filename=\"mycalendar.ics\"");
Calendar_Events::get_calendar_settings();
echo Calendar_Events::icalendar();
