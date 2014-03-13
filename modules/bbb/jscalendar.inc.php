<?php

/* ========================================================================
 * Open eClass 3.0
 * E-learning and Course Management System
 * ========================================================================
 * Copyright 2003-2012  Greek Universities Network - GUnet
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

/* ============================================================================
  jscalendar.inc.php
  @last update: 19-10-2006 by Dionysios G. Synodinos
  @authors list: Dionysios G. Synodinos <synodinos@gmail.com>
  ==============================================================================
  @Description: Library for the pop-up calendar


  @Comments: For this to work you need to add:

  1.) draw($tool_content, 2, '', $local_head, '');
  2.) $tool_content .=  "<form method=\"post\"><tr><td>ΗΜΕΡΟΜΗΝΙΑ</td><td>".$start_cal."</td></tr></form>";


  ============================================================================== */

$local_style = '
    .month { font-weight : bold; color: #FFFFFF; background-color: #000066;
     padding-left: 15px; padding-right : 15px; }
    .content {position: relative; left: 25px; }';

require_once 'include/jscalendar/calendar.php';

$jscalendar = new DHTML_Calendar($urlServer . 'include/jscalendar/', $language, 'calendar-blue2', false);
$head_content = $jscalendar->get_load_files_code();

$u_date_end = strftime('%Y-%m-%d %H:%M', strtotime('now +2 month'));

$end_cal_Work = $jscalendar->make_input_field(
        array('showsTime' => true,
    'showOthers' => true,
    'ifFormat' => '%Y-%m-%d %H:%M',
    'timeFormat' => '24'), array('style' => 'width: 100px; color: #840; font-weight:bold; font-size:10px; background-color: #fff; border: 1px dotted #000; text-align: center',
    'name' => 'WorkEnd',
    'value' => $u_date_end));

function getJsDeadline($deadline) {
    global $language, $lang, $jscalendar, $head_content;

    $end_cal_Work_db = $jscalendar->make_input_field(
            array('showsTime' => true,
        'showOthers' => true,
        'ifFormat' => '%Y-%m-%d %H:%M',
        'timeFormat' => '24'), array('style' => 'width: 100px; color: #840; font-weight:bold; font-size:10px; background-color: #fff; border: 1px dotted #000; text-align: center',
        'name' => 'WorkEnd',
        'value' => $deadline));

    return $end_cal_Work_db;
}
