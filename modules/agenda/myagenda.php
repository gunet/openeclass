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


/*
 * My-Agenda Component
 *
 * @author Evelthon Prodromou <eprodromou@upnet.gr> 
 *
 * @abstract This component generates a month-view agenda of all items of the courses
 * 	the user is enrolled in
 *
 */

$require_login = TRUE;
$require_help = TRUE;
$helpTopic = 'MyAgenda';

include '../../include/baseTheme.php';
require_once 'include/lib/textLib.inc.php';

$pageName = $langMyAgenda;
$year = '';
$month = '';

$today = getdate();
if (isset($_GET['year'])) {
    $year = intval($_GET['year']);
} else {
    $year = $today['year'];
}
if (isset($_GET['month'])) {
    $month = intval($_GET['month']);
} else {
    $month = $today['mon'];
}

$agendaitems = get_agendaitems($month, $year);
$monthName = $langMonthNames['long'][$month - 1];
display_monthcalendar($agendaitems, $month, $year, $langDay_of_weekNames['long'], $monthName, $langToday);


/*
 * @brief getagenda items
 *
 * @param resource $query MySQL resource
 * @param string $month
 * @param string $year
 * @return array of agenda items
 */
function get_agendaitems($month, $year) {
    global $urlServer, $uid;

    if ($month < 10) {
        $month = '0'.$month;
    }
    
    $query = Database::get()->queryArray("SELECT course.code k, course.public_code fc,
                                course.title i, course.prof_names t, course.id id
                            FROM course, course_user, course_module
                            WHERE course.id = course_user.course_id
                            AND course.visible != " . COURSE_INACTIVE . "
                            AND course_user.user_id = $uid
                            AND course_module.module_id  = " . MODULE_ID_AGENDA . "
                            AND course_module.visible = 1
                            AND course_module.course_id = course.id");
    
    $items = array();    
    foreach ($query as $mycours) {
        $result = Database::get()->queryArray("SELECT * FROM agenda WHERE course_id = ?d
                                        AND DATE_FORMAT(start, '%m') = ?s
                                        AND DATE_FORMAT(start, '%Y') = ?s
                                        AND visible = 1", $mycours->id, $month, $year);

        foreach ($result as $item) {
            $URL = $urlServer . "modules/agenda/index.php?course=" . $mycours->k;
            $agendadate = date('d', strtotime($item->start));
            $time = date('H:i', strtotime($item->start));
            $items[$agendadate][$time] = "<br /><small>($time) <a href='$URL' title='$mycours->i ($mycours->fc)'>$mycours->i</a> $item->title</small>";
        }
    }

    // sorting by hour for every day
    $agendaitems = array();
    while (list($agendadate, $tmpitems) = each($items)) {
        sort($tmpitems);
        $agendaitems[$agendadate] = '';
        while (list($key, $val) = each($tmpitems)) {
            $agendaitems[$agendadate] .= $val;
        }
    }
    return $agendaitems;
}

/*
 * Function display_monthcalendar
 *
 * Creates the html content of the agenda module
 *
 * @param array $agendaitems
 * @param string $month
 * @param string $year
 * @param array $weekdaynames days of the week
 * @param string $monthName
 * @param string $langToday
 */

function display_monthcalendar($agendaitems, $month, $year, $weekdaynames, $monthName, $langToday) {
    global $tool_content;

    //Handle leap year
    $numberofdays = array(0, 31, 28, 31, 30, 31, 30, 31, 31, 30, 31, 30, 31);
    if (($year % 400 == 0) or ($year % 4 == 0 and $year % 100 <> 0)) {
        $numberofdays[2] = 29;
    }

    //Get the first day of the month
    $dayone = getdate(mktime(0, 0, 0, $month, 1, $year));
    //Start the week on monday
    $startdayofweek = $dayone['wday'] <> 0 ? ($dayone['wday'] - 1) : 6;

    $backwardsURL = "$_SERVER[SCRIPT_NAME]?month=" . ($month == 1 ? 12 : $month - 1) . "&amp;year=" . ($month == 1 ? $year - 1 : $year);
    $forewardsURL = "$_SERVER[SCRIPT_NAME]?month=" . ($month == 12 ? 1 : $month + 1) . "&amp;year=" . ($month == 12 ? $year + 1 : $year);

    $tool_content .= "<table width=100% class='title1'>";
    $tool_content .= "<tr>";
    $tool_content .= "<td width='250'><a href=$backwardsURL>&laquo;</a></td>";
    $tool_content .= "<td class='center'><b>$monthName $year</b></td>";
    $tool_content .= "<td width='250' class='right'><a href=$forewardsURL>&raquo;</a></td>";
    $tool_content .= "</tr>";
    $tool_content .= "</table><br />";
    $tool_content .= "<table width=100% class='tbl_1'><tr>";
    for ($ii = 1; $ii < 8; $ii++) {
        $tool_content .= "<th class='center'>" . $weekdaynames[$ii % 7] . "</th>";
    }
    $tool_content .= "</tr>";
    $curday = -1;
    $today = getdate();

    while ($curday <= $numberofdays[$month]) {
        $tool_content .= "<tr>";

        for ($ii = 0; $ii < 7; $ii++) {
            if (($curday == -1) && ($ii == $startdayofweek)) {
                $curday = 1;
            }
            if (($curday > 0) && ($curday <= $numberofdays[$month])) {
                $bgcolor = $ii < 5 ? "class='alert alert-danger'" : "class='odd'";
                $dayheader = "$curday";
                $class_style = "class=odd";
                if (($curday == $today['mday']) && ($year == $today['year']) && ($month == $today['mon'])) {
                    $dayheader = "<b>$curday</b> <small>($langToday)</small>";
                    $class_style = "class='today'";
                }
                $tool_content .= "<td height=50 width=14% valign=top $class_style><b>$dayheader</b>";                
                if (!empty($agendaitems[$curday])) {
                    $tool_content .= "$agendaitems[$curday]</td>";
                }
                $curday++;
            } else {
                $tool_content .= "<td width=14%>&nbsp;</td>";
            }
        }
        $tool_content .= "</tr>";
    }
    $tool_content .= "</table>";
    draw($tool_content, 1);
}
